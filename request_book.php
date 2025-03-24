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
    header("Location: browse.php");
    exit;
}

$bookId = $_POST['book_id'];
$username = $_SESSION['username'];

try {
    $pdo = new PDO(DBCONNSTRING, DBUSER, DBPASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Begin transaction
    $pdo->beginTransaction();
    
    // Check if book exists and is available
    $sqlCheck = "SELECT * FROM books WHERE book_id = :bookId AND status = 'available'";
    $stmtCheck = $pdo->prepare($sqlCheck);
    $stmtCheck->bindValue(':bookId', $bookId);
    $stmtCheck->execute();
    
    if ($stmtCheck->rowCount() == 0) {
        // Book not found or not available
        $pdo->rollBack();
        $_SESSION['error_message'] = "This book is not available for request.";
        header("Location: book_detail.php?id=" . $bookId);
        exit;
    }
    
    $book = $stmtCheck->fetch(PDO::FETCH_ASSOC);
    
    // Check if user is not requesting their own book
    if ($book['username'] == $username) {
        $pdo->rollBack();
        $_SESSION['error_message'] = "You cannot request your own book.";
        header("Location: book_detail.php?id=" . $bookId);
        exit;
    }
    
    // Check if user has already requested this book
    $sqlCheckRequest = "SELECT * FROM book_requests 
                        WHERE book_id = :bookId AND requester_username = :username";
    $stmtCheckRequest = $pdo->prepare($sqlCheckRequest);
    $stmtCheckRequest->bindValue(':bookId', $bookId);
    $stmtCheckRequest->bindValue(':username', $username);
    $stmtCheckRequest->execute();
    
    if ($stmtCheckRequest->rowCount() > 0) {
        $pdo->rollBack();
        $_SESSION['error_message'] = "You have already requested this book.";
        header("Location: book_detail.php?id=" . $bookId);
        exit;
    }
    
    // Insert request
    $sqlInsert = "INSERT INTO book_requests (book_id, requester_username, status) 
                  VALUES (:bookId, :username, 'pending')";
    $stmtInsert = $pdo->prepare($sqlInsert);
    $stmtInsert->bindValue(':bookId', $bookId);
    $stmtInsert->bindValue(':username', $username);
    $stmtInsert->execute();
    
    // Update book status to 'reserved'
    $sqlUpdate = "UPDATE books SET status = 'reserved' WHERE book_id = :bookId";
    $stmtUpdate = $pdo->prepare($sqlUpdate);
    $stmtUpdate->bindValue(':bookId', $bookId);
    $stmtUpdate->execute();
    
    // Commit transaction
    $pdo->commit();
    
    $_SESSION['success_message'] = "Book request submitted successfully.";
    header("Location: dashboard.php");
    exit;
    
} catch(PDOException $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    $_SESSION['error_message'] = "Error: " . $e->getMessage();
    header("Location: book_detail.php?id=" . $bookId);
    exit;
}
?> 