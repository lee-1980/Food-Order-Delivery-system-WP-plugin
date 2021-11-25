<?php


// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Get Checkout Form
 *
 * @since 1.0
 * @return string
 */
function pl8app_checkout_form() {
	$payment_mode = pl8app_get_chosen_gateway();
	$form_action  = esc_url( pl8app_get_checkout_uri( 'payment-mode=' . $payment_mode ) );
    $service_type = pl8app_get_option('enable_service', array());

	ob_start();

    if(!empty($service_type['pickup']) || !empty($service_type['delivery'])) {

        if (pl8app_get_cart_contents() || pl8app_cart_has_fees()) :

            pl8app_checkout_cart();
            $login_method = pl8app_get_option('login_method', 'login_guest');
            $login_class = is_user_logged_in() || $login_method == 'guest_only' ? 'pl8app-logged-in' : 'pl8app-logged-out';
            ?>
            <div id="pl8app_checkout_form_wrap"
                 class="pl8app-col-lg-8 pl8app-col-md-8 pl8app-col-sm-12 pl8app-col-xs-12 <?php echo $login_class; ?>">
                <?php do_action('pl8app_before_purchase_form'); ?>
                <form id="pl8app_purchase_form" class="pl8app_form" action="<?php echo $form_action; ?>" method="POST">
                    <?php
                    /**
                     * Hooks in at the top of the checkout form
                     *
                     * @since 1.0
                     */
                    do_action('pl8app_checkout_form_top');

                    do_action('pl8app_purchase_form');

                    do_action('pl8app_payment_mode_select');

                    /**
                     * Hooks in at the bottom of the checkout form
                     *
                     * @since 1.0
                     */
                    do_action('pl8app_checkout_form_bottom')
                    ?>
                </form>
                <?php do_action('pl8app_after_purchase_form'); ?>
            </div><!--end #pl8app_checkout_form_wrap-->
        <?php
        else:
            /**
             * Fires off when there is nothing in the cart
             *
             * @since 1.0
             */
            do_action('pl8app_cart_empty');
        endif;
        echo '</div><!--end #pl8app_checkout_wrap-->';
    }
    else{
        echo '<div id="pl8app_checkout_wrap" class="pl8app-section">';
        echo '<p>'.__('No service is available now!','pl8app').'</p>';
        echo '</div><!--end #pl8app_checkout_wrap-->';
    }

	return ob_get_clean();
}


/**
 * Renders the user account link
 *
 * @since  2.5
 * @return string
 */

function pl8app_checkout_user_account() { ?>

	<fieldset id="pl8app_checkout_login_register" class="pl8app-checkout-account-wrap pl8app-checkout-block">
		<legend><?php _e('Account', 'pl8app'); ?></legend>
		<p><?php _e('To place your order now, log into your existing account or signup now!', 'pl8app'); ?></p>
		<div class="clear"></div>
		<div class="pl8app-checkout-button-actions">
			<div class="pl8app-col-md-4 pl8app-col-lg-4 pl8app-col-sm-6 pl8app-col-xs-12">
				<span><?php _e('Have an account?', 'pl8app'); ?></span>
				<a href="<?php echo esc_url( add_query_arg( 'login', 1 ) ); ?>" class="pl8app_checkout_register_login pl8app-submit button pl8app-col-sm-12" data-action="pl8app_checkout_login"><?php _e( 'Login', 'pl8app' ); ?></a>
			</div>
			<div class="pl8app-col-md-8 pl8app-col-sm-6 pl8app-col-xs-12">
				<span><?php echo sprintf( __( 'New to %s?', 'pl8app' ), get_bloginfo( 'name' ) ); ?></span>
				<a href="<?php echo esc_url( remove_query_arg('login') ); ?>" class="pl8app_checkout_register_login pl8app-submit button" data-action="pl8app_checkout_register">
					<?php _e( 'Register', 'pl8app' ); if(!pl8app_no_guest_checkout()) { echo ' ' . __( 'or checkout as a guest', 'pl8app' ); } ?>
				</a>
			</div>
		</div>
	</fieldset> <?php
}
add_action('pl8app_purchase_login_options', 'pl8app_checkout_user_account');

/**
 * Renders the Purchase Form, hooks are provided to add to the purchase form.
 * The default Purchase Form rendered displays a list of the enabled payment
 * gateways, a user registration form (if enable) and a credit card info form
 * if credit cards are enabled
 *
 * @since  1.0.0
 * @return string
 */
function pl8app_show_purchase_form() {

	/**
	 * Hooks in at the top of the purchase form
	 *
	 * @since  1.0.0
	 */
	do_action( 'pl8app_purchase_form_top' );

	if ( pl8app_can_checkout() ) {

		$login_method = pl8app_get_option( 'login_method', 'login_guest' );

		if( ! is_user_logged_in() && $login_method != 'guest_only' ){
			do_action( 'pl8app_purchase_form_before_register_login' );
			do_action( 'pl8app_purchase_login_options' );
		}
		else{
			do_action( 'pl8app_purchase_form_after_user_info' );
		}

	} else {
		// Can't checkout
		do_action( 'pl8app_purchase_form_no_access' );
	}

	/**
	 * Hooks in at the bottom of the purchase form
	 *
	 * @since  1.0.0
	 */
	do_action( 'pl8app_purchase_form_bottom' );
}
add_action( 'pl8app_purchase_form', 'pl8app_show_purchase_form' );

function pl8app_show_cc_form() {

	$payment_mode = pl8app_get_chosen_gateway();

	/**
	 * Hooks in before Credit Card Form
	 *
	 * @since  1.0.0
	 */
	do_action( 'pl8app_purchase_form_before_cc_form' );

	if( pl8app_get_cart_total() > 0 ) {

		// Load the credit card form and allow gateways to load their own if they wish
		if ( has_action( 'pl8app_' . $payment_mode . '_cc_form' ) ) {
			do_action( 'pl8app_' . $payment_mode . '_cc_form' );
		} else {
			do_action( 'pl8app_cc_form' );
		}
	}

	/**
	 * Hooks in after Credit Card Form
	 *
	 * @since  1.0.0
	 */
	do_action( 'pl8app_purchase_form_after_cc_form' );

}

