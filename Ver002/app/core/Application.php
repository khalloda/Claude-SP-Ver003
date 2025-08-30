<?php

/**
 * File: app/core/Application.php
 * Purpose: Main application bootstrap and request handler
 * Depends on: All core classes, Config
 * Notes: Initializes framework, handles requests through middleware pipeline
 */

namespace App\Core;

use App\Config\Config;
use App\Middleware\AuthMiddleware;
use App\Middleware\CsrfMiddleware;

class Application
{
    private Router $router;
    private array $middleware = [];

    public function __construct()
    {
        $this->initializeFramework();
        $this->router = new Router();
        $this->registerMiddleware();
        $this->registerRoutes();
    }

    private function initializeFramework(): void
    {
        // Load global functions FIRST (before anything else)
        require_once dirname(__DIR__) . '/core/functions.php';
        
        // Initialize configuration
        Config::init();
        
        // Start session with security settings
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Initialize internationalization
        I18n::init();
        
        // Set error handler for production
        if (!Config::get('app.debug')) {
            set_error_handler([$this, 'errorHandler']);
            set_exception_handler([$this, 'exceptionHandler']);
        }
    }

    private function registerMiddleware(): void
    {
        $this->middleware = [
            'auth' => new AuthMiddleware(),
            'csrf' => new CsrfMiddleware(),
        ];
    }

    private function registerRoutes(): void
    {
        // Public routes
        $this->router->get('/', 'AuthController@loginForm');
        $this->router->get('/login', 'AuthController@loginForm');
        $this->router->post('/login', 'AuthController@login');
        $this->router->get('/logout', 'AuthController@logout');

        // Protected routes (require authentication)
        $this->router->group(['middleware' => ['auth']], function($router) {
            
            // Dashboard
            $router->get('/dashboard', 'DashboardController@index');
            
            // Resource routes with CSRF protection for state-changing operations
            $this->addResourceRoutes($router, 'clients', 'ClientController');
            $this->addResourceRoutes($router, 'suppliers', 'SupplierController');
            $this->addResourceRoutes($router, 'products', 'ProductController');
            $this->addResourceRoutes($router, 'warehouses', 'WarehouseController');
            $this->addResourceRoutes($router, 'quotes', 'QuoteController');
            $this->addResourceRoutes($router, 'salesorders', 'SalesorderController');
            $this->addResourceRoutes($router, 'invoices', 'InvoiceController');
            $this->addResourceRoutes($router, 'payments', 'PaymentController');
            $this->addResourceRoutes($router, 'currencies', 'CurrencyController');
            $this->addResourceRoutes($router, 'dropdowns', 'DropdownController');
            $this->addResourceRoutes($router, 'users', 'UserController');
            
            // Profile management
            $router->get('/profile', 'ProfileController@index');
            $router->get('/profile/edit', 'ProfileController@edit');
            $router->post('/profile/update', 'ProfileController@update', ['middleware' => ['csrf']]);
            $router->get('/profile/change-password', 'ProfileController@changePassword');
            $router->post('/profile/update-password', 'ProfileController@updatePassword', ['middleware' => ['csrf']]);
            $router->get('/profile/preferences', 'ProfileController@preferences');
            $router->post('/profile/update-preferences', 'ProfileController@updatePreferences', ['middleware' => ['csrf']]);
            $router->get('/profile/activity', 'ProfileController@activity');
            $router->get('/profile/export-data', 'ProfileController@exportData');
            $router->post('/profile/delete-account', 'ProfileController@deleteAccount', ['middleware' => ['csrf']]);
            $router->get('/profile/download-report', 'ProfileController@downloadReport');
            
            // Reports and analytics
            $router->get('/reports', 'ReportsController@index');
            $router->get('/reports/sales', 'ReportsController@sales');
            $router->get('/reports/sales/{type}', 'ReportsController@sales');
            $router->get('/reports/inventory', 'ReportsController@inventory');
            $router->get('/reports/inventory/{type}', 'ReportsController@inventory');
            $router->get('/reports/financial', 'ReportsController@financial');
            $router->get('/reports/financial/{type}', 'ReportsController@financial');
            $router->get('/reports/clients', 'ReportsController@clients');
            $router->get('/reports/clients/{type}', 'ReportsController@clients');
            $router->get('/reports/custom', 'ReportsController@custom');
            $router->post('/reports/generate-custom', 'ReportsController@generateCustom', ['middleware' => ['csrf']]);
            
            // Settings (admin role check handled in controller)
            $router->get('/settings', 'SettingsController@index');
            $router->get('/settings/general', 'SettingsController@general');
            $router->post('/settings/update-general', 'SettingsController@updateGeneral', ['middleware' => ['csrf']]);
            $router->get('/settings/security', 'SettingsController@security');
            $router->post('/settings/update-security', 'SettingsController@updateSecurity', ['middleware' => ['csrf']]);
            $router->get('/settings/email', 'SettingsController@email');
            $router->post('/settings/update-email', 'SettingsController@updateEmail', ['middleware' => ['csrf']]);
            $router->post('/settings/test-email', 'SettingsController@testEmail', ['middleware' => ['csrf']]);
            $router->get('/settings/backup', 'SettingsController@backup');
            $router->post('/settings/create-backup', 'SettingsController@createBackup', ['middleware' => ['csrf']]);
            $router->get('/settings/download-backup/{filename}', 'SettingsController@downloadBackup');
            $router->post('/settings/delete-backup/{filename}', 'SettingsController@deleteBackup', ['middleware' => ['csrf']]);
            $router->get('/settings/maintenance', 'SettingsController@maintenance');
            $router->post('/settings/run-maintenance', 'SettingsController@runMaintenance', ['middleware' => ['csrf']]);
            $router->get('/settings/logs', 'SettingsController@logs');
            
            // AJAX endpoints
            $router->get('/api/dropdowns/get-by-parent', 'DropdownController@getByParent');
            $router->get('/api/quotes/get-product-details', 'QuoteController@getProductDetails');
            $router->post('/api/quotes/convert-to-order', 'QuoteController@convertToOrder', ['middleware' => ['csrf']]);
            $router->post('/api/salesorders/convert-to-invoice', 'SalesorderController@convertToInvoice', ['middleware' => ['csrf']]);
        });
    }

