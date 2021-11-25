<?php


// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Registers and sets up the pl8app custom post type
 *
 * @since 1.0
 * @return void
 */
function pl8app_setup_pl8app_post_types() {

	$archives = defined( 'pl8app_DISABLE_ARCHIVE' ) && pl8app_DISABLE_ARCHIVE ? false : true;
	$slug     = defined( 'pl8app_SLUG' ) ? pl8app_SLUG : 'menuitems';
	$rewrite  = defined( 'pl8app_DISABLE_REWRITE' ) && pl8app_DISABLE_REWRITE ? false : array('slug' => $slug, 'with_front' => false);

	$menuitem_labels = apply_filters( 'pl8app_menuitem_labels', array(
		'name'                  => _x( '%2$s', 'menuitem post type name', 'pl8app' ),
		'singular_name'         => _x( '%1$s', 'singular menuitem post type name', 'pl8app' ),
		'add_new'               => __( 'Add New', 'pl8app' ),
		'add_new_item'          => __( 'Add New %1$s', 'pl8app'),
		'edit_item'             => __( 'Edit %1$s', 'pl8app' ),
		'new_item'              => __( 'New %1$s', 'pl8app' ),
		'all_items'             => __( 'All %2$s', 'pl8app' ),
		'view_item'             => __( 'View %1$s', 'pl8app' ),
		'search_items'          => __( 'Search %2$s', 'pl8app' ),
		'not_found'             => __( 'No %2$s found', 'pl8app' ),
		'not_found_in_trash'    => __( 'No %2$s found in Trash', 'pl8app' ),
		'parent_item_colon'     => '',
		'menu_name'             => _x( 'Menu Items', 'menuitem post type menu name', 'pl8app' ),
		'featured_image'        => __( '%1$s Image', 'pl8app' ),
		'set_featured_image'    => __( 'Set %1$s Image', 'pl8app' ),
		'remove_featured_image' => __( 'Remove %1$s Image', 'pl8app' ),
		'use_featured_image'    => __( 'Use as %1$s Image', 'pl8app' ),
		'attributes'            => __( '%1$s Attributes', 'pl8app' ),
		'filter_items_list'     => __( 'Filter %2$s list', 'pl8app' ),
		'items_list_navigation' => __( '%2$s list navigation', 'pl8app' ),
		'items_list'            => __( '%2$s list', 'pl8app' ),
	));

	foreach ( $menuitem_labels as $key => $value ) {
		$menuitem_labels[ $key ] = sprintf( $value, pl8app_get_label_singular(), pl8app_get_label_plural() );
	}

	$menuitem_args = array(
		'labels'             => $menuitem_labels,
		'public'             => false,
		'show_ui'            => true,
		'show_in_menu'       => true,
		'query_var'          => true,
		'rewrite'            => false,
		'capability_type'    => 'product',
		'map_meta_cap'       => true,
		'publicly_queryable' => false,
		'has_archive'        => false,
		'hierarchical'       => false,
		'supports'           => apply_filters( 'pl8app_menuitem_supports', array( 'title', 'editor', 'thumbnail', 'revisions', 'author' ) ),
	);
	register_post_type( 'menuitem', apply_filters( 'pl8app_menuitem_post_type_args', $menuitem_args ) );


	/** Payment Post Type */
	$payment_labels = array(
		'name'               => _x( 'Orders', 'post type general name', 'pl8app' ),
		'singular_name'      => _x( 'Order', 'post type singular name', 'pl8app' ),
		'add_new'            => __( 'Add New', 'pl8app' ),
		'add_new_item'       => __( 'Add New Order', 'pl8app' ),
		'edit_item'          => __( 'Edit Order', 'pl8app' ),
		'new_item'           => __( 'New Order', 'pl8app' ),
		'all_items'          => __( 'All Orders', 'pl8app' ),
		'view_item'          => __( 'View Order', 'pl8app' ),
		'search_items'       => __( 'Search Orders', 'pl8app' ),
		'not_found'          => __( 'No Orders found', 'pl8app' ),
		'not_found_in_trash' => __( 'No Orders found in Trash', 'pl8app' ),
		'parent_item_colon'  => '',
		'menu_name'          => __( 'Orders', 'pl8app' )
	);


	$payment_args = array(
		'labels'          => apply_filters( 'pl8app_payment_labels', $payment_labels ),
		'public'          => false,
		'query_var'       => false,
		'rewrite'         => false,
		'capability_type' => 'shop_payment',
		'map_meta_cap'    => true,
		'supports'        => array( 'title' ),
		'can_export'      => true
	);
	register_post_type( 'pl8app_payment', $payment_args );

	/** Discounts Post Type */
	$discount_labels = array(
		'name'               => _x( 'Discounts', 'post type general name', 'pl8app' ),
		'singular_name'      => _x( 'Discount', 'post type singular name', 'pl8app' ),
		'add_new'            => __( 'Add New', 'pl8app' ),
		'add_new_item'       => __( 'Add New Discount', 'pl8app' ),
		'edit_item'          => __( 'Edit Discount', 'pl8app' ),
		'new_item'           => __( 'New Discount', 'pl8app' ),
		'all_items'          => __( 'All Discounts', 'pl8app' ),
		'view_item'          => __( 'View Discount', 'pl8app' ),
		'search_items'       => __( 'Search Discounts', 'pl8app' ),
		'not_found'          => __( 'No Discounts found', 'pl8app' ),
		'not_found_in_trash' => __( 'No Discounts found in Trash', 'pl8app' ),
		'parent_item_colon'  => '',
		'menu_name'          => __( 'Discounts', 'pl8app' )
	);

	$discount_args = array(
		'labels'          => apply_filters( 'pl8app_discount_labels', $discount_labels ),
		'public'          => false,
		'query_var'       => false,
		'rewrite'         => false,
		'show_ui'         => false,
		'capability_type' => 'shop_discount',
		'map_meta_cap'    => true,
		'supports'        => array( 'title' ),
		'can_export'      => true
	);
	register_post_type( 'pl8app_discount', $discount_args );

}
add_action( 'init', 'pl8app_setup_pl8app_post_types', 1 );

