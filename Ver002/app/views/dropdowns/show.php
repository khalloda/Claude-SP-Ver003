<?php
/**
 * File: app/views/dropdowns/show.php
 * Purpose: Dropdown item detail view with usage analytics and comprehensive information
 * Layout: Uses app layout with professional presentation
 */

$this->layout('layouts/app', [
    'title' => $page_title ?? t('dropdowns.item_details'),
    'active_nav' => 'dropdowns'
]);

$currentUser = $this->getCurrentUser();
$canEdit = $this->hasRole(['admin', 'manager']);
$canDelete = $this->hasRole(['admin']);
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">
            <i class="fas fa-<?= $this->getTypeIcon($dropdown->type) ?> me-2"></i>
            <?= htmlspecialchars($dropdown->name) ?>
            <span class="badge bg-primary ms-2"><?= t('dropdowns.' . $dropdown->type) ?></span>
            <span class="badge bg-<?= $dropdown->is_active ? 'success' : 'secondary' ?> ms-1">
                <?= $dropdown->is_active ? t('common.active') : t('common.inactive') ?>
            </span>
        </h1>
        <div class="btn-toolbar mb-2 mb-md-0">
            <div class="btn-group me-2">
                <?php if ($canEdit): ?>
                <a href="/dropdowns/<?= $dropdown->id ?>/edit" class="btn btn-primary">
                    <i class="fas fa-edit"></i> <?= t('common.edit') ?>
                </a>
                <?php endif; ?>
                <a href="/dropdowns" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left"></i> <?= t('common.back') ?>
                </a>
            </div>
            <div class="dropdown">
                <button class="btn btn-outline-info dropdown-toggle" type="button" data-bs-toggle="dropdown">
                    <i class="fas fa-ellipsis-v"></i>
                </button>
                <ul class="dropdown-menu">
                    <li><a class="dropdown-item" href="/dropdowns?type=<?= $dropdown->type ?>">
                        <i class="fas fa-list me-2"></i><?= t('dropdowns.view_all_type', ['type' => t('dropdowns.' . $dropdown->type . 's')]) ?>
                    </a></li>
                    <li><a class="dropdown-item" href="#" onclick="showUsageAnalytics()">
                        <i class="fas fa-chart-bar me-2"></i><?= t('dropdowns.usage_analytics') ?>
                    </a></li>
                    <li><a class="dropdown-item" href="#" onclick="exportUsageData()">
                        <i class="fas fa-download me-2"></i><?= t('dropdowns.export_usage') ?>
                    </a></li>
                    <?php if ($canEdit): ?>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item" href="/dropdowns/create?type=<?= $dropdown->type ?>">
                        <i class="fas fa-plus me-2"></i><?= t('dropdowns.add_similar') ?>
                    </a></li>
                    <li><a class="dropdown-item" href="/dropdowns/create?duplicate_from=<?= $dropdown->id ?>">
                        <i class="fas fa-copy me-2"></i><?= t('dropdowns.duplicate') ?>
                    </a></li>
                    <?php endif; ?>
                    <?php if ($canDelete && ($dropdown->usage_count ?? 0) == 0): ?>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item text-danger" href="#" onclick="deleteItem()">
                        <i class="fas fa-trash me-2"></i><?= t('common.delete') ?>
                    </a></li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Main Information -->
        <div class="col-lg-8">
            <!-- Basic Details Card -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0"><?= t('dropdowns.basic_information') ?></h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <td class="fw-medium"><?= t('dropdowns.type') ?>:</td>
                                    <td>
                                        <span class="badge bg-primary fs-6">
                                            <i class="fas fa-<?= $this->getTypeIcon($dropdown->type) ?> me-2"></i>
                                            <?= t('dropdowns.' . $dropdown->type) ?>
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="fw-medium"><?= t('common.name') ?>:</td>
                                    <td><?= htmlspecialchars($dropdown->name) ?></td>
                                </tr>
                                <?php if (!empty($dropdown->code)): ?>
                                <tr>
                                    <td class="fw-medium"><?= t('dropdowns.code') ?>:</td>
                                    <td><code><?= htmlspecialchars($dropdown->code) ?></code></td>
                                </tr>
                                <?php endif; ?>
                                <tr>
                                    <td class="fw-medium"><?= t('dropdowns.sort_order') ?>:</td>
                                    <td><?= $dropdown->sort_order ?? 0 ?></td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <td class="fw-medium"><?= t('common.status') ?>:</td>
                                    <td>
                                        <span class="badge bg-<?= $dropdown->is_active ? 'success' : 'secondary' ?>">
                                            <?= $dropdown->is_active ? t('common.active') : t('common.inactive') ?>
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="fw-medium"><?= t('dropdowns.usage_count') ?>:</td>
                                    <td>
                                        <span class="badge bg-info"><?= $dropdown->usage_count ?? 0 ?></span>
                                        <?= t('dropdowns.total_references') ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="fw-medium"><?= t('dropdowns.created') ?>:</td>
                                    <td><?= date('M j, Y', strtotime($dropdown->created_at)) ?></td>
                                </tr>
                                <tr>
                                    <td class="fw-medium"><?= t('dropdowns.last_modified') ?>:</td>
                                    <td><?= date('M j, Y', strtotime($dropdown->updated_at)) ?></td>
                                </tr>
                            </table>
                        </div>
                    </div>
                    
                    <?php if (!empty($dropdown->description)): ?>
                    <hr>
                    <div>
                        <strong><?= t('common.description') ?>:</strong>
                        <p class="mt-2 mb-0"><?= nl2br(htmlspecialchars($dropdown->description)) ?></p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Type-Specific Information -->
            <?php if ($dropdown->type !== 'country' || !empty($dropdown->iso_code) || !empty($dropdown->phone_code)): ?>
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0"><?= t('dropdowns.' . $dropdown->type . '_details') ?></h5>
                </div>
                <div class="card-body">
                    <?php if ($dropdown->type === 'category'): ?>
                    <!-- Category-specific details -->
                    <div class="row">
                        <?php if (!empty($dropdown->parent_id)): ?>
                        <div class="col-md-6">
                            <strong><?= t('dropdowns.parent_category') ?>:</strong>
                            <p><a href="/dropdowns/<?= $dropdown->parent_id ?>"><?= htmlspecialchars($dropdown->parent_name ?? 'Unknown') ?></a></p>
                        </div>
                        <?php endif; ?>
                        <?php if (!empty($dropdown->color)): ?>
                        <div class="col-md-6">
                            <strong><?= t('dropdowns.color') ?>:</strong>
                            <p>
                                <span class="badge me-2" style="background-color: <?= htmlspecialchars($dropdown->color) ?>;">&nbsp;&nbsp;&nbsp;</span>
                                <?= htmlspecialchars($dropdown->color) ?>
                            </p>
                        </div>
                        <?php endif; ?>
                    </div>
                    
                    <?php if (!empty($dropdown->subcategories_count)): ?>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        <?= t('dropdowns.has_subcategories', ['count' => $dropdown->subcategories_count]) ?>
                    </div>
                    <?php endif; ?>
                    
                    <?php elseif ($dropdown->type === 'unit'): ?>
                    <!-- Unit-specific details -->
                    <div class="row">
                        <?php if (!empty($dropdown->symbol)): ?>
                        <div class="col-md-4">
                            <strong><?= t('dropdowns.symbol') ?>:</strong>
                            <p class="fs-4"><?= htmlspecialchars($dropdown->symbol) ?></p>
                        </div>
                        <?php endif; ?>
                        <?php if (!empty($dropdown->base_unit)): ?>
                        <div class="col-md-4">
                            <strong><?= t('dropdowns.base_unit') ?>:</strong>
                            <p><?= htmlspecialchars($dropdown->base_unit) ?></p>
                        </div>
                        <?php endif; ?>
                        <?php if (isset($dropdown->conversion_factor)): ?>
                        <div class="col-md-4">
                            <strong><?= t('dropdowns.conversion_factor') ?>:</strong>
                            <p><?= number_format($dropdown->conversion_factor, 3) ?></p>
                        </div>
                        <?php endif; ?>
                    </div>
                    
                    <?php elseif ($dropdown->type === 'status'): ?>
                    <!-- Status-specific details -->
                    <div class="row">
                        <?php if (!empty($dropdown->color)): ?>
                        <div class="col-md-6">
                            <strong><?= t('dropdowns.color') ?>:</strong>
                            <p>
                                <span class="badge" style="background-color: <?= htmlspecialchars($dropdown->color) ?>;">
                                    <?= htmlspecialchars($dropdown->color) ?>
                                </span>
                            </p>
                        </div>
                        <?php endif; ?>
                        <?php if (!empty($dropdown->status_type)): ?>
                        <div class="col-md-6">
                            <strong><?= t('dropdowns.status_type') ?>:</strong>
                            <p>
                                <span class="badge bg-<?= $dropdown->status_type === 'active' ? 'success' : 'secondary' ?>">
                                    <?= t('dropdowns.' . $dropdown->status_type . '_status') ?>
                                </span>
                            </p>
                        </div>
                        <?php endif; ?>
                    </div>
                    
                    <?php elseif ($dropdown->type === 'priority'): ?>
                    <!-- Priority-specific details -->
                    <div class="row">
                        <?php if (isset($dropdown->level)): ?>
                        <div class="col-md-6">
                            <strong><?= t('dropdowns.priority_level') ?>:</strong>
                            <p>
                                <span class="badge bg-info"><?= $dropdown->level ?></span>
                                <?= t('dropdowns.level_' . $dropdown->level) ?>
                            </p>
                        </div>
                        <?php endif; ?>
                        <?php if (!empty($dropdown->color)): ?>
                        <div class="col-md-6">
                            <strong><?= t('dropdowns.color') ?>:</strong>
                            <p>
                                <span class="badge" style="background-color: <?= htmlspecialchars($dropdown->color) ?>;">
                                    <?= htmlspecialchars($dropdown->color) ?>
                                </span>
                            </p>
                        </div>
                        <?php endif; ?>
                    </div>
                    
                    <?php elseif ($dropdown->type === 'country'): ?>
                    <!-- Country-specific details -->
                    <div class="row">
                        <?php if (!empty($dropdown->iso_code)): ?>
                        <div class="col-md-4">
                            <strong><?= t('dropdowns.iso_code') ?>:</strong>
                            <p><code><?= htmlspecialchars($dropdown->iso_code) ?></code></p>
                        </div>
                        <?php endif; ?>
                        <?php if (!empty($dropdown->phone_code)): ?>
                        <div class="col-md-4">
                            <strong><?= t('dropdowns.phone_code') ?>:</strong>
                            <p><code><?= htmlspecialchars($dropdown->phone_code) ?></code></p>
                        </div>
                        <?php endif; ?>
                        <?php if (!empty($dropdown->currency_code)): ?>
                        <div class="col-md-4">
                            <strong><?= t('dropdowns.currency_code') ?>:</strong>
                            <p><code><?= htmlspecialchars($dropdown->currency_code) ?></code></p>
                        </div>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- Usage Analytics -->
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0"><?= t('dropdowns.usage_analytics') ?></h5>
                    <?php if (($dropdown->usage_count ?? 0) > 0): ?>
                    <button class="btn btn-sm btn-outline-primary" onclick="showUsageAnalytics()">
                        <i class="fas fa-chart-bar"></i> <?= t('dropdowns.detailed_analytics') ?>
                    </button>
                    <?php endif; ?>
                </div>
                <div class="card-body">
                    <?php if (($dropdown->usage_count ?? 0) > 0): ?>
                    <div class="row text-center mb-4">
                        <div class="col-md-3">
                            <div class="h4 text-primary mb-1"><?= $stats['total_usage'] ?? 0 ?></div>
                            <div class="small text-muted"><?= t('dropdowns.total_references') ?></div>
                        </div>
                        <div class="col-md-3">
                            <div class="h4 text-success mb-1"><?= $stats['active_usage'] ?? 0 ?></div>
                            <div class="small text-muted"><?= t('dropdowns.active_records') ?></div>
                        </div>
                        <div class="col-md-3">
                            <div class="h4 text-info mb-1"><?= $stats['this_month'] ?? 0 ?></div>
                            <div class="small text-muted"><?= t('dropdowns.this_month') ?></div>
                        </div>
                        <div class="col-md-3">
                            <div class="h4 text-warning mb-1"><?= $stats['last_30_days'] ?? 0 ?></div>
                            <div class="small text-muted"><?= t('dropdowns.last_30_days') ?></div>
                        </div>
                    </div>

                    <?php if (!empty($usageByModule)): ?>
                    <h6><?= t('dropdowns.usage_by_module') ?></h6>
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th><?= t('dropdowns.module') ?></th>
                                    <th class="text-end"><?= t('dropdowns.count') ?></th>
                                    <th class="text-end"><?= t('dropdowns.percentage') ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($usageByModule as $module): ?>
                                <tr>
                                    <td>
                                        <i class="fas fa-<?= $this->getModuleIcon($module->module) ?> me-2"></i>
                                        <?= t('nav.' . $module->module) ?>
                                    </td>
                                    <td class="text-end"><?= $module->count ?></td>
                                    <td class="text-end">
                                        <div class="progress" style="width: 60px; height: 6px;">
                                            <div class="progress-bar" role="progressbar" 
                                                 style="width: <?= ($module->count / $stats['total_usage']) * 100 ?>%">
                                            </div>
                                        </div>
                                        <?= number_format(($module->count / $stats['total_usage']) * 100, 1) ?>%
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php endif; ?>
                    <?php else: ?>
                    <div class="text-center py-4">
                        <i class="fas fa-chart-bar fa-3x text-muted mb-3"></i>
                        <h5><?= t('dropdowns.no_usage_data') ?></h5>
                        <p class="text-muted"><?= t('dropdowns.no_usage_desc') ?></p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Usage Examples -->
            <?php if (!empty($usageExamples)): ?>
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0"><?= t('dropdowns.usage_examples') ?></h5>
                    <a href="/dropdowns/<?= $dropdown->id ?>/usage" class="btn btn-sm btn-outline-primary">
                        <?= t('dropdowns.view_all') ?>
                    </a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th><?= t('dropdowns.entity_type') ?></th>
                                    <th><?= t('dropdowns.entity_name') ?></th>
                                    <th><?= t('common.date') ?></th>
                                    <th><?= t('common.actions') ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach (array_slice($usageExamples, 0, 10) as $example): ?>
                                <tr>
                                    <td>
                                        <i class="fas fa-<?= $this->getModuleIcon($example->entity_type) ?> me-2"></i>
                                        <?= t('nav.' . $example->entity_type) ?>
                                    </td>
                                    <td>
                                        <a href="<?= $example->url ?>" class="text-decoration-none">
                                            <?= htmlspecialchars($example->entity_name) ?>
                                        </a>
                                    </td>
                                    <td>
                                        <span class="text-muted"><?= date('M j, Y', strtotime($example->created_at)) ?></span>
                                    </td>
                                    <td>
                                        <a href="<?= $example->url ?>" class="btn btn-sm btn-outline-info">
                                            <i class="fas fa-external-link-alt"></i>
                                        </a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Quick Stats -->
            <div class="card mb-3">
                <div class="card-header">
                    <h6 class="card-title mb-0">
                        <i class="fas fa-chart-pie me-2"></i><?= t('dropdowns.quick_stats') ?>
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-6">
                            <div class="border-end">
                                <div class="h5 text-primary mb-1"><?= $dropdown->usage_count ?? 0 ?></div>
                                <div class="small text-muted"><?= t('dropdowns.total_usage') ?></div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="h5 text-success mb-1"><?= date('M j, Y', strtotime($dropdown->created_at)) ?></div>
                            <div class="small text-muted"><?= t('dropdowns.created') ?></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="card mb-3">
                <div class="card-header">
                    <h6 class="card-title mb-0">
                        <i class="fas fa-bolt me-2"></i><?= t('common.quick_actions') ?>
                    </h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <?php if ($canEdit): ?>
                        <a href="/dropdowns/<?= $dropdown->id ?>/edit" class="btn btn-outline-primary btn-sm">
                            <i class="fas fa-edit"></i> <?= t('common.edit_item') ?>
                        </a>
                        <?php endif; ?>
                        <a href="/dropdowns?type=<?= $dropdown->type ?>" class="btn btn-outline-info btn-sm">
                            <i class="fas fa-list"></i> <?= t('dropdowns.view_all_type', ['type' => t('dropdowns.' . $dropdown->type . 's')]) ?>
                        </a>
                        <button class="btn btn-outline-success btn-sm" onclick="showUsageAnalytics()">
                            <i class="fas fa-chart-bar"></i> <?= t('dropdowns.usage_analytics') ?>
                        </button>
                        <button class="btn btn-outline-secondary btn-sm" onclick="exportUsageData()">
                            <i class="fas fa-download"></i> <?= t('dropdowns.export_data') ?>
                        </button>
                        <?php if ($canEdit): ?>
                        <a href="/dropdowns/create?type=<?= $dropdown->type ?>" class="btn btn-outline-warning btn-sm">
                            <i class="fas fa-plus"></i> <?= t('dropdowns.add_similar') ?>
                        </a>
                        <a href="/dropdowns/create?duplicate_from=<?= $dropdown->id ?>" class="btn btn-outline-warning btn-sm">
                            <i class="fas fa-copy"></i> <?= t('dropdowns.duplicate') ?>
                        </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- System Information -->
            <div class="card">
                <div class="card-header">
                    <h6 class="card-title mb-0">
                        <i class="fas fa-info-circle me-2"></i><?= t('dropdowns.system_info') ?>
                    </h6>
                </div>
                <div class="card-body">
                    <div class="small">
                        <div class="d-flex justify-content-between mb-2">
                            <span><?= t('dropdowns.created') ?>:</span>
                            <span class="text-muted"><?= date('M j, Y g:i A', strtotime($dropdown->created_at)) ?></span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span><?= t('dropdowns.last_modified') ?>:</span>
                            <span class="text-muted"><?= date('M j, Y g:i A', strtotime($dropdown->updated_at)) ?></span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span><?= t('dropdowns.created_by') ?>:</span>
                            <span class="text-muted"><?= htmlspecialchars($dropdown->created_by_name ?? t('common.system')) ?></span>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span><?= t('common.id') ?>:</span>
                            <span class="text-muted font-monospace"><?= $dropdown->id ?></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Usage Analytics Modal -->
