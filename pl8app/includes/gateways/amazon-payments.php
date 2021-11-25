<?php
namespace PayWithAmazon;

/**
 * Amazon Payments Gateway
 *
 * @package     pl8app
 * @subpackage  Gateways
 * @copyright   Copyright (c) 2018, Pippin's Pages, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 1.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

final class pl8app_Amazon_Payments {

	private static $instance;
	public $gateway_id      = 'amazon';
	public $client          = null;
	public $redirect_uri    = null;
	public $checkout_uri    = null;
	public $signin_redirect = null;
	public $reference_id    = null;
	public $doing_ipn       = false;
	public $is_setup        = null;

	/**
	 * Get things going
	 *
	 * @access private
	 * @since 1.0
	 * @return void
	 */
	private function __construct() {

		if ( version_compare( phpversion(), 5.3, '<' ) ) {
			// The Amazon Login & Pay libraries require PHP 5.3
			return;
		}

		$this->reference_id = ! empty( $_REQUEST['amazon_reference_id'] ) ? sanitize_text_field( $_REQUEST['amazon_reference_id'] ) : '';

		// Run this separate so we can ditch as early as possible
		$this->register();

		if ( ! pl8app_is_gateway_active( $this->gateway_id ) ) {
			return;
		}

		$this->config();
		$this->includes();
		$this->setup_client();
		$this->filters();
		$this->actions();

	}

	/**
	 * Retrieve current instance
	 *
	 * @access private
	 * @since 1.0
	 * @return pl8app_Amazon_Payments instance
	 */
	public static function getInstance() {

		if ( ! isset( self::$instance ) && ! ( self::$instance instanceof pl8app_Amazon_Payments ) ) {
			self::$instance = new pl8app_Amazon_Payments;
		}

		return self::$instance;

	}

	/**
	 * Register the payment gateway
	 *
	 * @access private
	 * @since 1.0
	 * @return void
	 */
	private function register() {

		add_filter( 'pl8app_payment_gateways', array( $this, 'register_gateway' ), 1, 1 );

	}

	/**
	 * Setup constant configuration for file paths
	 *
	 * @access private
	 * @since 1.0
	 * @return void
	 */
	private function config() {

		if ( ! defined( 'pl8app_AMAZON_CLASS_DIR' ) ) {
			$path = trailingslashit( plugin_dir_path( PL8_PLUGIN_FILE ) ) . 'includes/gateways/libs/amazon';
			define( 'pl8app_AMAZON_CLASS_DIR', trailingslashit( $path ) );
		}

	}

	/**
	 * Method to check if all the required settings have been filled out, allowing us to not output information without it.
	 *
	 * @since 1.0
	 * @return bool
	 */
	public function is_setup() {
		if ( null !== $this->is_setup ) {
			return $this->is_setup;
		}

		$required_items = array( 'merchant_id', 'client_id', 'access_key', 'secret_key' );

		$current_values = array(
			'merchant_id' => pl8app_get_option( 'amazon_seller_id', '' ),
			'client_id'   => pl8app_get_option( 'amazon_client_id', '' ),
			'access_key'  => pl8app_get_option( 'amazon_mws_access_key', '' ),
			'secret_key'  => pl8app_get_option( 'amazon_mws_secret_key', '' ),
		);

		$this->is_setup = true;

		foreach ( $required_items as $key ) {
			if ( empty( $current_values[ $key ] ) ) {
				$this->is_setup = false;
				break;
			}
		}

		return $this->is_setup;
	}

	/**
	 * Load additional files
	 *
	 * @access private
	 * @since 1.0
	 * @return void
	 */
	private function includes() {

		// Include the Amazon Library
		require_once pl8app_AMAZON_CLASS_DIR . 'Client.php'; // Requires the other files itself
		require_once pl8app_AMAZON_CLASS_DIR . 'IpnHandler.php';

	}

	/**
	 * Add filters
	 *
	 * @since 1.0
	 * @return void
	 */
	private function filters() {

		add_filter( 'pl8app_accepted_payment_icons', array( $this, 'register_payment_icon' ), 10, 1 );
		add_filter( 'pl8app_show_gateways', array( $this, 'maybe_hide_gateway_select' ) );

		// Since the Amazon Gateway loads scripts on page, it needs the scripts to load in the header.
		add_filter( 'pl8app_load_scripts_in_footer', '__return_false' );

		if ( is_admin() ) {
			add_filter( 'pl8app_settings_sections_gateways', array( $this, 'register_gateway_section' ), 1, 1 );
			add_filter( 'pl8app_settings_gateways', array( $this, 'register_gateway_settings' ), 1, 1 );
			add_filter( 'pl8app_payment_details_transaction_id-' . $this->gateway_id, array( $this, 'link_transaction_id' ), 10, 2 );
		}

	}

	/**
	 * Add actions
	 *
	 * @access private
	 * @since 1.0
	 * @return void
	 */
	private function actions() {

		add_action( 'wp_enqueue_scripts',                      array( $this, 'print_client' ), 10 );
		add_action( 'wp_enqueue_scripts',                      array( $this, 'load_scripts' ), 11 );
		add_action( 'pl8app_pre_process_purchase',                array( $this, 'check_config' ), 1  );
		add_action( 'init',                                    array( $this, 'capture_oauth' ), 9 );
		add_action( 'init',                                    array( $this, 'signin_redirect' ) );
		add_action( 'pl8app_purchase_form_before_register_login', array( $this, 'login_form' ) );
		add_action( 'pl8app_checkout_error_check',                array( $this, 'checkout_errors' ), 10, 2 );
		add_action( 'pl8app_gateway_amazon',                      array( $this, 'process_purchase' ) );
		add_action( 'wp_ajax_pl8app_amazon_get_address',          array( $this, 'ajax_get_address' ) );
		add_action( 'wp_ajax_nopriv_pl8app_amazon_get_address',   array( $this, 'ajax_get_address' ) );
		add_action( 'pl8app_pre_process_purchase',                array( $this, 'disable_address_requirement' ), 99999 );
		add_action( 'init',                                    array( $this, 'process_ipn' ) );
		add_action( 'pl8app_update_payment_status',               array( $this, 'process_refund' ), 200, 3 );

		if ( empty( $this->reference_id ) ) {
			return;
		}

		add_action( 'pl8app_amazon_cc_form', array( $this, 'wallet_form' ) );

	}

	/**
	 * Show an error message on checkout if Amazon is enabled but not setup.
	 *
	 * @since 1.0
	 */
	public function check_config() {
		$is_enabled = pl8app_is_gateway_active( $this->gateway_id );
		if ( ( ! $is_enabled || false === $this->is_setup() ) && 'amazon' == pl8app_get_chosen_gateway() ) {
			pl8app_set_error( 'amazon_gateway_not_configured', __( 'There is an error with the Amazon Payments configuration.', 'pl8app' ) );
		}
	}

	/**
	 * Retrieve the client object
	 *
	 * @access private
	 * @since 1.0
	 * @return PayWithAmazon\Client
	 */
	private function get_client() {

		if ( ! $this->is_setup() ) {
			return false;
		}

		if ( ! is_null( $this->client ) ) {
			return $this->client;
		}

		$this->setup_client();

		return $this->client;
	}

	/**
	 * Setup the client object
	 *
	 * @access private
	 * @since 1.0
	 * @return void
	 */
	private function setup_client() {

		if ( ! $this->is_setup() ) {
			return;
		}

		$region = pl8app_get_shop_country();

		if( 'GB' === $region ) {
			$region = 'UK';
		}

		$config = array(
			'merchant_id' => pl8app_get_option( 'amazon_seller_id', '' ),
			'client_id'   => pl8app_get_option( 'amazon_client_id', '' ),
			'access_key'  => pl8app_get_option( 'amazon_mws_access_key', '' ),
			'secret_key'  => pl8app_get_option( 'amazon_mws_secret_key', '' ),
			'region'      => $region,
			'sandbox'     => pl8app_is_test_mode(),
		);

		$config = apply_filters( 'pl8app_amazon_client_config', $config );

		$this->client = new Client( $config );

	}

	/**
	 * Register the gateway
	 *
	 * @since 1.0
	 * @param  $gateways array
	 * @return array
	 */
	public function register_gateway( $gateways ) {

		$default_amazon_info = array(
			$this->gateway_id => array(
				'admin_label'    => __( 'Amazon', 'pl8app' ),
				'checkout_label' => __( 'Amazon', 'pl8app' ),
				'supports'       => array(),
			),
		);

		$default_amazon_info = apply_filters( 'pl8app_register_amazon_gateway', $default_amazon_info );
		$gateways            = array_merge( $gateways, $default_amazon_info );

		return $gateways;

	}

	/**
	 * Register the payment icon
	 *
	 * @since 1.0
	 * @param  array $payment_icons Array of payment icons
	 * @return array                The array of icons with Amazon Added
	 */
	public function register_payment_icon( $payment_icons ) {
		$payment_icons['amazon'] = 'Amazon';

		return $payment_icons;
	}

	/**
	 * Hides payment gateway select options after return from Amazon
	 *
	 * @since 1.0.6
	 * @param  bool $show Should gateway select be shown
	 * @return bool
	 */
	public function maybe_hide_gateway_select( $show ) {

		if( ! empty( $_REQUEST['payment-mode'] ) && 'amazon' == $_REQUEST['payment-mode'] && ! empty( $_REQUEST['amazon_reference_id'] ) && ! empty( $_REQUEST['state'] ) && 'authorized' == $_REQUEST['state'] ) {

			$show = false;
		}

		return $show;
	}

	/**
	 * Register the payment gateways setting section
	 *
	 * @since  1.0.0
	 * @param  array $gateway_sections Array of sections for the gateways tab
	 * @return array                   Added Amazon Payments into sub-sections
	 */
	public function register_gateway_section( $gateway_sections ) {
		$gateway_sections['amazon'] = __( 'Amazon Payments', 'pl8app' );

		return $gateway_sections;
	}

	/**
	 * Register the gateway settings
	 *
	 * @since 1.0
	 * @param  $gateway_settings array
	 * @return array
	 */
	public function register_gateway_settings( $gateway_settings ) {

		$default_amazon_settings = array(
			'amazon' => array(
				'id'   => 'amazon',
				'name' => '<strong>' . __( 'Amazon Payments Settings', 'pl8app' ) . '</strong>',
				'type' => 'header',
			),
			'amazon_register' => array(
				'id'   => 'amazon_register',
				'name' => __( 'Register with Amazon', 'pl8app' ),
				'desc' => '<p><a href="' . $this->get_registration_url() . '" class="button" target="_blank">' .
						__( 'Connect pl8app to Amazon', 'pl8app' ) .
						'</a></p>' .
						'<p class="description">' .
						__( 'Once registration is complete, enter your API credentials below.', 'pl8app' ) .
						'</p>',
				'type' => 'descriptive_text',
			),
			'amazon_seller_id' => array(
				'id'   => 'amazon_seller_id',
				'name' => __( 'Seller ID', 'pl8app' ),
				'desc' => __( 'Found in the Integration settings. Also called a Merchant ID', 'pl8app' ),
				'type' => 'text',
				'size' => 'regular',
			),
			'amazon_mws_access_key' => array(
				'id'   => 'amazon_mws_access_key',
				'name' => __( 'MWS Access Key', 'pl8app' ),
				'desc' => __( 'Found on Seller Central in the MWS Keys section', 'pl8app' ),
				'type' => 'text',
				'size' => 'regular',
			),
			'amazon_mws_secret_key' => array(
				'id'   => 'amazon_mws_secret_key',
				'name' => __( 'MWS Secret Key', 'pl8app' ),
				'desc' => __( 'Found on Seller Central in the MWS Keys section', 'pl8app' ),
				'type' => 'text',
				'size' => 'regular',
			),
			'amazon_client_id' => array(
				'id'   => 'amazon_client_id',
				'name' => __( 'Client ID', 'pl8app' ),
				'desc' => __( 'The Amazon Client ID. Should look like `amzn1.application-oa2...`', 'pl8app' ),
				'type' => 'text',
				'size' => 'regular',
			),
			'amazon_mws_callback_url' => array(
				'id'       => 'amazon_callback_url',
				'name'     => __( 'Amazon MWS Callback URL', 'pl8app' ),
				'desc'     => __( 'The Return URL to provide in your MWS Application. Enter this under your Login and Pay &rarr; Web Settings', 'pl8app' ),
				'type'     => 'text',
				'size'     => 'large',
				'std'      => $this->get_amazon_authenticate_redirect(),
				'faux'     => true,
			),
			'amazon_mws_ipn_url' => array(
				'id'       => 'amazon_ipn_url',
				'name'     => __( 'Amazon Merchant IPN URL', 'pl8app' ),
				'desc'     => sprintf( __( 'The IPN URL to provide in your MWS account. Enter this under your <a href="%s">Integration Settings</a>', 'pl8app' ), 'https://sellercentral.amazon.com/gp/pyop/seller/account/settings/user-settings-edit.html' ),
				'type'     => 'text',
				'size'     => 'large',
				'std'      => $this->get_amazon_ipn_url(),
				'faux'     => true,
			),
		);

		$default_amazon_settings    = apply_filters( 'pl8app_default_amazon_settings', $default_amazon_settings );
		$gateway_settings['amazon'] = $default_amazon_settings;

		return $gateway_settings;

	}

	/**
	 * Load javascript files and localized variables
	 *
	 * @since 1.0
	 * @return void
	 */
	public function load_scripts() {

		if ( ! $this->is_setup() ) {
			return;
		}

		if ( ! pl8app_is_checkout() ) {
			return;
		}

		$test_mode = pl8app_is_test_mode();
		$seller_id = pl8app_get_option( 'amazon_seller_id', '' );
		$client_id = pl8app_get_option( 'amazon_client_id', '' );

		$default_amazon_scope = array(
			'profile',
			'postal_code',
			'payments:widget',
		);

		if ( pl8app_use_taxes() ) {
			$default_amazon_scope[] = 'payments:shipping_address';
		}

		$default_amazon_button_settings = array(
			'type'  => 'PwA',
			'color' => 'Gold',
			'size'  => 'medium',
			'scope' => implode( ' ', $default_amazon_scope ),
			'popup' => true,
		);

		$amazon_button_settings = apply_filters( 'pl8app_amazon_button_settings', $default_amazon_button_settings );
		$base_url = '';
		$sandbox  = $test_mode ? 'sandbox/' : '';

		switch ( pl8app_get_shop_country() ) {
			case 'GB':
				$base_url = 'https://static-eu.payments-amazon.com/OffAmazonPayments/uk/' . $sandbox . 'lpa/';
			break;
			case 'DE':
				$base_url = 'https://static-eu.payments-amazon.com/OffAmazonPayments/de/' . $sandbox. 'lpa/';
			break;
			default:
				$base_url = 'https://static-na.payments-amazon.com/OffAmazonPayments/us/' . $sandbox;
			break;
		}

		if ( ! empty( $base_url ) ) {

			$url = $base_url . 'js/Widgets.js?sellerId=' . $seller_id;

			wp_enqueue_script( 'pl8app-amazon-widgets', $url, array( 'jquery' ), null, false );
			wp_localize_script( 'pl8app-amazon-widgets', 'pl8app_amazon', apply_filters( 'pl8app_amazon_checkout_vars', array(
				'sellerId'      => $seller_id,
				'clientId'      => $client_id,
				'referenceID'   => $this->reference_id,
				'buttonType'    => $amazon_button_settings['type'],
				'buttonColor'   => $amazon_button_settings['color'],
				'buttonSize'    => $amazon_button_settings['size'],
				'scope'         => $amazon_button_settings['scope'],
				'popup'         => $amazon_button_settings['popup'],
				'checkoutUri'   => $this->get_amazon_checkout_uri(),
				'redirectUri'   => $this->get_amazon_authenticate_redirect(),
				'signinUri'     => $this->get_amazon_signin_redirect(),
			) ) );

		}

	}

	/**
	 * Print client ID in header
	 *
	 * @since 1.0
	 * @return void
	 */
	public function print_client() {

		if ( ! $this->is_setup() ) {
			return false;
		}

		if ( ! pl8app_is_checkout() ) {
			return;
		}
		?>
		<script>
			window.onAmazonLoginReady = function() {
				amazon.Login.setClientId(<?php echo json_encode( pl8app_get_option( 'amazon_client_id', '' ) ); ?>);
			};
		</script>
		<?php

	}

	/**
	 * Capture authentication after returning from Amazon
	 *
	 * @since 1.0
	 * @return void
	 */
	public function capture_oauth() {

		if ( ! isset( $_GET['pl8app-listener'] ) || $_GET['pl8app-listener'] !== 'amazon' ) {
			return;
		}

		if ( ! isset( $_GET['state'] ) || $_GET['state'] !== 'return_auth' ) {
			return;
		}

		if( empty( $_GET['access_token'] ) || false === strpos( $_GET['access_token'], 'Atza' ) ) {
			return;
		}

		try {

			$profile = $this->client->getUserInfo( $_GET['access_token'] );

			PL8PRESS()->session->set( 'amazon_access_token', $_GET['access_token'] );
			PL8PRESS()->session->set( 'amazon_profile', $profile );

		} catch( Exception $e ) {

			wp_die( print_r( $e, true ) );

		}

	}

	/**
	 * Set customer details after authentication
	 *
	 * @since 1.0
	 * @return void
	 */
	public function signin_redirect() {

		if ( ! isset( $_GET['pl8app-listener'] ) || $_GET['pl8app-listener'] !== 'amazon' ) {
			return;
		}

		if ( ! isset( $_GET['state'] ) || $_GET['state'] !== 'signed-in' ) {
			return;
		}

		$profile   = PL8PRESS()->session->get( 'amazon_profile' );
		$reference = $_GET['amazon_reference_id'];

		if( ! is_user_logged_in() ) {

			$user = get_user_by( 'email', $profile['email'] );

			if( $user ) {

				pl8app_log_user_in( $user->ID, $user->user_login, '' );

				$customer = array(
					'first_name' => $user->first_name,
					'last_name'  => $user->last_name,
					'email'      => $user->user_email
				);

			} else {

				$names = explode( ' ', $profile['name'], 2 );

				$customer = array(
					'first_name' => $names[0],
					'last_name'  => isset( $names[1] ) ? $names[1] : '',
					'email'      => $profile['email']
				);

				if( 'none' !== pl8app_get_option( 'show_register_form' ) ) {

					// Create a customer account if registration is not disabled

					$args  = array(
						'user_email'   => $profile['email'],
						'user_login'   => $profile['email'],
						'display_name' => $profile['name'],
						'first_name'   => $customer['first_name'],
						'last_name'    => $customer['last_name'],
						'user_pass'    => wp_generate_password( 20 ),
					);

					$user_id = wp_insert_user( $args );

					pl8app_log_user_in( $user_id, $args['user_login'], $args['user_pass'] );

				}

			}

			PL8PRESS()->session->set( 'customer', $customer );

		}


		wp_redirect( pl8app_get_checkout_uri( array( 'payment-mode' => 'amazon', 'state' => 'authorized', 'amazon_reference_id' => $reference ) ) ); exit;

	}


	/**
	 * Display the log in button
	 *
	 * @since 1.0
	 * @return void
	 */
	public function login_form() {

		if ( ! $this->is_setup() ) {
			return false;
		}

		if ( empty( $this->reference_id ) && 'amazon' == pl8app_get_chosen_gateway() ) :

			remove_all_actions( 'pl8app_purchase_form_after_cc_form' );
			remove_all_actions( 'pl8app_purchase_form_after_user_info' );
			remove_all_actions( 'pl8app_purchase_form_register_fields' );
			remove_all_actions( 'pl8app_purchase_form_login_fields' );
			remove_all_actions( 'pl8app_register_fields_before' );
			remove_all_actions( 'pl8app_cc_form' );
			remove_all_actions( 'pl8app_checkout_form_top' );

			ob_start(); ?>
			<fieldset id="pl8app-amazon-login-fields" class="pl8app-amazon-fields">

				<div id="pl8app-amazon-pay-button"></div>
				<script type="text/javascript">
					var authRequest;
					OffAmazonPayments.Button('pl8app-amazon-pay-button', pl8app_amazon.sellerId, {
						type:  pl8app_amazon.buttonType,
						color: pl8app_amazon.buttonColor,
						size:  pl8app_amazon.buttonSize,

						authorization: function() {

							loginOptions = {
								scope: pl8app_amazon.scope,
								popup: pl8app_amazon.popup
							};

							authRequest = amazon.Login.authorize( loginOptions, pl8app_amazon.redirectUri );

						},
						onSignIn: function( orderReference ) {
							amazonOrderReferenceId = orderReference.getAmazonOrderReferenceId();
							window.location = pl8app_amazon.signinUri + '&amazon_reference_id=' + amazonOrderReferenceId;
						}, onError: function(error) {
							jQuery('#pl8app_purchase_submit').prepend( '<div class="pl8app_errors"><p class="pl8app_error" id="pl8app_error_"' + error.getErrorCode() + '>' + error.getErrorMessage() + '</p></div>' );
						}
					});
				</script>

			</fieldset>

		<?php

		echo ob_get_clean();

		endif;
	}

	/**
	 * Display the wallet and address forms
	 *
	 * @since 1.0
	 * @return void
	 */
	public function wallet_form() {

		if ( ! $this->is_setup() ) {
			return false;
		}

		$profile   = PL8PRESS()->session->get( 'amazon_profile' );
		remove_action( 'pl8app_purchase_form_after_cc_form', 'pl8app_checkout_tax_fields', 999 );
		ob_start(); ?>
		<fieldset id="pl8app_cc_fields" class="pl8app-amazon-fields">
			<p class="pl8app-amazon-profile-wrapper">
				<?php _e( 'Currently logged into Amazon as', 'pl8app' ); ?>: <span class="pl8app-amazon-profile-name"><?php echo $profile['name']; ?></span>
				<span class="pl8app-amazon-logout">(<a id="Logout"><?php _e( 'Logout', 'pl8app' ); ?></a>)</span>
			</p>
			<?php if( pl8app_use_taxes() ) : ?>
				<div id="pl8app-amazon-address-box"></div>
			<?php endif; ?>
			<div id="pl8app-amazon-wallet-box"></div>
			<script>
				var pl8app_global_vars;
				if( '1' == pl8app_global_vars.taxes_enabled ) {
					new OffAmazonPayments.Widgets.AddressBook({
						sellerId: pl8app_amazon.sellerId,
						amazonOrderReferenceId: pl8app_amazon.referenceID,
						onOrderReferenceCreate: function(orderReference) {
							orderReference.getAmazonOrderReferenceId();
						},
						onAddressSelect: function(orderReference) {
							jQuery.ajax({
								type: "POST",
								data: {
									action       : 'pl8app_amazon_get_address',
									reference_id : pl8app_amazon.referenceID
								},
								dataType: "json",
								url: pl8app_global_vars.ajaxurl,
								xhrFields: {
									withCredentials: true
								},
								success: function (response) {
									jQuery('#card_city').val( response.City );
									jQuery('#card_address').val( response.AddressLine1 );
									jQuery('#card_address_2').val( response.AddressLine2 );
									jQuery('#card_zip').val( response.PostalCode );
									jQuery('#billing_country').val( response.CountryCode );
									jQuery('#card_state').val( response.StateOrRegion ).trigger( 'change' );
								}
							}).fail(function (response) {
								if ( window.console && window.console.log ) {
									console.log( response );
								}
							}).done(function (response) {

							});
						},
						design: {
							designMode: 'responsive'
						},
						onError: function(error) {
							jQuery('#pl8app-amazon-address-box').hide();
							jQuery('#pl8app_purchase_submit').prepend( '<div class="pl8app_errors"><p class="pl8app_error" id="pl8app_error_"' + error.getErrorCode() + '>' + error.getErrorMessage() + '</p></div>' );
						}
					}).bind("pl8app-amazon-address-box");

					new OffAmazonPayments.Widgets.Wallet({
						sellerId: pl8app_amazon.sellerId,
						amazonOrderReferenceId: pl8app_amazon.referenceID,
						design: {
							designMode: 'responsive'
						},
						onPaymentSelect: function(orderReference) {
							// Display your custom complete purchase button
						},
						onError: function(error) {
							jQuery('#pl8app_purchase_submit').prepend( '<div class="pl8app_errors"><p class="pl8app_error" id="pl8app_error_"' + error.getErrorCode() + '>' + error.getErrorMessage() + '</p></div>' );
						}
					}).bind("pl8app-amazon-wallet-box");

				} else {

					new OffAmazonPayments.Widgets.Wallet({
						sellerId: pl8app_amazon.sellerId,
						design: {
							designMode: 'responsive'
						},
						onOrderReferenceCreate: function(orderReference) {
							jQuery( '#pl8app_amazon_reference_id' ).val( orderReference.getAmazonOrderReferenceId() );
						},
						onPaymentSelect: function(orderReference) {
							// Display your custom complete purchase button
						},
						onError: function(error) {
							jQuery('#pl8app_purchase_submit').prepend( '<div class="pl8app_errors"><p class="pl8app_error" id="pl8app_error_"' + error.getErrorCode() + '>' + error.getErrorMessage() + '</p></div>' );
						}
					}).bind("pl8app-amazon-wallet-box");

				}
			</script>

			<div id="pl8app_cc_address">
				<input type="hidden" name="pl8app_amazon_reference_id" id="pl8app_amazon_reference_id" value="<?php echo esc_attr( $this->reference_id ); ?>"/>
				<input type="hidden" name="card_city" class="card_city" id="card_city" value=""/>
				<input type="hidden" name="card_address" class="card_address" id="card_address" value=""/>
				<input type="hidden" name="card_address_2" class="card_address_2" id="card_address_2" value=""/>
				<input type="hidden" name="card_zip" class="card_zip" id="card_zip" value=""/>
				<input type="hidden" name="card_state" class="card_state" id="card_state" value=""/>
				<input type="hidden" name="billing_country" class="billing_country" id="billing_country" value=""/>
			</div>

		</fieldset>

		<?php
		$form = ob_get_clean();
		echo $form;

	}

	/**
	 * Retrieve the billing address via ajax
	 *
	 * @since 1.0
	 * @return void
	 */
	public function ajax_get_address() {

		if ( ! $this->is_setup() ) {
			return false;
		}

		if( empty( $_POST['reference_id'] ) ) {
			die( '-2' );
		}

		$request = $this->client->getOrderReferenceDetails( array(
			'merchant_id'               => pl8app_get_option( 'amazon_seller_id', '' ),
			'amazon_order_reference_id' => $_POST['reference_id'],
			'address_consent_token'     => PL8PRESS()->session->get( 'amazon_access_token' )
		) );


		$address = array();
		$data    = new ResponseParser( $request->response );
		$data    = $data->toArray();

		if( isset( $data['GetOrderReferenceDetailsResult']['OrderReferenceDetails']['Destination']['PhysicalDestination'] ) ) {

			$address = $data['GetOrderReferenceDetailsResult']['OrderReferenceDetails']['Destination']['PhysicalDestination'];
			$address = wp_parse_args( $address, array( 'City', 'CountryCode', 'StateOrRegion', 'PostalCode', 'AddressLine1', 'AddressLine2' ) );

		}

		echo json_encode( $address ); exit;

	}

	/**
	 * Check for errors during checkout
	 *
	 * @since 1.0
	 * @param  $valid_data Customer / product data from checkout
	 * @param  $post_data $_POST
	 * @return void
	 */
	public function checkout_errors( $valid_data, $post_data ) {

		// should validate that we have a reference ID here, perhaps even fire the API call here
		if( empty( $post_data['pl8app_amazon_reference_id'] ) ) {
			pl8app_set_error( 'missing_reference_id', __( 'Missing Reference ID, please try again', 'pl8app' ) );
		}
	}

	/**
	 * Process the purchase and create the charge in Amazon
	 *
	 * @since 1.0
	 * @param  $purchase_data array Cart details
	 * @return void
	 */
	public function process_purchase( $purchase_data ) {

		if( empty( $purchase_data['post_data']['pl8app_amazon_reference_id'] ) ) {
			pl8app_set_error( 'missing_reference_id', __( 'Missing Reference ID, please try again', 'pl8app' ) );
		}

		$errors = pl8app_get_errors();
		if ( $errors ) {

			pl8app_send_back_to_checkout( '?payment-mode=amazon' );

		}

		$args = apply_filters( 'pl8app_amazon_charge_args', array(
			'merchant_id'                => pl8app_get_option( 'amazon_seller_id', '' ),
			'amazon_reference_id'        => $purchase_data['post_data']['pl8app_amazon_reference_id'],
			'authorization_reference_id' => $purchase_data['purchase_key'],
			'charge_amount'              => $purchase_data['price'],
			'currency_code'              => pl8app_get_currency(),
			'charge_note'                => html_entity_decode( pl8app_get_purchase_summary( $purchase_data, false ) ),
			'charge_order_id'            => $purchase_data['purchase_key'],
			'store_name'                 => remove_accents( wp_specialchars_decode( get_bloginfo( 'name' ), ENT_QUOTES ) ),
			'transaction_timeout'        => 0
		), $purchase_data );

		$args['platform_id'] = 'A3JST9YM1SX7LB';

		$charge = $this->client->charge( $args );

		if( 200 == $charge->response['Status'] ) {

			$charge = new ResponseParser( $charge->response );
			$charge = $charge->toArray();

			$status = $charge['AuthorizeResult']['AuthorizationDetails']['AuthorizationStatus']['State'];

			if( 'Declined' === $status ) {

				$reason = $charge['AuthorizeResult']['AuthorizationDetails']['AuthorizationStatus']['ReasonCode'];
				pl8app_set_error( 'payment_declined', sprintf( __( 'Your payment could not be authorized, please try a different payment method. Reason: %s', 'pl8app' ), $reason ) );
				pl8app_send_back_to_checkout( '?payment-mode=amazon&amazon_reference_id=' . $purchase_data['post_data']['pl8app_amazon_reference_id'] );
			}

			// Setup payment data to be recorded
			$payment_data = array(
				'price'         => $purchase_data['price'],
				'date'          => $purchase_data['date'],
				'user_email'    => $purchase_data['user_email'],
				'purchase_key'  => $purchase_data['purchase_key'],
				'currency'      => pl8app_get_currency(),
				'menuitems'     => $purchase_data['menuitems'],
				'user_info'     => $purchase_data['user_info'],
				'cart_details'  => $purchase_data['cart_details'],
				'gateway'       => $this->gateway_id,
				'status'        => 'pending',
			);

			$payment_id = pl8app_insert_payment( $payment_data );

			$authorization_id = $charge['AuthorizeResult']['AuthorizationDetails']['AmazonAuthorizationId'];
			$capture_id       = str_replace( '-A', '-C', $authorization_id );
			$reference_id     = sanitize_text_field( $_POST['pl8app_amazon_reference_id'] );

			// Confirm the capture was completed
			$capture = $this->client->getCaptureDetails( array(
				'merchant_id'       => pl8app_get_option( 'amazon_seller_id', '' ),
				'amazon_capture_id' => $capture_id
			) );

			$capture = new ResponseParser( $capture->response );
			$capture = $capture->toArray();

			pl8app_update_payment_meta( $payment_id, '_pl8app_amazon_authorization_id', $authorization_id );
			pl8app_update_payment_meta( $payment_id, '_pl8app_amazon_capture_id', $capture_id );

			pl8app_set_payment_transaction_id( $payment_id, $reference_id );

			pl8app_update_payment_status( $payment_id, 'processing' );

			// Empty the shopping cart
			pl8app_empty_cart();
			pl8app_send_to_success_page();

		} else {

			// Set an error
			pl8app_set_error( 'amazon_error',sprintf( __( 'There was an issue processing your payment. Amazon error: %s', 'pl8app' ), print_r( $charge, true ) ) );
			pl8app_send_back_to_checkout( '?payment-mode=amazon&amazon_reference_id=' . $purchase_data['post_data']['pl8app_amazon_reference_id'] );

		}


	}

	/**
	 * Retrieve the checkout URL for Amazon after authentication is complete
	 *
	 * @since 1.0
	 * @return string
	 */
	private function get_amazon_checkout_uri() {

		if ( is_null( $this->checkout_uri ) ) {
			$this->checkout_uri = esc_url_raw( add_query_arg( array( 'payment-mode' => 'amazon' ), pl8app_get_checkout_uri() ) );
		}

		return $this->checkout_uri;

	}

	/**
	 * Retrieve the return URL for Amazon after authentication on Amazon is complete
	 *
	 * @since 1.0
	 * @return string
	 */
	private function get_amazon_authenticate_redirect() {

		if ( is_null( $this->redirect_uri ) ) {
			$this->redirect_uri = esc_url_raw( add_query_arg( array( 'pl8app-listener' => 'amazon', 'state' => 'return_auth' ), pl8app_get_checkout_uri() ) );
		}

		return $this->redirect_uri;

	}

	/**
	 * Retrieve the URL to send customers too once sign-in is complete
	 *
	 * @since 1.0
	 * @return string
	 */
	private function get_amazon_signin_redirect() {

		if ( is_null( $this->signin_redirect ) ) {
			$this->signin_redirect = esc_url_raw( add_query_arg( array( 'pl8app-listener' => 'amazon', 'state' => 'signed-in' ), home_url() ) );
		}

		return $this->signin_redirect;

	}

	/**
	 * Retrieve the IPN URL for Amazon
	 *
	 * @since 1.0
	 * @return string
	 */
	private function get_amazon_ipn_url() {

		return esc_url_raw( add_query_arg( array( 'pl8app-listener' => 'amazon' ), home_url( 'index.php' ) ) );

	}

	/**
	 * Removes the requirement for entering the billing address
	 *
	 * Address is pulled directly from Amazon
	 *
	 * @since 1.0
	 * @return void
	 */
	public function disable_address_requirement() {

		if( ! empty( $_POST['pl8app-gateway'] ) && $this->gateway_id == $_REQUEST['pl8app-gateway'] ) {
			add_filter( 'pl8app_require_billing_address', '__return_false', 9999 );
		}

	}

	/**
	 * Given a transaction ID, generate a link to the Amazon transaction ID details
	 *
	 * @since 1.0
	 * @param  string $transaction_id The Transaction ID
	 * @param  int    $payment_id     The payment ID for this transaction
	 * @return string                 A link to the PayPal transaction details
	 */
	public function link_transaction_id( $transaction_id, $payment_id ) {

		$base_url = 'https://sellercentral.amazon.com/hz/me/pmd/payment-details?orderReferenceId=';
		$transaction_url = '<a href="' . esc_url( $base_url . $transaction_id ) . '" target="_blank">' . $transaction_id . '</a>';

		return apply_filters( 'pl8app_' . $this->gateway_id . '_link_payment_details_transaction_id', $transaction_url );

	}

	/**
	 * Process IPN messages from Amazon
	 *
	 * @since 1.0
	 * @return void
	 */
	public function process_ipn() {

		if ( ! isset( $_GET['pl8app-listener'] ) || $_GET['pl8app-listener'] !== 'amazon' ) {
			return;
		}

		if ( isset( $_GET['state'] ) ) {
			return;
		}

		// Get the IPN headers and Message body
		$headers = getallheaders();
		$body    = file_get_contents( 'php://input' );

		$this->doing_ipn = true;

		try {

			$ipn       = new IpnHandler( $headers, $body );
			$data      = $ipn->toArray();
			$seller_id = $data['SellerId'];

			if( $seller_id != pl8app_get_option( 'amazon_seller_id', '' ) ) {
				wp_die( __( 'Invalid Amazon seller ID', 'pl8app' ), __( 'IPN Error', 'pl8app' ), array( 'response' => 401 ) );
			}

			switch( $data['NotificationType'] ) {

				case 'OrderReferenceNotification' :

					break;

				case 'PaymentAuthorize' :

					break;

				case 'PaymentCapture' :

					$key     = $data['CaptureDetails']['CaptureReferenceId'];
					$status  = $data['CaptureDetails']['CaptureStatus']['State'];

					if( 'Declined' === $status ) {

						$payment_id = pl8app_get_purchase_id_by_key( $key );

						pl8app_update_payment_status( $payment_id, 'failed' );

						pl8app_insert_payment_note( $payment_id, __( 'Capture declined in Amazon', 'pl8app' ) );

					}

					break;


				case 'PaymentRefund' :

					$trans_id = substr( $data['RefundDetails']['AmazonRefundId'], 0, 19 );
					$status   = $data['RefundDetails']['RefundStatus']['State'];

					if( 'Completed' === $status ) {

						$payment_id = pl8app_get_purchase_id_by_transaction_id( $trans_id );

						pl8app_update_payment_status( $payment_id, 'refunded' );

						pl8app_insert_payment_note( $payment_id, sprintf( __( 'Refund completed in Amazon. Refund ID: %s', 'pl8app' ), $data['RefundDetails']['AmazonRefundId'] ) );

					}

					break;

			}



		} catch( Exception $e ) {

			wp_die( $e->getErrorMessage(), __( 'IPN Error', 'pl8app' ), array( 'response' => 401 ) );

		}

	}

	/**
	 * Detect a refund action from pl8app
	 *
	 * @since 1.0
	 * @param  $payment_id int The ID number of the payment being refunded
	 * @param  $new_status string The new status assigned to the payment
	 * @param  $old_status string The previous status of the payment
	 * @return void
	 */
	public function process_refund( $payment_id, $new_status, $old_status ) {

		if( 'publish' != $old_status && 'revoked' != $old_status ) {
			return;
		}

		if( 'refunded' != $new_status ) {
			return;
		}

		if( $this->doing_ipn ) {
			return;
		}

		if( 'amazon' !== pl8app_get_payment_gateway( $payment_id ) ) {
			return;
		}

		$this->refund( $payment_id );

	}

	/**
	 * Refund a charge in Amazon
	 *
	 * @since 1.0
	 * @param  $payment_id int The ID number of the payment being refunded
	 * @return string
	 */
	private function refund( $payment_id = 0 ) {

		$refund = $this->client->refund( array(
			'merchant_id'         => pl8app_get_option( 'amazon_seller_id', '' ),
			'amazon_capture_id'   => pl8app_get_payment_meta( $payment_id, '_pl8app_amazon_capture_id', true ),
			'refund_reference_id' => md5( pl8app_get_payment_key( $payment_id ) . '-refund' ),
			'refund_amount'       => pl8app_get_payment_amount( $payment_id ),
			'currency_code'       => pl8app_get_payment_currency_code( $payment_id ),
		) );

		if( 200 == $refund->response['Status'] ) {

			$refund = new ResponseParser( $refund->response );
			$refund = $refund->toArray();

			$reference_id = $refund['RefundResult']['RefundDetails']['RefundReferenceId'];
			$status       = $refund['RefundResult']['RefundDetails']['RefundStatus']['State'];

			switch( $status ) {

				case 'Declined' :

					$code   = $refund['RefundResult']['RefundDetails']['RefundStatus']['ReasonCode'];
					$note   = __( 'Refund declined in Amazon. Refund ID: %s', 'pl8app' );

					break;

				case 'Completed' :

					$refund_id = $refund['RefundResult']['RefundDetails']['AmazonRefundId'];
					$note      = sprintf( __( 'Refund completed in Amazon. Refund ID: %s', 'pl8app' ), $refund_id );

					break;

				case 'Pending' :

					$note = sprintf( __( 'Refund initiated in Amazon. Reference ID: %s', 'pl8app' ), $reference_id );

					break;
			}

			pl8app_insert_payment_note( $payment_id, $note );

		} else {

			pl8app_insert_payment_note( $payment_id, __( 'Refund request failed in Amazon.', 'pl8app' ) );

		}

	}

	/**
	 * Retrieve the URL for connecting Amazon account to pl8app
	 *
	 * @since 1.0
	 * @return string
	 */
	private function get_registration_url() {

		switch ( pl8app_get_shop_country() ) {
			case 'GB':
				$base_url = 'https://payments.amazon.co.uk/preregistration/lpa';
			break;
			case 'DE':
				$base_url = 'https://payments.amazon.de/preregistration/lpa';
			break;
			default:
				$base_url = 'https://sellercentral.amazon.com/hz/me/sp/signup';
			break;
		}

		$query_args = array(
			'solutionProviderId' => 'A3JST9YM1SX7LB',
			'marketplaceId'      => 'AGWSWK15IEJJ7',
			'solutionProviderToken' => 'AAAAAQAAAAEAAAAQnngerc8vYweGDt8byl2smgAAAHBgMm923quugHaGmPi%2B3sqo93TSL1aKwU85v71Zh7EXVK8De%2FuahjCFHft3cxN3rwAF4Iwg03sDW0jnkLULmFk7M1Fr69IV2XF477m0kU1EM0Z%2FbQssHdLai%2Fzoce1jZVmw8So3F2jhiDyfTHUK2AYP',
			'solutionProviderOptions' => 'lwa%3Bmws-acc%3B',
		);

		return add_query_arg( $query_args, $base_url );
	}

}

/**
 * Load pl8app_Amazon_Payments
 *
 * @since 1.0
 * @return object pl8app_Amazon_Payments
 */
function pl8app_Amazon() {
	return pl8app_Amazon_Payments::getInstance();
}
pl8app_Amazon();
