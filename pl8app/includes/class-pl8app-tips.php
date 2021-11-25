<?php
/**
 * PL8_Tips
 *
 * @package PL8_Tips
 * @since 1.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Main PL8_Tips Class.
 *
 * @class PL8_Tips
 */
class PL8_Tips {

  /**
   * pl8app version.
   *
   * @var string
   */
  public $version = '1.9';


  /**
   * The single instance of the class.
   *
   * @var PL8_Tips
   * @since 1.0
   */
  protected static $_instance = null;

  /**
   * Main PL8_Tips Instance.
   *
   * Ensures only one instance of PL8_Tips is loaded or can be loaded.
   *
   * @since 1.0
   * @static
   * @return PL8_Tips - Main instance.
   */
  public static function instance() {
    if ( is_null( self::$_instance ) ) {
      self::$_instance = new self();
    }
    return self::$_instance;
  }

  /**
   * PL8_Tips Constructor.
   */
  public function __construct() {
    $this->define_constants();
    $this->includes();
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
    $this->define( 'PL8_TIPS_VERSION', $this->version );
    $this->define( 'PL8_TIPS_PLUGIN_DIR', plugin_dir_path( PL8_PLUGIN_FILE ) );
    $this->define( 'PL8_TIPS_PLUGIN_URL', plugin_dir_url( PL8_PLUGIN_FILE ) );
    $this->define( 'PL8_TIPS_BASE', plugin_basename( PL8_PLUGIN_FILE ) );
  }


  /**
   * Include required files for settings
   *
   * @since 1.0
   */
  private function includes() {
    require_once PL8_TIPS_PLUGIN_DIR . 'includes/admin/tips/pl8app-tips-settings.php';
    require_once PL8_TIPS_PLUGIN_DIR . 'includes/pl8app-tips-functions.php';
  }

}