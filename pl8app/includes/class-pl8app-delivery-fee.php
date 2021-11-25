<?php
/**
 * pl8app_Delivery_Fee_Functions
 *
 * @package pl8app_Delivery_Fee_Functions
 * @since 1.0
 */

defined('ABSPATH') || exit;

class pl8app_Delivery_Fee
{

    public function __construct()
    {

        add_action('pl8app_before_service_time', array($this, 'render_zipcode_field'), 9);

        add_action('wp_enqueue_scripts', array($this, 'enqueue_style'));

        add_action('wp_enqueue_scripts', array($this, 'enqueue_delivery_fee_script'));

        add_filter('pl8app_check_service_slot', array($this, 'check_delivery_zone'));

        add_filter('pl8app_delivery_address', array($this, 'set_checkout_zip'), 10);

        add_action('pl8app_add_email_tags', array($this, 'set_delivery_fee_tag'), 11, 1);

        add_action('pl8app_add_email_tags', array($this, 'set_delivery_zone_tag'), 10, 1);

        add_filter('pl8app_proceed_checkout_page', array($this, 'pl8app_delivery_fee_check_error'), 10);

        add_filter('pl8app_apply_delivery_fee', array($this, 'enable_delivery_fee'));

        add_filter('pl8app_cart_data', array($this, 'add_fee_to_cart'));

        add_action('pl8app_payment_saved', array($this, 'update_payment_zip'), 10);

        add_action('pl8app_order_details_after', array($this, 'show_delivery_zone_details'));

        add_action('pl8app_cart_line_item', array($this, 'cart_line_item'));

        add_action('pl8app_checkout_error_checks', array($this, 'validate_delivery_on_checkout'), 10, 2);

        add_action('pl8app_purchase_form_before_order_details', array($this, 'pl8app_add_delivery_address'), 10);

        add_filter('pl8app_delivery_address', array($this, 'pl8app_fill_user_delivery_address'));

        add_filter('pl8app_proceed_checkout', array($this, 'pl8app_delivery_fee_validate_purchase'), 10);

        add_filter('pl8app_delivery_address_meta', array($this, 'pl8app_delivery_fee_custome_store_data'), 10);
    }


    /**
     * Adds the zip/postalcode field
     *
     * @since 1.0
     */
    public function render_zipcode_field($service_type)
    {

        //Add zip/postcode field on the delivery tab.
        if ($service_type != 'delivery') return;
        pl8app_Delivery_Fee_Settings::get_template_part('delivery-zone-field');
    }

    /**
     * Set delivery fee
     *
     * @return delivery fee
     * @since 1.0
     */
    public function get_delivery_fee()
    {

        if (empty($_COOKIE['service_type']) || $_COOKIE['service_type'] == 'pickup') return 0;

        $zip_code = isset($_COOKIE['delivery_zip']) ? $_COOKIE['delivery_zip'] : '';
        $delivery_latlng = isset($_COOKIE['delivery_latlng']) ? $_COOKIE['delivery_latlng'] : '';

        $delivery_settings = pl8app_Delivery_Fee_Settings::pl8app_fee_settings();

        $delivery_fee_method = isset($delivery_settings['delivery_method']) ? $delivery_settings['delivery_method'] : 'zip_based';

        $fee = 0;
        $cart_subtotal = pl8app_get_cart_subtotal();

        $free_delivery_amount = !empty($delivery_settings['free_delivery_amount']) ? $delivery_settings['free_delivery_amount'] : 0;

        if ($free_delivery_amount > 0 && $cart_subtotal > $free_delivery_amount) {
            PL8PRESS()->fees->remove_fee('delivery_fee');
            return 0;
        }

        if ('location_based' === $delivery_fee_method) {
            if (!empty($delivery_latlng)) {
                $response = $this->get_delivery_fee_by_location($delivery_latlng);
            }
        } else {
            $zip_code = isset($_COOKIE['delivery_zip']) ? $_COOKIE['delivery_zip'] : '';
            $response = $this->get_delivery_fee_by_zip($zip_code);

        }

        $fee = isset($response['fee']) ? $response['fee'] : 0;
        $min_order_amount = isset($response['min_order_amount']) ? $response['min_order_amount'] : 0;
        if ($cart_subtotal >= $min_order_amount) {
            $fee = 0;
        }

        if (isset($response['status'])
            && $response['status'] == 'success') {

            PL8PRESS()->fees->remove_fee('delivery_fee');

            if ($fee > 0) {
                PL8PRESS()->fees->add_fee($fee, __('Delivery Fee', 'pl8app-delivery-fee'), 'delivery_fee', 'all', false);
            }
        }

        return $fee;
    }

