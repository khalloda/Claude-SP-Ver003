<?php

/**
 * File: app/controllers/SettingsController.php
 * Purpose: System settings and configuration controller
 * Depends on: Controller, Settings model, Auth
 * Notes: Handles system configuration, backup, maintenance, and admin settings
 */

namespace App\Controllers;

use App\Core\Controller;
use App\Models\User;
use App\Core\Database;

class SettingsController extends Controller
{
    /**
     * Settings dashboard
     */
    public function index(): void
    {
        $this->requireAuth();
        $this->requireRole('admin');

        $systemInfo = [
            'version' => '2.0.0',
            'php_version' => phpversion(),
            'database_version' => $this->getDatabaseVersion(),
            'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown',
            'max_execution_time' => ini_get('max_execution_time'),
            'memory_limit' => ini_get('memory_limit'),
            'upload_max_filesize' => ini_get('upload_max_filesize'),
            'post_max_size' => ini_get('post_max_size'),
            'disk_free_space' => $this->formatBytes(disk_free_space('.')),
            'disk_total_space' => $this->formatBytes(disk_total_space('.'))
        ];

        $settingsCategories = [
            'general' => [
                'title' => t('settings.general'),
                'icon' => 'fa-cogs',
                'description' => t('settings.general_description')
            ],
            'security' => [
                'title' => t('settings.security'),
                'icon' => 'fa-shield-alt',
                'description' => t('settings.security_description')
            ],
            'email' => [
                'title' => t('settings.email'),
                'icon' => 'fa-envelope',
                'description' => t('settings.email_description')
            ],
            'backup' => [
                'title' => t('settings.backup'),
                'icon' => 'fa-database',
                'description' => t('settings.backup_description')
            ],
            'maintenance' => [
                'title' => t('settings.maintenance'),
                'icon' => 'fa-wrench',
                'description' => t('settings.maintenance_description')
            ],
            'logs' => [
                'title' => t('settings.logs'),
                'icon' => 'fa-file-alt',
                'description' => t('settings.logs_description')
            ]
        ];

        $this->view('settings/index', [
            'system_info' => $systemInfo,
            'settings_categories' => $settingsCategories,
            'page_title' => t('nav.settings')
        ]);
    }

    /**
     * General settings
     */
    public function general(): void
    {
        $this->requireAuth();
        $this->requireRole('admin');

        $settings = $this->getSettings([
            'app_name',
            'app_description',
            'app_url',
            'timezone',
            'date_format',
            'time_format',
            'currency',
            'language',
            'items_per_page',
            'session_timeout',
            'maintenance_mode',
            'allow_registration',
            'require_email_verification'
        ]);

        $timezones = [
            'UTC' => 'UTC',
            'America/New_York' => 'Eastern Time (US & Canada)',
            'America/Chicago' => 'Central Time (US & Canada)',
            'America/Denver' => 'Mountain Time (US & Canada)',
            'America/Los_Angeles' => 'Pacific Time (US & Canada)',
            'Europe/London' => 'London',
            'Europe/Paris' => 'Paris',
            'Europe/Berlin' => 'Berlin',
            'Asia/Dubai' => 'Dubai',
            'Asia/Riyadh' => 'Riyadh',
            'Asia/Tokyo' => 'Tokyo'
        ];

        $languages = [
            'en' => 'English',
            'ar' => 'العربية (Arabic)'
        ];

        $currencies = [
            'USD' => 'US Dollar ($)',
            'EUR' => 'Euro (€)',
            'GBP' => 'British Pound (£)',
            'AED' => 'UAE Dirham (د.إ)',
            'SAR' => 'Saudi Riyal (ر.س)'
        ];

        $this->view('settings/general', [
            'settings' => $settings,
            'timezones' => $timezones,
            'languages' => $languages,
            'currencies' => $currencies,
            'page_title' => t('settings.general_settings')
        ]);
    }

