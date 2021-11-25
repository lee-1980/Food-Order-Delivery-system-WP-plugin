<?php
/**
 * Register Settings
 *
 * @package     pl8app
 * @subpackage  Admin/Settings
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Get an option
 *
 * Looks to see if the specified setting exists, returns default if not
 *
 * @since 1.0
 * @global $pl8app_options Array of all the pl8app Options
 * @return mixed
 */
function pl8app_get_option( $key = '', $default = false ) {
    global $pl8app_options;
    $value = ! empty( $pl8app_options[ $key ] ) ? $pl8app_options[ $key ] : $default;
    $value = apply_filters( 'pl8app_get_option', $value, $key, $default );
    return apply_filters( 'pl8app_get_option_' . $key, $value, $key, $default );
}

/**
 * Update an option
 *
 * Updates an pl8app setting value in both the db and the global variable.
 * Warning: Passing in an empty, false or null string value will remove
 *          the key from the pl8app_options array.
 *
 * @since 1.0
 * @param string $key The Key to update
 * @param string|bool|int $value The value to set the key to
 * @global $pl8app_options Array of all the pl8app Options
 * @return boolean True if updated, false if not.
 */

function pl8app_update_option( $key = '', $value = false ) {

    // If no key, exit
    if ( empty( $key ) ){
        return false;
    }

    if ( empty( $value ) ) {
        $remove_option = pl8app_delete_option( $key );
        return $remove_option;
    }

    // First let's grab the current settings
    $options = get_option( 'pl8app_settings' );

    // Let's let devs alter that value coming in
    $value = apply_filters( 'pl8app_update_option', $value, $key );

    // Next let's try to update the value
    $options[ $key ] = $value;
    $did_update = update_option( 'pl8app_settings', $options );

    // If it updated, let's update the global variable
    if ( $did_update ){
        global $pl8app_options;
        $pl8app_options[ $key ] = $value;

    }

    return $did_update;
}

/**
 * Remove an option
 *
 * Removes an pl8app setting value in both the db and the global variable.
 *
 * @since 1.0
 * @param string $key The Key to delete
 * @global $pl8app_options Array of all the pl8app Options
 * @return boolean True if removed, false if not.
 */

function pl8app_delete_option( $key = '' ) {
    global $pl8app_options;

    // If no key, exit
    if ( empty( $key ) ){
        return false;
    }

    // First let's grab the current settings
    $options = get_option( 'pl8app_settings' );

    // Next let's try to update the value
    if( isset( $options[ $key ] ) ) {
        unset( $options[ $key ] );
    }

    // Remove this option from the global pl8app settings to the array_merge in pl8app_settings_sanitize() doesn't re-add it.
    if( isset( $pl8app_options[ $key ] ) ) {
        unset( $pl8app_options[ $key ] );
    }

    $did_update = update_option( 'pl8app_settings', $options );

    // If it updated, let's update the global variable
    if ( $did_update ){
        global $pl8app_options;
        $pl8app_options = $options;
    }

    return $did_update;
}
/**
 * Get Settings
 *
 * Retrieves all plugin settings
 *
 * @since 1.0
 * @return array pl8app settings
 */
function pl8app_get_settings() {

    $settings = get_option( 'pl8app_settings' );

    if( empty( $settings ) ) {

        // Update old settings with new single option
        $general_settings = is_array( get_option( 'pl8app_settings_general' ) )    ? get_option( 'pl8app_settings_general' )    : array();
        $gateway_settings = is_array( get_option( 'pl8app_settings_gateways' ) )   ? get_option( 'pl8app_settings_gateways' )   : array();
        $email_settings   = is_array( get_option( 'pl8app_settings_emails' ) )     ? get_option( 'pl8app_settings_emails' )     : array();
        $style_settings   = is_array( get_option( 'pl8app_settings_styles' ) )     ? get_option( 'pl8app_settings_styles' )     : array();
        $tax_settings     = is_array( get_option( 'pl8app_settings_taxes' ) )      ? get_option( 'pl8app_settings_taxes' )      : array();
        $misc_settings    = is_array( get_option( 'pl8app_settings_misc' ) )       ? get_option( 'pl8app_settings_misc' )       : array();

        $settings = array_merge( $general_settings, $gateway_settings, $email_settings, $style_settings, $tax_settings, $misc_settings );
        update_option( 'pl8app_settings', $settings );

    }
    return apply_filters( 'pl8app_get_settings', $settings );
}

/**
 * Add all settings sections and fields
 *
 * @since 1.0
 * @return void
 */
function pl8app_register_settings() {

    if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
        return;
    }

    if ( false == get_option( 'pl8app_settings' ) ) {
        add_option( 'pl8app_settings' );
    }

    $registered_settings = pl8app_get_registered_settings();

    if( is_array( $registered_settings ) && !empty( $registered_settings ) ) {

        foreach ( $registered_settings as $tab => $sections ) {

            foreach ( $sections as $section => $settings) {

                // Check for backwards compatibility
                $section_tabs = pl8app_get_settings_tab_sections( $tab );
                if ( ! is_array( $section_tabs ) || ! array_key_exists( $section, $section_tabs ) ) {
                    $section = 'main';
                    $settings = $sections;
                }

                add_settings_section(
                    'pl8app_settings_' . $tab . '_' . $section,
                    __return_null(),
                    '__return_false',
                    'pl8app_settings_' . $tab . '_' . $section
                );

                foreach ( $settings as $option ) {

                    // For backwards compatibility
                    if ( empty( $option['id'] ) ) {
                        continue;
                    }

                    $args = wp_parse_args( $option, array(
                        'section'       => $section,
                        'id'            => null,
                        'desc'          => '',
                        'name'          => '',
                        'size'          => null,
                        'options'       => '',
                        'std'           => '',
                        'min'           => null,
                        'max'           => null,
                        'step'          => null,
                        'chosen'        => null,
                        'multiple'      => null,
                        'placeholder'   => null,
                        'allow_blank'   => true,
                        'readonly'      => false,
                        'faux'          => false,
                        'tooltip_title' => false,
                        'tooltip_desc'  => false,
                        'field_class'   => '',
                    ) );

                    add_settings_field(
                        'pl8app_settings[' . $args['id'] . ']',
                        $args['name'],
                        function_exists( 'pl8app_' . $args['type'] . '_callback' ) ? 'pl8app_' . $args['type'] . '_callback' : 'pl8app_missing_callback',
                        'pl8app_settings_' . $tab . '_' . $section,
                        'pl8app_settings_' . $tab . '_' . $section,
                        $args
                    );
                }
            }
        }
    }

    // Creates our settings in the options table
    register_setting( 'pl8app_settings', 'pl8app_settings', 'pl8app_settings_sanitize' );

}
add_action( 'admin_init', 'pl8app_register_settings' );

/**
 * Retrieve the array of plugin settings
 *
 * @since 1.0
 * @return array
 */
function pl8app_get_registered_settings() {

    /**
     * 'Whitelisted' pl8app settings, filters are provided for each settings
     * section to allow extensions and other plugins to add their own settings
     */

    $shop_states = pl8app_get_states( pl8app_get_shop_country() );

    $pl8app_settings = array(
        /** General Settings */
        'general' => apply_filters( 'pl8app_settings_general',
            array()
        ),

    );

    $payment_statuses = pl8app_get_payment_statuses();


    if ( ! pl8app_shop_supports_buy_now() ) {
        $pl8app_settings['misc']['button_text']['buy_now_text']['disabled']      = true;
        $pl8app_settings['misc']['button_text']['buy_now_text']['tooltip_title'] = __( 'Buy Now Disabled', 'pl8app' );
        $pl8app_settings['misc']['button_text']['buy_now_text']['tooltip_desc']  = __( 'Buy Now buttons are only available for stores that have a single supported gateway active and that do not use taxes.', 'pl8app' );
    }
    return apply_filters( 'pl8app_registered_settings', $pl8app_settings );
}

/**
 * Settings Sanitization
 *
 * Adds a settings error (for the updated message)
 * At some point this will validate input
 *
 * @since 1.0
 *
 * @param array $input The value inputted in the field
 * @global array $pl8app_options Array of all the pl8app Options
 *
 * @return string $input Sanitized value
 */

