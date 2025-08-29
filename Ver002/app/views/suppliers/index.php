<?php
/**
 * File: app/views/suppliers/index.php
 * Purpose: Suppliers listing page with vendor management
 * Layout: Uses app layout with supplier performance tracking
 */

$this->layout('layouts/app', [
    'title' => $page_title ?? t('nav.suppliers'),
    'active_nav' => 'suppliers'
]);

$currentUser = $this->getCurrentUser();
$canCreate = $this->hasRole(['admin', 'manager', 'purchasing']);
$canEdit = $this->hasRole(['admin', 'manager', 'purchasing']);
$canDelete = $this->hasRole(['admin', 'manager']);
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">
            <i class="fas fa-truck me-2"></i><?= t('nav.suppliers') ?>
            <span class="badge bg-secondary ms-2"><?= count($suppliers ?? []) ?></span>
        </h1>
        <div class="btn-toolbar mb-2 mb-md-0">
            <div class="btn-group me-2">
                <button type="button" class="btn btn-sm btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown">
                    <i class="fas fa-download"></i> <?= t('common.export') ?>
                </button>
                <ul class="dropdown-menu">
                    <li><a class="dropdown-item" href="/suppliers/export?format=csv"><i class="fas fa-file-csv me-2"></i>CSV</a></li>
                    <li><a class="dropdown-item" href="/suppliers/export?format=excel"><i class="fas fa-file-excel me-2"></i>Excel</a></li>
                    <li><a class="dropdown-item" href="/suppliers/performance-report"><i class="fas fa-chart-line me-2"></i><?= t('suppliers.performance_report') ?></a></li>
                </ul>
            </div>
            <?php if ($canCreate): ?>
            <a href="/suppliers/create" class="btn btn-primary">
                <i class="fas fa-plus"></i> <?= t('suppliers.add_supplier') ?>
            </a>
            <?php endif; ?>
        </div>
    </div>

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="/suppliers" class="row g-3">
                <div class="col-md-4">
                    <label for="search" class="form-label"><?= t('common.search') ?></label>
                    <input type="text" class="form-control" id="search" name="search" 
                           value="<?= htmlspecialchars($search ?? '') ?>" 
                           placeholder="<?= t('suppliers.search_placeholder') ?>">
                </div>
                
                <div class="col-md-2">
                    <label for="status" class="form-label"><?= t('common.status') ?></label>
                    <select class="form-select" id="status" name="status">
                        <option value=""><?= t('common.all_statuses') ?></option>
                        <option value="active" <?= ($status ?? '') === 'active' ? 'selected' : '' ?>><?= t('common.active') ?></option>
                        <option value="inactive" <?= ($status ?? '') === 'inactive' ? 'selected' : '' ?>><?= t('common.inactive') ?></option>
                        <option value="pending" <?= ($status ?? '') === 'pending' ? 'selected' : '' ?>><?= t('suppliers.status.pending') ?></option>
                    </select>
                </div>
                
                <div class="col-md-2">
                    <label for="type" class="form-label"><?= t('suppliers.type') ?></label>
                    <select class="form-select" id="type" name="type">
                        <option value=""><?= t('common.all_types') ?></option>
                        <option value="manufacturer" <?= ($type ?? '') === 'manufacturer' ? 'selected' : '' ?>><?= t('suppliers.type.manufacturer') ?></option>
                        <option value="distributor" <?= ($type ?? '') === 'distributor' ? 'selected' : '' ?>><?= t('suppliers.type.distributor') ?></option>
                        <option value="wholesaler" <?= ($type ?? '') === 'wholesaler' ? 'selected' : '' ?>><?= t('suppliers.type.wholesaler') ?></option>
                        <option value="retailer" <?= ($type ?? '') === 'retailer' ? 'selected' : '' ?>><?= t('suppliers.type.retailer') ?></option>
                    </select>
                </div>
                
                <div class="col-md-2">
                    <label for="country" class="form-label"><?= t('suppliers.country') ?></label>
                    <select class="form-select" id="country" name="country">
                        <option value=""><?= t('common.all_countries') ?></option>
                        <?php foreach ($countries ?? [] as $country): ?>
                        <option value="<?= $country ?>" <?= ($country_filter ?? '') === $country ? 'selected' : '' ?>>
                            <?= htmlspecialchars($country) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-outline-primary me-2">
                        <i class="fas fa-filter"></i> <?= t('common.filter') ?>
                    </button>
                    <a href="/suppliers" class="btn btn-outline-secondary">
                        <i class="fas fa-times"></i>
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Suppliers Grid -->
    <div class="card">
        <div class="card-body">
            <?php if (empty($suppliers)): ?>
                <div class="text-center py-5">
                    <i class="fas fa-truck fa-4x text-muted mb-4"></i>
                    <h4><?= t('suppliers.no_suppliers_found') ?></h4>
                    <p class="text-muted"><?= t('suppliers.no_suppliers_desc') ?></p>
                    <?php if ($canCreate): ?>
                    <a href="/suppliers/create" class="btn btn-primary">
                        <i class="fas fa-plus"></i> <?= t('suppliers.add_first_supplier') ?>
                    </a>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <div class="row">
                    <?php foreach ($suppliers as $supplier): ?>
                    <div class="col-xl-4 col-lg-6 col-md-6 mb-4">
                        <div class="card h-100 supplier-card">
                            <div class="card-body">
                                <div class="d-flex align-items-start">
                                    <div class="user-avatar bg-info text-white rounded-circle me-3 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 50px; height: 50px;">
                                        <i class="fas fa-building"></i>
                                    </div>
                                    
                                    <div class="flex-grow-1">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <div>
                                                <h6 class="card-title mb-1">
                                                    <a href="/suppliers/<?= $supplier->id ?>" class="text-decoration-none">
                                                        <?= htmlspecialchars($supplier->name) ?>
                                                    </a>
                                                </h6>
                                                <div class="small text-muted mb-2">
                                                    <?= t('suppliers.type.' . $supplier->type) ?>
                                                    <?php if ($supplier->is_active): ?>
                                                        <span class="badge bg-success ms-2"><?= t('common.active') ?></span>
                                                    <?php else: ?>
                                                        <span class="badge bg-secondary ms-2"><?= t('common.inactive') ?></span>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                            
                                            <div class="dropdown">
                                                <button class="btn btn-sm btn-outline-secondary" type="button" data-bs-toggle="dropdown">
                                                    <i class="fas fa-ellipsis-v"></i>
                                                </button>
                                                <ul class="dropdown-menu">
                                                    <li><a class="dropdown-item" href="/suppliers/<?= $supplier->id ?>">
                                                        <i class="fas fa-eye me-2"></i><?= t('common.view') ?>
                                                    </a></li>
                                                    <?php if ($canEdit): ?>
                                                    <li><a class="dropdown-item" href="/suppliers/<?= $supplier->id ?>/edit">
                                                        <i class="fas fa-edit me-2"></i><?= t('common.edit') ?>
                                                    </a></li>
                                                    <?php endif; ?>
                                                    <li><hr class="dropdown-divider"></li>
                                                    <li><a class="dropdown-item" href="/purchase-orders/create?supplier_id=<?= $supplier->id ?>">
                                                        <i class="fas fa-shopping-cart me-2"></i><?= t('suppliers.create_purchase_order') ?>
                                                    </a></li>
                                                    <li><a class="dropdown-item" href="/suppliers/<?= $supplier->id ?>/products">
                                                        <i class="fas fa-boxes me-2"></i><?= t('suppliers.view_products') ?>
                                                    </a></li>
                                                    <li><a class="dropdown-item" href="/suppliers/<?= $supplier->id ?>/performance">
                                                        <i class="fas fa-chart-line me-2"></i><?= t('suppliers.performance') ?>
                                                    </a></li>
                                                    <?php if ($canDelete): ?>
                                                    <li><hr class="dropdown-divider"></li>
                                                    <li><a class="dropdown-item text-danger" href="#" onclick="deleteSupplier(<?= $supplier->id ?>, '<?= htmlspecialchars($supplier->name) ?>')">
                                                        <i class="fas fa-trash me-2"></i><?= t('common.delete') ?>
                                                    </a></li>
                                                    <?php endif; ?>
                                                </ul>
                                            </div>
                                        </div>
                                        
                                        <?php if ($supplier->contact_person): ?>
                                        <div class="small mb-2">
                                            <i class="fas fa-user text-muted me-2"></i>
                                            <?= htmlspecialchars($supplier->contact_person) ?>
                                        </div>
                                        <?php endif; ?>
                                        
                                        <?php if ($supplier->email): ?>
                                        <div class="small mb-2">
                                            <i class="fas fa-envelope text-muted me-2"></i>
                                            <a href="mailto:<?= htmlspecialchars($supplier->email) ?>" class="text-decoration-none">
                                                <?= htmlspecialchars($supplier->email) ?>
                                            </a>
                                        </div>
                                        <?php endif; ?>
                                        
                                        <?php if ($supplier->phone): ?>
                                        <div class="small mb-2">
                                            <i class="fas fa-phone text-muted me-2"></i>
                                            <a href="tel:<?= htmlspecialchars($supplier->phone) ?>" class="text-decoration-none">
                                                <?= htmlspecialchars($supplier->phone) ?>
                                            </a>
                                        </div>
                                        <?php endif; ?>
                                        
                                        <?php if ($supplier->city || $supplier->country): ?>
                                        <div class="small mb-3">
                                            <i class="fas fa-map-marker-alt text-muted me-2"></i>
                                            <?= htmlspecialchars(trim($supplier->city . ', ' . $supplier->country, ', ')) ?>
                                        </div>
                                        <?php endif; ?>
                                        
                                        <!-- Supplier Performance Metrics -->
                                        <div class="row text-center small">
                                            <div class="col-4">
                                                <div class="border-end">
                                                    <div class="fw-bold text-primary"><?= $supplier->products_count ?? 0 ?></div>
                                                    <div class="text-muted"><?= t('nav.products') ?></div>
                                                </div>
                                            </div>
                                            <div class="col-4">
                                                <div class="border-end">
                                                    <div class="fw-bold text-success"><?= $supplier->orders_count ?? 0 ?></div>
                                                    <div class="text-muted"><?= t('suppliers.orders') ?></div>
                                                </div>
                                            </div>
                                            <div class="col-4">
                                                <div class="fw-bold text-info">
                                                    <?php if (isset($supplier->rating)): ?>
                                                    <span class="rating">
                                                        <?php for ($i = 1; $i <= 5; $i++): ?>
                                                        <i class="fas fa-star <?= $i <= $supplier->rating ? 'text-warning' : 'text-muted' ?>" style="font-size: 0.8rem;"></i>
                                                        <?php endfor; ?>
                                                    </span>
                                                    <?php else: ?>
                                                    -
                                                    <?php endif; ?>
                                                </div>
                                                <div class="text-muted"><?= t('suppliers.rating') ?></div>
                                            </div>
                                        </div>
                                        
                                        <?php if (isset($supplier->payment_terms) || isset($supplier->delivery_time)): ?>
                                        <hr class="my-2">
                                        <div class="row small">
                                            <?php if (isset($supplier->payment_terms)): ?>
                                            <div class="col-6">
                                                <div class="text-muted"><?= t('suppliers.payment_terms') ?>:</div>
                                                <div><?= htmlspecialchars($supplier->payment_terms) ?></div>
                                            </div>
                                            <?php endif; ?>
                                            
                                            <?php if (isset($supplier->delivery_time)): ?>
                                            <div class="col-6">
                                                <div class="text-muted"><?= t('suppliers.delivery_time') ?>:</div>
                                                <div><?= $supplier->delivery_time ?> <?= t('suppliers.days') ?></div>
                                            </div>
                                            <?php endif; ?>
                                        </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                
                <!-- Pagination -->
                <?php if (isset($pagination) && $pagination['total_pages'] > 1): ?>
                <nav aria-label="<?= t('common.pagination') ?>" class="mt-4">
                    <ul class="pagination justify-content-center">
                        <?php if ($pagination['current_page'] > 1): ?>
                        <li class="page-item">
                            <a class="page-link" href="/suppliers?page=1<?= !empty($query_string) ? '&' . $query_string : '' ?>">
                                <?= t('pagination.first') ?>
                            </a>
                        </li>
                        <li class="page-item">
                            <a class="page-link" href="/suppliers?page=<?= $pagination['current_page'] - 1 ?><?= !empty($query_string) ? '&' . $query_string : '' ?>">
                                <?= t('pagination.previous') ?>
                            </a>
                        </li>
                        <?php endif; ?>
                        
                        <?php for ($i = max(1, $pagination['current_page'] - 2); $i <= min($pagination['total_pages'], $pagination['current_page'] + 2); $i++): ?>
                        <li class="page-item <?= $i == $pagination['current_page'] ? 'active' : '' ?>">
                            <a class="page-link" href="/suppliers?page=<?= $i ?><?= !empty($query_string) ? '&' . $query_string : '' ?>">
                                <?= $i ?>
                            </a>
                        </li>
                        <?php endfor; ?>
                        
                        <?php if ($pagination['current_page'] < $pagination['total_pages']): ?>
                        <li class="page-item">
                            <a class="page-link" href="/suppliers?page=<?= $pagination['current_page'] + 1 ?><?= !empty($query_string) ? '&' . $query_string : '' ?>">
                                <?= t('pagination.next') ?>
                            </a>
                        </li>
                        <li class="page-item">
                            <a class="page-link" href="/suppliers?page=<?= $pagination['total_pages'] ?><?= !empty($query_string) ? '&' . $query_string : '' ?>">
                                <?= t('pagination.last') ?>
                            </a>
                        </li>
                        <?php endif; ?>
                    </ul>
                </nav>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
.supplier-card {
    transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
}

.supplier-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
}

.rating .fa-star {
    margin-right: 1px;
}
</style>

<script>
function deleteSupplier(supplierId, supplierName) {
    if (confirm('<?= t('suppliers.confirm_delete') ?>'.replace(':name', supplierName))) {
        fetch(`/suppliers/${supplierId}`, {
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