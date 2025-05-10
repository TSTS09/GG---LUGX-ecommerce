/**
 * Product validation script
 * Validates the product form before submission
 */
document.addEventListener('DOMContentLoaded', function () {
    // Get product form
    const productForm = document.getElementById('productForm');

    if (productForm) {
        // Add submit event listener
        productForm.addEventListener('submit', function (event) {
            // Prevent default form submission
            event.preventDefault();
            console.log("Form submission intercepted");

            // Get form fields
            const productTitle = document.getElementById('product_title').value.trim();
            const productCategory = document.getElementById('product_cat').value;
            const productBrand = document.getElementById('product_brand').value;
            const productPrice = document.getElementById('product_price').value.trim();
            const productDesc = document.getElementById('product_desc').value.trim();
            const productKeywords = document.getElementById('product_keywords').value.trim();

            // Get file input element
            const productImage = document.getElementById('product_image');

            // Validation flags
            let isValid = true;
            let errorMessage = '';

            // Validation logic (unchanged)...

            // If validation fails, show error and return
            if (!isValid) {
                showValidationError(errorMessage);
                return false;
            }

            // If we're here, validation passed, submit form
            console.log("Validation passed, submitting form");
            submitProductForm();
        });

        // Function to show validation error
        function showValidationError(message) {
            // Remove any existing error messages
            removeValidationError();

            // Create error message element
            const errorElement = document.createElement('div');
            errorElement.className = 'alert alert-danger mt-3';
            errorElement.id = 'product-validation-error';
            errorElement.textContent = message;

            // Insert error message after the form
            productForm.insertAdjacentElement('afterend', errorElement);

            // Scroll to error message
            errorElement.scrollIntoView({ behavior: 'smooth', block: 'center' });
            
            console.error("Validation error:", message);
        }

        // Function to remove validation error
        function removeValidationError() {
            const existingError = document.getElementById('product-validation-error');
            if (existingError) {
                existingError.remove();
            }
        }

        // Function to submit form via AJAX
        function submitProductForm() {
            // Get form data
            const formData = new FormData(productForm);

            // Debug: Log the FormData contents
            console.log("Form data being submitted:");
            for (let pair of formData.entries()) {
                console.log(pair[0] + ': ' + (pair[0] === 'product_image' ? 'File: ' + (pair[1].name || 'No file') : pair[1]));
            }

            // Show loading state
            const submitButton = productForm.querySelector('button[type="submit"]');
            const originalButtonText = submitButton.textContent;
            submitButton.disabled = true;
            submitButton.textContent = 'Processing...';

            // Determine if this is an add or edit operation
            const isEdit = productForm.querySelector('input[name="product_id"]') !== null;
            const actionUrl = isEdit ? '../Actions/update_product.php' : '../Actions/add_product.php';
            console.log("Action URL:", actionUrl);

            // Send AJAX request with improved error handling
            console.log("Sending AJAX request to:", actionUrl);
            
            fetch(actionUrl, {
                method: 'POST',
                body: formData
            })
            .then(response => {
                console.log("Response status:", response.status);
                console.log("Response headers:", Object.fromEntries(response.headers.entries()));
                
                // First get the raw text
                return response.text();
            })
            .then(text => {
                console.log("Raw response:", text);
                
                // Try to parse as JSON
                try {
                    const data = JSON.parse(text);
                    console.log("Parsed data:", data);
                    return data;
                } catch (e) {
                    console.error("Failed to parse response as JSON:", e);
                    throw new Error("Invalid JSON response: " + text);
                }
            })
            .then(data => {
                if (data.status === 'success') {
                    // Show success message
                    const successElement = document.createElement('div');
                    successElement.className = 'alert alert-success mt-3';
                    successElement.textContent = data.message;
                    productForm.insertAdjacentElement('afterend', successElement);

                    // Redirect to product list after delay
                    setTimeout(() => {
                        window.location.href = 'product.php';
                    }, 1500);
                } else {
                    // Show error message
                    let errorMsg = data.message || 'An error occurred';
                    
                    // Add details if available
                    if (data.details) {
                        console.error("Error details:", data.details);
                        errorMsg += " (Check console for details)";
                    }
                    
                    showValidationError(errorMsg);

                    // Reset button state
                    submitButton.disabled = false;
                    submitButton.textContent = originalButtonText;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showValidationError('An unexpected error occurred: ' + error.message);

                // Reset button state
                submitButton.disabled = false;
                submitButton.textContent = originalButtonText;
            });
        }

        // Image preview functionality (unchanged)...
    }
});