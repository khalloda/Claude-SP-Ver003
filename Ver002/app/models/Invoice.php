<?php

/**
 * File: app/models/Invoice.php
 * Purpose: Invoice model with payment tracking and PDF generation
 * Depends on: Model base class, Client, SalesOrder, Payment, InvoiceItem models
 * Notes: Handles invoice lifecycle, payment application, document generation
 */

namespace App\Models;

use App\Core\Model;

class Invoice extends Model
{
    protected static string $table = 'sp_invoices';
    
    protected static array $fillable = [
        'invoice_number',
        'client_id',
        'sales_order_id',
        'invoice_date',
        'due_date',
        'subtotal',
        'tax_rate',
        'tax_amount',
        'discount_percentage',
        'discount_amount',
        'total_amount',
        'paid_amount',
        'notes',
        'terms_conditions',
        'status',
        'sent_date',
        'payment_date',
        'created_by'
    ];

    protected static array $casts = [
        'id' => 'int',
        'client_id' => 'int',
        'sales_order_id' => 'int',
        'subtotal' => 'float',
        'tax_rate' => 'float',
        'tax_amount' => 'float',
        'discount_percentage' => 'float',
        'discount_amount' => 'float',
        'total_amount' => 'float',
        'paid_amount' => 'float',
        'status' => 'int',
        'created_by' => 'int'
    ];

    public const STATUS_DRAFT = 1;
    public const STATUS_SENT = 2;
    public const STATUS_PAID = 3;
    public const STATUS_OVERDUE = 4;
    public const STATUS_CANCELLED = 5;

    public static function generateInvoiceNumber(): string
    {
        $year = date('Y');
        $month = date('m');
        
        // Get the last invoice number for this month
        $lastInvoice = self::where('invoice_number', 'LIKE', "INV-{$year}{$month}%")
                           ->orderBy('id', 'DESC')
                           ->first();
        
        if ($lastInvoice) {
            $lastNumber = (int)substr($lastInvoice->invoice_number, -4);
            $nextNumber = $lastNumber + 1;
        } else {
            $nextNumber = 1;
        }
        
        return sprintf('INV-%s%s%04d', $year, $month, $nextNumber);
    }

    public function client(): ?Client
    {
        return Client::find($this->client_id);
    }

    public function salesOrder(): ?SalesOrder
    {
        return $this->sales_order_id ? SalesOrder::find($this->sales_order_id) : null;
    }

    public function items(): array
    {
        return InvoiceItem::where('invoice_id', $this->id)->get();
    }

    public function payments(): array
    {
        return Payment::where('invoice_id', $this->id)
                     ->where('status', Payment::STATUS_COMPLETED)
                     ->get();
    }

    public function canBeEdited(): bool
    {
        return $this->status === self::STATUS_DRAFT;
    }

    public function canBeCancelled(): bool
    {
        return in_array($this->status, [self::STATUS_DRAFT, self::STATUS_SENT]);
    }

    public function hasPayments(): bool
    {
        $payments = $this->payments();
        return !empty($payments);
    }

    public function getPaidAmount(): float
    {
        $payments = $this->payments();
        $total = 0;
        
        foreach ($payments as $payment) {
            $total += $payment->amount;
        }
        
        return $total;
    }

    public function getOutstandingAmount(): float
    {
        return $this->total_amount - $this->getPaidAmount();
    }

    public function isOverdue(): bool
    {
        if ($this->status === self::STATUS_PAID || $this->status === self::STATUS_CANCELLED) {
            return false;
        }
        
        return strtotime($this->due_date) < time();
    }

    public function isPaid(): bool
    {
        return abs($this->getOutstandingAmount()) < 0.01;
    }

    public function isPartiallyPaid(): bool
    {
        $paidAmount = $this->getPaidAmount();
        return $paidAmount > 0 && $paidAmount < $this->total_amount;
    }

    public function markAsSent(): bool
    {
        if ($this->status !== self::STATUS_DRAFT) {
            return false;
        }

        $this->status = self::STATUS_SENT;
        $this->sent_date = date('Y-m-d H:i:s');
        return $this->save();
    }

    public function markAsPaid(): bool
    {
        if (!$this->isPaid()) {
            return false;
        }

        $this->status = self::STATUS_PAID;
        $this->payment_date = date('Y-m-d H:i:s');
        return $this->save();
    }

    public function cancel(string $reason = ''): bool
    {
        if (!$this->canBeCancelled()) {
            return false;
        }

        // Cannot cancel if has payments
        if ($this->hasPayments()) {
            return false;
        }

        $this->status = self::STATUS_CANCELLED;
        $this->notes = $this->notes . "\nCancelled: " . $reason;
        return $this->save();
    }

    public function addPayment(float $amount, string $method, string $reference = '', string $notes = ''): ?Payment
    {
        if ($amount <= 0 || $amount > $this->getOutstandingAmount()) {
            return null;
        }

        $paymentData = [
            'payment_number' => Payment::generatePaymentNumber(),
            'invoice_id' => $this->id,
            'client_id' => $this->client_id,
            'amount' => $amount,
            'payment_method' => $method,
            'payment_date' => date('Y-m-d'),
            'reference_number' => $reference,
            'notes' => $notes,
            'status' => Payment::STATUS_COMPLETED,
            'recorded_by' => $_SESSION['user']['id'] ?? 1
        ];

        $payment = Payment::create($paymentData);
        
        if ($payment) {
            $this->applyPayment($payment);
        }

        return $payment;
    }

    public function applyPayment(Payment $payment): void
    {
        // Recalculate status based on payments
        $this->recalculateStatus();
    }

