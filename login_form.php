<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['username'])) {
        $username = $_POST['username'];
    }
    if (isset($_POST['password'])) {
        $password = $_POST['password'];
    }

    try {
        require_once('protected/config.php');
        $pdo = new PDO(DBCONNSTRING, DBUSER, DBPASS);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $sql = "select * from users";
        $result = $pdo->query($sql);
        while ($row = $result->fetch()) {
            if ($row['username'] == $username) {
                if ($row['password'] != $password) {
                    echo "Wrong password, redirecting to login to try again...";
                    header('Refresh: 5; URL=login.php');
                    die();
                }
            } else {
                echo "Invalid username, redirecting to login to try again...";
                header('Refresh: 5; URL=login.php');
                die();
            }
        }
        session_start();
        $_SESSION['username'] = $username;
        $_SESSION['profilepic'] = "./profilepics".$username.".";
        header('Location: dashboard.php');
    } catch(PDOException $e) {
        die($e->getMessage());
    }
}
?>