document.addEventListener("DOMContentLoaded", () => {
    const commentsContainer = document.getElementById("comments-container");
    const threadId = document.getElementById("thread_id").value;
    let lastCommentId = 0;
    
    // Load initial comments
    loadComments(true);
    
    // Set up polling for new comments every 5 seconds
    const commentInterval = setInterval(() => loadComments(false), 5000);
    
    // Form submission for adding new comments
    const commentForm = document.getElementById("add-comment-form");
    if (commentForm) {
        commentForm.addEventListener("submit", (e) => {
            e.preventDefault();
            
            const contentInput = document.getElementById("comment-content");
            const errorMessage = document.getElementById("comment-error-message");
            const submitButton = commentForm.querySelector("button[type='submit']");
            
            // Validate content
            if (contentInput.value.trim() === "") {
                errorMessage.textContent = "Comment cannot be empty";
                return;
            }
            
            // Clear error message
            errorMessage.textContent = "";
            
            // Disable button and show loading state
            submitButton.disabled = true;
            submitButton.textContent = "Posting...";
            
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
                    loadComments(false);
                    
                    // Scroll to the bottom of the comments
                    setTimeout(() => {
                        if (commentsContainer.lastChild) {
                            commentsContainer.lastChild.scrollIntoView({ behavior: 'smooth' });
                        }
                    }, 300);
                } else {
                    errorMessage.textContent = data.error || "Failed to post comment";
                }
                
                // Re-enable button
                submitButton.disabled = false;
                submitButton.textContent = "Post Comment";
            })
            .catch(error => {
                errorMessage.textContent = "An error occurred: " + error.message;
                submitButton.disabled = false;
                submitButton.textContent = "Post Comment";
            });
        });
    }
    
    // Function to load comments
    function loadComments(isInitialLoad) {
        // Show loading indicator only on initial load
        if (isInitialLoad) {
            const loadingElement = commentsContainer.querySelector('.loading-comments');
            if (loadingElement) {
                loadingElement.style.display = 'block';
            }
        }
        
        fetch(`comment_handler.php?thread_id=${threadId}&last_id=${lastCommentId}`)
            .then(response => response.json())
            .then(data => {
                // Remove loading indicator on initial load
                if (isInitialLoad) {
                    const loadingElement = commentsContainer.querySelector('.loading-comments');
                    if (loadingElement) {
                        loadingElement.remove();
                    }
                    
                    // Clear out the container if initializing
                    commentsContainer.innerHTML = '';
                    
                    // If no comments, show a message
                    if (data.comments.length === 0) {
                        const noCommentsElement = document.createElement('p');
                        noCommentsElement.className = 'no-comments';
                        noCommentsElement.textContent = 'No comments yet. Be the first to comment!';
                        commentsContainer.appendChild(noCommentsElement);
                    }
                }
                
                if (data.success && data.comments.length > 0) {
                    // Remove no comments message if it exists
                    const noCommentsElement = commentsContainer.querySelector('.no-comments');
                    if (noCommentsElement) {
                        noCommentsElement.remove();
                    }
                    
                    // Render comments in flat structure
                    data.comments.forEach(comment => {
                        if (!document.querySelector(`[data-comment-id="${comment.id}"]`)) {
                            appendComment(comment, !isInitialLoad, commentsContainer);
                            
                            // Update the last comment ID
                            if (comment.id > lastCommentId) {
                                lastCommentId = comment.id;
                            }
                        }
                    });
                }
            })
            .catch(error => {
                console.error("Error loading comments:", error);
                if (isInitialLoad) {
                    const loadingElement = commentsContainer.querySelector('.loading-comments');
                    if (loadingElement) {
                        loadingElement.textContent = 'Error loading comments. Please refresh the page.';
                    }
                }
            });
    }
    
    // Function to create a comment element
    function createCommentElement(comment, isNew) {
        const commentElement = document.createElement("div");
        commentElement.className = "comment";
        commentElement.id = "comment-" + comment.id;
        if (isNew) {
            commentElement.className += " new-comment";
        }
        commentElement.setAttribute("data-comment-id", comment.id);
        
        const commentDate = new Date(comment.created_at);
        const formattedDate = commentDate.toLocaleString();
        
        // Build HTML for the comment
        let commentHTML = `
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
            <div class="comment-footer">
        `;
        
        // Add report link if user is logged in
        if (document.getElementById('reportModal')) {
            commentHTML += `<a href="#" class="report-link" data-type="comment" data-id="${comment.id}">Report</a>`;
        }
        
        // Add delete button for admins or if user is the comment owner
        if (comment.is_admin_viewing) {
            commentHTML += `
                <form action="admin_actions.php" method="post" class="admin-comment-action">
                    <input type="hidden" name="action" value="delete_comment">
                    <input type="hidden" name="comment_id" value="${comment.id}">
                    <button type="submit" class="delete-button" onclick="return confirm('Are you sure you want to delete this comment?')">Delete Comment</button>
                </form>
            `;
        } else if (comment.is_comment_owner) {
            commentHTML += `
                <form action="delete_comment.php" method="post" class="user-comment-action">
                    <input type="hidden" name="comment_id" value="${comment.id}">
                    <input type="hidden" name="comment_type" value="thread">
                    <input type="hidden" name="redirect" value="thread.php?id=${window.location.href.split('?id=')[1]}">
                    <button type="submit" class="delete-comment-button" onclick="return confirm('Are you sure you want to delete this comment?')">Delete</button>
                </form>
            `;
        }
        
        commentHTML += `</div>`;
        
        commentElement.innerHTML = commentHTML;
        
        // Add event listeners for report links
        setupCommentEventListeners(commentElement);
        
        return commentElement;
    }
    
    // Function to append a comment to a container
    function appendComment(comment, isNew, container) {
        const commentElement = createCommentElement(comment, isNew);
        
        // Check if the comment already exists to avoid duplicates
        const existingComment = document.querySelector(`[data-comment-id="${comment.id}"]`);
        if (!existingComment) {
            container.appendChild(commentElement);
            
            // If it's a new comment (not initial load), highlight it briefly
            if (isNew) {
                setTimeout(() => {
                    commentElement.classList.remove("new-comment");
                }, 3000);
            }
        }
    }
    
    // Function to set up event listeners for comment elements
    function setupCommentEventListeners(commentElement) {
        // Add event listener to report link
        const reportLink = commentElement.querySelector('.report-link');
        if (reportLink) {
            reportLink.addEventListener('click', function(e) {
                e.preventDefault();
                const contentType = this.getAttribute('data-type');
                const contentId = this.getAttribute('data-id');
                
                document.getElementById('content_type').value = contentType;
                document.getElementById('content_id').value = contentId;
                
                document.getElementById('reportModal').style.display = 'block';
            });
        }
    }
}); 