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
    
    <div class="thread-detail">
        <h2><?php echo htmlspecialchars($thread['title']); ?></h2>
        <p class="thread-meta">
            Started by: <?php echo htmlspecialchars($threadAuthor); ?> | 
            Date: <?php echo date('M j, Y g:i A', strtotime($thread['created_at'])); ?>
        </p>
        <div class="thread-content">
            <?php echo nl2br(htmlspecialchars($thread['content'])); ?>
        </div>
        <?php if (isset($_SESSION['username'])): ?>
        <div class="thread-actions">
            <a href="#" class="report-link" data-toggle="modal" data-target="#reportModal" 
               data-type="thread" data-id="<?php echo $thread_id; ?>">Report Thread</a>
        </div>
        <?php endif; ?>
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