<?php
// edit_education.php
session_start();

// กำหนด Content-Type เป็น JSON
header('Content-Type: application/json');

// ฟังก์ชันส่ง JSON และหยุดการทำงาน
function sendResponse($status, $message) {
    echo json_encode(['status' => $status, 'message' => $message]);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // ตรวจสอบสิทธิ์
    if (!isset($_SESSION['user_id']) || !in_array($_SESSION['access_level'], ['admin', 'faculty','department','major'])) {
        sendResponse('error', 'คุณไม่มีสิทธิ์ในการดำเนินการนี้');
    }

    require 'db_connect.php';

    // รับข้อมูลจากฟอร์ม
    $education_id = $_POST['education_id'] ?? '';
    $degree_level = $_POST['degree_level'] ?? '';
    $country = $_POST['country'] ?? '';
    $university = $_POST['university'] ?? '';
    $student_id = $_POST['student_id'] ?? '';
    $faculty_name = $_POST['faculty_name'] ?? '';
    $major_name = $_POST['major_name'] ?? '';
    $graduation_year = $_POST['graduation_year'] ?? '';

    // การตรวจสอบข้อมูลเพิ่มเติม
    if (empty($education_id) || empty($degree_level) || empty($country) || empty($university) || empty($student_id) || empty($faculty_name) || empty($major_name) || empty($graduation_year)) {
        sendResponse('error', 'กรุณากรอกข้อมูลให้ครบถ้วน');
    }

    try {
        $stmt = $pdo->prepare("UPDATE education SET 
                                degree_level = :degree_level, 
                                country = :country, 
                                university = :university, 
                                student_id = :student_id, 
                                faculty_name = :faculty_name, 
                                major_name = :major_name, 
                                graduation_year = :graduation_year 
                               WHERE education_id = :education_id");
        $stmt->bindParam(':degree_level', $degree_level);
        $stmt->bindParam(':country', $country);
        $stmt->bindParam(':university', $university);
        $stmt->bindParam(':student_id', $student_id);
        $stmt->bindParam(':faculty_name', $faculty_name);
        $stmt->bindParam(':major_name', $major_name);
        $stmt->bindParam(':graduation_year', $graduation_year);
        $stmt->bindParam(':education_id', $education_id);

        $stmt->execute();

        sendResponse('success', 'แก้ไขข้อมูลการศึกษาเรียบร้อยแล้ว');
    } catch (Exception $e) {
        // บันทึกข้อผิดพลาดใน log
        error_log($e->getMessage());
        sendResponse('error', 'เกิดข้อผิดพลาดในการแก้ไขข้อมูลการศึกษา');
    }
} else {
    sendResponse('error', 'Invalid request method');
}
?>
