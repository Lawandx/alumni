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
    $faculty_name = trim($_POST['faculty_name'] ?? '');
    $faculty_description = trim($_POST['faculty_description'] ?? '');

    if (empty($faculty_name)) {
        $response['message'] = 'กรุณากรอกชื่อคณะ';
        echo json_encode($response);
        exit();
    }

    try {
        // ตรวจสอบว่ามีชื่อคณะนี้อยู่แล้วหรือไม่
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM faculty WHERE faculty_name = :faculty_name");
        $stmt->execute([':faculty_name' => $faculty_name]);
        if ($stmt->fetchColumn() > 0) {
            $response['message'] = 'มีชื่อคณะนี้อยู่แล้ว';
            echo json_encode($response);
            exit();
        }

        // เพิ่มคณะใหม่
        $stmt = $pdo->prepare("INSERT INTO faculty (faculty_name, faculty_description) VALUES (:faculty_name, :faculty_description)");
        $stmt->execute([
            ':faculty_name' => $faculty_name,
            ':faculty_description' => $faculty_description
        ]);

        $response['success'] = true;
        $response['message'] = 'เพิ่มคณะใหม่สำเร็จ';
    } catch (Exception $e) {
        $response['message'] = 'เกิดข้อผิดพลาด: ' . $e->getMessage();
    }
} else {
    $response['message'] = 'ไม่ได้รับอนุญาตให้เข้าถึง';
}

echo json_encode($response);
?>
