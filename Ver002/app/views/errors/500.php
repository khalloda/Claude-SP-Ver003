<?php
/**
 * File: app/views/errors/500.php
 * Purpose: 500 Internal Server Error page
 * Layout: Minimal error layout with debugging info for admins
 */

$this->layout('layouts/error', [
    'title' => t('errors.500_title'),
    'error_code' => '500'
]);

$isDebug = \App\Config\Config::get('app.debug', false);
$currentUser = $this->getCurrentUser();
$isAdmin = $currentUser && in_array($currentUser['role'], ['admin', 'developer']);
?>

<div class="error-container">
    <div class="error-content">
        <div class="error-icon">
            <i class="fas fa-exclamation-triangle fa-5x text-danger mb-4"></i>
        </div>
        
        <h1 class="error-title"><?= t('errors.500_title') ?></h1>
        <h2 class="error-subtitle"><?= t('errors.500_subtitle') ?></h2>
        
        <p class="error-description">
            <?= t('errors.500_description') ?>
        </p>
        
        <div class="error-actions">
            <button onclick="location.reload()" class="btn btn-primary btn-lg me-3">
                <i class="fas fa-sync-alt me-2"></i><?= t('common.try_again') ?>
            </button>
            
            <a href="/" class="btn btn-outline-secondary btn-lg">
                <i class="fas fa-home me-2"></i><?= t('common.back_home') ?>
            </a>
        </div>
        
        <?php if ($isDebug && $isAdmin && isset($error_details)): ?>
        <hr class="my-4">
        
        <div class="error-debug">
            <div class="card bg-dark text-light">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-bug me-2"></i><?= t('errors.debug_info') ?>
                    </h5>
                </div>
                <div class="card-body">
                    <?php if (isset($error_details['message'])): ?>
                    <div class="mb-3">
                        <strong><?= t('errors.error_message') ?>:</strong><br>
                        <code class="text-danger"><?= htmlspecialchars($error_details['message']) ?></code>
                    </div>
                    <?php endif; ?>
                    
                    <?php if (isset($error_details['file']) && isset($error_details['line'])): ?>
                    <div class="mb-3">
                        <strong><?= t('errors.error_location') ?>:</strong><br>
                        <code><?= htmlspecialchars($error_details['file']) ?>:<?= $error_details['line'] ?></code>
                    </div>
                    <?php endif; ?>
                    
                    <?php if (isset($error_details['trace'])): ?>
                    <div class="mb-3">
                        <strong><?= t('errors.stack_trace') ?>:</strong>
                        <div class="mt-2" style="max-height: 300px; overflow-y: auto;">
                            <pre class="text-light mb-0"><code><?= htmlspecialchars($error_details['trace']) ?></code></pre>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endif; ?>
        
        <hr class="my-4">
        
        <div class="error-help">
            <h5><?= t('errors.what_happened') ?></h5>
            <ul class="list-unstyled">
                <li><i class="fas fa-info-circle text-info me-2"></i><?= t('errors.server_error_desc1') ?></li>
                <li><i class="fas fa-info-circle text-info me-2"></i><?= t('errors.server_error_desc2') ?></li>
                <li><i class="fas fa-info-circle text-info me-2"></i><?= t('errors.server_error_desc3') ?></li>
            </ul>
            
            <div class="mt-3">
                <small class="text-muted">
                    <?= t('errors.if_persists') ?> 
                    <a href="mailto:support@spareparts.com" class="text-decoration-none">support@spareparts.com</a>
                </small>
            </div>
        </div>
        
        <div class="mt-4">
            <small class="text-muted">
                <?= t('errors.error_id') ?>: <?= uniqid() ?> | 
                <?= t('errors.timestamp') ?>: <?= date('Y-m-d H:i:s') ?> |
                <?= t('errors.user_agent') ?>: <?= htmlspecialchars($_SERVER['HTTP_USER_AGENT'] ?? 'Unknown') ?>
            </small>
        </div>
    </div>
</div>