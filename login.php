<?php
session_start();
require_once('protected/config.php');

// Redirect if already logged in
if (isset($_SESSION['username'])) {
    header('Location: dashboard.php');
    exit;
}

$page_title = 'Login';
$additional_scripts = '<script src="scripts/loginscript.js"></script>';

include 'includes/header.php';
?>

<div class="main-container">
    <div>
        <h2>Login</h2>
        <form id="login-form" method="post"
            action="login_form.php"
            novalidate>
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="login-username" name="username" placeholder="Enter login username." required>
                <span id="name-error-message"></span>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="login-password" name="password" placeholder="Enter login password." required>
                <span id="password-error-message"></span>
            </div>
            <div class="form-buttons">
                <button type="submit" class="submit">Submit</button>
            </div>
        </form>
    </div>
    
</div>

<?php include 'includes/footer.php'; ?>