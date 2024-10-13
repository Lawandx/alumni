<?php
session_start();

// ตรวจสอบว่าผู้ใช้ได้เข้าสู่ระบบแล้วหรือยัง และมีสิทธิ์เป็น admin หรือไม่
if (!isset($_SESSION['user_id']) || $_SESSION['access_level'] !== 'admin') {
    header("Location: login.php");
    exit();
}

require 'db_connect.php'; // เรียกใช้ไฟล์เชื่อมต่อฐานข้อมูล

// สร้าง CSRF Token
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$notification = ""; // เก็บข้อความแจ้งเตือน
$alertType = ""; // เก็บประเภทของการแจ้งเตือน (success หรือ danger)
$showModal = false; // ใช้สำหรับควบคุมการแสดงผลของ Modal
$errors = []; // เก็บข้อผิดพลาดในฟอร์ม

// ค่าเริ่มต้นของฟิลด์ในฟอร์ม
// Personal Info
$title = '';
$first_name = '';
$last_name = '';
$citizen_id = '';
$birth_date = '';
$phone_personal = '';
$email = '';
$line_id = '';
$facebook = '';
$special_ability = '';
$photo_url = '';

// Address
$address_type = '';
$house_number = '';
$village = '';
$sub_district = '';
$district = '';
$province = '';
$zip_code = '';
$phone_address = '';
$is_same_as_permanent = '';

