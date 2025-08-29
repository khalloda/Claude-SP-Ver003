<?php
/**
 * File: app/views/users/show.php
 * Purpose: User profile detail view with comprehensive activity tracking
 * Layout: Uses app layout with professional user presentation
 */

$this->layout('layouts/app', [
    'title' => $page_title ?? t('users.user_details'),
    'active_nav' => 'users'
]);

$currentUser = $this->getCurrentUser();
$canEdit = $this->hasRole(['admin', 'manager']) || ($currentUser && $currentUser->id === $user->id);
$canDelete = $this->hasRole(['admin']) && $user->id !== $currentUser->id;
$canManageRoles = $this->hasRole(['admin']);
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">
            <i class="fas fa-user me-2"></i><?= t('users.user_profile') ?>
        </h1>
        <div class="btn-toolbar mb-2 mb-md-0">
            <div class="btn-group me-2">
                <button type="button" class="btn btn-outline-primary dropdown-toggle" data-bs-toggle="dropdown">
                    <i class="fas fa-cog"></i> <?= t('common.actions') ?>
                </button>
                <ul class="dropdown-menu">
                    <?php if ($canEdit): ?>
                    <li><a class="dropdown-item" href="/users/<?= $user->id ?>/edit">
                        <i class="fas fa-edit me-2"></i><?= t('common.edit_profile') ?>
                    </a></li>
                    <?php endif; ?>
                    <?php if ($canManageRoles): ?>
                    <li><a class="dropdown-item" href="#" onclick="showRoleModal()">
                        <i class="fas fa-user-tag me-2"></i><?= t('users.manage_roles') ?>
                    </a></li>
                    <li><a class="dropdown-item" href="#" onclick="resetPassword(<?= $user->id ?>)">
                        <i class="fas fa-key me-2"></i><?= t('users.reset_password') ?>
                    </a></li>
                    <?php endif; ?>
                    <?php if ($user->status === 'active'): ?>
                    <li><a class="dropdown-item" href="#" onclick="toggleUserStatus(<?= $user->id ?>, 'inactive')">
                        <i class="fas fa-user-slash me-2"></i><?= t('users.deactivate_user') ?>
                    </a></li>
                    <?php else: ?>
                    <li><a class="dropdown-item" href="#" onclick="toggleUserStatus(<?= $user->id ?>, 'active')">
                        <i class="fas fa-user-check me-2"></i><?= t('users.activate_user') ?>
                    </a></li>
                    <?php endif; ?>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item" href="/users/<?= $user->id ?>/activity-log">
                        <i class="fas fa-history me-2"></i><?= t('users.view_activity_log') ?>
                    </a></li>
                    <li><a class="dropdown-item" href="mailto:<?= htmlspecialchars($user->email) ?>">
                        <i class="fas fa-envelope me-2"></i><?= t('users.send_email') ?>
                    </a></li>
                </ul>
            </div>
            
            <?php if ($canEdit): ?>
            <a href="/users/<?= $user->id ?>/edit" class="btn btn-primary me-2">
                <i class="fas fa-edit"></i> <?= t('common.edit') ?>
            </a>
            <?php endif; ?>
            
            <?php if ($canDelete): ?>
            <button class="btn btn-outline-danger me-2" onclick="deleteUser(<?= $user->id ?>)">
                <i class="fas fa-trash"></i> <?= t('common.delete') ?>
            </button>
            <?php endif; ?>
            
            <a href="/users" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left"></i> <?= t('common.back') ?>
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8">
            <!-- User Information -->
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><?= t('users.user_information') ?></h5>
                    <div>
                        <?php 
                        $statusClass = match($user->status) {
                            'active' => 'bg-success',
                            'inactive' => 'bg-secondary',
                            'suspended' => 'bg-danger',
                            'pending' => 'bg-warning text-dark',
                            default => 'bg-secondary'
                        };
                        ?>
                        <span class="badge <?= $statusClass ?> me-2">
                            <?= t('users.status.' . $user->status) ?>
                        </span>
                        
                        <?php if ($user->is_online): ?>
                        <span class="badge bg-success">
                            <i class="fas fa-circle"></i> <?= t('users.online') ?>
                        </span>
                        <?php elseif ($user->last_login): ?>
                        <span class="badge bg-secondary">
                            <i class="fas fa-clock"></i> <?= t('users.last_seen') ?> <?= date('M d, H:i', strtotime($user->last_login)) ?>
                        </span>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row align-items-center mb-4">
                        <div class="col-auto">
                            <?php if ($user->avatar): ?>
                            <img src="<?= htmlspecialchars($user->avatar) ?>" alt="Avatar" class="rounded-circle" style="width: 100px; height: 100px; object-fit: cover;">
                            <?php else: ?>
                            <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 100px; height: 100px;">
                                <i class="fas fa-user fa-3x"></i>
                            </div>
                            <?php endif; ?>
                        </div>
                        <div class="col">
                            <h4 class="mb-1"><?= htmlspecialchars($user->first_name . ' ' . $user->last_name) ?></h4>
                            <p class="text-muted mb-1">
                                <i class="fas fa-envelope me-2"></i><?= htmlspecialchars($user->email) ?>
                            </p>
                            <?php if ($user->phone): ?>
                            <p class="text-muted mb-1">
                                <i class="fas fa-phone me-2"></i><?= htmlspecialchars($user->phone) ?>
                            </p>
                            <?php endif; ?>
                            <div class="mt-2">
                                <?php if (!empty($user->roles)): ?>
                                <?php foreach ($user->roles as $role): ?>
                                <span class="badge bg-primary me-1">
                                    <?= htmlspecialchars(ucfirst($role)) ?>
                                </span>
                                <?php endforeach; ?>
                                <?php else: ?>
                                <span class="badge bg-secondary"><?= t('users.no_roles') ?></span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <td><strong><?= t('users.username') ?>:</strong></td>
                                    <td><?= htmlspecialchars($user->username) ?></td>
                                </tr>
                                <tr>
                                    <td><strong><?= t('users.employee_id') ?>:</strong></td>
                                    <td><?= htmlspecialchars($user->employee_id ?? t('common.not_specified')) ?></td>
                                </tr>
                                <tr>
                                    <td><strong><?= t('users.department') ?>:</strong></td>
                                    <td><?= htmlspecialchars($user->department ?? t('common.not_specified')) ?></td>
                                </tr>
                                <tr>
                                    <td><strong><?= t('users.job_title') ?>:</strong></td>
                                    <td><?= htmlspecialchars($user->job_title ?? t('common.not_specified')) ?></td>
                                </tr>
                                <?php if ($user->manager_id): ?>
                                <tr>
                                    <td><strong><?= t('users.manager') ?>:</strong></td>
                                    <td>
                                        <a href="/users/<?= $user->manager_id ?>" class="text-decoration-none">
                                            <?= htmlspecialchars($user->manager_name ?? 'Manager') ?>
                                        </a>
                                    </td>
                                </tr>
                                <?php endif; ?>
                            </table>
                        </div>
                        
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <td><strong><?= t('users.date_joined') ?>:</strong></td>
                                    <td><?= date('M d, Y', strtotime($user->created_at)) ?></td>
                                </tr>
                                <tr>
                                    <td><strong><?= t('users.last_login') ?>:</strong></td>
                                    <td>
                                        <?php if ($user->last_login): ?>
                                        <?= date('M d, Y H:i', strtotime($user->last_login)) ?>
                                        <?php else: ?>
                                        <span class="text-muted"><?= t('users.never_logged_in') ?></span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong><?= t('users.timezone') ?>:</strong></td>
                                    <td><?= htmlspecialchars($user->timezone ?? 'UTC') ?></td>
                                </tr>
                                <tr>
                                    <td><strong><?= t('users.language') ?>:</strong></td>
                                    <td><?= $user->language === 'ar' ? 'العربية' : 'English' ?></td>
                                </tr>
                                <tr>
                                    <td><strong><?= t('users.two_factor') ?>:</strong></td>
                                    <td>
                                        <?php if ($user->two_factor_enabled): ?>
                                        <span class="badge bg-success"><?= t('users.enabled') ?></span>
                                        <?php else: ?>
                                        <span class="badge bg-secondary"><?= t('users.disabled') ?></span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>
                    
                    <!-- Biography/Notes -->
                    <?php if ($user->bio): ?>
                    <hr>
                    <div class="row">
                        <div class="col-md-12">
                            <h6><i class="fas fa-info-circle me-2"></i><?= t('users.biography') ?></h6>
                            <p class="text-muted"><?= nl2br(htmlspecialchars($user->bio)) ?></p>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Tabbed Content -->
            <div class="card">
                <div class="card-header">
                    <ul class="nav nav-tabs card-header-tabs" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" data-bs-toggle="tab" href="#activity" role="tab">
                                <i class="fas fa-chart-line me-1"></i><?= t('users.activity') ?>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-bs-toggle="tab" href="#permissions" role="tab">
                                <i class="fas fa-shield-alt me-1"></i><?= t('users.permissions') ?>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-bs-toggle="tab" href="#sessions" role="tab">
                                <i class="fas fa-desktop me-1"></i><?= t('users.sessions') ?>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-bs-toggle="tab" href="#audit-log" role="tab">
                                <i class="fas fa-history me-1"></i><?= t('users.audit_log') ?>
                            </a>
                        </li>
                    </ul>
                </div>
                <div class="card-body">
                    <div class="tab-content">
                        <!-- Activity Tab -->
                        <div class="tab-pane fade show active" id="activity" role="tabpanel">
                            <?php if (!empty($user->activity_stats)): ?>
                            <div class="row mb-4">
                                <div class="col-md-3">
                                    <div class="text-center border rounded p-3">
                                        <div class="h4 text-primary"><?= $user->activity_stats->quotes_created ?? 0 ?></div>
                                        <small class="text-muted"><?= t('users.quotes_created') ?></small>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="text-center border rounded p-3">
                                        <div class="h4 text-success"><?= $user->activity_stats->orders_processed ?? 0 ?></div>
                                        <small class="text-muted"><?= t('users.orders_processed') ?></small>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="text-center border rounded p-3">
                                        <div class="h4 text-info"><?= $user->activity_stats->invoices_created ?? 0 ?></div>
                                        <small class="text-muted"><?= t('users.invoices_created') ?></small>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="text-center border rounded p-3">
                                        <div class="h4 text-warning"><?= $user->activity_stats->clients_managed ?? 0 ?></div>
                                        <small class="text-muted"><?= t('users.clients_managed') ?></small>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Recent Activity -->
                            <?php if (!empty($user->recent_activity)): ?>
                            <h6><?= t('users.recent_activity') ?></h6>
                            <div class="timeline">
                                <?php foreach ($user->recent_activity as $activity): ?>
                                <div class="d-flex mb-3">
                                    <div class="flex-shrink-0 me-3">
                                        <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                            <i class="fas fa-<?= $activity->icon ?? 'circle' ?> text-white"></i>
                                        </div>
                                    </div>
                                    <div class="flex-grow-1">
                                        <div class="fw-bold"><?= htmlspecialchars($activity->title) ?></div>
                                        <div class="text-muted"><?= htmlspecialchars($activity->description) ?></div>
                                        <div class="small text-muted"><?= date('M d, Y H:i', strtotime($activity->created_at)) ?></div>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                            <?php else: ?>
                            <div class="text-center py-4">
                                <i class="fas fa-chart-line fa-3x text-muted mb-3"></i>
                                <p class="text-muted"><?= t('users.no_recent_activity') ?></p>
                            </div>
                            <?php endif; ?>
                            <?php else: ?>
                            <div class="text-center py-4">
                                <i class="fas fa-chart-line fa-3x text-muted mb-3"></i>
                                <p class="text-muted"><?= t('users.no_activity_data') ?></p>
                            </div>
                            <?php endif; ?>
                        </div>

                        <!-- Permissions Tab -->
                        <div class="tab-pane fade" id="permissions" role="tabpanel">
                            <?php if (!empty($user->permissions)): ?>
                            <div class="row">
                                <?php foreach ($user->permissions as $module => $perms): ?>
                                <div class="col-md-6 mb-4">
                                    <div class="card">
                                        <div class="card-header">
                                            <h6 class="mb-0"><?= htmlspecialchars(ucfirst($module)) ?></h6>
                                        </div>
                                        <div class="card-body">
                                            <?php foreach ($perms as $permission): ?>
                                            <div class="d-flex justify-content-between align-items-center mb-2">
                                                <span><?= htmlspecialchars(str_replace('_', ' ', ucfirst($permission))) ?></span>
                                                <span class="badge bg-success">
                                                    <i class="fas fa-check"></i>
                                                </span>
                                            </div>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                            <?php else: ?>
                            <div class="text-center py-4">
                                <i class="fas fa-shield-alt fa-3x text-muted mb-3"></i>
                                <p class="text-muted"><?= t('users.no_permissions') ?></p>
                            </div>
                            <?php endif; ?>
                        </div>

                        <!-- Sessions Tab -->
                        <div class="tab-pane fade" id="sessions" role="tabpanel">
                            <?php if (!empty($user->active_sessions)): ?>
                            <div class="table-responsive">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th><?= t('users.device') ?></th>
                                            <th><?= t('users.ip_address') ?></th>
                                            <th><?= t('users.location') ?></th>
                                            <th><?= t('users.last_activity') ?></th>
                                            <th><?= t('users.status') ?></th>
                                            <th></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($user->active_sessions as $session): ?>
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <i class="fas fa-<?= strpos($session->user_agent, 'Mobile') !== false ? 'mobile-alt' : 'desktop' ?> me-2"></i>
                                                    <div>
                                                        <div class="fw-bold"><?= htmlspecialchars($session->browser ?? 'Unknown') ?></div>
                                                        <small class="text-muted"><?= htmlspecialchars($session->platform ?? 'Unknown OS') ?></small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td><?= htmlspecialchars($session->ip_address) ?></td>
                                            <td><?= htmlspecialchars($session->location ?? 'Unknown') ?></td>
                                            <td><?= date('M d, Y H:i', strtotime($session->last_activity)) ?></td>
                                            <td>
                                                <span class="badge bg-<?= $session->is_current ? 'success' : 'secondary' ?>">
                                                    <?= $session->is_current ? t('users.current') : t('users.active') ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?php if (!$session->is_current && $canManageRoles): ?>
                                                <button class="btn btn-sm btn-outline-danger" onclick="terminateSession('<?= $session->id ?>')">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                            <?php else: ?>
                            <div class="text-center py-4">
                                <i class="fas fa-desktop fa-3x text-muted mb-3"></i>
                                <p class="text-muted"><?= t('users.no_active_sessions') ?></p>
                            </div>
                            <?php endif; ?>
                        </div>

                        <!-- Audit Log Tab -->
                        <div class="tab-pane fade" id="audit-log" role="tabpanel">
                            <?php if (!empty($user->audit_log)): ?>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th><?= t('users.action') ?></th>
                                            <th><?= t('users.description') ?></th>
                                            <th><?= t('users.ip_address') ?></th>
                                            <th><?= t('users.timestamp') ?></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($user->audit_log as $log): ?>
                                        <tr>
                                            <td>
                                                <span class="badge bg-<?= match($log->action_type) {
                                                    'login' => 'success',
                                                    'logout' => 'info',
                                                    'create' => 'primary',
                                                    'update' => 'warning',
                                                    'delete' => 'danger',
                                                    default => 'secondary'
                                                } ?>">
                                                    <?= htmlspecialchars($log->action_type) ?>
                                                </span>
                                            </td>
                                            <td><?= htmlspecialchars($log->description) ?></td>
                                            <td><?= htmlspecialchars($log->ip_address) ?></td>
                                            <td><?= date('M d, Y H:i:s', strtotime($log->created_at)) ?></td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                            <?php else: ?>
                            <div class="text-center py-4">
                                <i class="fas fa-history fa-3x text-muted mb-3"></i>
                                <p class="text-muted"><?= t('users.no_audit_logs') ?></p>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <!-- Quick Stats -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><?= t('users.quick_stats') ?></h5>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-6">
                            <div class="border rounded p-3 mb-3">
                                <div class="h4 mb-1 text-primary"><?= $user->login_count ?? 0 ?></div>
                                <small class="text-muted"><?= t('users.total_logins') ?></small>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="border rounded p-3 mb-3">
                                <div class="h4 mb-1 text-success">
                                    <?php 
                                    if ($user->last_login) {
                                        echo ceil((time() - strtotime($user->last_login)) / 86400);
                                    } else {
                                        echo '∞';
                                    }
                                    ?>
                                </div>
                                <small class="text-muted"><?= t('users.days_since_login') ?></small>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="border rounded p-3 mb-3">
                                <div class="h4 mb-1 text-info"><?= count($user->active_sessions ?? []) ?></div>
                                <small class="text-muted"><?= t('users.active_sessions') ?></small>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="border rounded p-3 mb-3">
                                <div class="h4 mb-1 text-warning"><?= count($user->roles ?? []) ?></div>
                                <small class="text-muted"><?= t('users.assigned_roles') ?></small>
                            </div>
                        </div>
                    </div>
                    
                    <?php if ($user->created_at): ?>
                    <div class="border-top pt-3">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="text-muted"><?= t('users.member_since') ?>:</span>
                            <span><?= date('M d, Y', strtotime($user->created_at)) ?></span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="text-muted"><?= t('users.member_for') ?>:</span>
                            <span class="badge bg-info">
                                <?= ceil((time() - strtotime($user->created_at)) / 86400) ?> <?= t('common.days') ?>
                            </span>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Contact Information -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><?= t('users.contact_information') ?></h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <i class="fas fa-envelope text-muted me-2"></i>
                        <a href="mailto:<?= htmlspecialchars($user->email) ?>"><?= htmlspecialchars($user->email) ?></a>
                    </div>
                    
                    <?php if ($user->phone): ?>
                    <div class="mb-3">
                        <i class="fas fa-phone text-muted me-2"></i>
                        <a href="tel:<?= htmlspecialchars($user->phone) ?>"><?= htmlspecialchars($user->phone) ?></a>
                    </div>
                    <?php endif; ?>
                    
                    <?php if ($user->address): ?>
                    <div class="mb-3">
                        <i class="fas fa-map-marker-alt text-muted me-2"></i>
                        <?= nl2br(htmlspecialchars($user->address)) ?>
                    </div>
                    <?php endif; ?>
                    
                    <?php if ($user->emergency_contact): ?>
                    <hr>
                    <h6><?= t('users.emergency_contact') ?></h6>
                    <div class="text-muted">
                        <div><?= htmlspecialchars($user->emergency_contact) ?></div>
                        <?php if ($user->emergency_phone): ?>
                        <div>
                            <i class="fas fa-phone me-2"></i>
                            <a href="tel:<?= htmlspecialchars($user->emergency_phone) ?>"><?= htmlspecialchars($user->emergency_phone) ?></a>
                        </div>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><?= t('users.quick_actions') ?></h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="mailto:<?= htmlspecialchars($user->email) ?>" class="btn btn-primary">
                            <i class="fas fa-envelope"></i> <?= t('users.send_email') ?>
                        </a>
                        
                        <?php if ($canEdit): ?>
                        <a href="/users/<?= $user->id ?>/edit" class="btn btn-outline-primary">
                            <i class="fas fa-edit"></i> <?= t('common.edit_profile') ?>
                        </a>
                        <?php endif; ?>
                        
                        <?php if ($canManageRoles): ?>
                        <button class="btn btn-outline-secondary" onclick="resetPassword(<?= $user->id ?>)">
                            <i class="fas fa-key"></i> <?= t('users.reset_password') ?>
                        </button>
                        
                        <button class="btn btn-outline-warning" onclick="showRoleModal()">
                            <i class="fas fa-user-tag"></i> <?= t('users.manage_roles') ?>
                        </button>
                        <?php endif; ?>
                        
                        <a href="/users/<?= $user->id ?>/activity-log" class="btn btn-outline-info">
                            <i class="fas fa-history"></i> <?= t('users.full_activity_log') ?>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Role Management Modal -->
