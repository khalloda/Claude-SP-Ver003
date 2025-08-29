<?php

/**
 * File: app/controllers/CurrencyController.php
 * Purpose: Currency management controller with exchange rate handling
 * Depends on: Controller, Currency model
 * Notes: Handles currency CRUD, exchange rates, multi-currency support
 */

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Currency;

class CurrencyController extends Controller
{
    public function index(array $params = []): void
    {
        $this->requireAuth();
        $this->requireRole('admin');

        $status = $this->input['status'] ?? 'all';
        $search = $this->input['search'] ?? '';
        
        $currencies = [];
        
        if ($search) {
            $currencies = Currency::where('name', 'LIKE', "%{$search}%")
                                 ->orWhere('code', 'LIKE', "%{$search}%")
                                 ->orWhere('symbol', 'LIKE', "%{$search}%")
                                 ->orderBy('is_default', 'DESC')
                                 ->orderBy('name')
                                 ->get();
        } else {
            switch ($status) {
                case 'active':
                    $currencies = Currency::where('is_active', 1)
                                        ->orderBy('is_default', 'DESC')
                                        ->orderBy('name')
                                        ->get();
                    break;
                case 'inactive':
                    $currencies = Currency::where('is_active', 0)
                                        ->orderBy('name')
                                        ->get();
                    break;
                default:
                    $currencies = Currency::orderBy('is_default', 'DESC')
                                        ->orderBy('name')
                                        ->get();
            }
        }

        $this->view('currencies/index', [
            'currencies' => $currencies,
            'search' => $search,
            'status' => $status,
            'page_title' => t('nav.currencies')
        ]);
    }

    public function create(): void
    {
        $this->requireAuth();
        $this->requireRole('admin');

        $this->view('currencies/create', [
            'currency' => new Currency(),
            'page_title' => t('currencies.add_currency')
        ]);
    }

    public function store(): void
    {
        $this->requireAuth();
        $this->requireRole('admin');

        if (!$this->verifyCsrf()) {
            $this->setFlash('error', t('messages.error.invalid_csrf'));
            $this->back();
        }

        if (!$this->validate([
            'name' => 'required|min:2|max:100',
            'code' => 'required|min:3|max:3',
            'symbol' => 'required|min:1|max:10',
            'exchange_rate' => 'required|numeric'
        ])) {
            $this->flashInput();
            $this->back();
        }

        // Check code uniqueness
        if (Currency::findByCode($this->input['code'])) {
            $this->setFlash('error', 'Currency code already exists. Please use a different code.');
            $this->flashInput();
            $this->back();
        }

        $currencyData = $this->sanitizeInput([
            'name' => $this->input['name'],
            'code' => strtoupper($this->input['code']),
            'symbol' => $this->input['symbol'],
            'exchange_rate' => $this->input['exchange_rate'],
            'decimal_places' => $this->input['decimal_places'] ?? 2,
            'thousand_separator' => $this->input['thousand_separator'] ?? ',',
            'decimal_separator' => $this->input['decimal_separator'] ?? '.',
            'symbol_position' => $this->input['symbol_position'] ?? 'before',
            'is_active' => isset($this->input['is_active']) ? 1 : 0,
            'is_default' => isset($this->input['is_default']) ? 1 : 0
        ]);

        // If setting as default, unset other defaults
        if ($currencyData['is_default']) {
            Currency::where('is_default', 1)->update(['is_default' => 0]);
            $currencyData['exchange_rate'] = 1.00; // Default currency always has rate of 1
        }

        $currency = Currency::create($currencyData);

        if ($currency) {
            $this->clearOldInput();
            $this->setFlash('success', t('messages.success.created'));
            $this->redirect('/currencies/' . $currency->id);
        } else {
            $this->flashInput();
            $this->setFlash('error', t('messages.error.general'));
            $this->back();
        }
    }

    public function show(array $params): void
    {
        $this->requireAuth();
        $this->requireRole('admin');

        $id = $params['id'] ?? 0;
        $currency = Currency::find($id);

        if (!$currency) {
            $this->setFlash('error', t('messages.error.not_found'));
            $this->redirect('/currencies');
        }

        // Get exchange rate history (last 30 days)
        $rateHistory = $currency->getExchangeRateHistory(30);

        $this->view('currencies/show', [
            'currency' => $currency,
            'rate_history' => $rateHistory,
            'page_title' => $currency->name
        ]);
    }

    public function edit(array $params): void
    {
        $this->requireAuth();
        $this->requireRole('admin');

        $id = $params['id'] ?? 0;
        $currency = Currency::find($id);

        if (!$currency) {
            $this->setFlash('error', t('messages.error.not_found'));
            $this->redirect('/currencies');
        }

        $this->view('currencies/edit', [
            'currency' => $currency,
            'page_title' => t('common.edit') . ' - ' . $currency->name
        ]);
    }

