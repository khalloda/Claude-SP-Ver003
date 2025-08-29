<?php

/**
 * File: app/controllers/SalesorderController.php
 * Purpose: Sales order management controller with complete workflow
 * Depends on: Controller, SalesOrder model, Client model, Product model
 * Notes: Handles order creation, fulfillment, invoicing, status tracking
 */

namespace App\Controllers;

use App\Core\Controller;
use App\Models\SalesOrder;
use App\Models\Client;
use App\Models\Product;
use App\Models\Quote;

class SalesorderController extends Controller
{
    public function index(array $params = []): void
    {
        $this->requireAuth();

        $status = $this->input['status'] ?? 'all';
        $client_id = $this->input['client_id'] ?? '';
        $search = $this->input['search'] ?? '';
        
        $orders = [];
        
        if ($search) {
            $orders = SalesOrder::where('order_number', 'LIKE', "%{$search}%")->get();
        } elseif ($client_id) {
            $orders = SalesOrder::where('client_id', $client_id)->orderBy('created_at', 'DESC')->get();
        } else {
            switch ($status) {
                case 'pending':
                    $orders = SalesOrder::where('status', SalesOrder::STATUS_PENDING)->orderBy('created_at', 'DESC')->get();
                    break;
                case 'processing':
                    $orders = SalesOrder::where('status', SalesOrder::STATUS_PROCESSING)->orderBy('created_at', 'DESC')->get();
                    break;
                case 'shipped':
                    $orders = SalesOrder::where('status', SalesOrder::STATUS_SHIPPED)->orderBy('created_at', 'DESC')->get();
                    break;
                case 'delivered':
                    $orders = SalesOrder::where('status', SalesOrder::STATUS_DELIVERED)->orderBy('created_at', 'DESC')->get();
                    break;
                case 'cancelled':
                    $orders = SalesOrder::where('status', SalesOrder::STATUS_CANCELLED)->orderBy('created_at', 'DESC')->get();
                    break;
                default:
                    $orders = SalesOrder::orderBy('created_at', 'DESC')->get();
            }
        }

        $clients = Client::getActiveClients();

        $this->view('salesorders/index', [
            'orders' => $orders,
            'clients' => $clients,
            'status' => $status,
            'client_id' => $client_id,
            'search' => $search,
            'page_title' => t('nav.sales_orders')
        ]);
    }