function pl8app_settings_sanitize( $input = array() ) {
    global $pl8app_options;

    $doing_section = false;
    if ( ! empty( $_POST['_wp_http_referer'] ) ) {
        $doing_section = true;
    }

    $setting_types = pl8app_get_registered_settings_types();
    $input         = $input ? $input : array();

    if ( $doing_section ) {

        // Pull out the tab and section
        parse_str( $_POST['_wp_http_referer'], $referrer );
        $tab      = isset( $referrer['tab'] ) ? $referrer['tab'] : 'general';
        $section  = isset( $referrer['section'] ) ? $referrer['section'] : 'main';

        if ( ! empty( $_POST['pl8app_section_override'] ) ) {
            $section = sanitize_text_field( $_POST['pl8app_section_override'] );
        }

        $setting_types = pl8app_get_registered_settings_types( $tab, $section );

        // Run a general sanitization for the tab for special fields (like taxes)
        $input = apply_filters( 'pl8app_settings_' . $tab . '_sanitize', $input );

        // Run a general sanitization for the section so custom tabs with sub-sections can save special data
        $input = apply_filters( 'pl8app_settings_' . $tab . '-' . $section . '_sanitize', $input );

    }

    // Merge our new settings with the existing
    $output = array_merge( $pl8app_options, $input );
    foreach ( $setting_types as $key => $type ) {

        if ( empty( $type ) ) {
            continue;
        }

        // Some setting types are not actually settings, just keep moving along here
        $non_setting_types = apply_filters( 'pl8app_non_setting_types', array(
            'header', 'descriptive_text', 'hook',
        ) );

        if ( in_array( $type, $non_setting_types ) ) {
            continue;
        }

        if ( array_key_exists( $key, $output ) ) {
            $output[ $key ] = apply_filters( 'pl8app_settings_sanitize_' . $type, $output[ $key ], $key );
            $output[ $key ] = apply_filters( 'pl8app_settings_sanitize', $output[ $key ], $key );
        }

        if ( $doing_section ) {
            switch( $type ) {
                case 'checkbox':
                case 'gateways':
                case 'multicheck':
                case 'payment_icons':
                    if ( array_key_exists( $key, $input ) && $output[ $key ] === '-1' ) {
                        unset( $output[ $key ] );
                    }
                    break;
                case 'text':
                    if ( array_key_exists( $key, $input ) && empty( $input[ $key ] ) ) {
                        unset( $output[ $key ] );
                    }
                    break;
                default:
                    if ( array_key_exists( $key, $input ) && empty( $input[ $key ] ) || ( array_key_exists( $key, $output ) && ! array_key_exists( $key, $input ) ) ) {
                        unset( $output[ $key ] );
                    }
                    break;
            }
        } else {
            if ( empty( $input[ $key ] ) ) {
                unset( $output[ $key ] );
            }
        }

    }

    $output= apply_filters( 'pl8app_settings_sanitize_custom_addition', $output);

    if ( $doing_section ) {
        add_settings_error( 'pl8app-notices', '', __( 'Settings updated.', 'pl8app' ), 'updated' );
    }

    return $output;
}

/**
 * Flattens the set of registered settings and their type so we can easily sanitize all the settings
 * in a much cleaner set of logic in pl8app_settings_sanitize
 *
 * @since  1.0.0.5
 * @since 1.0.0 - Added the ability to filter setting types by tab and section
 *
 * @param $filtered_tab bool|string     A tab to filter setting types by.
 * @param $filtered_section bool|string A section to filter setting types by.
 * @return array Key is the setting ID, value is the type of setting it is registered as
 */
function pl8app_get_registered_settings_types( $filtered_tab = false, $filtered_section = false ) {
    $settings      = pl8app_get_registered_settings();
    $setting_types = array();
    foreach ( $settings as $tab_id => $tab ) {

        if ( false !== $filtered_tab && $filtered_tab !== $tab_id ) {
            continue;
        }

        foreach ( $tab as $section_id => $section_or_setting ) {

            // See if we have a setting registered at the tab level for backwards compatibility
            if ( false !== $filtered_section && is_array( $section_or_setting ) && array_key_exists( 'type', $section_or_setting ) ) {
                $setting_types[ $section_or_setting['id'] ] = $section_or_setting['type'];
                continue;
            }

            if ( false !== $filtered_section && $filtered_section !== $section_id ) {
                continue;
            }

            foreach ( $section_or_setting as $section => $section_settings ) {

                if ( ! empty( $section_settings['type'] ) ) {
                    $setting_types[ $section_settings['id'] ] = $section_settings['type'];
                }

            }

        }

    }

    return $setting_types;
}

/**
 * Taxes Settings Sanitization
 *
 * Adds a settings error (for the updated message)
 * This also saves the tax rates table
 *
 * @since  1.0.0
 * @param array $input The value inputted in the field
 * @return string $input Sanitized value
 */
function pl8app_settings_sanitize_taxes( $input ) {

    if( ! current_user_can( 'manage_shop_settings' ) ) {
        return $input;
    }

    if( ! isset( $_POST['tax_rates'] ) ) {
        return $input;
    }

    $new_rates = ! empty( $_POST['tax_rates'] ) ? array_values( $_POST['tax_rates'] ) : array();

    update_option( 'pl8app_tax_rates', $new_rates );

    return $input;
}
add_filter( 'pl8app_settings_taxes_sanitize', 'pl8app_settings_sanitize_taxes' );

/**
 * Payment Gateways Settings Sanitization
 *
 * Adds a settings error (for the updated message)
 *
 * @since 1.0
 * @param array $input The value inputted in the field
 * @return string $input Sanitized value
 */
function pl8app_settings_sanitize_gateways( $input ) {

    if ( ! current_user_can( 'manage_shop_settings' ) || empty( $input['default_gateway'] ) ) {
        return $input;
    }

    if ( empty( $input['gateways'] ) || '-1' == $input['gateways'] )  {

        add_settings_error( 'pl8app-notices', '', __( 'Error setting default gateway. No gateways are enabled.', 'pl8app' ) );
        unset( $input['default_gateway'] );

    } else if ( ! array_key_exists( $input['default_gateway'], $input['gateways'] ) ) {

        $enabled_gateways = $input['gateways'];
        $all_gateways     = pl8app_get_payment_gateways();
        $selected_default = $all_gateways[ $input['default_gateway'] ];

        reset( $enabled_gateways );
        $first_gateway = key( $enabled_gateways );

        if ( $first_gateway ) {
            add_settings_error( 'pl8app-notices', '', sprintf( __( '%s could not be set as the default gateway. It must first be enabled.', 'pl8app' ), $selected_default['admin_label'] ), 'error' );
            $input['default_gateway'] = $first_gateway;
        }

    }

    return $input;
}
add_filter( 'pl8app_settings_gateways_sanitize', 'pl8app_settings_sanitize_gateways' );

/**
 * Sanitize text fields
 *
 * @since 1.0
 * @param array $input The field value
 * @return string $input Sanitized value
 */
function pl8app_sanitize_text_field( $input ) {
    $tags = array(
        'p' => array(
            'class' => array(),
            'id'    => array(),
        ),
        'span' => array(
            'class' => array(),
            'id'    => array(),
        ),
        'a' => array(
            'href'   => array(),
            'target' => array(),
            'title'  => array(),
            'class'  => array(),
            'id'     => array(),
        ),
        'strong' => array(),
        'em' => array(),
        'br' => array(),
        'img' => array(
            'src'   => array(),
            'title' => array(),
            'alt'   => array(),
            'id'    => array(),
        ),
        'div' => array(
            'class' => array(),
            'id'    => array(),
        ),
        'ul' => array(
            'class' => array(),
            'id'    => array(),
        ),
        'li' => array(
            'class' => array(),
            'id'    => array(),
        )
    );

    $allowed_tags = apply_filters( 'pl8app_allowed_html_tags', $tags );

    return trim( wp_kses( $input, $allowed_tags ) );
}
add_filter( 'pl8app_settings_sanitize_text', 'pl8app_sanitize_text_field' );

/**
 * Sanitize HTML Class Names
 *
 * @since 1.0.0.11
 * @param  string|array $class HTML Class Name(s)
 * @return string $class
 */
function pl8app_sanitize_html_class( $class = '' ) {

    if ( is_string( $class ) ) {
        $class = sanitize_html_class( $class );
    } else if ( is_array( $class ) ) {
        $class = array_values( array_map( 'sanitize_html_class', $class ) );
        $class = implode( ' ', array_unique( $class ) );
    }

    return $class;

}

