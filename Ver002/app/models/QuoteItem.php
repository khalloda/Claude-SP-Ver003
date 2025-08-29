<?php

/**
 * File: app/models/QuoteItem.php
 * Purpose: Quote line item model for quote details
 * Depends on: Model base class, Quote, Product models
 * Notes: Handles individual quote line items with pricing and calculations
 */

namespace App\Models;

use App\Core\Model;

class QuoteItem extends Model
{
    protected static string $table = 'sp_quote_items';
    
    protected static array $fillable = [
        'quote_id',
        'product_id',
        'description',
        'quantity',
        'unit_price',
        'line_total',
        'sort_order'
    ];

    protected static array $casts = [
        'id' => 'int',
        'quote_id' => 'int',
        'product_id' => 'int',
        'quantity' => 'int',
        'unit_price' => 'float',
        'line_total' => 'float',
        'sort_order' => 'int'
    ];

    public function quote(): ?Quote
    {
        return Quote::find($this->quote_id);
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

    public function isProductAvailable(): bool
    {
        $product = $this->product();
        return $product ? $product->stock_quantity >= $this->quantity : false;
    }

    public function getStockShortfall(): int
    {
        $product = $this->product();
        if (!$product) {
            return $this->quantity;
        }
        
        return max(0, $this->quantity - $product->stock_quantity);
    }

    public function toArray(): array
    {
        $data = parent::toArray();
        
        // Add computed fields
        $data['product_name'] = $this->getProductName();
        $data['product_sku'] = $this->getProductSku();
        $data['is_product_available'] = $this->isProductAvailable();
        $data['stock_shortfall'] = $this->getStockShortfall();
        
        return $data;
    }

    // Validation
    public function validate(array $data = null): array
    {
        $errors = [];
        $data = $data ?: $this->attributes;

        // Required fields
        if (empty($data['quote_id'])) {
            $errors['quote_id'] = 'Quote is required';
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

        return $errors;
    }

    // Lifecycle hooks
    public function beforeSave(): void
    {
        $this->calculateTotal();
        parent::beforeSave();
    }
}