/**
 * Get Default Labels
 *
 * @since 1.0
 * @return array $defaults Default labels
 */
function pl8app_get_default_labels() {
	$defaults = array(
	   'singular' => __( 'Menu Item', 'pl8app' ),
	   'plural'   => __( 'Menu Items','pl8app' )
	);
	return apply_filters( 'pl8app_default_menuitems_name', $defaults );
}

/**
 * Get Singular Label
 *
 * @since 1.0
 *
 * @param bool $lowercase
 * @return string $defaults['singular'] Singular label
 */
function pl8app_get_label_singular( $lowercase = false ) {
	$defaults = pl8app_get_default_labels();
	return ($lowercase) ? strtolower( $defaults['singular'] ) : $defaults['singular'];
}

/**
 * Get Plural Label
 *
 * @since 1.0
 * @return string $defaults['plural'] Plural label
 */
function pl8app_get_label_plural( $lowercase = false ) {
	$defaults = pl8app_get_default_labels();
	return ( $lowercase ) ? strtolower( $defaults['plural'] ) : $defaults['plural'];
}

/**
 * Change default "Enter title here" input
 *
 * @since  1.0.2
 * @param string $title Default title placeholder text
 * @return string $title New placeholder text
 */
function pl8app_change_default_title( $title ) {
	 // If a frontend plugin uses this filter (check extensions before changing this function)
	 if ( !is_admin() ) {
		$label = pl8app_get_label_singular();
		$title = sprintf( __( 'Enter %s name here', 'pl8app' ), $label );
		return $title;
	 }

	 $screen = get_current_screen();

	 if ( 'menuitem' == $screen->post_type ) {
		$label = pl8app_get_label_singular();
		$title = sprintf( __( 'Enter %s name here', 'pl8app' ), $label );
	 }

	 return $title;
}
add_filter( 'enter_title_here', 'pl8app_change_default_title' );

