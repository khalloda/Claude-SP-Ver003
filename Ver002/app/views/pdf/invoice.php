<?php
// Invoice PDF template
$document_type = 'Invoice';
$document_number = $invoice->invoice_number ?? '';
$document_date = isset($invoice->invoice_date) ? date('M d, Y', strtotime($invoice->invoice_date)) : date('M d, Y');
$due_date = isset($invoice->due_date) ? date('M d, Y', strtotime($invoice->due_date)) : '';
$status = ucfirst($invoice->status ?? 'draft');
$reference = $invoice->reference ?? '';

// Company information
$company_name = 'MISP System';
$company_tagline = 'Management Information System for Spare Parts';
$company_address = "123 Business Street\nCity, State 12345\nCountry";
$company_phone = '+1 (555) 123-4567';
$company_email = 'info@mispsystem.com';
$company_website = 'www.mispsystem.com';

// Footer information
$footer_disclaimer = "This invoice is computer generated and does not require a signature.\nPayment terms: Net 30 days unless otherwise specified.\nLate payments may incur additional charges as per our terms and conditions.";
$contact_info = "Questions? Contact us at accounting@mispsystem.com";
$terms_url = "/terms-and-conditions";

ob_start();
?>

<!-- Client Information Section -->
<div class="content-section">
    <div style="display: flex; justify-content: space-between; margin-bottom: 30px;">
        <!-- Bill To -->
        <div style="flex: 1; margin-right: 40px;">
            <h3 class="section-title">Bill To:</h3>
            <div style="font-size: 14px; line-height: 1.6;">
                <strong style="font-size: 16px;"><?= htmlspecialchars($invoice->client_name ?? 'Client Name') ?></strong><br>
                <?php if (!empty($invoice->client_company)): ?>
                    <?= htmlspecialchars($invoice->client_company) ?><br>
                <?php endif; ?>
                <?php if (!empty($invoice->client_address)): ?>
                    <?= nl2br(htmlspecialchars($invoice->client_address)) ?><br>
                <?php endif; ?>
                <?php if (!empty($invoice->client_email)): ?>
                    Email: <?= htmlspecialchars($invoice->client_email) ?><br>
                <?php endif; ?>
                <?php if (!empty($invoice->client_phone)): ?>
                    Phone: <?= htmlspecialchars($invoice->client_phone) ?>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Ship To (if different) -->
        <?php if (!empty($invoice->shipping_address) && $invoice->shipping_address !== $invoice->client_address): ?>
        <div style="flex: 1;">
            <h3 class="section-title">Ship To:</h3>
            <div style="font-size: 14px; line-height: 1.6;">
                <?= nl2br(htmlspecialchars($invoice->shipping_address)) ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Invoice Details -->
<?php if (!empty($invoice->po_number) || !empty($invoice->sales_rep) || !empty($invoice->payment_terms)): ?>
<div class="content-section">
    <div class="info-box">
        <h4>Invoice Details</h4>
        <?php if (!empty($invoice->po_number)): ?>
            <p><strong>PO Number:</strong> <?= htmlspecialchars($invoice->po_number) ?></p>
        <?php endif; ?>
        <?php if (!empty($invoice->sales_rep)): ?>
            <p><strong>Sales Representative:</strong> <?= htmlspecialchars($invoice->sales_rep) ?></p>
        <?php endif; ?>
        <?php if (!empty($invoice->payment_terms)): ?>
            <p><strong>Payment Terms:</strong> <?= htmlspecialchars($invoice->payment_terms) ?></p>
        <?php endif; ?>
    </div>
</div>
<?php endif; ?>

