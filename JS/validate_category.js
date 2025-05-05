/**
 * Category validation script
 * Validates the category form before submission
 */
document.addEventListener('DOMContentLoaded', function () {
    // Get category form
    const categoryForm = document.getElementById('categoryForm');

    if (categoryForm) {
        // Add submit event listener
        categoryForm.addEventListener('submit', function (event) {
            // Prevent default form submission
            event.preventDefault();

            // Get category name value
            const categoryName = document.getElementById('cat_name').value.trim();

            // Validate category name
            if (categoryName === '') {
                showValidationError('Category name is required');
                return false;
            }

            if (categoryName.length > 100) {
                showValidationError('Category name is too long (maximum 100 characters)');
                return false;
            }

            // If we're here, validation passed, submit form
            submitCategoryForm();
        });

        // Function to show validation error
        function showValidationError(message) {
            // Remove any existing error messages
            removeValidationError();

            // Create error message element
            const errorElement = document.createElement('div');
            errorElement.className = 'alert alert-danger mt-3';
            errorElement.id = 'category-validation-error';
            errorElement.textContent = message;

            // Insert error message after the form
            categoryForm.insertAdjacentElement('afterend', errorElement);

            // Scroll to error message
            errorElement.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        }

        // Function to remove validation error
        function removeValidationError() {
            const existingError = document.getElementById('category-validation-error');
            if (existingError) {
                existingError.remove();
            }
        }

        // Function to submit form via AJAX
        function submitCategoryForm() {
            // Get form data
            const formData = new FormData(categoryForm);

            // Show loading state
            const submitButton = categoryForm.querySelector('button[type="submit"]');
            const originalButtonText = submitButton.textContent;
            submitButton.disabled = true;
            submitButton.textContent = 'Processing...';

            // Determine if this is an add or edit operation
            const isEdit = categoryForm.querySelector('input[name="cat_id"]') !== null;
            const actionUrl = isEdit ? '../Actions/update_category.php' : '../Actions/add_category.php';

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
                        categoryForm.insertAdjacentElement('afterend', successElement);

                        // Reset form for add operation
                        if (!isEdit) {
                            categoryForm.reset();
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