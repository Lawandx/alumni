<?php
// add_work_experience.php
session_start();

ini_set('display_errors', 0); // ปิดการแสดงข้อผิดพลาดใน production
ini_set('display_startup_errors', 0);
error_reporting(0);

header('Content-Type: application/json'); // กำหนดให้ PHP ส่งข้อมูลเป็น JSON

require 'db_connect.php'; 

$response = ['status' => 'error', 'message' => 'เกิดข้อผิดพลาดในการเพิ่มประสบการณ์การทำงาน'];

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
        $position = trim(filter_input(INPUT_POST, 'position', FILTER_SANITIZE_STRING));
        $company_name = trim(filter_input(INPUT_POST, 'company_name', FILTER_SANITIZE_STRING));
        $start_date = filter_input(INPUT_POST, 'start_date', FILTER_SANITIZE_STRING);
        $end_date_input = filter_input(INPUT_POST, 'end_date', FILTER_SANITIZE_STRING);
        $country = trim(filter_input(INPUT_POST, 'country', FILTER_SANITIZE_STRING));
        $house_number = trim(filter_input(INPUT_POST, 'house_number', FILTER_SANITIZE_STRING));
        $village = trim(filter_input(INPUT_POST, 'village', FILTER_SANITIZE_STRING));

        // Initialize variables
        $Province = '';
        $District = '';
        $sub_district = '';
        $zip_code = '';
        $work_phone = trim(filter_input(INPUT_POST, 'work_phone', FILTER_SANITIZE_STRING));

        if ($country === 'ประเทศไทย') {
            // Get province, district, sub_district from select fields
            $province_id = filter_input(INPUT_POST, 'province_id', FILTER_VALIDATE_INT);
            $district_id = filter_input(INPUT_POST, 'district_id', FILTER_VALIDATE_INT);
            $sub_district_id = filter_input(INPUT_POST, 'sub_district_id', FILTER_VALIDATE_INT);
            $zip_code_select = trim(filter_input(INPUT_POST, 'zip_code_select', FILTER_SANITIZE_STRING));

            // Validate required fields
            if (!$province_id || !$district_id || !$sub_district_id || empty($zip_code_select)) {
                sendResponse('error', 'กรุณากรอกข้อมูลให้ครบถ้วน');
            }

            // Fetch province name
            $stmt = $pdo->prepare("SELECT name_in_thai FROM provinces WHERE id = :province_id");
            $stmt->bindParam(':province_id', $province_id, PDO::PARAM_INT);
            $stmt->execute();
            $province = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($province) {
                $Province = $province['name_in_thai'];
            } else {
                sendResponse('error', 'ไม่พบข้อมูลจังหวัด');
            }

            // Fetch district name
            $stmt = $pdo->prepare("SELECT name_in_thai FROM districts WHERE id = :district_id");
            $stmt->bindParam(':district_id', $district_id, PDO::PARAM_INT);
            $stmt->execute();
            $district = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($district) {
                $District = $district['name_in_thai'];
            } else {
                sendResponse('error', 'ไม่พบข้อมูลอำเภอ');
            }

            // Fetch subdistrict name
            $stmt = $pdo->prepare("SELECT name_in_thai FROM subdistricts WHERE id = :sub_district_id");
            $stmt->bindParam(':sub_district_id', $sub_district_id, PDO::PARAM_INT);
            $stmt->execute();
            $subdistrict = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($subdistrict) {
                $sub_district = $subdistrict['name_in_thai'];
            } else {
                sendResponse('error', 'ไม่พบข้อมูลตำบล');
            }

            $zip_code = $zip_code_select;
        } else {
            // Get province, district, sub_district, zip_code from input fields
            $Province = trim(filter_input(INPUT_POST, 'province_other', FILTER_SANITIZE_STRING));
            $District = trim(filter_input(INPUT_POST, 'district_other', FILTER_SANITIZE_STRING));
            $sub_district = trim(filter_input(INPUT_POST, 'sub_district_other', FILTER_SANITIZE_STRING));
            $zip_code = trim(filter_input(INPUT_POST, 'zip_code_other', FILTER_SANITIZE_STRING));

            // Validate required fields
            if (empty($country) || empty($Province) || empty($District) || empty($sub_district) || empty($zip_code)) {
                sendResponse('error', 'กรุณากรอกข้อมูลให้ครบถ้วน');
            }
        }

        // Validate other required fields
        if (!$person_id || empty($position) || empty($company_name) || empty($start_date) || empty($country)) {
            sendResponse('error', 'กรุณากรอกข้อมูลให้ครบถ้วน');
        }

        // หาก end_date ว่างเปล่า ให้ตั้งค่าเป็น NULL
        $end_date = !empty($end_date_input) ? $end_date_input : null;

        // เพิ่มข้อมูลประสบการณ์การทำงานลงในฐานข้อมูล
        $stmt = $pdo->prepare("INSERT INTO workexperience (person_id, position, company_name, start_date, end_date, country, house_number, village, Province, District, sub_district, zip_code, work_phone) VALUES (:person_id, :position, :company_name, :start_date, :end_date, :country, :house_number, :village, :Province, :District, :sub_district, :zip_code, :work_phone)");
        $stmt->bindParam(':person_id', $person_id, PDO::PARAM_INT);
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

        if ($stmt->execute()) {
            sendResponse('success', 'เพิ่มประสบการณ์การทำงานสำเร็จ');
        } else {
            sendResponse('error', 'ไม่สามารถเพิ่มประสบการณ์การทำงานได้');
        }
    } else {
        sendResponse('error', 'Invalid request method');
    }
} catch (Exception $e) {
    error_log($e->getMessage()); 
    sendResponse('error', 'ข้อผิดพลาด: ' . $e->getMessage());
}
?>
