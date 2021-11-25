<?php


// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Returns default pl8app pl8app meta fields.
 *
 * @since  1.0.0
 * @return array $fields Array of fields.
 */
function pl8app_menuitem_metabox_fields() {

	$fields = array(
			'_pl8app_product_type',
			'pl8app_price',
			'pl8app_menuitem_vat',
			'_variable_pricing',
			'_pl8app_price_options_mode',
			'pl8app_variable_prices',
			'_pl8app_purchase_text',
			'_pl8app_purchase_style',
			'_pl8app_purchase_color',
			'_pl8app_bundled_products',
            '_pl8app_bundled_discount',
			'_pl8app_hide_purchase_link',
			'_pl8app_menuitem_tax_exclusive',
			'_pl8app_button_behavior',
			'_pl8app_quantities_disabled',
			'pl8app_product_notes',
			'_pl8app_default_price_id',
			'_pl8app_bundled_products_conditions',
            'pl8app_item_stock',
            'pl8app_item_stock_enable'
		);

	if ( pl8app_use_skus() ) {
		$fields[] = 'pl8app_sku';
	}

	return apply_filters( 'pl8app_metabox_fields_save', $fields );
}

/**
 * Save post meta when the save_post action is called
 *
 * @since 1.0
 * @param int $post_id pl8app (Post) ID
 * @global array $post All the data of the the current post
 * @return void
 */
function pl8app_menuitem_meta_box_save( $post_id, $post ) {

	if ( ! isset( $_POST['pl8app_menuitem_meta_box_nonce'] ) || ! wp_verify_nonce( wp_unslash($_POST['pl8app_menuitem_meta_box_nonce']), 'pl8app_save_meta_data')) {
		return;
	}

	if ( ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) || ( defined( 'DOING_AJAX') && DOING_AJAX ) || isset( $_REQUEST['bulk_edit'] ) ) {

		return;
	}

	if ( isset( $post->post_type ) && 'revision' == $post->post_type ) {
		return;
	}

	if ( ! current_user_can( 'edit_product', $post_id ) ) {
		return;
	}
	// The default fields that get saved
	$fields = pl8app_menuitem_metabox_fields();

	foreach ( $fields as $field ) {
		if ( '_pl8app_default_price_id' == $field && pl8app_has_variable_prices( $post_id ) ) {

			if ( isset( $_POST[ $field ] ) ) {
				$new_default_price_id = ( ! empty( $_POST[ $field ] ) && is_numeric( $_POST[ $field ] ) ) || ( 0 === (int) $_POST[ $field ] ) ? (int) $_POST[ $field ] : 1;
			} else {
				$new_default_price_id = 1;
			}

			update_post_meta( $post_id, $field, $new_default_price_id );

		}
		else if ('pl8app_menuitem_vat' == $field){
            if ( isset( $_POST[ $field ] ) ) {
                update_post_meta( $post_id, 'pl8app_menuitem_vat', $_POST[ $field ] );
            } else {
                delete_post_meta( $post_id, 'pl8app_menuitem_vat' );
            }
        }
		else {
			if ( ! empty( $_POST[ $field ] ) ) {
				$new = apply_filters( 'pl8app_metabox_save_' . $field, $_POST[ $field ] );
				update_post_meta( $post_id, $field, $new );
			} else {
				delete_post_meta( $post_id, $field );
			}
		}
	}

	if ( pl8app_has_variable_prices( $post_id ) ) {
		$lowest = pl8app_get_lowest_price_option( $post_id );
		update_post_meta( $post_id, 'pl8app_price', $lowest );
	}

	do_action( 'pl8app_save_menuitem', $post_id, $post );
}
add_action( 'save_post', 'pl8app_menuitem_meta_box_save', 10, 2 );

/**
 * Sanitize bundled products on save
 *
 * Ensures a user doesn't try and include a product's ID in the products bundled with that product
 *
 * @since  1.0.0
 *
 * @param array $products
 * @return array
 */
