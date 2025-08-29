<?php

/**
 * File: app/controllers/QuoteController.php
 * Purpose: Quote management controller with workflow handling
 * Depends on: Controller, Quote model, Client model, Product model
 * Notes: Handles quote creation, editing, approval workflow, conversion
 */

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Quote;
use App\Models\Client;
use App\Models\Product;

class QuoteController extends Controller
{
    public function index(array $params = []): void
    {
        $this->requireAuth();

        $status = $this->input['status'] ?? 'all';
        $client_id = $this->input['client_id'] ?? '';
        $search = $this->input['search'] ?? '';
        
        $quotes = [];
        
        if ($search) {
            $quotes = Quote::where('quote_number', 'LIKE', "%{$search}%")->get();
        } elseif ($client_id) {
            $quotes = Quote::where('client_id', $client_id)->orderBy('created_at', 'DESC')->get();
        } else {
            switch ($status) {
                case 'draft':
                    $quotes = Quote::where('status', Quote::STATUS_DRAFT)->orderBy('created_at', 'DESC')->get();
                    break;
                case 'sent':
                    $quotes = Quote::where('status', Quote::STATUS_SENT)->orderBy('created_at', 'DESC')->get();
                    break;
                case 'accepted':
                    $quotes = Quote::where('status', Quote::STATUS_ACCEPTED)->orderBy('created_at', 'DESC')->get();
                    break;
                case 'expired':
                    $quotes = Quote::where('status', Quote::STATUS_EXPIRED)->orderBy('created_at', 'DESC')->get();
                    break;
                default:
                    $quotes = Quote::orderBy('created_at', 'DESC')->get();
            }
        }

        $clients = Client::getActiveClients();

        $this->view('quotes/index', [
            'quotes' => $quotes,
            'clients' => $clients,
            'status' => $status,
            'client_id' => $client_id,
            'search' => $search,
            'page_title' => t('nav.quotes')
        ]);
    }

    public function create(): void
    {
        $this->requireAuth();

        $clients = Client::getActiveClients();
        $products = Product::getActiveProducts();

        $this->view('quotes/create', [
            'quote' => new Quote(),
            'clients' => $clients,
            'products' => $products,
            'page_title' => t('quotes.create_quote')
        ]);
    }

    public function store(): void
    {
        $this->requireAuth();

        if (!$this->verifyCsrf()) {
            $this->setFlash('error', t('messages.error.invalid_csrf'));
            $this->back();
        }

        if (!$this->validate([
            'client_id' => 'required|integer',
            'quote_date' => 'required',
            'valid_until' => 'required',
            'items' => 'required'
        ])) {
            $this->flashInput();
            $this->back();
        }

        // Validate client exists
        $client = Client::find($this->input['client_id']);
        if (!$client) {
            $this->setFlash('error', 'Invalid client selected.');
            $this->flashInput();
            $this->back();
        }

        // Validate items
        $items = $this->input['items'] ?? [];
        if (empty($items) || !is_array($items)) {
            $this->setFlash('error', 'At least one item is required.');
            $this->flashInput();
            $this->back();
        }

        // Create quote
        $quoteData = [
            'quote_number' => Quote::generateQuoteNumber(),
            'client_id' => $this->input['client_id'],
            'quote_date' => $this->input['quote_date'],
            'valid_until' => $this->input['valid_until'],
            'tax_rate' => $this->input['tax_rate'] ?? 0,
            'discount_percentage' => $this->input['discount_percentage'] ?? 0,
            'notes' => $this->input['notes'] ?? '',
            'terms_conditions' => $this->input['terms_conditions'] ?? '',
            'status' => Quote::STATUS_DRAFT,
            'created_by' => $this->getCurrentUser()['id']
        ];

        $quote = Quote::create($quoteData);

        if ($quote) {
            // Add quote items
            $this->addQuoteItems($quote, $items);
            
            // Recalculate totals
            $quote->recalculateTotals();

            $this->clearOldInput();
            $this->setFlash('success', t('messages.success.created'));
            $this->redirect('/quotes/' . $quote->id);
        } else {
            $this->flashInput();
            $this->setFlash('error', t('messages.error.general'));
            $this->back();
        }
    }

