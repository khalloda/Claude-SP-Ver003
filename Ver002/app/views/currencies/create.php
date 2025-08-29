<?php
/**
 * File: app/views/currencies/create.php
 * Purpose: Currency creation form with exchange rate setup
 * Layout: Uses app layout with form validation
 */

$this->layout('layouts/app', [
    'title' => $page_title ?? t('currencies.add_currency'),
    'active_nav' => 'currencies'
]);

$currentUser = $this->getCurrentUser();
$canCreate = $this->hasRole(['admin', 'manager']);
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">
            <i class="fas fa-coins me-2"></i><?= t('currencies.add_currency') ?>
        </h1>
        <div class="btn-toolbar mb-2 mb-md-0">
            <a href="/currencies" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left"></i> <?= t('common.back') ?>
            </a>
        </div>
    </div>

    <?php if (!$canCreate): ?>
    <div class="alert alert-danger">
        <i class="fas fa-exclamation-triangle me-2"></i>
        <?= t('messages.error.insufficient_permissions') ?>
    </div>
    <?php else: ?>
    
    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0"><?= t('currencies.currency_details') ?></h5>
                </div>
                <div class="card-body">
                    <form id="currencyForm" method="POST" action="/currencies">
                        <div class="row">
                            <!-- Basic Information -->
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="code" class="form-label"><?= t('currencies.code') ?> <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="code" name="code" required
                                           maxlength="3" placeholder="USD" style="text-transform: uppercase;">
                                    <div class="form-text"><?= t('currencies.code_help') ?></div>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="name" class="form-label"><?= t('currencies.name') ?> <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="name" name="name" required
                                           placeholder="<?= t('currencies.name_placeholder') ?>">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="symbol" class="form-label"><?= t('currencies.symbol') ?> <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="symbol" name="symbol" required
                                           maxlength="5" placeholder="$">
                                    <div class="form-text"><?= t('currencies.symbol_help') ?></div>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="decimal_places" class="form-label"><?= t('currencies.decimal_places') ?></label>
                                    <select class="form-select" id="decimal_places" name="decimal_places">
                                        <option value="0">0 (1, 2, 3)</option>
                                        <option value="2" selected>2 (1.00, 2.50, 3.75)</option>
                                        <option value="3">3 (1.000, 2.500, 3.750)</option>
                                        <option value="4">4 (1.0000, 2.5000, 3.7500)</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- Exchange Rate Information -->
                        <div class="card mb-4">
                            <div class="card-header">
                                <h6 class="card-title mb-0"><?= t('currencies.exchange_rate_info') ?></h6>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="exchange_rate" class="form-label"><?= t('currencies.exchange_rate') ?> <span class="text-danger">*</span></label>
                                            <div class="input-group">
                                                <span class="input-group-text">1 <span id="base-currency">USD</span> =</span>
                                                <input type="number" class="form-control" id="exchange_rate" name="exchange_rate" 
                                                       step="0.00001" min="0" required placeholder="1.00000">
                                                <span class="input-group-text" id="target-currency">XXX</span>
                                            </div>
                                            <div class="form-text"><?= t('currencies.exchange_rate_help') ?></div>
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label"><?= t('currencies.rate_source') ?></label>
                                            <div class="btn-group w-100" role="group">
                                                <input type="radio" class="btn-check" name="rate_source" id="manual" value="manual" checked>
                                                <label class="btn btn-outline-primary" for="manual"><?= t('currencies.manual') ?></label>
                                                
                                                <input type="radio" class="btn-check" name="rate_source" id="api" value="api">
                                                <label class="btn btn-outline-primary" for="api"><?= t('currencies.api') ?></label>
                                            </div>
                                        </div>
                                        
                                        <div id="api-options" class="d-none">
                                            <button type="button" class="btn btn-sm btn-info" onclick="fetchLiveRate()">
                                                <i class="fas fa-sync-alt"></i> <?= t('currencies.fetch_live_rate') ?>
                                            </button>
                                        </div>
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
                                            <select class="form-select" id="position" name="position">
                                                <option value="before"><?= t('currencies.before_amount') ?> ($100)</option>
                                                <option value="after"><?= t('currencies.after_amount') ?> (100$)</option>
                                            </select>
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="separator" class="form-label"><?= t('currencies.thousand_separator') ?></label>
                                            <select class="form-select" id="separator" name="separator">
                                                <option value=","><?= t('currencies.comma') ?> (1,000)</option>
                                                <option value="."><?= t('currencies.period') ?> (1.000)</option>
                                                <option value=" "><?= t('currencies.space') ?> (1 000)</option>
                                                <option value=""><?= t('currencies.none') ?> (1000)</option>
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
                                    <input class="form-check-input" type="checkbox" id="is_active" name="is_active" checked>
                                    <label class="form-check-label" for="is_active">
                                        <?= t('currencies.is_active') ?>
                                    </label>
                                    <div class="form-text"><?= t('currencies.is_active_help') ?></div>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-check form-switch mb-3">
                                    <input class="form-check-input" type="checkbox" id="is_default" name="is_default">
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
                                      placeholder="<?= t('currencies.notes_placeholder') ?>"></textarea>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> <?= t('common.save') ?>
                            </button>
                            <button type="button" class="btn btn-success" onclick="saveAndContinue()">
                                <i class="fas fa-plus"></i> <?= t('common.save_and_add_another') ?>
                            </button>
                            <a href="/currencies" class="btn btn-secondary">
                                <?= t('common.cancel') ?>
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Help Card -->
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <h6 class="card-title mb-0">
                        <i class="fas fa-info-circle me-2"></i><?= t('currencies.help_title') ?>
                    </h6>
                </div>
                <div class="card-body">
                    <h6><?= t('currencies.common_currencies') ?></h6>
                    <div class="list-group list-group-flush">
                        <div class="list-group-item px-0 py-2">
                            <div class="d-flex justify-content-between">
                                <span>USD - US Dollar</span>
                                <span class="text-muted">$</span>
                            </div>
                        </div>
                        <div class="list-group-item px-0 py-2">
                            <div class="d-flex justify-content-between">
                                <span>EUR - Euro</span>
                                <span class="text-muted">€</span>
                            </div>
                        </div>
                        <div class="list-group-item px-0 py-2">
                            <div class="d-flex justify-content-between">
                                <span>GBP - British Pound</span>
                                <span class="text-muted">£</span>
                            </div>
                        </div>
                        <div class="list-group-item px-0 py-2">
                            <div class="d-flex justify-content-between">
                                <span>JPY - Japanese Yen</span>
                                <span class="text-muted">¥</span>
                            </div>
                        </div>
                        <div class="list-group-item px-0 py-2">
                            <div class="d-flex justify-content-between">
                                <span>AED - UAE Dirham</span>
                                <span class="text-muted">د.إ</span>
                            </div>
                        </div>
                        <div class="list-group-item px-0 py-2">
                            <div class="d-flex justify-content-between">
                                <span>SAR - Saudi Riyal</span>
                                <span class="text-muted">ر.س</span>
                            </div>
                        </div>
                    </div>
                    
                    <hr>
                    
                    <h6><?= t('currencies.tips') ?></h6>
                    <ul class="small text-muted">
                        <li><?= t('currencies.tip_code') ?></li>
                        <li><?= t('currencies.tip_rate') ?></li>
                        <li><?= t('currencies.tip_default') ?></li>
                        <li><?= t('currencies.tip_precision') ?></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
    
    <?php endif; ?>
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
    document.getElementById('target-currency').textContent = document.getElementById('code').value || 'XXX';
}

