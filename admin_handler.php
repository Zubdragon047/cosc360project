<?php
session_start();
header('Content-Type: application/json');

require_once('protected/config.php');

// Enhanced error logging
error_log("-------- Admin Handler Request --------");
error_log("Request method: " . $_SERVER['REQUEST_METHOD']);
error_log("GET params: " . print_r($_GET, true));
error_log("Session username: " . (isset($_SESSION['username']) ? $_SESSION['username'] : 'not set'));
error_log("Session type: " . (isset($_SESSION['type']) ? $_SESSION['type'] : 'not set'));

// Check if user is logged in and is an admin
if (!isset($_SESSION['username']) || !isset($_SESSION['type']) || $_SESSION['type'] !== 'admin') {
    error_log("Unauthorized access attempt to admin_handler.php");
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

// Connect to database
try {
    $pdo = new PDO(DBCONNSTRING, DBUSER, DBPASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Get action parameter
    $action = isset($_GET['action']) ? $_GET['action'] : '';
    
    // Log the action for debugging
    error_log("Admin handler called with action: " . $action);
    
    switch ($action) {
        case 'get_content_overview':
            // Get recent content of each type
            try {
                $content = [
                    'books' => [],
                    'threads' => [],
                    'comments' => []
                ];
                
                // Get recent books
                $sql = "SELECT b.*, u.username 
                        FROM books b
                        JOIN users u ON b.username = u.username
                        ORDER BY b.created_at DESC
                        LIMIT 10";
                $stmt = $pdo->prepare($sql);
                $stmt->execute();
                $books = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                foreach ($books as $book) {
                    $content['books'][] = [
                        'book_id' => $book['book_id'],
                        'title' => $book['title'],
                        'username' => $book['username'],
                        'created_at' => $book['created_at'],
                        'category' => $book['category'],
                        'status' => $book['status']
                    ];
                }
                
                // Get recent threads
                $sql = "SELECT t.*, u.username
                        FROM threads t
                        JOIN users u ON t.username = u.username
                        ORDER BY t.created_at DESC
                        LIMIT 10";
                $stmt = $pdo->prepare($sql);
                $stmt->execute();
                $threads = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                foreach ($threads as $thread) {
                    $content['threads'][] = [
                        'thread_id' => $thread['thread_id'],
                        'title' => $thread['title'],
                        'username' => $thread['username'],
                        'created_at' => $thread['created_at']
                    ];
                }
                
                // Get recent comments
                $sql = "SELECT c.*, u.username, t.title as thread_title
                        FROM comments c
                        JOIN users u ON c.username = u.username
                        JOIN threads t ON c.thread_id = t.thread_id
                        ORDER BY c.created_at DESC
                        LIMIT 10";
                $stmt = $pdo->prepare($sql);
                $stmt->execute();
                $comments = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                foreach ($comments as $comment) {
                    $content['comments'][] = [
                        'comment_id' => $comment['comment_id'],
                        'thread_id' => $comment['thread_id'],
                        'thread_title' => $comment['thread_title'],
                        'content' => $comment['content'],
                        'username' => $comment['username'],
                        'created_at' => $comment['created_at']
                    ];
                }
                
                // Get content counts for summary
                $sql = "SELECT 
                        (SELECT COUNT(*) FROM books) as book_count,
                        (SELECT COUNT(*) FROM threads) as thread_count,
                        (SELECT COUNT(*) FROM comments) as comment_count";
                $stmt = $pdo->prepare($sql);
                $stmt->execute();
                $counts = $stmt->fetch(PDO::FETCH_ASSOC);
                
                $summary = [
                    'total' => $counts['book_count'] + $counts['thread_count'] + $counts['comment_count'],
                    'books' => $counts['book_count'],
                    'threads' => $counts['thread_count'],
                    'comments' => $counts['comment_count']
                ];
                
                echo json_encode([
                    'success' => true,
                    'content' => $content,
                    'summary' => $summary
                ]);
            } catch (PDOException $e) {
                error_log("Database error in get_content_overview: " . $e->getMessage());
                echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
            }
            break;
            
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
            
        case 'search_content':
            $searchTerm = isset($_GET['search']) ? $_GET['search'] : '';
            
            if (empty($searchTerm)) {
                echo json_encode(['success' => false, 'message' => 'No search term provided']);
                exit;
            }
            
            $wildcard = '%' . $searchTerm . '%';
            $results = [
                'books' => [],
                'threads' => [],
                'comments' => []
            ];
            
            // Search for books
            $sql = "SELECT b.*, u.username 
                    FROM books b
                    JOIN users u ON b.username = u.username
                    WHERE b.title LIKE :search 
                    OR b.description LIKE :search
                    LIMIT 50";
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':search', $wildcard, PDO::PARAM_STR);
            $stmt->execute();
            
            $books = $stmt->fetchAll(PDO::FETCH_ASSOC);
            foreach ($books as $book) {
                $results['books'][] = [
                    'book_id' => $book['book_id'],
                    'title' => $book['title'],
                    'description' => $book['description'],
                    'username' => $book['username'],
                    'created_at' => $book['created_at'],
                    'html' => generateContentSearchRow($book, 'book')
                ];
            }
            
            // Search for threads
            $sql = "SELECT t.*, u.username 
                    FROM threads t
                    JOIN users u ON t.username = u.username
                    WHERE t.title LIKE :search 
                    OR t.content LIKE :search
                    LIMIT 50";
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':search', $wildcard, PDO::PARAM_STR);
            $stmt->execute();
            
            $threads = $stmt->fetchAll(PDO::FETCH_ASSOC);
            foreach ($threads as $thread) {
                $results['threads'][] = [
                    'thread_id' => $thread['thread_id'],
                    'title' => $thread['title'],
                    'content' => $thread['content'],
                    'username' => $thread['username'],
                    'created_at' => $thread['created_at'],
                    'html' => generateContentSearchRow($thread, 'thread')
                ];
            }
            
            // Search for comments
            $sql = "SELECT c.*, u.username, t.title as thread_title, t.thread_id
                    FROM comments c
                    JOIN users u ON c.username = u.username
                    JOIN threads t ON c.thread_id = t.thread_id
                    WHERE c.content LIKE :search
                    LIMIT 50";
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':search', $wildcard, PDO::PARAM_STR);
            $stmt->execute();
            
            $comments = $stmt->fetchAll(PDO::FETCH_ASSOC);
            foreach ($comments as $comment) {
                $results['comments'][] = [
                    'comment_id' => $comment['comment_id'],
                    'thread_id' => $comment['thread_id'],
                    'thread_title' => $comment['thread_title'],
                    'content' => $comment['content'],
                    'username' => $comment['username'],
                    'created_at' => $comment['created_at'],
                    'html' => generateContentSearchRow($comment, 'comment')
                ];
            }
            
            // Prepare summary data
            $summary = [
                'total' => count($results['books']) + count($results['threads']) + count($results['comments']),
                'books' => count($results['books']),
                'threads' => count($results['threads']),
                'comments' => count($results['comments'])
            ];
            
            echo json_encode([
                'success' => true, 
                'results' => $results,
                'summary' => $summary
            ]);
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
            
            // Log for debugging
            error_log("Loading reports with status: " . $statusFilter);
            
            try {
                // Prepare query based on status filter
                if ($statusFilter === 'all') {
                    $sql = "SELECT r.*, u.username as reporter_name
                            FROM reports r
                            JOIN users u ON r.reporter_username = u.username
                            ORDER BY r.created_at DESC";
                    $stmt = $pdo->prepare($sql);
                } else {
                    $sql = "SELECT r.*, u.username as reporter_name
                            FROM reports r
                            JOIN users u ON r.reporter_username = u.username
                            WHERE r.status = :status
                            ORDER BY r.created_at DESC";
                    $stmt = $pdo->prepare($sql);
                    $stmt->bindParam(':status', $statusFilter, PDO::PARAM_STR);
                }
                
                // Execute the query
                $stmt->execute();
                $reports = $stmt->fetchAll(PDO::FETCH_ASSOC);
                error_log("Found " . count($reports) . " reports");
                
                // For debugging, print out report details
                foreach ($reports as $report) {
                    error_log("Report #" . $report['report_id'] . ": " . $report['content_type'] . " reported by " . $report['reporter_username']);
                }
                
                // Process reports for display
                $processedReports = [];
                foreach ($reports as $report) {
                    // Make sure we're getting all report data
                    if (!isset($report['report_id']) || !isset($report['content_type']) || !isset($report['reporter_username'])) {
                        error_log("Incomplete report data: " . print_r($report, true));
                        continue;
                    }
                    
                    $processedReports[] = [
                        'report_id' => $report['report_id'],
                        'content_type' => $report['content_type'],
                        'content_id' => $report['content_id'],
                        'reporter_username' => $report['reporter_username'],
                        'reason' => $report['reason'],
                        'details' => $report['details'],
                        'status' => $report['status'],
                        'created_at' => $report['created_at'],
                        'html' => generateReportRow($report)
                    ];
                }
                
                echo json_encode(['success' => true, 'reports' => $processedReports]);
            } catch (PDOException $e) {
                error_log("Database error in get_reports: " . $e->getMessage());
                echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
            }
            break;
            
        case 'get_report_details':
            $reportId = isset($_GET['report_id']) ? (int)$_GET['report_id'] : 0;
            
            if (!$reportId) {
                echo json_encode(['success' => false, 'message' => 'Invalid report ID']);
                exit;
            }
            
            $sql = "SELECT r.*, u.username as reporter_name
                    FROM reports r
                    JOIN users u ON r.reporter_username = u.username
                    WHERE r.report_id = :report_id";
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':report_id', $reportId);
            $stmt->execute();
            
            if ($stmt->rowCount() === 0) {
                echo json_encode(['success' => false, 'message' => 'Report not found']);
                exit;
            }
            
            $report = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // If it's a comment, get the thread ID for linking
            if ($report['content_type'] === 'comment') {
                $report['thread_id'] = getThreadIdForComment($report['content_id']);
            }
            
            echo json_encode(['success' => true, 'report' => $report]);
            break;
            
        case 'get_usage_report':
            $reportType = isset($_GET['report_type']) ? $_GET['report_type'] : 'content';
            $dateRange = isset($_GET['date_range']) ? $_GET['date_range'] : '30';
            
            try {
                // Calculate start date based on range
                if ($dateRange === 'all') {
                    $startDate = '1900-01-01'; // Effectively all time
                } else {
                    $startDate = date('Y-m-d', strtotime("-$dateRange days"));
                }
                
                $endDate = date('Y-m-d'); // Today
                $response = ['success' => true];
                
                // Debug info
                $response['debug'] = [
                    'report_type' => $reportType,
                    'date_range' => $dateRange,
                    'start_date' => $startDate,
                    'end_date' => $endDate
                ];
                
                // Generate report based on type
                switch ($reportType) {
                    case 'content':
                        // Get content creation statistics
                        $contentData = [
                            'books' => [],
                            'threads' => [],
                            'comments' => []
                        ];
                        
                        // Get book uploads by date
                        $sql = "SELECT DATE(created_at) as date, COUNT(*) as count 
                                FROM books 
                                WHERE created_at BETWEEN :start_date AND :end_date 
                                GROUP BY DATE(created_at) 
                                ORDER BY date";
                        $stmt = $pdo->prepare($sql);
                        $stmt->bindParam(':start_date', $startDate);
                        $stmt->bindParam(':end_date', $endDate);
                        $stmt->execute();
                        $contentData['books'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
                        
                        // Get thread creation by date
                        $sql = "SELECT DATE(created_at) as date, COUNT(*) as count 
                                FROM threads 
                                WHERE created_at BETWEEN :start_date AND :end_date 
                                GROUP BY DATE(created_at) 
                                ORDER BY date";
                        $stmt = $pdo->prepare($sql);
                        $stmt->bindParam(':start_date', $startDate);
                        $stmt->bindParam(':end_date', $endDate);
                        $stmt->execute();
                        $contentData['threads'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
                        
                        // Get comment creation by date
                        $sql = "SELECT DATE(created_at) as date, COUNT(*) as count 
                                FROM comments 
                                WHERE created_at BETWEEN :start_date AND :end_date 
                                GROUP BY DATE(created_at) 
                                ORDER BY date";
                        $stmt = $pdo->prepare($sql);
                        $stmt->bindParam(':start_date', $startDate);
                        $stmt->bindParam(':end_date', $endDate);
                        $stmt->execute();
                        $contentData['comments'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
                        
                        // Get totals for comparison
                        $prevPeriodStart = date('Y-m-d', strtotime("-" . ($dateRange * 2) . " days"));
                        $prevPeriodEnd = date('Y-m-d', strtotime("-$dateRange days"));
                        
                        $sql = "SELECT 
                                (SELECT COUNT(*) FROM books WHERE created_at BETWEEN :start_date AND :end_date) as books_current,
                                (SELECT COUNT(*) FROM books WHERE created_at BETWEEN :prev_period_start AND :prev_period_end) as books_previous,
                                (SELECT COUNT(*) FROM threads WHERE created_at BETWEEN :start_date AND :end_date) as threads_current,
                                (SELECT COUNT(*) FROM threads WHERE created_at BETWEEN :prev_period_start AND :prev_period_end) as threads_previous,
                                (SELECT COUNT(*) FROM comments WHERE created_at BETWEEN :start_date AND :end_date) as comments_current,
                                (SELECT COUNT(*) FROM comments WHERE created_at BETWEEN :prev_period_start AND :prev_period_end) as comments_previous";
                        $stmt = $pdo->prepare($sql);
                        $stmt->bindParam(':start_date', $startDate);
                        $stmt->bindParam(':end_date', $endDate);
                        $stmt->bindParam(':prev_period_start', $prevPeriodStart);
                        $stmt->bindParam(':prev_period_end', $prevPeriodEnd);
                        $stmt->execute();
                        $totals = $stmt->fetch(PDO::FETCH_ASSOC);
                        
                        $response['data'] = $contentData;
                        $response['totals'] = $totals;
                        $response['title'] = "Content Creation (" . ($dateRange === 'all' ? 'All Time' : "Last $dateRange days") . ")";
                        break;
                        
                    case 'activity':
                        // Get user activity metrics
                        $sql = "SELECT u.username, 
                                (SELECT COUNT(*) FROM books WHERE username = u.username) as book_count,
                                (SELECT COUNT(*) FROM threads WHERE username = u.username) as thread_count,
                                (SELECT COUNT(*) FROM comments WHERE username = u.username) as comment_count,
                                (SELECT MAX(created_at) FROM comments WHERE username = u.username) as last_activity
                                FROM users u
                                WHERE ((SELECT COUNT(*) FROM books WHERE username = u.username) > 0 
                                       OR (SELECT COUNT(*) FROM threads WHERE username = u.username) > 0 
                                       OR (SELECT COUNT(*) FROM comments WHERE username = u.username) > 0)
                                ORDER BY book_count + thread_count + comment_count DESC 
                                LIMIT 20";
                        $stmt = $pdo->prepare($sql);
                        $stmt->execute();
                        $activity = $stmt->fetchAll(PDO::FETCH_ASSOC);
                        
                        // Make sure we have the date column name for users
                        if (!isset($dateColumnName)) {
                            // Determine the date column name for the users table
                            $dateColumnName = 'registration_date';
                            try {
                                $checkStmt = $pdo->prepare("SHOW COLUMNS FROM users LIKE 'registration_date'");
                                $checkStmt->execute();
                                if ($checkStmt->rowCount() == 0) {
                                    $checkStmt = $pdo->prepare("SHOW COLUMNS FROM users LIKE 'created_at'");
                                    $checkStmt->execute();
                                    if ($checkStmt->rowCount() > 0) {
                                        $dateColumnName = 'created_at';
                                    }
                                }
                            } catch (PDOException $e) {
                                error_log("Error checking date column in activity report: " . $e->getMessage());
                            }
                        }
                        
                        // Get total activity counts
                        $sql = "SELECT 
                                (SELECT COUNT(*) FROM books WHERE created_at BETWEEN :start_date AND :end_date) as book_count,
                                (SELECT COUNT(*) FROM threads WHERE created_at BETWEEN :start_date AND :end_date) as thread_count,
                                (SELECT COUNT(*) FROM comments WHERE created_at BETWEEN :start_date AND :end_date) as comment_count,
                                (SELECT COUNT(DISTINCT username) FROM users 
                                 WHERE username IN (SELECT DISTINCT username FROM comments WHERE created_at BETWEEN :start_date_1 AND :end_date_1)
                                 OR username IN (SELECT DISTINCT username FROM threads WHERE created_at BETWEEN :start_date_2 AND :end_date_2)
                                 OR username IN (SELECT DISTINCT username FROM books WHERE created_at BETWEEN :start_date_3 AND :end_date_3))
                                 as active_users";
                        $stmt = $pdo->prepare($sql);
                        $stmt->bindParam(':start_date', $startDate);
                        $stmt->bindParam(':end_date', $endDate);
                        $stmt->bindParam(':start_date_1', $startDate);
                        $stmt->bindParam(':end_date_1', $endDate);
                        $stmt->bindParam(':start_date_2', $startDate);
                        $stmt->bindParam(':end_date_2', $endDate);
                        $stmt->bindParam(':start_date_3', $startDate);
                        $stmt->bindParam(':end_date_3', $endDate);
                        $stmt->execute();
                        $totals = $stmt->fetch(PDO::FETCH_ASSOC);
                        
                        $response['data'] = $activity;
                        $response['totals'] = $totals;
                        $response['title'] = "User Activity (" . ($dateRange === 'all' ? 'All Time' : "Last $dateRange days") . ")";
                        break;
                        
                    case 'popular':
                        // Most popular content stats
                        $popular = [
                            'books' => [],
                            'threads' => []
                        ];
                        
                        // Add views column to books table if it doesn't exist
                        try {
                            $checkStmt = $pdo->prepare("SHOW COLUMNS FROM books LIKE 'views'");
                            $checkStmt->execute();
                            if ($checkStmt->rowCount() == 0) {
                                // Views column doesn't exist, add it
                                $alterStmt = $pdo->prepare("ALTER TABLE books ADD COLUMN views INT DEFAULT 0");
                                $alterStmt->execute();
                                error_log("Added views column to books table");
                            }
                        } catch (PDOException $e) {
                            error_log("Error checking/adding views column to books table: " . $e->getMessage());
                        }
                        
                        // Add views column to threads table if it doesn't exist
                        try {
                            $checkStmt = $pdo->prepare("SHOW COLUMNS FROM threads LIKE 'views'");
                            $checkStmt->execute();
                            if ($checkStmt->rowCount() == 0) {
                                // Views column doesn't exist, add it
                                $alterStmt = $pdo->prepare("ALTER TABLE threads ADD COLUMN views INT DEFAULT 0");
                                $alterStmt->execute();
                                error_log("Added views column to threads table");
                            }
                        } catch (PDOException $e) {
                            error_log("Error checking/adding views column to threads table: " . $e->getMessage());
                        }
                        
                        // Check if views column exists in books table
                        $viewsExistsInBooks = false;
                        try {
                            $checkStmt = $pdo->prepare("SHOW COLUMNS FROM books LIKE 'views'");
                            $checkStmt->execute();
                            $viewsExistsInBooks = ($checkStmt->rowCount() > 0);
                        } catch (PDOException $e) {
                            error_log("Error checking views column in books: " . $e->getMessage());
                        }
                        
                        // Most viewed/popular books
                        if ($viewsExistsInBooks) {
                            // Check if book_comments table exists
                            $bookCommentsExist = false;
                            try {
                                $checkStmt = $pdo->prepare("SHOW TABLES LIKE 'book_comments'");
                                $checkStmt->execute();
                                $bookCommentsExist = ($checkStmt->rowCount() > 0);
                            } catch (PDOException $e) {
                                error_log("Error checking book_comments table: " . $e->getMessage());
                            }
                            
                            // Use views if the column exists
                            if ($bookCommentsExist) {
                                // Include both book_comments and thread comments
                                $sql = "SELECT b.book_id, b.title, b.username, b.views, 
                                        (SELECT COUNT(*) FROM book_comments bc WHERE bc.book_id = b.book_id) +
                                        (SELECT COUNT(*) FROM comments c 
                                         JOIN threads t ON c.thread_id = t.thread_id 
                                         WHERE t.book_id = b.book_id) as comment_count
                                        FROM books b
                                        GROUP BY b.book_id
                                        ORDER BY b.views DESC, comment_count DESC
                                        LIMIT 10";
                            } else {
                                // Use only thread comments
                                $sql = "SELECT b.book_id, b.title, b.username, b.views, 
                                        COUNT(c.comment_id) as comment_count
                                        FROM books b
                                        LEFT JOIN threads t ON t.book_id = b.book_id
                                        LEFT JOIN comments c ON c.thread_id = t.thread_id
                                        GROUP BY b.book_id
                                        ORDER BY b.views DESC, comment_count DESC
                                        LIMIT 10";
                            }
                        } else {
                            // Sort by comment count as a popularity metric
                            // Check if book_comments table exists
                            $bookCommentsExist = false;
                            try {
                                $checkStmt = $pdo->prepare("SHOW TABLES LIKE 'book_comments'");
                                $checkStmt->execute();
                                $bookCommentsExist = ($checkStmt->rowCount() > 0);
                            } catch (PDOException $e) {
                                error_log("Error checking book_comments table: " . $e->getMessage());
                            }
                            
                            if ($bookCommentsExist) {
                                // Include both book_comments and thread comments
                                $sql = "SELECT b.book_id, b.title, b.username, 0 as views, 
                                        (SELECT COUNT(*) FROM book_comments bc WHERE bc.book_id = b.book_id) +
                                        (SELECT COUNT(*) FROM comments c 
                                         JOIN threads t ON c.thread_id = t.thread_id 
                                         WHERE t.book_id = b.book_id) as comment_count
                                        FROM books b
                                        GROUP BY b.book_id
                                        ORDER BY comment_count DESC
                                        LIMIT 10";
                            } else {
                                // Use only thread comments
                                $sql = "SELECT b.book_id, b.title, b.username, 0 as views, 
                                        COUNT(c.comment_id) as comment_count
                                        FROM books b
                                        LEFT JOIN threads t ON t.book_id = b.book_id
                                        LEFT JOIN comments c ON c.thread_id = t.thread_id
                                        GROUP BY b.book_id
                                        ORDER BY comment_count DESC
                                        LIMIT 10";
                            }
                        }
                        $stmt = $pdo->prepare($sql);
                        $stmt->execute();
                        $popular['books'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
                        
                        // Check if views column exists in threads table
                        $viewsExistsInThreads = false;
                        try {
                            $checkStmt = $pdo->prepare("SHOW COLUMNS FROM threads LIKE 'views'");
                            $checkStmt->execute();
                            $viewsExistsInThreads = ($checkStmt->rowCount() > 0);
                        } catch (PDOException $e) {
                            error_log("Error checking views column in threads: " . $e->getMessage());
                        }
                        
                        // Most active threads 
                        if ($viewsExistsInThreads) {
                            $sql = "SELECT t.thread_id, t.title, t.username, t.views, 
                                    COUNT(c.comment_id) as comment_count
                                    FROM threads t
                                    LEFT JOIN comments c ON t.thread_id = c.thread_id
                                    GROUP BY t.thread_id
                                    ORDER BY comment_count DESC, t.views DESC
                                    LIMIT 10";
                        } else {
                            $sql = "SELECT t.thread_id, t.title, t.username, 0 as views, 
                                    COUNT(c.comment_id) as comment_count
                                    FROM threads t
                                    LEFT JOIN comments c ON t.thread_id = c.thread_id
                                    GROUP BY t.thread_id
                                    ORDER BY comment_count DESC
                                    LIMIT 10";
                        }
                        $stmt = $pdo->prepare($sql);
                        $stmt->execute();
                        $popular['threads'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
                        
                        $response['data'] = $popular;
                        $response['title'] = "Content Popularity (" . ($dateRange === 'all' ? 'All Time' : "Last $dateRange days") . ")";
                        break;
                        
                    default:
                        throw new Exception("Invalid report type: $reportType");
                }
                
                echo json_encode($response);
            } catch (PDOException $e) {
                error_log("Database error in get_usage_report: " . $e->getMessage());
                echo json_encode([
                    'success' => false, 
                    'message' => 'Database error: ' . $e->getMessage(),
                    'error_code' => $e->getCode(),
                    'report_type' => isset($reportType) ? $reportType : 'unknown'
                ]);
            } catch (Exception $e) {
                error_log("Error in get_usage_report: " . $e->getMessage());
                echo json_encode(['success' => false, 'message' => $e->getMessage()]);
            }
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
    if (!isset($report['content_type']) || !isset($report['report_id'])) {
        error_log("Invalid report data in generateReportRow: " . print_r($report, true));
        return '<tr><td colspan="6">Invalid report data</td></tr>';
    }
    
    $contentTypeLabel = ucfirst($report['content_type']);
    $statusClass = 'status-' . $report['status'];
    $statusLabel = ucfirst($report['status']);
    $reportId = $report['report_id'];
    
    // Ensure all necessary fields exist
    $contentId = isset($report['content_id']) ? $report['content_id'] : 'Unknown';
    $reporter = isset($report['reporter_username']) ? $report['reporter_username'] : 'Unknown';
    $reason = isset($report['reason']) ? $report['reason'] : 'Not specified';
    $created = isset($report['created_at']) ? $report['created_at'] : date('Y-m-d H:i:s');
    
    $html = '<tr data-report-id="' . $reportId . '">';
    $html .= '<td>' . htmlspecialchars($contentTypeLabel) . ' #' . $contentId . '</td>';
    $html .= '<td>' . htmlspecialchars($reporter) . '</td>';
    $html .= '<td>' . htmlspecialchars($reason) . '</td>';
    $html .= '<td class="' . $statusClass . '">' . $statusLabel . '</td>';
    $html .= '<td>' . date('M j, Y g:i A', strtotime($created)) . '</td>';
    $html .= '<td class="actions-column">';
    
    // View button
    $html .= '<button class="view-report-button" data-report-id="' . $reportId . '">View Details</button>';
    
    // Status update buttons based on current status
    if (isset($report['status']) && $report['status'] === 'pending') {
        $html .= '<form action="admin_actions.php" method="post" class="inline-form">';
        $html .= '<input type="hidden" name="action" value="resolve_report">';
        $html .= '<input type="hidden" name="report_id" value="' . $reportId . '">';
        $html .= '<button type="submit" class="resolve-button">Resolve</button>';
        $html .= '</form>';
        
        $html .= '<form action="admin_actions.php" method="post" class="inline-form">';
        $html .= '<input type="hidden" name="action" value="dismiss_report">';
        $html .= '<input type="hidden" name="report_id" value="' . $reportId . '">';
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

// Function to generate HTML for a content search row
function generateContentSearchRow($item, $type) {
    $html = '<tr>';
    
    // Type column with icon
    $html .= '<td class="content-type-column">';
    if ($type === 'book') {
        $html .= '<span class="content-type book">Book</span>';
    } else if ($type === 'thread') {
        $html .= '<span class="content-type thread">Thread</span>';
    } else if ($type === 'comment') {
        $html .= '<span class="content-type comment">Comment</span>';
    }
    $html .= '</td>';
    
    // Title column
    $html .= '<td>';
    if ($type === 'book') {
        $html .= '<a href="book_detail.php?id=' . $item['book_id'] . '">' . htmlspecialchars($item['title']) . '</a>';
    } else if ($type === 'thread') {
        $html .= '<a href="thread.php?id=' . $item['thread_id'] . '">' . htmlspecialchars($item['title']) . '</a>';
    } else if ($type === 'comment') {
        $html .= '<a href="thread.php?id=' . $item['thread_id'] . '#comment-' . $item['comment_id'] . '">Comment on: ' . htmlspecialchars($item['thread_title']) . '</a>';
    }
    $html .= '</td>';
    
    // Content preview
    $html .= '<td>';
    if ($type === 'book') {
        $html .= htmlspecialchars(substr(strip_tags($item['description']), 0, 100)) . '...';
    } else if ($type === 'thread' || $type === 'comment') {
        $html .= htmlspecialchars(substr(strip_tags($item['content']), 0, 100)) . '...';
    }
    $html .= '</td>';
    
    // Author
    $html .= '<td>' . htmlspecialchars($item['username']) . '</td>';
    
    // Date
    $html .= '<td>' . date('M j, Y g:i A', strtotime($item['created_at'])) . '</td>';
    
    // Actions
    $html .= '<td class="actions-column">';
    
    // View button
    if ($type === 'book') {
        $html .= '<a href="book_detail.php?id=' . $item['book_id'] . '" class="view-button">View</a>';
    } else if ($type === 'thread') {
        $html .= '<a href="thread.php?id=' . $item['thread_id'] . '" class="view-button">View</a>';
    } else if ($type === 'comment') {
        $html .= '<a href="thread.php?id=' . $item['thread_id'] . '#comment-' . $item['comment_id'] . '" class="view-button">View</a>';
    }
    
    // Delete button for each type
    if ($type === 'book') {
        $html .= '<form action="admin_actions.php" method="post" class="inline-form">';
        $html .= '<input type="hidden" name="action" value="delete_book">';
        $html .= '<input type="hidden" name="book_id" value="' . $item['book_id'] . '">';
        $html .= '<button type="submit" class="delete-button" onclick="return confirm(\'Are you sure you want to delete this book? This action cannot be undone.\')">Delete</button>';
        $html .= '</form>';
    } else if ($type === 'thread') {
        $html .= '<form action="admin_actions.php" method="post" class="inline-form">';
        $html .= '<input type="hidden" name="action" value="delete_thread">';
        $html .= '<input type="hidden" name="thread_id" value="' . $item['thread_id'] . '">';
        $html .= '<button type="submit" class="delete-button" onclick="return confirm(\'Are you sure you want to delete this thread? All comments will also be deleted. This action cannot be undone.\')">Delete</button>';
        $html .= '</form>';
    } else if ($type === 'comment') {
        $html .= '<form action="admin_actions.php" method="post" class="inline-form">';
        $html .= '<input type="hidden" name="action" value="delete_comment">';
        $html .= '<input type="hidden" name="comment_id" value="' . $item['comment_id'] . '">';
        $html .= '<button type="submit" class="delete-button" onclick="return confirm(\'Are you sure you want to delete this comment? This action cannot be undone.\')">Delete</button>';
        $html .= '</form>';
    }
    
    $html .= '</td>';
    $html .= '</tr>';
    
    return $html;
}
?> 