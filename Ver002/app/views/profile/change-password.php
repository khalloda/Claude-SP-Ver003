<?php
/**
 * File: app/views/profile/change-password.php
 * Purpose: Password change interface with security validation
 * Layout: Uses app layout with security-focused design
 */

$this->layout('layouts/app', [
    'title' => $page_title ?? t('profile.change_password'),
    'active_nav' => 'profile'
]);

$currentUser = $this->getCurrentUser();
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">
            <i class="fas fa-key me-2"></i><?= t('profile.change_password') ?>
        </h1>
        <div class="btn-toolbar mb-2 mb-md-0">
            <a href="/profile" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left"></i> <?= t('common.back') ?>
            </a>
        </div>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-6">
            <!-- Password Change Form -->
            <div class="card">
                <div class="card-header bg-warning text-dark">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-shield-alt me-2"></i><?= t('profile.password_security') ?>
                    </h5>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        <?= t('profile.password_change_info') ?>
                    </div>

                    <form id="changePasswordForm" method="POST" action="/profile/change-password">
                        <!-- Current Password -->
                        <div class="mb-4">
                            <label for="current_password" class="form-label">
                                <?= t('profile.current_password') ?> <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <input type="password" class="form-control" id="current_password" name="current_password" required>
                                <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('current_password')">
                                    <i class="fas fa-eye" id="current_password_icon"></i>
                                </button>
                            </div>
                            <div class="form-text"><?= t('profile.current_password_help') ?></div>
                            <div class="invalid-feedback" id="current_password_error"></div>
                        </div>

                        <!-- New Password -->
                        <div class="mb-3">
                            <label for="new_password" class="form-label">
                                <?= t('profile.new_password') ?> <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <input type="password" class="form-control" id="new_password" name="new_password" required
                                       onkeyup="checkPasswordStrength(this.value)">
                                <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('new_password')">
                                    <i class="fas fa-eye" id="new_password_icon"></i>
                                </button>
                            </div>
                            
                            <!-- Password Strength Indicator -->
                            <div class="password-strength mt-2">
                                <div class="progress" style="height: 6px;">
                                    <div class="progress-bar" id="strength_bar" role="progressbar" style="width: 0%"></div>
                                </div>
                                <small class="form-text" id="strength_text"><?= t('profile.password_strength') ?>: <?= t('profile.strength.none') ?></small>
                            </div>
                            
                            <div class="invalid-feedback" id="new_password_error"></div>
                        </div>

                        <!-- Confirm New Password -->
                        <div class="mb-4">
                            <label for="confirm_password" class="form-label">
                                <?= t('profile.confirm_new_password') ?> <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <input type="password" class="form-control" id="confirm_password" name="confirm_password" required
                                       onkeyup="checkPasswordMatch()">
                                <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('confirm_password')">
                                    <i class="fas fa-eye" id="confirm_password_icon"></i>
                                </button>
                            </div>
                            <div class="invalid-feedback" id="confirm_password_error"></div>
                        </div>

                        <!-- Password Requirements -->
                        <div class="card bg-light mb-4">
                            <div class="card-body py-3">
                                <h6 class="card-title"><?= t('profile.password_requirements') ?>:</h6>
                                <div class="row">
                                    <div class="col-md-6">
                                        <ul class="small mb-0" id="requirements_list">
                                            <li id="req_length" class="text-muted">
                                                <i class="fas fa-times me-2"></i><?= t('profile.req.min_8_chars') ?>
                                            </li>
                                            <li id="req_lowercase" class="text-muted">
                                                <i class="fas fa-times me-2"></i><?= t('profile.req.lowercase') ?>
                                            </li>
                                            <li id="req_uppercase" class="text-muted">
                                                <i class="fas fa-times me-2"></i><?= t('profile.req.uppercase') ?>
                                            </li>
                                        </ul>
                                    </div>
                                    <div class="col-md-6">
                                        <ul class="small mb-0">
                                            <li id="req_number" class="text-muted">
                                                <i class="fas fa-times me-2"></i><?= t('profile.req.number') ?>
                                            </li>
                                            <li id="req_special" class="text-muted">
                                                <i class="fas fa-times me-2"></i><?= t('profile.req.special_char') ?>
                                            </li>
                                            <li id="req_different" class="text-muted">
                                                <i class="fas fa-times me-2"></i><?= t('profile.req.different_current') ?>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Additional Security Options -->
                        <div class="card mb-4">
                            <div class="card-header">
                                <h6 class="card-title mb-0"><?= t('profile.additional_security') ?></h6>
                            </div>
                            <div class="card-body">
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="checkbox" id="logout_other_sessions" name="logout_other_sessions" checked>
                                    <label class="form-check-label" for="logout_other_sessions">
                                        <?= t('profile.logout_other_sessions') ?>
                                    </label>
                                    <div class="form-text"><?= t('profile.logout_other_sessions_help') ?></div>
                                </div>
                                
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="email_notification" name="email_notification" checked>
                                    <label class="form-check-label" for="email_notification">
                                        <?= t('profile.email_notification') ?>
                                    </label>
                                    <div class="form-text"><?= t('profile.email_notification_help') ?></div>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-warning" id="change_password_btn" disabled>
                                <i class="fas fa-key"></i> <?= t('profile.change_password') ?>
                            </button>
                            <a href="/profile" class="btn btn-secondary">
                                <?= t('common.cancel') ?>
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Security Information Sidebar -->
        <div class="col-lg-4">
            <!-- Security Tips -->
            <div class="card">
                <div class="card-header">
                    <h6 class="card-title mb-0">
                        <i class="fas fa-shield-alt me-2"></i><?= t('profile.security_tips') ?>
                    </h6>
                </div>
                <div class="card-body">
                    <div class="small">
                        <h6><?= t('profile.strong_password_tips') ?>:</h6>
                        <ul class="mb-3">
                            <li><?= t('profile.tip.unique_password') ?></li>
                            <li><?= t('profile.tip.mix_characters') ?></li>
                            <li><?= t('profile.tip.avoid_personal') ?></li>
                            <li><?= t('profile.tip.use_passphrase') ?></li>
                        </ul>
                        
                        <h6><?= t('profile.security_best_practices') ?>:</h6>
                        <ul class="mb-0">
                            <li><?= t('profile.tip.change_regularly') ?></li>
                            <li><?= t('profile.tip.dont_share') ?></li>
                            <li><?= t('profile.tip.use_2fa') ?></li>
                            <li><?= t('profile.tip.secure_storage') ?></li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Password History -->
            <div class="card mt-3">
                <div class="card-header">
                    <h6 class="card-title mb-0">
                        <i class="fas fa-history me-2"></i><?= t('profile.password_history') ?>
                    </h6>
                </div>
                <div class="card-body">
                    <div class="small">
                        <div class="d-flex justify-content-between mb-2">
                            <span><?= t('profile.last_changed') ?>:</span>
                            <span class="text-muted">
                                <?= $user->password_changed_at ? date('M j, Y', strtotime($user->password_changed_at)) : t('common.never') ?>
                            </span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span><?= t('profile.days_since_change') ?>:</span>
                            <span class="badge bg-<?= $this->getPasswordAgeBadge($user->password_changed_at) ?>">
                                <?= $this->getPasswordAge($user->password_changed_at) ?> <?= t('profile.days') ?>
                            </span>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span><?= t('profile.password_strength_current') ?>:</span>
                            <span class="badge bg-<?= $user->password_strength_color ?? 'secondary' ?>">
                                <?= t('profile.strength.' . ($user->password_strength ?? 'unknown')) ?>
                            </span>
                        </div>
                    </div>
                    
                    <?php if ($this->getPasswordAge($user->password_changed_at) > 90): ?>
                    <div class="alert alert-warning mt-3 p-2">
                        <small>
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            <?= t('profile.password_old_warning') ?>
                        </small>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Two-Factor Authentication -->
            <div class="card mt-3">
                <div class="card-header">
                    <h6 class="card-title mb-0">
                        <i class="fas fa-mobile-alt me-2"></i><?= t('profile.two_factor_auth') ?>
                    </h6>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span><?= t('profile.2fa_status') ?>:</span>
                        <span class="badge bg-<?= $user->two_factor_enabled ? 'success' : 'danger' ?>">
                            <?= $user->two_factor_enabled ? t('common.enabled') : t('common.disabled') ?>
                        </span>
                    </div>
                    
                    <?php if (!$user->two_factor_enabled): ?>
                    <div class="alert alert-info p-2">
                        <small>
                            <i class="fas fa-info-circle me-2"></i>
                            <?= t('profile.2fa_recommendation') ?>
                        </small>
                    </div>
                    <a href="/profile/2fa" class="btn btn-sm btn-outline-success w-100">
                        <i class="fas fa-plus"></i> <?= t('profile.enable_2fa') ?>
                    </a>
                    <?php else: ?>
                    <a href="/profile/2fa" class="btn btn-sm btn-outline-primary w-100">
                        <i class="fas fa-cog"></i> <?= t('profile.manage_2fa') ?>
                    </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Password strength checker
