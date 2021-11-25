<?php


// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/*
|--------------------------------------------------------------------------
| FRONT-END WIDGETS
|--------------------------------------------------------------------------
|
| - Cart Widget
| - Categories / Tags Widget
|
*/

/**
 * Cart Widget.
 *
 * pl8app cart widget class.
 *
 * @since 1.0
 * @return void
*/
class pl8app_cart_widget extends WP_Widget {
	/** Constructor */
	function __construct() {
		parent::__construct( 'pl8app_cart_widget', __( 'pl8app Cart', 'pl8app' ), array( 'description' => __( 'Display the pl8app order totals', 'pl8app' ) ) );
		add_filter( 'dynamic_sidebar_params', array( $this, 'cart_widget_class' ), 10, 1 );
	}

	/** @see WP_Widget::widget */
	function widget( $args, $instance ) {

		if ( ! empty( $instance['hide_on_checkout'] ) && pl8app_is_checkout() ) {
			return;
		}

		$args['id']        = ( isset( $args['id'] ) ) ? $args['id'] : 'pl8app_cart_widget';
		$instance['title'] = ( isset( $instance['title'] ) ) ? $instance['title'] : '';

		$title = apply_filters( 'widget_title', $instance['title'], $instance, $args['id'] );

		echo $args['before_widget'];

		if ( $title ) {
			echo $args['before_title'] . $title . $args['after_title'];
		}

		do_action( 'pl8app_before_cart_widget' );

		pl8app_shopping_cart( true );

		do_action( 'pl8app_after_cart_widget' );

		echo $args['after_widget'];
	}

	/** @see WP_Widget::update */
	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;

		$instance['title']            = strip_tags( $new_instance['title'] );
		$instance['hide_on_checkout'] = isset( $new_instance['hide_on_checkout'] );
		$instance['hide_on_empty']    = isset( $new_instance['hide_on_empty'] );

		return $instance;
	}

	/** @see WP_Widget::form */
	function form( $instance ) {

		$defaults = array(
			'title'            => '',
			'hide_on_checkout' => false,
			'hide_on_empty'    => false,
		);

		$instance = wp_parse_args( (array) $instance, $defaults ); ?>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php _e( 'Title:', 'pl8app' ); ?></label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php echo $instance['title']; ?>"/>
		</p>

		<!-- Hide on Checkout Page -->
		<p>
			<input <?php checked( $instance['hide_on_checkout'], true ); ?> id="<?php echo esc_attr( $this->get_field_id( 'hide_on_checkout' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'hide_on_checkout' ) ); ?>" type="checkbox" />
			<label for="<?php echo esc_attr( $this->get_field_id( 'hide_on_checkout' ) ); ?>"><?php _e( 'Hide on Checkout Page', 'pl8app' ); ?></label>
		</p>

		<!-- Hide when cart is empty -->
		<p>
			<input <?php checked( $instance['hide_on_empty'], true ); ?> id="<?php echo esc_attr( $this->get_field_id( 'hide_on_empty' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'hide_on_empty' ) ); ?>" type="checkbox" />
			<label for="<?php echo esc_attr( $this->get_field_id( 'hide_on_empty' ) ); ?>"><?php _e( 'Hide if cart is empty', 'pl8app' ); ?></label>
		</p>

		<?php
	}

	/**
	 * Check if the widget needs to be hidden when empty.
	 *
	 * @since 2.7
	 * @param $params
	 *
	 * @return array
	 */
	public function cart_widget_class( $params ) {
		if ( strpos( $params[0]['widget_id'], 'pl8app_cart_widget' ) !== false ) {
			$instance_id       = $params[1]['number'];
			$all_settings      = $this->get_settings();
			$instance_settings = $all_settings[ $instance_id ];

			if ( ! empty( $instance_settings['hide_on_empty'] ) ) {
				$cart_quantity = pl8app_get_cart_quantity();
				$class         = empty( $cart_quantity ) ? 'cart-empty' : 'cart-not-empty';

				$params[0]['before_widget'] = preg_replace( '/class="(.*?)"/', 'class="$1 pl8app-hide-on-empty ' . $class . '"', $params[0]['before_widget'] );
			}
		}

		return $params;
	}

}

