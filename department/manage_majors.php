<?php
// manage_majors.php
session_start();
require '../db_connect.php'; // ปรับพาธให้ถูกต้องตามโครงสร้างโฟลเดอร์

// ตรวจสอบสิทธิ์ผู้ใช้: อนุญาตเฉพาะ admin และ department
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['access_level'], ['department'])) {
    header("Location: ../login.php");
    exit();
}

// สร้าง CSRF token ถ้ายังไม่มี
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// ฟังก์ชันสำหรับการป้องกัน XSS
function escape($html) {
    return htmlspecialchars($html, ENT_QUOTES | ENT_SUBSTITUTE, "UTF-8");
}

// ดึงข้อมูลสาขาวิชาที่เกี่ยวข้องกับผู้ใช้ (สำหรับ department)
$department_id = null;
if ($_SESSION['access_level'] === 'department') {
    $department_id = $_SESSION['department_id'];
}

// การจัดการการเพิ่มสาขาวิชา
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_major') {
    // ตรวจสอบ CSRF token
    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        die("Invalid CSRF token");
    }

    $major_name = trim($_POST['major_name']);
    $major_description = trim($_POST['major_description']);

    if (empty($major_name) || empty($major_description)) {
        $error = "กรุณากรอกชื่อสาขาวิชาและคำอธิบาย";
    } else {
        try {
            // สำหรับผู้ใช้ระดับ 'department', กำหนด department_id อัตโนมัติ
            if ($_SESSION['access_level'] === 'department') {
                $stmt = $pdo->prepare("INSERT INTO major (major_name, major_description, department_id) VALUES (:major_name, :major_description, :department_id)");
                $stmt->execute([
                    ':major_name' => $major_name,
                    ':major_description' => $major_description,
                    ':department_id' => $department_id
                ]);
            } 
            header("Location: manage_majors.php?message=เพิ่มสาขาวิชาสำเร็จ");
            exit();
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) { // Duplicate entry
                $error = "ชื่อสาขาวิชานี้มีอยู่แล้ว";
            } else {
                $error = "เกิดข้อผิดพลาด: " . escape($e->getMessage());
            }
        } catch (Exception $e) {
            $error = "เกิดข้อผิดพลาด: " . escape($e->getMessage());
        }
    }
}

// การจัดการการแก้ไขสาขาวิชา
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'edit_major') {
    // ตรวจสอบ CSRF token
    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        die("Invalid CSRF token");
    }

    $major_id = intval($_POST['major_id']);
    $new_major_name = trim($_POST['new_major_name']);
    $new_major_description = trim($_POST['new_major_description']);

    if ($major_id <= 0 || empty($new_major_name)) {
        $error = "กรุณากรอกชื่อสาขา";
    } else {
        try {
            // สำหรับผู้ใช้ระดับ 'department', ตรวจสอบว่า major นี้อยู่ภายใต้ภาควิชาของตนเอง
            if ($_SESSION['access_level'] === 'department') {
                $stmt = $pdo->prepare("SELECT department_id FROM major WHERE major_id = :major_id");
                $stmt->execute([':major_id' => $major_id]);
                $current_department_id = $stmt->fetchColumn();

                if ($current_department_id != $department_id) {
                    throw new Exception("คุณไม่มีสิทธิ์แก้ไขสาขาวิชานี้");
                }
            }

            // อัพเดทชื่อสาขาวิชาและคำอธิบาย
            $stmt = $pdo->prepare("UPDATE major SET major_name = :major_name, major_description = :major_description WHERE major_id = :major_id");
            $stmt->execute([
                ':major_name' => $new_major_name,
                ':major_description' => $new_major_description,
                ':major_id' => $major_id
            ]);

            header("Location: manage_majors.php?message=แก้ไขสาขาวิชาสำเร็จ");
            exit();
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) { // Duplicate entry
                $error = "ชื่อสาขาวิชานี้มีอยู่แล้ว";
            } else {
                $error = "เกิดข้อผิดพลาด: " . escape($e->getMessage());
            }
        } catch (Exception $e) {
            $error = "เกิดข้อผิดพลาด: " . escape($e->getMessage());
        }
    }
}

