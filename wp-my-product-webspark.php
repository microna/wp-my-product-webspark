<?php
/**
 * Plugin Name: WP My Product Webspark
 * Description: WP My Product Webspark
 * Version: 1.0.0
 * Author: Stas Shokarev
 * Text Domain: wp-my-product-webspark
 * Requires at least: 5.8
 * Requires PHP: 7.4
 * WC requires at least: 5.0
 */


if (!defined('ABSPATH')) {
    exit;
}


function wp_myproduct_webspark_check_woocommerce() {
    if (!in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')))) {
        add_action('admin_notices', 'wp_myproduct_webspark_woocommerce_missing_notice');
        return false;
    }
    return true;
}

function wp_myproduct_webspark_woocommerce_missing_notice() {
    ?>
<div class="error">
    <p><?php _e('WP My Product Webspark requires WooCommerce to be installed and active.', 'wp-my-product-webspark'); ?>
    </p>
</div>
<?php
}

function wp_myproduct_webspark_init() {
    if (!wp_myproduct_webspark_check_woocommerce()) {
        return;
    }

    require_once plugin_dir_path(__FILE__) . 'includes/class-wp-myproduct-account-endpoints.php';
    require_once plugin_dir_path(__FILE__) . 'includes/class-wp-myproduct-product-manager.php';
    require_once plugin_dir_path(__FILE__) . 'includes/class-wp-myproduct-email.php';

    new WP_MyProduct_Account_Endpoints();
    new WP_MyProduct_Product_Manager();
}
add_action('plugins_loaded', 'wp_myproduct_webspark_init');


function wp_myproduct_webspark_register_email($emails) {
    if (!class_exists('WP_MyProduct_Email')) {
        require_once plugin_dir_path(__FILE__) . 'includes/class-wp-myproduct-email.php';
    }
    
    $emails['wp_myproduct_notification'] = new WP_MyProduct_Email();
    
    return $emails;
}
add_filter('woocommerce_email_classes', 'wp_myproduct_webspark_register_email', 90);


function wp_myproduct_webspark_email_templates($template, $template_name, $template_path) {
    if (strpos($template_name, 'emails/admin-new-product.php') !== false || 
        strpos($template_name, 'emails/plain/admin-new-product.php') !== false) {
        
        $plugin_path = plugin_dir_path(__FILE__) . 'templates/';
        
        if (file_exists($plugin_path . $template_name)) {
            return $plugin_path . $template_name;
        }
    }
    
    return $template;
}
add_filter('woocommerce_locate_template', 'wp_myproduct_webspark_email_templates', 10, 3);


function wp_myproduct_webspark_activate() {
    if (wp_myproduct_webspark_check_woocommerce()) {
        require_once plugin_dir_path(__FILE__) . 'includes/class-wp-myproduct-account-endpoints.php';
        
        $endpoints = new WP_MyProduct_Account_Endpoints();
        $endpoints->add_endpoints();
        flush_rewrite_rules();
    }
}
register_activation_hook(__FILE__, 'wp_myproduct_webspark_activate');


function wp_myproduct_webspark_deactivate() {
    flush_rewrite_rules();
}
register_deactivation_hook(__FILE__, 'wp_myproduct_webspark_deactivate');


function wp_myproduct_webspark_enqueue_scripts() {
    if (is_account_page()) {
        wp_enqueue_style('wp-myproduct-webspark-style', plugin_dir_url(__FILE__) . 'assets/css/style.css', array(), '1.0.0');
        wp_enqueue_script('wp-myproduct-webspark-script', plugin_dir_url(__FILE__) . 'assets/js/script.js', array('jquery'), '1.0.0', true);
        
        wp_enqueue_media();
    }
}
add_action('wp_enqueue_scripts', 'wp_myproduct_webspark_enqueue_scripts');