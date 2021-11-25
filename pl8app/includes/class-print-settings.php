<?php
/**
 * pl8app_Print_Settings
 *
 * @package pl8app_Print_Settings
 * @since 1.0.0
 */

defined('ABSPATH') || exit;

class pl8app_Print_Settings
{

    /**
     * Setting the Global Redirect URI
     */
    private $redirect_uri;

    /**
     * Setting the Google API Auth URI
     */
    private $auth_url;

    /**
     * Setting the Access Token URL
     */
    private $access_token_url;

    /**
     * Setting the Refresh Token URL
     */
    private $refresh_token_url;

    /**
     * Class Constructor
     */
    public function __construct()
    {

        $this->redirect_uri = admin_url('admin.php?page=pl8app-settings&tab=general&section=printer_settings&auth=offline');

        $this->auth_url = 'https://accounts.google.com/o/oauth2/auth';

        $this->access_token_url = 'https://accounts.google.com/o/oauth2/token';

        $this->refresh_token_url = 'https://www.googleapis.com/oauth2/v3/token';

        add_action('pl8app_after_order_title', array($this, 'pl8printer_add_meta_box'), 10, 1);

//        add_filter('pl8app_settings_general', array($this, 'pl8app_general_printer_settings'), 10);
//
//        add_filter('pl8app_settings_sections_general', array($this, 'pl8app_add_printer_settings'));

        add_action('admin_enqueue_scripts', array($this, 'pl8app_printer_admin_scripts'), 10);

        add_action('wp_ajax_pl8app_print_payment_data', array($this, 'pl8app_print_payment_data'));

        if (is_admin()) {

            add_filter('pl8app_payments_table_columns', array($this, 'add_print_column'));
            add_filter('pl8app_payments_table_column', array($this, 'get_printer_action'), 10, 3);
        }

        // Preparing auto print once a new order is saved
        add_action('pl8app_payment_saved', array($this, 'new_order_auto_print'), 10, 2);

        add_action('admin_init', array($this, 'google_cloud_console_callback'));
    }

    public function google_cloud_console_callback()
    {

        $settings = pl8app_Print_Settings::get_printer_settings();

        $client_id = isset($settings['g_client_id']) ? $settings['g_client_id'] : '';
        $client_secret = isset($settings['g_client_secret']) ? $settings['g_client_secret'] : '';

        if (isset($_GET['auth']) && 'offline' === $_GET['auth']) {

            if (isset($_GET['code']) && $_GET['code'] != '') {

                $authConfig = array(
                    'code' => $_GET['code'],
                    'client_id' => $client_id,
                    'client_secret' => $client_secret,
                    'redirect_uri' => $this->redirect_uri,
                    "grant_type" => "authorization_code"
                );

                $gcp = new GoogleCloudPrint();
                $responseObj = $gcp->getAccessToken($this->access_token_url, $authConfig);

                $refreshToken = $responseObj->refresh_token;

                echo '<p style="border: dotted 1px #885777; padding: 1em; background-color: #d4d4d4; font-size: 18px; color: #1d1d1d;">Your <b>Refresh Token</b>: <i>' . $refreshToken . '</i></p>';

                exit;
            }
        }
    }


    /**
     * Creates section for Printer Settings in the admin
     *
     * @since  1.0.0
     * @param  array $section Array of section
     * @return array  array of links for the section
     */
    public function pl8app_add_printer_settings($section)
    {
        $section['printer_settings'] = __('Printer Settings', 'pl8app-printer');
        return $section;
    }

