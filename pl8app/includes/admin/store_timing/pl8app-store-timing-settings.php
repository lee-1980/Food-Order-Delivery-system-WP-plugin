<?php
/**
 * pl8app_StoreTiming_Settings
 *
 * @package pl8app_StoreTiming_Settings
 * @since 1.0.1
 */

defined('ABSPATH') || exit;

class pl8app_StoreTiming_Settings
{

    public function __construct()
    {
        add_filter('pl8app_settings_general', array($this, 'pl8app_add_time_settings'), 1, 1);

//    add_filter( 'pl8app_settings_sections_general', array( $this, 'pl8app_add_store_time_settings' ) );

        add_action('admin_enqueue_scripts', array($this, 'pl8app_st_admin_styles'));

        add_action('admin_enqueue_scripts', array($this, 'pl8app_st_admin_scripts'));


        add_filter('pl8app_settings_general_sanitize', array($this, 'pl8app_settings_sanitize_store_timings'), 10, 1);

        add_action('pl8app_save_menuitem', array($this, 'pl8app_menuitem_timings') , 10 ,2);


        $this->get_admin_fields();
    }


    /**
     * Set store timing in the general settings tab
     *
     * @param array links of settings array
     * @return array of settings link
     * @since 1.2
     */
    public function pl8app_add_time_settings($general_settings)
    {
        $store_time_settings = array(
            'store_times' => array(
                'id' => 'store_timing',
                'type' => 'store_timing',
            ),
        );

        $store_time_settings = apply_filters('pl8app_timing_settings', $store_time_settings);
        $general_settings['store_timings'] = $store_time_settings;

        return $general_settings;
    }


    /**
     * Creates section for Store Timing in the admin
     *
     * @since  1.0
     * @param  array $section Array of section
     * @return array  array of links for the section
     */
    public function pl8app_add_store_time_settings($section)
    {
        $section['store_timings'] = __('Store Timing', 'pl8app-store-timing');
        return $section;
    }


    /**
     *
     * Include required files for admin fields
     *
     */
    public function get_admin_fields()
    {
        require_once PL8_PLUGIN_DIR . 'includes/admin/store_timing/pl8app-store-timing-fields.php';
    }

    /**
     * Include template file
     *
     * @since  1.2
     * @param  string file name which would be included
     */
    public static function get_template_part($template, $data = '')
    {
        if (!empty($template)) {
            require PL8_PLUGIN_DIR . 'templates/store_timing/' . $template . '.php';
        }
    }


    /**
     * Add necessary css file for the plugin admin section
     *
     * @since  1.0
     */
    public function pl8app_st_admin_styles()
    {
        global $current_screen;

        if ((isset($_GET['page'])
                && $_GET['page'] == 'pl8app-settings'
                && isset($_GET['section'])
                && $_GET['section'] == 'store_timings') || (isset($_GET['page']) && ($_GET['page'] == 'pl8app-store-otime' || $_GET['page'] == 'pl8app-delivery-settings')) || $current_screen->post_type =='menuitem') {
            wp_register_style('jquery-ui', 'http://code.jquery.com/ui/1.21.2/themes/smoothness/jquery-ui.css');
            wp_enqueue_style('jquery-ui');
            wp_register_style('pl8app-st-admin-style', PL8_PLUGIN_URL . 'assets/css/pl8app-store-time-admin.css', array(), PL8_VERSION);
            wp_enqueue_style('pl8app-st-admin-style');
        }

    }


    /**
     * Add necessary js file for the plugin admin section
     *
     * @since  1.0
     */
    public function pl8app_st_admin_scripts()
    {
        global $current_screen;

        if ((isset($_GET['page'])
                && $_GET['page'] == 'pl8app-settings'
                && isset($_GET['section'])
                && $_GET['section'] == 'store_timings') || (isset($_GET['page']) && ($_GET['page'] == 'pl8app-store-otime' || $_GET['page'] == 'pl8app-delivery-settings'))) {
            wp_enqueue_script('jquery-ui-datepicker');
            wp_register_script('pl8app-store-timings-admin', PL8_PLUGIN_URL . 'assets/js/pl8app-store-time-admin.js', array('jquery', 'jquery-ui-datepicker'), PL8_VERSION);

            wp_localize_script('pl8app-store-timings-admin', 'pl8appStoreTime', array('remove_holiday' => __('Remove', 'pl8app-store-timing'),
            ));

            wp_enqueue_script('pl8app-store-timings-admin');
        }
        else if($current_screen->post_type =='menuitem'){
            wp_register_script('pl8app-store-timings-admin', PL8_PLUGIN_URL . 'assets/js/pl8app-store-time-admin.js', array('jquery'), PL8_VERSION);
            wp_localize_script('pl8app-store-timings-admin', 'pl8appStoreTime', array('remove_holiday' => __('Remove', 'pl8app-store-timing'),
            ));

            wp_enqueue_script('pl8app-store-timings-admin');
        }
    }


