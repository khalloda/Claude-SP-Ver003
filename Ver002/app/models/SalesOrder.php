<?php

/**
 * File: app/models/SalesOrder.php
 * Purpose: Sales Order model with workflow and business logic
 * Depends on: Model base class, Client, Product, SalesOrderItem models
 * Notes: Handles order lifecycle, stock allocation, invoicing workflow
 */

namespace App\Models;

use App\Core\Model;

class SalesOrder extends Model
{
    protected static string $table = 'sp_sales_orders';
    
    protected static array $fillable = [
        'order_number',
        'client_id',
        'quote_id',
        'order_date',
        'delivery_date',
        'shipping_address',
        'subtotal',
        'tax_rate',
        'tax_amount',
        'discount_percentage',
        'discount_amount',
        'total_amount',
        'notes',
        'terms_conditions',
        'status',
        'tracking_number',
        'shipped_date',
        'delivered_date',
        'created_by'
    ];

    protected static array $casts = [
        'id' => 'int',
        'client_id' => 'int',
        'quote_id' => 'int',
        'subtotal' => 'float',
        'tax_rate' => 'float',
        'tax_amount' => 'float',
        'discount_percentage' => 'float',
        'discount_amount' => 'float',
        'total_amount' => 'float',
        'status' => 'int',
        'created_by' => 'int'
    ];

    public const STATUS_PENDING = 1;
    public const STATUS_PROCESSING = 2;
    public const STATUS_SHIPPED = 3;
    public const STATUS_DELIVERED = 4;
    public const STATUS_CANCELLED = 5;

    public static function generateOrderNumber(): string
    {
        $year = date('Y');
        $month = date('m');
        
        // Get the last order number for this month
        $lastOrder = self::where('order_number', 'LIKE', "SO-{$year}{$month}%")
                          ->orderBy('id', 'DESC')
                          ->first();
        
        if ($lastOrder) {
            $lastNumber = (int)substr($lastOrder->order_number, -4);
            $nextNumber = $lastNumber + 1;
        } else {
            $nextNumber = 1;
        }
        
        return sprintf('SO-%s%s%04d', $year, $month, $nextNumber);
    }

    public function client(): ?Client
    {
        return Client::find($this->client_id);
    }

    public function quote(): ?Quote
    {
        return $this->quote_id ? Quote::find($this->quote_id) : null;
    }

    public function items(): array
    {
        return SalesOrderItem::where('sales_order_id', $this->id)->get();
    }

    public function invoices(): array
    {
        return Invoice::where('sales_order_id', $this->id)->get();
    }

