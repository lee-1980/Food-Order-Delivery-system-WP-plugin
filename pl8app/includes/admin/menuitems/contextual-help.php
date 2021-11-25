<?php
/**
 * Contextual Help
 *
 * @package     pl8app
 * @subpackage  Admin/pl8app
 * @copyright
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since  1.0.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Adds the Contextual Help for the main pl8app page
 *
 * @since 1.0
 * @return void
 */
function pl8app_menuitems_contextual_help() {

	$screen = get_current_screen();

	if ( $screen->id != 'menuitem' )
		return;

	$screen->add_help_tab( array(
		'id'	    => 'pl8app-menuitem-prices',
		'title'	    => sprintf( __( '%s Prices', 'pl8app' ), pl8app_get_label_singular() ),
		'content'	=>
			'<p>' . __( '<strong>Enable variable pricing</strong> - By enabling variable pricing, multiple menuitem options and prices can be configured.', 'pl8app' ) . '</p>' .

			'<p>' . __( '<strong>Enable multi-option purchases</strong> - By enabling multi-option purchases customers can add multiple variable price items to their cart at once.', 'pl8app' ) . '</p>'
	) );

	$screen->add_help_tab( array(
		'id'	    => 'pl8app-product-notes',
		'title'	    => sprintf( __( '%s Notes', 'pl8app' ), pl8app_get_label_singular() ),
		'content'	=> '<p>' . __( 'Special notes or instructions for the product. These notes will be added to the purchase receipt, and additionally may be used by some extensions or themes on the frontend.', 'pl8app' ) . '</p>'
	) );

	$colors = array(
		'gray', 'pink', 'blue', 'green', 'teal', 'black', 'dark gray', 'orange', 'purple', 'slate'
	);

	$screen->add_help_tab( array(
		'id'	    => 'pl8app-purchase-shortcode',
		'title'	    => __( 'Purchase Shortcode', 'pl8app' ),
		'content'	=>
			'<p>' . __( '<strong>Purchase Shortcode</strong> - If the automatic output of the purchase button has been disabled via the pl8app Configuration box, a shortcode can be used to output the button or link.', 'pl8app' ) . '</p>' .
			'<p><code>[purchase_link id="#" price="1" text="Add to Cart" color="blue"]</code></p>' .
			'<ul>
				<li><strong>id</strong> - ' . __( 'The ID of a specific menuitem to purchase.', 'pl8app' ) . '</li>
				<li><strong>price</strong> - ' . __( 'Whether to show the price on the purchase button. 1 to show the price, 0 to disable it.', 'pl8app' ) . '</li>
				<li><strong>text</strong> - ' . __( 'The text to be displayed on the button or link.', 'pl8app' ) . '</li>
				<li><strong>style</strong> - ' . __( '<em>button</em> | <em>text</em> - The style of the purchase link.', 'pl8app' ) . '</li>
				<li><strong>color</strong> - <em>' . implode( '</em> | <em>', $colors ) . '</em></li>
				<li><strong>class</strong> - ' . __( 'One or more custom CSS classes you want applied to the button.', 'pl8app' ) . '</li>
			</ul>' .
			'<p>' . sprintf( __( 'For more information, see <a href="%s">using Shortcodes</a> on the WordPress.org Codex or <a href="%s">pl8app Support</a>', 'pl8app' ), 'https://codex.wordpress.org/Shortcode', 'support.pl8app.co.uk' ) . '</p>'
	) );

	/**
	 * Fires off in the pl8app pl8app Contextual Help Screen
	 *
	 * @since 1.0
	 * @param object $screen The current admin screen
	 */
	do_action( 'pl8app_menuitems_contextual_help', $screen );
}
add_action( 'load-post.php', 'pl8app_menuitems_contextual_help' );
add_action( 'load-post-new.php', 'pl8app_menuitems_contextual_help' );