/**
 * Shows the User Info fields in the Personal Info box, more fields can be added
 * via the hooks provided.
 *
 * @since 1.0.0
 * @return void
 */
function pl8app_user_info_fields() {
	$customer = PL8PRESS()->session->get( 'customer' );
	$customer = wp_parse_args( $customer, array( 'first_name' => '', 'last_name' => '', 'email' => '', 'phone'	=> '' ) );

	if( is_user_logged_in() ) {
		$user_data = get_userdata( get_current_user_id() );
		foreach( $customer as $key => $field ) {

			if ( 'email' == $key && empty( $field ) ) {
				$customer[ $key ] = $user_data->user_email;
			} elseif ( empty( $field ) ) {
				$customer[ $key ] = $user_data->$key;
			}

		}
		$customer['phone']	= get_user_meta( get_current_user_id(), '_pl8app_phone', true );
	}
	$customer = array_map( 'sanitize_text_field', $customer );
	?>
	<fieldset id="pl8app_checkout_user_info">
		<legend><?php echo apply_filters( 'pl8app_checkout_personal_info_text', esc_html__( 'Personal Info', 'pl8app' ) ); ?></legend>
		<p id="pl8app-first-name-wrap" class="pl8app-col-md-6 pl8app-col-sm-12">
			<label class="pl8app-label" for="pl8app-first">
				<?php esc_html_e( 'First Name', 'pl8app' ); ?>
				<?php if( pl8app_field_is_required( 'pl8app_first' ) ) { ?>
					<span class="pl8app-required-indicator">*</span>
				<?php } ?>
			</label>
			<input class="pl8app-input required" type="text" name="pl8app_first" placeholder="<?php esc_html_e( 'First Name', 'pl8app' ); ?>" id="pl8app-first" value="<?php echo esc_attr( $customer['first_name'] ); ?>"<?php if( pl8app_field_is_required( 'pl8app_first' ) ) {  echo ' required '; } ?> aria-describedby="pl8app-first-description" />
		</p>
		<p id="pl8app-last-name-wrap" class="pl8app-col-md-6 pl8app-col-sm-12">
			<label class="pl8app-label" for="pl8app-last">
				<?php esc_html_e( 'Last Name', 'pl8app' ); ?>
				<?php if( pl8app_field_is_required( 'pl8app_last' ) ) { ?>
					<span class="pl8app-required-indicator">*</span>
				<?php } ?>
			</label>
			<input class="pl8app-input<?php if( pl8app_field_is_required( 'pl8app_last' ) ) { echo ' required'; } ?>" type="text" name="pl8app_last" id="pl8app-last" placeholder="<?php esc_html_e( 'Last Name', 'pl8app' ); ?>" value="<?php echo esc_attr( $customer['last_name'] ); ?>"<?php if( pl8app_field_is_required( 'pl8app_last' ) ) {  echo ' required '; } ?> aria-describedby="pl8app-last-description"/>
		</p>
		<?php do_action( 'pl8app_purchase_form_before_email' ); ?>
		<p id="pl8app-email-wrap" class="pl8app-col-md-6 pl8app-col-sm-12">
			<label class="pl8app-label" for="pl8app-email">
				<?php esc_html_e( 'Email Address', 'pl8app' ); ?>
				<?php if( pl8app_field_is_required( 'pl8app_email' ) ) { ?>
					<span class="pl8app-required-indicator">*</span>
				<?php } ?>
			</label>
			<input class="pl8app-input required" type="email" name="pl8app_email" placeholder="<?php esc_html_e( 'Email address', 'pl8app' ); ?>" id="pl8app-email" value="<?php echo esc_attr( $customer['email'] ); ?>" aria-describedby="pl8app-email-description"<?php if( pl8app_field_is_required( 'pl8app_email' ) ) {  echo ' required '; } ?>/>
		</p>
		<?php do_action( 'pl8app_purchase_form_after_email' ); ?>
		<p id="pl8app-phone-wrap" class="pl8app-col-md-6 pl8app-col-sm-12">
      <label class="pl8app-label" for="pl8app-phone"><?php esc_html_e('Phone Number', 'pl8app'); ?><span class="pl8app-required-indicator">*</span></label>
      <input class="pl8app-input required" type="text" name="pl8app_phone" id="pl8app-phone" value="<?php echo esc_attr( $customer['phone'] ); ?>" placeholder="<?php esc_html_e('Phone Number', 'pl8app'); ?>" maxlength="16" required />
    </p>
		<?php do_action( 'pl8app_purchase_form_user_info' ); ?>
		<?php do_action( 'pl8app_purchase_form_user_info_fields' ); ?>
	</fieldset>
	<?php
}
add_action( 'pl8app_purchase_form_after_user_info', 'pl8app_user_info_fields', 10 );
add_action( 'pl8app_register_fields_before', 'pl8app_user_info_fields' );

