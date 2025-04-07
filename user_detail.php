<?php
session_start();
require_once('protected/config.php');

// Set page title
$pageTitle = 'User Details';
$additionalStyles = '';
$additionalScripts = '<script src="scripts/adminscript.js"></script>';

// Check if user is logged in and is an admin
if (!isset($_SESSION['username']) || !isset($_SESSION['type']) || $_SESSION['type'] !== 'admin') {
    header("Location: login.php");
    exit;
}

// Check if username is provided
if (!isset($_GET['username']) || empty($_GET['username'])) {
    header("Location: admin.php");
    exit;
}

$targetUsername = $_GET['username'];
$userData = null;
$userBooks = [];
$userComments = [];
$userThreads = [];

try {
    $pdo = new PDO(DBCONNSTRING, DBUSER, DBPASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Get user data
    $sql = "SELECT * FROM users WHERE username = :username";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':username', $targetUsername, PDO::PARAM_STR);
    $stmt->execute();
    
    if ($stmt->rowCount() == 0) {
        $_SESSION['error_message'] = "User not found.";
        header("Location: admin.php");
        exit;
    }
    
    $userData = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Get user's books
    $sql = "SELECT * FROM books WHERE username = :username ORDER BY created_at DESC LIMIT 10";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':username', $targetUsername, PDO::PARAM_STR);
    $stmt->execute();
    $userBooks = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get user's comments
    $sql = "SELECT c.*, t.title as thread_title FROM comments c 
            JOIN threads t ON c.thread_id = t.thread_id 
            WHERE c.username = :username 
            ORDER BY c.created_at DESC LIMIT 10";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':username', $targetUsername, PDO::PARAM_STR);
    $stmt->execute();
    $userComments = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get user's threads
    $sql = "SELECT * FROM threads WHERE username = :username ORDER BY created_at DESC LIMIT 10";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':username', $targetUsername, PDO::PARAM_STR);
    $stmt->execute();
    $userThreads = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch(PDOException $e) {
    $_SESSION['error_message'] = "Database error: " . $e->getMessage();
    header("Location: admin.php");
    exit;
}

// Include header
include('includes/header.php');
?>

