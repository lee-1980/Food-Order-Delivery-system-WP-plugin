<?php


// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

if ( class_exists( 'pla_Admin_Menus', false ) ) {
	return new pla_Admin_Menus();
}


/**
 * pla_Admin_Menus Class.
 */
class pla_Admin_Menus {

	/**
	 * Hook in tabs.
	 */
	public function __construct() {
        add_action( 'admin_menu', array( $this, 'menu_order_count' ) );
		// Add menus.
		add_action( 'admin_menu', array( $this, 'admin_menu' ), 9 );
        add_action( 'admin_footer', array ($this, 'pl8app_admin_footer_for_notification'));

		
		//Custom menu ordering
		add_filter( 'custom_menu_order', '__return_true' );
		add_filter( 'menu_order', array( $this, 'menu_order' ) );
	}

    /**
     * Add the Content for Notification Modal
     *
     */

    public function pl8app_admin_footer_for_notification(){
        ?>
        <div id="pl8app_check_sound_notification" style="display: none">
            <p><?php echo __('To enable sound notification/autoprinting for new orders, close this modal!');?></p>
        </div>
        <div id="pl8app_new_order_print_content" style="display: none">
        </div>
        <input type="button" id="_pl8app_copies_per_print_start" style="visibility: hidden;">
        <input type="hidden" id="_pl8app_copies_per_print">
        <?php
    }
    /**
     * Adds the order processing count to the menu.
     */

