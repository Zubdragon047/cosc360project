<?php
session_start();
require_once('protected/config.php');

$page_title = 'About';
$additional_scripts = '<script src="scripts/aboutscript.js"></script>';

include 'includes/header.php';
?>

<div class="main-container">
    <div>
        <h2>About</h2>
        <h3>Describe our team and the purpose of the project?</h3>
    </div>
    
</div>

<?php include 'includes/footer.php'; ?>