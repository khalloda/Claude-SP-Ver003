<?php

/**
 * File: app/models/Currency.php
 * Purpose: Currency model for multi-currency support and exchange rates
 * Depends on: Model base class
 * Notes: Handles currency management, exchange rate tracking, formatting
 */

namespace App\Models;

use App\Core\Model;

class Currency extends Model
{
    protected static string $table = 'sp_currencies';
    
    protected static array $fillable = [
        'name',
        'code',
        'symbol',
        'exchange_rate',
        'decimal_places',
        'thousand_separator',
        'decimal_separator',
        'symbol_position',
        'is_active',
        'is_default'
    ];

    protected static array $casts = [
        'id' => 'int',
        'exchange_rate' => 'float',
        'decimal_places' => 'int',
        'is_active' => 'int',
        'is_default' => 'int'
    ];

    public const SYMBOL_BEFORE = 'before';
    public const SYMBOL_AFTER = 'after';

    public static function findByCode(string $code): ?self
    {
        return self::where('code', strtoupper($code))->first();
    }

    public static function getDefault(): ?self
    {
        return self::where('is_default', 1)->first();
    }

    public static function getActiveCurrencies(): array
    {
        return self::where('is_active', 1)->orderBy('is_default', 'DESC')->orderBy('name')->get();
    }

    public function isDefault(): bool
    {
        return $this->is_default === 1;
    }

    public function isActive(): bool
    {
        return $this->is_active === 1;
    }

    public function formatAmount(float $amount): string
    {
        // Format the number with appropriate decimal places and separators
        $formattedNumber = number_format(
            $amount,
            $this->decimal_places ?: 2,
            $this->decimal_separator ?: '.',
            $this->thousand_separator ?: ','
        );

        // Add currency symbol
        if ($this->symbol_position === self::SYMBOL_AFTER) {
            return $formattedNumber . ' ' . $this->symbol;
        } else {
            return $this->symbol . ' ' . $formattedNumber;
        }
    }

    public function convertTo(float $amount, self $targetCurrency): float
    {
        // Convert to base currency first (default currency)
        if (!$this->isDefault()) {
            $amount = $amount / $this->exchange_rate;
        }

        // Convert to target currency
        if (!$targetCurrency->isDefault()) {
            $amount = $amount * $targetCurrency->exchange_rate;
        }

        return $amount;
    }

    public static function convert(float $amount, self $fromCurrency, self $toCurrency): float
    {
        return $fromCurrency->convertTo($amount, $toCurrency);
    }

    public function updateExchangeRate(float $newRate, int $userId = null): bool
    {
        if ($this->isDefault()) {
            return false; // Default currency rate is always 1
        }

        $oldRate = $this->exchange_rate;
        $this->exchange_rate = $newRate;

        if ($this->save()) {
            $this->logExchangeRateChange($oldRate, $newRate, $userId);
            return true;
        }

        return false;
    }

    public function logExchangeRateChange(float $oldRate, float $newRate, int $userId = null): void
    {
        // This would log to an exchange rate history table
        // For now, just a placeholder implementation
        $logData = [
            'currency_id' => $this->id,
            'old_rate' => $oldRate,
            'new_rate' => $newRate,
            'changed_by' => $userId ?: ($_SESSION['user']['id'] ?? 1),
            'changed_at' => date('Y-m-d H:i:s')
        ];
        
        // Save to exchange_rate_history table (would need to create this table)
    }

    public function getExchangeRateHistory(int $days = 30): array
    {
        // This would fetch from exchange rate history table
        // For now, return empty array as placeholder
        return [];
    }

    public function isInUse(): bool
    {
        // Check if currency is being used in any transactions
        // This would check quotes, orders, invoices, etc.
        // For now, return false as placeholder
        return false;
    }

    public static function updateExchangeRatesFromAPI(): int
    {
        // This would integrate with a currency API service like:
        // - CurrencyLayer
        // - Fixer.io
        // - Open Exchange Rates
        // For now, return 0 as placeholder
        return 0;
    }

    public function getSymbolPositionLabel(): string
    {
        return $this->symbol_position === self::SYMBOL_AFTER ? 'After Amount' : 'Before Amount';
    }

    public static function getSymbolPositions(): array
    {
        return [
            self::SYMBOL_BEFORE => 'Before Amount',
            self::SYMBOL_AFTER => 'After Amount'
        ];
    }

    public function toArray(): array
    {
        $data = parent::toArray();
        
        // Add computed fields
        $data['is_default'] = $this->isDefault();
        $data['is_active'] = $this->isActive();
        $data['symbol_position_label'] = $this->getSymbolPositionLabel();
        $data['is_in_use'] = $this->isInUse();
        $data['formatted_sample'] = $this->formatAmount(1234.56);
        
        return $data;
    }

    // Validation
    public function validate(array $data = null): array
    {
        $errors = [];
        $data = $data ?: $this->attributes;

        // Required fields
        if (empty($data['name'])) {
            $errors['name'] = 'Currency name is required';
        }

        if (empty($data['code'])) {
            $errors['code'] = 'Currency code is required';
        }

        if (empty($data['symbol'])) {
            $errors['symbol'] = 'Currency symbol is required';
        }

        if (empty($data['exchange_rate']) || $data['exchange_rate'] <= 0) {
            $errors['exchange_rate'] = 'Exchange rate must be greater than zero';
        }

        // Validate code format (should be 3 characters)
        if (!empty($data['code']) && strlen($data['code']) !== 3) {
            $errors['code'] = 'Currency code must be exactly 3 characters';
        }

        // Check code uniqueness
        if (!empty($data['code'])) {
            $existing = self::where('code', strtoupper($data['code']))->first();
            if ($existing && $existing->id !== ($this->id ?? 0)) {
                $errors['code'] = 'Currency code already exists';
            }
        }

        // Validate decimal places
        if (isset($data['decimal_places']) && ($data['decimal_places'] < 0 || $data['decimal_places'] > 4)) {
            $errors['decimal_places'] = 'Decimal places must be between 0 and 4';
        }

        // Validate symbol position
        if (!empty($data['symbol_position'])) {
            $validPositions = array_keys(self::getSymbolPositions());
            if (!in_array($data['symbol_position'], $validPositions)) {
                $errors['symbol_position'] = 'Invalid symbol position';
            }
        }

        return $errors;
    }

    // Lifecycle hooks
    public function beforeSave(): void
    {
        // Ensure code is uppercase
        if ($this->code) {
            $this->code = strtoupper($this->code);
        }

        // Default currency must have exchange rate of 1
        if ($this->is_default) {
            $this->exchange_rate = 1.00;
            $this->is_active = 1; // Default currency must be active
        }

        parent::beforeSave();
    }

    public function afterSave(): void
    {
        // If setting as default, unset other defaults
        if ($this->is_default) {
            self::where('id', '!=', $this->id)
                ->where('is_default', 1)
                ->update(['is_default' => 0]);
        }

        parent::afterSave();
    }
}