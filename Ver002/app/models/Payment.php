<?php

/**
 * File: app/models/Payment.php
 * Purpose: Payment model with transaction tracking and reconciliation
 * Depends on: Model base class, Invoice, Client models
 * Notes: Handles payment processing, refunds, audit trail
 */

namespace App\Models;

use App\Core\Model;

class Payment extends Model
{
    protected static string $table = 'sp_payments';
    
    protected static array $fillable = [
        'payment_number',
        'invoice_id',
        'client_id',
        'amount',
        'payment_method',
        'payment_date',
        'reference_number',
        'notes',
        'status',
        'failure_reason',
        'refunded_amount',
        'refund_date',
        'refund_reason',
        'recorded_by'
    ];

    protected static array $casts = [
        'id' => 'int',
        'invoice_id' => 'int',
        'client_id' => 'int',
        'amount' => 'float',
        'refunded_amount' => 'float',
        'status' => 'int',
        'recorded_by' => 'int'
    ];

    public const STATUS_PENDING = 1;
    public const STATUS_COMPLETED = 2;
    public const STATUS_FAILED = 3;
    public const STATUS_REFUNDED = 4;

    public const METHOD_CASH = 'cash';
    public const METHOD_BANK_TRANSFER = 'bank_transfer';
    public const METHOD_CREDIT_CARD = 'credit_card';
    public const METHOD_DEBIT_CARD = 'debit_card';
    public const METHOD_CHEQUE = 'cheque';
    public const METHOD_ONLINE = 'online';

    public static function generatePaymentNumber(): string
    {
        $year = date('Y');
        $month = date('m');
        
        // Get the last payment number for this month
        $lastPayment = self::where('payment_number', 'LIKE', "PAY-{$year}{$month}%")
                           ->orderBy('id', 'DESC')
                           ->first();
        
        if ($lastPayment) {
            $lastNumber = (int)substr($lastPayment->payment_number, -4);
            $nextNumber = $lastNumber + 1;
        } else {
            $nextNumber = 1;
        }
        
        return sprintf('PAY-%s%s%04d', $year, $month, $nextNumber);
    }

    public function invoice(): ?Invoice
    {
        return Invoice::find($this->invoice_id);
    }

    public function client(): ?Client
    {
        return Client::find($this->client_id);
    }

