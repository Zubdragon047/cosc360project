<?php
session_start();
require_once('protected/config.php');

// Check if user is logged in
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit;
}

// Check if form was submitted with book_id
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['book_id']) || !is_numeric($_POST['book_id'])) {
    header("Location: dashboard.php");
    exit;
}

$bookId = $_POST['book_id'];
$username = $_SESSION['username'];

try {
    $pdo = new PDO(DBCONNSTRING, DBUSER, DBPASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Begin transaction
    $pdo->beginTransaction();
    
    // Check if book belongs to the user and is currently borrowed
    $sqlCheck = "SELECT * FROM books 
                 WHERE book_id = :bookId AND username = :username AND status = 'borrowed'";
    $stmtCheck = $pdo->prepare($sqlCheck);
    $stmtCheck->bindValue(':bookId', $bookId);
    $stmtCheck->bindValue(':username', $username);
    $stmtCheck->execute();
    
    if ($stmtCheck->rowCount() == 0) {
        // Book not found, not owned by this user, or not borrowed
        $pdo->rollBack();
        $_SESSION['error_message'] = "Invalid book or you don't have permission to mark it as returned.";
        header("Location: dashboard.php");
        exit;
    }
    
    // Find the most recent accepted request for this book
    $sqlFindRequest = "SELECT * FROM book_requests 
                      WHERE book_id = :bookId AND status = 'accepted' 
                      ORDER BY updated_at DESC LIMIT 1";
    $stmtFindRequest = $pdo->prepare($sqlFindRequest);
    $stmtFindRequest->bindValue(':bookId', $bookId);
    $stmtFindRequest->execute();
    
    if ($stmtFindRequest->rowCount() > 0) {
        $request = $stmtFindRequest->fetch(PDO::FETCH_ASSOC);
        
        // Update request status to 'returned'
        $sqlUpdateRequest = "UPDATE book_requests 
                            SET status = 'returned'
                            WHERE request_id = :requestId";
        $stmtUpdateRequest = $pdo->prepare($sqlUpdateRequest);
        $stmtUpdateRequest->bindValue(':requestId', $request['request_id']);
        $stmtUpdateRequest->execute();
    }
    
    // Update book status to 'available'
    $sqlUpdateBook = "UPDATE books SET status = 'available' WHERE book_id = :bookId";
    $stmtUpdateBook = $pdo->prepare($sqlUpdateBook);
    $stmtUpdateBook->bindValue(':bookId', $bookId);
    $stmtUpdateBook->execute();
    
    // Commit transaction
    $pdo->commit();
    
    $_SESSION['success_message'] = "Book marked as returned successfully.";
    header("Location: dashboard.php");
    exit;
    
} catch(PDOException $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    $_SESSION['error_message'] = "Error: " . $e->getMessage();
    header("Location: dashboard.php");
    exit;
}
?> 