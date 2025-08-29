<?php

/**
 * File: app/controllers/WarehouseController.php
 * Purpose: Warehouse and location management controller
 * Depends on: Controller, Warehouse model, Product model
 * Notes: Handles warehouse operations, stock transfers, location tracking
 */

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Warehouse;
use App\Models\Product;

class WarehouseController extends Controller
{
    public function index(array $params = []): void
    {
        $this->requireAuth();

        $status = $this->input['status'] ?? 'all';
        $search = $this->input['search'] ?? '';
        
        $warehouses = [];
        
        if ($search) {
            $warehouses = Warehouse::where('name', 'LIKE', "%{$search}%")
                                  ->orWhere('code', 'LIKE', "%{$search}%")
                                  ->orWhere('location', 'LIKE', "%{$search}%")
                                  ->get();
        } else {
            switch ($status) {
                case 'active':
                    $warehouses = Warehouse::where('status', Warehouse::STATUS_ACTIVE)->orderBy('name')->get();
                    break;
                case 'inactive':
                    $warehouses = Warehouse::where('status', Warehouse::STATUS_INACTIVE)->orderBy('name')->get();
                    break;
                default:
                    $warehouses = Warehouse::orderBy('name')->get();
            }
        }

        $this->view('warehouses/index', [
            'warehouses' => $warehouses,
            'search' => $search,
            'status' => $status,
            'page_title' => t('nav.warehouses')
        ]);
    }

    public function create(): void
    {
        $this->requireAuth();
        $this->requireRole('manager');

        $this->view('warehouses/create', [
            'warehouse' => new Warehouse(),
            'page_title' => t('warehouses.add_warehouse')
        ]);
    }

