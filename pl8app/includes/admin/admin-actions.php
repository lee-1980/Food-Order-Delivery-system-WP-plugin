<?php


// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Processes all pl8app actions sent via POST and GET by looking for the 'pl8app-action'
 * request and running do_action() to call the function
 *
 * @since 1.0.0
 * @return void
 */
function pl8app_process_actions() {
	if ( isset( $_POST['pl8app-action'] ) ) {
		do_action( 'pl8app_' . $_POST['pl8app-action'], $_POST );
	}

	if ( isset( $_GET['pl8app-action'] ) ) {
		do_action( 'pl8app_' . $_GET['pl8app-action'], $_GET );
	}
}
add_action( 'admin_init', 'pl8app_process_actions' );


