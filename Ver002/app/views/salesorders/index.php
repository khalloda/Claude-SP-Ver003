<?php
/**
 * File: app/views/salesorders/index.php
 * Purpose: Sales Orders listing page with workflow management
 * Layout: Uses app layout with order status tracking
 */

$this->layout('layouts/app', [
    'title' => $page_title ?? t('nav.sales_orders'),
    'active_nav' => 'sales_orders'
]);

$currentUser = $this->getCurrentUser();
$canCreate = $this->hasRole(['admin', 'manager', 'sales']);
$canEdit = $this->hasRole(['admin', 'manager', 'sales']);
$canDelete = $this->hasRole(['admin', 'manager']);
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">
            <i class="fas fa-shopping-cart me-2"></i><?= t('nav.sales_orders') ?>
            <span class="badge bg-secondary ms-2"><?= count($sales_orders ?? []) ?></span>
        </h1>
        <div class="btn-toolbar mb-2 mb-md-0">
            <div class="btn-group me-2">
                <button type="button" class="btn btn-sm btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown">
                    <i class="fas fa-chart-line"></i> <?= t('sales_orders.reports') ?>
                </button>
                <ul class="dropdown-menu">
                    <li><a class="dropdown-item" href="/reports/sales-orders/summary"><i class="fas fa-chart-pie me-2"></i><?= t('reports.orders_summary') ?></a></li>
                    <li><a class="dropdown-item" href="/reports/sales-orders/fulfillment"><i class="fas fa-truck me-2"></i><?= t('reports.fulfillment_report') ?></a></li>
                    <li><a class="dropdown-item" href="/reports/sales-orders/revenue"><i class="fas fa-dollar-sign me-2"></i><?= t('reports.revenue_analysis') ?></a></li>
                </ul>
            </div>
            <?php if ($canCreate): ?>
            <a href="/sales-orders/create" class="btn btn-primary">
                <i class="fas fa-plus"></i> <?= t('sales_orders.new_order') ?>
            </a>
            <?php endif; ?>
        </div>
    </div>

    <!-- Status Summary Cards -->
    <div class="row mb-4">
        <div class="col-md-2">
            <div class="card bg-secondary text-white">
                <div class="card-body text-center">
                    <div class="h4"><?= $summary['pending'] ?? 0 ?></div>
                    <div class="small"><?= t('sales_orders.status.pending') ?></div>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card bg-warning text-white">
                <div class="card-body text-center">
                    <div class="h4"><?= $summary['processing'] ?? 0 ?></div>
                    <div class="small"><?= t('sales_orders.status.processing') ?></div>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card bg-info text-white">
                <div class="card-body text-center">
                    <div class="h4"><?= $summary['shipped'] ?? 0 ?></div>
                    <div class="small"><?= t('sales_orders.status.shipped') ?></div>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card bg-success text-white">
                <div class="card-body text-center">
                    <div class="h4"><?= $summary['delivered'] ?? 0 ?></div>
                    <div class="small"><?= t('sales_orders.status.delivered') ?></div>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card bg-danger text-white">
                <div class="card-body text-center">
                    <div class="h4"><?= $summary['cancelled'] ?? 0 ?></div>
                    <div class="small"><?= t('sales_orders.status.cancelled') ?></div>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card bg-primary text-white">
                <div class="card-body text-center">
                    <div class="h4"><?= number_format($summary['total_value'] ?? 0, 0) ?></div>
                    <div class="small"><?= t('sales_orders.total_value') ?></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="/sales-orders" class="row g-3">
                <div class="col-md-3">
                    <label for="search" class="form-label"><?= t('common.search') ?></label>
                    <input type="text" class="form-control" id="search" name="search" 
                           value="<?= htmlspecialchars($search ?? '') ?>" 
                           placeholder="<?= t('sales_orders.search_placeholder') ?>">
                </div>
                
                <div class="col-md-2">
                    <label for="status" class="form-label"><?= t('common.status') ?></label>
                    <select class="form-select" id="status" name="status">
                        <option value=""><?= t('common.all_statuses') ?></option>
                        <option value="pending" <?= ($status ?? '') === 'pending' ? 'selected' : '' ?>><?= t('sales_orders.status.pending') ?></option>
                        <option value="processing" <?= ($status ?? '') === 'processing' ? 'selected' : '' ?>><?= t('sales_orders.status.processing') ?></option>
                        <option value="shipped" <?= ($status ?? '') === 'shipped' ? 'selected' : '' ?>><?= t('sales_orders.status.shipped') ?></option>
                        <option value="delivered" <?= ($status ?? '') === 'delivered' ? 'selected' : '' ?>><?= t('sales_orders.status.delivered') ?></option>
                        <option value="cancelled" <?= ($status ?? '') === 'cancelled' ? 'selected' : '' ?>><?= t('sales_orders.status.cancelled') ?></option>
                    </select>
                </div>
                
                <div class="col-md-2">
                    <label for="client_id" class="form-label"><?= t('clients.client') ?></label>
                    <select class="form-select" id="client_id" name="client_id">
                        <option value=""><?= t('common.all_clients') ?></option>
                        <?php foreach ($clients ?? [] as $client): ?>
                        <option value="<?= $client->id ?>" <?= ($client_id ?? '') == $client->id ? 'selected' : '' ?>>
                            <?= htmlspecialchars($client->name) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="col-md-2">
                    <label for="date_from" class="form-label"><?= t('common.date_from') ?></label>
                    <input type="date" class="form-control" id="date_from" name="date_from" 
                           value="<?= htmlspecialchars($date_from ?? '') ?>">
                </div>
                
                <div class="col-md-2">
                    <label for="date_to" class="form-label"><?= t('common.date_to') ?></label>
                    <input type="date" class="form-control" id="date_to" name="date_to" 
                           value="<?= htmlspecialchars($date_to ?? '') ?>">
                </div>
                
                <div class="col-md-1 d-flex align-items-end">
                    <button type="submit" class="btn btn-outline-primary me-2">
                        <i class="fas fa-filter"></i>
                    </button>
                    <a href="/sales-orders" class="btn btn-outline-secondary">
                        <i class="fas fa-times"></i>
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Sales Orders Table -->
    <div class="card">
        <div class="card-body">
            <?php if (empty($sales_orders)): ?>
                <div class="text-center py-5">
                    <i class="fas fa-shopping-cart fa-4x text-muted mb-4"></i>
                    <h4><?= t('sales_orders.no_orders_found') ?></h4>
                    <p class="text-muted"><?= t('sales_orders.no_orders_desc') ?></p>
                    <?php if ($canCreate): ?>
                    <a href="/sales-orders/create" class="btn btn-primary">
                        <i class="fas fa-plus"></i> <?= t('sales_orders.create_first_order') ?>
                    </a>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th><?= t('sales_orders.order_number') ?></th>
                                <th><?= t('clients.client') ?></th>
                                <th><?= t('sales_orders.order_date') ?></th>
                                <th><?= t('sales_orders.delivery_date') ?></th>
                                <th><?= t('sales_orders.total_amount') ?></th>
                                <th><?= t('common.status') ?></th>
                                <th><?= t('sales_orders.fulfillment') ?></th>
                                <th class="text-end"><?= t('common.actions') ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($sales_orders as $order): ?>
                            <tr>
                                <td>
                                    <strong><?= htmlspecialchars($order->order_number) ?></strong>
                                    <?php if ($order->reference): ?>
                                    <br><small class="text-muted"><?= htmlspecialchars($order->reference) ?></small>
                                    <?php endif; ?>
                                    <?php if ($order->quote_number): ?>
                                    <br><small class="text-info">
                                        <i class="fas fa-link me-1"></i><?= t('sales_orders.from_quote') ?>: <?= htmlspecialchars($order->quote_number) ?>
                                    </small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="user-avatar bg-primary text-white rounded-circle me-2 d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                                            <?= strtoupper(substr($order->client_name ?? 'C', 0, 1)) ?>
                                        </div>
                                        <div>
                                            <div class="fw-bold"><?= htmlspecialchars($order->client_name ?? 'Unknown') ?></div>
                                            <?php if ($order->client_email): ?>
                                            <small class="text-muted"><?= htmlspecialchars($order->client_email) ?></small>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </td>
                                <td><?= date('M d, Y', strtotime($order->order_date)) ?></td>
                                <td>
                                    <?php if ($order->delivery_date): ?>
                                    <?php 
                                    $deliveryDate = strtotime($order->delivery_date);
                                    $isOverdue = $deliveryDate < time() && !in_array($order->status, ['delivered', 'cancelled']);
                                    ?>
                                    <span class="<?= $isOverdue ? 'text-danger' : '' ?>">
                                        <?= date('M d, Y', $deliveryDate) ?>
                                    </span>
                                    <?php if ($isOverdue): ?>
                                    <br><small class="badge bg-danger"><?= t('sales_orders.overdue') ?></small>
                                    <?php endif; ?>
                                    <?php else: ?>
                                    <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <strong><?= number_format($order->total_amount, 2) ?> <?= $order->currency ?? 'USD' ?></strong>
                                </td>
                                <td>
                                    <?php 
                                    $statusClass = match($order->status) {
                                        'pending' => 'bg-secondary',
                                        'processing' => 'bg-warning',
                                        'shipped' => 'bg-info',
                                        'delivered' => 'bg-success',
                                        'cancelled' => 'bg-danger',
                                        default => 'bg-secondary'
                                    };
                                    ?>
                                    <span class="badge <?= $statusClass ?>">
                                        <?= t('sales_orders.status.' . $order->status) ?>
                                    </span>
                                </td>
                                <td>
                                    <?php $fulfillmentPercent = $order->fulfillment_percentage ?? 0; ?>
                                    <div class="progress" style="height: 20px;">
                                        <div class="progress-bar <?= $fulfillmentPercent == 100 ? 'bg-success' : ($fulfillmentPercent > 0 ? 'bg-info' : 'bg-secondary') ?>" 
                                             role="progressbar" style="width: <?= $fulfillmentPercent ?>%">
                                            <?= $fulfillmentPercent ?>%
                                        </div>
                                    </div>
                                </td>
                                <td class="text-end">
                                    <div class="btn-group">
                                        <a href="/sales-orders/<?= $order->id ?>" class="btn btn-sm btn-outline-primary" title="<?= t('common.view') ?>">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        
                                        <?php if ($canEdit && !in_array($order->status, ['delivered', 'cancelled'])): ?>
                                        <a href="/sales-orders/<?= $order->id ?>/edit" class="btn btn-sm btn-outline-secondary" title="<?= t('common.edit') ?>">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <?php endif; ?>
                                        
                                        <div class="btn-group">
                                            <button type="button" class="btn btn-sm btn-outline-secondary dropdown-toggle dropdown-toggle-split" data-bs-toggle="dropdown">
                                                <span class="visually-hidden"><?= t('common.actions') ?></span>
                                            </button>
                                            <ul class="dropdown-menu">
                                                <li><a class="dropdown-item" href="/sales-orders/<?= $order->id ?>/pdf" target="_blank">
                                                    <i class="fas fa-file-pdf me-2"></i><?= t('common.download_pdf') ?>
                                                </a></li>
                                                
                                                <?php if ($order->status === 'pending'): ?>
                                                <li><a class="dropdown-item" href="#" onclick="updateOrderStatus(<?= $order->id ?>, 'processing')">
                                                    <i class="fas fa-play me-2"></i><?= t('sales_orders.start_processing') ?>
                                                </a></li>
                                                <?php elseif ($order->status === 'processing'): ?>
                                                <li><a class="dropdown-item" href="#" onclick="updateOrderStatus(<?= $order->id ?>, 'shipped')">
                                                    <i class="fas fa-shipping-fast me-2"></i><?= t('sales_orders.mark_shipped') ?>
                                                </a></li>
                                                <?php elseif ($order->status === 'shipped'): ?>
                                                <li><a class="dropdown-item" href="#" onclick="updateOrderStatus(<?= $order->id ?>, 'delivered')">
                                                    <i class="fas fa-check me-2"></i><?= t('sales_orders.mark_delivered') ?>
                                                </a></li>
                                                <?php endif; ?>
                                                
                                                <?php if ($order->status === 'delivered'): ?>
                                                <li><a class="dropdown-item" href="/invoices/create?order_id=<?= $order->id ?>">
                                                    <i class="fas fa-file-invoice me-2"></i><?= t('sales_orders.create_invoice') ?>
                                                </a></li>
                                                <?php endif; ?>
                                                
                                                <?php if ($canEdit): ?>
                                                <li><a class="dropdown-item" href="/sales-orders/<?= $order->id ?>/duplicate">
                                                    <i class="fas fa-copy me-2"></i><?= t('common.duplicate') ?>
                                                </a></li>
                                                <?php endif; ?>
                                                
                                                <?php if (!in_array($order->status, ['delivered', 'cancelled'])): ?>
                                                <li><hr class="dropdown-divider"></li>
                                                <li><a class="dropdown-item text-danger" href="#" onclick="updateOrderStatus(<?= $order->id ?>, 'cancelled')">
                                                    <i class="fas fa-ban me-2"></i><?= t('sales_orders.cancel_order') ?>
                                                </a></li>
                                                <?php endif; ?>
                                                
                                                <?php if ($canDelete && $order->status === 'pending'): ?>
                                                <li><a class="dropdown-item text-danger" href="#" onclick="deleteOrder(<?= $order->id ?>, '<?= htmlspecialchars($order->order_number) ?>')">
                                                    <i class="fas fa-trash me-2"></i><?= t('common.delete') ?>
                                                </a></li>
                                                <?php endif; ?>
                                            </ul>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                
                <!-- Pagination -->
                <?php if (isset($pagination) && $pagination['total_pages'] > 1): ?>
                <nav aria-label="<?= t('common.pagination') ?>" class="mt-4">
                    <ul class="pagination justify-content-center">
                        <!-- Pagination implementation -->
                    </ul>
                </nav>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
function updateOrderStatus(orderId, newStatus) {
    const statusNames = {
        'processing': '<?= t('sales_orders.status.processing') ?>',
        'shipped': '<?= t('sales_orders.status.shipped') ?>',
        'delivered': '<?= t('sales_orders.status.delivered') ?>',
        'cancelled': '<?= t('sales_orders.status.cancelled') ?>'
    };
    
    if (confirm('<?= t('sales_orders.confirm_status_change') ?>'.replace(':status', statusNames[newStatus]))) {
        fetch(`/sales-orders/${orderId}/status`, {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({ status: newStatus })
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

function deleteOrder(orderId, orderNumber) {
    if (confirm('<?= t('sales_orders.confirm_delete') ?>'.replace(':number', orderNumber))) {
        fetch(`/sales-orders/${orderId}`, {
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