    /**
     * Add submenu section for printer in the pl8app section
     * @since 1.0.0
     */
    public function pl8app_general_printer_settings($general_settings)
    {

        $general_settings['printer_settings'] = array(
            'printer_settings' => array(
                'id' => 'printer_settings',
                'type' => 'header',
                'name' => '<h3>' . __('Printer Settings', 'pl8app-printer') . '</h3>',
            ),

            'enable_printing' => array(
                'id' => 'enable_printing',
                'name' => __('Enable Printing Option', 'pl8app-printer'),
                'desc' => __('Check this option to enable printing of invoice', 'pl8app-printer'),
                'type' => 'checkbox',
            ),

            'store_logo' => array(
                'id' => 'store_logo',
                'name' => __('Store Logo', 'pl8app-printer'),
                'desc' => __('Select an image to use as the logo in the invoice. Recommended size 280x75.', 'pl8app-printer'),
                'type' => 'upload',
            ),

            'order_print_status' => array(
                'id' => 'order_print_status',
                'name' => __('Select Order Statuses', 'pl8app-printer'),
                'desc' => __('Select the order statuses for which the print will work.', 'pl8app-printer'),
                'type' => 'multicheck',
                'options' => pl8app_get_order_statuses()
            ),

            'order_printing_font' => array(
                'id' => 'order_printing_font',
                'name' => __('Printing Font', 'pl8app-printer'),
                'desc' => __('Choose the text font for printing.', 'pl8app-printer'),
                'type' => 'select',
                'options' => array(
                    'Times, serif' => __('Times New', 'pl8app-printer'),
                    'Georgia, serif' => __('Georgia', 'pl8app-printer'),
                    'Palatino, serif' => __('Palatino', 'pl8app-printer'),
                    'Arial, Helvetica' => __('Arial', 'pl8app-printer'),
                    'Comic Sans MS, cursive, sans-serif' => __('Comic Sans', 'pl8app-printer'),
                    'Lucida Sans Unicode, sans-serif' => __('Lucida Sans', 'pl8app-printer'),
                    'Tahoma, Geneva, sans-serif' => __('Tahoma', 'pl8app-printer'),
                    'Trebuchet MS, Helvetica, sans-serif' => __('Trebuchet MS', 'pl8app-printer'),
                    'Courier New, Courier, monospace' => __('Courier New', 'pl8app-printer'),
                    'Lucida Console, Monaco, monospace' => __('Lucida Console', 'pl8app-printer'),
                ),
            ),

            'paper_size' => array(
                'id' => 'paper_size',
                'name' => __('Select Paper Size', 'pl8app-printer'),
                'desc' => __('Select the paper size that you want to print', 'pl8app-printer'),
                'type' => 'select',
                'options' => $this->get_paper_sizes(),
                'placeholder' => __('Select page size', 'pl8app-printer'),
            ),

            'footer_area_content' => array(
                'id' => 'footer_area_content',
                'name' => __('Footer Text', 'pl8app-printer'),
                'desc' => __('Enter the details you want to show on invoice below the items listing and total price.You can add image and align the content using the editor.', 'pl8app-printer'),
                'type' => 'rich_editor',
            ),

            'complementary_close' => array(
                'id' => 'complementary_close',
                'name' => __('Complementary Close', 'pl8app-printer'),
                'desc' => __('Enter the details you want to show on invoice at the end of receipt.', 'pl8app-printer'),
                'type' => 'rich_editor',
            ),

            'auto_printing_settings' => array(
                'id' => 'auto_printing_settings',
                'type' => 'header',
                'name' => '<h3>' . __('Auto Printing Settings', 'pl8app-printer') . '</h3>',
            ),

            'enable_auto_printing' => array(
                'id' => 'enable_auto_printing',
                'name' => __('Enable Automatic Printing', 'pl8app-printer'),
                'desc' => __('Check this option to enable auto printing when a new order is received.', 'pl8app-printer'),
                'type' => 'checkbox',
            ),

            'copies_per_print' => array(
                'id' => 'copies_per_auto_print',
                'name' => __('Copies Per Auto Print', 'pl8app-printer'),
                'desc' => __('Set the copy number when auto printing ', 'pl8app-printer'),
                'type' => 'select',
                'options' => array(
                    'pending' => __('All Orders', 'pl8app-printer'),
                    'limited' => __('Paid / Cash on Delivery Orders', 'pl8app-printer'),
                ),
            ),
        );
        return $general_settings;
    }

    /**
     * Description to ask users to check YouTube video for
     * setting up the Cloud Printing API
     *
     * @since 1.1
     * @author pl8app
     */
    public function get_client_id_message()
    {

        $yt_link = 'https://youtu.be/dKIKuUWxWqU';

        return sprintf(__('Please check our <a target="_blank" href="%s">YouTube Video</a> to get Client ID and other informations.', 'pl8app-printer'), $yt_link);
    }

