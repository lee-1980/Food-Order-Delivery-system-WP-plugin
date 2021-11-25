<?php


// Exit if accessed directly
if (!defined('ABSPATH')) exit;

/**
 * Get Cart Items By Key
 *
 * @since       1.0
 * @param       int | key
 * @return      array | cart items array
 */
function pl8app_get_cart_items_by_key($key)
{
    $cart_items_arr = array();
    if ($key !== '') {
        $cart_items = pl8app_get_cart_contents();
        if (is_array($cart_items) && !empty($cart_items)) {
            $items_in_cart = $cart_items[$key];
            if (is_array($items_in_cart)) {
                if (isset($items_in_cart['addon_items'])) {
                    $cart_items_arr = $items_in_cart['addon_items'];
                }
            }
        }
    }
    return $cart_items_arr;
}

/**
 * Get Cart Items Price
 *
 * @since       1.0
 * @param       int | key
 * @return      int | total price for cart
 */
function pl8app_get_cart_item_by_price($key)
{
    $cart_items_price = array();

    if ($key !== '') {
        $cart_items = pl8app_get_cart_contents();

        if (is_array($cart_items) && !empty($cart_items)) {
            $items_in_cart = $cart_items[$key];
            if (is_array($items_in_cart)) {
                $item_price = pl8app_get_menuitem_price($items_in_cart['id']);

                if ($items_in_cart['quantity'] > 0) {
                    $item_price = $item_price * $items_in_cart['quantity'];
                }
                array_push($cart_items_price, $item_price);

                if (isset($items_in_cart['addon_items']) && is_array($items_in_cart['addon_items'])) {
                    foreach ($items_in_cart['addon_items'] as $item_list) {
                        array_push($cart_items_price, $item_list['price']);
                    }
                }

            }
        }
    }

    $cart_item_total = array_sum($cart_items_price);
    return $cart_item_total;
}

function addon_category_taxonomy_custom_fields($tag)
{
    $t_id = $tag->term_id;
    $term_meta = get_option("taxonomy_term_$t_id");
    $use_addon_like = isset($term_meta['use_it_like']) ? $term_meta['use_it_like'] : 'checkbox';
    ?>
    <?php if ($tag->parent != 0): ?>
    <tr class="form-field">
        <th scope="row" valign="top">
            <label for="price_id"><?php _e('Price'); ?></label>
        </th>
        <td>
            <input type="number" step=".01" name="term_meta[price]" id="term_meta[price]" size="25" style="width:15%;"
                   value="<?php echo isset($term_meta['price']) ? $term_meta['price'] : ''; ?>"><br/>
            <span class="description"><?php _e('Price for this Option and Upgrade item'); ?></span>
        </td>
    </tr>
<?php endif; ?>

    <?php if ($tag->parent == 0): ?>
    <tr class="form-field">
        <th scope="row" valign="top">
            <label for="use_it_as">
                <?php _e('Option and Upgrade item selection type', 'pl8app'); ?></label>
        </th>
        <td>
            <div class="use-it-like-wrap">
                <label for="use_like_radio">
                    <input id="use_like_radio" type="radio" value="radio"
                           name="term_meta[use_it_like]" <?php checked($use_addon_like, 'radio'); ?> >
                    <?php _e('Single item', 'pl8app'); ?>
                </label>
                <br/><br/>
                <label for="use_like_checkbox">
                    <input id="use_like_checkbox" type="radio" value="checkbox"
                           name="term_meta[use_it_like]" <?php checked($use_addon_like, 'checkbox'); ?> >
                    <?php _e('Multiple Items', 'pl8app'); ?>
                </label>
            </div>
        </td>
    </tr>
<?php endif; ?>

    <?php
}

function add_addon_category_taxonomy_custom_fields(){
    ?>
    <div class="form-field addon-item-selection-type">
            <label for="use_it_as">
                <?php _e('Option and Upgrade item selection type', 'pl8app'); ?></label>

            <div class="use-it-like-wrap">
                <label for="use_like_radio">
                    <input id="use_like_radio" type="radio" value="radio"
                           name="term_meta[use_it_like]">
                    <?php _e('Single item', 'pl8app'); ?>
                </label>

                <label for="use_like_checkbox">
                    <input id="use_like_checkbox" type="radio" value="checkbox"
                           name="term_meta[use_it_like]">
                    <?php _e('Multiple Items', 'pl8app'); ?>
                </label>
            </div>
            <br/><br/>
    </div>

    <div class="form-field addon-item-price" style="display: none;">
        <label for="price_id"><?php _e('Price'); ?></label>
        <input type="number" step=".01" name="term_meta[price]" id="term_meta[price]" size="25" style="width:40%;"><br/>
        <span class="description"><?php _e('Price for this Option and Upgrade item'); ?></span>
    </div>
    <script>
        jQuery(document.body).on('change','select#parent', function(){
            if(jQuery(this).val() == -1){
                jQuery('.addon-item-selection-type').show();
                jQuery('.addon-item-price').hide();
            }
            else{
                jQuery('.addon-item-selection-type').hide();
                jQuery('.addon-item-price').show();
            }
        });
    </script>
    <?php
}

/**
 * Update taxonomy meta data
 *
 * @since       1.0
 * @param       int | term_id
 * @return      update meta data
 */
function save_addon_category_custom_fields($term_id)
{
    if (isset($_POST['term_meta'])) {
        $t_id = $term_id;
        $term_meta = get_option("taxonomy_term_$t_id");
        if(!isset($term_meta)) $term_meta = array();
        $cat_keys = array_keys($_POST['term_meta']);

        if (is_array($cat_keys) && !empty($cat_keys)) {
            foreach ($cat_keys as $key) {
                if (isset($_POST['term_meta'][$key])) {
                    $term_meta[$key] = $_POST['term_meta'][$key];
                }
            }
        }

        //save the option array
        update_option("taxonomy_term_$t_id", $term_meta);
    }
}

// Add the fields to the "addon_category" taxonomy, using our callback function
add_action('addon_category_edit_form_fields', 'addon_category_taxonomy_custom_fields', 10, 2);

// Add the fields to the "addon_category" taxonomy, using our callback function
add_action('addon_category_add_form_fields', 'add_addon_category_taxonomy_custom_fields', 10, 2);

// Save the changes made on the "addon_category" taxonomy, using our callback function
add_action('edited_addon_category', 'save_addon_category_custom_fields', 10, 2);

// Save the news created on the "addon_category" taxonomy, using our callback function
add_action('create_addon_category', 'save_addon_category_custom_fields', 10, 2);


/**
 * Get menu item quantity in the cart by key
 *
 * @since       1.0
 * @param       int | cart_key
 * @return      array | cart items array
 */
function pl8app_get_item_qty_by_key($cart_key)
{
    if ($cart_key !== '') {
        $cart_items = pl8app_get_cart_contents();
        $cart_items = $cart_items[$cart_key];
        return $cart_items['quantity'];
    }
}

add_action('wp_footer', 'pl8app_popup');
if (!function_exists('pl8app_popup')) {
    function pl8app_popup()
    {
        pl8app_get_template_part('pl8app', 'popup');
    }
}


add_action('pla_get_categories', 'get_menuitems_categories');

if (!function_exists('get_menuitems_categories')) {
    function get_menuitems_categories($params)
    {
        global $data;
        $data = $params;
        pl8app_get_template_part('pl8app', 'get-categories');
    }
}

if (!function_exists('pl8app_search_form')) {
    function pl8app_search_form()
    {
        ?>
        <div class="pl8app-search-wrap pl8app-live-search">
            <input id="pl8app-menu-search" type="text" placeholder="<?php _e('Search Menu Item', 'pl8app') ?>">
        </div>
        <?php
    }
}

add_action('before_menuitems_list', 'pl8app_search_form');

if (!function_exists('pl8app_product_menu_tab')) {
    /**
     * Output the pl8app menu tab content.
     */
    function pl8app_product_menu_tab()
    {
        echo do_shortcode('[pl8app_items]');
    }
}

/**
 * Get special instruction for menu items
 *
 * @since       1.0
 * @param       array | menu items
 * @return      string | Special instruction string
 */
function get_special_instruction($items)
{
    $instruction = '';

    if (is_array($items)) {
        if (isset($items['options'])) {
            $instruction = $items['options']['instruction'];
        } else {
            if (isset($items['instruction'])) {
                $instruction = $items['instruction'];
            }
        }
    }

    return apply_filters('pl8app_sepcial_instruction', $instruction);
}

