<?php

/**
 * File: app/models/Product.php
 * Purpose: Product model with inventory tracking and stock management
 * Depends on: Model base class, Dropdown, Supplier
 * Notes: Handles product data, stock levels, pricing, relationships
 */

namespace App\Models;

use App\Core\Model;

class Product extends Model
{
    protected static string $table = 'sp_products';
    
    protected static array $fillable = [
        'id',
        'name',
        'description', 
        'sku',
        'part_number',
        'category_id',
        'supplier_id',
        'purchase_price',
        'selling_price',
        'markup_percentage',
        'stock_quantity',
        'min_stock_level',
        'unit_of_measure',
        'weight',
        'dimensions',
        'location',
        'brand',
        'model',
        'status',
        'created_at',
        'updated_at'
    ];

    protected static array $casts = [
        'id' => 'int',
        'category_id' => 'int',
        'supplier_id' => 'int',
        'purchase_price' => 'float',
        'selling_price' => 'float',
        'markup_percentage' => 'float',
        'stock_quantity' => 'int',
        'min_stock_level' => 'int',
        'weight' => 'float',
        'status' => 'int'
    ];

    public const STATUS_INACTIVE = 0;
    public const STATUS_ACTIVE = 1;
    public const STATUS_DISCONTINUED = 2;

    public static function findBySku(string $sku): ?self
    {
        return self::where('sku', $sku)->first();
    }

    public static function findByPartNumber(string $partNumber): ?self
    {
        return self::where('part_number', $partNumber)->first();
    }

    public static function getActiveProducts(): array
    {
        return self::where('status', self::STATUS_ACTIVE)
                  ->orderBy('name')
                  ->get();
    }

    public static function getLowStockProducts(): array
    {
        return self::where('status', self::STATUS_ACTIVE)
                  ->whereRaw('stock_quantity <= min_stock_level')
                  ->orderBy('stock_quantity')
                  ->get();
    }

    public static function searchProducts(string $query): array
    {
        return self::where('name', 'LIKE', "%{$query}%")
                  ->orWhere('sku', 'LIKE', "%{$query}%")
                  ->orWhere('part_number', 'LIKE', "%{$query}%")
                  ->where('status', self::STATUS_ACTIVE)
                  ->orderBy('name')
                  ->get();
    }

    public static function getProductsByCategory(int $categoryId): array
    {
        return self::where('category_id', $categoryId)
                  ->where('status', self::STATUS_ACTIVE)
                  ->orderBy('name')
                  ->get();
    }

    public static function getProductsBySupplier(int $supplierId): array
    {
        return self::where('supplier_id', $supplierId)
                  ->where('status', self::STATUS_ACTIVE)
                  ->orderBy('name')
                  ->get();
    }

    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    public function isLowStock(): bool
    {
        return $this->stock_quantity <= $this->min_stock_level;
    }

    public function isOutOfStock(): bool
    {
        return $this->stock_quantity <= 0;
    }

    public function isDiscontinued(): bool
    {
        return $this->status === self::STATUS_DISCONTINUED;
    }

    public function hasStock(int $quantity = 1): bool
    {
        return $this->stock_quantity >= $quantity;
    }

    public function addStock(int $quantity, string $reason = 'Manual adjustment'): bool
    {
        if ($quantity <= 0) {
            return false;
        }

        $oldQuantity = $this->stock_quantity;
        $this->stock_quantity += $quantity;
        
        if ($this->save()) {
            $this->logStockMovement($quantity, 'in', $reason, $oldQuantity, $this->stock_quantity);
            return true;
        }
        
        return false;
    }

    public function removeStock(int $quantity, string $reason = 'Manual adjustment'): bool
    {
        if ($quantity <= 0 || $this->stock_quantity < $quantity) {
            return false;
        }

        $oldQuantity = $this->stock_quantity;
        $this->stock_quantity -= $quantity;
        
        if ($this->save()) {
            $this->logStockMovement($quantity, 'out', $reason, $oldQuantity, $this->stock_quantity);
            return true;
        }
        
        return false;
    }

    public function reserveStock(int $quantity, string $reference = null): bool
    {
        if (!$this->hasStock($quantity)) {
            return false;
        }

        return $this->removeStock($quantity, 'Reserved for order: ' . ($reference ?: 'Unknown'));
    }

    public function releaseStock(int $quantity, string $reference = null): bool
    {
        return $this->addStock($quantity, 'Released from order: ' . ($reference ?: 'Unknown'));
    }

    public function updatePricing(float $purchasePrice, float $sellingPrice = null, float $markup = null): bool
    {
        $this->purchase_price = $purchasePrice;
        
        if ($sellingPrice !== null) {
            $this->selling_price = $sellingPrice;
            $this->markup_percentage = $purchasePrice > 0 ? (($sellingPrice - $purchasePrice) / $purchasePrice) * 100 : 0;
        } elseif ($markup !== null) {
            $this->markup_percentage = $markup;
            $this->selling_price = $purchasePrice * (1 + $markup / 100);
        }
        
        return $this->save();
    }

