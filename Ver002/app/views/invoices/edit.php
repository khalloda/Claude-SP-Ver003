<?php
/**
 * File: app/views/invoices/edit.php
 * Purpose: Invoice editing form with comprehensive invoice modification
 * Layout: Uses app layout with professional invoice editing interface
 */

$this->layout('layouts/app', [
    'title' => $page_title ?? t('invoices.edit_invoice'),
    'active_nav' => 'invoices'
]);

$currentUser = $this->getCurrentUser();
$canEdit = $this->hasRole(['admin', 'manager', 'accounting']) && in_array($invoice->status, ['draft', 'sent']);
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">
            <i class="fas fa-edit me-2"></i><?= t('invoices.edit_invoice') ?>
            <small class="text-muted ms-2"><?= htmlspecialchars($invoice->invoice_number) ?></small>
        </h1>
        <div class="btn-toolbar mb-2 mb-md-0">
            <a href="/invoices/<?= $invoice->id ?>" class="btn btn-outline-secondary me-2">
                <i class="fas fa-eye"></i> <?= t('common.view') ?>
            </a>
            <a href="/invoices" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left"></i> <?= t('common.back') ?>
            </a>
        </div>
    </div>

    <?php if (!$canEdit): ?>
    <div class="alert alert-warning">
        <i class="fas fa-exclamation-triangle me-2"></i>
        <?= t('invoices.cannot_edit_status', ['status' => t('invoices.status.' . $invoice->status)]) ?>
    </div>
    <?php endif; ?>

    <form id="invoiceForm" <?= !$canEdit ? 'data-readonly="true"' : '' ?>>
        <div class="row">
            <div class="col-md-8">
                <!-- Invoice Header -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0"><?= t('invoices.invoice_information') ?></h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label"><?= t('invoices.invoice_number') ?> *</label>
                                    <input type="text" class="form-control" name="invoice_number" 
                                           value="<?= htmlspecialchars($invoice->invoice_number) ?>" 
                                           <?= !$canEdit ? 'readonly' : 'required' ?>>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label"><?= t('invoices.status') ?></label>
                                    <select class="form-select" name="status" <?= !$canEdit ? 'disabled' : '' ?>>
                                        <option value="draft" <?= $invoice->status === 'draft' ? 'selected' : '' ?>><?= t('invoices.status.draft') ?></option>
                                        <option value="sent" <?= $invoice->status === 'sent' ? 'selected' : '' ?>><?= t('invoices.status.sent') ?></option>
                                        <option value="viewed" <?= $invoice->status === 'viewed' ? 'selected' : '' ?>><?= t('invoices.status.viewed') ?></option>
                                        <option value="partial" <?= $invoice->status === 'partial' ? 'selected' : '' ?>><?= t('invoices.status.partial') ?></option>
                                        <option value="paid" <?= $invoice->status === 'paid' ? 'selected' : '' ?>><?= t('invoices.status.paid') ?></option>
                                        <option value="overdue" <?= $invoice->status === 'overdue' ? 'selected' : '' ?>><?= t('invoices.status.overdue') ?></option>
                                        <option value="cancelled" <?= $invoice->status === 'cancelled' ? 'selected' : '' ?>><?= t('invoices.status.cancelled') ?></option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label"><?= t('invoices.invoice_date') ?> *</label>
                                    <input type="date" class="form-control" name="invoice_date" 
                                           value="<?= date('Y-m-d', strtotime($invoice->invoice_date)) ?>" 
                                           <?= !$canEdit ? 'readonly' : 'required' ?>>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label"><?= t('invoices.due_date') ?> *</label>
                                    <input type="date" class="form-control" name="due_date" 
                                           value="<?= date('Y-m-d', strtotime($invoice->due_date)) ?>" 
                                           <?= !$canEdit ? 'readonly' : 'required' ?>>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label"><?= t('invoices.client') ?> *</label>
                                    <select class="form-select" name="client_id" <?= !$canEdit ? 'disabled' : 'required' ?> id="clientSelect">
                                        <?php if (!empty($clients)): ?>
                                        <?php foreach ($clients as $client): ?>
                                        <option value="<?= $client->id ?>" 
                                                <?= $invoice->client_id == $client->id ? 'selected' : '' ?>
                                                data-email="<?= htmlspecialchars($client->email) ?>"
                                                data-address="<?= htmlspecialchars($client->billing_address ?? '') ?>"
                                                data-currency="<?= htmlspecialchars($client->currency ?? 'USD') ?>"
                                                data-payment-terms="<?= htmlspecialchars($client->payment_terms ?? 'Net 30') ?>">
                                            <?= htmlspecialchars($client->name) ?>
                                        </option>
                                        <?php endforeach; ?>
                                        <?php endif; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label"><?= t('invoices.sales_order') ?></label>
                                    <select class="form-select" name="sales_order_id" <?= !$canEdit ? 'disabled' : '' ?>>
                                        <option value=""><?= t('invoices.select_order_optional') ?></option>
                                        <?php if (!empty($sales_orders)): ?>
                                        <?php foreach ($sales_orders as $order): ?>
                                        <option value="<?= $order->id ?>" 
                                                <?= $invoice->sales_order_id == $order->id ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($order->order_number) ?> - <?= htmlspecialchars($order->client_name) ?>
                                        </option>
                                        <?php endforeach; ?>
                                        <?php endif; ?>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label"><?= t('invoices.po_number') ?></label>
                                    <input type="text" class="form-control" name="po_number" 
                                           value="<?= htmlspecialchars($invoice->po_number ?? '') ?>" 
                                           <?= !$canEdit ? 'readonly' : '' ?>>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label"><?= t('invoices.payment_terms') ?></label>
                                    <select class="form-select" name="payment_terms" <?= !$canEdit ? 'disabled' : '' ?>>
                                        <option value="Net 15" <?= ($invoice->payment_terms ?? 'Net 30') === 'Net 15' ? 'selected' : '' ?>>Net 15</option>
                                        <option value="Net 30" <?= ($invoice->payment_terms ?? 'Net 30') === 'Net 30' ? 'selected' : '' ?>>Net 30</option>
                                        <option value="Net 45" <?= ($invoice->payment_terms ?? '') === 'Net 45' ? 'selected' : '' ?>>Net 45</option>
                                        <option value="Net 60" <?= ($invoice->payment_terms ?? '') === 'Net 60' ? 'selected' : '' ?>>Net 60</option>
                                        <option value="Due on Receipt" <?= ($invoice->payment_terms ?? '') === 'Due on Receipt' ? 'selected' : '' ?>>Due on Receipt</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label"><?= t('invoices.currency') ?></label>
                                    <select class="form-select" name="currency" <?= !$canEdit ? 'disabled' : '' ?>>
                                        <option value="USD" <?= ($invoice->currency ?? 'USD') === 'USD' ? 'selected' : '' ?>>USD - US Dollar</option>
                                        <option value="EUR" <?= ($invoice->currency ?? '') === 'EUR' ? 'selected' : '' ?>>EUR - Euro</option>
                                        <option value="AED" <?= ($invoice->currency ?? '') === 'AED' ? 'selected' : '' ?>>AED - UAE Dirham</option>
                                        <option value="SAR" <?= ($invoice->currency ?? '') === 'SAR' ? 'selected' : '' ?>>SAR - Saudi Riyal</option>
                                        <option value="GBP" <?= ($invoice->currency ?? '') === 'GBP' ? 'selected' : '' ?>>GBP - British Pound</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label"><?= t('invoices.reference') ?></label>
                                    <input type="text" class="form-control" name="reference" 
                                           value="<?= htmlspecialchars($invoice->reference ?? '') ?>" 
                                           <?= !$canEdit ? 'readonly' : '' ?>>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Invoice Items -->
                <div class="card mb-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><?= t('invoices.invoice_items') ?></h5>
                        <?php if ($canEdit): ?>
                        <button type="button" class="btn btn-primary btn-sm" onclick="addInvoiceItem()">
                            <i class="fas fa-plus"></i> <?= t('invoices.add_item') ?>
                        </button>
                        <?php endif; ?>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table" id="invoiceItemsTable">
                                <thead>
                                    <tr>
                                        <th style="width: 35%;"><?= t('invoices.product_service') ?></th>
                                        <th style="width: 15%;" class="text-center"><?= t('invoices.quantity') ?></th>
                                        <th style="width: 15%;" class="text-end"><?= t('invoices.unit_price') ?></th>
                                        <th style="width: 15%;" class="text-end"><?= t('invoices.total') ?></th>
                                        <?php if ($canEdit): ?>
                                        <th style="width: 10%;" class="text-center"><?= t('common.actions') ?></th>
                                        <?php endif; ?>
                                    </tr>
                                </thead>
                                <tbody id="invoiceItemsBody">
                                    <?php if (!empty($invoice->items)): ?>
                                    <?php foreach ($invoice->items as $index => $item): ?>
                                    <tr id="item-<?= $index + 1 ?>">
                                        <td>
                                            <input type="text" class="form-control" name="items[<?= $index + 1 ?>][description]" 
                                                   value="<?= htmlspecialchars($item->description ?? $item->product_name) ?>" 
                                                   <?= !$canEdit ? 'readonly' : 'required' ?>>
                                            <input type="hidden" name="items[<?= $index + 1 ?>][id]" value="<?= $item->id ?? '' ?>">
                                            <input type="hidden" name="items[<?= $index + 1 ?>][product_id]" value="<?= $item->product_id ?? '' ?>">
                                        </td>
                                        <td>
                                            <input type="number" class="form-control text-center" name="items[<?= $index + 1 ?>][quantity]" 
                                                   min="1" step="1" value="<?= $item->quantity ?>" 
                                                   onchange="calculateItemTotal(<?= $index + 1 ?>)" 
                                                   <?= !$canEdit ? 'readonly' : 'required' ?>>
                                        </td>
                                        <td>
                                            <input type="number" class="form-control text-end" name="items[<?= $index + 1 ?>][unit_price]" 
                                                   min="0" step="0.01" value="<?= $item->unit_price ?>" 
                                                   onchange="calculateItemTotal(<?= $index + 1 ?>)" 
                                                   <?= !$canEdit ? 'readonly' : 'required' ?>>
                                        </td>
                                        <td class="text-end">
                                            <span class="item-total"><?= number_format($item->total_price ?? ($item->quantity * $item->unit_price), 2) ?></span>
                                        </td>
                                        <?php if ($canEdit): ?>
                                        <td class="text-center">
                                            <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeInvoiceItem(<?= $index + 1 ?>)">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </td>
                                        <?php endif; ?>
                                    </tr>
                                    <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                        
                        <div class="row mt-4">
                            <div class="col-md-6">
                                <?php if ($canEdit): ?>
                                <button type="button" class="btn btn-outline-secondary btn-sm" onclick="recalculateFromOrder()">
                                    <i class="fas fa-sync"></i> <?= t('invoices.recalculate') ?>
                                </button>
                                <?php endif; ?>
                            </div>
                            <div class="col-md-6">
                                <table class="table table-sm">
                                    <tr>
                                        <td class="text-end"><strong><?= t('invoices.subtotal') ?>:</strong></td>
                                        <td class="text-end" id="subtotalAmount"><?= number_format($invoice->subtotal ?? 0, 2) ?></td>
                                    </tr>
                                    <tr>
                                        <td class="text-end">
                                            <div class="input-group input-group-sm">
                                                <span class="input-group-text"><?= t('invoices.discount') ?></span>
                                                <input type="number" class="form-control" name="discount_rate" 
                                                       min="0" max="100" step="0.01" value="<?= $invoice->discount_rate ?? 0 ?>" 
                                                       onchange="calculateTotals()" style="max-width: 80px;"
                                                       <?= !$canEdit ? 'readonly' : '' ?>>
                                                <span class="input-group-text">%</span>
                                            </div>
                                        </td>
                                        <td class="text-end" id="discountAmount"><?= number_format($invoice->discount_amount ?? 0, 2) ?></td>
                                    </tr>
                                    <tr>
                                        <td class="text-end">
                                            <div class="input-group input-group-sm">
                                                <span class="input-group-text"><?= t('invoices.tax') ?></span>
                                                <input type="number" class="form-control" name="tax_rate" 
                                                       min="0" max="50" step="0.01" value="<?= $invoice->tax_rate ?? 0 ?>" 
                                                       onchange="calculateTotals()" style="max-width: 80px;"
                                                       <?= !$canEdit ? 'readonly' : '' ?>>
                                                <span class="input-group-text">%</span>
                                            </div>
                                        </td>
                                        <td class="text-end" id="taxAmount"><?= number_format($invoice->tax_amount ?? 0, 2) ?></td>
                                    </tr>
                                    <tr class="table-primary">
                                        <td class="text-end"><strong><?= t('invoices.total') ?>:</strong></td>
                                        <td class="text-end"><strong id="totalAmount"><?= number_format($invoice->total_amount ?? 0, 2) ?></strong></td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Notes and Terms -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0"><?= t('invoices.notes_terms') ?></h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label"><?= t('invoices.internal_notes') ?></label>
                            <textarea class="form-control" name="notes" rows="3" <?= !$canEdit ? 'readonly' : '' ?>><?= htmlspecialchars($invoice->notes ?? '') ?></textarea>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label"><?= t('invoices.terms_conditions') ?></label>
                            <textarea class="form-control" name="terms_conditions" rows="4" <?= !$canEdit ? 'readonly' : '' ?>><?= htmlspecialchars($invoice->terms_conditions ?? '') ?></textarea>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <!-- Payment Summary -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0"><?= t('invoices.payment_summary') ?></h5>
                    </div>
                    <div class="card-body">
                        <?php
                        $balanceDue = ($invoice->total_amount ?? 0) - ($invoice->paid_amount ?? 0);
                        $paymentProgress = ($invoice->total_amount ?? 0) > 0 ? (($invoice->paid_amount ?? 0) / ($invoice->total_amount ?? 0)) * 100 : 0;
                        ?>
                        
                        <div class="row text-center mb-3">
                            <div class="col-4">
                                <div class="border rounded p-2">
                                    <div class="h6 mb-0 text-primary"><?= number_format($invoice->total_amount ?? 0, 0) ?></div>
                                    <small class="text-muted"><?= t('invoices.total') ?></small>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="border rounded p-2">
                                    <div class="h6 mb-0 text-success"><?= number_format($invoice->paid_amount ?? 0, 0) ?></div>
                                    <small class="text-muted"><?= t('invoices.paid') ?></small>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="border rounded p-2">
                                    <div class="h6 mb-0 text-<?= $balanceDue > 0 ? 'warning' : 'success' ?>"><?= number_format($balanceDue, 0) ?></div>
                                    <small class="text-muted"><?= t('invoices.balance') ?></small>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <div class="d-flex justify-content-between mb-1">
                                <small><?= t('invoices.payment_progress') ?></small>
                                <small><?= number_format($paymentProgress, 1) ?>%</small>
                            </div>
                            <div class="progress">
                                <div class="progress-bar bg-success" role="progressbar" style="width: <?= $paymentProgress ?>%"></div>
                            </div>
                        </div>
                        
                        <?php if ($invoice->due_date): ?>
                        <div class="text-center">
                            <?php if (strtotime($invoice->due_date) > time()): ?>
                            <span class="badge bg-info">
                                <?= ceil((strtotime($invoice->due_date) - time()) / 86400) ?> <?= t('invoices.days_until_due') ?>
                            </span>
                            <?php elseif ($invoice->status !== 'paid'): ?>
                            <span class="badge bg-danger">
                                <?= abs(ceil((strtotime($invoice->due_date) - time()) / 86400)) ?> <?= t('invoices.days_overdue') ?>
                            </span>
                            <?php endif; ?>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Client Information -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0"><?= t('invoices.client_information') ?></h5>
                    </div>
                    <div class="card-body" id="clientInfoBody">
                        <div class="mb-2">
                            <strong><?= t('clients.name') ?>:</strong><br>
                            <?= htmlspecialchars($invoice->client_name) ?>
                        </div>
                        <div class="mb-2">
                            <strong><?= t('clients.email') ?>:</strong><br>
                            <?= htmlspecialchars($invoice->client_email) ?>
                        </div>
                        <?php if ($invoice->client_phone): ?>
                        <div class="mb-2">
                            <strong><?= t('clients.phone') ?>:</strong><br>
                            <?= htmlspecialchars($invoice->client_phone) ?>
                        </div>
                        <?php endif; ?>
                        <?php if ($invoice->billing_address): ?>
                        <div class="mb-2">
                            <strong><?= t('invoices.billing_address') ?>:</strong><br>
                            <?= nl2br(htmlspecialchars($invoice->billing_address)) ?>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="card">
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <?php if ($canEdit): ?>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> <?= t('common.save_changes') ?>
                            </button>
                            <button type="button" class="btn btn-outline-secondary" onclick="resetForm()">
                                <i class="fas fa-undo"></i> <?= t('common.reset_form') ?>
                            </button>
                            <?php endif; ?>
                            <a href="/invoices/<?= $invoice->id ?>" class="btn btn-outline-info">
                                <i class="fas fa-eye"></i> <?= t('common.view_invoice') ?>
                            </a>
                            <a href="/invoices/<?= $invoice->id ?>/pdf" class="btn btn-outline-primary" target="_blank">
                                <i class="fas fa-file-pdf"></i> <?= t('common.download_pdf') ?>
                            </a>
                            <?php if ($canEdit && $invoice->status === 'draft'): ?>
                            <button type="button" class="btn btn-success" onclick="markAsSent()">
                                <i class="fas fa-paper-plane"></i> <?= t('invoices.mark_as_sent') ?>
                            </button>
                            <?php endif; ?>
                            <a href="/invoices" class="btn btn-outline-secondary">
                                <i class="fas fa-list"></i> <?= t('invoices.back_to_list') ?>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
