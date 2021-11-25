<?php


// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Tools
 *
 * Shows the tools panel which contains pl8app-specific tools including the
 * built-in import/export system.
 *
 * @since 1.0
 * @author      pl8app
 * @return      void
 */
function pl8app_tools_page() {
	$active_tab = isset( $_GET['tab'] ) ? $_GET['tab'] : 'general';
?>
	<div class="wrap">
		<h2><?php _e( 'pl8app Info and Tools', 'pl8app' ); ?></h2>
		<div class="metabox-holder">
			<?php
			do_action( 'pl8app_tools_tab_' . $active_tab );
			?>
		</div><!-- .metabox-holder -->
	</div><!-- .wrap -->
<?php
}


/**
 * Retrieve tools tabs
 *
 * @since       2.0
 * @return      array
 */
function pl8app_get_tools_tabs() {

	$tabs                  = array();
	$tabs['general']       = __( 'General', 'pl8app' );

	if( count( pl8app_get_beta_enabled_extensions() ) > 0 ) {
		$tabs['betas'] = __( 'Beta Versions', 'pl8app' );
	}

	$tabs['system_info']   = __( 'System Info', 'pl8app' );

	if( pl8app_is_debug_mode() ) {
		$tabs['debug_log'] = __( 'Debug Log', 'pl8app' );
	}

	$tabs['import_export'] = __( 'Import/Export', 'pl8app' );

	return apply_filters( 'pl8app_tools_tabs', $tabs );
}


/**
 * Display the recount stats
 *
 * @since 1.0
 * @return      void
 */
function pl8app_tools_recount_stats_display() {

	if( ! current_user_can( 'manage_shop_settings' ) ) {
		return;
	}

	do_action( 'pl8app_tools_recount_stats_before' );
?>
	<div class="postbox">
		<h3><span><?php _e( 'Recount Stats', 'pl8app' ); ?></span></h3>
		<div class="inside recount-stats-controls">
			<p><?php _e( 'Use these tools to recount / reset store stats.', 'pl8app' ); ?></p>
			<form method="post" id="pl8app-tools-recount-form" class="pl8app-export-form pl8app-import-export-form">
				<span>

					<?php wp_nonce_field( 'pl8app_ajax_export', 'pl8app_ajax_export' ); ?>

					<select name="pl8app-export-class" id="recount-stats-type">
						<option value="0" selected="selected" disabled="disabled"><?php _e( 'Please select an option', 'pl8app' ); ?></option>
						<option data-type="recount-store" value="pl8app_Tools_Recount_Store_Earnings"><?php _e( 'Recount Store Earnings and Sales', 'pl8app' ); ?></option>
						<option data-type="recount-menuitem" value="pl8app_Tools_Recount_Download_Stats"><?php printf( __( 'Recount Earnings and Sales for a %s', 'pl8app' ), pl8app_get_label_singular( true ) ); ?></option>
						<option data-type="recount-all" value="pl8app_Tools_Recount_All_Stats"><?php printf( __( 'Recount Earnings and Sales for All %s', 'pl8app' ), pl8app_get_label_plural( true ) ); ?></option>
						<option data-type="recount-customer-stats" value="pl8app_Tools_Recount_Customer_Stats"><?php _e( 'Recount Customer Stats', 'pl8app' ); ?></option>
						<?php do_action( 'pl8app_recount_tool_options' ); ?>
						<option data-type="reset-stats" value="pl8app_Tools_Reset_Stats"><?php _e( 'Reset Store', 'pl8app' ); ?></option>
					</select>

					<span id="tools-product-dropdown" style="display: none">
						<?php
							$args = array(
								'name'   => 'menuitem_id',
								'number' => -1,
								'chosen' => true,
							);
							echo PL8PRESS()->html->product_dropdown( $args );
						?>
					</span>

					<input type="submit" id="recount-stats-submit" value="<?php _e( 'Submit', 'pl8app' ); ?>" class="button-secondary"/>

					<br />

					<span class="pl8app-recount-stats-descriptions">
						<span id="recount-store"><?php _e( 'Recalculates the total store earnings and sales.', 'pl8app' ); ?></span>
						<span id="recount-menuitem"><?php printf( __( 'Recalculates the earnings and sales stats for a specific %s.', 'pl8app' ), pl8app_get_label_singular( true ) ); ?></span>
						<span id="recount-all"><?php printf( __( 'Recalculates the earnings and sales stats for all %s.', 'pl8app' ), pl8app_get_label_plural( true ) ); ?></span>
						<span id="recount-customer-stats"><?php _e( 'Recalculates the lifetime value and purchase counts for all customers.', 'pl8app' ); ?></span>
						<?php do_action( 'pl8app_recount_tool_descriptions' ); ?>
						<span id="reset-stats"><?php _e( '<strong>Deletes</strong> all payment records, customers, and related log entries.', 'pl8app' ); ?></span>
					</span>

					<span class="spinner"></span>

				</span>
			</form>
			<?php do_action( 'pl8app_tools_recount_forms' ); ?>
		</div><!-- .inside -->
	</div><!-- .postbox -->
<?php
	do_action( 'pl8app_tools_recount_stats_after' );
}
add_action( 'pl8app_tools_tab_general', 'pl8app_tools_recount_stats_display' );

/**
 * Display the clear upgrades tab
 *
 * @since       2.3.5
 * @return      void
 */
function pl8app_tools_clear_doing_upgrade_display() {

	if( ! current_user_can( 'manage_shop_settings' ) || false === get_option( 'pl8app_doing_upgrade' ) ) {
		return;
	}

	do_action( 'pl8app_tools_clear_doing_upgrade_before' );
?>
	<div class="postbox">
		<h3><span><?php _e( 'Clear Incomplete Upgrade Notice', 'pl8app' ); ?></span></h3>
		<div class="inside">
			<p><?php _e( 'Sometimes a database upgrade notice may not be cleared after an upgrade is completed due to conflicts with other extensions or other minor issues.', 'pl8app' ); ?></p>
			<p><?php _e( 'If you\'re certain these upgrades have been completed, you can clear these upgrade notices by clicking the button below. If you have any questions about this, please contact the pl8app support team and we\'ll be happy to help.', 'pl8app' ); ?></p>
			<form method="post" action="<?php echo admin_url( 'admin.php?page=pl8app-tools&tab=general' ); ?>">
				<p>
					<input type="hidden" name="pl8app_action" value="clear_doing_upgrade" />
					<?php wp_nonce_field( 'pl8app_clear_upgrades_nonce', 'pl8app_clear_upgrades_nonce' ); ?>
					<?php submit_button( __( 'Clear Incomplete Upgrade Notice', 'pl8app' ), 'secondary', 'submit', false ); ?>
				</p>
			</form>
		</div><!-- .inside -->
	</div><!-- .postbox -->
<?php
	do_action( 'pl8app_tools_clear_doing_upgrade_after' );
}
add_action( 'pl8app_tools_tab_general', 'pl8app_tools_clear_doing_upgrade_display' );



