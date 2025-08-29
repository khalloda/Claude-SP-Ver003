<?php
/**
 * File: app/views/settings/index.php
 * Purpose: System configuration dashboard with comprehensive settings management
 * Layout: Uses app layout with professional settings organization
 */

$this->layout('layouts/app', [
    'title' => $page_title ?? t('settings.system_settings'),
    'active_nav' => 'settings'
]);

$currentUser = $this->getCurrentUser();
$canManageSettings = $this->hasRole(['admin', 'manager']);
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">
            <i class="fas fa-cogs me-2"></i><?= t('settings.system_settings') ?>
        </h1>
        <div class="btn-toolbar mb-2 mb-md-0">
            <div class="btn-group me-2">
                <button type="button" class="btn btn-outline-primary dropdown-toggle" data-bs-toggle="dropdown">
                    <i class="fas fa-download"></i> <?= t('settings.backup_restore') ?>
                </button>
                <ul class="dropdown-menu">
                    <li><a class="dropdown-item" href="#" onclick="createBackup()">
                        <i class="fas fa-save me-2"></i><?= t('settings.create_backup') ?>
                    </a></li>
                    <li><a class="dropdown-item" href="#" onclick="showRestoreModal()">
                        <i class="fas fa-upload me-2"></i><?= t('settings.restore_backup') ?>
                    </a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item" href="/settings/export">
                        <i class="fas fa-file-export me-2"></i><?= t('settings.export_settings') ?>
                    </a></li>
                </ul>
            </div>
            
            <button class="btn btn-success" onclick="saveAllSettings()">
                <i class="fas fa-save"></i> <?= t('settings.save_all_changes') ?>
            </button>
        </div>
    </div>

    <div class="row">
        <!-- Settings Navigation -->
        <div class="col-md-3">
            <div class="list-group">
                <a href="#general" class="list-group-item list-group-item-action active" data-bs-toggle="pill">
                    <i class="fas fa-building me-2"></i><?= t('settings.general') ?>
                </a>
                <a href="#localization" class="list-group-item list-group-item-action" data-bs-toggle="pill">
                    <i class="fas fa-globe me-2"></i><?= t('settings.localization') ?>
                </a>
                <a href="#email" class="list-group-item list-group-item-action" data-bs-toggle="pill">
                    <i class="fas fa-envelope me-2"></i><?= t('settings.email') ?>
                </a>
                <a href="#notifications" class="list-group-item list-group-item-action" data-bs-toggle="pill">
                    <i class="fas fa-bell me-2"></i><?= t('settings.notifications') ?>
                </a>
                <a href="#inventory" class="list-group-item list-group-item-action" data-bs-toggle="pill">
                    <i class="fas fa-boxes me-2"></i><?= t('settings.inventory') ?>
                </a>
                <a href="#accounting" class="list-group-item list-group-item-action" data-bs-toggle="pill">
                    <i class="fas fa-calculator me-2"></i><?= t('settings.accounting') ?>
                </a>
                <a href="#security" class="list-group-item list-group-item-action" data-bs-toggle="pill">
                    <i class="fas fa-shield-alt me-2"></i><?= t('settings.security') ?>
                </a>
                <a href="#system" class="list-group-item list-group-item-action" data-bs-toggle="pill">
                    <i class="fas fa-server me-2"></i><?= t('settings.system') ?>
                </a>
            </div>
        </div>

        <!-- Settings Content -->
        <div class="col-md-9">
            <div class="tab-content">
                <!-- General Settings -->
                <div class="tab-pane fade show active" id="general">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0"><?= t('settings.general_settings') ?></h5>
                        </div>
                        <div class="card-body">
                            <form id="generalForm">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label"><?= t('settings.company_name') ?> *</label>
                                            <input type="text" class="form-control" name="company_name" 
                                                   value="<?= htmlspecialchars($settings['company_name'] ?? '') ?>" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label"><?= t('settings.company_phone') ?></label>
                                            <input type="tel" class="form-control" name="company_phone" 
                                                   value="<?= htmlspecialchars($settings['company_phone'] ?? '') ?>">
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label"><?= t('settings.company_email') ?></label>
                                            <input type="email" class="form-control" name="company_email" 
                                                   value="<?= htmlspecialchars($settings['company_email'] ?? '') ?>">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label"><?= t('settings.company_website') ?></label>
                                            <input type="url" class="form-control" name="company_website" 
                                                   value="<?= htmlspecialchars($settings['company_website'] ?? '') ?>">
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label"><?= t('settings.company_address') ?></label>
                                    <textarea class="form-control" name="company_address" rows="3"><?= htmlspecialchars($settings['company_address'] ?? '') ?></textarea>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label"><?= t('settings.tax_number') ?></label>
                                            <input type="text" class="form-control" name="tax_number" 
                                                   value="<?= htmlspecialchars($settings['tax_number'] ?? '') ?>">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label"><?= t('settings.registration_number') ?></label>
                                            <input type="text" class="form-control" name="registration_number" 
                                                   value="<?= htmlspecialchars($settings['registration_number'] ?? '') ?>">
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label"><?= t('settings.company_logo') ?></label>
                                    <input type="file" class="form-control" name="company_logo" accept="image/*">
                                    <?php if (!empty($settings['company_logo'])): ?>
                                    <div class="mt-2">
                                        <img src="<?= htmlspecialchars($settings['company_logo']) ?>" alt="Logo" class="img-thumbnail" style="max-height: 100px;">
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Localization Settings -->
                <div class="tab-pane fade" id="localization">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0"><?= t('settings.localization_settings') ?></h5>
                        </div>
                        <div class="card-body">
                            <form id="localizationForm">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label"><?= t('settings.default_language') ?></label>
                                            <select class="form-select" name="default_language">
                                                <option value="en" <?= ($settings['default_language'] ?? 'en') === 'en' ? 'selected' : '' ?>>English</option>
                                                <option value="ar" <?= ($settings['default_language'] ?? '') === 'ar' ? 'selected' : '' ?>>العربية</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label"><?= t('settings.default_timezone') ?></label>
                                            <select class="form-select" name="default_timezone">
                                                <option value="UTC" <?= ($settings['default_timezone'] ?? 'UTC') === 'UTC' ? 'selected' : '' ?>>UTC</option>
                                                <option value="Asia/Dubai" <?= ($settings['default_timezone'] ?? '') === 'Asia/Dubai' ? 'selected' : '' ?>>Dubai (GMT+4)</option>
                                                <option value="Asia/Riyadh" <?= ($settings['default_timezone'] ?? '') === 'Asia/Riyadh' ? 'selected' : '' ?>>Riyadh (GMT+3)</option>
                                                <option value="America/New_York" <?= ($settings['default_timezone'] ?? '') === 'America/New_York' ? 'selected' : '' ?>>New York (EST)</option>
                                                <option value="Europe/London" <?= ($settings['default_timezone'] ?? '') === 'Europe/London' ? 'selected' : '' ?>>London (GMT)</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label"><?= t('settings.default_currency') ?></label>
                                            <select class="form-select" name="default_currency">
                                                <option value="USD" <?= ($settings['default_currency'] ?? 'USD') === 'USD' ? 'selected' : '' ?>>USD - US Dollar</option>
                                                <option value="EUR" <?= ($settings['default_currency'] ?? '') === 'EUR' ? 'selected' : '' ?>>EUR - Euro</option>
                                                <option value="AED" <?= ($settings['default_currency'] ?? '') === 'AED' ? 'selected' : '' ?>>AED - UAE Dirham</option>
                                                <option value="SAR" <?= ($settings['default_currency'] ?? '') === 'SAR' ? 'selected' : '' ?>>SAR - Saudi Riyal</option>
                                                <option value="GBP" <?= ($settings['default_currency'] ?? '') === 'GBP' ? 'selected' : '' ?>>GBP - British Pound</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label"><?= t('settings.date_format') ?></label>
                                            <select class="form-select" name="date_format">
                                                <option value="Y-m-d" <?= ($settings['date_format'] ?? 'Y-m-d') === 'Y-m-d' ? 'selected' : '' ?>>YYYY-MM-DD</option>
                                                <option value="m/d/Y" <?= ($settings['date_format'] ?? '') === 'm/d/Y' ? 'selected' : '' ?>>MM/DD/YYYY</option>
                                                <option value="d/m/Y" <?= ($settings['date_format'] ?? '') === 'd/m/Y' ? 'selected' : '' ?>>DD/MM/YYYY</option>
                                                <option value="d-m-Y" <?= ($settings['date_format'] ?? '') === 'd-m-Y' ? 'selected' : '' ?>>DD-MM-YYYY</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label"><?= t('settings.decimal_places') ?></label>
                                            <select class="form-select" name="decimal_places">
                                                <option value="0" <?= ($settings['decimal_places'] ?? '2') === '0' ? 'selected' : '' ?>>0</option>
                                                <option value="2" <?= ($settings['decimal_places'] ?? '2') === '2' ? 'selected' : '' ?>>2</option>
                                                <option value="3" <?= ($settings['decimal_places'] ?? '') === '3' ? 'selected' : '' ?>>3</option>
                                                <option value="4" <?= ($settings['decimal_places'] ?? '') === '4' ? 'selected' : '' ?>>4</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label"><?= t('settings.thousand_separator') ?></label>
                                            <select class="form-select" name="thousand_separator">
                                                <option value="," <?= ($settings['thousand_separator'] ?? ',') === ',' ? 'selected' : '' ?>>, (Comma)</option>
                                                <option value="." <?= ($settings['thousand_separator'] ?? '') === '.' ? 'selected' : '' ?>>. (Dot)</option>
                                                <option value=" " <?= ($settings['thousand_separator'] ?? '') === ' ' ? 'selected' : '' ?>>Space</option>
                                                <option value="" <?= ($settings['thousand_separator'] ?? '') === '' ? 'selected' : '' ?>><?= t('common.none') ?></option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Email Settings -->
                <div class="tab-pane fade" id="email">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="mb-0"><?= t('settings.email_settings') ?></h5>
                            <button class="btn btn-outline-primary btn-sm" onclick="testEmailSettings()">
                                <i class="fas fa-paper-plane"></i> <?= t('settings.test_email') ?>
                            </button>
                        </div>
                        <div class="card-body">
                            <form id="emailForm">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label"><?= t('settings.smtp_host') ?></label>
                                            <input type="text" class="form-control" name="smtp_host" 
                                                   value="<?= htmlspecialchars($settings['smtp_host'] ?? '') ?>">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label"><?= t('settings.smtp_port') ?></label>
                                            <input type="number" class="form-control" name="smtp_port" 
                                                   value="<?= htmlspecialchars($settings['smtp_port'] ?? '587') ?>">
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label"><?= t('settings.smtp_username') ?></label>
                                            <input type="text" class="form-control" name="smtp_username" 
                                                   value="<?= htmlspecialchars($settings['smtp_username'] ?? '') ?>">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label"><?= t('settings.smtp_password') ?></label>
                                            <input type="password" class="form-control" name="smtp_password" 
                                                   placeholder="<?= !empty($settings['smtp_password']) ? '••••••••' : '' ?>">
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label"><?= t('settings.smtp_encryption') ?></label>
                                            <select class="form-select" name="smtp_encryption">
                                                <option value="" <?= empty($settings['smtp_encryption']) ? 'selected' : '' ?>><?= t('common.none') ?></option>
                                                <option value="tls" <?= ($settings['smtp_encryption'] ?? '') === 'tls' ? 'selected' : '' ?>>TLS</option>
                                                <option value="ssl" <?= ($settings['smtp_encryption'] ?? '') === 'ssl' ? 'selected' : '' ?>>SSL</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label"><?= t('settings.from_email') ?></label>
                                            <input type="email" class="form-control" name="from_email" 
                                                   value="<?= htmlspecialchars($settings['from_email'] ?? '') ?>">
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label"><?= t('settings.from_name') ?></label>
                                    <input type="text" class="form-control" name="from_name" 
                                           value="<?= htmlspecialchars($settings['from_name'] ?? '') ?>">
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Notification Settings -->
                <div class="tab-pane fade" id="notifications">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0"><?= t('settings.notification_settings') ?></h5>
                        </div>
                        <div class="card-body">
                            <form id="notificationsForm">
                                <h6 class="border-bottom pb-2 mb-3"><?= t('settings.email_notifications') ?></h6>
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-check mb-3">
                                            <input class="form-check-input" type="checkbox" name="notify_low_stock" 
                                                   <?= !empty($settings['notify_low_stock']) ? 'checked' : '' ?>>
                                            <label class="form-check-label">
                                                <?= t('settings.notify_low_stock') ?>
                                            </label>
                                        </div>
                                        
                                        <div class="form-check mb-3">
                                            <input class="form-check-input" type="checkbox" name="notify_new_orders" 
                                                   <?= !empty($settings['notify_new_orders']) ? 'checked' : '' ?>>
                                            <label class="form-check-label">
                                                <?= t('settings.notify_new_orders') ?>
                                            </label>
                                        </div>
                                        
                                        <div class="form-check mb-3">
                                            <input class="form-check-input" type="checkbox" name="notify_payment_received" 
                                                   <?= !empty($settings['notify_payment_received']) ? 'checked' : '' ?>>
                                            <label class="form-check-label">
                                                <?= t('settings.notify_payment_received') ?>
                                            </label>
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <div class="form-check mb-3">
                                            <input class="form-check-input" type="checkbox" name="notify_overdue_invoices" 
                                                   <?= !empty($settings['notify_overdue_invoices']) ? 'checked' : '' ?>>
                                            <label class="form-check-label">
                                                <?= t('settings.notify_overdue_invoices') ?>
                                            </label>
                                        </div>
                                        
                                        <div class="form-check mb-3">
                                            <input class="form-check-input" type="checkbox" name="notify_quote_expiry" 
                                                   <?= !empty($settings['notify_quote_expiry']) ? 'checked' : '' ?>>
                                            <label class="form-check-label">
                                                <?= t('settings.notify_quote_expiry') ?>
                                            </label>
                                        </div>
                                        
                                        <div class="form-check mb-3">
                                            <input class="form-check-input" type="checkbox" name="notify_system_updates" 
                                                   <?= !empty($settings['notify_system_updates']) ? 'checked' : '' ?>>
                                            <label class="form-check-label">
                                                <?= t('settings.notify_system_updates') ?>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                
                                <hr>
                                
                                <div class="mb-3">
                                    <label class="form-label"><?= t('settings.admin_email') ?></label>
                                    <input type="email" class="form-control" name="admin_email" 
                                           value="<?= htmlspecialchars($settings['admin_email'] ?? '') ?>">
                                    <div class="form-text"><?= t('settings.admin_email_help') ?></div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Inventory Settings -->
                <div class="tab-pane fade" id="inventory">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0"><?= t('settings.inventory_settings') ?></h5>
                        </div>
                        <div class="card-body">
                            <form id="inventoryForm">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label"><?= t('settings.default_stock_alert') ?></label>
                                            <input type="number" class="form-control" name="default_stock_alert" 
                                                   value="<?= htmlspecialchars($settings['default_stock_alert'] ?? '10') ?>" min="0">
                                            <div class="form-text"><?= t('settings.stock_alert_help') ?></div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label"><?= t('settings.auto_generate_sku') ?></label>
                                            <select class="form-select" name="auto_generate_sku">
                                                <option value="1" <?= ($settings['auto_generate_sku'] ?? '1') === '1' ? 'selected' : '' ?>><?= t('common.yes') ?></option>
                                                <option value="0" <?= ($settings['auto_generate_sku'] ?? '') === '0' ? 'selected' : '' ?>><?= t('common.no') ?></option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label"><?= t('settings.sku_prefix') ?></label>
                                            <input type="text" class="form-control" name="sku_prefix" 
                                                   value="<?= htmlspecialchars($settings['sku_prefix'] ?? 'SKU') ?>">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label"><?= t('settings.barcode_format') ?></label>
                                            <select class="form-select" name="barcode_format">
                                                <option value="CODE128" <?= ($settings['barcode_format'] ?? 'CODE128') === 'CODE128' ? 'selected' : '' ?>>CODE128</option>
                                                <option value="EAN13" <?= ($settings['barcode_format'] ?? '') === 'EAN13' ? 'selected' : '' ?>>EAN-13</option>
                                                <option value="UPC" <?= ($settings['barcode_format'] ?? '') === 'UPC' ? 'selected' : '' ?>>UPC</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="form-check mb-3">
                                    <input class="form-check-input" type="checkbox" name="track_serial_numbers" 
                                           <?= !empty($settings['track_serial_numbers']) ? 'checked' : '' ?>>
                                    <label class="form-check-label">
                                        <?= t('settings.track_serial_numbers') ?>
                                    </label>
                                </div>
                                
                                <div class="form-check mb-3">
                                    <input class="form-check-input" type="checkbox" name="allow_negative_stock" 
                                           <?= !empty($settings['allow_negative_stock']) ? 'checked' : '' ?>>
                                    <label class="form-check-label">
                                        <?= t('settings.allow_negative_stock') ?>
                                    </label>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Accounting Settings -->
                <div class="tab-pane fade" id="accounting">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0"><?= t('settings.accounting_settings') ?></h5>
                        </div>
                        <div class="card-body">
                            <form id="accountingForm">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label"><?= t('settings.default_tax_rate') ?></label>
                                            <div class="input-group">
                                                <input type="number" class="form-control" name="default_tax_rate" 
                                                       value="<?= htmlspecialchars($settings['default_tax_rate'] ?? '0') ?>" 
                                                       min="0" max="100" step="0.01">
                                                <span class="input-group-text">%</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label"><?= t('settings.invoice_terms') ?></label>
                                            <select class="form-select" name="invoice_terms">
                                                <option value="Net 15" <?= ($settings['invoice_terms'] ?? 'Net 30') === 'Net 15' ? 'selected' : '' ?>>Net 15</option>
                                                <option value="Net 30" <?= ($settings['invoice_terms'] ?? 'Net 30') === 'Net 30' ? 'selected' : '' ?>>Net 30</option>
                                                <option value="Net 45" <?= ($settings['invoice_terms'] ?? '') === 'Net 45' ? 'selected' : '' ?>>Net 45</option>
                                                <option value="Net 60" <?= ($settings['invoice_terms'] ?? '') === 'Net 60' ? 'selected' : '' ?>>Net 60</option>
                                                <option value="Due on Receipt" <?= ($settings['invoice_terms'] ?? '') === 'Due on Receipt' ? 'selected' : '' ?>>Due on Receipt</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label"><?= t('settings.quote_validity_days') ?></label>
                                            <input type="number" class="form-control" name="quote_validity_days" 
                                                   value="<?= htmlspecialchars($settings['quote_validity_days'] ?? '30') ?>" min="1">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label"><?= t('settings.invoice_prefix') ?></label>
                                            <input type="text" class="form-control" name="invoice_prefix" 
                                                   value="<?= htmlspecialchars($settings['invoice_prefix'] ?? 'INV') ?>">
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label"><?= t('settings.quote_prefix') ?></label>
                                            <input type="text" class="form-control" name="quote_prefix" 
                                                   value="<?= htmlspecialchars($settings['quote_prefix'] ?? 'QT') ?>">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label"><?= t('settings.order_prefix') ?></label>
                                            <input type="text" class="form-control" name="order_prefix" 
                                                   value="<?= htmlspecialchars($settings['order_prefix'] ?? 'SO') ?>">
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label"><?= t('settings.invoice_footer') ?></label>
                                    <textarea class="form-control" name="invoice_footer" rows="3"><?= htmlspecialchars($settings['invoice_footer'] ?? '') ?></textarea>
                                    <div class="form-text"><?= t('settings.invoice_footer_help') ?></div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Security Settings -->
                <div class="tab-pane fade" id="security">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0"><?= t('settings.security_settings') ?></h5>
                        </div>
                        <div class="card-body">
                            <form id="securityForm">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label"><?= t('settings.session_timeout') ?></label>
                                            <select class="form-select" name="session_timeout">
                                                <option value="1800" <?= ($settings['session_timeout'] ?? '3600') === '1800' ? 'selected' : '' ?>>30 <?= t('common.minutes') ?></option>
                                                <option value="3600" <?= ($settings['session_timeout'] ?? '3600') === '3600' ? 'selected' : '' ?>>1 <?= t('common.hour') ?></option>
                                                <option value="7200" <?= ($settings['session_timeout'] ?? '') === '7200' ? 'selected' : '' ?>>2 <?= t('common.hours') ?></option>
                                                <option value="14400" <?= ($settings['session_timeout'] ?? '') === '14400' ? 'selected' : '' ?>>4 <?= t('common.hours') ?></option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label"><?= t('settings.password_min_length') ?></label>
                                            <input type="number" class="form-control" name="password_min_length" 
                                                   value="<?= htmlspecialchars($settings['password_min_length'] ?? '8') ?>" 
                                                   min="6" max="32">
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="form-check mb-3">
                                    <input class="form-check-input" type="checkbox" name="force_password_change" 
                                           <?= !empty($settings['force_password_change']) ? 'checked' : '' ?>>
                                    <label class="form-check-label">
                                        <?= t('settings.force_password_change') ?>
                                    </label>
                                    <div class="form-text"><?= t('settings.force_password_change_help') ?></div>
                                </div>
                                
                                <div class="form-check mb-3">
                                    <input class="form-check-input" type="checkbox" name="enable_2fa" 
                                           <?= !empty($settings['enable_2fa']) ? 'checked' : '' ?>>
                                    <label class="form-check-label">
                                        <?= t('settings.enable_2fa') ?>
                                    </label>
                                </div>
                                
                                <div class="form-check mb-3">
                                    <input class="form-check-input" type="checkbox" name="log_user_activities" 
                                           <?= !empty($settings['log_user_activities']) ? 'checked' : '' ?>>
                                    <label class="form-check-label">
                                        <?= t('settings.log_user_activities') ?>
                                    </label>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- System Settings -->
                <div class="tab-pane fade" id="system">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0"><?= t('settings.system_settings') ?></h5>
                        </div>
                        <div class="card-body">
                            <form id="systemForm">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label"><?= t('settings.system_name') ?></label>
                                            <input type="text" class="form-control" name="system_name" 
                                                   value="<?= htmlspecialchars($settings['system_name'] ?? 'MISP') ?>">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label"><?= t('settings.items_per_page') ?></label>
                                            <select class="form-select" name="items_per_page">
                                                <option value="10" <?= ($settings['items_per_page'] ?? '25') === '10' ? 'selected' : '' ?>>10</option>
                                                <option value="25" <?= ($settings['items_per_page'] ?? '25') === '25' ? 'selected' : '' ?>>25</option>
                                                <option value="50" <?= ($settings['items_per_page'] ?? '') === '50' ? 'selected' : '' ?>>50</option>
                                                <option value="100" <?= ($settings['items_per_page'] ?? '') === '100' ? 'selected' : '' ?>>100</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="form-check mb-3">
                                    <input class="form-check-input" type="checkbox" name="maintenance_mode" 
                                           <?= !empty($settings['maintenance_mode']) ? 'checked' : '' ?>>
                                    <label class="form-check-label">
                                        <?= t('settings.maintenance_mode') ?>
                                    </label>
                                    <div class="form-text"><?= t('settings.maintenance_mode_help') ?></div>
                                </div>
                                
                                <div class="form-check mb-3">
                                    <input class="form-check-input" type="checkbox" name="enable_debug_mode" 
                                           <?= !empty($settings['enable_debug_mode']) ? 'checked' : '' ?>>
                                    <label class="form-check-label">
                                        <?= t('settings.enable_debug_mode') ?>
                                    </label>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label"><?= t('settings.backup_frequency') ?></label>
                                    <select class="form-select" name="backup_frequency">
                                        <option value="daily" <?= ($settings['backup_frequency'] ?? 'weekly') === 'daily' ? 'selected' : '' ?>><?= t('settings.daily') ?></option>
                                        <option value="weekly" <?= ($settings['backup_frequency'] ?? 'weekly') === 'weekly' ? 'selected' : '' ?>><?= t('settings.weekly') ?></option>
                                        <option value="monthly" <?= ($settings['backup_frequency'] ?? '') === 'monthly' ? 'selected' : '' ?>><?= t('settings.monthly') ?></option>
                                        <option value="never" <?= ($settings['backup_frequency'] ?? '') === 'never' ? 'selected' : '' ?>><?= t('settings.never') ?></option>
                                    </select>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function saveAllSettings() {
    const forms = ['generalForm', 'localizationForm', 'emailForm', 'notificationsForm', 'inventoryForm', 'accountingForm', 'securityForm', 'systemForm'];
    const formData = new FormData();
    
    forms.forEach(formId => {
        const form = document.getElementById(formId);
        if (form) {
            const data = new FormData(form);
            for (let [key, value] of data.entries()) {
                formData.append(key, value);
            }
        }
    });
    
    fetch('/settings/save', {
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
        } else {
            showAlert('error', data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('error', '<?= t('messages.error.general') ?>');
    });
}

function testEmailSettings() {
    const emailForm = document.getElementById('emailForm');
    const formData = new FormData(emailForm);
    
    fetch('/settings/test-email', {
        method: 'POST',
        headers: {
            'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert('success', '<?= t('settings.email_test_success') ?>');
        } else {
            showAlert('error', data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('error', '<?= t('messages.error.general') ?>');
    });
}

function createBackup() {
    if (confirm('<?= t('settings.confirm_backup') ?>')) {
        fetch('/settings/backup', {
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
                if (data.download_url) {
                    window.open(data.download_url, '_blank');
                }
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
</script>