    /**
     * Help text for users to know how to add a printer.
     *
     * @since 1.1
     * @author pl8app
     */
    public function get_list_printers_message()
    {

        $add_printer_link = 'https://support.google.com/cloudprint/answer/1686197';

        return sprintf(__('Select printer to auto print. <a target="_blank" href="%s">How to Add Printer to Google Cloud Print</a>.', 'pl8app-printer'), $add_printer_link);
    }

    /**
     * Prepare autorization URL for google cloud printing
     *
     * @since 1.1
     * @author pl8app
     */
    public function get_authorization_uri_message()
    {

        $settings = pl8app_Print_Settings::get_printer_settings();
        $client_id = isset($settings['g_client_id']) ? $settings['g_client_id'] : '';
        $client_secret = isset($settings['g_client_secret']) ? $settings['g_client_secret'] : '';
        $refresh_token = isset($settings['g_refresh_token']) ? $settings['g_refresh_token'] : '';

        if ($refresh_token != '') {
            return __('Cloud Print Refresh Token.', 'pl8app-printer');
        }

        if ($client_id != '' && $client_secret != '') {

            $redirectConfig = array(
                'client_id' => $client_id,
                'redirect_uri' => $this->redirect_uri,
                'response_type' => 'code',
                'scope' => 'https://www.googleapis.com/auth/cloudprint',
            );

            $offlineAccessConfig = array(
                'access_type' => 'offline'
            );

            $link_location = $this->auth_url . "?" . http_build_query(array_merge($redirectConfig, $offlineAccessConfig));

            return sprintf(__('<a target="_blank" href="%s">Click</a> to get Refresh Token. Link will work only after saving the Client ID and Secret.', 'pl8app-printer'), $link_location);
        } else {

            $link_location = admin_url('admin.php?page=pl8app-printer-settings&auth=offline');

            return sprintf(__('<a target="_blank" href="%s">Click</a> to get Refresh Token. Link will work only after saving the Client ID and Secret.', 'pl8app-printer'), $link_location);
        }
    }

    /**
     * Get list of Online Printers
     *
     * @since 1.1
     * @author pl8app
     */
    public function pl8app_get_online_printers()
    {

        $printer_list = array();

        $settings = pl8app_Print_Settings::get_printer_settings();

        $client_id = !empty($settings['g_client_id']) ? $settings['g_client_id'] : '';
        $client_secret = !empty($settings['g_client_secret']) ? $settings['g_client_secret'] : '';
        $refresh_token = !empty($settings['g_refresh_token']) ? $settings['g_refresh_token'] : '';

        if ('' == $client_id || '' == $client_secret || '' == $refresh_token) {
            return $printer_list;
        }

        $refreshTokenConfig = array(
            'refresh_token' => $refresh_token,
            'client_id' => $client_id,
            'client_secret' => $client_secret,
            'grant_type' => "refresh_token"
        );


        $gcp = new GoogleCloudPrint();

        $token = $gcp->getAccessTokenByRefreshToken($this->refresh_token_url, http_build_query($refreshTokenConfig));

        if ($token) {

            $gcp->setAuthToken($token);
            $printers = $gcp->getPrinters();

            if (count($printers) > 0) {
                foreach ($printers as $printer) {
                    $printer_list[$printer['id']] = $printer['displayName'];
                }
            }
        }

        return $printer_list;
    }

    /**
     * Get printer paper size
     *
     * @since 1.1
     * @return array
     */
    private function get_paper_sizes()
    {

        $paper_sizes = array(
            '' => __('Select Paper Size', 'pl8app-printer'),
            '56.9mm' => __('57mm x 38mm (Mobile/Small CC Terminals)', 'pl8app-printer'),
            '57.0mm' => __('57mm x 40mm (Mobile/Small CC Terminals)', 'pl8app-printer'),
            '57.1mm' => __('57mm x 50mm (Mobile/Small CC Terminals)', 'pl8app-printer'),
            '79.9mm' => __('80mm x 60mm (Thermal Receipt Printers)', 'pl8app-printer'),
            '80.0mm' => __('80mm x 70mm (Thermal Receipt Printers)', 'pl8app-printer'),
            '80.1mm' => __('80mm x 80mm (Thermal Receipt Printers)', 'pl8app-printer'),
        );

        return apply_filters('pl8app_printer_paper_size', $paper_sizes);
    }

