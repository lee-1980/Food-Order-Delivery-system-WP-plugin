<?php



// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * Plugins row action links
 *
 * @author pl8app
 * @since 1.0
 * @param array $links already defined action links
 * @param string $file plugin file path and name being processed
 * @return array $links
 */
function pl8app_plugin_action_links( $links, $file ) {
	$settings_link = '<a href="' . admin_url( 'admin.php?page=pl8app-settings' ) . '">' . esc_html__( 'General Settings', 'pl8app' ) . '</a>';
	if ( $file == 'pl8app/pl8app.php' )
		array_unshift( $links, $settings_link );

	return $links;
}
add_filter( 'plugin_action_links', 'pl8app_plugin_action_links', 10, 2 );


/**
 * Plugin row meta links
 *
 * @author pl8app
 * @since 1.0
 * @param array $input already defined meta links
 * @param string $file plugin file path and name being processed
 * @return array $input
 */
function pl8app_plugin_row_meta( $input, $file ) {

	if ( $file != 'pl8app/pl8app.php' )
		return $input;

	$support_link = esc_url( add_query_arg( array(), 'https://support.pl8app.co.uk' )
	);


	$links = array(
		'<a href="' . $support_link . '">' . __( 'Support', 'pl8app' ) . '</a>',
	);

	$input = array_merge( $input, $links );

	return $input;
}
add_filter( 'plugin_row_meta', 'pl8app_plugin_row_meta', 10, 2 );
