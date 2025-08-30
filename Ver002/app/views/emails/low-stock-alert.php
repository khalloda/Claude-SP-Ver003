<?php
// Low stock alert email template
$title = t('emails.low_stock.title', 'Low Stock Alert');
$subtitle = t('emails.low_stock.subtitle', 'Inventory levels require attention');
$greeting = t('emails.low_stock.greeting', 'Hello :name!', ['name' => $user_name ?? 'Inventory Manager']);
$show_signature = true;

ob_start();
?>

<p>
    <?= t('emails.low_stock.intro', 'This is an automated notification to inform you that some products in your inventory have fallen below their minimum stock levels.') ?>
</p>

<div class="info-box warning">
    <h4><?= t('emails.urgent_attention', 'Urgent Attention Required') ?></h4>
    <p>
        <?= t('emails.low_stock.urgent_message', 'The following products require immediate restocking to avoid potential stockouts:') ?>
    </p>
</div>

<?php if (!empty($products)): ?>
<table class="data-table">
    <thead>
        <tr>
            <th><?= t('products.product_name') ?></th>
            <th><?= t('products.sku') ?></th>
            <th><?= t('products.current_stock') ?></th>
            <th><?= t('products.minimum_stock') ?></th>
            <th><?= t('products.recommended_order') ?></th>
            <th><?= t('products.status') ?></th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($products as $product): ?>
        <tr>
            <td><strong><?= htmlspecialchars($product['name'] ?? '') ?></strong></td>
            <td><?= htmlspecialchars($product['sku'] ?? '') ?></td>
            <td style="color: #dc3545; font-weight: bold;"><?= number_format($product['current_stock'] ?? 0) ?></td>
            <td><?= number_format($product['minimum_stock'] ?? 0) ?></td>
            <td><?= number_format($product['recommended_order'] ?? 0) ?></td>
            <td>
                <?php 
                $status = 'critical';
                if (($product['current_stock'] ?? 0) <= 0) {
                    $status = 'out-of-stock';
                } elseif (($product['current_stock'] ?? 0) < ($product['minimum_stock'] ?? 0) * 0.5) {
                    $status = 'critical';
                } else {
                    $status = 'low';
                }
                ?>
                <span class="status-badge <?= $status === 'out-of-stock' ? 'danger' : ($status === 'critical' ? 'warning' : 'info') ?>">
                    <?= t('inventory.status.' . str_replace('-', '_', $status), ucfirst(str_replace('-', ' ', $status))) ?>
                </span>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>
<?php else: ?>
<p><?= t('emails.low_stock.no_products', 'No products with low stock levels found.') ?></p>
<?php endif; ?>

<div class="btn-container">
    <a href="<?= $inventory_url ?? '/products' ?>" class="btn">
        <?= t('emails.low_stock.view_inventory', 'View Full Inventory') ?>
    </a>
</div>

<div class="info-box">
    <h4><?= t('emails.recommended_actions', 'Recommended Actions') ?></h4>
    <ul style="margin: 10px 0; padding-left: 20px;">
        <li><?= t('emails.low_stock.action1', 'Review current stock levels and update minimum thresholds if needed') ?></li>
        <li><?= t('emails.low_stock.action2', 'Contact suppliers to place urgent orders for critical items') ?></li>
        <li><?= t('emails.low_stock.action3', 'Consider alternative suppliers for faster delivery') ?></li>
        <li><?= t('emails.low_stock.action4', 'Update customers about potential delivery delays if applicable') ?></li>
    </ul>
</div>

<p>
    <strong><?= t('emails.report_generated', 'Report Generated:') ?></strong> <?= date('M d, Y H:i T') ?><br>
    <strong><?= t('emails.next_check', 'Next Scheduled Check:') ?></strong> <?= $next_check ?? 'Tomorrow at 9:00 AM' ?>
</p>

<p style="font-size: 14px; color: #666666;">
    <?= t('emails.low_stock.footer_note', 'This alert is sent automatically based on your inventory monitoring settings. You can adjust these settings in your account preferences.') ?>
</p>

<?php
$content = ob_get_clean();
include 'layout.php';
?>