<div class="modal fade" id="usageAnalyticsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><?= t('dropdowns.usage_analytics') ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="analyticsContent">
                    <div class="text-center py-3">
                        <i class="fas fa-spinner fa-spin"></i> <?= t('common.loading') ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Show usage analytics
async function showUsageAnalytics() {
    const modal = new bootstrap.Modal(document.getElementById('usageAnalyticsModal'));
    modal.show();
    
    try {
        const response = await fetch('/api/dropdowns/<?= $dropdown->id ?>/analytics');
        const data = await response.json();
        
        let content = '<div class="row text-center mb-4">';
        content += '<div class="col-3"><div class="h4 text-primary">' + (data.total || 0) + '</div><div class="small">Total</div></div>';
        content += '<div class="col-3"><div class="h4 text-success">' + (data.active || 0) + '</div><div class="small">Active</div></div>';
        content += '<div class="col-3"><div class="h4 text-info">' + (data.month || 0) + '</div><div class="small">This Month</div></div>';
        content += '<div class="col-3"><div class="h4 text-warning">' + (data.week || 0) + '</div><div class="small">This Week</div></div>';
        content += '</div>';
        
        if (data.timeline && data.timeline.length > 0) {
            content += '<h6><?= t('dropdowns.usage_timeline') ?></h6>';
            content += '<canvas id="usageChart" height="200"></canvas>';
        } else {
            content += '<div class="alert alert-info"><?= t('dropdowns.no_analytics_data') ?></div>';
        }
        
        document.getElementById('analyticsContent').innerHTML = content;
        
        // Initialize chart if data exists
        if (data.timeline && data.timeline.length > 0) {
            const ctx = document.getElementById('usageChart').getContext('2d');
            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: data.timeline.map(d => d.date),
                    datasets: [{
                        label: '<?= t('dropdowns.usage') ?>',
                        data: data.timeline.map(d => d.count),
                        borderColor: 'rgb(75, 192, 192)',
                        backgroundColor: 'rgba(75, 192, 192, 0.1)',
                        tension: 0.4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: { precision: 0 }
                        }
                    },
                    plugins: {
                        legend: { display: false }
                    }
                }
            });
        }
        
    } catch (error) {
        document.getElementById('analyticsContent').innerHTML = 
            '<div class="alert alert-danger"><?= t('dropdowns.analytics_load_error') ?></div>';
    }
}

