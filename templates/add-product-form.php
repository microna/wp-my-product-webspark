<?php
/**
 * Template for the Add Product form
 */

if (!defined('ABSPATH')) {
    exit;
}
?>

<h2><?php _e('Add New Product', 'wp-my-product-webspark'); ?></h2>

<form method="post" class="woocommerce-form woocommerce-form-add-product">
    <?php wp_nonce_field('add_product_nonce'); ?>

    <p class="form-row form-row-wide">
        <label for="product_name"><?php _e('Product Name', 'wp-my-product-webspark'); ?> <span
                class="required">*</span></label>
        <input type="text" class="input-text" name="product_name" id="product_name" required>
    </p>

    <p class="form-row form-row-first">
        <label for="product_price"><?php _e('Product Price', 'wp-my-product-webspark'); ?>
            (<?php echo get_woocommerce_currency_symbol(); ?>) <span class="required">*</span></label>
        <input type="number" class="input-text" name="product_price" id="product_price" step="0.01" min="0" required>
    </p>

    <p class="form-row form-row-last">
        <label for="product_quantity"><?php _e('Quantity', 'wp-my-product-webspark'); ?> <span
                class="required">*</span></label>
        <input type="number" class="input-text" name="product_quantity" id="product_quantity" min="0" step="1" required>
    </p>

    <p class="form-row form-row-wide">
        <label for="product_description"><?php _e('Product Description', 'wp-my-product-webspark'); ?></label>
        <?php
        $content = '';
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
        <div id="product_image_preview"
            style="width: 100px; height: 100px; background-size: cover; background-position: center; margin-bottom: 10px; border: 1px solid #ddd;">
        </div>
        <input type="hidden" name="product_image_id" id="product_image_id">
        <button type="button" class="button"
            id="upload_product_image"><?php _e('Upload Image', 'wp-my-product-webspark'); ?></button>
        <button type="button" class="button" id="remove_product_image"
            style="display: none;"><?php _e('Remove Image', 'wp-my-product-webspark'); ?></button>
    </div>
    </p>

    <p class="form-row">
        <button type="submit" class="button" name="submit_add_product"
            value="<?php _e('Save Product', 'wp-my-product-webspark'); ?>"><?php _e('Save Product', 'wp-my-product-webspark'); ?></button>
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