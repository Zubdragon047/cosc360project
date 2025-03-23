document.addEventListener("DOMContentLoaded", function() {
    const addBookForm = document.getElementById("add-book-form");
    
    if (addBookForm) {
        addBookForm.addEventListener("submit", function(event) {
            let isValid = true;
            
            // Title validation
            const bookTitle = document.getElementById("book-title");
            const titleError = document.getElementById("title-error-message");
            
            if (!bookTitle.value.trim()) {
                titleError.textContent = "Book title is required";
                bookTitle.classList.add("error-input");
                isValid = false;
            } else if (bookTitle.value.trim().length < 2) {
                titleError.textContent = "Book title must be at least 2 characters";
                bookTitle.classList.add("error-input");
                isValid = false;
            } else {
                titleError.textContent = "";
                bookTitle.classList.remove("error-input");
            }
            
            // Description validation
            const bookDescription = document.getElementById("book-description");
            const descriptionError = document.getElementById("description-error-message");
            
            if (!bookDescription.value.trim()) {
                descriptionError.textContent = "Book description is required";
                bookDescription.classList.add("error-input");
                isValid = false;
            } else if (bookDescription.value.trim().length < 10) {
                descriptionError.textContent = "Book description must be at least 10 characters";
                bookDescription.classList.add("error-input");
                isValid = false;
            } else {
                descriptionError.textContent = "";
                bookDescription.classList.remove("error-input");
            }
            
            // Category validation
            const bookCategory = document.getElementById("book-category");
            const categoryError = document.getElementById("category-error-message");
            
            if (!bookCategory.value || bookCategory.value.trim() === " ") {
                categoryError.textContent = "Please select a category";
                bookCategory.classList.add("error-input");
                isValid = false;
            } else {
                categoryError.textContent = "";
                bookCategory.classList.remove("error-input");
            }
            
            // Image validation (optional)
            const bookImage = document.getElementById("book-picture");
            if (bookImage.files.length > 0) {
                const file = bookImage.files[0];
                const fileSize = file.size / 1024 / 1024; // in MB
                const validTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/bmp'];
                
                if (fileSize > 10) {
                    alert("Image file is too large. Maximum size is 10MB.");
                    isValid = false;
                } else if (!validTypes.includes(file.type)) {
                    alert("Invalid file type. Please upload a JPG, PNG, GIF, or BMP image.");
                    isValid = false;
                }
            }
            
            if (!isValid) {
                event.preventDefault();
            }
        });
        
        // Real-time validation
        const bookTitle = document.getElementById("book-title");
        const bookDescription = document.getElementById("book-description");
        const bookCategory = document.getElementById("book-category");
        
        bookTitle.addEventListener("input", function() {
            const titleError = document.getElementById("title-error-message");
            if (!this.value.trim()) {
                titleError.textContent = "Book title is required";
                this.classList.add("error-input");
            } else if (this.value.trim().length < 2) {
                titleError.textContent = "Book title must be at least 2 characters";
                this.classList.add("error-input");
            } else {
                titleError.textContent = "";
                this.classList.remove("error-input");
            }
        });
        
        bookDescription.addEventListener("input", function() {
            const descriptionError = document.getElementById("description-error-message");
            if (!this.value.trim()) {
                descriptionError.textContent = "Book description is required";
                this.classList.add("error-input");
            } else if (this.value.trim().length < 10) {
                descriptionError.textContent = "Book description must be at least 10 characters";
                this.classList.add("error-input");
            } else {
                descriptionError.textContent = "";
                this.classList.remove("error-input");
            }
        });
        
        bookCategory.addEventListener("change", function() {
            const categoryError = document.getElementById("category-error-message");
            if (!this.value || this.value.trim() === " ") {
                categoryError.textContent = "Please select a category";
                this.classList.add("error-input");
            } else {
                categoryError.textContent = "";
                this.classList.remove("error-input");
            }
        });
    }
}); 