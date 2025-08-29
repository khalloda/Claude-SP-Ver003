<?php

/**
 * File: app/models/InvoiceItem.php
 * Purpose: Invoice line item model for billing details
 * Depends on: Model base class, Invoice, Product models
 * Notes: Handles individual invoice line items with pricing calculations
 */

namespace App\Models;

use App\Core\Model;

class InvoiceItem extends Model
{
    protected static string $table = 'sp_invoice_items';
    
    protected static array $fillable = [
        'invoice_id',
        'product_id',
        'description',
        'quantity',
        'unit_price',
        'line_total',
        'sort_order'
    ];

    protected static array $casts = [
        'id' => 'int',
        'invoice_id' => 'int',
        'product_id' => 'int',
        'quantity' => 'int',
        'unit_price' => 'float',
        'line_total' => 'float',
        'sort_order' => 'int'
    ];

    public function invoice(): ?Invoice
    {
        return Invoice::find($this->invoice_id);
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

    public function getFormattedPrice(): string
    {
        return number_format($this->unit_price, 2);
    }

    public function getFormattedTotal(): string
    {
        return number_format($this->line_total, 2);
    }

    public function toArray(): array
    {
        $data = parent::toArray();
        
        // Add computed fields
        $data['product_name'] = $this->getProductName();
        $data['product_sku'] = $this->getProductSku();
        $data['formatted_price'] = $this->getFormattedPrice();
        $data['formatted_total'] = $this->getFormattedTotal();
        
        return $data;
    }

    // Validation
    public function validate(array $data = null): array
    {
        $errors = [];
        $data = $data ?: $this->attributes;

        // Required fields
        if (empty($data['invoice_id'])) {
            $errors['invoice_id'] = 'Invoice is required';
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