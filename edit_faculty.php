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
    $faculty_id = $_POST['faculty_id'] ?? '';
    $faculty_name = trim($_POST['faculty_name'] ?? '');
    $faculty_description = trim($_POST['faculty_description'] ?? '');

    if (empty($faculty_id) || empty($faculty_name)) {
        $response['message'] = 'ข้อมูลไม่ครบถ้วน';
        echo json_encode($response);
        exit();
    }

    try {
        // ตรวจสอบว่าคณะมีอยู่หรือไม่
        $stmt = $pdo->prepare("SELECT * FROM faculty WHERE faculty_id = :faculty_id");
        $stmt->execute([':faculty_id' => $faculty_id]);
        $faculty = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$faculty) {
            $response['message'] = 'ไม่พบคณะที่ระบุ';
            echo json_encode($response);
            exit();
        }

        // อัปเดตข้อมูลคณะ
        $stmt = $pdo->prepare("UPDATE faculty SET faculty_name = :faculty_name, faculty_description = :faculty_description WHERE faculty_id = :faculty_id");
        $stmt->execute([
            ':faculty_name' => $faculty_name,
            ':faculty_description' => $faculty_description,
            ':faculty_id' => $faculty_id
        ]);

        $response['success'] = true;
        $response['message'] = 'แก้ไขข้อมูลคณะสำเร็จ';
    } catch (Exception $e) {
        $response['message'] = 'เกิดข้อผิดพลาด: ' . $e->getMessage();
    }
} else {
    $response['message'] = 'ไม่ได้รับอนุญาตให้เข้าถึง';
}

echo json_encode($response);
?>
