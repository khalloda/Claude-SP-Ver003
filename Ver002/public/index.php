<?php

/**
 * File: public/index.php  
 * Purpose: Front controller with security headers and application bootstrap
 * Depends on: App\Core\Application, autoloader
 * Notes: Sets security headers, handles all requests through single entry point
 */

// Security headers
header('X-Frame-Options: SAMEORIGIN');
header('X-Content-Type-Options: nosniff');
header('X-XSS-Protection: 1; mode=block');
header('Referrer-Policy: strict-origin-when-cross-origin');

// Remove server signature
if (function_exists('header_remove')) {
    header_remove('X-Powered-By');
}

// Start output buffering
ob_start();

// Set error reporting based on environment
if (isset($_ENV['APP_DEBUG']) && $_ENV['APP_DEBUG'] === 'true') {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
    ini_set('log_errors', 1);
}

// Define base path
define('BASE_PATH', dirname(__DIR__));

// Autoloader
require_once BASE_PATH . '/app/core/Autoloader.php';
App\Core\Autoloader::register();

try {
    // Create and run application
    $app = new App\Core\Application();
    $app->run();
    
} catch (Exception $e) {
    // Log the error
    error_log("Application error: " . $e->getMessage());
    
    // Show user-friendly error page
    http_response_code(500);
    
    if (isset($_ENV['APP_DEBUG']) && $_ENV['APP_DEBUG'] === 'true') {
        echo "<h1>Application Error</h1>";
        echo "<p>" . htmlspecialchars($e->getMessage()) . "</p>";
        echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
    } else {
        echo "<h1>Service Temporarily Unavailable</h1>";
        echo "<p>We're experiencing technical difficulties. Please try again later.</p>";
    }
}

// Flush output buffer
ob_end_flush();
?>