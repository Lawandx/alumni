<?php
// add_department.php
session_start();
require '../db_connect.php';

// ตรวจสอบว่าผู้ใช้ได้เข้าสู่ระบบและมีสิทธิ์หรือไม่
if (!isset($_SESSION['user_id']) || $_SESSION['access_level'] !== 'faculty') {
    echo json_encode(['success' => false, 'message' => 'คุณไม่มีสิทธิ์เข้าถึงหน้านี้']);
    exit();
}

$faculty_id = $_SESSION['faculty_id'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $department_name = $_POST['department_name'] ?? '';
    $department_description = $_POST['department_description'] ?? '';

    if (empty($department_name)) {
        echo json_encode(['success' => false, 'message' => 'กรุณากรอกชื่อภาควิชา']);
        exit();
    }

    try {
        $stmt = $pdo->prepare("INSERT INTO department (department_name, department_description, faculty_id) VALUES (:name, :description, :faculty_id)");
        $stmt->execute([
            ':name' => $department_name,
            ':description' => $department_description,
            ':faculty_id' => $faculty_id
        ]);
        echo json_encode(['success' => true]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'เกิดข้อผิดพลาด: ' . $e->getMessage()]);
    }
}
?>
