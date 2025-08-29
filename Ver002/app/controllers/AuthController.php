<?php

/**
 * File: app/controllers/AuthController.php
 * Purpose: Authentication controller for login/logout functionality
 * Depends on: Controller, Auth, RateLimitMiddleware
 * Notes: Handles login forms, authentication, session management
 */

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Auth;
use App\Middleware\RateLimitMiddleware;

class AuthController extends Controller
{
    public function loginForm(): void
    {
        // Redirect if already authenticated
        if (Auth::check()) {
            $this->redirect('/dashboard');
        }

        $this->view('auth/login');
    }

    public function login(): void
    {
        // Rate limiting for login attempts
        $rateLimiter = new RateLimitMiddleware();
        if (!$rateLimiter->checkLoginAttempts()) {
            return; // Rate limiter handles the response
        }

        if (!$this->verifyCsrf()) {
            $this->setFlash('error', 'Security token invalid. Please try again.');
            $this->back();
        }

        // Validate input
        if (!$this->validate([
            'email' => 'required|email',
            'password' => 'required|min:1'
        ])) {
            $this->flashInput();
            $this->setFlash('error', 'Please check your input and try again.');
            $this->back();
        }

        $email = $this->input['email'];
        $password = $this->input['password'];
        $remember = isset($this->input['remember']) && $this->input['remember'];

        // Attempt authentication
        if (Auth::attempt($email, $password)) {
            $this->clearOldInput();
            
            // Set remember me cookie if requested
            if ($remember) {
                $this->setRememberToken();
            }

            $this->setFlash('success', 'Welcome back!');
            
            // Redirect to intended URL or dashboard
            $intendedUrl = $_SESSION['intended_url'] ?? '/dashboard';
            unset($_SESSION['intended_url']);
            
            $this->redirect($intendedUrl);
        } else {
            $this->flashInput();
            $this->setFlash('error', 'Invalid email or password.');
            $this->redirect('/login');
        }
    }

    public function logout(): void
    {
        Auth::logout();
        $this->setFlash('success', 'You have been logged out successfully.');
        $this->redirect('/login');
    }

