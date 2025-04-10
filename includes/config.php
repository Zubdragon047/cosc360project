<?php
/**
 * Configuration file
 */

// Database configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'cosc360project');
define('DB_USER', 'root');
define('DB_PASS', '');

// Session configuration
define('SESSION_LIFETIME', 3600); // 1 hour
define('SESSION_NAME', 'COSC360_SESSION');

// Security configuration
define('CSRF_TOKEN_LENGTH', 32);
define('PASSWORD_MIN_LENGTH', 8);

// File upload configuration
define('MAX_FILE_SIZE', 5242880); // 5MB
define('ALLOWED_FILE_TYPES', ['image/jpeg', 'image/png', 'image/gif']);

// Error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../logs/error.log');

// Timezone
date_default_timezone_set('America/Vancouver'); 