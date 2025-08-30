<?php
$page_title = t('auth.login');
ob_start();
?>

<div class="row justify-content-center">
    <div class="col-md-6 col-lg-4">
        <div class="card shadow-sm">
            <div class="card-header text-center bg-primary text-white">
                <h4 class="mb-0">
                    <i class="fas fa-sign-in-alt me-2"></i>
                    <?= t('auth.login') ?>
                </h4>
            </div>
            
            <div class="card-body p-4">
                <!-- Language Switcher -->
                <div class="text-end mb-3">
                    <div class="dropdown">
                        <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                            <i class="fas fa-globe me-1"></i>
                            <?= \App\Core\I18n::getCurrentLanguage()['name'] ?>
                        </button>
                        <ul class="dropdown-menu">
                            <?php foreach (\App\Core\I18n::getAvailableLanguages() as $code => $lang): ?>
                                <li>
                                    <a class="dropdown-item <?= $current_lang === $code ? 'active' : '' ?>" 
                                       href="<?= \App\Core\I18n::getLanguageSwitchUrl($code) ?>">
                                        <?= htmlspecialchars($lang['native']) ?>
                                    </a>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>

                <form method="POST" action="/login" id="login-form">
                    <input type="hidden" name="_token" value="<?= htmlspecialchars($csrf_token) ?>">
                    
                    <!-- Email Field -->
                    <div class="mb-3">
                        <label for="email" class="form-label">
                            <i class="fas fa-envelope me-1"></i>
                            <?= t('auth.email') ?>
                        </label>
                        <input type="email" 
                               class="form-control <?= isset($errors['email']) ? 'is-invalid' : '' ?>" 
                               id="email" 
                               name="email" 
                               value="<?= htmlspecialchars(old('email')) ?>"
                               required 
                               autofocus
                               placeholder="<?= t('auth.email_placeholder') ?>">
                        <?php if (isset($errors['email'])): ?>
                            <div class="invalid-feedback">
                                <?= htmlspecialchars($errors['email']) ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Password Field -->
                    <div class="mb-3">
                        <label for="password" class="form-label">
                            <i class="fas fa-lock me-1"></i>
                            <?= t('auth.password') ?>
                        </label>
                        <div class="input-group">
                            <input type="password" 
                                   class="form-control <?= isset($errors['password']) ? 'is-invalid' : '' ?>" 
                                   id="password" 
                                   name="password" 
                                   required
                                   placeholder="<?= t('auth.password_placeholder') ?>">
                            <button class="btn btn-outline-secondary" 
                                    type="button" 
                                    id="toggle-password"
                                    title="<?= t('auth.show_password') ?>">
                                <i class="fas fa-eye"></i>
                            </button>
                            <?php if (isset($errors['password'])): ?>
                                <div class="invalid-feedback">
                                    <?= htmlspecialchars($errors['password']) ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Remember Me -->
                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" 
                                   type="checkbox" 
                                   id="remember" 
                                   name="remember" 
                                   value="1"
                                   <?= old('remember') ? 'checked' : '' ?>>
                            <label class="form-check-label" for="remember">
                                <?= t('auth.remember_me') ?>
                            </label>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="fas fa-sign-in-alt me-2"></i>
                            <?= t('auth.login') ?>
                        </button>
                    </div>
                </form>

                <!-- Additional Links -->
                <div class="text-center mt-3">
                    <a href="/forgot-password" class="text-muted">
                        <i class="fas fa-question-circle me-1"></i>
                        <?= t('auth.forgot_password') ?>
                    </a>
                </div>
            </div>
            
            <div class="card-footer text-center text-muted">
                <small>
                    <i class="fas fa-shield-alt me-1"></i>
                    <?= t('auth.secure_login') ?>
                </small>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Password visibility toggle
    const togglePassword = document.getElementById('toggle-password');
    const passwordInput = document.getElementById('password');
    
    togglePassword.addEventListener('click', function() {
        const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
        passwordInput.setAttribute('type', type);
        
        const icon = this.querySelector('i');
        icon.classList.toggle('fa-eye');
        icon.classList.toggle('fa-eye-slash');
    });
    
    // Form validation
    const form = document.getElementById('login-form');
    form.addEventListener('submit', function(e) {
        const email = document.getElementById('email').value.trim();
        const password = document.getElementById('password').value;
        
        if (!email || !password) {
            e.preventDefault();
            alert('<?= t('auth.fill_all_fields') ?>');
            return false;
        }
        
        // Show loading
        const submitBtn = form.querySelector('button[type="submit"]');
        const originalText = submitBtn.innerHTML;
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i><?= t('auth.logging_in') ?>';
        
        // Re-enable if form fails to submit (client-side validation)
        setTimeout(function() {
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalText;
        }, 10000);
    });
    
    // Auto-focus on error
    <?php if (isset($errors) && !empty($errors)): ?>
        <?php if (isset($errors['email'])): ?>
            document.getElementById('email').focus();
        <?php elseif (isset($errors['password'])): ?>
            document.getElementById('password').focus();
        <?php endif; ?>
    <?php endif; ?>
});
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/app.php';
?>