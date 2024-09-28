<?php
// edit_address.php
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
    $person_id = isset($_POST['person_id']) ? intval($_POST['person_id']) : 0;
    $address_type = isset($_POST['address_type']) ? $_POST['address_type'] : '';
    $house_number = isset($_POST['house_number']) ? trim($_POST['house_number']) : '';
    $village = isset($_POST['village']) ? trim($_POST['village']) : '';
    $subdistrict = isset($_POST['subdistrict']) ? intval($_POST['subdistrict']) : 0; // เป็น ID ของ subdistrict
    $district = isset($_POST['district']) ? intval($_POST['district']) : 0; // เป็น ID ของ district
    $province = isset($_POST['province']) ? intval($_POST['province']) : 0; // เป็น ID ของ province
    $zip_code = isset($_POST['zip_code']) ? intval($_POST['zip_code']) : 0;
    $phone_address = isset($_POST['phone_address']) ? trim($_POST['phone_address']) : '';
    $is_same_as_permanent = isset($_POST['is_same_as_permanent']) ? intval($_POST['is_same_as_permanent']) : 0;

    // การตรวจสอบข้อมูลเพิ่มเติม
    if (empty($person_id) || empty($address_type) || empty($house_number) || empty($subdistrict) || empty($district) || empty($province) || empty($zip_code) || empty($phone_address)) {
        echo json_encode(['status' => 'error', 'message' => 'กรุณากรอกข้อมูลให้ครบถ้วน']);
        exit();
    }

    try {
        // ตรวจสอบว่ามีที่อยู่สำหรับ person_id นี้หรือไม่
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM address WHERE person_id = :person_id");
        $stmt->bindParam(':person_id', $person_id, PDO::PARAM_INT);
        $stmt->execute();
        $count = $stmt->fetchColumn();

        if ($count > 0) {
            // มีอยู่แล้ว, ทำการ UPDATE
            $stmt = $pdo->prepare("UPDATE address SET 
                                    address_type = :address_type, 
                                    house_number = :house_number, 
                                    village = :village, 
                                    sub_district = :subdistrict, 
                                    district = :district, 
                                    province = :province, 
                                    zip_code = :zip_code, 
                                    phone_address = :phone_address, 
                                    is_same_as_permanent = :is_same_as_permanent 
                                   WHERE person_id = :person_id");
            $stmt->bindParam(':address_type', $address_type);
            $stmt->bindParam(':house_number', $house_number);
            $stmt->bindParam(':village', $village);
            $stmt->bindParam(':subdistrict', $subdistrict, PDO::PARAM_INT);
            $stmt->bindParam(':district', $district, PDO::PARAM_INT);
            $stmt->bindParam(':province', $province, PDO::PARAM_INT);
            $stmt->bindParam(':zip_code', $zip_code, PDO::PARAM_INT);
            $stmt->bindParam(':phone_address', $phone_address);
            $stmt->bindParam(':is_same_as_permanent', $is_same_as_permanent, PDO::PARAM_BOOL);
            $stmt->bindParam(':person_id', $person_id, PDO::PARAM_INT);

            $stmt->execute();

            echo json_encode(['status' => 'success', 'message' => 'แก้ไขข้อมูลที่อยู่เรียบร้อยแล้ว']);
        } else {
            // ไม่มีอยู่, ทำการ INSERT
            $stmt = $pdo->prepare("INSERT INTO address (person_id, address_type, house_number, village, sub_district, district, province, zip_code, phone_address, is_same_as_permanent) 
                                   VALUES (:person_id, :address_type, :house_number, :village, :subdistrict, :district, :province, :zip_code, :phone_address, :is_same_as_permanent)");
            $stmt->bindParam(':person_id', $person_id, PDO::PARAM_INT);
            $stmt->bindParam(':address_type', $address_type);
            $stmt->bindParam(':house_number', $house_number);
            $stmt->bindParam(':village', $village);
            $stmt->bindParam(':subdistrict', $subdistrict, PDO::PARAM_INT);
            $stmt->bindParam(':district', $district, PDO::PARAM_INT);
            $stmt->bindParam(':province', $province, PDO::PARAM_INT);
            $stmt->bindParam(':zip_code', $zip_code, PDO::PARAM_INT);
            $stmt->bindParam(':phone_address', $phone_address);
            $stmt->bindParam(':is_same_as_permanent', $is_same_as_permanent, PDO::PARAM_BOOL);

            $stmt->execute();

            echo json_encode(['status' => 'success', 'message' => 'เพิ่มข้อมูลที่อยู่เรียบร้อยแล้ว']);
        }
    } catch (Exception $e) {
        // บันทึกข้อผิดพลาดใน log และส่งข้อความผิดพลาดกลับไปยังผู้ใช้
        error_log($e->getMessage());
        echo json_encode(['status' => 'error', 'message' => 'เกิดข้อผิดพลาดในการแก้ไขข้อมูลที่อยู่: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
}
?>