// การจัดการการลบสาขาวิชา
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete_major') {
    // ตรวจสอบ CSRF token
    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        die("Invalid CSRF token");
    }

    $major_id = intval($_POST['major_id']);

    if ($major_id <= 0) {
        $error = "Invalid Major ID";
    } else {
        try {
            // สำหรับผู้ใช้ระดับ 'department', ตรวจสอบว่า major นี้อยู่ภายใต้ภาควิชาของตนเอง
            if ($_SESSION['access_level'] === 'department') {
                $stmt = $pdo->prepare("SELECT department_id FROM major WHERE major_id = :major_id");
                $stmt->execute([':major_id' => $major_id]);
                $current_department_id = $stmt->fetchColumn();

                if ($current_department_id != $department_id) {
                    throw new Exception("คุณไม่มีสิทธิ์ลบสาขาวิชานี้");
                }
            }

            // ลบสาขาวิชา
            $stmt = $pdo->prepare("DELETE FROM major WHERE major_id = :major_id");
            $stmt->execute([':major_id' => $major_id]);

            header("Location: manage_majors.php?message=ลบสาขาวิชาสำเร็จ");
            exit();
        } catch (PDOException $e) {
            $error = "เกิดข้อผิดพลาด: " . escape($e->getMessage());
        } catch (Exception $e) {
            $error = "เกิดข้อผิดพลาด: " . escape($e->getMessage());
        }
    }
}

