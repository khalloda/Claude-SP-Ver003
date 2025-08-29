<?php
/**
 * File: app/views/dropdowns/index.php
 * Purpose: System dropdowns and reference data management
 * Layout: Uses app layout with tabbed interface for different dropdown types
 */

$this->layout('layouts/app', [
    'title' => $page_title ?? t('nav.dropdowns'),
    'active_nav' => 'dropdowns'
]);

$currentUser = $this->getCurrentUser();
$canCreate = $this->hasRole(['admin', 'manager']);
$canEdit = $this->hasRole(['admin', 'manager']);
$canDelete = $this->hasRole(['admin']);
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">
            <i class="fas fa-list-ul me-2"></i><?= t('nav.dropdowns') ?>
            <span class="badge bg-secondary ms-2"><?= $totalItems ?? 0 ?></span>
        </h1>
        <?php if ($canCreate): ?>
        <div class="btn-toolbar mb-2 mb-md-0">
            <div class="btn-group me-2">
                <button type="button" class="btn btn-primary dropdown-toggle" data-bs-toggle="dropdown">
                    <i class="fas fa-plus"></i> <?= t('dropdowns.add_item') ?>
                </button>
                <ul class="dropdown-menu">
                    <li><a class="dropdown-item" href="/dropdowns/create?type=category">
                        <i class="fas fa-tags me-2"></i><?= t('dropdowns.categories') ?>
                    </a></li>
                    <li><a class="dropdown-item" href="/dropdowns/create?type=unit">
                        <i class="fas fa-balance-scale me-2"></i><?= t('dropdowns.units') ?>
                    </a></li>
                    <li><a class="dropdown-item" href="/dropdowns/create?type=status">
                        <i class="fas fa-circle me-2"></i><?= t('dropdowns.statuses') ?>
                    </a></li>
                    <li><a class="dropdown-item" href="/dropdowns/create?type=priority">
                        <i class="fas fa-exclamation me-2"></i><?= t('dropdowns.priorities') ?>
                    </a></li>
                    <li><a class="dropdown-item" href="/dropdowns/create?type=country">
                        <i class="fas fa-globe me-2"></i><?= t('dropdowns.countries') ?>
                    </a></li>
                </ul>
            </div>
            <button class="btn btn-outline-info" onclick="bulkImport()">
                <i class="fas fa-upload"></i> <?= t('dropdowns.bulk_import') ?>
            </button>
        </div>
        <?php endif; ?>
    </div>

    <!-- Quick Stats -->
    <div class="row mb-4">
        <div class="col-md-2">
            <div class="card text-center">
                <div class="card-body py-3">
                    <div class="h4 text-primary mb-1"><?= $stats['categories'] ?? 0 ?></div>
                    <div class="small text-muted"><?= t('dropdowns.categories') ?></div>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card text-center">
                <div class="card-body py-3">
                    <div class="h4 text-success mb-1"><?= $stats['units'] ?? 0 ?></div>
                    <div class="small text-muted"><?= t('dropdowns.units') ?></div>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card text-center">
                <div class="card-body py-3">
                    <div class="h4 text-info mb-1"><?= $stats['statuses'] ?? 0 ?></div>
                    <div class="small text-muted"><?= t('dropdowns.statuses') ?></div>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card text-center">
                <div class="card-body py-3">
                    <div class="h4 text-warning mb-1"><?= $stats['priorities'] ?? 0 ?></div>
                    <div class="small text-muted"><?= t('dropdowns.priorities') ?></div>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card text-center">
                <div class="card-body py-3">
                    <div class="h4 text-secondary mb-1"><?= $stats['countries'] ?? 0 ?></div>
                    <div class="small text-muted"><?= t('dropdowns.countries') ?></div>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card text-center">
                <div class="card-body py-3">
                    <div class="h4 text-dark mb-1"><?= $stats['total'] ?? 0 ?></div>
                    <div class="small text-muted"><?= t('common.total') ?></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="/dropdowns" class="row g-3">
                <div class="col-md-3">
                    <label for="search" class="form-label"><?= t('common.search') ?></label>
                    <input type="text" class="form-control" id="search" name="search" 
                           value="<?= htmlspecialchars($search ?? '') ?>" 
                           placeholder="<?= t('dropdowns.search_placeholder') ?>">
                </div>
                
                <div class="col-md-2">
                    <label for="type" class="form-label"><?= t('dropdowns.type') ?></label>
                    <select class="form-select" id="type" name="type">
                        <option value=""><?= t('common.all_types') ?></option>
                        <option value="category" <?= ($type ?? '') === 'category' ? 'selected' : '' ?>><?= t('dropdowns.categories') ?></option>
                        <option value="unit" <?= ($type ?? '') === 'unit' ? 'selected' : '' ?>><?= t('dropdowns.units') ?></option>
                        <option value="status" <?= ($type ?? '') === 'status' ? 'selected' : '' ?>><?= t('dropdowns.statuses') ?></option>
                        <option value="priority" <?= ($type ?? '') === 'priority' ? 'selected' : '' ?>><?= t('dropdowns.priorities') ?></option>
                        <option value="country" <?= ($type ?? '') === 'country' ? 'selected' : '' ?>><?= t('dropdowns.countries') ?></option>
                    </select>
                </div>
                
                <div class="col-md-2">
                    <label for="status" class="form-label"><?= t('common.status') ?></label>
                    <select class="form-select" id="status" name="status">
                        <option value=""><?= t('common.all_statuses') ?></option>
                        <option value="active" <?= ($status ?? '') === 'active' ? 'selected' : '' ?>><?= t('common.active') ?></option>
                        <option value="inactive" <?= ($status ?? '') === 'inactive' ? 'selected' : '' ?>><?= t('common.inactive') ?></option>
                    </select>
                </div>
                
                <div class="col-md-2">
                    <label for="sort" class="form-label"><?= t('common.sort_by') ?></label>
                    <select class="form-select" id="sort" name="sort">
                        <option value="name" <?= ($sort ?? 'name') === 'name' ? 'selected' : '' ?>><?= t('common.name') ?></option>
                        <option value="type" <?= ($sort ?? '') === 'type' ? 'selected' : '' ?>><?= t('dropdowns.type') ?></option>
                        <option value="usage" <?= ($sort ?? '') === 'usage' ? 'selected' : '' ?>><?= t('dropdowns.usage_count') ?></option>
                        <option value="created" <?= ($sort ?? '') === 'created' ? 'selected' : '' ?>><?= t('common.created') ?></option>
                    </select>
                </div>
                
                <div class="col-md-3 d-flex align-items-end">
                    <button type="submit" class="btn btn-outline-primary me-2">
                        <i class="fas fa-filter"></i> <?= t('common.filter') ?>
                    </button>
                    <a href="/dropdowns" class="btn btn-outline-secondary me-2">
                        <i class="fas fa-times"></i>
                    </a>
                    <button type="button" class="btn btn-outline-success" onclick="exportData()">
                        <i class="fas fa-download"></i>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Dropdowns Tabs -->
    <div class="card">
        <div class="card-header">
            <ul class="nav nav-tabs card-header-tabs" id="dropdownTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="categories-tab" data-bs-toggle="tab" data-bs-target="#categories" type="button" role="tab">
                        <i class="fas fa-tags me-2"></i><?= t('dropdowns.categories') ?>
                        <span class="badge bg-secondary ms-2"><?= $stats['categories'] ?? 0 ?></span>
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="units-tab" data-bs-toggle="tab" data-bs-target="#units" type="button" role="tab">
                        <i class="fas fa-balance-scale me-2"></i><?= t('dropdowns.units') ?>
                        <span class="badge bg-secondary ms-2"><?= $stats['units'] ?? 0 ?></span>
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="statuses-tab" data-bs-toggle="tab" data-bs-target="#statuses" type="button" role="tab">
                        <i class="fas fa-circle me-2"></i><?= t('dropdowns.statuses') ?>
                        <span class="badge bg-secondary ms-2"><?= $stats['statuses'] ?? 0 ?></span>
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="priorities-tab" data-bs-toggle="tab" data-bs-target="#priorities" type="button" role="tab">
                        <i class="fas fa-exclamation me-2"></i><?= t('dropdowns.priorities') ?>
                        <span class="badge bg-secondary ms-2"><?= $stats['priorities'] ?? 0 ?></span>
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="countries-tab" data-bs-toggle="tab" data-bs-target="#countries" type="button" role="tab">
                        <i class="fas fa-globe me-2"></i><?= t('dropdowns.countries') ?>
                        <span class="badge bg-secondary ms-2"><?= $stats['countries'] ?? 0 ?></span>
                    </button>
                </li>
            </ul>
        </div>
        <div class="card-body">
            <div class="tab-content" id="dropdownTabsContent">
                <!-- Categories Tab -->
                <div class="tab-pane fade show active" id="categories" role="tabpanel">
                    <?php $this->renderDropdownTable('category', $categoriesData ?? [], $canEdit, $canDelete); ?>
                </div>
                
                <!-- Units Tab -->
                <div class="tab-pane fade" id="units" role="tabpanel">
                    <?php $this->renderDropdownTable('unit', $unitsData ?? [], $canEdit, $canDelete); ?>
                </div>
                
                <!-- Statuses Tab -->
                <div class="tab-pane fade" id="statuses" role="tabpanel">
                    <?php $this->renderDropdownTable('status', $statusesData ?? [], $canEdit, $canDelete); ?>
                </div>
                
                <!-- Priorities Tab -->
                <div class="tab-pane fade" id="priorities" role="tabpanel">
                    <?php $this->renderDropdownTable('priority', $prioritiesData ?? [], $canEdit, $canDelete); ?>
                </div>
                
                <!-- Countries Tab -->
                <div class="tab-pane fade" id="countries" role="tabpanel">
                    <?php $this->renderDropdownTable('country', $countriesData ?? [], $canEdit, $canDelete); ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Bulk Import Modal -->
