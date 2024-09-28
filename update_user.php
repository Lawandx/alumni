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
    $email = $_POST['email'] ?? '';
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_new_password = $_POST['confirm_new_password'] ?? '';

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

        // ดึงข้อมูลผู้ใช้เพื่อเปรียบเทียบรหัสผ่านเก่า
        if (!empty($new_password) || !empty($confirm_new_password)) {
            if (empty($current_password)) {
                throw new Exception('กรุณากรอกรหัสผ่านเก่าเพื่อเปลี่ยนรหัสผ่าน');
            }

            $stmt = $pdo->prepare("SELECT password_hash FROM user WHERE user_id = :user_id");
            $stmt->execute([':user_id' => $_SESSION['user_id']]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$user || !password_verify($current_password, $user['password_hash'])) {
                throw new Exception('รหัสผ่านเก่าไม่ถูกต้อง');
            }

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
                ':user_id' => $_SESSION['user_id']
            ]);
        }

        // อัปเดต Email
        if (!empty($email)) {
            $stmt = $pdo->prepare("UPDATE user SET email = :email WHERE user_id = :user_id");
            $stmt->execute([
                ':email' => $email,
                ':user_id' => $_SESSION['user_id']
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
