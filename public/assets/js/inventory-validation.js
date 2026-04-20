/**
 * Inventory Form Validation
 * Validates the add/edit medicine form
 */

function validateInventoryForm(formId) {
    const form = document.getElementById(formId);
    if (!form) return false;


    const brandExisting = form.querySelector('input[name="brand_existing"]')?.value.trim() || '';
    const brandNew = form.querySelector('input[name="brand_new"]')?.value.trim() || '';
    const medName = form.querySelector('input[name="med_name"]')?.value.trim() || '';
    const supplierExisting = form.querySelector('select[name="supplier_existing"]')?.value || '';
    const supplierNew = form.querySelector('input[name="supplier_new"]')?.value.trim() || '';
    const strength = form.querySelector('input[name="strength"]')?.value.trim() || '';
    const price = parseFloat(form.querySelector('input[name="price"]')?.value || '0');
    const lowStockThreshold = parseInt(form.querySelector('input[name="low_stock_threshold"]')?.value || '0');

  
    if (brandExisting === '' && brandNew === '') {
        showError('Brand name is required.');
        return false;
    }

  
    if (medName === '') {
        showError('Medicine name is required.');
        return false;
    }

   
    if (supplierExisting === '' && supplierNew === '') {
        showError('Supplier is required.');
        return false;
    }

    
    if (strength === '') {
        showError('Strength is required.');
        return false;
    }

    
    if (lowStockThreshold < 0) {
        showError('Low stock threshold cannot be negative.');
        return false;
    }

   
    if (price < 0) {
        showError('Price must be zero or positive.');
        return false;
    }

   
    clearError();
    return true;
}

function showError(message) {
    
    clearError();

    
    const errorDiv = document.createElement('div');
    errorDiv.className = 'alert alert-error inventory-validation-error';
    errorDiv.setAttribute('role', 'alert');
    errorDiv.textContent = message;
    errorDiv.style.cssText = 'grid-column: span 2; margin-bottom: 16px;';

    // Find the form and insert error at the top
    const form = document.querySelector('form.styled-form');
    if (form) {
        form.insertBefore(errorDiv, form.firstChild);
        errorDiv.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }
}

function clearError() {
    const errorDiv = document.querySelector('.inventory-validation-error');
    if (errorDiv) {
        errorDiv.remove();
    }
}

// Add real-time validation on form load
document.addEventListener('DOMContentLoaded', function () {
    const form = document.querySelector('form.styled-form');
    if (form) {
        // Prevent form submission if validation fails
        form.addEventListener('submit', function (e) {
            if (!validateInventoryForm('medicineForm')) {
                e.preventDefault();
            }
        });

        // Real-time validation on blur
        const fieldsToValidate = [
            'brand_new',
            'med_name',
            'supplier_new',
            'strength',
            'price',
            'low_stock_threshold'
        ];

        fieldsToValidate.forEach(fieldName => {
            const field = form.querySelector(`[name="${fieldName}"]`);
            if (field) {
                field.addEventListener('blur', clearError);
            }
        });
    }
});
