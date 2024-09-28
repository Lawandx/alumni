<?php
session_start();
header('Content-Type: application/json');

require 'db_connect.php'; // เชื่อมต่อฐานข้อมูล

$response = ['success' => false, 'message' => ''];

if (!isset($_SESSION['user_id']) || $_SESSION['access_level'] !== 'admin') {
    $response['message'] = 'ไม่ได้รับอนุญาตให้เข้าถึง';
    echo json_encode($response);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // รับข้อมูลจากฟอร์ม
    $user_id = $_POST['user_id'] ?? '';
    $email = $_POST['email'] ?? '';
    $faculty_id = $_POST['faculty_id'] ?? '';
    $department_id = $_POST['department_id'] ?? null;
    $major_id = $_POST['major_id'] ?? null;
    $access_level = $_POST['access_level'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_new_password = $_POST['confirm_new_password'] ?? '';

    // ตรวจสอบว่า user_id เป็นตัวเลขและมีอยู่ในฐานข้อมูล
    if (!ctype_digit($user_id)) {
        $response['message'] = 'รหัสผู้ใช้ไม่ถูกต้อง';
        echo json_encode($response);
        exit();
    }

    // ตรวจสอบว่า access_level ไม่ใช่ 'admin'
    if ($access_level === 'admin') {
        $response['message'] = 'ไม่สามารถเปลี่ยนระดับการเข้าถึงเป็น admin ได้';
        echo json_encode($response);
        exit();
    }

    // ตรวจสอบว่ารหัสผ่านใหม่และรหัสผ่านยืนยันตรงกันหรือไม่
    if (!empty($new_password) || !empty($confirm_new_password)) {
        if ($new_password !== $confirm_new_password) {
            $response['message'] = 'รหัสผ่านใหม่และรหัสผ่านยืนยันไม่ตรงกัน';
            echo json_encode($response);
            exit();
        }
    }

    try {
        // เริ่ม Transaction
        $pdo->beginTransaction();

        // อัปเดต Email
        if (!empty($email)) {
            $stmt = $pdo->prepare("UPDATE user SET email = :email WHERE user_id = :user_id");
            $stmt->execute([
                ':email' => $email,
                ':user_id' => $user_id
            ]);
        }

        // อัปเดต คณะ, ภาควิชา, สาขาวิชา และ access_level
        if (!empty($faculty_id) && !empty($access_level)) {
            // ตรวจสอบว่าคณะมีอยู่ในฐานข้อมูล
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM faculty WHERE faculty_id = :faculty_id");
            $stmt->execute([':faculty_id' => $faculty_id]);
            if ($stmt->fetchColumn() == 0) {
                throw new Exception('ไม่พบคณะที่ระบุ');
            }

            // ตรวจสอบว่าภาควิชามีอยู่และสังกัดกับคณะที่เลือก
            if (!empty($department_id)) {
                $stmt = $pdo->prepare("SELECT COUNT(*) FROM department WHERE department_id = :department_id AND faculty_id = :faculty_id");
                $stmt->execute([':department_id' => $department_id, ':faculty_id' => $faculty_id]);
                if ($stmt->fetchColumn() == 0) {
                    throw new Exception('ไม่พบภาควิชาที่ระบุ หรือภาควิชาไม่สังกัดกับคณะที่เลือก');
                }
            }

            // ตรวจสอบว่าสาขาวิชามีอยู่และสังกัดกับภาควิชาที่เลือก
            if (!empty($major_id)) {
                $stmt = $pdo->prepare("SELECT COUNT(*) FROM major WHERE major_id = :major_id AND department_id = :department_id");
                $stmt->execute([':major_id' => $major_id, ':department_id' => $department_id]);
                if ($stmt->fetchColumn() == 0) {
                    throw new Exception('ไม่พบสาขาวิชาที่ระบุ หรือสาขาวิชาไม่สังกัดกับภาควิชาที่เลือก');
                }
            }

            // อัปเดตคณะ, ภาควิชา, สาขาวิชา, และ access_level
            $stmt = $pdo->prepare("
                UPDATE user 
                SET faculty_id = :faculty_id, 
                    department_id = :department_id, 
                    major_id = :major_id,
                    access_level = :access_level
                WHERE user_id = :user_id
            ");
            $stmt->execute([
                ':faculty_id' => $faculty_id,
                ':department_id' => !empty($department_id) ? $department_id : null,
                ':major_id' => !empty($major_id) ? $major_id : null,
                ':access_level' => $access_level,
                ':user_id' => $user_id
            ]);
        }

        // อัปเดตรหัสผ่าน
        if (!empty($new_password)) {
            // เข้ารหัสรหัสผ่านใหม่
            $new_password_hash = password_hash($new_password, PASSWORD_DEFAULT);

            // อัปเดตรหัสผ่านและ password_part
            $stmt = $pdo->prepare("
                UPDATE user 
                SET password_hash = :new_password_hash, 
                    password_part = :password_part 
                WHERE user_id = :user_id
            ");
            $stmt->execute([
                ':new_password_hash' => $new_password_hash,
                ':password_part' => htmlspecialchars($new_password), // หรือวิธีการอื่นในการเก็บ password_part
                ':user_id' => $user_id
            ]);
        }

        // Commit Transaction
        $pdo->commit();
        $response['success'] = true;
        $response['message'] = 'แก้ไขข้อมูลสำเร็จ';
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
