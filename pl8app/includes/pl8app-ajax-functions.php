<?php
/**
 * AJAX Functions
 *
 * Process the front-end AJAX actions.
 *
 * @package     pl8app
 * @subpackage  Functions/AJAX
 * @copyright
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */

// Exit if accessed directly
if (!defined('ABSPATH')) exit;

/**
 * Checks whether AJAX is enabled.
 *
 * This will be deprecated soon in favor of pl8app_is_ajax_disabled()
 *
 * @since 1.0
 * @return bool True when pl8app AJAX is enabled (for the cart), false otherwise.
 */
function pl8app_is_ajax_enabled()
{
    $retval = !pl8app_is_ajax_disabled();
    return apply_filters('pl8app_is_ajax_enabled', $retval);
}

/**
 * Checks whether AJAX is disabled.
 *
 * @since  1.0.0
 * @since 1.0 Setting to disable AJAX was removed
 * @return bool True when pl8app AJAX is disabled (for the cart), false otherwise.
 */
function pl8app_is_ajax_disabled()
{
    return apply_filters('pl8app_is_ajax_disabled', false);
}

/**
 * Check if AJAX works as expected
 *
 * @since  1.0.0
 * @return bool True if AJAX works, false otherwise
 */
function pl8app_test_ajax_works()
{

    // Check if the Airplane Mode plugin is installed
    if (class_exists('Airplane_Mode_Core')) {

        $airplane = Airplane_Mode_Core::getInstance();

        if (method_exists($airplane, 'enabled')) {

            if ($airplane->enabled()) {
                return true;
            }

        } else {

            if ($airplane->check_status() == 'on') {
                return true;
            }
        }
    }

    add_filter('block_local_requests', '__return_false');

    if (get_transient('_pl8app_ajax_works')) {
        return true;
    }

    $params = array(
        'sslverify' => false,
        'timeout' => 30,
        'body' => array(
            'action' => 'pl8app_test_ajax'
        )
    );

    $ajax = wp_remote_post(pl8app_get_ajax_url(), $params);
    $works = true;

    if (is_wp_error($ajax)) {

        $works = false;

    } else {

        if (empty($ajax['response'])) {
            $works = false;
        }

        if (empty($ajax['response']['code']) || 200 !== (int)$ajax['response']['code']) {
            $works = false;
        }

        if (empty($ajax['response']['message']) || 'OK' !== $ajax['response']['message']) {
            $works = false;
        }

        if (!isset($ajax['body']) || 0 !== (int)$ajax['body']) {
            $works = false;
        }

    }

    if ($works) {
        set_transient('_pl8app_ajax_works', '1', DAY_IN_SECONDS);
    }

    return $works;
}

/**
 * Get AJAX URL
 *
 * @since 1.0
 * @return string URL to the AJAX file to call during AJAX requests.
 */
function pl8app_get_ajax_url()
{
    $scheme = defined('FORCE_SSL_ADMIN') && FORCE_SSL_ADMIN ? 'https' : 'admin';

    $current_url = pl8app_get_current_page_url();
    $ajax_url = admin_url('admin-ajax.php', $scheme);

    if (preg_match('/^https/', $current_url) && !preg_match('/^https/', $ajax_url)) {
        $ajax_url = preg_replace('/^http/', 'https', $ajax_url);
    }

    return apply_filters('pl8app_ajax_url', $ajax_url);
}

/**
 * Get menu items list .
 *
 * @since 1.0
 * @return void
 */
