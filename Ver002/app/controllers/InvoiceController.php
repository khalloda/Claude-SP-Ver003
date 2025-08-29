<?php

/**
 * File: app/controllers/InvoiceController.php
 * Purpose: Invoice management controller with payment tracking
 * Depends on: Controller, Invoice model, SalesOrder model, Payment model
 * Notes: Handles invoice creation, payment tracking, status management
 */

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Invoice;
use App\Models\SalesOrder;
use App\Models\Client;
use App\Models\Payment;

class InvoiceController extends Controller
{
    public function index(array $params = []): void
    {
        $this->requireAuth();

        $status = $this->input['status'] ?? 'all';
        $client_id = $this->input['client_id'] ?? '';
        $search = $this->input['search'] ?? '';
        $date_from = $this->input['date_from'] ?? '';
        $date_to = $this->input['date_to'] ?? '';
        
        $invoices = [];
        
        if ($search) {
            $invoices = Invoice::where('invoice_number', 'LIKE', "%{$search}%")->get();
        } elseif ($client_id) {
            $invoices = Invoice::where('client_id', $client_id)->orderBy('created_at', 'DESC')->get();
        } else {
            switch ($status) {
                case 'draft':
                    $invoices = Invoice::where('status', Invoice::STATUS_DRAFT)->orderBy('created_at', 'DESC')->get();
                    break;
                case 'sent':
                    $invoices = Invoice::where('status', Invoice::STATUS_SENT)->orderBy('created_at', 'DESC')->get();
                    break;
                case 'paid':
                    $invoices = Invoice::where('status', Invoice::STATUS_PAID)->orderBy('created_at', 'DESC')->get();
                    break;
                case 'overdue':
                    $invoices = Invoice::where('status', Invoice::STATUS_OVERDUE)->orderBy('created_at', 'DESC')->get();
                    break;
                case 'cancelled':
                    $invoices = Invoice::where('status', Invoice::STATUS_CANCELLED)->orderBy('created_at', 'DESC')->get();
                    break;
                default:
                    $invoices = Invoice::orderBy('created_at', 'DESC')->get();
            }
        }

        // Apply date filter
        if ($date_from && $date_to) {
            $invoices = array_filter($invoices, function($invoice) use ($date_from, $date_to) {
                return $invoice->invoice_date >= $date_from && $invoice->invoice_date <= $date_to;
            });
        }

        $clients = Client::getActiveClients();

        $this->view('invoices/index', [
            'invoices' => $invoices,
            'clients' => $clients,
            'status' => $status,
            'client_id' => $client_id,
            'search' => $search,
            'date_from' => $date_from,
            'date_to' => $date_to,
            'page_title' => t('nav.invoices')
        ]);
    }