/**
 * Display beta opt-ins
 *
 * @since 1.01
 * @return      void
 */
function pl8app_tools_betas_display() {
	if( ! current_user_can( 'manage_shop_settings' ) ) {
		return;
	}

	$has_beta = pl8app_get_beta_enabled_extensions();

	do_action( 'pl8app_tools_betas_before' );
	?>

	<div class="postbox pl8app-beta-support">
		<h3><span><?php _e( 'Enable Beta Versions', 'pl8app' ); ?></span></h3>
		<div class="inside">
			<p><?php _e( 'Checking any of the below checkboxes will opt you in to receive pre-release update notifications. You can opt-out at any time. Pre-release updates do not install automatically, you will still have the opportunity to ignore update notifications.', 'pl8app' ); ?></p>
			<form method="post" action="<?php echo admin_url( 'admin.php?page=pl8app-tools&tab=betas' ); ?>">
				<table class="form-table pl8app-beta-support">
					<tbody>
						<?php foreach( $has_beta as $slug => $product ) : ?>
							<tr>
								<?php $checked = pl8app_extension_has_beta_support( $slug ); ?>
								<th scope="row"><?php echo esc_html( $product ); ?></th>
								<td>
									<input type="checkbox" name="enabled_betas[<?php echo esc_attr( $slug ); ?>]" id="enabled_betas[<?php echo esc_attr( $slug ); ?>]"<?php echo checked( $checked, true, false ); ?> value="1" />
									<label for="enabled_betas[<?php echo esc_attr( $slug ); ?>]"><?php printf( __( 'Get updates for pre-release versions of %s', 'pl8app' ), $product ); ?></label>
								</td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
				<input type="hidden" name="pl8app_action" value="save_enabled_betas" />
				<?php wp_nonce_field( 'pl8app_save_betas_nonce', 'pl8app_save_betas_nonce' ); ?>
				<?php submit_button( __( 'Save', 'pl8app' ), 'secondary', 'submit', false ); ?>
			</form>
		</div>
	</div>

	<?php
	do_action( 'pl8app_tools_betas_after' );
}
add_action( 'pl8app_tools_tab_betas', 'pl8app_tools_betas_display' );


/**
 * Return an array of all extensions with beta support
 *
 * Extensions should be added as 'extension-slug' => 'Extension Name'
 *
 * @since 1.01
 * @return      array $extensions The array of extensions
 */
function pl8app_get_beta_enabled_extensions() {
	return apply_filters( 'pl8app_beta_enabled_extensions', array() );
}


/**
 * Check if a given extensions has beta support enabled
 *
 * @since 1.01
 * @param       string $slug The slug of the extension to check
 * @return      bool True if enabled, false otherwise
 */
function pl8app_extension_has_beta_support( $slug ) {
	$enabled_betas = pl8app_get_option( 'enabled_betas', array() );
	$return        = false;

	if( array_key_exists( $slug, $enabled_betas ) ) {
		$return = true;
	}

	return $return;
}


/**
 * Save enabled betas
 *
 * @since 1.01
 * @return      void
 */
function pl8app_tools_enabled_betas_save() {
	if( ! wp_verify_nonce( $_POST['pl8app_save_betas_nonce'], 'pl8app_save_betas_nonce' ) ) {
		return;
	}

	if( ! current_user_can( 'manage_shop_settings' ) ) {
		return;
	}

	if( ! empty( $_POST['enabled_betas'] ) ) {
		$enabled_betas = array_filter( array_map( 'pl8app_tools_enabled_betas_sanitize_value', $_POST['enabled_betas'] ) );
		pl8app_update_option( 'enabled_betas', $enabled_betas );
	} else {
		pl8app_delete_option( 'enabled_betas' );
	}
}
add_action( 'pl8app_save_enabled_betas', 'pl8app_tools_enabled_betas_save' );

/**
 * Sanitize the supported beta values by making them booleans
 *
 * @since 1.0.0.11
 * @param mixed $value The value being sent in, determining if beta support is enabled.
 *
 * @return bool
 */
function pl8app_tools_enabled_betas_sanitize_value( $value ) {
	return filter_var( $value, FILTER_VALIDATE_BOOLEAN );
}


/**
 * Save banned emails
 *
 * @since       2.0
 * @return      void
 */
function pl8app_tools_banned_emails_save() {

	if( ! wp_verify_nonce( $_POST['pl8app_banned_emails_nonce'], 'pl8app_banned_emails_nonce' ) ) {
		return;
	}

	if( ! current_user_can( 'manage_shop_settings' ) ) {
		return;
	}

	if( ! empty( $_POST['banned_emails'] ) ) {

		// Sanitize the input
		$emails = array_map( 'trim', explode( "\n", $_POST['banned_emails'] ) );
		$emails = array_unique( $emails );
		$emails = array_map( 'sanitize_text_field', $emails );

		foreach( $emails as $id => $email ) {
			if( ! is_email( $email ) && $email[0] != '@' && $email[0] != '.' ) {
				unset( $emails[$id] );
			}
		}
	} else {
		$emails = '';
	}

	$noshow_limit = isset($_POST['customer_no_show_limit'])?$_POST['customer_no_show_limit']:0;

	pl8app_update_option( 'banned_emails', $emails );
	pl8app_update_option('noshow_limit', $noshow_limit);
}
add_action( 'pl8app_save_banned_emails', 'pl8app_tools_banned_emails_save' );

/**
 * Save reCaptcha Keys
 */


function pl8app_tools_save_recaptcha_key(){

    if( ! wp_verify_nonce( $_POST['pl8app_recaptcha_nonce'], 'pl8app_recaptcha_nonce' ) ) {
        return;
    }

    if( ! current_user_can( 'manage_shop_settings' ) ) {
        return;
    }

    $recaptcha_site_key = isset($_POST['sitekey'])?$_POST['sitekey']:0;
    $recaptcha_secret_key = isset($_POST['secret'])?$_POST['secret']:0;
    $recaptcha_recap_enable = isset($_POST['recap_enable'])?$_POST['recap_enable']:false;

    pl8app_update_option( 'recap_site_key', $recaptcha_site_key );
    pl8app_update_option('recap_secret_key', $recaptcha_secret_key);
    pl8app_update_option('recap_enable', $recaptcha_recap_enable);
}

add_action( 'pl8app_save_recaptcha_key', 'pl8app_tools_save_recaptcha_key');
/**
 * Execute upgrade notice clear
 *
 * @since       2.3.5
 * @return      void
 */
