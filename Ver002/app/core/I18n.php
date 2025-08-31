<?php

/**
 * File: app/core/I18n.php
 * Purpose: Internationalization and localization support
 * Depends on: Config
 * Notes: Handles translations, language switching, pluralization, RTL support
 */

namespace App\Core;

use App\Config\Config;

class I18n
{
    private static array $translations = [];
    private static string $currentLocale = 'en';
    private static array $supportedLocales = ['en', 'ar'];
    private static array $rtlLocales = ['ar', 'he', 'fa'];

    public static function init(): void
    {
        // Set locale from session, URL, or default
        $locale = self::detectLocale();
        self::setLocale($locale);
        
        // Load translations
        self::loadTranslations($locale);
    }

    public static function setLocale(string $locale): void
    {
        if (in_array($locale, self::$supportedLocales)) {
            self::$currentLocale = $locale;
            $_SESSION['lang'] = $locale;
            
            // Set PHP locale for date/number formatting
            $phpLocale = self::getPhpLocale($locale);
            setlocale(LC_TIME, $phpLocale);
        }
    }

    public static function getLocale(): string
    {
        return self::$currentLocale;
    }

    public static function isRtl(): bool
    {
        return in_array(self::$currentLocale, self::$rtlLocales);
    }

    public static function translate(string $key, array $params = []): string
    {
        $translation = self::getTranslation($key);
        
        if (!empty($params)) {
            foreach ($params as $param => $value) {
                $translation = str_replace("{{$param}}", $value, $translation);
            }
        }
        
        return $translation;
    }

    public static function translateChoice(string $key, int $number, array $params = []): string
    {
        $translations = self::getTranslation($key);
        
        if (!is_array($translations)) {
            return self::translate($key, $params);
        }

        $translation = self::choosePlural($translations, $number);
        
        // Replace {count} placeholder
        $params['count'] = $number;
        
        foreach ($params as $param => $value) {
            $translation = str_replace("{{$param}}", $value, $translation);
        }
        
        return $translation;
    }

    public static function getSupportedLocales(): array
    {
        return self::$supportedLocales;
    }

    public static function getAvailableLanguages(): array
    {
        return [
            'en' => [
                'name' => 'English',
                'native' => 'English',
                'dir' => 'ltr'
            ],
            'ar' => [
                'name' => 'Arabic',
                'native' => 'العربية',
                'dir' => 'rtl'
            ]
        ];
    }

    public static function formatDate(string $date, string $format = null): string
    {
        $format = $format ?: self::getDateFormat();
        
        if (self::$currentLocale === 'ar') {
            return self::formatArabicDate($date, $format);
        }
        
        return date($format, strtotime($date));
    }

    public static function formatNumber(float $number, int $decimals = 2): string
    {
        if (self::$currentLocale === 'ar') {
            $formatted = number_format($number, $decimals, '.', ',');
            return self::convertToArabicNumerals($formatted);
        }
        
        return number_format($number, $decimals, '.', ',');
    }

    public static function formatCurrency(float $amount, string $currency = null): string
    {
        $currency = $currency ?: Config::get('app.default_currency', 'USD');
        $formatted = self::formatNumber($amount);
        
        $symbols = [
            'USD' => '$',
            'EUR' => '€',
            'SAR' => 'ر.س',
            'AED' => 'د.إ',
            'EGP' => 'ج.م'
        ];
        
        $symbol = $symbols[$currency] ?? $currency;
        
        if (self::isRtl()) {
            return $formatted . ' ' . $symbol;
        } else {
            return $symbol . ' ' . $formatted;
        }
    }

    private static function detectLocale(): string
    {
        // 1. Check URL parameter
        if (isset($_GET['lang']) && in_array($_GET['lang'], self::$supportedLocales)) {
            return $_GET['lang'];
        }
        
        // 2. Check session
        if (isset($_SESSION['lang']) && in_array($_SESSION['lang'], self::$supportedLocales)) {
            return $_SESSION['lang'];
        }
        
        // 3. Check Accept-Language header
        if (isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
            $acceptLanguage = $_SERVER['HTTP_ACCEPT_LANGUAGE'];
            $languages = explode(',', $acceptLanguage);
            
            foreach ($languages as $language) {
                $lang = substr(trim($language), 0, 2);
                if (in_array($lang, self::$supportedLocales)) {
                    return $lang;
                }
            }
        }
        
        // 4. Default to English
        return 'en';
    }

