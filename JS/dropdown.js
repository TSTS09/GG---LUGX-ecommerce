// JavaScript function to toggle the dropdown menu
function toggleProductList(bundleId) {
    // Close all other open dropdowns first
    document.querySelectorAll('.custom-dropdown-menu').forEach(function(menu) {
        if (menu.id !== 'productList' + bundleId) {
            menu.style.display = 'none';
        }
    });
    
    // Toggle the current dropdown
    var menu = document.getElementById('productList' + bundleId);
    if (menu.style.display === 'none') {
        menu.style.display = 'block';
    } else {
        menu.style.display = 'none';
    }
    
    // Close dropdown when clicking outside
    document.addEventListener('click', function closeDropdown(e) {
        // If click is outside dropdown and toggle
        if (!e.target.closest('.custom-dropdown-menu') && 
            !e.target.closest('.custom-dropdown-toggle')) {
            menu.style.display = 'none';
            document.removeEventListener('click', closeDropdown);
        }
    });
    
    // Prevent the click from immediately closing the dropdown
    event.stopPropagation();
}

// Close dropdowns when clicking elsewhere on the page
document.addEventListener('DOMContentLoaded', function() {
    document.addEventListener('click', function(e) {
        // If click is outside any dropdown
        if (!e.target.closest('.custom-dropdown-menu') && 
            !e.target.closest('.custom-dropdown-toggle')) {
            document.querySelectorAll('.custom-dropdown-menu').forEach(function(menu) {
                menu.style.display = 'none';
            });
        }
    });
    
    // Ensure dropdowns are properly positioned
    window.addEventListener('resize', function() {
        document.querySelectorAll('.custom-dropdown-menu').forEach(function(menu) {
            if (menu.style.display !== 'none') {
                // Recalculate position
                var toggle = menu.previousElementSibling;
                var rect = toggle.getBoundingClientRect();
                menu.style.top = rect.bottom + 'px';
                
                // Ensure dropdown doesn't go offscreen on the right
                var menuRect = menu.getBoundingClientRect();
                if (menuRect.right > window.innerWidth) {
                    menu.style.right = '0';
                    menu.style.left = 'auto';
                }
            }
        });
    });
});