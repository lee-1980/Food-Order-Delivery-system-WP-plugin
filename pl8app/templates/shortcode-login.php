<?php
/**
 * This template is used to display the login form with [pl8app_login]
 */
global $pl8app_login_redirect;

if ( ! is_user_logged_in() ) :

	$style = pl8app_get_option( 'button_style', 'button' );

	// Show any error messages after form submission
	pl8app_print_errors(); ?>
	<form id="pl8app_login_form" class="pl8app_form" action="" method="post">
		<fieldset>
			<legend><?php _e( 'Log into Your Account', 'pl8app' ); ?></legend>
			<?php do_action( 'pl8app_login_fields_before' ); ?>
			<p class="pl8app-login-username">
				<label for="pl8app_user_login"><?php _e( 'Username or Email', 'pl8app' ); ?></label>
				<input name="pl8app_user_login" id="pl8app_user_login" class="pl8app-required pl8app-input" type="text"/>
			</p>
			<p class="pl8app-login-password">
				<label for="pl8app_user_pass"><?php _e( 'Password', 'pl8app' ); ?></label>
				<input name="pl8app_user_pass" id="pl8app_user_pass" class="pl8app-password pl8app-required pl8app-input" type="password"/>
			</p>
			<p class="pl8app-login-remember">
				<label><input name="rememberme" type="checkbox" id="rememberme" value="forever" /> <?php _e( 'Remember Me', 'pl8app' ); ?></label>
			</p>
			<p class="pl8app-login-submit">
				<input type="hidden" name="pl8app_redirect" value="<?php echo esc_url( $pl8app_login_redirect ); ?>"/>
				<input type="hidden" name="pl8app_login_nonce" value="<?php echo wp_create_nonce( 'pl8app-login-nonce' ); ?>"/>
				<input type="hidden" name="pl8app_action" value="user_login"/>


				<input type="submit" class="pl8app-submit <?php echo $style; ?>" id="pl8app_login_submit"  value="<?php _e( 'Log In', 'pl8app' ); ?>"/>
			</p>
			<p class="pl8app-lost-password">
				<a href="<?php echo wp_lostpassword_url(); ?>">
					<?php _e( 'Lost Password?', 'pl8app' ); ?>
				</a>
			</p>
			<?php do_action( 'pl8app_login_fields_after' ); ?>
		</fieldset>
	</form>
<?php else : ?>

	<?php do_action( 'pl8app_login_form_logged_in' ); ?>

<?php endif; ?>
