<?php
/**
 * File: app/views/users/index.php
 * Purpose: User management listing with role-based administration
 * Layout: Uses app layout with security and access management
 */

$this->layout('layouts/app', [
    'title' => $page_title ?? t('nav.users'),
    'active_nav' => 'users'
]);

$currentUser = $this->getCurrentUser();
$canCreate = $this->hasRole(['admin']);
$canEdit = $this->hasRole(['admin', 'manager']);
$canDelete = $this->hasRole(['admin']);
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-border">
        <h1 class="h2">
            <i class="fas fa-users me-2"></i><?= t('nav.users') ?>
            <span class="badge bg-secondary ms-2"><?= count($users ?? []) ?></span>
        </h1>
        <div class="btn-toolbar mb-2 mb-md-0">
            <div class="btn-group me-2">
                <button type="button" class="btn btn-sm btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown">
                    <i class="fas fa-shield-alt"></i> <?= t('users.security') ?>
                </button>
                <ul class="dropdown-menu">
                    <li><a class="dropdown-item" href="/users/security-log"><i class="fas fa-history me-2"></i><?= t('users.security_log') ?></a></li>
                    <li><a class="dropdown-item" href="/users/login-attempts"><i class="fas fa-exclamation-triangle me-2"></i><?= t('users.failed_attempts') ?></a></li>
                    <li><a class="dropdown-item" href="/users/active-sessions"><i class="fas fa-desktop me-2"></i><?= t('users.active_sessions') ?></a></li>
                </ul>
            </div>
            <?php if ($canCreate): ?>
            <a href="/users/create" class="btn btn-primary">
                <i class="fas fa-user-plus"></i> <?= t('users.add_user') ?>
            </a>
            <?php endif; ?>
        </div>
    </div>

    <!-- User Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body text-center">
                    <div class="d-flex justify-content-between">
                        <div>
                            <div class="h4"><?= $stats['total_users'] ?? 0 ?></div>
                            <div class="small"><?= t('users.total_users') ?></div>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-users fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body text-center">
                    <div class="d-flex justify-content-between">
                        <div>
                            <div class="h4"><?= $stats['active_users'] ?? 0 ?></div>
                            <div class="small"><?= t('users.active_users') ?></div>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-user-check fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card bg-warning text-white">
                <div class="card-body text-center">
                    <div class="d-flex justify-content-between">
                        <div>
                            <div class="h4"><?= $stats['locked_users'] ?? 0 ?></div>
                            <div class="small"><?= t('users.locked_users') ?></div>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-user-lock fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body text-center">
                    <div class="d-flex justify-content-between">
                        <div>
                            <div class="h4"><?= $stats['online_now'] ?? 0 ?></div>
                            <div class="small"><?= t('users.online_now') ?></div>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-circle fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="/users" class="row g-3">
                <div class="col-md-4">
                    <label for="search" class="form-label"><?= t('common.search') ?></label>
                    <input type="text" class="form-control" id="search" name="search" 
                           value="<?= htmlspecialchars($search ?? '') ?>" 
                           placeholder="<?= t('users.search_placeholder') ?>">
                </div>
                
                <div class="col-md-2">
                    <label for="role" class="form-label"><?= t('users.role') ?></label>
                    <select class="form-select" id="role" name="role">
                        <option value=""><?= t('users.all_roles') ?></option>
                        <option value="admin" <?= ($role ?? '') === 'admin' ? 'selected' : '' ?>><?= t('users.role.admin') ?></option>
                        <option value="manager" <?= ($role ?? '') === 'manager' ? 'selected' : '' ?>><?= t('users.role.manager') ?></option>
                        <option value="user" <?= ($role ?? '') === 'user' ? 'selected' : '' ?>><?= t('users.role.user') ?></option>
                    </select>
                </div>
                
                <div class="col-md-2">
                    <label for="status" class="form-label"><?= t('common.status') ?></label>
                    <select class="form-select" id="status" name="status">
                        <option value=""><?= t('common.all_statuses') ?></option>
                        <option value="1" <?= ($status ?? '') === '1' ? 'selected' : '' ?>><?= t('common.active') ?></option>
                        <option value="0" <?= ($status ?? '') === '0' ? 'selected' : '' ?>><?= t('common.inactive') ?></option>
                        <option value="2" <?= ($status ?? '') === '2' ? 'selected' : '' ?>><?= t('users.status.suspended') ?></option>
                    </select>
                </div>
                
                <div class="col-md-2">
                    <label for="last_login" class="form-label"><?= t('users.last_login') ?></label>
                    <select class="form-select" id="last_login" name="last_login">
                        <option value=""><?= t('users.any_time') ?></option>
                        <option value="today" <?= ($last_login ?? '') === 'today' ? 'selected' : '' ?>><?= t('users.today') ?></option>
                        <option value="week" <?= ($last_login ?? '') === 'week' ? 'selected' : '' ?>><?= t('users.this_week') ?></option>
                        <option value="month" <?= ($last_login ?? '') === 'month' ? 'selected' : '' ?>><?= t('users.this_month') ?></option>
                        <option value="never" <?= ($last_login ?? '') === 'never' ? 'selected' : '' ?>><?= t('users.never_logged_in') ?></option>
                    </select>
                </div>
                
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-outline-primary me-2">
                        <i class="fas fa-filter"></i> <?= t('common.filter') ?>
                    </button>
                    <a href="/users" class="btn btn-outline-secondary">
                        <i class="fas fa-times"></i>
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Users Table -->
    <div class="card">
        <div class="card-body">
            <?php if (empty($users)): ?>
                <div class="text-center py-5">
                    <i class="fas fa-users fa-4x text-muted mb-4"></i>
                    <h4><?= t('users.no_users_found') ?></h4>
                    <p class="text-muted"><?= t('users.no_users_desc') ?></p>
                    <?php if ($canCreate): ?>
                    <a href="/users/create" class="btn btn-primary">
                        <i class="fas fa-user-plus"></i> <?= t('users.add_first_user') ?>
                    </a>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th><?= t('users.user') ?></th>
                                <th><?= t('users.role') ?></th>
                                <th><?= t('users.last_login') ?></th>
                                <th><?= t('users.login_attempts') ?></th>
                                <th><?= t('common.status') ?></th>
                                <th><?= t('users.created_at') ?></th>
                                <th class="text-end"><?= t('common.actions') ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($users as $user): ?>
                            <tr class="<?= $user->status === 2 ? 'table-warning' : ($user->status === 0 ? 'table-secondary' : '') ?>">
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="user-avatar bg-primary text-white rounded-circle me-3 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                            <?= strtoupper(substr($user->name, 0, 1)) ?>
                                        </div>
                                        <div>
                                            <div class="fw-bold">
                                                <?= htmlspecialchars($user->name) ?>
                                                <?php if ($user->id === $currentUser['id']): ?>
                                                <span class="badge bg-info ms-1"><?= t('users.you') ?></span>
                                                <?php endif; ?>
                                                <?php if ($user->is_online ?? false): ?>
                                                <span class="text-success" title="<?= t('users.online_now') ?>">
                                                    <i class="fas fa-circle" style="font-size: 0.5rem;"></i>
                                                </span>
                                                <?php endif; ?>
                                            </div>
                                            <div class="text-muted small">
                                                <i class="fas fa-envelope me-1"></i><?= htmlspecialchars($user->email) ?>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <?php 
                                    $roleClass = match($user->role) {
                                        'admin' => 'bg-danger',
                                        'manager' => 'bg-warning text-dark',
                                        'user' => 'bg-secondary',
                                        default => 'bg-secondary'
                                    };
                                    $roleIcon = match($user->role) {
                                        'admin' => 'fas fa-crown',
                                        'manager' => 'fas fa-user-tie',
                                        'user' => 'fas fa-user',
                                        default => 'fas fa-user'
                                    };
                                    ?>
                                    <span class="badge <?= $roleClass ?>">
                                        <i class="<?= $roleIcon ?> me-1"></i><?= t('users.role.' . $user->role) ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ($user->last_login_at): ?>
                                    <div><?= date('M d, Y', strtotime($user->last_login_at)) ?></div>
                                    <small class="text-muted"><?= date('H:i', strtotime($user->last_login_at)) ?></small>
                                    <?php else: ?>
                                    <span class="text-muted"><?= t('users.never') ?></span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($user->failed_login_attempts > 0): ?>
                                    <span class="badge bg-warning text-dark">
                                        <i class="fas fa-exclamation-triangle me-1"></i><?= $user->failed_login_attempts ?>
                                    </span>
                                    <?php if ($user->locked_until && strtotime($user->locked_until) > time()): ?>
                                    <br><small class="text-danger"><?= t('users.locked_until') ?> <?= date('H:i', strtotime($user->locked_until)) ?></small>
                                    <?php endif; ?>
                                    <?php else: ?>
                                    <span class="text-muted">0</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php 
                                    $statusClass = match($user->status) {
                                        1 => 'bg-success',
                                        0 => 'bg-secondary',
                                        2 => 'bg-warning text-dark',
                                        default => 'bg-secondary'
                                    };
                                    $statusText = match($user->status) {
                                        1 => t('common.active'),
                                        0 => t('common.inactive'),
                                        2 => t('users.status.suspended'),
                                        default => t('common.inactive')
                                    };
                                    ?>
                                    <span class="badge <?= $statusClass ?>"><?= $statusText ?></span>
                                </td>
                                <td>
                                    <?= date('M d, Y', strtotime($user->created_at)) ?>
                                </td>
                                <td class="text-end">
                                    <div class="btn-group">
                                        <a href="/users/<?= $user->id ?>" class="btn btn-sm btn-outline-primary" title="<?= t('common.view') ?>">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        
                                        <?php if ($canEdit && $user->id !== $currentUser['id']): ?>
                                        <a href="/users/<?= $user->id ?>/edit" class="btn btn-sm btn-outline-secondary" title="<?= t('common.edit') ?>">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <?php endif; ?>
                                        
                                        <div class="btn-group">
                                            <button type="button" class="btn btn-sm btn-outline-secondary dropdown-toggle dropdown-toggle-split" data-bs-toggle="dropdown">
                                                <span class="visually-hidden"><?= t('common.actions') ?></span>
                                            </button>
                                            <ul class="dropdown-menu">
                                                <?php if ($user->failed_login_attempts > 0 && $canEdit): ?>
                                                <li><a class="dropdown-item" href="#" onclick="resetLoginAttempts(<?= $user->id ?>)">
                                                    <i class="fas fa-unlock me-2"></i><?= t('users.reset_login_attempts') ?>
                                                </a></li>
                                                <?php endif; ?>
                                                
                                                <?php if ($user->id !== $currentUser['id'] && $canEdit): ?>
                                                <?php if ($user->status === 1): ?>
                                                <li><a class="dropdown-item" href="#" onclick="toggleUserStatus(<?= $user->id ?>, 0)">
                                                    <i class="fas fa-user-slash me-2"></i><?= t('users.deactivate') ?>
                                                </a></li>
                                                <li><a class="dropdown-item" href="#" onclick="toggleUserStatus(<?= $user->id ?>, 2)">
                                                    <i class="fas fa-ban me-2"></i><?= t('users.suspend') ?>
                                                </a></li>
                                                <?php elseif ($user->status === 0): ?>
                                                <li><a class="dropdown-item" href="#" onclick="toggleUserStatus(<?= $user->id ?>, 1)">
                                                    <i class="fas fa-user-check me-2"></i><?= t('users.activate') ?>
                                                </a></li>
                                                <?php elseif ($user->status === 2): ?>
                                                <li><a class="dropdown-item" href="#" onclick="toggleUserStatus(<?= $user->id ?>, 1)">
                                                    <i class="fas fa-user-check me-2"></i><?= t('users.unsuspend') ?>
                                                </a></li>
                                                <?php endif; ?>
                                                <?php endif; ?>
                                                
                                                <li><a class="dropdown-item" href="/users/<?= $user->id ?>/activity">
                                                    <i class="fas fa-history me-2"></i><?= t('users.activity_log') ?>
                                                </a></li>
                                                
                                                <?php if ($canEdit): ?>
                                                <li><a class="dropdown-item" href="/users/<?= $user->id ?>/reset-password">
                                                    <i class="fas fa-key me-2"></i><?= t('users.reset_password') ?>
                                                </a></li>
                                                <?php endif; ?>
                                                
                                                <?php if ($canDelete && $user->id !== $currentUser['id'] && $user->role !== 'admin'): ?>
                                                <li><hr class="dropdown-divider"></li>
                                                <li><a class="dropdown-item text-danger" href="#" onclick="deleteUser(<?= $user->id ?>, '<?= htmlspecialchars($user->name) ?>')">
                                                    <i class="fas fa-trash me-2"></i><?= t('common.delete') ?>
                                                </a></li>
                                                <?php endif; ?>
                                            </ul>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                
                <!-- Pagination -->
                <?php if (isset($pagination) && $pagination['total_pages'] > 1): ?>
                <nav aria-label="<?= t('common.pagination') ?>" class="mt-4">
                    <ul class="pagination justify-content-center">
                        <!-- Pagination implementation -->
                    </ul>
                </nav>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
function toggleUserStatus(userId, newStatus) {
    const statusNames = {
        0: '<?= t('common.inactive') ?>',
        1: '<?= t('common.active') ?>',
        2: '<?= t('users.status.suspended') ?>'
    };
    
    if (confirm('<?= t('users.confirm_status_change') ?>'.replace(':status', statusNames[newStatus]))) {
        fetch(`/users/${userId}/status`, {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({ status: newStatus })
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

function resetLoginAttempts(userId) {
    if (confirm('<?= t('users.confirm_reset_attempts') ?>')) {
        fetch(`/users/${userId}/reset-attempts`, {
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

function deleteUser(userId, userName) {
    if (confirm('<?= t('users.confirm_delete') ?>'.replace(':name', userName))) {
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