<!DOCTYPE html>
<html lang="<?= $this->getCurrentLocale() ?>" dir="<?= $this->getCurrentLocale() === 'ar' ? 'rtl' : 'ltr' ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title><?= htmlspecialchars($title ?? 'Error') ?> - <?= t('app.name') ?></title>
    
    <link rel="icon" type="image/png" href="/assets/images/favicon.png">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Custom Error Styles -->
    <style>
        :root {
            --primary-color: #0d6efd;
            --secondary-color: #6c757d;
            --success-color: #198754;
            --danger-color: #dc3545;
            --warning-color: #ffc107;
            --info-color: #0dcaf0;
            
            --bg-color: #ffffff;
            --bg-secondary: #f8f9fa;
            --text-color: #212529;
            --text-muted: #6c757d;
            --border-color: #dee2e6;
        }
        
        @media (prefers-color-scheme: dark) {
            :root {
                --bg-color: #1a1a1a;
                --bg-secondary: #2d2d2d;
                --text-color: #ffffff;
                --text-muted: #adb5bd;
                --border-color: #495057;
            }
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, var(--bg-secondary), var(--bg-color));
            color: var(--text-color);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0;
            padding: 20px;
        }
        
        .error-container {
            max-width: 800px;
            width: 100%;
            text-align: center;
        }
        
        .error-content {
            background: var(--bg-color);
            border-radius: 16px;
            padding: 40px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        }
        
        .error-icon {
            margin-bottom: 20px;
        }
        
        .error-title {
            font-size: 3rem;
            font-weight: 700;
            color: var(--primary-color);
            margin-bottom: 10px;
        }
        
        .error-subtitle {
            font-size: 1.5rem;
            color: var(--text-muted);
            margin-bottom: 20px;
            font-weight: 400;
        }
        
        .error-description {
            font-size: 1.1rem;
            color: var(--text-color);
            margin-bottom: 30px;
            line-height: 1.6;
        }
        
        .error-actions {
            margin-bottom: 30px;
        }
        
        .error-help h5 {
            color: var(--text-color);
            margin-bottom: 15px;
        }
        
        .error-help ul li {
            text-align: left;
            margin-bottom: 8px;
            color: var(--text-color);
        }
        
        .error-debug {
            text-align: left;
        }
        
        .btn {
            border-radius: 8px;
            font-weight: 500;
            padding: 12px 24px;
            transition: all 0.3s ease;
        }
        
        .btn:hover {
            transform: translateY(-1px);
        }
        
        .card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        }
        
        .alert {
            border: none;
            border-radius: 12px;
        }
        
        @media (max-width: 768px) {
            .error-content {
                padding: 20px;
            }
            
            .error-title {
                font-size: 2rem;
            }
            
            .error-subtitle {
                font-size: 1.2rem;
            }
            
            .btn-lg {
                padding: 10px 20px;
                font-size: 1rem;
            }
        }
        
        /* RTL Support */
        [dir="rtl"] .error-help ul li {
            text-align: right;
        }
        
        [dir="rtl"] .error-debug {
            text-align: right;
        }
        
        /* Animation */
        .error-content {
            animation: fadeInUp 0.6s ease-out;
        }
        
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        /* Status Code Badge */
        .status-code {
            position: absolute;
            top: 20px;
            right: 20px;
            font-size: 0.9rem;
            font-weight: 600;
            padding: 8px 16px;
            border-radius: 20px;
            background: var(--danger-color);
            color: white;
        }
        
        [dir="rtl"] .status-code {
            right: auto;
            left: 20px;
        }
    </style>
</head>
<body>
    <?php if (isset($error_code)): ?>
    <div class="status-code">
        HTTP <?= htmlspecialchars($error_code) ?>
    </div>
    <?php endif; ?>
    
    <?= $content ?>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>