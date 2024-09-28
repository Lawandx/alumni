<?php
// edit_award_history.php
session_start();

header('Content-Type: application/json'); // กำหนดให้ PHP ส่งข้อมูลเป็น JSON

require 'db_connect.php'; 

$response = ['status' => 'error', 'message' => 'เกิดข้อผิดพลาดในการแก้ไขประวัติการรับรางวัล'];

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

        // รับข้อมูลจากฟอร์มและทำความสะอาดข้อมูล
        $award_id = filter_input(INPUT_POST, 'award_id', FILTER_VALIDATE_INT);
        $award_name = trim(filter_input(INPUT_POST, 'award_name', FILTER_SANITIZE_STRING));
        $award_date = filter_input(INPUT_POST, 'award_date', FILTER_SANITIZE_STRING);
        $awarding_organization = trim(filter_input(INPUT_POST, 'awarding_organization', FILTER_SANITIZE_STRING));
        $description = trim(filter_input(INPUT_POST, 'description', FILTER_SANITIZE_STRING));

        // ตรวจสอบข้อมูลที่จำเป็น
        if (!$award_id || empty($award_name) || empty($award_date) || empty($awarding_organization)) {
            sendResponse('error', 'กรุณากรอกข้อมูลให้ครบถ้วน');
        }

        // อัปเดตประวัติการรับรางวัลในฐานข้อมูล
        $stmt = $pdo->prepare("UPDATE awardhistory SET 
            award_name = :award_name, 
            award_date = :award_date, 
            awarding_organization = :awarding_organization, 
            description = :description 
            WHERE award_id = :award_id");
        $stmt->bindParam(':award_name', $award_name);
        $stmt->bindParam(':award_date', $award_date);
        $stmt->bindParam(':awarding_organization', $awarding_organization);
        $stmt->bindParam(':description', $description);
        $stmt->bindParam(':award_id', $award_id, PDO::PARAM_INT);

        if ($stmt->execute()) {
            sendResponse('success', 'แก้ไขประวัติการรับรางวัลสำเร็จ');
        } else {
            sendResponse('error', 'ไม่สามารถแก้ไขประวัติการรับรางวัลได้');
        }
    } else {
        sendResponse('error', 'Invalid request method');
    }
} catch (Exception $e) {
    error_log($e->getMessage()); 
    sendResponse('error', 'ข้อผิดพลาด: ' . $e->getMessage());
}
?>
