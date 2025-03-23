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
                <a href="threads.php"><button>Discussions</button></a>
            </nav>
        </div>
    </header>

<div class="main-container">
    <h2>Browse Books</h2>
    <h3>Search</h3>
        <form id="search-form" method="get" action="browse.php" novalidate>
            <div class="form-group">
                <input type="text" id="search" name="search" placeholder="Enter a book title or category." 
                       value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
                <button type="submit" class="submit">Search</button>
            </div>
            <div class="filter-options">
                <select name="category" id="category-filter">
                    <option value="">All Categories</option>
                    <option value="non-fiction" <?php echo (isset($_GET['category']) && $_GET['category'] == 'non-fiction') ? 'selected' : ''; ?>>Non-Fiction</option>
                    <option value="fantasy" <?php echo (isset($_GET['category']) && $_GET['category'] == 'fantasy') ? 'selected' : ''; ?>>Fantasy</option>
                    <option value="sci-fi" <?php echo (isset($_GET['category']) && $_GET['category'] == 'sci-fi') ? 'selected' : ''; ?>>Sci-Fi</option>
                    <option value="romance" <?php echo (isset($_GET['category']) && $_GET['category'] == 'romance') ? 'selected' : ''; ?>>Romance</option>
                    <option value="mystery" <?php echo (isset($_GET['category']) && $_GET['category'] == 'mystery') ? 'selected' : ''; ?>>Mystery</option>
                    <option value="horror" <?php echo (isset($_GET['category']) && $_GET['category'] == 'horror') ? 'selected' : ''; ?>>Horror</option>
                </select>
                <select name="status" id="status-filter">
                    <option value="">All Statuses</option>
                    <option value="available" <?php echo (isset($_GET['status']) && $_GET['status'] == 'available') ? 'selected' : ''; ?>>Available</option>
                    <option value="borrowed" <?php echo (isset($_GET['status']) && $_GET['status'] == 'borrowed') ? 'selected' : ''; ?>>Borrowed</option>
                    <option value="reserved" <?php echo (isset($_GET['status']) && $_GET['status'] == 'reserved') ? 'selected' : ''; ?>>Reserved</option>
                </select>
            </div>
        </form>
    
    <div class="books-container">
        <?php
        require_once('protected/config.php');
        try {
            $pdo = new PDO(DBCONNSTRING, DBUSER, DBPASS);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            // Build query based on search parameters
            $params = [];
            $whereConditions = [];
            
            $sql = "SELECT b.*, u.username FROM books b 
                    JOIN users u ON b.username = u.username";
            
            // Search term
            if (isset($_GET['search']) && !empty($_GET['search'])) {
                $searchTerm = "%" . $_GET['search'] . "%";
                $whereConditions[] = "(b.title LIKE :search OR b.description LIKE :search)";
                $params[':search'] = $searchTerm;
            }
            
            // Category filter
            if (isset($_GET['category']) && !empty($_GET['category'])) {
                $whereConditions[] = "b.category = :category";
                $params[':category'] = $_GET['category'];
            }
            
            // Status filter
            if (isset($_GET['status']) && !empty($_GET['status'])) {
                $whereConditions[] = "b.status = :status";
                $params[':status'] = $_GET['status'];
            }
            
            // Add WHERE clause if conditions exist
            if (!empty($whereConditions)) {
                $sql .= " WHERE " . implode(' AND ', $whereConditions);
            }
            
            // Order by newest first
            $sql .= " ORDER BY b.created_at DESC";
            
            $stmt = $pdo->prepare($sql);
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }
            $stmt->execute();
            
            // Display results
            if ($stmt->rowCount() > 0) {
                echo '<div class="book-grid">';
                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    echo '<div class="book-item">';
                    
                    // Book cover image
                    if ($row['cover_image']) {
                        echo '<img src="' . htmlspecialchars($row['cover_image']) . '" alt="' . htmlspecialchars($row['title']) . '" class="book-cover">';
                    } else {
                        echo '<img src="images/book.PNG" alt="Default cover" class="book-cover">';
                    }
                    
                    // Book details
                    echo '<div class="book-details">';
                    echo '<h4>' . htmlspecialchars($row['title']) . '</h4>';
                    echo '<p class="book-owner">Owner: ' . htmlspecialchars($row['username']) . '</p>';
                    echo '<p class="book-category">Category: ' . htmlspecialchars($row['category']) . '</p>';
                    echo '<p class="book-status">Status: ' . htmlspecialchars($row['status']) . '</p>';
                    echo '<p class="book-description">' . htmlspecialchars(substr($row['description'], 0, 150)) . 
                         (strlen($row['description']) > 150 ? '...' : '') . '</p>';
                    echo '<div class="book-actions">';
                    echo '<a href="book_detail.php?id=' . $row['book_id'] . '" class="book-view">View Details</a>';
                    echo '</div>';
                    echo '</div>';
                    
                    echo '</div>';
                }
                echo '</div>';
            } else {
                echo '<p>No books found matching your criteria.</p>';
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