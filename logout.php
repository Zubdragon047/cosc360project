<?php
echo "Logging you out...";
session_start();
session_unset();
session_destroy();
header('Refresh: 5; URL=home.php');
?>