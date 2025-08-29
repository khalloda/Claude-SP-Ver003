<?php
/**
 * File: app/views/auth/register.php
 * Purpose: User registration form with role assignment and validation
 * Layout: Uses auth layout with professional registration interface
 */

$this->layout('layouts/auth', [
    'title' => $page_title ?? t('auth.register'),
    'body_class' => 'register-page'
]);

$roles = $roles ?? [];
$departments = $departments ?? [];
$registration_enabled = $registration_enabled ?? true;
?>

<div class="register-container">
    <div class="card shadow-lg">
        <div class="card-header text-center bg-primary text-white">
            <div class="mb-3">
                <i class="fas fa-user-plus fa-3x"></i>
            </div>
            <h4 class="mb-0"><?= t('auth.create_account') ?></h4>
            <small><?= t('auth.register_subtitle') ?></small>
        </div>
        
        <div class="card-body">
            <?php if (!$registration_enabled): ?>
            <div class="alert alert-warning">
                <i class="fas fa-exclamation-triangle me-2"></i>
                <?= t('auth.registration_disabled') ?>
            </div>
            <?php else: ?>
            
            <form method="POST" action="/auth/register" id="registerForm" novalidate>
                <?= $this->csrf() ?>
                
                <!-- Personal Information -->
                <div class="mb-4">
                    <h6 class="text-muted border-bottom pb-2 mb-3">
                        <i class="fas fa-user me-2"></i><?= t('auth.personal_information') ?>
                    </h6>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="first_name" class="form-label">
                                    <?= t('auth.first_name') ?> *
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-user"></i></span>
                                    <input type="text" class="form-control" id="first_name" name="first_name" 
                                           value="<?= $this->old('first_name') ?>" required
                                           placeholder="<?= t('auth.first_name_placeholder') ?>">
                                </div>
                                <?= $this->error('first_name') ?>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="last_name" class="form-label">
                                    <?= t('auth.last_name') ?> *
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-user"></i></span>
                                    <input type="text" class="form-control" id="last_name" name="last_name" 
                                           value="<?= $this->old('last_name') ?>" required
                                           placeholder="<?= t('auth.last_name_placeholder') ?>">
                                </div>
                                <?= $this->error('last_name') ?>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="email" class="form-label">
                            <?= t('auth.email_address') ?> *
                        </label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                            <input type="email" class="form-control" id="email" name="email" 
                                   value="<?= $this->old('email') ?>" required
                                   placeholder="<?= t('auth.email_placeholder') ?>">
                        </div>
                        <?= $this->error('email') ?>
                        <small class="form-text text-muted">
                            <?= t('auth.email_verification_notice') ?>
                        </small>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="phone" class="form-label"><?= t('auth.phone') ?></label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-phone"></i></span>
                                    <input type="tel" class="form-control" id="phone" name="phone" 
                                           value="<?= $this->old('phone') ?>"
                                           placeholder="<?= t('auth.phone_placeholder') ?>">
                                </div>
                                <?= $this->error('phone') ?>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="department_id" class="form-label"><?= t('auth.department') ?></label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-building"></i></span>
                                    <select class="form-select" id="department_id" name="department_id">
                                        <option value=""><?= t('auth.select_department') ?></option>
                                        <?php foreach ($departments as $department): ?>
                                        <option value="<?= $department->id ?>" 
                                                <?= $this->selected('department_id', $department->id) ?>>
                                            <?= htmlspecialchars($department->name) ?>
                                        </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <?= $this->error('department_id') ?>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Account Credentials -->
                <div class="mb-4">
                    <h6 class="text-muted border-bottom pb-2 mb-3">
                        <i class="fas fa-key me-2"></i><?= t('auth.account_credentials') ?>
                    </h6>
                    
                    <div class="mb-3">
                        <label for="username" class="form-label">
                            <?= t('auth.username') ?> *
                        </label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-at"></i></span>
                            <input type="text" class="form-control" id="username" name="username" 
                                   value="<?= $this->old('username') ?>" required
                                   placeholder="<?= t('auth.username_placeholder') ?>">
                            <button type="button" class="btn btn-outline-secondary" id="generateUsername">
                                <i class="fas fa-magic"></i> <?= t('auth.generate') ?>
                            </button>
                        </div>
                        <?= $this->error('username') ?>
                        <small class="form-text text-muted">
                            <?= t('auth.username_requirements') ?>
                        </small>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="password" class="form-label">
                                    <?= t('auth.password') ?> *
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                    <input type="password" class="form-control" id="password" name="password" required
                                           placeholder="<?= t('auth.password_placeholder') ?>">
                                    <button type="button" class="btn btn-outline-secondary" id="togglePassword">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                                <?= $this->error('password') ?>
                                <div class="password-strength mt-2">
                                    <div class="progress" style="height: 3px;">
                                        <div class="progress-bar" id="passwordStrength" style="width: 0%"></div>
                                    </div>
                                    <small class="form-text text-muted" id="passwordHelp">
                                        <?= t('auth.password_requirements') ?>
                                    </small>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="password_confirmation" class="form-label">
                                    <?= t('auth.confirm_password') ?> *
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                    <input type="password" class="form-control" id="password_confirmation" 
                                           name="password_confirmation" required
                                           placeholder="<?= t('auth.confirm_password_placeholder') ?>">
                                    <span class="input-group-text" id="passwordMatch">
                                        <i class="fas fa-times text-danger"></i>
                                    </span>
                                </div>
                                <?= $this->error('password_confirmation') ?>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <button type="button" class="btn btn-sm btn-outline-primary" id="generatePassword">
                            <i class="fas fa-random"></i> <?= t('auth.generate_secure_password') ?>
                        </button>
                    </div>
                </div>
                
                <!-- Role Assignment -->
                <?php if ($this->hasRole(['admin', 'manager'])): ?>
                <div class="mb-4">
                    <h6 class="text-muted border-bottom pb-2 mb-3">
                        <i class="fas fa-user-tag me-2"></i><?= t('auth.role_assignment') ?>
                    </h6>
                    
                    <div class="mb-3">
                        <label for="role" class="form-label"><?= t('auth.primary_role') ?> *</label>
                        <select class="form-select" id="role" name="role" required>
                            <option value=""><?= t('auth.select_role') ?></option>
                            <?php foreach ($roles as $role): ?>
                            <option value="<?= $role->slug ?>" 
                                    data-description="<?= htmlspecialchars($role->description) ?>"
                                    <?= $this->selected('role', $role->slug) ?>>
                                <?= htmlspecialchars($role->name) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                        <?= $this->error('role') ?>
                        <small class="form-text text-muted" id="roleDescription">
                            <?= t('auth.select_role_to_see_description') ?>
                        </small>
                    </div>
                    
                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="send_welcome_email" 
                                   name="send_welcome_email" value="1" checked>
                            <label class="form-check-label" for="send_welcome_email">
                                <?= t('auth.send_welcome_email') ?>
                            </label>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="require_password_change" 
                                   name="require_password_change" value="1">
                            <label class="form-check-label" for="require_password_change">
                                <?= t('auth.require_password_change') ?>
                            </label>
                        </div>
                    </div>
                </div>
                <?php else: ?>
                <input type="hidden" name="role" value="user">
                <?php endif; ?>
                
                <!-- Terms and Conditions -->
                <div class="mb-4">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="agree_terms" name="agree_terms" 
                               value="1" required>
                        <label class="form-check-label" for="agree_terms">
                            <?= t('auth.agree_terms_start') ?>
                            <a href="/legal/terms" target="_blank"><?= t('auth.terms_of_service') ?></a>
                            <?= t('auth.and') ?>
                            <a href="/legal/privacy" target="_blank"><?= t('auth.privacy_policy') ?></a> *
                        </label>
                    </div>
                    <?= $this->error('agree_terms') ?>
                </div>
                
                <!-- Marketing Preferences -->
                <div class="mb-4">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="marketing_emails" 
                               name="marketing_emails" value="1">
                        <label class="form-check-label" for="marketing_emails">
                            <?= t('auth.marketing_emails_consent') ?>
                        </label>
                    </div>
                </div>
                
                <!-- Submit Button -->
                <div class="d-grid">
                    <button type="submit" class="btn btn-primary btn-lg">
                        <i class="fas fa-user-plus me-2"></i><?= t('auth.create_account') ?>
                    </button>
                </div>
            </form>
            
            <?php endif; ?>
        </div>
        
        <div class="card-footer text-center">
            <p class="mb-0">
                <?= t('auth.already_have_account') ?>
                <a href="/auth/login" class="text-decoration-none">
                    <?= t('auth.sign_in_here') ?>
                </a>
            </p>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('registerForm');
    const firstNameInput = document.getElementById('first_name');
    const lastNameInput = document.getElementById('last_name');
    const emailInput = document.getElementById('email');
    const usernameInput = document.getElementById('username');
    const passwordInput = document.getElementById('password');
    const passwordConfirmInput = document.getElementById('password_confirmation');
    const roleSelect = document.getElementById('role');
    const roleDescription = document.getElementById('roleDescription');
    
    // Generate username automatically
    function generateUsername() {
        const firstName = firstNameInput.value.toLowerCase().replace(/[^a-z]/g, '');
        const lastName = lastNameInput.value.toLowerCase().replace(/[^a-z]/g, '');
        
        if (firstName && lastName) {
            const username = firstName.charAt(0) + lastName;
            const randomNum = Math.floor(Math.random() * 99) + 1;
            usernameInput.value = username + (username.length < 4 ? randomNum : '');
        }
    }
    
    // Auto-generate username when name fields change
    firstNameInput.addEventListener('blur', generateUsername);
    lastNameInput.addEventListener('blur', generateUsername);
    
    // Manual username generation
    document.getElementById('generateUsername').addEventListener('click', generateUsername);
    
    // Password strength checker
    passwordInput.addEventListener('input', function() {
        const password = this.value;
        const strengthBar = document.getElementById('passwordStrength');
        const helpText = document.getElementById('passwordHelp');
        
        let strength = 0;
        let feedback = [];
        
        // Length check
        if (password.length >= 8) strength += 25;
        else feedback.push('<?= t('auth.password_length_requirement') ?>');
        
        // Uppercase check
        if (/[A-Z]/.test(password)) strength += 25;
        else feedback.push('<?= t('auth.password_uppercase_requirement') ?>');
        
        // Lowercase check
        if (/[a-z]/.test(password)) strength += 25;
        else feedback.push('<?= t('auth.password_lowercase_requirement') ?>');
        
        // Number or symbol check
        if (/[\d\W]/.test(password)) strength += 25;
        else feedback.push('<?= t('auth.password_special_requirement') ?>');
        
        // Update strength bar
        strengthBar.style.width = strength + '%';
        
        if (strength === 100) {
            strengthBar.className = 'progress-bar bg-success';
            helpText.textContent = '<?= t('auth.password_strong') ?>';
            helpText.className = 'form-text text-success';
        } else if (strength >= 50) {
            strengthBar.className = 'progress-bar bg-warning';
            helpText.textContent = '<?= t('auth.password_medium') ?>: ' + feedback.join(', ');
            helpText.className = 'form-text text-warning';
        } else {
            strengthBar.className = 'progress-bar bg-danger';
            helpText.textContent = '<?= t('auth.password_weak') ?>: ' + feedback.join(', ');
            helpText.className = 'form-text text-danger';
        }
    });
    
    // Password confirmation check
    function checkPasswordMatch() {
        const matchIcon = document.getElementById('passwordMatch');
        if (passwordConfirmInput.value && passwordInput.value) {
            if (passwordInput.value === passwordConfirmInput.value) {
                matchIcon.innerHTML = '<i class="fas fa-check text-success"></i>';
            } else {
                matchIcon.innerHTML = '<i class="fas fa-times text-danger"></i>';
            }
        } else {
            matchIcon.innerHTML = '<i class="fas fa-times text-danger"></i>';
        }
    }
    
    passwordInput.addEventListener('input', checkPasswordMatch);
    passwordConfirmInput.addEventListener('input', checkPasswordMatch);
    
    // Password visibility toggle
    document.getElementById('togglePassword').addEventListener('click', function() {
        const type = passwordInput.type === 'password' ? 'text' : 'password';
        passwordInput.type = type;
        this.querySelector('i').className = type === 'password' ? 'fas fa-eye' : 'fas fa-eye-slash';
    });
    
    // Generate secure password
    document.getElementById('generatePassword').addEventListener('click', function() {
        const charset = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789!@#$%^&*';
        let password = '';
        
        // Ensure at least one character from each category
        password += 'ABCDEFGHIJKLMNOPQRSTUVWXYZ'[Math.floor(Math.random() * 26)];
        password += 'abcdefghijklmnopqrstuvwxyz'[Math.floor(Math.random() * 26)];
        password += '0123456789'[Math.floor(Math.random() * 10)];
        password += '!@#$%^&*'[Math.floor(Math.random() * 8)];
        
        // Fill the rest randomly
        for (let i = 4; i < 12; i++) {
            password += charset[Math.floor(Math.random() * charset.length)];
        }
        
        // Shuffle the password
        password = password.split('').sort(() => 0.5 - Math.random()).join('');
        
        passwordInput.value = password;
        passwordConfirmInput.value = password;
        passwordInput.dispatchEvent(new Event('input'));
        checkPasswordMatch();
        
        // Show the generated password temporarily
        const originalType = passwordInput.type;
        passwordInput.type = 'text';
        setTimeout(() => {
            passwordInput.type = originalType;
        }, 3000);
    });
    
    // Role description update
    if (roleSelect) {
        roleSelect.addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            const description = selectedOption.getAttribute('data-description');
            roleDescription.textContent = description || '<?= t('auth.select_role_to_see_description') ?>';
        });
    }
    
    // Form validation
    form.addEventListener('submit', function(e) {
        let isValid = true;
        const requiredFields = form.querySelectorAll('[required]');
        
        requiredFields.forEach(field => {
            if (!field.value.trim()) {
                isValid = false;
                field.classList.add('is-invalid');
            } else {
                field.classList.remove('is-invalid');
            }
        });
        
        // Email validation
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(emailInput.value)) {
            isValid = false;
            emailInput.classList.add('is-invalid');
        }
        
        // Username validation
        const usernameRegex = /^[a-zA-Z0-9_]{3,20}$/;
        if (!usernameRegex.test(usernameInput.value)) {
            isValid = false;
            usernameInput.classList.add('is-invalid');
        }
        
        // Password match validation
        if (passwordInput.value !== passwordConfirmInput.value) {
            isValid = false;
            passwordConfirmInput.classList.add('is-invalid');
        }
        
        if (!isValid) {
            e.preventDefault();
            showAlert('error', '<?= t('auth.form_validation_errors') ?>');
        }
    });
    
    // Real-time field validation
    emailInput.addEventListener('blur', function() {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        this.classList.toggle('is-invalid', !emailRegex.test(this.value));
    });
    
    usernameInput.addEventListener('blur', function() {
        const usernameRegex = /^[a-zA-Z0-9_]{3,20}$/;
        this.classList.toggle('is-invalid', !usernameRegex.test(this.value));
    });
});
</script>

<style>
.register-container {
    max-width: 800px;
    margin: 2rem auto;
    padding: 0 1rem;
}

.register-page {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
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
    border-color: #667eea;
    box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
}

.btn-primary {
    background: linear-gradient(45deg, #667eea, #764ba2);
    border: none;
    border-radius: 25px;
    padding: 0.75rem 2rem;
}

.btn-primary:hover {
    background: linear-gradient(45deg, #764ba2, #667eea);
    transform: translateY(-1px);
    box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
}

.progress-bar {
    transition: all 0.3s ease;
}

.form-check-input:checked {
    background-color: #667eea;
    border-color: #667eea;
}

@media (max-width: 768px) {
    .register-container {
        margin: 1rem auto;
    }
    
    .card-header {
        padding: 1.5rem 1rem;
    }
    
    .card-body {
        padding: 1.5rem 1rem;
    }
}
</style>