function pl8app_tools_clear_upgrade_notice() {
	if( ! wp_verify_nonce( $_POST['pl8app_clear_upgrades_nonce'], 'pl8app_clear_upgrades_nonce' ) ) {
		return;
	}

	if( ! current_user_can( 'manage_shop_settings' ) ) {
		return;
	}

	delete_option( 'pl8app_doing_upgrade' );
}
add_action( 'pl8app_clear_doing_upgrade', 'pl8app_tools_clear_upgrade_notice' );


/**
 * Display the tools import/export tab
 *
 * @since       2.0
 * @return      void
 */
function pl8app_tools_import_export_display() {

	if( ! current_user_can( 'manage_shop_settings' ) ) {
		return;
	}

	do_action( 'pl8app_tools_import_export_before' ); ?>

	<div class="postbox pl8app-import-payment-history">
		<h3><span><?php _e( 'Import Payment History', 'pl8app' ); ?></span></h3>
		<div class="inside">
			<p><?php _e( 'Import a CSV file of payment records.', 'pl8app' ); ?></p>
			<form id="pl8app-import-payments" class="pl8app-import-form pl8app-import-export-form" action="<?php echo esc_url( add_query_arg( 'pl8app_action', 'upload_import_file', admin_url() ) ); ?>" method="post" enctype="multipart/form-data">

				<div class="pl8app-import-file-wrap">
					<?php wp_nonce_field( 'pl8app_ajax_import', 'pl8app_ajax_import' ); ?>
					<input type="hidden" name="pl8app-import-class" value="pl8app_Batch_Payments_Import"/>
					<p>
						<input name="pl8app-import-file" id="pl8app-payments-import-file" type="file" />
					</p>
					<span>
						<input type="submit" value="<?php _e( 'Import CSV', 'pl8app' ); ?>" class="button-secondary"/>
						<span class="spinner"></span>
					</span>
				</div>

				<div class="pl8app-import-options" id="pl8app-import-payments-options" style="display:none;">

					<p>
						<?php
						printf(
							__( 'Each column loaded from the CSV needs to be mapped to a payment field. Select the column that should be mapped to each field below. Any columns not needed can be ignored.', 'pl8app' )
						);
						?>
					</p>

					<table class="widefat pl8app_repeatable_table striped" width="100%" cellpadding="0" cellspacing="0">
						<thead>
							<tr>
								<th><strong><?php _e( 'Payment Field', 'pl8app' ); ?></strong></th>
								<th><strong><?php _e( 'CSV Column', 'pl8app' ); ?></strong></th>
								<th><strong><?php _e( 'Data Preview', 'pl8app' ); ?></strong></th>
							</tr>
						</thead>
						<tbody>
							<tr>
								<td><?php _e( 'Currency Code', 'pl8app' ); ?></td>
								<td>
									<select name="pl8app-import-field[currency]" class="pl8app-import-csv-column" data-field="Currency">
										<option value=""><?php _e( '- Ignore this field -', 'pl8app' ); ?></option>
									</select>
								</td>
								<td class="pl8app-import-preview-field"><?php _e( '- select field to preview data -', 'pl8app' ); ?></td>
							</tr>
							<tr>
								<td><?php _e( 'Email', 'pl8app' ); ?></td>
								<td>
									<select name="pl8app-import-field[email]" class="pl8app-import-csv-column" data-field="Email">
										<option value=""><?php _e( '- Ignore this field -', 'pl8app' ); ?></option>
									</select>
								</td>
								<td class="pl8app-import-preview-field"><?php _e( '- select field to preview data -', 'pl8app' ); ?></td>
							</tr>
							<tr>
								<td><?php _e( 'First Name', 'pl8app' ); ?></td>
								<td>
									<select name="pl8app-import-field[first_name]" class="pl8app-import-csv-column" data-field="First Name">
										<option value=""><?php _e( '- Ignore this field -', 'pl8app' ); ?></option>
									</select>
								</td>
								<td class="pl8app-import-preview-field"><?php _e( '- select field to preview data -', 'pl8app' ); ?></td>
							</tr>
							<tr>
								<td><?php _e( 'Last Name', 'pl8app' ); ?></td>
								<td>
									<select name="pl8app-import-field[last_name]" class="pl8app-import-csv-column" data-field="Last Name">
										<option value=""><?php _e( '- Ignore this field -', 'pl8app' ); ?></option>
									</select>
								</td>
								<td class="pl8app-import-preview-field"><?php _e( '- select field to preview data -', 'pl8app' ); ?></td>
							</tr>
							<tr>
								<td><?php _e( 'Customer ID', 'pl8app' ); ?></td>
								<td>
									<select name="pl8app-import-field[customer_id]" class="pl8app-import-csv-column" data-field="Customer ID">
										<option value=""><?php _e( '- Ignore this field -', 'pl8app' ); ?></option>
									</select>
								</td>
								<td class="pl8app-import-preview-field"><?php _e( '- select field to preview data -', 'pl8app' ); ?></td>
							</tr>
							<tr>
								<td><?php _e( 'Discount Code(s)', 'pl8app' ); ?></td>
								<td>
									<select name="pl8app-import-field[discounts]" class="pl8app-import-csv-column" data-field="Discount Code">
										<option value=""><?php _e( '- Ignore this field -', 'pl8app' ); ?></option>
									</select>
								</td>
								<td class="pl8app-import-preview-field"><?php _e( '- select field to preview data -', 'pl8app' ); ?></td>
							</tr>
							<tr>
								<td><?php _e( 'IP Address', 'pl8app' ); ?></td>
								<td>
									<select name="pl8app-import-field[ip]" class="pl8app-import-csv-column" data-field="IP Address">
										<option value=""><?php _e( '- Ignore this field -', 'pl8app' ); ?></option>
									</select>
								</td>
								<td class="pl8app-import-preview-field"><?php _e( '- select field to preview data -', 'pl8app' ); ?></td>
							</tr>
							<tr>
								<td><?php _e( 'Mode (Live|Test)', 'pl8app' ); ?></td>
								<td>
									<select name="pl8app-import-field[mode]" class="pl8app-import-csv-column" data-field="Mode (Live|Test)">
										<option value=""><?php _e( '- Ignore this field -', 'pl8app' ); ?></option>
									</select>
								</td>
								<td class="pl8app-import-preview-field"><?php _e( '- select field to preview data -', 'pl8app' ); ?></td>
							</tr>
							<tr>
								<td><?php _e( 'Parent Payment ID', 'pl8app' ); ?></td>
								<td>
									<select name="pl8app-import-field[parent_payment_id]" class="pl8app-import-csv-column" data-field="">
										<option value=""><?php _e( '- Ignore this field -', 'pl8app' ); ?></option>
									</select>
								</td>
								<td class="pl8app-import-preview-field"><?php _e( '- select field to preview data -', 'pl8app' ); ?></td>
							</tr>
							<tr>
								<td><?php _e( 'Payment Method', 'pl8app' ); ?></td>
								<td>
									<select name="pl8app-import-field[gateway]" class="pl8app-import-csv-column" data-field="Payment Method">
										<option value=""><?php _e( '- Ignore this field -', 'pl8app' ); ?></option>
									</select>
								</td>
								<td class="pl8app-import-preview-field"><?php _e( '- select field to preview data -', 'pl8app' ); ?></td>
							</tr>
							<tr>
								<td><?php _e( 'Payment Number', 'pl8app' ); ?></td>
								<td>
									<select name="pl8app-import-field[number]" class="pl8app-import-csv-column" data-field="Payment Number">
										<option value=""><?php _e( '- Ignore this field -', 'pl8app' ); ?></option>
									</select>
								</td>
								<td class="pl8app-import-preview-field"><?php _e( '- select field to preview data -', 'pl8app' ); ?></td>
							</tr>
							<tr>
								<td><?php _e( 'Date', 'pl8app' ); ?></td>
								<td>
									<select name="pl8app-import-field[date]" class="pl8app-import-csv-column" data-field="Date">
										<option value=""><?php _e( '- Ignore this field -', 'pl8app' ); ?></option>
									</select>
								</td>
								<td class="pl8app-import-preview-field"><?php _e( '- select field to preview data -', 'pl8app' ); ?></td>
							</tr>
							<tr>
								<td><?php _e( 'Purchase Key', 'pl8app' ); ?></td>
								<td>
									<select name="pl8app-import-field[key]" class="pl8app-import-csv-column" data-field="Purchase Key">
										<option value=""><?php _e( '- Ignore this field -', 'pl8app' ); ?></option>
									</select>
								</td>
								<td class="pl8app-import-preview-field"><?php _e( '- select field to preview data -', 'pl8app' ); ?></td>
							</tr>
							<tr>
								<td><?php _e( 'Purchased Product(s)', 'pl8app' ); ?></td>
								<td>
									<select name="pl8app-import-field[menuitems]" class="pl8app-import-csv-column" data-field="Products (Raw)">
										<option value=""><?php _e( '- Ignore this field -', 'pl8app' ); ?></option>
									</select>
								</td>
								<td class="pl8app-import-preview-field"><?php _e( '- select field to preview data -', 'pl8app' ); ?></td>
							</tr>
							<tr>
								<td><?php _e( 'Status', 'pl8app' ); ?></td>
								<td>
									<select name="pl8app-import-field[status]" class="pl8app-import-csv-column" data-field="Status">
										<option value=""><?php _e( '- Ignore this field -', 'pl8app' ); ?></option>
									</select>
								</td>
								<td class="pl8app-import-preview-field"><?php _e( '- select field to preview data -', 'pl8app' ); ?></td>
							</tr>
							<tr>
								<td><?php _e( 'Subtotal', 'pl8app' ); ?></td>
								<td>
									<select name="pl8app-import-field[subtotal]" class="pl8app-import-csv-column" data-field="">
										<option value=""><?php _e( '- Ignore this field -', 'pl8app' ); ?></option>
									</select>
								</td>
								<td class="pl8app-import-preview-field"><?php _e( '- select field to preview data -', 'pl8app' ); ?></td>
							</tr>
							<tr>
								<td><?php _e( 'Tax', 'pl8app' ); ?></td>
								<td>
									<select name="pl8app-import-field[tax]" class="pl8app-import-csv-column" data-field="Tax ($)">
										<option value=""><?php _e( '- Ignore this field -', 'pl8app' ); ?></option>
									</select>
								</td>
								<td class="pl8app-import-preview-field"><?php _e( '- select field to preview data -', 'pl8app' ); ?></td>
							</tr>
							<tr>
								<td><?php _e( 'Total', 'pl8app' ); ?></td>
								<td>
									<select name="pl8app-import-field[total]" class="pl8app-import-csv-column" data-field="Amount ($)">
										<option value=""><?php _e( '- Ignore this field -', 'pl8app' ); ?></option>
									</select>
								</td>
								<td class="pl8app-import-preview-field"><?php _e( '- select field to preview data -', 'pl8app' ); ?></td>
							</tr>
							<tr>
								<td><?php _e( 'Transaction ID', 'pl8app' ); ?></td>
								<td>
									<select name="pl8app-import-field[transaction_id]" class="pl8app-import-csv-column" data-field="Transaction ID">
										<option value=""><?php _e( '- Ignore this field -', 'pl8app' ); ?></option>
									</select>
								</td>
								<td class="pl8app-import-preview-field"><?php _e( '- select field to preview data -', 'pl8app' ); ?></td>
							</tr>
							<tr>
								<td><?php _e( 'User', 'pl8app' ); ?></td>
								<td>
									<select name="pl8app-import-field[user_id]" class="pl8app-import-csv-column" data-field="User">
										<option value=""><?php _e( '- Ignore this field -', 'pl8app' ); ?></option>
									</select>
								</td>
								<td class="pl8app-import-preview-field"><?php _e( '- select field to preview data -', 'pl8app' ); ?></td>
							</tr>
							<tr>
								<td><?php _e( 'Address Line 1', 'pl8app' ); ?></td>
								<td>
									<select name="pl8app-import-field[line1]" class="pl8app-import-csv-column" data-field="Address">
										<option value=""><?php _e( '- Ignore this field -', 'pl8app' ); ?></option>
									</select>
								</td>
								<td class="pl8app-import-preview-field"><?php _e( '- select field to preview data -', 'pl8app' ); ?></td>
							</tr>
							<tr>
								<td><?php _e( 'Address Line 2', 'pl8app' ); ?></td>
								<td>
									<select name="pl8app-import-field[line2]" class="pl8app-import-csv-column" data-field="Address (Line 2)">
										<option value=""><?php _e( '- Ignore this field -', 'pl8app' ); ?></option>
									</select>
								</td>
								<td class="pl8app-import-preview-field"><?php _e( '- select field to preview data -', 'pl8app' ); ?></td>
							</tr>
							<tr>
								<td><?php _e( 'City', 'pl8app' ); ?></td>
								<td>
									<select name="pl8app-import-field[city]" class="pl8app-import-csv-column" data-field="City">
										<option value=""><?php _e( '- Ignore this field -', 'pl8app' ); ?></option>
									</select>
								</td>
								<td class="pl8app-import-preview-field"><?php _e( '- select field to preview data -', 'pl8app' ); ?></td>
							</tr>
							<tr>
								<td><?php _e( 'State / Province', 'pl8app' ); ?></td>
								<td>
									<select name="pl8app-import-field[state]" class="pl8app-import-csv-column" data-field="State">
										<option value=""><?php _e( '- Ignore this field -', 'pl8app' ); ?></option>
									</select>
								</td>
								<td class="pl8app-import-preview-field"><?php _e( '- select field to preview data -', 'pl8app' ); ?></td>
							</tr>
							<tr>
								<td><?php _e( 'Zip / Postal Code', 'pl8app' ); ?></td>
								<td>
									<select name="pl8app-import-field[zip]" class="pl8app-import-csv-column" data-field="Zip / Postal Code">
										<option value=""><?php _e( '- Ignore this field -', 'pl8app' ); ?></option>
									</select>
								</td>
								<td class="pl8app-import-preview-field"><?php _e( '- select field to preview data -', 'pl8app' ); ?></td>
							</tr>
							<tr>
								<td><?php _e( 'Country', 'pl8app' ); ?></td>
								<td>
									<select name="pl8app-import-field[country]" class="pl8app-import-csv-column" data-field="Country">
										<option value=""><?php _e( '- Ignore this field -', 'pl8app' ); ?></option>
									</select>
								</td>
								<td class="pl8app-import-preview-field"><?php _e( '- select field to preview data -', 'pl8app' ); ?></td>
							</tr>
						</tbody>
					</table>
					<p class="submit">
						<button class="pl8app-import-proceed button-primary"><?php _e( 'Process Import', 'pl8app' ); ?></button>
					</p>
				</div>
			</form>
		</div><!-- .inside -->
	</div><!-- .postbox -->


	<div class="postbox">
		<h3><span><?php _e( 'Export Settings', 'pl8app' ); ?></span></h3>
		<div class="inside">
			<p><?php _e( 'Export the pl8app settings for this site as a .json file. This allows you to easily import the configuration into another site.', 'pl8app' ); ?></p>
			<p><?php printf( __( 'To export shop data (purchases, customers, etc), visit the <a href="%s">Reports</a> page.', 'pl8app' ), admin_url( 'admin.php?page=pl8app-reports&tab=export' ) ); ?></p>
			<form method="post" action="<?php echo admin_url( 'admin.php?page=pl8app-tools&tab=import_export' ); ?>">
				<p><input type="hidden" name="pl8app_action" value="export_settings" /></p>
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
			<form method="post" enctype="multipart/form-data" action="<?php echo admin_url( 'admin.php?page=pl8app-tools&tab=import_export' ); ?>">
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
<?php
	do_action( 'pl8app_tools_import_export_after' );
}
add_action( 'pl8app_tools_tab_import_export', 'pl8app_tools_import_export_display' );


