<?php
session_start();
require_once('protected/config.php');

// Check if user is logged in
if (!isset($_SESSION['username'])) {
    header('Location: login.php');
    exit;
}

// Check if it's a POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: browse.php');
    exit;
}

// Get form data
$book_id = isset($_POST['book_id']) ? intval($_POST['book_id']) : 0;
$comment = isset($_POST['comment']) ? trim($_POST['comment']) : '';
$username = $_SESSION['username'];

// Debugging
error_log("Book comment handler called: book_id=$book_id, username=$username, comment length=" . strlen($comment));

// Validate data
if (!$book_id) {
    $_SESSION['error_message'] = 'Invalid book ID';
    header('Location: browse.php');
    exit;
}

if (empty($comment)) {
    $_SESSION['error_message'] = 'Comment cannot be empty';
    header("Location: book_detail.php?id=$book_id");
    exit;
}

// Connect to database
try {
    $pdo = new PDO(DBCONNSTRING, DBUSER, DBPASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Check if the book_comments table exists, create it if it doesn't
    try {
        $checkTableStmt = $pdo->prepare("SHOW TABLES LIKE 'book_comments'");
        $checkTableStmt->execute();
        
        if ($checkTableStmt->rowCount() == 0) {
            // Table doesn't exist, create it
            $createTableSql = "CREATE TABLE IF NOT EXISTS `book_comments` (
                `comment_id` INT AUTO_INCREMENT,
                `book_id` INT NOT NULL,
                `username` VARCHAR(25) NOT NULL,
                `content` TEXT NOT NULL,
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (`comment_id`),
                FOREIGN KEY (`book_id`) REFERENCES `books`(`book_id`) ON DELETE CASCADE,
                FOREIGN KEY (`username`) REFERENCES `users`(`username`) ON DELETE CASCADE
            )";
            $pdo->exec($createTableSql);
            
            // Create indexes
            $pdo->exec("CREATE INDEX idx_book_id ON book_comments(book_id)");
            $pdo->exec("CREATE INDEX idx_book_comment_username ON book_comments(username)");
            
            error_log("Created book_comments table");
        }
    } catch (PDOException $e) {
        error_log("Error checking/creating book_comments table: " . $e->getMessage());
        $_SESSION['error_message'] = 'Error creating comments table: ' . $e->getMessage();
        header("Location: book_detail.php?id=$book_id");
        exit;
    }
    
    // Check if book exists
    $checkBookStmt = $pdo->prepare("SELECT book_id FROM books WHERE book_id = :book_id");
    $checkBookStmt->bindParam(':book_id', $book_id);
    $checkBookStmt->execute();
    
    if ($checkBookStmt->rowCount() == 0) {
        $_SESSION['error_message'] = 'Book not found';
        header('Location: browse.php');
        exit;
    }
    
    // Insert comment
    $sql = "INSERT INTO book_comments (book_id, username, content) VALUES (:book_id, :username, :content)";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':book_id', $book_id);
    $stmt->bindParam(':username', $username);
    $stmt->bindParam(':content', $comment);
    $stmt->execute();
    
    $comment_id = $pdo->lastInsertId();
    error_log("Inserted comment ID: $comment_id");
    
    // Redirect back to the book detail page
    $_SESSION['success_message'] = 'Comment added successfully';
    header("Location: book_detail.php?id=$book_id");
    exit;
    
} catch(PDOException $e) {
    error_log("Book comment error: " . $e->getMessage());
    $_SESSION['error_message'] = 'Database error: ' . $e->getMessage();
    header("Location: book_detail.php?id=$book_id");
    exit;
}
?> 