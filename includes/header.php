<?php
// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title><?php echo isset($pageTitle) ? $pageTitle . ' - Book Exchange' : 'Book Exchange'; ?></title>
    <link rel="stylesheet" href="css/stylesheet.css">
    <?php if (isset($additionalStyles)) echo $additionalStyles; ?>
    <?php if (isset($additionalScripts)) echo $additionalScripts; ?>
</head>

<body>
    <header>
        <h1>Book Exchange</h1>
        <div class="header-rightside">
            <?php if (isset($_SESSION['username'])): ?>
                <nav class="header-nav-top">
                    <?php 
                    $welcomeText = "<h3>Welcome " . htmlspecialchars($_SESSION['username']);
                    if (isset($_SESSION['type']) && $_SESSION['type'] === 'admin') {
                        $welcomeText .= " (Admin)";
                    }
                    $welcomeText .= "</h3>";
                    echo $welcomeText;
                    ?>
                    <a href="logout.php">Logout</a>
                    <a href="myaccount.php">My Account</a>
                    <?php echo '<img class="header-profilepic" src="' . htmlspecialchars($_SESSION['profilepic']) . '">'; ?>
                </nav>
            <?php else: ?>
                <nav class="header-nav-top">
                    <a href="login.php">Login</a> /
                    <a href="register.php">Register</a>
                </nav>
            <?php endif; ?>
            <nav class="header-nav-bottom">
                <a href="home.php"><button>Home</button></a>
                <a href="browse.php"><button>Browse</button></a>
                <?php if (isset($_SESSION['username'])): ?>
                    <a href="dashboard.php"><button>Dashboard</button></a>
                    <a href="threads.php"><button>Discussions</button></a>
                    <?php if (isset($_SESSION['type']) && $_SESSION['type'] === 'admin'): ?>
                        <a href="admin.php"><button class="admin-button">Admin</button></a>
                    <?php endif; ?>
                <?php endif; ?>
            </nav>
        </div>
    </header>
