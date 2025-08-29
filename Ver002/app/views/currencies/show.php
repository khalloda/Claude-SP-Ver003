<?php
/**
 * File: app/views/currencies/show.php
 * Purpose: Currency detail view with comprehensive information and analytics
 * Layout: Uses app layout with professional presentation
 */

$this->layout('layouts/app', [
    'title' => $page_title ?? t('currencies.currency_details'),
    'active_nav' => 'currencies'
]);

$currentUser = $this->getCurrentUser();
$canEdit = $this->hasRole(['admin', 'manager']);
$canDelete = $this->hasRole(['admin']);
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">
            <i class="fas fa-coins me-2"></i><?= htmlspecialchars($currency->name) ?>
            <span class="badge bg-<?= $currency->is_active ? 'success' : 'secondary' ?> ms-2">
                <?= $currency->is_active ? t('common.active') : t('common.inactive') ?>
            </span>
            <?php if ($currency->is_default): ?>
            <span class="badge bg-primary ms-1"><?= t('currencies.default') ?></span>
            <?php endif; ?>
        </h1>
        <div class="btn-toolbar mb-2 mb-md-0">
            <div class="btn-group me-2">
                <?php if ($canEdit): ?>
                <a href="/currencies/<?= $currency->id ?>/edit" class="btn btn-primary">
                    <i class="fas fa-edit"></i> <?= t('common.edit') ?>
                </a>
                <?php endif; ?>
                <a href="/currencies" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left"></i> <?= t('common.back') ?>
                </a>
            </div>
            <div class="dropdown">
                <button class="btn btn-outline-info dropdown-toggle" type="button" data-bs-toggle="dropdown">
                    <i class="fas fa-ellipsis-v"></i>
                </button>
                <ul class="dropdown-menu">
                    <li><a class="dropdown-item" href="/reports/currency/<?= $currency->id ?>">
                        <i class="fas fa-chart-bar me-2"></i><?= t('currencies.usage_report') ?>
                    </a></li>
                    <li><a class="dropdown-item" href="#" onclick="showRateHistory()">
                        <i class="fas fa-history me-2"></i><?= t('currencies.rate_history') ?>
                    </a></li>
                    <li><a class="dropdown-item" href="#" onclick="exportCurrencyData()">
                        <i class="fas fa-download me-2"></i><?= t('common.export') ?>
                    </a></li>
                    <?php if ($canEdit): ?>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item" href="/currencies/create?duplicate_from=<?= $currency->id ?>">
                        <i class="fas fa-copy me-2"></i><?= t('currencies.duplicate') ?>
                    </a></li>
                    <?php endif; ?>
                    <?php if ($canDelete && !$currency->is_default && $currency->usage_count == 0): ?>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item text-danger" href="#" onclick="deleteCurrency()">
                        <i class="fas fa-trash me-2"></i><?= t('common.delete') ?>
                    </a></li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Main Information -->
        <div class="col-lg-8">
            <!-- Basic Details Card -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0"><?= t('currencies.basic_information') ?></h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <td class="fw-medium"><?= t('currencies.code') ?>:</td>
                                    <td><span class="badge bg-primary fs-6"><?= $currency->code ?></span></td>
                                </tr>
                                <tr>
                                    <td class="fw-medium"><?= t('currencies.name') ?>:</td>
                                    <td><?= htmlspecialchars($currency->name) ?></td>
                                </tr>
                                <tr>
                                    <td class="fw-medium"><?= t('currencies.symbol') ?>:</td>
                                    <td><span class="fs-5"><?= htmlspecialchars($currency->symbol) ?></span></td>
                                </tr>
                                <tr>
                                    <td class="fw-medium"><?= t('currencies.decimal_places') ?>:</td>
                                    <td><?= $currency->decimal_places ?> <?= t('currencies.digits') ?></td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <td class="fw-medium"><?= t('currencies.position') ?>:</td>
                                    <td><?= t('currencies.' . $currency->position . '_amount') ?></td>
                                </tr>
                                <tr>
                                    <td class="fw-medium"><?= t('currencies.separator') ?>:</td>
                                    <td><?= $currency->separator ? htmlspecialchars($currency->separator) : t('currencies.none') ?></td>
                                </tr>
                                <tr>
                                    <td class="fw-medium"><?= t('currencies.format_example') ?>:</td>
                                    <td class="currency-example">
                                        <?php
                                        $amount = '1234.56';
                                        if ($currency->decimal_places == 0) $amount = '1235';
                                        elseif ($currency->decimal_places == 3) $amount = '1234.560';
                                        elseif ($currency->decimal_places == 4) $amount = '1234.5600';
                                        
                                        if ($currency->separator) {
                                            $amount = preg_replace('/\B(?=(\d{3})+(?!\d))/', $currency->separator, $amount);
                                        }
                                        
                                        echo $currency->position === 'before' 
                                            ? htmlspecialchars($currency->symbol) . $amount 
                                            : $amount . htmlspecialchars($currency->symbol);
                                        ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="fw-medium"><?= t('common.status') ?>:</td>
                                    <td>
                                        <span class="badge bg-<?= $currency->is_active ? 'success' : 'secondary' ?>">
                                            <?= $currency->is_active ? t('common.active') : t('common.inactive') ?>
                                        </span>
                                        <?php if ($currency->is_default): ?>
                                        <span class="badge bg-primary ms-1"><?= t('currencies.default') ?></span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>
                    
                    <?php if (!empty($currency->notes)): ?>
                    <hr>
                    <div>
                        <strong><?= t('common.notes') ?>:</strong>
                        <p class="mt-2 mb-0"><?= nl2br(htmlspecialchars($currency->notes)) ?></p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Exchange Rate Information -->
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0"><?= t('currencies.exchange_rate_info') ?></h5>
                    <?php if ($canEdit && $currency->rate_source === 'api'): ?>
                    <button class="btn btn-sm btn-outline-info" onclick="updateExchangeRate()">
                        <i class="fas fa-sync-alt"></i> <?= t('currencies.update_rate') ?>
                    </button>
                    <?php endif; ?>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="text-center p-3 bg-light rounded">
                                <div class="h4 mb-2">
                                    1 <?= $baseCurrency->code ?? 'USD' ?> = 
                                    <span class="text-primary"><?= number_format($currency->exchange_rate, 5) ?></span> 
                                    <?= $currency->code ?>
                                </div>
                                <?php if (isset($currency->rate_change) && $currency->rate_change != 0): ?>
                                <div class="text-<?= $currency->rate_change > 0 ? 'success' : 'danger' ?>">
                                    <i class="fas fa-arrow-<?= $currency->rate_change > 0 ? 'up' : 'down' ?>"></i>
                                    <?= abs($currency->rate_change) ?>% <?= t('currencies.since_last_update') ?>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-sm">
                                <tr>
                                    <td><?= t('currencies.rate_source') ?>:</td>
                                    <td>
                                        <span class="badge bg-<?= $currency->rate_source === 'api' ? 'info' : 'secondary' ?>">
                                            <?= t('currencies.' . $currency->rate_source) ?>
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <td><?= t('currencies.last_updated') ?>:</td>
                                    <td><?= $currency->rate_updated_at ? date('M j, Y g:i A', strtotime($currency->rate_updated_at)) : t('common.never') ?></td>
                                </tr>
                                <tr>
                                    <td><?= t('currencies.update_frequency') ?>:</td>
                                    <td><?= $currency->rate_source === 'api' ? t('currencies.daily') : t('currencies.manual_only') ?></td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Usage Statistics -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0"><?= t('currencies.usage_statistics') ?></h5>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-md-3">
                            <div class="border-end">
                                <div class="h4 text-primary mb-1"><?= $stats['total_transactions'] ?? 0 ?></div>
                                <div class="small text-muted"><?= t('currencies.total_transactions') ?></div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="border-end">
                                <div class="h4 text-success mb-1"><?= number_format($stats['total_value'] ?? 0, $currency->decimal_places) ?></div>
                                <div class="small text-muted"><?= t('currencies.total_value') ?></div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="border-end">
                                <div class="h4 text-info mb-1"><?= $stats['active_products'] ?? 0 ?></div>
                                <div class="small text-muted"><?= t('currencies.products_using') ?></div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="h4 text-warning mb-1"><?= $stats['recent_activity'] ?? 0 ?></div>
                            <div class="small text-muted"><?= t('currencies.this_month') ?></div>
                        </div>
                    </div>
                    
                    <?php if (!empty($stats['monthly_data'])): ?>
                    <hr>
                    <div>
                        <h6><?= t('currencies.monthly_usage_trend') ?></h6>
                        <canvas id="usageChart" height="100"></canvas>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Recent Transactions -->
            <?php if (!empty($recentTransactions)): ?>
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0"><?= t('currencies.recent_transactions') ?></h5>
                    <a href="/reports/currency/<?= $currency->id ?>" class="btn btn-sm btn-outline-primary">
                        <?= t('common.view_all') ?>
                    </a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th><?= t('common.date') ?></th>
                                    <th><?= t('common.type') ?></th>
                                    <th><?= t('common.reference') ?></th>
                                    <th><?= t('common.amount') ?></th>
                                    <th><?= t('common.client') ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recentTransactions as $transaction): ?>
                                <tr>
                                    <td><?= date('M j, Y', strtotime($transaction->created_at)) ?></td>
                                    <td>
                                        <span class="badge bg-<?= $transaction->type_color ?? 'secondary' ?>">
                                            <?= t($transaction->type) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <a href="<?= $transaction->url ?>" class="text-decoration-none">
                                            <?= $transaction->reference ?>
                                        </a>
                                    </td>
                                    <td class="text-end">
                                        <?php
                                        $amount = number_format($transaction->amount, $currency->decimal_places);
                                        if ($currency->separator && $currency->separator !== ',') {
                                            $amount = str_replace(',', $currency->separator, $amount);
                                        }
                                        echo $currency->position === 'before' 
                                            ? htmlspecialchars($currency->symbol) . $amount 
                                            : $amount . htmlspecialchars($currency->symbol);
                                        ?>
                                    </td>
                                    <td><?= htmlspecialchars($transaction->client_name) ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Quick Stats -->
            <div class="card mb-3">
                <div class="card-header">
                    <h6 class="card-title mb-0">
                        <i class="fas fa-chart-pie me-2"></i><?= t('currencies.quick_stats') ?>
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-6">
                            <div class="border-end">
                                <div class="h5 text-primary mb-1"><?= $currency->usage_count ?? 0 ?></div>
                                <div class="small text-muted"><?= t('currencies.total_usage') ?></div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="h5 text-success mb-1"><?= date('M j, Y', strtotime($currency->created_at)) ?></div>
                            <div class="small text-muted"><?= t('currencies.created') ?></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="card mb-3">
                <div class="card-header">
                    <h6 class="card-title mb-0">
                        <i class="fas fa-bolt me-2"></i><?= t('common.quick_actions') ?>
                    </h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <?php if ($canEdit): ?>
                        <a href="/currencies/<?= $currency->id ?>/edit" class="btn btn-outline-primary btn-sm">
                            <i class="fas fa-edit"></i> <?= t('common.edit_currency') ?>
                        </a>
                        <?php endif; ?>
                        <a href="/reports/currency/<?= $currency->id ?>" class="btn btn-outline-info btn-sm">
                            <i class="fas fa-chart-bar"></i> <?= t('currencies.detailed_report') ?>
                        </a>
                        <button class="btn btn-outline-success btn-sm" onclick="showRateHistory()">
                            <i class="fas fa-history"></i> <?= t('currencies.rate_history') ?>
                        </button>
                        <button class="btn btn-outline-secondary btn-sm" onclick="exportCurrencyData()">
                            <i class="fas fa-download"></i> <?= t('currencies.export_data') ?>
                        </button>
                        <?php if ($canEdit): ?>
                        <a href="/currencies/create?duplicate_from=<?= $currency->id ?>" class="btn btn-outline-warning btn-sm">
                            <i class="fas fa-copy"></i> <?= t('currencies.duplicate') ?>
                        </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- System Information -->
            <div class="card">
                <div class="card-header">
                    <h6 class="card-title mb-0">
                        <i class="fas fa-info-circle me-2"></i><?= t('currencies.system_info') ?>
                    </h6>
                </div>
                <div class="card-body">
                    <div class="small">
                        <div class="d-flex justify-content-between mb-2">
                            <span><?= t('currencies.created') ?>:</span>
                            <span class="text-muted"><?= date('M j, Y g:i A', strtotime($currency->created_at)) ?></span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span><?= t('currencies.last_modified') ?>:</span>
                            <span class="text-muted"><?= date('M j, Y g:i A', strtotime($currency->updated_at)) ?></span>
                        </div>
                        <?php if ($currency->rate_updated_at): ?>
                        <div class="d-flex justify-content-between mb-2">
                            <span><?= t('currencies.rate_updated') ?>:</span>
                            <span class="text-muted"><?= date('M j, Y g:i A', strtotime($currency->rate_updated_at)) ?></span>
                        </div>
                        <?php endif; ?>
                        <div class="d-flex justify-content-between mb-2">
                            <span><?= t('currencies.created_by') ?>:</span>
                            <span class="text-muted"><?= htmlspecialchars($currency->created_by_name ?? t('common.system')) ?></span>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span><?= t('common.id') ?>:</span>
                            <span class="text-muted font-monospace"><?= $currency->id ?></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Rate History Modal -->
