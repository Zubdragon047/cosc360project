<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['username'])) {
        if ($_POST['username'] == "" || $_POST['username'] == null) {
            die("Username is required.");
        } else {
            $username = $_POST["username"];
        }
    } else {
        die("Username not set.");
    }
    if (isset($_POST['password'])) {
        if ($_POST['password'] == "" || $_POST['password'] == null) {
            die("Password is required.");
        } else {
            $password = $_POST["password"];
        }
    } else {
        die("Password not set.");
    }
    if (isset($_POST['email'])) {
        if ($_POST['email'] == "" || $_POST['email'] == null) {
            die("Email is required.");
        } else {
            $email = $_POST["email"];
        }
    } else {
        die("Email not set.");
    }
    if (isset($_POST['firstname'])) {
        $firstname = $_POST['firstname'];
        if ($firstname == "") $firstname = " ";
    }
    if (isset($_POST['lastname'])) {
        $lastname = $_POST['lastname'];
        if ($lastname == "") $lastname = " ";
    }

    try {
        require_once('protected/config.php');
        $pdo = new PDO(DBCONNSTRING, DBUSER, DBPASS);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $sql = "select * from users";
        $result = $pdo->query($sql);
        while ($row = $result->fetch()) {
            if ($row['username'] == $username) {
                die("Username already exists.");
            }
        }
        $data = [
            'username' => $username,
            'password' => $password,
            'email' => $email,
            'firstname'=> $firstname,
            'lastname'=> $lastname
        ];
        $sql = "insert into users(username,password,email,firstname,lastname) values(:username,:password,:email,:firstname,:lastname)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($data);
        echo "Registration successful, redirecting to login page...";
        header('Refresh: 5; URL=login.html');
    } catch(PDOException $e) {
        die($e->getMessage());
    }
}
?>