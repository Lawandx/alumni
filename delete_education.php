<?php
// delete_education.php
session_start();


// กำหนด Content-Type เป็น JSON
header('Content-Type: application/json');

// ฟังก์ชันส่ง JSON Response และหยุดการทำงาน
function sendResponse($status, $message) {
    echo json_encode(['status' => $status, 'message' => $message]);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // ตรวจสอบสิทธิ์ผู้ใช้
    if (!isset($_SESSION['user_id']) || !in_array($_SESSION['access_level'], ['admin', 'faculty','department','major'])) {
        sendResponse('error', 'คุณไม่มีสิทธิ์ในการดำเนินการนี้');
    }

    require 'db_connect.php';

    // รับข้อมูลจาก AJAX
    $data = json_decode(file_get_contents('php://input'), true);
    $education_id = isset($data['education_id']) ? intval($data['education_id']) : 0;

    if ($education_id <= 0) {
        sendResponse('error', 'ไม่มีรหัสข้อมูลการศึกษา');
    }

    try {
        // ตรวจสอบว่าข้อมูลการศึกษามีอยู่จริง
        $stmt = $pdo->prepare("SELECT * FROM education WHERE education_id = :education_id");
        $stmt->bindParam(':education_id', $education_id, PDO::PARAM_INT);
        $stmt->execute();
        $education = $stmt->fetch();

        if (!$education) {
            sendResponse('error', 'ไม่พบข้อมูลการศึกษาที่ต้องการลบ');
        }

        // ลบข้อมูลการศึกษา
        $stmt = $pdo->prepare("DELETE FROM education WHERE education_id = :education_id");
        $stmt->bindParam(':education_id', $education_id, PDO::PARAM_INT);
        $stmt->execute();

        sendResponse('success', 'ลบข้อมูลการศึกษาเรียบร้อยแล้ว');
    } catch (Exception $e) {
        // บันทึกข้อผิดพลาดใน log
        error_log($e->getMessage());
        sendResponse('error', 'เกิดข้อผิดพลาดในการลบข้อมูลการศึกษา');
    }
} else {
    sendResponse('error', 'Invalid request method');
}
?>
