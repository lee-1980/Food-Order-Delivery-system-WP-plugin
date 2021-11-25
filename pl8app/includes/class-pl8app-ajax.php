<?php


defined('ABSPATH') || exit;

/**
 * pla_Ajax class.
 */
class pla_AJAX
{

    /**
     * Hook in ajax handlers.
     */
    public static function init()
    {

        add_action('init', array(__CLASS__, 'define_ajax'), 0);
        add_action('template_redirect', array(__CLASS__, 'do_pla_ajax'), 0);
        self::add_ajax_events();
    }

    /**
     * Get PL8 Ajax Endpoint.
     *
     * @param string $request Optional.
     *
     * @return string
     */
    public static function get_endpoint($request = '')
    {
        return esc_url_raw(apply_filters('pla_ajax_get_endpoint', add_query_arg('pl8app-ajax', $request, home_url('/', 'relative')), $request));
    }

    /**
     * Set PL8 AJAX constant and headers.
     */
    public static function define_ajax()
    {

        // phpcs:disable
        if (!empty($_GET['pl8app-ajax'])) {
            pla_maybe_define_constant('DOING_AJAX', true);
            pla_maybe_define_constant('pla_DOING_AJAX', true);
            if (!WP_DEBUG || (WP_DEBUG && !WP_DEBUG_DISPLAY)) {
                @ini_set('display_errors', 0); // Turn off display_errors during AJAX events to prevent malformed JSON.
            }
            $GLOBALS['wpdb']->hide_errors();
        }
    }

    /**
     * Send headers for PL8 Ajax Requests.
     *
     */
    private static function pla_ajax_headers()
    {

        if (!headers_sent()) {
            send_origin_headers();
            send_nosniff_header();
            pla_nocache_headers();
            header('Content-Type: text/html; charset=' . get_option('blog_charset'));
            header('X-Robots-Tag: noindex');
            status_header(200);
        } elseif (defined('WP_DEBUG') && WP_DEBUG) {
            headers_sent($file, $line);
            trigger_error("pla_ajax_headers cannot set headers - headers already sent by {$file} on line {$line}", E_USER_NOTICE); // @codingStandardsIgnoreLine
        }
    }

    /**
     * Check for PL8 Ajax request and fire action.
     */
    public static function do_pla_ajax()
    {

        global $wp_query;

        if (!empty($_GET['pl8app-ajax'])) {
            $wp_query->set('pl8app-ajax', sanitize_text_field(wp_unslash($_GET['pl8app-ajax'])));
        }

        $action = $wp_query->get('pl8app-ajax');

        if ($action) {
            self::pla_ajax_headers();
            $action = sanitize_text_field($action);
            do_action('pla_ajax_' . $action);
            wp_die();
        } // phpcs:enable
    }

    /**
     * Hook in methods - uses WordPress ajax handlers (admin-ajax).
     */
    public static function add_ajax_events()
    {

        $ajax_events_nopriv = array(
            'show_products',
            'add_to_cart',
            'show_delivery_options',
            'check_service_slot',
            'edit_cart_menuitem',
            'update_cart_items',
            'remove_from_cart',
            'clear_cart',
            'proceed_checkout',
            'get_subtotal',
            'apply_discount',
            'remove_discount',
            'checkout_login',
            'checkout_register',
            'recalculate_taxes',
            'get_states',
            'menuitem_search',
            'update_quantity',
            'allergy_email_to',
            'contact_email_to',
            'emergency_stop',
            'emergency_stop_disable',
            'order_visual_widget_render'
        );

        foreach ($ajax_events_nopriv as $ajax_event) {
            add_action('wp_ajax_pl8app_' . $ajax_event, array(__CLASS__, $ajax_event));
            add_action('wp_ajax_nopriv_pl8app_' . $ajax_event, array(__CLASS__, $ajax_event));

            // RP AJAX can be used for frontend ajax requests.
            add_action('pla_ajax_' . $ajax_event, array(__CLASS__, $ajax_event));
        }

        $ajax_events = array(
            'add_addon',
            'load_addon_child',
            'add_price',
            'add_category',
            'get_bundle_item',
            'get_order_details',
            'update_order_status',
            'check_for_menuitem_price_variations',
            'admin_order_addon_items',
            'customer_search',
            'user_search',
            'search_users',
            'check_new_orders',
            'activate_addon_license',
            'deactivate_addon_license'
        );

        foreach ($ajax_events as $ajax_event) {
            add_action('wp_ajax_pl8app_' . $ajax_event, array(__CLASS__, $ajax_event));
        }
    }

    /**
     * Add an variable price row.
     */
    public static function add_price()
    {

        ob_start();

        check_ajax_referer('add-price', 'security');

        $current = $_POST['i'];

        include 'admin/menuitems/views/html-menuitem-variable-price.php';
        wp_die();
    }

    /**
     * Add an addon row.
     */
    public static function add_addon()
    {

        ob_start();

        check_ajax_referer('add-addon', 'security');

        $current = $_POST['i'];

        if ($_POST['iscreate'] == 'true') {
            $addon_types = pl8app_get_addon_types();
            include 'admin/menuitems/views/html-menuitem-new-addon-category.php';
        } else {
            $addon_categories = pl8app_get_addons();
            $item_id = $_POST['item_id'];
            include 'admin/menuitems/views/html-menuitem-addon.php';
        }

        wp_die();
    }


    /**
     * Add Category to menuitem
     */
    public static function add_category()
    {

        check_ajax_referer('add-category', 'security');

        $parent = $_POST['parent'];
        $name = $_POST['name'];
        $args = apply_filters('pl8app_add_category_args', array('parent' => $parent));

        $category = wp_insert_term($name, 'menu-category', $args);

        wp_send_json($category);
    }

    /**
     * Get the item information
     */
    public static function get_bundle_item()
    {
        check_ajax_referer('load-bundle-item', 'security', false);

        if (isset($_POST['menuitem_id'])) {
            $bundled_id = $_POST['menuitem_id'];
            $bundled_item = new pl8app_Menuitem($bundled_id);
            ob_start();
            ?>
            <li style="visibility: visible;">
                <span class="move"></span>
                <span class="item_id" aria-label="Default">
                                    <input type="hidden" name="_pl8app_bundled_products[]"
                                           value="<?PHP echo $bundled_id; ?>">
                                </span>
                <span class="data" data-price="<?PHP echo $bundled_item->get_price(); ?>">
                                    <span class="name"><?PHP echo $bundled_item->post_title; ?></span>
                                    <span class="info">
                                        <?PHP echo pl8app_price($bundled_id); ?>
                                    </span>
                                </span>
                <span class="type">
                                    <a href="<?PHP echo get_edit_post_link($bundled_id); ?>"
                                       target="_blank">View item<br><?PHP echo $bundled_id; ?></a>
                                </span>
                <span class="remove hint--left" aria-label="Remove">×</span>
            </li>
            <?php
            $content = ob_get_clean();
            $response = array(
                'html' => $content,
            );
            wp_send_json_success($response);
        }
        pl8app_die();
    }

