<?php
// get_zipcode.php
require '../db_connect.php'; 

header('Content-Type: application/json');

if (!isset($_GET['subdistrict_id'])) {
    echo json_encode(['error' => 'Missing subdistrict_id']);
    exit();
}

$subdistrict_id = intval($_GET['subdistrict_id']);

try {
    $stmt = $pdo->prepare("SELECT zip_code FROM subdistricts WHERE id = :subdistrict_id LIMIT 1");
    $stmt->bindParam(':subdistrict_id', $subdistrict_id, PDO::PARAM_INT);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($result) {
        echo json_encode(['zip_code' => $result['zip_code']]);
    } else {
        echo json_encode(['zip_code' => '']);
    }
} catch (Exception $e) {
    echo json_encode(['error' => 'เกิดข้อผิดพลาดในการดึงข้อมูลรหัสไปรษณีย์']);
}
?>
