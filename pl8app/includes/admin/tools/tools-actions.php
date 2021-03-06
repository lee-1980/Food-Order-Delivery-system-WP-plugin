<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


/**
 * Register the recount batch processor
 * @since  1.0.0
 */
function pl8app_register_batch_recount_store_earnings_tool() {
	add_action( 'pl8app_batch_export_class_include', 'pl8app_include_recount_store_earnings_tool_batch_processer', 10, 1 );
}
add_action( 'pl8app_register_batch_exporter', 'pl8app_register_batch_recount_store_earnings_tool', 10 );

/**
 * Loads the tools batch processing class for recounting store earnings
 *
 * @since  1.0.0
 * @param  string $class The class being requested to run for the batch export
 * @return void
 */
function pl8app_include_recount_store_earnings_tool_batch_processer( $class ) {

	if ( 'pl8app_Tools_Recount_Store_Earnings' === $class ) {
		require_once PL8_PLUGIN_DIR . 'includes/admin/tools/class-pl8app-tools-recount-store-earnings.php';
	}

}

/**
 * Register the recount menuitem batch processor
 * @since  1.0.0
 */
function pl8app_register_batch_recount_menuitem_tool() {
	add_action( 'pl8app_batch_export_class_include', 'pl8app_include_recount_menuitem_tool_batch_processer', 10, 1 );
}
add_action( 'pl8app_register_batch_exporter', 'pl8app_register_batch_recount_menuitem_tool', 10 );

/**
 * Loads the tools batch processing class for recounting menuitem stats
 *
 * @since  1.0.0
 * @param  string $class The class being requested to run for the batch export
 * @return void
 */
function pl8app_include_recount_menuitem_tool_batch_processer( $class ) {

	if ( 'pl8app_Tools_Recount_Download_Stats' === $class ) {
		require_once PL8_PLUGIN_DIR . 'includes/admin/tools/class-pl8app-tools-recount-download-stats.php';
	}

}

/**
 * Register the recount all stats batch processor
 * @since  1.0.0
 */
function pl8app_register_batch_recount_all_tool() {
	add_action( 'pl8app_batch_export_class_include', 'pl8app_include_recount_all_tool_batch_processer', 10, 1 );
}
add_action( 'pl8app_register_batch_exporter', 'pl8app_register_batch_recount_all_tool', 10 );

/**
 * Loads the tools batch processing class for recounting all stats
 *
 * @since  1.0.0
 * @param  string $class The class being requested to run for the batch export
 * @return void
 */
function pl8app_include_recount_all_tool_batch_processer( $class ) {

	if ( 'pl8app_Tools_Recount_All_Stats' === $class ) {
		require_once PL8_PLUGIN_DIR . 'includes/admin/tools/class-pl8app-tools-recount-all-stats.php';
	}

}

/**
 * Register the reset stats batch processor
 * @since  1.0.0
 */
function pl8app_register_batch_reset_tool() {
	add_action( 'pl8app_batch_export_class_include', 'pl8app_include_reset_tool_batch_processer', 10, 1 );
}
add_action( 'pl8app_register_batch_exporter', 'pl8app_register_batch_reset_tool', 10 );

/**
 * Loads the tools batch processing class for resetting store and product earnings
 *
 * @since  1.0.0
 * @param  string $class The class being requested to run for the batch export
 * @return void
 */
function pl8app_include_reset_tool_batch_processer( $class ) {

	if ( 'pl8app_Tools_Reset_Stats' === $class ) {
		require_once PL8_PLUGIN_DIR . 'includes/admin/tools/class-pl8app-tools-reset-stats.php';
	}

}

/**
 * Register the reset customer stats batch processor
 * @since  1.0.0
 */
function pl8app_register_batch_customer_recount_tool() {
	add_action( 'pl8app_batch_export_class_include', 'pl8app_include_customer_recount_tool_batch_processer', 10, 1 );
}
add_action( 'pl8app_register_batch_exporter', 'pl8app_register_batch_customer_recount_tool', 10 );

/**
 * Loads the tools batch processing class for resetting all customer stats
 *
 * @since  1.0.0
 * @param  string $class The class being requested to run for the batch export
 * @return void
 */
function pl8app_include_customer_recount_tool_batch_processer( $class ) {

	if ( 'pl8app_Tools_Recount_Customer_Stats' === $class ) {
		require_once PL8_PLUGIN_DIR . 'includes/admin/tools/class-pl8app-tools-recount-customer-stats.php';
	}

}
