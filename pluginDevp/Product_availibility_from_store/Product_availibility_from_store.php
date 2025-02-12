<?php
/*
Plugin Name: Product Availability Status from Different Offline Stores
Description: A plugin to add custom functionality and shortcodes for product availability.
Version: 1.3
Author: DevP
Company: www.thethinktech.com
*/

function product_availibility_from_store_enqueue_assets()
{
    wp_enqueue_style('product-availibility-styles', plugins_url('assets/style.css', __FILE__));
    wp_enqueue_script('product-availibility-script', plugins_url('assets/script.js', __FILE__), array('jquery'), null, true);

    // Localize script to pass the AJAX URL to JavaScript
    wp_localize_script('product-availibility-script', 'product_availibility_vars', array(
        'ajax_url' => admin_url('admin-ajax.php')
    ));
}
add_action('wp_enqueue_scripts', 'product_availibility_from_store_enqueue_assets');

// Define the AJAX handler for fetching availability
function product_availibility_from_store_check_availability()
{
    if (!isset($_POST['product_id']) || !isset($_POST['variation_id'])) {
        wp_send_json_error('Invalid product or variation ID.');
    }

    $product_id = intval($_POST['product_id']);
    $variation_id = intval($_POST['variation_id']);

    // Fetch product and variation data
    $product = wc_get_product($product_id);
    $variation = wc_get_product($variation_id);

    if (!$product || !$variation) {
        wp_send_json_error('Product or variation not found.');
    }

    // Make API call to fetch products
    $products_response = wp_remote_get('LIGHTSPEED_API_KEY', array(
        'headers' => array(
            'accept' => 'application/json',
            'Authorization' => 'Bearer lsxs_pt_Gb8aNwKOubkNR0BXVKD1ZErw1cxfJZuZ',
        ),
    ));

    if (is_wp_error($products_response)) {
        wp_send_json_error('Failed to fetch products. ' . $products_response->get_error_message());
    }

    $products_body = wp_remote_retrieve_body($products_response);
    $products_data = json_decode((string)$products_body, true);

    if (!is_array($products_data)) {
        wp_send_json_error('Invalid products data received from API.');
    }

    // Get the selected variant's SKU
    $product_sku = $variation->get_sku();

    // Find the product ID from the SKU
    $lightspeed_product_id = null;
    foreach ($products_data['data'] as $prod) {
        if (is_array($prod['variants'])) {
            foreach ($prod['variants'] as $variant) {
                if (isset($variant['primary_sku_code']) && trim($variant['primary_sku_code']) === trim($product_sku)) {
                    $lightspeed_product_id = $variant['id'];
                    break 2;
                }
            }
        }
    }

    if (!$lightspeed_product_id) {
        wp_send_json_error('Product not found in the API response.');
		
    }

    // Make API call to fetch inventory for the specific product
    $inventory_response = wp_remote_get('https://altvapeinc.retail.lightspeed.app/api/2.0/products/' . $lightspeed_product_id . '/inventory', array(
        'headers' => array(
            'accept' => 'application/json',
            'Authorization' => 'Bearer lsxs_pt_Gb8aNwKOubkNR0BXVKD1ZErw1cxfJZuZ',
        ),
    ));

    if (is_wp_error($inventory_response)) {
        wp_send_json_error('Failed to fetch inventory. ' . $inventory_response->get_error_message());
    }

    $inventory_body = wp_remote_retrieve_body($inventory_response);
    $inventory_data = json_decode($inventory_body, true);

    if (!is_array($inventory_data)) {
        wp_send_json_error('Invalid inventory data received from API.');
    }

    // Define the outlets data
    $outlets = array(
        array("id" => "0ac54c19-8c55-11ed-fd4a-98f338e7b1a5", "name" => "Online"),
        array("id" => "0ac54c19-8cec-11ed-ea0a-bdddb4544d54", "name" => "Cochrane"),
        array("id" => "0ac54c19-8c55-11ed-fd4a-98f338e7b1a5", "name" => "Heritage"),
        array("id" => "06e94082-edec-11ee-fd41-2a5d8f6db977", "name" => "Lethbridge West"),
        array("id" => "0ac54c19-8cec-11ed-ea0a-bddd2fef5f0a", "name" => "Northland"),
        array("id" => "0ac54c19-8cec-11ed-ea0a-bddd4a408df5", "name" => "Sage Hill"),
        array("id" => "062791b7-ddec-11ef-eaf5-000090a8d1ed", "name" => "Strathmore"),
        array("id" => "0ac54c19-8cec-11ed-ea0a-bddd7015a9a2", "name" => "TransCanada"),
        array("id" => "0ac54c19-8cec-11ed-ea0a-bddac42eeccc", "name" => "Westhills")
    );

    // Search for the product ID in the inventory data and collect all matching outlet IDs
    $matching_outlet_ids = array();
    $is_available_heritage = false;
    $is_available_online = false;

    foreach ($inventory_data['data'] as $inventory) {
        if ($inventory['inventory_level'] > 0) {
            $matching_outlet_ids[] = $inventory['outlet_id'];
            if ($inventory['outlet_id'] === "0ac54c19-8c55-11ed-fd4a-98f338e7b1a5") {
                $is_available_online = true;
            }
            if ($inventory['outlet_id'] === "0ac54c19-8c55-11ed-fd4a-98f338e7b1a5") {
                $is_available_heritage = true;
            }
        }
    }

    // Prepare the response content
    ob_start();
    echo '<div style="display: grid; grid-template-columns: auto auto;" class="display-grid">';
    foreach ($outlets as $outlet) {
        $outlet_id = $outlet['id'];
        $outlet_name = $outlet['name'];

        $green_class = 'icon-box green-' . strtolower(str_replace(' ', '-', $outlet_name));
        $red_class = 'icon-box red-' . strtolower(str_replace(' ', '-', $outlet_name));

        if (in_array($outlet_id, $matching_outlet_ids)) {
            echo '<div class="' . esc_attr($green_class) . '"><img src="' . plugins_url('assets/check.svg', __FILE__) . '" alt="Check Icon" class="icon"/> ' . esc_html($outlet_name) . '</div>';
        } else {
            echo '<div class="' . esc_attr($red_class) . '"><img src="' . plugins_url('assets/cross.svg', __FILE__) . '" alt="Cross Icon" class="icon"/> ' . esc_html($outlet_name) . '</div>';
        }
    }
    echo '</div>';
    $response_content = ob_get_clean();

    // Additional logic to hide the "Add to Cart" button if not available in Heritage store
    if (!$is_available_heritage) {
        $response_content .= '<style>.single_add_to_cart_button { display: none !important; }</style>';
    }

    // Show message if not available online but available offline
    if (!$is_available_online && !empty($matching_outlet_ids)) {
     $response_content .= '<style>.product-msg { display: block !important; }</style>';
    }

    wp_send_json_success($response_content);
}
add_action('wp_ajax_product_availibility_from_store_check_availability', 'product_availibility_from_store_check_availability');
add_action('wp_ajax_nopriv_product_availibility_from_store_check_availability', 'product_availibility_from_store_check_availability');

