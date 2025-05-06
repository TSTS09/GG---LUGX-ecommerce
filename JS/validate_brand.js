/**
 * Brand validation script
 * Validates the brand form before submission
 */
document.addEventListener('DOMContentLoaded', function () {
    // Get brand form
    const brandForm = document.getElementById('brandForm');

    if (brandForm) {
        // Add submit event listener
        brandForm.addEventListener('submit', function (event) {
            // Prevent default form submission
            event.preventDefault();

            // Get brand name value
            const brandName = document.getElementById('brand_name').value.trim();

            // Validate brand name
            if (brandName === '') {
                showValidationError('Brand name is required');
                return false;
            }

            if (brandName.length > 100) {
                showValidationError('Brand name is too long (maximum 100 characters)');
                return false;
            }

            // If we're here, validation passed, submit form
            submitBrandForm();
        });

        // Function to show validation error
        function showValidationError(message) {
            // Remove any existing error messages
            removeValidationError();

            // Create error message element
            const errorElement = document.createElement('div');
            errorElement.className = 'alert alert-danger mt-3';
            errorElement.id = 'brand-validation-error';
            errorElement.textContent = message;

            // Insert error message after the form
            brandForm.insertAdjacentElement('afterend', errorElement);

            // Scroll to error message
            errorElement.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        }

        // Function to remove validation error
        function removeValidationError() {
            const existingError = document.getElementById('brand-validation-error');
            if (existingError) {
                existingError.remove();
            }
        }

        // Function to submit form via AJAX
        function submitBrandForm() {
            // Get form data
            const formData = new FormData(brandForm);

            // Show loading state
            const submitButton = brandForm.querySelector('button[type="submit"]');
            const originalButtonText = submitButton.textContent;
            submitButton.disabled = true;
            submitButton.textContent = 'Processing...';

            // Determine if this is an add or edit operation
            const isEdit = brandForm.querySelector('input[name="brand_id"]') !== null;
            const actionUrl = isEdit ? '../Actions/update_brand.php' : '../Actions/add_brand.php';

            // Send AJAX request
            fetch(actionUrl, {
                method: 'POST',
                body: formData
            })
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        // Show success message
                        const successElement = document.createElement('div');
                        successElement.className = 'alert alert-success mt-3';
                        successElement.textContent = data.message;
                        brandForm.insertAdjacentElement('afterend', successElement);

                        // Reset form for add operation
                        if (!isEdit) {
                            brandForm.reset();
                        }

                        // Reload page after delay
                        setTimeout(() => {
                            window.location.reload();
                        }, 1500);
                    } else {
                        // Show error message
                        showValidationError(data.message || 'An error occurred');

                        // Reset button state
                        submitButton.disabled = false;
                        submitButton.textContent = originalButtonText;
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showValidationError('An unexpected error occurred. Please try again.');

                    // Reset button state
                    submitButton.disabled = false;
                    submitButton.textContent = originalButtonText;
                });
        }
    }
});