    /**
     * Get saved data from the database
     */
    public static function pl8app_timing_options()
    {
        $store_timing = get_option('pl8app_store_timing', array());
        return apply_filters('pl8app_get_store_timing', $store_timing);
    }


    /**
     * Santize store timing data
     *
     */
    public function pl8app_settings_sanitize_store_timings($input)
    {
        if (!current_user_can('manage_shop_settings')) {
            return $input;
        }

        if (isset($_POST['blogname'])) {
            update_option('blogname', $_POST['blogname']);
        }
        if (isset($_POST['blogdescription'])) {
            update_option( 'blogdescription', $_POST['blogdescription']);
        }

        if (!isset($_POST['pl8app_store_timing'])) {
            return $input;
        }


        if (isset($_POST['pl8app_store_timing']) && !empty($_POST['pl8app_store_timing'])) {

            $new_input = array();

            foreach ($_POST['pl8app_store_timing'] as $key => $val) {
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

        if (!empty($_POST['timezone_string'])) {
            if (preg_match('/^UTC[+-]/', $_POST['timezone_string'])) {
                $_POST['gmt_offset'] = $_POST['timezone_string'];
                $_POST['gmt_offset'] = preg_replace('/UTC\+?/', '', $_POST['gmt_offset']);
                $_POST['timezone_string'] = '';
            } else {
                $_POST['gmt_offset'] = '';
            }
            update_option('gmt_offset', $_POST['gmt_offset']);
            update_option('timezone_string', $_POST['timezone_string']);

        }


        $old_store_timings = pl8app_StoreTiming_Settings::pl8app_timing_options();

        if(isset($_POST['pl8app_store_timing']['pre_order_range'])){
            $old_store_timings['pre_order_range'] = $_POST['pl8app_store_timing']['pre_order_range'];
        }
        else{
            if(isset($_POST['pl8app_store_timing']['date_format'])) $old_store_timings['date_format'] = $_POST['pl8app_store_timing']['date_format'];
            $old_store_timings['open_time'] = array();
            $old_store_timings['close_time'] = array();
            $old_store_timings['open_day'] = array();

            if(isset($_POST['pl8app_store_timing']['open_day']) && is_array($_POST['pl8app_store_timing']['open_day'])){
                $old_store_timings['open_day'] = $_POST['pl8app_store_timing']['open_day'];
                foreach ($_POST['pl8app_store_timing']['open_day'] as $key => $day) {
                    if(isset($_POST['pl8app_store_timing']['open_time'][$key]) && is_array($_POST['pl8app_store_timing']['open_time'][$key]) && count($_POST['pl8app_store_timing']['open_time'][$key]) > 0){
                        $old_store_timings['open_time'][$key] = array();
                        $old_store_timings['close_time'][$key]= array();
                        foreach ($_POST['pl8app_store_timing']['open_time'][$key] as $index => $value){
                            if(!empty($value) && !empty($_POST['pl8app_store_timing']['close_time'][$key][$index])){
                                $old_store_timings['open_time'][$key][$index] = $value;
                                $old_store_timings['close_time'][$key][$index] = $_POST['pl8app_store_timing']['close_time'][$key][$index];
                            }
                        }
                    }
                    else{
                        $old_store_timings['open_time'][$key] = array();
                        $old_store_timings['close_time'][$key] = array();
                    }
                }
            }

            if(isset($_POST['pl8app_store_timing']['24hours'])){
                $old_store_timings['24hours'] = $_POST['pl8app_store_timing']['24hours'];
            }
            else{
                $old_store_timings['24hours'] = array();
            }
            if(isset($_POST['pl8app_store_timing']['holiday']) && count($_POST['pl8app_store_timing']['holiday']) > 0){
                $old_store_timings['holiday'] = $_POST['pl8app_store_timing']['holiday'];
                foreach($old_store_timings['holiday'] as $key=> $value){
                    if(empty($value)) unset($old_store_timings['holiday'][$key]);
                }
            }
            else{
                $old_store_timings['holiday'] = array();
            }
        }

        update_option('pl8app_store_timing', $old_store_timings);
        return $input;
    }

    /**
     * Save the Menu items ' Timing meta data
     * @param $post_id
     * @param $post
     */
    public function pl8app_menuitem_timings($post_id, $post){
        if(!empty($_POST['pl8app_menuitem_timing'])){
            update_post_meta($post_id, 'pl8app_menuitem_timing', $_POST['pl8app_menuitem_timing']);
        }
    }


}

new pl8app_StoreTiming_Settings();