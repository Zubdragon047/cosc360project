<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>COSC360 Project - Book Exchange</title>
    <link rel="stylesheet" href="css/stylesheet.css">
    <script src="scripts/threadviewscript.js"></script>
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

    <?php
    require_once('protected/config.php');
    
    // Check if thread ID is provided
    if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
        header('Location: threads.php');
        exit;
    }
    
    $thread_id = $_GET['id'];
    $thread = null;
    $threadAuthor = null;
    
    try {
        $pdo = new PDO(DBCONNSTRING, DBUSER, DBPASS);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // Get thread details
        $sql = "SELECT * FROM threads WHERE thread_id = :thread_id";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':thread_id', $thread_id, PDO::PARAM_INT);
        $stmt->execute();
        
        if ($stmt->rowCount() == 0) {
            header('Location: threads.php');
            exit;
        }
        
        $thread = $stmt->fetch(PDO::FETCH_ASSOC);
        $threadAuthor = $thread['username'];
    } catch(PDOException $e) {
        echo '<p>Error: ' . $e->getMessage() . '</p>';
        exit;
    }
    ?>

    <div class="main-container">
        <div class="breadcrumb">
            <a href="home.php">Home</a> / 
            <a href="threads.php">Discussions</a> / 
            <span><?php echo htmlspecialchars($thread['title']); ?></span>
        </div>
        
        <div class="thread-detail">
            <h2><?php echo htmlspecialchars($thread['title']); ?></h2>
            <p class="thread-meta">
                Started by: <?php echo htmlspecialchars($threadAuthor); ?> | 
                Date: <?php echo date('M j, Y g:i A', strtotime($thread['created_at'])); ?>
            </p>
            <div class="thread-content">
                <?php echo nl2br(htmlspecialchars($thread['content'])); ?>
            </div>
        </div>
        
        <h3>Comments</h3>
        
        <?php if (isset($_SESSION['username'])): ?>
            <div class="comment-form">
                <form id="add-comment-form" method="post">
                    <input type="hidden" id="thread_id" value="<?php echo $thread_id; ?>">
                    <div class="form-group">
                        <textarea id="comment-content" name="content" placeholder="Add your comment..." required></textarea>
                        <span id="comment-error-message"></span>
                    </div>
                    <div class="form-buttons">
                        <button type="submit" class="submit">Post Comment</button>
                    </div>
                </form>
            </div>
        <?php else: ?>
            <p><a href="login.php">Login</a> to post comments.</p>
        <?php endif; ?>
        
        <div id="comments-container">
            <!-- Comments will be loaded here via AJAX -->
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