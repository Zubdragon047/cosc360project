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
$returnToDetail = isset($_POST['return_to_detail']) && $_POST['return_to_detail'] === '1';

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
            
            // Check if user exists and is not already an admin
            $sql = "SELECT type FROM users WHERE username = :username";
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':username', $username, PDO::PARAM_STR);
            $stmt->execute();
            
            if ($stmt->rowCount() === 0) {
                $_SESSION['error_message'] = "User not found.";
                redirectBack();
            }
            
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($row['type'] === 'admin') {
                $_SESSION['error_message'] = "User is already an admin.";
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
            
            // Check if user exists and is an admin
            $sql = "SELECT type FROM users WHERE username = :username";
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':username', $username, PDO::PARAM_STR);
            $stmt->execute();
            
            if ($stmt->rowCount() === 0) {
                $_SESSION['error_message'] = "User not found.";
                redirectBack();
            }
            
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($row['type'] !== 'admin') {
                $_SESSION['error_message'] = "User is not an admin.";
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
            $sql = "SELECT username FROM users WHERE username = :username";
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':username', $username, PDO::PARAM_STR);
            $stmt->execute();
            
            if ($stmt->rowCount() === 0) {
                $_SESSION['error_message'] = "User not found.";
                redirectBack();
            }
            
            // Begin transaction
            $pdo->beginTransaction();
            
            try {
                // Delete user's books
                $sql = "DELETE FROM books WHERE username = :username";
                $stmt = $pdo->prepare($sql);
                $stmt->bindParam(':username', $username, PDO::PARAM_STR);
                $stmt->execute();
                
                // Delete user's comments
                $sql = "DELETE FROM comments WHERE username = :username";
                $stmt = $pdo->prepare($sql);
                $stmt->bindParam(':username', $username, PDO::PARAM_STR);
                $stmt->execute();
                
                // Delete user's threads
                $sql = "DELETE FROM threads WHERE username = :username";
                $stmt = $pdo->prepare($sql);
                $stmt->bindParam(':username', $username, PDO::PARAM_STR);
                $stmt->execute();
                
                // Delete user's book requests
                $sql = "DELETE FROM book_requests WHERE requester_username = :username OR book_owner = :username";
                $stmt = $pdo->prepare($sql);
                $stmt->bindParam(':username', $username, PDO::PARAM_STR);
                $stmt->execute();
                
                // Delete user's reports
                $sql = "DELETE FROM reports WHERE reporter_username = :username";
                $stmt = $pdo->prepare($sql);
                $stmt->bindParam(':username', $username, PDO::PARAM_STR);
                $stmt->execute();
                
                // Finally, delete the user
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
            
            // Enhanced error logging for comment deletion
            error_log("Delete comment request received for comment ID: " . $comment_id);
            
            if (empty($comment_id)) {
                $_SESSION['error_message'] = "Comment ID is required.";
                error_log("Error: Comment ID is empty");
                redirectBack();
            }
            
            // Check if comment exists and get thread_id for redirection
            $sql = "SELECT thread_id FROM comments WHERE comment_id = :comment_id";
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':comment_id', $comment_id, PDO::PARAM_INT);
            $stmt->execute();
            
            if ($stmt->rowCount() == 0) {
                $_SESSION['error_message'] = "Comment not found.";
                error_log("Error: Comment with ID " . $comment_id . " not found");
                redirectBack();
            }
            
            $thread_id = $stmt->fetch(PDO::FETCH_ASSOC)['thread_id'];
            error_log("Comment found in thread ID: " . $thread_id);
            
            try {
                // Delete comment from database
                $sql = "DELETE FROM comments WHERE comment_id = :comment_id";
                $stmt = $pdo->prepare($sql);
                $stmt->bindParam(':comment_id', $comment_id, PDO::PARAM_INT);
                
                $result = $stmt->execute();
                
                if ($result) {
                    error_log("Comment " . $comment_id . " successfully deleted");
                    $_SESSION['success_message'] = "Comment has been deleted.";
                } else {
                    error_log("Error: Failed to delete comment " . $comment_id);
                    $_SESSION['error_message'] = "Failed to delete comment.";
                }
                
                // Check if we need to redirect to the thread detail
                $referrer = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '';
                error_log("Referrer: " . $referrer);
                
                if (strpos($referrer, 'thread.php') !== false) {
                    error_log("Redirecting to thread.php?id=" . $thread_id);
                    header("Location: thread.php?id=" . $thread_id);
                    exit;
                } else {
                    error_log("Redirecting back using redirectBack()");
                    redirectBack();
                }
            } catch (Exception $e) {
                error_log("Exception during comment deletion: " . $e->getMessage());
                $_SESSION['error_message'] = "Error deleting comment: " . $e->getMessage();
                redirectBack();
            }
            
        case 'resolve_report':
            $reportId = isset($_POST['report_id']) ? (int)$_POST['report_id'] : 0;
            
            // Validate input
            if (empty($reportId)) {
                $_SESSION['error_message'] = 'Report ID is required';
                redirectBack();
            }
            
            // Check if report exists
            $sql = "SELECT report_id FROM reports WHERE report_id = :report_id";
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':report_id', $reportId);
            $stmt->execute();
            
            if ($stmt->rowCount() === 0) {
                $_SESSION['error_message'] = 'Report not found';
                redirectBack();
            }
            
            // Update report status
            $sql = "UPDATE reports SET status = 'resolved' WHERE report_id = :report_id";
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':report_id', $reportId);
            $stmt->execute();
            
            $_SESSION['success_message'] = 'Report marked as resolved';
            redirectBack();
            
        case 'dismiss_report':
            $reportId = isset($_POST['report_id']) ? (int)$_POST['report_id'] : 0;
            
            // Validate input
            if (empty($reportId)) {
                $_SESSION['error_message'] = 'Report ID is required';
                redirectBack();
            }
            
            // Check if report exists
            $sql = "SELECT report_id FROM reports WHERE report_id = :report_id";
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':report_id', $reportId);
            $stmt->execute();
            
            if ($stmt->rowCount() === 0) {
                $_SESSION['error_message'] = 'Report not found';
                redirectBack();
            }
            
            // Update report status
            $sql = "UPDATE reports SET status = 'dismissed' WHERE report_id = :report_id";
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':report_id', $reportId);
            $stmt->execute();
            
            $_SESSION['success_message'] = 'Report dismissed';
            redirectBack();
            
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
    global $returnToDetail;
    
    // Check if a custom redirect URL was provided
    if (isset($_POST['redirect']) && !empty($_POST['redirect'])) {
        header("Location: " . $_POST['redirect']);
        exit;
    }
    
    if ($returnToDetail) {
        header("Location: " . $_SERVER['HTTP_REFERER']);
    } else {
        header("Location: admin.php");
    }
    exit;
}
?> 