function pl8app_order_details_fields(){
?>
<!-- Order details fields -->
<fieldset id="pl8app_checkout_order_details">
	<legend><?php echo apply_filters( 'pl8app_checkout_order_details_text', esc_html__( 'Order Details', 'pl8app' ) ); ?></legend>
	<?php do_action( 'pl8app_purchase_form_before_order_details' ); ?>
	<?php
		if( pl8app_selected_service() == 'delivery' ) :
			$customer  = PL8PRESS()->session->get( 'customer' );
			$customer  = wp_parse_args( $customer, array( 'delivery_address' => array(
				'address'		=> '',
				'flat'			=> '',
				'city'    	=> '',
				'postcode'	=> '',
			) ) );

			$customer['delivery_address'] = array_map( 'sanitize_text_field', $customer['delivery_address'] );

			if( is_user_logged_in() ) {

				$user_address = get_user_meta( get_current_user_id(), '_pl8app_user_delivery_address', true );

				foreach( $customer['delivery_address'] as $key => $field ) {

					if ( empty( $field ) && ! empty( $user_address[ $key ] ) ) {
						$customer['delivery_address'][ $key ] = $user_address[ $key ];
					} else {
						$customer['delivery_address'][ $key ] = '';
					}
				}
			}
			$customer['delivery_address'] = apply_filters( 'pl8app_delivery_address', $customer['delivery_address'] );
	?>
		<p id="pl8app-street-address" class="pl8app-col-md-6 pl8app-col-sm-12">
			<label class="pl8app-street-address" for="pl8app-street-address">
				<?php esc_html_e('Street Address', 'pl8app') ?>
				<span class="pl8app-required-indicator">*</span>
			</label>
			<input class="pl8app-input" type="text" name="pl8app_street_address" id="pl8app-street-address" placeholder="<?php esc_html_e('Street Address', 'pl8app'); ?>" value="<?php echo $customer['delivery_address']['address']; ?>" />
		</p>
		<p id="pl8app-apt-suite" class="pl8app-col-md-6 pl8app-col-sm-12">
			<label class="pl8app-apt-suite" for="pl8app-apt-suite">
				<?php esc_html_e('Apartment, suite, unit etc. (optional)', 'pl8app'); ?>
			</label>
			<input class="pl8app-input" type="text" name="pl8app_apt_suite" id="pl8app-apt-suite" placeholder="<?php esc_html_e('Apartment, suite, unit etc. (optional)', 'pl8app'); ?>" value="<?php echo $customer['delivery_address']['flat']; ?>" />
		</p>
		<p id="pl8app-city" class="pl8app-col-md-6 pl8app-col-sm-12">
			<label class="pl8app-city" for="pl8app-city">
				<?php _e('Town / City', 'pl8app') ?>
				<span class="pl8app-required-indicator">*</span>
			</label>
			<input class="pl8app-input" type="text" name="pl8app_city" id="pl8app-city" placeholder="<?php _e('Town / City', 'pl8app') ?>" value="<?php echo $customer['delivery_address']['city']; ?>" />
		</p>
		<p id="pl8app-postcode" class="pl8app-col-md-6 pl8app-col-sm-12">
			<label class="pl8app-postcode" for="pl8app-postcode">
				<?php _e('Postcode / ZIP', 'pl8app') ?>
				<span class="pl8app-required-indicator">*</span>
			</label>
			<input class="pl8app-input" type="text" name="pl8app_postcode" id="pl8app-postcode" placeholder="<?php _e('Postcode / ZIP', 'pl8app') ?>" value="<?php echo $customer['delivery_address']['postcode']; ?>" />
		</p>
	<?php endif; ?>
	<p id="pl8app-order-note" class="pl8app-col-sm-12">
    <label class="pl8app-order-note" for="pl8app-order-note"><?php echo sprintf( __('%s Instructions', 'pl8app'), pl8app_selected_service( 'label' ) ); ?></label>
    <textarea name="pl8app_order_note" class="pl8app-input" rows="5" cols="8" placeholder="<?php echo sprintf( __('Add %s instructions (optional)', 'pl8app'), strtolower( pl8app_selected_service( 'label' ) ) ); ?>"></textarea>
  </p>
	<?php do_action( 'pl8app_purchase_form_order_details' ); ?>
	<?php do_action( 'pl8app_purchase_form_order_details_fields' ); ?>
</fieldset>

<?php
}
add_action( 'pl8app_purchase_form_after_user_info', 'pl8app_order_details_fields', 11 );
add_action( 'pl8app_register_fields_after', 'pl8app_order_details_fields' );
/**
 * Renders the credit card info form.
 *
 * @since 1.0
 * @return void
 */
function pl8app_get_cc_form() {
	ob_start(); ?>

	<?php do_action( 'pl8app_before_cc_fields' ); ?>

	<fieldset id="pl8app_cc_fields" class="pl8app-do-validate">
		<legend><?php _e( 'Credit Card Info', 'pl8app' ); ?></legend>
		<?php if( is_ssl() ) : ?>
			<div id="pl8app_secure_site_wrapper">
				<span class="padlock">
					<svg class="pl8app-icon pl8app-icon-lock" xmlns="http://www.w3.org/2000/svg" width="18" height="28" viewBox="0 0 18 28" aria-hidden="true">
						<path d="M5 12h8V9c0-2.203-1.797-4-4-4S5 6.797 5 9v3zm13 1.5v9c0 .828-.672 1.5-1.5 1.5h-15C.672 24 0 23.328 0 22.5v-9c0-.828.672-1.5 1.5-1.5H2V9c0-3.844 3.156-7 7-7s7 3.156 7 7v3h.5c.828 0 1.5.672 1.5 1.5z"/>
					</svg>
				</span>
				<span><?php _e( 'This is a secure SSL encrypted payment.', 'pl8app' ); ?></span>
			</div>
		<?php endif; ?>
		<p id="pl8app-card-number-wrap pl8app-col-sm-12">
			<label for="card_number" class="pl8app-label">
				<?php _e( 'Card Number', 'pl8app' ); ?>
				<span class="pl8app-required-indicator">*</span>
				<span class="card-type"></span>
			</label>
			<span class="pl8app-description"><?php _e( 'The (typically) 16 digits on the front of your credit card.', 'pl8app' ); ?></span>
			<input type="tel" pattern="^[0-9!@#$%^&* ]*$" autocomplete="off" name="card_number" id="card_number" class="card-number pl8app-input required" placeholder="<?php _e( 'Card number', 'pl8app' ); ?>" />
		</p>
		<p id="pl8app-card-cvc-wrap" class="pl8app-col-md-6 pl8app-col-sm-12">
			<label for="card_cvc" class="pl8app-label">
				<?php _e( 'CVC', 'pl8app' ); ?>
				<span class="pl8app-required-indicator">*</span>
			</label>
			<span class="pl8app-description"><?php _e( 'The 3 digit (back) or 4 digit (front) value on your card.', 'pl8app' ); ?></span>
			<input type="tel" pattern="[0-9]{3,4}" size="4" maxlength="4" autocomplete="off" name="card_cvc" id="card_cvc" class="card-cvc pl8app-input required" placeholder="<?php _e( 'Security code', 'pl8app' ); ?>" />
		</p>
		<p id="pl8app-card-name-wrap" class="pl8app-col-md-6 pl8app-col-sm-12">
			<label for="card_name" class="pl8app-label">
				<?php _e( 'Name on the Card', 'pl8app' ); ?>
				<span class="pl8app-required-indicator">*</span>
			</label>
			<span class="pl8app-description"><?php _e( 'The name printed on the front of your credit card.', 'pl8app' ); ?></span>
			<input type="text" autocomplete="off" name="card_name" id="card_name" class="card-name pl8app-input required" placeholder="<?php _e( 'Card name', 'pl8app' ); ?>" />
		</p>
		<?php do_action( 'pl8app_before_cc_expiration' ); ?>
		<p class="card-expiration pl8app-col-sm-12">
			<label for="card_exp_month" class="pl8app-label">
				<?php _e( 'Expiration (MM/YY)', 'pl8app' ); ?>
				<span class="pl8app-required-indicator">*</span>
			</label>
			<span class="pl8app-description"><?php _e( 'The date your credit card expires, typically on the front of the card.', 'pl8app' ); ?></span>
			<select id="card_exp_month" name="card_exp_month" class="card-expiry-month pl8app-select pl8app-select-small required pl8app-form-control">
				<?php for( $i = 1; $i <= 12; $i++ ) { echo '<option value="' . $i . '">' . sprintf ('%02d', $i ) . '</option>'; } ?>
			</select>
			<span class="exp-divider"> / </span>
			<select id="card_exp_year" name="card_exp_year" class="card-expiry-year pl8app-select pl8app-select-small required pl8app-form-control">
				<?php for( $i = date('Y'); $i <= date('Y') + 30; $i++ ) { echo '<option value="' . $i . '">' . substr( $i, 2 ) . '</option>'; } ?>
			</select>
		</p>
		<?php do_action( 'pl8app_after_cc_expiration' ); ?>

	</fieldset>
	<?php
	do_action( 'pl8app_after_cc_fields' );

	echo ob_get_clean();
}
add_action( 'pl8app_cc_form', 'pl8app_get_cc_form' );

