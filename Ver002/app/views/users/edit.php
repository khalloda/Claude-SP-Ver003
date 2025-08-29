<?php
/**
 * File: app/views/users/edit.php
 * Purpose: User profile editing form with comprehensive user management
 * Layout: Uses app layout with professional user editing interface
 */

$this->layout('layouts/app', [
    'title' => $page_title ?? t('users.edit_user'),
    'active_nav' => 'users'
]);

$currentUser = $this->getCurrentUser();
$canEdit = $this->hasRole(['admin', 'manager']) || ($currentUser && $currentUser->id === $user->id);
$canManageRoles = $this->hasRole(['admin']);
$canManagePermissions = $this->hasRole(['admin', 'manager']);
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">
            <i class="fas fa-edit me-2"></i><?= t('users.edit_user') ?>
            <small class="text-muted ms-2"><?= htmlspecialchars($user->first_name . ' ' . $user->last_name) ?></small>
        </h1>
        <div class="btn-toolbar mb-2 mb-md-0">
            <a href="/users/<?= $user->id ?>" class="btn btn-outline-secondary me-2">
                <i class="fas fa-eye"></i> <?= t('common.view') ?>
            </a>
            <a href="/users" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left"></i> <?= t('common.back') ?>
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
                                <?php if ($user->avatar): ?>
                                <img src="<?= htmlspecialchars($user->avatar) ?>" alt="Avatar" class="rounded-circle" id="avatarPreview" style="width: 80px; height: 80px; object-fit: cover;">
                                <?php else: ?>
                                <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" id="avatarPreview" style="width: 80px; height: 80px;">
                                    <i class="fas fa-user fa-2x"></i>
                                </div>
                                <?php endif; ?>
                            </div>
                            <div class="col">
                                <div class="mb-2">
                                    <input type="file" class="form-control" name="avatar" accept="image/*" id="avatarInput">
                                    <div class="form-text"><?= t('users.avatar_help') ?></div>
                                </div>
                                <?php if ($user->avatar): ?>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="remove_avatar">
                                    <label class="form-check-label">
                                        <?= t('users.remove_current_avatar') ?>
                                    </label>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label"><?= t('users.first_name') ?> *</label>
                                    <input type="text" class="form-control" name="first_name" 
                                           value="<?= htmlspecialchars($user->first_name) ?>" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label"><?= t('users.last_name') ?> *</label>
                                    <input type="text" class="form-control" name="last_name" 
                                           value="<?= htmlspecialchars($user->last_name) ?>" required>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label"><?= t('users.username') ?> *</label>
                                    <input type="text" class="form-control" name="username" 
                                           value="<?= htmlspecialchars($user->username) ?>" required>
                                    <div class="form-text"><?= t('users.username_help') ?></div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label"><?= t('users.email') ?> *</label>
                                    <input type="email" class="form-control" name="email" 
                                           value="<?= htmlspecialchars($user->email) ?>" required>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label"><?= t('users.phone') ?></label>
                                    <input type="tel" class="form-control" name="phone" 
                                           value="<?= htmlspecialchars($user->phone ?? '') ?>">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label"><?= t('users.status') ?></label>
                                    <select class="form-select" name="status" <?= !$canManagePermissions ? 'disabled' : '' ?>>
                                        <option value="active" <?= ($user->status ?? 'active') === 'active' ? 'selected' : '' ?>>
                                            <?= t('users.status.active') ?>
                                        </option>
                                        <option value="inactive" <?= ($user->status ?? '') === 'inactive' ? 'selected' : '' ?>>
                                            <?= t('users.status.inactive') ?>
                                        </option>
                                        <option value="suspended" <?= ($user->status ?? '') === 'suspended' ? 'selected' : '' ?>>
                                            <?= t('users.status.suspended') ?>
                                        </option>
                                        <option value="pending" <?= ($user->status ?? '') === 'pending' ? 'selected' : '' ?>>
                                            <?= t('users.status.pending') ?>
                                        </option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label"><?= t('users.biography') ?></label>
                            <textarea class="form-control" name="bio" rows="3"><?= htmlspecialchars($user->bio ?? '') ?></textarea>
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
                                    <input type="text" class="form-control" name="employee_id" 
                                           value="<?= htmlspecialchars($user->employee_id ?? '') ?>">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label"><?= t('users.job_title') ?></label>
                                    <input type="text" class="form-control" name="job_title" 
                                           value="<?= htmlspecialchars($user->job_title ?? '') ?>">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label"><?= t('users.department') ?></label>
                                    <select class="form-select" name="department">
                                        <option value=""><?= t('common.select_department') ?></option>
                                        <option value="sales" <?= ($user->department ?? '') === 'sales' ? 'selected' : '' ?>><?= t('users.departments.sales') ?></option>
                                        <option value="marketing" <?= ($user->department ?? '') === 'marketing' ? 'selected' : '' ?>><?= t('users.departments.marketing') ?></option>
                                        <option value="accounting" <?= ($user->department ?? '') === 'accounting' ? 'selected' : '' ?>><?= t('users.departments.accounting') ?></option>
                                        <option value="warehouse" <?= ($user->department ?? '') === 'warehouse' ? 'selected' : '' ?>><?= t('users.departments.warehouse') ?></option>
                                        <option value="purchasing" <?= ($user->department ?? '') === 'purchasing' ? 'selected' : '' ?>><?= t('users.departments.purchasing') ?></option>
                                        <option value="management" <?= ($user->department ?? '') === 'management' ? 'selected' : '' ?>><?= t('users.departments.management') ?></option>
                                        <option value="hr" <?= ($user->department ?? '') === 'hr' ? 'selected' : '' ?>><?= t('users.departments.hr') ?></option>
                                        <option value="it" <?= ($user->department ?? '') === 'it' ? 'selected' : '' ?>><?= t('users.departments.it') ?></option>
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
                                        <?php if ($manager->id !== $user->id): ?>
                                        <option value="<?= $manager->id ?>" <?= $user->manager_id == $manager->id ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($manager->first_name . ' ' . $manager->last_name) ?>
                                        </option>
                                        <?php endif; ?>
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
                                    <input type="date" class="form-control" name="hire_date" 
                                           value="<?= $user->hire_date ? date('Y-m-d', strtotime($user->hire_date)) : '' ?>">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label"><?= t('users.employment_type') ?></label>
                                    <select class="form-select" name="employment_type">
                                        <option value="full_time" <?= ($user->employment_type ?? 'full_time') === 'full_time' ? 'selected' : '' ?>>
                                            <?= t('users.employment.full_time') ?>
                                        </option>
                                        <option value="part_time" <?= ($user->employment_type ?? '') === 'part_time' ? 'selected' : '' ?>>
                                            <?= t('users.employment.part_time') ?>
                                        </option>
                                        <option value="contract" <?= ($user->employment_type ?? '') === 'contract' ? 'selected' : '' ?>>
                                            <?= t('users.employment.contract') ?>
                                        </option>
                                        <option value="freelance" <?= ($user->employment_type ?? '') === 'freelance' ? 'selected' : '' ?>>
                                            <?= t('users.employment.freelance') ?>
                                        </option>
                                        <option value="intern" <?= ($user->employment_type ?? '') === 'intern' ? 'selected' : '' ?>>
                                            <?= t('users.employment.intern') ?>
                                        </option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label"><?= t('users.work_location') ?></label>
                            <input type="text" class="form-control" name="work_location" 
                                   value="<?= htmlspecialchars($user->work_location ?? '') ?>">
                        </div>
                    </div>
                </div>

                <!-- Roles and Permissions -->
                <?php if ($canManageRoles): ?>
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0"><?= t('users.roles_permissions') ?></h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label"><?= t('users.assigned_roles') ?></label>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="roles[]" value="admin" 
                                               <?= in_array('admin', $user->roles ?? []) ? 'checked' : '' ?>>
                                        <label class="form-check-label">
                                            <strong><?= t('users.roles.admin') ?></strong>
                                            <br><small class="text-muted"><?= t('users.roles.admin_desc') ?></small>
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="roles[]" value="manager" 
                                               <?= in_array('manager', $user->roles ?? []) ? 'checked' : '' ?>>
                                        <label class="form-check-label">
                                            <strong><?= t('users.roles.manager') ?></strong>
                                            <br><small class="text-muted"><?= t('users.roles.manager_desc') ?></small>
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="roles[]" value="sales" 
                                               <?= in_array('sales', $user->roles ?? []) ? 'checked' : '' ?>>
                                        <label class="form-check-label">
                                            <strong><?= t('users.roles.sales') ?></strong>
                                            <br><small class="text-muted"><?= t('users.roles.sales_desc') ?></small>
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="roles[]" value="warehouse" 
                                               <?= in_array('warehouse', $user->roles ?? []) ? 'checked' : '' ?>>
                                        <label class="form-check-label">
                                            <strong><?= t('users.roles.warehouse') ?></strong>
                                            <br><small class="text-muted"><?= t('users.roles.warehouse_desc') ?></small>
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="roles[]" value="accounting" 
                                               <?= in_array('accounting', $user->roles ?? []) ? 'checked' : '' ?>>
                                        <label class="form-check-label">
                                            <strong><?= t('users.roles.accounting') ?></strong>
                                            <br><small class="text-muted"><?= t('users.roles.accounting_desc') ?></small>
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="roles[]" value="purchasing" 
                                               <?= in_array('purchasing', $user->roles ?? []) ? 'checked' : '' ?>>
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
                <?php endif; ?>

                <!-- Password Change -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0"><?= t('users.password_change') ?></h5>
                    </div>
                    <div class="card-body">
                        <?php if ($currentUser->id === $user->id): ?>
                        <div class="mb-3">
                            <label class="form-label"><?= t('users.current_password') ?></label>
                            <input type="password" class="form-control" name="current_password" autocomplete="current-password">
                            <div class="form-text"><?= t('users.current_password_help') ?></div>
                        </div>
                        <?php endif; ?>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label"><?= t('users.new_password') ?></label>
                                    <input type="password" class="form-control" name="new_password" autocomplete="new-password" minlength="8">
                                    <div class="form-text"><?= t('users.password_requirements') ?></div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label"><?= t('users.confirm_password') ?></label>
                                    <input type="password" class="form-control" name="confirm_password" autocomplete="new-password">
                                </div>
                            </div>
                        </div>

                        <?php if ($canManagePermissions && $currentUser->id !== $user->id): ?>
                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" name="force_password_change">
                            <label class="form-check-label">
                                <?= t('users.force_password_change') ?>
                            </label>
                            <div class="form-text"><?= t('users.force_password_change_help') ?></div>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Preferences -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0"><?= t('users.preferences') ?></h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label"><?= t('users.language') ?></label>
                                    <select class="form-select" name="language">
                                        <option value="en" <?= ($user->language ?? 'en') === 'en' ? 'selected' : '' ?>>English</option>
                                        <option value="ar" <?= ($user->language ?? '') === 'ar' ? 'selected' : '' ?>>العربية</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label"><?= t('users.timezone') ?></label>
                                    <select class="form-select" name="timezone">
                                        <option value="UTC" <?= ($user->timezone ?? 'UTC') === 'UTC' ? 'selected' : '' ?>>UTC</option>
                                        <option value="Asia/Dubai" <?= ($user->timezone ?? '') === 'Asia/Dubai' ? 'selected' : '' ?>>Dubai (GMT+4)</option>
                                        <option value="Asia/Riyadh" <?= ($user->timezone ?? '') === 'Asia/Riyadh' ? 'selected' : '' ?>>Riyadh (GMT+3)</option>
                                        <option value="America/New_York" <?= ($user->timezone ?? '') === 'America/New_York' ? 'selected' : '' ?>>New York (EST)</option>
                                        <option value="Europe/London" <?= ($user->timezone ?? '') === 'Europe/London' ? 'selected' : '' ?>>London (GMT)</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label"><?= t('users.date_format') ?></label>
                                    <select class="form-select" name="date_format">
                                        <option value="Y-m-d" <?= ($user->date_format ?? 'Y-m-d') === 'Y-m-d' ? 'selected' : '' ?>>YYYY-MM-DD</option>
                                        <option value="m/d/Y" <?= ($user->date_format ?? '') === 'm/d/Y' ? 'selected' : '' ?>>MM/DD/YYYY</option>
                                        <option value="d/m/Y" <?= ($user->date_format ?? '') === 'd/m/Y' ? 'selected' : '' ?>>DD/MM/YYYY</option>
                                        <option value="d-m-Y" <?= ($user->date_format ?? '') === 'd-m-Y' ? 'selected' : '' ?>>DD-MM-YYYY</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label"><?= t('users.items_per_page') ?></label>
                                    <select class="form-select" name="items_per_page">
                                        <option value="10" <?= ($user->items_per_page ?? '25') === '10' ? 'selected' : '' ?>>10</option>
                                        <option value="25" <?= ($user->items_per_page ?? '25') === '25' ? 'selected' : '' ?>>25</option>
                                        <option value="50" <?= ($user->items_per_page ?? '') === '50' ? 'selected' : '' ?>>50</option>
                                        <option value="100" <?= ($user->items_per_page ?? '') === '100' ? 'selected' : '' ?>>100</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" name="email_notifications" 
                                   <?= !empty($user->email_notifications) ? 'checked' : '' ?>>
                            <label class="form-check-label">
                                <?= t('users.enable_email_notifications') ?>
                            </label>
                        </div>

                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" name="desktop_notifications" 
                                   <?= !empty($user->desktop_notifications) ? 'checked' : '' ?>>
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
                            <textarea class="form-control" name="address" rows="3"><?= htmlspecialchars($user->address ?? '') ?></textarea>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label"><?= t('users.city') ?></label>
                                    <input type="text" class="form-control" name="city" 
                                           value="<?= htmlspecialchars($user->city ?? '') ?>">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label"><?= t('users.postal_code') ?></label>
                                    <input type="text" class="form-control" name="postal_code" 
                                           value="<?= htmlspecialchars($user->postal_code ?? '') ?>">
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label"><?= t('users.country') ?></label>
                            <select class="form-select" name="country">
                                <option value=""><?= t('common.select_country') ?></option>
                                <option value="US" <?= ($user->country ?? '') === 'US' ? 'selected' : '' ?>>United States</option>
                                <option value="AE" <?= ($user->country ?? '') === 'AE' ? 'selected' : '' ?>>United Arab Emirates</option>
                                <option value="SA" <?= ($user->country ?? '') === 'SA' ? 'selected' : '' ?>>Saudi Arabia</option>
                                <option value="GB" <?= ($user->country ?? '') === 'GB' ? 'selected' : '' ?>>United Kingdom</option>
                                <option value="CA" <?= ($user->country ?? '') === 'CA' ? 'selected' : '' ?>>Canada</option>
                                <option value="AU" <?= ($user->country ?? '') === 'AU' ? 'selected' : '' ?>>Australia</option>
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
                            <input type="text" class="form-control" name="emergency_contact" 
                                   value="<?= htmlspecialchars($user->emergency_contact ?? '') ?>">
                        </div>

                        <div class="mb-3">
                            <label class="form-label"><?= t('users.emergency_contact_phone') ?></label>
                            <input type="tel" class="form-control" name="emergency_phone" 
                                   value="<?= htmlspecialchars($user->emergency_phone ?? '') ?>">
                        </div>

                        <div class="mb-3">
                            <label class="form-label"><?= t('users.emergency_contact_relationship') ?></label>
                            <input type="text" class="form-control" name="emergency_relationship" 
                                   value="<?= htmlspecialchars($user->emergency_relationship ?? '') ?>">
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
                            <input class="form-check-input" type="checkbox" name="two_factor_enabled" 
                                   <?= !empty($user->two_factor_enabled) ? 'checked' : '' ?>>
                            <label class="form-check-label">
                                <?= t('users.enable_two_factor') ?>
                            </label>
                            <div class="form-text"><?= t('users.two_factor_help') ?></div>
                        </div>

                        <?php if ($canManagePermissions): ?>
                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" name="can_login" 
                                   <?= !empty($user->can_login) ? 'checked' : 'checked' ?>>
                            <label class="form-check-label">
                                <?= t('users.can_login') ?>
                            </label>
                        </div>

                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" name="api_access" 
                                   <?= !empty($user->api_access) ? 'checked' : '' ?>>
                            <label class="form-check-label">
                                <?= t('users.api_access') ?>
                            </label>
                        </div>
                        <?php endif; ?>

                        <div class="mb-3">
                            <label class="form-label"><?= t('users.session_timeout') ?></label>
                            <select class="form-select" name="session_timeout">
                                <option value="1800" <?= ($user->session_timeout ?? '3600') === '1800' ? 'selected' : '' ?>>30 <?= t('users.minutes') ?></option>
                                <option value="3600" <?= ($user->session_timeout ?? '3600') === '3600' ? 'selected' : '' ?>>1 <?= t('users.hour') ?></option>
                                <option value="7200" <?= ($user->session_timeout ?? '') === '7200' ? 'selected' : '' ?>>2 <?= t('users.hours') ?></option>
                                <option value="14400" <?= ($user->session_timeout ?? '') === '14400' ? 'selected' : '' ?>>4 <?= t('users.hours') ?></option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="card">
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> <?= t('common.save_changes') ?>
                            </button>
                            <button type="button" class="btn btn-outline-secondary" onclick="resetForm()">
                                <i class="fas fa-undo"></i> <?= t('common.reset_form') ?>
                            </button>
                            <a href="/users/<?= $user->id ?>" class="btn btn-outline-info">
                                <i class="fas fa-eye"></i> <?= t('common.view_profile') ?>
                            </a>
                            <?php if ($canManagePermissions && $currentUser->id !== $user->id): ?>
                            <button type="button" class="btn btn-outline-warning" onclick="resetPassword()">
                                <i class="fas fa-key"></i> <?= t('users.reset_password') ?>
                            </button>
                            <?php endif; ?>
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
                if (avatarPreview.tagName === 'IMG') {
                    avatarPreview.src = e.target.result;
                } else {
                    // Replace div with img
                    const img = document.createElement('img');
                    img.src = e.target.result;
                    img.alt = 'Avatar';
                    img.className = 'rounded-circle';
                    img.id = 'avatarPreview';
                    img.style.width = '80px';
                    img.style.height = '80px';
                    img.style.objectFit = 'cover';
                    avatarPreview.parentNode.replaceChild(img, avatarPreview);
                }
            };
            reader.readAsDataURL(file);
        }
    });
    
    // Password confirmation validation
    const newPassword = document.querySelector('input[name="new_password"]');
    const confirmPassword = document.querySelector('input[name="confirm_password"]');
    
    function validatePasswords() {
        if (newPassword.value && confirmPassword.value) {
            if (newPassword.value !== confirmPassword.value) {
                confirmPassword.setCustomValidity('<?= t('users.password_mismatch') ?>');
            } else {
                confirmPassword.setCustomValidity('');
            }
        }
    }
    
    newPassword.addEventListener('input', validatePasswords);
    confirmPassword.addEventListener('input', validatePasswords);
});