<?php if ($canManageRoles): ?>
<div class="modal fade" id="roleModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><?= t('users.manage_roles') ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="roleForm">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label"><?= t('users.select_roles') ?></label>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" value="admin" id="role_admin" 
                                   <?= in_array('admin', $user->roles ?? []) ? 'checked' : '' ?>>
                            <label class="form-check-label" for="role_admin">
                                <?= t('users.roles.admin') ?>
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" value="manager" id="role_manager" 
                                   <?= in_array('manager', $user->roles ?? []) ? 'checked' : '' ?>>
                            <label class="form-check-label" for="role_manager">
                                <?= t('users.roles.manager') ?>
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" value="sales" id="role_sales" 
                                   <?= in_array('sales', $user->roles ?? []) ? 'checked' : '' ?>>
                            <label class="form-check-label" for="role_sales">
                                <?= t('users.roles.sales') ?>
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" value="warehouse" id="role_warehouse" 
                                   <?= in_array('warehouse', $user->roles ?? []) ? 'checked' : '' ?>>
                            <label class="form-check-label" for="role_warehouse">
                                <?= t('users.roles.warehouse') ?>
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" value="accounting" id="role_accounting" 
                                   <?= in_array('accounting', $user->roles ?? []) ? 'checked' : '' ?>>
                            <label class="form-check-label" for="role_accounting">
                                <?= t('users.roles.accounting') ?>
                            </label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><?= t('common.cancel') ?></button>
                    <button type="submit" class="btn btn-primary"><?= t('common.save_changes') ?></button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>

