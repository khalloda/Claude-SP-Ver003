<?php
/**
 * File: app/views/products/edit.php
 * Purpose: Product editing form with comprehensive inventory management
 * Layout: Uses app layout with professional product editing interface
 */

$page_title = $page_title ?? t('products.edit_product');
$active_nav = 'products';
ob_start();

// Get current user from passed data
$currentUser = $current_user ?? null;
$canEdit = $currentUser && in_array($currentUser['role'] ?? '', ['admin', 'manager', 'warehouse']);
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">
            <i class="fas fa-edit me-2"></i><?= t('products.edit_product') ?>
            <small class="text-muted ms-2"><?= htmlspecialchars($product->name) ?></small>
        </h1>
        <div class="btn-toolbar mb-2 mb-md-0">
            <a href="/products/<?= $product->id ?>" class="btn btn-outline-secondary me-2">
                <i class="fas fa-eye"></i> <?= t('common.view') ?>
            </a>
            <a href="/products" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left"></i> <?= t('common.back') ?>
            </a>
        </div>
    </div>

    <form id="productForm" enctype="multipart/form-data">
        <div class="row">
            <div class="col-md-8">
                <!-- Basic Information -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0"><?= t('products.basic_information') ?></h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label"><?= t('products.name') ?> *</label>
                                    <input type="text" class="form-control" name="name" value="<?= htmlspecialchars($product->name) ?>" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label"><?= t('products.sku') ?> *</label>
                                    <input type="text" class="form-control" name="sku" value="<?= htmlspecialchars($product->sku) ?>" required>
                                    <div class="form-text"><?= t('products.sku_help') ?></div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label"><?= t('products.category') ?></label>
                                    <select class="form-select" name="category_id">
                                        <option value=""><?= t('common.select_category') ?></option>
                                        <?php if (!empty($categories)): ?>
                                        <?php foreach ($categories as $category): ?>
                                        <option value="<?= $category->id ?>" <?= $product->category_id == $category->id ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($category->name) ?>
                                        </option>
                                        <?php endforeach; ?>
                                        <?php endif; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label"><?= t('products.brand') ?></label>
                                    <input type="text" class="form-control" name="brand" value="<?= htmlspecialchars($product->brand ?? '') ?>">
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label"><?= t('products.description') ?></label>
                            <textarea class="form-control" name="description" rows="4"><?= htmlspecialchars($product->description ?? '') ?></textarea>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label"><?= t('products.unit') ?></label>
                                    <select class="form-select" name="unit">
                                        <option value="pcs" <?= ($product->unit ?? 'pcs') === 'pcs' ? 'selected' : '' ?>><?= t('products.units.pcs') ?></option>
                                        <option value="kg" <?= ($product->unit ?? '') === 'kg' ? 'selected' : '' ?>><?= t('products.units.kg') ?></option>
                                        <option value="lbs" <?= ($product->unit ?? '') === 'lbs' ? 'selected' : '' ?>><?= t('products.units.lbs') ?></option>
                                        <option value="meters" <?= ($product->unit ?? '') === 'meters' ? 'selected' : '' ?>><?= t('products.units.meters') ?></option>
                                        <option value="liters" <?= ($product->unit ?? '') === 'liters' ? 'selected' : '' ?>><?= t('products.units.liters') ?></option>
                                        <option value="boxes" <?= ($product->unit ?? '') === 'boxes' ? 'selected' : '' ?>><?= t('products.units.boxes') ?></option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label"><?= t('products.status') ?></label>
                                    <select class="form-select" name="status">
                                        <option value="active" <?= ($product->status ?? 'active') === 'active' ? 'selected' : '' ?>><?= t('products.status.active') ?></option>
                                        <option value="inactive" <?= ($product->status ?? '') === 'inactive' ? 'selected' : '' ?>><?= t('products.status.inactive') ?></option>
                                        <option value="discontinued" <?= ($product->status ?? '') === 'discontinued' ? 'selected' : '' ?>><?= t('products.status.discontinued') ?></option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="track_inventory" <?= !empty($product->track_inventory) ? 'checked' : '' ?>>
                            <label class="form-check-label">
                                <?= t('products.track_inventory') ?>
                            </label>
                            <div class="form-text"><?= t('products.track_inventory_help') ?></div>
                        </div>
                    </div>
                </div>

                <!-- Pricing Information -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0"><?= t('products.pricing_information') ?></h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label"><?= t('products.cost_price') ?></label>
                                    <div class="input-group">
                                        <input type="number" class="form-control" name="cost_price" 
                                               value="<?= $product->cost_price ?? '' ?>" step="0.01" min="0">
                                        <span class="input-group-text"><?= $default_currency ?? 'USD' ?></span>
                                    </div>
                                    <div class="form-text"><?= t('products.cost_price_help') ?></div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label"><?= t('products.selling_price') ?> *</label>
                                    <div class="input-group">
                                        <input type="number" class="form-control" name="selling_price" 
                                               value="<?= $product->selling_price ?? '' ?>" step="0.01" min="0" required>
                                        <span class="input-group-text"><?= $default_currency ?? 'USD' ?></span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label"><?= t('products.msrp') ?></label>
                                    <div class="input-group">
                                        <input type="number" class="form-control" name="msrp" 
                                               value="<?= $product->msrp ?? '' ?>" step="0.01" min="0">
                                        <span class="input-group-text"><?= $default_currency ?? 'USD' ?></span>
                                    </div>
                                    <div class="form-text"><?= t('products.msrp_help') ?></div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label"><?= t('products.profit_margin') ?></label>
                                    <div class="input-group">
                                        <input type="number" class="form-control" name="profit_margin" 
                                               value="<?= $product->profit_margin ?? '' ?>" step="0.01" min="0" max="100" readonly>
                                        <span class="input-group-text">%</span>
                                    </div>
                                    <div class="form-text"><?= t('products.profit_margin_auto') ?></div>
                                </div>
                            </div>
                        </div>

                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="tax_exempt" <?= !empty($product->tax_exempt) ? 'checked' : '' ?>>
                            <label class="form-check-label">
                                <?= t('products.tax_exempt') ?>
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Inventory Management -->
                <div class="card mb-4" id="inventoryCard">
                    <div class="card-header">
                        <h5 class="mb-0"><?= t('products.inventory_management') ?></h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label"><?= t('products.current_stock') ?></label>
                                    <input type="number" class="form-control" name="stock_quantity" 
                                           value="<?= $product->stock_quantity ?? 0 ?>" step="1" min="0">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label"><?= t('products.reserved_stock') ?></label>
                                    <input type="number" class="form-control" name="reserved_stock" 
                                           value="<?= $product->reserved_stock ?? 0 ?>" step="1" min="0" readonly>
                                    <div class="form-text"><?= t('products.reserved_stock_help') ?></div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label"><?= t('products.minimum_stock') ?></label>
                                    <input type="number" class="form-control" name="minimum_stock" 
                                           value="<?= $product->minimum_stock ?? 10 ?>" step="1" min="0">
                                    <div class="form-text"><?= t('products.minimum_stock_help') ?></div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label"><?= t('products.reorder_point') ?></label>
                                    <input type="number" class="form-control" name="reorder_point" 
                                           value="<?= $product->reorder_point ?? 15 ?>" step="1" min="0">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label"><?= t('products.reorder_quantity') ?></label>
                                    <input type="number" class="form-control" name="reorder_quantity" 
                                           value="<?= $product->reorder_quantity ?? 50 ?>" step="1" min="1">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label"><?= t('products.maximum_stock') ?></label>
                                    <input type="number" class="form-control" name="maximum_stock" 
                                           value="<?= $product->maximum_stock ?? '' ?>" step="1" min="0">
                                    <div class="form-text"><?= t('products.maximum_stock_help') ?></div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label"><?= t('products.location') ?></label>
                                    <input type="text" class="form-control" name="location" 
                                           value="<?= htmlspecialchars($product->location ?? '') ?>" 
                                           placeholder="<?= t('products.location_placeholder') ?>">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label"><?= t('products.barcode') ?></label>
                                    <input type="text" class="form-control" name="barcode" 
                                           value="<?= htmlspecialchars($product->barcode ?? '') ?>">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Physical Specifications -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0"><?= t('products.physical_specifications') ?></h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label"><?= t('products.weight') ?></label>
                                    <div class="input-group">
                                        <input type="number" class="form-control" name="weight" 
                                               value="<?= $product->weight ?? '' ?>" step="0.01" min="0">
                                        <select class="form-select" name="weight_unit" style="max-width: 100px;">
                                            <option value="kg" <?= ($product->weight_unit ?? 'kg') === 'kg' ? 'selected' : '' ?>>kg</option>
                                            <option value="lbs" <?= ($product->weight_unit ?? '') === 'lbs' ? 'selected' : '' ?>>lbs</option>
                                            <option value="g" <?= ($product->weight_unit ?? '') === 'g' ? 'selected' : '' ?>>g</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label"><?= t('products.dimensions') ?></label>
                                    <div class="row">
                                        <div class="col">
                                            <input type="number" class="form-control" name="length" 
                                                   value="<?= $product->length ?? '' ?>" step="0.01" min="0" 
                                                   placeholder="<?= t('products.length') ?>">
                                        </div>
                                        <div class="col">
                                            <input type="number" class="form-control" name="width" 
                                                   value="<?= $product->width ?? '' ?>" step="0.01" min="0" 
                                                   placeholder="<?= t('products.width') ?>">
                                        </div>
                                        <div class="col">
                                            <input type="number" class="form-control" name="height" 
                                                   value="<?= $product->height ?? '' ?>" step="0.01" min="0" 
                                                   placeholder="<?= t('products.height') ?>">
                                        </div>
                                        <div class="col-auto">
                                            <select class="form-select" name="dimension_unit">
                                                <option value="cm" <?= ($product->dimension_unit ?? 'cm') === 'cm' ? 'selected' : '' ?>>cm</option>
                                                <option value="in" <?= ($product->dimension_unit ?? '') === 'in' ? 'selected' : '' ?>>in</option>
                                                <option value="m" <?= ($product->dimension_unit ?? '') === 'm' ? 'selected' : '' ?>>m</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label"><?= t('products.color') ?></label>
                                    <input type="text" class="form-control" name="color" 
                                           value="<?= htmlspecialchars($product->color ?? '') ?>">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label"><?= t('products.material') ?></label>
                                    <input type="text" class="form-control" name="material" 
                                           value="<?= htmlspecialchars($product->material ?? '') ?>">
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label"><?= t('products.warranty_period') ?></label>
                            <div class="input-group">
                                <input type="number" class="form-control" name="warranty_period" 
                                       value="<?= $product->warranty_period ?? '' ?>" min="0">
                                <select class="form-select" name="warranty_unit" style="max-width: 150px;">
                                    <option value="days" <?= ($product->warranty_unit ?? 'months') === 'days' ? 'selected' : '' ?>><?= t('products.days') ?></option>
                                    <option value="months" <?= ($product->warranty_unit ?? 'months') === 'months' ? 'selected' : '' ?>><?= t('products.months') ?></option>
                                    <option value="years" <?= ($product->warranty_unit ?? '') === 'years' ? 'selected' : '' ?>><?= t('products.years') ?></option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <!-- Product Images -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0"><?= t('products.product_images') ?></h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label"><?= t('products.main_image') ?></label>
                            <input type="file" class="form-control" name="main_image" accept="image/*">
                            <?php if ($product->image): ?>
                            <div class="mt-2">
                                <img src="<?= htmlspecialchars($product->image) ?>" alt="Current Image" class="img-thumbnail" style="max-height: 150px;">
                                <div class="form-check mt-2">
                                    <input class="form-check-input" type="checkbox" name="remove_main_image">
                                    <label class="form-check-label">
                                        <?= t('products.remove_current_image') ?>
                                    </label>
                                </div>
                            </div>
                            <?php endif; ?>
                        </div>

                        <div class="mb-3">
                            <label class="form-label"><?= t('products.additional_images') ?></label>
                            <input type="file" class="form-control" name="additional_images[]" accept="image/*" multiple>
                            <div class="form-text"><?= t('products.additional_images_help') ?></div>
                        </div>

                        <?php if (!empty($product->additional_images)): ?>
                        <div class="mb-3">
                            <label class="form-label"><?= t('products.current_additional_images') ?></label>
                            <div class="row">
                                <?php foreach ($product->additional_images as $index => $image): ?>
                                <div class="col-6 mb-2">
                                    <img src="<?= htmlspecialchars($image) ?>" alt="Additional Image" class="img-thumbnail w-100" style="height: 80px; object-fit: cover;">
                                    <div class="form-check mt-1">
                                        <input class="form-check-input" type="checkbox" name="remove_additional_images[]" value="<?= $index ?>">
                                        <label class="form-check-label small">
                                            <?= t('common.remove') ?>
                                        </label>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Supplier Information -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0"><?= t('products.supplier_information') ?></h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label"><?= t('products.primary_supplier') ?></label>
                            <select class="form-select" name="primary_supplier_id">
                                <option value=""><?= t('common.select_supplier') ?></option>
                                <?php if (!empty($suppliers)): ?>
                                <?php foreach ($suppliers as $supplier): ?>
                                <option value="<?= $supplier->id ?>" <?= $product->primary_supplier_id == $supplier->id ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($supplier->company_name) ?>
                                </option>
                                <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label"><?= t('products.supplier_sku') ?></label>
                            <input type="text" class="form-control" name="supplier_sku" 
                                   value="<?= htmlspecialchars($product->supplier_sku ?? '') ?>">
                        </div>

                        <div class="mb-3">
                            <label class="form-label"><?= t('products.lead_time') ?></label>
                            <div class="input-group">
                                <input type="number" class="form-control" name="lead_time" 
                                       value="<?= $product->lead_time ?? '' ?>" min="0">
                                <span class="input-group-text"><?= t('products.days') ?></span>
                            </div>
                            <div class="form-text"><?= t('products.lead_time_help') ?></div>
                        </div>
                    </div>
                </div>

                <!-- Quick Stats -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0"><?= t('products.quick_stats') ?></h5>
                    </div>
                    <div class="card-body">
                        <div class="row text-center">
                            <div class="col-6">
                                <div class="border rounded p-2 mb-2">
                                    <div class="h6 mb-0"><?= $product->total_sold ?? 0 ?></div>
                                    <small class="text-muted"><?= t('products.total_sold') ?></small>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="border rounded p-2 mb-2">
                                    <div class="h6 mb-0"><?= $product->available_stock ?? ($product->stock_quantity ?? 0) ?></div>
                                    <small class="text-muted"><?= t('products.available') ?></small>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="border rounded p-2 mb-2">
                                    <div class="h6 mb-0">
                                        <?php if ($product->cost_price && $product->selling_price): ?>
                                        <?= number_format((($product->selling_price - $product->cost_price) / $product->selling_price) * 100, 1) ?>%
                                        <?php else: ?>
                                        -
                                        <?php endif; ?>
                                    </div>
                                    <small class="text-muted"><?= t('products.margin') ?></small>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="border rounded p-2 mb-2">
                                    <div class="h6 mb-0"><?= $product->views ?? 0 ?></div>
                                    <small class="text-muted"><?= t('products.views') ?></small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="card">
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> <?= t('common.save_changes') ?>
                            </button>
                            <button type="button" class="btn btn-outline-secondary" onclick="resetForm()">
                                <i class="fas fa-undo"></i> <?= t('common.reset_form') ?>
                            </button>
                            <a href="/products/<?= $product->id ?>" class="btn btn-outline-info">
                                <i class="fas fa-eye"></i> <?= t('common.view_product') ?>
                            </a>
                            <a href="/products" class="btn btn-outline-secondary">
                                <i class="fas fa-list"></i> <?= t('products.back_to_list') ?>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Calculate profit margin automatically
    const costPriceInput = document.querySelector('input[name="cost_price"]');
    const sellingPriceInput = document.querySelector('input[name="selling_price"]');
    const profitMarginInput = document.querySelector('input[name="profit_margin"]');
    
    function calculateMargin() {
        const costPrice = parseFloat(costPriceInput.value) || 0;
        const sellingPrice = parseFloat(sellingPriceInput.value) || 0;
        
        if (costPrice > 0 && sellingPrice > 0) {
            const margin = ((sellingPrice - costPrice) / sellingPrice) * 100;
            profitMarginInput.value = margin.toFixed(2);
        } else {
            profitMarginInput.value = '';
        }
    }
    
    costPriceInput.addEventListener('input', calculateMargin);
    sellingPriceInput.addEventListener('input', calculateMargin);
    
    // Toggle inventory fields based on track_inventory checkbox
    const trackInventoryCheckbox = document.querySelector('input[name="track_inventory"]');
    const inventoryCard = document.getElementById('inventoryCard');
    
    function toggleInventoryFields() {
        const inventoryInputs = inventoryCard.querySelectorAll('input');
        inventoryInputs.forEach(input => {
            if (input.name !== 'track_inventory') {
                input.disabled = !trackInventoryCheckbox.checked;
            }
        });
        
        if (trackInventoryCheckbox.checked) {
            inventoryCard.classList.remove('opacity-50');
        } else {
            inventoryCard.classList.add('opacity-50');
        }
    }
    
    trackInventoryCheckbox.addEventListener('change', toggleInventoryFields);
    toggleInventoryFields(); // Initial state
});

