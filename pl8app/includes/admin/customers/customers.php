<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Customers Page
 *
 * Renders the customers page contents.
 *
 * @since  1.0.0
 * @return void
*/
function pl8app_customers_page() {
	$default_views = pl8app_customer_views();
	$requested_view = isset( $_GET['view'] ) ? sanitize_text_field( $_GET['view'] ) : 'customers';
	if ( array_key_exists( $requested_view, $default_views ) && is_callable( $default_views[$requested_view] ) ) {
		pl8app_render_customer_view( $requested_view, $default_views );
	} else {
		pl8app_customers_list();
	}
}

/**
 * Register the views for customer management
 *
 * @since  1.0.0
 * @return array Array of views and their callbacks
 */
function pl8app_customer_views() {

	$views = array();
	return apply_filters( 'pl8app_customer_views', $views );

}

/**
 * Register the tabs for customer management
 *
 * @since  1.0.0
 * @return array Array of tabs for the customer
 */
function pl8app_customer_tabs() {

	$tabs = array();
	return apply_filters( 'pl8app_customer_tabs', $tabs );

}

/**
 * List table of customers
 *
 * @since  1.0.0
 * @return void
 */
function pl8app_customers_list() {
	include( dirname( __FILE__ ) . '/class-customer-table.php' );

	$customers_table = new pl8app_Customer_Reports_Table();
	$customers_table->prepare_items();
	?>
	<div class="wrap">
		<h1><?php _e( 'Customers', 'pl8app' ); ?></h1>
		<?php do_action( 'pl8app_customers_table_top' ); ?>
		<form id="pl8app-customers-filter" method="get" action="<?php echo admin_url( 'admin.php?page=pl8app-customers' ); ?>">
			<?php
			$customers_table->search_box( __( 'Search Customers', 'pl8app' ), 'pl8app-customers' );
			$customers_table->display();
			?>
			<input type="hidden" name="page" value="pl8app-customers" />
			<input type="hidden" name="view" value="customers" />
		</form>
		<?php do_action( 'pl8app_customers_table_bottom' ); ?>
	</div>
	<?php
}

/**
 * Renders the customer view wrapper
 *
 * @since  1.0.0
 * @param  string $view      The View being requested
 * @param  array $callbacks  The Registered views and their callback functions
 * @return void
 */
function pl8app_render_customer_view( $view, $callbacks ) {

	$render = true;

	$customer_view_role = apply_filters( 'pl8app_view_customers_role', 'view_shop_reports' );

	if ( ! current_user_can( $customer_view_role ) ) {
		pl8app_set_error( 'pl8app-no-access', __( 'You are not permitted to view this data.', 'pl8app' ) );
		$render = false;
	}

	if ( ! isset( $_GET['id'] ) || ! is_numeric( $_GET['id'] ) ) {
		pl8app_set_error( 'pl8app-invalid_customer', __( 'Invalid Customer ID Provided.', 'pl8app' ) );
		$render = false;
	}

	$customer_id = (int)$_GET['id'];
	$customer    = new pl8app_Customer( $customer_id );

	if ( empty( $customer->id ) ) {
		pl8app_set_error( 'pl8app-invalid_customer', __( 'Invalid Customer ID Provided.', 'pl8app' ) );
		$render = false;
	}

	$customer_tabs = pl8app_customer_tabs();
	?>

	<div class='wrap'>
		<h2>
			<?php _e( 'Customer Details', 'pl8app' ); ?>
			<?php do_action( 'pl8app_after_customer_details_header', $customer ); ?>
		</h2>
		<?php if ( pl8app_get_errors() ) :?>
			<div class="error settings-error">
				<?php pl8app_print_errors(); ?>
			</div>
		<?php endif; ?>

		<?php if ( $customer && $render ) : ?>

			<div id="pl8app-item-wrapper" class="pl8app-item-has-tabs pl8app-clearfix">
				<div id="pl8app-item-tab-wrapper" class="customer-tab-wrapper">
					<ul id="pl8app-item-tab-wrapper-list" class="customer-tab-wrapper-list">
						<?php foreach ( $customer_tabs as $key => $tab ) : ?>
							<?php $active = $key === $view ? true : false; ?>
							<?php $class  = $active ? 'active' : 'inactive'; ?>

							<li class="<?php echo sanitize_html_class( $class ); ?>">

								<?php
								// prevent double "Customer" output from extensions
								$tab['title'] = preg_replace("(^Customer )","",$tab['title']);

								// pl8app item tab full title
								$tab_title = sprintf( _x( 'Customer %s', 'Customer Details page tab title', 'pl8app' ), esc_attr( $tab[ 'title' ] ) );

								// aria-label output
								$aria_label = ' aria-label="' . $tab_title . '"';
								?>

								<?php if ( ! $active ) : ?>
									<a href="<?php echo esc_url( admin_url( 'admin.php?page=pl8app-customers&view=' . $key . '&id=' . $customer->id . '#wpbody-content' ) ); ?>"<?php echo $aria_label; ?>>
								<?php endif; ?>

									<span class="pl8app-item-tab-label-wrap"<?php echo $active ? $aria_label : ''; ?>>
										<span class="dashicons <?php echo sanitize_html_class( $tab['dashicon'] ); ?>" aria-hidden="true"></span>
										<span class="pl8app-item-tab-label"><?php echo esc_attr( $tab['title'] ); ?></span>
									</span>

								<?php if ( ! $active ) : ?>
									</a>
								<?php endif; ?>

							</li>

						<?php endforeach; ?>
					</ul>
				</div>

				<div id="pl8app-item-card-wrapper" class="pl8app-customer-card-wrapper" style="float: left">
					<?php call_user_func( $callbacks[ $view ], $customer ); ?>
				</div>
			</div>

		<?php endif; ?>

	</div>
	<?php

}


