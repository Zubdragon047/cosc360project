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
        
        /* Report filters styling */
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
            color: #333;
            font-weight: bold;
            text-decoration: none;
            display: inline-block;
        }
        
        .report-filter.active {
            background-color: #f44336;
            color: white;
            border-color: #d32f2f;
        }
        
        .report-filter:hover:not(.active) {
            background-color: #ddd;
            text-decoration: none;
        }
        
        /* Status indicators */
        .status-indicator {
            padding: 3px 8px;
            border-radius: 3px;
            font-weight: bold;
            font-size: 12px;
        }
        
        .status-indicator.pending {
            background-color: #fff3cd;
            color: #856404;
        }
        
        .status-indicator.resolved {
            background-color: #d4edda;
            color: #155724;
        }
        
        .status-indicator.dismissed {
            background-color: #e2e3e5;
            color: #383d41;
        }
        
        /* Content type indicators */
        .content-type-indicator {
            display: inline-block;
            font-size: 11px;
            padding: 2px 5px;
            border-radius: 3px;
            margin-left: 5px;
            background-color: #e9ecef;
            color: #495057;
        }
        
        .content-type-indicator[data-type="book"] {
            background-color: #cfe2ff;
            color: #084298;
        }
        
        .content-type-indicator[data-type="thread"] {
            background-color: #d1e7dd;
            color: #0f5132;
        }
        
        .content-type-indicator[data-type="comment"] {
            background-color: #f8d7da;
            color: #842029;
        }
        
        /* Troubleshoot link */
        .troubleshoot-link {
            display: inline-block;
            margin-top: 15px;
            color: #6c757d;
            text-decoration: none;
            padding: 5px 10px;
            border: 1px solid #ced4da;
            border-radius: 4px;
            font-size: 14px;
        }
        
        .troubleshoot-link:hover {
            background-color: #f8f9fa;
            color: #495057;
        }

        /* Modal styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0,0,0,0.4);
        }

        .modal-content {
            background-color: #fefefe;
            margin: 5% auto;
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 5px;
            width: 80%;
            max-width: 800px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }

        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
        }

        .close:hover,
        .close:focus {
            color: black;
            text-decoration: none;
            cursor: pointer;
        }

        /* Report details styles */
        .report-details {
            padding: 15px;
            background-color: #f9f9f9;
            border-radius: 5px;
        }

        .report-details p {
            margin: 10px 0;
            line-height: 1.5;
        }

        .report-details-text {
            background-color: white;
            padding: 15px;
            border: 1px solid #eee;
            border-radius: 4px;
            margin: 10px 0 20px;
            max-height: 200px;
            overflow-y: auto;
        }

        .report-actions {
            display: flex;
            gap: 10px;
            margin-top: 20px;
            flex-wrap: wrap;
        }

        .content-type-indicator {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 3px;
            font-size: 13px;
            font-weight: bold;
        }

        .content-type-indicator.book {
            background-color: #e3f2fd;
            color: #1565c0;
        }

        .content-type-indicator.thread {
            background-color: #e8f5e9;
            color: #2e7d32;
        }

        .content-type-indicator.comment {
            background-color: #fff8e1;
            color: #ff8f00;
        }

        .status-indicator {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 3px;
            font-size: 13px;
            font-weight: bold;
        }

        .status-indicator.pending {
            background-color: #fff8e1;
            color: #ff8f00;
        }

        .status-indicator.resolved {
            background-color: #e8f5e9;
            color: #2e7d32;
        }

        .status-indicator.dismissed {
            background-color: #f5f5f5;
            color: #616161;
        }

        .loading {
            text-align: center;
            padding: 20px;
            color: #666;
        }

        .error-message {
            color: #d32f2f;
            background-color: #ffebee;
            padding: 15px;
            border-radius: 4px;
            margin: 10px 0;
        }

        .view-content-btn {
            display: inline-block;
            background-color: #2196F3;
            color: white;
            padding: 8px 15px;
            text-decoration: none;
            border-radius: 4px;
            font-weight: bold;
        }

        .view-content-btn:hover {
            background-color: #0b7dda;
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
        
        <?php
        // Server-side content overview (fallback)
        try {
            $pdo = new PDO(DBCONNSTRING, DBUSER, DBPASS);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            // Get books
            $sql = "SELECT b.*, u.username 
                    FROM books b
                    JOIN users u ON b.username = u.username
                    ORDER BY b.created_at DESC
                    LIMIT 10";
            $stmt = $pdo->prepare($sql);
            $stmt->execute();
            $books = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Get threads
            $sql = "SELECT t.*, u.username
                    FROM threads t
                    JOIN users u ON t.username = u.username
                    ORDER BY t.created_at DESC
                    LIMIT 10";
            $stmt = $pdo->prepare($sql);
            $stmt->execute();
            $threads = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Get comments
            $sql = "SELECT c.*, u.username, t.title as thread_title
                    FROM comments c
                    JOIN users u ON c.username = u.username
                    JOIN threads t ON c.thread_id = t.thread_id
                    ORDER BY c.created_at DESC
                    LIMIT 10";
            $stmt = $pdo->prepare($sql);
            $stmt->execute();
            $comments = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // JavaScript to show the server-side content if AJAX fails
            echo "<script>
                setTimeout(function() {
                    if (document.querySelector('#content-search-results .loading-indicator')) {
                        document.getElementById('server-content-overview').style.display = 'block';
                        document.querySelector('#content-search-results .loading-indicator').style.display = 'none';
                    }
                }, 5000);
            </script>";
            ?>
            
            <div id="server-content-overview" style="display: none;">
                <div class="alert" style="background-color: #fff3cd; padding: 10px; border-radius: 5px; margin-bottom: 20px;">
                    <p>Server-rendered content overview shown after AJAX loading timeout.</p>
                </div>
                
                <div class="content-overview">
                    <div class="overview-section">
                        <h4>Recent Activity</h4>
                        
                        <!-- Books section -->
                        <div class="content-section">
                            <h5>Latest Books (<?php echo count($books); ?>)</h5>
                            <?php if (!empty($books)): ?>
                                <table class="overview-table">
                                    <thead>
                                        <tr>
                                            <th>Title</th>
                                            <th>User</th>
                                            <th>Date</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($books as $book): ?>
                                            <tr>
                                                <td><a href="book_detail.php?id=<?php echo $book['book_id']; ?>"><?php echo htmlspecialchars($book['title']); ?></a></td>
                                                <td><?php echo htmlspecialchars($book['username']); ?></td>
                                                <td><?php echo date('M j, Y g:i A', strtotime($book['created_at'])); ?></td>
                                                <td>
                                                    <a href="book_detail.php?id=<?php echo $book['book_id']; ?>" class="view-button">View</a>
                                                    <form action="admin_actions.php" method="post" style="display:inline;">
                                                        <input type="hidden" name="action" value="delete_book">
                                                        <input type="hidden" name="book_id" value="<?php echo $book['book_id']; ?>">
                                                        <input type="hidden" name="redirect" value="admin.php?status=<?php echo $statusFilter; ?>#reports">
                                                        <button type="submit" class="delete-button" onclick="return confirm('Are you sure you want to delete this book?')">Delete</button>
                                                    </form>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            <?php else: ?>
                                <p>No recent books found.</p>
                            <?php endif; ?>
                        </div>
                        
                        <!-- Threads section -->
                        <div class="content-section">
                            <h5>Latest Discussions (<?php echo count($threads); ?>)</h5>
                            <?php if (!empty($threads)): ?>
                                <table class="overview-table">
                                    <thead>
                                        <tr>
                                            <th>Title</th>
                                            <th>User</th>
                                            <th>Date</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($threads as $thread): ?>
                                            <tr>
                                                <td><a href="thread.php?id=<?php echo $thread['thread_id']; ?>"><?php echo htmlspecialchars($thread['title']); ?></a></td>
                                                <td><?php echo htmlspecialchars($thread['username']); ?></td>
                                                <td><?php echo date('M j, Y g:i A', strtotime($thread['created_at'])); ?></td>
                                                <td>
                                                    <a href="thread.php?id=<?php echo $thread['thread_id']; ?>" class="view-button">View</a>
                                                    <form action="admin_actions.php" method="post" style="display:inline;">
                                                        <input type="hidden" name="action" value="delete_thread">
                                                        <input type="hidden" name="thread_id" value="<?php echo $thread['thread_id']; ?>">
                                                        <input type="hidden" name="redirect" value="admin.php?status=<?php echo $statusFilter; ?>#reports">
                                                        <button type="submit" class="delete-button" onclick="return confirm('Are you sure you want to delete this thread? This will delete all comments.')">Delete</button>
                                                    </form>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            <?php else: ?>
                                <p>No recent discussions found.</p>
                            <?php endif; ?>
                        </div>
                        
                        <!-- Comments section -->
                        <div class="content-section">
                            <h5>Latest Comments (<?php echo count($comments); ?>)</h5>
                            <?php if (!empty($comments)): ?>
                                <table class="overview-table">
                                    <thead>
                                        <tr>
                                            <th>Comment</th>
                                            <th>User</th>
                                            <th>Thread</th>
                                            <th>Date</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($comments as $comment): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars(substr($comment['content'], 0, 50)) . (strlen($comment['content']) > 50 ? '...' : ''); ?></td>
                                                <td><?php echo htmlspecialchars($comment['username']); ?></td>
                                                <td><a href="thread.php?id=<?php echo $comment['thread_id']; ?>"><?php echo htmlspecialchars($comment['thread_title']); ?></a></td>
                                                <td><?php echo date('M j, Y g:i A', strtotime($comment['created_at'])); ?></td>
                                                <td>
                                                    <a href="thread.php?id=<?php echo $comment['thread_id']; ?>#comment-<?php echo $comment['comment_id']; ?>" class="view-button">View</a>
                                                    <form action="admin_actions.php" method="post" style="display:inline;">
                                                        <input type="hidden" name="action" value="delete_comment">
                                                        <input type="hidden" name="comment_id" value="<?php echo $comment['comment_id']; ?>">
                                                        <input type="hidden" name="redirect" value="admin.php?status=<?php echo $statusFilter; ?>#reports">
                                                        <button type="submit" class="delete-button" onclick="return confirm('Are you sure you want to delete this comment?')">Delete</button>
                                                    </form>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            <?php else: ?>
                                <p>No recent comments found.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            <?php
        } catch (PDOException $e) {
            error_log("Error in content overview: " . $e->getMessage());
        }
        ?>
    </div>
    
    <div id="reports" class="tab-content">
        <h3 class="admin-section-heading">Reported Content</h3>
        <p style="margin-bottom: 20px;">View and handle reported content here.</p>
        
        <?php
        // Get status filter from URL if it exists
        $statusFilter = isset($_GET['status']) ? $_GET['status'] : 'all';
        ?>
        
        <div class="report-filters">
            <a href="admin.php?status=all#reports" class="report-filter <?php echo $statusFilter === 'all' ? 'active' : ''; ?>" data-status="all">All Reports</a>
            <a href="admin.php?status=pending#reports" class="report-filter <?php echo $statusFilter === 'pending' ? 'active' : ''; ?>" data-status="pending">Pending</a>
            <a href="admin.php?status=resolved#reports" class="report-filter <?php echo $statusFilter === 'resolved' ? 'active' : ''; ?>" data-status="resolved">Resolved</a>
            <a href="admin.php?status=dismissed#reports" class="report-filter <?php echo $statusFilter === 'dismissed' ? 'active' : ''; ?>" data-status="dismissed">Dismissed</a>
        </div>
        
        <div id="report-results">
            <div class="loading-indicator">Loading reports...</div>
        </div>
        
        <?php
        // Server-side reports fallback
        try {
            $pdo = new PDO(DBCONNSTRING, DBUSER, DBPASS);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            // Get reports based on filter
            if ($statusFilter === 'all') {
                $sql = "SELECT r.*, 
                        CASE 
                            WHEN r.content_type = 'book' THEN (SELECT title FROM books WHERE book_id = r.content_id) 
                            WHEN r.content_type = 'thread' THEN (SELECT title FROM threads WHERE thread_id = r.content_id)
                            WHEN r.content_type = 'comment' THEN CONCAT('Comment #', r.content_id)
                            ELSE CONCAT(r.content_type, ' #', r.content_id)
                        END AS content_title
                    FROM reports r
                    ORDER BY r.created_at DESC";
                $stmt = $pdo->prepare($sql);
                $stmt->execute();
            } else {
                $sql = "SELECT r.*, 
                        CASE 
                            WHEN r.content_type = 'book' THEN (SELECT title FROM books WHERE book_id = r.content_id) 
                            WHEN r.content_type = 'thread' THEN (SELECT title FROM threads WHERE thread_id = r.content_id)
                            WHEN r.content_type = 'comment' THEN CONCAT('Comment #', r.content_id)
                            ELSE CONCAT(r.content_type, ' #', r.content_id)
                        END AS content_title
                    FROM reports r
                    WHERE r.status = :status
                    ORDER BY r.created_at DESC";
                $stmt = $pdo->prepare($sql);
                $stmt->bindParam(':status', $statusFilter, PDO::PARAM_STR);
                $stmt->execute();
            }
            
            $reports = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // JavaScript to show the server-side reports if AJAX fails
            echo "<script>
                setTimeout(function() {
                    if (document.querySelector('#report-results .loading-indicator')) {
                        document.getElementById('fallback-reports').style.display = 'block';
                        document.querySelector('#report-results .loading-indicator').style.display = 'none';
                    }
                }, 5000);
            </script>";
        ?>
        
        <div id="fallback-reports" style="display: none;">
            <div class="alert" style="background-color: #fff3cd; padding: 10px; border-radius: 5px; margin-bottom: 20px;">
                <p>Note: This is a server-rendered fallback. AJAX loading failed.</p>
            </div>
            
            <h3>PHP Fallback Report Display</h3>
            
            <?php if (count($reports) > 0): ?>
            <div class="report-table-container">
                <table class="report-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Content</th>
                            <th>Reporter</th>
                            <th>Reason</th>
                            <th>Status</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($reports as $report): ?>
                        <tr>
                            <td><?php echo $report['report_id']; ?></td>
                            <td>
                                <?php echo htmlspecialchars($report['content_title']); ?>
                                <div class="content-type-indicator" data-type="<?php echo $report['content_type']; ?>">
                                    <?php echo ucfirst($report['content_type']); ?>
                                </div>
                            </td>
                            <td><?php echo htmlspecialchars($report['reporter_username']); ?></td>
                            <td><?php echo htmlspecialchars($report['reason']); ?></td>
                            <td>
                                <span class="status-indicator <?php echo $report['status']; ?>">
                                    <?php echo ucfirst($report['status']); ?>
                                </span>
                            </td>
                            <td><?php echo date('M j, Y g:i A', strtotime($report['created_at'])); ?></td>
                            <td>
                                <?php if ($report['content_type'] === 'book'): ?>
                                <a href="book_detail.php?id=<?php echo $report['content_id']; ?>" class="view-button">View</a>
                                <?php elseif ($report['content_type'] === 'thread'): ?>
                                <a href="thread.php?id=<?php echo $report['content_id']; ?>" class="view-button">View</a>
                                <?php elseif ($report['content_type'] === 'comment'): ?>
                                <?php
                                // Get thread_id for the comment
                                $commentStmt = $pdo->prepare("SELECT thread_id FROM comments WHERE comment_id = ?");
                                $commentStmt->execute([$report['content_id']]);
                                $threadId = $commentStmt->rowCount() > 0 ? $commentStmt->fetch(PDO::FETCH_ASSOC)['thread_id'] : 0;
                                ?>
                                <a href="thread.php?id=<?php echo $threadId; ?>#comment-<?php echo $report['content_id']; ?>" class="view-button">View</a>
                                <?php endif; ?>
                                
                                <?php if ($report['status'] === 'pending'): ?>
                                <form action="admin_actions.php" method="post" class="inline-form">
                                    <input type="hidden" name="action" value="resolve_report">
                                    <input type="hidden" name="report_id" value="<?php echo $report['report_id']; ?>">
                                    <input type="hidden" name="redirect" value="admin.php?status=<?php echo $statusFilter; ?>#reports">
                                    <button type="submit" class="resolve-button">Resolve</button>
                                </form>
                                
                                <form action="admin_actions.php" method="post" class="inline-form">
                                    <input type="hidden" name="action" value="dismiss_report">
                                    <input type="hidden" name="report_id" value="<?php echo $report['report_id']; ?>">
                                    <input type="hidden" name="redirect" value="admin.php?status=<?php echo $statusFilter; ?>#reports">
                                    <button type="submit" class="dismiss-button">Dismiss</button>
                                </form>
                                <?php endif; ?>
                                
                                <?php
                                // Add delete content button based on content type
                                if ($report['content_type'] === 'book'): ?>
                                <form action="admin_actions.php" method="post" class="inline-form">
                                    <input type="hidden" name="action" value="delete_book">
                                    <input type="hidden" name="book_id" value="<?php echo $report['content_id']; ?>">
                                    <input type="hidden" name="redirect" value="admin.php?status=<?php echo $statusFilter; ?>#reports">
                                    <button type="submit" class="delete-button" onclick="return confirm('Are you sure you want to delete this book?')">Delete Book</button>
                                </form>
                                <?php elseif ($report['content_type'] === 'thread'): ?>
                                <form action="admin_actions.php" method="post" class="inline-form">
                                    <input type="hidden" name="action" value="delete_thread">
                                    <input type="hidden" name="thread_id" value="<?php echo $report['content_id']; ?>">
                                    <input type="hidden" name="redirect" value="admin.php?status=<?php echo $statusFilter; ?>#reports">
                                    <button type="submit" class="delete-button" onclick="return confirm('Are you sure you want to delete this thread?')">Delete Thread</button>
                                </form>
                                <?php elseif ($report['content_type'] === 'comment'): ?>
                                <form action="admin_actions.php" method="post" class="inline-form">
                                    <input type="hidden" name="action" value="delete_comment">
                                    <input type="hidden" name="comment_id" value="<?php echo $report['content_id']; ?>">
                                    <input type="hidden" name="redirect" value="admin.php?status=<?php echo $statusFilter; ?>#reports">
                                    <button type="submit" class="delete-button" onclick="return confirm('Are you sure you want to delete this comment?')">Delete Comment</button>
                                </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php else: ?>
            <p>No reports found.</p>
            <?php endif; ?>
            
            <a href="test_api.php?action=get_reports" target="_blank" class="troubleshoot-link">Troubleshoot Reports</a>
        </div>
        <?php
        } catch (PDOException $e) {
            error_log("Error in reports fallback: " . $e->getMessage());
        }
        ?>
    </div>

    <!-- Report Details Modal -->
    <div id="report-details-modal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h3>Report Details</h3>
            <div id="report-detail-content"></div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // If URL has status parameter, activate the reports tab
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.has('status')) {
        console.log('Status parameter found in URL, activating reports tab');
        // Select the reports tab
        const reportsTab = document.querySelector('[data-tab="reports"]');
        if (reportsTab) {
            // Select all tabs and remove active class
            const tabLinks = document.querySelectorAll('.tab-link');
            const tabContents = document.querySelectorAll('.tab-content');
            
            tabLinks.forEach(link => link.classList.remove('active'));
            tabContents.forEach(content => content.classList.remove('active'));
            
            // Activate the reports tab
            reportsTab.classList.add('active');
            document.getElementById('reports').classList.add('active');
            
            // Update hash
            window.location.hash = 'reports';
            
            // Scroll to the reports section
            document.getElementById('reports').scrollIntoView();
        }
    }
    
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
    
    // Set up JS URL handling for the filter links when using regular links for fallbacks
    // This ensures that if AJAX is working, we still use it properly
    const reportFilters = document.querySelectorAll('.report-filter');
    reportFilters.forEach(filter => {
        if (window.location.hash === '#reports') {
            filter.addEventListener('click', function(e) {
                if (typeof loadReports === 'function') {
                    e.preventDefault();
                    const status = this.getAttribute('data-status');
                    // Remove active class from all filters
                    reportFilters.forEach(f => f.classList.remove('active'));
                    // Add active class to current filter
                    this.classList.add('active');
                    loadReports(status);
                }
            });
        }
    });
});
</script>

<?php
// Include footer
include('includes/footer.php');
?> 