    private static function loadTranslations(string $locale): void
    {
        $translationFile = dirname(__DIR__) . "/lang/{$locale}.php";
        
        if (file_exists($translationFile)) {
            self::$translations[$locale] = include $translationFile;
            error_log("I18n: Loaded translations for locale: {$locale} from {$translationFile}");
        } else {
            error_log("I18n: Translation file not found: {$translationFile}");
            // Fallback to English if translation file doesn't exist
            if ($locale !== 'en') {
                $fallbackFile = dirname(__DIR__) . "/lang/en.php";
                if (file_exists($fallbackFile)) {
                    self::$translations[$locale] = include $fallbackFile;
                }
            }
        }
    }

    private static function getTranslation(string $key): string
    {
        $keys = explode('.', $key);
        $translation = self::$translations[self::$currentLocale] ?? [];
        
        foreach ($keys as $k) {
            if (is_array($translation) && isset($translation[$k])) {
                $translation = $translation[$k];
            } else {
                // Return the key if translation not found
                return $key;
            }
        }
        
        return is_string($translation) ? $translation : $key;
    }

    private static function choosePlural(array $translations, int $number): string
    {
        if (self::$currentLocale === 'ar') {
            return self::chooseArabicPlural($translations, $number);
        }
        
        // English pluralization
        if ($number === 1) {
            return $translations['one'] ?? $translations[0] ?? '';
        } else {
            return $translations['other'] ?? $translations[1] ?? '';
        }
    }

    private static function chooseArabicPlural(array $translations, int $number): string
    {
        // Arabic has complex pluralization rules
        if ($number === 0) {
            return $translations['zero'] ?? $translations['other'] ?? '';
        } elseif ($number === 1) {
            return $translations['one'] ?? '';
        } elseif ($number === 2) {
            return $translations['two'] ?? $translations['other'] ?? '';
        } elseif ($number >= 3 && $number <= 10) {
            return $translations['few'] ?? $translations['other'] ?? '';
        } else {
            return $translations['many'] ?? $translations['other'] ?? '';
        }
    }

    private static function getPhpLocale(string $locale): string
    {
        $locales = [
            'en' => 'en_US.UTF-8',
            'ar' => 'ar_SA.UTF-8'
        ];
        
        return $locales[$locale] ?? 'en_US.UTF-8';
    }

    private static function getDateFormat(): string
    {
        $formats = [
            'en' => 'Y-m-d',
            'ar' => 'Y-m-d'
        ];
        
        return $formats[self::$currentLocale] ?? 'Y-m-d';
    }

    private static function formatArabicDate(string $date, string $format): string
    {
        $englishMonths = [
            'January', 'February', 'March', 'April', 'May', 'June',
            'July', 'August', 'September', 'October', 'November', 'December'
        ];
        
        $arabicMonths = [
            'يناير', 'فبراير', 'مارس', 'أبريل', 'مايو', 'يونيو',
            'يوليو', 'أغسطس', 'سبتمبر', 'أكتوبر', 'نوفمبر', 'ديسمبر'
        ];
        
        $formatted = date($format, strtotime($date));
        $formatted = str_replace($englishMonths, $arabicMonths, $formatted);
        
        return self::convertToArabicNumerals($formatted);
    }

    private static function convertToArabicNumerals(string $text): string
    {
        $english = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];
        $arabic = ['٠', '١', '٢', '٣', '٤', '٥', '٦', '٧', '٨', '٩'];
        
        return str_replace($english, $arabic, $text);
    }

    public static function getDirection(): string
    {
        return self::isRtl() ? 'rtl' : 'ltr';
    }

    public static function getCurrentLanguage(): array
    {
        $languages = self::getAvailableLanguages();
        return $languages[self::$currentLocale] ?? $languages['en'];
    }

    public static function getLanguageSwitchUrl(string $locale): string
    {
        $currentUrl = $_SERVER['REQUEST_URI'];
        
        // Remove existing lang parameter
        $url = preg_replace('/[?&]lang=[^&]*/', '', $currentUrl);
        
        // Add new lang parameter
        $separator = str_contains($url, '?') ? '&' : '?';
        
        return $url . $separator . 'lang=' . $locale;
    }
}