document.getElementById('userForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    
    fetch('/users/<?= $user->id ?>', {
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
            // Optionally redirect to user view
            if (data.redirect) {
                setTimeout(() => {
                    window.location.href = data.redirect;
                }, 1500);
            }
        } else {
            showAlert('error', data.message);
            if (data.errors) {
                // Display field-specific errors
                Object.keys(data.errors).forEach(field => {
                    const input = document.querySelector(`[name="${field}"]`);
                    if (input) {
                        input.classList.add('is-invalid');
                        const feedback = input.parentNode.querySelector('.invalid-feedback');
                        if (feedback) {
                            feedback.textContent = data.errors[field][0];
                        }
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

function resetForm() {
    if (confirm('<?= t('users.confirm_reset_form') ?>')) {
        document.getElementById('userForm').reset();
        // Remove any validation classes
        document.querySelectorAll('.is-invalid').forEach(el => {
            el.classList.remove('is-invalid');
        });
    }
}

<?php if ($canManagePermissions && $currentUser->id !== $user->id): ?>
function resetPassword() {
    if (confirm('<?= t('users.confirm_reset_password') ?>')) {
        fetch('/users/<?= $user->id ?>/reset-password', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showAlert('success', data.message);
            } else {
                showAlert('error', data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showAlert('error', '<?= t('messages.error.general') ?>');
        });
    }
}
<?php endif; ?>
</script>