// Education
$degree_level = '';
$country = 'ประเทศไทย';
$university = '';
$student_id = '';
$faculty_name = '';
$major_name = '';
$graduation_year = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // ตรวจสอบ CSRF Token
    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        $notification = "Invalid CSRF token.";
        $alertType = "danger";
        $showModal = true;
    } else {
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
        $photo_url = $_POST['photo_url'];

        // การตรวจสอบฟิลด์ที่จำเป็น
        if (empty($title)) {
            $errors['title'] = "กรุณากรอกคำนำหน้า";
        }

        if (empty($first_name)) {
            $errors['first_name'] = "กรุณากรอกชื่อ";
        }

        if (empty($last_name)) {
            $errors['last_name'] = "กรุณากรอกนามสกุล";
        }

        if (empty($citizen_id) || !preg_match('/^\d{13}$/', $citizen_id)) {
            $errors['citizen_id'] = "กรุณากรอกเลขประจำตัวประชาชน 13 หลัก";
        }

        if (empty($birth_date)) {
            $errors['birth_date'] = "กรุณากรอกวันเกิด";
        }

        if (empty($phone_personal)) {
            $errors['phone_personal'] = "กรุณากรอกเบอร์โทรเคลื่อนที่";
        }

        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = "กรุณากรอกอีเมลที่ถูกต้อง";
        }

        // ตรวจสอบการอัพโหลดรูปภาพ
        if (!empty($_FILES['photo_file']['name']) && !empty($photo_url)) {
            $errors['photo'] = "กรุณาเลือกอัพโหลดไฟล์หรือระบุ URL ของรูปภาพเพียงอย่างใดอย่างหนึ่ง";
        } elseif (!empty($_FILES['photo_file']['name'])) {
            // การอัพโหลดไฟล์ภาพจากเครื่อง
            // เปลี่ยนตำแหน่งโฟลเดอร์การอัพโหลดเป็น assets/images/profile_pictures/
            $upload_dir = './assets/images/profile_pictures/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }

            if ($_FILES['photo_file']['error'] == UPLOAD_ERR_OK) {
                // จำกัดขนาดไฟล์ (สูงสุด 2MB)
                if ($_FILES['photo_file']['size'] > 2 * 1024 * 1024) {
                    $errors['photo'] = "ขนาดไฟล์ใหญ่เกินกำหนด (2MB)";
                } else {
                    // ตรวจสอบ MIME Type
                    $finfo = finfo_open(FILEINFO_MIME_TYPE);
                    $mime_type = finfo_file($finfo, $_FILES['photo_file']['tmp_name']);
                    finfo_close($finfo);

                    $allowed_types = ['image/jpeg', 'image/png'];
                    if (in_array($mime_type, $allowed_types)) {
                        // สร้างชื่อไฟล์ใหม่
                        $new_filename = uniqid('', true) . '.' . pathinfo($_FILES['photo_file']['name'], PATHINFO_EXTENSION);
                        $target_file = $upload_dir . $new_filename;

                        if (move_uploaded_file($_FILES['photo_file']['tmp_name'], $target_file)) {
                            $photo = $target_file; // เก็บ path ของไฟล์ที่อัพโหลด
                        } else {
                            $errors['photo'] = "เกิดข้อผิดพลาดในการอัพโหลดไฟล์";
                        }
                    } else {
                        $errors['photo'] = "ประเภทไฟล์ไม่ถูกต้อง อนุญาตเฉพาะไฟล์ JPG และ PNG เท่านั้น";
                    }
                }
            } else {
                $errors['photo'] = "เกิดข้อผิดพลาดในการอัพโหลดไฟล์";
            }
        } elseif (!empty($photo_url)) {
            if (filter_var($photo_url, FILTER_VALIDATE_URL)) {
                $photo = $photo_url; // เก็บ URL ของภาพ
            } else {
                $errors['photo'] = "URL ของรูปภาพไม่ถูกต้อง";
            }
        } else {
            $errors['photo'] = "กรุณาระบุรูปภาพผ่าน URL หรืออัพโหลดไฟล์";
        }

        // ถ้าไม่มีข้อผิดพลาดในส่วนที่ 1 ให้ตรวจสอบส่วนที่ 2
        if (empty($errors)) {
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

            // การตรวจสอบฟิลด์ที่จำเป็นในส่วนที่อยู่
            if (empty($address_type)) {
                $errors['address_type'] = "กรุณาเลือกประเภทที่อยู่";
            }

            if (empty($house_number)) {
                $errors['house_number'] = "กรุณากรอกบ้านเลขที่";
            }

            if (empty($province)) {
                $errors['province'] = "กรุณาเลือกจังหวัด";
            }

            if (empty($district)) {
                $errors['district'] = "กรุณาเลือกอำเภอ";
            }

            if (empty($sub_district)) {
                $errors['subdistrict'] = "กรุณาเลือกตำบล";
            }

            if (empty($zip_code)) {
                $errors['zip_code'] = "ไม่พบรหัสไปรษณีย์";
            }

            if (empty($phone_address)) {
                $errors['phone_address'] = "กรุณากรอกเบอร์โทรศัพท์";
            }

            // ถ้าไม่มีข้อผิดพลาดในส่วนที่อยู่ ให้ตรวจสอบส่วนการศึกษา
            if (empty($errors)) {
                // ข้อมูลการศึกษา
                $degree_level = $_POST['degree_level'];
                $country = $_POST['country'];
                $university = $_POST['university'];
                $student_id = $_POST['student_id'];
                $faculty_name = $_POST['faculty_name'];
                $major_name = $_POST['major_name'];
                $graduation_year = $_POST['graduation_year'];

                // การตรวจสอบฟิลด์ที่จำเป็นในส่วนการศึกษา
                if (empty($degree_level)) {
                    $errors['degree_level'] = "กรุณาเลือกระดับการศึกษา";
                }

                if (empty($country)) {
                    $errors['country'] = "กรุณากรอกประเทศ";
                }

                if (empty($university)) {
                    $errors['university'] = "กรุณากรอกมหาวิทยาลัย";
                }

                if (empty($student_id)) {
                    $errors['student_id'] = "กรุณากรอกรหัสนักศึกษา";
                }

                if (empty($faculty_name)) {
                    $errors['faculty_name'] = "กรุณากรอกคณะ";
                }

                if (empty($major_name)) {
                    $errors['major_name'] = "กรุณากรอกสาขา";
                }

                if (empty($graduation_year) || !preg_match('/^\d{4}$/', $graduation_year)) {
                    $errors['graduation_year'] = "กรุณากรอกปีที่จบการศึกษาเป็นตัวเลข 4 หลัก";
                }

                // ถ้าไม่มีข้อผิดพลาดในทุกส่วน ให้บันทึกข้อมูลลงฐานข้อมูล
                if (empty($errors)) {
                    // เริ่มการทำธุรกรรมเพื่อความปลอดภัยของการบันทึกข้อมูล
                    $pdo->beginTransaction();

                    try {
                        // การบันทึกข้อมูลส่วนบุคคล
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

                        // การบันทึกข้อมูลที่อยู่
                        $stmt = $pdo->prepare("INSERT INTO Address (person_id, address_type, house_number, village, sub_district, district, province, zip_code, phone_address, is_same_as_permanent) 
                                               VALUES (:person_id, :address_type, :house_number, :village, :sub_district, :district, :province, :zip_code, :phone_address, :is_same_as_permanent)");
                        $stmt->bindParam(':person_id', $person_id);
                        $stmt->bindParam(':address_type', $address_type);
                        $stmt->bindParam(':house_number', $house_number);
                        $stmt->bindParam(':village', $village);
                        $stmt->bindParam(':sub_district', $sub_district);
                        $stmt->bindParam(':district', $district);
                        $stmt->bindParam(':province', $province);
                        $stmt->bindParam(':zip_code', $zip_code);
                        $stmt->bindParam(':phone_address', $phone_address);
                        $stmt->bindParam(':is_same_as_permanent', $is_same_as_permanent);

                        $stmt->execute();

                        // การบันทึกข้อมูลการศึกษา
                        $stmt = $pdo->prepare("INSERT INTO education (person_id, degree_level, country, university, student_id, faculty_name, major_name, graduation_year) 
                                               VALUES (:person_id, :degree_level, :country, :university, :student_id, :faculty_name, :major_name, :graduation_year)");
                        $stmt->bindParam(':person_id', $person_id);
                        $stmt->bindParam(':degree_level', $degree_level);
                        $stmt->bindParam(':country', $country);
                        $stmt->bindParam(':university', $university);
                        $stmt->bindParam(':student_id', $student_id);
                        $stmt->bindParam(':faculty_name', $faculty_name);
                        $stmt->bindParam(':major_name', $major_name);
                        $stmt->bindParam(':graduation_year', $graduation_year);

                        $stmt->execute();

                        // ยืนยันการทำธุรกรรม
                        $pdo->commit();

                        $notification = "เพิ่มข้อมูลสำเร็จ";
                        $alertType = "success";
                        $showModal = true;

                        // ล้างค่าฟิลด์หลังจากบันทึกสำเร็จ
                        $title = '';
                        $first_name = '';
                        $last_name = '';
                        $citizen_id = '';
                        $birth_date = '';
                        $phone_personal = '';
                        $email = '';
                        $line_id = '';
                        $facebook = '';
                        $special_ability = '';
                        $photo_url = '';

                        $address_type = '';
                        $house_number = '';
                        $village = '';
                        $sub_district = '';
                        $district = '';
                        $province = '';
                        $zip_code = '';
                        $phone_address = '';
                        $is_same_as_permanent = '';

                        $degree_level = '';
                        $country = 'ประเทศไทย';
                        $university = '';
                        $student_id = '';
                        $faculty_name = '';
                        $major_name = '';
                        $graduation_year = '';
                    } catch (Exception $e) {
                        // ยกเลิกการทำธุรกรรมหากมีข้อผิดพลาด
                        $pdo->rollBack();
                        // บันทึกข้อผิดพลาดลงใน log
                        error_log("Error inserting data: " . $e->getMessage());

                        $notification = "เกิดข้อผิดพลาดในการบันทึกข้อมูล กรุณาลองใหม่อีกครั้ง.";
                        $alertType = "danger";
                        $showModal = true;
                    }
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
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
            width: 33%;
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
                <div class="step" id="step3">
                    <div class="circle">3</div>
                    <div class="label">ข้อมูลการศึกษา</div>
                </div>
            </div>
        </div>

        <!-- ฟอร์มข้อมูลส่วนบุคคล -->
        <form id="personalForm" method="POST" action="add_personal.php" enctype="multipart/form-data">
            <!-- เพิ่ม CSRF Token -->
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">

            <!-- Section 1 -->
            <div id="section1" class="form-section active">
                <h3>Step 1: เพิ่มข้อมูลส่วนบุคคล</h3>
                <div class="row">
                    <div class="col-md-3">
                        <div class="mb-3">
                            <label for="title" class="form-label">คำนำหน้า</label>
                            <input type="text" class="form-control <?= isset($errors['title']) ? 'is-invalid' : '' ?>" id="title" name="title" value="<?= htmlspecialchars($title) ?>" required>
                            <?php if (isset($errors['title'])): ?>
                                <div class="invalid-feedback">
                                    <?= $errors['title'] ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="col-md-5">
                        <div class="mb-3">
                            <label for="first_name" class="form-label">ชื่อ</label>
                            <input type="text" class="form-control <?= isset($errors['first_name']) ? 'is-invalid' : '' ?>" id="first_name" name="first_name" value="<?= htmlspecialchars($first_name) ?>" required>
                            <?php if (isset($errors['first_name'])): ?>
                                <div class="invalid-feedback">
                                    <?= $errors['first_name'] ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="last_name" class="form-label">นามสกุล</label>
                            <input type="text" class="form-control <?= isset($errors['last_name']) ? 'is-invalid' : '' ?>" id="last_name" name="last_name" value="<?= htmlspecialchars($last_name) ?>" required>
                            <?php if (isset($errors['last_name'])): ?>
                                <div class="invalid-feedback">
                                    <?= $errors['last_name'] ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="citizen_id" class="form-label">เลขประจำตัวประชาชน</label>
                            <input type="text" class="form-control <?= isset($errors['citizen_id']) ? 'is-invalid' : '' ?>" id="citizen_id" name="citizen_id" value="<?= htmlspecialchars($citizen_id) ?>" required maxlength="13" pattern="\d{13}" title="กรุณากรอกเลข 13 หลัก">
                            <?php if (isset($errors['citizen_id'])): ?>
                                <div class="invalid-feedback">
                                    <?= $errors['citizen_id'] ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="birth_date" class="form-label">วันเกิด</label>
                            <input type="date" class="form-control <?= isset($errors['birth_date']) ? 'is-invalid' : '' ?>" id="birth_date" name="birth_date" value="<?= htmlspecialchars($birth_date) ?>" required>
                            <?php if (isset($errors['birth_date'])): ?>
                                <div class="invalid-feedback">
                                    <?= $errors['birth_date'] ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="phone_personal" class="form-label">เบอร์โทรเคลื่อนที่</label>
                            <input type="text" class="form-control <?= isset($errors['phone_personal']) ? 'is-invalid' : '' ?>" id="phone_personal" name="phone_personal" value="<?= htmlspecialchars($phone_personal) ?>" required>
                            <?php if (isset($errors['phone_personal'])): ?>
                                <div class="invalid-feedback">
                                    <?= $errors['phone_personal'] ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="email" class="form-label">อีเมล</label>
                            <input type="email" class="form-control <?= isset($errors['email']) ? 'is-invalid' : '' ?>" id="email" name="email" value="<?= htmlspecialchars($email) ?>" required>
                            <?php if (isset($errors['email'])): ?>
                                <div class="invalid-feedback">
                                    <?= $errors['email'] ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="line_id" class="form-label">Line ID</label>
                            <input type="text" class="form-control" id="line_id" name="line_id" value="<?= htmlspecialchars($line_id) ?>">
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="facebook" class="form-label">Facebook</label>
                            <input type="text" class="form-control" id="facebook" name="facebook" value="<?= htmlspecialchars($facebook) ?>">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="photo_url" class="form-label">URL ของรูปภาพ</label>
                            <input type="url" class="form-control <?= isset($errors['photo']) ? 'is-invalid' : '' ?>" id="photo_url" name="photo_url" value="<?= htmlspecialchars($photo_url) ?>">
                        </div>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="special_ability" class="form-label">ความสามารถพิเศษ</label>
                    <textarea class="form-control" id="special_ability" name="special_ability"><?= htmlspecialchars($special_ability) ?></textarea>
                </div>

                <div class="mb-3">
                    <label for="photo_file" class="form-label">หรืออัพโหลดรูปภาพ (เฉพาะ JPG และ PNG)</label>
                    <input type="file" class="form-control <?= isset($errors['photo']) ? 'is-invalid' : '' ?>" id="photo_file" name="photo_file" accept=".jpg, .jpeg, .png">
                    <?php if (isset($errors['photo'])): ?>
                        <div class="invalid-feedback">
                            <?= $errors['photo'] ?>
                        </div>
                    <?php endif; ?>
                </div>
                <button type="button" class="btn btn-primary" onclick="nextSection()">ถัดไป</button>
            </div>

            <!-- Section 2 -->
            <div id="section2" class="form-section">
                <h3>Step 2: เพิ่มข้อมูลที่อยู่</h3>
                <div class="row">
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="address_type" class="form-label">ประเภทที่อยู่</label>
                            <select class="form-control <?= isset($errors['address_type']) ? 'is-invalid' : '' ?>" id="address_type" name="address_type" required>
                                <option value="">เลือกประเภทที่อยู่</option>
                                <option value="permanent" <?= $address_type == 'permanent' ? 'selected' : '' ?>>ที่อยู่บ้าน</option>
                                <option value="contact" <?= $address_type == 'contact' ? 'selected' : '' ?>>ที่อยู่ติดต่อ</option>
                                <option value="work" <?= $address_type == 'work' ? 'selected' : '' ?>>ที่ทำงาน</option>
                            </select>
                            <?php if (isset($errors['address_type'])): ?>
                                <div class="invalid-feedback">
                                    <?= $errors['address_type'] ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="house_number" class="form-label">บ้านเลขที่</label>
                            <input type="text" class="form-control <?= isset($errors['house_number']) ? 'is-invalid' : '' ?>" id="house_number" name="house_number" value="<?= htmlspecialchars($house_number) ?>" required>
                            <?php if (isset($errors['house_number'])): ?>
                                <div class="invalid-feedback">
                                    <?= $errors['house_number'] ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="village" class="form-label">หมู่บ้าน</label>
                            <input type="text" class="form-control" id="village" name="village" value="<?= htmlspecialchars($village) ?>">
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="province" class="form-label">จังหวัด</label>
                            <select class="form-control <?= isset($errors['province']) ? 'is-invalid' : '' ?>" id="province" name="province" required>
                                <option value="">เลือกจังหวัด</option>
                                <!-- ตัวเลือกจังหวัดจะถูกเพิ่มโดย JavaScript -->
                            </select>
                            <?php if (isset($errors['province'])): ?>
                                <div class="invalid-feedback">
                                    <?= $errors['province'] ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="district" class="form-label">อำเภอ</label>
                            <select class="form-control <?= isset($errors['district']) ? 'is-invalid' : '' ?>" id="district" name="district" required>
                                <option value="">เลือกอำเภอ</option>
                                <!-- ตัวเลือกอำเภอจะถูกเพิ่มโดย JavaScript -->
                            </select>
                            <?php if (isset($errors['district'])): ?>
                                <div class="invalid-feedback">
                                    <?= $errors['district'] ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="subdistrict" class="form-label">ตำบล</label>
                            <select class="form-control <?= isset($errors['subdistrict']) ? 'is-invalid' : '' ?>" id="subdistrict" name="subdistrict" required>
                                <option value="">เลือกตำบล</option>
                                <!-- ตัวเลือกตำบลจะถูกเพิ่มโดย JavaScript -->
                            </select>
                            <?php if (isset($errors['subdistrict'])): ?>
                                <div class="invalid-feedback">
                                    <?= $errors['subdistrict'] ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="zip_code" class="form-label">รหัสไปรษณีย์</label>
                            <input type="text" class="form-control <?= isset($errors['zip_code']) ? 'is-invalid' : '' ?>" id="zip_code" name="zip_code" value="<?= htmlspecialchars($zip_code) ?>" readonly>
                            <?php if (isset($errors['zip_code'])): ?>
                                <div class="invalid-feedback">
                                    <?= $errors['zip_code'] ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="phone_address" class="form-label">เบอร์โทรศัพท์</label>
                            <input type="text" class="form-control <?= isset($errors['phone_address']) ? 'is-invalid' : '' ?>" id="phone_address" name="phone_address" value="<?= htmlspecialchars($phone_address) ?>" required>
                            <?php if (isset($errors['phone_address'])): ?>
                                <div class="invalid-feedback">
                                    <?= $errors['phone_address'] ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="is_same_as_permanent" class="form-label">ที่อยู่นี้เหมือนกับที่อยู่ถาวรหรือไม่</label>
                            <select class="form-control" id="is_same_as_permanent" name="is_same_as_permanent">
                                <option value="0" <?= $is_same_as_permanent == '0' ? 'selected' : '' ?>>ไม่ใช่</option>
                                <option value="1" <?= $is_same_as_permanent == '1' ? 'selected' : '' ?>>ใช่</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="d-flex justify-content-between mt-3">
                    <button type="button" class="btn btn-secondary" onclick="prevSection()">ย้อนกลับ</button>
                    <button type="button" class="btn btn-primary" onclick="nextSection()">ถัดไป</button>
                </div>
            </div>

            <!-- Section 3 -->
            <div id="section3" class="form-section">
                <h3>Step 3: เพิ่มข้อมูลการศึกษา</h3>
                <div class="mb-3">
                    <label for="degree_level" class="form-label">ระดับการศึกษา</label>
                    <select class="form-control <?= isset($errors['degree_level']) ? 'is-invalid' : '' ?>" id="degree_level" name="degree_level" required>
                        <option value="">เลือกระดับการศึกษา</option>
                        <option value="bachelor" <?= $degree_level == 'bachelor' ? 'selected' : '' ?>>ปริญญาตรี</option>
                        <option value="master" <?= $degree_level == 'master' ? 'selected' : '' ?>>ปริญญาโท</option>
                        <option value="doctorate" <?= $degree_level == 'doctorate' ? 'selected' : '' ?>>ปริญญาเอก</option>
                    </select>
                    <?php if (isset($errors['degree_level'])): ?>
                        <div class="invalid-feedback">
                            <?= $errors['degree_level'] ?>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="mb-3">
                    <label for="country" class="form-label">ประเทศ</label>
                    <input type="text" class="form-control <?= isset($errors['country']) ? 'is-invalid' : '' ?>" id="country" name="country" value="<?= htmlspecialchars($country) ?>" required>
                    <?php if (isset($errors['country'])): ?>
                        <div class="invalid-feedback">
                            <?= $errors['country'] ?>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="mb-3">
                    <label for="university" class="form-label">มหาวิทยาลัย</label>
                    <input type="text" class="form-control <?= isset($errors['university']) ? 'is-invalid' : '' ?>" id="university" name="university" required>
                    <?php if (isset($errors['university'])): ?>
                        <div class="invalid-feedback">
                            <?= $errors['university'] ?>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="mb-3">
                    <label for="student_id" class="form-label">รหัสนักศึกษา</label>
                    <input type="text" class="form-control <?= isset($errors['student_id']) ? 'is-invalid' : '' ?>" id="student_id" name="student_id" required>
                    <?php if (isset($errors['student_id'])): ?>
                        <div class="invalid-feedback">
                            <?= $errors['student_id'] ?>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="mb-3">
                    <label for="faculty_name" class="form-label">คณะ</label>
                    <input type="text" class="form-control <?= isset($errors['faculty_name']) ? 'is-invalid' : '' ?>" id="faculty_name" name="faculty_name" value="<?= htmlspecialchars($_SESSION['faculty_name'] ?? '') ?>" required>
                    <?php if (isset($errors['faculty_name'])): ?>
                        <div class="invalid-feedback">
                            <?= $errors['faculty_name'] ?>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="mb-3">
                    <label for="major_name" class="form-label">สาขา</label>
                    <input type="text" class="form-control <?= isset($errors['major_name']) ? 'is-invalid' : '' ?>" id="major_name" name="major_name" required>
                    <?php if (isset($errors['major_name'])): ?>
                        <div class="invalid-feedback">
                            <?= $errors['major_name'] ?>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="mb-3">
                    <label for="graduation_year" class="form-label">ปีที่จบการศึกษา (ค.ศ.)</label>
                    <input type="text" class="form-control <?= isset($errors['graduation_year']) ? 'is-invalid' : '' ?>" id="graduation_year" name="graduation_year" pattern="\d{4}" maxlength="4" required>
                    <?php if (isset($errors['graduation_year'])): ?>
                        <div class="invalid-feedback">
                            <?= $errors['graduation_year'] ?>
                        </div>
                    <?php endif; ?>
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
            var currentSection = document.querySelector('.form-section.active');
            var nextSection = currentSection.nextElementSibling;

            // ตรวจสอบว่ามีส่วนถัดไปหรือไม่
            if (nextSection && nextSection.classList.contains('form-section')) {
                // ตรวจสอบฟิลด์ในส่วนปัจจุบัน
                var inputs = currentSection.querySelectorAll('input, textarea, select');
                var valid = true;

                inputs.forEach(function(input) {
                    if (!input.checkValidity()) {
                        input.classList.add('is-invalid');
                        valid = false;
                    } else {
                        input.classList.remove('is-invalid');
                    }
                });

                if (valid) {
                    currentSection.classList.remove('active');
                    nextSection.classList.add('active');

                    // อัปเดต Stepper
                    var currentStep = document.querySelector('.step.active');
                    var nextStep = currentStep.nextElementSibling;
                    if (nextStep && nextStep.classList.contains('step')) {
                        currentStep.classList.remove('active');
                        nextStep.classList.add('active');
                    }
                }
            }
        }

        function prevSection() {
            var currentSection = document.querySelector('.form-section.active');
            var prevSection = currentSection.previousElementSibling;

            if (prevSection && prevSection.classList.contains('form-section')) {
                currentSection.classList.remove('active');
                prevSection.classList.add('active');

                // อัปเดต Stepper
                var currentStep = document.querySelector('.step.active');
                var prevStep = currentStep.previousElementSibling;
                if (prevStep && prevStep.classList.contains('step')) {
                    currentStep.classList.remove('active');
                    prevStep.classList.add('active');
                }
            }
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
                            if ("<?= $sub_district ?>" == subdistrict.id) {
                                option.selected = true;
                            }
                            subdistrictSelect.appendChild(option);
                        });

                        // หลังจากเพิ่มตำบลแล้ว ให้โหลดรหัสไปรษณีย์ถ้าเลือกตำบล
                        if ("<?= $sub_district ?>" !== "") {
                            fetch('get_zipcode.php?subdistrict_id=' + subdistrictSelect.value)
                                .then(response => response.json())
                                .then(data => {
                                    zipCodeInput.value = data.zip_code;
                                });
                        }
                    });
            }

            // เมื่อเลือกจังหวัดให้แสดงอำเภอที่เกี่ยวข้อง
            provinceSelect.addEventListener('change', function() {
                districtSelect.innerHTML = '<option value="">เลือกอำเภอ</option>'; // ล้างตัวเลือกอำเภอ
                subdistrictSelect.innerHTML = '<option value="">เลือกตำบล</option>'; // ล้างตัวเลือกตำบล
                zipCodeInput.value = ''; // ล้างรหัสไปรษณีย์

                if (provinceSelect.value !== "") {
                    loadDistricts(provinceSelect.value);
                }
            });

            // เมื่อเลือกอำเภอให้แสดงตำบลที่เกี่ยวข้อง
            districtSelect.addEventListener('change', function() {
                subdistrictSelect.innerHTML = '<option value="">เลือกตำบล</option>'; // ล้างตัวเลือกตำบล
                zipCodeInput.value = ''; // ล้างรหัสไปรษณีย์

                if (districtSelect.value !== "") {
                    loadSubdistricts(districtSelect.value);
                }
            });

            // เมื่อเลือกตำบลให้แสดงรหัสไปรษณีย์ที่เกี่ยวข้อง
            subdistrictSelect.addEventListener('change', function() {
                if (subdistrictSelect.value !== "") {
                    fetch('get_zipcode.php?subdistrict_id=' + subdistrictSelect.value)
                        .then(response => response.json())
                        .then(data => {
                            zipCodeInput.value = data.zip_code;
                        });
                }
            });
        });
    </script>

</body>

</html>