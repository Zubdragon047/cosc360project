<?php
session_start();
header('Content-Type: application/json');

require_once('protected/config.php');

try {
    $pdo = new PDO(DBCONNSTRING, DBUSER, DBPASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Handle different request types
    $requestType = $_SERVER['REQUEST_METHOD'];
    
    // GET request - fetch comments for a thread
    if ($requestType === 'GET') {
        if (!isset($_GET['thread_id']) || !is_numeric($_GET['thread_id'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid thread ID']);
            exit;
        }
        
        $thread_id = $_GET['thread_id'];
        $last_id = isset($_GET['last_id']) ? $_GET['last_id'] : 0;
        
        // Get comments for the thread
        $sql = "SELECT c.*, u.profilepic 
                FROM comments c
                JOIN users u ON c.username = u.username
                WHERE c.thread_id = :thread_id AND c.comment_id > :last_id
                ORDER BY c.created_at ASC";
        
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':thread_id', $thread_id, PDO::PARAM_INT);
        $stmt->bindParam(':last_id', $last_id, PDO::PARAM_INT);
        $stmt->execute();
        
        // Debug logging for admin status
        if (isset($_SESSION['type']) && $_SESSION['type'] === 'admin') {
            error_log('Admin user is viewing comments');
        } else {
            error_log('Non-admin user is viewing comments. Session type: ' . (isset($_SESSION['type']) ? $_SESSION['type'] : 'not set'));
        }
        
        $comments = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $comments[] = [
                'id' => $row['comment_id'],
                'username' => $row['username'],
                'content' => $row['content'],
                'created_at' => $row['created_at'],
                'profilepic' => $row['profilepic'],
                'parent_id' => $row['parent_id'],
                'is_admin_viewing' => isset($_SESSION['type']) && $_SESSION['type'] === 'admin'
            ];
        }
        
        echo json_encode(['success' => true, 'comments' => $comments]);
        exit;
    }
    
    // POST request - add a new comment
    if ($requestType === 'POST') {
        // Check if user is logged in
        if (!isset($_SESSION['username'])) {
            http_response_code(401);
            echo json_encode(['error' => 'You must be logged in to post comments']);
            exit;
        }
        
        // Get JSON data from the request body
        $json = file_get_contents('php://input');
        $data = json_decode($json, true);
        
        if (!$data || !isset($data['thread_id']) || !isset($data['content']) || 
            !is_numeric($data['thread_id']) || empty($data['content'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid input data']);
            exit;
        }
        
        $thread_id = $data['thread_id'];
        $content = $data['content'];
        $username = $_SESSION['username'];
        $parent_id = isset($data['parent_id']) && is_numeric($data['parent_id']) ? $data['parent_id'] : null;
        
        // Check if thread exists
        $sql = "SELECT thread_id FROM threads WHERE thread_id = :thread_id";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':thread_id', $thread_id, PDO::PARAM_INT);
        $stmt->execute();
        
        if ($stmt->rowCount() == 0) {
            http_response_code(404);
            echo json_encode(['error' => 'Thread not found']);
            exit;
        }
        
        // If parent_id is provided, check if it exists
        if ($parent_id) {
            $sql = "SELECT comment_id FROM comments WHERE comment_id = :parent_id AND thread_id = :thread_id";
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':parent_id', $parent_id, PDO::PARAM_INT);
            $stmt->bindParam(':thread_id', $thread_id, PDO::PARAM_INT);
            $stmt->execute();
            
            if ($stmt->rowCount() == 0) {
                $parent_id = null; // Parent comment not found or not in this thread
            }
        }
        
        // Insert the comment
        try {
            $sql = "INSERT INTO comments (thread_id, username, content, parent_id) 
                    VALUES (:thread_id, :username, :content, :parent_id)";
            $stmt = $pdo->prepare($sql);
            
            $params = [
                'thread_id' => $thread_id,
                'username' => $username,
                'content' => $content,
                'parent_id' => $parent_id
            ];
            
            $stmt->execute($params);
        } catch (PDOException $e) {
            // If parent_id column doesn't exist, try without it
            if (strpos($e->getMessage(), "parent_id") !== false) {
                $sql = "INSERT INTO comments (thread_id, username, content) 
                        VALUES (:thread_id, :username, :content)";
                $stmt = $pdo->prepare($sql);
                
                $params = [
                    'thread_id' => $thread_id,
                    'username' => $username,
                    'content' => $content
                ];
                
                $stmt->execute($params);
                
                // Try to add the parent_id column for future use
                try {
                    $alterSql = "ALTER TABLE comments ADD COLUMN parent_id INT DEFAULT NULL";
                    $pdo->exec($alterSql);
                    error_log("Added parent_id column to comments table");
                } catch (PDOException $alterEx) {
                    error_log("Error adding parent_id column: " . $alterEx->getMessage());
                }
            } else {
                // Re-throw if it's a different error
                throw $e;
            }
        }
        
        $comment_id = $pdo->lastInsertId();
        
        // Update the thread's updated_at timestamp
        $sql = "UPDATE threads SET updated_at = CURRENT_TIMESTAMP WHERE thread_id = :thread_id";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':thread_id', $thread_id, PDO::PARAM_INT);
        $stmt->execute();
        
        // Get the comment details to return
        $sql = "SELECT c.*, u.profilepic FROM comments c JOIN users u ON c.username = u.username WHERE c.comment_id = :comment_id";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':comment_id', $comment_id, PDO::PARAM_INT);
        $stmt->execute();
        
        $comment = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Check if parent_id exists in the result
        $parentIdValue = isset($comment['parent_id']) ? $comment['parent_id'] : null;
        
        echo json_encode([
            'success' => true, 
            'comment' => [
                'id' => $comment['comment_id'],
                'username' => $comment['username'],
                'content' => $comment['content'],
                'created_at' => $comment['created_at'],
                'profilepic' => $comment['profilepic'],
                'parent_id' => $parentIdValue,
                'is_admin_viewing' => isset($_SESSION['type']) && $_SESSION['type'] === 'admin'
            ]
        ]);
        exit;
    }
    
    // Unsupported request method
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    
} catch(PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
?> 