<?php

/**
 * File: app/models/Client.php
 * Purpose: Client model for customer management
 * Depends on: Model base class
 * Notes: Handles client data, contact information, credit limits
 */

namespace App\Models;

use App\Core\Model;

class Client extends Model
{
    protected static string $table = 'sp_clients';
    
    protected static array $fillable = [
        'id',
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
        'credit_limit',
        'payment_terms',
        'discount_percentage',
        'notes',
        'status',
        'created_at',
        'updated_at'
    ];

    protected static array $casts = [
        'id' => 'int',
        'credit_limit' => 'float',
        'discount_percentage' => 'float',
        'status' => 'int'
    ];

    public const STATUS_INACTIVE = 0;
    public const STATUS_ACTIVE = 1;
    public const STATUS_SUSPENDED = 2;

    public static function findByEmail(string $email): ?self
    {
        return self::where('email', $email)->first();
    }

    public static function getActiveClients(): array
    {
        return self::where('status', self::STATUS_ACTIVE)
                  ->orderBy('company_name')
                  ->get();
    }

    public static function searchClients(string $query): array
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

    public function isSuspended(): bool
    {
        return $this->status === self::STATUS_SUSPENDED;
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
        $statuses = [
            self::STATUS_INACTIVE => 'Inactive',
            self::STATUS_ACTIVE => 'Active',
            self::STATUS_SUSPENDED => 'Suspended'
        ];
        
        return $statuses[$this->status] ?? 'Unknown';
    }

    public function getStatusClass(): string
    {
        $classes = [
            self::STATUS_INACTIVE => 'secondary',
            self::STATUS_ACTIVE => 'success',
            self::STATUS_SUSPENDED => 'danger'
        ];
        
        return $classes[$this->status] ?? 'secondary';
    }

    public function getTotalQuotes(): int
    {
        return count($this->quotes());
    }

    public function getTotalOrders(): int
    {
        return count($this->salesOrders());
    }

    public function getTotalInvoices(): int
    {
        return count($this->invoices());
    }

    public function getTotalSales(): float
    {
        $total = 0;
        foreach ($this->invoices() as $invoice) {
            if ($invoice->status === Invoice::STATUS_PAID || $invoice->status === Invoice::STATUS_PARTIAL) {
                $total += $invoice->total_amount;
            }
        }
        return $total;
    }

    public function getOutstandingBalance(): float
    {
        $total = 0;
        foreach ($this->invoices() as $invoice) {
            if ($invoice->status !== Invoice::STATUS_PAID) {
                $total += $invoice->total_amount - $invoice->paid_amount;
            }
        }
        return $total;
    }

    public function hasExceededCreditLimit(): bool
    {
        if (!$this->credit_limit || $this->credit_limit <= 0) {
            return false;
        }
        
        return $this->getOutstandingBalance() > $this->credit_limit;
    }

    public function getAvailableCredit(): float
    {
        if (!$this->credit_limit || $this->credit_limit <= 0) {
            return 0;
        }
        
        return max(0, $this->credit_limit - $this->getOutstandingBalance());
    }

    public function canPlaceOrder(float $amount): bool
    {
        if ($this->isSuspended()) {
            return false;
        }
        
        if (!$this->credit_limit || $this->credit_limit <= 0) {
            return true; // No credit limit set
        }
        
        return ($this->getOutstandingBalance() + $amount) <= $this->credit_limit;
    }

    public function getFormattedCreditLimit(): string
    {
        return $this->credit_limit ? number_format($this->credit_limit, 2) : 'No Limit';
    }

    public function getFormattedOutstandingBalance(): string
    {
        return number_format($this->getOutstandingBalance(), 2);
    }

    public function getFormattedAvailableCredit(): string
    {
        return number_format($this->getAvailableCredit(), 2);
    }

    public function getFormattedTotalSales(): string
    {
        return number_format($this->getTotalSales(), 2);
    }

    public function toArray(): array
    {
        $data = parent::toArray();
        
        // Add computed fields
        $data['display_name'] = $this->getDisplayName();
        $data['full_address'] = $this->getFullAddress();
        $data['status_label'] = $this->getStatusLabel();
        $data['status_class'] = $this->getStatusClass();
        $data['total_quotes'] = $this->getTotalQuotes();
        $data['total_orders'] = $this->getTotalOrders();
        $data['total_invoices'] = $this->getTotalInvoices();
        $data['total_sales'] = $this->getTotalSales();
        $data['outstanding_balance'] = $this->getOutstandingBalance();
        $data['available_credit'] = $this->getAvailableCredit();
        $data['formatted_credit_limit'] = $this->getFormattedCreditLimit();
        $data['formatted_outstanding_balance'] = $this->getFormattedOutstandingBalance();
        $data['formatted_available_credit'] = $this->getFormattedAvailableCredit();
        $data['formatted_total_sales'] = $this->getFormattedTotalSales();
        $data['is_active'] = $this->isActive();
        $data['is_suspended'] = $this->isSuspended();
        $data['has_exceeded_credit_limit'] = $this->hasExceededCreditLimit();
        
        return $data;
    }

    // Relationships
    public function quotes(): array
    {
        return $this->hasMany(Quote::class);
    }

    public function salesOrders(): array
    {
        return $this->hasMany(SalesOrder::class);
    }

    public function invoices(): array
    {
        return $this->hasMany(Invoice::class);
    }

    public function payments(): array
    {
        return $this->hasMany(Payment::class);
    }

    // Validation
    public function validate(array $data = null): array
    {
        $errors = [];
        $data = $data ?: $this->attributes;

        // Required fields
        if (empty($data['company_name']) && empty($data['contact_person'])) {
            $errors['company_name'] = 'Either company name or contact person is required';
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

        // Numeric validations
        if (isset($data['credit_limit']) && $data['credit_limit'] < 0) {
            $errors['credit_limit'] = 'Credit limit cannot be negative';
        }

        if (isset($data['discount_percentage']) && ($data['discount_percentage'] < 0 || $data['discount_percentage'] > 100)) {
            $errors['discount_percentage'] = 'Discount percentage must be between 0 and 100';
        }

        return $errors;
    }
}