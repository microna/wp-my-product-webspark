<?php
/**
 * Admin new product email (HTML)
 */

if (!defined('ABSPATH')) {
    exit;
}


do_action('woocommerce_email_header', $email_heading, $email); ?>

<?php if ($is_edit) : ?>
<p><?php printf(__('A product has been edited by %s.', 'wp-my-product-webspark'), esc_html($user->display_name)); ?></p>
<?php else : ?>
<p><?php printf(__('A new product has been submitted by %s.', 'wp-my-product-webspark'), esc_html($user->display_name)); ?>
</p>
<?php endif; ?>

<h2><?php echo esc_html($product->get_name()); ?></h2>

<table class="td" cellspacing="0" cellpadding="6"
    style="width: 100%; font-family: 'Helvetica Neue', Helvetica, Roboto, Arial, sans-serif; margin-bottom: 20px;"
    border="1">
    <tbody>
        <tr>
            <th class="td" scope="row" style="text-align:left;"><?php esc_html_e('Price:', 'wp-my-product-webspark'); ?>
            </th>
            <td class="td"><?php echo wp_kses_post($product->get_price_html()); ?></td>
        </tr>
        <tr>
            <th class="td" scope="row" style="text-align:left;">
                <?php esc_html_e('Stock Quantity:', 'wp-my-product-webspark'); ?></th>
            <td class="td"><?php echo esc_html($product->get_stock_quantity()); ?></td>
        </tr>
        <tr>
            <th class="td" scope="row" style="text-align:left;">
                <?php esc_html_e('Status:', 'wp-my-product-webspark'); ?></th>
            <td class="td"><?php echo esc_html(ucfirst($product->get_status())); ?></td>
        </tr>
    </tbody>
</table>

<p>
    <a href="<?php echo esc_url($product_edit_url); ?>"
        class="button button-primary"><?php esc_html_e('Edit Product', 'wp-my-product-webspark'); ?></a>
    <a href="<?php echo esc_url($user_edit_url); ?>"
        class="button"><?php esc_html_e('View Author', 'wp-my-product-webspark'); ?></a>
</p>

<?php if ($product->get_description()) : ?>
<h3><?php esc_html_e('Product Description:', 'wp-my-product-webspark'); ?></h3>
<div>
    <?php echo wp_kses_post(wpautop(wptexturize($product->get_description()))); ?>
</div>
<?php endif; ?>

<?php

if ($additional_content) {
    echo wp_kses_post(wpautop(wptexturize($additional_content)));
}

do_action('woocommerce_email_footer', $email);