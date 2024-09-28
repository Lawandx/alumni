<?php
// edit_employee.php
session_start();
header('Content-Type: application/json');

require 'db_connect.php';

$response = ['status' => 'error', 'message' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // รับข้อมูลจากฟอร์ม
    $user_id = intval($_POST['user_id'] ?? 0);
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $access_level = $_POST['access_level'] ?? '';
    $faculty_id = isset($_POST['faculty']) ? intval($_POST['faculty']) : NULL;
    $department_id = isset($_POST['department']) ? intval($_POST['department']) : NULL;
    $major_id = isset($_POST['major']) ? intval($_POST['major']) : NULL;

    // ตรวจสอบความครบถ้วนของข้อมูล
    if (empty($user_id) || empty($username) || empty($email) || empty($access_level)) {
        $response['message'] = 'กรุณากรอกข้อมูลให้ครบถ้วน';
        echo json_encode($response);
        exit();
    }

    // ตรวจสอบว่า username และ email ยังไม่ถูกใช้งานโดยผู้ใช้คนอื่น
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM user WHERE (username = :username OR email = :email) AND user_id != :user_id");
    $stmt->execute([':username' => $username, ':email' => $email, ':user_id' => $user_id]);
    $count = $stmt->fetchColumn();
    if ($count > 0) {
        $response['message'] = 'ชื่อผู้ใช้หรือ Email นี้ถูกใช้งานแล้วโดยผู้ใช้อื่น';
        echo json_encode($response);
        exit();
    }

    // อัปเดตข้อมูลผู้ใช้
    try {
        $stmt = $pdo->prepare("
            UPDATE user 
            SET username = :username, 
                email = :email, 
                access_level = :access_level, 
                faculty_id = :faculty_id, 
                department_id = :department_id, 
                major_id = :major_id 
            WHERE user_id = :user_id
        ");
        $stmt->execute([
            ':username' => $username,
            ':email' => $email,
            ':access_level' => $access_level,
            ':faculty_id' => $faculty_id ? $faculty_id : NULL,
            ':department_id' => $department_id ? $department_id : NULL,
            ':major_id' => $major_id ? $major_id : NULL,
            ':user_id' => $user_id
        ]);

        $response['status'] = 'success';
        $response['message'] = 'แก้ไขพนักงานสำเร็จ';
    } catch (PDOException $e) {
        $response['message'] = 'เกิดข้อผิดพลาดในการแก้ไขพนักงาน: ' . $e->getMessage();
    }
} else {
    $response['message'] = 'ไม่ได้รับอนุญาตให้เข้าถึง';
}

echo json_encode($response);
?>