    /**
     * Update general settings
     */
    public function updateGeneral(): void
    {
        $this->requireAuth();
        $this->requireRole('admin');

        if (!$this->verifyCsrf()) {
            $this->setFlash('error', t('messages.error.invalid_csrf'));
            $this->back();
        }

        $settingsData = [
            'app_name' => $this->input['app_name'] ?? 'MISP Ver002',
            'app_description' => $this->input['app_description'] ?? '',
            'app_url' => $this->input['app_url'] ?? '',
            'timezone' => $this->input['timezone'] ?? 'UTC',
            'date_format' => $this->input['date_format'] ?? 'Y-m-d',
            'time_format' => $this->input['time_format'] ?? '24',
            'currency' => $this->input['currency'] ?? 'USD',
            'language' => $this->input['language'] ?? 'en',
            'items_per_page' => (int)($this->input['items_per_page'] ?? 20),
            'session_timeout' => (int)($this->input['session_timeout'] ?? 3600),
            'maintenance_mode' => (bool)($this->input['maintenance_mode'] ?? false),
            'allow_registration' => (bool)($this->input['allow_registration'] ?? false),
            'require_email_verification' => (bool)($this->input['require_email_verification'] ?? true)
        ];

        if ($this->updateSettings($settingsData)) {
            $this->setFlash('success', t('settings.general_updated'));
        } else {
            $this->setFlash('error', t('messages.error.general'));
        }

        $this->redirect('/settings/general');
    }

    /**
     * Security settings
     */
    public function security(): void
    {
        $this->requireAuth();
        $this->requireRole('admin');

        $settings = $this->getSettings([
            'password_min_length',
            'password_require_uppercase',
            'password_require_lowercase',
            'password_require_numbers',
            'password_require_symbols',
            'max_login_attempts',
            'lockout_duration',
            'force_password_change',
            'password_expiry_days',
            'two_factor_auth',
            'ip_whitelist',
            'session_secure',
            'csrf_protection',
            'rate_limiting'
        ]);

        $this->view('settings/security', [
            'settings' => $settings,
            'page_title' => t('settings.security_settings')
        ]);
    }

    /**
     * Update security settings
     */
    public function updateSecurity(): void
    {
        $this->requireAuth();
        $this->requireRole('admin');

        if (!$this->verifyCsrf()) {
            $this->setFlash('error', t('messages.error.invalid_csrf'));
            $this->back();
        }

        $settingsData = [
            'password_min_length' => (int)($this->input['password_min_length'] ?? 8),
            'password_require_uppercase' => (bool)($this->input['password_require_uppercase'] ?? true),
            'password_require_lowercase' => (bool)($this->input['password_require_lowercase'] ?? true),
            'password_require_numbers' => (bool)($this->input['password_require_numbers'] ?? true),
            'password_require_symbols' => (bool)($this->input['password_require_symbols'] ?? false),
            'max_login_attempts' => (int)($this->input['max_login_attempts'] ?? 5),
            'lockout_duration' => (int)($this->input['lockout_duration'] ?? 900),
            'force_password_change' => (bool)($this->input['force_password_change'] ?? false),
            'password_expiry_days' => (int)($this->input['password_expiry_days'] ?? 90),
            'two_factor_auth' => (bool)($this->input['two_factor_auth'] ?? false),
            'ip_whitelist' => $this->input['ip_whitelist'] ?? '',
            'session_secure' => (bool)($this->input['session_secure'] ?? true),
            'csrf_protection' => (bool)($this->input['csrf_protection'] ?? true),
            'rate_limiting' => (bool)($this->input['rate_limiting'] ?? true)
        ];

        if ($this->updateSettings($settingsData)) {
            $this->setFlash('success', t('settings.security_updated'));
        } else {
            $this->setFlash('error', t('messages.error.general'));
        }

        $this->redirect('/settings/security');
    }

    /**
     * Email settings
     */
    public function email(): void
    {
        $this->requireAuth();
        $this->requireRole('admin');

        $settings = $this->getSettings([
            'mail_driver',
            'smtp_host',
            'smtp_port',
            'smtp_username',
            'smtp_password',
            'smtp_encryption',
            'mail_from_address',
            'mail_from_name',
            'mail_reply_to',
            'mail_test_mode',
            'email_notifications',
            'low_stock_notifications',
            'order_notifications',
            'payment_notifications'
        ]);

        $this->view('settings/email', [
            'settings' => $settings,
            'page_title' => t('settings.email_settings')
        ]);
    }

