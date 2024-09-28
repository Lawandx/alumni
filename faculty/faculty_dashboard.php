<?php
session_start();
require '../db_connect.php'; // ปรับพาธตามโครงสร้างโฟลเดอร์

// ตรวจสอบว่าผู้ใช้ได้เข้าสู่ระบบหรือไม่
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

// ตรวจสอบระดับการเข้าถึง
if ($_SESSION['access_level'] !== 'faculty') {
    echo "คุณไม่มีสิทธิ์เข้าถึงหน้านี้";
    exit();
}

// ดึงข้อมูลคณะของผู้ใช้
$faculty_id = $_SESSION['faculty_id']; 

// ตรวจสอบว่า faculty_id ถูกตั้งค่าไว้หรือไม่
if (!$faculty_id) {
    echo "ไม่พบข้อมูลคณะ";
    exit();
}

try {
    // ดึงข้อมูลคณะ
    $stmt = $pdo->prepare("SELECT * FROM faculty WHERE faculty_id = :faculty_id");
    $stmt->execute([':faculty_id' => $faculty_id]);
    $faculty = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$faculty) {
        echo "ไม่พบข้อมูลคณะ";
        exit();
    }

    // ดึงจำนวนภาควิชาในคณะนี้
    $stmt = $pdo->prepare("SELECT COUNT(*) AS department_count FROM department WHERE faculty_id = :faculty_id");
    $stmt->execute([':faculty_id' => $faculty_id]);
    $department_count = $stmt->fetch(PDO::FETCH_ASSOC)['department_count'];

    // ดึงจำนวนสาขาวิชาในคณะนี้
    $stmt = $pdo->prepare("
        SELECT COUNT(*) AS major_count 
        FROM major 
        WHERE department_id IN (SELECT department_id FROM department WHERE faculty_id = :faculty_id)
    ");
    $stmt->execute([':faculty_id' => $faculty_id]);
    $major_count = $stmt->fetch(PDO::FETCH_ASSOC)['major_count'];

    // คุณสามารถดึงข้อมูลเพิ่มเติมได้ตามต้องการ เช่น จำนวนศิษย์เก่า เป็นต้น

} catch (Exception $e) {
    echo "เกิดข้อผิดพลาด: " . $e->getMessage();
    exit();
}
?>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <title>แดชบอร์ดคณะ - <?= htmlspecialchars($faculty['faculty_name']) ?></title>
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

        .faculty-header {
            background-color: #343a40;
            color: #fff;
            padding: 20px;
            border-radius: 5px;
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
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
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
<?php include 'navbar-faculty.php'; ?>

    <div class="container">
        <div class="faculty-header text-center">
            <h2>คณะ<?= htmlspecialchars($faculty['faculty_name']) ?></h2>
            <p><?= nl2br(htmlspecialchars($faculty['faculty_description'])) ?></p>
        </div>

        <div class="row summary-cards">
            <div class="col-md-4">
                <div class="summary-card">
                    <h3><?= $department_count ?></h3>
                    <p>ภาควิชา</p>
                </div>
            </div>
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
