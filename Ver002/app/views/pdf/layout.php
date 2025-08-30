<!DOCTYPE html>
<html lang="<?= $language ?? 'en' ?>" dir="<?= ($language === 'ar' || $isRTL ?? false) ? 'rtl' : 'ltr' ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'MISP Document' ?></title>
    
    <style type="text/css">
        /* PDF-optimized styles */
        @page {
            size: A4;
            margin: 20mm;
            
            @top-left {
                content: "<?= $company_name ?? 'MISP System' ?>";
                font-family: Arial, sans-serif;
                font-size: 10px;
                color: #666666;
            }
            
            @top-right {
                content: "<?= $document_title ?? $title ?? 'Document' ?>";
                font-family: Arial, sans-serif;
                font-size: 10px;
                color: #666666;
            }
            
            @bottom-left {
                content: "Generated on " counter(page) " / " counter(pages);
                font-family: Arial, sans-serif;
                font-size: 10px;
                color: #666666;
            }
            
            @bottom-right {
                content: "Page " counter(page) " of " counter(pages);
                font-family: Arial, sans-serif;
                font-size: 10px;
                color: #666666;
            }
        }
        
        /* Base styles */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 12px;
            line-height: 1.4;
            color: #333333;
            background-color: #ffffff;
        }
        
        /* Typography */
        h1, h2, h3, h4, h5, h6 {
            margin-bottom: 10px;
            font-weight: bold;
            color: #2c3e50;
        }
        
        h1 { font-size: 24px; margin-bottom: 15px; }
        h2 { font-size: 20px; margin-bottom: 12px; }
        h3 { font-size: 16px; margin-bottom: 10px; }
        h4 { font-size: 14px; margin-bottom: 8px; }
        h5 { font-size: 12px; margin-bottom: 6px; }
        h6 { font-size: 11px; margin-bottom: 5px; }
        
        p {
            margin-bottom: 8px;
            line-height: 1.5;
        }
        
        /* Document structure */
        .pdf-container {
            width: 100%;
            max-width: 210mm;
            margin: 0 auto;
            background: #ffffff;
        }
        
        /* Header */
        .pdf-header {
            padding: 20px 0;
            border-bottom: 2px solid #0d6efd;
            margin-bottom: 30px;
            position: relative;
        }
        
        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
        }
        
        .company-info {
            flex: 1;
        }
        
        .company-logo {
            font-size: 32px;
            font-weight: bold;
            color: #0d6efd;
            margin-bottom: 10px;
        }
        
        .company-name {
            font-size: 24px;
            font-weight: bold;
            color: #2c3e50;
            margin-bottom: 5px;
        }
        
        .company-tagline {
            font-size: 14px;
            color: #6c757d;
            margin-bottom: 15px;
        }
        
        .company-details {
            font-size: 11px;
            color: #555555;
            line-height: 1.4;
        }
        
        .document-info {
            text-align: right;
            min-width: 200px;
            padding-left: 20px;
        }
        
        .document-title {
            font-size: 28px;
            font-weight: bold;
            color: #0d6efd;
            margin-bottom: 10px;
            text-transform: uppercase;
        }
        
        .document-number {
            font-size: 16px;
            font-weight: bold;
            color: #2c3e50;
            margin-bottom: 15px;
        }
        
        .document-meta {
            font-size: 11px;
            color: #555555;
        }
        
        .document-meta .meta-item {
            margin-bottom: 5px;
        }
        
        .meta-label {
            font-weight: bold;
            display: inline-block;
            min-width: 80px;
        }
        
        /* Content sections */
        .pdf-content {
            margin-bottom: 30px;
        }
        
        .content-section {
            margin-bottom: 25px;
            page-break-inside: avoid;
        }
        
        .section-title {
            font-size: 16px;
            font-weight: bold;
            color: #2c3e50;
            margin-bottom: 15px;
            padding-bottom: 5px;
            border-bottom: 1px solid #e9ecef;
        }
        
        /* Tables */
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin: 15px 0;
            page-break-inside: auto;
        }
        
        .data-table thead {
            background-color: #f8f9fa;
            page-break-after: avoid;
        }
        
        .data-table th {
            padding: 12px 8px;
            text-align: left;
            font-weight: bold;
            color: #495057;
            border: 1px solid #dee2e6;
            font-size: 11px;
        }
        
        .data-table td {
            padding: 10px 8px;
            border: 1px solid #dee2e6;
            font-size: 11px;
            vertical-align: top;
        }
        
        .data-table tbody tr:nth-child(even) {
            background-color: #f8f9fa;
        }
        
        .data-table tbody tr:hover {
            background-color: #e9ecef;
        }
        
        /* Text alignment helpers */
        .text-left { text-align: left; }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .text-justify { text-align: justify; }
        
        /* Number formatting */
        .number {
            font-family: 'DejaVu Sans Mono', monospace;
            text-align: right;
        }
        
        .currency {
            font-weight: bold;
            color: #0d6efd;
        }
        
        /* Status indicators */
        .status-badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 10px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .status-badge.paid {
            background-color: #d1e7dd;
            color: #0a3622;
        }
        
        .status-badge.pending {
            background-color: #fff3cd;
            color: #664d03;
        }
        
        .status-badge.overdue {
            background-color: #f8d7da;
            color: #58151c;
        }
        
        .status-badge.draft {
            background-color: #e2e3e5;
            color: #41464b;
        }
        
        /* Information boxes */
        .info-box {
            background-color: #f8f9fa;
            border: 1px solid #e9ecef;
            border-left: 4px solid #0d6efd;
            padding: 15px;
            margin: 15px 0;
            page-break-inside: avoid;
        }
        
        .info-box.warning {
            border-left-color: #ffc107;
            background-color: #fffef5;
        }
        
        .info-box.success {
            border-left-color: #198754;
            background-color: #f5fdf5;
        }
        
        .info-box.danger {
            border-left-color: #dc3545;
            background-color: #fdf5f5;
        }
        
        .info-box h4 {
            margin-bottom: 8px;
            font-size: 14px;
            color: #2c3e50;
        }
        
        .info-box p {
            margin-bottom: 5px;
            font-size: 11px;
            color: #555555;
        }
        
        /* Summary sections */
        .summary-section {
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 6px;
            margin: 20px 0;
            page-break-inside: avoid;
        }
        
        .summary-table {
            width: 100%;
            max-width: 400px;
            margin-left: auto;
        }
        
        .summary-table td {
            padding: 8px;
            border: none;
            font-size: 12px;
        }
        
        .summary-table .total-row {
            border-top: 2px solid #0d6efd;
            font-weight: bold;
            font-size: 14px;
            color: #0d6efd;
        }
        
        /* Footer */
        .pdf-footer {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #e9ecef;
            font-size: 10px;
            color: #6c757d;
            page-break-inside: avoid;
        }
        
        .footer-content {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
        }
        
        .footer-left,
        .footer-right {
            flex: 1;
        }
        
        .footer-right {
            text-align: right;
        }
        
        .footer-disclaimer {
            margin-top: 15px;
            font-size: 9px;
            line-height: 1.3;
            color: #868e96;
        }
        
        /* Page breaks */
        .page-break {
            page-break-before: always;
        }
        
        .no-page-break {
            page-break-inside: avoid;
        }
        
        /* Print optimizations */
        .print-only {
            display: none;
        }
        
        @media print {
            .print-only {
                display: block;
            }
            
            .no-print {
                display: none !important;
            }
        }
        
        /* RTL Support */
        [dir="rtl"] .header-content {
            flex-direction: row-reverse;
        }
        
        [dir="rtl"] .document-info {
            text-align: left;
            padding-left: 0;
            padding-right: 20px;
        }
        
        [dir="rtl"] .data-table th,
        [dir="rtl"] .data-table td {
            text-align: right;
        }
        
        [dir="rtl"] .text-left {
            text-align: right;
        }
        
        [dir="rtl"] .text-right {
            text-align: left;
        }
        
        [dir="rtl"] .info-box {
            border-left: none;
            border-right: 4px solid #0d6efd;
        }
        
        [dir="rtl"] .info-box.warning {
            border-right-color: #ffc107;
        }
        
        [dir="rtl"] .info-box.success {
            border-right-color: #198754;
        }
        
        [dir="rtl"] .info-box.danger {
            border-right-color: #dc3545;
        }
        
        [dir="rtl"] .summary-table {
            margin-left: 0;
            margin-right: auto;
        }
        
        [dir="rtl"] .footer-left {
            text-align: right;
        }
        
        [dir="rtl"] .footer-right {
            text-align: left;
        }
        
        /* Watermark support */
        .watermark {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-45deg);
            font-size: 72px;
            font-weight: bold;
            color: rgba(13, 110, 253, 0.1);
            z-index: -1;
            pointer-events: none;
        }
        
        /* Barcode/QR code styling */
        .barcode-container {
            text-align: center;
            margin: 20px 0;
        }
        
        .barcode-image {
            max-width: 200px;
            height: auto;
        }
        
        .barcode-text {
            font-family: 'DejaVu Sans Mono', monospace;
            font-size: 10px;
            margin-top: 5px;
            color: #666666;
        }
        
        /* Signature areas */
        .signature-area {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #e9ecef;
        }
        
        .signature-box {
            display: inline-block;
            width: 200px;
            height: 60px;
            border-bottom: 1px solid #000000;
            margin: 20px 20px 0 0;
            vertical-align: top;
        }
        
        .signature-label {
            font-size: 10px;
            color: #666666;
            margin-top: 5px;
        }
        
        /* Special formatting for numbers and currency */
        .amount-large {
            font-size: 18px;
            font-weight: bold;
            color: #0d6efd;
        }
        
        .amount-words {
            font-style: italic;
            color: #666666;
            font-size: 10px;
        }
    </style>