/**
 * Retrieve settings tabs
 *
 * @since 1.0
 * @return array $tabs
 */
function pl8app_get_settings_tabs() {

    $settings = pl8app_get_registered_settings();

    $tabs             = array();

    return apply_filters( 'pl8app_settings_tabs', $tabs );
}

/**
 * Retrieve settings tabs
 *
 * @since  1.0.0
 * @return array $section
 */
function pl8app_get_settings_tab_sections( $tab = false ) {

    $tabs     = array();
    $sections = pl8app_get_registered_settings_sections();

    if( $tab && ! empty( $sections[ $tab ] ) ) {
        $tabs = $sections[ $tab ];
    } else if ( $tab ) {
        $tabs = array();
    }

    return $tabs;
}

/**
 * Get the settings sections for each tab
 * Uses a static to avoid running the filters on every request to this function
 *
 * @since  1.0.0
 * @return array Array of tabs and sections
 */
function pl8app_get_registered_settings_sections() {

    static $sections = false;

    if ( false !== $sections ) {
        return $sections;
    }

    $sections = array(
        'general'    => apply_filters( 'pl8app_settings_sections_general', array(
        ) ),
        'gateways'   => apply_filters( 'pl8app_settings_sections_gateways', array(
            'main'               => __( 'General', 'pl8app' ),
            'paypal'             => __( 'PayPal Standard', 'pl8app' ),
        ) ),
        'emails'     => apply_filters( 'pl8app_settings_sections_emails', array(
            'main'               => __( 'General', 'pl8app' ),
            'order_notifications' => __( 'Order Notifications', 'pl8app' ),
        ) ),
        'styles'     => apply_filters( 'pl8app_settings_sections_styles', array(
            'main'               => __( 'General', 'pl8app' ),
        ) ),
        'taxes'      => apply_filters( 'pl8app_settings_sections_taxes', array(
            'main'               => __( 'General', 'pl8app' ),
        ) ),
        'extensions' => apply_filters( 'pl8app_settings_sections_extensions', array(
            'main'               => __( 'Main', 'pl8app' )
        ) ),
        'licenses'   => apply_filters( 'pl8app_settings_sections_licenses', array() ),
        'misc'       => apply_filters( 'pl8app_settings_sections_misc', array(
            'main'               => __( 'Miscellaneous', 'pl8app' ),
            'site_terms'         => __( 'Terms of Agreement', 'pl8app' ),
        ) ),
        'privacy'    => apply_filters( 'pl8app_settings_section_privacy', array(
             'general'      => __( 'General', 'pl8app' ),
            'export_erase' => __( 'Export & Erase', 'pl8app' ),
        ) ),
        'sms_notification' => apply_filters( 'pl8app_settings_section_sms_notification', array() ),
    );

    $sections = apply_filters( 'pl8app_settings_sections', $sections );

    return $sections;
}

/**
 * Retrieve a list of all published pages
 *
 * On large sites this can be expensive, so only load if on the settings page or $force is set to true
 *
 * @since  1.0.0
 * @param bool $force Force the pages to be loaded even if not on settings
 * @return array $pages_options An array of the pages
 */
function pl8app_get_pages( $force = false ) {

    $pages_options = array( '' => '' ); // Blank option

    if( ( ! isset( $_GET['page'] ) || 'pl8app-settings' != $_GET['page'] ) && ! $force ) {
        return $pages_options;
    }

    $pages = get_pages();
    if ( $pages ) {
        foreach ( $pages as $page ) {
            $pages_options[ $page->ID ] = $page->post_title;
        }
    }

    return $pages_options;
}

/**
 * Header Callback
 *
 * Renders the header.
 *
 * @since 1.0
 * @param array $args Arguments passed by the setting
 * @return void
 */
function pl8app_header_callback( $args ) {
    echo apply_filters( 'pl8app_after_setting_output', '', $args );
}

/**
 * Checkbox Callback
 *
 * Renders checkboxes.
 *
 * @since 1.0
 * @param array $args Arguments passed by the setting
 *
 * @return void
 */
function pl8app_checkbox_callback( $args ) {

    $pl8app_option = pl8app_get_option( $args['id'] );
    if ( isset( $args['faux'] ) && true === $args['faux'] ) {
        $name = '';
    } else {
        $name = 'name="pl8app_settings[' . pl8app_sanitize_key( $args['id'] ) . ']"';
    }

    $class = pl8app_sanitize_html_class( $args['field_class'] );

    $checked  = ! empty( $pl8app_option ) ? checked( 1, $pl8app_option, false ) : '';
    $html     = '<input type="hidden"' . $name . ' value="-1" />';
    $html    .= '<input type="checkbox" id="pl8app_settings[' . pl8app_sanitize_key( $args['id'] ) . ']"' . $name . ' value="1" ' . $checked . ' class="' . $class . '"/>';
    $html    .= '<label for="pl8app_settings[' . pl8app_sanitize_key( $args['id'] ) . ']"> '  . wp_kses_post( $args['desc'] ) . '</label>';

    echo apply_filters( 'pl8app_after_setting_output', $html, $args );
}

/**
 * Multicheck Callback
 *
 * Renders multiple checkboxes.
 *
 * @since 1.0
 * @param array $args Arguments passed by the setting
 *
 * @return void
 */
function pl8app_multicheck_callback( $args ) {

    $pl8app_option = pl8app_get_option( $args['id'] );

    $class = pl8app_sanitize_html_class( $args['field_class'] );

    $html = '';
    if ( ! empty( $args['options'] ) ) {
        $html .= '<input type="hidden" name="pl8app_settings[' . pl8app_sanitize_key( $args['id'] ) . ']" value="-1" />';
        foreach( $args['options'] as $key => $option ):
            if( isset( $pl8app_option[ $key ] ) ) { $enabled = $option; } else { $enabled = NULL; }
            $html .= '<input name="pl8app_settings[' . pl8app_sanitize_key( $args['id'] ) . '][' . pl8app_sanitize_key( $key ) . ']" id="pl8app_settings[' . pl8app_sanitize_key( $args['id'] ) . '][' . pl8app_sanitize_key( $key ) . ']" class="' . $class . '" type="checkbox" value="' . esc_attr( $option ) . '" ' . checked($option, $enabled, false) . '/>&nbsp;';
            $html .= '<label for="pl8app_settings[' . pl8app_sanitize_key( $args['id'] ) . '][' . pl8app_sanitize_key( $key ) . ']">' . wp_kses_post( $option ) . '</label><br/>';
        endforeach;
        $html .= '<p class="description">' . $args['desc'] . '</p>';
    }

    echo apply_filters( 'pl8app_after_setting_output', $html, $args );
}

/**
 * Payment method icons callback
 *
 * @since  1.0.0
 * @param array $args Arguments passed by the setting
 *
 * @return void
 */
function pl8app_payment_icons_callback( $args ) {
    $pl8app_option = pl8app_get_option( $args['id'] );

    $class = pl8app_sanitize_html_class( $args['field_class'] );

    $html = '<input type="hidden" name="pl8app_settings[' . pl8app_sanitize_key( $args['id'] ) . ']" value="-1" />';
    if ( ! empty( $args['options'] ) ) {
        foreach( $args['options'] as $key => $option ) {

            if( isset( $pl8app_option[ $key ] ) ) {
                $enabled = $option;
            } else {
                $enabled = NULL;
            }

            $html .= '<label for="pl8app_settings[' . pl8app_sanitize_key( $args['id'] ) . '][' . pl8app_sanitize_key( $key ) . ']" class="pl8app-settings-payment-icon-wrapper">';

            $html .= '<input name="pl8app_settings[' . pl8app_sanitize_key( $args['id'] ) . '][' . pl8app_sanitize_key( $key ) . ']" id="pl8app_settings[' . pl8app_sanitize_key( $args['id'] ) . '][' . pl8app_sanitize_key( $key ) . ']" class="' . $class . '" type="checkbox" value="' . esc_attr( $option ) . '" ' . checked( $option, $enabled, false ) . '/>&nbsp;';

            if( pl8app_string_is_image_url( $key ) ) {

                $html .= '<img class="payment-icon" src="' . esc_url( $key ) . '" style="width:32px;height:24px;position:relative;top:6px;margin-right:5px;"/>';

            } else {

                $card = strtolower( str_replace( ' ', '', $option ) );

                if( has_filter( 'pl8app_accepted_payment_' . $card . '_image' ) ) {

                    $image = apply_filters( 'pl8app_accepted_payment_' . $card . '_image', '' );

                } else {

                    $image       = pl8app_locate_template( 'images' . DIRECTORY_SEPARATOR . 'icons' . DIRECTORY_SEPARATOR . $card . '.png', false );
                    $content_dir = WP_CONTENT_DIR;

                    if( function_exists( 'wp_normalize_path' ) ) {

                        // Replaces backslashes with forward slashes for Windows systems
                        $image = wp_normalize_path( $image );
                        $content_dir = wp_normalize_path( $content_dir );

                    }

                    $image = str_replace( $content_dir, content_url(), $image );

                }

                $html .= '<img class="payment-icon" src="' . esc_url( $image ) . '" style="width:32px;height:24px;position:relative;top:6px;margin-right:5px;"/>';
            }


            $html .= $option . '</label>';

        }
        $html .= '<p class="description" style="margin-top:16px;">' . wp_kses_post( $args['desc'] ) . '</p>';
    }

    echo apply_filters( 'pl8app_after_setting_output', $html, $args );
}

