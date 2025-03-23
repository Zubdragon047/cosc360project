document.addEventListener("DOMContentLoaded", () => {
    const searchForm = document.getElementById("thread-search-form");
    
    if (searchForm) {
        // Optional: Add search input validation if needed
        searchForm.addEventListener("submit", (e) => {
            const searchInput = document.getElementById("thread-search");
            
            if (searchInput.value.trim() === "") {
                e.preventDefault();
                // Optional: display an error message or just prevent empty searches
            }
        });
    }
}); 