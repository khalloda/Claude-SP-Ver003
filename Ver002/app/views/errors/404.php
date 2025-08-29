<?php
/**
 * File: app/views/errors/404.php
 * Purpose: 404 Not Found error page
 * Layout: Minimal error layout with branding
 */

$this->layout('layouts/error', [
    'title' => t('errors.404_title'),
    'error_code' => '404'
]);
?>

<div class="error-container">
    <div class="error-content">
        <div class="error-icon">
            <i class="fas fa-search fa-5x text-muted mb-4"></i>
        </div>
        
        <h1 class="error-title"><?= t('errors.404_title') ?></h1>
        <h2 class="error-subtitle"><?= t('errors.404_subtitle') ?></h2>
        
        <p class="error-description">
            <?= t('errors.404_description') ?>
        </p>
        
        <div class="error-actions">
            <a href="/" class="btn btn-primary btn-lg me-3">
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
                <li><i class="fas fa-check text-success me-2"></i><?= t('errors.check_url') ?></li>
                <li><i class="fas fa-check text-success me-2"></i><?= t('errors.use_navigation') ?></li>
                <li><i class="fas fa-check text-success me-2"></i><?= t('errors.contact_support') ?></li>
            </ul>
        </div>
        
        <div class="mt-4">
            <small class="text-muted">
                <?= t('errors.error_id') ?>: <?= uniqid() ?> | 
                <?= t('errors.timestamp') ?>: <?= date('Y-m-d H:i:s') ?>
            </small>
        </div>
    </div>
</div>