/**
 * Outputs the default credit card address fields
 *
 * @since 1.0
 * @return void
 */
function pl8app_default_cc_address_fields() {

	$logged_in = is_user_logged_in();
	$customer  = PL8PRESS()->session->get( 'customer' );
	$customer  = wp_parse_args( $customer, array( 'address' => array(
		'line1'   => '',
		'line2'   => '',
		'city'    => '',
		'zip'     => '',
		'state'   => '',
		'country' => ''
	) ) );

	$customer['address'] = array_map( 'sanitize_text_field', $customer['address'] );

	if( $logged_in ) {

		$user_address = get_user_meta( get_current_user_id(), '_pl8app_user_address', true );

		foreach( $customer['address'] as $key => $field ) {

			if ( empty( $field ) && ! empty( $user_address[ $key ] ) ) {
				$customer['address'][ $key ] = $user_address[ $key ];
			} else {
				$customer['address'][ $key ] = '';
			}

		}

	}

	/**
	 * Billing Address Details.
	 *
	 * Allows filtering the customer address details that will be pre-populated on the checkout form.
	 *
	 * @since 1.0.0
	 *
	 * @param array $address The customer address.
	 * @param array $customer The customer data from the session
	 */
	$customer['address'] = apply_filters( 'pl8app_checkout_billing_details_address', $customer['address'], $customer );

	ob_start(); ?>
	<fieldset id="pl8app_cc_address" class="cc-address">
		<legend><?php _e( 'Billing Details', 'pl8app' ); ?></legend>
		<?php do_action( 'pl8app_cc_billing_top' ); ?>
		<p id="pl8app-card-address-wrap" class="pl8app-col-md-6 pl8app-col-sm-12">
			<label for="card_address" class="pl8app-label">
				<?php _e( 'Billing Address', 'pl8app' ); ?>
				<?php if( pl8app_field_is_required( 'card_address' ) ) { ?>
					<span class="pl8app-required-indicator">*</span>
				<?php } ?>
			</label>
			<span class="pl8app-description"><?php _e( 'The primary billing address for your credit card.', 'pl8app' ); ?></span>
			<input type="text" id="card_address" name="card_address" class="card-address pl8app-input<?php if( pl8app_field_is_required( 'card_address' ) ) { echo ' required'; } ?>" placeholder="<?php _e( 'Address line 1', 'pl8app' ); ?>" value="<?php echo $customer['address']['line1']; ?>"<?php if( pl8app_field_is_required( 'card_address' ) ) {  echo ' required '; } ?>/>
		</p>
		<p id="pl8app-card-address-2-wrap" class="pl8app-col-md-6 pl8app-col-sm-12">
			<label for="card_address_2" class="pl8app-label">
				<?php _e( 'Billing Address Line 2 (optional)', 'pl8app' ); ?>
				<?php if( pl8app_field_is_required( 'card_address_2' ) ) { ?>
					<span class="pl8app-required-indicator">*</span>
				<?php } ?>
			</label>
			<span class="pl8app-description"><?php _e( 'The suite, apt no, etc, associated with your billing address.', 'pl8app' ); ?></span>
			<input type="text" id="card_address_2" name="card_address_2" class="card-address-2 pl8app-input<?php if( pl8app_field_is_required( 'card_address_2' ) ) { echo ' required'; } ?>" placeholder="<?php _e( 'Address line 2', 'pl8app' ); ?>" value="<?php echo $customer['address']['line2']; ?>"<?php if( pl8app_field_is_required( 'card_address_2' ) ) {  echo ' required '; } ?>/>
		</p>
		<p id="pl8app-card-city-wrap" class="pl8app-col-md-6 pl8app-col-sm-12">
			<label for="card_city" class="pl8app-label">
				<?php _e( 'Billing City', 'pl8app' ); ?>
				<?php if( pl8app_field_is_required( 'card_city' ) ) { ?>
					<span class="pl8app-required-indicator">*</span>
				<?php } ?>
			</label>
			<span class="pl8app-description"><?php _e( 'The city for your billing address.', 'pl8app' ); ?></span>
			<input type="text" id="card_city" name="card_city" class="card-city pl8app-input<?php if( pl8app_field_is_required( 'card_city' ) ) { echo ' required'; } ?>" placeholder="<?php _e( 'City', 'pl8app' ); ?>" value="<?php echo $customer['address']['city']; ?>"<?php if( pl8app_field_is_required( 'card_city' ) ) {  echo ' required '; } ?>/>
		</p>
		<p id="pl8app-card-zip-wrap" class="pl8app-col-md-6 pl8app-col-sm-12">
			<label for="card_zip" class="pl8app-label">
				<?php _e( 'Billing Zip / Postal Code', 'pl8app' ); ?>
				<?php if( pl8app_field_is_required( 'card_zip' ) ) { ?>
					<span class="pl8app-required-indicator">*</span>
				<?php } ?>
			</label>
			<span class="pl8app-description"><?php _e( 'The zip or postal code for your billing address.', 'pl8app' ); ?></span>
			<input type="text" size="4" id="card_zip" name="card_zip" class="card-zip pl8app-input<?php if( pl8app_field_is_required( 'card_zip' ) ) { echo ' required'; } ?>" placeholder="<?php _e( 'Zip / Postal Code', 'pl8app' ); ?>" value="<?php echo $customer['address']['zip']; ?>"<?php if( pl8app_field_is_required( 'card_zip' ) ) {  echo ' required '; } ?>/>
		</p>
		<p id="pl8app-card-country-wrap" class="pl8app-col-md-6 pl8app-col-sm-12">
			<label for="billing_country" class="pl8app-label">
				<?php _e( 'Billing Country', 'pl8app' ); ?>
				<?php if( pl8app_field_is_required( 'billing_country' ) ) { ?>
					<span class="pl8app-required-indicator">*</span>
				<?php } ?>
			</label>
			<span class="pl8app-description"><?php _e( 'The country for your billing address.', 'pl8app' ); ?></span>
			<select name="billing_country" id="billing_country" class="billing_country pl8app-form-control <?php if( pl8app_field_is_required( 'billing_country' ) ) { echo ' required'; } ?>"<?php if( pl8app_field_is_required( 'billing_country' ) ) {  echo ' required '; } ?>>
				<?php

				$selected_country = pl8app_get_shop_country();

				if( ! empty( $customer['address']['country'] ) && '*' !== $customer['address']['country'] ) {
					$selected_country = $customer['address']['country'];
				}

				$countries = pl8app_get_country_list();
				foreach( $countries as $country_code => $country ) {
				  echo '<option value="' . esc_attr( $country_code ) . '"' . selected( $country_code, $selected_country, false ) . '>' . $country . '</option>';
				}
				?>
			</select>
		</p>
		<p id="pl8app-card-state-wrap" class="pl8app-col-md-6 pl8app-col-sm-12">
			<label for="card_state" class="pl8app-label">
				<?php _e( 'Billing State / Province', 'pl8app' ); ?>
				<?php if( pl8app_field_is_required( 'card_state' ) ) { ?>
					<span class="pl8app-required-indicator">*</span>
				<?php } ?>
			</label>
			<span class="pl8app-description"><?php _e( 'The state or province for your billing address.', 'pl8app' ); ?></span>
			<?php
			$selected_state = pl8app_get_shop_state();
			$states         = pl8app_get_states( $selected_country );

			if( ! empty( $customer['address']['state'] ) ) {
				$selected_state = $customer['address']['state'];
			}

			if( ! empty( $states ) ) : ?>
			<select name="card_state" id="card_state" class="card_state pl8app-form-control <?php if( pl8app_field_is_required( 'card_state' ) ) { echo ' required'; } ?>">
				<?php
					foreach( $states as $state_code => $state ) {
						echo '<option value="' . $state_code . '"' . selected( $state_code, $selected_state, false ) . '>' . $state . '</option>';
					}
				?>
			</select>
			<?php else : ?>
			<?php $customer_state = ! empty( $customer['address']['state'] ) ? $customer['address']['state'] : ''; ?>
			<input type="text" size="6" name="card_state" id="card_state" class="card_state pl8app-input" value="<?php echo esc_attr( $customer_state ); ?>" placeholder="<?php _e( 'State / Province', 'pl8app' ); ?>"/>
			<?php endif; ?>
		</p>
		<?php do_action( 'pl8app_cc_billing_bottom' ); ?>
	</fieldset>
	<?php
	echo ob_get_clean();
}
add_action( 'pl8app_after_cc_fields', 'pl8app_default_cc_address_fields' );


