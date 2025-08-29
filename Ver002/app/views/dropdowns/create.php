<?php
/**
 * File: app/views/dropdowns/create.php
 * Purpose: Create new dropdown item form with type-specific fields
 * Layout: Uses app layout with dynamic form fields based on type
 */

$this->layout('layouts/app', [
    'title' => $page_title ?? t('dropdowns.add_item'),
    'active_nav' => 'dropdowns'
]);

$currentUser = $this->getCurrentUser();
$canCreate = $this->hasRole(['admin', 'manager']);
$selectedType = $type ?? $_GET['type'] ?? '';
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">
            <i class="fas fa-plus me-2"></i><?= t('dropdowns.add_item') ?>
            <?php if ($selectedType): ?>
            <span class="badge bg-primary ms-2"><?= t('dropdowns.' . $selectedType) ?></span>
            <?php endif; ?>
        </h1>
        <div class="btn-toolbar mb-2 mb-md-0">
            <a href="/dropdowns" class="btn btn-outline-secondary">
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
                    <h5 class="card-title mb-0"><?= t('dropdowns.item_details') ?></h5>
                </div>
                <div class="card-body">
                    <form id="dropdownForm" method="POST" action="/dropdowns">
                        <!-- Type Selection -->
                        <div class="mb-4">
                            <label for="type" class="form-label"><?= t('dropdowns.type') ?> <span class="text-danger">*</span></label>
                            <select class="form-select" id="type" name="type" required onchange="updateFormFields()">
                                <option value=""><?= t('common.select_type') ?></option>
                                <option value="category" <?= $selectedType === 'category' ? 'selected' : '' ?>><?= t('dropdowns.categories') ?></option>
                                <option value="unit" <?= $selectedType === 'unit' ? 'selected' : '' ?>><?= t('dropdowns.units') ?></option>
                                <option value="status" <?= $selectedType === 'status' ? 'selected' : '' ?>><?= t('dropdowns.statuses') ?></option>
                                <option value="priority" <?= $selectedType === 'priority' ? 'selected' : '' ?>><?= t('dropdowns.priorities') ?></option>
                                <option value="country" <?= $selectedType === 'country' ? 'selected' : '' ?>><?= t('dropdowns.countries') ?></option>
                            </select>
                        </div>

                        <div id="typeFields">
                            <!-- Basic Fields (Always Visible) -->
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="name" class="form-label"><?= t('common.name') ?> <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="name" name="name" required
                                               placeholder="<?= t('dropdowns.name_placeholder') ?>">
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="code" class="form-label"><?= t('dropdowns.code') ?></label>
                                        <input type="text" class="form-control" id="code" name="code" 
                                               placeholder="<?= t('dropdowns.code_placeholder') ?>"
                                               style="text-transform: uppercase;">
                                        <div class="form-text"><?= t('dropdowns.code_help') ?></div>
                                    </div>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="description" class="form-label"><?= t('common.description') ?></label>
                                <textarea class="form-control" id="description" name="description" rows="3"
                                          placeholder="<?= t('dropdowns.description_placeholder') ?>"></textarea>
                            </div>

                            <!-- Type-Specific Fields -->
                            <div id="specificFields">
                                <!-- Category Fields -->
                                <div id="categoryFields" class="type-fields d-none">
                                    <h6 class="mt-4 mb-3"><?= t('dropdowns.category_settings') ?></h6>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="parent_id" class="form-label"><?= t('dropdowns.parent_category') ?></label>
                                                <select class="form-select" id="parent_id" name="parent_id">
                                                    <option value=""><?= t('dropdowns.no_parent') ?></option>
                                                    <?php foreach ($parentCategories ?? [] as $parent): ?>
                                                    <option value="<?= $parent->id ?>"><?= htmlspecialchars($parent->name) ?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="color" class="form-label"><?= t('dropdowns.color') ?></label>
                                                <input type="color" class="form-control form-control-color" id="color" name="color" value="#007bff">
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Unit Fields -->
                                <div id="unitFields" class="type-fields d-none">
                                    <h6 class="mt-4 mb-3"><?= t('dropdowns.unit_settings') ?></h6>
                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="mb-3">
                                                <label for="symbol" class="form-label"><?= t('dropdowns.symbol') ?></label>
                                                <input type="text" class="form-control" id="symbol" name="symbol" 
                                                       placeholder="kg, m, pcs">
                                                <div class="form-text"><?= t('dropdowns.symbol_help') ?></div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="mb-3">
                                                <label for="base_unit" class="form-label"><?= t('dropdowns.base_unit') ?></label>
                                                <input type="text" class="form-control" id="base_unit" name="base_unit" 
                                                       placeholder="kilogram, meter">
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="mb-3">
                                                <label for="conversion_factor" class="form-label"><?= t('dropdowns.conversion_factor') ?></label>
                                                <input type="number" class="form-control" id="conversion_factor" name="conversion_factor" 
                                                       step="0.001" value="1.000">
                                                <div class="form-text"><?= t('dropdowns.conversion_help') ?></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Status Fields -->
                                <div id="statusFields" class="type-fields d-none">
                                    <h6 class="mt-4 mb-3"><?= t('dropdowns.status_settings') ?></h6>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="color" class="form-label"><?= t('dropdowns.color') ?></label>
                                                <div class="btn-group w-100" role="group">
                                                    <input type="radio" class="btn-check" name="color" id="success" value="#28a745">
                                                    <label class="btn btn-outline-success" for="success"><?= t('dropdowns.green') ?></label>
                                                    
                                                    <input type="radio" class="btn-check" name="color" id="warning" value="#ffc107">
                                                    <label class="btn btn-outline-warning" for="warning"><?= t('dropdowns.yellow') ?></label>
                                                    
                                                    <input type="radio" class="btn-check" name="color" id="danger" value="#dc3545">
                                                    <label class="btn btn-outline-danger" for="danger"><?= t('dropdowns.red') ?></label>
                                                    
                                                    <input type="radio" class="btn-check" name="color" id="info" value="#17a2b8">
                                                    <label class="btn btn-outline-info" for="info"><?= t('dropdowns.blue') ?></label>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label"><?= t('dropdowns.status_type') ?></label>
                                                <div class="btn-group w-100" role="group">
                                                    <input type="radio" class="btn-check" name="status_type" id="active_status" value="active" checked>
                                                    <label class="btn btn-outline-success" for="active_status"><?= t('dropdowns.active_status') ?></label>
                                                    
                                                    <input type="radio" class="btn-check" name="status_type" id="inactive_status" value="inactive">
                                                    <label class="btn btn-outline-secondary" for="inactive_status"><?= t('dropdowns.inactive_status') ?></label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Priority Fields -->
                                <div id="priorityFields" class="type-fields d-none">
                                    <h6 class="mt-4 mb-3"><?= t('dropdowns.priority_settings') ?></h6>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="level" class="form-label"><?= t('dropdowns.priority_level') ?></label>
                                                <select class="form-select" id="level" name="level">
                                                    <option value="1"><?= t('dropdowns.lowest') ?></option>
                                                    <option value="2"><?= t('dropdowns.low') ?></option>
                                                    <option value="3" selected><?= t('dropdowns.normal') ?></option>
                                                    <option value="4"><?= t('dropdowns.high') ?></option>
                                                    <option value="5"><?= t('dropdowns.highest') ?></option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="color" class="form-label"><?= t('dropdowns.color') ?></label>
                                                <input type="color" class="form-control form-control-color" id="color" name="color" value="#6c757d">
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Country Fields -->
                                <div id="countryFields" class="type-fields d-none">
                                    <h6 class="mt-4 mb-3"><?= t('dropdowns.country_settings') ?></h6>
                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="mb-3">
                                                <label for="iso_code" class="form-label"><?= t('dropdowns.iso_code') ?></label>
                                                <input type="text" class="form-control" id="iso_code" name="iso_code" 
                                                       maxlength="2" placeholder="US"
                                                       style="text-transform: uppercase;">
                                                <div class="form-text"><?= t('dropdowns.iso_code_help') ?></div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="mb-3">
                                                <label for="phone_code" class="form-label"><?= t('dropdowns.phone_code') ?></label>
                                                <input type="text" class="form-control" id="phone_code" name="phone_code" 
                                                       placeholder="+1">
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="mb-3">
                                                <label for="currency_code" class="form-label"><?= t('dropdowns.currency_code') ?></label>
                                                <select class="form-select" id="currency_code" name="currency_code">
                                                    <option value=""><?= t('common.select') ?></option>
                                                    <?php foreach ($currencies ?? [] as $currency): ?>
                                                    <option value="<?= $currency->code ?>"><?= $currency->code ?> - <?= $currency->name ?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Common Fields -->
                            <div class="row mt-4">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="sort_order" class="form-label"><?= t('dropdowns.sort_order') ?></label>
                                        <input type="number" class="form-control" id="sort_order" name="sort_order" 
                                               value="0" min="0" max="999">
                                        <div class="form-text"><?= t('dropdowns.sort_order_help') ?></div>
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" id="is_active" name="is_active" checked>
                                            <label class="form-check-label" for="is_active">
                                                <?= t('dropdowns.is_active') ?>
                                            </label>
                                            <div class="form-text"><?= t('dropdowns.is_active_help') ?></div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save"></i> <?= t('common.save') ?>
                                </button>
                                <button type="button" class="btn btn-success" onclick="saveAndContinue()">
                                    <i class="fas fa-plus"></i> <?= t('common.save_and_add_another') ?>
                                </button>
                                <a href="/dropdowns" class="btn btn-secondary">
                                    <?= t('common.cancel') ?>
                                </a>
                            </div>
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
                        <i class="fas fa-info-circle me-2"></i><?= t('dropdowns.help_title') ?>
                    </h6>
                </div>
                <div class="card-body">
                    <div id="helpContent">
                        <div id="generalHelp">
                            <h6><?= t('dropdowns.general_tips') ?></h6>
                            <ul class="small text-muted">
                                <li><?= t('dropdowns.tip_name') ?></li>
                                <li><?= t('dropdowns.tip_code') ?></li>
                                <li><?= t('dropdowns.tip_sort') ?></li>
                                <li><?= t('dropdowns.tip_active') ?></li>
                            </ul>
                        </div>
                        
                        <div id="categoryHelp" class="help-content d-none">
                            <h6><?= t('dropdowns.category_help') ?></h6>
                            <ul class="small text-muted">
                                <li><?= t('dropdowns.category_tip_1') ?></li>
                                <li><?= t('dropdowns.category_tip_2') ?></li>
                                <li><?= t('dropdowns.category_tip_3') ?></li>
                            </ul>
                        </div>
                        
                        <div id="unitHelp" class="help-content d-none">
                            <h6><?= t('dropdowns.unit_help') ?></h6>
                            <ul class="small text-muted">
                                <li><?= t('dropdowns.unit_tip_1') ?></li>
                                <li><?= t('dropdowns.unit_tip_2') ?></li>
                                <li><?= t('dropdowns.unit_tip_3') ?></li>
                            </ul>
                        </div>
                        
                        <div id="statusHelp" class="help-content d-none">
                            <h6><?= t('dropdowns.status_help') ?></h6>
                            <ul class="small text-muted">
                                <li><?= t('dropdowns.status_tip_1') ?></li>
                                <li><?= t('dropdowns.status_tip_2') ?></li>
                                <li><?= t('dropdowns.status_tip_3') ?></li>
                            </ul>
                        </div>
                        
                        <div id="priorityHelp" class="help-content d-none">
                            <h6><?= t('dropdowns.priority_help') ?></h6>
                            <ul class="small text-muted">
                                <li><?= t('dropdowns.priority_tip_1') ?></li>
                                <li><?= t('dropdowns.priority_tip_2') ?></li>
                                <li><?= t('dropdowns.priority_tip_3') ?></li>
                            </ul>
                        </div>
                        
                        <div id="countryHelp" class="help-content d-none">
                            <h6><?= t('dropdowns.country_help') ?></h6>
                            <ul class="small text-muted">
                                <li><?= t('dropdowns.country_tip_1') ?></li>
                                <li><?= t('dropdowns.country_tip_2') ?></li>
                                <li><?= t('dropdowns.country_tip_3') ?></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Examples -->
            <div class="card mt-3">
                <div class="card-header">
                    <h6 class="card-title mb-0">
                        <i class="fas fa-lightbulb me-2"></i><?= t('dropdowns.examples') ?>
                    </h6>
                </div>
                <div class="card-body">
                    <div id="examplesContent">
                        <div id="generalExamples">
                            <p class="small text-muted"><?= t('dropdowns.select_type_to_see_examples') ?></p>
                        </div>
                        
                        <div id="categoryExamples" class="examples-content d-none">
                            <div class="small">
                                <strong><?= t('dropdowns.example_categories') ?>:</strong><br>
                                • Electronics<br>
                                • Office Supplies<br>
                                • Raw Materials<br>
                                • Safety Equipment
                            </div>
                        </div>
                        
                        <div id="unitExamples" class="examples-content d-none">
                            <div class="small">
                                <strong><?= t('dropdowns.example_units') ?>:</strong><br>
                                • Piece (pcs)<br>
                                • Kilogram (kg)<br>
                                • Meter (m)<br>
                                • Liter (L)
                            </div>
                        </div>
                        
                        <div id="statusExamples" class="examples-content d-none">
                            <div class="small">
                                <strong><?= t('dropdowns.example_statuses') ?>:</strong><br>
                                • Active<br>
                                • Pending<br>
                                • Completed<br>
                                • Cancelled
                            </div>
                        </div>
                        
                        <div id="priorityExamples" class="examples-content d-none">
                            <div class="small">
                                <strong><?= t('dropdowns.example_priorities') ?>:</strong><br>
                                • Critical<br>
                                • High<br>
                                • Normal<br>
                                • Low
                            </div>
                        </div>
                        
                        <div id="countryExamples" class="examples-content d-none">
                            <div class="small">
                                <strong><?= t('dropdowns.example_countries') ?>:</strong><br>
                                • United States (US, +1)<br>
                                • United Kingdom (GB, +44)<br>
                                • Germany (DE, +49)<br>
                                • Japan (JP, +81)
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <?php endif; ?>
</div>

