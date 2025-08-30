<?php
/**
 * File: app/views/profile/index.php
 * Purpose: User profile display page with comprehensive account information
 * Layout: Uses app layout with professional profile presentation
 */

$this->layout('layouts/app', [
    'title' => $page_title ?? t('profile.my_profile'),
    'active_nav' => 'profile'
]);

$currentUser = $this->getCurrentUser();
$canEdit = true; // Users can always edit their own profile
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">
            <i class="fas fa-user me-2"></i><?= t('profile.my_profile') ?>
            <span class="badge bg-<?= $user->is_active ? 'success' : 'secondary' ?> ms-2">
                <?= $user->is_active ? t('common.active') : t('common.inactive') ?>
            </span>
        </h1>
        <div class="btn-toolbar mb-2 mb-md-0">
            <div class="btn-group me-2">
                <a href="/profile/edit" class="btn btn-primary">
                    <i class="fas fa-edit"></i> <?= t('profile.edit_profile') ?>
                </a>
                <a href="/profile/change-password" class="btn btn-outline-warning">
                    <i class="fas fa-key"></i> <?= t('profile.change_password') ?>
                </a>
            </div>
            <div class="dropdown">
                <button class="btn btn-outline-info dropdown-toggle" type="button" data-bs-toggle="dropdown">
                    <i class="fas fa-cog"></i> <?= t('profile.settings') ?>
                </button>
                <ul class="dropdown-menu">
                    <li><a class="dropdown-item" href="/profile/preferences">
                        <i class="fas fa-sliders-h me-2"></i><?= t('profile.preferences') ?>
                    </a></li>
                    <li><a class="dropdown-item" href="/profile/security">
                        <i class="fas fa-shield-alt me-2"></i><?= t('profile.security_settings') ?>
                    </a></li>
                    <li><a class="dropdown-item" href="/profile/activity">
                        <i class="fas fa-history me-2"></i><?= t('profile.activity_log') ?>
                    </a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item" href="#" onclick="exportProfileData()">
                        <i class="fas fa-download me-2"></i><?= t('profile.export_data') ?>
                    </a></li>
                </ul>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Main Profile Information -->
        <div class="col-lg-8">
            <!-- Basic Profile Card -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0"><?= t('profile.basic_information') ?></h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3 text-center">
                            <!-- Profile Avatar -->
                            <div class="profile-avatar mb-3">
                                <?php if (!empty($user->avatar_url)): ?>
                                <img src="<?= htmlspecialchars($user->avatar_url) ?>" class="rounded-circle img-fluid" 
                                     alt="<?= htmlspecialchars($user->full_name) ?>" style="width: 120px; height: 120px; object-fit: cover;">
                                <?php else: ?>
                                <div class="avatar-placeholder rounded-circle d-flex align-items-center justify-content-center bg-primary text-white" 
                                     style="width: 120px; height: 120px; font-size: 2.5rem; font-weight: 600;">
                                    <?= strtoupper(substr($user->first_name ?? '', 0, 1) . substr($user->last_name ?? '', 0, 1)) ?>
                                </div>
                                <?php endif; ?>
                                <div class="mt-2">
                                    <a href="/profile/avatar" class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-camera"></i> <?= t('profile.change_avatar') ?>
                                    </a>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-9">
                            <div class="row">
                                <div class="col-md-6">
                                    <table class="table table-borderless">
                                        <tr>
                                            <td class="fw-medium"><?= t('profile.full_name') ?>:</td>
                                            <td><?= htmlspecialchars($user->first_name . ' ' . $user->last_name) ?></td>
                                        </tr>
                                        <tr>
                                            <td class="fw-medium"><?= t('profile.email') ?>:</td>
                                            <td>
                                                <?= htmlspecialchars($user->email) ?>
                                                <?php if ($user->email_verified_at): ?>
                                                <i class="fas fa-check-circle text-success ms-2" title="<?= t('profile.email_verified') ?>"></i>
                                                <?php else: ?>
                                                <i class="fas fa-exclamation-triangle text-warning ms-2" title="<?= t('profile.email_unverified') ?>"></i>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="fw-medium"><?= t('profile.username') ?>:</td>
                                            <td><?= htmlspecialchars($user->username) ?></td>
                                        </tr>
                                        <tr>
                                            <td class="fw-medium"><?= t('profile.role') ?>:</td>
                                            <td>
                                                <span class="badge bg-info"><?= t('roles.' . $user->role) ?></span>
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                                
                                <div class="col-md-6">
                                    <table class="table table-borderless">
                                        <tr>
                                            <td class="fw-medium"><?= t('profile.phone') ?>:</td>
                                            <td><?= htmlspecialchars($user->phone ?? t('common.not_set')) ?></td>
                                        </tr>
                                        <tr>
                                            <td class="fw-medium"><?= t('profile.department') ?>:</td>
                                            <td><?= htmlspecialchars($user->department ?? t('common.not_set')) ?></td>
                                        </tr>
                                        <tr>
                                            <td class="fw-medium"><?= t('profile.joined_date') ?>:</td>
                                            <td><?= date('F j, Y', strtotime($user->created_at)) ?></td>
                                        </tr>
                                        <tr>
                                            <td class="fw-medium"><?= t('profile.last_login') ?>:</td>
                                            <td>
                                                <?= $user->last_login_at ? date('M j, Y g:i A', strtotime($user->last_login_at)) : t('common.never') ?>
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <?php if (!empty($user->bio)): ?>
                    <hr>
                    <div>
                        <strong><?= t('profile.bio') ?>:</strong>
                        <p class="mt-2 mb-0"><?= nl2br(htmlspecialchars($user->bio)) ?></p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- User Preferences -->
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0"><?= t('profile.preferences') ?></h5>
                    <a href="/profile/preferences" class="btn btn-sm btn-outline-primary">
                        <i class="fas fa-edit"></i> <?= t('common.edit') ?>
                    </a>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <strong><?= t('profile.language') ?>:</strong>
                                <p class="mb-2">
                                    <?php if (($user->preferred_language ?? 'en') === 'ar'): ?>
                                    <i class="fas fa-globe me-2"></i><?= t('languages.arabic') ?>
                                    <?php else: ?>
                                    <i class="fas fa-globe me-2"></i><?= t('languages.english') ?>
                                    <?php endif; ?>
                                </p>
                            </div>
                            
                            <div class="mb-3">
                                <strong><?= t('profile.timezone') ?>:</strong>
                                <p class="mb-2">
                                    <i class="fas fa-clock me-2"></i>
                                    <?= htmlspecialchars($user->timezone ?? 'UTC') ?>
                                </p>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="mb-3">
                                <strong><?= t('profile.date_format') ?>:</strong>
                                <p class="mb-2">
                                    <i class="fas fa-calendar me-2"></i>
                                    <?= htmlspecialchars($user->date_format ?? 'M j, Y') ?>
                                    <small class="text-muted">(<?= date($user->date_format ?? 'M j, Y') ?>)</small>
                                </p>
                            </div>
                            
                            <div class="mb-3">
                                <strong><?= t('profile.currency') ?>:</strong>
                                <p class="mb-2">
                                    <i class="fas fa-coins me-2"></i>
                                    <?= htmlspecialchars($user->preferred_currency ?? 'USD') ?>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Account Activity -->
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0"><?= t('profile.recent_activity') ?></h5>
                    <a href="/profile/activity" class="btn btn-sm btn-outline-primary">
                        <?= t('profile.view_all') ?>
                    </a>
                </div>
                <div class="card-body">
                    <?php if (!empty($recentActivity)): ?>
                    <div class="timeline">
                        <?php foreach (array_slice($recentActivity, 0, 5) as $activity): ?>
                        <div class="timeline-item">
                            <div class="timeline-marker bg-<?= $activity->type_color ?? 'primary' ?>">
                                <i class="fas fa-<?= $activity->icon ?? 'circle' ?>"></i>
                            </div>
                            <div class="timeline-content">
                                <h6 class="timeline-title"><?= htmlspecialchars($activity->title) ?></h6>
                                <p class="timeline-description"><?= htmlspecialchars($activity->description) ?></p>
                                <small class="text-muted">
                                    <i class="fas fa-clock me-1"></i>
                                    <?= $this->timeAgo($activity->created_at) ?>
                                </small>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php else: ?>
                    <div class="text-center py-4">
                        <i class="fas fa-history fa-3x text-muted mb-3"></i>
                        <h5><?= t('profile.no_activity') ?></h5>
                        <p class="text-muted"><?= t('profile.no_activity_desc') ?></p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Quick Stats -->
            <div class="card mb-3">
                <div class="card-header">
                    <h6 class="card-title mb-0">
                        <i class="fas fa-chart-bar me-2"></i><?= t('profile.quick_stats') ?>
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-6">
                            <div class="border-end">
                                <div class="h4 text-primary mb-1"><?= $stats['logins_count'] ?? 0 ?></div>
                                <div class="small text-muted"><?= t('profile.total_logins') ?></div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="h4 text-success mb-1"><?= $stats['days_active'] ?? 0 ?></div>
                            <div class="small text-muted"><?= t('profile.days_active') ?></div>
                        </div>
                    </div>
                    
                    <hr>
                    
                    <div class="row text-center">
                        <div class="col-6">
                            <div class="border-end">
                                <div class="h5 text-info mb-1"><?= $stats['records_created'] ?? 0 ?></div>
                                <div class="small text-muted"><?= t('profile.records_created') ?></div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="h5 text-warning mb-1"><?= $stats['this_month_activity'] ?? 0 ?></div>
                            <div class="small text-muted"><?= t('profile.this_month') ?></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Security Information -->
            <div class="card mb-3">
                <div class="card-header">
                    <h6 class="card-title mb-0">
                        <i class="fas fa-shield-alt me-2"></i><?= t('profile.security_info') ?>
                    </h6>
                </div>
                <div class="card-body">
                    <div class="small">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span><?= t('profile.password_last_changed') ?>:</span>
                            <span class="text-muted">
                                <?= $user->password_changed_at ? date('M j, Y', strtotime($user->password_changed_at)) : t('common.unknown') ?>
                            </span>
                        </div>
                        
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span><?= t('profile.two_factor_auth') ?>:</span>
                            <span class="badge bg-<?= $user->two_factor_enabled ? 'success' : 'warning' ?>">
                                <?= $user->two_factor_enabled ? t('common.enabled') : t('common.disabled') ?>
                            </span>
                        </div>
                        
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span><?= t('profile.login_notifications') ?>:</span>
                            <span class="badge bg-<?= $user->login_notifications ? 'success' : 'secondary' ?>">
                                <?= $user->login_notifications ? t('common.enabled') : t('common.disabled') ?>
                            </span>
                        </div>
                        
                        <div class="d-flex justify-content-between align-items-center">
                            <span><?= t('profile.session_timeout') ?>:</span>
                            <span class="text-muted"><?= $user->session_timeout ?? 120 ?> <?= t('profile.minutes') ?></span>
                        </div>
                    </div>
                    
                    <div class="mt-3">
                        <a href="/profile/security" class="btn btn-sm btn-outline-primary w-100">
                            <i class="fas fa-cog"></i> <?= t('profile.security_settings') ?>
                        </a>
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
                        <a href="/profile/edit" class="btn btn-outline-primary btn-sm">
                            <i class="fas fa-edit"></i> <?= t('profile.edit_profile') ?>
                        </a>
                        <a href="/profile/change-password" class="btn btn-outline-warning btn-sm">
                            <i class="fas fa-key"></i> <?= t('profile.change_password') ?>
                        </a>
                        <a href="/profile/preferences" class="btn btn-outline-info btn-sm">
                            <i class="fas fa-sliders-h"></i> <?= t('profile.preferences') ?>
                        </a>
                        <button class="btn btn-outline-success btn-sm" onclick="exportProfileData()">
                            <i class="fas fa-download"></i> <?= t('profile.export_data') ?>
                        </button>
                        <a href="/profile/activity" class="btn btn-outline-secondary btn-sm">
                            <i class="fas fa-history"></i> <?= t('profile.activity_log') ?>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Session Information -->
            <div class="card">
                <div class="card-header">
                    <h6 class="card-title mb-0">
                        <i class="fas fa-desktop me-2"></i><?= t('profile.current_session') ?>
                    </h6>
                </div>
                <div class="card-body">
                    <div class="small">
                        <div class="d-flex justify-content-between mb-2">
                            <span><?= t('profile.ip_address') ?>:</span>
                            <code class="text-muted"><?= $_SERVER['REMOTE_ADDR'] ?? 'Unknown' ?></code>
                        </div>
                        
                        <div class="d-flex justify-content-between mb-2">
                            <span><?= t('profile.user_agent') ?>:</span>
                            <span class="text-muted text-truncate" style="max-width: 150px;" title="<?= htmlspecialchars($_SERVER['HTTP_USER_AGENT'] ?? '') ?>">
                                <?= $this->getBrowserName($_SERVER['HTTP_USER_AGENT'] ?? '') ?>
                            </span>
                        </div>
                        
                        <div class="d-flex justify-content-between mb-2">
                            <span><?= t('profile.session_started') ?>:</span>
                            <span class="text-muted"><?= date('g:i A', $_SERVER['REQUEST_TIME']) ?></span>
                        </div>
                        
                        <div class="d-flex justify-content-between">
                            <span><?= t('profile.session_expires') ?>:</span>
                            <span class="text-muted" id="sessionExpiry">-</span>
                        </div>
                    </div>
                    
                    <div class="mt-3">
                        <button class="btn btn-sm btn-outline-danger w-100" onclick="logoutAllSessions()">
                            <i class="fas fa-sign-out-alt"></i> <?= t('profile.logout_all_sessions') ?>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Export profile data
