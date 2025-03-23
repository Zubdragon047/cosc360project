<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>COSC360 Project - Book Details</title>
    <link rel="stylesheet" href="css/stylesheet.css">
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
        <?php
        require_once('protected/config.php');
        
        // Check if book ID is provided
        if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
            echo '<div class="error-message">Invalid book ID.</div>';
            echo '<p><a href="browse.php">Return to Browse</a></p>';
            exit;
        }
        
        $bookId = $_GET['id'];
        
        try {
            $pdo = new PDO(DBCONNSTRING, DBUSER, DBPASS);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            // Get book details
            $sql = "SELECT b.*, u.username FROM books b 
                    JOIN users u ON b.username = u.username
                    WHERE b.book_id = :bookId";
            
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':bookId', $bookId);
            $stmt->execute();
            
            if ($stmt->rowCount() == 0) {
                echo '<div class="error-message">Book not found.</div>';
                echo '<p><a href="browse.php">Return to Browse</a></p>';
                exit;
            }
            
            $book = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Check if the current user has already requested this book
            $hasRequested = false;
            if (isset($_SESSION['username'])) {
                $sqlRequest = "SELECT * FROM book_requests 
                               WHERE book_id = :bookId AND requester_username = :username";
                $stmtRequest = $pdo->prepare($sqlRequest);
                $stmtRequest->bindValue(':bookId', $bookId);
                $stmtRequest->bindValue(':username', $_SESSION['username']);
                $stmtRequest->execute();
                $hasRequested = ($stmtRequest->rowCount() > 0);
            }
            
            // Display book details
            echo '<div class="book-detail">';
            echo '<h2>' . htmlspecialchars($book['title']) . '</h2>';
            
            echo '<div class="book-detail-container">';
            
            // Left column with image
            echo '<div class="book-detail-image">';
            if ($book['cover_image']) {
                echo '<img src="' . htmlspecialchars($book['cover_image']) . '" alt="' . 
                    htmlspecialchars($book['title']) . '" class="book-cover-large">';
            } else {
                echo '<img src="images/book.PNG" alt="Default cover" class="book-cover-large">';
            }
            echo '</div>';
            
            // Right column with details
            echo '<div class="book-detail-info">';
            echo '<p><strong>Owner:</strong> ' . htmlspecialchars($book['username']) . '</p>';
            echo '<p><strong>Category:</strong> ' . htmlspecialchars($book['category']) . '</p>';
            echo '<p><strong>Status:</strong> ' . htmlspecialchars($book['status']) . '</p>';
            echo '<p><strong>Description:</strong></p>';
            echo '<div class="book-description-full">' . htmlspecialchars($book['description']) . '</div>';
            
            // Request book button
            if (isset($_SESSION['username'])) {
                // Don't show request button if the book belongs to the current user
                if ($_SESSION['username'] != $book['username']) {
                    echo '<div class="book-request-section">';
                    if ($book['status'] == 'available' && !$hasRequested) {
                        echo '<form action="request_book.php" method="post">';
                        echo '<input type="hidden" name="book_id" value="' . $book['book_id'] . '">';
                        echo '<button type="submit" class="request-button">Request Book</button>';
                        echo '</form>';
                    } elseif ($hasRequested) {
                        echo '<p class="request-status">You have already requested this book.</p>';
                    } else {
                        echo '<p class="request-status">This book is currently not available for request.</p>';
                    }
                    echo '</div>';
                }
            } else {
                echo '<p><a href="login.php">Log in</a> to request this book.</p>';
            }
            
            echo '</div>'; // End book-detail-info
            echo '</div>'; // End book-detail-container
            echo '</div>'; // End book-detail
            
        } catch(PDOException $e) {
            echo '<div class="error-message">Error: ' . $e->getMessage() . '</div>';
        }
        ?>
        <div class="back-link">
            <a href="browse.php">Back to Browse</a>
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