<?php
/**
 * File: app/views/invoices/create.php
 * Purpose: Invoice creation form with comprehensive billing setup
 * Layout: Uses app layout with professional invoice creation interface
 */

$this->layout('layouts/app', [
    'title' => $page_title ?? t('invoices.create_invoice'),
    'active_nav' => 'invoices'
]);

$currentUser = $this->getCurrentUser();
$canCreate = $this->hasRole(['admin', 'manager', 'accounting', 'sales']);
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">
            <i class="fas fa-plus me-2"></i><?= t('invoices.create_new_invoice') ?>
        </h1>
        <div class="btn-toolbar mb-2 mb-md-0">
            <a href="/invoices" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left"></i> <?= t('common.back_to_list') ?>
            </a>
        </div>
    </div>

    <form id="invoiceForm">
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
                                           value="<?= 'INV-' . date('Y') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT) ?>" required>
                                    <div class="form-text"><?= t('invoices.invoice_number_help') ?></div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label"><?= t('invoices.status') ?></label>
                                    <select class="form-select" name="status">
                                        <option value="draft" selected><?= t('invoices.status.draft') ?></option>
                                        <option value="sent"><?= t('invoices.status.sent') ?></option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label"><?= t('invoices.invoice_date') ?> *</label>
                                    <input type="date" class="form-control" name="invoice_date" 
                                           value="<?= date('Y-m-d') ?>" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label"><?= t('invoices.due_date') ?> *</label>
                                    <input type="date" class="form-control" name="due_date" 
                                           value="<?= date('Y-m-d', strtotime('+30 days')) ?>" required>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label"><?= t('invoices.client') ?> *</label>
                                    <select class="form-select" name="client_id" required id="clientSelect">
                                        <option value=""><?= t('common.select_client') ?></option>
                                        <?php if (!empty($clients)): ?>
                                        <?php foreach ($clients as $client): ?>
                                        <option value="<?= $client->id ?>" 
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
                                    <select class="form-select" name="sales_order_id" id="salesOrderSelect">
                                        <option value=""><?= t('invoices.select_order_optional') ?></option>
                                        <?php if (!empty($sales_orders)): ?>
                                        <?php foreach ($sales_orders as $order): ?>
                                        <option value="<?= $order->id ?>" data-client="<?= $order->client_id ?>">
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
                                    <input type="text" class="form-control" name="po_number">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label"><?= t('invoices.payment_terms') ?></label>
                                    <select class="form-select" name="payment_terms">
                                        <option value="Net 15">Net 15</option>
                                        <option value="Net 30" selected>Net 30</option>
                                        <option value="Net 45">Net 45</option>
                                        <option value="Net 60">Net 60</option>
                                        <option value="Due on Receipt">Due on Receipt</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label"><?= t('invoices.currency') ?></label>
                                    <select class="form-select" name="currency">
                                        <option value="USD" selected>USD - US Dollar</option>
                                        <option value="EUR">EUR - Euro</option>
                                        <option value="AED">AED - UAE Dirham</option>
                                        <option value="SAR">SAR - Saudi Riyal</option>
                                        <option value="GBP">GBP - British Pound</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label"><?= t('invoices.reference') ?></label>
                                    <input type="text" class="form-control" name="reference">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Invoice Items -->
                <div class="card mb-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><?= t('invoices.invoice_items') ?></h5>
                        <button type="button" class="btn btn-primary btn-sm" onclick="addInvoiceItem()">
                            <i class="fas fa-plus"></i> <?= t('invoices.add_item') ?>
                        </button>
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
                                        <th style="width: 10%;" class="text-center"><?= t('common.actions') ?></th>
                                    </tr>
                                </thead>
                                <tbody id="invoiceItemsBody">
                                    <!-- Items will be added dynamically -->
                                </tbody>
                            </table>
                        </div>
                        
                        <div class="row mt-4">
                            <div class="col-md-6">
                                <button type="button" class="btn btn-outline-primary" onclick="loadFromOrder()">
                                    <i class="fas fa-download"></i> <?= t('invoices.load_from_order') ?>
                                </button>
                            </div>
                            <div class="col-md-6">
                                <table class="table table-sm">
                                    <tr>
                                        <td class="text-end"><strong><?= t('invoices.subtotal') ?>:</strong></td>
                                        <td class="text-end" id="subtotalAmount">0.00</td>
                                    </tr>
                                    <tr>
                                        <td class="text-end">
                                            <div class="input-group input-group-sm">
                                                <span class="input-group-text"><?= t('invoices.discount') ?></span>
                                                <input type="number" class="form-control" name="discount_rate" 
                                                       min="0" max="100" step="0.01" value="0" 
                                                       onchange="calculateTotals()" style="max-width: 80px;">
                                                <span class="input-group-text">%</span>
                                            </div>
                                        </td>
                                        <td class="text-end" id="discountAmount">0.00</td>
                                    </tr>
                                    <tr>
                                        <td class="text-end">
                                            <div class="input-group input-group-sm">
                                                <span class="input-group-text"><?= t('invoices.tax') ?></span>
                                                <input type="number" class="form-control" name="tax_rate" 
                                                       min="0" max="50" step="0.01" value="0" 
                                                       onchange="calculateTotals()" style="max-width: 80px;">
                                                <span class="input-group-text">%</span>
                                            </div>
                                        </td>
                                        <td class="text-end" id="taxAmount">0.00</td>
                                    </tr>
                                    <tr class="table-primary">
                                        <td class="text-end"><strong><?= t('invoices.total') ?>:</strong></td>
                                        <td class="text-end"><strong id="totalAmount">0.00</strong></td>
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
                            <textarea class="form-control" name="notes" rows="3"></textarea>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label"><?= t('invoices.terms_conditions') ?></label>
                            <textarea class="form-control" name="terms_conditions" rows="4"></textarea>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <!-- Client Information -->
                <div class="card mb-4" id="clientInfoCard" style="display: none;">
                    <div class="card-header">
                        <h5 class="mb-0"><?= t('invoices.client_information') ?></h5>
                    </div>
                    <div class="card-body" id="clientInfoBody">
                        <!-- Client info will be populated dynamically -->
                    </div>
                </div>

                <!-- Invoice Summary -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0"><?= t('invoices.invoice_summary') ?></h5>
                    </div>
                    <div class="card-body">
                        <div class="d-flex justify-content-between mb-2">
                            <span><?= t('invoices.total_items') ?>:</span>
                            <span id="itemCount">0</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span><?= t('invoices.subtotal') ?>:</span>
                            <span id="summarySubtotal">0.00</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span><?= t('invoices.total_amount') ?>:</span>
                            <strong id="summaryTotal">0.00</strong>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between">
                            <span><?= t('invoices.status') ?>:</span>
                            <span class="badge bg-secondary" id="summaryStatus"><?= t('invoices.status.draft') ?></span>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0"><?= t('invoices.quick_actions') ?></h5>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <button type="button" class="btn btn-outline-primary btn-sm" onclick="previewInvoice()">
                                <i class="fas fa-eye"></i> <?= t('invoices.preview') ?>
                            </button>
                            <button type="button" class="btn btn-outline-secondary btn-sm" onclick="saveAsDraft()">
                                <i class="fas fa-save"></i> <?= t('invoices.save_draft') ?>
                            </button>
                            <button type="button" class="btn btn-outline-info btn-sm" onclick="calculateFromTemplate()">
                                <i class="fas fa-calculator"></i> <?= t('invoices.use_template') ?>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="card">
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-plus"></i> <?= t('invoices.create_invoice') ?>
                            </button>
                            <button type="button" class="btn btn-outline-success" onclick="createAndSend()">
                                <i class="fas fa-paper-plane"></i> <?= t('invoices.create_and_send') ?>
                            </button>
                            <button type="button" class="btn btn-outline-secondary" onclick="resetForm()">
                                <i class="fas fa-undo"></i> <?= t('common.reset_form') ?>
                            </button>
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

