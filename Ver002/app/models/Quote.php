<?php

/**
 * File: app/models/Quote.php
 * Purpose: Quote model for quotation management
 * Depends on: Model base class, Client, User, QuoteItem
 * Notes: Handles quote workflow, pricing, conversion to sales orders
 */

namespace App\Models;

use App\Core\Model;

class Quote extends Model
{
    protected static string $table = 'sp_quotes';
    
    protected static array $fillable = [
        'quote_number',
        'client_id',
        'quote_date',
        'valid_until',
        'subtotal',
        'tax_rate',
        'tax_amount',
        'discount_percentage',
        'discount_amount',
        'total_amount',
        'notes',
        'terms_conditions',
        'status',
        'created_by',
        'sales_order_id'
    ];

    protected static array $casts = [
        'id' => 'int',
        'client_id' => 'int',
        'subtotal' => 'float',
        'tax_rate' => 'float',
        'tax_amount' => 'float',
        'discount_percentage' => 'float',
        'discount_amount' => 'float',
        'total_amount' => 'float',
        'status' => 'int',
        'created_by' => 'int',
        'sales_order_id' => 'int',
        'quote_date' => 'date',
        'valid_until' => 'date'
    ];

    public const STATUS_DRAFT = 0;
    public const STATUS_SENT = 1;
    public const STATUS_ACCEPTED = 2;
    public const STATUS_REJECTED = 3;
    public const STATUS_EXPIRED = 4;
    public const STATUS_CONVERTED = 5;

    public static function generateQuoteNumber(): string
    {
        $prefix = 'QT';
        $year = date('Y');
        $lastQuote = self::where('quote_number', 'LIKE', "{$prefix}{$year}%")
                          ->orderBy('quote_number', 'DESC')
                          ->first();
        
        if ($lastQuote) {
            $lastNumber = intval(substr($lastQuote->quote_number, -4));
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }
        
        return $prefix . $year . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
    }

    public function isDraft(): bool
    {
        return $this->status === self::STATUS_DRAFT;
    }

    public function isSent(): bool
    {
        return $this->status === self::STATUS_SENT;
    }

    public function isAccepted(): bool
    {
        return $this->status === self::STATUS_ACCEPTED;
    }

    public function isRejected(): bool
    {
        return $this->status === self::STATUS_REJECTED;
    }

    public function isExpired(): bool
    {
        return $this->status === self::STATUS_EXPIRED || 
               ($this->valid_until && strtotime($this->valid_until) < time());
    }

    public function isConverted(): bool
    {
        return $this->status === self::STATUS_CONVERTED;
    }

    public function canBeEdited(): bool
    {
        return $this->isDraft();
    }

    public function canBeConverted(): bool
    {
        return $this->isAccepted() && !$this->isConverted();
    }

    public function markAsSent(): bool
    {
        if (!$this->isDraft()) {
            return false;
        }
        
        $this->status = self::STATUS_SENT;
        return $this->save();
    }

    public function markAsAccepted(): bool
    {
        if (!$this->isSent()) {
            return false;
        }
        
        $this->status = self::STATUS_ACCEPTED;
        return $this->save();
    }

    public function markAsRejected(): bool
    {
        if (!$this->isSent()) {
            return false;
        }
        
        $this->status = self::STATUS_REJECTED;
        return $this->save();
    }

    public function convertToSalesOrder(): ?SalesOrder
    {
        if (!$this->canBeConverted()) {
            return null;
        }

        $salesOrder = SalesOrder::create([
            'order_number' => SalesOrder::generateOrderNumber(),
            'client_id' => $this->client_id,
            'order_date' => date('Y-m-d'),
            'subtotal' => $this->subtotal,
            'tax_rate' => $this->tax_rate,
            'tax_amount' => $this->tax_amount,
            'discount_percentage' => $this->discount_percentage,
            'discount_amount' => $this->discount_amount,
            'total_amount' => $this->total_amount,
            'notes' => $this->notes,
            'terms_conditions' => $this->terms_conditions,
            'status' => SalesOrder::STATUS_CONFIRMED,
            'created_by' => $_SESSION['user']['id'] ?? $this->created_by,
            'quote_id' => $this->id
        ]);

        if ($salesOrder) {
            // Copy quote items to sales order items
            foreach ($this->items() as $quoteItem) {
                SalesOrderItem::create([
                    'sales_order_id' => $salesOrder->id,
                    'product_id' => $quoteItem->product_id,
                    'quantity' => $quoteItem->quantity,
                    'unit_price' => $quoteItem->unit_price,
                    'line_total' => $quoteItem->line_total,
                    'notes' => $quoteItem->notes
                ]);
            }

            // Mark quote as converted
            $this->status = self::STATUS_CONVERTED;
            $this->sales_order_id = $salesOrder->id;
            $this->save();

            return $salesOrder;
        }

        return null;
    }

