<?php
// manage_departments.php
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
    // ดึงข้อมูลภาควิชาที่อยู่ภายใต้คณะนี้
    $stmt = $pdo->prepare("SELECT * FROM department WHERE faculty_id = :faculty_id ORDER BY department_name ASC");
    $stmt->execute([':faculty_id' => $faculty_id]);
    $departments = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // ดึงข้อมูลภาควิชาเพื่อใช้ใน Dropdown ของ Modal แก้ไขสาขาวิชา
    $departmentsForDropdown = $departments;
} catch (Exception $e) {
    echo "เกิดข้อผิดพลาด: " . $e->getMessage();
    exit();
}

?>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <title>จัดการภาควิชา</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Google Fonts (Sarabun) -->
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@400;500;600&display=swap" rel="stylesheet">
    <!-- Font Awesome สำหรับไอคอน -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- สไตล์เพิ่มเติม -->
    <style>
        body {
            font-family: "Sarabun", sans-serif;
            background-color: #f8f9fa;
        }

        .container {
            margin-top: 30px;
        }

        .department-card {
            margin-bottom: 20px;
            border: 1px solid #dee2e6;
            border-radius: 5px;
            background-color: #ffffff;
        }

        .department-header {
            background-color: #6c757d;
            color: #fff;
            padding: 15px;
            cursor: pointer;
            border-top-left-radius: 5px;
            border-top-right-radius: 5px;
        }

        .card-body {
            padding: 15px;
        }

        .major-table th,
        .major-table td {
            vertical-align: middle;
        }

        .btn-group {
            display: flex;
            gap: 5px;
        }

        .table th {
            background-color: #e9ecef;
        }
    </style>
</head>