    /**
     * Add css style
     *
     * @since 1.0
     */
    public function enqueue_style()
    {

        wp_register_style('pl8app-delivery-fee', PL8_PLUGIN_URL . 'assets/css/pl8app-delivery-fee.css', array(), PL8_VERSION);

        wp_enqueue_style('pl8app-delivery-fee');
    }


    /**
     * Add js file
     *
     * @since 1.0
     */
    public function enqueue_delivery_fee_script()
    {

        $delivery_settings = pl8app_Delivery_Fee_Settings::pl8app_fee_settings();

        $google_map_api = isset($delivery_settings['google_map_api']) ? $delivery_settings['google_map_api'] : '';

        $delivery_fee_method = isset($delivery_settings['delivery_method']) ? $delivery_settings['delivery_method'] : 'zip_based';

        wp_register_script('pl8app-delivery-fee', PL8_PLUGIN_URL . 'assets/js/pl8app-delivery-fee.js', array('jquery'), PL8_VERSION);

        wp_register_script('pl8app-google-map-api', "https://maps.googleapis.com/maps/api/js?key=$google_map_api&libraries=places", array(), '', true);

        wp_enqueue_script('pl8app-delivery-fee');

        if ($delivery_fee_method == 'location_based') {
            wp_enqueue_script('pl8app-google-map-api');
        }

        $pl8app_settings = get_option('pl8app_settings', true);
        $store_country = isset($pl8app_settings['base_country']) ? $pl8app_settings['base_country'] : '';

        $params = array(
            'fee' => __('Delivery Fee', 'pl8app-delivery-fee'),
            'delivery_fee_method' => $delivery_fee_method,
            'store_country' => $store_country,
        );

        wp_localize_script('pl8app-delivery-fee', 'DeliveryFeeVars', $params);
    }

    /**
     * Set zipcode on checkout page
     *
     * @since 1.5
     */
    public function set_checkout_zip($address)
    {
        //If cookie is set for delivery zip then use it for the zip on checkout
        if (!empty($_COOKIE['delivery_zip']))
            $address['postcode'] = $_COOKIE['delivery_zip'];
        return $address;
    }


    /**
     * Check whether delivery fee is applied on the selected zone
     * @param $postdata $_POST Data
     * @return json
     * @since 1.0
     */
    public function check_delivery_zone($postdata)
    {

        $delivery_zip_code = isset($postdata['delivery_zip']) ? sanitize_text_field($postdata['delivery_zip']) : '';
        $service_type = isset($postdata['serviceType']) ? sanitize_text_field($postdata['serviceType']) : '';
        $delivery_latlng = isset($postdata['delivery_latlng']) ? (trim($postdata['delivery_latlng'])) : '';
        $delivery_location = isset($postdata['delivery_location']) ? (trim($postdata['delivery_location'])) : '';
        $cart_subtotal = pl8app_get_cart_subtotal();

        $response = array();

        $delivery_settings = pl8app_Delivery_Fee_Settings::pl8app_fee_settings();
        $delivery_fee_method = isset($delivery_settings['delivery_method']) ? $delivery_settings['delivery_method'] : 'zip_based';

        if ('delivery' == $service_type) {

            if ($delivery_fee_method == 'zip_based') {
                $response = $this->get_delivery_fee_by_zip($delivery_zip_code);
            } else {

                if (empty($delivery_location)) {
                    $response['fee'] = 0;
                    $response['status'] = 'error';
                    $response['msg'] = __('Please enter your location', 'pl8app-delivery-fee');
                } else {
                    $response = $this->get_delivery_fee_by_location($delivery_latlng);
                }


            }

            if (empty($delivery_zip_code)) {
                setcookie('service_type', '', time() + (60 * 60), "/", '', false, false);
            }

            if ($response['status'] == 'error') {
                setcookie('service_type', '', time() + (60 * 60), "/", '', false, false);
            }

            if ($response['status'] == 'success') {

                $min_order_amount = isset($response['min_order_amount']) ? $response['min_order_amount'] : 0;
                if ($min_order_amount > 0 && $cart_subtotal > $min_order_amount) {
                    PL8PRESS()->fees->remove_fee('delivery_fee');
                    $data = $this->pl8app_update_cart_fee(0);
                } else {
                    $data = $this->pl8app_update_cart_fee($response['fee']);
                }

                if (is_array($data) && isset($data['fee'])) {

                    $fee = html_entity_decode(pl8app_currency_filter(pl8app_format_amount($data['fee'])), ENT_COMPAT, 'UTF-8');
                    $response['fee'] = $data['fee'];
                    $response['delivery_fee'] = $fee;
                    $response['total'] = $data['cart_total'];
                    $response['subtotal'] = $data['cart_subtotal'];
                    $response['service'] = 'delivery';
                }
            }

            //Set cookie for zip code
            unset($_COOKIE['delivery_zip']);
            setcookie("delivery_zip", '', time() - 300, "/");
            setcookie('delivery_zip', $delivery_zip_code, time() + (60 * 60), "/");

            //set cookie for location lat lng
            unset($_COOKIE['delivery_latlng']);
            setcookie("delivery_latlng", '', time() - 300, "/");
            setcookie('delivery_latlng', $delivery_latlng, time() + (60 * 60), "/");

            unset($_COOKIE['delivery_location']);
            setcookie("delivery_location", '', time() - 300, "/");
            setcookie('delivery_location', $delivery_location, time() + (60 * 60), "/");

        } else {
            $data = $this->pl8app_update_cart_fee(0);
            $response['status'] = 'success';
            $response['msg'] = '';
            $response['fee'] = 0;
            $response['total'] = isset($data['cart_total'])?$data['cart_total']: 0;
            $response['subtotal'] = isset($data['cart_subtotal'])?$data['cart_subtotal']:0;
        }
        return $response;
    }


