<?php

/**
 * File: app/config/Config.php
 * Purpose: Central configuration management with environment-based settings
 * Depends on: App\Config\Env
 * Notes: No hardcoded credentials; all sensitive data from environment
 */

namespace App\Config;

class Config
{
    private static array $config = [];

    public static function init(): void
    {
        Env::load();

        self::$config = [
            'app' => [
                'name' => 'Spare Parts Management System',
                'env' => Env::get('APP_ENV', 'production'),
                'debug' => Env::bool('APP_DEBUG', false),
                'url' => Env::get('APP_URL', 'http://localhost'),
                'timezone' => Env::get('APP_TIMEZONE', 'UTC'),
                'key' => Env::require('APP_KEY'),
            ],

            'database' => [
                'host' => Env::require('DB_HOST'),
                'port' => Env::int('DB_PORT', 3306),
                'database' => Env::require('DB_DATABASE'),
                'username' => Env::require('DB_USERNAME'),
                'password' => Env::require('DB_PASSWORD'),
                'charset' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'options' => [
                    \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
                    \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
                    \PDO::ATTR_EMULATE_PREPARES => false,
                    \PDO::ATTR_TIMEOUT => 30,
                    \PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci",
                    \PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true
                ]
            ],

            'session' => [
                'lifetime' => Env::int('SESSION_LIFETIME', 7200),
                'path' => '/',
                'domain' => '',
                'secure' => !Env::bool('APP_DEBUG', false), // HTTPS in production
                'httponly' => true,
                'samesite' => 'Lax'
            ],

            'security' => [
                'csrf_token_lifetime' => Env::int('CSRF_TOKEN_LIFETIME', 3600),
                'password_min_length' => 8,
                'session_regenerate_interval' => 1800,
                'max_login_attempts' => 5,
                'lockout_duration' => 900, // 15 minutes
            ],

            'mail' => [
                'host' => Env::get('MAIL_HOST', 'localhost'),
                'port' => Env::int('MAIL_PORT', 587),
                'username' => Env::get('MAIL_USERNAME'),
                'password' => Env::get('MAIL_PASSWORD'),
                'encryption' => Env::get('MAIL_ENCRYPTION', 'tls'),
                'from_address' => Env::get('MAIL_FROM_ADDRESS', 'noreply@localhost'),
                'from_name' => Env::get('MAIL_FROM_NAME', 'Spare Parts System'),
            ],

            'cache' => [
                'driver' => Env::get('CACHE_DRIVER', 'file'),
                'redis_host' => Env::get('CACHE_REDIS_HOST', '127.0.0.1'),
                'redis_port' => Env::int('CACHE_REDIS_PORT', 6379),
            ],

            'pagination' => [
                'per_page' => 15,
                'max_per_page' => 100,
            ],

            'currencies' => [
                'default' => 'USD',
                'supported' => ['USD', 'EUR', 'SAR', 'AED', 'EGP'],
            ],

            'localization' => [
                'default_locale' => 'en',
                'supported_locales' => ['en', 'ar'],
                'fallback_locale' => 'en',
            ],
        ];

        // Set error reporting based on environment
        if (self::get('app.debug')) {
            error_reporting(E_ALL);
            ini_set('display_errors', 1);
        } else {
            error_reporting(0);
            ini_set('display_errors', 0);
            ini_set('log_errors', 1);
        }

        // Set timezone
        date_default_timezone_set(self::get('app.timezone'));

        // Configure session
        self::configureSession();
    }

    public static function get(string $key, $default = null)
    {
        $keys = explode('.', $key);
        $value = self::$config;

        foreach ($keys as $k) {
            if (!isset($value[$k])) {
                return $default;
            }
            $value = $value[$k];
        }

        return $value;
    }

    public static function set(string $key, $value): void
    {
        $keys = explode('.', $key);
        $config = &self::$config;

        foreach ($keys as $k) {
            if (!isset($config[$k]) || !is_array($config[$k])) {
                $config[$k] = [];
            }
            $config = &$config[$k];
        }

        $config = $value;
    }

    private static function configureSession(): void
    {
        $sessionConfig = self::get('session');
        
        ini_set('session.cookie_lifetime', $sessionConfig['lifetime']);
        ini_set('session.cookie_path', $sessionConfig['path']);
        ini_set('session.cookie_domain', $sessionConfig['domain']);
        ini_set('session.cookie_secure', $sessionConfig['secure'] ? '1' : '0');
        ini_set('session.cookie_httponly', $sessionConfig['httponly'] ? '1' : '0');
        ini_set('session.cookie_samesite', $sessionConfig['samesite']);
        ini_set('session.use_strict_mode', '1');
        ini_set('session.use_cookies', '1');
        ini_set('session.use_only_cookies', '1');
    }

    public static function all(): array
    {
        return self::$config;
    }
}