<div class="main-container">
    <div class="breadcrumb">
        <a href="home.php">Home</a> / 
        <a href="admin.php">Admin Dashboard</a> / 
        <span>User Details: <?php echo htmlspecialchars($targetUsername); ?></span>
    </div>
    
    <h2 style="color: #f44336; border-bottom: 2px solid #f44336; padding-bottom: 10px;">User Details: <?php echo htmlspecialchars($targetUsername); ?></h2>
    
    <?php if (isset($_SESSION['success_message'])): ?>
        <div class="success-message"><?php echo $_SESSION['success_message']; ?></div>
        <?php unset($_SESSION['success_message']); ?>
    <?php endif; ?>
    
    <?php if (isset($_SESSION['error_message'])): ?>
        <div class="error-message"><?php echo $_SESSION['error_message']; ?></div>
        <?php unset($_SESSION['error_message']); ?>
    <?php endif; ?>
    
    <div class="user-profile">
        <div class="user-profile-header">
            <div class="user-profile-image">
                <img src="<?php echo htmlspecialchars($userData['profilepic']); ?>" alt="<?php echo htmlspecialchars($targetUsername); ?>'s profile picture">
            </div>
            <div class="user-profile-info">
                <h3><?php echo htmlspecialchars($targetUsername); ?></h3>
                <p><strong>Email:</strong> <?php echo htmlspecialchars($userData['email']); ?></p>
                <p>
                    <strong>Name:</strong> 
                    <?php 
                        $fullName = trim($userData['firstname'] . ' ' . $userData['lastname']);
                        echo !empty($fullName) ? htmlspecialchars($fullName) : '<em>Not provided</em>';
                    ?>
                </p>
                <p><strong>Account Type:</strong> <?php echo htmlspecialchars($userData['type']); ?></p>
                
                <div class="user-actions">
                    <?php if ($targetUsername !== $_SESSION['username']): ?>
                        <div class="action-buttons">
                            <?php if ($userData['type'] === 'user'): ?>
                                <form action="admin_actions.php" method="post" class="inline-form">
                                    <input type="hidden" name="action" value="promote_admin">
                                    <input type="hidden" name="username" value="<?php echo htmlspecialchars($targetUsername); ?>">
                                    <button type="submit" class="promote-button">Make Admin</button>
                                </form>
                            <?php else: ?>
                                <form action="admin_actions.php" method="post" class="inline-form">
                                    <input type="hidden" name="action" value="demote_user">
                                    <input type="hidden" name="username" value="<?php echo htmlspecialchars($targetUsername); ?>">
                                    <button type="submit" class="demote-button">Remove Admin</button>
                                </form>
                            <?php endif; ?>
                            
                            <form action="admin_actions.php" method="post" class="inline-form">
                                <input type="hidden" name="action" value="delete_user">
                                <input type="hidden" name="username" value="<?php echo htmlspecialchars($targetUsername); ?>">
                                <button type="submit" class="delete-button" onclick="return confirm('Are you sure you want to delete this user? All their content will be deleted. This action cannot be undone.')">Delete User</button>
                            </form>
                        </div>
                    <?php else: ?>
                        <p><em>(This is your account)</em></p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <div class="user-content-tabs">
        <button class="content-tab-link active" data-tab="user-books">Books (<?php echo count($userBooks); ?>)</button>
        <button class="content-tab-link" data-tab="user-threads">Threads (<?php echo count($userThreads); ?>)</button>
        <button class="content-tab-link" data-tab="user-comments">Comments (<?php echo count($userComments); ?>)</button>
    </div>
    
    <div id="user-books" class="tab-content active">
        <?php if (empty($userBooks)): ?>
            <p>This user has not added any books yet.</p>
        <?php else: ?>
            <h3 class="admin-section-heading">User Books</h3>
            <div class="book-grid">
                <?php foreach ($userBooks as $book): ?>
                    <div class="book-item">
                        <?php if ($book['cover_image']): ?>
                            <img src="<?php echo htmlspecialchars($book['cover_image']); ?>" alt="<?php echo htmlspecialchars($book['title']); ?>" class="book-cover">
                        <?php else: ?>
                            <img src="images/book.PNG" alt="Default cover" class="book-cover">
                        <?php endif; ?>
                        
                        <div class="book-details">
                            <h4><?php echo htmlspecialchars($book['title']); ?></h4>
                            <p class="book-category">Category: <?php echo htmlspecialchars($book['category']); ?></p>
                            <p class="book-status">Status: <?php echo htmlspecialchars($book['status']); ?></p>
                            <p class="book-description">
                                <?php echo htmlspecialchars(substr($book['description'], 0, 100)) . 
                                (strlen($book['description']) > 100 ? '...' : ''); ?>
                            </p>
                            <div class="book-actions">
                                <a href="book_detail.php?id=<?php echo $book['book_id']; ?>" class="view-button">View</a>
                                <form action="admin_actions.php" method="post" class="inline-form">
                                    <input type="hidden" name="action" value="delete_book">
                                    <input type="hidden" name="book_id" value="<?php echo $book['book_id']; ?>">
                                    <input type="hidden" name="from_user_detail" value="1">
                                    <input type="hidden" name="username" value="<?php echo htmlspecialchars($targetUsername); ?>">
                                    <button type="submit" class="delete-button" onclick="return confirm('Are you sure you want to delete this book? This action cannot be undone.')">Delete</button>
                                </form>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
    
    <div id="user-threads" class="tab-content">
        <?php if (empty($userThreads)): ?>
            <p>This user has not created any discussion threads yet.</p>
        <?php else: ?>
            <h3 class="admin-section-heading">User Threads</h3>
            <div class="threads-container">
                <?php foreach ($userThreads as $thread): ?>
                    <div class="thread-item">
                        <h3><a href="thread.php?id=<?php echo $thread['thread_id']; ?>"><?php echo htmlspecialchars($thread['title']); ?></a></h3>
                        <p class="thread-meta">
                            Date: <?php echo date('M j, Y g:i A', strtotime($thread['created_at'])); ?>
                        </p>
                        <p class="thread-preview">
                            <?php echo htmlspecialchars(substr($thread['content'], 0, 150)) . '...'; ?>
                        </p>
                        <div class="thread-actions">
                            <a href="thread.php?id=<?php echo $thread['thread_id']; ?>" class="view-button">View</a>
                            <form action="admin_actions.php" method="post" class="inline-form">
                                <input type="hidden" name="action" value="delete_thread">
                                <input type="hidden" name="thread_id" value="<?php echo $thread['thread_id']; ?>">
                                <input type="hidden" name="from_user_detail" value="1">
                                <input type="hidden" name="username" value="<?php echo htmlspecialchars($targetUsername); ?>">
                                <button type="submit" class="delete-button" onclick="return confirm('Are you sure you want to delete this thread? All comments will also be deleted. This action cannot be undone.')">Delete</button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
    
    <div id="user-comments" class="tab-content">
        <?php if (empty($userComments)): ?>
            <p>This user has not made any comments yet.</p>
        <?php else: ?>
            <h3 class="admin-section-heading">User Comments</h3>
            <div class="comments-container">
                <?php foreach ($userComments as $comment): ?>
                    <div class="comment">
                        <div class="comment-header">
                            <p>In thread: <a href="thread.php?id=<?php echo $comment['thread_id']; ?>"><?php echo htmlspecialchars($comment['thread_title']); ?></a></p>
                            <p class="comment-date">Posted on: <?php echo date('M j, Y g:i A', strtotime($comment['created_at'])); ?></p>
                        </div>
                        <div class="comment-content">
                            <?php echo nl2br(htmlspecialchars($comment['content'])); ?>
                        </div>
                        <div class="comment-actions">
                            <form action="admin_actions.php" method="post" class="inline-form">
                                <input type="hidden" name="action" value="delete_comment">
                                <input type="hidden" name="comment_id" value="<?php echo $comment['comment_id']; ?>">
                                <input type="hidden" name="from_user_detail" value="1">
                                <input type="hidden" name="username" value="<?php echo htmlspecialchars($targetUsername); ?>">
                                <button type="submit" class="delete-button" onclick="return confirm('Are you sure you want to delete this comment? This action cannot be undone.')">Delete</button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
    
    <div class="back-link">
        <a href="admin.php">&laquo; Back to Admin Dashboard</a>
    </div>
</div>

<script>
    // Tab navigation for user content
    document.addEventListener('DOMContentLoaded', () => {
        const tabLinks = document.querySelectorAll('.content-tab-link');
        const tabContents = document.querySelectorAll('.tab-content');
        
        tabLinks.forEach(link => {
            link.addEventListener('click', () => {
                // Remove active class from all tabs
                tabLinks.forEach(t => t.classList.remove('active'));
                tabContents.forEach(c => c.classList.remove('active'));
                
                // Add active class to clicked tab
                link.classList.add('active');
                const tabId = link.getAttribute('data-tab');
                document.getElementById(tabId).classList.add('active');
            });
        });
    });
</script>

<?php
// Include footer
include('includes/footer.php');
?> 