    public function create(): void
    {
        $this->requireAuth();

        $clients = Client::getActiveClients();
        $salesOrders = SalesOrder::getUninvoicedOrders();

        $this->view('invoices/create', [
            'invoice' => new Invoice(),
            'clients' => $clients,
            'sales_orders' => $salesOrders,
            'page_title' => t('invoices.create_invoice')
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
            'invoice_date' => 'required',
            'due_date' => 'required',
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

        // Create invoice
        $invoiceData = [
            'invoice_number' => Invoice::generateInvoiceNumber(),
            'client_id' => $this->input['client_id'],
            'sales_order_id' => $this->input['sales_order_id'] ?? null,
            'invoice_date' => $this->input['invoice_date'],
            'due_date' => $this->input['due_date'],
            'tax_rate' => $this->input['tax_rate'] ?? 0,
            'discount_percentage' => $this->input['discount_percentage'] ?? 0,
            'notes' => $this->input['notes'] ?? '',
            'terms_conditions' => $this->input['terms_conditions'] ?? '',
            'status' => Invoice::STATUS_DRAFT,
            'created_by' => $this->getCurrentUser()['id']
        ];

        $invoice = Invoice::create($invoiceData);

        if ($invoice) {
            // Add invoice items
            $this->addInvoiceItems($invoice, $items);
            
            // Recalculate totals
            $invoice->recalculateTotals();

            $this->clearOldInput();
            $this->setFlash('success', t('messages.success.created'));
            $this->redirect('/invoices/' . $invoice->id);
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
        $invoice = Invoice::find($id);

        if (!$invoice) {
            $this->setFlash('error', t('messages.error.not_found'));
            $this->redirect('/invoices');
        }

        $client = $invoice->client();
        $items = $invoice->items();
        $payments = $invoice->payments();
        $salesOrder = $invoice->salesOrder();

        $this->view('invoices/show', [
            'invoice' => $invoice,
            'client' => $client,
            'items' => $items,
            'payments' => $payments,
            'sales_order' => $salesOrder,
            'page_title' => $invoice->invoice_number
        ]);
    }

    public function edit(array $params): void
    {
        $this->requireAuth();

        $id = $params['id'] ?? 0;
        $invoice = Invoice::find($id);

        if (!$invoice) {
            $this->setFlash('error', t('messages.error.not_found'));
            $this->redirect('/invoices');
        }

        if (!$invoice->canBeEdited()) {
            $this->setFlash('error', 'Invoice cannot be edited in current status.');
            $this->redirect('/invoices/' . $invoice->id);
        }

        $clients = Client::getActiveClients();
        $items = $invoice->items();

        $this->view('invoices/edit', [
            'invoice' => $invoice,
            'clients' => $clients,
            'items' => $items,
            'page_title' => t('common.edit') . ' - ' . $invoice->invoice_number
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
        $invoice = Invoice::find($id);

        if (!$invoice || !$invoice->canBeEdited()) {
            $this->setFlash('error', 'Invoice cannot be modified.');
            $this->redirect('/invoices');
        }

        if (!$this->validate([
            'client_id' => 'required|integer',
            'invoice_date' => 'required',
            'due_date' => 'required',
            'items' => 'required'
        ])) {
            $this->flashInput();
            $this->back();
        }

        // Update invoice data
        $invoiceData = [
            'client_id' => $this->input['client_id'],
            'invoice_date' => $this->input['invoice_date'],
            'due_date' => $this->input['due_date'],
            'tax_rate' => $this->input['tax_rate'] ?? 0,
            'discount_percentage' => $this->input['discount_percentage'] ?? 0,
            'notes' => $this->input['notes'] ?? '',
            'terms_conditions' => $this->input['terms_conditions'] ?? ''
        ];

        $invoice->fill($invoiceData);
        
        if ($invoice->save()) {
            // Update invoice items
            $items = $this->input['items'] ?? [];
            $this->updateInvoiceItems($invoice, $items);
            
            // Recalculate totals
            $invoice->recalculateTotals();

            $this->clearOldInput();
            $this->setFlash('success', t('messages.success.updated'));
            $this->redirect('/invoices/' . $invoice->id);
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
        $invoice = Invoice::find($id);

        if (!$invoice) {
            $this->json([
                'success' => false,
                'message' => t('messages.error.not_found')
            ], 404);
        }

        if ($invoice->hasPayments()) {
            $this->json([
                'success' => false,
                'message' => 'Cannot delete invoice with existing payments.'
            ], 400);
        }

        if ($invoice->delete()) {
            $this->json([
                'success' => true,
                'message' => t('messages.success.deleted'),
                'redirect' => '/invoices'
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
        $invoice = Invoice::find($id);

        if (!$invoice) {
            $this->json([
                'success' => false,
                'message' => t('messages.error.not_found')
            ], 404);
        }

        if ($invoice->markAsSent()) {
            $this->json([
                'success' => true,
                'message' => 'Invoice marked as sent successfully.'
            ]);
        } else {
            $this->json([
                'success' => false,
                'message' => 'Cannot mark invoice as sent in current status.'
            ], 400);
        }
    }

    public function markAsPaid(array $params): void
    {
        $this->requireAuth();

        if (!$this->verifyCsrf()) {
            $this->json([
                'success' => false,
                'message' => t('messages.error.invalid_csrf')
            ], 419);
        }

        $id = $params['id'] ?? 0;
        $invoice = Invoice::find($id);

        if (!$invoice) {
            $this->json([
                'success' => false,
                'message' => t('messages.error.not_found')
            ], 404);
        }

        if ($invoice->markAsPaid()) {
            $this->json([
                'success' => true,
                'message' => 'Invoice marked as paid successfully.'
            ]);
        } else {
            $this->json([
                'success' => false,
                'message' => 'Cannot mark invoice as paid in current status.'
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
        $invoice = Invoice::find($id);

        if (!$invoice) {
            $this->json([
                'success' => false,
                'message' => t('messages.error.not_found')
            ], 404);
        }

        $reason = $this->input['reason'] ?? 'Invoice cancelled';

        if ($invoice->cancel($reason)) {
            $this->json([
                'success' => true,
                'message' => 'Invoice cancelled successfully.'
            ]);
        } else {
            $this->json([
                'success' => false,
                'message' => 'Cannot cancel invoice in current status.'
            ], 400);
        }
    }

    public function addPayment(array $params): void
    {
        $this->requireAuth();

        if (!$this->verifyCsrf()) {
            $this->json([
                'success' => false,
                'message' => t('messages.error.invalid_csrf')
            ], 419);
        }

        $id = $params['id'] ?? 0;
        $invoice = Invoice::find($id);

        if (!$invoice) {
            $this->json([
                'success' => false,
                'message' => t('messages.error.not_found')
            ], 404);
        }

        $amount = (float)($this->input['amount'] ?? 0);
        $paymentMethod = $this->input['payment_method'] ?? '';
        $reference = $this->input['reference'] ?? '';
        $notes = $this->input['notes'] ?? '';

        if ($amount <= 0) {
            $this->json([
                'success' => false,
                'message' => 'Payment amount must be greater than zero.'
            ], 400);
        }

        if ($amount > $invoice->getOutstandingAmount()) {
            $this->json([
                'success' => false,
                'message' => 'Payment amount cannot exceed outstanding balance.'
            ], 400);
        }

        $payment = $invoice->addPayment($amount, $paymentMethod, $reference, $notes);

        if ($payment) {
            $this->json([
                'success' => true,
                'message' => 'Payment recorded successfully.',
                'payment_id' => $payment->id,
                'new_outstanding' => $invoice->getOutstandingAmount()
            ]);
        } else {
            $this->json([
                'success' => false,
                'message' => 'Failed to record payment.'
            ], 400);
        }
    }

    public function generatePdf(array $params): void
    {
        $this->requireAuth();

        $id = $params['id'] ?? 0;
        $invoice = Invoice::find($id);

        if (!$invoice) {
            $this->setFlash('error', t('messages.error.not_found'));
            $this->redirect('/invoices');
        }

        $client = $invoice->client();
        $items = $invoice->items();

        // Generate PDF (this would use a PDF library like TCPDF or DomPDF)
        $pdfContent = $invoice->generatePdf();

        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="' . $invoice->invoice_number . '.pdf"');
        header('Content-Length: ' . strlen($pdfContent));
        echo $pdfContent;
        exit;
    }

    public function sendEmail(array $params): void
    {
        $this->requireAuth();

        if (!$this->verifyCsrf()) {
            $this->json([
                'success' => false,
                'message' => t('messages.error.invalid_csrf')
            ], 419);
        }

        $id = $params['id'] ?? 0;
        $invoice = Invoice::find($id);

        if (!$invoice) {
            $this->json([
                'success' => false,
                'message' => t('messages.error.not_found')
            ], 404);
        }

        $client = $invoice->client();
        if (empty($client->email)) {
            $this->json([
                'success' => false,
                'message' => 'Client does not have an email address.'
            ], 400);
        }

        if ($invoice->sendEmail()) {
            // Also mark as sent if it was draft
            if ($invoice->status === Invoice::STATUS_DRAFT) {
                $invoice->markAsSent();
            }

            $this->json([
                'success' => true,
                'message' => 'Invoice sent successfully.'
            ]);
        } else {
            $this->json([
                'success' => false,
                'message' => 'Failed to send invoice email.'
            ], 500);
        }
    }

    private function addInvoiceItems(Invoice $invoice, array $items): void
    {
        foreach ($items as $item) {
            if (empty($item['description']) || empty($item['quantity']) || empty($item['unit_price'])) {
                continue;
            }

            $quantity = (int)$item['quantity'];
            $unitPrice = (float)$item['unit_price'];
            $lineTotal = $quantity * $unitPrice;

            // This would normally use InvoiceItem model
            // You'll need to create InvoiceItem model and implement this properly
        }
    }

    private function updateInvoiceItems(Invoice $invoice, array $items): void
    {
        // Delete existing items and add new ones
        // This is a simplified implementation - you'd want to update existing items
        // and only delete/add as needed for better performance
        $this->addInvoiceItems($invoice, $items);
    }
}