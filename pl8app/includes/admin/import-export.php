<?php


// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;



function pl8app_export_import() {
	?>
	<div class="wrap">
		<h2><?php _e( 'Export / Import Settings', 'pl8app' ); ?></h2>
		<div class="metabox-holder">
			<?php do_action( 'pl8app_export_import_top' ); ?>
			<div class="postbox">
				<h3><span><?php _e( 'Export Settings', 'pl8app' ); ?></span></h3>
				<div class="inside">
					<p><?php _e( 'Export the pl8app settings for this site as a .json file. This allows you to easily import the configuration into another site.', 'pl8app' ); ?></p>
					<p><?php printf( __( 'To export shop data (purchases, customers, etc), visit the <a href="%s">Reports</a> page.', 'pl8app' ), admin_url( 'admin.php?page=pl8app-reports&tab=export' ) ); ?>
					<form method="post" action="<?php echo admin_url( 'tools.php?page=pl8app-settings-export-import' ); ?>">
						<p>
							<input type="hidden" name="pl8app_action" value="export_settings" />
						</p>
						<p>
							<?php wp_nonce_field( 'pl8app_export_nonce', 'pl8app_export_nonce' ); ?>
							<?php submit_button( __( 'Export', 'pl8app' ), 'secondary', 'submit', false ); ?>
						</p>
					</form>
				</div><!-- .inside -->
			</div><!-- .postbox -->
			<div class="postbox">
				<h3><span><?php _e( 'Import Settings', 'pl8app' ); ?></span></h3>
				<div class="inside">
					<p><?php _e( 'Import the pl8app settings from a .json file. This file can be obtained by exporting the settings on another site using the form above.', 'pl8app' ); ?></p>
					<form method="post" enctype="multipart/form-data" action="<?php echo admin_url( 'tools.php?page=pl8app-settings-export-import' ); ?>">
						<p>
							<input type="file" name="import_file"/>
						</p>
						<p>
							<input type="hidden" name="pl8app_action" value="import_settings" />
							<?php wp_nonce_field( 'pl8app_import_nonce', 'pl8app_import_nonce' ); ?>
							<?php submit_button( __( 'Import', 'pl8app' ), 'secondary', 'submit', false ); ?>
						</p>
					</form>
				</div><!-- .inside -->
			</div><!-- .postbox -->
			<?php do_action( 'pl8app_export_import_bottom' ); ?>
		</div><!-- .metabox-holder -->
	</div><!-- .wrap -->
	<?php

}


/**
 * Process a settings export that generates a .json file of the shop settings
 *
 * @since  1.0.0
 * @return void
 */
function pl8app_process_settings_export() {

	if( empty( $_POST['pl8app_export_nonce'] ) )
		return;

	if( ! wp_verify_nonce( $_POST['pl8app_export_nonce'], 'pl8app_export_nonce' ) )
		return;

	if( ! current_user_can( 'manage_shop_settings' ) )
		return;

	$settings = array();
	$settings['general']    = get_option( 'pl8app_general' );
	$settings['gateways']   = get_option( 'pl8app_gateways' );
	$settings['emails']     = get_option( 'pl8app_emails' );
	$settings['styles']     = get_option( 'pl8app_styles' );
	$settings['taxes']      = get_option( 'pl8app_taxes' );
	$settings['extensions'] = get_option( 'pl8app_extensions' );
	$settings['misc']       = get_option( 'pl8app_misc' );

	ignore_user_abort( true );

	if ( ! pl8app_is_func_disabled( 'set_time_limit' ) && ! ini_get( 'safe_mode' ) )
		set_time_limit( 0 );

	nocache_headers();
	header( 'Content-Type: application/json; charset=utf-8' );
	header( 'Content-Disposition: attachment; filename=pl8app-settings-export-' . date( 'm-d-Y' ) . '.json' );
	header( "Expires: 0" );

	echo json_encode( $settings );
	exit;

}
add_action( 'pl8app_export_settings', 'pl8app_process_settings_export' );

/**
 * Process a settings import from a json file
 *
 * @since  1.0.0
 * @return void
 */
function pl8app_process_settings_import() {

	if( empty( $_POST['pl8app_import_nonce'] ) )
		return;

	if( ! wp_verify_nonce( $_POST['pl8app_import_nonce'], 'pl8app_import_nonce' ) )
		return;

	if( ! current_user_can( 'manage_shop_settings' ) )
		return;

	$import_file = $_FILES['import_file']['tmp_name'];

	if( empty( $import_file ) ) {
		wp_die( __( 'Please upload a file to import', 'pl8app' ) );
	}

	// Retrieve the settings from the file and convert the json object to an array
	$settings = pl8app_object_to_array( json_decode( file_get_contents( $import_file ) ) );

	update_option( 'pl8app_general'   , $settings['general']    );
	update_option( 'pl8app_gateways'  , $settings['gateways']   );
	update_option( 'pl8app_emails'    , $settings['emails']     );
	update_option( 'pl8app_styles'    , $settings['styles']     );
	update_option( 'pl8app_taxes'     , $settings['taxes']      );
	update_option( 'pl8app_extensions', $settings['extensions'] );
	update_option( 'pl8app_misc'      , $settings['misc']       );

	wp_safe_redirect( admin_url( 'tools.php?page=pl8app-settings-export-import&pl8app-message=settings-imported' ) ); exit;

}
add_action( 'pl8app_import_settings', 'pl8app_process_settings_import' );