<!-- Items Table -->
<div class="content-section">
    <h3 class="section-title">Invoice Items</h3>
    
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 40%;">Description</th>
                <th style="width: 15%; text-align: center;">Quantity</th>
                <th style="width: 15%; text-align: right;">Unit Price</th>
                <th style="width: 15%; text-align: right;">Discount</th>
                <th style="width: 15%; text-align: right;">Amount</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($invoice->items)): ?>
                <?php 
                $subtotal = 0;
                foreach ($invoice->items as $item): 
                    $item_total = ($item->quantity ?? 0) * ($item->unit_price ?? 0) - ($item->discount_amount ?? 0);
                    $subtotal += $item_total;
                ?>
                <tr>
                    <td style="vertical-align: top;">
                        <strong><?= htmlspecialchars($item->product_name ?? $item->description ?? '') ?></strong>
                        <?php if (!empty($item->product_sku)): ?>
                            <br><small style="color: #666;">SKU: <?= htmlspecialchars($item->product_sku) ?></small>
                        <?php endif; ?>
                        <?php if (!empty($item->description) && $item->description !== $item->product_name): ?>
                            <br><small style="color: #666;"><?= htmlspecialchars($item->description) ?></small>
                        <?php endif; ?>
                    </td>
                    <td class="text-center number">
                        <?= number_format($item->quantity ?? 0, 2) ?>
                        <?php if (!empty($item->unit)): ?>
                            <br><small style="color: #666;"><?= htmlspecialchars($item->unit) ?></small>
                        <?php endif; ?>
                    </td>
                    <td class="text-right number">
                        <?= number_format($item->unit_price ?? 0, 2) ?>
                        <br><small style="color: #666;"><?= $invoice->currency ?? 'USD' ?></small>
                    </td>
                    <td class="text-right number">
                        <?php if (($item->discount_amount ?? 0) > 0): ?>
                            <?= number_format($item->discount_amount, 2) ?>
                            <br><small style="color: #666;"><?= $invoice->currency ?? 'USD' ?></small>
                        <?php else: ?>
                            -
                        <?php endif; ?>
                    </td>
                    <td class="text-right number">
                        <strong><?= number_format($item_total, 2) ?></strong>
                        <br><small style="color: #666;"><?= $invoice->currency ?? 'USD' ?></small>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="5" class="text-center" style="padding: 40px; color: #666;">
                        No items found for this invoice.
                    </td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- Summary Section -->
<div class="content-section">
    <div class="summary-section">
        <table class="summary-table">
            <tr>
                <td style="text-align: right; padding-right: 20px;">Subtotal:</td>
                <td style="text-align: right; font-weight: bold;">
                    <?= number_format($invoice->subtotal ?? $subtotal ?? 0, 2) ?> <?= $invoice->currency ?? 'USD' ?>
                </td>
            </tr>
            
            <?php if (($invoice->discount_amount ?? 0) > 0): ?>
            <tr>
                <td style="text-align: right; padding-right: 20px;">
                    Discount <?php if (($invoice->discount_rate ?? 0) > 0): ?>(<?= $invoice->discount_rate ?>%)<?php endif; ?>:
                </td>
                <td style="text-align: right; color: #198754;">
                    -<?= number_format($invoice->discount_amount, 2) ?> <?= $invoice->currency ?? 'USD' ?>
                </td>
            </tr>
            <?php endif; ?>
            
            <?php if (($invoice->tax_amount ?? 0) > 0): ?>
            <tr>
                <td style="text-align: right; padding-right: 20px;">
                    Tax <?php if (($invoice->tax_rate ?? 0) > 0): ?>(<?= $invoice->tax_rate ?>%)<?php endif; ?>:
                </td>
                <td style="text-align: right;">
                    <?= number_format($invoice->tax_amount, 2) ?> <?= $invoice->currency ?? 'USD' ?>
                </td>
            </tr>
            <?php endif; ?>
            
            <?php if (($invoice->shipping_cost ?? 0) > 0): ?>
            <tr>
                <td style="text-align: right; padding-right: 20px;">Shipping:</td>
                <td style="text-align: right;">
                    <?= number_format($invoice->shipping_cost, 2) ?> <?= $invoice->currency ?? 'USD' ?>
                </td>
            </tr>
            <?php endif; ?>
            
            <?php if (($invoice->adjustment ?? 0) != 0): ?>
            <tr>
                <td style="text-align: right; padding-right: 20px;">Adjustment:</td>
                <td style="text-align: right;">
                    <?= $invoice->adjustment > 0 ? '+' : '' ?><?= number_format($invoice->adjustment, 2) ?> <?= $invoice->currency ?? 'USD' ?>
                </td>
            </tr>
            <?php endif; ?>
            
            <tr class="total-row">
                <td style="text-align: right; padding-right: 20px; padding-top: 15px;">
                    <strong>Total Amount:</strong>
                </td>
                <td style="text-align: right; padding-top: 15px;">
                    <span class="amount-large"><?= number_format($invoice->total_amount ?? 0, 2) ?> <?= $invoice->currency ?? 'USD' ?></span>
                </td>
            </tr>
        </table>
        
        <?php if (!empty($total_in_words)): ?>
        <div class="amount-words" style="text-align: right; margin-top: 10px;">
            <strong>Amount in words:</strong> <?= htmlspecialchars($total_in_words) ?>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Payment Information -->
