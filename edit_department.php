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
    $department_id = $_POST['department_id'] ?? '';
    $department_name = trim($_POST['department_name'] ?? '');
    $department_description = trim($_POST['department_description'] ?? '');
    $faculty_id = $_POST['faculty_id'] ?? '';

    if (empty($department_id) || empty($department_name) || empty($faculty_id)) {
        $response['message'] = 'ข้อมูลไม่ครบถ้วน';
        echo json_encode($response);
        exit();
    }

    try {
        // ตรวจสอบว่าภาควิชามีอยู่หรือไม่
        $stmt = $pdo->prepare("SELECT * FROM department WHERE department_id = :department_id");
        $stmt->execute([':department_id' => $department_id]);
        $department = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$department) {
            $response['message'] = 'ไม่พบภาควิชาที่ระบุ';
            echo json_encode($response);
            exit();
        }

        // ตรวจสอบว่าคณะมีอยู่หรือไม่
        $stmt = $pdo->prepare("SELECT * FROM faculty WHERE faculty_id = :faculty_id");
        $stmt->execute([':faculty_id' => $faculty_id]);
        $faculty = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$faculty) {
            $response['message'] = 'ไม่พบคณะที่ระบุ';
            echo json_encode($response);
            exit();
        }

        // อัปเดตข้อมูลภาควิชา
        $stmt = $pdo->prepare("UPDATE department SET department_name = :department_name, department_description = :department_description, faculty_id = :faculty_id WHERE department_id = :department_id");
        $stmt->execute([
            ':department_name' => $department_name,
            ':department_description' => $department_description,
            ':faculty_id' => $faculty_id,
            ':department_id' => $department_id
        ]);

        $response['success'] = true;
        $response['message'] = 'แก้ไขข้อมูลภาควิชาสำเร็จ';
    } catch (Exception $e) {
        $response['message'] = 'เกิดข้อผิดพลาด: ' . $e->getMessage();
    }
} else {
    $response['message'] = 'ไม่ได้รับอนุญาตให้เข้าถึง';
}

echo json_encode($response);
?>
