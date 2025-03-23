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
    }
    if (isset($_POST['lastname'])) {
        $lastname = $_POST['lastname'];
    }
    
    $max_file_size = 10000000;
    $valid_ext = array("jpg", "bmp", "gif");
    $valid_mime = array("image/jpeg","image/bmp","image/gif");
    $exploded = explode(".", $_FILES["profilepic"]["name"]);
    $ext = end($exploded);
    if ($_FILES['profilepic']['error'] == 0) {
        if ($_FILES["profilepic"]["size"] > $max_file_size) {
            die("File size larger than 10mb.");
        }
        if (!in_array($_FILES["profilepic"]["type"], $valid_mime) || !in_array($ext, $valid_ext)) {
            die("Invalid file type.");
        }
        if (!move_uploaded_file($_FILES["profilepic"]["tmp_name"], "./profilepics/".$username.".".$ext)) {
            die("Unable to move file to destination folder.");
        }
        $profilepic = "./profilepics/".$username.".".$ext;
    }
    else {
        $profilepic = "./images/emptyprofilepic.jpg";
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
            'lastname'=> $lastname,
            'profilepic' => $profilepic,
            'type' => 'user'
        ];
        $sql = "insert into users values(:username,:password,:email,:firstname,:lastname,:profilepic,:type)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($data);
        echo "Registration successful, redirecting to login page...";
        header('Refresh: 5; URL=login.php');
    } catch(PDOException $e) {
        die($e->getMessage());
    }
}
?>