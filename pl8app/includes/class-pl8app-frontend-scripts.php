<?php


if (!defined('ABSPATH')) {
    exit;
}

/**
 * Frontend scripts class.
 */
class pla_Frontend_Scripts
{

    /**
     * Contains an array of script handles registered by PL8.
     *
     * @var array
     */
    private static $scripts = array();

    /**
     * Contains an array of script handles registered by PL8.
     *
     * @var array
     */
    private static $styles = array();

    /**
     * Contains an array of script handles localized by PL8.
     *
     * @var array
     */
    private static $wp_localize_scripts = array();

    /**
     * Hook in methods.
     */
    public static function init()
    {
        add_filter('page_template', array(__CLASS__, 'pl8app_reserve_page_template'));
        add_action('wp_enqueue_scripts', array(__CLASS__, 'load_scripts'), 99999);
        add_action('wp_enqueue_scripts', array(__CLASS__, 'register_styles'), 99999);
        add_action('wp_head', array(__CLASS__, 'pla_head_styles'));
        add_action('wp_head', array(__CLASS__, 'pla_head_colors'));
        add_action('wp_footer', array(__CLASS__, 'pla_footer_area'));
    }

    /**
     * Render the Customer Footer
     */
    public static function pla_footer_area(){

        ob_start();
        do_action('pl8app_widget_before_footer');
        $store_notice_enable = pl8app_get_option('pl8app_enable_notice', '');
        if($store_notice_enable == 1) {
            $store_notice = pl8app_get_option('pl8app_store_notice', 'Hello! everyone. pl8app starts it\'s way to provide the best service to all food business.');
            $notice_id = md5( $store_notice );
            ?>
            <p class="pl8app-store-notice" data-notice-id="<?php echo esc_attr( $notice_id ); ?>" style="display:none;"><?php echo wp_kses_post( $store_notice )?><a href="#" class="pl8app-store-notice__dismiss-link"><?php echo esc_html__( 'Dismiss', 'pl8app' ) ?></a></p>
            <?PHP
        }
        $custome_footer = ob_get_clean();
        echo $custome_footer;
    }
    /**
     * Return asset URL.
     *
     * @param string $path Assets path.
     * @return string
     */
    private static function get_asset_url($path)
    {
        return apply_filters('pl8app_get_asset_url', plugins_url($path, PL8_PLUGIN_FILE), $path);
    }

    /**
     * Register a script for use.
     *
     * @uses   wp_register_script()
     * @param  string $handle Name of the script. Should be unique.
     * @param  string $path Full URL of the script, or path of the script relative to the WordPress root directory.
     * @param  string[] $deps An array of registered script handles this script depends on.
     * @param  string $version String specifying script version number, if it has one, which is added to the URL as a query string for cache busting purposes. If version is set to false, a version number is automatically added equal to current installed WordPress version. If set to null, no version is added.
     * @param  boolean $in_footer Whether to enqueue the script before </body> instead of in the <head>. Default 'false'.
     */
    private static function register_script($handle, $path, $deps = array('jquery'), $version = PL8_VERSION, $in_footer = true)
    {
        self::$scripts[] = $handle;
        wp_register_script($handle, $path, $deps, $version, $in_footer);
    }

    /**
     * Register and enqueue a script for use.
     *
     * @uses   wp_enqueue_script()
     * @param  string $handle Name of the script. Should be unique.
     * @param  string $path Full URL of the script, or path of the script relative to the WordPress root directory.
     * @param  string[] $deps An array of registered script handles this script depends on.
     * @param  string $version String specifying script version number, if it has one, which is added to the URL as a query string for cache busting purposes. If version is set to false, a version number is automatically added equal to current installed WordPress version. If set to null, no version is added.
     * @param  boolean $in_footer Whether to enqueue the script before </body> instead of in the <head>. Default 'false'.
     */
    private static function enqueue_script($handle, $path = '', $deps = array('jquery'), $version = PL8_VERSION, $in_footer = true)
    {
        if (!in_array($handle, self::$scripts, true) && $path) {
            self::register_script($handle, $path, $deps, $version, $in_footer);
        }
        wp_enqueue_script($handle);
    }

