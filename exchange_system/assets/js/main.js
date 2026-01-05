/**
 * Main JavaScript for Money Exchange System
 * Diamond Group
 */

// Utility Functions
const Utils = {
    // Format currency
    formatCurrency: (amount, currencyCode = 'IQD') => {
        return new Intl.NumberFormat('en-US', {
            style: 'decimal',
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        }).format(amount) + ' ' + currencyCode;
    },

    // Format date
    formatDate: (date) => {
        return new Date(date).toLocaleDateString();
    },

    // Show alert
    showAlert: (message, type = 'success') => {
        const alert = document.createElement('div');
        alert.className = `alert alert-${type}`;
        alert.innerHTML = `
            <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'}"></i>
            ${message}
        `;
        
        const container = document.querySelector('.content-container');
        if (container) {
            container.insertBefore(alert, container.firstChild);
            
            setTimeout(() => {
                alert.style.opacity = '0';
                setTimeout(() => alert.remove(), 300);
            }, 5000);
        }
    },

    // Confirm action
    confirm: (message, callback) => {
        if (window.confirm(message)) {
            callback();
        }
    },

    // Debounce function
    debounce: (func, wait) => {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }
};

// Modal Management
class Modal {
    constructor(id) {
        this.modal = document.getElementById(id);
        this.closeBtn = this.modal?.querySelector('.modal-close');
        
        if (this.closeBtn) {
            this.closeBtn.addEventListener('click', () => this.close());
        }
        
        // Close on outside click
        this.modal?.addEventListener('click', (e) => {
            if (e.target === this.modal) {
                this.close();
            }
        });
    }

    open() {
        if (this.modal) {
            this.modal.classList.add('active');
        }
    }

    close() {
        if (this.modal) {
            this.modal.classList.remove('active');
        }
    }
}

// Table Management
class DataTable {
    constructor(tableId, options = {}) {
        this.table = document.getElementById(tableId);
        this.options = {
            searchable: options.searchable ?? true,
            sortable: options.sortable ?? true,
            exportable: options.exportable ?? true,
            printable: options.printable ?? true,
            ...options
        };
        
        if (this.options.searchable) {
            this.initSearch();
        }
        
        if (this.options.sortable) {
            this.initSort();
        }
    }

    initSearch() {
        const searchInput = document.getElementById('tableSearch');
        if (searchInput) {
            searchInput.addEventListener('input', Utils.debounce((e) => {
                this.filterTable(e.target.value);
            }, 300));
        }
    }

    filterTable(searchTerm) {
        const tbody = this.table?.querySelector('tbody');
        if (!tbody) return;

        const rows = tbody.querySelectorAll('tr');
        const term = searchTerm.toLowerCase();

        rows.forEach(row => {
            const text = row.textContent.toLowerCase();
            row.style.display = text.includes(term) ? '' : 'none';
        });
    }

    initSort() {
        const headers = this.table?.querySelectorAll('th[data-sortable]');
        
        headers?.forEach(header => {
            header.style.cursor = 'pointer';
            header.addEventListener('click', () => {
                this.sortTable(header);
            });
        });
    }

    sortTable(header) {
        const tbody = this.table?.querySelector('tbody');
        if (!tbody) return;

        const columnIndex = Array.from(header.parentElement.children).indexOf(header);
        const rows = Array.from(tbody.querySelectorAll('tr'));
        const isAscending = header.classList.contains('sort-asc');

        rows.sort((a, b) => {
            const aValue = a.children[columnIndex]?.textContent.trim();
            const bValue = b.children[columnIndex]?.textContent.trim();

            if (!isNaN(aValue) && !isNaN(bValue)) {
                return isAscending ? aValue - bValue : bValue - aValue;
            }

            return isAscending 
                ? aValue.localeCompare(bValue)
                : bValue.localeCompare(aValue);
        });

        // Update table
        rows.forEach(row => tbody.appendChild(row));

        // Update sort indicators
        this.table?.querySelectorAll('th').forEach(th => {
            th.classList.remove('sort-asc', 'sort-desc');
        });
        header.classList.add(isAscending ? 'sort-desc' : 'sort-asc');
    }

    export(format = 'csv') {
        if (format === 'csv') {
            this.exportCSV();
        }
    }

