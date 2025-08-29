<?php
/**
 * File: app/views/salesorders/edit.php
 * Purpose: Sales Order editing form with status-based permissions
 * Layout: Uses app layout with comprehensive order modification capabilities
 */

$this->layout('layouts/app', [
    'title' => $page_title ?? t('sales_orders.edit_order'),
    'active_nav' => 'sales_orders'
]);

$currentUser = $this->getCurrentUser();
$canEdit = $this->hasRole(['admin', 'manager', 'sales']) && in_array($order->status, ['pending', 'confirmed']);
$canDelete = $this->hasRole(['admin', 'manager']) && in_array($order->status, ['pending', 'cancelled']);
$isReadOnly = !$canEdit || in_array($order->status, ['shipped', 'completed', 'cancelled']);

$clients = $clients ?? [];
$products = $products ?? [];
$currencies = $currencies ?? [];
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">
            <i class="fas fa-edit me-2"></i><?= t('sales_orders.edit_order') ?>
            <small class="text-muted ms-2"><?= htmlspecialchars($order->order_number) ?></small>
        </h1>
        <div class="btn-toolbar mb-2 mb-md-0">
            <?php if (!$isReadOnly): ?>
            <div class="btn-group me-2">
                <button type="button" class="btn btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown">
                    <i class="fas fa-cogs"></i> <?= t('common.actions') ?>
                </button>
                <ul class="dropdown-menu">
                    <li><a class="dropdown-item" href="/sales-orders/<?= $order->id ?>" target="_blank">
                        <i class="fas fa-eye me-2"></i><?= t('common.view') ?>
                    </a></li>
                    <li><a class="dropdown-item" href="/sales-orders/<?= $order->id ?>/duplicate">
                        <i class="fas fa-copy me-2"></i><?= t('common.duplicate') ?>
                    </a></li>
                    <?php if ($canDelete): ?>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item text-danger" href="#" onclick="deleteOrder(<?= $order->id ?>)">
                        <i class="fas fa-trash me-2"></i><?= t('common.delete') ?>
                    </a></li>
                    <?php endif; ?>
                </ul>
            </div>
            <?php endif; ?>
            
            <a href="/sales-orders" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left"></i> <?= t('common.back') ?>
            </a>
        </div>
    </div>

    <?php if ($isReadOnly): ?>
    <div class="alert alert-info">
        <i class="fas fa-info-circle me-2"></i>
        <?= t('sales_orders.readonly_notice', ['status' => t('sales_orders.status.' . $order->status)]) ?>
    </div>
    <?php endif; ?>

    <form method="POST" action="/sales-orders/<?= $order->id ?>" id="editOrderForm">
        <?= $this->csrf() ?>
        <input type="hidden" name="_method" value="PUT">
        
        <div class="row">
            <div class="col-md-8">
                <!-- Order Details -->
                <div class="card mb-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><?= t('sales_orders.order_details') ?></h5>
                        <div>
                            <?php 
                            $statusClass = match($order->status) {
                                'pending' => 'bg-warning text-dark',
                                'confirmed' => 'bg-info',
                                'processing' => 'bg-primary',
                                'shipped' => 'bg-secondary',
                                'completed' => 'bg-success',
                                'cancelled' => 'bg-danger',
                                default => 'bg-secondary'
                            };
                            ?>
                            <span class="badge <?= $statusClass ?> me-2">
                                <?= t('sales_orders.status.' . $order->status) ?>
                            </span>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="client_id" class="form-label"><?= t('clients.client') ?> *</label>
                                    <select class="form-select" id="client_id" name="client_id" <?= $isReadOnly ? 'disabled' : 'required' ?>>
                                        <option value=""><?= t('common.select_client') ?></option>
                                        <?php foreach ($clients as $client): ?>
                                        <option value="<?= $client->id ?>" 
                                                <?= $this->selected('client_id', $client->id, $order->client_id) ?>>
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
                                           value="<?= $this->old('order_date', date('Y-m-d', strtotime($order->order_date))) ?>" 
                                           <?= $isReadOnly ? 'readonly' : 'required' ?>>
                                    <?= $this->error('order_date') ?>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="delivery_date" class="form-label"><?= t('sales_orders.delivery_date') ?></label>
                                    <input type="date" class="form-control" id="delivery_date" name="delivery_date" 
                                           value="<?= $this->old('delivery_date', $order->delivery_date ? date('Y-m-d', strtotime($order->delivery_date)) : '') ?>" 
                                           <?= $isReadOnly ? 'readonly' : '' ?>>
                                    <?= $this->error('delivery_date') ?>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="currency" class="form-label"><?= t('common.currency') ?> *</label>
                                    <select class="form-select" id="currency" name="currency" <?= $isReadOnly ? 'disabled' : 'required' ?>>
                                        <?php foreach ($currencies as $currency): ?>
                                        <option value="<?= $currency->code ?>" 
                                                <?= $this->selected('currency', $currency->code, $order->currency) ?>>
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
                                           value="<?= $this->old('reference', $order->reference) ?>" 
                                           placeholder="<?= t('sales_orders.reference_placeholder') ?>"
                                           <?= $isReadOnly ? 'readonly' : '' ?>>
                                    <?= $this->error('reference') ?>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="priority" class="form-label"><?= t('sales_orders.priority') ?></label>
                                    <select class="form-select" id="priority" name="priority" <?= $isReadOnly ? 'disabled' : '' ?>>
                                        <option value="normal" <?= $this->selected('priority', 'normal', $order->priority) ?>>
                                            <?= t('sales_orders.priority.normal') ?>
                                        </option>
                                        <option value="high" <?= $this->selected('priority', 'high', $order->priority) ?>>
                                            <?= t('sales_orders.priority.high') ?>
                                        </option>
                                        <option value="urgent" <?= $this->selected('priority', 'urgent', $order->priority) ?>>
                                            <?= t('sales_orders.priority.urgent') ?>
                                        </option>
                                    </select>
                                    <?= $this->error('priority') ?>
                                </div>
                            </div>
                        </div>
                        
                        <?php if ($this->hasRole(['admin', 'manager'])): ?>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="status" class="form-label"><?= t('common.status') ?></label>
                                    <select class="form-select" id="status" name="status" <?= $isReadOnly ? 'disabled' : '' ?>>
                                        <option value="pending" <?= $this->selected('status', 'pending', $order->status) ?>>
                                            <?= t('sales_orders.status.pending') ?>
                                        </option>
                                        <option value="confirmed" <?= $this->selected('status', 'confirmed', $order->status) ?>>
                                            <?= t('sales_orders.status.confirmed') ?>
                                        </option>
                                        <option value="processing" <?= $this->selected('status', 'processing', $order->status) ?>>
                                            <?= t('sales_orders.status.processing') ?>
                                        </option>
                                        <option value="shipped" <?= $this->selected('status', 'shipped', $order->status) ?>>
                                            <?= t('sales_orders.status.shipped') ?>
                                        </option>
                                        <option value="completed" <?= $this->selected('status', 'completed', $order->status) ?>>
                                            <?= t('sales_orders.status.completed') ?>
                                        </option>
                                        <option value="cancelled" <?= $this->selected('status', 'cancelled', $order->status) ?>>
                                            <?= t('sales_orders.status.cancelled') ?>
                                        </option>
                                    </select>
                                    <?= $this->error('status') ?>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="assigned_to" class="form-label"><?= t('sales_orders.assigned_to') ?></label>
                                    <select class="form-select" id="assigned_to" name="assigned_to" <?= $isReadOnly ? 'disabled' : '' ?>>
                                        <option value=""><?= t('common.unassigned') ?></option>
                                        <?php foreach (($users ?? []) as $user): ?>
                                        <option value="<?= $user->id ?>" 
                                                <?= $this->selected('assigned_to', $user->id, $order->assigned_to) ?>>
                                            <?= htmlspecialchars($user->name) ?>
                                        </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <?= $this->error('assigned_to') ?>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Order Items -->
                <div class="card mb-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><?= t('sales_orders.order_items') ?></h5>
                        <?php if (!$isReadOnly): ?>
                        <button type="button" class="btn btn-sm btn-primary" onclick="addOrderItem()">
                            <i class="fas fa-plus"></i> <?= t('sales_orders.add_item') ?>
                        </button>
                        <?php endif; ?>
                    </div>
                    <div class="card-body">
                        <div id="order-items">
                            <!-- Existing order items will be populated here -->
                        </div>
                        
                        <div class="row mt-3">
                            <div class="col-md-8"></div>
                            <div class="col-md-4">
                                <div class="table-responsive">
                                    <table class="table table-sm">
                                        <tr>
                                            <td><strong><?= t('sales_orders.subtotal') ?>:</strong></td>
                                            <td class="text-end"><span id="subtotal"><?= number_format($order->subtotal, 2) ?></span></td>
                                        </tr>
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <label for="tax_rate" class="form-label mb-0 me-2"><?= t('sales_orders.tax') ?>:</label>
                                                    <input type="number" class="form-control form-control-sm" id="tax_rate" name="tax_rate" 
                                                           value="<?= $this->old('tax_rate', $order->tax_rate ?? '0') ?>" 
                                                           min="0" max="100" step="0.01" style="width: 80px;" 
                                                           <?= $isReadOnly ? 'readonly' : '' ?>>
                                                    <span class="ms-1">%</span>
                                                </div>
                                            </td>
                                            <td class="text-end"><span id="tax_amount"><?= number_format($order->tax_amount, 2) ?></span></td>
                                        </tr>
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <label for="discount_rate" class="form-label mb-0 me-2"><?= t('sales_orders.discount') ?>:</label>
                                                    <input type="number" class="form-control form-control-sm" id="discount_rate" name="discount_rate" 
                                                           value="<?= $this->old('discount_rate', $order->discount_rate ?? '0') ?>" 
                                                           min="0" max="100" step="0.01" style="width: 80px;" 
                                                           <?= $isReadOnly ? 'readonly' : '' ?>>
                                                    <span class="ms-1">%</span>
                                                </div>
                                            </td>
                                            <td class="text-end">-<span id="discount_amount"><?= number_format($order->discount_amount, 2) ?></span></td>
                                        </tr>
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <label for="shipping_cost" class="form-label mb-0 me-2"><?= t('sales_orders.shipping') ?>:</label>
                                                    <input type="number" class="form-control form-control-sm" id="shipping_cost" name="shipping_cost" 
                                                           value="<?= $this->old('shipping_cost', $order->shipping_cost ?? '0') ?>" 
                                                           min="0" step="0.01" style="width: 100px;" 
                                                           <?= $isReadOnly ? 'readonly' : '' ?>>
                                                </div>
                                            </td>
                                            <td class="text-end"><span id="shipping_amount"><?= number_format($order->shipping_cost, 2) ?></span></td>
                                        </tr>
                                        <tr class="table-primary">
                                            <td><strong><?= t('sales_orders.total') ?>:</strong></td>
                                            <td class="text-end"><strong><span id="total_amount"><?= number_format($order->total_amount, 2) ?></span></strong></td>
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
                                              placeholder="<?= t('sales_orders.shipping_address_placeholder') ?>"
                                              <?= $isReadOnly ? 'readonly' : '' ?>><?= $this->old('shipping_address', $order->shipping_address) ?></textarea>
                                    <?= $this->error('shipping_address') ?>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="shipping_method" class="form-label"><?= t('sales_orders.shipping_method') ?></label>
                                    <select class="form-select" id="shipping_method" name="shipping_method" <?= $isReadOnly ? 'disabled' : '' ?>>
                                        <option value=""><?= t('sales_orders.select_shipping_method') ?></option>
                                        <option value="standard" <?= $this->selected('shipping_method', 'standard', $order->shipping_method) ?>>
                                            <?= t('sales_orders.shipping.standard') ?>
                                        </option>
                                        <option value="express" <?= $this->selected('shipping_method', 'express', $order->shipping_method) ?>>
                                            <?= t('sales_orders.shipping.express') ?>
                                        </option>
                                        <option value="overnight" <?= $this->selected('shipping_method', 'overnight', $order->shipping_method) ?>>
                                            <?= t('sales_orders.shipping.overnight') ?>
                                        </option>
                                        <option value="pickup" <?= $this->selected('shipping_method', 'pickup', $order->shipping_method) ?>>
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
                                      placeholder="<?= t('sales_orders.internal_notes_placeholder') ?>"
                                      <?= $isReadOnly ? 'readonly' : '' ?>><?= $this->old('notes', $order->notes) ?></textarea>
                            <?= $this->error('notes') ?>
                        </div>
                        
                        <div class="mb-3">
                            <label for="special_instructions" class="form-label"><?= t('sales_orders.special_instructions') ?></label>
                            <textarea class="form-control" id="special_instructions" name="special_instructions" rows="3" 
                                      placeholder="<?= t('sales_orders.special_instructions_placeholder') ?>"
                                      <?= $isReadOnly ? 'readonly' : '' ?>><?= $this->old('special_instructions', $order->special_instructions) ?></textarea>
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
                        <?php if (!$isReadOnly): ?>
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> <?= t('common.save_changes') ?>
                            </button>
                            
                            <button type="button" class="btn btn-outline-secondary" onclick="history.back()">
                                <i class="fas fa-times"></i> <?= t('common.cancel') ?>
                            </button>
                        </div>
                        <?php else: ?>
                        <div class="alert alert-info mb-0">
                            <i class="fas fa-lock me-2"></i>
                            <?= t('sales_orders.editing_disabled') ?>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Order Information -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0"><?= t('sales_orders.order_information') ?></h5>
                    </div>
                    <div class="card-body">
                        <table class="table table-sm table-borderless">
                            <tr>
                                <td><strong><?= t('sales_orders.order_number') ?>:</strong></td>
                                <td><?= htmlspecialchars($order->order_number) ?></td>
                            </tr>
                            <tr>
                                <td><strong><?= t('sales_orders.created_by') ?>:</strong></td>
                                <td><?= htmlspecialchars($order->created_by_name ?? 'System') ?></td>
                            </tr>
                            <tr>
                                <td><strong><?= t('sales_orders.created_at') ?>:</strong></td>
                                <td><?= date('M d, Y H:i', strtotime($order->created_at)) ?></td>
                            </tr>
                            <tr>
                                <td><strong><?= t('sales_orders.last_updated') ?>:</strong></td>
                                <td><?= date('M d, Y H:i', strtotime($order->updated_at)) ?></td>
                            </tr>
                            <?php if ($order->quote_id): ?>
                            <tr>
                                <td><strong><?= t('sales_orders.source_quote') ?>:</strong></td>
                                <td>
                                    <a href="/quotes/<?= $order->quote_id ?>" class="text-decoration-none">
                                        <?= htmlspecialchars($order->quote_number ?? 'Quote #' . $order->quote_id) ?>
                                    </a>
                                </td>
                            </tr>
                            <?php endif; ?>
                        </table>
                    </div>
                </div>

                <!-- Client Information -->
                <div class="card" id="client-info" style="<?= $order->client_id ? '' : 'display: none;' ?>">
                    <div class="card-header">
                        <h5 class="mb-0"><?= t('clients.client_information') ?></h5>
                    </div>
                    <div class="card-body">
                        <div id="client-details">
                            <?php if ($order->client_name): ?>
                            <div class="mb-2">
                                <strong><?= htmlspecialchars($order->client_name) ?></strong>
                            </div>
                            <?php if ($order->client_email): ?>
                            <div class="mb-2">
                                <i class="fas fa-envelope me-2"></i><?= htmlspecialchars($order->client_email) ?>
                            </div>
                            <?php endif; ?>
                            <?php if ($order->client_phone): ?>
                            <div class="mb-2">
                                <i class="fas fa-phone me-2"></i><?= htmlspecialchars($order->client_phone) ?>
                            </div>
                            <?php endif; ?>
                            <?php if ($order->client_address): ?>
                            <div class="mb-2">
                                <i class="fas fa-map-marker-alt me-2"></i><?= htmlspecialchars($order->client_address) ?>
                            </div>
                            <?php endif; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<!-- Order Item Template -->