function get_menuitem_lists($menuitem_id, $cart_key = '', $bundle_sub = false, $index = null)
{

    $addons = get_post_meta($menuitem_id, '_addon_items', true);
    $chosen_addons = array();
    $price_id = 0;
    $addon_ids = $child_ids = array();

    if ($addons) {
        foreach ($addons as $addon) {
            if (!empty($addon['category'])) {
                array_push($addon_ids, $addon['category']);
            }
            if (isset($addon['items']) && is_array($addon['items'])) {
                $child_ids = array_merge($child_ids, $addon['items']);
            }
        }
    }

    // if( $cart_key !== '' ) {         // Showed Ajax Error as per Nirmal
    // if( !empty( $cart_key ) ) {      // Did work but had issue somewhere else
    // if( !is_null( $cart_key ) ) {    // Did work but had issue somewhere else
    // if( is_int( $cart_key ) ) {      // Did work but had issue somewhere else

    if ($cart_key !== '') {

        $cart_contents = pl8app_get_cart_contents();
        $cart_contents = $cart_contents[$cart_key];
        $price_id = isset($cart_contents['price_id']) ? $cart_contents['price_id'] : 0;

        if (!empty($cart_contents['addon_items'])) {
            foreach ($cart_contents['addon_items'] as $key => $val) {
                array_push($chosen_addons, $val['addon_id']);
            }
        }
    }

    ob_start();

    if (!empty($menuitem_id) && pl8app_has_variable_prices($menuitem_id)) {

        $prices = pl8app_get_variable_prices($menuitem_id);

        if (is_array($prices) && !empty($prices)) {

            $variable_price_label = get_post_meta($menuitem_id, 'pl8app_variable_price_label', true);
            $variable_price_label = !empty($variable_price_label) ? $variable_price_label : esc_html('Price Options', 'pl8app');
            $variable_price_heading = apply_filters('pla_variable_price_heading', $variable_price_label); ?>

            <h6><?php echo $variable_price_heading; ?></h6>

            <div class="pl8app-variable-price-wrapper">

                <?php
                foreach ($prices as $k => $price) {

                    $price_option = $price['name'];
                    $is_first = ($k == $price_id) ? 'checked' : '';
                    $price_option_slug = sanitize_title($price['name']);
                    $price_option_amount = pl8app_currency_filter(pl8app_format_amount($price['amount'])); ?>

                    <div class="menu-item-list active">
                        <label for="<?php echo $price_option_slug; ?>" class="radio-container">
                            <input type="radio" name="price_options" id="<?php echo $price_option_slug; ?>"
                                   data-value="<?php echo $price_option_slug . '|1|' . $price['amount'] . '|radio'; ?>"
                                   value="<?php echo $k; ?>" <?php echo $is_first; ?>
                                   class="pl8app-variable-price-option"/>
                            <span><?php echo $price_option; ?></span>
                            <span class="control__indicator"></span>
                        </label>
                        <span class="cat_price"><?php echo $price_option_amount; ?></span>
                    </div>
                <?php } ?>
            </div>
        <?php }
    }

    if (isset($addon_ids) && is_array($addon_ids) && !empty($addon_ids)) { ?>

        <div class="pl8app-addons-data-wrapper">

            <?php
            foreach ($addon_ids as $parent) {

                $addon_items = get_term_by('id', $parent, 'addon_category');
                $addon_name = $addon_items->name;
                $addon_slug = $addon_items->slug;

                $is_required = isset($addons[$parent]['is_required']) ? $addons[$parent]['is_required'] : 'no';
                $max_addons = isset($addons[$parent]['max_addons']) ? $addons[$parent]['max_addons'] : 0;

                ?>

                <div class="addons-wrapper addons-wrapper-<?php echo $parent; ?>" data-id="<?php echo $parent; ?>">

                    <h6 class="pl8app-addon-category">
                        <?php echo $addon_name; ?>
                        <?php if ($is_required == 'yes') : ?>
                            <span class="pl8app-addon-required">
								<?php esc_html_e('Required', 'pl8app'); ?>
							</span>
                        <?php endif; ?>
                        <?php if (!empty($max_addons)) : ?>
                            <span class="pl8app-max-addon">
								<?php echo sprintf(__('Maximum %s allowed', 'pl8app'), $max_addons); ?>
							</span>
                        <?php endif; ?>
                    </h6>
                    <input type="hidden" name="is_required" class="addon_is_required"
                           value="<?php echo $is_required; ?>"/>
                    <input type="hidden" name="max_limit" class="addon_max_limit" value="<?php echo $max_addons; ?>"/>
                    <?php

                    $addon_category_args = array('taxonomy' => 'addon_category', 'parent' => $addon_items->term_id, 'include' => $child_ids);
                    $child_addons = get_terms(apply_filters('pla_addon_category', $addon_category_args));

                    if ($child_addons) {
                        $child_addons = wp_list_pluck($child_addons, 'term_id');
                    }

                    if (is_array($child_addons) && !empty($child_addons)) {

                        foreach ($child_addons as $child_addon) {

                            $child_data = get_term_by('id', $child_addon, 'addon_category');
                            $child_addon_slug = $child_data->slug;
                            $child_addon_name = $child_data->name;
                            $child_addon_id = $child_data->term_id;
                            $term_meta = get_option("taxonomy_term_$parent");
                            $use_addon_like = isset($term_meta['use_it_like']) ? $term_meta['use_it_like'] : 'checkbox';
                            $child_addon_type_name = ($use_addon_like == 'radio') ? $addon_name : $child_addon_name;

                            if (is_array($chosen_addons)) :
                                if (!empty($prices) && is_array($prices)) :
                                    foreach ($prices as $p_id => $price) :

                                        $get_addon_price = pl8app_dynamic_addon_price($menuitem_id, $child_data->term_id, $child_data->parent, $p_id); ?>

                                        <div class="menu-item-list list_<?php echo $p_id; ?> <?php if ($p_id == $price_id) {
                                            echo 'active';
                                        } ?>">
                                            <label for="<?php echo $child_addon_slug . ($bundle_sub ? '[' . $index . '][' . $menuitem_id . ']' : ''); ?>_<?php echo $p_id; ?>"
                                                   class="<?php echo $use_addon_like; ?>-container">
                                                <?php $is_selected = in_array($child_addon_id, $chosen_addons) ? 'checked' : ''; ?>
                                                <input data-type="<?php echo $use_addon_like; ?>"
                                                       type="<?php echo $use_addon_like; ?>"
                                                       name="<?php echo $child_addon_type_name . ($bundle_sub ? '[' . $index . '][' . $menuitem_id . ']' : ''); ?>" <?php echo 'data-bundle-name="' . $child_addon_type_name . '"'; ?>
                                                       id="<?php echo $child_addon_slug . ($bundle_sub ? '[' . $index . '][' . $menuitem_id . ']' : ''); ?>_<?php echo $p_id; ?>"
                                                       value="<?php echo $child_addon . '|1|' . $get_addon_price . '|' . $use_addon_like; ?>" <?php echo $is_selected; ?> >
                                                <span><?php echo $child_addon_name; ?></span>
                                                <span class="control__indicator"></span>
                                            </label>

                                            <?php if ($get_addon_price > 0) : ?>
                                                <span class="cat_price">&nbsp;+&nbsp;<?php echo pl8app_currency_filter(pl8app_format_amount($get_addon_price)); ?></span> <?php

                                            endif; ?>
                                        </div> <?php
                                    endforeach;

                                else:

                                    $get_addon_price = pl8app_dynamic_addon_price($menuitem_id, $child_data->term_id, $child_data->parent); ?>

                                    <div class="menu-item-list active">
                                        <label for="<?php echo $child_addon_slug . ($bundle_sub ? '[' . $index . '][' . $menuitem_id . ']' : ''); ?>"
                                               class="<?php echo $use_addon_like; ?>-container">
                                            <?php $is_selected = in_array($child_addon_id, $chosen_addons) ? 'checked' : ''; ?>
                                            <input data-type="<?php echo $use_addon_like; ?>"
                                                   type="<?php echo $use_addon_like; ?>"
                                                   name="<?php echo $child_addon_type_name . ($bundle_sub ? '[' . $index . '][' . $menuitem_id . ']' : ''); ?>" <?php echo 'data-bundle-name="' . $child_addon_type_name . '"'; ?>
                                                   id="<?php echo $child_addon_slug . ($bundle_sub ? '[' . $index . '][' . $menuitem_id . ']' : ''); ?>"
                                                   value="<?php echo $child_addon . '|1|' . $get_addon_price . '|' . $use_addon_like; ?>" <?php echo $is_selected; ?> >
                                            <span><?php echo $child_addon_name; ?></span>
                                            <span class="control__indicator"></span>
                                        </label>

                                        <?php if ($get_addon_price > 0) : ?>
                                            <span class="cat_price">&nbsp;+&nbsp;<?php echo pl8app_currency_filter(pl8app_format_amount($get_addon_price)); ?></span> <?php
                                        endif; ?>

                                    </div> <?php
                                endif;
                            endif;
                        }
                    }

                    ?>

                </div>
            <?php } ?>
        </div>
    <?php }
