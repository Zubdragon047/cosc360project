<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>COSC360 Project - Book Exchange</title>
    <link rel="stylesheet" href="css/stylesheet.css">
    <script src="scripts/dashboardscript.js"></script>
    <script src="scripts/bookscript.js"></script>
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
    <?php if (!isset($_SESSION['username'])): ?>
        <h3 style="color:red">Must be logged in to use dashboard.</h3>
    <?php else: ?>
        <?php if (isset($_SESSION['success_message'])): ?>
            <div class="success-message"><?php echo $_SESSION['success_message']; ?></div>
            <?php unset($_SESSION['success_message']); ?>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['error_message'])): ?>
            <div class="error-message"><?php echo $_SESSION['error_message']; ?></div>
            <?php unset($_SESSION['error_message']); ?>
        <?php endif; ?>
        
        <h3>Add Listing</h3>
        <form id="add-book-form" method="post"
                action="book_form.php"
                enctype="multipart/form-data"
                novalidate>
                <div class="form-group">
                    <label for="book-title">Title</label>
                    <input type="text" id="book-title" name="book-title" placeholder="Enter book title." required>
                    <span id="title-error-message"></span>
                </div>
                <div class="form-group">
                    <label for="book-description">Description</label>
                    <textarea id="book-description" name="book-description" placeholder="Enter book description." required></textarea>
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
        <div class="book-listings">
            <?php
            try {
                require_once('protected/config.php');
                $pdo = new PDO(DBCONNSTRING, DBUSER, DBPASS);
                $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                
                $sql = "SELECT * FROM books WHERE username = :username ORDER BY created_at DESC";
                $stmt = $pdo->prepare($sql);
                $stmt->bindParam(':username', $_SESSION['username'], PDO::PARAM_STR);
                $stmt->execute();
                
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
                    echo '<p>You have not added any books yet.</p>';
                }
            } catch(PDOException $e) {
                echo '<p>Error: ' . $e->getMessage() . '</p>';
            }
            ?>
        </div>
        
        <h3>Book Requests</h3>
        <div class="book-requests">
            <?php
            try {
                $sql = "SELECT br.*, b.title, u.username as requester
                        FROM book_requests br
                        JOIN books b ON br.book_id = b.book_id
                        JOIN users u ON br.requester_username = u.username
                        WHERE b.username = :username AND br.status = 'pending'
                        ORDER BY br.created_at DESC";
                $stmt = $pdo->prepare($sql);
                $stmt->bindParam(':username', $_SESSION['username'], PDO::PARAM_STR);
                $stmt->execute();
                
                if ($stmt->rowCount() > 0) {
                    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                        echo '<div class="request-item">';
                        echo '<div class="request-info">';
                        echo '<p><strong>' . htmlspecialchars($row['requester']) . '</strong> has requested to borrow <strong>' . htmlspecialchars($row['title']) . '</strong></p>';
                        echo '<p class="request-date">Requested on: ' . date('F j, Y', strtotime($row['created_at'])) . '</p>';
                        echo '</div>';
                        echo '<div class="request-actions">';
                        echo '<form action="process_request.php" method="post">';
                        echo '<input type="hidden" name="request_id" value="' . $row['request_id'] . '">';
                        echo '<input type="hidden" name="action" value="accept">';
                        echo '<button type="submit" class="accept-button">Accept</button>';
                        echo '</form>';
                        echo '<form action="process_request.php" method="post">';
                        echo '<input type="hidden" name="request_id" value="' . $row['request_id'] . '">';
                        echo '<input type="hidden" name="action" value="decline">';
                        echo '<button type="submit" class="decline-button">Decline</button>';
                        echo '</form>';
                        echo '</div>';
                        echo '</div>';
                    }
                } else {
                    echo '<p>You have no pending book requests.</p>';
                }
            } catch(PDOException $e) {
                echo '<p>Error: ' . $e->getMessage() . '</p>';
            }
            ?>
        </div>
        
        <h3>Borrowed Books</h3>
        <div class="borrowed-books">
            <?php
            try {
                $sql = "SELECT b.*, u.username as borrower
                        FROM books b
                        JOIN book_requests br ON b.book_id = br.book_id
                        JOIN users u ON br.requester_username = u.username
                        WHERE b.username = :username AND b.status = 'borrowed' AND br.status = 'accepted'
                        ORDER BY br.created_at DESC";
                $stmt = $pdo->prepare($sql);
                $stmt->bindParam(':username', $_SESSION['username'], PDO::PARAM_STR);
                $stmt->execute();
                
                if ($stmt->rowCount() > 0) {
                    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                        echo '<div class="request-item">';
                        echo '<div class="request-info">';
                        echo '<p><strong>' . htmlspecialchars($row['title']) . '</strong> is currently borrowed by <strong>' . htmlspecialchars($row['borrower']) . '</strong></p>';
                        echo '</div>';
                        echo '<div class="request-actions">';
                        echo '<form action="return_book.php" method="post">';
                        echo '<input type="hidden" name="book_id" value="' . $row['book_id'] . '">';
                        echo '<button type="submit" class="return-button">Mark as Returned</button>';
                        echo '</form>';
                        echo '</div>';
                        echo '</div>';
                    }
                } else {
                    echo '<p>You have no books currently borrowed by others.</p>';
                }
            } catch(PDOException $e) {
                echo '<p>Error: ' . $e->getMessage() . '</p>';
            }
            ?>
        </div>
        
        <h3>My Requested Books</h3>
        <div class="my-requests">
            <?php
            try {
                $sql = "SELECT br.*, b.title, b.cover_image, u.username as owner
                        FROM book_requests br
                        JOIN books b ON br.book_id = b.book_id
                        JOIN users u ON b.username = u.username
                        WHERE br.requester_username = :username
                        ORDER BY br.created_at DESC";
                $stmt = $pdo->prepare($sql);
                $stmt->bindParam(':username', $_SESSION['username'], PDO::PARAM_STR);
                $stmt->execute();
                
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
                        echo '<p>Owner: ' . htmlspecialchars($row['owner']) . '</p>';
                        echo '<p>Status: ';
                        
                        switch ($row['status']) {
                            case 'pending':
                                echo '<span style="color: #ff9800;">Request Pending</span>';
                                break;
                            case 'accepted':
                                echo '<span style="color: #4CAF50;">Request Accepted</span>';
                                break;
                            case 'declined':
                                echo '<span style="color: #f44336;">Request Declined</span>';
                                break;
                            case 'returned':
                                echo '<span style="color: #2196F3;">Returned</span>';
                                break;
                            default:
                                echo htmlspecialchars($row['status']);
                        }
                        
                        echo '</p>';
                        echo '<p>Requested on: ' . date('F j, Y', strtotime($row['created_at'])) . '</p>';
                        
                        if (isset($row['updated_at']) && $row['status'] != 'pending') {
                            echo '<p>Response on: ' . date('F j, Y', strtotime($row['updated_at'])) . '</p>';
                        }
                        
                        echo '<div class="book-actions">';
                        echo '<a href="book_detail.php?id=' . $row['book_id'] . '" class="book-view">View Details</a>';
                        echo '</div>';
                        echo '</div>';
                        
                        echo '</div>';
                    }
                    echo '</div>';
                } else {
                    echo '<p>You have not requested any books yet.</p>';
                }
            } catch(PDOException $e) {
                echo '<p>Error: ' . $e->getMessage() . '</p>';
            }
            ?>
        </div>
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