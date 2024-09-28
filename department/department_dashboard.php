<?php
// department_dashboard.php
session_start();
require '../db_connect.php'; // ปรับพาธให้ถูกต้องตามโครงสร้างโฟลเดอร์

// ตรวจสอบว่าผู้ใช้ได้เข้าสู่ระบบแล้วหรือยัง และมีสิทธิ์เป็น department หรือไม่
if (!isset($_SESSION['user_id']) || $_SESSION['access_level'] !== 'department') {
    header("Location: ../login.php");
    exit();
}

// ตรวจสอบว่า faculty_id และ department_id ถูกตั้งค่าไว้ใน SESSION หรือไม่
if (!isset($_SESSION['faculty_id']) || !isset($_SESSION['department_id'])) {
    echo "เกิดข้อผิดพลาด: ไม่พบข้อมูลคณะหรือภาควิชาใน SESSION";
    exit();
}

$faculty_id = $_SESSION['faculty_id'];
$department_id = $_SESSION['department_id'];

try {
    // ดึงข้อมูลคณะ
    $stmt = $pdo->prepare("SELECT * FROM faculty WHERE faculty_id = :faculty_id");
    $stmt->execute([':faculty_id' => $faculty_id]);
    $faculty = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$faculty) {
        echo "ไม่พบข้อมูลคณะ";
        exit();
    }

    // ดึงข้อมูลภาควิชา
    $stmt = $pdo->prepare("SELECT * FROM department WHERE department_id = :department_id AND faculty_id = :faculty_id");
    $stmt->execute([
        ':department_id' => $department_id,
        ':faculty_id' => $faculty_id
    ]);
    $department = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$department) {
        echo "ไม่พบข้อมูลภาควิชา หรือภาควิชานี้ไม่ได้สังกัดในคณะที่ระบุ";
        exit();
    }

    // ดึงจำนวนสาขาวิชาที่อยู่ภายใต้ภาควิชานี้
    $stmt = $pdo->prepare("SELECT COUNT(*) AS major_count FROM major WHERE department_id = :department_id");
    $stmt->execute([':department_id' => $department_id]);
    $major_count = $stmt->fetch(PDO::FETCH_ASSOC)['major_count'];

    // คุณสามารถดึงข้อมูลเพิ่มเติมได้ตามต้องการ เช่น จำนวนศิษย์เก่า เป็นต้น

} catch (Exception $e) {
    echo "เกิดข้อผิดพลาด: " . htmlspecialchars($e->getMessage());
    exit();
}
?>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <title>แดชบอร์ดภาควิชา - <?= htmlspecialchars($department['department_name']) ?></title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Google Fonts (Sarabun) -->
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@400;500;600&display=swap" rel="stylesheet">
    <!-- Font Awesome สำหรับไอคอน -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            font-family: "Sarabun", sans-serif;
            background-color: #f8f9fa;
        }

        .container {
            margin-top: 30px;
        }

        .department-header {
            background-color: #343a40;
            color: #fff;
            padding: 20px;
            border-radius: 5px;
            text-align: center;
        }

        .summary-cards {
            margin-top: 30px;
        }

        .summary-card {
            background-color: #ffffff;
            border: 1px solid #dee2e6;
            border-radius: 5px;
            padding: 20px;
            text-align: center;
            transition: box-shadow 0.3s;
        }

        .summary-card:hover {
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .summary-card h3 {
            margin-bottom: 10px;
            font-size: 36px;
            color: #343a40;
        }

        .summary-card p {
            margin: 0;
            font-size: 18px;
            color: #6c757d;
        }
    </style>
</head>

<body>
    <?php include 'navbar-department.php'; ?>

    <div class="container">
        <div class="department-header">
            <h2><?= htmlspecialchars($department['department_name']) ?></h2>
            <p>สังกัดคณะ <?= htmlspecialchars($faculty['faculty_name']) ?></p>
        </div>

        <div class="row summary-cards">
            <div class="col-md-4">
                <div class="summary-card">
                    <h3><?= $major_count ?></h3>
                    <p>สาขาวิชา</p>
                </div>
            </div>
            <!-- คุณสามารถเพิ่มการ์ดสรุปอื่น ๆ ได้ตามต้องการ -->
        </div>

        <!-- คุณสามารถเพิ่มกราฟหรือแผนภูมิแสดงข้อมูลสรุปเพิ่มเติมได้ที่นี่ -->
    </div>

    <!-- Bootstrap JS และ dependencies -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- jQuery (ถ้าจำเป็นต้องใช้) -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</body>

</html>
