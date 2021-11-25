<?php
/**
 * Contextual Help
 *
 * @package     pl8app
 * @subpackage  Admin/Reports
 * @copyright
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since  1.0.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Reports contextual help.
 *
 * @access      private
 * @since  1.0.0
 * @return      void
 */
function pl8app_reporting_contextual_help() {
	$screen = get_current_screen();

	if ( $screen->id != 'pl8app_page_pl8app-reports' )
		return;

	do_action( 'pl8app_reports_contextual_help', $screen );
}
add_action( 'load-pl8app_page_pl8app-reports', 'pl8app_reporting_contextual_help' );
