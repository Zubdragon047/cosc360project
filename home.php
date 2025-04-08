<?php
session_start();
require_once('protected/config.php');

$page_title = 'Home';
$additional_scripts = '<script src="scripts/homescript.js"></script>';

include 'includes/header.php';
?>

<div class="main-container">
    <h2>Home</h2>
    <h3>Featured Items</h3>
    <div class="feature-container">
        <img src="images/book.PNG">
        <img src="images/book.PNG">
        <img src="images/book.PNG">
    </div>
</div>

<?php include 'includes/footer.php'; ?>