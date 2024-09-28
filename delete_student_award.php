<?php
// delete_student_award.php
session_start();

header('Content-Type: application/json'); // กำหนดให้ PHP ส่งข้อมูลเป็น JSON

require 'db_connect.php'; 

$response = ['status' => 'error', 'message' => 'เกิดข้อผิดพลาดในการลบรางวัลนักเรียน'];

function sendResponse($status, $message) {
    echo json_encode(['status' => $status, 'message' => $message]);
    exit();
}

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // ตรวจสอบสิทธิ์ผู้ใช้
        if (!isset($_SESSION['user_id']) || !in_array($_SESSION['access_level'], ['admin', 'faculty','department','major'])) {
            sendResponse('error', 'คุณไม่มีสิทธิ์ในการดำเนินการนี้');
        }

        // รับข้อมูลจาก AJAX
        $data = json_decode(file_get_contents('php://input'), true);
        $award_id = isset($data['award_id']) ? intval($data['award_id']) : 0;

        if ($award_id <= 0) {
            sendResponse('error', 'ไม่มีรหัสรางวัลนักเรียน');
        }

        // ตรวจสอบว่ารางวัลนักเรียนมีอยู่จริง
        $stmt = $pdo->prepare("SELECT * FROM studentaward WHERE award_id = :award_id");
        $stmt->bindParam(':award_id', $award_id, PDO::PARAM_INT);
        $stmt->execute();
        $award = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$award) {
            sendResponse('error', 'ไม่พบรางวัลนักเรียนที่ต้องการลบ');
        }

        // ลบรางวัลนักเรียน
        $stmt = $pdo->prepare("DELETE FROM studentaward WHERE award_id = :award_id");
        $stmt->bindParam(':award_id', $award_id, PDO::PARAM_INT);
        if ($stmt->execute()) {
            sendResponse('success', 'ลบรางวัลนักเรียนสำเร็จ');
        } else {
            sendResponse('error', 'ไม่สามารถลบรางวัลนักเรียนได้');
        }
    } else {
        sendResponse('error', 'Invalid request method');
    }
} catch (Exception $e) {
    error_log($e->getMessage()); 
    sendResponse('error', 'ข้อผิดพลาด: ' . $e->getMessage());
}
?>
