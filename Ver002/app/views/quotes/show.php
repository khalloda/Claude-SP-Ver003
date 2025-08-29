<?php
/**
 * File: app/views/quotes/show.php
 * Purpose: Quote detail view with comprehensive information display
 * Layout: Uses app layout with professional quote presentation
 */

$this->layout('layouts/app', [
    'title' => $page_title ?? t('quotes.quote_details'),
    'active_nav' => 'quotes'
]);

$currentUser = $this->getCurrentUser();
$canEdit = $this->hasRole(['admin', 'manager', 'sales']) && in_array($quote->status, ['draft', 'sent']);
$canDelete = $this->hasRole(['admin', 'manager']) && $quote->status === 'draft';
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">
            <i class="fas fa-file-alt me-2"></i><?= t('quotes.quote_details') ?>
            <small class="text-muted ms-2"><?= htmlspecialchars($quote->quote_number) ?></small>
        </h1>
        <div class="btn-toolbar mb-2 mb-md-0">
            <div class="btn-group me-2">
                <a href="/quotes/<?= $quote->id ?>/pdf" class="btn btn-outline-primary" target="_blank">
                    <i class="fas fa-file-pdf"></i> <?= t('common.download_pdf') ?>
                </a>
                <button type="button" class="btn btn-outline-secondary dropdown-toggle dropdown-toggle-split" data-bs-toggle="dropdown">
                    <span class="visually-hidden"><?= t('common.actions') ?></span>
                </button>
                <ul class="dropdown-menu">
                    <li><a class="dropdown-item" href="#" onclick="window.print()">
                        <i class="fas fa-print me-2"></i><?= t('common.print') ?>
                    </a></li>
                    <li><a class="dropdown-item" href="/quotes/<?= $quote->id ?>/email">
                        <i class="fas fa-envelope me-2"></i><?= t('quotes.email_quote') ?>
                    </a></li>
                    <li><a class="dropdown-item" href="/quotes/<?= $quote->id ?>/duplicate">
                        <i class="fas fa-copy me-2"></i><?= t('common.duplicate') ?>
                    </a></li>
                </ul>
            </div>
            
            <?php if ($canEdit): ?>
            <a href="/quotes/<?= $quote->id ?>/edit" class="btn btn-primary me-2">
                <i class="fas fa-edit"></i> <?= t('common.edit') ?>
            </a>
            <?php endif; ?>
            
            <a href="/quotes" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left"></i> <?= t('common.back') ?>
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8">
            <!-- Quote Header -->
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><?= t('quotes.quote_information') ?></h5>
                    <div>
                        <?php 
                        $statusClass = match($quote->status) {
                            'draft' => 'bg-secondary',
                            'sent' => 'bg-info',
                            'accepted' => 'bg-success',
                            'rejected' => 'bg-danger',
                            'expired' => 'bg-warning text-dark',
                            default => 'bg-secondary'
                        };
                        ?>
                        <span class="badge <?= $statusClass ?> me-2">
                            <?= t('quotes.status.' . $quote->status) ?>
                        </span>
                        
                        <?php if ($quote->status === 'accepted'): ?>
                        <a href="/sales-orders/create?quote_id=<?= $quote->id ?>" class="btn btn-sm btn-success">
                            <i class="fas fa-shopping-cart"></i> <?= t('quotes.convert_to_order') ?>
                        </a>
                        <?php elseif ($quote->status === 'draft'): ?>
                        <button class="btn btn-sm btn-primary" onclick="sendQuote(<?= $quote->id ?>)">
                            <i class="fas fa-paper-plane"></i> <?= t('quotes.send_quote') ?>
                        </button>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <td><strong><?= t('quotes.quote_number') ?>:</strong></td>
                                    <td><?= htmlspecialchars($quote->quote_number) ?></td>
                                </tr>
                                <tr>
                                    <td><strong><?= t('quotes.quote_date') ?>:</strong></td>
                                    <td><?= date('M d, Y', strtotime($quote->quote_date)) ?></td>
                                </tr>
                                <tr>
                                    <td><strong><?= t('quotes.valid_until') ?>:</strong></td>
                                    <td>
                                        <?= date('M d, Y', strtotime($quote->valid_until)) ?>
                                        <?php if (strtotime($quote->valid_until) < time() && $quote->status !== 'accepted'): ?>
                                        <span class="badge bg-danger ms-2"><?= t('quotes.expired') ?></span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php if ($quote->reference): ?>
                                <tr>
                                    <td><strong><?= t('quotes.reference') ?>:</strong></td>
                                    <td><?= htmlspecialchars($quote->reference) ?></td>
                                </tr>
                                <?php endif; ?>
                            </table>
                        </div>
                        
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <td><strong><?= t('quotes.created_by') ?>:</strong></td>
                                    <td><?= htmlspecialchars($quote->created_by_name ?? 'System') ?></td>
                                </tr>
                                <tr>
                                    <td><strong><?= t('quotes.created_at') ?>:</strong></td>
                                    <td><?= date('M d, Y H:i', strtotime($quote->created_at)) ?></td>
                                </tr>
                                <tr>
                                    <td><strong><?= t('quotes.last_updated') ?>:</strong></td>
                                    <td><?= date('M d, Y H:i', strtotime($quote->updated_at)) ?></td>
                                </tr>
                                <tr>
                                    <td><strong><?= t('common.currency') ?>:</strong></td>
                                    <td><?= htmlspecialchars($quote->currency) ?></td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quote Items -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><?= t('quotes.quote_items') ?></h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th><?= t('products.product') ?></th>
                                    <th class="text-center"><?= t('quotes.quantity') ?></th>
                                    <th class="text-end"><?= t('quotes.unit_price') ?></th>
                                    <th class="text-end"><?= t('quotes.total') ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($quote->items)): ?>
                                    <?php foreach ($quote->items as $item): ?>
                                    <tr>
                                        <td>
                                            <strong><?= htmlspecialchars($item->product_name) ?></strong>
                                            <?php if ($item->description): ?>
                                            <br><small class="text-muted"><?= htmlspecialchars($item->description) ?></small>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-center">
                                            <?= number_format($item->quantity, 2) ?>
                                            <?php if ($item->unit): ?>
                                            <small class="text-muted"><?= htmlspecialchars($item->unit) ?></small>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-end">
                                            <?= number_format($item->unit_price, 2) ?> <?= $quote->currency ?>
                                        </td>
                                        <td class="text-end">
                                            <strong><?= number_format($item->total_price, 2) ?> <?= $quote->currency ?></strong>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="4" class="text-center text-muted py-4">
                                            <?= t('quotes.no_items') ?>
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
                                    <td><?= t('quotes.subtotal') ?>:</td>
                                    <td class="text-end"><?= number_format($quote->subtotal, 2) ?> <?= $quote->currency ?></td>
                                </tr>
                                <?php if ($quote->discount_rate > 0): ?>
                                <tr>
                                    <td><?= t('quotes.discount') ?> (<?= $quote->discount_rate ?>%):</td>
                                    <td class="text-end">-<?= number_format($quote->discount_amount, 2) ?> <?= $quote->currency ?></td>
                                </tr>
                                <?php endif; ?>
                                <?php if ($quote->tax_rate > 0): ?>
                                <tr>
                                    <td><?= t('quotes.tax') ?> (<?= $quote->tax_rate ?>%):</td>
                                    <td class="text-end"><?= number_format($quote->tax_amount, 2) ?> <?= $quote->currency ?></td>
                                </tr>
                                <?php endif; ?>
                                <tr class="table-primary">
                                    <td><strong><?= t('quotes.total') ?>:</strong></td>
                                    <td class="text-end"><strong><?= number_format($quote->total_amount, 2) ?> <?= $quote->currency ?></strong></td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Notes and Terms -->
            <?php if ($quote->notes || $quote->terms_conditions): ?>
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><?= t('quotes.notes_terms') ?></h5>
                </div>
                <div class="card-body">
                    <?php if ($quote->notes): ?>
                    <div class="mb-3">
                        <h6><?= t('quotes.internal_notes') ?></h6>
                        <p class="text-muted"><?= nl2br(htmlspecialchars($quote->notes)) ?></p>
                    </div>
                    <?php endif; ?>
                    
                    <?php if ($quote->terms_conditions): ?>
                    <div class="mb-3">
                        <h6><?= t('quotes.terms_conditions') ?></h6>
                        <p><?= nl2br(htmlspecialchars($quote->terms_conditions)) ?></p>
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
                            <?= strtoupper(substr($quote->client_name, 0, 1)) ?>
                        </div>
                        <div>
                            <h6 class="mb-0">
                                <a href="/clients/<?= $quote->client_id ?>" class="text-decoration-none">
                                    <?= htmlspecialchars($quote->client_name) ?>
                                </a>
                            </h6>
                            <small class="text-muted"><?= t('clients.type.' . ($quote->client_type ?? 'individual')) ?></small>
                        </div>
                    </div>
                    
                    <?php if ($quote->client_email): ?>
                    <div class="mb-2">
                        <i class="fas fa-envelope text-muted me-2"></i>
                        <a href="mailto:<?= htmlspecialchars($quote->client_email) ?>"><?= htmlspecialchars($quote->client_email) ?></a>
                    </div>
                    <?php endif; ?>
                    
                    <?php if ($quote->client_phone): ?>
                    <div class="mb-2">
                        <i class="fas fa-phone text-muted me-2"></i>
                        <a href="tel:<?= htmlspecialchars($quote->client_phone) ?>"><?= htmlspecialchars($quote->client_phone) ?></a>
                    </div>
                    <?php endif; ?>
                    
                    <?php if ($quote->client_address): ?>
                    <div class="mb-2">
                        <i class="fas fa-map-marker-alt text-muted me-2"></i>
                        <?= nl2br(htmlspecialchars($quote->client_address)) ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Related Records -->
            <?php if (!empty($quote->sales_orders)): ?>
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><?= t('quotes.related_orders') ?></h5>
                </div>
                <div class="card-body">
                    <?php foreach ($quote->sales_orders as $order): ?>
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <div>
                            <a href="/sales-orders/<?= $order->id ?>" class="fw-bold text-decoration-none">
                                <?= htmlspecialchars($order->order_number) ?>
                            </a>
                            <br><small class="text-muted"><?= date('M d, Y', strtotime($order->created_at)) ?></small>
                        </div>
                        <span class="badge bg-<?= $order->status === 'completed' ? 'success' : 'info' ?>">
                            <?= t('sales_orders.status.' . $order->status) ?>
                        </span>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- Quote Activity Log -->
            <?php if (!empty($quote->activity_log)): ?>
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><?= t('quotes.activity_log') ?></h5>
                </div>
                <div class="card-body">
                    <?php foreach ($quote->activity_log as $activity): ?>
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
function sendQuote(quoteId) {
    if (confirm('<?= t('quotes.confirm_send') ?>')) {
        fetch(`/quotes/${quoteId}/send`, {
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