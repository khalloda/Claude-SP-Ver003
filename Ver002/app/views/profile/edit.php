<?php
/**
 * File: app/views/profile/edit.php
 * Purpose: User profile editing form with comprehensive account management
 * Layout: Uses app layout with tabbed interface for different profile sections
 */

$this->layout('layouts/app', [
    'title' => $page_title ?? t('profile.edit_profile'),
    'active_nav' => 'profile'
]);

$currentUser = $this->getCurrentUser();
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">
            <i class="fas fa-user-edit me-2"></i><?= t('profile.edit_profile') ?>
        </h1>
        <div class="btn-toolbar mb-2 mb-md-0">
            <a href="/profile" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left"></i> <?= t('common.back') ?>
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <!-- Profile Edit Tabs -->
            <div class="card">
                <div class="card-header">
                    <ul class="nav nav-tabs card-header-tabs" id="profileTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="basic-tab" data-bs-toggle="tab" data-bs-target="#basic" type="button" role="tab">
                                <i class="fas fa-user me-2"></i><?= t('profile.basic_info') ?>
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="contact-tab" data-bs-toggle="tab" data-bs-target="#contact" type="button" role="tab">
                                <i class="fas fa-address-card me-2"></i><?= t('profile.contact_info') ?>
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="preferences-tab" data-bs-toggle="tab" data-bs-target="#preferences" type="button" role="tab">
                                <i class="fas fa-cog me-2"></i><?= t('profile.preferences') ?>
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="avatar-tab" data-bs-toggle="tab" data-bs-target="#avatar" type="button" role="tab">
                                <i class="fas fa-camera me-2"></i><?= t('profile.avatar') ?>
                            </button>
                        </li>
                    </ul>
                </div>
                <div class="card-body">
                    <form id="profileForm" method="POST" action="/profile" enctype="multipart/form-data">
                        <input type="hidden" name="_method" value="PUT">
                        
                        <div class="tab-content" id="profileTabsContent">
                            <!-- Basic Information Tab -->
                            <div class="tab-pane fade show active" id="basic" role="tabpanel">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="first_name" class="form-label"><?= t('profile.first_name') ?> <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control" id="first_name" name="first_name" required
                                                   value="<?= htmlspecialchars($user->first_name ?? '') ?>">
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="last_name" class="form-label"><?= t('profile.last_name') ?> <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control" id="last_name" name="last_name" required
                                                   value="<?= htmlspecialchars($user->last_name ?? '') ?>">
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="username" class="form-label"><?= t('profile.username') ?> <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control" id="username" name="username" required
                                                   value="<?= htmlspecialchars($user->username) ?>" readonly>
                                            <div class="form-text"><?= t('profile.username_readonly') ?></div>
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="email" class="form-label"><?= t('profile.email') ?> <span class="text-danger">*</span></label>
                                            <div class="input-group">
                                                <input type="email" class="form-control" id="email" name="email" required
                                                       value="<?= htmlspecialchars($user->email) ?>">
                                                <?php if ($user->email_verified_at): ?>
                                                <span class="input-group-text text-success">
                                                    <i class="fas fa-check-circle" title="<?= t('profile.email_verified') ?>"></i>
                                                </span>
                                                <?php else: ?>
                                                <span class="input-group-text text-warning">
                                                    <i class="fas fa-exclamation-triangle" title="<?= t('profile.email_unverified') ?>"></i>
                                                </span>
                                                <?php endif; ?>
                                            </div>
                                            <?php if (!$user->email_verified_at): ?>
                                            <div class="form-text">
                                                <?= t('profile.email_unverified_help') ?>
                                                <a href="#" onclick="resendVerification()"><?= t('profile.resend_verification') ?></a>
                                            </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="bio" class="form-label"><?= t('profile.bio') ?></label>
                                    <textarea class="form-control" id="bio" name="bio" rows="4" 
                                              placeholder="<?= t('profile.bio_placeholder') ?>"><?= htmlspecialchars($user->bio ?? '') ?></textarea>
                                    <div class="form-text"><?= t('profile.bio_help') ?></div>
                                </div>
                            </div>

                            <!-- Contact Information Tab -->
                            <div class="tab-pane fade" id="contact" role="tabpanel">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="phone" class="form-label"><?= t('profile.phone') ?></label>
                                            <input type="tel" class="form-control" id="phone" name="phone"
                                                   value="<?= htmlspecialchars($user->phone ?? '') ?>"
                                                   placeholder="<?= t('profile.phone_placeholder') ?>">
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="mobile" class="form-label"><?= t('profile.mobile') ?></label>
                                            <input type="tel" class="form-control" id="mobile" name="mobile"
                                                   value="<?= htmlspecialchars($user->mobile ?? '') ?>"
                                                   placeholder="<?= t('profile.mobile_placeholder') ?>">
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="department" class="form-label"><?= t('profile.department') ?></label>
                                            <input type="text" class="form-control" id="department" name="department"
                                                   value="<?= htmlspecialchars($user->department ?? '') ?>"
                                                   placeholder="<?= t('profile.department_placeholder') ?>">
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="job_title" class="form-label"><?= t('profile.job_title') ?></label>
                                            <input type="text" class="form-control" id="job_title" name="job_title"
                                                   value="<?= htmlspecialchars($user->job_title ?? '') ?>"
                                                   placeholder="<?= t('profile.job_title_placeholder') ?>">
                                        </div>
                                    </div>
                                </div>

                                <hr>
                                <h6><?= t('profile.address_information') ?></h6>

                                <div class="mb-3">
                                    <label for="address_line_1" class="form-label"><?= t('profile.address_line_1') ?></label>
                                    <input type="text" class="form-control" id="address_line_1" name="address_line_1"
                                           value="<?= htmlspecialchars($user->address_line_1 ?? '') ?>">
                                </div>

                                <div class="mb-3">
                                    <label for="address_line_2" class="form-label"><?= t('profile.address_line_2') ?></label>
                                    <input type="text" class="form-control" id="address_line_2" name="address_line_2"
                                           value="<?= htmlspecialchars($user->address_line_2 ?? '') ?>">
                                </div>

                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label for="city" class="form-label"><?= t('profile.city') ?></label>
                                            <input type="text" class="form-control" id="city" name="city"
                                                   value="<?= htmlspecialchars($user->city ?? '') ?>">
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label for="state" class="form-label"><?= t('profile.state') ?></label>
                                            <input type="text" class="form-control" id="state" name="state"
                                                   value="<?= htmlspecialchars($user->state ?? '') ?>">
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label for="postal_code" class="form-label"><?= t('profile.postal_code') ?></label>
                                            <input type="text" class="form-control" id="postal_code" name="postal_code"
                                                   value="<?= htmlspecialchars($user->postal_code ?? '') ?>">
                                        </div>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="country" class="form-label"><?= t('profile.country') ?></label>
                                    <select class="form-select" id="country" name="country">
                                        <option value=""><?= t('profile.select_country') ?></option>
                                        <?php foreach ($countries ?? [] as $country): ?>
                                        <option value="<?= $country->code ?>" <?= ($user->country ?? '') === $country->code ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($country->name) ?>
                                        </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>

                            <!-- Preferences Tab -->
                            <div class="tab-pane fade" id="preferences" role="tabpanel">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="preferred_language" class="form-label"><?= t('profile.preferred_language') ?></label>
                                            <select class="form-select" id="preferred_language" name="preferred_language">
                                                <option value="en" <?= ($user->preferred_language ?? 'en') === 'en' ? 'selected' : '' ?>>
                                                    <?= t('languages.english') ?>
                                                </option>
                                                <option value="ar" <?= ($user->preferred_language ?? '') === 'ar' ? 'selected' : '' ?>>
                                                    <?= t('languages.arabic') ?>
                                                </option>
                                            </select>
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="timezone" class="form-label"><?= t('profile.timezone') ?></label>
                                            <select class="form-select" id="timezone" name="timezone">
                                                <?php foreach ($timezones ?? [] as $tz): ?>
                                                <option value="<?= $tz ?>" <?= ($user->timezone ?? 'UTC') === $tz ? 'selected' : '' ?>>
                                                    <?= $tz ?>
                                                </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="date_format" class="form-label"><?= t('profile.date_format') ?></label>
                                            <select class="form-select" id="date_format" name="date_format">
                                                <option value="M j, Y" <?= ($user->date_format ?? 'M j, Y') === 'M j, Y' ? 'selected' : '' ?>>
                                                    <?= date('M j, Y') ?> (M j, Y)
                                                </option>
                                                <option value="F j, Y" <?= ($user->date_format ?? '') === 'F j, Y' ? 'selected' : '' ?>>
                                                    <?= date('F j, Y') ?> (F j, Y)
                                                </option>
                                                <option value="Y-m-d" <?= ($user->date_format ?? '') === 'Y-m-d' ? 'selected' : '' ?>>
                                                    <?= date('Y-m-d') ?> (Y-m-d)
                                                </option>
                                                <option value="d/m/Y" <?= ($user->date_format ?? '') === 'd/m/Y' ? 'selected' : '' ?>>
                                                    <?= date('d/m/Y') ?> (d/m/Y)
                                                </option>
                                                <option value="m/d/Y" <?= ($user->date_format ?? '') === 'm/d/Y' ? 'selected' : '' ?>>
                                                    <?= date('m/d/Y') ?> (m/d/Y)
                                                </option>
                                            </select>
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="time_format" class="form-label"><?= t('profile.time_format') ?></label>
                                            <select class="form-select" id="time_format" name="time_format">
                                                <option value="g:i A" <?= ($user->time_format ?? 'g:i A') === 'g:i A' ? 'selected' : '' ?>>
                                                    <?= date('g:i A') ?> (12-hour)
                                                </option>
                                                <option value="H:i" <?= ($user->time_format ?? '') === 'H:i' ? 'selected' : '' ?>>
                                                    <?= date('H:i') ?> (24-hour)
                                                </option>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="preferred_currency" class="form-label"><?= t('profile.preferred_currency') ?></label>
                                            <select class="form-select" id="preferred_currency" name="preferred_currency">
                                                <?php foreach ($currencies ?? [] as $currency): ?>
                                                <option value="<?= $currency->code ?>" <?= ($user->preferred_currency ?? 'USD') === $currency->code ? 'selected' : '' ?>>
                                                    <?= $currency->code ?> - <?= $currency->name ?>
                                                </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="items_per_page" class="form-label"><?= t('profile.items_per_page') ?></label>
                                            <select class="form-select" id="items_per_page" name="items_per_page">
                                                <option value="10" <?= ($user->items_per_page ?? 25) == 10 ? 'selected' : '' ?>>10</option>
                                                <option value="25" <?= ($user->items_per_page ?? 25) == 25 ? 'selected' : '' ?>>25</option>
                                                <option value="50" <?= ($user->items_per_page ?? 25) == 50 ? 'selected' : '' ?>>50</option>
                                                <option value="100" <?= ($user->items_per_page ?? 25) == 100 ? 'selected' : '' ?>>100</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <hr>
                                <h6><?= t('profile.notification_preferences') ?></h6>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-check form-switch mb-3">
                                            <input class="form-check-input" type="checkbox" id="email_notifications" name="email_notifications"
                                                   <?= $user->email_notifications ? 'checked' : '' ?>>
                                            <label class="form-check-label" for="email_notifications">
                                                <?= t('profile.email_notifications') ?>
                                            </label>
                                        </div>
                                        
                                        <div class="form-check form-switch mb-3">
                                            <input class="form-check-input" type="checkbox" id="login_notifications" name="login_notifications"
                                                   <?= $user->login_notifications ? 'checked' : '' ?>>
                                            <label class="form-check-label" for="login_notifications">
                                                <?= t('profile.login_notifications') ?>
                                            </label>
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <div class="form-check form-switch mb-3">
                                            <input class="form-check-input" type="checkbox" id="marketing_emails" name="marketing_emails"
                                                   <?= $user->marketing_emails ? 'checked' : '' ?>>
                                            <label class="form-check-label" for="marketing_emails">
                                                <?= t('profile.marketing_emails') ?>
                                            </label>
                                        </div>
                                        
                                        <div class="form-check form-switch mb-3">
                                            <input class="form-check-input" type="checkbox" id="system_notifications" name="system_notifications"
                                                   <?= $user->system_notifications ? 'checked' : '' ?>>
                                            <label class="form-check-label" for="system_notifications">
                                                <?= t('profile.system_notifications') ?>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Avatar Tab -->
                            <div class="tab-pane fade" id="avatar" role="tabpanel">
                                <div class="text-center mb-4">
                                    <div class="current-avatar mb-3">
                                        <?php if (!empty($user->avatar_url)): ?>
                                        <img src="<?= htmlspecialchars($user->avatar_url) ?>" class="rounded-circle img-fluid" 
                                             alt="<?= htmlspecialchars($user->first_name . ' ' . $user->last_name) ?>" 
                                             style="width: 150px; height: 150px; object-fit: cover;" id="avatarPreview">
                                        <?php else: ?>
                                        <div class="avatar-placeholder rounded-circle d-flex align-items-center justify-content-center bg-primary text-white mx-auto" 
                                             style="width: 150px; height: 150px; font-size: 3rem; font-weight: 600;" id="avatarPreview">
                                            <?= strtoupper(substr($user->first_name ?? '', 0, 1) . substr($user->last_name ?? '', 0, 1)) ?>
                                        </div>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <h5><?= t('profile.current_avatar') ?></h5>
                                </div>

                                <div class="mb-3">
                                    <label for="avatar" class="form-label"><?= t('profile.upload_new_avatar') ?></label>
                                    <input type="file" class="form-control" id="avatar" name="avatar" 
                                           accept="image/*" onchange="previewAvatar(this)">
                                    <div class="form-text"><?= t('profile.avatar_help') ?></div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label"><?= t('profile.avatar_options') ?></label>
                                            <div class="btn-group w-100" role="group">
                                                <input type="radio" class="btn-check" name="avatar_option" id="keep_current" value="keep" checked>
                                                <label class="btn btn-outline-secondary" for="keep_current"><?= t('profile.keep_current') ?></label>
                                                
                                                <input type="radio" class="btn-check" name="avatar_option" id="upload_new" value="upload">
                                                <label class="btn btn-outline-primary" for="upload_new"><?= t('profile.upload_new') ?></label>
                                                
                                                <input type="radio" class="btn-check" name="avatar_option" id="use_gravatar" value="gravatar">
                                                <label class="btn btn-outline-info" for="use_gravatar"><?= t('profile.use_gravatar') ?></label>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <?php if (!empty($user->avatar_url)): ?>
                                        <div class="mb-3">
                                            <label class="form-label"><?= t('profile.remove_avatar') ?></label>
                                            <div>
                                                <button type="button" class="btn btn-outline-danger" onclick="removeAvatar()">
                                                    <i class="fas fa-trash"></i> <?= t('profile.remove_current_avatar') ?>
                                                </button>
                                            </div>
                                        </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex gap-2 mt-4">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> <?= t('profile.save_changes') ?>
                            </button>
                            <button type="button" class="btn btn-outline-success" onclick="saveAndContinue()">
                                <i class="fas fa-check"></i> <?= t('profile.save_and_view') ?>
                            </button>
                            <a href="/profile" class="btn btn-secondary">
                                <?= t('common.cancel') ?>
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Help Sidebar -->
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <h6 class="card-title mb-0">
                        <i class="fas fa-info-circle me-2"></i><?= t('profile.help_tips') ?>
                    </h6>
                </div>
                <div class="card-body">
                    <div class="accordion" id="helpAccordion">
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#basicHelp">
                                    <?= t('profile.basic_info_help') ?>
                                </button>
                            </h2>
                            <div id="basicHelp" class="accordion-collapse collapse" data-bs-parent="#helpAccordion">
                                <div class="accordion-body small">
                                    <ul class="mb-0">
                                        <li><?= t('profile.help.name_tip') ?></li>
                                        <li><?= t('profile.help.email_tip') ?></li>
                                        <li><?= t('profile.help.bio_tip') ?></li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#contactHelp">
                                    <?= t('profile.contact_info_help') ?>
                                </button>
                            </h2>
                            <div id="contactHelp" class="accordion-collapse collapse" data-bs-parent="#helpAccordion">
                                <div class="accordion-body small">
                                    <ul class="mb-0">
                                        <li><?= t('profile.help.phone_tip') ?></li>
                                        <li><?= t('profile.help.address_tip') ?></li>
                                        <li><?= t('profile.help.department_tip') ?></li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#preferencesHelp">
                                    <?= t('profile.preferences_help') ?>
                                </button>
                            </h2>
                            <div id="preferencesHelp" class="accordion-collapse collapse" data-bs-parent="#helpAccordion">
                                <div class="accordion-body small">
                                    <ul class="mb-0">
                                        <li><?= t('profile.help.language_tip') ?></li>
                                        <li><?= t('profile.help.timezone_tip') ?></li>
                                        <li><?= t('profile.help.notifications_tip') ?></li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#avatarHelp">
                                    <?= t('profile.avatar_help_title') ?>
                                </button>
                            </h2>
                            <div id="avatarHelp" class="accordion-collapse collapse" data-bs-parent="#helpAccordion">
                                <div class="accordion-body small">
                                    <ul class="mb-0">
                                        <li><?= t('profile.help.avatar_size_tip') ?></li>
                                        <li><?= t('profile.help.avatar_format_tip') ?></li>
                                        <li><?= t('profile.help.gravatar_tip') ?></li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="card mt-3">
                <div class="card-header">
                    <h6 class="card-title mb-0">
                        <i class="fas fa-shield-alt me-2"></i><?= t('profile.security_reminder') ?>
                    </h6>
                </div>
                <div class="card-body">
                    <div class="small text-muted">
                        <p><?= t('profile.security_reminder_text') ?></p>
                        <div class="d-grid">
                            <a href="/profile/change-password" class="btn btn-sm btn-outline-warning">
                                <i class="fas fa-key"></i> <?= t('profile.change_password') ?>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Avatar preview
