<?php
/**
 * The Template for displaying Delivery Zone Field
 *
 * @package pl8app_Delivery_Zone/Templates
 * @version 1.0
 */

defined( 'ABSPATH' ) || exit;

$delivery_settings = pl8app_Delivery_Fee_Settings::pl8app_fee_settings();
$zip_code_text = isset( $delivery_settings['zip_code_placeholder'] ) ? $delivery_settings['zip_code_placeholder'] : '';
$delivery_fee_method = isset( $delivery_settings['delivery_method'] ) ? $delivery_settings['delivery_method'] : 'zip_based';

if ( empty( $zip_code_text ) ) {
  $zip_code_text = esc_html( 'Enter your zip/postal code', 'pl8app-delivery-fee' );
}
$zip_code_placeholder = apply_filters( 'zip_code_text', $zip_code_text );
?>

<div class="pl8app-delivery-zone-wrapper">
  
  <?php if ( $delivery_fee_method == 'location_based' ) : ?>
    <input type="text" placeholder="<?php echo $zip_code_placeholder; ?>" id="pl8app_delivery_location" name="pl8app_delivery_location" class="pl8app-input">
    <input type="hidden" name="pl8app_delivery_latllng" id="pl8app_delivery_latllng">
  <?php else : ?>
    <input type="text" placeholder="<?php echo $zip_code_placeholder; ?>" id="pl8app_delivery_zone" name="pl8app_delivery_zone" class="pl8app-input">
  <?php endif; ?>
  
</div>
