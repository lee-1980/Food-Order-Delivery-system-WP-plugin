<?php
/**
 * Load assets
 *
 * @package pl8app/Admin
 * @since 3.0
 */

if (!defined('ABSPATH')) {
    exit;
}

if (!class_exists('pla_Admin_Assets', false)) :

    /**
     * pla_Admin_Assets Class.
     */
    class pla_Admin_Assets
    {

        /**
         * Hook in tabs.
         */
        public function __construct()
        {
            add_action('admin_enqueue_scripts', array($this, 'admin_styles'));
            add_action('admin_enqueue_scripts', array($this, 'admin_scripts'));
            add_action('admin_enqueue_scripts', array($this, 'register_styles'), 100);
            add_action('admin_head', array($this, 'admin_icons'));
        }

        /**
         * Enqueue styles.
         */
        public function admin_styles()
        {

            global $wp_scripts;

            $screen = get_current_screen();
            $screen_id = $screen ? $screen->id : '';
            $suffix = '';

            // Register admin styles.
            wp_register_style('pl8app_admin_icon_styles', PL8_PLUGIN_URL . '/assets/css/admin-icons.css', array(), PL8_VERSION);
            wp_register_style('pl8app_admin_styles', PL8_PLUGIN_URL . 'assets/css/admin.css', array('select2'), PL8_VERSION);
            wp_register_style('select2', PL8_PLUGIN_URL . 'assets/css/select2.min.css', array(), PL8_VERSION);
            wp_register_style('toast', PL8_PLUGIN_URL . '/assets/css/jquery.toast.css', array(), PL8_VERSION);
            wp_register_style('timepicker', plugins_url('assets/css/jquery.timepicker.css', PL8_PLUGIN_FILE), array(), PL8_VERSION);
            wp_register_style('pl8app-addon-bootstrap-style', plugins_url('assets/css/pl8app-bootstrap.css', PL8_PLUGIN_FILE), array(), PL8_VERSION);
            wp_register_style('jquery-chosen', plugins_url('assets/css/chosen.min.css', PL8_PLUGIN_FILE), array(), PL8_VERSION);
            wp_register_style('backbone-modal', plugins_url('assets/css/pl8app-backbone-modal.css', PL8_PLUGIN_FILE), array(), PL8_VERSION);
            wp_register_style('leaflet-map', plugins_url('assets/css/leaflet.css', PL8_PLUGIN_FILE), array(), PL8_VERSION);

            $ui_style = ('classic' == get_user_option('admin_color')) ? 'classic' : 'fresh';
            wp_register_style('jquery-ui-css', plugins_url('assets/css/jquery-ui-' . $ui_style . '.min.css', PL8_PLUGIN_FILE));

            wp_enqueue_style('jquery-ui-css');
            wp_enqueue_style('timepicker');
            wp_enqueue_style('pl8app_admin_styles');
            wp_enqueue_style('jquery-chosen');
            wp_enqueue_style('wp-color-picker');
            wp_enqueue_style('toast');
            wp_enqueue_style('thickbox');
            wp_enqueue_style('backbone-modal');
            wp_enqueue_style('leaflet-map');

            // Sitewide Admin Icons.
            wp_enqueue_style('pl8app_admin_icon_styles');

            if (isset($_GET['page'])
                && $_GET['page'] == 'pl8app-extensions') {
                wp_enqueue_style('pl8app-addon-bootstrap-style');
            }
        }

        /**
         * Enqueue scripts.
         */
        public function admin_scripts()
        {

            global $wp_query, $post;

            $screen = get_current_screen();
            $screen_id = $screen ? $screen->id : '';
            $pla_screen_id = sanitize_title(__('pl8app', 'pl8app'));
            //$suffix       = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';
            $suffix = '';

            $admin_deps = array('jquery', 'jquery-toast', 'timepicker', 'jquery-form', 'inline-edit-post', 'jquery-ui-tooltip');

            wp_register_script('jquery-tiptip', PL8_PLUGIN_URL . 'assets/js/jquery-tiptip/jquery.tipTip' . $suffix . '.js', array('jquery'), PL8_VERSION, true);
            wp_register_script('select2', PL8_PLUGIN_URL . 'assets/js/select2/select2' . $suffix . '.js', array('jquery'), PL8_VERSION, true);
            wp_register_script('jquery-blockui', PL8_PLUGIN_URL . 'assets/js/jquery-blockui/jquery.blockUI' . $suffix . '.js', array('jquery'), PL8_VERSION, true);
            wp_register_script('pl8app-backbone-modal', PL8_PLUGIN_URL . 'assets/js/admin/backbone-modal.js', array('underscore', 'backbone', 'wp-util'), PL8_VERSION);
            wp_register_script('timepicker', PL8_PLUGIN_URL . 'assets/js/timepicker/jquery.timepicker.js', array('jquery'), PL8_VERSION);
            wp_register_script('pl8app-admin-meta-boxes', PL8_PLUGIN_URL . 'assets/js/admin/meta-boxes' . $suffix . '.js', array('jquery', 'jquery-ui-datepicker', 'jquery-ui-sortable', 'select2', 'jquery-tiptip', 'jquery-blockui'), PL8_VERSION);
            wp_register_script('pl8app-orders', PL8_PLUGIN_URL . 'assets/js/admin/pl8app-orders' . $suffix . '.js', array('jquery', 'pl8app-backbone-modal'), PL8_VERSION);
            wp_register_script('jquery-toast', PL8_PLUGIN_URL . 'assets/js/admin/jquery.toast.js', array('jquery'), PL8_VERSION);
            wp_register_script('drag-arrange', PL8_PLUGIN_URL . 'assets/js/admin/drag-arrange.js', array('jquery'), PL8_VERSION);
            wp_register_script('leaflet-map', PL8_PLUGIN_URL . 'assets/js/leaflet.js', array(), PL8_VERSION);
            wp_register_script('pl8app-admin', PL8_PLUGIN_URL . 'assets/js/admin/pl8app-admin.js', $admin_deps, PL8_VERSION);
            wp_register_script('jquery-chosen', PL8_PLUGIN_URL . 'assets/js/jquery-chosen/chosen.jquery' . $suffix . '.js', array('jquery'), PL8_VERSION);

            wp_enqueue_script('jquery-chosen');
            wp_enqueue_script('jquery-form');
            wp_enqueue_script('jquery-ui-datepicker');
            wp_enqueue_script('jquery-ui-dialog');
            wp_enqueue_script('wp-jquery-ui-dialog');
            wp_enqueue_script('jquery-ui-tooltip');
            wp_enqueue_script('media-upload');
            wp_enqueue_script('thickbox');
            wp_enqueue_script('drag-arrange');
            wp_enqueue_script('leaflet-map');

            $is_custom_cordinates_enabled = !empty(pl8app_get_option('use_custom_latlng')) ? 'yes' : 'no';

            $notification_style = pl8app_get_option('order_notification_styles');
            if(empty($notification_style)) $notification_style = 'default';
            $notification_sounds = array(
                'default' => PL8_PLUGIN_URL.'assets/sound/pl8app_new_order.mp3',
                'sound' => PL8_PLUGIN_URL.'assets/sound/pl8app_alternative.mp3',
                'voice' => PL8_PLUGIN_URL.'assets/sound/pl8app_voice.mp3',
            );

            $admin_params = array(
                'ajaxurl' => pl8app_get_ajax_url(),
                'please_wait' => esc_html('Please Wait', 'pl8app'),
                'success' => esc_html('Success', 'pl8app'),
                'error' => esc_html('Error', 'pl8app'),
                'information' => esc_html('Information', 'pl8app'),
                'license_success' => esc_html('Congrats, your license successfully activated!', 'pl8app'),
                'license_error' => esc_html('Invalid License Key', 'pl8app'),
                'license_activate' => esc_html('Activate License', 'pl8app'),
                'license_deactivated' => esc_html('Your license has been deactivated', 'pl8app'),
                'deactivate_license' => esc_html('Deactivate License', 'pl8app'),
                'empty_license' => esc_html('Please enter valid license key', 'pl8app'),
                'update_order_nonce' => wp_create_nonce('update-order'),
                'use_custom_cordinates' => $is_custom_cordinates_enabled,
                'post_id' => isset($post->ID) ? $post->ID : null,
                'pl8app_version' => PL8_VERSION,
                'add_new_menuitem' => __('Add New Menu Item', 'pl8app'),
                'use_this_file' => __('Use This File', 'pl8app'),
                'quick_edit_warning' => __('Sorry, not available for variable priced products.', 'pl8app'),
                'delete_payment' => __('Are you sure you wish to delete this payment?', 'pl8app'),
                'delete_payment_note' => __('Are you sure you wish to delete this note?', 'pl8app'),
                'delete_tax_rate' => __('Are you sure you wish to delete this tax rate?', 'pl8app'),
                'resend_receipt' => __('Are you sure you wish to resend the purchase receipt?', 'pl8app'),
                'disconnect_customer' => __('Are you sure you wish to disconnect the WordPress user from this customer record?', 'pl8app'),
                'copy_menuitem_link_text' => __('Copy these links to your clipboard and give them to your customer', 'pl8app'),
                'delete_payment_menuitem' => sprintf(__('Are you sure you wish to delete this %s?', 'pl8app'), pla_get_label_singular()), /* translators: %s: singular payment */
                'one_price_min' => __('You must have at least one price', 'pl8app'),
                'one_field_min' => __('You must have at least one field', 'pl8app'),
                'one_menuitem_min' => __('Payments must contain at least one item', 'pl8app'),
                'one_option' => sprintf(__('Choose a %s', 'pl8app'), pla_get_label_singular()), /* translators: %s: singular label */
                'one_or_more_option' => sprintf( /* translators: %s: singular label */
                    __('Choose one or more %s', 'pl8app'), pla_get_label_plural()),
                'numeric_item_price' => __('Item price must be numeric', 'pl8app'),
                'numeric_item_tax' => __('Item tax must be numeric', 'pl8app'),
                'numeric_quantity' => __('Quantity must be numeric', 'pl8app'),
                'currency' => pl8app_get_currency(),
                'currency_sign' => pl8app_currency_filter(''),
                'currency_pos' => pl8app_get_option('currency_position', 'before'),
                'currency_decimals' => pl8app_currency_decimal_filter(),
                'decimal_separator' => pl8app_get_option('decimal_separator', '.'),
                'thousands_separator' => pl8app_get_option('thousands_separator', ','),
                'new_media_ui' => apply_filters('pl8app_use_35_media_ui', 1),
                'remove_text' => __('Remove', 'pl8app'),
                'type_to_search' => __('Type to search', 'pl8app'),
                'quantities_enabled' => pl8app_item_quantities_enabled(),
                'batch_export_no_class' => __('You must choose a method.', 'pl8app'),
                'batch_export_no_reqs' => __('Required fields not completed.', 'pl8app'),
                'reset_stats_warn' => __('Are you sure you want to reset your store? This process is <strong><em>not reversible</em></strong>. Please be sure you have a recent backup.', 'pl8app'),
                'unsupported_browser' => __('We are sorry but your browser is not compatible with this kind of file upload. Please upgrade your browser.', 'pl8app'),
                'show_advanced_settings' => __('Show advanced settings', 'pl8app'),
                'hide_advanced_settings' => __('Hide advanced settings', 'pl8app'),
                'is_admin' => is_admin() && current_user_can( 'manage_shop_settings' ),
                'enable_order_notification' => pl8app_get_option('enable_order_notification') == 1? 1: 0,
                'load_admin_addon_nonce' => wp_create_nonce('load-admin-addon'),
                'load_bundle_item_nonce' => wp_create_nonce('load-bundle-item'),
                'store_logo' => PL8_PLUGIN_URL.'assets/images/pl8app_logo.png',
                'pl8app_icon' => PL8_PLUGIN_URL.'assets/images/icons/pl8app_logo.ico',
                'order_auto_print_enable' => pl8app_get_option('enable_auto_printing') == 1? 1: 0,
                'notification_sound' => $notification_sounds[$notification_style],
                'order_list_page' => admin_url('admin.php').'?page=pl8app-payment-history',
                'new_menu_item_template' => $this->get_menuitem_template_row(),
            );

            wp_localize_script('pl8app-admin', 'pl8app_vars',
                $admin_params
            );

            wp_register_script('pl8app-admin-scripts-compatibility', PL8_PLUGIN_URL . '/assets/js/admin/admin-backwards-compatibility' . $suffix . '.js', array('jquery', 'pl8app-admin'), PL8_VERSION);
            wp_localize_script('pl8app-admin-scripts-compatibility', 'pl8app_backcompat_vars', array(
                'purchase_limit_settings' => __('Purchase Limit Settings', 'pl8app'),
                'simple_shipping_settings' => __('Simple Shipping Settings', 'pl8app'),
                'software_licensing_settings' => __('Software Licensing Settings', 'pl8app'),
                'recurring_payments_settings' => __('Recurring Payments Settings', 'pl8app'),
            ));

            wp_enqueue_script('wp-color-picker');

            //call for media manager
            wp_enqueue_media();

            wp_register_script('jquery-flot', PL8_PLUGIN_URL . '/assets/js/jquery-flot/jquery.flot' . $suffix . '.js');
            wp_enqueue_script('jquery-flot');

            // Meta boxes.
            if (in_array($screen_id, array('menuitem', 'edit-menuitem'))) {

                wp_register_script('pl8app-admin-menuitem-meta-boxes', PL8_PLUGIN_URL . 'assets/js/admin/meta-boxes-menuitem' . $suffix . '.js', array('pl8app-admin-meta-boxes'), PL8_VERSION);
                wp_enqueue_script('pl8app-admin-menuitem-meta-boxes');

                $params = array(
                    'post_id' => isset($post->ID) ? $post->ID : '',
                    'ajax_url' => admin_url('admin-ajax.php'),
                    'add_price_nonce' => wp_create_nonce('add-price'),
                    'add_category_nonce' => wp_create_nonce('add-category'),
                    'add_addon_nonce' => wp_create_nonce('add-addon'),
                    'load_addon_nonce' => wp_create_nonce('load-addon'),
                    'delete_pricing' => esc_js(__('Are you sure you want to remove this?', 'pl8app')),
                    'delete_new_category' => esc_js(__('Are you sure to delete this category?', 'pl8app')),
                    'select_addon_category' => esc_js(__('Please select Options and Upgrades category first.', 'pl8app')),
                    'addon_category_already_selected' => esc_js(__('Addon category already selected.', 'pl8app')),
                );

                wp_localize_script('pl8app-admin-menuitem-meta-boxes', 'menuitem_meta_boxes', $params);
            }

            if ($screen_id == 'orders_page_pl8app-payment-history') {

                wp_enqueue_script('pl8app-orders');

                wp_localize_script(
                    'pl8app-orders',
                    'pla_orders_params',
                    array(
                        'ajax_url' => admin_url('admin-ajax.php'),
                        'preview_nonce' => wp_create_nonce('pl8app-preview-order'),
                        'auto_print_per_order' => pl8app_get_option('auto_print'),
                        'order_auto_print_enable' => pl8app_get_option('enable_auto_printing') == 1? 1: 0,
                    )
                );
            }

            wp_enqueue_script('jquery-toast');
            wp_enqueue_script('pl8app-admin');

        }

        /**
         * Register Required admin style
         * Taken from scripts.php from PL8 2.5
         *
         * @since 1.0
         * @global $post
         * @param string $hook Page hook
         * @return void
         */
        public function register_styles()
        {

            global $post;

            $js_dir = PL8_PLUGIN_URL . 'assets/js/';
            $css_dir = PL8_PLUGIN_URL . 'assets/css/';

            // Use minified libraries if SCRIPT_DEBUG is turned off
            // $suffix  = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';
            $suffix = '';

            wp_register_style('pl8app-admin', $css_dir . 'pl8app-admin' . $suffix . '.css', array(), PL8_VERSION);
            wp_enqueue_style('pl8app-admin');


            wp_register_style('admin-icons', $css_dir . 'admin-icons' . $suffix . '.css', array(), PL8_VERSION);
            wp_enqueue_style('admin-icons');
        }

        /**
         * pl8app Admin Menu Items Icons
         * Taken from scripts.php from PL8 2.5
         *
         * Echoes the CSS for the menuitems post type icon.
         *
         * @since 1.0
         * @since 1.0.0.11 Removed globals and CSS for custom icon
         * @return void
         */
        public function admin_icons()
        {

            $svg_images_url = PL8_PLUGIN_URL . 'assets/svg/pl8app-icon.svg';

            ?>

            <style type="text/css" media="screen">
                #dashboard_right_now .menuitem-count:before {
                    background-image: url(<?php echo $svg_images_url; ?>);
                    content: '';
                    width: 20px;
                    height: 20px;
                    background-repeat: no-repeat;
                    filter: grayscale(1);
                    background-size: 80%;
                    -webkit-background-size: 80%;
                    -moz-background-size: 80%;
                }

                #icon-edit.icon32-posts-menuitem {
                    background-image: url(<?php echo $svg_images_url; ?>);
                    content: '';
                    width: 20px;
                    height: 20px;
                    background-repeat: no-repeat;
                    filter: grayscale(1);
                    background-size: 80%;
                    -webkit-background-size: 80%;
                    -moz-background-size: 80%;
                }

                @media only screen and (-webkit-min-device-pixel-ratio: 1.5), only screen and (   min--moz-device-pixel-ratio: 1.5), only screen and (     -o-min-device-pixel-ratio: 3/2), only screen and (        min-device-pixel-ratio: 1.5), only screen and (            min-resolution: 1.5dppx) {
                    #icon-edit.icon32-posts-menuitem {
                        background-image: url(<?php echo $svg_images_url; ?>);
                        content: '';
                        width: 20px;
                        height: 20px;
                        background-repeat: no-repeat;
                        filter: grayscale(1);
                        background-size: 80%;
                        -webkit-background-size: 80%;
                        -moz-background-size: 80%;
                    }
                }
            </style>

        <?php }

        /**
         * pl8app menuitem template in Quick Entry
         *
         * @return false|string
         */
        public function get_menuitem_template_row(){
            ob_start();
            $currency = pl8app_currency_symbol();
            ?>
            <div class="row pl8app-purchased-row">

                <div class="pl8app-order-items-wrapper">
                    <ul class="pl8app-purchased-items-list-wrapper 1">
                        <li class="menuitem">
                            <span class="pl8app-purchased-menuitem-actions actions">
		                                <input type="hidden" class="pl8app-payment-details-menuitem-has-log"
                                               name="pl8app-payment-details-menuitems[1][has_log]" value="1">
		                                <a href="" class="pl8app-order-remove-menuitem pl8app-delete" data-key="1">×</a>
		                            </span>
                            <span class="pl8app-purchased-menuitem-title">
                                <a href="http://localhost:8082/wp-admin/post.php?post=1004&amp;action=edit">Pilau Rice</a>
                            </span>
                            <input type="hidden" name="pl8app-payment-details-menuitems[1][id]"
                                   class="pl8app-payment-details-menuitem-id" value="1004">
                            <input type="hidden" name="pl8app-payment-details-menuitems[1][price_id]"
                                   class="pl8app-payment-details-menuitem-price-id" value="">
                            <input type="hidden" name="pl8app-payment-details-menuitems[1][quantity]"
                                   class="pl8app-payment-details-menuitem-quantity" value="1">
                        </li>

                        <li class="item_price">
                            <span class="pl8app-order-price-wrap">
                  						<span class="pl8app-payment-details-label-mobile">Price</span>
                                        <?php echo $currency; ?>
                                        <input type="text" class="pl8app-order-input medium-text pl8app-price-field pl8app-payment-details-menuitem-item-price pl8app-payment-item-input"
                                               name="pl8app-payment-details-menuitems[1][item_price]" value="2.50">
                  					</span>
                            <span class="pl8app-order-quantity-wrap">
                                <span class="pl8app-payment-details-label-mobile">
                  							Quantity                  						</span>
                                <input type="number" name="pl8app-payment-details-menuitems[1][quantity]"
                                               class="small-text pl8app-payment-details-menuitem-quantity pl8app-payment-item-input pl8app-order-input"
                                               min="1" step="1" value="1">
                  				</span>
                        </li>

                        <li class="item_tax">
                            <span class="pl8app-payment-details-label-mobile">0</span>
                            <?php echo $currency; ?> <input type="text" class="small-text pl8app-price-field pl8app-payment-details-menuitem-item-tax pl8app-payment-item-input pl8app-order-input"
                                     name="pl8app-payment-details-menuitems[1][item_tax]" value="0.12" readonly="">
                        </li>

                        <li class="price">
                            <span class="pl8app-payment-details-label-mobile">
                  						Menu Item Total Price                  					</span>
                            <span class="pl8app-price-currency"><?php echo $currency; ?></span>
                            <span class="price-text pl8app-payment-details-menuitem-amount">5.50</span>
                            <input type="hidden" name="pl8app-payment-details-menuitems[1][amount]"
                                   class="pl8app-payment-details-menuitem-amount" value="5.5">
                        </li>
                    </ul>

                    <!-- Options and Upgrades Items Starts Here -->
                    <div class="pl8app-addon-items">
                        <span class="order-addon-items">
                  						Options and Upgrades Items                  					</span>

                        <div class="menu-item-list">
                            <select multiple="" class="addon-items-list"
                                    name="pl8app-payment-details-menuitems[1][addon_items][]" style="display: none;">
                                <option data-price="2.00<?php echo $currency; ?>" data-id="25" value="Vegetable|25|2|1">
                                    Vegetable (2.00<?php echo $currency; ?>)
                                </option>

                                <option data-price="" data-id="17" value="Chips|17||1">
                                    Chips ()
                                </option>
                                <option selected="" data-price="1.50<?php echo $currency; ?>" data-id="24" value="Hot|24|1.5|1">
                                    Hot (1.50<?php echo $currency; ?>)
                                </option>
                                <option selected="" data-price="1.50<?php echo $currency; ?>" data-id="24" value="Hot|24|1.5|1">
                                    Hot (1.50<?php echo $currency; ?>)
                                </option>
                                <option data-price="1.50<?php echo $currency; ?>" data-id="24" value="Hot|24|1.5|1">
                                    Hot (1.50<?php echo $currency; ?>)
                                </option>
                            </select>
                            <div class="chosen-container chosen-container-multi" title="" style="width: 145px;">
                                <ul class="chosen-choices">
                                    <li class="search-choice"><span>
                                                                                                    Hot (1.50<?php echo $currency; ?>)                                                                                                 </span><a
                                                class="search-choice-close" data-option-array-index="2"></a></li>
                                    <li class="search-choice"><span>
                                                                                                    Hot (1.50<?php echo $currency; ?>)                                                                                                 </span><a
                                                class="search-choice-close" data-option-array-index="3"></a></li>
                                    <li class="search-field">
                                        <input class="chosen-search-input" type="text" autocomplete="off"
                                               value="Select Some Options" style="width: 25px;">
                                    </li>
                                </ul>
                                <div class="chosen-drop">
                                    <ul class="chosen-results"></ul>
                                </div>
                            </div>
                        </div>

                    </div> <!-- end of Option and Upgrade items-->

                    <!-- Options and Upgrades Items Ends Here -->

                    <div class="clear"></div>

                </div>
            </div>
            <?php
            return ob_get_clean();
        }
    }

endif;

return new pla_Admin_Assets();