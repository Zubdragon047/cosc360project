<?php
session_start();
require_once('protected/config.php');

$page_title = 'Home';
$additional_scripts = '<script src="scripts/homescript.js"></script>';

include 'includes/header.php';
?>

<div class="main-container">
    <h2>Book Exchange Platform</h2>
    <h3>COSC360 Final Project</h3>
    <p>
        Welcome to The Virtual Library and Book Exchange where you can share, borrow, 
        and exchange books within your online community. Once registered, you can 
        list books, browse available books, request book exchanges, and communicate with other users.
    </p>
    <div class="feature-container">
        <img src="images/book.PNG">
        <img src="images/book.PNG">
        <img src="images/book.PNG">
    </div>
</div>

<?php include 'includes/footer.php'; ?>