<?php
/**
 * pl8app_Order_Time_intervals_Limit_Functions
 *
 * @package pl8app_Order_Time_intervals_Limit_Functions
 * @since 1.0
 */

defined('ABSPATH') || exit;


class pl8app_Order_Time_intervals_Limit_Functions
{


    public function __construct()
    {

        add_filter('pl8app_is_service_enabled', array($this, 'otil_service_type'), 10, 2);

        add_filter('pl8app_store_time_interval', array($this, 'otil_store_time_interval'), 10);

        add_action('wp_enqueue_scripts', array($this, 'pl8app_otil_scripts'), 10);

        add_action('pl8app_after_service_time', array($this, 'display_service_type_message'));

        add_filter('pl8app_proceed_checkout', array($this, 'otil_check_error'), 10);

        add_filter('pl8app_validate_slot', array($this, 'otil_check_service_slot'), 11);

        add_action('pl8app_checkout_error_checks', array($this, 'validate_order_limit'), 10, 2);

        add_action('pl8app_order_history_header_after', array($this, 'customer_order_history_ext_label'), 10);

        add_action('pl8app_order_history_row_end', array($this, 'customer_order_history_ext_content'), 10, 2);

        add_action('pl8app_check_item_timing_and_reorder', array($this, 'pl8app_check_item_timing_and_reorder'), 10, 3);

        add_action('wp_ajax_pl8app_reorder_cart', array($this, 'reorder_cart'));
        add_action('wp_ajax_nopriv_pl8app_reorder_cart', array($this, 'reorder_cart'));
    }

    /**
     * Check service slot
     *
     * @since 1.0
     * @param array $response Params for response
     * @return array| response
     */
    public function otil_check_service_slot($response)
    {
        if (!isset($response['status'])
            || $response['status'] !== 'error') {

            $stop_store = pl8app_get_option('emergency_stop', false);

            if($stop_store){
                $response['status'] = 'error';
                $response['msg'] = __('No service is available!','pl8app');
                return $response;
            }

            $service_type = !empty($_POST['serviceType']) ? $_POST['serviceType'] : '';

            $service_time = !empty($_POST['serviceTime']) ? $_POST['serviceTime'] : '';

            $service_date = !empty($_POST['service_date']) ? $_POST['service_date'] : '';

            /**
             *  When reorder is requested with Payment Id, reorder the items based on Item Timing and Service Time!
             */
            if (!empty($_POST['payment_id'])) do_action('pl8app_check_item_timing_and_reorder', $_POST['payment_id'], $service_time, $service_date);
            $otil_settings = new Order_Time_Interval_Limit_Settings();
            $otil_settings = $otil_settings->options;

            $orders_per_interval = !empty($otil_settings['orders_per_interval']) ? intval($otil_settings['orders_per_interval']) : '';
            $orders_per_delivery_interval = !empty($otil_settings['orders_per_delivery_interval']) ? intval($otil_settings['orders_per_delivery_interval']) : $orders_per_interval;
            $orders_per_pickup_interval = !empty($otil_settings['orders_per_pickup_interval']) ? intval($otil_settings['orders_per_pickup_interval']) : $orders_per_interval;

            $orders_interval = ($service_type == 'delivery') ? $orders_per_delivery_interval : $orders_per_pickup_interval;

            if (!empty($service_date) && !empty($service_time)) {
                //Count all the orders
                $count = $this->get_orders_count($service_date, $service_time, $service_type);

                if (!empty($orders_interval) && $count >= $orders_interval && !empty($service_time)) {
                    $response['status'] = 'error';
                    $response['msg'] = $otil_settings['order_interval_error_msg'];
                }
            }
        }
        return $response;
    }


    public function pl8app_check_item_timing_and_reorder($payment_id, $service_time, $service_date)
    {

        pl8app_empty_cart();

        $payment = new pl8app_Payment($payment_id);

        $payment_meta   = $payment->get_meta();
        $cart_details = isset($payment_meta['cart_details'])? $payment_meta['cart_details']: '';

        if(is_array($cart_details)) {
            foreach ($cart_details as $key=>$item){
                $options = array();
                $options['id'] = $item['id'];
                $options['price'] = $item['item_price'];
                $options['quantity'] = $item['quantity'];
                $options['instruction'] = $item['instruction'];
                $options['addon_items'] = $item['addon_items'];
                if(isset($options['addon_items']['price_id'])) unset($options['addon_items']['price_id']);
                if(isset($options['addon_items']['quantity'])) unset($options['addon_items']['quantity']);
                $options['price_id'] = isset($item['addon_items']['price_id'])?$item['addon_items']['price_id']:'';
                if(check_availability_menu_item_timing($item['id'], $service_time, $service_date)) pl8app_add_to_cart($item['id'], $options);
            }
        }
    }

