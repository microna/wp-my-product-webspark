<?php
/**
 * Admin new product email (plain text)
 */

if (!defined('ABSPATH')) {
    exit;
}

echo "=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=\n";
echo esc_html(wp_strip_all_tags($email_heading));
echo "\n=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=\n\n";

if ($is_edit) {
    echo sprintf(__('A product has been edited by %s.', 'wp-my-product-webspark'), esc_html($user->display_name)) . "\n\n";
} else {
    echo sprintf(__('A new product has been submitted by %s.', 'wp-my-product-webspark'), esc_html($user->display_name)) . "\n\n";
}

echo "----------------------------------------\n";
echo __('Product Name:', 'wp-my-product-webspark') . ' ' . esc_html($product->get_name()) . "\n";
echo __('Price:', 'wp-my-product-webspark') . ' ' . wp_strip_all_tags($product->get_price_html()) . "\n";
echo __('Stock Quantity:', 'wp-my-product-webspark') . ' ' . esc_html($product->get_stock_quantity()) . "\n";
echo __('Status:', 'wp-my-product-webspark') . ' ' . esc_html(ucfirst($product->get_status())) . "\n";
echo "----------------------------------------\n\n";

echo __('Edit Product:', 'wp-my-product-webspark') . ' ' . esc_url($product_edit_url) . "\n";
echo __('View Author:', 'wp-my-product-webspark') . ' ' . esc_url($user_edit_url) . "\n\n";

if ($product->get_description()) {
    echo __('Product Description:', 'wp-my-product-webspark') . "\n";
    echo wp_strip_all_tags(wptexturize($product->get_description())) . "\n\n";
}

echo esc_html($additional_content) . "\n\n";

echo __('Thank you for reading.', 'wp-my-product-webspark') . "\n\n";

echo wp_strip_all_tags(apply_filters('woocommerce_email_footer_text', get_option('woocommerce_email_footer_text')));