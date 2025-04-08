<?php
require_once('protected/config.php');

try {
    $pdo = new PDO(DBCONNSTRING, DBUSER, DBPASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Check if reports table exists
    $sql = "SHOW TABLES LIKE 'reports'";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    
    if ($stmt->rowCount() > 0) {
        echo "Reports table exists.<br>";
        
        // Check if there are any reports
        $sql = "SELECT * FROM reports";
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        $reports = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (count($reports) > 0) {
            echo "Found " . count($reports) . " reports:<br>";
            foreach ($reports as $report) {
                echo "ID: " . $report['report_id'] . 
                     ", Type: " . $report['content_type'] . 
                     ", Content ID: " . $report['content_id'] . 
                     ", Reporter: " . $report['reporter_username'] . 
                     ", Status: " . $report['status'] . 
                     ", Created: " . $report['created_at'] . "<br>";
            }
        } else {
            echo "No reports found in the table.<br>";
        }
    } else {
        echo "Reports table does not exist.<br>";
        
        // Try to create the reports table
        echo "Attempting to create the reports table... ";
        
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
        echo "Reports table created successfully.<br>";
    }
} catch(PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?> 