<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>COSC360 Project - Book Exchange</title>
    <link rel="stylesheet" href="css/stylesheet.css">
    <script src="scripts/dashboardscript.js"></script>
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
    <h2>Dashboard</h2>
    <?php if (!isset($_SESSION['username'])): echo '<h3 style="color:red">Must be logged in to use dashboard.</h3>';
    else: ?>
    <h3>Add Listing</h3>
    <form id="add-book-form" method="post"
            action="http://www.randyconnolly.com/tests/process.php"
            enctype="multipart/form-data"
            novalidate>
            <div class="form-group">
                <label for="book-title">Title</label>
                <input type="text" id="book-title" name="book-title" placeholder="Enter book title." required>
                <span id="title-error-message"></span>
            </div>
            <div class="form-group">
                <label for="book-description">Description</label>
                <textarea id="book-description" name="book-desription" placeholder="Enter book description." required></textarea>
                <span id="description-error-message"></span>
            </div>
            <div>
                <label for="book-category">Category</label>
                <select id="book-category" name="book-category">
                    <option value=" ">Choose a genre/category</option>
                    <option value="non-fiction">non-fiction</option>
                    <option value="fantasy">fantasy</option>
                    <option value="sci-fi">sci-fi</option>
                    <option value="romance">romance</option>
                    <option value="mystery">mystery</option>
                    <option value="horror">horror</option>
                </select>
                <span id="category-error-message"></span>
            </div>
            <div class="form-group">
                <label for="book-picture">(OPTIONAL) Cover Image (jpg/gif/bmp/png less than 10mb)</label>
                <input type="file" id="book-picture" name="book-picture">
            </div>
            <div class="form-buttons">
                <button type="submit" class="submit">Submit</button>
            </div>
        </form>
    <h3>My Listings</h3>
    <?php endif; ?>
    
</div>

<footer>
    <nav>
        <a href="home.php">| Home |</a>
        <a href="about.php"> About |</a>
        <a href="browse.php"> Browse |</a>
        <a href="dashboard.php"> Dashboard |</a>
        <a href="threads.php"> Discussions |</a>
    </nav>
</footer>
</body>
</html>