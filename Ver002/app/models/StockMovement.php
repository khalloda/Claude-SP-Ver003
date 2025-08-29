<?php

/**
 * File: app/models/StockMovement.php
 * Purpose: Stock movement tracking model for inventory audit trail
 * Depends on: Model base class, Product, User models
 * Notes: Handles all stock transactions for complete inventory tracking
 */

namespace App\Models;

use App\Core\Model;

class StockMovement extends Model
{
    protected static string $table = 'sp_stock_movements';
    
    protected static array $fillable = [
        'product_id',
        'movement_type',
        'quantity',
        'previous_quantity',
        'new_quantity',
        'reference_type',
        'reference_id',
        'reference_number',
        'reason',
        'cost_per_unit',
        'total_cost',
        'notes',
        'created_by'
    ];

    protected static array $casts = [
        'id' => 'int',
        'product_id' => 'int',
        'quantity' => 'int',
        'previous_quantity' => 'int',
        'new_quantity' => 'int',
        'reference_id' => 'int',
        'cost_per_unit' => 'float',
        'total_cost' => 'float',
        'created_by' => 'int'
    ];

    public const TYPE_IN = 'in';
    public const TYPE_OUT = 'out';
    public const TYPE_ADJUSTMENT = 'adjustment';
    public const TYPE_TRANSFER = 'transfer';
    public const TYPE_RESERVED = 'reserved';
    public const TYPE_RELEASED = 'released';

    public const REF_PURCHASE_ORDER = 'purchase_order';
    public const REF_SALES_ORDER = 'sales_order';
    public const REF_STOCK_ADJUSTMENT = 'stock_adjustment';
    public const REF_TRANSFER = 'transfer';
    public const REF_INITIAL_STOCK = 'initial_stock';
    public const REF_MANUAL = 'manual';

    public function product(): ?Product
    {
        return Product::find($this->product_id);
    }

    public function user(): ?User
    {
        return $this->created_by ? User::find($this->created_by) : null;
    }

    public function isInbound(): bool
    {
        return in_array($this->movement_type, [self::TYPE_IN, self::TYPE_ADJUSTMENT]) && $this->quantity > 0;
    }

    public function isOutbound(): bool
    {
        return in_array($this->movement_type, [self::TYPE_OUT, self::TYPE_ADJUSTMENT]) && $this->quantity < 0;
    }

    public function isReservation(): bool
    {
        return $this->movement_type === self::TYPE_RESERVED;
    }

    public function isRelease(): bool
    {
        return $this->movement_type === self::TYPE_RELEASED;
    }

    public function getMovementTypeLabel(): string
    {
        switch ($this->movement_type) {
            case self::TYPE_IN:
                return 'Stock In';
            case self::TYPE_OUT:
                return 'Stock Out';
            case self::TYPE_ADJUSTMENT:
                return 'Adjustment';
            case self::TYPE_TRANSFER:
                return 'Transfer';
            case self::TYPE_RESERVED:
                return 'Reserved';
            case self::TYPE_RELEASED:
                return 'Released';
            default:
                return ucfirst($this->movement_type);
        }
    }

    public function getMovementClass(): string
    {
        if ($this->isInbound()) {
            return 'success';
        } elseif ($this->isOutbound()) {
            return 'danger';
        } elseif ($this->isReservation()) {
            return 'warning';
        } elseif ($this->isRelease()) {
            return 'info';
        } else {
            return 'secondary';
        }
    }

    public function getReferenceTypeLabel(): string
    {
        switch ($this->reference_type) {
            case self::REF_PURCHASE_ORDER:
                return 'Purchase Order';
            case self::REF_SALES_ORDER:
                return 'Sales Order';
            case self::REF_STOCK_ADJUSTMENT:
                return 'Stock Adjustment';
            case self::REF_TRANSFER:
                return 'Stock Transfer';
            case self::REF_INITIAL_STOCK:
                return 'Initial Stock';
            case self::REF_MANUAL:
                return 'Manual Entry';
            default:
                return ucfirst(str_replace('_', ' ', $this->reference_type));
        }
    }

    public function getFormattedQuantity(): string
    {
        $sign = $this->quantity >= 0 ? '+' : '';
        return $sign . number_format($this->quantity);
    }

    public function getFormattedCost(): string
    {
        return $this->total_cost ? number_format($this->total_cost, 2) : 'N/A';
    }

    public function getUserName(): string
    {
        $user = $this->user();
        return $user ? $user->name : 'System';
    }

