document.addEventListener('DOMContentLoaded', function() {
    console.log('Book detail script loaded');
    
    // Get references to elements
    const commentForm = document.getElementById('book-comment-form');
    const commentsList = document.getElementById('book-comments-list');
    const commentInput = document.getElementById('comment-input');
    const errorMessage = document.getElementById('comment-error');
    
    // Check if comment form exists (user is logged in)
    if (commentForm) {
        console.log('Comment form found');
        
        commentForm.addEventListener('submit', function(e) {
            e.preventDefault();
            console.log('Form submitted');
            
            // Clear previous error message
            errorMessage.textContent = '';
            errorMessage.style.display = 'none';
            
            // Get the comment text
            const comment = commentInput.value.trim();
            console.log('Comment:', comment);
            
            if (!comment) {
                errorMessage.textContent = 'Comment cannot be empty';
                errorMessage.style.display = 'block';
                return;
            }
            
            // Get book ID from the form
            const bookId = commentForm.querySelector('input[name="book_id"]').value;
            console.log('Book ID:', bookId);
            
            // Send the comment to the server
            const formData = new FormData();
            formData.append('book_id', bookId);
            formData.append('comment', comment);
            
            console.log('Sending request to book_comment_handler.php');
            
            fetch('book_comment_handler.php', {
                method: 'POST',
                body: formData
            })
            .then(response => {
                console.log('Response received');
                return response.json();
            })
            .then(data => {
                console.log('Processed data:', data);
                
                if (data.success) {
                    // Clear the input field
                    commentInput.value = '';
                    
                    // Add the new comment to the list
                    addCommentToList(data.comment);
                    
                    // Update comment count
                    updateCommentCount();
                } else {
                    // Show error message
                    errorMessage.textContent = data.message;
                    errorMessage.style.display = 'block';
                }
            })
            .catch(error => {
                console.error('Error:', error);
                errorMessage.textContent = 'An error occurred. Please try again.';
                errorMessage.style.display = 'block';
            });
        });
    } else {
        console.log('Comment form not found');
    }
    
    // Function to add a new comment to the list
    function addCommentToList(comment) {
        console.log('Adding comment to list:', comment);
        
        // Create comment elements
        const commentDiv = document.createElement('div');
        commentDiv.className = 'comment';
        commentDiv.id = 'comment-' + comment.id;
        
        const commentHeader = document.createElement('div');
        commentHeader.className = 'comment-header';
        
        const commentUser = document.createElement('span');
        commentUser.className = 'comment-username';
        commentUser.textContent = comment.username;
        
        const commentTime = document.createElement('span');
        commentTime.className = 'comment-time';
        commentTime.textContent = comment.created_at;
        
        const commentContent = document.createElement('div');
        commentContent.className = 'comment-content';
        commentContent.textContent = comment.content;
        
        // Assemble the comment structure
        commentHeader.appendChild(commentUser);
        commentHeader.appendChild(commentTime);
        
        commentDiv.appendChild(commentHeader);
        commentDiv.appendChild(commentContent);
        
        // Insert at the top of the comments list
        if (commentsList.firstChild) {
            commentsList.insertBefore(commentDiv, commentsList.firstChild);
        } else {
            commentsList.appendChild(commentDiv);
            
            // Remove "no comments" message if it exists
            const noComments = document.querySelector('.no-comments');
            if (noComments) {
                noComments.remove();
            }
        }
    }
    
    // Function to update comment count display
    function updateCommentCount() {
        const countElement = document.getElementById('comments-count');
        if (countElement) {
            let count = parseInt(countElement.textContent);
            countElement.textContent = count + 1;
        }
    }
}); 