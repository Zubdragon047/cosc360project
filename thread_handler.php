<?php
session_start();
header('Content-Type: application/json');

require_once('protected/config.php');

try {
    $pdo = new PDO(DBCONNSTRING, DBUSER, DBPASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Action parameter to determine what to do
    $action = isset($_GET['action']) ? $_GET['action'] : null;
    
    // Handle thread listing
    if ($action === 'list') {
        $search = isset($_GET['search']) ? '%' . $_GET['search'] . '%' : null;
        $lastUpdate = isset($_GET['last_update']) ? $_GET['last_update'] : null;
        
        // Start building the query
        $sql = "SELECT t.*, COUNT(c.comment_id) AS comment_count 
                FROM threads t 
                LEFT JOIN comments c ON t.thread_id = c.thread_id";
        
        $params = [];
        $conditions = [];
        
        // Add search condition if provided
        if ($search) {
            $conditions[] = "(t.title LIKE :search OR t.content LIKE :search)";
            $params[':search'] = $search;
        }
        
        // Add last update condition if provided
        if ($lastUpdate) {
            $conditions[] = "t.updated_at > :lastUpdate";
            $params[':lastUpdate'] = $lastUpdate;
        }
        
        // Add WHERE clause if there are conditions
        if (!empty($conditions)) {
            $sql .= " WHERE " . implode(" AND ", $conditions);
        }
        
        // Group by and order
        $sql .= " GROUP BY t.thread_id ORDER BY t.updated_at DESC";
        
        $stmt = $pdo->prepare($sql);
        
        // Bind parameters
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        
        $stmt->execute();
        
        $threads = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $threads[] = [
                'thread_id' => $row['thread_id'],
                'title' => htmlspecialchars($row['title']),
                'username' => htmlspecialchars($row['username']),
                'content' => htmlspecialchars($row['content']),
                'created_at' => $row['created_at'],
                'updated_at' => $row['updated_at'],
                'comment_count' => $row['comment_count']
            ];
        }
        
        echo json_encode([
            'success' => true,
            'threads' => $threads,
            'timestamp' => date('Y-m-d H:i:s')
        ]);
        exit;
    }
    
    // Handle creating a new thread (could be added in the future)
    
    // Unsupported action
    http_response_code(400);
    echo json_encode(['error' => 'Invalid action']);
    
} catch(PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
?> 