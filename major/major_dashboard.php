<?php 
// major_dashboard.php

session_start();

// ตรวจสอบสิทธิ์ผู้ใช้: อนุญาตเฉพาะ major
if (!isset($_SESSION['user_id']) || $_SESSION['access_level'] !== 'major') {
    header("Location: ../login.php"); // ปรับพาธให้ถูกต้อง
    exit();
}

require '../db_connect.php'; // ปรับพาธให้ถูกต้องตามโครงสร้างโฟลเดอร์

// ตรวจสอบว่า faculty_id, department_id และ major_id ถูกตั้งค่าไว้ใน SESSION หรือไม่
if (!isset($_SESSION['faculty_id']) || !isset($_SESSION['department_id']) || !isset($_SESSION['major_id'])) {
    echo "เกิดข้อผิดพลาด: ไม่พบข้อมูลคณะ ภาควิชา หรือสาขาวิชาใน SESSION";
    exit();
}

$faculty_id = $_SESSION['faculty_id'];
$department_id = $_SESSION['department_id'];
$major_id = $_SESSION['major_id'];

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

    // ดึงข้อมูลสาขาวิชา
    $stmt = $pdo->prepare("SELECT * FROM major WHERE major_id = :major_id AND department_id = :department_id");
    $stmt->execute([
        ':major_id' => $major_id,
        ':department_id' => $department_id
    ]);
    $major = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$major) {
        echo "ไม่พบข้อมูลสาขาวิชา หรือสาขาวิชานี้ไม่ได้สังกัดในภาควิชาที่ระบุ";
        exit();
    }

    // ดึงจำนวนศิษย์เก่าในสาขาวิชานี้
    $stmt = $pdo->prepare("
        SELECT COUNT(DISTINCT p.person_id) AS alumni_count
        FROM personalinfo p
        JOIN education e ON p.person_id = e.person_id
        WHERE e.major_name = :major_name
    ");
    $stmt->execute([':major_name' => $major['major_name']]);
    $alumni_count = $stmt->fetch(PDO::FETCH_ASSOC)['alumni_count'];

    // ดึงข้อมูลรางวัลที่ได้รับจากศิษย์เก่าในสาขาวิชานี้
    $stmt = $pdo->prepare("
        SELECT COUNT(ah.award_id) AS total_awards
        FROM awardhistory ah
        JOIN personalinfo p ON ah.person_id = p.person_id
        JOIN education e ON p.person_id = e.person_id
        WHERE e.major_name = :major_name
    ");
    $stmt->execute([':major_name' => $major['major_name']]);
    $awards = $stmt->fetch(PDO::FETCH_ASSOC)['total_awards'];

    // ดึงข้อมูลจำนวนศิษย์เก่าในแต่ละตำแหน่ง (sub_district) ของสาขาวิชานี้
    $stmt_users_by_subdistrict = $pdo->prepare("
        SELECT 
            sd.name_in_thai AS subdistrict_name,
            sd.latitude,
            sd.longitude,
            COUNT(p.person_id) AS user_count
        FROM personalinfo p
        JOIN address a ON p.person_id = a.person_id
        JOIN subdistricts sd ON a.sub_district = sd.id
        JOIN education e ON p.person_id = e.person_id
        WHERE e.major_name = :major_name
        GROUP BY sd.id, sd.name_in_thai, sd.latitude, sd.longitude
        HAVING sd.latitude IS NOT NULL AND sd.longitude IS NOT NULL
        ORDER BY user_count DESC
    ");
    $stmt_users_by_subdistrict->execute([':major_name' => $major['major_name']]);
    $users_by_subdistrict = $stmt_users_by_subdistrict->fetchAll(PDO::FETCH_ASSOC);

} catch (Exception $e) {
    // จัดการข้อผิดพลาด
    $error = $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <title>แดชบอร์ดสาขาวิชา - <?= htmlspecialchars($major['major_name']) ?></title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Google Fonts (Sarabun) -->
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@400;500;600&display=swap" rel="stylesheet">
    <!-- Font Awesome สำหรับไอคอน -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.5/css/dataTables.bootstrap5.min.css">
    <!-- Leaflet CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <style>
        body {
            font-family: "Sarabun", sans-serif;
            background-color: #f8f9fa;
        }

        .container {
            margin-top: 30px;
        }

        .major-header {
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

        .alumni-table-container {
            margin-top: 50px;
        }

        .alumni-table {
            background-color: #ffffff;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        .alumni-map-container {
            margin-top: 50px;
            background-color: #ffffff;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        #alumniMap {
            height: 500px;
            border: 1px solid #dee2e6;
            border-radius: 5px;
        }

        .navbar-brand img {
            height: 50px;
        }

        @media (max-width: 576px) {
            .navbar-brand img {
                height: 40px;
            }

            .summary-card h3 {
                font-size: 28px;
            }

            .summary-card p {
                font-size: 16px;
            }
        }

        /* Legend Styles */
        .legend {
            background: white;
            line-height: 1.5em;
            padding: 6px 8px;
            font: 14px Arial, Helvetica, sans-serif;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.2);
            border-radius: 5px;
        }

        .legend i {
            display: inline-block;
            width: 18px;
            height: 18px;
            margin-right: 8px;
        }
    </style>
</head>

<body>
    <?php include 'navbar-major.php'; // รวม Navbar สำหรับ major ?>

    <div class="container">
        <div class="major-header">
            <h2><?= htmlspecialchars($major['major_name']) ?></h2>
            <p>สังกัดภาควิชา <?= htmlspecialchars($department['department_name']) ?>, คณะ <?= htmlspecialchars($faculty['faculty_name']) ?></p>
        </div>

        <div class="row summary-cards">
            <div class="col-md-4 col-sm-6 mb-4">
                <div class="summary-card">
                    <h3><?= number_format($alumni_count) ?></h3>
                    <p>ศิษย์เก่า</p>
                </div>
            </div>
            <div class="col-md-4 col-sm-6 mb-4">
                <div class="summary-card">
                    <h3><?= number_format($awards) ?></h3>
                    <p>รางวัล</p>
                </div>
            </div>
            <!-- คุณสามารถเพิ่มการ์ดสรุปอื่น ๆ ได้ตามต้องการ -->
        </div>

        <!-- ตารางแสดงจำนวนศิษย์เก่าในแต่ละตำแหน่ง -->
        <div class="alumni-table-container">
            <h4 class="text-center mb-4">จำนวนศิษย์เก่าในแต่ละตำแหน่ง</h4>
            <div class="alumni-table">
                <table class="table table-striped table-bordered" id="alumniBySubdistrictTable">
                    <thead class="table-dark">
                        <tr>
                            <th>ตำแหน่ง</th>
                            <th>จำนวนศิษย์เก่า</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users_by_subdistrict as $subdistrict): ?>
                            <tr>
                                <td><?= htmlspecialchars($subdistrict['subdistrict_name']) ?></td>
                                <td>
                                    <?php
                                        if ($subdistrict['user_count'] > 0) {
                                            echo number_format($subdistrict['user_count']);
                                        } else {
                                            echo "ไม่มีศิษย์ในตำแหน่งนี้";
                                        }
                                    ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- เพิ่มส่วนแผนที่แสดงตำแหน่งของศิษย์เก่าในสาขาวิชา -->
        <div class="alumni-map-container">
            <h4 class="text-center mb-4">แผนที่แสดงตำแหน่งของศิษย์เก่าในสาขาวิชา</h4>
            <div id="alumniMap"></div>
        </div>
    </div>

    <!-- Bootstrap 5 JS และ dependencies -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- jQuery (จำเป็นสำหรับ DataTables) -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- DataTables JS -->
    <script src="https://cdn.datatables.net/1.13.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.5/js/dataTables.bootstrap5.min.js"></script>
    <!-- DataTables Thai Localization -->
    <script src="https://cdn.datatables.net/plug-ins/1.13.5/i18n/th.json"></script>
    <!-- Leaflet JS -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        $(document).ready(function() {
            // DataTable สำหรับตารางจำนวนศิษย์เก่าในแต่ละตำแหน่ง
            $('#alumniBySubdistrictTable').DataTable({
                "language": {
                    "url": "https://cdn.datatables.net/plug-ins/1.13.5/i18n/th.json"
                },
                "paging": true,
                "searching": true,
                "ordering": true,
                "order": [[1, 'desc']], // เรียงลำดับจากจำนวนศิษย์เก่า
                "lengthMenu": [10, 25, 50, 100],
                "pageLength": 10
            });
        });

        // เริ่มต้นแผนที่
        var map = L.map('alumniMap').setView([13.7563, 100.5018], 6); // พิกัดเริ่มต้นที่ประเทศไทย

        // เพิ่มชั้นแผนที่จาก OpenStreetMap
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; OpenStreetMap contributors'
        }).addTo(map);

        // ฟังก์ชั่นสำหรับเลือกสีตามจำนวนศิษย์เก่า
        function getColor(count) {
            return count > 50 ? '#800026' :
                   count > 30 ? '#BD0026' :
                   count > 20 ? '#E31A1C' :
                   count > 10 ? '#FC4E2A' :
                   count > 5  ? '#FD8D3C' :
                   count > 0  ? '#FEB24C' :
                                '#FFEDA0';
        }

        // ฟังก์ชั่นสำหรับจัดรูปแบบตัวเลขให้อ่านง่าย
        function formatNumber(num) {
            if (num >= 1000000) {
                return (num / 1000000).toFixed(1) + 'M';
            } else if (num >= 1000) {
                return (num / 1000).toFixed(1) + 'K';
            } else {
                return num.toString();
            }
        }

        // ฟังก์ชั่นสำหรับสร้าง CircleMarker บนแผนที่
        function addAlumniCircleMarker(subdistrict) {
            if(subdistrict.latitude && subdistrict.longitude) {
                var circle = L.circleMarker([subdistrict.latitude, subdistrict.longitude], {
                    radius: 8 + (subdistrict.user_count / 5), // ขนาดวงกลมเพิ่มขึ้นตามจำนวน
                    fillColor: getColor(subdistrict.user_count),
                    color: "#000",
                    weight: 1,
                    opacity: 1,
                    fillOpacity: 0.8
                }).addTo(map);
                // ใช้ฟังก์ชั่น formatNumber ในการจัดรูปแบบตัวเลข
                circle.bindPopup("<b>" + subdistrict.subdistrict_name + "</b><br>จำนวนศิษย์เก่า: " + formatNumber(subdistrict.user_count));
            }
        }

        // เพิ่ม CircleMarker สำหรับแต่ละตำแหน่ง
        <?php foreach ($users_by_subdistrict as $subdistrict): ?>
            addAlumniCircleMarker({
                subdistrict_name: "<?= addslashes($subdistrict['subdistrict_name']) ?>",
                latitude: <?= htmlspecialchars($subdistrict['latitude']) ?>,
                longitude: <?= htmlspecialchars($subdistrict['longitude']) ?>,
                user_count: <?= htmlspecialchars($subdistrict['user_count']) ?>
            });
        <?php endforeach; ?>

        // เพิ่ม Legend บนแผนที่
        var legend = L.control({position: 'bottomright'});

        legend.onAdd = function (map) {
            var div = L.DomUtil.create('div', 'legend'),
                grades = [0, 1, 6, 11, 21, 31, 51],
                labels = [];

            div.innerHTML += '<strong>จำนวนศิษย์เก่า</strong><br>';

            for (var i = 0; i < grades.length; i++) {
                div.innerHTML +=
                    '<i style="background:' + getColor(grades[i] + 1) + '"></i> ' +
                    formatNumber(grades[i]) + (grades[i + 1] ? '&ndash;' + formatNumber(grades[i + 1] - 1) + '<br>' : '+');
            }

            return div;
        };

        legend.addTo(map);
    </script>
</body>

</html>
