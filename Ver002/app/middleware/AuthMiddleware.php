<?php

/**
 * File: app/middleware/AuthMiddleware.php
 * Purpose: Authentication middleware for protected routes
 * Depends on: Auth, Config
 * Notes: Validates user authentication, handles session timeout
 */

namespace App\Middleware;

use App\Core\Auth;
use App\Config\Config;

class AuthMiddleware
{
    public function handle(): bool
    {
        // Check if user is authenticated
        if (!Auth::check()) {
            $this->handleUnauthenticated();
            return false;
        }

        // Check for session timeout
        if ($this->isSessionExpired()) {
            Auth::logout();
            $this->redirectToLogin('Session expired. Please login again.');
            return false;
        }

        // Update last activity
        $this->updateLastActivity();

        return true;
    }

    private function handleUnauthenticated(): void
    {
        // If it's an AJAX request, return JSON
        if ($this->isAjaxRequest()) {
            $this->sendJsonResponse([
                'success' => false,
                'message' => 'Authentication required',
                'redirect' => '/login'
            ], 401);
        } else {
            // Store intended URL for redirect after login
            if ($_SERVER['REQUEST_METHOD'] === 'GET') {
                $_SESSION['intended_url'] = $_SERVER['REQUEST_URI'];
            }
            
            $this->redirectToLogin();
        }
    }

    private function redirectToLogin(string $message = null): void
    {
        if ($message) {
            $_SESSION['flash']['error'] = $message;
        }
        
        http_response_code(302);
        header('Location: /login');
        exit;
    }

    private function isSessionExpired(): bool
    {
        $timeout = Config::get('session.timeout', 3600); // 1 hour default
        $lastActivity = $_SESSION['last_activity'] ?? 0;
        
        return (time() - $lastActivity) > $timeout;
    }

    private function updateLastActivity(): void
    {
        $_SESSION['last_activity'] = time();
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

    public function requireRole(string $requiredRole): bool
    {
        if (!Auth::check()) {
            return false;
        }

        $user = Auth::user();
        $userRole = $user['role'] ?? 'user';

        // Role hierarchy: admin > manager > user
        $roleHierarchy = ['user' => 1, 'manager' => 2, 'admin' => 3];
        
        $userLevel = $roleHierarchy[$userRole] ?? 0;
        $requiredLevel = $roleHierarchy[$requiredRole] ?? 999;

        if ($userLevel < $requiredLevel) {
            if ($this->isAjaxRequest()) {
                $this->sendJsonResponse([
                    'success' => false,
                    'message' => 'Insufficient permissions'
                ], 403);
            } else {
                http_response_code(403);
                include dirname(__DIR__, 2) . '/views/errors/403.php';
                exit;
            }
        }

        return true;
    }

    public function requireAdmin(): bool
    {
        return $this->requireRole('admin');
    }

    public function requireManager(): bool
    {
        return $this->requireRole('manager');
    }
}