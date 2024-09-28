<?php
// get_provinces.php
require '../db_connect.php'; 

header('Content-Type: application/json');

try {
    $stmt = $pdo->query("SELECT id, name_in_thai FROM provinces ORDER BY name_in_thai ASC");
    $provinces = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($provinces);
} catch (Exception $e) {
    echo json_encode(['error' => 'เกิดข้อผิดพลาดในการดึงข้อมูลจังหวัด']);
}
?>
