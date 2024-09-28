<?php
// delete_major.php
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

    if (empty($major_id)) {
        echo json_encode(['success' => false, 'message' => 'ข้อมูลไม่ครบถ้วน']);
        exit();
    }

    try {
        // ตรวจสอบว่าสาขาวิชานี้อยู่ในภาควิชาที่อยู่ในคณะของผู้ใช้หรือไม่
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

        // ลบสาขาวิชา
        $stmt = $pdo->prepare("DELETE FROM major WHERE major_id = :major_id");
        $stmt->execute([
            ':major_id' => $major_id
        ]);

        echo json_encode(['success' => true]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'เกิดข้อผิดพลาด: ' . $e->getMessage()]);
    }
}
?>
