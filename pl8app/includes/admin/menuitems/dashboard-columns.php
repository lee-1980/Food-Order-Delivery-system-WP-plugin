<?php
/**
 * Dashboard Columns
 *
 * @package     pl8app
 * @subpackage  Admin/pl8app
 * @copyright
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * pl8app Columns
 *
 * Defines the custom columns and their order
 *
 * @since 1.0
 * @param array $menuitem_columns Array of menuitem columns
 * @return array $menuitem_columns Updated array of menuitem columns for pl8app
 *  Post Type List Table
 */
function pl8app_menuitem_columns( $menuitem_columns ) {
	$category_labels = pl8app_get_taxonomy_labels( 'menu-category' );
//	$tag_labels      = pl8app_get_taxonomy_labels( 'menuitem_tag' );

	$menuitem_columns = array(
		'cb'                => '<input type="checkbox"/>',
		'title'             => __( 'Name', 'pl8app' ),
		'menu_category' 	=> $category_labels['menu_name'],
//		'menuitem_tag'      => $tag_labels['menu_name'],
		'price'             => __( 'Price', 'pl8app' ),
		'earnings'          => __( 'Earnings', 'pl8app' ),
		'date'              => __( 'Date', 'pl8app' )
	);

	return apply_filters( 'pl8app_menuitem_columns', $menuitem_columns );
}
add_filter( 'manage_edit-menuitem_columns', 'pl8app_menuitem_columns' );

/**
 * Render MenuItem Columns
 *
 * @since 1.0
 * @param string $column_name Column name
 * @param int $post_id MenuItem (Post) ID
 * @return void
 */
function pl8app_render_menuitem_columns( $column_name, $post_id ) {
	if ( get_post_type( $post_id ) == 'menuitem' ) {
		switch ( $column_name ) {
			case 'menu_category':
				echo get_the_term_list( $post_id, 'menu-category', '', ', ', '');
				break;
			case 'menuitem_tag':
				echo get_the_term_list( $post_id, 'menuitem_tag', '', ', ', '');
				break;
			case 'price':
				if ( pl8app_has_variable_prices( $post_id ) ) {
					echo pl8app_price_range( $post_id );
				} else {
					echo pl8app_price( $post_id, false );
					echo '<input type="hidden" class="menuitemprice-' . $post_id . '" value="' . pl8app_get_menuitem_price( $post_id ) . '" />';
				}
				break;
			case 'sales':
				if ( current_user_can( 'view_product_stats', $post_id ) ) {
					echo '<a href="' . esc_url( admin_url( 'admin.php?page=pl8app-reports&tab=logs&view=sales&menuitem=' . $post_id ) ) . '">';
						echo pl8app_get_menuitem_sales_stats( $post_id );
					echo '</a>';
				} else {
					echo '-';
				}
				break;
			case 'earnings':
				if ( current_user_can( 'view_product_stats', $post_id ) ) {
					echo '<a href="' . esc_url( admin_url( 'admin.php?page=pl8app-reports&view=menuitems&menuitem-id=' . $post_id ) ) . '">';
						echo pl8app_currency_filter( pl8app_format_amount( pl8app_get_menuitem_earnings_stats( $post_id ) ) );
					echo '</a>';
				} else {
					echo '-';
				}
				break;
		}
	}
}
add_action( 'manage_posts_custom_column', 'pl8app_render_menuitem_columns', 10, 2 );

/**
 * Registers the sortable columns in the list table
 *
 * @since 1.0
 * @param array $columns Array of the columns
 * @return array $columns Array of sortable columns
 */
function pl8app_sortable_menuitem_columns( $columns ) {
	$columns['price']    = 'price';
	$columns['sales']    = 'sales';
	$columns['earnings'] = 'earnings';

	return $columns;
}
add_filter( 'manage_edit-menuitem_sortable_columns', 'pl8app_sortable_menuitem_columns' );

/**
 * Sorts Columns in the pl8app List Table
 *
 * @since 1.0
 * @param array $vars Array of all the sort variables
 * @return array $vars Array of all the sort variables
 */
function pl8app_sort_menuitems( $vars ) {
	// Check if we're viewing the "menuitem" post type
	if ( isset( $vars['post_type'] ) && 'menuitem' == $vars['post_type'] ) {
		// Check if 'orderby' is set to "sales"
		if ( isset( $vars['orderby'] ) && 'sales' == $vars['orderby'] ) {
			$vars = array_merge(
				$vars,
				array(
					'meta_key' => '_pl8app_menuitem_sales',
					'orderby'  => 'meta_value_num'
				)
			);
		}

		// Check if "orderby" is set to "earnings"
		if ( isset( $vars['orderby'] ) && 'earnings' == $vars['orderby'] ) {
			$vars = array_merge(
				$vars,
				array(
					'meta_key' => '_pl8app_menuitem_earnings',
					'orderby'  => 'meta_value_num'
				)
			);
		}

		// Check if "orderby" is set to "earnings"
		if ( isset( $vars['orderby'] ) && 'price' == $vars['orderby'] ) {
			$vars = array_merge(
				$vars,
				array(
					'meta_key' => 'pl8app_price',
					'orderby'  => 'meta_value_num'
				)
			);
		}
	}

	return $vars;
}

