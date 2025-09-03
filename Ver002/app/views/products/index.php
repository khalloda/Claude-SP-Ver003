<?php
/**
 * File: app/views/products/index.php
 * Purpose: Products listing page with inventory management
 * Layout: Uses app layout with advanced filtering and grid view
 */

$page_title = $page_title ?? t('nav.products');
$active_nav = 'products';
ob_start();

// Get current user from passed data
$currentUser = $current_user ?? null;
$canCreate = $currentUser && in_array($currentUser['role'] ?? '', ['admin', 'manager', 'inventory']);
$canEdit = $currentUser && in_array($currentUser['role'] ?? '', ['admin', 'manager', 'inventory']);
$canDelete = $currentUser && in_array($currentUser['role'] ?? '', ['admin', 'manager']);
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">
            <i class="fas fa-boxes me-2"></i><?= t('nav.products') ?>
            <span class="badge bg-secondary ms-2"><?= count($products) ?></span>
        </h1>
        <div class="btn-toolbar mb-2 mb-md-0">
            <div class="btn-group me-2">
                <button type="button" class="btn btn-sm btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown">
                    <i class="fas fa-download"></i> <?= t('common.export') ?>
                </button>
                <ul class="dropdown-menu">
                    <li><a class="dropdown-item" href="/products/export?format=csv"><i class="fas fa-file-csv me-2"></i>CSV</a></li>
                    <li><a class="dropdown-item" href="/products/export?format=excel"><i class="fas fa-file-excel me-2"></i>Excel</a></li>
                    <li><a class="dropdown-item" href="/products/export?format=pdf"><i class="fas fa-file-pdf me-2"></i>PDF</a></li>
                </ul>
            </div>
            <?php if ($canCreate): ?>
            <a href="/products/create" class="btn btn-primary">
                <i class="fas fa-plus"></i> <?= t('products.add_product') ?>
            </a>
            <?php endif; ?>
        </div>
    </div>

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="/products" class="row g-3">
                <div class="col-md-3">
                    <label for="search" class="form-label"><?= t('common.search') ?></label>
                    <input type="text" class="form-control" id="search" name="search" 
                           value="<?= htmlspecialchars($search ?? '') ?>" 
                           placeholder="<?= t('products.search_placeholder') ?>">
                </div>
                
                <div class="col-md-2">
                    <label for="category_id" class="form-label"><?= t('categories.category') ?></label>
                    <select class="form-select" id="category_id" name="category_id">
                        <option value=""><?= t('common.all_categories') ?></option>
                        <?php foreach ($categories ?? [] as $category): ?>
                        <option value="<?= $category->id ?>" <?= ($category_id ?? '') == $category->id ? 'selected' : '' ?>>
                            <?= htmlspecialchars($category->name) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="col-md-2">
                    <label for="supplier_id" class="form-label"><?= t('suppliers.supplier') ?></label>
                    <select class="form-select" id="supplier_id" name="supplier_id">
                        <option value=""><?= t('common.all_suppliers') ?></option>
                        <?php foreach ($suppliers ?? [] as $supplier): ?>
                        <option value="<?= $supplier->id ?>" <?= ($supplier_id ?? '') == $supplier->id ? 'selected' : '' ?>>
                            <?= htmlspecialchars($supplier->name) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="col-md-2">
                    <label for="status" class="form-label"><?= t('common.status') ?></label>
                    <select class="form-select" id="status" name="status">
                        <option value=""><?= t('common.all_statuses') ?></option>
                        <option value="active" <?= ($status ?? '') === 'active' ? 'selected' : '' ?>><?= t('common.active') ?></option>
                        <option value="inactive" <?= ($status ?? '') === 'inactive' ? 'selected' : '' ?>><?= t('common.inactive') ?></option>
                        <option value="low_stock" <?= ($status ?? '') === 'low_stock' ? 'selected' : '' ?>><?= t('products.low_stock') ?></option>
                        <option value="out_of_stock" <?= ($status ?? '') === 'out_of_stock' ? 'selected' : '' ?>><?= t('products.out_of_stock') ?></option>
                    </select>
                </div>
                
                <div class="col-md-1">
                    <label for="view_mode" class="form-label"><?= t('common.view') ?></label>
                    <select class="form-select" id="view_mode" name="view_mode" onchange="this.form.submit()">
                        <option value="grid" <?= ($view_mode ?? 'grid') === 'grid' ? 'selected' : '' ?>><?= t('common.grid') ?></option>
                        <option value="table" <?= ($view_mode ?? 'grid') === 'table' ? 'selected' : '' ?>><?= t('common.table') ?></option>
                    </select>
                </div>
                
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-outline-primary me-2">
                        <i class="fas fa-filter"></i> <?= t('common.filter') ?>
                    </button>
                    <a href="/products" class="btn btn-outline-secondary">
                        <i class="fas fa-times"></i>
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Products Display -->
    <div class="card">
        <div class="card-body">
            <?php if (empty($products)): ?>
                <div class="text-center py-5">
                    <i class="fas fa-boxes fa-4x text-muted mb-4"></i>
                    <h4><?= t('products.no_products_found') ?></h4>
                    <p class="text-muted"><?= t('products.no_products_desc') ?></p>
                    <?php if ($canCreate): ?>
                    <a href="/products/create" class="btn btn-primary">
                        <i class="fas fa-plus"></i> <?= t('products.add_first_product') ?>
                    </a>
                    <?php endif; ?>
                </div>
            <?php elseif (($view_mode ?? 'grid') === 'grid'): ?>
                <!-- Grid View -->
                <div class="row">
                    <?php foreach ($products as $product): ?>
                    <div class="col-xl-3 col-lg-4 col-md-6 mb-4">
                        <div class="card h-100 product-card">
                            <?php if ($product->image): ?>
                            <img src="<?= htmlspecialchars($product->image) ?>" class="card-img-top" alt="<?= htmlspecialchars($product->name) ?>" style="height: 200px; object-fit: cover;">
                            <?php else: ?>
                            <div class="card-img-top bg-light d-flex align-items-center justify-content-center" style="height: 200px;">
                                <i class="fas fa-image fa-3x text-muted"></i>
                            </div>
                            <?php endif; ?>
                            
                            <div class="card-body d-flex flex-column">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <h6 class="card-title mb-0">
                                        <?php 
                                        $productId = $product->attributes['id'] ?? $product->id ?? null;
                                        if ($productId && $productId > 0): 
                                        ?>
                                            <a href="/products/<?= $productId ?>" class="text-decoration-none">
                                                <?= htmlspecialchars($product->name ?? '') ?>
                                            </a>
                                        <?php else: ?>
                                            <?= htmlspecialchars($product->name ?? 'Unknown Product') ?>
                                        <?php endif; ?>
                                    </h6>
                                    
                                    <div class="dropdown">
                                        <button class="btn btn-sm btn-outline-secondary" type="button" data-bs-toggle="dropdown">
                                            <i class="fas fa-ellipsis-v"></i>
                                        </button>
                                        <ul class="dropdown-menu">
                                            <?php 
                                            $productId = $product->attributes['id'] ?? $product->id ?? null;
                                            ?>
                                            <!-- DEBUG PRODUCT FULL: <?= htmlspecialchars(print_r($product, true)) ?> -->
                                            <!-- DEBUG PRODUCT: ID=<?= htmlspecialchars($productId ?? 'NULL') ?>, Type=<?= gettype($productId) ?>, Name=<?= htmlspecialchars($product->name ?? 'NULL') ?> -->
                                            <li><a class="dropdown-item" href="/products/<?= $productId ?: 'INVALID' ?>">
                                                <i class="fas fa-eye me-2"></i><?= t('common.view') ?>
                                            </a></li>
                                            <?php if ($canEdit): ?>
                                            <li><a class="dropdown-item" href="/products/<?= $productId ?: 'INVALID' ?>/edit">
                                                <i class="fas fa-edit me-2"></i><?= t('common.edit') ?>
                                            </a></li>
                                            <?php endif; ?>
                                            <li><a class="dropdown-item" href="/products/<?= $productId ?: 'INVALID' ?>/duplicate">
                                                <i class="fas fa-copy me-2"></i><?= t('common.duplicate') ?>
                                            </a></li>
                                            <?php if ($canDelete): ?>
                                            <li><hr class="dropdown-divider"></li>
                                            <li><a class="dropdown-item text-danger" href="#" onclick="deleteProduct(<?= $productId ?: 0 ?>, '<?= htmlspecialchars($product->name ?? '') ?>')">
                                                <i class="fas fa-trash me-2"></i><?= t('common.delete') ?>
                                            </a></li>
                                            <?php endif; ?>
                                        </ul>
                                    </div>
                                </div>
                                
                                <p class="card-text text-muted small mb-2"><?= htmlspecialchars($product->sku) ?></p>
                                
                                <?php if ($product->description): ?>
                                <p class="card-text small mb-3"><?= htmlspecialchars(substr($product->description, 0, 100)) ?><?= strlen($product->description) > 100 ? '...' : '' ?></p>
                                <?php endif; ?>
                                
                                <div class="mt-auto">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <strong class="text-primary">$<?= number_format($product->selling_price ?? 0, 2) ?></strong>
                                        
                                        <?php
                                        $stockClass = 'text-success';
                                        $stockIcon = 'fa-check-circle';
                                        if (($product->stock_quantity ?? 0) <= 0) {
                                            $stockClass = 'text-danger';
                                            $stockIcon = 'fa-times-circle';
                                        } elseif (($product->stock_quantity ?? 0) <= ($product->min_stock_level ?? 10)) {
                                            $stockClass = 'text-warning';
                                            $stockIcon = 'fa-exclamation-triangle';
                                        }
                                        ?>
                                        <span class="<?= $stockClass ?>">
                                            <i class="fas <?= $stockIcon ?> me-1"></i>
                                            <?= $product->stock_quantity ?? 0 ?> <?= htmlspecialchars($product->unit_of_measure ?? 'pcs') ?>
                                        </span>
                                    </div>
                                    
                                    <div class="d-flex justify-content-between align-items-center">
                                        <small class="text-muted">
                                            <?php if (($product->status ?? 0) === 1): ?>
                                                <span class="badge bg-success"><?= t('common.active') ?></span>
                                            <?php else: ?>
                                                <span class="badge bg-secondary"><?= t('common.inactive') ?></span>
                                            <?php endif; ?>
                                        </small>
                                        
                                        <?php 
                                        // Get category name if category exists
                                        $categoryName = '';
                                        if (!empty($product->category_id)) {
                                            foreach ($categories as $category) {
                                                if ($category->id == $product->category_id) {
                                                    $categoryName = $category->name;
                                                    break;
                                                }
                                            }
                                        }
                                        ?>
                                        <?php if ($categoryName): ?>
                                        <small class="text-muted"><?= htmlspecialchars($categoryName) ?></small>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <!-- Table View -->
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th><?= t('products.image') ?></th>
                                <th><?= t('products.name') ?></th>
                                <th><?= t('products.sku') ?></th>
                                <th><?= t('categories.category') ?></th>
                                <th><?= t('products.price') ?></th>
                                <th><?= t('products.stock') ?></th>
                                <th><?= t('common.status') ?></th>
                                <th class="text-end"><?= t('common.actions') ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($products as $product): ?>
                            <tr>
                                <td>
                                    <?php if ($product->image): ?>
                                    <img src="<?= htmlspecialchars($product->image) ?>" class="product-image" alt="<?= htmlspecialchars($product->name) ?>">
                                    <?php else: ?>
                                    <div class="product-image bg-light d-flex align-items-center justify-content-center">
                                        <i class="fas fa-image text-muted"></i>
                                    </div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div>
                                        <strong><?= htmlspecialchars($product->name) ?></strong>
                                        <?php if ($product->description): ?>
                                        <br><small class="text-muted"><?= htmlspecialchars(substr($product->description, 0, 60)) ?><?= strlen($product->description) > 60 ? '...' : '' ?></small>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td><code><?= htmlspecialchars($product->sku ?? '') ?></code></td>
                                <td><?= htmlspecialchars($categoryName) ?></td>
                                <td><strong>$<?= number_format($product->selling_price ?? 0, 2) ?></strong></td>
                                <td>
                                    <?php
                                    $stockClass = 'text-success';
                                    $stockIcon = 'fa-check-circle';
                                    if (($product->stock_quantity ?? 0) <= 0) {
                                        $stockClass = 'text-danger';
                                        $stockIcon = 'fa-times-circle';
                                    } elseif (($product->stock_quantity ?? 0) <= ($product->min_stock_level ?? 10)) {
                                        $stockClass = 'text-warning';
                                        $stockIcon = 'fa-exclamation-triangle';
                                    }
                                    ?>
                                    <span class="<?= $stockClass ?>">
                                        <i class="fas <?= $stockIcon ?> me-1"></i>
                                        <?= $product->stock_quantity ?? 0 ?> <?= htmlspecialchars($product->unit_of_measure ?? 'pcs') ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if (($product->status ?? 0) === 1): ?>
                                        <span class="badge bg-success"><?= t('common.active') ?></span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary"><?= t('common.inactive') ?></span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end">
                                    <?php 
                                    $productId = $product->attributes['id'] ?? $product->id ?? null;
                                    if ($productId && $productId > 0): 
                                    ?>
                                    <div class="btn-group">
                                        <a href="/products/<?= $productId ?>" class="btn btn-sm btn-outline-primary" title="<?= t('common.view') ?>">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        
                                        <?php if ($canEdit): ?>
                                        <a href="/products/<?= $productId ?>/edit" class="btn btn-sm btn-outline-secondary" title="<?= t('common.edit') ?>">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <?php endif; ?>
                                        
                                        <div class="btn-group">
                                            <button type="button" class="btn btn-sm btn-outline-secondary dropdown-toggle dropdown-toggle-split" data-bs-toggle="dropdown">
                                                <span class="visually-hidden"><?= t('common.actions') ?></span>
                                            </button>
                                            <ul class="dropdown-menu">
                                                <li><a class="dropdown-item" href="/products/<?= $product->id ?>/duplicate">
                                                    <i class="fas fa-copy me-2"></i><?= t('common.duplicate') ?>
                                                </a></li>
                                                
                                                <?php if ($canEdit): ?>
                                                <li><a class="dropdown-item" href="/products/<?= $product->id ?>/stock">
                                                    <i class="fas fa-boxes me-2"></i><?= t('products.manage_stock') ?>
                                                </a></li>
                                                <?php endif; ?>
                                                
                                                <?php if ($canDelete): ?>
                                                <li><hr class="dropdown-divider"></li>
                                                <li><a class="dropdown-item text-danger" href="#" onclick="deleteProduct(<?= $product->id ?>, '<?= htmlspecialchars($product->name ?? '') ?>')">
                                                    <i class="fas fa-trash me-2"></i><?= t('common.delete') ?>
                                                </a></li>
                                                <?php endif; ?>
                                            </ul>
                                        </div>
                                    </div>
                                    <?php else: ?>
                                    <span class="text-muted small">
                                        <i class="fas fa-exclamation-triangle me-1"></i><?= t('messages.error.invalid_product_data') ?>
                                    </span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
            
            <!-- Pagination -->
            <?php if (isset($pagination) && $pagination['total_pages'] > 1): ?>
            <nav aria-label="<?= t('common.pagination') ?>" class="mt-4">
                <ul class="pagination justify-content-center">
                    <!-- Pagination implementation similar to clients -->
                </ul>
            </nav>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
.product-card {
    transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
}

.product-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
}
</style>

<script>
function deleteProduct(productId, productName) {
    if (confirm('<?= t('products.confirm_delete') ?>'.replace(':name', productName))) {
        fetch(`/products/${productId}`, {
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

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/app.php';
?>