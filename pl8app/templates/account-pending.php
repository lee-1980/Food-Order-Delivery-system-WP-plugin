<?php if( ! empty( $_GET['pl8app-verify-request'] ) ) : ?>
  <p class="pl8app-account-pending pl8app_success">
	 <?php _e( 'An email with an activation link has been sent.', 'pl8app' ); ?>
  </p>
<?php endif; ?>
  <p class="pl8app-account-pending">
		<?php $url = esc_url( pl8app_get_user_verification_request_url() ); ?>
		<?php printf( __( 'Your account is pending verification. Please click the link in your email to activate your account. No email? <a href="%s">Click here</a> to send a new activation code.', 'pl8app' ), $url ); ?>
	</p>