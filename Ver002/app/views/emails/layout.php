<!DOCTYPE html>
<html lang="<?= $language ?? 'en' ?>" dir="<?= ($language === 'ar' || $isRTL ?? false) ? 'rtl' : 'ltr' ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title><?= $subject ?? 'MISP System Notification' ?></title>
    
    <style type="text/css">
        /* Reset styles */
        body, table, td, a { -webkit-text-size-adjust: 100%; -ms-text-size-adjust: 100%; }
        table, td { mso-table-lspace: 0pt; mso-table-rspace: 0pt; }
        img { -ms-interpolation-mode: bicubic; border: 0; height: auto; line-height: 100%; outline: none; text-decoration: none; }
        
        /* Base styles */
        body {
            margin: 0 !important;
            padding: 0 !important;
            background-color: #f4f7fa !important;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            font-size: 16px;
            line-height: 1.6;
            color: #333333;
        }
        
        /* Container */
        .email-container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }
        
        /* Header */
        .email-header {
            background: linear-gradient(135deg, #0d6efd 0%, #0056b3 100%);
            color: #ffffff;
            padding: 30px 40px;
            text-align: center;
        }
        
        .email-logo {
            font-size: 28px;
            font-weight: bold;
            margin-bottom: 10px;
        }
        
        .email-title {
            font-size: 24px;
            font-weight: 600;
            margin: 0;
            line-height: 1.3;
        }
        
        .email-subtitle {
            font-size: 16px;
            margin: 5px 0 0 0;
            opacity: 0.9;
        }
        
        /* Content */
        .email-content {
            padding: 40px;
        }
        
        .email-greeting {
            font-size: 18px;
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 20px;
        }
        
        .email-body {
            font-size: 16px;
            line-height: 1.7;
            color: #555555;
            margin-bottom: 30px;
        }
        
        .email-body p {
            margin: 0 0 16px 0;
        }
        
        .email-body p:last-child {
            margin-bottom: 0;
        }
        
        /* Buttons */
        .btn-container {
            text-align: center;
            margin: 30px 0;
        }
        
        .btn {
            display: inline-block;
            padding: 16px 32px;
            background-color: #0d6efd;
            color: #ffffff !important;
            text-decoration: none;
            border-radius: 6px;
            font-weight: 600;
            font-size: 16px;
            transition: background-color 0.3s ease;
        }
        
        .btn:hover {
            background-color: #0056b3;
        }
        
        .btn-success {
            background-color: #198754;
        }
        
        .btn-success:hover {
            background-color: #157347;
        }
        
        .btn-warning {
            background-color: #ffc107;
            color: #000000 !important;
        }
        
        .btn-warning:hover {
            background-color: #e0a800;
        }
        
        .btn-danger {
            background-color: #dc3545;
        }
        
        .btn-danger:hover {
            background-color: #bb2d3b;
        }
        
        /* Info boxes */
        .info-box {
            background-color: #f8f9fa;
            border-left: 4px solid #0d6efd;
            padding: 20px;
            margin: 25px 0;
            border-radius: 0 6px 6px 0;
        }
        
        .info-box.success {
            border-left-color: #198754;
            background-color: #f0f9f0;
        }
        
        .info-box.warning {
            border-left-color: #ffc107;
            background-color: #fffef0;
        }
        
        .info-box.danger {
            border-left-color: #dc3545;
            background-color: #fef0f0;
        }
        
        .info-box h4 {
            margin: 0 0 10px 0;
            color: #2c3e50;
            font-size: 18px;
        }
        
        .info-box p {
            margin: 0;
            font-size: 14px;
            color: #666666;
        }
        
        /* Tables */
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin: 25px 0;
            background-color: #ffffff;
            border-radius: 6px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }
        
        .data-table th {
            background-color: #f8f9fa;
            color: #495057;
            font-weight: 600;
            padding: 15px;
            text-align: left;
            border-bottom: 2px solid #e9ecef;
        }
        
        .data-table td {
            padding: 15px;
            border-bottom: 1px solid #e9ecef;
        }
        
        .data-table tr:last-child td {
            border-bottom: none;
        }
        
        .data-table tr:hover {
            background-color: #f8f9fa;
        }
        
        /* Footer */
        .email-footer {
            background-color: #f8f9fa;
            color: #6c757d;
            padding: 30px 40px;
            text-align: center;
            border-top: 1px solid #e9ecef;
        }
        
        .footer-content {
            margin-bottom: 20px;
        }
        
        .footer-links {
            margin-bottom: 20px;
        }
        
        .footer-links a {
            color: #0d6efd;
            text-decoration: none;
            margin: 0 15px;
            font-size: 14px;
        }
        
        .footer-links a:hover {
            text-decoration: underline;
        }
        
        .footer-disclaimer {
            font-size: 12px;
            color: #868e96;
            line-height: 1.5;
        }
        
        /* Status badges */
        .status-badge {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .status-badge.success {
            background-color: #d1e7dd;
            color: #0a3622;
        }
        
        .status-badge.warning {
            background-color: #fff3cd;
            color: #664d03;
        }
        
        .status-badge.danger {
            background-color: #f8d7da;
            color: #58151c;
        }
        
        .status-badge.info {
            background-color: #cff4fc;
            color: #055160;
        }
        
        /* Responsive */
        @media only screen and (max-width: 600px) {
            .email-container {
                width: 100% !important;
                margin: 0 !important;
                border-radius: 0 !important;
            }
            
            .email-header,
            .email-content,
            .email-footer {
                padding: 20px !important;
            }
            
            .email-title {
                font-size: 20px !important;
            }
            
            .btn {
                padding: 12px 24px !important;
                font-size: 14px !important;
            }
            
            .data-table {
                font-size: 14px;
            }
            
            .data-table th,
            .data-table td {
                padding: 10px !important;
            }
        }
        
        /* RTL Support */
        [dir="rtl"] .info-box {
            border-left: none;
            border-right: 4px solid #0d6efd;
            border-radius: 6px 0 0 6px;
        }
        
        [dir="rtl"] .info-box.success {
            border-right-color: #198754;
        }
        
        [dir="rtl"] .info-box.warning {
            border-right-color: #ffc107;
        }
        
        [dir="rtl"] .info-box.danger {
            border-right-color: #dc3545;
        }
        
        [dir="rtl"] .data-table th,
        [dir="rtl"] .data-table td {
            text-align: right;
        }
        
        /* Dark mode support */
        @media (prefers-color-scheme: dark) {
            .email-container {
                background-color: #2d3748;
            }
            
            .email-content {
                color: #e2e8f0;
            }
            
            .email-greeting {
                color: #f7fafc;
            }
            
            .email-body {
                color: #cbd5e0;
            }
            
            .info-box {
                background-color: #4a5568;
            }
            
            .data-table {
                background-color: #2d3748;
            }
            
            .data-table th {
                background-color: #4a5568;
                color: #e2e8f0;
            }
            
            .data-table td {
                color: #cbd5e0;
            }
        }
    </style>
</head>

<body>
    <div class="email-container">
        <!-- Header -->
        <div class="email-header">
            <div class="email-logo">
                <i class="fas fa-cogs" style="margin-right: 10px;"></i>MISP
            </div>
            <h1 class="email-title"><?= $title ?? 'System Notification' ?></h1>
            <?php if (isset($subtitle)): ?>
            <p class="email-subtitle"><?= htmlspecialchars($subtitle) ?></p>
            <?php endif; ?>
        </div>
        
        <!-- Content -->
        <div class="email-content">
            <?php if (isset($greeting)): ?>
            <div class="email-greeting">
                <?= htmlspecialchars($greeting) ?>
            </div>
            <?php endif; ?>
            
            <div class="email-body">
                <?= $content ?>
            </div>
            
            <?php if (isset($show_signature) && $show_signature): ?>
            <div style="margin-top: 40px; padding-top: 20px; border-top: 2px solid #e9ecef;">
                <p style="margin: 0; color: #6c757d;">
                    <?= $signature ?? t('emails.best_regards') ?><br>
                    <strong>MISP System Team</strong><br>
                    <small><?= t('emails.automated_message') ?></small>
                </p>
            </div>
            <?php endif; ?>
        </div>
        
        <!-- Footer -->
        <div class="email-footer">
            <div class="footer-content">
                <strong>MISP - Management Information System for Spare Parts</strong>
            </div>
            
            <div class="footer-links">
                <a href="<?= $app_url ?? '#' ?>/dashboard">Dashboard</a>
                <a href="<?= $app_url ?? '#' ?>/profile">Profile</a>
                <a href="<?= $app_url ?? '#' ?>/help">Help Center</a>
                <a href="<?= $app_url ?? '#' ?>/contact">Contact</a>
            </div>
            
            <div class="footer-disclaimer">
                <p>
                    This email was sent to <?= htmlspecialchars($recipient_email ?? 'you') ?> 
                    because you have an account with MISP System.
                </p>
                <p>
                    If you no longer wish to receive these emails, you can 
                    <a href="<?= $unsubscribe_url ?? '#' ?>" style="color: #6c757d;">unsubscribe here</a>.
                </p>
                <p style="margin-top: 15px;">
                    &copy; <?= date('Y') ?> MISP System. All rights reserved.
                </p>
            </div>
        </div>
    </div>
</body>
</html>