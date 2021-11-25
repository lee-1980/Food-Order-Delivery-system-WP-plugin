<?php
/**
 * pl8app_Delivery_Fee
 *
 * @package pl8app_Delivery_Fee
 * @since 1.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Main pl8app_Delivery_Fee Class.
 *
 * @class pl8app_Delivery_Fee
 */

class pl8app_Delivery_Fee_Loader {

  /**
   * pl8app_Delivery_Fee version.
   *
   * @var string
   */
  public $version = '2.5.1';


  /**
   * The single instance of the class.
   *
   * @var pl8app_Delivery_Fee
   * @since 1.0
   */
  protected static $_instance = null;


  /**
   * Main pl8app_Delivery_Fee Instance.
   *
   * Ensures only one instance of pl8app_Delivery_Fee is loaded or can be loaded.
   *
   * @since 1.0
   * @static
   * @return pl8app_Delivery_Fee - Main instance.
   */
  public static function instance() {
    if ( is_null( self::$_instance ) ) {
      self::$_instance = new self();
    }
    return self::$_instance;
  }

  /**
   * pl8app_Delivery_Fee Constructor.
   */
  public function __construct() {
    $this->define_constants();
    $this->includes();
    $this->init_hooks();
  }

  /**
   * Define constant if not already set.
   *
   * @param string      $name  Constant name.
   * @param string|bool $value Constant value.
   */
  private function define( $name, $value ) {
    if ( ! defined( $name ) ) {
      define( $name, $value );
    }
  }

  /**
   * Define Constants
   */
  private function define_constants() {
    $this->define( 'PL8_VERSION', $this->version );
    $this->define( 'PL8_PLUGIN_DIR', plugin_dir_path( PL8_PLUGIN_FILE ) );
    $this->define( 'PL8_PLUGIN_URL', plugin_dir_url( PL8_PLUGIN_FILE ) );
    $this->define( 'pl8app_DELIVERY_FEE_BASE', plugin_basename( PL8_PLUGIN_FILE ) );
  }

  /**
   * Hook into actions and filters.
   *
   * @since 1.0
   */
  private function init_hooks() {
//    add_filter( 'plugin_action_links_'.pl8app_DELIVERY_FEE_BASE, array( $this, 'delivery_fee_settings_link' ) );
  }


  /**
   * Add settings link for the plugin
   *
   * @since 1.0
   */
  public function delivery_fee_settings_link( $links ) {
    $link = admin_url( 'admin.php?page=pl8app-settings&tab=general&section=delivery_fee' );
    /* translators: %1$s: settings page link */
    $settings_link = sprintf( __( '<a href="%1$s">Settings</a>', 'pl8app-delivery-fee' ), esc_url( $link ) );
    array_unshift( $links, $settings_link );
    return $links;
  }

  /**
   * Include required files for settings
   *
   * @since 1.0
   */
  private function includes() {
    require_once PL8_PLUGIN_DIR . 'includes/admin/delivery_fee/pl8app-delivery-fee-settings.php';
    require_once PL8_PLUGIN_DIR . 'includes/class-pl8app-delivery-fee.php';
  }

}
