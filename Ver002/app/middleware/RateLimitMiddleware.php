<?php

/**
 * File: app/middleware/RateLimitMiddleware.php
 * Purpose: Rate limiting middleware to prevent abuse
 * Depends on: Config, Database (optional for persistent storage)
 * Notes: Implements sliding window rate limiting, IP-based and user-based limits
 */

namespace App\Middleware;

use App\Config\Config;

class RateLimitMiddleware
{
    private const DEFAULT_LIMIT = 60; // requests per minute
    private const DEFAULT_WINDOW = 60; // seconds
    private const STORAGE_PREFIX = 'rate_limit:';
    
    private array $limits = [
        'login' => ['requests' => 5, 'window' => 300], // 5 attempts per 5 minutes
        'api' => ['requests' => 100, 'window' => 60],   // 100 requests per minute
        'upload' => ['requests' => 10, 'window' => 60], // 10 uploads per minute
        'default' => ['requests' => 60, 'window' => 60] // 60 requests per minute
    ];

    public function handle(string $type = 'default'): bool
    {
        $limit = $this->limits[$type] ?? $this->limits['default'];
        $identifier = $this->getIdentifier($type);
        
        if ($this->isRateLimited($identifier, $limit)) {
            $this->handleRateLimit($limit);
            return false;
        }
        
        $this->recordRequest($identifier, $limit);
        return true;
    }

    private function getIdentifier(string $type): string
    {
        $ip = $this->getClientIp();
        
        // For authenticated users, use user ID + IP
        if (isset($_SESSION['user']['id'])) {
            return $type . ':user:' . $_SESSION['user']['id'] . ':' . $ip;
        }
        
        // For anonymous users, use IP only
        return $type . ':ip:' . $ip;
    }

    private function isRateLimited(string $identifier, array $limit): bool
    {
        $key = self::STORAGE_PREFIX . $identifier;
        $currentTime = time();
        $windowStart = $currentTime - $limit['window'];
        
        // Get existing requests from session (simple implementation)
        $requests = $_SESSION[$key] ?? [];
        
        // Clean old requests outside the window
        $requests = array_filter($requests, function($timestamp) use ($windowStart) {
            return $timestamp > $windowStart;
        });
        
        // Update session
        $_SESSION[$key] = $requests;
        
        // Check if limit exceeded
        return count($requests) >= $limit['requests'];
    }

    private function recordRequest(string $identifier, array $limit): void
    {
        $key = self::STORAGE_PREFIX . $identifier;
        $currentTime = time();
        
        // Get existing requests
        $requests = $_SESSION[$key] ?? [];
        
        // Add current request
        $requests[] = $currentTime;
        
        // Keep only requests within the window
        $windowStart = $currentTime - $limit['window'];
        $requests = array_filter($requests, function($timestamp) use ($windowStart) {
            return $timestamp > $windowStart;
        });
        
        // Store updated requests
        $_SESSION[$key] = array_values($requests);
    }

    private function handleRateLimit(array $limit): void
    {
        $retryAfter = $limit['window'];
        
        error_log("Rate limit exceeded for IP: {$this->getClientIp()} - {$limit['requests']} requests in {$limit['window']} seconds");
        
        http_response_code(429);
        header("Retry-After: {$retryAfter}");
        header('X-RateLimit-Limit: ' . $limit['requests']);
        header('X-RateLimit-Remaining: 0');
        header('X-RateLimit-Reset: ' . (time() + $retryAfter));
        
        if ($this->isAjaxRequest()) {
            $this->sendJsonResponse([
                'success' => false,
                'message' => 'Too many requests. Please try again later.',
                'retry_after' => $retryAfter
            ], 429);
        } else {
            echo $this->getRateLimitPage($retryAfter);
            exit;
        }
    }

    private function getRateLimitPage(int $retryAfter): string
    {
        return "
        <!DOCTYPE html>
        <html>
        <head>
            <title>Rate Limited</title>
            <style>
                body { font-family: Arial, sans-serif; text-align: center; padding: 50px; }
                .container { max-width: 500px; margin: 0 auto; }
                h1 { color: #e74c3c; }
                .retry-info { margin: 20px 0; padding: 15px; background: #f8f9fa; border-radius: 5px; }
            </style>
        </head>
        <body>
            <div class='container'>
                <h1>Too Many Requests</h1>
                <p>You have made too many requests. Please wait before trying again.</p>
                <div class='retry-info'>
                    <strong>Retry after: {$retryAfter} seconds</strong>
                </div>
                <p><a href='/'>Return to Homepage</a></p>
            </div>
            <script>
                setTimeout(function() {
                    window.location.reload();
                }, {$retryAfter}000);
            </script>
        </body>
        </html>";
    }

    private function isAjaxRequest(): bool
    {
        return (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 
                strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') ||
               (isset($_SERVER['CONTENT_TYPE']) && 
                str_contains($_SERVER['CONTENT_TYPE'], 'application/json'));
    }

    private function sendJsonResponse(array $data, int $statusCode = 200): void
    {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }

    private function getClientIp(): string
    {
        $ipKeys = [
            'HTTP_X_FORWARDED_FOR',
            'HTTP_X_REAL_IP',
            'HTTP_CLIENT_IP',
            'REMOTE_ADDR'
        ];

        foreach ($ipKeys as $key) {
            if (isset($_SERVER[$key]) && !empty($_SERVER[$key])) {
                $ip = $_SERVER[$key];
                
                // Handle comma-separated IPs
                if (str_contains($ip, ',')) {
                    $ip = trim(explode(',', $ip)[0]);
                }
                
                // Validate IP
                if (filter_var($ip, FILTER_VALIDATE_IP)) {
                    return $ip;
                }
            }
        }

        return $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
    }

    public function checkLoginAttempts(): bool
    {
        return $this->handle('login');
    }

    public function checkApiRequests(): bool
    {
        return $this->handle('api');
    }

    public function checkUploadRequests(): bool
    {
        return $this->handle('upload');
    }

    public static function getRemainingRequests(string $type = 'default'): int
    {
        $middleware = new self();
        $limit = $middleware->limits[$type] ?? $middleware->limits['default'];
        $identifier = $middleware->getIdentifier($type);
        $key = self::STORAGE_PREFIX . $identifier;
        
        $requests = $_SESSION[$key] ?? [];
        $currentTime = time();
        $windowStart = $currentTime - $limit['window'];
        
        // Count requests in current window
        $currentRequests = count(array_filter($requests, function($timestamp) use ($windowStart) {
            return $timestamp > $windowStart;
        }));
        
        return max(0, $limit['requests'] - $currentRequests);
    }

    public static function getResetTime(string $type = 'default'): int
    {
        $middleware = new self();
        $limit = $middleware->limits[$type] ?? $middleware->limits['default'];
        
        return time() + $limit['window'];
    }
}