<div class="modal fade" id="bulkImportModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><?= t('dropdowns.bulk_import') ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="bulkImportForm">
                    <div class="mb-3">
                        <label for="importType" class="form-label"><?= t('dropdowns.type') ?></label>
                        <select class="form-select" id="importType" name="type" required>
                            <option value=""><?= t('common.select_type') ?></option>
                            <option value="category"><?= t('dropdowns.categories') ?></option>
                            <option value="unit"><?= t('dropdowns.units') ?></option>
                            <option value="status"><?= t('dropdowns.statuses') ?></option>
                            <option value="priority"><?= t('dropdowns.priorities') ?></option>
                            <option value="country"><?= t('dropdowns.countries') ?></option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="importFile" class="form-label"><?= t('dropdowns.csv_file') ?></label>
                        <input type="file" class="form-control" id="importFile" name="file" accept=".csv" required>
                        <div class="form-text"><?= t('dropdowns.csv_help') ?></div>
                    </div>
                    
                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="skipDuplicates" name="skip_duplicates" checked>
                            <label class="form-check-label" for="skipDuplicates">
                                <?= t('dropdowns.skip_duplicates') ?>
                            </label>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><?= t('common.cancel') ?></button>
                <button type="button" class="btn btn-primary" onclick="processBulkImport()">
                    <i class="fas fa-upload"></i> <?= t('dropdowns.import') ?>
                </button>
            </div>
        </div>
    </div>
