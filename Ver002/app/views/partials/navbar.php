<nav class="navbar navbar-expand-lg navbar-dark bg-primary fixed-top">
    <div class="container-fluid">
        <!-- Brand -->
        <a class="navbar-brand d-flex align-items-center" href="/dashboard">
            <i class="fas fa-cogs me-2"></i>
            <span class="d-none d-sm-inline"><?= htmlspecialchars($app_name ?? 'SPMS') ?></span>
        </a>

        <!-- Mobile Toggle -->
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarNav">
            <!-- Main Navigation -->
            <ul class="navbar-nav me-auto">
                <li class="nav-item">
                    <a class="nav-link <?= $_SERVER['REQUEST_URI'] === '/dashboard' ? 'active' : '' ?>" href="/dashboard">
                        <i class="fas fa-tachometer-alt me-1"></i>
                        <?= t('nav.dashboard') ?>
                    </a>
                </li>

                <!-- Products Dropdown -->
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle <?= str_starts_with($_SERVER['REQUEST_URI'], '/products') ? 'active' : '' ?>" 
                       href="#" role="button" data-bs-toggle="dropdown">
                        <i class="fas fa-boxes me-1"></i>
                        <?= t('nav.products') ?>
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="/products">
                            <i class="fas fa-list me-2"></i><?= t('nav.all_products') ?>
                        </a></li>
                        <li><a class="dropdown-item" href="/products/create">
                            <i class="fas fa-plus me-2"></i><?= t('nav.add_product') ?>
                        </a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="/products?filter=low_stock">
                            <i class="fas fa-exclamation-triangle text-warning me-2"></i><?= t('nav.low_stock') ?>
                        </a></li>
                    </ul>
                </li>

                <!-- Clients -->
                <li class="nav-item">
                    <a class="nav-link <?= str_starts_with($_SERVER['REQUEST_URI'], '/clients') ? 'active' : '' ?>" href="/clients">
                        <i class="fas fa-users me-1"></i>
                        <?= t('nav.clients') ?>
                    </a>
                </li>

                <!-- Sales Dropdown -->
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                        <i class="fas fa-chart-line me-1"></i>
                        <?= t('nav.sales') ?>
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="/quotes">
                            <i class="fas fa-file-alt me-2"></i><?= t('nav.quotes') ?>
                        </a></li>
                        <li><a class="dropdown-item" href="/salesorders">
                            <i class="fas fa-shopping-cart me-2"></i><?= t('nav.sales_orders') ?>
                        </a></li>
                        <li><a class="dropdown-item" href="/invoices">
                            <i class="fas fa-file-invoice-dollar me-2"></i><?= t('nav.invoices') ?>
                        </a></li>
                        <li><a class="dropdown-item" href="/payments">
                            <i class="fas fa-money-bill-wave me-2"></i><?= t('nav.payments') ?>
                        </a></li>
                    </ul>
                </li>

                <!-- Management Dropdown (for managers and admins) -->
                <?php if (isset($current_user) && ($current_user['role'] === 'manager' || $current_user['role'] === 'admin')): ?>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                        <i class="fas fa-cog me-1"></i>
                        <?= t('nav.management') ?>
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="/suppliers">
                            <i class="fas fa-truck me-2"></i><?= t('nav.suppliers') ?>
                        </a></li>
                        <li><a class="dropdown-item" href="/warehouses">
                            <i class="fas fa-warehouse me-2"></i><?= t('nav.warehouses') ?>
                        </a></li>
                        <li><a class="dropdown-item" href="/currencies">
                            <i class="fas fa-dollar-sign me-2"></i><?= t('nav.currencies') ?>
                        </a></li>
                        <li><a class="dropdown-item" href="/dropdowns">
                            <i class="fas fa-list me-2"></i><?= t('nav.categories') ?>
                        </a></li>
                        <?php if ($current_user['role'] === 'admin'): ?>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="/users">
                            <i class="fas fa-users-cog me-2"></i><?= t('nav.users') ?>
                        </a></li>
                        <?php endif; ?>
                    </ul>
                </li>
                <?php endif; ?>
            </ul>

            <!-- Right Side Navigation -->
            <ul class="navbar-nav">
                <!-- Language Switcher -->
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                        <i class="fas fa-globe me-1"></i>
                        <span class="d-none d-md-inline"><?= \App\Core\I18n::getCurrentLanguage()['native'] ?></span>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <?php foreach (\App\Core\I18n::getAvailableLanguages() as $code => $lang): ?>
                            <li>
                                <a class="dropdown-item <?= $current_lang === $code ? 'active' : '' ?>" 
                                   href="<?= \App\Core\I18n::getLanguageSwitchUrl($code) ?>">
                                    <?= htmlspecialchars($lang['native']) ?>
                                    <?php if ($current_lang === $code): ?>
                                        <i class="fas fa-check text-success ms-2"></i>
                                    <?php endif; ?>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </li>

                <!-- Notifications -->
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle position-relative" href="#" role="button" data-bs-toggle="dropdown" id="notificationsDropdown">
                        <i class="fas fa-bell"></i>
                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" id="notification-count">
                            0
                        </span>
                    </a>
                    <div class="dropdown-menu dropdown-menu-end notification-dropdown" style="width: 300px;">
                        <div class="dropdown-header d-flex justify-content-between align-items-center">
                            <span><?= t('nav.notifications') ?></span>
                            <small class="text-muted" id="notification-time"><?= t('nav.just_now') ?></small>
                        </div>
                        <div class="dropdown-divider"></div>
                        <div id="notification-list">
                            <div class="dropdown-item-text text-center text-muted">
                                <i class="fas fa-bell-slash me-2"></i>
                                <?= t('nav.no_notifications') ?>
                            </div>
                        </div>
                        <div class="dropdown-divider"></div>
                        <a class="dropdown-item text-center small" href="/notifications">
                            <?= t('nav.view_all_notifications') ?>
                        </a>
                    </div>
                </li>

                <!-- User Dropdown -->
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" role="button" data-bs-toggle="dropdown">
                        <div class="user-avatar me-2">
                            <i class="fas fa-user-circle"></i>
                        </div>
                        <div class="d-none d-md-block">
                            <div class="user-name"><?= htmlspecialchars($current_user['name'] ?? 'User') ?></div>
                            <small class="user-role text-muted"><?= htmlspecialchars(ucfirst($current_user['role'] ?? 'user')) ?></small>
                        </div>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="/profile">
                            <i class="fas fa-user me-2"></i><?= t('nav.profile') ?>
                        </a></li>
                        <li><a class="dropdown-item" href="/profile/password">
                            <i class="fas fa-key me-2"></i><?= t('nav.change_password') ?>
                        </a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item text-danger" href="/logout">
                            <i class="fas fa-sign-out-alt me-2"></i><?= t('nav.logout') ?>
                        </a></li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</nav>

