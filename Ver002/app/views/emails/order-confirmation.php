<?php
// Order confirmation email template
$title = t('emails.order_confirmation.title', 'Order Confirmation');
$subtitle = t('emails.order_confirmation.subtitle', 'Your order has been received');
$greeting = t('emails.order_confirmation.greeting', 'Dear :name,', ['name' => $customer_name ?? 'Valued Customer']);
$show_signature = true;

ob_start();
?>

<p>
    <?= t('emails.order_confirmation.intro', 'Thank you for your order! We have successfully received your order and it is now being processed.') ?>
</p>

<div class="info-box success">
    <h4><?= t('emails.order_confirmed', 'Order Confirmed') ?></h4>
    <p>
        <strong><?= t('sales_orders.order_number') ?>:</strong> <?= htmlspecialchars($order_number ?? '') ?><br>
        <strong><?= t('sales_orders.order_date') ?>:</strong> <?= $order_date ?? date('M d, Y') ?><br>
        <strong><?= t('sales_orders.expected_delivery') ?>:</strong> <?= $expected_delivery ?? 'To be confirmed' ?>
    </p>
</div>

<div class="btn-container">
    <a href="<?= $order_url ?? '#' ?>" class="btn">
        <?= t('emails.order_confirmation.view_order', 'View Order Details') ?>
    </a>
</div>

<h3 style="color: #2c3e50; margin-top: 30px; margin-bottom: 20px;">
    <?= t('sales_orders.order_items') ?>
</h3>

<?php if (!empty($items)): ?>
<table class="data-table">
    <thead>
        <tr>
            <th><?= t('products.product') ?></th>
            <th style="text-align: center;"><?= t('sales_orders.quantity') ?></th>
            <th style="text-align: right;"><?= t('sales_orders.unit_price') ?></th>
            <th style="text-align: right;"><?= t('sales_orders.total') ?></th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($items as $item): ?>
        <tr>
            <td>
                <strong><?= htmlspecialchars($item['name'] ?? '') ?></strong><br>
                <small style="color: #666666;"><?= htmlspecialchars($item['sku'] ?? '') ?></small>
                <?php if (!empty($item['description'])): ?>
                <br><small style="color: #666666;"><?= htmlspecialchars($item['description']) ?></small>
                <?php endif; ?>
            </td>
            <td style="text-align: center;">
                <?= number_format($item['quantity'] ?? 0, 2) ?>
                <?php if (!empty($item['unit'])): ?>
                <br><small style="color: #666666;"><?= htmlspecialchars($item['unit']) ?></small>
                <?php endif; ?>
            </td>
            <td style="text-align: right;">
                <?= number_format($item['unit_price'] ?? 0, 2) ?> <?= $currency ?? 'USD' ?>
            </td>
            <td style="text-align: right;">
                <strong><?= number_format($item['total_price'] ?? 0, 2) ?> <?= $currency ?? 'USD' ?></strong>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>
<?php endif; ?>

<!-- Order Summary -->
<div style="margin-top: 30px;">
    <table style="width: 100%; max-width: 300px; margin-left: auto;">
        <tr>
            <td style="padding: 8px; text-align: right; color: #555555;">
                <?= t('sales_orders.subtotal') ?>:
            </td>
            <td style="padding: 8px; text-align: right; font-weight: bold;">
                <?= number_format($subtotal ?? 0, 2) ?> <?= $currency ?? 'USD' ?>
            </td>
        </tr>
        <?php if (($discount_amount ?? 0) > 0): ?>
        <tr>
            <td style="padding: 8px; text-align: right; color: #555555;">
                <?= t('sales_orders.discount') ?> (<?= $discount_rate ?? 0 ?>%):
            </td>
            <td style="padding: 8px; text-align: right; color: #198754;">
                -<?= number_format($discount_amount, 2) ?> <?= $currency ?? 'USD' ?>
            </td>
        </tr>
        <?php endif; ?>
        <?php if (($tax_amount ?? 0) > 0): ?>
        <tr>
            <td style="padding: 8px; text-align: right; color: #555555;">
                <?= t('sales_orders.tax') ?> (<?= $tax_rate ?? 0 ?>%):
            </td>
            <td style="padding: 8px; text-align: right;">
                <?= number_format($tax_amount, 2) ?> <?= $currency ?? 'USD' ?>
            </td>
        </tr>
        <?php endif; ?>
        <?php if (($shipping_cost ?? 0) > 0): ?>
        <tr>
            <td style="padding: 8px; text-align: right; color: #555555;">
                <?= t('sales_orders.shipping') ?>:
            </td>
            <td style="padding: 8px; text-align: right;">
                <?= number_format($shipping_cost, 2) ?> <?= $currency ?? 'USD' ?>
            </td>
        </tr>
        <?php endif; ?>
        <tr style="border-top: 2px solid #0d6efd;">
            <td style="padding: 12px 8px; text-align: right; font-weight: bold; color: #2c3e50;">
                <?= t('sales_orders.total') ?>:
            </td>
            <td style="padding: 12px 8px; text-align: right; font-weight: bold; font-size: 18px; color: #0d6efd;">
                <?= number_format($total_amount ?? 0, 2) ?> <?= $currency ?? 'USD' ?>
            </td>
        </tr>
    </table>
</div>

<?php if (!empty($shipping_address)): ?>
<div class="info-box">
    <h4><?= t('sales_orders.shipping_address') ?></h4>
    <p><?= nl2br(htmlspecialchars($shipping_address)) ?></p>
</div>
<?php endif; ?>

<?php if (!empty($special_instructions)): ?>
<div class="info-box">
    <h4><?= t('sales_orders.special_instructions') ?></h4>
    <p><?= nl2br(htmlspecialchars($special_instructions)) ?></p>
</div>
<?php endif; ?>

<div style="margin-top: 30px;">
    <h4 style="color: #2c3e50;"><?= t('emails.what_happens_next', 'What happens next?') ?></h4>
    <ul style="color: #555555; padding-left: 20px;">
        <li><?= t('emails.order_confirmation.step1', 'Our team will review and process your order') ?></li>
        <li><?= t('emails.order_confirmation.step2', 'You\'ll receive updates on order status and shipping') ?></li>
        <li><?= t('emails.order_confirmation.step3', 'Tracking information will be provided once shipped') ?></li>
        <li><?= t('emails.order_confirmation.step4', 'Delivery within the estimated timeframe') ?></li>
    </ul>
</div>

<p>
    <?= t('emails.order_confirmation.questions', 'If you have any questions about your order, please don\'t hesitate to contact us. We\'re here to help!') ?>
</p>

<p style="font-size: 14px; color: #666666;">
    <?= t('emails.order_confirmation.footer_note', 'This confirmation was sent to the email address associated with your order. Please keep this email for your records.') ?>
</p>

<?php
$content = ob_get_clean();
include 'layout.php';
?>