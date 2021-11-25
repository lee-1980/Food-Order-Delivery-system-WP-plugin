<?php


// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! class_exists( 'pl8app_License' ) ) :

/**
 * pl8app_License Class
 */
class pl8app_License {

	private $file;
	private $license;
	private $item_name;
	private $item_id;
	private $item_shortname;
	private $version;
	private $author;
	private $api_url = 'https://www.pl8app.com';

	/**
	 * Class constructor
	 *
	 * @param string  $_file
	 * @param string  $_item_name
	 * @param string  $_version
	 * @param string  $_author
	 * @param string  $_optname
	 * @param string  $_api_url
	 * @param int     $_item_id
	 */
	function __construct( $_file, $_item_name, $_version, $_author, $_optname = null, $_api_url = null, $_item_id = null ) {

		$this->file = $_file;
		$this->item_name = $_item_name;

		if ( is_numeric( $_item_id ) ) {
			$this->item_id = absint( $_item_id );
		}

		$this->item_shortname = $_optname;
		$this->version        = $_version;
		$this->license        = trim( get_option( $this->item_shortname, '' ) );
		$this->author         = $_author;
		$this->api_url        = is_null( $_api_url ) ? $this->api_url : $_api_url;

		// Setup hooks
		$this->includes();
		$this->hooks();
	}

	/**
	 * Include the updater class
	 *
	 * @access  private
	 * @return  void
	 */
	private function includes() {
		if ( ! class_exists( 'pl8app_Addon_Updater' ) )  {
			require_once 'class-pl8app-addon-updater.php';
		}
	}

	/**
	 * Setup hooks
	 *
	 * @access  private
	 * @return  void
	 */
	private function hooks() {

		// Check that license is valid once per week
		if ( pl8app_doing_cron() ) {
			add_action( 'pl8app_weekly_scheduled_events', array( $this, 'weekly_license_check' ) );
		}

		// For testing license notices, uncomment this line to force checks on every page load
		// add_action( 'admin_init', array( $this, 'weekly_license_check' ) );

		// Addons Updater
		add_action( 'admin_init', array( $this, 'auto_updater' ), 0 );

		// Display notices to admins
		add_action( 'admin_notices', array( $this, 'notices' ) );

		// Display Notice in Plugins page
		add_action( 'in_plugin_update_message-' . plugin_basename( $this->file ), array( $this, 'plugin_row_license_missing' ), 10, 2 );
	}

	/**
	 * Auto updater
	 *
	 * @access  private
	 * @return  void
	 */
	public function auto_updater() {

		$args = array(
			'version'   => $this->version,
			'license'   => $this->license,
			'author'    => $this->author,
		);

		if( ! empty( $this->item_id ) ) {
			$args['item_id']   = $this->item_id;
		} else {
			$args['item_name'] = $this->item_name;
		}

		// Setup the updater
		// $edd_updater = new pl8app_Addon_Updater(
		// 	$this->api_url,
		// 	$this->file,
		// 	$args
		// );
	}

	/**
	 * Admin notices for errors
	 *
	 * @return  void
	 */
	public function notices() {

		static $showed_invalid_message;

		if( empty( $this->license ) ) {
			return;
		}

		if( ! current_user_can( 'manage_shop_settings' ) ) {
			return;
		}

		$messages = array();

		$license = get_option( $this->item_shortname . '_license_data' );

		if( is_object( $license ) && 'valid' !== $license->license && empty( $showed_invalid_message ) ) {

			if( empty( $_GET['page'] ) || 'pl8app-extensions' !== $_GET['page'] ) {

				$messages[] = sprintf(
					__( 'You have invalid or expired license keys for pl8app. Please go to the <a href="%s">Extensions page</a> to correct this issue.', 'pl8app' ),
					admin_url( 'admin.php?page=pl8app-extensions' )
				);
				$showed_invalid_message = true;
			}
		}

		if( ! empty( $messages ) ) {

			foreach( $messages as $message ) {
				echo '<div class="error">';
					echo '<p>' . $message . '</p>';
				echo '</div>';
			}
		}
	}

	/**
	 * Displays message inline on plugin row that the license key is missing
	 *
	 * @since   2.6
	 * @return  void
	 */
	public function plugin_row_license_missing( $plugin_data, $version_info ) {

		static $showed_imissing_key_message;

		$license = get_option( $this->item_shortname . '_license_data' );

		if( ( ! is_object( $license ) || 'valid' !== $license->license ) && empty( $showed_imissing_key_message[ $this->item_shortname ] ) ) {

			echo '&nbsp;<strong><a href="' . esc_url( admin_url( 'admin.php?page=pl8app-extensions' ) ) . '">' . __( 'Enter valid license key for automatic updates.', 'pl8app' ) . '</a></strong>';
			$showed_imissing_key_message[ $this->item_shortname ] = true;
		}
	}

	/**
	 * Check if license key is valid once per week
	 *
	 * @since   2.5
	 * @return  void
	 */
	public function weekly_license_check() {

		if( empty( $this->license ) ) {
			return;
		}

		// data to send in our API request
		$api_params = array(
			'edd_action'=> 'check_license',
			'license' 	=> $this->license,
			'item_name' => urlencode( $this->item_name ),
			'url'       => home_url()
		);

		if ( ! empty( $this->item_id ) ) {
			$api_params['item_id'] = $this->item_id;
		}

		// Call the API
		$response = wp_remote_post(
			$this->api_url,
			array(
				'timeout'   => 15,
				'sslverify' => false,
				'body'      => $api_params
			)
		);

		// make sure the response came back okay
		if ( is_wp_error( $response ) ) {
			return false;
		}

		$license_data = json_decode( wp_remote_retrieve_body( $response ) );

		update_option( $this->item_shortname . '_status', $license_data->license );
		update_option( $this->item_shortname . '_license_data', $license_data );
	}
}

endif; // end class_exists check