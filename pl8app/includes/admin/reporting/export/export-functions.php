<?php


// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

require_once PL8_PLUGIN_DIR . 'includes/admin/reporting/class-export.php';
require_once PL8_PLUGIN_DIR . 'includes/admin/reporting/export/export-actions.php';

/**
 * Process batch exports via ajax
 *
 * @since 2.4
 * @return void
 */
function pl8app_do_ajax_export() {

	require_once PL8_PLUGIN_DIR . 'includes/admin/reporting/export/class-batch-export.php';

	parse_str( $_POST['form'], $form );

	$_REQUEST = $form = (array) $form;


	if( ! wp_verify_nonce( $_REQUEST['pl8app_ajax_export'], 'pl8app_ajax_export' ) ) {
		die( '-2' );
	}

	do_action( 'pl8app_batch_export_class_include', $form['pl8app-export-class'] );

	$step     = absint( $_POST['step'] );
	$class    = sanitize_text_field( $form['pl8app-export-class'] );
	$export   = new $class( $step );

	if( ! $export->can_export() ) {
		die( '-1' );
	}

	if ( ! $export->is_writable ) {
		echo json_encode( array( 'error' => true, 'message' => __( 'Export location or file not writable', 'pl8app' ) ) ); exit;
	}

	$export->set_properties( $_REQUEST );

	// Added in 2.5 to allow a bulk processor to pre-fetch some data to speed up the remaining steps and cache data
	$export->pre_fetch();

	$ret = $export->process_step( $step );

	$percentage = $export->get_percentage_complete();

	if( $ret ) {

		$step += 1;
		echo json_encode( array( 'step' => $step, 'percentage' => $percentage ) ); exit;

	} elseif ( true === $export->is_empty ) {

		echo json_encode( array( 'error' => true, 'message' => __( 'No data found for export parameters', 'pl8app' ) ) ); exit;

	} elseif ( true === $export->done && true === $export->is_void ) {

		$message = ! empty( $export->message ) ? $export->message : __( 'Batch Processing Complete', 'pl8app' );
		echo json_encode( array( 'success' => true, 'message' => $message ) ); exit;

	} else {

		$args = array_merge( $_REQUEST, array(
			'step'       => $step,
			'class'      => $class,
			'nonce'      => wp_create_nonce( 'pl8app-batch-export' ),
			'pl8app_action' => 'menuitem_batch_export',
		) );

		$menuitem_url = add_query_arg( $args, admin_url() );

		echo json_encode( array( 'step' => 'done', 'url' => $menuitem_url ) ); exit;

	}
}
add_action( 'wp_ajax_pl8app_do_ajax_export', 'pl8app_do_ajax_export' );