/**
 * Radio Callback
 *
 * Renders radio boxes.
 *
 * @since 1.0.0
 * @param array $args Arguments passed by the setting
 *
 * @return void
 */
function pl8app_radio_callback( $args ) {
    $pl8app_options = pl8app_get_option( $args['id'] );

    $html = '';

    $class = pl8app_sanitize_html_class( $args['field_class'] );

    foreach ( $args['options'] as $key => $option ) :
        $checked = false;

        if ( $pl8app_options && $pl8app_options == $key )
            $checked = true;
        elseif( isset( $args['std'] ) && $args['std'] == $key && ! $pl8app_options )
            $checked = true;

        $html .= '<input name="pl8app_settings[' . pl8app_sanitize_key( $args['id'] ) . ']" id="pl8app_settings[' . pl8app_sanitize_key( $args['id'] ) . '][' . pl8app_sanitize_key( $key ) . ']" class="' . $class . '" type="radio" value="' . pl8app_sanitize_key( $key ) . '" ' . checked(true, $checked, false) . '/>&nbsp;';
        $html .= '<label for="pl8app_settings[' . pl8app_sanitize_key( $args['id'] ) . '][' . pl8app_sanitize_key( $key ) . ']">' . esc_html( $option ) . '</label><br/>';
    endforeach;

    $html .= '<p class="description">' . apply_filters( 'pl8app_after_setting_output', wp_kses_post( $args['desc'] ), $args ) . '</p>';

    echo $html;
}

/**
 * Gateways Callback
 *
 * Renders gateways fields.
 *
 * @since 1.0
 * @param array $args Arguments passed by the setting
 *
 * @return void
 */
function pl8app_gateways_callback( $args ) {
    $pl8app_option = pl8app_get_option( $args['id'] );

    $class = pl8app_sanitize_html_class( $args['field_class'] );

    $html = '<input type="hidden" name="pl8app_settings[' . pl8app_sanitize_key( $args['id'] ) . ']" value="-1" />';

    foreach ( $args['options'] as $key => $option ) :
        if ( isset( $pl8app_option[ $key ] ) )
            $enabled = '1';
        else
            $enabled = null;

        $html .= '<input name="pl8app_settings[' . esc_attr( $args['id'] ) . '][' . pl8app_sanitize_key( $key ) . ']" id="pl8app_settings[' . pl8app_sanitize_key( $args['id'] ) . '][' . pl8app_sanitize_key( $key ) . ']" class="' . $class . '" type="checkbox" value="1" ' . checked('1', $enabled, false) . '/>&nbsp;';
        $html .= '<label for="pl8app_settings[' . pl8app_sanitize_key( $args['id'] ) . '][' . pl8app_sanitize_key( $key ) . ']">' . esc_html( $option['admin_label'] ) . '</label><br/>';
    endforeach;
    $url_args  = array(
        'utm_source'   => 'settings',
        'utm_medium'   => 'gateways',
        'utm_campaign' => 'admin',
    );

    echo apply_filters( 'pl8app_after_setting_output', $html, $args );
}

/**
 * Gateways Callback (drop down)
 *
 * Renders gateways select menu
 *
 * @since 1.0
 * @param array $args Arguments passed by the setting
 *
 * @return void
 */
function pl8app_gateway_select_callback( $args ) {
    $pl8app_option = pl8app_get_option( $args['id'] );

    $class = pl8app_sanitize_html_class( $args['field_class'] );

    $html = '';

    $html .= '<select name="pl8app_settings[' . pl8app_sanitize_key( $args['id'] ) . ']"" id="pl8app_settings[' . pl8app_sanitize_key( $args['id'] ) . ']" class="' . $class . '">';

    foreach ( $args['options'] as $key => $option ) :
        $selected = isset( $pl8app_option ) ? selected( $key, $pl8app_option, false ) : '';
        $html .= '<option value="' . pl8app_sanitize_key( $key ) . '"' . $selected . '>' . esc_html( $option['admin_label'] ) . '</option>';
    endforeach;

    $html .= '</select>';
    $html .= '<label for="pl8app_settings[' . pl8app_sanitize_key( $args['id'] ) . ']"> '  . wp_kses_post( $args['desc'] ) . '</label>';

    echo apply_filters( 'pl8app_after_setting_output', $html, $args );
}

/**
 * Text Callback
 *
 * Renders text fields.
 *
 * @since 1.0
 * @param array $args Arguments passed by the setting
 *
 * @return void
 */
function pl8app_text_callback( $args ) {
    $pl8app_option = pl8app_get_option( $args['id'] );

    if ( $pl8app_option ) {
        $value = $pl8app_option;
    } elseif( ! empty( $args['allow_blank'] ) && empty( $pl8app_option ) ) {
        $value = '';
    } else {
        $value = isset( $args['std'] ) ? $args['std'] : '';
    }

    if ( isset( $args['faux'] ) && true === $args['faux'] ) {
        $args['readonly'] = true;
        $value = isset( $args['std'] ) ? $args['std'] : '';
        $name  = '';
    } else {
        $name = 'name="pl8app_settings[' . esc_attr( $args['id'] ) . ']"';
    }

    $class = pl8app_sanitize_html_class( $args['field_class'] );

    $disabled = ! empty( $args['disabled'] ) ? ' disabled="disabled"' : '';
    $readonly = $args['readonly'] === true ? ' readonly="readonly"' : '';
    $size     = ( isset( $args['size'] ) && ! is_null( $args['size'] ) ) ? $args['size'] : 'regular';
    $html     = '<input type="text" class="' . $class . ' ' . sanitize_html_class( $size ) . '-text" id="pl8app_settings[' . pl8app_sanitize_key( $args['id'] ) . ']" ' . $name . ' value="' . esc_attr( stripslashes( $value ) ) . '"' . $readonly . $disabled . ' placeholder="' . esc_attr( $args['placeholder'] ) . '"/>';
    $html    .= '<label for="pl8app_settings[' . pl8app_sanitize_key( $args['id'] ) . ']"> '  . wp_kses_post( $args['desc'] ) . '</label>';

    echo apply_filters( 'pl8app_after_setting_output', $html, $args );
}

/**
 * Email Callback
 *
 * Renders email fields.
 *
 * @since 1.0.0
 * @param array $args Arguments passed by the setting
 *
 * @return void
 */
