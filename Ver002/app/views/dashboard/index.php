<?php
/**
 * File: app/views/dashboard/index.php
 * Purpose: Main dashboard interface with statistics and quick actions
 * Layout: Uses app layout with responsive design
 */

$page_title = t('nav.dashboard');
ob_start();

// Get current user (from session or controller data)
$currentUser = $current_user ?? ($_SESSION['user'] ?? null);

// Define permission variables safely
$canManageUsers = $currentUser && in_array($currentUser['role'] ?? '', ['admin', 'manager']);
$canManageProducts = $currentUser && in_array($currentUser['role'] ?? '', ['admin', 'manager', 'inventory']);
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2"><?= t('nav.dashboard') ?></h1>
        <div class="btn-toolbar mb-2 mb-md-0">
            <div class="btn-group me-2">
                <button type="button" class="btn btn-sm btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown">
                    <?= t('common.actions') ?>
                </button>
                <ul class="dropdown-menu">
                    <li><a class="dropdown-item" href="/quotes/create"><i class="fas fa-file-alt me-2"></i><?= t('quotes.new_quote') ?></a></li>
                    <li><a class="dropdown-item" href="/sales-orders/create"><i class="fas fa-shopping-cart me-2"></i><?= t('sales_orders.new_order') ?></a></li>
                    <li><a class="dropdown-item" href="/invoices/create"><i class="fas fa-file-invoice me-2"></i><?= t('invoices.new_invoice') ?></a></li>
                    <?php if ($canManageProducts): ?>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item" href="/products/create"><i class="fas fa-plus me-2"></i><?= t('products.add_product') ?></a></li>
                    <?php endif; ?>
                </ul>
            </div>
            <button type="button" class="btn btn-sm btn-primary" onclick="location.reload()">
                <i class="fas fa-sync-alt"></i> <?= t('common.refresh') ?>
            </button>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="stats-card bg-primary">
                <div class="stats-icon">
                    <i class="fas fa-file-alt"></i>
                </div>
                <div class="stats-content">
                    <div class="stats-value"><?= number_format($stats['total_quotes'] ?? 0) ?></div>
                    <div class="stats-label"><?= t('nav.quotes') ?></div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="stats-card bg-success">
                <div class="stats-icon">
                    <i class="fas fa-shopping-cart"></i>
                </div>
                <div class="stats-content">
                    <div class="stats-value"><?= number_format($stats['total_orders'] ?? 0) ?></div>
                    <div class="stats-label"><?= t('nav.sales_orders') ?></div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="stats-card bg-info">
                <div class="stats-icon">
                    <i class="fas fa-file-invoice"></i>
                </div>
                <div class="stats-content">
                    <div class="stats-value"><?= number_format($stats['total_invoices'] ?? 0) ?></div>
                    <div class="stats-label"><?= t('nav.invoices') ?></div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="stats-card bg-warning">
                <div class="stats-icon">
                    <i class="fas fa-dollar-sign"></i>
                </div>
                <div class="stats-content">
                    <div class="stats-value"><?= ($stats['total_revenue'] ?? 0) ?></div>
                    <div class="stats-label"><?= t('dashboard.total_revenue') ?></div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Recent Activity -->
        <div class="col-xl-8 col-lg-7">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary"><?= t('dashboard.recent_activity') ?></h6>
                    <a href="/activity-log" class="btn btn-sm btn-outline-primary"><?= t('common.view_all') ?></a>
                </div>
                <div class="card-body">
                    <?php if (!empty($recent_activities)): ?>
                        <div class="table-responsive">
                            <table class="table table-borderless">
                                <tbody>
                                    <?php foreach (array_slice($recent_activities, 0, 10) as $activity): ?>
                                    <tr>
                                        <td class="border-left-primary" style="width: 50px;">
                                            <i class="fas <?= $activity['icon'] ?? 'fa-circle' ?> text-primary"></i>
                                        </td>
                                        <td>
                                            <div class="font-weight-bold"><?= htmlspecialchars($activity['title']) ?></div>
                                            <div class="small text-muted"><?= htmlspecialchars($activity['description']) ?></div>
                                        </td>
                                        <td class="text-end">
                                            <div class="small text-muted">
                                                <?php 
                                                $activityDate = $activity['date'] ?? $activity['created_at'] ?? null;
                                                if ($activityDate) {
                                                    echo date('M d, H:i', strtotime($activityDate));
                                                } else {
                                                    echo t('common.no_date');
                                                }
                                                ?>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-4">
                            <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                            <p class="text-muted"><?= t('dashboard.no_recent_activity') ?></p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Quick Stats & Alerts -->
        <div class="col-xl-4 col-lg-5">
            <!-- Low Stock Alert -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-warning"><?= t('dashboard.low_stock_alert') ?></h6>
                </div>
                <div class="card-body">
                    <?php if (!empty($low_stock_products)): ?>
                        <?php foreach (array_slice($low_stock_products, 0, 5) as $product): ?>
                        <div class="d-flex align-items-center mb-3">
                            <div class="flex-shrink-0">
                                <div class="status-indicator status-pending"></div>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <div class="fw-bold"><?= htmlspecialchars($product['name']) ?></div>
                                <div class="small text-muted"><?= t('products.stock') ?>: <?= $product['stock_quantity'] ?></div>
                            </div>
                            <div class="text-end">
                                <a href="/products/<?= $product['id'] ?>/edit" class="btn btn-sm btn-outline-primary">
                                    <?= t('common.update') ?>
                                </a>
                            </div>
                        </div>
                        <?php endforeach; ?>
                        <?php if (count($low_stock_products) > 5): ?>
                        <div class="text-center">
                            <a href="/products?status=low_stock" class="btn btn-sm btn-warning">
                                <?= t('common.view_all') ?> (<?= count($low_stock_products) ?>)
                            </a>
                        </div>
                        <?php endif; ?>
                    <?php else: ?>
                        <div class="text-center py-3">
                            <i class="fas fa-check-circle fa-2x text-success mb-2"></i>
                            <p class="text-muted mb-0"><?= t('dashboard.all_stock_good') ?></p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary"><?= t('dashboard.quick_actions') ?></h6>
                </div>
                <div class="card-body">
                    <div class="list-group list-group-flush">
                        <a href="/quotes/create" class="list-group-item list-group-item-action d-flex align-items-center">
                            <i class="fas fa-file-alt text-primary me-3"></i>
                            <div>
                                <div class="fw-bold"><?= t('quotes.create_quote') ?></div>
                                <div class="small text-muted"><?= t('quotes.create_new_quote_desc') ?></div>
                            </div>
                        </a>
                        
                        <a href="/sales-orders/create" class="list-group-item list-group-item-action d-flex align-items-center">
                            <i class="fas fa-shopping-cart text-success me-3"></i>
                            <div>
                                <div class="fw-bold"><?= t('sales_orders.create_order') ?></div>
                                <div class="small text-muted"><?= t('sales_orders.create_new_order_desc') ?></div>
                            </div>
                        </a>
                        
                        <a href="/invoices/create" class="list-group-item list-group-item-action d-flex align-items-center">
                            <i class="fas fa-file-invoice text-info me-3"></i>
                            <div>
                                <div class="fw-bold"><?= t('invoices.create_invoice') ?></div>
                                <div class="small text-muted"><?= t('invoices.create_new_invoice_desc') ?></div>
                            </div>
                        </a>
                        
                        <?php if ($canManageProducts): ?>
                        <a href="/products/create" class="list-group-item list-group-item-action d-flex align-items-center">
                            <i class="fas fa-plus text-warning me-3"></i>
                            <div>
                                <div class="fw-bold"><?= t('products.add_product') ?></div>
                                <div class="small text-muted"><?= t('products.add_new_product_desc') ?></div>
                            </div>
                        </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="row">
        <div class="col-xl-8 col-lg-7">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary"><?= t('dashboard.revenue_chart') ?></h6>
                </div>
                <div class="card-body">
                    <div class="chart-container">
                        <canvas id="revenueChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-4 col-lg-5">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary"><?= t('dashboard.order_status_distribution') ?></h6>
                </div>
                <div class="card-body">
                    <div class="chart-container" style="height: 300px;">
                        <canvas id="statusChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Revenue Chart
    if (document.getElementById('revenueChart')) {
        const ctx = document.getElementById('revenueChart').getContext('2d');
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: <?= json_encode($chart_data['revenue_labels'] ?? []) ?>,
                datasets: [{
                    label: '<?= t('dashboard.revenue') ?>',
                    data: <?= json_encode($chart_data['revenue_data'] ?? []) ?>,
                    borderColor: 'rgb(75, 192, 192)',
                    backgroundColor: 'rgba(75, 192, 192, 0.1)',
                    tension: 0.1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: true
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    }

    // Status Distribution Chart
    if (document.getElementById('statusChart')) {
        const ctx2 = document.getElementById('statusChart').getContext('2d');
        new Chart(ctx2, {
            type: 'doughnut',
            data: {
                labels: <?= json_encode($chart_data['status_labels'] ?? []) ?>,
                datasets: [{
                    data: <?= json_encode($chart_data['status_data'] ?? []) ?>,
                    backgroundColor: [
                        'rgb(54, 162, 235)',
                        'rgb(255, 205, 86)',
                        'rgb(255, 99, 132)',
                        'rgb(75, 192, 192)'
                    ]
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });
    }
});
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/app.php';
?>