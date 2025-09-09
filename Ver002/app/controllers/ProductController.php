<?php

/**
 * File: app/controllers/ProductController.php
 * Purpose: Product management controller with inventory tracking
 * Depends on: Controller, Product model, Dropdown model
 * Notes: Handles product CRUD, stock management, pricing
 */

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Product;
use App\Models\Dropdown;

class ProductController extends Controller
{
    public function index(array $params = []): void
    {
        $this->requireAuth();

        $search = $this->input['search'] ?? '';
        $category = $this->input['category'] ?? '';
        $status = $this->input['status'] ?? 'all';
        $stock_filter = $this->input['stock_filter'] ?? 'all';
        
        $products = [];
        
        if ($search) {
            $products = Product::searchProducts($search);
        } elseif ($category) {
            $products = Product::getProductsByCategory($category);
        } else {
            switch ($status) {
                case 'active':
                    $products = Product::getActiveProducts();
                    break;
                case 'discontinued':
                    $products = Product::where('status', Product::STATUS_DISCONTINUED)->get();
                    break;
                default:
                    $products = Product::all();
            }
        }

        // Apply stock filter
        if ($stock_filter === 'low_stock') {
            $products = array_filter($products, function($product) {
                return $product->isLowStock();
            });
        } elseif ($stock_filter === 'out_of_stock') {
            $products = array_filter($products, function($product) {
                return $product->isOutOfStock();
            });
        }

        $categories = Dropdown::getCategories() ?? [];
        
        $this->view('products/index', [
            'products' => $products ?? [],
            'categories' => $categories,
            'search' => $search,
            'category' => $category,
            'status' => $status,
            'stock_filter' => $stock_filter,
            'page_title' => t('nav.products'),
            'current_user' => $this->getCurrentUser()
        ]);
    }

    public function create(): void
    {
        $this->requireAuth();

        $categories = Dropdown::getCategories();
        $units = Dropdown::getUnitsOfMeasure();

        $this->view('products/create', [
            'product' => new Product(),
            'categories' => $categories,
            'units' => $units,
            'page_title' => t('products.add_product'),
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
            'name' => 'required|min:2|max:255',
            'sku' => 'required|min:3|max:100',
            'purchase_price' => 'required|numeric',
            'selling_price' => 'required|numeric',
            'stock_quantity' => 'required|integer',
            'min_stock_level' => 'required|integer'
        ])) {
            $this->flashInput();
            $this->back();
        }

        // Check SKU uniqueness
        if (Product::findBySku($this->input['sku'])) {
            $this->setFlash('error', 'SKU already exists. Please use a different SKU.');
            $this->flashInput();
            $this->back();
        }

        $productData = $this->sanitizeInput([
            'name' => $this->input['name'],
            'description' => $this->input['description'] ?? '',
            'sku' => strtoupper($this->input['sku']),
            'part_number' => $this->input['part_number'] ?? '',
            'category_id' => $this->input['category_id'] ?? null,
            'supplier_id' => $this->input['supplier_id'] ?? null,
            'purchase_price' => $this->input['purchase_price'],
            'selling_price' => $this->input['selling_price'],
            'stock_quantity' => $this->input['stock_quantity'],
            'min_stock_level' => $this->input['min_stock_level'],
            'unit_of_measure' => $this->input['unit_of_measure'] ?? 'pcs',
            'weight' => $this->input['weight'] ?? null,
            'dimensions' => $this->input['dimensions'] ?? '',
            'location' => $this->input['location'] ?? '',
            'brand' => $this->input['brand'] ?? '',
            'model' => $this->input['model'] ?? '',
            'status' => Product::STATUS_ACTIVE
        ]);

        // Calculate markup percentage
        if ($productData['purchase_price'] > 0) {
            $productData['markup_percentage'] = (($productData['selling_price'] - $productData['purchase_price']) / $productData['purchase_price']) * 100;
        }

        $product = Product::create($productData);

        if ($product) {
            // Log initial stock if quantity > 0
            if ($product->stock_quantity > 0) {
                $product->addStock($product->stock_quantity, 'Initial stock entry');
            }

            $this->clearOldInput();
            $this->setFlash('success', t('messages.success.created'));
            $this->redirect('/products/' . $product->id);
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
        $product = Product::find($id);

        if (!$product) {
            $this->setFlash('error', t('messages.error.not_found'));
            $this->redirect('/products');
        }

        // Get stock movements (last 20)
        $stockMovements = $product->stockMovements();
        $stockMovements = array_slice(array_reverse($stockMovements), 0, 20);

        $this->view('products/show', [
            'product' => $product,
            'stock_movements' => $stockMovements,
            'page_title' => $product->name,
            'current_user' => $this->getCurrentUser()
        ]);
    }

