<!DOCTYPE html>
<html lang="<?= getCurrentLanguage() ?>" dir="<?= isRTL() ? 'rtl' : 'ltr' ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?= csrf_token() ?>">
    <meta name="description" content="MISP - Management Information System for Spare Parts">
    <meta name="keywords" content="spare parts, inventory, management, system">
    <meta name="author" content="MISP System">
    
    <title><?= isset($title) ? $title . ' - ' : '' ?>MISP System</title>
    
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="/assets/images/favicon.ico">
    <link rel="apple-touch-icon" href="/assets/images/apple-touch-icon.png">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    
    <!-- Custom CSS -->
    <link href="/assets/css/auth.css" rel="stylesheet">
    
    <!-- RTL Support -->
    <?php if (isRTL()): ?>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
    <link href="/assets/css/auth-rtl.css" rel="stylesheet">
    <?php endif; ?>
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --primary-color: #0d6efd;
            --secondary-color: #6c757d;
            --success-color: #198754;
            --danger-color: #dc3545;
            --warning-color: #ffc107;
            --info-color: #0dcaf0;
            --light-color: #f8f9fa;
            --dark-color: #212529;
            --body-bg: #f5f7fa;
            --card-bg: #ffffff;
        }
        
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px 0;
        }
        
        .auth-container {
            width: 100%;
            max-width: 480px;
            margin: 0 auto;
        }
        
        .auth-card {
            background: var(--card-bg);
            border-radius: 16px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            border: none;
            overflow: hidden;
            backdrop-filter: blur(10px);
        }
        
        .auth-header {
            background: linear-gradient(135deg, var(--primary-color), #0056b3);
            color: white;
            padding: 2rem;
            text-align: center;
            position: relative;
        }
        
        .auth-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grain" width="100" height="100" patternUnits="userSpaceOnUse"><circle cx="25" cy="25" r="1" fill="rgba(255,255,255,0.1)"/><circle cx="75" cy="75" r="1" fill="rgba(255,255,255,0.1)"/><circle cx="50" cy="10" r="0.5" fill="rgba(255,255,255,0.1)"/></pattern></defs><rect width="100" height="100" fill="url(%23grain)"/></svg>') repeat;
            opacity: 0.1;
        }
        
        .auth-logo {
            font-size: 2.5rem;
            margin-bottom: 0.5rem;
            position: relative;
        }
        
        .auth-title {
            font-size: 1.75rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
            position: relative;
        }
        
        .auth-subtitle {
            opacity: 0.9;
            margin-bottom: 0;
            position: relative;
        }
        
        .auth-body {
            padding: 2.5rem;
        }
        
        .form-group {
            margin-bottom: 1.5rem;
        }
        
        .form-label {
            font-weight: 500;
            color: var(--dark-color);
            margin-bottom: 0.5rem;
        }
        
        .form-control {
            border: 2px solid #e9ecef;
            border-radius: 10px;
            padding: 0.75rem 1rem;
            font-size: 1rem;
            transition: all 0.3s ease;
            background-color: #f8f9fa;
        }
        
        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.15);
            background-color: white;
        }
        
        .input-group .form-control {
            border-left: none;
        }
        
        .input-group-text {
            background-color: #f8f9fa;
            border: 2px solid #e9ecef;
            border-right: none;
            border-radius: 10px 0 0 10px;
            color: var(--secondary-color);
        }
        
        .btn {
            border-radius: 10px;
            padding: 0.75rem 1.5rem;
            font-weight: 500;
            font-size: 1rem;
            transition: all 0.3s ease;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, var(--primary-color), #0056b3);
            border: none;
            box-shadow: 0 4px 15px rgba(13, 110, 253, 0.3);
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(13, 110, 253, 0.4);
            background: linear-gradient(135deg, #0056b3, var(--primary-color));
        }
        
        .btn-outline-secondary {
            border: 2px solid #e9ecef;
            color: var(--secondary-color);
        }
        
        .btn-outline-secondary:hover {
            background-color: var(--secondary-color);
            border-color: var(--secondary-color);
            color: white;
        }
        
        .alert {
            border-radius: 10px;
            border: none;
            margin-bottom: 1.5rem;
        }
        
        .alert-danger {
            background-color: rgba(220, 53, 69, 0.1);
            color: var(--danger-color);
        }
        
        .alert-success {
            background-color: rgba(25, 135, 84, 0.1);
            color: var(--success-color);
        }
        
        .auth-footer {
            background-color: #f8f9fa;
            padding: 1.5rem 2.5rem;
            text-align: center;
            border-top: 1px solid #e9ecef;
        }
        
        .auth-links {
            color: var(--secondary-color);
            text-decoration: none;
            font-weight: 500;
            transition: color 0.3s ease;
        }
        
        .auth-links:hover {
            color: var(--primary-color);
        }
        
        .language-switcher {
            position: absolute;
            top: 1rem;
            right: 1rem;
        }
        
        .language-switcher .dropdown-toggle {
            border: 1px solid rgba(255, 255, 255, 0.3);
            color: white;
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
        }
        
        .language-switcher .dropdown-toggle:hover {
            background: rgba(255, 255, 255, 0.2);
        }
        
        .loading-spinner {
            display: none;
            width: 1rem;
            height: 1rem;
            margin-right: 0.5rem;
        }
        
        .form-check-input {
            border-radius: 6px;
        }
        
        .form-check-input:checked {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }
        
        .password-strength {
            margin-top: 0.5rem;
        }
        
        .strength-meter {
            height: 4px;
            border-radius: 2px;
            background-color: #e9ecef;
            overflow: hidden;
            margin-bottom: 0.5rem;
        }
        
        .strength-fill {
            height: 100%;
            transition: width 0.3s ease, background-color 0.3s ease;
            width: 0;
            background-color: var(--danger-color);
        }
        
        .strength-fill.weak { width: 25%; background-color: var(--danger-color); }
        .strength-fill.fair { width: 50%; background-color: var(--warning-color); }
        .strength-fill.good { width: 75%; background-color: var(--info-color); }
        .strength-fill.strong { width: 100%; background-color: var(--success-color); }
        
        /* RTL Support */
        [dir="rtl"] .language-switcher {
            right: auto;
            left: 1rem;
        }
        
        [dir="rtl"] .input-group-text {
            border-radius: 0 10px 10px 0;
            border-right: 2px solid #e9ecef;
            border-left: none;
        }
        
        [dir="rtl"] .input-group .form-control {
            border-right: none;
            border-left: 2px solid #e9ecef;
        }
        
        /* Dark mode support */
        @media (prefers-color-scheme: dark) {
            :root {
                --body-bg: #1a1a1a;
                --card-bg: #2d3748;
                --dark-color: #e2e8f0;
            }
        }
        
        /* Mobile responsiveness */
        @media (max-width: 576px) {
            .auth-container {
                margin: 1rem;
            }
            
            .auth-header {
                padding: 1.5rem;
            }
            
            .auth-body {
                padding: 2rem;
            }
            
            .auth-footer {
                padding: 1rem 2rem;
            }
            
            .auth-logo {
                font-size: 2rem;
            }
            
            .auth-title {
                font-size: 1.5rem;
            }
        }
        
        /* Animation for form validation */
        .was-validated .form-control:invalid {
            animation: shake 0.6s ease-in-out;
        }
        
        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            10%, 30%, 50%, 70%, 90% { transform: translateX(-5px); }
            20%, 40%, 60%, 80% { transform: translateX(5px); }
        }
        
        /* Loading state */
        .btn.loading {
            pointer-events: none;
            opacity: 0.8;
        }
        
        .btn.loading .loading-spinner {
            display: inline-block;
        }
    </style>
