/**
 * File: public/assets/js/app.js
 * Purpose: Main application JavaScript with security and functionality
 * Dependencies: jQuery 3.7.0, Bootstrap 5.3.0, Chart.js 3.9.1
 * Notes: CSRF handling, AJAX helpers, form validation, UI enhancements
 */

'use strict';

// Application namespace
window.App = window.App || {};

// Configuration
App.config = {
    csrfToken: window.App?.csrfToken || '',
    baseUrl: window.App?.baseUrl || '',
    lang: window.App?.lang || 'en',
    isRtl: window.App?.isRtl || false,
    user: window.App?.user || null,
    
    // API endpoints
    endpoints: {
        notifications: '/api/notifications',
        csrf: '/api/csrf-token',
        search: '/api/search'
    },
    
    // Timeouts
    timeouts: {
        toast: 5000,
        loading: 30000,
        debounce: 300
    }
};

// Utility functions
App.utils = {
    // Debounce function
    debounce: function(func, wait, immediate) {
        let timeout;
        return function executedFunction() {
            const context = this;
            const args = arguments;
            const later = function() {
                timeout = null;
                if (!immediate) func.apply(context, args);
            };
            const callNow = immediate && !timeout;
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
            if (callNow) func.apply(context, args);
        };
    },
    
    // Format currency
    formatCurrency: function(amount, currency = 'USD') {
        try {
            return new Intl.NumberFormat(App.config.lang === 'ar' ? 'ar-SA' : 'en-US', {
                style: 'currency',
                currency: currency
            }).format(amount);
        } catch (e) {
            return currency + ' ' + parseFloat(amount).toFixed(2);
        }
    },
    
    // Format number
    formatNumber: function(number, decimals = 2) {
        try {
            return new Intl.NumberFormat(App.config.lang === 'ar' ? 'ar-SA' : 'en-US', {
                minimumFractionDigits: decimals,
                maximumFractionDigits: decimals
            }).format(number);
        } catch (e) {
            return parseFloat(number).toFixed(decimals);
        }
    },
    
    // Sanitize HTML
    sanitizeHtml: function(str) {
        const temp = document.createElement('div');
        temp.textContent = str;
        return temp.innerHTML;
    },
    
    // Generate UUID
    generateUuid: function() {
        return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function(c) {
            const r = Math.random() * 16 | 0;
            const v = c === 'x' ? r : (r & 0x3 | 0x8);
            return v.toString(16);
        });
    },
    
    // Show/hide loading
    showLoading: function() {
        $('#loading-overlay').addClass('show');
    },
    
    hideLoading: function() {
        $('#loading-overlay').removeClass('show');
    }
};

// CSRF handling
App.csrf = {
    token: App.config.csrfToken,
    
    // Get current CSRF token
    getToken: function() {
        return this.token;
    },
    
    // Refresh CSRF token
    refreshToken: function() {
        return $.ajax({
            url: App.config.endpoints.csrf,
            method: 'GET',
            success: (response) => {
                if (response.success && response.token) {
                    this.token = response.token;
                    App.config.csrfToken = response.token;
                    $('meta[name="csrf-token"]').attr('content', response.token);
                    $('input[name="csrf_token"]').val(response.token);
                }
            }
        });
    },
    
    // Setup CSRF for AJAX requests
    setupAjax: function() {
        $.ajaxSetup({
            beforeSend: (xhr, settings) => {
                if (!/^(GET|HEAD|OPTIONS|TRACE)$/i.test(settings.type) && !settings.crossDomain) {
                    xhr.setRequestHeader('X-CSRF-Token', this.getToken());
                }
            }
        });
    }
};

// AJAX helper
App.ajax = {
    // Make AJAX request with error handling
    request: function(options) {
        const defaults = {
            method: 'GET',
            dataType: 'json',
            timeout: App.config.timeouts.loading,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        };
        
        const settings = $.extend({}, defaults, options);
        
        // Add CSRF token for non-GET requests
        if (!/^(GET|HEAD|OPTIONS|TRACE)$/i.test(settings.method)) {
            settings.headers['X-CSRF-Token'] = App.csrf.getToken();
        }
        
        return $.ajax(settings)
            .fail((xhr, status, error) => {
                console.error('AJAX request failed:', { xhr, status, error });
                
                // Handle CSRF token expiration
                if (xhr.status === 419) {
                    App.csrf.refreshToken().then(() => {
                        App.toast.error('Session expired. Please try again.');
                    });
                    return;
                }
                
                // Show generic error message
                const message = xhr.responseJSON?.message || 'An error occurred. Please try again.';
                App.toast.error(message);
            });
    },
    
    // GET request
    get: function(url, data = {}) {
        return this.request({
            url: url,
            method: 'GET',
            data: data
        });
    },
    
    // POST request
    post: function(url, data = {}) {
        return this.request({
            url: url,
            method: 'POST',
            data: data
        });
    },
    
    // PUT request
    put: function(url, data = {}) {
        return this.request({
            url: url,
            method: 'PUT',
            data: data
        });
    },
    
    // DELETE request
    delete: function(url) {
        return this.request({
            url: url,
            method: 'DELETE'
        });
    }
};