// Export usage data
function exportUsageData() {
    const button = event.target;
    const originalText = button.textContent;
    button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> ' + originalText;
    button.disabled = true;
    
    window.location.href = '/api/dropdowns/<?= $dropdown->id ?>/export?format=csv';
    
    setTimeout(() => {
        button.innerHTML = '<i class="fas fa-download"></i> <?= t('dropdowns.export_data') ?>';
        button.disabled = false;
    }, 2000);
}

// Delete item
function deleteItem() {
    if (confirm('<?= t('dropdowns.confirm_delete') ?>')) {
        fetch('/dropdowns/<?= $dropdown->id ?>', {
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
                setTimeout(() => window.location.href = '/dropdowns', 1500);
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

<?php
// Helper functions for icons and module names
$this->extend('getTypeIcon', function($type) {
    $icons = [
        'category' => 'tags',
        'unit' => 'balance-scale',
        'status' => 'circle',
        'priority' => 'exclamation',
        'country' => 'globe'
    ];
    return $icons[$type] ?? 'list-ul';
});

$this->extend('getModuleIcon', function($module) {
    $icons = [
        'products' => 'box',
        'clients' => 'users',
        'suppliers' => 'truck',
        'warehouses' => 'warehouse',
        'quotes' => 'file-alt',
        'salesorders' => 'shopping-cart',
        'invoices' => 'file-invoice',
        'payments' => 'credit-card'
    ];
    return $icons[$module] ?? 'circle';
});
?>
</script>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<style>
.table-borderless td {
    padding: 0.5rem 0;
}

.border-end:last-child {
    border-right: none !important;
}

.progress {
    display: inline-block;
    vertical-align: middle;
}

@media (max-width: 768px) {
    .border-end {
        border-right: none !important;
        border-bottom: 1px solid #dee2e6;
        margin-bottom: 1rem;
        padding-bottom: 1rem;
    }
    
    .border-end:last-child {
        border-bottom: none !important;
        margin-bottom: 0;
        padding-bottom: 0;
    }
}
</style>