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

    if (empty($department_id)) {
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

        // ตรวจสอบว่ามีสาขาวิชาสังกัดภาควิชานี้หรือไม่
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM major WHERE department_id = :department_id");
        $stmt->execute([':department_id' => $department_id]);
        if ($stmt->fetchColumn() > 0) {
            $response['message'] = 'ไม่สามารถลบภาควิชานี้ได้เนื่องจากมีสาขาวิชาสังกัดอยู่';
            echo json_encode($response);
            exit();
        }

        // ลบภาควิชา
        $stmt = $pdo->prepare("DELETE FROM department WHERE department_id = :department_id");
        $stmt->execute([':department_id' => $department_id]);

        $response['success'] = true;
        $response['message'] = 'ลบภาควิชาสำเร็จ';
    } catch (Exception $e) {
        $response['message'] = 'เกิดข้อผิดพลาด: ' . $e->getMessage();
    }
} else {
    $response['message'] = 'ไม่ได้รับอนุญาตให้เข้าถึง';
}

echo json_encode($response);
?>
