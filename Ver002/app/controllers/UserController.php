<?php

/**
 * File: app/controllers/UserController.php
 * Purpose: User management controller with role-based access control
 * Depends on: Controller, User model, Auth
 * Notes: Handles user CRUD, role management, profile updates
 */

namespace App\Controllers;

use App\Core\Controller;
use App\Models\User;

class UserController extends Controller
{
    public function index(array $params = []): void
    {
        $this->requireAuth();
        $this->requireRole('admin');

        $status = $this->input['status'] ?? 'all';
        $role = $this->input['role'] ?? 'all';
        $search = $this->input['search'] ?? '';
        
        $users = [];
        
        if ($search) {
            $users = User::where('name', 'LIKE', "%{$search}%")
                        ->orWhere('email', 'LIKE', "%{$search}%")
                        ->orWhere('username', 'LIKE', "%{$search}%")
                        ->orderBy('created_at', 'DESC')
                        ->get();
        } else {
            $query = [];
            
            if ($status !== 'all') {
                $query['status'] = ($status === 'active') ? User::STATUS_ACTIVE : User::STATUS_INACTIVE;
            }
            
            if ($role !== 'all') {
                $query['role'] = $role;
            }
            
            if (!empty($query)) {
                $users = User::where($query)->orderBy('created_at', 'DESC')->get();
            } else {
                $users = User::orderBy('created_at', 'DESC')->get();
            }
        }

        $roles = User::getAvailableRoles();

        $this->view('users/index', [
            'users' => $users,
            'roles' => $roles,
            'status' => $status,
            'role' => $role,
            'search' => $search,
            'page_title' => t('nav.users')
        ]);
    }

    public function create(): void
    {
        $this->requireAuth();
        $this->requireRole('admin');

        $roles = User::getAvailableRoles();

        $this->view('users/create', [
            'user' => new User(),
            'roles' => $roles,
            'page_title' => t('users.add_user')
        ]);
    }

    public function store(): void
    {
        $this->requireAuth();
        $this->requireRole('admin');

        if (!$this->verifyCsrf()) {
            $this->setFlash('error', t('messages.error.invalid_csrf'));
            $this->back();
        }

        if (!$this->validate([
            'name' => 'required|min:2|max:100',
            'username' => 'required|min:3|max:50',
            'email' => 'required|email',
            'password' => 'required|min:8',
            'role' => 'required'
        ])) {
            $this->flashInput();
            $this->back();
        }

        // Check username uniqueness
        if (User::findByUsername($this->input['username'])) {
            $this->setFlash('error', 'Username already exists. Please choose a different username.');
            $this->flashInput();
            $this->back();
        }

        // Check email uniqueness
        if (User::findByEmail($this->input['email'])) {
            $this->setFlash('error', 'Email already exists. Please use a different email.');
            $this->flashInput();
            $this->back();
        }

        $userData = $this->sanitizeInput([
            'name' => $this->input['name'],
            'username' => $this->input['username'],
            'email' => $this->input['email'],
            'password' => password_hash($this->input['password'], PASSWORD_DEFAULT),
            'role' => $this->input['role'],
            'phone' => $this->input['phone'] ?? '',
            'department' => $this->input['department'] ?? '',
            'notes' => $this->input['notes'] ?? '',
            'status' => User::STATUS_ACTIVE,
            'created_by' => $this->getCurrentUser()['id']
        ]);

        $user = User::create($userData);

        if ($user) {
            // Send welcome email (if email system is configured)
            $user->sendWelcomeEmail($this->input['password']);

            $this->clearOldInput();
            $this->setFlash('success', t('messages.success.created'));
            $this->redirect('/users/' . $user->id);
        } else {
            $this->flashInput();
            $this->setFlash('error', t('messages.error.general'));
            $this->back();
        }
    }

    public function show(array $params): void
    {
        $this->requireAuth();
        $this->requireRole('admin');

        $id = $params['id'] ?? 0;
        $user = User::find($id);

        if (!$user) {
            $this->setFlash('error', t('messages.error.not_found'));
            $this->redirect('/users');
        }

        // Get user activity statistics
        $stats = [
            'total_logins' => $user->getTotalLogins(),
            'last_login' => $user->getLastLogin(),
            'quotes_created' => $user->getQuotesCreated(),
            'orders_processed' => $user->getOrdersProcessed(),
            'invoices_created' => $user->getInvoicesCreated()
        ];

        $this->view('users/show', [
            'user' => $user,
            'stats' => $stats,
            'page_title' => $user->name
        ]);
    }

    public function edit(array $params): void
    {
        $this->requireAuth();

        $id = $params['id'] ?? 0;
        $user = User::find($id);

        if (!$user) {
            $this->setFlash('error', t('messages.error.not_found'));
            $this->redirect('/users');
        }

        // Users can edit their own profile, admins can edit any profile
        $currentUser = $this->getCurrentUser();
        if ($user->id !== $currentUser['id'] && $currentUser['role'] !== 'admin') {
            $this->setFlash('error', t('messages.error.forbidden'));
            $this->redirect('/dashboard');
        }

        $roles = User::getAvailableRoles();

        $this->view('users/edit', [
            'user' => $user,
            'roles' => $roles,
            'can_change_role' => $currentUser['role'] === 'admin',
            'page_title' => t('common.edit') . ' - ' . $user->name
        ]);
    }