<body>
    <?php include 'navbar-faculty.php'; ?>

    <div class="container">
        <h3>จัดการภาควิชา</h3>
        <!-- ปุ่มเพิ่มภาควิชาใหม่ -->
        <div class="text-end mb-3">
            <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addDepartmentModal">
                <i class="fas fa-plus"></i> เพิ่มภาควิชาใหม่
            </button>
        </div>

        <?php if (!empty($departments)): ?>
            <?php foreach ($departments as $dept): ?>
                <div class="department-card">
                    <div class="department-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><?= htmlspecialchars($dept['department_name']) ?></h5>
                        <div class="btn-group">
                            <!-- ปุ่มแก้ไขภาควิชา -->
                            <button class="btn btn-sm btn-primary editDepartmentBtn" data-department-id="<?= $dept['department_id'] ?>" data-department-name="<?= htmlspecialchars($dept['department_name']) ?>" data-department-description="<?= htmlspecialchars($dept['department_description']) ?>">
                                <i class="fas fa-edit"></i> แก้ไข
                            </button>
                            <!-- ปุ่มลบภาควิชา -->
                            <button class="btn btn-sm btn-danger deleteDepartmentBtn" data-department-id="<?= $dept['department_id'] ?>" data-department-name="<?= htmlspecialchars($dept['department_name']) ?>">
                                <i class="fas fa-trash"></i> ลบ
                            </button>
                            <!-- ปุ่มเพิ่มสาขาวิชาใหม่ -->
                            <button class="btn btn-sm btn-success addMajorBtn" data-department-id="<?= $dept['department_id'] ?>">
                                <i class="fas fa-plus"></i> เพิ่มสาขาวิชา
                            </button>
                        </div>
                    </div>

                    <div class="card-body">
                        <p><?= nl2br(htmlspecialchars($dept['department_description'])) ?></p>

                        <?php
                        // ดึงข้อมูลสาขาวิชาที่อยู่ภายใต้ภาควิชานี้
                        $stmt = $pdo->prepare("SELECT * FROM major WHERE department_id = :department_id ORDER BY major_name ASC");
                        $stmt->execute([':department_id' => $dept['department_id']]);
                        $majors = $stmt->fetchAll(PDO::FETCH_ASSOC);
                        ?>

                        <?php if (!empty($majors)): ?>
                            <table class="table table-bordered major-table">
                                <thead class="table-light">
                                    <tr>
                                        <th>รหัสสาขาวิชา</th>
                                        <th>ชื่อสาขาวิชา</th>
                                        <th>รายละเอียด</th>
                                        <th>การจัดการ</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($majors as $major): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($major['major_id']) ?></td>
                                            <td><?= htmlspecialchars($major['major_name']) ?></td>
                                            <td><?= nl2br(htmlspecialchars($major['major_description'])) ?></td>
                                            <td>
                                                <div class="btn-group">
                                                    <!-- ปุ่มแก้ไขสาขาวิชา -->
                                                    <button class="btn btn-sm btn-primary editMajorBtn"
                                                        data-major-id="<?= $major['major_id'] ?>"
                                                        data-major-name="<?= htmlspecialchars($major['major_name']) ?>"
                                                        data-major-description="<?= htmlspecialchars($major['major_description']) ?>"
                                                        data-department-id="<?= $dept['department_id'] ?>">
                                                        <i class="fas fa-edit"></i> แก้ไข
                                                    </button>


                                                    <!-- ปุ่มลบสาขาวิชา -->
                                                    <button class="btn btn-sm btn-danger deleteMajorBtn"
                                                        data-major-id="<?= $major['major_id'] ?>"
                                                        data-major-name="<?= htmlspecialchars($major['major_name']) ?>">
                                                        <i class="fas fa-trash"></i> ลบ
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        <?php else: ?>
                            <p class="text-muted">ไม่มีสาขาวิชาในภาควิชานี้</p>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p class="text-muted">ไม่มีภาควิชาในคณะนี้</p>
        <?php endif; ?>
    </div>

    <!-- Modal สำหรับเพิ่มภาควิชาใหม่ -->
    <div class="modal fade" id="addDepartmentModal" tabindex="-1" aria-labelledby="addDepartmentModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <form id="addDepartmentForm">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="addDepartmentModalLabel">เพิ่มภาควิชาใหม่</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="ปิด"></button>
                    </div>
                    <div class="modal-body">
                        <!-- ชื่อภาควิชา -->
                        <div class="mb-3">
                            <label for="addDepartmentName" class="form-label">ชื่อภาควิชา</label>
                            <input type="text" class="form-control" id="addDepartmentName" name="department_name" required>
                        </div>
                        <!-- รายละเอียดภาควิชา -->
                        <div class="mb-3">
                            <label for="addDepartmentDescription" class="form-label">รายละเอียดภาควิชา</label>
                            <textarea class="form-control" id="addDepartmentDescription" name="department_description" rows="3"></textarea>
                        </div>
                        <div id="addDepartmentError" class="text-danger"></div>
                        <div id="addDepartmentSuccess" class="text-success"></div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
                        <button type="submit" class="btn btn-primary">เพิ่มภาควิชา</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal สำหรับแก้ไขภาควิชา -->
    <div class="modal fade" id="editDepartmentModal" tabindex="-1" aria-labelledby="editDepartmentModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <form id="editDepartmentForm">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="editDepartmentModalLabel">แก้ไขภาควิชา</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="ปิด"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" id="editDepartmentId" name="department_id">
                        <!-- ชื่อภาควิชา -->
                        <div class="mb-3">
                            <label for="editDepartmentName" class="form-label">ชื่อภาควิชา</label>
                            <input type="text" class="form-control" id="editDepartmentName" name="department_name" required>
                        </div>
                        <!-- รายละเอียดภาควิชา -->
                        <div class="mb-3">
                            <label for="editDepartmentDescription" class="form-label">รายละเอียดภาควิชา</label>
                            <textarea class="form-control" id="editDepartmentDescription" name="department_description" rows="3"></textarea>
                        </div>
                        <div id="editDepartmentError" class="text-danger"></div>
                        <div id="editDepartmentSuccess" class="text-success"></div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
                        <button type="submit" class="btn btn-primary">บันทึกการเปลี่ยนแปลง</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal สำหรับยืนยันการลบภาควิชา -->
    <div class="modal fade" id="deleteDepartmentModal" tabindex="-1" aria-labelledby="deleteDepartmentModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <form id="deleteDepartmentForm">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="deleteDepartmentModalLabel">ลบภาควิชา</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="ปิด"></button>
                    </div>
                    <div class="modal-body">
                        <p>คุณแน่ใจหรือว่าต้องการลบภาควิชา "<span id="deleteDepartmentName"></span>"?</p>
                        <input type="hidden" id="deleteDepartmentId" name="department_id">
                        <div id="deleteDepartmentError" class="text-danger"></div>
                        <div id="deleteDepartmentSuccess" class="text-success"></div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
                        <button type="submit" class="btn btn-danger">ลบภาควิชา</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal สำหรับเพิ่มสาขาวิชาใหม่ -->
    <div class="modal fade" id="addMajorModal" tabindex="-1" aria-labelledby="addMajorModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <form id="addMajorForm">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="addMajorModalLabel">เพิ่มสาขาวิชาใหม่</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="ปิด"></button>
                    </div>
                    <div class="modal-body">
                        <!-- ชื่อสาขาวิชา -->
                        <div class="mb-3">
                            <label for="addMajorName" class="form-label">ชื่อสาขาวิชา</label>
                            <input type="text" class="form-control" id="addMajorName" name="major_name" required>
                        </div>
                        <!-- รายละเอียดสาขาวิชา -->
                        <div class="mb-3">
                            <label for="addMajorDescription" class="form-label">รายละเอียดสาขาวิชา</label>
                            <textarea class="form-control" id="addMajorDescription" name="major_description" rows="3"></textarea>
                        </div>
                        <!-- ซ่อนค่า department_id -->
                        <input type="hidden" id="addMajorDepartmentId" name="department_id">
                        <div id="addMajorError" class="text-danger"></div>
                        <div id="addMajorSuccess" class="text-success"></div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
                        <button type="submit" class="btn btn-primary">เพิ่มสาขาวิชา</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal สำหรับแก้ไขสาขาวิชา -->
    <div class="modal fade" id="editMajorModal" tabindex="-1" aria-labelledby="editMajorModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <form id="editMajorForm">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="editMajorModalLabel">แก้ไขสาขาวิชา</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="ปิด"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" id="editMajorId" name="major_id">
                        <!-- ชื่อสาขาวิชา -->
                        <div class="mb-3">
                            <label for="editMajorName" class="form-label">ชื่อสาขาวิชา</label>
                            <input type="text" class="form-control" id="editMajorName" name="major_name" required>
                        </div>
                        <!-- รายละเอียดสาขาวิชา -->
                        <div class="mb-3">
                            <label for="editMajorDescription" class="form-label">รายละเอียดสาขาวิชา</label>
                            <textarea class="form-control" id="editMajorDescription" name="major_description" rows="3"></textarea>
                        </div>
                        <!-- เลือกภาควิชา -->
                        <div class="mb-3">
                            <label for="editMajorDepartmentId" class="form-label">ภาควิชา</label>
                            <select class="form-control" id="editMajorDepartmentId" name="department_id" required>
                                <?php foreach ($departmentsForDropdown as $deptOption): ?>
                                    <option value="<?= $deptOption['department_id'] ?>"><?= htmlspecialchars($deptOption['department_name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div id="editMajorError" class="text-danger"></div>
                        <div id="editMajorSuccess" class="text-success"></div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
                        <button type="submit" class="btn btn-primary">บันทึกการเปลี่ยนแปลง</button>
                    </div>
                </div>
            </form>
        </div>
    </div>


    <!-- Modal สำหรับยืนยันการลบสาขาวิชา -->
    <div class="modal fade" id="deleteMajorModal" tabindex="-1" aria-labelledby="deleteMajorModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <form id="deleteMajorForm">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="deleteMajorModalLabel">ลบสาขาวิชา</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="ปิด"></button>
                    </div>
                    <div class="modal-body">
                        <p>คุณแน่ใจหรือว่าต้องการลบสาขาวิชา "<span id="deleteMajorName"></span>"?</p>
                        <input type="hidden" id="deleteMajorId" name="major_id">
                        <div id="deleteMajorError" class="text-danger"></div>
                        <div id="deleteMajorSuccess" class="text-success"></div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
                        <button type="submit" class="btn btn-danger">ลบสาขาวิชา</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Bootstrap JS และ dependencies -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- jQuery (ถ้าจำเป็นต้องใช้) -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        // เพิ่มภาควิชาใหม่
        const addDepartmentForm = document.getElementById('addDepartmentForm');
        addDepartmentForm.addEventListener('submit', function(e) {
            e.preventDefault();
            // ส่งข้อมูลไปยังเซิร์ฟเวอร์เพื่อเพิ่มภาควิชา
            // คุณสามารถใช้ AJAX หรือส่งฟอร์มแบบธรรมดา
        });

        // แก้ไขภาควิชา
        document.querySelectorAll('.editDepartmentBtn').forEach(btn => {
            btn.addEventListener('click', function() {
                const departmentId = this.getAttribute('data-department-id');
                const departmentName = this.getAttribute('data-department-name');
                const departmentDescription = this.getAttribute('data-department-description');

                // ตั้งค่าในฟอร์มแก้ไข
                document.getElementById('editDepartmentId').value = departmentId;
                document.getElementById('editDepartmentName').value = departmentName;
                document.getElementById('editDepartmentDescription').value = departmentDescription;

                // เปิด Modal
                const editDepartmentModal = new bootstrap.Modal(document.getElementById('editDepartmentModal'));
                editDepartmentModal.show();
            });
        });

        // ลบภาควิชา
        document.querySelectorAll('.deleteDepartmentBtn').forEach(btn => {
            btn.addEventListener('click', function() {
                const departmentId = this.getAttribute('data-department-id');
                const departmentName = this.getAttribute('data-department-name');

                // ตั้งค่าในฟอร์มลบ
                document.getElementById('deleteDepartmentId').value = departmentId;
                document.getElementById('deleteDepartmentName').textContent = departmentName;

                // เปิด Modal
                const deleteDepartmentModal = new bootstrap.Modal(document.getElementById('deleteDepartmentModal'));
                deleteDepartmentModal.show();
            });
        });
    </script>
    <script>
        // เพิ่มสาขาวิชาใหม่
        document.querySelectorAll('.addMajorBtn').forEach(btn => {
            btn.addEventListener('click', function() {
                const departmentId = this.getAttribute('data-department-id');

                // ตั้งค่า department_id ในฟอร์ม
                document.getElementById('addMajorDepartmentId').value = departmentId;

                // เปิด Modal
                const addMajorModal = new bootstrap.Modal(document.getElementById('addMajorModal'));
                addMajorModal.show();
            });
        });

        // แก้ไขสาขาวิชา
        document.querySelectorAll('.editMajorBtn').forEach(btn => {
            btn.addEventListener('click', function() {
                const majorId = this.getAttribute('data-major-id');
                const majorName = this.getAttribute('data-major-name');
                const majorDescription = this.getAttribute('data-major-description');
                const departmentId = this.getAttribute('data-department-id');

                // ตั้งค่าในฟอร์มแก้ไข
                document.getElementById('editMajorId').value = majorId;
                document.getElementById('editMajorName').value = majorName;
                document.getElementById('editMajorDescription').value = majorDescription;
                document.getElementById('editMajorDepartmentId').value = departmentId; // ตั้งค่า department_id ใน Dropdown

                // รีเซ็ตข้อความข้อผิดพลาด
                $('#editMajorError').text('');

                // เปิด Modal
                const editMajorModal = new bootstrap.Modal(document.getElementById('editMajorModal'));
                editMajorModal.show();
            });
        });


        // ลบสาขาวิชา
        document.querySelectorAll('.deleteMajorBtn').forEach(btn => {
            btn.addEventListener('click', function() {
                const majorId = this.getAttribute('data-major-id');
                const majorName = this.getAttribute('data-major-name');

                // ตั้งค่าในฟอร์มลบ
                document.getElementById('deleteMajorId').value = majorId;
                document.getElementById('deleteMajorName').textContent = majorName;

                // เปิด Modal
                const deleteMajorModal = new bootstrap.Modal(document.getElementById('deleteMajorModal'));
                deleteMajorModal.show();
            });
        });
    </script>
    <script>
        $(document).ready(function() {
            // ฟังก์ชันสำหรับส่งฟอร์มเพิ่มภาควิชา
            $('#addDepartmentForm').submit(function(e) {
                e.preventDefault(); // ป้องกันการส่งฟอร์มแบบปกติ
                $.ajax({
                    url: 'add_department.php',
                    type: 'POST',
                    data: $(this).serialize(),
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            // ถ้าสำเร็จ ให้รีเฟรชหน้า หรืออัปเดตเนื้อหา
                            location.reload();
                        } else {
                            // แสดงข้อความข้อผิดพลาด
                            $('#addDepartmentError').text(response.message);
                        }
                    },
                    error: function() {
                        $('#addDepartmentError').text('เกิดข้อผิดพลาดในการเชื่อมต่อกับเซิร์ฟเวอร์');
                    }
                });
            });

            // ฟังก์ชันสำหรับส่งฟอร์มแก้ไขภาควิชา
            $('#editDepartmentForm').submit(function(e) {
                e.preventDefault();
                $.ajax({
                    url: 'edit_department.php',
                    type: 'POST',
                    data: $(this).serialize(),
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            location.reload();
                        } else {
                            $('#editDepartmentError').text(response.message);
                        }
                    },
                    error: function() {
                        $('#editDepartmentError').text('เกิดข้อผิดพลาดในการเชื่อมต่อกับเซิร์ฟเวอร์');
                    }
                });
            });

            // ฟังก์ชันสำหรับส่งฟอร์มลบภาควิชา
            $('#deleteDepartmentForm').submit(function(e) {
                e.preventDefault();
                $.ajax({
                    url: 'delete_department.php',
                    type: 'POST',
                    data: $(this).serialize(),
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            location.reload();
                        } else {
                            $('#deleteDepartmentError').text(response.message);
                        }
                    },
                    error: function() {
                        $('#deleteDepartmentError').text('เกิดข้อผิดพลาดในการเชื่อมต่อกับเซิร์ฟเวอร์');
                    }
                });
            });

            // ฟังก์ชันสำหรับเพิ่มสาขาวิชา
            $('#addMajorForm').submit(function(e) {
                e.preventDefault();
                $.ajax({
                    url: 'add_major.php',
                    type: 'POST',
                    data: $(this).serialize(),
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            location.reload();
                        } else {
                            $('#addMajorError').text(response.message);
                        }
                    },
                    error: function() {
                        $('#addMajorError').text('เกิดข้อผิดพลาดในการเชื่อมต่อกับเซิร์ฟเวอร์');
                    }
                });
            });

            // ฟังก์ชันสำหรับแก้ไขสาขาวิชา
            $('#editMajorForm').submit(function(e) {
                e.preventDefault();
                $.ajax({
                    url: 'edit_major.php',
                    type: 'POST',
                    data: $(this).serialize(),
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            location.reload();
                        } else {
                            $('#editMajorError').text(response.message);
                        }
                    },
                    error: function() {
                        $('#editMajorError').text('เกิดข้อผิดพลาดในการเชื่อมต่อกับเซิร์ฟเวอร์');
                    }
                });
            });

            // ฟังก์ชันสำหรับลบสาขาวิชา
            $('#deleteMajorForm').submit(function(e) {
                e.preventDefault();
                $.ajax({
                    url: 'delete_major.php',
                    type: 'POST',
                    data: $(this).serialize(),
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            location.reload();
                        } else {
                            $('#deleteMajorError').text(response.message);
                        }
                    },
                    error: function() {
                        $('#deleteMajorError').text('เกิดข้อผิดพลาดในการเชื่อมต่อกับเซิร์ฟเวอร์');
                    }
                });
            });
        });
    </script>

</body>

</html>