</head>

<body>
    <div class="auth-container">
        <div class="auth-card card">
            <!-- Language Switcher -->
            <div class="language-switcher">
                <div class="dropdown">
                    <button class="btn btn-sm dropdown-toggle" type="button" id="languageDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fas fa-globe me-1"></i>
                        <?= getCurrentLanguage() === 'ar' ? 'العربية' : 'English' ?>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="languageDropdown">
                        <li><a class="dropdown-item" href="<?= switchLanguageUrl('en') ?>">
                            <i class="fas fa-check me-2 <?= getCurrentLanguage() === 'en' ? '' : 'invisible' ?>"></i>
                            English
                        </a></li>
                        <li><a class="dropdown-item" href="<?= switchLanguageUrl('ar') ?>">
                            <i class="fas fa-check me-2 <?= getCurrentLanguage() === 'ar' ? '' : 'invisible' ?>"></i>
                            العربية
                        </a></li>
                    </ul>
                </div>
            </div>
            
            <!-- Header -->
            <div class="auth-header">
                <div class="auth-logo">
                    <i class="fas fa-cogs"></i>
                </div>
                <h1 class="auth-title"><?= $title ?? 'MISP System' ?></h1>
                <p class="auth-subtitle">
                    <?= $subtitle ?? t('auth.system_subtitle', 'Management Information System for Spare Parts') ?>
                </p>
            </div>
            
            <!-- Content -->
            <div class="auth-body">
                <?php if (isset($error_message)): ?>
                <div class="alert alert-danger" role="alert">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    <?= htmlspecialchars($error_message) ?>
                </div>
                <?php endif; ?>
                
                <?php if (isset($success_message)): ?>
                <div class="alert alert-success" role="alert">
                    <i class="fas fa-check-circle me-2"></i>
                    <?= htmlspecialchars($success_message) ?>
                </div>
                <?php endif; ?>
                
                <?php if (isset($info_message)): ?>
                <div class="alert alert-info" role="alert">
                    <i class="fas fa-info-circle me-2"></i>
                    <?= htmlspecialchars($info_message) ?>
                </div>
                <?php endif; ?>
                
                <?php if (isset($warning_message)): ?>
                <div class="alert alert-warning" role="alert">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <?= htmlspecialchars($warning_message) ?>
                </div>
                <?php endif; ?>
                
                <!-- Page Content -->
                <?php 
                if (isset($content)) {
                    echo $content;
                } else {
                    // If using content blocks
                    if (function_exists('getContent')) {
                        echo getContent();
                    }
                }
                ?>
            </div>
            
            <!-- Footer -->
            <?php if (isset($show_footer) && $show_footer !== false): ?>
            <div class="auth-footer">
                <div class="row align-items-center">
                    <div class="col-md-6 text-center text-md-start">
                        <small class="text-muted">
                            &copy; <?= date('Y') ?> MISP System. <?= t('auth.all_rights_reserved', 'All rights reserved.') ?>
                        </small>
                    </div>
                    <div class="col-md-6 text-center text-md-end">
                        <small>
                            <a href="/privacy" class="auth-links me-3"><?= t('auth.privacy_policy', 'Privacy Policy') ?></a>
                            <a href="/terms" class="auth-links"><?= t('auth.terms_service', 'Terms of Service') ?></a>
                        </small>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
        
        <!-- System Status -->
        <div class="text-center mt-4">
            <small class="text-white-50">
                <i class="fas fa-shield-alt me-1"></i>
                <?= t('auth.secure_connection', 'Secure Connection') ?>
                <span class="mx-2">•</span>
                <i class="fas fa-clock me-1"></i>
                <?= t('auth.system_time', 'System Time') ?>: <?= date('H:i T') ?>
            </small>
        </div>
    </div>
    
    <!-- Toast Container for Notifications -->
    <div class="toast-container position-fixed bottom-0 end-0 p-3" id="toast-container"></div>
    
    <!-- Scripts -->
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Custom Auth JS -->
    <script>
        // Global auth functionality
        class AuthManager {
            static init() {
                this.setupFormValidation();
                this.setupPasswordStrength();
                this.setupLoadingStates();
                this.setupKeyboardShortcuts();
            }
            
            static setupFormValidation() {
                const forms = document.querySelectorAll('.needs-validation');
                forms.forEach(form => {
                    form.addEventListener('submit', function(event) {
                        if (!form.checkValidity()) {
                            event.preventDefault();
                            event.stopPropagation();
                            this.showValidationErrors(form);
                        }
                        form.classList.add('was-validated');
                    });
                });
            }
            
            static setupPasswordStrength() {
                const passwordInputs = document.querySelectorAll('input[type="password"][data-strength="true"]');
                passwordInputs.forEach(input => {
                    const strengthContainer = document.createElement('div');
                    strengthContainer.className = 'password-strength';
                    strengthContainer.innerHTML = `
                        <div class="strength-meter">
                            <div class="strength-fill"></div>
                        </div>
                        <small class="strength-text text-muted"></small>
                    `;
                    input.parentNode.appendChild(strengthContainer);
                    
                    input.addEventListener('input', () => {
                        this.checkPasswordStrength(input, strengthContainer);
                    });
                });
            }
            
            static checkPasswordStrength(input, container) {
                const password = input.value;
                const fill = container.querySelector('.strength-fill');
                const text = container.querySelector('.strength-text');
                
                if (!password) {
                    fill.className = 'strength-fill';
                    text.textContent = '';
                    return;
                }
                
                let score = 0;
                let feedback = [];
                
                // Length check
                if (password.length >= 8) score++;
                else feedback.push('8+ characters');
                
                // Lowercase check
                if (/[a-z]/.test(password)) score++;
                else feedback.push('lowercase letter');
                
                // Uppercase check
                if (/[A-Z]/.test(password)) score++;
                else feedback.push('uppercase letter');
                
                // Number check
                if (/\d/.test(password)) score++;
                else feedback.push('number');
                
                // Special character check
                if (/[!@#$%^&*(),.?":{}|<>]/.test(password)) score++;
                else feedback.push('special character');
                
                const strength = ['weak', 'weak', 'fair', 'good', 'strong'][Math.min(score, 4)];
                fill.className = `strength-fill ${strength}`;
                
                const strengthText = {
                    'weak': 'Weak',
                    'fair': 'Fair', 
                    'good': 'Good',
                    'strong': 'Strong'
                };
                
                text.textContent = `Password strength: ${strengthText[strength]}`;
                if (feedback.length > 0 && score < 4) {
                    text.textContent += ` (Add: ${feedback.slice(0, 2).join(', ')})`;
                }
            }
            
            static setupLoadingStates() {
                const submitButtons = document.querySelectorAll('button[type="submit"]');
                submitButtons.forEach(button => {
                    const form = button.closest('form');
                    if (form) {
                        form.addEventListener('submit', () => {
                            this.setLoading(button, true);
                        });
                    }
                });
            }
            
            static setLoading(button, loading) {
                if (loading) {
                    button.classList.add('loading');
                    button.disabled = true;
                    if (!button.querySelector('.loading-spinner')) {
                        const spinner = document.createElement('div');
                        spinner.className = 'loading-spinner spinner-border spinner-border-sm';
                        spinner.setAttribute('role', 'status');
                        button.insertBefore(spinner, button.firstChild);
                    }
                } else {
                    button.classList.remove('loading');
                    button.disabled = false;
                    const spinner = button.querySelector('.loading-spinner');
                    if (spinner) spinner.remove();
                }
            }
            
            static setupKeyboardShortcuts() {
                document.addEventListener('keydown', (e) => {
                    // Alt + L for language switch
                    if (e.altKey && e.key === 'l') {
                        e.preventDefault();
                        document.getElementById('languageDropdown')?.click();
                    }
                    
                    // Enter on login form
                    if (e.key === 'Enter' && e.target.matches('input')) {
                        const form = e.target.closest('form');
                        if (form) {
                            const submitButton = form.querySelector('button[type="submit"]');
                            if (submitButton && !submitButton.disabled) {
                                submitButton.click();
                            }
                        }
                    }
                });
            }
            
            static showNotification(message, type = 'info', duration = 5000) {
                const container = document.getElementById('toast-container');
                const toast = document.createElement('div');
                toast.className = `toast align-items-center text-bg-${type} border-0`;
                toast.setAttribute('role', 'alert');
                toast.innerHTML = `
                    <div class="d-flex">
                        <div class="toast-body">${message}</div>
                        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
                    </div>
                `;
                
                container.appendChild(toast);
                const bsToast = new bootstrap.Toast(toast, { delay: duration });
                bsToast.show();
                
                toast.addEventListener('hidden.bs.toast', () => toast.remove());
            }
        }
        
        // Initialize when DOM is ready
        document.addEventListener('DOMContentLoaded', () => {
            AuthManager.init();
        });
        
        // CSRF token for AJAX requests
        window.csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        
        // Global error handler
        window.addEventListener('error', (e) => {
            console.error('Auth page error:', e.error);
        });
        
        // Form utilities
        window.AuthUtils = {
            setLoading: AuthManager.setLoading,
            showNotification: AuthManager.showNotification,
            
            // Toggle password visibility
            togglePassword: function(inputId) {
                const input = document.getElementById(inputId);
                const icon = document.querySelector(`[data-toggle-password="${inputId}"] i`);
                
                if (input.type === 'password') {
                    input.type = 'text';
                    icon.className = 'fas fa-eye-slash';
                } else {
                    input.type = 'password';
                    icon.className = 'fas fa-eye';
                }
            },
            
            // Validate email format
            isValidEmail: function(email) {
                const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                return re.test(email);
            }
        };
    </script>
    
    <!-- Page specific scripts -->
    <?php if (isset($page_scripts)): ?>
        <?= $page_scripts ?>
    <?php endif; ?>
    
    <!-- Custom inline scripts -->
    <?php if (isset($inline_scripts)): ?>
        <script><?= $inline_scripts ?></script>
    <?php endif; ?>
</body>
</html>