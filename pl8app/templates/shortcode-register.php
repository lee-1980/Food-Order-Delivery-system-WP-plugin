<?php
/**
 * This template is used to display the registration form with [pl8app_register]
 */
global $pl8app_register_redirect;

do_action( 'pl8app_print_errors' );

if ( ! is_user_logged_in() ) :

	$style = pl8app_get_option( 'button_style', 'button' ); ?>


	<form id="pl8app_register_form" class="pl8app_form" action="" method="post">

		<?php do_action( 'pl8app_register_form_fields_top' ); ?>

		<fieldset>
			<legend><?php _e( 'Register New User', 'pl8app' ); ?></legend>

			<?php do_action( 'pl8app_register_form_fields_before' ); ?>

			<p>
				<label for="pl8app-user-login"><?php _e( 'Username', 'pl8app' ); ?></label>
				<input id="pl8app-user-login" class="required pl8app-input" type="text" name="pl8app_user_login" />
			</p>

			<p>
				<label for="pl8app-user-email"><?php _e( 'Email', 'pl8app' ); ?></label>
				<input id="pl8app-user-email" class="required pl8app-input" type="email" name="pl8app_user_email" />
			</p>

			<p>
				<label for="pl8app-user-pass"><?php _e( 'Password', 'pl8app' ); ?></label>
				<input id="pl8app-user-pass" class="password required pl8app-input" type="password" name="pl8app_user_pass" />
			</p>

			<p>
				<label for="pl8app-user-pass2"><?php _e( 'Confirm Password', 'pl8app' ); ?></label>
				<input id="pl8app-user-pass2" class="password required pl8app-input" type="password" name="pl8app_user_pass2" />
			</p>


			<?php do_action( 'pl8app_register_form_fields_before_submit' ); ?>

			<p>
				<input type="hidden" name="pl8app_honeypot" value="" />
				<input type="hidden" name="pl8app_action" value="user_register" />
				<input type="hidden" name="pl8app_redirect" value="<?php echo esc_url( $pl8app_register_redirect ); ?>"/>

				<input type="submit" class="pl8app-submit <?php echo $style; ?>" id="pl8app-purchase-button" name="pl8app_register_submit" value="<?php esc_attr_e( 'Register', 'pl8app' ); ?>"/>
			</p>

			<?php do_action( 'pl8app_register_form_fields_after' ); ?>
		</fieldset>

		<?php do_action( 'pl8app_register_form_fields_bottom' ); ?>
	</form>

<?php else : ?>

	<?php do_action( 'pl8app_register_form_logged_in' ); ?>

<?php endif; ?>
