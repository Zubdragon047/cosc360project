<?php
require_once('protected/config.php');
try {
    $pdo = new PDO(DBCONNSTRING, DBUSER, DBPASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Get all column names from users table
    $stmt = $pdo->query("SHOW COLUMNS FROM users");
    echo "<h2>Columns in users table:</h2>";
    echo "<pre>";
    while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        print_r($row);
    }
    echo "</pre>";
    
    // Get the SQL for the register_form.php
    echo "<h2>SQL used in register_form.php:</h2>";
    echo '<pre>$sql = "insert into users values(:username,:password,:email,:firstname,:lastname,:profilepic,:type)";</pre>';
    
    // Add type column if it doesn't exist
    echo "<h2>Adding type column if it doesn't exist:</h2>";
    $pdo->exec("ALTER TABLE users ADD COLUMN IF NOT EXISTS type VARCHAR(10) DEFAULT 'user'");
    echo "Type column check complete.";
    
} catch(PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?> 