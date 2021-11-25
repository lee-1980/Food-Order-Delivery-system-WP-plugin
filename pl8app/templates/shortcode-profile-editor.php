<?php
/**
 * This template is used to display the profile editor with [pl8app_profile_editor]
 */
global $current_user;

if ( is_user_logged_in() ):
	$user_id      = get_current_user_id();
	$first_name   = get_user_meta( $user_id, 'first_name', true );
	$last_name    = get_user_meta( $user_id, 'last_name', true );
	$display_name = $current_user->display_name;
	$address      = pl8app_get_customer_address( $user_id );
	$states       = pl8app_get_states( $address['country'] );
	$state 		  = $address['state'];

	if ( pl8app_is_cart_saved() ): ?>
		<?php $restore_url = add_query_arg( array( 'pl8app_action' => 'restore_cart', 'pl8app_cart_token' => pl8app_get_cart_token() ), pl8app_get_checkout_uri() ); ?>
		<div class="pl8app_success pl8app-alert pl8app-alert-success"><strong><?php _e( 'Saved cart','pl8app' ); ?>:</strong> <?php printf( __( 'You have a saved cart, <a href="%s">click here</a> to restore it.', 'pl8app' ), esc_url( $restore_url ) ); ?></div>
	<?php endif; ?>

	<?php if ( isset( $_GET['updated'] ) && $_GET['updated'] == true && ! pl8app_get_errors() ): ?>
		<div class="pl8app_success pl8app-alert pl8app-alert-success"><strong><?php _e( 'Success','pl8app' ); ?>:</strong> <?php _e( 'Your profile has been edited successfully.', 'pl8app' ); ?></div>
	<?php endif; ?>

	<?php pl8app_print_errors(); ?>

	<?php do_action( 'pl8app_profile_editor_before' ); ?>

	<form id="pl8app_profile_editor_form" class="pl8app_form" action="<?php echo pl8app_get_current_page_url(); ?>" method="post">

		<?php do_action( 'pl8app_profile_editor_fields_top' ); ?>

		<fieldset id="pl8app_profile_personal_fieldset">

			<legend id="pl8app_profile_name_label"><?php _e( 'Change your Name', 'pl8app' ); ?></legend>

			<p id="pl8app_profile_first_name_wrap">
				<label for="pl8app_first_name"><?php _e( 'First Name', 'pl8app' ); ?></label>
				<input name="pl8app_first_name" id="pl8app_first_name" class="text pl8app-input" type="text" value="<?php echo esc_attr( $first_name ); ?>" />
			</p>

			<p id="pl8app_profile_last_name_wrap">
				<label for="pl8app_last_name"><?php _e( 'Last Name', 'pl8app' ); ?></label>
				<input name="pl8app_last_name" id="pl8app_last_name" class="text pl8app-input" type="text" value="<?php echo esc_attr( $last_name ); ?>" />
			</p>

			<p id="pl8app_profile_display_name_wrap">
				<label for="pl8app_display_name"><?php _e( 'Display Name', 'pl8app' ); ?></label>
				<select name="pl8app_display_name" id="pl8app_display_name" class="select pl8app-select">
					<?php if ( ! empty( $current_user->first_name ) ): ?>
					<option <?php selected( $display_name, $current_user->first_name ); ?> value="<?php echo esc_attr( $current_user->first_name ); ?>"><?php echo esc_html( $current_user->first_name ); ?></option>
					<?php endif; ?>
					<option <?php selected( $display_name, $current_user->user_nicename ); ?> value="<?php echo esc_attr( $current_user->user_nicename ); ?>"><?php echo esc_html( $current_user->user_nicename ); ?></option>
					<?php if ( ! empty( $current_user->last_name ) ): ?>
					<option <?php selected( $display_name, $current_user->last_name ); ?> value="<?php echo esc_attr( $current_user->last_name ); ?>"><?php echo esc_html( $current_user->last_name ); ?></option>
					<?php endif; ?>
					<?php if ( ! empty( $current_user->first_name ) && ! empty( $current_user->last_name ) ): ?>
					<option <?php selected( $display_name, $current_user->first_name . ' ' . $current_user->last_name ); ?> value="<?php echo esc_attr( $current_user->first_name . ' ' . $current_user->last_name ); ?>"><?php echo esc_html( $current_user->first_name . ' ' . $current_user->last_name ); ?></option>
					<option <?php selected( $display_name, $current_user->last_name . ' ' . $current_user->first_name ); ?> value="<?php echo esc_attr( $current_user->last_name . ' ' . $current_user->first_name ); ?>"><?php echo esc_html( $current_user->last_name . ' ' . $current_user->first_name ); ?></option>
					<?php endif; ?>
				</select>
				<?php do_action( 'pl8app_profile_editor_name' ); ?>
			</p>

			<?php do_action( 'pl8app_profile_editor_after_name' ); ?>

			<p id="pl8app_profile_primary_email_wrap">
				<label for="pl8app_email"><?php _e( 'Primary Email Address', 'pl8app' ); ?></label>
				<?php $customer = new pl8app_Customer( $user_id, true ); ?>
				<?php if ( $customer->id > 0 ) : ?>

					<?php if ( 1 === count( $customer->emails ) ) : ?>
						<input name="pl8app_email" id="pl8app_email" class="text pl8app-input required" type="email" value="<?php echo esc_attr( $customer->email ); ?>" />
					<?php else: ?>
						<?php
							$emails           = array();
							$customer->emails = array_reverse( $customer->emails, true );

							foreach ( $customer->emails as $email ) {
								$emails[ $email ] = $email;
							}

							$email_select_args = array(
								'options'          => $emails,
								'name'             => 'pl8app_email',
								'id'               => 'pl8app_email',
								'selected'         => $customer->email,
								'show_option_none' => false,
								'show_option_all'  => false,
							);

							echo PL8PRESS()->html->select( $email_select_args );
						?>
					<?php endif; ?>
				<?php else: ?>
					<input name="pl8app_email" id="pl8app_email" class="text pl8app-input required" type="email" value="<?php echo esc_attr( $current_user->user_email ); ?>" />
				<?php endif; ?>

				<?php do_action( 'pl8app_profile_editor_email' ); ?>
			</p>

			<?php if ( $customer->id > 0 && count( $customer->emails ) > 1 ) : ?>
				<p id="pl8app_profile_emails_wrap">
					<label for="pl8app_emails"><?php _e( 'Additional Email Addresses', 'pl8app' ); ?></label>
					<ul class="pl8app-profile-emails">
					<?php foreach ( $customer->emails as $email ) : ?>
						<?php if ( $email === $customer->email ) { continue; } ?>
						<li class="pl8app-profile-email">
							<?php echo $email; ?>
							<span class="actions">
								<?php
									$remove_url = wp_nonce_url(
										add_query_arg(
											array(
												'email'      => rawurlencode( $email ),
												'pl8app_action' => 'profile-remove-email',
												'redirect'   => esc_url( pl8app_get_current_page_url() ),
											)
										),
										'pl8app-remove-customer-email'
									);
								?>
								<a href="<?php echo $remove_url ?>" class="delete"><?php _e( 'Remove', 'pl8app' ); ?></a>
							</span>
						</li>
					<?php endforeach; ?>
					</ul>
				</p>
			<?php endif; ?>

			<?php do_action( 'pl8app_profile_editor_after_email' ); ?>

		</fieldset>

		<?php do_action( 'pl8app_profile_editor_after_personal_fields' ); ?>

		<fieldset id="pl8app_profile_address_fieldset">

			<legend id="pl8app_profile_billing_address_label"><?php _e( 'Change your Billing Address', 'pl8app' ); ?></legend>

			<p id="pl8app_profile_billing_address_line_1_wrap">
				<label for="pl8app_address_line1"><?php _e( 'Line 1', 'pl8app' ); ?></label>
				<input name="pl8app_address_line1" id="pl8app_address_line1" class="text pl8app-input" type="text" value="<?php echo esc_attr( $address['line1'] ); ?>" />
			</p>

			<p id="pl8app_profile_billing_address_line_2_wrap">
				<label for="pl8app_address_line2"><?php _e( 'Line 2', 'pl8app' ); ?></label>
				<input name="pl8app_address_line2" id="pl8app_address_line2" class="text pl8app-input" type="text" value="<?php echo esc_attr( $address['line2'] ); ?>" />
			</p>

			<p id="pl8app_profile_billing_address_city_wrap">
				<label for="pl8app_address_city"><?php _e( 'City', 'pl8app' ); ?></label>
				<input name="pl8app_address_city" id="pl8app_address_city" class="text pl8app-input" type="text" value="<?php echo esc_attr( $address['city'] ); ?>" />
			</p>

			<p id="pl8app_profile_billing_address_postal_wrap">
				<label for="pl8app_address_zip"><?php _e( 'Zip / Postal Code', 'pl8app' ); ?></label>
				<input name="pl8app_address_zip" id="pl8app_address_zip" class="text pl8app-input" type="text" value="<?php echo esc_attr( $address['zip'] ); ?>" />
			</p>

			<p id="pl8app_profile_billing_address_country_wrap">
				<label for="pl8app_address_country"><?php _e( 'Country', 'pl8app' ); ?></label>
				<select name="pl8app_address_country" id="pl8app_address_country" class="select pl8app-select pl8app-form-control">
					<?php foreach( pl8app_get_country_list() as $key => $country ) : ?>
					<option value="<?php echo $key; ?>"<?php selected( $address['country'], $key ); ?>><?php echo esc_html( $country ); ?></option>
					<?php endforeach; ?>
				</select>
			</p>

			<p id="pl8app_profile_billing_address_state_wrap">
				<label for="pl8app_address_state"><?php _e( 'State / Province', 'pl8app' ); ?></label>
				<?php if( ! empty( $states ) ) : ?>
					<select name="pl8app_address_state" id="pl8app_address_state" class="select pl8app-select pl8app-form-control">
						<?php
							foreach( $states as $state_code => $state_name ) {
								echo '<option value="' . $state_code . '"' . selected( $state_code, $state, false ) . '>' . $state_name . '</option>';
							}
						?>
					</select>
				<?php else : ?>
					<input name="pl8app_address_state" id="pl8app_address_state" class="text pl8app-input" type="text" value="<?php echo esc_attr( $state ); ?>" />
				<?php endif; ?>

				<?php do_action( 'pl8app_profile_editor_address' ); ?>
			</p>

			<?php do_action( 'pl8app_profile_editor_after_address' ); ?>

		</fieldset>

		<?php do_action( 'pl8app_profile_editor_after_address_fields' ); ?>

		<fieldset id="pl8app_profile_password_fieldset">

			<legend id="pl8app_profile_password_label"><?php _e( 'Change your Password', 'pl8app' ); ?></legend>

			<p id="pl8app_profile_password_wrap">
				<label for="pl8app_user_pass"><?php _e( 'New Password', 'pl8app' ); ?></label>
				<input name="pl8app_new_user_pass1" id="pl8app_new_user_pass1" class="password pl8app-input" type="password"/>
			</p>

			<p id="pl8app_profile_confirm_password_wrap">
				<label for="pl8app_user_pass"><?php _e( 'Re-enter Password', 'pl8app' ); ?></label>
				<input name="pl8app_new_user_pass2" id="pl8app_new_user_pass2" class="password pl8app-input" type="password"/>
				<?php do_action( 'pl8app_profile_editor_password' ); ?>
			</p>

			<?php do_action( 'pl8app_profile_editor_after_password' ); ?>

		</fieldset>

		<?php do_action( 'pl8app_profile_editor_after_password_fields' ); ?>

		<fieldset id="pl8app_profile_submit_fieldset">

			<p id="pl8app_profile_submit_wrap">
				<input type="hidden" name="pl8app_profile_editor_nonce" value="<?php echo wp_create_nonce( 'pl8app-profile-editor-nonce' ); ?>"/>
				<input type="hidden" name="pl8app_action" value="edit_user_profile" />
				<input type="hidden" name="pl8app_redirect" value="<?php echo esc_url( pl8app_get_current_page_url() ); ?>" />
				<input name="pl8app_profile_editor_submit" id="pl8app_profile_editor_submit" type="submit" class="pl8app_submit pl8app-submit" value="<?php _e( 'Save Changes', 'pl8app' ); ?>"/>
			</p>

		</fieldset>

		<?php do_action( 'pl8app_profile_editor_fields_bottom' ); ?>

	</form><!-- #pl8app_profile_editor_form -->

	<?php do_action( 'pl8app_profile_editor_after' ); ?>

	<?php
else:
	do_action( 'pl8app_profile_editor_logged_out' );
endif;