<script>
// Update form fields based on selected type
function updateFormFields() {
    const type = document.getElementById('type').value;
    
    // Hide all type-specific fields
    document.querySelectorAll('.type-fields').forEach(field => {
        field.classList.add('d-none');
    });
    
    // Hide all help content
    document.querySelectorAll('.help-content').forEach(help => {
        help.classList.add('d-none');
    });
    
    // Hide all examples content
    document.querySelectorAll('.examples-content').forEach(example => {
        example.classList.add('d-none');
    });
    
    if (type) {
        // Show type-specific fields
        const typeFields = document.getElementById(type + 'Fields');
        if (typeFields) {
            typeFields.classList.remove('d-none');
        }
        
        // Show type-specific help
        const helpContent = document.getElementById(type + 'Help');
        if (helpContent) {
            helpContent.classList.remove('d-none');
            document.getElementById('generalHelp').classList.add('d-none');
        } else {
            document.getElementById('generalHelp').classList.remove('d-none');
        }
        
        // Show type-specific examples
        const examplesContent = document.getElementById(type + 'Examples');
        if (examplesContent) {
            examplesContent.classList.remove('d-none');
            document.getElementById('generalExamples').classList.add('d-none');
        } else {
            document.getElementById('generalExamples').classList.remove('d-none');
        }
        
        // Update form placeholders and labels based on type
        updatePlaceholders(type);
    } else {
        document.getElementById('generalHelp').classList.remove('d-none');
        document.getElementById('generalExamples').classList.remove('d-none');
    }
}

