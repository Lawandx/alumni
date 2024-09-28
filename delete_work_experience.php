<?php
// delete_work_experience.php
session_start();

header('Content-Type: application/json'); // กำหนดให้ PHP ส่งข้อมูลเป็น JSON

require 'db_connect.php'; 

$response = ['status' => 'error', 'message' => 'เกิดข้อผิดพลาดในการลบประสบการณ์การทำงาน'];

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

        // รับข้อมูลจาก AJAX
        $data = json_decode(file_get_contents('php://input'), true);
        $work_id = isset($data['work_id']) ? intval($data['work_id']) : 0;

        if ($work_id <= 0) {
            sendResponse('error', 'ไม่มีรหัสประสบการณ์การทำงาน');
        }

        // ตรวจสอบว่ามีรายการประสบการณ์การทำงานนั้นจริง
        $stmt = $pdo->prepare("SELECT * FROM workexperience WHERE work_id = :work_id");
        $stmt->bindParam(':work_id', $work_id, PDO::PARAM_INT);
        $stmt->execute();
        $work = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$work) {
            sendResponse('error', 'ไม่พบประสบการณ์การทำงานที่ต้องการลบ');
        }

        // ลบประสบการณ์การทำงาน
        $stmt = $pdo->prepare("DELETE FROM workexperience WHERE work_id = :work_id");
        $stmt->bindParam(':work_id', $work_id, PDO::PARAM_INT);
        if ($stmt->execute()) {
            sendResponse('success', 'ลบประสบการณ์การทำงานสำเร็จ');
        } else {
            sendResponse('error', 'ไม่สามารถลบประสบการณ์การทำงานได้');
        }
    } else {
        sendResponse('error', 'Invalid request method');
    }
} catch (Exception $e) {
    error_log($e->getMessage()); 
    sendResponse('error', 'ข้อผิดพลาด: ' . $e->getMessage());
}
?>
