<?php
/**
 * File: app/views/errors/403.php
 * Purpose: 403 Forbidden error page
 * Layout: Minimal error layout with permission information
 */

$this->layout('layouts/error', [
    'title' => t('errors.403_title'),
    'error_code' => '403'
]);

$currentUser = $this->getCurrentUser();
?>

<div class="error-container">
    <div class="error-content">
        <div class="error-icon">
            <i class="fas fa-shield-alt fa-5x text-warning mb-4"></i>
        </div>
        
        <h1 class="error-title"><?= t('errors.403_title') ?></h1>
        <h2 class="error-subtitle"><?= t('errors.403_subtitle') ?></h2>
        
        <p class="error-description">
            <?= t('errors.403_description') ?>
        </p>
        
        <?php if ($currentUser): ?>
        <div class="alert alert-info">
            <i class="fas fa-info-circle me-2"></i>
            <?= t('errors.logged_in_as') ?> <strong><?= htmlspecialchars($currentUser['name']) ?></strong> 
            (<?= t('users.role.' . $currentUser['role']) ?>)
        </div>
        <?php endif; ?>
        
        <div class="error-actions">
            <?php if (!$currentUser): ?>
            <a href="/login" class="btn btn-primary btn-lg me-3">
                <i class="fas fa-sign-in-alt me-2"></i><?= t('auth.login') ?>
            </a>
            <?php endif; ?>
            
            <a href="/" class="btn btn-outline-secondary btn-lg me-3">
                <i class="fas fa-home me-2"></i><?= t('common.back_home') ?>
            </a>
            
            <button onclick="history.back()" class="btn btn-outline-secondary btn-lg">
                <i class="fas fa-arrow-left me-2"></i><?= t('common.go_back') ?>
            </button>
        </div>
        
        <hr class="my-4">
        
        <div class="error-help">
            <h5><?= t('errors.what_can_do') ?></h5>
            <ul class="list-unstyled">
                <?php if (!$currentUser): ?>
                <li><i class="fas fa-check text-success me-2"></i><?= t('errors.login_required') ?></li>
                <?php else: ?>
                <li><i class="fas fa-check text-success me-2"></i><?= t('errors.contact_admin') ?></li>
                <li><i class="fas fa-check text-success me-2"></i><?= t('errors.check_permissions') ?></li>
                <?php endif; ?>
                <li><i class="fas fa-check text-success me-2"></i><?= t('errors.use_navigation') ?></li>
                <li><i class="fas fa-check text-success me-2"></i><?= t('errors.contact_support') ?></li>
            </ul>
        </div>
        
        <div class="row mt-4">
            <div class="col-md-6">
                <div class="card bg-light">
                    <div class="card-body">
                        <h6 class="card-title">
                            <i class="fas fa-users me-2"></i><?= t('errors.need_access') ?>
                        </h6>
                        <p class="card-text small">
                            <?= t('errors.contact_admin_desc') ?>
                        </p>
                        <a href="mailto:admin@spareparts.com" class="btn btn-sm btn-primary">
                            <?= t('errors.contact_admin') ?>
                        </a>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="card bg-light">
                    <div class="card-body">
                        <h6 class="card-title">
                            <i class="fas fa-question-circle me-2"></i><?= t('errors.need_help') ?>
                        </h6>
                        <p class="card-text small">
                            <?= t('errors.support_desc') ?>
                        </p>
                        <a href="mailto:support@spareparts.com" class="btn btn-sm btn-outline-primary">
                            <?= t('errors.contact_support') ?>
                        </a>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="mt-4">
            <small class="text-muted">
                <?= t('errors.error_id') ?>: <?= uniqid() ?> | 
                <?= t('errors.timestamp') ?>: <?= date('Y-m-d H:i:s') ?>
            </small>
        </div>
    </div>
</div>