function pl8app_sanitize_bundled_products_save( $products = array() ) {

	global $post;

	$self = array_search( $post->ID, $products );

	if( $self !== false )
		unset( $products[ $self ] );

	return array_values( array_unique( $products ) );
}
//add_filter( 'pl8app_metabox_save__pl8app_bundled_products', 'pl8app_sanitize_bundled_products_save' );

/**
 * Don't save blank rows.
 *
 * When saving, check the price and file table for blank rows.
 * If the name of the price or file is empty, that row should not
 * be saved.
 *
 * @since  1.0.0
 * @param array $new Array of all the meta values
 * @return array $new New meta value with empty keys removed
 */
function pl8app_metabox_save_check_blank_rows( $new ) {

	foreach ( $new as $key => $value ) {
		if ( empty( $value['name'] ) && empty( $value['amount'] ) && empty( $value['file'] ) )
			unset( $new[ $key ] );
	}
	return $new;
}

/**
 * Alter the Add to post button in the media manager for menuitems
 *
 * @since 1.0
 * @param  array $strings Array of default strings for media manager
 * @return array          The altered array of strings for media manager
 */
function pl8app_menuitem_media_strings( $strings ) {
	global $post;

	if ( ! $post || $post->post_type !== 'menuitem' ) {
		return $strings;
	}

	$menuitems_object = get_post_type_object( 'menuitem' );
	$labels = $menuitems_object->labels;

	$strings['insertIntoPost'] = sprintf( __( 'Insert into %s', 'pl8app' ), strtolower( $labels->singular_name ) );

	return $strings;
}
add_filter( 'media_view_strings', 'pl8app_menuitem_media_strings', 10, 1 );

/**
 * Internal use only
 *
 * This function takes any hooked functions for pl8app_menuitem_price_table_head and re-registers them into the pl8app_menuitem_price_table_row
 * action. It will also de-register any original table_row data, so that labels appear before their setting, then re-registers the table_row.
 *
 * @since 1.0.0
 *
 * @param $arg1
 * @param $arg2
 * @param $arg3
 *
 * @return void
 */
function pl8app_hijack_pl8app_menuitem_price_table_head( $arg1, $arg2, $arg3 ) {

	global $wp_filter;

	$found_fields  = isset( $wp_filter['pl8app_menuitem_price_table_row'] )  ? $wp_filter['pl8app_menuitem_price_table_row']  : false;
	$found_headers = isset( $wp_filter['pl8app_menuitem_price_table_head'] ) ? $wp_filter['pl8app_menuitem_price_table_head'] : false;

	$re_register = array();

	if ( ! $found_fields && ! $found_headers ) {
		return;
	}

	foreach ( $found_fields->callbacks as $priority => $callbacks ) {
		if ( -1 === $priority ) {
			continue; // Skip our -1 priority so we don't break the interwebs
		}

		if ( is_object( $found_headers ) && property_exists( $found_headers, 'callbacks' ) && array_key_exists( $priority, $found_headers->callbacks ) ) {

			// De-register any row data.
			foreach ( $callbacks as $callback ) {
				$re_register[ $priority ][] = $callback;
				remove_action( 'pl8app_menuitem_price_table_row', $callback['function'], $priority, $callback['accepted_args'] );
			}

			// Register any header data.
			foreach( $found_headers->callbacks[ $priority ] as $callback ) {
				if ( is_callable( $callback['function'] ) ) {
					add_action( 'pl8app_menuitem_price_table_row', $callback['function'], $priority, 1 );
				}
			}
		}
	}

	// Now that we've re-registered our headers first...re-register the inputs
	foreach ( $re_register as $priority => $callbacks ) {
		foreach ( $callbacks as $callback ) {
			add_action( 'pl8app_menuitem_price_table_row', $callback['function'], $priority, $callback['accepted_args'] );
		}
	}
}
add_action( 'pl8app_menuitem_price_table_row', 'pl8app_hijack_pl8app_menuitem_price_table_head', -1, 3 );