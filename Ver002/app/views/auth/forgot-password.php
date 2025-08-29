<?php
/**
 * File: app/views/auth/forgot-password.php
 * Purpose: Password reset request form with security features
 * Layout: Uses auth layout with professional password recovery interface
 */

$this->layout('layouts/auth', [
    'title' => $page_title ?? t('auth.forgot_password'),
    'body_class' => 'forgot-password-page'
]);

$recaptcha_enabled = $recaptcha_enabled ?? false;
$max_attempts = $max_attempts ?? 5;
$lockout_duration = $lockout_duration ?? 15; // minutes
?>

<div class="forgot-password-container">
    <div class="card shadow-lg">
        <div class="card-header text-center bg-info text-white">
            <div class="mb-3">
                <i class="fas fa-key fa-3x"></i>
            </div>
            <h4 class="mb-0"><?= t('auth.forgot_password') ?></h4>
            <small><?= t('auth.reset_password_subtitle') ?></small>
        </div>
        
        <div class="card-body">
            <?php if (isset($success) && $success): ?>
            <!-- Success Message -->
            <div class="alert alert-success">
                <div class="d-flex align-items-center">
                    <i class="fas fa-check-circle fa-2x me-3"></i>
                    <div>
                        <h6 class="mb-1"><?= t('auth.reset_email_sent') ?></h6>
                        <p class="mb-0"><?= t('auth.reset_email_instructions') ?></p>
                    </div>
                </div>
            </div>
            
            <div class="text-center mt-4">
                <p class="text-muted">
                    <?= t('auth.didnt_receive_email') ?>
                </p>
                <div class="d-grid gap-2 d-md-block">
                    <button type="button" class="btn btn-outline-secondary" onclick="resendEmail()">
                        <i class="fas fa-redo"></i> <?= t('auth.resend_email') ?>
                    </button>
                    <a href="/auth/login" class="btn btn-outline-primary">
                        <i class="fas fa-arrow-left"></i> <?= t('auth.back_to_login') ?>
                    </a>
                </div>
            </div>
            
            <hr class="my-4">
            
            <!-- Reset Instructions -->
            <div class="row">
                <div class="col-md-4 text-center mb-3">
                    <div class="step-icon bg-primary text-white rounded-circle mx-auto mb-2" style="width: 50px; height: 50px; line-height: 50px;">
                        1
                    </div>
                    <h6><?= t('auth.step_check_email') ?></h6>
                    <small class="text-muted"><?= t('auth.step_check_email_desc') ?></small>
                </div>
                <div class="col-md-4 text-center mb-3">
                    <div class="step-icon bg-primary text-white rounded-circle mx-auto mb-2" style="width: 50px; height: 50px; line-height: 50px;">
                        2
                    </div>
                    <h6><?= t('auth.step_click_link') ?></h6>
                    <small class="text-muted"><?= t('auth.step_click_link_desc') ?></small>
                </div>
                <div class="col-md-4 text-center mb-3">
                    <div class="step-icon bg-primary text-white rounded-circle mx-auto mb-2" style="width: 50px; height: 50px; line-height: 50px;">
                        3
                    </div>
                    <h6><?= t('auth.step_create_password') ?></h6>
                    <small class="text-muted"><?= t('auth.step_create_password_desc') ?></small>
                </div>
            </div>
            
            <?php else: ?>
            <!-- Request Form -->
            
            <?php if (isset($locked_until)): ?>
            <!-- Account Locked Notice -->
            <div class="alert alert-warning">
                <div class="d-flex align-items-center">
                    <i class="fas fa-exclamation-triangle fa-2x me-3"></i>
                    <div>
                        <h6 class="mb-1"><?= t('auth.too_many_attempts') ?></h6>
                        <p class="mb-0">
                            <?= t('auth.account_locked_until', ['time' => date('H:i', strtotime($locked_until))]) ?>
                        </p>
                    </div>
                </div>
            </div>
            <?php endif; ?>
            
            <div class="row align-items-center mb-4">
                <div class="col-md-2 text-center">
                    <i class="fas fa-shield-alt fa-3x text-primary"></i>
                </div>
                <div class="col-md-10">
                    <h6><?= t('auth.secure_password_reset') ?></h6>
                    <p class="text-muted mb-0">
                        <?= t('auth.reset_security_notice') ?>
                    </p>
                </div>
            </div>
            
            <form method="POST" action="/auth/forgot-password" id="forgotPasswordForm" novalidate>
                <?= $this->csrf() ?>
                
                <div class="mb-4">
                    <label for="email" class="form-label">
                        <?= t('auth.email_or_username') ?> *
                    </label>
                    <div class="input-group input-group-lg">
                        <span class="input-group-text">
                            <i class="fas fa-envelope"></i>
                        </span>
                        <input type="text" class="form-control" id="email" name="email" 
                               value="<?= $this->old('email') ?>" required
                               placeholder="<?= t('auth.enter_email_or_username') ?>"
                               <?= isset($locked_until) ? 'disabled' : '' ?>>
                    </div>
                    <?= $this->error('email') ?>
                    <small class="form-text text-muted">
                        <?= t('auth.reset_email_help') ?>
                    </small>
                </div>
                
                <!-- Security Questions (if enabled) -->
                <?php if (isset($security_questions) && !empty($security_questions)): ?>
                <div class="mb-4">
                    <h6 class="text-muted border-bottom pb-2 mb-3">
                        <i class="fas fa-question-circle me-2"></i><?= t('auth.security_verification') ?>
                    </h6>
                    
                    <?php foreach ($security_questions as $index => $question): ?>
                    <div class="mb-3">
                        <label for="security_answer_<?= $index ?>" class="form-label">
                            <?= htmlspecialchars($question->question) ?> *
                        </label>
                        <input type="text" class="form-control" id="security_answer_<?= $index ?>" 
                               name="security_answers[<?= $question->id ?>]" required
                               placeholder="<?= t('auth.enter_your_answer') ?>">
                        <?= $this->error('security_answers.' . $question->id) ?>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
                
                <!-- Rate Limiting Notice -->
                <div class="alert alert-light border">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-info-circle text-info me-2"></i>
                        <small class="text-muted">
                            <?= t('auth.rate_limit_notice', [
                                'max_attempts' => $max_attempts, 
                                'duration' => $lockout_duration
                            ]) ?>
                        </small>
                    </div>
                </div>
                
                <!-- reCAPTCHA -->
                <?php if ($recaptcha_enabled): ?>
                <div class="mb-4">
                    <div class="g-recaptcha" data-sitekey="<?= $recaptcha_site_key ?? '' ?>"></div>
                    <?= $this->error('recaptcha') ?>
                </div>
                <?php endif; ?>
                
                <!-- Submit Button -->
                <div class="d-grid">
                    <button type="submit" class="btn btn-info btn-lg" 
                            <?= isset($locked_until) ? 'disabled' : '' ?>>
                        <i class="fas fa-paper-plane me-2"></i>
                        <?= t('auth.send_reset_link') ?>
                    </button>
                </div>
            </form>
            
            <!-- Alternative Options -->
            <div class="mt-4">
                <div class="row">
                    <div class="col-md-6">
                        <div class="d-grid">
                            <a href="/auth/login" class="btn btn-outline-secondary">
                                <i class="fas fa-arrow-left me-2"></i><?= t('auth.back_to_login') ?>
                            </a>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="d-grid">
                            <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#contactSupportModal">
                                <i class="fas fa-headset me-2"></i><?= t('auth.contact_support') ?>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            
            <?php endif; ?>
        </div>
        
        <div class="card-footer text-center">
            <small class="text-muted">
                <?= t('auth.security_footer_text') ?>
            </small>
        </div>
    </div>