/**
 * Registers the custom taxonomies for the menuitems custom post type
 *
 * @since 1.0
 * @return void
*/
function pl8app_setup_menuitem_taxonomies() {

	$slug = defined( 'pl8app_SLUG' ) ? pl8app_SLUG : 'menuitems';

	$menu_category_label = array(
    'name'              => _x( 'Menu Category', 'taxonomy general name', 'pl8app' ),
    'singular_name'     => _x( 'Menu Category', 'taxonomy singular name', 'pl8app' ),
    'search_items'      => __( 'Search Menu Category', 'pl8app' ),
    'all_items'         => __( 'All Menu Category', 'pl8app' ),
    'parent_item'       => __( 'Parent Menu Category', 'textdomain' ),
    'parent_item_colon' => __( 'Parent Menu Category:', 'textdomain' ),
    'edit_item'         => __( 'Edit Menu Category', 'pl8app' ),
    'update_item'       => __( 'Update Menu Category', 'pl8app' ),
    'add_new_item'      => __( 'Add New Menu Category', 'pl8app' ),
    'new_item_name'     => __( 'New Menu Category', 'pl8app' ),
    'menu_name'         => __( 'Categories', 'pl8app' ),
  );

  $menu_item_args = array(
    'hierarchical' 		=> true,
    'tax_position' 		=> true,
    'show_admin_column' => true,
    'labels'            => $menu_category_label,
    'show_ui'           => true,
    'query_var'         => true,
    'rewrite'           => array( 'slug' => 'menu-category' ),
    'capabilities' => array( 'manage_terms' => 'manage_product_terms','edit_terms' => 'edit_product_terms','assign_terms' => 'assign_product_terms','delete_terms' => 'delete_product_terms' )
  );

  register_taxonomy( 'menu-category', array( 'menuitem' ), $menu_item_args );

  //Register taxonomy for menu category
  register_taxonomy_for_object_type( 'menu-category', 'menuitem' );

	/** Categories */
	$category_labels = array(
		'name'              => sprintf( _x( 'Options and Upgrades', 'taxonomy general name', 'pl8app' ), pl8app_get_label_singular() ),
		'singular_name'     => sprintf( _x( 'Options and Upgrades', 'taxonomy singular name', 'pl8app' ), pl8app_get_label_singular() ),
		'search_items'      => sprintf( __( 'Search Options and Upgrades', 'pl8app' ), pl8app_get_label_singular() ),
		'all_items'         => sprintf( __( 'All Options and Upgrades', 'pl8app' ), pl8app_get_label_singular() ),
		'parent_item'       => sprintf( __( 'Parent Options and Upgrades', 'pl8app' ), pl8app_get_label_singular() ),
		'parent_item_colon' => sprintf( __( 'Parent Options and Upgrades:', 'pl8app' ), pl8app_get_label_singular() ),
		'edit_item'         => sprintf( __( 'Edit Options and Upgrades', 'pl8app' ), pl8app_get_label_singular() ),
		'update_item'       => sprintf( __( 'Update Options and Upgrades', 'pl8app' ), pl8app_get_label_singular() ),
		'add_new_item'      => sprintf( __( 'Add New Options and Upgrades', 'pl8app' ), pl8app_get_label_singular() ),
		'new_item_name'     => sprintf( __( 'New Options and Upgrades Name', 'pl8app' ), pl8app_get_label_singular() ),
		'menu_name'         => __( 'Options and Upgrades', 'pl8app' ),
	);

	$category_args = apply_filters( 'pl8app_addon_category_args', array(
			'hierarchical' => true,
			'labels'       => apply_filters('pl8app_addon_category_labels', $category_labels),
			'show_ui'      => true,
			'show_admin_column' => false,
			'query_var'    => 'addon_category',
			'rewrite'      => array('slug' => $slug . '/category', 'with_front' => false, 'hierarchical' => true ),
			'capabilities' => array( 'manage_terms' => 'manage_product_terms','edit_terms' => 'edit_product_terms','assign_terms' => 'assign_product_terms','delete_terms' => 'delete_product_terms' )
		)
	);
	register_taxonomy( 'addon_category', array('menuitem'), $category_args );
	register_taxonomy_for_object_type( 'addon_category', 'menuitem' );


}
add_action( 'init', 'pl8app_setup_menuitem_taxonomies', 0 );

/**
 * Get the singular and plural labels for a menuitem taxonomy
 *
 * @since 1.0
 * @param  string $taxonomy The Taxonomy to get labels for
 * @return array            Associative array of labels (name = plural)
 */