    /**
     * Register a style for use.
     *
     * @uses   wp_register_style()
     * @param  string $handle Name of the stylesheet. Should be unique.
     * @param  string $path Full URL of the stylesheet, or path of the stylesheet relative to the WordPress root directory.
     * @param  string[] $deps An array of registered stylesheet handles this stylesheet depends on.
     * @param  string $version String specifying stylesheet version number, if it has one, which is added to the URL as a query string for cache busting purposes. If version is set to false, a version number is automatically added equal to current installed WordPress version. If set to null, no version is added.
     * @param  string $media The media for which this stylesheet has been defined. Accepts media types like 'all', 'print' and 'screen', or media queries like '(orientation: portrait)' and '(max-width: 640px)'.
     * @param  boolean $has_rtl If has RTL version to load too.
     */
    private static function register_style($handle, $path, $deps = array(), $version = PL8_VERSION, $media = 'all', $has_rtl = false)
    {
        self::$styles[] = $handle;
        wp_register_style($handle, $path, $deps, $version, $media);

        if ($has_rtl) {
            wp_style_add_data($handle, 'rtl', 'replace');
        }
    }

    /**
     * Register and enqueue a styles for use.
     *
     * @uses   wp_enqueue_style()
     * @param  string $handle Name of the stylesheet. Should be unique.
     * @param  string $path Full URL of the stylesheet, or path of the stylesheet relative to the WordPress root directory.
     * @param  string[] $deps An array of registered stylesheet handles this stylesheet depends on.
     * @param  string $version String specifying stylesheet version number, if it has one, which is added to the URL as a query string for cache busting purposes. If version is set to false, a version number is automatically added equal to current installed WordPress version. If set to null, no version is added.
     * @param  string $media The media for which this stylesheet has been defined. Accepts media types like 'all', 'print' and 'screen', or media queries like '(orientation: portrait)' and '(max-width: 640px)'.
     * @param  boolean $has_rtl If has RTL version to load too.
     */
    private static function enqueue_style($handle, $path = '', $deps = array(), $version = PL8_VERSION, $media = 'all', $has_rtl = false)
    {
        if (!in_array($handle, self::$styles, true) && $path) {
            self::register_style($handle, $path, $deps, $version, $media, $has_rtl);
        }
        wp_enqueue_style($handle);
    }

