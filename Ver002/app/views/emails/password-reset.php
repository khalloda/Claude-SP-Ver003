<?php
// Password reset email template
$title = t('emails.password_reset.title', 'Password Reset Request');
$subtitle = t('emails.password_reset.subtitle', 'Reset your MISP account password');
$greeting = t('emails.password_reset.greeting', 'Hello :name!', ['name' => $user_name ?? 'User']);
$show_signature = true;

ob_start();
?>

<p>
    <?= t('emails.password_reset.intro', 'We received a request to reset your password for your MISP account.') ?>
</p>

<p>
    <?= t('emails.password_reset.instructions', 'To reset your password, please click the button below. This link will expire in :minutes minutes for security purposes.', ['minutes' => $expiry_minutes ?? '30']) ?>
</p>

<div class="btn-container">
    <a href="<?= $reset_url ?? '#' ?>" class="btn">
        <?= t('emails.password_reset.reset_button', 'Reset My Password') ?>
    </a>
</div>

<div class="info-box warning">
    <h4><?= t('emails.security_notice', 'Security Notice') ?></h4>
    <p>
        <?= t('emails.password_reset.security_info', 'If you did not request a password reset, please ignore this email. Your password will remain unchanged.') ?>
    </p>
</div>

<p style="font-size: 14px; color: #666666;">
    <?= t('emails.password_reset.manual_link', 'If the button above doesn\'t work, you can copy and paste the following link into your browser:') ?>
</p>

<p style="font-size: 12px; word-break: break-all; background-color: #f8f9fa; padding: 10px; border-radius: 4px; color: #495057;">
    <?= $reset_url ?? '#' ?>
</p>

<div class="info-box">
    <h4><?= t('emails.request_details', 'Request Details') ?></h4>
    <p><strong><?= t('emails.requested_at') ?>:</strong> <?= date('M d, Y H:i T') ?></p>
    <p><strong><?= t('emails.ip_address') ?>:</strong> <?= htmlspecialchars($ip_address ?? 'Unknown') ?></p>
    <p><strong><?= t('emails.user_agent') ?>:</strong> <?= htmlspecialchars($user_agent ?? 'Unknown') ?></p>
</div>

<p>
    <?= t('emails.password_reset.contact_support', 'If you\'re having trouble resetting your password or believe this request was made in error, please contact our support team immediately.') ?>
</p>

<?php
$content = ob_get_clean();
include 'layout.php';
?>