    exportCSV() {
        const rows = [];
        const headers = [];

        // Get headers
        this.table?.querySelectorAll('thead th').forEach(th => {
            headers.push(th.textContent.trim());
        });
        rows.push(headers.join(','));

        // Get data
        this.table?.querySelectorAll('tbody tr').forEach(tr => {
            const row = [];
            tr.querySelectorAll('td').forEach(td => {
                row.push('"' + td.textContent.trim().replace(/"/g, '""') + '"');
            });
            rows.push(row.join(','));
        });

        // Download
        const csv = rows.join('\n');
        const blob = new Blob([csv], { type: 'text/csv' });
        const url = window.URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = 'export_' + Date.now() + '.csv';
        a.click();
        window.URL.revokeObjectURL(url);
    }

    print() {
        const printWindow = window.open('', '', 'height=600,width=800');
        printWindow.document.write('<html><head><title>Print</title>');
        printWindow.document.write('<style>');
        printWindow.document.write('body { font-family: Arial, sans-serif; }');
        printWindow.document.write('table { width: 100%; border-collapse: collapse; }');
        printWindow.document.write('th, td { border: 1px solid #000; padding: 8px; text-align: left; }');
        printWindow.document.write('th { background-color: #f0f0f0; }');
        printWindow.document.write('</style></head><body>');
        printWindow.document.write(this.table?.outerHTML || '');
        printWindow.document.write('</body></html>');
        printWindow.document.close();
        printWindow.print();
    }
}

// Form Validation
class FormValidator {
    constructor(formId) {
        this.form = document.getElementById(formId);
        this.errors = {};
    }

    validate(rules) {
        this.errors = {};
        
        for (const [field, fieldRules] of Object.entries(rules)) {
            const input = this.form?.querySelector(`[name="${field}"]`);
            const value = input?.value.trim();

            if (fieldRules.required && !value) {
                this.errors[field] = 'This field is required';
                continue;
            }

            if (fieldRules.min && value && value.length < fieldRules.min) {
                this.errors[field] = `Minimum length is ${fieldRules.min}`;
                continue;
            }

            if (fieldRules.max && value && value.length > fieldRules.max) {
                this.errors[field] = `Maximum length is ${fieldRules.max}`;
                continue;
            }

            if (fieldRules.pattern && value && !fieldRules.pattern.test(value)) {
                this.errors[field] = 'Invalid format';
                continue;
            }

            if (fieldRules.email && value && !this.isEmail(value)) {
                this.errors[field] = 'Invalid email address';
                continue;
            }
        }

        this.displayErrors();
        return Object.keys(this.errors).length === 0;
    }

    isEmail(email) {
        return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
    }

    displayErrors() {
        // Clear previous errors
        this.form?.querySelectorAll('.error-message').forEach(el => el.remove());
        this.form?.querySelectorAll('.error').forEach(el => el.classList.remove('error'));

        // Display new errors
        for (const [field, message] of Object.entries(this.errors)) {
            const input = this.form?.querySelector(`[name="${field}"]`);
            if (input) {
                input.classList.add('error');
                const errorDiv = document.createElement('div');
                errorDiv.className = 'error-message';
                errorDiv.style.color = '#ef4444';
                errorDiv.style.fontSize = '0.875rem';
                errorDiv.style.marginTop = '0.25rem';
                errorDiv.textContent = message;
                input.parentElement?.appendChild(errorDiv);
            }
        }
    }

    clearErrors() {
        this.errors = {};
        this.form?.querySelectorAll('.error-message').forEach(el => el.remove());
        this.form?.querySelectorAll('.error').forEach(el => el.classList.remove('error'));
    }
}

// AJAX Helper
class Ajax {
    static async request(url, method = 'GET', data = null) {
        const options = {
            method,
            headers: {
                'Content-Type': 'application/json',
            }
        };

        if (data && method !== 'GET') {
            options.body = JSON.stringify(data);
        }

        try {
            const response = await fetch(url, options);
            const result = await response.json();
            return result;
        } catch (error) {
            console.error('Ajax error:', error);
            throw error;
        }
    }

    static get(url) {
        return this.request(url, 'GET');
    }

    static post(url, data) {
        return this.request(url, 'POST', data);
    }

    static put(url, data) {
        return this.request(url, 'PUT', data);
    }

    static delete(url) {
        return this.request(url, 'DELETE');
    }
}

// Currency Calculator
class CurrencyCalculator {
    constructor() {
        this.rates = {};
    }

    async loadRates() {
        try {
            // Load exchange rates from server
            const result = await Ajax.get('api/get_rates.php');
            if (result.success) {
                this.rates = result.rates;
            }
        } catch (error) {
            console.error('Failed to load exchange rates:', error);
        }
    }

    convert(amount, fromCurrency, toCurrency) {
        if (fromCurrency === toCurrency) {
            return amount;
        }

        if (!this.rates[fromCurrency] || !this.rates[toCurrency]) {
            return 0;
        }

        // Convert to IQD first, then to target currency
        const inIQD = amount * this.rates[fromCurrency];
        return inIQD / this.rates[toCurrency];
    }

    calculate(amount, rate) {
        return amount * rate;
    }
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', () => {
    // Initialize currency calculator
    window.currencyCalc = new CurrencyCalculator();
    window.currencyCalc.loadRates();

    // Auto-calculate commission
    const amountInputs = document.querySelectorAll('[data-auto-calculate]');
    amountInputs.forEach(input => {
        input.addEventListener('input', (e) => {
            const amount = parseFloat(e.target.value) || 0;
            const rate = parseFloat(e.target.dataset.commissionRate) || 0;
            const commissionField = document.getElementById(e.target.dataset.commissionField);
            
            if (commissionField) {
                const commission = (amount * rate) / 100;
                commissionField.value = commission.toFixed(2);
            }
        });
    });

    // Print buttons
    document.querySelectorAll('[data-print]').forEach(btn => {
        btn.addEventListener('click', () => {
            window.print();
        });
    });

    // Export buttons
    document.querySelectorAll('[data-export]').forEach(btn => {
        btn.addEventListener('click', () => {
            const tableId = btn.dataset.export;
            const table = new DataTable(tableId);
            table.export('csv');
        });
    });

    // Delete confirmation
    document.querySelectorAll('[data-confirm-delete]').forEach(btn => {
        btn.addEventListener('click', (e) => {
            if (!confirm('Are you sure you want to delete this item?')) {
                e.preventDefault();
            }
        });
    });

    // Number formatting
    document.querySelectorAll('[data-format="number"]').forEach(input => {
        input.addEventListener('blur', (e) => {
            const value = parseFloat(e.target.value) || 0;
            e.target.value = value.toFixed(2);
        });
    });
});

// Export for use in other scripts
window.Utils = Utils;
window.Modal = Modal;
window.DataTable = DataTable;
window.FormValidator = FormValidator;
window.Ajax = Ajax;
window.CurrencyCalculator = CurrencyCalculator;