/**
 * Get the Tax name for menu items
 * @param $items
 */
function get_cart_item_tax_name($item) {

    $tax_name = '';

    if(isset($item['options'])){
        if(pl8app_use_taxes() && isset($item['options']['tax_key'])){
            $tax_object = pl8app_get_tax_rates($item['options']['tax_key']);
            if(isset($tax_object['name'])){
                $tax_name = $tax_object['name'];
            }
        }
    }
    else{
        if(pl8app_use_taxes() && isset($item['tax_key'])){
            $tax_object = pl8app_get_tax_rates($item['tax_key']);
            if(isset($tax_object['name'])){
                $tax_name = $tax_object['name'];
            }
        }
    }

    return $tax_name;
}
/**
 * Get instruction in the cart by key
 *
 * @since       1.0
 * @param       int | cart_key
 * @return      string | Special instruction string
 */
function pl8app_get_instruction_by_key($cart_key)
{
    $instruction = '';
    if ($cart_key !== '') {
        $cart_items = pl8app_get_cart_contents();
        $cart_items = $cart_items[$cart_key];
        if (isset($cart_items['instruction'])) {
            $instruction = !empty($cart_items['instruction']) ? $cart_items['instruction'] : '';
        }
    }
    return $instruction;
}

/**
 * Show delivery options in the cart
 *
 * @since       1.0.2
 * @param       void
 * @return      string | Outputs the html for the delivery options with texts
 */
function get_delivery_options($changeble)
{

    $service_date = isset($_COOKIE['delivery_date']) ? $_COOKIE['delivery_date'] : '';
    ob_start();
    ?>
    <div class="pl8app item-order">
        <span>Service</span>
    </div>
    <div class="delivery-wrap">
        <div class="delivery-opts">
            <?php if (!empty($_COOKIE['service_type'])) : ?>
                <span class="delMethod"><?php echo pl8app_service_label($_COOKIE['service_type']) . ', ' . $service_date; ?></span><?php if (!empty($_COOKIE['service_time'])) : ?>
                    <span class="delTime"><?php printf(__(', %s', 'pl8app'), sanitize_text_field($_COOKIE['service_time_text'])); ?></span><?php endif; ?>
            <?php endif; ?>
        </div>
        <?php if ($changeble && !empty($_COOKIE['service_type'])) { ?>
            <a href="#" class="delivery-change"><?php esc_html_e('Change?', 'pl8app'); ?></a>
        <?php } else { ?>
            <a href="#" class="delivery-change new"><span><i class="fa fa-calendar" aria-hidden="true"></i></span><?php esc_html_e('Choose the Service Type, Date and Slot!', 'pl8app'); ?></a>
        <?php } ?>
    </div>
    <?php
    $data = ob_get_contents();
    ob_get_clean();
    return $data;
}

/**
 * Stores delivery address meta
 *
 * @since       1.0.3
 * @param       array | Delivery address meta array
 * @return      array | Custom data with delivery address meta array
 */
function pl8app_store_custom_fields($delivery_address_meta)
{
    $delivery_address_meta['address'] = !empty($_POST['pl8app_street_address']) ? sanitize_text_field($_POST['pl8app_street_address']) : '';
    $delivery_address_meta['flat'] = !empty($_POST['pl8app_apt_suite']) ? sanitize_text_field($_POST['pl8app_apt_suite']) : '';
    $delivery_address_meta['city'] = !empty($_POST['pl8app_city']) ? sanitize_text_field($_POST['pl8app_city']) : '';
    $delivery_address_meta['postcode'] = !empty($_POST['pl8app_postcode']) ? sanitize_text_field($_POST['pl8app_postcode']) : '';
    return $delivery_address_meta;
}

add_filter('pl8app_delivery_address_meta', 'pl8app_store_custom_fields');


/**
 * Add order note to the order
 */
add_filter('pl8app_order_note_meta', 'pl8app_order_note_fields');
function pl8app_order_note_fields($order_note)
{
    $order_note = isset($_POST['pl8app_order_note']) ? sanitize_text_field($_POST['pl8app_order_note']) : '';
    return $order_note;
}

/**
 * Add phone number to payment meta
 */
add_filter('pl8app_payment_meta', 'pl8app_add_phone');
function pl8app_add_phone($payment_meta)
{
    if (!empty($_POST['pl8app_phone']))
        $payment_meta['phone'] = $_POST['pl8app_phone'];
    return $payment_meta;
}

/**
 * Get Service type
 *
 * @since       1.0.4
 * @param       Int | Payment_id
 * @return      string | Service type string
 */
function pl8app_get_service_type($payment_id)
{
    if ($payment_id) {
        $service_type = get_post_meta($payment_id, '_pl8app_delivery_type', true);
        return strtolower($service_type);
    }
}

/* Remove View Link From Menu Items */
add_filter('post_row_actions', 'pl8app_remove_view_link', 10, 2);

function pl8app_remove_view_link($actions, $post)
{
    if ($post->post_type == "menuitem") {
        unset($actions['view']);
    }
    return $actions;
}

/* Remove View Link From Menu Addon Category */
add_filter('addon_category_row_actions', 'pl8app_remove_tax_view_link', 10, 2);

function pl8app_remove_tax_view_link($actions, $taxonomy)
{
    if ($taxonomy->taxonomy == 'addon_category') {
        unset($actions['view']);
    }
    return $actions;
}

/* Remove View Link From Menu Category */
add_filter('menu-category_row_actions', 'pl8app_remove_menu_cat_view_link', 10, 2);

function pl8app_remove_menu_cat_view_link($actions, $taxonomy)
{
    if ($taxonomy->taxonomy == 'menu-category') {
        unset($actions['view']);
    }
    return $actions;
}

/**
 * Get store timings for the store
 *
 * @since       1.0.0
 * @return      array | store timings
 */
function pla_get_store_timings($hide_past_time = true)
{

    $current_time = current_time('timestamp');
    $prep_time = !empty(pl8app_get_option('prep_time')) ? pl8app_get_option('prep_time') : 30;
    $open_time = !empty(pl8app_get_option('open_time')) ? pl8app_get_option('open_time') : '12:00am';
    $close_time = !empty(pl8app_get_option('close_time')) ? pl8app_get_option('close_time') : '11:30pm';

    $time_interval = apply_filters('pla_store_time_interval', '30');
    $time_interval = $time_interval * 60;

    $prep_time = $prep_time * 60;
    $open_time = strtotime(date_i18n('Y-m-d') . ' ' . $open_time);
    $close_time = strtotime(date_i18n('Y-m-d') . ' ' . $close_time);
    $time_today = apply_filters('pl8app_timing_for_today', true);

    $store_times = range($open_time, $close_time, $time_interval);

    //If not today then return normal time
    if (!$time_today) return $store_times;

    //Add prep time to current time to determine the time to display for the dropdown
    if ($prep_time > 0) {
        $current_time = $current_time + $prep_time;
    }
    //Store timings for today.
    $store_timings = [];
    foreach ($store_times as $store_time) {
        if ($hide_past_time) {
            if ($store_time > $current_time) {
                $store_timings[] = $store_time;
            }
        } else {
            $store_timings[] = $store_time;
        }

    }
    return $store_timings;
}

/**
 * Get the 24 hours slot range
 */
function pla_get_24hours_timings(){

    $open_time = '00:00';
    $close_time = '23:30';

    $time_interval = apply_filters('pl8app_store_time_interval', '30');
    $time_interval = $time_interval * 60;

    $store_times = range(strtotime($open_time), strtotime($close_time), $time_interval);

    return $store_times;
}

/**
 * Get current time
 *
 * @since       1.0.0
 * @return      string | current time
 */
function pla_get_current_time()
{
    $current_time = '';
    $timezone = get_option('timezone_string');
    if (!empty($timezone)) {
        $tz = new DateTimeZone($timezone);
        $dt = new DateTime("now", $tz);
        $current_time = $dt->format("H:i:s");
    }
    return $current_time;
}

/**
 * Get current date
 *
 * @since       1.0.0
 * @return      string | current date
 */
function pla_current_date($format = '')
{
    $date_format = empty($format) ? get_option('date_format') : $format;
    $date_i18n = date_i18n($date_format);
    return apply_filters('pl8app_current_date', $date_i18n);
}

/**
 * Get local date from date string
 *
 * @since       1.0.0
 * @return      string | localized date based on date string
 */