function previewAvatar(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            const preview = document.getElementById('avatarPreview');
            preview.innerHTML = `<img src="${e.target.result}" class="rounded-circle img-fluid" style="width: 150px; height: 150px; object-fit: cover;">`;
            document.getElementById('upload_new').checked = true;
        };
        reader.readAsDataURL(input.files[0]);
    }
}

// Remove avatar
function removeAvatar() {
    if (confirm('<?= t('profile.confirm_remove_avatar') ?>')) {
        const preview = document.getElementById('avatarPreview');
        const initials = '<?= strtoupper(substr($user->first_name ?? '', 0, 1) . substr($user->last_name ?? '', 0, 1)) ?>';
        preview.innerHTML = `<div class="avatar-placeholder rounded-circle d-flex align-items-center justify-content-center bg-primary text-white mx-auto" style="width: 150px; height: 150px; font-size: 3rem; font-weight: 600;">${initials}</div>`;
        
        // Add hidden field to indicate avatar removal
        let removeInput = document.querySelector('input[name="remove_avatar"]');
        if (!removeInput) {
            removeInput = document.createElement('input');
            removeInput.type = 'hidden';
            removeInput.name = 'remove_avatar';
            removeInput.value = '1';
            document.getElementById('profileForm').appendChild(removeInput);
        }
    }
}

