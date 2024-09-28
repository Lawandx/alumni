<?php
// edit_personal_info.php
session_start();

header('Content-Type: application/json'); // กำหนดให้ PHP ส่งข้อมูลเป็น JSON

require 'db_connect.php'; 

$response = ['status' => 'error', 'message' => 'เกิดข้อผิดพลาดในการอัปเดตข้อมูล'];

// ฟังก์ชันส่ง JSON Response และหยุดการทำงาน
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

        // รับข้อมูลจากฟอร์ม
        $person_id = $_POST['person_id'];
        $first_name = $_POST['first_name'];
        $last_name = $_POST['last_name'];
        $title = $_POST['title'];
        $birth_date = $_POST['birth_date'];
        $phone_personal = $_POST['phone_personal'];
        $email = $_POST['email'];
        $line_id = $_POST['line_id'];
        $facebook = $_POST['facebook'];
        $special_ability = $_POST['special_ability'];

        // ดึงข้อมูลรูปภาพปัจจุบันจากฐานข้อมูล
        $stmt = $pdo->prepare("SELECT photo FROM PersonalInfo WHERE person_id = :person_id");
        $stmt->bindParam(':person_id', $person_id, PDO::PARAM_INT);
        $stmt->execute();
        $current_person = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$current_person) {
            sendResponse('error', 'ไม่พบข้อมูลบุคคล');
        }

        $current_photo = $current_person['photo'];

        // ตรวจสอบตัวเลือกการเปลี่ยนรูปภาพ
        $photo_option = $_POST['photo_option'] ?? 'none'; // เพิ่มตัวเลือก 'none' เพื่อไม่เปลี่ยนรูป
        $photo_url = $_POST['photo_url'] ?? '';
        $photo_upload_path = $current_photo; // เริ่มต้นด้วยรูปภาพเดิม

        if ($photo_option === 'url') {
            if (!empty($photo_url) && filter_var($photo_url, FILTER_VALIDATE_URL)) {
                $photo_upload_path = $photo_url;
            } else {
                // ถ้าไม่กรอก URL หรือ URL ไม่ถูกต้อง, ใช้รูปภาพเดิม
                // สามารถส่งข้อความเตือนได้หากต้องการ
                // sendResponse('error', 'URL รูปภาพไม่ถูกต้อง');
            }
        } elseif ($photo_option === 'upload') {
            if (isset($_FILES['photo_upload']) && $_FILES['photo_upload']['error'] === UPLOAD_ERR_OK) {
                $fileTmpPath = $_FILES['photo_upload']['tmp_name'];
                $fileName = $_FILES['photo_upload']['name'];
                $fileSize = $_FILES['photo_upload']['size'];
                $fileType = $_FILES['photo_upload']['type'];
                $fileNameCmps = explode(".", $fileName);
                $fileExtension = strtolower(end($fileNameCmps));

                // กำหนดชนิดไฟล์ที่อนุญาต
                $allowedfileExtensions = ['jpg', 'jpeg', 'png', 'gif'];

                if (in_array($fileExtension, $allowedfileExtensions)) {
                    // สร้างชื่อไฟล์ใหม่เพื่อป้องกันการซ้ำ
                    $newFileName = md5(time() . $fileName) . '.' . $fileExtension;

                    // กำหนดเส้นทางในการเก็บไฟล์
                    $uploadFileDir = './assets/images/profile_pictures/';
                    if (!is_dir($uploadFileDir)) {
                        mkdir($uploadFileDir, 0755, true);
                    }
                    $dest_path = $uploadFileDir . $newFileName;

                    if(move_uploaded_file($fileTmpPath, $dest_path)) {
                        $photo_upload_path = $dest_path;
                    } else {
                        sendResponse('error', 'ไม่สามารถอัปโหลดไฟล์ได้');
                    }
                } else {
                    sendResponse('error', 'ชนิดไฟล์ไม่ถูกต้อง. อนุญาตเฉพาะ: ' . implode(', ', $allowedfileExtensions));
                }
            } else {
                // ถ้าไม่ได้อัปโหลดไฟล์, ใช้รูปภาพเดิม
                // สามารถส่งข้อความเตือนได้หากต้องการ
                // sendResponse('error', 'เกิดข้อผิดพลาดในการอัปโหลดไฟล์');
            }
        }
        // ถ้า photo_option ไม่ได้เลือก 'url' หรือ 'upload', จะใช้รูปภาพเดิม

        // Update ข้อมูลส่วนบุคคลในฐานข้อมูล
        $stmt = $pdo->prepare("UPDATE PersonalInfo SET 
            first_name = :first_name, 
            last_name = :last_name, 
            title = :title, 
            birth_date = :birth_date, 
            phone_personal = :phone_personal, 
            email = :email, 
            line_id = :line_id, 
            facebook = :facebook, 
            special_ability = :special_ability, 
            photo = :photo 
            WHERE person_id = :person_id
        ");

        $stmt->bindParam(':first_name', $first_name);
        $stmt->bindParam(':last_name', $last_name);
        $stmt->bindParam(':title', $title);
        $stmt->bindParam(':birth_date', $birth_date);
        $stmt->bindParam(':phone_personal', $phone_personal);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':line_id', $line_id);
        $stmt->bindParam(':facebook', $facebook);
        $stmt->bindParam(':special_ability', $special_ability);
        $stmt->bindParam(':photo', $photo_upload_path);
        $stmt->bindParam(':person_id', $person_id, PDO::PARAM_INT);

        if ($stmt->execute()) {
            sendResponse('success', 'อัปเดตข้อมูลส่วนบุคคลสำเร็จ');
        } else {
            sendResponse('error', 'ไม่สามารถอัปเดตข้อมูลได้');
        }
    } else {
        sendResponse('error', 'Invalid request method');
    }
} catch (Exception $e) {
    error_log($e->getMessage()); 
    sendResponse('error', 'ข้อผิดพลาด: ' . $e->getMessage());
}
?>
