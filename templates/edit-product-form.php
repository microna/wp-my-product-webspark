<?php
/**
 * Template for the Edit Product form
 */

if (!defined('ABSPATH')) {
    exit;
}

add_action('woocommerce_account_edit-product_endpoint', 'wp_myproduct_edit_product_content');

function wp_myproduct_edit_product_content() {
    if (!is_user_logged_in()) {
        echo '<p>' . __('You must be logged in to edit products.', 'wp-my-product-webspark') . '</p>';
        return;
    }

    global $wp;
    $product_id = absint($wp->query_vars['edit-product']);
    
    if (!$product_id) {
        echo '<p>' . __('Invalid product.', 'wp-my-product-webspark') . '</p>';
        return;
    }
    
    $product = wc_get_product($product_id);
    
    if (!$product || get_post_field('post_author', $product_id) != get_current_user_id()) {
        echo '<p>' . __('You do not have permission to edit this product.', 'wp-my-product-webspark') . '</p>';
        return;
    }
    
    ?>
<h2><?php _e('Edit Product', 'wp-my-product-webspark'); ?></h2>

<form method="post" class="woocommerce-form woocommerce-form-edit-product">
    <?php wp_nonce_field('edit_product_nonce'); ?>
    <input type="hidden" name="product_id" value="<?php echo esc_attr($product_id); ?>">

    <p class="form-row form-row-wide">
        <label for="product_name"><?php _e('Product Name', 'wp-my-product-webspark'); ?> <span
                class="required">*</span></label>
        <input type="text" class="input-text" name="product_name" id="product_name"
            value="<?php echo esc_attr($product->get_name()); ?>" required>
    </p>

    <p class="form-row form-row-first">
        <label for="product_price"><?php _e('Product Price', 'wp-my-product-webspark'); ?>
            (<?php echo get_woocommerce_currency_symbol(); ?>) <span class="required">*</span></label>
        <input type="number" class="input-text" name="product_price" id="product_price" step="0.01" min="0"
            value="<?php echo esc_attr($product->get_regular_price()); ?>" required>
    </p>

    <p class="form-row form-row-last">
        <label for="product_quantity"><?php _e('Quantity', 'wp-my-product-webspark'); ?> <span
                class="required">*</span></label>
        <input type="number" class="input-text" name="product_quantity" id="product_quantity" min="0" step="1"
            value="<?php echo esc_attr($product->get_stock_quantity()); ?>" required>
    </p>

    <p class="form-row form-row-wide">
        <label for="product_description"><?php _e('Product Description', 'wp-my-product-webspark'); ?></label>
        <?php
            $content = $product->get_description();
            $editor_id = 'product_description';
            $settings = array(
                'media_buttons' => false,
                'textarea_name' => 'product_description',
                'textarea_rows' => 10,
                'teeny' => true,
            );
            wp_editor($content, $editor_id, $settings);
            ?>
    </p>

    <p class="form-row form-row-wide">
        <label for="product_image"><?php _e('Product Image', 'wp-my-product-webspark'); ?></label>
    <div class="product-image-wrapper">
        <?php 
                $image_id = $product->get_image_id();
                $image_url = $image_id ? wp_get_attachment_image_url($image_id, 'thumbnail') : '';
                $image_style = $image_url ? 'background-image: url(' . esc_url($image_url) . ');' : '';
                ?>
        <div id="product_image_preview"
            style="width: 100px; height: 100px; background-size: cover; background-position: center; margin-bottom: 10px; border: 1px solid #ddd; <?php echo $image_style; ?>">
        </div>
        <input type="hidden" name="product_image_id" id="product_image_id" value="<?php echo esc_attr($image_id); ?>">
        <button type="button" class="button"
            id="upload_product_image"><?php _e('Upload/Change Image', 'wp-my-product-webspark'); ?></button>
        <button type="button" class="button" id="remove_product_image"
            style="<?php echo $image_id ? '' : 'display: none;'; ?>"><?php _e('Remove Image', 'wp-my-product-webspark'); ?></button>
    </div>
    </p>

    <p class="form-row">
        <button type="submit" class="button" name="submit_edit_product"
            value="<?php _e('Update Product', 'wp-my-product-webspark'); ?>"><?php _e('Update Product', 'wp-my-product-webspark'); ?></button>
        <a href="<?php echo esc_url(wc_get_account_endpoint_url('my-products')); ?>"
            class="button"><?php _e('Cancel', 'wp-my-product-webspark'); ?></a>
    </p>
</form>

<script type="text/javascript">
jQuery(document).ready(function($) {
    var mediaUploader;

    $('#upload_product_image').on('click', function(e) {
        e.preventDefault();

        if (mediaUploader) {
            mediaUploader.open();
            return;
        }

        mediaUploader = wp.media({
            title: '<?php _e('Select Product Image', 'wp-my-product-webspark'); ?>',
            button: {
                text: '<?php _e('Use this image', 'wp-my-product-webspark'); ?>'
            },
            library: {
                type: 'image',
                uploadedTo: null,
                author: '<?php echo get_current_user_id(); ?>'
            },
            multiple: false
        });

        mediaUploader.on('select', function() {
            var attachment = mediaUploader.state().get('selection').first().toJSON();
            $('#product_image_id').val(attachment.id);
            $('#product_image_preview').css('background-image', 'url(' + attachment.url + ')');
            $('#remove_product_image').show();
        });

        mediaUploader.open();
    });

    $('#remove_product_image').on('click', function(e) {
        e.preventDefault();
        $('#product_image_id').val('');
        $('#product_image_preview').css('background-image', 'none');
        $(this).hide();
    });
});
</script>
<?php
}