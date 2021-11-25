<?php
/**
 * Pl8App setup
 *
 * @package Pl8App
 * @since   1.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Main Pl8App Class.
 *
 * @class Pl8App
 */

final class pl8app {

    /**
     * Pl8App version.
     *
     * @var string
     */

    public $version = '1.0.0';

    /**
     * The single instance of the class.
     *
     * @var Pl8App
     * @since  1.0
     */
    private static $instance;

    /**
     * pl8app Roles Object.
     *
     * @var object|pl8app_Roles
     * @since 1.0
     */
    public $roles;

    /**
     * pl8app Cart Fees Object.
     *
     * @var object|pl8app_Fees
     * @since 1.0
     */
    public $fees;

    /**
     * pl8app HTML Session Object.
     *
     * This holds cart items, purchase sessions, and anything else stored in the session.
     *
     * @var object|pl8app_Session
     * @since 1.0
     */
    public $session;

    /**
     * pl8app HTML Element Helper Object.
     *
     * @var object|pl8app_HTML_Elements
     * @since 1.0
     */
    public $html;

    /**
     * pl8app Emails Object.
     *
     * @var object|pl8app_Emails
     * @since  1.0.0
     */
    public $emails;

    /**
     * pl8app Email Template Tags Object.
     *
     * @var object|pl8app_Email_Template_Tags
     * @since  1.0.0
     */
    public $email_tags;

    /**
     * pl8app Customers DB Object.
     *
     * @var object|pl8app_DB_Customers
     * @since  1.0.0
     */
    public $customers;

    /**
     * pl8app Customer meta DB Object.
     *
     * @var object|pl8app_DB_Customer_Meta
     * @since 1.0.0
     */
    public $customer_meta;

    /**
     * pl8app Cart Object
     *
     * @var object|pl8app_Cart
     * @since 1.0
     */
    public $cart;

    /**
     * Main Pl8App Instance.
     *
     * Insures that only one instance of Pl8App exists in memory at any one
     * time. Also prevents needing to define globals all over the place.
     *
     * @since  1.0.0
     * @static
     * @staticvar array $instance
     * @uses Pl8App::setup_constants() Setup the constants needed.
     * @uses Pl8App::includes() Include the required files.
     * @uses Pl8App::load_textdomain() load the language files.
     * @see PL8PRESS()
     * @return object|Pl8App The one true Pl8App
     */

    public static function instance() {
        if ( ! isset( self::$instance ) && ! ( self::$instance instanceof Pl8App ) ) {
            self::$instance = new pl8app;

            self::$instance->includes();
            self::$instance->roles         = new pl8app_Roles();
            self::$instance->fees          = new pl8app_Fees();
            self::$instance->session       = new pl8app_Session();
            self::$instance->html          = new pl8app_HTML_Elements();
            self::$instance->emails        = new pl8app_Emails();
            self::$instance->email_tags    = new pl8app_Email_Template_Tags();
            self::$instance->customers     = new pl8app_DB_Customers();
            self::$instance->customer_meta = new pl8app_DB_Customer_Meta();
            self::$instance->payment_stats = new pl8app_Payment_Stats();
            self::$instance->cart          = new pl8app_Cart();
        }

        return self::$instance;
    }

    /**
     * Cloning is forbidden.
     *
     * @since 2.1
     */
    public function __clone() {
        _doing_it_wrong( __FUNCTION__, __( 'Cloning is forbidden.', 'pl8app' ), '2.6.1' );
    }

    /**
     * Unserializing instances of this class is forbidden.
     *
     * @since 2.1
     */
    public function __wakeup() {
        _doing_it_wrong( __FUNCTION__, __( 'Unserializing instances of this class is forbidden.', 'pl8app' ), '2.6.1' );
    }