/**
 * Process a settings export that generates a .json file of the shop settings
 *
 * @since 1.0
 * @return      void
 */
function pl8app_tools_import_export_process_export() {

	if( empty( $_POST['pl8app_export_nonce'] ) )
		return;

	if( ! wp_verify_nonce( $_POST['pl8app_export_nonce'], 'pl8app_export_nonce' ) )
		return;

	if( ! current_user_can( 'manage_shop_settings' ) )
		return;

	$pl8app_settings  = get_option( 'pl8app_settings' );
	$pl8app_tax_rates = get_option( 'pl8app_tax_rates' );
	$settings = array(
		'pl8app_settings'  => $pl8app_settings,
		'pl8app_tax_rates' => $pl8app_tax_rates,
	);

	ignore_user_abort( true );

	if ( ! pl8app_is_func_disabled( 'set_time_limit' ) )
		set_time_limit( 0 );

	nocache_headers();
	header( 'Content-Type: application/json; charset=utf-8' );
	header( 'Content-Disposition: attachment; filename=' . apply_filters( 'pl8app_settings_export_filename', 'pl8app-settings-export-' . date( 'm-d-Y' ) ) . '.json' );
	header( "Expires: 0" );

	echo json_encode( $settings );
	exit;
}
add_action( 'pl8app_export_settings', 'pl8app_tools_import_export_process_export' );


