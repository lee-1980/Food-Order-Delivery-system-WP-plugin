<?php
/**
 * Admin / Heartbeat
 *
 * @package     pl8app
 * @subpackage  Admin
 * @copyright
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0.0
*/

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * pl8app_Heartbeart Class
 *
 * Hooks into the WP heartbeat API to update various parts of the dashboard as new sales are made
 *
 * Dashboard components that are effect:
 *	- Dashboard Summary Widget
 *
 * @since 1.0.0
 */
class pl8app_Heartbeat {

	/**
	 * Get things started
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public static function init() {

		add_filter( 'heartbeat_received', array( 'pl8app_Heartbeat', 'heartbeat_received' ), 10, 2 );
		add_action( 'admin_enqueue_scripts', array( 'pl8app_Heartbeat', 'enqueue_scripts' ) );
	}

	/**
	 * Tie into the heartbeat and append our stats
	 *
	 * @since 1.0.0
	 * @return array
	 */
	public static function heartbeat_received( $response, $data ) {

		if( ! current_user_can( 'view_shop_reports' ) ) {
			return $response; // Only modify heartbeat if current user can view show reports
		}

		// Make sure we only run our query if the pl8app_heartbeat key is present
		if( ( isset( $data['pl8app_heartbeat'] ) ) && ( $data['pl8app_heartbeat'] == 'dashboard_summary' ) ) {

			// Instantiate the stats class
			$stats = new pl8app_Payment_Stats;

			$earnings = pl8app_get_total_earnings();

			// Send back the number of complete payments
			$response['pl8app-total-payments'] = pl8app_format_amount( pl8app_get_total_sales(), false );
			$response['pl8app-total-earnings'] = html_entity_decode( pl8app_currency_filter( pl8app_format_amount( $earnings ) ), ENT_COMPAT, 'UTF-8' );
			$response['pl8app-payments-month'] = pl8app_format_amount( $stats->get_sales( 0, 'this_month', false, array( 'publish', 'revoked' ) ), false );
			$response['pl8app-earnings-month'] = html_entity_decode( pl8app_currency_filter( pl8app_format_amount( $stats->get_earnings( 0, 'this_month' ) ) ), ENT_COMPAT, 'UTF-8' );
			$response['pl8app-payments-today'] = pl8app_format_amount( $stats->get_sales( 0, 'today', false, array( 'publish', 'revoked' ) ), false );
			$response['pl8app-earnings-today'] = html_entity_decode( pl8app_currency_filter( pl8app_format_amount( $stats->get_earnings( 0, 'today' ) ) ), ENT_COMPAT, 'UTF-8' );

		}

		return $response;

	}

	/**
	 * Load the heartbeat scripts
	 *
	 * @since 1.0.0
	 * @return array
	 */
	public static function enqueue_scripts() {

		if( ! current_user_can( 'view_shop_reports' ) ) {
			return; // Only load heartbeat if current user can view show reports
		}

		// Make sure the JS part of the Heartbeat API is loaded.
		wp_enqueue_script( 'heartbeat' );
		add_action( 'admin_print_footer_scripts', array( 'pl8app_Heartbeat', 'footer_js' ), 20 );
	}

	/**
	 * Inject our JS into the admin footer
	 *
	 * @since 1.0.0
	 * @return array
	 */
	public static function footer_js() {
		global $pagenow;

		// Only proceed if on the dashboard
		if( 'index.php' != $pagenow ) {
			return;
		}

		if( ! current_user_can( 'view_shop_reports' ) ) {
			return; // Only load heartbeat if current user can view show reports
		}

		?>
		<script>
			(function($){
				// Hook into the heartbeat-send
				$(document).on('heartbeat-send', function(e, data) {
					data['pl8app_heartbeat'] = 'dashboard_summary';
				});

				// Listen for the custom event "heartbeat-tick" on $(document).
				$(document).on( 'heartbeat-tick', function(e, data) {

					// Only proceed if our pl8app data is present
					if ( ! data['pl8app-total-payments'] )
						return;

					<?php if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) : ?>
					console.log('tick');
					<?php endif; ?>

					// Update sale count and bold it to provide a highlight
					pl8app_dashboard_heartbeat_update( '.pl8app_dashboard_widget .table_totals .b.b-earnings', data['pl8app-total-earnings'] );
					pl8app_dashboard_heartbeat_update( '.pl8app_dashboard_widget .table_totals .b.b-sales', data['pl8app-total-payments'] );
					pl8app_dashboard_heartbeat_update( '.pl8app_dashboard_widget .table_today .b.b-earnings', data['pl8app-earnings-today'] );
					pl8app_dashboard_heartbeat_update( '.pl8app_dashboard_widget .table_today .b.b-sales', data['pl8app-payments-today'] );
					pl8app_dashboard_heartbeat_update( '.pl8app_dashboard_widget .table_current_month .b-earnings', data['pl8app-earnings-month'] );
					pl8app_dashboard_heartbeat_update( '.pl8app_dashboard_widget .table_current_month .b-sales', data['pl8app-payments-month'] );

					// Return font-weight to normal after 2 seconds
					setTimeout(function(){
						$('.pl8app_dashboard_widget .b.b-sales,.pl8app_dashboard_widget .b.b-earnings').css( 'font-weight', 'normal' );
						$('.pl8app_dashboard_widget .table_current_month .b.b-earnings,.pl8app_dashboard_widget .table_current_month .b.b-sales').css( 'font-weight', 'normal' );
					}, 2000);

				});

				function pl8app_dashboard_heartbeat_update( selector, new_value ) {
					var current_value = $(selector).text();
					$(selector).text( new_value );
					if ( current_value !== new_value ) {
						$(selector).css( 'font-weight', 'bold' );
					}
				}
			}(jQuery));
		</script>
		<?php
	}
}
add_action( 'plugins_loaded', array( 'pl8app_Heartbeat', 'init' ) );
