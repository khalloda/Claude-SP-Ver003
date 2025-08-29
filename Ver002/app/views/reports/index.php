<?php
/**
 * File: app/views/reports/index.php
 * Purpose: Main reports dashboard with comprehensive analytics overview
 * Layout: Uses app layout with professional reporting interface
 */

$this->layout('layouts/app', [
    'title' => $page_title ?? t('nav.reports'),
    'active_nav' => 'reports'
]);

$currentUser = $this->getCurrentUser();
$canViewReports = $this->hasRole(['admin', 'manager', 'analyst']);
$canViewFinancial = $this->hasRole(['admin', 'manager', 'finance']);
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">
            <i class="fas fa-chart-line me-2"></i><?= t('nav.reports') ?> & <?= t('reports.analytics') ?>
        </h1>
        <div class="btn-toolbar mb-2 mb-md-0">
            <div class="btn-group me-2">
                <button type="button" class="btn btn-sm btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown">
                    <i class="fas fa-calendar"></i> <?= t('reports.date_range') ?>
                </button>
                <ul class="dropdown-menu">
                    <li><a class="dropdown-item" href="?period=today"><?= t('reports.today') ?></a></li>
                    <li><a class="dropdown-item" href="?period=week"><?= t('reports.this_week') ?></a></li>
                    <li><a class="dropdown-item" href="?period=month"><?= t('reports.this_month') ?></a></li>
                    <li><a class="dropdown-item" href="?period=quarter"><?= t('reports.this_quarter') ?></a></li>
                    <li><a class="dropdown-item" href="?period=year"><?= t('reports.this_year') ?></a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#customRangeModal"><?= t('reports.custom_range') ?></a></li>
                </ul>
            </div>
            <button type="button" class="btn btn-primary btn-sm" onclick="refreshDashboard()">
                <i class="fas fa-sync-alt"></i> <?= t('common.refresh') ?>
            </button>
        </div>
    </div>

    <!-- Key Metrics Summary -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="h4 mb-0"><?= number_format($metrics['total_revenue'] ?? 0, 2) ?></div>
                            <div class="small"><?= t('reports.total_revenue') ?></div>
                            <small class="opacity-75">
                                <?= ($metrics['revenue_change'] ?? 0) >= 0 ? '+' : '' ?><?= number_format($metrics['revenue_change'] ?? 0, 1) ?>% <?= t('reports.vs_previous') ?>
                            </small>
                        </div>
                        <i class="fas fa-dollar-sign fa-2x opacity-75"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="h4 mb-0"><?= number_format($metrics['total_orders'] ?? 0) ?></div>
                            <div class="small"><?= t('reports.total_orders') ?></div>
                            <small class="opacity-75">
                                <?= ($metrics['orders_change'] ?? 0) >= 0 ? '+' : '' ?><?= number_format($metrics['orders_change'] ?? 0, 1) ?>% <?= t('reports.vs_previous') ?>
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
                            <div class="h4 mb-0"><?= number_format($metrics['inventory_value'] ?? 0, 0) ?></div>
                            <div class="small"><?= t('reports.inventory_value') ?></div>
                            <small class="opacity-75">
                                <?= number_format($metrics['inventory_items'] ?? 0) ?> <?= t('reports.items') ?>
                            </small>
                        </div>
                        <i class="fas fa-boxes fa-2x opacity-75"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="h4 mb-0"><?= number_format($metrics['active_clients'] ?? 0) ?></div>
                            <div class="small"><?= t('reports.active_clients') ?></div>
                            <small class="opacity-75">
                                <?= ($metrics['new_clients'] ?? 0) > 0 ? '+' . $metrics['new_clients'] . ' ' . t('reports.new') : t('reports.no_new') ?>
                            </small>
                        </div>
                        <i class="fas fa-users fa-2x opacity-75"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8">
            <!-- Revenue Trend Chart -->
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><?= t('reports.revenue_trend') ?></h5>
                    <div class="btn-group btn-group-sm">
                        <button type="button" class="btn btn-outline-primary active" onclick="updateChart('revenue', 'daily')"><?= t('reports.daily') ?></button>
                        <button type="button" class="btn btn-outline-primary" onclick="updateChart('revenue', 'weekly')"><?= t('reports.weekly') ?></button>
                        <button type="button" class="btn btn-outline-primary" onclick="updateChart('revenue', 'monthly')"><?= t('reports.monthly') ?></button>
                    </div>
                </div>
                <div class="card-body">
                    <canvas id="revenueChart" height="300"></canvas>
                </div>
            </div>

            <!-- Recent Activity -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><?= t('reports.recent_activity') ?></h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th><?= t('reports.time') ?></th>
                                    <th><?= t('reports.activity') ?></th>
                                    <th><?= t('reports.user') ?></th>
                                    <th><?= t('reports.details') ?></th>
                                </tr>
                            </thead>
                            <tbody id="activityTable">
                                <!-- Activity data will be populated via AJAX -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <!-- Quick Report Access -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><?= t('reports.quick_reports') ?></h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <?php if ($canViewReports): ?>
                        <a href="/reports/inventory" class="btn btn-outline-primary">
                            <i class="fas fa-boxes me-2"></i><?= t('reports.inventory_report') ?>
                        </a>
                        <a href="/reports/sales" class="btn btn-outline-success">
                            <i class="fas fa-chart-bar me-2"></i><?= t('reports.sales_report') ?>
                        </a>
                        <?php endif; ?>
                        
                        <?php if ($canViewFinancial): ?>
                        <a href="/reports/financial" class="btn btn-outline-info">
                            <i class="fas fa-calculator me-2"></i><?= t('reports.financial_report') ?>
                        </a>
                        <?php endif; ?>
                        
                        <a href="/reports/clients" class="btn btn-outline-secondary">
                            <i class="fas fa-users me-2"></i><?= t('reports.client_report') ?>
                        </a>
                        
                        <hr>
                        
                        <a href="/reports/custom" class="btn btn-primary">
                            <i class="fas fa-cog me-2"></i><?= t('reports.custom_report') ?>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Top Performing Items -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><?= t('reports.top_products') ?></h5>
                </div>
                <div class="card-body">
                    <div id="topProducts">
                        <?php if (!empty($top_products)): ?>
                        <?php foreach ($top_products as $index => $product): ?>
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <div class="d-flex align-items-center">
                                <span class="badge bg-primary me-2"><?= $index + 1 ?></span>
                                <div>
                                    <div class="fw-bold"><?= htmlspecialchars($product->name) ?></div>
                                    <small class="text-muted"><?= number_format($product->quantity_sold) ?> <?= t('reports.sold') ?></small>
                                </div>
                            </div>
                            <div class="text-end">
                                <div class="fw-bold"><?= number_format($product->revenue, 2) ?></div>
                                <small class="text-muted"><?= $product->currency ?></small>
                            </div>
                        </div>
                        <?php endforeach; ?>
                        <?php else: ?>
                        <div class="text-center text-muted py-3">
                            <i class="fas fa-chart-line fa-2x mb-2"></i>
                            <p><?= t('reports.no_data_available') ?></p>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- System Alerts -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><?= t('reports.system_alerts') ?></h5>
                </div>
                <div class="card-body">
                    <div id="systemAlerts">
                        <?php if (!empty($alerts)): ?>
                        <?php foreach ($alerts as $alert): ?>
                        <div class="alert alert-<?= $alert->type ?> alert-dismissible fade show py-2" role="alert">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-<?= $alert->icon ?> me-2"></i>
                                <div class="flex-grow-1">
                                    <strong><?= htmlspecialchars($alert->title) ?></strong>
                                    <div class="small"><?= htmlspecialchars($alert->message) ?></div>
                                </div>
                            </div>
                            <button type="button" class="btn-close btn-close-sm" data-bs-dismiss="alert"></button>
                        </div>
                        <?php endforeach; ?>
                        <?php else: ?>
                        <div class="text-center text-muted py-3">
                            <i class="fas fa-check-circle fa-2x text-success mb-2"></i>
                            <p><?= t('reports.no_alerts') ?></p>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Custom Date Range Modal -->
