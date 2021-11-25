<?php

// Exit if accessed directly
if (!defined('ABSPATH')) exit;

/**
 * Register all the meta boxes for the menu items custom post type
 *
 * @since 3.0
 * @return void
 */
class pla_MenuItem_Meta_Boxes
{

    public static function init()
    {
        add_action('add_meta_boxes', array(__CLASS__, 'add_meta_boxes'));
        add_action('save_post', array(__CLASS__, 'save_meta_boxes'), 1, 2);
    }

    public static function add_meta_boxes()
    {
        $screen = get_current_screen();
        $screen_id = $screen ? $screen->id : '';

        add_meta_box('pl8app-menuitem-data', __('Menu Item Data', 'pl8app'), array(__CLASS__, 'metabox_output'), 'menuitem', 'normal', 'high');
    }

    public static function metabox_output($post)
    {
        global $thepostid, $menuitem_object;

        $thepostid = $post->ID;
        $menuitem_object = new pl8app_Menuitem($thepostid);

        wp_nonce_field('pl8app_save_data', 'pl8app_meta_nonce');
        wp_nonce_field( 'pl8app_save_meta_data', 'pl8app_menuitem_meta_box_nonce');

        include 'views/html-menuitem-data-panel.php';
    }

    /**
     * Show tab content/settings.
     */
    private static function output_tabs()
    {
        global $post, $thepostid, $menuitem_object;

        include 'views/html-menuitem-data-general.php';
        include 'views/html-menuitem-data-category.php';
        include 'views/html-menuitem-data-addons.php';
    }

    /**
     * Return array of tabs to show.
     *
     * @return array
     */
    private static function get_menuitem_data_tabs()
    {
        $tabs = apply_filters(
            'pl8app_menuitem_data_tabs',
            array(
                'general' => array(
                    'label' => __('General', 'pl8app'),
                    'target' => 'general_menuitem_data',
                    'class' => array(),
                    'icon' => 'icon-general',
                    'priority' => 10,
                ),
                'category' => array(
                    'label' => __('Category', 'pl8app'),
                    'target' => 'category_menuitem_data',
                    'class' => array(),
                    'icon' => 'icon-category',
                    'priority' => 20,
                ),
                'addons' => array(
                    'label' => __('Options and Upgrades', 'pl8app'),
                    'target' => 'addons_menuitem_data',
                    'class' => array(),
                    'icon' => 'icon-addon',
                    'priority' => 30,
                )
            )
        );

        // Sort tabs based on priority.
        uasort($tabs, array(__CLASS__, 'menuitem_data_tabs_sort'));

        return $tabs;
    }

    public static function metabox_fields()
    {
        $fields = array(
            'pl8app_menu_type',
            'pl8app_price',
            '_variable_pricing',
            'pl8app_variable_price_label',
            'pl8app_variable_prices',
            'addons'
        );

        return apply_filters('pl8app_metabox_fields_save', $fields);
    }

