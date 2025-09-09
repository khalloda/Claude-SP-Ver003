<?php
/**
 * File: app/views/clients/create.php
 * Purpose: Client creation form with comprehensive client onboarding
 * Layout: Uses app layout with professional client creation interface
 */

$page_title = $page_title ?? t('clients.create_client');
$active_nav = 'clients';
ob_start();

// Get current user from passed data
$currentUser = $current_user ?? null;
$canCreate = $currentUser && in_array($currentUser['role'] ?? '', ['admin', 'manager', 'sales']);
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">
            <i class="fas fa-plus me-2"></i><?= t('clients.create_new_client') ?>
        </h1>
        <div class="btn-toolbar mb-2 mb-md-0">
            <a href="/clients" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left"></i> <?= t('common.back_to_list') ?>
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
                                        <option value="individual"><?= t('clients.type.individual') ?></option>
                                        <option value="business"><?= t('clients.type.business') ?></option>
                                        <option value="government"><?= t('clients.type.government') ?></option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label"><?= t('clients.status') ?></label>
                                    <select class="form-select" name="status">
                                        <option value="active"><?= t('clients.status.active') ?></option>
                                        <option value="prospect" selected><?= t('clients.status.prospect') ?></option>
                                        <option value="inactive"><?= t('clients.status.inactive') ?></option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="row" id="individualFields">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label"><?= t('clients.first_name') ?> *</label>
                                    <input type="text" class="form-control" name="first_name" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label"><?= t('clients.last_name') ?> *</label>
                                    <input type="text" class="form-control" name="last_name" required>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3" id="companyField" style="display: none;">
                            <label class="form-label"><?= t('clients.company_name') ?> *</label>
                            <input type="text" class="form-control" name="company_name">
                        </div>

                        <div class="row" id="businessFields" style="display: none;">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label"><?= t('clients.tax_number') ?></label>
                                    <input type="text" class="form-control" name="tax_number">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label"><?= t('clients.registration_number') ?></label>
                                    <input type="text" class="form-control" name="registration_number">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label"><?= t('clients.industry') ?></label>
                                    <select class="form-select" name="industry">
                                        <option value=""><?= t('common.select_industry') ?></option>
                                        <option value="automotive"><?= t('clients.industry.automotive') ?></option>
                                        <option value="construction"><?= t('clients.industry.construction') ?></option>
                                        <option value="manufacturing"><?= t('clients.industry.manufacturing') ?></option>
                                        <option value="retail"><?= t('clients.industry.retail') ?></option>
                                        <option value="healthcare"><?= t('clients.industry.healthcare') ?></option>
                                        <option value="technology"><?= t('clients.industry.technology') ?></option>
                                        <option value="education"><?= t('clients.industry.education') ?></option>
                                        <option value="government"><?= t('clients.industry.government') ?></option>
                                        <option value="other"><?= t('clients.industry.other') ?></option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label"><?= t('clients.client_source') ?></label>
                                    <select class="form-select" name="client_source">
                                        <option value=""><?= t('common.select_source') ?></option>
                                        <option value="referral"><?= t('clients.source.referral') ?></option>
                                        <option value="website"><?= t('clients.source.website') ?></option>
                                        <option value="social_media"><?= t('clients.source.social_media') ?></option>
                                        <option value="advertisement"><?= t('clients.source.advertisement') ?></option>
                                        <option value="cold_call"><?= t('clients.source.cold_call') ?></option>
                                        <option value="trade_show"><?= t('clients.source.trade_show') ?></option>
                                        <option value="existing_client"><?= t('clients.source.existing_client') ?></option>
                                        <option value="other"><?= t('clients.source.other') ?></option>
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
                                    <input type="email" class="form-control" name="email" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label"><?= t('clients.secondary_email') ?></label>
                                    <input type="email" class="form-control" name="secondary_email">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label"><?= t('clients.phone') ?> *</label>
                                    <input type="tel" class="form-control" name="phone" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label"><?= t('clients.mobile') ?></label>
                                    <input type="tel" class="form-control" name="mobile">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label"><?= t('clients.fax') ?></label>
                                    <input type="tel" class="form-control" name="fax">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label"><?= t('clients.website') ?></label>
                                    <input type="url" class="form-control" name="website">
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label"><?= t('clients.preferred_contact_method') ?></label>
                            <select class="form-select" name="preferred_contact_method">
                                <option value="email"><?= t('clients.contact.email') ?></option>
                                <option value="phone"><?= t('clients.contact.phone') ?></option>
                                <option value="mobile"><?= t('clients.contact.mobile') ?></option>
                                <option value="fax"><?= t('clients.contact.fax') ?></option>
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
                        <h6 class="border-bottom pb-2 mb-3"><?= t('clients.billing_address') ?></h6>
                        <div class="mb-3">
                            <label class="form-label"><?= t('clients.street_address') ?></label>
                            <input type="text" class="form-control" name="billing_street">
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label"><?= t('clients.city') ?></label>
                                    <input type="text" class="form-control" name="billing_city">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label"><?= t('clients.state_province') ?></label>
                                    <input type="text" class="form-control" name="billing_state">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label"><?= t('clients.postal_code') ?></label>
                                    <input type="text" class="form-control" name="billing_postal_code">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label"><?= t('clients.country') ?></label>
                                    <select class="form-select" name="billing_country">
                                        <option value=""><?= t('common.select_country') ?></option>
                                        <option value="US">United States</option>
                                        <option value="AE">United Arab Emirates</option>
                                        <option value="SA">Saudi Arabia</option>
                                        <option value="GB">United Kingdom</option>
                                        <option value="CA">Canada</option>
                                        <option value="AU">Australia</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h6 class="border-bottom pb-2 mb-0"><?= t('clients.shipping_address') ?></h6>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="sameAsBilling" checked>
                                <label class="form-check-label" for="sameAsBilling">
                                    <?= t('clients.same_as_billing') ?>
                                </label>
                            </div>
                        </div>

                        <div id="shippingAddressFields" style="display: none;">
                            <div class="mb-3">
                                <label class="form-label"><?= t('clients.street_address') ?></label>
                                <input type="text" class="form-control" name="shipping_street">
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label"><?= t('clients.city') ?></label>
                                        <input type="text" class="form-control" name="shipping_city">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label"><?= t('clients.state_province') ?></label>
                                        <input type="text" class="form-control" name="shipping_state">
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label"><?= t('clients.postal_code') ?></label>
                                        <input type="text" class="form-control" name="shipping_postal_code">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label"><?= t('clients.country') ?></label>
                                        <select class="form-select" name="shipping_country">
                                            <option value=""><?= t('common.select_country') ?></option>
                                            <option value="US">United States</option>
                                            <option value="AE">United Arab Emirates</option>
                                            <option value="SA">Saudi Arabia</option>
                                            <option value="GB">United Kingdom</option>
                                            <option value="CA">Canada</option>
                                            <option value="AU">Australia</option>
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
                                        <input type="number" class="form-control" name="credit_limit" step="0.01" min="0" value="1000">
                                        <span class="input-group-text">USD</span>
                                    </div>
                                    <div class="form-text"><?= t('clients.credit_limit_help') ?></div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label"><?= t('clients.payment_terms') ?></label>
                                    <select class="form-select" name="payment_terms">
                                        <option value="Net 15">Net 15</option>
                                        <option value="Net 30" selected>Net 30</option>
                                        <option value="Net 45">Net 45</option>
                                        <option value="Net 60">Net 60</option>
                                        <option value="Due on Receipt">Due on Receipt</option>
                                        <option value="COD">Cash on Delivery</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label"><?= t('clients.currency') ?></label>
                                    <select class="form-select" name="currency">
                                        <option value="USD" selected>USD - US Dollar</option>
                                        <option value="EUR">EUR - Euro</option>
                                        <option value="AED">AED - UAE Dirham</option>
                                        <option value="SAR">SAR - Saudi Riyal</option>
                                        <option value="GBP">GBP - British Pound</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label"><?= t('clients.tax_exempt') ?></label>
                                    <select class="form-select" name="tax_exempt">
                                        <option value="0"><?= t('common.no') ?></option>
                                        <option value="1"><?= t('common.yes') ?></option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <!-- Contact Person (for Business) -->
                <div class="card mb-4" id="contactPersonCard" style="display: none;">
                    <div class="card-header">
                        <h5 class="mb-0"><?= t('clients.primary_contact') ?></h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label"><?= t('clients.contact_name') ?></label>
                            <input type="text" class="form-control" name="contact_person_name">
                        </div>

                        <div class="mb-3">
                            <label class="form-label"><?= t('clients.contact_title') ?></label>
                            <input type="text" class="form-control" name="contact_person_title">
                        </div>

                        <div class="mb-3">
                            <label class="form-label"><?= t('clients.contact_email') ?></label>
                            <input type="email" class="form-control" name="contact_person_email">
                        </div>

                        <div class="mb-3">
                            <label class="form-label"><?= t('clients.contact_phone') ?></label>
                            <input type="tel" class="form-control" name="contact_person_phone">
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
                            <label class="form-label"><?= t('clients.assigned_sales_rep') ?></label>
                            <select class="form-select" name="assigned_sales_rep_id">
                                <option value=""><?= t('common.select_user') ?></option>
                                <?php if (!empty($sales_reps)): ?>
                                <?php foreach ($sales_reps as $rep): ?>
                                <option value="<?= $rep->id ?>">
                                    <?= htmlspecialchars($rep->first_name . ' ' . $rep->last_name) ?>
                                </option>
                                <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label"><?= t('clients.priority_level') ?></label>
                            <select class="form-select" name="priority_level">
                                <option value="normal" selected><?= t('clients.priority.normal') ?></option>
                                <option value="high"><?= t('clients.priority.high') ?></option>
                                <option value="vip"><?= t('clients.priority.vip') ?></option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label"><?= t('clients.notes') ?></label>
                            <textarea class="form-control" name="notes" rows="4"></textarea>
                        </div>

                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" name="email_notifications" checked>
                            <label class="form-check-label">
                                <?= t('clients.enable_email_notifications') ?>
                            </label>
                        </div>

                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" name="marketing_consent">
                            <label class="form-check-label">
                                <?= t('clients.marketing_consent') ?>
                            </label>
                            <div class="form-text"><?= t('clients.marketing_consent_help') ?></div>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="card">
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-plus"></i> <?= t('clients.create_client') ?>
                            </button>
                            <button type="button" class="btn btn-outline-secondary" onclick="resetForm()">
                                <i class="fas fa-undo"></i> <?= t('common.reset_form') ?>
                            </button>
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
            
            document.querySelector('input[name="first_name"]').required = true;
            document.querySelector('input[name="last_name"]').required = true;
            document.querySelector('input[name="company_name"]').required = false;
        } else {
            individualFields.style.display = 'none';
            companyField.style.display = 'block';
            businessFields.style.display = 'block';
            contactPersonCard.style.display = 'block';
            
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
    
    sameAsBillingCheckbox.addEventListener('change', function() {
        if (this.checked) {
            shippingFields.style.display = 'none';
        } else {
            shippingFields.style.display = 'block';
        }
    });
});

document.getElementById('clientForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    
    fetch('/clients', {
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
    if (confirm('<?= t('clients.confirm_reset_form') ?>')) {
        document.getElementById('clientForm').reset();
        document.querySelectorAll('.is-invalid').forEach(el => {
            el.classList.remove('is-invalid');
        });
        document.querySelector('select[name="client_type"]').dispatchEvent(new Event('change'));
    }
}
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/app.php';
?>