<?php if (!$isReadOnly): ?>
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
        
        <input type="hidden" name="items[INDEX][id]" class="item-id">
    </div>
</template>
<?php endif; ?>

<script>
let itemIndex = 0;
let clientsData = <?= json_encode($clients) ?>;
let existingItems = <?= json_encode($order->items ?? []) ?>;
const isReadOnly = <?= $isReadOnly ? 'true' : 'false' ?>;

document.addEventListener('DOMContentLoaded', function() {
    // Load existing order items
    if (existingItems && existingItems.length > 0) {
        existingItems.forEach(item => {
            addOrderItem(item);
        });
    } else if (!isReadOnly) {
        // Add initial empty item if no existing items and not readonly
        addOrderItem();
    }
    
    if (!isReadOnly) {
        // Update totals when values change
        document.getElementById('tax_rate').addEventListener('change', calculateTotals);
        document.getElementById('discount_rate').addEventListener('change', calculateTotals);
        document.getElementById('shipping_cost').addEventListener('change', calculateTotals);
        
        // Load client info when client is selected
        document.getElementById('client_id').addEventListener('change', loadClientInfo);
    }
    
    // Initial calculation
    calculateTotals();
    
    // Load client info if already selected
    if (document.getElementById('client_id').value) {
        loadClientInfo();
    }
});

