<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['username'])) {
        $username = $_POST['username'];
    }
    if (isset($_POST['password'])) {
        $password = $_POST['password'];
        $pass_hash = md5($password);
    }

    try {
        require_once('protected/config.php');
        $pdo = new PDO(DBCONNSTRING, DBUSER, DBPASS);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $sql = "select * from users";
        $result = $pdo->query($sql);
        while ($row = $result->fetch()) {
            if ($row['username'] == $username) {
                if ($row['password'] == $pass_hash) {
                    session_start();
                    $_SESSION['username'] = $username;
                    $_SESSION['password'] = $pass_hash;
                    $_SESSION['email'] = $row['email'];
                    $_SESSION['firstname'] = $row['firstname'];
                    $_SESSION['lastname'] = $row['lastname'];
                    $_SESSION['profilepic'] = $row['profilepic'];
                    $_SESSION['type'] = $row['type'];
                    
                    // Redirect admin users to admin dashboard, regular users to dashboard
                    if ($row['type'] === 'admin') {
                        header('Location: admin.php#_top');
                    } else {
                        header('Location: dashboard.php');
                    }
                    die();
                } else {
                    echo "Wrong password, redirecting to login to try again...";
                    header('Refresh: 5; URL=login.php');
                    die();
                }
            }
        }
        echo "Invalid username, redirecting to login to try again...";
        header('Refresh: 5; URL=login.php');
        die();
    } catch(PDOException $e) {
        die($e->getMessage());
    }
}
?>