<?php if ($status !== 'Paid'): ?>
<div class="content-section">
    <div class="info-box">
        <h4>Payment Information</h4>
        <p><strong>Payment Due:</strong> <?= $due_date ?: 'Upon Receipt' ?></p>
        <?php if (!empty($invoice->payment_instructions)): ?>
            <p><strong>Payment Instructions:</strong></p>
            <p><?= nl2br(htmlspecialchars($invoice->payment_instructions)) ?></p>
        <?php else: ?>
            <p><strong>Payment Methods:</strong> Bank Transfer, Credit Card, Check</p>
            <p><strong>Bank Details:</strong> Available upon request</p>
        <?php endif; ?>
    </div>
</div>
<?php else: ?>
<div class="content-section">
    <div class="info-box success">
        <h4>Payment Status</h4>
        <p><strong>Status:</strong> PAID</p>
        <?php if (!empty($invoice->payment_date)): ?>
            <p><strong>Payment Date:</strong> <?= date('M d, Y', strtotime($invoice->payment_date)) ?></p>
        <?php endif; ?>
        <?php if (!empty($invoice->payment_method)): ?>
            <p><strong>Payment Method:</strong> <?= htmlspecialchars($invoice->payment_method) ?></p>
        <?php endif; ?>
        <?php if (!empty($invoice->payment_reference)): ?>
            <p><strong>Payment Reference:</strong> <?= htmlspecialchars($invoice->payment_reference) ?></p>
        <?php endif; ?>
    </div>
</div>
<?php endif; ?>

<!-- Notes -->
<?php if (!empty($invoice->notes) || !empty($invoice->terms_conditions)): ?>
<div class="content-section">
    <?php if (!empty($invoice->notes)): ?>
        <h4>Notes:</h4>
        <p style="font-size: 11px; color: #555555; line-height: 1.5; margin-bottom: 15px;">
            <?= nl2br(htmlspecialchars($invoice->notes)) ?>
        </p>
    <?php endif; ?>
    
    <?php if (!empty($invoice->terms_conditions)): ?>
        <h4>Terms & Conditions:</h4>
        <p style="font-size: 10px; color: #666666; line-height: 1.4;">
            <?= nl2br(htmlspecialchars($invoice->terms_conditions)) ?>
        </p>
    <?php endif; ?>
</div>
<?php endif; ?>

<!-- QR Code / Barcode (if needed) -->
<?php if (!empty($qr_code_url)): ?>
<div class="content-section">
    <div class="barcode-container">
        <img src="<?= $qr_code_url ?>" alt="QR Code" class="barcode-image">
        <div class="barcode-text">Scan to pay or view online</div>
    </div>
</div>
<?php endif; ?>

<!-- Signature Area (if needed) -->
<?php if (($invoice->status ?? '') === 'draft' || !empty($require_signature)): ?>
<div class="signature-area">
    <div style="display: flex; justify-content: space-between;">
        <div>
            <div class="signature-box"></div>
            <div class="signature-label">Authorized Signature</div>
        </div>
        <div>
            <div class="signature-box"></div>
            <div class="signature-label">Date</div>
        </div>
        <div>
            <div class="signature-box"></div>
            <div class="signature-label">Company Stamp</div>
        </div>
    </div>
</div>
<?php endif; ?>

<?php
$content = ob_get_clean();
include 'layout.php';
?>