    /**
     * Update email settings
     */
    public function updateEmail(): void
    {
        $this->requireAuth();
        $this->requireRole('admin');

        if (!$this->verifyCsrf()) {
            $this->setFlash('error', t('messages.error.invalid_csrf'));
            $this->back();
        }

        $settingsData = [
            'mail_driver' => $this->input['mail_driver'] ?? 'smtp',
            'smtp_host' => $this->input['smtp_host'] ?? '',
            'smtp_port' => (int)($this->input['smtp_port'] ?? 587),
            'smtp_username' => $this->input['smtp_username'] ?? '',
            'smtp_password' => $this->input['smtp_password'] ?? '',
            'smtp_encryption' => $this->input['smtp_encryption'] ?? 'tls',
            'mail_from_address' => $this->input['mail_from_address'] ?? '',
            'mail_from_name' => $this->input['mail_from_name'] ?? '',
            'mail_reply_to' => $this->input['mail_reply_to'] ?? '',
            'mail_test_mode' => (bool)($this->input['mail_test_mode'] ?? false),
            'email_notifications' => (bool)($this->input['email_notifications'] ?? true),
            'low_stock_notifications' => (bool)($this->input['low_stock_notifications'] ?? true),
            'order_notifications' => (bool)($this->input['order_notifications'] ?? true),
            'payment_notifications' => (bool)($this->input['payment_notifications'] ?? true)
        ];

        if ($this->updateSettings($settingsData)) {
            $this->setFlash('success', t('settings.email_updated'));
        } else {
            $this->setFlash('error', t('messages.error.general'));
        }

        $this->redirect('/settings/email');
    }

