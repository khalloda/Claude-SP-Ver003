<?php
/**
 * File: app/views/quotes/index.php
 * Purpose: Quotes listing page with filters and search
 * Layout: Uses app layout with data table functionality
 */

$this->layout('layouts/app', [
    'title' => $page_title ?? t('nav.quotes'),
    'active_nav' => 'quotes'
]);

$currentUser = $this->getCurrentUser();
$canCreate = $this->hasRole(['admin', 'manager', 'sales']);
$canEdit = $this->hasRole(['admin', 'manager', 'sales']);
$canDelete = $this->hasRole(['admin', 'manager']);
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">
            <i class="fas fa-file-alt me-2"></i><?= t('nav.quotes') ?>
            <span class="badge bg-secondary ms-2"><?= count($quotes) ?></span>
        </h1>
        <?php if ($canCreate): ?>
        <div class="btn-toolbar mb-2 mb-md-0">
            <a href="/quotes/create" class="btn btn-primary">
                <i class="fas fa-plus"></i> <?= t('quotes.new_quote') ?>
            </a>
        </div>
        <?php endif; ?>
    </div>

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="/quotes" class="row g-3">
                <div class="col-md-3">
                    <label for="search" class="form-label"><?= t('common.search') ?></label>
                    <input type="text" class="form-control" id="search" name="search" 
                           value="<?= htmlspecialchars($search ?? '') ?>" 
                           placeholder="<?= t('quotes.search_placeholder') ?>">
                </div>
                
                <div class="col-md-2">
                    <label for="status" class="form-label"><?= t('common.status') ?></label>
                    <select class="form-select" id="status" name="status">
                        <option value=""><?= t('common.all_statuses') ?></option>
                        <option value="draft" <?= ($status ?? '') === 'draft' ? 'selected' : '' ?>><?= t('quotes.status.draft') ?></option>
                        <option value="sent" <?= ($status ?? '') === 'sent' ? 'selected' : '' ?>><?= t('quotes.status.sent') ?></option>
                        <option value="accepted" <?= ($status ?? '') === 'accepted' ? 'selected' : '' ?>><?= t('quotes.status.accepted') ?></option>
                        <option value="rejected" <?= ($status ?? '') === 'rejected' ? 'selected' : '' ?>><?= t('quotes.status.rejected') ?></option>
                        <option value="expired" <?= ($status ?? '') === 'expired' ? 'selected' : '' ?>><?= t('quotes.status.expired') ?></option>
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
                        <i class="fas fa-filter"></i> <?= t('common.filter') ?>
                    </button>
                    <a href="/quotes" class="btn btn-outline-secondary">
                        <i class="fas fa-times"></i>
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Quotes Table -->
    <div class="card">
        <div class="card-body">
            <?php if (empty($quotes)): ?>
                <div class="text-center py-5">
                    <i class="fas fa-file-alt fa-4x text-muted mb-4"></i>
                    <h4><?= t('quotes.no_quotes_found') ?></h4>
                    <p class="text-muted"><?= t('quotes.no_quotes_desc') ?></p>
                    <?php if ($canCreate): ?>
                    <a href="/quotes/create" class="btn btn-primary">
                        <i class="fas fa-plus"></i> <?= t('quotes.create_first_quote') ?>
                    </a>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th><?= t('quotes.quote_number') ?></th>
                                <th><?= t('clients.client') ?></th>
                                <th><?= t('quotes.quote_date') ?></th>
                                <th><?= t('quotes.valid_until') ?></th>
                                <th><?= t('quotes.total_amount') ?></th>
                                <th><?= t('common.status') ?></th>
                                <th class="text-end"><?= t('common.actions') ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($quotes as $quote): ?>
                            <tr>
                                <td>
                                    <strong><?= htmlspecialchars($quote->quote_number) ?></strong>
                                    <?php if ($quote->reference): ?>
                                    <br><small class="text-muted"><?= htmlspecialchars($quote->reference) ?></small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="user-avatar bg-primary text-white rounded-circle me-2 d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                                            <?= strtoupper(substr($quote->client_name ?? 'C', 0, 1)) ?>
                                        </div>
                                        <div>
                                            <div class="fw-bold"><?= htmlspecialchars($quote->client_name ?? 'Unknown') ?></div>
                                            <?php if ($quote->client_email): ?>
                                            <small class="text-muted"><?= htmlspecialchars($quote->client_email) ?></small>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </td>
                                <td><?= date('M d, Y', strtotime($quote->quote_date)) ?></td>
                                <td>
                                    <?php 
                                    $validUntil = strtotime($quote->valid_until);
                                    $isExpired = $validUntil < time();
                                    ?>
                                    <span class="<?= $isExpired ? 'text-danger' : '' ?>">
                                        <?= date('M d, Y', $validUntil) ?>
                                    </span>
                                    <?php if ($isExpired): ?>
                                    <br><small class="badge bg-danger"><?= t('quotes.expired') ?></small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <strong><?= number_format($quote->total_amount, 2) ?> <?= $quote->currency ?? 'USD' ?></strong>
                                </td>
                                <td>
                                    <?php 
                                    $statusClass = match($quote->status) {
                                        'draft' => 'bg-secondary',
                                        'sent' => 'bg-info',
                                        'accepted' => 'bg-success',
                                        'rejected' => 'bg-danger',
                                        'expired' => 'bg-warning text-dark',
                                        default => 'bg-secondary'
                                    };
                                    ?>
                                    <span class="badge <?= $statusClass ?>">
                                        <?= t('quotes.status.' . $quote->status) ?>
                                    </span>
                                </td>
                                <td class="text-end">
                                    <div class="btn-group">
                                        <a href="/quotes/<?= $quote->id ?>" class="btn btn-sm btn-outline-primary" title="<?= t('common.view') ?>">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        
                                        <?php if ($canEdit && in_array($quote->status, ['draft', 'sent'])): ?>
                                        <a href="/quotes/<?= $quote->id ?>/edit" class="btn btn-sm btn-outline-secondary" title="<?= t('common.edit') ?>">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <?php endif; ?>
                                        
                                        <div class="btn-group">
                                            <button type="button" class="btn btn-sm btn-outline-secondary dropdown-toggle dropdown-toggle-split" data-bs-toggle="dropdown">
                                                <span class="visually-hidden"><?= t('common.actions') ?></span>
                                            </button>
                                            <ul class="dropdown-menu">
                                                <li><a class="dropdown-item" href="/quotes/<?= $quote->id ?>/pdf" target="_blank">
                                                    <i class="fas fa-file-pdf me-2"></i><?= t('common.download_pdf') ?>
                                                </a></li>
                                                
                                                <?php if ($quote->status === 'draft'): ?>
                                                <li><a class="dropdown-item" href="#" onclick="sendQuote(<?= $quote->id ?>)">
                                                    <i class="fas fa-paper-plane me-2"></i><?= t('quotes.send_quote') ?>
                                                </a></li>
                                                <?php endif; ?>
                                                
                                                <?php if ($quote->status === 'accepted'): ?>
                                                <li><a class="dropdown-item" href="/sales-orders/create?quote_id=<?= $quote->id ?>">
                                                    <i class="fas fa-shopping-cart me-2"></i><?= t('quotes.convert_to_order') ?>
                                                </a></li>
                                                <?php endif; ?>
                                                
                                                <?php if ($canEdit): ?>
                                                <li><a class="dropdown-item" href="/quotes/<?= $quote->id ?>/duplicate">
                                                    <i class="fas fa-copy me-2"></i><?= t('common.duplicate') ?>
                                                </a></li>
                                                <?php endif; ?>
                                                
                                                <?php if ($canDelete && $quote->status === 'draft'): ?>
                                                <li><hr class="dropdown-divider"></li>
                                                <li><a class="dropdown-item text-danger" href="#" onclick="deleteQuote(<?= $quote->id ?>, '<?= htmlspecialchars($quote->quote_number) ?>')">
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
                
                <!-- Pagination would go here -->
                <?php if (isset($pagination) && $pagination['total_pages'] > 1): ?>
                <nav aria-label="<?= t('common.pagination') ?>">
                    <ul class="pagination justify-content-center">
                        <!-- Pagination links -->
                    </ul>
                </nav>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
function sendQuote(quoteId) {
    if (confirm('<?= t('quotes.confirm_send') ?>')) {
        fetch(`/quotes/${quoteId}/send`, {
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

function deleteQuote(quoteId, quoteNumber) {
    if (confirm('<?= t('quotes.confirm_delete') ?>'.replace(':number', quoteNumber))) {
        fetch(`/quotes/${quoteId}`, {
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