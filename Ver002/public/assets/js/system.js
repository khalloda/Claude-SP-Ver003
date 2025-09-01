/*!
 * MISP Ver002 - Unified JavaScript System
 * File: public/assets/js/system.js
 * Purpose: Centralized JavaScript functionality with Chart.js theming
 * Dependencies: jQuery 3.7.0, Bootstrap 5.3.0, Chart.js 3.9.1
 * Architecture: Modular namespace with security, UI, and charting capabilities
 */

'use strict';

// ========================================================================
// NAMESPACE & CONFIGURATION
// ========================================================================

window.App = window.App || {};

// Global Configuration
App.config = {
    // Security
    csrfToken: window.App?.csrfToken || document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
    baseUrl: window.App?.baseUrl || '',
    
    // Localization
    lang: window.App?.lang || document.documentElement.lang || 'en',
    isRtl: window.App?.isRtl || document.dir === 'rtl' || false,
    user: window.App?.user || null,
    
    // API Configuration
    endpoints: {
        notifications: '/api/notifications',
        csrf: '/api/csrf-token',
        search: '/api/search',
        charts: '/api/charts'
    },
    
    // Timing Configuration
    timeouts: {
        toast: 5000,
        loading: 30000,
        debounce: 300,
        animation: 300
    },
    
    // UI Configuration
    ui: {
        animationsEnabled: !window.matchMedia('(prefers-reduced-motion: reduce)').matches,
        tooltipDelay: 500,
        loadingMinTime: 200
    }
};

// ========================================================================
// UTILITY FUNCTIONS
// ========================================================================

App.utils = {
    /**
     * Debounce function execution
     */
    debounce(func, wait, immediate = false) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                timeout = null;
                if (!immediate) func.apply(this, args);
            };
            const callNow = immediate && !timeout;
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
            if (callNow) func.apply(this, args);
        };
    },

    /**
     * Throttle function execution
     */
    throttle(func, limit) {
        let inThrottle;
        return function(...args) {
            if (!inThrottle) {
                func.apply(this, args);
                inThrottle = true;
                setTimeout(() => inThrottle = false, limit);
            }
        };
    },

    /**
     * Format currency with internationalization
     */
    formatCurrency(amount, currency = 'USD', locale = null) {
        try {
            const localeStr = locale || (App.config.lang === 'ar' ? 'ar-SA' : 'en-US');
            return new Intl.NumberFormat(localeStr, {
                style: 'currency',
                currency: currency,
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            }).format(parseFloat(amount) || 0);
        } catch (e) {
            return `${currency} ${parseFloat(amount).toFixed(2)}`;
        }
    },

    /**
     * Format number with locale support
     */
    formatNumber(number, options = {}) {
        const defaults = {
            minimumFractionDigits: 0,
            maximumFractionDigits: 2
        };
        const config = { ...defaults, ...options };
        
        try {
            const locale = App.config.lang === 'ar' ? 'ar-SA' : 'en-US';
            return new Intl.NumberFormat(locale, config).format(parseFloat(number) || 0);
        } catch (e) {
            return parseFloat(number).toFixed(config.maximumFractionDigits);
        }
    },

    /**
     * Format date with locale support
     */
    formatDate(date, options = {}) {
        const defaults = {
            year: 'numeric',
            month: 'short',
            day: 'numeric'
        };
        const config = { ...defaults, ...options };
        
        try {
            const locale = App.config.lang === 'ar' ? 'ar-SA' : 'en-US';
            return new Intl.DateTimeFormat(locale, config).format(new Date(date));
        } catch (e) {
            return new Date(date).toLocaleDateString();
        }
    },

    /**
     * Sanitize HTML to prevent XSS
     */
    sanitizeHtml(str) {
        if (typeof str !== 'string') return '';
        const temp = document.createElement('div');
        temp.textContent = str;
        return temp.innerHTML;
    },

    /**
     * Generate UUID v4
     */
    generateUuid() {
        return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, (c) => {
            const r = Math.random() * 16 | 0;
            const v = c === 'x' ? r : (r & 0x3 | 0x8);
            return v.toString(16);
        });
    },

    /**
     * Show loading overlay
     */
    showLoading(message = null) {
        let overlay = document.getElementById('loading-overlay');
        if (!overlay) {
            overlay = document.createElement('div');
            overlay.id = 'loading-overlay';
            overlay.className = 'loading-overlay';
            overlay.innerHTML = `
                <div class="d-flex flex-column align-items-center">
                    <div class="spinner mb-3"></div>
                    <div class="loading-text text-white">${message || 'Loading...'}</div>
                </div>
            `;
            document.body.appendChild(overlay);
        }
        
        overlay.classList.add('show');
        document.body.style.overflow = 'hidden';
    },

    /**
     * Hide loading overlay
     */
    hideLoading() {
        const overlay = document.getElementById('loading-overlay');
        if (overlay) {
            overlay.classList.remove('show');
            document.body.style.overflow = '';
        }
    },

    /**
     * Scroll to element smoothly
     */
    scrollTo(element, offset = 0) {
        const target = typeof element === 'string' ? document.querySelector(element) : element;
        if (target) {
            const targetPosition = target.getBoundingClientRect().top + window.pageYOffset - offset;
            window.scrollTo({
                top: targetPosition,
                behavior: 'smooth'
            });
        }
    }
};