    /**
     *
     * Change order status from order history
     *
     * @since 3.0
     * @return mixed
     */
    public static function update_order_status()
    {

        if (isset($_GET['status']) && isset($_GET['payment_id'])) {

            $payment_id = $_GET['payment_id'];
            $new_status = $_GET['status'];

            $status = sanitize_text_field(wp_unslash($new_status));
            $statuses = pl8app_get_order_statuses();

            if (array_key_exists($status, $statuses)) {
                pl8app_update_order_status($payment_id, $status);
            }

        }

        $redirect = wp_get_referer() ? wp_get_referer() : admin_url( 'admin.php?page=rpress-payment-history' );

        if( !empty( $_GET['redirect'] ) ) {
            wp_safe_redirect( esc_url( $redirect ) );
            exit;
        }

        wp_send_json( [ 'redirect' => esc_url( $redirect ) ], 200 );
        exit;
    }

    /**
     * Load addon child items when after selecting parent addon
     */
    public static function load_addon_child()
    {

        check_ajax_referer('load-addon', 'security');

        $parent = $_POST['parent'];
        $current = $_POST['i'];
        $item_id = $_POST['item_id'];
        $addon_items = pl8app_get_addons($parent);
        $variation_label = '';

        if (!is_null($item_id) && pl8app_has_variable_prices($item_id)) {
            $variation_label = get_post_meta($item_id, 'pl8app_variable_price_label', true);
            $variation_label = !is_null($variation_label) ? $variation_label : __('Variation', 'pl8app');
        }

        $output = '<table class="pl8app-addon-items">';
        $output .= '<thead>';
        $output .= '<tr>';
        $output .= '<th class="select_addon">';
        $output .= '<strong>' . __('Enable', 'pl8app') . '</strong>';
        $output .= '</th>';
        $output .= '<th class="addon_name">';
        $output .= '<strong>' . __('Addon Name', 'pl8app') . '</strong>';
        $output .= '</th>';
        $output .= '<th class="variation_name">';
        $output .= '<strong>' . $variation_label . '</strong>';
        $output .= '</th>';
        $output .= '<th class="addon_price">';
        $output .= '<strong>' . __('Price', 'pl8app') . '</strong>';
        $output .= '</th>';
        $output .= '</tr>';
        $output .= '</thead>';
        $output .= '<tbody>';

        foreach ($addon_items as $addon_item) {

            $addon_price = pl8app_get_addon_data($addon_item->term_id, 'price');
            $addon_price = !empty($addon_price) ? $addon_price : '0.00';
            $parent_class = ($addon_item->parent == 0) ? 'pl8app-parent-addon' : 'pl8app-child-addon';

            $count = 1;

            if (!empty($item_id) && pl8app_has_variable_prices($item_id)) {

                foreach (pl8app_get_variable_prices($item_id) as $price) {

                    $output .= '<tr class="' . $parent_class . '">';
                    if ($count == 1) {
                        $output .= '<td class="td_checkbox"><input type="checkbox" value="' . $addon_item->term_id . '" id="' . $addon_item->slug . '" name="addons[' . $current . '][items][]" class="pl8app-checkbox"></td>';
                    } else {
                        $output .= '<td class="td_checkbox">&nbsp;</td>';
                    }
                    $output .= '<td class="add_label"><label for="' . $addon_item->slug . '">' . $addon_item->name . '</label></td>';
                    $output .= '<td class="variation_label"><label for="' . $price['name'] . '">' . $price['name'] . '</label></td>';
                    $output .= '<td class="addon_price"><input class="addon-custom-price" type="text" placeholder="0.00" value="' . $addon_price . '" name="addons[' . $current . '][prices][' . $addon_item->term_id . '][' . $price['name'] . ']"></td>';
                    $output .= '</tr>';
                    $count++;
                }

            } else {

                $output .= '<tr class="' . $parent_class . '">';
                $output .= '<td class="td_checkbox"><input type="checkbox" value="' . $addon_item->term_id . '" id="' . $addon_item->slug . '" name="addons[' . $current . '][items][]" class="pl8app-checkbox"></td>';
                $output .= '<td class="add_label"><label for="' . $addon_item->slug . '">' . $addon_item->name . '</label></td>';
                $output .= '<td class="variation_label">&nbsp;</label></td>';
                $output .= '<td class="addon_price"><input class="addon-custom-price" type="text" placeholder="0.00" value="' . $addon_price . '" name="addons[' . $current . '][prices][' . $addon_item->term_id . ']"></td>';
                $output .= '</tr>';
            }
        }
        $output .= '</tbody>';
        $output .= '</table>';

        echo $output;
        wp_die();
    }

    /**
     * Load Menuitems List in the popup
     */
    public static function show_products()
    {

        check_ajax_referer('show-products', 'security', false);

        if (empty($_POST['menuitem_id']))
            return;

        $menuitem_id = $_POST['menuitem_id'];


        $menu_title = get_the_title($menuitem_id);
        $menuitem_desc = get_post_field('post_content', $menuitem_id);
        $item_addons = get_menuitem_lists($menuitem_id, $cart_key = '');

        ob_start();
        pl8app_get_template_part('pl8app', 'show-products');
        $data = ob_get_clean();

        $data = str_replace('{menuitemslist}', $item_addons, $data);
        $data = str_replace('{itemdescription}', $menuitem_desc, $data);

        $menuitem = new pl8app_Menuitem($menuitem_id);

        $price = 0;

        if (!empty($menuitem_id)) {
            //Check item is bundle or simple

            if ($menuitem->is_bundled_menuitem()) {

                $bundled_products = $menuitem->get_bundled_menuitems();
                foreach ($bundled_products as $bundled_id){
                    if(!check_availability_menu_item_timing($bundled_id)){
                        unset($bundled_products[$bundled_id]);
                    }
                }

                $data = apply_filters('pl8app_bundled_products_content', $data, $bundled_products);
                $type = 'bundle';
                $price = $menuitem->get_bundle_discount();

            } else {
                $data = str_replace('{hidden}', 'hidden', $data);
                $type = 'default';
                $price = pl8app_get_menuitem_price($menuitem_id);
            }
        }

        $formatted_price = pl8app_currency_filter(pl8app_format_amount($price));

        $response = array(
            'price' => $formatted_price,
            'price_raw' => $price,
            'html' => $data,
            'html_title' => apply_filters('pl8app_modal_title', $menu_title),
            'type' => $type,
            'bundle_discount' => isset($bundle_discount)?$bundle_discount:0
        );


        wp_send_json_success($response);
        pl8app_die();
    }

    /**
     * Show Service Options in the popup
     */
    public static function show_delivery_options()
    {

        check_ajax_referer('service-type', 'security', false);

        $menuitem_id = isset($_POST['menuitem_id']) ? $_POST['menuitem_id'] : '';
        $get_addons = pl8app_get_delivery_steps($menuitem_id);

        $response = array(
            'html' => $get_addons,
            'html_title' => apply_filters('pl8app_delivery_options_title', __('Your Order Settings', 'pl8app')),
        );

        wp_send_json_success($response);
        pl8app_die();
    }

