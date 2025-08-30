<?php 
$title = 'Profile Preferences - MISP System';
$page = 'profile';
$subpage = 'preferences';
include_once '../layouts/app.php';
startContent();
?>

<!-- Page Header -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-1">Profile Preferences</h1>
        <p class="text-muted mb-0">Customize your system preferences and settings</p>
    </div>
    <div class="d-flex gap-2">
        <a href="/profile" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left"></i> Back to Profile
        </a>
        <button type="button" class="btn btn-primary" onclick="savePreferences()">
            <i class="fas fa-save"></i> Save Preferences
        </button>
    </div>
</div>

<!-- Preferences Form -->
<form id="preferencesForm" method="POST" action="/profile/update-preferences">
    <?= csrf_field() ?>
    
    <div class="row">
        <!-- Left Column -->
        <div class="col-md-8">
            <!-- Display Settings -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-desktop text-primary me-2"></i>
                        Display Settings
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="language" class="form-label">Language</label>
                            <select class="form-select" id="language" name="language">
                                <option value="en" selected>English</option>
                                <option value="ar">العربية (Arabic)</option>
                            </select>
                            <div class="form-text">Select your preferred language</div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="timezone" class="form-label">Timezone</label>
                            <select class="form-select" id="timezone" name="timezone">
                                <option value="UTC" selected>UTC (Coordinated Universal Time)</option>
                                <option value="America/New_York">Eastern Time (EST/EDT)</option>
                                <option value="America/Chicago">Central Time (CST/CDT)</option>
                                <option value="America/Denver">Mountain Time (MST/MDT)</option>
                                <option value="America/Los_Angeles">Pacific Time (PST/PDT)</option>
                                <option value="Europe/London">London (GMT/BST)</option>
                                <option value="Europe/Paris">Paris (CET/CEST)</option>
                                <option value="Europe/Berlin">Berlin (CET/CEST)</option>
                                <option value="Asia/Dubai">Dubai (GST)</option>
                                <option value="Asia/Riyadh">Riyadh (AST)</option>
                                <option value="Asia/Tokyo">Tokyo (JST)</option>
                                <option value="Asia/Shanghai">Shanghai (CST)</option>
                            </select>
                            <div class="form-text">All dates and times will be displayed in this timezone</div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="date_format" class="form-label">Date Format</label>
                            <select class="form-select" id="date_format" name="date_format">
                                <option value="Y-m-d" selected>2025-08-29 (YYYY-MM-DD)</option>
                                <option value="m/d/Y">08/29/2025 (MM/DD/YYYY)</option>
                                <option value="d/m/Y">29/08/2025 (DD/MM/YYYY)</option>
                                <option value="d.m.Y">29.08.2025 (DD.MM.YYYY)</option>
                                <option value="M d, Y">Aug 29, 2025</option>
                                <option value="d M Y">29 Aug 2025</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="time_format" class="form-label">Time Format</label>
                            <select class="form-select" id="time_format" name="time_format">
                                <option value="H:i" selected>24-hour (14:30)</option>
                                <option value="g:i A">12-hour (2:30 PM)</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="currency_display" class="form-label">Primary Currency</label>
                            <select class="form-select" id="currency_display" name="currency_display">
                                <option value="USD" selected>USD ($)</option>
                                <option value="EUR">EUR (€)</option>
                                <option value="GBP">GBP (£)</option>
                                <option value="AED">AED (د.إ)</option>
                                <option value="SAR">SAR (ر.س)</option>
                                <option value="JPY">JPY (¥)</option>
                            </select>
                            <div class="form-text">Default currency for display</div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="rows_per_page" class="form-label">Table Rows per Page</label>
                            <select class="form-select" id="rows_per_page" name="rows_per_page">
                                <option value="10">10 rows</option>
                                <option value="25" selected>25 rows</option>
                                <option value="50">50 rows</option>
                                <option value="100">100 rows</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Notification Settings -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-bell text-warning me-2"></i>
                        Notification Preferences
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="fw-bold mb-3">Email Notifications</h6>
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" value="1" id="email_new_orders" name="email_new_orders" checked>
                                <label class="form-check-label" for="email_new_orders">
                                    New Orders
                                </label>
                            </div>
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" value="1" id="email_payment_received" name="email_payment_received" checked>
                                <label class="form-check-label" for="email_payment_received">
                                    Payment Received
                                </label>
                            </div>
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" value="1" id="email_low_stock" name="email_low_stock" checked>
                                <label class="form-check-label" for="email_low_stock">
                                    Low Stock Alerts
                                </label>
                            </div>
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" value="1" id="email_system_updates" name="email_system_updates">
                                <label class="form-check-label" for="email_system_updates">
                                    System Updates
                                </label>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <h6 class="fw-bold mb-3">In-App Notifications</h6>
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" value="1" id="app_new_quotes" name="app_new_quotes" checked>
                                <label class="form-check-label" for="app_new_quotes">
                                    New Quote Requests
                                </label>
                            </div>
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" value="1" id="app_order_updates" name="app_order_updates" checked>
                                <label class="form-check-label" for="app_order_updates">
                                    Order Status Updates
                                </label>
                            </div>
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" value="1" id="app_inventory_alerts" name="app_inventory_alerts" checked>
                                <label class="form-check-label" for="app_inventory_alerts">
                                    Inventory Alerts
                                </label>
                            </div>
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" value="1" id="app_system_maintenance" name="app_system_maintenance" checked>
                                <label class="form-check-label" for="app_system_maintenance">
                                    Maintenance Notifications
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Dashboard Preferences -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-tachometer-alt text-info me-2"></i>
                        Dashboard Preferences
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="dashboard_refresh" class="form-label">Auto-refresh Dashboard</label>
                            <select class="form-select" id="dashboard_refresh" name="dashboard_refresh">
                                <option value="0">Never</option>
                                <option value="30">Every 30 seconds</option>
                                <option value="60" selected>Every 1 minute</option>
                                <option value="300">Every 5 minutes</option>
                                <option value="600">Every 10 minutes</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="default_dashboard_view" class="form-label">Default Dashboard View</label>
                            <select class="form-select" id="default_dashboard_view" name="default_dashboard_view">
                                <option value="overview" selected>Overview</option>
                                <option value="sales">Sales Focus</option>
                                <option value="inventory">Inventory Focus</option>
                                <option value="financial">Financial Focus</option>
                            </select>
                        </div>
                    </div>
                    
                    <h6 class="fw-bold mb-3">Dashboard Widgets</h6>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" value="1" id="widget_sales_overview" name="widgets[]" checked>
                                <label class="form-check-label" for="widget_sales_overview">
                                    Sales Overview
                                </label>
                            </div>
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" value="1" id="widget_recent_orders" name="widgets[]" checked>
                                <label class="form-check-label" for="widget_recent_orders">
                                    Recent Orders
                                </label>
                            </div>
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" value="1" id="widget_inventory_status" name="widgets[]" checked>
                                <label class="form-check-label" for="widget_inventory_status">
                                    Inventory Status
                                </label>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" value="1" id="widget_top_products" name="widgets[]" checked>
                                <label class="form-check-label" for="widget_top_products">
                                    Top Products
                                </label>
                            </div>
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" value="1" id="widget_payment_status" name="widgets[]" checked>
                                <label class="form-check-label" for="widget_payment_status">
                                    Payment Status
                                </label>
                            </div>
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" value="1" id="widget_quick_actions" name="widgets[]" checked>
                                <label class="form-check-label" for="widget_quick_actions">
                                    Quick Actions
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Security Preferences -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-shield-alt text-success me-2"></i>
                        Security & Privacy
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="session_timeout" class="form-label">Session Timeout</label>
                            <select class="form-select" id="session_timeout" name="session_timeout">
                                <option value="900">15 minutes</option>
                                <option value="1800" selected>30 minutes</option>
                                <option value="3600">1 hour</option>
                                <option value="7200">2 hours</option>
                                <option value="14400">4 hours</option>
                            </select>
                            <div class="form-text">Automatic logout after inactivity</div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="form-check mt-4 pt-2">
                                <input class="form-check-input" type="checkbox" value="1" id="two_factor_enabled" name="two_factor_enabled">
                                <label class="form-check-label" for="two_factor_enabled">
                                    Enable Two-Factor Authentication
                                </label>
                            </div>
                            <div class="form-text">Requires secondary authentication for login</div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-12">
                            <h6 class="fw-bold mb-3">Privacy Settings</h6>
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" value="1" id="activity_tracking" name="activity_tracking" checked>
                                <label class="form-check-label" for="activity_tracking">
                                    Allow activity tracking for analytics
                                </label>
                            </div>
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" value="1" id="usage_statistics" name="usage_statistics" checked>
                                <label class="form-check-label" for="usage_statistics">
                                    Share usage statistics for system improvement
                                </label>
                            </div>
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" value="1" id="login_notifications" name="login_notifications" checked>
                                <label class="form-check-label" for="login_notifications">
                                    Email me about new login sessions
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Column - Preview & Actions -->
        <div class="col-md-4">
            <!-- Preview Card -->
            <div class="card mb-4 sticky-top">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-eye text-secondary me-2"></i>
                        Preview
                    </h5>
                </div>
                <div class="card-body">
                    <h6 class="fw-bold">Current Settings</h6>
                    <div id="settingsPreview">
                        <p class="mb-2"><strong>Language:</strong> <span id="preview_language">English</span></p>
                        <p class="mb-2"><strong>Timezone:</strong> <span id="preview_timezone">UTC</span></p>
                        <p class="mb-2"><strong>Date Format:</strong> <span id="preview_date">2025-08-29</span></p>
                        <p class="mb-2"><strong>Time Format:</strong> <span id="preview_time">14:30</span></p>
                        <p class="mb-2"><strong>Currency:</strong> <span id="preview_currency">USD ($)</span></p>
                        <p class="mb-0"><strong>Rows per Page:</strong> <span id="preview_rows">25</span></p>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-bolt text-warning me-2"></i>
                        Quick Actions
                    </h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <button type="button" class="btn btn-outline-primary btn-sm" onclick="resetToDefaults()">
                            <i class="fas fa-undo"></i> Reset to Defaults
                        </button>
                        <button type="button" class="btn btn-outline-info btn-sm" onclick="exportSettings()">
                            <i class="fas fa-download"></i> Export Settings
                        </button>
                        <button type="button" class="btn btn-outline-secondary btn-sm" onclick="importSettings()">
                            <i class="fas fa-upload"></i> Import Settings
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>

