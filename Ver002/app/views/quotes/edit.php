<?php
/**
 * File: app/views/quotes/edit.php
 * Purpose: Quote editing form with line item modification
 * Layout: Uses app layout with dynamic form features
 */

$this->layout('layouts/app', [
    'title' => $page_title ?? t('quotes.edit_quote'),
    'active_nav' => 'quotes'
]);

$clients = $clients ?? [];
$products = $products ?? [];
$currencies = $currencies ?? [];
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">
            <i class="fas fa-file-alt me-2"></i><?= t('quotes.edit_quote') ?>
            <small class="text-muted ms-2"><?= htmlspecialchars($quote->quote_number) ?></small>
        </h1>
        <div class="btn-toolbar mb-2 mb-md-0">
            <a href="/quotes/<?= $quote->id ?>" class="btn btn-outline-secondary me-2">
                <i class="fas fa-eye"></i> <?= t('common.view') ?>
            </a>
            <a href="/quotes" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left"></i> <?= t('common.back') ?>
            </a>
        </div>
    </div>

    <form method="POST" action="/quotes/<?= $quote->id ?>" id="quoteForm">
        <?= $this->csrf() ?>
        <input type="hidden" name="_method" value="PUT">
        
        <div class="row">
            <div class="col-md-8">
                <!-- Quote Details -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0"><?= t('quotes.quote_details') ?></h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="client_id" class="form-label"><?= t('clients.client') ?> *</label>
                                    <select class="form-select" id="client_id" name="client_id" required>
                                        <option value=""><?= t('common.select_client') ?></option>
                                        <?php foreach ($clients as $client): ?>
                                        <option value="<?= $client->id ?>" <?= $this->selected('client_id', $client->id, $quote->client_id) ?>>
                                            <?= htmlspecialchars($client->name) ?>
                                        </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <?= $this->error('client_id') ?>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="quote_date" class="form-label"><?= t('quotes.quote_date') ?> *</label>
                                    <input type="date" class="form-control" id="quote_date" name="quote_date" 
                                           value="<?= $this->old('quote_date', date('Y-m-d', strtotime($quote->quote_date))) ?>" required>
                                    <?= $this->error('quote_date') ?>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="valid_until" class="form-label"><?= t('quotes.valid_until') ?> *</label>
                                    <input type="date" class="form-control" id="valid_until" name="valid_until" 
                                           value="<?= $this->old('valid_until', date('Y-m-d', strtotime($quote->valid_until))) ?>" required>
                                    <?= $this->error('valid_until') ?>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="currency" class="form-label"><?= t('common.currency') ?> *</label>
                                    <select class="form-select" id="currency" name="currency" required>
                                        <?php foreach ($currencies as $currency): ?>
                                        <option value="<?= $currency->code ?>" <?= $this->selected('currency', $currency->code, $quote->currency) ?>>
                                            <?= htmlspecialchars($currency->name) ?> (<?= $currency->code ?>)
                                        </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <?= $this->error('currency') ?>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-12">
                                <div class="mb-3">
                                    <label for="reference" class="form-label"><?= t('quotes.reference') ?></label>
                                    <input type="text" class="form-control" id="reference" name="reference" 
                                           value="<?= $this->old('reference', $quote->reference) ?>" 
                                           placeholder="<?= t('quotes.reference_placeholder') ?>">
                                    <?= $this->error('reference') ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Quote Items -->
                <div class="card mb-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><?= t('quotes.quote_items') ?></h5>
                        <button type="button" class="btn btn-sm btn-primary" onclick="addQuoteItem()">
                            <i class="fas fa-plus"></i> <?= t('quotes.add_item') ?>
                        </button>
                    </div>
                    <div class="card-body">
                        <div id="quote-items">
                            <!-- Existing quote items will be loaded here -->
                        </div>
                        
                        <div class="row mt-3">
                            <div class="col-md-8"></div>
                            <div class="col-md-4">
                                <div class="table-responsive">
                                    <table class="table table-sm">
                                        <tr>
                                            <td><strong><?= t('quotes.subtotal') ?>:</strong></td>
                                            <td class="text-end"><span id="subtotal"><?= number_format($quote->subtotal ?? 0, 2) ?></span></td>
                                        </tr>
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <label for="tax_rate" class="form-label mb-0 me-2"><?= t('quotes.tax') ?>:</label>
                                                    <input type="number" class="form-control form-control-sm" id="tax_rate" name="tax_rate" 
                                                           value="<?= $this->old('tax_rate', $quote->tax_rate ?? '0') ?>" 
                                                           min="0" max="100" step="0.01" style="width: 80px;">
                                                    <span class="ms-1">%</span>
                                                </div>
                                            </td>
                                            <td class="text-end"><span id="tax_amount"><?= number_format($quote->tax_amount ?? 0, 2) ?></span></td>
                                        </tr>
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <label for="discount_rate" class="form-label mb-0 me-2"><?= t('quotes.discount') ?>:</label>
                                                    <input type="number" class="form-control form-control-sm" id="discount_rate" name="discount_rate" 
                                                           value="<?= $this->old('discount_rate', $quote->discount_rate ?? '0') ?>" 
                                                           min="0" max="100" step="0.01" style="width: 80px;">
                                                    <span class="ms-1">%</span>
                                                </div>
                                            </td>
                                            <td class="text-end">-<span id="discount_amount"><?= number_format($quote->discount_amount ?? 0, 2) ?></span></td>
                                        </tr>
                                        <tr class="table-primary">
                                            <td><strong><?= t('quotes.total') ?>:</strong></td>
                                            <td class="text-end"><strong><span id="total_amount"><?= number_format($quote->total_amount ?? 0, 2) ?></span></strong></td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Notes -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0"><?= t('quotes.notes') ?></h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="notes" class="form-label"><?= t('quotes.internal_notes') ?></label>
                            <textarea class="form-control" id="notes" name="notes" rows="3" 
                                      placeholder="<?= t('quotes.internal_notes_placeholder') ?>"><?= $this->old('notes', $quote->notes) ?></textarea>
                            <?= $this->error('notes') ?>
                        </div>
                        
                        <div class="mb-3">
                            <label for="terms_conditions" class="form-label"><?= t('quotes.terms_conditions') ?></label>
                            <textarea class="form-control" id="terms_conditions" name="terms_conditions" rows="4" 
                                      placeholder="<?= t('quotes.terms_conditions_placeholder') ?>"><?= $this->old('terms_conditions', $quote->terms_conditions) ?></textarea>
                            <?= $this->error('terms_conditions') ?>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <!-- Actions -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0"><?= t('common.actions') ?></h5>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <button type="submit" name="action" value="update" class="btn btn-primary">
                                <i class="fas fa-save"></i> <?= t('quotes.update_quote') ?>
                            </button>
                            
                            <?php if ($quote->status === 'draft'): ?>
                            <button type="submit" name="action" value="update_and_send" class="btn btn-success">
                                <i class="fas fa-paper-plane"></i> <?= t('quotes.update_and_send') ?>
                            </button>
                            <?php endif; ?>
                            
                            <hr>
                            
                            <a href="/quotes/<?= $quote->id ?>" class="btn btn-outline-secondary">
                                <i class="fas fa-times"></i> <?= t('common.cancel') ?>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Quote Status -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0"><?= t('quotes.quote_status') ?></h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <strong><?= t('common.status') ?>:</strong>
                            <span class="badge bg-<?= $quote->status === 'draft' ? 'secondary' : ($quote->status === 'sent' ? 'info' : 'success') ?> ms-2">
                                <?= t('quotes.status.' . $quote->status) ?>
                            </span>
                        </div>
                        
                        <div class="mb-3">
                            <strong><?= t('quotes.created_at') ?>:</strong><br>
                            <small><?= date('M d, Y H:i', strtotime($quote->created_at)) ?></small>
                        </div>
                        
                        <div class="mb-3">
                            <strong><?= t('quotes.last_updated') ?>:</strong><br>
                            <small><?= date('M d, Y H:i', strtotime($quote->updated_at)) ?></small>
                        </div>
                        
                        <?php if ($quote->status !== 'draft'): ?>
                        <div class="alert alert-info">
                            <small><?= t('quotes.edit_sent_warning') ?></small>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Client Information -->
                <div class="card" id="client-info">
                    <div class="card-header">
                        <h5 class="mb-0"><?= t('clients.client_information') ?></h5>
                    </div>
                    <div class="card-body">
                        <div id="client-details"></div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<!-- Quote Item Template -->
