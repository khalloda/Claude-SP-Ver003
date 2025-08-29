<?php

/**
 * File: app/core/Autoloader.php
 * Purpose: PSR-4 compliant autoloader with fallback support
 * Depends on: None (core functionality)
 * Notes: Handles class loading with namespace mapping and case-insensitive fallback
 */

namespace App\Core;

class Autoloader
{
    private static array $prefixes = [];
    private static bool $registered = false;

    public static function register(): void
    {
        if (self::$registered) {
            return;
        }

        // Register PSR-4 namespaces
        self::addNamespace('App\\', dirname(__DIR__) . '/');
        
        spl_autoload_register([self::class, 'loadClass']);
        self::$registered = true;
    }

    public static function addNamespace(string $prefix, string $baseDir): void
    {
        $prefix = trim($prefix, '\\') . '\\';
        $baseDir = rtrim($baseDir, DIRECTORY_SEPARATOR) . '/';
        
        if (!isset(self::$prefixes[$prefix])) {
            self::$prefixes[$prefix] = [];
        }
        
        array_push(self::$prefixes[$prefix], $baseDir);
    }

    public static function loadClass(string $class): bool
    {
        $prefix = $class;
        
        while (false !== $pos = strrpos($prefix, '\\')) {
            $prefix = substr($class, 0, $pos + 1);
            $relativeClass = substr($class, $pos + 1);
            
            $mappedFile = self::loadMappedFile($prefix, $relativeClass);
            if ($mappedFile) {
                return $mappedFile;
            }
            
            $prefix = rtrim($prefix, '\\');
        }
        
        return false;
    }

    private static function loadMappedFile(string $prefix, string $relativeClass): bool
    {
        if (!isset(self::$prefixes[$prefix])) {
            return false;
        }
        
        foreach (self::$prefixes[$prefix] as $baseDir) {
            $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';
            
            if (self::requireFile($file)) {
                return true;
            }
            
            // Fallback: try lowercase filename (for case-insensitive filesystems)
            $fileLower = $baseDir . str_replace('\\', '/', strtolower($relativeClass)) . '.php';
            if (self::requireFile($fileLower)) {
                return true;
            }
        }
        
        return false;
    }

    private static function requireFile(string $file): bool
    {
        if (file_exists($file)) {
            require_once $file;
            return true;
        }
        
        return false;
    }
}