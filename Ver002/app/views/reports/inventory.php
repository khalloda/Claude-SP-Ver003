<?php
/**
 * File: app/views/reports/inventory.php
 * Purpose: Comprehensive inventory reports and analytics
 * Layout: Uses app layout with advanced inventory reporting capabilities
 */

$this->layout('layouts/app', [
    'title' => $page_title ?? t('reports.inventory_reports'),
    'active_nav' => 'reports'
]);

$currentUser = $this->getCurrentUser();
$canExport = $this->hasRole(['admin', 'manager', 'analyst']);
$canViewCosts = $this->hasRole(['admin', 'manager', 'finance']);
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">
            <i class="fas fa-boxes me-2"></i><?= t('reports.inventory_reports') ?>
        </h1>
        <div class="btn-toolbar mb-2 mb-md-0">
            <?php if ($canExport): ?>
            <div class="btn-group me-2">
                <button type="button" class="btn btn-sm btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown">
                    <i class="fas fa-download"></i> <?= t('common.export') ?>
                </button>
                <ul class="dropdown-menu">
                    <li><a class="dropdown-item" href="#" onclick="exportReport('csv', 'inventory')">
                        <i class="fas fa-file-csv me-2"></i><?= t('common.export_csv') ?>
                    </a></li>
                    <li><a class="dropdown-item" href="#" onclick="exportReport('excel', 'inventory')">
                        <i class="fas fa-file-excel me-2"></i><?= t('common.export_excel') ?>
                    </a></li>
                    <li><a class="dropdown-item" href="#" onclick="exportReport('pdf', 'inventory')">
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
                <div class="col-md-3">
                    <label for="warehouse" class="form-label"><?= t('reports.warehouse') ?></label>
                    <select class="form-select" id="warehouse" name="warehouse">
                        <option value=""><?= t('reports.all_warehouses') ?></option>
                        <?php foreach (($warehouses ?? []) as $warehouse): ?>
                        <option value="<?= $warehouse->id ?>" <?= $this->selected('warehouse', $warehouse->id) ?>>
                            <?= htmlspecialchars($warehouse->name) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="category" class="form-label"><?= t('reports.category') ?></label>
                    <select class="form-select" id="category" name="category">
                        <option value=""><?= t('reports.all_categories') ?></option>
                        <?php foreach (($categories ?? []) as $category): ?>
                        <option value="<?= $category->id ?>" <?= $this->selected('category', $category->id) ?>>
                            <?= htmlspecialchars($category->name) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="stock_status" class="form-label"><?= t('reports.stock_status') ?></label>
                    <select class="form-select" id="stock_status" name="stock_status">
                        <option value=""><?= t('reports.all_statuses') ?></option>
                        <option value="in_stock" <?= $this->selected('stock_status', 'in_stock') ?>><?= t('reports.in_stock') ?></option>
                        <option value="low_stock" <?= $this->selected('stock_status', 'low_stock') ?>><?= t('reports.low_stock') ?></option>
                        <option value="out_of_stock" <?= $this->selected('stock_status', 'out_of_stock') ?>><?= t('reports.out_of_stock') ?></option>
                        <option value="overstock" <?= $this->selected('stock_status', 'overstock') ?>><?= t('reports.overstock') ?></option>
                    </select>
                </div>
                <div class="col-md-3">
                    <div class="btn-group w-100">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-filter"></i> <?= t('common.filter') ?>
                        </button>
                        <a href="/reports/inventory" class="btn btn-outline-secondary">
                            <i class="fas fa-times"></i> <?= t('common.clear') ?>
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Inventory Summary Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body text-center">
                    <div class="h4"><?= number_format($summary['total_products'] ?? 0) ?></div>
                    <div class="small"><?= t('reports.total_products') ?></div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body text-center">
                    <div class="h4"><?= number_format($summary['total_quantity'] ?? 0) ?></div>
                    <div class="small"><?= t('reports.total_quantity') ?></div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-white">
                <div class="card-body text-center">
                    <div class="h4"><?= number_format($summary['low_stock_items'] ?? 0) ?></div>
                    <div class="small"><?= t('reports.low_stock_items') ?></div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body text-center">
                    <div class="h4"><?= number_format($summary['total_value'] ?? 0, 2) ?></div>
                    <div class="small"><?= t('reports.total_value') ?> (<?= $summary['currency'] ?? 'USD' ?>)</div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8">
            <!-- Inventory Levels Chart -->
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><?= t('reports.inventory_levels') ?></h5>
                    <div class="btn-group btn-group-sm">
                        <button type="button" class="btn btn-outline-primary active" onclick="updateChart('quantity')"><?= t('reports.by_quantity') ?></button>
                        <?php if ($canViewCosts): ?>
                        <button type="button" class="btn btn-outline-primary" onclick="updateChart('value')"><?= t('reports.by_value') ?></button>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="card-body">
                    <canvas id="inventoryChart" height="300"></canvas>
                </div>
            </div>

            <!-- Detailed Inventory List -->
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><?= t('reports.inventory_details') ?></h5>
                    <div class="d-flex align-items-center">
                        <label for="per_page" class="form-label me-2 mb-0"><?= t('common.show') ?>:</label>
                        <select class="form-select form-select-sm" id="per_page" name="per_page" onchange="this.form.submit()">
                            <option value="25" <?= $this->selected('per_page', '25', true) ?>>25</option>
                            <option value="50" <?= $this->selected('per_page', '50') ?>>50</option>
                            <option value="100" <?= $this->selected('per_page', '100') ?>>100</option>
                        </select>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped" id="inventoryTable">
                            <thead>
                                <tr>
                                    <th>
                                        <a href="?<?= http_build_query(array_merge($_GET, ['sort' => 'name', 'direction' => ($_GET['sort'] ?? '') === 'name' && ($_GET['direction'] ?? '') === 'asc' ? 'desc' : 'asc'])) ?>" class="text-decoration-none">
                                            <?= t('products.product') ?>
                                            <?php if (($_GET['sort'] ?? '') === 'name'): ?>
                                            <i class="fas fa-sort-<?= ($_GET['direction'] ?? 'asc') === 'asc' ? 'up' : 'down' ?>"></i>
                                            <?php endif; ?>
                                        </a>
                                    </th>
                                    <th><?= t('reports.warehouse') ?></th>
                                    <th class="text-center">
                                        <a href="?<?= http_build_query(array_merge($_GET, ['sort' => 'quantity', 'direction' => ($_GET['sort'] ?? '') === 'quantity' && ($_GET['direction'] ?? '') === 'asc' ? 'desc' : 'asc'])) ?>" class="text-decoration-none">
                                            <?= t('reports.current_stock') ?>
                                            <?php if (($_GET['sort'] ?? '') === 'quantity'): ?>
                                            <i class="fas fa-sort-<?= ($_GET['direction'] ?? 'asc') === 'asc' ? 'up' : 'down' ?>"></i>
                                            <?php endif; ?>
                                        </a>
                                    </th>
                                    <th class="text-center"><?= t('reports.reorder_level') ?></th>
                                    <?php if ($canViewCosts): ?>
                                    <th class="text-end"><?= t('reports.unit_cost') ?></th>
                                    <th class="text-end"><?= t('reports.total_value') ?></th>
                                    <?php endif; ?>
                                    <th class="text-center"><?= t('reports.status') ?></th>
                                    <th class="text-center"><?= t('reports.last_movement') ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($inventory_items)): ?>
                                <?php foreach ($inventory_items as $item): ?>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <?php if ($item->image): ?>
                                            <img src="<?= htmlspecialchars($item->image) ?>" alt="" class="rounded me-2" style="width: 32px; height: 32px; object-fit: cover;">
                                            <?php else: ?>
                                            <div class="bg-light rounded me-2 d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                                                <i class="fas fa-box text-muted"></i>
                                            </div>
                                            <?php endif; ?>
                                            <div>
                                                <a href="/products/<?= $item->id ?>" class="fw-bold text-decoration-none">
                                                    <?= htmlspecialchars($item->name) ?>
                                                </a>
                                                <?php if ($item->sku): ?>
                                                <br><small class="text-muted"><?= htmlspecialchars($item->sku) ?></small>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <a href="/warehouses/<?= $item->warehouse_id ?>" class="text-decoration-none">
                                            <?= htmlspecialchars($item->warehouse_name) ?>
                                        </a>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-<?= $item->stock_status_color ?>"><?= number_format($item->quantity) ?></span>
                                        <?php if ($item->unit): ?>
                                        <br><small class="text-muted"><?= htmlspecialchars($item->unit) ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center">
                                        <?= $item->reorder_level ? number_format($item->reorder_level) : '-' ?>
                                    </td>
                                    <?php if ($canViewCosts): ?>
                                    <td class="text-end">
                                        <?= number_format($item->unit_cost, 2) ?>
                                    </td>
                                    <td class="text-end">
                                        <strong><?= number_format($item->total_value, 2) ?></strong>
                                    </td>
                                    <?php endif; ?>
                                    <td class="text-center">
                                        <?php
                                        $statusClass = match($item->stock_status) {
                                            'in_stock' => 'success',
                                            'low_stock' => 'warning',
                                            'out_of_stock' => 'danger',
                                            'overstock' => 'info',
                                            default => 'secondary'
                                        };
                                        ?>
                                        <span class="badge bg-<?= $statusClass ?>">
                                            <?= t('reports.' . $item->stock_status) ?>
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <?= $item->last_movement ? date('M d, Y', strtotime($item->last_movement)) : '-' ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                                <?php else: ?>
                                <tr>
                                    <td colspan="<?= $canViewCosts ? '8' : '6' ?>" class="text-center text-muted py-4">
                                        <i class="fas fa-search fa-2x mb-2"></i>
                                        <p><?= t('reports.no_inventory_data') ?></p>
                                    </td>
                                </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Pagination -->
                    <?php if (!empty($inventory_items)): ?>
                    <div class="d-flex justify-content-between align-items-center mt-3">
                        <div>
                            <?= t('common.showing') ?> <?= ($pagination['current_page'] - 1) * $pagination['per_page'] + 1 ?> 
                            <?= t('common.to') ?> <?= min($pagination['current_page'] * $pagination['per_page'], $pagination['total']) ?> 
                            <?= t('common.of') ?> <?= $pagination['total'] ?> <?= t('common.entries') ?>
                        </div>
                        <?= $this->paginate($inventory_items) ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <!-- Stock Status Distribution -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><?= t('reports.stock_distribution') ?></h5>
                </div>
                <div class="card-body">
                    <canvas id="stockDistributionChart" height="250"></canvas>
                </div>
            </div>

            <!-- Critical Stock Alerts -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><?= t('reports.critical_stock_alerts') ?></h5>
                </div>
                <div class="card-body">
                    <?php if (!empty($critical_stock)): ?>
                    <?php foreach ($critical_stock as $item): ?>
                    <div class="alert alert-<?= $item->quantity <= 0 ? 'danger' : 'warning' ?> py-2 mb-2">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <strong><?= htmlspecialchars($item->name) ?></strong>
                                <br><small><?= htmlspecialchars($item->warehouse_name) ?></small>
                            </div>
                            <div class="text-end">
                                <strong><?= number_format($item->quantity) ?></strong>
                                <br><small><?= t('reports.need') ?> <?= number_format($item->reorder_level - $item->quantity) ?></small>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                    <?php else: ?>
                    <div class="text-center text-muted py-3">
                        <i class="fas fa-check-circle fa-2x text-success mb-2"></i>
                        <p><?= t('reports.no_critical_stock') ?></p>
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
                        <a href="/inventory/reorder-report" class="btn btn-outline-warning btn-sm">
                            <i class="fas fa-exclamation-triangle me-2"></i><?= t('reports.reorder_report') ?>
                        </a>
                        <a href="/inventory/valuation-report" class="btn btn-outline-info btn-sm">
                            <i class="fas fa-calculator me-2"></i><?= t('reports.valuation_report') ?>
                        </a>
                        <a href="/inventory/movement-history" class="btn btn-outline-secondary btn-sm">
                            <i class="fas fa-history me-2"></i><?= t('reports.movement_history') ?>
                        </a>
                        <hr class="my-2">
                        <a href="/inventory/cycle-count" class="btn btn-primary btn-sm">
                            <i class="fas fa-clipboard-check me-2"></i><?= t('reports.cycle_count') ?>
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

