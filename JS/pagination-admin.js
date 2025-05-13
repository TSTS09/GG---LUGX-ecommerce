
// Function to show image preview
document.getElementById('product_image').addEventListener('change', function (e) {
    const preview = document.getElementById('image-preview');
    const file = e.target.files[0];

    if (file) {
        const reader = new FileReader();
        reader.onload = function (e) {
            preview.src = e.target.result;
            preview.style.display = 'block';
        }
        reader.readAsDataURL(file);
    } else {
        preview.style.display = 'none';
    }
});

// Function to change display count
function changeDisplayCount(limit) {
    // Get current URL
    let url = new URL(window.location.href);

    // Set the limit parameter
    url.searchParams.set('limit', limit);

    // Reset to first page when changing limit
    url.searchParams.set('page', 1);

    // Redirect to new URL
    window.location.href = url.toString();
}

// Function to search products
function searchProducts() {
    // Get search term
    let searchTerm = document.getElementById('search-products').value;

    // Get current URL
    let url = new URL(window.location.href);

    // Set search parameter
    url.searchParams.set('search', searchTerm);

    // Reset to first page for new search
    url.searchParams.set('page', 1);

    // Redirect to new URL
    window.location.href = url.toString();
}

// Search on Enter key press
document.getElementById('search-products').addEventListener('keypress', function (e) {
    if (e.key === 'Enter') {
        searchProducts();
    }
});