/**
 * Process a settings import from a json file
 *
 * @since  1.0.0
 * @return void
 */
function pl8app_tools_import_export_process_import() {

	if( empty( $_POST['pl8app_import_nonce'] ) )
		return;

	if( ! wp_verify_nonce( $_POST['pl8app_import_nonce'], 'pl8app_import_nonce' ) )
		return;

	if( ! current_user_can( 'manage_shop_settings' ) )
		return;

	if( pl8app_get_file_extension( $_FILES['import_file']['name'] ) != 'json' ) {
		wp_die( __( 'Please upload a valid .json file', 'pl8app' ), __( 'Error', 'pl8app' ), array( 'response' => 400 ) );
	}

	$import_file = $_FILES['import_file']['tmp_name'];

	if( empty( $import_file ) ) {
		wp_die( __( 'Please upload a file to import', 'pl8app' ), __( 'Error', 'pl8app' ), array( 'response' => 400 ) );
	}

	// Retrieve the settings from the file and convert the json object to an array
	$settings = pl8app_object_to_array( json_decode( file_get_contents( $import_file ) ) );

	if ( ! isset( $settings['pl8app_settings'] ) ) {

		// Process a settings export from a pre 2.8 version of pl8app
		update_option( 'pl8app_settings', $settings );

	} else {

		// Update the settings from a 2.8+ export file
		$pl8app_settings  = $settings['pl8app_settings'];
		update_option( 'pl8app_settings', $pl8app_settings );

		$pl8app_tax_rates = $settings['pl8app_tax_rates'];
		update_option( 'pl8app_tax_rates', $pl8app_tax_rates );

	}



	wp_safe_redirect( admin_url( 'admin.php?page=pl8app-tools&pl8app-message=settings-imported' ) ); exit;

}
add_action( 'pl8app_import_settings', 'pl8app_tools_import_export_process_import' );


/**
 * Display the debug log tab
 *
 * @since 1.0.7
 * @return      void
 */
function pl8app_tools_debug_log_display() {

	global $pl8app_logs;

	if( ! current_user_can( 'manage_shop_settings' ) || ! pl8app_is_debug_mode() ) {
		return;
	}

?>
	<div class="postbox">
		<h3><span><?php esc_html_e( 'Debug Log', 'pl8app' ); ?></span></h3>
		<div class="inside">
			<form id="pl8app-debug-log" method="post">
				<textarea readonly="readonly" class="large-text" rows="15" name="pl8app-debug-log-contents"><?php echo esc_textarea( $pl8app_logs->get_file_contents() ); ?></textarea>
				<p class="submit">
					<input type="hidden" name="pl8app_action" value="submit_debug_log" />
					<?php
					submit_button( __( 'Download Debug Log File', 'pl8app' ), 'primary', 'pl8app-menuitem-debug-log', false );
					submit_button( __( 'Clear Log', 'pl8app' ), 'secondary pl8app-inline-button', 'pl8app-clear-debug-log', false );
					submit_button( __( 'Copy Entire Log', 'pl8app' ), 'secondary pl8app-inline-button', 'pl8app-copy-debug-log', false, array( 'onclick' => "this.form['pl8app-debug-log-contents'].focus();this.form['pl8app-debug-log-contents'].select();document.execCommand('copy');return false;" ) );
					?>
				</p>
				<?php wp_nonce_field( 'pl8app-debug-log-action' ); ?>
			</form>
			<p><?php _e( 'Log file', 'pl8app' ); ?>: <code><?php echo $pl8app_logs->get_log_file_path(); ?></code></p>
		</div><!-- .inside -->
	</div><!-- .postbox -->
<?php
}
add_action( 'pl8app_tools_tab_debug_log', 'pl8app_tools_debug_log_display' );

