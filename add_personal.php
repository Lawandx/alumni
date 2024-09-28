<?php

session_start();

// ตรวจสอบว่าผู้ใช้ได้เข้าสู่ระบบแล้วหรือยัง และมีสิทธิ์เป็น admin หรือไม่
if (!isset($_SESSION['user_id']) || $_SESSION['access_level'] !== 'admin') {
    header("Location: login.php");
    exit();
}
require 'db_connect.php'; // เรียกใช้ไฟล์เชื่อมต่อฐานข้อมูล
$notification = ""; // เก็บข้อความแจ้งเตือน
$alertType = ""; // เก็บประเภทของการแจ้งเตือน (success หรือ danger)
$showModal = false; // ใช้สำหรับควบคุมการแสดงผลของ Modal

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // ข้อมูลส่วนบุคคล
    $title = $_POST['title'];
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $citizen_id = $_POST['citizen_id'];
    $birth_date = $_POST['birth_date'];
    $phone_personal = $_POST['phone_personal'];
    $email = $_POST['email'];
    $line_id = $_POST['line_id'];
    $facebook = $_POST['facebook'];
    $special_ability = $_POST['special_ability'];
    $photo = '';

    // เปลี่ยนตำแหน่งโฟลเดอร์การอัพโหลดเป็น assets/images/profile_pictures/
    $upload_dir = './assets/images/profile_pictures/';
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }

    // การอัพโหลดไฟล์ภาพจากเครื่อง
    if (isset($_FILES['photo_file']) && $_FILES['photo_file']['error'] == UPLOAD_ERR_OK) {
        $file_name = basename($_FILES['photo_file']['name']);
        $file_type = pathinfo($file_name, PATHINFO_EXTENSION);

        // ตรวจสอบประเภทไฟล์ให้เป็น jpg หรือ png เท่านั้น
        if (in_array(strtolower($file_type), ['jpg', 'jpeg', 'png'])) {
            $target_file = $upload_dir . $file_name;
            if (move_uploaded_file($_FILES['photo_file']['tmp_name'], $target_file)) {
                $photo = $target_file; // เก็บ path ของไฟล์ที่อัพโหลด
            } else {
                $notification = "เกิดข้อผิดพลาดในการอัพโหลดไฟล์";
                $alertType = "danger";
                $showModal = true;
            }
        } else {
            $notification = "ประเภทไฟล์ไม่ถูกต้อง อนุญาตเฉพาะไฟล์ JPG และ PNG เท่านั้น";
            $alertType = "danger";
            $showModal = true;
        }
    }

    // การเก็บ URL ของภาพ
    if (isset($_POST['photo_url']) && filter_var($_POST['photo_url'], FILTER_VALIDATE_URL)) {
        $photo = $_POST['photo_url']; // เก็บ URL ของภาพ
    }

    // ตรวจสอบว่ามีการระบุภาพหรือไม่
    if (empty($photo)) {
        $notification = "กรุณาระบุรูปภาพผ่าน URL หรืออัพโหลดไฟล์";
        $alertType = "danger";
        $showModal = true;
    } else {
        // เริ่มการทำธุรกรรมเพื่อความปลอดภัยของการบันทึกข้อมูล
        $pdo->beginTransaction();

        try {
            // เตรียมคำสั่ง SQL สำหรับการเพิ่มข้อมูลส่วนบุคคลลงในฐานข้อมูล
            $stmt = $pdo->prepare("INSERT INTO PersonalInfo (title, first_name, last_name, citizen_id, birth_date, phone_personal, email, line_id, facebook, photo, special_ability) 
                                   VALUES (:title, :first_name, :last_name, :citizen_id, :birth_date, :phone_personal, :email, :line_id, :facebook, :photo, :special_ability)");
            $stmt->bindParam(':title', $title);
            $stmt->bindParam(':first_name', $first_name);
            $stmt->bindParam(':last_name', $last_name);
            $stmt->bindParam(':citizen_id', $citizen_id);
            $stmt->bindParam(':birth_date', $birth_date);
            $stmt->bindParam(':phone_personal', $phone_personal);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':line_id', $line_id);
            $stmt->bindParam(':facebook', $facebook);
            $stmt->bindParam(':photo', $photo);
            $stmt->bindParam(':special_ability', $special_ability);

            $stmt->execute();

            // ดึง ID ของผู้ใช้ที่เพิ่งบันทึก
            $person_id = $pdo->lastInsertId();

            // ข้อมูลที่อยู่
            $address_type = $_POST['address_type'];
            $house_number = $_POST['house_number'];
            $village = $_POST['village'];
            $sub_district = $_POST['subdistrict']; // ชื่อฟิลด์ในฐานข้อมูลอาจเป็น sub_district
            $district = $_POST['district'];
            $province = $_POST['province'];
            $zip_code = $_POST['zip_code'];
            $phone_address = $_POST['phone_address'];
            $is_same_as_permanent = $_POST['is_same_as_permanent'];

            // เตรียมคำสั่ง SQL สำหรับการเพิ่มข้อมูลที่อยู่ลงในฐานข้อมูล
            $stmt = $pdo->prepare("INSERT INTO Address (person_id, address_type, house_number, village, sub_district, district, province, zip_code, phone_address, is_same_as_permanent) 
                                   VALUES (:person_id, :address_type, :house_number, :village, :sub_district, :district, :province, :zip_code, :phone_address, :is_same_as_permanent)");
            $stmt->bindParam(':person_id', $person_id);
            $stmt->bindParam(':address_type', $address_type);
            $stmt->bindParam(':house_number', $house_number);
            $stmt->bindParam(':village', $village);
            $stmt->bindParam(':sub_district', $sub_district); // ตรวจสอบให้แน่ใจว่าชื่อฟิลด์ในฐานข้อมูลคือ sub_district
            $stmt->bindParam(':district', $district);
            $stmt->bindParam(':province', $province);
            $stmt->bindParam(':zip_code', $zip_code);
            $stmt->bindParam(':phone_address', $phone_address);
            $stmt->bindParam(':is_same_as_permanent', $is_same_as_permanent);

            $stmt->execute();

            // ยืนยันการทำธุรกรรม
            $pdo->commit();

            $notification = "เพิ่มข้อมูลสำเร็จ";
            $alertType = "success";
            $showModal = true;
        } catch (Exception $e) {
            // ยกเลิกการทำธุรกรรมหากมีข้อผิดพลาด
            $pdo->rollBack();
            $notification = "เกิดข้อผิดพลาดในการบันทึกข้อมูล: " . $e->getMessage();
            $alertType = "danger";
            $showModal = true;
        }
    }
}
?>