function pl8app_local_date($date)
{
    $date_format = apply_filters('pl8app_date_format', get_option('date_format', true));
    $timestamp = strtotime($date);
    $local_date = empty(get_option('timezone_string')) ? date_i18n($date_format, $timestamp) : wp_date($date_format, $timestamp);
    $day = date('l', $timestamp);
    $local_date .= ' '. $day;
    return apply_filters('pl8app_local_date', $local_date, $date);
}

/**
 * Get list of categories
 *
 * @since 2.2.4
 * @return array of categories
 */
function pl8app_get_categories($params = array())
{

    if (!empty($params['ids'])) {
        $params['include'] = $params['ids'];
        $params['orderby'] = 'include';
    }

    unset($params['ids']);

    $defaults = array(
        'taxonomy' => 'menu-category',
        'hide_empty' => true,
        'orderby' => 'name',
        'order' => 'ASC',
    );
    $term_args = wp_parse_args($params, $defaults);
    $term_args = apply_filters('pl8app_get_categories', $term_args);
    $get_all_items = get_terms($term_args);

    return $get_all_items;
}

function pl8app_get_service_types()
{
    $service_types = array(
        'delivery' => __('Delivery', 'pl8app'),
        'pickup' => __('Pickup', 'pl8app')
    );
    return apply_filters('pl8app_service_type', $service_types);
}

/**
 * Get Store service hours
 * @since 3.0
 * @param string $service_type Select service type
 * @param bool $current_time_aware if current_time_aware is set true then it would show the next time from now otherwise it would show the default store timings
 * @return store time
 */
function pla_get_store_service_hours($service_type, $current_time_aware = true, $selected_time)
{

    if (empty($service_type)) {
        return;
    }

    $time_format = get_option('time_format', true);
    $time_format = apply_filters('pla_store_time_format', $time_format);

    $current_time = !empty(pla_get_current_time()) ? pla_get_current_time() : date($time_format);
    $store_times = pla_get_store_timings(false);

    if ($service_type == 'delivery') {
        $store_timings = apply_filters('pl8app_store_delivery_timings', $store_times);
    } else {
        $store_timings = apply_filters('pl8app_store_pickup_timings', $store_times);
    }

    $store_timings_for_today = apply_filters('pl8app_timing_for_today', true);

    if (is_array($store_timings)) {


        foreach ($store_timings as $time) {

            // Bring both curent time and Selected time to Admin Time Format
            $store_time = date($time_format, $time);
            $selected_time = date($time_format, strtotime($selected_time));

            if ($store_timings_for_today) {

                // Remove any extra space in Current Time and Selected Time
                $timing_slug = str_replace(' ', '', $store_time);
                $selected_time = str_replace(' ', '', $selected_time);

                if ($current_time_aware) {

                    if (strtotime($store_time) > strtotime($current_time)) { ?>

                        <option <?php selected($selected_time, $timing_slug); ?> value='<?php echo $store_time; ?>'>
                            <?php echo $store_time; ?>
                        </option>

                    <?php }

                } else { ?>

                    <option <?php selected($selected_time, $timing_slug); ?> value='<?php echo $store_time; ?>'>
                        <?php echo $store_time; ?>
                    </option>

                <?php }
            }
        }
    }
}

/**
 * Get list of categories/subcategories
 *
 * @since 2.3
 * @return array of Get list of categories/subcategories
 */
function pl8app_get_child_cats($category)
{
    $taxonomy_name = 'menu-category';
    $parent_term = $category[0];
    $get_child_terms = get_terms($taxonomy_name,
        ['child_of' => $parent_term]);

    if (empty($get_child_terms)) {
        $parent_terms = array(
            'taxonomy' => $taxonomy_name,
            'hide_empty' => true,
            'include' => $category,
        );

        $get_child_terms = get_terms($parent_terms);
    }
    return $get_child_terms;
}

add_filter('post_updated_messages', 'pl8app_menuitem_update_messages');
function pl8app_menuitem_update_messages($messages)
{
    global $post, $post_ID;

    $post_types = get_post_types(array('show_ui' => true, '_builtin' => false), 'objects');

    foreach ($post_types as $post_type => $post_object) {
        if ($post_type == 'menuitem') {
            $messages[$post_type] = array(
                0 => '', // Unused. Messages start at index 1.
                1 => sprintf(__('%s updated.'), $post_object->labels->singular_name),
                2 => __('Custom field updated.'),
                3 => __('Custom field deleted.'),
                4 => sprintf(__('%s updated.'), $post_object->labels->singular_name),
                5 => isset($_GET['revision']) ? sprintf(__('%s restored to revision from %s'), $post_object->labels->singular_name, wp_post_revision_title((int)$_GET['revision'], false)) : false,
                6 => sprintf(__('%s published.'), $post_object->labels->singular_name),
                7 => sprintf(__('%s saved.'), $post_object->labels->singular_name),
                8 => sprintf(__('%s submitted'), $post_object->labels->singular_name),
                9 => sprintf(__('%s scheduled for: <strong>%1$s</strong>'), $post_object->labels->singular_name, date_i18n(__('M j, Y @ G:i'), strtotime($post->post_date)), $post_object->labels->singular_name),
                10 => sprintf(__('%s draft updated.'), $post_object->labels->singular_name),
            );
        }
    }

    return $messages;

}

/**
 * Return the html selected attribute if stringified $value is found in array of stringified $options
 * or if stringified $value is the same as scalar stringified $options.
 *
 * @param string|int $value Value to find within options.
 * @param string|int|array $options Options to go through when looking for value.
 * @return string
 */
function pla_selected($value, $options)
{
    if (is_array($options)) {
        $options = array_map('strval', $options);
        return selected(in_array((string)$value, $options, true), true, false);
    }
    return selected($value, $options, false);
}


/**
 * Return the currently selected service type
 *
 * @since       2.5
 * @param       string | type
 * @return      string | Currently selected service type
 */
function pl8app_selected_service($type = '')
{
    $service_type = isset($_COOKIE['service_type']) ? $_COOKIE['service_type'] : '';
    //Return service type label when $type is label
    if ($type == 'label')
        $service_type = pl8app_service_label($service_type);

    return $service_type;
}

/**
 * Return the service type label based on the service slug.
 *
 * @since       2.5
 * @param       string | service type
 * @return      string | Service type label
 */
function pl8app_service_label($service)
{
    $service_types = array(
            'undefined' => __('No service', 'pl8app'),
        'delivery' => __('Delivery', 'pl8app'),
        'pickup' => __('Pickup', 'pl8app'),
    );
    //Allow to filter the service types.
    $service_types = apply_filters('pl8app_service_types', $service_types);

    //Check for the service key in the service types and return the service type label
    if (array_key_exists($service, $service_types))
        $service = $service_types[$service];

    return $service;
}

/**
 * Save order type in session
 *
 * @since       1.0.4
 * @param       string | Delivery Type
 * @param           string | Delivery Time
 * @return      array  | Session array for delivery type and delivery time
 */
function pl8app_checkout_delivery_type($service_type, $service_time)
{

    $_COOKIE['service_type'] = $service_type;
    $_COOKIE['service_time'] = $service_time;
}

/**
 * Validates the cart before checkout
 *
 * @since       2.5
 * @param       void
 * @return      array | Respose as success/error
 */
function pl8app_pre_validate_order()
{

    $service_type = !empty($_COOKIE['service_type']) ? $_COOKIE['service_type'] : '';
    $service_time = !empty($_COOKIE['service_time']) ? $_COOKIE['service_time'] : '';
    $service_date = !empty($_COOKIE['service_date']) ? $_COOKIE['service_date'] : current_time('Y-m-d');
    $prep_time = pl8app_get_option('prep_time', 0);
    $prep_time = $prep_time * 60;
    $current_time = current_time('timestamp');

    if ($prep_time > 0) {
        $current_time = $current_time + $prep_time;
    }

    $service_time = strtotime($service_date . ' ' . $service_time);

    // Check minimum order
    $enable_minimum_order = pl8app_get_option('allow_minimum_order');
    $minimum_order_price_delivery = pl8app_get_option('minimum_order_price');
    $minimum_order_price_delivery = floatval($minimum_order_price_delivery);
    $minimum_order_price_pickup = pl8app_get_option('minimum_order_price_pickup');
    $minimum_order_price_pickup = floatval($minimum_order_price_pickup);

    if ($enable_minimum_order && $service_type == 'delivery' && pl8app_get_cart_subtotal() < $minimum_order_price_delivery) {
        $minimum_price_error = pl8app_get_option('minimum_order_error');
        $minimum_order_formatted = pl8app_currency_filter(pl8app_format_amount($minimum_order_price_delivery));
        $minimum_price_error = str_replace('{min_order_price}', $minimum_order_formatted, $minimum_price_error);
        $response = array('status' => 'error', 'minimum_price' => $minimum_order_price, 'error_msg' => $minimum_price_error);
    } else if ($enable_minimum_order && $service_type == 'pickup' && pl8app_get_cart_subtotal() < $minimum_order_price_pickup) {
        $minimum_price_error_pickup = pl8app_get_option('minimum_order_error_pickup');
        $minimum_order_formatted = pl8app_currency_filter(pl8app_format_amount($minimum_order_price_pickup));
        $minimum_price_error_pickup = str_replace('{min_order_price}', $minimum_order_formatted, $minimum_price_error_pickup);
        $response = array('status' => 'error', 'minimum_price' => $minimum_order_price_pickup, 'error_msg' => $minimum_price_error_pickup);
    } else if ($current_time > $service_time && !empty($_COOKIE['service_time'])) {
        $time_error = __('Please select a different time slot.', 'pl8app');
        $response = array(
            'status' => 'error',
            'error_msg' => $time_error
        );
    } else {
        $response = array('status' => 'success');
    }
    return $response;
}

