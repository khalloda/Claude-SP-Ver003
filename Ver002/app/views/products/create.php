<?php
/**
 * File: app/views/products/create.php
 * Purpose: Product creation form with comprehensive inventory setup
 * Layout: Uses app layout with professional product creation interface
 */

$page_title = $page_title ?? t('products.create_product');
$active_nav = 'products';
ob_start();

// Get current user from passed data
$currentUser = $current_user ?? null;
$canCreate = $currentUser && in_array($currentUser['role'] ?? '', ['admin', 'manager', 'warehouse']);
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">
            <i class="fas fa-plus me-2"></i><?= t('products.create_new_product') ?>
        </h1>
        <div class="btn-toolbar mb-2 mb-md-0">
            <a href="/products" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left"></i> <?= t('common.back_to_list') ?>
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
                                    <input type="text" class="form-control" name="name" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label"><?= t('products.sku') ?> *</label>
                                    <input type="text" class="form-control" name="sku" required>
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
                                        <option value="<?= $category->id ?>">
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
                                    <input type="text" class="form-control" name="brand">
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label"><?= t('products.description') ?></label>
                            <textarea class="form-control" name="description" rows="4"></textarea>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label"><?= t('products.unit') ?></label>
                                    <select class="form-select" name="unit">
                                        <option value="pcs" selected><?= t('products.units.pcs') ?></option>
                                        <option value="kg"><?= t('products.units.kg') ?></option>
                                        <option value="lbs"><?= t('products.units.lbs') ?></option>
                                        <option value="meters"><?= t('products.units.meters') ?></option>
                                        <option value="liters"><?= t('products.units.liters') ?></option>
                                        <option value="boxes"><?= t('products.units.boxes') ?></option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label"><?= t('products.status') ?></label>
                                    <select class="form-select" name="status">
                                        <option value="active" selected><?= t('products.status.active') ?></option>
                                        <option value="inactive"><?= t('products.status.inactive') ?></option>
                                        <option value="discontinued"><?= t('products.status.discontinued') ?></option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="track_inventory" checked>
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
                                        <input type="number" class="form-control" name="cost_price" step="0.01" min="0">
                                        <span class="input-group-text">USD</span>
                                    </div>
                                    <div class="form-text"><?= t('products.cost_price_help') ?></div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label"><?= t('products.selling_price') ?> *</label>
                                    <div class="input-group">
                                        <input type="number" class="form-control" name="selling_price" step="0.01" min="0" required>
                                        <span class="input-group-text">USD</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label"><?= t('products.msrp') ?></label>
                                    <div class="input-group">
                                        <input type="number" class="form-control" name="msrp" step="0.01" min="0">
                                        <span class="input-group-text">USD</span>
                                    </div>
                                    <div class="form-text"><?= t('products.msrp_help') ?></div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label"><?= t('products.profit_margin') ?></label>
                                    <div class="input-group">
                                        <input type="number" class="form-control" name="profit_margin" step="0.01" min="0" max="100" readonly>
                                        <span class="input-group-text">%</span>
                                    </div>
                                    <div class="form-text"><?= t('products.profit_margin_auto') ?></div>
                                </div>
                            </div>
                        </div>

                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="tax_exempt">
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
                                    <label class="form-label"><?= t('products.initial_stock') ?></label>
                                    <input type="number" class="form-control" name="stock_quantity" step="1" min="0" value="0">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label"><?= t('products.minimum_stock') ?></label>
                                    <input type="number" class="form-control" name="minimum_stock" step="1" min="0" value="10">
                                    <div class="form-text"><?= t('products.minimum_stock_help') ?></div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label"><?= t('products.reorder_point') ?></label>
                                    <input type="number" class="form-control" name="reorder_point" step="1" min="0" value="15">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label"><?= t('products.reorder_quantity') ?></label>
                                    <input type="number" class="form-control" name="reorder_quantity" step="1" min="1" value="50">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label"><?= t('products.location') ?></label>
                                    <input type="text" class="form-control" name="location" placeholder="<?= t('products.location_placeholder') ?>">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label"><?= t('products.barcode') ?></label>
                                    <div class="input-group">
                                        <input type="text" class="form-control" name="barcode">
                                        <button type="button" class="btn btn-outline-secondary" onclick="generateBarcode()">
                                            <i class="fas fa-barcode"></i> <?= t('products.generate') ?>
                                        </button>
                                    </div>
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
                                        <input type="number" class="form-control" name="weight" step="0.01" min="0">
                                        <select class="form-select" name="weight_unit" style="max-width: 100px;">
                                            <option value="kg" selected>kg</option>
                                            <option value="lbs">lbs</option>
                                            <option value="g">g</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label"><?= t('products.dimensions') ?></label>
                                    <div class="row">
                                        <div class="col">
                                            <input type="number" class="form-control" name="length" step="0.01" min="0" placeholder="<?= t('products.length') ?>">
                                        </div>
                                        <div class="col">
                                            <input type="number" class="form-control" name="width" step="0.01" min="0" placeholder="<?= t('products.width') ?>">
                                        </div>
                                        <div class="col">
                                            <input type="number" class="form-control" name="height" step="0.01" min="0" placeholder="<?= t('products.height') ?>">
                                        </div>
                                        <div class="col-auto">
                                            <select class="form-select" name="dimension_unit">
                                                <option value="cm" selected>cm</option>
                                                <option value="in">in</option>
                                                <option value="m">m</option>
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
                                    <input type="text" class="form-control" name="color">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label"><?= t('products.material') ?></label>
                                    <input type="text" class="form-control" name="material">
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label"><?= t('products.warranty_period') ?></label>
                            <div class="input-group">
                                <input type="number" class="form-control" name="warranty_period" min="0" value="12">
                                <select class="form-select" name="warranty_unit" style="max-width: 150px;">
                                    <option value="days"><?= t('products.days') ?></option>
                                    <option value="months" selected><?= t('products.months') ?></option>
                                    <option value="years"><?= t('products.years') ?></option>
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
                            <input type="file" class="form-control" name="main_image" accept="image/*" id="mainImageInput">
                            <div id="mainImagePreview" class="mt-2" style="display: none;">
                                <img alt="Main Image Preview" class="img-thumbnail" style="max-height: 150px;">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label"><?= t('products.additional_images') ?></label>
                            <input type="file" class="form-control" name="additional_images[]" accept="image/*" multiple>
                            <div class="form-text"><?= t('products.additional_images_help') ?></div>
                        </div>
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
                                <option value="<?= $supplier->id ?>">
                                    <?= htmlspecialchars($supplier->company_name) ?>
                                </option>
                                <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label"><?= t('products.supplier_sku') ?></label>
                            <input type="text" class="form-control" name="supplier_sku">
                        </div>

                        <div class="mb-3">
                            <label class="form-label"><?= t('products.lead_time') ?></label>
                            <div class="input-group">
                                <input type="number" class="form-control" name="lead_time" min="0" value="7">
                                <span class="input-group-text"><?= t('products.days') ?></span>
                            </div>
                            <div class="form-text"><?= t('products.lead_time_help') ?></div>
                        </div>
                    </div>
                </div>

                <!-- Quick Setup -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0"><?= t('products.quick_setup') ?></h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <button type="button" class="btn btn-outline-primary btn-sm w-100 mb-2" onclick="generateSKU()">
                                <i class="fas fa-magic"></i> <?= t('products.auto_generate_sku') ?>
                            </button>
                            <button type="button" class="btn btn-outline-secondary btn-sm w-100 mb-2" onclick="calculateMargins()">
                                <i class="fas fa-calculator"></i> <?= t('products.calculate_margins') ?>
                            </button>
                            <button type="button" class="btn btn-outline-info btn-sm w-100" onclick="setDefaults()">
                                <i class="fas fa-cog"></i> <?= t('products.set_defaults') ?>
                            </button>
                        </div>

                        <div class="alert alert-info small">
                            <i class="fas fa-lightbulb me-2"></i>
                            <?= t('products.quick_setup_help') ?>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="card">
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-plus"></i> <?= t('products.create_product') ?>
                            </button>
                            <button type="button" class="btn btn-outline-secondary" onclick="resetForm()">
                                <i class="fas fa-undo"></i> <?= t('common.reset_form') ?>
                            </button>
                            <button type="button" class="btn btn-outline-info" onclick="previewProduct()">
                                <i class="fas fa-eye"></i> <?= t('products.preview') ?>
                            </button>
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
    
    // Image preview
    const mainImageInput = document.getElementById('mainImageInput');
    const mainImagePreview = document.getElementById('mainImagePreview');
    
    mainImageInput.addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                const img = mainImagePreview.querySelector('img');
                img.src = e.target.result;
                mainImagePreview.style.display = 'block';
            };
            reader.readAsDataURL(file);
        } else {
            mainImagePreview.style.display = 'none';
        }
    });
});