    public function update(array $params): void
    {
        $this->requireAuth();
        $this->requireRole('admin');

        if (!$this->verifyCsrf()) {
            $this->setFlash('error', t('messages.error.invalid_csrf'));
            $this->back();
        }

        $id = $params['id'] ?? 0;
        $currency = Currency::find($id);

        if (!$currency) {
            $this->setFlash('error', t('messages.error.not_found'));
            $this->redirect('/currencies');
        }

        if (!$this->validate([
            'name' => 'required|min:2|max:100',
            'code' => 'required|min:3|max:3',
            'symbol' => 'required|min:1|max:10',
            'exchange_rate' => 'required|numeric'
        ])) {
            $this->flashInput();
            $this->back();
        }

        // Check code uniqueness (excluding current currency)
        $existingCurrency = Currency::findByCode($this->input['code']);
        if ($existingCurrency && $existingCurrency->id !== $currency->id) {
            $this->setFlash('error', 'Currency code already exists. Please use a different code.');
            $this->flashInput();
            $this->back();
        }

        $oldRate = $currency->exchange_rate;
        $newRate = (float)$this->input['exchange_rate'];

        $currencyData = $this->sanitizeInput([
            'name' => $this->input['name'],
            'code' => strtoupper($this->input['code']),
            'symbol' => $this->input['symbol'],
            'exchange_rate' => $newRate,
            'decimal_places' => $this->input['decimal_places'] ?? 2,
            'thousand_separator' => $this->input['thousand_separator'] ?? ',',
            'decimal_separator' => $this->input['decimal_separator'] ?? '.',
            'symbol_position' => $this->input['symbol_position'] ?? 'before',
            'is_active' => isset($this->input['is_active']) ? 1 : 0,
            'is_default' => isset($this->input['is_default']) ? 1 : 0
        ]);

        // If setting as default, unset other defaults and set rate to 1
        if ($currencyData['is_default'] && !$currency->is_default) {
            Currency::where('is_default', 1)->update(['is_default' => 0]);
            $currencyData['exchange_rate'] = 1.00;
        }

        // Default currency must have rate of 1
        if ($currency->is_default) {
            $currencyData['exchange_rate'] = 1.00;
        }

        $currency->fill($currencyData);
        
        if ($currency->save()) {
            // Log exchange rate change if significant
            if (abs($oldRate - $newRate) > 0.01) {
                $currency->logExchangeRateChange($oldRate, $newRate, $this->getCurrentUser()['id']);
            }

            $this->clearOldInput();
            $this->setFlash('success', t('messages.success.updated'));
            $this->redirect('/currencies/' . $currency->id);
        } else {
            $this->flashInput();
            $this->setFlash('error', t('messages.error.general'));
            $this->back();
        }
    }

    public function destroy(array $params): void
    {
        $this->requireAuth();
        $this->requireRole('admin');

        if (!$this->verifyCsrf()) {
            $this->json([
                'success' => false,
                'message' => t('messages.error.invalid_csrf')
            ], 419);
        }

        $id = $params['id'] ?? 0;
        $currency = Currency::find($id);

        if (!$currency) {
            $this->json([
                'success' => false,
                'message' => t('messages.error.not_found')
            ], 404);
        }

        // Cannot delete default currency
        if ($currency->is_default) {
            $this->json([
                'success' => false,
                'message' => 'Cannot delete the default currency.'
            ], 400);
        }

        // Check if currency is being used
        if ($currency->isInUse()) {
            $this->json([
                'success' => false,
                'message' => 'Cannot delete currency that is currently in use.'
            ], 400);
        }

        if ($currency->delete()) {
            $this->json([
                'success' => true,
                'message' => t('messages.success.deleted'),
                'redirect' => '/currencies'
            ]);
        } else {
            $this->json([
                'success' => false,
                'message' => t('messages.error.general')
            ], 500);
        }
    }

    public function setDefault(array $params): void
    {
        $this->requireAuth();
        $this->requireRole('admin');

        if (!$this->verifyCsrf()) {
            $this->json([
                'success' => false,
                'message' => t('messages.error.invalid_csrf')
            ], 419);
        }

        $id = $params['id'] ?? 0;
        $currency = Currency::find($id);

        if (!$currency) {
            $this->json([
                'success' => false,
                'message' => t('messages.error.not_found')
            ], 404);
        }

        // Unset other defaults
        Currency::where('is_default', 1)->update(['is_default' => 0]);

        $currency->is_default = 1;
        $currency->exchange_rate = 1.00;
        $currency->is_active = 1;
        
        if ($currency->save()) {
            $this->json([
                'success' => true,
                'message' => 'Default currency set successfully.'
            ]);
        } else {
            $this->json([
                'success' => false,
                'message' => t('messages.error.general')
            ], 500);
        }
    }

