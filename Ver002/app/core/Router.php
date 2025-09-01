<?php

/**
 * File: app/core/Router.php
 * Purpose: URL routing with middleware support and parameter validation
 * Depends on: Config, Application middleware
 * Notes: Handles route registration, parameter extraction, middleware execution
 */

namespace App\Core;

use App\Config\Config;

class Router
{
    private array $routes = [];
    private array $currentMiddleware = [];
    private array $middlewareMap = [];

    public function __construct()
    {
        $this->middlewareMap = [
            'auth' => \App\Middleware\AuthMiddleware::class,
            'csrf' => \App\Middleware\CsrfMiddleware::class,
            'rate' => \App\Middleware\RateLimitMiddleware::class,
        ];
    }

    public function get(string $uri, string $action, array $options = []): void
    {
        $this->addRoute('GET', $uri, $action, $options);
    }

    public function post(string $uri, string $action, array $options = []): void
    {
        $this->addRoute('POST', $uri, $action, $options);
    }

    public function put(string $uri, string $action, array $options = []): void
    {
        $this->addRoute('PUT', $uri, $action, $options);
    }

    public function delete(string $uri, string $action, array $options = []): void
    {
        $this->addRoute('DELETE', $uri, $action, $options);
    }

    public function group(array $attributes, callable $callback): void
    {
        $previousMiddleware = $this->currentMiddleware;
        
        if (isset($attributes['middleware'])) {
            $this->currentMiddleware = array_merge(
                $this->currentMiddleware, 
                (array) $attributes['middleware']
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
            $options['middleware'] ?? []
        );

        $this->routes[] = [
            'method' => $method,
            'uri' => $uri,
            'action' => $action,
            'middleware' => $middleware,
            'pattern' => $this->convertToPattern($uri),
            'name' => $options['name'] ?? null
        ];
    }

    private function normalizeUri(string $uri): string
    {
        $uri = '/' . trim($uri, '/');
        return $uri === '/' ? '/' : rtrim($uri, '/');
    }

    private function convertToPattern(string $uri): string
    {
        $pattern = preg_replace('/\{([^}]+)\}/', '([^/]+)', $uri);
        return '#^' . $pattern . '$#';
    }

    public function dispatch(): void
    {
        $method = $_SERVER['REQUEST_METHOD'];
        $uri = $this->getCurrentUri();

        foreach ($this->routes as $route) {
            if ($route['method'] !== $method) {
                continue;
            }

            if (preg_match($route['pattern'], $uri, $matches)) {
                array_shift($matches);
                
                if (!$this->executeMiddleware($route['middleware'])) {
                    return;
                }

                $params = $this->extractParameters($route['uri'], $matches);
                $this->executeAction($route['action'], $params);
                return;
            }
        }

        $this->handle404();
    }

    private function getCurrentUri(): string
    {
        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        return $this->normalizeUri($uri);
    }

    private function executeMiddleware(array $middleware): bool
    {
        foreach ($middleware as $middlewareName) {
            $middlewareInstance = $this->resolveMiddleware($middlewareName);
            
            if ($middlewareInstance && !$middlewareInstance->handle()) {
                return false;
            }
        }
        return true;
    }

    private function resolveMiddleware(string $name): ?object
    {
        if (isset($this->middlewareMap[$name])) {
            $class = $this->middlewareMap[$name];
            if (class_exists($class)) {
                return new $class();
            }
        }

        return null;
    }

    private function extractParameters(string $routeUri, array $matches): array
    {
        $params = [];
        preg_match_all('/\{([^}]+)\}/', $routeUri, $paramNames);
        
        foreach ($paramNames[1] as $index => $name) {
            $value = $matches[$index] ?? null;
            
            if ($name === 'id' && $value !== null) {
                // Check if the value is numeric before validation
                if (!is_numeric($value)) {
                    throw new \InvalidArgumentException("Invalid ID parameter: " . htmlspecialchars($value));
                }
                $params[$name] = filter_var($value, FILTER_VALIDATE_INT, FILTER_NULL_ON_FAILURE);
                if ($params[$name] === null || $params[$name] <= 0) {
                    throw new \InvalidArgumentException("Invalid ID parameter: " . htmlspecialchars($value));
                }
            } else {
                $params[$name] = $this->sanitizeParameter($value);
            }
        }

        return $params;
    }

    private function sanitizeParameter($value): string
    {
        if ($value === null) {
            return '';
        }
        
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    }

    private function executeAction(string $action, array $params): void
    {
        if (!str_contains($action, '@')) {
            throw new \RuntimeException("Invalid action format: {$action}");
        }

        [$controllerName, $method] = explode('@', $action);
        $controllerClass = "App\\Controllers\\{$controllerName}";

        if (!class_exists($controllerClass)) {
            throw new \RuntimeException("Controller {$controllerClass} not found");
        }

        $controller = new $controllerClass();
        
        if (!method_exists($controller, $method)) {
            throw new \RuntimeException("Method {$method} not found in {$controllerClass}");
        }

        $controller->{$method}($params);
    }

    private function handle404(): void
    {
        http_response_code(404);
        
        if (Config::get('app.debug')) {
            echo "<h1>404 - Route Not Found</h1>";
            echo "<p>URI: " . htmlspecialchars($this->getCurrentUri()) . "</p>";
            echo "<p>Method: " . htmlspecialchars($_SERVER['REQUEST_METHOD']) . "</p>";
        } else {
            include dirname(__DIR__, 2) . '/views/errors/404.php';
        }
    }

    public function url(string $name, array $params = []): string
    {
        foreach ($this->routes as $route) {
            if ($route['name'] === $name) {
                $uri = $route['uri'];
                
                foreach ($params as $key => $value) {
                    $uri = str_replace("{{$key}}", $value, $uri);
                }
                
                return $uri;
            }
        }

        throw new \RuntimeException("Named route '{$name}' not found");
    }
}