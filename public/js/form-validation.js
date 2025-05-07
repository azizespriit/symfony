(function() {
    'use strict';

    // Enable real-time validation for all forms with the needs-validation class
    document.addEventListener('DOMContentLoaded', function() {
        // Fetch all forms with the needs-validation class
        const forms = document.querySelectorAll('.needs-validation');
        
        // Add input event listeners to all form fields for real-time validation
        forms.forEach(form => {
            const inputs = form.querySelectorAll('input, select, textarea');
            
            inputs.forEach(input => {
                // Validate on input
                input.addEventListener('input', function() {
                    validateField(this);
                });
                
                // Validate on blur
                input.addEventListener('blur', function() {
                    validateField(this);
                });
            });
            
            // Prevent form submission if validation fails
            form.addEventListener('submit', function(event) {
                if (!form.checkValidity()) {
                    event.preventDefault();
                    event.stopPropagation();
                }
                
                form.classList.add('was-validated');
            }, false);
        });
    });
    
    // Function to validate a specific field
    function validateField(field) {
        // Clear previous validation state
        field.classList.remove('is-valid', 'is-invalid');
        
        // Set validation state based on validity
        if (field.checkValidity()) {
            field.classList.add('is-valid');
        } else {
            field.classList.add('is-invalid');
        }
        
        // Find the closest form group to update feedback messages
        const formGroup = field.closest('.form-group');
        if (formGroup) {
            const feedback = formGroup.querySelector('.invalid-feedback');
            if (feedback) {
                if (field.validationMessage) {
                    feedback.textContent = field.validationMessage;
                }
            }
        }
        
        // Handle specific validation for common field types
        handleSpecificValidation(field);
    }
    
    // Handle specific validation for common field types
    function handleSpecificValidation(field) {
        // Email validation
        if (field.type === 'email') {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (field.value && !emailRegex.test(field.value)) {
                field.setCustomValidity('Please enter a valid email address');
            } else {
                field.setCustomValidity('');
            }
        }
        
        // Password validation - if class 'password-field' is added
        if (field.classList.contains('password-field')) {
            if (field.value && field.value.length < 8) {
                field.setCustomValidity('Password must be at least 8 characters long');
            } else {
                field.setCustomValidity('');
            }
        }
        
        // Number validation
        if (field.type === 'number') {
            const min = parseFloat(field.getAttribute('min'));
            const max = parseFloat(field.getAttribute('max'));
            
            if (!isNaN(min) && parseFloat(field.value) < min) {
                field.setCustomValidity(`Value must be greater than or equal to ${min}`);
            } else if (!isNaN(max) && parseFloat(field.value) > max) {
                field.setCustomValidity(`Value must be less than or equal to ${max}`);
            } else {
                field.setCustomValidity('');
            }
        }
    }
})(); 