    public static function recordMovement(
        int $productId,
        string $movementType,
        int $quantity,
        int $previousQuantity,
        int $newQuantity,
        string $reason = '',
        string $referenceType = self::REF_MANUAL,
        int $referenceId = null,
        string $referenceNumber = '',
        float $costPerUnit = null,
        string $notes = '',
        int $createdBy = null
    ): ?self {
        $totalCost = $costPerUnit ? abs($quantity) * $costPerUnit : null;
        
        $movementData = [
            'product_id' => $productId,
            'movement_type' => $movementType,
            'quantity' => $quantity,
            'previous_quantity' => $previousQuantity,
            'new_quantity' => $newQuantity,
            'reference_type' => $referenceType,
            'reference_id' => $referenceId,
            'reference_number' => $referenceNumber,
            'reason' => $reason,
            'cost_per_unit' => $costPerUnit,
            'total_cost' => $totalCost,
            'notes' => $notes,
            'created_by' => $createdBy ?: ($_SESSION['user']['id'] ?? 1)
        ];

        return self::create($movementData);
    }

    public static function getProductMovements(int $productId, int $limit = 50): array
    {
        return self::where('product_id', $productId)
                   ->orderBy('created_at', 'DESC')
                   ->limit($limit)
                   ->get();
    }

    public static function getMovementsByType(string $type, int $limit = 100): array
    {
        return self::where('movement_type', $type)
                   ->orderBy('created_at', 'DESC')
                   ->limit($limit)
                   ->get();
    }

    public static function getMovementsByDateRange(string $startDate, string $endDate, int $productId = null): array
    {
        $query = [];
        
        if ($productId) {
            $query['product_id'] = $productId;
        }
        
        $movements = self::where($query)
                        ->orderBy('created_at', 'DESC')
                        ->get();
        
        // Filter by date range (simple implementation)
        return array_filter($movements, function($movement) use ($startDate, $endDate) {
            $movementDate = date('Y-m-d', strtotime($movement->created_at));
            return $movementDate >= $startDate && $movementDate <= $endDate;
        });
    }

    public function toArray(): array
    {
        $data = parent::toArray();
        
        // Add computed fields
        $data['movement_type_label'] = $this->getMovementTypeLabel();
        $data['movement_class'] = $this->getMovementClass();
        $data['reference_type_label'] = $this->getReferenceTypeLabel();
        $data['formatted_quantity'] = $this->getFormattedQuantity();
        $data['formatted_cost'] = $this->getFormattedCost();
        $data['user_name'] = $this->getUserName();
        $data['is_inbound'] = $this->isInbound();
        $data['is_outbound'] = $this->isOutbound();
        $data['is_reservation'] = $this->isReservation();
        $data['is_release'] = $this->isRelease();
        
        return $data;
    }

    // Validation
    public function validate(array $data = null): array
    {
        $errors = [];
        $data = $data ?: $this->attributes;

        // Required fields
        if (empty($data['product_id'])) {
            $errors['product_id'] = 'Product is required';
        }

        if (empty($data['movement_type'])) {
            $errors['movement_type'] = 'Movement type is required';
        }

        if (!isset($data['quantity']) || !is_numeric($data['quantity'])) {
            $errors['quantity'] = 'Quantity is required and must be numeric';
        }

        if (!isset($data['previous_quantity']) || !is_numeric($data['previous_quantity'])) {
            $errors['previous_quantity'] = 'Previous quantity is required';
        }

        if (!isset($data['new_quantity']) || !is_numeric($data['new_quantity'])) {
            $errors['new_quantity'] = 'New quantity is required';
        }

        if (empty($data['reason'])) {
            $errors['reason'] = 'Reason is required';
        }

        // Validate movement types
        $validTypes = [self::TYPE_IN, self::TYPE_OUT, self::TYPE_ADJUSTMENT, self::TYPE_TRANSFER, self::TYPE_RESERVED, self::TYPE_RELEASED];
        if (!empty($data['movement_type']) && !in_array($data['movement_type'], $validTypes)) {
            $errors['movement_type'] = 'Invalid movement type';
        }

        // Validate reference types
        $validRefTypes = [self::REF_PURCHASE_ORDER, self::REF_SALES_ORDER, self::REF_STOCK_ADJUSTMENT, self::REF_TRANSFER, self::REF_INITIAL_STOCK, self::REF_MANUAL];
        if (!empty($data['reference_type']) && !in_array($data['reference_type'], $validRefTypes)) {
            $errors['reference_type'] = 'Invalid reference type';
        }

        // Validate product exists
        if (!empty($data['product_id'])) {
            $product = Product::find($data['product_id']);
            if (!$product) {
                $errors['product_id'] = 'Invalid product selected';
            }
        }

        return $errors;
    }
}