<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['username'])) {
    header('Location: login.php');
    exit;
}

// Check if form was submitted
if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    header('Location: dashboard.php');
    exit;
}

// Validate input
if (!isset($_POST['book-title']) || empty($_POST['book-title'])) {
    die("Book title is required.");
}

if (!isset($_POST['book-description']) || empty($_POST['book-description'])) {
    die("Book description is required.");
}

if (!isset($_POST['book-category']) || $_POST['book-category'] == " ") {
    die("Book category is required.");
}

$title = $_POST['book-title'];
$description = $_POST['book-description'];
$category = $_POST['book-category'];
$username = $_SESSION['username'];
$cover_image = null;

// Handle cover image upload if provided
if (isset($_FILES['book-picture']) && $_FILES['book-picture']['error'] != UPLOAD_ERR_NO_FILE) {
    $max_file_size = 10000000; // 10MB
    $valid_ext = array("jpg", "jpeg", "png", "gif", "bmp");
    $valid_mime = array("image/jpeg", "image/png", "image/gif", "image/bmp");
    
    // Check for upload errors
    if ($_FILES['book-picture']['error'] != UPLOAD_ERR_OK) {
        die("File upload error: " . $_FILES['book-picture']['error']);
    }
    
    // Check file size
    if ($_FILES['book-picture']['size'] > $max_file_size) {
        die("File size larger than 10MB.");
    }
    
    // Check file type
    $file_info = pathinfo($_FILES['book-picture']['name']);
    $ext = strtolower($file_info['extension']);
    
    if (!in_array($_FILES['book-picture']['type'], $valid_mime) || !in_array($ext, $valid_ext)) {
        die("Invalid file type. Allowed types: JPG, PNG, GIF, BMP");
    }
    
    // Generate unique filename
    $new_filename = "book_" . time() . "_" . $username . "." . $ext;
    $upload_path = "./bookcovers/" . $new_filename;
    
    // Create bookcovers directory if it doesn't exist
    if (!file_exists("./bookcovers")) {
        mkdir("./bookcovers", 0777, true);
    }
    
    // Move the uploaded file
    if (!move_uploaded_file($_FILES['book-picture']['tmp_name'], $upload_path)) {
        die("Unable to move file to destination folder.");
    }
    
    $cover_image = $upload_path;
}

// Add the book to the database
try {
    require_once('protected/config.php');
    $pdo = new PDO(DBCONNSTRING, DBUSER, DBPASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Insert the book
    $sql = "INSERT INTO books (title, description, category, username, cover_image) 
            VALUES (:title, :description, :category, :username, :cover_image)";
    $stmt = $pdo->prepare($sql);
    
    $data = [
        'title' => $title,
        'description' => $description,
        'category' => $category,
        'username' => $username,
        'cover_image' => $cover_image
    ];
    
    $stmt->execute($data);
    $book_id = $pdo->lastInsertId();
    
    // Redirect to dashboard with success message
    header('Location: dashboard.php?success=1');
    exit;
} catch(PDOException $e) {
    die("Database error: " . $e->getMessage());
}
?> 