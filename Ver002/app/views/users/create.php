<?php
/**
 * File: app/views/users/create.php
 * Purpose: User creation form with comprehensive user onboarding
 * Layout: Uses app layout with professional user creation interface
 */

$this->layout('layouts/app', [
    'title' => $page_title ?? t('users.create_user'),
    'active_nav' => 'users'
]);

$currentUser = $this->getCurrentUser();
$canCreate = $this->hasRole(['admin', 'manager']);
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">
            <i class="fas fa-plus me-2"></i><?= t('users.create_new_user') ?>
        </h1>
        <div class="btn-toolbar mb-2 mb-md-0">
            <a href="/users" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left"></i> <?= t('common.back_to_list') ?>
            </a>
        </div>
    </div>

    <form id="userForm" enctype="multipart/form-data">
        <div class="row">
            <div class="col-md-8">
                <!-- Basic Information -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0"><?= t('users.basic_information') ?></h5>
                    </div>
                    <div class="card-body">
                        <div class="row align-items-center mb-4">
                            <div class="col-auto">
                                <div class="bg-light text-muted rounded-circle d-flex align-items-center justify-content-center" id="avatarPreview" style="width: 80px; height: 80px;">
                                    <i class="fas fa-user fa-2x"></i>
                                </div>
                            </div>
                            <div class="col">
                                <div class="mb-2">
                                    <input type="file" class="form-control" name="avatar" accept="image/*" id="avatarInput">
                                    <div class="form-text"><?= t('users.avatar_help') ?></div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label"><?= t('users.first_name') ?> *</label>
                                    <input type="text" class="form-control" name="first_name" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label"><?= t('users.last_name') ?> *</label>
                                    <input type="text" class="form-control" name="last_name" required>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label"><?= t('users.username') ?> *</label>
                                    <input type="text" class="form-control" name="username" required>
                                    <div class="form-text"><?= t('users.username_help') ?></div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label"><?= t('users.email') ?> *</label>
                                    <input type="email" class="form-control" name="email" required>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label"><?= t('users.phone') ?></label>
                                    <input type="tel" class="form-control" name="phone">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label"><?= t('users.status') ?></label>
                                    <select class="form-select" name="status">
                                        <option value="active" selected><?= t('users.status.active') ?></option>
                                        <option value="inactive"><?= t('users.status.inactive') ?></option>
                                        <option value="pending"><?= t('users.status.pending') ?></option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label"><?= t('users.biography') ?></label>
                            <textarea class="form-control" name="bio" rows="3"></textarea>
                        </div>
                    </div>
                </div>

                <!-- Work Information -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0"><?= t('users.work_information') ?></h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label"><?= t('users.employee_id') ?></label>
                                    <input type="text" class="form-control" name="employee_id">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label"><?= t('users.job_title') ?></label>
                                    <input type="text" class="form-control" name="job_title">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label"><?= t('users.department') ?></label>
                                    <select class="form-select" name="department">
                                        <option value=""><?= t('common.select_department') ?></option>
                                        <option value="sales"><?= t('users.departments.sales') ?></option>
                                        <option value="marketing"><?= t('users.departments.marketing') ?></option>
                                        <option value="accounting"><?= t('users.departments.accounting') ?></option>
                                        <option value="warehouse"><?= t('users.departments.warehouse') ?></option>
                                        <option value="purchasing"><?= t('users.departments.purchasing') ?></option>
                                        <option value="management"><?= t('users.departments.management') ?></option>
                                        <option value="hr"><?= t('users.departments.hr') ?></option>
                                        <option value="it"><?= t('users.departments.it') ?></option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label"><?= t('users.manager') ?></label>
                                    <select class="form-select" name="manager_id">
                                        <option value=""><?= t('common.select_manager') ?></option>
                                        <?php if (!empty($managers)): ?>
                                        <?php foreach ($managers as $manager): ?>
                                        <option value="<?= $manager->id ?>">
                                            <?= htmlspecialchars($manager->first_name . ' ' . $manager->last_name) ?>
                                        </option>
                                        <?php endforeach; ?>
                                        <?php endif; ?>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label"><?= t('users.hire_date') ?></label>
                                    <input type="date" class="form-control" name="hire_date" value="<?= date('Y-m-d') ?>">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label"><?= t('users.employment_type') ?></label>
                                    <select class="form-select" name="employment_type">
                                        <option value="full_time" selected><?= t('users.employment.full_time') ?></option>
                                        <option value="part_time"><?= t('users.employment.part_time') ?></option>
                                        <option value="contract"><?= t('users.employment.contract') ?></option>
                                        <option value="freelance"><?= t('users.employment.freelance') ?></option>
                                        <option value="intern"><?= t('users.employment.intern') ?></option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label"><?= t('users.work_location') ?></label>
                            <input type="text" class="form-control" name="work_location">
                        </div>
                    </div>
                </div>

                <!-- Roles and Permissions -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0"><?= t('users.roles_permissions') ?></h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label"><?= t('users.assign_roles') ?></label>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="roles[]" value="admin">
                                        <label class="form-check-label">
                                            <strong><?= t('users.roles.admin') ?></strong>
                                            <br><small class="text-muted"><?= t('users.roles.admin_desc') ?></small>
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="roles[]" value="manager">
                                        <label class="form-check-label">
                                            <strong><?= t('users.roles.manager') ?></strong>
                                            <br><small class="text-muted"><?= t('users.roles.manager_desc') ?></small>
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="roles[]" value="sales" checked>
                                        <label class="form-check-label">
                                            <strong><?= t('users.roles.sales') ?></strong>
                                            <br><small class="text-muted"><?= t('users.roles.sales_desc') ?></small>
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="roles[]" value="warehouse">
                                        <label class="form-check-label">
                                            <strong><?= t('users.roles.warehouse') ?></strong>
                                            <br><small class="text-muted"><?= t('users.roles.warehouse_desc') ?></small>
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="roles[]" value="accounting">
                                        <label class="form-check-label">
                                            <strong><?= t('users.roles.accounting') ?></strong>
                                            <br><small class="text-muted"><?= t('users.roles.accounting_desc') ?></small>
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="roles[]" value="purchasing">
                                        <label class="form-check-label">
                                            <strong><?= t('users.roles.purchasing') ?></strong>
                                            <br><small class="text-muted"><?= t('users.roles.purchasing_desc') ?></small>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            <?= t('users.roles_help') ?>
                        </div>
                    </div>
                </div>

                <!-- Password Setup -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0"><?= t('users.password_setup') ?></h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="password_option" value="generate" id="generatePassword" checked>
                                <label class="form-check-label" for="generatePassword">
                                    <?= t('users.generate_password') ?>
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="password_option" value="manual" id="manualPassword">
                                <label class="form-check-label" for="manualPassword">
                                    <?= t('users.set_password_manually') ?>
                                </label>
                            </div>
                        </div>

                        <div id="manualPasswordFields" style="display: none;">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label"><?= t('users.password') ?> *</label>
                                        <input type="password" class="form-control" name="password" autocomplete="new-password" minlength="8">
                                        <div class="form-text"><?= t('users.password_requirements') ?></div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label"><?= t('users.confirm_password') ?> *</label>
                                        <input type="password" class="form-control" name="confirm_password" autocomplete="new-password">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" name="force_password_change" checked>
                            <label class="form-check-label">
                                <?= t('users.force_password_change_first_login') ?>
                            </label>
                            <div class="form-text"><?= t('users.force_password_change_help') ?></div>
                        </div>

                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" name="send_welcome_email" checked>
                            <label class="form-check-label">
                                <?= t('users.send_welcome_email') ?>
                            </label>
                            <div class="form-text"><?= t('users.welcome_email_help') ?></div>
                        </div>
                    </div>
                </div>

                <!-- Preferences -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0"><?= t('users.default_preferences') ?></h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label"><?= t('users.language') ?></label>
                                    <select class="form-select" name="language">
                                        <option value="en" selected>English</option>
                                        <option value="ar">العربية</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label"><?= t('users.timezone') ?></label>
                                    <select class="form-select" name="timezone">
                                        <option value="UTC" selected>UTC</option>
                                        <option value="Asia/Dubai">Dubai (GMT+4)</option>
                                        <option value="Asia/Riyadh">Riyadh (GMT+3)</option>
                                        <option value="America/New_York">New York (EST)</option>
                                        <option value="Europe/London">London (GMT)</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label"><?= t('users.date_format') ?></label>
                                    <select class="form-select" name="date_format">
                                        <option value="Y-m-d" selected>YYYY-MM-DD</option>
                                        <option value="m/d/Y">MM/DD/YYYY</option>
                                        <option value="d/m/Y">DD/MM/YYYY</option>
                                        <option value="d-m-Y">DD-MM-YYYY</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label"><?= t('users.items_per_page') ?></label>
                                    <select class="form-select" name="items_per_page">
                                        <option value="10">10</option>
                                        <option value="25" selected>25</option>
                                        <option value="50">50</option>
                                        <option value="100">100</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" name="email_notifications" checked>
                            <label class="form-check-label">
                                <?= t('users.enable_email_notifications') ?>
                            </label>
                        </div>

                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" name="desktop_notifications">
                            <label class="form-check-label">
                                <?= t('users.enable_desktop_notifications') ?>
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <!-- Contact Information -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0"><?= t('users.contact_information') ?></h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label"><?= t('users.address') ?></label>
                            <textarea class="form-control" name="address" rows="3"></textarea>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label"><?= t('users.city') ?></label>
                                    <input type="text" class="form-control" name="city">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label"><?= t('users.postal_code') ?></label>
                                    <input type="text" class="form-control" name="postal_code">
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label"><?= t('users.country') ?></label>
                            <select class="form-select" name="country">
                                <option value=""><?= t('common.select_country') ?></option>
                                <option value="US" selected>United States</option>
                                <option value="AE">United Arab Emirates</option>
                                <option value="SA">Saudi Arabia</option>
                                <option value="GB">United Kingdom</option>
                                <option value="CA">Canada</option>
                                <option value="AU">Australia</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Emergency Contact -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0"><?= t('users.emergency_contact') ?></h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label"><?= t('users.emergency_contact_name') ?></label>
                            <input type="text" class="form-control" name="emergency_contact">
                        </div>

                        <div class="mb-3">
                            <label class="form-label"><?= t('users.emergency_contact_phone') ?></label>
                            <input type="tel" class="form-control" name="emergency_phone">
                        </div>

                        <div class="mb-3">
                            <label class="form-label"><?= t('users.emergency_contact_relationship') ?></label>
                            <input type="text" class="form-control" name="emergency_relationship" placeholder="<?= t('users.relationship_examples') ?>">
                        </div>
                    </div>
                </div>

                <!-- Security Settings -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0"><?= t('users.security_settings') ?></h5>
                    </div>
                    <div class="card-body">
                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" name="two_factor_enabled">
                            <label class="form-check-label">
                                <?= t('users.enable_two_factor') ?>
                            </label>
                            <div class="form-text"><?= t('users.two_factor_help') ?></div>
                        </div>

                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" name="can_login" checked>
                            <label class="form-check-label">
                                <?= t('users.can_login') ?>
                            </label>
                        </div>

                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" name="api_access">
                            <label class="form-check-label">
                                <?= t('users.api_access') ?>
                            </label>
                        </div>

                        <div class="mb-3">
                            <label class="form-label"><?= t('users.session_timeout') ?></label>
                            <select class="form-select" name="session_timeout">
                                <option value="1800">30 <?= t('users.minutes') ?></option>
                                <option value="3600" selected>1 <?= t('users.hour') ?></option>
                                <option value="7200">2 <?= t('users.hours') ?></option>
                                <option value="14400">4 <?= t('users.hours') ?></option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="card">
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-plus"></i> <?= t('users.create_user') ?>
                            </button>
                            <button type="button" class="btn btn-outline-success" onclick="createAndInvite()">
                                <i class="fas fa-user-plus"></i> <?= t('users.create_and_invite') ?>
                            </button>
                            <button type="button" class="btn btn-outline-secondary" onclick="resetForm()">
                                <i class="fas fa-undo"></i> <?= t('common.reset_form') ?>
                            </button>
                            <button type="button" class="btn btn-outline-info" onclick="previewUser()">
                                <i class="fas fa-eye"></i> <?= t('users.preview') ?>
                            </button>
                            <a href="/users" class="btn btn-outline-secondary">
                                <i class="fas fa-list"></i> <?= t('users.back_to_list') ?>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Avatar preview
    const avatarInput = document.getElementById('avatarInput');
    const avatarPreview = document.getElementById('avatarPreview');
    
    avatarInput.addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                avatarPreview.innerHTML = `<img src="${e.target.result}" alt="Avatar Preview" class="rounded-circle" style="width: 80px; height: 80px; object-fit: cover;">`;
            };
            reader.readAsDataURL(file);
        }
    });
    
    // Password option toggle
    const generatePasswordRadio = document.getElementById('generatePassword');
    const manualPasswordRadio = document.getElementById('manualPassword');
    const manualPasswordFields = document.getElementById('manualPasswordFields');
    
    function togglePasswordFields() {
        if (manualPasswordRadio.checked) {
            manualPasswordFields.style.display = 'block';
            document.querySelector('input[name="password"]').required = true;
            document.querySelector('input[name="confirm_password"]').required = true;
        } else {
            manualPasswordFields.style.display = 'none';
            document.querySelector('input[name="password"]').required = false;
            document.querySelector('input[name="confirm_password"]').required = false;
        }
    }
    
    generatePasswordRadio.addEventListener('change', togglePasswordFields);
    manualPasswordRadio.addEventListener('change', togglePasswordFields);
    
    // Password confirmation validation
    const passwordInput = document.querySelector('input[name="password"]');
    const confirmPasswordInput = document.querySelector('input[name="confirm_password"]');
    
    function validatePasswords() {
        if (passwordInput.value && confirmPasswordInput.value) {
            if (passwordInput.value !== confirmPasswordInput.value) {
                confirmPasswordInput.setCustomValidity('<?= t('users.password_mismatch') ?>');
            } else {
                confirmPasswordInput.setCustomValidity('');
            }
        }
    }
    
    passwordInput.addEventListener('input', validatePasswords);
    confirmPasswordInput.addEventListener('input', validatePasswords);
    
    // Auto-generate username from name
    const firstNameInput = document.querySelector('input[name="first_name"]');
    const lastNameInput = document.querySelector('input[name="last_name"]');
    const usernameInput = document.querySelector('input[name="username"]');
    
    function generateUsername() {
        if (firstNameInput.value && lastNameInput.value && !usernameInput.value) {
            const username = (firstNameInput.value.toLowerCase() + '.' + lastNameInput.value.toLowerCase())
                .replace(/[^a-z0-9.]/g, '');
            usernameInput.value = username;
        }
    }
    
    firstNameInput.addEventListener('blur', generateUsername);
    lastNameInput.addEventListener('blur', generateUsername);
});

