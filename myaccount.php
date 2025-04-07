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
<?php if (isset($_SESSION['username'])): ?>
    <div>
    <h2>My Account</h2>
        <form id="edit-user-form" method="post"
            action="edit_user_form.php"
            enctype="multipart/form-data"
            novalidate>
            <h3>Edit Account Details</h3>
            <h3>Leave new password fields blank if not changing</h3>
            <div class="form-group">
                <label for="username">User Name</label>
                <input type="text" id="edit-username" name="edit-username" value=<?php echo $_SESSION['username'] ?> required />
                <span id="edit-username-error-message"></span>
            </div>
            <div class="form-group">
                <label for="old-password">Password</label>
                <input type="password" id="edit-old-password" name="edit-old-password" placeholder="Enter current password." required />
                <span id="edit-password-error-message"></span>
            </div>
            <div class="form-group">
                <label for="new-password">New Password</label>
                <input type="password" id="edit-new-password" name="edit-new-password" placeholder="Enter new password if desired." />
                <span id="edit-new-password-error-message"></span>
            </div>
            <div class="form-group">
                <label for="confirm-new-password">Confirm New Password</label>
                <input type="password" id="confirm-new-password" name="confirm-new-password" placeholder="Confirm new password." />
                <span id="edit-confirm-password-error-message"></span>
            </div>
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="edit-email" name="edit-email" value=<?php echo $_SESSION['email'] ?> required />
                <span id="edit-email-error-message"></span>
            </div>
            <div class="form-group">
                <label for="edit-firstname">First Name</label>
                <?php if($_SESSION['firstname'] != ""): ?>
                    <input type="text" id="edit-firstname" name="edit-firstname" value=<?php echo $_SESSION['firstname'] ?> />
                <?php else: ?>
                    <input type="text" id="edit-firstname" name="edit-firstname" placeholder="Enter first name. (OPTIONAL)" />
                <?php endif; ?>
            </div>
            <div class="form-group">
                <label for="lastname">Last Name</label>
                <?php if($_SESSION['lastname'] != ""): ?>
                    <input type="text" id="register-lastname" name="edit-lastname" value=<?php echo $_SESSION['lastname'] ?> />
                <?php else: ?>
                    <input type="text" id="register-lastname" name="edit-lastname" placeholder="Enter last name. (OPTIONAL)" />
                <?php endif; ?>
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
<?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>