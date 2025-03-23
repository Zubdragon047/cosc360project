document.addEventListener("DOMContentLoaded", () => {
    const commentsContainer = document.getElementById("comments-container");
    const threadId = document.getElementById("thread_id").value;
    let lastCommentId = 0;
    
    // Load initial comments
    loadComments();
    
    // Set up polling for new comments every 5 seconds
    const commentInterval = setInterval(loadComments, 5000);
    
    // Form submission for adding new comments
    const commentForm = document.getElementById("add-comment-form");
    if (commentForm) {
        commentForm.addEventListener("submit", (e) => {
            e.preventDefault();
            
            const contentInput = document.getElementById("comment-content");
            const errorMessage = document.getElementById("comment-error-message");
            
            // Validate content
            if (contentInput.value.trim() === "") {
                errorMessage.textContent = "Comment cannot be empty";
                return;
            }
            
            // Clear error message
            errorMessage.textContent = "";
            
            // Post the comment using fetch API
            fetch("comment_handler.php", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json"
                },
                body: JSON.stringify({
                    thread_id: threadId,
                    content: contentInput.value
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Clear the input field
                    contentInput.value = "";
                    
                    // Load the latest comments
                    loadComments();
                } else {
                    errorMessage.textContent = data.error || "Failed to post comment";
                }
            })
            .catch(error => {
                errorMessage.textContent = "An error occurred: " + error.message;
            });
        });
    }
    
    // Function to load comments
    function loadComments() {
        fetch(`comment_handler.php?thread_id=${threadId}&last_id=${lastCommentId}`)
            .then(response => response.json())
            .then(data => {
                if (data.success && data.comments.length > 0) {
                    // Append new comments
                    data.comments.forEach(comment => {
                        appendComment(comment);
                        // Update the last comment ID
                        if (comment.id > lastCommentId) {
                            lastCommentId = comment.id;
                        }
                    });
                }
            })
            .catch(error => {
                console.error("Error loading comments:", error);
            });
    }
    
    // Function to append a comment to the comments container
    function appendComment(comment) {
        const commentElement = document.createElement("div");
        commentElement.className = "comment";
        commentElement.setAttribute("data-comment-id", comment.id);
        
        const commentDate = new Date(comment.created_at);
        const formattedDate = commentDate.toLocaleString();
        
        commentElement.innerHTML = `
            <div class="comment-header">
                <img src="${comment.profilepic}" alt="${comment.username}" class="comment-profilepic">
                <div class="comment-meta">
                    <span class="comment-author">${comment.username}</span>
                    <span class="comment-date">${formattedDate}</span>
                </div>
            </div>
            <div class="comment-content">
                ${comment.content.replace(/\n/g, '<br>')}
            </div>
        `;
        
        // Check if the comment already exists to avoid duplicates
        const existingComment = document.querySelector(`[data-comment-id="${comment.id}"]`);
        if (!existingComment) {
            commentsContainer.appendChild(commentElement);
        }
    }
}); 