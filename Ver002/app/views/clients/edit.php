<?php
/**
 * File: app/views/clients/edit.php
 * Purpose: Client editing form with comprehensive contact and business information management
 * Layout: Uses app layout with professional client editing interface
 */

$page_title = $page_title ?? t('clients.edit_client');
$active_nav = 'clients';
ob_start();

// Get current user from passed data
$currentUser = $current_user ?? null;
$canEdit = $currentUser && in_array($currentUser['role'] ?? '', ['admin', 'manager', 'sales']);
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">
            <i class="fas fa-edit me-2"></i><?= t('clients.edit_client') ?>
            <small class="text-muted ms-2"><?= htmlspecialchars($client->name) ?></small>
        </h1>
        <div class="btn-toolbar mb-2 mb-md-0">
            <a href="/clients/<?= $client->id ?>" class="btn btn-outline-secondary me-2">
                <i class="fas fa-eye"></i> <?= t('common.view') ?>
            </a>
            <a href="/clients" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left"></i> <?= t('common.back') ?>
            </a>
        </div>
    </div>

    <form id="clientForm">
        <div class="row">
            <div class="col-md-8">
                <!-- Basic Information -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0"><?= t('clients.basic_information') ?></h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label"><?= t('clients.client_type') ?> *</label>
                                    <select class="form-select" name="client_type" required>
                                        <option value="individual" <?= ($client->client_type ?? 'individual') === 'individual' ? 'selected' : '' ?>>
                                            <?= t('clients.type.individual') ?>
                                        </option>
                                        <option value="business" <?= ($client->client_type ?? '') === 'business' ? 'selected' : '' ?>>
                                            <?= t('clients.type.business') ?>
                                        </option>
                                        <option value="government" <?= ($client->client_type ?? '') === 'government' ? 'selected' : '' ?>>
                                            <?= t('clients.type.government') ?>
                                        </option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label"><?= t('clients.status') ?></label>
                                    <select class="form-select" name="status">
                                        <option value="active" <?= ($client->status ?? 'active') === 'active' ? 'selected' : '' ?>>
                                            <?= t('clients.status.active') ?>
                                        </option>
                                        <option value="inactive" <?= ($client->status ?? '') === 'inactive' ? 'selected' : '' ?>>
                                            <?= t('clients.status.inactive') ?>
                                        </option>
                                        <option value="prospect" <?= ($client->status ?? '') === 'prospect' ? 'selected' : '' ?>>
                                            <?= t('clients.status.prospect') ?>
                                        </option>
                                        <option value="suspended" <?= ($client->status ?? '') === 'suspended' ? 'selected' : '' ?>>
                                            <?= t('clients.status.suspended') ?>
                                        </option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="row" id="individualFields">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label"><?= t('clients.first_name') ?> *</label>
                                    <input type="text" class="form-control" name="first_name" 
                                           value="<?= htmlspecialchars($client->first_name ?? '') ?>" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label"><?= t('clients.last_name') ?> *</label>
                                    <input type="text" class="form-control" name="last_name" 
                                           value="<?= htmlspecialchars($client->last_name ?? '') ?>" required>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3" id="companyField">
                            <label class="form-label"><?= t('clients.company_name') ?> *</label>
                            <input type="text" class="form-control" name="company_name" 
                                   value="<?= htmlspecialchars($client->company_name ?? '') ?>">
                        </div>

                        <div class="row" id="businessFields">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label"><?= t('clients.tax_number') ?></label>
                                    <input type="text" class="form-control" name="tax_number" 
                                           value="<?= htmlspecialchars($client->tax_number ?? '') ?>">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label"><?= t('clients.registration_number') ?></label>
                                    <input type="text" class="form-control" name="registration_number" 
                                           value="<?= htmlspecialchars($client->registration_number ?? '') ?>">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label"><?= t('clients.industry') ?></label>
                                    <select class="form-select" name="industry">
                                        <option value=""><?= t('common.select_industry') ?></option>
                                        <option value="automotive" <?= ($client->industry ?? '') === 'automotive' ? 'selected' : '' ?>><?= t('clients.industry.automotive') ?></option>
                                        <option value="construction" <?= ($client->industry ?? '') === 'construction' ? 'selected' : '' ?>><?= t('clients.industry.construction') ?></option>
                                        <option value="manufacturing" <?= ($client->industry ?? '') === 'manufacturing' ? 'selected' : '' ?>><?= t('clients.industry.manufacturing') ?></option>
                                        <option value="retail" <?= ($client->industry ?? '') === 'retail' ? 'selected' : '' ?>><?= t('clients.industry.retail') ?></option>
                                        <option value="healthcare" <?= ($client->industry ?? '') === 'healthcare' ? 'selected' : '' ?>><?= t('clients.industry.healthcare') ?></option>
                                        <option value="technology" <?= ($client->industry ?? '') === 'technology' ? 'selected' : '' ?>><?= t('clients.industry.technology') ?></option>
                                        <option value="education" <?= ($client->industry ?? '') === 'education' ? 'selected' : '' ?>><?= t('clients.industry.education') ?></option>
                                        <option value="government" <?= ($client->industry ?? '') === 'government' ? 'selected' : '' ?>><?= t('clients.industry.government') ?></option>
                                        <option value="other" <?= ($client->industry ?? '') === 'other' ? 'selected' : '' ?>><?= t('clients.industry.other') ?></option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label"><?= t('clients.client_source') ?></label>
                                    <select class="form-select" name="client_source">
                                        <option value=""><?= t('common.select_source') ?></option>
                                        <option value="referral" <?= ($client->client_source ?? '') === 'referral' ? 'selected' : '' ?>><?= t('clients.source.referral') ?></option>
                                        <option value="website" <?= ($client->client_source ?? '') === 'website' ? 'selected' : '' ?>><?= t('clients.source.website') ?></option>
                                        <option value="social_media" <?= ($client->client_source ?? '') === 'social_media' ? 'selected' : '' ?>><?= t('clients.source.social_media') ?></option>
                                        <option value="advertisement" <?= ($client->client_source ?? '') === 'advertisement' ? 'selected' : '' ?>><?= t('clients.source.advertisement') ?></option>
                                        <option value="cold_call" <?= ($client->client_source ?? '') === 'cold_call' ? 'selected' : '' ?>><?= t('clients.source.cold_call') ?></option>
                                        <option value="trade_show" <?= ($client->client_source ?? '') === 'trade_show' ? 'selected' : '' ?>><?= t('clients.source.trade_show') ?></option>
                                        <option value="existing_client" <?= ($client->client_source ?? '') === 'existing_client' ? 'selected' : '' ?>><?= t('clients.source.existing_client') ?></option>
                                        <option value="other" <?= ($client->client_source ?? '') === 'other' ? 'selected' : '' ?>><?= t('clients.source.other') ?></option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Contact Information -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0"><?= t('clients.contact_information') ?></h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label"><?= t('clients.email') ?> *</label>
                                    <input type="email" class="form-control" name="email" 
                                           value="<?= htmlspecialchars($client->email ?? '') ?>" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label"><?= t('clients.secondary_email') ?></label>
                                    <input type="email" class="form-control" name="secondary_email" 
                                           value="<?= htmlspecialchars($client->secondary_email ?? '') ?>">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label"><?= t('clients.phone') ?> *</label>
                                    <input type="tel" class="form-control" name="phone" 
                                           value="<?= htmlspecialchars($client->phone ?? '') ?>" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label"><?= t('clients.mobile') ?></label>
                                    <input type="tel" class="form-control" name="mobile" 
                                           value="<?= htmlspecialchars($client->mobile ?? '') ?>">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label"><?= t('clients.fax') ?></label>
                                    <input type="tel" class="form-control" name="fax" 
                                           value="<?= htmlspecialchars($client->fax ?? '') ?>">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label"><?= t('clients.website') ?></label>
                                    <input type="url" class="form-control" name="website" 
                                           value="<?= htmlspecialchars($client->website ?? '') ?>">
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label"><?= t('clients.preferred_contact_method') ?></label>
                            <select class="form-select" name="preferred_contact_method">
                                <option value="email" <?= ($client->preferred_contact_method ?? 'email') === 'email' ? 'selected' : '' ?>>
                                    <?= t('clients.contact.email') ?>
                                </option>
                                <option value="phone" <?= ($client->preferred_contact_method ?? '') === 'phone' ? 'selected' : '' ?>>
                                    <?= t('clients.contact.phone') ?>
                                </option>
                                <option value="mobile" <?= ($client->preferred_contact_method ?? '') === 'mobile' ? 'selected' : '' ?>>
                                    <?= t('clients.contact.mobile') ?>
                                </option>
                                <option value="fax" <?= ($client->preferred_contact_method ?? '') === 'fax' ? 'selected' : '' ?>>
                                    <?= t('clients.contact.fax') ?>
                                </option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Address Information -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0"><?= t('clients.address_information') ?></h5>
                    </div>
                    <div class="card-body">
                        <!-- Billing Address -->
                        <h6 class="border-bottom pb-2 mb-3"><?= t('clients.billing_address') ?></h6>
                        <div class="mb-3">
                            <label class="form-label"><?= t('clients.street_address') ?></label>
                            <input type="text" class="form-control" name="billing_street" 
                                   value="<?= htmlspecialchars($client->billing_street ?? '') ?>">
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label"><?= t('clients.city') ?></label>
                                    <input type="text" class="form-control" name="billing_city" 
                                           value="<?= htmlspecialchars($client->billing_city ?? '') ?>">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label"><?= t('clients.state_province') ?></label>
                                    <input type="text" class="form-control" name="billing_state" 
                                           value="<?= htmlspecialchars($client->billing_state ?? '') ?>">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label"><?= t('clients.postal_code') ?></label>
                                    <input type="text" class="form-control" name="billing_postal_code" 
                                           value="<?= htmlspecialchars($client->billing_postal_code ?? '') ?>">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label"><?= t('clients.country') ?></label>
                                    <select class="form-select" name="billing_country">
                                        <option value=""><?= t('common.select_country') ?></option>
                                        <option value="US" <?= ($client->billing_country ?? '') === 'US' ? 'selected' : '' ?>>United States</option>
                                        <option value="AE" <?= ($client->billing_country ?? '') === 'AE' ? 'selected' : '' ?>>United Arab Emirates</option>
                                        <option value="SA" <?= ($client->billing_country ?? '') === 'SA' ? 'selected' : '' ?>>Saudi Arabia</option>
                                        <option value="GB" <?= ($client->billing_country ?? '') === 'GB' ? 'selected' : '' ?>>United Kingdom</option>
                                        <option value="CA" <?= ($client->billing_country ?? '') === 'CA' ? 'selected' : '' ?>>Canada</option>
                                        <option value="AU" <?= ($client->billing_country ?? '') === 'AU' ? 'selected' : '' ?>>Australia</option>
                                        <option value="DE" <?= ($client->billing_country ?? '') === 'DE' ? 'selected' : '' ?>>Germany</option>
                                        <option value="FR" <?= ($client->billing_country ?? '') === 'FR' ? 'selected' : '' ?>>France</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- Shipping Address -->
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h6 class="border-bottom pb-2 mb-0"><?= t('clients.shipping_address') ?></h6>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="sameAsBilling" 
                                       <?= ($client->shipping_same_as_billing ?? false) ? 'checked' : '' ?>>
                                <label class="form-check-label" for="sameAsBilling">
                                    <?= t('clients.same_as_billing') ?>
                                </label>
                            </div>
                        </div>

                        <div id="shippingAddressFields">
                            <div class="mb-3">
                                <label class="form-label"><?= t('clients.street_address') ?></label>
                                <input type="text" class="form-control" name="shipping_street" 
                                       value="<?= htmlspecialchars($client->shipping_street ?? '') ?>">
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label"><?= t('clients.city') ?></label>
                                        <input type="text" class="form-control" name="shipping_city" 
                                               value="<?= htmlspecialchars($client->shipping_city ?? '') ?>">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label"><?= t('clients.state_province') ?></label>
                                        <input type="text" class="form-control" name="shipping_state" 
                                               value="<?= htmlspecialchars($client->shipping_state ?? '') ?>">
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label"><?= t('clients.postal_code') ?></label>
                                        <input type="text" class="form-control" name="shipping_postal_code" 
                                               value="<?= htmlspecialchars($client->shipping_postal_code ?? '') ?>">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label"><?= t('clients.country') ?></label>
                                        <select class="form-select" name="shipping_country">
                                            <option value=""><?= t('common.select_country') ?></option>
                                            <option value="US" <?= ($client->shipping_country ?? '') === 'US' ? 'selected' : '' ?>>United States</option>
                                            <option value="AE" <?= ($client->shipping_country ?? '') === 'AE' ? 'selected' : '' ?>>United Arab Emirates</option>
                                            <option value="SA" <?= ($client->shipping_country ?? '') === 'SA' ? 'selected' : '' ?>>Saudi Arabia</option>
                                            <option value="GB" <?= ($client->shipping_country ?? '') === 'GB' ? 'selected' : '' ?>>United Kingdom</option>
                                            <option value="CA" <?= ($client->shipping_country ?? '') === 'CA' ? 'selected' : '' ?>>Canada</option>
                                            <option value="AU" <?= ($client->shipping_country ?? '') === 'AU' ? 'selected' : '' ?>>Australia</option>
                                            <option value="DE" <?= ($client->shipping_country ?? '') === 'DE' ? 'selected' : '' ?>>Germany</option>
                                            <option value="FR" <?= ($client->shipping_country ?? '') === 'FR' ? 'selected' : '' ?>>France</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Financial Information -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0"><?= t('clients.financial_information') ?></h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label"><?= t('clients.credit_limit') ?></label>
                                    <div class="input-group">
                                        <input type="number" class="form-control" name="credit_limit" 
                                               value="<?= $client->credit_limit ?? '' ?>" step="0.01" min="0">
                                        <span class="input-group-text"><?= $default_currency ?? 'USD' ?></span>
                                    </div>
                                    <div class="form-text"><?= t('clients.credit_limit_help') ?></div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label"><?= t('clients.payment_terms') ?></label>
                                    <select class="form-select" name="payment_terms">
                                        <option value="Net 15" <?= ($client->payment_terms ?? 'Net 30') === 'Net 15' ? 'selected' : '' ?>>Net 15</option>
                                        <option value="Net 30" <?= ($client->payment_terms ?? 'Net 30') === 'Net 30' ? 'selected' : '' ?>>Net 30</option>
                                        <option value="Net 45" <?= ($client->payment_terms ?? '') === 'Net 45' ? 'selected' : '' ?>>Net 45</option>
                                        <option value="Net 60" <?= ($client->payment_terms ?? '') === 'Net 60' ? 'selected' : '' ?>>Net 60</option>
                                        <option value="Due on Receipt" <?= ($client->payment_terms ?? '') === 'Due on Receipt' ? 'selected' : '' ?>>Due on Receipt</option>
                                        <option value="COD" <?= ($client->payment_terms ?? '') === 'COD' ? 'selected' : '' ?>>Cash on Delivery</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label"><?= t('clients.currency') ?></label>
                                    <select class="form-select" name="currency">
                                        <option value="USD" <?= ($client->currency ?? 'USD') === 'USD' ? 'selected' : '' ?>>USD - US Dollar</option>
                                        <option value="EUR" <?= ($client->currency ?? '') === 'EUR' ? 'selected' : '' ?>>EUR - Euro</option>
                                        <option value="AED" <?= ($client->currency ?? '') === 'AED' ? 'selected' : '' ?>>AED - UAE Dirham</option>
                                        <option value="SAR" <?= ($client->currency ?? '') === 'SAR' ? 'selected' : '' ?>>SAR - Saudi Riyal</option>
                                        <option value="GBP" <?= ($client->currency ?? '') === 'GBP' ? 'selected' : '' ?>>GBP - British Pound</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label"><?= t('clients.tax_exempt') ?></label>
                                    <select class="form-select" name="tax_exempt">
                                        <option value="0" <?= ($client->tax_exempt ?? 0) == 0 ? 'selected' : '' ?>><?= t('common.no') ?></option>
                                        <option value="1" <?= ($client->tax_exempt ?? 0) == 1 ? 'selected' : '' ?>><?= t('common.yes') ?></option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label"><?= t('clients.tax_exempt_number') ?></label>
                            <input type="text" class="form-control" name="tax_exempt_number" 
                                   value="<?= htmlspecialchars($client->tax_exempt_number ?? '') ?>">
                            <div class="form-text"><?= t('clients.tax_exempt_number_help') ?></div>
                        </div>
                    </div>
                </div>

                <!-- Additional Information -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0"><?= t('clients.additional_information') ?></h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label"><?= t('clients.notes') ?></label>
                            <textarea class="form-control" name="notes" rows="4"><?= htmlspecialchars($client->notes ?? '') ?></textarea>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label"><?= t('clients.assigned_sales_rep') ?></label>
                                    <select class="form-select" name="assigned_sales_rep_id">
                                        <option value=""><?= t('common.select_user') ?></option>
                                        <?php if (!empty($sales_reps)): ?>
                                        <?php foreach ($sales_reps as $rep): ?>
                                        <option value="<?= $rep->id ?>" <?= $client->assigned_sales_rep_id == $rep->id ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($rep->first_name . ' ' . $rep->last_name) ?>
                                        </option>
                                        <?php endforeach; ?>
                                        <?php endif; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label"><?= t('clients.priority_level') ?></label>
                                    <select class="form-select" name="priority_level">
                                        <option value="normal" <?= ($client->priority_level ?? 'normal') === 'normal' ? 'selected' : '' ?>>
                                            <?= t('clients.priority.normal') ?>
                                        </option>
                                        <option value="high" <?= ($client->priority_level ?? '') === 'high' ? 'selected' : '' ?>>
                                            <?= t('clients.priority.high') ?>
                                        </option>
                                        <option value="vip" <?= ($client->priority_level ?? '') === 'vip' ? 'selected' : '' ?>>
                                            <?= t('clients.priority.vip') ?>
                                        </option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" name="email_notifications" 
                                   <?= !empty($client->email_notifications) ? 'checked' : '' ?>>
                            <label class="form-check-label">
                                <?= t('clients.enable_email_notifications') ?>
                            </label>
                        </div>

                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" name="marketing_consent" 
                                   <?= !empty($client->marketing_consent) ? 'checked' : '' ?>>
                            <label class="form-check-label">
                                <?= t('clients.marketing_consent') ?>
                            </label>
                            <div class="form-text"><?= t('clients.marketing_consent_help') ?></div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <!-- Contact Person (for Business) -->
                <div class="card mb-4" id="contactPersonCard">
                    <div class="card-header">
                        <h5 class="mb-0"><?= t('clients.primary_contact') ?></h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label"><?= t('clients.contact_name') ?></label>
                            <input type="text" class="form-control" name="contact_person_name" 
                                   value="<?= htmlspecialchars($client->contact_person_name ?? '') ?>">
                        </div>

                        <div class="mb-3">
                            <label class="form-label"><?= t('clients.contact_title') ?></label>
                            <input type="text" class="form-control" name="contact_person_title" 
                                   value="<?= htmlspecialchars($client->contact_person_title ?? '') ?>">
                        </div>

                        <div class="mb-3">
                            <label class="form-label"><?= t('clients.contact_email') ?></label>
                            <input type="email" class="form-control" name="contact_person_email" 
                                   value="<?= htmlspecialchars($client->contact_person_email ?? '') ?>">
                        </div>

                        <div class="mb-3">
                            <label class="form-label"><?= t('clients.contact_phone') ?></label>
                            <input type="tel" class="form-control" name="contact_person_phone" 
                                   value="<?= htmlspecialchars($client->contact_person_phone ?? '') ?>">
                        </div>
                    </div>
                </div>

                <!-- Quick Stats -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0"><?= t('clients.quick_stats') ?></h5>
                    </div>
                    <div class="card-body">
                        <div class="row text-center">
                            <div class="col-6">
                                <div class="border rounded p-3 mb-3">
                                    <div class="h4 mb-1 text-primary"><?= $client->total_orders ?? 0 ?></div>
                                    <small class="text-muted"><?= t('clients.total_orders') ?></small>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="border rounded p-3 mb-3">
                                    <div class="h4 mb-1 text-success"><?= number_format($client->total_spent ?? 0, 0) ?></div>
                                    <small class="text-muted"><?= t('clients.total_spent') ?></small>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="border rounded p-3 mb-3">
                                    <div class="h4 mb-1 text-info"><?= $client->total_quotes ?? 0 ?></div>
                                    <small class="text-muted"><?= t('clients.total_quotes') ?></small>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="border rounded p-3 mb-3">
                                    <div class="h4 mb-1 text-warning"><?= number_format($client->outstanding_balance ?? 0, 0) ?></div>
                                    <small class="text-muted"><?= t('clients.outstanding') ?></small>
                                </div>
                            </div>
                        </div>

                        <?php if ($client->created_at): ?>
                        <div class="border-top pt-3">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="text-muted"><?= t('clients.member_since') ?>:</span>
                                <span><?= date('M d, Y', strtotime($client->created_at)) ?></span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="text-muted"><?= t('clients.last_order') ?>:</span>
                                <span>
                                    <?php if ($client->last_order_date): ?>
                                    <?= date('M d, Y', strtotime($client->last_order_date)) ?>
                                    <?php else: ?>
                                    <span class="text-muted"><?= t('common.never') ?></span>
                                    <?php endif; ?>
                                </span>
                            </div>
                        </div>
                        <?php endif; ?>
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
                            <a href="/clients/<?= $client->id ?>" class="btn btn-outline-info">
                                <i class="fas fa-eye"></i> <?= t('common.view_client') ?>
                            </a>
                            <a href="/quotes/create?client_id=<?= $client->id ?>" class="btn btn-outline-success">
                                <i class="fas fa-plus"></i> <?= t('clients.create_quote') ?>
                            </a>
                            <a href="/clients" class="btn btn-outline-secondary">
                                <i class="fas fa-list"></i> <?= t('clients.back_to_list') ?>
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
    // Toggle fields based on client type
    const clientTypeSelect = document.querySelector('select[name="client_type"]');
    const individualFields = document.getElementById('individualFields');
    const companyField = document.getElementById('companyField');
    const businessFields = document.getElementById('businessFields');
    const contactPersonCard = document.getElementById('contactPersonCard');
    
    function toggleClientTypeFields() {
        const clientType = clientTypeSelect.value;
        
        if (clientType === 'individual') {
            individualFields.style.display = 'block';
            companyField.style.display = 'none';
            businessFields.style.display = 'none';
            contactPersonCard.style.display = 'none';
            
            // Make individual fields required
            document.querySelector('input[name="first_name"]').required = true;
            document.querySelector('input[name="last_name"]').required = true;
            document.querySelector('input[name="company_name"]').required = false;
        } else {
            individualFields.style.display = 'none';
            companyField.style.display = 'block';
            businessFields.style.display = 'block';
            contactPersonCard.style.display = 'block';
            
            // Make company field required
            document.querySelector('input[name="first_name"]').required = false;
            document.querySelector('input[name="last_name"]').required = false;
            document.querySelector('input[name="company_name"]').required = true;
        }
    }
    
    clientTypeSelect.addEventListener('change', toggleClientTypeFields);
    toggleClientTypeFields(); // Initial state
    
    // Handle "Same as Billing" checkbox
    const sameAsBillingCheckbox = document.getElementById('sameAsBilling');
    const shippingFields = document.getElementById('shippingAddressFields');
    
    function toggleShippingFields() {
        if (sameAsBillingCheckbox.checked) {
            shippingFields.style.display = 'none';
            // Copy billing address to shipping
            copyBillingToShipping();
        } else {
            shippingFields.style.display = 'block';
        }
    }
    
    function copyBillingToShipping() {
        const billingFields = ['street', 'city', 'state', 'postal_code', 'country'];
        billingFields.forEach(field => {
            const billingInput = document.querySelector(`input[name="billing_${field}"], select[name="billing_${field}"]`);
            const shippingInput = document.querySelector(`input[name="shipping_${field}"], select[name="shipping_${field}"]`);
            if (billingInput && shippingInput) {
                shippingInput.value = billingInput.value;
            }
        });
    }
    
    sameAsBillingCheckbox.addEventListener('change', toggleShippingFields);
    toggleShippingFields(); // Initial state
});

document.getElementById('clientForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    
    fetch('/clients/<?= $client->id ?>', {
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
            // Optionally redirect to client view
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
    if (confirm('<?= t('clients.confirm_reset_form') ?>')) {
        document.getElementById('clientForm').reset();
        // Remove any validation classes
        document.querySelectorAll('.is-invalid').forEach(el => {
            el.classList.remove('is-invalid');
        });
        
        // Reset field visibility
        document.querySelector('select[name="client_type"]').dispatchEvent(new Event('change'));
        document.getElementById('sameAsBilling').dispatchEvent(new Event('change'));
    }
}
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/app.php';
?>