<div class="modal fade" id="customRangeModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><?= t('reports.custom_date_range') ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="GET">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <label for="start_date" class="form-label"><?= t('reports.start_date') ?></label>
                            <input type="date" class="form-control" id="start_date" name="start_date" 
                                   value="<?= $_GET['start_date'] ?? date('Y-m-01') ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label for="end_date" class="form-label"><?= t('reports.end_date') ?></label>
                            <input type="date" class="form-control" id="end_date" name="end_date" 
                                   value="<?= $_GET['end_date'] ?? date('Y-m-d') ?>" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><?= t('common.cancel') ?></button>
                    <button type="submit" class="btn btn-primary"><?= t('common.apply') ?></button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize revenue chart
    initializeRevenueChart();
    
    // Load recent activity
    loadRecentActivity();
    
    // Auto-refresh every 5 minutes
    setInterval(function() {
        refreshDashboard();
    }, 300000);
});

function initializeRevenueChart() {
    const ctx = document.getElementById('revenueChart').getContext('2d');
    
    window.revenueChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: <?= json_encode($chart_data['labels'] ?? []) ?>,
            datasets: [{
                label: '<?= t('reports.revenue') ?>',
                data: <?= json_encode($chart_data['revenue'] ?? []) ?>,
                borderColor: 'rgb(54, 162, 235)',
                backgroundColor: 'rgba(54, 162, 235, 0.1)',
                tension: 0.4,
                fill: true
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return new Intl.NumberFormat().format(value);
                        }
                    }
                }
            },
            plugins: {
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return '<?= t('reports.revenue') ?>: ' + new Intl.NumberFormat().format(context.parsed.y);
                        }
                    }
                }
            }
        }
    });
}

