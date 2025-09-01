<!DOCTYPE html>
<html lang="<?= htmlspecialchars($current_lang ?? 'en') ?>" dir="<?= \App\Core\I18n::getDirection() ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="<?= htmlspecialchars($csrf_token) ?>">
    
    <title><?= isset($page_title) ? htmlspecialchars($page_title) . ' - ' : '' ?><?= htmlspecialchars($app_name ?? 'Spare Parts Management') ?></title>
    
    <!-- Security headers are set via HTTP headers in middleware/server config -->
    
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="/assets/images/favicon.ico">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
    <!-- Unified Design System -->
    <link href="/assets/css/system.css" rel="stylesheet">
    
    <?php if (\App\Core\I18n::isRtl()): ?>
    <!-- RTL Support -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.rtl.min.css" rel="stylesheet">
    <link href="/assets/css/rtl.css" rel="stylesheet">
    <?php endif; ?>
    
    <!-- Additional CSS -->
    <?php if (isset($additional_css)): ?>
        <?php foreach ($additional_css as $css): ?>
            <link href="<?= htmlspecialchars($css) ?>" rel="stylesheet">
        <?php endforeach; ?>
    <?php endif; ?>
</head>

<body class="<?= \App\Core\I18n::isRtl() ? 'rtl' : 'ltr' ?>">
    <!-- Navigation -->
    <?php if (isset($current_user)): ?>
        <?php include __DIR__ . '/../partials/navbar.php'; ?>
    <?php endif; ?>
    
    <!-- Main Content -->
    <main class="<?= isset($current_user) ? 'main-content' : 'auth-content' ?>">
        <!-- Flash Messages -->
        <?php $flash_messages = $_SESSION['flash'] ?? []; ?>
        <?php if (!empty($flash_messages)): ?>
            <div class="flash-messages">
                <?php foreach ($flash_messages as $type => $message): ?>
                    <div class="alert alert-<?= $type === 'error' ? 'danger' : htmlspecialchars($type) ?> alert-dismissible fade show" role="alert">
                        <i class="fas fa-<?= $type === 'error' ? 'exclamation-triangle' : ($type === 'success' ? 'check-circle' : 'info-circle') ?>"></i>
                        <?= htmlspecialchars($message) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endforeach; ?>
                <?php unset($_SESSION['flash']); ?>
            </div>
        <?php endif; ?>
        
        <!-- Page Content -->
        <div class="container-fluid">
            <?= $content ?? '' ?>
        </div>
    </main>
    
    <!-- Footer -->
    <?php if (isset($current_user)): ?>
        <?php include __DIR__ . '/../partials/footer.php'; ?>
    <?php endif; ?>
    
    <!-- Loading Overlay -->
    <div id="loading-overlay" class="loading-overlay" style="display: none;">
        <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden"><?= t('common.loading') ?></span>
        </div>
    </div>
    
    <!-- Scripts -->
    <!-- Bootstrap JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    
    <!-- jQuery -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.0/jquery.min.js"></script>
    
    <!-- Chart.js for dashboard -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js"></script>
    
    <!-- App Configuration -->
    <script>
        window.App = {
            csrfToken: '<?= htmlspecialchars($csrf_token) ?>',
            baseUrl: '<?= htmlspecialchars($app_url ?? '') ?>',
            lang: '<?= htmlspecialchars($current_lang ?? 'en') ?>',
            isRtl: <?= \App\Core\I18n::isRtl() ? 'true' : 'false' ?>,
            user: <?= isset($current_user) ? json_encode($current_user, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) : 'null' ?>
        };
    </script>
    
    <!-- Unified System JS -->
    <script src="/assets/js/system.js"></script>
    
    <!-- Additional JS -->
    <?php if (isset($additional_js)): ?>
        <?php foreach ($additional_js as $js): ?>
            <script src="<?= htmlspecialchars($js) ?>"></script>
        <?php endforeach; ?>
    <?php endif; ?>
    
    <!-- Page-specific scripts -->
    <?php if (isset($page_scripts)): ?>
        <script><?= $page_scripts ?></script>
    <?php endif; ?>
</body>
</html>