let itemCounter = <?= count($invoice->items ?? []) ?>;

document.addEventListener('DOMContentLoaded', function() {
    calculateTotals();
    
    // Set readonly state if needed
    if (document.getElementById('invoiceForm').dataset.readonly === 'true') {
        document.querySelectorAll('input, select, textarea, button[type="submit"]').forEach(el => {
            if (el.type !== 'button' || el.type === 'submit') {
                el.disabled = true;
            }
        });
    }
});

function addInvoiceItem() {
    itemCounter++;
    const tbody = document.getElementById('invoiceItemsBody');
    const row = document.createElement('tr');
    row.id = `item-${itemCounter}`;
    
    row.innerHTML = `
        <td>
            <input type="text" class="form-control" name="items[${itemCounter}][description]" 
                   placeholder="<?= t('invoices.item_description') ?>" required>
            <input type="hidden" name="items[${itemCounter}][product_id]" value="">
        </td>
        <td>
            <input type="number" class="form-control text-center" name="items[${itemCounter}][quantity]" 
                   min="1" step="1" value="1" onchange="calculateItemTotal(${itemCounter})" required>
        </td>
        <td>
            <input type="number" class="form-control text-end" name="items[${itemCounter}][unit_price]" 
                   min="0" step="0.01" value="0.00" onchange="calculateItemTotal(${itemCounter})" required>
        </td>
        <td class="text-end">
            <span class="item-total">0.00</span>
        </td>
        <td class="text-center">
            <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeInvoiceItem(${itemCounter})">
                <i class="fas fa-times"></i>
            </button>
        </td>
    `;
    
    tbody.appendChild(row);
    calculateTotals();
}

