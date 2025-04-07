<?php
session_start();
require_once('protected/config.php');

// Set page title
$pageTitle = 'Admin Dashboard';
$additionalStyles = '';
$additionalScripts = '<script src="scripts/adminscript.js"></script>';

// Check if user is logged in and is an admin
if (!isset($_SESSION['username']) || !isset($_SESSION['type']) || $_SESSION['type'] !== 'admin') {
    header("Location: login.php");
    exit;
}

// Include header
include('includes/header.php');
?>

<div class="main-container">
    <div class="breadcrumb">
        <a href="home.php">Home</a> / <span>Admin Dashboard</span>
    </div>
    
    <h2 style="color: #f44336; border-bottom: 2px solid #f44336; padding-bottom: 10px;">Admin Dashboard</h2>
    
    <?php if (isset($_SESSION['success_message'])): ?>
        <div class="success-message"><?php echo $_SESSION['success_message']; ?></div>
        <?php unset($_SESSION['success_message']); ?>
    <?php endif; ?>
    
    <?php if (isset($_SESSION['error_message'])): ?>
        <div class="error-message"><?php echo $_SESSION['error_message']; ?></div>
        <?php unset($_SESSION['error_message']); ?>
    <?php endif; ?>
    
    <div class="admin-tabs">
        <button class="tab-link active" data-tab="users">User Management</button>
        <button class="tab-link" data-tab="books">Book Management</button>
        <button class="tab-link" data-tab="threads">Discussion Management</button>
        <button class="tab-link" data-tab="reports">Reported Content</button>
        <button class="tab-link" data-tab="content-search">Content Search</button>
    </div>
    
    <div id="users" class="tab-content active">
        <h3 class="admin-section-heading">User Management</h3>
        
        <div class="search-container">
            <form id="user-search-form">
                <div class="form-group">
                    <input type="text" id="user-search" name="search" placeholder="Search by username, email, or name...">
                    <button type="submit" class="submit">Search</button>
                </div>
            </form>
        </div>
        
        <div id="user-results">
            <!-- User search results will be displayed here -->
            <?php
            try {
                $pdo = new PDO(DBCONNSTRING, DBUSER, DBPASS);
                $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                
                // Get all users
                $sql = "SELECT username, email, firstname, lastname, type, profilepic, 
                       (SELECT COUNT(*) FROM books WHERE books.username = users.username) as book_count, 
                       (SELECT COUNT(*) FROM comments WHERE comments.username = users.username) as comment_count
                       FROM users
                       ORDER BY username";
                $stmt = $pdo->prepare($sql);
                $stmt->execute();
                
                if ($stmt->rowCount() > 0) {
                    echo '<div class="user-table-container">';
                    echo '<table class="user-table">';
                    echo '<thead>';
                    echo '<tr>';
                    echo '<th>Profile</th>';
                    echo '<th>Username</th>';
                    echo '<th>Email</th>';
                    echo '<th>Name</th>';
                    echo '<th>Type</th>';
                    echo '<th>Books</th>';
                    echo '<th>Comments</th>';
                    echo '<th>Actions</th>';
                    echo '</tr>';
                    echo '</thead>';
                    echo '<tbody>';
                    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                        $fullName = trim($row['firstname'] . ' ' . $row['lastname']);
                        
                        echo '<tr>';
                        echo '<td><img src="' . htmlspecialchars($row['profilepic']) . '" alt="Profile" class="small-profile-pic"></td>';
                        echo '<td>' . htmlspecialchars($row['username']) . '</td>';
                        echo '<td>' . htmlspecialchars($row['email']) . '</td>';
                        echo '<td>' . (!empty($fullName) ? htmlspecialchars($fullName) : '<em>Not provided</em>') . '</td>';
                        echo '<td>' . htmlspecialchars($row['type']) . '</td>';
                        echo '<td>' . $row['book_count'] . '</td>';
                        echo '<td>' . $row['comment_count'] . '</td>';
                        echo '<td class="actions-column">';
                        echo '<a href="user_detail.php?username=' . urlencode($row['username']) . '" class="view-button">View</a>';
                        
                        if ($row['username'] !== $_SESSION['username']) {
                            if ($row['type'] === 'user') {
                                echo '<form action="admin_actions.php" method="post" class="inline-form">';
                                echo '<input type="hidden" name="action" value="promote_admin">';
                                echo '<input type="hidden" name="username" value="' . htmlspecialchars($row['username']) . '">';
                                echo '<button type="submit" class="promote-button">Make Admin</button>';
                                echo '</form>';
                            } else {
                                echo '<form action="admin_actions.php" method="post" class="inline-form">';
                                echo '<input type="hidden" name="action" value="demote_user">';
                                echo '<input type="hidden" name="username" value="' . htmlspecialchars($row['username']) . '">';
                                echo '<button type="submit" class="demote-button">Remove Admin</button>';
                                echo '</form>';
                            }
                            
                            echo '<form action="admin_actions.php" method="post" class="inline-form">';
                            echo '<input type="hidden" name="action" value="delete_user">';
                            echo '<input type="hidden" name="username" value="' . htmlspecialchars($row['username']) . '">';
                            echo '<button type="submit" class="delete-button" onclick="return confirm(\'Are you sure you want to delete this user? All their content will be deleted. This action cannot be undone.\')">Delete</button>';
                            echo '</form>';
                        } else {
                            echo '<span class="current-user-marker">(You)</span>';
                        }
                        
                        echo '</td>';
                        echo '</tr>';
                    }
                    echo '</tbody>';
                    echo '</table>';
                    echo '</div>';
                } else {
                    echo '<p>No users found.</p>';
                }
            } catch(PDOException $e) {
                echo '<p>Error: ' . $e->getMessage() . '</p>';
            }
            ?>
        </div>
    </div>
    
    <div id="books" class="tab-content">
        <h3 class="admin-section-heading">Book Management</h3>
        <p>You can search and manage all book listings here.</p>
        
        <div class="search-container">
            <form id="book-search-form">
                <div class="form-group">
                    <input type="text" id="book-search" name="search" placeholder="Search by title, author, or category...">
                    <button type="submit" class="submit">Search</button>
                </div>
            </form>
        </div>
        
        <div id="book-results">
            <!-- Book search results will be displayed here -->
            <p>Enter a search term to find books or leave empty to view all books.</p>
        </div>
    </div>
    
    <div id="threads" class="tab-content">
        <h3 class="admin-section-heading">Discussion Management</h3>
        <p>You can manage all discussion threads here.</p>
        
        <div class="search-container">
            <form id="thread-admin-search-form">
                <div class="form-group">
                    <input type="text" id="thread-admin-search" name="search" placeholder="Search by title or content...">
                    <button type="submit" class="submit">Search</button>
                </div>
            </form>
        </div>
        
        <div id="thread-admin-results">
            <!-- Thread search results will be displayed here -->
            <p>Enter a search term to find discussion threads or leave empty to view all threads.</p>
        </div>
    </div>
    
    <div id="content-search" class="tab-content">
        <h3 class="admin-section-heading">Content Search</h3>
        <p>Search across all content types (books, threads, comments).</p>
        
        <div class="search-container">
            <form id="content-search-form" onsubmit="return false;">
                <div class="form-group">
                    <input type="text" id="content-search-input" name="search" placeholder="Search books, threads, and comments...">
                    <button type="button" class="submit" onclick="searchContent(document.getElementById('content-search-input').value);">Search</button>
                </div>
            </form>
        </div>
        
        <div id="content-search-summary" class="search-summary" style="display:none">
            <p>Found <span id="total-results">0</span> results: 
               <span id="book-results-count">0</span> books, 
               <span id="thread-results-count">0</span> threads, 
               <span id="comment-results-count">0</span> comments
            </p>
        </div>
        
        <div id="content-search-results">
            <!-- Content search results will be displayed here -->
            <p>Enter a search term to find content across the site.</p>
        </div>
    </div>
    
    <div id="reports" class="tab-content">
        <h3 class="admin-section-heading">Reported Content</h3>
        <p>View and handle reported content here.</p>
        
        <style>
            .report-filters {
                display: flex;
                margin-bottom: 20px;
                gap: 10px;
            }
            .report-filter {
                padding: 8px 15px;
                background-color: #f1f1f1;
                border: 1px solid #ddd;
                border-radius: 4px;
                cursor: pointer;
                transition: all 0.3s;
                text-decoration: none;
                color: #333;
                display: inline-block;
            }
            .report-filter.active {
                background-color: #f44336;
                color: white;
                border-color: #d32f2f;
            }
            .report-filter:hover:not(.active) {
                background-color: #ddd;
            }
            
            /* Status colors */
            .status-pending {
                color: #ff9800;
                font-weight: bold;
            }
            .status-resolved {
                color: #4CAF50;
                font-weight: bold;
            }
            .status-dismissed {
                color: #9e9e9e;
                font-weight: bold;
            }
        </style>
        
        <div class="report-filters">
            <a href="javascript:void(0);" class="report-filter active" data-status="all">All Reports</a>
            <a href="javascript:void(0);" class="report-filter" data-status="pending">Pending</a>
            <a href="javascript:void(0);" class="report-filter" data-status="resolved">Resolved</a>
            <a href="javascript:void(0);" class="report-filter" data-status="dismissed">Dismissed</a>
        </div>
        
        <div id="report-results">
            <div class="loading-indicator">Loading reports...</div>
            
            <?php
            // Fallback display of reports if AJAX fails
            try {
                $pdo = new PDO(DBCONNSTRING, DBUSER, DBPASS);
                $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                
                $sql = "SELECT * FROM reports ORDER BY created_at DESC";
                $stmt = $pdo->prepare($sql);
                $stmt->execute();
                
                if ($stmt->rowCount() > 0) {
                    echo '<div id="fallback-reports" style="display:none;">';
                    echo '<h4>PHP Fallback Report Display</h4>';
                    echo '<p><em>Note: This is a server-rendered fallback. AJAX loading failed.</em></p>';
                    echo '<table class="report-table">';
                    echo '<thead>';
                    echo '<tr>';
                    echo '<th>ID</th>';
                    echo '<th>Content</th>';
                    echo '<th>Reporter</th>';
                    echo '<th>Reason</th>';
                    echo '<th>Status</th>';
                    echo '<th>Date</th>';
                    echo '</tr>';
                    echo '</thead>';
                    echo '<tbody>';
                    
                    while ($report = $stmt->fetch(PDO::FETCH_ASSOC)) {
                        $contentTypeLabel = ucfirst($report['content_type']);
                        $statusClass = 'status-' . $report['status'];
                        $statusLabel = ucfirst($report['status']);
                        
                        echo '<tr>';
                        echo '<td>' . $report['report_id'] . '</td>';
                        echo '<td>' . htmlspecialchars($contentTypeLabel) . ' #' . $report['content_id'] . '</td>';
                        echo '<td>' . htmlspecialchars($report['reporter_username']) . '</td>';
                        echo '<td>' . htmlspecialchars($report['reason']) . '</td>';
                        echo '<td class="' . $statusClass . '">' . $statusLabel . '</td>';
                        echo '<td>' . date('M j, Y g:i A', strtotime($report['created_at'])) . '</td>';
                        echo '</tr>';
                    }
                    
                    echo '</tbody>';
                    echo '</table>';
                    echo '</div>';
                    
                    // Add JavaScript to show fallback after a timeout
                    echo '<script>
                        setTimeout(function() {
                            var loadingElem = document.querySelector("#report-results .loading-indicator");
                            var fallbackElem = document.getElementById("fallback-reports");
                            
                            if (loadingElem && fallbackElem && loadingElem.parentNode === document.getElementById("report-results")) {
                                fallbackElem.style.display = "block";
                                console.log("Showing fallback reports display");
                            }
                        }, 5000); // Show fallback after 5 seconds if AJAX hasn't completed
                    </script>';
                }
            } catch (PDOException $e) {
                // Silently fail - we're just a fallback
            }
            ?>
        </div>
        
        <p style="margin-top: 20px; font-size: 0.9em;">
            <a href="test_reports.php" target="_blank" style="color: #666;">Troubleshoot Reports</a>
        </p>
        
        <!-- Report details modal -->
        <div id="report-details-modal" class="modal">
            <div class="modal-content">
                <span class="close">&times;</span>
                <h3>Report Details</h3>
                <div id="report-detail-content">
                    <!-- Report details will be loaded here -->
                </div>
            </div>
        </div>
    </div>
</div>

<?php
// Include footer
include('includes/footer.php');
?> 