    public function recalculateStatus(): void
    {
        if ($this->status === self::STATUS_CANCELLED) {
            return;
        }

        $outstandingAmount = $this->getOutstandingAmount();
        
        if (abs($outstandingAmount) < 0.01) {
            // Fully paid
            $this->status = self::STATUS_PAID;
            $this->payment_date = date('Y-m-d H:i:s');
        } elseif ($this->isOverdue() && $outstandingAmount > 0) {
            // Overdue
            $this->status = self::STATUS_OVERDUE;
        } elseif ($this->status === self::STATUS_DRAFT) {
            // Keep as draft
        } else {
            // Sent but not paid
            $this->status = self::STATUS_SENT;
        }
        
        $this->save();
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

    public function generatePdf(): string
    {
        // This would use a PDF library like TCPDF or DomPDF
        // For now, return a placeholder
        $client = $this->client();
        $items = $this->items();
        
        $html = $this->generatePdfHtml($client, $items);
        
        // Convert HTML to PDF using your preferred library
        // return $pdfLibrary->generate($html);
        
        return $html; // Placeholder
    }

    private function generatePdfHtml(Client $client, array $items): string
    {
        $html = '<html><head><title>' . $this->invoice_number . '</title></head><body>';
        $html .= '<h1>INVOICE</h1>';
        $html .= '<p>Invoice Number: ' . $this->invoice_number . '</p>';
        $html .= '<p>Date: ' . $this->invoice_date . '</p>';
        $html .= '<p>Due Date: ' . $this->due_date . '</p>';
        
        $html .= '<h3>Bill To:</h3>';
        $html .= '<p>' . $client->getDisplayName() . '</p>';
        $html .= '<p>' . $client->getFullAddress() . '</p>';
        
        $html .= '<table border="1" style="width:100%; border-collapse: collapse;">';
        $html .= '<tr><th>Description</th><th>Qty</th><th>Unit Price</th><th>Total</th></tr>';
        
        foreach ($items as $item) {
            $html .= '<tr>';
            $html .= '<td>' . $item->description . '</td>';
            $html .= '<td>' . $item->quantity . '</td>';
            $html .= '<td>' . number_format($item->unit_price, 2) . '</td>';
            $html .= '<td>' . number_format($item->line_total, 2) . '</td>';
            $html .= '</tr>';
        }
        
        $html .= '</table>';
        
        $html .= '<p>Subtotal: ' . number_format($this->subtotal, 2) . '</p>';
        $html .= '<p>Discount: ' . number_format($this->discount_amount, 2) . '</p>';
        $html .= '<p>Tax: ' . number_format($this->tax_amount, 2) . '</p>';
        $html .= '<p><strong>Total: ' . number_format($this->total_amount, 2) . '</strong></p>';
        
        $html .= '</body></html>';
        
        return $html;
    }

    public function sendEmail(): bool
    {
        $client = $this->client();
        
        if (!$client || empty($client->email)) {
            return false;
        }

        // This would use your email system
        // For now, return true as placeholder
        return true;
    }

    public function getStatusLabel(): string
    {
        switch ($this->status) {
            case self::STATUS_DRAFT:
                return 'Draft';
            case self::STATUS_SENT:
                return 'Sent';
            case self::STATUS_PAID:
                return 'Paid';
            case self::STATUS_OVERDUE:
                return 'Overdue';
            case self::STATUS_CANCELLED:
                return 'Cancelled';
            default:
                return 'Unknown';
        }
    }

    public function getStatusClass(): string
    {
        switch ($this->status) {
            case self::STATUS_DRAFT:
                return 'secondary';
            case self::STATUS_SENT:
                return 'info';
            case self::STATUS_PAID:
                return 'success';
            case self::STATUS_OVERDUE:
                return 'danger';
            case self::STATUS_CANCELLED:
                return 'dark';
            default:
                return 'secondary';
        }
    }

    public static function getUnpaidInvoices(): array
    {
        return self::where('status', '!=', self::STATUS_PAID)
                  ->where('status', '!=', self::STATUS_CANCELLED)
                  ->orderBy('due_date')
                  ->get();
    }

    public function toArray(): array
    {
        $data = parent::toArray();
        
        // Add computed fields
        $data['status_label'] = $this->getStatusLabel();
        $data['status_class'] = $this->getStatusClass();
        $data['can_be_edited'] = $this->canBeEdited();
        $data['can_be_cancelled'] = $this->canBeCancelled();
        $data['has_payments'] = $this->hasPayments();
        $data['paid_amount'] = $this->getPaidAmount();
        $data['outstanding_amount'] = $this->getOutstandingAmount();
        $data['is_overdue'] = $this->isOverdue();
        $data['is_paid'] = $this->isPaid();
        $data['is_partially_paid'] = $this->isPartiallyPaid();
        
        return $data;
    }

    // Validation
    public function validate(array $data = null): array
    {
        $errors = [];
        $data = $data ?: $this->attributes;

        // Required fields
        if (empty($data['invoice_number'])) {
            $errors['invoice_number'] = 'Invoice number is required';
        }

        if (empty($data['client_id'])) {
            $errors['client_id'] = 'Client is required';
        }

        if (empty($data['invoice_date'])) {
            $errors['invoice_date'] = 'Invoice date is required';
        }

        if (empty($data['due_date'])) {
            $errors['due_date'] = 'Due date is required';
        }

        // Date validation
        if (!empty($data['due_date']) && !empty($data['invoice_date'])) {
            if (strtotime($data['due_date']) < strtotime($data['invoice_date'])) {
                $errors['due_date'] = 'Due date cannot be before invoice date';
            }
        }

        return $errors;
    }
}