/**
 * Is Test Mode
 *
 * @since 1.0
 * @return bool $ret True if test mode is enabled, false otherwise
 */
function pl8app_is_test_mode()
{
    $ret = pl8app_get_option('test_mode', false);
    if($ret == -1) {
        $ret = false;
    }
    return (bool)apply_filters('pl8app_is_test_mode', $ret);
}

/**
 * Is Debug Mode
 *
 * @since 1.0
 * @return bool $ret True if debug mode is enabled, false otherwise
 */
function pl8app_is_debug_mode()
{
    $ret = pl8app_get_option('debug_mode', false);
    if (defined('pl8app_DEBUG_MODE') && pl8app_DEBUG_MODE) {
        $ret = true;
    }
    return (bool)apply_filters('pl8app_is_debug_mode', $ret);
}

/**
 * Checks if Guest checkout is enabled
 *
 * @since 1.0
 * @return bool $ret True if guest checkout is enabled, false otherwise
 */
function pl8app_no_guest_checkout()
{
    $login_method = pl8app_get_option('login_method', 'login_guest');
    $ret = $login_method == 'login_only' ? true : false;
    return (bool)apply_filters('pl8app_no_guest_checkout', $ret);
}

/**
 * Redirect to checkout immediately after adding items to the cart?
 *
 * @since 1.0.0
 * @return bool $ret True is redirect is enabled, false otherwise
 */
function pl8app_straight_to_checkout()
{
    $ret = pl8app_get_option('redirect_on_add', false);
    return (bool)apply_filters('pl8app_straight_to_checkout', $ret);
}

/**
 * Verify credit card numbers live?
 *
 * @since  1.0.0
 * @return bool $ret True is verify credit cards is live
 */
function pl8app_is_cc_verify_enabled()
{
    $ret = true;

    /*
     * Enable if use a single gateway other than PayPal or Manual. We have to assume it accepts credit cards
     * Enable if using more than one gateway if they aren't both PayPal and manual, again assuming credit card usage
     */

    $gateways = pl8app_get_enabled_payment_gateways();

    if (count($gateways) == 1 && !isset($gateways['paypal']) && !isset($gateways['manual'])) {
        $ret = true;
    } else if (count($gateways) == 1) {
        $ret = false;
    } else if (count($gateways) == 2 && isset($gateways['paypal']) && isset($gateways['manual'])) {
        $ret = false;
    }

    return (bool)apply_filters('pl8app_verify_credit_cards', $ret);
}

/**
 * Check if the current page is a pl8app Page or not
 */
function is_pl8app_page()
{

    global $post;

    $pla_page = false;
    $menu_page = pl8app_get_option('menu_items_page', '');

    if (isset($post->ID) && $post->ID == $menu_page) {
        $pla_page = true;
    } else if (has_shortcode($post->post_content, 'menuitems')) {
        $pla_page = true;
    } else if (has_shortcode($post->post_content, 'menuitem_checkout')) {
        $pla_page = true;
    } else if (has_shortcode($post->post_content, 'pl8app_receipt')) {
        $pla_page = true;
    } else if (has_shortcode($post->post_content, 'order_history')) {
        $pla_page = true;
    }

    return apply_filters('is_a_pl8app_page', $pla_page);
}

/**
 * Check if current page is a Pl8app default page or not
 */

function is_pl8app_default_page(){

    global $post;

    $default_pages = array(
        'purchase_page',
        'success_page',
        'failure_page',
        'order_history_page',
        'menu_items_page',
        'allergy_page',
        'thank_you_page',
        'contact_us_page',
        'faq_page',
        'delivery_refund_page',
        'privacy_page',
        'reviews_page',
        'review_form_page'
    );

    $default_pages_ids = array();
    foreach($default_pages as $default_page){
        $default_page_id = pl8app_get_option($default_page, '');
        if(isset($default_page_id)){
            array_push($default_pages_ids, $default_page_id);
        }
    }
    $default_pages_ids = apply_filters('pl8app_default_pages_ids', $default_pages_ids);

    $current_page_id = $post->ID;

    if(in_array($current_page_id, $default_pages_ids)){
        return true;
    }
    else{
        return false;
    }
}

/**
 * Check if the current page is a pl8app Page or not
 */
function is_pl8app_form_page()
{

    global $post;

    $default_pages = array(
        'purchase_page',
        'success_page',
        'failure_page',
        'order_history_page',
        'menu_items_page',
        'allergy_page',
        'thank_you_page',
        'contact_us_page',
        'faq_page',
        'delivery_refund_page',
        'privacy_page',
        'reviews_page',
        'review_form_page'
    );

    $default_pages_ids = array();
    foreach($default_pages as $default_page){
        $default_page_id = pl8app_get_option($default_page, '');
        if(isset($default_page_id)){
            array_push($default_pages_ids, $default_page_id);
        }
    }
    $default_pages_ids = apply_filters('pl8app_default_pages_ids', $default_pages_ids);
    $current_page_id = $post->ID;

    $pla_page = false;
    if (has_shortcode($post->post_content, 'pl8app_contactform')) {
        $pla_page = true;
    } else if (has_shortcode($post->post_content, 'pl8app_allergyform')) {
        $pla_page = true;
    } else if (has_shortcode($post->post_content, 'pl8app_faq')) {
        $pla_page = true;
    } else if (has_shortcode($post->post_content, 'pl8app_delivery_refund')) {
        $pla_page = true;
    } else if (has_shortcode($post->post_content, 'privacy_policy_content')) {
        $pla_page = true;
    } else if (in_array($current_page_id, $default_pages_ids)){
        $pla_page = true;
    }

    return apply_filters('is_a_pl8app_page', $pla_page);
}

/**
 * Is Odd
 *
 * Checks whether an integer is odd.
 *
 * @since 1.0
 * @param int $int The integer to check
 * @return bool Is the integer odd?
 */
function pl8app_is_odd($int)
{
    return (bool)($int & 1);
}

/**
 * Get File Extension
 *
 * Returns the file extension of a filename.
 *
 * @since 1.0
 *
 * @param unknown $str File name
 *
 * @return mixed File extension
 */
function pl8app_get_file_extension($str)
{
    $parts = explode('.', $str);
    return end($parts);
}

/**
 * Checks if the string (filename) provided is an image URL
 *
 * @since 1.0
 * @param string $str Filename
 * @return bool Whether or not the filename is an image
 */
function pl8app_string_is_image_url($str)
{
    $ext = pl8app_get_file_extension($str);

    switch (strtolower($ext)) {
        case 'jpg';
            $return = true;
            break;
        case 'png';
            $return = true;
            break;
        case 'gif';
            $return = true;
            break;
        default:
            $return = false;
            break;
    }

    return (bool)apply_filters('pl8app_string_is_image', $return, $str);
}

/**
 * Get User IP
 *
 * Returns the IP address of the current visitor
 *
 * @since 1.0
 * @return string $ip User's IP address
 */
function pl8app_get_ip()
{

    $ip = '127.0.0.1';

    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        //check ip from share internet
        $ip = $_SERVER['HTTP_CLIENT_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        //to check ip is pass from proxy
        // can include more than 1 ip, first is the public one
        $ip = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
        $ip = trim($ip[0]);
    } elseif (!empty($_SERVER['REMOTE_ADDR'])) {
        $ip = $_SERVER['REMOTE_ADDR'];
    }

    // Fix potential CSV returned from $_SERVER variables
    $ip_array = explode(',', $ip);
    $ip_array = array_map('trim', $ip_array);

    return apply_filters('pl8app_get_ip', $ip_array[0]);
}


