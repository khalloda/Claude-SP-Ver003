<?php
/**
 * File: app/views/currencies/index.php
 * Purpose: Currency management listing with exchange rate tracking
 * Layout: Uses app layout with comprehensive currency administration
 */

$this->layout('layouts/app', [
    'title' => $page_title ?? t('nav.currencies'),
    'active_nav' => 'settings'
]);

$currentUser = $this->getCurrentUser();
$canManage = $this->hasRole(['admin', 'manager']);
$canEdit = $this->hasRole(['admin', 'manager']);
$canDelete = $this->hasRole(['admin']);
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">
            <i class="fas fa-coins me-2"></i><?= t('currencies.currency_management') ?>
            <span class="badge bg-secondary ms-2"><?= count($currencies ?? []) ?></span>
        </h1>
        <div class="btn-toolbar mb-2 mb-md-0">
            <div class="btn-group me-2">
                <button type="button" class="btn btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown">
                    <i class="fas fa-sync-alt"></i> <?= t('currencies.exchange_rates') ?>
                </button>
                <ul class="dropdown-menu">
                    <li><a class="dropdown-item" href="#" onclick="updateAllRates()">
                        <i class="fas fa-download me-2"></i><?= t('currencies.update_all_rates') ?>
                    </a></li>
                    <li><a class="dropdown-item" href="#" onclick="scheduleUpdates()">
                        <i class="fas fa-clock me-2"></i><?= t('currencies.schedule_updates') ?>
                    </a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item" href="/currencies/rates-history">
                        <i class="fas fa-history me-2"></i><?= t('currencies.rate_history') ?>
                    </a></li>
                </ul>
            </div>
            <?php if ($canManage): ?>
            <a href="/currencies/create" class="btn btn-primary">
                <i class="fas fa-plus"></i> <?= t('currencies.add_currency') ?>
            </a>
            <?php endif; ?>
        </div>
    </div>

    <!-- Currency Summary Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body text-center">
                    <div class="h4"><?= count($currencies ?? []) ?></div>
                    <div class="small"><?= t('currencies.total_currencies') ?></div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body text-center">
                    <div class="h4"><?= count(array_filter($currencies ?? [], fn($c) => $c->is_active)) ?></div>
                    <div class="small"><?= t('currencies.active_currencies') ?></div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body text-center">
                    <div class="h4"><?= ($base_currency->code ?? 'USD') ?></div>
                    <div class="small"><?= t('currencies.base_currency') ?></div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-white">
                <div class="card-body text-center">
                    <div class="h4"><?= $last_update ? date('M d', strtotime($last_update)) : 'Never' ?></div>
                    <div class="small"><?= t('currencies.last_update') ?></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Currencies Table -->
    <div class="card">
        <div class="card-body">
            <?php if (!empty($currencies)): ?>
            <div class="table-responsive">
                <table class="table table-hover" id="currenciesTable">
                    <thead>
                        <tr>
                            <th>
                                <input type="checkbox" class="form-check-input" id="selectAll">
                            </th>
                            <th><?= t('currencies.currency') ?></th>
                            <th class="text-center"><?= t('currencies.code') ?></th>
                            <th class="text-center"><?= t('currencies.symbol') ?></th>
                            <th class="text-end"><?= t('currencies.exchange_rate') ?></th>
                            <th class="text-center"><?= t('currencies.decimal_places') ?></th>
                            <th class="text-center"><?= t('currencies.status') ?></th>
                            <th class="text-center"><?= t('currencies.last_updated') ?></th>
                            <th class="text-center"><?= t('common.actions') ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($currencies as $currency): ?>
                        <tr>
                            <td>
                                <input type="checkbox" class="form-check-input row-select" value="<?= $currency->id ?>">
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <?php if ($currency->flag): ?>
                                    <img src="<?= htmlspecialchars($currency->flag) ?>" alt="" class="me-2 rounded" style="width: 24px; height: 18px;">
                                    <?php else: ?>
                                    <div class="bg-light rounded me-2 d-flex align-items-center justify-content-center" style="width: 24px; height: 18px;">
                                        <i class="fas fa-coins text-muted" style="font-size: 12px;"></i>
                                    </div>
                                    <?php endif; ?>
                                    <div>
                                        <a href="/currencies/<?= $currency->id ?>" class="fw-bold text-decoration-none">
                                            <?= htmlspecialchars($currency->name) ?>
                                        </a>
                                        <?php if ($currency->is_default): ?>
                                        <span class="badge bg-primary ms-2"><?= t('currencies.default') ?></span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </td>
                            <td class="text-center">
                                <code class="bg-light px-2 py-1 rounded"><?= htmlspecialchars($currency->code) ?></code>
                            </td>
                            <td class="text-center">
                                <span class="h6 mb-0"><?= htmlspecialchars($currency->symbol) ?></span>
                            </td>
                            <td class="text-end">
                                <?php if ($currency->is_default): ?>
                                <span class="text-muted">1.00000</span>
                                <?php else: ?>
                                <div class="d-flex align-items-center justify-content-end">
                                    <span class="me-2"><?= number_format($currency->exchange_rate, 5) ?></span>
                                    <?php if ($currency->rate_change): ?>
                                    <small class="text-<?= $currency->rate_change > 0 ? 'success' : 'danger' ?>">
                                        <i class="fas fa-arrow-<?= $currency->rate_change > 0 ? 'up' : 'down' ?>"></i>
                                        <?= abs($currency->rate_change) ?>%
                                    </small>
                                    <?php endif; ?>
                                </div>
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-info"><?= $currency->decimal_places ?></span>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-<?= $currency->is_active ? 'success' : 'secondary' ?>">
                                    <?= $currency->is_active ? t('common.active') : t('common.inactive') ?>
                                </span>
                            </td>
                            <td class="text-center">
                                <?php if ($currency->updated_at): ?>
                                <small class="text-muted"><?= date('M d, H:i', strtotime($currency->updated_at)) ?></small>
                                <?php else: ?>
                                <small class="text-muted">-</small>
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                                <div class="btn-group btn-group-sm">
                                    <a href="/currencies/<?= $currency->id ?>" class="btn btn-outline-primary" title="<?= t('common.view') ?>">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <?php if ($canEdit && !$currency->is_default): ?>
                                    <a href="/currencies/<?= $currency->id ?>/edit" class="btn btn-outline-secondary" title="<?= t('common.edit') ?>">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <?php endif; ?>
                                    <button type="button" class="btn btn-outline-info dropdown-toggle dropdown-toggle-split" data-bs-toggle="dropdown" title="<?= t('common.more_actions') ?>">
                                        <span class="visually-hidden"><?= t('common.actions') ?></span>
                                    </button>
                                    <ul class="dropdown-menu">
                                        <li><a class="dropdown-item" href="#" onclick="updateRate(<?= $currency->id ?>)">
                                            <i class="fas fa-sync me-2"></i><?= t('currencies.update_rate') ?>
                                        </a></li>
                                        <li><a class="dropdown-item" href="/currencies/<?= $currency->id ?>/history">
                                            <i class="fas fa-history me-2"></i><?= t('currencies.rate_history') ?>
                                        </a></li>
                                        <?php if ($canEdit && !$currency->is_default): ?>
                                        <li><hr class="dropdown-divider"></li>
                                        <li><a class="dropdown-item" href="#" onclick="toggleStatus(<?= $currency->id ?>, <?= $currency->is_active ? 'false' : 'true' ?>)">
                                            <i class="fas fa-<?= $currency->is_active ? 'pause' : 'play' ?> me-2"></i>
                                            <?= $currency->is_active ? t('currencies.deactivate') : t('currencies.activate') ?>
                                        </a></li>
                                        <?php if (!$currency->is_default): ?>
                                        <li><a class="dropdown-item" href="#" onclick="setAsDefault(<?= $currency->id ?>)">
                                            <i class="fas fa-star me-2"></i><?= t('currencies.set_as_default') ?>
                                        </a></li>
                                        <?php endif; ?>
                                        <?php endif; ?>
                                        <?php if ($canDelete && !$currency->is_default && !$currency->has_transactions): ?>
                                        <li><hr class="dropdown-divider"></li>
                                        <li><a class="dropdown-item text-danger" href="#" onclick="deleteCurrency(<?= $currency->id ?>)">
                                            <i class="fas fa-trash me-2"></i><?= t('common.delete') ?>
                                        </a></li>
                                        <?php endif; ?>
                                    </ul>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Bulk Actions -->
            <div class="row mt-3">
                <div class="col-md-6">
                    <div class="bulk-actions" style="display: none;">
                        <div class="btn-group">
                            <button type="button" class="btn btn-outline-secondary btn-sm dropdown-toggle" data-bs-toggle="dropdown">
                                <span id="selectedCount">0</span> <?= t('common.selected') ?> - <?= t('common.bulk_actions') ?>
                            </button>
                            <ul class="dropdown-menu">
                                <?php if ($canEdit): ?>
                                <li><a class="dropdown-item" href="#" onclick="bulkUpdateRates()">
                                    <i class="fas fa-sync me-2"></i><?= t('currencies.update_selected_rates') ?>
                                </a></li>
                                <li><a class="dropdown-item" href="#" onclick="bulkToggleStatus('activate')">
                                    <i class="fas fa-play me-2"></i><?= t('currencies.activate_selected') ?>
                                </a></li>
                                <li><a class="dropdown-item" href="#" onclick="bulkToggleStatus('deactivate')">
                                    <i class="fas fa-pause me-2"></i><?= t('currencies.deactivate_selected') ?>
                                </a></li>
                                <?php endif; ?>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 text-end">
                    <?= $this->paginate($currencies ?? []) ?>
                </div>
            </div>
            
            <?php else: ?>
            <!-- Empty State -->
            <div class="text-center py-5">
                <i class="fas fa-coins fa-4x text-muted mb-3"></i>
                <h5 class="text-muted"><?= t('currencies.no_currencies') ?></h5>
                <p class="text-muted mb-4"><?= t('currencies.no_currencies_description') ?></p>
                <?php if ($canManage): ?>
                <a href="/currencies/create" class="btn btn-primary">
                    <i class="fas fa-plus me-2"></i><?= t('currencies.add_first_currency') ?>
                </a>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Row selection functionality
    const selectAllCheckbox = document.getElementById('selectAll');
    const rowCheckboxes = document.querySelectorAll('.row-select');
    const bulkActions = document.querySelector('.bulk-actions');
    const selectedCount = document.getElementById('selectedCount');
    
    selectAllCheckbox?.addEventListener('change', function() {
        rowCheckboxes.forEach(checkbox => {
            checkbox.checked = this.checked;
        });
        updateBulkActions();
    });
    
    rowCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', updateBulkActions);
    });
    
    function updateBulkActions() {
        const selected = document.querySelectorAll('.row-select:checked').length;
        if (selectedCount) selectedCount.textContent = selected;
        
        if (selected > 0) {
            if (bulkActions) bulkActions.style.display = 'block';
        } else {
            if (bulkActions) bulkActions.style.display = 'none';
        }
        
        if (selectAllCheckbox) {
            selectAllCheckbox.indeterminate = selected > 0 && selected < rowCheckboxes.length;
            selectAllCheckbox.checked = selected === rowCheckboxes.length;
        }
    }
});

function updateRate(currencyId) {
    fetch(`/currencies/${currencyId}/update-rate`, {
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

function updateAllRates() {
    const btn = event.target.closest('a');
    const originalText = btn.innerHTML;
    
    btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i><?= t('currencies.updating') ?>';
    btn.classList.add('disabled');
    
    fetch('/currencies/update-all-rates', {
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
    })
    .finally(() => {
        btn.innerHTML = originalText;
        btn.classList.remove('disabled');
    });
}

function toggleStatus(currencyId, activate) {
    const action = activate === 'true' ? 'activate' : 'deactivate';
    
    fetch(`/currencies/${currencyId}/${action}`, {
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

function setAsDefault(currencyId) {
    if (confirm('<?= t('currencies.confirm_set_default') ?>')) {
        fetch(`/currencies/${currencyId}/set-default`, {
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

function deleteCurrency(currencyId) {
    if (confirm('<?= t('currencies.confirm_delete') ?>')) {
        fetch(`/currencies/${currencyId}`, {
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