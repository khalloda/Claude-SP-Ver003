<?php

/**
 * File: app/core/Auth.php
 * Purpose: Authentication management with security features
 * Depends on: Database, Config, User model
 * Notes: Handles login, logout, session management, brute force protection
 */

namespace App\Core;

use App\Config\Database;
use App\Models\User;
use PDO;
use PDOException;

class Auth
{
    private const MAX_LOGIN_ATTEMPTS = 5;
    private const LOCKOUT_DURATION = 900; // 15 minutes

    public static function attempt(string $email, string $password): bool
    {
        // Check for brute force protection
        if (!self::canAttemptLogin($email)) {
            return false;
        }

        $user = self::findUserByEmail($email);
        
        if (!$user) {
            self::recordFailedAttempt($email);
            return false;
        }

        if (!self::verifyPassword($password, $user['password_hash'])) {
            self::recordFailedAttempt($email);
            return false;
        }

        if ($user['status'] != 1) {
            return false;
        }

        // Login successful
        self::clearFailedAttempts($user['id']);
        self::updateLastLogin($user['id']);
        self::createSession($user);
        
        return true;
    }

    public static function login(array $user): void
    {
        self::createSession($user);
    }

    public static function logout(): void
    {
        // Clear session data
        $_SESSION = [];
        
        // Destroy session cookie
        if (isset($_COOKIE[session_name()])) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        
        // Destroy session
        session_destroy();
    }

    public static function check(): bool
    {
        return isset($_SESSION['user']) && self::validateSession();
    }

    public static function user(): ?array
    {
        return $_SESSION['user'] ?? null;
    }

    public static function id(): ?int
    {
        return $_SESSION['user']['id'] ?? null;
    }

    public static function hasRole(string $role): bool
    {
        $user = self::user();
        return $user && $user['role'] === $role;
    }

    public static function isAdmin(): bool
    {
        return self::hasRole('admin');
    }

    public static function isManager(): bool
    {
        return self::hasRole('manager') || self::isAdmin();
    }

    public static function changePassword(int $userId, string $newPassword): bool
    {
        if (strlen($newPassword) < 8) {
            return false;
        }

        $passwordHash = self::hashPassword($newPassword);
        
        try {
            $sql = "UPDATE sp_users SET password_hash = ?, updated_at = NOW() WHERE id = ?";
            $stmt = Database::getInstance()->prepare($sql);
            return $stmt->execute([$passwordHash, $userId]);
        } catch (PDOException $e) {
            error_log("Password change error: " . $e->getMessage());
            return false;
        }
    }

    public static function updateProfile(int $userId, array $data): bool
    {
        $allowedFields = ['name', 'email'];
        $updates = [];
        $values = [];

        foreach ($allowedFields as $field) {
            if (isset($data[$field])) {
                $updates[] = "{$field} = ?";
                $values[] = $data[$field];
            }
        }

        if (empty($updates)) {
            return true;
        }

        $updates[] = "updated_at = NOW()";
        $values[] = $userId;

        try {
            $sql = "UPDATE sp_users SET " . implode(', ', $updates) . " WHERE id = ?";
            $stmt = Database::getInstance()->prepare($sql);
            $result = $stmt->execute($values);

            // Update session if current user
            if ($userId === self::id()) {
                foreach ($allowedFields as $field) {
                    if (isset($data[$field])) {
                        $_SESSION['user'][$field] = $data[$field];
                    }
                }
            }

            return $result;
        } catch (PDOException $e) {
            error_log("Profile update error: " . $e->getMessage());
            return false;
        }
    }

    private static function findUserByEmail(string $email): ?array
    {
        try {
            $sql = "SELECT * FROM sp_users WHERE email = ? AND status = 1 LIMIT 1";
            $stmt = Database::getInstance()->prepare($sql);
            $stmt->execute([$email]);
            
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            return $user ?: null;
        } catch (PDOException $e) {
            error_log("Find user error: " . $e->getMessage());
            return null;
        }
    }

    private static function verifyPassword(string $password, string $hash): bool
    {
        return password_verify($password, $hash);
    }

    private static function hashPassword(string $password): string
    {
        return password_hash($password, PASSWORD_DEFAULT);
    }

