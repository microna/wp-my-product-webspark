<?php
/**
 * Class to handle WooCommerce account endpoints for product management
 */
class WP_MyProduct_Account_Endpoints {

public function __construct() {
        add_action('init', array($this, 'add_endpoints'));
        
        add_filter('woocommerce_account_menu_items', array($this, 'add_menu_items'));
        
        add_action('woocommerce_account_add-product_endpoint', array($this, 'add_product_content'));
        add_action('woocommerce_account_my-products_endpoint', array($this, 'my_products_content'));
        
        add_filter('query_vars', array($this, 'add_query_vars'), 0);
    }


    public function add_endpoints() {
        add_rewrite_endpoint('add-product', EP_ROOT | EP_PAGES);
        add_rewrite_endpoint('my-products', EP_ROOT | EP_PAGES);
        add_rewrite_endpoint('edit-product', EP_ROOT | EP_PAGES);
    }

   
    public function add_menu_items($items) {
        $logout = $items['customer-logout'];
        unset($items['customer-logout']);
        
        $items['add-product'] = __('Add Product', 'wp-my-product-webspark');
        $items['my-products'] = __('My Products', 'wp-my-product-webspark');
        
        $items['customer-logout'] = $logout;
        
        return $items;
    }

   
    public function add_query_vars($vars) {
        $vars[] = 'add-product';
        $vars[] = 'my-products';
        $vars[] = 'edit-product';
        $vars[] = 'product_id';
        return $vars;
    }

    
    public function add_product_content() {
        if (!is_user_logged_in()) {
            echo '<p>' . __('You must be logged in to add products.', 'wp-my-product-webspark') . '</p>';
            return;
        }

        if (isset($_POST['submit_add_product']) && wp_verify_nonce($_POST['_wpnonce'], 'add_product_nonce')) {
            $product_manager = new WP_MyProduct_Product_Manager();
            $result = $product_manager->add_product($_POST);
            
            if (is_wp_error($result)) {
                echo '<div class="woocommerce-error">' . $result->get_error_message() . '</div>';
            } else {
                echo '<div class="woocommerce-message">' . __('Product added successfully!', 'wp-my-product-webspark') . '</div>';
            }
        }

        include_once plugin_dir_path(dirname(__FILE__)) . 'templates/add-product-form.php';
    }

 
    public function my_products_content() {
        if (!is_user_logged_in()) {
            echo '<p>' . __('You must be logged in to view your products.', 'wp-my-product-webspark') . '</p>';
            return;
        }

        if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['product_id'])) {
            if (isset($_GET['_wpnonce']) && wp_verify_nonce($_GET['_wpnonce'], 'delete_product_' . $_GET['product_id'])) {
                $product_manager = new WP_MyProduct_Product_Manager();
                $result = $product_manager->delete_product($_GET['product_id']);
                
                if (is_wp_error($result)) {
                    echo '<div class="woocommerce-error">' . $result->get_error_message() . '</div>';
                } else {
                    echo '<div class="woocommerce-message">' . __('Product deleted successfully!', 'wp-my-product-webspark') . '</div>';
                }
            }
        }

        $product_manager = new WP_MyProduct_Product_Manager();
        $products = $product_manager->get_user_products();

        $can_edit_in_admin = current_user_can('edit_products');

        include_once plugin_dir_path(dirname(__FILE__)) . 'templates/my-products.php';
    }
}