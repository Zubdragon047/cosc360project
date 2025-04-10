<?php
session_start();
require_once('protected/config.php');

// Check if user is admin
if (!isset($_SESSION['type']) || $_SESSION['type'] !== 'admin') {
    echo "This page is only accessible to admins.";
    exit;
}

echo "<h1>User Registration Debugging</h1>";

try {
    $pdo = new PDO(DBCONNSTRING, DBUSER, DBPASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Check table structure
    echo "<h2>Users Table Structure</h2>";
    $sql = "DESCRIBE users";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<table border='1'><tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
    foreach ($columns as $column) {
        echo "<tr>";
        echo "<td>" . $column['Field'] . "</td>";
        echo "<td>" . $column['Type'] . "</td>";
        echo "<td>" . $column['Null'] . "</td>";
        echo "<td>" . $column['Key'] . "</td>";
        echo "<td>" . $column['Default'] . "</td>";
        echo "<td>" . $column['Extra'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // Check all users
    echo "<h2>All Users</h2>";
    $sql = "SELECT * FROM users ORDER BY username";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<table border='1'><tr>";
    // Output headers based on first user's keys
    if (!empty($users)) {
        foreach (array_keys($users[0]) as $key) {
            echo "<th>" . htmlspecialchars($key) . "</th>";
        }
        echo "</tr>";
        
        // Output each user's data
        foreach ($users as $user) {
            echo "<tr>";
            foreach ($user as $key => $value) {
                if ($key === 'profilepic') {
                    echo "<td><img src='" . htmlspecialchars($value) . "' width='50'></td>";
                } else {
                    echo "<td>" . htmlspecialchars($value) . "</td>";
                }
            }
            echo "</tr>";
        }
    } else {
        echo "<tr><td colspan='7'>No users found</td></tr>";
    }
    echo "</table>";
    
    // Test registration date grouping query
    echo "<h2>Registration Date Grouping Test</h2>";
    $startDate = date('Y-m-d', strtotime("-30 days"));
    $endDate = date('Y-m-d');
    
    echo "<p>Testing query for registrations between $startDate and $endDate</p>";
    
    // Find the registration date column
    $hasRegistrationDate = false;
    $hasCreatedAt = false;
    foreach ($columns as $column) {
        if ($column['Field'] === 'registration_date') {
            $hasRegistrationDate = true;
        }
        if ($column['Field'] === 'created_at') {
            $hasCreatedAt = true;
        }
    }
    
    $dateColumn = $hasRegistrationDate ? 'registration_date' : ($hasCreatedAt ? 'created_at' : null);
    
    if ($dateColumn) {
        echo "<p>Using column: $dateColumn</p>";
        
        // Try to run the exact query from admin_handler.php
        $sql = "SELECT DATE($dateColumn) as date, COUNT(*) as count 
                FROM users 
                WHERE $dateColumn BETWEEN :start_date AND :end_date 
                GROUP BY DATE($dateColumn) 
                ORDER BY date ASC";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':start_date', $startDate);
        $stmt->bindParam(':end_date', $endDate);
        $stmt->execute();
        $registrations = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<table border='1'><tr><th>Date</th><th>Count</th></tr>";
        if (!empty($registrations)) {
            foreach ($registrations as $reg) {
                echo "<tr><td>" . $reg['date'] . "</td><td>" . $reg['count'] . "</td></tr>";
            }
        } else {
            echo "<tr><td colspan='2'>No registrations found in this date range</td></tr>";
        }
        echo "</table>";
        
        // Show all registration dates for debugging
        echo "<h2>All Registration Dates</h2>";
        $sql = "SELECT username, $dateColumn FROM users ORDER BY $dateColumn DESC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        $dates = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<table border='1'><tr><th>Username</th><th>$dateColumn</th></tr>";
        foreach ($dates as $date) {
            echo "<tr><td>" . $date['username'] . "</td><td>" . $date[$dateColumn] . "</td></tr>";
        }
        echo "</table>";
    } else {
        echo "<p>No registration date column found in users table!</p>";
        
        // Let's add it
        echo "<h2>Adding registration_date column</h2>";
        try {
            $sql = "ALTER TABLE users ADD COLUMN registration_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP";
            $stmt = $pdo->prepare($sql);
            $stmt->execute();
            echo "<p>Successfully added registration_date column!</p>";
            
            // Update all users with current date
            $sql = "UPDATE users SET registration_date = NOW()";
            $stmt = $pdo->prepare($sql);
            $stmt->execute();
            echo "<p>Updated all users with registration date.</p>";
        } catch (PDOException $e) {
            echo "<p>Error adding column: " . $e->getMessage() . "</p>";
        }
    }
    
} catch (PDOException $e) {
    echo "Database error: " . $e->getMessage();
}
?> 