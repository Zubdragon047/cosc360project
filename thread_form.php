<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['username'])) {
    header('Location: login.php');
    exit;
}

// Check if form was submitted
if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    header('Location: threads.php');
    exit;
}

// Validate input
if (!isset($_POST['title']) || empty($_POST['title'])) {
    die("Thread title is required.");
}

if (!isset($_POST['content']) || empty($_POST['content'])) {
    die("Thread content is required.");
}

$title = $_POST['title'];
$content = $_POST['content'];
$username = $_SESSION['username'];

// Create the thread in the database
try {
    require_once('protected/config.php');
    $pdo = new PDO(DBCONNSTRING, DBUSER, DBPASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Insert the thread
    $sql = "INSERT INTO threads (title, username, content) VALUES (:title, :username, :content)";
    $stmt = $pdo->prepare($sql);
    
    $data = [
        'title' => $title,
        'username' => $username,
        'content' => $content
    ];
    
    $stmt->execute($data);
    $thread_id = $pdo->lastInsertId();
    
    // Redirect to the newly created thread
    header('Location: thread.php?id=' . $thread_id);
    exit;
} catch(PDOException $e) {
    die("Database error: " . $e->getMessage());
}
?> 