    public function canBeEdited(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function canBeDeleted(): bool
    {
        return in_array($this->status, [self::STATUS_PENDING, self::STATUS_FAILED]);
    }

    public function canBeRefunded(): bool
    {
        return $this->status === self::STATUS_COMPLETED && $this->refunded_amount < $this->amount;
    }

    public function isCompleted(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    public function isFailed(): bool
    {
        return $this->status === self::STATUS_FAILED;
    }

    public function isRefunded(): bool
    {
        return $this->status === self::STATUS_REFUNDED;
    }

    public function isPartiallyRefunded(): bool
    {
        return $this->refunded_amount > 0 && $this->refunded_amount < $this->amount;
    }

    public function getAvailableRefundAmount(): float
    {
        return $this->amount - $this->refunded_amount;
    }

    public function markAsFailed(string $reason): bool
    {
        if ($this->status !== self::STATUS_PENDING) {
            return false;
        }

        $this->status = self::STATUS_FAILED;
        $this->failure_reason = $reason;
        return $this->save();
    }

    public function refund(float $amount, string $reason = ''): bool
    {
        if (!$this->canBeRefunded()) {
            return false;
        }

        $availableAmount = $this->getAvailableRefundAmount();
        if ($amount > $availableAmount) {
            return false;
        }

        $this->refunded_amount += $amount;
        $this->refund_reason = $reason;
        $this->refund_date = date('Y-m-d H:i:s');

        // If fully refunded, update status
        if ($this->refunded_amount >= $this->amount) {
            $this->status = self::STATUS_REFUNDED;
        }

        return $this->save();
    }

    public function getStatusLabel(): string
    {
        switch ($this->status) {
            case self::STATUS_PENDING:
                return 'Pending';
            case self::STATUS_COMPLETED:
                return 'Completed';
            case self::STATUS_FAILED:
                return 'Failed';
            case self::STATUS_REFUNDED:
                return 'Refunded';
            default:
                return 'Unknown';
        }
    }

    public function getStatusClass(): string
    {
        switch ($this->status) {
            case self::STATUS_PENDING:
                return 'warning';
            case self::STATUS_COMPLETED:
                return 'success';
            case self::STATUS_FAILED:
                return 'danger';
            case self::STATUS_REFUNDED:
                return 'info';
            default:
                return 'secondary';
        }
    }

    public function getMethodLabel(): string
    {
        $methods = self::getPaymentMethods();
        return $methods[$this->payment_method] ?? ucfirst(str_replace('_', ' ', $this->payment_method));
    }

    public static function getPaymentMethods(): array
    {
        return [
            self::METHOD_CASH => 'Cash',
            self::METHOD_BANK_TRANSFER => 'Bank Transfer',
            self::METHOD_CREDIT_CARD => 'Credit Card',
            self::METHOD_DEBIT_CARD => 'Debit Card',
            self::METHOD_CHEQUE => 'Cheque',
            self::METHOD_ONLINE => 'Online Payment'
        ];
    }

    public function toArray(): array
    {
        $data = parent::toArray();
        
        // Add computed fields
        $data['status_label'] = $this->getStatusLabel();
        $data['status_class'] = $this->getStatusClass();
        $data['method_label'] = $this->getMethodLabel();
        $data['can_be_edited'] = $this->canBeEdited();
        $data['can_be_deleted'] = $this->canBeDeleted();
        $data['can_be_refunded'] = $this->canBeRefunded();
        $data['is_completed'] = $this->isCompleted();
        $data['is_failed'] = $this->isFailed();
        $data['is_refunded'] = $this->isRefunded();
        $data['is_partially_refunded'] = $this->isPartiallyRefunded();
        $data['available_refund_amount'] = $this->getAvailableRefundAmount();
        
        return $data;
    }

    // Validation
    public function validate(array $data = null): array
    {
        $errors = [];
        $data = $data ?: $this->attributes;

        // Required fields
        if (empty($data['payment_number'])) {
            $errors['payment_number'] = 'Payment number is required';
        }

        if (empty($data['invoice_id'])) {
            $errors['invoice_id'] = 'Invoice is required';
        }

        if (empty($data['client_id'])) {
            $errors['client_id'] = 'Client is required';
        }

        if (empty($data['amount']) || $data['amount'] <= 0) {
            $errors['amount'] = 'Amount must be greater than zero';
        }

        if (empty($data['payment_method'])) {
            $errors['payment_method'] = 'Payment method is required';
        }

        if (empty($data['payment_date'])) {
            $errors['payment_date'] = 'Payment date is required';
        }

        // Validate payment method
        if (!empty($data['payment_method'])) {
            $validMethods = array_keys(self::getPaymentMethods());
            if (!in_array($data['payment_method'], $validMethods)) {
                $errors['payment_method'] = 'Invalid payment method';
            }
        }

        // Validate amount against invoice
        if (!empty($data['invoice_id']) && !empty($data['amount'])) {
            $invoice = Invoice::find($data['invoice_id']);
            if ($invoice) {
                $maxAmount = $invoice->getOutstandingAmount();
                if (isset($data['id'])) {
                    // If updating existing payment, add back the current payment amount
                    $currentPayment = self::find($data['id']);
                    if ($currentPayment) {
                        $maxAmount += $currentPayment->amount;
                    }
                }
                
                if ($data['amount'] > $maxAmount) {
                    $errors['amount'] = 'Payment amount cannot exceed outstanding balance';
                }
            }
        }

        return $errors;
    }
}