// Update placeholders based on type
function updatePlaceholders(type) {
    const nameField = document.getElementById('name');
    const codeField = document.getElementById('code');
    const descField = document.getElementById('description');
    
    const placeholders = {
        'category': {
            name: '<?= t('dropdowns.category_name_placeholder') ?>',
            code: 'ELEC',
            description: '<?= t('dropdowns.category_desc_placeholder') ?>'
        },
        'unit': {
            name: '<?= t('dropdowns.unit_name_placeholder') ?>',
            code: 'KG',
            description: '<?= t('dropdowns.unit_desc_placeholder') ?>'
        },
        'status': {
            name: '<?= t('dropdowns.status_name_placeholder') ?>',
            code: 'ACTIVE',
            description: '<?= t('dropdowns.status_desc_placeholder') ?>'
        },
        'priority': {
            name: '<?= t('dropdowns.priority_name_placeholder') ?>',
            code: 'HIGH',
            description: '<?= t('dropdowns.priority_desc_placeholder') ?>'
        },
        'country': {
            name: '<?= t('dropdowns.country_name_placeholder') ?>',
            code: 'USA',
            description: '<?= t('dropdowns.country_desc_placeholder') ?>'
        }
    };
    
    if (placeholders[type]) {
        nameField.placeholder = placeholders[type].name;
        codeField.placeholder = placeholders[type].code;
        descField.placeholder = placeholders[type].description;
    }
}

