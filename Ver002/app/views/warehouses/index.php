<?php
/**
 * File: app/views/warehouses/index.php
 * Purpose: Warehouse listing page with inventory management
 * Layout: Uses app layout with comprehensive warehouse overview
 */

$this->layout('layouts/app', [
    'title' => $page_title ?? t('nav.warehouses'),
    'active_nav' => 'warehouses'
]);

$currentUser = $this->getCurrentUser();
$canCreate = $this->hasRole(['admin', 'manager', 'warehouse']);
$canEdit = $this->hasRole(['admin', 'manager', 'warehouse']);
$canDelete = $this->hasRole(['admin', 'manager']);
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">
            <i class="fas fa-warehouse me-2"></i><?= t('nav.warehouses') ?>
            <span class="badge bg-secondary ms-2"><?= count($warehouses ?? []) ?></span>
        </h1>
        <div class="btn-toolbar mb-2 mb-md-0">
            <div class="btn-group me-2">
                <button type="button" class="btn btn-sm btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown">
                    <i class="fas fa-chart-bar"></i> <?= t('warehouses.reports') ?>
                </button>
                <ul class="dropdown-menu">
                    <li><a class="dropdown-item" href="/reports/warehouses/inventory"><i class="fas fa-boxes me-2"></i><?= t('reports.inventory_report') ?></a></li>
                    <li><a class="dropdown-item" href="/reports/warehouses/capacity"><i class="fas fa-chart-pie me-2"></i><?= t('reports.capacity_report') ?></a></li>
                    <li><a class="dropdown-item" href="/reports/warehouses/movements"><i class="fas fa-exchange-alt me-2"></i><?= t('reports.movement_report') ?></a></li>
                    <li><a class="dropdown-item" href="/reports/warehouses/valuation"><i class="fas fa-dollar-sign me-2"></i><?= t('reports.valuation_report') ?></a></li>
                </ul>
            </div>
            <div class="btn-group me-2">
                <button type="button" class="btn btn-sm btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown">
                    <i class="fas fa-download"></i> <?= t('common.export') ?>
                </button>
                <ul class="dropdown-menu">
                    <li><a class="dropdown-item" href="/warehouses/export/csv"><i class="fas fa-file-csv me-2"></i><?= t('common.export_csv') ?></a></li>
                    <li><a class="dropdown-item" href="/warehouses/export/excel"><i class="fas fa-file-excel me-2"></i><?= t('common.export_excel') ?></a></li>
                    <li><a class="dropdown-item" href="/warehouses/export/pdf"><i class="fas fa-file-pdf me-2"></i><?= t('common.export_pdf') ?></a></li>
                </ul>
            </div>
            <?php if ($canCreate): ?>
            <a href="/warehouses/create" class="btn btn-primary">
                <i class="fas fa-plus"></i> <?= t('warehouses.new_warehouse') ?>
            </a>
            <?php endif; ?>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body text-center">
                    <div class="h4"><?= $summary['total_warehouses'] ?? 0 ?></div>
                    <div class="small"><?= t('warehouses.total_warehouses') ?></div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body text-center">
                    <div class="h4"><?= $summary['active_warehouses'] ?? 0 ?></div>
                    <div class="small"><?= t('warehouses.active_warehouses') ?></div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body text-center">
                    <div class="h4"><?= number_format($summary['total_capacity'] ?? 0, 0) ?></div>
                    <div class="small"><?= t('warehouses.total_capacity') ?> (<?= $summary['capacity_unit'] ?? 'm³' ?>)</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-white">
                <div class="card-body text-center">
                    <div class="h4"><?= number_format($summary['total_inventory_value'] ?? 0, 0) ?></div>
                    <div class="small"><?= t('warehouses.inventory_value') ?> (<?= $summary['base_currency'] ?? 'USD' ?>)</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" id="filtersForm">
                <div class="row align-items-end">
                    <div class="col-md-3">
                        <label for="status" class="form-label"><?= t('common.status') ?></label>
                        <select class="form-select" id="status" name="status">
                            <option value=""><?= t('common.all_statuses') ?></option>
                            <option value="active" <?= $this->selected('status', 'active') ?>><?= t('warehouses.status.active') ?></option>
                            <option value="inactive" <?= $this->selected('status', 'inactive') ?>><?= t('warehouses.status.inactive') ?></option>
                            <option value="maintenance" <?= $this->selected('status', 'maintenance') ?>><?= t('warehouses.status.maintenance') ?></option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="location" class="form-label"><?= t('warehouses.location') ?></label>
                        <select class="form-select" id="location" name="location">
                            <option value=""><?= t('warehouses.all_locations') ?></option>
                            <?php foreach (($locations ?? []) as $location): ?>
                            <option value="<?= $location->id ?>" <?= $this->selected('location', $location->id) ?>>
                                <?= htmlspecialchars($location->name) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="search" class="form-label"><?= t('common.search') ?></label>
                        <input type="text" class="form-control" id="search" name="search" 
                               value="<?= htmlspecialchars($_GET['search'] ?? '') ?>" 
                               placeholder="<?= t('warehouses.search_placeholder') ?>">
                    </div>
                    <div class="col-md-3">
                        <div class="btn-group w-100">
                            <button type="submit" class="btn btn-outline-primary">
                                <i class="fas fa-search"></i> <?= t('common.filter') ?>
                            </button>
                            <a href="/warehouses" class="btn btn-outline-secondary">
                                <i class="fas fa-times"></i> <?= t('common.clear') ?>
                            </a>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Warehouses Table -->
    <div class="card">
        <div class="card-body">
            <?php if (!empty($warehouses)): ?>
            <div class="table-responsive">
                <table class="table table-hover" id="warehousesTable">
                    <thead>
                        <tr>
                            <th>
                                <input type="checkbox" class="form-check-input" id="selectAll">
                            </th>
                            <th>
                                <a href="?<?= http_build_query(array_merge($_GET, ['sort' => 'name', 'direction' => ($_GET['sort'] ?? '') === 'name' && ($_GET['direction'] ?? '') === 'asc' ? 'desc' : 'asc'])) ?>" class="text-decoration-none">
                                    <?= t('warehouses.name') ?>
                                    <?php if (($_GET['sort'] ?? '') === 'name'): ?>
                                    <i class="fas fa-sort-<?= ($_GET['direction'] ?? 'asc') === 'asc' ? 'up' : 'down' ?>"></i>
                                    <?php endif; ?>
                                </a>
                            </th>
                            <th><?= t('warehouses.location') ?></th>
                            <th><?= t('warehouses.capacity') ?></th>
                            <th><?= t('warehouses.utilization') ?></th>
                            <th><?= t('warehouses.inventory_count') ?></th>
                            <th><?= t('warehouses.inventory_value') ?></th>
                            <th><?= t('common.status') ?></th>
                            <th><?= t('common.actions') ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($warehouses as $warehouse): ?>
                        <tr>
                            <td>
                                <input type="checkbox" class="form-check-input row-select" value="<?= $warehouse->id ?>">
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="warehouse-icon bg-primary text-white rounded me-3" style="width: 40px; height: 40px; display: flex; align-items: center; justify-content: center;">
                                        <i class="fas fa-warehouse"></i>
                                    </div>
                                    <div>
                                        <a href="/warehouses/<?= $warehouse->id ?>" class="fw-bold text-decoration-none">
                                            <?= htmlspecialchars($warehouse->name) ?>
                                        </a>
                                        <?php if ($warehouse->code): ?>
                                        <br><small class="text-muted"><?= htmlspecialchars($warehouse->code) ?></small>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div>
                                    <?= htmlspecialchars($warehouse->city ?? '') ?>
                                    <?php if ($warehouse->city && $warehouse->country): ?>, <?php endif; ?>
                                    <?= htmlspecialchars($warehouse->country ?? '') ?>
                                </div>
                                <?php if ($warehouse->address): ?>
                                <small class="text-muted">
                                    <i class="fas fa-map-marker-alt me-1"></i>
                                    <?= htmlspecialchars($warehouse->address) ?>
                                </small>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="me-2">
                                        <?= number_format($warehouse->capacity ?? 0, 0) ?> <?= $warehouse->capacity_unit ?? 'm³' ?>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <?php 
                                $utilization = $warehouse->capacity > 0 ? (($warehouse->used_capacity ?? 0) / $warehouse->capacity) * 100 : 0;
                                $utilizationClass = $utilization >= 90 ? 'danger' : ($utilization >= 75 ? 'warning' : 'success');
                                ?>
                                <div class="d-flex align-items-center">
                                    <div class="progress flex-grow-1 me-2" style="height: 8px;">
                                        <div class="progress-bar bg-<?= $utilizationClass ?>" 
                                             style="width: <?= min($utilization, 100) ?>%"></div>
                                    </div>
                                    <small class="text-<?= $utilizationClass ?>"><?= number_format($utilization, 1) ?>%</small>
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-info">
                                    <?= number_format($warehouse->inventory_count ?? 0, 0) ?>
                                </span>
                                <?php if (($warehouse->low_stock_count ?? 0) > 0): ?>
                                <br><small class="text-warning">
                                    <i class="fas fa-exclamation-triangle me-1"></i>
                                    <?= $warehouse->low_stock_count ?> <?= t('warehouses.low_stock') ?>
                                </small>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="fw-bold">
                                    <?= number_format($warehouse->inventory_value ?? 0, 2) ?>
                                </div>
                                <small class="text-muted"><?= $warehouse->currency ?? 'USD' ?></small>
                            </td>
                            <td>
                                <?php 
                                $statusClass = match($warehouse->status) {
                                    'active' => 'bg-success',
                                    'inactive' => 'bg-secondary',
                                    'maintenance' => 'bg-warning text-dark',
                                    default => 'bg-secondary'
                                };
                                ?>
                                <span class="badge <?= $statusClass ?>">
                                    <?= t('warehouses.status.' . $warehouse->status) ?>
                                </span>
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <a href="/warehouses/<?= $warehouse->id ?>" class="btn btn-outline-primary" title="<?= t('common.view') ?>">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <?php if ($canEdit): ?>
                                    <a href="/warehouses/<?= $warehouse->id ?>/edit" class="btn btn-outline-secondary" title="<?= t('common.edit') ?>">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <?php endif; ?>
                                    <button type="button" class="btn btn-outline-info dropdown-toggle dropdown-toggle-split" data-bs-toggle="dropdown" title="<?= t('common.more_actions') ?>">
                                        <span class="visually-hidden"><?= t('common.actions') ?></span>
                                    </button>
                                    <ul class="dropdown-menu">
                                        <li><a class="dropdown-item" href="/warehouses/<?= $warehouse->id ?>/inventory">
                                            <i class="fas fa-boxes me-2"></i><?= t('warehouses.view_inventory') ?>
                                        </a></li>
                                        <li><a class="dropdown-item" href="/warehouses/<?= $warehouse->id ?>/movements">
                                            <i class="fas fa-exchange-alt me-2"></i><?= t('warehouses.view_movements') ?>
                                        </a></li>
                                        <li><a class="dropdown-item" href="/warehouses/<?= $warehouse->id ?>/reports">
                                            <i class="fas fa-chart-bar me-2"></i><?= t('common.reports') ?>
                                        </a></li>
                                        <li><hr class="dropdown-divider"></li>
                                        <?php if ($canEdit): ?>
                                        <li><a class="dropdown-item" href="/warehouses/<?= $warehouse->id ?>/duplicate">
                                            <i class="fas fa-copy me-2"></i><?= t('common.duplicate') ?>
                                        </a></li>
                                        <?php endif; ?>
                                        <?php if ($canDelete): ?>
                                        <li><a class="dropdown-item text-danger" href="#" onclick="deleteWarehouse(<?= $warehouse->id ?>)">
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
                                <li><a class="dropdown-item" href="#" onclick="bulkAction('activate')">
                                    <i class="fas fa-play me-2"></i><?= t('warehouses.activate_selected') ?>
                                </a></li>
                                <li><a class="dropdown-item" href="#" onclick="bulkAction('deactivate')">
                                    <i class="fas fa-pause me-2"></i><?= t('warehouses.deactivate_selected') ?>
                                </a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="#" onclick="bulkAction('export')">
                                    <i class="fas fa-download me-2"></i><?= t('common.export_selected') ?>
                                </a></li>
                                <?php endif; ?>
                                <?php if ($canDelete): ?>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item text-danger" href="#" onclick="bulkAction('delete')">
                                    <i class="fas fa-trash me-2"></i><?= t('common.delete_selected') ?>
                                </a></li>
                                <?php endif; ?>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 text-end">
                    <?= $this->paginate($warehouses ?? []) ?>
                </div>
            </div>
            
            <?php else: ?>
            <!-- Empty State -->
            <div class="text-center py-5">
                <i class="fas fa-warehouse fa-4x text-muted mb-3"></i>
                <h5 class="text-muted"><?= t('warehouses.no_warehouses') ?></h5>
                <p class="text-muted mb-4"><?= t('warehouses.no_warehouses_description') ?></p>
                <?php if ($canCreate): ?>
                <a href="/warehouses/create" class="btn btn-primary">
                    <i class="fas fa-plus me-2"></i><?= t('warehouses.create_first_warehouse') ?>
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
        selectedCount.textContent = selected;
        
        if (selected > 0) {
            bulkActions.style.display = 'block';
        } else {
            bulkActions.style.display = 'none';
        }
        
        selectAllCheckbox.indeterminate = selected > 0 && selected < rowCheckboxes.length;
        selectAllCheckbox.checked = selected === rowCheckboxes.length;
    }
    
    // Auto-submit filters
    document.getElementById('status').addEventListener('change', function() {
        document.getElementById('filtersForm').submit();
    });
    
    document.getElementById('location').addEventListener('change', function() {
        document.getElementById('filtersForm').submit();
    });
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

function bulkAction(action) {
    const selected = Array.from(document.querySelectorAll('.row-select:checked')).map(cb => cb.value);
    
    if (selected.length === 0) {
        showAlert('warning', '<?= t('common.no_items_selected') ?>');
        return;
    }
    
    let confirmMessage = '';
    switch(action) {
        case 'delete':
            confirmMessage = '<?= t('warehouses.confirm_bulk_delete') ?>';
            break;
        case 'activate':
            confirmMessage = '<?= t('warehouses.confirm_bulk_activate') ?>';
            break;
        case 'deactivate':
            confirmMessage = '<?= t('warehouses.confirm_bulk_deactivate') ?>';
            break;
        case 'export':
            // No confirmation needed for export
            break;
        default:
            return;
    }
    
    if (confirmMessage && !confirm(confirmMessage)) {
        return;
    }
    
    fetch(`/warehouses/bulk-action`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({
            action: action,
            ids: selected
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert('success', data.message);
            if (action !== 'export') {
                location.reload();
            }
        } else {
            showAlert('error', data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('error', '<?= t('messages.error.general') ?>');
    });
}
</script>