    /**
     * Update cart with fee
     *
     * @param fee
     * @return array
     * @since 1.5
     */
    public function pl8app_update_cart_fee($fee)
    {

        $cart_quantity = pl8app_get_cart_quantity();

        if ($cart_quantity > 0) {

            PL8PRESS()->fees->remove_fee('delivery_fee');

            if ($fee > 0) {
                PL8PRESS()->fees->add_fee($fee, __('Delivery Fee', 'pl8app-delivery-fee'), 'delivery_fee', 'all', false);
            }

            return $response = array(
                'fee' => $fee,
                'cart_subtotal' => html_entity_decode(pl8app_currency_filter(pl8app_format_amount(pl8app_get_cart_subtotal())), ENT_COMPAT, 'UTF-8'),
                'cart_total' => html_entity_decode(pl8app_currency_filter(pl8app_format_amount(pl8app_get_cart_total())), ENT_COMPAT, 'UTF-8'),
            );
        }

    }


    /**
     * Get delivery fee based on the latlng
     *
     * @return array delivery response
     * @since 2.0
     */
    public function get_delivery_fee_by_location($delivery_latlng)
    {

        $response = array();

        if (empty($delivery_latlng)) {
            $response['fee'] = 0;
            $response['status'] = 'error';
            $response['msg'] = __('Please enter your location', 'pl8app-delivery-fee');
            return $response;
        }


        $delivery_settings = pl8app_Delivery_Fee_Settings::pl8app_fee_settings();
        $distance_unit = isset($delivery_settings['distance_unit']) ? $delivery_settings['distance_unit'] : 'km';

        $store_position = isset($delivery_settings['store_latlng']) ? $delivery_settings['store_latlng'] : '';

        $store_position = explode(',', $store_position);

        $store_lat = isset($store_position[0]) ? $store_position[0] : '';
        $store_lng = isset($store_position[1]) ? $store_position[1] : '';
        $response = array();

        $delivery_latlng = trim($delivery_latlng);
        $delivery_latlng = explode(',', $delivery_latlng);
        $delivery_pos_lat = isset($delivery_latlng[0]) ? trim($delivery_latlng[0]) : '';
        $delivery_pos_lng = isset($delivery_latlng[1]) ? trim($delivery_latlng[1]) : '';

        $distance = $this->get_distance_by_latlng($store_lat, $store_lng, $delivery_pos_lat, $delivery_pos_lng, $distance_unit);

        $response = self::get_fee_by_matching_distance($distance);

        return $response;
    }