// Resend email verification
async function resendVerification() {
    try {
        const response = await fetch('/api/profile/resend-verification', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        });
        
        const data = await response.json();
        if (data.success) {
            showAlert('success', data.message);
        } else {
            showAlert('error', data.message);
        }
    } catch (error) {
        console.error('Error:', error);
        showAlert('error', '<?= t('messages.error.general') ?>');
    }
}

// Save and continue
function saveAndContinue() {
    const form = document.getElementById('profileForm');
    const formData = new FormData(form);
    formData.append('redirect_to', 'profile');
    
    fetch('/profile', {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert('success', data.message);
            setTimeout(() => window.location.href = '/profile', 1500);
        } else {
            showAlert('error', data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('error', '<?= t('messages.error.general') ?>');
    });
}

// Form validation
document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('profileForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        // Validate required fields
        const requiredFields = ['first_name', 'last_name', 'email'];
        let isValid = true;
        
        requiredFields.forEach(field => {
            const input = document.getElementById(field);
            if (!input.value.trim()) {
                input.classList.add('is-invalid');
                isValid = false;
            } else {
                input.classList.remove('is-invalid');
            }
        });
        
        // Email format validation
        const emailInput = document.getElementById('email');
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(emailInput.value)) {
            emailInput.classList.add('is-invalid');
            isValid = false;
        }
        
        if (!isValid) {
            showAlert('error', '<?= t('messages.error.required_fields') ?>');
            return;
        }
        
        // Submit form
        this.submit();
    });
    
    // Auto-select upload option when file is chosen
    document.getElementById('avatar').addEventListener('change', function() {
        if (this.files.length > 0) {
            document.getElementById('upload_new').checked = true;
        }
    });
    
    // Update preview when initials change
    ['first_name', 'last_name'].forEach(field => {
        document.getElementById(field).addEventListener('input', function() {
            const firstName = document.getElementById('first_name').value;
            const lastName = document.getElementById('last_name').value;
            const initials = (firstName.charAt(0) + lastName.charAt(0)).toUpperCase();
            
            const placeholder = document.querySelector('.avatar-placeholder');
            if (placeholder) {
                placeholder.textContent = initials;
            }
        });
    });
});
</script>

<style>
.nav-tabs .nav-link {
    color: #6c757d;
}

.nav-tabs .nav-link.active {
    color: #495057;
}

.avatar-placeholder {
    margin: 0 auto;
}

.accordion-button:not(.collapsed) {
    color: #0d6efd;
    background-color: rgba(13, 110, 253, 0.1);
}

.form-check-input:checked {
    background-color: #0d6efd;
    border-color: #0d6efd;
}
</style>