<div class="modal fade" id="rateHistoryModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><?= t('currencies.exchange_rate_history') ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="rateHistoryContent">
                    <div class="text-center py-3">
                        <i class="fas fa-spinner fa-spin"></i> <?= t('common.loading') ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Initialize usage chart if data exists
<?php if (!empty($stats['monthly_data'])): ?>
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('usageChart').getContext('2d');
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: <?= json_encode(array_keys($stats['monthly_data'])) ?>,
            datasets: [{
                label: '<?= t('currencies.transactions') ?>',
                data: <?= json_encode(array_values($stats['monthly_data'])) ?>,
                borderColor: 'rgb(75, 192, 192)',
                backgroundColor: 'rgba(75, 192, 192, 0.1)',
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        precision: 0
                    }
                }
            },
            plugins: {
                legend: {
                    display: false
                }
            }
        }
    });
});
<?php endif; ?>

// Update exchange rate
async function updateExchangeRate() {
    const button = event.target;
    const originalText = button.innerHTML;
    button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> <?= t('currencies.updating') ?>';
    button.disabled = true;
    
    try {
        const response = await fetch('/api/currencies/<?= $currency->id ?>/update-rate', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        });
        const data = await response.json();
        
        if (data.success) {
            showAlert('success', data.message);
            setTimeout(() => location.reload(), 1500);
        } else {
            showAlert('error', data.message || '<?= t('currencies.rate_update_failed') ?>');
        }
    } catch (error) {
        console.error('Rate update error:', error);
        showAlert('error', '<?= t('currencies.rate_update_error') ?>');
    } finally {
        button.innerHTML = originalText;
        button.disabled = false;
    }
}