<script>
function deleteUser(userId) {
    if (confirm('<?= t('users.confirm_delete') ?>')) {
        fetch(`/users/${userId}`, {
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
                window.location.href = '/users';
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

function toggleUserStatus(userId, newStatus) {
    const action = newStatus === 'active' ? '<?= t('users.activate_user') ?>' : '<?= t('users.deactivate_user') ?>';
    if (confirm(`${action}?`)) {
        fetch(`/users/${userId}/status`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({status: newStatus})
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

function resetPassword(userId) {
    if (confirm('<?= t('users.confirm_reset_password') ?>')) {
        fetch(`/users/${userId}/reset-password`, {
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

<?php if ($canManageRoles): ?>
function showRoleModal() {
    const modal = new bootstrap.Modal(document.getElementById('roleModal'));
    modal.show();
}

document.getElementById('roleForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const roles = [];
    const checkboxes = this.querySelectorAll('input[type="checkbox"]:checked');
    checkboxes.forEach(cb => roles.push(cb.value));
    
    fetch(`/users/<?= $user->id ?>/roles`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({roles: roles})
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert('success', data.message);
            bootstrap.Modal.getInstance(document.getElementById('roleModal')).hide();
            location.reload();
        } else {
            showAlert('error', data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('error', '<?= t('messages.error.general') ?>');
    });
});

function terminateSession(sessionId) {
    if (confirm('<?= t('users.confirm_terminate_session') ?>')) {
        fetch(`/users/sessions/${sessionId}/terminate`, {
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
<?php endif; ?>
</script>