<!-- Item Selection Modal -->
<div class="modal fade" id="itemModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><?= t('invoices.select_product_service') ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <input type="text" class="form-control" id="productSearch" 
                           placeholder="<?= t('invoices.search_products') ?>">
                </div>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th><?= t('products.name') ?></th>
                                <th><?= t('products.sku') ?></th>
                                <th class="text-end"><?= t('products.price') ?></th>
                                <th class="text-center"><?= t('products.stock') ?></th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody id="productList">
                            <?php if (!empty($products)): ?>
                            <?php foreach ($products as $product): ?>
                            <tr>
                                <td><?= htmlspecialchars($product->name) ?></td>
                                <td><?= htmlspecialchars($product->sku) ?></td>
                                <td class="text-end"><?= number_format($product->selling_price, 2) ?></td>
                                <td class="text-center"><?= $product->stock_quantity ?? 0 ?></td>
                                <td>
                                    <button type="button" class="btn btn-sm btn-primary" 
                                            onclick="selectProduct(<?= $product->id ?>, '<?= htmlspecialchars($product->name) ?>', <?= $product->selling_price ?>)">
                                        <?= t('common.select') ?>
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
let itemCounter = 0;

document.addEventListener('DOMContentLoaded', function() {
    // Add first item row
    addInvoiceItem();
    
    // Client selection handler
    document.getElementById('clientSelect').addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        if (selectedOption.value) {
            updateClientInfo(selectedOption);
            filterSalesOrders(selectedOption.value);
        } else {
            document.getElementById('clientInfoCard').style.display = 'none';
        }
    });
    
    // Sales order selection handler
    document.getElementById('salesOrderSelect').addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        if (selectedOption.value && selectedOption.dataset.client) {
            // Auto-select client if not already selected
            const clientSelect = document.getElementById('clientSelect');
            if (!clientSelect.value) {
                clientSelect.value = selectedOption.dataset.client;
                clientSelect.dispatchEvent(new Event('change'));
            }
        }
    });
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
            <div class="btn-group">
                <button type="button" class="btn btn-sm btn-outline-primary" onclick="selectItemProduct(${itemCounter})">
                    <i class="fas fa-search"></i>
                </button>
                <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeInvoiceItem(${itemCounter})">
                    <i class="fas fa-times"></i>
                </button>
            </div>
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
    
    // Ensure at least one item remains
    const tbody = document.getElementById('invoiceItemsBody');
    if (tbody.children.length === 0) {
        addInvoiceItem();
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
    
    // Sum all item totals
    document.querySelectorAll('.item-total').forEach(span => {
        subtotal += parseFloat(span.textContent) || 0;
    });
    
    const discountRate = parseFloat(document.querySelector('input[name="discount_rate"]').value) || 0;
    const taxRate = parseFloat(document.querySelector('input[name="tax_rate"]').value) || 0;
    
    const discountAmount = subtotal * (discountRate / 100);
    const taxableAmount = subtotal - discountAmount;
    const taxAmount = taxableAmount * (taxRate / 100);
    const total = taxableAmount + taxAmount;
    
    // Update displays
    document.getElementById('subtotalAmount').textContent = subtotal.toFixed(2);
    document.getElementById('discountAmount').textContent = discountAmount.toFixed(2);
    document.getElementById('taxAmount').textContent = taxAmount.toFixed(2);
    document.getElementById('totalAmount').textContent = total.toFixed(2);
    
    // Update summary
    document.getElementById('itemCount').textContent = document.querySelectorAll('#invoiceItemsBody tr').length;
    document.getElementById('summarySubtotal').textContent = subtotal.toFixed(2);
    document.getElementById('summaryTotal').textContent = total.toFixed(2);
}

