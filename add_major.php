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
    $major_name = trim($_POST['major_name'] ?? '');
    $major_description = trim($_POST['major_description'] ?? '');

    if (empty($department_id) || empty($major_name)) {
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

        // ตรวจสอบว่ามีชื่อสาขาวิชานี้อยู่แล้วหรือไม่ในภาควิชาเดียวกัน
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM major WHERE major_name = :major_name AND department_id = :department_id");
        $stmt->execute([':major_name' => $major_name, ':department_id' => $department_id]);
        if ($stmt->fetchColumn() > 0) {
            $response['message'] = 'มีชื่อสาขาวิชานี้อยู่แล้วในภาควิชานี้';
            echo json_encode($response);
            exit();
        }

        // เพิ่มสาขาวิชาใหม่
        $stmt = $pdo->prepare("INSERT INTO major (major_name, major_description, department_id) VALUES (:major_name, :major_description, :department_id)");
        $stmt->execute([
            ':major_name' => $major_name,
            ':major_description' => $major_description,
            ':department_id' => $department_id
        ]);

        $response['success'] = true;
        $response['message'] = 'เพิ่มสาขาวิชาใหม่สำเร็จ';
    } catch (Exception $e) {
        $response['message'] = 'เกิดข้อผิดพลาด: ' . $e->getMessage();
    }
} else {
    $response['message'] = 'ไม่ได้รับอนุญาตให้เข้าถึง';
}

echo json_encode($response);
?>
