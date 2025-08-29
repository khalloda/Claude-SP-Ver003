<?php

/**
 * File: app/controllers/PaymentController.php
 * Purpose: Payment management controller with tracking and reconciliation
 * Depends on: Controller, Payment model, Invoice model, Client model
 * Notes: Handles payment recording, tracking, refunds, and reporting
 */

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Payment;
use App\Models\Invoice;
use App\Models\Client;

class PaymentController extends Controller
{
    public function index(array $params = []): void
    {
        $this->requireAuth();

        $status = $this->input['status'] ?? 'all';
        $client_id = $this->input['client_id'] ?? '';
        $method = $this->input['method'] ?? '';
        $date_from = $this->input['date_from'] ?? '';
        $date_to = $this->input['date_to'] ?? '';
        
        $payments = [];
        
        // Base query
        $query = [];
        
        if ($client_id) {
            $query['client_id'] = $client_id;
        }
        
        if ($method) {
            $query['payment_method'] = $method;
        }
        
        switch ($status) {
            case 'completed':
                $query['status'] = Payment::STATUS_COMPLETED;
                break;
            case 'pending':
                $query['status'] = Payment::STATUS_PENDING;
                break;
            case 'failed':
                $query['status'] = Payment::STATUS_FAILED;
                break;
            case 'refunded':
                $query['status'] = Payment::STATUS_REFUNDED;
                break;
        }
        
        if (!empty($query)) {
            $payments = Payment::where($query)->orderBy('created_at', 'DESC')->get();
        } else {
            $payments = Payment::orderBy('created_at', 'DESC')->get();
        }

        // Apply date filter
        if ($date_from && $date_to) {
            $payments = array_filter($payments, function($payment) use ($date_from, $date_to) {
                return $payment->payment_date >= $date_from && $payment->payment_date <= $date_to;
            });
        }

        $clients = Client::getActiveClients();
        $paymentMethods = Payment::getPaymentMethods();

        // Calculate totals
        $totalAmount = array_sum(array_map(function($p) { 
            return $p->status === Payment::STATUS_COMPLETED ? $p->amount : 0; 
        }, $payments));

        $this->view('payments/index', [
            'payments' => $payments,
            'clients' => $clients,
            'payment_methods' => $paymentMethods,
            'status' => $status,
            'client_id' => $client_id,
            'method' => $method,
            'date_from' => $date_from,
            'date_to' => $date_to,
            'total_amount' => $totalAmount,
            'page_title' => t('nav.payments')
        ]);
    }

    public function create(): void
    {
        $this->requireAuth();

        $clients = Client::getActiveClients();
        $unpaidInvoices = Invoice::getUnpaidInvoices();
        $paymentMethods = Payment::getPaymentMethods();

        $this->view('payments/create', [
            'payment' => new Payment(),
            'clients' => $clients,
            'invoices' => $unpaidInvoices,
            'payment_methods' => $paymentMethods,
            'page_title' => t('payments.record_payment')
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
            'invoice_id' => 'required|integer',
            'amount' => 'required|numeric',
            'payment_method' => 'required',
            'payment_date' => 'required'
        ])) {
            $this->flashInput();
            $this->back();
        }

        // Validate invoice exists
        $invoice = Invoice::find($this->input['invoice_id']);
        if (!$invoice) {
            $this->setFlash('error', 'Invalid invoice selected.');
            $this->flashInput();
            $this->back();
        }

        $amount = (float)$this->input['amount'];
        if ($amount <= 0) {
            $this->setFlash('error', 'Payment amount must be greater than zero.');
            $this->flashInput();
            $this->back();
        }

        if ($amount > $invoice->getOutstandingAmount()) {
            $this->setFlash('error', 'Payment amount cannot exceed outstanding balance.');
            $this->flashInput();
            $this->back();
        }

        // Create payment
        $paymentData = [
            'payment_number' => Payment::generatePaymentNumber(),
            'invoice_id' => $this->input['invoice_id'],
            'client_id' => $invoice->client_id,
            'amount' => $amount,
            'payment_method' => $this->input['payment_method'],
            'payment_date' => $this->input['payment_date'],
            'reference_number' => $this->input['reference_number'] ?? '',
            'notes' => $this->input['notes'] ?? '',
            'status' => Payment::STATUS_COMPLETED,
            'recorded_by' => $this->getCurrentUser()['id']
        ];

        $payment = Payment::create($paymentData);