// Show rate history
async function showRateHistory() {
    const modal = new bootstrap.Modal(document.getElementById('rateHistoryModal'));
    modal.show();
    
    try {
        const response = await fetch('/api/currencies/<?= $currency->id ?>/rate-history');
        const data = await response.json();
        
        let content = '<div class="table-responsive"><table class="table table-sm"><thead><tr>' +
                     '<th><?= t('common.date') ?></th>' +
                     '<th><?= t('currencies.rate') ?></th>' +
                     '<th><?= t('currencies.change') ?></th>' +
                     '<th><?= t('currencies.source') ?></th>' +
                     '</tr></thead><tbody>';
        
        if (data.history && data.history.length > 0) {
            data.history.forEach(record => {
                const change = record.change ? 
                    `<span class="text-${record.change > 0 ? 'success' : 'danger'}">${record.change > 0 ? '+' : ''}${record.change}%</span>` : 
                    '-';
                content += `<tr>
                    <td>${record.date}</td>
                    <td>${record.rate}</td>
                    <td>${change}</td>
                    <td><span class="badge bg-${record.source === 'api' ? 'info' : 'secondary'}">${record.source}</span></td>
                </tr>`;
            });
        } else {
            content += '<tr><td colspan="4" class="text-center text-muted"><?= t('currencies.no_rate_history') ?></td></tr>';
        }
        
        content += '</tbody></table></div>';
        document.getElementById('rateHistoryContent').innerHTML = content;
        
    } catch (error) {
        document.getElementById('rateHistoryContent').innerHTML = 
            '<div class="alert alert-danger"><?= t('currencies.history_load_error') ?></div>';
    }
}

