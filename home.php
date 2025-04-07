<?php
session_start();
require_once('protected/config.php');

$page_title = 'Home';
$additional_scripts = '<script src="scripts/homescript.js"></script>';

include 'includes/header.php';
?>

<div class="main-container">
    <div>
        <h2>Home</h2>
        <h3>Search</h3>
        <form id="search-form" method="post"
            action="http://www.randyconnolly.com/tests/process.php"
            novalidate>
            <div class="form-group">
                <input type="text" id="search" name="search" placeholder="Enter a book title or category." required>
                <span id="search-error-message"></span>
            </div>
            <div class="form-buttons">
                <button type="submit" class="submit">Search</button>
            </div>
        </form>
        <h3>Featured Items</h3>
    </div>
    <div class="feature-container">
        <img src="images/book.PNG">
        <img src="images/book.PNG">
        <img src="images/book.PNG">
    </div>
</div>

<?php include 'includes/footer.php'; ?>