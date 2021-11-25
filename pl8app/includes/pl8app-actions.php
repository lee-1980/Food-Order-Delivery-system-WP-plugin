<?php
/**
 * Front-end Actions
 *
 * @package     pl8app
 * @subpackage  Functions
 * @copyright
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Hooks pl8app actions, when present in the $_GET superglobal. Every pl8app_action
 * present in $_GET is called using WordPress's do_action function. These
 * functions are called on init.
 *
 * @since 1.0.0
 * @return void
*/
function pl8app_get_actions() {
	$key = ! empty( $_GET['pl8app_action'] ) ? sanitize_key( $_GET['pl8app_action'] ) : false;
	if ( ! empty( $key ) ) {
		do_action( "pl8app_{$key}" , $_GET );
	}
}
add_action( 'init', 'pl8app_get_actions' );

/**
 * Hooks pl8app actions, when present in the $_POST superglobal. Every pl8app_action
 * present in $_POST is called using WordPress's do_action function. These
 * functions are called on init.
 *
 * @since 1.0.0
 * @return void
*/
function pl8app_post_actions() {
	$key = ! empty( $_POST['pl8app_action'] ) ? sanitize_key( $_POST['pl8app_action'] ) : false;
	if ( ! empty( $key ) ) {
		do_action( "pl8app_{$key}", $_POST );
	}
}
add_action( 'init', 'pl8app_post_actions' );

/**
 * This sets the tax rate to fallback tax rate
 *
 * @since 2.6
 * @return mixed
 */

function pl8app_upgrade_data( $upgrader_object, $options ) {

  $pl8app_plugin_path_name = plugin_basename( __FILE__ );

  if ( $options['action'] == 'update' && $options['type'] == 'plugin' ) {

    if( is_array( $options['plugins'] ) ) {

      foreach ( $options['plugins'] as $plugin ) {

        if ( $plugin == $pl8app_plugin_path_name ){

          $default_tax  = '';
          $tax_rates    = get_option( 'pl8app_tax_rates', array() );

          if ( is_array( $tax_rates ) && !empty( $tax_rates ) ) {
            $default_tax = isset( $tax_rates[0]['rate'] ) ? $tax_rates[0]['rate'] : '';
          }

          if ( !empty( $default_tax ) ) {
            pl8app_update_option( 'tax_rate', $default_tax );
          }
        }
      }
    }
  }
}
add_action( 'upgrader_process_complete', 'pl8app_upgrade_data', 10, 2 );


function register_my_menus() {
    if ( !has_nav_menu( 'pl8app_main_menu' ) ) {
        // Register Menus
        register_nav_menus(
            array(
                'pl8app_main_menu' => __('pl8app Main Menu', 'pl8app'),
            )
        );
    }
}
add_action( 'after_setup_theme', 'register_my_menus' );

function update_icon_url($url){
    $pl8app_site_icon = pl8app_get_option('site_icon', '');
    if ( !empty( $pl8app_site_icon )) {
        return esc_url( $pl8app_site_icon );
    }
    else{
        return $url;
    }
}

add_filter('get_site_icon_url', 'update_icon_url');