    /**
     * Get template file for plugin
     *
     * @param string $template template name
     * @param array $data
     *
     * @return mixed
     */
    public function get_template_part($template, $data = '')
    {

        if (!empty($template)) {
            require PL8_PLUGIN_DIR . '/templates/printer_receipts/' . $template . '.php';
        }
    }

    /**
     *Add necessary css and js for the plugin
     *
     * @since 1.0.0
     * @return mixed
     */
    public function pl8app_printer_admin_scripts($hook)
    {

        wp_register_style('pl8app-printer-admin-style', PL8_PLUGIN_URL . 'assets/css/pl8app-printer-admin-style.css', array(), PL8_VERSION);
        wp_enqueue_style('pl8app-printer-admin-style');

        wp_register_script('pl8app-printer-admin-script', PL8_PLUGIN_URL . 'assets/js/pl8app-printer-admin.js', array('jquery'), PL8_VERSION);

        $params = array(
            'ajaxUrl' => admin_url('admin-ajax.php'),
        );

        wp_localize_script('pl8app-printer-admin-script', 'pl8app_printer_vars', $params);

        wp_enqueue_script('pl8app-printer-admin-script');
    }

    /**
     * Get printer settings from the database
     *
     * @since 1.0.0
     * @return mixed
     */
    protected function get_printer_settings()
    {

        $printer_settings = get_option('pl8app_settings', array());
        return apply_filters('pl8app_printer_settings_fields', $printer_settings);

    }

    /**
     * Add print text in the columns
     *
     * @since 1.0.0
     * @param array $columns columns
     *
     * @return array
     */
    public function add_print_column($columns)
    {

        $new_columns = (is_array($columns)) ? $columns : array();
        $get_settings = pl8app_Print_Settings::get_printer_settings();

        if (isset($get_settings['enable_printing'])) $new_columns['print'] = __('Print', 'pl8app-printer');

        return $new_columns;
    }

