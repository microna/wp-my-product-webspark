<?php
/**
 * Class to handle product CRUD operations
 */
class WP_MyProduct_Product_Manager {


    public function __construct() {
        add_action('template_redirect', array($this, 'process_edit_product'));
        
        add_action('wp_myproduct_created', array($this, 'send_direct_notification'), 20, 2);
        add_action('wp_myproduct_updated', array($this, 'send_direct_notification'), 20, 2);
        
        add_action('woocommerce_process_product_meta', array($this, 'admin_product_edited'), 20, 2);
    }

    public function admin_product_edited($product_id, $post) {
        if ($post->post_status == 'auto-draft' || $post->post_date == $post->post_modified) {
            return;
        }
        
        $user_id = get_current_user_id();
        
        $this->send_direct_notification($product_id, $user_id, true);
    }

   
    public function send_direct_notification($product_id, $user_id, $is_admin_edit = false) {
        $product = wc_get_product($product_id);
        $user = get_user_by('id', $user_id);
        
        if (!$product || !$user) {
            return;
        }
        
        if (!$is_admin_edit) {
            $is_edit = did_action('wp_myproduct_updated') > did_action('wp_myproduct_created');
        } else {
            $is_edit = true;
        }
        
        // Set up email data
        $admin_email = get_option('admin_email');
        $site_title = get_bloginfo('name');
        
        // Customize subject based on context
        if ($is_admin_edit) {
            $subject = sprintf('[%s] Product edited in admin: %s', $site_title, $product->get_name());
        } else {
            $subject = $is_edit 
                ? sprintf('[%s] Product edited by user: %s', $site_title, $product->get_name())
                : sprintf('[%s] New product submitted: %s', $site_title, $product->get_name());
        }
            
        $product_edit_url = admin_url('post.php?post=' . $product_id . '&action=edit');
        $user_edit_url = admin_url('user-edit.php?user_id=' . $user_id);
        
        $message = '<div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;">';
        
        if ($is_admin_edit) {
            $message .= '<h2 style="color: #7f54b3;">Product Edited in Admin</h2>';
            $message .= '<p>A product has been edited in the admin area by ' . esc_html($user->display_name) . '.</p>';
        } else {
            $message .= '<h2 style="color: #7f54b3;">' . ($is_edit ? 'Product Edited by User' : 'New Product Submitted') . '</h2>';
            
            if ($is_edit) {
                $message .= '<p>A product has been edited by ' . esc_html($user->display_name) . '.</p>';
            } else {
                $message .= '<p>A new product has been submitted by ' . esc_html($user->display_name) . '.</p>';
            }
        }
        
        $message .= '<h3>' . esc_html($product->get_name()) . '</h3>';
        
        $message .= '<table style="width: 100%; border-collapse: collapse; margin: 20px 0;">';
        $message .= '<tr>';
        $message .= '<th style="text-align: left; padding: 8px; border: 1px solid #ddd;">Price:</th>';
        $message .= '<td style="padding: 8px; border: 1px solid #ddd;">' . wc_price($product->get_price()) . '</td>';
        $message .= '</tr>';
        $message .= '<tr>';
        $message .= '<th style="text-align: left; padding: 8px; border: 1px solid #ddd;">Stock Quantity:</th>';
        $message .= '<td style="padding: 8px; border: 1px solid #ddd;">' . esc_html($product->get_stock_quantity()) . '</td>';
        $message .= '</tr>';
        $message .= '<tr>';
        $message .= '<th style="text-align: left; padding: 8px; border: 1px solid #ddd;">Status:</th>';
        $message .= '<td style="padding: 8px; border: 1px solid #ddd;">' . esc_html(ucfirst($product->get_status())) . '</td>';
        $message .= '</tr>';
        $message .= '</table>';
        
        $message .= '<p>';
        $message .= '<a href="' . esc_url($product_edit_url) . '" style="display: inline-block; background-color: #7f54b3; color: #fff; padding: 10px 15px; margin-right: 10px; text-decoration: none; border-radius: 3px;">Edit Product</a>';
        $message .= '<a href="' . esc_url($user_edit_url) . '" style="display: inline-block; background-color: #666; color: #fff; padding: 10px 15px; text-decoration: none; border-radius: 3px;">View Author</a>';
        $message .= '</p>';
        
        if ($product->get_description()) {
            $message .= '<h3>Product Description:</h3>';
            $message .= '<div>' . wpautop(wptexturize($product->get_description())) . '</div>';
        }
        
        $message .= '<p style="margin-top: 30px; font-size: 12px; color: #666;">This notification was sent from your WooCommerce store.</p>';
        $message .= '</div>';
        
        // Set email headers
        $headers = array(
            'Content-Type: text/html; charset=UTF-8',
            'From: ' . $site_title . ' <' . $admin_email . '>',
        );
        return wp_mail($admin_email, $subject, $message, $headers);
    }

  
    public function send_test_email($email_to = '') {
        if (empty($email_to)) {
            $email_to = get_option('admin_email');
        }
        
        $site_title = get_bloginfo('name');
        $subject = '[' . $site_title . '] Test Notification Email';
        
        $message = '<div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;">';
        $message .= '<h2 style="color: #7f54b3;">Test Email Notification</h2>';
        $message .= '<p>This is a test email to confirm the notification system is working correctly.</p>';
        $message .= '<p>If you received this email, the notification system is functioning properly!</p>';
        $message .= '<p>Sent at: ' . current_time('mysql') . '</p>';
        $message .= '<p style="margin-top: 30px; font-size: 12px; color: #666;">This test notification was sent from your WooCommerce store.</p>';
        $message .= '</div>';
        
        $headers = array(
            'Content-Type: text/html; charset=UTF-8',
            'From: ' . $site_title . ' <' . $email_to . '>',
        );
        
        return wp_mail($email_to, $subject, $message, $headers);
    }

   
    public function add_product($data) {
        if (!is_user_logged_in()) {
            return new WP_Error('not_logged_in', __('You must be logged in to add products.', 'wp-my-product-webspark'));
        }

        if (empty($data['product_name'])) {
            return new WP_Error('empty_name', __('Product name is required.', 'wp-my-product-webspark'));
        }
        
        $product = new WC_Product_Simple();
        $product->set_name($data['product_name']);
        $product->set_description(isset($data['product_description']) ? $data['product_description'] : '');
        $product->set_short_description(isset($data['product_short_description']) ? $data['product_short_description'] : '');
        $product->set_regular_price(isset($data['product_price']) ? $data['product_price'] : 0);
        $product->set_stock_quantity(isset($data['product_stock']) ? $data['product_stock'] : 0);
        $product->set_status('pending'); // Set as pending for review
        
        $product_id = $product->save();
        
        if (!empty($data['product_image_id'])) {
            update_post_meta($product_id, '_thumbnail_id', $data['product_image_id']);
        }
        
        do_action('wp_myproduct_created', $product_id, get_current_user_id());
        
        return $product_id;
    }
    
    
    public function update_product($product_id, $data) {
        if (!is_user_logged_in()) {
            return new WP_Error('not_logged_in', __('You must be logged in to update products.', 'wp-my-product-webspark'));
        }
        
        $product = wc_get_product($product_id);
        if (!$product) {
            return new WP_Error('invalid_product', __('Invalid product.', 'wp-my-product-webspark'));
        }
        
        $product_post = get_post($product_id);
        if ($product_post->post_author != get_current_user_id() && !current_user_can('edit_others_posts')) {
            return new WP_Error('not_owner', __('You do not have permission to edit this product.', 'wp-my-product-webspark'));
        }
        
        if (isset($data['product_name'])) {
            $product->set_name($data['product_name']);
        }
        
        if (isset($data['product_description'])) {
            $product->set_description($data['product_description']);
        }
        
        if (isset($data['product_short_description'])) {
            $product->set_short_description($data['product_short_description']);
        }
        
        if (isset($data['product_price'])) {
            $product->set_regular_price($data['product_price']);
        }
        
        if (isset($data['product_stock'])) {
            $product->set_stock_quantity($data['product_stock']);
        }
        
        $product->save();
        
        if (!empty($data['product_image_id'])) {
            update_post_meta($product_id, '_thumbnail_id', $data['product_image_id']);
        }
        
        do_action('wp_myproduct_updated', $product_id, get_current_user_id());
        
        return $product_id;
    }

    
    public function delete_product($product_id) {
        if (!is_user_logged_in()) {
            return new WP_Error('not_logged_in', __('You must be logged in to delete products.', 'wp-my-product-webspark'));
        }

        $product = wc_get_product($product_id);
        if (!$product || get_post_field('post_author', $product_id) != get_current_user_id()) {
            return new WP_Error('invalid_product', __('You do not have permission to delete this product.', 'wp-my-product-webspark'));
        }

        $product->delete(true);

        return true;
    }

    
    public function get_user_products() {
        if (!is_user_logged_in()) {
            return array();
        }

        $args = array(
            'post_type' => 'product',
            'post_status' => array('publish', 'draft', 'pending'),
            'posts_per_page' => -1,
            'author' => get_current_user_id(),
        );

        $products = array();
        $query = new WP_Query($args);

        if ($query->have_posts()) {
            while ($query->have_posts()) {
                $query->the_post();
                $product_id = get_the_ID();
                $product = wc_get_product($product_id);
                $products[] = $product;
            }
            wp_reset_postdata();
        }

        return $products;
    }

  
    public function process_edit_product() {
        if (!is_account_page() || !isset($_GET['edit-product'])) {
            return;
        }

        if (isset($_POST['submit_edit_product']) && wp_verify_nonce($_POST['_wpnonce'], 'edit_product_nonce')) {
            $product_id = intval($_POST['product_id']);
            $result = $this->update_product($product_id, $_POST);
            
            if (is_wp_error($result)) {
                wc_add_notice($result->get_error_message(), 'error');
            } else {
                wc_add_notice(__('Product updated successfully!', 'wp-my-product-webspark'), 'success');
            }

            wp_redirect(wc_get_account_endpoint_url('my-products'));
            exit;
        }
    }
}