/**
 * Sets restrictions on author of pl8app List Table
 *
 * @since 1.0
 * @param  array $vars Array of all sort varialbes
 * @return array       Array of all sort variables
 */
function pl8app_filter_menuitems( $vars ) {
	if ( isset( $vars['post_type'] ) && 'menuitem' == $vars['post_type'] ) {

		// If an author ID was passed, use it
		if ( isset( $_REQUEST['author'] ) && ! current_user_can( 'view_shop_reports' ) ) {

			$author_id = $_REQUEST['author'];
			if ( (int) $author_id !== get_current_user_id() ) {
				// Tried to view the products of another person, sorry
				wp_die( __( 'You do not have permission to view this data.', 'pl8app' ), __( 'Error', 'pl8app' ), array( 'response' => 403 ) );
			}
			$vars = array_merge(
				$vars,
				array(
					'author' => get_current_user_id()
				)
			);

		}

	}

	return $vars;
}

/**
 * pl8app Load
 *
 * Sorts the menuitems.
 *
 * @since 1.0
 * @return void
 */
function pl8app_menuitem_load() {
	add_filter( 'request', 'pl8app_sort_menuitems' );
	add_filter( 'request', 'pl8app_filter_menuitems' );
}
add_action( 'load-edit.php', 'pl8app_menuitem_load', 9999 );

/**
 * Add pl8app Filters
 *
 * Adds taxonomy drop down filters for menuitems.
 *
 * @since 1.0
 * @return void
 */
function pl8app_add_menuitem_filters() {
	global $typenow;

	// Checks if the current post type is 'menuitem'
	if ( $typenow == 'menuitem') {

		// Category Filters
		$terms = get_terms( 'menu-category' );
		if(count($terms) > 0) {
			echo "<select name='menu-category' id='menu-category' class='postform'>";
			$category_labels = pl8app_get_taxonomy_labels( 'menu-category' );
			echo "<option value=''>" . sprintf( __( 'Show all %s', 'pl8app' ), strtolower( $category_labels['name'] ) ) . "</option>";
			foreach ($terms as $term) {
				$selected = isset( $_GET['menu-category'] ) && $_GET['menu-category'] == $term->slug ? ' selected="selected"' : '';
				echo '<option value="' . esc_attr( $term->slug ) . '"' . $selected . '>' . esc_html( $term->name ) .' (' . $term->count .')</option>';
			}
			echo "</select>";
		}

		// Addons Filters
		$terms = get_terms( 'addon_category' );
		if ( count( $terms ) > 0 ) {
			echo "<select name='addon_category' id='addon_category' class='postform'>";
			$category_labels = pl8app_get_taxonomy_labels( 'addon_category' );
			echo "<option value=''>" . sprintf( __( 'Show all %s', 'pl8app' ), strtolower( $category_labels['name'] ) ) . "</option>";
			foreach ( $terms as $term ) {
				$selected = isset( $_GET['addon_category'] ) && $_GET['addon_category'] == $term->slug ? ' selected="selected"' : '';
				echo '<option value="' . esc_attr( $term->slug ) . '"' . $selected . '>' . esc_html( $term->name ) .' (' . $term->count .')</option>';
			}
			echo "</select>";
		}

		// Tags Filter
//		$terms = get_terms( 'menuitem_tag' );
//		if ( count( $terms ) > 0) {
//			echo "<select name='menuitem_tag' id='menuitem_tag' class='postform'>";
//			$tag_labels = pl8app_get_taxonomy_labels( 'menuitem_tag' );
//			echo "<option value=''>" . sprintf( __( 'Show all %s', 'pl8app' ), strtolower( $tag_labels['name'] ) ) . "</option>";
//			foreach ( $terms as $term ) {
//				$selected = isset( $_GET['menuitem_tag']) && $_GET['menuitem_tag'] == $term->slug ? ' selected="selected"' : '';
//				echo '<option value="' . esc_attr( $term->slug ) . '"' . $selected . '>' . esc_html( $term->name ) .' (' . $term->count .')</option>';
//			}
//			echo "</select>";
//		}

		if ( isset( $_REQUEST['all_posts'] ) && '1' === $_REQUEST['all_posts'] ) {
			echo '<input type="hidden" name="all_posts" value="1" />';
		} else if ( ! current_user_can( 'view_shop_reports' ) ) {
			$author_id = get_current_user_id();
			echo '<input type="hidden" name="author" value="' . esc_attr( $author_id ) . '" />';
		}
	}

}
add_action( 'restrict_manage_posts', 'pl8app_add_menuitem_filters', 100 );

/**
 * Remove pl8app Month Filter
 *
 * Removes the drop down filter for menuitems by date.
 *
 * @author pl8app
 * @since  1.0.0
 * @param array $dates The preset array of dates
 * @global $typenow The post type we are viewing
 * @return array Empty array disables the dropdown
 */