    public function edit(array $params): void
    {
        $this->requireAuth();

        $id = $params['id'] ?? 0;
        $product = Product::find($id);

        if (!$product) {
            $this->setFlash('error', t('messages.error.not_found'));
            $this->redirect('/products');
        }

        $categories = Dropdown::getCategories();
        $units = Dropdown::getUnitsOfMeasure();

        $this->view('products/edit', [
            'product' => $product,
            'categories' => $categories,
            'units' => $units,
            'page_title' => t('common.edit') . ' - ' . $product->name,
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
        $product = Product::find($id);

        if (!$product) {
            $this->setFlash('error', t('messages.error.not_found'));
            $this->redirect('/products');
        }

        if (!$this->validate([
            'name' => 'required|min:2|max:255',
            'sku' => 'required|min:3|max:100',
            'purchase_price' => 'required|numeric',
            'selling_price' => 'required|numeric',
            'min_stock_level' => 'required|integer'
        ])) {
            $this->flashInput();
            $this->back();
        }

        // Check SKU uniqueness (excluding current product)
        $existingProduct = Product::findBySku($this->input['sku']);
        if ($existingProduct && $existingProduct->id !== $product->id) {
            $this->setFlash('error', 'SKU already exists. Please use a different SKU.');
            $this->flashInput();
            $this->back();
        }

        $productData = $this->sanitizeInput([
            'name' => $this->input['name'],
            'description' => $this->input['description'] ?? '',
            'sku' => strtoupper($this->input['sku']),
            'part_number' => $this->input['part_number'] ?? '',
            'category_id' => $this->input['category_id'] ?? null,
            'supplier_id' => $this->input['supplier_id'] ?? null,
            'purchase_price' => $this->input['purchase_price'],
            'selling_price' => $this->input['selling_price'],
            'min_stock_level' => $this->input['min_stock_level'],
            'unit_of_measure' => $this->input['unit_of_measure'] ?? 'pcs',
            'weight' => $this->input['weight'] ?? null,
            'dimensions' => $this->input['dimensions'] ?? '',
            'location' => $this->input['location'] ?? '',
            'brand' => $this->input['brand'] ?? '',
            'model' => $this->input['model'] ?? '',
            'status' => $this->input['status'] ?? Product::STATUS_ACTIVE
        ]);

        // Calculate markup percentage
        if ($productData['purchase_price'] > 0) {
            $productData['markup_percentage'] = (($productData['selling_price'] - $productData['purchase_price']) / $productData['purchase_price']) * 100;
        }

        $product->fill($productData);
        
        if ($product->save()) {
            $this->clearOldInput();
            $this->setFlash('success', t('messages.success.updated'));
            $this->redirect('/products/' . $product->id);
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
        $product = Product::find($id);

        if (!$product) {
            $this->json([
                'success' => false,
                'message' => t('messages.error.not_found')
            ], 404);
        }

        // Check if product has stock movements or is used in quotes/orders
        if ($product->stock_quantity > 0) {
            $this->json([
                'success' => false,
                'message' => 'Cannot delete product with stock quantity. Adjust stock to zero first.'
            ], 400);
        }

        if ($product->delete()) {
            $this->json([
                'success' => true,
                'message' => t('messages.success.deleted'),
                'redirect' => '/products'
            ]);
        } else {
            $this->json([
                'success' => false,
                'message' => t('messages.error.general')
            ], 500);
        }
    }

    public function stock(array $params): void
    {
        $this->requireAuth();
        
        $id = $params['id'] ?? 0;
        $product = Product::find($id);
        
        if (!$product) {
            $this->setFlash('error', t('messages.error.not_found'));
            $this->redirect('/products');
        }
        
        // Get stock movements (last 20)
        $stockMovements = $product->stockMovements();
        $stockMovements = array_slice(array_reverse($stockMovements), 0, 20);
        
        $this->view('products/stock', [
            'product' => $product,
            'stock_movements' => $stockMovements,
            'page_title' => t('products.manage_stock') . ' - ' . $product->name,
            'current_user' => $this->getCurrentUser()
        ]);
    }
    
    public function adjustStock(array $params): void
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
        $product = Product::find($id);

        if (!$product) {
            $this->json([
                'success' => false,
                'message' => t('messages.error.not_found')
            ], 404);
        }

        $adjustment = (int)($this->input['adjustment'] ?? 0);
        $reason = $this->input['reason'] ?? 'Manual adjustment';

        if ($adjustment == 0) {
            $this->json([
                'success' => false,
                'message' => 'Adjustment cannot be zero.'
            ], 400);
        }

        $success = false;
        if ($adjustment > 0) {
            $success = $product->addStock($adjustment, $reason);
        } else {
            $success = $product->removeStock(abs($adjustment), $reason);
        }

        if ($success) {
            $this->json([
                'success' => true,
                'message' => 'Stock adjusted successfully.',
                'new_quantity' => $product->stock_quantity
            ]);
        } else {
            $this->json([
                'success' => false,
                'message' => 'Failed to adjust stock. Insufficient quantity or other error.'
            ], 400);
        }
    }

    public function getDetails(array $params): void
    {
        $this->requireAuth();

        $id = $params['id'] ?? 0;
        $product = Product::find($id);

        if (!$product) {
            $this->json([
                'success' => false,
                'message' => t('messages.error.not_found')
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
                'unit_of_measure' => $product->unit_of_measure,
                'is_available' => $product->stock_quantity > 0
            ]
        ]);
    }
}