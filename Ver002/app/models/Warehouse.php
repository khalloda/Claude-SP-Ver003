<?php

/**
 * File: app/models/Warehouse.php
 * Purpose: Warehouse model for location and inventory management
 * Depends on: Model base class, Product models
 * Notes: Handles warehouse operations, stock allocation, capacity tracking
 */

namespace App\Models;

use App\Core\Model;

class Warehouse extends Model
{
    protected static string $table = 'sp_warehouses';
    
    protected static array $fillable = [
        'name',
        'code',
        'description',
        'location',
        'address',
        'city',
        'postal_code',
        'country',
        'manager_name',
        'contact_phone',
        'contact_email',
        'capacity',
        'capacity_unit',
        'temperature_controlled',
        'security_level',
        'operating_hours',
        'notes',
        'status'
    ];

    protected static array $casts = [
        'id' => 'int',
        'capacity' => 'float',
        'temperature_controlled' => 'int',
        'status' => 'int'
    ];

    public const STATUS_ACTIVE = 1;
    public const STATUS_INACTIVE = 0;

    public const SECURITY_BASIC = 'basic';
    public const SECURITY_STANDARD = 'standard';
    public const SECURITY_HIGH = 'high';
    public const SECURITY_MAXIMUM = 'maximum';

    public static function findByCode(string $code): ?self
    {
        return self::where('code', $code)->first();
    }

