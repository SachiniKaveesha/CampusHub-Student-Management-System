<?php
session_start();
include('../connection.php'); 

if (isset($_POST['login_btn'])) {
    
    $email = $_POST['admin_email'];
    $password = $_POST['admin_password'];

    $query = "SELECT * FROM `admin` WHERE `email`='$email' AND `password`='$password'";
    $result = Database::search($query);

    if ($result && $result->num_rows == 1) {
        $_SESSION['admin_logged'] = true;
        $_SESSION['admin_email'] = $email;
        
        header('Location: admin-dashboard.php');
        exit();
    } else {
        header('Location: admin-login.php?error=1');
        exit();
    }
} else {
    header('Location: admin-login.php');
    exit();
}
?>