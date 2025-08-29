<?php
/**
 * File: app/views/auth/reset-password.php
 * Purpose: Password reset form with secure token validation
 * Layout: Uses auth layout with professional password reset interface
 */

$this->layout('layouts/auth', [
    'title' => $page_title ?? t('auth.reset_password'),
    'body_class' => 'reset-password-page'
]);

$token = $token ?? '';
$email = $email ?? '';
$expired = $expired ?? false;
$invalid = $invalid ?? false;
?>

<div class="reset-password-container">
    <div class="card shadow-lg">
        <div class="card-header text-center bg-success text-white">
            <div class="mb-3">
                <i class="fas fa-lock-open fa-3x"></i>
            </div>
            <h4 class="mb-0"><?= t('auth.reset_password') ?></h4>
            <small><?= t('auth.create_new_password') ?></small>
        </div>
        
        <div class="card-body">
            <?php if ($expired): ?>
            <!-- Expired Token -->
            <div class="alert alert-danger">
                <div class="d-flex align-items-center">
                    <i class="fas fa-clock fa-2x me-3"></i>
                    <div>
                        <h6 class="mb-1"><?= t('auth.reset_link_expired') ?></h6>
                        <p class="mb-0"><?= t('auth.reset_link_expired_desc') ?></p>
                    </div>
                </div>
            </div>
            
            <div class="text-center">
                <a href="/auth/forgot-password" class="btn btn-primary btn-lg">
                    <i class="fas fa-redo me-2"></i><?= t('auth.request_new_link') ?>
                </a>
            </div>
            
            <?php elseif ($invalid): ?>
            <!-- Invalid Token -->
            <div class="alert alert-danger">
                <div class="d-flex align-items-center">
                    <i class="fas fa-exclamation-triangle fa-2x me-3"></i>
                    <div>
                        <h6 class="mb-1"><?= t('auth.invalid_reset_link') ?></h6>
                        <p class="mb-0"><?= t('auth.invalid_reset_link_desc') ?></p>
                    </div>
                </div>
            </div>
            
            <div class="text-center">
                <a href="/auth/forgot-password" class="btn btn-primary btn-lg">
                    <i class="fas fa-key me-2"></i><?= t('auth.request_new_reset') ?>
                </a>
            </div>
            
            <?php elseif (isset($success) && $success): ?>
            <!-- Success Message -->
            <div class="alert alert-success">
                <div class="d-flex align-items-center">
                    <i class="fas fa-check-circle fa-2x me-3"></i>
                    <div>
                        <h6 class="mb-1"><?= t('auth.password_reset_successful') ?></h6>
                        <p class="mb-0"><?= t('auth.password_reset_success_desc') ?></p>
                    </div>
                </div>
            </div>
            
            <div class="text-center">
                <a href="/auth/login" class="btn btn-success btn-lg">
                    <i class="fas fa-sign-in-alt me-2"></i><?= t('auth.proceed_to_login') ?>
                </a>
            </div>
            
            <!-- Security Notice -->
            <div class="alert alert-info mt-4">
                <h6><i class="fas fa-shield-alt me-2"></i><?= t('auth.security_notice') ?></h6>
                <ul class="mb-0">
                    <li><?= t('auth.password_changed_confirmation') ?></li>
                    <li><?= t('auth.all_sessions_logged_out') ?></li>
                    <li><?= t('auth.change_password_if_suspicious') ?></li>
                </ul>
            </div>
            
            <?php else: ?>
            <!-- Reset Form -->
            
            <?php if ($email): ?>
            <div class="alert alert-light border">
                <div class="d-flex align-items-center">
                    <i class="fas fa-user text-primary me-3"></i>
                    <div>
                        <strong><?= t('auth.resetting_password_for') ?>:</strong><br>
                        <span class="text-muted"><?= htmlspecialchars($email) ?></span>
                    </div>
                </div>
            </div>
            <?php endif; ?>
            
            <div class="row align-items-center mb-4">
                <div class="col-md-2 text-center">
                    <i class="fas fa-key fa-3x text-success"></i>
                </div>
                <div class="col-md-10">
                    <h6><?= t('auth.create_strong_password') ?></h6>
                    <p class="text-muted mb-0">
                        <?= t('auth.password_security_notice') ?>
                    </p>
                </div>
            </div>
            
            <form method="POST" action="/auth/reset-password" id="resetPasswordForm" novalidate>
                <?= $this->csrf() ?>
                <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">
                <input type="hidden" name="email" value="<?= htmlspecialchars($email) ?>">
                
                <!-- New Password -->
                <div class="mb-4">
                    <label for="password" class="form-label">
                        <?= t('auth.new_password') ?> *
                    </label>
                    <div class="input-group input-group-lg">
                        <span class="input-group-text">
                            <i class="fas fa-lock"></i>
                        </span>
                        <input type="password" class="form-control" id="password" name="password" required
                               placeholder="<?= t('auth.enter_new_password') ?>">
                        <button type="button" class="btn btn-outline-secondary" id="togglePassword">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                    <?= $this->error('password') ?>
                    
                    <!-- Password Strength Indicator -->
                    <div class="password-strength mt-2">
                        <div class="progress" style="height: 4px;">
                            <div class="progress-bar" id="passwordStrength" style="width: 0%"></div>
                        </div>
                        <small class="form-text" id="passwordHelp">
                            <?= t('auth.password_requirements') ?>
                        </small>
                    </div>
                </div>
                
                <!-- Confirm Password -->
                <div class="mb-4">
                    <label for="password_confirmation" class="form-label">
                        <?= t('auth.confirm_new_password') ?> *
                    </label>
                    <div class="input-group input-group-lg">
                        <span class="input-group-text">
                            <i class="fas fa-lock"></i>
                        </span>
                        <input type="password" class="form-control" id="password_confirmation" 
                               name="password_confirmation" required
                               placeholder="<?= t('auth.confirm_new_password_placeholder') ?>">
                        <span class="input-group-text" id="passwordMatch">
                            <i class="fas fa-times text-danger"></i>
                        </span>
                    </div>
                    <?= $this->error('password_confirmation') ?>
                </div>
                
                <!-- Password Requirements -->
                <div class="card bg-light mb-4">
                    <div class="card-body py-3">
                        <h6 class="card-title mb-2">
                            <i class="fas fa-info-circle me-2"></i><?= t('auth.password_must_contain') ?>:
                        </h6>
                        <div class="row">
                            <div class="col-md-6">
                                <ul class="list-unstyled mb-0">
                                    <li class="requirement" id="req-length">
                                        <i class="fas fa-times text-danger me-2"></i>
                                        <?= t('auth.min_8_characters') ?>
                                    </li>
                                    <li class="requirement" id="req-uppercase">
                                        <i class="fas fa-times text-danger me-2"></i>
                                        <?= t('auth.uppercase_letter') ?>
                                    </li>
                                </ul>
                            </div>
                            <div class="col-md-6">
                                <ul class="list-unstyled mb-0">
                                    <li class="requirement" id="req-lowercase">
                                        <i class="fas fa-times text-danger me-2"></i>
                                        <?= t('auth.lowercase_letter') ?>
                                    </li>
                                    <li class="requirement" id="req-number">
                                        <i class="fas fa-times text-danger me-2"></i>
                                        <?= t('auth.number_or_symbol') ?>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Security Options -->
                <div class="card border-primary mb-4">
                    <div class="card-body">
                        <h6 class="card-title text-primary">
                            <i class="fas fa-shield-alt me-2"></i><?= t('auth.additional_security') ?>
                        </h6>
                        
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" id="logout_all_devices" 
                                   name="logout_all_devices" value="1" checked>
                            <label class="form-check-label" for="logout_all_devices">
                                <?= t('auth.logout_all_devices') ?>
                            </label>
                            <small class="form-text text-muted d-block">
                                <?= t('auth.logout_all_devices_desc') ?>
                            </small>
                        </div>
                        
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" id="enable_2fa" 
                                   name="enable_2fa" value="1">
                            <label class="form-check-label" for="enable_2fa">
                                <?= t('auth.enable_2fa_option') ?>
                            </label>
                            <small class="form-text text-muted d-block">
                                <?= t('auth.enable_2fa_desc') ?>
                            </small>
                        </div>
                        
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="security_alert" 
                                   name="security_alert" value="1" checked>
                            <label class="form-check-label" for="security_alert">
                                <?= t('auth.send_security_alert') ?>
                            </label>
                            <small class="form-text text-muted d-block">
                                <?= t('auth.security_alert_desc') ?>
                            </small>
                        </div>
                    </div>
                </div>
                
                <!-- Generate Password Option -->
                <div class="mb-4">
                    <button type="button" class="btn btn-outline-primary" id="generatePassword">
                        <i class="fas fa-random me-2"></i><?= t('auth.generate_secure_password') ?>
                    </button>
                    <small class="form-text text-muted d-block mt-1">
                        <?= t('auth.generate_password_help') ?>
                    </small>
                </div>
                
                <!-- Submit Button -->
                <div class="d-grid">
                    <button type="submit" class="btn btn-success btn-lg" id="submitBtn">
                        <i class="fas fa-check me-2"></i><?= t('auth.reset_password') ?>
                    </button>
                </div>
            </form>
            
            <!-- Security Notice -->
            <div class="alert alert-warning mt-4">
                <i class="fas fa-exclamation-triangle me-2"></i>
                <strong><?= t('auth.important') ?>:</strong> <?= t('auth.reset_token_warning') ?>
            </div>
            
            <?php endif; ?>
        </div>
        
        <div class="card-footer text-center">
            <small class="text-muted">
                <?= t('auth.need_help') ?>
                <a href="mailto:<?= $support_email ?? 'support@company.com' ?>" class="text-decoration-none">
                    <?= t('auth.contact_support') ?>
                </a>
            </small>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('resetPasswordForm');
    const passwordInput = document.getElementById('password');
    const passwordConfirmInput = document.getElementById('password_confirmation');
    const toggleBtn = document.getElementById('togglePassword');
    const generateBtn = document.getElementById('generatePassword');
    
    if (!passwordInput) return; // Exit if form not present
    
    // Password strength checker
    passwordInput.addEventListener('input', function() {
        const password = this.value;
        checkPasswordStrength(password);
        checkPasswordMatch();
    });
    
    // Password confirmation checker
    passwordConfirmInput.addEventListener('input', checkPasswordMatch);
    
    // Password visibility toggle
    toggleBtn.addEventListener('click', function() {
        const type = passwordInput.type === 'password' ? 'text' : 'password';
        passwordInput.type = type;
        this.querySelector('i').className = type === 'password' ? 'fas fa-eye' : 'fas fa-eye-slash';
    });
    
    // Generate secure password
    generateBtn.addEventListener('click', function() {
        const password = generateSecurePassword();
        passwordInput.value = password;
        passwordConfirmInput.value = password;
        
        // Check strength and match
        checkPasswordStrength(password);
        checkPasswordMatch();
        
        // Show password temporarily
        passwordInput.type = 'text';
        passwordConfirmInput.type = 'text';
        toggleBtn.querySelector('i').className = 'fas fa-eye-slash';
        
        // Hide after 5 seconds
        setTimeout(() => {
            passwordInput.type = 'password';
            passwordConfirmInput.type = 'password';
            toggleBtn.querySelector('i').className = 'fas fa-eye';
        }, 5000);
        
        showAlert('success', '<?= t('auth.secure_password_generated') ?>');
    });
    
    // Form validation
    form.addEventListener('submit', function(e) {
        const password = passwordInput.value;
        const passwordConfirm = passwordConfirmInput.value;
        
        if (!validatePassword(password)) {
            e.preventDefault();
            showAlert('error', '<?= t('auth.password_requirements_not_met') ?>');
            return;
        }
        
        if (password !== passwordConfirm) {
            e.preventDefault();
            showAlert('error', '<?= t('auth.passwords_do_not_match') ?>');
            return;
        }
        
        // Show loading state
        const submitBtn = document.getElementById('submitBtn');
        const originalText = submitBtn.innerHTML;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i><?= t('auth.processing') ?>';
        submitBtn.disabled = true;
    });
    
    function checkPasswordStrength(password) {
        const strengthBar = document.getElementById('passwordStrength');
        const helpText = document.getElementById('passwordHelp');
        
        let strength = 0;
        let feedback = [];
        
        // Check requirements
        const requirements = {
            length: password.length >= 8,
            uppercase: /[A-Z]/.test(password),
            lowercase: /[a-z]/.test(password),
            number: /[\d\W]/.test(password)
        };
        
        // Update requirement indicators
        updateRequirement('req-length', requirements.length);
        updateRequirement('req-uppercase', requirements.uppercase);
        updateRequirement('req-lowercase', requirements.lowercase);
        updateRequirement('req-number', requirements.number);
        
        // Calculate strength
        Object.values(requirements).forEach(met => {
            if (met) strength += 25;
        });
        
        // Update strength bar
        strengthBar.style.width = strength + '%';
        
        if (strength === 100) {
            strengthBar.className = 'progress-bar bg-success';
            helpText.textContent = '<?= t('auth.password_strong') ?>';
            helpText.className = 'form-text text-success';
        } else if (strength >= 75) {
            strengthBar.className = 'progress-bar bg-info';
            helpText.textContent = '<?= t('auth.password_good') ?>';
            helpText.className = 'form-text text-info';
        } else if (strength >= 50) {
            strengthBar.className = 'progress-bar bg-warning';
            helpText.textContent = '<?= t('auth.password_fair') ?>';
            helpText.className = 'form-text text-warning';
        } else {
            strengthBar.className = 'progress-bar bg-danger';
            helpText.textContent = '<?= t('auth.password_weak') ?>';
            helpText.className = 'form-text text-danger';
        }
        
        return strength === 100;
    }
    
    function updateRequirement(elementId, met) {
        const element = document.getElementById(elementId);
        const icon = element.querySelector('i');
        
        if (met) {
            icon.className = 'fas fa-check text-success me-2';
            element.classList.add('text-success');
        } else {
            icon.className = 'fas fa-times text-danger me-2';
            element.classList.remove('text-success');
        }
    }
    
    function checkPasswordMatch() {
        const matchIcon = document.getElementById('passwordMatch');
        const password = passwordInput.value;
        const confirm = passwordConfirmInput.value;
        
        if (confirm && password) {
            if (password === confirm) {
                matchIcon.innerHTML = '<i class="fas fa-check text-success"></i>';
            } else {
                matchIcon.innerHTML = '<i class="fas fa-times text-danger"></i>';
            }
        } else {
            matchIcon.innerHTML = '<i class="fas fa-times text-danger"></i>';
        }
    }
    
    function validatePassword(password) {
        return password.length >= 8 && 
               /[A-Z]/.test(password) && 
               /[a-z]/.test(password) && 
               /[\d\W]/.test(password);
    }
    
    function generateSecurePassword() {
        const charset = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789!@#$%^&*';
        let password = '';
        
        // Ensure at least one character from each category
        password += 'ABCDEFGHIJKLMNOPQRSTUVWXYZ'[Math.floor(Math.random() * 26)];
        password += 'abcdefghijklmnopqrstuvwxyz'[Math.floor(Math.random() * 26)];
        password += '0123456789'[Math.floor(Math.random() * 10)];
        password += '!@#$%^&*'[Math.floor(Math.random() * 8)];
        
        // Fill the rest randomly
        for (let i = 4; i < 14; i++) {
            password += charset[Math.floor(Math.random() * charset.length)];
        }
        
        // Shuffle the password
        return password.split('').sort(() => 0.5 - Math.random()).join('');
    }
});
</script>

<style>
.reset-password-container {
    max-width: 700px;
    margin: 2rem auto;
    padding: 0 1rem;
}

.reset-password-page {
    background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
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
    border-color: #28a745;
    box-shadow: 0 0 0 0.2rem rgba(40, 167, 69, 0.25);
}

.btn-success {
    background: linear-gradient(45deg, #28a745, #20c997);
    border: none;
    border-radius: 25px;
    padding: 0.75rem 2rem;
}

.btn-success:hover {
    background: linear-gradient(45deg, #20c997, #28a745);
    transform: translateY(-1px);
    box-shadow: 0 4px 15px rgba(40, 167, 69, 0.4);
}

.progress-bar {
    transition: all 0.3s ease;
}

.requirement {
    font-size: 0.9rem;
    margin-bottom: 0.25rem;
    transition: all 0.3s ease;
}

.form-check-input:checked {
    background-color: #28a745;
    border-color: #28a745;
}

.alert {
    border-radius: 10px;
}

@media (max-width: 768px) {
    .reset-password-container {
        margin: 1rem auto;
    }
    
    .card-header {
        padding: 1.5rem 1rem;
    }
    
    .card-body {
        padding: 1.5rem 1rem;
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