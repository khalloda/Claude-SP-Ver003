<?php
/**
 * File: app/views/salesorders/show.php
 * Purpose: Sales order detail view with comprehensive order tracking
 * Layout: Uses app layout with professional order presentation
 */

$this->layout('layouts/app', [
    'title' => $page_title ?? t('sales_orders.order_details'),
    'active_nav' => 'sales-orders'
]);

$currentUser = $this->getCurrentUser();
$canEdit = $this->hasRole(['admin', 'manager', 'sales']) && in_array($order->status, ['pending', 'confirmed']);
$canFulfill = $this->hasRole(['admin', 'manager', 'warehouse']) && $order->status === 'confirmed';
$canCancel = $this->hasRole(['admin', 'manager']) && !in_array($order->status, ['completed', 'cancelled']);
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">
            <i class="fas fa-shopping-cart me-2"></i><?= t('sales_orders.order_details') ?>
            <small class="text-muted ms-2"><?= htmlspecialchars($order->order_number) ?></small>
        </h1>
        <div class="btn-toolbar mb-2 mb-md-0">
            <div class="btn-group me-2">
                <a href="/sales-orders/<?= $order->id ?>/pdf" class="btn btn-outline-primary" target="_blank">
                    <i class="fas fa-file-pdf"></i> <?= t('common.download_pdf') ?>
                </a>
                <button type="button" class="btn btn-outline-secondary dropdown-toggle dropdown-toggle-split" data-bs-toggle="dropdown">
                    <span class="visually-hidden"><?= t('common.actions') ?></span>
                </button>
                <ul class="dropdown-menu">
                    <li><a class="dropdown-item" href="#" onclick="window.print()">
                        <i class="fas fa-print me-2"></i><?= t('common.print') ?>
                    </a></li>
                    <li><a class="dropdown-item" href="/sales-orders/<?= $order->id ?>/email">
                        <i class="fas fa-envelope me-2"></i><?= t('sales_orders.email_order') ?>
                    </a></li>
                    <li><a class="dropdown-item" href="/sales-orders/<?= $order->id ?>/duplicate">
                        <i class="fas fa-copy me-2"></i><?= t('common.duplicate') ?>
                    </a></li>
                    <?php if ($order->status === 'completed'): ?>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item" href="/invoices/create?order_id=<?= $order->id ?>">
                        <i class="fas fa-file-invoice me-2"></i><?= t('sales_orders.create_invoice') ?>
                    </a></li>
                    <?php endif; ?>
                </ul>
            </div>
            
            <?php if ($canEdit): ?>
            <a href="/sales-orders/<?= $order->id ?>/edit" class="btn btn-primary me-2">
                <i class="fas fa-edit"></i> <?= t('common.edit') ?>
            </a>
            <?php endif; ?>
            
            <?php if ($canFulfill): ?>
            <button class="btn btn-success me-2" onclick="fulfillOrder(<?= $order->id ?>)">
                <i class="fas fa-check-circle"></i> <?= t('sales_orders.fulfill_order') ?>
            </button>
            <?php endif; ?>
            
            <?php if ($canCancel): ?>
            <button class="btn btn-outline-danger me-2" onclick="cancelOrder(<?= $order->id ?>)">
                <i class="fas fa-times-circle"></i> <?= t('common.cancel') ?>
            </button>
            <?php endif; ?>
            
            <a href="/sales-orders" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left"></i> <?= t('common.back') ?>
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8">
            <!-- Order Header -->
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><?= t('sales_orders.order_information') ?></h5>
                    <div>
                        <?php 
                        $statusClass = match($order->status) {
                            'pending' => 'bg-warning text-dark',
                            'confirmed' => 'bg-info',
                            'processing' => 'bg-primary',
                            'shipped' => 'bg-secondary',
                            'completed' => 'bg-success',
                            'cancelled' => 'bg-danger',
                            default => 'bg-secondary'
                        };
                        ?>
                        <span class="badge <?= $statusClass ?> me-2">
                            <?= t('sales_orders.status.' . $order->status) ?>
                        </span>
                        
                        <?php if ($order->priority === 'high'): ?>
                        <span class="badge bg-danger">
                            <i class="fas fa-exclamation-circle"></i> <?= t('sales_orders.high_priority') ?>
                        </span>
                        <?php elseif ($order->priority === 'urgent'): ?>
                        <span class="badge bg-dark">
                            <i class="fas fa-bolt"></i> <?= t('sales_orders.urgent') ?>
                        </span>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <td><strong><?= t('sales_orders.order_number') ?>:</strong></td>
                                    <td><?= htmlspecialchars($order->order_number) ?></td>
                                </tr>
                                <tr>
                                    <td><strong><?= t('sales_orders.order_date') ?>:</strong></td>
                                    <td><?= date('M d, Y', strtotime($order->order_date)) ?></td>
                                </tr>
                                <tr>
                                    <td><strong><?= t('sales_orders.expected_delivery') ?>:</strong></td>
                                    <td>
                                        <?= date('M d, Y', strtotime($order->expected_delivery_date)) ?>
                                        <?php if (strtotime($order->expected_delivery_date) < time() && !in_array($order->status, ['completed', 'cancelled'])): ?>
                                        <span class="badge bg-warning text-dark ms-2"><?= t('sales_orders.overdue') ?></span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php if ($order->reference): ?>
                                <tr>
                                    <td><strong><?= t('sales_orders.reference') ?>:</strong></td>
                                    <td><?= htmlspecialchars($order->reference) ?></td>
                                </tr>
                                <?php endif; ?>
                                <?php if ($order->quote_id): ?>
                                <tr>
                                    <td><strong><?= t('sales_orders.source_quote') ?>:</strong></td>
                                    <td>
                                        <a href="/quotes/<?= $order->quote_id ?>" class="text-decoration-none">
                                            <?= htmlspecialchars($order->quote_number ?? 'Quote #' . $order->quote_id) ?>
                                        </a>
                                    </td>
                                </tr>
                                <?php endif; ?>
                            </table>
                        </div>
                        
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <td><strong><?= t('sales_orders.created_by') ?>:</strong></td>
                                    <td><?= htmlspecialchars($order->created_by_name ?? 'System') ?></td>
                                </tr>
                                <tr>
                                    <td><strong><?= t('sales_orders.assigned_to') ?>:</strong></td>
                                    <td><?= htmlspecialchars($order->assigned_to_name ?? t('common.unassigned')) ?></td>
                                </tr>
                                <tr>
                                    <td><strong><?= t('sales_orders.created_at') ?>:</strong></td>
                                    <td><?= date('M d, Y H:i', strtotime($order->created_at)) ?></td>
                                </tr>
                                <tr>
                                    <td><strong><?= t('sales_orders.last_updated') ?>:</strong></td>
                                    <td><?= date('M d, Y H:i', strtotime($order->updated_at)) ?></td>
                                </tr>
                                <tr>
                                    <td><strong><?= t('common.currency') ?>:</strong></td>
                                    <td><?= htmlspecialchars($order->currency) ?></td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Order Items -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><?= t('sales_orders.order_items') ?></h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th><?= t('products.product') ?></th>
                                    <th class="text-center"><?= t('sales_orders.ordered_qty') ?></th>
                                    <th class="text-center"><?= t('sales_orders.fulfilled_qty') ?></th>
                                    <th class="text-end"><?= t('sales_orders.unit_price') ?></th>
                                    <th class="text-end"><?= t('sales_orders.total') ?></th>
                                    <th class="text-center"><?= t('common.status') ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($order->items)): ?>
                                    <?php foreach ($order->items as $item): ?>
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <?php if ($item->product_image): ?>
                                                <img src="<?= htmlspecialchars($item->product_image) ?>" alt="" class="rounded me-2" style="width: 40px; height: 40px; object-fit: cover;">
                                                <?php else: ?>
                                                <div class="bg-light rounded me-2 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                                    <i class="fas fa-box text-muted"></i>
                                                </div>
                                                <?php endif; ?>
                                                <div>
                                                    <strong><?= htmlspecialchars($item->product_name) ?></strong>
                                                    <br><small class="text-muted"><?= htmlspecialchars($item->product_sku) ?></small>
                                                    <?php if ($item->description): ?>
                                                    <br><small class="text-muted"><?= htmlspecialchars($item->description) ?></small>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="text-center">
                                            <?= number_format($item->quantity, 2) ?>
                                            <?php if ($item->unit): ?>
                                            <br><small class="text-muted"><?= htmlspecialchars($item->unit) ?></small>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-center">
                                            <?= number_format($item->fulfilled_quantity ?? 0, 2) ?>
                                            <?php if ($item->quantity > ($item->fulfilled_quantity ?? 0)): ?>
                                            <br><small class="text-warning"><?= number_format($item->quantity - ($item->fulfilled_quantity ?? 0), 2) ?> <?= t('sales_orders.remaining') ?></small>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-end">
                                            <?= number_format($item->unit_price, 2) ?> <?= $order->currency ?>
                                        </td>
                                        <td class="text-end">
                                            <strong><?= number_format($item->total_price, 2) ?> <?= $order->currency ?></strong>
                                        </td>
                                        <td class="text-center">
                                            <?php 
                                            $itemStatus = 'pending';
                                            if (($item->fulfilled_quantity ?? 0) >= $item->quantity) {
                                                $itemStatus = 'fulfilled';
                                            } elseif (($item->fulfilled_quantity ?? 0) > 0) {
                                                $itemStatus = 'partial';
                                            }
                                            
                                            $itemStatusClass = match($itemStatus) {
                                                'fulfilled' => 'bg-success',
                                                'partial' => 'bg-warning text-dark',
                                                'pending' => 'bg-secondary',
                                                default => 'bg-secondary'
                                            };
                                            ?>
                                            <span class="badge <?= $itemStatusClass ?>">
                                                <?= t('sales_orders.item_status.' . $itemStatus) ?>
                                            </span>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="6" class="text-center text-muted py-4">
                                            <?= t('sales_orders.no_items') ?>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Totals -->
                    <div class="row">
                        <div class="col-md-8"></div>
                        <div class="col-md-4">
                            <table class="table table-sm">
                                <tr>
                                    <td><?= t('sales_orders.subtotal') ?>:</td>
                                    <td class="text-end"><?= number_format($order->subtotal, 2) ?> <?= $order->currency ?></td>
                                </tr>
                                <?php if ($order->discount_rate > 0): ?>
                                <tr>
                                    <td><?= t('sales_orders.discount') ?> (<?= $order->discount_rate ?>%):</td>
                                    <td class="text-end">-<?= number_format($order->discount_amount, 2) ?> <?= $order->currency ?></td>
                                </tr>
                                <?php endif; ?>
                                <?php if ($order->tax_rate > 0): ?>
                                <tr>
                                    <td><?= t('sales_orders.tax') ?> (<?= $order->tax_rate ?>%):</td>
                                    <td class="text-end"><?= number_format($order->tax_amount, 2) ?> <?= $order->currency ?></td>
                                </tr>
                                <?php endif; ?>
                                <?php if ($order->shipping_cost > 0): ?>
                                <tr>
                                    <td><?= t('sales_orders.shipping') ?>:</td>
                                    <td class="text-end"><?= number_format($order->shipping_cost, 2) ?> <?= $order->currency ?></td>
                                </tr>
                                <?php endif; ?>
                                <tr class="table-primary">
                                    <td><strong><?= t('sales_orders.total') ?>:</strong></td>
                                    <td class="text-end"><strong><?= number_format($order->total_amount, 2) ?> <?= $order->currency ?></strong></td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Shipping and Notes -->
            <?php if ($order->shipping_address || $order->notes || $order->special_instructions): ?>
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><?= t('sales_orders.shipping_notes') ?></h5>
                </div>
                <div class="card-body">
                    <?php if ($order->shipping_address): ?>
                    <div class="mb-3">
                        <h6><?= t('sales_orders.shipping_address') ?></h6>
                        <p><?= nl2br(htmlspecialchars($order->shipping_address)) ?></p>
                    </div>
                    <?php endif; ?>
                    
                    <?php if ($order->special_instructions): ?>
                    <div class="mb-3">
                        <h6><?= t('sales_orders.special_instructions') ?></h6>
                        <p><?= nl2br(htmlspecialchars($order->special_instructions)) ?></p>
                    </div>
                    <?php endif; ?>
                    
                    <?php if ($order->notes): ?>
                    <div class="mb-3">
                        <h6><?= t('sales_orders.internal_notes') ?></h6>
                        <p class="text-muted"><?= nl2br(htmlspecialchars($order->notes)) ?></p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>
        
        <div class="col-md-4">
            <!-- Client Information -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><?= t('clients.client_information') ?></h5>
                </div>
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        <div class="user-avatar bg-primary text-white rounded-circle me-3 d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                            <?= strtoupper(substr($order->client_name, 0, 1)) ?>
                        </div>
                        <div>
                            <h6 class="mb-0">
                                <a href="/clients/<?= $order->client_id ?>" class="text-decoration-none">
                                    <?= htmlspecialchars($order->client_name) ?>
                                </a>
                            </h6>
                            <small class="text-muted"><?= t('clients.type.' . ($order->client_type ?? 'individual')) ?></small>
                        </div>
                    </div>
                    
                    <?php if ($order->client_email): ?>
                    <div class="mb-2">
                        <i class="fas fa-envelope text-muted me-2"></i>
                        <a href="mailto:<?= htmlspecialchars($order->client_email) ?>"><?= htmlspecialchars($order->client_email) ?></a>
                    </div>
                    <?php endif; ?>
                    
                    <?php if ($order->client_phone): ?>
                    <div class="mb-2">
                        <i class="fas fa-phone text-muted me-2"></i>
                        <a href="tel:<?= htmlspecialchars($order->client_phone) ?>"><?= htmlspecialchars($order->client_phone) ?></a>
                    </div>
                    <?php endif; ?>
                    
                    <?php if ($order->client_address): ?>
                    <div class="mb-2">
                        <i class="fas fa-map-marker-alt text-muted me-2"></i>
                        <?= nl2br(htmlspecialchars($order->client_address)) ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Order Progress -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><?= t('sales_orders.order_progress') ?></h5>
                </div>
                <div class="card-body">
                    <?php
                    $totalItems = count($order->items ?? []);
                    $fulfilledItems = 0;
                    $totalQuantity = 0;
                    $fulfilledQuantity = 0;
                    
                    foreach ($order->items ?? [] as $item) {
                        $totalQuantity += $item->quantity;
                        $fulfilledQuantity += ($item->fulfilled_quantity ?? 0);
                        if (($item->fulfilled_quantity ?? 0) >= $item->quantity) {
                            $fulfilledItems++;
                        }
                    }
                    
                    $itemProgress = $totalItems > 0 ? ($fulfilledItems / $totalItems) * 100 : 0;
                    $quantityProgress = $totalQuantity > 0 ? ($fulfilledQuantity / $totalQuantity) * 100 : 0;
                    ?>
                    
                    <div class="mb-3">
                        <div class="d-flex justify-content-between mb-1">
                            <small><?= t('sales_orders.items_fulfilled') ?></small>
                            <small><?= $fulfilledItems ?>/<?= $totalItems ?></small>
                        </div>
                        <div class="progress">
                            <div class="progress-bar" role="progressbar" style="width: <?= $itemProgress ?>%"></div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <div class="d-flex justify-content-between mb-1">
                            <small><?= t('sales_orders.quantity_fulfilled') ?></small>
                            <small><?= number_format($fulfilledQuantity, 0) ?>/<?= number_format($totalQuantity, 0) ?></small>
                        </div>
                        <div class="progress">
                            <div class="progress-bar bg-success" role="progressbar" style="width: <?= $quantityProgress ?>%"></div>
                        </div>
                    </div>
                    
                    <div class="row text-center">
                        <div class="col-4">
                            <div class="border rounded p-2">
                                <div class="h6 mb-0"><?= number_format($order->total_amount, 0) ?></div>
                                <small class="text-muted"><?= t('sales_orders.total_value') ?></small>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="border rounded p-2">
                                <div class="h6 mb-0"><?= $totalItems ?></div>
                                <small class="text-muted"><?= t('sales_orders.total_items') ?></small>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="border rounded p-2">
                                <div class="h6 mb-0"><?= abs((strtotime($order->expected_delivery_date) - time()) / 86400) ?></div>
                                <small class="text-muted"><?= strtotime($order->expected_delivery_date) > time() ? t('common.days_left') : t('common.days_overdue') ?></small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Related Records -->
            <?php if (!empty($order->invoices)): ?>
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><?= t('sales_orders.related_invoices') ?></h5>
                </div>
                <div class="card-body">
                    <?php foreach ($order->invoices as $invoice): ?>
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <div>
                            <a href="/invoices/<?= $invoice->id ?>" class="fw-bold text-decoration-none">
                                <?= htmlspecialchars($invoice->invoice_number) ?>
                            </a>
                            <br><small class="text-muted"><?= date('M d, Y', strtotime($invoice->created_at)) ?></small>
                        </div>
                        <span class="badge bg-<?= $invoice->status === 'paid' ? 'success' : 'warning' ?>">
                            <?= t('invoices.status.' . $invoice->status) ?>
                        </span>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- Activity Log -->
            <?php if (!empty($order->activity_log)): ?>
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><?= t('sales_orders.activity_log') ?></h5>
                </div>
                <div class="card-body">
                    <?php foreach ($order->activity_log as $activity): ?>
                    <div class="d-flex mb-3">
                        <div class="flex-shrink-0 me-3">
                            <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                                <i class="fas fa-<?= $activity->icon ?? 'circle' ?> text-white" style="font-size: 0.8rem;"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1">
                            <div class="fw-bold"><?= htmlspecialchars($activity->title) ?></div>
                            <div class="small text-muted"><?= htmlspecialchars($activity->description) ?></div>
                            <div class="small text-muted"><?= date('M d, Y H:i', strtotime($activity->created_at)) ?></div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
function fulfillOrder(orderId) {
    if (confirm('<?= t('sales_orders.confirm_fulfill') ?>')) {
        fetch(`/sales-orders/${orderId}/fulfill`, {
            method: 'POST',
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

function cancelOrder(orderId) {
    if (confirm('<?= t('sales_orders.confirm_cancel') ?>')) {
        fetch(`/sales-orders/${orderId}/cancel`, {
            method: 'POST',
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