function pl8app_get_taxonomy_labels( $taxonomy = 'addon_category' ) {
	$allowed_taxonomies = apply_filters( 'pl8app_allowed_menuitem_taxonomies', array( 'addon_category', 'menu-category' ) );

	if ( ! in_array( $taxonomy, $allowed_taxonomies ) ) {
		return false;
	}

	$labels   = array();
	$taxonomy = get_taxonomy( $taxonomy );

	if ( false !== $taxonomy ) {
		$singular  = $taxonomy->labels->singular_name;
		$name      = $taxonomy->labels->name;
		$menu_name = $taxonomy->labels->menu_name;

		$labels = array(
			'name'          => $name,
			'singular_name' => $singular,
			'menu_name'     => $menu_name,
		);
	}

	return apply_filters( 'pl8app_get_taxonomy_labels', $labels, $taxonomy );
}

/**
 * Registers Custom Post Statuses which are used by the Payments and Discount
 * Codes
 *
 * @since 1.0.9.1
 * @return void
 */
function pl8app_register_post_type_statuses() {
	// Payment Statuses
	register_post_status( 'refunded', array(
		'label'                     => _x( 'Refunded', 'Refunded payment status', 'pl8app' ),
		'public'                    => true,
		'exclude_from_search'       => false,
		'show_in_admin_all_list'    => true,
		'show_in_admin_status_list' => true,
		'label_count'               => _n_noop( 'Refunded <span class="count">(%s)</span>', 'Refunded <span class="count">(%s)</span>', 'pl8app' )
	) );

	register_post_status( 'paid', array(
		'label'                     => _x( 'Paid', 'Paid payment status', 'pl8app' ),
		'public'                    => true,
		'exclude_from_search'       => false,
		'show_in_admin_all_list'    => true,
		'show_in_admin_status_list' => true,
		'label_count'               => _n_noop( 'Paid <span class="count">(%s)</span>', 'Paid <span class="count">(%s)</span>', 'pl8app' )
	) );

	register_post_status( 'failed', array(
		'label'                     => _x( 'Failed', 'Failed payment status', 'pl8app' ),
		'public'                    => true,
		'exclude_from_search'       => false,
		'show_in_admin_all_list'    => true,
		'show_in_admin_status_list' => true,
		'label_count'               => _n_noop( 'Failed <span class="count">(%s)</span>', 'Failed <span class="count">(%s)</span>', 'pl8app' )
	)  );
	register_post_status( 'revoked', array(
		'label'                     => _x( 'Revoked', 'Revoked payment status', 'pl8app' ),
		'public'                    => true,
		'exclude_from_search'       => false,
		'show_in_admin_all_list'    => true,
		'show_in_admin_status_list' => true,
		'label_count'               => _n_noop( 'Revoked <span class="count">(%s)</span>', 'Revoked <span class="count">(%s)</span>', 'pl8app' )
	)  );
	register_post_status( 'abandoned', array(
		'label'                     => _x( 'Abandoned', 'Abandoned payment status', 'pl8app' ),
		'public'                    => true,
		'exclude_from_search'       => false,
		'show_in_admin_all_list'    => true,
		'show_in_admin_status_list' => true,
		'label_count'               => _n_noop( 'Abandoned <span class="count">(%s)</span>', 'Abandoned <span class="count">(%s)</span>', 'pl8app' )
	)  );
	register_post_status( 'processing', array(
		'label'                     => _x( 'Processing', 'Processing payment status', 'pl8app' ),
		'public'                    => true,
		'exclude_from_search'       => false,
		'show_in_admin_all_list'    => true,
		'show_in_admin_status_list' => true,
		'label_count'               => _n_noop( 'Processing <span class="count">(%s)</span>', 'Processing <span class="count">(%s)</span>', 'pl8app' )
	)  );

	// Discount Code Statuses
	register_post_status( 'active', array(
		'label'                     => _x( 'Active', 'Active discount code status', 'pl8app' ),
		'public'                    => true,
		'exclude_from_search'       => false,
		'show_in_admin_all_list'    => true,
		'show_in_admin_status_list' => true,
		'label_count'               => _n_noop( 'Active <span class="count">(%s)</span>', 'Active <span class="count">(%s)</span>', 'pl8app' )
	)  );
	register_post_status( 'inactive', array(
		'label'                     => _x( 'Inactive', 'Inactive discount code status', 'pl8app' ),
		'public'                    => true,
		'exclude_from_search'       => false,
		'show_in_admin_all_list'    => true,
		'show_in_admin_status_list' => true,
		'label_count'               => _n_noop( 'Inactive <span class="count">(%s)</span>', 'Inactive <span class="count">(%s)</span>', 'pl8app' )
	)  );

}
add_action( 'init', 'pl8app_register_post_type_statuses', 2 );