//    do_action('pl8app_display_vat_to_show_products', $menuitem_id);
    return ob_get_clean();
}

/**
 * Get Option and Upgrade items for a specific Menuitem
 *
 * @since 1.0
 * @return void
 */
function get_addon_items_by_menuitem($menuitem_id)
{

    if (empty($menuitem_id)) {
        return;
    }

    // $addons  = wp_get_post_terms( $menuitem_id, 'addon_category' );
    // $addon_ids = $child_ids = array();

    // foreach( $addons as $addon ) {
    //   if( $addon->parent == 0 ) {
    //     $addon_ids[] = $addon->term_id;
    //   }
    //   else {
    //     $child_ids[] = $addon->term_id;
    //   }
    // }

    $addons = get_post_meta($menuitem_id, '_addon_items', true);
    $addon_ids = $child_ids = array();

    if ($addons) {
        foreach ($addons as $addon) {
            if (!empty($addon['category'])) {
                array_push($addon_ids, $addon['category']);
            }
            if (is_array($addon['items'])) {
                $child_ids = array_merge($child_ids, $addon['items']);
            }
        }
    }

    if (is_array($addon_ids) && !empty($addon_ids)) {

        foreach ($addon_ids as $parent) {

            $addon_items = get_term_by('id', $parent, 'addon_category');
            $addon_category_args = array('taxonomy' => 'addon_category', 'parent' => $addon_items->term_id);
            $child_addons = get_terms(apply_filters('pla_addon_category', $addon_category_args));

            if ($child_addons) {
                $child_addons = wp_list_pluck($child_addons, 'term_id');
            }

            if (is_array($child_addons) && !empty($child_addons)) {

                foreach ($child_addons as $child_addon) {
                    $child_data = get_term_by('id', $child_addon, 'addon_category');
                    $child_addon_slug = $child_data->slug;
                    $child_addon_name = $child_data->name;
                    $child_addon_price = pl8app_get_addon_data($child_data->term_id, 'price');
                    $addon_price = html_entity_decode(pl8app_currency_filter(pl8app_format_amount($child_addon_price)));
                    ?>
                    <option data-price="<?php echo $addon_price ?>" data-id="$child_addon"
                            value="<?php echo $child_addon_name . '|' . $child_addon . '|' . $child_addon_price . '|1'; ?> "><?php echo $child_addon_name . ' (' . $addon_price . ') '; ?> </option>
                    <?php
                }
            }
        }
    }
}


