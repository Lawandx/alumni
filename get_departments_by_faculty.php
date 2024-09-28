<?php
header('Content-Type: application/json');
require 'db_connect.php';

$response = ['success' => false, 'departments' => []];

$faculty_id = $_GET['faculty_id'] ?? '';

if (empty($faculty_id)) {
    $response['message'] = 'ไม่พบข้อมูลคณะ';
    echo json_encode($response);
    exit();
}

try {
    $stmt = $pdo->prepare("SELECT department_id, department_name FROM department WHERE faculty_id = :faculty_id ORDER BY department_name ASC");
    $stmt->execute([':faculty_id' => $faculty_id]);
    $departments = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $response['success'] = true;
    $response['departments'] = $departments;
} catch (Exception $e) {
    $response['message'] = 'เกิดข้อผิดพลาด: ' . $e->getMessage();
}

echo json_encode($response);
?>