function removeInvoiceItem(itemId) {
    const row = document.getElementById(`item-${itemId}`);
    if (row) {
        row.remove();
        calculateTotals();
    }
}

function calculateItemTotal(itemId) {
    const quantityInput = document.querySelector(`input[name="items[${itemId}][quantity]"]`);
    const priceInput = document.querySelector(`input[name="items[${itemId}][unit_price]"]`);
    const totalSpan = document.querySelector(`#item-${itemId} .item-total`);
    
    const quantity = parseFloat(quantityInput.value) || 0;
    const price = parseFloat(priceInput.value) || 0;
    const total = quantity * price;
    
    totalSpan.textContent = total.toFixed(2);
    calculateTotals();
}

function calculateTotals() {
    let subtotal = 0;
    
    document.querySelectorAll('.item-total').forEach(span => {
        subtotal += parseFloat(span.textContent) || 0;
    });
    
    const discountRate = parseFloat(document.querySelector('input[name="discount_rate"]').value) || 0;
    const taxRate = parseFloat(document.querySelector('input[name="tax_rate"]').value) || 0;
    
    const discountAmount = subtotal * (discountRate / 100);
    const taxableAmount = subtotal - discountAmount;
    const taxAmount = taxableAmount * (taxRate / 100);
    const total = taxableAmount + taxAmount;
    
    document.getElementById('subtotalAmount').textContent = subtotal.toFixed(2);
    document.getElementById('discountAmount').textContent = discountAmount.toFixed(2);
    document.getElementById('taxAmount').textContent = taxAmount.toFixed(2);
    document.getElementById('totalAmount').textContent = total.toFixed(2);
}

document.getElementById('invoiceForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    if (this.dataset.readonly === 'true') {
        return;
    }
    
    const formData = new FormData(this);
    
    fetch('/invoices/<?= $invoice->id ?>', {
        method: 'POST',
        headers: {
            'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert('success', data.message);
            if (data.redirect) {
                setTimeout(() => {
                    window.location.href = data.redirect;
                }, 1500);
            }
        } else {
            showAlert('error', data.message);
            if (data.errors) {
                Object.keys(data.errors).forEach(field => {
                    const input = document.querySelector(`[name="${field}"]`);
                    if (input) {
                        input.classList.add('is-invalid');
                    }
                });
            }
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('error', '<?= t('messages.error.general') ?>');
    });
});

function resetForm() {
    if (confirm('<?= t('invoices.confirm_reset_form') ?>')) {
        location.reload();
    }
}

function markAsSent() {
    if (confirm('<?= t('invoices.confirm_mark_as_sent') ?>')) {
        document.querySelector('select[name="status"]').value = 'sent';
        document.getElementById('invoiceForm').dispatchEvent(new Event('submit'));
    }
}
</script>