<?php
/**
 * File: app/views/partials/footer.php
 * Purpose: Application footer with system information and links
 * Notes: Responsive footer for authenticated users
 */
?>

<footer class="bg-light border-top mt-auto py-4">
    <div class="container-fluid">
        <div class="row align-items-center">
            <div class="col-md-6">
                <div class="d-flex align-items-center">
                    <i class="fas fa-cogs me-2 text-primary"></i>
                    <small class="text-muted">
                        <strong><?= htmlspecialchars($app_name ?? 'MISP Ver002') ?></strong> v2.0.0 
                        | <?= t('common.powered_by') ?> <a href="#" class="text-decoration-none">Claude AI</a>
                    </small>
                </div>
            </div>
            
            <div class="col-md-6 text-md-end">
                <div class="d-flex justify-content-md-end justify-content-start align-items-center flex-wrap">
                    <!-- System Status -->
                    <span class="badge bg-success me-2" title="<?= t('common.system_status') ?>">
                        <i class="fas fa-circle me-1"></i><?= t('common.online') ?>
                    </span>
                    
                    <!-- Current User Info -->
                    <?php if (isset($current_user) && $current_user): ?>
                        <small class="text-muted me-3">
                            <i class="fas fa-user me-1"></i>
                            <?= t('common.logged_in_as') ?>: <strong><?= htmlspecialchars($current_user['name'] ?? 'User') ?></strong>
                        </small>
                    <?php endif; ?>
                    
                    <!-- Current Time -->
                    <small class="text-muted">
                        <i class="fas fa-clock me-1"></i>
                        <span id="current-time"><?= date('Y-m-d H:i:s') ?></span>
                    </small>
                </div>
            </div>
        </div>
        
        <hr class="my-3">
        
        <div class="row">
            <div class="col-md-8">
                <div class="d-flex flex-wrap">
                    <a href="/reports" class="btn btn-outline-secondary btn-sm me-2 mb-2">
                        <i class="fas fa-chart-line me-1"></i><?= t('nav.reports') ?>
                    </a>
                    <a href="/settings" class="btn btn-outline-secondary btn-sm me-2 mb-2">
                        <i class="fas fa-cogs me-1"></i><?= t('nav.settings') ?>
                    </a>
                    <a href="/profile" class="btn btn-outline-secondary btn-sm me-2 mb-2">
                        <i class="fas fa-user me-1"></i><?= t('nav.profile') ?>
                    </a>
                </div>
            </div>
            
            <div class="col-md-4 text-md-end">
                <!-- Language Switcher -->
                <div class="btn-group" role="group" aria-label="<?= t('common.language_switcher') ?>">
                    <button type="button" class="btn btn-outline-secondary btn-sm <?= ($current_lang ?? 'en') === 'en' ? 'active' : '' ?>" 
                            onclick="switchLanguage('en')">
                        <i class="fas fa-globe me-1"></i>English
                    </button>
                    <button type="button" class="btn btn-outline-secondary btn-sm <?= ($current_lang ?? 'en') === 'ar' ? 'active' : '' ?>" 
                            onclick="switchLanguage('ar')">
                        <i class="fas fa-globe me-1"></i>العربية
                    </button>
                </div>
            </div>
        </div>
    </div>
</footer>

<!-- Footer JavaScript -->
<script>
// Update current time every second
function updateTime() {
    const now = new Date();
    const timeString = now.getFullYear() + '-' + 
                      String(now.getMonth() + 1).padStart(2, '0') + '-' + 
                      String(now.getDate()).padStart(2, '0') + ' ' +
                      String(now.getHours()).padStart(2, '0') + ':' + 
                      String(now.getMinutes()).padStart(2, '0') + ':' + 
                      String(now.getSeconds()).padStart(2, '0');
    
    const timeElement = document.getElementById('current-time');
    if (timeElement) {
        timeElement.textContent = timeString;
    }
}

// Update time every second
setInterval(updateTime, 1000);

// Language switcher function
function switchLanguage(lang) {
    const url = new URL(window.location);
    url.searchParams.set('lang', lang);
    window.location.href = url.toString();
}

// Initialize footer functionality
document.addEventListener('DOMContentLoaded', function() {
    updateTime();
    
    // Add smooth scroll to footer links
    document.querySelectorAll('footer a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth'
                });
            }
        });
    });
});
</script>

<style>
/* Footer-specific styles */
footer {
    margin-top: auto;
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    border-top: 2px solid #dee2e6;
}

footer .badge {
    font-size: 0.75rem;
    padding: 0.25rem 0.5rem;
}

footer .btn-sm {
    font-size: 0.75rem;
    padding: 0.25rem 0.5rem;
}

/* RTL support for footer */
[dir="rtl"] footer .me-2 {
    margin-left: 0.5rem !important;
    margin-right: 0 !important;
}

[dir="rtl"] footer .me-3 {
    margin-left: 1rem !important;
    margin-right: 0 !important;
}

[dir="rtl"] footer .text-md-end {
    text-align: right !important;
}

/* Dark mode support (future enhancement) */
@media (prefers-color-scheme: dark) {
    footer {
        background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
        color: #ecf0f1;
        border-top-color: #34495e;
    }
    
    footer .text-muted {
        color: #bdc3c7 !important;
    }
    
    footer .btn-outline-secondary {
        border-color: #7f8c8d;
        color: #ecf0f1;
    }
    
    footer .btn-outline-secondary:hover {
        background-color: #7f8c8d;
        border-color: #7f8c8d;
        color: #2c3e50;
    }
}

/* Mobile responsiveness */
@media (max-width: 768px) {
    footer .col-md-4,
    footer .col-md-6,
    footer .col-md-8 {
        text-align: center !important;
        margin-bottom: 1rem;
    }
    
    footer .d-flex {
        justify-content: center !important;
    }
    
    footer .btn-group {
        width: 100%;
    }
    
    footer .btn-group .btn {
        flex: 1;
    }
}
</style>