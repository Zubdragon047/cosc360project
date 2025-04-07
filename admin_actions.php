<?php
session_start();
require_once('protected/config.php');

// Check if user is logged in and is an admin
if (!isset($_SESSION['username']) || !isset($_SESSION['type']) || $_SESSION['type'] !== 'admin') {
    header("Location: login.php");
    exit;
}

// Check if form was submitted
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: admin.php");
    exit;
}

// Get action and required parameters
$action = isset($_POST['action']) ? $_POST['action'] : '';
$username = isset($_POST['username']) ? $_POST['username'] : '';
$returnToDetail = isset($_POST['from_user_detail']) && isset($_POST['username']);

// Connect to database
try {
    $pdo = new PDO(DBCONNSTRING, DBUSER, DBPASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Process actions
    switch ($action) {
        case 'promote_admin':
            if (empty($username)) {
                $_SESSION['error_message'] = "Username is required.";
                redirectBack();
            }
            
            // Don't allow promoting yourself (you're already admin)
            if ($username === $_SESSION['username']) {
                $_SESSION['error_message'] = "You cannot promote yourself.";
                redirectBack();
            }
            
            // Check if user exists
            $sql = "SELECT * FROM users WHERE username = :username";
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':username', $username, PDO::PARAM_STR);
            $stmt->execute();
            
            if ($stmt->rowCount() == 0) {
                $_SESSION['error_message'] = "User not found.";
                redirectBack();
            }
            
            // Update user type to 'admin'
            $sql = "UPDATE users SET type = 'admin' WHERE username = :username";
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':username', $username, PDO::PARAM_STR);
            $stmt->execute();
            
            $_SESSION['success_message'] = "User {$username} has been promoted to admin.";
            redirectBack();
            
        case 'demote_user':
            if (empty($username)) {
                $_SESSION['error_message'] = "Username is required.";
                redirectBack();
            }
            
            // Don't allow demoting yourself
            if ($username === $_SESSION['username']) {
                $_SESSION['error_message'] = "You cannot demote yourself.";
                redirectBack();
            }
            
            // Check if user exists
            $sql = "SELECT * FROM users WHERE username = :username";
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':username', $username, PDO::PARAM_STR);
            $stmt->execute();
            
            if ($stmt->rowCount() == 0) {
                $_SESSION['error_message'] = "User not found.";
                redirectBack();
            }
            
            // Update user type to 'user'
            $sql = "UPDATE users SET type = 'user' WHERE username = :username";
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':username', $username, PDO::PARAM_STR);
            $stmt->execute();
            
            $_SESSION['success_message'] = "User {$username} has been demoted to regular user.";
            redirectBack();
            
        case 'delete_user':
            if (empty($username)) {
                $_SESSION['error_message'] = "Username is required.";
                redirectBack();
            }
            
            // Don't allow deleting yourself
            if ($username === $_SESSION['username']) {
                $_SESSION['error_message'] = "You cannot delete your own account.";
                redirectBack();
            }
            
            // Check if user exists
            $sql = "SELECT * FROM users WHERE username = :username";
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':username', $username, PDO::PARAM_STR);
            $stmt->execute();
            
            if ($stmt->rowCount() == 0) {
                $_SESSION['error_message'] = "User not found.";
                redirectBack();
            }
            
            // Begin transaction
            $pdo->beginTransaction();
            
            try {
                // Delete user from database (foreign keys will automatically delete related content)
                $sql = "DELETE FROM users WHERE username = :username";
                $stmt = $pdo->prepare($sql);
                $stmt->bindParam(':username', $username, PDO::PARAM_STR);
                $stmt->execute();
                
                // Commit transaction
                $pdo->commit();
                
                $_SESSION['success_message'] = "User {$username} has been deleted.";
                header("Location: admin.php");
                exit;
            } catch (Exception $e) {
                // Rollback transaction on error
                $pdo->rollBack();
                $_SESSION['error_message'] = "Error deleting user: " . $e->getMessage();
                redirectBack();
            }
            
        case 'delete_book':
            $book_id = isset($_POST['book_id']) ? $_POST['book_id'] : '';
            
            if (empty($book_id)) {
                $_SESSION['error_message'] = "Book ID is required.";
                redirectBack();
            }
            
            // Check if book exists
            $sql = "SELECT * FROM books WHERE book_id = :book_id";
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':book_id', $book_id, PDO::PARAM_INT);
            $stmt->execute();
            
            if ($stmt->rowCount() == 0) {
                $_SESSION['error_message'] = "Book not found.";
                redirectBack();
            }
            
            // Begin transaction
            $pdo->beginTransaction();
            
            try {
                // Delete book from database (foreign keys will automatically delete related requests)
                $sql = "DELETE FROM books WHERE book_id = :book_id";
                $stmt = $pdo->prepare($sql);
                $stmt->bindParam(':book_id', $book_id, PDO::PARAM_INT);
                $stmt->execute();
                
                // Commit transaction
                $pdo->commit();
                
                $_SESSION['success_message'] = "Book has been deleted.";
                redirectBack();
            } catch (Exception $e) {
                // Rollback transaction on error
                $pdo->rollBack();
                $_SESSION['error_message'] = "Error deleting book: " . $e->getMessage();
                redirectBack();
            }
            
        case 'delete_thread':
            $thread_id = isset($_POST['thread_id']) ? $_POST['thread_id'] : '';
            
            if (empty($thread_id)) {
                $_SESSION['error_message'] = "Thread ID is required.";
                redirectBack();
            }
            
            // Check if thread exists
            $sql = "SELECT * FROM threads WHERE thread_id = :thread_id";
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':thread_id', $thread_id, PDO::PARAM_INT);
            $stmt->execute();
            
            if ($stmt->rowCount() == 0) {
                $_SESSION['error_message'] = "Thread not found.";
                redirectBack();
            }
            
            // Begin transaction
            $pdo->beginTransaction();
            
            try {
                // Delete thread from database (foreign keys will automatically delete related comments)
                $sql = "DELETE FROM threads WHERE thread_id = :thread_id";
                $stmt = $pdo->prepare($sql);
                $stmt->bindParam(':thread_id', $thread_id, PDO::PARAM_INT);
                $stmt->execute();
                
                // Commit transaction
                $pdo->commit();
                
                $_SESSION['success_message'] = "Thread has been deleted.";
                redirectBack();
            } catch (Exception $e) {
                // Rollback transaction on error
                $pdo->rollBack();
                $_SESSION['error_message'] = "Error deleting thread: " . $e->getMessage();
                redirectBack();
            }
            
        case 'delete_comment':
            $comment_id = isset($_POST['comment_id']) ? $_POST['comment_id'] : '';
            
            if (empty($comment_id)) {
                $_SESSION['error_message'] = "Comment ID is required.";
                redirectBack();
            }
            
            // Check if comment exists
            $sql = "SELECT * FROM comments WHERE comment_id = :comment_id";
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':comment_id', $comment_id, PDO::PARAM_INT);
            $stmt->execute();
            
            if ($stmt->rowCount() == 0) {
                $_SESSION['error_message'] = "Comment not found.";
                redirectBack();
            }
            
            // Get thread_id for potential redirection
            $commentData = $stmt->fetch(PDO::FETCH_ASSOC);
            $thread_id = $commentData['thread_id'];
            
            // Begin transaction
            $pdo->beginTransaction();
            
            try {
                // Delete comment from database
                $sql = "DELETE FROM comments WHERE comment_id = :comment_id";
                $stmt = $pdo->prepare($sql);
                $stmt->bindParam(':comment_id', $comment_id, PDO::PARAM_INT);
                $stmt->execute();
                
                // Commit transaction
                $pdo->commit();
                
                $_SESSION['success_message'] = "Comment has been deleted.";
                
                // Check if we came from a thread page
                if (isset($_POST['from_thread']) && !empty($thread_id)) {
                    header("Location: thread.php?id=" . $thread_id);
                    exit;
                } else {
                    redirectBack();
                }
            } catch (Exception $e) {
                // Rollback transaction on error
                $pdo->rollBack();
                $_SESSION['error_message'] = "Error deleting comment: " . $e->getMessage();
                redirectBack();
            }
            
        default:
            $_SESSION['error_message'] = "Invalid action.";
            redirectBack();
    }
    
} catch(PDOException $e) {
    $_SESSION['error_message'] = "Database error: " . $e->getMessage();
    redirectBack();
}

/**
 * Helper function to redirect back to appropriate page
 */
function redirectBack() {
    global $returnToDetail, $username;
    
    if ($returnToDetail) {
        header("Location: user_detail.php?username=" . urlencode($username));
    } else {
        header("Location: admin.php");
    }
    exit;
}
?> 