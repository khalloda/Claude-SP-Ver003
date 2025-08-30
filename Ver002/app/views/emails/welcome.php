<?php
// Welcome email template for new users
$title = t('emails.welcome.title', 'Welcome to MISP System');
$subtitle = t('emails.welcome.subtitle', 'Your account has been created successfully');
$greeting = t('emails.welcome.greeting', 'Hello :name!', ['name' => $user_name ?? 'User']);
$show_signature = true;

ob_start();
?>

<p>
    <?= t('emails.welcome.intro', 'Welcome to the MISP (Management Information System for Spare Parts)! We\'re excited to have you join our platform.') ?>
</p>

<p>
    <?= t('emails.welcome.account_created', 'Your account has been successfully created with the following details:') ?>
</p>

<div class="info-box">
    <h4><?= t('emails.account_details', 'Account Details') ?></h4>
    <p><strong><?= t('auth.email') ?>:</strong> <?= htmlspecialchars($user_email ?? '') ?></p>
    <p><strong><?= t('auth.role') ?>:</strong> <?= htmlspecialchars($user_role ?? 'User') ?></p>
    <p><strong><?= t('emails.account_created_at') ?>:</strong> <?= date('M d, Y H:i T') ?></p>
</div>

<div class="btn-container">
    <a href="<?= $login_url ?? '/login' ?>" class="btn">
        <?= t('emails.welcome.login_now', 'Login to Your Account') ?>
    </a>
</div>

<p>
    <?= t('emails.welcome.getting_started', 'Here are some things you can do to get started:') ?>
</p>

<ul style="color: #555555; padding-left: 20px;">
    <li><?= t('emails.welcome.step1', 'Complete your profile information') ?></li>
    <li><?= t('emails.welcome.step2', 'Explore the dashboard and available features') ?></li>
    <li><?= t('emails.welcome.step3', 'Set up your preferences and notifications') ?></li>
    <li><?= t('emails.welcome.step4', 'Start managing your spare parts inventory') ?></li>
</ul>

<div class="info-box success">
    <h4><?= t('emails.security_reminder', 'Security Reminder') ?></h4>
    <p>
        <?= t('emails.welcome.security_tip', 'For security purposes, please make sure to change your password on first login and enable two-factor authentication if available.') ?>
    </p>
</div>

<p>
    <?= t('emails.welcome.need_help', 'If you have any questions or need assistance, please don\'t hesitate to contact our support team.') ?>
</p>

<?php
$content = ob_get_clean();
include 'layout.php';
?>