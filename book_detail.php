<?php
session_start();
require_once('protected/config.php');

$page_title = 'Book Details';
$additional_scripts = '';

include 'includes/header.php';
?>

<div class="main-container">
    <?php
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
        
        // Book actions section
        echo '<div class="book-actions-section">';
        
        // Request book button - only show for regular users, not admins
        if (isset($_SESSION['username']) && (!isset($_SESSION['type']) || $_SESSION['type'] !== 'admin')) {
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
        } elseif (isset($_SESSION['type']) && $_SESSION['type'] === 'admin') {
            echo '<div class="admin-actions">';
            echo '<form action="admin_actions.php" method="post" class="inline-form">';
            echo '<input type="hidden" name="action" value="delete_book">';
            echo '<input type="hidden" name="book_id" value="' . $book['book_id'] . '">';
            echo '<button type="submit" class="delete-button" onclick="return confirm(\'Are you sure you want to delete this book? This action cannot be undone.\')">Delete Book</button>';
            echo '</form>';
            echo '</div>';
        } else {
            echo '<p><a href="login.php">Log in</a> to request this book.</p>';
        }
        
        // Add report link if user is logged in
        if (isset($_SESSION['username'])) {
            echo '<div class="book-report-section">';
            echo '<a href="#" class="report-link" data-toggle="modal" data-target="#reportModal" ';
            echo 'data-type="book" data-id="' . $book['book_id'] . '">Report Book</a>';
            echo '</div>';
        }
        
        echo '</div>'; // End book-actions-section
        
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

<!-- Report Modal -->
<?php if (isset($_SESSION['username'])): ?>
<div id="reportModal" class="modal">
    <div class="modal-content">
        <span class="close">&times;</span>
        <h3>Report Content</h3>
        <form action="report_content.php" method="post">
            <input type="hidden" id="content_type" name="content_type" value="">
            <input type="hidden" id="content_id" name="content_id" value="">
            <input type="hidden" id="return_url" name="return_url" value="<?php echo $_SERVER['REQUEST_URI']; ?>">
            
            <div class="form-group">
                <label for="reason">Reason:</label>
                <select id="reason" name="reason" required>
                    <option value="">Select a reason</option>
                    <option value="inappropriate">Inappropriate content</option>
                    <option value="spam">Spam</option>
                    <option value="harassment">Harassment</option>
                    <option value="misinformation">Misinformation</option>
                    <option value="other">Other</option>
                </select>
            </div>
            
            <div class="form-group">
                <label for="details">Additional details (optional):</label>
                <textarea id="details" name="details" rows="4"></textarea>
            </div>
            
            <div class="form-buttons">
                <button type="submit" class="submit">Submit Report</button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Get the modal
    var modal = document.getElementById('reportModal');
    
    // Get the <span> element that closes the modal
    var span = modal.querySelector('.close');
    
    // When the user clicks on <span> (x), close the modal
    span.onclick = function() {
        modal.style.display = 'none';
    }
    
    // When the user clicks anywhere outside of the modal, close it
    window.onclick = function(event) {
        if (event.target == modal) {
            modal.style.display = 'none';
        }
    }
    
    // Set up report links
    var reportLinks = document.querySelectorAll('.report-link');
    reportLinks.forEach(function(link) {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            var contentType = this.getAttribute('data-type');
            var contentId = this.getAttribute('data-id');
            
            document.getElementById('content_type').value = contentType;
            document.getElementById('content_id').value = contentId;
            
            modal.style.display = 'block';
        });
    });
});
</script>
<?php endif; ?>

<?php include 'includes/footer.php'; ?> 