function pl8app_email_callback( $args ) {
    $pl8app_option = pl8app_get_option( $args['id'] );

    if ( $pl8app_option ) {
        $value = $pl8app_option;
    } elseif( ! empty( $args['allow_blank'] ) && empty( $pl8app_option ) ) {
        $value = '';
    } else {
        $value = isset( $args['std'] ) ? $args['std'] : '';
    }

    if ( isset( $args['faux'] ) && true === $args['faux'] ) {
        $args['readonly'] = true;
        $value = isset( $args['std'] ) ? $args['std'] : '';
        $name  = '';
    } else {
        $name = 'name="pl8app_settings[' . esc_attr( $args['id'] ) . ']"';
    }

    $class = pl8app_sanitize_html_class( $args['field_class'] );

    $disabled = ! empty( $args['disabled'] ) ? ' disabled="disabled"' : '';
    $readonly = $args['readonly'] === true ? ' readonly="readonly"' : '';
    $size     = ( isset( $args['size'] ) && ! is_null( $args['size'] ) ) ? $args['size'] : 'regular';
    $html     = '<input type="email" class="' . $class . ' ' . sanitize_html_class( $size ) . '-text" id="pl8app_settings[' . pl8app_sanitize_key( $args['id'] ) . ']" ' . $name . ' value="' . esc_attr( stripslashes( $value ) ) . '"' . $readonly . $disabled . ' placeholder="' . esc_attr( $args['placeholder'] ) . '"/>';
    $html    .= '<label for="pl8app_settings[' . pl8app_sanitize_key( $args['id'] ) . ']"> '  . wp_kses_post( $args['desc'] ) . '</label>';

    echo apply_filters( 'pl8app_after_setting_output', $html, $args );
}

/**
 * Number Callback
 *
 * Renders number fields.
 *
 * @since  1.0.0
 * @param array $args Arguments passed by the setting
 *
 * @return void
 */
function pl8app_number_callback( $args ) {
    $pl8app_option = pl8app_get_option( $args['id'] );

    if ( $pl8app_option ) {
        $value = $pl8app_option;
    } else {
        $value = isset( $args['std'] ) ? $args['std'] : '';
    }

    if ( isset( $args['faux'] ) && true === $args['faux'] ) {
        $args['readonly'] = true;
        $value = isset( $args['std'] ) ? $args['std'] : '';
        $name  = '';
    } else {
        $name = 'name="pl8app_settings[' . esc_attr( $args['id'] ) . ']"';
    }

    $class = pl8app_sanitize_html_class( $args['field_class'] );

    $max  = isset( $args['max'] ) ? $args['max'] : 999999;
    $min  = isset( $args['min'] ) ? $args['min'] : 0;
    $step = isset( $args['step'] ) ? $args['step'] : 1;

    $size = ( isset( $args['size'] ) && ! is_null( $args['size'] ) ) ? $args['size'] : 'regular';
    $html = '<input type="number" step="' . esc_attr( $step ) . '" max="' . esc_attr( $max ) . '" min="' . esc_attr( $min ) . '" class="' . $class . ' ' . sanitize_html_class( $size ) . '-text" id="pl8app_settings[' . pl8app_sanitize_key( $args['id'] ) . ']" ' . $name . ' value="' . esc_attr( stripslashes( $value ) ) . '"/>';
    $html .= '<label for="pl8app_settings[' . pl8app_sanitize_key( $args['id'] ) . ']"> '  . wp_kses_post( $args['desc'] ) . '</label>';

    echo apply_filters( 'pl8app_after_setting_output', $html, $args );
}

/**
 * Textarea Callback
 *
 * Renders textarea fields.
 *
 * @since 1.0
 * @param array $args Arguments passed by the setting
 *
 * @return void
 */
function pl8app_textarea_callback( $args ) {
    $pl8app_option = pl8app_get_option( $args['id'] );

    if ( $pl8app_option ) {
        $value = $pl8app_option;
    } else {
        $value = isset( $args['std'] ) ? $args['std'] : '';
    }

    $class = pl8app_sanitize_html_class( $args['field_class'] );

    $html = '<textarea class="' . $class . ' large-text" cols="50" rows="5" id="pl8app_settings[' . pl8app_sanitize_key( $args['id'] ) . ']" name="pl8app_settings[' . esc_attr( $args['id'] ) . ']">' . esc_textarea( stripslashes( $value ) ) . '</textarea>';
    $html .= '<label for="pl8app_settings[' . pl8app_sanitize_key( $args['id'] ) . ']"> '  . wp_kses_post( $args['desc'] ) . '</label>';

    echo apply_filters( 'pl8app_after_setting_output', $html, $args );
}

/**
 * Password Callback
 *
 * Renders password fields.
 *
 * @since 1.0
 * @param array $args Arguments passed by the setting
 *
 * @return void
 */
function pl8app_password_callback( $args ) {
    $pl8app_options = pl8app_get_option( $args['id'] );

    if ( $pl8app_options ) {
        $value = $pl8app_options;
    } else {
        $value = isset( $args['std'] ) ? $args['std'] : '';
    }

    $class = pl8app_sanitize_html_class( $args['field_class'] );

    $size = ( isset( $args['size'] ) && ! is_null( $args['size'] ) ) ? $args['size'] : 'regular';
    $html = '<input type="password" class="' . $class . ' ' . sanitize_html_class( $size ) . '-text" id="pl8app_settings[' . pl8app_sanitize_key( $args['id'] ) . ']" name="pl8app_settings[' . esc_attr( $args['id'] ) . ']" value="' . esc_attr( $value ) . '"/>';
    $html .= '<label for="pl8app_settings[' . pl8app_sanitize_key( $args['id'] ) . ']"> ' . wp_kses_post( $args['desc'] ) . '</label>';

    echo apply_filters( 'pl8app_after_setting_output', $html, $args );
}

/**
 * Missing Callback
 *
 * If a function is missing for settings callbacks alert the user.
 *
 * @since  1.0.0
 * @param array $args Arguments passed by the setting
 * @return void
 */
function pl8app_missing_callback($args) {
    printf(
        __( 'The callback function used for the %s setting is missing.', 'pl8app' ),
        '<strong>' . $args['id'] . '</strong>'
    );
}

/**
 * Select Callback
 *
 * Renders select fields.
 *
 * @since 1.0
 * @param array $args Arguments passed by the setting
 *
 * @return void
 */
function pl8app_select_callback($args) {
    $pl8app_option = pl8app_get_option( $args['id'] );

    if ( $pl8app_option ) {
        $value = $pl8app_option;
    } else {

        // Properly set default fallback if the Select Field allows Multiple values
        if ( empty( $args['multiple'] ) ) {
            $value = isset( $args['std'] ) ? $args['std'] : '';
        } else {
            $value = ! empty( $args['std'] ) ? $args['std'] : array();
        }

    }

    if ( isset( $args['placeholder'] ) ) {
        $placeholder = $args['placeholder'];
    } else {
        $placeholder = '';
    }

    $class = pl8app_sanitize_html_class( $args['field_class'] );

    if ( isset( $args['chosen'] ) ) {
        $class .= ' pl8app-select-chosen';
    }

    // If the Select Field allows Multiple values, save as an Array
    $name_attr = 'pl8app_settings[' . esc_attr( $args['id'] ) . ']';
    $name_attr = ( $args['multiple'] ) ? $name_attr . '[]' : $name_attr;

    $html = '<select id="pl8app_settings[' . pl8app_sanitize_key( $args['id'] ) . ']" name="' . $name_attr . '" class="' . $class . '" data-placeholder="' . esc_html( $placeholder ) . '" ' . ( ( $args['multiple'] ) ? 'multiple="true"' : '' ) . '>';

    foreach ( $args['options'] as $option => $name ) {

        if ( ! $args['multiple'] ) {
            $selected = selected( $option, $value, false );
            $html .= '<option value="' . esc_attr( $option ) . '" ' . $selected . '>' . esc_html( $name ) . '</option>';
        } else {
            // Do an in_array() check to output selected attribute for Multiple
            $html .= '<option value="' . esc_attr( $option ) . '" ' . ( ( in_array( $option, $value ) ) ? 'selected="true"' : '' ) . '>' . esc_html( $name ) . '</option>';
        }

    }

    $html .= '</select>';
    $html .= '<label for="pl8app_settings[' . pl8app_sanitize_key( $args['id'] ) . ']"> ' . wp_kses_post( $args['desc'] ) . '</label>';

    echo apply_filters( 'pl8app_after_setting_output', $html, $args );
}

/**
 * Color select Callback
 *
 * Renders color select fields.
 *
 * @since 1.0
 * @param array $args Arguments passed by the setting
 *
 * @return void
 */
