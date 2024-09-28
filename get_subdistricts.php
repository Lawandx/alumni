<?php
// get_subdistricts.php
require 'db_connect.php';

header('Content-Type: application/json');

if (!isset($_GET['district_id'])) {
    echo json_encode(['error' => 'Missing district_id']);
    exit();
}

$district_id = intval($_GET['district_id']);

try {
    $stmt = $pdo->prepare("SELECT id, name_in_thai FROM subdistricts WHERE district_id = :district_id ORDER BY name_in_thai ASC");
    $stmt->bindParam(':district_id', $district_id, PDO::PARAM_INT);
    $stmt->execute();
    $subdistricts = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($subdistricts);
} catch (Exception $e) {
    echo json_encode(['error' => 'เกิดข้อผิดพลาดในการดึงข้อมูลตำบล']);
}
?>