<!-- Import Settings Modal -->
<div class="modal fade" id="importModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Import Settings</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label for="settingsFile" class="form-label">Settings File (JSON)</label>
                    <input class="form-control" type="file" id="settingsFile" accept=".json">
                </div>
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i>
                    Only import settings files that you have exported from this system.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="processImport()">Import</button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Update preview when form values change
    const form = document.getElementById('preferencesForm');
    const previewElements = {
        language: document.getElementById('preview_language'),
        timezone: document.getElementById('preview_timezone'),
        date_format: document.getElementById('preview_date'),
        time_format: document.getElementById('preview_time'),
        currency_display: document.getElementById('preview_currency'),
        rows_per_page: document.getElementById('preview_rows')
    };
    
    // Language mapping
    const languageMap = {
        'en': 'English',
        'ar': 'العربية (Arabic)'
    };
    
    // Update preview function
    function updatePreview() {
        const language = form.language.value;
        previewElements.language.textContent = languageMap[language] || language;
        
        const timezone = form.timezone.value;
        previewElements.timezone.textContent = timezone;
        
        const dateFormat = form.date_format.value;
        const now = new Date();
        previewElements.date.textContent = formatDate(now, dateFormat);
        
        const timeFormat = form.time_format.value;
        previewElements.time.textContent = formatTime(now, timeFormat);
        
        const currency = form.currency_display.value;
        previewElements.currency.textContent = currency;
        
        const rows = form.rows_per_page.value;
        previewElements.rows.textContent = rows + ' rows';
    }
    
    // Date formatting function
    function formatDate(date, format) {
        const map = {
            'Y': date.getFullYear(),
            'm': String(date.getMonth() + 1).padStart(2, '0'),
            'd': String(date.getDate()).padStart(2, '0'),
            'M': date.toLocaleString('en', { month: 'short' })
        };
        
        return format.replace(/[YmdM]/g, (match) => map[match] || match);
    }
    
    // Time formatting function
    function formatTime(date, format) {
        if (format === 'H:i') {
            return String(date.getHours()).padStart(2, '0') + ':' + String(date.getMinutes()).padStart(2, '0');
        } else {
            return date.toLocaleTimeString('en-US', { hour: 'numeric', minute: '2-digit' });
        }
    }
    
    // Add event listeners
    ['language', 'timezone', 'date_format', 'time_format', 'currency_display', 'rows_per_page'].forEach(field => {
        form[field].addEventListener('change', updatePreview);
    });
    
    // Initial preview update
    updatePreview();
});