    public function updateExchangeRate(array $params): void
    {
        $this->requireAuth();
        $this->requireRole('admin');

        if (!$this->verifyCsrf()) {
            $this->json([
                'success' => false,
                'message' => t('messages.error.invalid_csrf')
            ], 419);
        }

        $id = $params['id'] ?? 0;
        $currency = Currency::find($id);

        if (!$currency) {
            $this->json([
                'success' => false,
                'message' => t('messages.error.not_found')
            ], 404);
        }

        if ($currency->is_default) {
            $this->json([
                'success' => false,
                'message' => 'Cannot change exchange rate of default currency.'
            ], 400);
        }

        $newRate = (float)($this->input['exchange_rate'] ?? 0);
        if ($newRate <= 0) {
            $this->json([
                'success' => false,
                'message' => 'Exchange rate must be greater than zero.'
            ], 400);
        }

        $oldRate = $currency->exchange_rate;
        $currency->exchange_rate = $newRate;
        
        if ($currency->save()) {
            // Log exchange rate change
            $currency->logExchangeRateChange($oldRate, $newRate, $this->getCurrentUser()['id']);

            $this->json([
                'success' => true,
                'message' => 'Exchange rate updated successfully.',
                'old_rate' => $oldRate,
                'new_rate' => $newRate
            ]);
        } else {
            $this->json([
                'success' => false,
                'message' => t('messages.error.general')
            ], 500);
        }
    }

    public function updateAllRates(): void
    {
        $this->requireAuth();
        $this->requireRole('admin');

        if (!$this->verifyCsrf()) {
            $this->json([
                'success' => false,
                'message' => t('messages.error.invalid_csrf')
            ], 419);
        }

        // This would integrate with a currency API service
        $updated = Currency::updateExchangeRatesFromAPI();

        if ($updated > 0) {
            $this->json([
                'success' => true,
                'message' => "Updated {$updated} exchange rates successfully."
            ]);
        } else {
            $this->json([
                'success' => false,
                'message' => 'Failed to update exchange rates from external service.'
            ], 500);
        }
    }

    public function convert(): void
    {
        $this->requireAuth();

        $amount = (float)($this->input['amount'] ?? 0);
        $fromCurrency = $this->input['from_currency'] ?? '';
        $toCurrency = $this->input['to_currency'] ?? '';

        if ($amount <= 0) {
            $this->json([
                'success' => false,
                'message' => 'Amount must be greater than zero.'
            ], 400);
        }

        $from = Currency::findByCode($fromCurrency);
        $to = Currency::findByCode($toCurrency);

        if (!$from || !$to) {
            $this->json([
                'success' => false,
                'message' => 'Invalid currency codes.'
            ], 400);
        }

        $convertedAmount = Currency::convert($amount, $from, $to);

        $this->json([
            'success' => true,
            'data' => [
                'original_amount' => $amount,
                'from_currency' => $from->code,
                'to_currency' => $to->code,
                'converted_amount' => $convertedAmount,
                'exchange_rate' => $to->exchange_rate / $from->exchange_rate,
                'formatted_amount' => $to->formatAmount($convertedAmount)
            ]
        ]);
    }

    public function getActiveCurrencies(): void
    {
        $this->requireAuth();

        $currencies = Currency::getActiveCurrencies();

        $this->json([
            'success' => true,
            'data' => array_map(function($currency) {
                return [
                    'id' => $currency->id,
                    'name' => $currency->name,
                    'code' => $currency->code,
                    'symbol' => $currency->symbol,
                    'exchange_rate' => $currency->exchange_rate,
                    'is_default' => $currency->is_default
                ];
            }, $currencies)
        ]);
    }

    public function rateHistory(array $params): void
    {
        $this->requireAuth();
        $this->requireRole('admin');

        $id = $params['id'] ?? 0;
        $currency = Currency::find($id);

        if (!$currency) {
            $this->setFlash('error', t('messages.error.not_found'));
            $this->redirect('/currencies');
        }

        $days = $this->input['days'] ?? 90;
        $rateHistory = $currency->getExchangeRateHistory($days);

        $this->view('currencies/rate_history', [
            'currency' => $currency,
            'rate_history' => $rateHistory,
            'days' => $days,
            'page_title' => t('currencies.rate_history') . ' - ' . $currency->name
        ]);
    }
}