    /**
     * Check Service Options availibility
     */
    public static function check_service_slot()
    {
        $response = apply_filters('pl8app_check_service_slot', $_POST);
        $response = apply_filters('pl8app_validate_slot', $response);
        wp_send_json($response);
        wp_die();
    }

    /**
     * Edit menuitem in the popup
     */
    public static function edit_cart_menuitem()
    {

        check_ajax_referer('edit-cart-menuitem', 'security', false);

        $cart_key = !empty($_POST['cartitem_id']) ? $_POST['cartitem_id'] : 0;
        $cart_key = absint($cart_key);
        $menuitem_id = !empty($_POST['menuitem_id']) ? $_POST['menuitem_id'] : '';
        $menu_title = !empty($_POST['menuitem_name']) ? $_POST['menuitem_name'] : get_the_title($menuitem_id);
        $menuitem_desc = get_post_field('post_content', $menuitem_id);

        if (!empty($menuitem_id)) {

            $price = '';

            if (!empty($menuitem_id)) {
                //Check item is variable or simple
                if (pl8app_has_variable_prices($menuitem_id)) {
                    $price = pl8app_get_lowest_price_option($menuitem_id);
                } else {
                    $price = pl8app_get_menuitem_price($menuitem_id);
                }
            }

            if (!empty($price)) {
                $formatted_price = pl8app_currency_filter(pl8app_format_amount($price));
            }

            $parent_addons = get_menuitem_lists($menuitem_id, $cart_key);
            $special_instruction = pl8app_get_instruction_by_key($cart_key);

            ob_start();
            pl8app_get_template_part('pl8app', 'edit-product');
            $data = ob_get_clean();

            $data = str_replace('{itemdescription}', $menuitem_desc, $data);
            $data = str_replace('{menuitemslist}', $parent_addons, $data);
            $data = str_replace('{cartinstructions}', $special_instruction, $data);
        }

        $response = array(
            'price' => $formatted_price,
            'price_raw' => $price,
            'html' => $data,
            'html_title' => apply_filters('pl8app_modal_title', $menu_title),
        );

        wp_send_json_success($response);
        pl8app_die();
    }

    /**
     * Add To Cart in the popup
     */
    public static function add_to_cart()
    {

        check_ajax_referer('add-to-cart', 'security', false);

        if (empty($_POST['menuitem_id'])) {
            return;
        }

        $menuitem_id = $_POST['menuitem_id'];
        $quantity = !empty($_POST['menuitem_qty']) ? $_POST['menuitem_qty'] : 1;
        $instructions = !empty($_POST['special_instruction']) ? $_POST['special_instruction'] : '';
        $addon_items = !empty($_POST['post_data']) ? $_POST['post_data'] : '';

        $items = '';
        $options = array();
        $menuitem = new pl8app_Menuitem($menuitem_id);

        $options = apply_filters('pl8app_add_vat_to_cart_options', $options, $menuitem);

        //Check whether the menuitem is bundle
        if ($menuitem->is_bundled_menuitem()) {
            $bundle_discount = $menuitem->get_bundle_discount();
            $options['price'] = -1 * $bundle_discount;
        } else {
            $options['price'] = pl8app_get_menuitem_price($menuitem_id);
        }

        $options['id'] = $menuitem_id;
        $options['quantity'] = $quantity;
        $options['instruction'] = $instructions;

        if (is_array($addon_items) && !empty($addon_items)) {

            foreach ($addon_items as $key => $get_items) {

                $addon_data = explode('|', $get_items['value']);

                if (is_array($addon_data) && !empty($addon_data)) {

                    $addon_item_like = isset($addon_data[3]) ? $addon_data[3] : 'checkbox';

                    $addon_id = !empty($addon_data[0]) ? $addon_data[0] : '';
                    $addon_qty = !empty($addon_data[1]) ? $addon_data[1] : '';
                    $addon_price = !empty($addon_data[2]) ? $addon_data[2] : '';

                    $addon_details = get_term_by('id', $addon_id, 'addon_category');

                    if ($addon_details) {

                        $addon_item_name = $addon_details->name;

                        $options['addon_items'][$key]['addon_item_name'] = $addon_item_name;
                        $options['addon_items'][$key]['addon_id'] = $addon_id;
                        $options['addon_items'][$key]['price'] = $addon_price;
                        $options['addon_items'][$key]['quantity'] = $addon_qty;
                    }
                }
            }
        }


        $key = pl8app_add_to_cart($menuitem_id, $options);

        $item = array(
            'id' => $menuitem_id,
            'options' => $options
        );

        $item = apply_filters('pl8app_ajax_pre_cart_item_template', $item);
        $items .= pl8app_get_cart_item_template($key, $item, true, $data_key = $key);

        $return = array(
            'subtotal' => html_entity_decode(pl8app_currency_filter(pl8app_format_amount(pl8app_get_cart_subtotal())), ENT_COMPAT, 'UTF-8'),
            'total' => html_entity_decode(pl8app_currency_filter(pl8app_format_amount(pl8app_get_cart_total())), ENT_COMPAT, 'UTF-8'),
            'cart_item' => $items,
            'cart_key' => $key,
            'cart_quantity' => html_entity_decode(pl8app_get_cart_quantity())
        );

        if (pl8app_use_taxes()) {
            $return['taxes'] = pl8app_get_cart_tax_summary();
        }

        $return = apply_filters('pl8app_cart_data', $return);

        wp_send_json($return);
        pl8app_die();
    }

    /**
     * Allergy Email to admin
     *
     */

    public static function allergy_email_to(){

    }