/**
 * Get user host
 *
 * Returns the webhost this site is using if possible
 *
 * @since  1.0.0
 * @return mixed string $host if detected, false otherwise
 */
function pl8app_get_host()
{
    $host = false;

    if (defined('WPE_APIKEY')) {
        $host = 'WP Engine';
    } elseif (defined('PAGELYBIN')) {
        $host = 'Pagely';
    } elseif (DB_HOST == 'localhost:/tmp/mysql5.sock') {
        $host = 'ICDSoft';
    } elseif (DB_HOST == 'mysqlv5') {
        $host = 'NetworkSolutions';
    } elseif (strpos(DB_HOST, 'ipagemysql.com') !== false) {
        $host = 'iPage';
    } elseif (strpos(DB_HOST, 'ipowermysql.com') !== false) {
        $host = 'IPower';
    } elseif (strpos(DB_HOST, '.gridserver.com') !== false) {
        $host = 'MediaTemple Grid';
    } elseif (strpos(DB_HOST, '.pair.com') !== false) {
        $host = 'pair Networks';
    } elseif (strpos(DB_HOST, '.stabletransit.com') !== false) {
        $host = 'Rackspace Cloud';
    } elseif (strpos(DB_HOST, '.sysfix.eu') !== false) {
        $host = 'SysFix.eu Power Hosting';
    } elseif (strpos($_SERVER['SERVER_NAME'], 'Flywheel') !== false) {
        $host = 'Flywheel';
    } else {
        // Adding a general fallback for data gathering
        $host = 'DBH: ' . DB_HOST . ', SRV: ' . $_SERVER['SERVER_NAME'];
    }

    return $host;
}


/**
 * Check site host
 *
 * @since  1.0.0
 * @param $host The host to check
 * @return bool true if host matches, false if not
 */
function pl8app_is_host($host = false)
{

    $return = false;

    if ($host) {
        $host = str_replace(' ', '', strtolower($host));

        switch ($host) {
            case 'wpengine':
                if (defined('WPE_APIKEY'))
                    $return = true;
                break;
            case 'pagely':
                if (defined('PAGELYBIN'))
                    $return = true;
                break;
            case 'icdsoft':
                if (DB_HOST == 'localhost:/tmp/mysql5.sock')
                    $return = true;
                break;
            case 'networksolutions':
                if (DB_HOST == 'mysqlv5')
                    $return = true;
                break;
            case 'ipage':
                if (strpos(DB_HOST, 'ipagemysql.com') !== false)
                    $return = true;
                break;
            case 'ipower':
                if (strpos(DB_HOST, 'ipowermysql.com') !== false)
                    $return = true;
                break;
            case 'mediatemplegrid':
                if (strpos(DB_HOST, '.gridserver.com') !== false)
                    $return = true;
                break;
            case 'pairnetworks':
                if (strpos(DB_HOST, '.pair.com') !== false)
                    $return = true;
                break;
            case 'rackspacecloud':
                if (strpos(DB_HOST, '.stabletransit.com') !== false)
                    $return = true;
                break;
            case 'sysfix.eu':
            case 'sysfix.eupowerhosting':
                if (strpos(DB_HOST, '.sysfix.eu') !== false)
                    $return = true;
                break;
            case 'flywheel':
                if (strpos($_SERVER['SERVER_NAME'], 'Flywheel') !== false)
                    $return = true;
                break;
            default:
                $return = false;
        }
    }

    return $return;
}


/**
 * Get Currencies
 *
 * @since 1.0
 * @return array $currencies A list of the available currencies
 */
function pl8app_get_currencies()
{
    $currencies = array(
        'USD' => __('US Dollars (&#36;)', 'pl8app'),
        'EUR' => __('Euros (&euro;)', 'pl8app'),
        'GBP' => __('Pound Sterling (&pound;)', 'pl8app'),
        'AUD' => __('Australian Dollars (&#36;)', 'pl8app'),
        'BRL' => __('Brazilian Real (R&#36;)', 'pl8app'),
        'CAD' => __('Canadian Dollars (&#36;)', 'pl8app'),
        'CZK' => __('Czech Koruna', 'pl8app'),
        'DKK' => __('Danish Krone', 'pl8app'),
        'HKD' => __('Hong Kong Dollar (&#36;)', 'pl8app'),
        'HUF' => __('Hungarian Forint', 'pl8app'),
        'ILS' => __('Israeli Shekel (&#8362;)', 'pl8app'),
        'JPY' => __('Japanese Yen (&yen;)', 'pl8app'),
        'MYR' => __('Malaysian Ringgits', 'pl8app'),
        'MXN' => __('Mexican Peso (&#36;)', 'pl8app'),
        'NZD' => __('New Zealand Dollar (&#36;)', 'pl8app'),
        'NOK' => __('Norwegian Krone', 'pl8app'),
        'PKR' => __('Pakistani Rupee', 'pl8app'),
        'PHP' => __('Philippine Pesos', 'pl8app'),
        'PLN' => __('Polish Zloty', 'pl8app'),
        'SGD' => __('Singapore Dollar (&#36;)', 'pl8app'),
        'SEK' => __('Swedish Krona', 'pl8app'),
        'CHF' => __('Swiss Franc', 'pl8app'),
        'TWD' => __('Taiwan New Dollars', 'pl8app'),
        'THB' => __('Thai Baht (&#3647;)', 'pl8app'),
        'INR' => __('Indian Rupee (&#8377;)', 'pl8app'),
        'TRY' => __('Turkish Lira (&#8378;)', 'pl8app'),
        'RIAL' => __('Iranian Rial (&#65020;)', 'pl8app'),
        'RUB' => __('Russian Rubles', 'pl8app'),
        'AOA' => __('Angolan Kwanza', 'pl8app'),
        'NGN' => __('Nigerian Naira (&#8358;)', 'pl8app'),
        'VND' => __('Vietnamese dong', 'pl8app'),
    );

    return apply_filters('pl8app_currencies', $currencies);
}

/**
 * Get the store's set currency
 *
 * @since 1.0
 * @return string The currency code
 */
function pl8app_get_currency()
{
    $currency = pl8app_get_option('currency', 'USD');
    return apply_filters('pl8app_currency', $currency);
}

/**
 * Given a currency determine the symbol to use. If no currency given, site default is used.
 * If no symbol is determine, the currency string is returned.
 *
 * @since 1.0
 * @param  string $currency The currency string
 * @return string           The symbol to use for the currency
 */
function pl8app_currency_symbol($currency = '')
{
    if (empty($currency)) {
        $currency = pl8app_get_currency();
    }

    switch ($currency) :
        case "GBP" :
            $symbol = '&pound;';
            break;
        case "BRL" :
            $symbol = 'R&#36;';
            break;
        case "EUR" :
            $symbol = '&euro;';
            break;
        case "INR" :
            $symbol = '&#8377;';
            break;
        case "USD" :
        case "AUD" :
        case "NZD" :
        case "CAD" :
        case "HKD" :
        case "MXN" :
        case "SGD" :
            $symbol = '&#36;';
            break;
        case "JPY" :
            $symbol = '&yen;';
            break;
        case "AOA" :
            $symbol = 'Kz';
            break;
        case "NGN" :
            $symbol = '&#8358;';
            break;
        default :
            $symbol = $currency;
            break;
    endswitch;

    return apply_filters('pl8app_currency_symbol', $symbol, $currency);
}

/**
 * Get the name of a currency
 *
 * @since  1.0.0
 * @param  string $code The currency code
 * @return string The currency's name
 */
function pl8app_get_currency_name($code = 'USD')
{
    $currencies = pl8app_get_currencies();
    $name = isset($currencies[$code]) ? $currencies[$code] : $code;
    return apply_filters('pl8app_currency_name', $name);
}

/**
 * Month Num To Name
 *
 * Takes a month number and returns the name three letter name of it.
 *
 * @since 1.0
 *
 * @param integer $n
 * @return string Short month name
 */
function pl8app_month_num_to_name($n)
{
    $timestamp = mktime(0, 0, 0, $n, 1, 2005);

    return date_i18n("M", $timestamp);
}

/**
 * Get PHP Arg Separator Output
 *
 * @since 1.0
 * @return string Arg separator output
 */