    public function forgotPassword(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->handleForgotPassword();
        } else {
            $this->view('auth/forgot-password');
        }
    }

    private function handleForgotPassword(): void
    {
        if (!$this->verifyCsrf()) {
            $this->setFlash('error', 'Security token invalid. Please try again.');
            $this->back();
        }

        if (!$this->validate(['email' => 'required|email'])) {
            $this->flashInput();
            $this->setFlash('error', 'Please enter a valid email address.');
            $this->back();
        }

        $email = $this->input['email'];
        
        // Always show success message for security
        $this->setFlash('success', 'If an account with that email exists, password reset instructions have been sent.');
        
        // Actually send reset email (implement based on your email system)
        Auth::resetPassword($email);
        
        $this->redirect('/login');
    }

    public function resetPassword(): void
    {
        $token = $this->input['token'] ?? '';
        
        if (!$token) {
            $this->setFlash('error', 'Invalid reset token.');
            $this->redirect('/login');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->handleResetPassword($token);
        } else {
            $this->view('auth/reset-password', ['token' => $token]);
        }
    }

    private function handleResetPassword(string $token): void
    {
        if (!$this->verifyCsrf()) {
            $this->setFlash('error', 'Security token invalid. Please try again.');
            $this->back();
        }

        if (!$this->validate([
            'password' => 'required|min:8',
            'password_confirmation' => 'required'
        ])) {
            $this->setFlash('error', 'Please check your input and try again.');
            $this->back();
        }

        $password = $this->input['password'];
        $confirmation = $this->input['password_confirmation'];

        if ($password !== $confirmation) {
            $this->setFlash('error', 'Passwords do not match.');
            $this->back();
        }

        // Validate password strength
        if (!$this->isPasswordStrong($password)) {
            $this->setFlash('error', 'Password must be at least 8 characters and contain uppercase, lowercase, and numbers.');
            $this->back();
        }

        // Process reset (implement based on your reset token system)
        if ($this->processPasswordReset($token, $password)) {
            $this->setFlash('success', 'Password reset successfully. Please login with your new password.');
            $this->redirect('/login');
        } else {
            $this->setFlash('error', 'Invalid or expired reset token.');
            $this->redirect('/login');
        }
    }

    private function setRememberToken(): void
    {
        $token = bin2hex(random_bytes(32));
        $expiry = time() + (30 * 24 * 60 * 60); // 30 days
        
        // Store token in database (implement based on your schema)
        // For now, just set a cookie
        setcookie('remember_token', $token, $expiry, '/', '', true, true);
    }

    private function isPasswordStrong(string $password): bool
    {
        return strlen($password) >= 8 &&
               preg_match('/[A-Z]/', $password) &&
               preg_match('/[a-z]/', $password) &&
               preg_match('/[0-9]/', $password);
    }

    private function processPasswordReset(string $token, string $password): bool
    {
        // Implement based on your reset token system
        // This is a placeholder implementation
        
        try {
            $sql = "SELECT id FROM sp_users WHERE reset_token = ? AND reset_token_expiry > NOW() LIMIT 1";
            $stmt = \App\Config\Database::getInstance()->prepare($sql);
            $stmt->execute([$token]);
            
            $user = $stmt->fetch(\PDO::FETCH_ASSOC);
            
            if ($user) {
                $passwordHash = password_hash($password, PASSWORD_DEFAULT);
                
                $updateSql = "UPDATE sp_users SET password_hash = ?, reset_token = NULL, reset_token_expiry = NULL WHERE id = ?";
                $updateStmt = \App\Config\Database::getInstance()->prepare($updateSql);
                
                return $updateStmt->execute([$passwordHash, $user['id']]);
            }
        } catch (\Exception $e) {
            error_log("Password reset error: " . $e->getMessage());
        }
        
        return false;
    }

    public function profile(): void
    {
        $this->requireAuth();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->updateProfile();
        } else {
            $this->view('auth/profile');
        }
    }

    private function updateProfile(): void
    {
        if (!$this->verifyCsrf()) {
            $this->setFlash('error', 'Security token invalid. Please try again.');
            $this->back();
        }

        if (!$this->validate([
            'name' => 'required|min:2|max:100',
            'email' => 'required|email|max:150'
        ])) {
            $this->flashInput();
            $this->setFlash('error', 'Please check your input and try again.');
            $this->back();
        }

        $userId = Auth::id();
        $data = [
            'name' => $this->input['name'],
            'email' => $this->input['email']
        ];

        if (Auth::updateProfile($userId, $data)) {
            $this->clearOldInput();
            $this->setFlash('success', 'Profile updated successfully.');
        } else {
            $this->flashInput();
            $this->setFlash('error', 'Failed to update profile. Email may already be in use.');
        }

        $this->back();
    }

    public function changePassword(): void
    {
        $this->requireAuth();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->handleChangePassword();
        } else {
            $this->view('auth/change-password');
        }
    }

    private function handleChangePassword(): void
    {
        if (!$this->verifyCsrf()) {
            $this->setFlash('error', 'Security token invalid. Please try again.');
            $this->back();
        }

        if (!$this->validate([
            'current_password' => 'required',
            'new_password' => 'required|min:8',
            'confirm_password' => 'required'
        ])) {
            $this->setFlash('error', 'Please check your input and try again.');
            $this->back();
        }

        $currentPassword = $this->input['current_password'];
        $newPassword = $this->input['new_password'];
        $confirmPassword = $this->input['confirm_password'];

        // Verify current password
        $user = Auth::user();
        if (!password_verify($currentPassword, $user['password_hash'] ?? '')) {
            $this->setFlash('error', 'Current password is incorrect.');
            $this->back();
        }

        // Check new password confirmation
        if ($newPassword !== $confirmPassword) {
            $this->setFlash('error', 'New passwords do not match.');
            $this->back();
        }

        // Check password strength
        if (!$this->isPasswordStrong($newPassword)) {
            $this->setFlash('error', 'Password must be at least 8 characters and contain uppercase, lowercase, and numbers.');
            $this->back();
        }

        if (Auth::changePassword(Auth::id(), $newPassword)) {
            $this->setFlash('success', 'Password changed successfully.');
            
            // Log out all sessions except current for security
            session_regenerate_id(true);
        } else {
            $this->setFlash('error', 'Failed to change password. Please try again.');
        }

        $this->back();
    }
}