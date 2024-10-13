<?php
// faculty_dashboard.php
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
$faculty_id = $_SESSION['faculty_id'] ?? null;

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

    // ดึงจำนวนศิษย์เก่าในคณะนี้
    $stmt = $pdo->prepare("
        SELECT COUNT(DISTINCT p.person_id) AS alumni_count
        FROM personalinfo p
        JOIN education e ON p.person_id = e.person_id
        JOIN major m ON e.major_name = m.major_name
        JOIN department d ON m.department_id = d.department_id
        WHERE d.faculty_id = :faculty_id
    ");
    $stmt->execute([':faculty_id' => $faculty_id]);
    $alumni_count = $stmt->fetch(PDO::FETCH_ASSOC)['alumni_count'];

    // ดึงข้อมูลรางวัลที่ได้รับจากศิษย์เก่าในคณะนี้ 
    $stmt = $pdo->prepare("
        SELECT COUNT(ah.award_id) AS total_awards
        FROM awardhistory ah
        JOIN personalinfo p ON ah.person_id = p.person_id
        JOIN education e ON p.person_id = e.person_id
        JOIN major m ON e.major_name = m.major_name
        JOIN department d ON m.department_id = d.department_id
        WHERE d.faculty_id = :faculty_id
    ");
    $stmt->execute([':faculty_id' => $faculty_id]);
    $awards = $stmt->fetch(PDO::FETCH_ASSOC)['total_awards'];

    // ดึงข้อมูลจำนวนศิษย์เก่าในแต่ละภาควิชา
    $stmt = $pdo->prepare("
        SELECT d.department_name, COUNT(DISTINCT p.person_id) AS alumni_count
        FROM department d
        LEFT JOIN major m ON d.department_id = m.department_id
        LEFT JOIN education e ON m.major_name = e.major_name
        LEFT JOIN personalinfo p ON e.person_id = p.person_id
        WHERE d.faculty_id = :faculty_id
        GROUP BY d.department_name
        ORDER BY d.department_name
    ");
    $stmt->execute([':faculty_id' => $faculty_id]);
    $department_alumni = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // ดึงข้อมูลจำนวนศิษย์เก่าในแต่ละสาขาวิชา
    $stmt = $pdo->prepare("
        SELECT m.major_name, COUNT(DISTINCT p.person_id) AS alumni_count
        FROM major m
        LEFT JOIN education e ON m.major_name = e.major_name
        LEFT JOIN personalinfo p ON e.person_id = p.person_id
        JOIN department d ON m.department_id = d.department_id
        WHERE d.faculty_id = :faculty_id
        GROUP BY m.major_name
        ORDER BY m.major_name
    ");
    $stmt->execute([':faculty_id' => $faculty_id]);
    $major_alumni = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // ดึงข้อมูลจำนวนศิษย์เก่าตามตำแหน่ง (sub_district) พร้อมพิกัดเฉพาะคณะนี้
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
        JOIN major m ON e.major_name = m.major_name
        JOIN department d ON m.department_id = d.department_id
        WHERE d.faculty_id = :faculty_id
        GROUP BY sd.id, sd.name_in_thai, sd.latitude, sd.longitude
        HAVING sd.latitude IS NOT NULL AND sd.longitude IS NOT NULL
        ORDER BY user_count DESC
    ");
    $stmt_users_by_subdistrict->execute([':faculty_id' => $faculty_id]);
    $users_by_subdistrict = $stmt_users_by_subdistrict->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    // บันทึกข้อผิดพลาดลงไฟล์ log แทนการแสดงบนหน้าเว็บ
    error_log("Error: " . $e->getMessage());
    echo "เกิดข้อผิดพลาดในการดึงข้อมูล";
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
    <?php include 'navbar-faculty.php'; ?>

    <div class="container">
        <div class="faculty-header text-center">
            <h2>คณะ<?= htmlspecialchars($faculty['faculty_name']) ?></h2>
            <p><?= nl2br(htmlspecialchars($faculty['faculty_description'])) ?></p>
        </div>

        <div class="row summary-cards">
            <div class="col-md-3 col-sm-6 mb-4">
                <div class="summary-card">
                    <h3><?= $department_count ?></h3>
                    <p>ภาควิชา</p>
                </div>
            </div>
            <div class="col-md-3 col-sm-6 mb-4">
                <div class="summary-card">
                    <h3><?= $major_count ?></h3>
                    <p>สาขาวิชา</p>
                </div>
            </div>
            <div class="col-md-3 col-sm-6 mb-4">
                <div class="summary-card">
                    <h3><?= $alumni_count ?></h3>
                    <p>ศิษย์เก่า</p>
                </div>
            </div>
            <div class="col-md-3 col-sm-6 mb-4">
                <div class="summary-card">
                    <h3><?= $awards ?></h3>
                    <p>รางวัล</p>
                </div>
            </div>
        </div>

        <!-- เพิ่มส่วนแผนที่แสดงตำแหน่งของศิษย์เก่าในคณะ -->
        <div class="alumni-map-container">
            <h4 class="text-center mb-4">แผนที่แสดงตำแหน่งของศิษย์เก่าในคณะ</h4>
            <div id="alumniMap"></div>
        </div>

        <!-- ตารางแสดงจำนวนศิษย์เก่าในแต่ละภาควิชา -->
        <div class="alumni-table-container">
            <h4 class="text-center mb-4">จำนวนศิษย์เก่าในแต่ละภาควิชา</h4>
            <div class="alumni-table">
                <table class="table table-striped table-bordered" id="departmentAlumniTable">
                    <thead class="table-dark">
                        <tr>
                            <th>ภาควิชา</th>
                            <th>จำนวนศิษย์เก่า</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($department_alumni as $dept): ?>
                            <tr>
                                <td>ภาควิชา<?= htmlspecialchars($dept['department_name']) ?></td>
                                <td>
                                    <?php
                                    if ($dept['alumni_count'] > 0) {
                                        echo htmlspecialchars($dept['alumni_count']);
                                    } else {
                                        echo "ไม่มีศิษย์ในภาควิชานี้";
                                    }
                                    ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- ตารางแสดงจำนวนศิษย์เก่าในแต่ละสาขาวิชา -->
        <div class="alumni-table-container">
            <h4 class="text-center mb-4">จำนวนศิษย์เก่าในแต่ละสาขาวิชา</h4>
            <div class="alumni-table">
                <table class="table table-striped table-bordered" id="majorAlumniTable">
                    <thead class="table-dark">
                        <tr>
                            <th>สาขาวิชา</th>
                            <th>จำนวนศิษย์เก่า</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($major_alumni as $major): ?>
                            <tr>
                                <td>หลักสูตร<?= htmlspecialchars($major['major_name']) ?></td>
                                <td>
                                    <?php
                                    if ($major['alumni_count'] > 0) {
                                        echo htmlspecialchars($major['alumni_count']);
                                    } else {
                                        echo "ไม่มีศิษย์ในสาขาวิชานี้";
                                    }
                                    ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>


    </div>

    <!-- Bootstrap JS และ dependencies -->
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
            // DataTable สำหรับตารางจำนวนศิษย์เก่าในภาควิชา
            $('#departmentAlumniTable').DataTable({
                "language": {
                    "url": "https://cdn.datatables.net/plug-ins/1.13.5/i18n/th.json"
                },
                "paging": true,
                "searching": true,
                "ordering": true,
                "order": [
                    [0, 'asc']
                ],
                "lengthMenu": [10, 25, 50, 100],
                "pageLength": 10
            });

            // DataTable สำหรับตารางจำนวนศิษย์เก่าในสาขาวิชา
            $('#majorAlumniTable').DataTable({
                "language": {
                    "url": "https://cdn.datatables.net/plug-ins/1.13.5/i18n/th.json"
                },
                "paging": true,
                "searching": true,
                "ordering": true,
                "order": [
                    [0, 'asc']
                ],
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

        // ฟังก์ชั่นสำหรับเลือกสีตามจำนวนศิษย์เก่า (แก้ไขช่วงค่าใหม่)
        function getColor(count) {
            return count > 100 ? '#800026' :
                count > 50 ? '#BD0026' :
                count > 20 ? '#E31A1C' :
                count > 10 ? '#FC4E2A' :
                count > 5 ? '#FD8D3C' :
                count > 0 ? '#FEB24C' :
                '#FFEDA0';
        }

        // ฟังก์ชั่นสำหรับสร้าง CircleMarker บนแผนที่
        function addAlumniCircleMarker(subdistrict) {
            if (subdistrict.latitude && subdistrict.longitude) {
                var circle = L.circleMarker([subdistrict.latitude, subdistrict.longitude], {
                    radius: 8 + (subdistrict.user_count / 5), // ขนาดวงกลมเพิ่มขึ้นตามจำนวน
                    fillColor: getColor(subdistrict.user_count),
                    color: "#000",
                    weight: 1,
                    opacity: 1,
                    fillOpacity: 0.8
                }).addTo(map);
                circle.bindPopup("<b>" + subdistrict.subdistrict_name + "</b><br>จำนวนศิษย์เก่า: " + subdistrict.user_count);
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

        // เพิ่ม Legend บนแผนที่ (แก้ไขช่วงค่าใหม่)
        var legend = L.control({
            position: 'bottomright'
        });

        legend.onAdd = function(map) {
            var div = L.DomUtil.create('div', 'legend'),
                grades = [0, 5, 10, 20, 50, 100],
                labels = [];

            div.innerHTML += '<strong>จำนวนศิษย์เก่า</strong><br>';

            for (var i = 0; i < grades.length; i++) {
                div.innerHTML +=
                    '<i style="background:' + getColor(grades[i] + 1) + '"></i> ' +
                    grades[i] + (grades[i + 1] ? '&ndash;' + grades[i + 1] + '<br>' : '+');
            }

            return div;
        };

        legend.addTo(map);
    </script>
</body>

</html>