// Auto-generate code from name
document.getElementById('name').addEventListener('input', function() {
    const codeField = document.getElementById('code');
    if (!codeField.value) {
        let code = this.value
            .toUpperCase()
            .replace(/[^A-Z0-9\s]/g, '')
            .split(' ')
            .map(word => word.substring(0, 3))
            .join('')
            .substring(0, 10);
        codeField.value = code;
    }
});

// Save and continue function
function saveAndContinue() {
    const form = document.getElementById('dropdownForm');
    const formData = new FormData(form);
    formData.append('continue', 'true');
    
    fetch('/dropdowns', {
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
            // Reset form but keep type
            const selectedType = document.getElementById('type').value;
            form.reset();
            document.getElementById('type').value = selectedType;
            document.getElementById('is_active').checked = true;
            updateFormFields();
        } else {
            showAlert('error', data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('error', '<?= t('messages.error.general') ?>');
    });
}

// Form validation
document.getElementById('dropdownForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    // Validate required fields
    const requiredFields = ['type', 'name'];
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

// Initialize
document.addEventListener('DOMContentLoaded', function() {
    <?php if ($selectedType): ?>
    updateFormFields();
    <?php endif; ?>
});
</script>

<style>
.form-control-color {
    width: 100%;
    height: 38px;
}

.btn-group .btn-check:checked + .btn {
    background-color: var(--bs-primary);
    border-color: var(--bs-primary);
    color: white;
}

.type-fields {
    border-top: 1px solid #dee2e6;
    padding-top: 1rem;
    margin-top: 1rem;
}

.examples-content {
    background-color: #f8f9fa;
    padding: 0.75rem;
    border-radius: 0.375rem;
    border-left: 4px solid #007bff;
}
</style>