function pl8app_color_select_callback( $args ) {
    $pl8app_option = pl8app_get_option( $args['id'] );

    if ( $pl8app_option ) {
        $value = $pl8app_option;
    } else {
        $value = isset( $args['std'] ) ? $args['std'] : '';
    }

    $class = pl8app_sanitize_html_class( $args['field_class'] );

    $html = '<select id="pl8app_settings[' . pl8app_sanitize_key( $args['id'] ) . ']" class="' . $class . '" name="pl8app_settings[' . esc_attr( $args['id'] ) . ']"/>';

    foreach ( $args['options'] as $option => $color ) {
        $selected = selected( $option, $value, false );
        $html .= '<option value="' . esc_attr( $option ) . '" ' . $selected . '>' . esc_html( $color['label'] ) . '</option>';
    }

    $html .= '</select>';
    $html .= '<label for="pl8app_settings[' . pl8app_sanitize_key( $args['id'] ) . ']"> '  . wp_kses_post( $args['desc'] ) . '</label>';

    echo apply_filters( 'pl8app_after_setting_output', $html, $args );
}

/**
 * Rich Editor Callback
 *
 * Renders rich editor fields.
 *
 * @since 1.0
 * @param array $args Arguments passed by the setting
 */
function pl8app_rich_editor_callback( $args ) {
    $pl8app_option = pl8app_get_option( $args['id'] );

    if ( $pl8app_option ) {
        $value = $pl8app_option;
    } else {
        if( ! empty( $args['allow_blank'] ) && empty( $pl8app_option ) ) {
            $value = '';
        } else {
            $value = isset( $args['std'] ) ? $args['std'] : '';
        }
    }

    $rows = isset( $args['size'] ) ? $args['size'] : 20;

    $class = pl8app_sanitize_html_class( $args['field_class'] );
    ob_start();
    wp_editor( stripslashes( $value ), 'pl8app_settings_' . esc_attr( $args['id'] ), array( 'textarea_name' => 'pl8app_settings[' . esc_attr( $args['id'] ) . ']', 'textarea_rows' => absint( $rows ), 'editor_class' => $class ) );
    $html = ob_get_clean();

    $html .= '<br/><label for="pl8app_settings[' . pl8app_sanitize_key( $args['id'] ) . ']"> ' . wp_kses_post( $args['desc'] ) . '</label>';

    echo apply_filters( 'pl8app_after_setting_output', $html, $args );
}

/**
 * Upload Callback
 *
 * Renders upload fields.
 *
 * @since 1.0
 * @param array $args Arguments passed by the setting
 *
 * @return void
 */
function pl8app_upload_callback( $args ) {
    $pl8app_option = pl8app_get_option( $args['id'] );

    if ( $pl8app_option ) {
        $value = $pl8app_option;
    } else {
        $value = isset($args['std']) ? $args['std'] : '';
    }

    $class = pl8app_sanitize_html_class( $args['field_class'] );

    $size = ( isset( $args['size'] ) && ! is_null( $args['size'] ) ) ? $args['size'] : 'regular';
    $html = '<input type="text" class="' . sanitize_html_class( $size ) . '-text" id="pl8app_settings[' . pl8app_sanitize_key( $args['id'] ) . ']" class="' . $class . '" name="pl8app_settings[' . esc_attr( $args['id'] ) . ']" value="' . esc_attr( stripslashes( $value ) ) . '"/>';
    $html .= '<span>&nbsp;<input type="button" class="pl8app_settings_upload_button button-secondary" value="' . __( 'Upload File', 'pl8app' ) . '"/></span>';
    $html .= '<label for="pl8app_settings[' . pl8app_sanitize_key( $args['id'] ) . ']"> ' . wp_kses_post( $args['desc'] ) . '</label>';

    echo apply_filters( 'pl8app_after_setting_output', $html, $args );
}


/**
 * Color picker Callback
 *
 * Renders color picker fields.
 *
 * @since  1.0.0
 * @param array $args Arguments passed by the setting
 *
 * @return void
 */
function pl8app_color_callback( $args ) {
    $pl8app_option = pl8app_get_option( $args['id'] );

    if ( $pl8app_option ) {
        $value = $pl8app_option;
    } else {
        $value = isset( $args['std'] ) ? $args['std'] : '';
    }

    $default = isset( $args['std'] ) ? $args['std'] : '';

    $class = pl8app_sanitize_html_class( $args['field_class'] );

    $html = '<input type="text" class="' . $class . ' pl8app-color-picker" id="pl8app_settings[' . pl8app_sanitize_key( $args['id'] ) . ']" name="pl8app_settings[' . esc_attr( $args['id'] ) . ']" value="' . esc_attr( $value ) . '" data-default-color="' . esc_attr( $default ) . '" />';
    $html .= '<label for="pl8app_settings[' . pl8app_sanitize_key( $args['id'] ) . ']"> '  . wp_kses_post( $args['desc'] ) . '</label>';

    echo apply_filters( 'pl8app_after_setting_output', $html, $args );
}



/**
 * Shop States Callback
 *
 * Renders states drop down based on the currently selected country
 *
 * @since  1.0.0
 * @param array $args Arguments passed by the setting
 *
 * @return void
 */
function pl8app_shop_states_callback($args) {
    $pl8app_option = pl8app_get_option( $args['id'] );

    if ( isset( $args['placeholder'] ) ) {
        $placeholder = $args['placeholder'];
    } else {
        $placeholder = '';
    }

    $class = pl8app_sanitize_html_class( $args['field_class'] );

    $states = pl8app_get_states();

    if ( $args['chosen'] ) {
        $class .= ' pl8app-chosen';
    }

    if ( empty( $states ) ) {
        $class .= ' pl8app-no-states';
    }

    $html = '<select id="pl8app_settings[' . pl8app_sanitize_key( $args['id'] ) . ']" name="pl8app_settings[' . esc_attr( $args['id'] ) . ']"' . $class . 'data-placeholder="' . esc_html( $placeholder ) . '"/>';

    foreach ( $states as $option => $name ) {
        $selected = isset( $pl8app_option ) ? selected( $option, $pl8app_option, false ) : '';
        $html .= '<option value="' . esc_attr( $option ) . '" ' . $selected . '>' . esc_html( $name ) . '</option>';
    }

    $html .= '</select>';
    $html .= '<label for="pl8app_settings[' . pl8app_sanitize_key( $args['id'] ) . ']"> '  . wp_kses_post( $args['desc'] ) . '</label>';

    echo apply_filters( 'pl8app_after_setting_output', $html, $args );
}

