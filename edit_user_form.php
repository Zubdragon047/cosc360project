<?php
session_start();
if (isset($_SESSION['username'])) {
    $oldusername = $_SESSION['username'];
} else {
    die("Must be logged in to edit account.");
}
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['edit-username'])) {
        if ($_POST['edit-username'] == "" || $_POST['edit-username'] == null) {
            die("Username is required.");
        } else {
            $newusername = $_POST["edit-username"];
        }
    } else {
        die("Username not set.");
    }
    if (isset($_POST['edit-old-password'])) {
        if ($_POST['edit-old-password'] == "" || $_POST['edit-old-password'] == null || md5($_POST['edit-old-password']) != $_SESSION['password']) {
            die("Current password is required.");
        } else {
            $oldpassword = $_POST["edit-old-password"];
            $oldpass_hash = md5($oldpassword);
            $newpassword = $oldpassword;
            $newpass_hash = md5($newpassword);
        }
    } else {
        die("Current password not set.");
    }
    if (isset($_POST['confirm-new-password'])) {
        if ($_POST['confirm-new-password'] != "" && $_POST['confirm-new-password'] != null) {
            $confirmpassword = $_POST['confirm-new-password'];
        }
    }
    if (isset($_POST['edit-new-password'])) {
        if ($_POST['edit-new-password'] != "" && $_POST['edit-new-password'] != null) {
            if ($_POST['edit-new-password'] == $confirmpassword) {
                $newpassword = $confirmpassword;
                $newpass_hash = md5($confirmpassword);
            }
        }
    }
    if (isset($_POST['edit-email'])) {
        if ($_POST['edit-email'] == "" || $_POST['edit-email'] == null) {
            die("Email is required.");
        } else {
            $newemail = $_POST["edit-email"];
        }
    } else {
        die("Email not set.");
    }
    if (isset($_POST['edit-firstname'])) {
        $newfirstname = $_POST['edit-firstname'];
    }
    if (isset($_POST['edit-lastname'])) {
        $newlastname = $_POST['edit-lastname'];
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
        if (!move_uploaded_file($_FILES["profilepic"]["tmp_name"], "./profilepics/".$newusername.".".$ext)) {
            die("Unable to move file to destination folder.");
        }
        $profilepic = "./profilepics/".$newusername.".".$ext;
    } else {
        $profilepic = $_SESSION['profilepic'];
    }

    try {
        require_once('protected/config.php');
        $pdo = new PDO(DBCONNSTRING, DBUSER, DBPASS);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $data = [
            'username' => $newusername,
            'password' => $newpass_hash,
            'email' => $newemail,
            'firstname'=> $newfirstname,
            'lastname'=> $newlastname,
            'profilepic' => $profilepic,
            'currentuser' => $oldusername
        ];
        $sql = "UPDATE users
                SET username=:username, password=:password, email=:email, firstname=:firstname, lastname=:lastname, profilepic=:profilepic
                WHERE username=:currentuser";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($data);
        $_SESSION['username'] = $newusername;
        $_SESSION['password'] = $newpassword;
        $_SESSION['email'] = $newemail;
        $_SESSION['firstname'] = $newfirstname;
        $_SESSION['lastname'] = $newlastname;
        $_SESSION['profilepic'] = $profilepic;
        echo "Edit account successful, redirecting to dashboard page...";
        header('Refresh: 5; URL=dashboard.php');
    } catch(PDOException $e) {
        die($e->getMessage());
    }
}
?>