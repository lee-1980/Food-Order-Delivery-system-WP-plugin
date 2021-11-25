<?php
/**
 * PL8_Tips_Functions
 *
 * @package PL8_Tips_Functions
 * @since 1.0
 */

defined( 'ABSPATH' ) || exit;

class PL8_Tips_Functions {
  
  public function __construct() {

    add_action( 'pl8app_purchase_form' , array( $this, 'pl8app_tips_html_checkout' ), 12 );

    add_action( 'wp_ajax_pl8app_add_tips', array( $this, 'pl8app_add_tips') );
    add_action( 'wp_ajax_nopriv_pl8app_add_tips', array( $this, 'pl8app_add_tips') );

    add_action( 'pl8app_add_email_tags', array( $this, 'add_tips_to_email_tag' ), 100 ) ;

    add_action( 'wp_enqueue_scripts', array( $this, 'pl8app_tips_scripts' ) );

    add_action( 'wp_ajax_nopriv_pl8app_remove_tips', array( $this, 'pl8app_remove_tips' ) );

    add_action( 'wp_ajax_pl8app_remove_tips', array( $this, 'pl8app_remove_tips' ) );

    add_filter( 'pl8app_payments_table_columns', array( $this, 'pl8app_tips_column' ), 10 );

    add_filter( 'pl8app_payments_table_column', array( $this, 'pl8app_tips_column_value'), 10, 3 );

  }

  /**
   * Add necessary css and js file
   *
   * @since  1.0
   */
  public function pl8app_tips_scripts() {

    // Enqueue style and script
    wp_enqueue_style( 'pl8app-tips-style', PL8_TIPS_PLUGIN_URL . 'assets/css/pl8app-tips.css' );
    wp_enqueue_script( 'pl8app-tips-script', PL8_TIPS_PLUGIN_URL . 'assets/js/pl8app-tips.js', ['jquery'], PL8_TIPS_VERSION );

    // Tips type symbol
    $tip_type         = pl8app_get_option( 'tips_type' );
    $tip_type_symbol  = ( $tip_type == 'percentage_value' ) ? '%' : '';
    $remove_tips_label    = pl8app_get_option( 'remove_tips_label' );
    $remove_tips_label    = !empty( $remove_tips_label ) ? $remove_tips_label : __( 'Remove Tips', 'pl8app-tips' );

    // Localize the script with data
    $localize_data = array(
      'ajaxurl'          => admin_url( 'admin-ajax.php' ),
      'tip_type_symbol'  => $tip_type_symbol,
      'remove_tip_label' => $remove_tips_label,
    );
    wp_localize_script( 'pl8app-tips-script', 'tips_script', $localize_data );

  }

  /**
   * Add Tips option to checkout page
   * @since  1.0
   **/
  public function pl8app_tips_html_checkout() {

    $is_tip_enabled = pl8app_get_option( 'enable_tips_on_checkout' );
    //print_r(pl8app_cart());
    if ( $is_tip_enabled ) {

      // Get tips options
      $default_tips         = pl8app_get_option( 'tips_values' );
      $default_tips_values  = explode( ',', $default_tips );
      $tips_text            = pl8app_get_option( 'tips_text' );
      $tips_subtext         = pl8app_get_option( 'tips_subtext' );

      // Get the tips type
      $tip_type             = pl8app_get_option( 'tips_type' );
      $tip_type_symbol      = ( $tip_type == 'percentage_value' ) ? '%' : '';
      $remove_tips_label    = pl8app_get_option( 'remove_tips_label' );
      $remove_tips_label    = !empty( $remove_tips_label ) ? $remove_tips_label : __( 'Remove Tips', 'pl8app-tips' );
      $tips_type_display    = pl8app_get_option( 'tips_type_display' );

      $cart_fees = PL8PRESS()->fees->get_fees();
      ?>
        <div class="pl8app-tips">
          <div class="section-label top25">
            <a class="section-label-a">
              <span class="tips-text"><?php echo $tips_text ?></span>
              <span class="tips-subtext"><?php echo $tips_subtext; ?></span>
            </a>
            </div>
          <div>
            <ul class="tip-wrapper">
              <?php
              $remove_tips_class = 'disable';

              if ( is_array( $cart_fees )
                && array_key_exists( 'tip', $cart_fees )  ) {
                $remove_tips_class = 'enable';
              }
              ?>

              <li class="remove_tip">
                <a href="#" class="pl8app-input <?php echo $remove_tips_class; ?> pl8app-remove-tip">
                <?php echo $remove_tips_label; ?></a>
              </li>

              <?php
                if ( !empty( $default_tips ) && $tips_type_display != 'manual_tips' ) {
                  // Loop through all tips value
                  foreach ( $default_tips_values as $default_tips_value ) {
                    $default_tips_value = str_replace( ' ', '', $default_tips_value );
                    ?>
                      <li>
                        <a class="tips" href="javascript:;" data-type="tip_type_<?php echo $tip_type ?>" data-tip="<?php echo $default_tips_value; ?>"><?php echo ( $tip_type == 'fixed_values' ) ? pl8app_currency_filter( $default_tips_value  ) : $default_tips_value . $tip_type_symbol; ?></a>
                      </li>
                    <?php
                  }
                }
              ?>
            </ul>
          </div>

          <?php
          if ( $tips_type_display != 'tips_options' ) :
          ?>
          <div class="pl8app-custom-tip-wrapper">
            <input class="numeric_only pl8app-input" type="number" value="" name="tip_value" min="1" id="manual_tip_value" placeholder="<?php _e( 'Custom Tip', 'pl8app-tips' ); ?>">
            <input type="button" value="<?php _e( 'Add', 'pl8app-tips'); ?>" class="pl8app_tips_custom_amount">
          </div>
          <?php endif; ?>
        </div>
      <?php
    }

  }

