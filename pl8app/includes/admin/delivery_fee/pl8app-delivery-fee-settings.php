<?php
/**
 * pl8app_Delivery_Fee_Settings
 *
 * @package pl8app_Delivery_Fee_Settings
 * @since 1.0.0
 */

defined('ABSPATH') || exit;

class pl8app_Delivery_Fee_Settings
{

    public function __construct()
    {

        add_filter('pl8app_settings_general', array($this, 'pl8app_add_delivery_settings'), 1, 1);

//    add_filter( 'pl8app_settings_sections_general', array( $this, 'pl8app_add_delivery_fee_settings' ) );

        add_action('admin_enqueue_scripts', array($this, 'pl8app_delivery_fee_admin_styles'));

        add_action('admin_enqueue_scripts', array($this, 'pl8app_delivery_fee_admin_script'));

        add_filter('pl8app_settings_general_sanitize', array($this, 'pl8app_settings_sanitize_delivery_fee'), 10, 1);

        $this->get_admin_fields();

    }

    /**
     * @since 1.0.0
     * @param $general_settings array
     * @return array $general_settings
     */
    public function pl8app_add_delivery_settings($general_settings)
    {

        $delivery_fee_settings = array(
            'delivery_fees' => array(
                'id' => 'delivery_fee',
                'type' => 'delivery_fee',
            ),
        );

        $general_settings['delivery_fee'] = $delivery_fee_settings;

        return $general_settings;
    }

    /**
     * @since 1.0.0
     * @param $data array
     * @return array $data
     */
    public function pl8app_add_delivery_fee_settings($data)
    {
        $data['delivery_fee'] = __('Delivery Fee', 'pl8app-delivery-fee');
        return $data;
    }

    /**
     * Include template file
     *
     * @since  1.0
     * @param  string file name which would be included
     */
    public static function get_template_part($template, $data = '')
    {
        if (!empty($template)) {
            require PL8_PLUGIN_DIR . 'templates/delivery_fee/' . $template . '.php';
        }
    }


    /**
     *
     * Include required files for admin fields
     *
     */
    public function get_admin_fields()
    {
        require_once PL8_PLUGIN_DIR . 'includes/admin/delivery_fee/pl8app-delivery-fee-fields.php';
    }

    /**
     * Add necessary css file for the plugin admin section
     *
     * @since  1.0
     */
    public function pl8app_delivery_fee_admin_styles()
    {

        if ((isset($_GET['page'])
                && $_GET['page'] == 'pl8app-settings'
                && isset($_GET['section'])
                && $_GET['section'] == 'delivery_fee') || (isset($_GET['page']) && $_GET['page'] == 'pl8app-delivery-settings')) {
            wp_register_style('pl8app-admin-delivery-fee', PL8_PLUGIN_URL . 'assets/css/pl8app-admin-delivery-fee.css', array(), PL8_VERSION);
            wp_enqueue_style('pl8app-admin-delivery-fee');
        }

    }


    /**
     * Add necessary js file for the plugin admin section
     *
     * @since  1.0
     */
    public function pl8app_delivery_fee_admin_script()
    {
        if ((isset($_GET['page'])
                && $_GET['page'] == 'pl8app-settings'
                && isset($_GET['section'])
                && $_GET['section'] == 'delivery_fee') || (isset($_GET['page']) && $_GET['page'] == 'pl8app-delivery-settings')) {

            $delivery_settings = pl8app_Delivery_Fee_Settings::pl8app_fee_settings();

            $key = isset($delivery_settings['google_map_api']) ? $delivery_settings['google_map_api'] : '';

            wp_register_script('pl8app-admin-gmap-api', "https://maps.googleapis.com/maps/api/js?key=$key&libraries=places&callback=initMap", array(), '', true);
            wp_enqueue_script('pl8app-admin-gmap-api');

            wp_register_script('pl8app-admin-delivery-fee', PL8_PLUGIN_URL . 'assets/js/pl8app-admin-delivery-fee.js', array('jquery'), PL8_VERSION);
            wp_enqueue_script('pl8app-admin-delivery-fee');

            $store_location = isset($delivery_settings['store_location']) ? $delivery_settings['store_location'] : 'London, UK';

            $store_latlng = isset($delivery_settings['store_latlng']) ? $delivery_settings['store_latlng'] : '51.5285582,-0.2416802';

            $store_latlng = explode(',', $store_latlng);

            $store_lat_position = isset($store_latlng[0]) ? $store_latlng[0] : '51.5285582';

            $store_lng_position = isset($store_latlng[1]) ? $store_latlng[1] : '-0.2416802';

            $delivery_fee_method = isset($delivery_settings['delivery_method']) ? $delivery_settings['delivery_method'] : 'zip_based';

            $params = array(
                'delivery_fee_method' => $delivery_fee_method,
                'distance_example' => __('eg: 5-20', 'pl8app-delivery-fee'),
                'remove' => __('Remove', 'pl8app-delivery-fee'),
                'store_location' => $store_location,
                'store_lat_position' => $store_lat_position,
                'store_lng_position' => $store_lng_position,
            );

            wp_localize_script('pl8app-admin-delivery-fee', '_rpDeliveryFee', $params);

        }
    }


    /**
     * Sanitize data before saving
     *
     * @since  1.0
     */
    public function pl8app_settings_sanitize_delivery_fee($input)
    {

        if (!current_user_can('manage_shop_settings')) {
            return $input;
        }

        if (!isset($_POST['pl8app_delivery_fee'])) {
            return $input;
        }

        $pl8app_delivery_fee = !empty($_POST['pl8app_delivery_fee']) ? $_POST['pl8app_delivery_fee'] : array();

        if (isset($_POST['pl8app_delivery_fee']) && !empty($_POST['pl8app_delivery_fee'])) {

            $new_input = array();

            foreach ($_POST['pl8app_delivery_fee'] as $key => $val) {
                if (is_array($val) && !empty($val)) {
                    $new_data = array();
                    foreach ($val as $data) {
                        $new_data[] = !empty($data) ? sanitize_text_field($data) : '';
                    }
                    $new_input[$key] = $new_data;
                } else {
                    $new_input[$key] = (isset($key)) ? sanitize_text_field($val) : '';
                }
            }
        }

        $pl8app_old_delivery_fee = get_option('pl8app_delivery_fee');
        if(!isset($pl8app_old_delivery_fee) || !is_array($pl8app_old_delivery_fee)) $pl8app_old_delivery_fee = array();
        $pl8app_new_delivery_fee = array_merge($pl8app_old_delivery_fee, $_POST['pl8app_delivery_fee']);
        update_option('pl8app_delivery_fee', $pl8app_new_delivery_fee);
        return $input;
    }


    /**
     * Get data from the database
     *
     * @since  1.0
     */
    public static function pl8app_fee_settings()
    {
        $delivery_fee_settings = get_option('pl8app_delivery_fee', array());
        return apply_filters('pl8app_get_delivery_fee_setting', $delivery_fee_settings);
    }


}

new pl8app_Delivery_Fee_Settings();