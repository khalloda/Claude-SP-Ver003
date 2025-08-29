<?php
/**
 * File: app/views/currencies/edit.php
 * Purpose: Currency editing form with exchange rate management
 * Layout: Uses app layout with form validation
 */

$this->layout('layouts/app', [
    'title' => $page_title ?? t('currencies.edit_currency'),
    'active_nav' => 'currencies'
]);

$currentUser = $this->getCurrentUser();
$canEdit = $this->hasRole(['admin', 'manager']);
$canDelete = $this->hasRole(['admin']);
$isReadOnly = !$canEdit;
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">
            <i class="fas fa-coins me-2"></i><?= t('currencies.edit_currency') ?>
            <span class="badge bg-<?= $currency->is_active ? 'success' : 'secondary' ?> ms-2">
                <?= $currency->is_active ? t('common.active') : t('common.inactive') ?>
            </span>
            <?php if ($currency->is_default): ?>
            <span class="badge bg-primary ms-1"><?= t('currencies.default') ?></span>
            <?php endif; ?>
        </h1>
        <div class="btn-toolbar mb-2 mb-md-0">
            <div class="btn-group me-2">
                <a href="/currencies/<?= $currency->id ?>" class="btn btn-outline-info">
                    <i class="fas fa-eye"></i> <?= t('common.view') ?>
                </a>
                <a href="/currencies" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left"></i> <?= t('common.back') ?>
                </a>
            </div>
            <?php if ($canDelete && !$currency->is_default && $currency->usage_count == 0): ?>
            <button class="btn btn-outline-danger" onclick="deleteCurrency()">
                <i class="fas fa-trash"></i> <?= t('common.delete') ?>
            </button>
            <?php endif; ?>
        </div>
    </div>

    <?php if ($isReadOnly): ?>
    <div class="alert alert-info">
        <i class="fas fa-info-circle me-2"></i>
        <?= t('messages.info.view_only_mode') ?>
    </div>
    <?php endif; ?>

    <?php if ($currency->usage_count > 0 && $canDelete): ?>
    <div class="alert alert-warning">
        <i class="fas fa-exclamation-triangle me-2"></i>
        <?= t('currencies.cannot_delete_in_use', ['count' => $currency->usage_count]) ?>
    </div>
    <?php endif; ?>
    
    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0"><?= t('currencies.currency_details') ?></h5>
                </div>
                <div class="card-body">
                    <form id="currencyForm" method="POST" action="/currencies/<?= $currency->id ?>">
                        <input type="hidden" name="_method" value="PUT">
                        
                        <div class="row">
                            <!-- Basic Information -->
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="code" class="form-label"><?= t('currencies.code') ?> <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="code" name="code" required
                                           maxlength="3" value="<?= htmlspecialchars($currency->code) ?>"
                                           style="text-transform: uppercase;" <?= $isReadOnly ? 'readonly' : '' ?>>
                                    <div class="form-text"><?= t('currencies.code_help') ?></div>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="name" class="form-label"><?= t('currencies.name') ?> <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="name" name="name" required
                                           value="<?= htmlspecialchars($currency->name) ?>"
                                           <?= $isReadOnly ? 'readonly' : '' ?>>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="symbol" class="form-label"><?= t('currencies.symbol') ?> <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="symbol" name="symbol" required
                                           maxlength="5" value="<?= htmlspecialchars($currency->symbol) ?>"
                                           <?= $isReadOnly ? 'readonly' : '' ?>>
                                    <div class="form-text"><?= t('currencies.symbol_help') ?></div>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="decimal_places" class="form-label"><?= t('currencies.decimal_places') ?></label>
                                    <select class="form-select" id="decimal_places" name="decimal_places" <?= $isReadOnly ? 'disabled' : '' ?>>
                                        <option value="0" <?= $currency->decimal_places == 0 ? 'selected' : '' ?>>0 (1, 2, 3)</option>
                                        <option value="2" <?= $currency->decimal_places == 2 ? 'selected' : '' ?>>2 (1.00, 2.50, 3.75)</option>
                                        <option value="3" <?= $currency->decimal_places == 3 ? 'selected' : '' ?>>3 (1.000, 2.500, 3.750)</option>
                                        <option value="4" <?= $currency->decimal_places == 4 ? 'selected' : '' ?>>4 (1.0000, 2.5000, 3.7500)</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- Exchange Rate Information -->
                        <div class="card mb-4">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h6 class="card-title mb-0"><?= t('currencies.exchange_rate_info') ?></h6>
                                <?php if (!$isReadOnly): ?>
                                <button type="button" class="btn btn-sm btn-outline-info" onclick="showRateHistory()">
                                    <i class="fas fa-history"></i> <?= t('currencies.rate_history') ?>
                                </button>
                                <?php endif; ?>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="exchange_rate" class="form-label"><?= t('currencies.exchange_rate') ?> <span class="text-danger">*</span></label>
                                            <div class="input-group">
                                                <span class="input-group-text">1 <span id="base-currency"><?= $baseCurrency->code ?? 'USD' ?></span> =</span>
                                                <input type="number" class="form-control" id="exchange_rate" name="exchange_rate" 
                                                       step="0.00001" min="0" required 
                                                       value="<?= $currency->exchange_rate ?>"
                                                       <?= $isReadOnly ? 'readonly' : '' ?>>
                                                <span class="input-group-text"><?= $currency->code ?></span>
                                            </div>
                                            <?php if (isset($currency->rate_change) && $currency->rate_change != 0): ?>
                                            <div class="form-text text-<?= $currency->rate_change > 0 ? 'success' : 'danger' ?>">
                                                <i class="fas fa-arrow-<?= $currency->rate_change > 0 ? 'up' : 'down' ?>"></i>
                                                <?= abs($currency->rate_change) ?>% <?= t('currencies.since_last_update') ?>
                                            </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <?php if (!$isReadOnly): ?>
                                        <div class="mb-3">
                                            <label class="form-label"><?= t('currencies.rate_source') ?></label>
                                            <div class="btn-group w-100" role="group">
                                                <input type="radio" class="btn-check" name="rate_source" id="manual" 
                                                       value="manual" <?= $currency->rate_source === 'manual' ? 'checked' : '' ?>>
                                                <label class="btn btn-outline-primary" for="manual"><?= t('currencies.manual') ?></label>
                                                
                                                <input type="radio" class="btn-check" name="rate_source" id="api" 
                                                       value="api" <?= $currency->rate_source === 'api' ? 'checked' : '' ?>>
                                                <label class="btn btn-outline-primary" for="api"><?= t('currencies.api') ?></label>
                                            </div>
                                        </div>
                                        
                                        <div id="api-options" class="<?= $currency->rate_source === 'api' ? '' : 'd-none' ?>">
                                            <button type="button" class="btn btn-sm btn-info" onclick="fetchLiveRate()">
                                                <i class="fas fa-sync-alt"></i> <?= t('currencies.fetch_live_rate') ?>
                                            </button>
                                            <div class="form-text small">
                                                <?= t('currencies.last_updated') ?>: <?= $currency->rate_updated_at ? date('M j, Y g:i A', strtotime($currency->rate_updated_at)) : t('common.never') ?>
                                            </div>
                                        </div>
                                        <?php else: ?>
                                        <div class="mb-3">
                                            <label class="form-label"><?= t('currencies.rate_source') ?></label>
                                            <p class="form-control-plaintext"><?= t('currencies.' . $currency->rate_source) ?></p>
                                            <div class="form-text small">
                                                <?= t('currencies.last_updated') ?>: <?= $currency->rate_updated_at ? date('M j, Y g:i A', strtotime($currency->rate_updated_at)) : t('common.never') ?>
                                            </div>
                                        </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Display Options -->
                        <div class="card mb-4">
                            <div class="card-header">
                                <h6 class="card-title mb-0"><?= t('currencies.display_options') ?></h6>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="position" class="form-label"><?= t('currencies.symbol_position') ?></label>
                                            <select class="form-select" id="position" name="position" <?= $isReadOnly ? 'disabled' : '' ?>>
                                                <option value="before" <?= $currency->position === 'before' ? 'selected' : '' ?>><?= t('currencies.before_amount') ?> ($100)</option>
                                                <option value="after" <?= $currency->position === 'after' ? 'selected' : '' ?>><?= t('currencies.after_amount') ?> (100$)</option>
                                            </select>
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="separator" class="form-label"><?= t('currencies.thousand_separator') ?></label>
                                            <select class="form-select" id="separator" name="separator" <?= $isReadOnly ? 'disabled' : '' ?>>
                                                <option value="," <?= $currency->separator === ',' ? 'selected' : '' ?>><?= t('currencies.comma') ?> (1,000)</option>
                                                <option value="." <?= $currency->separator === '.' ? 'selected' : '' ?>><?= t('currencies.period') ?> (1.000)</option>
                                                <option value=" " <?= $currency->separator === ' ' ? 'selected' : '' ?>><?= t('currencies.space') ?> (1 000)</option>
                                                <option value="" <?= $currency->separator === '' ? 'selected' : '' ?>><?= t('currencies.none') ?> (1000)</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="mb-3">
                                            <label class="form-label"><?= t('currencies.preview') ?></label>
                                            <div class="alert alert-light">
                                                <strong><?= t('currencies.formatted_example') ?>:</strong>
                                                <span id="format-preview">$1,234.56</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Status Options -->
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-check form-switch mb-3">
                                    <input class="form-check-input" type="checkbox" id="is_active" name="is_active" 
                                           <?= $currency->is_active ? 'checked' : '' ?> <?= $isReadOnly ? 'disabled' : '' ?>>
                                    <label class="form-check-label" for="is_active">
                                        <?= t('currencies.is_active') ?>
                                    </label>
                                    <div class="form-text"><?= t('currencies.is_active_help') ?></div>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-check form-switch mb-3">
                                    <input class="form-check-input" type="checkbox" id="is_default" name="is_default" 
                                           <?= $currency->is_default ? 'checked' : '' ?> <?= $isReadOnly ? 'disabled' : '' ?>>
                                    <label class="form-check-label" for="is_default">
                                        <?= t('currencies.set_as_default') ?>
                                    </label>
                                    <div class="form-text"><?= t('currencies.set_as_default_help') ?></div>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="notes" class="form-label"><?= t('common.notes') ?></label>
                            <textarea class="form-control" id="notes" name="notes" rows="3" 
                                      placeholder="<?= t('currencies.notes_placeholder') ?>"
                                      <?= $isReadOnly ? 'readonly' : '' ?>><?= htmlspecialchars($currency->notes ?? '') ?></textarea>
                        </div>

                        <?php if (!$isReadOnly): ?>
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> <?= t('common.save_changes') ?>
                            </button>
                            <a href="/currencies" class="btn btn-secondary">
                                <?= t('common.cancel') ?>
                            </a>
                        </div>
                        <?php endif; ?>
                    </form>
                </div>
            </div>
        </div>

        <!-- Info Card -->
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <h6 class="card-title mb-0">
                        <i class="fas fa-chart-line me-2"></i><?= t('currencies.currency_stats') ?>
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-6">
                            <div class="border-end">
                                <div class="fs-5 fw-bold text-primary"><?= $currency->usage_count ?? 0 ?></div>
                                <div class="small text-muted"><?= t('currencies.total_usage') ?></div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="fs-5 fw-bold text-success">
                                <?= $currency->transactions_count ?? 0 ?>
                            </div>
                            <div class="small text-muted"><?= t('currencies.transactions') ?></div>
                        </div>
                    </div>
                    
                    <hr>
                    
                    <h6><?= t('currencies.recent_activity') ?></h6>
                    <div class="small">
                        <div class="d-flex justify-content-between mb-2">
                            <span><?= t('currencies.created') ?>:</span>
                            <span class="text-muted"><?= date('M j, Y', strtotime($currency->created_at)) ?></span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span><?= t('currencies.last_modified') ?>:</span>
                            <span class="text-muted"><?= date('M j, Y', strtotime($currency->updated_at)) ?></span>
                        </div>
                        <?php if ($currency->rate_updated_at): ?>
                        <div class="d-flex justify-content-between mb-2">
                            <span><?= t('currencies.rate_updated') ?>:</span>
                            <span class="text-muted"><?= date('M j, Y g:i A', strtotime($currency->rate_updated_at)) ?></span>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <!-- Quick Actions -->
            <div class="card mt-3">
                <div class="card-header">
                    <h6 class="card-title mb-0">
                        <i class="fas fa-bolt me-2"></i><?= t('common.quick_actions') ?>
                    </h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="/currencies/<?= $currency->id ?>" class="btn btn-outline-info btn-sm">
                            <i class="fas fa-eye"></i> <?= t('common.view_details') ?>
                        </a>
                        <a href="/reports/currency/<?= $currency->id ?>" class="btn btn-outline-primary btn-sm">
                            <i class="fas fa-chart-bar"></i> <?= t('currencies.usage_report') ?>
                        </a>
                        <?php if (!$isReadOnly): ?>
                        <button class="btn btn-outline-success btn-sm" onclick="duplicateCurrency()">
                            <i class="fas fa-copy"></i> <?= t('currencies.duplicate') ?>
                        </button>
                        <?php endif; ?>
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