    public function store(): void
    {
        $this->requireAuth();
        $this->requireRole('manager');

        if (!$this->verifyCsrf()) {
            $this->setFlash('error', t('messages.error.invalid_csrf'));
            $this->back();
        }

        if (!$this->validate([
            'name' => 'required|min:2|max:200',
            'code' => 'required|min:2|max:50',
            'capacity' => 'numeric'
        ])) {
            $this->flashInput();
            $this->back();
        }

        // Check code uniqueness
        if (Warehouse::findByCode($this->input['code'])) {
            $this->setFlash('error', 'Warehouse code already exists. Please use a different code.');
            $this->flashInput();
            $this->back();
        }

        $warehouseData = $this->sanitizeInput([
            'name' => $this->input['name'],
            'code' => strtoupper($this->input['code']),
            'description' => $this->input['description'] ?? '',
            'location' => $this->input['location'] ?? '',
            'address' => $this->input['address'] ?? '',
            'city' => $this->input['city'] ?? '',
            'postal_code' => $this->input['postal_code'] ?? '',
            'country' => $this->input['country'] ?? '',
            'manager_name' => $this->input['manager_name'] ?? '',
            'contact_phone' => $this->input['contact_phone'] ?? '',
            'contact_email' => $this->input['contact_email'] ?? '',
            'capacity' => $this->input['capacity'] ?? null,
            'temperature_controlled' => isset($this->input['temperature_controlled']) ? 1 : 0,
            'security_level' => $this->input['security_level'] ?? 'standard',
            'operating_hours' => $this->input['operating_hours'] ?? '',
            'notes' => $this->input['notes'] ?? '',
            'status' => Warehouse::STATUS_ACTIVE
        ]);

        $warehouse = Warehouse::create($warehouseData);

        if ($warehouse) {
            $this->clearOldInput();
            $this->setFlash('success', t('messages.success.created'));
            $this->redirect('/warehouses/' . $warehouse->id);
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
        $warehouse = Warehouse::find($id);

        if (!$warehouse) {
            $this->setFlash('error', t('messages.error.not_found'));
            $this->redirect('/warehouses');
        }

        // Get warehouse statistics
        $products = $warehouse->getProducts();
        $stats = [
            'total_products' => count($products),
            'total_stock_value' => array_sum(array_map(function($p) { 
                return $p->getStockValue(); 
            }, $products)),
            'low_stock_products' => count(array_filter($products, function($p) { 
                return $p->isLowStock(); 
            })),
            'out_of_stock_products' => count(array_filter($products, function($p) { 
                return $p->isOutOfStock(); 
            })),
            'capacity_used' => $warehouse->getCapacityUsed(),
            'capacity_percentage' => $warehouse->getCapacityPercentage()
        ];

        $this->view('warehouses/show', [
            'warehouse' => $warehouse,
            'products' => $products,
            'stats' => $stats,
            'page_title' => $warehouse->name
        ]);
    }

    public function edit(array $params): void
    {
        $this->requireAuth();
        $this->requireRole('manager');

        $id = $params['id'] ?? 0;
        $warehouse = Warehouse::find($id);

        if (!$warehouse) {
            $this->setFlash('error', t('messages.error.not_found'));
            $this->redirect('/warehouses');
        }

        $this->view('warehouses/edit', [
            'warehouse' => $warehouse,
            'page_title' => t('common.edit') . ' - ' . $warehouse->name
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
        $warehouse = Warehouse::find($id);

        if (!$warehouse) {
            $this->setFlash('error', t('messages.error.not_found'));
            $this->redirect('/warehouses');
        }

        if (!$this->validate([
            'name' => 'required|min:2|max:200',
            'code' => 'required|min:2|max:50',
            'capacity' => 'numeric'
        ])) {
            $this->flashInput();
            $this->back();
        }

        // Check code uniqueness (excluding current warehouse)
        $existingWarehouse = Warehouse::findByCode($this->input['code']);
        if ($existingWarehouse && $existingWarehouse->id !== $warehouse->id) {
            $this->setFlash('error', 'Warehouse code already exists. Please use a different code.');
            $this->flashInput();
            $this->back();
        }

        $warehouseData = $this->sanitizeInput([
            'name' => $this->input['name'],
            'code' => strtoupper($this->input['code']),
            'description' => $this->input['description'] ?? '',
            'location' => $this->input['location'] ?? '',
            'address' => $this->input['address'] ?? '',
            'city' => $this->input['city'] ?? '',
            'postal_code' => $this->input['postal_code'] ?? '',
            'country' => $this->input['country'] ?? '',
            'manager_name' => $this->input['manager_name'] ?? '',
            'contact_phone' => $this->input['contact_phone'] ?? '',
            'contact_email' => $this->input['contact_email'] ?? '',
            'capacity' => $this->input['capacity'] ?? null,
            'temperature_controlled' => isset($this->input['temperature_controlled']) ? 1 : 0,
            'security_level' => $this->input['security_level'] ?? 'standard',
            'operating_hours' => $this->input['operating_hours'] ?? '',
            'notes' => $this->input['notes'] ?? '',
            'status' => $this->input['status'] ?? Warehouse::STATUS_ACTIVE
        ]);

        $warehouse->fill($warehouseData);
        
        if ($warehouse->save()) {
            $this->clearOldInput();
            $this->setFlash('success', t('messages.success.updated'));
            $this->redirect('/warehouses/' . $warehouse->id);
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
        $warehouse = Warehouse::find($id);

        if (!$warehouse) {
            $this->json([
                'success' => false,
                'message' => t('messages.error.not_found')
            ], 404);
        }

        // Check if warehouse has products
        $products = $warehouse->getProducts();
        if (!empty($products)) {
            $this->json([
                'success' => false,
                'message' => 'Cannot delete warehouse with existing products.'
            ], 400);
        }

        if ($warehouse->delete()) {
            $this->json([
                'success' => true,
                'message' => t('messages.success.deleted'),
                'redirect' => '/warehouses'
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
        $warehouse = Warehouse::find($id);

        if (!$warehouse) {
            $this->json([
                'success' => false,
                'message' => t('messages.error.not_found')
            ], 404);
        }

        if ($warehouse->activate()) {
            $this->json([
                'success' => true,
                'message' => 'Warehouse activated successfully.'
            ]);
        } else {
            $this->json([
                'success' => false,
                'message' => t('messages.error.general')
            ], 500);
        }
    }

    public function deactivate(array $params): void
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
        $warehouse = Warehouse::find($id);

        if (!$warehouse) {
            $this->json([
                'success' => false,
                'message' => t('messages.error.not_found')
            ], 404);
        }

        if ($warehouse->deactivate()) {
            $this->json([
                'success' => true,
                'message' => 'Warehouse deactivated successfully.'
            ]);
        } else {
            $this->json([
                'success' => false,
                'message' => t('messages.error.general')
            ], 500);
        }
    }

    public function stockTransfer(): void
    {
        $this->requireAuth();
        $this->requireRole('manager');

        $warehouses = Warehouse::getActiveWarehouses();
        $products = Product::getActiveProducts();

        $this->view('warehouses/stock_transfer', [
            'warehouses' => $warehouses,
            'products' => $products,
            'page_title' => t('warehouses.stock_transfer')
        ]);
    }

    public function processStockTransfer(): void
    {
        $this->requireAuth();
        $this->requireRole('manager');

        if (!$this->verifyCsrf()) {
            $this->json([
                'success' => false,
                'message' => t('messages.error.invalid_csrf')
            ], 419);
        }

        if (!$this->validate([
            'from_warehouse_id' => 'required|integer',
            'to_warehouse_id' => 'required|integer',
            'product_id' => 'required|integer',
            'quantity' => 'required|integer|min:1'
        ])) {
            $this->json([
                'success' => false,
                'message' => 'Invalid input data.'
            ], 400);
        }

        $fromWarehouse = Warehouse::find($this->input['from_warehouse_id']);
        $toWarehouse = Warehouse::find($this->input['to_warehouse_id']);
        $product = Product::find($this->input['product_id']);

        if (!$fromWarehouse || !$toWarehouse || !$product) {
            $this->json([
                'success' => false,
                'message' => 'Invalid warehouse or product selection.'
            ], 400);
        }

        if ($fromWarehouse->id === $toWarehouse->id) {
            $this->json([
                'success' => false,
                'message' => 'Source and destination warehouses cannot be the same.'
            ], 400);
        }

        $quantity = (int)$this->input['quantity'];
        $notes = $this->input['notes'] ?? '';

        // Check if source warehouse has sufficient stock
        $sourceStock = $fromWarehouse->getProductStock($product->id);
        if ($sourceStock < $quantity) {
            $this->json([
                'success' => false,
                'message' => 'Insufficient stock in source warehouse.'
            ], 400);
        }

        // Perform stock transfer
        $transfer = $fromWarehouse->transferStock(
            $toWarehouse,
            $product,
            $quantity,
            $notes,
            $this->getCurrentUser()['id']
        );

        if ($transfer) {
            $this->json([
                'success' => true,
                'message' => 'Stock transfer completed successfully.',
                'transfer_id' => $transfer->id
            ]);
        } else {
            $this->json([
                'success' => false,
                'message' => 'Stock transfer failed.'
            ], 500);
        }
    }

    public function stockReport(array $params): void
    {
        $this->requireAuth();

        $id = $params['id'] ?? 0;
        $warehouse = Warehouse::find($id);

        if (!$warehouse) {
            $this->setFlash('error', t('messages.error.not_found'));
            $this->redirect('/warehouses');
        }

        $category = $this->input['category'] ?? '';
        $stock_filter = $this->input['stock_filter'] ?? 'all';

        $products = $warehouse->getProducts();

        // Apply category filter
        if ($category) {
            $products = array_filter($products, function($p) use ($category) {
                return $p->category_id == $category;
            });
        }

        // Apply stock filter
        switch ($stock_filter) {
            case 'low_stock':
                $products = array_filter($products, function($p) {
                    return $p->isLowStock();
                });
                break;
            case 'out_of_stock':
                $products = array_filter($products, function($p) {
                    return $p->isOutOfStock();
                });
                break;
            case 'overstock':
                $products = array_filter($products, function($p) {
                    return $p->isOverstock();
                });
                break;
        }

        $this->view('warehouses/stock_report', [
            'warehouse' => $warehouse,
            'products' => $products,
            'category' => $category,
            'stock_filter' => $stock_filter,
            'page_title' => t('warehouses.stock_report') . ' - ' . $warehouse->name
        ]);
    }

    public function getProductStock(): void
    {
        $this->requireAuth();

        $warehouseId = $this->input['warehouse_id'] ?? 0;
        $productId = $this->input['product_id'] ?? 0;

        $warehouse = Warehouse::find($warehouseId);
        $product = Product::find($productId);

        if (!$warehouse || !$product) {
            $this->json([
                'success' => false,
                'message' => 'Invalid warehouse or product ID'
            ], 404);
        }

        $stock = $warehouse->getProductStock($productId);

        $this->json([
            'success' => true,
            'data' => [
                'warehouse_id' => $warehouse->id,
                'warehouse_name' => $warehouse->name,
                'product_id' => $product->id,
                'product_name' => $product->name,
                'stock_quantity' => $stock,
                'is_available' => $stock > 0
            ]
        ]);
    }
}