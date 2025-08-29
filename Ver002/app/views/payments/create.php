<?php
/**
 * File: app/views/payments/create.php
 * Purpose: Payment recording form with invoice integration
 * Layout: Uses app layout with real-time validation
 */

$this->layout('layouts/app', [
    'title' => $page_title ?? t('payments.record_payment'),
    'active_nav' => 'payments'
]);

$payment = $payment ?? new stdClass();
$clients = $clients ?? [];
$invoices = $invoices ?? [];
$invoice = $invoice ?? null; // If recording for specific invoice
$currencies = $currencies ?? [];
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">
            <i class="fas fa-money-bill-wave me-2"></i><?= t('payments.record_payment') ?>
            <?php if ($invoice): ?>
            <small class="text-muted ms-2"><?= t('payments.for_invoice') ?> <?= htmlspecialchars($invoice->invoice_number) ?></small>
            <?php endif; ?>
        </h1>
        <div class="btn-toolbar mb-2 mb-md-0">
            <a href="/payments" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left"></i> <?= t('common.back') ?>
            </a>
        </div>
    </div>

    <form method="POST" action="/payments" id="paymentForm">
        <?= $this->csrf() ?>
        
        <?php if ($invoice): ?>
        <input type="hidden" name="invoice_id" value="<?= $invoice->id ?>">
        <?php endif; ?>
        
        <div class="row">
            <div class="col-md-8">
                <!-- Payment Details -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0"><?= t('payments.payment_details') ?></h5>
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
                                                <?= $this->selected('client_id', $client->id, ($invoice ? $invoice->client_id : null)) ?>>
                                            <?= htmlspecialchars($client->name) ?>
                                        </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <?= $this->error('client_id') ?>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="invoice_id" class="form-label"><?= t('payments.invoice') ?></label>
                                    <select class="form-select" id="invoice_id" name="invoice_id">
                                        <option value=""><?= t('payments.select_invoice_optional') ?></option>
                                        <?php foreach ($invoices as $inv): ?>
                                        <option value="<?= $inv->id ?>" 
                                                data-amount="<?= $inv->total_amount ?>"
                                                data-paid="<?= $inv->paid_amount ?? 0 ?>"
                                                data-currency="<?= $inv->currency ?>"
                                                <?= $this->selected('invoice_id', $inv->id, ($invoice ? $invoice->id : null)) ?>>
                                            <?= htmlspecialchars($inv->invoice_number) ?> - 
                                            <?= number_format($inv->total_amount - ($inv->paid_amount ?? 0), 2) ?> <?= $inv->currency ?>
                                        </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <?= $this->error('invoice_id') ?>
                                    <small class="form-text text-muted"><?= t('payments.invoice_help') ?></small>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="amount" class="form-label"><?= t('payments.amount') ?> *</label>
                                    <input type="number" class="form-control" id="amount" name="amount" 
                                           value="<?= $this->old('amount', $invoice ? $invoice->total_amount - ($invoice->paid_amount ?? 0) : '') ?>" 
                                           min="0" step="0.01" required>
                                    <?= $this->error('amount') ?>
                                    <div id="amount-validation" class="form-text"></div>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="currency" class="form-label"><?= t('common.currency') ?> *</label>
                                    <select class="form-select" id="currency" name="currency" required>
                                        <?php foreach ($currencies as $currency): ?>
                                        <option value="<?= $currency->code ?>" 
                                                <?= $this->selected('currency', $currency->code, 
                                                    ($invoice ? $invoice->currency : ($currency->is_default ? $currency->code : null))) ?>>
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
                                    <label for="payment_date" class="form-label"><?= t('payments.payment_date') ?> *</label>
                                    <input type="datetime-local" class="form-control" id="payment_date" name="payment_date" 
                                           value="<?= $this->old('payment_date', date('Y-m-d\TH:i')) ?>" required>
                                    <?= $this->error('payment_date') ?>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="payment_method" class="form-label"><?= t('payments.payment_method') ?> *</label>
                                    <select class="form-select" id="payment_method" name="payment_method" required onchange="togglePaymentFields()">
                                        <option value=""><?= t('payments.select_method') ?></option>
                                        <option value="cash" <?= $this->selected('payment_method', 'cash') ?>>
                                            <?= t('payments.method.cash') ?>
                                        </option>
                                        <option value="credit_card" <?= $this->selected('payment_method', 'credit_card') ?>>
                                            <?= t('payments.method.credit_card') ?>
                                        </option>
                                        <option value="bank_transfer" <?= $this->selected('payment_method', 'bank_transfer') ?>>
                                            <?= t('payments.method.bank_transfer') ?>
                                        </option>
                                        <option value="check" <?= $this->selected('payment_method', 'check') ?>>
                                            <?= t('payments.method.check') ?>
                                        </option>
                                    </select>
                                    <?= $this->error('payment_method') ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Payment Method Specific Fields -->
                <div class="card mb-4" id="method-fields" style="display: none;">
                    <div class="card-header">
                        <h5 class="mb-0 method-title"><?= t('payments.method_details') ?></h5>
                    </div>
                    <div class="card-body">
                        <!-- Credit Card Fields -->
                        <div id="credit_card_fields" class="method-fields" style="display: none;">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="card_last_four" class="form-label"><?= t('payments.card_last_four') ?></label>
                                        <input type="text" class="form-control" id="card_last_four" name="card_last_four" 
                                               value="<?= $this->old('card_last_four') ?>" maxlength="4" pattern="[0-9]{4}">
                                        <?= $this->error('card_last_four') ?>
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="card_type" class="form-label"><?= t('payments.card_type') ?></label>
                                        <select class="form-select" id="card_type" name="card_type">
                                            <option value=""><?= t('payments.select_card_type') ?></option>
                                            <option value="visa" <?= $this->selected('card_type', 'visa') ?>>Visa</option>
                                            <option value="mastercard" <?= $this->selected('card_type', 'mastercard') ?>>Mastercard</option>
                                            <option value="amex" <?= $this->selected('card_type', 'amex') ?>>American Express</option>
                                            <option value="discover" <?= $this->selected('card_type', 'discover') ?>>Discover</option>
                                        </select>
                                        <?= $this->error('card_type') ?>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="authorization_code" class="form-label"><?= t('payments.authorization_code') ?></label>
                                <input type="text" class="form-control" id="authorization_code" name="authorization_code" 
                                       value="<?= $this->old('authorization_code') ?>">
                                <?= $this->error('authorization_code') ?>
                            </div>
                        </div>

                        <!-- Bank Transfer Fields -->
                        <div id="bank_transfer_fields" class="method-fields" style="display: none;">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="bank_name" class="form-label"><?= t('payments.bank_name') ?></label>
                                        <input type="text" class="form-control" id="bank_name" name="bank_name" 
                                               value="<?= $this->old('bank_name') ?>">
                                        <?= $this->error('bank_name') ?>
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="account_number" class="form-label"><?= t('payments.account_number') ?></label>
                                        <input type="text" class="form-control" id="account_number" name="account_number" 
                                               value="<?= $this->old('account_number') ?>">
                                        <?= $this->error('account_number') ?>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Check Fields -->
                        <div id="check_fields" class="method-fields" style="display: none;">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="check_number" class="form-label"><?= t('payments.check_number') ?></label>
                                        <input type="text" class="form-control" id="check_number" name="check_number" 
                                               value="<?= $this->old('check_number') ?>">
                                        <?= $this->error('check_number') ?>
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="check_date" class="form-label"><?= t('payments.check_date') ?></label>
                                        <input type="date" class="form-control" id="check_date" name="check_date" 
                                               value="<?= $this->old('check_date') ?>">
                                        <?= $this->error('check_date') ?>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="bank_routing" class="form-label"><?= t('payments.bank_routing') ?></label>
                                <input type="text" class="form-control" id="bank_routing" name="bank_routing" 
                                       value="<?= $this->old('bank_routing') ?>">
                                <?= $this->error('bank_routing') ?>
                            </div>
                        </div>
                        
                        <!-- Common Transaction Fields -->
                        <div class="mb-3">
                            <label for="transaction_id" class="form-label"><?= t('payments.transaction_id') ?></label>
                            <input type="text" class="form-control" id="transaction_id" name="transaction_id" 
                                   value="<?= $this->old('transaction_id') ?>" placeholder="<?= t('payments.transaction_id_placeholder') ?>">
                            <?= $this->error('transaction_id') ?>
                        </div>
                    </div>
                </div>

                <!-- Notes -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0"><?= t('payments.notes') ?></h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="notes" class="form-label"><?= t('payments.payment_notes') ?></label>
                            <textarea class="form-control" id="notes" name="notes" rows="3" 
                                      placeholder="<?= t('payments.notes_placeholder') ?>"><?= $this->old('notes') ?></textarea>
                            <?= $this->error('notes') ?>
                        </div>
                        
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="send_receipt" name="send_receipt" value="1" 
                                   <?= $this->checked('send_receipt', '1', true) ?>>
                            <label class="form-check-label" for="send_receipt">
                                <?= t('payments.send_receipt_email') ?>
                            </label>
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
                            <button type="submit" name="action" value="record_pending" class="btn btn-outline-warning">
                                <i class="fas fa-hourglass-half"></i> <?= t('payments.record_as_pending') ?>
                            </button>
                            
                            <button type="submit" name="action" value="record_completed" class="btn btn-success">
                                <i class="fas fa-check"></i> <?= t('payments.record_as_completed') ?>
                            </button>
                            
                            <hr>
                            
                            <a href="/payments" class="btn btn-outline-secondary">
                                <i class="fas fa-times"></i> <?= t('common.cancel') ?>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Invoice Information -->
                <div class="card" id="invoice-info" style="display: none;">
                    <div class="card-header">
                        <h5 class="mb-0"><?= t('invoices.invoice_information') ?></h5>
                    </div>
                    <div class="card-body">
                        <div id="invoice-details"></div>
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
            </div>
        </div>
    </form>