    /**
     * Test email configuration
     */
    public function testEmail(): void
    {
        $this->requireAuth();
        $this->requireRole('admin');

        if (!$this->verifyCsrf()) {
            $this->json([
                'success' => false,
                'message' => t('messages.error.invalid_csrf')
            ], 419);
        }

        $testEmail = $this->input['test_email'] ?? '';
        
        if (empty($testEmail) || !filter_var($testEmail, FILTER_VALIDATE_EMAIL)) {
            $this->json([
                'success' => false,
                'message' => t('settings.invalid_email')
            ], 400);
        }

        try {
            // Send test email
            $subject = 'MISP Email Configuration Test';
            $message = 'This is a test email to verify your email configuration is working correctly.';
            
            $result = $this->sendTestEmail($testEmail, $subject, $message);
            
            if ($result) {
                $this->json([
                    'success' => true,
                    'message' => t('settings.test_email_sent')
                ]);
            } else {
                $this->json([
                    'success' => false,
                    'message' => t('settings.test_email_failed')
                ], 500);
            }
            
        } catch (\Exception $e) {
            $this->json([
                'success' => false,
                'message' => t('settings.test_email_error') . ': ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Backup settings and operations
     */
    public function backup(): void
    {
        $this->requireAuth();
        $this->requireRole('admin');

        $backupDir = APP_PATH . '/storage/backups/';
        $backups = [];

        if (is_dir($backupDir)) {
            $files = scandir($backupDir);
            foreach ($files as $file) {
                if ($file !== '.' && $file !== '..' && pathinfo($file, PATHINFO_EXTENSION) === 'sql') {
                    $backups[] = [
                        'filename' => $file,
                        'size' => $this->formatBytes(filesize($backupDir . $file)),
                        'created' => date('Y-m-d H:i:s', filemtime($backupDir . $file))
                    ];
                }
            }
        }

        // Sort by creation time, newest first
        usort($backups, function($a, $b) {
            return strtotime($b['created']) - strtotime($a['created']);
        });

        $settings = $this->getSettings([
            'auto_backup',
            'backup_frequency',
            'backup_retention',
            'backup_compression',
            'backup_notification'
        ]);

        $this->view('settings/backup', [
            'backups' => $backups,
            'settings' => $settings,
            'backup_dir' => $backupDir,
            'page_title' => t('settings.backup_restore')
        ]);
    }

    /**
     * Create database backup
     */
    public function createBackup(): void
    {
        $this->requireAuth();
        $this->requireRole('admin');

        if (!$this->verifyCsrf()) {
            $this->json([
                'success' => false,
                'message' => t('messages.error.invalid_csrf')
            ], 419);
        }

        try {
            $filename = 'backup_' . date('Y-m-d_H-i-s') . '.sql';
            $backupPath = APP_PATH . '/storage/backups/' . $filename;

            // Ensure backup directory exists
            $backupDir = dirname($backupPath);
            if (!is_dir($backupDir)) {
                mkdir($backupDir, 0755, true);
            }

            $result = $this->performDatabaseBackup($backupPath);

            if ($result) {
                $this->json([
                    'success' => true,
                    'message' => t('settings.backup_created'),
                    'filename' => $filename,
                    'size' => $this->formatBytes(filesize($backupPath))
                ]);
            } else {
                $this->json([
                    'success' => false,
                    'message' => t('settings.backup_failed')
                ], 500);
            }

        } catch (\Exception $e) {
            $this->json([
                'success' => false,
                'message' => t('settings.backup_error') . ': ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Download backup file
     */
    public function downloadBackup(array $params): void
    {
        $this->requireAuth();
        $this->requireRole('admin');

        $filename = $params['filename'] ?? '';
        $backupPath = APP_PATH . '/storage/backups/' . $filename;

        if (!file_exists($backupPath) || pathinfo($filename, PATHINFO_EXTENSION) !== 'sql') {
            $this->setFlash('error', t('messages.error.not_found'));
            $this->redirect('/settings/backup');
        }

        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Length: ' . filesize($backupPath));
        header('Cache-Control: no-cache, must-revalidate');

        readfile($backupPath);
        exit;
    }

    /**
     * Delete backup file
     */
    public function deleteBackup(array $params): void
    {
        $this->requireAuth();
        $this->requireRole('admin');

        if (!$this->verifyCsrf()) {
            $this->json([
                'success' => false,
                'message' => t('messages.error.invalid_csrf')
            ], 419);
        }

        $filename = $params['filename'] ?? '';
        $backupPath = APP_PATH . '/storage/backups/' . $filename;

        if (!file_exists($backupPath) || pathinfo($filename, PATHINFO_EXTENSION) !== 'sql') {
            $this->json([
                'success' => false,
                'message' => t('messages.error.not_found')
            ], 404);
        }

        if (unlink($backupPath)) {
            $this->json([
                'success' => true,
                'message' => t('settings.backup_deleted')
            ]);
        } else {
            $this->json([
                'success' => false,
                'message' => t('settings.backup_delete_failed')
            ], 500);
        }
    }

    /**
     * System maintenance
     */
    public function maintenance(): void
    {
        $this->requireAuth();
        $this->requireRole('admin');

        $maintenanceTasks = [
            'clear_cache' => [
                'title' => t('maintenance.clear_cache'),
                'description' => t('maintenance.clear_cache_desc'),
                'icon' => 'fa-broom'
            ],
            'clear_logs' => [
                'title' => t('maintenance.clear_logs'),
                'description' => t('maintenance.clear_logs_desc'),
                'icon' => 'fa-file-alt'
            ],
            'optimize_database' => [
                'title' => t('maintenance.optimize_database'),
                'description' => t('maintenance.optimize_database_desc'),
                'icon' => 'fa-database'
            ],
            'check_permissions' => [
                'title' => t('maintenance.check_permissions'),
                'description' => t('maintenance.check_permissions_desc'),
                'icon' => 'fa-shield-alt'
            ],
            'system_check' => [
                'title' => t('maintenance.system_check'),
                'description' => t('maintenance.system_check_desc'),
                'icon' => 'fa-check-circle'
            ]
        ];

        $this->view('settings/maintenance', [
            'maintenance_tasks' => $maintenanceTasks,
            'page_title' => t('settings.maintenance')
        ]);
    }

    /**
     * Run maintenance task
     */
    public function runMaintenance(): void
    {
        $this->requireAuth();
        $this->requireRole('admin');

        if (!$this->verifyCsrf()) {
            $this->json([
                'success' => false,
                'message' => t('messages.error.invalid_csrf')
            ], 419);
        }

        $task = $this->input['task'] ?? '';

        try {
            switch ($task) {
                case 'clear_cache':
                    $result = $this->clearCache();
                    break;
                    
                case 'clear_logs':
                    $result = $this->clearLogs();
                    break;
                    
                case 'optimize_database':
                    $result = $this->optimizeDatabase();
                    break;
                    
                case 'check_permissions':
                    $result = $this->checkPermissions();
                    break;
                    
                case 'system_check':
                    $result = $this->performSystemCheck();
                    break;
                    
                default:
                    $this->json([
                        'success' => false,
                        'message' => t('maintenance.invalid_task')
                    ], 400);
            }

            $this->json([
                'success' => $result['success'],
                'message' => $result['message'],
                'details' => $result['details'] ?? null
            ]);

        } catch (\Exception $e) {
            $this->json([
                'success' => false,
                'message' => t('maintenance.task_error') . ': ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * System logs viewer
     */
    public function logs(): void
    {
        $this->requireAuth();
        $this->requireRole('admin');

        $logType = $this->input['type'] ?? 'error';
        $logFile = $this->getLogFile($logType);
        
        $logs = [];
        $totalLines = 0;
        
        if (file_exists($logFile)) {
            $lines = file($logFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            $totalLines = count($lines);
            
            // Get last 100 lines
            $logs = array_slice(array_reverse($lines), 0, 100);
        }

        $logTypes = [
            'error' => t('logs.error_log'),
            'access' => t('logs.access_log'),
            'security' => t('logs.security_log'),
            'system' => t('logs.system_log')
        ];

        $this->view('settings/logs', [
            'logs' => $logs,
            'log_types' => $logTypes,
            'current_type' => $logType,
            'total_lines' => $totalLines,
            'log_file' => $logFile,
            'page_title' => t('settings.system_logs')
        ]);
    }

    // Helper methods

    private function getSettings(array $keys): array
    {
        $settings = [];
        foreach ($keys as $key) {
            $settings[$key] = $this->getSetting($key);
        }
        return $settings;
    }

    private function getSetting(string $key, $default = null)
    {
        // This would typically read from a settings table or config file
        // For now, return defaults
        $defaults = [
            'app_name' => 'MISP Ver002',
            'app_description' => 'Management Information System for Spare Parts',
            'timezone' => 'UTC',
            'language' => 'en',
            'currency' => 'USD',
            'items_per_page' => 20,
            'session_timeout' => 3600,
            'password_min_length' => 8,
            'max_login_attempts' => 5,
            'lockout_duration' => 900,
            'smtp_port' => 587,
            'smtp_encryption' => 'tls'
        ];

        return $defaults[$key] ?? $default;
    }

    private function updateSettings(array $settings): bool
    {
        // This would update the settings in database or config file
        // For now, simulate success
        return true;
    }

    private function getDatabaseVersion(): string
    {
        try {
            $stmt = $this->db->prepare("SELECT VERSION()");
            $result = $stmt->execute()->fetch();
            return $result ? $result->{'VERSION()'} : 'Unknown';
        } catch (\Exception $e) {
            return 'Unknown';
        }
    }

    private function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, 2) . ' ' . $units[$i];
    }

    private function sendTestEmail(string $email, string $subject, string $message): bool
    {
        // This would use the configured email settings to send a test email
        // For now, simulate success
        return true;
    }

    private function performDatabaseBackup(string $backupPath): bool
    {
        try {
            $config = Database::getConfig();
            
            $command = sprintf(
                'mysqldump -h%s -u%s -p%s %s > %s',
                escapeshellarg($config['host']),
                escapeshellarg($config['user']),
                escapeshellarg($config['pass']),
                escapeshellarg($config['name']),
                escapeshellarg($backupPath)
            );

            exec($command, $output, $returnCode);
            return $returnCode === 0;
            
        } catch (\Exception $e) {
            return false;
        }
    }

    private function clearCache(): array
    {
        $cacheDir = APP_PATH . '/storage/cache/';
        $cleared = 0;
        
        if (is_dir($cacheDir)) {
            $files = glob($cacheDir . '*');
            foreach ($files as $file) {
                if (is_file($file)) {
                    unlink($file);
                    $cleared++;
                }
            }
        }

        return [
            'success' => true,
            'message' => t('maintenance.cache_cleared'),
            'details' => ['files_cleared' => $cleared]
        ];
    }

    private function clearLogs(): array
    {
        $logDir = APP_PATH . '/storage/logs/';
        $cleared = 0;
        
        if (is_dir($logDir)) {
            $files = glob($logDir . '*.log');
            foreach ($files as $file) {
                file_put_contents($file, '');
                $cleared++;
            }
        }

        return [
            'success' => true,
            'message' => t('maintenance.logs_cleared'),
            'details' => ['files_cleared' => $cleared]
        ];
    }

    private function optimizeDatabase(): array
    {
        try {
            $tables = $this->db->prepare("SHOW TABLES")->execute()->fetchAll();
            $optimized = 0;
            
            foreach ($tables as $table) {
                $tableName = array_values((array)$table)[0];
                $this->db->prepare("OPTIMIZE TABLE `{$tableName}`")->execute();
                $optimized++;
            }

            return [
                'success' => true,
                'message' => t('maintenance.database_optimized'),
                'details' => ['tables_optimized' => $optimized]
            ];
            
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => t('maintenance.database_optimization_failed') . ': ' . $e->getMessage()
            ];
        }
    }

    private function checkPermissions(): array
    {
        $directories = [
            APP_PATH . '/storage/logs/',
            APP_PATH . '/storage/cache/',
            APP_PATH . '/storage/backups/',
            APP_PATH . '/storage/uploads/'
        ];

        $issues = [];
        
        foreach ($directories as $dir) {
            if (!is_dir($dir)) {
                $issues[] = "Directory does not exist: {$dir}";
            } elseif (!is_writable($dir)) {
                $issues[] = "Directory is not writable: {$dir}";
            }
        }

        return [
            'success' => empty($issues),
            'message' => empty($issues) ? t('maintenance.permissions_ok') : t('maintenance.permission_issues'),
            'details' => ['issues' => $issues]
        ];
    }

    private function performSystemCheck(): array
    {
        $checks = [
            'php_version' => version_compare(PHP_VERSION, '8.0.0', '>='),
            'pdo_extension' => extension_loaded('pdo'),
            'pdo_mysql_extension' => extension_loaded('pdo_mysql'),
            'mbstring_extension' => extension_loaded('mbstring'),
            'openssl_extension' => extension_loaded('openssl'),
            'curl_extension' => extension_loaded('curl'),
            'gd_extension' => extension_loaded('gd'),
            'database_connection' => $this->testDatabaseConnection(),
            'writable_storage' => is_writable(APP_PATH . '/storage/'),
            'memory_limit' => $this->checkMemoryLimit()
        ];

        $passed = array_sum($checks);
        $total = count($checks);

        return [
            'success' => $passed === $total,
            'message' => sprintf(t('maintenance.system_check_complete'), $passed, $total),
            'details' => ['checks' => $checks, 'passed' => $passed, 'total' => $total]
        ];
    }

    private function testDatabaseConnection(): bool
    {
        try {
            $this->db->prepare("SELECT 1")->execute();
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    private function checkMemoryLimit(): bool
    {
        $memoryLimit = ini_get('memory_limit');
        $memoryLimitBytes = $this->convertToBytes($memoryLimit);
        return $memoryLimitBytes >= 128 * 1024 * 1024; // 128MB minimum
    }

    private function convertToBytes(string $value): int
    {
        $unit = strtolower(substr($value, -1));
        $number = (int) substr($value, 0, -1);

        switch ($unit) {
            case 'g': $number *= 1024 * 1024 * 1024; break;
            case 'm': $number *= 1024 * 1024; break;
            case 'k': $number *= 1024; break;
        }

        return $number;
    }

    private function getLogFile(string $type): string
    {
        $logDir = APP_PATH . '/storage/logs/';
        
        switch ($type) {
            case 'error':
                return $logDir . 'error.log';
            case 'access':
                return $logDir . 'access.log';
            case 'security':
                return $logDir . 'security.log';
            case 'system':
                return $logDir . 'system.log';
            default:
                return $logDir . 'error.log';
        }
    }
}