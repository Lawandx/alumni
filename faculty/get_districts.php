<?php
header('Content-Type: application/json; charset=utf-8');
require '../db_connect.php'; 

if (isset($_GET['province_id'])) {
    $province_id = $_GET['province_id'];

    try {
        $stmt = $pdo->prepare("SELECT id, name_in_thai FROM districts WHERE province_id = :province_id");
        $stmt->execute([':province_id' => $province_id]);
        $districts = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode($districts);
    } catch (Exception $e) {
        echo json_encode(['error' => $e->getMessage()]);
    }
} else {
    echo json_encode(['error' => 'No province_id provided']);
}
?>
