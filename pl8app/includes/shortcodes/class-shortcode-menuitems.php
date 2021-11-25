<?php


defined( 'ABSPATH' ) || exit;

/**
 * Shortcode Menu Items Class.
 */
class pla_Shortcode_Menuitems {

	/**
	 * Menu Items Attributes Shortcode
	 *
	 * @var array
	 * @since 1.0
	 */
	public static $atts = array();

	/**
	 * Prepare the Menu Items Queries.
	 *
	 * @param array $atts Shortcode attributes.
	 */
	public static function query( $term_slug ) {

		$atts = pla_Shortcode_Menuitems::$atts;

		$query = array(
            'post_type'      => 'menuitem',
            'post_status'    => 'publish',
            'posts_per_page' => -1,
            'orderby'        => $atts['menuitem_orderby'],
            'order'          => $atts['menuitem_order']
        );

        $query['tax_query'][] = array(
            'taxonomy' => 'menu-category',
            'field'    => 'slug',
            'terms'    => $term_slug,
        );

        if( ! empty( $atts['ids'] ) )
            $query['post__in'] = explode( ',', $atts['ids'] );

        return $query;
	}

	/**
	 * Output the Menu Items shortcode.
	 *
	 * @param array $atts Shortcode attributes.
	 */
	public static function output( $atts ) {

		if ( !apply_filters( 'pl8app_output_menuitem_shortcode_content', true ) ) {
			return;
		}

		$atts = shortcode_atts( array(
        'category'          => '',
        'category_menu'     => '',
        'menuitem_orderby'  => 'title',
        'menuitem_order'    => 'ASC',
        'relation'          => 'OR',
        'cat_orderby'       => 'include',
        'cat_order'         => 'ASC',
	    ), $atts, 'menuitems' );

	    pla_Shortcode_Menuitems::$atts = apply_filters( 'pl8app_set_menuitems_attributes', $atts );

	    pl8app_get_template_part( 'menuitem/menuitems' );
	}
}