// ========================================================================
// SECURITY & CSRF HANDLING
// ========================================================================

App.csrf = {
    token: App.config.csrfToken,

    getToken() {
        return this.token;
    },

    async refreshToken() {
        try {
            const response = await fetch(App.config.endpoints.csrf, {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            
            if (response.ok) {
                const data = await response.json();
                if (data.success && data.token) {
                    this.token = data.token;
                    App.config.csrfToken = data.token;
                    
                    // Update meta tag
                    const metaTag = document.querySelector('meta[name="csrf-token"]');
                    if (metaTag) metaTag.setAttribute('content', data.token);
                    
                    // Update form inputs
                    document.querySelectorAll('input[name="csrf_token"]').forEach(input => {
                        input.value = data.token;
                    });
                    
                    return data.token;
                }
            }
            throw new Error('Failed to refresh CSRF token');
        } catch (error) {
            console.error('CSRF token refresh failed:', error);
            throw error;
        }
    },

    setupFetch() {
        const originalFetch = window.fetch;
        window.fetch = (url, options = {}) => {
            const method = (options.method || 'GET').toUpperCase();
            
            if (!['GET', 'HEAD', 'OPTIONS', 'TRACE'].includes(method)) {
                options.headers = {
                    ...options.headers,
                    'X-CSRF-Token': this.getToken(),
                    'X-Requested-With': 'XMLHttpRequest'
                };
            }
            
            return originalFetch(url, options);
        };
    }
};

// ========================================================================
// HTTP CLIENT & AJAX
// ========================================================================

App.http = {
    async request(url, options = {}) {
        const defaults = {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        };

        const config = { ...defaults, ...options };
        
        // Add CSRF token for non-GET requests
        if (!['GET', 'HEAD', 'OPTIONS', 'TRACE'].includes(config.method)) {
            config.headers['X-CSRF-Token'] = App.csrf.getToken();
        }

        try {
            const response = await fetch(url, config);
            
            // Handle CSRF token expiration
            if (response.status === 419) {
                await App.csrf.refreshToken();
                App.toast.warning('Session refreshed. Please try again.');
                throw new Error('CSRF token expired');
            }
            
            if (!response.ok) {
                const errorData = await response.json().catch(() => ({ message: 'Request failed' }));
                throw new Error(errorData.message || `HTTP ${response.status}`);
            }
            
            const contentType = response.headers.get('content-type');
            if (contentType && contentType.includes('application/json')) {
                return await response.json();
            }
            
            return await response.text();
            
        } catch (error) {
            console.error('HTTP request failed:', error);
            App.toast.error(error.message);
            throw error;
        }
    },

    get(url, params = {}) {
        const urlWithParams = new URL(url, window.location.origin);
        Object.keys(params).forEach(key => urlWithParams.searchParams.append(key, params[key]));
        return this.request(urlWithParams.toString());
    },

    post(url, data = {}) {
        return this.request(url, {
            method: 'POST',
            body: JSON.stringify(data)
        });
    },

    put(url, data = {}) {
        return this.request(url, {
            method: 'PUT',
            body: JSON.stringify(data)
        });
    },

    delete(url) {
        return this.request(url, { method: 'DELETE' });
    },

    upload(url, formData) {
        return this.request(url, {
            method: 'POST',
            body: formData,
            headers: {
                // Don't set Content-Type for FormData - browser will set it with boundary
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-Token': App.csrf.getToken()
            }
        });
    }
};

// ========================================================================
// TOAST NOTIFICATION SYSTEM
// ========================================================================

App.toast = {
    container: null,

    init() {
        if (!this.container) {
            this.container = document.querySelector('.flash-messages');
            if (!this.container) {
                this.container = document.createElement('div');
                this.container.className = 'flash-messages position-fixed top-0 end-0 p-3';
                this.container.style.zIndex = '1060';
                document.body.appendChild(this.container);
            }
        }
    },

    show(message, type = 'info', title = null, duration = App.config.timeouts.toast) {
        this.init();
        
        const toastId = App.utils.generateUuid();
        const icon = this.getIcon(type);
        
        const toast = document.createElement('div');
        toast.id = toastId;
        toast.className = `alert alert-${type} alert-dismissible fade show`;
        toast.setAttribute('role', 'alert');
        
        toast.innerHTML = `
            <i class="fas fa-${icon} me-2"></i>
            ${title ? `<strong>${App.utils.sanitizeHtml(title)}:</strong> ` : ''}
            ${App.utils.sanitizeHtml(message)}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        `;
        
        this.container.appendChild(toast);
        
        // Trigger animation
        if (App.config.ui.animationsEnabled) {
            toast.style.transform = 'translateX(100%)';
            toast.style.transition = 'transform 0.3s ease-out';
            requestAnimationFrame(() => {
                toast.style.transform = 'translateX(0)';
            });
        }
        
        // Auto-dismiss
        if (duration > 0) {
            setTimeout(() => {
                this.dismiss(toastId);
            }, duration);
        }
        
        return toastId;
    },

    dismiss(toastId) {
        const toast = document.getElementById(toastId);
        if (toast) {
            if (window.bootstrap?.Alert) {
                const alertInstance = new bootstrap.Alert(toast);
                alertInstance.close();
            } else {
                toast.remove();
            }
        }
    },

    success(message, title = null, duration = App.config.timeouts.toast) {
        return this.show(message, 'success', title, duration);
    },

    error(message, title = null, duration = 0) {
        return this.show(message, 'danger', title, duration);
    },

    warning(message, title = null, duration = App.config.timeouts.toast) {
        return this.show(message, 'warning', title, duration);
    },

    info(message, title = null, duration = App.config.timeouts.toast) {
        return this.show(message, 'info', title, duration);
    },

    getIcon(type) {
        const icons = {
            success: 'check-circle',
            danger: 'exclamation-triangle',
            warning: 'exclamation-triangle',
            info: 'info-circle',
            primary: 'info-circle'
        };
        return icons[type] || 'info-circle';
    }
};

// ========================================================================
// FORM HANDLING SYSTEM
// ========================================================================

App.forms = {
    validate(form) {
        const isValid = true;
        const errors = [];
        
        // Clear previous errors
        form.querySelectorAll('.is-invalid').forEach(field => {
            field.classList.remove('is-invalid');
        });
        form.querySelectorAll('.invalid-feedback').forEach(feedback => {
            feedback.remove();
        });
        
        // Validate required fields
        form.querySelectorAll('[required]').forEach(field => {
            const value = field.value.trim();
            if (!value) {
                this.showFieldError(field, 'This field is required');
                errors.push(`${field.name || 'Field'} is required`);
            }
        });
        
        // Validate email fields
        form.querySelectorAll('input[type="email"]').forEach(field => {
            const value = field.value.trim();
            if (value && !this.isValidEmail(value)) {
                this.showFieldError(field, 'Please enter a valid email address');
                errors.push('Invalid email format');
            }
        });
        
        // Validate number fields
        form.querySelectorAll('input[type="number"]').forEach(field => {
            const value = field.value.trim();
            const min = field.getAttribute('min');
            const max = field.getAttribute('max');
            
            if (value && isNaN(value)) {
                this.showFieldError(field, 'Please enter a valid number');
                errors.push('Invalid number format');
            } else if (min && parseFloat(value) < parseFloat(min)) {
                this.showFieldError(field, `Minimum value is ${min}`);
                errors.push(`Value below minimum (${min})`);
            } else if (max && parseFloat(value) > parseFloat(max)) {
                this.showFieldError(field, `Maximum value is ${max}`);
                errors.push(`Value above maximum (${max})`);
            }
        });
        
        return { valid: errors.length === 0, errors };
    },

    showFieldError(field, message) {
        field.classList.add('is-invalid');
        
        const feedback = document.createElement('div');
        feedback.className = 'invalid-feedback';
        feedback.textContent = message;
        
        field.parentNode.appendChild(feedback);
    },

    isValidEmail(email) {
        const regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return regex.test(email);
    },

    async submit(form, options = {}) {
        const validation = this.validate(form);
        if (!validation.valid) {
            App.toast.error('Please correct the form errors before submitting');
            return { success: false, errors: validation.errors };
        }
        
        const formData = new FormData(form);
        const url = options.url || form.getAttribute('action') || window.location.pathname;
        
        App.utils.showLoading('Submitting...');
        
        try {
            const response = await App.http.upload(url, formData);
            App.utils.hideLoading();
            
            if (response.success) {
                App.toast.success(response.message || 'Form submitted successfully');
                
                if (response.redirect) {
                    setTimeout(() => {
                        window.location.href = response.redirect;
                    }, 1000);
                }
            } else {
                App.toast.error(response.message || 'Form submission failed');
            }
            
            return response;
            
        } catch (error) {
            App.utils.hideLoading();
            App.toast.error('Form submission failed: ' + error.message);
            return { success: false, error: error.message };
        }
    }
};

// ========================================================================
// CHART.JS GLOBAL CONFIGURATION & THEMING
// ========================================================================

App.charts = {
    // Initialize Chart.js with global defaults
    init() {
        if (typeof Chart !== 'undefined') {
            this.setupGlobalDefaults();
            this.setupThemeColors();
        }
    },

    setupGlobalDefaults() {
        // Get CSS custom properties for theming
        const root = getComputedStyle(document.documentElement);
        const primary = root.getPropertyValue('--primary').trim() || '#0d6efd';
        const success = root.getPropertyValue('--success').trim() || '#198754';
        const warning = root.getPropertyValue('--warning').trim() || '#ffc107';
        const danger = root.getPropertyValue('--danger').trim() || '#dc3545';
        const info = root.getPropertyValue('--info').trim() || '#0dcaf0';
        const textColor = root.getPropertyValue('--text').trim() || '#212529';
        const textMuted = root.getPropertyValue('--text-muted').trim() || '#6c757d';
        const bgColor = root.getPropertyValue('--bg').trim() || '#ffffff';

        Chart.defaults.responsive = true;
        Chart.defaults.maintainAspectRatio = false;
        Chart.defaults.interaction = {
            intersect: false,
            mode: 'index'
        };

        // Global font settings
        Chart.defaults.font = {
            family: "'Segoe UI', Tahoma, Geneva, Verdana, sans-serif",
            size: 12,
            weight: '400',
            lineHeight: 1.4
        };

        // Color scheme
        Chart.defaults.color = textMuted;
        Chart.defaults.borderColor = 'rgba(0, 0, 0, 0.1)';
        Chart.defaults.backgroundColor = bgColor;

        // Plugin defaults
        Chart.defaults.plugins.legend = {
            position: 'top',
            align: 'center',
            labels: {
                usePointStyle: true,
                padding: 20,
                font: {
                    size: 13,
                    weight: '500'
                },
                color: textColor
            }
        };

        Chart.defaults.plugins.tooltip = {
            backgroundColor: 'rgba(0, 0, 0, 0.9)',
            titleColor: 'white',
            bodyColor: 'white',
            footerColor: 'white',
            borderColor: 'rgba(255, 255, 255, 0.1)',
            borderWidth: 1,
            cornerRadius: 8,
            padding: 12,
            displayColors: true,
            titleFont: {
                size: 13,
                weight: '600'
            },
            bodyFont: {
                size: 12,
                weight: '400'
            }
        };

        // Scale defaults
        Chart.defaults.scales.linear = {
            grid: {
                color: 'rgba(0, 0, 0, 0.08)',
                drawBorder: false
            },
            ticks: {
                color: textMuted,
                padding: 8,
                font: {
                    size: 11
                }
            }
        };

        Chart.defaults.scales.category = {
            grid: {
                display: false,
                drawBorder: false
            },
            ticks: {
                color: textMuted,
                padding: 8,
                font: {
                    size: 11
                }
            }
        };

        // Chart type specific defaults
        Chart.defaults.datasets.line = {
            borderWidth: 3,
            fill: false,
            tension: 0.4,
            pointBackgroundColor: bgColor,
            pointBorderWidth: 2,
            pointRadius: 4,
            pointHoverRadius: 6
        };

        Chart.defaults.datasets.bar = {
            borderRadius: 4,
            borderSkipped: false
        };

        Chart.defaults.datasets.doughnut = {
            cutout: '65%',
            borderWidth: 0,
            hoverBorderWidth: 2
        };

        Chart.defaults.datasets.pie = {
            borderWidth: 0,
            hoverBorderWidth: 2
        };
    },

    setupThemeColors() {
        // Modern color palette from design tokens
        this.colorPalette = {
            primary: ['#667eea', '#764ba2'],
            success: ['#11998e', '#38ef7d'],
            info: ['#3093e3', '#2dd1ac'],
            warning: ['#f093fb', '#f5576c'],
            danger: ['#ff9a9e', '#fecfef'],
            secondary: ['#a8caba', '#5d4e75'],
            
            // Additional colors for charts
            chart: [
                '#667eea', '#11998e', '#3093e3', '#f093fb',
                '#38ef7d', '#2dd1ac', '#f5576c', '#764ba2',
                '#ff9a9e', '#fecfef', '#a8caba', '#5d4e75'
            ]
        };
    },

    // Get gradient colors for charts
    getGradient(ctx, colorStops, direction = 'vertical') {
        const gradient = direction === 'vertical' 
            ? ctx.createLinearGradient(0, 0, 0, ctx.canvas.height)
            : ctx.createLinearGradient(0, 0, ctx.canvas.width, 0);
        
        colorStops.forEach((stop, index) => {
            gradient.addColorStop(index / (colorStops.length - 1), stop);
        });
        
        return gradient;
    },

    // Create themed line chart
    createLine(ctx, data, options = {}) {
        const config = {
            type: 'line',
            data: this.applyChartTheme(data),
            options: {
                ...this.getCommonOptions(),
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: 'rgba(0, 0, 0, 0.05)'
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        }
                    }
                },
                ...options
            }
        };
        
        return new Chart(ctx, config);
    },

    // Create themed bar chart
    createBar(ctx, data, options = {}) {
        const config = {
            type: 'bar',
            data: this.applyChartTheme(data),
            options: {
                ...this.getCommonOptions(),
                scales: {
                    y: {
                        beginAtZero: true
                    }
                },
                ...options
            }
        };
        
        return new Chart(ctx, config);
    },

    // Create themed doughnut chart
    createDoughnut(ctx, data, options = {}) {
        const config = {
            type: 'doughnut',
            data: this.applyChartTheme(data, 'doughnut'),
            options: {
                ...this.getCommonOptions(),
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                },
                ...options
            }
        };
        
        return new Chart(ctx, config);
    },

    // Apply theme colors to chart data
    applyChartTheme(data, chartType = 'line') {
        const themedData = { ...data };
        
        if (themedData.datasets) {
            themedData.datasets = themedData.datasets.map((dataset, index) => {
                const colorIndex = index % this.colorPalette.chart.length;
                const baseColor = this.colorPalette.chart[colorIndex];
                
                return {
                    ...dataset,
                    backgroundColor: chartType === 'doughnut' 
                        ? this.colorPalette.chart.slice(0, dataset.data?.length || 4)
                        : this.hexToRgba(baseColor, 0.1),
                    borderColor: baseColor,
                    hoverBackgroundColor: this.hexToRgba(baseColor, 0.2),
                    hoverBorderColor: baseColor
                };
            });
        }
        
        return themedData;
    },

    // Common chart options
    getCommonOptions() {
        return {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: true
                }
            },
            elements: {
                point: {
                    hoverRadius: 8
                }
            }
        };
    },

    // Convert hex to rgba
    hexToRgba(hex, alpha = 1) {
        const r = parseInt(hex.slice(1, 3), 16);
        const g = parseInt(hex.slice(3, 5), 16);
        const b = parseInt(hex.slice(5, 7), 16);
        return `rgba(${r}, ${g}, ${b}, ${alpha})`;
    }
};