function pl8app_remove_month_filter( $dates ) {
	global $typenow;

	if ( $typenow == 'menuitem' ) {
		$dates = array();
	}

	return $dates;
}
add_filter( 'months_dropdown_results', 'pl8app_remove_month_filter', 99 );

/**
 * Adds price field to Quick Edit options
 *
 * @since  1.0.0
 * @param string $column_name Name of the column
 * @param string $post_type Current Post Type (i.e. menuitem)
 * @return void
 */
function pl8app_price_field_quick_edit( $column_name, $post_type ) {
	if ( $column_name != 'price' || $post_type != 'menuitem' ) return;
	?>
	<fieldset class="inline-edit-pl8app-col-left">
		<div id="pl8app-menuitem-data" class="inline-edit-col">
			<h4><?php echo sprintf( __( '%s Configuration', 'pl8app' ), pl8app_get_label_singular() ); ?></h4>
			<label>
				<span class="title"><?php _e( 'Price', 'pl8app' ); ?></span>
				<span class="input-text-wrap">
					<input type="text" name="_pl8app_regprice" class="text regprice" />
				</span>
			</label>
			<br class="clear" />
		</div>
	</fieldset>
	<?php
}
add_action( 'quick_edit_custom_box', 'pl8app_price_field_quick_edit', 10, 2 );
add_action( 'bulk_edit_custom_box', 'pl8app_price_field_quick_edit', 10, 2 );

/**
 * Updates price when saving post
 *
 * @since  1.0.0
 * @param int $post_id pl8app (Post) ID
 * @return void
 */
function pl8app_price_save_quick_edit( $post_id ) {
	if ( ! isset( $_POST['post_type']) || 'menuitem' !== $_POST['post_type'] ) return;
	if ( ! current_user_can( 'edit_post', $post_id ) ) return $post_id;
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return $post_id;

	if ( isset( $_REQUEST['_pl8app_regprice'] ) ) {
		update_post_meta( $post_id, 'pl8app_price', strip_tags( stripslashes( $_REQUEST['_pl8app_regprice'] ) ) );
	}
}
add_action( 'save_post', 'pl8app_price_save_quick_edit' );

/**
 * Process bulk edit actions via AJAX
 *
 * @since  1.0.0
 * @return void
 */
function pl8app_save_bulk_edit() {

	$post_ids = ( isset( $_POST['post_ids'] ) && ! empty( $_POST['post_ids'] ) ) ? $_POST['post_ids'] : array();

	if ( ! empty( $post_ids ) && is_array( $post_ids ) ) {
		$price = isset( $_POST['price'] ) ? strip_tags( stripslashes( $_POST['price'] ) ) : 0;
		foreach ( $post_ids as $post_id ) {

			if( ! current_user_can( 'edit_post', $post_id ) ) {
				continue;
			}

			if ( ! empty( $price ) ) {
				update_post_meta( $post_id, 'pl8app_price', pl8app_sanitize_amount( $price ) );
			}
		}
	}

	die();
}
add_action( 'wp_ajax_pl8app_save_bulk_edit', 'pl8app_save_bulk_edit' );

function add_addons_price_type_columns( $columns ) {
    $columns['price-type'] = 'Price/Type';
    $columns['sold-count'] = 'Sold Count';
    return $columns;
}
add_filter( 'manage_edit-addon_category_columns', 'add_addons_price_type_columns' );

function add_addons_price_type_column_content( $content, $column_name, $term_id ) {

    $term = get_term( $term_id, 'addon_category' );
    $term_meta = get_option( "taxonomy_term_$term->term_id" );
    switch ( $column_name ) {
        case 'price-type':
            if( $term->parent == 0 ) {
  				$use_addon_like =  isset($term_meta['use_it_like']) ? $term_meta['use_it_like'] : 'checkbox';
  				$content = $use_addon_like == 'checkbox' ? 'Multiple' : 'Single';
            } else {
            	$price = !empty( $term_meta['price'] ) ? $term_meta['price'] : '0';
            	$content = pl8app_currency_filter( pl8app_format_amount( $price ), pl8app_get_payment_currency_code() );
            }
            break;
        case 'sold-count':
            global $wpdb;
            $content = 0;
            $results = $wpdb->get_results( "SELECT meta_value FROM $wpdb->postmeta WHERE meta_key = '_pl8app_payment_meta'");
            foreach($results as $order){
                $cart_details = unserialize($order->meta_value)['cart_details'];
                if(isset($cart_details[0]['addon_items']) && is_array($cart_details[0]['addon_items'])){
                    foreach($cart_details[0]['addon_items'] as $key => $item){
                        if(is_array($item) && isset($item['addon_id']) && isset($item['quantity']) && $item['addon_id'] == $term_id){
                            $content += (int)$item['quantity'];
                        }
                    }
                }
            }
            break;
        default:
            break;
    }
    return $content;
}
add_filter( 'manage_addon_category_custom_column', 'add_addons_price_type_column_content', 10, 3 );