// Toast notifications
App.toast = {
    // Show success toast
    success: function(message, title = 'Success') {
        this.show(message, 'success', title);
    },
    
    // Show error toast
    error: function(message, title = 'Error') {
        this.show(message, 'danger', title);
    },
    
    // Show warning toast
    warning: function(message, title = 'Warning') {
        this.show(message, 'warning', title);
    },
    
    // Show info toast
    info: function(message, title = 'Info') {
        this.show(message, 'info', title);
    },
    
    // Show toast with custom type
    show: function(message, type = 'info', title = '') {
        const toastId = App.utils.generateUuid();
        const icon = this.getIcon(type);
        
        const toast = $(`
            <div class="alert alert-${type} alert-dismissible fade show" role="alert" id="${toastId}">
                <i class="fas fa-${icon} me-2"></i>
                ${title ? `<strong>${App.utils.sanitizeHtml(title)}:</strong> ` : ''}
                ${App.utils.sanitizeHtml(message)}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        `);
        
        $('.flash-messages').append(toast);
        
        // Auto-dismiss after timeout
        setTimeout(() => {
            $(`#${toastId}`).alert('close');
        }, App.config.timeouts.toast);
    },
    
    // Get icon for toast type
    getIcon: function(type) {
        const icons = {
            success: 'check-circle',
            danger: 'exclamation-triangle',
            warning: 'exclamation-triangle',
            info: 'info-circle'
        };
        return icons[type] || 'info-circle';
    }
};

// Form helpers
App.forms = {
    // Validate form
    validate: function(form) {
        const $form = $(form);
        let isValid = true;
        
        // Clear previous errors
        $form.find('.is-invalid').removeClass('is-invalid');
        $form.find('.invalid-feedback').remove();
        
        // Check required fields
        $form.find('[required]').each(function() {
            const $field = $(this);
            const value = $field.val().trim();
            
            if (!value) {
                this.showFieldError($field, 'This field is required');
                isValid = false;
            }
        });
        
        // Check email fields
        $form.find('input[type="email"]').each(function() {
            const $field = $(this);
            const value = $field.val().trim();
            
            if (value && !this.isValidEmail(value)) {
                this.showFieldError($field, 'Please enter a valid email address');
                isValid = false;
            }
        });
        
        return isValid;
    },
    
    // Show field error
    showFieldError: function($field, message) {
        $field.addClass('is-invalid');
        $field.after(`<div class="invalid-feedback">${App.utils.sanitizeHtml(message)}</div>`);
    },
    
    // Validate email
    isValidEmail: function(email) {
        const regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return regex.test(email);
    },
    
    // Submit form via AJAX
    submitAjax: function(form, options = {}) {
        const $form = $(form);
        const url = options.url || $form.attr('action');
        const method = options.method || $form.attr('method') || 'POST';
        
        if (!this.validate(form)) {
            return Promise.reject('Form validation failed');
        }
        
        const formData = new FormData(form);
        
        App.utils.showLoading();
        
        return App.ajax.request({
            url: url,
            method: method,
            data: formData,
            processData: false,
            contentType: false
        }).always(() => {
            App.utils.hideLoading();
        });
    }
};

// Data tables helper
App.dataTable = {
    // Initialize data table
    init: function(selector, options = {}) {
        const defaults = {
            responsive: true,
            pageLength: 25,
            lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
            language: {
                search: "Search:",
                lengthMenu: "Show _MENU_ entries",
                info: "Showing _START_ to _END_ of _TOTAL_ entries",
                infoEmpty: "Showing 0 to 0 of 0 entries",
                infoFiltered: "(filtered from _MAX_ total entries)",
                paginate: {
                    first: "First",
                    last: "Last",
                    next: "Next",
                    previous: "Previous"
                }
            }
        };
        
        // RTL support
        if (App.config.isRtl) {
            defaults.language = {
                ...defaults.language,
                search: "بحث:",
                lengthMenu: "عرض _MENU_ عنصر",
                info: "عرض _START_ إلى _END_ من _TOTAL_ عنصر",
                infoEmpty: "عرض 0 إلى 0 من 0 عنصر",
                paginate: {
                    first: "الأول",
                    last: "الأخير",
                    next: "التالي",
                    previous: "السابق"
                }
            };
        }
        
        const settings = $.extend({}, defaults, options);
        
        if ($.fn.DataTable) {
            return $(selector).DataTable(settings);
        }
        
        console.warn('DataTables not available');
        return null;
    }
};

