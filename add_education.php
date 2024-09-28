<?php
// add_education.php
session_start();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // ตรวจสอบสิทธิ์
    if (!isset($_SESSION['user_id']) || !in_array($_SESSION['access_level'], ['admin', 'faculty','department','major'])) {
        echo json_encode(['status' => 'error', 'message' => 'คุณไม่มีสิทธิ์ในการดำเนินการนี้']);
        exit();
    }

    require 'db_connect.php';

    // รับข้อมูลจากฟอร์ม
    $person_id = $_POST['person_id'];
    $degree_level = $_POST['degree_level'];
    $country = $_POST['country'];
    $university = $_POST['university'];
    $student_id = $_POST['student_id'];
    $faculty_name = $_POST['faculty_name'];
    $major_name = $_POST['major_name'];
    $graduation_year = $_POST['graduation_year'];

    // การตรวจสอบข้อมูลเพิ่มเติม (เช่น ความถูกต้องของข้อมูล)
    if (empty($degree_level) || empty($country) || empty($university) || empty($student_id) || empty($faculty_name) || empty($major_name) || empty($graduation_year)) {
        echo json_encode(['status' => 'error', 'message' => 'กรุณากรอกข้อมูลให้ครบถ้วน']);
        exit();
    }

    try {
        $stmt = $pdo->prepare("INSERT INTO education (person_id, degree_level, country, university, student_id, faculty_name, major_name, graduation_year) 
                               VALUES (:person_id, :degree_level, :country, :university, :student_id, :faculty_name, :major_name, :graduation_year)");
        $stmt->bindParam(':person_id', $person_id);
        $stmt->bindParam(':degree_level', $degree_level);
        $stmt->bindParam(':country', $country);
        $stmt->bindParam(':university', $university);
        $stmt->bindParam(':student_id', $student_id);
        $stmt->bindParam(':faculty_name', $faculty_name);
        $stmt->bindParam(':major_name', $major_name);
        $stmt->bindParam(':graduation_year', $graduation_year);

        $stmt->execute();

        echo json_encode(['status' => 'success', 'message' => 'เพิ่มข้อมูลการศึกษาเรียบร้อยแล้ว']);
    } catch (Exception $e) {
        // บันทึกข้อผิดพลาดใน log
        error_log($e->getMessage());
        echo json_encode(['status' => 'error', 'message' => 'เกิดข้อผิดพลาดในการเพิ่มข้อมูลการศึกษา']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
}
