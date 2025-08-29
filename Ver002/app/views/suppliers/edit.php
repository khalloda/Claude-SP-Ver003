<?php
/**
 * File: app/views/suppliers/edit.php
 * Purpose: Supplier editing form with comprehensive supplier management
 * Layout: Uses app layout with professional supplier editing interface
 */

$this->layout('layouts/app', [
    'title' => $page_title ?? t('suppliers.edit_supplier'),
    'active_nav' => 'suppliers'
]);

$currentUser = $this->getCurrentUser();
$canEdit = $this->hasRole(['admin', 'manager', 'purchasing']);
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">
            <i class="fas fa-edit me-2"></i><?= t('suppliers.edit_supplier') ?>
            <small class="text-muted ms-2"><?= htmlspecialchars($supplier->company_name) ?></small>
        </h1>
        <div class="btn-toolbar mb-2 mb-md-0">
            <a href="/suppliers/<?= $supplier->id ?>" class="btn btn-outline-secondary me-2">
                <i class="fas fa-eye"></i> <?= t('common.view') ?>
            </a>
            <a href="/suppliers" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left"></i> <?= t('common.back') ?>
            </a>
        </div>
    </div>

    <form id="supplierForm">
        <div class="row">
            <div class="col-md-8">
                <!-- Basic Information -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0"><?= t('suppliers.basic_information') ?></h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label"><?= t('suppliers.company_name') ?> *</label>
                                    <input type="text" class="form-control" name="company_name" 
                                           value="<?= htmlspecialchars($supplier->company_name) ?>" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label"><?= t('suppliers.supplier_code') ?> *</label>
                                    <input type="text" class="form-control" name="supplier_code" 
                                           value="<?= htmlspecialchars($supplier->supplier_code) ?>" required>
                                    <div class="form-text"><?= t('suppliers.supplier_code_help') ?></div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label"><?= t('suppliers.contact_person') ?></label>
                                    <input type="text" class="form-control" name="contact_person" 
                                           value="<?= htmlspecialchars($supplier->contact_person ?? '') ?>">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label"><?= t('suppliers.contact_title') ?></label>
                                    <input type="text" class="form-control" name="contact_title" 
                                           value="<?= htmlspecialchars($supplier->contact_title ?? '') ?>">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label"><?= t('suppliers.status') ?></label>
                                    <select class="form-select" name="status">
                                        <option value="active" <?= ($supplier->status ?? 'active') === 'active' ? 'selected' : '' ?>>
                                            <?= t('suppliers.status.active') ?>
                                        </option>
                                        <option value="inactive" <?= ($supplier->status ?? '') === 'inactive' ? 'selected' : '' ?>>
                                            <?= t('suppliers.status.inactive') ?>
                                        </option>
                                        <option value="pending" <?= ($supplier->status ?? '') === 'pending' ? 'selected' : '' ?>>
                                            <?= t('suppliers.status.pending') ?>
                                        </option>
                                        <option value="suspended" <?= ($supplier->status ?? '') === 'suspended' ? 'selected' : '' ?>>
                                            <?= t('suppliers.status.suspended') ?>
                                        </option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label"><?= t('suppliers.supplier_type') ?></label>
                                    <select class="form-select" name="supplier_type">
                                        <option value="manufacturer" <?= ($supplier->supplier_type ?? 'manufacturer') === 'manufacturer' ? 'selected' : '' ?>>
                                            <?= t('suppliers.type.manufacturer') ?>
                                        </option>
                                        <option value="distributor" <?= ($supplier->supplier_type ?? '') === 'distributor' ? 'selected' : '' ?>>
                                            <?= t('suppliers.type.distributor') ?>
                                        </option>
                                        <option value="wholesaler" <?= ($supplier->supplier_type ?? '') === 'wholesaler' ? 'selected' : '' ?>>
                                            <?= t('suppliers.type.wholesaler') ?>
                                        </option>
                                        <option value="retailer" <?= ($supplier->supplier_type ?? '') === 'retailer' ? 'selected' : '' ?>>
                                            <?= t('suppliers.type.retailer') ?>
                                        </option>
                                        <option value="service_provider" <?= ($supplier->supplier_type ?? '') === 'service_provider' ? 'selected' : '' ?>>
                                            <?= t('suppliers.type.service_provider') ?>
                                        </option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" name="is_preferred" 
                                   <?= !empty($supplier->is_preferred) ? 'checked' : '' ?>>
                            <label class="form-check-label">
                                <?= t('suppliers.is_preferred') ?>
                            </label>
                            <div class="form-text"><?= t('suppliers.is_preferred_help') ?></div>
                        </div>
                    </div>
                </div>

                <!-- Contact Information -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0"><?= t('suppliers.contact_information') ?></h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label"><?= t('suppliers.email') ?> *</label>
                                    <input type="email" class="form-control" name="email" 
                                           value="<?= htmlspecialchars($supplier->email ?? '') ?>" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label"><?= t('suppliers.secondary_email') ?></label>
                                    <input type="email" class="form-control" name="secondary_email" 
                                           value="<?= htmlspecialchars($supplier->secondary_email ?? '') ?>">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label"><?= t('suppliers.phone') ?> *</label>
                                    <input type="tel" class="form-control" name="phone" 
                                           value="<?= htmlspecialchars($supplier->phone ?? '') ?>" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label"><?= t('suppliers.mobile') ?></label>
                                    <input type="tel" class="form-control" name="mobile" 
                                           value="<?= htmlspecialchars($supplier->mobile ?? '') ?>">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label"><?= t('suppliers.fax') ?></label>
                                    <input type="tel" class="form-control" name="fax" 
                                           value="<?= htmlspecialchars($supplier->fax ?? '') ?>">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label"><?= t('suppliers.website') ?></label>
                                    <input type="url" class="form-control" name="website" 
                                           value="<?= htmlspecialchars($supplier->website ?? '') ?>">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Address Information -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0"><?= t('suppliers.address_information') ?></h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label"><?= t('suppliers.street_address') ?></label>
                            <input type="text" class="form-control" name="street_address" 
                                   value="<?= htmlspecialchars($supplier->street_address ?? '') ?>">
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label"><?= t('suppliers.city') ?></label>
                                    <input type="text" class="form-control" name="city" 
                                           value="<?= htmlspecialchars($supplier->city ?? '') ?>">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label"><?= t('suppliers.state_province') ?></label>
                                    <input type="text" class="form-control" name="state_province" 
                                           value="<?= htmlspecialchars($supplier->state_province ?? '') ?>">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label"><?= t('suppliers.postal_code') ?></label>
                                    <input type="text" class="form-control" name="postal_code" 
                                           value="<?= htmlspecialchars($supplier->postal_code ?? '') ?>">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label"><?= t('suppliers.country') ?></label>
                                    <select class="form-select" name="country">
                                        <option value=""><?= t('common.select_country') ?></option>
                                        <option value="US" <?= ($supplier->country ?? '') === 'US' ? 'selected' : '' ?>>United States</option>
                                        <option value="AE" <?= ($supplier->country ?? '') === 'AE' ? 'selected' : '' ?>>United Arab Emirates</option>
                                        <option value="SA" <?= ($supplier->country ?? '') === 'SA' ? 'selected' : '' ?>>Saudi Arabia</option>
                                        <option value="GB" <?= ($supplier->country ?? '') === 'GB' ? 'selected' : '' ?>>United Kingdom</option>
                                        <option value="CA" <?= ($supplier->country ?? '') === 'CA' ? 'selected' : '' ?>>Canada</option>
                                        <option value="AU" <?= ($supplier->country ?? '') === 'AU' ? 'selected' : '' ?>>Australia</option>
                                        <option value="DE" <?= ($supplier->country ?? '') === 'DE' ? 'selected' : '' ?>>Germany</option>
                                        <option value="FR" <?= ($supplier->country ?? '') === 'FR' ? 'selected' : '' ?>>France</option>
                                        <option value="CN" <?= ($supplier->country ?? '') === 'CN' ? 'selected' : '' ?>>China</option>
                                        <option value="IN" <?= ($supplier->country ?? '') === 'IN' ? 'selected' : '' ?>>India</option>
                                        <option value="JP" <?= ($supplier->country ?? '') === 'JP' ? 'selected' : '' ?>>Japan</option>
                                        <option value="KR" <?= ($supplier->country ?? '') === 'KR' ? 'selected' : '' ?>>South Korea</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Financial Information -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0"><?= t('suppliers.financial_information') ?></h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label"><?= t('suppliers.payment_terms') ?></label>
                                    <select class="form-select" name="payment_terms">
                                        <option value="Net 15" <?= ($supplier->payment_terms ?? 'Net 30') === 'Net 15' ? 'selected' : '' ?>>Net 15</option>
                                        <option value="Net 30" <?= ($supplier->payment_terms ?? 'Net 30') === 'Net 30' ? 'selected' : '' ?>>Net 30</option>
                                        <option value="Net 45" <?= ($supplier->payment_terms ?? '') === 'Net 45' ? 'selected' : '' ?>>Net 45</option>
                                        <option value="Net 60" <?= ($supplier->payment_terms ?? '') === 'Net 60' ? 'selected' : '' ?>>Net 60</option>
                                        <option value="Due on Receipt" <?= ($supplier->payment_terms ?? '') === 'Due on Receipt' ? 'selected' : '' ?>>Due on Receipt</option>
                                        <option value="COD" <?= ($supplier->payment_terms ?? '') === 'COD' ? 'selected' : '' ?>>Cash on Delivery</option>
                                        <option value="Prepaid" <?= ($supplier->payment_terms ?? '') === 'Prepaid' ? 'selected' : '' ?>>Prepaid</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label"><?= t('suppliers.currency') ?></label>
                                    <select class="form-select" name="currency">
                                        <option value="USD" <?= ($supplier->currency ?? 'USD') === 'USD' ? 'selected' : '' ?>>USD - US Dollar</option>
                                        <option value="EUR" <?= ($supplier->currency ?? '') === 'EUR' ? 'selected' : '' ?>>EUR - Euro</option>
                                        <option value="AED" <?= ($supplier->currency ?? '') === 'AED' ? 'selected' : '' ?>>AED - UAE Dirham</option>
                                        <option value="SAR" <?= ($supplier->currency ?? '') === 'SAR' ? 'selected' : '' ?>>SAR - Saudi Riyal</option>
                                        <option value="GBP" <?= ($supplier->currency ?? '') === 'GBP' ? 'selected' : '' ?>>GBP - British Pound</option>
                                        <option value="CNY" <?= ($supplier->currency ?? '') === 'CNY' ? 'selected' : '' ?>>CNY - Chinese Yuan</option>
                                        <option value="JPY" <?= ($supplier->currency ?? '') === 'JPY' ? 'selected' : '' ?>>JPY - Japanese Yen</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label"><?= t('suppliers.tax_number') ?></label>
                                    <input type="text" class="form-control" name="tax_number" 
                                           value="<?= htmlspecialchars($supplier->tax_number ?? '') ?>">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label"><?= t('suppliers.credit_limit') ?></label>
                                    <div class="input-group">
                                        <input type="number" class="form-control" name="credit_limit" 
                                               value="<?= $supplier->credit_limit ?? '' ?>" step="0.01" min="0">
                                        <span class="input-group-text"><?= $supplier->currency ?? 'USD' ?></span>
                                    </div>
                                    <div class="form-text"><?= t('suppliers.credit_limit_help') ?></div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label"><?= t('suppliers.bank_name') ?></label>
                                    <input type="text" class="form-control" name="bank_name" 
                                           value="<?= htmlspecialchars($supplier->bank_name ?? '') ?>">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label"><?= t('suppliers.bank_account') ?></label>
                                    <input type="text" class="form-control" name="bank_account" 
                                           value="<?= htmlspecialchars($supplier->bank_account ?? '') ?>">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Business Information -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0"><?= t('suppliers.business_information') ?></h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label"><?= t('suppliers.business_license') ?></label>
                                    <input type="text" class="form-control" name="business_license" 
                                           value="<?= htmlspecialchars($supplier->business_license ?? '') ?>">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label"><?= t('suppliers.years_in_business') ?></label>
                                    <input type="number" class="form-control" name="years_in_business" 
                                           value="<?= $supplier->years_in_business ?? '' ?>" min="0">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label"><?= t('suppliers.employee_count') ?></label>
                                    <select class="form-select" name="employee_count">
                                        <option value=""><?= t('common.select') ?></option>
                                        <option value="1-10" <?= ($supplier->employee_count ?? '') === '1-10' ? 'selected' : '' ?>>1-10</option>
                                        <option value="11-50" <?= ($supplier->employee_count ?? '') === '11-50' ? 'selected' : '' ?>>11-50</option>
                                        <option value="51-200" <?= ($supplier->employee_count ?? '') === '51-200' ? 'selected' : '' ?>>51-200</option>
                                        <option value="201-500" <?= ($supplier->employee_count ?? '') === '201-500' ? 'selected' : '' ?>>201-500</option>
                                        <option value="501+" <?= ($supplier->employee_count ?? '') === '501+' ? 'selected' : '' ?>>501+</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label"><?= t('suppliers.annual_revenue') ?></label>
                                    <select class="form-select" name="annual_revenue">
                                        <option value=""><?= t('common.select') ?></option>
                                        <option value="<1M" <?= ($supplier->annual_revenue ?? '') === '<1M' ? 'selected' : '' ?>>< $1M</option>
                                        <option value="1M-10M" <?= ($supplier->annual_revenue ?? '') === '1M-10M' ? 'selected' : '' ?>>$1M - $10M</option>
                                        <option value="10M-100M" <?= ($supplier->annual_revenue ?? '') === '10M-100M' ? 'selected' : '' ?>>$10M - $100M</option>
                                        <option value="100M+" <?= ($supplier->annual_revenue ?? '') === '100M+' ? 'selected' : '' ?>>$100M+</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label"><?= t('suppliers.industry') ?></label>
                                    <select class="form-select" name="industry">
                                        <option value=""><?= t('common.select_industry') ?></option>
                                        <option value="automotive" <?= ($supplier->industry ?? '') === 'automotive' ? 'selected' : '' ?>><?= t('suppliers.industry.automotive') ?></option>
                                        <option value="electronics" <?= ($supplier->industry ?? '') === 'electronics' ? 'selected' : '' ?>><?= t('suppliers.industry.electronics') ?></option>
                                        <option value="manufacturing" <?= ($supplier->industry ?? '') === 'manufacturing' ? 'selected' : '' ?>><?= t('suppliers.industry.manufacturing') ?></option>
                                        <option value="construction" <?= ($supplier->industry ?? '') === 'construction' ? 'selected' : '' ?>><?= t('suppliers.industry.construction') ?></option>
                                        <option value="healthcare" <?= ($supplier->industry ?? '') === 'healthcare' ? 'selected' : '' ?>><?= t('suppliers.industry.healthcare') ?></option>
                                        <option value="food_beverage" <?= ($supplier->industry ?? '') === 'food_beverage' ? 'selected' : '' ?>><?= t('suppliers.industry.food_beverage') ?></option>
                                        <option value="textiles" <?= ($supplier->industry ?? '') === 'textiles' ? 'selected' : '' ?>><?= t('suppliers.industry.textiles') ?></option>
                                        <option value="chemicals" <?= ($supplier->industry ?? '') === 'chemicals' ? 'selected' : '' ?>><?= t('suppliers.industry.chemicals') ?></option>
                                        <option value="machinery" <?= ($supplier->industry ?? '') === 'machinery' ? 'selected' : '' ?>><?= t('suppliers.industry.machinery') ?></option>
                                        <option value="other" <?= ($supplier->industry ?? '') === 'other' ? 'selected' : '' ?>><?= t('suppliers.industry.other') ?></option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label"><?= t('suppliers.certifications') ?></label>
                                    <input type="text" class="form-control" name="certifications" 
                                           value="<?= htmlspecialchars($supplier->certifications ?? '') ?>"
                                           placeholder="ISO 9001, ISO 14001, etc.">
                                    <div class="form-text"><?= t('suppliers.certifications_help') ?></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Performance & Quality -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0"><?= t('suppliers.performance_quality') ?></h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label"><?= t('suppliers.lead_time_days') ?></label>
                                    <div class="input-group">
                                        <input type="number" class="form-control" name="lead_time_days" 
                                               value="<?= $supplier->lead_time_days ?? '' ?>" min="0">
                                        <span class="input-group-text"><?= t('suppliers.days') ?></span>
                                    </div>
                                    <div class="form-text"><?= t('suppliers.lead_time_help') ?></div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label"><?= t('suppliers.minimum_order_value') ?></label>
                                    <div class="input-group">
                                        <input type="number" class="form-control" name="minimum_order_value" 
                                               value="<?= $supplier->minimum_order_value ?? '' ?>" step="0.01" min="0">
                                        <span class="input-group-text"><?= $supplier->currency ?? 'USD' ?></span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label"><?= t('suppliers.quality_rating') ?></label>
                                    <select class="form-select" name="quality_rating">
                                        <option value=""><?= t('common.not_rated') ?></option>
                                        <option value="1" <?= ($supplier->quality_rating ?? '') === '1' ? 'selected' : '' ?>>1 - <?= t('suppliers.rating.poor') ?></option>
                                        <option value="2" <?= ($supplier->quality_rating ?? '') === '2' ? 'selected' : '' ?>>2 - <?= t('suppliers.rating.fair') ?></option>
                                        <option value="3" <?= ($supplier->quality_rating ?? '') === '3' ? 'selected' : '' ?>>3 - <?= t('suppliers.rating.good') ?></option>
                                        <option value="4" <?= ($supplier->quality_rating ?? '') === '4' ? 'selected' : '' ?>>4 - <?= t('suppliers.rating.very_good') ?></option>
                                        <option value="5" <?= ($supplier->quality_rating ?? '') === '5' ? 'selected' : '' ?>>5 - <?= t('suppliers.rating.excellent') ?></option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label"><?= t('suppliers.delivery_rating') ?></label>
                                    <select class="form-select" name="delivery_rating">
                                        <option value=""><?= t('common.not_rated') ?></option>
                                        <option value="1" <?= ($supplier->delivery_rating ?? '') === '1' ? 'selected' : '' ?>>1 - <?= t('suppliers.rating.poor') ?></option>
                                        <option value="2" <?= ($supplier->delivery_rating ?? '') === '2' ? 'selected' : '' ?>>2 - <?= t('suppliers.rating.fair') ?></option>
                                        <option value="3" <?= ($supplier->delivery_rating ?? '') === '3' ? 'selected' : '' ?>>3 - <?= t('suppliers.rating.good') ?></option>
                                        <option value="4" <?= ($supplier->delivery_rating ?? '') === '4' ? 'selected' : '' ?>>4 - <?= t('suppliers.rating.very_good') ?></option>
                                        <option value="5" <?= ($supplier->delivery_rating ?? '') === '5' ? 'selected' : '' ?>>5 - <?= t('suppliers.rating.excellent') ?></option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label"><?= t('suppliers.service_rating') ?></label>
                                    <select class="form-select" name="service_rating">
                                        <option value=""><?= t('common.not_rated') ?></option>
                                        <option value="1" <?= ($supplier->service_rating ?? '') === '1' ? 'selected' : '' ?>>1 - <?= t('suppliers.rating.poor') ?></option>
                                        <option value="2" <?= ($supplier->service_rating ?? '') === '2' ? 'selected' : '' ?>>2 - <?= t('suppliers.rating.fair') ?></option>
                                        <option value="3" <?= ($supplier->service_rating ?? '') === '3' ? 'selected' : '' ?>>3 - <?= t('suppliers.rating.good') ?></option>
                                        <option value="4" <?= ($supplier->service_rating ?? '') === '4' ? 'selected' : '' ?>>4 - <?= t('suppliers.rating.very_good') ?></option>
                                        <option value="5" <?= ($supplier->service_rating ?? '') === '5' ? 'selected' : '' ?>>5 - <?= t('suppliers.rating.excellent') ?></option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label"><?= t('suppliers.overall_rating') ?></label>
                                    <input type="number" class="form-control" name="overall_rating" 
                                           value="<?= $supplier->overall_rating ?? '' ?>" step="0.1" min="1" max="5" readonly>
                                    <div class="form-text"><?= t('suppliers.overall_rating_auto') ?></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Notes -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0"><?= t('suppliers.additional_information') ?></h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label"><?= t('suppliers.notes') ?></label>
                            <textarea class="form-control" name="notes" rows="4"><?= htmlspecialchars($supplier->notes ?? '') ?></textarea>
                        </div>

                        <div class="mb-3">
                            <label class="form-label"><?= t('suppliers.products_services') ?></label>
                            <textarea class="form-control" name="products_services" rows="3"><?= htmlspecialchars($supplier->products_services ?? '') ?></textarea>
                            <div class="form-text"><?= t('suppliers.products_services_help') ?></div>
                        </div>

                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" name="approved_vendor" 
                                   <?= !empty($supplier->approved_vendor) ? 'checked' : '' ?>>
                            <label class="form-check-label">
                                <?= t('suppliers.approved_vendor') ?>
                            </label>
                        </div>

                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" name="requires_po" 
                                   <?= !empty($supplier->requires_po) ? 'checked' : '' ?>>
                            <label class="form-check-label">
                                <?= t('suppliers.requires_po') ?>
                            </label>
                            <div class="form-text"><?= t('suppliers.requires_po_help') ?></div>
                        </div>

                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" name="can_dropship" 
                                   <?= !empty($supplier->can_dropship) ? 'checked' : '' ?>>
                            <label class="form-check-label">
                                <?= t('suppliers.can_dropship') ?>
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <!-- Quick Stats -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0"><?= t('suppliers.quick_stats') ?></h5>
                    </div>
                    <div class="card-body">
                        <div class="row text-center">
                            <div class="col-6">
                                <div class="border rounded p-3 mb-3">
                                    <div class="h4 mb-1 text-primary"><?= $supplier->total_orders ?? 0 ?></div>
                                    <small class="text-muted"><?= t('suppliers.total_orders') ?></small>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="border rounded p-3 mb-3">
                                    <div class="h4 mb-1 text-success"><?= number_format($supplier->total_spent ?? 0, 0) ?></div>
                                    <small class="text-muted"><?= t('suppliers.total_spent') ?></small>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="border rounded p-3 mb-3">
                                    <div class="h4 mb-1 text-info"><?= $supplier->products_count ?? 0 ?></div>
                                    <small class="text-muted"><?= t('suppliers.products') ?></small>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="border rounded p-3 mb-3">
                                    <div class="h4 mb-1 text-warning">
                                        <?php if ($supplier->overall_rating): ?>
                                        <?= number_format($supplier->overall_rating, 1) ?>/5
                                        <?php else: ?>
                                        -
                                        <?php endif; ?>
                                    </div>
                                    <small class="text-muted"><?= t('suppliers.rating') ?></small>
                                </div>
                            </div>
                        </div>

                        <?php if ($supplier->created_at): ?>
                        <div class="border-top pt-3">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="text-muted"><?= t('suppliers.partner_since') ?>:</span>
                                <span><?= date('M d, Y', strtotime($supplier->created_at)) ?></span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="text-muted"><?= t('suppliers.last_order') ?>:</span>
                                <span>
                                    <?php if ($supplier->last_order_date): ?>
                                    <?= date('M d, Y', strtotime($supplier->last_order_date)) ?>
                                    <?php else: ?>
                                    <span class="text-muted"><?= t('common.never') ?></span>
                                    <?php endif; ?>
                                </span>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Assigned Users -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0"><?= t('suppliers.assigned_users') ?></h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label"><?= t('suppliers.primary_buyer') ?></label>
                            <select class="form-select" name="primary_buyer_id">
                                <option value=""><?= t('common.select_user') ?></option>
                                <?php if (!empty($buyers)): ?>
                                <?php foreach ($buyers as $buyer): ?>
                                <option value="<?= $buyer->id ?>" <?= $supplier->primary_buyer_id == $buyer->id ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($buyer->first_name . ' ' . $buyer->last_name) ?>
                                </option>
                                <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label"><?= t('suppliers.backup_buyer') ?></label>
                            <select class="form-select" name="backup_buyer_id">
                                <option value=""><?= t('common.select_user') ?></option>
                                <?php if (!empty($buyers)): ?>
                                <?php foreach ($buyers as $buyer): ?>
                                <option value="<?= $buyer->id ?>" <?= $supplier->backup_buyer_id == $buyer->id ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($buyer->first_name . ' ' . $buyer->last_name) ?>
                                </option>
                                <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
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
                            <a href="/suppliers/<?= $supplier->id ?>" class="btn btn-outline-info">
                                <i class="fas fa-eye"></i> <?= t('common.view_supplier') ?>
                            </a>
                            <a href="/purchase-orders/create?supplier_id=<?= $supplier->id ?>" class="btn btn-outline-success">
                                <i class="fas fa-plus"></i> <?= t('suppliers.create_purchase_order') ?>
                            </a>
                            <a href="/suppliers" class="btn btn-outline-secondary">
                                <i class="fas fa-list"></i> <?= t('suppliers.back_to_list') ?>
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
    // Calculate overall rating automatically
    const qualityRating = document.querySelector('select[name="quality_rating"]');
    const deliveryRating = document.querySelector('select[name="delivery_rating"]');
    const serviceRating = document.querySelector('select[name="service_rating"]');
    const overallRating = document.querySelector('input[name="overall_rating"]');
    
    function calculateOverallRating() {
        const quality = parseFloat(qualityRating.value) || 0;
        const delivery = parseFloat(deliveryRating.value) || 0;
        const service = parseFloat(serviceRating.value) || 0;
        
        if (quality > 0 || delivery > 0 || service > 0) {
            const count = (quality > 0 ? 1 : 0) + (delivery > 0 ? 1 : 0) + (service > 0 ? 1 : 0);
            const average = (quality + delivery + service) / count;
            overallRating.value = average.toFixed(1);
        } else {
            overallRating.value = '';
        }
    }
    
    qualityRating.addEventListener('change', calculateOverallRating);
    deliveryRating.addEventListener('change', calculateOverallRating);
    serviceRating.addEventListener('change', calculateOverallRating);
});

document.getElementById('supplierForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    
    fetch('/suppliers/<?= $supplier->id ?>', {
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
            // Optionally redirect to supplier view
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
    if (confirm('<?= t('suppliers.confirm_reset_form') ?>')) {
        document.getElementById('supplierForm').reset();
        // Remove any validation classes
        document.querySelectorAll('.is-invalid').forEach(el => {
            el.classList.remove('is-invalid');
        });
    }
}
</script>