function addOrderItem(itemData = null) {
    if (isReadOnly) return;
    
    const template = document.getElementById('order-item-template');
    const clone = template.content.cloneNode(true);
    
    // Replace INDEX placeholders
    clone.innerHTML = clone.innerHTML.replace(/INDEX/g, itemIndex);
    clone.querySelector('.order-item').setAttribute('data-item-index', itemIndex);
    
    document.getElementById('order-items').appendChild(clone);
    
    // Populate with existing data if available
    if (itemData) {
        const item = document.querySelector(`.order-item[data-item-index="${itemIndex}"]`);
        item.querySelector('.product-select').value = itemData.product_id;
        item.querySelector('.quantity-input').value = itemData.quantity;
        item.querySelector('.price-input').value = itemData.unit_price;
        item.querySelector('textarea').value = itemData.description || '';
        item.querySelector('.item-id').value = itemData.id || '';
        
        updateProductInfo(item.querySelector('.product-select'));
    }
    
    itemIndex++;
}

function removeOrderItem(button) {
    if (isReadOnly) return;
    
    const item = button.closest('.order-item');
    item.remove();
    calculateTotals();
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
        
        if (price && !priceInput.value) {
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
    if (isReadOnly) return;
    
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
            
            // Auto-fill shipping address if empty and available
            if (client.address && !document.getElementById('shipping_address').value) {
                document.getElementById('shipping_address').value = client.address;
            }
        }
    } else {
        clientInfo.style.display = 'none';
    }
}

function deleteOrder(orderId) {
    if (confirm('<?= t('sales_orders.confirm_delete') ?>')) {
        fetch(`/sales-orders/${orderId}`, {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showAlert('success', data.message);
                window.location.href = '/sales-orders';
            } else {
                showAlert('error', data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showAlert('error', '<?= t('messages.error.general') ?>');
        });
    }
}

// Form validation
if (!isReadOnly) {
    document.getElementById('editOrderForm').addEventListener('submit', function(e) {
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
}
</script>