function pl8app_order_notification_settings_callback( $args ) {
    ob_start(); ?>
    <p class="order_notification_desc"><?php echo $args['desc']; ?></p>
    <table class="pl8app_emails widefat" cellspacing="0">
        <thead>
        <tr>
            <?php
            $columns = apply_filters(
                'pl8app_email_setting_columns',
                array(
                    'status'     => '',
                    'name'       => __( 'Email', 'pl8app' ),
                    'recipient'  => __( 'Recipient(s)', 'pl8app' ),
                    'actions'    => '',
                )
            );

            foreach ( $columns as $key => $column ) {
                echo '<th class="pl8app-email-settings-table-' . esc_attr( $key ) . '">' . esc_html( $column ) . '</th>';
            }
            ?>
        </tr>
        </thead>
        <tbody>
        <?php

        //Admin Order Notification
        echo '<tr>';

        echo '<td class="pl8app-email-settings-table-status">';
        $admin_notification = pl8app_get_option( 'admin_notification', array() );

        if ( !empty( $admin_notification['enable_notification'] ) ) :
            echo '<span class="status-enabled" data-tip="' . esc_attr__( 'Enabled', 'pl8app' ) . '"></span>';
        else :
            echo '<span class="status-enabled" data-tip="' . esc_attr__( 'Enabled', 'pl8app' ) . '"></span>';
        endif;

        echo '</td>';

        echo '<td>';
        echo __( 'Admin Order Notification', 'pl8app' );
        echo '</td>';

        echo '<td>';
        $admin_recipients = !empty( $admin_notification['admin_recipients'] ) ? $admin_notification['admin_recipients'] : '';

        if ( !empty( $admin_recipients ) ) {
            $admin_recipients = trim( $admin_recipients );
            $admin_recipients = str_replace( ' ', ',', $admin_recipients );
            echo $admin_recipients;
        }
        echo '</td>';

        echo '<td class="pl8app-email-settings-table">
                    <a class="button alignright" href="' . esc_url( admin_url( 'admin.php?page=pl8app-settings&tab=emails&section=order_notifications&pl8app_order_status=' . strtolower( 'admin_notification' ) ) ) . '">' . esc_html__( 'Manage', 'pl8app' ) . '</a>
                </td>';

        echo '</tr>';

        $order_statuses = pl8app_get_order_statuses();

        if ( is_array( $order_statuses ) && !empty( $order_statuses ) ) {
            foreach( $order_statuses as $order_key => $order_status ) {

                if ( $order_key == 'pending' ) {
                    $order_status = __( 'New Order', 'pl8app' );
                }
                else {
                    $order_status = sprintf( __( '%s Order', 'pl8app' ), $order_status );
                }

                echo '<tr>';

                foreach ( $columns as $key => $column ) {
                    switch ( $key ) {

                        case 'status':

                            $order_notification_settings = pl8app_get_option( $order_key );

                            echo '<td class="pl8app-email-settings-table-' . esc_attr( $key ) . '">';

                            if ( isset( $order_notification_settings['enable_notification'] ) ) :
                                echo '<span class="status-enabled" data-tip="' . esc_attr__( 'Enabled', 'pl8app' ) . '"></span>';
                            else :
                                echo '<span class="status-disabled" data-tip="' . esc_attr__( 'Disabled', 'pl8app' ) . '"></span>';
                            endif;
                            echo '</td>';
                            break;

                        case 'name':
                            echo '<td class="pl8app-email-settings-table-' . esc_attr( $key ) . '">';
                            echo $order_status;
                            echo '</td>';
                            break;

                        case 'recipient':
                            echo '<td class="pl8app-email-settings-table-' . esc_attr( $key ) . '">';
                            echo __( 'Customer', 'pl8app' );
                            echo '</td>';
                            break;

                        case 'actions':
                            echo '<td class="pl8app-email-settings-table-' . esc_attr( $key ) . '">
                                        <a class="button alignright" href="' . esc_url( admin_url( 'admin.php?page=pl8app-settings&tab=emails&section=order_notifications&pl8app_order_status=' . strtolower( $order_key ) ) ) . '">' . esc_html__( 'Manage', 'pl8app' ) . '</a>
                                    </td>';
                            break;
                    }
                }

                echo '</tr>';

            }
        }
        ?>
        </tbody>
    </table>

    <?php
    $order_status = !empty( $_GET['pl8app_order_status'] ) ?  strtolower( $_GET['pl8app_order_status'] ) : '';
    $order_statuses = pl8app_get_order_statuses();
    $order_status_names = array();

    if ( is_array( $order_statuses ) && !empty( $order_statuses ) ) {
        foreach( $order_statuses as $key => $status ) {
            array_push( $order_status_names, $key );
        }
    }

    //Cross check whether the status is a valid one
    if ( in_array( $order_status, $order_status_names ) || $order_status == 'admin_notification' ) {

        if ( $order_status == 'pending' ) {
            $status = __( 'New Order', 'pl8app' );
        }
        elseif( $order_status == 'admin_notification' ) {
            $status = __( 'Admin Order Notification', 'pl8app' );
        }
        else {
            $status = sprintf( __( '%s Order', 'pl8app' ), ucfirst( $order_status ) );
        }

        //Order Settings
        if ( $order_status == 'pending'
            || $order_status == 'admin_notification' ) {
            $order_settings = pl8app_get_option( $order_status, true );
        }
        else {
            $order_settings = pl8app_get_option( $order_status );
        }

        //Enable Notification
        $enable_notification = isset( $order_settings['enable_notification'] ) ? 'checked' : '';
        if ( $order_status == 'admin_notification'
            && empty( $order_settings ) ) {
            $enable_notification = 'checked';
        }

        //Email receipients
        $email_recipients = isset( $order_settings['admin_recipients'] ) ? $order_settings['admin_recipients'] : pl8app_get_option( 'admin_notice_emails' );
        $email_recipients = $email_recipients ? stripslashes( $email_recipients ) : '';
        $email_recipients = trim( $email_recipients );

        //Email Subject
        $email_subject = isset( $order_settings['subject'] ) ? $order_settings['subject'] : '';
        if ( $order_status == 'pending' && empty( $email_subject ) ) {
            $email_subject = pl8app_get_option( 'purchase_subject' );
        }
        else if( $order_status == 'admin_notification' && empty( $email_subject ) ) {
            $email_subject = pl8app_get_option( 'order_notification_subject' );
        }

        //Email Heading
        $email_heading = isset( $order_settings['heading'] ) ? $order_settings['heading'] : '';
        if ( $order_status == 'pending' && empty( $email_heading ) ) {
            $email_heading = pl8app_get_option( 'purchase_heading' );
        }
        else if( $order_status == 'admin_notification' && empty( $email_heading ) ) {
            $email_heading = pl8app_get_option( 'order_notification_heading' );
        }

        //Email Content
        $email_content = isset( $order_settings['content'] ) ? $order_settings['content'] : '';
        if ( $order_status == 'pending' && empty( $email_content ) ) {
            $email_content = pl8app_get_option( 'purchase_receipt' );
        }
        else if( $order_status == 'admin_notification' && empty( $email_content ) ) {
            $email_content = pl8app_get_option( 'order_notification' );
        }

        $email_content = $email_content ? stripslashes( $email_content ) : '';
        $email_content = wpautop( $email_content ) ;

        ?>
        <div class="pl8app_email_field_settings_wrapper">
            <h2><?php echo $status; ?>
                <small class="pl8app-admin-breadcrumb">
                    <a href="<?php echo admin_url( 'admin.php?page=pl8app-settings&tab=emails&section=order_notifications'); ?>">⤴</a>
                </small>
            </h2>

            <table>
                <tr>
                    <td>
                        <label for="enable_disable">
                            <?php echo __( 'Enable/Disable', 'pl8app' ); ?>
                        </label>
                    </td>
                    <td>
                        <label for="enable_disable">
                            <input id="enable_disable" <?php echo $enable_notification; ?> value="yes" type="checkbox" name="<?php echo 'pl8app_settings['.$order_status.'][enable_notification]'; ?>">
                            <?php echo __( 'Enable this email notification', 'pl8app' ); ?>
                        </label>
                    </td>
                </tr>

                <?php if ( $order_status == 'admin_notification' ) : ?>
                    <tr>
                        <td>
                            <label for="admin_recipients">
                                <?php echo __( 'Recipient(s)', 'pl8app' ); ?>
                            </label>

                        </td>
                        <td>
                            <textarea class="large-text" rows="5" cols="50" id="admin_recipients" name="<?php echo 'pl8app_settings['.$order_status.'][admin_recipients]'; ?>"><?php echo $email_recipients; ?></textarea>
                            <span class="help-text"><?php echo __( 'Enter the email address(es) that should receive a notification anytime a order is placed, one per line.', 'pl8app' ); ?></span>
                        </td>

                    </tr>
                <?php endif; ?>

                <tr>
                    <td>
                        <label for="subject">
                            <?php echo __( 'Subject', 'pl8app' ); ?>
                        </label>
                    </td>
                    <td>
                        <input id="subject" type="text" name="<?php echo 'pl8app_settings['.$order_status.'][subject]'; ?>" value="<?php echo $email_subject; ?>">
                    </td>
                </tr>

                <tr>
                    <td>
                        <label for="email_heading">
                            <?php echo __( 'Email heading', 'pl8app' ); ?>
                        </label>
                    </td>
                    <td>
                        <input id="email_heading" type="text" name="<?php echo 'pl8app_settings['.$order_status.'][heading]'; ?>" value="<?php echo $email_heading; ?>">
                    </td>

                </tr>

                <tr>
                    <td class="email_content">
                        <label for="email_content">
                            <?php echo __( 'Email Content', 'pl8app' ); ?>
                        </label>
                    </td>
                    <td class="email_message_contents">
                        <?php
                        wp_editor( stripslashes( $email_content ), 'pl8app_settings_' . esc_attr( $order_status ), array( 'textarea_name' => 'pl8app_settings['.$order_status.'][content]', 'textarea_rows' => absint( 20 ), 'editor_class' => 'pl8app' ) );
                        ?>
                        <label for="email_content">
                            <?php echo __('Enter the text that is sent as order notification email. HTML is accepted. Available template tags:','pl8app' ) . '<br/>' . pl8app_get_emails_tags_list(); ?>
                        </label>

                    </td>
                </tr>

            </table>

        </div>
        <?php
    }
    echo ob_get_clean();
}