function updateChart(type, period) {
    // Update active button
    event.target.parentNode.querySelectorAll('.btn').forEach(btn => btn.classList.remove('active'));
    event.target.classList.add('active');
    
    // Fetch new data
    fetch(`/api/reports/chart-data?type=${type}&period=${period}`)
    .then(response => response.json())
    .then(data => {
        window.revenueChart.data.labels = data.labels;
        window.revenueChart.data.datasets[0].data = data.values;
        window.revenueChart.update();
    })
    .catch(error => {
        console.error('Error updating chart:', error);
    });
}

function loadRecentActivity() {
    fetch('/api/reports/recent-activity')
    .then(response => response.json())
    .then(data => {
        const tbody = document.getElementById('activityTable');
        tbody.innerHTML = '';
        
        data.activities.forEach(activity => {
            const row = tbody.insertRow();
            row.innerHTML = `
                <td>${new Date(activity.created_at).toLocaleTimeString()}</td>
                <td>
                    <span class="badge bg-${activity.type_color}">${activity.type_label}</span>
                </td>
                <td>${activity.user_name}</td>
                <td>${activity.description}</td>
            `;
        });
    })
    .catch(error => {
        console.error('Error loading activity:', error);
    });
}

function refreshDashboard() {
    const btn = event.target;
    const originalContent = btn.innerHTML;
    
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> <?= t('reports.refreshing') ?>';
    btn.disabled = true;
    
    // Reload the page with current filters
    const urlParams = new URLSearchParams(window.location.search);
    window.location.href = window.location.pathname + '?' + urlParams.toString() + '&refresh=1';
}

// Export functionality
function exportReport(format, type) {
    const urlParams = new URLSearchParams(window.location.search);
    const exportUrl = `/reports/export?format=${format}&type=${type}&${urlParams.toString()}`;
    
    // Create temporary link and click it
    const link = document.createElement('a');
    link.href = exportUrl;
    link.download = `${type}_report_${new Date().toISOString().split('T')[0]}.${format}`;
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
}
</script>

<style>
.card {
    transition: all 0.3s ease;
}

.card:hover {
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
}

.opacity-75 {
    opacity: 0.75;
}

.alert-dismissible .btn-close-sm {
    padding: 0.25rem;
    font-size: 0.75rem;
}

@media (max-width: 768px) {
    .col-md-3 {
        margin-bottom: 1rem;
    }
    
    .btn-toolbar {
        flex-wrap: wrap;
        gap: 0.5rem;
    }
}
</style>