    /**
     * Register all PL8 scripts.
     */
    private static function register_scripts()
    {

        $suffix = defined('SCRIPT_DEBUG') && SCRIPT_DEBUG ? '' : '.min';

        $register_scripts = array(
            'jquery-cookies' => array(
                'src' => self::get_asset_url('assets/js/jquery.cookies.min.js'),
                'deps' => array('jquery'),
                'version' => PL8_VERSION,
            ),
            'sticky-sidebar' => array(
                'src' => self::get_asset_url('assets/js/sticky-sidebar/pl8app-sticky-sidebar.js'),
                'deps' => array('jquery'),
                'version' => '1.7.0',
            ),
            'timepicker' => array(
                'src' => self::get_asset_url('assets/js/timepicker/jquery.timepicker' . $suffix . '.js'),
                'deps' => array('jquery'),
                'version' => '1.11.14',
            ),
            'pl8app-fancybox' => array(
                'src' => self::get_asset_url('assets/js/jquery.fancybox.js'),
                'deps' => array('jquery'),
                'version' => PL8_VERSION,
            ),
            'pl8app-checkout' => array(
                'src' => self::get_asset_url('assets/js/frontend/pl8app-checkout' . $suffix . '.js'),
                'deps' => array('jquery'),
                'version' => PL8_VERSION,
            ),
            'jquery-payment' => array(
                'src' => self::get_asset_url('assets/js/jquery.payment' . $suffix . '.js'),
                'deps' => array('jquery'),
                'version' => '3.0.0',
            ),
            'jquery-creditcard-validator' => array(
                'src' => self::get_asset_url('assets/js/jquery.creditCardValidator' . $suffix . '.js'),
                'deps' => array('jquery'),
                'version' => '1.3.3',
            ),
            'jquery-chosen' => array(
                'src' => self::get_asset_url('assets/js/jquery-chosen/chosen.jquery' . $suffix . '.js'),
                'deps' => array('jquery'),
                'version' => '1.8.2',
            ),
            'jquery-flot' => array(
                'src' => self::get_asset_url('assets/js/jquery-flot/jquery-flot' . $suffix . '.js'),
                'deps' => array('jquery'),
                'version' => '0.7',
            ),
            'pl8app-frontend' => array(
                'src' => self::get_asset_url('assets/js/frontend/pl8app-frontend.js'),
                'deps' => array('jquery'),
                'version' => PL8_VERSION,
            ),
            'pl8app-ajax' => array(
                'src' => self::get_asset_url('assets/js/frontend/pl8app-ajax.js'),
                'deps' => array('jquery'),
                'version' => PL8_VERSION,
            ),
            'pl8app-bootstrap-script' => array(
                'src' => self::get_asset_url('assets/js/frontend/pl8app-bootstrap.js'),
                'deps' => array('jquery'),
                'version' => PL8_VERSION,
            ),
            'pl8app-modal' => array(
                'src' => self::get_asset_url('assets/js/frontend/pl8app-modal.js'),
                'deps' => array('jquery'),
                'version' => PL8_VERSION,
            ),
            'pl8app-sticky-menu' => array(
                'src' => self::get_asset_url('assets/js/frontend/pl8app-sticky-menu.js'),
                'deps' => array('jquery'),
                'version' => PL8_VERSION,
            ),
            'pl8app-template-custom' => array(
                'src' => self::get_asset_url('assets/js/frontend/pl8app-template-custom.js'),
                'deps' => array('jquery'),
                'version' => PL8_VERSION,
            ),
        );

        foreach ($register_scripts as $name => $props) {
            self::register_script($name, $props['src'], $props['deps'], $props['version']);
        }
    }

