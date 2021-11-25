<?php


defined('ABSPATH') || exit;

/**
 * pl8app Shortcodes class.
 */
class pla_Shortcodes
{

    /**
     * Init Shortcodes.
     */
    public static function init()
    {
        $shortcodes = array(
            'menuitems' => __CLASS__ . '::menuitems',
            'menuitem_cart' => __CLASS__ . '::menuitem_cart',
            'menuitem_checkout' => __CLASS__ . '::menuitem_checkout',
            'pl8app_receipt' => __CLASS__ . '::pl8app_receipt',
            'menuitem_history' => __CLASS__ . '::menuitem_history',
            'order_history' => __CLASS__ . '::order_history',
            'pl8app_login' => __CLASS__ . '::pl8app_login',
            'pl8app_register' => __CLASS__ . '::pl8app_register',
            'menuitem_discounts' => __CLASS__ . '::menuitem_discounts',
            'purchase_collection' => __CLASS__ . '::purchase_collection',
            'pl8app_profile_editor' => __CLASS__ . '::pl8app_profile_editor',
            'pl8app_store_phone' => __CLASS__ . '::pl8app_store_phone',
            'pl8app_allergyform' => __CLASS__ . '::pl8app_allergy_form',
            'pl8app_contactform' => __CLASS__ . '::pl8app_contact_form',
            'pl8app_faq' => __CLASS__ . '::pl8app_faq_page',
            'pl8app_delivery_refund' => __CLASS__ . '::pl8app_delivery_refund_page',
            'pl8app_privacy_policy_content' => __CLASS__ . '::pl8app_privacy_policy_content_page',
            'pl8app_store_contact_information' =>  __CLASS__ . '::pl8app_store_contact_information',
            'pl8app_before_footer' => __CLASS__ . '::pl8app_widget_before_footer',
            'pl8app_store_name' => __CLASS__ . '::pl8app_store_name',
            'pl8app_store_email' => __CLASS__ . '::pl8app_store_email',
        );

        foreach ($shortcodes as $shortcode => $function) {
            add_shortcode(apply_filters("{$shortcode}_shortcode_tag", $shortcode), $function);
        }
    }

    /**
     * Shortcode Wrapper.
     *
     * @param string[] $function Callback function.
     * @param array $atts Attributes. Default to empty array.
     * @param array $wrapper Customer wrapper data.
     *
     * @return string
     */
    public static function shortcode_wrapper(
        $function,
        $atts = array(),
        $wrapper = array(
            'class' => '',
            'before' => null,
            'after' => null,
        )
    )
    {

        ob_start();

        // @codingStandardsIgnoreStart
        echo empty($wrapper['before']) ? '<div class="pl8app ' . apply_filters('pl8app_container_class', esc_attr($wrapper['class'])) . '">' : $wrapper['before'];
        call_user_func($function, $atts);
        echo empty($wrapper['after']) ? '</div>' : $wrapper['after'];
        // @codingStandardsIgnoreEnd

        return ob_get_clean();
    }

    /**
     * Get the Store Name
     */
    public static function pl8app_store_name(){
        $options = get_option('pl8app_settings');

        return !empty($options['pl8app_store_name'])?$options['pl8app_store_name']: '';
    }

    /**
     * Get the Store Email
     */
    public static function pl8app_store_email(){
        $options = get_option('pl8app_settings');

        return !empty($options['pl8app_st_email'])?$options['pl8app_st_email']: '';
    }

    /**
     * Get the store Phone Number
     */
    public static function pl8app_store_phone(){
        $options = get_option('pl8app_settings');
        return !empty($options['pl8app_phone_number'])?$options['pl8app_phone_number']:'**(`Store Phone number is not available now!`)';
    }

    /**
     * Get the Allergy Form
     */
    public static function pl8app_allergy_form(){
        return pl8app_pg_allergy_form();
    }

    /**
     * Get the Allergy Form
     */
    public static function pl8app_contact_form(){
        return pl8app_pg_contact_form();
    }

