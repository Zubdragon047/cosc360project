<?php
session_start();
require_once('protected/config.php');

// Check if user is logged in
if (!isset($_SESSION['username'])) {
    header('Location: login.php');
    exit;
}

// Check if form was submitted
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: home.php');
    exit;
}

// Get form data
$contentType = isset($_POST['content_type']) ? $_POST['content_type'] : '';
$contentId = isset($_POST['content_id']) ? (int)$_POST['content_id'] : 0;
$reason = isset($_POST['reason']) ? $_POST['reason'] : '';
$details = isset($_POST['details']) ? $_POST['details'] : '';
$reporterUsername = $_SESSION['username'];
$returnUrl = isset($_POST['return_url']) ? $_POST['return_url'] : 'home.php';

// Validate data
if (empty($contentType) || empty($contentId) || empty($reason)) {
    $_SESSION['error_message'] = 'Missing required fields for report.';
    header('Location: ' . $returnUrl);
    exit;
}

// Connect to database
try {
    $pdo = new PDO(DBCONNSTRING, DBUSER, DBPASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Check if a reports table exists, if not create it
    $sql = "CREATE TABLE IF NOT EXISTS reports (
        report_id INT AUTO_INCREMENT PRIMARY KEY,
        content_type VARCHAR(50) NOT NULL,
        content_id INT NOT NULL,
        reporter_username VARCHAR(255) NOT NULL,
        reason VARCHAR(100) NOT NULL,
        details TEXT,
        status VARCHAR(20) DEFAULT 'pending',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (reporter_username) REFERENCES users(username) ON DELETE CASCADE
    )";
    $pdo->exec($sql);
    
    // Insert the report
    $sql = "INSERT INTO reports (content_type, content_id, reporter_username, reason, details) 
            VALUES (:content_type, :content_id, :reporter_username, :reason, :details)";
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':content_type', $contentType);
    $stmt->bindValue(':content_id', $contentId);
    $stmt->bindValue(':reporter_username', $reporterUsername);
    $stmt->bindValue(':reason', $reason);
    $stmt->bindValue(':details', $details);
    $stmt->execute();
    
    $_SESSION['success_message'] = 'Thank you for your report. An administrator will review it soon.';
    header('Location: ' . $returnUrl);
    exit;
    
} catch(PDOException $e) {
    $_SESSION['error_message'] = 'Error submitting report: ' . $e->getMessage();
    header('Location: ' . $returnUrl);
    exit;
}
?> 