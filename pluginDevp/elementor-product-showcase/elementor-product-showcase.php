<?php
/*
Plugin Name: Elementor Product Showcase by Attribute
Description: Custom Elementor widget to showcase WooCommerce products by attributes.
Version: 1.0
Author: devPrince
*/

// Ensure the file is being called within WordPress
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

// Register the custom widget
add_action('elementor/widgets/widgets_registered', function($widgets_manager) {
    require_once(__DIR__ . '/widgets/product-showcase-widget.php');
    $widgets_manager->register_widget_type(new \Your_Custom_Widget_Class());
});