    /**
     * Render the FAQ page
     */
    public static function pl8app_faq_page(){
        return pl8app_pg_faq();
    }
    /**
     * Render the DELIVERY, RETURNS AND REFUNDS page
     */
    public static function pl8app_delivery_refund_page(){
        return pl8app_pg_delivery_refund();
    }

    /**
     * Privacy Policy content render
     */

    public static function pl8app_privacy_policy_content_page(){
        $options = get_option('pl8app_settings');
        $pattern = '/\{%pl8app_store_contact_information%\}/i';
        $store_information = self::pl8app_store_contact_information();
        return  !empty($options['privacy_policy_content'])?preg_replace($pattern,$store_information,$options['privacy_policy_content']):'';
    }
    /**
     * Get the pl8app store contact information
     */
    public static function pl8app_store_contact_information(){
        $countries = pl8app_get_country_list();
        $options = get_option('pl8app_settings');

        if(!empty($options['base_country'])){
            $states = pl8app_get_states($options['base_country']);
        }
        else{
            $options['base_country'] = '';
        }

        if(!empty($options['base_state']) && !empty($states[$options['base_state']])){
            $state = $states[$options['base_state']];
        }
        else{
            $state = '';
        }


        $contact_information = '<p>'. (isset($options['pl8app_store_name'])?$options['pl8app_store_name']:'') .', '. (isset($options['pl8app_street_address'])?$options['pl8app_street_address']:'') .', '. $state .'</p>';
        $contact_information .= '<p>'. (isset($options['pl8app_pz_code'])?$options['pl8app_pz_code']:'') .', '. (isset($countries[$options['base_country']])?$countries[$options['base_country']]:'') .'</p>';
        $contact_information .= '<p>Telephone: '. $options['pl8app_phone_number'] .'</p>';

        return $contact_information;
    }

    /**
     * @param $atts
     */

    public static function pl8app_widget_before_footer($atts){
        return pl8app_wg_before_footer($atts);
    }
    /**
     * MenuItems Shortcode.
     *
     * @return string
     */
    public static function menuitems($atts)
    {
        return self::shortcode_wrapper(array('pla_Shortcode_Menuitems', 'output'), $atts);
    }

    /**
     * Item Cart Shortcode
     *
     * Show the shopping cart.
     *
     * @since 1.0
     * @param array $atts Shortcode attributes
     * @param string $content
     * @return string
     */
    public static function menuitem_cart($atts = array(), $content = null)
    {
        return pl8app_shopping_cart();
    }

    /**
     * Checkout Form Shortcode
     *
     * Show the checkout form.
     *
     * @since 1.0
     * @return string
     */
    public static function menuitem_checkout()
    {
        return pl8app_checkout_form();
    }

    /**
     * Receipt Shortcode
     *
     * Shows an order receipt.
     *
     * @since  1.0.0
     * @param array $atts Shortcode attributes
     * @param string $content
     * @return string
     */