    public function calculateMarkup(): float
    {
        if ($this->purchase_price <= 0) {
            return 0;
        }
        
        return (($this->selling_price - $this->purchase_price) / $this->purchase_price) * 100;
    }

    public function getStockValue(): float
    {
        return $this->stock_quantity * $this->purchase_price;
    }

    public function getDisplayName(): string
    {
        $name = $this->name;
        
        if ($this->sku) {
            $name .= " ({$this->sku})";
        }
        
        if ($this->part_number && $this->part_number !== $this->sku) {
            $name .= " - {$this->part_number}";
        }
        
        return $name;
    }

    public function getFormattedPrice(): string
    {
        return number_format($this->selling_price, 2);
    }

    public function getFormattedCost(): string
    {
        return number_format($this->purchase_price, 2);
    }

    public function getStatusLabel(): string
    {
        $statuses = [
            self::STATUS_INACTIVE => 'Inactive',
            self::STATUS_ACTIVE => 'Active',
            self::STATUS_DISCONTINUED => 'Discontinued'
        ];
        
        return $statuses[$this->status] ?? 'Unknown';
    }

    public function getStockStatusClass(): string
    {
        if ($this->isOutOfStock()) {
            return 'danger';
        } elseif ($this->isLowStock()) {
            return 'warning';
        }
        
        return 'success';
    }

    public function getStockStatusLabel(): string
    {
        if ($this->isOutOfStock()) {
            return 'Out of Stock';
        } elseif ($this->isLowStock()) {
            return 'Low Stock';
        }
        
        return 'In Stock';
    }

    private function logStockMovement(int $quantity, string $type, string $reason, int $oldQuantity, int $newQuantity): void
    {
        try {
            StockMovement::create([
                'product_id' => $this->id,
                'movement_type' => $type,
                'quantity' => $quantity,
                'old_quantity' => $oldQuantity,
                'new_quantity' => $newQuantity,
                'reason' => $reason,
                'created_by' => $_SESSION['user']['id'] ?? null
            ]);
        } catch (\Exception $e) {
            error_log("Failed to log stock movement: " . $e->getMessage());
        }
    }

    public function toArray(): array
    {
        $data = parent::toArray();
        
        // Add computed fields
        $data['display_name'] = $this->getDisplayName();
        $data['formatted_price'] = $this->getFormattedPrice();
        $data['formatted_cost'] = $this->getFormattedCost();
        $data['status_label'] = $this->getStatusLabel();
        $data['stock_status_label'] = $this->getStockStatusLabel();
        $data['stock_status_class'] = $this->getStockStatusClass();
        $data['stock_value'] = $this->getStockValue();
        $data['is_active'] = $this->isActive();
        $data['is_low_stock'] = $this->isLowStock();
        $data['is_out_of_stock'] = $this->isOutOfStock();
        $data['markup_percentage'] = $this->calculateMarkup();
        
        return $data;
    }

    // Relationships
    public function category(): ?Dropdown
    {
        return $this->belongsTo(Dropdown::class, 'category_id');
    }

    public function supplier(): ?Supplier
    {
        return $this->belongsTo(Supplier::class);
    }

    public function stockMovements(): array
    {
        return $this->hasMany(StockMovement::class);
    }

    public function quoteItems(): array
    {
        return $this->hasMany(QuoteItem::class);
    }

    public function salesOrderItems(): array
    {
        return $this->hasMany(SalesOrderItem::class);
    }

    public function invoiceItems(): array
    {
        return $this->hasMany(InvoiceItem::class);
    }

    // Validation
    public function validate(array $data = null): array
    {
        $errors = [];
        $data = $data ?: $this->attributes;

        // Required fields
        if (empty($data['name'])) {
            $errors['name'] = 'Product name is required';
        }

        if (empty($data['sku'])) {
            $errors['sku'] = 'SKU is required';
        } else {
            // Check SKU uniqueness
            $existing = self::where('sku', $data['sku'])->first();
            if ($existing && $existing->id !== $this->id) {
                $errors['sku'] = 'SKU already exists';
            }
        }

        // Numeric validations
        if (isset($data['purchase_price']) && $data['purchase_price'] < 0) {
            $errors['purchase_price'] = 'Purchase price cannot be negative';
        }

        if (isset($data['selling_price']) && $data['selling_price'] < 0) {
            $errors['selling_price'] = 'Selling price cannot be negative';
        }

        if (isset($data['stock_quantity']) && $data['stock_quantity'] < 0) {
            $errors['stock_quantity'] = 'Stock quantity cannot be negative';
        }

        if (isset($data['min_stock_level']) && $data['min_stock_level'] < 0) {
            $errors['min_stock_level'] = 'Minimum stock level cannot be negative';
        }

        return $errors;
    }
}