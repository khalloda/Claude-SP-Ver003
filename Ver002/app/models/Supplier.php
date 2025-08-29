<?php

/**
 * File: app/models/Supplier.php
 * Purpose: Supplier model for vendor management
 * Depends on: Model base class
 * Notes: Handles supplier data, contact information, product relationships
 */

namespace App\Models;

use App\Core\Model;

class Supplier extends Model
{
    protected static string $table = 'sp_suppliers';
    
    protected static array $fillable = [
        'company_name',
        'contact_person',
        'email',
        'phone',
        'mobile',
        'address',
        'city',
        'postal_code',
        'country',
        'tax_number',
        'payment_terms',
        'notes',
        'status'
    ];

    protected static array $casts = [
        'id' => 'int',
        'status' => 'int'
    ];

    public const STATUS_INACTIVE = 0;
    public const STATUS_ACTIVE = 1;

    public static function findByEmail(string $email): ?self
    {
        return self::where('email', $email)->first();
    }

    public static function getActiveSuppliers(): array
    {
        return self::where('status', self::STATUS_ACTIVE)
                  ->orderBy('company_name')
                  ->get();
    }

    public static function searchSuppliers(string $query): array
    {
        return self::where('company_name', 'LIKE', "%{$query}%")
                  ->orWhere('contact_person', 'LIKE', "%{$query}%")
                  ->orWhere('email', 'LIKE', "%{$query}%")
                  ->orWhere('phone', 'LIKE', "%{$query}%")
                  ->where('status', self::STATUS_ACTIVE)
                  ->orderBy('company_name')
                  ->get();
    }

    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    public function activate(): bool
    {
        $this->status = self::STATUS_ACTIVE;
        return $this->save();
    }

    public function deactivate(): bool
    {
        $this->status = self::STATUS_INACTIVE;
        return $this->save();
    }

    public function getDisplayName(): string
    {
        if ($this->company_name && $this->contact_person) {
            return "{$this->company_name} ({$this->contact_person})";
        }
        
        return $this->company_name ?: $this->contact_person ?: $this->email;
    }

    public function getFullAddress(): string
    {
        $parts = array_filter([
            $this->address,
            $this->city,
            $this->postal_code,
            $this->country
        ]);
        
        return implode(', ', $parts);
    }

    public function getStatusLabel(): string
    {
        return $this->isActive() ? 'Active' : 'Inactive';
    }

    public function getStatusClass(): string
    {
        return $this->isActive() ? 'success' : 'secondary';
    }

    public function getTotalProducts(): int
    {
        return count($this->products());
    }

    public function getActiveProducts(): array
    {
        $products = $this->products();
        return array_filter($products, function($product) {
            return $product->isActive();
        });
    }

    public function getTotalStockValue(): float
    {
        $total = 0;
        foreach ($this->products() as $product) {
            $total += $product->getStockValue();
        }
        return $total;
    }

    public function toArray(): array
    {
        $data = parent::toArray();
        
        // Add computed fields
        $data['display_name'] = $this->getDisplayName();
        $data['full_address'] = $this->getFullAddress();
        $data['status_label'] = $this->getStatusLabel();
        $data['status_class'] = $this->getStatusClass();
        $data['total_products'] = $this->getTotalProducts();
        $data['total_stock_value'] = $this->getTotalStockValue();
        $data['is_active'] = $this->isActive();
        
        return $data;
    }

    // Relationships
    public function products(): array
    {
        return $this->hasMany(Product::class);
    }

    // Validation
    public function validate(array $data = null): array
    {
        $errors = [];
        $data = $data ?: $this->attributes;

        // Required fields
        if (empty($data['company_name'])) {
            $errors['company_name'] = 'Company name is required';
        }

        // Email validation
        if (!empty($data['email']) && !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Please enter a valid email address';
        }

        // Check email uniqueness
        if (!empty($data['email'])) {
            $existing = self::where('email', $data['email'])->first();
            if ($existing && $existing->id !== $this->id) {
                $errors['email'] = 'Email address already exists';
            }
        }

        return $errors;
    }
}