    public function recalculateTotals(): bool
    {
        $items = $this->items();
        $subtotal = 0;

        foreach ($items as $item) {
            $subtotal += $item->line_total;
        }

        $this->subtotal = $subtotal;
        
        // Apply discount
        if ($this->discount_percentage > 0) {
            $this->discount_amount = ($subtotal * $this->discount_percentage) / 100;
        } else {
            $this->discount_amount = 0;
        }
        
        $afterDiscount = $subtotal - $this->discount_amount;
        
        // Apply tax
        if ($this->tax_rate > 0) {
            $this->tax_amount = ($afterDiscount * $this->tax_rate) / 100;
        } else {
            $this->tax_amount = 0;
        }
        
        $this->total_amount = $afterDiscount + $this->tax_amount;
        
        return $this->save();
    }

    public function getStatusLabel(): string
    {
        $statuses = [
            self::STATUS_DRAFT => 'Draft',
            self::STATUS_SENT => 'Sent',
            self::STATUS_ACCEPTED => 'Accepted',
            self::STATUS_REJECTED => 'Rejected',
            self::STATUS_EXPIRED => 'Expired',
            self::STATUS_CONVERTED => 'Converted'
        ];
        
        return $statuses[$this->status] ?? 'Unknown';
    }

    public function getStatusClass(): string
    {
        $classes = [
            self::STATUS_DRAFT => 'secondary',
            self::STATUS_SENT => 'info',
            self::STATUS_ACCEPTED => 'success',
            self::STATUS_REJECTED => 'danger',
            self::STATUS_EXPIRED => 'warning',
            self::STATUS_CONVERTED => 'primary'
        ];
        
        return $classes[$this->status] ?? 'secondary';
    }

    public function getFormattedTotal(): string
    {
        return number_format($this->total_amount, 2);
    }

    public function getFormattedSubtotal(): string
    {
        return number_format($this->subtotal, 2);
    }

    public function getFormattedTax(): string
    {
        return number_format($this->tax_amount, 2);
    }

    public function getFormattedDiscount(): string
    {
        return number_format($this->discount_amount, 2);
    }

    public function toArray(): array
    {
        $data = parent::toArray();
        
        // Add computed fields
        $data['status_label'] = $this->getStatusLabel();
        $data['status_class'] = $this->getStatusClass();
        $data['formatted_total'] = $this->getFormattedTotal();
        $data['formatted_subtotal'] = $this->getFormattedSubtotal();
        $data['formatted_tax'] = $this->getFormattedTax();
        $data['formatted_discount'] = $this->getFormattedDiscount();
        $data['is_draft'] = $this->isDraft();
        $data['is_sent'] = $this->isSent();
        $data['is_accepted'] = $this->isAccepted();
        $data['is_rejected'] = $this->isRejected();
        $data['is_expired'] = $this->isExpired();
        $data['is_converted'] = $this->isConverted();
        $data['can_be_edited'] = $this->canBeEdited();
        $data['can_be_converted'] = $this->canBeConverted();
        
        return $data;
    }

    // Relationships
    public function client(): ?Client
    {
        return $this->belongsTo(Client::class);
    }

    public function user(): ?User
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function items(): array
    {
        return $this->hasMany(QuoteItem::class);
    }

    public function salesOrder(): ?SalesOrder
    {
        return $this->belongsTo(SalesOrder::class, 'sales_order_id');
    }

    // Validation
    public function validate(array $data = null): array
    {
        $errors = [];
        $data = $data ?: $this->attributes;

        // Required fields
        if (empty($data['client_id'])) {
            $errors['client_id'] = 'Client is required';
        }

        if (empty($data['quote_date'])) {
            $errors['quote_date'] = 'Quote date is required';
        }

        if (empty($data['valid_until'])) {
            $errors['valid_until'] = 'Valid until date is required';
        }

        // Date validations
        if (!empty($data['quote_date']) && !empty($data['valid_until'])) {
            if (strtotime($data['valid_until']) < strtotime($data['quote_date'])) {
                $errors['valid_until'] = 'Valid until date must be after quote date';
            }
        }

        // Numeric validations
        if (isset($data['tax_rate']) && ($data['tax_rate'] < 0 || $data['tax_rate'] > 100)) {
            $errors['tax_rate'] = 'Tax rate must be between 0 and 100';
        }

        if (isset($data['discount_percentage']) && ($data['discount_percentage'] < 0 || $data['discount_percentage'] > 100)) {
            $errors['discount_percentage'] = 'Discount percentage must be between 0 and 100';
        }

        return $errors;
    }
}