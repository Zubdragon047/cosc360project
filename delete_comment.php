<?php
session_start();
require_once('protected/config.php');

// Redirect if not logged in
if (!isset($_SESSION['username'])) {
    header('Location: login.php');
    exit;
}

// Ensure the request is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: myaccount.php');
    exit;
}

// Get comment data
$comment_id = isset($_POST['comment_id']) ? intval($_POST['comment_id']) : 0;
$comment_type = isset($_POST['comment_type']) ? $_POST['comment_type'] : '';
$redirect = isset($_POST['redirect']) ? $_POST['redirect'] : 'myaccount.php';

// Validate inputs
if ($comment_id <= 0 || ($comment_type !== 'thread' && $comment_type !== 'book')) {
    $_SESSION['error_message'] = "Invalid comment information.";
    header("Location: $redirect");
    exit;
}

try {
    $pdo = new PDO(DBCONNSTRING, DBUSER, DBPASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Different tables based on comment type
    if ($comment_type === 'thread') {
        $table = 'comments';
    } else {
        $table = 'book_comments';
    }
    
    // First verify the comment belongs to the current user
    $sql = "SELECT username FROM $table WHERE comment_id = :comment_id";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':comment_id', $comment_id, PDO::PARAM_INT);
    $stmt->execute();
    
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // If no comment found or doesn't belong to user
    if (!$result || $result['username'] !== $_SESSION['username']) {
        $_SESSION['error_message'] = "You can only delete your own comments.";
        header("Location: $redirect");
        exit;
    }
    
    // Delete the comment
    $sql = "DELETE FROM $table WHERE comment_id = :comment_id AND username = :username";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':comment_id', $comment_id, PDO::PARAM_INT);
    $stmt->bindParam(':username', $_SESSION['username'], PDO::PARAM_STR);
    $stmt->execute();
    
    if ($stmt->rowCount() > 0) {
        $_SESSION['success_message'] = "Comment deleted successfully.";
    } else {
        $_SESSION['error_message'] = "Failed to delete comment.";
    }
    
} catch (PDOException $e) {
    $_SESSION['error_message'] = "Database error: " . $e->getMessage();
}

// Redirect back
header("Location: $redirect");
exit;
?> 