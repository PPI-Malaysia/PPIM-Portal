/**
 * CSV Upload JavaScript
 */

document.addEventListener('DOMContentLoaded', function() {
    // Handle CSV file input validation
    const csvFileInput = document.querySelector('input[name="csv_file"]');
    
    if (csvFileInput) {
        csvFileInput.addEventListener('change', function() {
            validateCsvFile(this.files[0]);
        });
    }

    // Handle template download with feedback
    const templateLink = document.querySelector('a[href*="download_template"]');
    if (templateLink) {
        templateLink.addEventListener('click', function(e) {
            showToast('Downloading CSV template...', 'info');
        });
    }
});

/**
 * CSV file validation
 */
function validateCsvFile(file) {
    const errorContainer = createOrGetErrorContainer();
    
    // Clear previous errors
    errorContainer.innerHTML = '';
    errorContainer.style.display = 'none';

    if (!file) return;

    const errors = [];
    const warnings = [];

    // Check file type
    const allowedExtensions = ['csv', 'txt'];
    const fileName = file.name.toLowerCase();
    const fileExtension = fileName.split('.').pop();
    
    if (!allowedExtensions.includes(fileExtension)) {
        errors.push('Please select a CSV file (.csv or .txt extension required)');
    }

    // Check MIME type as additional validation
    const allowedMimeTypes = ['text/csv', 'text/plain', 'application/csv'];
    if (file.type && !allowedMimeTypes.includes(file.type)) {
        warnings.push('File type might not be CSV. Please ensure it\'s a proper CSV file.');
    }

    // Check file size (5MB limit)
    const maxSize = 5 * 1024 * 1024; // 5MB
    if (file.size > maxSize) {
        errors.push('File size must be less than 5MB. Current size: ' + formatFileSize(file.size));
    }

    // Check if file is empty
    if (file.size === 0) {
        errors.push('File appears to be empty');
    }

    // Size warnings for very small or large files
    if (file.size > 0 && file.size < 100) {
        warnings.push('File seems very small. Please ensure it contains data.');
    } else if (file.size > 2 * 1024 * 1024) { // 2MB
        warnings.push('Large file detected. Processing may take a few moments.');
    }

    // Display results
    if (errors.length > 0) {
        showFileErrors(errors, 'danger');
        disableUploadButton();
    } else if (warnings.length > 0) {
        showFileWarnings(warnings, file);
        enableUploadButton();
    } else {
        showFileSuccess(file);
        enableUploadButton();
    }
}

/**
 * Create or get error container
 */
function createOrGetErrorContainer() {
    let container = document.getElementById('csv-validation-errors');
    
    if (!container) {
        container = document.createElement('div');
        container.id = 'csv-validation-errors';
        container.className = 'alert alert-dismissible fade show';
        container.style.display = 'none';
        
        // Insert after file input with better positioning
        const fileInput = document.querySelector('input[name="csv_file"]');
        if (fileInput && fileInput.parentNode) {
            const insertPoint = fileInput.parentNode.nextSibling || fileInput.parentNode.parentNode.lastChild;
            fileInput.parentNode.parentNode.insertBefore(container, insertPoint);
        }
    }
    
    return container;
}

/**
 * Show file validation errors
 */
function showFileErrors(errors, type = 'danger') {
    const container = createOrGetErrorContainer();
    const icon = type === 'danger' ? 'ti-alert-circle' : 'ti-alert-triangle';
    const title = type === 'danger' ? 'Validation Errors' : 'Warnings';
    
    container.className = `alert alert-${type} alert-dismissible fade show mt-2`;
    container.innerHTML = `
        <i class="ti ${icon} me-1"></i>
        <strong>${title}:</strong>
        <ul class="mb-0 mt-1">
            ${errors.map(error => `<li>${error}</li>`).join('')}
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    container.style.display = 'block';
}

/**
 * Show file warnings
 */
function showFileWarnings(warnings, file) {
    const container = createOrGetErrorContainer();
    container.className = 'alert alert-warning alert-dismissible fade show mt-2';
    container.innerHTML = `
        <i class="ti ti-alert-triangle me-1"></i>
        <strong>File ready with warnings:</strong> ${file.name} (${formatFileSize(file.size)})
        <ul class="mb-0 mt-1">
            ${warnings.map(warning => `<li>${warning}</li>`).join('')}
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    container.style.display = 'block';
}

/**
 * Show file validation success
 */
