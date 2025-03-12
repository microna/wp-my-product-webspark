<?php
/**
 * Custom email class for product notifications
 */

if (!defined('ABSPATH')) {
    exit; 
}

if (!class_exists('WC_Email')) {
    if (function_exists('WC')) {
        include_once WC()->plugin_path() . '/includes/emails/class-wc-email.php';
    } else {
        return;
    }
}


class WP_MyProduct_Email extends WC_Email {
    public $product_id;
    public $user_id;
    public $is_edit;
    public function __construct() {
        $this->id             = 'wp_myproduct_notification';
        $this->title          = __('User product notification', 'wp-my-product-webspark');
        $this->description    = __('Notification emails are sent to the admin when a user creates or edits a product.', 'wp-my-product-webspark');
        $this->template_html  = 'emails/admin-new-product.php';
        $this->template_plain = 'emails/plain/admin-new-product.php';
        $this->placeholders   = array(
            '{site_title}'   => $this->get_blogname(),
            '{product_name}' => '',
            '{user_name}'    => ''
        );
        
        parent::__construct();
        
        $this->recipient = $this->get_option('recipient', get_option('admin_email'));
        
        $this->init_form_fields();
        
        add_action('wp_myproduct_created', array($this, 'trigger_created'), 10, 2);
        add_action('wp_myproduct_updated', array($this, 'trigger_updated'), 10, 2);
    }

    
    public function get_default_subject() {
        return $this->is_edit 
            ? __('[{site_title}] Product edited by user: {product_name}', 'wp-my-product-webspark')
            : __('[{site_title}] New product submitted: {product_name}', 'wp-my-product-webspark');
    }

  
    public function get_default_heading() {
        return $this->is_edit 
            ? __('Product edited by user', 'wp-my-product-webspark')
            : __('New product submitted', 'wp-my-product-webspark');
    }

    /**
     * Trigger the sending of this email for a new product.
     *
     * @param int $product_id The product ID.
     * @param int $user_id The user ID.
     */
    public function trigger_created($product_id, $user_id) {
        $this->is_edit = false;
        $this->setup_and_send($product_id, $user_id);
    }

    /**
     * Trigger the sending of this email for an updated product.
     *
     * @param int $product_id The product ID.
     * @param int $user_id The user ID.
     */
    public function trigger_updated($product_id, $user_id) {
        $this->is_edit = true;
        $this->setup_and_send($product_id, $user_id);
    }

    /**
     * Setup and send the email.
     *
     * @param int $product_id The product ID.
     * @param int $user_id The user ID.
     */
    private function setup_and_send($product_id, $user_id) {
        if (!$this->is_enabled()) {
            return;
        }
        
        $this->product_id = $product_id;
        $this->user_id = $user_id;
        
        $product = wc_get_product($product_id);
        $user = get_user_by('id', $user_id);
        
        if (!$product || !$user) {
            return;
        }
        
        $this->placeholders['{product_name}'] = $product->get_name();
        $this->placeholders['{user_name}'] = $user->display_name;
        
        $this->data = array(
            'product' => $product,
            'user' => $user,
            'product_edit_url' => admin_url('post.php?post=' . $product_id . '&action=edit'),
            'user_edit_url' => admin_url('user-edit.php?user_id=' . $user_id),
            'is_edit' => $this->is_edit
        );
        
        $result = $this->send($this->get_recipient(), $this->get_subject(), $this->get_content(), $this->get_headers(), $this->get_attachments());
    }

    /**
     * Get content html.
     *
     * @return string
     */
    public function get_content_html() {
        return wc_get_template_html(
            $this->template_html,
            array(
                'email_heading' => $this->get_heading(),
                'product' => $this->data['product'],
                'user' => $this->data['user'],
                'product_edit_url' => $this->data['product_edit_url'],
                'user_edit_url' => $this->data['user_edit_url'],
                'is_edit' => $this->data['is_edit'],
                'additional_content' => $this->get_additional_content(),
                'email' => $this,
            ),
            '',
            $this->template_base
        );
    }

    /**
     * Get content plain.
     *
     * @return string
     */
    public function get_content_plain() {
        return wc_get_template_html(
            $this->template_plain,
            array(
                'email_heading' => $this->get_heading(),
                'product' => $this->data['product'],
                'user' => $this->data['user'],
                'product_edit_url' => $this->data['product_edit_url'],
                'user_edit_url' => $this->data['user_edit_url'],
                'is_edit' => $this->data['is_edit'],
                'additional_content' => $this->get_additional_content(),
                'email' => $this,
            ),
            '',
            $this->template_base
        );
    }
    public function init_form_fields() {
        $this->form_fields = array(
            'enabled' => array(
                'title'         => __('Enable/Disable', 'wp-my-product-webspark'),
                'type'          => 'checkbox',
                'label'         => __('Enable this email notification', 'wp-my-product-webspark'),
                'default'       => 'yes',
            ),
            'recipient' => array(
                'title'         => __('Recipient(s)', 'wp-my-product-webspark'),
                'type'          => 'text',
                'description'   => __('Enter recipients (comma separated) for this email. Defaults to admin email.', 'wp-my-product-webspark'),
                'placeholder'   => get_option('admin_email'),
                'default'       => get_option('admin_email'),
            ),
            'subject' => array(
                'title'         => __('Subject', 'wp-my-product-webspark'),
                'type'          => 'text',
                'description'   => __('This controls the email subject line. Leave blank to use the default subject.', 'wp-my-product-webspark'),
                'placeholder'   => $this->get_default_subject(),
                'default'       => '',
            ),
            'heading' => array(
                'title'         => __('Email Heading', 'wp-my-product-webspark'),
                'type'          => 'text',
                'description'   => __('This controls the main heading in the email notification. Leave blank to use the default heading.', 'wp-my-product-webspark'),
                'placeholder'   => $this->get_default_heading(),
                'default'       => '',
            ),
            'additional_content' => array(
                'title'         => __('Additional Content', 'wp-my-product-webspark'),
                'description'   => __('Text to appear below the main email content.', 'wp-my-product-webspark'),
                'type'          => 'textarea',
                'default'       => $this->get_default_additional_content(),
                'placeholder'   => __('N/A', 'wp-my-product-webspark'),
            ),
            'email_type' => array(
                'title'         => __('Email Type', 'wp-my-product-webspark'),
                'type'          => 'select',
                'description'   => __('Choose which format of email to send.', 'wp-my-product-webspark'),
                'default'       => 'html',
                'class'         => 'email_type',
                'options'       => array(
                    'plain'     => __('Plain text', 'wp-my-product-webspark'),
                    'html'      => __('HTML', 'wp-my-product-webspark'),
                    'multipart' => __('Multipart', 'wp-my-product-webspark'),
                ),
            ),
        );
    }

    /**
     * Get default additional content.
     *
     * @return string
     */
    public function get_default_additional_content() {
        return __('This notification was sent from your WooCommerce store.', 'wp-my-product-webspark');
    }
}