    /**
     * Contact Us Email to Admin
     *
     */
    public static function contact_email_to(){

    }
    /**
     * Update Cart Items
     */
    public static function update_cart_items()
    {

        check_ajax_referer('update-cart-item', 'security', false);

        $cart_key = isset($_POST['menuitem_cartkey']) ? $_POST['menuitem_cartkey'] : '';
        $menuitem_id = isset($_POST['menuitem_id']) ? $_POST['menuitem_id'] : '';
        $item_qty = isset($_POST['menuitem_qty']) ? $_POST['menuitem_qty'] : 1;

        if (empty($cart_key) && empty($menuitem_id)) {
            return;
        }

        $special_instruction = isset($_POST['special_instruction']) ? sanitize_text_field($_POST['special_instruction']) : '';
        $addon_items = isset($_POST['post_data']) ? $_POST['post_data'] : '';

        $options = array();
        $menuitem = new pl8app_Menuitem($menuitem_id);
        $options['id'] = $menuitem_id;
        $options['quantity'] = $item_qty;
        $options['instruction'] = $special_instruction;

        $options = apply_filters('pl8app_add_vat_to_cart_options', $options, $menuitem);

        $price_id = '';
        $items = '';

        if (pl8app_has_variable_prices($menuitem_id)) {
            if (isset($addon_items[0]['name']) && $addon_items[0]['name'] == 'price_options') {
                $price_id = $addon_items[0]['value'];
            }
        }

        $options['price_id'] = $price_id;

        if (is_array($addon_items) && !empty($addon_items)) {

            foreach ($addon_items as $key => $get_items) {

                $addon_data = explode('|', $get_items['value']);

                if (is_array($addon_data) && !empty($addon_data)) {

                    $addon_item_like = isset($addon_data[3]) ? $addon_data[3] : 'checkbox';

                    $addon_id = !empty($addon_data[0]) ? $addon_data[0] : '';
                    $addon_qty = !empty($addon_data[1]) ? $addon_data[1] : '';
                    $addon_price = !empty($addon_data[2]) ? $addon_data[2] : '';

                    $addon_details = get_term_by('id', $addon_id, 'addon_category');

                    if ($addon_details) {

                        $addon_item_name = $addon_details->name;

                        $options['addon_items'][$key]['addon_item_name'] = $addon_item_name;
                        $options['addon_items'][$key]['addon_id'] = $addon_id;
                        $options['addon_items'][$key]['price'] = $addon_price;
                        $options['addon_items'][$key]['quantity'] = $addon_qty;
                    }
                }
            }
        }



        PL8PRESS()->cart->set_item_quantity($menuitem_id, $item_qty, $options);

        $item = array(
            'id' => $menuitem_id,
            'options' => $options
        );

        $item = apply_filters('pl8app_ajax_pre_cart_item_template', $item);
        $items = pl8app_get_cart_item_template($cart_key, $item, true, $data_key = '');

        $return = array(
            'subtotal' => html_entity_decode(pl8app_currency_filter(pl8app_format_amount(pl8app_get_cart_subtotal())), ENT_COMPAT, 'UTF-8'),
            'total' => html_entity_decode(pl8app_currency_filter(pl8app_format_amount(pl8app_get_cart_total())), ENT_COMPAT, 'UTF-8'),
            'cart_item' => $items,
            'cart_key' => $cart_key,
            'cart_quantity' => html_entity_decode(pl8app_get_cart_quantity())
        );

        if (pl8app_use_taxes()) {
            $return['taxes'] = pl8app_get_cart_tax_summary();
        }

        $return = apply_filters('pl8app_cart_data', $return);
        echo json_encode($return);
        pl8app_die();
    }

    /**
     * Remove an item from Cart
     */
    public static function remove_from_cart()
    {

        if (isset($_POST['cart_item'])) {

            pl8app_remove_from_cart($_POST['cart_item']);

            $return = array(
                'removed' => 1,
                'subtotal' => html_entity_decode(pl8app_currency_filter(pl8app_format_amount(pl8app_get_cart_subtotal())), ENT_COMPAT, 'UTF-8'),
                'total' => html_entity_decode(pl8app_currency_filter(pl8app_format_amount(pl8app_get_cart_total())), ENT_COMPAT, 'UTF-8'),
                'cart_quantity' => html_entity_decode(pl8app_get_cart_quantity()),
            );

            if (pl8app_use_taxes()) {
                $return['taxes'] = pl8app_get_cart_tax_summary();
            }
            $return = apply_filters('pl8app_cart_data', $return);
            wp_send_json($return);

        }
        pl8app_die();
    }

    /**
     * Clear cart
     */
    public static function clear_cart()
    {

        pl8app_empty_cart();

        // Removing Service Time Cookie
        if (isset($_COOKIE['service_time'])) {
            unset($_COOKIE['service_time']);
            setcookie("service_time", "", time() - 300, "/");
        }

        // Removing Service Type Cookie
        if (isset($_COOKIE['service_type'])) {
            unset($_COOKIE['service_type']);
            setcookie("service_type", "", time() - 300, "/");
        }

        // Removing Delivery Date Cookie
        if (isset($_COOKIE['delivery_date'])) :
            unset($_COOKIE['delivery_date']);
            setcookie("delivery_date", "", time() - 300, "/");
        endif;

        $return['status'] = 'success';
        $return['response'] = '<li class="cart_item empty"><span class="pl8app_empty_cart">' . apply_filters('pl8app_empty_cart_message', '<span class="pl8app_empty_cart">' . __('CHOOSE AN ITEM FROM THE MENU TO GET STARTED.', 'pl8app') . '</span>') . '</span></li>';
        $return['response'] .= '<li class="delivery-items-options"><div class="pl8app item-order"><span>Service</span></div><div class="delivery-wrap"><div class="delivery-opts"></div><a href="#" class="delivery-change new"><span><i class="fa fa-calendar" aria-hidden="true"></i></span>Choose the Service Type, Date and Slot!</a></div></li>';
        echo json_encode($return);

        pl8app_die();
    }

    /**
     * Proceed Checkout
     */
    public static function proceed_checkout()
    {

        $response = pl8app_pre_validate_order();
        $response = apply_filters('pl8app_proceed_checkout', $response);
        wp_send_json($response);
        pl8app_die();
    }

    /**
     * Get Order Details
     */
    public static function get_order_details()
    {

        check_admin_referer('pl8app-preview-order', 'security');

        $order = pl8app_get_payment(absint($_GET['order_id']));

        if ($order) {
            include_once 'admin/payments/class-payments-table.php';

            wp_send_json_success(pl8app_Payment_History_Table::order_preview_get_order_details($order));
        }
        pl8app_die();
    }

    /**
     * Get Menuitem Variations
     */
    public static function check_for_menuitem_price_variations()
    {

        // Check if current user can edit products.
        if (!current_user_can('edit_products')) {
            die('-1');
        }

        $menuitem_id = isset($_POST['menuitem_id']) ? $_POST['menuitem_id'] : '';

        // Check menuitem has any variable pricing
        if (empty($menuitem_id))
            return;

        ob_start();

        if (pl8app_has_variable_prices($menuitem_id)) :
            $get_lowest_price_id = pl8app_get_lowest_price_id($menuitem_id);
            $get_lowest_price = pl8app_get_lowest_price_option($menuitem_id);
            ?>
            <div class="pl8app-get-variable-prices">
                <input type="hidden" class="pl8app_selected_price" name="pl8app_selected_price"
                       value="<?php echo $get_lowest_price; ?>">
                <?php
                foreach (pl8app_get_variable_prices($menuitem_id) as $key => $options) :
                    $option_price = $options['amount'];
                    $price = pl8app_currency_filter(pl8app_format_amount($option_price));
                    $option_name = $options['name'];
                    $option_name_slug = sanitize_title($option_name);
                    ?>
                    <label for="<?php echo $option_name_slug; ?>">
                        <input id="<?php echo $option_name_slug; ?>" <?php checked($get_lowest_price_id, $key, true); ?>
                               type="radio" name="pl8app_price_name" value="<?php echo $option_price; ?>">
                        <?php echo $option_name; ?>
                        <?php echo sprintf(__('( %1$s )', 'pl8app'), $price); ?>
                    </label>
                <?php
                endforeach;
                ?>
            </div>
        <?php
        else :
            $normal_price = pl8app_get_menuitem_price($menuitem_id);
            $price = pl8app_currency_filter(pl8app_format_amount($normal_price));
            ?>
            <span class="pl8app-price-name"><?php echo $price; ?></span>
            <input type="hidden" class="pl8app_selected_price" name="pl8app_selected_price"
                   value="<?php echo $normal_price; ?>">
        <?php
        endif;
        $output = ob_get_contents();
        ob_end_clean();
        $tax_key = get_post_meta($menuitem_id, 'pl8app_menuitem_vat', true);
        $tax_key = isset($tax_key) ? $tax_key : 0;
        $tax_object = pl8app_get_tax_rates($tax_key);
        if(!empty($tax_object['rate']) && pl8app_use_taxes()){
            $tax = $normal_price * (float) $tax_object['rate'] / 100;
        }
        else{
            $tax = 0;
        }

        $response = array('price' => $output, 'tax' => $tax);
        //Get the product type "Bundle or single"

        $menuitem = new pl8app_Menuitem( $menuitem_id );
        if($menuitem->is_bundled_menuitem()){
            $bundled_items = $menuitem->get_bundled_menuitems();
            $response['bundle'] = $bundled_items;
        }

        wp_send_json($response);
        pl8app_die();
    }

