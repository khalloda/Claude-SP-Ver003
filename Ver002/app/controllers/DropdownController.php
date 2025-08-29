<?php

/**
 * File: app/controllers/DropdownController.php
 * Purpose: Dropdown data management controller for system lookups
 * Depends on: Controller, Dropdown model
 * Notes: Handles categories, units, currencies, and other reference data
 */

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Dropdown;

class DropdownController extends Controller
{
    public function index(array $params = []): void
    {
        $this->requireAuth();
        $this->requireRole('admin');

        $type = $this->input['type'] ?? 'all';
        $search = $this->input['search'] ?? '';
        
        $dropdowns = [];
        
        if ($search) {
            $dropdowns = Dropdown::where('name', 'LIKE', "%{$search}%")
                                 ->orWhere('value', 'LIKE', "%{$search}%")
                                 ->orderBy('type', 'name')
                                 ->get();
        } else {
            if ($type !== 'all') {
                $dropdowns = Dropdown::where('type', $type)->orderBy('sort_order', 'name')->get();
            } else {
                $dropdowns = Dropdown::orderBy('type', 'sort_order', 'name')->get();
            }
        }

        $types = Dropdown::getAvailableTypes();

        $this->view('dropdowns/index', [
            'dropdowns' => $dropdowns,
            'types' => $types,
            'type' => $type,
            'search' => $search,
            'page_title' => t('nav.system_data')
        ]);
    }

    public function create(): void
    {
        $this->requireAuth();
        $this->requireRole('admin');

        $types = Dropdown::getAvailableTypes();
        $type = $this->input['type'] ?? '';

        $this->view('dropdowns/create', [
            'dropdown' => new Dropdown(),
            'types' => $types,
            'selected_type' => $type,
            'page_title' => t('dropdowns.add_item')
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
            'type' => 'required|min:2|max:50',
            'name' => 'required|min:1|max:200',
            'value' => 'required|min:1|max:200'
        ])) {
            $this->flashInput();
            $this->back();
        }

        // Check for duplicate values within the same type
        $existing = Dropdown::where('type', $this->input['type'])
                           ->where('value', $this->input['value'])
                           ->first();
        
        if ($existing) {
            $this->setFlash('error', 'Value already exists for this type.');
            $this->flashInput();
            $this->back();
        }

        $dropdownData = $this->sanitizeInput([
            'type' => $this->input['type'],
            'name' => $this->input['name'],
            'value' => $this->input['value'],
            'description' => $this->input['description'] ?? '',
            'sort_order' => $this->input['sort_order'] ?? 0,
            'is_active' => isset($this->input['is_active']) ? 1 : 0,
            'is_default' => isset($this->input['is_default']) ? 1 : 0
        ]);

        // If setting as default, unset other defaults for this type
        if ($dropdownData['is_default']) {
            Dropdown::where('type', $dropdownData['type'])
                   ->where('is_default', 1)
                   ->update(['is_default' => 0]);
        }

        $dropdown = Dropdown::create($dropdownData);

        if ($dropdown) {
            $this->clearOldInput();
            $this->setFlash('success', t('messages.success.created'));
            $this->redirect('/dropdowns?type=' . $dropdown->type);
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
        $dropdown = Dropdown::find($id);

        if (!$dropdown) {
            $this->setFlash('error', t('messages.error.not_found'));
            $this->redirect('/dropdowns');
        }

        $this->view('dropdowns/show', [
            'dropdown' => $dropdown,
            'page_title' => $dropdown->name
        ]);
    }

