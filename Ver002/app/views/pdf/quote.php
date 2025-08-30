<?php
// Quote PDF template
$document_type = 'Quote';
$document_number = $quote->quote_number ?? '';
$document_date = isset($quote->quote_date) ? date('M d, Y', strtotime($quote->quote_date)) : date('M d, Y');
$due_date = isset($quote->valid_until) ? date('M d, Y', strtotime($quote->valid_until)) : '';
$status = ucfirst($quote->status ?? 'draft');
$reference = $quote->reference ?? '';

// Company information
$company_name = 'MISP System';
$company_tagline = 'Management Information System for Spare Parts';
$company_address = "123 Business Street\nCity, State 12345\nCountry";
$company_phone = '+1 (555) 123-4567';
$company_email = 'info@mispsystem.com';
$company_website = 'www.mispsystem.com';

// Footer information
$footer_disclaimer = "This quote is valid for the period specified and subject to our terms and conditions.\nPrices are subject to change without notice.\nAll prices exclude taxes unless otherwise stated.";
$contact_info = "Questions? Contact us at sales@mispsystem.com";
$terms_url = "/terms-and-conditions";

ob_start();
?>

<!-- Client Information Section -->
<div class="content-section">
    <div style="display: flex; justify-content: space-between; margin-bottom: 30px;">
        <!-- Quote For -->
        <div style="flex: 1; margin-right: 40px;">
            <h3 class="section-title">Quote For:</h3>
            <div style="font-size: 14px; line-height: 1.6;">
                <strong style="font-size: 16px;"><?= htmlspecialchars($quote->client_name ?? 'Client Name') ?></strong><br>
                <?php if (!empty($quote->client_company)): ?>
                    <?= htmlspecialchars($quote->client_company) ?><br>
                <?php endif; ?>
                <?php if (!empty($quote->client_address)): ?>
                    <?= nl2br(htmlspecialchars($quote->client_address)) ?><br>
                <?php endif; ?>
                <?php if (!empty($quote->client_email)): ?>
                    Email: <?= htmlspecialchars($quote->client_email) ?><br>
                <?php endif; ?>
                <?php if (!empty($quote->client_phone)): ?>
                    Phone: <?= htmlspecialchars($quote->client_phone) ?>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Quote Validity -->
        <div style="flex: 1;">
            <h3 class="section-title">Quote Validity:</h3>
            <div style="font-size: 14px; line-height: 1.6;">
                <strong>Valid Until:</strong> <?= $due_date ?: 'Open' ?><br>
                <strong>Prepared By:</strong> <?= htmlspecialchars($quote->sales_rep ?? 'Sales Team') ?><br>
                <?php if (!empty($quote->prepared_by)): ?>
                    <strong>Contact:</strong> <?= htmlspecialchars($quote->prepared_by) ?><br>
                <?php endif; ?>
                <?php if (isset($quote->revision)): ?>
                    <strong>Revision:</strong> <?= $quote->revision ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Quote Details -->
<?php if (!empty($quote->project_name) || !empty($quote->delivery_terms) || !empty($quote->payment_terms)): ?>
<div class="content-section">
    <div class="info-box">
        <h4>Quote Details</h4>
        <?php if (!empty($quote->project_name)): ?>
            <p><strong>Project:</strong> <?= htmlspecialchars($quote->project_name) ?></p>
        <?php endif; ?>
        <?php if (!empty($quote->delivery_terms)): ?>
            <p><strong>Delivery Terms:</strong> <?= htmlspecialchars($quote->delivery_terms) ?></p>
        <?php endif; ?>
        <?php if (!empty($quote->payment_terms)): ?>
            <p><strong>Payment Terms:</strong> <?= htmlspecialchars($quote->payment_terms) ?></p>
        <?php endif; ?>
        <?php if (!empty($quote->warranty_terms)): ?>
            <p><strong>Warranty:</strong> <?= htmlspecialchars($quote->warranty_terms) ?></p>
        <?php endif; ?>
    </div>
</div>
<?php endif; ?>