    public static function getActiveWarehouses(): array
    {
        return self::where('status', self::STATUS_ACTIVE)->orderBy('name')->get();
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

    public function getProducts(): array
    {
        // This would need proper warehouse-product relationship
        // For now, return all products (simplified implementation)
        return Product::all();
    }

    public function getProductStock(int $productId): int
    {
        // This would check warehouse-specific stock levels
        // For now, return product's total stock (simplified implementation)
        $product = Product::find($productId);
        return $product ? $product->stock_quantity : 0;
    }

    public function hasProduct(int $productId): bool
    {
        return $this->getProductStock($productId) > 0;
    }

    public function addProductStock(int $productId, int $quantity, string $reason = ''): bool
    {
        $product = Product::find($productId);
        if (!$product) {
            return false;
        }

        // Record stock movement
        StockMovement::recordMovement(
            $productId,
            StockMovement::TYPE_IN,
            $quantity,
            $product->stock_quantity,
            $product->stock_quantity + $quantity,
            $reason ?: "Stock added to warehouse {$this->name}",
            StockMovement::REF_MANUAL,
            $this->id,
            $this->code
        );

        return $product->addStock($quantity, $reason);
    }

    public function removeProductStock(int $productId, int $quantity, string $reason = ''): bool
    {
        $product = Product::find($productId);
        if (!$product) {
            return false;
        }

        if ($product->stock_quantity < $quantity) {
            return false;
        }

        // Record stock movement
        StockMovement::recordMovement(
            $productId,
            StockMovement::TYPE_OUT,
            -$quantity,
            $product->stock_quantity,
            $product->stock_quantity - $quantity,
            $reason ?: "Stock removed from warehouse {$this->name}",
            StockMovement::REF_MANUAL,
            $this->id,
            $this->code
        );

        return $product->removeStock($quantity, $reason);
    }

    public function transferStock(Warehouse $toWarehouse, Product $product, int $quantity, string $notes = '', int $userId = null): ?StockTransfer
    {
        if (!$this->hasProduct($product->id) || $this->getProductStock($product->id) < $quantity) {
            return null;
        }

        // Create transfer record (simplified - would need StockTransfer model)
        $transferData = [
            'from_warehouse_id' => $this->id,
            'to_warehouse_id' => $toWarehouse->id,
            'product_id' => $product->id,
            'quantity' => $quantity,
            'notes' => $notes,
            'status' => 'completed',
            'transferred_by' => $userId ?: ($_SESSION['user']['id'] ?? 1),
            'transferred_at' => date('Y-m-d H:i:s')
        ];

        // Record outbound movement from source warehouse
        StockMovement::recordMovement(
            $product->id,
            StockMovement::TYPE_TRANSFER,
            -$quantity,
            $this->getProductStock($product->id),
            $this->getProductStock($product->id) - $quantity,
            "Transfer to {$toWarehouse->name}",
            StockMovement::REF_TRANSFER,
            $this->id,
            "TRANS-{$this->code}-{$toWarehouse->code}"
        );

        // Record inbound movement to destination warehouse
        StockMovement::recordMovement(
            $product->id,
            StockMovement::TYPE_TRANSFER,
            $quantity,
            $toWarehouse->getProductStock($product->id),
            $toWarehouse->getProductStock($product->id) + $quantity,
            "Transfer from {$this->name}",
            StockMovement::REF_TRANSFER,
            $toWarehouse->id,
            "TRANS-{$this->code}-{$toWarehouse->code}"
        );

        // Return mock transfer object
        return (object)$transferData;
    }

    public function getCapacityUsed(): float
    {
        // This would calculate actual capacity usage
        // For now, return a placeholder value
        return 0.0;
    }

    public function getCapacityPercentage(): float
    {
        if (!$this->capacity || $this->capacity <= 0) {
            return 0.0;
        }

        $used = $this->getCapacityUsed();
        return ($used / $this->capacity) * 100;
    }

    public function isAtCapacity(): bool
    {
        return $this->getCapacityPercentage() >= 100;
    }

    public function isNearCapacity(float $threshold = 90.0): bool
    {
        return $this->getCapacityPercentage() >= $threshold;
    }

    public function getStatusLabel(): string
    {
        return $this->isActive() ? 'Active' : 'Inactive';
    }

    public function getStatusClass(): string
    {
        return $this->isActive() ? 'success' : 'secondary';
    }

    public function getSecurityLevelLabel(): string
    {
        switch ($this->security_level) {
            case self::SECURITY_BASIC:
                return 'Basic';
            case self::SECURITY_STANDARD:
                return 'Standard';
            case self::SECURITY_HIGH:
                return 'High';
            case self::SECURITY_MAXIMUM:
                return 'Maximum';
            default:
                return ucfirst($this->security_level ?: 'Standard');
        }
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

    public static function getSecurityLevels(): array
    {
        return [
            self::SECURITY_BASIC => 'Basic',
            self::SECURITY_STANDARD => 'Standard',
            self::SECURITY_HIGH => 'High',
            self::SECURITY_MAXIMUM => 'Maximum'
        ];
    }

    public function toArray(): array
    {
        $data = parent::toArray();
        
        // Add computed fields
        $data['is_active'] = $this->isActive();
        $data['status_label'] = $this->getStatusLabel();
        $data['status_class'] = $this->getStatusClass();
        $data['security_level_label'] = $this->getSecurityLevelLabel();
        $data['full_address'] = $this->getFullAddress();
        $data['capacity_used'] = $this->getCapacityUsed();
        $data['capacity_percentage'] = $this->getCapacityPercentage();
        $data['is_at_capacity'] = $this->isAtCapacity();
        $data['is_near_capacity'] = $this->isNearCapacity();
        $data['total_products'] = count($this->getProducts());
        
        return $data;
    }

    // Validation
    public function validate(array $data = null): array
    {
        $errors = [];
        $data = $data ?: $this->attributes;

        // Required fields
        if (empty($data['name'])) {
            $errors['name'] = 'Warehouse name is required';
        }

        if (empty($data['code'])) {
            $errors['code'] = 'Warehouse code is required';
        }

        // Check code uniqueness
        if (!empty($data['code'])) {
            $existing = self::where('code', $data['code'])->first();
            if ($existing && $existing->id !== ($this->id ?? 0)) {
                $errors['code'] = 'Warehouse code already exists';
            }
        }

        // Validate email format
        if (!empty($data['contact_email']) && !filter_var($data['contact_email'], FILTER_VALIDATE_EMAIL)) {
            $errors['contact_email'] = 'Please enter a valid email address';
        }

        // Validate capacity
        if (!empty($data['capacity']) && $data['capacity'] <= 0) {
            $errors['capacity'] = 'Capacity must be greater than zero';
        }

        // Validate security level
        if (!empty($data['security_level'])) {
            $validLevels = array_keys(self::getSecurityLevels());
            if (!in_array($data['security_level'], $validLevels)) {
                $errors['security_level'] = 'Invalid security level';
            }
        }

        return $errors;
    }
}