/**
 * Renders the billing address fields for cart taxation
 *
 * @since  1.0.0
 * @return void
 */
function pl8app_checkout_tax_fields() {
	if( pl8app_cart_needs_tax_address_fields() && pl8app_get_cart_total() && pl8app_show_billing_fields() )
		pl8app_default_cc_address_fields();
}
add_action( 'pl8app_purchase_form_after_cc_form', 'pl8app_checkout_tax_fields', 999 );


/**
 * Renders the user registration fields. If the user is logged in, a login
 * form is displayed other a registration form is provided for the user to
 * create an account.
 *
 * @since 1.0
 * @return string
 */
function pl8app_get_register_fields() {
	ob_start(); ?>
	<div id="pl8app_register_fields">

		<p id="pl8app-login-account-wrap"><?php _e( 'Already have an account?', 'pl8app' ); ?> <a href="<?php echo esc_url( add_query_arg( 'login', 1 ) ); ?>" class="pl8app_checkout_register_login" data-action="pl8app_checkout_login"><?php _e( 'Login', 'pl8app' ); ?></a></p>

		<?php do_action('pl8app_register_fields_before'); ?>

		<fieldset id="pl8app_register_account_fields">
			<legend><?php _e( 'Create an account', 'pl8app' ); if( !pl8app_no_guest_checkout() ) { echo ' ' . __( '(optional)', 'pl8app' ); } ?></legend>
			<?php do_action('pl8app_register_account_fields_before'); ?>
			<p id="pl8app-user-login-wrap" class="pl8app-col-md-6 pl8app-col-sm-12">
				<label for="pl8app_user_login">
					<?php _e( 'Username', 'pl8app' ); ?>
					<?php if( pl8app_no_guest_checkout() ) { ?>
					<span class="pl8app-required-indicator">*</span>
					<?php } ?>
				</label>
				<span class="pl8app-description"><?php _e( 'The username you will use to log into your account.', 'pl8app' ); ?></span>
				<input name="pl8app_user_login" id="pl8app_user_login" class="<?php if(pl8app_no_guest_checkout()) { echo 'required '; } ?>pl8app-input" type="text" placeholder="<?php _e( 'Username', 'pl8app' ); ?>"/>
			</p>
			<p id="pl8app-user-pass-wrap" class="pl8app-col-md-6 pl8app-col-sm-12">
				<label for="pl8app_user_pass">
					<?php _e( 'Password', 'pl8app' ); ?>
					<?php if( pl8app_no_guest_checkout() ) { ?>
					<span class="pl8app-required-indicator">*</span>
					<?php } ?>
				</label>
				<span class="pl8app-description"><?php _e( 'The password used to access your account.', 'pl8app' ); ?></span>
				<input name="pl8app_user_pass" id="pl8app_user_pass" class="<?php if(pl8app_no_guest_checkout()) { echo 'required '; } ?>pl8app-input" placeholder="<?php _e( 'Password', 'pl8app' ); ?>" type="password"/>
			</p>
			<?php do_action( 'pl8app_register_account_fields_after' ); ?>
		</fieldset>

		<?php do_action('pl8app_register_fields_after'); ?>

		<input type="hidden" name="pl8app-purchase-var" value="needs-to-register"/>

		<?php do_action( 'pl8app_purchase_form_user_info' ); ?>
		<?php do_action( 'pl8app_purchase_form_user_register_fields' ); ?>

	</div>
	<?php
	echo ob_get_clean();
}
add_action( 'pl8app_purchase_form_register_fields', 'pl8app_get_register_fields' );