function pl8app_get_php_arg_separator_output()
{
    return ini_get('arg_separator.output');
}

/**
 * Get the current page URL
 *
 * @since 1.0
 * @param  bool $nocache If we should bust cache on the returned URL
 * @return string $page_url Current page URL
 */
function pl8app_get_current_page_url($nocache = false)
{

    global $wp;

    if (get_option('permalink_structure')) {

        $base = trailingslashit(home_url($wp->request));

    } else {

        $base = add_query_arg($wp->query_string, '', trailingslashit(home_url($wp->request)));
        $base = remove_query_arg(array('post_type', 'name'), $base);

    }

    $scheme = is_ssl() ? 'https' : 'http';
    $uri = set_url_scheme($base, $scheme);

    if (is_front_page()) {
        $uri = home_url('/');
    } elseif (pl8app_is_checkout()) {
        $uri = pl8app_get_checkout_uri();
    }

    $uri = apply_filters('pl8app_get_current_page_url', $uri);

    if ($nocache) {
        $uri = pl8app_add_cache_busting($uri);
    }

    return $uri;
}

/**
 * Adds the 'nocache' parameter to the provided URL
 *
 * @since  1.0.0
 * @param  string $url The URL being requested
 * @return string      The URL with cache busting added or not
 */
function pl8app_add_cache_busting($url = '')
{

    $no_cache_checkout = pl8app_get_option('no_cache_checkout', false);

    if (pl8app_is_caching_plugin_active() || (pl8app_is_checkout() && $no_cache_checkout)) {
        $url = add_query_arg('nocache', 'true', $url);
    }

    return $url;
}

/**
 * Marks a function as deprecated and informs when it has been used.
 *
 * There is a hook pl8app_deprecated_function_run that will be called that can be used
 * to get the backtrace up to what file and function called the deprecated
 * function.
 *
 * The current behavior is to trigger a user error if WP_DEBUG is true.
 *
 * This function is to be used in every function that is deprecated.
 *
 * @uses do_action() Calls 'pl8app_deprecated_function_run' and passes the function name, what to use instead,
 *   and the version the function was deprecated in.
 * @uses apply_filters() Calls 'pl8app_deprecated_function_trigger_error' and expects boolean value of true to do
 *   trigger or false to not trigger error.
 *
 * @param string $function The function that was called
 * @param string $version The version of pl8app that deprecated the function
 * @param string $replacement Optional. The function that should have been called
 * @param array $backtrace Optional. Contains stack backtrace of deprecated function
 */
function _pl8app_deprecated_function($function, $version, $replacement = null, $backtrace = null)
{
    do_action('pl8app_deprecated_function_run', $function, $replacement, $version);

    $show_errors = current_user_can('manage_options');

    // Allow plugin to filter the output error trigger
    if (WP_DEBUG && apply_filters('pl8app_deprecated_function_trigger_error', $show_errors)) {
        if (!is_null($replacement)) {
            trigger_error(sprintf(__('%1$s is <strong>deprecated</strong> since pl8app version %2$s! Use %3$s instead.', 'pl8app'), $function, $version, $replacement));
            trigger_error(print_r($backtrace, 1)); // Limited to previous 1028 characters, but since we only need to move back 1 in stack that should be fine.
            // Alternatively we could dump this to a file.
        } else {
            trigger_error(sprintf(__('%1$s is <strong>deprecated</strong> since pl8app version %2$s with no alternative available.', 'pl8app'), $function, $version));
            trigger_error(print_r($backtrace, 1));// Limited to previous 1028 characters, but since we only need to move back 1 in stack that should be fine.
            // Alternatively we could dump this to a file.
        }
    }
}

/**
 * Marks an argument in a function deprecated and informs when it's been used
 *
 * There is a hook pl8app_deprecated_argument_run that will be called that can be used
 * to get the backtrace up to what file and function called the deprecated
 * function.
 *
 * The current behavior is to trigger a user error if WP_DEBUG is true.
 *
 * This function is to be used in every function that has an argument being deprecated.
 *
 * @uses do_action() Calls 'pl8app_deprecated_argument_run' and passes the argument, function name, what to use instead,
 *   and the version the function was deprecated in.
 * @uses apply_filters() Calls 'pl8app_deprecated_argument_trigger_error' and expects boolean value of true to do
 *   trigger or false to not trigger error.
 *
 * @param string $argument The arguemnt that is being deprecated
 * @param string $function The function that was called
 * @param string $version The version of WordPress that deprecated the function
 * @param string $replacement Optional. The function that should have been called
 * @param array $backtrace Optional. Contains stack backtrace of deprecated function
 */
function _pl8app_deprected_argument($argument, $function, $version, $replacement = null, $backtrace = null)
{
    do_action('pl8app_deprecated_argument_run', $argument, $function, $replacement, $version);

    $show_errors = current_user_can('manage_options');

    // Allow plugin to filter the output error trigger
    if (WP_DEBUG && apply_filters('pl8app_deprecated_argument_trigger_error', $show_errors)) {
        if (!is_null($replacement)) {
            trigger_error(sprintf(__('The %1$s argument of %2$s is <strong>deprecated</strong> since pl8app version %3$s! Please use %4$s instead.', 'pl8app'), $argument, $function, $version, $replacement));
            trigger_error(print_r($backtrace, 1)); // Limited to previous 1028 characters, but since we only need to move back 1 in stack that should be fine.
            // Alternatively we could dump this to a file.
        } else {
            trigger_error(sprintf(__('The %1$s argument of %2$s is <strong>deprecated</strong> since pl8app version %3$s with no alternative available.', 'pl8app'), $argument, $function, $version));
            trigger_error(print_r($backtrace, 1));// Limited to previous 1028 characters, but since we only need to move back 1 in stack that should be fine.
            // Alternatively we could dump this to a file.
        }
    }
}

/**
 * Checks whether function is disabled.
 *
 * @since 1.0.5
 *
 * @param string $function Name of the function.
 * @return bool Whether or not function is disabled.
 */
function pl8app_is_func_disabled($function)
{
    $disabled = explode(',', ini_get('disable_functions'));

    return in_array($function, $disabled);
}

/**
 * pl8app Let To Num
 *
 * Does Size Conversions
 *
 * @since  1.0.0
 * @usedby pl8app_settings()
 * @author Chris Christoff
 *
 * @param unknown $v
 * @return int
 */
function pl8app_let_to_num($v)
{
    $l = substr($v, -1);
    $ret = substr($v, 0, -1);

    switch (strtoupper($l)) {
        case 'P': // fall-through
        case 'T': // fall-through
        case 'G': // fall-through
        case 'M': // fall-through
        case 'K': // fall-through
            $ret *= 1024;
            break;
        default:
            break;
    }

    return (int)$ret;
}

/**
 * Retrieve the URL of the symlink directory
 *
 * @since 1.0
 * @return string $url URL of the symlink directory
 */
function pl8app_get_symlink_url()
{
    $wp_upload_dir = wp_upload_dir();
    wp_mkdir_p($wp_upload_dir['basedir'] . '/pl8app/symlinks');
    $url = $wp_upload_dir['baseurl'] . '/pl8app/symlinks';

    return apply_filters('pl8app_get_symlink_url', $url);
}

/**
 * Retrieve the absolute path to the symlink directory
 *
 * @since 1.0
 * @return string $path Absolute path to the symlink directory
 */
function pl8app_get_symlink_dir()
{
    $wp_upload_dir = wp_upload_dir();
    wp_mkdir_p($wp_upload_dir['basedir'] . '/pl8app/symlinks');
    $path = $wp_upload_dir['basedir'] . '/pl8app/symlinks';

    return apply_filters('pl8app_get_symlink_dir', $path);
}

/**
 * Retrieve the absolute path to the file upload directory without the trailing slash
 *
 * @since 1.0
 * @return string $path Absolute path to the pl8app upload directory
 */
function pl8app_get_upload_dir()
{
    $wp_upload_dir = wp_upload_dir();
    wp_mkdir_p($wp_upload_dir['basedir'] . '/pl8app');
    $path = $wp_upload_dir['basedir'] . '/pl8app';

    return apply_filters('pl8app_get_upload_dir', $path);
}

/**
 * Delete symbolic links after they have been used
 *
 * This function is only intended to be used by WordPress cron.
 *
 * @since 1.0
 * @return void
 */
