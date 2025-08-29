<?php
/**
 * File: app/views/payments/index.php
 * Purpose: Payments listing page with financial tracking
 * Layout: Uses app layout with payment status and reconciliation features
 */

$this->layout('layouts/app', [
    'title' => $page_title ?? t('nav.payments'),
    'active_nav' => 'payments'
]);

$currentUser = $this->getCurrentUser();
$canCreate = $this->hasRole(['admin', 'manager', 'accounting']);
$canEdit = $this->hasRole(['admin', 'manager', 'accounting']);
$canDelete = $this->hasRole(['admin', 'manager']);
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">
            <i class="fas fa-money-bill-wave me-2"></i><?= t('nav.payments') ?>
            <span class="badge bg-secondary ms-2"><?= count($payments ?? []) ?></span>
        </h1>
        <div class="btn-toolbar mb-2 mb-md-0">
            <div class="btn-group me-2">
                <button type="button" class="btn btn-sm btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown">
                    <i class="fas fa-download"></i> <?= t('common.export') ?>
                </button>
                <ul class="dropdown-menu">
                    <li><a class="dropdown-item" href="/payments/export?format=csv"><i class="fas fa-file-csv me-2"></i>CSV</a></li>
                    <li><a class="dropdown-item" href="/payments/export?format=excel"><i class="fas fa-file-excel me-2"></i>Excel</a></li>
                    <li><a class="dropdown-item" href="/payments/reconciliation"><i class="fas fa-balance-scale me-2"></i><?= t('payments.reconciliation') ?></a></li>
                </ul>
            </div>
            <?php if ($canCreate): ?>
            <a href="/payments/create" class="btn btn-primary">
                <i class="fas fa-plus"></i> <?= t('payments.record_payment') ?>
            </a>
            <?php endif; ?>
        </div>
    </div>

    <!-- Financial Summary Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <div class="h4"><?= number_format($summary['total_received'] ?? 0, 0) ?></div>
                            <div class="small"><?= t('payments.total_received') ?></div>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-arrow-down fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <div class="h4"><?= number_format($summary['total_pending'] ?? 0, 0) ?></div>
                            <div class="small"><?= t('payments.total_pending') ?></div>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-hourglass-half fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <div class="h4"><?= number_format($summary['failed_count'] ?? 0) ?></div>
                            <div class="small"><?= t('payments.failed_payments') ?></div>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-exclamation-triangle fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <div class="h4"><?= number_format($summary['this_month'] ?? 0, 0) ?></div>
                            <div class="small"><?= t('payments.this_month') ?></div>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-calendar fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="/payments" class="row g-3">
                <div class="col-md-3">
                    <label for="search" class="form-label"><?= t('common.search') ?></label>
                    <input type="text" class="form-control" id="search" name="search" 
                           value="<?= htmlspecialchars($search ?? '') ?>" 
                           placeholder="<?= t('payments.search_placeholder') ?>">
                </div>
                
                <div class="col-md-2">
                    <label for="status" class="form-label"><?= t('common.status') ?></label>
                    <select class="form-select" id="status" name="status">
                        <option value=""><?= t('common.all_statuses') ?></option>
                        <option value="pending" <?= ($status ?? '') === 'pending' ? 'selected' : '' ?>><?= t('payments.status.pending') ?></option>
                        <option value="completed" <?= ($status ?? '') === 'completed' ? 'selected' : '' ?>><?= t('payments.status.completed') ?></option>
                        <option value="failed" <?= ($status ?? '') === 'failed' ? 'selected' : '' ?>><?= t('payments.status.failed') ?></option>
                        <option value="refunded" <?= ($status ?? '') === 'refunded' ? 'selected' : '' ?>><?= t('payments.status.refunded') ?></option>
                    </select>
                </div>
                
                <div class="col-md-2">
                    <label for="payment_method" class="form-label"><?= t('payments.method') ?></label>
                    <select class="form-select" id="payment_method" name="payment_method">
                        <option value=""><?= t('payments.all_methods') ?></option>
                        <option value="cash" <?= ($payment_method ?? '') === 'cash' ? 'selected' : '' ?>><?= t('payments.method.cash') ?></option>
                        <option value="credit_card" <?= ($payment_method ?? '') === 'credit_card' ? 'selected' : '' ?>><?= t('payments.method.credit_card') ?></option>
                        <option value="bank_transfer" <?= ($payment_method ?? '') === 'bank_transfer' ? 'selected' : '' ?>><?= t('payments.method.bank_transfer') ?></option>
                        <option value="check" <?= ($payment_method ?? '') === 'check' ? 'selected' : '' ?>><?= t('payments.method.check') ?></option>
                    </select>
                </div>
                
                <div class="col-md-2">
                    <label for="date_from" class="form-label"><?= t('common.date_from') ?></label>
                    <input type="date" class="form-control" id="date_from" name="date_from" 
                           value="<?= htmlspecialchars($date_from ?? '') ?>">
                </div>
                
                <div class="col-md-2">
                    <label for="date_to" class="form-label"><?= t('common.date_to') ?></label>
                    <input type="date" class="form-control" id="date_to" name="date_to" 
                           value="<?= htmlspecialchars($date_to ?? '') ?>">
                </div>
                
                <div class="col-md-1 d-flex align-items-end">
                    <button type="submit" class="btn btn-outline-primary me-2">
                        <i class="fas fa-filter"></i>
                    </button>
                    <a href="/payments" class="btn btn-outline-secondary">
                        <i class="fas fa-times"></i>
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Payments Table -->
    <div class="card">
        <div class="card-body">
            <?php if (empty($payments)): ?>
                <div class="text-center py-5">
                    <i class="fas fa-money-bill-wave fa-4x text-muted mb-4"></i>
                    <h4><?= t('payments.no_payments_found') ?></h4>
                    <p class="text-muted"><?= t('payments.no_payments_desc') ?></p>
                    <?php if ($canCreate): ?>
                    <a href="/payments/create" class="btn btn-primary">
                        <i class="fas fa-plus"></i> <?= t('payments.record_first_payment') ?>
                    </a>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th><?= t('payments.payment_id') ?></th>
                                <th><?= t('payments.invoice') ?></th>
                                <th><?= t('clients.client') ?></th>
                                <th><?= t('payments.payment_date') ?></th>
                                <th><?= t('payments.amount') ?></th>
                                <th><?= t('payments.method') ?></th>
                                <th><?= t('common.status') ?></th>
                                <th class="text-end"><?= t('common.actions') ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($payments as $payment): ?>
                            <tr class="<?= ($payment->status === 'failed' ? 'table-danger' : '') ?>">
                                <td>
                                    <strong><?= htmlspecialchars($payment->payment_number ?? $payment->id) ?></strong>
                                    <?php if ($payment->transaction_id): ?>
                                    <br><small class="text-muted">TXN: <?= htmlspecialchars($payment->transaction_id) ?></small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($payment->invoice_number): ?>
                                    <a href="/invoices/<?= $payment->invoice_id ?>" class="text-decoration-none">
                                        <?= htmlspecialchars($payment->invoice_number) ?>
                                    </a>
                                    <?php else: ?>
                                    <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="user-avatar bg-primary text-white rounded-circle me-2 d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                                            <?= strtoupper(substr($payment->client_name ?? 'C', 0, 1)) ?>
                                        </div>
                                        <div>
                                            <div class="fw-bold"><?= htmlspecialchars($payment->client_name ?? 'Unknown') ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <?= date('M d, Y', strtotime($payment->payment_date)) ?>
                                    <br><small class="text-muted"><?= date('H:i', strtotime($payment->payment_date)) ?></small>
                                </td>
                                <td>
                                    <strong class="<?= $payment->status === 'completed' ? 'text-success' : ($payment->status === 'failed' ? 'text-danger' : 'text-warning') ?>">
                                        <?= number_format($payment->amount, 2) ?> <?= $payment->currency ?? 'USD' ?>
                                    </strong>
                                </td>
                                <td>
                                    <?php 
                                    $methodIcon = match($payment->payment_method) {
                                        'cash' => 'fas fa-money-bill',
                                        'credit_card' => 'fas fa-credit-card',
                                        'bank_transfer' => 'fas fa-university',
                                        'check' => 'fas fa-check',
                                        default => 'fas fa-money-bill-wave'
                                    };
                                    ?>
                                    <i class="<?= $methodIcon ?> me-2"></i>
                                    <?= t('payments.method.' . $payment->payment_method) ?>
                                </td>
                                <td>
                                    <?php 
                                    $statusClass = match($payment->status) {
                                        'pending' => 'bg-warning text-dark',
                                        'completed' => 'bg-success',
                                        'failed' => 'bg-danger',
                                        'refunded' => 'bg-info',
                                        default => 'bg-secondary'
                                    };
                                    ?>
                                    <span class="badge <?= $statusClass ?>">
                                        <?= t('payments.status.' . $payment->status) ?>
                                    </span>
                                    <?php if ($payment->is_reconciled): ?>
                                    <br><small class="badge bg-secondary mt-1"><?= t('payments.reconciled') ?></small>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end">
                                    <div class="btn-group">
                                        <a href="/payments/<?= $payment->id ?>" class="btn btn-sm btn-outline-primary" title="<?= t('common.view') ?>">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        
                                        <div class="btn-group">
                                            <button type="button" class="btn btn-sm btn-outline-secondary dropdown-toggle dropdown-toggle-split" data-bs-toggle="dropdown">
                                                <span class="visually-hidden"><?= t('common.actions') ?></span>
                                            </button>
                                            <ul class="dropdown-menu">
                                                <li><a class="dropdown-item" href="/payments/<?= $payment->id ?>/receipt" target="_blank">
                                                    <i class="fas fa-receipt me-2"></i><?= t('payments.print_receipt') ?>
                                                </a></li>
                                                
                                                <?php if ($payment->status === 'pending' && $canEdit): ?>
                                                <li><a class="dropdown-item" href="#" onclick="updatePaymentStatus(<?= $payment->id ?>, 'completed')">
                                                    <i class="fas fa-check me-2"></i><?= t('payments.mark_completed') ?>
                                                </a></li>
                                                <li><a class="dropdown-item text-danger" href="#" onclick="updatePaymentStatus(<?= $payment->id ?>, 'failed')">
                                                    <i class="fas fa-times me-2"></i><?= t('payments.mark_failed') ?>
                                                </a></li>
                                                <?php endif; ?>
                                                
                                                <?php if ($payment->status === 'completed' && !$payment->is_reconciled && $canEdit): ?>
                                                <li><a class="dropdown-item" href="#" onclick="toggleReconciliation(<?= $payment->id ?>, true)">
                                                    <i class="fas fa-balance-scale me-2"></i><?= t('payments.mark_reconciled') ?>
                                                </a></li>
                                                <?php elseif ($payment->is_reconciled && $canEdit): ?>
                                                <li><a class="dropdown-item" href="#" onclick="toggleReconciliation(<?= $payment->id ?>, false)">
                                                    <i class="fas fa-undo me-2"></i><?= t('payments.unreconcile') ?>
                                                </a></li>
                                                <?php endif; ?>
                                                
                                                <?php if ($payment->status === 'completed' && $canEdit): ?>
                                                <li><hr class="dropdown-divider"></li>
                                                <li><a class="dropdown-item" href="/payments/<?= $payment->id ?>/refund">
                                                    <i class="fas fa-undo-alt me-2"></i><?= t('payments.process_refund') ?>
                                                </a></li>
                                                <?php endif; ?>
                                                
                                                <?php if ($canEdit): ?>
                                                <li><a class="dropdown-item" href="/payments/<?= $payment->id ?>/edit">
                                                    <i class="fas fa-edit me-2"></i><?= t('common.edit') ?>
                                                </a></li>
                                                <?php endif; ?>
                                                
                                                <?php if ($canDelete && $payment->status === 'pending'): ?>
                                                <li><hr class="dropdown-divider"></li>
                                                <li><a class="dropdown-item text-danger" href="#" onclick="deletePayment(<?= $payment->id ?>)">
                                                    <i class="fas fa-trash me-2"></i><?= t('common.delete') ?>
                                                </a></li>
                                                <?php endif; ?>
                                            </ul>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot>
                            <tr class="table-info">
                                <td colspan="4"><strong><?= t('common.totals') ?>:</strong></td>
                                <td><strong><?= number_format($totals['amount'] ?? 0, 2) ?></strong></td>
                                <td colspan="3"></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
                
                <!-- Pagination -->
                <?php if (isset($pagination) && $pagination['total_pages'] > 1): ?>
                <nav aria-label="<?= t('common.pagination') ?>" class="mt-4">
                    <ul class="pagination justify-content-center">
                        <!-- Pagination implementation -->
                    </ul>
                </nav>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
function updatePaymentStatus(paymentId, newStatus) {
    const statusNames = {
        'completed': '<?= t('payments.status.completed') ?>',
        'failed': '<?= t('payments.status.failed') ?>'
    };
    
    if (confirm('<?= t('payments.confirm_status_change') ?>'.replace(':status', statusNames[newStatus]))) {
        fetch(`/payments/${paymentId}/status`, {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({ status: newStatus })
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

function toggleReconciliation(paymentId, reconcile) {
    const message = reconcile ? '<?= t('payments.confirm_reconcile') ?>' : '<?= t('payments.confirm_unreconcile') ?>';
    
    if (confirm(message)) {
        fetch(`/payments/${paymentId}/reconcile`, {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({ reconcile: reconcile })
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
</script>