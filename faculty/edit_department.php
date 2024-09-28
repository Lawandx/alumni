<?php
// edit_department.php
session_start();
require '../db_connect.php';

// ตรวจสอบว่าผู้ใช้ได้เข้าสู่ระบบและมีสิทธิ์หรือไม่
if (!isset($_SESSION['user_id']) || $_SESSION['access_level'] !== 'faculty') {
    echo json_encode(['success' => false, 'message' => 'คุณไม่มีสิทธิ์เข้าถึงหน้านี้']);
    exit();
}

$faculty_id = $_SESSION['faculty_id'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $department_id = $_POST['department_id'] ?? '';
    $department_name = $_POST['department_name'] ?? '';
    $department_description = $_POST['department_description'] ?? '';

    if (empty($department_name) || empty($department_id)) {
        echo json_encode(['success' => false, 'message' => 'ข้อมูลไม่ครบถ้วน']);
        exit();
    }

    try {
        // ตรวจสอบว่าภาควิชานี้อยู่ในคณะของผู้ใช้หรือไม่
        $stmt = $pdo->prepare("SELECT * FROM department WHERE department_id = :department_id AND faculty_id = :faculty_id");
        $stmt->execute([
            ':department_id' => $department_id,
            ':faculty_id' => $faculty_id
        ]);
        $department = $stmt->fetch();

        if (!$department) {
            echo json_encode(['success' => false, 'message' => 'ไม่พบภาควิชาที่ระบุ']);
            exit();
        }

        // อัปเดตข้อมูลภาควิชา
        $stmt = $pdo->prepare("UPDATE department SET department_name = :name, department_description = :description WHERE department_id = :department_id");
        $stmt->execute([
            ':name' => $department_name,
            ':description' => $department_description,
            ':department_id' => $department_id
        ]);

        echo json_encode(['success' => true]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'เกิดข้อผิดพลาด: ' . $e->getMessage()]);
    }
}
?>
