<?php


/**
 * Output a message and login form on the profile editor when the
 * current visitor is not logged in.
 *
 * @since 1.0.0
 */
function pl8app_profile_editor_logged_out() {
	echo '<p class="pl8app-logged-out">' . esc_html__( 'You need to log in to edit your profile.', 'pl8app' ) . '</p>';
	echo pl8app_login_form(); // WPCS: XSS ok.
}
add_action( 'pl8app_profile_editor_logged_out', 'pl8app_profile_editor_logged_out' );

/**
 * Output a message on the login form when a user is already logged in.
 *
 * This remains mainly for backwards compatibility.
 *
 * @since 1.0.0
 */
function pl8app_login_form_logged_in() {
	echo '<p class="pl8app-logged-in">' . esc_html__( 'You are already logged in', 'pl8app' ) . '</p>';
}
add_action( 'pl8app_login_form_logged_in', 'pl8app_login_form_logged_in' );