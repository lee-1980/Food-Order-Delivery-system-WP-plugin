<?php
/**
 * Menu Items Page
 *
 * This template can be overridden by copying it to yourtheme/pl8app/menuitem/menuitems.php.
 *
 * @package pl8app/Templates
 * @version 1.0.0
 */

defined( 'ABSPATH' ) || exit;

?>

<div class="pl8app-section pl8app-col-lg-12 pl8app-col-md-12 pl8app-col-sm-12 pl8app-col-xs-12">

	<?php

  $shortcode_atts = pla_Shortcode_Menuitems::$atts;

	$category_ids = $all_terms = $query = [];

  if ( $shortcode_atts['category'] || $shortcode_atts['category_menu'] ) {

    if ( $shortcode_atts['category'] ) {
      $categories = explode( ',', $shortcode_atts['category'] );
    }

    if ( $shortcode_atts['category_menu'] ) {
      $categories = explode( ',', $shortcode_atts['category_menu'] );
    }

    foreach( $categories as $category ) {

      $is_id = is_int( $category ) && ! empty( $category );

      if ( $is_id ) {

        $term_id = $category;

      }
      else {

        $term = get_term_by( 'slug', $category, 'menu-category' );

        if( ! $term ) {
          continue;
        }

        $term_id = $term->term_id;

        }

        $category_ids[] = $term_id;
      }
    }

    $category_params = array(
      'orderby'         => !empty( $shortcode_atts['cat_orderby'] ) ? $shortcode_atts['cat_orderby'] : '',
      'order'           => !empty( $shortcode_atts['cat_order'] ) ? $shortcode_atts['cat_order'] : '' ,
      'ids'             => $category_ids,
      'category_menu'   => !empty( $shortcode_atts['category_menu'] ) ? true : false,
    );

		do_action( 'pl8app_get_menu_categories' );

		do_action( 'pla_get_categories', $category_params );

		?>
	<div class="pl8app_menuitems_list pl8app-col-lg-6 pl8app-col-md-6 pl8app-col-sm-9 pl8app-col-xs-12">

		<?php do_action( 'before_menuitems_list' );

    $get_categories = pl8app_get_categories( $category_params );

    if ( !empty( $shortcode_atts['category_menu'] ) ) {
      $get_categories = pl8app_get_child_cats( $category_ids );
    }

		$all_terms = array();

    if( is_array( $get_categories ) && !empty( $get_categories ) ) {
    	$all_terms = wp_list_pluck( $get_categories, 'slug' );
    }

    if ( is_array( $all_terms ) && !empty( $all_terms ) ) :

      foreach ( $all_terms as $term_slug ) :

        $prepared_query = pla_Shortcode_Menuitems::query($term_slug);
        $atts 			    = pla_Shortcode_Menuitems::$atts;

        // Allow the query to be manipulated by other plugins
        $query = apply_filters( 'pl8app_menuitems_query', $prepared_query, $atts );

        $menuitems = new WP_Query( $query );

        do_action( 'pl8app_menuitems_list_before', $atts );

        if ( $menuitems->have_posts() ) :

          $i = 1;

          do_action( 'pl8app_menuitems_list_top', $atts, $menuitems );
	        $curr_cat_var = '';

          while ( $menuitems->have_posts() ) : $menuitems->the_post();

            $id = get_the_ID();

            do_action( 'pl8app_menuitems_category_title', $term_slug, $id, $curr_cat_var );

            do_action( 'pl8app_menuitem_shortcode_item', $atts, $i );

            $i++;

          endwhile;

          wp_reset_postdata();

          do_action( 'pl8app_menuitems_list_bottom', $atts );

          wp_reset_query();

        endif;

      endforeach;

	    else:

	    	/* translators: %s: post singular name */
	    	printf( _x( 'No %s found', 'pl8app post type name', 'pl8app' ), pla_get_label_plural() );

	    endif;

	    ?>

	</div>

	<?php do_action( 'pl8app_menuitems_list_after', $atts, $menuitems ); ?>

	<?php do_action( 'pl8app_get_cart' ); ?>

</div>

<div class="pl8app-section pl8app-col-lg-12 pl8app-col-md-12 pl8app-col-sm-12 pl8app-col-xs-12">
    <div class="pl8app-store-location-map">
        <div id="pl8app-store-osgmap-container">
        </div>
    </div>
</div>