/**
 * Categories / Tags Widget.
 *
 * pl8app categories / tags widget class.
 *
 * @since 1.0
 * @return void
*/
class pl8app_categories_tags_widget extends WP_Widget {
	/** Constructor */
	function __construct() {
		parent::__construct( 'pl8app_categories_tags_widget', __( 'pl8app Categories / Tags', 'pl8app' ), array( 'description' => __( 'Display the menuitems categories or tags', 'pl8app' ) ) );
	}

	/** @see WP_Widget::widget */
	function widget( $args, $instance ) {
		// Set defaults.
		$args['id']           = ( isset( $args['id'] ) ) ? $args['id'] : 'pl8app_categories_tags_widget';
		$instance['title']    = ( isset( $instance['title'] ) ) ? $instance['title'] : '';
		$instance['taxonomy'] = ( isset( $instance['taxonomy'] ) ) ? $instance['taxonomy'] : 'addon_category';

		$title      = apply_filters( 'widget_title', $instance['title'], $instance, $args['id'] );
		$tax        = $instance['taxonomy'];
		$count      = isset( $instance['count'] ) && $instance['count'] == 'on' ? 1 : 0;
		$hide_empty = isset( $instance['hide_empty'] ) && $instance['hide_empty'] == 'on' ? 1 : 0;

		echo $args['before_widget'];

		if ( $title ) {
			echo $args['before_title'] . $title . $args['after_title'];
		}

		do_action( 'pl8app_before_taxonomy_widget' );

		echo "<ul class=\"pl8app-taxonomy-widget\">\n";
			wp_list_categories( 'title_li=&taxonomy=' . $tax . '&show_count=' . $count . '&hide_empty=' . $hide_empty );
		echo "</ul>\n";

		do_action( 'pl8app_after_taxonomy_widget' );

		echo $args['after_widget'];
	}

	/** @see WP_Widget::update */
	function update( $new_instance, $old_instance ) {
		$instance               = $old_instance;
		$instance['title']      = strip_tags( $new_instance['title'] );
		$instance['taxonomy']   = strip_tags( $new_instance['taxonomy'] );
		$instance['count']      = isset( $new_instance['count'] ) ? $new_instance['count'] : '';
		$instance['hide_empty'] = isset( $new_instance['hide_empty'] ) ? $new_instance['hide_empty'] : '';
		return $instance;
	}

	/** @see WP_Widget::form */
	function form( $instance ) {
		// Set up some default widget settings.
		$defaults = array(
			'title'         => '',
			'taxonomy'      => 'addon_category',
			'count'         => 'off',
			'hide_empty'    => 'off',
		);

		$instance = wp_parse_args( (array) $instance, $defaults ); ?>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php _e( 'Title:', 'pl8app' ); ?></label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php echo $instance['title']; ?>"/>
		</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'taxonomy' ) ); ?>"><?php _e( 'Taxonomy:', 'pl8app' ); ?></label>
			<select name="<?php echo esc_attr( $this->get_field_name( 'taxonomy' ) ); ?>" id="<?php echo esc_attr( $this->get_field_id( 'taxonomy' ) ); ?>">
				<?php
				$category_labels = pl8app_get_taxonomy_labels( 'addon_category' );
				$tag_labels      = pl8app_get_taxonomy_labels( 'menuitem_tag' );
				?>
				<option value="addon_category" <?php selected( 'addon_category', $instance['taxonomy'] ); ?>><?php echo $category_labels['name']; ?></option>
				<option value="menuitem_tag" <?php selected( 'menuitem_tag', $instance['taxonomy'] ); ?>><?php echo $tag_labels['name']; ?></option>
			</select>
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'count' ); ?>"><?php _e( 'Show Count:', 'pl8app' ); ?></label>
			<input <?php checked( $instance['count'], 'on' ); ?> id="<?php echo $this->get_field_id( 'count' ); ?>" name="<?php echo $this->get_field_name( 'count' ); ?>" type="checkbox" />
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'hide_empty' ); ?>"><?php _e( 'Hide Empty Categories:', 'pl8app' ); ?></label>
			<input <?php checked( $instance['hide_empty'], 'on' ); ?> id="<?php echo $this->get_field_id( 'hide_empty' ); ?>" name="<?php echo $this->get_field_name( 'hide_empty' ); ?>" type="checkbox" />
		</p>
	<?php
	}
}