// ดึงข้อมูลสาขาวิชาที่ผู้ใช้สามารถจัดการได้
try {
    if ($_SESSION['access_level'] === 'admin') {
        // สำหรับ admin, ดึงสาขาวิชาทั้งหมด
        $stmt = $pdo->prepare("
            SELECT m.major_id, m.major_name, m.major_description, dept.department_name, faculty.faculty_name
            FROM major m
            JOIN department dept ON m.department_id = dept.department_id
            JOIN faculty ON dept.faculty_id = faculty.faculty_id
            ORDER BY faculty.faculty_name, dept.department_name, m.major_name ASC
        ");
        $stmt->execute();
    } else {
        // สำหรับ department, ดึงสาขาวิชาเฉพาะภาควิชาของตนเอง
        $stmt = $pdo->prepare("
            SELECT m.major_id, m.major_name, m.major_description, dept.department_name, faculty.faculty_name
            FROM major m
            JOIN department dept ON m.department_id = dept.department_id
            JOIN faculty ON dept.faculty_id = faculty.faculty_id
            WHERE dept.department_id = :department_id
            ORDER BY m.major_name ASC
        ");
        $stmt->execute([':department_id' => $department_id]);
    }
    $majors_list = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error = "เกิดข้อผิดพลาดในการดึงข้อมูล: " . escape($e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>จัดการสาขาวิชา</title>
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

        .action-buttons form {
            display: inline;
        }
    </style>
</head>

<body>
    <?php
    // เลือก Navbar ตามระดับการเข้าถึง
    if ($_SESSION['access_level'] === 'admin') {
        include 'navbar.php';
    } elseif ($_SESSION['access_level'] === 'department') {
        include 'navbar-department.php';
    }
    ?>

    <div class="container mt-5">
        <h3 class="mb-4">จัดการสาขาวิชา</h3>

        <!-- แสดงข้อความสำเร็จหรือข้อผิดพลาด -->
        <?php if (isset($_GET['message'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?= escape($_GET['message']) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="ปิด"></button>
            </div>
        <?php endif; ?>
        <?php if (isset($error)): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?= escape($error) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="ปิด"></button>
            </div>
        <?php endif; ?>

        <!-- ฟอร์มสำหรับเพิ่มสาขาวิชา -->
        <div class="card mb-4">
            <div class="card-header">
                เพิ่มสาขาวิชาใหม่
            </div>
            <div class="card-body">
                <form method="POST" action="manage_majors.php">
                    <input type="hidden" name="csrf_token" value="<?= escape($_SESSION['csrf_token']) ?>">
                    <input type="hidden" name="action" value="add_major">
                    <div class="mb-3">
                        <label for="major_name" class="form-label">ชื่อสาขาวิชา</label>
                        <input type="text" class="form-control" id="major_name" name="major_name" required>
                    </div>
                    <div class="mb-3">
                        <label for="major_description" class="form-label">คำอธิบายสาขาวิชา</label>
                        <textarea class="form-control" id="major_description" name="major_description" rows="3" ></textarea>
                    </div>
                    <?php if ($_SESSION['access_level'] === 'admin'): ?>
                        <div class="mb-3">
                            <label for="department_id" class="form-label">ภาควิชา</label>
                            <select class="form-select" id="department_id" name="department_id" required>
                                <option value="">เลือกภาควิชา</option>
                                <?php
                                // ดึงข้อมูลภาควิชาทั้งหมดสำหรับ admin
                                try {
                                    $stmtDepartments = $pdo->prepare("
                                        SELECT department_id, department_name 
                                        FROM department 
                                        ORDER BY department_name ASC
                                    ");
                                    $stmtDepartments->execute();
                                    $departments = $stmtDepartments->fetchAll(PDO::FETCH_ASSOC);
                                    foreach ($departments as $dept) {
                                        echo '<option value="' . escape($dept['department_id']) . '">' . escape($dept['department_name']) . '</option>';
                                    }
                                } catch (PDOException $e) {
                                    echo '<option value="">ไม่สามารถดึงข้อมูลภาควิชาได้</option>';
                                }
                                ?>
                            </select>
                        </div>
                    <?php endif; ?>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-plus"></i> เพิ่มสาขาวิชา</button>
                </form>
            </div>
        </div>

        <!-- ตารางแสดงรายการสาขาวิชา -->
        <div class="card">
            <div class="card-header">
                รายการสาขาวิชา
            </div>
            <div class="card-body">
                <?php if (!empty($majors_list)): ?>
                    <table class="table table-striped table-bordered table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>ชื่อสาขาวิชา</th>
                                <th>คำอธิบาย</th>
                                <?php if ($_SESSION['access_level'] === 'admin'): ?>
                                    <th>ภาควิชา</th>
                                    <th>คณะ</th>
                                <?php endif; ?>
                                <th>การกระทำ</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($majors_list as $major): ?>
                                <tr>
                                    <td><?= escape($major['major_name']) ?></td>
                                    <td><?= escape($major['major_description']) ?></td>
                                    <?php if ($_SESSION['access_level'] === 'admin'): ?>
                                        <td><?= escape($major['department_name']) ?></td>
                                        <td><?= escape($major['faculty_name']) ?></td>
                                    <?php endif; ?>
                                    <td class="action-buttons">
                                        <!-- ปุ่มแก้ไข -->
                                        <button type="button" class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#editMajorModal<?= escape($major['major_id']) ?>">
                                            <i class="fas fa-edit"></i> แก้ไข
                                        </button>
                                        <!-- ปุ่มลบ -->
                                        <form method="POST" action="manage_majors.php" onsubmit="return confirm('คุณแน่ใจหรือไม่ที่จะลบสาขาวิชานี้?');">
                                            <input type="hidden" name="csrf_token" value="<?= escape($_SESSION['csrf_token']) ?>">
                                            <input type="hidden" name="action" value="delete_major">
                                            <input type="hidden" name="major_id" value="<?= escape($major['major_id']) ?>">
                                            <button type="submit" class="btn btn-danger btn-sm"><i class="fas fa-trash-alt"></i> ลบ</button>
                                        </form>

                                        <!-- Modal สำหรับแก้ไขสาขาวิชา -->
                                        <div class="modal fade" id="editMajorModal<?= escape($major['major_id']) ?>" tabindex="-1" aria-labelledby="editMajorModalLabel<?= escape($major['major_id']) ?>" aria-hidden="true">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <form method="POST" action="manage_majors.php">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title" id="editMajorModalLabel<?= escape($major['major_id']) ?>">แก้ไขสาขาวิชา</h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="ปิด"></button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <input type="hidden" name="csrf_token" value="<?= escape($_SESSION['csrf_token']) ?>">
                                                            <input type="hidden" name="action" value="edit_major">
                                                            <input type="hidden" name="major_id" value="<?= escape($major['major_id']) ?>">
                                                            <div class="mb-3">
                                                                <label for="new_major_name<?= escape($major['major_id']) ?>" class="form-label">ชื่อสาขาวิชาใหม่</label>
                                                                <input type="text" class="form-control" id="new_major_name<?= escape($major['major_id']) ?>" name="new_major_name" value="<?= escape($major['major_name']) ?>" required>
                                                            </div>
                                                            <div class="mb-3">
                                                                <label for="new_major_description<?= escape($major['major_id']) ?>" class="form-label">คำอธิบายสาขาวิชาใหม่</label>
                                                                <textarea class="form-control" id="new_major_description<?= escape($major['major_id']) ?>" name="new_major_description" rows="3" ><?= escape($major['major_description']) ?></textarea>
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
                                                            <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> บันทึก</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                        <!-- สิ้นสุด Modal -->
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p>ไม่มีสาขาวิชาในระบบ</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Bootstrap 5 JS และ Popper.js -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
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