    /**
     * Check items per order
     *
     * @since 1.0
     * @param array $response Params for response
     * @return array| response
     */
    public function otil_check_error($response)
    {

        $cart_items = pl8app_get_cart_contents();
        $quantity = array();

        $otil_settings = new Order_Time_Interval_Limit_Settings();
        $otil_settings = $otil_settings->options;

        $otil_ordered_items_error = !empty($otil_settings['menuitems_order_error']) ? $otil_settings['menuitems_order_error'] : __('You have more than max allowed items in your cart. Please try to remove some.', 'pl8app-otil');

        $service_time = !empty($_COOKIE['service_time']) ? $_COOKIE['service_time'] : '';
        $service_date = !empty($_COOKIE['service_date']) ? $_COOKIE['service_date'] : current_time('Y-m-d');
        $service_type = !empty($_COOKIE['service_type']) ? $_COOKIE['service_type'] : '';

        $otil_order_items = !empty($otil_settings['menuitems_per_order']) ? $otil_settings['menuitems_per_order'] : '';

        if ($service_type == 'delivery') {
            $otil_order_items = !empty($otil_settings['menuitems_per_delivery_order']) ? $otil_settings['menuitems_per_delivery_order'] : $otil_order_items;
        } else {
            $otil_order_items = !empty($otil_settings['menuitems_per_pickup_order']) ? $otil_settings['menuitems_per_pickup_order'] : $otil_order_items;
        }

        $count = $this->get_orders_count($service_date, $service_time, $service_type);

        $orders_interval = !empty($otil_settings['orders_per_interval']) ? $otil_settings['orders_per_interval'] : '';

        if (!empty($otil_order_items)) {
            if (is_array($cart_items)) {
                foreach ($cart_items as $key => $cart_item) {

                    $item_quantity = isset($cart_item['quantity']) ? $cart_item['quantity'] : 1;
                    array_push($quantity, $item_quantity);

                }

                $total_cart_item = array_sum($quantity);

                if ($otil_order_items < $total_cart_item) {
                    $response['status'] = 'error';
                    $response['error_msg'] = $otil_ordered_items_error;
                } else if (!empty($orders_interval) && $count >= $orders_interval && !empty($service_time)) {
                    $response['status'] = 'error';
                    $response['error_msg'] = $otil_settings['order_interval_error_msg'];
                }
            }
        }
        return $response;
    }

    /**
     * Add script for the frontend
     *
     * @since 1.0
     * @return mixed
     */
    public static function pl8app_otil_scripts()
    {

        $otil_settings = new Order_Time_Interval_Limit_Settings();
        $otil_settings = $otil_settings->options;

        $max_menuitems_error = !empty($otil_settings['menuitems_order_error']) ? $otil_settings['menuitems_order_error'] : '';

        wp_register_script('pl8app-otil-functions', PL8_PLUGIN_URL . 'assets/js/pl8app-otil.js', array('jquery'), PL8_VERSION);

        $params = array(
            'ajaxurl' => pl8app_get_ajax_url(),
            'order_interval_error_title' => esc_html('Time slot error', 'pl8app-otil'),
            'order_interval_error_msg' => esc_html('Please select another time slot for your order', 'pl8app-otil'),
            'order_menuitems_nonce' => wp_create_nonce('order-menuitems'),
            'max_menuitems_error' => $max_menuitems_error,
        );

        wp_localize_script('pl8app-otil-functions', 'pl8app_otil_vars', $params);
        wp_enqueue_script('pl8app-otil-functions');
    }


    /**
     * Check service type and disable time slots
     *
     * @since 1.3
     * @param bool $cond
     * @param string $service_type
     * @return bool
     */
    public function otil_service_type($cond, $service_type)
    {

        $otil_settings = new Order_Time_Interval_Limit_Settings();
        $otil_settings = $otil_settings->options;

        $disable_pickup_time = !empty($otil_settings['disable_pickup_time']) ? $otil_settings['disable_pickup_time'] : '';

        $disable_delivery_time = !empty($otil_settings['disable_delivery_time']) ? $otil_settings['disable_delivery_time'] : '';

        if ($service_type == 'delivery' && $disable_delivery_time) {
            $cond = false;
        }

        if ($service_type == 'pickup' && $disable_pickup_time) {
            $cond = false;
        }

        return $cond;

    }