    /**
     * Get matching fee based on the distance range limit
     *
     * @return array
     * @since 2.0
     */
    public static function get_fee_by_matching_distance($distance)
    {

        $delivery_settings = pl8app_Delivery_Fee_Settings::pl8app_fee_settings();
        $delivery_locations = $delivery_settings['delivery_location_fee'];
        $error_message = isset($delivery_settings['unavailable_message']) ? stripslashes($delivery_settings['unavailable_message']) : 'Sorry we don\'t deliver here';
        $service_type = isset($_COOKIE['service_type']) ? $_COOKIE['service_type'] : '';
        $cart_subtotal = pl8app_get_cart_subtotal();

        $response = array();

        $response['status'] = 'error';
        $response['msg'] = $error_message;
        $free_delivery_amount = isset($delivery_settings['free_delivery_amount']) ? $delivery_settings['free_delivery_amount'] : 0;

        if (is_array($delivery_locations) && !empty($delivery_locations)) {

            foreach ($delivery_locations as $key => $delivery_location) {

                $distance_limit = isset($delivery_location['distance']) ? $delivery_location['distance'] : '';

                $min_order_amount = !empty($delivery_location['order_amount']) ? $delivery_location['order_amount'] : $free_delivery_amount;

                $set_min_order_amount = !empty($delivery_location['set_min_order_amount']) ? $delivery_location['set_min_order_amount'] : 0;

                if (!empty($distance_limit)) {

                    $distance_limit = explode('-', $distance_limit);

                    $distance_from = isset($distance_limit[0]) ? $distance_limit[0] : 0;
                    $distance_to = isset($distance_limit[1]) ? $distance_limit[1] : 0;

                    if ((($distance == $distance_from)
                        || (($distance > $distance_from) && ($distance < $distance_to)))) {
                        $response['status'] = 'success';
                        $response['fee'] = $delivery_locations[$key]['fee_amount'];
                        $response['msg'] = '';
                        $response['min_order_amount'] = $min_order_amount;
                        $response['set_min_order_amount'] = $set_min_order_amount;
                    }
                }

            }
        }
        return $response;
    }


    /**
     * Calculate distance between source and target lat,lng values
     *
     * @return string
     * @since 2.0
     */
    public function get_distance_by_latlng($source_lat, $source_lon, $target_lat, $target_lon, $unit)
    {

        $source_lat = floatval($source_lat);
        $source_lon = floatval($source_lon);

        $target_lat = floatval($target_lat);
        $target_lon = floatval($target_lon);


        if (($source_lat == $target_lat) && ($source_lon == $target_lon)) {
            return 0;
        } else {
            $theta = $source_lon - $target_lon;
            $dist = sin(deg2rad($source_lat)) * sin(deg2rad($target_lat)) + cos(deg2rad($source_lat)) * cos(deg2rad($target_lat)) * cos(deg2rad($theta));
            $dist = acos($dist);
            $dist = rad2deg($dist);
            $miles = $dist * 60 * 1.1515;

            if ($unit == "km") {
                return ($miles * 1.609344);
            } else {
                return $miles;
            }
        }
    }


    /**
     * Get delivery fee based on the zone
     *
     * @return array delivery response
     * @since 1.0
     */
    public function get_delivery_fee_by_zip($delivery_zip_code)
    {

        $delivery_settings = pl8app_Delivery_Fee_Settings::pl8app_fee_settings();

        $response = array();

        $response['fee'] = 0;
        $response['status'] = 'error';
        $response['msg'] = '';


        $delivery_zip_code = trim($delivery_zip_code);
        $cart_subtotal = pl8app_get_cart_subtotal();
        $tax = pl8app_get_cart_tax();
        $new_cart_subtotal = $cart_subtotal + $tax;
        $free_delivery_amount = isset($delivery_settings['free_delivery_amount']) ? $delivery_settings['free_delivery_amount'] : 0;

        if (!isset($_COOKIE['delivery_zip'])) {
            setcookie('delivery_zip', $delivery_zip_code, time() + (60 * 10), "/");
        }

        if (empty($delivery_zip_code)) {
            $response['msg'] = __('Please enter your zip/postal code', 'pl8app-delivery-fee');
            return $response;
        }

        if (isset($delivery_settings['delivery_fee'])) {

            $response['msg'] = !empty($delivery_settings['unavailable_message']) ? stripslashes($delivery_settings['unavailable_message']) : esc_html('Sorry! we don\'t deliver to this zip/postal code', 'pl8app-delivery-fee');

            $delivery_zones = $delivery_settings['delivery_fee'];


            foreach ($delivery_zones as $key => $delivery_zone) {
                $zip_code = !empty($delivery_zone['zip_code']) ? $delivery_zone['zip_code'] : '';
                $min_order_amount = !empty($delivery_zone['order_amount']) ? $delivery_zone['order_amount'] : $free_delivery_amount;
                $set_min_order_amount = !empty($delivery_zone['set_min_order_amount']) ? $delivery_zone['set_min_order_amount'] : 0;

                if (!empty($zip_code)) {
                    $zip_code = array_map('trim', explode(',', strtolower($zip_code)));

                    $delivery_zip_code = strtolower($delivery_zip_code);

                    if (in_array(strtolower($delivery_zip_code), $zip_code)) {
                        $response['status'] = 'success';
                        $fee = $delivery_zones[$key]['fee_amount'];
                        $response['status'] = 'success';
                        $response['fee'] = $fee;
                        $response['msg'] = '';
                        $response['min_order_amount'] = $min_order_amount;
                        $response['set_min_order_amount'] = $set_min_order_amount;

                        break;
                    } else {
                        foreach ($zip_code as $k => $zip) {
                            if (strpos($zip, "*") !== false) {
                                $delivery_zip_code_array = array_map('trim', explode(' ', strtolower($delivery_zip_code)));
                                if (substr($zip, 0, -1) == $delivery_zip_code_array[0]) {

                                    $response['status'] = 'success';
                                    $response['status'] = 'success';
                                    $response['fee'] = $delivery_zones[$key]['fee_amount'];
                                    $response['msg'] = '';
                                    $response['min_order_amount'] = $min_order_amount;
                                    $response['set_min_order_amount'] = $set_min_order_amount;

                                    break 2;
                                }
                            }
                        }
                    }
                }
            }
        }

        return $response;

    }


