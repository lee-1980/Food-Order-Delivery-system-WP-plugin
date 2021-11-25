<?php
/**
 * pl8app_StoreTiming
 *
 * @package pl8app_StoreTiming
 * @since 1.0.1
 */

defined( 'ABSPATH' ) || exit;

/**
 * Main pl8app_StoreTiming Class.
 *
 * @class pl8app_StoreTiming
 */
class pl8app_StoreTiming {

  /**
   * pl8app version.
   *
   * @var string
   */
  public $version = '1.9.4';


  /**
   * The single instance of the class.
   *
   * @var pl8app_StoreTiming
   * @since 1.0.1
   */
  protected static $_instance = null;

  /**
   * Main pl8app_StoreTiming Instance.
   *
   * Ensures only one instance of pl8app_StoreTiming is loaded or can be loaded.
   *
   * @since 1.0.1
   * @static
   * @return pl8app_StoreTiming - Main instance.
   */
  public static function instance() {
    if ( is_null( self::$_instance ) ) {
      self::$_instance = new self();
    }
    return self::$_instance;
  }

  /**
   * pl8app_StoreTiming Constructor.
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
    $this->define( 'pl8app_ST_BASE', plugin_basename( PL8_PLUGIN_FILE ) );
  }


  /**
   * Hook into actions and filters.
   *
   * @since 1.2
   */
  private function init_hooks() {

//    add_filter( 'plugin_action_links_' . pl8app_ST_BASE, array( $this, 'pl8app_store_timing_settings_link' ) );

    add_action( 'plugins_loaded', array( $this, 'pl8app_st_load_textdomain' ) );
  }


  /**
   * Load text domain
   *
   * @since 1.2
   */
  public function pl8app_st_load_textdomain() {
    load_plugin_textdomain( 'pl8app-store-timing', false, dirname( plugin_basename( PL8_PLUGIN_FILE ) ) . '/languages/' );
  }


  /**
   * Include required files for settings
   *
   * @since 1.2
   */
  private function includes() {

    require_once PL8_PLUGIN_DIR . 'includes/admin/store_timing/pl8app-store-timing-settings.php';

    require_once PL8_PLUGIN_DIR . 'includes/pl8app-store-timing-functions.php';
  }



  /**
   * Add settings link for the plugin
   *
   * @since 1.2
   */
  public function pl8app_store_timing_settings_link( $links ) {
    $link = admin_url( 'admin.php?page=pl8app-settings&tab=general&section=store_timings' );
    $settings_link = sprintf( __( '<a href="%1$s">Settings</a>', 'pl8app-store-timing' ), esc_url( $link ) );
    array_unshift( $links, $settings_link );
    return $links;
  }

  /**
   * Get weekdays
   *
   * @return array Weekdays in array
   * @since 1.2
   */
  public static function pl8app_st_get_weekdays() {
    $days = array( 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday' );
    return $days;
  }

}
