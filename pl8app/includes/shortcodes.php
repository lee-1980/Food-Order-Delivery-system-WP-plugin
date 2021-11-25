<?php


// Exit if accessed directly
if (!defined('ABSPATH')) exit;

/**
 * Process Profile Updater Form
 *
 * Processes the profile updater form by updating the necessary fields
 *
 * @since  1.0.0
 * @author pl8app
 * @param array $data Data sent from the profile editor
 * @return void
 */
function pl8app_process_profile_editor_updates($data)
{
    // Profile field change request
    if (empty($_POST['pl8app_profile_editor_submit']) && !is_user_logged_in()) {
        return false;
    }

    // Pending users can't edit their profile
    if (pl8app_user_pending_verification()) {
        return false;
    }

    // Nonce security
    if (!wp_verify_nonce($data['pl8app_profile_editor_nonce'], 'pl8app-profile-editor-nonce')) {
        return false;
    }

    $user_id = get_current_user_id();
    $old_user_data = get_userdata($user_id);

    $display_name = isset($data['pl8app_display_name']) ? sanitize_text_field($data['pl8app_display_name']) : $old_user_data->display_name;
    $first_name = isset($data['pl8app_first_name']) ? sanitize_text_field($data['pl8app_first_name']) : $old_user_data->first_name;
    $last_name = isset($data['pl8app_last_name']) ? sanitize_text_field($data['pl8app_last_name']) : $old_user_data->last_name;
    $email = isset($data['pl8app_email']) ? sanitize_email($data['pl8app_email']) : $old_user_data->user_email;
    $line1 = isset($data['pl8app_address_line1']) ? sanitize_text_field($data['pl8app_address_line1']) : '';
    $line2 = isset($data['pl8app_address_line2']) ? sanitize_text_field($data['pl8app_address_line2']) : '';
    $city = isset($data['pl8app_address_city']) ? sanitize_text_field($data['pl8app_address_city']) : '';
    $state = isset($data['pl8app_address_state']) ? sanitize_text_field($data['pl8app_address_state']) : '';
    $zip = isset($data['pl8app_address_zip']) ? sanitize_text_field($data['pl8app_address_zip']) : '';
    $country = isset($data['pl8app_address_country']) ? sanitize_text_field($data['pl8app_address_country']) : '';

    $userdata = array(
        'ID' => $user_id,
        'first_name' => $first_name,
        'last_name' => $last_name,
        'display_name' => $display_name,
        'user_email' => $email
    );


    $address = array(
        'line1' => $line1,
        'line2' => $line2,
        'city' => $city,
        'state' => $state,
        'zip' => $zip,
        'country' => $country
    );

    do_action('pl8app_pre_update_user_profile', $user_id, $userdata);

    // New password
    if (!empty($data['pl8app_new_user_pass1'])) {
        if ($data['pl8app_new_user_pass1'] !== $data['pl8app_new_user_pass2']) {
            pl8app_set_error('password_mismatch', __('The passwords you entered do not match. Please try again.', 'pl8app'));
        } else {
            $userdata['user_pass'] = $data['pl8app_new_user_pass1'];
        }
    }

    // Make sure the new email doesn't belong to another user
    if ($email != $old_user_data->user_email) {
        // Make sure the new email is valid
        if (!is_email($email)) {
            pl8app_set_error('email_invalid', __('The email you entered is invalid. Please enter a valid email.', 'pl8app'));
        }

        // Make sure the new email doesn't belong to another user
        if (email_exists($email)) {
            pl8app_set_error('email_exists', __('The email you entered belongs to another user. Please use another.', 'pl8app'));
        }
    }

    // Check for errors
    $errors = pl8app_get_errors();

    if ($errors) {
        // Send back to the profile editor if there are errors
        wp_redirect($data['pl8app_redirect']);
        pl8app_die();
    }

    // Update the user
    $meta = update_user_meta($user_id, '_pl8app_user_address', $address);
    $updated = wp_update_user($userdata);

    // Possibly update the customer
    $customer = new pl8app_Customer($user_id, true);
    if ($customer->email === $email || (is_array($customer->emails) && in_array($email, $customer->emails))) {
        $customer->set_primary_email($email);
    };

    if ($customer->id > 0) {
        $update_args = array(
            'name' => $first_name . ' ' . $last_name,
        );

        $customer->update($update_args);
    }

    if ($updated) {
        do_action('pl8app_user_profile_updated', $user_id, $userdata);
        wp_redirect(add_query_arg('updated', 'true', $data['pl8app_redirect']));
        pl8app_die();
    }
}

add_action('pl8app_edit_user_profile', 'pl8app_process_profile_editor_updates');

/**
 * Process the 'remove' URL on the profile editor when customers wish to remove an email address
 *
 * @since  1.0.0
 * @return void
 */
function pl8app_process_profile_editor_remove_email()
{
    if (!is_user_logged_in()) {
        return false;
    }

    // Pending users can't edit their profile
    if (pl8app_user_pending_verification()) {
        return false;
    }

    // Nonce security
    if (!wp_verify_nonce($_GET['_wpnonce'], 'pl8app-remove-customer-email')) {
        return false;
    }

    if (empty($_GET['email']) || !is_email($_GET['email'])) {
        return false;
    }

    $customer = new pl8app_Customer(get_current_user_id(), true);
    if ($customer->remove_email($_GET['email'])) {

        $url = add_query_arg('updated', true, $_GET['redirect']);

        $user = wp_get_current_user();
        $user_login = !empty($user->user_login) ? $user->user_login : 'pl8appBot';
        $customer_note = sprintf(__('Email address %s removed by %s', 'pl8app'), sanitize_email($_GET['email']), $user_login);
        $customer->add_note($customer_note);

    } else {
        pl8app_set_error('profile-remove-email-failure', __('Error removing email address from profile. Please try again later.', 'pl8app'));
        $url = $_GET['redirect'];
    }

    wp_safe_redirect($url);
    exit;
}

add_action('pl8app_profile-remove-email', 'pl8app_process_profile_editor_remove_email');
