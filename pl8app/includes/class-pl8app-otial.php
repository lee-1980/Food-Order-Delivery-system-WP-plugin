<?php
/**
 * Order_Time_Interval_Limit
 *
 * @package Order_Time_Interval_Limit
 * @since 1.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Main Order_Time_Interval_Limit Class.
 *
 *
 * @class Order_Time_Interval_Limit
 */
class Order_Time_Interval_Limit {

  /**
   * Order_Time_Interval_Limit version.
   *
   * @var string
   */
  public $version = '1.4';


  /**
   * The single instance of the class.
   *
   * @var Order_Time_Interval_Limit
   * @since 1.0
   */
  protected static $_instance = null;

  /**
   * Main Order_Time_Interval_Limit Instance.
   *
   * Ensures only one instance of Order_Time_Interval_Limit is loaded or can be loaded.
   *
   * @since 1.0
   * @static
   * @return Order_Time_Interval_Limit - Main instance.
   */
  public static function instance() {
    if ( is_null( self::$_instance ) ) {
      self::$_instance = new self();
    }
    return self::$_instance;
  }

  /**
   * Order_Time_Interval_Limit Constructor.
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
    $this->define( 'pl8app_OTIAL_BASE', plugin_basename( PL8_PLUGIN_FILE ) );
  }


  /**
   * Hook into actions and filters.
   *
   * @since 1.0
   */
  private function init_hooks() {

//    add_filter( 'plugin_action_links_'.pl8app_OTIAL_BASE, array( $this, 'otial_settings_link' ) );


  }



  /**
   * Include required files for settings
   *
   * @since 1.0
   */
  private function includes() {
    require_once PL8_PLUGIN_DIR . 'includes/admin/order_time_ial/time-interval-order-limit-settings.php';
    require_once PL8_PLUGIN_DIR . 'includes/pl8app-otil-functions.php';
  }



  /**
   * Add settings link for the plugin
   *
   * @since 1.0
   */
  public function otial_settings_link( $links ) {
    $link = admin_url( 'admin.php?page=pl8app-settings&tab=general&section=order_time_interval_limits' );
    $settings_link = sprintf( __( '<a href="%1$s">Settings</a>', 'time-interval-order-limit' ), esc_url( $link ) );
    array_unshift( $links, $settings_link );
    return $links;
  }


}