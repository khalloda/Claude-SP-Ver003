<?php

/**
 * File: app/core/Helpers.php
 * Purpose: Utility functions with XSS protection, CSRF handling, and formatting
 * Depends on: App\Core\I18n, session management
 * Notes: All output functions include XSS protection; CSRF tokens with rotation
 */

namespace App\Core;

class Helpers
{
    public static function generateCsrfToken(): string
    {
        // Generate new token if none exists or if token is expired
        if (!isset($_SESSION['_token']) || 
            !isset($_SESSION['_token_time']) || 
            (time() - $_SESSION['_token_time']) > 3600) {
            
            $_SESSION['_token'] = bin2hex(random_bytes(32));
            $_SESSION['_token_time'] = time();
        }
        
        return $_SESSION['_token'];
    }

    public static function verifyCsrf(): bool
    {
        $token = self::input('_token');
        
        if (!isset($_SESSION['_token']) || empty($token)) {
            return false;
        }

        return hash_equals($_SESSION['_token'], $token);
    }

    public static function csrfField(): string
    {
        $token = self::generateCsrfToken();
        return '<input type="hidden" name="_token" value="' . htmlspecialchars($token, ENT_QUOTES) . '">';
    }

    public static function input(string $key, $default = null)
    {
        $value = $_POST[$key] ?? $_GET[$key] ?? $default;
        
        if (is_string($value)) {
            return htmlspecialchars(trim($value), ENT_QUOTES, 'UTF-8');
        }
        
        return $value;
    }

    public static function old(string $key, $default = '')
    {
        $oldData = $_SESSION['old'] ?? [];
        $value = $oldData[$key] ?? $default;
        
        if (is_string($value)) {
            return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
        }
        
        return $value;
    }

    public static function error(string $key): ?string
    {
        $errors = $_SESSION['errors'] ?? [];
        $error = $errors[$key] ?? null;
        
        return $error ? htmlspecialchars($error, ENT_QUOTES, 'UTF-8') : null;
    }

    public static function clearOldInputAndErrors(): void
    {
        unset($_SESSION['errors'], $_SESSION['old']);
    }

    public static function redirect(string $url): void
    {
        if (headers_sent()) {
            echo "<script>window.location.href='" . addslashes($url) . "';</script>";
        } else {
            header('Location: ' . $url);
        }
        exit;
    }

    public static function formatCurrency(float $amount, string $currency = 'USD'): string
    {
        $symbols = [
            'USD' => '$',
            'EUR' => '€',
            'SAR' => 'ر.س',
            'AED' => 'د.إ',
            'EGP' => 'ج.م'
        ];
        
        $symbol = $symbols[$currency] ?? $currency;
        return $symbol . ' ' . number_format($amount, 2);
    }

    public static function formatDate(string $date, string $format = 'Y-m-d'): string
    {
        try {
            $dateTime = new \DateTime($date);
            return $dateTime->format($format);
        } catch (\Exception $e) {
            return htmlspecialchars($date, ENT_QUOTES, 'UTF-8');
        }
    }

    public static function formatDateTime(string $datetime, string $format = 'Y-m-d H:i'): string
    {
        return self::formatDate($datetime, $format);
    }

    public static function truncate(string $text, int $length = 100, string $suffix = '...'): string
    {
        $text = htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
        
        if (mb_strlen($text) <= $length) {
            return $text;
        }
        
        return mb_substr($text, 0, $length) . $suffix;
    }

    public static function statusBadge(string $status, array $colors = []): string
    {
        $defaultColors = [
            'active' => 'success',
            'inactive' => 'secondary',
            'pending' => 'warning',
            'completed' => 'success',
            'cancelled' => 'danger',
            'paid' => 'success',
            'unpaid' => 'danger',
            'partial' => 'warning'
        ];
        
        $colors = array_merge($defaultColors, $colors);
        $color = $colors[$status] ?? 'secondary';
        $displayStatus = ucfirst($status);
        
        return "<span class=\"badge badge-{$color}\">" . htmlspecialchars($displayStatus, ENT_QUOTES, 'UTF-8') . "</span>";
    }

    public static function currentUrl(): string
    {
        return htmlspecialchars($_SERVER['REQUEST_URI'], ENT_QUOTES, 'UTF-8');
    }

    public static function asset(string $path): string
    {
        $baseUrl = rtrim($_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'], '/');
        return $baseUrl . '/assets/' . ltrim($path, '/');
    }

    public static function url(string $path): string
    {
        $baseUrl = rtrim($_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'], '/');
        return $baseUrl . '/' . ltrim($path, '/');
    }
}