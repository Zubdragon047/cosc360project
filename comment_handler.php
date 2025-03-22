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
        
        $comments = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $comments[] = [
                'id' => $row['comment_id'],
                'username' => $row['username'],
                'content' => $row['content'],
                'created_at' => $row['created_at'],
                'profilepic' => $row['profilepic']
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
        
        // Insert the comment
        $sql = "INSERT INTO comments (thread_id, username, content) 
                VALUES (:thread_id, :username, :content)";
        $stmt = $pdo->prepare($sql);
        
        $params = [
            'thread_id' => $thread_id,
            'username' => $username,
            'content' => $content
        ];
        
        $stmt->execute($params);
        $comment_id = $pdo->lastInsertId();
        
        // Update the thread's updated_at timestamp
        $sql = "UPDATE threads SET updated_at = CURRENT_TIMESTAMP WHERE thread_id = :thread_id";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':thread_id', $thread_id, PDO::PARAM_INT);
        $stmt->execute();
        
        // Get the comment details to return
        $sql = "SELECT c.*, u.profilepic
                FROM comments c
                JOIN users u ON c.username = u.username
                WHERE c.comment_id = :comment_id";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':comment_id', $comment_id, PDO::PARAM_INT);
        $stmt->execute();
        
        $comment = $stmt->fetch(PDO::FETCH_ASSOC);
        
        echo json_encode([
            'success' => true, 
            'comment' => [
                'id' => $comment['comment_id'],
                'username' => $comment['username'],
                'content' => $comment['content'],
                'created_at' => $comment['created_at'],
                'profilepic' => $comment['profilepic']
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