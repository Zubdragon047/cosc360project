<?php
session_start();
require_once('protected/config.php');

// Redirect if not logged in
if (!isset($_SESSION['username'])) {
    header('Location: login.php');
    exit;
}

// Get user's comment history
$commentHistory = [];
try {
    $pdo = new PDO(DBCONNSTRING, DBUSER, DBPASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Get all comments by the user with thread info for context
    $sql = "SELECT c.comment_id, c.content, c.created_at, t.thread_id, t.title as thread_title
            FROM comments c
            JOIN threads t ON c.thread_id = t.thread_id
            WHERE c.username = :username
            ORDER BY c.created_at DESC
            LIMIT 20";
    
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':username', $_SESSION['username']);
    $stmt->execute();
    $commentHistory = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get all book comments by the user
    $sqlBookComments = "SELECT bc.comment_id, bc.content, bc.created_at, b.book_id, b.title as book_title
                        FROM book_comments bc
                        JOIN books b ON bc.book_id = b.book_id
                        WHERE bc.username = :username
                        ORDER BY bc.created_at DESC
                        LIMIT 20";
    
    $stmtBookComments = $pdo->prepare($sqlBookComments);
    $stmtBookComments->bindParam(':username', $_SESSION['username']);
    $stmtBookComments->execute();
    $bookCommentHistory = $stmtBookComments->fetchAll(PDO::FETCH_ASSOC);
    
} catch(PDOException $e) {
    $error = "Database error: " . $e->getMessage();
}

$page_title = 'My Account';
$additional_scripts = '<script src="scripts/myaccountscript.js"></script>';

include 'includes/header.php';
?>

