<?php
session_start();

// ตรวจสอบว่าผู้ใช้ได้เข้าสู่ระบบแล้วหรือยัง และมีสิทธิ์เป็น admin หรือไม่
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['access_level'], ['admin', 'faculty','department','major'])) {
    header("Location: login.php");
    exit();
}

require 'db_connect.php'; // เรียกใช้ไฟล์เชื่อมต่อฐานข้อมูล

// ตรวจสอบว่ามีการส่ง person_id มาหรือไม่
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['person_id']) && isset($_POST['return_url'])) {
    $person_id = intval($_POST['person_id']);
    $return_url = filter_var($_POST['return_url'], FILTER_SANITIZE_URL); // กรอง URL เพื่อลดความเสี่ยงในการโจมตี

    try {
        // เริ่มต้น transaction
        $pdo->beginTransaction();

        // ลบข้อมูลจากตารางที่เกี่ยวข้องก่อน (ตาราง child)
        $delete_queries = [
            "DELETE FROM awardhistory WHERE person_id = :person_id",
            "DELETE FROM workexperience WHERE person_id = :person_id",
            "DELETE FROM studentaward WHERE person_id = :person_id",
            "DELETE FROM education WHERE person_id = :person_id",
            "DELETE FROM Address WHERE person_id = :person_id",
            "DELETE FROM PersonalInfo WHERE person_id = :person_id"
        ];

        foreach ($delete_queries as $sql) {
            $stmt = $pdo->prepare($sql);
            $stmt->execute([':person_id' => $person_id]);
        }

        // ยืนยันการทำธุรกรรม
        $pdo->commit();

        // ส่งข้อความสำเร็จและกลับไปที่หน้าที่ส่งมา
        header("Location: $return_url?message=ลบข้อมูลสำเร็จ");
        exit();
    } catch (PDOException $e) {
        // ยกเลิกการทำธุรกรรมในกรณีที่เกิดข้อผิดพลาด
        $pdo->rollBack();
        echo "เกิดข้อผิดพลาดในการลบข้อมูล: " . htmlspecialchars($e->getMessage());
        exit();
    }
} else {
    // ส่งกลับไปที่หน้าที่ส่งมา หากไม่มี person_id หรือไม่ใช่การร้องขอแบบ POST
    $return_url = isset($_POST['return_url']) ? filter_var($_POST['return_url'], FILTER_SANITIZE_URL) : 'search_results.php';
    header("Location: $return_url");
    exit();
}

?>
