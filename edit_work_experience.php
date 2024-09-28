<?php
// edit_work_experience.php
session_start();



header('Content-Type: application/json'); // กำหนดให้ PHP ส่งข้อมูลเป็น JSON

require 'db_connect.php'; 

$response = ['status' => 'error', 'message' => 'เกิดข้อผิดพลาดในการแก้ไขประสบการณ์การทำงาน'];

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
        $work_id = filter_input(INPUT_POST, 'work_id', FILTER_VALIDATE_INT);
        $position = trim(filter_input(INPUT_POST, 'position', FILTER_SANITIZE_STRING));
        $company_name = trim(filter_input(INPUT_POST, 'company_name', FILTER_SANITIZE_STRING));
        $start_date = filter_input(INPUT_POST, 'start_date', FILTER_SANITIZE_STRING);
        $end_date_input = filter_input(INPUT_POST, 'end_date', FILTER_SANITIZE_STRING);
        $country = trim(filter_input(INPUT_POST, 'country', FILTER_SANITIZE_STRING));
        $house_number = trim(filter_input(INPUT_POST, 'house_number', FILTER_SANITIZE_STRING));
        $village = trim(filter_input(INPUT_POST, 'village', FILTER_SANITIZE_STRING));
        $Province = trim(filter_input(INPUT_POST, 'Province', FILTER_SANITIZE_STRING));
        $District = trim(filter_input(INPUT_POST, 'District', FILTER_SANITIZE_STRING));
        $sub_district = trim(filter_input(INPUT_POST, 'sub_district', FILTER_SANITIZE_STRING));
        $zip_code = trim(filter_input(INPUT_POST, 'zip_code', FILTER_SANITIZE_STRING));
        $work_phone = trim(filter_input(INPUT_POST, 'work_phone', FILTER_SANITIZE_STRING));

        // ตรวจสอบข้อมูลที่จำเป็น
        if (!$work_id || empty($position) || empty($company_name) || empty($start_date) || empty($country)) {
            sendResponse('error', 'กรุณากรอกข้อมูลให้ครบถ้วน');
        }

        // หาก end_date ว่างเปล่า ให้ตั้งค่าเป็น NULL
        $end_date = !empty($end_date_input) ? $end_date_input : null;

        // อัปเดตรายการประสบการณ์การทำงานในฐานข้อมูล
        $stmt = $pdo->prepare("UPDATE workexperience SET 
            position = :position, 
            company_name = :company_name, 
            start_date = :start_date, 
            end_date = :end_date, 
            country = :country, 
            house_number = :house_number, 
            village = :village, 
            Province = :Province, 
            District = :District, 
            sub_district = :sub_district, 
            zip_code = :zip_code, 
            work_phone = :work_phone 
            WHERE work_id = :work_id");
        $stmt->bindParam(':position', $position);
        $stmt->bindParam(':company_name', $company_name);
        $stmt->bindParam(':start_date', $start_date);
        $stmt->bindParam(':end_date', $end_date);
        $stmt->bindParam(':country', $country);
        $stmt->bindParam(':house_number', $house_number);
        $stmt->bindParam(':village', $village);
        $stmt->bindParam(':Province', $Province);
        $stmt->bindParam(':District', $District);
        $stmt->bindParam(':sub_district', $sub_district);
        $stmt->bindParam(':zip_code', $zip_code);
        $stmt->bindParam(':work_phone', $work_phone);
        $stmt->bindParam(':work_id', $work_id, PDO::PARAM_INT);

        if ($stmt->execute()) {
            sendResponse('success', 'แก้ไขประสบการณ์การทำงานสำเร็จ');
        } else {
            sendResponse('error', 'ไม่สามารถแก้ไขประสบการณ์การทำงานได้');
        }
    } else {
        sendResponse('error', 'Invalid request method');
    }
} catch (Exception $e) {
    error_log($e->getMessage()); 
    sendResponse('error', 'ข้อผิดพลาด: ' . $e->getMessage());
}
?>
