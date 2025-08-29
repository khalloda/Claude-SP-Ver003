<?php
/**
 * File: app/views/payments/show.php
 * Purpose: Payment detail view with comprehensive transaction information
 * Layout: Uses app layout with professional payment presentation
 */

$this->layout('layouts/app', [
    'title' => $page_title ?? t('payments.payment_details'),
    'active_nav' => 'payments'
]);

$currentUser = $this->getCurrentUser();
$canEdit = $this->hasRole(['admin', 'manager', 'finance']) && in_array($payment->status, ['pending', 'processing']);
$canRefund = $this->hasRole(['admin', 'manager']) && $payment->status === 'completed' && $payment->refund_amount < $payment->amount;
$canVoid = $this->hasRole(['admin', 'manager']) && in_array($payment->status, ['pending', 'processing']);
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">
            <i class="fas fa-receipt me-2"></i><?= t('payments.payment_details') ?>
            <small class="text-muted ms-2"><?= htmlspecialchars($payment->payment_number) ?></small>
        </h1>
        <div class="btn-toolbar mb-2 mb-md-0">
            <div class="btn-group me-2">
                <a href="/payments/<?= $payment->id ?>/receipt" class="btn btn-outline-primary" target="_blank">
                    <i class="fas fa-print"></i> <?= t('payments.print_receipt') ?>
                </a>
                <button type="button" class="btn btn-outline-secondary dropdown-toggle dropdown-toggle-split" data-bs-toggle="dropdown">
                    <span class="visually-hidden"><?= t('common.actions') ?></span>
                </button>
                <ul class="dropdown-menu">
                    <li><a class="dropdown-item" href="#" onclick="window.print()">
                        <i class="fas fa-print me-2"></i><?= t('common.print') ?>
                    </a></li>
                    <li><a class="dropdown-item" href="/payments/<?= $payment->id ?>/pdf" target="_blank">
                        <i class="fas fa-file-pdf me-2"></i><?= t('common.download_pdf') ?>
                    </a></li>
                    <li><a class="dropdown-item" href="/payments/<?= $payment->id ?>/email">
                        <i class="fas fa-envelope me-2"></i><?= t('payments.email_receipt') ?>
                    </a></li>
                    <li><a class="dropdown-item" href="/payments/<?= $payment->id ?>/duplicate">
                        <i class="fas fa-copy me-2"></i><?= t('common.duplicate') ?>
                    </a></li>
                </ul>
            </div>
            
            <?php if ($canEdit): ?>
            <a href="/payments/<?= $payment->id ?>/edit" class="btn btn-primary me-2">
                <i class="fas fa-edit"></i> <?= t('common.edit') ?>
            </a>
            <?php endif; ?>
            
            <?php if ($canRefund): ?>
            <button class="btn btn-warning me-2" onclick="initiateRefund(<?= $payment->id ?>)">
                <i class="fas fa-undo"></i> <?= t('payments.refund') ?>
            </button>
            <?php endif; ?>
            
            <?php if ($canVoid): ?>
            <button class="btn btn-outline-danger me-2" onclick="voidPayment(<?= $payment->id ?>)">
                <i class="fas fa-ban"></i> <?= t('payments.void') ?>
            </button>
            <?php endif; ?>
            
            <a href="/payments" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left"></i> <?= t('common.back') ?>
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8">
            <!-- Payment Header -->
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><?= t('payments.payment_information') ?></h5>
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
                        
                        <?php if ($payment->is_recurring): ?>
                        <span class="badge bg-primary">
                            <i class="fas fa-redo"></i> <?= t('payments.recurring') ?>
                        </span>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <td><strong><?= t('payments.payment_number') ?>:</strong></td>
                                    <td><?= htmlspecialchars($payment->payment_number) ?></td>
                                </tr>
                                <tr>
                                    <td><strong><?= t('payments.payment_date') ?>:</strong></td>
                                    <td><?= date('M d, Y H:i', strtotime($payment->payment_date)) ?></td>
                                </tr>
                                <tr>
                                    <td><strong><?= t('payments.amount') ?>:</strong></td>
                                    <td class="h5 text-success mb-0">
                                        <?= number_format($payment->amount, 2) ?> <?= $payment->currency ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong><?= t('payments.payment_method') ?>:</strong></td>
                                    <td>
                                        <i class="fas fa-<?= $payment->method_icon ?? 'credit-card' ?> me-2"></i>
                                        <?= htmlspecialchars($payment->payment_method_name ?? $payment->payment_method) ?>
                                    </td>
                                </tr>
                                <?php if ($payment->reference): ?>
                                <tr>
                                    <td><strong><?= t('payments.reference') ?>:</strong></td>
                                    <td>
                                        <code><?= htmlspecialchars($payment->reference) ?></code>
                                    </td>
                                </tr>
                                <?php endif; ?>
                            </table>
                        </div>
                        
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <td><strong><?= t('payments.processed_by') ?>:</strong></td>
                                    <td><?= htmlspecialchars($payment->processed_by_name ?? 'System') ?></td>
                                </tr>
                                <tr>
                                    <td><strong><?= t('payments.created_at') ?>:</strong></td>
                                    <td><?= date('M d, Y H:i', strtotime($payment->created_at)) ?></td>
                                </tr>
                                <?php if ($payment->processed_at): ?>
                                <tr>
                                    <td><strong><?= t('payments.processed_at') ?>:</strong></td>
                                    <td><?= date('M d, Y H:i', strtotime($payment->processed_at)) ?></td>
                                </tr>
                                <?php endif; ?>
                                <?php if ($payment->transaction_id): ?>
                                <tr>
                                    <td><strong><?= t('payments.transaction_id') ?>:</strong></td>
                                    <td>
                                        <code><?= htmlspecialchars($payment->transaction_id) ?></code>
                                    </td>
                                </tr>
                                <?php endif; ?>
                                <?php if ($payment->processor_fee > 0): ?>
                                <tr>
                                    <td><strong><?= t('payments.processor_fee') ?>:</strong></td>
                                    <td class="text-muted">
                                        -<?= number_format($payment->processor_fee, 2) ?> <?= $payment->currency ?>
                                    </td>
                                </tr>
                                <?php endif; ?>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Invoice Information -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><?= t('invoices.related_invoice') ?></h5>
                </div>
                <div class="card-body">
                    <?php if ($payment->invoice_id): ?>
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h6 class="mb-1">
                                <a href="/invoices/<?= $payment->invoice_id ?>" class="text-decoration-none">
                                    <i class="fas fa-file-invoice me-2"></i>
                                    <?= htmlspecialchars($payment->invoice_number) ?>
                                </a>
                            </h6>
                            <p class="text-muted mb-2">
                                <?= t('invoices.client') ?>: <?= htmlspecialchars($payment->client_name) ?>
                            </p>
                            <p class="mb-0">
                                <?= t('invoices.invoice_date') ?>: <?= date('M d, Y', strtotime($payment->invoice_date)) ?>
                            </p>
                        </div>
                        <div class="col-md-4">
                            <div class="row text-center">
                                <div class="col-6">
                                    <div class="border rounded p-2">
                                        <div class="h6 mb-0"><?= number_format($payment->invoice_total, 2) ?></div>
                                        <small class="text-muted"><?= t('invoices.total') ?></small>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="border rounded p-2">
                                        <div class="h6 mb-0 <?= $payment->invoice_outstanding > 0 ? 'text-warning' : 'text-success' ?>">
                                            <?= number_format($payment->invoice_outstanding ?? 0, 2) ?>
                                        </div>
                                        <small class="text-muted"><?= t('invoices.outstanding') ?></small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php else: ?>
                    <div class="text-center text-muted py-4">
                        <i class="fas fa-info-circle fa-2x mb-2"></i>
                        <p><?= t('payments.no_associated_invoice') ?></p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Payment History -->
            <?php if (!empty($payment->history)): ?>
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><?= t('payments.payment_history') ?></h5>
                </div>
                <div class="card-body">
                    <div class="timeline">
                        <?php foreach ($payment->history as $event): ?>
                        <div class="timeline-item">
                            <div class="timeline-marker bg-<?= $event->status_color ?? 'primary' ?>">
                                <i class="fas fa-<?= $event->icon ?? 'circle' ?>"></i>
                            </div>
                            <div class="timeline-content">
                                <h6 class="timeline-title"><?= htmlspecialchars($event->title) ?></h6>
                                <p class="timeline-description text-muted">
                                    <?= htmlspecialchars($event->description) ?>
                                </p>
                                <small class="timeline-date text-muted">
                                    <i class="fas fa-clock me-1"></i>
                                    <?= date('M d, Y H:i', strtotime($event->created_at)) ?>
                                    <?php if ($event->user_name): ?>
                                    - <?= htmlspecialchars($event->user_name) ?>
                                    <?php endif; ?>
                                </small>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- Refund Information -->
            <?php if ($payment->refund_amount > 0): ?>
            <div class="card mb-4 border-warning">
                <div class="card-header bg-warning text-dark">
                    <h5 class="mb-0">
                        <i class="fas fa-undo me-2"></i><?= t('payments.refund_details') ?>
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="text-center border rounded p-3">
                                <div class="h4 text-warning mb-1">
                                    <?= number_format($payment->refund_amount, 2) ?>
                                </div>
                                <small class="text-muted"><?= t('payments.refund_amount') ?></small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-center border rounded p-3">
                                <div class="h4 text-success mb-1">
                                    <?= number_format($payment->amount - $payment->refund_amount, 2) ?>
                                </div>
                                <small class="text-muted"><?= t('payments.net_amount') ?></small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-sm table-borderless">
                                <tr>
                                    <td><strong><?= t('payments.refund_date') ?>:</strong></td>
                                    <td><?= date('M d, Y', strtotime($payment->refund_date)) ?></td>
                                </tr>
                                <tr>
                                    <td><strong><?= t('payments.refund_reason') ?>:</strong></td>
                                    <td><?= htmlspecialchars($payment->refund_reason) ?></td>
                                </tr>
                                <tr>
                                    <td><strong><?= t('payments.refunded_by') ?>:</strong></td>
                                    <td><?= htmlspecialchars($payment->refunded_by_name) ?></td>
                                </tr>
                            </table>
                        </div>
                    </div>
                    <?php if ($payment->refund_notes): ?>
                    <hr>
                    <div>
                        <strong><?= t('payments.refund_notes') ?>:</strong>
                        <p class="mb-0 mt-2"><?= nl2br(htmlspecialchars($payment->refund_notes)) ?></p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- Internal Notes -->
            <?php if ($payment->notes): ?>
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><?= t('payments.internal_notes') ?></h5>
                </div>
                <div class="card-body">
                    <p class="mb-0"><?= nl2br(htmlspecialchars($payment->notes)) ?></p>
                </div>
            </div>
            <?php endif; ?>
        </div>
        
        <div class="col-md-4">
            <!-- Payment Summary -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><?= t('payments.payment_summary') ?></h5>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-6 text-center">
                            <div class="border rounded p-3">
                                <div class="h4 mb-1"><?= number_format($payment->amount, 2) ?></div>
                                <small class="text-muted"><?= t('payments.paid_amount') ?></small>
                            </div>
                        </div>
                        <div class="col-6 text-center">
                            <div class="border rounded p-3">
                                <div class="h4 mb-1 <?= $payment->processor_fee > 0 ? 'text-warning' : 'text-success' ?>">
                                    <?= number_format($payment->amount - ($payment->processor_fee ?? 0), 2) ?>
                                </div>
                                <small class="text-muted"><?= t('payments.net_received') ?></small>
                            </div>
                        </div>
                    </div>
                    
                    <?php if ($payment->processor_fee > 0): ?>
                    <div class="alert alert-light border">
                        <div class="d-flex justify-content-between">
                            <span><?= t('payments.processing_fee') ?>:</span>
                            <span class="text-muted">-<?= number_format($payment->processor_fee, 2) ?> <?= $payment->currency ?></span>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Client Information -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><?= t('clients.client_information') ?></h5>
                </div>
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        <div class="user-avatar bg-primary text-white rounded-circle me-3 d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                            <?= strtoupper(substr($payment->client_name, 0, 1)) ?>
                        </div>
                        <div>
                            <h6 class="mb-0">
                                <a href="/clients/<?= $payment->client_id ?>" class="text-decoration-none">
                                    <?= htmlspecialchars($payment->client_name) ?>
                                </a>
                            </h6>
                            <small class="text-muted"><?= t('clients.type.' . ($payment->client_type ?? 'individual')) ?></small>
                        </div>
                    </div>
                    
                    <?php if ($payment->client_email): ?>
                    <div class="mb-2">
                        <i class="fas fa-envelope text-muted me-2"></i>
                        <a href="mailto:<?= htmlspecialchars($payment->client_email) ?>"><?= htmlspecialchars($payment->client_email) ?></a>
                    </div>
                    <?php endif; ?>
                    
                    <?php if ($payment->client_phone): ?>
                    <div class="mb-2">
                        <i class="fas fa-phone text-muted me-2"></i>
                        <a href="tel:<?= htmlspecialchars($payment->client_phone) ?>"><?= htmlspecialchars($payment->client_phone) ?></a>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><?= t('common.quick_actions') ?></h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <?php if ($payment->invoice_id): ?>
                        <a href="/invoices/<?= $payment->invoice_id ?>" class="btn btn-outline-primary btn-sm">
                            <i class="fas fa-file-invoice me-2"></i><?= t('payments.view_invoice') ?>
                        </a>
                        <?php endif; ?>
                        
                        <a href="/payments/<?= $payment->id ?>/receipt" class="btn btn-outline-success btn-sm" target="_blank">
                            <i class="fas fa-receipt me-2"></i><?= t('payments.download_receipt') ?>
                        </a>
                        
                        <?php if ($payment->client_email): ?>
                        <button class="btn btn-outline-info btn-sm" onclick="emailReceipt(<?= $payment->id ?>)">
                            <i class="fas fa-envelope me-2"></i><?= t('payments.email_receipt') ?>
                        </button>
                        <?php endif; ?>
                        
                        <?php if ($canRefund): ?>
                        <hr class="my-2">
                        <button class="btn btn-outline-warning btn-sm" onclick="initiateRefund(<?= $payment->id ?>)">
                            <i class="fas fa-undo me-2"></i><?= t('payments.process_refund') ?>
                        </button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Related Payments -->
            <?php if (!empty($payment->related_payments)): ?>
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><?= t('payments.related_payments') ?></h5>
                </div>
                <div class="card-body">
                    <?php foreach ($payment->related_payments as $related): ?>
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <div>
                            <a href="/payments/<?= $related->id ?>" class="fw-bold text-decoration-none">
                                <?= htmlspecialchars($related->payment_number) ?>
                            </a>
                            <br><small class="text-muted">
                                <?= number_format($related->amount, 2) ?> <?= $related->currency ?> - 
                                <?= date('M d, Y', strtotime($related->payment_date)) ?>
                            </small>
                        </div>
                        <span class="badge bg-<?= $related->status === 'completed' ? 'success' : 'warning' ?>">
                            <?= t('payments.status.' . $related->status) ?>
                        </span>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
