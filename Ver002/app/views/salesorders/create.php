<?php
/**
 * File: app/views/salesorders/create.php
 * Purpose: Sales Order creation form with quote conversion support
 * Layout: Uses app layout with dynamic line items and inventory checking
 */

$this->layout('layouts/app', [
    'title' => $page_title ?? t('sales_orders.create_order'),
    'active_nav' => 'sales_orders'
]);

$sales_order = $sales_order ?? new stdClass();
$clients = $clients ?? [];
$products = $products ?? [];
$currencies = $currencies ?? [];
$quote = $quote ?? null; // If converting from quote
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">
            <i class="fas fa-shopping-cart me-2"></i><?= t('sales_orders.create_order') ?>
            <?php if ($quote): ?>
            <small class="text-muted ms-2"><?= t('sales_orders.from_quote') ?> <?= htmlspecialchars($quote->quote_number) ?></small>
            <?php endif; ?>
        </h1>
        <div class="btn-toolbar mb-2 mb-md-0">
            <a href="/sales-orders" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left"></i> <?= t('common.back') ?>
            </a>
        </div>
    </div>

    <form method="POST" action="/sales-orders" id="salesOrderForm">
        <?= $this->csrf() ?>
        
        <?php if ($quote): ?>
        <input type="hidden" name="quote_id" value="<?= $quote->id ?>">
        <?php endif; ?>
        
        <div class="row">
            <div class="col-md-8">
                <!-- Order Details -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0"><?= t('sales_orders.order_details') ?></h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="client_id" class="form-label"><?= t('clients.client') ?> *</label>
                                    <select class="form-select" id="client_id" name="client_id" required>
                                        <option value=""><?= t('common.select_client') ?></option>
                                        <?php foreach ($clients as $client): ?>
                                        <option value="<?= $client->id ?>" 
                                                <?= $this->selected('client_id', $client->id, ($quote ? $quote->client_id : null)) ?>>
                                            <?= htmlspecialchars($client->name) ?>
                                        </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <?= $this->error('client_id') ?>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="order_date" class="form-label"><?= t('sales_orders.order_date') ?> *</label>
                                    <input type="date" class="form-control" id="order_date" name="order_date" 
                                           value="<?= $this->old('order_date', date('Y-m-d')) ?>" required>
                                    <?= $this->error('order_date') ?>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="delivery_date" class="form-label"><?= t('sales_orders.delivery_date') ?></label>
                                    <input type="date" class="form-control" id="delivery_date" name="delivery_date" 
                                           value="<?= $this->old('delivery_date', date('Y-m-d', strtotime('+7 days'))) ?>">
                                    <?= $this->error('delivery_date') ?>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="currency" class="form-label"><?= t('common.currency') ?> *</label>
                                    <select class="form-select" id="currency" name="currency" required>
                                        <?php foreach ($currencies as $currency): ?>
                                        <option value="<?= $currency->code ?>" 
                                                <?= $this->selected('currency', $currency->code, 
                                                    ($quote ? $quote->currency : ($currency->is_default ? $currency->code : null))) ?>>
                                            <?= htmlspecialchars($currency->name) ?> (<?= $currency->code ?>)
                                        </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <?= $this->error('currency') ?>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="reference" class="form-label"><?= t('sales_orders.reference') ?></label>
                                    <input type="text" class="form-control" id="reference" name="reference" 
                                           value="<?= $this->old('reference', $quote->reference ?? '') ?>" 
                                           placeholder="<?= t('sales_orders.reference_placeholder') ?>">
                                    <?= $this->error('reference') ?>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="priority" class="form-label"><?= t('sales_orders.priority') ?></label>
                                    <select class="form-select" id="priority" name="priority">
                                        <option value="normal" <?= $this->selected('priority', 'normal', true) ?>>
                                            <?= t('sales_orders.priority.normal') ?>
                                        </option>
                                        <option value="high" <?= $this->selected('priority', 'high') ?>>
                                            <?= t('sales_orders.priority.high') ?>
                                        </option>
                                        <option value="urgent" <?= $this->selected('priority', 'urgent') ?>>
                                            <?= t('sales_orders.priority.urgent') ?>
                                        </option>
                                    </select>
                                    <?= $this->error('priority') ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Order Items -->
                <div class="card mb-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><?= t('sales_orders.order_items') ?></h5>
                        <button type="button" class="btn btn-sm btn-primary" onclick="addOrderItem()">
                            <i class="fas fa-plus"></i> <?= t('sales_orders.add_item') ?>
                        </button>
                    </div>
                    <div class="card-body">
                        <div id="order-items">
                            <!-- Order items will be dynamically added here -->
                        </div>
                        
                        <div class="row mt-3">
                            <div class="col-md-8"></div>
                            <div class="col-md-4">
                                <div class="table-responsive">
                                    <table class="table table-sm">
                                        <tr>
                                            <td><strong><?= t('sales_orders.subtotal') ?>:</strong></td>
                                            <td class="text-end"><span id="subtotal">0.00</span></td>
                                        </tr>
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <label for="tax_rate" class="form-label mb-0 me-2"><?= t('sales_orders.tax') ?>:</label>
                                                    <input type="number" class="form-control form-control-sm" id="tax_rate" name="tax_rate" 
                                                           value="<?= $this->old('tax_rate', $quote->tax_rate ?? '0') ?>" 
                                                           min="0" max="100" step="0.01" style="width: 80px;">
                                                    <span class="ms-1">%</span>
                                                </div>
                                            </td>
                                            <td class="text-end"><span id="tax_amount">0.00</span></td>
                                        </tr>
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <label for="discount_rate" class="form-label mb-0 me-2"><?= t('sales_orders.discount') ?>:</label>
                                                    <input type="number" class="form-control form-control-sm" id="discount_rate" name="discount_rate" 
                                                           value="<?= $this->old('discount_rate', $quote->discount_rate ?? '0') ?>" 
                                                           min="0" max="100" step="0.01" style="width: 80px;">
                                                    <span class="ms-1">%</span>
                                                </div>
                                            </td>
                                            <td class="text-end">-<span id="discount_amount">0.00</span></td>
                                        </tr>
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <label for="shipping_cost" class="form-label mb-0 me-2"><?= t('sales_orders.shipping') ?>:</label>
                                                    <input type="number" class="form-control form-control-sm" id="shipping_cost" name="shipping_cost" 
                                                           value="<?= $this->old('shipping_cost', '0') ?>" 
                                                           min="0" step="0.01" style="width: 100px;">
                                                </div>
                                            </td>
                                            <td class="text-end"><span id="shipping_amount">0.00</span></td>
                                        </tr>
                                        <tr class="table-primary">
                                            <td><strong><?= t('sales_orders.total') ?>:</strong></td>
                                            <td class="text-end"><strong><span id="total_amount">0.00</span></strong></td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Shipping & Notes -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0"><?= t('sales_orders.shipping_notes') ?></h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="shipping_address" class="form-label"><?= t('sales_orders.shipping_address') ?></label>
                                    <textarea class="form-control" id="shipping_address" name="shipping_address" rows="3" 
                                              placeholder="<?= t('sales_orders.shipping_address_placeholder') ?>"><?= $this->old('shipping_address') ?></textarea>
                                    <?= $this->error('shipping_address') ?>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="shipping_method" class="form-label"><?= t('sales_orders.shipping_method') ?></label>
                                    <select class="form-select" id="shipping_method" name="shipping_method">
                                        <option value=""><?= t('sales_orders.select_shipping_method') ?></option>
                                        <option value="standard" <?= $this->selected('shipping_method', 'standard') ?>>
                                            <?= t('sales_orders.shipping.standard') ?>
                                        </option>
                                        <option value="express" <?= $this->selected('shipping_method', 'express') ?>>
                                            <?= t('sales_orders.shipping.express') ?>
                                        </option>
                                        <option value="overnight" <?= $this->selected('shipping_method', 'overnight') ?>>
                                            <?= t('sales_orders.shipping.overnight') ?>
                                        </option>
                                        <option value="pickup" <?= $this->selected('shipping_method', 'pickup') ?>>
                                            <?= t('sales_orders.shipping.pickup') ?>
                                        </option>
                                    </select>
                                    <?= $this->error('shipping_method') ?>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="notes" class="form-label"><?= t('sales_orders.internal_notes') ?></label>
                            <textarea class="form-control" id="notes" name="notes" rows="3" 
                                      placeholder="<?= t('sales_orders.internal_notes_placeholder') ?>"><?= $this->old('notes', $quote->notes ?? '') ?></textarea>
                            <?= $this->error('notes') ?>
                        </div>
                        
                        <div class="mb-3">
                            <label for="special_instructions" class="form-label"><?= t('sales_orders.special_instructions') ?></label>
                            <textarea class="form-control" id="special_instructions" name="special_instructions" rows="3" 
                                      placeholder="<?= t('sales_orders.special_instructions_placeholder') ?>"><?= $this->old('special_instructions') ?></textarea>
                            <?= $this->error('special_instructions') ?>
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
                            <button type="submit" name="action" value="save_pending" class="btn btn-outline-secondary">
                                <i class="fas fa-save"></i> <?= t('sales_orders.save_as_pending') ?>
                            </button>
                            
                            <button type="submit" name="action" value="save_and_process" class="btn btn-primary">
                                <i class="fas fa-play"></i> <?= t('sales_orders.save_and_process') ?>
                            </button>
                            
                            <hr>
                            
                            <a href="/sales-orders" class="btn btn-outline-secondary">
                                <i class="fas fa-times"></i> <?= t('common.cancel') ?>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Client Information -->
                <div class="card" id="client-info" style="display: none;">
                    <div class="card-header">
                        <h5 class="mb-0"><?= t('clients.client_information') ?></h5>
                    </div>
                    <div class="card-body">
                        <div id="client-details"></div>
                    </div>
                </div>

                <!-- Inventory Warnings -->
                <div class="card mt-4" id="inventory-warnings" style="display: none;">
                    <div class="card-header bg-warning text-dark">
                        <h6 class="mb-0">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            <?= t('sales_orders.inventory_warnings') ?>
                        </h6>
                    </div>
                    <div class="card-body">
                        <div id="warnings-list"></div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<!-- Order Item Template -->
