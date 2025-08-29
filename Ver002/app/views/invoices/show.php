<?php
/**
 * File: app/views/invoices/show.php
 * Purpose: Invoice detail view with comprehensive payment tracking
 * Layout: Uses app layout with professional invoice presentation
 */

$this->layout('layouts/app', [
    'title' => $page_title ?? t('invoices.invoice_details'),
    'active_nav' => 'invoices'
]);

$currentUser = $this->getCurrentUser();
$canEdit = $this->hasRole(['admin', 'manager', 'accounting']) && in_array($invoice->status, ['draft', 'sent']);
$canSend = $this->hasRole(['admin', 'manager', 'accounting', 'sales']) && $invoice->status === 'draft';
$canRecord = $this->hasRole(['admin', 'manager', 'accounting']) && !in_array($invoice->status, ['paid', 'cancelled']);
$canCancel = $this->hasRole(['admin', 'manager']) && !in_array($invoice->status, ['paid', 'cancelled']);
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">
            <i class="fas fa-file-invoice me-2"></i><?= t('invoices.invoice_details') ?>
            <small class="text-muted ms-2"><?= htmlspecialchars($invoice->invoice_number) ?></small>
        </h1>
        <div class="btn-toolbar mb-2 mb-md-0">
            <div class="btn-group me-2">
                <a href="/invoices/<?= $invoice->id ?>/pdf" class="btn btn-outline-primary" target="_blank">
                    <i class="fas fa-file-pdf"></i> <?= t('common.download_pdf') ?>
                </a>
                <button type="button" class="btn btn-outline-secondary dropdown-toggle dropdown-toggle-split" data-bs-toggle="dropdown">
                    <span class="visually-hidden"><?= t('common.actions') ?></span>
                </button>
                <ul class="dropdown-menu">
                    <li><a class="dropdown-item" href="#" onclick="window.print()">
                        <i class="fas fa-print me-2"></i><?= t('common.print') ?>
                    </a></li>
                    <li><a class="dropdown-item" href="/invoices/<?= $invoice->id ?>/email">
                        <i class="fas fa-envelope me-2"></i><?= t('invoices.email_invoice') ?>
                    </a></li>
                    <li><a class="dropdown-item" href="/invoices/<?= $invoice->id ?>/duplicate">
                        <i class="fas fa-copy me-2"></i><?= t('common.duplicate') ?>
                    </a></li>
                    <li><hr class="dropdown-divider"></li>
                    <?php if ($canRecord): ?>
                    <li><a class="dropdown-item" href="#" onclick="showPaymentModal(<?= $invoice->id ?>)">
                        <i class="fas fa-credit-card me-2"></i><?= t('invoices.record_payment') ?>
                    </a></li>
                    <?php endif; ?>
                    <li><a class="dropdown-item" href="/invoices/<?= $invoice->id ?>/statement">
                        <i class="fas fa-file-alt me-2"></i><?= t('invoices.payment_statement') ?>
                    </a></li>
                </ul>
            </div>
            
            <?php if ($canRecord): ?>
            <button class="btn btn-success me-2" onclick="showPaymentModal(<?= $invoice->id ?>)">
                <i class="fas fa-credit-card"></i> <?= t('invoices.record_payment') ?>
            </button>
            <?php endif; ?>
            
            <?php if ($canSend): ?>
            <button class="btn btn-primary me-2" onclick="sendInvoice(<?= $invoice->id ?>)">
                <i class="fas fa-paper-plane"></i> <?= t('invoices.send_invoice') ?>
            </button>
            <?php endif; ?>
            
            <?php if ($canEdit): ?>
            <a href="/invoices/<?= $invoice->id ?>/edit" class="btn btn-outline-primary me-2">
                <i class="fas fa-edit"></i> <?= t('common.edit') ?>
            </a>
            <?php endif; ?>
            
            <a href="/invoices" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left"></i> <?= t('common.back') ?>
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8">
            <!-- Invoice Header -->
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><?= t('invoices.invoice_information') ?></h5>
                    <div>
                        <?php 
                        $statusClass = match($invoice->status) {
                            'draft' => 'bg-secondary',
                            'sent' => 'bg-info',
                            'viewed' => 'bg-primary',
                            'partial' => 'bg-warning text-dark',
                            'paid' => 'bg-success',
                            'overdue' => 'bg-danger',
                            'cancelled' => 'bg-dark',
                            default => 'bg-secondary'
                        };
                        ?>
                        <span class="badge <?= $statusClass ?> me-2">
                            <?= t('invoices.status.' . $invoice->status) ?>
                        </span>
                        
                        <?php if ($invoice->status === 'overdue'): ?>
                        <span class="badge bg-danger">
                            <i class="fas fa-exclamation-triangle"></i> 
                            <?= abs((strtotime($invoice->due_date) - time()) / 86400) ?> <?= t('invoices.days_overdue') ?>
                        </span>
                        <?php elseif ($invoice->due_date && strtotime($invoice->due_date) - time() < 7 * 86400 && $invoice->status !== 'paid'): ?>
                        <span class="badge bg-warning text-dark">
                            <i class="fas fa-clock"></i> <?= t('invoices.due_soon') ?>
                        </span>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <td><strong><?= t('invoices.invoice_number') ?>:</strong></td>
                                    <td><?= htmlspecialchars($invoice->invoice_number) ?></td>
                                </tr>
                                <tr>
                                    <td><strong><?= t('invoices.invoice_date') ?>:</strong></td>
                                    <td><?= date('M d, Y', strtotime($invoice->invoice_date)) ?></td>
                                </tr>
                                <tr>
                                    <td><strong><?= t('invoices.due_date') ?>:</strong></td>
                                    <td>
                                        <?= date('M d, Y', strtotime($invoice->due_date)) ?>
                                        <?php if (strtotime($invoice->due_date) < time() && $invoice->status !== 'paid'): ?>
                                        <span class="badge bg-danger ms-2"><?= t('invoices.overdue') ?></span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php if ($invoice->po_number): ?>
                                <tr>
                                    <td><strong><?= t('invoices.po_number') ?>:</strong></td>
                                    <td><?= htmlspecialchars($invoice->po_number) ?></td>
                                </tr>
                                <?php endif; ?>
                                <?php if ($invoice->sales_order_id): ?>
                                <tr>
                                    <td><strong><?= t('invoices.source_order') ?>:</strong></td>
                                    <td>
                                        <a href="/sales-orders/<?= $invoice->sales_order_id ?>" class="text-decoration-none">
                                            <?= htmlspecialchars($invoice->order_number ?? 'Order #' . $invoice->sales_order_id) ?>
                                        </a>
                                    </td>
                                </tr>
                                <?php endif; ?>
                            </table>
                        </div>
                        
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <td><strong><?= t('invoices.created_by') ?>:</strong></td>
                                    <td><?= htmlspecialchars($invoice->created_by_name ?? 'System') ?></td>
                                </tr>
                                <tr>
                                    <td><strong><?= t('invoices.payment_terms') ?>:</strong></td>
                                    <td><?= htmlspecialchars($invoice->payment_terms ?? t('invoices.net_30')) ?></td>
                                </tr>
                                <tr>
                                    <td><strong><?= t('invoices.created_at') ?>:</strong></td>
                                    <td><?= date('M d, Y H:i', strtotime($invoice->created_at)) ?></td>
                                </tr>
                                <tr>
                                    <td><strong><?= t('invoices.last_updated') ?>:</strong></td>
                                    <td><?= date('M d, Y H:i', strtotime($invoice->updated_at)) ?></td>
                                </tr>
                                <tr>
                                    <td><strong><?= t('common.currency') ?>:</strong></td>
                                    <td><?= htmlspecialchars($invoice->currency) ?></td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Invoice Items -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><?= t('invoices.invoice_items') ?></h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th><?= t('products.product') ?></th>
                                    <th class="text-center"><?= t('invoices.quantity') ?></th>
                                    <th class="text-end"><?= t('invoices.unit_price') ?></th>
                                    <th class="text-end"><?= t('invoices.total') ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($invoice->items)): ?>
                                    <?php foreach ($invoice->items as $item): ?>
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <?php if ($item->product_image): ?>
                                                <img src="<?= htmlspecialchars($item->product_image) ?>" alt="" class="rounded me-2" style="width: 40px; height: 40px; object-fit: cover;">
                                                <?php else: ?>
                                                <div class="bg-light rounded me-2 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                                    <i class="fas fa-box text-muted"></i>
                                                </div>
                                                <?php endif; ?>
                                                <div>
                                                    <strong><?= htmlspecialchars($item->product_name) ?></strong>
                                                    <br><small class="text-muted"><?= htmlspecialchars($item->product_sku) ?></small>
                                                    <?php if ($item->description): ?>
                                                    <br><small class="text-muted"><?= htmlspecialchars($item->description) ?></small>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="text-center">
                                            <?= number_format($item->quantity, 2) ?>
                                            <?php if ($item->unit): ?>
                                            <br><small class="text-muted"><?= htmlspecialchars($item->unit) ?></small>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-end">
                                            <?= number_format($item->unit_price, 2) ?> <?= $invoice->currency ?>
                                        </td>
                                        <td class="text-end">
                                            <strong><?= number_format($item->total_price, 2) ?> <?= $invoice->currency ?></strong>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="4" class="text-center text-muted py-4">
                                            <?= t('invoices.no_items') ?>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Totals -->
                    <div class="row">
                        <div class="col-md-8"></div>
                        <div class="col-md-4">
                            <table class="table table-sm">
                                <tr>
                                    <td><?= t('invoices.subtotal') ?>:</td>
                                    <td class="text-end"><?= number_format($invoice->subtotal, 2) ?> <?= $invoice->currency ?></td>
                                </tr>
                                <?php if ($invoice->discount_rate > 0): ?>
                                <tr>
                                    <td><?= t('invoices.discount') ?> (<?= $invoice->discount_rate ?>%):</td>
                                    <td class="text-end">-<?= number_format($invoice->discount_amount, 2) ?> <?= $invoice->currency ?></td>
                                </tr>
                                <?php endif; ?>
                                <?php if ($invoice->tax_rate > 0): ?>
                                <tr>
                                    <td><?= t('invoices.tax') ?> (<?= $invoice->tax_rate ?>%):</td>
                                    <td class="text-end"><?= number_format($invoice->tax_amount, 2) ?> <?= $invoice->currency ?></td>
                                </tr>
                                <?php endif; ?>
                                <tr class="table-primary">
                                    <td><strong><?= t('invoices.total_amount') ?>:</strong></td>
                                    <td class="text-end"><strong><?= number_format($invoice->total_amount, 2) ?> <?= $invoice->currency ?></strong></td>
                                </tr>
                                <?php if ($invoice->paid_amount > 0): ?>
                                <tr class="table-success">
                                    <td><strong><?= t('invoices.paid_amount') ?>:</strong></td>
                                    <td class="text-end"><strong>-<?= number_format($invoice->paid_amount, 2) ?> <?= $invoice->currency ?></strong></td>
                                </tr>
                                <tr class="table-warning">
                                    <td><strong><?= t('invoices.balance_due') ?>:</strong></td>
                                    <td class="text-end"><strong><?= number_format($invoice->total_amount - $invoice->paid_amount, 2) ?> <?= $invoice->currency ?></strong></td>
                                </tr>
                                <?php endif; ?>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Notes and Terms -->
            <?php if ($invoice->notes || $invoice->terms_conditions): ?>
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><?= t('invoices.notes_terms') ?></h5>
                </div>
                <div class="card-body">
                    <?php if ($invoice->notes): ?>
                    <div class="mb-3">
                        <h6><?= t('invoices.internal_notes') ?></h6>
                        <p class="text-muted"><?= nl2br(htmlspecialchars($invoice->notes)) ?></p>
                    </div>
                    <?php endif; ?>
                    
                    <?php if ($invoice->terms_conditions): ?>
                    <div class="mb-3">
                        <h6><?= t('invoices.terms_conditions') ?></h6>
                        <p><?= nl2br(htmlspecialchars($invoice->terms_conditions)) ?></p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>
        
        <div class="col-md-4">
            <!-- Client Information -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><?= t('clients.client_information') ?></h5>
                </div>
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        <div class="user-avatar bg-primary text-white rounded-circle me-3 d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                            <?= strtoupper(substr($invoice->client_name, 0, 1)) ?>
                        </div>
                        <div>
                            <h6 class="mb-0">
                                <a href="/clients/<?= $invoice->client_id ?>" class="text-decoration-none">
                                    <?= htmlspecialchars($invoice->client_name) ?>
                                </a>
                            </h6>
                            <small class="text-muted"><?= t('clients.type.' . ($invoice->client_type ?? 'individual')) ?></small>
                        </div>
                    </div>
                    
                    <?php if ($invoice->client_email): ?>
                    <div class="mb-2">
                        <i class="fas fa-envelope text-muted me-2"></i>
                        <a href="mailto:<?= htmlspecialchars($invoice->client_email) ?>"><?= htmlspecialchars($invoice->client_email) ?></a>
                    </div>
                    <?php endif; ?>
                    
                    <?php if ($invoice->client_phone): ?>
                    <div class="mb-2">
                        <i class="fas fa-phone text-muted me-2"></i>
                        <a href="tel:<?= htmlspecialchars($invoice->client_phone) ?>"><?= htmlspecialchars($invoice->client_phone) ?></a>
                    </div>
                    <?php endif; ?>
                    
                    <?php if ($invoice->billing_address): ?>
                    <div class="mb-2">
                        <i class="fas fa-map-marker-alt text-muted me-2"></i>
                        <strong><?= t('invoices.billing_address') ?>:</strong><br>
                        <?= nl2br(htmlspecialchars($invoice->billing_address)) ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Payment Summary -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><?= t('invoices.payment_summary') ?></h5>
                </div>
                <div class="card-body">
                    <?php
                    $balanceDue = $invoice->total_amount - ($invoice->paid_amount ?? 0);
                    $paymentProgress = $invoice->total_amount > 0 ? (($invoice->paid_amount ?? 0) / $invoice->total_amount) * 100 : 0;
                    ?>
                    
                    <div class="row text-center mb-3">
                        <div class="col-4">
                            <div class="border rounded p-2">
                                <div class="h6 mb-0 text-primary"><?= number_format($invoice->total_amount, 0) ?></div>
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

            <!-- Payment History -->
            <?php if (!empty($invoice->payments)): ?>
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><?= t('invoices.payment_history') ?></h5>
                </div>
                <div class="card-body">
                    <?php foreach ($invoice->payments as $payment): ?>
                    <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom">
                        <div>
                            <div class="fw-bold"><?= number_format($payment->amount, 2) ?> <?= $invoice->currency ?></div>
                            <small class="text-muted"><?= htmlspecialchars($payment->payment_method) ?></small>
                            <br><small class="text-muted"><?= date('M d, Y', strtotime($payment->payment_date)) ?></small>
                            <?php if ($payment->reference): ?>
                            <br><small class="text-muted">Ref: <?= htmlspecialchars($payment->reference) ?></small>
                            <?php endif; ?>
                        </div>
                        <span class="badge bg-success">
                            <i class="fas fa-check"></i>
                        </span>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- Activity Log -->
            <?php if (!empty($invoice->activity_log)): ?>
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><?= t('invoices.activity_log') ?></h5>
                </div>
                <div class="card-body">
                    <?php foreach ($invoice->activity_log as $activity): ?>
                    <div class="d-flex mb-3">
                        <div class="flex-shrink-0 me-3">
                            <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                                <i class="fas fa-<?= $activity->icon ?? 'circle' ?> text-white" style="font-size: 0.8rem;"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1">
                            <div class="fw-bold"><?= htmlspecialchars($activity->title) ?></div>
                            <div class="small text-muted"><?= htmlspecialchars($activity->description) ?></div>
                            <div class="small text-muted"><?= date('M d, Y H:i', strtotime($activity->created_at)) ?></div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Payment Modal -->