    public function menu_order_count() {
        global $submenu;
        global $menu;

        if ( isset( $menu['55.3'] ) ) {
            // Add count if user has access.
            if ( current_user_can( 'edit_shop_payments' ) ) {
                $order_count = apply_filters( 'pl8app_menu_order_count', pl8app_get_order_count('pending'));

                if ( $order_count > 0 ) {
                    $count = sprintf(
                        '<span class="awaiting-mod update-plugins"><span class="pl8app-order-processing-count">%s</span></span>',
                        $order_count
                    );
                    $menu['55.3'] = array( sprintf( __( 'Orders %s' ), $count ), 'manage_shop_settings', 'pl8order', 'pl8order', 'menu-top menu-icon-generic toplevel_page_pl8order', 'toplevel_page_pl8order', 'dashicons-admin-generic' );
                }
            }
        }
    }
	/**
	 * Add menu items.
	 */
	public function admin_menu() {
		global $menu;

		$menu[] = array( '', 'read', 'separator-pl8app', '', 'wp-menu-separator pl8app' );

		$pl8app_payment 	= get_post_type_object( 'pl8app_payment' );
		$customer_view_role = apply_filters( 'pl8app_view_customers_role', 'manage_shop_settings' );


		//pl8app Order main Menu
        add_menu_page( __( 'pl8order', 'pl8app' ), __( 'Orders', 'pl8app' ), 'manage_shop_settings', 'pl8order', null, null, '55.3' );

        add_submenu_page( 'pl8order', __( 'Takeaway Orders', 'pl8app' ), __( 'Takeaway Orders', 'pl8app' ), 'edit_shop_payments', 'pl8app-payment-history', 'pl8app_payment_history_page', null , null );

        add_submenu_page( 'pl8order', __( 'Store Panel', 'pl8app' ), __( 'Store Panel', 'pl8app' ), 'edit_shop_payments', 'pl8app-payment-quick-entry', 'pl8app_payment_quick_entry', null , null );

        //pl8app Marketing main Menu
        add_menu_page( __( 'pl8marketing', 'pl8app' ), __( 'Marketing', 'pl8app' ), 'manage_shop_settings', 'pl8marketing', null, null, '55.5' );

        add_submenu_page( 'pl8marketing', __( 'Sitemap', 'pl8app' ), __( 'Sitemap ', 'pl8app' ), 'manage_shop_discounts', 'pl8app-sitemap', 'pl8app_sitemap_page' );

        add_submenu_page( 'pl8marketing', __( 'Discount Codes', 'pl8app' ), __( 'Discount Codes', 'pl8app' ), 'manage_shop_discounts', 'pl8app-discounts', 'pl8app_discounts_page' );

//        add_submenu_page( 'pl8marketing', __( 'Reviews Feed', 'pl8app' ), __( 'Reviews Feed ', 'pl8app' ), 'manage_shop_discounts', 'reviews-feed', 'pl8app_reviews_feed' );


		//Pl8app main menu:

		add_menu_page( __( 'pl8app', 'pl8app' ), __( 'Sales Data', 'pl8app' ), 'manage_shop_settings', 'pl8app', null, null, '55.5' );

		add_submenu_page( 'pl8app', __( 'Earnings and Sales Reports', 'pl8app' ), __( 'Reports', 'pl8app' ), 'view_shop_reports', 'pl8app-reports', 'pl8app_reports_page' );

//		add_submenu_page( 'pl8app', __( 'pl8app Settings', 'pl8app' ), __( 'Settings', 'pl8app' ), 'manage_shop_settings', 'pl8app-settings', 'pl8app_options_page' );

		add_submenu_page( 'pl8app', __( 'pl8app Info and Tools', 'pl8app' ), __( 'Tools', 'pl8app' ), 'manage_shop_settings', 'pl8app-tools', 'pl8app_tools_page' );

		//your Store pl8app menu
        add_menu_page( __( 'Yspl8app', 'pl8app' ), __( 'Your Store pl8app', 'pl8app' ), 'manage_shop_settings', 'Yspl8app', null, null, '55.5' );

        add_submenu_page( 'Yspl8app', __( 'pl8app Store Location', 'pl8app' ), __( 'Store Location', 'pl8app' ), 'manage_shop_settings', 'pl8app-store-location', 'pl8app_store_location_page' );

        add_submenu_page( 'Yspl8app', __( 'pl8app Store Opening Time', 'pl8app' ), __( 'Store Opening Time', 'pl8app' ), 'manage_shop_settings', 'pl8app-store-otime', 'pl8app_store_otime_page' );

        add_submenu_page( 'Yspl8app', __( 'pl8app Financial Settings', 'pl8app' ), __( 'Financial Settings', 'pl8app' ), 'manage_shop_settings', 'pl8app-financial-settings', 'pl8app_financial_settings_page' );

        add_submenu_page( 'Yspl8app', __( 'pl8app Store Emails', 'pl8app' ), __( 'Store Emails', 'pl8app' ), 'manage_shop_settings', 'pl8app-store-emails', 'pl8app_store_emails_page' );

        add_submenu_page( 'Yspl8app', __( 'Google reCaptcha', 'pl8app' ), __( 'Google reCaptcha', 'pl8app' ), 'manage_shop_settings', 'pl8app-form-recaptcha', 'pl8app_tools_form_recaptcha', null, null );

        add_submenu_page( 'Yspl8app', __( 'pl8app Appearance', 'pl8app' ), __( 'Appearance', 'pl8app' ), 'manage_shop_settings', 'pl8app-appearance', 'pl8app_appearance_page' );

        add_submenu_page( 'Yspl8app', __( 'pl8app FAQ', 'pl8app' ), __( 'FAQ', 'pl8app' ), 'manage_shop_settings', 'pl8app-faq', 'pl8app_faq_page' );

        add_submenu_page( 'Yspl8app', __( 'pl8app Delivery, Returns and Refunds', 'pl8app' ), __( 'Delivery, Returns and Refunds', 'pl8app' ), 'manage_shop_settings', 'pl8app-delivery-refund', 'pl8app_delivery_refund_page' );

        add_submenu_page( 'Yspl8app', __( 'pl8app Privacy Policy', 'pl8app' ), __( 'Privacy Policy', 'pl8app' ), 'manage_shop_settings', 'pl8app-privacy', 'pl8app_privacy_page' );

        add_submenu_page( 'Yspl8app', __( 'pl8app Order Notification', 'pl8app' ), __( 'Order Notification', 'pl8app' ), 'manage_shop_settings', 'pl8app-order-notification', 'pl8app_order_notification' );

        add_submenu_page( 'Yspl8app', __( 'pl8app Checkout Options', 'pl8app' ), __( 'Checkout Options', 'pl8app' ), 'manage_shop_settings', 'pl8app-checkout-options', 'pl8app_checkout_options' );

        add_submenu_page( 'Yspl8app', __( 'pl8app Printer Settings', 'pl8app' ), __( 'Printer Settings', 'pl8app' ), 'manage_shop_settings', 'pl8app-printer-settings', 'pl8app_printer_settings' );

        //Your service menu
        add_menu_page( __( 'servicepl8app', 'pl8app' ), __( 'Your Service', 'pl8app' ), 'manage_shop_settings', 'servicepl8app', null, null, '55.5' );

        add_submenu_page( 'servicepl8app', __( 'pl8app General Settings', 'pl8app' ), __( 'General Settings', 'pl8app' ), 'manage_shop_settings', 'pl8app-general-settings', 'pl8app_general_settings_page' );

        add_submenu_page( 'servicepl8app', __( 'pl8app Pickup Options', 'pl8app' ), __( 'Pickup Options', 'pl8app' ), 'manage_shop_settings', 'pl8app-pickup-settings', 'pl8app_pickup_settings_page' );

        add_submenu_page( 'servicepl8app', __( 'pl8app Delivery Options', 'pl8app' ), __( 'Delivery Options', 'pl8app' ), 'manage_shop_settings', 'pl8app-delivery-settings', 'pl8app_delivery_settings_page' );

		//add_submenu_page( 'pl8app', __( 'pl8app Extensions', 'pl8app' ), '<span style="color:#f39c12;">' . __( 'Extensions', 'pl8app' ) . '</span>', 'manage_shop_settings', 'pl8app-extensions', 'pl8app_extensions_page' );

        //Customer Management
        add_menu_page( __( 'cumgpl8app', 'pl8app' ), __( 'Customer Management', 'pl8app' ), 'manage_shop_settings', 'cumgpl8app', null, null, '55.5' );

        add_submenu_page( 'cumgpl8app', __( 'Customers', 'pl8app' ), __( 'Customers', 'pl8app' ), $customer_view_role, 'pl8app-customers', 'pl8app_customers_page', null, null );

        add_submenu_page( 'cumgpl8app', __( 'Customer No Shows and Blocking', 'pl8app' ), __( 'Customer No Shows and Blocking', 'pl8app' ), 'manage_shop_settings', 'pl8app-customers-noshow-blocking', 'pl8app_tools_banned_emails_display', null, null );

        //Menu Items Menu

        add_submenu_page('edit.php?post_type=menuitem',  __( 'pl8app Inventory Setting', 'pl8app' ), __( 'Inventory Setting', 'pl8app' ), 'manage_shop_settings', 'pl8app-inventory-settings', 'pl8app_inventory_settings_page' );
        add_submenu_page('edit.php?post_type=menuitem',  __( 'pl8app MenuItem Import/Export', 'pl8app' ), __( 'MenuItem Import/Export', 'pl8app' ), 'manage_shop_settings', 'pl8app-menuitem-export-import', 'pl8app_menuitem_ex_import' );

		// Remove the additional pl8app menu
        remove_submenu_page( 'pl8marketing', 'pl8marketing' );
        remove_submenu_page( 'pl8order', 'pl8order' );
		remove_submenu_page( 'pl8app', 'pl8app' );
        remove_submenu_page( 'Yspl8app', 'Yspl8app' );
        remove_submenu_page( 'servicepl8app', 'servicepl8app' );
        remove_submenu_page( 'cumgpl8app', 'cumgpl8app' );

	}

	/**
	 * Reorder the WC menu items in admin.
	 *
	 * @param int $menu_order Menu order.
	 * @return array
	 */
	public function menu_order( $menu_order ) {

		// Initialize our custom order array.
		$pl8app_menu_order = array();

		// Get the index of our custom separator.
		$pl8app_separator = array_search( 'separator-pl8app', $menu_order, true );

		// Get index of menuitem menu.
		$pl8app_menuitems = array_search( 'edit.php?post_type=menuitem', $menu_order, true );

		//Remove the custom separator and menuitems menu so that we can re-order them
		unset( $menu_order[ $pl8app_separator ] );
		unset( $menu_order[ $pl8app_menuitems ] );

		// Loop through menu order and do some rearranging.
		foreach ( $menu_order as $index => $item ) {

			if ( 'pl8app' === $item ) {
				$pl8app_menu_order[] = 'separator-pl8app';
				$pl8app_menu_order[] = $item;
				$pl8app_menu_order[] = 'edit.php?post_type=menuitem';
			} elseif ( ! in_array( $item, array( 'separator-pl8app' ), true ) ) {
				$pl8app_menu_order[] = $item;
			}
		}
		// Return order.
		return $pl8app_menu_order;
	}
}

return new pla_Admin_Menus();
