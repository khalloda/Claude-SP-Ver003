<?php
/**
 * File: app/views/reports/sales.php
 * Purpose: Comprehensive sales reports and analytics dashboard
 * Layout: Uses app layout with advanced sales reporting capabilities
 */

$this->layout('layouts/app', [
    'title' => $page_title ?? t('reports.sales_reports'),
    'active_nav' => 'reports'
]);

$currentUser = $this->getCurrentUser();
$canExport = $this->hasRole(['admin', 'manager', 'analyst']);
$canViewDetails = $this->hasRole(['admin', 'manager', 'sales']);
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">
            <i class="fas fa-chart-bar me-2"></i><?= t('reports.sales_reports') ?>
        </h1>
        <div class="btn-toolbar mb-2 mb-md-0">
            <?php if ($canExport): ?>
            <div class="btn-group me-2">
                <button type="button" class="btn btn-sm btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown">
                    <i class="fas fa-download"></i> <?= t('common.export') ?>
                </button>
                <ul class="dropdown-menu">
                    <li><a class="dropdown-item" href="#" onclick="exportReport('csv', 'sales')">
                        <i class="fas fa-file-csv me-2"></i><?= t('common.export_csv') ?>
                    </a></li>
                    <li><a class="dropdown-item" href="#" onclick="exportReport('excel', 'sales')">
                        <i class="fas fa-file-excel me-2"></i><?= t('common.export_excel') ?>
                    </a></li>
                    <li><a class="dropdown-item" href="#" onclick="exportReport('pdf', 'sales')">
                        <i class="fas fa-file-pdf me-2"></i><?= t('common.export_pdf') ?>
                    </a></li>
                </ul>
            </div>
            <?php endif; ?>
            <a href="/reports" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left"></i> <?= t('reports.back_to_reports') ?>
            </a>
        </div>
    </div>

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" id="filtersForm" class="row align-items-end">
                <div class="col-md-2">
                    <label for="period" class="form-label"><?= t('reports.period') ?></label>
                    <select class="form-select" id="period" name="period" onchange="toggleCustomDates()">
                        <option value="today" <?= $this->selected('period', 'today') ?>><?= t('reports.today') ?></option>
                        <option value="week" <?= $this->selected('period', 'week') ?>><?= t('reports.this_week') ?></option>
                        <option value="month" <?= $this->selected('period', 'month', true) ?>><?= t('reports.this_month') ?></option>
                        <option value="quarter" <?= $this->selected('period', 'quarter') ?>><?= t('reports.this_quarter') ?></option>
                        <option value="year" <?= $this->selected('period', 'year') ?>><?= t('reports.this_year') ?></option>
                        <option value="custom" <?= $this->selected('period', 'custom') ?>><?= t('reports.custom_range') ?></option>
                    </select>
                </div>
                <div class="col-md-2" id="custom-dates" style="<?= ($_GET['period'] ?? '') === 'custom' ? '' : 'display: none;' ?>">
                    <label for="start_date" class="form-label"><?= t('reports.start_date') ?></label>
                    <input type="date" class="form-control" id="start_date" name="start_date" 
                           value="<?= $_GET['start_date'] ?? date('Y-m-01') ?>">
                </div>
                <div class="col-md-2" id="custom-dates-end" style="<?= ($_GET['period'] ?? '') === 'custom' ? '' : 'display: none;' ?>">
                    <label for="end_date" class="form-label"><?= t('reports.end_date') ?></label>
                    <input type="date" class="form-control" id="end_date" name="end_date" 
                           value="<?= $_GET['end_date'] ?? date('Y-m-d') ?>">
                </div>
                <div class="col-md-2">
                    <label for="salesperson" class="form-label"><?= t('reports.salesperson') ?></label>
                    <select class="form-select" id="salesperson" name="salesperson">
                        <option value=""><?= t('reports.all_salespeople') ?></option>
                        <?php foreach (($salespeople ?? []) as $person): ?>
                        <option value="<?= $person->id ?>" <?= $this->selected('salesperson', $person->id) ?>>
                            <?= htmlspecialchars($person->name) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="client" class="form-label"><?= t('reports.client') ?></label>
                    <select class="form-select" id="client" name="client">
                        <option value=""><?= t('reports.all_clients') ?></option>
                        <?php foreach (($clients ?? []) as $client): ?>
                        <option value="<?= $client->id ?>" <?= $this->selected('client', $client->id) ?>>
                            <?= htmlspecialchars($client->name) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <div class="btn-group w-100">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-filter"></i> <?= t('common.filter') ?>
                        </button>
                        <a href="/reports/sales" class="btn btn-outline-secondary">
                            <i class="fas fa-times"></i> <?= t('common.clear') ?>
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Sales Summary Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="h4 mb-0"><?= number_format($summary['total_sales'] ?? 0, 2) ?></div>
                            <div class="small"><?= t('reports.total_sales') ?></div>
                            <small class="opacity-75">
                                <?= ($summary['sales_change'] ?? 0) >= 0 ? '+' : '' ?><?= number_format($summary['sales_change'] ?? 0, 1) ?>% <?= t('reports.vs_previous') ?>
                            </small>
                        </div>
                        <i class="fas fa-dollar-sign fa-2x opacity-75"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="h4 mb-0"><?= number_format($summary['total_orders'] ?? 0) ?></div>
                            <div class="small"><?= t('reports.total_orders') ?></div>
                            <small class="opacity-75">
                                <?= ($summary['orders_change'] ?? 0) >= 0 ? '+' : '' ?><?= number_format($summary['orders_change'] ?? 0, 1) ?>% <?= t('reports.vs_previous') ?>
                            </small>
                        </div>
                        <i class="fas fa-shopping-cart fa-2x opacity-75"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="h4 mb-0"><?= number_format($summary['avg_order_value'] ?? 0, 2) ?></div>
                            <div class="small"><?= t('reports.avg_order_value') ?></div>
                            <small class="opacity-75">
                                <?= ($summary['aov_change'] ?? 0) >= 0 ? '+' : '' ?><?= number_format($summary['aov_change'] ?? 0, 1) ?>% <?= t('reports.vs_previous') ?>
                            </small>
                        </div>
                        <i class="fas fa-chart-line fa-2x opacity-75"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="h4 mb-0"><?= number_format($summary['conversion_rate'] ?? 0, 1) ?>%</div>
                            <div class="small"><?= t('reports.conversion_rate') ?></div>
                            <small class="opacity-75">
                                <?= t('reports.quotes_to_orders') ?>
                            </small>
                        </div>
                        <i class="fas fa-percentage fa-2x opacity-75"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8">
            <!-- Sales Trend Chart -->
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><?= t('reports.sales_trend') ?></h5>
                    <div class="btn-group btn-group-sm">
                        <button type="button" class="btn btn-outline-primary active" onclick="updateChart('revenue')"><?= t('reports.revenue') ?></button>
                        <button type="button" class="btn btn-outline-primary" onclick="updateChart('orders')"><?= t('reports.orders') ?></button>
                        <button type="button" class="btn btn-outline-primary" onclick="updateChart('units')"><?= t('reports.units_sold') ?></button>
                    </div>
                </div>
                <div class="card-body">
                    <canvas id="salesChart" height="300"></canvas>
                </div>
            </div>

            <!-- Sales Performance by Product -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><?= t('reports.top_performing_products') ?></h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th><?= t('products.product') ?></th>
                                    <th class="text-center"><?= t('reports.units_sold') ?></th>
                                    <th class="text-end"><?= t('reports.revenue') ?></th>
                                    <th class="text-end"><?= t('reports.avg_price') ?></th>
                                    <th class="text-center"><?= t('reports.growth') ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($top_products)): ?>
                                <?php foreach ($top_products as $product): ?>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <?php if ($product->image): ?>
                                            <img src="<?= htmlspecialchars($product->image) ?>" alt="" class="rounded me-2" style="width: 32px; height: 32px; object-fit: cover;">
                                            <?php endif; ?>
                                            <div>
                                                <a href="/products/<?= $product->id ?>" class="fw-bold text-decoration-none">
                                                    <?= htmlspecialchars($product->name) ?>
                                                </a>
                                                <br><small class="text-muted"><?= htmlspecialchars($product->sku) ?></small>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-primary"><?= number_format($product->units_sold) ?></span>
                                    </td>
                                    <td class="text-end">
                                        <strong><?= number_format($product->revenue, 2) ?></strong>
                                        <br><small class="text-muted"><?= $product->currency ?></small>
                                    </td>
                                    <td class="text-end">
                                        <?= number_format($product->avg_price, 2) ?>
                                    </td>
                                    <td class="text-center">
                                        <span class="text-<?= ($product->growth ?? 0) >= 0 ? 'success' : 'danger' ?>">
                                            <?= ($product->growth ?? 0) >= 0 ? '+' : '' ?><?= number_format($product->growth ?? 0, 1) ?>%
                                        </span>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                                <?php else: ?>
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-4">
                                        <i class="fas fa-chart-line fa-2x mb-2"></i>
                                        <p><?= t('reports.no_sales_data') ?></p>
                                    </td>
                                </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Recent Sales -->
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><?= t('reports.recent_sales') ?></h5>
                    <a href="/sales-orders" class="btn btn-sm btn-outline-primary"><?= t('reports.view_all_orders') ?></a>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th><?= t('reports.date') ?></th>
                                    <th><?= t('reports.order_number') ?></th>
                                    <th><?= t('reports.client') ?></th>
                                    <th><?= t('reports.salesperson') ?></th>
                                    <th class="text-end"><?= t('reports.amount') ?></th>
                                    <th class="text-center"><?= t('reports.status') ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($recent_orders)): ?>
                                <?php foreach ($recent_orders as $order): ?>
                                <tr>
                                    <td><?= date('M d, Y', strtotime($order->order_date)) ?></td>
                                    <td>
                                        <a href="/sales-orders/<?= $order->id ?>" class="text-decoration-none">
                                            <?= htmlspecialchars($order->order_number) ?>
                                        </a>
                                    </td>
                                    <td>
                                        <a href="/clients/<?= $order->client_id ?>" class="text-decoration-none">
                                            <?= htmlspecialchars($order->client_name) ?>
                                        </a>
                                    </td>
                                    <td><?= htmlspecialchars($order->salesperson_name ?? 'N/A') ?></td>
                                    <td class="text-end">
                                        <strong><?= number_format($order->total_amount, 2) ?></strong>
                                        <br><small class="text-muted"><?= $order->currency ?></small>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-<?= $order->status === 'completed' ? 'success' : 'warning' ?>">
                                            <?= t('sales_orders.status.' . $order->status) ?>
                                        </span>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <!-- Sales by Category -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><?= t('reports.sales_by_category') ?></h5>
                </div>
                <div class="card-body">
                    <canvas id="categoryChart" height="250"></canvas>
                </div>
            </div>

            <!-- Sales Team Performance -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><?= t('reports.sales_team_performance') ?></h5>
                </div>
                <div class="card-body">
                    <?php if (!empty($team_performance)): ?>
                    <?php foreach ($team_performance as $member): ?>
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div class="d-flex align-items-center">
                            <div class="user-avatar bg-primary text-white rounded-circle me-3" style="width: 40px; height: 40px; display: flex; align-items: center; justify-content: center;">
                                <?= strtoupper(substr($member->name, 0, 1)) ?>
                            </div>
                            <div>
                                <div class="fw-bold"><?= htmlspecialchars($member->name) ?></div>
                                <small class="text-muted"><?= number_format($member->orders_count) ?> <?= t('reports.orders') ?></small>
                            </div>
                        </div>
                        <div class="text-end">
                            <div class="fw-bold"><?= number_format($member->total_sales, 2) ?></div>
                            <small class="text-<?= ($member->target_achievement ?? 0) >= 100 ? 'success' : 'warning' ?>">
                                <?= number_format($member->target_achievement ?? 0, 1) ?>% <?= t('reports.of_target') ?>
                            </small>
                        </div>
                    </div>
                    <div class="progress mb-3" style="height: 4px;">
                        <div class="progress-bar bg-<?= ($member->target_achievement ?? 0) >= 100 ? 'success' : 'primary' ?>" 
                             style="width: <?= min($member->target_achievement ?? 0, 100) ?>%"></div>
                    </div>
                    <?php endforeach; ?>
                    <?php else: ?>
                    <div class="text-center text-muted py-3">
                        <i class="fas fa-users fa-2x mb-2"></i>
                        <p><?= t('reports.no_team_data') ?></p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Sales Forecast -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><?= t('reports.sales_forecast') ?></h5>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-6">
                            <div class="border rounded p-3">
                                <div class="h6 mb-0"><?= number_format($forecast['next_month'] ?? 0, 2) ?></div>
                                <small class="text-muted"><?= t('reports.next_month') ?></small>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="border rounded p-3">
                                <div class="h6 mb-0"><?= number_format($forecast['next_quarter'] ?? 0, 2) ?></div>
                                <small class="text-muted"><?= t('reports.next_quarter') ?></small>
                            </div>
                        </div>
                    </div>
                    
                    <hr>
                    
                    <div class="small text-muted">
                        <i class="fas fa-info-circle me-2"></i>
                        <?= t('reports.forecast_note') ?>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><?= t('common.quick_actions') ?></h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="/reports/sales/detailed" class="btn btn-outline-primary btn-sm">
                            <i class="fas fa-list me-2"></i><?= t('reports.detailed_sales_report') ?>
                        </a>
                        <a href="/reports/sales/commissions" class="btn btn-outline-success btn-sm">
                            <i class="fas fa-percentage me-2"></i><?= t('reports.commission_report') ?>
                        </a>
                        <a href="/reports/sales/trends" class="btn btn-outline-info btn-sm">
                            <i class="fas fa-chart-line me-2"></i><?= t('reports.sales_trends') ?>
                        </a>
                        <hr class="my-2">
                        <a href="/sales-orders/create" class="btn btn-primary btn-sm">
                            <i class="fas fa-plus me-2"></i><?= t('reports.new_sales_order') ?>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    initializeCharts();
});