function showFileSuccess(file) {
    const container = createOrGetErrorContainer();
    container.className = 'alert alert-success alert-dismissible fade show mt-2';
    
    // Estimate number of rows (rough calculation)
    const estimatedRows = Math.max(1, Math.floor(file.size / 100)); // Very rough estimate
    const rowText = estimatedRows > 1 ? `(~${estimatedRows} estimated rows)` : '';
    
    container.innerHTML = `
        <i class="ti ti-check-circle me-1"></i>
        <strong>File ready for upload:</strong> ${file.name} (${formatFileSize(file.size)}) ${rowText}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    container.style.display = 'block';
}

/**
 * Enhanced file size formatting
 */
function formatFileSize(bytes) {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
}

/**
 * Disable upload button with visual feedback
 */
function disableUploadButton() {
    const submitButton = document.querySelector('#csvUpload button[type="submit"]');
    if (submitButton) {
        submitButton.disabled = true;
        submitButton.innerHTML = '<i class="ti ti-ban me-1"></i>Cannot Upload';
        submitButton.classList.remove('btn-success');
        submitButton.classList.add('btn-secondary');
    }
}

/**
 * Enable upload button with visual feedback
 */
function enableUploadButton() {
    const submitButton = document.querySelector('#csvUpload button[type="submit"]');
    if (submitButton) {
        submitButton.disabled = false;
        submitButton.innerHTML = '<i class="ti ti-upload me-1"></i>Upload and Import';
        submitButton.classList.remove('btn-secondary');
        submitButton.classList.add('btn-success');
    }
}

/**
 * Form submission with progress tracking
 */
document.addEventListener('DOMContentLoaded', function() {
    const csvForm = document.querySelector('#csvUpload form');
    
    if (csvForm) {
        csvForm.addEventListener('submit', function(e) {
            const submitButton = this.querySelector('button[type="submit"]');
            const fileInput = this.querySelector('input[name="csv_file"]');
            
            // Final validation before submit
            if (!fileInput.files[0]) {
                e.preventDefault();
                showToast('Please select a CSV file first', 'error');
                return;
            }

            if (submitButton) {
                // Show enhanced loading state
                submitButton.disabled = true;
                submitButton.innerHTML = '<i class="spinner-border spinner-border-sm me-1"></i>Processing...';
                
                // Create enhanced progress message
                const progressDiv = document.createElement('div');
                progressDiv.className = 'alert alert-info mt-2';
                progressDiv.innerHTML = `
                    <div class="d-flex align-items-center">
                        <i class="spinner-border spinner-border-sm me-2"></i>
                        <div>
                            <strong>Processing CSV file...</strong><br>
                            <small>This may take a few moments for large files. Please do not close this page.</small>
                        </div>
                    </div>
                `;
                submitButton.parentNode.appendChild(progressDiv);

                // Add timeout warning for very large files
                setTimeout(() => {
                    if (submitButton.disabled) {
                        const timeoutWarning = document.createElement('div');
                        timeoutWarning.className = 'alert alert-warning mt-2';
                        timeoutWarning.innerHTML = `
                            <i class="ti ti-clock me-1"></i>
                            <strong>Still processing...</strong> Large files can take several minutes to process.
                        `;
                        progressDiv.parentNode.insertBefore(timeoutWarning, progressDiv.nextSibling);
                    }
                }, 30000); // Show after 30 seconds
            }
        });
    }
});

/**
 * Form reset when collapsible is hidden
 */
document.addEventListener('DOMContentLoaded', function() {
    const csvCollapse = document.getElementById('csvUpload');
    
    if (csvCollapse) {
        csvCollapse.addEventListener('hidden.bs.collapse', function() {
            // Reset form
            const form = this.querySelector('form');
            if (form) {
                form.reset();
            }
            
            // Clear all validation messages
            const errorContainer = document.getElementById('csv-validation-errors');
            if (errorContainer) {
                errorContainer.style.display = 'none';
                errorContainer.innerHTML = '';
            }
            
            // Reset button state
            enableUploadButton();
            
            // Remove any progress messages
            const alertDivs = this.querySelectorAll('.alert-info, .alert-warning');
            alertDivs.forEach(div => {
                if (div.id !== 'csv-validation-errors') {
                    div.remove();
                }
            });
            
            // Clear any timers (if we had any)
            showToast('Upload form reset', 'info');
        });

        // Also reset on show to ensure clean state
        csvCollapse.addEventListener('show.bs.collapse', function() {
            const errorContainer = document.getElementById('csv-validation-errors');
            if (errorContainer) {
                errorContainer.style.display = 'none';
            }
        });
    }
});

/**
 * Simple toast notification (fallback)
 */
function showToast(message, type = 'info') {
    // Try to use Toastify if available, otherwise use a simple alert
    if (typeof Toastify !== 'undefined') {
        const colors = {
            'success': '#28a745',
            'error': '#dc3545', 
            'warning': '#ffc107',
            'info': '#17a2b8'
        };

        Toastify({
            text: message,
            duration: 3000,
            close: true,
            gravity: 'top',
            position: 'right',
            backgroundColor: colors[type] || colors.info,
            stopOnFocus: true
        }).showToast();
    } else {
        // Simple fallback - create a temporary alert
        const alertDiv = document.createElement('div');
        alertDiv.className = `alert alert-${type} alert-dismissible fade show position-fixed`;
        alertDiv.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
        alertDiv.innerHTML = `
            ${message}
            <button type="button" class="btn-close" onclick="this.parentElement.remove()"></button>
        `;
        document.body.appendChild(alertDiv);
        
        // Auto remove after 3 seconds
        setTimeout(() => {
            if (alertDiv.parentNode) {
                alertDiv.remove();
            }
        }, 3000);
    }
}

// Export functions for global access
window.csvUploadUtils = {
    validateCsvFile,
    formatFileSize,
    showToast
};