<?php
// get_districts.php
require 'db_connect.php';

header('Content-Type: application/json');

if (!isset($_GET['province_id'])) {
    echo json_encode(['error' => 'Missing province_id']);
    exit();
}

$province_id = intval($_GET['province_id']);

try {
    $stmt = $pdo->prepare("SELECT id, name_in_thai FROM districts WHERE province_id = :province_id ORDER BY name_in_thai ASC");
    $stmt->bindParam(':province_id', $province_id, PDO::PARAM_INT);
    $stmt->execute();
    $districts = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($districts);
} catch (Exception $e) {
    echo json_encode(['error' => 'เกิดข้อผิดพลาดในการดึงข้อมูลอำเภอ']);
}
?>
