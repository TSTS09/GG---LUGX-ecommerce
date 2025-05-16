// Function to toggle the product list dropdown
function toggleProductList(bundleId) {
    const productList = document.getElementById('productList' + bundleId);

    // Close other open dropdowns first
    document.querySelectorAll('.custom-dropdown-menu').forEach(menu => {
        if (menu.id !== 'productList' + bundleId && menu.style.display === 'block') {
            menu.style.display = 'none';
        }
    });

    // Toggle the selected dropdown
    if (productList.style.display === 'block') {
        productList.style.display = 'none';
    } else {
        productList.style.display = 'block';

        // Convert the existing layout into a proper table
        const container = productList.querySelector('.dropdown-items-container');
        if (container) {
            // Check if we've already converted this dropdown to a table
            if (!container.querySelector('.product-table')) {
                const items = container.querySelectorAll('.custom-dropdown-item');
                const table = document.createElement('table');
                table.className = 'product-table';

                // Create table content from existing items
                items.forEach(item => {
                    const row = document.createElement('tr');

                    // Product image cell
                    const imgCell = document.createElement('td');
                    imgCell.style.width = '50px';
                    const img = item.querySelector('img').cloneNode(true);
                    img.className = 'product-thumbnail';
                    if (img.parentElement.classList.contains('unavailable')) {
                        img.className += ' unavailable';
                    }
                    imgCell.appendChild(img);

                    // Product title cell
                    const titleCell = document.createElement('td');
                    const title = item.querySelector('.product-title').cloneNode(true);
                    titleCell.appendChild(title);

                    // Check for unavailable badge
                    const badge = item.querySelector('.badge');
                    if (badge) {
                        titleCell.appendChild(badge.cloneNode(true));
                    }

                    // Product price cell
                    const priceCell = document.createElement('td');
                    priceCell.style.textAlign = 'right';
                    const price = item.querySelector('.product-price').cloneNode(true);
                    priceCell.appendChild(price);

                    // Add cells to row
                    row.appendChild(imgCell);
                    row.appendChild(titleCell);
                    row.appendChild(priceCell);

                    // Add row to table
                    table.appendChild(row);
                });

                // Replace the content
                container.innerHTML = '';
                container.appendChild(table);
            }
        }
    }

    // Close dropdown when clicking outside
    document.addEventListener('click', function (event) {
        if (!event.target.closest('.custom-dropdown')) {
            const dropdowns = document.querySelectorAll('.custom-dropdown-menu');
            dropdowns.forEach(dropdown => {
                dropdown.style.display = 'none';
            });
        }
    }, { once: true });
}

// Add click event listeners to all dropdown toggles when the page loads
document.addEventListener('DOMContentLoaded', function () {
    const dropdownToggles = document.querySelectorAll('.custom-dropdown-toggle');
    dropdownToggles.forEach(toggle => {
        toggle.addEventListener('click', function (event) {
            event.stopPropagation();
        });
    });
});