    public function update(array $params): void
    {
        $this->requireAuth();

        if (!$this->verifyCsrf()) {
            $this->setFlash('error', t('messages.error.invalid_csrf'));
            $this->back();
        }

        $id = $params['id'] ?? 0;
        $user = User::find($id);

        if (!$user) {
            $this->setFlash('error', t('messages.error.not_found'));
            $this->redirect('/users');
        }

        // Users can edit their own profile, admins can edit any profile
        $currentUser = $this->getCurrentUser();
        if ($user->id !== $currentUser['id'] && $currentUser['role'] !== 'admin') {
            $this->setFlash('error', t('messages.error.forbidden'));
            $this->redirect('/dashboard');
        }

        $validation = [
            'name' => 'required|min:2|max:100',
            'username' => 'required|min:3|max:50',
            'email' => 'required|email'
        ];

        // Only validate password if provided
        if (!empty($this->input['password'])) {
            $validation['password'] = 'min:8';
        }

        // Only admins can change roles
        if ($currentUser['role'] === 'admin') {
            $validation['role'] = 'required';
        }

        if (!$this->validate($validation)) {
            $this->flashInput();
            $this->back();
        }

        // Check username uniqueness (excluding current user)
        $existingUser = User::findByUsername($this->input['username']);
        if ($existingUser && $existingUser->id !== $user->id) {
            $this->setFlash('error', 'Username already exists. Please choose a different username.');
            $this->flashInput();
            $this->back();
        }

        // Check email uniqueness (excluding current user)
        $existingUser = User::findByEmail($this->input['email']);
        if ($existingUser && $existingUser->id !== $user->id) {
            $this->setFlash('error', 'Email already exists. Please use a different email.');
            $this->flashInput();
            $this->back();
        }

        $userData = $this->sanitizeInput([
            'name' => $this->input['name'],
            'username' => $this->input['username'],
            'email' => $this->input['email'],
            'phone' => $this->input['phone'] ?? '',
            'department' => $this->input['department'] ?? '',
            'notes' => $this->input['notes'] ?? ''
        ]);

        // Update password if provided
        if (!empty($this->input['password'])) {
            $userData['password'] = password_hash($this->input['password'], PASSWORD_DEFAULT);
        }

        // Only admins can change role and status
        if ($currentUser['role'] === 'admin') {
            $userData['role'] = $this->input['role'];
            $userData['status'] = $this->input['status'] ?? User::STATUS_ACTIVE;
        }

        $user->fill($userData);
        
        if ($user->save()) {
            $this->clearOldInput();
            $this->setFlash('success', t('messages.success.updated'));
            
            // Redirect based on user role
            if ($currentUser['role'] === 'admin') {
                $this->redirect('/users/' . $user->id);
            } else {
                $this->redirect('/profile');
            }
        } else {
            $this->flashInput();
            $this->setFlash('error', t('messages.error.general'));
            $this->back();
        }
    }

    public function destroy(array $params): void
    {
        $this->requireAuth();
        $this->requireRole('admin');

        if (!$this->verifyCsrf()) {
            $this->json([
                'success' => false,
                'message' => t('messages.error.invalid_csrf')
            ], 419);
        }

        $id = $params['id'] ?? 0;
        $user = User::find($id);

        if (!$user) {
            $this->json([
                'success' => false,
                'message' => t('messages.error.not_found')
            ], 404);
        }

        // Cannot delete current user
        $currentUser = $this->getCurrentUser();
        if ($user->id === $currentUser['id']) {
            $this->json([
                'success' => false,
                'message' => 'Cannot delete your own account.'
            ], 400);
        }

        // Check if user has created records
        if ($user->hasCreatedRecords()) {
            $this->json([
                'success' => false,
                'message' => 'Cannot delete user with existing records. Deactivate instead.'
            ], 400);
        }

        if ($user->delete()) {
            $this->json([
                'success' => true,
                'message' => t('messages.success.deleted'),
                'redirect' => '/users'
            ]);
        } else {
            $this->json([
                'success' => false,
                'message' => t('messages.error.general')
            ], 500);
        }
    }

    public function activate(array $params): void
    {
        $this->requireAuth();
        $this->requireRole('admin');

        if (!$this->verifyCsrf()) {
            $this->json([
                'success' => false,
                'message' => t('messages.error.invalid_csrf')
            ], 419);
        }

        $id = $params['id'] ?? 0;
        $user = User::find($id);

        if (!$user) {
            $this->json([
                'success' => false,
                'message' => t('messages.error.not_found')
            ], 404);
        }

        $user->status = User::STATUS_ACTIVE;
        
        if ($user->save()) {
            $this->json([
                'success' => true,
                'message' => 'User activated successfully.'
            ]);
        } else {
            $this->json([
                'success' => false,
                'message' => t('messages.error.general')
            ], 500);
        }
    }