function checkPasswordStrength(password) {
    let score = 0;
    let feedback = [];
    
    // Length check
    const lengthReq = document.getElementById('req_length');
    if (password.length >= 8) {
        score += 20;
        lengthReq.className = 'text-success';
        lengthReq.innerHTML = '<i class="fas fa-check me-2"></i><?= t('profile.req.min_8_chars') ?>';
    } else {
        lengthReq.className = 'text-muted';
        lengthReq.innerHTML = '<i class="fas fa-times me-2"></i><?= t('profile.req.min_8_chars') ?>';
    }
    
    // Lowercase check
    const lowercaseReq = document.getElementById('req_lowercase');
    if (/[a-z]/.test(password)) {
        score += 20;
        lowercaseReq.className = 'text-success';
        lowercaseReq.innerHTML = '<i class="fas fa-check me-2"></i><?= t('profile.req.lowercase') ?>';
    } else {
        lowercaseReq.className = 'text-muted';
        lowercaseReq.innerHTML = '<i class="fas fa-times me-2"></i><?= t('profile.req.lowercase') ?>';
    }
    
    // Uppercase check
    const uppercaseReq = document.getElementById('req_uppercase');
    if (/[A-Z]/.test(password)) {
        score += 20;
        uppercaseReq.className = 'text-success';
        uppercaseReq.innerHTML = '<i class="fas fa-check me-2"></i><?= t('profile.req.uppercase') ?>';
    } else {
        uppercaseReq.className = 'text-muted';
        uppercaseReq.innerHTML = '<i class="fas fa-times me-2"></i><?= t('profile.req.uppercase') ?>';
    }
    
    // Number check
    const numberReq = document.getElementById('req_number');
    if (/\d/.test(password)) {
        score += 20;
        numberReq.className = 'text-success';
        numberReq.innerHTML = '<i class="fas fa-check me-2"></i><?= t('profile.req.number') ?>';
    } else {
        numberReq.className = 'text-muted';
        numberReq.innerHTML = '<i class="fas fa-times me-2"></i><?= t('profile.req.number') ?>';
    }
    
    // Special character check
    const specialReq = document.getElementById('req_special');
    if (/[!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?]/.test(password)) {
        score += 20;
        specialReq.className = 'text-success';
        specialReq.innerHTML = '<i class="fas fa-check me-2"></i><?= t('profile.req.special_char') ?>';
    } else {
        specialReq.className = 'text-muted';
        specialReq.innerHTML = '<i class="fas fa-times me-2"></i><?= t('profile.req.special_char') ?>';
    }
    
    // Update strength bar and text
    const strengthBar = document.getElementById('strength_bar');
    const strengthText = document.getElementById('strength_text');
    
    let strengthClass = 'bg-danger';
    let strengthLabel = '<?= t('profile.strength.weak') ?>';
    
    if (score >= 80) {
        strengthClass = 'bg-success';
        strengthLabel = '<?= t('profile.strength.strong') ?>';
    } else if (score >= 60) {
        strengthClass = 'bg-warning';
        strengthLabel = '<?= t('profile.strength.medium') ?>';
    } else if (score >= 40) {
        strengthClass = 'bg-info';
        strengthLabel = '<?= t('profile.strength.fair') ?>';
    }
    
    strengthBar.className = `progress-bar ${strengthClass}`;
    strengthBar.style.width = score + '%';
    strengthText.innerHTML = `<?= t('profile.password_strength') ?>: ${strengthLabel}`;
    
    // Enable/disable submit button
    const submitBtn = document.getElementById('change_password_btn');
    const confirmPassword = document.getElementById('confirm_password').value;
    
    if (score >= 80 && password === confirmPassword && password.length > 0) {
        submitBtn.disabled = false;
        submitBtn.className = 'btn btn-warning';
    } else {
        submitBtn.disabled = true;
        submitBtn.className = 'btn btn-secondary';
    }
    
    // Check password match
    checkPasswordMatch();
}

