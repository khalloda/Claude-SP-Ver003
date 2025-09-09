<?php
/**
 * File: app/views/products/show.php
 * Purpose: Product detail view with inventory and pricing information
 * Layout: Uses app layout with comprehensive product display
 */

$page_title = $page_title ?? t('products.product_details');
$active_nav = 'products';
ob_start();

// Get current user from passed data
$currentUser = $current_user ?? null;
$canEdit = $currentUser && in_array($currentUser['role'] ?? '', ['admin', 'manager', 'inventory']);
$canDelete = $currentUser && in_array($currentUser['role'] ?? '', ['admin', 'manager']);
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">
            <i class="fas fa-box me-2"></i><?= t('products.product_details') ?>
            <small class="text-muted ms-2"><?= htmlspecialchars($product->name) ?></small>
        </h1>
        <div class="btn-toolbar mb-2 mb-md-0">
            <div class="btn-group me-2">
                <button type="button" class="btn btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown">
                    <i class="fas fa-cog"></i> <?= t('common.actions') ?>
                </button>
                <ul class="dropdown-menu">
                    <?php if ($canEdit): ?>
                    <li><a class="dropdown-item" href="/products/<?= $product->id ?>/stock">
                        <i class="fas fa-boxes me-2"></i><?= t('products.manage_stock') ?>
                    </a></li>
                    <li><a class="dropdown-item" href="/products/<?= $product->id ?>/pricing">
                        <i class="fas fa-dollar-sign me-2"></i><?= t('products.update_pricing') ?>
                    </a></li>
                    <li><hr class="dropdown-divider"></li>
                    <?php endif; ?>
                    <li><a class="dropdown-item" href="/products/<?= $product->id ?>/duplicate">
                        <i class="fas fa-copy me-2"></i><?= t('common.duplicate') ?>
                    </a></li>
                    <li><a class="dropdown-item" href="/products/<?= $product->id ?>/barcode" target="_blank">
                        <i class="fas fa-barcode me-2"></i><?= t('products.generate_barcode') ?>
                    </a></li>
                </ul>
            </div>
            
            <?php if ($canEdit): ?>
            <a href="/products/<?= $product->id ?>/edit" class="btn btn-primary me-2">
                <i class="fas fa-edit"></i> <?= t('common.edit') ?>
            </a>
            <?php endif; ?>
            
            <a href="/products" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left"></i> <?= t('common.back') ?>
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-md-4">
            <!-- Product Image and Basic Info -->
            <div class="card mb-4">
                <div class="card-body text-center">
                    <?php if ($product->image): ?>
                    <img src="<?= htmlspecialchars($product->image) ?>" alt="<?= htmlspecialchars($product->name) ?>" 
                         class="img-fluid rounded mb-3" style="max-height: 200px;">
                    <?php else: ?>
                    <div class="bg-light rounded d-flex align-items-center justify-content-center mb-3" style="height: 200px;">
                        <i class="fas fa-image fa-4x text-muted"></i>
                    </div>
                    <?php endif; ?>
                    
                    <h4><?= htmlspecialchars($product->name) ?></h4>
                    <p class="text-muted"><?= htmlspecialchars($product->sku) ?></p>
                    
                    <div class="d-flex justify-content-center gap-2 mb-3">
                        <?php if ($product->is_active): ?>
                            <span class="badge bg-success"><?= t('common.active') ?></span>
                        <?php else: ?>
                            <span class="badge bg-secondary"><?= t('common.inactive') ?></span>
                        <?php endif; ?>
                        
                        <?php if ($product->is_featured): ?>
                            <span class="badge bg-warning text-dark"><?= t('products.featured') ?></span>
                        <?php endif; ?>
                    </div>
                    
                    <div class="row text-center">
                        <div class="col-6">
                            <div class="fw-bold text-primary h4"><?= number_format($product->price, 2) ?></div>
                            <div class="small text-muted"><?= t('products.selling_price') ?></div>
                        </div>
                        <div class="col-6">
                            <?php 
                            $stockClass = 'text-success';
                            if ($product->stock_quantity <= 0) {
                                $stockClass = 'text-danger';
                            } elseif ($product->stock_quantity <= ($product->low_stock_threshold ?? 10)) {
                                $stockClass = 'text-warning';
                            }
                            ?>
                            <div class="fw-bold <?= $stockClass ?> h4"><?= $product->stock_quantity ?></div>
                            <div class="small text-muted"><?= t('products.in_stock') ?></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Category and Supplier Info -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><?= t('products.classification') ?></h5>
                </div>
                <div class="card-body">
                    <?php if ($product->category_name): ?>
                    <div class="d-flex align-items-center mb-3">
                        <i class="fas fa-tags text-muted me-3" style="width: 20px;"></i>
                        <div>
                            <strong><?= t('categories.category') ?>:</strong><br>
                            <a href="/products?category_id=<?= $product->category_id ?>" class="text-decoration-none">
                                <?= htmlspecialchars($product->category_name) ?>
                            </a>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <?php if ($product->supplier_name): ?>
                    <div class="d-flex align-items-center mb-3">
                        <i class="fas fa-truck text-muted me-3" style="width: 20px;"></i>
                        <div>
                            <strong><?= t('suppliers.supplier') ?>:</strong><br>
                            <a href="/suppliers/<?= $product->supplier_id ?>" class="text-decoration-none">
                                <?= htmlspecialchars($product->supplier_name) ?>
                            </a>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <?php if ($product->brand): ?>
                    <div class="d-flex align-items-center mb-3">
                        <i class="fas fa-star text-muted me-3" style="width: 20px;"></i>
                        <div>
                            <strong><?= t('products.brand') ?>:</strong><br>
                            <?= htmlspecialchars($product->brand) ?>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <div class="d-flex align-items-center">
                        <i class="fas fa-balance-scale text-muted me-3" style="width: 20px;"></i>
                        <div>
                            <strong><?= t('products.unit') ?>:</strong><br>
                            <?= htmlspecialchars($product->unit ?? 'pcs') ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Stock Alert -->
            <?php if ($product->stock_quantity <= ($product->low_stock_threshold ?? 10)): ?>
            <div class="card border-warning mb-4">
                <div class="card-header bg-warning text-dark">
                    <h6 class="mb-0">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <?= t('products.stock_alert') ?>
                    </h6>
                </div>
                <div class="card-body">
                    <?php if ($product->stock_quantity <= 0): ?>
                    <p class="text-danger mb-2">
                        <strong><?= t('products.out_of_stock') ?></strong>
                    </p>
                    <p class="small text-muted mb-0"><?= t('products.out_of_stock_desc') ?></p>
                    <?php else: ?>
                    <p class="text-warning mb-2">
                        <strong><?= t('products.low_stock') ?></strong>
                    </p>
                    <p class="small text-muted mb-0">
                        <?= t('products.low_stock_desc') ?> 
                        (<?= t('products.threshold') ?>: <?= $product->low_stock_threshold ?? 10 ?>)
                    </p>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>
        
        <div class="col-md-8">
            <!-- Product Details Tabs -->
            <div class="card">
                <div class="card-header">
                    <ul class="nav nav-tabs card-header-tabs" id="productTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="details-tab" data-bs-toggle="tab" data-bs-target="#details" type="button" role="tab">
                                <i class="fas fa-info-circle me-2"></i><?= t('products.details') ?>
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="pricing-tab" data-bs-toggle="tab" data-bs-target="#pricing" type="button" role="tab">
                                <i class="fas fa-dollar-sign me-2"></i><?= t('products.pricing') ?>
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="inventory-tab" data-bs-toggle="tab" data-bs-target="#inventory" type="button" role="tab">
                                <i class="fas fa-boxes me-2"></i><?= t('products.inventory') ?>
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="history-tab" data-bs-toggle="tab" data-bs-target="#history" type="button" role="tab">
                                <i class="fas fa-history me-2"></i><?= t('products.history') ?>
                            </button>
                        </li>
                    </ul>
                </div>
                <div class="card-body">
                    <div class="tab-content" id="productTabsContent">
                        <!-- Details Tab -->
                        <div class="tab-pane fade show active" id="details" role="tabpanel" aria-labelledby="details-tab">
                            <div class="row">
                                <div class="col-md-6">
                                    <h6><?= t('products.basic_information') ?></h6>
                                    <table class="table table-borderless table-sm">
                                        <tr>
                                            <td><strong><?= t('products.name') ?>:</strong></td>
                                            <td><?= htmlspecialchars($product->name) ?></td>
                                        </tr>
                                        <tr>
                                            <td><strong><?= t('products.sku') ?>:</strong></td>
                                            <td><code><?= htmlspecialchars($product->sku) ?></code></td>
                                        </tr>
                                        <?php if ($product->barcode): ?>
                                        <tr>
                                            <td><strong><?= t('products.barcode') ?>:</strong></td>
                                            <td><code><?= htmlspecialchars($product->barcode) ?></code></td>
                                        </tr>
                                        <?php endif; ?>
                                        <?php if ($product->model): ?>
                                        <tr>
                                            <td><strong><?= t('products.model') ?>:</strong></td>
                                            <td><?= htmlspecialchars($product->model) ?></td>
                                        </tr>
                                        <?php endif; ?>
                                        <tr>
                                            <td><strong><?= t('products.created_at') ?>:</strong></td>
                                            <td><?= date('M d, Y', strtotime($product->created_at)) ?></td>
                                        </tr>
                                    </table>
                                </div>
                                
                                <div class="col-md-6">
                                    <h6><?= t('products.physical_properties') ?></h6>
                                    <table class="table table-borderless table-sm">
                                        <?php if ($product->weight): ?>
                                        <tr>
                                            <td><strong><?= t('products.weight') ?>:</strong></td>
                                            <td><?= $product->weight ?> kg</td>
                                        </tr>
                                        <?php endif; ?>
                                        <?php if ($product->dimensions): ?>
                                        <tr>
                                            <td><strong><?= t('products.dimensions') ?>:</strong></td>
                                            <td><?= htmlspecialchars($product->dimensions) ?></td>
                                        </tr>
                                        <?php endif; ?>
                                        <?php if ($product->color): ?>
                                        <tr>
                                            <td><strong><?= t('products.color') ?>:</strong></td>
                                            <td><?= htmlspecialchars($product->color) ?></td>
                                        </tr>
                                        <?php endif; ?>
                                        <?php if ($product->material): ?>
                                        <tr>
                                            <td><strong><?= t('products.material') ?>:</strong></td>
                                            <td><?= htmlspecialchars($product->material) ?></td>
                                        </tr>
                                        <?php endif; ?>
                                    </table>
                                </div>
                            </div>
                            
                            <?php if ($product->description): ?>
                            <hr>
                            <h6><?= t('products.description') ?></h6>
                            <p><?= nl2br(htmlspecialchars($product->description)) ?></p>
                            <?php endif; ?>
                            
                            <?php if ($product->specifications): ?>
                            <hr>
                            <h6><?= t('products.specifications') ?></h6>
                            <p><?= nl2br(htmlspecialchars($product->specifications)) ?></p>
                            <?php endif; ?>
                        </div>

                        <!-- Pricing Tab -->
                        <div class="tab-pane fade" id="pricing" role="tabpanel" aria-labelledby="pricing-tab">
                            <div class="row">
                                <div class="col-md-6">
                                    <h6><?= t('products.current_pricing') ?></h6>
                                    <table class="table table-borderless">
                                        <tr>
                                            <td><strong><?= t('products.cost_price') ?>:</strong></td>
                                            <td class="text-end"><?= number_format($product->cost_price ?? 0, 2) ?> <?= $product->currency ?? 'USD' ?></td>
                                        </tr>
                                        <tr>
                                            <td><strong><?= t('products.selling_price') ?>:</strong></td>
                                            <td class="text-end"><?= number_format($product->price, 2) ?> <?= $product->currency ?? 'USD' ?></td>
                                        </tr>
                                        <tr class="table-success">
                                            <td><strong><?= t('products.margin') ?>:</strong></td>
                                            <td class="text-end">
                                                <?php 
                                                $margin = ($product->cost_price > 0) ? 
                                                    (($product->price - $product->cost_price) / $product->cost_price * 100) : 0;
                                                ?>
                                                <?= number_format($margin, 1) ?>%
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                                
                                <div class="col-md-6">
                                    <h6><?= t('products.pricing_rules') ?></h6>
                                    <table class="table table-borderless">
                                        <?php if ($product->min_price): ?>
                                        <tr>
                                            <td><strong><?= t('products.minimum_price') ?>:</strong></td>
                                            <td class="text-end"><?= number_format($product->min_price, 2) ?> <?= $product->currency ?? 'USD' ?></td>
                                        </tr>
                                        <?php endif; ?>
                                        <?php if ($product->max_discount): ?>
                                        <tr>
                                            <td><strong><?= t('products.max_discount') ?>:</strong></td>
                                            <td class="text-end"><?= $product->max_discount ?>%</td>
                                        </tr>
                                        <?php endif; ?>
                                        <?php if ($product->tax_rate): ?>
                                        <tr>
                                            <td><strong><?= t('products.tax_rate') ?>:</strong></td>
                                            <td class="text-end"><?= $product->tax_rate ?>%</td>
                                        </tr>
                                        <?php endif; ?>
                                    </table>
                                </div>
                            </div>
                            
                            <?php if (!empty($product->pricing_history)): ?>
                            <hr>
                            <h6><?= t('products.pricing_history') ?></h6>
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th><?= t('products.date') ?></th>
                                            <th><?= t('products.old_price') ?></th>
                                            <th><?= t('products.new_price') ?></th>
                                            <th><?= t('products.changed_by') ?></th>
                                            <th><?= t('products.reason') ?></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach (array_slice($product->pricing_history, 0, 10) as $change): ?>
                                        <tr>
                                            <td><?= date('M d, Y H:i', strtotime($change->created_at)) ?></td>
                                            <td><?= number_format($change->old_price, 2) ?></td>
                                            <td><?= number_format($change->new_price, 2) ?></td>
                                            <td><?= htmlspecialchars($change->changed_by_name) ?></td>
                                            <td><?= htmlspecialchars($change->reason ?? '-') ?></td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                            <?php endif; ?>
                        </div>

                        <!-- Inventory Tab -->
                        <div class="tab-pane fade" id="inventory" role="tabpanel" aria-labelledby="inventory-tab">
                            <div class="row">
                                <div class="col-md-6">
                                    <h6><?= t('products.current_stock') ?></h6>
                                    <div class="card bg-light">
                                        <div class="card-body text-center">
                                            <div class="h2 <?= $stockClass ?>"><?= $product->stock_quantity ?></div>
                                            <div class="text-muted"><?= htmlspecialchars($product->unit ?? 'pcs') ?> <?= t('products.available') ?></div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <h6><?= t('products.stock_settings') ?></h6>
                                    <table class="table table-borderless">
                                        <tr>
                                            <td><strong><?= t('products.low_stock_threshold') ?>:</strong></td>
                                            <td><?= $product->low_stock_threshold ?? 10 ?> <?= htmlspecialchars($product->unit ?? 'pcs') ?></td>
                                        </tr>
                                        <tr>
                                            <td><strong><?= t('products.reorder_point') ?>:</strong></td>
                                            <td><?= $product->reorder_point ?? 0 ?> <?= htmlspecialchars($product->unit ?? 'pcs') ?></td>
                                        </tr>
                                        <tr>
                                            <td><strong><?= t('products.reorder_quantity') ?>:</strong></td>
                                            <td><?= $product->reorder_quantity ?? 0 ?> <?= htmlspecialchars($product->unit ?? 'pcs') ?></td>
                                        </tr>
                                        <tr>
                                            <td><strong><?= t('products.location') ?>:</strong></td>
                                            <td><?= htmlspecialchars($product->location ?? t('products.not_specified')) ?></td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                            
                            <?php if (!empty($product->stock_movements)): ?>
                            <hr>
                            <h6><?= t('products.recent_stock_movements') ?></h6>
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th><?= t('products.date') ?></th>
                                            <th><?= t('products.type') ?></th>
                                            <th><?= t('products.quantity') ?></th>
                                            <th><?= t('products.reference') ?></th>
                                            <th><?= t('products.notes') ?></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach (array_slice($product->stock_movements, 0, 10) as $movement): ?>
                                        <tr>
                                            <td><?= date('M d, Y H:i', strtotime($movement->created_at)) ?></td>
                                            <td>
                                                <span class="badge bg-<?= $movement->type === 'in' ? 'success' : 'danger' ?>">
                                                    <?= t('products.movement.' . $movement->type) ?>
                                                </span>
                                            </td>
                                            <td class="<?= $movement->type === 'in' ? 'text-success' : 'text-danger' ?>">
                                                <?= $movement->type === 'in' ? '+' : '-' ?><?= $movement->quantity ?>
                                            </td>
                                            <td><?= htmlspecialchars($movement->reference ?? '-') ?></td>
                                            <td><?= htmlspecialchars($movement->notes ?? '-') ?></td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                            <?php endif; ?>
                        </div>

                        <!-- History Tab -->
                        <div class="tab-pane fade" id="history" role="tabpanel" aria-labelledby="history-tab">
                            <?php if (!empty($product->sales_history)): ?>
                            <h6><?= t('products.sales_history') ?></h6>
                            <div class="table-responsive mb-4">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th><?= t('products.date') ?></th>
                                            <th><?= t('products.document') ?></th>
                                            <th><?= t('clients.client') ?></th>
                                            <th><?= t('products.quantity') ?></th>
                                            <th><?= t('products.unit_price') ?></th>
                                            <th><?= t('products.total') ?></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach (array_slice($product->sales_history, 0, 15) as $sale): ?>
                                        <tr>
                                            <td><?= date('M d, Y', strtotime($sale->created_at)) ?></td>
                                            <td>
                                                <a href="/<?= $sale->document_type ?>s/<?= $sale->document_id ?>" class="text-decoration-none">
                                                    <?= htmlspecialchars($sale->document_number) ?>
                                                </a>
                                            </td>
                                            <td><?= htmlspecialchars($sale->client_name) ?></td>
                                            <td><?= $sale->quantity ?></td>
                                            <td><?= number_format($sale->unit_price, 2) ?></td>
                                            <td><?= number_format($sale->total_price, 2) ?></td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                            <?php endif; ?>
                            
                            <?php if (!empty($product->activity_log)): ?>
                            <h6><?= t('products.activity_log') ?></h6>
                            <?php foreach (array_slice($product->activity_log, 0, 10) as $activity): ?>
                            <div class="d-flex mb-3">
                                <div class="flex-shrink-0 me-3">
                                    <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                                        <i class="fas fa-<?= $activity->icon ?? 'circle' ?> text-white" style="font-size: 0.8rem;"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1">
                                    <div class="fw-bold"><?= htmlspecialchars($activity->title) ?></div>
                                    <div class="text-muted"><?= htmlspecialchars($activity->description) ?></div>
                                    <div class="small text-muted"><?= date('M d, Y H:i', strtotime($activity->created_at)) ?></div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/app.php';
?>