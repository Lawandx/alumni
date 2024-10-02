<?php
session_start();

// ตรวจสอบว่าผู้ใช้ได้เข้าสู่ระบบแล้วหรือยัง และมีสิทธิ์เป็น faculty หรือไม่
if (!isset($_SESSION['user_id']) || $_SESSION['access_level'] !== 'faculty') {
    header("Location: ../login.php");
    exit();
}
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
require '../db_connect.php'; // เรียกใช้ไฟล์เชื่อมต่อฐานข้อมูล

// ตรวจสอบว่า $_SESSION['faculty_name'] ถูกตั้งค่าไว้หรือไม่
if (!isset($_SESSION['faculty_name'])) {
    if (isset($_SESSION['faculty_id'])) {
        // ดึง faculty_name จากฐานข้อมูลโดยใช้ faculty_id
        $stmt = $pdo->prepare("SELECT faculty_name FROM faculty WHERE faculty_id = :faculty_id");
        $stmt->execute([':faculty_id' => $_SESSION['faculty_id']]);
        $faculty = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($faculty) {
            $_SESSION['faculty_name'] = $faculty['faculty_name'];
        } else {
            // จัดการกรณีที่ไม่พบข้อมูลคณะ
            echo "เกิดข้อผิดพลาด: ไม่พบข้อมูลคณะของผู้ใช้ในฐานข้อมูล";
            exit();
        }
    } else {
        // จัดการกรณีที่ไม่มี faculty_id ใน SESSION
        echo "เกิดข้อผิดพลาด: ไม่มีข้อมูลคณะของผู้ใช้ใน SESSION";
        exit();
    }
}

// ฟังก์ชันสำหรับการเน้นคำที่ถูกค้นหา
function highlightSearchTerm($text, $term)
{
    if (!empty($term)) {
        return preg_replace('/(' . preg_quote($term, '/') . ')/i', '<mark>$1</mark>', $text);
    }
    return $text;
}

// รับค่าจากฟอร์มค้นหา
$first_name = isset($_GET['first_name']) ? trim($_GET['first_name']) : '';
$last_name = isset($_GET['last_name']) ? trim($_GET['last_name']) : '';
$student_id = isset($_GET['student_id']) ? trim($_GET['student_id']) : '';
$major = isset($_GET['major']) ? trim($_GET['major']) : '';
$award = isset($_GET['award']) ? trim($_GET['award']) : '';
$country = isset($_GET['country']) ? trim($_GET['country']) : '';
$province = isset($_GET['province']) ? intval($_GET['province']) : '';
$district = isset($_GET['district']) ? intval($_GET['district']) : '';
$subdistrict = isset($_GET['subdistrict']) ? intval($_GET['subdistrict']) : '';

// เตรียมพารามิเตอร์สำหรับคำสั่ง SQL
$params = [];

// เตรียมคำสั่ง SQL สำหรับการดึงข้อมูลตามเงื่อนไขที่เลือก
$query = "
SELECT 
    p.person_id, 
    p.first_name, 
    p.last_name, 
    prov.name_in_thai AS province_name, 
    dist.name_in_thai AS district_name, 
    subd.name_in_thai AS subdistrict_name,
    edu.country AS edu_country, 
    we.country AS we_country,
    edu.student_id, 
    edu.faculty_name, 
    edu.major_name, 
    GROUP_CONCAT(DISTINCT sa.student_award_name SEPARATOR ', ') AS student_awards,
    GROUP_CONCAT(DISTINCT ah.award_name SEPARATOR ', ') AS award_histories
FROM PersonalInfo p 
JOIN Address a ON p.person_id = a.person_id 
JOIN provinces prov ON a.province = prov.id 
JOIN districts dist ON a.district = dist.id 
JOIN subdistricts subd ON a.sub_district = subd.id 
LEFT JOIN education edu ON p.person_id = edu.person_id 
LEFT JOIN studentaward sa ON p.person_id = sa.person_id 
LEFT JOIN workexperience we ON p.person_id = we.person_id 
LEFT JOIN awardhistory ah ON p.person_id = ah.person_id 
WHERE 1=1";

// จำกัดผลลัพธ์ให้เฉพาะคณะของผู้ใช้
$query .= " AND edu.faculty_name = :session_faculty_name";
$params[':session_faculty_name'] = $_SESSION['faculty_name'];

