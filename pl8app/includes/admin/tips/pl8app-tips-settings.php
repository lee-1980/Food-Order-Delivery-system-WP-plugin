<?php
/**
 * PL8_Tips_Settings
 *
 * @package PL8_Tips_Settings
 * @since 1.0
 */

defined( 'ABSPATH' ) || exit;

class PL8_Tips_Settings {

  public function __construct() {

  }

  /**
   * Get tips html
   * @param $tip string Fee ID to be added
   * @return html
   **/
  public static function pl8app_get_tips_html( $tip ) {

    // Get tip data
    $fee_id = $tip;
    $fees   = pl8app_get_cart_fees();
    $fee    = $fees[$tip];
    ob_start();
    ?>
      <tr class="pl8app_cart_fee" id="pl8app_cart_fee_<?php echo $fee_id; ?>">
        <?php do_action( 'pl8app_cart_fee_rows_before', $fee_id, $fee ); ?>

        <th colspan="3" class="pl8app_cart_fee_label">
          <?php echo esc_html( $fee['label'] ); ?>
            <span>
              <?php echo esc_html( pl8app_currency_filter( pl8app_format_amount( $fee['amount'] ) ) ); ?>

              <?php if( ! empty( $fee['type'] ) && 'item' == $fee['type'] ) : ?>
            <a href="<?php echo esc_url( pl8app_remove_cart_fee_url( $fee_id ) ); ?>"><?php _e( 'Remove', 'pl8app' ); ?></a>
          <?php endif; ?>
            </span>
        </td>

        <?php do_action( 'pl8app_cart_fee_rows_after', $fee_id, $fee ); ?>
      </tr>
    <?php
    return ob_get_clean();

  }

}
new PL8_Tips_Settings();