// Save preferences function
async function savePreferences() {
    const form = document.getElementById('preferencesForm');
    const formData = new FormData(form);
    
    try {
        const response = await fetch(form.action, {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-Token': document.querySelector('input[name="_token"]').value
            }
        });
        
        const result = await response.json();
        
        if (result.success) {
            showNotification('Preferences saved successfully!', 'success');
            
            // Refresh page if language changed
            if (formData.get('language') !== 'en') {
                setTimeout(() => {
                    window.location.reload();
                }, 1000);
            }
        } else {
            showNotification('Error saving preferences: ' + result.message, 'danger');
        }
    } catch (error) {
        showNotification('Error saving preferences: ' + error.message, 'danger');
    }
}

// Reset to defaults
function resetToDefaults() {
    if (confirm('Are you sure you want to reset all preferences to default values?')) {
        const form = document.getElementById('preferencesForm');
        form.reset();
        
        // Set specific defaults
        form.language.value = 'en';
        form.timezone.value = 'UTC';
        form.date_format.value = 'Y-m-d';
        form.time_format.value = 'H:i';
        form.currency_display.value = 'USD';
        form.rows_per_page.value = '25';
        form.dashboard_refresh.value = '60';
        form.session_timeout.value = '1800';
        
        // Update preview
        document.dispatchEvent(new Event('DOMContentLoaded'));
        
        showNotification('Preferences reset to default values', 'info');
    }
}