    public function edit(array $params): void
    {
        $this->requireAuth();
        $this->requireRole('admin');

        $id = $params['id'] ?? 0;
        $dropdown = Dropdown::find($id);

        if (!$dropdown) {
            $this->setFlash('error', t('messages.error.not_found'));
            $this->redirect('/dropdowns');
        }

        $types = Dropdown::getAvailableTypes();

        $this->view('dropdowns/edit', [
            'dropdown' => $dropdown,
            'types' => $types,
            'page_title' => t('common.edit') . ' - ' . $dropdown->name
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
        $dropdown = Dropdown::find($id);

        if (!$dropdown) {
            $this->setFlash('error', t('messages.error.not_found'));
            $this->redirect('/dropdowns');
        }

        if (!$this->validate([
            'type' => 'required|min:2|max:50',
            'name' => 'required|min:1|max:200',
            'value' => 'required|min:1|max:200'
        ])) {
            $this->flashInput();
            $this->back();
        }

        // Check for duplicate values within the same type (excluding current record)
        $existing = Dropdown::where('type', $this->input['type'])
                           ->where('value', $this->input['value'])
                           ->where('id', '!=', $dropdown->id)
                           ->first();
        
        if ($existing) {
            $this->setFlash('error', 'Value already exists for this type.');
            $this->flashInput();
            $this->back();
        }

        $dropdownData = $this->sanitizeInput([
            'type' => $this->input['type'],
            'name' => $this->input['name'],
            'value' => $this->input['value'],
            'description' => $this->input['description'] ?? '',
            'sort_order' => $this->input['sort_order'] ?? 0,
            'is_active' => isset($this->input['is_active']) ? 1 : 0,
            'is_default' => isset($this->input['is_default']) ? 1 : 0
        ]);

        // If setting as default, unset other defaults for this type
        if ($dropdownData['is_default'] && !$dropdown->is_default) {
            Dropdown::where('type', $dropdownData['type'])
                   ->where('is_default', 1)
                   ->where('id', '!=', $dropdown->id)
                   ->update(['is_default' => 0]);
        }

        $dropdown->fill($dropdownData);
        
        if ($dropdown->save()) {
            $this->clearOldInput();
            $this->setFlash('success', t('messages.success.updated'));
            $this->redirect('/dropdowns?type=' . $dropdown->type);
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
        $dropdown = Dropdown::find($id);

        if (!$dropdown) {
            $this->json([
                'success' => false,
                'message' => t('messages.error.not_found')
            ], 404);
        }

        // Check if dropdown is being used
        if ($dropdown->isInUse()) {
            $this->json([
                'success' => false,
                'message' => 'Cannot delete dropdown item that is currently in use.'
            ], 400);
        }

        if ($dropdown->delete()) {
            $this->json([
                'success' => true,
                'message' => t('messages.success.deleted'),
                'redirect' => '/dropdowns?type=' . $dropdown->type
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
        $this->requireRole('admin');

        if (!$this->verifyCsrf()) {
            $this->json([
                'success' => false,
                'message' => t('messages.error.invalid_csrf')
            ], 419);
        }

        $id = $params['id'] ?? 0;
        $dropdown = Dropdown::find($id);

        if (!$dropdown) {
            $this->json([
                'success' => false,
                'message' => t('messages.error.not_found')
            ], 404);
        }

        $dropdown->is_active = 1;
        
        if ($dropdown->save()) {
            $this->json([
                'success' => true,
                'message' => 'Item activated successfully.'
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
        $this->requireRole('admin');

        if (!$this->verifyCsrf()) {
            $this->json([
                'success' => false,
                'message' => t('messages.error.invalid_csrf')
            ], 419);
        }

        $id = $params['id'] ?? 0;
        $dropdown = Dropdown::find($id);

        if (!$dropdown) {
            $this->json([
                'success' => false,
                'message' => t('messages.error.not_found')
            ], 404);
        }

        // Don't allow deactivating default items
        if ($dropdown->is_default) {
            $this->json([
                'success' => false,
                'message' => 'Cannot deactivate default item.'
            ], 400);
        }

        $dropdown->is_active = 0;
        
        if ($dropdown->save()) {
            $this->json([
                'success' => true,
                'message' => 'Item deactivated successfully.'
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
        $dropdown = Dropdown::find($id);

        if (!$dropdown) {
            $this->json([
                'success' => false,
                'message' => t('messages.error.not_found')
            ], 404);
        }

        // Unset other defaults for this type
        Dropdown::where('type', $dropdown->type)
               ->where('is_default', 1)
               ->update(['is_default' => 0]);

        $dropdown->is_default = 1;
        $dropdown->is_active = 1; // Default items must be active
        
        if ($dropdown->save()) {
            $this->json([
                'success' => true,
                'message' => 'Default item set successfully.'
            ]);
        } else {
            $this->json([
                'success' => false,
                'message' => t('messages.error.general')
            ], 500);
        }
    }

    public function reorder(): void
    {
        $this->requireAuth();
        $this->requireRole('admin');

        if (!$this->verifyCsrf()) {
            $this->json([
                'success' => false,
                'message' => t('messages.error.invalid_csrf')
            ], 419);
        }

        $items = $this->input['items'] ?? [];
        if (empty($items) || !is_array($items)) {
            $this->json([
                'success' => false,
                'message' => 'Invalid items data.'
            ], 400);
        }

        $updated = 0;
        foreach ($items as $item) {
            if (!isset($item['id']) || !isset($item['sort_order'])) {
                continue;
            }

            $dropdown = Dropdown::find($item['id']);
            if ($dropdown) {
                $dropdown->sort_order = (int)$item['sort_order'];
                if ($dropdown->save()) {
                    $updated++;
                }
            }
        }

        $this->json([
            'success' => true,
            'message' => "Updated sort order for {$updated} items."
        ]);
    }

    public function bulkImport(): void
    {
        $this->requireAuth();
        $this->requireRole('admin');

        $types = Dropdown::getAvailableTypes();

        $this->view('dropdowns/bulk_import', [
            'types' => $types,
            'page_title' => t('dropdowns.bulk_import')
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

        $type = $this->input['type'] ?? '';
        if (empty($type)) {
            $this->json([
                'success' => false,
                'message' => 'Type is required.'
            ], 400);
        }

        $csvData = file_get_contents($file['tmp_name']);
        $rows = str_getcsv($csvData, "\n");
        
        $imported = 0;
        $errors = [];

        foreach ($rows as $index => $row) {
            if ($index === 0) continue; // Skip header
            
            $data = str_getcsv($row);
            
            // Expected format: name, value, description, sort_order
            if (count($data) < 2) {
                $errors[] = "Row " . ($index + 1) . ": Insufficient data";
                continue;
            }

            $name = trim($data[0]);
            $value = trim($data[1]);
            $description = isset($data[2]) ? trim($data[2]) : '';
            $sortOrder = isset($data[3]) ? (int)trim($data[3]) : 0;

            if (empty($name) || empty($value)) {
                $errors[] = "Row " . ($index + 1) . ": Name and value are required";
                continue;
            }

            // Check for duplicates
            $existing = Dropdown::where('type', $type)
                               ->where('value', $value)
                               ->first();
            
            if ($existing) {
                $errors[] = "Row " . ($index + 1) . ": Value already exists";
                continue;
            }

            // Create dropdown item
            $dropdownData = [
                'type' => $type,
                'name' => $name,
                'value' => $value,
                'description' => $description,
                'sort_order' => $sortOrder,
                'is_active' => 1,
                'is_default' => 0
            ];

            $dropdown = Dropdown::create($dropdownData);
            if ($dropdown) {
                $imported++;
            } else {
                $errors[] = "Row " . ($index + 1) . ": Failed to create item";
            }
        }

        $this->json([
            'success' => true,
            'message' => "Imported {$imported} items successfully.",
            'imported' => $imported,
            'errors' => $errors
        ]);
    }

    // API endpoints for dropdown data
    public function getByType(array $params): void
    {
        $this->requireAuth();

        $type = $params['type'] ?? '';
        if (empty($type)) {
            $this->json([
                'success' => false,
                'message' => 'Type parameter is required'
            ], 400);
        }

        $items = Dropdown::getByType($type, true); // Only active items

        $this->json([
            'success' => true,
            'data' => array_map(function($item) {
                return [
                    'id' => $item->id,
                    'name' => $item->name,
                    'value' => $item->value,
                    'is_default' => $item->is_default
                ];
            }, $items)
        ]);
    }
}