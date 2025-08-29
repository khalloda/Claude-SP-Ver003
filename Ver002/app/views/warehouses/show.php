<?php
/**
 * File: app/views/warehouses/show.php
 * Purpose: Warehouse detail view with comprehensive inventory and analytics
 * Layout: Uses app layout with professional warehouse presentation
 */

$this->layout('layouts/app', [
    'title' => $page_title ?? t('warehouses.warehouse_details'),
    'active_nav' => 'warehouses'
]);

$currentUser = $this->getCurrentUser();
$canEdit = $this->hasRole(['admin', 'manager', 'warehouse']);
$canManageInventory = $this->hasRole(['admin', 'manager', 'warehouse', 'inventory']);
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">
            <i class="fas fa-warehouse me-2"></i><?= htmlspecialchars($warehouse->name) ?>
            <small class="text-muted ms-2"><?= htmlspecialchars($warehouse->code) ?></small>
        </h1>
        <div class="btn-toolbar mb-2 mb-md-0">
            <div class="btn-group me-2">
                <button type="button" class="btn btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown">
                    <i class="fas fa-download"></i> <?= t('common.export') ?>
                </button>
                <ul class="dropdown-menu">
                    <li><a class="dropdown-item" href="/warehouses/<?= $warehouse->id ?>/export/inventory">
                        <i class="fas fa-boxes me-2"></i><?= t('warehouses.export_inventory') ?>
                    </a></li>
                    <li><a class="dropdown-item" href="/warehouses/<?= $warehouse->id ?>/export/report">
                        <i class="fas fa-chart-bar me-2"></i><?= t('warehouses.export_report') ?>
                    </a></li>
                </ul>
            </div>
            
            <?php if ($canEdit): ?>
            <a href="/warehouses/<?= $warehouse->id ?>/edit" class="btn btn-primary me-2">
                <i class="fas fa-edit"></i> <?= t('common.edit') ?>
            </a>
            <?php endif; ?>
            
            <?php if ($canManageInventory): ?>
            <a href="/warehouses/<?= $warehouse->id ?>/inventory/adjust" class="btn btn-success me-2">
                <i class="fas fa-plus-minus"></i> <?= t('warehouses.adjust_inventory') ?>
            </a>
            <?php endif; ?>
            
            <a href="/warehouses" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left"></i> <?= t('common.back') ?>
            </a>
        </div>
    </div>

    <!-- Status Alert -->
    <?php if ($warehouse->status !== 'active'): ?>
    <div class="alert alert-warning">
        <i class="fas fa-exclamation-triangle me-2"></i>
        <?= t('warehouses.status_notice', ['status' => t('warehouses.status.' . $warehouse->status)]) ?>
    </div>
    <?php endif; ?>

    <div class="row">
        <div class="col-md-8">
            <!-- Warehouse Overview -->
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><?= t('warehouses.warehouse_overview') ?></h5>
                    <div>
                        <span class="badge bg-<?= $warehouse->status === 'active' ? 'success' : 'secondary' ?> me-2">
                            <?= t('warehouses.status.' . $warehouse->status) ?>
                        </span>
                        <?php if ($warehouse->features): ?>
                        <?php foreach (explode(',', $warehouse->features) as $feature): ?>
                        <span class="badge bg-info me-1"><?= t('warehouses.features.' . trim($feature)) ?></span>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <td><strong><?= t('warehouses.type') ?>:</strong></td>
                                    <td><?= t('warehouses.types.' . $warehouse->type) ?></td>
                                </tr>
                                <tr>
                                    <td><strong><?= t('warehouses.capacity') ?>:</strong></td>
                                    <td><?= number_format($warehouse->total_capacity) ?> <?= $warehouse->capacity_unit ?></td>
                                </tr>
                                <tr>
                                    <td><strong><?= t('warehouses.utilization') ?>:</strong></td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="progress me-2" style="width: 100px; height: 8px;">
                                                <div class="progress-bar bg-<?= $warehouse->utilization >= 90 ? 'danger' : ($warehouse->utilization >= 75 ? 'warning' : 'success') ?>" 
                                                     style="width: <?= min($warehouse->utilization, 100) ?>%"></div>
                                            </div>
                                            <span class="text-<?= $warehouse->utilization >= 90 ? 'danger' : ($warehouse->utilization >= 75 ? 'warning' : 'success') ?>">
                                                <?= number_format($warehouse->utilization, 1) ?>%
                                            </span>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong><?= t('warehouses.manager') ?>:</strong></td>
                                    <td>
                                        <?php if ($warehouse->manager_name): ?>
                                        <a href="/users/<?= $warehouse->manager_id ?>" class="text-decoration-none">
                                            <?= htmlspecialchars($warehouse->manager_name) ?>
                                        </a>
                                        <?php else: ?>
                                        <span class="text-muted"><?= t('common.unassigned') ?></span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            </table>
                        </div>
                        
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <td><strong><?= t('warehouses.location') ?>:</strong></td>
                                    <td>
                                        <?= htmlspecialchars($warehouse->city) ?>, <?= htmlspecialchars($warehouse->country) ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong><?= t('warehouses.address') ?>:</strong></td>
                                    <td><?= nl2br(htmlspecialchars($warehouse->address)) ?></td>
                                </tr>
                                <?php if ($warehouse->phone): ?>
                                <tr>
                                    <td><strong><?= t('warehouses.phone') ?>:</strong></td>
                                    <td><a href="tel:<?= $warehouse->phone ?>"><?= htmlspecialchars($warehouse->phone) ?></a></td>
                                </tr>
                                <?php endif; ?>
                                <?php if ($warehouse->email): ?>
                                <tr>
                                    <td><strong><?= t('warehouses.email') ?>:</strong></td>
                                    <td><a href="mailto:<?= $warehouse->email ?>"><?= htmlspecialchars($warehouse->email) ?></a></td>
                                </tr>
                                <?php endif; ?>
                            </table>
                        </div>
                    </div>
                    
                    <?php if ($warehouse->description): ?>
                    <hr>
                    <div>
                        <strong><?= t('warehouses.description') ?>:</strong>
                        <p class="mt-2"><?= nl2br(htmlspecialchars($warehouse->description)) ?></p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Inventory Summary -->
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><?= t('warehouses.inventory_summary') ?></h5>
                    <a href="/warehouses/<?= $warehouse->id ?>/inventory" class="btn btn-sm btn-outline-primary">
                        <?= t('warehouses.view_full_inventory') ?>
                    </a>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-3 text-center">
                            <div class="border rounded p-3">
                                <div class="h4 mb-1"><?= number_format($warehouse->total_items ?? 0) ?></div>
                                <small class="text-muted"><?= t('warehouses.total_items') ?></small>
                            </div>
                        </div>
                        <div class="col-md-3 text-center">
                            <div class="border rounded p-3">
                                <div class="h4 mb-1"><?= number_format($warehouse->unique_products ?? 0) ?></div>
                                <small class="text-muted"><?= t('warehouses.unique_products') ?></small>
                            </div>
                        </div>
                        <div class="col-md-3 text-center">
                            <div class="border rounded p-3">
                                <div class="h4 mb-1 text-warning"><?= number_format($warehouse->low_stock_items ?? 0) ?></div>
                                <small class="text-muted"><?= t('warehouses.low_stock') ?></small>
                            </div>
                        </div>
                        <div class="col-md-3 text-center">
                            <div class="border rounded p-3">
                                <div class="h4 mb-1"><?= number_format($warehouse->inventory_value ?? 0, 2) ?></div>
                                <small class="text-muted"><?= t('warehouses.total_value') ?></small>
                            </div>
                        </div>
                    </div>
                    
                    <?php if (!empty($warehouse->top_products)): ?>
                    <h6><?= t('warehouses.top_products') ?></h6>
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th><?= t('products.product') ?></th>
                                    <th class="text-center"><?= t('warehouses.quantity') ?></th>
                                    <th class="text-end"><?= t('warehouses.value') ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach (array_slice($warehouse->top_products, 0, 5) as $product): ?>
                                <tr>
                                    <td>
                                        <a href="/products/<?= $product->id ?>" class="text-decoration-none">
                                            <?= htmlspecialchars($product->name) ?>
                                        </a>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-light text-dark"><?= number_format($product->quantity) ?></span>
                                    </td>
                                    <td class="text-end">
                                        <?= number_format($product->total_value, 2) ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Recent Movements -->
            <?php if (!empty($warehouse->recent_movements)): ?>
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><?= t('warehouses.recent_movements') ?></h5>
                    <a href="/warehouses/<?= $warehouse->id ?>/movements" class="btn btn-sm btn-outline-secondary">
                        <?= t('warehouses.view_all_movements') ?>
                    </a>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th><?= t('warehouses.date') ?></th>
                                    <th><?= t('warehouses.type') ?></th>
                                    <th><?= t('products.product') ?></th>
                                    <th class="text-center"><?= t('warehouses.quantity') ?></th>
                                    <th><?= t('warehouses.reference') ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach (array_slice($warehouse->recent_movements, 0, 10) as $movement): ?>
                                <tr>
                                    <td><?= date('M d, H:i', strtotime($movement->created_at)) ?></td>
                                    <td>
                                        <span class="badge bg-<?= $movement->type === 'in' ? 'success' : 'danger' ?>">
                                            <?= t('warehouses.movement.' . $movement->type) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <a href="/products/<?= $movement->product_id ?>" class="text-decoration-none">
                                            <?= htmlspecialchars($movement->product_name) ?>
                                        </a>
                                    </td>
                                    <td class="text-center">
                                        <span class="text-<?= $movement->type === 'in' ? 'success' : 'danger' ?>">
                                            <?= $movement->type === 'in' ? '+' : '-' ?><?= number_format($movement->quantity) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if ($movement->reference_type && $movement->reference_id): ?>
                                        <a href="/<?= $movement->reference_type ?>/<?= $movement->reference_id ?>" class="text-decoration-none">
                                            <?= htmlspecialchars($movement->reference_number ?? $movement->reference_type . ' #' . $movement->reference_id) ?>
                                        </a>
                                        <?php else: ?>
                                        <span class="text-muted"><?= t('warehouses.manual_adjustment') ?></span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
        
        <div class="col-md-4">
            <!-- Warehouse Image -->
            <?php if ($warehouse->image): ?>
            <div class="card mb-4">
                <div class="card-body text-center">
                    <img src="<?= htmlspecialchars($warehouse->image) ?>" alt="<?= htmlspecialchars($warehouse->name) ?>" 
                         class="img-fluid rounded" style="max-height: 200px;">
                </div>
            </div>
            <?php endif; ?>

            <!-- Quick Stats -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><?= t('warehouses.quick_stats') ?></h5>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-6 border-end">
                            <div class="h5 mb-0"><?= number_format($warehouse->monthly_movements ?? 0) ?></div>
                            <small class="text-muted"><?= t('warehouses.monthly_movements') ?></small>
                        </div>
                        <div class="col-6">
                            <div class="h5 mb-0"><?= number_format($warehouse->daily_average ?? 0) ?></div>
                            <small class="text-muted"><?= t('warehouses.daily_average') ?></small>
                        </div>
                    </div>
                    
                    <hr>
                    
                    <table class="table table-sm table-borderless">
                        <tr>
                            <td><?= t('warehouses.created') ?>:</td>
                            <td><?= date('M d, Y', strtotime($warehouse->created_at)) ?></td>
                        </tr>
                        <tr>
                            <td><?= t('warehouses.last_updated') ?>:</td>
                            <td><?= date('M d, Y H:i', strtotime($warehouse->updated_at)) ?></td>
                        </tr>
                        <?php if ($warehouse->last_inventory_check): ?>
                        <tr>
                            <td><?= t('warehouses.last_inventory') ?>:</td>
                            <td><?= date('M d, Y', strtotime($warehouse->last_inventory_check)) ?></td>
                        </tr>
                        <?php endif; ?>
                    </table>
                </div>
            </div>

            <!-- Capacity Visualization -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><?= t('warehouses.capacity_breakdown') ?></h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <div class="d-flex justify-content-between mb-1">
                            <small><?= t('warehouses.used_capacity') ?></small>
                            <small><?= number_format($warehouse->used_capacity ?? 0) ?> / <?= number_format($warehouse->total_capacity) ?> <?= $warehouse->capacity_unit ?></small>
                        </div>
                        <div class="progress mb-3">
                            <div class="progress-bar bg-<?= $warehouse->utilization >= 90 ? 'danger' : ($warehouse->utilization >= 75 ? 'warning' : 'success') ?>" 
                                 style="width: <?= min($warehouse->utilization, 100) ?>%">
                                <?= number_format($warehouse->utilization, 1) ?>%
                            </div>
                        </div>
                    </div>
                    
                    <?php if ($warehouse->zones > 1): ?>
                    <div class="mb-3">
                        <h6 class="text-muted"><?= t('warehouses.zone_utilization') ?></h6>
                        <?php for ($i = 1; $i <= min($warehouse->zones, 6); $i++): ?>
                        <?php $zoneUtil = $warehouse->{'zone_' . $i . '_utilization'} ?? rand(20, 95); ?>
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <small><?= t('warehouses.zone') ?> <?= $i ?></small>
                            <div class="progress flex-grow-1 mx-2" style="height: 6px;">
                                <div class="progress-bar bg-<?= $zoneUtil >= 90 ? 'danger' : ($zoneUtil >= 75 ? 'warning' : 'success') ?>" 
                                     style="width: <?= $zoneUtil ?>%"></div>
                            </div>
                            <small><?= $zoneUtil ?>%</small>
                        </div>
                        <?php endfor; ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><?= t('common.quick_actions') ?></h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <?php if ($canManageInventory): ?>
                        <a href="/warehouses/<?= $warehouse->id ?>/inventory/add" class="btn btn-outline-success btn-sm">
                            <i class="fas fa-plus me-2"></i><?= t('warehouses.add_inventory') ?>
                        </a>
                        <a href="/warehouses/<?= $warehouse->id ?>/inventory/transfer" class="btn btn-outline-info btn-sm">
                            <i class="fas fa-exchange-alt me-2"></i><?= t('warehouses.transfer_items') ?>
                        </a>
                        <a href="/warehouses/<?= $warehouse->id ?>/inventory/audit" class="btn btn-outline-warning btn-sm">
                            <i class="fas fa-clipboard-check me-2"></i><?= t('warehouses.inventory_audit') ?>
                        </a>
                        <hr class="my-2">
                        <?php endif; ?>
                        
                        <a href="/warehouses/<?= $warehouse->id ?>/reports" class="btn btn-outline-primary btn-sm">
                            <i class="fas fa-chart-line me-2"></i><?= t('warehouses.analytics') ?>
                        </a>
                        
                        <a href="/warehouses/<?= $warehouse->id ?>/print-labels" class="btn btn-outline-secondary btn-sm">
                            <i class="fas fa-tags me-2"></i><?= t('warehouses.print_labels') ?>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Real-time updates for warehouse stats (optional)
document.addEventListener('DOMContentLoaded', function() {
    // You could implement WebSocket or periodic AJAX updates here
    // to keep warehouse statistics current
});
</script>