</head>

<body>
    <div class="pdf-container">
        <!-- Watermark (if specified) -->
        <?php if (isset($watermark)): ?>
        <div class="watermark"><?= htmlspecialchars($watermark) ?></div>
        <?php endif; ?>
        
        <!-- Header -->
        <div class="pdf-header">
            <div class="header-content">
                <div class="company-info">
                    <div class="company-logo">⚙ MISP</div>
                    <div class="company-name"><?= $company_name ?? 'MISP System' ?></div>
                    <div class="company-tagline"><?= $company_tagline ?? 'Management Information System for Spare Parts' ?></div>
                    <div class="company-details">
                        <?php if (isset($company_address)): ?>
                            <?= nl2br(htmlspecialchars($company_address)) ?><br>
                        <?php endif; ?>
                        <?php if (isset($company_phone)): ?>
                            Phone: <?= htmlspecialchars($company_phone) ?><br>
                        <?php endif; ?>
                        <?php if (isset($company_email)): ?>
                            Email: <?= htmlspecialchars($company_email) ?><br>
                        <?php endif; ?>
                        <?php if (isset($company_website)): ?>
                            Website: <?= htmlspecialchars($company_website) ?>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="document-info">
                    <div class="document-title"><?= $document_type ?? 'Document' ?></div>
                    <?php if (isset($document_number)): ?>
                    <div class="document-number"># <?= htmlspecialchars($document_number) ?></div>
                    <?php endif; ?>
                    <div class="document-meta">
                        <?php if (isset($document_date)): ?>
                        <div class="meta-item">
                            <span class="meta-label">Date:</span>
                            <?= $document_date ?>
                        </div>
                        <?php endif; ?>
                        <?php if (isset($due_date)): ?>
                        <div class="meta-item">
                            <span class="meta-label">Due:</span>
                            <?= $due_date ?>
                        </div>
                        <?php endif; ?>
                        <?php if (isset($status)): ?>
                        <div class="meta-item">
                            <span class="meta-label">Status:</span>
                            <span class="status-badge <?= strtolower($status) ?>"><?= htmlspecialchars($status) ?></span>
                        </div>
                        <?php endif; ?>
                        <?php if (isset($reference)): ?>
                        <div class="meta-item">
                            <span class="meta-label">Ref:</span>
                            <?= htmlspecialchars($reference) ?>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Content -->
        <div class="pdf-content">
            <?= $content ?>
        </div>
        
        <!-- Footer -->
        <div class="pdf-footer">
            <div class="footer-content">
                <div class="footer-left">
                    <strong><?= $company_name ?? 'MISP System' ?></strong><br>
                    Generated on <?= date('M d, Y H:i T') ?>
                </div>
                <div class="footer-right">
                    <?php if (isset($terms_url)): ?>
                        <a href="<?= $terms_url ?>">Terms & Conditions</a><br>
                    <?php endif; ?>
                    <?php if (isset($contact_info)): ?>
                        <?= htmlspecialchars($contact_info) ?>
                    <?php endif; ?>
                </div>
            </div>
            
            <?php if (isset($footer_disclaimer)): ?>
            <div class="footer-disclaimer">
                <?= nl2br(htmlspecialchars($footer_disclaimer)) ?>
            </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>