    public static function save_meta_boxes($post_id, $post)
    {

        // $post_id and $post are required
        if (empty($post_id) || empty($post) || $post->post_type != 'menuitem') {
            return;
        }

        // Dont' save meta boxes for revisions or autosaves.
        if ((defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) || is_int(wp_is_post_revision($post)) || is_int(wp_is_post_autosave($post))) {
            return;
        }

        // Check the nonce.
        if (empty($_POST['pl8app_meta_nonce']) || !wp_verify_nonce(wp_unslash($_POST['pl8app_meta_nonce']), 'pl8app_save_data')) {
            return;
        }

        // Check the post being saved == the $post_id to prevent triggering this call for other save_post events.
        if (empty($_POST['post_ID']) || absint($_POST['post_ID']) !== $post_id) {
            return;
        }

        // Check user has permission to edit.
        if (!current_user_can('edit_post', $post_id)) {
            return;
        }

        // Get custom fields to save for the metabox
        $fields = self::metabox_fields();

        foreach ($fields as $field) {

            if ($field != 'addons' && !empty($_POST[$field])) {
                $value = apply_filters('pl8app_metabox_save_' . $field, $_POST[$field]);
                update_post_meta($post_id, $field, $value);
            } else {
                delete_post_meta($post_id, $field);
            }
        }

        // Set the lowest price as the product price so that we can use it on frontend display.
        if (pl8app_has_variable_prices()) {
            $lowest = pl8app_get_lowest_price_option($post_id);
            update_post_meta($post_id, 'pl8app_price', $lowest);
        }

        // Save categories for the menu item.
        if (!empty($_POST['menu_categories']) && count($_POST['menu_categories']) > 0) {
            $menu_categories = $_POST['menu_categories'];
            wp_set_post_terms($post_id, $menu_categories, 'menu-category');
        }

        // Save addons for the menu item.
        if (!empty($_POST['addons']) && count($_POST['addons']) > 0) {

            $addons = $_POST['addons'];

            $addon_terms = array();
            $addon_to_save = array();

            foreach ($addons as $addon) {
                if (!empty($addon['category'])) {
                    $addon_to_save[$addon['category']] = $addon;
                    $addon_terms[] = $addon['category'];
                    if (isset($addon['items'])) {
                        foreach ($addon['items'] as $item) {
                            $addon_terms[] = $item;
                        }
                    }
                }
            }

            $addon_terms = array_unique($addon_terms);
            $product_terms = wp_get_post_terms($post_id, 'addon_category', array('fields' => 'ids'));

            if (!is_wp_error($product_terms)) {
                $terms_to_remove = array_diff($product_terms, $addon_terms);
                wp_remove_object_terms($post_id, $terms_to_remove, 'addon_category');
            }

            wp_set_post_terms($post_id, $addon_terms, 'addon_category', true);
            update_post_meta($post_id, '_addon_items', $addon_to_save);

        } else {

            $product_terms = wp_get_post_terms($post_id, 'addon_category', array('fields' => 'ids'));
            if (!is_wp_error($product_terms)) {
                wp_remove_object_terms($post_id, $product_terms, 'addon_category');
            }
            update_post_meta($post_id, '_addon_items', '');
        }

        // Save Addon Category
        if (isset($_POST['addon_category']) && !empty($_POST['addon_category']) && count($_POST['addon_category']) > 0) {

            $addon_data = array();

            $addon_categories = $_POST['addon_category'];

            foreach ($addon_categories as $key => $addon_cat) {

                $name = !empty($addon_cat['name']) ? $addon_cat['name'] : '';
                $type = !empty($addon_cat['type']) ? $addon_cat['type'] : 'single';

                $term_data = wp_insert_term($name, 'addon_category', array('parent' => 0, 'slug' => sanitize_title($name)));

                if (!is_wp_error($term_data)) {

                    if (!empty($term_data['term_id'])) {

                        $term_id = $term_data['term_id'];

                        update_term_meta($term_id, '_type', $type);

                        wp_set_post_terms($post_id, $term_id, 'addon_category', true);

                        $addon_data[$key]['category'] = $term_id;

                        if (!empty($addon_cat['addon_name']) && count($addon_cat['addon_name']) > 0) {

                            foreach ($addon_cat['addon_name'] as $k => $child_addon) {

                                $term_name = !empty($child_addon) ? $child_addon : '';
                                $term_price = !empty($addon_cat['addon_price'][$k]) ? $addon_cat['addon_price'][$k] : '';

                                if (!empty($term_name)) {
                                    $child_terms = wp_insert_term($term_name, 'addon_category', array('parent' => $term_id, 'slug' => sanitize_title($term_name)));
                                }

                                if (!empty($term_price) && !empty($child_terms['term_id'])) {
                                    update_term_meta($child_terms['term_id'], '_price', $term_price);
                                    wp_set_post_terms($post_id, $child_terms['term_id'], 'addon_category', true);
                                    $addon_data[$key]['items'][] = $child_terms['term_id'];
                                }
                            }
                        }
                    }
                }
            }
            self::update_addon_items($post_id, $addon_data);
        }

        // Hook to allow users to save any custom fields.
        do_action('pl8app_save_menuitem', $post_id, $post);
    }


    /**
     * Update menu Option and Upgrade items
     *
     * @since 3.0
     * @param int $post_id MenuItem id.
     * @param int $addon_category addon category.
     * @param int $addon_items addon category items
     *
     * @return bool
     */
    public static function update_addon_items($post_id, $addon_data)
    {

        if (empty($post_id)) {
            return;
        }

        $get_addon_items = get_post_meta($post_id, '_addon_items', true);

        if (is_array($get_addon_items)
            && !empty($get_addon_items)) {
            foreach ($addon_data as $addon_list) {
                $get_addon_items[] = $addon_list;
            }
        } else {
            $get_addon_items = $addon_data;
        }

        update_post_meta($post_id, '_addon_items', $get_addon_items);
    }


    /**
     * Callback to sort menuitem data tabs on priority.
     *
     * @since 3.0
     * @param int $a First item.
     * @param int $b Second item.
     *
     * @return bool
     */
    private static function menuitem_data_tabs_sort($a, $b)
    {
        if (!isset($a['priority'], $b['priority'])) {
            return -1;
        }

        if ($a['priority'] === $b['priority']) {
            return 0;
        }

        return $a['priority'] < $b['priority'] ? -1 : 1;
    }

}

pla_MenuItem_Meta_Boxes::init();