    public function canBeEdited(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function canBeCancelled(): bool
    {
        return in_array($this->status, [self::STATUS_PENDING, self::STATUS_PROCESSING]);
    }

    public function canBeShipped(): bool
    {
        return $this->status === self::STATUS_PROCESSING;
    }

    public function canBeDelivered(): bool
    {
        return $this->status === self::STATUS_SHIPPED;
    }

    public function hasInvoices(): bool
    {
        $invoices = $this->invoices();
        return !empty($invoices);
    }

    public function isFullyInvoiced(): bool
    {
        $invoices = $this->invoices();
        $invoicedAmount = 0;
        
        foreach ($invoices as $invoice) {
            if ($invoice->status !== Invoice::STATUS_CANCELLED) {
                $invoicedAmount += $invoice->total_amount;
            }
        }
        
        return abs($invoicedAmount - $this->total_amount) < 0.01;
    }

    public function markAsProcessing(): bool
    {
        if ($this->status !== self::STATUS_PENDING) {
            return false;
        }

        $this->status = self::STATUS_PROCESSING;
        return $this->save();
    }

    public function markAsShipped(string $trackingNumber = ''): bool
    {
        if ($this->status !== self::STATUS_PROCESSING) {
            return false;
        }

        $this->status = self::STATUS_SHIPPED;
        $this->tracking_number = $trackingNumber;
        $this->shipped_date = date('Y-m-d H:i:s');
        
        // Reduce actual stock quantities
        $this->commitStockReservations();
        
        return $this->save();
    }

    public function markAsDelivered(): bool
    {
        if ($this->status !== self::STATUS_SHIPPED) {
            return false;
        }

        $this->status = self::STATUS_DELIVERED;
        $this->delivered_date = date('Y-m-d H:i:s');
        return $this->save();
    }

    public function cancel(string $reason = ''): bool
    {
        if (!$this->canBeCancelled()) {
            return false;
        }

        $this->status = self::STATUS_CANCELLED;
        $this->notes = $this->notes . "\nCancelled: " . $reason;
        
        // Release reserved stock
        $this->releaseReservedStock();
        
        return $this->save();
    }

    public function createInvoice(): ?Invoice
    {
        if ($this->status === self::STATUS_CANCELLED) {
            return null;
        }

        // Check if already fully invoiced
        if ($this->isFullyInvoiced()) {
            return null;
        }

        $client = $this->client();
        if (!$client) {
            return null;
        }

        // Create invoice
        $invoiceData = [
            'invoice_number' => Invoice::generateInvoiceNumber(),
            'client_id' => $this->client_id,
            'sales_order_id' => $this->id,
            'invoice_date' => date('Y-m-d'),
            'due_date' => date('Y-m-d', strtotime('+30 days')),
            'subtotal' => $this->subtotal,
            'tax_rate' => $this->tax_rate,
            'tax_amount' => $this->tax_amount,
            'discount_percentage' => $this->discount_percentage,
            'discount_amount' => $this->discount_amount,
            'total_amount' => $this->total_amount,
            'notes' => $this->notes,
            'terms_conditions' => $this->terms_conditions,
            'status' => Invoice::STATUS_DRAFT,
            'created_by' => $this->created_by
        ];

        $invoice = Invoice::create($invoiceData);

        if ($invoice) {
            // Copy order items to invoice items
            $this->copyItemsToInvoice($invoice);
            $invoice->recalculateTotals();
        }

        return $invoice;
    }

    private function copyItemsToInvoice(Invoice $invoice): void
    {
        $items = $this->items();
        
        foreach ($items as $item) {
            InvoiceItem::create([
                'invoice_id' => $invoice->id,
                'product_id' => $item->product_id,
                'description' => $item->description,
                'quantity' => $item->quantity,
                'unit_price' => $item->unit_price,
                'line_total' => $item->line_total
            ]);
        }
    }

    public function recalculateTotals(): void
    {
        $items = $this->items();
        $subtotal = 0;
        
        foreach ($items as $item) {
            $subtotal += $item->line_total;
        }
        
        $this->subtotal = $subtotal;
        $this->discount_amount = $subtotal * ($this->discount_percentage / 100);
        $taxableAmount = $subtotal - $this->discount_amount;
        $this->tax_amount = $taxableAmount * ($this->tax_rate / 100);
        $this->total_amount = $taxableAmount + $this->tax_amount;
        
        $this->save();
    }

    public function reserveStock(): bool
    {
        $items = $this->items();
        
        foreach ($items as $item) {
            $product = Product::find($item->product_id);
            if (!$product) {
                continue;
            }
            
            if (!$product->reserveStock($item->quantity, "Reserved for order {$this->order_number}")) {
                return false;
            }
        }
        
        return true;
    }

    public function releaseReservedStock(): void
    {
        $items = $this->items();
        
        foreach ($items as $item) {
            $product = Product::find($item->product_id);
            if (!$product) {
                continue;
            }
            
            $product->releaseStock($item->quantity, "Released from cancelled order {$this->order_number}");
        }
    }

    public function commitStockReservations(): void
    {
        $items = $this->items();
        
        foreach ($items as $item) {
            $product = Product::find($item->product_id);
            if (!$product) {
                continue;
            }
            
            $product->removeStock($item->quantity, "Shipped with order {$this->order_number}");
        }
    }

    public function getStatusLabel(): string
    {
        switch ($this->status) {
            case self::STATUS_PENDING:
                return 'Pending';
            case self::STATUS_PROCESSING:
                return 'Processing';
            case self::STATUS_SHIPPED:
                return 'Shipped';
            case self::STATUS_DELIVERED:
                return 'Delivered';
            case self::STATUS_CANCELLED:
                return 'Cancelled';
            default:
                return 'Unknown';
        }
    }

    public function getStatusClass(): string
    {
        switch ($this->status) {
            case self::STATUS_PENDING:
                return 'warning';
            case self::STATUS_PROCESSING:
                return 'info';
            case self::STATUS_SHIPPED:
                return 'primary';
            case self::STATUS_DELIVERED:
                return 'success';
            case self::STATUS_CANCELLED:
                return 'danger';
            default:
                return 'secondary';
        }
    }

    public static function getUninvoicedOrders(): array
    {
        // This would need proper SQL to check for orders that aren't fully invoiced
        // For now, return a simple implementation
        return self::where('status', '!=', self::STATUS_CANCELLED)->get();
    }

    public function toArray(): array
    {
        $data = parent::toArray();
        
        // Add computed fields
        $data['status_label'] = $this->getStatusLabel();
        $data['status_class'] = $this->getStatusClass();
        $data['can_be_edited'] = $this->canBeEdited();
        $data['can_be_cancelled'] = $this->canBeCancelled();
        $data['can_be_shipped'] = $this->canBeShipped();
        $data['can_be_delivered'] = $this->canBeDelivered();
        $data['has_invoices'] = $this->hasInvoices();
        $data['is_fully_invoiced'] = $this->isFullyInvoiced();
        
        return $data;
    }

    // Validation
    public function validate(array $data = null): array
    {
        $errors = [];
        $data = $data ?: $this->attributes;

        // Required fields
        if (empty($data['order_number'])) {
            $errors['order_number'] = 'Order number is required';
        }

        if (empty($data['client_id'])) {
            $errors['client_id'] = 'Client is required';
        }

        if (empty($data['order_date'])) {
            $errors['order_date'] = 'Order date is required';
        }

        if (empty($data['delivery_date'])) {
            $errors['delivery_date'] = 'Delivery date is required';
        }

        // Date validation
        if (!empty($data['delivery_date']) && !empty($data['order_date'])) {
            if (strtotime($data['delivery_date']) < strtotime($data['order_date'])) {
                $errors['delivery_date'] = 'Delivery date cannot be before order date';
            }
        }

        return $errors;
    }
}