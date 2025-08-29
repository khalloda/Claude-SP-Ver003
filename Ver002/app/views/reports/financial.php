<?php
/**
 * File: app/views/reports/financial.php
 * Purpose: Comprehensive financial reports and analytics dashboard
 * Layout: Uses app layout with advanced financial reporting capabilities
 */

$this->layout('layouts/app', [
    'title' => $page_title ?? t('reports.financial_reports'),
    'active_nav' => 'reports'
]);

$currentUser = $this->getCurrentUser();
$canExport = $this->hasRole(['admin', 'manager', 'finance']);
$canViewDetails = $this->hasRole(['admin', 'manager', 'finance']);

// Restrict access to financial reports
if (!$canViewDetails) {
    $this->redirect('/reports');
    return;
}
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">
            <i class="fas fa-calculator me-2"></i><?= t('reports.financial_reports') ?>
            <span class="badge bg-warning text-dark ms-2"><?= t('reports.confidential') ?></span>
        </h1>
        <div class="btn-toolbar mb-2 mb-md-0">
            <?php if ($canExport): ?>
            <div class="btn-group me-2">
                <button type="button" class="btn btn-sm btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown">
                    <i class="fas fa-download"></i> <?= t('common.export') ?>
                </button>
                <ul class="dropdown-menu">
                    <li><a class="dropdown-item" href="#" onclick="exportReport('csv', 'financial')">
                        <i class="fas fa-file-csv me-2"></i><?= t('common.export_csv') ?>
                    </a></li>
                    <li><a class="dropdown-item" href="#" onclick="exportReport('excel', 'financial')">
                        <i class="fas fa-file-excel me-2"></i><?= t('common.export_excel') ?>
                    </a></li>
                    <li><a class="dropdown-item" href="#" onclick="exportReport('pdf', 'financial')">
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
                    <label for="period" class="form-label"><?= t('reports.period') ?></label>
                    <select class="form-select" id="period" name="period" onchange="toggleCustomDates()">
                        <option value="month" <?= $this->selected('period', 'month', true) ?>><?= t('reports.this_month') ?></option>
                        <option value="quarter" <?= $this->selected('period', 'quarter') ?>><?= t('reports.this_quarter') ?></option>
                        <option value="year" <?= $this->selected('period', 'year') ?>><?= t('reports.this_year') ?></option>
                        <option value="ytd" <?= $this->selected('period', 'ytd') ?>><?= t('reports.year_to_date') ?></option>
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
                <div class="col-md-3">
                    <label for="currency" class="form-label"><?= t('reports.currency') ?></label>
                    <select class="form-select" id="currency" name="currency">
                        <option value=""><?= t('reports.all_currencies') ?></option>
                        <?php foreach (($currencies ?? []) as $curr): ?>
                        <option value="<?= $curr->code ?>" <?= $this->selected('currency', $curr->code) ?>>
                            <?= htmlspecialchars($curr->name) ?> (<?= $curr->code ?>)
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <div class="btn-group w-100">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-filter"></i> <?= t('common.filter') ?>
                        </button>
                        <a href="/reports/financial" class="btn btn-outline-secondary">
                            <i class="fas fa-times"></i> <?= t('common.clear') ?>
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Financial Summary Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="h4 mb-0"><?= number_format($summary['gross_revenue'] ?? 0, 2) ?></div>
                            <div class="small"><?= t('reports.gross_revenue') ?></div>
                            <small class="opacity-75">
                                <?= ($summary['revenue_change'] ?? 0) >= 0 ? '+' : '' ?><?= number_format($summary['revenue_change'] ?? 0, 1) ?>% <?= t('reports.vs_previous') ?>
                            </small>
                        </div>
                        <i class="fas fa-arrow-up fa-2x opacity-75"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="h4 mb-0"><?= number_format($summary['net_profit'] ?? 0, 2) ?></div>
                            <div class="small"><?= t('reports.net_profit') ?></div>
                            <small class="opacity-75">
                                <?= number_format($summary['profit_margin'] ?? 0, 1) ?>% <?= t('reports.margin') ?>
                            </small>
                        </div>
                        <i class="fas fa-chart-pie fa-2x opacity-75"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="h4 mb-0"><?= number_format($summary['total_expenses'] ?? 0, 2) ?></div>
                            <div class="small"><?= t('reports.total_expenses') ?></div>
                            <small class="opacity-75">
                                <?= ($summary['expenses_change'] ?? 0) >= 0 ? '+' : '' ?><?= number_format($summary['expenses_change'] ?? 0, 1) ?>% <?= t('reports.vs_previous') ?>
                            </small>
                        </div>
                        <i class="fas fa-arrow-down fa-2x opacity-75"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="h4 mb-0"><?= number_format($summary['outstanding_receivables'] ?? 0, 2) ?></div>
                            <div class="small"><?= t('reports.outstanding_receivables') ?></div>
                            <small class="opacity-75">
                                <?= number_format($summary['avg_collection_days'] ?? 0, 0) ?> <?= t('reports.avg_days') ?>
                            </small>
                        </div>
                        <i class="fas fa-clock fa-2x opacity-75"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8">
            <!-- Profit & Loss Chart -->
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><?= t('reports.profit_loss_trend') ?></h5>
                    <div class="btn-group btn-group-sm">
                        <button type="button" class="btn btn-outline-primary active" onclick="updateChart('profit')"><?= t('reports.profit') ?></button>
                        <button type="button" class="btn btn-outline-primary" onclick="updateChart('revenue')"><?= t('reports.revenue') ?></button>
                        <button type="button" class="btn btn-outline-primary" onclick="updateChart('expenses')"><?= t('reports.expenses') ?></button>
                    </div>
                </div>
                <div class="card-body">
                    <canvas id="profitLossChart" height="300"></canvas>
                </div>
            </div>

            <!-- Profit & Loss Statement -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><?= t('reports.profit_loss_statement') ?></h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th><?= t('reports.account') ?></th>
                                    <th class="text-end"><?= t('reports.current_period') ?></th>
                                    <th class="text-end"><?= t('reports.previous_period') ?></th>
                                    <th class="text-end"><?= t('reports.change') ?></th>
                                    <th class="text-end">% <?= t('reports.change') ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Revenue Section -->
                                <tr class="table-primary">
                                    <td><strong><?= t('reports.revenue') ?></strong></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                </tr>
                                <tr>
                                    <td class="ps-4"><?= t('reports.sales_revenue') ?></td>
                                    <td class="text-end"><?= number_format($pl_data['sales_revenue'] ?? 0, 2) ?></td>
                                    <td class="text-end"><?= number_format($pl_data['prev_sales_revenue'] ?? 0, 2) ?></td>
                                    <td class="text-end text-<?= ($pl_data['sales_revenue_change'] ?? 0) >= 0 ? 'success' : 'danger' ?>">
                                        <?= number_format($pl_data['sales_revenue_change'] ?? 0, 2) ?>
                                    </td>
                                    <td class="text-end text-<?= ($pl_data['sales_revenue_pct'] ?? 0) >= 0 ? 'success' : 'danger' ?>">
                                        <?= number_format($pl_data['sales_revenue_pct'] ?? 0, 1) ?>%
                                    </td>
                                </tr>
                                <tr>
                                    <td class="ps-4"><?= t('reports.other_income') ?></td>
                                    <td class="text-end"><?= number_format($pl_data['other_income'] ?? 0, 2) ?></td>
                                    <td class="text-end"><?= number_format($pl_data['prev_other_income'] ?? 0, 2) ?></td>
                                    <td class="text-end text-<?= ($pl_data['other_income_change'] ?? 0) >= 0 ? 'success' : 'danger' ?>">
                                        <?= number_format($pl_data['other_income_change'] ?? 0, 2) ?>
                                    </td>
                                    <td class="text-end text-<?= ($pl_data['other_income_pct'] ?? 0) >= 0 ? 'success' : 'danger' ?>">
                                        <?= number_format($pl_data['other_income_pct'] ?? 0, 1) ?>%
                                    </td>
                                </tr>
                                <tr class="table-success">
                                    <td><strong><?= t('reports.total_revenue') ?></strong></td>
                                    <td class="text-end"><strong><?= number_format($pl_data['total_revenue'] ?? 0, 2) ?></strong></td>
                                    <td class="text-end"><strong><?= number_format($pl_data['prev_total_revenue'] ?? 0, 2) ?></strong></td>
                                    <td class="text-end"><strong><?= number_format($pl_data['total_revenue_change'] ?? 0, 2) ?></strong></td>
                                    <td class="text-end"><strong><?= number_format($pl_data['total_revenue_pct'] ?? 0, 1) ?>%</strong></td>
                                </tr>
                                
                                <!-- Expenses Section -->
                                <tr class="table-danger">
                                    <td><strong><?= t('reports.cost_of_goods_sold') ?></strong></td>
                                    <td class="text-end"><?= number_format($pl_data['cogs'] ?? 0, 2) ?></td>
                                    <td class="text-end"><?= number_format($pl_data['prev_cogs'] ?? 0, 2) ?></td>
                                    <td class="text-end"><?= number_format($pl_data['cogs_change'] ?? 0, 2) ?></td>
                                    <td class="text-end"><?= number_format($pl_data['cogs_pct'] ?? 0, 1) ?>%</td>
                                </tr>
                                <tr class="table-info">
                                    <td><strong><?= t('reports.gross_profit') ?></strong></td>
                                    <td class="text-end"><strong><?= number_format($pl_data['gross_profit'] ?? 0, 2) ?></strong></td>
                                    <td class="text-end"><strong><?= number_format($pl_data['prev_gross_profit'] ?? 0, 2) ?></strong></td>
                                    <td class="text-end"><strong><?= number_format($pl_data['gross_profit_change'] ?? 0, 2) ?></strong></td>
                                    <td class="text-end"><strong><?= number_format($pl_data['gross_profit_pct'] ?? 0, 1) ?>%</strong></td>
                                </tr>
                                
                                <!-- Operating Expenses -->
                                <tr class="table-warning">
                                    <td><strong><?= t('reports.operating_expenses') ?></strong></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                </tr>
                                <tr>
                                    <td class="ps-4"><?= t('reports.salaries_wages') ?></td>
                                    <td class="text-end"><?= number_format($pl_data['salaries'] ?? 0, 2) ?></td>
                                    <td class="text-end"><?= number_format($pl_data['prev_salaries'] ?? 0, 2) ?></td>
                                    <td class="text-end"><?= number_format($pl_data['salaries_change'] ?? 0, 2) ?></td>
                                    <td class="text-end"><?= number_format($pl_data['salaries_pct'] ?? 0, 1) ?>%</td>
                                </tr>
                                <tr>
                                    <td class="ps-4"><?= t('reports.rent_utilities') ?></td>
                                    <td class="text-end"><?= number_format($pl_data['rent_utilities'] ?? 0, 2) ?></td>
                                    <td class="text-end"><?= number_format($pl_data['prev_rent_utilities'] ?? 0, 2) ?></td>
                                    <td class="text-end"><?= number_format($pl_data['rent_utilities_change'] ?? 0, 2) ?></td>
                                    <td class="text-end"><?= number_format($pl_data['rent_utilities_pct'] ?? 0, 1) ?>%</td>
                                </tr>
                                <tr>
                                    <td class="ps-4"><?= t('reports.other_expenses') ?></td>
                                    <td class="text-end"><?= number_format($pl_data['other_expenses'] ?? 0, 2) ?></td>
                                    <td class="text-end"><?= number_format($pl_data['prev_other_expenses'] ?? 0, 2) ?></td>
                                    <td class="text-end"><?= number_format($pl_data['other_expenses_change'] ?? 0, 2) ?></td>
                                    <td class="text-end"><?= number_format($pl_data['other_expenses_pct'] ?? 0, 1) ?>%</td>
                                </tr>
                                
                                <!-- Net Profit -->
                                <tr class="table-primary">
                                    <td><strong><?= t('reports.net_profit') ?></strong></td>
                                    <td class="text-end"><strong><?= number_format($pl_data['net_profit'] ?? 0, 2) ?></strong></td>
                                    <td class="text-end"><strong><?= number_format($pl_data['prev_net_profit'] ?? 0, 2) ?></strong></td>
                                    <td class="text-end text-<?= ($pl_data['net_profit_change'] ?? 0) >= 0 ? 'success' : 'danger' ?>">
                                        <strong><?= number_format($pl_data['net_profit_change'] ?? 0, 2) ?></strong>
                                    </td>
                                    <td class="text-end text-<?= ($pl_data['net_profit_pct'] ?? 0) >= 0 ? 'success' : 'danger' ?>">
                                        <strong><?= number_format($pl_data['net_profit_pct'] ?? 0, 1) ?>%</strong>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <!-- Expense Breakdown -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><?= t('reports.expense_breakdown') ?></h5>
                </div>
                <div class="card-body">
                    <canvas id="expenseChart" height="250"></canvas>
                </div>
            </div>

            <!-- Cash Flow Summary -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><?= t('reports.cash_flow_summary') ?></h5>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-6">
                            <div class="border rounded p-3 mb-2">
                                <div class="h6 mb-0 text-success"><?= number_format($cash_flow['cash_in'] ?? 0, 2) ?></div>
                                <small class="text-muted"><?= t('reports.cash_in') ?></small>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="border rounded p-3 mb-2">
                                <div class="h6 mb-0 text-danger"><?= number_format($cash_flow['cash_out'] ?? 0, 2) ?></div>
                                <small class="text-muted"><?= t('reports.cash_out') ?></small>
                            </div>
                        </div>
                    </div>
                    
                    <hr>
                    
                    <div class="d-flex justify-content-between align-items-center">
                        <strong><?= t('reports.net_cash_flow') ?>:</strong>
                        <strong class="text-<?= ($cash_flow['net_flow'] ?? 0) >= 0 ? 'success' : 'danger' ?>">
                            <?= number_format($cash_flow['net_flow'] ?? 0, 2) ?>
                        </strong>
                    </div>
                    
                    <small class="text-muted mt-2 d-block">
                        <?= t('reports.current_period') ?>: <?= date('M Y') ?>
                    </small>
                </div>
            </div>

            <!-- Accounts Receivable Aging -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><?= t('reports.ar_aging') ?></h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th><?= t('reports.age_range') ?></th>
                                    <th class="text-end"><?= t('reports.amount') ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>0-30 <?= t('reports.days') ?></td>
                                    <td class="text-end text-success"><?= number_format($ar_aging['0_30'] ?? 0, 2) ?></td>
                                </tr>
                                <tr>
                                    <td>31-60 <?= t('reports.days') ?></td>
                                    <td class="text-end text-warning"><?= number_format($ar_aging['31_60'] ?? 0, 2) ?></td>
                                </tr>
                                <tr>
                                    <td>61-90 <?= t('reports.days') ?></td>
                                    <td class="text-end text-danger"><?= number_format($ar_aging['61_90'] ?? 0, 2) ?></td>
                                </tr>
                                <tr>
                                    <td>>90 <?= t('reports.days') ?></td>
                                    <td class="text-end text-dark"><?= number_format($ar_aging['over_90'] ?? 0, 2) ?></td>
                                </tr>
                                <tr class="table-primary">
                                    <td><strong><?= t('reports.total') ?></strong></td>
                                    <td class="text-end"><strong><?= number_format($ar_aging['total'] ?? 0, 2) ?></strong></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Key Financial Ratios -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><?= t('reports.key_ratios') ?></h5>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-6 mb-3">
                            <div class="border rounded p-2">
                                <div class="h6 mb-0"><?= number_format($ratios['gross_margin'] ?? 0, 1) ?>%</div>
                                <small class="text-muted"><?= t('reports.gross_margin') ?></small>
                            </div>
                        </div>
                        <div class="col-6 mb-3">
                            <div class="border rounded p-2">
                                <div class="h6 mb-0"><?= number_format($ratios['net_margin'] ?? 0, 1) ?>%</div>
                                <small class="text-muted"><?= t('reports.net_margin') ?></small>
                            </div>
                        </div>
                        <div class="col-6 mb-3">
                            <div class="border rounded p-2">
                                <div class="h6 mb-0"><?= number_format($ratios['current_ratio'] ?? 0, 2) ?></div>
                                <small class="text-muted"><?= t('reports.current_ratio') ?></small>
                            </div>
                        </div>
                        <div class="col-6 mb-3">
                            <div class="border rounded p-2">
                                <div class="h6 mb-0"><?= number_format($ratios['debt_ratio'] ?? 0, 2) ?></div>
                                <small class="text-muted"><?= t('reports.debt_ratio') ?></small>
                            </div>
                        </div>
                    </div>
                    
                    <div class="d-grid">
                        <a href="/reports/financial/detailed" class="btn btn-primary btn-sm">
                            <i class="fas fa-chart-line me-2"></i><?= t('reports.detailed_analysis') ?>
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
    // Profit & Loss chart
    const ctx1 = document.getElementById('profitLossChart').getContext('2d');
    window.plChart = new Chart(ctx1, {
        type: 'line',
        data: {
            labels: <?= json_encode($chart_data['labels'] ?? []) ?>,
            datasets: [{
                label: '<?= t('reports.profit') ?>',
                data: <?= json_encode($chart_data['profit'] ?? []) ?>,
                borderColor: 'rgb(40, 167, 69)',
                backgroundColor: 'rgba(40, 167, 69, 0.1)',
                tension: 0.4,
                fill: true
            }, {
                label: '<?= t('reports.revenue') ?>',
                data: <?= json_encode($chart_data['revenue'] ?? []) ?>,
                borderColor: 'rgb(54, 162, 235)',
                backgroundColor: 'rgba(54, 162, 235, 0.1)',
                tension: 0.4,
                fill: false
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

    // Expense breakdown chart
    const ctx2 = document.getElementById('expenseChart').getContext('2d');
    new Chart(ctx2, {
        type: 'doughnut',
        data: {
            labels: [
                '<?= t('reports.cost_of_goods') ?>',
                '<?= t('reports.salaries') ?>',
                '<?= t('reports.rent_utilities') ?>',
                '<?= t('reports.other_expenses') ?>'
            ],
            datasets: [{
                data: <?= json_encode($expense_breakdown ?? [45, 30, 15, 10]) ?>,
                backgroundColor: [
                    'rgba(220, 53, 69, 0.8)',
                    'rgba(255, 193, 7, 0.8)',
                    'rgba(23, 162, 184, 0.8)',
                    'rgba(108, 117, 125, 0.8)'
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
    
    fetch(`/api/reports/financial-chart?type=${type}&${currentFilters.toString()}`)
    .then(response => response.json())
    .then(data => {
        // Update chart based on type
        if (type === 'profit') {
            window.plChart.data.datasets[0].data = data.profit;
            window.plChart.data.datasets[1].data = data.revenue;
        } else if (type === 'revenue') {
            window.plChart.data.datasets[0].data = data.revenue;
            window.plChart.data.datasets[1].data = [];
        } else if (type === 'expenses') {
            window.plChart.data.datasets[0].data = data.expenses;
            window.plChart.data.datasets[1].data = [];
        }
        window.plChart.update();
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

.table-primary {
    background-color: rgba(13, 110, 253, 0.1);
}

.table-success {
    background-color: rgba(25, 135, 84, 0.1);
}

.table-danger {
    background-color: rgba(220, 53, 69, 0.1);
}

.table-info {
    background-color: rgba(13, 202, 240, 0.1);
}

.table-warning {
    background-color: rgba(255, 193, 7, 0.1);
}

@media (max-width: 768px) {
    .col-md-3, .col-md-2 {
        margin-bottom: 1rem;
    }
}
</style>