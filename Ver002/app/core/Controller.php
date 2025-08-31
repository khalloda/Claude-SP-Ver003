<?php

/**
 * File: app/core/Controller.php  
 * Purpose: Base controller with common functionality and security
 * Depends on: Config, Helpers, I18n
 * Notes: Provides view rendering, validation, CSRF protection, input handling
 */

namespace App\Core;

use App\Config\Config;

abstract class Controller
{
    protected array $data = [];
    protected array $errors = [];
    protected array $input = [];

    public function __construct()
    {
        $this->loadInput();
        $this->setCommonViewData();
    }

    protected function loadInput(): void
    {
        $this->input = array_merge($_GET, $_POST);
        
        foreach ($this->input as $key => $value) {
            if (is_string($value)) {
                $this->input[$key] = trim($value);
            }
        }
    }

    protected function setCommonViewData(): void
    {
        $this->data['app_name'] = Config::get('app.name');
        $this->data['app_url'] = Config::get('app.url');
        $this->data['csrf_token'] = csrf_token();
        $this->data['current_user'] = $this->getCurrentUser();
        $this->data['current_lang'] = $_SESSION['lang'] ?? 'en';
        $this->data['errors'] = $this->errors;
    }

    protected function view(string $view, array $data = []): void
    {
        $data = array_merge($this->data, $data);
        
        extract($data);
        
        $viewPath = $this->resolveViewPath($view);
        
        if (!file_exists($viewPath)) {
            throw new \RuntimeException("View not found: {$view}");
        }

        include $viewPath;
    }

    protected function layout(string $layout, $contentOrData = null, array $data = []): void
    {
        // Handle two calling patterns:
        // 1. layout($layout, $data) - for views that call $this->layout() directly
        // 2. layout($layout, $content, $data) - for controller internal use
        
        if (is_array($contentOrData)) {
            // Pattern 1: layout($layout, $data) - set up for view rendering
            $this->data = array_merge($this->data, $contentOrData);
            return; // View will handle the actual rendering
        } else {
            // Pattern 2: layout($layout, $content, $data) - render immediately
            $data = array_merge($this->data, $data);
            $data['content'] = $contentOrData;
            
            extract($data);
            
            $layoutPath = dirname(__DIR__) . "/views/layouts/{$layout}.php";
            
            if (!file_exists($layoutPath)) {
                throw new \RuntimeException("Layout not found: {$layout}");
            }

            include $layoutPath;
        }
    }

    protected function json(array $data, int $statusCode = 200): void
    {
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');
        
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit;
    }

    protected function redirect(string $url, int $statusCode = 302): void
    {
        if (!filter_var($url, FILTER_VALIDATE_URL) && !str_starts_with($url, '/')) {
            $url = '/' . ltrim($url, '/');
        }

        http_response_code($statusCode);
        header("Location: {$url}");
        exit;
    }

    protected function back(): void
    {
        $referer = $_SERVER['HTTP_REFERER'] ?? '/';
        $this->redirect($referer);
    }

    protected function validate(array $rules): bool
    {
        $this->errors = [];
        
        foreach ($rules as $field => $rule) {
            $value = $this->input[$field] ?? null;
            $this->validateField($field, $value, $rule);
        }

        return empty($this->errors);
    }

    private function validateField(string $field, $value, string $rules): void
    {
        $ruleList = explode('|', $rules);
        
        foreach ($ruleList as $rule) {
            $this->applyRule($field, $value, $rule);
        }
    }