    public static function pl8app_receipt($atts = array(), $content = null)
    {

        global $pl8app_receipt_args;

        $pl8app_receipt_args = shortcode_atts(array(
            'error' => __('Sorry, trouble retrieving payment receipt.', 'pl8app'),
            'price' => true,
            'discount' => true,
            'products' => true,
            'date' => true,
            'notes' => true,
            'payment_key' => false,
            'payment_method' => true,
            'payment_id' => true
        ), $atts, 'pl8app_receipt');

        $session = pl8app_get_purchase_session();
        if (isset($_GET['payment_key'])) {
            $payment_key = urldecode($_GET['payment_key']);
        } else if ($session) {
            $payment_key = $session['purchase_key'];
        } elseif ($pl8app_receipt_args['payment_key']) {
            $payment_key = $pl8app_receipt_args['payment_key'];
        }

        // No key found
        if (!isset($payment_key)) {
            return '<p class="pl8app-alert pl8app-alert-error">' . $pl8app_receipt_args['error'] . '</p>';
        }

        $payment_id = pl8app_get_purchase_id_by_key($payment_key);
        $user_can_view = pl8app_can_view_receipt($payment_key);

        // Key was provided, but user is logged out. Offer them the ability to login and view the receipt
        if (!$user_can_view && !empty($payment_key) && !is_user_logged_in() && !pl8app_is_guest_payment($payment_id)) {
            global $pl8app_login_redirect;
            $pl8app_login_redirect = pl8app_get_current_page_url();

            ob_start();

            echo '<p class="pl8app-alert pl8app-alert-warn">' . __('You must be logged in to view this payment receipt.', 'pl8app') . '</p>';
            pl8app_get_template_part('shortcode', 'login');

            $login_form = ob_get_clean();

            return $login_form;
        }

        $user_can_view = apply_filters('pl8app_user_can_view_receipt', $user_can_view, $pl8app_receipt_args);

        // If this was a guest checkout and the purchase session is empty, output a relevant error message
        if (empty($session) && !is_user_logged_in() && !$user_can_view) {
            return '<p class="pl8app-alert pl8app-alert-error">' . apply_filters('pl8app_receipt_guest_error_message', __('Receipt could not be retrieved, your purchase session has expired.', 'pl8app')) . '</p>';
        }

        /*
         * Check if the user has permission to view the receipt
         *
         * If user is logged in, user ID is compared to user ID of ID stored in payment meta
         *
         * Or if user is logged out and purchase was made as a guest, the purchase session is checked for
         *
         * Or if user is logged in and the user can view sensitive shop data
         *
         */
        if (!$user_can_view) {
            return '<p class="pl8app-alert pl8app-alert-error">' . $pl8app_receipt_args['error'] . '</p>';
        }

        ob_start();

        pl8app_get_template_part('shortcode', 'receipt');

        $display = ob_get_clean();

        return $display;
    }

    /**
     * Item History Shortcode
     *
     * Displays a user's menuitem history.
     *
     * @since 1.0
     * @return string
     */
    public static function menuitem_history()
    {

        if (is_user_logged_in()) {

            ob_start();

            if (!pl8app_user_pending_verification()) {
                pl8app_get_template_part('history', 'menuitems');
            } else {
                pl8app_get_template_part('account', 'pending');
            }
            return ob_get_clean();
        }
    }

    /**
     * Order History Shortcode
     *
     * Displays a user's order history.
     *
     * @since 1.0
     * @return string
     */
    public static function order_history()
    {

        ob_start();

        if (!pl8app_user_pending_verification()) {
            pl8app_get_template_part('history', 'purchases');
        } else {
            pl8app_get_template_part('account', 'pending');
        }
        return ob_get_clean();
    }

    /**
     * Login Shortcode
     *
     * Shows a login form allowing users to users to log in. This function simply
     * calls the pl8app_login_form function to display the login form.
     *
     * @since 1.0
     * @param array $atts Shortcode attributes
     * @param string $content
     * @uses pl8app_login_form()
     * @return string
     */
    public static function pl8app_login($atts, $content = null)
    {

        $redirect = '';

        extract(shortcode_atts(array(
                'redirect' => $redirect
            ), $atts, 'pl8app_login')
        );

        if (empty($redirect)) {
            $login_redirect_page = pl8app_get_option('login_redirect_page', '');

            if (!empty($login_redirect_page)) {
                $redirect = get_permalink($login_redirect_page);
            }
        }

        if (empty($redirect)) {
            $order_history = pl8app_get_option('order_history_page', 0);

            if (!empty($order_history)) {
                $redirect = get_permalink($order_history);
            }
        }

        if (empty($redirect)) {
            $redirect = home_url();
        }

        return pl8app_login_form($redirect);
    }