    /**
     * Register/queue frontend scripts.
     */
    public static function load_scripts()
    {

        global $post;

        self::disable_current_theme_default_style();
        self::register_scripts();
        self::enqueue_script('jquery-cookies');

        if (is_pl8app_page()) {
            self::enqueue_script('sticky-sidebar');
            self::enqueue_script('pl8app-fancybox');
            self::enqueue_script('timepicker');
            self::enqueue_script('jquery-chosen');
            self::enqueue_script('pl8app-modal');
            self::enqueue_script('pl8app-frontend');
            self::enqueue_script('pl8app-sticky-menu');
            self::enqueue_script('pl8app-template-custom');
        }


        if (pl8app_is_checkout()) {
            self::enqueue_script('pl8app-checkout');
            if (pl8app_is_cc_verify_enabled()) {
                self::enqueue_script('jquery-creditcard-validator');
                self::enqueue_script('jquery-payment');
            }
        }

        if (!pl8app_is_ajax_disabled()) {
            self::enqueue_script('pl8app-ajax');
        }

        wp_enqueue_script('pl8app-bootstrap-script');


        $add_to_cart = apply_filters('pla_add_to_cart', __('Add To Cart', 'pl8app'));
        $update_cart = apply_filters('pla_update_cart', __('Update Cart', 'pl8app'));
        $added_to_cart = apply_filters('pla_added_to_cart', __('Added To Cart', 'pl8app'));
        $please_wait_text = __('Please Wait...', 'pl8app');
        $color = pl8app_get_option('primary_color', 'red');
        $service_options = !empty(pl8app_get_option('enable_service')) ? pl8app_get_option('enable_service') : array();
        $minimum_order_error_title = !empty(pl8app_get_option('minimum_order_error_title')) ? pl8app_get_option('minimum_order_error_title') : 'Minimum Order Error';
        $expire_cookie_time = !empty(pl8app_get_option('expire_service_cookie')) ? pl8app_get_option('expire_service_cookie') : 30;

        $setting = get_option('pl8app_settings');
        $store_latitude = isset($setting['pl8app_st_latitude'])?$setting['pl8app_st_latitude']:'53.958332';
        $store_longitude = isset($setting['pl8app_st_longitude'])?$setting['pl8app_st_longitude']:'-1.080278';
        $vat_enable = isset($setting['enable_vat']) && $setting['enable_vat'] == 1? true: false;

        ob_start();
        pl8app_get_template_part( 'osm-map-popup/pl8app-store-information' );
        $store_osm_popup = ob_get_clean();

        $params = array(
            'estimated_tax' => pl8app_get_tax_name(),
            'total_text' => __('Subtotal', 'pl8app'),
            'ajaxurl' => pl8app_get_ajax_url(),
            'show_products_nonce' => wp_create_nonce('show-products'),
            'add_to_cart' => $add_to_cart,
            'update_cart' => $update_cart,
            'added_to_cart' => $added_to_cart,
            'please_wait' => $please_wait_text,
            'at' => __('at', 'pl8app'),
            'color' => $color,
            'checkout_page' => pl8app_get_checkout_uri(),
            'add_to_cart_nonce' => wp_create_nonce('add-to-cart'),
            'service_type_nonce' => wp_create_nonce('service-type'),
            'service_options' => $service_options,
            'minimum_order_title' => $minimum_order_error_title,
            'edit_cart_menuitem_nonce' => wp_create_nonce('edit-cart-menuitem'),
            'update_cart_item_nonce' => wp_create_nonce('update-cart-item'),
            'clear_cart_nonce' => wp_create_nonce('clear-cart'),
            'update_service_nonce' => wp_create_nonce('update-service'),
            'proceed_checkout_nonce' => wp_create_nonce('proceed-checkout'),
            'error' => __('Error', 'pl8app'),
            'change_txt' => __('Change?', 'pl8app'),
            'currency' => pl8app_get_currency(),
            'currency_sign' => pl8app_currency_filter(),
            'expire_cookie_time' => $expire_cookie_time,
            'confirm_empty_cart' => __('Are you sure to clear all items?', 'pl8app'),
            'store_logo' => PL8_PLUGIN_URL.'assets/images/pl8app_logo.png',
            'store_latitude' => $store_latitude,
            'store_longitude' => $store_longitude,
            'osm_popup_content' => $store_osm_popup
        );
        wp_localize_script('pl8app-frontend', 'pla_scripts', $params);

        $co_params = array(
            'ajaxurl' => pl8app_get_ajax_url(),
            'checkout_nonce' => wp_create_nonce('pl8app_checkout_nonce'),
            'checkout_error_anchor' => '#pl8app_purchase_submit',
            'currency_sign' => pl8app_currency_filter(''),
            'currency_pos' => pl8app_get_option('currency_position', 'before'),
            'decimal_separator' => pl8app_get_option('decimal_separator', '.'),
            'thousands_separator' => pl8app_get_option('thousands_separator', ','),
            'no_gateway' => __('Please select a payment method', 'pl8app'),
            'no_discount' => __('Please enter a discount code', 'pl8app'), // Blank discount code message
            'enter_discount' => __('Enter coupon code', 'pl8app'),
            'discount_applied' => __('Discount Applied', 'pl8app'), // Discount verified message
            'no_email' => __('Please enter an email address before applying a discount code', 'pl8app'),
            'no_username' => __('Please enter a username before applying a discount code', 'pl8app'),
            'purchase_loading' => __('Please Wait...', 'pl8app'),
            'complete_purchase' => pl8app_get_checkout_button_purchase_label(),
            'taxes_enabled' => pl8app_use_taxes() ? '1' : '0',
            'vat_enabled' => $vat_enable,
            'pl8app_version' => PL8_VERSION
        );
        wp_localize_script('pl8app-checkout', 'pl8app_global_vars', apply_filters('pl8app_global_checkout_script_vars', $co_params));

        if (isset($post->ID))
            $position = pl8app_get_item_position_in_cart($post->ID);

        $has_purchase_links = false;
        if ((!empty($post->post_content) && (has_shortcode($post->post_content, 'purchase_link') || has_shortcode($post->post_content, 'menuitems'))) || is_post_type_archive('menuitem'))
            $has_purchase_links = true;

        $pickup_time_enabled = pl8app_is_service_enabled('pickup');
        $delivery_time_enabled = pl8app_is_service_enabled('delivery');

        $ajax_params = array(
            'ajaxurl' => pl8app_get_ajax_url(),
            'position_in_cart' => isset($position) ? $position : -1,
            'has_purchase_links' => $has_purchase_links,
            'already_in_cart_message' => __('You have already added this item to your cart', 'pl8app'), // Item already in the cart message
            'empty_cart_message' => __('Your cart is empty', 'pl8app'), // Item already in the cart message
            'loading' => __('Loading', 'pl8app'), // General loading message
            'select_option' => __('Please select an option', 'pl8app'), // Variable pricing error with multi-purchase option enabled
            'is_checkout' => pl8app_is_checkout() ? '1' : '0',
            'default_gateway' => pl8app_get_default_gateway(),
            'redirect_to_checkout' => (pl8app_straight_to_checkout() || pl8app_is_checkout()) ? '1' : '0',
            'checkout_page' => pl8app_get_checkout_uri(),
            'permalinks' => get_option('permalink_structure') ? '1' : '0',
            'quantities_enabled' => pl8app_item_quantities_enabled(),
            'taxes_enabled' => pl8app_use_taxes() ? '1' : '0', // Adding here for widget, but leaving in checkout vars for backcompat
            'open_hours' => pl8app_get_option('open_time'),
            'close_hours' => pl8app_get_option('close_time'),
            'please_wait' => __('Please Wait', 'pl8app'),
            'add_to_cart' => __('Add To Cart', 'pl8app'),
            'update_cart' => __('Update Cart', 'pl8app'),
            'button_color' => $color,
            'color' => $color,
            'delivery_time_enabled' => $delivery_time_enabled,
            'pickup_time_enabled' => $pickup_time_enabled,
            'display_date' => pla_current_date(),
            'current_date' => current_time('Y-m-d'),
            'update' => __('update', 'pl8app'),
            'subtotal' => __('SubTotal', 'pl8app'),
            'change_txt' => __('Change?', 'pl8app'),
            'fee' => __('Fee', 'pl8app'),

        );
        wp_localize_script('pl8app-ajax', 'pl8app_scripts', apply_filters('pl8app_ajax_script_vars', $ajax_params));

        // CSS Styles.
        $enqueue_styles = self::get_styles();
        if ($enqueue_styles && (is_pl8app_page() || is_pl8app_form_page())) {
            foreach ($enqueue_styles as $handle => $args) {
                if (!isset($args['has_rtl'])) {
                    $args['has_rtl'] = false;
                }
                self::enqueue_style($handle, $args['src'], $args['deps'], $args['version'], $args['media'], $args['has_rtl']);
            }
        }
        wp_register_style('leaflet-map', plugins_url('assets/css/leaflet.css', PL8_PLUGIN_FILE), array(), PL8_VERSION);
        wp_enqueue_style('leaflet-map');
        wp_register_script('leaflet-map', PL8_PLUGIN_URL . 'assets/js/leaflet.js', array(), PL8_VERSION);
        wp_enqueue_script('leaflet-map');
    }

