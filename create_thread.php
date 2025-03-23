<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>COSC360 Project - Book Exchange</title>
    <link rel="stylesheet" href="css/stylesheet.css">
    <script src="scripts/createthreadscript.js"></script>
</head>

<body>
    <header>
        <h1>Book Exchange</h1>
        <div class="header-rightside">
            <?php 
            session_start(); 
            
            // Redirect if not logged in
            if (!isset($_SESSION['username'])) {
                header('Location: login.php');
                exit;
            }
            ?>
            <nav class="header-nav-top">
                <?php echo "<h3>Welcome ".$_SESSION['username']."</h3>"; ?>
                <a href="logout.php">Logout</a>
                <a href="myaccount.php">My Account</a>
                <?php echo '<img class="header-profilepic" src="'.$_SESSION['profilepic'].'">'; ?>
            </nav>
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
        <div class="breadcrumb">
            <a href="home.php">Home</a> / 
            <a href="threads.php">Discussions</a> / 
            <span>Create Thread</span>
        </div>
        
        <h2>Create New Thread</h2>
        
        <div class="create-thread-form">
            <form id="create-thread-form" method="post" action="thread_form.php" novalidate>
                <div class="form-group">
                    <label for="thread-title">Title</label>
                    <input type="text" id="thread-title" name="title" placeholder="Enter thread title" required>
                    <span id="title-error-message"></span>
                </div>
                <div class="form-group">
                    <label for="thread-content">Content</label>
                    <textarea id="thread-content" name="content" placeholder="Write your post here..." required></textarea>
                    <span id="content-error-message"></span>
                </div>
                <div class="form-buttons">
                    <button type="submit" class="submit">Create Thread</button>
                    <a href="threads.php"><button type="button" class="cancel">Cancel</button></a>
                </div>
            </form>
        </div>
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