<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>เพิ่มข้อมูลส่วนบุคคล</title>
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@100;400&display=swap" rel="stylesheet">
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
     <!-- Font Awesome for Icons -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        h3 {
            font-family: 'Sarabun', sans-serif;
            font-weight: 600;
            /* หนา */
            font-size: 1.75rem;
            /* ขนาดฟอนต์ */
            color: #fd7e14;
            /* สีฟอนต์ */
            margin-bottom: 20px;
            /* ระยะห่างด้านล่าง */
        }

        .sarabun-thin {
            font-family: "Sarabun", sans-serif;
            font-weight: 100;
            font-style: normal;
        }

       
        body {
            font-family: 'Sarabun', sans-serif;
        }

        .modal-content {
            border-radius: 10px;
            text-align: center;
        }

        .modal-body img {
            width: 50px;
            height: 50px;
        }

        .btn-primary-custom {
            background-color: #fd7e14;
            border-color: #fd7e14;
        }

        .btn-primary-custom:hover {
            background-color: #e06b0e;
            border-color: #e06b0e;
        }

        .form-section {
            display: none;
        }

        .form-section.active {
            display: block;
        }

        /* Stepper Styles */
        .step {
            display: flex;
            flex-direction: column;
            align-items: center;
            width: 50%;
            position: relative;
        }

        .step:not(:last-child)::after {
            content: '';
            position: absolute;
            top: 15px;
            right: -50%;
            width: 100%;
            height: 2px;
            background-color: #ccc;
            z-index: -1;
        }

        .step.active .circle {
            background-color: #fd7e14;
            color: white;
        }

        .circle {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            background-color: #ccc;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 5px;
        }

        .label {
            font-size: 0.9rem;
            text-align: center;
        }
    </style>
</head>