<script>
// Real-time format preview
function updateFormatPreview() {
    const symbol = document.getElementById('symbol').value || '$';
    const position = document.getElementById('position').value;
    const separator = document.getElementById('separator').value;
    const decimalPlaces = parseInt(document.getElementById('decimal_places').value);
    
    let amount = '1234.56';
    if (decimalPlaces === 0) amount = '1235';
    else if (decimalPlaces === 3) amount = '1234.560';
    else if (decimalPlaces === 4) amount = '1234.5600';
    
    // Add thousand separator
    if (separator) {
        amount = amount.replace(/\B(?=(\d{3})+(?!\d))/g, separator);
    }
    
    const formatted = position === 'before' ? symbol + amount : amount + symbol;
    document.getElementById('format-preview').textContent = formatted;
}

// Fetch live exchange rate
async function fetchLiveRate() {
    const code = '<?= $currency->code ?>';
    
    const button = event.target;
    const originalText = button.innerHTML;
    button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> <?= t('currencies.fetching') ?>';
    button.disabled = true;
    
    try {
        const response = await fetch('/api/exchange-rates/live/' + code);
        const data = await response.json();
        
        if (data.success) {
            const currentRate = parseFloat(document.getElementById('exchange_rate').value);
            const newRate = parseFloat(data.rate);
            const change = ((newRate - currentRate) / currentRate * 100).toFixed(2);
            
            document.getElementById('exchange_rate').value = data.rate;
            showAlert('success', '<?= t('currencies.rate_updated') ?>' + (change !== '0.00' ? ` (${change > 0 ? '+' : ''}${change}%)` : ''));
            updateFormatPreview();
        } else {
            showAlert('error', data.message || '<?= t('currencies.rate_fetch_failed') ?>');
        }
    } catch (error) {
        console.error('Exchange rate fetch error:', error);
        showAlert('error', '<?= t('currencies.rate_fetch_error') ?>');
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

// Duplicate currency
function duplicateCurrency() {
    if (confirm('<?= t('currencies.confirm_duplicate') ?>')) {
        window.location.href = '/currencies/create?duplicate_from=<?= $currency->id ?>';
    }
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

// Event listeners
document.addEventListener('DOMContentLoaded', function() {
    // Update format preview on input changes
    ['symbol', 'position', 'separator', 'decimal_places'].forEach(id => {
        const element = document.getElementById(id);
        if (element) {
            element.addEventListener('input', updateFormatPreview);
            element.addEventListener('change', updateFormatPreview);
        }
    });
    
    // Toggle API options
    document.querySelectorAll('input[name="rate_source"]').forEach(radio => {
        radio.addEventListener('change', function() {
            const apiOptions = document.getElementById('api-options');
            if (this.value === 'api') {
                apiOptions.classList.remove('d-none');
            } else {
                apiOptions.classList.add('d-none');
            }
        });
    });
    
    <?php if (!$isReadOnly): ?>
    // Form validation
    document.getElementById('currencyForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        // Validate required fields
        const requiredFields = ['code', 'name', 'symbol', 'exchange_rate'];
        let isValid = true;
        
        requiredFields.forEach(field => {
            const input = document.getElementById(field);
            if (!input.value.trim()) {
                input.classList.add('is-invalid');
                isValid = false;
            } else {
                input.classList.remove('is-invalid');
            }
        });
        
        if (!isValid) {
            showAlert('error', '<?= t('messages.error.required_fields') ?>');
            return;
        }
        
        // Submit form
        this.submit();
    });
    <?php endif; ?>
    
    // Initialize format preview
    updateFormatPreview();
});
</script>

<style>
.currency-example {
    font-family: 'Courier New', monospace;
    background-color: #f8f9fa;
    padding: 8px 12px;
    border-radius: 4px;
    border: 1px solid #dee2e6;
}
</style>