</div>

<?php
// Helper function to render dropdown tables
$this->extend('renderDropdownTable', function($type, $data, $canEdit, $canDelete) {
?>
    <?php if (empty($data)): ?>
        <div class="text-center py-5">
            <i class="fas fa-list-ul fa-4x text-muted mb-4"></i>
            <h4><?= t('dropdowns.no_items_found', ['type' => t('dropdowns.' . $type . 's')]) ?></h4>
            <p class="text-muted"><?= t('dropdowns.no_items_desc') ?></p>
            <?php if ($canEdit): ?>
            <a href="/dropdowns/create?type=<?= $type ?>" class="btn btn-primary">
                <i class="fas fa-plus"></i> <?= t('dropdowns.add_first_item', ['type' => t('dropdowns.' . $type)]) ?>
            </a>
            <?php endif; ?>
        </div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table table-hover">
                <thead class="table-light">
                    <tr>
                        <th style="width: 40px;">
                            <?php if ($canEdit): ?>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="selectAll<?= ucfirst($type) ?>" onchange="toggleSelectAll('<?= $type ?>')">
                            </div>
                            <?php endif; ?>
                        </th>
                        <th><?= t('common.name') ?></th>
                        <th><?= t('common.description') ?></th>
                        <th><?= t('dropdowns.usage_count') ?></th>
                        <th><?= t('common.status') ?></th>
                        <th><?= t('dropdowns.sort_order') ?></th>
                        <th><?= t('common.actions') ?></th>
                    </tr>
                </thead>
                <tbody id="<?= $type ?>Table">
                    <?php foreach ($data as $item): ?>
                    <tr data-id="<?= $item->id ?>">
                        <td>
                            <?php if ($canEdit): ?>
                            <div class="form-check">
                                <input class="form-check-input item-checkbox" type="checkbox" value="<?= $item->id ?>" data-type="<?= $type ?>">
                            </div>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div class="d-flex align-items-center">
                                <?php if (!empty($item->color)): ?>
                                <span class="badge me-2" style="background-color: <?= htmlspecialchars($item->color) ?>;">&nbsp;</span>
                                <?php endif; ?>
                                <strong><?= htmlspecialchars($item->name) ?></strong>
                                <?php if (!empty($item->code)): ?>
                                <small class="text-muted ms-2">(<?= htmlspecialchars($item->code) ?>)</small>
                                <?php endif; ?>
                            </div>
                        </td>
                        <td>
                            <span class="text-muted"><?= htmlspecialchars(substr($item->description ?? '', 0, 50)) ?><?= strlen($item->description ?? '') > 50 ? '...' : '' ?></span>
                        </td>
                        <td>
                            <span class="badge bg-info"><?= $item->usage_count ?? 0 ?></span>
                        </td>
                        <td>
                            <span class="badge bg-<?= $item->is_active ? 'success' : 'secondary' ?>">
                                <?= $item->is_active ? t('common.active') : t('common.inactive') ?>
                            </span>
                        </td>
                        <td>
                            <?php if ($canEdit): ?>
                            <div class="input-group input-group-sm" style="width: 80px;">
                                <input type="number" class="form-control sort-order" 
                                       value="<?= $item->sort_order ?? 0 ?>" 
                                       min="0" max="999"
                                       onchange="updateSortOrder(<?= $item->id ?>, '<?= $type ?>', this.value)">
                            </div>
                            <?php else: ?>
                            <span class="text-muted"><?= $item->sort_order ?? 0 ?></span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div class="btn-group btn-group-sm">
                                <a href="/dropdowns/<?= $item->id ?>" class="btn btn-outline-info">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <?php if ($canEdit): ?>
                                <a href="/dropdowns/<?= $item->id ?>/edit" class="btn btn-outline-primary">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <?php endif; ?>
                                <?php if ($canDelete && ($item->usage_count ?? 0) == 0): ?>
                                <button class="btn btn-outline-danger" onclick="deleteItem(<?= $item->id ?>, '<?= htmlspecialchars($item->name) ?>', '<?= $type ?>')">
                                    <i class="fas fa-trash"></i>
                                </button>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
        <?php if ($canEdit): ?>
        <div class="d-flex justify-content-between align-items-center mt-3">
            <div>
                <button class="btn btn-outline-success btn-sm" onclick="bulkActivate('<?= $type ?>')">
                    <i class="fas fa-check"></i> <?= t('dropdowns.activate_selected') ?>
                </button>
                <button class="btn btn-outline-warning btn-sm" onclick="bulkDeactivate('<?= $type ?>')">
                    <i class="fas fa-times"></i> <?= t('dropdowns.deactivate_selected') ?>
                </button>
                <?php if ($canDelete): ?>
                <button class="btn btn-outline-danger btn-sm" onclick="bulkDelete('<?= $type ?>')">
                    <i class="fas fa-trash"></i> <?= t('dropdowns.delete_selected') ?>
                </button>
                <?php endif; ?>
            </div>
            <div>
                <small class="text-muted"><?= count($data) ?> <?= t('dropdowns.items_total') ?></small>
            </div>
        </div>
        <?php endif; ?>
    <?php endif; ?>
<?php
});
?>