    /**
     * Email tag for the delivery fee
     *
     * @return mixed
     * @since 1.0
     */
    public function set_delivery_fee_tag()
    {
        $email_tag = 'delivery_fee';
        $tag_description = esc_html('Delivery fee for the order', 'pl8app-delivery-fee');

        pl8app_add_email_tag($email_tag, $tag_description, array($this, 'pl8app_delivery_fee_tag'));
    }

    /**
     * Email tag for the delivery fee
     *
     * @return mixed
     * @since 1.0
     */
    public function set_delivery_zone_tag()
    {
        $email_tag = 'delivery_zone';
        $tag_description = esc_html('Customer\'s zip for the order', 'pl8app-delivery-fee');

        pl8app_add_email_tag($email_tag, $tag_description, array($this, 'delivery_zip_tag'));
    }


    /**
     * Set email tag for delivery fee
     *
     * @return delivery fee
     * @since 1.0
     */
    public function pl8app_delivery_fee_tag($payment_id)
    {

        $delivery_fee_amount = 0;

        if ($payment_id) {
            $delivery_fee = get_post_meta($payment_id, '_pl8app_payment_meta', true);

            if (isset($delivery_fee['fees']['delivery_fee'])) {
                $delivery_fee_amount = isset($delivery_fee['fees']['delivery_fee']['amount']) ? $delivery_fee['fees']['delivery_fee']['amount'] : 0;
                $delivery_fee_amount = html_entity_decode(pl8app_currency_filter(pl8app_format_amount($delivery_fee_amount)), ENT_COMPAT, 'UTF-8');
            }

        }

        return $delivery_fee_amount;
    }

    /**
     * Set delivery zip tag for delivery fee
     *
     * @return delivery zip
     * @since 1.0
     */
    public function delivery_zip_tag($payment_id)
    {

        if ($payment_id) {
            $delivery_zone = get_post_meta($payment_id, '_pl8app_delivery_zip', true);
            return $delivery_zone;

        }
    }


    /**
     * Check delivery fee is enabled or not
     *
     * @return bool
     * @since 1.0
     */
    public function enable_delivery_fee()
    {
        $delivery_settings = pl8app_Delivery_Fee_Settings::pl8app_fee_settings();
        $cart_subtotal = pl8app_get_cart_subtotal();
        $free_delivery_amount = isset($delivery_settings['free_delivery_amount']) ? $delivery_settings['free_delivery_amount'] : '';

        $cond = false;

        if ($cart_subtotal < $free_delivery_amount) {
            $cond = true;
        }

        return $cond;
    }


