<?php
session_start();

// ตรวจสอบสิทธิ์การเข้าถึง
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['access_level'], ['admin'])) {
    header("Location: login.php");
    exit();
}

require 'db_connect.php'; // เรียกใช้ไฟล์เชื่อมต่อฐานข้อมูล

try {
    // ดึงข้อมูลคณะทั้งหมด
    $stmt = $pdo->prepare("SELECT faculty_id, faculty_name, faculty_description FROM faculty ORDER BY faculty_name ASC");
    $stmt->execute();
    $faculties = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // เตรียมข้อมูลสำหรับภาควิชาและสาขาวิชา
    $facultyData = [];

    foreach ($faculties as $faculty) {
        $faculty_id = $faculty['faculty_id'];

        // ดึงข้อมูลภาควิชาที่สังกัดคณะนี้
        $stmt = $pdo->prepare("SELECT department_id, department_name, department_description FROM department WHERE faculty_id = :faculty_id ORDER BY department_name ASC");
        $stmt->execute([':faculty_id' => $faculty_id]);
        $departments = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $departmentData = [];

        foreach ($departments as $department) {
            $department_id = $department['department_id'];

            // ดึงข้อมูลสาขาวิชาที่สังกัดภาควิชานี้
            $stmt = $pdo->prepare("SELECT major_id, major_name, major_description FROM major WHERE department_id = :department_id ORDER BY major_name ASC");
            $stmt->execute([':department_id' => $department_id]);
            $majors = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $department['majors'] = $majors;
            $departmentData[] = $department;
        }

        $faculty['departments'] = $departmentData;
        $facultyData[] = $faculty;
    }
} catch (PDOException $e) {
    echo "เกิดข้อผิดพลาด: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <title>คณะ ภาควิชา และสาขาวิชา</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Google Fonts (Sarabun) -->
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@400;500;600&display=swap" rel="stylesheet">
    <!-- Font Awesome สำหรับไอคอน -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            font-family: "Sarabun", sans-serif;
        }

        .faculty-card {
            margin-bottom: 20px;
        }

        .faculty-header {
            background-color: #343a40;
            color: #fff;
            padding: 10px;
            cursor: pointer;
        }

        .department-header {
            background-color: #6c757d;
            color: #fff;
            padding: 8px;
            cursor: pointer;
        }

        .major-table th,
        .major-table td {
            vertical-align: middle;
        }

        .toggle-header {
            cursor: pointer;
            /* เปลี่ยนเคอร์เซอร์เป็นมือเมื่อวางเมาส์เหนือหัวข้อ */
        }
    </style>

</head>


<script>
    document.addEventListener('DOMContentLoaded', function() {
        // เลือกทุกองค์ประกอบที่มีคลาส 'toggle-header'
        const toggleHeaders = document.querySelectorAll('.toggle-header');

        toggleHeaders.forEach(function(header) {
            header.addEventListener('click', function() {
                // ค้นหาไอคอนที่เป็นลูกของหัวข้อนี้
                const icon = header.querySelector('.toggle-icon');

                if (icon) {
                    // สลับคลาสระหว่าง chevron-up และ chevron-down
                    if (icon.classList.contains('fa-chevron-up')) {
                        icon.classList.remove('fa-chevron-up');
                        icon.classList.add('fa-chevron-down');
                    } else {
                        icon.classList.remove('fa-chevron-down');
                        icon.classList.add('fa-chevron-up');
                    }
                }
            });
        });
    });
</script>

