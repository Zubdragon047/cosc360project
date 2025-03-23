<?php
session_start();
$username = $_SESSION['username'];

if ($_SERVER['REQUEST_METHOD'] == "POST") {
    if (isset($_POST["book-title"])) {
        if ($_POST["book-title"] == null || $_POST["book-title"] == "") {
            die("Title field is required.");
        }
        else {
            $title = $_POST["book-title"];
        }
    }
    if (isset($_POST["book-description"])) {
        if ($_POST["book-description"] == null || $_POST["book-description"] == ""){
            die("Description field is required");
        }
        else {
            $description = $_POST["book-description"];
        }
    }
    if (isset($_POST["book-category"])) {
        if ($_POST["book-category"] == null || $_POST["book-category"] == "" || $_POST["book-category"] == " "){
            die("Category field is required");
        }
        else {
            $category = $_POST["book-category"];
        }
    }

    $max_file_size = 10000000;
    $valid_ext = array("jpg", "bmp", "gif");
    $valid_mime = array("image/jpeg","image/bmp","image/gif");
    $exploded = explode(".", $_FILES["book-picture"]["name"]);
    $ext = end($exploded);
    $needimagepath = false;
    if ($_FILES['book-picture']['error'] == 0) {
        if ($_FILES["book-picture"]["size"] > $max_file_size) {
            die("File size larger than 10mb.");
        }
        if (!in_array($_FILES["book-picture"]["type"], $valid_mime) || !in_array($ext, $valid_ext)) {
            die("Invalid file type.");
        }
        if (!move_uploaded_file($_FILES["book-picture"]["tmp_name"], "./bookpics/".$username.".".$ext)) {
            die("Unable to move file to destination folder.");
        }
        $needimagepath = true;
    }

    try {
        require_once('protected/config.php');
        $pdo = new PDO(DBCONNSTRING, DBUSER, DBPASS);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $data = [
            'username' => $username,
            'title' => $title,
            'description' => $description,
            'category'=> $category
        ];
        $sql = "insert into books(username,title,description,category) values(:username,:title,:description,:category)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($data);
        $sql = "select id from books order by id desc limit 1";
        $result = $pdo->query($sql);
        $row = $result->fetch();
        $num = $row['id'];
        if ($needimagepath) {
            $sql = "update books set coverimage=? where id=?";
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(1, "./bookpics/".$num.".".$ext);
            $stmt->bindValue(2,$num);
            $stmt->execute();
        }
        else {
            $sql = "update books set coverimage=? where id=?";
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(1, "./images/book.png");
            $stmt->bindValue(2,$num);
            $stmt->execute();
        }
        echo "Book added succesfully, redirecting to dashboard page...";
        header('Refresh: 5; URL=dashboard.php');
    } catch(PDOException $e) {
        die($e->getMessage());
    }
}
?>