    /**
     * Get delivery fee response
     *
     * @return array
     * @since 1.5
     */
    public function pl8app_delivery_fee_check_error($response)
    {

        $delivery_settings = pl8app_Delivery_Fee_Settings::pl8app_fee_settings();

        $cart_total = pl8app_get_cart_subtotal();

        $min_order_amount = !empty($delivery_settings['free_delivery_amount']) ? $delivery_settings['free_delivery_amount'] : '';

        if ($cart_total > $min_order_amount) {
            PL8PRESS()->fees->remove_fee('delivery_fee');
        }

        $response = array('status' => 'success');

        return $response;
    }


    /**
     * Update the cookie once pyamnet has been done
     *
     * @return mixed
     * @since 1.5
     */
    public function update_payment_zip($payment_id)
    {

        if (!empty($payment_id)) {
            $get_delivery_zone = isset($_COOKIE['delivery_zip']) ? $_COOKIE['delivery_zip'] : '';

            if (!empty($get_delivery_zone)) {

                update_post_meta($payment_id, '_pl8app_delivery_zip', $get_delivery_zone);

                unset($_COOKIE['delivery_zip']);
                setcookie("delivery_zip", "", time() - 300, "/");
            }

            if (isset($_COOKIE['delivery_latlng']) && !empty($_COOKIE['delivery_latlng'])) {
                //remove latlng cookie
                unset($_COOKIE['delivery_latlng']);
                setcookie("delivery_latlng", "", time() - 300, "/");
            }

            if (isset($_COOKIE['city']) && !empty($_COOKIE['city'])) {
                //remove city cookie
                unset($_COOKIE['city']);
                setcookie("city", "", time() - 300, "/");
            }

            if (isset($_COOKIE['delivery_location']) && !empty($_COOKIE['delivery_location'])) {
                //remove delivery_location cookie
                unset($_COOKIE['delivery_location']);
                setcookie("delivery_location", "", time() - 300, "/");
            }

            if (isset($_COOKIE['street_address']) && !empty($_COOKIE['street_address'])) {
                //remove street_address cookie
                unset($_COOKIE['street_address']);
                setcookie("street_address", "", time() - 300, "/");
            }

        }
    }


    /**
     * Show delivery zip in the order details
     *
     * @return string
     * @since 1.5
     */
    public function show_delivery_zone_details($payment_id)
    {
        $delivery_zip = get_post_meta($payment_id, '_pl8app_delivery_zip', true);
        if (!empty($delivery_zip)) :
            ?>
            <div class="pl8app-delivery-details pl8app-admin-box-inside">
                <p>
        <span class="label">
          <?php esc_html_e('Delivery zip/postal code', 'pl8app-delivery-fee'); ?> :
        </span>
                    <?php echo $delivery_zip; ?>
                </p>
            </div>
        <?php
        endif;
    }

    /**
     * Add delivery fee line item
     *
     * @return string
     * @since 1.5
     */
    public function cart_line_item()
    {
        $delivery_fee = $this->get_delivery_fee();
        $style = $delivery_fee > 0 ? '' : 'style="display:none;"';
        $line_item = '<li class="cart_item pl8app-cart-meta pl8app_delivery_fee" ' . $style . ' >' . __('Delivery Fee:', 'pl8app-delivery-fee') . '<span class="pl8app-delivery-fee">';
        $line_item .= pl8app_currency_filter(pl8app_format_amount($delivery_fee));
        $line_item .= '</span></li>';
        echo $line_item;
    }

    /**
     * get response when add to cart
     *
     * @return array
     * @since 1.5
     */
    public function add_fee_to_cart($data)
    {

        $delivery_fee = 0;
        $subtotal = pl8app_get_cart_subtotal();
        $tax = pl8app_get_cart_tax();
        $subtotal = $subtotal + $tax;

        if (empty($_COOKIE['service_type']) || $_COOKIE['service_type'] == 'delivery') {

            $delivery_settings = pl8app_Delivery_Fee_Settings::pl8app_fee_settings();
            $delivery_fee_method = isset($delivery_settings['delivery_method']) ? $delivery_settings['delivery_method'] : 'zip_based';

            if ('zip_based' === $delivery_fee_method) {

                $zip_code = isset($_COOKIE['delivery_zip']) ? $_COOKIE['delivery_zip'] : '';
                $response = $this->get_delivery_fee_by_zip($zip_code);
                $delivery_fee = $response['fee'];
                $min_order_amount = isset($response['min_order_amount']) ? $response['min_order_amount'] : 0;

                if ($subtotal >= $min_order_amount) {
                    $delivery_fee = 0;
                }
            } else {

                $delivery_latlng = isset($_COOKIE['delivery_latlng']) ? $_COOKIE['delivery_latlng'] : '';
                $response = $this->get_delivery_fee_by_location($delivery_latlng);
                $min_order_amount = isset($response['min_order_amount']) ? $response['min_order_amount'] : 0;
                $delivery_fee = $response['fee'];

                if ($subtotal >= $min_order_amount) {
                    $delivery_fee = 0;
                }

            }
        }

        $fees = $this->pl8app_update_cart_fee($delivery_fee);

        //Add delivery fee to the cart
        $data['fee'] = $delivery_fee;
        $data['delivery_fee'] = html_entity_decode(pl8app_currency_filter(pl8app_format_amount($delivery_fee)), ENT_COMPAT, 'UTF-8');
        $data['total'] = $fees['cart_total'];

        return $data;
    }