<template id="quote-item-template">
    <div class="quote-item border rounded p-3 mb-3" data-item-index="">
        <div class="row">
            <div class="col-md-4">
                <label class="form-label"><?= t('products.product') ?> *</label>
                <select class="form-select product-select" name="items[INDEX][product_id]" required onchange="updateProductInfo(this)">
                    <option value=""><?= t('common.select_product') ?></option>
                    <?php foreach ($products as $product): ?>
                    <option value="<?= $product->id ?>" 
                            data-price="<?= $product->price ?>" 
                            data-unit="<?= htmlspecialchars($product->unit) ?>">
                        <?= htmlspecialchars($product->name) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="col-md-2">
                <label class="form-label"><?= t('quotes.quantity') ?> *</label>
                <input type="number" class="form-control quantity-input" name="items[INDEX][quantity]" 
                       min="1" step="0.01" value="1" required onchange="calculateItemTotal(this)">
            </div>
            
            <div class="col-md-2">
                <label class="form-label"><?= t('quotes.unit_price') ?> *</label>
                <input type="number" class="form-control price-input" name="items[INDEX][unit_price]" 
                       min="0" step="0.01" required onchange="calculateItemTotal(this)">
            </div>
            
            <div class="col-md-2">
                <label class="form-label"><?= t('quotes.total') ?></label>
                <input type="number" class="form-control total-input" readonly tabindex="-1">
            </div>
            
            <div class="col-md-2 d-flex align-items-end">
                <button type="button" class="btn btn-outline-danger btn-sm" onclick="removeQuoteItem(this)" title="<?= t('common.remove') ?>">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
        </div>
        
        <div class="row mt-2">
            <div class="col-md-12">
                <label class="form-label"><?= t('quotes.description') ?></label>
                <textarea class="form-control" name="items[INDEX][description]" rows="2" 
                          placeholder="<?= t('quotes.item_description_placeholder') ?>"></textarea>
                <input type="hidden" name="items[INDEX][id]" class="item-id">
            </div>
        </div>
    </div>
