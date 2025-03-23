<?php
require_once('protected/config.php');
try {
    $pdo = new PDO(DBCONNSTRING, DBUSER, DBPASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Get all column names from book_requests table
    $stmt = $pdo->query("SHOW COLUMNS FROM book_requests");
    echo "<h2>Columns in book_requests table:</h2>";
    echo "<pre>";
    while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        print_r($row);
    }
    echo "</pre>";
    
    // Check if there are any existing requests
    $stmt = $pdo->query("SELECT * FROM book_requests LIMIT 5");
    echo "<h2>Sample book requests (if any):</h2>";
    echo "<pre>";
    while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        print_r($row);
    }
    echo "</pre>";
    
    // Compare with the SQL in request_book.php
    echo "<h2>SQL used in request_book.php:</h2>";
    echo '<pre>$sqlInsert = "INSERT INTO book_requests (book_id, requester_username, status, request_date) 
                  VALUES (:bookId, :username, \'pending\', NOW())";</pre>';
    
    // Check for any missing columns that might be in book_requests.sql
    echo "<h2>Adding missing columns if needed:</h2>";
    if(!$pdo->query("SHOW COLUMNS FROM book_requests LIKE 'request_date'")->fetch()) {
        $pdo->exec("ALTER TABLE book_requests ADD COLUMN request_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP");
        echo "Added request_date column.<br>";
    }
    if(!$pdo->query("SHOW COLUMNS FROM book_requests LIKE 'response_date'")->fetch()) {
        $pdo->exec("ALTER TABLE book_requests ADD COLUMN response_date TIMESTAMP NULL");
        echo "Added response_date column.<br>";
    }
    if(!$pdo->query("SHOW COLUMNS FROM book_requests LIKE 'return_date'")->fetch()) {
        $pdo->exec("ALTER TABLE book_requests ADD COLUMN return_date TIMESTAMP NULL");
        echo "Added return_date column.<br>";
    }
    echo "Check complete.";
    
} catch(PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?> 