// Chart helper
App.charts = {
    // Default chart options
    defaultOptions: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: 'top',
            },
            tooltip: {
                backgroundColor: 'rgba(0, 0, 0, 0.8)',
                titleColor: 'white',
                bodyColor: 'white',
                borderColor: 'rgba(255, 255, 255, 0.2)',
                borderWidth: 1
            }
        }
    },
    
    // Create line chart
    line: function(ctx, data, options = {}) {
        const config = {
            type: 'line',
            data: data,
            options: $.extend(true, {}, this.defaultOptions, options)
        };
        
        return new Chart(ctx, config);
    },
    
    // Create bar chart
    bar: function(ctx, data, options = {}) {
        const config = {
            type: 'bar',
            data: data,
            options: $.extend(true, {}, this.defaultOptions, options)
        };
        
        return new Chart(ctx, config);
    },
    
    // Create doughnut chart
    doughnut: function(ctx, data, options = {}) {
        const config = {
            type: 'doughnut',
            data: data,
            options: $.extend(true, {}, this.defaultOptions, {
                cutout: '60%'
            }, options)
        };
        
        return new Chart(ctx, config);
    }
};

// Initialize application
App.init = function() {
    // Setup CSRF
    App.csrf.setupAjax();
    
    // Initialize tooltips
    if (typeof bootstrap !== 'undefined') {
        const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.map(function(tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
        
        // Initialize popovers
        const popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
        popoverTriggerList.map(function(popoverTriggerEl) {
            return new bootstrap.Popover(popoverTriggerEl);
        });
    }
    
    // Handle form submissions
    $(document).on('submit', 'form[data-ajax="true"]', function(e) {
        e.preventDefault();
        
        App.forms.submitAjax(this)
            .done(function(response) {
                if (response.success) {
                    App.toast.success(response.message || 'Operation completed successfully');
                    
                    if (response.redirect) {
                        setTimeout(() => {
                            window.location.href = response.redirect;
                        }, 1000);
                    }
                } else {
                    App.toast.error(response.message || 'Operation failed');
                }
            });
    });
    
    // Handle delete confirmations
    $(document).on('click', '[data-confirm]', function(e) {
        e.preventDefault();
        
        const message = $(this).data('confirm') || 'Are you sure you want to delete this item?';
        const url = $(this).attr('href') || $(this).data('url');
        
        if (confirm(message)) {
            App.ajax.delete(url)
                .done(function(response) {
                    if (response.success) {
                        App.toast.success(response.message || 'Item deleted successfully');
                        
                        if (response.redirect) {
                            setTimeout(() => {
                                window.location.href = response.redirect;
                            }, 1000);
                        } else {
                            // Reload page or update UI
                            location.reload();
                        }
                    }
                });
        }
    });
    
    // Auto-save forms
    $(document).on('change', 'form[data-auto-save="true"] input, form[data-auto-save="true"] select, form[data-auto-save="true"] textarea', 
        App.utils.debounce(function() {
            const $form = $(this).closest('form');
            App.forms.submitAjax($form[0]);
        }, App.config.timeouts.debounce)
    );
    
    // Handle search inputs
    $(document).on('input', '[data-search]', App.utils.debounce(function() {
        const query = $(this).val();
        const target = $(this).data('search');
        
        if (query.length >= 2) {
            App.ajax.get(App.config.endpoints.search, { q: query, type: target })
                .done(function(response) {
                    if (response.success && response.data) {
                        // Update search results
                        $(target).html(response.html || '');
                    }
                });
        }
    }, App.config.timeouts.debounce));
    
    // Close flash messages automatically
    setTimeout(function() {
        $('.flash-messages .alert').alert('close');
    }, App.config.timeouts.toast);
    
    // Add fade-in animation to cards
    $('.card').addClass('fade-in');
    
    // Handle number formatting
    $('.format-currency').each(function() {
        const amount = $(this).text();
        if (!isNaN(amount)) {
            $(this).text(App.utils.formatCurrency(amount));
        }
    });
    
    $('.format-number').each(function() {
        const number = $(this).text();
        if (!isNaN(number)) {
            $(this).text(App.utils.formatNumber(number));
        }
    });
    
    console.log('App initialized successfully');
};

// Initialize when DOM is ready
$(document).ready(function() {
    App.init();
});

// Export to global scope
window.App = App;