</div>

<!-- Contact Support Modal -->
<div class="modal fade" id="contactSupportModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-headset me-2"></i><?= t('auth.contact_support') ?>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="text-center mb-3">
                            <i class="fas fa-envelope fa-2x text-primary mb-2"></i>
                            <h6><?= t('auth.email_support') ?></h6>
                            <p class="text-muted small">
                                <a href="mailto:<?= $support_email ?? 'support@company.com' ?>">
                                    <?= $support_email ?? 'support@company.com' ?>
                                </a>
                            </p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="text-center mb-3">
                            <i class="fas fa-phone fa-2x text-success mb-2"></i>
                            <h6><?= t('auth.phone_support') ?></h6>
                            <p class="text-muted small">
                                <?= $support_phone ?? '+1-800-SUPPORT' ?><br>
                                <small><?= t('auth.business_hours') ?></small>
                            </p>
                        </div>
                    </div>
                </div>
                
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>
                    <?= t('auth.support_info_needed') ?>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <?= t('common.close') ?>
                </button>
            </div>
        </div>
    </div>
</div>

<?php if ($recaptcha_enabled): ?>
<script src="https://www.google.com/recaptcha/api.js" async defer></script>
<?php endif; ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('forgotPasswordForm');
    const emailInput = document.getElementById('email');
    
    // Email/Username validation
    emailInput.addEventListener('blur', function() {
        const value = this.value.trim();
        const isEmail = /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value);
        const isUsername = /^[a-zA-Z0-9_]{3,20}$/.test(value);
        
        if (value && !isEmail && !isUsername) {
            this.classList.add('is-invalid');
        } else {
            this.classList.remove('is-invalid');
        }
    });
    
    // Form submission
    form.addEventListener('submit', function(e) {
        const emailValue = emailInput.value.trim();
        
        if (!emailValue) {
            e.preventDefault();
            emailInput.classList.add('is-invalid');
            showAlert('error', '<?= t('auth.email_required') ?>');
            return;
        }
        
        // Show loading state
        const submitBtn = this.querySelector('button[type="submit"]');
        const originalText = submitBtn.innerHTML;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i><?= t('auth.sending') ?>';
        submitBtn.disabled = true;
        
        // Re-enable after 5 seconds to prevent permanent lock
        setTimeout(() => {
            submitBtn.innerHTML = originalText;
            submitBtn.disabled = false;
        }, 5000);
    });
});

