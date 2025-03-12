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


// function wp_myproduct_test_wp_mail() {
//     if (current_user_can('manage_options') && isset($_GET['test_wp_mail'])) {
//         $admin_email = get_option('admin_email');
//         $site_title = get_bloginfo('name');
        
//         $subject = 'Test Email from ' . $site_title;
//         $message = '<div style="padding: 20px; font-family: Arial, sans-serif;">
//             <h2>Test Email</h2>
//             <p>This is a test email from your WordPress site to verify email functionality.</p>
//             <p>If you are seeing this email, WordPress email functionality is working correctly!</p>
//             <p>Time sent: ' . current_time('mysql') . '</p>
//         </div>';
        
//         $headers = array(
//             'Content-Type: text/html; charset=UTF-8',
//             'From: ' . $site_title . ' <' . $admin_email . '>',
//         );
        
//         $result = wp_mail($admin_email, $subject, $message, $headers);
        
//         echo '<div style="background:#fff;padding:20px;margin:20px;border:1px solid #ccc;">';
//         echo '<h3>WordPress Mail Test</h3>';
//         echo '<p>Attempted to send test email to: ' . esc_html($admin_email) . '</p>';
//         echo '<p>Result: ' . ($result ? '<span style="color:green">Email sent successfully</span>' : '<span style="color:red">Email failed to send</span>') . '</p>';
        
//         if (!$result) {
//             echo '<h4>Troubleshooting Email Issues:</h4>';
//             echo '<ol>';
//             echo '<li>Check if your web host allows sending emails (many shared hosts restrict this)</li>';
//             echo '<li>Install an SMTP plugin like "WP Mail SMTP" or "Post SMTP"</li>';
//             echo '<li>Check your server\'s mail logs for any errors</li>';
//             echo '<li>Make sure your admin email address is correct</li>';
//             echo '<li>Check if your emails are being marked as spam</li>';
//             echo '</ol>';
//         }
        
//         // Add a link to test the admin notification specifically
//         echo '<h3>Test Admin Product Edit Notification</h3>';
//         echo '<p>To test the admin product notification specifically, click the button below:</p>';
//         echo '<a href="' . admin_url('admin.php?page=wc-status&tab=tools&test_admin_notification=1') . '" class="button button-primary">Test Admin Product Notification</a>';
        
//         echo '</div>';
//     }
// }
// add_action('admin_init', 'wp_myproduct_test_wp_mail');


// function wp_myproduct_test_admin_notification() {
//     if (current_user_can('manage_options') && isset($_GET['test_admin_notification'])) {
//         require_once plugin_dir_path(__FILE__) . 'includes/class-wp-myproduct-product-manager.php';
        
//         $products = wc_get_products(array('limit' => 1));
//         if (!empty($products)) {
//             $product_manager = new WP_MyProduct_Product_Manager();
            
//             $result = $product_manager->send_test_email();
            
//             echo '<div style="background:#fff;padding:20px;margin:20px;border:1px solid #ccc;">';
//             echo '<h3>Admin Product Notification Test</h3>';
//             echo '<p>Attempted to send admin product notification to: ' . esc_html(get_option('admin_email')) . '</p>';
//             echo '<p>Result: ' . ($result ? '<span style="color:green">Notification sent successfully</span>' : '<span style="color:red">Notification failed to send</span>') . '</p>';
            
//             if (!$result) {
//                 echo '<h4>Troubleshooting Steps:</h4>';
//                 echo '<ol>';
//                 echo '<li>Check server error logs for any issues with wp_mail()</li>';
//                 echo '<li>Ensure your admin email address is correct: ' . esc_html(get_option('admin_email')) . '</li>';
//                 echo '<li>Try using an SMTP plugin to improve email deliverability</li>';
//                 echo '</ol>';
//             }
            
//             echo '<p><a href="' . admin_url('admin.php?page=wc-status&tab=tools') . '" class="button">Return to Tools</a></p>';
//             echo '</div>';
//         } else {
//             echo '<div class="error"><p>No products found in the system to use for testing.</p></div>';
//         }
//     }
// }
// add_action('admin_init', 'wp_myproduct_test_admin_notification');