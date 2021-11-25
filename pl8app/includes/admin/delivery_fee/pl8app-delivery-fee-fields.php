<?php
/**
 * pl8app_Delivery_Fee_Fields
 *
 * @package pl8app_Delivery_Fee_Fields
 * @since 1.0.0
 */

defined( 'ABSPATH' ) || exit;

if ( ! function_exists ( 'pl8app_delivery_fee_callback' ) ) {
  function pl8app_delivery_fee_callback( $args ) {
    pl8app_Delivery_Fee_Settings::get_template_part( 'delivery-fee-setting-fields' );
  }
}
