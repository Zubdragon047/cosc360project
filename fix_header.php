<?php
// This script fixes the header.php file by removing the incorrect closing body and html tags

$headerFile = 'includes/header.php';
$content = file_get_contents($headerFile);

// Remove the closing body and html tags
$fixedContent = str_replace('</body>
</html> ', '', $content);

// Save the fixed content back to the file
file_put_contents($headerFile, $fixedContent);

echo "Header file has been fixed.";
?> 