function toggleCustomDates() {
    const period = document.getElementById('period').value;
    const customDates = document.getElementById('custom-dates');
    const customDatesEnd = document.getElementById('custom-dates-end');
    
    if (period === 'custom') {
        customDates.style.display = 'block';
        customDatesEnd.style.display = 'block';
    } else {
        customDates.style.display = 'none';
        customDatesEnd.style.display = 'none';
    }
}

function initializeCharts() {
    // Sales trend chart
    const ctx1 = document.getElementById('salesChart').getContext('2d');
    window.salesChart = new Chart(ctx1, {
        type: 'line',
        data: {
            labels: <?= json_encode($chart_data['labels'] ?? []) ?>,
            datasets: [{
                label: '<?= t('reports.sales') ?>',
                data: <?= json_encode($chart_data['sales'] ?? []) ?>,
                borderColor: 'rgb(40, 167, 69)',
                backgroundColor: 'rgba(40, 167, 69, 0.1)',
                tension: 0.4,
                fill: true
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });

    // Category chart
    const ctx2 = document.getElementById('categoryChart').getContext('2d');
    new Chart(ctx2, {
        type: 'doughnut',
        data: {
            labels: <?= json_encode($category_data['labels'] ?? []) ?>,
            datasets: [{
                data: <?= json_encode($category_data['values'] ?? []) ?>,
                backgroundColor: [
                    'rgba(255, 99, 132, 0.8)',
                    'rgba(54, 162, 235, 0.8)',
                    'rgba(255, 205, 86, 0.8)',
                    'rgba(75, 192, 192, 0.8)',
                    'rgba(153, 102, 255, 0.8)',
                    'rgba(255, 159, 64, 0.8)'
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

function updateChart(type) {
    // Update active button
    event.target.parentNode.querySelectorAll('.btn').forEach(btn => btn.classList.remove('active'));
    event.target.classList.add('active');
    
    const currentFilters = new URLSearchParams(window.location.search);
    
    fetch(`/api/reports/sales-chart?type=${type}&${currentFilters.toString()}`)
    .then(response => response.json())
    .then(data => {
        window.salesChart.data.labels = data.labels;
        window.salesChart.data.datasets[0].data = data.values;
        window.salesChart.data.datasets[0].label = data.label;
        window.salesChart.update();
    })
    .catch(error => {
        console.error('Error updating chart:', error);
    });
}

function exportReport(format, type) {
    const currentFilters = new URLSearchParams(window.location.search);
    const exportUrl = `/reports/export?format=${format}&type=${type}&${currentFilters.toString()}`;
    
    const link = document.createElement('a');
    link.href = exportUrl;
    link.download = `${type}_report_${new Date().toISOString().split('T')[0]}.${format}`;
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
}
</script>

<style>
.opacity-75 {
    opacity: 0.75;
}

.user-avatar {
    font-weight: bold;
}

.progress {
    transition: all 0.3s ease;
}

@media (max-width: 768px) {
    .col-md-3, .col-md-2 {
        margin-bottom: 1rem;
    }
}
</style>