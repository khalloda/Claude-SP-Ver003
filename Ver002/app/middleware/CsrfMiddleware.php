<?php

/**
 * File: app/middleware/CsrfMiddleware.php
 * Purpose: CSRF protection middleware for state-changing operations
 * Depends on: Helpers (csrf functions)
 * Notes: Validates CSRF tokens, handles token rotation, provides security headers
 */

namespace App\Middleware;

class CsrfMiddleware
{
    private const EXCLUDED_METHODS = ['GET', 'HEAD', 'OPTIONS'];
    private const TOKEN_HEADER = 'HTTP_X_CSRF_TOKEN';

    public function handle(): bool
    {
        // Skip CSRF protection for read-only methods
        if (in_array($_SERVER['REQUEST_METHOD'], self::EXCLUDED_METHODS)) {
            return true;
        }

        // Get token from various sources
        $token = $this->getTokenFromRequest();

        if (!$token) {
            $this->handleMissingToken();
            return false;
        }

        // Verify token
        if (!verify_csrf_token($token)) {
            $this->handleInvalidToken();
            return false;
        }

        // Rotate token after successful verification (for high-security forms)
        if ($this->shouldRotateToken()) {
            regenerate_csrf_token();
        }

        return true;
    }

    private function getTokenFromRequest(): ?string
    {
        // Check POST data first
        if (isset($_POST['csrf_token'])) {
            return $_POST['csrf_token'];
        }

        // Check custom header (for AJAX requests)
        if (isset($_SERVER[self::TOKEN_HEADER])) {
            return $_SERVER[self::TOKEN_HEADER];
        }

        // Check meta tag token (fallback)
        if (isset($_POST['_token'])) {
            return $_POST['_token'];
        }

        return null;
    }

    private function handleMissingToken(): void
    {
        error_log("CSRF: Missing token for {$_SERVER['REQUEST_METHOD']} {$_SERVER['REQUEST_URI']} from IP: {$this->getClientIp()}");
        
        if ($this->isAjaxRequest()) {
            $this->sendJsonResponse([
                'success' => false,
                'message' => 'CSRF token missing',
                'code' => 'CSRF_MISSING'
            ], 419);
        } else {
            http_response_code(419);
            $_SESSION['flash']['error'] = 'Security token missing. Please try again.';
            $this->redirectBack();
        }
    }

    private function handleInvalidToken(): void
    {
        error_log("CSRF: Invalid token for {$_SERVER['REQUEST_METHOD']} {$_SERVER['REQUEST_URI']} from IP: {$this->getClientIp()}");
        
        if ($this->isAjaxRequest()) {
            $this->sendJsonResponse([
                'success' => false,
                'message' => 'CSRF token invalid',
                'code' => 'CSRF_INVALID',
                'new_token' => csrf_token() // Provide new token
            ], 419);
        } else {
            http_response_code(419);
            $_SESSION['flash']['error'] = 'Security token invalid. Please try again.';
            $this->redirectBack();
        }
    }

    private function shouldRotateToken(): bool
    {
        // Rotate tokens for high-risk operations
        $highRiskRoutes = [
            '/profile',
            '/users',
            '/admin',
            '/settings',
            '/password'
        ];

        $currentRoute = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        
        foreach ($highRiskRoutes as $route) {
            if (str_starts_with($currentRoute, $route)) {
                return true;
            }
        }

        return false;
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
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }

    private function redirectBack(): void
    {
        $referer = $_SERVER['HTTP_REFERER'] ?? '/';
        
        // Validate referer is from same origin
        if (!$this->isSameOrigin($referer)) {
            $referer = '/';
        }
        
        header("Location: {$referer}");
        exit;
    }

    private function isSameOrigin(string $url): bool
    {
        $parsed = parse_url($url);
        $currentHost = $_SERVER['HTTP_HOST'] ?? '';
        
        return ($parsed['host'] ?? '') === $currentHost;
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
                
                // Handle comma-separated IPs (from proxies)
                if (str_contains($ip, ',')) {
                    $ip = trim(explode(',', $ip)[0]);
                }
                
                // Validate IP
                if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                    return $ip;
                }
            }
        }

        return $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    }

    public static function generateSecurityHeaders(): void
    {
        // Content Security Policy
        header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline' 'unsafe-eval' https://cdnjs.cloudflare.com; style-src 'self' 'unsafe-inline' https://fonts.googleapis.com; font-src 'self' https://fonts.gstatic.com; img-src 'self' data: https:; connect-src 'self'");
        
        // Other security headers
        header('X-Content-Type-Options: nosniff');
        header('X-Frame-Options: SAMEORIGIN');
        header('X-XSS-Protection: 1; mode=block');
        header('Referrer-Policy: strict-origin-when-cross-origin');
        header('Permissions-Policy: geolocation=(), microphone=(), camera=()');
        
        // HSTS (only if using HTTPS)
        if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
            header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
        }
    }
}