    /**
     * Register Style
     * Code taken from scripts.php present in PL82.5
     *
     */
    public static function register_styles()
    {

        if (pl8app_get_option('disable_styles', false)) {
            return;
        }

        if (!is_pl8app_page()) {
            return;
        }

        // Use minified libraries if SCRIPT_DEBUG is turned off
        $suffix = (defined('SCRIPT_DEBUG') && SCRIPT_DEBUG) ? '' : '.min';

        $file = 'pl8app' . $suffix . '.css';
        $templates_dir = pl8app_get_theme_template_dir_name();

        $child_theme_style_sheet = trailingslashit(get_stylesheet_directory()) . $templates_dir . $file;
        $child_theme_style_sheet_2 = trailingslashit(get_stylesheet_directory()) . $templates_dir . 'pl8app.css';
        $parent_theme_style_sheet = trailingslashit(get_template_directory()) . $templates_dir . $file;
        $parent_theme_style_sheet_2 = trailingslashit(get_template_directory()) . $templates_dir . 'pl8app.css';
        $pl8app_plugin_style_sheet = trailingslashit(pl8app_get_templates_dir()) . $file;

        // Look in the child theme directory first, followed by the parent theme, followed by the pl8app core templates directory
        // Also look for the min version first, followed by non minified version, even if SCRIPT_DEBUG is not enabled.
        // This allows users to copy just pl8app.css to their theme
        if (file_exists($child_theme_style_sheet) || (!empty($suffix) && ($nonmin = file_exists($child_theme_style_sheet_2)))) {
            if (!empty($nonmin)) {
                $url = trailingslashit(get_stylesheet_directory_uri()) . $templates_dir . 'pl8app.css';
            } else {
                $url = trailingslashit(get_stylesheet_directory_uri()) . $templates_dir . $file;
            }
        } elseif (file_exists($parent_theme_style_sheet) || (!empty($suffix) && ($nonmin = file_exists($parent_theme_style_sheet_2)))) {
            if (!empty($nonmin)) {
                $url = trailingslashit(get_template_directory_uri()) . $templates_dir . 'pl8app.css';
            } else {
                $url = trailingslashit(get_template_directory_uri()) . $templates_dir . $file;
            }
        } elseif (file_exists($pl8app_plugin_style_sheet) || file_exists($pl8app_plugin_style_sheet)) {
            $url = trailingslashit(pl8app_get_templates_url()) . $file;
        }

        wp_register_style('pl8app-styles', $url, array(), PL8_VERSION, 'all');
        wp_enqueue_style('pl8app-styles');
    }