/**
 * Handles submit actions for the debug log.
 *
 * @since 1.0
 */
function pl8app_handle_submit_debug_log() {

	global $pl8app_logs;

	if ( ! current_user_can( 'manage_shop_settings' ) ) {
		return;
	}

	check_admin_referer( 'pl8app-debug-log-action' );

	if ( isset( $_REQUEST['pl8app-menuitem-debug-log'] ) ) {
		nocache_headers();

		header( 'Content-Type: text/plain' );
		header( 'Content-Disposition: attachment; filename="pl8app-debug-log.txt"' );

		echo wp_strip_all_tags( $_REQUEST['pl8app-debug-log-contents'] );
		exit;

	} elseif ( isset( $_REQUEST['pl8app-clear-debug-log'] ) ) {

		// Clear the debug log.
		$pl8app_logs->clear_log_file();

		wp_safe_redirect( admin_url( 'admin.php?page=pl8app-tools&tab=debug_log' ) );
		exit;

	}
}
add_action( 'pl8app_submit_debug_log', 'pl8app_handle_submit_debug_log' );

/**
 * Display the system info tab
 *
 * @since       2.0
 * @return      void
 */
function pl8app_tools_sysinfo_display() {

	if( ! current_user_can( 'manage_shop_settings' ) ) {
		return;
	}

?>
	<form action="<?php echo esc_url( admin_url( 'admin.php?page=pl8app-tools&tab=system_info' ) ); ?>" method="post" dir="ltr">
		<textarea readonly="readonly" onclick="this.focus(); this.select()" id="system-info-textarea" name="pl8app-sysinfo"><?php echo pl8app_tools_sysinfo_get(); ?></textarea>
		<p class="submit">
			<input type="hidden" name="pl8app-action" value="menuitem_sysinfo" />
			<?php submit_button( 'Download System Info File', 'primary', 'pl8app-menuitem-sysinfo', false ); ?>
		</p>
	</form>
<?php
}
add_action( 'pl8app_tools_tab_system_info', 'pl8app_tools_sysinfo_display' );


/**
 * Get system info
 *
 * @since       2.0
 * @global      object $wpdb Used to query the database using the WordPress Database API
 * @return      string $return A string containing the info to output
 */
