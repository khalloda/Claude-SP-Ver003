<?php
/**
 * File: app/views/suppliers/show.php
 * Purpose: Supplier detail view with comprehensive relationship tracking
 * Layout: Uses app layout with professional supplier presentation
 */

$this->layout('layouts/app', [
    'title' => $page_title ?? t('suppliers.supplier_details'),
    'active_nav' => 'suppliers'
]);

$currentUser = $this->getCurrentUser();
$canEdit = $this->hasRole(['admin', 'manager', 'purchasing']);
$canDelete = $this->hasRole(['admin']) && ($supplier->purchase_orders_count ?? 0) === 0;
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">
            <i class="fas fa-truck me-2"></i><?= t('suppliers.supplier_details') ?>
        </h1>
        <div class="btn-toolbar mb-2 mb-md-0">
            <div class="btn-group me-2">
                <button type="button" class="btn btn-outline-primary dropdown-toggle" data-bs-toggle="dropdown">
                    <i class="fas fa-file-export"></i> <?= t('common.export') ?>
                </button>
                <ul class="dropdown-menu">
                    <li><a class="dropdown-item" href="/suppliers/<?= $supplier->id ?>/export/pdf">
                        <i class="fas fa-file-pdf me-2"></i><?= t('suppliers.export_pdf') ?>
                    </a></li>
                    <li><a class="dropdown-item" href="/suppliers/<?= $supplier->id ?>/export/vcard">
                        <i class="fas fa-address-card me-2"></i><?= t('suppliers.export_vcard') ?>
                    </a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item" href="/purchase-orders/create?supplier_id=<?= $supplier->id ?>">
                        <i class="fas fa-plus me-2"></i><?= t('suppliers.create_purchase_order') ?>
                    </a></li>
                </ul>
            </div>
            
            <?php if ($canEdit): ?>
            <a href="/suppliers/<?= $supplier->id ?>/edit" class="btn btn-primary me-2">
                <i class="fas fa-edit"></i> <?= t('common.edit') ?>
            </a>
            <?php endif; ?>
            
            <?php if ($canDelete): ?>
            <button class="btn btn-outline-danger me-2" onclick="deleteSupplier(<?= $supplier->id ?>)">
                <i class="fas fa-trash"></i> <?= t('common.delete') ?>
            </button>
            <?php endif; ?>
            
            <a href="/suppliers" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left"></i> <?= t('common.back') ?>
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8">
            <!-- Supplier Information -->
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><?= t('suppliers.supplier_information') ?></h5>
                    <div>
                        <?php 
                        $statusClass = match($supplier->status) {
                            'active' => 'bg-success',
                            'inactive' => 'bg-secondary',
                            'suspended' => 'bg-danger',
                            'pending' => 'bg-warning text-dark',
                            default => 'bg-secondary'
                        };
                        ?>
                        <span class="badge <?= $statusClass ?> me-2">
                            <?= t('suppliers.status.' . $supplier->status) ?>
                        </span>
                        
                        <?php if ($supplier->is_preferred): ?>
                        <span class="badge bg-primary">
                            <i class="fas fa-star"></i> <?= t('suppliers.preferred') ?>
                        </span>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <td><strong><?= t('suppliers.company_name') ?>:</strong></td>
                                    <td><?= htmlspecialchars($supplier->company_name) ?></td>
                                </tr>
                                <tr>
                                    <td><strong><?= t('suppliers.supplier_code') ?>:</strong></td>
                                    <td><code><?= htmlspecialchars($supplier->supplier_code) ?></code></td>
                                </tr>
                                <tr>
                                    <td><strong><?= t('suppliers.contact_person') ?>:</strong></td>
                                    <td><?= htmlspecialchars($supplier->contact_person ?? t('common.not_specified')) ?></td>
                                </tr>
                                <tr>
                                    <td><strong><?= t('suppliers.phone') ?>:</strong></td>
                                    <td>
                                        <?php if ($supplier->phone): ?>
                                        <a href="tel:<?= htmlspecialchars($supplier->phone) ?>"><?= htmlspecialchars($supplier->phone) ?></a>
                                        <?php else: ?>
                                        <?= t('common.not_specified') ?>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong><?= t('suppliers.email') ?>:</strong></td>
                                    <td>
                                        <?php if ($supplier->email): ?>
                                        <a href="mailto:<?= htmlspecialchars($supplier->email) ?>"><?= htmlspecialchars($supplier->email) ?></a>
                                        <?php else: ?>
                                        <?= t('common.not_specified') ?>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php if ($supplier->website): ?>
                                <tr>
                                    <td><strong><?= t('suppliers.website') ?>:</strong></td>
                                    <td>
                                        <a href="<?= htmlspecialchars($supplier->website) ?>" target="_blank" class="text-decoration-none">
                                            <?= htmlspecialchars($supplier->website) ?> <i class="fas fa-external-link-alt fa-sm"></i>
                                        </a>
                                    </td>
                                </tr>
                                <?php endif; ?>
                            </table>
                        </div>
                        
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <td><strong><?= t('suppliers.tax_number') ?>:</strong></td>
                                    <td><?= htmlspecialchars($supplier->tax_number ?? t('common.not_specified')) ?></td>
                                </tr>
                                <tr>
                                    <td><strong><?= t('suppliers.payment_terms') ?>:</strong></td>
                                    <td><?= htmlspecialchars($supplier->payment_terms ?? t('suppliers.net_30')) ?></td>
                                </tr>
                                <tr>
                                    <td><strong><?= t('suppliers.currency') ?>:</strong></td>
                                    <td><?= htmlspecialchars($supplier->currency ?? 'USD') ?></td>
                                </tr>
                                <tr>
                                    <td><strong><?= t('suppliers.created_at') ?>:</strong></td>
                                    <td><?= date('M d, Y H:i', strtotime($supplier->created_at)) ?></td>
                                </tr>
                                <tr>
                                    <td><strong><?= t('suppliers.last_updated') ?>:</strong></td>
                                    <td><?= date('M d, Y H:i', strtotime($supplier->updated_at)) ?></td>
                                </tr>
                                <tr>
                                    <td><strong><?= t('suppliers.rating') ?>:</strong></td>
                                    <td>
                                        <?php if ($supplier->rating): ?>
                                        <div class="d-flex align-items-center">
                                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                            <i class="fas fa-star text-<?= $i <= $supplier->rating ? 'warning' : 'muted' ?>"></i>
                                            <?php endfor; ?>
                                            <span class="ms-2">(<?= $supplier->rating ?>/5)</span>
                                        </div>
                                        <?php else: ?>
                                        <span class="text-muted"><?= t('suppliers.not_rated') ?></span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>
                    
                    <!-- Address Information -->
                    <?php if ($supplier->address): ?>
                    <hr>
                    <div class="row">
                        <div class="col-md-12">
                            <h6><i class="fas fa-map-marker-alt me-2"></i><?= t('suppliers.address') ?></h6>
                            <p><?= nl2br(htmlspecialchars($supplier->address)) ?></p>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <!-- Notes -->
                    <?php if ($supplier->notes): ?>
                    <hr>
                    <div class="row">
                        <div class="col-md-12">
                            <h6><i class="fas fa-sticky-note me-2"></i><?= t('suppliers.notes') ?></h6>
                            <p class="text-muted"><?= nl2br(htmlspecialchars($supplier->notes)) ?></p>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Tabbed Content -->
            <div class="card">
                <div class="card-header">
                    <ul class="nav nav-tabs card-header-tabs" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" data-bs-toggle="tab" href="#purchase-orders" role="tab">
                                <i class="fas fa-shopping-cart me-1"></i><?= t('suppliers.purchase_orders') ?>
                                <?php if (!empty($supplier->purchase_orders_count)): ?>
                                <span class="badge bg-primary ms-1"><?= $supplier->purchase_orders_count ?></span>
                                <?php endif; ?>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-bs-toggle="tab" href="#products" role="tab">
                                <i class="fas fa-box me-1"></i><?= t('suppliers.products') ?>
                                <?php if (!empty($supplier->products_count)): ?>
                                <span class="badge bg-primary ms-1"><?= $supplier->products_count ?></span>
                                <?php endif; ?>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-bs-toggle="tab" href="#invoices" role="tab">
                                <i class="fas fa-file-invoice me-1"></i><?= t('suppliers.invoices') ?>
                                <?php if (!empty($supplier->invoices_count)): ?>
                                <span class="badge bg-primary ms-1"><?= $supplier->invoices_count ?></span>
                                <?php endif; ?>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-bs-toggle="tab" href="#activity" role="tab">
                                <i class="fas fa-history me-1"></i><?= t('suppliers.activity') ?>
                            </a>
                        </li>
                    </ul>
                </div>
                <div class="card-body">
                    <div class="tab-content">
                        <!-- Purchase Orders Tab -->
                        <div class="tab-pane fade show active" id="purchase-orders" role="tabpanel">
                            <?php if (!empty($supplier->purchase_orders)): ?>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th><?= t('purchase_orders.order_number') ?></th>
                                            <th><?= t('purchase_orders.order_date') ?></th>
                                            <th class="text-center"><?= t('purchase_orders.status') ?></th>
                                            <th class="text-end"><?= t('purchase_orders.total') ?></th>
                                            <th></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($supplier->purchase_orders as $order): ?>
                                        <tr>
                                            <td>
                                                <a href="/purchase-orders/<?= $order->id ?>" class="fw-bold text-decoration-none">
                                                    <?= htmlspecialchars($order->order_number) ?>
                                                </a>
                                            </td>
                                            <td><?= date('M d, Y', strtotime($order->order_date)) ?></td>
                                            <td class="text-center">
                                                <?php
                                                $statusClass = match($order->status) {
                                                    'pending' => 'bg-warning text-dark',
                                                    'confirmed' => 'bg-info',
                                                    'shipped' => 'bg-secondary',
                                                    'received' => 'bg-success',
                                                    'cancelled' => 'bg-danger',
                                                    default => 'bg-secondary'
                                                };
                                                ?>
                                                <span class="badge <?= $statusClass ?>">
                                                    <?= t('purchase_orders.status.' . $order->status) ?>
                                                </span>
                                            </td>
                                            <td class="text-end"><?= number_format($order->total_amount, 2) ?> <?= $order->currency ?></td>
                                            <td>
                                                <a href="/purchase-orders/<?= $order->id ?>" class="btn btn-sm btn-outline-primary">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                            <?php else: ?>
                            <div class="text-center py-4">
                                <i class="fas fa-shopping-cart fa-3x text-muted mb-3"></i>
                                <p class="text-muted"><?= t('suppliers.no_purchase_orders') ?></p>
                                <a href="/purchase-orders/create?supplier_id=<?= $supplier->id ?>" class="btn btn-primary">
                                    <i class="fas fa-plus"></i> <?= t('suppliers.create_first_order') ?>
                                </a>
                            </div>
                            <?php endif; ?>
                        </div>

                        <!-- Products Tab -->
                        <div class="tab-pane fade" id="products" role="tabpanel">
                            <?php if (!empty($supplier->products)): ?>
                            <div class="row">
                                <?php foreach ($supplier->products as $product): ?>
                                <div class="col-md-6 col-lg-4 mb-3">
                                    <div class="card h-100">
                                        <?php if ($product->image): ?>
                                        <img src="<?= htmlspecialchars($product->image) ?>" class="card-img-top" alt="" style="height: 150px; object-fit: cover;">
                                        <?php else: ?>
                                        <div class="card-img-top bg-light d-flex align-items-center justify-content-center" style="height: 150px;">
                                            <i class="fas fa-box fa-3x text-muted"></i>
                                        </div>
                                        <?php endif; ?>
                                        <div class="card-body d-flex flex-column">
                                            <h6 class="card-title">
                                                <a href="/products/<?= $product->id ?>" class="text-decoration-none">
                                                    <?= htmlspecialchars($product->name) ?>
                                                </a>
                                            </h6>
                                            <p class="card-text text-muted small"><?= htmlspecialchars($product->sku) ?></p>
                                            <div class="mt-auto">
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <span class="fw-bold"><?= number_format($product->supplier_price ?? 0, 2) ?> <?= $supplier->currency ?></span>
                                                    <span class="badge bg-<?= ($product->stock_quantity ?? 0) > 0 ? 'success' : 'danger' ?>">
                                                        <?= ($product->stock_quantity ?? 0) > 0 ? t('products.in_stock') : t('products.out_of_stock') ?>
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                            <?php else: ?>
                            <div class="text-center py-4">
                                <i class="fas fa-box fa-3x text-muted mb-3"></i>
                                <p class="text-muted"><?= t('suppliers.no_products') ?></p>
                            </div>
                            <?php endif; ?>
                        </div>

                        <!-- Invoices Tab -->
                        <div class="tab-pane fade" id="invoices" role="tabpanel">
                            <?php if (!empty($supplier->invoices)): ?>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th><?= t('invoices.invoice_number') ?></th>
                                            <th><?= t('invoices.invoice_date') ?></th>
                                            <th><?= t('invoices.due_date') ?></th>
                                            <th class="text-center"><?= t('invoices.status') ?></th>
                                            <th class="text-end"><?= t('invoices.total') ?></th>
                                            <th></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($supplier->invoices as $invoice): ?>
                                        <tr>
                                            <td>
                                                <a href="/invoices/<?= $invoice->id ?>" class="fw-bold text-decoration-none">
                                                    <?= htmlspecialchars($invoice->invoice_number) ?>
                                                </a>
                                            </td>
                                            <td><?= date('M d, Y', strtotime($invoice->invoice_date)) ?></td>
                                            <td>
                                                <?= date('M d, Y', strtotime($invoice->due_date)) ?>
                                                <?php if (strtotime($invoice->due_date) < time() && $invoice->status !== 'paid'): ?>
                                                <span class="badge bg-danger ms-2"><?= t('invoices.overdue') ?></span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-center">
                                                <?php
                                                $statusClass = match($invoice->status) {
                                                    'draft' => 'bg-secondary',
                                                    'sent' => 'bg-info',
                                                    'partial' => 'bg-warning text-dark',
                                                    'paid' => 'bg-success',
                                                    'overdue' => 'bg-danger',
                                                    default => 'bg-secondary'
                                                };
                                                ?>
                                                <span class="badge <?= $statusClass ?>">
                                                    <?= t('invoices.status.' . $invoice->status) ?>
                                                </span>
                                            </td>
                                            <td class="text-end"><?= number_format($invoice->total_amount, 2) ?> <?= $invoice->currency ?></td>
                                            <td>
                                                <a href="/invoices/<?= $invoice->id ?>" class="btn btn-sm btn-outline-primary">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                            <?php else: ?>
                            <div class="text-center py-4">
                                <i class="fas fa-file-invoice fa-3x text-muted mb-3"></i>
                                <p class="text-muted"><?= t('suppliers.no_invoices') ?></p>
                            </div>
                            <?php endif; ?>
                        </div>

                        <!-- Activity Tab -->
                        <div class="tab-pane fade" id="activity" role="tabpanel">
                            <?php if (!empty($supplier->activity_log)): ?>
                            <?php foreach ($supplier->activity_log as $activity): ?>
                            <div class="d-flex mb-4">
                                <div class="flex-shrink-0 me-3">
                                    <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                        <i class="fas fa-<?= $activity->icon ?? 'circle' ?> text-white"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1">
                                    <div class="fw-bold"><?= htmlspecialchars($activity->title) ?></div>
                                    <div class="text-muted"><?= htmlspecialchars($activity->description) ?></div>
                                    <div class="small text-muted"><?= date('M d, Y H:i', strtotime($activity->created_at)) ?></div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                            <?php else: ?>
                            <div class="text-center py-4">
                                <i class="fas fa-history fa-3x text-muted mb-3"></i>
                                <p class="text-muted"><?= t('suppliers.no_activity') ?></p>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <!-- Statistics -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><?= t('suppliers.statistics') ?></h5>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-6">
                            <div class="border rounded p-3 mb-3">
                                <div class="h4 mb-1 text-primary"><?= $supplier->purchase_orders_count ?? 0 ?></div>
                                <small class="text-muted"><?= t('suppliers.total_orders') ?></small>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="border rounded p-3 mb-3">
                                <div class="h4 mb-1 text-success"><?= number_format($supplier->total_spent ?? 0, 0) ?></div>
                                <small class="text-muted"><?= t('suppliers.total_spent') ?></small>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="border rounded p-3 mb-3">
                                <div class="h4 mb-1 text-info"><?= $supplier->products_count ?? 0 ?></div>
                                <small class="text-muted"><?= t('suppliers.products_supplied') ?></small>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="border rounded p-3 mb-3">
                                <div class="h4 mb-1 text-warning"><?= number_format($supplier->average_delivery_days ?? 0, 1) ?></div>
                                <small class="text-muted"><?= t('suppliers.avg_delivery') ?></small>
                            </div>
                        </div>
                    </div>
                    
                    <?php if ($supplier->last_order_date): ?>
                    <div class="border-top pt-3">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="text-muted"><?= t('suppliers.last_order') ?>:</span>
                            <span><?= date('M d, Y', strtotime($supplier->last_order_date)) ?></span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="text-muted"><?= t('suppliers.days_since_last_order') ?>:</span>
                            <span class="badge bg-<?= (time() - strtotime($supplier->last_order_date)) / 86400 > 90 ? 'warning' : 'info' ?>">
                                <?= ceil((time() - strtotime($supplier->last_order_date)) / 86400) ?> <?= t('common.days') ?>
                            </span>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Contact Information -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><?= t('suppliers.contact_information') ?></h5>
                </div>
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        <div class="user-avatar bg-primary text-white rounded-circle me-3 d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                            <?= strtoupper(substr($supplier->company_name, 0, 1)) ?>
                        </div>
                        <div>
                            <h6 class="mb-0"><?= htmlspecialchars($supplier->company_name) ?></h6>
                            <small class="text-muted"><?= htmlspecialchars($supplier->supplier_code) ?></small>
                        </div>
                    </div>
                    
                    <?php if ($supplier->contact_person): ?>
                    <div class="mb-2">
                        <i class="fas fa-user text-muted me-2"></i>
                        <?= htmlspecialchars($supplier->contact_person) ?>
                    </div>
                    <?php endif; ?>
                    
                    <?php if ($supplier->email): ?>
                    <div class="mb-2">
                        <i class="fas fa-envelope text-muted me-2"></i>
                        <a href="mailto:<?= htmlspecialchars($supplier->email) ?>"><?= htmlspecialchars($supplier->email) ?></a>
                    </div>
                    <?php endif; ?>
                    
                    <?php if ($supplier->phone): ?>
                    <div class="mb-2">
                        <i class="fas fa-phone text-muted me-2"></i>
                        <a href="tel:<?= htmlspecialchars($supplier->phone) ?>"><?= htmlspecialchars($supplier->phone) ?></a>
                    </div>
                    <?php endif; ?>
                    
                    <?php if ($supplier->website): ?>
                    <div class="mb-2">
                        <i class="fas fa-globe text-muted me-2"></i>
                        <a href="<?= htmlspecialchars($supplier->website) ?>" target="_blank" class="text-decoration-none">
                            <?= htmlspecialchars($supplier->website) ?> <i class="fas fa-external-link-alt fa-sm"></i>
                        </a>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><?= t('suppliers.quick_actions') ?></h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="/purchase-orders/create?supplier_id=<?= $supplier->id ?>" class="btn btn-primary">
                            <i class="fas fa-plus"></i> <?= t('suppliers.new_purchase_order') ?>
                        </a>
                        <?php if ($supplier->email): ?>
                        <a href="mailto:<?= htmlspecialchars($supplier->email) ?>" class="btn btn-outline-primary">
                            <i class="fas fa-envelope"></i> <?= t('suppliers.send_email') ?>
                        </a>
                        <?php endif; ?>
                        <button class="btn btn-outline-secondary" onclick="updateRating(<?= $supplier->id ?>)">
                            <i class="fas fa-star"></i> <?= t('suppliers.update_rating') ?>
                        </button>
                        <?php if ($canEdit): ?>
                        <a href="/suppliers/<?= $supplier->id ?>/edit" class="btn btn-outline-secondary">
                            <i class="fas fa-edit"></i> <?= t('common.edit') ?>
                        </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function deleteSupplier(supplierId) {
    if (confirm('<?= t('suppliers.confirm_delete') ?>')) {
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
                window.location.href = '/suppliers';
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

function updateRating(supplierId) {
    const rating = prompt('<?= t('suppliers.enter_rating') ?>', '<?= $supplier->rating ?? 5 ?>');
    if (rating && rating >= 1 && rating <= 5) {
        fetch(`/suppliers/${supplierId}/rating`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({rating: parseInt(rating)})
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