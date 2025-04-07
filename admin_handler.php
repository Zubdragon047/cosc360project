<?php
session_start();
require_once('protected/config.php');
header('Content-Type: application/json');

// Check if user is logged in and is an admin
if (!isset($_SESSION['username']) || !isset($_SESSION['type']) || $_SESSION['type'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

// Connect to database
try {
    $pdo = new PDO(DBCONNSTRING, DBUSER, DBPASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Get action parameter
    $action = isset($_GET['action']) ? $_GET['action'] : '';
    
    switch ($action) {
        case 'search_users':
            $searchTerm = isset($_GET['search']) ? '%' . $_GET['search'] . '%' : '';
            
            if (empty($searchTerm) || $searchTerm === '%%') {
                // If no search term, return most recent users
                $sql = "SELECT username, email, firstname, lastname, type, profilepic, 
                       (SELECT COUNT(*) FROM books WHERE books.username = users.username) as book_count,
                       (SELECT COUNT(*) FROM comments WHERE comments.username = users.username) as comment_count
                       FROM users 
                       ORDER BY username
                       LIMIT 20";
                $stmt = $pdo->prepare($sql);
                $stmt->execute();
            } else {
                // Search for users
                $sql = "SELECT username, email, firstname, lastname, type, profilepic, 
                       (SELECT COUNT(*) FROM books WHERE books.username = users.username) as book_count,
                       (SELECT COUNT(*) FROM comments WHERE comments.username = users.username) as comment_count
                       FROM users 
                       WHERE username LIKE :search 
                       OR email LIKE :search 
                       OR firstname LIKE :search 
                       OR lastname LIKE :search
                       ORDER BY username
                       LIMIT 50";
                $stmt = $pdo->prepare($sql);
                $stmt->bindParam(':search', $searchTerm, PDO::PARAM_STR);
                $stmt->execute();
            }
            
            $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Process user data for display
            $processedUsers = [];
            foreach ($users as $user) {
                $fullName = trim($user['firstname'] . ' ' . $user['lastname']);
                $processedUsers[] = [
                    'username' => $user['username'],
                    'email' => $user['email'],
                    'name' => !empty($fullName) ? $fullName : '-',
                    'type' => $user['type'],
                    'profilepic' => $user['profilepic'],
                    'book_count' => $user['book_count'],
                    'comment_count' => $user['comment_count'],
                    'html' => generateUserRow($user)
                ];
            }
            
            echo json_encode(['success' => true, 'users' => $processedUsers]);
            break;
            
        case 'search_books':
            $searchTerm = isset($_GET['search']) ? '%' . $_GET['search'] . '%' : '';
            
            if (empty($searchTerm) || $searchTerm === '%%') {
                // If no search term, return most recent books
                $sql = "SELECT b.*, u.username 
                       FROM books b
                       JOIN users u ON b.username = u.username
                       ORDER BY b.created_at DESC
                       LIMIT 20";
                $stmt = $pdo->prepare($sql);
                $stmt->execute();
            } else {
                // Search for books
                $sql = "SELECT b.*, u.username 
                       FROM books b
                       JOIN users u ON b.username = u.username
                       WHERE b.title LIKE :search 
                       OR b.category LIKE :search
                       OR b.status LIKE :search 
                       OR b.description LIKE :search
                       OR b.username LIKE :search
                       ORDER BY b.created_at DESC
                       LIMIT 50";
                $stmt = $pdo->prepare($sql);
                $stmt->bindParam(':search', $searchTerm, PDO::PARAM_STR);
                $stmt->execute();
            }
            
            $books = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Process books for display
            $processedBooks = [];
            foreach ($books as $book) {
                $processedBooks[] = [
                    'book_id' => $book['book_id'],
                    'title' => $book['title'],
                    'category' => $book['category'],
                    'status' => $book['status'],
                    'username' => $book['username'],
                    'cover_image' => $book['cover_image'],
                    'html' => generateBookRow($book)
                ];
            }
            
            echo json_encode(['success' => true, 'books' => $processedBooks]);
            break;
            
        case 'search_threads':
            $searchTerm = isset($_GET['search']) ? '%' . $_GET['search'] . '%' : '';
            
            if (empty($searchTerm) || $searchTerm === '%%') {
                // If no search term, return most recent threads
                $sql = "SELECT t.*, u.username, 
                        (SELECT COUNT(*) FROM comments c WHERE c.thread_id = t.thread_id) as comment_count
                        FROM threads t
                        JOIN users u ON t.username = u.username
                        ORDER BY t.created_at DESC
                        LIMIT 20";
                $stmt = $pdo->prepare($sql);
                $stmt->execute();
            } else {
                // Search for threads
                $sql = "SELECT t.*, u.username, 
                        (SELECT COUNT(*) FROM comments c WHERE c.thread_id = t.thread_id) as comment_count
                        FROM threads t
                        JOIN users u ON t.username = u.username
                        WHERE t.title LIKE :search 
                        OR t.content LIKE :search
                        OR t.username LIKE :search
                        ORDER BY t.created_at DESC
                        LIMIT 50";
                $stmt = $pdo->prepare($sql);
                $stmt->bindParam(':search', $searchTerm, PDO::PARAM_STR);
                $stmt->execute();
            }
            
            $threads = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Process threads for display
            $processedThreads = [];
            foreach ($threads as $thread) {
                $processedThreads[] = [
                    'thread_id' => $thread['thread_id'],
                    'title' => $thread['title'],
                    'username' => $thread['username'],
                    'created_at' => $thread['created_at'],
                    'comment_count' => $thread['comment_count'],
                    'html' => generateThreadRow($thread)
                ];
            }
            
            echo json_encode(['success' => true, 'threads' => $processedThreads]);
            break;
            
        case 'get_reports':
            // Get status filter if provided
            $statusFilter = isset($_GET['status']) ? $_GET['status'] : 'all';
            
            // Prepare query based on status filter
            if ($statusFilter === 'all') {
                $sql = "SELECT r.*, u.username as reporter_name
                        FROM reports r
                        JOIN users u ON r.reporter_username = u.username
                        ORDER BY r.created_at DESC";
                $stmt = $pdo->prepare($sql);
                $stmt->execute();
            } else {
                $sql = "SELECT r.*, u.username as reporter_name
                        FROM reports r
                        JOIN users u ON r.reporter_username = u.username
                        WHERE r.status = :status
                        ORDER BY r.created_at DESC";
                $stmt = $pdo->prepare($sql);
                $stmt->bindParam(':status', $statusFilter);
                $stmt->execute();
            }
            
            $reports = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Process reports for display
            $processedReports = [];
            foreach ($reports as $report) {
                $processedReports[] = [
                    'report_id' => $report['report_id'],
                    'content_type' => $report['content_type'],
                    'content_id' => $report['content_id'],
                    'reporter' => $report['reporter_username'],
                    'reason' => $report['reason'],
                    'details' => $report['details'],
                    'status' => $report['status'],
                    'created_at' => $report['created_at'],
                    'html' => generateReportRow($report)
                ];
            }
            
            echo json_encode(['success' => true, 'reports' => $processedReports]);
            break;
            
        default:
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
            break;
    }
} catch(PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    exit;
}

// Function to generate HTML for a user row
function generateUserRow($user) {
    $fullName = trim($user['firstname'] . ' ' . $user['lastname']);
    $html = '<tr>';
    $html .= '<td><img src="' . htmlspecialchars($user['profilepic']) . '" alt="Profile" class="small-profile-pic"></td>';
    $html .= '<td>' . htmlspecialchars($user['username']) . '</td>';
    $html .= '<td>' . htmlspecialchars($user['email']) . '</td>';
    $html .= '<td>' . (!empty($fullName) ? htmlspecialchars($fullName) : '-') . '</td>';
    $html .= '<td>' . htmlspecialchars($user['type']) . '</td>';
    $html .= '<td>' . $user['book_count'] . '</td>';
    $html .= '<td>' . $user['comment_count'] . '</td>';
    $html .= '<td class="actions-column">';
    $html .= '<a href="user_detail.php?username=' . urlencode($user['username']) . '" class="view-button">View</a>';
    
    if ($user['username'] !== $_SESSION['username']) {
        if ($user['type'] === 'user') {
            $html .= '<form action="admin_actions.php" method="post" class="inline-form">';
            $html .= '<input type="hidden" name="action" value="promote_admin">';
            $html .= '<input type="hidden" name="username" value="' . htmlspecialchars($user['username']) . '">';
            $html .= '<button type="submit" class="promote-button">Make Admin</button>';
            $html .= '</form>';
        } else {
            $html .= '<form action="admin_actions.php" method="post" class="inline-form">';
            $html .= '<input type="hidden" name="action" value="demote_user">';
            $html .= '<input type="hidden" name="username" value="' . htmlspecialchars($user['username']) . '">';
            $html .= '<button type="submit" class="demote-button">Remove Admin</button>';
            $html .= '</form>';
        }
        
        $html .= '<form action="admin_actions.php" method="post" class="inline-form">';
        $html .= '<input type="hidden" name="action" value="delete_user">';
        $html .= '<input type="hidden" name="username" value="' . htmlspecialchars($user['username']) . '">';
        $html .= '<button type="submit" class="delete-button" onclick="return confirm(\'Are you sure you want to delete this user? All their content will be deleted. This action cannot be undone.\')">Delete</button>';
        $html .= '</form>';
    } else {
        $html .= '<span class="current-user-marker">(You)</span>';
    }
    
    $html .= '</td>';
    $html .= '</tr>';
    return $html;
}

// Function to generate HTML for a book row
function generateBookRow($book) {
    $html = '<tr>';
    $html .= '<td>';
    if (!empty($book['cover_image'])) {
        $html .= '<img src="' . htmlspecialchars($book['cover_image']) . '" alt="Cover" class="small-book-cover">';
    } else {
        $html .= '<img src="images/book.PNG" alt="Default cover" class="small-book-cover">';
    }
    $html .= '</td>';
    $html .= '<td>' . htmlspecialchars($book['title']) . '</td>';
    $html .= '<td>' . htmlspecialchars($book['category']) . '</td>';
    $html .= '<td>' . htmlspecialchars($book['status']) . '</td>';
    $html .= '<td>' . htmlspecialchars($book['username']) . '</td>';
    $html .= '<td class="actions-column">';
    $html .= '<a href="book_detail.php?id=' . $book['book_id'] . '" class="view-button">View</a>';
    $html .= '<form action="admin_actions.php" method="post" class="inline-form">';
    $html .= '<input type="hidden" name="action" value="delete_book">';
    $html .= '<input type="hidden" name="book_id" value="' . $book['book_id'] . '">';
    $html .= '<button type="submit" class="delete-button" onclick="return confirm(\'Are you sure you want to delete this book? This action cannot be undone.\')">Delete</button>';
    $html .= '</form>';
    $html .= '</td>';
    $html .= '</tr>';
    return $html;
}

// Function to generate HTML for a thread row
function generateThreadRow($thread) {
    $html = '<tr>';
    $html .= '<td>' . htmlspecialchars($thread['title']) . '</td>';
    $html .= '<td>' . htmlspecialchars($thread['username']) . '</td>';
    $html .= '<td>' . date('M j, Y g:i A', strtotime($thread['created_at'])) . '</td>';
    $html .= '<td>' . $thread['comment_count'] . '</td>';
    $html .= '<td class="actions-column">';
    $html .= '<a href="thread.php?id=' . $thread['thread_id'] . '" class="view-button">View</a>';
    $html .= '<form action="admin_actions.php" method="post" class="inline-form">';
    $html .= '<input type="hidden" name="action" value="delete_thread">';
    $html .= '<input type="hidden" name="thread_id" value="' . $thread['thread_id'] . '">';
    $html .= '<button type="submit" class="delete-button" onclick="return confirm(\'Are you sure you want to delete this thread? All comments will also be deleted. This action cannot be undone.\')">Delete</button>';
    $html .= '</form>';
    $html .= '</td>';
    $html .= '</tr>';
    return $html;
}

// Function to generate HTML for a report row
function generateReportRow($report) {
    $contentTypeLabel = ucfirst($report['content_type']);
    $statusClass = 'status-' . $report['status'];
    $statusLabel = ucfirst($report['status']);
    
    $html = '<tr>';
    $html .= '<td>' . htmlspecialchars($contentTypeLabel) . ' #' . $report['content_id'] . '</td>';
    $html .= '<td>' . htmlspecialchars($report['reporter_username']) . '</td>';
    $html .= '<td>' . htmlspecialchars($report['reason']) . '</td>';
    $html .= '<td class="' . $statusClass . '">' . $statusLabel . '</td>';
    $html .= '<td>' . date('M j, Y g:i A', strtotime($report['created_at'])) . '</td>';
    $html .= '<td class="actions-column">';
    
    // View button
    $html .= '<button class="view-report-button" data-report-id="' . $report['report_id'] . '">View Details</button>';
    
    // Status update buttons based on current status
    if ($report['status'] === 'pending') {
        $html .= '<form action="admin_actions.php" method="post" class="inline-form">';
        $html .= '<input type="hidden" name="action" value="resolve_report">';
        $html .= '<input type="hidden" name="report_id" value="' . $report['report_id'] . '">';
        $html .= '<button type="submit" class="resolve-button">Resolve</button>';
        $html .= '</form>';
        
        $html .= '<form action="admin_actions.php" method="post" class="inline-form">';
        $html .= '<input type="hidden" name="action" value="dismiss_report">';
        $html .= '<input type="hidden" name="report_id" value="' . $report['report_id'] . '">';
        $html .= '<button type="submit" class="dismiss-button">Dismiss</button>';
        $html .= '</form>';
    }
    
    $html .= '</td>';
    $html .= '</tr>';
    
    return $html;
}

// Helper function to create status badges
function getStatusBadge($status) {
    switch($status) {
        case 'pending':
            return '<span class="status-badge pending">Pending</span>';
        case 'resolved':
            return '<span class="status-badge resolved">Resolved</span>';
        case 'dismissed':
            return '<span class="status-badge dismissed">Dismissed</span>';
        default:
            return '<span class="status-badge">' . htmlspecialchars($status) . '</span>';
    }
}

// Helper function to get thread ID for a comment
function getThreadIdForComment($commentId) {
    global $pdo;
    try {
        $sql = "SELECT thread_id FROM comments WHERE comment_id = :comment_id";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':comment_id', $commentId);
        $stmt->execute();
        
        if ($row = $stmt->fetch()) {
            return $row['thread_id'];
        }
        return 0;
    } catch(PDOException $e) {
        return 0;
    }
}
?> 