<!-- Push content down due to fixed navbar -->
<div style="height: 76px;"></div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Load notifications
    loadNotifications();
    
    // Auto-refresh notifications every 30 seconds
    setInterval(loadNotifications, 30000);
});

function loadNotifications() {
    fetch('/api/notifications', {
        method: 'GET',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-Token': window.App.csrfToken
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            updateNotificationUI(data.data);
        }
    })
    .catch(error => {
        console.error('Error loading notifications:', error);
    });
}

function updateNotificationUI(notifications) {
    const countBadge = document.getElementById('notification-count');
    const notificationList = document.getElementById('notification-list');
    
    // Update count
    const unreadCount = notifications.filter(n => !n.is_read).length;
    countBadge.textContent = unreadCount;
    countBadge.style.display = unreadCount > 0 ? 'inline-block' : 'none';
    
    // Update list
    if (notifications.length === 0) {
        notificationList.innerHTML = `
            <div class="dropdown-item-text text-center text-muted">
                <i class="fas fa-bell-slash me-2"></i>
                <?= t('nav.no_notifications') ?>
            </div>
        `;
    } else {
        notificationList.innerHTML = notifications.slice(0, 5).map(notification => `
            <a class="dropdown-item ${notification.is_read ? '' : 'fw-bold'}" href="${notification.url || '#'}">
                <div class="d-flex">
                    <div class="flex-shrink-0">
                        <i class="fas fa-${notification.icon || 'info-circle'} text-${notification.type || 'primary'}"></i>
                    </div>
                    <div class="flex-grow-1 ms-2">
                        <div class="small">${notification.message}</div>
                        <small class="text-muted">${notification.time_ago}</small>
                    </div>
                </div>
            </a>
        `).join('');
    }
    
    // Update time
    document.getElementById('notification-time').textContent = new Date().toLocaleTimeString();
}
</script>