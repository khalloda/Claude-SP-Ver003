<?php
/**
 * File: app/views/warehouses/create.php
 * Purpose: Warehouse creation form with comprehensive facility setup
 * Layout: Uses app layout with professional warehouse creation interface
 */

$this->layout('layouts/app', [
    'title' => $page_title ?? t('warehouses.create_warehouse'),
    'active_nav' => 'warehouses'
]);

$countries = $countries ?? [];
$currencies = $currencies ?? [];
$warehouse_types = $warehouse_types ?? [];
$managers = $managers ?? [];
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">
            <i class="fas fa-plus me-2"></i><?= t('warehouses.create_warehouse') ?>
        </h1>
        <div class="btn-toolbar mb-2 mb-md-0">
            <a href="/warehouses" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left"></i> <?= t('common.back') ?>
            </a>
        </div>
    </div>

    <form method="POST" action="/warehouses" id="createWarehouseForm" enctype="multipart/form-data">
        <?= $this->csrf() ?>
        
        <div class="row">
            <div class="col-md-8">
                <!-- Basic Information -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0"><?= t('warehouses.basic_information') ?></h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="name" class="form-label"><?= t('warehouses.warehouse_name') ?> *</label>
                                    <input type="text" class="form-control" id="name" name="name" 
                                           value="<?= $this->old('name') ?>" required
                                           placeholder="<?= t('warehouses.name_placeholder') ?>">
                                    <?= $this->error('name') ?>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="code" class="form-label"><?= t('warehouses.warehouse_code') ?></label>
                                    <div class="input-group">
                                        <input type="text" class="form-control" id="code" name="code" 
                                               value="<?= $this->old('code') ?>"
                                               placeholder="<?= t('warehouses.code_placeholder') ?>">
                                        <button type="button" class="btn btn-outline-secondary" id="generateCode">
                                            <i class="fas fa-magic"></i>
                                        </button>
                                    </div>
                                    <?= $this->error('code') ?>
                                    <small class="form-text text-muted"><?= t('warehouses.code_help') ?></small>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="type" class="form-label"><?= t('warehouses.warehouse_type') ?> *</label>
                                    <select class="form-select" id="type" name="type" required>
                                        <option value=""><?= t('warehouses.select_type') ?></option>
                                        <?php foreach ($warehouse_types as $type): ?>
                                        <option value="<?= $type->slug ?>" <?= $this->selected('type', $type->slug) ?>>
                                            <?= htmlspecialchars($type->name) ?>
                                        </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <?= $this->error('type') ?>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="status" class="form-label"><?= t('common.status') ?> *</label>
                                    <select class="form-select" id="status" name="status" required>
                                        <option value="active" <?= $this->selected('status', 'active', true) ?>>
                                            <?= t('warehouses.status.active') ?>
                                        </option>
                                        <option value="inactive" <?= $this->selected('status', 'inactive') ?>>
                                            <?= t('warehouses.status.inactive') ?>
                                        </option>
                                        <option value="maintenance" <?= $this->selected('status', 'maintenance') ?>>
                                            <?= t('warehouses.status.maintenance') ?>
                                        </option>
                                    </select>
                                    <?= $this->error('status') ?>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="description" class="form-label"><?= t('warehouses.description') ?></label>
                            <textarea class="form-control" id="description" name="description" rows="3" 
                                      placeholder="<?= t('warehouses.description_placeholder') ?>"><?= $this->old('description') ?></textarea>
                            <?= $this->error('description') ?>
                        </div>
                    </div>
                </div>

                <!-- Location Information -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0"><?= t('warehouses.location_information') ?></h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="address" class="form-label"><?= t('warehouses.address') ?> *</label>
                            <textarea class="form-control" id="address" name="address" rows="2" required
                                      placeholder="<?= t('warehouses.address_placeholder') ?>"><?= $this->old('address') ?></textarea>
                            <?= $this->error('address') ?>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="city" class="form-label"><?= t('warehouses.city') ?> *</label>
                                    <input type="text" class="form-control" id="city" name="city" 
                                           value="<?= $this->old('city') ?>" required
                                           placeholder="<?= t('warehouses.city_placeholder') ?>">
                                    <?= $this->error('city') ?>
                                </div>
                            </div>
                            
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="state" class="form-label"><?= t('warehouses.state') ?></label>
                                    <input type="text" class="form-control" id="state" name="state" 
                                           value="<?= $this->old('state') ?>"
                                           placeholder="<?= t('warehouses.state_placeholder') ?>">
                                    <?= $this->error('state') ?>
                                </div>
                            </div>
                            
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="postal_code" class="form-label"><?= t('warehouses.postal_code') ?></label>
                                    <input type="text" class="form-control" id="postal_code" name="postal_code" 
                                           value="<?= $this->old('postal_code') ?>"
                                           placeholder="<?= t('warehouses.postal_code_placeholder') ?>">
                                    <?= $this->error('postal_code') ?>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="country" class="form-label"><?= t('warehouses.country') ?> *</label>
                                    <select class="form-select" id="country" name="country" required>
                                        <option value=""><?= t('warehouses.select_country') ?></option>
                                        <?php foreach ($countries as $country): ?>
                                        <option value="<?= $country->code ?>" <?= $this->selected('country', $country->code) ?>>
                                            <?= htmlspecialchars($country->name) ?>
                                        </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <?= $this->error('country') ?>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="timezone" class="form-label"><?= t('warehouses.timezone') ?></label>
                                    <select class="form-select" id="timezone" name="timezone">
                                        <option value=""><?= t('warehouses.select_timezone') ?></option>
                                        <?php foreach (timezone_identifiers_list() as $tz): ?>
                                        <option value="<?= $tz ?>" <?= $this->selected('timezone', $tz) ?>>
                                            <?= str_replace('_', ' ', $tz) ?>
                                        </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <?= $this->error('timezone') ?>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="latitude" class="form-label"><?= t('warehouses.latitude') ?></label>
                                    <input type="number" class="form-control" id="latitude" name="latitude" 
                                           value="<?= $this->old('latitude') ?>" step="any"
                                           placeholder="<?= t('warehouses.latitude_placeholder') ?>">
                                    <?= $this->error('latitude') ?>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="longitude" class="form-label"><?= t('warehouses.longitude') ?></label>
                                    <input type="number" class="form-control" id="longitude" name="longitude" 
                                           value="<?= $this->old('longitude') ?>" step="any"
                                           placeholder="<?= t('warehouses.longitude_placeholder') ?>">
                                    <?= $this->error('longitude') ?>
                                    <small class="form-text text-muted">
                                        <a href="#" id="getCurrentLocation"><?= t('warehouses.get_current_location') ?></a>
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Capacity & Specifications -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0"><?= t('warehouses.capacity_specifications') ?></h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="total_capacity" class="form-label"><?= t('warehouses.total_capacity') ?> *</label>
                                    <div class="input-group">
                                        <input type="number" class="form-control" id="total_capacity" name="total_capacity" 
                                               value="<?= $this->old('total_capacity') ?>" min="0" step="0.01" required>
                                        <select class="form-select" id="capacity_unit" name="capacity_unit" style="max-width: 120px;">
                                            <option value="m³" <?= $this->selected('capacity_unit', 'm³', true) ?>>m³</option>
                                            <option value="ft³" <?= $this->selected('capacity_unit', 'ft³') ?>>ft³</option>
                                            <option value="pallets" <?= $this->selected('capacity_unit', 'pallets') ?>><?= t('warehouses.pallets') ?></option>
                                            <option value="shelves" <?= $this->selected('capacity_unit', 'shelves') ?>><?= t('warehouses.shelves') ?></option>
                                        </select>
                                    </div>
                                    <?= $this->error('total_capacity') ?>
                                </div>
                            </div>
                            
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="floor_area" class="form-label"><?= t('warehouses.floor_area') ?></label>
                                    <div class="input-group">
                                        <input type="number" class="form-control" id="floor_area" name="floor_area" 
                                               value="<?= $this->old('floor_area') ?>" min="0" step="0.01">
                                        <span class="input-group-text">m²</span>
                                    </div>
                                    <?= $this->error('floor_area') ?>
                                </div>
                            </div>
                            
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="height" class="form-label"><?= t('warehouses.height') ?></label>
                                    <div class="input-group">
                                        <input type="number" class="form-control" id="height" name="height" 
                                               value="<?= $this->old('height') ?>" min="0" step="0.01">
                                        <span class="input-group-text">m</span>
                                    </div>
                                    <?= $this->error('height') ?>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="dock_doors" class="form-label"><?= t('warehouses.dock_doors') ?></label>
                                    <input type="number" class="form-control" id="dock_doors" name="dock_doors" 
                                           value="<?= $this->old('dock_doors', '0') ?>" min="0">
                                    <?= $this->error('dock_doors') ?>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="zones" class="form-label"><?= t('warehouses.zones') ?></label>
                                    <input type="number" class="form-control" id="zones" name="zones" 
                                           value="<?= $this->old('zones', '1') ?>" min="1">
                                    <?= $this->error('zones') ?>
                                    <small class="form-text text-muted"><?= t('warehouses.zones_help') ?></small>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Warehouse Features -->
                        <div class="mb-3">
                            <label class="form-label"><?= t('warehouses.features') ?></label>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="climate_controlled" 
                                               name="features[]" value="climate_controlled" 
                                               <?= $this->checked('features', 'climate_controlled') ?>>
                                        <label class="form-check-label" for="climate_controlled">
                                            <?= t('warehouses.features.climate_controlled') ?>
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="refrigerated" 
                                               name="features[]" value="refrigerated" 
                                               <?= $this->checked('features', 'refrigerated') ?>>
                                        <label class="form-check-label" for="refrigerated">
                                            <?= t('warehouses.features.refrigerated') ?>
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="hazmat_certified" 
                                               name="features[]" value="hazmat_certified" 
                                               <?= $this->checked('features', 'hazmat_certified') ?>>
                                        <label class="form-check-label" for="hazmat_certified">
                                            <?= t('warehouses.features.hazmat_certified') ?>
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="automated_systems" 
                                               name="features[]" value="automated_systems" 
                                               <?= $this->checked('features', 'automated_systems') ?>>
                                        <label class="form-check-label" for="automated_systems">
                                            <?= t('warehouses.features.automated_systems') ?>
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="security_system" 
                                               name="features[]" value="security_system" 
                                               <?= $this->checked('features', 'security_system') ?>>
                                        <label class="form-check-label" for="security_system">
                                            <?= t('warehouses.features.security_system') ?>
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="fire_suppression" 
                                               name="features[]" value="fire_suppression" 
                                               <?= $this->checked('features', 'fire_suppression') ?>>
                                        <label class="form-check-label" for="fire_suppression">
                                            <?= t('warehouses.features.fire_suppression') ?>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Contact Information -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0"><?= t('warehouses.contact_information') ?></h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="phone" class="form-label"><?= t('warehouses.phone') ?></label>
                                    <input type="tel" class="form-control" id="phone" name="phone" 
                                           value="<?= $this->old('phone') ?>"
                                           placeholder="<?= t('warehouses.phone_placeholder') ?>">
                                    <?= $this->error('phone') ?>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="email" class="form-label"><?= t('warehouses.email') ?></label>
                                    <input type="email" class="form-control" id="email" name="email" 
                                           value="<?= $this->old('email') ?>"
                                           placeholder="<?= t('warehouses.email_placeholder') ?>">
                                    <?= $this->error('email') ?>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="manager_id" class="form-label"><?= t('warehouses.warehouse_manager') ?></label>
                            <select class="form-select" id="manager_id" name="manager_id">
                                <option value=""><?= t('warehouses.select_manager') ?></option>
                                <?php foreach ($managers as $manager): ?>
                                <option value="<?= $manager->id ?>" <?= $this->selected('manager_id', $manager->id) ?>>
                                    <?= htmlspecialchars($manager->name) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                            <?= $this->error('manager_id') ?>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <!-- Actions -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0"><?= t('common.actions') ?></h5>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> <?= t('warehouses.create_warehouse') ?>
                            </button>
                            
                            <button type="button" class="btn btn-outline-secondary" onclick="saveDraft()">
                                <i class="fas fa-file-alt"></i> <?= t('common.save_draft') ?>
                            </button>
                            
                            <hr>
                            
                            <a href="/warehouses" class="btn btn-outline-secondary">
                                <i class="fas fa-times"></i> <?= t('common.cancel') ?>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Cost Information -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0"><?= t('warehouses.cost_information') ?></h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="monthly_rent" class="form-label"><?= t('warehouses.monthly_rent') ?></label>
                            <div class="input-group">
                                <input type="number" class="form-control" id="monthly_rent" name="monthly_rent" 
                                       value="<?= $this->old('monthly_rent') ?>" min="0" step="0.01">
                                <select class="form-select" id="currency" name="currency" style="max-width: 120px;">
                                    <?php foreach ($currencies as $currency): ?>
                                    <option value="<?= $currency->code ?>" <?= $this->selected('currency', $currency->code, $currency->is_default) ?>>
                                        <?= $currency->code ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <?= $this->error('monthly_rent') ?>
                        </div>
                        
                        <div class="mb-3">
                            <label for="utility_cost" class="form-label"><?= t('warehouses.monthly_utilities') ?></label>
                            <input type="number" class="form-control" id="utility_cost" name="utility_cost" 
                                   value="<?= $this->old('utility_cost') ?>" min="0" step="0.01">
                            <?= $this->error('utility_cost') ?>
                        </div>
                        
                        <div class="mb-3">
                            <label for="insurance_cost" class="form-label"><?= t('warehouses.monthly_insurance') ?></label>
                            <input type="number" class="form-control" id="insurance_cost" name="insurance_cost" 
                                   value="<?= $this->old('insurance_cost') ?>" min="0" step="0.01">
                            <?= $this->error('insurance_cost') ?>
                        </div>
                        
                        <hr>
                        
                        <div class="d-flex justify-content-between">
                            <strong><?= t('warehouses.total_monthly_cost') ?>:</strong>
                            <strong><span id="total_cost">0.00</span></strong>
                        </div>
                    </div>
                </div>

                <!-- Image Upload -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0"><?= t('warehouses.warehouse_image') ?></h5>
                    </div>
                    <div class="card-body text-center">
                        <div class="image-upload-area border-dashed border-2 rounded p-4 mb-3" 
                             style="min-height: 200px; display: flex; align-items: center; justify-content: center;">
                            <div id="image-preview" style="display: none;">
                                <img id="preview-img" src="" alt="Preview" class="img-fluid rounded" style="max-height: 180px;">
                            </div>
                            <div id="upload-placeholder">
                                <i class="fas fa-cloud-upload-alt fa-3x text-muted mb-3"></i>
                                <p class="text-muted"><?= t('warehouses.drag_drop_image') ?></p>
                            </div>
                        </div>
                        
                        <input type="file" class="form-control" id="image" name="image" 
                               accept="image/jpeg,image/png,image/jpg" style="display: none;">
                        <button type="button" class="btn btn-outline-primary" onclick="document.getElementById('image').click()">
                            <i class="fas fa-upload"></i> <?= t('warehouses.upload_image') ?>
                        </button>
                        <?= $this->error('image') ?>
                        
                        <small class="form-text text-muted d-block mt-2">
                            <?= t('warehouses.image_requirements') ?>
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('createWarehouseForm');
    const nameInput = document.getElementById('name');
    const codeInput = document.getElementById('code');
    const imageInput = document.getElementById('image');
    const costInputs = ['monthly_rent', 'utility_cost', 'insurance_cost'];
    
    // Auto-generate warehouse code from name
    nameInput.addEventListener('blur', function() {
        if (!codeInput.value && this.value) {
            generateWarehouseCode();
        }
    });
    
    // Manual code generation
    document.getElementById('generateCode').addEventListener('click', generateWarehouseCode);
    
    function generateWarehouseCode() {
        const name = nameInput.value.trim();
        if (name) {
            const code = name.substring(0, 3).toUpperCase() + 
                        Math.floor(Math.random() * 1000).toString().padStart(3, '0');
            codeInput.value = code;
        }
    }
    
    // Calculate total monthly cost
    costInputs.forEach(inputId => {
        document.getElementById(inputId).addEventListener('input', calculateTotalCost);
    });
    
    function calculateTotalCost() {
        let total = 0;
        costInputs.forEach(inputId => {
            const value = parseFloat(document.getElementById(inputId).value) || 0;
            total += value;
        });
        document.getElementById('total_cost').textContent = total.toFixed(2);
    }
    
    // Image upload handling
    imageInput.addEventListener('change', function() {
        const file = this.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById('preview-img').src = e.target.result;
                document.getElementById('image-preview').style.display = 'block';
                document.getElementById('upload-placeholder').style.display = 'none';
            };
            reader.readAsDataURL(file);
        }
    });
    
    // Get current location
    document.getElementById('getCurrentLocation').addEventListener('click', function(e) {
        e.preventDefault();
        
        if (navigator.geolocation) {
            const btn = this;
            const originalText = btn.textContent;
            btn.textContent = '<?= t('warehouses.getting_location') ?>';
            
            navigator.geolocation.getCurrentPosition(function(position) {
                document.getElementById('latitude').value = position.coords.latitude.toFixed(6);
                document.getElementById('longitude').value = position.coords.longitude.toFixed(6);
                btn.textContent = originalText;
                showAlert('success', '<?= t('warehouses.location_updated') ?>');
            }, function(error) {
                btn.textContent = originalText;
                showAlert('error', '<?= t('warehouses.location_error') ?>');
            });
        } else {
            showAlert('error', '<?= t('warehouses.geolocation_not_supported') ?>');
        }
    });
    
    // Form validation
    form.addEventListener('submit', function(e) {
        let isValid = true;
        const requiredFields = form.querySelectorAll('[required]');
        
        requiredFields.forEach(field => {
            if (!field.value.trim()) {
                isValid = false;
                field.classList.add('is-invalid');
            } else {
                field.classList.remove('is-invalid');
            }
        });
        
        if (!isValid) {
            e.preventDefault();
            showAlert('error', '<?= t('warehouses.form_validation_errors') ?>');
            return false;
        }
        
        return true;
    });
    
    // Initial total cost calculation
    calculateTotalCost();
});

function saveDraft() {
    const formData = new FormData(document.getElementById('createWarehouseForm'));
    formData.append('save_as_draft', '1');
    
    fetch('/warehouses/draft', {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert('success', data.message || '<?= t('warehouses.draft_saved') ?>');
        } else {
            showAlert('error', data.message || '<?= t('messages.error.general') ?>');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('error', '<?= t('messages.error.general') ?>');
    });
}
</script>

<style>
.border-dashed {
    border-style: dashed !important;
}

.image-upload-area {
    transition: all 0.3s ease;
    cursor: pointer;
}

.image-upload-area:hover {
    border-color: #007bff;
    background-color: #f8f9fa;
}

@media (max-width: 768px) {
    .col-md-6, .col-md-4 {
        margin-bottom: 1rem;
    }
}
</style>