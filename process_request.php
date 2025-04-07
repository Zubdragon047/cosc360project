<?php
session_start();
require_once('protected/config.php');

// Check if user is logged in
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit;
}

// Check if form was submitted with request_id and action
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['request_id']) || !is_numeric($_POST['request_id']) || 
    !isset($_POST['action']) || ($_POST['action'] != 'accept' && $_POST['action'] != 'decline')) {
    header("Location: dashboard.php");
    exit;
}

$requestId = $_POST['request_id'];
$action = $_POST['action'];
$username = $_SESSION['username'];

try {
    $pdo = new PDO(DBCONNSTRING, DBUSER, DBPASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Begin transaction
    $pdo->beginTransaction();
    
    // Check if request exists and belongs to the user's book
    $sqlCheck = "SELECT r.*, b.username as book_owner, b.book_id 
                 FROM book_requests r
                 JOIN books b ON r.book_id = b.book_id
                 WHERE r.request_id = :requestId AND b.username = :username AND r.status = 'pending'";
    $stmtCheck = $pdo->prepare($sqlCheck);
    $stmtCheck->bindValue(':requestId', $requestId);
    $stmtCheck->bindValue(':username', $username);
    $stmtCheck->execute();
    
    if ($stmtCheck->rowCount() == 0) {
        // Request not found or not owned by this user
        $pdo->rollBack();
        $_SESSION['error_message'] = "Invalid request or you don't have permission to process it.";
        header("Location: dashboard.php");
        exit;
    }
    
    $request = $stmtCheck->fetch(PDO::FETCH_ASSOC);
    $bookId = $request['book_id'];
    $requesterUsername = $request['requester_username'];
    
    if ($action == 'accept') {
        // Update request status to 'accepted'
        $sqlUpdateRequest = "UPDATE book_requests 
                            SET status = 'accepted'
                            WHERE request_id = :requestId";
        $stmtUpdateRequest = $pdo->prepare($sqlUpdateRequest);
        $stmtUpdateRequest->bindValue(':requestId', $requestId);
        $stmtUpdateRequest->execute();
        
        // Update book status to 'borrowed'
        $sqlUpdateBook = "UPDATE books SET status = 'borrowed' WHERE book_id = :bookId";
        $stmtUpdateBook = $pdo->prepare($sqlUpdateBook);
        $stmtUpdateBook->bindValue(':bookId', $bookId);
        $stmtUpdateBook->execute();
        
        // Decline all other pending requests for this book
        $sqlDeclineOthers = "UPDATE book_requests 
                            SET status = 'declined'
                            WHERE book_id = :bookId AND request_id != :requestId AND status = 'pending'";
        $stmtDeclineOthers = $pdo->prepare($sqlDeclineOthers);
        $stmtDeclineOthers->bindValue(':bookId', $bookId);
        $stmtDeclineOthers->bindValue(':requestId', $requestId);
        $stmtDeclineOthers->execute();
        
        $message = "Book request accepted successfully.";
    } else { // decline
        // Update request status to 'declined'
        $sqlUpdateRequest = "UPDATE book_requests 
                            SET status = 'declined'
                            WHERE request_id = :requestId";
        $stmtUpdateRequest = $pdo->prepare($sqlUpdateRequest);
        $stmtUpdateRequest->bindValue(':requestId', $requestId);
        $stmtUpdateRequest->execute();
        
        // Check if there are any other pending requests for this book
        $sqlCheckOthers = "SELECT COUNT(*) as count FROM book_requests 
                          WHERE book_id = :bookId AND status = 'pending'";
        $stmtCheckOthers = $pdo->prepare($sqlCheckOthers);
        $stmtCheckOthers->bindValue(':bookId', $bookId);
        $stmtCheckOthers->execute();
        $count = $stmtCheckOthers->fetch(PDO::FETCH_ASSOC)['count'];
        
        // If no other pending requests, change book status back to 'available'
        if ($count == 0) {
            $sqlUpdateBook = "UPDATE books SET status = 'available' WHERE book_id = :bookId";
            $stmtUpdateBook = $pdo->prepare($sqlUpdateBook);
            $stmtUpdateBook->bindValue(':bookId', $bookId);
            $stmtUpdateBook->execute();
        }
        
        $message = "Book request declined successfully.";
    }
    
    // Commit transaction
    $pdo->commit();
    
    $_SESSION['success_message'] = $message;
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