  /**
   * Add ajax function to include tip to total price
   * @since  1.0
   **/
  public function pl8app_add_tips() {

    // bail if tip is not set
    if ( !isset( $_POST['tip'] )  ) return;

    $tip = floatval( $_POST['tip'] );

    if ( $tip == 0 ) {
      return;
    }

    // Remove all fees from checkout
    PL8PRESS()->fees->remove_fee( 'tip' );

    // Get tips cart label
    $tips_cart_label = pl8app_get_option( 'tips_cart_label' );

    // Calculate the tip value
    $cart_sub_total   = pl8app_get_cart_subtotal();

    // Get tax option
    $is_tax       = pl8app_get_option( 'enable_taxes' );
    $is_tax_tips  = pl8app_get_option( 'inculde_tax_on_tips' );


    // Check tips type
    if ( $_POST['type'] == 'tip_type_percentage_value' ) {
      $tip_percent  = $_POST['tip'];
      $tip_value    = ( $tip_percent / 100 ) * $cart_sub_total;
    }else {
      $tip_value    = $_POST['tip'];
      $tip_percent  = ( $tip_value / $cart_sub_total ) * 100;
    }

    if ( $is_tax && $is_tax_tips ) $tip_value = $tip_value + pl8app_calculate_tax( $tip_value );

    // Add a tip to checkout
    if ( $tip_value > 0 ) {
      PL8PRESS()->fees->add_fee( $tip_value, $tips_cart_label, 'tip', 'tip', $is_tax );
    }

    $total  = pl8app_get_cart_total();
    $return = array(
      'response'    => 'success',
      'total_plan'  => $total,
      'total'       => html_entity_decode( pl8app_currency_filter( pl8app_format_amount( $total ) ), ENT_COMPAT, 'UTF-8' ),
      'percentage'  => pl8app_format_amount( $tip_percent ),
      'html'        => PL8_Tips_Settings::pl8app_get_tips_html( 'tip' ),
      'tip_value'   => html_entity_decode( pl8app_currency_filter( pl8app_format_amount( $tip_value ) ), ENT_COMPAT, 'UTF-8' ),
    );

    echo json_encode( $return );

    wp_die();

  }


  /**
   * Add email tag for tips
   * @since  1.2
   **/
  public function add_tips_to_email_tag( $email_tags ) {
    $email_tag = 'tips';
    $tag_description = pl8app_get_option( 'tips_text' );

    $is_tip_enabled = pl8app_get_option( 'enable_tips_on_checkout' );

    if ( $is_tip_enabled ) {

      pl8app_add_email_tag( $email_tag, $tag_description, array( $this,  'pl8app_email_tag_tips') );
    }

  }

  /**
   * The {tips} email tag\
   * @since  1.2
   */
  public function pl8app_email_tag_tips( $payment_id ) {

    $tips_price = 0;

    if ( !empty( $payment_id ) ) {
      $tips_price = $this->tips_price( $payment_id );
    }

    return pl8app_currency_filter( pl8app_format_amount( $tips_price ) );

  }


  /**
   * Get tips price by payment id
   * @since  1.2
   */
  public function tips_price( $payment_id ) {
    $fees       = pl8app_get_payment_fees( $payment_id, 'fee' );
    $tip_price  = '';

    foreach ( $fees as $fee ) {
      if ( $fee['id'] == 'tip' ) {
        $tip_price = $fee['amount'];
      }
    }

    return $tip_price;
  }


  /**
   * Remove tips through ajax
   * @since  1.3
   */
  public function pl8app_remove_tips() {

    // Remove all fees from checkout
    PL8PRESS()->fees->remove_fee( 'tip' );

    //Get cart total
    $total  = pl8app_get_cart_total();

    $return = array(
      'response'    => 'success',
      'total_plan'  => $total,
      'total'       => html_entity_decode( pl8app_currency_filter( pl8app_format_amount( $total ) ), ENT_COMPAT, 'UTF-8' ),
    );

    echo json_encode( $return );

    wp_die();

  }


  /**
   * Check items per order
   *
   * @since 1.6
   * @param array $columns Params for table columns
   * @return array| columns
   */
  public function pl8app_tips_column( $columns ) {

    $enable_tips_column = pl8app_get_option( 'show_tips_column' );
    // Get tips cart label
    $tips_cart_label    = pl8app_get_option( 'tips_cart_label' ,'Tips');

    if ( $enable_tips_column == 1) {
      $columns['tips'] = __( $tips_cart_label, 'pl8app-tips' );
    }

    return $columns;
  }

  /**
   * Show tips column in the order column
   *
   * @since 1.6
   * @param string $value
   * @param int $payment_id
   * @param string $column_name
   * @return string| value
   */
  public function pl8app_tips_column_value( $value, $payment_id, $column_name ) {

    $enable_tips_column = pl8app_get_option( 'show_tips_column' );

    if ( $enable_tips_column == 1) {
      if ( 'tips' == $column_name ) {
        $tips_price = $this->tips_price( $payment_id );
        $tips_price = !empty( $tips_price ) ? $tips_price : 0;
        $tips_price = html_entity_decode( pl8app_currency_filter( pl8app_format_amount( $tips_price ) ), ENT_COMPAT, 'UTF-8' );
        $value = $tips_price;
      }
    }
    
    return $value;
  }

}

new PL8_Tips_Functions();