        if ($payment) {
            // Update invoice status
            $invoice->applyPayment($payment);

            $this->clearOldInput();
            $this->setFlash('success', t('messages.success.created'));
            $this->redirect('/payments/' . $payment->id);
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
        $payment = Payment::find($id);

        if (!$payment) {
            $this->setFlash('error', t('messages.error.not_found'));
            $this->redirect('/payments');
        }

        $invoice = $payment->invoice();
        $client = $payment->client();

        $this->view('payments/show', [
            'payment' => $payment,
            'invoice' => $invoice,
            'client' => $client,
            'page_title' => $payment->payment_number
        ]);
    }

    public function edit(array $params): void
    {
        $this->requireAuth();
        $this->requireRole('manager');

        $id = $params['id'] ?? 0;
        $payment = Payment::find($id);

        if (!$payment) {
            $this->setFlash('error', t('messages.error.not_found'));
            $this->redirect('/payments');
        }

        if (!$payment->canBeEdited()) {
            $this->setFlash('error', 'Payment cannot be edited in current status.');
            $this->redirect('/payments/' . $payment->id);
        }

        $paymentMethods = Payment::getPaymentMethods();
        $invoice = $payment->invoice();

        $this->view('payments/edit', [
            'payment' => $payment,
            'invoice' => $invoice,
            'payment_methods' => $paymentMethods,
            'page_title' => t('common.edit') . ' - ' . $payment->payment_number
        ]);
    }

    public function update(array $params): void
    {
        $this->requireAuth();
        $this->requireRole('manager');

        if (!$this->verifyCsrf()) {
            $this->setFlash('error', t('messages.error.invalid_csrf'));
            $this->back();
        }

        $id = $params['id'] ?? 0;
        $payment = Payment::find($id);

        if (!$payment || !$payment->canBeEdited()) {
            $this->setFlash('error', 'Payment cannot be modified.');
            $this->redirect('/payments');
        }

        if (!$this->validate([
            'amount' => 'required|numeric',
            'payment_method' => 'required',
            'payment_date' => 'required'
        ])) {
            $this->flashInput();
            $this->back();
        }

        $newAmount = (float)$this->input['amount'];
        if ($newAmount <= 0) {
            $this->setFlash('error', 'Payment amount must be greater than zero.');
            $this->flashInput();
            $this->back();
        }

        $invoice = $payment->invoice();
        $currentOutstanding = $invoice->getOutstandingAmount() + $payment->amount; // Add back current payment
        
        if ($newAmount > $currentOutstanding) {
            $this->setFlash('error', 'Payment amount cannot exceed outstanding balance.');
            $this->flashInput();
            $this->back();
        }

        // Update payment data
        $paymentData = [
            'amount' => $newAmount,
            'payment_method' => $this->input['payment_method'],
            'payment_date' => $this->input['payment_date'],
            'reference_number' => $this->input['reference_number'] ?? '',
            'notes' => $this->input['notes'] ?? ''
        ];

        $payment->fill($paymentData);
        
        if ($payment->save()) {
            // Recalculate invoice status
            $invoice->recalculateStatus();

            $this->clearOldInput();
            $this->setFlash('success', t('messages.success.updated'));
            $this->redirect('/payments/' . $payment->id);
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
        $payment = Payment::find($id);

        if (!$payment) {
            $this->json([
                'success' => false,
                'message' => t('messages.error.not_found')
            ], 404);
        }

        if (!$payment->canBeDeleted()) {
            $this->json([
                'success' => false,
                'message' => 'Payment cannot be deleted in current status.'
            ], 400);
        }

        if ($payment->delete()) {
            // Recalculate invoice status
            $invoice = $payment->invoice();
            $invoice->recalculateStatus();

            $this->json([
                'success' => true,
                'message' => t('messages.success.deleted'),
                'redirect' => '/payments'
            ]);
        } else {
            $this->json([
                'success' => false,
                'message' => t('messages.error.general')
            ], 500);
        }
    }

    public function markAsFailed(array $params): void
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
        $payment = Payment::find($id);

        if (!$payment) {
            $this->json([
                'success' => false,
                'message' => t('messages.error.not_found')
            ], 404);
        }

        $reason = $this->input['reason'] ?? 'Payment failed';

        if ($payment->markAsFailed($reason)) {
            // Recalculate invoice status
            $invoice = $payment->invoice();
            $invoice->recalculateStatus();

            $this->json([
                'success' => true,
                'message' => 'Payment marked as failed successfully.'
            ]);
        } else {
            $this->json([
                'success' => false,
                'message' => 'Cannot mark payment as failed in current status.'
            ], 400);
        }
    }