/**
 * Gets the login fields for the login form on the checkout. This function hooks
 * on the pl8app_purchase_form_login_fields to display the login form if a user already
 * had an account.
 *
 * @since 1.0
 * @return string
 */
function pl8app_get_login_fields() {

	ob_start(); ?>

	<fieldset id="pl8app_login_fields">
		<p id="pl8app-new-account-wrap">
			<?php _e( 'Need to create an account?', 'pl8app' ); ?>
			<a href="<?php echo esc_url( remove_query_arg('login') ); ?>" class="pl8app_checkout_register_login" data-action="pl8app_checkout_register">
				<?php _e( 'Register', 'pl8app' ); if(!pl8app_no_guest_checkout()) { echo ' ' . __( 'or checkout as a guest', 'pl8app' ); } ?>
			</a>
		</p>
		<?php do_action('pl8app_checkout_login_fields_before'); ?>
		<p id="pl8app-user-login-wrap" class="pl8app-col-md-6 pl8app-col-sm-12">
			<label class="pl8app-label" for="pl8app-username">
				<?php _e( 'Username or Email', 'pl8app' ); ?>
				<?php if( pl8app_no_guest_checkout() ) { ?>
				<span class="pl8app-required-indicator">*</span>
				<?php } ?>
			</label>
			<input class="<?php if(pl8app_no_guest_checkout()) { echo 'required '; } ?>pl8app-input" type="text" name="pl8app_user_login" id="pl8app_user_login" value="" placeholder="<?php _e( 'Your username or email address', 'pl8app' ); ?>"/>
		</p>
		<p id="pl8app-user-pass-wrap" class="pl8app-col-md-6 pl8app-col-sm-12 pl8app_login_password">
			<label class="pl8app-label" for="pl8app-password">
				<?php _e( 'Password', 'pl8app' ); ?>
				<?php if( pl8app_no_guest_checkout() ) { ?>
				<span class="pl8app-required-indicator">*</span>
				<?php } ?>
			</label>
			<input class="<?php if( pl8app_no_guest_checkout() ) { echo 'required '; } ?>pl8app-input" type="password" name="pl8app_user_pass" id="pl8app_user_pass" placeholder="<?php _e( 'Your password', 'pl8app' ); ?>"/>
			<?php if( pl8app_no_guest_checkout() ) : ?>
				<input type="hidden" name="pl8app-purchase-var" value="needs-to-login"/>
			<?php endif; ?>
		</p>
		<p id="pl8app-user-login-submit">
			<input type="submit" class="pl8app-submit button" name="pl8app_login_submit" value="<?php _e( 'Login', 'pl8app' ); ?>"/>
		</p>
		<?php do_action('pl8app_checkout_login_fields_after'); ?>
	</fieldset><!--end #pl8app_login_fields-->
	<?php
	echo ob_get_clean();
}
add_action( 'pl8app_purchase_form_login_fields', 'pl8app_get_login_fields' );

/**
 * Renders the payment mode form by getting all the enabled payment gateways and
 * outputting them as radio buttons for the user to choose the payment gateway. If
 * a default payment gateway has been chosen from the pl8app Settings, it will be
 * automatically selected.
 *
 * @since  1.0.0
 * @return void
 */
function pl8app_payment_mode_select() {
	$gateways = pl8app_get_enabled_payment_gateways( true );
	$page_URL = pl8app_get_current_page_url();
	$chosen_gateway = pl8app_get_chosen_gateway();
	?>
	<div id="pl8app_payment_mode_select_wrap">
		<?php do_action('pl8app_payment_mode_top'); ?>
		<?php if( pl8app_is_ajax_disabled() ) { ?>
		<form id="pl8app_payment_mode" action="<?php echo $page_URL; ?>" method="GET">
		<?php } ?>
			<fieldset id="pl8app_payment_mode_select">
				<legend><?php _e( 'Select Payment Method', 'pl8app' ); ?></legend>
				<?php do_action( 'pl8app_payment_mode_before_gateways_wrap' ); ?>
				<div id="pl8app-payment-mode-wrap">
					<?php

					do_action( 'pl8app_payment_mode_before_gateways' );

					foreach ( $gateways as $gateway_id => $gateway ) :

						$label         = apply_filters( 'pl8app_gateway_checkout_label_' . $gateway_id, $gateway['checkout_label'] );
						$checked       = checked( $gateway_id, $chosen_gateway, false );
						$checked_class = $checked ? ' pl8app-gateway-option-selected' : '';

						echo '<label for="pl8app-gateway-' . esc_attr( $gateway_id ) . '" class="pl8app-gateway-option' . $checked_class . '" id="pl8app-gateway-option-' . esc_attr( $gateway_id ) . '">';
							echo '<input type="radio" name="payment-mode" class="pl8app-gateway" id="pl8app-gateway-' . esc_attr( $gateway_id ) . '" value="' . esc_attr( $gateway_id ) . '"' . $checked . '>' . esc_html( $label );
							echo '<div class="control__indicator">';
							echo '</div>';
						echo '</label>';

					endforeach;

					do_action( 'pl8app_payment_mode_after_gateways' );

					?>
				</div>
				<?php do_action( 'pl8app_payment_mode_after_gateways_wrap' ); ?>
			</fieldset>
			<fieldset id="pl8app_payment_mode_submit" class="pl8app-no-js">
				<p id="pl8app-next-submit-wrap">
					<?php echo pl8app_checkout_button_next(); ?>
				</p>
			</fieldset>
		<?php if( pl8app_is_ajax_disabled() ) { ?>
		</form>
		<?php } ?>
	</div>
	<?php do_action('pl8app_after_payment_gateways'); ?>
	<div id="pl8app_purchase_form_wrap"></div><!-- the checkout fields are loaded into this-->

	<?php do_action('pl8app_payment_mode_bottom');
}
add_action( 'pl8app_payment_mode_select', 'pl8app_payment_mode_select' );


