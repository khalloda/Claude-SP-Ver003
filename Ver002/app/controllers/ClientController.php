<?php

/**
 * File: app/controllers/ClientController.php
 * Purpose: Client management controller with full CRUD operations
 * Depends on: Controller, Client model, Auth
 * Notes: Handles client creation, editing, viewing, and management
 */

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Client;

class ClientController extends Controller
{
    public function index(array $params = []): void
    {
        $this->requireAuth();

        $search = $this->input['search'] ?? '';
        $status = $this->input['status'] ?? 'all';
        
        $clients = [];
        if ($search) {
            $clients = Client::searchClients($search);
        } else {
            switch ($status) {
                case 'active':
                    $clients = Client::getActiveClients();
                    break;
                case 'suspended':
                    $clients = Client::where('status', Client::STATUS_SUSPENDED)->get();
                    break;
                default:
                    $clients = Client::all();
            }
        }

        $this->view('clients/index', [
            'clients' => $clients,
            'search' => $search,
            'status' => $status,
            'page_title' => t('nav.clients')
        ]);
    }

    public function create(): void
    {
        $this->requireAuth();

        $this->view('clients/create', [
            'page_title' => t('clients.add_client'),
            'client' => new Client(),
            'current_user' => $this->getCurrentUser()
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
            'company_name' => 'required|min:2|max:200',
            'email' => 'email',
            'phone' => 'min:10|max:20',
            'credit_limit' => 'numeric',
            'discount_percentage' => 'numeric'
        ])) {
            $this->flashInput();
            $this->back();
        }

        $clientData = $this->sanitizeInput([
            'company_name' => $this->input['company_name'],
            'contact_person' => $this->input['contact_person'] ?? '',
            'email' => $this->input['email'] ?? '',
            'phone' => $this->input['phone'] ?? '',
            'mobile' => $this->input['mobile'] ?? '',
            'address' => $this->input['address'] ?? '',
            'city' => $this->input['city'] ?? '',
            'postal_code' => $this->input['postal_code'] ?? '',
            'country' => $this->input['country'] ?? '',
            'tax_number' => $this->input['tax_number'] ?? '',
            'credit_limit' => $this->input['credit_limit'] ?? 0,
            'payment_terms' => $this->input['payment_terms'] ?? '',
            'discount_percentage' => $this->input['discount_percentage'] ?? 0,
            'notes' => $this->input['notes'] ?? '',
            'status' => Client::STATUS_ACTIVE
        ]);

        $client = Client::create($clientData);

        if ($client) {
            $this->clearOldInput();
            $this->setFlash('success', t('messages.success.created'));
            $this->redirect('/clients/' . $client->id);
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
        $client = Client::find($id);

        if (!$client) {
            $this->setFlash('error', t('messages.error.not_found'));
            $this->redirect('/clients');
        }

        // Get client statistics
        $stats = [
            'total_quotes' => $client->getTotalQuotes(),
            'total_orders' => $client->getTotalOrders(),
            'total_invoices' => $client->getTotalInvoices(),
            'total_sales' => $client->getTotalSales(),
            'outstanding_balance' => $client->getOutstandingBalance(),
            'available_credit' => $client->getAvailableCredit()
        ];

        $this->view('clients/show', [
            'client' => $client,
            'stats' => $stats,
            'page_title' => $client->getDisplayName(),
            'current_user' => $this->getCurrentUser()
        ]);
    }

    public function edit(array $params): void
    {
        $this->requireAuth();

        $id = $params['id'] ?? 0;
        $client = Client::find($id);

        if (!$client) {
            $this->setFlash('error', t('messages.error.not_found'));
            $this->redirect('/clients');
        }

        $this->view('clients/edit', [
            'client' => $client,
            'page_title' => t('common.edit') . ' - ' . $client->getDisplayName(),
            'current_user' => $this->getCurrentUser()
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
        $client = Client::find($id);

        if (!$client) {
            $this->setFlash('error', t('messages.error.not_found'));
            $this->redirect('/clients');
        }

        if (!$this->validate([
            'company_name' => 'required|min:2|max:200',
            'email' => 'email',
            'phone' => 'min:10|max:20',
            'credit_limit' => 'numeric',
            'discount_percentage' => 'numeric'
        ])) {
            $this->flashInput();
            $this->back();
        }

        $clientData = $this->sanitizeInput([
            'company_name' => $this->input['company_name'],
            'contact_person' => $this->input['contact_person'] ?? '',
            'email' => $this->input['email'] ?? '',
            'phone' => $this->input['phone'] ?? '',
            'mobile' => $this->input['mobile'] ?? '',
            'address' => $this->input['address'] ?? '',
            'city' => $this->input['city'] ?? '',
            'postal_code' => $this->input['postal_code'] ?? '',
            'country' => $this->input['country'] ?? '',
            'tax_number' => $this->input['tax_number'] ?? '',
            'credit_limit' => $this->input['credit_limit'] ?? 0,
            'payment_terms' => $this->input['payment_terms'] ?? '',
            'discount_percentage' => $this->input['discount_percentage'] ?? 0,
            'notes' => $this->input['notes'] ?? '',
            'status' => $this->input['status'] ?? Client::STATUS_ACTIVE
        ]);

        $client->fill($clientData);
        
        if ($client->save()) {
            $this->clearOldInput();
            $this->setFlash('success', t('messages.success.updated'));
            $this->redirect('/clients/' . $client->id);
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
        $client = Client::find($id);

        if (!$client) {
            $this->json([
                'success' => false,
                'message' => t('messages.error.not_found')
            ], 404);
        }

        // Check if client has related records
        if ($client->getTotalQuotes() > 0 || $client->getTotalOrders() > 0 || $client->getTotalInvoices() > 0) {
            $this->json([
                'success' => false,
                'message' => 'Cannot delete client with existing quotes, orders, or invoices.'
            ], 400);
        }

        if ($client->delete()) {
            $this->json([
                'success' => true,
                'message' => t('messages.success.deleted'),
                'redirect' => '/clients'
            ]);
        } else {
            $this->json([
                'success' => false,
                'message' => t('messages.error.general')
            ], 500);
        }
    }

    public function activate(array $params): void
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
        $client = Client::find($id);

        if (!$client) {
            $this->json([
                'success' => false,
                'message' => t('messages.error.not_found')
            ], 404);
        }

        if ($client->activate()) {
            $this->json([
                'success' => true,
                'message' => 'Client activated successfully.'
            ]);
        } else {
            $this->json([
                'success' => false,
                'message' => t('messages.error.general')
            ], 500);
        }
    }

    public function suspend(array $params): void
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
        $client = Client::find($id);

        if (!$client) {
            $this->json([
                'success' => false,
                'message' => t('messages.error.not_found')
            ], 404);
        }

        if ($client->suspend()) {
            $this->json([
                'success' => true,
                'message' => 'Client suspended successfully.'
            ]);
        } else {
            $this->json([
                'success' => false,
                'message' => t('messages.error.general')
            ], 500);
        }
    }
}