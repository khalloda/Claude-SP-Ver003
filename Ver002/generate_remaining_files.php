<?php

/**
 * This script creates all the remaining files for the Ver002 system
 * Run this to populate the entire codebase structure
 */

// Core files to create
$coreFiles = [
    'app/core/Application.php' => '<?php

namespace App\\Core;

use App\\Config\\Config;
use App\\Middleware\\AuthMiddleware;
use App\\Middleware\\CsrfMiddleware;

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
        Config::init();
        
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        I18n::init();
        
        if (!Config::get(\'app.debug\')) {
            set_error_handler([$this, \'errorHandler\']);
            set_exception_handler([$this, \'exceptionHandler\']);
        }
    }

    private function registerMiddleware(): void
    {
        $this->middleware = [
            \'auth\' => new AuthMiddleware(),
            \'csrf\' => new CsrfMiddleware(),
        ];
    }

    private function registerRoutes(): void
    {
        // Public routes
        $this->router->get(\'/\', \'AuthController@loginForm\');
        $this->router->get(\'/login\', \'AuthController@loginForm\');
        $this->router->post(\'/login\', \'AuthController@login\');
        $this->router->get(\'/logout\', \'AuthController@logout\');

        // Protected routes
        $this->router->group([\'middleware\' => [\'auth\']], function($router) {
            $router->get(\'/dashboard\', \'DashboardController@index\');
            $this->addResourceRoutes($router, \'products\', \'ProductController\');
            $this->addResourceRoutes($router, \'clients\', \'ClientController\');
        });
    }

    private function addResourceRoutes(Router $router, string $resource, string $controller): void
    {
        $router->get("/{$resource}", "{$controller}@index");
        $router->get("/{$resource}/create", "{$controller}@create");
        $router->post("/{$resource}", "{$controller}@store", [\'middleware\' => [\'csrf\']]);
        $router->get("/{$resource}/{id}", "{$controller}@show");
        $router->get("/{$resource}/{id}/edit", "{$controller}@edit");
        $router->post("/{$resource}/{id}", "{$controller}@update", [\'middleware\' => [\'csrf\']]);
        $router->post("/{$resource}/{id}/delete", "{$controller}@destroy", [\'middleware\' => [\'csrf\']]);
    }

    public function run(): void
    {
        try {
            $this->router->dispatch();
        } catch (\\Exception $e) {
            $this->handleException($e);
        }
    }

    private function handleException(\\Exception $e): void
    {
        error_log("Application error: " . $e->getMessage());
        
        if (Config::get(\'app.debug\')) {
            echo "<h1>Application Error</h1>";
            echo "<p>" . htmlspecialchars($e->getMessage()) . "</p>";
            echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
        } else {
            http_response_code(500);
            echo "<h1>Service Temporarily Unavailable</h1>";
        }
    }

    public function errorHandler($severity, $message, $file, $line): bool
    {
        error_log("PHP Error: {$message} in {$file}:{$line}");
        return true;
    }

    public function exceptionHandler(\\Throwable $exception): void
    {
        $this->handleException($exception);
    }
}',

    'app/core/Router.php' => '<?php

namespace App\\Core;

use App\\Config\\Config;

class Router
{
    private array $routes = [];
    private array $currentMiddleware = [];

    public function get(string $uri, string $action, array $options = []): void
    {
        $this->addRoute(\'GET\', $uri, $action, $options);
    }

    public function post(string $uri, string $action, array $options = []): void
    {
        $this->addRoute(\'POST\', $uri, $action, $options);
    }

    public function group(array $attributes, callable $callback): void
    {
        $previousMiddleware = $this->currentMiddleware;
        
        if (isset($attributes[\'middleware\'])) {
            $this->currentMiddleware = array_merge(
                $this->currentMiddleware, 
                (array) $attributes[\'middleware\']
            );
        }

        $callback($this);
        $this->currentMiddleware = $previousMiddleware;
    }

    private function addRoute(string $method, string $uri, string $action, array $options = []): void
    {
        $uri = $this->normalizeUri($uri);
        
        $middleware = array_merge(
            $this->currentMiddleware,
            $options[\'middleware\'] ?? []
        );

        $this->routes[] = [
            \'method\' => $method,
            \'uri\' => $uri,
            \'action\' => $action,
            \'middleware\' => $middleware,
            \'pattern\' => $this->convertToPattern($uri)
        ];
    }

    private function normalizeUri(string $uri): string
    {
        $uri = \'/\' . trim($uri, \'/\');
        return $uri === \'/\' ? \'/\' : rtrim($uri, \'/\');
    }

    private function convertToPattern(string $uri): string
    {
        $pattern = preg_replace(\'/\\{([^}]+)\\}/\', \'([^/]+)\', $uri);
        return \'#^\' . $pattern . \'$#\';
    }

    public function dispatch(): void
    {
        $method = $_SERVER[\'REQUEST_METHOD\'];
        $uri = $this->normalizeUri(parse_url($_SERVER[\'REQUEST_URI\'], PHP_URL_PATH));

        foreach ($this->routes as $route) {
            if ($route[\'method\'] !== $method) {
                continue;
            }

            if (preg_match($route[\'pattern\'], $uri, $matches)) {
                array_shift($matches);
                
                if (!$this->executeMiddleware($route[\'middleware\'])) {
                    return;
                }

                $params = $this->extractParameters($route[\'uri\'], $matches);
                $this->executeAction($route[\'action\'], $params);
                return;
            }
        }

        $this->handle404();
    }

    private function executeMiddleware(array $middleware): bool
    {
        foreach ($middleware as $middlewareName) {
            $middlewareClass = $this->resolveMiddleware($middlewareName);
            
            if ($middlewareClass && !$middlewareClass->handle()) {
                return false;
            }
        }
        return true;
    }

    private function resolveMiddleware(string $name): ?object
    {
        $middlewareMap = [
            \'auth\' => \\App\\Middleware\\AuthMiddleware::class,
            \'csrf\' => \\App\\Middleware\\CsrfMiddleware::class,
        ];

        if (isset($middlewareMap[$name])) {
            return new $middlewareMap[$name]();
        }

        return null;
    }

    private function extractParameters(string $routeUri, array $matches): array
    {
        $params = [];
        preg_match_all(\'/\\{([^}]+)\\}/\', $routeUri, $paramNames);
        
        foreach ($paramNames[1] as $index => $name) {
            $value = $matches[$index] ?? null;
            
            if ($name === \'id\' && $value !== null) {
                $params[$name] = filter_var($value, FILTER_VALIDATE_INT, FILTER_NULL_ON_FAILURE);
                if ($params[$name] === null) {
                    throw new \\InvalidArgumentException("Invalid ID parameter");
                }
            } else {
                $params[$name] = htmlspecialchars($value ?? \'\', ENT_QUOTES, \'UTF-8\');
            }
        }

        return $params;
    }

    private function executeAction(string $action, array $params): void
    {
        [$controllerName, $method] = explode(\'@\', $action);
        $controllerClass = "App\\\\Controllers\\\\{$controllerName}";

        if (!class_exists($controllerClass)) {
            throw new \\RuntimeException("Controller {$controllerClass} not found");
        }

        $controller = new $controllerClass();
        
        if (!method_exists($controller, $method)) {
            throw new \\RuntimeException("Method {$method} not found in {$controllerClass}");
        }

        $controller->{$method}($params);
    }

    private function handle404(): void
    {
        http_response_code(404);
        echo "<h1>404 - Page Not Found</h1>";
    }
}'
];

// Create the files
foreach ($coreFiles as $path => $content) {
    $fullPath = __DIR__ . '/' . $path;
    $dir = dirname($fullPath);
    
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
    
    file_put_contents($fullPath, $content);
    echo "Created: $path\n";
}

echo "\nCore files created successfully!\n";
echo "Run this script to generate remaining framework files.\n";
?>