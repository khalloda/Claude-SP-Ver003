<?php

/**
 * File: app/core/functions.php
 * Purpose: Global helper functions loaded early in bootstrap
 * Notes: Critical functions needed throughout the application
 */

if (!function_exists('csrf_token')) {
    function csrf_token(): string
    {
        return \App\Core\Helpers::generateCsrfToken();
    }
}

if (!function_exists('csrf_field')) {
    function csrf_field(): string
    {
        return \App\Core\Helpers::csrfField();
    }
}

if (!function_exists('old')) {
    function old(string $key, $default = '')
    {
        return \App\Core\Helpers::old($key, $default);
    }
}

if (!function_exists('input')) {
    function input(string $key, $default = null)
    {
        return \App\Core\Helpers::input($key, $default);
    }
}

if (!function_exists('error')) {
    function error(string $key): ?string
    {
        return \App\Core\Helpers::error($key);
    }
}

if (!function_exists('t')) {
    function t(string $key, array $params = []): string
    {
        // Ensure I18n is loaded
        if (class_exists('\App\Core\I18n')) {
            return \App\Core\I18n::translate($key, $params);
        }
        
        // Fallback if I18n not loaded yet
        return $key;
    }
}

if (!function_exists('tc')) {
    function tc(string $key, int $number, array $params = []): string
    {
        // Ensure I18n is loaded
        if (class_exists('\App\Core\I18n')) {
            return \App\Core\I18n::translateChoice($key, $number, $params);
        }
        
        // Fallback if I18n not loaded yet
        return $key;
    }
}

if (!function_exists('asset')) {
    function asset(string $path): string
    {
        return \App\Core\Helpers::asset($path);
    }
}

if (!function_exists('url')) {
    function url(string $path): string
    {
        return \App\Core\Helpers::url($path);
    }
}

if (!function_exists('format_currency')) {
    function format_currency(float $amount, string $currency = 'USD'): string
    {
        return \App\Core\Helpers::formatCurrency($amount, $currency);
    }
}

if (!function_exists('format_date')) {
    function format_date(string $date, string $format = 'Y-m-d'): string
    {
        return \App\Core\Helpers::formatDate($date, $format);
    }
}