// ========================================================================
// UI ENHANCEMENTS & INTERACTIONS
// ========================================================================

App.ui = {
    init() {
        this.initTooltips();
        this.initPopovers();
        this.initAnimations();
        this.initFormEnhancements();
        this.initTableEnhancements();
        this.setupEventListeners();
    },

    initTooltips() {
        if (typeof bootstrap !== 'undefined') {
            const tooltips = document.querySelectorAll('[data-bs-toggle="tooltip"]');
            tooltips.forEach(tooltip => {
                new bootstrap.Tooltip(tooltip, {
                    delay: { show: App.config.ui.tooltipDelay, hide: 100 }
                });
            });
        }
    },

    initPopovers() {
        if (typeof bootstrap !== 'undefined') {
            const popovers = document.querySelectorAll('[data-bs-toggle="popover"]');
            popovers.forEach(popover => {
                new bootstrap.Popover(popover);
            });
        }
    },

    initAnimations() {
        if (App.config.ui.animationsEnabled) {
            // Animate cards on page load
            const cards = document.querySelectorAll('.card');
            cards.forEach((card, index) => {
                card.style.animationDelay = `${index * 50}ms`;
                card.classList.add('fade-in');
            });
            
            // Setup intersection observer for scroll animations
            this.setupScrollAnimations();
        }
    },

    setupScrollAnimations() {
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('animate-in');
                }
            });
        }, { threshold: 0.1 });

        document.querySelectorAll('.animate-on-scroll').forEach(el => {
            observer.observe(el);
        });
    },

    initFormEnhancements() {
        // Auto-format number inputs
        document.querySelectorAll('input[type="number"]').forEach(input => {
            input.addEventListener('blur', (e) => {
                const value = parseFloat(e.target.value);
                if (!isNaN(value)) {
                    e.target.value = App.utils.formatNumber(value);
                }
            });
        });

        // Auto-format currency inputs
        document.querySelectorAll('.currency-input').forEach(input => {
            input.addEventListener('blur', (e) => {
                const value = parseFloat(e.target.value.replace(/[^0-9.-]/g, ''));
                if (!isNaN(value)) {
                    e.target.value = value.toFixed(2);
                }
            });
        });
    },

    initTableEnhancements() {
        // Make tables responsive
        document.querySelectorAll('.table').forEach(table => {
            if (!table.parentElement.classList.contains('table-responsive')) {
                const wrapper = document.createElement('div');
                wrapper.className = 'table-responsive';
                table.parentNode.insertBefore(wrapper, table);
                wrapper.appendChild(table);
            }
        });
    },

    setupEventListeners() {
        // Global click handler for various UI interactions
        document.addEventListener('click', (e) => {
            // Handle delete confirmations
            if (e.target.matches('[data-confirm]')) {
                e.preventDefault();
                this.handleDeleteConfirmation(e.target);
            }
            
            // Handle AJAX form submissions
            if (e.target.matches('[data-ajax-submit]')) {
                e.preventDefault();
                const form = e.target.closest('form');
                if (form) App.forms.submit(form);
            }
        });

        // Handle form submissions with data-ajax attribute
        document.addEventListener('submit', (e) => {
            if (e.target.hasAttribute('data-ajax')) {
                e.preventDefault();
                App.forms.submit(e.target);
            }
        });

        // Search input handling
        document.addEventListener('input', App.utils.debounce((e) => {
            if (e.target.hasAttribute('data-search')) {
                this.handleSearch(e.target);
            }
        }, App.config.timeouts.debounce));
    },

    async handleDeleteConfirmation(element) {
        const message = element.getAttribute('data-confirm') || 'Are you sure you want to delete this item?';
        const url = element.getAttribute('href') || element.getAttribute('data-url');
        
        if (confirm(message) && url) {
            try {
                App.utils.showLoading('Deleting...');
                const response = await App.http.delete(url);
                App.utils.hideLoading();
                
                if (response.success) {
                    App.toast.success(response.message || 'Item deleted successfully');
                    
                    if (response.redirect) {
                        setTimeout(() => window.location.href = response.redirect, 1000);
                    } else {
                        // Remove element or reload page
                        const row = element.closest('tr') || element.closest('.list-item');
                        if (row) {
                            row.remove();
                        } else {
                            location.reload();
                        }
                    }
                }
            } catch (error) {
                App.utils.hideLoading();
                // Error already handled by http client
            }
        }
    },

    async handleSearch(input) {
        const query = input.value.trim();
        const target = input.getAttribute('data-search');
        
        if (query.length >= 2) {
            try {
                const response = await App.http.get(App.config.endpoints.search, {
                    q: query,
                    type: target
                });
                
                if (response.success && response.html) {
                    const targetElement = document.querySelector(target);
                    if (targetElement) {
                        targetElement.innerHTML = response.html;
                    }
                }
            } catch (error) {
                // Error already handled by http client
            }
        }
    }
};

