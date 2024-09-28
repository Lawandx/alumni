<?php
session_start();
header('Content-Type: application/json');

require 'db_connect.php';

$response = ['success' => false, 'message' => ''];

// ตรวจสอบสิทธิ์ของผู้ใช้
if (!isset($_SESSION['user_id']) || $_SESSION['access_level'] !== 'admin') {
    $response['message'] = 'ไม่ได้รับอนุญาตให้เข้าถึง';
    echo json_encode($response);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $major_id = $_POST['major_id'] ?? '';

    if (empty($major_id)) {
        $response['message'] = 'ข้อมูลไม่ครบถ้วน';
        echo json_encode($response);
        exit();
    }

    try {
        // ตรวจสอบว่าสาขาวิชามีอยู่หรือไม่
        $stmt = $pdo->prepare("SELECT * FROM major WHERE major_id = :major_id");
        $stmt->execute([':major_id' => $major_id]);
        $major = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$major) {
            $response['message'] = 'ไม่พบสาขาวิชาที่ระบุ';
            echo json_encode($response);
            exit();
        }

        // ลบสาขาวิชา
        $stmt = $pdo->prepare("DELETE FROM major WHERE major_id = :major_id");
        $stmt->execute([':major_id' => $major_id]);

        $response['success'] = true;
        $response['message'] = 'ลบสาขาวิชาสำเร็จ';
    } catch (Exception $e) {
        $response['message'] = 'เกิดข้อผิดพลาด: ' . $e->getMessage();
    }
} else {
    $response['message'] = 'ไม่ได้รับอนุญาตให้เข้าถึง';
}

echo json_encode($response);
?>