    public function refund(array $params): void
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
        $payment = Payment::find($id);

        if (!$payment) {
            $this->json([
                'success' => false,
                'message' => t('messages.error.not_found')
            ], 404);
        }

        $refundAmount = (float)($this->input['amount'] ?? $payment->amount);
        $reason = $this->input['reason'] ?? 'Payment refunded';

        if ($refundAmount <= 0 || $refundAmount > $payment->amount) {
            $this->json([
                'success' => false,
                'message' => 'Invalid refund amount.'
            ], 400);
        }

        if ($payment->refund($refundAmount, $reason)) {
            // Recalculate invoice status
            $invoice = $payment->invoice();
            $invoice->recalculateStatus();

            $this->json([
                'success' => true,
                'message' => 'Payment refunded successfully.'
            ]);
        } else {
            $this->json([
                'success' => false,
                'message' => 'Cannot refund payment in current status.'
            ], 400);
        }
    }

    public function getInvoiceDetails(): void
    {
        $this->requireAuth();

        $invoiceId = $this->input['invoice_id'] ?? 0;
        $invoice = Invoice::find($invoiceId);

        if (!$invoice) {
            $this->json([
                'success' => false,
                'message' => 'Invoice not found'
            ], 404);
        }

        $client = $invoice->client();

        $this->json([
            'success' => true,
            'data' => [
                'id' => $invoice->id,
                'invoice_number' => $invoice->invoice_number,
                'client_name' => $client->getDisplayName(),
                'total_amount' => $invoice->total_amount,
                'paid_amount' => $invoice->getPaidAmount(),
                'outstanding_amount' => $invoice->getOutstandingAmount(),
                'due_date' => $invoice->due_date,
                'is_overdue' => $invoice->isOverdue()
            ]
        ]);
    }

    public function bulkImport(): void
    {
        $this->requireAuth();
        $this->requireRole('admin');

        $this->view('payments/bulk_import', [
            'page_title' => t('payments.bulk_import')
        ]);
    }

    public function processBulkImport(): void
    {
        $this->requireAuth();
        $this->requireRole('admin');

        if (!$this->verifyCsrf()) {
            $this->json([
                'success' => false,
                'message' => t('messages.error.invalid_csrf')
            ], 419);
        }

        // Handle CSV file upload and processing
        $file = $_FILES['csv_file'] ?? null;
        if (!$file || $file['error'] !== UPLOAD_ERR_OK) {
            $this->json([
                'success' => false,
                'message' => 'No valid CSV file uploaded.'
            ], 400);
        }

        $csvData = file_get_contents($file['tmp_name']);
        $rows = str_getcsv($csvData, "\n");
        
        $imported = 0;
        $errors = [];

        foreach ($rows as $index => $row) {
            if ($index === 0) continue; // Skip header
            
            $data = str_getcsv($row);
            
            // Expected format: invoice_number, amount, payment_method, payment_date, reference
            if (count($data) < 5) {
                $errors[] = "Row " . ($index + 1) . ": Insufficient data";
                continue;
            }

            $invoice = Invoice::where('invoice_number', trim($data[0]))->first();
            if (!$invoice) {
                $errors[] = "Row " . ($index + 1) . ": Invoice not found";
                continue;
            }

            $amount = (float)trim($data[1]);
            if ($amount <= 0) {
                $errors[] = "Row " . ($index + 1) . ": Invalid amount";
                continue;
            }

            // Create payment
            $paymentData = [
                'payment_number' => Payment::generatePaymentNumber(),
                'invoice_id' => $invoice->id,
                'client_id' => $invoice->client_id,
                'amount' => $amount,
                'payment_method' => trim($data[2]),
                'payment_date' => trim($data[3]),
                'reference_number' => trim($data[4]) ?? '',
                'status' => Payment::STATUS_COMPLETED,
                'recorded_by' => $this->getCurrentUser()['id']
            ];

            $payment = Payment::create($paymentData);
            if ($payment) {
                $invoice->applyPayment($payment);
                $imported++;
            } else {
                $errors[] = "Row " . ($index + 1) . ": Failed to create payment";
            }
        }

        $this->json([
            'success' => true,
            'message' => "Imported {$imported} payments successfully.",
            'imported' => $imported,
            'errors' => $errors
        ]);
    }
}