    /**
     * Get Option and Upgrade items in the admin order screen
     */
    public static function admin_order_addon_items()
    {

        check_ajax_referer('load-admin-addon', 'security');

        $menuitem_id = isset($_POST['menuitem_id']) ? $_POST['menuitem_id'] : '';
        $get_addon_items = '';

        ob_start();

        if (!empty($menuitem_id)) {
            $get_addon_items = get_addon_items_by_menuitem($menuitem_id);
        }

        $output = ob_get_contents();
        ob_end_clean();

        echo $output;
        pl8app_die();
    }

    /**
     * Gets the cart's subtotal via AJAX.
     *
     * @since 1.0
     * @return void
     */
    public static function get_subtotal()
    {

        echo pl8app_currency_filter(pl8app_get_cart_subtotal());
        pl8app_die();
    }

    /**
     * Validates the supplied discount sent via AJAX.
     *
     * @since 1.0
     * @return void
     */
    public static function apply_discount()
    {

        if (isset($_POST['code'])) {

            $discount_code = sanitize_text_field($_POST['code']);

            $return = array(
                'msg' => '',
                'code' => $discount_code
            );

            $user = '';

            if (is_user_logged_in()) {
                $user = get_current_user_id();
            } else {
                parse_str($_POST['form'], $form);
                if (!empty($form['pl8app_email'])) {
                    $user = urldecode($form['pl8app_email']);
                }
            }

            if (pl8app_is_discount_valid($discount_code, $user)) {

                $discount = pl8app_get_discount_by_code($discount_code);
                $amount = pl8app_format_discount_rate(pl8app_get_discount_type($discount->ID), pl8app_get_discount_amount($discount->ID));
                $discounts = pl8app_set_cart_discount($discount_code);
                $total = pl8app_get_cart_total($discounts);
                $discount_value = pl8app_get_discount_value($discount_code, $total);

                $return = array(
                    'msg' => 'valid',
                    'discount_value' => $discount_value,
                    'amount' => $amount,
                    'total_plain' => $total,
                    'total' => html_entity_decode(pl8app_currency_filter(pl8app_format_amount($total)), ENT_COMPAT, 'UTF-8'),
                    'code' => $discount_code,
                    'html' => pl8app_get_cart_discounts_html($discounts)
                );

            } else {

                $errors = pl8app_get_errors();
                $return['msg'] = $errors['pl8app-discount-error'];
                pl8app_unset_error('pl8app-discount-error');
            }

            // Allow for custom discount code handling
            $return = apply_filters('pl8app_ajax_discount_response', $return);

            echo json_encode($return);
        }
        pl8app_die();
    }

    /**
     * Removes a discount code from the cart via ajax
     *
     * @since  1.0.0
     * @return void
     */
    public static function remove_discount()
    {

        if (isset($_POST['code'])) {

            pl8app_unset_cart_discount(urldecode($_POST['code']));

            $total = pl8app_get_cart_total();

            $return = array(
                'total' => html_entity_decode(pl8app_currency_filter(pl8app_format_amount($total)), ENT_COMPAT, 'UTF-8'),
                'code' => $_POST['code'],
                'discounts' => pl8app_get_cart_discounts(),
                'html' => pl8app_get_cart_discounts_html()
            );

            echo json_encode($return);
        }
        pl8app_die();
    }

    /**
     * Remove the all Store timing based on Emergency stop request
     */

    public static function emergency_stop(){

        check_ajax_referer( 'screen-options-nonce', 'screenoptionnonce' );
        $customer_edit_role = apply_filters( 'pl8app_edit_customers_role', 'edit_shop_payments' );
        if ( ! is_admin() || ! current_user_can( $customer_edit_role ) ) {
            pl8app_die();
        }

        $old_settings = get_option('pl8app_settings', array());
        $old_settings['emergency_stop'] = true;
        update_option('pl8app_settings', $old_settings);

        $return = array(
            'message' => 'Store is stopped succesfully!',
            'success' => 'success'
        );
        echo json_encode($return);
        pl8app_die();
    }

    /**
     * Disable Emergency stop request
     */
    public static function emergency_stop_disable(){
        check_ajax_referer( 'screen-options-nonce', 'screenoptionnonce' );
        $customer_edit_role = apply_filters( 'pl8app_edit_customers_role', 'edit_shop_payments' );
        if ( ! is_admin() || ! current_user_can( $customer_edit_role ) ) {
            pl8app_die();
        }

        $old_settings = get_option('pl8app_settings', array());
        $old_settings['emergency_stop'] = false;
        update_option('pl8app_settings', $old_settings);

        $return = array(
            'message' => 'Store Emergency stop is lifted succesfully!',
            'success' => 'success'
        );
        echo json_encode($return);
        pl8app_die();

    }

