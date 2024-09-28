<?php
session_start();
header('Content-Type: application/json');

require 'db_connect.php'; // เชื่อมต่อฐานข้อมูล

$response = ['success' => false, 'password_part' => ''];

if (!isset($_SESSION['user_id']) || $_SESSION['access_level'] !== 'admin') {
    $response['message'] = 'ไม่ได้รับอนุญาตให้เข้าถึง';
    echo json_encode($response);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['password'])) {
    $input_password = $_POST['password'];

    // ดึงข้อมูล password_hash ของผู้ใช้ admin
    $stmt = $pdo->prepare("SELECT password_hash FROM user WHERE user_id = :user_id");
    $stmt->execute([':user_id' => $_SESSION['user_id']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($input_password, $user['password_hash'])) {
        // ดึงข้อมูล password_part ของผู้ใช้ที่ล็อกอินเข้ามา
        $stmt = $pdo->prepare("SELECT password_part FROM user WHERE user_id = :user_id");
        $stmt->execute([':user_id' => $_SESSION['user_id']]);
        $current_user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($current_user) {
            $response['success'] = true;
            $response['password_part'] = htmlspecialchars($current_user['password_part']);
        } else {
            $response['message'] = 'ไม่พบข้อมูลผู้ใช้';
        }
    } else {
        $response['message'] = 'รหัสผ่านไม่ถูกต้อง';
    }
} else {
    $response['message'] = 'ข้อมูลไม่ครบถ้วน';
}

echo json_encode($response);
?>