<!-- Items Table -->
<div class="content-section">
    <h3 class="section-title">Quoted Items</h3>
    
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 5%; text-align: center;">#</th>
                <th style="width: 35%;">Description</th>
                <th style="width: 12%; text-align: center;">Quantity</th>
                <th style="width: 12%; text-align: right;">Unit Price</th>
                <th style="width: 12%; text-align: right;">Discount</th>
                <th style="width: 12%; text-align: right;">Lead Time</th>
                <th style="width: 12%; text-align: right;">Amount</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($quote->items)): ?>
                <?php 
                $subtotal = 0;
                $item_count = 0;
                foreach ($quote->items as $item): 
                    $item_count++;
                    $item_total = ($item->quantity ?? 0) * ($item->unit_price ?? 0) - ($item->discount_amount ?? 0);
                    $subtotal += $item_total;
                ?>
                <tr>
                    <td class="text-center"><?= $item_count ?></td>
                    <td style="vertical-align: top;">
                        <strong><?= htmlspecialchars($item->product_name ?? $item->description ?? '') ?></strong>
                        <?php if (!empty($item->product_sku)): ?>
                            <br><small style="color: #666;">SKU: <?= htmlspecialchars($item->product_sku) ?></small>
                        <?php endif; ?>
                        <?php if (!empty($item->specifications)): ?>
                            <br><small style="color: #666;">Specs: <?= htmlspecialchars($item->specifications) ?></small>
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
                        <br><small style="color: #666;"><?= $quote->currency ?? 'USD' ?></small>
                    </td>
                    <td class="text-right number">
                        <?php if (($item->discount_amount ?? 0) > 0): ?>
                            <?= number_format($item->discount_amount, 2) ?>
                            <br><small style="color: #666;"><?= $quote->currency ?? 'USD' ?></small>
                        <?php else: ?>
                            -
                        <?php endif; ?>
                    </td>
                    <td class="text-center">
                        <?php if (!empty($item->lead_time)): ?>
                            <?= htmlspecialchars($item->lead_time) ?>
                        <?php else: ?>
                            <small style="color: #666;">TBD</small>
                        <?php endif; ?>
                    </td>
                    <td class="text-right number">
                        <strong><?= number_format($item_total, 2) ?></strong>
                        <br><small style="color: #666;"><?= $quote->currency ?? 'USD' ?></small>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="7" class="text-center" style="padding: 40px; color: #666;">
                        No items found for this quote.
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
                    <?= number_format($quote->subtotal ?? $subtotal ?? 0, 2) ?> <?= $quote->currency ?? 'USD' ?>
                </td>
            </tr>
            
            <?php if (($quote->discount_amount ?? 0) > 0): ?>
            <tr>
                <td style="text-align: right; padding-right: 20px;">
                    Discount <?php if (($quote->discount_rate ?? 0) > 0): ?>(<?= $quote->discount_rate ?>%)<?php endif; ?>:
                </td>
                <td style="text-align: right; color: #198754;">
                    -<?= number_format($quote->discount_amount, 2) ?> <?= $quote->currency ?? 'USD' ?>
                </td>
            </tr>
            <?php endif; ?>
            
            <?php if (($quote->tax_amount ?? 0) > 0): ?>
            <tr>
                <td style="text-align: right; padding-right: 20px;">
                    Tax <?php if (($quote->tax_rate ?? 0) > 0): ?>(<?= $quote->tax_rate ?>%)<?php endif; ?>:
                </td>
                <td style="text-align: right;">
                    <?= number_format($quote->tax_amount, 2) ?> <?= $quote->currency ?? 'USD' ?>
                </td>
            </tr>
            <?php endif; ?>
            
            <?php if (($quote->shipping_cost ?? 0) > 0): ?>
            <tr>
                <td style="text-align: right; padding-right: 20px;">Shipping:</td>
                <td style="text-align: right;">
                    <?= number_format($quote->shipping_cost, 2) ?> <?= $quote->currency ?? 'USD' ?>
                </td>
            </tr>
            <?php endif; ?>
            
            <tr class="total-row">
                <td style="text-align: right; padding-right: 20px; padding-top: 15px;">
                    <strong>Total Quote:</strong>
                </td>
                <td style="text-align: right; padding-top: 15px;">
                    <span class="amount-large"><?= number_format($quote->total_amount ?? 0, 2) ?> <?= $quote->currency ?? 'USD' ?></span>
                </td>
            </tr>
        </table>
    </div>
</div>