// Check password match
function checkPasswordMatch() {
    const newPassword = document.getElementById('new_password').value;
    const confirmPassword = document.getElementById('confirm_password').value;
    const confirmField = document.getElementById('confirm_password');
    const errorDiv = document.getElementById('confirm_password_error');
    
    if (confirmPassword.length > 0) {
        if (newPassword === confirmPassword) {
            confirmField.classList.remove('is-invalid');
            confirmField.classList.add('is-valid');
            errorDiv.textContent = '';
        } else {
            confirmField.classList.remove('is-valid');
            confirmField.classList.add('is-invalid');
            errorDiv.textContent = '<?= t('profile.passwords_dont_match') ?>';
        }
    } else {
        confirmField.classList.remove('is-valid', 'is-invalid');
        errorDiv.textContent = '';
    }
}

// Toggle password visibility
function togglePassword(fieldId) {
    const field = document.getElementById(fieldId);
    const icon = document.getElementById(fieldId + '_icon');
    
    if (field.type === 'password') {
        field.type = 'text';
        icon.className = 'fas fa-eye-slash';
    } else {
        field.type = 'password';
        icon.className = 'fas fa-eye';
    }
}

// Validate current password
async function validateCurrentPassword() {
    const currentPassword = document.getElementById('current_password').value;
    
    if (currentPassword.length < 1) return;
    
    try {
        const response = await fetch('/api/profile/validate-password', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({ password: currentPassword })
        });
        
        const data = await response.json();
        const field = document.getElementById('current_password');
        const error = document.getElementById('current_password_error');
        const differentReq = document.getElementById('req_different');
        
        if (data.valid) {
            field.classList.remove('is-invalid');
            field.classList.add('is-valid');
            error.textContent = '';
            
            // Check if new password is different
            const newPassword = document.getElementById('new_password').value;
            if (newPassword && newPassword !== currentPassword) {
                differentReq.className = 'text-success';
                differentReq.innerHTML = '<i class="fas fa-check me-2"></i><?= t('profile.req.different_current') ?>';
            } else if (newPassword === currentPassword) {
                differentReq.className = 'text-danger';
                differentReq.innerHTML = '<i class="fas fa-times me-2"></i><?= t('profile.req.different_current') ?>';
            }
        } else {
            field.classList.remove('is-valid');
            field.classList.add('is-invalid');
            error.textContent = data.message || '<?= t('profile.invalid_current_password') ?>';
        }
    } catch (error) {
        console.error('Password validation error:', error);
    }
}

