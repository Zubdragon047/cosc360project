<?php
session_start();
require_once('protected/config.php');

// Check for admin access
$isAdmin = isset($_SESSION['type']) && $_SESSION['type'] === 'admin';
if (!$isAdmin) {
    echo "<p>Admin access required. <a href='login.php'>Login as admin</a></p>";
    exit;
}

// Get thread ID from URL if provided
$thread_id = isset($_GET['thread_id']) ? intval($_GET['thread_id']) : 0;

// Handle deletion if requested
$deleteResult = '';
$deleteError = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_comment'])) {
    $comment_id = isset($_POST['comment_id']) ? intval($_POST['comment_id']) : 0;
    
    if ($comment_id) {
        try {
            $pdo = new PDO(DBCONNSTRING, DBUSER, DBPASS);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            // Get thread_id for the comment first
            $stmt = $pdo->prepare("SELECT thread_id FROM comments WHERE comment_id = ?");
            $stmt->execute([$comment_id]);
            
            if ($stmt->rowCount() > 0) {
                $thread_id = $stmt->fetch(PDO::FETCH_ASSOC)['thread_id'];
                
                // Delete the comment
                $deleteStmt = $pdo->prepare("DELETE FROM comments WHERE comment_id = ?");
                if ($deleteStmt->execute([$comment_id])) {
                    $deleteResult = "Comment #$comment_id deleted successfully";
                } else {
                    $deleteError = "Failed to delete comment #$comment_id";
                }
            } else {
                $deleteError = "Comment #$comment_id not found";
            }
        } catch (PDOException $e) {
            $deleteError = "Database error: " . $e->getMessage();
        }
    } else {
        $deleteError = "Invalid comment ID";
    }
}

