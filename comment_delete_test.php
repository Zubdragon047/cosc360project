<?php
session_start();
require_once('protected/config.php');

// Check if user is admin
$isAdmin = isset($_SESSION['type']) && $_SESSION['type'] === 'admin';
if (!$isAdmin) {
    echo "<p>Admin access required. <a href='login.php'>Login as admin</a></p>";
    exit;
}

// Process delete if submitted
$deleteSuccess = false;
$errorMessage = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_comment'])) {
    $comment_id = isset($_POST['comment_id']) ? (int)$_POST['comment_id'] : 0;
    
    // Attempt to delete the comment
    try {
        $pdo = new PDO(DBCONNSTRING, DBUSER, DBPASS);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // Get thread_id first for return URL
        $stmt = $pdo->prepare("SELECT thread_id FROM comments WHERE comment_id = ?");
        $stmt->execute([$comment_id]);
        
        if ($stmt->rowCount() === 0) {
            $errorMessage = "Comment not found.";
        } else {
            $thread_id = $stmt->fetch(PDO::FETCH_ASSOC)['thread_id'];
            
            // Delete the comment
            $stmt = $pdo->prepare("DELETE FROM comments WHERE comment_id = ?");
            $deleteSuccess = $stmt->execute([$comment_id]);
            
            if ($deleteSuccess) {
                $deleteSuccess = true;
                $redirectUrl = "thread.php?id=$thread_id";
            } else {
                $errorMessage = "Failed to delete comment.";
            }
        }
    } catch (PDOException $e) {
        $errorMessage = "Database error: " . $e->getMessage();
    }
}

// Get all comments for testing
try {
    $pdo = new PDO(DBCONNSTRING, DBUSER, DBPASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $sql = "SELECT c.*, t.title AS thread_title, u.username 
            FROM comments c
            JOIN threads t ON c.thread_id = t.thread_id
            JOIN users u ON c.username = u.username
            ORDER BY c.created_at DESC
            LIMIT 50";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $comments = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $errorMessage = "Database error: " . $e->getMessage();
    $comments = [];
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Comment Deletion Test</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        
        h1, h2 {
            color: #333;
        }
        
        .success {
            background-color: #dff0d8;
            color: #3c763d;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        
        .error {
            background-color: #f2dede;
            color: #a94442;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        
        th {
            background-color: #e0e0e0;
            text-align: left;
        }
        
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        
        td, th {
            padding: 8px;
            border: 1px solid #ddd;
        }
        
        .delete-form {
            display: inline;
        }
        
        .delete-button {
            background-color: #f44336;
            color: white;
            border: none;
            padding: 5px 10px;
            border-radius: 3px;
            cursor: pointer;
        }
        
        .delete-button:hover {
            background-color: #d32f2f;
        }
    </style>
</head>
<body>
    <h1>Comment Deletion Test</h1>
    
    <p><a href="admin.php">Return to Admin Dashboard</a></p>
    
    <?php if ($deleteSuccess): ?>
    <div class="success">
        <p>Comment deleted successfully! <a href="<?php echo $redirectUrl; ?>">View thread</a></p>
    </div>
    <?php endif; ?>
    
    <?php if ($errorMessage): ?>
    <div class="error">
        <p><?php echo $errorMessage; ?></p>
    </div>
    <?php endif; ?>
    
    <h2>Comments List</h2>
    
    <?php if (count($comments) > 0): ?>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Content</th>
                <th>User</th>
                <th>Thread</th>
                <th>Date</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($comments as $comment): ?>
            <tr>
                <td><?php echo $comment['comment_id']; ?></td>
                <td><?php echo htmlspecialchars(substr($comment['content'], 0, 50)); ?><?php echo strlen($comment['content']) > 50 ? '...' : ''; ?></td>
                <td><?php echo htmlspecialchars($comment['username']); ?></td>
                <td><a href="thread.php?id=<?php echo $comment['thread_id']; ?>"><?php echo htmlspecialchars($comment['thread_title']); ?></a></td>
                <td><?php echo $comment['created_at']; ?></td>
                <td>
                    <form method="post" class="delete-form" onsubmit="return confirm('Are you sure you want to delete this comment?');">
                        <input type="hidden" name="comment_id" value="<?php echo $comment['comment_id']; ?>">
                        <button type="submit" name="delete_comment" class="delete-button">Delete Comment</button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php else: ?>
    <p>No comments found.</p>
    <?php endif; ?>
</body>
</html> 