    public static function order_visual_widget_render(){

        check_ajax_referer( 'screen-options-nonce', 'screenoptionnonce' );
        $customer_edit_role = apply_filters( 'pl8app_edit_customers_role', 'edit_shop_payments' );
        if ( ! is_admin() || ! current_user_can( $customer_edit_role ) ) {
            pl8app_die();
        }

        if(empty($_POST['service_date'])) pl8app_die();

        $selected_date = $_POST['service_date'];
        $args = array(
            'output'     => 'pl8app_payments',
            'service_date' => $selected_date
        );

        $p_query  = new pl8app_Payments_Query( $args );
        $payments = $p_query->get_payments();


        $store_timing = new pl8app_StoreTiming_Functions();
        $day_number = $store_timing->get_weekday_number($selected_date);
        $time_interval = $store_timing::store_time_interval();

        $store_timings = pl8app_StoreTiming_Settings::pl8app_timing_options();

        if (!isset($store_timings['24hours'][$day_number])) {
            $open_time = isset($store_timings['open_time'][$day_number]) ? $store_timings['open_time'][$day_number] : '';
            $close_time = isset($store_timings['close_time'][$day_number]) ? $store_timings['close_time'][$day_number] : '';
        } else {
            $open_time = array(0 => '00:00');
            $close_time = array(0 => '23:59');
        }

        $store_times = array();
        $store_timeline = array();
        $rendered_content = '';
        if (!empty($open_time) && !empty($close_time) && is_array($open_time) && is_array($close_time)) {

            $time_format = get_option('time_format', true);
            $time_format = apply_filters('pla_store_time_format', $time_format);

            $otil_settings = get_option('pl8app_otil', array());
            $store_service_setting = get_option('pl8app_settings', array());
            $store_service_type = isset($store_service_setting['enable_service'])?$store_service_setting['enable_service']:array();
            $delivery_max = isset($otil_settings['orders_per_delivery_interval'])?$otil_settings['orders_per_delivery_interval']:'';
            $pickup_max = isset($otil_settings['orders_per_pickup_interval'])?$otil_settings['orders_per_pickup_interval']:'';

            foreach ($open_time as $index => $open_time_row) {
                if (!empty($open_time_row) && !empty($close_time[$index])) {
                    $store_times = array_merge($store_times, range(strtotime($open_time_row), strtotime($close_time[$index]), $time_interval));
                }
            }

            foreach ($store_times as $store_time) {
                $time = date($time_format, $store_time);
                $time = str_replace(' ', '', $time);
                $store_timeline[$time] = array('delivery' => 0, 'pickup' => 0);
            }

            if(!empty($payments) && is_array($payments)){
                foreach($payments as $payment){
                    $service_type = $payment->get_meta( '_pl8app_delivery_type' );
                    $service_time = $payment->get_meta( '_pl8app_delivery_time' );
                    if(isset($store_timeline[$service_time][$service_type])) {
                        $store_timeline[$service_time][$service_type] += 1;
                    }
                }
            }

            foreach($store_timeline as $time => $order_slot){

                ob_start();
                if(!empty($delivery_max))
                {
                    $del_percentage = round($order_slot['delivery']/$delivery_max, 2) * 100;
                }
                else{
                    $del_percentage = 'non-limit';
                }
                if(!empty($pickup_max))
                {
                    $pic_percentage = round($order_slot['pickup']/$pickup_max, 2) * 100;
                }
                else{
                    $pic_percentage = 'non-limit';
                }

                ?>
                <tr>
                    <td><?PHP echo $time;?></td>
                    <td>
                <?php if ( !empty($store_service_type['delivery'])) {
                    ?>
                        <div class="progress-bar horizontal"><strong>Delivery</strong></div>
                <?php }
                if(!empty($store_service_type['pickup'])) { ?>
                        <div class="progress-bar horizontal"><strong>Pickup<strong></strong></div>
                <?php } ?>
                    </td>
                    <td>
                <?php if (!empty($store_service_type['delivery'])) {
                    ?>
                        <div class="progress-bar horizontal">
                            <div class="progress-track">
                                <div class="progress-fill" style="<?PHP echo pl8app_get_chart_style($del_percentage);?>">
                                    <span ><?PHP echo $del_percentage;?>%</span>
                                </div>
                            </div>
                        </div>
                <?php }
                if(!empty($store_service_type['pickup'])) { ?>
                        <div class="progress-bar horizontal">
                            <div class="progress-track">
                                <div class="progress-fill" style="<?PHP echo pl8app_get_chart_style($pic_percentage);?>">
                                    <span><?PHP echo $pic_percentage;?>%</span>
                                </div>
                            </div>
                        </div>
                <?php } ?>
                    </td>
                </tr>
                <?PHP
                $order_slot_content = ob_get_clean();
                $rendered_content .= $order_slot_content;
            }
        }

        $return = array(
            'data' => $rendered_content,
            'order' => $store_timeline,
            'success' => 'success'
        );

        echo json_encode($return);
        pl8app_die();
    }

    /**
     * Loads Checkout Login Fields the via AJAX
     *
     * @since 1.0
     * @return void
     */
    public static function checkout_login()
    {

        do_action('pl8app_purchase_form_login_fields');
        pl8app_die();
    }

    /**
     * Load Checkout Register Fields via AJAX
     *
     * @since 1.0
     * @return void
     */
    public static function checkout_register()
    {

        do_action('pl8app_purchase_form_register_fields');
        pl8app_die();
    }

    /**
     * Recalculate cart taxes
     *
     * @since  1.0.0
     * @return void
     */
    public static function recalculate_taxes()
    {

        if (!pl8app_get_cart_contents()) {
            return false;
        }

        if (empty($_POST['billing_country'])) {
            $_POST['billing_country'] = pl8app_get_shop_country();
        }

        ob_start();
        pl8app_checkout_cart();
        $cart = ob_get_clean();
        $response = array(
            'html' => $cart,
            'tax_raw' => pl8app_get_cart_tax(),
            'tax' => html_entity_decode(pl8app_cart_tax(false), ENT_COMPAT, 'UTF-8'),
            'tax_rate_raw' => pl8app_get_tax_rate(),
            'tax_rate' => html_entity_decode(pl8app_get_formatted_tax_rate(), ENT_COMPAT, 'UTF-8'),
            'total' => html_entity_decode(pl8app_cart_total(false), ENT_COMPAT, 'UTF-8'),
            'total_raw' => pl8app_get_cart_total(),
        );

        echo json_encode($response);

        pl8app_die();
    }

    /**
     * Retrieve a states drop down
     *
     * @since  1.0.0
     * @return void
     */
    public static function get_states()
    {

        if (empty($_POST['country'])) {
            $_POST['country'] = pl8app_get_shop_country();
        }

        $states = pl8app_get_states($_POST['country']);

        if (!empty($states)) {

            $args = array(
                'name' => $_POST['field_name'],
                'id' => $_POST['field_name'],
                'class' => $_POST['field_name'] . '  pl8app-select',
                'options' => $states,
                'show_option_all' => false,
                'show_option_none' => false
            );

            $response = PL8PRESS()->html->select($args);

        } else {

            $response = 'nostates';
        }

        echo $response;

        pl8app_die();
    }