/**
 * Product Details Widget.
 *
 * Displays a product's details in a widget.
 *
 * @since 1.9
 * @return void
 */
class pl8app_Product_Details_Widget extends WP_Widget {

	/** Constructor */
	public function __construct() {
		parent::__construct(
			'pl8app_product_details',
			sprintf( __( '%s Details', 'pl8app' ), pl8app_get_label_singular() ),
			array(
				'description' => sprintf( __( 'Display the details of a specific %s', 'pl8app' ), pl8app_get_label_singular() ),
			)
		);
	}

	/** @see WP_Widget::widget */
	public function widget( $args, $instance ) {
		$args['id'] = ( isset( $args['id'] ) ) ? $args['id'] : 'pl8app_menuitem_details_widget';

		if ( ! empty( $instance['menuitem_id'] ) ) {
			if ( 'current' === ( $instance['menuitem_id'] ) ) {
				$instance['display_type'] = 'current';
				unset( $instance['menuitem_id'] );
			} elseif ( is_numeric( $instance['menuitem_id'] ) ) {
				$instance['display_type'] = 'specific';
			}
		}

		if ( ! isset( $instance['display_type'] ) || ( 'specific' === $instance['display_type'] && ! isset( $instance['menuitem_id'] ) ) || ( 'current' == $instance['display_type'] && ! is_singular( 'menuitem' ) ) ) {
			return;
		}

		// set correct menuitem ID.
		if ( 'current' == $instance['display_type'] && is_singular( 'menuitem' ) ) {
			$menuitem_id = get_the_ID();
		} else {
			$menuitem_id = absint( $instance['menuitem_id'] );
		}

		// Since we can take a typed in value, make sure it's a menuitem we're looking for
		$menuitem = get_post( $menuitem_id );
		if ( ! is_object( $menuitem ) || 'menuitem' !== $menuitem->post_type ) {
			return;
		}

		// Variables from widget settings.
		$title           = apply_filters( 'widget_title', $instance['title'], $instance, $args['id'] );
		$menuitem_title  = $instance['menuitem_title'] ? apply_filters( 'pl8app_product_details_widget_menuitem_title', '<h3>' . get_the_title( $menuitem_id ) . '</h3>', $menuitem_id ) : '';
		$purchase_button = $instance['purchase_button'] ? apply_filters( 'pl8app_product_details_widget_purchase_button', pl8app_get_purchase_link( array( 'menuitem_id' => $menuitem_id ) ), $menuitem_id ) : '';
		$categories      = $instance['categories'] ? $instance['categories'] : '';
		$tags            = $instance['tags'] ? $instance['tags'] : '';

		// Used by themes. Opens the widget.
		echo $args['before_widget'];

		// Display the widget title.
		if( $title ) {
			echo $args['before_title'] . $title . $args['after_title'];
		}

		do_action( 'pl8app_product_details_widget_before_title' , $instance , $menuitem_id );

		// menuitem title.
		echo $menuitem_title;

		do_action( 'pl8app_product_details_widget_before_purchase_button' , $instance , $menuitem_id );
		// purchase button.
		echo $purchase_button;

		// categories and tags.
		$category_list  = false;
		$category_label = '';
		if ( $categories ) {

			$category_terms = get_the_terms( $menuitem_id, 'addon_category' );

			if ( $category_terms && ! is_wp_error( $category_terms ) ) {
				$category_list     = get_the_term_list( $menuitem_id, 'addon_category', '', ', ' );
				$category_count    = count( $category_terms );
				$category_labels   = pl8app_get_taxonomy_labels( 'addon_category' );
				$category_label    = $category_count > 1 ? $category_labels['name'] : $category_labels['singular_name'];
			}

		}

		$tag_list  = false;
		$tag_label = '';
		if ( $tags ) {

			$tag_terms = get_the_terms( $menuitem_id, 'menuitem_tag' );

			if ( $tag_terms && ! is_wp_error( $tag_terms ) ) {
				$tag_list     = get_the_term_list( $menuitem_id, 'menuitem_tag', '', ', ' );
				$tag_count    = count( $tag_terms );
				$tag_taxonomy = pl8app_get_taxonomy_labels( 'menuitem_tag' );
				$tag_label    = $tag_count > 1 ? $tag_taxonomy['name'] : $tag_taxonomy['singular_name'];
			}

		}


		$text = '';

		if( $category_list || $tag_list ) {
			$text .= '<p class="pl8app-meta">';

			if( $category_list ) {

				$text .= '<span class="categories">%1$s: %2$s</span><br/>';
			}

			if ( $tag_list ) {
				$text .= '<span class="tags">%3$s: %4$s</span>';
			}

			$text .= '</p>';
		}

		do_action( 'pl8app_product_details_widget_before_categories_and_tags', $instance, $menuitem_id );

		printf( $text, $category_label, $category_list, $tag_label, $tag_list );

		do_action( 'pl8app_product_details_widget_before_end', $instance, $menuitem_id );

		// Used by themes. Closes the widget.
		echo $args['after_widget'];
	}