    private function addResourceRoutes(Router $router, string $resource, string $controller): void
    {
        // Read operations (no CSRF needed)
        $router->get("/{$resource}", "{$controller}@index");
        $router->get("/{$resource}/create", "{$controller}@create");
        $router->get("/{$resource}/{id}", "{$controller}@show");
        $router->get("/{$resource}/{id}/edit", "{$controller}@edit");
        
        // Write operations (require CSRF)
        $router->post("/{$resource}", "{$controller}@store", ['middleware' => ['csrf']]);
        $router->post("/{$resource}/{id}", "{$controller}@update", ['middleware' => ['csrf']]);
        $router->post("/{$resource}/{id}/delete", "{$controller}@destroy", ['middleware' => ['csrf']]);
    }

    public function run(): void
    {
        try {
            $this->router->dispatch();
        } catch (\Exception $e) {
            $this->handleException($e);
        }
    }

    private function handleException(\Exception $e): void
    {
        error_log("Application error: " . $e->getMessage() . " in " . $e->getFile() . ":" . $e->getLine());
        
        if (Config::get('app.debug')) {
            echo "<h1>Application Error</h1>";
            echo "<p>" . htmlspecialchars($e->getMessage()) . "</p>";
            echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
        } else {
            http_response_code(500);
            include dirname(__DIR__) . '/views/errors/500.php';
        }
    }

    public function errorHandler($severity, $message, $file, $line): bool
    {
        if (!(error_reporting() & $severity)) {
            return false;
        }

        error_log("PHP Error: {$message} in {$file}:{$line}");
        
        if (Config::get('app.debug')) {
            echo "<b>Error:</b> {$message} in <b>{$file}</b> on line <b>{$line}</b><br>";
        }
        
        return true;
    }

    public function exceptionHandler(\Throwable $exception): void
    {
        $this->handleException($exception);
    }

    public function getRouter(): Router
    {
        return $this->router;
    }

    public function getMiddleware(string $name): ?object
    {
        return $this->middleware[$name] ?? null;
    }
}