    /**
     * Load head styles
     *
     * Ensures menuitem styling is still shown correctly if a theme is using the CSS template file
     *
     * @since  1.0.0
     * @global $post
     * @return void
     */
    public static function pla_head_styles()
    {

        global $post;

        $menu_style = get_theme_mod( 'menu-style', 'clean' );
        if ( $menu_style == 'boxed' ) {
            $menu_style_css = ' <style>
			@media (min-width: 768px) {
				.navbar-nav > li > a {
					padding: 15px;
					border-left: 1px solid #D3D3D3 !important;
					margin-left: -1px;
				}
				.navbar-nav > li:last-of-type {
					border-right: 1px solid #D3D3D3 !important;	
				}
				.navbar-inverse .navbar-nav > li > a:after {
					content: "";
					margin-left: 0;
				}
				.navbar {
					border-left: 1px solid #D3D3D3;
					border-right: 1px solid #D3D3D3;
					border-top: 1px solid #D3D3D3;
					margin-top: -1px;
				}
			}
		</style>';
        } elseif ( $menu_style == 'clean' ) {
            $menu_style_css = '<style>
			@media (min-width: 768px) {
				.navbar-nav > li {
					padding: 15px 0;
				}
				.navbar-nav > li > a {
					padding: 0 15px;
					border-right: 1px solid;
				}
				.navbar-inverse .navbar-nav > li > a:after {
					content: "";
					margin-left: 0;
				}
				.navbar {
					border-top: 1px solid #D3D3D3;
					margin-top: -1px;
				}
			}
		</style>';
        } else {
            $menu_style_css = '';
        }
        echo $menu_style_css;

        if (pl8app_get_option('disable_styles', false) || !is_object($post)) {
            return;
        }

        // Use minified libraries if SCRIPT_DEBUG is turned off
        $suffix = (defined('SCRIPT_DEBUG') && SCRIPT_DEBUG) ? '' : '.min';

        $file = 'pl8app' . $suffix . '.css';
        $templates_dir = pl8app_get_theme_template_dir_name();

        $child_theme_style_sheet = trailingslashit(get_stylesheet_directory()) . $templates_dir . $file;
        $child_theme_style_sheet_2 = trailingslashit(get_stylesheet_directory()) . $templates_dir . 'pl8app.css';
        $parent_theme_style_sheet = trailingslashit(get_template_directory()) . $templates_dir . $file;
        $parent_theme_style_sheet_2 = trailingslashit(get_template_directory()) . $templates_dir . 'pl8app.css';

        $has_css_template = false;

        if (has_shortcode($post->post_content, 'menuitems') && file_exists($child_theme_style_sheet) || file_exists($child_theme_style_sheet_2) || file_exists($parent_theme_style_sheet) || file_exists($parent_theme_style_sheet_2)) {
            $has_css_template = apply_filters('pl8app_load_head_styles', true);
        }

        if (!$has_css_template) {
            return;
        }


        ?>

        <style>
            .pl8app_menuitem {
                float: left;
            }

            .pl8app_menuitem_columns_1 .pl8app_menuitem {
                width: 100%;
            }

            .pl8app_menuitem_columns_2 .pl8app_menuitem {
                width: 50%;
            }

            .pl8app_menuitem_columns_0 .pl8app_menuitem, .pl8app_menuitem_columns_3 .pl8app_menuitem {
                width: 33%;
            }

            .pl8app_menuitem_columns_4 .pl8app_menuitem {
                width: 25%;
            }

            .pl8app_menuitem_columns_5 .pl8app_menuitem {
                width: 20%;
            }

            .pl8app_menuitem_columns_6 .pl8app_menuitem {
                width: 16.6%;
            }

            </style>
        <?php
    }

