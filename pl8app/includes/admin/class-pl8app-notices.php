<?php
/**
 * Admin Notices Class
 *
 * @package     pl8app
 * @subpackage  Admin/Notices
 * @copyright
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * pl8app_Notices Class
 *
 * @since 1.0.0
 */
class pl8app_Notices {

	/**
	 * Get things started
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		add_action( 'admin_notices', array( $this, 'show_notices' ) );
		add_action( 'pl8app_dismiss_notices', array( $this, 'dismiss_notices' ) );
	}

	/**
	 * Show relevant notices
	 *
	 * @since 1.0.0
	 */
	public function show_notices() {

		$notices = array(
			'updated' => array(),
			'error'   => array(),
		);

		// Global (non-action-based) messages
		if ( ( pl8app_get_option( 'menu_items_page', '' ) == '' || 'trash' == get_post_status( pl8app_get_option( 'menu_items_page', '' ) ) ) && current_user_can( 'edit_pages' ) && ! get_user_meta( get_current_user_id(), '_pl8app_set_menupage_dismissed' ) ) {
			ob_start();
			?>
			<div class="error">
				<p><?php printf( __( 'No menu items page has been configured. Visit <a href="%s">Settings</a> to set one.', 'pl8app' ), admin_url( 'admin.php?page=pl8app-settings' ) ); ?></p>
				<p><a href="<?php echo esc_url( add_query_arg( array( 'pl8app_action' => 'dismiss_notices', 'pl8app_notice' => 'set_menupage' ) ) ); ?>"><?php _e( 'Dismiss Notice', 'pl8app' ); ?></a></p>
			</div>
			<?php
			echo ob_get_clean();
		}

		if ( ( pl8app_get_option( 'purchase_page', '' ) == '' || 'trash' == get_post_status( pl8app_get_option( 'purchase_page', '' ) ) ) && current_user_can( 'edit_pages' ) && ! get_user_meta( get_current_user_id(), '_pl8app_set_checkout_dismissed' ) ) {
			ob_start();
			?>
			<div class="error">
				<p><?php printf( __( 'No checkout page has been configured. Visit <a href="%s">Settings</a> to set one.', 'pl8app' ), admin_url( 'admin.php?page=pl8app-settings' ) ); ?></p>
				<p><a href="<?php echo esc_url( add_query_arg( array( 'pl8app_action' => 'dismiss_notices', 'pl8app_notice' => 'set_checkout' ) ) ); ?>"><?php _e( 'Dismiss Notice', 'pl8app' ); ?></a></p>
			</div>
			<?php
			echo ob_get_clean();
		}

		if ( isset( $_GET['page'] ) && 'pl8app-payment-history' == $_GET['page'] && current_user_can( 'view_shop_reports' ) && pl8app_is_test_mode() ) {
			$notices['updated']['pl8app-payment-history-test-mode'] = sprintf( __( 'Note: Test Mode is enabled. While in test mode no live transactions are processed. <a href="%s">Settings</a>.', 'pl8app' ), admin_url( 'admin.php?page=pl8app-financial-settings' ) );
		}


		if ( class_exists( 'pl8app_Recount_Earnings' ) && current_user_can( 'manage_shop_settings' ) ) {

			ob_start();
			?>
			<div class="error">
				<p><?php printf( __( 'pl8app 2.5 contains a <a href="%s">built in recount tool</a>. Please <a href="%s">deactivate the pl8app - Recount Earnings plugin</a>', 'pl8app' ), admin_url( 'admin.php?page=pl8app-tools&tab=general' ), admin_url( 'plugins.php' ) ); ?></p>
			</div>
			<?php
			echo ob_get_clean();

		}

		/* Commented out per
		if( ! pl8app_test_ajax_works() && ! get_user_meta( get_current_user_id(), '_pl8app_admin_ajax_inaccessible_dismissed', true ) && current_user_can( 'manage_shop_settings' ) ) {
			echo '<div class="error">';
				echo '<p>' . __( 'Your site appears to be blocking the WordPress ajax interface. This may causes issues with your store.', 'pl8app' ) . '</p>';
				echo '<p>' . sprintf( __( 'Please see <a href="%s" target="_blank">this reference</a> for possible solutions.', 'pl8app' ), '' ) . '</p>';
				echo '<p><a href="' . add_query_arg( array( 'pl8app_action' => 'dismiss_notices', 'pl8app_notice' => 'admin_ajax_inaccessible' ) ) . '">' . __( 'Dismiss Notice', 'pl8app' ) . '</a></p>';
			echo '</div>';
		}
		*/

		if ( isset( $_GET['pl8app-message'] ) ) {
			// Shop discounts errors
			if( current_user_can( 'manage_shop_discounts' ) ) {
				switch( $_GET['pl8app-message'] ) {
					case 'discount_added' :
						$notices['updated']['pl8app-discount-added'] = __( 'Discount code added.', 'pl8app' );
						break;
					case 'discount_add_failed' :
						$notices['error']['pl8app-discount-add-fail'] = __( 'There was a problem adding your discount code, please try again.', 'pl8app' );
						break;
					case 'discount_exists' :
						$notices['error']['pl8app-discount-exists'] = __( 'A discount with that code already exists, please use a different code.', 'pl8app' );
						break;
					case 'discount_updated' :
						$notices['updated']['pl8app-discount-updated'] = __( 'Discount code updated.', 'pl8app' );
						break;
					case 'discount_update_failed' :
						$notices['error']['pl8app-discount-updated-fail'] = __( 'There was a problem updating your discount code, please try again.', 'pl8app' );
						break;
					case 'discount_validation_failed' :
						$notices['error']['pl8app-discount-validation-fail'] = __( 'The discount code could not be added because one or more of the required fields was empty, please try again.', 'pl8app' );
						break;
					case 'discount_invalid_code':
						$notices['error']['pl8app-discount-invalid-code'] = __( 'The discount code entered is invalid; only alphanumeric characters are allowed, please try again.', 'pl8app' );
				}
			}

			// Shop reports errors
			if( current_user_can( 'view_shop_reports' ) ) {
				switch( $_GET['pl8app-message'] ) {
					case 'payment_deleted' :
						$notices['updated']['pl8app-payment-deleted'] = __( 'The payment has been deleted.', 'pl8app' );
						break;
					case 'email_sent' :
						$notices['updated']['pl8app-payment-sent'] = __( 'The order receipt has been resent.', 'pl8app' );
						break;
					case 'refreshed-reports' :
						$notices['updated']['pl8app-refreshed-reports'] = __( 'The reports have been refreshed.', 'pl8app' );
						break;
					case 'payment-note-deleted' :
						$notices['updated']['pl8app-payment-note-deleted'] = __( 'The payment note has been deleted.', 'pl8app' );
						break;
				}
			}

			// Shop settings errors
			if( current_user_can( 'manage_shop_settings' ) ) {
				switch( $_GET['pl8app-message'] ) {
					case 'settings-imported' :
						$notices['updated']['pl8app-settings-imported'] = __( 'The settings have been imported.', 'pl8app' );
						break;
					case 'api-key-generated' :
						$notices['updated']['pl8app-api-key-generated'] = __( 'API keys successfully generated.', 'pl8app' );
						break;
					case 'api-key-exists' :
						$notices['error']['pl8app-api-key-exists'] = __( 'The specified user already has API keys.', 'pl8app' );
						break;
					case 'api-key-regenerated' :
						$notices['updated']['pl8app-api-key-regenerated'] = __( 'API keys successfully regenerated.', 'pl8app' );
						break;
					case 'api-key-revoked' :
						$notices['updated']['pl8app-api-key-revoked'] = __( 'API keys successfully revoked.', 'pl8app' );
						break;
				}
			}

			// Shop payments errors
			if( current_user_can( 'edit_shop_payments' ) ) {
				switch( $_GET['pl8app-message'] ) {
					case 'note-added' :
						$notices['updated']['pl8app-note-added'] = __( 'The payment note has been added successfully.', 'pl8app' );
						break;
					case 'payment-updated' :
						$notices['updated']['pl8app-payment-updated'] = __( 'The order has been successfully updated.', 'pl8app' );
						break;
				}
			}

			// Customer Notices
			if ( current_user_can( 'edit_shop_payments' ) ) {
				switch( $_GET['pl8app-message'] ) {
					case 'customer-deleted' :
						$notices['updated']['pl8app-customer-deleted'] = __( 'Customer successfully deleted', 'pl8app' );
						break;
					case 'user-verified' :
						$notices['updated']['pl8app-user-verified'] = __( 'User successfully verified', 'pl8app' );
						break;
					case 'email-added' :
						$notices['updated']['pl8app-customer-email-added'] = __( 'Customer email added', 'pl8app' );
						break;
					case 'email-removed' :
						$notices['updated']['pl8app-customer-email-removed'] = __( 'Customer email removed', 'pl8app');
						break;
					case 'email-remove-failed' :
						$notices['error']['pl8app-customer-email-remove-failed'] = __( 'Failed to remove customer email', 'pl8app');
						break;
					case 'primary-email-updated' :
						$notices['updated']['pl8app-customer-primary-email-updated'] = __( 'Primary email updated for customer', 'pl8app');
						break;
					case 'primary-email-failed' :
						$notices['error']['pl8app-customer-primary-email-failed'] = __( 'Failed to set primary email', 'pl8app');
						break;
				}
			}

		}

		if ( count( $notices['updated'] ) > 0 ) {
			foreach( $notices['updated'] as $notice => $message ) {
				add_settings_error( 'pl8app-notices', $notice, $message, 'updated' );
			}
		}

		if ( count( $notices['error'] ) > 0 ) {
			foreach( $notices['error'] as $notice => $message ) {
				add_settings_error( 'pl8app-notices', $notice, $message, 'error' );
			}
		}

		settings_errors( 'pl8app-notices' );
	}

	/**
	 * Dismiss admin notices when Dismiss links are clicked
	 *
	 * @since 1.0.0
	 * @return void
	 */
	function dismiss_notices() {
		if( isset( $_GET['pl8app_notice'] ) ) {
			update_user_meta( get_current_user_id(), '_pl8app_' . $_GET['pl8app_notice'] . '_dismissed', 1 );
			wp_redirect( remove_query_arg( array( 'pl8app_action', 'pl8app_notice' ) ) );
			exit;
		}
	}
}
new pl8app_Notices;
