<?php
/**
 * Template for the My Products page
 */

if (!defined('ABSPATH')) {
    exit;
}
?>

<h2><?php _e('My Products', 'wp-my-product-webspark'); ?></h2>

<?php 
$current_page = isset($_GET['product_page']) ? max(1, intval($_GET['product_page'])) : 1;
$per_page = 10; 
$total_products = count($products);
$max_pages = ceil($total_products / $per_page);

$products_slice = array_slice($products, ($current_page - 1) * $per_page, $per_page);

if (empty($products)) : ?>
<p><?php _e('You have not added any products yet.', 'wp-my-product-webspark'); ?></p>
<p><a href="<?php echo esc_url(wc_get_account_endpoint_url('add-product')); ?>"
        class="button"><?php _e('Add Product', 'wp-my-product-webspark'); ?></a></p>
<?php else : ?>
<table
    class="woocommerce-orders-table woocommerce-MyAccount-orders shop_table shop_table_responsive my_account_orders account-orders-table">
    <thead>
        <tr>
            <th class="woocommerce-orders-table__header product-image">
                <span class="nobr"><?php _e('Image', 'wp-my-product-webspark'); ?></span>
            </th>
            <th class="woocommerce-orders-table__header product-name">
                <span class="nobr"><?php _e('Name', 'wp-my-product-webspark'); ?></span>
            </th>
            <th class="woocommerce-orders-table__header product-price">
                <span class="nobr"><?php _e('Price', 'wp-my-product-webspark'); ?></span>
            </th>
            <th class="woocommerce-orders-table__header product-stock">
                <span class="nobr"><?php _e('Stock', 'wp-my-product-webspark'); ?></span>
            </th>
            <th class="woocommerce-orders-table__header product-status">
                <span class="nobr"><?php _e('Status', 'wp-my-product-webspark'); ?></span>
            </th>
            <th class="woocommerce-orders-table__header product-actions">
                <span class="nobr"><?php _e('Actions', 'wp-my-product-webspark'); ?></span>
            </th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($products_slice as $product) : ?>
        <tr class="woocommerce-orders-table__row">
            <td class="woocommerce-orders-table__cell product-image">
                <?php echo $product->get_image(array(50, 50)); ?>
            </td>
            <td class="woocommerce-orders-table__cell product-name">
                <?php echo esc_html($product->get_name()); ?>
            </td>
            <td class="woocommerce-orders-table__cell product-price">
                <?php echo $product->get_price_html(); ?>
            </td>
            <td class="woocommerce-orders-table__cell product-stock">
                <?php 
                        if ($product->get_manage_stock()) {
                            echo esc_html($product->get_stock_quantity());
                        } else {
                            echo __('N/A', 'wp-my-product-webspark');
                        }
                        ?>
            </td>
            <td class="woocommerce-orders-table__cell product-status">
                <?php 
                        $status = $product->get_status();
                        switch ($status) {
                            case 'publish':
                                echo '<span class="product-status-published">' . __('Published', 'wp-my-product-webspark') . '</span>';
                                break;
                            case 'pending':
                                echo '<span class="product-status-pending">' . __('Pending Review', 'wp-my-product-webspark') . '</span>';
                                break;
                            case 'draft':
                                echo '<span class="product-status-draft">' . __('Draft', 'wp-my-product-webspark') . '</span>';
                                break;
                            default:
                                echo '<span class="product-status-' . esc_attr($status) . '">' . esc_html($status) . '</span>';
                                break;
                        }
                        ?>
            </td>
            <td class="woocommerce-orders-table__cell product-actions">
                <?php if (isset($can_edit_in_admin) && $can_edit_in_admin) : ?>
                <a href="<?php echo esc_url(admin_url('post.php?post=' . $product->get_id() . '&action=edit')); ?>"
                    class="button view" target="_blank"><?php _e('Edit in Admin', 'wp-my-product-webspark'); ?></a>
                <?php else : ?>
                <span class="button disabled"
                    title="<?php esc_attr_e('You don\'t have permission to edit in admin', 'wp-my-product-webspark'); ?>"><?php _e('Edit', 'wp-my-product-webspark'); ?></span>
                <?php endif; ?>
                <a href="<?php echo esc_url(add_query_arg(array('action' => 'delete', 'product_id' => $product->get_id(), '_wpnonce' => wp_create_nonce('delete_product_' . $product->get_id())), wc_get_account_endpoint_url('my-products'))); ?>"
                    class="button delete"
                    onclick="return confirm('<?php _e('Are you sure you want to delete this product?', 'wp-my-product-webspark'); ?>');"><?php _e('Delete', 'wp-my-product-webspark'); ?></a>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<?php if ($max_pages > 1) : ?>
<div class="woocommerce-pagination woocommerce-pagination--without-numbers products-pagination">
    <div class="pagination-links">
        <?php if ($current_page > 1) : ?>
        <a class="prev page-numbers"
            href="<?php echo esc_url(add_query_arg('product_page', $current_page - 1)); ?>"><?php _e('Previous', 'wp-my-product-webspark'); ?></a>
        <?php endif; ?>

        <?php
                // Show page numbers
                for ($i = 1; $i <= $max_pages; $i++) {
                    if ($i == $current_page) {
                        echo '<span class="page-numbers current">' . $i . '</span>';
                    } else {
                        echo '<a class="page-numbers" href="' . esc_url(add_query_arg('product_page', $i)) . '">' . $i . '</a>';
                    }
                }
                ?>

        <?php if ($current_page < $max_pages) : ?>
        <a class="next page-numbers"
            href="<?php echo esc_url(add_query_arg('product_page', $current_page + 1)); ?>"><?php _e('Next', 'wp-my-product-webspark'); ?></a>
        <?php endif; ?>
    </div>
</div>
<?php endif; ?>

<p><a href="<?php echo esc_url(wc_get_account_endpoint_url('add-product')); ?>"
        class="button"><?php _e('Add New Product', 'wp-my-product-webspark'); ?></a></p>
<?php endif; ?>