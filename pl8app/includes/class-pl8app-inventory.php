<?php
/**
 * PL8_Inventory
 *
 * @package PL8_Inventory
 * @since 1.0.1
 */

defined('ABSPATH') || exit;

/**
 * Main PL8_Inventory Class.
 *
 * @class PL8_Inventory
 */
class PL8_Inventory
{

    /**
     * PL8_Inventory version.
     *
     * @var string
     */
    public $version = '1.1';

    /**
     * PL8_Inventory Settings.
     *
     * @var array
     */
    private static $settings = array();

    /**
     * The single instance of the class.
     *
     * @var PL8_Inventory
     * @since 1.0.0
     */
    protected static $_instance = null;

    /**
     * Main PL8_Inventory Instance.
     *
     * Ensures only one instance of PL8_Inventory is loaded or can be loaded.
     *
     * @since 1.0.0
     * @return PL8_Inventory - Main instance.
     */
    public static function instance()
    {
        if (is_null(self::$_instance)) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    /**
     * PL8_Inventory Constructor.
     */
    public function __construct()
    {

        $this->define_constants();

        $this->init_hooks();

        self::$settings = $this->get_stock_settings();
    }

    /**
     * Define constant if not already set.
     *
     * @param string $name Constant name.
     * @param string|bool $value Constant value.
     */
    private function define($name, $value)
    {
        if (!defined($name)) {
            define($name, $value);
        }
    }

    /**
     * Define Constants
     *
     * @since 1.0.0
     */
    private function define_constants()
    {
        $this->define('PL8_INV_VERSION', $this->version);
        $this->define('PL8_INV_PLUGIN_DIR', plugin_dir_path(PL8_PLUGIN_FILE));
        $this->define('PL8_INV_PLUGIN_URL', plugin_dir_url(PL8_PLUGIN_FILE));
        $this->define('PL8_INV_BASE', plugin_basename(PL8_PLUGIN_FILE));
    }

    /**
     * Hook into actions and filters.
     *
     * @since 1.0.0
     */
    private function init_hooks()
    {

        // Enqueue admin styles and scripts for addon usage
        add_action('admin_enqueue_scripts', array($this, 'inventory_admin_styles'));

        add_action('admin_enqueue_scripts', array($this, 'inventory_admin_scripts'));

        // Add Column To Display Stock Status and Stock Settings
        add_filter('pl8app_menuitem_columns', array($this, 'pl8app_inventory_columns'), 10, 1);

        // Create post listing column in order to display stock value
        add_action('manage_menuitem_posts_custom_column', array($this, 'pl8app_inventory_columns_content'), 10, 2);

        // Inventory Setting in Menu item detail page
        add_action('pl8app_menuitem_options_general_item_details', array($this, 'pl8app_add_inventory_setting_area'));

        // Validate stock while Proceed to Checkout
        add_filter('pl8app_proceed_checkout_page', array($this, 'pl8app_inventory_check_error'), 15, 1);

        // Validate before purchasing on Checkout Page
        add_action('pl8app_checkout_error_checks', array($this, 'pl8app_inventory_validate_stock_before_purchase'));

        // Update Stock Count once Purchase is Done
        add_action('pl8app_payment_saved', array($this, 'pl8app_inventory_update_after_payment'), 10, 2);

        // Disable Clicking on ADD if Out of Stock
        add_filter('pl8app_is_orderable', array($this, 'pl8app_inventory_check_item_orderable'), 10, 2);

        // Text for Out of Stock
        add_filter('pl8app_not_available', array($this, 'pl8app_inventory_no_stock_label'), 10, 2);

        // Return items to Stock once the Order is cancelled
        add_action('pl8app_update_order_status', array($this, 'pl8app_inventory_update_stock_on_order_cancel'), 10, 2);

        // Update item availability with AJAX
        add_action('wp_ajax_pl8app_inventory_update_stock_status', array($this, 'pl8app_inventory_update_stock_status'));

        // Create filter option to search items by their availaibility
        // add_action( 'restrict_manage_posts', array( $this, 'pl8app_inventory_stock_search_filter' ) );

        // parse query based on the filter condition and value
        // add_filter( 'parse_query', array( $this, 'pl8app_inventory_stock_search_filter_query' ), 10);

        // Add class to Menu item container if item is not available
        add_filter('pl8app_menuitem_class', array($this, 'pl8app_inventory_disabled_class'), 10, 2);

    }


    /**
     * Enqueue stylesheets for admin area usage
     *
     * @since 1.0.0
     */
    public function inventory_admin_styles()
    {
        wp_register_style('pl8appi_admin_styles', PL8_INV_PLUGIN_URL . 'assets/css/admin.css', array());
        wp_enqueue_style('pl8appi_admin_styles');
    }

    /**
     * Enqueue scripts for admin area usage
     *
     * @since 1.0.0
     */
    public function inventory_admin_scripts()
    {

        wp_register_script('pl8appi-admin-scripts', PL8_INV_PLUGIN_URL . 'assets/js/admin/inventory-admin-scripts.js', array('jquery'));

        wp_enqueue_script('pl8appi-admin-scripts');

        wp_localize_script(
            'pl8appi-admin-scripts',
            'pl8appi_admin_params',
            array(
                'ajax_url' => admin_url('admin-ajax.php')
            )
        );
    }

    /**
     * Adding columns for Stock value and Availibility Toggle
     * Availibility toggle will depend on the settings
     *
     * @since 1.0.0
     * @param arr $columns Existing array of columns
     */
    public function pl8app_inventory_columns($columns)
    {

        $columns["stock_value"] = __('Stock', 'pl8app-inventory');

        return $columns;
    }

    /**
     * Adding column content for Stock value and Availibility Toggle
     *
     * @since 1.0.0
     * @param str $column name of columns to add content for
     * @param int $item_id Current item ID to check functionality
     */
    public function pl8app_inventory_columns_content($column, $item_id)
    {

        $stock_enable = get_post_meta($item_id, 'pl8app_item_stock_enable', true);
        // Check for Stock Enabled Setting
        if ($stock_enable != 1)
            return;
        switch ($column) {

            case 'stock_value':

                $product_type = get_post_meta($item_id, '_pl8app_product_type', true);
                if ($product_type == 'bundle') {
                    $bundle_items = (array)get_post_meta($item_id, '_pl8app_bundled_products', true);
                    $bundle_item_stock = array();
                    foreach ($bundle_items as $bundled_id) {
                        if (!isset($bundle_item_stock[$bundled_id])) $bundle_item_stock[$bundled_id] = 0;
                        $bundle_item_stock[$bundled_id] += 1;
                    }

                    foreach ($bundle_item_stock as $key => $item_stock_divider) {
                        $bundle_item_stock[$key] = (int)(get_post_meta($key, 'pl8app_item_stock', true) / $item_stock_divider);
                    }
                    $bundle_item_stock_quanity = min($bundle_item_stock);

                    $value = '<span>' . (!empty($bundle_item_stock_quanity) ? $bundle_item_stock_quanity : 0) . '</span>';
                    echo $value;
                } else {
                    $stock_quantity = get_post_meta($item_id, 'pl8app_item_stock', true);
                    $value = '<input type="number" class="pl8app_stock_status" name="pl8app_stock_status" value="' . (!empty($stock_quantity) ? $stock_quantity : 0) . '" data-item-id="' . $item_id . '"/>';

                    $value .= '<span class="stock-status-loading"></span>';
                    echo $value;
                }

                break;

        }
    }

    /**
     * Get Inventory settings from the pl8app Settings
     *
     * @since 1.0.0
     * @return mixed
     */
    protected function get_stock_settings()
    {
        $stock_options = get_option('pl8app_settings', array());
        return apply_filters('pl8app_inventory_options', $stock_options);
    }


    public function pl8app_add_inventory_setting_area()
    {

        $post_id = get_the_ID();
        //Check product is bundle or not
        $product_type = get_post_meta($post_id, '_pl8app_product_type', true);
        $stock_enable = get_post_meta($post_id, 'pl8app_item_stock_enable', true);
        if ($product_type == 'bundle') {
            $bundle_items = (array)get_post_meta($post_id, '_pl8app_bundled_products', true);
            $bundle_item_stock = array();
            foreach ($bundle_items as $bundled_id) {
                if (!isset($bundle_item_stock[$bundled_id])) $bundle_item_stock[$bundled_id] = 0;
                $bundle_item_stock[$bundled_id] += 1;
            }

            foreach ($bundle_item_stock as $key => $item_stock_divider) {
                $bundle_item_stock[$key] = (int)((int)get_post_meta($key, 'pl8app_item_stock', true) / $item_stock_divider);
            }
            $stock_value = min($bundle_item_stock);

        } else {
            $stock_value = get_post_meta($post_id, 'pl8app_item_stock', true);
        }
        ?>
        <div class="pl8app-metabox-container">
            <div class="toolbar toolbar-top">
                <span class="pl8app-toolbar-title">
				<?php esc_html_e('Menu Item Stock Management', 'pl8app'); ?>
                </span>
                <span class="alignright">
                    <span><?php esc_html_e('Disable/Enable', 'pl8app'); ?></span>
                    <label for="stock_enable" class="inventory_setting_checkbox_wrapper">
                        <input type="hidden" name="pl8app_item_stock_enable" value="-1">
                        <input type="checkbox" class="st_checkbox"
                               name="pl8app_item_stock_enable" <?php echo checked($stock_enable, 1, true); ?> value="1">
                        <span class="st_checkbox_slider round"></span>
                    </label>
                </span>
            </div>
            <div class="options_group inventory_setting <?php echo $stock_enable == 1 ? '' : 'hidden' ?>">
                <div class="pl8app-tab-content">
                    <p>
                        <strong>Stock Value:</strong>
                        <?php if ($product_type == 'bundle') {
                            ?>
                            <input type="hidden" name="pl8app_item_stock" placeholder="10"
                                   value="<?php echo $stock_value; ?>">
                            <span><?php echo $stock_value; ?></span>
                            <?php
                        } else {
                            ?>
                            <input type="number" name="pl8app_item_stock" placeholder="10"
                                   value="<?php echo $stock_value; ?>">
                        <?php } ?>
                    </p>
                </div>
            </div>
        </div>
        <?php

    }

    /**
     * Get item stock status by checking and calculating the values
     *
     * @since 1.0.0
     * @param int $post_id Post ID
     */
    public function get_item_stock_status($post_id)
    {

        //Check product is bundle or not
        $product_type = get_post_meta($post_id, '_pl8app_product_type', true);
        $stock_enable = get_post_meta($post_id, 'pl8app_item_stock_enable', true);
        // Check for Stock Enabled Setting
        if ($stock_enable != 1)
            return 'available';

        if ($product_type == 'bundle') {
            $bundle_items = (array)get_post_meta($post_id, '_pl8app_bundled_products', true);
            $bundle_item_stock = array();
            foreach ($bundle_items as $bundled_id) {
                if (!isset($bundle_item_stock[$bundled_id])) $bundle_item_stock[$bundled_id] = 0;
                $bundle_item_stock[$bundled_id] += 1;
            }

            foreach ($bundle_item_stock as $key => $item_stock_divider) {
                $bundle_item_stock[$key] = (int)(get_post_meta($key, 'pl8app_item_stock', true) / $item_stock_divider);
            }
            $bundle_item_stock_quanity = min($bundle_item_stock);
            $available_stock = !empty($bundle_item_stock_quanity) ? (int)$bundle_item_stock_quanity : 0;
        } else {
            if (isset(self::$settings['enable_null_stock'])) {

                $stock_meta = get_post_meta($post_id, 'pl8app_item_stock', true);
                $available_stock = !empty($stock_meta) ? (int)$stock_meta : 0;

            } else {

                if (metadata_exists('post', $post_id, 'pl8app_item_stock')) {
                    $stock_meta = get_post_meta($post_id, 'pl8app_item_stock', true);
                    $available_stock = !empty($stock_meta) ? (int)$stock_meta : 0;
                } else {
                    $available_stock = 'available';
                }

            }
        }
        return $available_stock;
    }

    /**
     * Validate Cart contents when Proceed to Checkout called
     *
     * @since 1.0.0
     * @param string $response Original Response Text
     */
    public function pl8app_inventory_check_error($response)
    {

        $cart_items = pl8app_get_cart_contents();

        $quantity_array = array();

        // Calculate Total Quantity for Each Items
        foreach ($cart_items as $item) {
            //Check product Type
            $product_type = get_post_meta($item['id'], '_pl8app_product_type', true);
            if ($product_type == 'bundle') {
                continue;
            }

            if (isset($quantity_array[$item['id']])) {
                $quantity_array[$item['id']] += $item['quantity'];
            } else {
                $quantity_array[$item['id']] = $item['quantity'];
            }
        }

        // Validate each items with Available stock
        foreach ($quantity_array as $post_id => $quantity) {


            $post = get_post($post_id);

            // Get product details
            $available_stock = $this->get_item_stock_status($post_id);

            // If empty ignore the stock
            if ($available_stock == 'available')
                continue;

            // If not Empty do the calculation
            if (empty($available_stock)) {
                $response['status'] = 'error';
                $response['error_msg'] = $post->post_title . ' is currently Out of Stock.';
            }

            if ($available_stock > 0) {

                if ((int)$quantity > (int)$available_stock) {
                    $response['status'] = 'error';
                    $response['error_msg'] = $post->post_title . ' is Low on Stock. You can add maximum of ' . (int)$available_stock . ' to your cart.';
                }
            }

            return $response;
        }
    }

    /**
     * Sort the array with item and their total quantity in cart
     *
     * @since 1.0.0
     * @param array $menuitems Cart Items
     */
    public function inventory_sort_items_with_total_quantity($menuitems)
    {

        if (!is_array($menuitems))
            return;

        $quantity_array = array();

        // Calculate Total Quantity for Each Items
        foreach ($menuitems as $item) {
            //Check product Type
            $product_type = get_post_meta($item['id'], '_pl8app_product_type', true);
            if ($product_type == 'bundle') {
                continue;
            }

            if (isset($quantity_array[$item['id']])) {
                $quantity_array[$item['id']] += $item['quantity'];
            } else {
                $quantity_array[$item['id']] = $item['quantity'];
            }
        }

        return $quantity_array;
    }

    /**
     * Validate Cart contents when Purchase button is clicked
     *
     * @since 1.0.0
     * @return void
     */
    public function pl8app_inventory_validate_stock_before_purchase()
    {

        $cart_items = pl8app_get_cart_contents();
        $quantity_array = $this->inventory_sort_items_with_total_quantity($cart_items);

        // Validate each items with Available stock
        foreach ($quantity_array as $post_id => $quantity) {
            $stock_enable = get_post_meta($post_id, 'pl8app_item_stock_enable', true);
            if ($stock_enable != 1) {
                continue;
            }
            $post = get_post($post_id);

            // Get product details
            $available_stock = $this->get_item_stock_status($post_id);

            // If not Empty do the calculation
            if (empty($available_stock)) {
                pl8app_set_error('pl8app-item-sold-out', sprintf(__($post->post_title . ' is currently Out of Stock.', 'pl8app-inventory')));
            }

            if ($available_stock > 0) {

                if ((int)$quantity > (int)$available_stock) {
                    pl8app_set_error('pl8app-item-low-stock', sprintf(__($post->post_title . ' is Low on Stock. Only ' . (int)$available_stock . ' left now.')));
                }
            }
        }

    }

    /**
     * Updating new Stock value once the order is placed
     * It does not check whether the payment is successful or not
     *
     * @since 1.0.0
     * @param int $payment_id Payment ID
     * @param object $payment Complete Payment Object
     */
    public function pl8app_inventory_update_after_payment($payment_id, $payment)
    {

        // Update stock value after purchase is done
        if (!empty($payment->menuitems) && $payment->status == 'pending') {

            foreach ($payment->menuitems as $key => $item) {

                //Check product Type
                $product_type = get_post_meta($item['id'], '_pl8app_product_type', true);
                if ($product_type == 'bundle') {
                    continue;
                }

                $post_id = $item['id'];
                $quantity = $item['options']['quantity'];

                // Current Stock
                $stock = $this->get_item_stock_status($post_id);

                if (!empty($stock) && $stock !== 'available') {
                    $updated_stock = (int)$stock - (int)$quantity;

                    // Update new Stock Value
                    update_post_meta($post_id, 'pl8app_item_stock', $updated_stock);
                }
            }
        }
    }

    /**
     * Validate is an item is In stock and can be puirchasd or not
     *
     * @since 1.0.0
     * @param string $response Original Response Text
     * @param int $item_id Item ID
     */
    public function pl8app_inventory_check_item_orderable($response, $item_id)
    {

        // Get product details
        $available_stock = $this->get_item_stock_status($item_id);

        // If not Empty do the calculation
        if (empty($available_stock)) {
            return false;
        }

        return $response;
    }

    /**
     * Update the "Button Text" when the item is out of Stock
     *
     * @since 1.0.0
     * @param string $response Original Response Text
     * @param int $item_id Item ID to check stock
     */
    public function pl8app_inventory_no_stock_label($label, $item_id)
    {

        // Get product details
        $available_stock = $this->get_item_stock_status($item_id);

        // If not Empty do the calculation
        if (empty($available_stock)) {
            if (isset(self::$settings['no_stock_text']) && self::$settings['no_stock_text'] != '')
                return self::$settings['no_stock_text'];
            else
                return __('Sold Out', 'pl8app-inventory');
        }

        return $label;
    }

    /**
     * Update stock amount of the items once the Order is cancelled
     *
     * @since 1.0.0
     * @param int $item_id Item ID to check stock
     * @param string $status New Updated Status
     */
    public function pl8app_inventory_update_stock_on_order_cancel($payment_id, $status)
    {

        if ($status !== 'cancelled')
            return;

        if (isset(self::$settings['return_to_stock'])) {

            $menuitems = pl8app_get_payment_meta_cart_details($payment_id);
            $quantity_array = $this->inventory_sort_items_with_total_quantity($menuitems);

            // Validate each items with Available stock
            foreach ($quantity_array as $post_id => $quantity) {

                if (!empty($quantity) && (int)$quantity == 0)
                    continue;

                // Get product details
                $stock = $this->get_item_stock_status($post_id);

                // Current Stock
                if (!empty($stock) && $stock !== 'available') {
                    $updated_stock = (int)$stock + (int)$quantity;

                    // Update new Stock Value
                    update_post_meta($post_id, 'pl8app_item_stock', $updated_stock);
                }
            }
        }
    }

    /**
     * Ajax action to update the availibilty status of an item
     *
     * @since 1.0.0
     */
    public function pl8app_inventory_update_stock_status()
    {

        $item_id = $_REQUEST['item_id'];
        $status = $_REQUEST['status'];

        update_post_meta($item_id, 'pl8app_item_stock', $status);
        wp_send_json_success();

    }


    /**
     * Add filter for listing items base on their availibility status
     *
     * @since 1.0.0
     * @param str $post_type
     */
    public function pl8app_inventory_stock_search_filter($post_type)
    {

        if ('menuitem' !== $post_type) {
            return;
        }

        $selected = '';
        $request_attr = 'stock_status';

        if (isset($_REQUEST[$request_attr])) {
            $selected = $_REQUEST[$request_attr];
        }

        $meta_key = 'pl8app_stock_status';
        global $wpdb;
        $results = $wpdb->get_col(
            $wpdb->prepare("
	            SELECT DISTINCT pm.meta_value FROM {$wpdb->postmeta} pm
	            LEFT JOIN {$wpdb->posts} p ON p.ID = pm.post_id
	            WHERE pm.meta_key = '%s'
	            AND p.post_status IN ('publish', 'draft')
	            ORDER BY pm.meta_value",
                $meta_key
            )
        );

        echo '<select id="pl8app_stock_status" name="pl8app_stock_status">';
        echo '<option value="0">' . __('Show all Items', 'pl8app-inventory') . ' </option>';
        foreach ($results as $status) {
            $select = ($status == $selected) ? ' selected="selected"' : '';
            echo '<option value="' . $status . '"' . $select . '>' . ucfirst($status) . ' </option>';
        }
        echo '</select>';
    }

    /**
     * Query posts as per the filter chosen by user
     *
     * @since 1.0.0
     * @param obj $query Main Query Object
     */
    public function pl8app_inventory_stock_search_filter_query($query)
    {

        if (!(is_admin() && $query->is_main_query())) {
            return $query;
        }

        if (!('menuitem' === $query->query['post_type'] && isset($_REQUEST['pl8app_stock_status']))) {
            return $query;
        }

        if (empty($_REQUEST['pl8app_stock_status'])) {
            return $query;
        }

        $query->query_vars['meta_key'] = 'pl8app_stock_status';
        $query->query_vars['meta_value'] = $_REQUEST['pl8app_stock_status'];
        $query->query_vars['meta_compare'] = '=';

        return $query;
    }


    public function pl8app_inventory_disabled_class($classes, $item_id)
    {

        $available_stock = $this->get_item_stock_status($item_id);

        if (empty($available_stock)) {
            return $classes . ' item-disabled';
        }

        return $classes;
    }

}