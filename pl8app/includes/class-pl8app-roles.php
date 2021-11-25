<?php


// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * pl8app_Roles Class
 *
 * This class handles the role creation and assignment of capabilities for those roles.
 *
 * These roles let us have Shop Accountants, Shop Workers, etc, each of whom can do
 * certain things within the pl8app store
 *
 * @since  1.0.0
 */
class pl8app_Roles {

	/**
	 * Get things going
	 *
	 * @since  1.0.0
	 */
	public function __construct() {

        add_filter( 'editable_roles', array($this, 'wpse_shop_manager_editable_roles') );
		add_filter( 'map_meta_cap', array( $this, 'meta_caps' ), 10, 4 );
        add_filter( 'bulk_actions-users', array($this, 'remove_from_bulk_actions') );
	}
	public function remove_from_bulk_actions($actions){
        if ( $user = wp_get_current_user() ) {
            if ( !in_array( 'administrator', $user->roles ) ) {
                unset( $actions[ 'delete' ] );
            }
            return $actions;
        }
    }

	public function wpse_shop_manager_editable_roles($roles ){
        if ( $user = wp_get_current_user() ) {
            $allowed = $this->wpse_shop_manager_get_allowed_roles( $user );

            foreach ( $roles as $role => $caps ) {
                if ( ! in_array( $role, $allowed ) )
                    unset( $roles[ $role ] );
            }
        }
        return $roles;
    }
    /**
     * Helper function get getting roles that the user is allowed to create/edit/delete.
     *
     * @param   WP_User $user
     * @return  array
     */

	public function wpse_shop_manager_get_allowed_roles($user){
        $allowed = array();

        if ( in_array( 'administrator', $user->roles ) ) { // Admin can edit all roles
            $allowed = array_keys( $GLOBALS['wp_roles']->roles );
        } elseif ( in_array( 'shop_manager', $user->roles ) ) {
            $allowed[] = 'shop_worker';
            $allowed[] = 'shop_accountant';
        }

        return $allowed;
    }
	/**
	 * Add new shop roles with default WP caps
	 *
	 * @since  1.0.0
	 * @return void
	 */
	public function add_roles() {
	    remove_role('shop_manager');
        remove_role('shop_accountant');
        remove_role('shop_worker');
		add_role( 'shop_manager', __( 'Shop Manager', 'pl8app' ), array(
			'read'                   => true,
			'unfiltered_html'        => true,
			'upload_files'           => true,
			'export'                 => true,
			'import'                 => true,
			'manage_links'           => true,
			'publish_pages'          => true,
			'publish_posts'          => true,
			'read_private_pages'     => true,
			'read_private_posts'     => true,
            'list_users'             => true,
            'edit_users'             => true,
            'add_users'              => true,
            'create_users'           => true,
            'delete_users'           => true,
            'promote_users'          => true,
		) );

		add_role( 'shop_accountant', __( 'Shop Accountant', 'pl8app' ), array(
		    'read'                   => true,
		) );

		add_role( 'shop_worker', __( 'Shop Worker', 'pl8app' ), array(
			'read'                   => true,
		) );
	}

	/**
	 * Add new shop-specific capabilities
	 *
	 * @since  1.4.4
	 * @global WP_Roles $wp_roles
	 * @return void
	 */
	public function add_caps() {
		global $wp_roles;

		if ( class_exists('WP_Roles') ) {
			if ( ! isset( $wp_roles ) ) {
				$wp_roles = new WP_Roles();
			}
		}

		if ( is_object( $wp_roles ) ) {
			$wp_roles->add_cap( 'shop_manager', 'view_shop_reports' );
			$wp_roles->add_cap( 'shop_manager', 'view_shop_sensitive_data' );
			$wp_roles->add_cap( 'shop_manager', 'export_shop_reports' );
			$wp_roles->add_cap( 'shop_manager', 'manage_shop_settings' );
			$wp_roles->add_cap( 'shop_manager', 'manage_shop_discounts' );
            $wp_roles->add_cap( 'shop_manager', 'manage_store_location_settings' );
            $wp_roles->add_cap( 'shop_manager', 'foodsafety_tables' );

			$wp_roles->add_cap( 'administrator', 'view_shop_reports' );
			$wp_roles->add_cap( 'administrator', 'view_shop_sensitive_data' );
			$wp_roles->add_cap( 'administrator', 'export_shop_reports' );
			$wp_roles->add_cap( 'administrator', 'manage_shop_discounts' );
			$wp_roles->add_cap( 'administrator', 'manage_shop_settings' );

            $wp_roles->add_cap( 'shop_worker', 'foodsafety_tables' );

			// Add the main post type capabilities
			$capabilities = $this->get_core_caps();
			foreach ( $capabilities as $cap_group ) {
				foreach ( $cap_group as $cap ) {
					$wp_roles->add_cap( 'shop_manager', $cap );
					$wp_roles->add_cap( 'administrator', $cap );
					$wp_roles->add_cap( 'shop_worker', $cap );
				}
			}

			$wp_roles->add_cap( 'shop_accountant', 'read_private_products' );
			$wp_roles->add_cap( 'shop_accountant', 'view_shop_reports' );
			$wp_roles->add_cap( 'shop_accountant', 'export_shop_reports' );

		}
	}