    /**
     * Load head styles for Primary & Secondary colors
     *
     * @since  2.7
     * @return void
     */
    public static function pla_head_colors()
    {

        $primary_color = pl8app_get_option('primary_color', '#9E1B10');
        $hover_color = pl8app_get_option('hover_color', '#dd3333');
        $footer_color = pl8app_get_option('footer_color', '#F1F1F1');
        $nav_menu_color = pl8app_get_option('nav_menu_color', '#0c0c0c');
        ?>

        <style type="text/css">
            .pl8app-categories-menu ul li a,
            .pl8app-price-holder span.price {
                color: <?php echo $primary_color; ?>;
            }

            .pl8app-categories-menu ul li a:hover,
            .pl8app-categories-menu ul li a.active,
            .pl8app-price-holder span.price:hover {
                color: <?php echo $hover_color; ?>;
            }

            div.pl8app-search-wrap input#pl8app-menu-search,
            .pl8app_menuitem_tags span.menuitem_tag {
                border-color: <?php echo $primary_color; ?>;
            }

            .button.pl8app-submit,
            .btn.btn-block.btn-primary,
            .cart_item.pl8app_checkout a,
            .pl8app-popup-actions .submit-menuitem-button,
            .pl8app-mobile-cart-icons, button.modal__close {
                background: <?php echo $primary_color; ?>;
                color: #fff;
                border: 1px solid<?php echo $primary_color; ?>;
            }

            .button.pl8app-submit:active,
            .button.pl8app-submit:focus,
            .button.pl8app-submit:hover,
            .btn.btn-block.btn-primary:hover,
            .cart_item.pl8app_checkout a:hover,
            .pl8app-popup-actions .submit-menuitem-button:hover {
                background: transparent;
                color: <?php echo $primary_color; ?>;
                border: 1px solid<?php echo $primary_color; ?>;
            }

            .delivery-change,
            .special-inst a,
            .special-margin a,
            .pl8app-clear-cart,
            .cart-action-wrap a,
            .pl8app_menuitems_list h5.pl8app-cat,
            ul.pl8app-cart span.cart-total,
            a.pl8app_cart_remove_item_btn,
            .pl8app-show-terms a {
                color: <?php echo $primary_color; ?>;
            }

            .pl8app-clear-cart:hover,
            .delivery-change:hover,
            .cart-action-wrap a:hover,
            a.pl8app_cart_remove_item_btn:hover,
            .pl8app-show-terms a:hover {
                color: <?php echo $primary_color; ?>;
                opacity: 0.8;
            }

            .nav#pl8appdeliveryTab > li > a {
                text-decoration: none;
                color: <?php echo $primary_color; ?>;
            }

