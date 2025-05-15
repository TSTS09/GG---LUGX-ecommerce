 // Update selected products display
        function updateSelectedProducts() {
            const selectedProductsList = document.getElementById('selectedProductsList');
            const noProductsSelected = document.getElementById('noProductsSelected');

            if (selectedProducts.length === 0) {
                selectedProductsList.innerHTML = '';
                noProductsSelected.style.display = 'block';
                return;
            }

            noProductsSelected.style.display = 'none';
            selectedProductsList.innerHTML = '';

            selectedProducts.forEach(productId => {
                const checkbox = document.getElementById('product_' + productId);
                if (!checkbox) return;

                const label = checkbox.nextElementSibling.nextElementSibling;
                const img = checkbox.nextElementSibling;

                const productElement = document.createElement('div');
                productElement.className = 'selected-product';

                const productInfo = document.createElement('div');
                productInfo.className = 'd-flex align-items-center';

                const productImg = document.createElement('img');
                productImg.src = img.src;
                productImg.alt = label.textContent.trim();

                const productName = document.createElement('span');
                productName.textContent = label.textContent.trim();
                productName.className = 'ml-2';

                // Add quantity input
                const quantityInput = document.createElement('input');
                quantityInput.type = 'number';
                quantityInput.min = '1';
                quantityInput.max = '10';
                quantityInput.value = '1';
                quantityInput.className = 'form-control ml-2 quantity-input';
                quantityInput.style.width = '70px';
                quantityInput.id = 'quantity_' + productId;

                const removeButton = document.createElement('button');
                removeButton.type = 'button';
                removeButton.className = 'btn btn-sm btn-danger';
                removeButton.innerHTML = '<i class="fa fa-times"></i>';
                removeButton.addEventListener('click', function() {
                    checkbox.checked = false;
                    selectedProducts = selectedProducts.filter(id => id != productId);
                    updateSelectedProducts();
                    updatePricing();
                });

                productInfo.appendChild(productImg);
                productInfo.appendChild(productName);
                productInfo.appendChild(quantityInput);

                productElement.appendChild(productInfo);
                productElement.appendChild(removeButton);

                selectedProductsList.appendChild(productElement);

                // Add hidden input for the selected product
                const hiddenInput = document.createElement('input');
                hiddenInput.type = 'hidden';
                hiddenInput.name = 'product_ids[]';
                hiddenInput.value = productId;
                selectedProductsList.appendChild(hiddenInput);

                // Add hidden input for the discount
                const discountInput = document.createElement('input');
                discountInput.type = 'hidden';
                discountInput.name = 'discounts[]';
                discountInput.value = '0';
                selectedProductsList.appendChild(discountInput);

                // Add hidden input for the quantity
                const quantityHiddenInput = document.createElement('input');
                quantityHiddenInput.type = 'hidden';
                quantityHiddenInput.name = 'quantities[]';
                quantityHiddenInput.value = '1';
                quantityHiddenInput.id = 'hidden_quantity_' + productId;
                selectedProductsList.appendChild(quantityHiddenInput);

                // Update hidden quantity value when visible input changes
                quantityInput.addEventListener('change', function() {
                    quantityHiddenInput.value = this.value;
                    updatePricing();
                });
            });
        }

        // Modify the updatePricing function to account for quantities
        function updatePricing() {
            const totalOriginalPrice = document.getElementById('totalOriginalPrice');
            const bundlePrice = document.getElementById('bundlePrice');
            const savingsAmount = document.getElementById('savingsAmount');
            const savingsPercent = document.getElementById('savingsPercent');

            // Calculate total original price
            let total = 0;
            selectedProducts.forEach(productId => {
                if (productPrices[productId]) {
                    const quantity = parseInt(document.getElementById('quantity_' + productId)?.value || 1);
                    total += productPrices[productId] * quantity;
                }
            });

            // Get bundle price
            const bundlePriceValue = parseFloat(document.getElementById('bundle_price').value) || 0;

            // Calculate savings
            const savings = total - bundlePriceValue;
            const savingsPercentValue = total > 0 ? (savings / total) * 100 : 0;

            // Update display
            totalOriginalPrice.textContent = '$' + total.toFixed(2);
            bundlePrice.textContent = '$' + bundlePriceValue.toFixed(2);
            savingsAmount.textContent = '$' + savings.toFixed(2);
            savingsPercent.textContent = Math.round(savingsPercentValue);
        }
        // Add real-time validation for bundle price
        document.getElementById('bundleForm').addEventListener('submit', function(event) {
            const bundlePriceValue = parseFloat(document.getElementById('bundle_price').value) || 0;
            let totalOriginalPrice = 0;

            // Calculate total original price of selected products
            selectedProducts.forEach(productId => {
                if (productPrices[productId]) {
                    totalOriginalPrice += productPrices[productId];
                }
            });

            // If no products selected or bundle price is >= total, prevent form submission
            if (selectedProducts.length === 0) {
                alert('Please select at least one product for the bundle.');
                event.preventDefault();
                return false;
            }

            if (bundlePriceValue >= totalOriginalPrice) {
                alert('Bundle price must be lower than the total price of individual products ($' + totalOriginalPrice.toFixed(2) + ')');
                event.preventDefault();
                return false;
            }

            return true;
        });