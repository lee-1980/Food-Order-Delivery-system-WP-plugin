<?php
/**
 * pl8app_Printer
 *
 * @package pl8app_Printer
 * @since 1.0.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Main pl8app_Printer Class.
 *
 * @class pl8app_Printer
 */
class pl8app_Printer {

  /**
   * pl8app_Printer version.
   *
   * @var string
   */
  public $version = '1.1.5';

  /**
   * The single instance of the class.
   *
   * @var pl8app_Printer
   * @since 1.0.0
   */
  protected static $_instance = null;

  /**
   * Main pl8app_Printer Instance.
   *
   * Ensures only one instance of pl8app_Printer is loaded or can be loaded.
   *
   * @since 1.0.0
   * @return pl8app_Printer - Main instance.
   */
  public static function instance() {
    if ( is_null( self::$_instance ) ) {
      self::$_instance = new self();
    }
    return self::$_instance;
  }

  /**
   * pl8app_Printer Constructor.
   */
  public function __construct() {
    $this->define_constants();
    $this->includes();
    $this->init_hooks();
  }

  /**
   * Define constant if not already set.
   *
   * @since 1.0.0
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
   *
   * @since 1.0.0
   */
  private function define_constants() {
    $this->define( 'PL8_VERSION', $this->version );
    $this->define( 'PL8_PLUGIN_DIR', plugin_dir_path( PL8_PLUGIN_FILE ) );
    $this->define( 'PL8_PLUGIN_URL', plugin_dir_url( PL8_PLUGIN_FILE ) );
    $this->define( 'pl8app_PRINTER_BASE', plugin_basename( PL8_PLUGIN_FILE ) );
  }

  /**
   * Include required files for settings and functionality
   *
   * @since 1.0.0
   */
  private function includes() {

    // Required Class Files
    require_once PL8_PLUGIN_DIR.'includes/class-http-request.php';
    require_once PL8_PLUGIN_DIR.'includes/class-google-cloud-print.php';
    require_once PL8_PLUGIN_DIR.'includes/class-print-settings.php';

    // PDF Library
    require_once PL8_PLUGIN_DIR.'includes/libraries/html2pdf/vendor/autoload.php';
  }

  /**
   * Hook into actions and filters.
   *
   * @since 1.0.0
   */
  private function init_hooks() {



    // Add Addon Settings link directly on plugins page
//    add_filter( 'plugin_action_links_'.pl8app_PRINTER_BASE, array( $this, 'pl8app_printer_settings_link' ) );


  }

  /**
   * Add settings link for pl8app Printer
   * @since 1.0.0
   *
   * @param $links Array of links
   * @return array
   */
  public function pl8app_printer_settings_link( $links ) {

    $action_links = array(
      'settings' => '<a href="' . admin_url( 'admin.php?page=pl8app-settings&tab=general&section=printer_settings' ) . '" aria-label="' . esc_attr__( 'View Printer settings', 'pl8app-printer' ) . '">' . esc_html__( 'Settings', 'pl8app-printer' ) . '</a>',
    );

    return array_merge( $action_links, $links );
  }

}