// เพิ่มเงื่อนไขการค้นหาตามที่ผู้ใช้กรอก
if (!empty($first_name)) {
    $query .= " AND p.first_name LIKE :first_name";
    $params[':first_name'] = '%' . $first_name . '%';
}

if (!empty($last_name)) {
    $query .= " AND p.last_name LIKE :last_name";
    $params[':last_name'] = '%' . $last_name . '%';
}

if (!empty($student_id)) {
    $query .= " AND edu.student_id LIKE :student_id";
    $params[':student_id'] = '%' . $student_id . '%';
}

if (!empty($major)) {
    $query .= " AND edu.major_name LIKE :major";
    $params[':major'] = '%' . $major . '%';
}

if (!empty($award)) {
    // ค้นหาจากทั้งตาราง studentaward และ awardhistory ด้วยชื่อพารามิเตอร์ที่ไม่ซ้ำกัน
    $query .= " AND (sa.student_award_name LIKE :award_sa OR ah.award_name LIKE :award_ah)";
    $params[':award_sa'] = '%' . $award . '%';
    $params[':award_ah'] = '%' . $award . '%';
}

if (!empty($country)) {
    // ค้นหาจากทั้งตาราง education และ workexperience ด้วยชื่อพารามิเตอร์ที่ไม่ซ้ำกัน
    $query .= " AND (edu.country LIKE :country_edu OR we.country LIKE :country_we)";
    $params[':country_edu'] = '%' . $country . '%';
    $params[':country_we'] = '%' . $country . '%';
}

if (!empty($province)) {
    $query .= " AND prov.id = :province";
    $params[':province'] = $province;
}

if (!empty($district)) {
    $query .= " AND dist.id = :district";
    $params[':district'] = $district;
}

if (!empty($subdistrict)) {
    $query .= " AND subd.id = :subdistrict";
    $params[':subdistrict'] = $subdistrict;
}

// เพิ่มการจัดกลุ่มและจัดเรียงผลลัพธ์ตามชื่อ นามสกุล
$query .= " 
    GROUP BY p.person_id, p.first_name, p.last_name, 
             prov.name_in_thai, dist.name_in_thai, subd.name_in_thai, 
             edu.country, we.country, edu.student_id, 
             edu.faculty_name, edu.major_name
    ORDER BY p.last_name ASC, p.first_name ASC";

// เตรียมและดำเนินการคำสั่ง SQL
$stmt = $pdo->prepare($query);

// ผูกพารามิเตอร์และดำเนินการ
try {
    $stmt->execute($params);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "เกิดข้อผิดพลาดในการค้นหา: " . htmlspecialchars($e->getMessage());
    exit();
}
?>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ค้นหาข้อมูล</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Google Fonts (Sarabun) -->
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@400;500;600&display=swap" rel="stylesheet">
    <!-- Font Awesome for Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        mark {
            background-color: yellow;
            color: inherit;
        }

        body {
            font-family: "Sarabun", sans-serif;
        }
    </style>
</head>

