<?php
/**
 * File: app/views/warehouses/edit.php
 * Purpose: Warehouse editing form with inventory management integration
 * Layout: Uses app layout with comprehensive warehouse modification capabilities
 */

$this->layout('layouts/app', [
    'title' => $page_title ?? t('warehouses.edit_warehouse'),
    'active_nav' => 'warehouses'
]);

$currentUser = $this->getCurrentUser();
$canEdit = $this->hasRole(['admin', 'manager', 'warehouse']);
$canDelete = $this->hasRole(['admin', 'manager']);
$isReadOnly = !$canEdit;

$countries = $countries ?? [];
$currencies = $currencies ?? [];
$warehouse_types = $warehouse_types ?? [];
$managers = $managers ?? [];
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">
            <i class="fas fa-edit me-2"></i><?= t('warehouses.edit_warehouse') ?>
            <small class="text-muted ms-2"><?= htmlspecialchars($warehouse->name) ?></small>
        </h1>
        <div class="btn-toolbar mb-2 mb-md-0">
            <?php if (!$isReadOnly): ?>
            <div class="btn-group me-2">
                <button type="button" class="btn btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown">
                    <i class="fas fa-cogs"></i> <?= t('common.actions') ?>
                </button>
                <ul class="dropdown-menu">
                    <li><a class="dropdown-item" href="/warehouses/<?= $warehouse->id ?>">
                        <i class="fas fa-eye me-2"></i><?= t('common.view') ?>
                    </a></li>
                    <li><a class="dropdown-item" href="/warehouses/<?= $warehouse->id ?>/inventory">
                        <i class="fas fa-boxes me-2"></i><?= t('warehouses.manage_inventory') ?>
                    </a></li>
                    <li><a class="dropdown-item" href="/warehouses/<?= $warehouse->id ?>/duplicate">
                        <i class="fas fa-copy me-2"></i><?= t('common.duplicate') ?>
                    </a></li>
                    <?php if ($canDelete): ?>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item text-danger" href="#" onclick="deleteWarehouse(<?= $warehouse->id ?>)">
                        <i class="fas fa-trash me-2"></i><?= t('common.delete') ?>
                    </a></li>
                    <?php endif; ?>
                </ul>
            </div>
            <?php endif; ?>
            
            <a href="/warehouses" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left"></i> <?= t('common.back') ?>
            </a>
        </div>
    </div>

    <?php if ($isReadOnly): ?>
    <div class="alert alert-info">
        <i class="fas fa-info-circle me-2"></i>
        <?= t('warehouses.readonly_notice') ?>
    </div>
    <?php endif; ?>

    <form method="POST" action="/warehouses/<?= $warehouse->id ?>" id="editWarehouseForm" enctype="multipart/form-data">
        <?= $this->csrf() ?>
        <input type="hidden" name="_method" value="PUT">
        
        <div class="row">
            <div class="col-md-8">
                <!-- Basic Information -->
                <div class="card mb-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><?= t('warehouses.basic_information') ?></h5>
                        <span class="badge bg-<?= $warehouse->status === 'active' ? 'success' : 'secondary' ?>">
                            <?= t('warehouses.status.' . $warehouse->status) ?>
                        </span>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="name" class="form-label"><?= t('warehouses.warehouse_name') ?> *</label>
                                    <input type="text" class="form-control" id="name" name="name" 
                                           value="<?= $this->old('name', $warehouse->name) ?>" 
                                           <?= $isReadOnly ? 'readonly' : 'required' ?>>
                                    <?= $this->error('name') ?>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="code" class="form-label"><?= t('warehouses.warehouse_code') ?></label>
                                    <input type="text" class="form-control" id="code" name="code" 
                                           value="<?= $this->old('code', $warehouse->code) ?>" 
                                           <?= $isReadOnly ? 'readonly' : '' ?>>
                                    <?= $this->error('code') ?>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="type" class="form-label"><?= t('warehouses.warehouse_type') ?> *</label>
                                    <select class="form-select" id="type" name="type" <?= $isReadOnly ? 'disabled' : 'required' ?>>
                                        <?php foreach ($warehouse_types as $type): ?>
                                        <option value="<?= $type->slug ?>" <?= $this->selected('type', $type->slug, $warehouse->type) ?>>
                                            <?= htmlspecialchars($type->name) ?>
                                        </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <?= $this->error('type') ?>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="status" class="form-label"><?= t('common.status') ?> *</label>
                                    <select class="form-select" id="status" name="status" <?= $isReadOnly ? 'disabled' : 'required' ?>>
                                        <option value="active" <?= $this->selected('status', 'active', $warehouse->status) ?>>
                                            <?= t('warehouses.status.active') ?>
                                        </option>
                                        <option value="inactive" <?= $this->selected('status', 'inactive', $warehouse->status) ?>>
                                            <?= t('warehouses.status.inactive') ?>
                                        </option>
                                        <option value="maintenance" <?= $this->selected('status', 'maintenance', $warehouse->status) ?>>
                                            <?= t('warehouses.status.maintenance') ?>
                                        </option>
                                    </select>
                                    <?= $this->error('status') ?>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="description" class="form-label"><?= t('warehouses.description') ?></label>
                            <textarea class="form-control" id="description" name="description" rows="3" 
                                      <?= $isReadOnly ? 'readonly' : '' ?>><?= $this->old('description', $warehouse->description) ?></textarea>
                            <?= $this->error('description') ?>
                        </div>
                    </div>
                </div>

                <!-- Location Information -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0"><?= t('warehouses.location_information') ?></h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="address" class="form-label"><?= t('warehouses.address') ?> *</label>
                            <textarea class="form-control" id="address" name="address" rows="2" 
                                      <?= $isReadOnly ? 'readonly' : 'required' ?>><?= $this->old('address', $warehouse->address) ?></textarea>
                            <?= $this->error('address') ?>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="city" class="form-label"><?= t('warehouses.city') ?> *</label>
                                    <input type="text" class="form-control" id="city" name="city" 
                                           value="<?= $this->old('city', $warehouse->city) ?>" 
                                           <?= $isReadOnly ? 'readonly' : 'required' ?>>
                                    <?= $this->error('city') ?>
                                </div>
                            </div>
                            
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="country" class="form-label"><?= t('warehouses.country') ?> *</label>
                                    <select class="form-select" id="country" name="country" <?= $isReadOnly ? 'disabled' : 'required' ?>>
                                        <?php foreach ($countries as $country): ?>
                                        <option value="<?= $country->code ?>" <?= $this->selected('country', $country->code, $warehouse->country) ?>>
                                            <?= htmlspecialchars($country->name) ?>
                                        </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <?= $this->error('country') ?>
                                </div>
                            </div>
                            
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="postal_code" class="form-label"><?= t('warehouses.postal_code') ?></label>
                                    <input type="text" class="form-control" id="postal_code" name="postal_code" 
                                           value="<?= $this->old('postal_code', $warehouse->postal_code) ?>" 
                                           <?= $isReadOnly ? 'readonly' : '' ?>>
                                    <?= $this->error('postal_code') ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Capacity Information -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0"><?= t('warehouses.capacity_specifications') ?></h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="total_capacity" class="form-label"><?= t('warehouses.total_capacity') ?> *</label>
                                    <div class="input-group">
                                        <input type="number" class="form-control" id="total_capacity" name="total_capacity" 
                                               value="<?= $this->old('total_capacity', $warehouse->total_capacity) ?>" 
                                               min="0" step="0.01" <?= $isReadOnly ? 'readonly' : 'required' ?>>
                                        <span class="input-group-text"><?= $warehouse->capacity_unit ?? 'm³' ?></span>
                                    </div>
                                    <?= $this->error('total_capacity') ?>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="manager_id" class="form-label"><?= t('warehouses.warehouse_manager') ?></label>
                                    <select class="form-select" id="manager_id" name="manager_id" <?= $isReadOnly ? 'disabled' : '' ?>>
                                        <option value=""><?= t('warehouses.select_manager') ?></option>
                                        <?php foreach ($managers as $manager): ?>
                                        <option value="<?= $manager->id ?>" <?= $this->selected('manager_id', $manager->id, $warehouse->manager_id) ?>>
                                            <?= htmlspecialchars($manager->name) ?>
                                        </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <?= $this->error('manager_id') ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <!-- Actions -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0"><?= t('common.actions') ?></h5>
                    </div>
                    <div class="card-body">
                        <?php if (!$isReadOnly): ?>
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> <?= t('common.save_changes') ?>
                            </button>
                            
                            <a href="/warehouses/<?= $warehouse->id ?>" class="btn btn-outline-secondary">
                                <i class="fas fa-eye"></i> <?= t('common.view') ?>
                            </a>
                        </div>
                        <?php else: ?>
                        <div class="alert alert-info mb-0">
                            <i class="fas fa-lock me-2"></i>
                            <?= t('warehouses.editing_disabled') ?>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Warehouse Stats -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0"><?= t('warehouses.statistics') ?></h5>
                    </div>
                    <div class="card-body">
                        <div class="row text-center">
                            <div class="col-6">
                                <div class="border rounded p-2 mb-2">
                                    <div class="h6 mb-0"><?= number_format($warehouse->inventory_count ?? 0) ?></div>
                                    <small class="text-muted"><?= t('warehouses.items') ?></small>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="border rounded p-2 mb-2">
                                    <div class="h6 mb-0"><?= number_format(($warehouse->utilization ?? 0), 1) ?>%</div>
                                    <small class="text-muted"><?= t('warehouses.utilization') ?></small>
                                </div>
                            </div>
                        </div>
                        
                        <div class="progress mb-3" style="height: 8px;">
                            <div class="progress-bar bg-<?= ($warehouse->utilization ?? 0) >= 90 ? 'danger' : (($warehouse->utilization ?? 0) >= 75 ? 'warning' : 'success') ?>" 
                                 style="width: <?= min($warehouse->utilization ?? 0, 100) ?>%"></div>
                        </div>
                        
                        <table class="table table-sm table-borderless">
                            <tr>
                                <td><?= t('warehouses.created') ?>:</td>
                                <td><?= date('M d, Y', strtotime($warehouse->created_at)) ?></td>
                            </tr>
                            <tr>
                                <td><?= t('warehouses.updated') ?>:</td>
                                <td><?= date('M d, Y', strtotime($warehouse->updated_at)) ?></td>
                            </tr>
                        </table>
                    </div>
                </div>

                <!-- Quick Links -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><?= t('warehouses.quick_links') ?></h5>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <a href="/warehouses/<?= $warehouse->id ?>/inventory" class="btn btn-outline-primary btn-sm">
                                <i class="fas fa-boxes me-2"></i><?= t('warehouses.inventory') ?>
                            </a>
                            <a href="/warehouses/<?= $warehouse->id ?>/movements" class="btn btn-outline-info btn-sm">
                                <i class="fas fa-exchange-alt me-2"></i><?= t('warehouses.movements') ?>
                            </a>
                            <a href="/warehouses/<?= $warehouse->id ?>/reports" class="btn btn-outline-success btn-sm">
                                <i class="fas fa-chart-bar me-2"></i><?= t('common.reports') ?>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('editWarehouseForm');
    const isReadOnly = <?= $isReadOnly ? 'true' : 'false' ?>;
    
    if (!isReadOnly) {
        // Form validation
        form.addEventListener('submit', function(e) {
            let isValid = true;
            const requiredFields = form.querySelectorAll('[required]');
            
            requiredFields.forEach(field => {
                if (!field.value.trim()) {
                    isValid = false;
                    field.classList.add('is-invalid');
                } else {
                    field.classList.remove('is-invalid');
                }
            });
            
            if (!isValid) {
                e.preventDefault();
                showAlert('error', '<?= t('warehouses.form_validation_errors') ?>');
                return false;
            }
            
            return true;
        });
    }
});

function deleteWarehouse(warehouseId) {
    if (confirm('<?= t('warehouses.confirm_delete') ?>')) {
        fetch(`/warehouses/${warehouseId}`, {
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
                window.location.href = '/warehouses';
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