function pl8app_cleanup_file_symlinks()
{

    // Bail if not in WordPress cron
    if (!pl8app_doing_cron()) {
        return;
    }

    $path = pl8app_get_symlink_dir();
    $dir = opendir($path);

    while (($file = readdir($dir)) !== false) {
        if ($file == '.' || $file == '..')
            continue;

        $transient = get_transient(md5($file));
        if ($transient === false)
            @unlink($path . '/' . $file);
    }
}

add_action('pl8app_cleanup_file_symlinks', 'pl8app_cleanup_file_symlinks');

/**
 * Checks if SKUs are enabled
 *
 * @since  1.0.0
 * @author Daniel J Griffiths
 * @return bool $ret True if SKUs are enabled, false otherwise
 */
function pl8app_use_skus()
{
    $ret = pl8app_get_option('enable_skus', false);
    return (bool)apply_filters('pl8app_use_skus', $ret);
}

/**
 * Retrieve timezone
 *
 * @since  1.0.0
 * @return string $timezone The timezone ID
 */
function pl8app_get_timezone_id()
{

    // if site timezone string exists, return it
    if ($timezone = get_option('timezone_string'))
        return $timezone;

    // get UTC offset, if it isn't set return UTC
    if (!($utc_offset = 3600 * get_option('gmt_offset', 0)))
        return 'UTC';

    // attempt to guess the timezone string from the UTC offset
    $timezone = timezone_name_from_abbr('', $utc_offset);

    // last try, guess timezone string manually
    if ($timezone === false) {

        $is_dst = date('I');

        foreach (timezone_abbreviations_list() as $abbr) {
            foreach ($abbr as $city) {
                if ($city['dst'] == $is_dst && $city['offset'] == $utc_offset)
                    return $city['timezone_id'];
            }
        }
    }

    // fallback
    return 'UTC';
}

/**
 * Given an object or array of objects, convert them to arrays
 *
 * @since 1.0
 * @internal Updated in 2.6
 * @param    object|array $object An object or an array of objects
 * @return   array                An array or array of arrays, converted from the provided object(s)
 */
function pl8app_object_to_array($object = array())
{

    if (empty($object) || (!is_object($object) && !is_array($object))) {
        return $object;
    }

    if (is_array($object)) {
        $return = array();
        foreach ($object as $item) {
            if ($object instanceof pl8app_Payment) {
                $return[] = $object->array_convert();
            } else {
                $return[] = pl8app_object_to_array($item);
            }

        }
    } else {
        if ($object instanceof pl8app_Payment) {
            $return = $object->array_convert();
        } else {
            $return = get_object_vars($object);

            // Now look at the items that came back and convert any nested objects to arrays
            foreach ($return as $key => $value) {
                $value = (is_array($value) || is_object($value)) ? pl8app_object_to_array($value) : $value;
                $return[$key] = $value;
            }
        }
    }

    return $return;
}

/**
 * Set Upload Directory
 *
 * Sets the upload dir to pl8app. This function is called from
 * pl8app_change_menuitems_upload_dir()
 *
 * @since 1.0
 * @return array Upload directory information
 */
function pl8app_set_upload_dir($upload)
{

    // Override the year / month being based on the post publication date, if year/month organization is enabled
    if (get_option('uploads_use_yearmonth_folders')) {
        // Generate the yearly and monthly dirs
        $time = current_time('mysql');
        $y = substr($time, 0, 4);
        $m = substr($time, 5, 2);
        $upload['subdir'] = "/$y/$m";
    }

    $upload['subdir'] = '/pl8app' . $upload['subdir'];
    $upload['path'] = $upload['basedir'] . $upload['subdir'];
    $upload['url'] = $upload['baseurl'] . $upload['subdir'];
    return $upload;
}

/**
 * Check if the upgrade routine has been run for a specific action
 *
 * @since  1.0.0
 * @param  string $upgrade_action The upgrade action to check completion for
 * @return bool                   If the action has been added to the copmleted actions array
 */
function pl8app_has_upgrade_completed($upgrade_action = '')
{

    if (empty($upgrade_action)) {
        return false;
    }

    $completed_upgrades = pl8app_get_completed_upgrades();

    return in_array($upgrade_action, $completed_upgrades);

}

/**
 * Get's the array of completed upgrade actions
 *
 * @since  1.0.0
 * @return array The array of completed upgrades
 */
function pl8app_get_completed_upgrades()
{

    $completed_upgrades = get_option('pl8app_completed_upgrades');

    if (false === $completed_upgrades) {
        $completed_upgrades = array();
    }

    return $completed_upgrades;

}


if (!function_exists('cal_days_in_month')) {
    // Fallback in case the calendar extension is not loaded in PHP
    // Only supports Gregorian calendar
    function cal_days_in_month($calendar, $month, $year)
    {
        return date('t', mktime(0, 0, 0, $month, 1, $year));
    }
}


if (!function_exists('hash_equals')) :
    /**
     * Compare two strings in constant time.
     *
     * This function was added in PHP 5.6.
     * It can leak the length of a string.
     *
     * @since 1.0
     *
     * @param string $a Expected string.
     * @param string $b Actual string.
     * @return bool Whether strings are equal.
     */
    function hash_equals($a, $b)
    {
        $a_length = strlen($a);
        if ($a_length !== strlen($b)) {
            return false;
        }
        $result = 0;

        // Do not attempt to "optimize" this.
        for ($i = 0; $i < $a_length; $i++) {
            $result |= ord($a[$i]) ^ ord($b[$i]);
        }

        return $result === 0;
    }
endif;

if (!function_exists('getallheaders')) :

    /**
     * Retrieve all headers
     *
     * Ensure getallheaders function exists in the case we're using nginx
     *
     * @since 1.0
     * @return array
     */
    function getallheaders()
    {
        $headers = array();
        foreach ($_SERVER as $name => $value) {
            if (substr($name, 0, 5) == 'HTTP_') {
                $headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value;
            }
        }
        return $headers;
    }

endif;

/**
 * Determines the receipt visibility status
 *
 * @return bool Whether the receipt is visible or not.
 */
function pl8app_can_view_receipt($payment_key = '')
{

    $return = false;

    if (empty($payment_key)) {
        return $return;
    }

    global $pl8app_receipt_args;

    $pl8app_receipt_args['id'] = pl8app_get_purchase_id_by_key($payment_key);

    $user_id = (int)pl8app_get_payment_user_id($pl8app_receipt_args['id']);

    $payment_meta = pl8app_get_payment_meta($pl8app_receipt_args['id']);

    if (is_user_logged_in()) {
        if ($user_id === (int)get_current_user_id()) {
            $return = true;
        } elseif (wp_get_current_user()->user_email === pl8app_get_payment_user_email($pl8app_receipt_args['id'])) {
            $return = true;
        } elseif (current_user_can('view_shop_sensitive_data')) {
            $return = true;
        }
    }

    $session = pl8app_get_purchase_session();
    if (!empty($session) && !is_user_logged_in()) {
        if ($session['purchase_key'] === $payment_meta['key']) {
            $return = true;
        }
    }

    return (bool)apply_filters('pl8app_can_view_receipt', $return, $payment_key);
}

/**
 * Given a Payment ID, generate a link to IP address provider (ipinfo.io)
 *
 * @since 1.0
 * @param  int $payment_id The Payment ID
 * @return string    A link to the IP details provider
 */
function pl8app_payment_get_ip_address_url($payment_id)
{

    $payment = new pl8app_Payment($payment_id);

    $base_url = 'https://ipinfo.io/';
    $provider_url = '<a href="' . esc_url($base_url) . esc_attr($payment->ip) . '" target="_blank">' . esc_attr($payment->ip) . '</a>';

    return apply_filters('pl8app_payment_get_ip_address_url', $provider_url, $payment->ip, $payment_id);

}

/**
 * Abstraction for WordPress cron checking, to avoid code duplication.
 *
 * In future versions of pl8app, this function will be changed to only refer to
 * pl8app specific cron related jobs. You probably won't want to use it until then.
 *
 * @since 1.0
 *
 * @return boolean
 */
function pl8app_doing_cron()
{

    // Bail if not doing WordPress cron (>4.8.0)
    if (function_exists('wp_doing_cron') && wp_doing_cron()) {
        return true;

        // Bail if not doing WordPress cron (<4.8.0)
    } elseif (defined('DOING_CRON') && (true === DOING_CRON)) {
        return true;
    }

    // Default to false
    return false;
}

/**
 * Display a pl8app help tip.
 *
 * @since  3.0
 *
 * @param  string $tip Help tip text.
 * @param  bool $allow_html Allow sanitized HTML if true or escape.
 * @return string
 */
