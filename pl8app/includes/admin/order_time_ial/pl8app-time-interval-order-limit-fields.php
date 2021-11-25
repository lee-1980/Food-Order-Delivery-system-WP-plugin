<?php
/**
 * pl8app_TimeInterval_Order_Limit_Fields
 *
 *
 * @package pl8app_TimeInterval_Order_Limit_Fields
 * @since 1.0
 */

defined( 'ABSPATH' ) || exit;

if ( ! function_exists ( 'pl8app_order_time_interval_limits_callback' ) ) {
  function pl8app_order_time_interval_limits_callback( $args ) {
     Order_Time_Interval_Limit_Settings::get_template_part( 'tiaol-setting-fields' );
  }
}