</div>

<script>
let clientsData = <?= json_encode($clients) ?>;
let invoicesData = <?= json_encode($invoices) ?>;

document.addEventListener('DOMContentLoaded', function() {
    // Load client and invoice info if pre-selected
    <?php if ($invoice): ?>
    loadInvoiceInfo();
    loadClientInfo();
    <?php endif; ?>
    
    // Update fields when selections change
    document.getElementById('client_id').addEventListener('change', function() {
        loadClientInfo();
        filterInvoicesByClient();
    });
    
    document.getElementById('invoice_id').addEventListener('change', loadInvoiceInfo);
    document.getElementById('amount').addEventListener('change', validateAmount);
    
    togglePaymentFields();
});

function togglePaymentFields() {
    const method = document.getElementById('payment_method').value;
    const methodFields = document.getElementById('method-fields');
    const allMethodFields = document.querySelectorAll('.method-fields');
    
    // Hide all method-specific fields
    allMethodFields.forEach(field => field.style.display = 'none');
    
    if (method && method !== 'cash') {
        methodFields.style.display = 'block';
        document.querySelector('.method-title').textContent = 
            document.querySelector(`option[value="${method}"]`).textContent + ' Details';
        
        // Show specific method fields
        const specificFields = document.getElementById(method + '_fields');
        if (specificFields) {
            specificFields.style.display = 'block';
        }
    } else {
        methodFields.style.display = 'none';
    }
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
        }
    } else {
        clientInfo.style.display = 'none';
    }
}