function generateSKU() {
    const nameInput = document.querySelector('input[name="name"]');
    const skuInput = document.querySelector('input[name="sku"]');
    
    if (nameInput.value && !skuInput.value) {
        const name = nameInput.value.toUpperCase().replace(/[^A-Z0-9]/g, '').substring(0, 8);
        const timestamp = Date.now().toString().slice(-4);
        skuInput.value = `SKU-${name}-${timestamp}`;
    }
}

function generateBarcode() {
    const barcodeInput = document.querySelector('input[name="barcode"]');
    const randomBarcode = Math.floor(Math.random() * 1000000000000).toString().padStart(12, '0');
    barcodeInput.value = randomBarcode;
}

function calculateMargins() {
    const costPrice = parseFloat(document.querySelector('input[name="cost_price"]').value) || 0;
    const sellingPrice = parseFloat(document.querySelector('input[name="selling_price"]').value) || 0;
    
    if (costPrice > 0 && !sellingPrice) {
        // Suggest 30% margin
        const suggestedPrice = costPrice * 1.3;
        document.querySelector('input[name="selling_price"]').value = suggestedPrice.toFixed(2);
        document.querySelector('input[name="selling_price"]').dispatchEvent(new Event('input'));
    }
}

function setDefaults() {
    if (!document.querySelector('input[name="minimum_stock"]').value) {
        document.querySelector('input[name="minimum_stock"]').value = '10';
    }
    if (!document.querySelector('input[name="reorder_point"]').value) {
        document.querySelector('input[name="reorder_point"]').value = '15';
    }
    if (!document.querySelector('input[name="reorder_quantity"]').value) {
        document.querySelector('input[name="reorder_quantity"]').value = '50';
    }
    if (!document.querySelector('input[name="warranty_period"]').value) {
        document.querySelector('input[name="warranty_period"]').value = '12';
    }
}

document.getElementById('productForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    
    fetch('/products', {
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
            if (data.redirect) {
                setTimeout(() => {
                    window.location.href = data.redirect;
                }, 1500);
            }
        } else {
            showAlert('error', data.message);
            if (data.errors) {
                Object.keys(data.errors).forEach(field => {
                    const input = document.querySelector(`[name="${field}"]`);
                    if (input) {
                        input.classList.add('is-invalid');
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
        document.querySelectorAll('.is-invalid').forEach(el => {
            el.classList.remove('is-invalid');
        });
        document.getElementById('mainImagePreview').style.display = 'none';
    }
}

function previewProduct() {
    const formData = new FormData(document.getElementById('productForm'));
    // Implementation for product preview modal would go here
    showAlert('info', '<?= t('products.preview_feature_coming_soon') ?>');
}
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/app.php';
?>