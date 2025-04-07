<?php
session_start();
require_once('protected/config.php');

// Set page title
$pageTitle = 'Dashboard';
$additionalStyles = '';
$additionalScripts = '<script src="scripts/dashboardscript.js"></script><script src="scripts/bookscript.js"></script>';

// Include header
include('includes/header.php');
?>

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
                        echo '<p><strong>' . htmlspecialchars($row['borrower']) . '</strong> has borrowed <strong>' . htmlspecialchars($row['title']) . '</strong></p>';
                        echo '</div>';
                        echo '</div>';
                    }
                } else {
                    echo '<p>None of your books are currently borrowed.</p>';
                }
            } catch(PDOException $e) {
                echo '<p>Error: ' . $e->getMessage() . '</p>';
            }
            ?>
        </div>
        
        <h3>Your Borrowed Books</h3>
        <div class="your-borrowed-books">
            <?php
            try {
                $sql = "SELECT b.*, br.request_id, br.created_at, br.updated_at, u.username as owner
                        FROM books b
                        JOIN book_requests br ON b.book_id = br.book_id
                        JOIN users u ON b.username = u.username
                        WHERE br.requester_username = :username AND b.status = 'borrowed' AND br.status = 'accepted'
                        ORDER BY br.created_at DESC";
                $stmt = $pdo->prepare($sql);
                $stmt->bindParam(':username', $_SESSION['username'], PDO::PARAM_STR);
                $stmt->execute();
                
                if ($stmt->rowCount() > 0) {
                    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                        echo '<div class="request-item">';
                        echo '<div class="request-info">';
                        echo '<p>You are borrowing <strong>' . htmlspecialchars($row['title']) . '</strong> from <strong>' . htmlspecialchars($row['owner']) . '</strong></p>';
                        echo '<p>Borrowed on: ' . date('F j, Y', strtotime($row['updated_at'])) . '</p>';
                        echo '</div>';
                        echo '<div class="request-actions">';
                        echo '<form action="return_book.php" method="post">';
                        echo '<input type="hidden" name="book_id" value="' . $row['book_id'] . '">';
                        echo '<button type="submit" class="return-button">Return Book</button>';
                        echo '</form>';
                        echo '</div>';
                        echo '</div>';
                    }
                } else {
                    echo '<p>You are not currently borrowing any books.</p>';
                }
            } catch(PDOException $e) {
                echo '<p>Error: ' . $e->getMessage() . '</p>';
            }
            ?>
        </div>
        
        <h3>Your Book Requests</h3>
        <div class="your-requests">
            <?php
            try {
                $sql = "SELECT br.*, b.title, u.username as owner
                        FROM book_requests br
                        JOIN books b ON br.book_id = b.book_id
                        JOIN users u ON b.username = u.username
                        WHERE br.requester_username = :username AND br.status != 'returned'
                        ORDER BY br.created_at DESC";
                $stmt = $pdo->prepare($sql);
                $stmt->bindParam(':username', $_SESSION['username'], PDO::PARAM_STR);
                $stmt->execute();
                
                if ($stmt->rowCount() > 0) {
                    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                        echo '<div class="request-item">';
                        echo '<div class="request-info">';
                        echo '<p>You have requested to borrow <strong>' . htmlspecialchars($row['title']) . '</strong> from <strong>' . htmlspecialchars($row['owner']) . '</strong></p>';
                        echo '<p class="request-date">Requested on: ' . date('F j, Y', strtotime($row['created_at'])) . '</p>';
                        
                        // Show response date if request has been responded to
                        if (isset($row['updated_at']) && $row['status'] != 'pending') {
                            echo '<p>Response on: ' . date('F j, Y', strtotime($row['updated_at'])) . '</p>';
                        }
                        
                        // Show status
                        echo '<p>Status: <strong>' . ucfirst(htmlspecialchars($row['status'])) . '</strong></p>';
                        echo '</div>';
                        echo '</div>';
                    }
                } else {
                    echo '<p>You have not requested any books.</p>';
                }
            } catch(PDOException $e) {
                echo '<p>Error: ' . $e->getMessage() . '</p>';
            }
            ?>
        </div>
    <?php endif; ?>
</div>

<?php
// Include footer
include('includes/footer.php');
?>