/**
 * Show Payment Icons by getting all the accepted icons from the pl8app Settings
 * then outputting the icons.
 *
 * @since 1.0
 * @return void
*/
function pl8app_show_payment_icons() {

	$payment_methods = pl8app_get_option( 'accepted_cards', array() );

	if( empty( $payment_methods ) ) {
		return;
	}

	echo '<fieldset id="pl8app_payment_icons">';
	echo '<legend>'.__('Accepted Cards', 'pl8app').'</legend>';
	echo '<div class="pl8app-payment-icons">';

	foreach( $payment_methods as $key => $card ) {

		if( pl8app_string_is_image_url( $key ) ) {

			echo '<img class="payment-icon" src="' . esc_url( $key ) . '"/>';

		} else {

			$card = strtolower( str_replace( ' ', '', $card ) );

			if( has_filter( 'pl8app_accepted_payment_' . $card . '_image' ) ) {

				$image = apply_filters( 'pl8app_accepted_payment_' . $card . '_image', '' );

			} else {

				$image = pl8app_locate_template( 'images' . DIRECTORY_SEPARATOR . 'icons' . DIRECTORY_SEPARATOR . $card . '.png', false );

				// Replaces backslashes with forward slashes for Windows systems
				$plugin_dir  = wp_normalize_path( WP_PLUGIN_DIR );
				$content_dir = wp_normalize_path( WP_CONTENT_DIR );
				$image       = wp_normalize_path( $image );

				$image = str_replace( $plugin_dir, WP_PLUGIN_URL, $image );
				$image = str_replace( $content_dir, WP_CONTENT_URL, $image );

			}

			if( pl8app_is_ssl_enforced() || is_ssl() ) {

				$image = pl8app_enforced_ssl_asset_filter( $image );

			}

			echo '<img class="payment-icon" src="' . esc_url( $image ) . '"/>';
		}

	}

	echo '</div>';
	echo '</fieldset>';

}
add_action( 'pl8app_after_payment_gateways', 'pl8app_show_payment_icons' );


/**
 * Renders the Discount Code field which allows users to enter a discount code.
 * This field is only displayed if there are any active discounts on the site else
 * it's not displayed.
 *
 * @since  1.0.0
 * @return void
*/
function pl8app_discount_field() {

	if( isset( $_GET['payment-mode'] ) && pl8app_is_ajax_disabled() ) {
		return; // Only show before a payment method has been selected if ajax is disabled
	}

	if( ! pl8app_is_checkout() ) {
		return;
	}

	if ( pl8app_has_active_discounts() && pl8app_get_cart_total() ) :

		$style = pl8app_get_option( 'button_style', 'button' ); ?>

		<fieldset id="pl8app_discount_code">
			<p id="pl8app_show_discount" style="display:none;">
				<?php _e( 'Have a discount code?', 'pl8app' ); ?> <a href="#" class="pl8app_discount_link"><?php echo _x( 'Click to enter it', 'Entering a discount code', 'pl8app' ); ?></a>
			</p>
			<p id="pl8app-discount-code-wrap" class="pl8app-cart-adjustment">
				<label class="pl8app-label" for="pl8app-discount">
					<?php _e( 'Discount', 'pl8app' ); ?>
				</label>
				<span class="pl8app-description"><?php _e( 'Enter a coupon code if you have one.', 'pl8app' ); ?></span>
				<span class="pl8app-discount-code-field-wrap">
					<input class="pl8app-input" type="text" id="pl8app-discount" name="pl8app-discount" placeholder="<?php _e( 'Enter coupon code', 'pl8app' ); ?>"/>
					<input type="submit" class="pl8app-apply-discount pl8app-submit <?php echo $style; ?>" value="<?php echo _x( 'Apply', 'Apply discount at checkout', 'pl8app' ); ?>"/>
				</span>

				<span id="pl8app-discount-error-wrap" class="pl8app_error pl8app-alert pl8app-alert-error" aria-hidden="true" style="display:none;"></span>
			</p>
		</fieldset>

	<?php endif;
}
add_action( 'pl8app_checkout_form_top', 'pl8app_discount_field', -1 );

/**
 * Renders the Checkout Agree to Terms, this displays a checkbox for users to
 * agree the T&Cs set in the pl8app Settings. This is only displayed if T&Cs are
 * set in the pl8app Settings.
 *
 * @since 1.0
 * @return void
 */
function pl8app_terms_agreement() {
	if ( pl8app_get_option( 'show_agree_to_terms' ) ) {
		$agree_text  = pl8app_get_option( 'agree_text' );
        $pattern = '/\{%pl8app_store_contact_information%\}/i';
        $store_information = pla_Shortcodes::pl8app_store_contact_information();
        $agree_text = preg_replace($pattern,$store_information,$agree_text);
		$agree_label = pl8app_get_option( 'agree_label', __( 'Agree to Terms?', 'pl8app' ) );

		ob_start();
	?>
		<fieldset id="pl8app_terms_agreement">
			<div id="pl8app_terms" class="pl8app-terms" style="display:none;">
				<?php
					do_action( 'pl8app_before_terms' );
					echo wpautop( stripslashes( $agree_text ) );
					do_action( 'pl8app_after_terms' );
				?>
			</div>
			<div id="pl8app_show_terms" class="pl8app-show-terms">
				<a href="#" class="pl8app_terms_links"><?php _e( 'Show Terms', 'pl8app' ); ?></a>
				<a href="#" class="pl8app_terms_links" style="display:none;"><?php _e( 'Hide Terms', 'pl8app' ); ?></a>
			</div>

			<div class="pl8app-terms-agreement">
				<input name="pl8app_agree_to_terms" class="required" type="checkbox" id="pl8app_agree_to_terms" value="1"/>
				<label for="pl8app_agree_to_terms"><?php echo stripslashes( $agree_label ); ?></label>
			</div>
		</fieldset>
<?php
		$html_output = ob_get_clean();

		echo apply_filters( 'pl8app_checkout_terms_agreement_html', $html_output );
	}
}
add_action( 'pl8app_purchase_form_before_submit', 'pl8app_terms_agreement' );


