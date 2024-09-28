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
    $major_name = trim($_POST['major_name'] ?? '');
    $major_description = trim($_POST['major_description'] ?? '');
    $department_id = $_POST['department_id'] ?? '';

    if (empty($major_id) || empty($major_name) || empty($department_id)) {
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

        // ตรวจสอบว่าภาควิชามีอยู่หรือไม่
        $stmt = $pdo->prepare("SELECT * FROM department WHERE department_id = :department_id");
        $stmt->execute([':department_id' => $department_id]);
        $department = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$department) {
            $response['message'] = 'ไม่พบภาควิชาที่ระบุ';
            echo json_encode($response);
            exit();
        }

        // อัปเดตข้อมูลสาขาวิชา
        $stmt = $pdo->prepare("UPDATE major SET major_name = :major_name, major_description = :major_description, department_id = :department_id WHERE major_id = :major_id");
        $stmt->execute([
            ':major_name' => $major_name,
            ':major_description' => $major_description,
            ':department_id' => $department_id,
            ':major_id' => $major_id
        ]);

        $response['success'] = true;
        $response['message'] = 'แก้ไขข้อมูลสาขาวิชาสำเร็จ';
    } catch (Exception $e) {
        $response['message'] = 'เกิดข้อผิดพลาด: ' . $e->getMessage();
    }
} else {
    $response['message'] = 'ไม่ได้รับอนุญาตให้เข้าถึง';
}

echo json_encode($response);
?>
