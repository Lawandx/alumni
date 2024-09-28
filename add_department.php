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
    $department_name = trim($_POST['department_name'] ?? '');
    $department_description = trim($_POST['department_description'] ?? '');

    if (empty($faculty_id) || empty($department_name)) {
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

        // ตรวจสอบว่ามีชื่อภาควิชานี้อยู่แล้วหรือไม่ในคณะเดียวกัน
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM department WHERE department_name = :department_name AND faculty_id = :faculty_id");
        $stmt->execute([':department_name' => $department_name, ':faculty_id' => $faculty_id]);
        if ($stmt->fetchColumn() > 0) {
            $response['message'] = 'มีชื่อภาควิชานี้อยู่แล้วในคณะนี้';
            echo json_encode($response);
            exit();
        }

        // เพิ่มภาควิชาใหม่
        $stmt = $pdo->prepare("INSERT INTO department (department_name, department_description, faculty_id) VALUES (:department_name, :department_description, :faculty_id)");
        $stmt->execute([
            ':department_name' => $department_name,
            ':department_description' => $department_description,
            ':faculty_id' => $faculty_id
        ]);

        $response['success'] = true;
        $response['message'] = 'เพิ่มภาควิชาใหม่สำเร็จ';
    } catch (Exception $e) {
        $response['message'] = 'เกิดข้อผิดพลาด: ' . $e->getMessage();
    }
} else {
    $response['message'] = 'ไม่ได้รับอนุญาตให้เข้าถึง';
}

echo json_encode($response);
?>