    /**
     * Check whether print action should be available with selected order
     *
     * @since 1.0.0
     * @param int $payment_id
     *
     * @return bool
     */
    public function check_print_action_available($payment_id)
    {

        $current_status = pl8app_get_order_status($payment_id);
        $get_settings = pl8app_Print_Settings::get_printer_settings();

        $print_status = isset($get_settings['order_print_status']) ? $get_settings['order_print_status'] : array();

        if (isset($get_settings['enable_printing'])
            && array_key_exists($current_status, $print_status)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Add print actions with this column
     *
     * @since 1.0.0
     * @param string $value output string with matching column
     * @param int $payment_id payment id to be checked
     * @param string $column_name column name
     *
     * @return array
     */
    public function get_printer_action($value, $payment_id, $column_name)
    {
        if ('print' === $column_name) {
            if ($this->check_print_action_available($payment_id)) {
                $value = '<div style="display: none;" class="print-display-area" id="print-display-area-' . $payment_id . '"></div><button type="button" data-payment-id="' . $payment_id . '" class="button pl8app_print_now">' . apply_filters('pl8app_print_text', __('<span class="dashicons dashicons-media-document"></span>', 'pl8app-printer')) . '</button>';
            }
        }
        return $value;
    }

    /**
     * Add print actions on payment details page
     *
     * @since 1.0.0
     * @param int $payment_id
     */
    public function pl8printer_add_meta_box($payment_id)
    {

        if ($this->check_print_action_available($payment_id)):

            echo '<div style="display: none;" class="print-display-area" id="print-display-area-' . $payment_id . '"></div><button type="button" data-payment-id="' . $payment_id . '" class="button pl8app_print_now">' . apply_filters('pl8app_edit_print_text', __('Print', 'pl8app-printer')) . '</button>';

        endif;
    }

    /**
     * Generating complete print screen HTML
     * All the content will be later replaced with shortcodes
     * availble in the template file
     *
     * @since 1.0.0
     */
    public function pl8app_print_payment_data($pay_id = '')
    {

        $payment_id = isset($_GET['payment_id']) ? $_GET['payment_id'] : $pay_id;

        $payment = new pl8app_Payment($payment_id);

        $payment_meta = $payment->get_meta();
        $address_meta = get_post_meta($payment_id, '_pl8app_delivery_address', true);

        $invoice_content = pl8app_get_template_part('printer-receipt');

        if (!$invoice_content) {

            $template = new pl8app_Print_Settings();
            ob_start();
            $receipt_content = $template->get_template_part('printer-receipt');
            $invoice_content = ob_get_clean();
        }

        $printer_settings = pl8app_Print_Settings::get_printer_settings();

        // Paper Size
        $paper_size = isset($printer_settings['paper_size']) ? $printer_settings['paper_size'] : '80mm';

        // Selected Font
        $printing_font = isset($printer_settings['order_printing_font']) ? 'font-family: ' . $printer_settings['order_printing_font'] . ';' : 'font-family: Arial, Helvetica, sans-serif;';

        // Store Logo / Name
        $image_path = isset($printer_settings['store_logo']) ? $printer_settings['store_logo'] : '';

        if ($image_path != '') {
            $image_type = pathinfo($image_path, PATHINFO_EXTENSION);
            $image_data = file_get_contents($image_path);
            $base64_img = 'data:image/' . $image_type . ';base64,' . base64_encode($image_data);

            $store_logo = '<img style="height: 75px; width:100%; margin-bottom: 10px;" src="' . $base64_img . '">';
        } else {
            $store_logo = get_bloginfo('name');
        }

        // Customer Info (Email, Phone, Email)
        if(isset($payment_meta['user_info']['first_name'])){
            $customer_name = $payment_meta['user_info']['first_name'];
        }
        else{
            if(isset($payment_meta['user_info']['last_name'])){
                $customer_name = $payment_meta['user_info']['last_name'];
            }
            else{
                $customer_name = '';
            }
        }
        $customer_mail = $payment_meta['email'];
        $customer_phone = !empty($payment_meta['phone']) ? $payment_meta['phone'] : '';

        // Service Type
        $service_type = $payment->get_meta('_pl8app_delivery_type');

        // Service Date
        $service_date = get_post_meta($payment_id, '_pl8app_delivery_date', true);
        $service_date = pl8app_local_date($service_date);

        // Service Time
        $service_time = get_post_meta($payment_id, '_pl8app_delivery_time', true);

        // Payment Type
        $payment_type = !empty($payment->gateway) ? '<p><b>' . apply_filters('printer_payment_type_text', __('Payment: ', 'pl8app-printer')) . '</b> ' . pl8app_get_gateway_checkout_label($payment->gateway) . ' (' . $payment->status_nicename . ')</p>' : '';

        // Address to be shown
        $address_string = '';

        if (!empty($service_type) && $service_type == 'delivery'):

            $address_string .= '<p>' . apply_filters('printer_payment_address_text', __('Address: ', 'pl8app-printer')) . '<b>';

            $address_array = array();

            if (!empty($address_meta['address'])) {
                array_push($address_array, $address_meta['address']);
            }

            if (!empty($address_meta['flat'])) {
                array_push($address_array, $address_meta['flat']);
            }

            if (!empty($address_meta['city'])) {
                array_push($address_array, $address_meta['city']);
            }

            if (!empty($address_meta['postcode'])) {
                array_push($address_array, $address_meta['postcode']);
                $delivery_group = '';
                $delivery_settings = pl8app_Delivery_Fee_Settings::pl8app_fee_settings();
                $delivery_zones = $delivery_settings['delivery_fee'];
                foreach ($delivery_zones as $key => $delivery_zone) {
                    $zip_code = !empty($delivery_zone['zip_code']) ? $delivery_zone['zip_code'] : '';

                    if (!empty($zip_code)) {
                        $zip_code = array_map('trim', explode(',', strtolower($zip_code)));

                        $delivery_zip_code = strtolower($address_meta['postcode']);

                        if (in_array(strtolower($delivery_zip_code), $zip_code)) {

                            $delivery_group = $delivery_zones[$key]['driver_group'];
                            break;
                        } else {
                            foreach ($zip_code as $k => $zip) {
                                if (strpos($zip, "*") !== false) {
                                    $delivery_zip_code_array = array_map('trim', explode(' ', strtolower($delivery_zip_code)));
                                    if (substr($zip, 0, -1) == $delivery_zip_code_array[0]) {
                                        $delivery_group = $delivery_zones[$key]['driver_group'];
                                        break 2;
                                    }
                                }
                            }
                        }
                    }

                }

            }

            $address_from_array = implode(', ', $address_array);

            $address_string .= $address_from_array;
            $address_string .= '</b>';
            if(!empty($delivery_group)) $address_string .= '</br><strong>'. __('Delivery Group: ', 'pl8app-printer'). '</strong>'.$delivery_group;
            $address_string .= '</p>';

        endif;

        if (!empty($service_type) && $service_type == 'dinein'):

            $dinein_table = isset($payment_meta['pl8app_dinein_table_id']) ? $payment_meta['pl8app_dinein_table_id'] : '';

            if (!empty($dinein_table)) {
                $address_string .= '<p>' . apply_filters('printer_dine_in_text', __('Dinein Table: ', 'pl8app-printer')) . '<b>';
                $address_string .= $dinein_table . '</b></p>';
            }

        endif;

        // Items List
        $receipt_content = $this->render_payment_order_details($payment_id);

        // Order Note
        $payment_note = get_post_meta($payment_id, '_pl8app_order_note', true);
        $payment_note = !empty($payment_note) ? '<p> ' . apply_filters('printer_payment_note_text', __('Instructions: ', 'pl8app-printer')) . '<b> ' . $payment_note . '</b></p>' : '';

        // Footer Notes
        $footer_note = isset($printer_settings['footer_area_content']) ? $printer_settings['footer_area_content'] : '';
        $complm_note = isset($printer_settings['complementary_close']) ? $printer_settings['complementary_close'] : '';


        // Filters for Serive Type Translation
        $service_type_text = '';
        if (!empty($service_type) && $service_type == 'delivery') {
            $service_type_text = apply_filters('pl8app_print_delivery_service_text', __('Delivery', 'pl8app-printer'));
        } else if (!empty($service_type) && $service_type == 'pickup') {
            $service_type_text = apply_filters('pl8app_print_pickup_service_text', __('Pickup', 'pl8app-printer'));
        } else if (!empty($service_type) && $service_type == 'dinein') {
            $service_type_text = apply_filters('pl8app_print_dinein_service_text', __('Dinein', 'pl8app-printer'));
        }

        // Service Time Text
        $pl8p_order_time_text = sprintf(__('%s Time:', 'pl8app-printer'), $service_type_text);

        $search = array(
            '{pl8p_choosen_font}',
            '{pl8p_paper_size}',
            '{pl8p_store_logo}',
            '{pl8p_order_id}',
            '{pl8p_customer_name}',
            '{pl8p_customer_phone}',
            '{pl8p_customer_email}',
            '{pl8p_order_type}',
            '{pl8p_order_time_text}',
            '{pl8p_order_time}',
            '{pl8p_order_date}',
            '{pl8p_order_location}',
            '{pl8p_order_payment_type}',
            '{pl8p_order_note}',
            '{pl8p_order_items}',
            '{footer_note}',
            '{footer_complementary}',
        );

        $replace = array(
            $printing_font,
            $paper_size,
            $store_logo,
            $payment_id,
            $customer_name,
            $customer_phone,
            $customer_mail,
            $service_type_text,
            $pl8p_order_time_text,
            $service_time,
            $service_date,
            $address_string,
            $payment_type,
            $payment_note,
            $receipt_content,
            $footer_note,
            $complm_note
        );

        $content = str_replace($search, $replace, $invoice_content);

        if (!empty($pay_id)) {
            return $content;
        } else {
            // Output the content
            ob_start();
            echo $content;
            wp_die();
        }
    }

    /**
     * Get the HTML part of the receipt to print
     *
     * @since 1.0.0
     * @param int $payment_id Payment ID
     *
     */
    public function render_payment_order_details($payment_id)
    {

        $payment_receipt = pl8app_get_template_part('printer-receipt-content');

        if (!$payment_receipt) {
            $template = new pl8app_Print_Settings();
            ob_start();
            $receipt_content = $template->get_template_part('printer-receipt-content', $payment_id);
            $payment_receipt = ob_get_clean();
        }

        return $payment_receipt;
    }

    /**
     * New order receipt generation once a order is placed
     *
     * @since 1.0.0
     * @param int $payment_id Payment ID
     * @param object $payment Complete Payment Object
     */
    public function new_order_auto_print($payment_id, $payment)
    {

        $settings = pl8app_Print_Settings::get_printer_settings();
        $cloud_print_status = !empty($settings['auto_print_status']) ? $settings['auto_print_status'] : 'pending';

        $do_printing = false;

        // Verify the conditions
        if ($cloud_print_status == 'pending' && $payment->status == 'pending') {

            $do_printing = true;

        } else {

            // Only if Payment Status is Paid then enable Printing
            if ($payment->status == 'publish') {
                $do_printing = true;
            }

            // Else if Payments methods are manual then enable printing
            if ($payment->gateway == 'cash_on_delivery' || $payment->gateway == 'manual') {
                $do_printing = true;
            }
        }

        // Proceed to Auto Printing
        if (!is_admin() && !empty($payment->menuitems) && isset($settings['enable_cloud_printing']) && $do_printing) {

            // If transient already created for auto print then exit
            if (get_transient('pl8app_auto_print_' . $payment_id))
                return '';

            // Create transient to avoid multiple print
            set_transient('pl8app_auto_print_' . $payment_id, 'on_progress', 300);

            $client_id = isset($settings['g_client_id']) ? $settings['g_client_id'] : '';
            $client_secret = isset($settings['g_client_secret']) ? $settings['g_client_secret'] : '';
            $refresh_token = isset($settings['g_refresh_token']) ? $settings['g_refresh_token'] : '';

            if ('' == $refresh_token)
                return '';

            if (isset($settings['g_online_printer'])) {
                if (!is_array($settings['g_online_printer'])) {
                    $cloud_printer[$settings['g_online_printer']] = $settings['g_online_printer'];
                } else {
                    $cloud_printer = $settings['g_online_printer'];
                }
            }

            $refreshTokenConfig = array(
                'refresh_token' => $refresh_token,
                'client_id' => $client_id,
                'client_secret' => $client_secret,
                'grant_type' => "refresh_token"
            );

            $gcp = new GoogleCloudPrint();

            $token = $gcp->getAccessTokenByRefreshToken($this->refresh_token_url, http_build_query($refreshTokenConfig));
            $gcp->setAuthToken($token);

            // Create PDF to Proceed
            $pdf_uploads_dir = wp_upload_dir()['basedir'] . '/print-pdfs/';
            if (!is_dir($pdf_uploads_dir)) {
                mkdir($pdf_uploads_dir, 0755);
            }

            $print_content = $this->pl8app_print_payment_data($payment_id);
            $file_path = $pdf_uploads_dir . 'receipt_' . $payment_id . '.pdf';

            $html2pdf = new Spipu\Html2Pdf\Html2Pdf('P', array(74, 350), 'en', true, 'UTF-8', array(0, 0, 0, 0));
            $html2pdf->setDefaultFont('Times');
            $html2pdf->writeHtml($print_content);
            $html2pdf->output($file_path, 'F');

            // $html2pdf->output();
            // exit;

            foreach ($cloud_printer as $printerid => $printer_name) {

                $resarray = $gcp->sendPrintToPrinter($printerid, "receipt_" . $payment_id, $file_path, "application/pdf");

                if ($resarray['status'] == true) {
                    update_post_meta($payment_id, 'pl8app_auto_print_id', $resarray['id']);
                } else {
                    update_post_meta($payment_id, 'pl8app_auto_print_id', $resarray['errormessage']);
                }
            }
        }
    }
}

new pl8app_Print_Settings();