	/**
	 * Gets the core post type capabilities
	 *
	 * @since  1.4.4
	 * @return array $capabilities Core post type capabilities
	 */
	public function get_core_caps() {
		$capabilities = array();

		$capability_types = array( 'product', 'shop_payment', 'shop_discount' );

		foreach ( $capability_types as $capability_type ) {
			$capabilities[ $capability_type ] = array(
				// Post type
				"edit_{$capability_type}",
				"read_{$capability_type}",
				"delete_{$capability_type}",
				"edit_{$capability_type}s",
				"edit_others_{$capability_type}s",
				"publish_{$capability_type}s",
				"read_private_{$capability_type}s",
				"delete_{$capability_type}s",
				"delete_private_{$capability_type}s",
				"delete_published_{$capability_type}s",
				"delete_others_{$capability_type}s",
				"edit_private_{$capability_type}s",
				"edit_published_{$capability_type}s",

				// Terms
				"manage_{$capability_type}_terms",
				"edit_{$capability_type}_terms",
				"delete_{$capability_type}_terms",
				"assign_{$capability_type}_terms",

				// Custom
				"view_{$capability_type}_stats",
				"import_{$capability_type}s",
			);
		}

		return $capabilities;
	}

	/**
	 * Map meta caps to primitive caps
	 *
	 * @since 1.0.0
	 * @return array $caps
	 */
	public function meta_caps( $caps, $cap, $user_id, $args ) {

		switch( $cap ) {

			case 'view_product_stats' :

				if( empty( $args[0] ) ) {
					break;
				}

				$menuitem = get_post( $args[0] );
				if ( empty( $menuitem ) ) {
					break;
				}

				if( user_can( $user_id, 'view_shop_reports' ) || $user_id == $menuitem->post_author ) {
					$caps = array();
				}

				break;
            case 'edit_user':
            case 'delete_user':
                if(isset($args)){
                    $the_user = get_userdata( $user_id ); // The user performing the task
                    $user     = get_userdata( $args[0] ); // The user being edited/deleted

                    if ( $the_user && $user && $the_user->ID != $user->ID /* User can always edit self */ ) {
                        $allowed = $this->wpse_shop_manager_get_allowed_roles( $the_user );

                        if ( array_diff( $user->roles, $allowed ) ) {
                            // Target user has roles outside of our limits
                            $caps[] = 'not_allowed';
                        }
                    }
                }
                break;
		}
		return $caps;

	}

	/**
	 * Remove core post type capabilities (called on uninstall)
	 *
	 * @since 1.0
	 * @return void
	 */
	public function remove_caps() {

		global $wp_roles;

		if ( class_exists( 'WP_Roles' ) ) {
			if ( ! isset( $wp_roles ) ) {
				$wp_roles = new WP_Roles();
			}
		}

		if ( is_object( $wp_roles ) ) {
			/** Shop Manager Capabilities */
			$wp_roles->remove_cap( 'shop_manager', 'view_shop_reports' );
			$wp_roles->remove_cap( 'shop_manager', 'view_shop_sensitive_data' );
			$wp_roles->remove_cap( 'shop_manager', 'export_shop_reports' );
			$wp_roles->remove_cap( 'shop_manager', 'manage_shop_discounts' );
			$wp_roles->remove_cap( 'shop_manager', 'manage_shop_settings' );
            $wp_roles->remove_cap( 'shop_manager', 'foodsafety_tables' );

			/** Site Administrator Capabilities */
			$wp_roles->remove_cap( 'administrator', 'view_shop_reports' );
			$wp_roles->remove_cap( 'administrator', 'view_shop_sensitive_data' );
			$wp_roles->remove_cap( 'administrator', 'export_shop_reports' );
			$wp_roles->remove_cap( 'administrator', 'manage_shop_discounts' );
			$wp_roles->remove_cap( 'administrator', 'manage_shop_settings' );

            $wp_roles->remove_cap( 'shop_worker', 'foodsafety_tables' );
			/** Remove the Main Post Type Capabilities */
			$capabilities = $this->get_core_caps();

			foreach ( $capabilities as $cap_group ) {
				foreach ( $cap_group as $cap ) {
					$wp_roles->remove_cap( 'shop_manager', $cap );
					$wp_roles->remove_cap( 'administrator', $cap );
					$wp_roles->remove_cap( 'shop_worker', $cap );
				}
			}

			/** Shop Accountant Capabilities */
			$wp_roles->remove_cap( 'shop_accountant', 'edit_products' );
			$wp_roles->remove_cap( 'shop_accountant', 'read_private_products' );
			$wp_roles->remove_cap( 'shop_accountant', 'view_shop_reports' );
			$wp_roles->remove_cap( 'shop_accountant', 'export_shop_reports' );

			/** Shop Vendor Capabilities */
			$wp_roles->remove_cap( 'shop_vendor', 'edit_product' );
			$wp_roles->remove_cap( 'shop_vendor', 'edit_products' );
			$wp_roles->remove_cap( 'shop_vendor', 'delete_product' );
			$wp_roles->remove_cap( 'shop_vendor', 'delete_products' );
			$wp_roles->remove_cap( 'shop_vendor', 'publish_products' );
			$wp_roles->remove_cap( 'shop_vendor', 'edit_published_products' );
			$wp_roles->remove_cap( 'shop_vendor', 'upload_files' );
		}
	}
}
