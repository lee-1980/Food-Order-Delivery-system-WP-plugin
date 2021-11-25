<?php


// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Disables admin sorting of Post Types Order
 *
 * When sorting menuitems by price, earnings, sales, date, or name,
 * we need to remove the posts_orderby that Post Types Order imposes
 *
 * @since  1.0.0
 * @return void
 */
function pl8app_remove_post_types_order() {
	remove_filter( 'posts_orderby', 'CPTOrderPosts' );
}
add_action( 'load-edit.php', 'pl8app_remove_post_types_order' );

/**
 * Disables opengraph tags on the checkout page
 *
 * There is a bizarre conflict that makes the checkout errors not get displayed
 * when the Jetpack opengraph tags are displayed
 *
 * @since 1.0.0.1
 * @return bool
 */
function pl8app_disable_jetpack_og_on_checkout() {
	if ( pl8app_is_checkout() ) {
		remove_action( 'wp_head', 'jetpack_og_tags' );
	}
}
add_action( 'template_redirect', 'pl8app_disable_jetpack_og_on_checkout' );

/**
 * Checks if a caching plugin is active
 *
 * @since 1.0
 * @return bool $caching True if caching plugin is enabled, false otherwise
 */
function pl8app_is_caching_plugin_active() {
	$caching = ( function_exists( 'wpsupercache_site_admin' ) || defined( 'W3TC' ) || function_exists( 'rocket_init' ) );
	return apply_filters( 'pl8app_is_caching_plugin_active', $caching );
}

/**
 * Adds a ?nocache option for the checkout page
 *
 * This ensures the checkout page remains uncached when plugins like WP Super Cache are activated
 *
 * @since 1.0
 * @param array $settings Misc Settings
 * @return array $settings Updated Misc Settings
 */
function pl8app_append_no_cache_param( $settings ) {
	if ( ! pl8app_is_caching_plugin_active() )
		return $settings;

	$settings[] = array(
		'id' => 'no_cache_checkout',
		'name' => __('No Caching on Checkout?','pl8app' ),
		'desc' => __('Check this box in order to append a ?nocache parameter to the checkout URL to prevent caching plugins from caching the page.','pl8app' ),
		'type' => 'checkbox'
	);

	return $settings;
}
add_filter( 'pl8app_settings_misc', 'pl8app_append_no_cache_param', -1 );

/**
 * Show the correct language on the [menuitems] shortcode if qTranslate is active
 *
 * @since  1.0.0
 * @param string $content 
 * @return string $content 
 */
function pl8app_qtranslate_content( $content ) {
	if( defined( 'QT_LANGUAGE' ) )
		$content = qtrans_useCurrentLanguageIfNotFoundShowAvailable( $content );
	return $content;
}
add_filter( 'pl8app_menuitems_content', 'pl8app_qtranslate_content' );
add_filter( 'pl8app_menuitems_excerpt', 'pl8app_qtranslate_content' );

/**
 * Prevents qTranslate from redirecting to language-specific URL when menuiteming purchased files
 *
 * @since  1.0.0
 * @param string       $target Target URL
 * @return string|bool $target Target URL. False if redirect is disabled
 */
function pl8app_qtranslate_prevent_redirect( $target ) {

	if( strpos( $target, 'pl8appfile' ) ) {
		$target = false;
		global $q_config;
		$q_config['url_mode'] = '';
	}

	return $target;
}
add_filter( 'qtranslate_language_detect_redirect', 'pl8app_qtranslate_prevent_redirect' );

/**
 * Disable the WooCommerce 'Un-force SSL when leaving checkout' option on pl8app checkout
 * to prevent redirect loops
 *
 * @since  1.0.0
 * @return void
 */
function pl8app_disable_woo_ssl_on_checkout() {
	if( pl8app_is_checkout() && pl8app_is_ssl_enforced() ) {
		remove_action( 'template_redirect', array( 'WC_HTTPS', 'unforce_https_template_redirect' ) );
	}
}
add_action( 'template_redirect', 'pl8app_disable_woo_ssl_on_checkout', 9 );

/**
 * Disables the mandrill_nl2br filter while sending pl8app emails
 *
 * @since  1.0.0
 * @return void
 */
function pl8app_disable_mandrill_nl2br() {
	add_filter( 'mandrill_nl2br', '__return_false' );
}
add_action( 'pl8app_email_send_before', 'pl8app_disable_mandrill_nl2br');

/**
 * Prevents the Purchase Confirmation screen from being detected as a 404 error in the 404 Redirected plugin
 *
 * @since 1.0.0
 * @return void
 */
function pl8app_disable_404_redirected_redirect()
{

    if (is_404()){
        wp_safe_redirect(home_url('/'));
        exit;
    }

	if( ! defined( 'WBZ404_VERSION' ) ) {
		return;
	}

	if( pl8app_is_success_page() ) {
		remove_action( 'template_redirect', 'wbz404_process404', 10 );
	}

}
add_action( 'template_redirect', 'pl8app_disable_404_redirected_redirect', 9 );

/**
 * Adds 'pl8app' to the list of Say What aliases after moving to WordPress.org language packs
 *
 * @since 1.0.0
 * @param  array $aliases Say What domain aliases
 * @return array          Say What domain alises with 'pl8app' added
 */
function pl8app_say_what_domain_aliases( $aliases ) {
	$aliases['pl8app'][] = 'pl8app';

	return $aliases;
}
add_filter( 'say_what_domain_aliases', 'pl8app_say_what_domain_aliases', 10, 1 );

/**
 * Removes the Really Simple SSL mixed content filter during file menuitems to avoid
 * errors with chunked file delivery
 *
 * @see https://github.com/rlankhorst/really-simple-ssl/issues/30
 *
 * @since 1.0.10
 * @return void
 */
function pl8app_rsssl_remove_mixed_content_filter() {
	if ( class_exists( 'REALLY_SIMPLE_SSL' ) && did_action( 'pl8app_process_verified_menuitem' ) ) {
		remove_action( 'init', array( RSSSL()->rsssl_mixed_content_fixer, 'start_buffer' ) );
		remove_action( 'shutdown', array( RSSSL()->rsssl_mixed_content_fixer, 'end_buffer' ) );
	}
}
add_action( 'plugins_loaded', 'pl8app_rsssl_remove_mixed_content_filter', 999 );