function pla_help_tip($tip, $allow_html = false)
{
    if ($allow_html) {
        $tip = pl8app_sanitize_tooltip($tip);
    } else {
        $tip = esc_attr($tip);
    }

    return '<span class="pl8app-help-tip" data-tip="' . $tip . '"></span>';
}

/**
 * Is pickup/delivery time enabled
 *
 * @since 1.0
 * @return bool $ret True if test mode is enabled, false otherwise
 */
function pl8app_is_service_enabled($service)
{
    return (bool)apply_filters('pl8app_is_service_enabled', true, $service);
}

function pl8app_menuitem_available($menuitem_id)
{
    return (bool)apply_filters('pl8app_is_orderable', true, $menuitem_id);
}

/** Get Singular Label
 * @since 2.0.7
 *
 * @param bool $lowercase
 * @return string $defaults['singular'] Singular label
 */
function pla_get_label_singular($lowercase = false)
{
    $defaults = pla_get_default_labels();
    return ($lowercase) ? strtolower($defaults['singular']) : $defaults['singular'];
}

/**
 * Get Plural Label
 *
 * @since 1.0
 * @return string $defaults['plural'] Plural label
 */
function pla_get_label_plural($lowercase = false)
{
    $defaults = pla_get_default_labels();
    return ($lowercase) ? strtolower($defaults['plural']) : $defaults['plural'];
}

/**
 * Get Default Labels
 *
 * @since 1.0
 * @return array $defaults Default labels
 */
function pla_get_default_labels()
{
    $defaults = array(
        'singular' => __('Menu Item', 'pl8app'),
        'plural' => __('Menu Items', 'pl8app')
    );
    return apply_filters('pla_default_menuitems_name', $defaults);
}

/**
 * Get Sitemap Default Pages Links
 *
 * @since 1.0
 * @return array $defaults Default labels
 */
function pla_get_default_pages()
{
    $defaults = array(
        'menu_items_page' => __('Menu Items', 'pl8app'),
        'order_history_page' => __('Orders', 'pl8app'),
        'purchase_page' => __('Checkout', 'pl8app'),
        'contact_us_page' => __('Contact Us', 'pl8app'),
        'reviews_page' => __('Reviews', 'pl8app'),
        'review_form_page' => __('Review Form', 'pl8app'),
        'thank_you_page' => __('Thank you for your payment', 'pl8app'),
        'faq_page' => __('FAQ', 'pl8app'),
        'delivery_refund_page' => __('Delivery, Ruturns AND Refunds', 'pl8app'),
        'privacy_page' => __('Privacy Policy', 'pl8app'),
        'allergy_page' => __('Do you have a Food Allergy?', 'pl8app'),
    );
    return apply_filters('pla_default_pages', $defaults);
}

/**
 * Check menu item timing availability with selected service time
 * @param $menuitem_id
 * @return bool
 */
function check_availability_menu_item_timing($menuitem_id, $service_time = null, $service_date = null){

    $menuitem_timings = get_post_meta($menuitem_id, 'pl8app_menuitem_timing', true);
    $menuitem_timings_enabled = true;

    if($service_time == null) $service_time = isset($_COOKIE['service_time'])? $_COOKIE['service_time']: '';
    if($service_date == null) $service_date = isset($_COOKIE['delivery_date'])? $_COOKIE['delivery_date']: '';

    if(!empty($service_time)){
        $day_num = date('l', strtotime(current_time('Y-m-d')));
        $current_time = strtotime($service_time);
        if(!empty($service_date)) {
            $day_num = date('l', strtotime($service_date));
        }
        if (!empty($menuitem_timings['open_day'][$day_num]) && !empty($menuitem_timings['open_time'][$day_num]) && !empty($menuitem_timings['close_time'][$day_num])) {
            $menuitem_timings_enabled = false;
            $menuitem_opentime_list = $menuitem_timings['open_time'][$day_num];
            $menuitem_closetime_list = $menuitem_timings['close_time'][$day_num];
            if (is_array($menuitem_opentime_list) && count($menuitem_opentime_list) > 0 && !empty($menuitem_opentime_list[0]) && !empty($menuitem_closetime_list[0])) {
                foreach ($menuitem_opentime_list as $index => $opentime) {
                    $closetime = $menuitem_closetime_list[$index];
                    if (strtotime($opentime) <= $current_time && $current_time <= strtotime($closetime)) {
                        $menuitem_timings_enabled = true;
                        break;
                    }
                }
            }
            else {
                $menuitem_timings_enabled = true;
            }
        }
        else{
            $menuitem_timings_enabled = false;
        }

    }

    return $menuitem_timings_enabled;
}


/**
 * pick up the chart style of Order overview widget in admin dashboard
 * @param $percentage
 * @return string
 */
function pl8app_get_chart_style($percentage){

    $response = 'width:'.$percentage.'%;';

    if($percentage > 0 && $percentage <= 25) {
        $response .= 'background-color: green;';
    }else if($percentage > 25 && $percentage <= 50) {
        $response .= 'background-color: blue;';
    }else if($percentage > 50 && $percentage <= 75) {
        $response .= 'background-color: yellow; color: black;';
    }else if($percentage > 75 && $percentage <= 100) {
        $response .= 'background-color: red;';
    }else{
        $response .= 'color: black;';
    }
    return $response;
}

/**
 * Always time-format is 'H:i' in pl8app plugin.
 * @param $time_format
 * @return string
 */
function pla_store_time_format($time_format){
    return 'H:i';
}

add_filter('pla_store_time_format', 'pla_store_time_format', 10, 1);

/**
 * Render the widget of Sitemap, Opening_time and Store Location
 */
function pl8app_widget_before_footer(){
    do_shortcode('[pl8app_before_footer sitemap="1" opentime="1" storeinfo="1"]');
}

add_action('pl8app_widget_before_footer','pl8app_widget_before_footer');


////////////////////////////////////////////////////////////////////
// Social links
////////////////////////////////////////////////////////////////////
if ( !function_exists( 'pl8app_bar_social_links' ) ) :

    /**
     * This function is for social links display on header
     *
     * Get links through Theme Options
     */
    function pl8app_bar_social_links() {
        $twp_social_links	 = array(
            'twp_social_facebook'	 => 'facebook',
            'twp_social_twitter'	 => 'twitter',
            'twp_social_google'		 => 'google-plus',
            'twp_social_instagram'	 => 'instagram',
            'twp_social_pin'		 => 'pinterest',
            'twp_social_youtube'	 => 'youtube',
            'twp_social_reddit'		 => 'reddit',
            'twp_social_linkedin'	 => 'linkedin',
            'twp_social_skype'		 => 'skype',
            'twp_social_vimeo'		 => 'vimeo',
            'twp_social_flickr'		 => 'flickr',
            'twp_social_dribble'	 => 'dribbble',
            'twp_social_envelope-o'	 => 'envelope-o',
            'twp_social_rss'		 => 'rss',
            'twp_social_vk'			 => 'vk',
        );
        ?>
        <div class="social-links">
            <ul>
                <?php

                $i					 = 0;
                $twp_links_output	 = '';

                if (pl8app_get_option('pl8app_socials_text', '') != '') {
                    $twp_links_output .= '<li>'.pl8app_get_option('pl8app_socials_text', '').'</li>';
                }

                foreach ( $twp_social_links as $key => $value ) {
                    $link = pl8app_get_option( $key, '' );
                    if ( !empty( $link ) ) {
                        if ( $key == 'twp_social_envelope-o' ) {
                            $twp_links_output .= '<li><a href="' . esc_url( $link ) . '" ><i class="fa fa-' . strtolower( $value ) . '"></i></a></li>';
                        } else {
                            $twp_links_output .= '<li><a href="' . esc_url( $link ) . '" target="_blank"><i class="fa fa-' . strtolower( $value ) . '"></i></a></li>';
                        }
                    }
                    $i++;
                }

                echo $twp_links_output;
                ?>
            </ul>
        </div><!-- .social-links -->
        <?php
    }

endif;


/**
 *  Get order count based on order status
 *  @since 2.8.4
 *
 *  @param string $status
 *  @return int $order_count
 */
function pl8app_get_order_count( $status = 'pending' ) {
    global $wpdb;

    $query = $wpdb->prepare( "SELECT count(*) as count
  FROM {$wpdb->postmeta}
  WHERE `meta_key` = '_order_status'
  AND `meta_value` = '%s'
  GROUP BY meta_value", $status );

    $order_count = $wpdb->get_var( $query );

    return apply_filters( 'pl8app_order_count', $order_count, $status );
}