<script>
// Toggle select all checkboxes
function toggleSelectAll(type) {
    const selectAllCheckbox = document.getElementById('selectAll' + type.charAt(0).toUpperCase() + type.slice(1));
    const checkboxes = document.querySelectorAll(`input[data-type="${type}"].item-checkbox`);
    
    checkboxes.forEach(checkbox => {
        checkbox.checked = selectAllCheckbox.checked;
    });
}

// Update sort order
async function updateSortOrder(id, type, sortOrder) {
    try {
        const response = await fetch(`/api/dropdowns/${id}/sort`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({ sort_order: parseInt(sortOrder) })
        });
        
        const data = await response.json();
        if (!data.success) {
            showAlert('error', data.message || '<?= t('dropdowns.sort_update_failed') ?>');
        }
    } catch (error) {
        console.error('Sort update error:', error);
        showAlert('error', '<?= t('messages.error.general') ?>');
    }
}

// Delete item
function deleteItem(id, name, type) {
    if (confirm('<?= t('dropdowns.confirm_delete') ?>'.replace(':name', name))) {
        fetch(`/dropdowns/${id}`, {
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

// Bulk operations
function getSelectedItems(type) {
    const checkboxes = document.querySelectorAll(`input[data-type="${type}"].item-checkbox:checked`);
    return Array.from(checkboxes).map(cb => cb.value);
}

function bulkActivate(type) {
    const ids = getSelectedItems(type);
    if (ids.length === 0) {
        showAlert('warning', '<?= t('dropdowns.select_items_first') ?>');
        return;
    }
    
    if (confirm('<?= t('dropdowns.confirm_bulk_activate') ?>'.replace(':count', ids.length))) {
        bulkOperation(ids, 'activate');
    }
}

function bulkDeactivate(type) {
    const ids = getSelectedItems(type);
    if (ids.length === 0) {
        showAlert('warning', '<?= t('dropdowns.select_items_first') ?>');
        return;
    }
    
    if (confirm('<?= t('dropdowns.confirm_bulk_deactivate') ?>'.replace(':count', ids.length))) {
        bulkOperation(ids, 'deactivate');
    }
}

function bulkDelete(type) {
    const ids = getSelectedItems(type);
    if (ids.length === 0) {
        showAlert('warning', '<?= t('dropdowns.select_items_first') ?>');
        return;
    }
    
    if (confirm('<?= t('dropdowns.confirm_bulk_delete') ?>'.replace(':count', ids.length))) {
        bulkOperation(ids, 'delete');
    }
}

async function bulkOperation(ids, operation) {
    try {
        const response = await fetch('/api/dropdowns/bulk', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({ ids, operation })
        });
        
        const data = await response.json();
        if (data.success) {
            showAlert('success', data.message);
            location.reload();
        } else {
            showAlert('error', data.message);
        }
    } catch (error) {
        console.error('Bulk operation error:', error);
        showAlert('error', '<?= t('messages.error.general') ?>');
    }
}

// Bulk import
function bulkImport() {
    const modal = new bootstrap.Modal(document.getElementById('bulkImportModal'));
    modal.show();
}

async function processBulkImport() {
    const form = document.getElementById('bulkImportForm');
    const formData = new FormData(form);
    
    const button = event.target;
    const originalText = button.innerHTML;
    button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> <?= t('dropdowns.importing') ?>';
    button.disabled = true;
    
    try {
        const response = await fetch('/api/dropdowns/bulk-import', {
            method: 'POST',
            headers: {
                'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: formData
        });
        
        const data = await response.json();
        if (data.success) {
            showAlert('success', data.message);
            bootstrap.Modal.getInstance(document.getElementById('bulkImportModal')).hide();
            location.reload();
        } else {
            showAlert('error', data.message);
        }
    } catch (error) {
        console.error('Import error:', error);
        showAlert('error', '<?= t('dropdowns.import_error') ?>');
    } finally {
        button.innerHTML = originalText;
        button.disabled = false;
    }
}

// Export data
function exportData() {
    const params = new URLSearchParams(window.location.search);
    params.set('format', 'csv');
    window.location.href = '/api/dropdowns/export?' + params.toString();
}

// Initialize
document.addEventListener('DOMContentLoaded', function() {
    // Auto-refresh sort orders
    setInterval(() => {
        const sortInputs = document.querySelectorAll('.sort-order');
        sortInputs.forEach(input => {
            if (document.activeElement !== input && input.dataset.changed) {
                delete input.dataset.changed;
            }
        });
    }, 5000);
});
</script>

<style>
.sort-order {
    text-align: center;
}

.nav-tabs .nav-link .badge {
    font-size: 0.7em;
}

.table th {
    font-weight: 600;
    border-bottom: 2px solid #dee2e6;
}

.btn-group-sm > .btn {
    padding: 0.25rem 0.5rem;
}
</style>