/**
 * Output the bundled products in Frontend Modal
 * @param $data
 * @param $menuitem
 */
function pl8app_bundled_products_content($data, $bundled_products){

    $data = str_replace('{hidden}', '', $data);

    $bundled_products_content = '<h6 class="pl8app-bundle-products-header">Bundled Items:</h6>';
    foreach($bundled_products as $order=>$bundled_id) {

        $menu_title = get_the_title($bundled_id);
        $menuitem_desc = get_post_field('post_content', $bundled_id);
        $item_addons = get_menuitem_lists($bundled_id, $cart_key = '', true, $order);

        $price = '';

        if (!empty($bundled_id)) {
            $price = pl8app_get_menuitem_price($bundled_id);
        }

        if (!empty($price)) {
            $formatted_price = pl8app_currency_filter(pl8app_format_amount($price));
        }

        ob_start();
        pl8app_get_template_part('pl8app', 'show-bundle-products');
        $products_content = ob_get_clean();

        $products_content = str_replace('{menuitem_title}', $menu_title, $products_content);
        $products_content = str_replace('{product_price}', $price, $products_content);
        $products_content = str_replace('{formate_price}', $formatted_price, $products_content);
        $products_content = str_replace('{product_id}', $bundled_id, $products_content);
        $products_content = str_replace('{menuitemslist}', $item_addons, $products_content);
        $products_content = str_replace('{itemdescription}', $menuitem_desc, $products_content);
        $bundled_products_content .= $products_content;
    }

    $data = str_replace('{bundleditems}', $bundled_products_content, $data);

    return $data;
}
add_filter('pl8app_bundled_products_content','pl8app_bundled_products_content', 10 , 2);



/**
 * Add the VAT to Cart item Options
 */

function pl8app_add_vat_to_cart_options($options, $menuitem){

    $tax_key = $menuitem->get_vat();
    if(!empty($tax_key)){
        $options['tax_key'] = $tax_key;
    }

    return $options;
}

add_filter('pl8app_add_vat_to_cart_options', 'pl8app_add_vat_to_cart_options', 10 , 2);