function exportProfileData() {
    const button = event.target;
    const originalText = button.textContent;
    button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> ' + originalText;
    button.disabled = true;
    
    window.location.href = '/api/profile/export?format=json';
    
    setTimeout(() => {
        button.innerHTML = '<i class="fas fa-download"></i> <?= t('profile.export_data') ?>';
        button.disabled = false;
    }, 2000);
}

// Logout all sessions
function logoutAllSessions() {
    if (confirm('<?= t('profile.confirm_logout_all') ?>')) {
        fetch('/api/profile/logout-all', {
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
                setTimeout(() => window.location.href = '/auth/login', 2000);
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

// Update session expiry countdown
function updateSessionExpiry() {
    const sessionTimeout = <?= ($user->session_timeout ?? 120) * 60 ?>; // Convert minutes to seconds
    const sessionStart = <?= $_SERVER['REQUEST_TIME'] ?? time() ?>;
    const currentTime = Math.floor(Date.now() / 1000);
    const timeRemaining = sessionTimeout - (currentTime - sessionStart);
    
    if (timeRemaining > 0) {
        const minutes = Math.floor(timeRemaining / 60);
        const seconds = timeRemaining % 60;
        document.getElementById('sessionExpiry').textContent = `${minutes}:${seconds.toString().padStart(2, '0')}`;
    } else {
        document.getElementById('sessionExpiry').textContent = '<?= t('profile.expired') ?>';
    }
}

// Initialize and update session timer
document.addEventListener('DOMContentLoaded', function() {
    updateSessionExpiry();
    setInterval(updateSessionExpiry, 1000);
});

<?php
// Helper functions
$this->extend('timeAgo', function($datetime) {
    $time = time() - strtotime($datetime);
    
    if ($time < 60) return t('profile.time.just_now');
    if ($time < 3600) return t('profile.time.minutes_ago', ['count' => floor($time/60)]);
    if ($time < 86400) return t('profile.time.hours_ago', ['count' => floor($time/3600)]);
    if ($time < 2592000) return t('profile.time.days_ago', ['count' => floor($time/86400)]);
    
    return date('M j, Y', strtotime($datetime));
});

$this->extend('getBrowserName', function($userAgent) {
    if (strpos($userAgent, 'Chrome') !== false) return 'Chrome';
    if (strpos($userAgent, 'Firefox') !== false) return 'Firefox';
    if (strpos($userAgent, 'Safari') !== false) return 'Safari';
    if (strpos($userAgent, 'Edge') !== false) return 'Edge';
    if (strpos($userAgent, 'Opera') !== false) return 'Opera';
    return 'Unknown';
});
?>
</script>

<style>
.profile-avatar {
    position: relative;
}

.avatar-placeholder {
    margin: 0 auto;
}

.timeline {
    position: relative;
    padding-left: 30px;
}

.timeline::before {
    content: '';
    position: absolute;
    left: 15px;
    top: 0;
    bottom: 0;
    width: 2px;
    background: #dee2e6;
}

.timeline-item {
    position: relative;
    margin-bottom: 20px;
}

.timeline-marker {
    position: absolute;
    left: -22px;
    top: 0;
    width: 30px;
    height: 30px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 0.8rem;
    border: 3px solid white;
    box-shadow: 0 0 0 3px #dee2e6;
}

.timeline-content {
    background: #f8f9fa;
    padding: 15px;
    border-radius: 8px;
    border: 1px solid #dee2e6;
}

.timeline-title {
    margin-bottom: 5px;
    font-size: 0.95rem;
    font-weight: 600;
}

.timeline-description {
    margin-bottom: 8px;
    font-size: 0.9rem;
    color: #6c757d;
}

.table-borderless td {
    padding: 0.5rem 0;
}

.border-end:last-child {
    border-right: none !important;
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
    
    .timeline {
        padding-left: 20px;
    }
    
    .timeline-marker {
        left: -15px;
        width: 24px;
        height: 24px;
        font-size: 0.7rem;
    }
}
</style>