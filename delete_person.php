<?php
session_start();

// ตรวจสอบว่าผู้ใช้ได้เข้าสู่ระบบแล้วหรือยัง และมีสิทธิ์เป็น admin หรือไม่
if (!isset($_SESSION['user_id']) || $_SESSION['access_level'] !== 'admin') {
    header("Location: login.php");
    exit();
}

require 'db_connect.php'; // เรียกใช้ไฟล์เชื่อมต่อฐานข้อมูล

// ตรวจสอบว่ามีการส่ง person_id มาหรือไม่
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['person_id'])) {
    $person_id = intval($_POST['person_id']);

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

        // ส่งข้อความสำเร็จและกลับไปที่หน้าค้นหา
        header("Location: search_results.php?message=ลบข้อมูลสำเร็จ");
        exit();
    } catch (PDOException $e) {
        // ยกเลิกการทำธุรกรรมในกรณีที่เกิดข้อผิดพลาด
        $pdo->rollBack();
        echo "เกิดข้อผิดพลาดในการลบข้อมูล: " . htmlspecialchars($e->getMessage());
        exit();
    }
} else {
    // ส่งกลับไปที่หน้าค้นหา หากไม่มี person_id หรือไม่ใช่การร้องขอแบบ POST
    header("Location: search_results.php");
    exit();
}
?>
