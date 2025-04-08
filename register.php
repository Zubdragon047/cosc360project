<?php
session_start();
require_once('protected/config.php');

// Redirect if already logged in
if (isset($_SESSION['username'])) {
    header('Location: dashboard.php');
    exit;
}

$page_title = 'Register';
$additional_scripts = '<script src="scripts/registerscript.js"></script>';

include 'includes/header.php';
?>

<div class="main-container">
    <div>
        <h2>Register</h2>
        <h3>Create a user account</h3>
        <form id="register-form" method="post"
            action="register_form.php"
            enctype="multipart/form-data"
            novalidate>
            <div class="form-group">
                <label for="username">User Name</label>
                <input type="text" id="register-username" name="username" placeholder="Enter login name." required>
                <span id="username-error-message"></span>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="register-password" name="password" placeholder="Enter login password." required>
                <span id="password-error-message"></span>
            </div>
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="register-email" name="email" placeholder="Enter login email." required>
                <span id="email-error-message"></span>
            </div>
            <div class="form-group">
                <label for="firstname">First Name</label>
                <input type="text" id="register-firstname" name="firstname" placeholder="Enter your first name (OPTIONAL)">
            </div>
            <div class="form-group">
                <label for="lastname">Last Name</label>
                <input type="text" id="register-lastname" name="lastname" placeholder="Enter your last name (OPTIONAL)">
            </div>
            <div class="form-group">
                <label for="profilepic">Profile Picture</label>
                <label>(jpg/gif/bmp/png less than 10mb)</label>
                <input type="file" id="register-profilepic" name="profilepic">
            </div>
            <div class="form-buttons">
                <button type="submit" class="submit">Submit</button>
            </div>
        </form>
    </div>
    
</div>

<?php include 'includes/footer.php'; ?>