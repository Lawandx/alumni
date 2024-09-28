<?php
// edit_major.php
session_start();
require '../db_connect.php';

// ตรวจสอบว่าผู้ใช้ได้เข้าสู่ระบบและมีสิทธิ์หรือไม่
if (!isset($_SESSION['user_id']) || $_SESSION['access_level'] !== 'faculty') {
    echo json_encode(['success' => false, 'message' => 'คุณไม่มีสิทธิ์เข้าถึงหน้านี้']);
    exit();
}

$faculty_id = $_SESSION['faculty_id'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $major_id = $_POST['major_id'] ?? '';
    $major_name = $_POST['major_name'] ?? '';
    $major_description = $_POST['major_description'] ?? '';
    $department_id = $_POST['department_id'] ?? '';

    if (empty($major_name) || empty($major_id) || empty($department_id)) {
        echo json_encode(['success' => false, 'message' => 'ข้อมูลไม่ครบถ้วน']);
        exit();
    }

    try {
        // ตรวจสอบว่าภาควิชาที่เลือกอยู่ในคณะของผู้ใช้หรือไม่
        $stmt = $pdo->prepare("SELECT * FROM department WHERE department_id = :department_id AND faculty_id = :faculty_id");
        $stmt->execute([
            ':department_id' => $department_id,
            ':faculty_id' => $faculty_id
        ]);
        $department = $stmt->fetch();

        if (!$department) {
            echo json_encode(['success' => false, 'message' => 'ภาควิชาที่เลือกไม่ถูกต้อง']);
            exit();
        }

        // ตรวจสอบว่าสาขาวิชานี้อยู่ในคณะของผู้ใช้หรือไม่ (ไม่จำเป็นต้องอยู่ในภาควิชาเดิม)
        $stmt = $pdo->prepare("
            SELECT m.*, d.faculty_id 
            FROM major m 
            JOIN department d ON m.department_id = d.department_id 
            WHERE m.major_id = :major_id AND d.faculty_id = :faculty_id
        ");
        $stmt->execute([
            ':major_id' => $major_id,
            ':faculty_id' => $faculty_id
        ]);
        $major = $stmt->fetch();

        if (!$major) {
            echo json_encode(['success' => false, 'message' => 'ไม่พบสาขาวิชาที่ระบุ']);
            exit();
        }

        // อัปเดตข้อมูลสาขาวิชา รวมถึง department_id
        $stmt = $pdo->prepare("UPDATE major SET major_name = :name, major_description = :description, department_id = :department_id WHERE major_id = :major_id");
        $stmt->execute([
            ':name' => $major_name,
            ':description' => $major_description,
            ':department_id' => $department_id,
            ':major_id' => $major_id
        ]);

        echo json_encode(['success' => true]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'เกิดข้อผิดพลาด: ' . $e->getMessage()]);
    }
}
?>