<!-- Validity and Terms -->
<div class="content-section">
    <div style="display: flex; justify-content: space-between; gap: 20px;">
        <!-- Quote Validity -->
        <div style="flex: 1;">
            <div class="info-box <?= (strtotime($quote->valid_until ?? '') < time()) ? 'warning' : '' ?>">
                <h4>Quote Validity</h4>
                <p><strong>Valid Until:</strong> <?= $due_date ?: 'Open Quote' ?></p>
                <?php if (!empty($quote->valid_until) && strtotime($quote->valid_until) < time()): ?>
                    <p style="color: #dc3545;"><strong>Status:</strong> This quote has expired</p>
                <?php elseif (!empty($quote->valid_until)): ?>
                    <?php $days_left = ceil((strtotime($quote->valid_until) - time()) / 86400); ?>
                    <p><strong>Days Remaining:</strong> <?= max(0, $days_left) ?> days</p>
                <?php endif; ?>
                <p><strong>Quote Prepared:</strong> <?= $document_date ?></p>
            </div>
        </div>
        
        <!-- Acceptance -->
        <div style="flex: 1;">
            <div class="info-box">
                <h4>Quote Acceptance</h4>
                <p>To accept this quote, please:</p>
                <ul style="margin: 5px 0; padding-left: 20px; font-size: 11px;">
                    <li>Sign and return a copy</li>
                    <li>Provide purchase order (if required)</li>
                    <li>Confirm delivery requirements</li>
                </ul>
                <p style="font-size: 10px; color: #666;">
                    Acceptance of this quote constitutes agreement to our terms and conditions.
                </p>
            </div>
        </div>
    </div>
</div>

<!-- Delivery Information -->
<?php if (!empty($quote->delivery_address) || !empty($quote->special_instructions)): ?>
<div class="content-section">
    <h3 class="section-title">Delivery Information</h3>
    
    <?php if (!empty($quote->delivery_address)): ?>
    <div style="margin-bottom: 15px;">
        <h4>Delivery Address:</h4>
        <p style="font-size: 12px; color: #555555;">
            <?= nl2br(htmlspecialchars($quote->delivery_address)) ?>
        </p>
    </div>
    <?php endif; ?>
    
    <?php if (!empty($quote->special_instructions)): ?>
    <div>
        <h4>Special Instructions:</h4>
        <p style="font-size: 12px; color: #555555;">
            <?= nl2br(htmlspecialchars($quote->special_instructions)) ?>
        </p>
    </div>
    <?php endif; ?>
</div>
<?php endif; ?>

<!-- Notes and Terms -->
<?php if (!empty($quote->notes) || !empty($quote->terms_conditions)): ?>
<div class="content-section">
    <?php if (!empty($quote->notes)): ?>
        <h4>Notes:</h4>
        <p style="font-size: 11px; color: #555555; line-height: 1.5; margin-bottom: 15px;">
            <?= nl2br(htmlspecialchars($quote->notes)) ?>
        </p>
    <?php endif; ?>
    
    <?php if (!empty($quote->terms_conditions)): ?>
        <h4>Terms & Conditions:</h4>
        <p style="font-size: 10px; color: #666666; line-height: 1.4;">
            <?= nl2br(htmlspecialchars($quote->terms_conditions)) ?>
        </p>
    <?php endif; ?>
</div>
<?php endif; ?>

<!-- Standard Terms (if no custom terms) -->
<?php if (empty($quote->terms_conditions)): ?>
<div class="content-section">
    <h4>Standard Terms & Conditions:</h4>
    <div style="font-size: 10px; color: #666666; line-height: 1.4; columns: 2; gap: 20px;">
        <p><strong>1. Quote Validity:</strong> This quote is valid for 30 days from the date of issue unless otherwise specified.</p>
        <p><strong>2. Pricing:</strong> All prices are in <?= $quote->currency ?? 'USD' ?> and exclude taxes unless stated otherwise.</p>
        <p><strong>3. Payment Terms:</strong> Payment terms to be agreed upon acceptance of quote.</p>
        <p><strong>4. Delivery:</strong> Delivery times are estimates and subject to product availability.</p>
        <p><strong>5. Changes:</strong> Any changes to the quoted items may affect pricing and delivery schedules.</p>
        <p><strong>6. Acceptance:</strong> Acceptance of this quote constitutes agreement to these terms.</p>
    </div>
</div>
<?php endif; ?>

<!-- Signature Area -->
<div class="signature-area">
    <div style="display: flex; justify-content: space-between;">
        <div style="width: 45%;">
            <h4>Client Acceptance:</h4>
            <div class="signature-box"></div>
            <div class="signature-label">
                Signature: _________________________<br>
                Print Name: _______________________<br>
                Title: ____________________________<br>
                Date: ____________________________
            </div>
        </div>
        
        <div style="width: 45%;">
            <h4>Company Representative:</h4>
            <div class="signature-box"></div>
            <div class="signature-label">
                Signature: _________________________<br>
                Print Name: <?= htmlspecialchars($quote->sales_rep ?? 'Sales Representative') ?><br>
                Title: Sales Representative<br>
                Date: <?= $document_date ?>
            </div>
        </div>
    </div>
    
    <div style="margin-top: 20px; text-align: center; font-size: 10px; color: #666;">
        Please sign and return one copy to confirm acceptance of this quote.
    </div>
</div>

<?php
$content = ob_get_clean();
include 'layout.php';
?>