// Export currency data
function exportCurrencyData() {
    const button = event.target;
    const originalText = button.textContent;
    button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> ' + originalText;
    button.disabled = true;
    
    window.location.href = '/api/currencies/<?= $currency->id ?>/export?format=csv';
    
    setTimeout(() => {
        button.innerHTML = '<i class="fas fa-download"></i> <?= t('currencies.export_data') ?>';
        button.disabled = false;
    }, 2000);
}

// Delete currency
function deleteCurrency() {
    if (confirm('<?= t('currencies.confirm_delete') ?>')) {
        fetch('/currencies/<?= $currency->id ?>', {
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
                setTimeout(() => window.location.href = '/currencies', 1500);
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

<style>
.currency-example {
    font-family: 'Courier New', monospace;
    font-size: 1.1em;
    font-weight: 600;
    color: #495057;
}

.table-borderless td {
    padding: 0.5rem 0;
}

.border-end:last-child {
    border-right: none !important;
}

@media (max-width: 768px) {
    .border-end {
        border-right: none !important;
        border-bottom: 1px solid #dee2e6;
        margin-bottom: 1rem;
        padding-bottom: 1rem;
    }
    
    .border-end:last-child {
        border-bottom: none !important;
        margin-bottom: 0;
        padding-bottom: 0;
    }
}
</style>