<template id="order-item-template">
    <div class="order-item border rounded p-3 mb-3" data-item-index="">
        <div class="row">
            <div class="col-md-4">
                <label class="form-label"><?= t('products.product') ?> *</label>
                <select class="form-select product-select" name="items[INDEX][product_id]" required onchange="updateProductInfo(this)">
                    <option value=""><?= t('common.select_product') ?></option>
                    <?php foreach ($products as $product): ?>
                    <option value="<?= $product->id ?>" 
                            data-price="<?= $product->price ?>" 
                            data-unit="<?= htmlspecialchars($product->unit) ?>"
                            data-stock="<?= $product->stock_quantity ?>">
                        <?= htmlspecialchars($product->name) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="col-md-2">
                <label class="form-label"><?= t('sales_orders.quantity') ?> *</label>
                <input type="number" class="form-control quantity-input" name="items[INDEX][quantity]" 
                       min="1" step="0.01" value="1" required onchange="calculateItemTotal(this)">
                <small class="text-muted stock-info"></small>
            </div>
            
            <div class="col-md-2">
                <label class="form-label"><?= t('sales_orders.unit_price') ?> *</label>
                <input type="number" class="form-control price-input" name="items[INDEX][unit_price]" 
                       min="0" step="0.01" required onchange="calculateItemTotal(this)">
            </div>
            
            <div class="col-md-2">
                <label class="form-label"><?= t('sales_orders.total') ?></label>
                <input type="number" class="form-control total-input" readonly tabindex="-1">
            </div>
            
            <div class="col-md-2 d-flex align-items-end">
                <button type="button" class="btn btn-outline-danger btn-sm" onclick="removeOrderItem(this)" title="<?= t('common.remove') ?>">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
        </div>
        
        <div class="row mt-2">
            <div class="col-md-12">
                <label class="form-label"><?= t('sales_orders.description') ?></label>
                <textarea class="form-control" name="items[INDEX][description]" rows="2" 
                          placeholder="<?= t('sales_orders.item_description_placeholder') ?>"></textarea>
            </div>
        </div>
    </div>