// Auto-populate common currency data
const commonCurrencies = {
    'USD': { name: 'US Dollar', symbol: '$', decimal_places: 2 },
    'EUR': { name: 'Euro', symbol: '€', decimal_places: 2 },
    'GBP': { name: 'British Pound', symbol: '£', decimal_places: 2 },
    'JPY': { name: 'Japanese Yen', symbol: '¥', decimal_places: 0 },
    'AED': { name: 'UAE Dirham', symbol: 'د.إ', decimal_places: 2 },
    'SAR': { name: 'Saudi Riyal', symbol: 'ر.س', decimal_places: 2 }
};

// Fetch live exchange rate
async function fetchLiveRate() {
    const code = document.getElementById('code').value.toUpperCase();
    if (!code || code.length !== 3) {
        showAlert('warning', '<?= t('currencies.enter_currency_code_first') ?>');
        return;
    }
    
    const button = event.target;
    const originalText = button.innerHTML;
    button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> <?= t('currencies.fetching') ?>';
    button.disabled = true;
    
    try {
        const response = await fetch('/api/exchange-rates/live/' + code);
        const data = await response.json();
        
        if (data.success) {
            document.getElementById('exchange_rate').value = data.rate;
            showAlert('success', '<?= t('currencies.rate_updated') ?>');
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

// Save and continue function
function saveAndContinue() {
    const form = document.getElementById('currencyForm');
    const formData = new FormData(form);
    formData.append('continue', 'true');
    
    fetch('/currencies', {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert('success', data.message);
            form.reset();
            document.getElementById('is_active').checked = true;
            updateFormatPreview();
        } else {
            showAlert('error', data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('error', '<?= t('messages.error.general') ?>');
    });
}

// Event listeners
document.addEventListener('DOMContentLoaded', function() {
    // Update format preview on input changes
    ['symbol', 'position', 'separator', 'decimal_places', 'code'].forEach(id => {
        const element = document.getElementById(id);
        if (element) {
            element.addEventListener('input', updateFormatPreview);
            element.addEventListener('change', updateFormatPreview);
        }
    });
    
    // Auto-populate currency data
    document.getElementById('code').addEventListener('input', function() {
        const code = this.value.toUpperCase();
        this.value = code;
        
        if (commonCurrencies[code]) {
            const currency = commonCurrencies[code];
            document.getElementById('name').value = currency.name;
            document.getElementById('symbol').value = currency.symbol;
            document.getElementById('decimal_places').value = currency.decimal_places;
            updateFormatPreview();
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