    public function create(): void
    {
        $this->requireAuth();

        $clients = Client::getActiveClients();
        $products = Product::getActiveProducts();

        $this->view('salesorders/create', [
            'order' => new SalesOrder(),
            'clients' => $clients,
            'products' => $products,
            'page_title' => t('orders.create_order')
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
            'order_date' => 'required',
            'delivery_date' => 'required',
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

        // Validate items and stock availability
        $items = $this->input['items'] ?? [];
        if (empty($items) || !is_array($items)) {
            $this->setFlash('error', 'At least one item is required.');
            $this->flashInput();
            $this->back();
        }

        // Check stock availability
        foreach ($items as $item) {
            if (empty($item['product_id']) || empty($item['quantity'])) {
                continue;
            }

            $product = Product::find($item['product_id']);
            if (!$product) {
                $this->setFlash('error', 'Invalid product selected.');
                $this->flashInput();
                $this->back();
            }

            if ($product->stock_quantity < (int)$item['quantity']) {
                $this->setFlash('error', "Insufficient stock for product: {$product->name}");
                $this->flashInput();
                $this->back();
            }
        }

        // Create sales order
        $orderData = [
            'order_number' => SalesOrder::generateOrderNumber(),
            'client_id' => $this->input['client_id'],
            'quote_id' => $this->input['quote_id'] ?? null,
            'order_date' => $this->input['order_date'],
            'delivery_date' => $this->input['delivery_date'],
            'shipping_address' => $this->input['shipping_address'] ?? '',
            'tax_rate' => $this->input['tax_rate'] ?? 0,
            'discount_percentage' => $this->input['discount_percentage'] ?? 0,
            'notes' => $this->input['notes'] ?? '',
            'terms_conditions' => $this->input['terms_conditions'] ?? '',
            'status' => SalesOrder::STATUS_PENDING,
            'created_by' => $this->getCurrentUser()['id']
        ];

        $order = SalesOrder::create($orderData);

        if ($order) {
            // Add order items and reserve stock
            $this->addOrderItems($order, $items);
            
            // Recalculate totals
            $order->recalculateTotals();

            $this->clearOldInput();
            $this->setFlash('success', t('messages.success.created'));
            $this->redirect('/salesorders/' . $order->id);
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
        $order = SalesOrder::find($id);

        if (!$order) {
            $this->setFlash('error', t('messages.error.not_found'));
            $this->redirect('/salesorders');
        }

        $client = $order->client();
        $items = $order->items();
        $invoices = $order->invoices();

        $this->view('salesorders/show', [
            'order' => $order,
            'client' => $client,
            'items' => $items,
            'invoices' => $invoices,
            'page_title' => $order->order_number
        ]);
    }

    public function edit(array $params): void
    {
        $this->requireAuth();

        $id = $params['id'] ?? 0;
        $order = SalesOrder::find($id);

        if (!$order) {
            $this->setFlash('error', t('messages.error.not_found'));
            $this->redirect('/salesorders');
        }

        if (!$order->canBeEdited()) {
            $this->setFlash('error', 'Order cannot be edited in current status.');
            $this->redirect('/salesorders/' . $order->id);
        }

        $clients = Client::getActiveClients();
        $products = Product::getActiveProducts();
        $items = $order->items();

        $this->view('salesorders/edit', [
            'order' => $order,
            'clients' => $clients,
            'products' => $products,
            'items' => $items,
            'page_title' => t('common.edit') . ' - ' . $order->order_number
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
        $order = SalesOrder::find($id);

        if (!$order || !$order->canBeEdited()) {
            $this->setFlash('error', 'Order cannot be modified.');
            $this->redirect('/salesorders');
        }

        if (!$this->validate([
            'client_id' => 'required|integer',
            'order_date' => 'required',
            'delivery_date' => 'required',
            'items' => 'required'
        ])) {
            $this->flashInput();
            $this->back();
        }

        // Update order data
        $orderData = [
            'client_id' => $this->input['client_id'],
            'order_date' => $this->input['order_date'],
            'delivery_date' => $this->input['delivery_date'],
            'shipping_address' => $this->input['shipping_address'] ?? '',
            'tax_rate' => $this->input['tax_rate'] ?? 0,
            'discount_percentage' => $this->input['discount_percentage'] ?? 0,
            'notes' => $this->input['notes'] ?? '',
            'terms_conditions' => $this->input['terms_conditions'] ?? ''
        ];

        $order->fill($orderData);
        
        if ($order->save()) {
            // Update order items (requires stock validation)
            $items = $this->input['items'] ?? [];
            $this->updateOrderItems($order, $items);
            
            // Recalculate totals
            $order->recalculateTotals();

            $this->clearOldInput();
            $this->setFlash('success', t('messages.success.updated'));
            $this->redirect('/salesorders/' . $order->id);
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
        $order = SalesOrder::find($id);

        if (!$order) {
            $this->json([
                'success' => false,
                'message' => t('messages.error.not_found')
            ], 404);
        }

        if ($order->hasInvoices()) {
            $this->json([
                'success' => false,
                'message' => 'Cannot delete order with existing invoices.'
            ], 400);
        }

        if ($order->delete()) {
            $this->json([
                'success' => true,
                'message' => t('messages.success.deleted'),
                'redirect' => '/salesorders'
            ]);
        } else {
            $this->json([
                'success' => false,
                'message' => t('messages.error.general')
            ], 500);
        }
    }

    public function markAsProcessing(array $params): void
    {
        $this->requireAuth();

        if (!$this->verifyCsrf()) {
            $this->json([
                'success' => false,
                'message' => t('messages.error.invalid_csrf')
            ], 419);
        }

        $id = $params['id'] ?? 0;
        $order = SalesOrder::find($id);

        if (!$order) {
            $this->json([
                'success' => false,
                'message' => t('messages.error.not_found')
            ], 404);
        }

        if ($order->markAsProcessing()) {
            $this->json([
                'success' => true,
                'message' => 'Order marked as processing successfully.'
            ]);
        } else {
            $this->json([
                'success' => false,
                'message' => 'Cannot mark order as processing in current status.'
            ], 400);
        }
    }

    public function markAsShipped(array $params): void
    {
        $this->requireAuth();

        if (!$this->verifyCsrf()) {
            $this->json([
                'success' => false,
                'message' => t('messages.error.invalid_csrf')
            ], 419);
        }

        $id = $params['id'] ?? 0;
        $order = SalesOrder::find($id);

        if (!$order) {
            $this->json([
                'success' => false,
                'message' => t('messages.error.not_found')
            ], 404);
        }

        $tracking_number = $this->input['tracking_number'] ?? '';

        if ($order->markAsShipped($tracking_number)) {
            $this->json([
                'success' => true,
                'message' => 'Order marked as shipped successfully.'
            ]);
        } else {
            $this->json([
                'success' => false,
                'message' => 'Cannot mark order as shipped in current status.'
            ], 400);
        }
    }

    public function markAsDelivered(array $params): void
    {
        $this->requireAuth();

        if (!$this->verifyCsrf()) {
            $this->json([
                'success' => false,
                'message' => t('messages.error.invalid_csrf')
            ], 419);
        }

        $id = $params['id'] ?? 0;
        $order = SalesOrder::find($id);

        if (!$order) {
            $this->json([
                'success' => false,
                'message' => t('messages.error.not_found')
            ], 404);
        }

        if ($order->markAsDelivered()) {
            $this->json([
                'success' => true,
                'message' => 'Order marked as delivered successfully.'
            ]);
        } else {
            $this->json([
                'success' => false,
                'message' => 'Cannot mark order as delivered in current status.'
            ], 400);
        }
    }

    public function cancel(array $params): void
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
        $order = SalesOrder::find($id);

        if (!$order) {
            $this->json([
                'success' => false,
                'message' => t('messages.error.not_found')
            ], 404);
        }

        $reason = $this->input['reason'] ?? 'Order cancelled';

        if ($order->cancel($reason)) {
            $this->json([
                'success' => true,
                'message' => 'Order cancelled successfully.'
            ]);
        } else {
            $this->json([
                'success' => false,
                'message' => 'Cannot cancel order in current status.'
            ], 400);
        }
    }

    public function createInvoice(array $params): void
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
        $order = SalesOrder::find($id);

        if (!$order) {
            $this->json([
                'success' => false,
                'message' => t('messages.error.not_found')
            ], 404);
        }

        $invoice = $order->createInvoice();

        if ($invoice) {
            $this->json([
                'success' => true,
                'message' => 'Invoice created successfully.',
                'redirect' => '/invoices/' . $invoice->id
            ]);
        } else {
            $this->json([
                'success' => false,
                'message' => 'Cannot create invoice for this order.'
            ], 400);
        }
    }

    private function addOrderItems(SalesOrder $order, array $items): void
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

            // Reserve stock
            $product->reserveStock($quantity, "Reserved for order {$order->order_number}");

            // This would normally use SalesOrderItem model
            // You'll need to create SalesOrderItem model and implement this properly
        }
    }

    private function updateOrderItems(SalesOrder $order, array $items): void
    {
        // Release existing reserved stock and add new reservations
        // This is a simplified implementation - you'd want to update existing items
        // and only delete/add as needed for better performance
        $order->releaseReservedStock();
        $this->addOrderItems($order, $items);
    }
}