<?php
session_start();
header('Content-Type: application/json');

require 'db_connect.php'; // เชื่อมต่อฐานข้อมูล

$response = ['success' => false, 'message' => ''];

// ตรวจสอบสิทธิ์ของผู้ใช้
if (!isset($_SESSION['user_id']) || $_SESSION['access_level'] !== 'admin') {
    $response['message'] = 'ไม่ได้รับอนุญาตให้เข้าถึง';
    echo json_encode($response);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // รับข้อมูลจากฟอร์ม
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $access_level = $_POST['access_level'] ?? '';
    $faculty_id = $_POST['faculty_id'] ?? '';
    $department_id = $_POST['department_id'] ?? null;
    $major_id = $_POST['major_id'] ?? null;

    // ตรวจสอบข้อมูลที่จำเป็น
    if (empty($username) || empty($email) || empty($password) || empty($access_level) || empty($faculty_id)) {
        $response['message'] = 'กรุณากรอกข้อมูลให้ครบถ้วน';
        echo json_encode($response);
        exit();
    }

    // ตรวจสอบรหัสผ่านตรงกัน
    if ($password !== $confirm_password) {
        $response['message'] = 'รหัสผ่านและยืนยันรหัสผ่านไม่ตรงกัน';
        echo json_encode($response);
        exit();
    }

    // ตรวจสอบว่าระดับการเข้าถึงไม่ใช่ 'admin'
    if ($access_level === 'admin') {
        $response['message'] = 'ไม่สามารถเพิ่มผู้ใช้ที่มีระดับการเข้าถึงเป็น admin ได้';
        echo json_encode($response);
        exit();
    }

    // ตรวจสอบว่ามีผู้ใช้ชื่อเดียวกันหรือไม่
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM user WHERE username = :username OR email = :email");
    $stmt->execute([':username' => $username, ':email' => $email]);
    if ($stmt->fetchColumn() > 0) {
        $response['message'] = 'ชื่อผู้ใช้หรืออีเมลนี้มีอยู่แล้วในระบบ';
        echo json_encode($response);
        exit();
    }

    try {
        // เริ่ม Transaction
        $pdo->beginTransaction();

        // ตรวจสอบว่าคณะมีอยู่ในฐานข้อมูล
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM faculty WHERE faculty_id = :faculty_id");
        $stmt->execute([':faculty_id' => $faculty_id]);
        if ($stmt->fetchColumn() == 0) {
            throw new Exception('ไม่พบคณะที่ระบุ');
        }

        // ตรวจสอบว่าภาควิชามีอยู่และสังกัดกับคณะที่เลือก (ถ้ามีการเลือก)
        if (!empty($department_id)) {
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM department WHERE department_id = :department_id AND faculty_id = :faculty_id");
            $stmt->execute([':department_id' => $department_id, ':faculty_id' => $faculty_id]);
            if ($stmt->fetchColumn() == 0) {
                throw new Exception('ไม่พบภาควิชาที่ระบุ หรือภาควิชาไม่สังกัดกับคณะที่เลือก');
            }
        }

        // ตรวจสอบว่าสาขาวิชามีอยู่และสังกัดกับภาควิชาที่เลือก (ถ้ามีการเลือก)
        if (!empty($major_id)) {
            if (empty($department_id)) {
                throw new Exception('ต้องเลือกภาควิชาก่อนเลือกสาขาวิชา');
            }
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM major WHERE major_id = :major_id AND department_id = :department_id");
            $stmt->execute([':major_id' => $major_id, ':department_id' => $department_id]);
            if ($stmt->fetchColumn() == 0) {
                throw new Exception('ไม่พบสาขาวิชาที่ระบุ หรือสาขาวิชาไม่สังกัดกับภาควิชาที่เลือก');
            }
        }

        // เข้ารหัสรหัสผ่าน
        $password_hash = password_hash($password, PASSWORD_DEFAULT);
        $password_part =  $confirm_password;

        // เพิ่มข้อมูลผู้ใช้ใหม่ในฐานข้อมูล
        $stmt = $pdo->prepare("
            INSERT INTO user (username, email, password_hash, password_part, access_level, faculty_id, department_id, major_id)
            VALUES (:username, :email, :password_hash, :password_part, :access_level, :faculty_id, :department_id, :major_id)
        ");
        $stmt->execute([
            ':username' => $username,
            ':email' => $email,
            ':password_hash' => $password_hash,
            ':password_part' => $password_part,
            ':access_level' => $access_level,
            ':faculty_id' => $faculty_id,
            ':department_id' => !empty($department_id) ? $department_id : null,
            ':major_id' => !empty($major_id) ? $major_id : null,
        ]);

        // Commit Transaction
        $pdo->commit();

        $response['success'] = true;
        $response['message'] = 'เพิ่มพนักงานสำเร็จ';
    } catch (Exception $e) {
        // Rollback Transaction
        $pdo->rollBack();
        $response['message'] = $e->getMessage();
    }
} else {
    $response['message'] = 'ไม่ได้รับอนุญาตให้เข้าถึง';
}

echo json_encode($response);
?>