/**
 * Shows the final purchase total at the bottom of the checkout page
 *
 * @since 1.0
 * @return void
 */
function pl8app_checkout_final_total() {
?>
<p id="pl8app_final_total_wrap">
	<strong><?php _e( 'Order Total:', 'pl8app' ); ?></strong>
	<span class="pl8app_cart_amount" data-subtotal="<?php echo pl8app_get_cart_subtotal(); ?>" data-total="<?php echo pl8app_get_cart_total(); ?>"><?php pl8app_cart_total(); ?></span>
</p>
<?php
}
add_action( 'pl8app_purchase_form_before_submit', 'pl8app_checkout_final_total', 999 );


/**
 * Renders the Checkout Submit section
 *
 * @since 1.0.0
 * @return void
 */
function pl8app_checkout_submit() {
?>
	<fieldset id="pl8app_purchase_submit">
		<?php do_action( 'pl8app_purchase_form_before_submit' ); ?>

		<?php pl8app_checkout_hidden_fields(); ?>

		<?php echo pl8app_checkout_button_purchase(); ?>

		<?php do_action( 'pl8app_purchase_form_after_submit' ); ?>

		<?php if ( pl8app_is_ajax_disabled() ) { ?>
			<p class="pl8app-cancel"><a href="<?php echo pl8app_get_checkout_uri(); ?>"><?php _e( 'Go back', 'pl8app' ); ?></a></p>
		<?php } ?>
	</fieldset>
<?php
}
add_action( 'pl8app_purchase_form_after_cc_form', 'pl8app_checkout_submit', 9999 );

/**
 * Renders the Next button on the Checkout
 *
 * @since 1.0.0
 * @return string
 */
function pl8app_checkout_button_next() {

	$style = pl8app_get_option( 'button_style', 'button' );
	$purchase_page = pl8app_get_option( 'purchase_page', '0' );

	ob_start(); ?>

	<input type="hidden" name="pl8app_action" value="gateway_select" />
	<input type="hidden" name="page_id" value="<?php echo absint( $purchase_page ); ?>"/>
	<input type="submit" name="gateway_submit" id="pl8app_next_button" class="pl8app-submit <?php echo $style; ?>" value="<?php _e( 'Next', 'pl8app' ); ?>"/>

	<?php
	return apply_filters( 'pl8app_checkout_button_next', ob_get_clean() );
}

/**
 * Renders the Purchase button on the Checkout
 *
 * @since 1.0.0
 * @return string
 */
function pl8app_checkout_button_purchase() {

	$style = pl8app_get_option( 'button_style', 'button' );
	$label = pl8app_get_checkout_button_purchase_label();

	ob_start(); ?>

	<input type="submit" class="pl8app-submit <?php echo $style; ?>" id="pl8app-purchase-button" name="pl8app-purchase" value="<?php echo $label; ?>"/>

	<?php
	return apply_filters( 'pl8app_checkout_button_purchase', ob_get_clean() );
}

/**
 * Retrieves the label for the place order button
 *
 * @since 1.0.0
 * @return string
 */
function pl8app_get_checkout_button_purchase_label() {

	$label             = pl8app_get_option( 'checkout_label', '' );
	$complete_purchase = '';
	if ( pl8app_get_cart_total() ) {
		$complete_purchase = ! empty( $label ) ? $label : __( 'Place Order', 'pl8app' );
	}

	return apply_filters( 'pl8app_get_checkout_button_purchase_label', $complete_purchase, $label );
}

/**
 * Outputs the JavaScript code for the Agree to Terms section to toggle
 * the T&Cs text
 *
 * @since 1.0
 * @return void
 */
function pl8app_agree_to_terms_js() {
	if ( pl8app_get_option( 'show_agree_to_terms', false ) || pl8app_get_option( 'show_agree_to_privacy_policy', false ) ) {
?>
	<script type="text/javascript">
		jQuery(document).ready(function($){
			$( document.body ).on('click', '.pl8app_terms_links', function(e) {
				//e.preventDefault();
				$(this).parent().prev('.pl8app-terms').slideToggle();
				$(this).parent().find('.pl8app_terms_links').toggle();
				return false;
			});
		});
	</script>
<?php
	}
}
add_action( 'pl8app_checkout_form_top', 'pl8app_agree_to_terms_js' );

/**
 * Renders the hidden Checkout fields
 *
 * @since 1.0
 * @return void
 */
function pl8app_checkout_hidden_fields() {
?>
	<?php if ( is_user_logged_in() ) { ?>
	<input type="hidden" name="pl8app-user-id" value="<?php echo get_current_user_id(); ?>"/>
	<?php } ?>
	<input type="hidden" name="pl8app_action" value="purchase"/>
	<input type="hidden" name="pl8app-gateway" value="<?php echo pl8app_get_chosen_gateway(); ?>" />
<?php
}

/**
 * Filter Success Page Content
 *
 * Applies filters to the success page content.
 *
 * @since 1.0
 * @param string $content Content before filters
 * @return string $content Filtered content
 */
function pl8app_filter_success_page_content( $content ) {
	if ( isset( $_GET['payment-confirmation'] ) && pl8app_is_success_page() ) {
		if ( has_filter( 'pl8app_payment_confirm_' . $_GET['payment-confirmation'] ) ) {
			$content = apply_filters( 'pl8app_payment_confirm_' . $_GET['payment-confirmation'], $content );
		}
	}

	return $content;
}
add_filter( 'the_content', 'pl8app_filter_success_page_content', 99999 );
