<?php
session_start();
require_once('protected/config.php');

// Set page title
$thread_id = isset($_GET['id']) ? $_GET['id'] : 0;
$pageTitle = 'Discussion Thread';
$additionalStyles = '';
$additionalScripts = '<script src="scripts/threadviewscript.js"></script>';

// Check if thread ID is provided
if (!$thread_id || !is_numeric($thread_id)) {
    header('Location: threads.php');
    exit;
}

// Get thread details
$thread = null;
$threadAuthor = null;

try {
    $pdo = new PDO(DBCONNSTRING, DBUSER, DBPASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Get thread details
    $sql = "SELECT * FROM threads WHERE thread_id = :thread_id";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':thread_id', $thread_id, PDO::PARAM_INT);
    $stmt->execute();
    
    if ($stmt->rowCount() == 0) {
        header('Location: threads.php');
        exit;
    }
    
    $thread = $stmt->fetch(PDO::FETCH_ASSOC);
    $threadAuthor = $thread['username'];
    $pageTitle = htmlspecialchars($thread['title']);
} catch(PDOException $e) {
    echo '<p>Error: ' . $e->getMessage() . '</p>';
    exit;
}

// Include header
include('includes/header.php');
?>

<div class="main-container">
    <div class="breadcrumb">
        <a href="home.php">Home</a> / 
        <a href="threads.php">Discussions</a> / 
        <span><?php echo htmlspecialchars($thread['title']); ?></span>
    </div>
    
    <style>
        .admin-comment-action {
            display: inline-block;
            margin-left: 10px;
        }
        
        .admin-comment-action .delete-button {
            background-color: #f44336;
            color: white;
            border: none;
            padding: 5px 10px;
            border-radius: 3px;
            cursor: pointer;
            font-size: 13px;
            font-weight: bold;
        }
        
        .admin-comment-action .delete-button:hover {
            background-color: #d32f2f;
            box-shadow: 0 1px 3px rgba(0,0,0,0.2);
        }
        
        .comment-footer {
            margin-top: 10px;
            font-size: 12px;
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            padding-top: 10px;
            border-top: 1px solid #eee;
        }
        
        .comment-footer a {
            margin-right: 10px;
        }
        
        .comment {
            background-color: white;
            border: 1px solid #ddd;
            padding: 15px;
            margin-bottom: 15px;
            border-radius: 5px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        
        .comment-header {
            display: flex;
            align-items: center;
            margin-bottom: 10px;
        }
        
        .comment-profilepic {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            margin-right: 10px;
            object-fit: cover;
        }
        
        .comment-meta {
            font-size: 14px;
        }
        
        .comment-author {
            font-weight: bold;
            margin-right: 10px;
        }
        
        .comment-date {
            color: #666;
        }
        
        .comment-content {
            margin-bottom: 10px;
            background-color: #f9f9f9;
            padding: 10px;
            border-radius: 4px;
        }
        
        .admin-thread-action .delete-button {
            background-color: #f44336;
            color: white;
            border: none;
            padding: 5px 10px;
            border-radius: 3px;
            cursor: pointer;
            font-size: 14px;
        }
        
        .admin-thread-action .delete-button:hover {
            background-color: #d32f2f;
        }
        
        .thread-detail {
            background-color: white;
            border: 1px solid #ddd;
            padding: 20px;
            margin-bottom: 20px;
            border-radius: 5px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        
        .thread-content {
            background-color: #f9f9f9;
            padding: 15px;
            margin: 15px 0;
            border-radius: 4px;
            line-height: 1.5;
        }
        
        .thread-actions {
            display: flex;
            align-items: center;
            margin-top: 15px;
        }
        
        .thread-actions a {
            margin-right: 15px;
        }
        
        .report-link {
            color: #777;
            text-decoration: none;
            padding: 3px 8px;
            border-radius: 3px;
            transition: background-color 0.2s;
        }
        
        .report-link:hover {
            background-color: #f0f0f0;
            color: #555;
        }
    </style>
    
    <div class="thread-detail">
        <h2><?php echo htmlspecialchars($thread['title']); ?></h2>
        <p class="thread-meta">
            Started by: <?php echo htmlspecialchars($threadAuthor); ?> | 
            Date: <?php echo date('M j, Y g:i A', strtotime($thread['created_at'])); ?>
        </p>
        <div class="thread-content">
            <?php echo nl2br(htmlspecialchars($thread['content'])); ?>
        </div>
        <div class="thread-actions">
            <?php if (isset($_SESSION['username'])): ?>
                <a href="#" class="report-link" data-toggle="modal" data-target="#reportModal" 
                   data-type="thread" data-id="<?php echo $thread_id; ?>">Report Thread</a>
                
                <?php if (isset($_SESSION['type']) && $_SESSION['type'] === 'admin'): ?>
                    <form action="admin_actions.php" method="post" class="admin-thread-action" style="display: inline-block; margin-left: 15px;">
                        <input type="hidden" name="action" value="delete_thread">
                        <input type="hidden" name="thread_id" value="<?php echo $thread_id; ?>">
                        <button type="submit" class="delete-button" onclick="return confirm('Are you sure you want to delete this thread? All comments will be deleted as well. This action cannot be undone.')">Delete Thread</button>
                    </form>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
    
    <h3>Comments</h3>
    
    <?php if (isset($_SESSION['username'])): ?>
        <div class="comment-form">
            <form id="add-comment-form" method="post">
                <input type="hidden" id="thread_id" value="<?php echo $thread_id; ?>">
                <div class="form-group">
                    <textarea id="comment-content" name="content" placeholder="Add your comment..." required></textarea>
                    <span id="comment-error-message"></span>
                </div>
                <div class="form-buttons">
                    <button type="submit" class="submit">Post Comment</button>
                </div>
            </form>
        </div>
    <?php else: ?>
        <p><a href="login.php">Login</a> to post comments.</p>
    <?php endif; ?>
    
    <div id="comments-container">
        <!-- Comments will be loaded here via AJAX -->
        <div class="loading-comments">Loading comments...</div>
    </div>
</div>

<!-- Report Modal -->
<?php if (isset($_SESSION['username'])): ?>
<div id="reportModal" class="modal">
    <div class="modal-content">
        <span class="close">&times;</span>
        <h3>Report Content</h3>
        <form action="report_content.php" method="post">
            <input type="hidden" id="content_type" name="content_type" value="">
            <input type="hidden" id="content_id" name="content_id" value="">
            <input type="hidden" id="return_url" name="return_url" value="<?php echo $_SERVER['REQUEST_URI']; ?>">
            
            <div class="form-group">
                <label for="reason">Reason:</label>
                <select id="reason" name="reason" required>
                    <option value="">Select a reason</option>
                    <option value="inappropriate">Inappropriate content</option>
                    <option value="spam">Spam</option>
                    <option value="harassment">Harassment</option>
                    <option value="misinformation">Misinformation</option>
                    <option value="other">Other</option>
                </select>
            </div>
            
            <div class="form-group">
                <label for="details">Additional details (optional):</label>
                <textarea id="details" name="details" rows="4"></textarea>
            </div>
            
            <div class="form-buttons">
                <button type="submit" class="submit">Submit Report</button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Get the modal
    var modal = document.getElementById('reportModal');
    
    // Get the <span> element that closes the modal
    var span = modal.querySelector('.close');
    
    // When the user clicks on <span> (x), close the modal
    span.onclick = function() {
        modal.style.display = 'none';
    }
    
    // When the user clicks anywhere outside of the modal, close it
    window.onclick = function(event) {
        if (event.target == modal) {
            modal.style.display = 'none';
        }
    }
    
    // Set up report links
    var reportLinks = document.querySelectorAll('.report-link');
    reportLinks.forEach(function(link) {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            var contentType = this.getAttribute('data-type');
            var contentId = this.getAttribute('data-id');
            
            document.getElementById('content_type').value = contentType;
            document.getElementById('content_id').value = contentId;
            
            modal.style.display = 'block';
        });
    });
});
</script>
<?php endif; ?>

<?php
// Include footer
include('includes/footer.php');
?> 