<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>COSC360 Project - Book Exchange</title>
    <link rel="stylesheet" href="css/stylesheet.css">
    <script src="scripts/homescript.js"></script>
</head>

<body>
    <header>
        <h1>Book Exchange</h1>
        <div class="header-rightside">
            <?php session_start(); ?>
            <?php if (isset($_SESSION['username'])): ?>
                <nav class="header-nav-top">
                    <?php echo "<h3>Welcome ".$_SESSION['username']."</h3>"; ?>
                    <a href="logout.php">Logout</a>
                    <a href="myaccount.php">My Account</a>
                    <?php echo '<img class="header-profilepic" src="'.$_SESSION['profilepic'].'">'; ?>
                </nav>
            <?php else: ?>
                <nav class="header-nav-top">
                    <a href="login.php">Login</a> /
                    <a href="register.php">Register</a>
                </nav>
            <?php endif; ?>
            <nav class="header-nav-bottom">
                <a href="home.php"><button>Home</button></a>
                <a href="about.php"><button>About</button></a>
                <a href="browse.php"><button>Browse</button></a>
                <a href="dashboard.php"><button>Dashboard</button></a>
                <a href="threads.php"><button>Discussions</button></a>
            </nav>
        </div>
    </header>

<div class="main-container">
    <h2>Home</h2>
    <h3>Featured Items</h3>
    <div class="feature-container">
        <img src="images/book.PNG">
        <img src="images/book.PNG">
        <img src="images/book.PNG">
    </div>
    <h3>Discussion Board</h3>
    <form id="post-discussion-form" method="post"
        action="http://www.randyconnolly.com/tests/process.php"
        novalidate>
        <div class="form-group">
            <label for="comment">Comment</label>
            <textarea id="comment" name="comment" placeholder="Enter your comment." required></textarea>
            <span id="comment-error-message"></span>
        </div>
        <div class="form-buttons">
                <button type="submit" class="submit">Submit</button>
        </div>
    </form>
</div>

<footer>
    <nav>
        <a href="home.php">| Home |</a>
        <a href="about.php"> About |</a>
        <a href="browse.php"> Browse |</a>
        <a href="dashboard.php"> Dashboard |</a>
    </nav>
</footer>
</body>
</html>