document.getElementById('productForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    
    fetch('/products/<?= $product->id ?>', {
        method: 'POST',
        headers: {
            'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert('success', data.message);
            // Optionally redirect to product view
            if (data.redirect) {
                setTimeout(() => {
                    window.location.href = data.redirect;
                }, 1500);
            }
        } else {
            showAlert('error', data.message);
            if (data.errors) {
                // Display field-specific errors
                Object.keys(data.errors).forEach(field => {
                    const input = document.querySelector(`[name="${field}"]`);
                    if (input) {
                        input.classList.add('is-invalid');
                        const feedback = input.parentNode.querySelector('.invalid-feedback');
                        if (feedback) {
                            feedback.textContent = data.errors[field][0];
                        }
                    }
                });
            }
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('error', '<?= t('messages.error.general') ?>');
    });
});

function resetForm() {
    if (confirm('<?= t('products.confirm_reset_form') ?>')) {
        document.getElementById('productForm').reset();
        // Remove any validation classes
        document.querySelectorAll('.is-invalid').forEach(el => {
            el.classList.remove('is-invalid');
        });
    }
}

// Image preview functionality
document.querySelector('input[name="main_image"]').addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            const existingImg = document.querySelector('.current-main-image');
            if (existingImg) {
                existingImg.src = e.target.result;
            } else {
                const preview = document.createElement('img');
                preview.src = e.target.result;
                preview.className = 'img-thumbnail mt-2 current-main-image';
                preview.style.maxHeight = '150px';
                e.target.parentNode.appendChild(preview);
            }
        };
        reader.readAsDataURL(file);
    }
});
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/app.php';
?>