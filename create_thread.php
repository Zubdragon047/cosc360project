<?php
session_start();
require_once('protected/config.php');

// Redirect if not logged in
if (!isset($_SESSION['username'])) {
    header('Location: login.php');
    exit;
}

$page_title = 'Create Thread';
$additional_scripts = '<script src="scripts/createthreadscript.js"></script>';

include 'includes/header.php';
?>

<div class="main-container">
    <div class="breadcrumb">
        <a href="home.php">Home</a> / 
        <a href="threads.php">Discussions</a> / 
        <span>Create Thread</span>
    </div>
    
    <h2>Create New Thread</h2>
    
    <div class="create-thread-form">
        <form id="create-thread-form" method="post" action="thread_form.php" novalidate>
            <div class="form-group">
                <label for="thread-title">Title</label>
                <input type="text" id="thread-title" name="title" placeholder="Enter thread title" required>
                <span id="title-error-message"></span>
            </div>
            <div class="form-group">
                <label for="thread-content">Content</label>
                <textarea id="thread-content" name="content" placeholder="Write your post here..." required></textarea>
                <span id="content-error-message"></span>
            </div>
            <div class="form-buttons">
                <button type="submit" class="submit">Create Thread</button>
                <a href="threads.php"><button type="button" class="cancel">Cancel</button></a>
            </div>
        </form>
    </div>
</div>

<?php include 'includes/footer.php'; ?> 