    private function applyRule(string $field, $value, string $rule): void
    {
        if (str_contains($rule, ':')) {
            [$ruleName, $parameter] = explode(':', $rule, 2);
        } else {
            $ruleName = $rule;
            $parameter = null;
        }

        switch ($ruleName) {
            case 'required':
                if (empty($value)) {
                    $this->errors[$field] = t("Field {$field} is required");
                }
                break;
                
            case 'email':
                if (!empty($value) && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                    $this->errors[$field] = t("Field {$field} must be a valid email");
                }
                break;
                
            case 'min':
                if (!empty($value) && strlen($value) < (int)$parameter) {
                    $this->errors[$field] = t("Field {$field} must be at least {$parameter} characters");
                }
                break;
                
            case 'max':
                if (!empty($value) && strlen($value) > (int)$parameter) {
                    $this->errors[$field] = t("Field {$field} must not exceed {$parameter} characters");
                }
                break;
                
            case 'numeric':
                if (!empty($value) && !is_numeric($value)) {
                    $this->errors[$field] = t("Field {$field} must be numeric");
                }
                break;
                
            case 'integer':
                if (!empty($value) && !filter_var($value, FILTER_VALIDATE_INT)) {
                    $this->errors[$field] = t("Field {$field} must be an integer");
                }
                break;
                
            case 'alpha':
                if (!empty($value) && !preg_match('/^[a-zA-Z\s]+$/', $value)) {
                    $this->errors[$field] = t("Field {$field} must contain only letters");
                }
                break;
                
            case 'alphanumeric':
                if (!empty($value) && !preg_match('/^[a-zA-Z0-9\s]+$/', $value)) {
                    $this->errors[$field] = t("Field {$field} must contain only letters and numbers");
                }
                break;
                
            case 'in':
                if (!empty($value)) {
                    $allowedValues = explode(',', $parameter);
                    if (!in_array($value, $allowedValues)) {
                        $this->errors[$field] = t("Field {$field} must be one of: " . $parameter);
                    }
                }
                break;
        }
    }

    protected function sanitizeInput(array $data): array
    {
        $sanitized = [];
        
        foreach ($data as $key => $value) {
            if (is_string($value)) {
                $sanitized[$key] = htmlspecialchars(trim($value), ENT_QUOTES, 'UTF-8');
            } elseif (is_array($value)) {
                $sanitized[$key] = $this->sanitizeInput($value);
            } else {
                $sanitized[$key] = $value;
            }
        }
        
        return $sanitized;
    }

    protected function old(string $key, $default = ''): string
    {
        return $_SESSION['old_input'][$key] ?? $default;
    }

    protected function flashInput(): void
    {
        $_SESSION['old_input'] = $this->input;
    }

    protected function clearOldInput(): void
    {
        unset($_SESSION['old_input']);
    }

    protected function setFlash(string $type, string $message): void
    {
        $_SESSION['flash'][$type] = $message;
    }

    protected function getFlash(string $type = null): array|string|null
    {
        if ($type) {
            $message = $_SESSION['flash'][$type] ?? null;
            unset($_SESSION['flash'][$type]);
            return $message;
        }
        
        $messages = $_SESSION['flash'] ?? [];
        unset($_SESSION['flash']);
        return $messages;
    }

    protected function getCurrentUser(): ?array
    {
        return $_SESSION['user'] ?? null;
    }

    // Add methods that views call directly
    public function hasRole($roles): bool
    {
        $user = $this->getCurrentUser();
        if (!$user) return false;
        
        if (is_string($roles)) {
            $roles = [$roles];
        }
        
        return in_array($user['role'] ?? '', $roles);
    }

    protected function requireAuth(): void
    {
        if (!$this->getCurrentUser()) {
            $this->redirect('/login');
        }
    }

    protected function requireRole(string $role): void
    {
        $user = $this->getCurrentUser();
        
        if (!$user || $user['role'] !== $role) {
            http_response_code(403);
            $this->view('errors/403');
            exit;
        }
    }

    protected function verifyCsrf(): bool
    {
        return verify_csrf_token();
    }

    private function resolveViewPath(string $view): string
    {
        $view = str_replace('.', '/', $view);
        return dirname(__DIR__) . "/views/{$view}.php";
    }

    protected function abort(int $code, string $message = ''): void
    {
        http_response_code($code);
        
        if ($message) {
            echo htmlspecialchars($message);
        } else {
            $this->view("errors/{$code}");
        }
        
        exit;
    }

    protected function upload(string $inputName, string $directory = 'uploads'): ?string
    {
        if (!isset($_FILES[$inputName]) || $_FILES[$inputName]['error'] !== UPLOAD_ERR_OK) {
            return null;
        }

        $file = $_FILES[$inputName];
        $uploadDir = dirname(__DIR__, 2) . "/public/{$directory}/";
        
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $filename = uniqid() . '_' . basename($file['name']);
        $filepath = $uploadDir . $filename;

        if (move_uploaded_file($file['tmp_name'], $filepath)) {
            return "{$directory}/{$filename}";
        }

        return null;
    }
}