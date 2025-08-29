<?php
/**
 * File: app/views/payments/edit.php
 * Purpose: Payment editing form with transaction management
 * Layout: Uses app layout with comprehensive payment modification capabilities
 */

$this->layout('layouts/app', [
    'title' => $page_title ?? t('payments.edit_payment'),
    'active_nav' => 'payments'
]);

$currentUser = $this->getCurrentUser();
$canEdit = $this->hasRole(['admin', 'manager', 'finance']);
$canDelete = $this->hasRole(['admin', 'manager']) && $payment->status !== 'completed';
$isReadOnly = !$canEdit || in_array($payment->status, ['completed', 'refunded', 'cancelled']);

$invoices = $invoices ?? [];
$payment_methods = $payment_methods ?? [];
$currencies = $currencies ?? [];
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">
            <i class="fas fa-edit me-2"></i><?= t('payments.edit_payment') ?>
            <small class="text-muted ms-2"><?= htmlspecialchars($payment->payment_number) ?></small>
        </h1>
        <div class="btn-toolbar mb-2 mb-md-0">
            <?php if (!$isReadOnly): ?>
            <div class="btn-group me-2">
                <button type="button" class="btn btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown">
                    <i class="fas fa-cogs"></i> <?= t('common.actions') ?>
                </button>
                <ul class="dropdown-menu">
                    <li><a class="dropdown-item" href="/payments/<?= $payment->id ?>" target="_blank">
                        <i class="fas fa-eye me-2"></i><?= t('common.view') ?>
                    </a></li>
                    <li><a class="dropdown-item" href="/payments/<?= $payment->id ?>/receipt" target="_blank">
                        <i class="fas fa-receipt me-2"></i><?= t('payments.print_receipt') ?>
                    </a></li>
                    <?php if ($payment->status === 'completed' && $this->hasRole(['admin', 'manager'])): ?>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item" href="#" onclick="initiateRefund(<?= $payment->id ?>)">
                        <i class="fas fa-undo me-2"></i><?= t('payments.initiate_refund') ?>
                    </a></li>
                    <?php endif; ?>
                    <?php if ($canDelete): ?>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item text-danger" href="#" onclick="deletePayment(<?= $payment->id ?>)">
                        <i class="fas fa-trash me-2"></i><?= t('common.delete') ?>
                    </a></li>
                    <?php endif; ?>
                </ul>
            </div>
            <?php endif; ?>
            
            <a href="/payments" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left"></i> <?= t('common.back') ?>
            </a>
        </div>
    </div>

    <?php if ($isReadOnly): ?>
    <div class="alert alert-info">
        <i class="fas fa-info-circle me-2"></i>
        <?= t('payments.readonly_notice', ['status' => t('payments.status.' . $payment->status)]) ?>
    </div>
    <?php endif; ?>

    <form method="POST" action="/payments/<?= $payment->id ?>" id="editPaymentForm">
        <?= $this->csrf() ?>
        <input type="hidden" name="_method" value="PUT">
        
        <div class="row">
            <div class="col-md-8">
                <!-- Payment Details -->
                <div class="card mb-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><?= t('payments.payment_details') ?></h5>
                        <div>
                            <?php 
                            $statusClass = match($payment->status) {
                                'pending' => 'bg-warning text-dark',
                                'processing' => 'bg-info',
                                'completed' => 'bg-success',
                                'failed' => 'bg-danger',
                                'refunded' => 'bg-secondary',
                                'cancelled' => 'bg-dark',
                                default => 'bg-secondary'
                            };
                            ?>
                            <span class="badge <?= $statusClass ?> me-2">
                                <?= t('payments.status.' . $payment->status) ?>
                            </span>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="invoice_id" class="form-label"><?= t('invoices.invoice') ?> *</label>
                                    <select class="form-select" id="invoice_id" name="invoice_id" <?= $isReadOnly ? 'disabled' : 'required' ?>>
                                        <option value=""><?= t('payments.select_invoice') ?></option>
                                        <?php foreach ($invoices as $invoice): ?>
                                        <option value="<?= $invoice->id ?>" 
                                                data-amount="<?= $invoice->total_amount ?>"
                                                data-currency="<?= $invoice->currency ?>"
                                                data-outstanding="<?= $invoice->outstanding_amount ?>"
                                                <?= $this->selected('invoice_id', $invoice->id, $payment->invoice_id) ?>>
                                            <?= htmlspecialchars($invoice->invoice_number) ?> - 
                                            <?= number_format($invoice->outstanding_amount, 2) ?> <?= $invoice->currency ?>
                                        </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <?= $this->error('invoice_id') ?>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="payment_date" class="form-label"><?= t('payments.payment_date') ?> *</label>
                                    <input type="datetime-local" class="form-control" id="payment_date" name="payment_date" 
                                           value="<?= $this->old('payment_date', date('Y-m-d\TH:i', strtotime($payment->payment_date))) ?>" 
                                           <?= $isReadOnly ? 'readonly' : 'required' ?>>
                                    <?= $this->error('payment_date') ?>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="amount" class="form-label"><?= t('payments.amount') ?> *</label>
                                    <div class="input-group">
                                        <input type="number" class="form-control" id="amount" name="amount" 
                                               value="<?= $this->old('amount', $payment->amount) ?>" 
                                               min="0" step="0.01" <?= $isReadOnly ? 'readonly' : 'required' ?>>
                                        <span class="input-group-text" id="currency-display"><?= $payment->currency ?></span>
                                    </div>
                                    <?= $this->error('amount') ?>
                                    <small class="form-text text-muted" id="outstanding-amount">
                                        <?= t('payments.outstanding_amount') ?>: 
                                        <span class="fw-bold"><?= number_format($payment->invoice_outstanding_amount ?? 0, 2) ?> <?= $payment->currency ?></span>
                                    </small>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="payment_method" class="form-label"><?= t('payments.payment_method') ?> *</label>
                                    <select class="form-select" id="payment_method" name="payment_method" <?= $isReadOnly ? 'disabled' : 'required' ?>>
                                        <option value=""><?= t('payments.select_method') ?></option>
                                        <?php foreach ($payment_methods as $method): ?>
                                        <option value="<?= $method->slug ?>" 
                                                data-requires-reference="<?= $method->requires_reference ? 'true' : 'false' ?>"
                                                <?= $this->selected('payment_method', $method->slug, $payment->payment_method) ?>>
                                            <?= htmlspecialchars($method->name) ?>
                                        </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <?= $this->error('payment_method') ?>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3" id="reference-field" style="<?= empty($payment->reference) ? 'display: none;' : '' ?>">
                                    <label for="reference" class="form-label"><?= t('payments.reference_number') ?></label>
                                    <input type="text" class="form-control" id="reference" name="reference" 
                                           value="<?= $this->old('reference', $payment->reference) ?>" 
                                           placeholder="<?= t('payments.reference_placeholder') ?>"
                                           <?= $isReadOnly ? 'readonly' : '' ?>>
                                    <?= $this->error('reference') ?>
                                    <small class="form-text text-muted"><?= t('payments.reference_help') ?></small>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <?php if ($this->hasRole(['admin', 'manager'])): ?>
                                <div class="mb-3">
                                    <label for="status" class="form-label"><?= t('common.status') ?></label>
                                    <select class="form-select" id="status" name="status" <?= $isReadOnly ? 'disabled' : '' ?>>
                                        <option value="pending" <?= $this->selected('status', 'pending', $payment->status) ?>>
                                            <?= t('payments.status.pending') ?>
                                        </option>
                                        <option value="processing" <?= $this->selected('status', 'processing', $payment->status) ?>>
                                            <?= t('payments.status.processing') ?>
                                        </option>
                                        <option value="completed" <?= $this->selected('status', 'completed', $payment->status) ?>>
                                            <?= t('payments.status.completed') ?>
                                        </option>
                                        <option value="failed" <?= $this->selected('status', 'failed', $payment->status) ?>>
                                            <?= t('payments.status.failed') ?>
                                        </option>
                                        <option value="cancelled" <?= $this->selected('status', 'cancelled', $payment->status) ?>>
                                            <?= t('payments.status.cancelled') ?>
                                        </option>
                                    </select>
                                    <?= $this->error('status') ?>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Transaction Details -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0"><?= t('payments.transaction_details') ?></h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="transaction_id" class="form-label"><?= t('payments.transaction_id') ?></label>
                                    <input type="text" class="form-control" id="transaction_id" name="transaction_id" 
                                           value="<?= $this->old('transaction_id', $payment->transaction_id) ?>" 
                                           placeholder="<?= t('payments.transaction_id_placeholder') ?>"
                                           <?= $isReadOnly ? 'readonly' : '' ?>>
                                    <?= $this->error('transaction_id') ?>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="processor_fee" class="form-label"><?= t('payments.processor_fee') ?></label>
                                    <div class="input-group">
                                        <input type="number" class="form-control" id="processor_fee" name="processor_fee" 
                                               value="<?= $this->old('processor_fee', $payment->processor_fee ?? '0') ?>" 
                                               min="0" step="0.01" <?= $isReadOnly ? 'readonly' : '' ?>>
                                        <span class="input-group-text"><?= $payment->currency ?></span>
                                    </div>
                                    <?= $this->error('processor_fee') ?>
                                    <small class="form-text text-muted"><?= t('payments.processor_fee_help') ?></small>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="notes" class="form-label"><?= t('payments.internal_notes') ?></label>
                            <textarea class="form-control" id="notes" name="notes" rows="3" 
                                      placeholder="<?= t('payments.internal_notes_placeholder') ?>"
                                      <?= $isReadOnly ? 'readonly' : '' ?>><?= $this->old('notes', $payment->notes) ?></textarea>
                            <?= $this->error('notes') ?>
                        </div>
                    </div>
                </div>

                <!-- Refund Information -->
                <?php if ($payment->refund_amount > 0): ?>
                <div class="card mb-4 border-warning">
                    <div class="card-header bg-warning text-dark">
                        <h5 class="mb-0">
                            <i class="fas fa-undo me-2"></i><?= t('payments.refund_information') ?>
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4">
                                <strong><?= t('payments.refund_amount') ?>:</strong><br>
                                <span class="h5 text-warning">
                                    <?= number_format($payment->refund_amount, 2) ?> <?= $payment->currency ?>
                                </span>
                            </div>
                            <div class="col-md-4">
                                <strong><?= t('payments.refund_date') ?>:</strong><br>
                                <?= $payment->refund_date ? date('M d, Y', strtotime($payment->refund_date)) : 'N/A' ?>
                            </div>
                            <div class="col-md-4">
                                <strong><?= t('payments.refund_reason') ?>:</strong><br>
                                <?= htmlspecialchars($payment->refund_reason ?? 'N/A') ?>
                            </div>
                        </div>
                        <?php if ($payment->refund_notes): ?>
                        <hr>
                        <p class="mb-0"><strong><?= t('payments.refund_notes') ?>:</strong></p>
                        <p><?= nl2br(htmlspecialchars($payment->refund_notes)) ?></p>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endif; ?>
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
                            <?= t('payments.editing_disabled') ?>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Payment Information -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0"><?= t('payments.payment_information') ?></h5>
                    </div>
                    <div class="card-body">
                        <table class="table table-sm table-borderless">
                            <tr>
                                <td><strong><?= t('payments.payment_number') ?>:</strong></td>
                                <td><?= htmlspecialchars($payment->payment_number) ?></td>
                            </tr>
                            <tr>
                                <td><strong><?= t('payments.created_by') ?>:</strong></td>
                                <td><?= htmlspecialchars($payment->created_by_name ?? 'System') ?></td>
                            </tr>
                            <tr>
                                <td><strong><?= t('payments.created_at') ?>:</strong></td>
                                <td><?= date('M d, Y H:i', strtotime($payment->created_at)) ?></td>
                            </tr>
                            <tr>
                                <td><strong><?= t('payments.last_updated') ?>:</strong></td>
                                <td><?= date('M d, Y H:i', strtotime($payment->updated_at)) ?></td>
                            </tr>
                            <?php if ($payment->processed_at): ?>
                            <tr>
                                <td><strong><?= t('payments.processed_at') ?>:</strong></td>
                                <td><?= date('M d, Y H:i', strtotime($payment->processed_at)) ?></td>
                            </tr>
                            <?php endif; ?>
                        </table>
                    </div>
                </div>

                <!-- Invoice Information -->
                <div class="card" id="invoice-info">
                    <div class="card-header">
                        <h5 class="mb-0"><?= t('invoices.invoice_information') ?></h5>
                    </div>
                    <div class="card-body">
                        <div id="invoice-details">
                            <?php if ($payment->invoice_number): ?>
                            <div class="mb-2">
                                <strong>
                                    <a href="/invoices/<?= $payment->invoice_id ?>" class="text-decoration-none">
                                        <?= htmlspecialchars($payment->invoice_number) ?>
                                    </a>
                                </strong>
                            </div>
                            <div class="mb-2">
                                <strong><?= t('invoices.client') ?>:</strong><br>
                                <?= htmlspecialchars($payment->client_name ?? 'N/A') ?>
                            </div>
                            <div class="mb-2">
                                <strong><?= t('invoices.invoice_total') ?>:</strong><br>
                                <?= number_format($payment->invoice_amount ?? 0, 2) ?> <?= $payment->currency ?>
                            </div>
                            <div class="mb-2">
                                <strong><?= t('invoices.remaining_balance') ?>:</strong><br>
                                <span class="fw-bold <?= ($payment->invoice_outstanding_amount ?? 0) > 0 ? 'text-warning' : 'text-success' ?>">
                                    <?= number_format($payment->invoice_outstanding_amount ?? 0, 2) ?> <?= $payment->currency ?>
                                </span>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const invoiceSelect = document.getElementById('invoice_id');
    const amountInput = document.getElementById('amount');
    const paymentMethodSelect = document.getElementById('payment_method');
    const referenceField = document.getElementById('reference-field');
    const currencyDisplay = document.getElementById('currency-display');
    const outstandingAmount = document.getElementById('outstanding-amount');
    const form = document.getElementById('editPaymentForm');
    
    const isReadOnly = <?= $isReadOnly ? 'true' : 'false' ?>;
    
    if (!isReadOnly) {
        // Update invoice information when selection changes
        invoiceSelect.addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            if (selectedOption.value) {
                const currency = selectedOption.getAttribute('data-currency');
                const outstanding = parseFloat(selectedOption.getAttribute('data-outstanding'));
                
                currencyDisplay.textContent = currency;
                outstandingAmount.innerHTML = '<?= t('payments.outstanding_amount') ?>: <span class="fw-bold">' + 
                                            outstanding.toFixed(2) + ' ' + currency + '</span>';
                
                // Suggest the outstanding amount as payment amount
                if (!amountInput.value || parseFloat(amountInput.value) === 0) {
                    amountInput.value = outstanding.toFixed(2);
                }
            }
        });
        
        // Toggle reference field based on payment method
        paymentMethodSelect.addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            const requiresReference = selectedOption.getAttribute('data-requires-reference') === 'true';
            
            if (requiresReference) {
                referenceField.style.display = 'block';
                referenceField.querySelector('input').required = true;
            } else {
                referenceField.style.display = 'none';
                referenceField.querySelector('input').required = false;
            }
        });
        
        // Trigger payment method change event on page load
        if (paymentMethodSelect.value) {
            paymentMethodSelect.dispatchEvent(new Event('change'));
        }
        
        // Form validation
        form.addEventListener('submit', function(e) {
            const amount = parseFloat(amountInput.value);
            const selectedInvoice = invoiceSelect.options[invoiceSelect.selectedIndex];
            
            if (selectedInvoice.value) {
                const outstanding = parseFloat(selectedInvoice.getAttribute('data-outstanding'));
                
                if (amount > outstanding) {
                    e.preventDefault();
                    showAlert('warning', '<?= t('payments.amount_exceeds_outstanding') ?>');
                    return false;
                }
            }
            
            return true;
        });
    }
});

function deletePayment(paymentId) {
    if (confirm('<?= t('payments.confirm_delete') ?>')) {
        fetch(`/payments/${paymentId}`, {
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
                window.location.href = '/payments';
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

function initiateRefund(paymentId) {
    // This would typically open a modal or redirect to a refund form
    if (confirm('<?= t('payments.confirm_refund') ?>')) {
        window.location.href = `/payments/${paymentId}/refund`;
    }
}
</script>