<body>
    <?php include 'navbar-faculty.php'; ?>

    <div class="container mt-5">
        <h3 class="mb-4">ค้นหาข้อมูลบุคคล</h3>

        <!-- แสดงข้อความสำเร็จ -->
        <?php if (isset($_GET['message'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?= htmlspecialchars($_GET['message']) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="ปิด"></button>
            </div>
        <?php endif; ?>

        <!-- ฟอร์มสำหรับค้นหา -->
        <form id="searchForm" method="GET" action="search_results.php" class="mb-5">
            <div class="row g-3">
                <!-- แถวที่ 1: ชื่อ, นามสกุล, รหัสนิสิต -->
                <div class="col-md-4">
                    <label for="first_name" class="form-label">ชื่อ</label>
                    <input type="text" class="form-control" id="first_name" name="first_name" value="<?= htmlspecialchars($first_name) ?>" placeholder="กรอกชื่อ">
                </div>
                <div class="col-md-4">
                    <label for="last_name" class="form-label">นามสกุล</label>
                    <input type="text" class="form-control" id="last_name" name="last_name" value="<?= htmlspecialchars($last_name) ?>" placeholder="กรอกนามสกุล">
                </div>
                <div class="col-md-4">
                    <label for="student_id" class="form-label">รหัสนิสิต</label>
                    <input type="text" class="form-control" id="student_id" name="student_id" value="<?= htmlspecialchars($student_id) ?>" placeholder="กรอกรหัสนิสิต">
                </div>

                <!-- แถวที่ 2: สาขา -->
                <div class="col-md-6">
                    <label for="major" class="form-label">สาขา</label>
                    <input type="text" class="form-control" id="major" name="major" value="<?= htmlspecialchars($major) ?>" placeholder="กรอกสาขา">
                </div>

                <!-- แถวที่ 3: รางวัล, ประเทศ -->
                <div class="col-md-6">
                    <label for="award" class="form-label">รางวัล</label>
                    <input type="text" class="form-control" id="award" name="award" value="<?= htmlspecialchars($award) ?>" placeholder="กรอกชื่อรางวัล">
                </div>
                <div class="col-md-6">
                    <label for="country" class="form-label">ประเทศ</label>
                    <input type="text" class="form-control" id="country" name="country" value="<?= htmlspecialchars($country) ?>" placeholder="กรอกประเทศ">
                </div>

                <!-- แถวที่ 4: จังหวัด, อำเภอ, ตำบล -->
                <div class="col-md-4">
                    <label for="province" class="form-label">จังหวัด</label>
                    <select class="form-select" id="province" name="province">
                        <option value="">เลือกจังหวัด</option>
                        <!-- ตัวเลือกจังหวัดจะถูกเพิ่มโดย JavaScript -->
                    </select>
                </div>
                <div class="col-md-4">
                    <label for="district" class="form-label">อำเภอ</label>
                    <select class="form-select" id="district" name="district">
                        <option value="">เลือกอำเภอ</option>
                        <!-- ตัวเลือกอำเภอจะถูกเพิ่มโดย JavaScript -->
                    </select>
                </div>
                <div class="col-md-4">
                    <label for="subdistrict" class="form-label">ตำบล</label>
                    <select class="form-select" id="subdistrict" name="subdistrict">
                        <option value="">เลือกตำบล</option>
                        <!-- ตัวเลือกตำบลจะถูกเพิ่มโดย JavaScript -->
                    </select>
                </div>
            </div>
            <div class="mt-3">
                <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i> ค้นหา</button>
                <a href="search_results.php" class="btn btn-secondary"><i class="fas fa-redo"></i> รีเซ็ต</a>
            </div>
        </form>

        <!-- ตารางแสดงผลลัพธ์การค้นหา -->
        <div>
            <table class="table table-striped table-bordered table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>ชื่อ</th>
                        <th>นามสกุล</th>
                        <th>รหัสนิสิต</th>
                        <th>คณะ</th>
                        <th>สาขา</th>
                        <th>รางวัล (การศึกษา)</th>
                        <th>รางวัล (ประสบการณ์ทำงาน)</th>
                        <th>จังหวัด</th>
                        <th>อำเภอ</th>
                        <th>ตำบล</th>
                        <th>ประเทศ (การศึกษา)</th>
                        <th>ประเทศ (ประสบการณ์ทำงาน)</th>
                        <th>การกระทำ</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($results)): ?>
                        <?php foreach ($results as $row): ?>
                            <tr>
                                <td><?= highlightSearchTerm(htmlspecialchars($row['first_name']), $first_name) ?></td>
                                <td><?= highlightSearchTerm(htmlspecialchars($row['last_name']), $last_name) ?></td>
                                <td><?= highlightSearchTerm(htmlspecialchars($row['student_id']), $student_id) ?></td>
                                <td><?= htmlspecialchars($row['faculty_name']) ?></td>
                                <td><?= highlightSearchTerm(htmlspecialchars($row['major_name']), $major) ?></td>
                                <td><?= highlightSearchTerm(htmlspecialchars($row['student_awards']), $award) ?></td>
                                <td><?= highlightSearchTerm(htmlspecialchars($row['award_histories']), $award) ?></td>
                                <td><?= htmlspecialchars($row['province_name']) ?></td>
                                <td><?= htmlspecialchars($row['district_name']) ?></td>
                                <td><?= htmlspecialchars($row['subdistrict_name']) ?></td>
                                <td><?= highlightSearchTerm(htmlspecialchars($row['edu_country']), $country) ?></td>
                                <td><?= highlightSearchTerm(htmlspecialchars($row['we_country']), $country) ?></td>
                                <td class="text-center action-column">
                                    <a href="../details.php?id=<?= $row['person_id'] ?>" class="btn btn-info btn-sm"><i class="fas fa-eye"></i> ดู</a>
                                    <form method="POST" action="../delete_person.php" onsubmit="return confirm('คุณแน่ใจหรือไม่ที่จะลบข้อมูลนี้? การกระทำนี้ไม่สามารถกู้คืนได้');">
                                        <input type="hidden" name="person_id" value="<?= htmlspecialchars($row['person_id']) ?>">
                                        <input type="hidden" name="return_url" value="<?= htmlspecialchars($_SERVER['REQUEST_URI']) ?>"> <!-- เก็บ URL ปัจจุบัน -->
                                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                                        <button type="submit" class="btn btn-danger btn-sm"><i class="fas fa-trash-alt"></i> ลบ</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="13" class="text-center">ไม่พบข้อมูล</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- JavaScript สำหรับดึงข้อมูลพื้นที่และอัพเดตฟอร์ม -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const provinceSelect = document.getElementById('province');
            const districtSelect = document.getElementById('district');
            const subdistrictSelect = document.getElementById('subdistrict');

            // ฟังก์ชันในการดึงข้อมูลจังหวัด
            fetch('get_provinces.php')
                .then(response => response.json())
                .then(data => {
                    data.forEach(province => {
                        let option = document.createElement('option');
                        option.value = province.id;
                        option.textContent = province.name_in_thai;
                        if ("<?= $province ?>" == province.id) {
                            option.selected = true;
                        }
                        provinceSelect.appendChild(option);
                    });

                    // หลังจากเพิ่มจังหวัดแล้ว ให้โหลดอำเภอถ้าเลือกจังหวัด
                    if ("<?= $province ?>" !== "") {
                        loadDistricts("<?= $province ?>");
                    }
                });

            // ฟังก์ชันในการดึงข้อมูลอำเภอ
            function loadDistricts(province_id) {
                fetch('get_districts.php?province_id=' + province_id)
                    .then(response => response.json())
                    .then(data => {
                        districtSelect.innerHTML = '<option value="">เลือกอำเภอ</option>'; // ล้างตัวเลือกอำเภอ
                        data.forEach(district => {
                            let option = document.createElement('option');
                            option.value = district.id;
                            option.textContent = district.name_in_thai;
                            if ("<?= $district ?>" == district.id) {
                                option.selected = true;
                            }
                            districtSelect.appendChild(option);
                        });

                        // หลังจากเพิ่มอำเภอแล้ว ให้โหลดตำบลถ้าเลือกอำเภอ
                        if ("<?= $district ?>" !== "") {
                            loadSubdistricts("<?= $district ?>");
                        }
                    });
            }

            // ฟังก์ชันในการดึงข้อมูลตำบล
            function loadSubdistricts(district_id) {
                fetch('get_subdistricts.php?district_id=' + district_id)
                    .then(response => response.json())
                    .then(data => {
                        subdistrictSelect.innerHTML = '<option value="">เลือกตำบล</option>'; // ล้างตัวเลือกตำบล
                        data.forEach(subdistrict => {
                            let option = document.createElement('option');
                            option.value = subdistrict.id;
                            option.textContent = subdistrict.name_in_thai;
                            if ("<?= $subdistrict ?>" == subdistrict.id) {
                                option.selected = true;
                            }
                            subdistrictSelect.appendChild(option);
                        });
                    });
            }

            // เมื่อเลือกจังหวัด ให้แสดงอำเภอที่เกี่ยวข้อง
            provinceSelect.addEventListener('change', function() {
                const selectedProvince = this.value;
                districtSelect.innerHTML = '<option value="">เลือกอำเภอ</option>'; // ล้างตัวเลือกอำเภอ
                subdistrictSelect.innerHTML = '<option value="">เลือกตำบล</option>'; // ล้างตัวเลือกตำบล

                if (selectedProvince !== "") {
                    loadDistricts(selectedProvince);
                }
            });

            // เมื่อเลือกอำเภอ ให้แสดงตำบลที่เกี่ยวข้อง
            districtSelect.addEventListener('change', function() {
                const selectedDistrict = this.value;
                subdistrictSelect.innerHTML = '<option value="">เลือกตำบล</option>'; // ล้างตัวเลือกตำบล

                if (selectedDistrict !== "") {
                    loadSubdistricts(selectedDistrict);
                }
            });

            // ฟังก์ชันในการปิดข้อความแจ้งเตือนหลังจาก 3 วินาที
            var alertElement = document.querySelector('.alert');
            if (alertElement) {
                setTimeout(function() {
                    var alert = new bootstrap.Alert(alertElement);
                    alert.close();
                }, 3000); // 3000 มิลลิวินาที = 3 วินาที
            }
        });
    </script>
</body>

</html>