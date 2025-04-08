<?php
session_start();
require_once('protected/config.php');

// Set appropriate headers
header('Content-Type: text/html');

// Check if user is admin
$isAdmin = isset($_SESSION['type']) && $_SESSION['type'] === 'admin';
if (!$isAdmin) {
    echo "<p>Admin access required. <a href='login.php'>Login as admin</a></p>";
    exit;
}

// Get the action from query string
$action = isset($_GET['action']) ? $_GET['action'] : '';
$status = isset($_GET['status']) ? $_GET['status'] : 'all';

// Connect to DB
try {
    $pdo = new PDO(DBCONNSTRING, DBUSER, DBPASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Session info
echo "<h1>API Test Page</h1>";
echo "<h2>Session Information</h2>";
echo "<ul>";
echo "<li>Username: " . ($_SESSION['username'] ?? 'Not set') . "</li>";
echo "<li>User Type: " . ($_SESSION['type'] ?? 'Not set') . "</li>";
echo "</ul>";

// Test form
echo "<h2>Test API Call</h2>";
echo "<form method='get'>";
echo "<select name='action'>";
echo "<option value='get_reports'" . ($action == 'get_reports' ? " selected" : "") . ">Get Reports</option>";
echo "<option value='get_content_overview'" . ($action == 'get_content_overview' ? " selected" : "") . ">Content Overview</option>";
echo "<option value='search_users'" . ($action == 'search_users' ? " selected" : "") . ">Search Users</option>";
echo "<option value='search_books'" . ($action == 'search_books' ? " selected" : "") . ">Search Books</option>";
echo "<option value='search_threads'" . ($action == 'search_threads' ? " selected" : "") . ">Search Threads</option>";
echo "</select>";

// Status parameter for reports
echo "<div id='status-selector' style='" . ($action != 'get_reports' ? "display:none;" : "") . "margin-top: 10px;'>";
echo "<label>Status: </label>";
echo "<select name='status'>";
echo "<option value='all'" . ($status == 'all' ? " selected" : "") . ">All</option>";
echo "<option value='pending'" . ($status == 'pending' ? " selected" : "") . ">Pending</option>";
echo "<option value='resolved'" . ($status == 'resolved' ? " selected" : "") . ">Resolved</option>";
echo "<option value='dismissed'" . ($status == 'dismissed' ? " selected" : "") . ">Dismissed</option>";
echo "</select>";
echo "</div>";

// Search term for searches
echo "<div id='search-term' style='" . (!in_array($action, ['search_users', 'search_books', 'search_threads']) ? "display:none;" : "") . "margin-top: 10px;'>";
echo "<label>Search Term: </label>";
echo "<input type='text' name='search' value='" . (isset($_GET['search']) ? htmlspecialchars($_GET['search']) : "") . "'>";
echo "</div>";

echo "<button type='submit' style='margin-top: 10px;'>Test API Call</button>";
echo "</form>";

echo "<script>
document.querySelector('select[name=\"action\"]').addEventListener('change', function() {
    document.getElementById('status-selector').style.display = this.value === 'get_reports' ? 'block' : 'none';
    document.getElementById('search-term').style.display = ['search_users', 'search_books', 'search_threads'].includes(this.value) ? 'block' : 'none';
});
</script>";

// Process test if action is set
if ($action) {
    echo "<h2>API Response</h2>";
    
    // Build API URL
    $apiUrl = "admin_handler.php?action=$action";
    
    // Add parameters based on action
    if ($action === 'get_reports') {
        $apiUrl .= "&status=$status";
    } else if (in_array($action, ['search_users', 'search_books', 'search_threads']) && isset($_GET['search'])) {
        $apiUrl .= "&search=" . urlencode($_GET['search']);
    }
    
    echo "<p>Calling: <code>$apiUrl</code></p>";
    
    // Make the direct database call to avoid issues with AJAX
    echo "<h3>Direct Database Query Results</h3>";
    try {
        if ($action === 'get_reports') {
            // Get reports
            $whereClause = $status !== 'all' ? "WHERE status = :status" : "";
            $sql = "SELECT r.*, 
                        CASE 
                            WHEN r.content_type = 'book' THEN (SELECT title FROM books WHERE book_id = r.content_id) 
                            WHEN r.content_type = 'thread' THEN (SELECT title FROM threads WHERE thread_id = r.content_id)
                            WHEN r.content_type = 'comment' THEN CONCAT('Comment #', r.content_id)
                            ELSE CONCAT(r.content_type, ' #', r.content_id)
                        END AS content_title
                    FROM reports r
                    $whereClause
                    ORDER BY r.created_at DESC";
                    
            $stmt = $pdo->prepare($sql);
            
            if ($status !== 'all') {
                $stmt->bindParam(':status', $status);
            }
            
            $stmt->execute();
            $reports = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            if (count($reports) > 0) {
                echo "<table border='1' cellpadding='5' style='border-collapse: collapse; width: 100%;'>";
                echo "<tr><th>ID</th><th>Content</th><th>Reporter</th><th>Reason</th><th>Status</th><th>Date</th></tr>";
                
                foreach ($reports as $report) {
                    echo "<tr>";
                    echo "<td>" . $report['report_id'] . "</td>";
                    echo "<td>" . htmlspecialchars($report['content_title']) . "</td>";
                    echo "<td>" . htmlspecialchars($report['reporter_username']) . "</td>";
                    echo "<td>" . htmlspecialchars($report['reason']) . "</td>";
                    echo "<td>" . htmlspecialchars($report['status']) . "</td>";
                    echo "<td>" . $report['created_at'] . "</td>";
                    echo "</tr>";
                }
                
                echo "</table>";
            } else {
                echo "<p>No reports found.</p>";
            }
        } else if ($action === 'get_content_overview') {
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
            
            echo "<h4>Latest Books (" . count($books) . ")</h4>";
            if (count($books) > 0) {
                echo "<table border='1' cellpadding='5' style='border-collapse: collapse; width: 100%;'>";
                echo "<tr><th>ID</th><th>Title</th><th>User</th><th>Date</th></tr>";
                
                foreach ($books as $book) {
                    echo "<tr>";
                    echo "<td>" . $book['book_id'] . "</td>";
                    echo "<td>" . htmlspecialchars($book['title']) . "</td>";
                    echo "<td>" . htmlspecialchars($book['username']) . "</td>";
                    echo "<td>" . $book['created_at'] . "</td>";
                    echo "</tr>";
                }
                
                echo "</table>";
            } else {
                echo "<p>No books found.</p>";
            }
            
            echo "<h4>Latest Threads (" . count($threads) . ")</h4>";
            if (count($threads) > 0) {
                echo "<table border='1' cellpadding='5' style='border-collapse: collapse; width: 100%;'>";
                echo "<tr><th>ID</th><th>Title</th><th>User</th><th>Date</th></tr>";
                
                foreach ($threads as $thread) {
                    echo "<tr>";
                    echo "<td>" . $thread['thread_id'] . "</td>";
                    echo "<td>" . htmlspecialchars($thread['title']) . "</td>";
                    echo "<td>" . htmlspecialchars($thread['username']) . "</td>";
                    echo "<td>" . $thread['created_at'] . "</td>";
                    echo "</tr>";
                }
                
                echo "</table>";
            } else {
                echo "<p>No threads found.</p>";
            }
            
            echo "<h4>Latest Comments (" . count($comments) . ")</h4>";
            if (count($comments) > 0) {
                echo "<table border='1' cellpadding='5' style='border-collapse: collapse; width: 100%;'>";
                echo "<tr><th>ID</th><th>Content</th><th>User</th><th>Thread</th><th>Date</th></tr>";
                
                foreach ($comments as $comment) {
                    echo "<tr>";
                    echo "<td>" . $comment['comment_id'] . "</td>";
                    echo "<td>" . htmlspecialchars(substr($comment['content'], 0, 50)) . (strlen($comment['content']) > 50 ? '...' : '') . "</td>";
                    echo "<td>" . htmlspecialchars($comment['username']) . "</td>";
                    echo "<td>" . htmlspecialchars($comment['thread_title']) . "</td>";
                    echo "<td>" . $comment['created_at'] . "</td>";
                    echo "</tr>";
                }
                
                echo "</table>";
            } else {
                echo "<p>No comments found.</p>";
            }
        }
    } catch (PDOException $e) {
        echo "<p>Error: " . $e->getMessage() . "</p>";
    }
    
    // Make the API call
    echo "<h3>API Response Data</h3>";
    $ch = curl_init($apiUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    echo "<p>HTTP Status: $httpCode</p>";
    
    if ($response) {
        $prettyPrintJson = json_encode(json_decode($response), JSON_PRETTY_PRINT);
        echo "<pre style='background-color: #f5f5f5; padding: 10px; overflow: auto; max-height: 500px;'>";
        echo htmlspecialchars($prettyPrintJson);
        echo "</pre>";
    } else {
        echo "<p>No response received.</p>";
    }
}
?>

<style>
body {
    font-family: Arial, sans-serif;
    max-width: 1200px;
    margin: 0 auto;
    padding: 20px;
}

h1, h2, h3, h4 {
    color: #333;
}

form {
    background-color: #f5f5f5;
    padding: 15px;
    border-radius: 5px;
    margin-bottom: 20px;
}

select, button, input {
    padding: 8px;
    margin-right: 10px;
}

table {
    width: 100%;
    border-collapse: collapse;
    margin-bottom: 20px;
}

th {
    background-color: #e0e0e0;
    text-align: left;
}

tr:nth-child(even) {
    background-color: #f9f9f9;
}

td, th {
    padding: 8px;
    border: 1px solid #ddd;
}
</style> 