            .nav#pl8appdeliveryTab > li > a:hover,
            .nav#pl8appdeliveryTab > li > a:focus {
                background-color: #eee;
            }

            .nav#pl8appdeliveryTab > li.active > a,
            .nav#pl8appdeliveryTab > li.active > a:hover,
            .nav#pl8appdeliveryTab > li.active > a:focus,
            .close-cart-ic {
                background-color: <?php echo $primary_color; ?>;
                color: #fff;
            }
        </style>
        <style>
            .pl8app-footer {
                background-color: <?php echo $footer_color; ?>;
            }
            .pl8app-author-credits {
                padding: 10px 20px 10px 20px;
                text-transform: uppercase;
                margin: auto;
            }
            .pl8app-author-credits p {
                text-align: center;
            }
            .pl8app-author-credits p a {
                text-decoration: none;
                color: <?php echo $primary_color; ?>
            }
            .pl8app-author-credits p a:hover {
                opacity: 0.5;
            }
            p.pl8app-store-notice {
                position: absolute;
                top: 32px;
                left: 0;
                right: 0;
                margin: 0;
                width: 100%;
                font-size: 1em;
                padding: 1em 0;
                text-align: center;
                background-color: <?php echo $nav_menu_color; ?>;
                color: #fff;
                z-index: 99998;
                box-shadow: 0 1px 1em rgb(0 0 0 / 20%);
                display: none;
            }
        </style>
        <?php
    }

    /**
     * Get styles for the frontend.
     *
     * @return array
     */
    public static function get_styles()
    {
        return apply_filters('pl8app_enqueue_styles',
            array(
                'font-awesome' => array(
                    'src' => 'https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css',
                    'deps' => '',
                    'version' => PL8_VERSION,
                    'media' => 'all',
                    'has_rtl' => false,
                ),

                'pl8app-frontend-icons' => array(
                    'src' => self::get_asset_url('assets/css/frontend-icons.css'),
                    'deps' => '',
                    'version' => PL8_VERSION,
                    'media' => 'all',
                    'has_rtl' => false,
                ),

                'pl8app-bootstrap-styles' => array(
                    'src' => self::get_asset_url('assets/css/pl8app-bootstrap.css'),
                    'deps' => array(),
                    'version' => PL8_VERSION,
                    'media' => 'all',
                    'has_rtl' => false,
                ),

                'pl8app-fancybox' => array(
                    'src' => self::get_asset_url('assets/css/jquery.fancybox.css'),
                    'deps' => array(),
                    'version' => PL8_VERSION,
                    'media' => 'all',
                    'has_rtl' => false,
                ),

                'jquery-chosen' => array(
                    'src' => self::get_asset_url('assets/css/chosen.css'),
                    'deps' => array(),
                    'version' => PL8_VERSION,
                    'media' => 'all',
                    'has_rtl' => false,
                ),
                'pl8app-template-style' => array(
                    'src' => self::get_asset_url('assets/css/pl8app-template-style.css'),
                    'deps' => array(),
                    'version' => PL8_VERSION,
                    'media' => 'all',
                    'has_rtl' => false,
                ),

                'pl8app-frontend-styles' => array(
                    'src' => self::get_asset_url('assets/css/pl8app.css'),
                    'deps' => array(),
                    'version' => PL8_VERSION,
                    'media' => 'all',
                    'has_rtl' => false,
                )
            )
        );
    }

    /**
     * Import pl8app page template
     */

    public static function pl8app_reserve_page_template($page_template){
        if(is_pl8app_default_page()){
            $page_template = PL8_PLUGIN_DIR. 'templates/page/page.php';
        }
        return $page_template;
    }

    /**
     * Disable any theme default style
     */
    public static function disable_current_theme_default_style(){

        $wp_scripts = wp_scripts();
        $wp_styles  = wp_styles();
        $themes_uri = get_theme_root_uri();

        foreach ( $wp_scripts->registered as $wp_script ) {
            if ( strpos( $wp_script->src, $themes_uri ) !== false ) {
                wp_deregister_script( $wp_script->handle );
            }
        }

        foreach ( $wp_styles->registered as $wp_style ) {
            if ( strpos( $wp_style->src, $themes_uri ) !== false ) {
                wp_deregister_style( $wp_style->handle );
            }
        }
    }

}

pla_Frontend_Scripts::init();
