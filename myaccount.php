<?php
session_start();
require_once('protected/config.php');

// Redirect if not logged in
if (!isset($_SESSION['username'])) {
    header('Location: login.php');
    exit;
}

$page_title = 'My Account';
$additional_scripts = '<script src="scripts/myaccountscript.js"></script>';

include 'includes/header.php';
?>

<div class="main-container">
    <div>
        <h2>My Account</h2>
        <h3>User Details</h3>
        <?php
        echo "<p>Username: ".htmlspecialchars($_SESSION['username'])."</p>";
        echo "<p>Password: ".htmlspecialchars($_SESSION['password'])."</p>";
        echo "<p>Email: ".htmlspecialchars($_SESSION['email'])."</p>";
        echo "<p>First Name: ".htmlspecialchars($_SESSION['firstname'])."</p>";
        echo "<p>Last Name: ".htmlspecialchars($_SESSION['lastname'])."</p>";
        echo "<p>Profile Pic: </p>";
        echo '<img class="account-profilepic" src="'.htmlspecialchars($_SESSION['profilepic']).'">';
        ?>
    </div>
    
</div>

<?php include 'includes/footer.php'; ?>