function resendEmail() {
    const btn = event.target;
    const originalText = btn.innerHTML;
    
    btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i><?= t('auth.sending') ?>';
    btn.disabled = true;
    
    fetch('/auth/resend-password-reset', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({
            email: '<?= $email ?? '' ?>'
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert('success', data.message || '<?= t('auth.reset_email_resent') ?>');
        } else {
            showAlert('error', data.message || '<?= t('messages.error.general') ?>');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('error', '<?= t('messages.error.general') ?>');
    })
    .finally(() => {
        btn.innerHTML = originalText;
        btn.disabled = false;
    });
}
</script>

<style>
.forgot-password-container {
    max-width: 600px;
    margin: 2rem auto;
    padding: 0 1rem;
}

.forgot-password-page {
    background: linear-gradient(135deg, #17a2b8 0%, #138496 100%);
    min-height: 100vh;
    display: flex;
    align-items: center;
}

.card {
    border: none;
    border-radius: 15px;
}

.card-header {
    border-radius: 15px 15px 0 0 !important;
    padding: 2rem;
}

.input-group-text {
    background-color: #f8f9fa;
    border-right: none;
}

.form-control {
    border-left: none;
    padding-left: 0.5rem;
}

.form-control:focus {
    border-color: #17a2b8;
    box-shadow: 0 0 0 0.2rem rgba(23, 162, 184, 0.25);
}

.btn-info {
    background: linear-gradient(45deg, #17a2b8, #138496);
    border: none;
    border-radius: 25px;
    padding: 0.75rem 2rem;
}

.btn-info:hover {
    background: linear-gradient(45deg, #138496, #17a2b8);
    transform: translateY(-1px);
    box-shadow: 0 4px 15px rgba(23, 162, 184, 0.4);
}

.step-icon {
    font-weight: bold;
    font-size: 1.1rem;
}

.alert {
    border-radius: 10px;
}

@media (max-width: 768px) {
    .forgot-password-container {
        margin: 1rem auto;
    }
    
    .card-header {
        padding: 1.5rem 1rem;
    }
    
    .card-body {
        padding: 1.5rem 1rem;
    }
    
    .row .col-md-6 {
        margin-bottom: 1rem;
    }
}

/* Loading animation */
@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

.fa-spin {
    animation: spin 1s linear infinite;
}
</style>