    /**
     * Register Shortcode
     *
     * Shows a registration form allowing users to register for the site
     *
     * @since  1.0.0
     * @param array $atts Shortcode attributes
     * @param string $content
     * @uses pl8app_register_form()
     * @return string
     */
    public static function pl8app_register($atts, $content = null)
    {

        $redirect = home_url();
        $order_history = pl8app_get_option('order_history_page', 0);

        if (!empty($order_history)) {
            $redirect = get_permalink($order_history);
        }

        extract(shortcode_atts(array(
                'redirect' => $redirect
            ), $atts, 'pl8app_register')
        );
        return pl8app_register_form($redirect);
    }

    /**
     * Discounts shortcode
     *
     * Displays a list of all the active discounts. The active discounts can be configured
     * from the Discount Codes admin screen.
     *
     * @since 1.0
     * @param array $atts Shortcode attributes
     * @param string $content
     * @uses pl8app_get_discounts()
     * @return string $discounts_lists List of all the active discount codes
     */
    public static function menuitem_discounts($atts, $content = null)
    {

        $discounts = pl8app_get_discounts();

        $discounts_list = '<ul id="pl8app_discounts_list">';

        if (!empty($discounts) && pl8app_has_active_discounts()) {

            foreach ($discounts as $discount) {

                if (pl8app_is_discount_active($discount->ID)) {

                    $discounts_list .= '<li class="pl8app_discount">';

                    $discounts_list .= '<span class="pl8app_discount_name">' . pl8app_get_discount_code($discount->ID) . '</span>';
                    $discounts_list .= '<span class="pl8app_discount_separator"> - </span>';
                    $discounts_list .= '<span class="pl8app_discount_amount">' . pl8app_format_discount_rate(pl8app_get_discount_type($discount->ID), pl8app_get_discount_amount($discount->ID)) . '</span>';

                    $discounts_list .= '</li>';
                }
            }
        } else {

            $discounts_list .= '<li class="pl8app_discount">' . __('No discounts found', 'pl8app') . '</li>';
        }
        $discounts_list .= '</ul>';

        return $discounts_list;
    }

    /**
     * Purchase Collection Shortcode
     *
     * Displays a collection purchase link for adding all items in a taxonomy term
     * to the cart.
     *
     * @since 1.0.0
     * @param array $atts Shortcode attributes
     * @param string $content
     * @return string
     */
    public static function purchase_collection($atts, $content = null)
    {

        extract(shortcode_atts(array(
                'taxonomy' => '',
                'terms' => '',
                'text' => __('Purchase All Items', 'pl8app'),
                'style' => pl8app_get_option('button_style', 'button'),
                'color' => '',
                'class' => 'pl8app-submit'
            ), $atts, 'purchase_collection')
        );

        $button_display = implode(' ', array($style, $class));

        return '<a href="' . esc_url(add_query_arg(array('pl8app_action' => 'purchase_collection', 'taxonomy' => $taxonomy, 'terms' => $terms))) . '" class="' . $button_display . '">' . $text . '</a>';
    }

    /**
     * Profile Editor Shortcode
     *
     * Outputs the pl8app Profile Editor to allow users to amend their details from the
     * front-end. This function uses the pl8app templating system allowing users to
     * override the default profile editor template. The profile editor template is located
     * under templates/profile-editor.php, however, it can be altered by creating a
     * file called profile-editor.php in the pl8app_template directory in your active theme's
     * folder. Please visit the pl8app Documentation for more information on how the
     * templating system is used.
     *
     * @since  1.0.0
     *
     * @author pl8app
     *
     * @param      $atts Shortcode attributes
     * @param null $content
     * @return string Output generated from the profile editor
     */
    public static function pl8app_profile_editor($atts, $content = null)
    {

        ob_start();

        if (!pl8app_user_pending_verification()) {
            pl8app_get_template_part('shortcode', 'profile-editor');
        } else {
            pl8app_get_template_part('account', 'pending');
        }
        $display = ob_get_clean();

        return $display;
    }
}

add_action('init', array('pla_Shortcodes', 'init'));