function initializeCharts() {
    // Inventory levels chart
    const ctx1 = document.getElementById('inventoryChart').getContext('2d');
    window.inventoryChart = new Chart(ctx1, {
        type: 'bar',
        data: {
            labels: <?= json_encode($chart_data['labels'] ?? []) ?>,
            datasets: [{
                label: '<?= t('reports.quantity') ?>',
                data: <?= json_encode($chart_data['quantities'] ?? []) ?>,
                backgroundColor: 'rgba(54, 162, 235, 0.8)',
                borderColor: 'rgba(54, 162, 235, 1)',
                borderWidth: 1
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

    // Stock distribution chart
    const ctx2 = document.getElementById('stockDistributionChart').getContext('2d');
    new Chart(ctx2, {
        type: 'doughnut',
        data: {
            labels: [
                '<?= t('reports.in_stock') ?>',
                '<?= t('reports.low_stock') ?>',
                '<?= t('reports.out_of_stock') ?>',
                '<?= t('reports.overstock') ?>'
            ],
            datasets: [{
                data: <?= json_encode($stock_distribution ?? [60, 25, 10, 5]) ?>,
                backgroundColor: [
                    'rgba(40, 167, 69, 0.8)',
                    'rgba(255, 193, 7, 0.8)',
                    'rgba(220, 53, 69, 0.8)',
                    'rgba(23, 162, 184, 0.8)'
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
    
    // Update chart data based on type
    const currentFilters = new URLSearchParams(window.location.search);
    
    fetch(`/api/reports/inventory-chart?type=${type}&${currentFilters.toString()}`)
    .then(response => response.json())
    .then(data => {
        window.inventoryChart.data.labels = data.labels;
        window.inventoryChart.data.datasets[0].data = data.values;
        window.inventoryChart.data.datasets[0].label = type === 'value' ? '<?= t('reports.value') ?>' : '<?= t('reports.quantity') ?>';
        window.inventoryChart.update();
    })
    .catch(error => {
        console.error('Error updating chart:', error);
    });
}

function exportReport(format, type) {
    const currentFilters = new URLSearchParams(window.location.search);
    const exportUrl = `/reports/export?format=${format}&type=${type}&${currentFilters.toString()}`;
    
    // Create temporary link and click it
    const link = document.createElement('a');
    link.href = exportUrl;
    link.download = `${type}_report_${new Date().toISOString().split('T')[0]}.${format}`;
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
}
</script>