<body>
<?php include 'navbar.php'; ?>
    <div class="container mt-5">
        <!-- Stepper -->
        <div class="mb-4">
            <div class="d-flex justify-content-between">
                <div class="step active" id="step1">
                    <div class="circle">1</div>
                    <div class="label">ข้อมูลส่วนบุคคล</div>
                </div>
                <div class="step" id="step2">
                    <div class="circle">2</div>
                    <div class="label">ข้อมูลที่อยู่</div>
                </div>
            </div>
        </div>



        <!-- ฟอร์มข้อมูลส่วนบุคคล -->
        <form id="personalForm" method="POST" action="add_personal.php" enctype="multipart/form-data">
            <div id="section1" class="form-section active">
                <h3>Step 1: เพิ่มข้อมูลส่วนบุคคล</h3>
                <div class="row">
                    <div class="col-md-3">
                        <div class="mb-3">
                            <label for="title" class="form-label">คำนำหน้า</label>
                            <input type="text" class="form-control" id="title" name="title" required>
                        </div>
                    </div>
                    <div class="col-md-5">
                        <div class="mb-3">
                            <label for="first_name" class="form-label">ชื่อ</label>
                            <input type="text" class="form-control" id="first_name" name="first_name" required>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="last_name" class="form-label">นามสกุล</label>
                            <input type="text" class="form-control" id="last_name" name="last_name" required>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="citizen_id" class="form-label">เลขประจำตัวประชาชน</label>
                            <input type="text" class="form-control" id="citizen_id" name="citizen_id" required maxlength="13" pattern="\d{13}" title="กรุณากรอกเลข 13 หลัก">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="birth_date" class="form-label">วันเกิด</label>
                            <input type="date" class="form-control" id="birth_date" name="birth_date" required>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="phone_personal" class="form-label">เบอร์โทรเคลื่อนที่</label>
                            <input type="text" class="form-control" id="phone_personal" name="phone_personal" required>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="email" class="form-label">อีเมล</label>
                            <input type="email" class="form-control" id="email" name="email" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="line_id" class="form-label">Line ID</label>
                            <input type="text" class="form-control" id="line_id" name="line_id">
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="facebook" class="form-label">Facebook</label>
                            <input type="text" class="form-control" id="facebook" name="facebook">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="photo_url" class="form-label">URL ของรูปภาพ</label>
                            <input type="url" class="form-control" id="photo_url" name="photo_url">
                        </div>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="special_ability" class="form-label">ความสามารถพิเศษ</label>
                    <textarea class="form-control" id="special_ability" name="special_ability"></textarea>
                </div>

                <div class="mb-3">
                    <label for="photo_file" class="form-label">หรืออัพโหลดรูปภาพ (เฉพาะ JPG และ PNG)</label>
                    <input type="file" class="form-control" id="photo_file" name="photo_file" accept=".jpg, .jpeg, .png">
                </div>

                <button type="button" class="btn btn-primary" onclick="nextSection()">ถัดไป</button>
            </div>

            <div id="section2" class="form-section">
                <h3>Step 2: เพิ่มข้อมูลที่อยู่</h3>

                <div class="row">
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="address_type" class="form-label">ประเภทที่อยู่</label>
                            <select class="form-control" id="address_type" name="address_type" required>
                                <option value="permanent">ที่อยู่บ้าน</option>
                                <option value="contact">ที่อยู่ติดต่อ</option>
                                <option value="work">ที่ทำงาน</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="house_number" class="form-label">บ้านเลขที่</label>
                            <input type="text" class="form-control" id="house_number" name="house_number" required>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="village" class="form-label">หมู่บ้าน</label>
                            <input type="text" class="form-control" id="village" name="village">
                        </div>
                    </div>
                </div>



                <div class="row">
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="province" class="form-label">จังหวัด</label>
                            <select class="form-control" id="province" name="province" required>
                                <option value="">เลือกจังหวัด</option>
                                <!-- ตัวเลือกจังหวัดจะถูกเพิ่มโดย JavaScript -->
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="district" class="form-label">อำเภอ</label>
                            <select class="form-control" id="district" name="district" required>
                                <option value="">เลือกอำเภอ</option>
                                <!-- ตัวเลือกอำเภอจะถูกเพิ่มโดย JavaScript -->
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="subdistrict" class="form-label">ตำบล</label>
                            <select class="form-control" id="subdistrict" name="subdistrict" required>
                                <option value="">เลือกตำบล</option>
                                <!-- ตัวเลือกตำบลจะถูกเพิ่มโดย JavaScript -->
                            </select>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="zip_code" class="form-label">รหัสไปรษณีย์</label>
                            <input type="text" class="form-control" id="zip_code" name="zip_code" readonly>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="phone_address" class="form-label">เบอร์โทรศัพท์</label>
                            <input type="text" class="form-control" id="phone_address" name="phone_address" required>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="is_same_as_permanent" class="form-label">ที่อยู่นี้เหมือนกับที่อยู่ถาวรหรือไม่</label>
                            <select class="form-control" id="is_same_as_permanent" name="is_same_as_permanent">
                                <option value="0">ไม่ใช่</option>
                                <option value="1">ใช่</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-between mt-3">
                    <button type="button" class="btn btn-secondary" onclick="prevSection()">ย้อนกลับ</button>
                    <button type="submit" class="btn btn-primary">เพิ่มข้อมูล</button>
                </div>
            </div>

        </form>

    </div>
    <!-- Modal สำหรับการแจ้งเตือน -->
    <div class="modal fade" id="notificationModal" tabindex="-1" aria-labelledby="notificationModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-body">
                    <?php if ($alertType === 'success'): ?>
                        <i class="bi bi-check-circle-fill text-success" style="font-size: 3rem;"></i>
                    <?php else: ?>
                        <i class="bi bi-exclamation-circle-fill text-danger" style="font-size: 3rem;"></i>
                    <?php endif; ?>
                    <h5 class="mt-3"><?= $notification ?></h5>
                    <p>กรุณากด ตกลง เพื่อดำเนินการต่อ</p>
                    <button type="button" class="btn btn-primary-custom" data-bs-dismiss="modal" onclick="window.location.href='add_personal.php';">ตกลง</button>
                </div>
            </div>
        </div>
    </div>


    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <?php if ($showModal): ?>
        <script>
            var notificationModal = new bootstrap.Modal(document.getElementById('notificationModal'));
            notificationModal.show();
        </script>
    <?php endif; ?>
    <script>
        function nextSection() {
            document.getElementById('section1').classList.remove('active');
            document.getElementById('section2').classList.add('active');

            // อัปเดต Stepper
            document.getElementById('step1').classList.remove('active');
            document.getElementById('step2').classList.add('active');

        }

        function prevSection() {
            document.getElementById('section2').classList.remove('active');
            document.getElementById('section1').classList.add('active');

            // อัปเดต Stepper
            document.getElementById('step2').classList.remove('active');
            document.getElementById('step1').classList.add('active');

        }
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const provinceSelect = document.getElementById('province');
            const districtSelect = document.getElementById('district');
            const subdistrictSelect = document.getElementById('subdistrict');
            const zipCodeInput = document.getElementById('zip_code');

            // ฟังก์ชันในการดึงข้อมูลจังหวัด
            fetch('get_provinces.php')
                .then(response => response.json())
                .then(data => {
                    data.forEach(province => {
                        let option = document.createElement('option');
                        option.value = province.id;
                        option.textContent = province.name_in_thai;
                        provinceSelect.appendChild(option);
                    });
                });

            // เมื่อเลือกจังหวัดให้แสดงอำเภอที่เกี่ยวข้อง
            provinceSelect.addEventListener('change', function() {
                districtSelect.innerHTML = '<option value="">เลือกอำเภอ</option>'; // ล้างตัวเลือกอำเภอ
                subdistrictSelect.innerHTML = '<option value="">เลือกตำบล</option>'; // ล้างตัวเลือกตำบล
                zipCodeInput.value = ''; // ล้างรหัสไปรษณีย์

                fetch('get_districts.php?province_id=' + provinceSelect.value)
                    .then(response => response.json())
                    .then(data => {
                        data.forEach(district => {
                            let option = document.createElement('option');
                            option.value = district.id;
                            option.textContent = district.name_in_thai;
                            districtSelect.appendChild(option);
                        });
                    });
            });

            // เมื่อเลือกอำเภอให้แสดงตำบลที่เกี่ยวข้อง
            districtSelect.addEventListener('change', function() {
                subdistrictSelect.innerHTML = '<option value="">เลือกตำบล</option>'; // ล้างตัวเลือกตำบล
                zipCodeInput.value = ''; // ล้างรหัสไปรษณีย์

                fetch('get_subdistricts.php?district_id=' + districtSelect.value)
                    .then(response => response.json())
                    .then(data => {
                        data.forEach(subdistrict => {
                            let option = document.createElement('option');
                            option.value = subdistrict.id;
                            option.textContent = subdistrict.name_in_thai;
                            subdistrictSelect.appendChild(option);
                        });
                    });
            });

            // เมื่อเลือกตำบลให้แสดงรหัสไปรษณีย์ที่เกี่ยวข้อง
            subdistrictSelect.addEventListener('change', function() {
                fetch('get_zipcode.php?subdistrict_id=' + subdistrictSelect.value)
                    .then(response => response.json())
                    .then(data => {
                        zipCodeInput.value = data.zip_code;
                    });
            });
        });
    </script>

</body>

</html>