document.addEventListener("DOMContentLoaded", () => {
    const form = document.getElementById("create-thread-form");
    form.setAttribute("novalidate", true);
    
    form.addEventListener("submit", (e) => {
        const titleInput = document.getElementById("thread-title");
        const contentInput = document.getElementById("thread-content");
        const titleError = document.getElementById("title-error-message");
        const contentError = document.getElementById("content-error-message");
        
        let isValid = true;
        
        // Validate title
        if (titleInput.value.trim() === "") {
            e.preventDefault();
            titleError.textContent = "Thread title is required";
            isValid = false;
        } else if (titleInput.value.length > 100) {
            e.preventDefault();
            titleError.textContent = "Title cannot be more than 100 characters";
            isValid = false;
        } else {
            titleError.textContent = "";
        }
        
        // Validate content
        if (contentInput.value.trim() === "") {
            e.preventDefault();
            contentError.textContent = "Thread content is required";
            isValid = false;
        } else {
            contentError.textContent = "";
        }
        
        return isValid;
    });
}); 