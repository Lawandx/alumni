<?php
session_start();

// ตรวจสอบว่าผู้ใช้ได้เข้าสู่ระบบแล้วหรือยัง และมีสิทธิ์เป็น admin หรือไม่
if (!isset($_SESSION['user_id']) || $_SESSION['access_level'] !== 'admin') {
    header("Location: login.php");
    exit();
}

require 'db_connect.php'; // เรียกใช้ไฟล์เชื่อมต่อฐานข้อมูล

// ดึงข้อมูลสำหรับรายงานต่างๆ

// 1. จำนวนศิษย์เก่าทั้งหมด
$stmt_total_alumni = $pdo->prepare("SELECT COUNT(*) AS total_alumni FROM personalinfo");
$stmt_total_alumni->execute();
$total_alumni = $stmt_total_alumni->fetch(PDO::FETCH_ASSOC)['total_alumni'];

// 2. จำนวนคณะ
$stmt_total_faculties = $pdo->prepare("SELECT COUNT(*) AS total_faculties FROM faculty");
$stmt_total_faculties->execute();
$total_faculties = $stmt_total_faculties->fetch(PDO::FETCH_ASSOC)['total_faculties'];

// 3. จำนวนภาควิชา
$stmt_total_departments = $pdo->prepare("SELECT COUNT(*) AS total_departments FROM department");
$stmt_total_departments->execute();
$total_departments = $stmt_total_departments->fetch(PDO::FETCH_ASSOC)['total_departments'];

// 4. จำนวนสาขาวิชา
$stmt_total_majors = $pdo->prepare("SELECT COUNT(*) AS total_majors FROM major");
$stmt_total_majors->execute();
$total_majors = $stmt_total_majors->fetch(PDO::FETCH_ASSOC)['total_majors'];

// 5. จำนวนรางวัลนักศึกษาและรางวัลทำงานทั้งหมด
$stmt_total_student_awards = $pdo->prepare("SELECT COUNT(*) AS total_student_awards FROM studentaward");
$stmt_total_student_awards->execute();
$total_student_awards = $stmt_total_student_awards->fetch(PDO::FETCH_ASSOC)['total_student_awards'];

$stmt_total_work_awards = $pdo->prepare("SELECT COUNT(*) AS total_work_awards FROM awardhistory");
$stmt_total_work_awards->execute();
$total_work_awards = $stmt_total_work_awards->fetch(PDO::FETCH_ASSOC)['total_work_awards'];

$total_awards = $total_student_awards + $total_work_awards;

$stmt_working_alumni = $pdo->prepare("SELECT COUNT(DISTINCT person_id) AS working_alumni FROM workexperience");
$stmt_working_alumni->execute();
$working_alumni = $stmt_working_alumni->fetch(PDO::FETCH_ASSOC)['working_alumni'];