    /**
     * Search menu items
     *
     * @since  1.0.0
     * @return void
     */
    public static function menuitem_search()
    {

        global $wpdb;

        $search = esc_sql(sanitize_text_field($_GET['s']));
        $excludes = (isset($_GET['current_id']) ? (array)$_GET['current_id'] : array());

        $no_bundles = isset($_GET['no_bundles']) ? filter_var($_GET['no_bundles'], FILTER_VALIDATE_BOOLEAN) : false;
        if (true === $no_bundles) {
            $bundles = $wpdb->get_results("SELECT post_id FROM $wpdb->postmeta WHERE meta_key = '_pl8app_product_type' AND meta_value = 'bundle';", ARRAY_A);
            $bundles = wp_list_pluck($bundles, 'post_id');
            $excludes = array_merge($excludes, $bundles);
        }

        $variations = isset($_GET['variations']) ? filter_var($_GET['variations'], FILTER_VALIDATE_BOOLEAN) : false;

        $excludes = array_unique(array_map('absint', $excludes));
        $exclude = implode(",", $excludes);

        $results = array();

        // Setup the SELECT statement
        $select = "SELECT ID,post_title FROM $wpdb->posts ";

        // Setup the WHERE clause
        $where = "WHERE `post_type` = 'menuitem' and `post_title` LIKE '%s' ";

        // If we have items to exclude, exclude them
        if (!empty($exclude)) {
            $where .= "AND `ID` NOT IN (" . $exclude . ") ";
        }

        if (!current_user_can('edit_products')) {
            $status = apply_filters('pl8app_product_dropdown_status_nopriv', array('publish'));
        } else {
            $status = apply_filters('pl8app_product_dropdown_status', array('publish', 'draft', 'private', 'future'));
        }

        if (is_array($status) && !empty($status)) {

            $status = array_map('sanitize_text_field', $status);
            $status_in = "'" . join("', '", $status) . "'";
            $where .= "AND `post_status` IN ({$status_in}) ";

        } else {

            $where .= "AND `post_status` = `publish` ";

        }

        // Limit the result sets
        $limit = "LIMIT 50";

        $sql = $select . $where . $limit;

        $prepared_statement = $wpdb->prepare($sql, '%' . $search . '%');

        $items = $wpdb->get_results($prepared_statement);

        if ($items) {

            foreach ($items as $item) {

                $results[] = array(
                    'id' => $item->ID,
                    'name' => $item->post_title
                );

                if ($variations && pl8app_has_variable_prices($item->ID)) {
                    $prices = pl8app_get_variable_prices($item->ID);

                    foreach ($prices as $key => $value) {
                        $name = !empty($value['name']) ? $value['name'] : '';
                        $amount = !empty($value['amount']) ? $value['amount'] : '';
                        $index = !empty($value['index']) ? $value['index'] : $key;

                        if ($name && $index) {
                            $results[] = array(
                                'id' => $item->ID . '_' . $key,
                                'name' => esc_html($item->post_title . ': ' . $name),
                            );
                        }
                    }
                }
            }

        } else {

            $results[] = array(
                'id' => 0,
                'name' => __('No results found', 'pl8app')
            );

        }

        echo json_encode($results);

        pl8app_die();
    }

    /**
     * Processes the updated quantity value on
     * Checkout page
     *
     * @since 1.0.0
     */
    public function update_quantity()
    {

        if (empty($_POST['quantity']) || empty($_POST['menuitem_id']))
            return;

        $menuitem_id = absint($_POST['menuitem_id']);
        $quantity = absint($_POST['quantity']);
        $options = json_decode(stripslashes($_POST['options']), true);

        PL8PRESS()->cart->set_item_quantity($menuitem_id, $quantity, $options);

        $return = array(
            'menuitem_id' => $menuitem_id,
            'quantity' => PL8PRESS()->cart->get_item_quantity($menuitem_id, $options, $quantity),
            'subtotal' => html_entity_decode(pl8app_currency_filter(pl8app_format_amount(PL8PRESS()->cart->get_subtotal())), ENT_COMPAT, 'UTF-8'),
            'taxes' => html_entity_decode(pl8app_currency_filter(pl8app_format_amount(PL8PRESS()->cart->get_tax())), ENT_COMPAT, 'UTF-8'),
            'total' => html_entity_decode(pl8app_currency_filter(pl8app_format_amount(PL8PRESS()->cart->get_total())), ENT_COMPAT, 'UTF-8')
        );

        // Allow for custom cart item quantity handling
        $return = apply_filters('pl8app_ajax_cart_item_quantity_response', $return);
        echo json_encode($return);
        pl8app_die();
    }

    /**
     * Search the customers database via AJAX
     *
     * @since  1.0.0
     * @return void
     */
    public static function customer_search()
    {

        global $wpdb;

        $search = esc_sql(sanitize_text_field($_GET['s']));
        $results = array();
        $customer_view_role = apply_filters('pl8app_view_customers_role', 'view_shop_reports');
        if (!current_user_can($customer_view_role)) {
            $customers = array();
        } else {
            $select = "SELECT id, name, email FROM {$wpdb->prefix}pl8app_customers ";
            if (is_numeric($search)) {
                $where = "WHERE `id` LIKE '%$search%' OR `user_id` LIKE '%$search%' ";
            } else {
                $where = "WHERE `name` LIKE '%$search%' OR `email` LIKE '%$search%' ";
            }
            $limit = "LIMIT 50";

            $customers = $wpdb->get_results($select . $where . $limit);
        }

        if ($customers) {

            foreach ($customers as $customer) {

                $results[] = array(
                    'id' => $customer->id,
                    'name' => $customer->name . '(' . $customer->email . ')'
                );
            }

        } else {

            $customers[] = array(
                'id' => 0,
                'name' => __('No results found', 'pl8app')
            );

        }

        echo json_encode($results);

        pl8app_die();
    }

    /**
     * Search the users database via AJAX
     *
     * @since 1.0.0
     * @return void
     */
    public static function user_search()
    {

        global $wpdb;

        $search = esc_sql(sanitize_text_field($_GET['s']));
        $results = array();
        $user_view_role = apply_filters('pl8app_view_users_role', 'view_shop_reports');

        if (!current_user_can($user_view_role)) {
            $results = array();
        } else {
            $user_args = array(
                'search' => '*' . esc_attr($search) . '*',
                'number' => 50,
            );

            $users = get_users($user_args);
        }

        if ($users) {

            foreach ($users as $user) {

                $results[] = array(
                    'id' => $user->ID,
                    'name' => $user->display_name,
                );
            }

        } else {

            $results[] = array(
                'id' => 0,
                'name' => __('No users found', 'pl8app')
            );

        }

        echo json_encode($results);

        pl8app_die();
    }

    /**
     * Searches for users via ajax and returns a list of results
     *
     * @since  1.0.0
     * @return void
     */
    public static function search_users()
    {

        if (current_user_can('manage_shop_settings')) {

            $search_query = trim($_POST['user_name']);
            $exclude = trim($_POST['exclude']);

            $get_users_args = array(
                'number' => 9999,
                'search' => $search_query . '*'
            );

            if (!empty($exclude)) {
                $exclude_array = explode(',', $exclude);
                $get_users_args['exclude'] = $exclude_array;
            }

            $get_users_args = apply_filters('pl8app_search_users_args', $get_users_args);

            $found_users = apply_filters('pl8app_ajax_found_users', get_users($get_users_args), $search_query);

            $user_list = '<ul>';
            if ($found_users) {
                foreach ($found_users as $user) {
                    $user_list .= '<li><a href="#" data-userid="' . esc_attr($user->ID) . '" data-login="' . esc_attr($user->user_login) . '">' . esc_html($user->user_login) . '</a></li>';
                }
            } else {
                $user_list .= '<li>' . __('No users found', 'pl8app') . '</li>';
            }
            $user_list .= '</ul>';

            echo json_encode(array('results' => $user_list));

        }
        die();
    }

