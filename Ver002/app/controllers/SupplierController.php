<?php

/**
 * File: app/controllers/SupplierController.php
 * Purpose: Supplier management controller with CRUD operations
 * Depends on: Controller, Supplier model
 * Notes: Handles supplier creation, editing, viewing, and management
 */

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Supplier;

class SupplierController extends Controller
{
    public function index(array $params = []): void
    {
        $this->requireAuth();

        $search = $this->input['search'] ?? '';
        $status = $this->input['status'] ?? 'all';
        
        $suppliers = [];
        if ($search) {
            $suppliers = Supplier::where('company_name', 'LIKE', "%{$search}%")
                               ->orWhere('contact_person', 'LIKE', "%{$search}%")
                               ->orWhere('email', 'LIKE', "%{$search}%")
                               ->get();
        } else {
            switch ($status) {
                case 'active':
                    $suppliers = Supplier::where('status', 1)->orderBy('company_name')->get();
                    break;
                case 'inactive':
                    $suppliers = Supplier::where('status', 0)->orderBy('company_name')->get();
                    break;
                default:
                    $suppliers = Supplier::orderBy('company_name')->get();
            }
        }

        $this->view('suppliers/index', [
            'suppliers' => $suppliers,
            'search' => $search,
            'status' => $status,
            'page_title' => t('nav.suppliers')
        ]);
    }

    public function create(): void
    {
        $this->requireAuth();

        $this->view('suppliers/create', [
            'supplier' => new Supplier(),
            'page_title' => t('suppliers.add_supplier')
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
            'phone' => 'min:10|max:20'
        ])) {
            $this->flashInput();
            $this->back();
        }

        $supplierData = $this->sanitizeInput([
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
            'payment_terms' => $this->input['payment_terms'] ?? '',
            'notes' => $this->input['notes'] ?? '',
            'status' => 1
        ]);

        $supplier = Supplier::create($supplierData);

        if ($supplier) {
            $this->clearOldInput();
            $this->setFlash('success', t('messages.success.created'));
            $this->redirect('/suppliers/' . $supplier->id);
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
        $supplier = Supplier::find($id);

        if (!$supplier) {
            $this->setFlash('error', t('messages.error.not_found'));
            $this->redirect('/suppliers');
        }

        // Get supplier statistics
        $products = $supplier->products();
        $stats = [
            'total_products' => count($products),
            'active_products' => count(array_filter($products, function($p) { return $p->isActive(); })),
            'total_stock_value' => array_sum(array_map(function($p) { return $p->getStockValue(); }, $products))
        ];

        $this->view('suppliers/show', [
            'supplier' => $supplier,
            'products' => $products,
            'stats' => $stats,
            'page_title' => $supplier->company_name
        ]);
    }

    public function edit(array $params): void
    {
        $this->requireAuth();

        $id = $params['id'] ?? 0;
        $supplier = Supplier::find($id);

        if (!$supplier) {
            $this->setFlash('error', t('messages.error.not_found'));
            $this->redirect('/suppliers');
        }

        $this->view('suppliers/edit', [
            'supplier' => $supplier,
            'page_title' => t('common.edit') . ' - ' . $supplier->company_name
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
        $supplier = Supplier::find($id);

        if (!$supplier) {
            $this->setFlash('error', t('messages.error.not_found'));
            $this->redirect('/suppliers');
        }

        if (!$this->validate([
            'company_name' => 'required|min:2|max:200',
            'email' => 'email',
            'phone' => 'min:10|max:20'
        ])) {
            $this->flashInput();
            $this->back();
        }

        $supplierData = $this->sanitizeInput([
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
            'payment_terms' => $this->input['payment_terms'] ?? '',
            'notes' => $this->input['notes'] ?? '',
            'status' => $this->input['status'] ?? 1
        ]);

        $supplier->fill($supplierData);
        
        if ($supplier->save()) {
            $this->clearOldInput();
            $this->setFlash('success', t('messages.success.updated'));
            $this->redirect('/suppliers/' . $supplier->id);
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
        $supplier = Supplier::find($id);

        if (!$supplier) {
            $this->json([
                'success' => false,
                'message' => t('messages.error.not_found')
            ], 404);
        }

        // Check if supplier has products
        $products = $supplier->products();
        if (!empty($products)) {
            $this->json([
                'success' => false,
                'message' => 'Cannot delete supplier with existing products.'
            ], 400);
        }

        if ($supplier->delete()) {
            $this->json([
                'success' => true,
                'message' => t('messages.success.deleted'),
                'redirect' => '/suppliers'
            ]);
        } else {
            $this->json([
                'success' => false,
                'message' => t('messages.error.general')
            ], 500);
        }
    }
}