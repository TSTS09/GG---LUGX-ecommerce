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

            // Validate title
            if (productTitle === '') {
                isValid = false;
                errorMessage = 'Product title is required';
            } else if (productTitle.length > 100) {
                isValid = false;
                errorMessage = 'Product title is too long (maximum 100 characters)';
            }

            // Validate category
            if (productCategory === '' || productCategory === '0') {
                isValid = false;
                errorMessage = 'Please select a product category';
            }

            // Validate brand
            if (productBrand === '' || productBrand === '0') {
                isValid = false;
                errorMessage = 'Please select a product brand';
            }

            // Validate price
            if (productPrice === '') {
                isValid = false;
                errorMessage = 'Product price is required';
            } else if (isNaN(parseFloat(productPrice)) || parseFloat(productPrice) <= 0) {
                isValid = false;
                errorMessage = 'Please enter a valid product price (greater than 0)';
            }

            // Validate description
            if (productDesc === '') {
                isValid = false;
                errorMessage = 'Product description is required';
            }

            // Validate keywords
            if (productKeywords === '') {
                isValid = false;
                errorMessage = 'Product keywords are required';
            }

            // Validate image (only for new products or if editing and a new image is selected)
            const isEditing = productForm.querySelector('input[name="product_id"]') !== null;
            if (!isEditing && productImage.files.length === 0) {
                isValid = false;
                errorMessage = 'Please select a product image';
            } else if (productImage.files.length > 0) {
                const file = productImage.files[0];

                // Check file size (max 5MB)
                if (file.size > 5 * 1024 * 1024) {
                    isValid = false;
                    errorMessage = 'Image file is too large (maximum 5MB)';
                }

                // Check file type
                const validTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
                if (!validTypes.includes(file.type)) {
                    isValid = false;
                    errorMessage = 'Please select a valid image file (JPG, JPEG, PNG, or GIF)';
                }
            }

            // If validation fails, show error and return
            if (!isValid) {
                showValidationError(errorMessage);
                return false;
            }

            // If we're here, validation passed, submit form
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

            // Show loading state
            const submitButton = productForm.querySelector('button[type="submit"]');
            const originalButtonText = submitButton.textContent;
            submitButton.disabled = true;
            submitButton.textContent = 'Processing...';

            // Determine if this is an add or edit operation
            const isEdit = productForm.querySelector('input[name="product_id"]') !== null;
            const actionUrl = isEdit ? '../Actions/update_product.php' : '../Actions/add_product.php';

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
                        productForm.insertAdjacentElement('afterend', successElement);

                        // Redirect to product list after delay
                        setTimeout(() => {
                            window.location.href = 'product.php';
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

        // Image preview functionality
        const imageInput = document.getElementById('product_image');
        const imagePreview = document.getElementById('image-preview');

        if (imageInput && imagePreview) {
            imageInput.addEventListener('change', function () {
                if (this.files && this.files[0]) {
                    const reader = new FileReader();

                    reader.onload = function (e) {
                        imagePreview.src = e.target.result;
                        imagePreview.style.display = 'block';
                    };

                    reader.readAsDataURL(this.files[0]);
                }
            });
        }
    }
});