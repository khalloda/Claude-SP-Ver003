<?php

/**
 * File: app/models/SalesOrderItem.php
 * Purpose: Sales order line item model with stock reservation
 * Depends on: Model base class, SalesOrder, Product models
 * Notes: Handles order line items with inventory allocation
 */

namespace App\Models;

use App\Core\Model;

class SalesOrderItem extends Model
{
    protected static string $table = 'sp_sales_order_items';
    
    protected static array $fillable = [
        'sales_order_id',
        'product_id',
        'description',
        'quantity',
        'unit_price',
        'line_total',
        'reserved_quantity',
        'shipped_quantity',
        'sort_order'
    ];

    protected static array $casts = [
        'id' => 'int',
        'sales_order_id' => 'int',
        'product_id' => 'int',
        'quantity' => 'int',
        'unit_price' => 'float',
        'line_total' => 'float',
        'reserved_quantity' => 'int',
        'shipped_quantity' => 'int',
        'sort_order' => 'int'
    ];

    public function salesOrder(): ?SalesOrder
    {
        return SalesOrder::find($this->sales_order_id);
    }

    public function product(): ?Product
    {
        return $this->product_id ? Product::find($this->product_id) : null;
    }

    public function calculateTotal(): void
    {
        $this->line_total = $this->quantity * $this->unit_price;
    }

    public function getProductName(): string
    {
        $product = $this->product();
        return $product ? $product->name : 'N/A';
    }

    public function getProductSku(): string
    {
        $product = $this->product();
        return $product ? $product->sku : 'N/A';
    }

    public function isFullyReserved(): bool
    {
        return $this->reserved_quantity >= $this->quantity;
    }

    public function isFullyShipped(): bool
    {
        return $this->shipped_quantity >= $this->quantity;
    }

    public function getRemainingQuantity(): int
    {
        return max(0, $this->quantity - $this->shipped_quantity);
    }

    public function getUnreservedQuantity(): int
    {
        return max(0, $this->quantity - $this->reserved_quantity);
    }

    public function canReserveStock(): bool
    {
        if ($this->isFullyReserved()) {
            return false;
        }
        
        $product = $this->product();
        if (!$product) {
            return false;
        }
        
        $unreserved = $this->getUnreservedQuantity();
        return $product->stock_quantity >= $unreserved;
    }

    public function reserveStock(): bool
    {
        if (!$this->canReserveStock()) {
            return false;
        }
        
        $product = $this->product();
        $unreserved = $this->getUnreservedQuantity();
        
        if ($product->reserveStock($unreserved, "Reserved for order item")) {
            $this->reserved_quantity += $unreserved;
            return $this->save();
        }
        
        return false;
    }

    public function releaseStock(): bool
    {
        if ($this->reserved_quantity <= 0) {
            return true;
        }
        
        $product = $this->product();
        if (!$product) {
            return false;
        }
        
        if ($product->releaseStock($this->reserved_quantity, "Released from order item")) {
            $this->reserved_quantity = 0;
            return $this->save();
        }
        
        return false;
    }

    public function commitStock(int $quantity = null): bool
    {
        $quantity = $quantity ?? $this->getRemainingQuantity();
        
        if ($quantity <= 0) {
            return true;
        }
        
        $product = $this->product();
        if (!$product) {
            return false;
        }
        
        // Remove from actual stock
        if ($product->removeStock($quantity, "Shipped from order")) {
            $this->shipped_quantity += $quantity;
            $this->reserved_quantity = max(0, $this->reserved_quantity - $quantity);
            return $this->save();
        }
        
        return false;
    }

    public function toArray(): array
    {
        $data = parent::toArray();
        
        // Add computed fields
        $data['product_name'] = $this->getProductName();
        $data['product_sku'] = $this->getProductSku();
        $data['is_fully_reserved'] = $this->isFullyReserved();
        $data['is_fully_shipped'] = $this->isFullyShipped();
        $data['remaining_quantity'] = $this->getRemainingQuantity();
        $data['unreserved_quantity'] = $this->getUnreservedQuantity();
        $data['can_reserve_stock'] = $this->canReserveStock();
        
        return $data;
    }

    // Validation
    public function validate(array $data = null): array
    {
        $errors = [];
        $data = $data ?: $this->attributes;

        // Required fields
        if (empty($data['sales_order_id'])) {
            $errors['sales_order_id'] = 'Sales order is required';
        }

        if (empty($data['description'])) {
            $errors['description'] = 'Description is required';
        }

        if (empty($data['quantity']) || $data['quantity'] <= 0) {
            $errors['quantity'] = 'Quantity must be greater than zero';
        }

        if (empty($data['unit_price']) || $data['unit_price'] <= 0) {
            $errors['unit_price'] = 'Unit price must be greater than zero';
        }

        // Validate product if provided
        if (!empty($data['product_id'])) {
            $product = Product::find($data['product_id']);
            if (!$product) {
                $errors['product_id'] = 'Invalid product selected';
            }
        }

        // Validate quantities
        if (!empty($data['reserved_quantity']) && !empty($data['quantity'])) {
            if ($data['reserved_quantity'] > $data['quantity']) {
                $errors['reserved_quantity'] = 'Reserved quantity cannot exceed total quantity';
            }
        }

        if (!empty($data['shipped_quantity']) && !empty($data['quantity'])) {
            if ($data['shipped_quantity'] > $data['quantity']) {
                $errors['shipped_quantity'] = 'Shipped quantity cannot exceed total quantity';
            }
        }

        return $errors;
    }

    // Lifecycle hooks
    public function beforeSave(): void
    {
        $this->calculateTotal();
        parent::beforeSave();
    }
}