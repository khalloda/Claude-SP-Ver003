<?php
/**
 * File: app/views/dropdowns/edit.php
 * Purpose: Edit dropdown item form with type-specific fields
 * Layout: Uses app layout with dynamic form fields based on type
 */

$this->layout('layouts/app', [
    'title' => $page_title ?? t('dropdowns.edit_item'),
    'active_nav' => 'dropdowns'
]);

$currentUser = $this->getCurrentUser();
$canEdit = $this->hasRole(['admin', 'manager']);
$canDelete = $this->hasRole(['admin']);
$isReadOnly = !$canEdit;
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">
            <i class="fas fa-edit me-2"></i><?= t('dropdowns.edit_item') ?>
            <span class="badge bg-primary ms-2"><?= t('dropdowns.' . $dropdown->type) ?></span>
            <span class="badge bg-<?= $dropdown->is_active ? 'success' : 'secondary' ?> ms-1">
                <?= $dropdown->is_active ? t('common.active') : t('common.inactive') ?>
            </span>
        </h1>
        <div class="btn-toolbar mb-2 mb-md-0">
            <div class="btn-group me-2">
                <a href="/dropdowns/<?= $dropdown->id ?>" class="btn btn-outline-info">
                    <i class="fas fa-eye"></i> <?= t('common.view') ?>
                </a>
                <a href="/dropdowns" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left"></i> <?= t('common.back') ?>
                </a>
            </div>
            <?php if ($canDelete && ($dropdown->usage_count ?? 0) == 0): ?>
            <button class="btn btn-outline-danger" onclick="deleteItem()">
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

    <?php if (($dropdown->usage_count ?? 0) > 0 && $canDelete): ?>
    <div class="alert alert-warning">
        <i class="fas fa-exclamation-triangle me-2"></i>
        <?= t('dropdowns.cannot_delete_in_use', ['count' => $dropdown->usage_count]) ?>
    </div>
    <?php endif; ?>
    
    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0"><?= t('dropdowns.item_details') ?></h5>
                </div>
                <div class="card-body">
                    <form id="dropdownForm" method="POST" action="/dropdowns/<?= $dropdown->id ?>">
                        <input type="hidden" name="_method" value="PUT">
                        
                        <!-- Type Display (Read Only) -->
                        <div class="mb-4">
                            <label class="form-label"><?= t('dropdowns.type') ?></label>
                            <p class="form-control-plaintext">
                                <span class="badge bg-primary fs-6">
                                    <i class="fas fa-<?= $this->getTypeIcon($dropdown->type) ?> me-2"></i>
                                    <?= t('dropdowns.' . $dropdown->type) ?>
                                </span>
                            </p>
                            <input type="hidden" name="type" value="<?= $dropdown->type ?>">
                        </div>

                        <div id="typeFields">
                            <!-- Basic Fields -->
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="name" class="form-label"><?= t('common.name') ?> <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="name" name="name" required
                                               value="<?= htmlspecialchars($dropdown->name) ?>"
                                               <?= $isReadOnly ? 'readonly' : '' ?>>
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="code" class="form-label"><?= t('dropdowns.code') ?></label>
                                        <input type="text" class="form-control" id="code" name="code" 
                                               value="<?= htmlspecialchars($dropdown->code ?? '') ?>"
                                               style="text-transform: uppercase;" <?= $isReadOnly ? 'readonly' : '' ?>>
                                        <div class="form-text"><?= t('dropdowns.code_help') ?></div>
                                    </div>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="description" class="form-label"><?= t('common.description') ?></label>
                                <textarea class="form-control" id="description" name="description" rows="3"
                                          <?= $isReadOnly ? 'readonly' : '' ?>><?= htmlspecialchars($dropdown->description ?? '') ?></textarea>
                            </div>

                            <!-- Type-Specific Fields -->
                            <div id="specificFields">
                                <?php if ($dropdown->type === 'category'): ?>
                                <!-- Category Fields -->
                                <div class="category-fields">
                                    <h6 class="mt-4 mb-3"><?= t('dropdowns.category_settings') ?></h6>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="parent_id" class="form-label"><?= t('dropdowns.parent_category') ?></label>
                                                <select class="form-select" id="parent_id" name="parent_id" <?= $isReadOnly ? 'disabled' : '' ?>>
                                                    <option value=""><?= t('dropdowns.no_parent') ?></option>
                                                    <?php foreach ($parentCategories ?? [] as $parent): ?>
                                                    <option value="<?= $parent->id ?>" <?= ($dropdown->parent_id ?? '') == $parent->id ? 'selected' : '' ?>>
                                                        <?= htmlspecialchars($parent->name) ?>
                                                    </option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="color" class="form-label"><?= t('dropdowns.color') ?></label>
                                                <input type="color" class="form-control form-control-color" id="color" name="color" 
                                                       value="<?= $dropdown->color ?? '#007bff' ?>" <?= $isReadOnly ? 'disabled' : '' ?>>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <?php endif; ?>

                                <?php if ($dropdown->type === 'unit'): ?>
                                <!-- Unit Fields -->
                                <div class="unit-fields">
                                    <h6 class="mt-4 mb-3"><?= t('dropdowns.unit_settings') ?></h6>
                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="mb-3">
                                                <label for="symbol" class="form-label"><?= t('dropdowns.symbol') ?></label>
                                                <input type="text" class="form-control" id="symbol" name="symbol" 
                                                       value="<?= htmlspecialchars($dropdown->symbol ?? '') ?>"
                                                       <?= $isReadOnly ? 'readonly' : '' ?>>
                                                <div class="form-text"><?= t('dropdowns.symbol_help') ?></div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="mb-3">
                                                <label for="base_unit" class="form-label"><?= t('dropdowns.base_unit') ?></label>
                                                <input type="text" class="form-control" id="base_unit" name="base_unit" 
                                                       value="<?= htmlspecialchars($dropdown->base_unit ?? '') ?>"
                                                       <?= $isReadOnly ? 'readonly' : '' ?>>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="mb-3">
                                                <label for="conversion_factor" class="form-label"><?= t('dropdowns.conversion_factor') ?></label>
                                                <input type="number" class="form-control" id="conversion_factor" name="conversion_factor" 
                                                       step="0.001" value="<?= $dropdown->conversion_factor ?? '1.000' ?>"
                                                       <?= $isReadOnly ? 'readonly' : '' ?>>
                                                <div class="form-text"><?= t('dropdowns.conversion_help') ?></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <?php endif; ?>

                                <?php if ($dropdown->type === 'status'): ?>
                                <!-- Status Fields -->
                                <div class="status-fields">
                                    <h6 class="mt-4 mb-3"><?= t('dropdowns.status_settings') ?></h6>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label"><?= t('dropdowns.color') ?></label>
                                                <?php if (!$isReadOnly): ?>
                                                <div class="btn-group w-100" role="group">
                                                    <input type="radio" class="btn-check" name="color" id="success" value="#28a745" <?= ($dropdown->color ?? '') === '#28a745' ? 'checked' : '' ?>>
                                                    <label class="btn btn-outline-success" for="success"><?= t('dropdowns.green') ?></label>
                                                    
                                                    <input type="radio" class="btn-check" name="color" id="warning" value="#ffc107" <?= ($dropdown->color ?? '') === '#ffc107' ? 'checked' : '' ?>>
                                                    <label class="btn btn-outline-warning" for="warning"><?= t('dropdowns.yellow') ?></label>
                                                    
                                                    <input type="radio" class="btn-check" name="color" id="danger" value="#dc3545" <?= ($dropdown->color ?? '') === '#dc3545' ? 'checked' : '' ?>>
                                                    <label class="btn btn-outline-danger" for="danger"><?= t('dropdowns.red') ?></label>
                                                    
                                                    <input type="radio" class="btn-check" name="color" id="info" value="#17a2b8" <?= ($dropdown->color ?? '') === '#17a2b8' ? 'checked' : '' ?>>
                                                    <label class="btn btn-outline-info" for="info"><?= t('dropdowns.blue') ?></label>
                                                </div>
                                                <?php else: ?>
                                                <div class="form-control-plaintext">
                                                    <span class="badge" style="background-color: <?= htmlspecialchars($dropdown->color ?? '#007bff') ?>;">
                                                        <?= htmlspecialchars($dropdown->color ?? '#007bff') ?>
                                                    </span>
                                                </div>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label"><?= t('dropdowns.status_type') ?></label>
                                                <?php if (!$isReadOnly): ?>
                                                <div class="btn-group w-100" role="group">
                                                    <input type="radio" class="btn-check" name="status_type" id="active_status" value="active" <?= ($dropdown->status_type ?? 'active') === 'active' ? 'checked' : '' ?>>
                                                    <label class="btn btn-outline-success" for="active_status"><?= t('dropdowns.active_status') ?></label>
                                                    
                                                    <input type="radio" class="btn-check" name="status_type" id="inactive_status" value="inactive" <?= ($dropdown->status_type ?? '') === 'inactive' ? 'checked' : '' ?>>
                                                    <label class="btn btn-outline-secondary" for="inactive_status"><?= t('dropdowns.inactive_status') ?></label>
                                                </div>
                                                <?php else: ?>
                                                <div class="form-control-plaintext">
                                                    <span class="badge bg-<?= ($dropdown->status_type ?? 'active') === 'active' ? 'success' : 'secondary' ?>">
                                                        <?= t('dropdowns.' . ($dropdown->status_type ?? 'active') . '_status') ?>
                                                    </span>
                                                </div>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <?php endif; ?>

                                <?php if ($dropdown->type === 'priority'): ?>
                                <!-- Priority Fields -->
                                <div class="priority-fields">
                                    <h6 class="mt-4 mb-3"><?= t('dropdowns.priority_settings') ?></h6>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="level" class="form-label"><?= t('dropdowns.priority_level') ?></label>
                                                <select class="form-select" id="level" name="level" <?= $isReadOnly ? 'disabled' : '' ?>>
                                                    <option value="1" <?= ($dropdown->level ?? 3) == 1 ? 'selected' : '' ?>><?= t('dropdowns.lowest') ?></option>
                                                    <option value="2" <?= ($dropdown->level ?? 3) == 2 ? 'selected' : '' ?>><?= t('dropdowns.low') ?></option>
                                                    <option value="3" <?= ($dropdown->level ?? 3) == 3 ? 'selected' : '' ?>><?= t('dropdowns.normal') ?></option>
                                                    <option value="4" <?= ($dropdown->level ?? 3) == 4 ? 'selected' : '' ?>><?= t('dropdowns.high') ?></option>
                                                    <option value="5" <?= ($dropdown->level ?? 3) == 5 ? 'selected' : '' ?>><?= t('dropdowns.highest') ?></option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="color" class="form-label"><?= t('dropdowns.color') ?></label>
                                                <input type="color" class="form-control form-control-color" id="color" name="color" 
                                                       value="<?= $dropdown->color ?? '#6c757d' ?>" <?= $isReadOnly ? 'disabled' : '' ?>>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <?php endif; ?>

                                <?php if ($dropdown->type === 'country'): ?>
                                <!-- Country Fields -->
                                <div class="country-fields">
                                    <h6 class="mt-4 mb-3"><?= t('dropdowns.country_settings') ?></h6>
                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="mb-3">
                                                <label for="iso_code" class="form-label"><?= t('dropdowns.iso_code') ?></label>
                                                <input type="text" class="form-control" id="iso_code" name="iso_code" 
                                                       maxlength="2" value="<?= htmlspecialchars($dropdown->iso_code ?? '') ?>"
                                                       style="text-transform: uppercase;" <?= $isReadOnly ? 'readonly' : '' ?>>
                                                <div class="form-text"><?= t('dropdowns.iso_code_help') ?></div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="mb-3">
                                                <label for="phone_code" class="form-label"><?= t('dropdowns.phone_code') ?></label>
                                                <input type="text" class="form-control" id="phone_code" name="phone_code" 
                                                       value="<?= htmlspecialchars($dropdown->phone_code ?? '') ?>"
                                                       <?= $isReadOnly ? 'readonly' : '' ?>>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="mb-3">
                                                <label for="currency_code" class="form-label"><?= t('dropdowns.currency_code') ?></label>
                                                <?php if (!$isReadOnly): ?>
                                                <select class="form-select" id="currency_code" name="currency_code">
                                                    <option value=""><?= t('common.select') ?></option>
                                                    <?php foreach ($currencies ?? [] as $currency): ?>
                                                    <option value="<?= $currency->code ?>" <?= ($dropdown->currency_code ?? '') === $currency->code ? 'selected' : '' ?>>
                                                        <?= $currency->code ?> - <?= $currency->name ?>
                                                    </option>
                                                    <?php endforeach; ?>
                                                </select>
                                                <?php else: ?>
                                                <div class="form-control-plaintext">
                                                    <?= htmlspecialchars($dropdown->currency_code ?? t('common.not_set')) ?>
                                                </div>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <?php endif; ?>
                            </div>

                            <!-- Common Fields -->
                            <div class="row mt-4">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="sort_order" class="form-label"><?= t('dropdowns.sort_order') ?></label>
                                        <input type="number" class="form-control" id="sort_order" name="sort_order" 
                                               value="<?= $dropdown->sort_order ?? 0 ?>" min="0" max="999"
                                               <?= $isReadOnly ? 'readonly' : '' ?>>
                                        <div class="form-text"><?= t('dropdowns.sort_order_help') ?></div>
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" id="is_active" name="is_active" 
                                                   <?= $dropdown->is_active ? 'checked' : '' ?> <?= $isReadOnly ? 'disabled' : '' ?>>
                                            <label class="form-check-label" for="is_active">
                                                <?= t('dropdowns.is_active') ?>
                                            </label>
                                            <div class="form-text"><?= t('dropdowns.is_active_help') ?></div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <?php if (!$isReadOnly): ?>
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save"></i> <?= t('common.save_changes') ?>
                                </button>
                                <a href="/dropdowns" class="btn btn-secondary">
                                    <?= t('common.cancel') ?>
                                </a>
                            </div>
                            <?php endif; ?>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Info Sidebar -->
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <h6 class="card-title mb-0">
                        <i class="fas fa-chart-line me-2"></i><?= t('dropdowns.usage_stats') ?>
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-6">
                            <div class="border-end">
                                <div class="h4 text-primary mb-1"><?= $dropdown->usage_count ?? 0 ?></div>
                                <div class="small text-muted"><?= t('dropdowns.total_usage') ?></div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="h4 text-success mb-1"><?= $dropdown->active_usage ?? 0 ?></div>
                            <div class="small text-muted"><?= t('dropdowns.active_usage') ?></div>
                        </div>
                    </div>
                    
                    <hr>
                    
                    <div class="small">
                        <div class="d-flex justify-content-between mb-2">
                            <span><?= t('dropdowns.created') ?>:</span>
                            <span class="text-muted"><?= date('M j, Y', strtotime($dropdown->created_at)) ?></span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span><?= t('dropdowns.last_modified') ?>:</span>
                            <span class="text-muted"><?= date('M j, Y', strtotime($dropdown->updated_at)) ?></span>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span><?= t('common.id') ?>:</span>
                            <span class="text-muted font-monospace"><?= $dropdown->id ?></span>
                        </div>
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
                        <a href="/dropdowns/<?= $dropdown->id ?>" class="btn btn-outline-info btn-sm">
                            <i class="fas fa-eye"></i> <?= t('common.view_details') ?>
                        </a>
                        <a href="/dropdowns?type=<?= $dropdown->type ?>" class="btn btn-outline-primary btn-sm">
                            <i class="fas fa-list"></i> <?= t('dropdowns.view_all_type', ['type' => t('dropdowns.' . $dropdown->type . 's')]) ?>
                        </a>
                        <?php if (!$isReadOnly): ?>
                        <a href="/dropdowns/create?type=<?= $dropdown->type ?>" class="btn btn-outline-success btn-sm">
                            <i class="fas fa-plus"></i> <?= t('dropdowns.add_similar') ?>
                        </a>
                        <button class="btn btn-outline-warning btn-sm" onclick="duplicateItem()">
                            <i class="fas fa-copy"></i> <?= t('dropdowns.duplicate') ?>
                        </button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <?php if (!empty($usageExamples)): ?>
            <!-- Usage Examples -->
            <div class="card mt-3">
                <div class="card-header">
                    <h6 class="card-title mb-0">
                        <i class="fas fa-list-ul me-2"></i><?= t('dropdowns.usage_examples') ?>
                    </h6>
                </div>
                <div class="card-body">
                    <div class="list-group list-group-flush">
                        <?php foreach (array_slice($usageExamples, 0, 5) as $example): ?>
                        <div class="list-group-item px-0 py-2">
                            <div class="d-flex justify-content-between align-items-center">
                                <div class="small">
                                    <strong><?= htmlspecialchars($example->entity_type) ?></strong><br>
                                    <span class="text-muted"><?= htmlspecialchars($example->entity_name) ?></span>
                                </div>
                                <a href="<?= $example->url ?>" class="btn btn-sm btn-outline-info">
                                    <i class="fas fa-external-link-alt"></i>
                                </a>
                            </div>
                        </div>
                        <?php endforeach; ?>
                        
                        <?php if (count($usageExamples) > 5): ?>
                        <div class="list-group-item px-0 py-2 text-center">
                            <a href="/dropdowns/<?= $dropdown->id ?>/usage" class="btn btn-sm btn-outline-primary">
                                <?= t('dropdowns.view_all_usage') ?>
                            </a>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
// Delete item
function deleteItem() {
    if (confirm('<?= t('dropdowns.confirm_delete') ?>'.replace(':name', '<?= htmlspecialchars($dropdown->name) ?>'))) {
        fetch('/dropdowns/<?= $dropdown->id ?>', {
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
                setTimeout(() => window.location.href = '/dropdowns', 1500);
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

// Duplicate item
function duplicateItem() {
    if (confirm('<?= t('dropdowns.confirm_duplicate') ?>')) {
        window.location.href = '/dropdowns/create?duplicate_from=<?= $dropdown->id ?>';
    }
}

// Form validation
document.addEventListener('DOMContentLoaded', function() {
    <?php if (!$isReadOnly): ?>
    document.getElementById('dropdownForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        // Validate required fields
        const requiredFields = ['name'];
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
});

<?php
// Helper function to get type icons
$this->extend('getTypeIcon', function($type) {
    $icons = [
        'category' => 'tags',
        'unit' => 'balance-scale',
        'status' => 'circle',
        'priority' => 'exclamation',
        'country' => 'globe'
    ];
    return $icons[$type] ?? 'list-ul';
});
?>
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