// Hook to initialize the plugin
function product_availibility_from_store_status_init()
{
    // Register the shortcode
    add_shortcode('product_availibility_from_store_status_shortcode', 'product_availibility_from_store_status_shortcode_function');
}
add_action('init', 'product_availibility_from_store_status_init');

function product_availibility_from_store_status_shortcode_function()
{
    global $product;

    if (!$product) {
        return '<div class="my-custom-content"><p>Product not found.</p></div>';
    }

    // Initial output with an empty div for the availability status
    $content = '<div id="availability-status" class="my-custom-content"><p class="inventory-initiak-status">Please select a variant to know availability status.</p></div>';

    // Add a hidden input to hold the product ID
    $content .= '<input type="hidden" id="product_id" value="' . esc_attr($product->get_id()) . '">';

    return $content;
}

// Activation hook
function product_availibility_from_store_activate()
{
    // Actions to perform on activation
    if (!wp_next_scheduled('product_availibility_from_store_cron_hook')) {
        wp_schedule_event(time(), 'hourly', 'product_availibility_from_store_cron_hook');
    }
}
register_activation_hook(__FILE__, 'product_availibility_from_store_activate');

// Deactivation hook
function product_availibility_from_store_deactivate()
{
    // Actions to perform on deactivation
    wp_clear_scheduled_hook('product_availibility_from_store_cron_hook');
}
register_deactivation_hook(__FILE__, 'product_availibility_from_store_deactivate');

// Uninstall hook
function product_availibility_from_store_uninstall()
{
    // Actions to perform on uninstallation
    delete_option('product_availibility_from_store_options');
}
register_uninstall_hook(__FILE__, 'product_availibility_from_store_uninstall');

// Custom action hook function
function product_availibility_from_store_custom_action_function()
{
    // Example function for the custom action hook
    error_log('Custom action hook executed!');
}
add_action('product_availibility_from_store_custom_action', 'product_availibility_from_store_custom_action_function');

// Cron job function
function product_availibility_from_store_cron_function()
{
    // Example function for the scheduled cron job
    error_log('Cron job executed!');
}
add_action('product_availibility_from_store_cron_hook', 'product_availibility_from_store_cron_function');
