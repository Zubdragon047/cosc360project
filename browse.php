<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>COSC360 Project - Book Exchange</title>
    <link rel="stylesheet" href="css/stylesheet.css">
    <script src="scripts/browsescript.js"></script>
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
            </nav>
        </div>
    </header>

<div class="main-container">
    <h2>Browse</h2>
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
    <?php
    try {
        require_once('protected/config.php');
        $pdo = new PDO(DBCONNSTRING, DBUSER, DBPASS);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $sql = "select * from books where username=?";
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(1, $_SESSION['username']);
        $stmt->execute();
        echo "<h3>Non Fiction</h3>";
        echo "<div class='browse-container'>";
        while ($row = $stmt->fetch()) {
            if ($row['category'] == 'non-fiction'){
                echo "<div class='browse-bookentry'>";
                echo "<img class='browse-bookpic' src=".$row['coverimage'].">";
                echo "<div class='browse-info'>";
                echo "<p>Title: ".$row['title']."</p>";
                echo "<p>Description: ".$row['description']."</p>";
                echo "<p>Listed By: ".$row['username']."</p>";
                echo "</div>";
                echo "</div>";
            }
        }
        echo "</div>";
        echo "<h3>Fantasy</h3>";
        echo "<div class='browse-container'>";
        $stmt->execute();
        while ($row = $stmt->fetch()) {
            if ($row['category'] == 'fantasy'){
                echo "<div class='browse-bookentry'>";
                echo "<img class='browse-bookpic' src=".$row['coverimage'].">";
                echo "<div class='browse-info'>";
                echo "<p>Title: ".$row['title']."</p>";
                echo "<p>Description: ".$row['description']."</p>";
                echo "<p>Listed By: ".$row['username']."</p>";
                echo "</div>";
                echo "</div>";
            }
        }
        echo "</div>";
        echo "<h3>Science Fiction</h3>";
        echo "<div class='browse-container'>";
        $stmt->execute();
        while ($row = $stmt->fetch()) {
            if ($row['category'] == 'sci-fi'){
                echo "<div class='browse-bookentry'>";
                echo "<img class='browse-bookpic' src=".$row['coverimage'].">";
                echo "<div class='browse-info'>";
                echo "<p>Title: ".$row['title']."</p>";
                echo "<p>Description: ".$row['description']."</p>";
                echo "<p>Listed By: ".$row['username']."</p>";
                echo "</div>";
                echo "</div>";
            }
        }
        echo "</div>";
        echo "<h3>Romance</h3>";
        echo "<div class='browse-container'>";
        $stmt->execute();
        while ($row = $stmt->fetch()) {
            if ($row['category'] == 'romance'){
                echo "<div class='browse-bookentry'>";
                echo "<img class='browse-bookpic' src=".$row['coverimage'].">";
                echo "<div class='browse-info'>";
                echo "<p>Title: ".$row['title']."</p>";
                echo "<p>Description: ".$row['description']."</p>";
                echo "<p>Listed By: ".$row['username']."</p>";
                echo "</div>";
                echo "</div>";
            }
        }
        echo "</div>";
        echo "<h3>Mystery</h3>";
        echo "<div class='browse-container'>";
        $stmt->execute();
        while ($row = $stmt->fetch()) {
            if ($row['category'] == 'mystery'){
                echo "<div class='browse-bookentry'>";
                echo "<img class='browse-bookpic' src=".$row['coverimage'].">";
                echo "<div class='browse-info'>";
                echo "<p>Title: ".$row['title']."</p>";
                echo "<p>Description: ".$row['description']."</p>";
                echo "<p>Listed By: ".$row['username']."</p>";
                echo "</div>";
                echo "</div>";
            }
        }
        echo "</div>";
        echo "<h3>Horror</h3>";
        echo "<div class='browse-container'>";
        $stmt->execute();
        while ($row = $stmt->fetch()) {
            if ($row['category'] == 'horror'){
                echo "<div class='browse-bookentry'>";
                echo "<img class='browse-bookpic' src=".$row['coverimage'].">";
                echo "<div class='browse-info'>";
                echo "<p>Title: ".$row['title']."</p>";
                echo "<p>Description: ".$row['description']."</p>";
                echo "<p>Listed By: ".$row['username']."</p>";
                echo "</div>";
                echo "</div>";
            }
        }
        echo "</div>";
        echo "<h3>Other</h3>";
        echo "<div class='browse-container'>";
        $stmt->execute();
        while ($row = $stmt->fetch()) {
            if ($row['category'] == 'other'){
                echo "<div class='browse-bookentry'>";
                echo "<img class='browse-bookpic' src=".$row['coverimage'].">";
                echo "<div class='browse-info'>";
                echo "<p>Title: ".$row['title']."</p>";
                echo "<p>Description: ".$row['description']."</p>";
                echo "<p>Listed By: ".$row['username']."</p>";
                echo "</div>";
                echo "</div>";
            }
        }
        echo "</div>";
    } catch(PDOException $e) {
        die($e->getMessage());
    }
    ?>
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