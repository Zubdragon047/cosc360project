document.addEventListener("DOMContentLoaded", function() {
    // Initialize filter functionality
    const searchForm = document.getElementById("search-form");
    const categoryFilter = document.getElementById("category-filter");
    const statusFilter = document.getElementById("status-filter");
    
    if (searchForm && categoryFilter && statusFilter) {
        // Auto-submit form when filters change
        categoryFilter.addEventListener("change", function() {
            searchForm.submit();
        });
        
        statusFilter.addEventListener("change", function() {
            searchForm.submit();
        });
    }
}); 