<div class="modal fade" id="paymentModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><?= t('invoices.record_payment') ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="paymentForm">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label"><?= t('invoices.payment_amount') ?> *</label>
                        <div class="input-group">
                            <input type="number" class="form-control" id="payment_amount" step="0.01" max="<?= $invoice->total_amount - ($invoice->paid_amount ?? 0) ?>" required>
                            <span class="input-group-text"><?= $invoice->currency ?></span>
                        </div>
                        <div class="form-text"><?= t('invoices.balance_due') ?>: <?= number_format($invoice->total_amount - ($invoice->paid_amount ?? 0), 2) ?> <?= $invoice->currency ?></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label"><?= t('invoices.payment_date') ?> *</label>
                        <input type="date" class="form-control" id="payment_date" value="<?= date('Y-m-d') ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label"><?= t('invoices.payment_method') ?> *</label>
                        <select class="form-select" id="payment_method" required>
                            <option value=""><?= t('common.select') ?></option>
                            <option value="cash"><?= t('invoices.cash') ?></option>
                            <option value="check"><?= t('invoices.check') ?></option>
                            <option value="bank_transfer"><?= t('invoices.bank_transfer') ?></option>
                            <option value="credit_card"><?= t('invoices.credit_card') ?></option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label"><?= t('invoices.reference') ?></label>
                        <input type="text" class="form-control" id="reference" placeholder="<?= t('invoices.reference_placeholder') ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label"><?= t('common.notes') ?></label>
                        <textarea class="form-control" id="payment_notes" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><?= t('common.cancel') ?></button>
                    <button type="submit" class="btn btn-success"><?= t('invoices.record_payment') ?></button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function sendInvoice(invoiceId) {
    if (confirm('<?= t('invoices.confirm_send') ?>')) {
        fetch(`/invoices/${invoiceId}/send`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showAlert('success', data.message);
                location.reload();
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

function showPaymentModal(invoiceId) {
    const modal = new bootstrap.Modal(document.getElementById('paymentModal'));
    modal.show();
    
    document.getElementById('paymentForm').onsubmit = function(e) {
        e.preventDefault();
        recordPayment(invoiceId);
    };
}

function recordPayment(invoiceId) {
    const formData = {
        amount: document.getElementById('payment_amount').value,
        payment_date: document.getElementById('payment_date').value,
        payment_method: document.getElementById('payment_method').value,
        reference: document.getElementById('reference').value,
        notes: document.getElementById('payment_notes').value
    };
    
    fetch(`/invoices/${invoiceId}/payments`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify(formData)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert('success', data.message);
            bootstrap.Modal.getInstance(document.getElementById('paymentModal')).hide();
            location.reload();
        } else {
            showAlert('error', data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('error', '<?= t('messages.error.general') ?>');
    });
}
</script>