    public function show(array $params): void
    {
        $this->requireAuth();

        $id = $params['id'] ?? 0;
        $quote = Quote::find($id);

        if (!$quote) {
            $this->setFlash('error', t('messages.error.not_found'));
            $this->redirect('/quotes');
        }

        $client = $quote->client();
        $items = $quote->items();

        $this->view('quotes/show', [
            'quote' => $quote,
            'client' => $client,
            'items' => $items,
            'page_title' => $quote->quote_number
        ]);
    }

    public function edit(array $params): void
    {
        $this->requireAuth();

        $id = $params['id'] ?? 0;
        $quote = Quote::find($id);

        if (!$quote) {
            $this->setFlash('error', t('messages.error.not_found'));
            $this->redirect('/quotes');
        }

        if (!$quote->canBeEdited()) {
            $this->setFlash('error', 'Quote cannot be edited in current status.');
            $this->redirect('/quotes/' . $quote->id);
        }

        $clients = Client::getActiveClients();
        $products = Product::getActiveProducts();
        $items = $quote->items();

        $this->view('quotes/edit', [
            'quote' => $quote,
            'clients' => $clients,
            'products' => $products,
            'items' => $items,
            'page_title' => t('common.edit') . ' - ' . $quote->quote_number
        ]);
    }

    public function update(array $params): void
    {
        $this->requireAuth();

        if (!$this->verifyCsrf()) {
            $this->setFlash('error', t('messages.error.invalid_csrf'));
            $this->back();
        }

        $id = $params['id'] ?? 0;
        $quote = Quote::find($id);

        if (!$quote || !$quote->canBeEdited()) {
            $this->setFlash('error', 'Quote cannot be modified.');
            $this->redirect('/quotes');
        }

        if (!$this->validate([
            'client_id' => 'required|integer',
            'quote_date' => 'required',
            'valid_until' => 'required',
            'items' => 'required'
        ])) {
            $this->flashInput();
            $this->back();
        }

        // Update quote data
        $quoteData = [
            'client_id' => $this->input['client_id'],
            'quote_date' => $this->input['quote_date'],
            'valid_until' => $this->input['valid_until'],
            'tax_rate' => $this->input['tax_rate'] ?? 0,
            'discount_percentage' => $this->input['discount_percentage'] ?? 0,
            'notes' => $this->input['notes'] ?? '',
            'terms_conditions' => $this->input['terms_conditions'] ?? ''
        ];

        $quote->fill($quoteData);
        
        if ($quote->save()) {
            // Update quote items
            $items = $this->input['items'] ?? [];
            $this->updateQuoteItems($quote, $items);
            
            // Recalculate totals
            $quote->recalculateTotals();

            $this->clearOldInput();
            $this->setFlash('success', t('messages.success.updated'));
            $this->redirect('/quotes/' . $quote->id);
        } else {
            $this->flashInput();
            $this->setFlash('error', t('messages.error.general'));
            $this->back();
        }
    }

    public function destroy(array $params): void
    {
        $this->requireAuth();
        $this->requireRole('manager');

        if (!$this->verifyCsrf()) {
            $this->json([
                'success' => false,
                'message' => t('messages.error.invalid_csrf')
            ], 419);
        }

        $id = $params['id'] ?? 0;
        $quote = Quote::find($id);

        if (!$quote) {
            $this->json([
                'success' => false,
                'message' => t('messages.error.not_found')
            ], 404);
        }

        if ($quote->isConverted()) {
            $this->json([
                'success' => false,
                'message' => 'Cannot delete converted quotes.'
            ], 400);
        }

        if ($quote->delete()) {
            $this->json([
                'success' => true,
                'message' => t('messages.success.deleted'),
                'redirect' => '/quotes'
            ]);
        } else {
            $this->json([
                'success' => false,
                'message' => t('messages.error.general')
            ], 500);
        }
    }