document.getElementById('userForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    
    fetch('/users', {
        method: 'POST',
        headers: {
            'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert('success', data.message);
            if (data.redirect) {
                setTimeout(() => {
                    window.location.href = data.redirect;
                }, 1500);
            }
        } else {
            showAlert('error', data.message);
            if (data.errors) {
                Object.keys(data.errors).forEach(field => {
                    const input = document.querySelector(`[name="${field}"]`);
                    if (input) {
                        input.classList.add('is-invalid');
                    }
                });
            }
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('error', '<?= t('messages.error.general') ?>');
    });
});

function createAndInvite() {
    const form = document.getElementById('userForm');
    const formData = new FormData(form);
    formData.append('send_invitation', '1');
    
    fetch('/users', {
        method: 'POST',
        headers: {
            'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert('success', data.message);
            if (data.redirect) {
                setTimeout(() => {
                    window.location.href = data.redirect;
                }, 1500);
            }
        } else {
            showAlert('error', data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('error', '<?= t('messages.error.general') ?>');
    });
}

function resetForm() {
    if (confirm('<?= t('users.confirm_reset_form') ?>')) {
        document.getElementById('userForm').reset();
        document.getElementById('avatarPreview').innerHTML = '<i class="fas fa-user fa-2x"></i>';
        document.getElementById('manualPasswordFields').style.display = 'none';
        document.querySelectorAll('.is-invalid').forEach(el => {
            el.classList.remove('is-invalid');
        });
    }
}

function previewUser() {
    const formData = new FormData(document.getElementById('userForm'));
    // Implementation for user preview modal would go here
    showAlert('info', '<?= t('users.preview_feature_coming_soon') ?>');
}
</script>