<body>
    <?php include 'navbar.php'; ?>

    <div class="container mt-5">
        <h3 class="mb-4">คณะ ภาควิชา และสาขาวิชาทั้งหมด</h3>
        <!-- ปุ่มเพิ่มคณะใหม่ -->
        <button class="btn btn-success mb-3" data-bs-toggle="modal" data-bs-target="#addFacultyModal">
            <i class="fas fa-plus"></i> เพิ่มคณะใหม่
        </button>
        <div class="accordion" id="facultyAccordion">
            <?php foreach ($facultyData as $facultyIndex => $faculty): ?>
                <div class="faculty-card">
                    <!-- แก้ไขส่วน faculty-header -->
                    <div class="faculty-header d-flex justify-content-between align-items-center" id="headingFaculty<?= $facultyIndex ?>" data-bs-toggle="collapse" data-bs-target="#collapseFaculty<?= $facultyIndex ?>" aria-expanded="true" aria-controls="collapseFaculty<?= $facultyIndex ?>">
                        <h4 class="mb-0 toggle-header">
                            คณะ<?= htmlspecialchars($faculty['faculty_name']) ?>
                            <i class="fa-solid fa-chevron-up toggle-icon"></i>
                        </h4>
                        <div>
                            <!-- ปุ่มแก้ไข -->
                            <button class="btn btn-sm btn-primary editFacultyBtn" data-faculty-id="<?= $faculty['faculty_id'] ?>" data-faculty-name="<?= htmlspecialchars($faculty['faculty_name']) ?>" data-faculty-description="<?= htmlspecialchars($faculty['faculty_description']) ?>">
                                <i class="fas fa-edit"></i> แก้ไข
                            </button>
                            <!-- ปุ่มลบ -->
                            <button class="btn btn-sm btn-danger deleteFacultyBtn" data-faculty-id="<?= $faculty['faculty_id'] ?>" data-faculty-name="<?= htmlspecialchars($faculty['faculty_name']) ?>">
                                <i class="fas fa-trash"></i> ลบ
                            </button>
                            <!-- ปุ่มเพิ่มภาควิชาใหม่ -->
                            <button class="btn btn-sm btn-success addDepartmentBtn" data-faculty-id="<?= $faculty['faculty_id'] ?>">
                                <i class="fas fa-plus"></i> เพิ่มภาควิชา
                            </button>
                        </div>
                    </div>

                    <div id="collapseFaculty<?= $facultyIndex ?>" class="collapse show" aria-labelledby="headingFaculty<?= $facultyIndex ?>" data-bs-parent="#facultyAccordion">
                        <div class="card-body">
                            <p><?= nl2br(htmlspecialchars($faculty['faculty_description'])) ?></p>

                            <?php if (!empty($faculty['departments'])): ?>
                                <div class="accordion" id="departmentAccordion<?= $facultyIndex ?>">
                                    <?php foreach ($faculty['departments'] as $deptIndex => $department): ?>
                                        <div class="department-card">
                                            <div class="department-header d-flex justify-content-between align-items-center" id="headingDept<?= $facultyIndex ?><?= $deptIndex ?>" data-bs-toggle="collapse" data-bs-target="#collapseDept<?= $facultyIndex ?><?= $deptIndex ?>" aria-expanded="false" aria-controls="collapseDept<?= $facultyIndex ?><?= $deptIndex ?>">
                                                <h5 class="mb-0 toggle-header">
                                                    ภาควิชา<?= htmlspecialchars($department['department_name']) ?>
                                                    <i class="fa-solid fa-chevron-up toggle-icon"></i>
                                                </h5>
                                                <div>
                                                    <!-- ปุ่มแก้ไขภาควิชา -->
                                                    <button class="btn btn-sm btn-primary editDepartmentBtn" data-department-id="<?= $department['department_id'] ?>" data-department-name="<?= htmlspecialchars($department['department_name']) ?>" data-department-description="<?= htmlspecialchars($department['department_description']) ?>" data-faculty-id="<?= $faculty['faculty_id'] ?>">
                                                        <i class="fas fa-edit"></i> แก้ไข
                                                    </button>
                                                    <!-- ปุ่มลบภาควิชา -->
                                                    <button class="btn btn-sm btn-danger deleteDepartmentBtn" data-department-id="<?= $department['department_id'] ?>" data-department-name="<?= htmlspecialchars($department['department_name']) ?>">
                                                        <i class="fas fa-trash"></i> ลบ
                                                    </button>
                                                    <!-- ปุ่มเพิ่มสาขาวิชาใหม่ -->
                                                    <button class="btn btn-sm btn-success addMajorBtn" data-department-id="<?= $department['department_id'] ?>">
                                                        <i class="fas fa-plus"></i> เพิ่มสาขาวิชา
                                                    </button>
                                                </div>
                                            </div>

                                            <div id="collapseDept<?= $facultyIndex ?><?= $deptIndex ?>" class="collapse" aria-labelledby="headingDept<?= $facultyIndex ?><?= $deptIndex ?>" data-bs-parent="#departmentAccordion<?= $facultyIndex ?>">
                                                <div class="card-body ms-3">
                                                    <p><?= nl2br(htmlspecialchars($department['department_description'])) ?></p>

                                                    <?php if (!empty($department['majors'])): ?>
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
                                                                <?php foreach ($department['majors'] as $major): ?>
                                                                    <tr>
                                                                        <td><?= htmlspecialchars($major['major_id']) ?></td>
                                                                        <td><?= htmlspecialchars($major['major_name']) ?></td>
                                                                        <td><?= nl2br(htmlspecialchars($major['major_description'])) ?></td>
                                                                        <td>
                                                                            <!-- ปุ่มแก้ไขสาขาวิชา -->
                                                                            <button class="btn btn-sm btn-primary editMajorBtn"
                                                                                data-major-id="<?= $major['major_id'] ?>"
                                                                                data-major-name="<?= htmlspecialchars($major['major_name']) ?>"
                                                                                data-major-description="<?= htmlspecialchars($major['major_description']) ?>"
                                                                                data-department-id="<?= $department['department_id'] ?>"
                                                                                data-faculty-id="<?= $faculty['faculty_id'] ?>">
                                                                                <i class="fas fa-edit"></i> แก้ไข
                                                                            </button>

                                                                            <!-- ปุ่มลบสาขาวิชา -->
                                                                            <button class="btn btn-sm btn-danger deleteMajorBtn"
                                                                                data-major-id="<?= $major['major_id'] ?>"
                                                                                data-major-name="<?= htmlspecialchars($major['major_name']) ?>">
                                                                                <i class="fas fa-trash"></i> ลบ
                                                                            </button>
                                                                        </td>
                                                                    </tr>
                                                                <?php endforeach; ?>
                                                            </tbody>
                                                        </table>
                                                    <?php else: ?>
                                                        <p>ไม่มีสาขาวิชา</p>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php else: ?>
                                <p>ไม่มีภาควิชา</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Modal สำหรับเพิ่มคณะใหม่ -->
    <div class="modal fade" id="addFacultyModal" tabindex="-1" aria-labelledby="addFacultyModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <form id="addFacultyForm">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="addFacultyModalLabel">เพิ่มคณะใหม่</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="ปิด"></button>
                    </div>
                    <div class="modal-body">
                        <!-- ชื่อคณะ -->
                        <div class="mb-3">
                            <label for="facultyName" class="form-label">ชื่อคณะ</label>
                            <input type="text" class="form-control" id="facultyName" name="faculty_name" required>
                        </div>
                        <!-- รายละเอียดคณะ -->
                        <div class="mb-3">
                            <label for="facultyDescription" class="form-label">รายละเอียดคณะ</label>
                            <textarea class="form-control" id="facultyDescription" name="faculty_description" rows="3"></textarea>
                        </div>
                        <div id="addFacultyError" class="text-danger"></div>
                        <div id="addFacultySuccess" class="text-success"></div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
                        <button type="submit" class="btn btn-primary">เพิ่มคณะ</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal สำหรับแก้ไขคณะ -->
    <div class="modal fade" id="editFacultyModal" tabindex="-1" aria-labelledby="editFacultyModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <form id="editFacultyForm">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="editFacultyModalLabel">แก้ไขคณะ</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="ปิด"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" id="editFacultyId" name="faculty_id">
                        <!-- ชื่อคณะ -->
                        <div class="mb-3">
                            <label for="editFacultyName" class="form-label">ชื่อคณะ</label>
                            <input type="text" class="form-control" id="editFacultyName" name="faculty_name" required>
                        </div>
                        <!-- รายละเอียดคณะ -->
                        <div class="mb-3">
                            <label for="editFacultyDescription" class="form-label">รายละเอียดคณะ</label>
                            <textarea class="form-control" id="editFacultyDescription" name="faculty_description" rows="3"></textarea>
                        </div>
                        <div id="editFacultyError" class="text-danger"></div>
                        <div id="editFacultySuccess" class="text-success"></div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
                        <button type="submit" class="btn btn-primary">บันทึกการเปลี่ยนแปลง</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal สำหรับยืนยันการลบคณะ -->
    <div class="modal fade" id="deleteFacultyModal" tabindex="-1" aria-labelledby="deleteFacultyModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <form id="deleteFacultyForm">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="deleteFacultyModalLabel">ยืนยันการลบคณะ</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="ปิด"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" id="deleteFacultyId" name="faculty_id">
                        <p>คุณแน่ใจหรือไม่ว่าต้องการลบคณะ <strong id="deleteFacultyName"></strong> นี้?</p>
                        <div id="deleteFacultyError" class="text-danger"></div>
                        <div id="deleteFacultySuccess" class="text-success"></div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
                        <button type="submit" class="btn btn-danger">ลบคณะ</button>
                    </div>
                </div>
            </form>
        </div>
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
                        <input type="hidden" id="addDeptFacultyId" name="faculty_id">
                        <!-- ชื่อภาควิชา -->
                        <div class="mb-3">
                            <label for="departmentName" class="form-label">ชื่อภาควิชา</label>
                            <input type="text" class="form-control" id="departmentName" name="department_name" required>
                        </div>
                        <!-- รายละเอียดภาควิชา -->
                        <div class="mb-3">
                            <label for="departmentDescription" class="form-label">รายละเอียดภาควิชา</label>
                            <textarea class="form-control" id="departmentDescription" name="department_description" rows="3"></textarea>
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
                        <!-- เลือกคณะ (เผื่อมีการย้ายภาควิชาไปยังคณะอื่น) -->
                        <div class="mb-3">
                            <label for="editDepartmentFaculty" class="form-label">สังกัดคณะ</label>
                            <select class="form-select" id="editDepartmentFaculty" name="faculty_id" required>
                                <option value="">เลือกคณะ</option>
                                <?php foreach ($faculties as $fac): ?>
                                    <option value="<?= $fac['faculty_id'] ?>"><?= htmlspecialchars($fac['faculty_name']) ?></option>
                                <?php endforeach; ?>
                            </select>
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
                        <h5 class="modal-title" id="deleteDepartmentModalLabel">ยืนยันการลบภาควิชา</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="ปิด"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" id="deleteDepartmentId" name="department_id">
                        <p>คุณแน่ใจหรือไม่ว่าต้องการลบภาควิชา <strong id="deleteDepartmentName"></strong> นี้?</p>
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
                        <input type="hidden" id="addMajorDepartmentId" name="department_id">
                        <!-- ชื่อสาขาวิชา -->
                        <div class="mb-3">
                            <label for="majorName" class="form-label">ชื่อสาขาวิชา</label>
                            <input type="text" class="form-control" id="majorName" name="major_name" required>
                        </div>
                        <!-- รายละเอียดสาขาวิชา -->
                        <div class="mb-3">
                            <label for="majorDescription" class="form-label">รายละเอียดสาขาวิชา</label>
                            <textarea class="form-control" id="majorDescription" name="major_description" rows="3"></textarea>
                        </div>
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
                            <label for="editMajorDepartment" class="form-label">สังกัดภาควิชา</label>
                            <select class="form-select" id="editMajorDepartment" name="department_id" required>
                                <!-- ตัวเลือกภาควิชาจะถูกโหลดด้วย JavaScript -->
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
                        <h5 class="modal-title" id="deleteMajorModalLabel">ยืนยันการลบสาขาวิชา</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="ปิด"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" id="deleteMajorId" name="major_id">
                        <p>คุณแน่ใจหรือไม่ว่าต้องการลบสาขาวิชา <strong id="deleteMajorName"></strong> นี้?</p>
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
        document.addEventListener('DOMContentLoaded', function() {
            // ตัวแปรสำหรับฟอร์มและ Modal
            const addFacultyForm = document.getElementById('addFacultyForm');
            const addFacultyModal = new bootstrap.Modal(document.getElementById('addFacultyModal'));
            const addFacultyError = document.getElementById('addFacultyError');
            const addFacultySuccess = document.getElementById('addFacultySuccess');

            const editFacultyForm = document.getElementById('editFacultyForm');
            const editFacultyModal = new bootstrap.Modal(document.getElementById('editFacultyModal'));
            const editFacultyError = document.getElementById('editFacultyError');
            const editFacultySuccess = document.getElementById('editFacultySuccess');

            const deleteFacultyForm = document.getElementById('deleteFacultyForm');
            const deleteFacultyModal = new bootstrap.Modal(document.getElementById('deleteFacultyModal'));
            const deleteFacultyError = document.getElementById('deleteFacultyError');
            const deleteFacultySuccess = document.getElementById('deleteFacultySuccess');

            // จัดการการส่งฟอร์มเพิ่มคณะ
            addFacultyForm.addEventListener('submit', function(e) {
                e.preventDefault();
                const formData = new FormData(addFacultyForm);

                fetch('add_faculty.php', {
                        method: 'POST',
                        body: new URLSearchParams(formData)
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            addFacultySuccess.textContent = data.message;
                            addFacultyError.textContent = '';
                            // ปิด Modal และรีเฟรชหน้า
                            setTimeout(() => {
                                addFacultyModal.hide();
                                location.reload();
                            }, 1500);
                        } else {
                            addFacultyError.textContent = data.message || 'เกิดข้อผิดพลาดในการเพิ่มคณะ';
                            addFacultySuccess.textContent = '';
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        addFacultyError.textContent = 'เกิดข้อผิดพลาดในการเชื่อมต่อเซิร์ฟเวอร์';
                        addFacultySuccess.textContent = '';
                    });
            });

            // จัดการเมื่อคลิกปุ่มแก้ไขคณะ
            document.querySelectorAll('.editFacultyBtn').forEach(btn => {
                btn.addEventListener('click', function() {
                    const facultyId = this.getAttribute('data-faculty-id');
                    const facultyName = this.getAttribute('data-faculty-name');
                    const facultyDescription = this.getAttribute('data-faculty-description');

                    // ตั้งค่าในฟอร์ม
                    document.getElementById('editFacultyId').value = facultyId;
                    document.getElementById('editFacultyName').value = facultyName;
                    document.getElementById('editFacultyDescription').value = facultyDescription;

                    editFacultyError.textContent = '';
                    editFacultySuccess.textContent = '';

                    // เปิด Modal
                    editFacultyModal.show();
                });
            });

            // จัดการการส่งฟอร์มแก้ไขคณะ
            editFacultyForm.addEventListener('submit', function(e) {
                e.preventDefault();
                const formData = new FormData(editFacultyForm);

                fetch('edit_faculty.php', {
                        method: 'POST',
                        body: new URLSearchParams(formData)
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            editFacultySuccess.textContent = data.message;
                            editFacultyError.textContent = '';
                            // ปิด Modal และรีเฟรชหน้า
                            setTimeout(() => {
                                editFacultyModal.hide();
                                location.reload();
                            }, 1500);
                        } else {
                            editFacultyError.textContent = data.message || 'เกิดข้อผิดพลาดในการแก้ไขคณะ';
                            editFacultySuccess.textContent = '';
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        editFacultyError.textContent = 'เกิดข้อผิดพลาดในการเชื่อมต่อเซิร์ฟเวอร์';
                        editFacultySuccess.textContent = '';
                    });
            });

            // จัดการเมื่อคลิกปุ่มลบคณะ
            document.querySelectorAll('.deleteFacultyBtn').forEach(btn => {
                btn.addEventListener('click', function() {
                    const facultyId = this.getAttribute('data-faculty-id');
                    const facultyName = this.getAttribute('data-faculty-name');

                    // ตั้งค่าในฟอร์ม
                    document.getElementById('deleteFacultyId').value = facultyId;
                    document.getElementById('deleteFacultyName').textContent = facultyName;

                    deleteFacultyError.textContent = '';
                    deleteFacultySuccess.textContent = '';

                    // เปิด Modal
                    deleteFacultyModal.show();
                });
            });

            // จัดการการส่งฟอร์มลบคณะ
            deleteFacultyForm.addEventListener('submit', function(e) {
                e.preventDefault();
                const formData = new FormData(deleteFacultyForm);

                fetch('delete_faculty.php', {
                        method: 'POST',
                        body: new URLSearchParams(formData)
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            deleteFacultySuccess.textContent = data.message;
                            deleteFacultyError.textContent = '';
                            // ปิด Modal และรีเฟรชหน้า
                            setTimeout(() => {
                                deleteFacultyModal.hide();
                                location.reload();
                            }, 1500);
                        } else {
                            deleteFacultyError.textContent = data.message || 'เกิดข้อผิดพลาดในการลบคณะ';
                            deleteFacultySuccess.textContent = '';
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        deleteFacultyError.textContent = 'เกิดข้อผิดพลาดในการเชื่อมต่อเซิร์ฟเวอร์';
                        deleteFacultySuccess.textContent = '';
                    });
            });
        });
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // ตัวแปรสำหรับฟอร์มและ Modal ของภาควิชา
            const addDepartmentForm = document.getElementById('addDepartmentForm');
            const addDepartmentModal = new bootstrap.Modal(document.getElementById('addDepartmentModal'));
            const addDepartmentError = document.getElementById('addDepartmentError');
            const addDepartmentSuccess = document.getElementById('addDepartmentSuccess');

            const editDepartmentForm = document.getElementById('editDepartmentForm');
            const editDepartmentModal = new bootstrap.Modal(document.getElementById('editDepartmentModal'));
            const editDepartmentError = document.getElementById('editDepartmentError');
            const editDepartmentSuccess = document.getElementById('editDepartmentSuccess');

            const deleteDepartmentForm = document.getElementById('deleteDepartmentForm');
            const deleteDepartmentModal = new bootstrap.Modal(document.getElementById('deleteDepartmentModal'));
            const deleteDepartmentError = document.getElementById('deleteDepartmentError');
            const deleteDepartmentSuccess = document.getElementById('deleteDepartmentSuccess');

            // จัดการเมื่อคลิกปุ่มเพิ่มภาควิชาใหม่
            document.querySelectorAll('.addDepartmentBtn').forEach(btn => {
                btn.addEventListener('click', function() {
                    const facultyId = this.getAttribute('data-faculty-id');
                    document.getElementById('addDeptFacultyId').value = facultyId;

                    // รีเซ็ตฟอร์มและข้อความ
                    addDepartmentForm.reset();
                    addDepartmentError.textContent = '';
                    addDepartmentSuccess.textContent = '';

                    // เปิด Modal
                    addDepartmentModal.show();
                });
            });

            // จัดการการส่งฟอร์มเพิ่มภาควิชา
            addDepartmentForm.addEventListener('submit', function(e) {
                e.preventDefault();
                const formData = new FormData(addDepartmentForm);

                fetch('add_department.php', {
                        method: 'POST',
                        body: new URLSearchParams(formData)
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            addDepartmentSuccess.textContent = data.message;
                            addDepartmentError.textContent = '';
                            // ปิด Modal และรีเฟรชหน้า
                            setTimeout(() => {
                                addDepartmentModal.hide();
                                location.reload();
                            }, 1500);
                        } else {
                            addDepartmentError.textContent = data.message || 'เกิดข้อผิดพลาดในการเพิ่มภาควิชา';
                            addDepartmentSuccess.textContent = '';
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        addDepartmentError.textContent = 'เกิดข้อผิดพลาดในการเชื่อมต่อเซิร์ฟเวอร์';
                        addDepartmentSuccess.textContent = '';
                    });
            });

            // จัดการเมื่อคลิกปุ่มแก้ไขภาควิชา
            document.querySelectorAll('.editDepartmentBtn').forEach(btn => {
                btn.addEventListener('click', function() {
                    const departmentId = this.getAttribute('data-department-id');
                    const departmentName = this.getAttribute('data-department-name');
                    const departmentDescription = this.getAttribute('data-department-description');
                    const facultyId = this.getAttribute('data-faculty-id');

                    // ตั้งค่าในฟอร์ม
                    document.getElementById('editDepartmentId').value = departmentId;
                    document.getElementById('editDepartmentName').value = departmentName;
                    document.getElementById('editDepartmentDescription').value = departmentDescription;
                    document.getElementById('editDepartmentFaculty').value = facultyId;

                    editDepartmentError.textContent = '';
                    editDepartmentSuccess.textContent = '';

                    // เปิด Modal
                    editDepartmentModal.show();
                });
            });

            // จัดการการส่งฟอร์มแก้ไขภาควิชา
            editDepartmentForm.addEventListener('submit', function(e) {
                e.preventDefault();
                const formData = new FormData(editDepartmentForm);

                fetch('edit_department.php', {
                        method: 'POST',
                        body: new URLSearchParams(formData)
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            editDepartmentSuccess.textContent = data.message;
                            editDepartmentError.textContent = '';
                            // ปิด Modal และรีเฟรชหน้า
                            setTimeout(() => {
                                editDepartmentModal.hide();
                                location.reload();
                            }, 1500);
                        } else {
                            editDepartmentError.textContent = data.message || 'เกิดข้อผิดพลาดในการแก้ไขภาควิชา';
                            editDepartmentSuccess.textContent = '';
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        editDepartmentError.textContent = 'เกิดข้อผิดพลาดในการเชื่อมต่อเซิร์ฟเวอร์';
                        editDepartmentSuccess.textContent = '';
                    });
            });

            // จัดการเมื่อคลิกปุ่มลบภาควิชา
            document.querySelectorAll('.deleteDepartmentBtn').forEach(btn => {
                btn.addEventListener('click', function() {
                    const departmentId = this.getAttribute('data-department-id');
                    const departmentName = this.getAttribute('data-department-name');

                    // ตั้งค่าในฟอร์ม
                    document.getElementById('deleteDepartmentId').value = departmentId;
                    document.getElementById('deleteDepartmentName').textContent = departmentName;

                    deleteDepartmentError.textContent = '';
                    deleteDepartmentSuccess.textContent = '';

                    // เปิด Modal
                    deleteDepartmentModal.show();
                });
            });

            // จัดการการส่งฟอร์มลบภาควิชา
            deleteDepartmentForm.addEventListener('submit', function(e) {
                e.preventDefault();
                const formData = new FormData(deleteDepartmentForm);

                fetch('delete_department.php', {
                        method: 'POST',
                        body: new URLSearchParams(formData)
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            deleteDepartmentSuccess.textContent = data.message;
                            deleteDepartmentError.textContent = '';
                            // ปิด Modal และรีเฟรชหน้า
                            setTimeout(() => {
                                deleteDepartmentModal.hide();
                                location.reload();
                            }, 1500);
                        } else {
                            deleteDepartmentError.textContent = data.message || 'เกิดข้อผิดพลาดในการลบภาควิชา';
                            deleteDepartmentSuccess.textContent = '';
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        deleteDepartmentError.textContent = 'เกิดข้อผิดพลาดในการเชื่อมต่อเซิร์ฟเวอร์';
                        deleteDepartmentSuccess.textContent = '';
                    });
            });
        });
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // ตัวแปรสำหรับฟอร์มและ Modal ของสาขาวิชา
            const addMajorForm = document.getElementById('addMajorForm');
            const addMajorModal = new bootstrap.Modal(document.getElementById('addMajorModal'));
            const addMajorError = document.getElementById('addMajorError');
            const addMajorSuccess = document.getElementById('addMajorSuccess');

            const editMajorForm = document.getElementById('editMajorForm');
            const editMajorModal = new bootstrap.Modal(document.getElementById('editMajorModal'));
            const editMajorError = document.getElementById('editMajorError');
            const editMajorSuccess = document.getElementById('editMajorSuccess');

            const deleteMajorForm = document.getElementById('deleteMajorForm');
            const deleteMajorModal = new bootstrap.Modal(document.getElementById('deleteMajorModal'));
            const deleteMajorError = document.getElementById('deleteMajorError');
            const deleteMajorSuccess = document.getElementById('deleteMajorSuccess');

            // จัดการเมื่อคลิกปุ่มเพิ่มสาขาวิชาใหม่
            document.querySelectorAll('.addMajorBtn').forEach(btn => {
                btn.addEventListener('click', function() {
                    const departmentId = this.getAttribute('data-department-id');
                    document.getElementById('addMajorDepartmentId').value = departmentId;

                    // รีเซ็ตฟอร์มและข้อความ
                    addMajorForm.reset();
                    addMajorError.textContent = '';
                    addMajorSuccess.textContent = '';

                    // เปิด Modal
                    addMajorModal.show();
                });
            });

            // จัดการการส่งฟอร์มเพิ่มสาขาวิชา
            addMajorForm.addEventListener('submit', function(e) {
                e.preventDefault();
                const formData = new FormData(addMajorForm);

                fetch('add_major.php', {
                        method: 'POST',
                        body: new URLSearchParams(formData)
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            addMajorSuccess.textContent = data.message;
                            addMajorError.textContent = '';
                            // ปิด Modal และรีเฟรชหน้า
                            setTimeout(() => {
                                addMajorModal.hide();
                                location.reload();
                            }, 1500);
                        } else {
                            addMajorError.textContent = data.message || 'เกิดข้อผิดพลาดในการเพิ่มสาขาวิชา';
                            addMajorSuccess.textContent = '';
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        addMajorError.textContent = 'เกิดข้อผิดพลาดในการเชื่อมต่อเซิร์ฟเวอร์';
                        addMajorSuccess.textContent = '';
                    });
            });

            // จัดการเมื่อคลิกปุ่มแก้ไขสาขาวิชา
            document.querySelectorAll('.editMajorBtn').forEach(btn => {
                btn.addEventListener('click', function() {
                    const majorId = this.getAttribute('data-major-id');
                    const majorName = this.getAttribute('data-major-name');
                    const majorDescription = this.getAttribute('data-major-description');
                    const departmentId = this.getAttribute('data-department-id');
                    const facultyId = this.getAttribute('data-faculty-id');

                    // ตั้งค่าในฟอร์ม
                    document.getElementById('editMajorId').value = majorId;
                    document.getElementById('editMajorName').value = majorName;
                    document.getElementById('editMajorDescription').value = majorDescription;

                    // ล้างตัวเลือกเดิม
                    const departmentSelect = document.getElementById('editMajorDepartment');
                    departmentSelect.innerHTML = '';

                    // โหลดภาควิชาที่อยู่ในคณะเดียวกัน
                    fetch('get_departments_by_faculty.php?faculty_id=' + facultyId)
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                // เพิ่มตัวเลือกภาควิชา
                                data.departments.forEach(dept => {
                                    const option = document.createElement('option');
                                    option.value = dept.department_id;
                                    option.textContent = dept.department_name;
                                    if (parseInt(dept.department_id) === parseInt(departmentId)) {
                                        option.selected = true; // เลือกภาควิชาปัจจุบัน
                                    }
                                    departmentSelect.appendChild(option);
                                });

                                // เปิด Modal
                                editMajorModal.show();
                            } else {
                                alert('เกิดข้อผิดพลาดในการโหลดภาควิชา');
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            alert('เกิดข้อผิดพลาดในการเชื่อมต่อเซิร์ฟเวอร์');
                        });
                });
            });



            // จัดการการส่งฟอร์มแก้ไขสาขาวิชา
            editMajorForm.addEventListener('submit', function(e) {
                e.preventDefault();
                const formData = new FormData(editMajorForm);

                fetch('edit_major.php', {
                        method: 'POST',
                        body: new URLSearchParams(formData)
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            editMajorSuccess.textContent = data.message;
                            editMajorError.textContent = '';
                            // ปิด Modal และรีเฟรชหน้า
                            setTimeout(() => {
                                editMajorModal.hide();
                                location.reload();
                            }, 1500);
                        } else {
                            editMajorError.textContent = data.message || 'เกิดข้อผิดพลาดในการแก้ไขสาขาวิชา';
                            editMajorSuccess.textContent = '';
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        editMajorError.textContent = 'เกิดข้อผิดพลาดในการเชื่อมต่อเซิร์ฟเวอร์';
                        editMajorSuccess.textContent = '';
                    });
            });

            // จัดการเมื่อคลิกปุ่มลบสาขาวิชา
            document.querySelectorAll('.deleteMajorBtn').forEach(btn => {
                btn.addEventListener('click', function() {
                    const majorId = this.getAttribute('data-major-id');
                    const majorName = this.getAttribute('data-major-name');

                    // ตั้งค่าในฟอร์ม
                    document.getElementById('deleteMajorId').value = majorId;
                    document.getElementById('deleteMajorName').textContent = majorName;

                    deleteMajorError.textContent = '';
                    deleteMajorSuccess.textContent = '';

                    // เปิด Modal
                    deleteMajorModal.show();
                });
            });

            // จัดการการส่งฟอร์มลบสาขาวิชา
            deleteMajorForm.addEventListener('submit', function(e) {
                e.preventDefault();
                const formData = new FormData(deleteMajorForm);

                fetch('delete_major.php', {
                        method: 'POST',
                        body: new URLSearchParams(formData)
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            deleteMajorSuccess.textContent = data.message;
                            deleteMajorError.textContent = '';
                            // ปิด Modal และรีเฟรชหน้า
                            setTimeout(() => {
                                deleteMajorModal.hide();
                                location.reload();
                            }, 1500);
                        } else {
                            deleteMajorError.textContent = data.message || 'เกิดข้อผิดพลาดในการลบสาขาวิชา';
                            deleteMajorSuccess.textContent = '';
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        deleteMajorError.textContent = 'เกิดข้อผิดพลาดในการเชื่อมต่อเซิร์ฟเวอร์';
                        deleteMajorSuccess.textContent = '';
                    });
            });
        });
    </script>


</body>

</html>