// Form submission
document.addEventListener('DOMContentLoaded', function() {
    // Validate current password on blur
    document.getElementById('current_password').addEventListener('blur', validateCurrentPassword);
    
    // Check password difference when new password changes
    document.getElementById('new_password').addEventListener('input', function() {
        const currentPassword = document.getElementById('current_password').value;
        const newPassword = this.value;
        const differentReq = document.getElementById('req_different');
        
        if (currentPassword && newPassword) {
            if (newPassword !== currentPassword) {
                differentReq.className = 'text-success';
                differentReq.innerHTML = '<i class="fas fa-check me-2"></i><?= t('profile.req.different_current') ?>';
            } else {
                differentReq.className = 'text-danger';
                differentReq.innerHTML = '<i class="fas fa-times me-2"></i><?= t('profile.req.different_current') ?>';
            }
        }
    });
    
    // Form submission
    document.getElementById('changePasswordForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const submitBtn = document.getElementById('change_password_btn');
        if (submitBtn.disabled) {
            showAlert('error', '<?= t('profile.password_requirements_not_met') ?>');
            return;
        }
        
        // Confirm password change
        if (confirm('<?= t('profile.confirm_password_change') ?>')) {
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> <?= t('profile.changing_password') ?>';
            submitBtn.disabled = true;
            this.submit();
        }
    });
});

<?php
// Helper functions
$this->extend('getPasswordAge', function($passwordChangedAt) {
    if (!$passwordChangedAt) return 0;
    return floor((time() - strtotime($passwordChangedAt)) / (60 * 60 * 24));
});

$this->extend('getPasswordAgeBadge', function($passwordChangedAt) {
    $days = $this->getPasswordAge($passwordChangedAt);
    if ($days > 90) return 'danger';
    if ($days > 60) return 'warning';
    if ($days > 30) return 'info';
    return 'success';
});
?>
</script>

<style>
.password-strength .progress {
    height: 6px;
}

.form-check-input:checked {
    background-color: #ffc107;
    border-color: #ffc107;
}

.btn-warning:disabled {
    background-color: #6c757d;
    border-color: #6c757d;
}

.card-header.bg-warning {
    border-bottom: 1px solid rgba(0,0,0,.125);
}

#requirements_list li.text-success {
    font-weight: 500;
}

.input-group .btn-outline-secondary {
    border-color: #ced4da;
}

.input-group .btn-outline-secondary:hover {
    background-color: #e9ecef;
}
</style>