/**
 * View a customer
 *
 * @since  1.0.0
 * @param  $customer The Customer object being displayed
 * @return void
 */
function pl8app_customers_view( $customer ) {

	$customer_edit_role = apply_filters( 'pl8app_edit_customers_role', 'edit_shop_payments' );

	?>

	<?php do_action( 'pl8app_customer_card_top', $customer ); ?>

	<div class="info-wrapper customer-section">

		<form id="edit-customer-info" method="post" action="<?php echo admin_url( 'admin.php?page=pl8app-customers&view=overview&id=' . $customer->id ); ?>">

			<div class="pl8app-item-info customer-info">

				<div class="avatar-wrap left" id="customer-avatar">
					<?php echo get_avatar( $customer->email ); ?><br />
					<?php if ( current_user_can( $customer_edit_role ) ): ?>
						<span class="info-item editable customer-edit-link"><a href="#" id="edit-customer"><?php _e( 'Edit Customer', 'pl8app' ); ?></a></span>
						<?php do_action( 'pl8app_after_customer_edit_link', $customer ); ?>
					<?php endif; ?>
				</div>

				<div class="customer-id right">
					#<?php echo $customer->id; ?>
				</div>

				<div class="customer-address-wrapper right">
				<?php if ( isset( $customer->user_id ) && $customer->user_id > 0 ) : ?>

					<?php
						$address = get_user_meta( $customer->user_id, '_pl8app_user_address', true );
						$defaults = array(
							'line1'   => '',
							'line2'   => '',
							'city'    => '',
							'state'   => '',
							'country' => '',
							'zip'     => ''
						);

						$address = wp_parse_args( $address, $defaults );
					?>

					<strong><?php _e( 'Customer Address', 'pl8app' ); ?></strong>
					<span class="customer-address info-item editable">
						<span class="info-item" data-key="line1"><?php echo $address['line1']; ?></span>
						<span class="info-item" data-key="line2"><?php echo $address['line2']; ?></span>
						<span class="info-item" data-key="city"><?php echo $address['city']; ?></span>
						<span class="info-item" data-key="state"><?php echo pl8app_get_state_name( $address['country'], $address['state'] ); ?></span>
						<span class="info-item" data-key="country"><?php echo pl8app_get_country_name( $address['country'] ); ?></span>
						<span class="info-item" data-key="zip"><?php echo $address['zip']; ?></span>
					</span>

					<span class="customer-address info-item edit-item">
						<input class="info-item" type="text" data-key="line1" name="customerinfo[line1]" placeholder="<?php _e( 'Address 1', 'pl8app' ); ?>" value="<?php echo $address['line1']; ?>" />
						<input class="info-item" type="text" data-key="line2" name="customerinfo[line2]" placeholder="<?php _e( 'Address 2', 'pl8app' ); ?>" value="<?php echo $address['line2']; ?>" />
						<input class="info-item" type="text" data-key="city" name="customerinfo[city]" placeholder="<?php _e( 'City', 'pl8app' ); ?>" value="<?php echo $address['city']; ?>" />
						<select data-key="country" name="customerinfo[country]" id="billing_country" class="billing_country pl8app-select edit-item">
							<?php

							$selected_country = $address['country'];

							$countries = pl8app_get_country_list();
							foreach( $countries as $country_code => $country ) {
								echo '<option value="' . esc_attr( $country_code ) . '"' . selected( $country_code, $selected_country, false ) . '>' . $country . '</option>';
							}
							?>
						</select>
						<?php
						$selected_state = pl8app_get_shop_state();
						$states         = pl8app_get_states( $selected_country );

						$selected_state = isset( $address['state'] ) ? $address['state'] : $selected_state;

						if( ! empty( $states ) ) : ?>
						<select data-key="state" name="customerinfo[state]" id="card_state" class="card_state pl8app-select info-item">
							<?php
								foreach( $states as $state_code => $state ) {
									echo '<option value="' . $state_code . '"' . selected( $state_code, $selected_state, false ) . '>' . $state . '</option>';
								}
							?>
						</select>
						<?php else : ?>
						<input type="text" data-key="state" name="customerinfo[state]" id="card_state" class="card_state pl8app-input info-item" placeholder="<?php _e( 'State / Province', 'pl8app' ); ?>" value="<?php echo $address['state']; ?>"/>
						<?php endif; ?>
						<input class="info-item" type="text" data-key="zip" name="customerinfo[zip]" placeholder="<?php _e( 'Postal', 'pl8app' ); ?>" value="<?php echo $address['zip']; ?>" />
					</span>
				<?php endif; ?>
				</div>

				<div class="customer-main-wrapper left">

					<span class="customer-name info-item edit-item"><input size="15" data-key="name" name="customerinfo[name]" type="text" value="<?php echo esc_attr( $customer->name ); ?>" placeholder="<?php _e( 'Customer Name', 'pl8app' ); ?>" /></span>
					<span class="customer-name info-item editable"><span data-key="name"><?php echo $customer->name; ?></span></span>
					<span class="customer-name info-item edit-item"><input size="20" data-key="email" name="customerinfo[email]" type="text" value="<?php echo $customer->email; ?>" placeholder="<?php _e( 'Customer Email', 'pl8app' ); ?>" /></span>
					<span class="customer-email info-item editable" data-key="email"><?php echo $customer->email; ?></span>
					<span class="customer-since info-item">
						<?php
						printf(
							/* translators: The date. */
							esc_html__( 'Customer since %s', 'pl8app' ),
							esc_html( date_i18n( get_option( 'date_format' ), strtotime( $customer->date_created ) ) )
						);
						?>
					</span>
					<span class="customer-user-id info-item edit-item">
						<?php

						$user_id    = $customer->user_id > 0 ? $customer->user_id : '';
						$data_atts  = array( 'key' => 'user_login', 'exclude' => $user_id );
						$user_args  = array(
							'name'  => 'customerinfo[user_login]',
							'class' => 'pl8app-user-dropdown',
							'data'  => $data_atts,
						);

						if( ! empty( $user_id ) ) {
							$userdata = get_userdata( $user_id );
							$user_args['value'] = $userdata->user_login;
						}

						echo PL8PRESS()->html->ajax_user_search( $user_args );
						?>
						<input type="hidden" name="customerinfo[user_id]" data-key="user_id" value="<?php echo $customer->user_id; ?>" />
					</span>

					<span class="customer-user-id info-item editable">
						<?php _e( 'User ID', 'pl8app' ); ?>:&nbsp;
						<?php if( intval( $customer->user_id ) > 0 ) : ?>
							<span data-key="user_id"><a href="<?php echo admin_url( 'user-edit.php?user_id=' . $customer->user_id ); ?>"><?php echo $customer->user_id; ?></a></span>
						<?php else : ?>
							<span data-key="user_id"><?php _e( 'none', 'pl8app' ); ?></span>
						<?php endif; ?>
						<?php if ( current_user_can( $customer_edit_role ) && intval( $customer->user_id ) > 0 ) : ?>
							<span class="disconnect-user"> - <a id="disconnect-customer" href="#disconnect"><?php _e( 'Disconnect User', 'pl8app' ); ?></a></span>
						<?php endif; ?>
					</span>

				</div>

			</div>

			<span id="customer-edit-actions" class="edit-item">
				<input type="hidden" data-key="id" name="customerinfo[id]" value="<?php echo $customer->id; ?>" />
				<?php wp_nonce_field( 'edit-customer', '_wpnonce', false, true ); ?>
				<input type="hidden" name="pl8app_action" value="edit-customer" />
				<input type="submit" id="pl8app-edit-customer-save" class="button-secondary" value="<?php _e( 'Update Customer', 'pl8app' ); ?>" />
				<a id="pl8app-edit-customer-cancel" href="" class="delete"><?php _e( 'Cancel', 'pl8app' ); ?></a>
			</span>

		</form>
	</div>

	<?php do_action( 'pl8app_customer_before_stats', $customer ); ?>

	<div id="pl8app-item-stats-wrapper" class="customer-stats-wrapper customer-section">
		<ul>
			<li>
				<a href="<?php echo admin_url( 'admin.php?page=pl8app-payment-history&customer=' . $customer->id ); ?>">
					<span class="dashicons dashicons-cart"></span>
					<?php printf( _n( '%d Completed Order', '%d Completed Orders', $customer->purchase_count, 'pl8app' ), $customer->purchase_count ); ?>
				</a>
			</li>
			<?php do_action( 'pl8app_customer_stats_list', $customer ); ?>
		</ul>
	</div>

	<?php do_action( 'pl8app_customer_before_tables_wrapper', $customer ); ?>

	<div id="pl8app-item-tables-wrapper" class="customer-tables-wrapper customer-section">

		<?php do_action( 'pl8app_customer_before_tables', $customer ); ?>

		<h3>
			<?php _e( 'Customer Emails', 'pl8app' ); ?>
			<span alt="f223" class="pl8app-help-tip dashicons dashicons-editor-help" title="<?php _e( 'This customer can use any of the emails listed here when making new purchases.', 'pl8app' ); ?>"></span>
		</h3>
		<?php
			$primary_email     = $customer->email;
			$additional_emails = $customer->emails;

			$all_emails = array( 'primary' => $primary_email );
			foreach ( $additional_emails as $key => $email ) {
				if ( $primary_email === $email ) {
					continue;
				}

				$all_emails[ $key ] = $email;
			}
		?>
		<table class="wp-list-table widefat striped emails">
			<thead>
				<tr>
					<th><?php _e( 'Email', 'pl8app' ); ?></th>
					<th><?php _e( 'Actions', 'pl8app' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php if ( ! empty( $all_emails ) ) : ?>
					<?php foreach ( $all_emails as $key => $email ) : ?>
						<tr data-key="<?php echo $key; ?>">
							<td>
								<?php echo $email; ?>
								<?php if ( 'primary' === $key ) : ?>
									<span class="dashicons dashicons-star-filled primary-email-icon"></span>
								<?php endif; ?>
							</td>
							<td>
								<?php if ( 'primary' !== $key ) : ?>
									<?php
										$base_url    = admin_url( 'admin.php?page=pl8app-customers&view=overview&id=' . $customer->id );
										$promote_url = wp_nonce_url( add_query_arg( array( 'email' => rawurlencode( $email ), 'pl8app_action' => 'customer-primary-email'), $base_url ), 'pl8app-set-customer-primary-email' );
										$remove_url  = wp_nonce_url( add_query_arg( array( 'email' => rawurlencode( $email ), 'pl8app_action' => 'customer-remove-email'), $base_url ), 'pl8app-remove-customer-email' );
									?>
									<a href="<?php echo $promote_url; ?>"><?php _e( 'Make Primary', 'pl8app' ); ?></a>
									&nbsp;|&nbsp;
									<a href="<?php echo $remove_url; ?>" class="delete"><?php _e( 'Remove', 'pl8app' ); ?></a>
								<?php endif; ?>
							</td>
						</tr>
					<?php endforeach; ?>
					<tr class="add-customer-email-row">
						<td colspan="2" class="add-customer-email-td">
							<div class="add-customer-email-wrapper">
								<input type="hidden" name="customer-id" value="<?php echo $customer->id; ?>" />
								<?php wp_nonce_field( 'pl8app-add-customer-email', 'add_email_nonce', false, true ); ?>
								<input type="email" name="additional-email" value="" placeholder="<?php _e( 'Email Address', 'pl8app' ); ?>" />&nbsp;
								<input type="checkbox" name="make-additional-primary" value="1" id="make-additional-primary" />&nbsp;<label for="make-additional-primary"><?php _e( 'Make Primary', 'pl8app' ); ?></label>
								<button class="button-secondary pl8app-add-customer-email" id="add-customer-email" style="margin: 6px 0;"><?php _e( 'Add Email', 'pl8app' ); ?></button>
								<span class="spinner"></span>
							</div>
							<div class="notice-container"></div>
						</td>
					</tr>
				<?php else: ?>
					<tr><td colspan="2"><?php _e( 'No Emails Found', 'pl8app' ); ?></td></tr>
				<?php endif; ?>
			</tbody>
		</table>

		<h3><?php _e( 'Recent Orders', 'pl8app' ); ?></h3>
		<?php
			$payment_ids = explode( ',', $customer->payment_ids );
			$payments    = pl8app_get_payments( array( 'post__in' => $payment_ids ) );
			$payments    = array_slice( $payments, 0, 10 );
		?>
		<table class="wp-list-table widefat striped payments">
			<thead>
				<tr>
					<th><?php _e( 'ID', 'pl8app' ); ?></th>
					<th><?php _e( 'Amount', 'pl8app' ); ?></th>
					<th><?php _e( 'Date', 'pl8app' ); ?></th>
					<th><?php _e( 'Status', 'pl8app' ); ?></th>
					<th><?php _e( 'Actions', 'pl8app' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php if ( ! empty( $payments ) ) : ?>
					<?php foreach ( $payments as $payment ) : ?>
						<tr>
							<td><?php echo $payment->ID; ?></td>
							<td><?php echo pl8app_payment_amount( $payment->ID ); ?></td>
							<td><?php echo date_i18n( get_option( 'date_format' ), strtotime( $payment->post_date ) ); ?></td>
							<td><?php echo pl8app_get_payment_status( $payment, true ); ?></td>
							<td>
								<a href="<?php echo admin_url( 'admin.php?page=pl8app-payment-history&view=view-order-details&id=' . $payment->ID ); ?>">
									<?php _e( 'View Details', 'pl8app' ); ?>
								</a>
								<?php do_action( 'pl8app_customer_recent_purchases_actions', $customer, $payment ); ?>
							</td>
						</tr>
					<?php endforeach; ?>
				<?php else: ?>
					<tr><td colspan="5"><?php _e( 'No Payments Found', 'pl8app' ); ?></td></tr>
				<?php endif; ?>
			</tbody>
		</table>

		<h3><?php printf( __( 'Ordered Item', 'pl8app' ), pl8app_get_label_plural() ); ?></h3>
		<?php
			$menuitems = pl8app_get_users_ordered_products( $customer->email );
		?>
		<table class="wp-list-table widefat striped menuitems">
			<thead>
				<tr>
					<th>Item</th>
					<th width="120px"><?php _e( 'Actions', 'pl8app' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php if ( ! empty( $menuitems ) ) : ?>
					<?php foreach ( $menuitems as $menuitem ) : ?>
						<tr>
							<td><?php echo $menuitem->post_title; ?></td>
							<td>
								<a href="<?php echo esc_url( admin_url( 'post.php?action=edit&post=' . $menuitem->ID ) ); ?>">
									<?php _e('View Item', 'pl8app'); ?>
								</a>
							</td>
						</tr>
					<?php endforeach; ?>
				<?php else: ?>
					<tr><td colspan="2"><?php printf( __( 'No %s Found', 'pl8app' ), pl8app_get_label_plural() ); ?></td></tr>
				<?php endif; ?>
			</tbody>
		</table>

		<?php do_action( 'pl8app_customer_after_tables', $customer ); ?>

	</div>

	<?php do_action( 'pl8app_customer_card_bottom', $customer ); ?>

	<?php
}

/**
 * View the notes of a customer
 *
 * @since  1.0.0
 * @param  $customer The Customer being displayed
 * @return void
 */
function pl8app_customer_notes_view( $customer ) {

	$paged       = isset( $_GET['paged'] ) && is_numeric( $_GET['paged'] ) ? $_GET['paged'] : 1;
	$paged       = absint( $paged );
	$note_count  = $customer->get_notes_count();
	$per_page    = apply_filters( 'pl8app_customer_notes_per_page', 20 );
	$total_pages = ceil( $note_count / $per_page );

	$customer_notes = $customer->get_notes( $per_page, $paged );
	?>

	<div id="pl8app-item-notes-wrapper">
		<div class="pl8app-item-notes-header">
			<?php echo get_avatar( $customer->email, 30 ); ?> <span><?php echo $customer->name; ?></span>
		</div>
		<?php
		$show_agree_to_terms   = pl8app_get_option( 'show_agree_to_terms', false );
		$show_agree_to_privacy = pl8app_get_option( 'show_agree_to_privacy_policy', false );

		$agreement_timestamps = $customer->get_meta( 'agree_to_terms_time', false );
		$privacy_timestamps   = $customer->get_meta( 'agree_to_privacy_time', false );

		$payments = pl8app_get_payments( array(
			'output'         => 'payments',
			'post__in'       => explode( ',', $customer->payment_ids ),
			'orderby'        => 'date',
			'posts_per_page' => 1
		));

		$last_payment_date = '';

		foreach ( $payments as $payment ) {
			if ( empty( $payment->gateway ) ) {
				continue;
			}

			// We should be using `date` here, as that is the date the button was clicked.
			$last_payment_date = strtotime( $payment->date );
			break;
		}

		if ( is_array( $agreement_timestamps ) ) {
			$agreement_timestamp = array_pop( $agreement_timestamps );
		}

		if ( is_array( $privacy_timestamps ) ) {
			$privacy_timestamp = array_pop( $privacy_timestamps );
		}

		?>

		<h3><?php _e( 'Agreements', 'pl8app' ); ?></h3>

		<span class="customer-terms-agreement-date info-item">
			<?php _e( 'Last Agreed to Terms', 'pl8app' ); ?>:
			<?php if ( ! empty( $agreement_timestamp ) ) : ?>
				<?php echo date_i18n( get_option( 'date_format' ) . ' H:i:s', $agreement_timestamp ); ?>
				<?php if ( ! empty( $agreement_timestamps ) ) : ?>
					<span alt="f223" class="pl8app-help-tip dashicons dashicons-editor-help" title="<strong><?php _e( 'Previous Agreement Dates', 'pl8app' ); ?></strong><br /><?php foreach ( $agreement_timestamps as $timestamp ) { echo date_i18n( get_option( 'date_format' ) . ' H:i:s', $timestamp ); } ?>"></span>
				<?php endif; ?>
			<?php else: ?>
				<?php
				if ( empty( $last_payment_date ) ) {
					_e( 'No date found.', 'pl8app' );
				} else {
					echo date_i18n( get_option( 'date_format' ) . ' H:i:s', $last_payment_date );
					?>
					<span alt="f223" class="pl8app-help-tip dashicons dashicons-editor-help" title="<strong><?php _e( 'Estimated Privacy Policy Date', 'pl8app' ); ?></strong><br /><?php _e( 'This customer made a purchase prior to agreement dates being logged, this is the date of their last purchase. If your site was displaying the agreement checkbox at that time, this is our best estimate as to when they last agreed to your terms.', 'pl8app' ); ?>"></span>
					<?php
				}
				?>
			<?php endif; ?>
		</span>

		<span class="customer-privacy-policy-date info-item">
			<?php _e( 'Last Agreed to Privacy Policy', 'pl8app' ); ?>:
			<?php if ( ! empty( $privacy_timestamp ) ) : ?>
				<?php echo date_i18n( get_option( 'date_format' ) . ' H:i:s', $privacy_timestamp ); ?>
				<?php if ( ! empty( $privacy_timestamps ) ) : ?>
					<span alt="f223" class="pl8app-help-tip dashicons dashicons-editor-help" title="<strong><?php _e( 'Previous Agreement Dates', 'pl8app' ); ?></strong><br /><?php foreach ( $privacy_timestamps as $timestamp ) { echo date_i18n( get_option( 'date_format' ) . ' H:i:s', $timestamp ); } ?>"></span>
				<?php endif; ?>
			<?php else: ?>
				<?php
				if ( empty( $last_payment_date ) ) {
					_e( 'No date found.', 'pl8app' );
				} else {
					echo date_i18n( get_option( 'date_format' ) . ' H:i:s', $last_payment_date );
					?>
					<span alt="f223" class="pl8app-help-tip dashicons dashicons-editor-help" title="<strong><?php _e( 'Estimated Privacy Policy Date', 'pl8app' ); ?></strong><br /><?php _e( 'This customer made a purchase prior to privacy policy dates being logged, this is the date of their last purchase. If your site was displaying the privacy policy checkbox at that time, this is our best estimate as to when they last agreed to your privacy policy.', 'pl8app' ); ?>"></span>
					<?php
				}
				?>
			<?php endif; ?>
		</span>

		<h3><?php _e( 'Notes', 'pl8app' ); ?></h3>

		<?php if ( 1 == $paged ) : ?>
		<div style="display: block; margin-bottom: 35px;">
			<form id="pl8app-add-customer-note" method="post" action="<?php echo admin_url( 'admin.php?page=pl8app-customers&view=notes&id=' . $customer->id ); ?>">
				<textarea id="customer-note" name="customer_note" class="customer-note-input" rows="10"></textarea>
				<br />
				<input type="hidden" id="customer-id" name="customer_id" value="<?php echo $customer->id; ?>" />
				<input type="hidden" name="pl8app_action" value="add-customer-note" />
				<?php wp_nonce_field( 'add-customer-note', 'add_customer_note_nonce', true, true ); ?>
				<input id="add-customer-note" class="right button-primary" type="submit" value="Add Note" />
			</form>
		</div>
		<?php endif; ?>

		<?php
		$pagination_args = array(
			'base'     => '%_%',
			'format'   => '?paged=%#%',
			'total'    => $total_pages,
			'current'  => $paged,
			'show_all' => true
		);

		echo paginate_links( $pagination_args );
		?>

		<div id="pl8app-customer-notes">
		<?php if ( count( $customer_notes ) > 0 ) : ?>
			<?php foreach( $customer_notes as $key => $note ) : ?>
				<div class="customer-note-wrapper dashboard-comment-wrap comment-item">
					<span class="note-content-wrap">
						<?php echo stripslashes( $note ); ?>
					</span>
				</div>
			<?php endforeach; ?>
		<?php else: ?>
			<div class="pl8app-no-customer-notes">
				<?php _e( 'No Customer Notes', 'pl8app' ); ?>
			</div>
		<?php endif; ?>
		</div>

		<?php echo paginate_links( $pagination_args ); ?>

	</div>

	<?php
}

function pl8app_customers_delete_view( $customer ) {
	$customer_edit_role = apply_filters( 'pl8app_edit_customers_role', 'edit_shop_payments' );

	?>

	<?php do_action( 'pl8app_customer_delete_top', $customer ); ?>

	<div class="info-wrapper customer-section">

		<form id="delete-customer" method="post" action="<?php echo admin_url( 'admin.php?page=pl8app-customers&view=delete&id=' . $customer->id ); ?>">

				<div class="pl8app-item-notes-header">
				<?php echo get_avatar( $customer->email, 30 ); ?> <span><?php echo $customer->name; ?></span>
			</div>


			<div class="customer-info delete-customer">

				<span class="delete-customer-options">
					<p>
						<?php echo PL8PRESS()->html->checkbox( array( 'name' => 'pl8app-customer-delete-confirm' ) ); ?>
						<label for="pl8app-customer-delete-confirm"><?php _e( 'Are you sure you want to delete this customer?', 'pl8app' ); ?></label>
					</p>

					<p>
						<?php echo PL8PRESS()->html->checkbox( array( 'name' => 'pl8app-customer-delete-records', 'options' => array( 'disabled' => true ) ) ); ?>
						<label for="pl8app-customer-delete-records"><?php _e( 'Delete all associated payments and records?', 'pl8app' ); ?></label>
					</p>

					<?php do_action( 'pl8app_customer_delete_inputs', $customer ); ?>
				</span>

				<span id="customer-edit-actions">
					<input type="hidden" name="customer_id" value="<?php echo $customer->id; ?>" />
					<?php wp_nonce_field( 'delete-customer', '_wpnonce', false, true ); ?>
					<input type="hidden" name="pl8app_action" value="delete-customer" />
					<input type="submit" disabled="disabled" id="pl8app-delete-customer" class="button-primary" value="<?php _e( 'Delete Customer', 'pl8app' ); ?>" />
					<a id="pl8app-delete-customer-cancel" href="<?php echo admin_url( 'admin.php?page=pl8app-customers&view=overview&id=' . $customer->id ); ?>" class="delete"><?php _e( 'Cancel', 'pl8app' ); ?></a>
				</span>

			</div>

		</form>
	</div>

	<?php

	do_action( 'pl8app_customer_delete_bottom', $customer );
}

function pl8app_customer_tools_view( $customer ) {
	$customer_edit_role = apply_filters( 'pl8app_edit_customers_role', 'edit_shop_payments' );

	?>

	<?php do_action( 'pl8app_customer_tools_top', $customer ); ?>

	<div class="info-wrapper customer-section">

		<div class="customer-notes-header">
			<?php echo get_avatar( $customer->email, 30 ); ?> <span><?php echo $customer->name; ?></span>
		</div>
		<h3><?php _e( 'Tools', 'pl8app' ); ?></h3>

		<div class="pl8app-item-info customer-info">
			<h4><?php _e( 'Recount Customer Stats', 'pl8app' ); ?></h4>
			<p class="pl8app-item-description"><?php _e( 'Use this tool to recalculate the purchase count and total value of the customer.', 'pl8app' ); ?></p>
			<form method="post" id="pl8app-tools-recount-form" class="pl8app-export-form pl8app-import-export-form">
				<span>
					<?php wp_nonce_field( 'pl8app_ajax_export', 'pl8app_ajax_export' ); ?>

					<input type="hidden" name="pl8app-export-class" data-type="recount-single-customer-stats" value="pl8app_Tools_Recount_Single_Customer_Stats" />
					<input type="hidden" name="customer_id" value="<?php echo $customer->id; ?>" />
					<input type="submit" id="recount-stats-submit" value="<?php _e( 'Recount Stats', 'pl8app' ); ?>" class="button-secondary"/>
					<span class="spinner"></span>

				</span>
			</form>

		</div>

	</div>

	<?php

	do_action( 'pl8app_customer_tools_bottom', $customer );
}

/**
 * Display a notice on customer account if they are pending verification
 *
 * @since  1.0.0
 * @return void
 */
function pl8app_verify_customer_notice( $customer ) {

	if ( ! pl8app_user_pending_verification( $customer->user_id ) ) {
		return;
	}

	$url = wp_nonce_url( admin_url( 'admin.php?page=pl8app-customers&view=overview&pl8app_action=verify_user_admin&id=' . $customer->id ), 'pl8app-verify-user' );

	echo '<div class="update error"><p>';
	_e( 'This customer\'s user account is pending verification.', 'pl8app' );
	echo ' ';
	echo '<a href="' . $url . '">' . __( 'Verify account.', 'pl8app' ) . '</a>';
	echo "\n\n";

	echo '</p></div>';
}
add_action( 'pl8app_customer_card_top', 'pl8app_verify_customer_notice', 10, 1 );

/**
 * Customer Email banned and No shows
 */
function pl8app_tools_banned_emails_display(){

    if( ! current_user_can( 'manage_shop_settings' ) ) {
        return;
    }

    do_action( 'pl8app_tools_banned_emails_before' );
    ?>
    <div class="wrap">
        <h2><?php _e( 'Customer No Shows and Blocking', 'pl8app' ); ?></h2>
        <div class="metabox-holder">
            <div class="postbox">

                <div class="inside">

                    <form method="post" action="<?php echo admin_url( 'admin.php?page=pl8app-customers-noshow-blocking' ); ?>">
                        <h3><span><?php _e( 'Banned Emails', 'pl8app' ); ?></span></h3>
                        <p><?php _e( 'Emails placed in the box below will not be allowed to make purchases.', 'pl8app' ); ?></p>
                        <p>
                            <textarea name="banned_emails" rows="10" class="large-text"><?php echo implode( "\n", pl8app_get_banned_emails() ); ?></textarea>
                            <span class="description"><?php _e( 'Enter emails and/or domains (starting with "@") and/or TLDs (starting with ".") to disallow, one per line.', 'pl8app' ); ?></span>
                        </p>
                        <h3><span><?php _e( 'Customer No Show Limit', 'pl8app' ); ?></span></h3>
                        <p>
                            <input type="number" name="customer_no_show_limit" class="text-center" value="<?PHP echo pl8app_get_customer_noshow_limit();?>">
                        <p class="description"><?php _e( '*Once customer reaches this limit of No shows Order, then his/her Email will be added to banned Email list automatically. 0 value means "Not limited"', 'pl8app' ); ?></p>
                        </p>
                        <p>
                            <input type="hidden" name="pl8app_action" value="save_banned_emails" />
                            <?php wp_nonce_field( 'pl8app_banned_emails_nonce', 'pl8app_banned_emails_nonce' ); ?>
                            <?php submit_button( __( 'Save', 'pl8app' ), 'secondary', 'submit', false ); ?>
                        </p>
                    </form>
                </div><!-- .inside -->
            </div><!-- .postbox -->
        </div>
    </div>

    <?php
    do_action( 'pl8app_tools_banned_emails_after' );
    do_action( 'pl8app_tools_after' );
}