function pl8app_tools_sysinfo_get() {
	global $wpdb;

	if( !class_exists( 'Browser' ) )
		require_once PL8_PLUGIN_DIR . 'includes/libraries/browser.php';

	$browser = new Browser();

	// Get theme info
	$theme_data   = wp_get_theme();
	$theme        = $theme_data->Name . ' ' . $theme_data->Version;
	$parent_theme = $theme_data->Template;
	if ( ! empty( $parent_theme ) ) {
		$parent_theme_data = wp_get_theme( $parent_theme );
		$parent_theme      = $parent_theme_data->Name . ' ' . $parent_theme_data->Version;
	}

	// Try to identify the hosting provider
	$host = pl8app_get_host();

	$return  = '### Begin System Info ###' . "\n\n";

	// Start with the basics...
	$return .= '-- Site Info' . "\n\n";
	$return .= 'Site URL:                 ' . site_url() . "\n";
	$return .= 'Home URL:                 ' . home_url() . "\n";
	$return .= 'Multisite:                ' . ( is_multisite() ? 'Yes' : 'No' ) . "\n";

	$return  = apply_filters( 'pl8app_sysinfo_after_site_info', $return );

	// Can we determine the site's host?
	if( $host ) {
		$return .= "\n" . '-- Hosting Provider' . "\n\n";
		$return .= 'Host:                     ' . $host . "\n";

		$return  = apply_filters( 'pl8app_sysinfo_after_host_info', $return );
	}

	// The local users' browser information, handled by the Browser class
	$return .= "\n" . '-- User Browser' . "\n\n";
	$return .= $browser;

	$return  = apply_filters( 'pl8app_sysinfo_after_user_browser', $return );

	$locale = get_locale();

	// WordPress configuration
	$return .= "\n" . '-- WordPress Configuration' . "\n\n";
	$return .= 'Version:                  ' . get_bloginfo( 'version' ) . "\n";
	$return .= 'Language:                 ' . ( !empty( $locale ) ? $locale : 'en_US' ) . "\n";
	$return .= 'Permalink Structure:      ' . ( get_option( 'permalink_structure' ) ? get_option( 'permalink_structure' ) : 'Default' ) . "\n";
	$return .= 'Active Theme:             ' . $theme . "\n";
	if ( $parent_theme !== $theme ) {
		$return .= 'Parent Theme:             ' . $parent_theme . "\n";
	}
	$return .= 'Show On Front:            ' . get_option( 'show_on_front' ) . "\n";

	// Only show page specs if frontpage is set to 'page'
	if( get_option( 'show_on_front' ) == 'page' ) {
		$front_page_id = get_option( 'page_on_front' );
		$blog_page_id = get_option( 'page_for_posts' );

		$return .= 'Page On Front:            ' . ( $front_page_id != 0 ? get_the_title( $front_page_id ) . ' (#' . $front_page_id . ')' : 'Unset' ) . "\n";
		$return .= 'Page For Posts:           ' . ( $blog_page_id != 0 ? get_the_title( $blog_page_id ) . ' (#' . $blog_page_id . ')' : 'Unset' ) . "\n";
	}

	$return .= 'ABSPATH:                  ' . ABSPATH . "\n";

	// Make sure wp_remote_post() is working
	$request['cmd'] = '_notify-validate';

	$params = array(
		'sslverify'     => false,
		'timeout'       => 60,
		'user-agent'    => 'pl8app/' . PL8_VERSION,
		'body'          => $request
	);

	$response = wp_remote_post( 'https://www.paypal.com/cgi-bin/webscr', $params );

	if( !is_wp_error( $response ) && $response['response']['code'] >= 200 && $response['response']['code'] < 300 ) {
		$WP_REMOTE_POST = 'wp_remote_post() works';
	} else {
		$WP_REMOTE_POST = 'wp_remote_post() does not work';
	}

	$return .= 'Remote Post:              ' . $WP_REMOTE_POST . "\n";
	$return .= 'Table Prefix:             ' . 'Length: ' . strlen( $wpdb->prefix ) . '   Status: ' . ( strlen( $wpdb->prefix ) > 16 ? 'ERROR: Too long' : 'Acceptable' ) . "\n";
	//$return .= 'Admin AJAX:               ' . ( pl8app_test_ajax_works() ? 'Accessible' : 'Inaccessible' ) . "\n";
	$return .= 'WP_DEBUG:                 ' . ( defined( 'WP_DEBUG' ) ? WP_DEBUG ? 'Enabled' : 'Disabled' : 'Not set' ) . "\n";
	$return .= 'Memory Limit:             ' . WP_MEMORY_LIMIT . "\n";
	$return .= 'Registered Post Stati:    ' . implode( ', ', get_post_stati() ) . "\n";

	$return  = apply_filters( 'pl8app_sysinfo_after_wordpress_config', $return );

	// pl8app configuration
	$return .= "\n" . '-- pl8app Configuration' . "\n\n";
	$return .= 'Version:                  ' . PL8_VERSION . "\n";
	$return .= 'Upgraded From:            ' . get_option( 'pl8app_version_upgraded_from', 'None' ) . "\n";
	$return .= 'Test Mode:                ' . ( pl8app_is_test_mode() ? "Enabled\n" : "Disabled\n" );
	$return .= 'AJAX:                     ' . ( ! pl8app_is_ajax_disabled() ? "Enabled\n" : "Disabled\n" );
	$return .= 'Guest Checkout:           ' . ( pl8app_no_guest_checkout() ? "Disabled\n" : "Enabled\n" );
	$return .= 'Download Method:          ' . ucfirst( pl8app_get_file_menuitem_method() ) . "\n";
	$return .= 'Currency Code:            ' . pl8app_get_currency() . "\n";
	$return .= 'Currency Position:        ' . pl8app_get_option( 'currency_position', 'before' ) . "\n";
	$return .= 'Decimal Separator:        ' . pl8app_get_option( 'decimal_separator', '.' ) . "\n";
	$return .= 'Thousands Separator:      ' . pl8app_get_option( 'thousands_separator', ',' ) . "\n";
	$return .= 'Upgrades Completed:       ' . implode( ',', pl8app_get_completed_upgrades() ) . "\n";
	$return .= 'Download Link Expiration: ' . pl8app_get_option( 'menuitem_link_expiration' ) . " hour(s)\n";

	$return  = apply_filters( 'pl8app_sysinfo_after_pl8app_config', $return );

	// pl8app pages
	$menu_page = pl8app_get_option( 'menu_items_page', '' );
	$purchase_page = pl8app_get_option( 'purchase_page', '' );
	$success_page  = pl8app_get_option( 'success_page', '' );
	$failure_page  = pl8app_get_option( 'failure_page', '' );

	$return .= "\n" . '-- pl8app Page Configuration' . "\n\n";
	$return .= 'Menu Menu:                 ' . ( !empty( $$menu_page ) ? "Valid\n" : "Invalid\n" );
	$return .= 'Checkout:                 ' . ( !empty( $purchase_page ) ? "Valid\n" : "Invalid\n" );
	$return .= 'Checkout Page:            ' . ( !empty( $purchase_page ) ? get_permalink( $purchase_page ) . "\n" : "Unset\n" );
	$return .= 'Success Page:             ' . ( !empty( $success_page ) ? get_permalink( $success_page ) . "\n" : "Unset\n" );
	$return .= 'Failure Page:             ' . ( !empty( $failure_page ) ? get_permalink( $failure_page ) . "\n" : "Unset\n" );
	$return .= 'pl8app Slug:           ' . ( defined( 'pl8app_SLUG' ) ? '/' . pl8app_SLUG . "\n" : "/menuitems\n" );

	$return  = apply_filters( 'pl8app_sysinfo_after_pl8app_pages', $return );

	// pl8app gateways
	$return .= "\n" . '-- pl8app Gateway Configuration' . "\n\n";

	$active_gateways = pl8app_get_enabled_payment_gateways();
	if( $active_gateways ) {
		$default_gateway_is_active = pl8app_is_gateway_active( pl8app_get_default_gateway() );
		if( $default_gateway_is_active ) {
			$default_gateway = pl8app_get_default_gateway();
			$default_gateway = $active_gateways[$default_gateway]['admin_label'];
		} else {
			$default_gateway = 'Test Payment';
		}

		$gateways        = array();
		foreach( $active_gateways as $gateway ) {
			$gateways[] = $gateway['admin_label'];
		}

		$return .= 'Enabled Gateways:         ' . implode( ', ', $gateways ) . "\n";
		$return .= 'Default Gateway:          ' . $default_gateway . "\n";
	} else {
		$return .= 'Enabled Gateways:         None' . "\n";
	}

	$return  = apply_filters( 'pl8app_sysinfo_after_pl8app_gateways', $return );


	// pl8app Taxes
	$return .= "\n" . '-- pl8app Tax Configuration' . "\n\n";
	$return .= 'Taxes:                    ' . ( pl8app_use_taxes() ? "Enabled\n" : "Disabled\n" );
	$return .= 'Tax Rate:                 ' . pl8app_get_tax_rate() * 100 . "\n";
	$return .= 'Display On Checkout:      ' . ( pl8app_get_option( 'checkout_include_tax', false ) ? "Displayed\n" : "Not Displayed\n" );
	$return .= 'Prices Include Tax:       ' . ( pl8app_prices_include_tax() ? "Yes\n" : "No\n" );

	$return  = apply_filters( 'pl8app_sysinfo_after_pl8app_taxes', $return );

	// pl8app Templates
	$dir = get_stylesheet_directory() . '/pl8app_templates/*';
	if( is_dir( $dir ) && ( count( glob( "$dir/*" ) ) !== 0 ) ) {
		$return .= "\n" . '-- pl8app Template Overrides' . "\n\n";

		foreach( glob( $dir ) as $file ) {
			$return .= 'Filename:                 ' . basename( $file ) . "\n";
		}

		$return  = apply_filters( 'pl8app_sysinfo_after_pl8app_templates', $return );
	}

	// Get plugins that have an update
	$updates = get_plugin_updates();

	// Must-use plugins
	// NOTE: MU plugins can't show updates!
	$muplugins = get_mu_plugins();
	if( count( $muplugins ) > 0 ) {
		$return .= "\n" . '-- Must-Use Plugins' . "\n\n";

		foreach( $muplugins as $plugin => $plugin_data ) {
			$return .= $plugin_data['Name'] . ': ' . $plugin_data['Version'] . "\n";
		}

		$return = apply_filters( 'pl8app_sysinfo_after_wordpress_mu_plugins', $return );
	}

	// WordPress active plugins
	$return .= "\n" . '-- WordPress Active Plugins' . "\n\n";

	$plugins = get_plugins();
	$active_plugins = get_option( 'active_plugins', array() );

	foreach( $plugins as $plugin_path => $plugin ) {
		if( !in_array( $plugin_path, $active_plugins ) )
			continue;

		$update = ( array_key_exists( $plugin_path, $updates ) ) ? ' (needs update - ' . $updates[$plugin_path]->update->new_version . ')' : '';
		$return .= $plugin['Name'] . ': ' . $plugin['Version'] . $update . "\n";
	}

	$return  = apply_filters( 'pl8app_sysinfo_after_wordpress_plugins', $return );

	// WordPress inactive plugins
	$return .= "\n" . '-- WordPress Inactive Plugins' . "\n\n";

	foreach( $plugins as $plugin_path => $plugin ) {
		if( in_array( $plugin_path, $active_plugins ) )
			continue;

		$update = ( array_key_exists( $plugin_path, $updates ) ) ? ' (needs update - ' . $updates[$plugin_path]->update->new_version . ')' : '';
		$return .= $plugin['Name'] . ': ' . $plugin['Version'] . $update . "\n";
	}

	$return  = apply_filters( 'pl8app_sysinfo_after_wordpress_plugins_inactive', $return );

	if( is_multisite() ) {
		// WordPress Multisite active plugins
		$return .= "\n" . '-- Network Active Plugins' . "\n\n";

		$plugins = wp_get_active_network_plugins();
		$active_plugins = get_site_option( 'active_sitewide_plugins', array() );

		foreach( $plugins as $plugin_path ) {
			$plugin_base = plugin_basename( $plugin_path );

			if( !array_key_exists( $plugin_base, $active_plugins ) )
				continue;

			$update = ( array_key_exists( $plugin_path, $updates ) ) ? ' (needs update - ' . $updates[$plugin_path]->update->new_version . ')' : '';
			$plugin  = get_plugin_data( $plugin_path );
			$return .= $plugin['Name'] . ': ' . $plugin['Version'] . $update . "\n";
		}

		$return  = apply_filters( 'pl8app_sysinfo_after_wordpress_ms_plugins', $return );
	}

	// Server configuration (really just versioning)
	$return .= "\n" . '-- Webserver Configuration' . "\n\n";
	$return .= 'PHP Version:              ' . PHP_VERSION . "\n";
	$return .= 'MySQL Version:            ' . $wpdb->db_version() . "\n";
	$return .= 'Webserver Info:           ' . $_SERVER['SERVER_SOFTWARE'] . "\n";

	$return  = apply_filters( 'pl8app_sysinfo_after_webserver_config', $return );

	// PHP configs... now we're getting to the important stuff
	$return .= "\n" . '-- PHP Configuration' . "\n\n";
	$return .= 'Memory Limit:             ' . ini_get( 'memory_limit' ) . "\n";
	$return .= 'Upload Max Size:          ' . ini_get( 'upload_max_filesize' ) . "\n";
	$return .= 'Post Max Size:            ' . ini_get( 'post_max_size' ) . "\n";
	$return .= 'Upload Max Filesize:      ' . ini_get( 'upload_max_filesize' ) . "\n";
	$return .= 'Time Limit:               ' . ini_get( 'max_execution_time' ) . "\n";
	$return .= 'Max Input Vars:           ' . ini_get( 'max_input_vars' ) . "\n";
	$return .= 'Display Errors:           ' . ( ini_get( 'display_errors' ) ? 'On (' . ini_get( 'display_errors' ) . ')' : 'N/A' ) . "\n";
	$return .= 'PHP Arg Separator:        ' . pl8app_get_php_arg_separator_output() . "\n";

	$return  = apply_filters( 'pl8app_sysinfo_after_php_config', $return );

	// PHP extensions and such
	$return .= "\n" . '-- PHP Extensions' . "\n\n";
	$return .= 'cURL:                     ' . ( function_exists( 'curl_init' ) ? 'Supported' : 'Not Supported' ) . "\n";
	$return .= 'fsockopen:                ' . ( function_exists( 'fsockopen' ) ? 'Supported' : 'Not Supported' ) . "\n";
	$return .= 'SOAP Client:              ' . ( class_exists( 'SoapClient' ) ? 'Installed' : 'Not Installed' ) . "\n";
	$return .= 'Suhosin:                  ' . ( extension_loaded( 'suhosin' ) ? 'Installed' : 'Not Installed' ) . "\n";

	$return  = apply_filters( 'pl8app_sysinfo_after_php_ext', $return );

	// Session stuff
	$return .= "\n" . '-- Session Configuration' . "\n\n";
	$return .= 'pl8app Use Sessions:         ' . ( defined( 'pl8app_USE_PHP_SESSIONS' ) && pl8app_USE_PHP_SESSIONS ? 'Enforced' : ( PL8PRESS()->session->use_php_sessions() ? 'Enabled' : 'Disabled' ) ) . "\n";
	$return .= 'Session:                  ' . ( isset( $_SESSION ) ? 'Enabled' : 'Disabled' ) . "\n";

	// The rest of this is only relevant is session is enabled
	if( isset( $_SESSION ) ) {
		$return .= 'Session Name:             ' . esc_html( ini_get( 'session.name' ) ) . "\n";
		$return .= 'Cookie Path:              ' . esc_html( ini_get( 'session.cookie_path' ) ) . "\n";
		$return .= 'Save Path:                ' . esc_html( ini_get( 'session.save_path' ) ) . "\n";
		$return .= 'Use Cookies:              ' . ( ini_get( 'session.use_cookies' ) ? 'On' : 'Off' ) . "\n";
		$return .= 'Use Only Cookies:         ' . ( ini_get( 'session.use_only_cookies' ) ? 'On' : 'Off' ) . "\n";
	}

	$return  = apply_filters( 'pl8app_sysinfo_after_session_config', $return );

	$return .= "\n" . '### End System Info ###';

	return $return;
}


/**
 * Generates a System Info menuitem file
 *
 * @since       2.0
 * @return      void
 */
function pl8app_tools_sysinfo_menuitem() {

	if( ! current_user_can( 'manage_shop_settings' ) ) {
		return;
	}

	nocache_headers();

	header( 'Content-Type: text/plain' );
	header( 'Content-Disposition: attachment; filename="pl8app-system-info.txt"' );

	echo wp_strip_all_tags( $_POST['pl8app-sysinfo'] );
	pl8app_die();
}
add_action( 'pl8app_menuitem_sysinfo', 'pl8app_tools_sysinfo_menuitem' );
