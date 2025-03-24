<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>COSC360 Project - Book Exchange</title>
    <link rel="stylesheet" href="css/stylesheet.css">
    <script src="scripts/threadscript.js"></script>
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
        <div class="breadcrumb">
            <a href="home.php">Home</a> / <span>Discussions</span>
        </div>
        
        <h2>Discussion Threads</h2>
        
        <?php if (isset($_SESSION['username'])): ?>
            <div class="create-thread">
                <a href="create_thread.php"><button class="submit">Create New Thread</button></a>
            </div>
        <?php endif; ?>
        
        <div class="search-container">
            <form id="thread-search-form" method="get" action="threads.php">
                <div class="form-group">
                    <input type="text" id="thread-search" name="search" placeholder="Search threads...">
                    <button type="submit" class="submit">Search</button>
                </div>
            </form>
        </div>
        
        <div class="threads-container">
            <?php
            require_once('protected/config.php');
            try {
                $pdo = new PDO(DBCONNSTRING, DBUSER, DBPASS);
                $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                
                // Check if search is being performed
                $search = isset($_GET['search']) ? '%' . $_GET['search'] . '%' : null;
                
                if ($search) {
                    // Search query
                    $sql = "SELECT t.*, COUNT(c.comment_id) AS comment_count 
                            FROM threads t 
                            LEFT JOIN comments c ON t.thread_id = c.thread_id 
                            WHERE t.title LIKE :search OR t.content LIKE :search 
                            GROUP BY t.thread_id 
                            ORDER BY t.updated_at DESC";
                    $stmt = $pdo->prepare($sql);
                    $stmt->bindParam(':search', $search, PDO::PARAM_STR);
                } else {
                    // Regular query - get all threads
                    $sql = "SELECT t.*, COUNT(c.comment_id) AS comment_count 
                            FROM threads t 
                            LEFT JOIN comments c ON t.thread_id = c.thread_id 
                            GROUP BY t.thread_id 
                            ORDER BY t.updated_at DESC";
                    $stmt = $pdo->prepare($sql);
                }
                
                $stmt->execute();
                
                if ($stmt->rowCount() > 0) {
                    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                        echo '<div class="thread-item" data-thread-id="' . $row['thread_id'] . '">';
                        echo '<h3><a href="thread.php?id=' . $row['thread_id'] . '">' . htmlspecialchars($row['title']) . '</a></h3>';
                        echo '<p class="thread-meta">Started by: ' . htmlspecialchars($row['username']) . ' | ';
                        echo 'Date: ' . date('M j, Y g:i A', strtotime($row['created_at'])) . ' | ';
                        echo 'Comments: ' . $row['comment_count'] . '</p>';
                        echo '<p class="thread-preview">' . htmlspecialchars(substr($row['content'], 0, 150)) . '...</p>';
                        echo '</div>';
                    }
                } else {
                    if ($search) {
                        echo '<p>No threads found matching your search.</p>';
                    } else {
                        echo '<p>No threads have been created yet.</p>';
                    }
                }
            } catch(PDOException $e) {
                echo '<p>Error: ' . $e->getMessage() . '</p>';
            }
            ?>
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