function initiateRefund(paymentId) {
    if (confirm('<?= t('payments.confirm_refund_process') ?>')) {
        // This would typically open a refund modal or redirect to refund form
        window.location.href = `/payments/${paymentId}/refund`;
    }
}

function voidPayment(paymentId) {
    if (confirm('<?= t('payments.confirm_void') ?>')) {
        fetch(`/payments/${paymentId}/void`, {
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

function emailReceipt(paymentId) {
    const btn = event.target;
    const originalText = btn.innerHTML;
    
    btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i><?= t('payments.sending') ?>';
    btn.disabled = true;
    
    fetch(`/payments/${paymentId}/email-receipt`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert('success', data.message || '<?= t('payments.receipt_sent') ?>');
        } else {
            showAlert('error', data.message || '<?= t('messages.error.general') ?>');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('error', '<?= t('messages.error.general') ?>');
    })
    .finally(() => {
        btn.innerHTML = originalText;
        btn.disabled = false;
    });
}
</script>

<style>
.timeline {
    position: relative;
    padding: 0;
    margin: 0;
}

.timeline-item {
    position: relative;
    padding-left: 3rem;
    margin-bottom: 2rem;
}

.timeline-item:not(:last-child)::before {
    content: '';
    position: absolute;
    left: 1.125rem;
    top: 2.5rem;
    width: 2px;
    height: calc(100% - 1rem);
    background: #dee2e6;
}

.timeline-marker {
    position: absolute;
    left: 0;
    top: 0;
    width: 2.25rem;
    height: 2.25rem;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    z-index: 1;
}

.timeline-content {
    background: #f8f9fa;
    border-radius: 0.375rem;
    padding: 1rem;
    border-left: 3px solid #dee2e6;
}

.timeline-title {
    margin-bottom: 0.5rem;
    color: #495057;
}

.timeline-description {
    margin-bottom: 0.5rem;
    line-height: 1.4;
}

.timeline-date {
    display: block;
}

.user-avatar {
    font-weight: bold;
    font-size: 1.25rem;
}

@media (max-width: 768px) {
    .timeline-item {
        padding-left: 2.5rem;
    }
    
    .timeline-marker {
        width: 1.75rem;
        height: 1.75rem;
    }
    
    .timeline-item:not(:last-child)::before {
        left: 0.875rem;
    }
}
</style>