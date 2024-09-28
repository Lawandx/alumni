<?php
// edit_student_award.php
session_start();

header('Content-Type: application/json'); // กำหนดให้ PHP ส่งข้อมูลเป็น JSON

require 'db_connect.php'; 

$response = ['status' => 'error', 'message' => 'เกิดข้อผิดพลาดในการแก้ไขรางวัลนักเรียน'];

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
        $student_award_name = trim(filter_input(INPUT_POST, 'student_award_name', FILTER_SANITIZE_STRING));
        $student_award_date = filter_input(INPUT_POST, 'student_award_date', FILTER_SANITIZE_STRING);
        $faculty = trim(filter_input(INPUT_POST, 'faculty', FILTER_SANITIZE_STRING));
        $major = trim(filter_input(INPUT_POST, 'major', FILTER_SANITIZE_STRING));
        $student_awarding_organization = trim(filter_input(INPUT_POST, 'student_awarding_organization', FILTER_SANITIZE_STRING));
        $student_description = trim(filter_input(INPUT_POST, 'student_description', FILTER_SANITIZE_STRING));

        // ตรวจสอบข้อมูลที่จำเป็น
        if (!$award_id || empty($student_award_name) || empty($student_award_date) || empty($faculty) || empty($major) || empty($student_awarding_organization)) {
            sendResponse('error', 'กรุณากรอกข้อมูลให้ครบถ้วน');
        }

        // อัปเดตรางวัลนักเรียนในฐานข้อมูล
        $stmt = $pdo->prepare("UPDATE studentaward SET 
            student_award_name = :student_award_name, 
            student_award_date = :student_award_date, 
            faculty = :faculty, 
            major = :major, 
            student_awarding_organization = :student_awarding_organization, 
            student_description = :student_description 
            WHERE award_id = :award_id");
        $stmt->bindParam(':student_award_name', $student_award_name);
        $stmt->bindParam(':student_award_date', $student_award_date);
        $stmt->bindParam(':faculty', $faculty);
        $stmt->bindParam(':major', $major);
        $stmt->bindParam(':student_awarding_organization', $student_awarding_organization);
        $stmt->bindParam(':student_description', $student_description);
        $stmt->bindParam(':award_id', $award_id, PDO::PARAM_INT);

        if ($stmt->execute()) {
            sendResponse('success', 'แก้ไขรางวัลนักเรียนสำเร็จ');
        } else {
            sendResponse('error', 'ไม่สามารถแก้ไขรางวัลนักเรียนได้');
        }
    } else {
        sendResponse('error', 'Invalid request method');
    }
} catch (Exception $e) {
    error_log($e->getMessage()); 
    sendResponse('error', 'ข้อผิดพลาด: ' . $e->getMessage());
}
?>
