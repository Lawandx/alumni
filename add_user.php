<?php
require 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $access_level = $_POST['access_level'];

    // ตรวจสอบว่า username หรือ email ซ้ำกันหรือไม่
    $stmt = $pdo->prepare("SELECT * FROM User WHERE username = :username OR email = :email");
    $stmt->bindParam(':username', $username);
    $stmt->bindParam(':email', $email);
    $stmt->execute();
    $existing_user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($existing_user) {
        echo "<p style='color:red;text-align:center;'>Username or Email already exists</p>";
    } else {
        // ทำการ Hash รหัสผ่าน
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $password_part = substr($password, 0, 3); // เก็บส่วนหนึ่งของรหัสผ่าน

        // เพิ่มข้อมูลผู้ใช้ใหม่ลงในฐานข้อมูล
        $stmt = $pdo->prepare("INSERT INTO User (username, email, password_hash, password_part, access_level) VALUES (:username, :email, :hashed_password, :password_part, :access_level)");
        $stmt->bindParam(':username', $username);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':hashed_password', $hashed_password);
        $stmt->bindParam(':password_part', $password_part);
        $stmt->bindParam(':access_level', $access_level);

        if ($stmt->execute()) {
            echo "<p style='color:green;text-align:center;'>User added successfully!</p>";
        } else {
            echo "<p style='color:red;text-align:center;'>Error adding user</p>";
        }
    }
}
?>