function updateClientInfo(option) {
    const clientInfoCard = document.getElementById('clientInfoCard');
    const clientInfoBody = document.getElementById('clientInfoBody');
    
    clientInfoBody.innerHTML = `
        <div class="mb-2">
            <strong><?= t('clients.email') ?>:</strong><br>
            ${option.dataset.email}
        </div>
        <div class="mb-2">
            <strong><?= t('clients.address') ?>:</strong><br>
            ${option.dataset.address || '<?= t('common.not_specified') ?>'}
        </div>
        <div class="mb-2">
            <strong><?= t('clients.currency') ?>:</strong><br>
            ${option.dataset.currency}
        </div>
        <div class="mb-2">
            <strong><?= t('clients.payment_terms') ?>:</strong><br>
            ${option.dataset.paymentTerms}
        </div>
    `;
    
    // Update form fields
    document.querySelector('select[name="currency"]').value = option.dataset.currency;
    document.querySelector('select[name="payment_terms"]').value = option.dataset.paymentTerms;
    
    clientInfoCard.style.display = 'block';
}

function filterSalesOrders(clientId) {
    const salesOrderSelect = document.getElementById('salesOrderSelect');
    const options = salesOrderSelect.querySelectorAll('option');
    
    options.forEach(option => {
        if (option.value === '' || option.dataset.client === clientId) {
            option.style.display = 'block';
        } else {
            option.style.display = 'none';
        }
    });
}

function selectItemProduct(itemId) {
    // Store current item ID for the modal
    window.currentItemId = itemId;
    const modal = new bootstrap.Modal(document.getElementById('itemModal'));
    modal.show();
}

function selectProduct(productId, productName, productPrice) {
    const itemId = window.currentItemId;
    const descriptionInput = document.querySelector(`input[name="items[${itemId}][description]"]`);
    const productIdInput = document.querySelector(`input[name="items[${itemId}][product_id]"]`);
    const priceInput = document.querySelector(`input[name="items[${itemId}][unit_price]"]`);
    
    descriptionInput.value = productName;
    productIdInput.value = productId;
    priceInput.value = productPrice.toFixed(2);
    
    calculateItemTotal(itemId);
    
    bootstrap.Modal.getInstance(document.getElementById('itemModal')).hide();
}

document.getElementById('invoiceForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    
    fetch('/invoices', {
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
        document.getElementById('invoiceForm').reset();
        document.getElementById('invoiceItemsBody').innerHTML = '';
        itemCounter = 0;
        addInvoiceItem();
        calculateTotals();
        document.getElementById('clientInfoCard').style.display = 'none';
    }
}

function createAndSend() {
    document.querySelector('select[name="status"]').value = 'sent';
    document.getElementById('invoiceForm').dispatchEvent(new Event('submit'));
}
</script>