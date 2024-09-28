<!-- details.php -->
<?php
session_start();

// ตรวจสอบสิทธิ์ผู้ใช้: อนุญาตทั้ง faculty และ admin
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['access_level'], ['admin', 'faculty','department','major'])) {
    header("Location: login.php");
    exit();
}

require 'db_connect.php'; // เรียกใช้ไฟล์เชื่อมต่อฐานข้อมูล

// รับ person_id จาก GET parameter
$person_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// ตรวจสอบว่ามี person_id หรือไม่
if ($person_id <= 0) {
    header("Location: search_results.php");
    exit();
}

// ดึงข้อมูลส่วนบุคคล
$stmt = $pdo->prepare("SELECT * FROM personalinfo WHERE person_id = :person_id");
$stmt->bindParam(':person_id', $person_id);
$stmt->execute();
$person = $stmt->fetch(PDO::FETCH_ASSOC);

// ดึงข้อมูลที่อยู่พร้อมกับชื่อจังหวัด อำเภอ และตำบล
$stmt = $pdo->prepare("
    SELECT a.*, prov.name_in_thai AS province_name, dist.name_in_thai AS district_name, subd.name_in_thai AS subdistrict_name 
    FROM address a 
    LEFT JOIN provinces prov ON a.province = prov.id 
    LEFT JOIN districts dist ON a.district = dist.id 
    LEFT JOIN subdistricts subd ON a.sub_district = subd.id 
    WHERE a.person_id = :person_id
");
$stmt->bindParam(':person_id', $person_id);
$stmt->execute();
$address = $stmt->fetch(PDO::FETCH_ASSOC);

// แปลงประเภทที่อยู่ให้เป็นภาษาไทย
$type_label = '';
if ($address['address_type'] === 'permanent') {
    $type_label = 'ที่อยู่บ้าน';
} elseif ($address['address_type'] === 'contact') {
    $type_label = 'ที่อยู่ติดต่อ';
} elseif ($address['address_type'] === 'work') {
    $type_label = 'ที่ทำงาน';
}

// ดึงข้อมูลการศึกษา
$stmt = $pdo->prepare("SELECT * FROM education WHERE person_id = :person_id");
$stmt->bindParam(':person_id', $person_id);
$stmt->execute();
$education = $stmt->fetchAll(PDO::FETCH_ASSOC);

// ดึงข้อมูลรางวัลนักเรียน
$stmt = $pdo->prepare("SELECT * FROM studentaward WHERE person_id = :person_id");
$stmt->bindParam(':person_id', $person_id);
$stmt->execute();
$studentaward = $stmt->fetchAll(PDO::FETCH_ASSOC);

// ดึงข้อมูลประสบการณ์การทำงาน
$stmt = $pdo->prepare("SELECT * FROM workexperience WHERE person_id = :person_id");
$stmt->bindParam(':person_id', $person_id);
$stmt->execute();
$workexperience = $stmt->fetchAll(PDO::FETCH_ASSOC);

// ดึงข้อมูลรางวัล
$stmt = $pdo->prepare("SELECT * FROM awardhistory WHERE person_id = :person_id");
$stmt->bindParam(':person_id', $person_id);
$stmt->execute();
$awardhistory = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>รายละเอียดข้อมูลบุคคล</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Google Fonts (Sarabun) -->
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@400;500;600&display=swap" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="assets/css/styles.css" rel="stylesheet">
    <style>
        /* เพิ่มสไตล์สำหรับปุ่มกลับ */
        .back-button {
            margin-bottom: 20px;
            z-index: 1000;
            /* ให้ปุ่มอยู่เหนือองค์ประกอบอื่นๆ */
        }
    </style>
</head>

<body>
   
    <div class="header">
        <h2>รายละเอียดข้อมูลบุคคล</h2>
    </div>

    <div class="container">
         <!-- ปุ่มกลับที่มุมซ้ายบน -->
    <button type="button" class="btn btn-secondary back-button" onclick="history.back()">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-arrow-left"
            viewBox="0 0 16 16">
            <path fill-rule="evenodd"
                d="M15 8a.5.5 0 0 1-.5.5H2.707l3.147 3.146a.5.5 0 0 1-.708.708l-4-4a.5.5 0 0 1 0-.708l4-4a.5.5 0 1 1 .708.708L2.707 7.5H14.5A.5.5 0 0 1 15 8z" />
        </svg>
        กลับ
    </button>
        <div class="row">
            <!-- ฝั่งซ้าย: ข้อมูลส่วนบุคคลและที่อยู่ -->
            <div class="col-lg-4 col-md-5 mb-4">
                <?php include 'components/personal_info.php'; ?>
                <?php include 'components/address.php'; ?>
            </div>

            <!-- ฝั่งขวา: ข้อมูลการศึกษา รางวัลนักเรียน ประสบการณ์การทำงาน ประวัติการรับรางวัล -->
            <div class="col-lg-8 col-md-7">
                <?php include 'components/education.php'; ?>
                <?php include 'components/student_award.php'; ?>
                <?php include 'components/work_experience.php'; ?>
                <?php include 'components/award_history.php'; ?>
            </div>
        </div>
    </div>

    <!-- Modals -->
    <?php include 'modals/add_education_modal.php'; ?>
    <?php include 'modals/edit_education_modal.php'; ?>
    <?php include 'modals/add_student_award_modal.php'; ?>
    <?php include 'modals/edit_student_award_modal.php'; ?>
    <?php include 'modals/confirm_delete_student_award_modal.php'; ?>
    <?php include 'modals/add_work_experience_modal.php'; ?>
    <?php include 'modals/edit_work_experience_modal.php'; ?>
    <?php include 'modals/confirm_delete_work_experience_modal.php'; ?>
    <?php include 'modals/confirm_delete__education_modal.php'; ?>
    <?php include 'modals/edit_personal_info_modal.php'; ?>
    <?php include 'modals/edit_address_modal.php'; ?>
    <?php include 'modals/add_award_history_modal.php'; ?>
    <?php include 'modals/edit_award_history_modal.php'; ?>
    <?php include 'modals/confirm_delete_award_history_modal.php'; ?>

    <!-- Toast Notification -->
    <div class="position-fixed bottom-0 end-0 p-3" style="z-index: 11">
        <div id="toastNotification" class="toast" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="toast-header">
                <strong class="me-auto">แจ้งเตือน</strong>
                <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
            <div class="toast-body" id="toastBody">
                <!-- ข้อความจะแสดงที่นี่ -->
            </div>
        </div>
    </div>

    <!-- กำหนด personId ให้กับ JavaScript -->
    <script>
        var personId = <?= htmlspecialchars($person_id) ?>;
    </script>

    <!-- Bootstrap 5 JS (รวม Popper.js อยู่ใน bundle) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Custom JS -->
    <script src="assets/js/scripts.js"></script>
</body>

</html>