<div class="main-container">
<?php if (isset($_SESSION['username'])): ?>
    <div>
    <h2>My Account</h2>
        <form id="edit-user-form" method="post"
            action="edit_user_form.php"
            enctype="multipart/form-data"
            novalidate>
            <h3>Edit Account Details</h3>
            <h3>Leave new password fields blank if not changing</h3>
            <div class="form-group">
                <label for="username">User Name</label>
                <input type="text" id="edit-username" name="edit-username" value=<?php echo $_SESSION['username'] ?> required />
                <span id="edit-username-error-message"></span>
            </div>
            <div class="form-group">
                <label for="old-password">Password</label>
                <input type="password" id="edit-old-password" name="edit-old-password" placeholder="Enter current password." required />
                <span id="edit-password-error-message"></span>
            </div>
            <div class="form-group">
                <label for="new-password">New Password</label>
                <input type="password" id="edit-new-password" name="edit-new-password" placeholder="Enter new password if desired." />
                <span id="edit-new-password-error-message"></span>
            </div>
            <div class="form-group">
                <label for="confirm-new-password">Confirm New Password</label>
                <input type="password" id="confirm-new-password" name="confirm-new-password" placeholder="Confirm new password." />
                <span id="edit-confirm-password-error-message"></span>
            </div>
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="edit-email" name="edit-email" value=<?php echo $_SESSION['email'] ?> required />
                <span id="edit-email-error-message"></span>
            </div>
            <div class="form-group">
                <label for="edit-firstname">First Name</label>
                <?php if($_SESSION['firstname'] != ""): ?>
                    <input type="text" id="edit-firstname" name="edit-firstname" value=<?php echo $_SESSION['firstname'] ?> />
                <?php else: ?>
                    <input type="text" id="edit-firstname" name="edit-firstname" placeholder="Enter first name. (OPTIONAL)" />
                <?php endif; ?>
            </div>
            <div class="form-group">
                <label for="lastname">Last Name</label>
                <?php if($_SESSION['lastname'] != ""): ?>
                    <input type="text" id="register-lastname" name="edit-lastname" value=<?php echo $_SESSION['lastname'] ?> />
                <?php else: ?>
                    <input type="text" id="register-lastname" name="edit-lastname" placeholder="Enter last name. (OPTIONAL)" />
                <?php endif; ?>
            </div>
            <div class="form-group">
                <label for="profilepic">Profile Picture</label>
                <label>(jpg/gif/bmp/png less than 10mb)</label>
                <input type="file" id="register-profilepic" name="profilepic">
            </div>
            <div class="form-buttons">
                <button type="submit" class="submit">Submit</button>
            </div>
        </form>
    </div>
    
    <!-- Comment History Section -->
    <div class="comment-history-section">
        <h3>My Comment History</h3>
        
        <?php if (isset($error)): ?>
            <div class="error-message"><?php echo $error; ?></div>
        <?php else: ?>
            <!-- Discussion Thread Comments -->
            <div class="comment-section">
                <h4>Discussion Comments</h4>
                <?php if (empty($commentHistory)): ?>
                    <p>You haven't made any discussion comments yet.</p>
                <?php else: ?>
                    <div class="comment-list">
                        <?php foreach ($commentHistory as $comment): ?>
                            <div class="comment-item" data-comment-id="<?php echo $comment['comment_id']; ?>" data-comment-type="thread">
                                <div class="comment-header">
                                    <a href="thread.php?id=<?php echo $comment['thread_id']; ?>#comment-<?php echo $comment['comment_id']; ?>">
                                        <strong><?php echo htmlspecialchars($comment['thread_title']); ?></strong>
                                    </a>
                                    <span class="comment-date"><?php echo date('M j, Y g:i A', strtotime($comment['created_at'])); ?></span>
                                </div>
                                <div class="comment-content" <?php if(strlen($comment['content']) > 150): ?>data-full-content="<?php echo htmlspecialchars($comment['content']); ?>"<?php endif; ?>>
                                    <?php echo htmlspecialchars(substr($comment['content'], 0, 150)) . (strlen($comment['content']) > 150 ? '...' : ''); ?>
                                </div>
                                <div class="comment-actions">
                                    <form action="delete_comment.php" method="post" class="delete-comment-form" onsubmit="return confirm('Are you sure you want to delete this comment? This action cannot be undone.')">
                                        <input type="hidden" name="comment_id" value="<?php echo $comment['comment_id']; ?>">
                                        <input type="hidden" name="comment_type" value="thread">
                                        <input type="hidden" name="redirect" value="myaccount.php">
                                        <button type="submit" class="delete-comment-button">Delete</button>
                                    </form>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
            
            <!-- Book Comments -->
            <div class="comment-section">
                <h4>Book Comments</h4>
                <?php if (empty($bookCommentHistory)): ?>
                    <p>You haven't made any book comments yet.</p>
                <?php else: ?>
                    <div class="comment-list">
                        <?php foreach ($bookCommentHistory as $comment): ?>
                            <div class="comment-item" data-comment-id="<?php echo $comment['comment_id']; ?>" data-comment-type="book">
                                <div class="comment-header">
                                    <a href="book_detail.php?id=<?php echo $comment['book_id']; ?>">
                                        <strong><?php echo htmlspecialchars($comment['book_title']); ?></strong>
                                    </a>
                                    <span class="comment-date"><?php echo date('M j, Y g:i A', strtotime($comment['created_at'])); ?></span>
                                </div>
                                <div class="comment-content" <?php if(strlen($comment['content']) > 150): ?>data-full-content="<?php echo htmlspecialchars($comment['content']); ?>"<?php endif; ?>>
                                    <?php echo htmlspecialchars(substr($comment['content'], 0, 150)) . (strlen($comment['content']) > 150 ? '...' : ''); ?>
                                </div>
                                <div class="comment-actions">
                                    <form action="delete_comment.php" method="post" class="delete-comment-form" onsubmit="return confirm('Are you sure you want to delete this comment? This action cannot be undone.')">
                                        <input type="hidden" name="comment_id" value="<?php echo $comment['comment_id']; ?>">
                                        <input type="hidden" name="comment_type" value="book">
                                        <input type="hidden" name="redirect" value="myaccount.php">
                                        <button type="submit" class="delete-comment-button">Delete</button>
                                    </form>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
<?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>