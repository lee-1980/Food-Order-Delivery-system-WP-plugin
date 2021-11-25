<?php

if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * Add a hook allowing extensions to register a hook on the batch export process
 *
 * @since  1.0.0
 * @return void
 */
function pl8app_register_batch_importers() {
	if ( is_admin() ) {
		do_action( 'pl8app_register_batch_importer' );
	}
}
add_action( 'plugins_loaded', 'pl8app_register_batch_importers' );

/**
 * Register the payments batch importer
 *
 * @since  1.0.0
 */
function pl8app_register_payments_batch_import() {
	add_action( 'pl8app_batch_import_class_include', 'pl8app_include_payments_batch_import_processer', 10 );
}
add_action( 'pl8app_register_batch_importer', 'pl8app_register_payments_batch_import', 10 );

/**
 * Loads the payments batch process if needed
 *
 * @since  1.0.0
 * @param  string $class The class being requested to run for the batch import
 * @return void
 */
function pl8app_include_payments_batch_import_processer( $class ) {

	if ( 'pl8app_Batch_Payments_Import' === $class ) {
		require_once PL8_PLUGIN_DIR . 'includes/admin/import/class-batch-import-payments.php';
	}

}

/**
 * Register the menuitems batch importer
 *
 * @since  1.0.0
 */
function pl8app_register_menuitems_batch_import() {
	add_action( 'pl8app_batch_import_class_include', 'pl8app_include_menuitems_batch_import_processer', 10 );
}
add_action( 'pl8app_register_batch_importer', 'pl8app_register_menuitems_batch_import', 10 );

/**
 * Loads the menuitems batch process if needed
 *
 * @since  1.0.0
 * @param  string $class The class being requested to run for the batch import
 * @return void
 */
function pl8app_include_menuitems_batch_import_processer( $class ) {

	if ( 'pl8app_Batch_MenuItems_Import' === $class || 'pl8app_Batch_Categories_Import' === $class || 'pl8app_Batch_Addons_Import' === $class ) {
		require_once PL8_PLUGIN_DIR . 'includes/admin/import/class-batch-import-menuitems.php';
	}

}