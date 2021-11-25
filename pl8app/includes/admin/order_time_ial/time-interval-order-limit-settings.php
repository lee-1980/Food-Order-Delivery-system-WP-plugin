<?php
/**
 * Time_Interval_Order_Settings
 *
 * @package Time_Interval_Order_Settings
 * @since 1.0
 */

defined('ABSPATH') || exit;

class Order_Time_Interval_Limit_Settings
{

    public $options;

    public function __construct()
    {

        add_filter('pl8app_settings_general', array($this, 'order_time_interval_limits'), 1, 1);

//    add_filter( 'pl8app_settings_sections_general', array( $this, 'pl8app_add_otil_settings' ) );

        add_filter('pl8app_settings_general_sanitize', array($this, 'pl8app_settings_sanitize_pl8app_otil'), 10, 1);

        add_action('admin_enqueue_scripts', array($this, 'pl8app_tiaol_admin_styles'));

        $this->get_admin_fields();

        $this->options = $this->get_otil_settings();
    }


    /**
     * Set time interval in the general settings tab
     *
     * @param array links of settings array
     * @return array of settings link
     * @since 1.0
     */
    public function order_time_interval_limits($general_settings)
    {
        $order_time_interval_limits = array(
            'time_intervals' => array(
                'id' => 'order_time_interval_limits',
                'type' => 'order_time_interval_limits',
            ),
        );

        $order_time_interval_limits = apply_filters('pl8app_order_time_interval_limits', $order_time_interval_limits);
        $general_settings['order_time_interval_limits'] = $order_time_interval_limits;

        return $general_settings;
    }


    /**
     * Creates section for Time Interval in the admin
     *
     * @since  1.0
     * @param  array $section Array of section
     * @return array  array of links for the section
     */
    public function pl8app_add_otil_settings($section)
    {
        $section['order_time_interval_limits'] = __('Time Interval Order Limit', 'pl8app-otil');
        return $section;
    }


    /**
     *
     * Include required files for admin fields
     *
     * @since  1.0
     *
     */
    public function get_admin_fields()
    {
        require_once PL8_PLUGIN_DIR . 'includes/admin/order_time_ial/pl8app-time-interval-order-limit-fields.php';
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
            require PL8_PLUGIN_DIR . 'templates/order_time_ial/' . $template . '.php';
        }
    }


    /**
     * Get saved data from the database
     */
    public static function get_otil_settings()
    {
        $otil_settings = get_option('pl8app_otil', array());
        return apply_filters('pl8app_get_otil_setting', $otil_settings);
    }


    /**
     * Add necessary css file for the plugin admin section
     *
     * @since  1.0
     */
    public function pl8app_tiaol_admin_styles()
    {

        if (isset($_GET['page'])
            && $_GET['page'] == 'pl8app-settings'
            && isset($_GET['section'])
            && $_GET['section'] == 'order_time_interval_limits') {
            wp_register_style('pl8app-tiaol-admin-style', PL8_PLUGIN_URL . 'assets/css/pl8app-tioal-admin.css', array(), PL8_VERSION);
            wp_enqueue_style('pl8app-tiaol-admin-style');
        }

    }


    /**
     * Santize data
     *
     */
    public function pl8app_settings_sanitize_pl8app_otil($input)
    {
        if (!current_user_can('manage_shop_settings')) {
            return $input;
        }

        if (!isset($_POST['pl8app_otil'])) {
            return $input;
        }

        $otil_settings = !empty($_POST['pl8app_otil']) ? $_POST['pl8app_otil'] : array();

        if (isset($_POST['pl8app_otil']) && !empty($_POST['pl8app_otil'])) {

            $new_input = array();

            foreach ($_POST['pl8app_otil'] as $key => $val) {
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

        $otil_settings = get_option('pl8app_otil');
        if(!isset($otil_settings) || !is_array($otil_settings)) $otil_settings = array();
        $otil_settings = array_merge($otil_settings, $_POST['pl8app_otil']);
        update_option('pl8app_otil', $otil_settings);
        return $input;
    }

}

new Order_Time_Interval_Limit_Settings();