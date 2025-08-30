<?php
// Invoice notification email template
$title = t('emails.invoice_notification.title', 'Invoice Ready');
$subtitle = t('emails.invoice_notification.subtitle', 'Your invoice is now available');
$greeting = t('emails.invoice_notification.greeting', 'Dear :name,', ['name' => $customer_name ?? 'Valued Customer']);
$show_signature = true;

ob_start();
?>

<p>
    <?= t('emails.invoice_notification.intro', 'Your invoice has been generated and is now ready for your review. Please find the details below.') ?>
</p>

<div class="info-box">
    <h4><?= t('invoices.invoice_details', 'Invoice Details') ?></h4>
    <p>
        <strong><?= t('invoices.invoice_number') ?>:</strong> <?= htmlspecialchars($invoice_number ?? '') ?><br>
        <strong><?= t('invoices.invoice_date') ?>:</strong> <?= $invoice_date ?? date('M d, Y') ?><br>
        <strong><?= t('invoices.due_date') ?>:</strong> <?= $due_date ?? 'Upon receipt' ?><br>
        <strong><?= t('invoices.total_amount') ?>:</strong> <span style="font-size: 18px; color: #0d6efd; font-weight: bold;"><?= number_format($total_amount ?? 0, 2) ?> <?= $currency ?? 'USD' ?></span>
    </p>
</div>

<div class="btn-container">
    <a href="<?= $invoice_url ?? '#' ?>" class="btn" style="margin-right: 10px;">
        <?= t('emails.invoice_notification.view_invoice', 'View Invoice') ?>
    </a>
    <a href="<?= $payment_url ?? '#' ?>" class="btn btn-success">
        <?= t('emails.invoice_notification.pay_now', 'Pay Now') ?>
    </a>
</div>

<?php 
$status = $invoice_status ?? 'draft';
$status_class = match($status) {
    'paid' => 'success',
    'overdue' => 'danger',
    'sent' => 'info',
    'draft' => 'warning',
    default => 'info'
};
?>

<div style="text-align: center; margin: 25px 0;">
    <span class="status-badge <?= $status_class ?>">
        <?= t('invoices.status.' . $status, ucfirst($status)) ?>
    </span>
</div>

<?php if ($status === 'overdue'): ?>
<div class="info-box danger">
    <h4><?= t('invoices.overdue_notice', 'Overdue Notice') ?></h4>
    <p>
        <?= t('emails.invoice_notification.overdue_message', 'This invoice is now overdue. Please arrange payment as soon as possible to avoid any service disruptions.') ?>
    </p>
</div>
<?php elseif ($status === 'paid'): ?>
<div class="info-box success">
    <h4><?= t('invoices.payment_received', 'Payment Received') ?></h4>
    <p>
        <?= t('emails.invoice_notification.paid_message', 'Thank you! Payment for this invoice has been received and processed.') ?>
    </p>
</div>
<?php endif; ?>

<h3 style="color: #2c3e50; margin-top: 30px; margin-bottom: 20px;">
    <?= t('invoices.invoice_summary', 'Invoice Summary') ?>
</h3>

<?php if (!empty($items)): ?>
<table class="data-table">
    <thead>
        <tr>
            <th><?= t('products.product') ?></th>
            <th style="text-align: center;"><?= t('invoices.quantity') ?></th>
            <th style="text-align: right;"><?= t('invoices.unit_price') ?></th>
            <th style="text-align: right;"><?= t('invoices.amount') ?></th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($items as $item): ?>
        <tr>
            <td>
                <strong><?= htmlspecialchars($item['name'] ?? '') ?></strong><br>
                <small style="color: #666666;"><?= htmlspecialchars($item['description'] ?? '') ?></small>
            </td>
            <td style="text-align: center;">
                <?= number_format($item['quantity'] ?? 0, 2) ?>
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

<!-- Invoice Totals -->
<div style="margin-top: 30px;">
    <table style="width: 100%; max-width: 300px; margin-left: auto;">
        <tr>
            <td style="padding: 8px; text-align: right; color: #555555;">
                <?= t('invoices.subtotal') ?>:
            </td>
            <td style="padding: 8px; text-align: right; font-weight: bold;">
                <?= number_format($subtotal ?? 0, 2) ?> <?= $currency ?? 'USD' ?>
            </td>
        </tr>
        <?php if (($discount_amount ?? 0) > 0): ?>
        <tr>
            <td style="padding: 8px; text-align: right; color: #555555;">
                <?= t('invoices.discount') ?> (<?= $discount_rate ?? 0 ?>%):
            </td>
            <td style="padding: 8px; text-align: right; color: #198754;">
                -<?= number_format($discount_amount, 2) ?> <?= $currency ?? 'USD' ?>
            </td>
        </tr>
        <?php endif; ?>
        <?php if (($tax_amount ?? 0) > 0): ?>
        <tr>
            <td style="padding: 8px; text-align: right; color: #555555;">
                <?= t('invoices.tax') ?> (<?= $tax_rate ?? 0 ?>%):
            </td>
            <td style="padding: 8px; text-align: right;">
                <?= number_format($tax_amount, 2) ?> <?= $currency ?? 'USD' ?>
            </td>
        </tr>
        <?php endif; ?>
        <tr style="border-top: 2px solid #0d6efd;">
            <td style="padding: 12px 8px; text-align: right; font-weight: bold; color: #2c3e50;">
                <?= t('invoices.total_amount') ?>:
            </td>
            <td style="padding: 12px 8px; text-align: right; font-weight: bold; font-size: 18px; color: #0d6efd;">
                <?= number_format($total_amount ?? 0, 2) ?> <?= $currency ?? 'USD' ?>
            </td>
        </tr>
    </table>
</div>

<?php if ($status !== 'paid'): ?>
<div class="info-box">
    <h4><?= t('invoices.payment_methods', 'Payment Methods') ?></h4>
    <p><?= t('emails.invoice_notification.payment_methods_info', 'You can pay this invoice using any of the following methods:') ?></p>
    <ul style="margin: 10px 0; padding-left: 20px;">
        <li><?= t('payments.online_payment', 'Online payment via credit card or bank transfer') ?></li>
        <li><?= t('payments.bank_transfer', 'Direct bank transfer to our account') ?></li>
        <li><?= t('payments.check_payment', 'Check payment (if applicable)') ?></li>
    </ul>
</div>

<?php if (!empty($payment_terms)): ?>
<div style="margin-top: 20px;">
    <h4 style="color: #2c3e50;"><?= t('invoices.payment_terms', 'Payment Terms') ?></h4>
    <p style="color: #555555; font-size: 14px;">
        <?= nl2br(htmlspecialchars($payment_terms)) ?>
    </p>
</div>
<?php endif; ?>
<?php endif; ?>

<?php if (!empty($notes)): ?>
<div style="margin-top: 30px;">
    <h4 style="color: #2c3e50;"><?= t('invoices.notes', 'Notes') ?></h4>
    <p style="color: #555555;"><?= nl2br(htmlspecialchars($notes)) ?></p>
</div>
<?php endif; ?>

<p>
    <?= t('emails.invoice_notification.questions', 'If you have any questions about this invoice or need assistance with payment, please don\'t hesitate to contact us.') ?>
</p>

<p style="font-size: 14px; color: #666666;">
    <?= t('emails.invoice_notification.footer_note', 'This invoice notification was sent automatically. Please keep this email and the attached invoice for your records.') ?>
</p>

<?php
$content = ob_get_clean();
include 'layout.php';
?>