    /**
     * Validate delivery zip code
     *
     * @return mixed
     * @since 1.5
     */
    public function validate_delivery_on_checkout($valid_data, $data)
    {
        $delivery_settings = pl8app_Delivery_Fee_Settings::pl8app_fee_settings();
        $delivery_fee_method = isset($delivery_settings['delivery_method']) ? $delivery_settings['delivery_method'] : 'zip_based';
        $cart_subtotal = pl8app_get_cart_subtotal();

        $free_delivery_amount = !empty($delivery_settings['free_delivery_amount']) ? $delivery_settings['free_delivery_amount'] : 0;

        $service_type = isset($_COOKIE['service_type']) ? $_COOKIE['service_type'] : '';


        //Check the delivery fee method
        if ($delivery_fee_method == 'zip_based') {

            if (!empty($data['pl8app_postcode'])) {

                setcookie('delivery_zip', $data['pl8app_postcode'], time() + (60 * 60), "/");

                $response = $this->get_delivery_fee_by_zip($data['pl8app_postcode']);

                if ($response['status'] == 'error') {
                    pl8app_set_error('invalid_service_location', $response['msg']);
                    $this->pl8app_update_cart_fee(0);
                } else {
                    $min_order_amount = isset($response['min_order_amount']) ? $response['min_order_amount'] : 0;
                    if ($min_order_amount > 0 && $cart_subtotal > $min_order_amount) {
                        PL8PRESS()->fees->remove_fee('delivery_fee');
                    } else {
                        $this->pl8app_update_cart_fee($response['fee']);
                    }

                }
            }

        } else {

            $error_message = isset($delivery_settings['unavailable_message']) ? stripslashes($delivery_settings['unavailable_message']) : 'Sorry we don\'t deliver here ';
            $delivery_latlng = isset($_COOKIE['delivery_latlng']) ? $_COOKIE['delivery_latlng'] : '';

            if (!empty($delivery_latlng)) {
                $response = $this->get_delivery_fee_by_location($delivery_latlng);

                if ($response['status'] == 'error') {
                    pl8app_set_error('invalid_service_location', $response['msg']);
                    $this->pl8app_update_cart_fee(0);
                } else {
                    $min_order_amount = isset($response['min_order_amount']) ? $response['min_order_amount'] : 0;
                    if ($min_order_amount > 0 && $cart_subtotal > $min_order_amount) {
                        PL8PRESS()->fees->remove_fee('delivery_fee');
                    } else {
                        $this->pl8app_update_cart_fee($response['fee']);
                    }
                }
            } else {
                if ($service_type == 'delivery') {
                    pl8app_set_error('invalid_service_location', $error_message);
                }

            }
        }

    }


    /**
     * Set address input for users to set their location
     *
     * @return html
     * @since 2.0
     */
    public function pl8app_add_delivery_address()
    {
        $service_type = isset($_COOKIE['service_type']) ? $_COOKIE['service_type'] : '';

        if ($service_type == 'delivery') :
            $delivery_settings = pl8app_Delivery_Fee_Settings::pl8app_fee_settings();
            $delivery_fee_method = isset($delivery_settings['delivery_method']) ? $delivery_settings['delivery_method'] : 'zip_based';

            if ($delivery_fee_method == 'location_based') :
                $delivery_location = isset($_COOKIE['delivery_location']) ? $_COOKIE['delivery_location'] : '';
                ?>
                <p id="pl8app-delivery-address-delivery-fee" class="pl8app-col-md-6 pl8app-col-sm-12">
                    <label class="pl8app-delivery-address" for="pl8app-delivery-address">
                        <?php esc_html_e('Address', 'pl8app-delivery-fee'); ?>
                    </label>
                    <input class="pl8app-input" type="text" name="pl8app_delivery_address" id="pl8app-delivery-address"
                           placeholder="<?php esc_html_e('Address', 'pl8app-delivery-fee'); ?>"
                           value="<?php echo $delivery_location; ?>"/>
                </p>
            <?php
            endif;
        endif;
    }