    /**
     * Time interval
     *
     * @since 1.3
     * @return int
     */
    public function otil_store_time_interval()
    {

        $otil_settings = new Order_Time_Interval_Limit_Settings();
        $otil_settings = $otil_settings->options;

        $time_interval = !empty($otil_settings['time_interval_duration']) ? $otil_settings['time_interval_duration'] : 30;

        return $time_interval;
    }


    /**
     * Count number of orders already placed
     *
     * @since 1.3
     * @param string $date
     * @param string $time
     * @param string $service_type
     * @return int
     */
    public function get_orders_count($date, $time, $service_type)
    {
        $args = array(
            'post_type' => 'pl8app_payment',
            'post_status' => array('publish', 'processing'),
            'meta_query' => array(
                array(
                    'key' => '_pl8app_delivery_date',
                    'value' => $date
                )
            )
        );

        if ($time != '') {
            $args['meta_query'][] = array(
                'key' => '_pl8app_delivery_time',
                'value' => $time
            );
        }

        $args['meta_query'][] = array(
            'key' => '_pl8app_delivery_type',
            'value' => $service_type
        );


        $posts = new WP_Query($args);
        $count = $posts->found_posts;
        return $count;
    }


    /**
     * Show service message
     *
     * @since 1.3
     * @param string $service_type
     * @return string
     */
    public function display_service_type_message($service_type)
    {

        $otil_settings = new Order_Time_Interval_Limit_Settings();
        $otil_settings = $otil_settings->options;

        $disable_pickup_time = !empty($otil_settings['disable_pickup_time']) ? $otil_settings['disable_pickup_time'] : '';
        $disable_delivery_time = !empty($otil_settings['disable_delivery_time']) ? $otil_settings['disable_delivery_time'] : '';
        $service_message = '';

        if ($service_type == 'pickup' && !empty($disable_pickup_time)) {
            $service_message = !empty($otil_settings['pickup_message']) ? $otil_settings['pickup_message'] : '';
        }

        if ($service_type == 'delivery' && !empty($disable_delivery_time)) {
            $service_message = !empty($otil_settings['delivery_message']) ? $otil_settings['delivery_message'] : '';
        }
        echo $service_message;
    }


    /**
     * Show error message with order limit
     *
     * @since 1.3
     * @param string $valid_data
     * @param array $data
     * @return string
     */
    public function validate_order_limit($valid_data, $data)
    {
        $response = $this->otil_check_error($res = array());
        if (isset($response['status']) && $response['status'] == 'error') {
            pl8app_set_error('order_time_limit', $response['error_msg']);
        }
    }

    /**
     * Add the Action column for reorder action in Customer Order history page
     */
    public function customer_order_history_ext_label()
    {
        ?>
        <th class="pl8app_purchase_details"><?php _e('Action', 'pl8app'); ?></th>
        <?PHP
    }

    public function customer_order_history_ext_content($id, $meta)
    {

        ?>
        <td>
            <a href="#" class="button pl8app_reorder" data-payment-id="<?PHP echo $id; ?>">
                <span class="pl8app-add-to-cart-label"><?php echo __('<span class="dashicons dashicons-cart"></span> Reorder','pl8app');?></span>
            </a>
        </td>
        <?PHP

    }

    /**
     * Re add Order items to Cart
     */

    public function reorder_cart()
    {

        check_ajax_referer('order-menuitems', 'security', false);

        if (empty($_POST['payment_id'])) {
            return;
        }

        pl8app_empty_cart();

        $payment = new pl8app_Payment($_POST['payment_id']);

        $payment_meta = $payment->get_meta();
        $cart_details = isset($payment_meta['cart_details']) ? $payment_meta['cart_details'] : '';

        if (is_array($cart_details)) {
            foreach ($cart_details as $key => $item) {
                $options = array();
                $options['id'] = $item['id'];
                $options['price'] = $item['item_price'];
                $options['quantity'] = $item['quantity'];
                $options['instruction'] = $item['instruction'];
                $options['addon_items'] = $item['addon_items'];
                if (isset($options['addon_items']['price_id'])) unset($options['addon_items']['price_id']);
                if (isset($options['addon_items']['quantity'])) unset($options['addon_items']['quantity']);
                $options['price_id'] = isset($item['addon_items']['price_id']) ? $item['addon_items']['price_id'] : '';
                if (check_availability_menu_item_timing($item['id'])) pl8app_add_to_cart($item['id'], $options);
            }
        }

        $return = array(
            'status' => 'success',
        );

        wp_send_json($return);
        pl8app_die();
    }

}

new pl8app_Order_Time_intervals_Limit_Functions();
