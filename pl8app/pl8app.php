<?php
/**
 * Plugin Name: pl8app
 * Description: pl8app is an online ordering system for WordPress.
 * Version: 1.0
 * Author: pl8apptoken
 * Author URI: https://pl8app.co.uk
 * Text Domain: pl8app
 * Domain Path: languages
 * @package pl8app
 */

defined( 'ABSPATH' ) || exit;

if ( ! defined( 'PL8_PLUGIN_FILE' ) ) {
    define( 'PL8_PLUGIN_FILE', __FILE__ );
}

// Include the main Pl8App Class

if ( ! class_exists( 'Pl8App', false ) ) {
    include_once dirname( __FILE__ ) . '/includes/Pl8App.php';
}

/**
 * Returns the main instance of Pl8App.
 *
 * @return Pl8App
 */
function PL8PRESS() {
    return Pl8App::instance();
}
//Get pl8app Running.
PL8PRESS();

// Include the main pla_StoreTiming class.
if ( ! class_exists( 'pl8app_StoreTiming', false ) ) {
    include_once dirname( __FILE__ ) . '/includes/class-pl8app-store-timing.php';
}
function pl8app_StoreTiming() {
    return pl8app_StoreTiming::instance();
}

pl8app_StoreTiming();

// Include the main class pl8app_Delivery_Fee.
if ( ! class_exists( 'pl8app_Delivery_Fee_Loader', false ) ) {
    include_once dirname( __FILE__ ) . '/includes/class-pl8app-delivery-fee-loader.php';
}

/**
 * Returns the main instance of pl8app_Delivery_Fee.
 *
 * @return pl8app_Delivery_Fee
 */
function pl8app_Delivery_Fee() {
    return pl8app_Delivery_Fee_Loader::instance();
}

pl8app_Delivery_Fee();

// Include the main class pl8app_Order_Time_Interval_Limit
if ( ! class_exists( 'Order_Time_Interval_Limit', false ) ) {
    include_once dirname( __FILE__ ) . '/includes/class-pl8app-otial.php';
}

/**
 * Returns the main instance of Order_Time_Interval_Limit.
 *
 * @return Order_Time_Interval_Limit
 */
function pl8app_Order_Time_Interval_Limit() {
    return Order_Time_Interval_Limit::instance();
}

pl8app_Order_Time_Interval_Limit();

// Include the main pl8app_Order_Tracking class
//if ( ! class_exists( 'pl8app_Order_Tracking', false ) ) {
//    include_once dirname( __FILE__ ) . '/includes/class-pl8app-order-api.php';
//}
//
//function pl8app_Order_API() {
//    return pl8app_Order_Tracking::instance();
//}
//pl8app_Order_API();


// Include the main pl8app_PRINTER class.
if ( ! class_exists( 'pl8app_Printer', false ) ) {
    include_once dirname( __FILE__ ) . '/includes/class-print-receipts.php';
}

function pl8app_Printer() {
    return pl8app_Printer::instance();
}

pl8app_Printer();

/**
 * Returns the main instance of Tips.
 *
 * @return Tips
 */

// Include the main PL8_Tips class.
if ( ! class_exists( 'PL8_Tips', false ) ) {
    include_once dirname( __FILE__ ) . '/includes/class-pl8app-tips.php';
}
function pl8app_Tips() {
    return PL8_Tips::instance();
}

pl8app_Tips();


/**
 * Returns the main instance of Inventory
 */

// Include the main pl8app_Inventory class.
if ( ! class_exists( 'PL8_Inventory', false ) ) {
    include_once dirname( __FILE__ ) . '/includes/class-pl8app-inventory.php';
}

function pl8apps_Inventory() {
    return PL8_Inventory::instance();
}
pl8apps_Inventory();

/**
 * SendGrid SMTP Server instance, Currently only API version
 */


if ( ! class_exists( 'PL8_sendgrid_SMTP', false ) ) {
    include_once dirname( __FILE__ ) . '/includes/class-pl8app-sendgrid-mailer.php';
}

function pl8apps_sendgrid_smtp() {
    return PL8_sendgrid_SMTP::instance();
}
pl8apps_sendgrid_smtp();