    /**
     * Prefill user address besed on their address cokie settings
     *
     * @param delivery address array
     * @return array
     * @since 2.0
     */
    public function pl8app_fill_user_delivery_address($delivery_address)
    {
        $delivery_settings = pl8app_Delivery_Fee_Settings::pl8app_fee_settings();
        $delivery_fee_method = isset($delivery_settings['delivery_method']) ? $delivery_settings['delivery_method'] : 'zip_based';

        $zip_code = isset($_COOKIE['delivery_zip']) ? $_COOKIE['delivery_zip'] : '';
        $street_address = isset($_COOKIE['street_address']) ? $_COOKIE['street_address'] : '';
        $city = isset($_COOKIE['city']) ? $_COOKIE['city'] : '';
        $flat = isset($_COOKIE['flat']) ? $_COOKIE['flat'] : '';
        if ($delivery_fee_method == 'location_based') {
            if (is_array($delivery_address)) {
                $delivery_address['postcode'] = $zip_code;
                $delivery_address['city'] = $city;
                $delivery_address['address'] = $street_address;
                $delivery_address['flat'] = $flat;
            }
        }

        return $delivery_address;
    }

    /**
     * Check error  before proceed  checkout
     * @param $response Object
     * @return $response Object
     * @since 2.3
     */
    public function pl8app_delivery_fee_validate_purchase($response)
    {


        $service_type = isset($_COOKIE['service_type']) ? $_COOKIE['service_type'] : 'pickup';

        if ($service_type == 'pickup') {
            return $response;
        }

        $delivery_settings = pl8app_Delivery_Fee_Settings::pl8app_fee_settings();

        $minimum_order_amount_error = isset($delivery_settings['minimum_order_amount_error']) ? $delivery_settings['minimum_order_amount_error'] : __('Minimum order amount for this address is') . '{minimum_order_amount}';

        $delivery_zip_code = isset($_COOKIE['delivery_zip']) ? $_COOKIE['delivery_zip'] : '';
        $delivery_latlng = isset($_COOKIE['delivery_latlng']) ? (trim($_COOKIE['delivery_latlng'])) : '';
        $delivery_location = isset($_COOKIE['delivery_location']) ? (trim($_COOKIE['delivery_location'])) : '';

        $delivery_fee_method = isset($delivery_settings['delivery_method']) ? $delivery_settings['delivery_method'] : 'zip_based';

        $cart_subtotal = pl8app_get_cart_subtotal();
        $tax = pl8app_get_cart_tax();
        $new_cart_subtotal = $cart_subtotal + $tax;


        if ($delivery_fee_method == 'zip_based') {

            if (!empty($delivery_zip_code)) {
                $delivery_response = $this->get_delivery_fee_by_zip($delivery_zip_code);
            }

        } else {

            if (!empty($delivery_latlng)) {
                $delivery_response = $this->get_delivery_fee_by_location($delivery_latlng);
            }

        }
        // $min_order_amount = isset( $delivery_response['min_order_amount'] ) ? $delivery_response['min_order_amount'] : 0;
        $set_min_order_amount = isset($delivery_response['set_min_order_amount']) ? $delivery_response['set_min_order_amount'] : 0;

        if ($set_min_order_amount != 0 && $set_min_order_amount > 0 && $new_cart_subtotal < $set_min_order_amount) {
            $min_order_amount_message = str_replace('{minimum_order_amount}', pl8app_currency_filter(pl8app_format_amount($set_min_order_amount)), $minimum_order_amount_error);
            $response['status'] = 'error';
            $response['error_msg'] = $min_order_amount_message;
        }

        return $response;
    }

    /**
     * Adding custom form value to meta data
     * @param $delivery_address_meta array
     * @return $delivery_address_meta array
     * @since 2.6
     */
    public function pl8app_delivery_fee_custome_store_data($delivery_address_meta)
    {

        $delivery_address_meta['delivery_fee_address'] = !empty($_POST['pl8app_delivery_address']) ? sanitize_text_field($_POST['pl8app_delivery_address']) : '';
        return $delivery_address_meta;

    }

}

new pl8app_Delivery_Fee();