    private static function canAttemptLogin(string $email): bool
    {
        try {
            $sql = "SELECT failed_login_attempts, locked_until FROM sp_users WHERE email = ? LIMIT 1";
            $stmt = Database::getInstance()->prepare($sql);
            $stmt->execute([$email]);
            
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$user) {
                return true;
            }

            // Check if account is locked
            if ($user['locked_until'] && strtotime($user['locked_until']) > time()) {
                return false;
            }

            // Check if too many attempts
            return $user['failed_login_attempts'] < self::MAX_LOGIN_ATTEMPTS;
        } catch (PDOException $e) {
            error_log("Can attempt login error: " . $e->getMessage());
            return false;
        }
    }

    private static function recordFailedAttempt(string $email): void
    {
        try {
            // First, ensure user record exists
            $sql = "SELECT id, failed_login_attempts FROM sp_users WHERE email = ? LIMIT 1";
            $stmt = Database::getInstance()->prepare($sql);
            $stmt->execute([$email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$user) {
                return;
            }

            $attempts = $user['failed_login_attempts'] + 1;
            $lockedUntil = null;

            // Lock account if max attempts reached
            if ($attempts >= self::MAX_LOGIN_ATTEMPTS) {
                $lockedUntil = date('Y-m-d H:i:s', time() + self::LOCKOUT_DURATION);
            }

            $sql = "UPDATE sp_users SET failed_login_attempts = ?, locked_until = ? WHERE id = ?";
            $stmt = Database::getInstance()->prepare($sql);
            $stmt->execute([$attempts, $lockedUntil, $user['id']]);
        } catch (PDOException $e) {
            error_log("Record failed attempt error: " . $e->getMessage());
        }
    }

    private static function clearFailedAttempts(int $userId): void
    {
        try {
            $sql = "UPDATE sp_users SET failed_login_attempts = 0, locked_until = NULL WHERE id = ?";
            $stmt = Database::getInstance()->prepare($sql);
            $stmt->execute([$userId]);
        } catch (PDOException $e) {
            error_log("Clear failed attempts error: " . $e->getMessage());
        }
    }

    private static function updateLastLogin(int $userId): void
    {
        try {
            $sql = "UPDATE sp_users SET last_login_at = NOW() WHERE id = ?";
            $stmt = Database::getInstance()->prepare($sql);
            $stmt->execute([$userId]);
        } catch (PDOException $e) {
            error_log("Update last login error: " . $e->getMessage());
        }
    }

    private static function createSession(array $user): void
    {
        // Regenerate session ID for security
        session_regenerate_id(true);
        
        // Store user data in session
        $_SESSION['user'] = [
            'id' => $user['id'],
            'name' => $user['name'],
            'email' => $user['email'],
            'role' => $user['role']
        ];
        
        // Store session fingerprint
        $_SESSION['fingerprint'] = self::generateFingerprint();
        
        // Store session timestamp
        $_SESSION['last_activity'] = time();
    }

    private static function validateSession(): bool
    {
        // Check session fingerprint
        if (!isset($_SESSION['fingerprint']) || $_SESSION['fingerprint'] !== self::generateFingerprint()) {
            return false;
        }
        
        // Check session timeout (1 hour)
        if (!isset($_SESSION['last_activity']) || (time() - $_SESSION['last_activity']) > 3600) {
            return false;
        }
        
        // Update last activity
        $_SESSION['last_activity'] = time();
        
        return true;
    }

    private static function generateFingerprint(): string
    {
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
        $acceptLanguage = $_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? '';
        $acceptEncoding = $_SERVER['HTTP_ACCEPT_ENCODING'] ?? '';
        
        return hash('sha256', $userAgent . $acceptLanguage . $acceptEncoding);
    }

    public static function createUser(array $data): ?array
    {
        $requiredFields = ['name', 'email', 'password'];
        
        foreach ($requiredFields as $field) {
            if (empty($data[$field])) {
                return null;
            }
        }

        // Check if email already exists
        if (self::findUserByEmail($data['email'])) {
            return null;
        }

        $passwordHash = self::hashPassword($data['password']);
        
        try {
            $sql = "INSERT INTO sp_users (name, email, password_hash, role, status, created_at, updated_at) 
                    VALUES (?, ?, ?, ?, 1, NOW(), NOW())";
            
            $stmt = Database::getInstance()->prepare($sql);
            $result = $stmt->execute([
                $data['name'],
                $data['email'],
                $passwordHash,
                $data['role'] ?? 'user'
            ]);

            if ($result) {
                $userId = Database::getInstance()->lastInsertId();
                return [
                    'id' => $userId,
                    'name' => $data['name'],
                    'email' => $data['email'],
                    'role' => $data['role'] ?? 'user'
                ];
            }
        } catch (PDOException $e) {
            error_log("Create user error: " . $e->getMessage());
        }

        return null;
    }

    public static function resetPassword(string $email): bool
    {
        $user = self::findUserByEmail($email);
        
        if (!$user) {
            return false;
        }

        // Generate reset token
        $token = bin2hex(random_bytes(32));
        $expiry = date('Y-m-d H:i:s', time() + 3600); // 1 hour

        try {
            // Store reset token (you might want a separate table for this)
            $sql = "UPDATE sp_users SET reset_token = ?, reset_token_expiry = ? WHERE id = ?";
            $stmt = Database::getInstance()->prepare($sql);
            $result = $stmt->execute([$token, $expiry, $user['id']]);

            if ($result) {
                // Here you would send the reset email
                // mail($email, "Password Reset", "Reset token: " . $token);
                return true;
            }
        } catch (PDOException $e) {
            error_log("Reset password error: " . $e->getMessage());
        }

        return false;
    }
}