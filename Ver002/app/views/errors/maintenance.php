<?php
/**
 * File: app/views/errors/maintenance.php
 * Purpose: Maintenance mode page
 * Layout: Minimal error layout with maintenance information
 */

$this->layout('layouts/error', [
    'title' => t('errors.maintenance_title'),
    'error_code' => '503'
]);
?>

<div class="error-container">
    <div class="error-content">
        <div class="error-icon">
            <i class="fas fa-tools fa-5x text-info mb-4"></i>
        </div>
        
        <h1 class="error-title"><?= t('errors.maintenance_title') ?></h1>
        <h2 class="error-subtitle"><?= t('errors.maintenance_subtitle') ?></h2>
        
        <p class="error-description">
            <?= t('errors.maintenance_description') ?>
        </p>
        
        <div class="alert alert-info">
            <div class="d-flex align-items-center">
                <i class="fas fa-clock me-3 fa-2x"></i>
                <div>
                    <strong><?= t('errors.estimated_downtime') ?>:</strong><br>
                    <span id="countdown" class="h5 text-primary">
                        <?= $maintenance_end ?? t('errors.unknown_time') ?>
                    </span>
                </div>
            </div>
        </div>
        
        <div class="error-actions">
            <button onclick="location.reload()" class="btn btn-primary btn-lg">
                <i class="fas fa-sync-alt me-2"></i><?= t('common.try_again') ?>
            </button>
        </div>
        
        <hr class="my-4">
        
        <div class="error-help">
            <h5><?= t('errors.what_doing') ?></h5>
            <div class="row">
                <div class="col-md-6">
                    <ul class="list-unstyled">
                        <li class="mb-2">
                            <i class="fas fa-server text-success me-2"></i>
                            <?= t('errors.maintenance_item1') ?>
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-shield-alt text-success me-2"></i>
                            <?= t('errors.maintenance_item2') ?>
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-rocket text-success me-2"></i>
                            <?= t('errors.maintenance_item3') ?>
                        </li>
                    </ul>
                </div>
                <div class="col-md-6">
                    <ul class="list-unstyled">
                        <li class="mb-2">
                            <i class="fas fa-bug text-success me-2"></i>
                            <?= t('errors.maintenance_item4') ?>
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-database text-success me-2"></i>
                            <?= t('errors.maintenance_item5') ?>
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-cogs text-success me-2"></i>
                            <?= t('errors.maintenance_item6') ?>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
        
        <div class="row mt-4">
            <div class="col-md-4">
                <div class="card text-center">
                    <div class="card-body">
                        <i class="fas fa-bell fa-2x text-warning mb-3"></i>
                        <h6 class="card-title"><?= t('errors.stay_updated') ?></h6>
                        <p class="card-text small"><?= t('errors.follow_updates') ?></p>
                        <a href="https://twitter.com/sparepartsystem" class="btn btn-sm btn-outline-primary" target="_blank">
                            <i class="fab fa-twitter me-1"></i>Twitter
                        </a>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="card text-center">
                    <div class="card-body">
                        <i class="fas fa-envelope fa-2x text-info mb-3"></i>
                        <h6 class="card-title"><?= t('errors.need_help') ?></h6>
                        <p class="card-text small"><?= t('errors.emergency_contact') ?></p>
                        <a href="mailto:support@spareparts.com" class="btn btn-sm btn-outline-primary">
                            <?= t('errors.contact_support') ?>
                        </a>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="card text-center">
                    <div class="card-body">
                        <i class="fas fa-download fa-2x text-success mb-3"></i>
                        <h6 class="card-title"><?= t('errors.mobile_app') ?></h6>
                        <p class="card-text small"><?= t('errors.app_available') ?></p>
                        <div class="btn-group-vertical">
                            <a href="#" class="btn btn-sm btn-outline-primary mb-1">
                                <i class="fab fa-apple me-1"></i>iOS
                            </a>
                            <a href="#" class="btn btn-sm btn-outline-primary">
                                <i class="fab fa-android me-1"></i>Android
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="mt-4">
            <small class="text-muted">
                <?= t('errors.maintenance_id') ?>: <?= uniqid() ?> | 
                <?= t('errors.started_at') ?>: <?= $maintenance_start ?? date('Y-m-d H:i:s') ?>
            </small>
        </div>
    </div>
</div>

<?php if (isset($maintenance_end_timestamp)): ?>
<script>
// Countdown timer
function updateCountdown() {
    const endTime = <?= $maintenance_end_timestamp ?> * 1000;
    const now = new Date().getTime();
    const distance = endTime - now;
    
    if (distance < 0) {
        document.getElementById('countdown').innerHTML = '<?= t('errors.maintenance_complete') ?>';
        setTimeout(function() {
            location.reload();
        }, 5000);
        return;
    }
    
    const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
    const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
    const seconds = Math.floor((distance % (1000 * 60)) / 1000);
    
    document.getElementById('countdown').innerHTML = 
        hours.toString().padStart(2, '0') + ':' + 
        minutes.toString().padStart(2, '0') + ':' + 
        seconds.toString().padStart(2, '0');
}

// Update countdown every second
setInterval(updateCountdown, 1000);
updateCountdown();
</script>
<?php endif; ?>