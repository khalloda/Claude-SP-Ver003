<?php

/**
 * File: app/models/User.php
 * Purpose: User model with authentication and role management
 * Depends on: Model base class, Auth
 * Notes: Handles user data, roles, permissions, password management
 */

namespace App\Models;

use App\Core\Model;

class User extends Model
{
    protected static string $table = 'sp_users';
    
    protected static array $fillable = [
        'name',
        'email', 
        'password_hash',
        'role',
        'status',
        'last_login_at',
        'failed_login_attempts',
        'locked_until'
    ];

    protected static array $hidden = [
        'password_hash',
        'failed_login_attempts',
        'locked_until',
        'reset_token',
        'reset_token_expiry'
    ];

    protected static array $casts = [
        'id' => 'int',
        'status' => 'int',
        'failed_login_attempts' => 'int',
        'last_login_at' => 'datetime',
        'locked_until' => 'datetime'
    ];

    public const STATUS_INACTIVE = 0;
    public const STATUS_ACTIVE = 1;
    public const STATUS_SUSPENDED = 2;

    public const ROLE_USER = 'user';
    public const ROLE_MANAGER = 'manager';
    public const ROLE_ADMIN = 'admin';

    public static function findByEmail(string $email): ?self
    {
        return self::where('email', $email)->first();
    }

    public static function getActiveUsers(): array
    {
        return self::where('status', self::STATUS_ACTIVE)->get();
    }

    public static function getUsersByRole(string $role): array
    {
        return self::where('role', $role)
                  ->where('status', self::STATUS_ACTIVE)
                  ->get();
    }

    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    public function isSuspended(): bool
    {
        return $this->status === self::STATUS_SUSPENDED;
    }

    public function isLocked(): bool
    {
        return $this->locked_until && strtotime($this->locked_until) > time();
    }

    public function hasRole(string $role): bool
    {
        return $this->role === $role;
    }

    public function isAdmin(): bool
    {
        return $this->hasRole(self::ROLE_ADMIN);
    }

    public function isManager(): bool
    {
        return $this->hasRole(self::ROLE_MANAGER) || $this->isAdmin();
    }

    public function canManageUsers(): bool
    {
        return $this->isAdmin();
    }

    public function canViewReports(): bool
    {
        return $this->isManager();
    }

    public function canManageInventory(): bool
    {
        return $this->isManager();
    }

    public function setPassword(string $password): void
    {
        $this->password_hash = password_hash($password, PASSWORD_DEFAULT);
    }

    public function verifyPassword(string $password): bool
    {
        return password_verify($password, $this->password_hash);
    }

    public function activate(): bool
    {
        $this->status = self::STATUS_ACTIVE;
        return $this->save();
    }

    public function suspend(): bool
    {
        $this->status = self::STATUS_SUSPENDED;
        return $this->save();
    }

    public function unlock(): bool
    {
        $this->locked_until = null;
        $this->failed_login_attempts = 0;
        return $this->save();
    }

    public function incrementFailedAttempts(): bool
    {
        $this->failed_login_attempts++;
        
        // Lock account after 5 failed attempts for 15 minutes
        if ($this->failed_login_attempts >= 5) {
            $this->locked_until = date('Y-m-d H:i:s', time() + 900);
        }
        
        return $this->save();
    }

    public function clearFailedAttempts(): bool
    {
        $this->failed_login_attempts = 0;
        $this->locked_until = null;
        return $this->save();
    }

    public function updateLastLogin(): bool
    {
        $this->last_login_at = date('Y-m-d H:i:s');
        return $this->save();
    }

    public static function createUser(array $data): ?self
    {
        // Validate required fields
        $required = ['name', 'email', 'password'];
        foreach ($required as $field) {
            if (empty($data[$field])) {
                return null;
            }
        }

        // Check if email already exists
        if (self::findByEmail($data['email'])) {
            return null;
        }

        // Validate email
        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            return null;
        }

        // Validate password strength
        if (!self::isPasswordStrong($data['password'])) {
            return null;
        }

        $user = new self();
        $user->name = trim($data['name']);
        $user->email = strtolower(trim($data['email']));
        $user->setPassword($data['password']);
        $user->role = $data['role'] ?? self::ROLE_USER;
        $user->status = $data['status'] ?? self::STATUS_ACTIVE;

        if ($user->save()) {
            return $user;
        }

        return null;
    }

    public static function isPasswordStrong(string $password): bool
    {
        // Minimum 8 characters, at least one uppercase, one lowercase, one number
        return strlen($password) >= 8 &&
               preg_match('/[A-Z]/', $password) &&
               preg_match('/[a-z]/', $password) &&
               preg_match('/[0-9]/', $password);
    }

    public function updateProfile(array $data): bool
    {
        $allowedFields = ['name', 'email'];
        
        foreach ($allowedFields as $field) {
            if (isset($data[$field])) {
                // Validate email if provided
                if ($field === 'email') {
                    $email = strtolower(trim($data[$field]));
                    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                        return false;
                    }
                    
                    // Check if email is already taken by another user
                    $existingUser = self::findByEmail($email);
                    if ($existingUser && $existingUser->id !== $this->id) {
                        return false;
                    }
                    
                    $this->$field = $email;
                } else {
                    $this->$field = trim($data[$field]);
                }
            }
        }
        
        return $this->save();
    }

    public function getDisplayName(): string
    {
        return $this->name ?: $this->email;
    }

    public function getRoleLabel(): string
    {
        $roles = [
            self::ROLE_USER => 'User',
            self::ROLE_MANAGER => 'Manager',
            self::ROLE_ADMIN => 'Administrator'
        ];
        
        return $roles[$this->role] ?? 'Unknown';
    }

    public function getStatusLabel(): string
    {
        $statuses = [
            self::STATUS_INACTIVE => 'Inactive',
            self::STATUS_ACTIVE => 'Active',
            self::STATUS_SUSPENDED => 'Suspended'
        ];
        
        return $statuses[$this->status] ?? 'Unknown';
    }

    public function toArray(): array
    {
        $data = parent::toArray();
        
        // Add computed fields
        $data['display_name'] = $this->getDisplayName();
        $data['role_label'] = $this->getRoleLabel();
        $data['status_label'] = $this->getStatusLabel();
        $data['is_active'] = $this->isActive();
        $data['is_locked'] = $this->isLocked();
        
        return $data;
    }

    // Relationships
    public function quotes(): array
    {
        return $this->hasMany(Quote::class, 'created_by');
    }

    public function salesOrders(): array
    {
        return $this->hasMany(SalesOrder::class, 'created_by');
    }

    public function invoices(): array
    {
        return $this->hasMany(Invoice::class, 'created_by');
    }
}