</template>

<script>
let itemIndex = 0;
let clientsData = <?= json_encode($clients) ?>;
let existingItems = <?= json_encode($quote->items ?? []) ?>;

document.addEventListener('DOMContentLoaded', function() {
    // Load existing quote items
    if (existingItems && existingItems.length > 0) {
        existingItems.forEach(item => {
            addQuoteItem(item);
        });
    } else {
        addQuoteItem();
    }
    
    // Update totals when values change
    document.getElementById('tax_rate').addEventListener('change', calculateTotals);
    document.getElementById('discount_rate').addEventListener('change', calculateTotals);
    
    // Load client info
    document.getElementById('client_id').addEventListener('change', loadClientInfo);
    loadClientInfo();
});

function addQuoteItem(itemData = null) {
    const template = document.getElementById('quote-item-template');
    const clone = template.content.cloneNode(true);
    
    // Replace INDEX placeholders
    clone.innerHTML = clone.innerHTML.replace(/INDEX/g, itemIndex);
    clone.querySelector('.quote-item').setAttribute('data-item-index', itemIndex);
    
    document.getElementById('quote-items').appendChild(clone);
    
    // Populate with existing data if available
    if (itemData) {
        const item = document.querySelector(`.quote-item[data-item-index="${itemIndex}"]`);
        item.querySelector('.product-select').value = itemData.product_id;
        item.querySelector('.quantity-input').value = itemData.quantity;
        item.querySelector('.price-input').value = itemData.unit_price;
        item.querySelector('textarea').value = itemData.description || '';
        item.querySelector('.item-id').value = itemData.id || '';
        
        updateProductInfo(item.querySelector('.product-select'));
    }
    
    itemIndex++;
}

function removeQuoteItem(button) {
    const item = button.closest('.quote-item');
    item.remove();
    calculateTotals();
}

function updateProductInfo(select) {
    const option = select.selectedOptions[0];
    if (option && option.value) {
        const item = select.closest('.quote-item');
        const priceInput = item.querySelector('.price-input');
        const price = option.getAttribute('data-price');
        
        if (price) {
            priceInput.value = parseFloat(price).toFixed(2);
            calculateItemTotal(priceInput);
        }
    }
}

function calculateItemTotal(input) {
    const item = input.closest('.quote-item');
    const quantity = parseFloat(item.querySelector('.quantity-input').value) || 0;
    const unitPrice = parseFloat(item.querySelector('.price-input').value) || 0;
    const total = quantity * unitPrice;
    
    item.querySelector('.total-input').value = total.toFixed(2);
    calculateTotals();
}

function calculateTotals() {
    let subtotal = 0;
    
    document.querySelectorAll('.total-input').forEach(input => {
        subtotal += parseFloat(input.value) || 0;
    });
    
    const taxRate = parseFloat(document.getElementById('tax_rate').value) || 0;
    const discountRate = parseFloat(document.getElementById('discount_rate').value) || 0;
    
    const discountAmount = (subtotal * discountRate) / 100;
    const taxableAmount = subtotal - discountAmount;
    const taxAmount = (taxableAmount * taxRate) / 100;
    const totalAmount = taxableAmount + taxAmount;
    
    document.getElementById('subtotal').textContent = subtotal.toFixed(2);
    document.getElementById('tax_amount').textContent = taxAmount.toFixed(2);
    document.getElementById('discount_amount').textContent = discountAmount.toFixed(2);
    document.getElementById('total_amount').textContent = totalAmount.toFixed(2);
}

function loadClientInfo() {
    const clientId = document.getElementById('client_id').value;
    const clientInfo = document.getElementById('client-info');
    const clientDetails = document.getElementById('client-details');
    
    if (clientId) {
        const client = clientsData.find(c => c.id == clientId);
        if (client) {
            clientDetails.innerHTML = `
                <div class="mb-2">
                    <strong>${client.name}</strong>
                </div>
                ${client.email ? `<div class="mb-2"><i class="fas fa-envelope me-2"></i>${client.email}</div>` : ''}
                ${client.phone ? `<div class="mb-2"><i class="fas fa-phone me-2"></i>${client.phone}</div>` : ''}
                ${client.address ? `<div class="mb-2"><i class="fas fa-map-marker-alt me-2"></i>${client.address}</div>` : ''}
            `;
        }
    }
}

// Initial calculations
calculateTotals();
</script>