    public function deactivate(array $params): void
    {
        $this->requireAuth();
        $this->requireRole('admin');

        if (!$this->verifyCsrf()) {
            $this->json([
                'success' => false,
                'message' => t('messages.error.invalid_csrf')
            ], 419);
        }

        $id = $params['id'] ?? 0;
        $user = User::find($id);

        if (!$user) {
            $this->json([
                'success' => false,
                'message' => t('messages.error.not_found')
            ], 404);
        }

        // Cannot deactivate current user
        $currentUser = $this->getCurrentUser();
        if ($user->id === $currentUser['id']) {
            $this->json([
                'success' => false,
                'message' => 'Cannot deactivate your own account.'
            ], 400);
        }

        $user->status = User::STATUS_INACTIVE;
        
        if ($user->save()) {
            $this->json([
                'success' => true,
                'message' => 'User deactivated successfully.'
            ]);
        } else {
            $this->json([
                'success' => false,
                'message' => t('messages.error.general')
            ], 500);
        }
    }

    public function resetPassword(array $params): void
    {
        $this->requireAuth();
        $this->requireRole('admin');

        if (!$this->verifyCsrf()) {
            $this->json([
                'success' => false,
                'message' => t('messages.error.invalid_csrf')
            ], 419);
        }

        $id = $params['id'] ?? 0;
        $user = User::find($id);

        if (!$user) {
            $this->json([
                'success' => false,
                'message' => t('messages.error.not_found')
            ], 404);
        }

        // Generate temporary password
        $tempPassword = User::generateTemporaryPassword();
        $user->password = password_hash($tempPassword, PASSWORD_DEFAULT);
        $user->must_change_password = 1;
        
        if ($user->save()) {
            // Send password reset email
            $user->sendPasswordResetEmail($tempPassword);

            $this->json([
                'success' => true,
                'message' => 'Password reset successfully. New password sent to user.',
                'temp_password' => $tempPassword
            ]);
        } else {
            $this->json([
                'success' => false,
                'message' => t('messages.error.general')
            ], 500);
        }
    }

    public function profile(): void
    {
        $this->requireAuth();

        $user = User::find($this->getCurrentUser()['id']);

        $this->view('users/profile', [
            'user' => $user,
            'page_title' => t('nav.profile')
        ]);
    }

    public function updateProfile(): void
    {
        $this->requireAuth();

        if (!$this->verifyCsrf()) {
            $this->setFlash('error', t('messages.error.invalid_csrf'));
            $this->back();
        }

        $user = User::find($this->getCurrentUser()['id']);

        $validation = [
            'name' => 'required|min:2|max:100',
            'email' => 'required|email',
            'current_password' => 'required'
        ];

        // Only validate new password if provided
        if (!empty($this->input['new_password'])) {
            $validation['new_password'] = 'min:8';
            $validation['confirm_password'] = 'required';
        }

        if (!$this->validate($validation)) {
            $this->flashInput();
            $this->back();
        }

        // Verify current password
        if (!password_verify($this->input['current_password'], $user->password)) {
            $this->setFlash('error', 'Current password is incorrect.');
            $this->flashInput();
            $this->back();
        }

        // Check password confirmation
        if (!empty($this->input['new_password'])) {
            if ($this->input['new_password'] !== $this->input['confirm_password']) {
                $this->setFlash('error', 'New password confirmation does not match.');
                $this->flashInput();
                $this->back();
            }
        }

        // Check email uniqueness (excluding current user)
        $existingUser = User::findByEmail($this->input['email']);
        if ($existingUser && $existingUser->id !== $user->id) {
            $this->setFlash('error', 'Email already exists. Please use a different email.');
            $this->flashInput();
            $this->back();
        }

        $userData = $this->sanitizeInput([
            'name' => $this->input['name'],
            'email' => $this->input['email'],
            'phone' => $this->input['phone'] ?? ''
        ]);

        // Update password if provided
        if (!empty($this->input['new_password'])) {
            $userData['password'] = password_hash($this->input['new_password'], PASSWORD_DEFAULT);
            $userData['must_change_password'] = 0;
        }

        $user->fill($userData);
        
        if ($user->save()) {
            $this->clearOldInput();
            $this->setFlash('success', 'Profile updated successfully.');
            $this->redirect('/profile');
        } else {
            $this->flashInput();
            $this->setFlash('error', t('messages.error.general'));
            $this->back();
        }
    }

    public function activityLog(array $params): void
    {
        $this->requireAuth();
        $this->requireRole('admin');

        $id = $params['id'] ?? 0;
        $user = User::find($id);

        if (!$user) {
            $this->setFlash('error', t('messages.error.not_found'));
            $this->redirect('/users');
        }

        $activities = $user->getActivityLog();

        $this->view('users/activity_log', [
            'user' => $user,
            'activities' => $activities,
            'page_title' => t('users.activity_log') . ' - ' . $user->name
        ]);
    }
}