// Load threads for selection
try {
    $pdo = new PDO(DBCONNSTRING, DBUSER, DBPASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Get threads
    $stmt = $pdo->prepare("SELECT thread_id, title FROM threads ORDER BY created_at DESC LIMIT 20");
    $stmt->execute();
    $threads = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // If thread_id is provided, get comments for that thread
    $comments = [];
    if ($thread_id) {
        $stmt = $pdo->prepare("
            SELECT c.*, u.username 
            FROM comments c
            JOIN users u ON c.username = u.username
            WHERE c.thread_id = ?
            ORDER BY c.created_at DESC
        ");
        $stmt->execute([$thread_id]);
        $comments = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Get thread details
        $stmt = $pdo->prepare("SELECT * FROM threads WHERE thread_id = ?");
        $stmt->execute([$thread_id]);
        $threadDetails = $stmt->fetch(PDO::FETCH_ASSOC);
    }
} catch (PDOException $e) {
    echo "<p>Database error: " . $e->getMessage() . "</p>";
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Thread Comments Test</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        
        h1, h2, h3 {
            color: #333;
        }
        
        .success {
            background-color: #d4edda;
            color: #155724;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        
        .error {
            background-color: #f8d7da;
            color: #721c24;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        
        .thread-select {
            margin-bottom: 20px;
        }
        
        .thread-select select {
            padding: 8px;
            width: 300px;
        }
        
        .thread-select button {
            padding: 8px 15px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        
        .thread-select button:hover {
            background-color: #0069d9;
        }
        
        .thread-details {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        
        .thread-title {
            margin-top: 0;
            color: #495057;
        }
        
        .thread-meta {
            color: #6c757d;
            font-size: 14px;
        }
        
        .thread-content {
            background-color: white;
            padding: 15px;
            border: 1px solid #dee2e6;
            border-radius: 4px;
            margin-top: 10px;
        }
        
        .comments-container {
            margin-top: 20px;
        }
        
        .comment {
            background-color: white;
            border: 1px solid #dee2e6;
            border-radius: 5px;
            padding: 15px;
            margin-bottom: 15px;
        }
        
        .comment-header {
            margin-bottom: 10px;
        }
        
        .comment-author {
            font-weight: bold;
            color: #495057;
        }
        
        .comment-date {
            color: #6c757d;
            font-size: 12px;
            margin-left: 10px;
        }
        
        .comment-content {
            background-color: #f8f9fa;
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 10px;
        }
        
        .comment-actions {
            margin-top: 10px;
        }
        
        .delete-form {
            display: inline;
        }
        
        .delete-button {
            background-color: #dc3545;
            color: white;
            border: none;
            padding: 5px 10px;
            border-radius: 3px;
            cursor: pointer;
            font-size: 12px;
        }
        
        .delete-button:hover {
            background-color: #c82333;
        }
        
        .view-thread-button {
            background-color: #28a745;
            color: white;
            border: none;
            padding: 5px 10px;
            border-radius: 3px;
            text-decoration: none;
            font-size: 12px;
            margin-right: 10px;
        }
        
        .view-thread-button:hover {
            background-color: #218838;
        }
        
        .navigation {
            margin-bottom: 20px;
        }
        
        .navigation a {
            color: #007bff;
            text-decoration: none;
            margin-right: 15px;
        }
        
        .navigation a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="navigation">
        <a href="admin.php">← Back to Admin Dashboard</a>
        <a href="test_api.php">API Test Tool</a>
    </div>
    
    <h1>Thread Comments Test</h1>
    
    <p>This tool helps diagnose issues with thread comments and deletion functionality.</p>
    
    <?php if ($deleteResult): ?>
    <div class="success">
        <p><?php echo $deleteResult; ?></p>
    </div>
    <?php endif; ?>
    
    <?php if ($deleteError): ?>
    <div class="error">
        <p><?php echo $deleteError; ?></p>
    </div>
    <?php endif; ?>
    
    <div class="thread-select">
        <h2>Select Thread</h2>
        <form method="get">
            <select name="thread_id">
                <option value="">-- Select a thread --</option>
                <?php foreach ($threads as $thread): ?>
                <option value="<?php echo $thread['thread_id']; ?>" <?php echo ($thread_id == $thread['thread_id']) ? 'selected' : ''; ?>>
                    #<?php echo $thread['thread_id']; ?>: <?php echo htmlspecialchars($thread['title']); ?>
                </option>
                <?php endforeach; ?>
            </select>
            <button type="submit">Load Comments</button>
        </form>
    </div>
    
    <?php if ($thread_id && isset($threadDetails)): ?>
    <div class="thread-details">
        <h3 class="thread-title"><?php echo htmlspecialchars($threadDetails['title']); ?></h3>
        <div class="thread-meta">
            <span>Author: <?php echo htmlspecialchars($threadDetails['username']); ?></span> | 
            <span>Created: <?php echo date('M j, Y g:i A', strtotime($threadDetails['created_at'])); ?></span>
        </div>
        <div class="thread-content">
            <?php echo nl2br(htmlspecialchars($threadDetails['content'])); ?>
        </div>
        
        <p><a href="thread.php?id=<?php echo $thread_id; ?>" target="_blank" class="view-thread-button">View Thread in Site</a></p>
    </div>
    
    <div class="comments-container">
        <h2>Comments (<?php echo count($comments); ?>)</h2>
        
        <?php if (count($comments) > 0): ?>
            <?php foreach ($comments as $comment): ?>
            <div class="comment" id="comment-<?php echo $comment['comment_id']; ?>">
                <div class="comment-header">
                    <span class="comment-author"><?php echo htmlspecialchars($comment['username']); ?></span>
                    <span class="comment-date"><?php echo date('M j, Y g:i A', strtotime($comment['created_at'])); ?></span>
                </div>
                <div class="comment-content">
                    <?php echo nl2br(htmlspecialchars($comment['content'])); ?>
                </div>
                <div class="comment-actions">
                    <a href="thread.php?id=<?php echo $thread_id; ?>#comment-<?php echo $comment['comment_id']; ?>" target="_blank" class="view-thread-button">View in Thread</a>
                    
                    <form method="post" class="delete-form" onsubmit="return confirm('Are you sure you want to delete this comment?');">
                        <input type="hidden" name="comment_id" value="<?php echo $comment['comment_id']; ?>">
                        <button type="submit" name="delete_comment" class="delete-button">Delete Comment</button>
                    </form>
                    
                    <!-- Direct admin_actions.php method -->
                    <form action="admin_actions.php" method="post" class="delete-form" style="margin-left: 10px;" onsubmit="return confirm('Try admin_actions.php delete route?');">
                        <input type="hidden" name="action" value="delete_comment">
                        <input type="hidden" name="comment_id" value="<?php echo $comment['comment_id']; ?>">
                        <button type="submit" class="delete-button" style="background-color: #6c757d;">Use admin_actions.php</button>
                    </form>
                </div>
            </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>No comments found for this thread.</p>
        <?php endif; ?>
    </div>
    <?php endif; ?>
</body>
</html> 