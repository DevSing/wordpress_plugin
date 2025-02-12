<?php
/**
 * Plugin Name: WooCommerce Product Last Updated
 * Description: Shows the date when a product was last added or updated in WooCommerce.
 * Version: 1.0
 * Author: Hubtec
 */

if (!defined('ABSPATH')) {
    exit; 
}


add_action('save_post_product', 'wc_save_last_product_update_time', 10, 3);

function wc_save_last_product_update_time($post_id, $post, $update) {
    
    if ($post->post_type !== 'product') {
        return;
    }

    update_option('wc_last_product_update_time', current_time('mysql'));
}


add_action('admin_menu', 'wc_last_update_admin_menu');

function wc_last_update_admin_menu() {
    add_menu_page(
        'Last Product Update',        
        'Last Product Update',        
        'manage_options',             
        'wc-last-product-update',     
        'wc_display_last_product_update_page' 
    );
}


function wc_display_last_product_update_page() {

    $last_update_time = get_option('wc_last_product_update_time', __('No updates yet', 'woocommerce'));

    echo '<div class="wrap">';
    echo '<h1>Last Lightspeed Product Sync Status.</h1>';
    echo '<p>The last product update happened on: <strong>' . esc_html($last_update_time) . '</strong></p>';
    echo '</div>';
}