// Export settings
function exportSettings() {
    const form = document.getElementById('preferencesForm');
    const formData = new FormData(form);
    const settings = {};
    
    for (let [key, value] of formData.entries()) {
        settings[key] = value;
    }
    
    const blob = new Blob([JSON.stringify(settings, null, 2)], { type: 'application/json' });
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = 'misp_preferences_' + new Date().toISOString().split('T')[0] + '.json';
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
    URL.revokeObjectURL(url);
    
    showNotification('Settings exported successfully!', 'success');
}

// Import settings
function importSettings() {
    const modal = new bootstrap.Modal(document.getElementById('importModal'));
    modal.show();
}

// Process import
function processImport() {
    const fileInput = document.getElementById('settingsFile');
    const file = fileInput.files[0];
    
    if (!file) {
        showNotification('Please select a file to import', 'warning');
        return;
    }
    
    const reader = new FileReader();
    reader.onload = function(e) {
        try {
            const settings = JSON.parse(e.target.result);
            const form = document.getElementById('preferencesForm');
            
            // Apply settings to form
            Object.keys(settings).forEach(key => {
                if (form.elements[key]) {
                    if (form.elements[key].type === 'checkbox') {
                        form.elements[key].checked = settings[key] === '1';
                    } else {
                        form.elements[key].value = settings[key];
                    }
                }
            });
            
            // Update preview
            document.dispatchEvent(new Event('DOMContentLoaded'));
            
            // Close modal
            bootstrap.Modal.getInstance(document.getElementById('importModal')).hide();
            
            showNotification('Settings imported successfully!', 'success');
        } catch (error) {
            showNotification('Error importing settings: Invalid file format', 'danger');
        }
    };
    reader.readAsText(file);
}

// Notification function
function showNotification(message, type) {
    // Create toast notification
    const toastContainer = document.querySelector('.toast-container') || createToastContainer();
    
    const toast = document.createElement('div');
    toast.className = `toast align-items-center text-bg-${type} border-0`;
    toast.setAttribute('role', 'alert');
    toast.innerHTML = `
        <div class="d-flex">
            <div class="toast-body">${message}</div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>
    `;
    
    toastContainer.appendChild(toast);
    const bsToast = new bootstrap.Toast(toast);
    bsToast.show();
    
    // Remove toast after it hides
    toast.addEventListener('hidden.bs.toast', () => {
        toast.remove();
    });
}

// Create toast container if it doesn't exist
function createToastContainer() {
    const container = document.createElement('div');
    container.className = 'toast-container position-fixed bottom-0 end-0 p-3';
    document.body.appendChild(container);
    return container;
}
</script>

<?php endContent(); ?>