    /**
     * Pl8App Constructor.
     */
    public function __construct() {
        define( 'PL8_VERSION', $this->version );
        define( 'PL8_PLUGIN_DIR', plugin_dir_path( PL8_PLUGIN_FILE ) );
        define( 'PL8_PLUGIN_URL', plugin_dir_url( PL8_PLUGIN_FILE ) );
        !defined( 'CAL_GREGORIAN')? define('CAL_GREGORIAN', 1): '';
    }

    /**
     * What type of request is this?
     *
     * @param  string $type admin, ajax, cron or frontend.
     * @return bool
     */
    private function is_request( $type ) {
        switch ( $type ) {
            case 'admin':
                return is_admin();
            case 'ajax':
                return defined( 'DOING_AJAX' );
            case 'cron':
                return defined( 'DOING_CRON' );
            case 'frontend':
                return ( ! is_admin() || defined( 'DOING_AJAX' ) ) && ! defined( 'DOING_CRON' );
        }
    }

    /**
     * Include required core files used in admin and on the frontend.
     */

    public function includes() {

        global $pl8app_options;

        require_once PL8_PLUGIN_DIR . 'includes/admin/settings/register-settings.php';

        $pl8app_options = pl8app_get_settings();

        require_once PL8_PLUGIN_DIR . 'includes/pl8app-actions.php';

        if( file_exists( PL8_PLUGIN_DIR . 'includes/deprecated-functions.php' ) ) {
            require_once PL8_PLUGIN_DIR . 'includes/deprecated-functions.php';
        }

        require_once PL8_PLUGIN_DIR . 'includes/libraries/wp_bootstrap_navwalker.php';
        require_once PL8_PLUGIN_DIR . 'includes/pl8app-ajax-functions.php';
        include_once PL8_PLUGIN_DIR . 'includes/class-pl8app-ajax.php';
        require_once PL8_PLUGIN_DIR . 'includes/template-functions.php';
        require_once PL8_PLUGIN_DIR . 'includes/template-actions.php';
        require_once PL8_PLUGIN_DIR . 'includes/checkout/template.php';
        require_once PL8_PLUGIN_DIR . 'includes/checkout/functions.php';
        require_once PL8_PLUGIN_DIR . 'includes/cart/class-pl8app-cart.php';
        require_once PL8_PLUGIN_DIR . 'includes/cart/functions.php';
        require_once PL8_PLUGIN_DIR . 'includes/cart/template.php';
        require_once PL8_PLUGIN_DIR . 'includes/cart/actions.php';
        require_once PL8_PLUGIN_DIR . 'includes/class-pl8app-db.php';
        require_once PL8_PLUGIN_DIR . 'includes/class-pl8app-db-customers.php';
        require_once PL8_PLUGIN_DIR . 'includes/class-pl8app-db-customer-meta.php';
        require_once PL8_PLUGIN_DIR . 'includes/class-pl8app-customer-query.php';
        require_once PL8_PLUGIN_DIR . 'includes/class-pl8app-customer.php';
        require_once PL8_PLUGIN_DIR . 'includes/class-pl8app-license-handler.php';

        require_once PL8_PLUGIN_DIR . 'includes/class-pl8app-discount.php';
        require_once PL8_PLUGIN_DIR . 'includes/class-pl8app-menuitem.php';
        require_once PL8_PLUGIN_DIR . 'includes/class-pl8app-cache-helper.php';

        require_once PL8_PLUGIN_DIR . 'includes/class-pl8app-cron.php';
        require_once PL8_PLUGIN_DIR . 'includes/class-pl8app-fees.php';
        require_once PL8_PLUGIN_DIR . 'includes/class-pl8app-html-elements.php';

        require_once PL8_PLUGIN_DIR . 'includes/class-pl8app-logging.php';
        require_once PL8_PLUGIN_DIR . 'includes/class-pl8app-session.php';
        require_once PL8_PLUGIN_DIR . 'includes/class-pl8app-stats.php';
        require_once PL8_PLUGIN_DIR . 'includes/class-pl8app-roles.php';
        require_once PL8_PLUGIN_DIR . 'includes/country-functions.php';
        require_once PL8_PLUGIN_DIR . 'includes/formatting.php';
        require_once PL8_PLUGIN_DIR . 'includes/pl8app-core-functions.php';
        require_once PL8_PLUGIN_DIR . 'includes/gateways/actions.php';
        require_once PL8_PLUGIN_DIR . 'includes/gateways/functions.php';


        require_once PL8_PLUGIN_DIR . 'includes/gateways/paypal-standard.php';
        require_once PL8_PLUGIN_DIR . 'includes/gateways/manual.php';

        //Add frontend discount functionality
        require_once PL8_PLUGIN_DIR . 'includes/discount-functions.php';

        require_once PL8_PLUGIN_DIR . 'includes/admin/orders/actions.php';
        require_once PL8_PLUGIN_DIR . 'includes/payments/functions.php';
        require_once PL8_PLUGIN_DIR . 'includes/payments/actions.php';
        require_once PL8_PLUGIN_DIR . 'includes/payments/class-payment-stats.php';
        require_once PL8_PLUGIN_DIR . 'includes/payments/class-payments-query.php';
        require_once PL8_PLUGIN_DIR . 'includes/payments/class-pl8app-payment.php';
        require_once PL8_PLUGIN_DIR . 'includes/menuitem-functions.php';
        require_once PL8_PLUGIN_DIR . 'includes/post-types.php';
        require_once PL8_PLUGIN_DIR . 'includes/plugin-compatibility.php';
        require_once PL8_PLUGIN_DIR . 'includes/emails/class-pl8app-emails.php';
        require_once PL8_PLUGIN_DIR . 'includes/emails/class-pl8app-email-tags.php';
        require_once PL8_PLUGIN_DIR . 'includes/emails/email-tags.php';
        require_once PL8_PLUGIN_DIR . 'includes/emails/functions.php';
        require_once PL8_PLUGIN_DIR . 'includes/emails/template.php';
        require_once PL8_PLUGIN_DIR . 'includes/emails/actions.php';
        require_once PL8_PLUGIN_DIR . 'includes/error-tracking.php';
        require_once PL8_PLUGIN_DIR . 'includes/user-functions.php';
        require_once PL8_PLUGIN_DIR . 'includes/query-filters.php';
        require_once PL8_PLUGIN_DIR . 'includes/tax-functions.php';
        require_once PL8_PLUGIN_DIR . 'includes/process-purchase.php';
        require_once PL8_PLUGIN_DIR . 'includes/login-register.php';
        // Must be loaded on frontend to ensure cron runs
        require_once PL8_PLUGIN_DIR . 'includes/admin/tracking.php';
        require_once PL8_PLUGIN_DIR . 'includes/privacy-functions.php';
        require_once PL8_PLUGIN_DIR . 'includes/shortcodes.php';


        /**
         * Migrating 3.0 Features to 2.x
         *
         * @since 2.4.2
         */
        include_once PL8_PLUGIN_DIR . 'includes/class-pl8app-shortcodes.php';
        include_once PL8_PLUGIN_DIR . 'includes/shortcodes/class-shortcode-menuitems.php';

        if ( $this->is_request( 'admin' ) || ( defined( 'WP_CLI' ) && WP_CLI ) ) {

            /**
             * Migrating 3.0 Features to 2.x
             *
             * @since 2.4.2
             */
            include_once PL8_PLUGIN_DIR . 'includes/admin/includes-pl8app-admin.php';

            require_once PL8_PLUGIN_DIR . 'includes/admin/admin-actions.php';
            require_once PL8_PLUGIN_DIR . 'includes/admin/class-pl8app-notices.php';
            require_once PL8_PLUGIN_DIR . 'includes/admin/admin-pages.php';
            require_once PL8_PLUGIN_DIR . 'includes/admin/dashboard-widgets.php';
            require_once PL8_PLUGIN_DIR . 'includes/admin/menuitems/dashboard-columns.php';
            require_once PL8_PLUGIN_DIR . 'includes/admin/customers/customers.php';
            require_once PL8_PLUGIN_DIR . 'includes/admin/customers/customer-functions.php';
            require_once PL8_PLUGIN_DIR . 'includes/admin/customers/customer-actions.php';
            require_once PL8_PLUGIN_DIR . 'includes/admin/menuitems/metabox.php';
            require_once PL8_PLUGIN_DIR . 'includes/admin/menuitems/contextual-help.php';

            // Add admin discount codes
            require_once PL8_PLUGIN_DIR . 'includes/admin/discounts/discount-actions.php';
            require_once PL8_PLUGIN_DIR . 'includes/admin/discounts/discount-codes.php';
            require_once PL8_PLUGIN_DIR . 'includes/admin/sitemap/sitemap.php';
            require_once PL8_PLUGIN_DIR . 'includes/admin/import/import-actions.php';
            require_once PL8_PLUGIN_DIR . 'includes/admin/import/import-functions.php';
            require_once PL8_PLUGIN_DIR . 'includes/admin/payments/actions.php';
            require_once PL8_PLUGIN_DIR . 'includes/admin/payments/payments-history.php';
            require_once PL8_PLUGIN_DIR . 'includes/admin/payments/quick-entry.php';
            require_once PL8_PLUGIN_DIR . 'includes/admin/payments/contextual-help.php';
            require_once PL8_PLUGIN_DIR . 'includes/admin/reporting/contextual-help.php';
            require_once PL8_PLUGIN_DIR . 'includes/admin/reporting/export/export-functions.php';
            require_once PL8_PLUGIN_DIR . 'includes/admin/reporting/reports.php';
            require_once PL8_PLUGIN_DIR . 'includes/admin/reporting/class-pl8app-graph.php';
            require_once PL8_PLUGIN_DIR . 'includes/admin/reporting/class-pl8app-pie-graph.php';
            require_once PL8_PLUGIN_DIR . 'includes/admin/reporting/graphing.php';
            require_once PL8_PLUGIN_DIR . 'includes/admin/settings/display-settings.php';
            require_once PL8_PLUGIN_DIR . 'includes/admin/store_location/display-store-location.php';
            require_once PL8_PLUGIN_DIR . 'includes/admin/financial/financial-setting.php';
            require_once PL8_PLUGIN_DIR . 'includes/admin/store_emails/store-emails.php';
            require_once PL8_PLUGIN_DIR . 'includes/admin/appearance/pl8app-appearance.php';
            require_once PL8_PLUGIN_DIR . 'includes/admin/appearance/pl8app-faq.php';
            require_once PL8_PLUGIN_DIR . 'includes/admin/appearance/pl8app-delivery-privacy.php';
            require_once PL8_PLUGIN_DIR . 'includes/admin/settings/contextual-help.php';
            require_once PL8_PLUGIN_DIR . 'includes/admin/tools.php';
            require_once PL8_PLUGIN_DIR . 'includes/admin/plugins.php';
            require_once PL8_PLUGIN_DIR . 'includes/admin/upgrades/upgrade-functions.php';
            require_once PL8_PLUGIN_DIR . 'includes/admin/upgrades/upgrades.php';
            require_once PL8_PLUGIN_DIR . 'includes/admin/class-pl8app-heartbeat.php';
            require_once PL8_PLUGIN_DIR . 'includes/admin/tools/tools-actions.php';


        }

        if ( $this->is_request( 'frontend' ) ) {
            $this->frontend_includes();
        }

        require_once PL8_PLUGIN_DIR . 'includes/class-pl8app-register-meta.php';
        require_once PL8_PLUGIN_DIR . 'includes/install.php';
        require_once PL8_PLUGIN_DIR . 'includes/class-pl8app-tax-order.php';
    }

    /**
     * Include required frontend files.
     */
    public function frontend_includes() {
        include_once PL8_PLUGIN_DIR . 'includes/class-pl8app-frontend-scripts.php';
    }

}