    public function markAsSent(array $params): void
    {
        $this->requireAuth();

        if (!$this->verifyCsrf()) {
            $this->json([
                'success' => false,
                'message' => t('messages.error.invalid_csrf')
            ], 419);
        }

        $id = $params['id'] ?? 0;
        $quote = Quote::find($id);

        if (!$quote) {
            $this->json([
                'success' => false,
                'message' => t('messages.error.not_found')
            ], 404);
        }

        if ($quote->markAsSent()) {
            $this->json([
                'success' => true,
                'message' => 'Quote marked as sent successfully.'
            ]);
        } else {
            $this->json([
                'success' => false,
                'message' => 'Cannot mark quote as sent in current status.'
            ], 400);
        }
    }

    public function markAsAccepted(array $params): void
    {
        $this->requireAuth();

        if (!$this->verifyCsrf()) {
            $this->json([
                'success' => false,
                'message' => t('messages.error.invalid_csrf')
            ], 419);
        }

        $id = $params['id'] ?? 0;
        $quote = Quote::find($id);

        if (!$quote) {
            $this->json([
                'success' => false,
                'message' => t('messages.error.not_found')
            ], 404);
        }

        if ($quote->markAsAccepted()) {
            $this->json([
                'success' => true,
                'message' => 'Quote accepted successfully.'
            ]);
        } else {
            $this->json([
                'success' => false,
                'message' => 'Cannot accept quote in current status.'
            ], 400);
        }
    }

    public function markAsRejected(array $params): void
    {
        $this->requireAuth();

        if (!$this->verifyCsrf()) {
            $this->json([
                'success' => false,
                'message' => t('messages.error.invalid_csrf')
            ], 419);
        }

        $id = $params['id'] ?? 0;
        $quote = Quote::find($id);

        if (!$quote) {
            $this->json([
                'success' => false,
                'message' => t('messages.error.not_found')
            ], 404);
        }

        if ($quote->markAsRejected()) {
            $this->json([
                'success' => true,
                'message' => 'Quote rejected successfully.'
            ]);
        } else {
            $this->json([
                'success' => false,
                'message' => 'Cannot reject quote in current status.'
            ], 400);
        }
    }

    public function convertToOrder(array $params): void
    {
        $this->requireAuth();
        $this->requireRole('manager');

        if (!$this->verifyCsrf()) {
            $this->json([
                'success' => false,
                'message' => t('messages.error.invalid_csrf')
            ], 419);
        }

        $id = $params['id'] ?? 0;
        $quote = Quote::find($id);

        if (!$quote) {
            $this->json([
                'success' => false,
                'message' => t('messages.error.not_found')
            ], 404);
        }

        $salesOrder = $quote->convertToSalesOrder();

        if ($salesOrder) {
            $this->json([
                'success' => true,
                'message' => 'Quote converted to sales order successfully.',
                'redirect' => '/salesorders/' . $salesOrder->id
            ]);
        } else {
            $this->json([
                'success' => false,
                'message' => 'Cannot convert quote. Check quote status and stock availability.'
            ], 400);
        }
    }

    private function addQuoteItems(Quote $quote, array $items): void
    {
        foreach ($items as $item) {
            if (empty($item['product_id']) || empty($item['quantity'])) {
                continue;
            }

            $product = Product::find($item['product_id']);
            if (!$product) {
                continue;
            }

            $quantity = (int)$item['quantity'];
            $unitPrice = (float)($item['unit_price'] ?? $product->selling_price);
            $lineTotal = $quantity * $unitPrice;

            // This would normally use QuoteItem model, but for now we'll simulate
            // You'll need to create QuoteItem model and implement this properly
        }
    }

    private function updateQuoteItems(Quote $quote, array $items): void
    {
        // Delete existing items and add new ones
        // This is a simplified implementation - you'd want to update existing items
        // and only delete/add as needed for better performance
        $this->addQuoteItems($quote, $items);
    }

    public function getProductDetails(): void
    {
        $this->requireAuth();

        $productId = $this->input['product_id'] ?? 0;
        $product = Product::find($productId);

        if (!$product) {
            $this->json([
                'success' => false,
                'message' => 'Product not found'
            ], 404);
        }

        $this->json([
            'success' => true,
            'data' => [
                'id' => $product->id,
                'name' => $product->name,
                'sku' => $product->sku,
                'selling_price' => $product->selling_price,
                'stock_quantity' => $product->stock_quantity,
                'unit_of_measure' => $product->unit_of_measure
            ]
        ]);
    }
}