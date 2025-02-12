<?php
/**
 * Plugin Name: wp woo orderid date adder
 * Description: Adds a timestamp to the transaction ID for all orders in WooCommerce to avoid Moneris duplicate order ID issues.
 * Version: 1.4
 * Author: Hubtech
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Function to log messages to a custom file.
function custom_log_to_file( $message ) {
    $log_file = WP_CONTENT_DIR . '/custom-logs.txt';  // Log file name
    $log_entry = date( 'Y-m-d H:i:s' ) . ' - ' . $message . "\n";
    file_put_contents( $log_file, $log_entry, FILE_APPEND );  // Append message to log
}

// Record a credit card decline if checkout fails.
add_action( 'woocommerce_checkout_order_processed', 'custom_woocommerce_checkout_order_processed', 10, 3 );
function custom_woocommerce_checkout_order_processed( $order_id, $posted_data, $order ) {
    custom_log_to_file( 'Order processed: ' . $order_id . ' | Status: ' . $order->get_status() );

    if ( 'failed' === $order->get_status() ) {
        custom_log_to_file( 'Order failed: ' . $order_id );
        // Trigger a custom action to display an alert message and redirect
        add_action('wp_footer', 'custom_alert_script');
        // Clear the WooCommerce session after failure
        custom_clear_woocommerce_session();
    }
}

// Record a credit card decline if order-pay (paying for OBO) fails.
add_action( 'woocommerce_after_pay_action', 'custom_woocommerce_after_pay_action', 10, 1 );
function custom_woocommerce_after_pay_action( $order ) {
    custom_log_to_file( 'Order Pay Action Triggered: ' . $order->get_id() );

    if ( 'failed' === $order->get_status() ) {
        custom_log_to_file( 'Order failed during after-pay: ' . $order->get_id() );
        add_action('wp_footer', 'custom_alert_script');
        // Clear the WooCommerce session after failure
        custom_clear_woocommerce_session();
    }
}

// Function to handle JavaScript alert and redirect when order fails.
function custom_alert_script() {
    custom_log_to_file( 'Custom alert script triggered.' );
    ?>
    <script type="text/javascript">
        alert('Transaction declined. Please try again.');
        window.location.href = 'https://altvape.ca/';  // Redirect after the alert (modify the URL)
    </script>
    <?php
}

// Clear WooCommerce session and cart on order failure.
function custom_clear_woocommerce_session() {
    // Clear WooCommerce session
    WC()->session->destroy_session();  // Destroys the entire session

    // Optionally, empty the cart too
    if ( WC()->cart ) {
        WC()->cart->empty_cart();
        custom_log_to_file( 'Cart emptied and session destroyed.' );
    }
}

// Optionally, clear the cart if the order fails.
add_action( 'woocommerce_order_status_failed', 'custom_empty_cart_on_failed_order' );
function custom_empty_cart_on_failed_order( $order_id ) {
    custom_clear_woocommerce_session();
    custom_log_to_file( 'Cart and session cleared for failed order: ' . $order_id );
}