function loadInvoiceInfo() {
    const invoiceId = document.getElementById('invoice_id').value;
    const invoiceInfo = document.getElementById('invoice-info');
    const invoiceDetails = document.getElementById('invoice-details');
    const amountField = document.getElementById('amount');
    const currencyField = document.getElementById('currency');
    
    if (invoiceId) {
        const invoiceOption = document.querySelector(`#invoice_id option[value="${invoiceId}"]`);
        if (invoiceOption) {
            const totalAmount = parseFloat(invoiceOption.getAttribute('data-amount'));
            const paidAmount = parseFloat(invoiceOption.getAttribute('data-paid'));
            const currency = invoiceOption.getAttribute('data-currency');
            const remainingAmount = totalAmount - paidAmount;
            
            invoiceDetails.innerHTML = `
                <div class="mb-2">
                    <strong>${invoiceOption.textContent.split(' - ')[0]}</strong>
                </div>
                <div class="mb-2">
                    <small class="text-muted">${'<?= t('invoices.total_amount') ?>'}:</small><br>
                    ${totalAmount.toFixed(2)} ${currency}
                </div>
                <div class="mb-2">
                    <small class="text-muted">${'<?= t('invoices.paid_amount') ?>'}:</small><br>
                    ${paidAmount.toFixed(2)} ${currency}
                </div>
                <div class="mb-2">
                    <small class="text-muted">${'<?= t('invoices.remaining_amount') ?>'}:</small><br>
                    <strong class="text-primary">${remainingAmount.toFixed(2)} ${currency}</strong>
                </div>
            `;
            invoiceInfo.style.display = 'block';
            
            // Auto-fill amount and currency
            amountField.value = remainingAmount.toFixed(2);
            currencyField.value = currency;
            
            validateAmount();
        }
    } else {
        invoiceInfo.style.display = 'none';
    }
}