	/** @see WP_Widget::form */
	public function form( $instance ) {
		// Set up some default widget settings.
		$defaults = array(
			'title'           => sprintf( __( '%s Details', 'pl8app' ), pl8app_get_label_singular() ),
			'display_type'    => 'current',
			'menuitem_id'     => false,
			'menuitem_title'  => 'on',
			'purchase_button' => 'on',
			'categories'      => 'on',
			'tags'            => 'on',
		);

		$instance = wp_parse_args( (array) $instance, $defaults ); ?>

		<?php
		if ( 'current' === ( $instance['menuitem_id'] ) ) {
			$instance['display_type'] = 'current';
			$instance['menuitem_id']  = false;
		} elseif ( is_numeric( $instance['menuitem_id'] ) ) {
			$instance['display_type'] = 'specific';
		}

		?>

		<!-- Title -->
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php _e( 'Title:', 'pl8app' ) ?></label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php echo $instance['title']; ?>" />
		</p>

		<p>
			<?php _e( 'Display Type:', 'pl8app' ); ?><br />
			<input type="radio" onchange="jQuery(this).parent().next('.menuitem-details-selector').hide();" <?php checked( 'current', $instance['display_type'], true ); ?> value="current" name="<?php echo esc_attr( $this->get_field_name( 'display_type' ) ); ?>" id="<?php echo esc_attr( $this->get_field_id( 'display_type' ) ); ?>-current"><label for="<?php echo esc_attr( $this->get_field_id( 'display_type' ) ); ?>-current"><?php _e( 'Current', 'pl8app' ); ?></label>
			<input type="radio" onchange="jQuery(this).parent().next('.menuitem-details-selector').show();" <?php checked( 'specific', $instance['display_type'], true ); ?> value="specific" name="<?php echo esc_attr( $this->get_field_name( 'display_type' ) ); ?>" id="<?php echo esc_attr( $this->get_field_id( 'display_type' ) ); ?>-specific"><label for="<?php echo esc_attr( $this->get_field_id( 'display_type' ) ); ?>-specific"><?php _e( 'Specific', 'pl8app' ); ?></label>
		</p>

		<!-- pl8app -->
		<?php $display = 'current' === $instance['display_type'] ? ' style="display: none;"' : ''; ?>
		<p class="menuitem-details-selector" <?php echo $display; ?>>
		<label for="<?php echo esc_attr( $this->get_field_id( 'menuitem_id' ) ); ?>"><?php printf( __( '%s:', 'pl8app' ), pl8app_get_label_singular() ); ?></label>
		<?php $menuitem_count = wp_count_posts( 'menuitem' ); ?>
		<?php if ( $menuitem_count->publish < 1000 ) : ?>
			<?php
			$args = array(
				'post_type'      => 'menuitem',
				'posts_per_page' => -1,
				'post_status'    => 'publish',
			);
			$menuitems = get_posts( $args );
			?>
			<select class="widefat" name="<?php echo esc_attr( $this->get_field_name( 'menuitem_id' ) ); ?>" id="<?php echo esc_attr( $this->get_field_id( 'menuitem_id' ) ); ?>">
			<?php foreach ( $menuitems as $menuitem ) { ?>
				<option <?php selected( absint( $instance['menuitem_id'] ), $menuitem->ID ); ?> value="<?php echo esc_attr( $menuitem->ID ); ?>"><?php echo $menuitem->post_title; ?></option>
			<?php } ?>
			</select>
		<?php else: ?>
			<br />
			<input type="text" value="<?php echo esc_attr( $instance['menuitem_id'] ); ?>" placeholder="<?php printf( __( '%s ID', 'pl8app' ), pl8app_get_label_singular() ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'menuitem_id' ) ); ?>" id="<?php echo esc_attr( $this->get_field_id( 'menuitem_id' ) ); ?>">
		<?php endif; ?>
		</p>

		<!-- Download title -->
		<p>
			<input <?php checked( $instance['menuitem_title'], 'on' ); ?> id="<?php echo esc_attr( $this->get_field_id( 'menuitem_title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'menuitem_title' ) ); ?>" type="checkbox" />
			<label for="<?php echo esc_attr( $this->get_field_id( 'menuitem_title' ) ); ?>"><?php printf( __( 'Show %s Title', 'pl8app' ), pl8app_get_label_singular() ); ?></label>
		</p>

		<!-- Show purchase button -->
		<p>
			<input <?php checked( $instance['purchase_button'], 'on' ); ?> id="<?php echo esc_attr( $this->get_field_id( 'purchase_button' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'purchase_button' ) ); ?>" type="checkbox" />
			<label for="<?php echo esc_attr( $this->get_field_id( 'purchase_button' ) ); ?>"><?php _e( 'Show Purchase Button', 'pl8app' ); ?></label>
		</p>

		<!-- Show menuitem categories -->
		<p>
			<?php $category_labels = pl8app_get_taxonomy_labels( 'addon_category' ); ?>
			<input <?php checked( $instance['categories'], 'on' ); ?> id="<?php echo esc_attr( $this->get_field_id( 'categories' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'categories' ) ); ?>" type="checkbox" />
			<label for="<?php echo esc_attr( $this->get_field_id( 'categories' ) ); ?>"><?php printf( __( 'Show %s', 'pl8app' ), $category_labels['name'] ); ?></label>
		</p>

		<!-- Show menuitem tags -->
		<p>
			<?php $tag_labels = pl8app_get_taxonomy_labels( 'menuitem_tag' ); ?>
			<input <?php checked( $instance['tags'], 'on' ); ?> id="<?php echo esc_attr( $this->get_field_id( 'tags' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'tags' ) ); ?>" type="checkbox" />
			<label for="<?php echo esc_attr( $this->get_field_id( 'tags' ) ); ?>"><?php printf( __( 'Show %s', 'pl8app' ), $tag_labels['name'] ); ?></label>
		</p>

		<?php do_action( 'pl8app_product_details_widget_form' , $instance ); ?>
	<?php }

