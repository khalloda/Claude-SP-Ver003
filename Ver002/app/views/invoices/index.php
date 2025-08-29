<?php
/**
 * File: app/views/invoices/index.php
 * Purpose: Invoices listing page with payment tracking
 * Layout: Uses app layout with financial data visualization
 */

$this->layout('layouts/app', [
    'title' => $page_title ?? t('nav.invoices'),
    'active_nav' => 'invoices'
]);

$currentUser = $this->getCurrentUser();
$canCreate = $this->hasRole(['admin', 'manager', 'accounting']);
$canEdit = $this->hasRole(['admin', 'manager', 'accounting']);
$canDelete = $this->hasRole(['admin', 'manager']);
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">
            <i class="fas fa-file-invoice me-2"></i><?= t('nav.invoices') ?>
            <span class="badge bg-secondary ms-2"><?= count($invoices) ?></span>
        </h1>
        <div class="btn-toolbar mb-2 mb-md-0">
            <div class="btn-group me-2">
                <button type="button" class="btn btn-sm btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown">
                    <i class="fas fa-chart-bar"></i> <?= t('invoices.reports') ?>
                </button>
                <ul class="dropdown-menu">
                    <li><a class="dropdown-item" href="/reports/invoices/summary"><i class="fas fa-chart-pie me-2"></i><?= t('reports.invoice_summary') ?></a></li>
                    <li><a class="dropdown-item" href="/reports/invoices/aging"><i class="fas fa-clock me-2"></i><?= t('reports.aging_report') ?></a></li>
                    <li><a class="dropdown-item" href="/reports/invoices/payments"><i class="fas fa-money-bill me-2"></i><?= t('reports.payment_report') ?></a></li>
                </ul>
            </div>
            <?php if ($canCreate): ?>
            <a href="/invoices/create" class="btn btn-primary">
                <i class="fas fa-plus"></i> <?= t('invoices.new_invoice') ?>
            </a>
            <?php endif; ?>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <div class="h4"><?= number_format($summary['total_invoices'] ?? 0) ?></div>
                            <div class="small"><?= t('invoices.total_invoices') ?></div>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-file-invoice fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <div class="h4"><?= number_format($summary['paid_amount'] ?? 0, 0) ?></div>
                            <div class="small"><?= t('invoices.total_paid') ?></div>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-check-circle fa-2x"></i>
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
                            <div class="h4"><?= number_format($summary['pending_amount'] ?? 0, 0) ?></div>
                            <div class="small"><?= t('invoices.total_pending') ?></div>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-hourglass-half fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card bg-danger text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <div class="h4"><?= number_format($summary['overdue_count'] ?? 0) ?></div>
                            <div class="small"><?= t('invoices.overdue_invoices') ?></div>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-exclamation-triangle fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="/invoices" class="row g-3">
                <div class="col-md-3">
                    <label for="search" class="form-label"><?= t('common.search') ?></label>
                    <input type="text" class="form-control" id="search" name="search" 
                           value="<?= htmlspecialchars($search ?? '') ?>" 
                           placeholder="<?= t('invoices.search_placeholder') ?>">
                </div>
                
                <div class="col-md-2">
                    <label for="status" class="form-label"><?= t('common.status') ?></label>
                    <select class="form-select" id="status" name="status">
                        <option value=""><?= t('common.all_statuses') ?></option>
                        <option value="draft" <?= ($status ?? '') === 'draft' ? 'selected' : '' ?>><?= t('invoices.status.draft') ?></option>
                        <option value="sent" <?= ($status ?? '') === 'sent' ? 'selected' : '' ?>><?= t('invoices.status.sent') ?></option>
                        <option value="paid" <?= ($status ?? '') === 'paid' ? 'selected' : '' ?>><?= t('invoices.status.paid') ?></option>
                        <option value="overdue" <?= ($status ?? '') === 'overdue' ? 'selected' : '' ?>><?= t('invoices.status.overdue') ?></option>
                        <option value="cancelled" <?= ($status ?? '') === 'cancelled' ? 'selected' : '' ?>><?= t('invoices.status.cancelled') ?></option>
                    </select>
                </div>
                
                <div class="col-md-2">
                    <label for="client_id" class="form-label"><?= t('clients.client') ?></label>
                    <select class="form-select" id="client_id" name="client_id">
                        <option value=""><?= t('common.all_clients') ?></option>
                        <?php foreach ($clients ?? [] as $client): ?>
                        <option value="<?= $client->id ?>" <?= ($client_id ?? '') == $client->id ? 'selected' : '' ?>>
                            <?= htmlspecialchars($client->name) ?>
                        </option>
                        <?php endforeach; ?>
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
                    <a href="/invoices" class="btn btn-outline-secondary">
                        <i class="fas fa-times"></i>
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Invoices Table -->
    <div class="card">
        <div class="card-body">
            <?php if (empty($invoices)): ?>
                <div class="text-center py-5">
                    <i class="fas fa-file-invoice fa-4x text-muted mb-4"></i>
                    <h4><?= t('invoices.no_invoices_found') ?></h4>
                    <p class="text-muted"><?= t('invoices.no_invoices_desc') ?></p>
                    <?php if ($canCreate): ?>
                    <a href="/invoices/create" class="btn btn-primary">
                        <i class="fas fa-plus"></i> <?= t('invoices.create_first_invoice') ?>
                    </a>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th><?= t('invoices.invoice_number') ?></th>
                                <th><?= t('clients.client') ?></th>
                                <th><?= t('invoices.invoice_date') ?></th>
                                <th><?= t('invoices.due_date') ?></th>
                                <th><?= t('invoices.amount') ?></th>
                                <th><?= t('invoices.paid_amount') ?></th>
                                <th><?= t('common.status') ?></th>
                                <th class="text-end"><?= t('common.actions') ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($invoices as $invoice): ?>
                            <tr class="<?= ($invoice->status === 'overdue' ? 'table-warning' : '') ?>">
                                <td>
                                    <strong><?= htmlspecialchars($invoice->invoice_number) ?></strong>
                                    <?php if ($invoice->reference): ?>
                                    <br><small class="text-muted"><?= htmlspecialchars($invoice->reference) ?></small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="user-avatar bg-primary text-white rounded-circle me-2 d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                                            <?= strtoupper(substr($invoice->client_name ?? 'C', 0, 1)) ?>
                                        </div>
                                        <div>
                                            <div class="fw-bold"><?= htmlspecialchars($invoice->client_name ?? 'Unknown') ?></div>
                                            <?php if ($invoice->client_email): ?>
                                            <small class="text-muted"><?= htmlspecialchars($invoice->client_email) ?></small>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </td>
                                <td><?= date('M d, Y', strtotime($invoice->invoice_date)) ?></td>
                                <td>
                                    <?php 
                                    $dueDate = strtotime($invoice->due_date);
                                    $isOverdue = $dueDate < time() && $invoice->status !== 'paid';
                                    ?>
                                    <span class="<?= $isOverdue ? 'text-danger' : '' ?>">
                                        <?= date('M d, Y', $dueDate) ?>
                                    </span>
                                    <?php if ($isOverdue): ?>
                                    <br><small class="badge bg-danger"><?= t('invoices.overdue') ?></small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <strong><?= number_format($invoice->total_amount, 2) ?> <?= $invoice->currency ?? 'USD' ?></strong>
                                </td>
                                <td>
                                    <?php $paidAmount = $invoice->paid_amount ?? 0; ?>
                                    <?php if ($paidAmount > 0): ?>
                                    <span class="text-success"><?= number_format($paidAmount, 2) ?></span>
                                    <?php if ($paidAmount < $invoice->total_amount): ?>
                                    <br><small class="text-muted">
                                        <?= number_format($invoice->total_amount - $paidAmount, 2) ?> <?= t('invoices.remaining') ?>
                                    </small>
                                    <?php endif; ?>
                                    <?php else: ?>
                                    <span class="text-muted">0.00</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php 
                                    $statusClass = match($invoice->status) {
                                        'draft' => 'bg-secondary',
                                        'sent' => 'bg-info',
                                        'paid' => 'bg-success',
                                        'overdue' => 'bg-danger',
                                        'cancelled' => 'bg-dark',
                                        default => 'bg-secondary'
                                    };
                                    ?>
                                    <span class="badge <?= $statusClass ?>">
                                        <?= t('invoices.status.' . $invoice->status) ?>
                                    </span>
                                </td>
                                <td class="text-end">
                                    <div class="btn-group">
                                        <a href="/invoices/<?= $invoice->id ?>" class="btn btn-sm btn-outline-primary" title="<?= t('common.view') ?>">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        
                                        <?php if ($canEdit && in_array($invoice->status, ['draft', 'sent'])): ?>
                                        <a href="/invoices/<?= $invoice->id ?>/edit" class="btn btn-sm btn-outline-secondary" title="<?= t('common.edit') ?>">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <?php endif; ?>
                                        
                                        <div class="btn-group">
                                            <button type="button" class="btn btn-sm btn-outline-secondary dropdown-toggle dropdown-toggle-split" data-bs-toggle="dropdown">
                                                <span class="visually-hidden"><?= t('common.actions') ?></span>
                                            </button>
                                            <ul class="dropdown-menu">
                                                <li><a class="dropdown-item" href="/invoices/<?= $invoice->id ?>/pdf" target="_blank">
                                                    <i class="fas fa-file-pdf me-2"></i><?= t('common.download_pdf') ?>
                                                </a></li>
                                                
                                                <?php if ($invoice->status === 'draft'): ?>
                                                <li><a class="dropdown-item" href="#" onclick="sendInvoice(<?= $invoice->id ?>)">
                                                    <i class="fas fa-paper-plane me-2"></i><?= t('invoices.send_invoice') ?>
                                                </a></li>
                                                <?php endif; ?>
                                                
                                                <?php if (in_array($invoice->status, ['sent', 'overdue'])): ?>
                                                <li><a class="dropdown-item" href="/payments/create?invoice_id=<?= $invoice->id ?>">
                                                    <i class="fas fa-money-bill me-2"></i><?= t('payments.record_payment') ?>
                                                </a></li>
                                                <?php endif; ?>
                                                
                                                <?php if ($canEdit): ?>
                                                <li><a class="dropdown-item" href="/invoices/<?= $invoice->id ?>/duplicate">
                                                    <i class="fas fa-copy me-2"></i><?= t('common.duplicate') ?>
                                                </a></li>
                                                <?php endif; ?>
                                                
                                                <?php if ($canDelete && $invoice->status === 'draft'): ?>
                                                <li><hr class="dropdown-divider"></li>
                                                <li><a class="dropdown-item text-danger" href="#" onclick="deleteInvoice(<?= $invoice->id ?>, '<?= htmlspecialchars($invoice->invoice_number) ?>')">
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

function deleteInvoice(invoiceId, invoiceNumber) {
    if (confirm('<?= t('invoices.confirm_delete') ?>'.replace(':number', invoiceNumber))) {
        fetch(`/invoices/${invoiceId}`, {
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