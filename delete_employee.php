<?php
session_start();
header('Content-Type: application/json');

require 'db_connect.php'; // เชื่อมต่อฐานข้อมูล

$response = ['success' => false, 'message' => ''];

// ตรวจสอบสิทธิ์ของผู้ใช้
if (!isset($_SESSION['user_id']) || $_SESSION['access_level'] !== 'admin') {
    $response['message'] = 'ไม่ได้รับอนุญาตให้เข้าถึง';
    echo json_encode($response);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // รับ user_id จากฟอร์ม
    $user_id = $_POST['user_id'] ?? '';

    // ตรวจสอบว่า user_id เป็นตัวเลข
    if (!ctype_digit($user_id)) {
        $response['message'] = 'รหัสผู้ใช้ไม่ถูกต้อง';
        echo json_encode($response);
        exit();
    }

    try {
        // ตรวจสอบว่าผู้ใช้มีอยู่ในระบบ
        $stmt = $pdo->prepare("SELECT username FROM user WHERE user_id = :user_id");
        $stmt->execute([':user_id' => $user_id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            $response['message'] = 'ไม่พบผู้ใช้ที่ระบุ';
            echo json_encode($response);
            exit();
        }

        // ลบผู้ใช้จากฐานข้อมูล
        $stmt = $pdo->prepare("DELETE FROM user WHERE user_id = :user_id");
        $stmt->execute([':user_id' => $user_id]);

        $response['success'] = true;
        $response['message'] = 'ลบผู้ใช้สำเร็จ';
    } catch (Exception $e) {
        $response['message'] = 'เกิดข้อผิดพลาด: ' . $e->getMessage();
    }
} else {
    $response['message'] = 'ไม่ได้รับอนุญาตให้เข้าถึง';
}

echo json_encode($response);
?>