    /**
     * Check for new orders and send notification
     *
     * @since       2.0.1
     * @param       void
     * @return      json | user notification json object
     */
    public static function check_new_orders()
    {
        $last_order = get_option('pla_last_order_id');
        $user = wp_get_current_user();
        if(empty($user->ID)) wp_die();
        if(!isset($last_order) || !is_array($last_order)) $last_order = array();

        $last_order_by_user = isset($last_order[$user->ID])?$last_order[$user->ID]:'';
        $order = pl8app_get_payments(array( 'last_old_id' => $last_order_by_user));

        if (is_array($order) && count($order) && $order[0]->ID != $last_order_by_user) {

            $payment_id = $order[0]->ID;

            $printer_setting = new pl8app_Print_Settings();
            $print_content = $printer_setting->pl8app_print_payment_data($payment_id);
            $order_status = get_post_meta($payment_id,'_order_status', true);
            !isset($order_status)? wp_die(): '';

            $option = get_option('pl8app_settings');

            $enable_auto_printing = isset($option['enable_auto_printing']) && $option['enable_auto_printing'] == 1? true: false;
            $auto_printing_option = isset($option['auto_print'])?$option['auto_print']:array();
            $enable_order_status_auto_printing = isset($auto_printing_option[$order_status]['status'])&& $auto_printing_option[$order_status]['status'] ==- 1 ? false: true;

            $notification = array(
                'title' => __('New Order', 'pl8app'),
                'url' => admin_url('admin.php?page=pl8app-payment-history&view=view-order-details&id=' . $payment_id)
            );

            if($enable_auto_printing && $enable_order_status_auto_printing){

                $notification['print_content'] = $print_content;
                $notification['order_status'] = $order_status;
                $notification['copies_per_print'] = !empty($auto_printing_option[$order_status]['copies'])? $auto_printing_option[$order_status]['copies']: 1;
            }
            $last_order[$user->ID] = $payment_id;
            update_option('pla_last_order_id', $last_order);
            wp_send_json($notification);
        }
        wp_die();
    }

    /**
     * Activate addon license with ajax call
     *
     * @since 2.5
     * @author Restpl8app
     */
    public function activate_addon_license()
    {

        // listen for our activate button to be clicked
        if (isset($_POST['license_key'])) {

            // Get the license from the user
            // Item ID (Normally a 2 or 3 digit code)
            $item_id = isset($_POST['item_id']) ? absint($_POST['item_id']) : '';

            // The actual license code
            $license = isset($_POST['license']) ? trim($_POST['license']) : '';

            // Name of the addon (Print Receipts)
            $name = isset($_POST['product_name']) ? $_POST['product_name'] : '';

            // Key to be saved in to DB
            $license_key = isset($_POST['license_key']) ? $_POST['license_key'] : '';

            // data to send in our API request
            $api_params = array(
                'edd_action' => 'activate_license',
                'item_id' => $item_id,
                'item_name' => urlencode($name),
                'license' => $license,
                'url' => home_url()
            );

            // Call the custom API.
            $response = wp_remote_post('https://www.pl8app.com', array('timeout' => 15, 'sslverify' => false, 'body' => $api_params));

            // make sure the response came back okay
            if (is_wp_error($response)
                || 200 !== wp_remote_retrieve_response_code($response)) {

                if (is_wp_error($response)) {
                    $message = $response->get_error_message();
                } else {
                    $message = __('An error occurred, please try again.');
                }

            } else {

                $license_data = json_decode(wp_remote_retrieve_body($response));

                if (false === $license_data->success) {

                    switch ($license_data->error) {

                        case 'expired' :

                            $message = sprintf(
                                __('Your license key expired on %s.'),
                                date_i18n(get_option('date_format'), strtotime($license_data->expires, current_time('timestamp')))
                            );
                            break;

                        case 'revoked' :

                            $message = __('Your license key has been disabled.');
                            break;

                        case 'missing' :

                            $message = __('Invalid license.');
                            break;

                        case 'invalid' :
                        case 'site_inactive' :

                            $message = __('Your license is not active for this URL.');
                            break;

                        case 'item_name_mismatch' :

                            $message = sprintf(__('This appears to be an invalid license key for %s.'), $name);
                            break;

                        case 'no_activations_left':

                            $message = __('Your license key has reached its activation limit.');
                            break;

                        default :

                            $message = __('An error occurred, please try again.');
                            break;
                    }
                }
            }

            // Check if anything passed on a message constituting a failure
            if (!empty($message))

                $return = array('status' => 'error', 'message' => $message);

            else {

                //Save the license key in database
                update_option($license_key, $license);

                // $license_data->license will be either "valid" or "invalid"
                update_option($license_key . '_status', $license_data->license);
                $return = array('status' => 'updated', 'message' => 'Your license is successfully activated.');
            }

            echo json_encode($return);
            wp_die();
        }
    }

    /**
     * Deactivate the license of plugin with AJAX call
     *
     * @since 2.5
     * @author pl8app
     * @return void
     */
    public function deactivate_addon_license()
    {

        if (isset($_POST['license_key'])) {

            $license_key = isset($_POST['license_key']) ? $_POST['license_key'] : '';

            // retrieve the license from the database
            $license = trim(get_option($license_key));

            $item_name = isset($_POST['product_name']) ? $_POST['product_name'] : '';

            // data to send in our API request
            $api_params = array(
                'edd_action' => 'deactivate_license',
                'license' => $license,
                'item_name' => urlencode($item_name), // the name of our product in EDD
                'url' => home_url()
            );

            // Call the custom API.
            $response = wp_remote_post('https://www.pl8app.com', array('timeout' => 15, 'sslverify' => false, 'body' => $api_params));

            // make sure the response came back okay
            if (is_wp_error($response) || 200 !== wp_remote_retrieve_response_code($response)) {

                if (is_wp_error($response)) {
                    $message = $response->get_error_message();
                } else {
                    $message = __('An error occurred, please try again.', 'pl8app');
                }
                $return = array('status' => 'error', 'message' => $message);

            } else {

                // decode the license data
                $license_data = json_decode(wp_remote_retrieve_body($response));

                // $license_data->license will be either "deactivated" or "failed"
                if ($license_data->license == 'deactivated') {
                    delete_option($license_key . '_status');
                    delete_option($license_key);
                }
                $return = array('status' => 'updated', 'message' => __('License successfully deactivated.', 'pl8app'));
            }
            echo json_encode($return);
            wp_die();
        }
    }
}

pla_AJAX::init();