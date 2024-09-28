<?php
// add_award_history.php
session_start();

header('Content-Type: application/json'); // กำหนดให้ PHP ส่งข้อมูลเป็น JSON

require 'db_connect.php'; 

$response = ['status' => 'error', 'message' => 'เกิดข้อผิดพลาดในการเพิ่มประวัติการรับรางวัล'];

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
        $person_id = filter_input(INPUT_POST, 'person_id', FILTER_VALIDATE_INT);
        $award_name = trim(filter_input(INPUT_POST, 'award_name', FILTER_SANITIZE_STRING));
        $award_date = filter_input(INPUT_POST, 'award_date', FILTER_SANITIZE_STRING);
        $awarding_organization = trim(filter_input(INPUT_POST, 'awarding_organization', FILTER_SANITIZE_STRING));
        $description = trim(filter_input(INPUT_POST, 'description', FILTER_SANITIZE_STRING));

        // ตรวจสอบข้อมูลที่จำเป็น
        if (!$person_id || empty($award_name) || empty($award_date) || empty($awarding_organization)) {
            sendResponse('error', 'กรุณากรอกข้อมูลให้ครบถ้วน');
        }

        // เพิ่มข้อมูลประวัติการรับรางวัลลงในฐานข้อมูล
        $stmt = $pdo->prepare("INSERT INTO awardhistory (person_id, award_name, award_date, awarding_organization, description) VALUES (:person_id, :award_name, :award_date, :awarding_organization, :description)");
        $stmt->bindParam(':person_id', $person_id, PDO::PARAM_INT);
        $stmt->bindParam(':award_name', $award_name);
        $stmt->bindParam(':award_date', $award_date);
        $stmt->bindParam(':awarding_organization', $awarding_organization);
        $stmt->bindParam(':description', $description);

        if ($stmt->execute()) {
            sendResponse('success', 'เพิ่มประวัติการรับรางวัลสำเร็จ');
        } else {
            sendResponse('error', 'ไม่สามารถเพิ่มประวัติการรับรางวัลได้');
        }
    } else {
        sendResponse('error', 'Invalid request method');
    }
} catch (Exception $e) {
    error_log($e->getMessage()); 
    sendResponse('error', 'ข้อผิดพลาด: ' . $e->getMessage());
}
?>