// ========================================================================
// APPLICATION INITIALIZATION
// ========================================================================

App.init = function() {
    console.log('🚀 Initializing MISP System...');
    
    // Initialize core systems
    App.csrf.setupFetch();
    App.toast.init();
    App.charts.init();
    App.ui.init();
    
    // Format numbers and currencies on page load
    this.formatDisplayValues();
    
    // Setup global error handling
    this.setupErrorHandling();
    
    console.log('✅ MISP System initialized successfully');
    
    // Dispatch custom event for other scripts
    document.dispatchEvent(new CustomEvent('app:initialized', {
        detail: { version: '2.0', timestamp: Date.now() }
    }));
};

App.formatDisplayValues = function() {
    // Format currency displays
    document.querySelectorAll('.format-currency').forEach(element => {
        const amount = element.textContent;
        const currency = element.getAttribute('data-currency') || 'USD';
        if (!isNaN(parseFloat(amount))) {
            element.textContent = App.utils.formatCurrency(amount, currency);
        }
    });
    
    // Format number displays
    document.querySelectorAll('.format-number').forEach(element => {
        const number = element.textContent;
        const decimals = parseInt(element.getAttribute('data-decimals')) || 0;
        if (!isNaN(parseFloat(number))) {
            element.textContent = App.utils.formatNumber(number, { maximumFractionDigits: decimals });
        }
    });
    
    // Format date displays
    document.querySelectorAll('.format-date').forEach(element => {
        const dateStr = element.textContent;
        const format = element.getAttribute('data-format');
        try {
            element.textContent = App.utils.formatDate(dateStr, format ? JSON.parse(format) : {});
        } catch (e) {
            // Keep original text if formatting fails
        }
    });
};

App.setupErrorHandling = function() {
    // Global error handler
    window.addEventListener('error', (event) => {
        console.error('Global error:', event.error);
        if (App.config.user?.role === 'admin') {
            App.toast.error(`JavaScript Error: ${event.error.message}`, 'Development Error');
        }
    });
    
    // Unhandled promise rejection handler
    window.addEventListener('unhandledrejection', (event) => {
        console.error('Unhandled promise rejection:', event.reason);
        if (App.config.user?.role === 'admin') {
            App.toast.error(`Promise Rejection: ${event.reason}`, 'Development Error');
        }
    });
};

// ========================================================================
// DOM READY INITIALIZATION
// ========================================================================

// Initialize when DOM is ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', App.init);
} else {
    App.init();
}

// Export to global scope
window.App = App;