</template>

<script>
let itemIndex = 0;
let clientsData = <?= json_encode($clients) ?>;
let quoteItems = <?= json_encode($quote->items ?? []) ?>;

document.addEventListener('DOMContentLoaded', function() {
    // Load quote items if converting from quote
    if (quoteItems && quoteItems.length > 0) {
        quoteItems.forEach(item => {
            addOrderItem(item);
        });
    } else {
        // Add initial order item
        addOrderItem();
    }
    
    // Update totals when values change
    document.getElementById('tax_rate').addEventListener('change', calculateTotals);
    document.getElementById('discount_rate').addEventListener('change', calculateTotals);
    document.getElementById('shipping_cost').addEventListener('change', calculateTotals);
    
    // Load client info when client is selected
    document.getElementById('client_id').addEventListener('change', loadClientInfo);
    
    // Pre-select client if converting from quote
    <?php if ($quote): ?>
    loadClientInfo();
    <?php endif; ?>
});

function addOrderItem(itemData = null) {
    const template = document.getElementById('order-item-template');
    const clone = template.content.cloneNode(true);
    
    // Replace INDEX placeholders
    clone.innerHTML = clone.innerHTML.replace(/INDEX/g, itemIndex);
    clone.querySelector('.order-item').setAttribute('data-item-index', itemIndex);
    
    document.getElementById('order-items').appendChild(clone);
    
    // Populate with quote data if available
    if (itemData) {
        const item = document.querySelector(`.order-item[data-item-index="${itemIndex}"]`);
        item.querySelector('.product-select').value = itemData.product_id;
        item.querySelector('.quantity-input').value = itemData.quantity;
        item.querySelector('.price-input').value = itemData.unit_price;
        item.querySelector('textarea').value = itemData.description || '';
        
        updateProductInfo(item.querySelector('.product-select'));
    }
    
    itemIndex++;
}

