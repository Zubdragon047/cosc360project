<?php
session_start();
require_once('protected/config.php');

// Only allow admins to run this test
if (!isset($_SESSION['username']) || !isset($_SESSION['type']) || $_SESSION['type'] !== 'admin') {
    echo "Unauthorized - Admin access required";
    exit;
}

echo "<h1>Reports API Test</h1>";

// Get the raw response from admin_handler.php
$apiUrl = 'admin_handler.php?action=get_reports&status=all&_=' . time();
echo "<p>Testing API URL: <code>$apiUrl</code></p>";

// Use cURL to make the request
$ch = curl_init($apiUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HEADER, true);
$response = curl_exec($ch);

// Get response info
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
$headers = substr($response, 0, $headerSize);
$body = substr($response, $headerSize);

curl_close($ch);

echo "<h2>Response Status Code: $httpCode</h2>";

echo "<h2>Response Headers:</h2>";
echo "<pre>" . htmlspecialchars($headers) . "</pre>";

echo "<h2>Response Body:</h2>";

// Try to parse the JSON
$decodedResponse = json_decode($body, true);
if (json_last_error() === JSON_ERROR_NONE) {
    echo "<p><strong>Valid JSON response received</strong></p>";
    
    // Pretty print the JSON for readability
    echo "<pre>" . htmlspecialchars(json_encode($decodedResponse, JSON_PRETTY_PRINT)) . "</pre>";
    
    if (isset($decodedResponse['success']) && $decodedResponse['success'] === true) {
        echo "<p style='color:green'>✓ API reports success: true</p>";
        
        if (isset($decodedResponse['reports']) && is_array($decodedResponse['reports'])) {
            $count = count($decodedResponse['reports']);
            echo "<p>Found $count reports in the API response</p>";
            
            if ($count > 0) {
                echo "<h3>First Report Details:</h3>";
                echo "<pre>" . htmlspecialchars(json_encode($decodedResponse['reports'][0], JSON_PRETTY_PRINT)) . "</pre>";
                
                if (isset($decodedResponse['reports'][0]['html'])) {
                    echo "<h3>HTML for First Report:</h3>";
                    echo "<div style='border: 1px solid #ccc; padding: 10px;'>";
                    echo $decodedResponse['reports'][0]['html'];
                    echo "</div>";
                } else {
                    echo "<p style='color:red'>✗ Missing HTML field in report data</p>";
                }
            }
        } else {
            echo "<p style='color:red'>✗ Missing or invalid 'reports' array in response</p>";
        }
    } else {
        echo "<p style='color:red'>✗ API reports failure or missing success field</p>";
        if (isset($decodedResponse['message'])) {
            echo "<p>Error message: " . htmlspecialchars($decodedResponse['message']) . "</p>";
        }
    }
} else {
    echo "<p style='color:red'>✗ Invalid JSON response</p>";
    echo "<p>JSON error: " . json_last_error_msg() . "</p>";
    echo "<pre>" . htmlspecialchars($body) . "</pre>";
}
?>

<p><a href="admin.php#reports">Back to Admin Dashboard</a></p> 