// 6. จำนวนศิษย์เก่าตามตำแหน่ง (sub_district) พร้อมพิกัด
$stmt_users_by_subdistrict = $pdo->prepare("
    SELECT 
        sd.name_in_thai AS subdistrict_name,
        sd.latitude,
        sd.longitude,
        COUNT(p.person_id) AS user_count
    FROM PersonalInfo p
    JOIN Address a ON p.person_id = a.person_id
    JOIN subdistricts sd ON a.sub_district = sd.id
    GROUP BY sd.id, sd.name_in_thai, sd.latitude, sd.longitude
    HAVING sd.latitude IS NOT NULL AND sd.longitude IS NOT NULL
    ORDER BY user_count DESC
");
$stmt_users_by_subdistrict->execute();
$users_by_subdistrict = $stmt_users_by_subdistrict->fetchAll(PDO::FETCH_ASSOC);

// 7. ดึงข้อมูลของผู้ใช้ที่ล็อกอินเข้ามา
$stmt_current_user = $pdo->prepare("
    SELECT 
        u.user_id, 
        u.username, 
        u.email, 
        u.access_level, 
        u.password_part,
        f.faculty_name, 
        d.department_name, 
        m.major_name
    FROM user u
    LEFT JOIN faculty f ON u.faculty_id = f.faculty_id
    LEFT JOIN department d ON u.department_id = d.department_id
    LEFT JOIN major m ON u.major_id = m.major_id
    WHERE u.user_id = :user_id
");
$stmt_current_user->execute([':user_id' => $_SESSION['user_id']]);
$current_user = $stmt_current_user->fetch(PDO::FETCH_ASSOC);

// 8. จำนวนศิษย์เก่าตามคณะ
$stmt_users_by_faculty = $pdo->prepare("
    SELECT edu.faculty_name AS faculty, COUNT(p.person_id) AS user_count
    FROM PersonalInfo p
    JOIN education edu ON p.person_id = edu.person_id
    GROUP BY edu.faculty_name
    ORDER BY user_count DESC
");
$stmt_users_by_faculty->execute();
$users_by_faculty = $stmt_users_by_faculty->fetchAll(PDO::FETCH_ASSOC);

// 9. จำนวนศิษย์เก่าตามสาขาวิชา
$stmt_users_by_major = $pdo->prepare("
    SELECT 
        m.major_name AS major, 
        COUNT(p.person_id) AS user_count
    FROM PersonalInfo p
    JOIN education edu ON p.person_id = edu.person_id
    JOIN major m ON edu.major_name = m.major_name
    GROUP BY m.major_name
    ORDER BY user_count DESC
");
$stmt_users_by_major->execute();
$users_by_major = $stmt_users_by_major->fetchAll(PDO::FETCH_ASSOC);

// 10. จำนวนศิษย์เก่าตามบริษัท
$stmt_users_by_company = $pdo->prepare("
    SELECT 
        company_name, 
        COUNT(DISTINCT person_id) AS alumni_count
    FROM workexperience
    WHERE company_name IS NOT NULL AND company_name != ''
    GROUP BY company_name
    ORDER BY alumni_count DESC
    LIMIT 10
");
$stmt_users_by_company->execute();
$users_by_company = $stmt_users_by_company->fetchAll(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Google Fonts (Sarabun) -->
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@400;500;600&display=swap" rel="stylesheet">
    <!-- Font Awesome for Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Leaflet CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.3/dist/leaflet.css" />
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Leaflet JS -->
    <script src="https://unpkg.com/leaflet@1.9.3/dist/leaflet.js"></script>
    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <!-- DataTables JS -->
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <!-- Chart.js JS -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body {
            font-family: "Sarabun", sans-serif;
            background-color: #f8f9fa;
        }

        .summary-card {
            min-height: 100px;
            /* ลดขนาดความสูงลง */
            padding: 15px;
            /* ลด padding */
            margin-bottom: 10px;
            /* ลดระยะห่างระหว่างการ์ด */
            border-radius: 8px;
            /* ให้มุมมน */
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            /* เพิ่มเงาเพื่อความสวยงาม */
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
        }

        .summary-number {
            font-size: 1.5rem;
            /* ลดขนาดตัวเลขลง */
            font-weight: bold;
            margin: 5px 0;
        }

        .card-body i {
            font-size: 1.8rem;
            /* ลดขนาดไอคอนลง */
            margin-right: 15px;
            /* เพิ่มระยะห่างระหว่างไอคอนกับตัวเลข */
        }

        .card-text {
            font-size: 0.9rem;
            /* ลดขนาดข้อความคำบรรยาย */
            margin: 0;
        }

        #usersMap {
            height: 500px;
            border-radius: 10px;
        }

        .legend {
            background: white;
            padding: 10px;
            font-size: 14px;
            line-height: 1.5;
        }

        .legend i {
            display: inline-block;
            width: 20px;
            height: 20px;
            margin-right: 5px;
        }

        /* เพิ่มสไตล์สำหรับการ์ดแผนภูมิ */
        .card-body canvas {
            width: 100% !important;
            height: auto !important;
        }

        /* เพิ่มสไตล์สำหรับตาราง */
        .data-table {
            width: 100%;
        }

        .data-table th,
        .data-table td {
            text-align: center;
            vertical-align: middle;
        }

        /* เพิ่มสไตล์สำหรับ DataTables */
        table.dataTable thead th,
        table.dataTable thead td {
            border-bottom: none;
        }

        table.dataTable.no-footer {
            border-bottom: none;
        }

        table.dataTable tbody tr:nth-of-type(odd) {
            background-color: #f2f2f2;
        }

        table.dataTable tbody tr:hover {
            background-color: #ddd;
        }

        /* ปรับแต่งปุ่มของ DataTables */
        .dataTables_wrapper .dataTables_paginate .paginate_button {
            padding: 0.2em 0.6em;
            margin-left: 2px;
            display: inline-block;
            border: 1px solid transparent;
            border-radius: 4px;
        }

        .dataTables_wrapper .dataTables_paginate .paginate_button.current,
        .dataTables_wrapper .dataTables_paginate .paginate_button.current:hover {
            background-color: #0d6efd;
            color: white !important;
            border: 1px solid #0d6efd;
            cursor: default;
        }

        .dataTables_wrapper .dataTables_filter input {
            border-radius: 4px;
            border: 1px solid #ced4da;
            padding: 0.375rem 0.75rem;
        }
    </style>
</head>

<body>
    <?php include 'navbar.php'; ?>

    <div class="container mt-4">
        <!-- แสดงข้อมูลของผู้ใช้ที่ล็อกอินเข้ามา -->
        <div class="card mb-5">
            <div class="card-header bg-info text-white">
                <i class="fas fa-user-circle"></i> ข้อมูลผู้ใช้ของคุณ
                <button class="btn btn-sm btn-light float-end" data-bs-toggle="modal" data-bs-target="#editUserModal">
                    <i class="fas fa-edit"></i> แก้ไขข้อมูล
                </button>
            </div>
            <div class="card-body">
                <?php if ($current_user): ?>
                    <table class="table table-bordered">
                        <tr>
                            <th>รหัสผู้ใช้</th>
                            <td><?= htmlspecialchars($current_user['user_id']) ?></td>
                        </tr>
                        <tr>
                            <th>ชื่อผู้ใช้</th>
                            <td><?= htmlspecialchars($current_user['username']) ?></td>
                        </tr>
                        <tr>
                            <th>Email</th>
                            <td><?= htmlspecialchars($current_user['email']) ?></td>
                        </tr>
                        <tr>
                            <th>ระดับการเข้าถึง</th>
                            <td><?= htmlspecialchars($current_user['access_level']) ?></td>
                        </tr>
                        <tr>
                            <th>คณะ</th>
                            <td><?= htmlspecialchars($current_user['faculty_name']) ?></td>
                        </tr>
                        <tr>
                            <th>ภาควิชา</th>
                            <td><?= htmlspecialchars($current_user['department_name']) ?></td>
                        </tr>
                        <tr>
                            <th>สาขาวิชา</th>
                            <td><?= htmlspecialchars($current_user['major_name']) ?></td>
                        </tr>
                        <tr>
                            <th>รหัสที่ซ่อน</th>
                            <td>
                                <span id="passwordPartMasked"><?= str_repeat('*', strlen($current_user['password_part'])) ?></span>
                                <button class="btn btn-sm btn-secondary" id="revealPasswordPartBtn" data-bs-toggle="modal" data-bs-target="#passwordModal">ดูรหัส</button>
                            </td>
                        </tr>
                    </table>
                <?php else: ?>
                    <p class="text-danger">ไม่พบข้อมูลของผู้ใช้</p>
                <?php endif; ?>
            </div>
        </div>

        <!-- แสดงข้อมูลภาพรวมด้วย Cards -->
        <div class="row g-4 mb-5">
            <!-- จำนวนศิษย์เก่าทั้งหมด -->
            <div class="col-md-3 col-sm-6">
                <div class="card text-white bg-primary h-100 summary-card">
                    <div class="card-body d-flex align-items-center">
                        <i class="fas fa-users fa-3x"></i>
                        <div class="ms-3">
                            <p class="card-text">จำนวนศิษย์เก่าทั้งหมด</p>
                            <h5 class="card-title"><?= $total_alumni ?></h5>
                        </div>
                    </div>
                </div>
            </div>

            <!-- จำนวนคณะ -->
            <div class="col-md-3 col-sm-6">
                <div class="card text-white bg-success h-100 summary-card">
                    <div class="card-body d-flex align-items-center">
                        <i class="fas fa-building fa-3x"></i>
                        <div class="ms-3">
                            <p class="card-text">จำนวนคณะทั้งหมด</p>
                            <h5 class="card-title"><?= $total_faculties ?></h5>
                        </div>
                    </div>
                </div>
            </div>

            <!-- จำนวนภาควิชา -->
            <div class="col-md-3 col-sm-6">
                <div class="card text-white bg-warning h-100 summary-card">
                    <div class="card-body d-flex align-items-center">
                        <i class="fas fa-layer-group fa-3x"></i>
                        <div class="ms-3">
                            <p class="card-text">จำนวนภาควิชา</p>
                            <h5 class="card-title"><?= $total_departments ?></h5>
                        </div>
                    </div>
                </div>
            </div>

            <!-- จำนวนสาขาวิชา -->
            <div class="col-md-3 col-sm-6">
                <div class="card text-white bg-danger h-100 summary-card">
                    <div class="card-body d-flex align-items-center">
                        <i class="fas fa-graduation-cap fa-3x"></i>
                        <div class="ms-3">
                            <p class="card-text">จำนวนสาขาวิชา</p>
                            <h5 class="card-title"><?= $total_majors ?></h5>
                        </div>
                    </div>
                </div>
            </div>

            <!-- จำนวนรางวัลทั้งหมด -->
            <div class="col-md-3 col-sm-6">
                <div class="card text-white bg-info h-100 summary-card">
                    <div class="card-body d-flex align-items-center">
                        <i class="fas fa-trophy fa-3x"></i>
                        <div class="ms-3">
                            <p class="card-text">จำนวนรางวัลทั้งหมด</p>
                            <h5 class="card-title"><?= $total_awards ?></h5>
                        </div>
                    </div>
                </div>
            </div>

            <!-- จำนวนผู้ที่ได้ทำงาน -->
            <div class="col-md-3 col-sm-6">
                <div class="card text-white bg-secondary h-100 summary-card">
                    <div class="card-body d-flex align-items-center">
                        <i class="fas fa-briefcase fa-3x"></i>
                        <div class="ms-3">
                            <p class="card-text">จำนวนผู้ที่ได้ทำงาน</p>
                            <h5 class="card-title"><?= $working_alumni ?></h5>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- แผนที่ -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <i class="fas fa-map-marked-alt"></i> แผนที่ศิษย์เก่า
                    </div>
                    <div class="card-body">
                        <div id="usersMap"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ตารางจำนวนศิษย์เก่าตามคณะ -->
        <div class="row mt-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <i class="fas fa-chart-bar"></i> จำนวนศิษย์เก่าตามคณะ
                    </div>
                    <div class="card-body">
                        <table class="table table-bordered data-table">
                            <thead class="table-light">
                                <tr>
                                    <th>คณะ</th>
                                    <th>จำนวนศิษย์เก่า</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($users_by_faculty as $faculty): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($faculty['faculty']) ?></td>
                                        <td><?= $faculty['user_count'] ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- ตารางจำนวนศิษย์เก่าตามสาขาวิชา -->
        <div class="row mt-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <i class="fas fa-graduation-cap"></i> จำนวนศิษย์เก่าตามสาขาวิชา
                    </div>
                    <div class="card-body">
                        <table class="table table-bordered data-table">
                            <thead class="table-light">
                                <tr>
                                    <th>สาขาวิชา</th>
                                    <th>จำนวนศิษย์เก่า</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($users_by_major as $major): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($major['major']) ?></td>
                                        <td><?= $major['user_count'] ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <!-- รายงานบริษัทที่ศิษย์เก่าไปทำงานและจัดลำดับความนิยม -->
        <div class="row mt-4">
            <div class="col-12">
                <div class="card mb-4">
                    <div class="card-header">
                        <i class="fas fa-building"></i> บริษัทที่ศิษย์เก่าไปทำงานและจัดลำดับความนิยม
                    </div>
                    <div class="card-body">
                        <!-- ตารางบริษัทที่ศิษย์เก่าไปทำงาน -->
                        <table class="table table-bordered data-table" id="companyTable">
                            <thead class="table-light">
                                <tr>
                                    <th>บริษัท</th>
                                    <th>จำนวนศิษย์เก่า</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($users_by_company as $company): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($company['company_name']) ?></td>
                                        <td><?= $company['alumni_count'] ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>

                        <!-- กราฟแสดงความนิยมของบริษัท (Optional) -->
                        <canvas id="usersByCompanyChart" class="mt-4" height="400"></canvas>
                    </div>
                </div>
            </div>
        </div>



    </div>

    <!-- Modal สำหรับกรอกรหัสผ่าน -->
    <div class="modal fade" id="passwordModal" tabindex="-1" aria-labelledby="passwordModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <form id="passwordForm">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="passwordModalLabel">ยืนยันตัวตน</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="ปิด"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="adminPassword" class="form-label">กรอกรหัสผ่านของ <?= htmlspecialchars($current_user['username']) ?></label>
                            <input type="password" class="form-control" id="adminPassword" name="password" required>
                        </div>
                        <div id="passwordError" class="text-danger"></div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
                        <button type="submit" class="btn btn-primary">ยืนยัน</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal สำหรับแก้ไขข้อมูลผู้ใช้ -->
    <div class="modal fade" id="editUserModal" tabindex="-1" aria-labelledby="editUserModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <form id="editUserForm">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="editUserModalLabel">แก้ไขข้อมูลผู้ใช้</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="ปิด"></button>
                    </div>
                    <div class="modal-body">
                        <!-- แสดงชื่อผู้ใช้ -->
                        <div class="mb-3">
                            <label class="form-label">ชื่อผู้ใช้</label>
                            <input type="text" class="form-control" value="<?= htmlspecialchars($current_user['username']) ?>" readonly>
                        </div>
                        <!-- แก้ไข Email -->
                        <div class="mb-3">
                            <label for="editEmail" class="form-label">Email</label>
                            <input type="email" class="form-control" id="editEmail" name="email" value="<?= htmlspecialchars($current_user['email']) ?>" required>
                        </div>
                        <!-- เปลี่ยนรหัสผ่าน -->
                        <hr>
                        <h5>เปลี่ยนรหัสผ่าน</h5>
                        <div class="mb-3">
                            <label for="currentPassword" class="form-label">รหัสผ่านเก่า</label>
                            <input type="password" class="form-control" id="currentPassword" name="current_password">
                        </div>
                        <div class="mb-3">
                            <label for="newPassword" class="form-label">รหัสผ่านใหม่</label>
                            <input type="password" class="form-control" id="newPassword" name="new_password">
                        </div>
                        <div class="mb-3">
                            <label for="confirmNewPassword" class="form-label">ยืนยันรหัสผ่านใหม่</label>
                            <input type="password" class="form-control" id="confirmNewPassword" name="confirm_new_password">
                        </div>
                        <div id="editUserError" class="text-danger"></div>
                        <div id="editUserSuccess" class="text-success"></div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
                        <button type="submit" class="btn btn-primary">บันทึกการเปลี่ยนแปลง</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- JavaScript สำหรับการสร้างแผนที่ -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const map = L.map('usersMap').setView([13.736717, 100.523186], 6); // พิกัดเริ่มต้นที่ประเทศไทย

            // เพิ่มเลเยอร์แผนที่จาก OpenStreetMap
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; OpenStreetMap contributors'
            }).addTo(map);

            // ข้อมูลจาก PHP สำหรับตำแหน่งศิษย์เก่า
            const usersBySubdistrict = <?= json_encode($users_by_subdistrict) ?>;

            // สร้างสีตามจำนวนศิษย์เก่า
            function getColor(count) {
                return count > 100 ? '#800026' :
                    count > 50 ? '#BD0026' :
                    count > 20 ? '#E31A1C' :
                    count > 10 ? '#FC4E2A' :
                    count > 5 ? '#FD8D3C' :
                    '#FEB24C';
            }

            // เพิ่มจุด marker สำหรับแต่ละตำแหน่ง
            usersBySubdistrict.forEach(function(subdistrict) {
                const marker = L.circleMarker([subdistrict.latitude, subdistrict.longitude], {
                    radius: 8,
                    fillColor: getColor(subdistrict.user_count),
                    color: '#000',
                    weight: 1,
                    fillOpacity: 0.8
                }).addTo(map);

                marker.bindPopup(`<strong>${subdistrict.subdistrict_name}</strong><br>จำนวนศิษย์เก่า: ${subdistrict.user_count}`);
            });

            // สร้าง legend สำหรับแสดงข้อมูลสีของ marker
            const legend = L.control({
                position: 'bottomright'
            });

            legend.onAdd = function(map) {
                const div = L.DomUtil.create('div', 'legend'),
                    grades = [0, 5, 10, 20, 50, 100],
                    labels = [];

                div.innerHTML += '<strong>จำนวนศิษย์เก่า</strong><br>';
                for (let i = 0; i < grades.length; i++) {
                    div.innerHTML += '<i style="background:' + getColor(grades[i] + 1) + '"></i> ' +
                        grades[i] + (grades[i + 1] ? '&ndash;' + grades[i + 1] + '<br>' : '+');
                }

                return div;
            };

            legend.addTo(map);
        });
    </script>
    <script>
        $(document).ready(function() {
            const revealBtn = $('#revealPasswordPartBtn');
            const passwordForm = $('#passwordForm');
            const passwordModal = new bootstrap.Modal(document.getElementById('passwordModal'), {
                keyboard: false
            });
            const passwordError = $('#passwordError');
            const passwordPartMasked = $('#passwordPartMasked');

            passwordForm.on('submit', function(e) {
                e.preventDefault();
                const password = $('#adminPassword').val();

                // ส่ง AJAX request ไปยัง reveal_password_part.php
                $.post('reveal_password_part.php', {
                        password: password
                    })
                    .done(function(data) {
                        if (data.success) {
                            // แสดง password_part ที่ได้รับจากเซิร์ฟเวอร์
                            passwordPartMasked.text(data.password_part);
                            // ปิด Modal
                            passwordModal.hide();
                            // ล้างฟอร์มและข้อความผิดพลาด
                            passwordForm[0].reset();
                            passwordError.text('');
                        } else {
                            // แสดงข้อความผิดพลาด
                            passwordError.text(data.message || 'เกิดข้อผิดพลาด');
                        }
                    })
                    .fail(function(error) {
                        console.error('Error:', error);
                        passwordError.text('เกิดข้อผิดพลาดในการเชื่อมต่อเซิร์ฟเวอร์');
                    });
            });
        });

        // JavaScript สำหรับการแก้ไขข้อมูลผู้ใช้
        $(document).ready(function() {
            const editUserForm = $('#editUserForm');
            const editUserError = $('#editUserError');
            const editUserSuccess = $('#editUserSuccess');

            editUserForm.on('submit', function(e) {
                e.preventDefault();
                const email = $('#editEmail').val();
                const currentPassword = $('#currentPassword').val();
                const newPassword = $('#newPassword').val();
                const confirmNewPassword = $('#confirmNewPassword').val();

                // ตรวจสอบว่ารหัสผ่านใหม่และรหัสผ่านยืนยันตรงกันหรือไม่
                if (newPassword !== confirmNewPassword) {
                    editUserError.text('รหัสผ่านใหม่และรหัสผ่านยืนยันไม่ตรงกัน');
                    editUserSuccess.text('');
                    return;
                }

                // ส่งข้อมูลผ่าน AJAX ไปยัง update_user.php
                const formData = {
                    email: email,
                    current_password: currentPassword,
                    new_password: newPassword,
                    confirm_new_password: confirmNewPassword
                };

                $.post('update_user.php', formData)
                    .done(function(data) {
                        if (data.success) {
                            editUserSuccess.text('แก้ไขข้อมูลสำเร็จ');
                            editUserError.text('');
                            // ปิด Modal หลังจากแก้ไขสำเร็จ
                            setTimeout(() => {
                                $('#editUserModal').modal('hide');
                                location.reload(); // รีเฟรชหน้าเพื่ออัปเดตข้อมูล
                            }, 1500);
                        } else {
                            editUserError.text(data.message || 'เกิดข้อผิดพลาดในการแก้ไขข้อมูล');
                            editUserSuccess.text('');
                        }
                    })
                    .fail(function(error) {
                        console.error('Error:', error);
                        editUserError.text('เกิดข้อผิดพลาดในการเชื่อมต่อเซิร์ฟเวอร์');
                        editUserSuccess.text('');
                    });
            });

            // Reset error and success messagesเมื่อเปิด Modal
            $('#editUserModal').on('show.bs.modal', function() {
                editUserError.text('');
                editUserSuccess.text('');
                editUserForm[0].reset();
            });
        });
    </script>
    <script>
        const revealBtn = $('#revealPasswordPartBtn');
        const passwordForm = $('#passwordForm');
        const passwordModal = new bootstrap.Modal(document.getElementById('passwordModal'), {
            keyboard: false
        });
        const passwordError = $('#passwordError');
        const passwordPartMasked = $('#passwordPartMasked');

        passwordForm.on('submit', function(e) {
            e.preventDefault();
            const password = $('#adminPassword').val();

            // ส่ง AJAX request ไปยัง reveal_password_part.php
            $.post('reveal_password_part.php', {
                    password: password
                })
                .done(function(data) {
                    if (data.success) {
                        // แสดง password_part ที่ได้รับจากเซิร์ฟเวอร์
                        passwordPartMasked.text(data.password_part);
                        // ปิด Modal
                        passwordModal.hide();
                        // ล้างฟอร์มและข้อความผิดพลาด
                        passwordForm[0].reset();
                        passwordError.text('');
                    } else {
                        // แสดงข้อความผิดพลาด
                        passwordError.text(data.message || 'เกิดข้อผิดพลาด');
                    }
                })
                .fail(function(error) {
                    console.error('Error:', error);
                    passwordError.text('เกิดข้อผิดพลาดในการเชื่อมต่อเซิร์ฟเวอร์');
                });
        });

        // JavaScript สำหรับการแก้ไขข้อมูลผู้ใช้
        const editUserForm = $('#editUserForm');
        const editUserError = $('#editUserError');
        const editUserSuccess = $('#editUserSuccess');

        editUserForm.on('submit', function(e) {
            e.preventDefault();
            const email = $('#editEmail').val();
            const currentPassword = $('#currentPassword').val();
            const newPassword = $('#newPassword').val();
            const confirmNewPassword = $('#confirmNewPassword').val();

            // ตรวจสอบว่ารหัสผ่านใหม่และรหัสผ่านยืนยันตรงกันหรือไม่
            if (newPassword !== confirmNewPassword) {
                editUserError.text('รหัสผ่านใหม่และรหัสผ่านยืนยันไม่ตรงกัน');
                editUserSuccess.text('');
                return;
            }

            // ส่งข้อมูลผ่าน AJAX ไปยัง update_user.php
            const formData = {
                email: email,
                current_password: currentPassword,
                new_password: newPassword,
                confirm_new_password: confirmNewPassword
            };

            $.post('update_user.php', formData)
                .done(function(data) {
                    if (data.success) {
                        editUserSuccess.text('แก้ไขข้อมูลสำเร็จ');
                        editUserError.text('');
                        // ปิด Modal หลังจากแก้ไขสำเร็จ
                        setTimeout(() => {
                            $('#editUserModal').modal('hide');
                            location.reload(); // รีเฟรชหน้าเพื่ออัปเดตข้อมูล
                        }, 1500);
                    } else {
                        editUserError.text(data.message || 'เกิดข้อผิดพลาดในการแก้ไขข้อมูล');
                        editUserSuccess.text('');
                    }
                })
                .fail(function(error) {
                    console.error('Error:', error);
                    editUserError.text('เกิดข้อผิดพลาดในการเชื่อมต่อเซิร์ฟเวอร์');
                    editUserSuccess.text('');
                });
        });

        // Reset error and success messagesเมื่อเปิด Modal
        $('#editUserModal').on('show.bs.modal', function() {
            editUserError.text('');
            editUserSuccess.text('');
            editUserForm[0].reset();
        });
    </script>
    <script>
        $(document).ready(function() {
            // เริ่มต้น DataTables บนตารางที่มีคลาส 'data-table'
            $('.data-table').DataTable({
                "language": {
                    "search": "ค้นหา:",
                    "lengthMenu": "แสดง _MENU_ แถวต่อหน้า",
                    "zeroRecords": "ไม่พบข้อมูลที่ค้นหา",
                    "info": "แสดงหน้าที่ _PAGE_ จาก _PAGES_",
                    "infoEmpty": "ไม่พบข้อมูล",
                    "infoFiltered": "(กรองจากทั้งหมด _MAX_ รายการ)",
                    "paginate": {
                        "first": "แรก",
                        "last": "สุดท้าย",
                        "next": "ถัดไป",
                        "previous": "ก่อนหน้า"
                    }
                },
                "pagingType": "simple_numbers",
                "lengthMenu": [
                    [10, 25, 50, 100, -1],
                    [10, 25, 50, 100, "ทั้งหมด"]
                ],
                "pageLength": 10,
                "responsive": true
            });
        });
        // ข้อมูลสำหรับกราฟบริษัท
        const usersByCompany = <?= json_encode($users_by_company) ?>;
        const companyLabels = usersByCompany.map(item => item.company_name);
        const companyData = usersByCompany.map(item => item.alumni_count);

        // สร้างกราฟด้วย Chart.js
        const ctxCompany = document.getElementById('usersByCompanyChart').getContext('2d');
        const usersByCompanyChart = new Chart(ctxCompany, {
            type: 'bar', // เปลี่ยนเป็น 'pie' หรือ 'doughnut' หากต้องการ
            data: {
                labels: companyLabels,
                datasets: [{
                    label: 'จำนวนศิษย์เก่า',
                    data: companyData,
                    backgroundColor: [
                        'rgba(54, 162, 235, 0.6)',
                        'rgba(255, 99, 132, 0.6)',
                        'rgba(255, 206, 86, 0.6)',
                        'rgba(75, 192, 192, 0.6)',
                        'rgba(153, 102, 255, 0.6)',
                        'rgba(255, 159, 64, 0.6)',
                        'rgba(199, 199, 199, 0.6)',
                        'rgba(83, 102, 255, 0.6)',
                        'rgba(255, 99, 255, 0.6)',
                        'rgba(99, 255, 132, 0.6)'
                        // เพิ่มสีเพิ่มเติมตามต้องการ
                    ],
                    borderColor: [
                        'rgba(54, 162, 235, 1)',
                        'rgba(255, 99, 132, 1)',
                        'rgba(255, 206, 86, 1)',
                        'rgba(75, 192, 192, 1)',
                        'rgba(153, 102, 255, 1)',
                        'rgba(255, 159, 64, 1)',
                        'rgba(199, 199, 199, 1)',
                        'rgba(83, 102, 255, 1)',
                        'rgba(255, 99, 255, 1)',
                        'rgba(99, 255, 132, 1)'
                        // เพิ่มสีเพิ่มเติมตามต้องการ
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            font: {
                                size: 12
                            }
                        }
                    },
                    title: {
                        display: true,
                        text: 'บริษัทที่ศิษย์เก่าไปทำงานมากที่สุด',
                        font: {
                            size: 16
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        precision: 0,
                        title: {
                            display: true,
                            text: 'จำนวนศิษย์เก่า'
                        }
                    },
                    x: {
                        title: {
                            display: true,
                            text: 'บริษัท'
                        }
                    }
                }
            }
        });
    </script>

</body>

</html>