function filterInvoicesByClient() {
    const clientId = document.getElementById('client_id').value;
    const invoiceSelect = document.getElementById('invoice_id');
    const options = invoiceSelect.querySelectorAll('option');
    
    options.forEach(option => {
        if (option.value === '') {
            option.style.display = 'block';
        } else {
            const invoice = invoicesData.find(inv => inv.id == option.value);
            if (!clientId || (invoice && invoice.client_id == clientId)) {
                option.style.display = 'block';
            } else {
                option.style.display = 'none';
            }
        }
    });
    
    // Clear selection if filtered out
    const currentSelection = invoiceSelect.value;
    if (currentSelection) {
        const selectedOption = invoiceSelect.querySelector(`option[value="${currentSelection}"]`);
        if (selectedOption.style.display === 'none') {
            invoiceSelect.value = '';
            document.getElementById('invoice-info').style.display = 'none';
        }
    }
}

function validateAmount() {
    const amountField = document.getElementById('amount');
    const invoiceId = document.getElementById('invoice_id').value;
    const validation = document.getElementById('amount-validation');
    
    if (invoiceId) {
        const invoiceOption = document.querySelector(`#invoice_id option[value="${invoiceId}"]`);
        if (invoiceOption) {
            const totalAmount = parseFloat(invoiceOption.getAttribute('data-amount'));
            const paidAmount = parseFloat(invoiceOption.getAttribute('data-paid'));
            const remainingAmount = totalAmount - paidAmount;
            const enteredAmount = parseFloat(amountField.value) || 0;
            
            if (enteredAmount > remainingAmount) {
                validation.textContent = `<?= t('payments.amount_exceeds_remaining') ?>`;
                validation.className = 'form-text text-danger';
                amountField.classList.add('is-invalid');
            } else if (enteredAmount < remainingAmount && enteredAmount > 0) {
                validation.textContent = `<?= t('payments.partial_payment_note') ?>`;
                validation.className = 'form-text text-info';
                amountField.classList.remove('is-invalid');
            } else {
                validation.textContent = '';
                validation.className = 'form-text';
                amountField.classList.remove('is-invalid');
            }
        }
    } else {
        validation.textContent = '';
        validation.className = 'form-text';
        amountField.classList.remove('is-invalid');
    }
}

// Form validation
document.getElementById('paymentForm').addEventListener('submit', function(e) {
    const amount = parseFloat(document.getElementById('amount').value) || 0;
    
    if (amount <= 0) {
        e.preventDefault();
        showAlert('error', '<?= t('payments.error_invalid_amount') ?>');
        return false;
    }
});
</script>