/**
 * Descriptive text callback.
 *
 * Renders descriptive text onto the settings field.
 *
 * @since 1.0.0
 * @param array $args Arguments passed by the setting
 * @return void
 */
function pl8app_descriptive_text_callback( $args ) {
    $html = wp_kses_post( $args['desc'] );

    echo apply_filters( 'pl8app_after_setting_output', $html, $args );
}

/**
 * Registers the license field callback for Software Licensing
 *
 * @since 1.0
 * @param array $args Arguments passed by the setting
 *
 * @return void
 */
if ( ! function_exists( 'pl8app_license_key_callback' ) ) {
    function pl8app_license_key_callback( $args ) {
        $pl8app_option = pl8app_get_option( $args['id'] );

        $messages = array();
        $license  = get_option( $args['options']['is_valid_license_option'] );

        if ( $pl8app_option ) {
            $value = $pl8app_option;
        } else {
            $value = isset( $args['std'] ) ? $args['std'] : '';
        }

        if( ! empty( $license ) && is_object( $license ) ) {

            // activate_license 'invalid' on anything other than valid, so if there was an error capture it
            if ( false === $license->success ) {

                switch( $license->error ) {

                    case 'expired' :

                        $class = 'expired';
                        $messages[] = sprintf(
                            __( 'Your license key expired on %s. Please <a href="%s" target="_blank">renew your license key</a>.', 'pl8app' ),
                            date_i18n( get_option( 'date_format' ), strtotime( $license->expires, current_time( 'timestamp' ) ) ),
                            'https://pl8app.com/checkout/?pl8app_license_key=' . $value . '&utm_campaign=admin&utm_source=licenses&utm_medium=expired'
                        );

                        $license_status = 'license-' . $class . '-notice';

                        break;

                    case 'revoked' :

                        $class = 'error';
                        $messages[] = sprintf(
                            __( 'Your license key has been disabled. Please <a href="%s" target="_blank">contact support</a> for more information.', 'pl8app' ),
                            'https://pl8app.com/support?utm_campaign=admin&utm_source=licenses&utm_medium=revoked'
                        );

                        $license_status = 'license-' . $class . '-notice';

                        break;

                    case 'missing' :

                        $class = 'error';
                        $messages[] = sprintf(
                            __( 'Invalid license. Please <a href="%s" target="_blank">visit your account page</a> and verify it.', 'pl8app' ),
                            'https://pl8app.com/your-account?utm_campaign=admin&utm_source=licenses&utm_medium=missing'
                        );

                        $license_status = 'license-' . $class . '-notice';

                        break;

                    case 'invalid' :
                    case 'site_inactive' :

                        $class = 'error';
                        $messages[] = sprintf(
                            __( 'Your %s is not active for this URL. Please <a href="%s" target="_blank">visit your account page</a> to manage your license key URLs.', 'pl8app' ),
                            $args['name'],
                            'https://pl8app.com/your-account?utm_campaign=admin&utm_source=licenses&utm_medium=invalid'
                        );

                        $license_status = 'license-' . $class . '-notice';

                        break;

                    case 'item_name_mismatch' :

                        $class = 'error';
                        $messages[] = sprintf( __( 'This appears to be an invalid license key for %s.', 'pl8app' ), $args['name'] );

                        $license_status = 'license-' . $class . '-notice';

                        break;

                    case 'no_activations_left':

                        $class = 'error';
                        $messages[] = sprintf( __( 'Your license key has reached its activation limit. <a href="%s">View possible upgrades</a> now.', 'pl8app' ), 'https://pl8app.com/your-account/' );

                        $license_status = 'license-' . $class . '-notice';

                        break;

                    case 'license_not_activable':

                        $class = 'error';
                        $messages[] = __( 'The key you entered belongs to a bundle, please use the product specific license key.', 'pl8app' );

                        $license_status = 'license-' . $class . '-notice';
                        break;

                    default :

                        $class = 'error';
                        $error = ! empty(  $license->error ) ?  $license->error : __( 'unknown_error', 'pl8app' );
                        $messages[] = sprintf( __( 'There was an error with this license key: %s. Please <a href="%s">contact our support team</a>.', 'pl8app' ), $error, 'https://magnigenie.com' );

                        $license_status = 'license-' . $class . '-notice';
                        break;
                }

            } else {

                switch( $license->license ) {

                    case 'valid' :
                    default:

                        $class = 'valid';

                        $now        = current_time( 'timestamp' );
                        $expiration = strtotime( $license->expires, current_time( 'timestamp' ) );

                        if( 'lifetime' === $license->expires ) {

                            $messages[] = __( 'License key never expires.', 'pl8app' );

                            $license_status = 'license-lifetime-notice';

                        } elseif( $expiration > $now && $expiration - $now < ( DAY_IN_SECONDS * 30 ) ) {

                            $messages[] = sprintf(
                                __( 'Your license key expires soon! It expires on %s. <a href="%s" target="_blank">Renew your license key</a>.', 'pl8app' ),
                                date_i18n( get_option( 'date_format' ), strtotime( $license->expires, current_time( 'timestamp' ) ) ),
                                'https://magnigenie.com'
                            );

                            $license_status = 'license-expires-soon-notice';

                        } else {

                            $messages[] = sprintf(
                                __( 'Your license key expires on %s.', 'pl8app' ),
                                date_i18n( get_option( 'date_format' ), strtotime( $license->expires, current_time( 'timestamp' ) ) )
                            );

                            $license_status = 'license-expiration-date-notice';

                        }

                        break;

                }

            }

        } else {
            $class = 'empty';

            $messages[] = sprintf(
                __( 'To receive updates, please enter your valid %s license key.', 'pl8app' ),
                $args['name']
            );

            $license_status = null;
        }

        $class .= ' ' . pl8app_sanitize_html_class( $args['field_class'] );

        $size = ( isset( $args['size'] ) && ! is_null( $args['size'] ) ) ? $args['size'] : 'regular';
        $html = '<input type="text" class="' . sanitize_html_class( $size ) . '-text" id="pl8app_settings[' . pl8app_sanitize_key( $args['id'] ) . ']" name="pl8app_settings[' . pl8app_sanitize_key( $args['id'] ) . ']" value="' . esc_attr( $value ) . '"/>';

        if ( ( is_object( $license ) && 'valid' == $license->license ) || 'valid' == $license ) {
            $html .= '<input type="submit" class="button-secondary" name="' . $args['id'] . '_deactivate" value="' . __( 'Deactivate License',  'pl8app' ) . '"/>';
        }

        $html .= '<label for="pl8app_settings[' . pl8app_sanitize_key( $args['id'] ) . ']"> '  . wp_kses_post( $args['desc'] ) . '</label>';

        if ( ! empty( $messages ) ) {
            foreach( $messages as $message ) {

                $html .= '<div class="pl8app-license-data pl8app-license-' . $class . ' ' . $license_status . '">';
                $html .= '<p>' . $message . '</p>';
                $html .= '</div>';

            }
        }

        wp_nonce_field( pl8app_sanitize_key( $args['id'] ) . '-nonce', pl8app_sanitize_key( $args['id'] ) . '-nonce' );

        echo $html;
    }
}

/**
 * Hook Callback
 *
 * Adds a do_action() hook in place of the field
 *
 * @since 1.0
 * @param array $args Arguments passed by the setting
 * @return void
 */
function pl8app_hook_callback( $args ) {
    do_action( 'pl8app_' . $args['id'], $args );
}

/**
 * Set manage_shop_settings as the cap required to save pl8app settings pages
 *
 * @since  1.0.0
 * @return string capability required
 */
function pl8app_set_settings_cap() {
    return 'manage_shop_settings';
}
add_filter( 'option_page_capability_pl8app_settings', 'pl8app_set_settings_cap' );

function pl8app_add_setting_tooltip( $html, $args ) {

    if ( ! empty( $args['tooltip_title'] ) && ! empty( $args['tooltip_desc'] ) ) {
        $tooltip = '<span alt="f223" class="pl8app-help-tip dashicons dashicons-editor-help" title="<strong>' . $args['tooltip_title'] . '</strong><br />' . $args['tooltip_desc'] . '"></span>';
        $html .= $tooltip;
    }

    return $html;
}
add_filter( 'pl8app_after_setting_output', 'pl8app_add_setting_tooltip', 10, 2 );
