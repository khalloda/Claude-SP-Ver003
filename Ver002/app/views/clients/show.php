<?php
/**
 * File: app/views/clients/show.php
 * Purpose: Client profile view with relationship history
 * Layout: Uses app layout with comprehensive client information
 */

$this->layout('layouts/app', [
    'title' => $page_title ?? t('clients.client_profile'),
    'active_nav' => 'clients'
]);

$currentUser = $this->getCurrentUser();
$canEdit = $this->hasRole(['admin', 'manager', 'sales']);
$canDelete = $this->hasRole(['admin', 'manager']);
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">
            <i class="fas fa-user me-2"></i><?= t('clients.client_profile') ?>
            <small class="text-muted ms-2"><?= htmlspecialchars($client->name) ?></small>
        </h1>
        <div class="btn-toolbar mb-2 mb-md-0">
            <div class="btn-group me-2">
                <button type="button" class="btn btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown">
                    <i class="fas fa-plus"></i> <?= t('common.create') ?>
                </button>
                <ul class="dropdown-menu">
                    <li><a class="dropdown-item" href="/quotes/create?client_id=<?= $client->id ?>">
                        <i class="fas fa-file-alt me-2"></i><?= t('quotes.new_quote') ?>
                    </a></li>
                    <li><a class="dropdown-item" href="/sales-orders/create?client_id=<?= $client->id ?>">
                        <i class="fas fa-shopping-cart me-2"></i><?= t('sales_orders.new_order') ?>
                    </a></li>
                    <li><a class="dropdown-item" href="/invoices/create?client_id=<?= $client->id ?>">
                        <i class="fas fa-file-invoice me-2"></i><?= t('invoices.new_invoice') ?>
                    </a></li>
                </ul>
            </div>
            
            <?php if ($canEdit): ?>
            <a href="/clients/<?= $client->id ?>/edit" class="btn btn-primary me-2">
                <i class="fas fa-edit"></i> <?= t('common.edit') ?>
            </a>
            <?php endif; ?>
            
            <a href="/clients" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left"></i> <?= t('common.back') ?>
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-md-4">
            <!-- Client Information Card -->
            <div class="card mb-4">
                <div class="card-body text-center">
                    <div class="user-avatar bg-primary text-white rounded-circle mx-auto mb-3 d-flex align-items-center justify-content-center" style="width: 80px; height: 80px; font-size: 2rem;">
                        <?php if ($client->type === 'company'): ?>
                            <i class="fas fa-building"></i>
                        <?php else: ?>
                            <?= strtoupper(substr($client->name ?? '', 0, 1)) ?>
                        <?php endif; ?>
                    </div>
                    
                    <h4><?= htmlspecialchars($client->name) ?></h4>
                    <p class="text-muted mb-3"><?= t('clients.type.' . $client->type) ?></p>
                    
                    <?php if ($client->is_active): ?>
                        <span class="badge bg-success"><?= t('common.active') ?></span>
                    <?php else: ?>
                        <span class="badge bg-secondary"><?= t('common.inactive') ?></span>
                    <?php endif; ?>
                    
                    <?php if ($client->is_preferred): ?>
                        <span class="badge bg-warning text-dark ms-2"><?= t('clients.preferred') ?></span>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Contact Information -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><?= t('clients.contact_information') ?></h5>
                </div>
                <div class="card-body">
                    <?php if ($client->email): ?>
                    <div class="d-flex align-items-center mb-3">
                        <i class="fas fa-envelope text-muted me-3" style="width: 20px;"></i>
                        <div>
                            <a href="mailto:<?= htmlspecialchars($client->email) ?>" class="text-decoration-none">
                                <?= htmlspecialchars($client->email) ?>
                            </a>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <?php if ($client->phone): ?>
                    <div class="d-flex align-items-center mb-3">
                        <i class="fas fa-phone text-muted me-3" style="width: 20px;"></i>
                        <div>
                            <a href="tel:<?= htmlspecialchars($client->phone) ?>" class="text-decoration-none">
                                <?= htmlspecialchars($client->phone) ?>
                            </a>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <?php if ($client->mobile): ?>
                    <div class="d-flex align-items-center mb-3">
                        <i class="fas fa-mobile-alt text-muted me-3" style="width: 20px;"></i>
                        <div>
                            <a href="tel:<?= htmlspecialchars($client->mobile) ?>" class="text-decoration-none">
                                <?= htmlspecialchars($client->mobile) ?>
                            </a>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <?php if ($client->website): ?>
                    <div class="d-flex align-items-center mb-3">
                        <i class="fas fa-globe text-muted me-3" style="width: 20px;"></i>
                        <div>
                            <a href="<?= htmlspecialchars($client->website) ?>" target="_blank" class="text-decoration-none">
                                <?= htmlspecialchars($client->website) ?>
                            </a>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Address Information -->
            <?php if ($client->address || $client->city || $client->country): ?>
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><?= t('clients.address_information') ?></h5>
                </div>
                <div class="card-body">
                    <div class="d-flex align-items-start">
                        <i class="fas fa-map-marker-alt text-muted me-3 mt-1" style="width: 20px;"></i>
                        <div>
                            <?php if ($client->address): ?>
                            <div><?= nl2br(htmlspecialchars($client->address)) ?></div>
                            <?php endif; ?>
                            
                            <?php if ($client->city || $client->state): ?>
                            <div><?= htmlspecialchars(trim($client->city . ', ' . $client->state, ', ')) ?></div>
                            <?php endif; ?>
                            
                            <?php if ($client->postal_code): ?>
                            <div><?= htmlspecialchars($client->postal_code) ?></div>
                            <?php endif; ?>
                            
                            <?php if ($client->country): ?>
                            <div><strong><?= htmlspecialchars($client->country) ?></strong></div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- Client Statistics -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><?= t('clients.statistics') ?></h5>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-6">
                            <div class="fw-bold text-primary h4"><?= $stats['quotes_count'] ?? 0 ?></div>
                            <div class="small text-muted"><?= t('nav.quotes') ?></div>
                        </div>
                        <div class="col-6">
                            <div class="fw-bold text-success h4"><?= $stats['orders_count'] ?? 0 ?></div>
                            <div class="small text-muted"><?= t('nav.sales_orders') ?></div>
                        </div>
                    </div>
                    <hr>
                    <div class="row text-center">
                        <div class="col-6">
                            <div class="fw-bold text-info h4"><?= $stats['invoices_count'] ?? 0 ?></div>
                            <div class="small text-muted"><?= t('nav.invoices') ?></div>
                        </div>
                        <div class="col-6">
                            <div class="fw-bold text-warning h4"><?= number_format($stats['total_value'] ?? 0, 0) ?></div>
                            <div class="small text-muted"><?= t('clients.total_value') ?></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-8">
            <!-- Business Relationship History -->
            <div class="card mb-4">
                <div class="card-header">
                    <ul class="nav nav-tabs card-header-tabs" id="relationshipTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="quotes-tab" data-bs-toggle="tab" data-bs-target="#quotes" type="button" role="tab">
                                <?= t('nav.quotes') ?> (<?= count($client->quotes ?? []) ?>)
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="orders-tab" data-bs-toggle="tab" data-bs-target="#orders" type="button" role="tab">
                                <?= t('nav.sales_orders') ?> (<?= count($client->sales_orders ?? []) ?>)
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="invoices-tab" data-bs-toggle="tab" data-bs-target="#invoices" type="button" role="tab">
                                <?= t('nav.invoices') ?> (<?= count($client->invoices ?? []) ?>)
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="activity-tab" data-bs-toggle="tab" data-bs-target="#activity" type="button" role="tab">
                                <?= t('clients.activity') ?>
                            </button>
                        </li>
                    </ul>
                </div>
                <div class="card-body">
                    <div class="tab-content" id="relationshipTabsContent">
                        <!-- Quotes Tab -->
                        <div class="tab-pane fade show active" id="quotes" role="tabpanel" aria-labelledby="quotes-tab">
                            <?php if (!empty($client->quotes)): ?>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th><?= t('quotes.quote_number') ?></th>
                                            <th><?= t('quotes.quote_date') ?></th>
                                            <th><?= t('quotes.amount') ?></th>
                                            <th><?= t('common.status') ?></th>
                                            <th class="text-end"><?= t('common.actions') ?></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach (array_slice($client->quotes, 0, 10) as $quote): ?>
                                        <tr>
                                            <td>
                                                <a href="/quotes/<?= $quote->id ?>" class="text-decoration-none fw-bold">
                                                    <?= htmlspecialchars($quote->quote_number) ?>
                                                </a>
                                            </td>
                                            <td><?= date('M d, Y', strtotime($quote->quote_date)) ?></td>
                                            <td><?= number_format($quote->total_amount, 2) ?> <?= $quote->currency ?></td>
                                            <td>
                                                <span class="badge bg-<?= $quote->status === 'accepted' ? 'success' : ($quote->status === 'sent' ? 'info' : 'secondary') ?>">
                                                    <?= t('quotes.status.' . $quote->status) ?>
                                                </span>
                                            </td>
                                            <td class="text-end">
                                                <a href="/quotes/<?= $quote->id ?>" class="btn btn-sm btn-outline-primary">
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
                                <i class="fas fa-file-alt fa-3x text-muted mb-3"></i>
                                <p class="text-muted"><?= t('quotes.no_quotes_for_client') ?></p>
                                <a href="/quotes/create?client_id=<?= $client->id ?>" class="btn btn-primary">
                                    <i class="fas fa-plus"></i> <?= t('quotes.create_first_quote') ?>
                                </a>
                            </div>
                            <?php endif; ?>
                        </div>

                        <!-- Sales Orders Tab -->
                        <div class="tab-pane fade" id="orders" role="tabpanel" aria-labelledby="orders-tab">
                            <?php if (!empty($client->sales_orders)): ?>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th><?= t('sales_orders.order_number') ?></th>
                                            <th><?= t('sales_orders.order_date') ?></th>
                                            <th><?= t('sales_orders.amount') ?></th>
                                            <th><?= t('common.status') ?></th>
                                            <th class="text-end"><?= t('common.actions') ?></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach (array_slice($client->sales_orders, 0, 10) as $order): ?>
                                        <tr>
                                            <td>
                                                <a href="/sales-orders/<?= $order->id ?>" class="text-decoration-none fw-bold">
                                                    <?= htmlspecialchars($order->order_number) ?>
                                                </a>
                                            </td>
                                            <td><?= date('M d, Y', strtotime($order->order_date)) ?></td>
                                            <td><?= number_format($order->total_amount, 2) ?> <?= $order->currency ?></td>
                                            <td>
                                                <span class="badge bg-<?= $order->status === 'delivered' ? 'success' : ($order->status === 'shipped' ? 'info' : 'warning') ?>">
                                                    <?= t('sales_orders.status.' . $order->status) ?>
                                                </span>
                                            </td>
                                            <td class="text-end">
                                                <a href="/sales-orders/<?= $order->id ?>" class="btn btn-sm btn-outline-primary">
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
                                <p class="text-muted"><?= t('sales_orders.no_orders_for_client') ?></p>
                                <a href="/sales-orders/create?client_id=<?= $client->id ?>" class="btn btn-primary">
                                    <i class="fas fa-plus"></i> <?= t('sales_orders.create_first_order') ?>
                                </a>
                            </div>
                            <?php endif; ?>
                        </div>

                        <!-- Invoices Tab -->
                        <div class="tab-pane fade" id="invoices" role="tabpanel" aria-labelledby="invoices-tab">
                            <?php if (!empty($client->invoices)): ?>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th><?= t('invoices.invoice_number') ?></th>
                                            <th><?= t('invoices.invoice_date') ?></th>
                                            <th><?= t('invoices.amount') ?></th>
                                            <th><?= t('invoices.paid_amount') ?></th>
                                            <th><?= t('common.status') ?></th>
                                            <th class="text-end"><?= t('common.actions') ?></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach (array_slice($client->invoices, 0, 10) as $invoice): ?>
                                        <tr>
                                            <td>
                                                <a href="/invoices/<?= $invoice->id ?>" class="text-decoration-none fw-bold">
                                                    <?= htmlspecialchars($invoice->invoice_number) ?>
                                                </a>
                                            </td>
                                            <td><?= date('M d, Y', strtotime($invoice->invoice_date)) ?></td>
                                            <td><?= number_format($invoice->total_amount, 2) ?> <?= $invoice->currency ?></td>
                                            <td><?= number_format($invoice->paid_amount ?? 0, 2) ?> <?= $invoice->currency ?></td>
                                            <td>
                                                <span class="badge bg-<?= $invoice->status === 'paid' ? 'success' : ($invoice->status === 'overdue' ? 'danger' : 'warning') ?>">
                                                    <?= t('invoices.status.' . $invoice->status) ?>
                                                </span>
                                            </td>
                                            <td class="text-end">
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
                                <p class="text-muted"><?= t('invoices.no_invoices_for_client') ?></p>
                                <a href="/invoices/create?client_id=<?= $client->id ?>" class="btn btn-primary">
                                    <i class="fas fa-plus"></i> <?= t('invoices.create_first_invoice') ?>
                                </a>
                            </div>
                            <?php endif; ?>
                        </div>

                        <!-- Activity Tab -->
                        <div class="tab-pane fade" id="activity" role="tabpanel" aria-labelledby="activity-tab">
                            <?php if (!empty($client->activity_log)): ?>
                            <?php foreach ($client->activity_log as $activity): ?>
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
                            <?php else: ?>
                            <div class="text-center py-4">
                                <i class="fas fa-history fa-3x text-muted mb-3"></i>
                                <p class="text-muted"><?= t('clients.no_activity') ?></p>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>