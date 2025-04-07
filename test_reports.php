<?php
session_start();
require_once('protected/config.php');

// Only allow admins to run this test
if (!isset($_SESSION['username']) || !isset($_SESSION['type']) || $_SESSION['type'] !== 'admin') {
    echo "Unauthorized - Admin access required";
    exit;
}

echo "<h1>Reports Table Test</h1>";

try {
    // Connect to database
    $pdo = new PDO(DBCONNSTRING, DBUSER, DBPASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Check if reports table exists
    $sql = "SHOW TABLES LIKE 'reports'";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    
    if ($stmt->rowCount() > 0) {
        echo "<p style='color:green'>✓ Reports table exists</p>";
    } else {
        echo "<p style='color:red'>✗ Reports table does not exist! Creating it now...</p>";
        
        // Create the reports table
        $sql = "CREATE TABLE IF NOT EXISTS reports (
            report_id INT AUTO_INCREMENT PRIMARY KEY,
            content_type VARCHAR(50) NOT NULL,
            content_id INT NOT NULL,
            reporter_username VARCHAR(255) NOT NULL,
            reason VARCHAR(100) NOT NULL,
            details TEXT,
            status VARCHAR(20) DEFAULT 'pending',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (reporter_username) REFERENCES users(username) ON DELETE CASCADE
        )";
        $pdo->exec($sql);
        echo "<p style='color:green'>✓ Reports table created successfully</p>";
    }
    
    // Count reports
    $sql = "SELECT COUNT(*) as count FROM reports";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $count = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    
    echo "<p>Total reports in database: <strong>$count</strong></p>";
    
    if ($count === 0) {
        echo "<p style='color:orange'>⚠ No reports found in the database. Adding a test report...</p>";
        
        // Check if there's an admin user to use as reporter
        $sql = "SELECT username FROM users WHERE type = 'admin' LIMIT 1";
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        $admin = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($admin) {
            $reporterUsername = $admin['username'];
            
            // Check if there's a book to report
            $sql = "SELECT book_id FROM books LIMIT 1";
            $stmt = $pdo->prepare($sql);
            $stmt->execute();
            $book = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($book) {
                // Insert test report
                $sql = "INSERT INTO reports (content_type, content_id, reporter_username, reason, details, status) 
                        VALUES ('book', :book_id, :username, 'Test report', 'This is a test report created by the system.', 'pending')";
                $stmt = $pdo->prepare($sql);
                $stmt->bindValue(':book_id', $book['book_id']);
                $stmt->bindValue(':username', $reporterUsername);
                $stmt->execute();
                
                echo "<p style='color:green'>✓ Test report added successfully</p>";
            } else {
                echo "<p style='color:red'>✗ No books found to create a test report</p>";
            }
        } else {
            echo "<p style='color:red'>✗ No admin users found to use as reporter</p>";
        }
    }
    
    // Get all reports
    $sql = "SELECT r.*, u.username FROM reports r JOIN users u ON r.reporter_username = u.username ORDER BY r.created_at DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $reports = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (count($reports) > 0) {
        echo "<h2>Reports in Database:</h2>";
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr style='background-color: #f2f2f2;'>";
        echo "<th>ID</th><th>Type</th><th>Content ID</th><th>Reporter</th>";
        echo "<th>Reason</th><th>Status</th><th>Created</th>";
        echo "</tr>";
        
        foreach ($reports as $report) {
            echo "<tr>";
            echo "<td>" . $report['report_id'] . "</td>";
            echo "<td>" . $report['content_type'] . "</td>";
            echo "<td>" . $report['content_id'] . "</td>";
            echo "<td>" . $report['reporter_username'] . "</td>";
            echo "<td>" . $report['reason'] . "</td>";
            echo "<td>" . $report['status'] . "</td>";
            echo "<td>" . $report['created_at'] . "</td>";
            echo "</tr>";
        }
        
        echo "</table>";
    } else {
        echo "<p style='color:red'>✗ No reports found even after attempting to create one</p>";
    }
    
} catch (PDOException $e) {
    echo "<p style='color:red'>Database error: " . $e->getMessage() . "</p>";
}
?>

<p><a href="admin.php#reports">Back to Admin Dashboard</a></p> 