function removeOrderItem(button) {
    const item = button.closest('.order-item');
    item.remove();
    calculateTotals();
    checkInventoryWarnings();
}

function updateProductInfo(select) {
    const option = select.selectedOptions[0];
    if (option && option.value) {
        const item = select.closest('.order-item');
        const priceInput = item.querySelector('.price-input');
        const stockInfo = item.querySelector('.stock-info');
        const quantityInput = item.querySelector('.quantity-input');
        
        const price = option.getAttribute('data-price');
        const stock = parseInt(option.getAttribute('data-stock'));
        const unit = option.getAttribute('data-unit');
        
        if (price) {
            priceInput.value = parseFloat(price).toFixed(2);
        }
        
        stockInfo.textContent = `Stock: ${stock} ${unit}`;
        if (stock <= 0) {
            stockInfo.className = 'text-danger stock-info';
        } else if (stock < 10) {
            stockInfo.className = 'text-warning stock-info';
        } else {
            stockInfo.className = 'text-muted stock-info';
        }
        
        quantityInput.max = stock;
        calculateItemTotal(priceInput);
        checkInventoryWarnings();
    }
}

function calculateItemTotal(input) {
    const item = input.closest('.order-item');
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
    const shippingCost = parseFloat(document.getElementById('shipping_cost').value) || 0;
    
    const discountAmount = (subtotal * discountRate) / 100;
    const taxableAmount = subtotal - discountAmount;
    const taxAmount = (taxableAmount * taxRate) / 100;
    const totalAmount = taxableAmount + taxAmount + shippingCost;
    
    document.getElementById('subtotal').textContent = subtotal.toFixed(2);
    document.getElementById('tax_amount').textContent = taxAmount.toFixed(2);
    document.getElementById('discount_amount').textContent = discountAmount.toFixed(2);
    document.getElementById('shipping_amount').textContent = shippingCost.toFixed(2);
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
            clientInfo.style.display = 'block';
            
            // Auto-fill shipping address if available
            if (client.address && !document.getElementById('shipping_address').value) {
                document.getElementById('shipping_address').value = client.address;
            }
        }
    } else {
        clientInfo.style.display = 'none';
    }
}

function checkInventoryWarnings() {
    const warnings = [];
    const warningsCard = document.getElementById('inventory-warnings');
    const warningsList = document.getElementById('warnings-list');
    
    document.querySelectorAll('.order-item').forEach(item => {
        const productSelect = item.querySelector('.product-select');
        const quantityInput = item.querySelector('.quantity-input');
        
        if (productSelect.value && quantityInput.value) {
            const option = productSelect.selectedOptions[0];
            const stock = parseInt(option.getAttribute('data-stock'));
            const requested = parseFloat(quantityInput.value);
            const productName = option.textContent;
            
            if (requested > stock) {
                warnings.push(`<div class="alert alert-warning py-2 mb-2">
                    <strong>${productName}</strong><br>
                    Requested: ${requested}, Available: ${stock}
                </div>`);
            }
        }
    });
    
    if (warnings.length > 0) {
        warningsList.innerHTML = warnings.join('');
        warningsCard.style.display = 'block';
    } else {
        warningsCard.style.display = 'none';
    }
}

// Form validation
document.getElementById('salesOrderForm').addEventListener('submit', function(e) {
    const items = document.querySelectorAll('.order-item');
    if (items.length === 0) {
        e.preventDefault();
        showAlert('error', '<?= t('sales_orders.error_no_items') ?>');
        return false;
    }
    
    let hasValidItems = false;
    items.forEach(item => {
        const productSelect = item.querySelector('.product-select');
        const quantity = item.querySelector('.quantity-input');
        const price = item.querySelector('.price-input');
        
        if (productSelect.value && quantity.value && price.value) {
            hasValidItems = true;
        }
    });
    
    if (!hasValidItems) {
        e.preventDefault();
        showAlert('error', '<?= t('sales_orders.error_invalid_items') ?>');
        return false;
    }
});
</script>