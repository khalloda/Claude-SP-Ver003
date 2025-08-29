<?php
/**
 * File: app/views/suppliers/create.php
 * Purpose: Supplier creation form with comprehensive vendor information
 * Layout: Uses app layout with multi-tab organization
 */

$this->layout('layouts/app', [
    'title' => $page_title ?? t('suppliers.add_supplier'),
    'active_nav' => 'suppliers'
]);

$supplier = $supplier ?? new stdClass();
$countries = $countries ?? [];
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">
            <i class="fas fa-truck me-2"></i><?= t('suppliers.add_supplier') ?>
        </h1>
        <div class="btn-toolbar mb-2 mb-md-0">
            <a href="/suppliers" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left"></i> <?= t('common.back') ?>
            </a>
        </div>
    </div>

    <form method="POST" action="/suppliers" id="supplierForm" enctype="multipart/form-data">
        <?= $this->csrf() ?>
        
        <!-- Navigation Tabs -->
        <ul class="nav nav-tabs mb-4" id="supplierTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="basic-tab" data-bs-toggle="tab" data-bs-target="#basic" type="button" role="tab">
                    <i class="fas fa-info-circle me-2"></i><?= t('suppliers.basic_info') ?>
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="contact-tab" data-bs-toggle="tab" data-bs-target="#contact" type="button" role="tab">
                    <i class="fas fa-address-card me-2"></i><?= t('suppliers.contact_info') ?>
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="business-tab" data-bs-toggle="tab" data-bs-target="#business" type="button" role="tab">
                    <i class="fas fa-handshake me-2"></i><?= t('suppliers.business_terms') ?>
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="documents-tab" data-bs-toggle="tab" data-bs-target="#documents" type="button" role="tab">
                    <i class="fas fa-file-alt me-2"></i><?= t('suppliers.documents') ?>
                </button>
            </li>
        </ul>

        <!-- Tab Content -->
        <div class="tab-content" id="supplierTabsContent">
            <!-- Basic Information Tab -->
            <div class="tab-pane fade show active" id="basic" role="tabpanel" aria-labelledby="basic-tab">
                <div class="row">
                    <div class="col-md-8">
                        <div class="card mb-4">
                            <div class="card-header">
                                <h5 class="mb-0"><?= t('suppliers.basic_info') ?></h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="name" class="form-label"><?= t('suppliers.company_name') ?> *</label>
                                            <input type="text" class="form-control" id="name" name="name" 
                                                   value="<?= $this->old('name') ?>" required>
                                            <?= $this->error('name') ?>
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="supplier_code" class="form-label"><?= t('suppliers.supplier_code') ?></label>
                                            <input type="text" class="form-control" id="supplier_code" name="supplier_code" 
                                                   value="<?= $this->old('supplier_code') ?>" placeholder="<?= t('suppliers.auto_generated') ?>">
                                            <?= $this->error('supplier_code') ?>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="type" class="form-label"><?= t('suppliers.supplier_type') ?> *</label>
                                            <select class="form-select" id="type" name="type" required>
                                                <option value=""><?= t('suppliers.select_type') ?></option>
                                                <option value="manufacturer" <?= $this->selected('type', 'manufacturer') ?>>
                                                    <?= t('suppliers.type.manufacturer') ?>
                                                </option>
                                                <option value="distributor" <?= $this->selected('type', 'distributor') ?>>
                                                    <?= t('suppliers.type.distributor') ?>
                                                </option>
                                                <option value="wholesaler" <?= $this->selected('type', 'wholesaler') ?>>
                                                    <?= t('suppliers.type.wholesaler') ?>
                                                </option>
                                                <option value="retailer" <?= $this->selected('type', 'retailer') ?>>
                                                    <?= t('suppliers.type.retailer') ?>
                                                </option>
                                            </select>
                                            <?= $this->error('type') ?>
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="registration_number" class="form-label"><?= t('suppliers.registration_number') ?></label>
                                            <input type="text" class="form-control" id="registration_number" name="registration_number" 
                                                   value="<?= $this->old('registration_number') ?>">
                                            <?= $this->error('registration_number') ?>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="tax_number" class="form-label"><?= t('suppliers.tax_number') ?></label>
                                            <input type="text" class="form-control" id="tax_number" name="tax_number" 
                                                   value="<?= $this->old('tax_number') ?>">
                                            <?= $this->error('tax_number') ?>
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="website" class="form-label"><?= t('suppliers.website') ?></label>
                                            <input type="url" class="form-control" id="website" name="website" 
                                                   value="<?= $this->old('website') ?>" placeholder="https://">
                                            <?= $this->error('website') ?>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="description" class="form-label"><?= t('suppliers.description') ?></label>
                                    <textarea class="form-control" id="description" name="description" rows="3" 
                                              placeholder="<?= t('suppliers.description_placeholder') ?>"><?= $this->old('description') ?></textarea>
                                    <?= $this->error('description') ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-4">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0"><?= t('suppliers.status_settings') ?></h5>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" 
                                               <?= $this->checked('is_active', '1', true) ?>>
                                        <label class="form-check-label" for="is_active">
                                            <?= t('suppliers.active_supplier') ?>
                                        </label>
                                    </div>
                                    <small class="form-text text-muted"><?= t('suppliers.active_help') ?></small>
                                </div>
                                
                                <div class="mb-3">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="is_preferred" name="is_preferred" value="1" 
                                               <?= $this->checked('is_preferred', '1') ?>>
                                        <label class="form-check-label" for="is_preferred">
                                            <?= t('suppliers.preferred_supplier') ?>
                                        </label>
                                    </div>
                                    <small class="form-text text-muted"><?= t('suppliers.preferred_help') ?></small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Contact Information Tab -->
            <div class="tab-pane fade" id="contact" role="tabpanel" aria-labelledby="contact-tab">
                <div class="row">
                    <div class="col-md-6">
                        <div class="card mb-4">
                            <div class="card-header">
                                <h5 class="mb-0"><?= t('suppliers.primary_contact') ?></h5>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <label for="contact_person" class="form-label"><?= t('suppliers.contact_person') ?></label>
                                    <input type="text" class="form-control" id="contact_person" name="contact_person" 
                                           value="<?= $this->old('contact_person') ?>">
                                    <?= $this->error('contact_person') ?>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="contact_title" class="form-label"><?= t('suppliers.contact_title') ?></label>
                                    <input type="text" class="form-control" id="contact_title" name="contact_title" 
                                           value="<?= $this->old('contact_title') ?>" placeholder="e.g., Sales Manager">
                                    <?= $this->error('contact_title') ?>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="phone" class="form-label"><?= t('suppliers.phone') ?></label>
                                            <input type="tel" class="form-control" id="phone" name="phone" 
                                                   value="<?= $this->old('phone') ?>">
                                            <?= $this->error('phone') ?>
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="mobile" class="form-label"><?= t('suppliers.mobile') ?></label>
                                            <input type="tel" class="form-control" id="mobile" name="mobile" 
                                                   value="<?= $this->old('mobile') ?>">
                                            <?= $this->error('mobile') ?>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="email" class="form-label"><?= t('suppliers.email') ?></label>
                                    <input type="email" class="form-control" id="email" name="email" 
                                           value="<?= $this->old('email') ?>">
                                    <?= $this->error('email') ?>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="fax" class="form-label"><?= t('suppliers.fax') ?></label>
                                    <input type="text" class="form-control" id="fax" name="fax" 
                                           value="<?= $this->old('fax') ?>">
                                    <?= $this->error('fax') ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="card mb-4">
                            <div class="card-header">
                                <h5 class="mb-0"><?= t('suppliers.address_info') ?></h5>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <label for="address" class="form-label"><?= t('suppliers.address') ?></label>
                                    <textarea class="form-control" id="address" name="address" rows="3" 
                                              placeholder="<?= t('suppliers.address_placeholder') ?>"><?= $this->old('address') ?></textarea>
                                    <?= $this->error('address') ?>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="city" class="form-label"><?= t('suppliers.city') ?></label>
                                            <input type="text" class="form-control" id="city" name="city" 
                                                   value="<?= $this->old('city') ?>">
                                            <?= $this->error('city') ?>
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="state" class="form-label"><?= t('suppliers.state') ?></label>
                                            <input type="text" class="form-control" id="state" name="state" 
                                                   value="<?= $this->old('state') ?>">
                                            <?= $this->error('state') ?>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="postal_code" class="form-label"><?= t('suppliers.postal_code') ?></label>
                                            <input type="text" class="form-control" id="postal_code" name="postal_code" 
                                                   value="<?= $this->old('postal_code') ?>">
                                            <?= $this->error('postal_code') ?>
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="country" class="form-label"><?= t('suppliers.country') ?></label>
                                            <select class="form-select" id="country" name="country">
                                                <option value=""><?= t('suppliers.select_country') ?></option>
                                                <?php foreach ($countries as $country): ?>
                                                <option value="<?= $country ?>" <?= $this->selected('country', $country) ?>>
                                                    <?= htmlspecialchars($country) ?>
                                                </option>
                                                <?php endforeach; ?>
                                            </select>
                                            <?= $this->error('country') ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Business Terms Tab -->
            <div class="tab-pane fade" id="business" role="tabpanel" aria-labelledby="business-tab">
                <div class="row">
                    <div class="col-md-6">
                        <div class="card mb-4">
                            <div class="card-header">
                                <h5 class="mb-0"><?= t('suppliers.payment_terms') ?></h5>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <label for="payment_terms" class="form-label"><?= t('suppliers.payment_terms') ?></label>
                                    <select class="form-select" id="payment_terms" name="payment_terms">
                                        <option value=""><?= t('suppliers.select_payment_terms') ?></option>
                                        <option value="net_15" <?= $this->selected('payment_terms', 'net_15') ?>><?= t('suppliers.payment.net_15') ?></option>
                                        <option value="net_30" <?= $this->selected('payment_terms', 'net_30') ?>><?= t('suppliers.payment.net_30') ?></option>
                                        <option value="net_45" <?= $this->selected('payment_terms', 'net_45') ?>><?= t('suppliers.payment.net_45') ?></option>
                                        <option value="net_60" <?= $this->selected('payment_terms', 'net_60') ?>><?= t('suppliers.payment.net_60') ?></option>
                                        <option value="net_90" <?= $this->selected('payment_terms', 'net_90') ?>><?= t('suppliers.payment.net_90') ?></option>
                                        <option value="cod" <?= $this->selected('payment_terms', 'cod') ?>><?= t('suppliers.payment.cod') ?></option>
                                        <option value="prepaid" <?= $this->selected('payment_terms', 'prepaid') ?>><?= t('suppliers.payment.prepaid') ?></option>
                                    </select>
                                    <?= $this->error('payment_terms') ?>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="credit_limit" class="form-label"><?= t('suppliers.credit_limit') ?></label>
                                    <input type="number" class="form-control" id="credit_limit" name="credit_limit" 
                                           value="<?= $this->old('credit_limit') ?>" min="0" step="0.01">
                                    <?= $this->error('credit_limit') ?>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="currency" class="form-label"><?= t('suppliers.preferred_currency') ?></label>
                                    <select class="form-select" id="currency" name="currency">
                                        <option value=""><?= t('suppliers.select_currency') ?></option>
                                        <option value="USD" <?= $this->selected('currency', 'USD') ?>>USD - US Dollar</option>
                                        <option value="EUR" <?= $this->selected('currency', 'EUR') ?>>EUR - Euro</option>
                                        <option value="SAR" <?= $this->selected('currency', 'SAR') ?>>SAR - Saudi Riyal</option>
                                        <option value="AED" <?= $this->selected('currency', 'AED') ?>>AED - UAE Dirham</option>
                                    </select>
                                    <?= $this->error('currency') ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="card mb-4">
                            <div class="card-header">
                                <h5 class="mb-0"><?= t('suppliers.delivery_terms') ?></h5>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <label for="delivery_time" class="form-label"><?= t('suppliers.delivery_time') ?></label>
                                    <div class="input-group">
                                        <input type="number" class="form-control" id="delivery_time" name="delivery_time" 
                                               value="<?= $this->old('delivery_time') ?>" min="1">
                                        <span class="input-group-text"><?= t('suppliers.days') ?></span>
                                    </div>
                                    <?= $this->error('delivery_time') ?>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="minimum_order" class="form-label"><?= t('suppliers.minimum_order') ?></label>
                                    <input type="number" class="form-control" id="minimum_order" name="minimum_order" 
                                           value="<?= $this->old('minimum_order') ?>" min="0" step="0.01">
                                    <?= $this->error('minimum_order') ?>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="shipping_terms" class="form-label"><?= t('suppliers.shipping_terms') ?></label>
                                    <select class="form-select" id="shipping_terms" name="shipping_terms">
                                        <option value=""><?= t('suppliers.select_shipping_terms') ?></option>
                                        <option value="fob" <?= $this->selected('shipping_terms', 'fob') ?>><?= t('suppliers.shipping.fob') ?></option>
                                        <option value="cif" <?= $this->selected('shipping_terms', 'cif') ?>><?= t('suppliers.shipping.cif') ?></option>
                                        <option value="exw" <?= $this->selected('shipping_terms', 'exw') ?>><?= t('suppliers.shipping.exw') ?></option>
                                        <option value="dap" <?= $this->selected('shipping_terms', 'dap') ?>><?= t('suppliers.shipping.dap') ?></option>
                                    </select>
                                    <?= $this->error('shipping_terms') ?>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="quality_rating" class="form-label"><?= t('suppliers.quality_rating') ?></label>
                                    <select class="form-select" id="quality_rating" name="quality_rating">
                                        <option value=""><?= t('suppliers.select_rating') ?></option>
                                        <option value="1" <?= $this->selected('quality_rating', '1') ?>>1 - <?= t('suppliers.rating.poor') ?></option>
                                        <option value="2" <?= $this->selected('quality_rating', '2') ?>>2 - <?= t('suppliers.rating.fair') ?></option>
                                        <option value="3" <?= $this->selected('quality_rating', '3') ?>>3 - <?= t('suppliers.rating.good') ?></option>
                                        <option value="4" <?= $this->selected('quality_rating', '4') ?>>4 - <?= t('suppliers.rating.very_good') ?></option>
                                        <option value="5" <?= $this->selected('quality_rating', '5') ?>>5 - <?= t('suppliers.rating.excellent') ?></option>
                                    </select>
                                    <?= $this->error('quality_rating') ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Documents Tab -->
            <div class="tab-pane fade" id="documents" role="tabpanel" aria-labelledby="documents-tab">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><?= t('suppliers.documents_contracts') ?></h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="contract_file" class="form-label"><?= t('suppliers.contract_file') ?></label>
                            <input type="file" class="form-control" id="contract_file" name="contract_file" 
                                   accept=".pdf,.doc,.docx">
                            <small class="form-text text-muted"><?= t('suppliers.contract_help') ?></small>
                            <?= $this->error('contract_file') ?>
                        </div>
                        
                        <div class="mb-3">
                            <label for="certifications" class="form-label"><?= t('suppliers.certifications') ?></label>
                            <input type="file" class="form-control" id="certifications" name="certifications[]" 
                                   accept=".pdf,.jpg,.jpeg,.png" multiple>
                            <small class="form-text text-muted"><?= t('suppliers.certifications_help') ?></small>
                            <?= $this->error('certifications') ?>
                        </div>
                        
                        <div class="mb-3">
                            <label for="notes" class="form-label"><?= t('suppliers.internal_notes') ?></label>
                            <textarea class="form-control" id="notes" name="notes" rows="4" 
                                      placeholder="<?= t('suppliers.notes_placeholder') ?>"><?= $this->old('notes') ?></textarea>
                            <?= $this->error('notes') ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Form Actions -->
        <div class="card mt-4">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <a href="/suppliers" class="btn btn-outline-secondary">
                        <i class="fas fa-times"></i> <?= t('common.cancel') ?>
                    </a>
                    
                    <div>
                        <button type="submit" name="action" value="save_inactive" class="btn btn-outline-primary me-2">
                            <i class="fas fa-save"></i> <?= t('suppliers.save_as_inactive') ?>
                        </button>
                        
                        <button type="submit" name="action" value="save_active" class="btn btn-primary">
                            <i class="fas fa-check"></i> <?= t('suppliers.save_and_activate') ?>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Auto-generate supplier code if empty
    const nameField = document.getElementById('name');
    const codeField = document.getElementById('supplier_code');
    
    nameField.addEventListener('blur', function() {
        if (!codeField.value && this.value) {
            // Generate code from name (first 3 letters + random number)
            const code = this.value.substr(0, 3).toUpperCase() + Math.floor(Math.random() * 1000).toString().padStart(3, '0');
            codeField.value = code;
        }
    });
    
    // Form validation
    document.getElementById('supplierForm').addEventListener('submit', function(e) {
        const requiredFields = ['name', 'type'];
        let isValid = true;
        
        requiredFields.forEach(fieldId => {
            const field = document.getElementById(fieldId);
            if (!field.value.trim()) {
                field.classList.add('is-invalid');
                isValid = false;
            } else {
                field.classList.remove('is-invalid');
            }
        });
        
        if (!isValid) {
            e.preventDefault();
            showAlert('error', '<?= t('suppliers.error_required_fields') ?>');
            
            // Switch to first tab with errors
            const firstTab = document.querySelector('#basic-tab');
            if (firstTab) {
                firstTab.click();
            }
        }
    });
    
    // Clear validation on input
    document.querySelectorAll('input, select, textarea').forEach(field => {
        field.addEventListener('input', function() {
            this.classList.remove('is-invalid');
        });
    });
});
</script>