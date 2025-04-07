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
    
    <style>
        /* Error message styling */
        .error-message {
            background-color: #ffebee;
            border: 1px solid #f44336;
            color: #c62828;
            padding: 10px 15px;
            border-radius: 4px;
            margin: 10px 0;
        }
        
        /* Retry button styling */
        .retry-button {
            background-color: #f44336;
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            margin-top: 10px;
        }
        
        .retry-button:hover {
            background-color: #d32f2f;
        }
        
        /* Loading indicator */
        .loading-indicator {
            text-align: center;
            padding: 20px;
            color: #666;
            font-style: italic;
        }
        
        /* Table styling improvements */
        .user-table, .book-table, .thread-table, .report-table, .content-search-table, .overview-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
            background-color: white;
            border: 1px solid #aaa;
            box-shadow: 0 2px 5px rgba(0,0,0,0.15);
        }
        
        .user-table th, .book-table th, .thread-table th, .report-table th, .content-search-table th, .overview-table th {
            background-color: #e0e0e0;
            padding: 10px;
            text-align: left;
            border-bottom: 2px solid #bbb;
            position: sticky;
            top: 0;
            color: #333;
            font-weight: bold;
        }
        
        .user-table td, .book-table td, .thread-table td, .report-table td, .content-search-table td, .overview-table td {
            padding: 8px 10px;
            border-bottom: 1px solid #ccc;
            color: #333;
        }
        
        .user-table tr:hover, .book-table tr:hover, .thread-table tr:hover, .report-table tr:hover, .content-search-table tr:hover, .overview-table tr:hover {
            background-color: #f0f0f0;
        }
        
        /* Alternate row colors for better readability */
        .user-table tr:nth-child(even), 
        .book-table tr:nth-child(even), 
        .thread-table tr:nth-child(even), 
        .report-table tr:nth-child(even), 
        .content-search-table tr:nth-child(even),
        .overview-table tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        
        /* Action buttons styling */
        .view-button, .resolve-button, .dismiss-button, .promote-button, .demote-button {
            padding: 5px 10px;
            margin-right: 5px;
            border: none;
            border-radius: 3px;
            cursor: pointer;
            font-size: 12px;
            display: inline-block;
            text-decoration: none;
            text-align: center;
        }
        
        .view-button {
            background-color: #2196F3;
            color: white;
        }
        
        .resolve-button {
            background-color: #4CAF50;
            color: white;
        }
        
        .dismiss-button {
            background-color: #9e9e9e;
            color: white;
        }
        
        .promote-button {
            background-color: #673AB7;
            color: white;
        }
        
        .demote-button {
            background-color: #FF9800;
            color: white;
        }
        
        .delete-button {
            background-color: #f44336;
            color: white;
            padding: 5px 10px;
            margin-right: 5px;
            border: none;
            border-radius: 3px;
            cursor: pointer;
            font-size: 12px;
        }
        
        .view-button:hover { background-color: #0b7dda; }
        .resolve-button:hover { background-color: #388E3C; }
        .dismiss-button:hover { background-color: #757575; }
        .promote-button:hover { background-color: #5E35B1; }
        .demote-button:hover { background-color: #F57C00; }
        .delete-button:hover { background-color: #d32f2f; }
        
        /* Table container styles */
        .user-table-container, 
        .book-table-container, 
        .thread-table-container, 
        .report-table-container,
        .content-search-table-container {
            max-height: 600px;
            overflow-y: auto;
            border: 1px solid #ccc;
            border-radius: 4px;
            background: white;
            margin-bottom: 20px;
        }
        
        /* Add table header styling */
        .user-table thead tr, 
        .book-table thead tr, 
        .thread-table thead tr, 
        .report-table thead tr, 
        .content-search-table thead tr,
        .overview-table thead tr {
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        /* Add outline to table headers for visual separation */
        .user-table th, 
        .book-table th, 
        .thread-table th, 
        .report-table th, 
        .content-search-table th,
        .overview-table th {
            border-right: 1px solid #bbb;
        }
        
        .user-table th:last-child, 
        .book-table th:last-child, 
        .thread-table th:last-child, 
        .report-table th:last-child, 
        .content-search-table th:last-child,
        .overview-table th:last-child {
            border-right: none;
        }
        
        /* Action buttons container */
        .actions-column {
            white-space: nowrap;
            min-width: 180px;
            background-color: rgba(249, 249, 249, 0.7);
        }
        
        /* Inline form styling for action buttons */
        .inline-form {
            display: inline-block;
            margin-right: 3px;
        }
    </style>
    
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
        <button class="tab-link" data-tab="content-search">Content Overview</button>
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
        <h3 class="admin-section-heading">Content Overview</h3>
        <p>Overview of recent content across the site.</p>
        
        <style>
            .content-overview {
                margin-top: 20px;
            }
            
            .overview-section {
                margin-bottom: 30px;
            }
            
            .content-section {
                background-color: #f9f9f9;
                border: 1px solid #ddd;
                border-radius: 5px;
                padding: 15px;
                margin-bottom: 20px;
            }
            
            .content-section h5 {
                margin-top: 0;
                padding-bottom: 10px;
                border-bottom: 1px solid #eee;
                color: #333;
            }
            
            .overview-table {
                width: 100%;
                border-collapse: collapse;
            }
            
            .overview-table th {
                background-color: #f5f5f5;
                padding: 8px;
                text-align: left;
                border-bottom: 2px solid #ddd;
            }
            
            .overview-table td {
                padding: 8px;
                border-bottom: 1px solid #eee;
            }
            
            .overview-table tr:hover {
                background-color: #f1f1f1;
            }
        </style>
        
        <div id="content-search-summary" class="search-summary" style="display:none">
            <p>Content stats: <span id="total-results">0</span> items total: 
               <span id="book-results-count">0</span> books, 
               <span id="thread-results-count">0</span> threads, 
               <span id="comment-results-count">0</span> comments
            </p>
        </div>
        
        <div id="content-search-results">
            <div class="loading-indicator">Loading content overview...</div>
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
                    echo "<script>
                        setTimeout(function() {
                            var loadingElem = document.querySelector('#report-results .loading-indicator');
                            var fallbackElem = document.getElementById('fallback-reports');
                            
                            if (loadingElem && fallbackElem && loadingElem.parentNode === document.getElementById('report-results')) {
                                fallbackElem.style.display = 'block';
                                console.log('Showing fallback reports display');
                            }
                        }, 5000); // Show fallback after 5 seconds if AJAX hasn't completed
                    </script>";
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

<script>
document.addEventListener('DOMContentLoaded', function() {
    // If tab hash is content-search, load the content overview
    if (window.location.hash === '#content-search') {
        setTimeout(() => {
            searchContent('');
        }, 300);
    }
    
    // Also load content overview when that tab is clicked
    document.querySelector('[data-tab="content-search"]').addEventListener('click', function() {
        setTimeout(() => {
            searchContent('');
        }, 100);
    });
});
</script>

<?php
// Include footer
include('includes/footer.php');
?> 