	/** @see WP_Widget::update */
	public function update( $new_instance, $old_instance ) {
		$instance = $old_instance;

		$instance['title']           = strip_tags( $new_instance['title'] );
		$instance['menuitem_id']     = strip_tags( $new_instance['menuitem_id'] );
		$instance['display_type']    = isset( $new_instance['display_type'] )    ? strip_tags( $new_instance['display_type'] ) : '';
		$instance['menuitem_title']  = isset( $new_instance['menuitem_title'] )  ? $new_instance['menuitem_title']  : '';
		$instance['purchase_button'] = isset( $new_instance['purchase_button'] ) ? $new_instance['purchase_button'] : '';
		$instance['categories']      = isset( $new_instance['categories'] )      ? $new_instance['categories']      : '';
		$instance['tags']            = isset( $new_instance['tags'] )            ? $new_instance['tags']            : '';

		do_action( 'pl8app_product_details_widget_update', $instance );

		// If the new view is 'current menuitem' then remove the specific menuitem ID
		if ( 'current' === $instance['display_type'] ) {
			unset( $instance['menuitem_id'] );
		}

		return $instance;
	}

}



/**
 * Register Widgets.
 *
 * Registers the pl8app Widgets.
 *
 * @since 1.0
 * @return void
 */
function pl8app_register_widgets() {
	register_widget( 'pl8app_cart_widget' );
	register_widget( 'pl8app_categories_tags_widget' );
	register_widget( 'pl8app_product_details_widget' );
}
add_action( 'widgets_init', 'pl8app_register_widgets' );