/**
 * Updated Messages
 *
 * Returns an array of with all updated messages.
 *
 * @since 1.0
 * @param array $messages Post updated message
 * @return array $messages New post updated messages
 */
function pl8app_updated_messages( $messages ) {
	global $post, $post_ID;

	$url1 = '<a href="' . get_permalink( $post_ID ) . '">';
	$url2 = pl8app_get_label_singular();
	$url3 = '</a>';

	$messages['menuitem'] = array(
		1 => sprintf( __( '%2$s updated. %1$sView %2$s%3$s.', 'pl8app' ), $url1, $url2, $url3 ),
		4 => sprintf( __( '%2$s updated. %1$sView %2$s%3$s.', 'pl8app' ), $url1, $url2, $url3 ),
		6 => sprintf( __( '%2$s published. %1$sView %2$s%3$s.', 'pl8app' ), $url1, $url2, $url3 ),
		7 => sprintf( __( '%2$s saved. %1$sView %2$s%3$s.', 'pl8app' ), $url1, $url2, $url3 ),
		8 => sprintf( __( '%2$s submitted. %1$sView %2$s%3$s.', 'pl8app' ), $url1, $url2, $url3 )
	);

	return $messages;
}
add_filter( 'post_updated_messages', 'pl8app_updated_messages' );

/**
 * Updated bulk messages
 *
 * @since 1.0
 * @param array $bulk_messages Post updated messages
 * @param array $bulk_counts Post counts
 * @return array $bulk_messages New post updated messages
 */
function pl8app_bulk_updated_messages( $bulk_messages, $bulk_counts ) {
	$singular = pl8app_get_label_singular();
	$plural   = pl8app_get_label_plural();

	$bulk_messages['menuitem'] = array(
		'updated'   => sprintf( _n( '%1$s %2$s updated.', '%1$s %3$s updated.', $bulk_counts['updated'], 'pl8app' ), $bulk_counts['updated'], $singular, $plural ),
		'locked'    => sprintf( _n( '%1$s %2$s not updated, somebody is editing it.', '%1$s %3$s not updated, somebody is editing them.', $bulk_counts['locked'], 'pl8app' ), $bulk_counts['locked'], $singular, $plural ),
		'deleted'   => sprintf( _n( '%1$s %2$s permanently deleted.', '%1$s %3$s permanently deleted.', $bulk_counts['deleted'], 'pl8app' ), $bulk_counts['deleted'], $singular, $plural ),
		'trashed'   => sprintf( _n( '%1$s %2$s moved to the Trash.', '%1$s %3$s moved to the Trash.', $bulk_counts['trashed'], 'pl8app' ), $bulk_counts['trashed'], $singular, $plural ),
		'untrashed' => sprintf( _n( '%1$s %2$s restored from the Trash.', '%1$s %3$s restored from the Trash.', $bulk_counts['untrashed'], 'pl8app' ), $bulk_counts['untrashed'], $singular, $plural )
	);

	return $bulk_messages;
}
add_filter( 'bulk_post_updated_messages', 'pl8app_bulk_updated_messages', 10, 2 );

/**
 * Add row actions for the menuitems custom post type
 *
 * @since  1.0.0
 * @param  array $actions
 * @param  WP_Post $post
 * @return array
 */
function  pl8app_menuitem_row_actions( $actions, $post ) {
	if ( 'menuitem' === $post->post_type ) {
		return array_merge( array( 'id' => 'ID: ' . $post->ID ), $actions );
	}

	return $actions;
}
add_filter( 'post_row_actions', 'pl8app_menuitem_row_actions', 2, 100 );
