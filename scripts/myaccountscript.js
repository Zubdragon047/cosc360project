document.addEventListener("DOMContentLoaded", () => {
    const form = document.getElementById("edit-user-form");
    form.setAttribute("novalidate", true);
    form.addEventListener("submit", (e) => {
        const editname = document.getElementById("edit-username");
        const oldpassword = document.getElementById("edit-old-password");
        const newpassword = document.getElementById("edit-new-password");
        const confirmpassword = document.getElementById("confirm-new-password");
        const editemail = document.getElementById("edit-email");
        const editnamemsg = document.getElementById("edit-username-error-message");
        const oldpassmsg = document.getElementById("edit-password-error-message");
        const newpassmsg = document.getElementById("edit-new-password-error-message");
        const confirmpassmsg = document.getElementById("edit-confirm-password-error-message");
        const editemailmsg = document.getElementById("edit-email-error-message");
        if (editname.value == null || editname.value == "") {
            e.preventDefault();
            editnamemsg.textContent = "Must enter a user name.";
        } else {
            editnamemsg.textContent = "";
        }
        if (oldpassword.value == null || oldpassword.value == "") {
            e.preventDefault();
            oldpassmsg.textContent = "Must enter a password.";
        } else {
            oldpassmsg.textContent = "";
        }
        if (newpassword.value != null && newpassword.value != "") {
            if (newpassword.value != confirmpassword.value) {
                e.preventDefault();
                confirmpassmsg.textContent = "New password must match confirmed password.";
            } else {
                confirmpassmsg.textContent = "";
            }
        } else {
            confirmpassmsg.textContent = "";
        }
        if (editemail.value == null || editemail.value == "" || !editemail.value.includes('@')) {
            e.preventDefault();
            editemailmsg.textContent = "Must enter an email address.";
        } else {
            editemailmsg.textContent = "";
        }
    });
    
    // Initialize the comment history functionality
    initCommentHistory();
});

/**
 * Initialize the comment history functionality
 */
function initCommentHistory() {
    // Get all comment content elements
    const commentContents = document.querySelectorAll('.comment-content');
    
    // Add click event to each comment that has truncated content
    commentContents.forEach(content => {
        // Store the full content as a data attribute if it's truncated
        const text = content.textContent;
        if (text.endsWith('...')) {
            // Set a data attribute to indicate this comment is expandable
            content.setAttribute('data-expandable', 'true');
            content.setAttribute('data-expanded', 'false');
            
            // Make it clickable to expand
            content.style.cursor = 'pointer';
            content.title = 'Click to expand';
            
            // Add click event
            content.addEventListener('click', toggleCommentExpansion);
        }
    });
}

/**
 * Toggle comment expansion when clicked
 */
function toggleCommentExpansion(event) {
    const content = event.currentTarget;
    const isExpanded = content.getAttribute('data-expanded') === 'true';
    
    if (isExpanded) {
        // Collapse the comment
        const text = content.textContent;
        content.textContent = text.substring(0, 150) + '...';
        content.setAttribute('data-expanded', 'false');
        content.title = 'Click to expand';
    } else {
        // Expand the comment - fetch the full content
        const commentItem = content.closest('.comment-item');
        const commentId = commentItem.getAttribute('data-comment-id');
        const commentType = commentItem.getAttribute('data-comment-type');
        
        // If we already know the full content
        if (content.hasAttribute('data-full-content')) {
            content.textContent = content.getAttribute('data-full-content');
            content.setAttribute('data-expanded', 'true');
            content.title = 'Click to collapse';
        } else {
            // For this demo, we'll just remove the ellipsis
            // In a real implementation, you would fetch the full content via AJAX
            const text = content.textContent;
            const fullText = text.substring(0, text.length - 3); // Remove "..."
            content.textContent = fullText;
            content.setAttribute('data-expanded', 'true');
            content.title = 'Click to collapse';
        }
    }
}