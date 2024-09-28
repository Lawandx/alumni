<?php
session_start();

// ตรวจสอบว่าผู้ใช้ได้เข้าสู่ระบบแล้วหรือยัง และมีสิทธิ์เป็น admin หรือไม่
if (!isset($_SESSION['user_id']) || $_SESSION['access_level'] !== 'admin') {
  header("Location: login.php");
  exit();
}

require 'db_connect.php'; // เรียกใช้ไฟล์เชื่อมต่อฐานข้อมูล

// ดึงข้อมูลผู้ใช้ทั้งหมด โดยไม่รวม password_hash
$stmt = $pdo->prepare("
    SELECT 
        u.user_id, 
        u.username, 
        u.email, 
        u.password_part,
        u.access_level,
        f.faculty_name, 
        d.department_name, 
        m.major_name,
        u.faculty_id,
        u.department_id,
        u.major_id
    FROM user u
    LEFT JOIN faculty f ON u.faculty_id = f.faculty_id
    LEFT JOIN department d ON u.department_id = d.department_id
    LEFT JOIN major m ON u.major_id = m.major_id
    ORDER BY u.access_level ASC, u.user_id ASC
");
$stmt->execute();
$all_users = $stmt->fetchAll(PDO::FETCH_ASSOC);

// แยกผู้ใช้ตาม access_level
$users_by_access = [];
foreach ($all_users as $user) {
  $access_level = $user['access_level'];
  if (!isset($users_by_access[$access_level])) {
    $users_by_access[$access_level] = [];
  }
  $users_by_access[$access_level][] = $user;
}

// ดึงข้อมูลคณะทั้งหมด
$stmt_faculty = $pdo->prepare("SELECT faculty_id, faculty_name FROM faculty ORDER BY faculty_name ASC");
$stmt_faculty->execute();
$faculties = $stmt_faculty->fetchAll(PDO::FETCH_ASSOC);

// ดึงข้อมูลภาควิชาและสาขาวิชา
$stmt_department = $pdo->prepare("SELECT department_id, department_name, faculty_id FROM department ORDER BY department_name ASC");
$stmt_department->execute();
$departments = $stmt_department->fetchAll(PDO::FETCH_ASSOC);

$stmt_major = $pdo->prepare("SELECT major_id, major_name, department_id FROM major ORDER BY major_name ASC");
$stmt_major->execute();
$majors = $stmt_major->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="th">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>พนักงาน</title>
  <!-- Bootstrap 5 CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Google Fonts (Sarabun) -->
  <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@400;500;600&display=swap" rel="stylesheet">
  <!-- Font Awesome for Icons -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <style>
    body {
      font-family: "Sarabun", sans-serif;
    }
  </style>
</head>

<body>
  <?php include 'navbar.php'; ?>

  <div class="container mt-5">
    <h3 class="mb-4">ข้อมูลพนักงานทั้งหมด</h3>

    <!-- เพิ่มปุ่ม "เพิ่มพนักงาน" -->
    <button class="btn btn-success mb-3" data-bs-toggle="modal" data-bs-target="#addEmployeeModal">
      <i class="fas fa-plus"></i> เพิ่มพนักงาน
    </button>

    <?php foreach ($users_by_access as $access_level => $users): ?>
      <div class="card mb-4">
        <div class="card-header bg-secondary text-white">
          <i class="fas fa-users"></i> รายชื่อพนักงาน - <?= htmlspecialchars(ucfirst($access_level)) ?>
        </div>
        <div class="card-body">
          <table class="table table-striped table-bordered">
            <thead class="table-light">
              <tr>
                <th>รหัสผู้ใช้</th>
                <th>ชื่อผู้ใช้</th>
                <th>Email</th>
                <th>รหัส</th>
                <th>ระดับการเข้าถึง</th>
                <th>คณะ</th>
                <th>ภาควิชา</th>
                <th>สาขาวิชา</th>
                <?php if ($access_level !== 'admin'): ?>
                  <th>จัดการ</th>
                <?php endif; ?>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($users as $user): ?>
                <tr>
                  <td><?= htmlspecialchars($user['user_id']) ?></td>
                  <td><?= htmlspecialchars($user['username']) ?></td>
                  <td><?= htmlspecialchars($user['email']) ?></td>
                  <td><?= str_repeat('*', strlen($user['password_part'])) ?></td>
                  <td><?= htmlspecialchars($user['access_level']) ?></td>
                  <td><?= htmlspecialchars($user['faculty_name']) ?></td>
                  <td><?= htmlspecialchars($user['department_name']) ?></td>
                  <td><?= htmlspecialchars($user['major_name']) ?></td>
                  <?php if ($access_level !== 'admin'): ?>
                    <td>
                      <button class="btn btn-sm btn-primary editUserBtn"
                        data-user-id="<?= $user['user_id'] ?>"
                        data-username="<?= htmlspecialchars($user['username']) ?>"
                        data-email="<?= htmlspecialchars($user['email']) ?>"
                        data-faculty-id="<?= htmlspecialchars($user['faculty_id']) ?>"
                        data-department-id="<?= htmlspecialchars($user['department_id']) ?>"
                        data-major-id="<?= htmlspecialchars($user['major_id']) ?>"
                        data-access-level="<?= htmlspecialchars($user['access_level']) ?>"
                        data-faculty-name="<?= htmlspecialchars($user['faculty_name']) ?>"
                        data-department-name="<?= htmlspecialchars($user['department_name']) ?>"
                        data-major-name="<?= htmlspecialchars($user['major_name']) ?>">
                        <i class="fas fa-edit"></i> แก้ไข
                      </button>
                      <!-- เพิ่มปุ่ม "ลบ" -->
                      <button class="btn btn-sm btn-danger deleteUserBtn"
                        data-user-id="<?= $user['user_id'] ?>"
                        data-username="<?= htmlspecialchars($user['username']) ?>">
                        <i class="fas fa-trash"></i> ลบ
                      </button>
                    </td>
                  <?php endif; ?>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
    <?php endforeach; ?>


    <!-- Modal สำหรับเพิ่มพนักงานใหม่ -->
    <div class="modal fade" id="addEmployeeModal" tabindex="-1" aria-labelledby="addEmployeeModalLabel" aria-hidden="true">
      <div class="modal-dialog">
        <form id="addEmployeeForm">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title" id="addEmployeeModalLabel">เพิ่มพนักงานใหม่</h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="ปิด"></button>
            </div>
            <div class="modal-body">
              <!-- ชื่อผู้ใช้ -->
              <div class="mb-3">
                <label class="form-label">ชื่อผู้ใช้</label>
                <input type="text" class="form-control" id="addUsername" name="username" required>
              </div>
              <!-- Email -->
              <div class="mb-3">
                <label for="addEmail" class="form-label">Email</label>
                <input type="email" class="form-control" id="addEmail" name="email" required>
              </div>
              <!-- รหัสผ่าน -->
              <div class="mb-3">
                <label for="addPassword" class="form-label">รหัสผ่าน</label>
                <input type="password" class="form-control" id="addPassword" name="password" required>
              </div>
              <!-- ยืนยันรหัสผ่าน -->
              <div class="mb-3">
                <label for="addConfirmPassword" class="form-label">ยืนยันรหัสผ่าน</label>
                <input type="password" class="form-control" id="addConfirmPassword" name="confirm_password" required>
              </div>
              <!-- ระดับการเข้าถึง -->
              <div class="mb-3">
                <label for="addAccessLevel" class="form-label">ระดับการเข้าถึง</label>
                <select class="form-select" id="addAccessLevel" name="access_level" required>
                  <option value="">เลือกระดับการเข้าถึง</option>
                  <option value="faculty">Faculty</option>
                  <option value="department">Department</option>
                  <option value="major">Major</option>
                </select>
              </div>
              <!-- เลือกคณะ -->
              <div class="mb-3">
                <label for="addFaculty" class="form-label">คณะ</label>
                <select class="form-select" id="addFaculty" name="faculty_id" required>
                  <option value="">เลือกคณะ</option>
                  <?php foreach ($faculties as $faculty): ?>
                    <option value="<?= htmlspecialchars($faculty['faculty_id']) ?>"><?= htmlspecialchars($faculty['faculty_name']) ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
              <!-- เลือกภาควิชา -->
              <div class="mb-3">
                <label for="addDepartment" class="form-label">ภาควิชา</label>
                <select class="form-select" id="addDepartment" name="department_id">
                  <option value="">เลือกภาควิชา</option>
                  <!-- Options จะถูกเติมโดย JavaScript ตามคณะที่เลือก -->
                </select>
              </div>
              <!-- เลือกสาขาวิชา -->
              <div class="mb-3">
                <label for="addMajor" class="form-label">สาขาวิชา</label>
                <select class="form-select" id="addMajor" name="major_id">
                  <option value="">เลือกสาขาวิชา</option>
                  <!-- Options จะถูกเติมโดย JavaScript ตามภาควิชาที่เลือก -->
                </select>
              </div>
              <div id="addEmployeeError" class="text-danger"></div>
              <div id="addEmployeeSuccess" class="text-success"></div>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
              <button type="submit" class="btn btn-primary">เพิ่มพนักงาน</button>
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
              <input type="hidden" id="editUserId" name="user_id">
              <!-- แสดงชื่อผู้ใช้ -->
              <div class="mb-3">
                <label class="form-label">ชื่อผู้ใช้</label>
                <input type="text" class="form-control" id="editUsername" name="username" readonly>
              </div>
              <!-- แก้ไข Email -->
              <div class="mb-3">
                <label for="editEmail" class="form-label">Email</label>
                <input type="email" class="form-control" id="editEmail" name="email" required>
              </div>
              <!-- เลือกระดับการเข้าถึง -->
              <div class="mb-3">
                <label for="editAccessLevel" class="form-label">ระดับการเข้าถึง</label>
                <select class="form-select" id="editAccessLevel" name="access_level" required>
                  <option value="">เลือกระดับการเข้าถึง</option>
                  <option value="faculty">Faculty</option>
                  <option value="department">Department</option>
                  <option value="major">Major</option>
                </select>
              </div>
              <!-- เลือกคณะ -->
              <div class="mb-3">
                <label for="editFaculty" class="form-label">คณะ</label>
                <select class="form-select" id="editFaculty" name="faculty_id">
                  <option value="">เลือกคณะ</option>
                  <?php foreach ($faculties as $faculty): ?>
                    <option value="<?= htmlspecialchars($faculty['faculty_id']) ?>"><?= htmlspecialchars($faculty['faculty_name']) ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
              <!-- เลือกภาควิชา -->
              <div class="mb-3">
                <label for="editDepartment" class="form-label">ภาควิชา</label>
                <select class="form-select" id="editDepartment" name="department_id">
                  <option value="">เลือกภาควิชา</option>
                  <!-- Options จะถูกเติมโดย JavaScript ตามคณะที่เลือก -->
                </select>
              </div>
              <!-- เลือกสาขาวิชา -->
              <div class="mb-3">
                <label for="editMajor" class="form-label">สาขาวิชา</label>
                <select class="form-select" id="editMajor" name="major_id">
                  <option value="">เลือกสาขาวิชา</option>
                  <!-- Options จะถูกเติมโดย JavaScript ตามภาควิชาที่เลือก -->
                </select>
              </div>

              <!-- เปลี่ยนรหัสผ่าน -->
              <hr>
              <h5>เปลี่ยนรหัสผ่าน</h5>
              <div class="mb-3">
                <label for="editNewPassword" class="form-label">รหัสผ่านใหม่</label>
                <input type="password" class="form-control" id="editNewPassword" name="new_password">
              </div>
              <div class="mb-3">
                <label for="editConfirmNewPassword" class="form-label">ยืนยันรหัสผ่านใหม่</label>
                <input type="password" class="form-control" id="editConfirmNewPassword" name="confirm_new_password">
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
    <!-- Modal สำหรับยืนยันการลบผู้ใช้ -->
    <div class="modal fade" id="deleteUserModal" tabindex="-1" aria-labelledby="deleteUserModalLabel" aria-hidden="true">
      <div class="modal-dialog">
        <form id="deleteUserForm">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title" id="deleteUserModalLabel">ยืนยันการลบผู้ใช้</h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="ปิด"></button>
            </div>
            <div class="modal-body">
              <input type="hidden" id="deleteUserId" name="user_id">
              <p>คุณแน่ใจหรือไม่ว่าต้องการลบผู้ใช้ <strong id="deleteUsername"></strong> นี้?</p>
              <div id="deleteUserError" class="text-danger"></div>
              <div id="deleteUserSuccess" class="text-success"></div>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
              <button type="submit" class="btn btn-danger">ลบผู้ใช้</button>
            </div>
          </div>
        </form>
      </div>
    </div>

  </div>


  <!-- Bootstrap 5 JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <!-- jQuery (ต้องเพิ่มก่อนสคริปต์ที่ใช้ $) -->
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

  <script>
    // กำหนดค่าตัวแปรสำหรับพนักงานใหม่
    const faculties = <?= json_encode($faculties) ?>;
    const departments = <?= json_encode($departments) ?>;
    const majors = <?= json_encode($majors) ?>;
    // JavaScript สำหรับเพิ่มพนักงานใหม่
    const addEmployeeForm = document.getElementById('addEmployeeForm');
    const addEmployeeError = document.getElementById('addEmployeeError');
    const addEmployeeSuccess = document.getElementById('addEmployeeSuccess');

    // ฟังก์ชันเพื่อเติมข้อมูลภาควิชาตามคณะที่เลือกใน Modal เพิ่มพนักงาน
    function populateAddDepartments(selectedFacultyId) {
      const addDepartment = document.getElementById('addDepartment');
      addDepartment.innerHTML = '<option value="">เลือกภาควิชา</option>';
      departments.forEach(dept => {
        if (dept.faculty_id == selectedFacultyId) {
          addDepartment.innerHTML += `<option value="${dept.department_id}">${dept.department_name}</option>`;
        }
      });
    }

    // ฟังก์ชันเพื่อเติมข้อมูลสาขาวิชาตามภาควิชาที่เลือกใน Modal เพิ่มพนักงาน
    function populateAddMajors(selectedDepartmentId) {
      const addMajor = document.getElementById('addMajor');
      addMajor.innerHTML = '<option value="">เลือกสาขาวิชา</option>';
      majors.forEach(maj => {
        if (maj.department_id == selectedDepartmentId) {
          addMajor.innerHTML += `<option value="${maj.major_id}">${maj.major_name}</option>`;
        }
      });
    }

    // ฟังก์ชันเพื่อกำหนดความจำเป็นของฟิลด์ตาม access_level ใน Modal เพิ่มพนักงาน
    function setAddFieldRequirements(accessLevel) {
      const addFaculty = document.getElementById('addFaculty');
      const addDepartment = document.getElementById('addDepartment');
      const addMajor = document.getElementById('addMajor');

      if (accessLevel === 'faculty') {
        addFaculty.required = true;
        addDepartment.required = false;
        addMajor.required = false;
        addDepartment.disabled = true;
        addMajor.disabled = true;
      } else if (accessLevel === 'department') {
        addFaculty.required = true;
        addDepartment.required = true;
        addMajor.required = false;
        addDepartment.disabled = false;
        addMajor.disabled = true;
      } else if (accessLevel === 'major') {
        addFaculty.required = true;
        addDepartment.required = true;
        addMajor.required = true;
        addDepartment.disabled = false;
        addMajor.disabled = false;
      } else {
        // สำหรับ access_level อื่น ๆ ถ้ามี
        addFaculty.required = false;
        addDepartment.required = false;
        addMajor.required = false;
        addDepartment.disabled = true;
        addMajor.disabled = true;
      }
    }

    // เมื่อเลือกคณะใน Modal เพิ่มพนักงาน
    document.getElementById('addFaculty').addEventListener('change', function() {
      const selectedFacultyId = this.value;
      populateAddDepartments(selectedFacultyId);
      document.getElementById('addMajor').innerHTML = '<option value="">เลือกสาขาวิชา</option>';

      // ตั้งค่าความจำเป็นของฟิลด์ตาม access_level ที่เลือก
      const selectedAccessLevel = document.getElementById('addAccessLevel').value;
      setAddFieldRequirements(selectedAccessLevel);
    });

    // เมื่อเลือกภาควิชาใน Modal เพิ่มพนักงาน
    document.getElementById('addDepartment').addEventListener('change', function() {
      const selectedDepartmentId = this.value;
      populateAddMajors(selectedDepartmentId);

      // ตั้งค่าความจำเป็นของฟิลด์ตาม access_level ที่เลือก
      const selectedAccessLevel = document.getElementById('addAccessLevel').value;
      setAddFieldRequirements(selectedAccessLevel);
    });

    // เมื่อเลือกระดับการเข้าถึงใน Modal เพิ่มพนักงาน
    document.getElementById('addAccessLevel').addEventListener('change', function() {
      const selectedAccessLevel = this.value;
      setAddFieldRequirements(selectedAccessLevel);

      // รีเซ็ตฟิลด์ที่ไม่จำเป็น
      if (selectedAccessLevel === 'faculty') {
        document.getElementById('addDepartment').value = '';
        document.getElementById('addMajor').value = '';
        document.getElementById('addDepartment').disabled = true;
        document.getElementById('addMajor').disabled = true;
      } else if (selectedAccessLevel === 'department') {
        document.getElementById('addMajor').value = '';
        document.getElementById('addMajor').disabled = true;
      } else if (selectedAccessLevel === 'major') {
        document.getElementById('addDepartment').disabled = false;
        document.getElementById('addMajor').disabled = false;
      } else {
        document.getElementById('addDepartment').value = '';
        document.getElementById('addMajor').value = '';
        document.getElementById('addDepartment').disabled = true;
        document.getElementById('addMajor').disabled = true;
      }
    });

    // จัดการการส่งฟอร์มเพิ่มพนักงาน
    addEmployeeForm.addEventListener('submit', function(e) {
      e.preventDefault();
      const formData = new FormData(addEmployeeForm);

      // ตรวจสอบรหัสผ่านใหม่และยืนยันรหัสผ่านใหม่
      const password = formData.get('password');
      const confirmPassword = formData.get('confirm_password');
      if (password !== confirmPassword) {
        addEmployeeError.textContent = 'รหัสผ่านและยืนยันรหัสผ่านไม่ตรงกัน';
        addEmployeeSuccess.textContent = '';
        return;
      }

      // ส่งข้อมูลผ่าน AJAX ไปยัง add_employee.php
      fetch('add_employee.php', {
          method: 'POST',
          body: new URLSearchParams(formData)
        })
        .then(response => response.json())
        .then(data => {
          if (data.success) {
            addEmployeeSuccess.textContent = 'เพิ่มพนักงานสำเร็จ';
            addEmployeeError.textContent = '';
            // ปิด Modal หลังจากเพิ่มสำเร็จ
            setTimeout(() => {
              $('#addEmployeeModal').modal('hide');
              location.reload(); // รีเฟรชหน้าเพื่อแสดงข้อมูลที่เพิ่มใหม่
            }, 1500);
          } else {
            addEmployeeError.textContent = data.message || 'เกิดข้อผิดพลาดในการเพิ่มพนักงาน';
            addEmployeeSuccess.textContent = '';
          }
        })
        .catch(error => {
          console.error('Error:', error);
          addEmployeeError.textContent = 'เกิดข้อผิดพลาดในการเชื่อมต่อเซิร์ฟเวอร์';
          addEmployeeSuccess.textContent = '';
        });
    });

    // JavaScript สำหรับการแก้ไขข้อมูลผู้ใช้
    document.addEventListener('DOMContentLoaded', function() {
      const editUserBtns = document.querySelectorAll('.editUserBtn');
      const editUserModal = new bootstrap.Modal(document.getElementById('editUserModal'), {
        keyboard: false
      });
      const editUserForm = document.getElementById('editUserForm');
      const editUserError = document.getElementById('editUserError');
      const editUserSuccess = document.getElementById('editUserSuccess');


      // ฟังก์ชันเพื่อเติมข้อมูลภาควิชาตามคณะที่เลือก
      function populateDepartments(selectedFacultyId) {
        const editDepartment = document.getElementById('editDepartment');
        editDepartment.innerHTML = '<option value="">เลือกภาควิชา</option>';
        departments.forEach(dept => {
          if (dept.faculty_id == selectedFacultyId) {
            editDepartment.innerHTML += `<option value="${dept.department_id}">${dept.department_name}</option>`;
          }
        });
      }

      // ฟังก์ชันเพื่อเติมข้อมูลสาขาวิชาตามภาควิชาที่เลือก
      function populateMajors(selectedDepartmentId) {
        const editMajor = document.getElementById('editMajor');
        editMajor.innerHTML = '<option value="">เลือกสาขาวิชา</option>';
        majors.forEach(maj => {
          if (maj.department_id == selectedDepartmentId) {
            editMajor.innerHTML += `<option value="${maj.major_id}">${maj.major_name}</option>`;
          }
        });
      }

      // ฟังก์ชันเพื่อกำหนดความจำเป็นของฟิลด์ตาม access_level
      function setFieldRequirements(accessLevel) {
        const editFaculty = document.getElementById('editFaculty');
        const editDepartment = document.getElementById('editDepartment');
        const editMajor = document.getElementById('editMajor');

        if (accessLevel === 'faculty') {
          editFaculty.required = true;
          editDepartment.required = false;
          editMajor.required = false;
        } else if (accessLevel === 'department') {
          editFaculty.required = true;
          editDepartment.required = true;
          editMajor.required = false;
        } else if (accessLevel === 'major') {
          editFaculty.required = true;
          editDepartment.required = true;
          editMajor.required = true;
        } else {
          // สำหรับ access_level อื่น ๆ ถ้ามี
          editFaculty.required = false;
          editDepartment.required = false;
          editMajor.required = false;
        }
      }

      editUserBtns.forEach(btn => {
        btn.addEventListener('click', function() {
          const userId = this.getAttribute('data-user-id');
          const username = this.getAttribute('data-username');
          const email = this.getAttribute('data-email');
          const facultyId = this.getAttribute('data-faculty-id');
          const departmentId = this.getAttribute('data-department-id');
          const majorId = this.getAttribute('data-major-id');
          const accessLevel = this.getAttribute('data-access-level');

          // ตั้งค่าในฟอร์ม
          document.getElementById('editUserId').value = userId;
          document.getElementById('editUsername').value = username;
          document.getElementById('editEmail').value = email;
          document.getElementById('editAccessLevel').value = accessLevel;
          document.getElementById('editFaculty').value = facultyId;

          // เติมข้อมูลภาควิชาและสาขาวิชา
          populateDepartments(facultyId);
          document.getElementById('editDepartment').value = departmentId;
          populateMajors(departmentId);
          document.getElementById('editMajor').value = majorId;

          // ตั้งค่า required ของฟิลด์ตาม access_level
          setFieldRequirements(accessLevel);

          // รีเซ็ตรหัสผ่านใหม่และยืนยันรหัสผ่านใหม่
          document.getElementById('editNewPassword').value = '';
          document.getElementById('editConfirmNewPassword').value = '';
          editUserError.textContent = '';
          editUserSuccess.textContent = '';

          // เปิด Modal
          editUserModal.show();
        });
      });

      // เมื่อเลือกคณะใน Modal
      document.getElementById('editFaculty').addEventListener('change', function() {
        const selectedFacultyId = this.value;
        populateDepartments(selectedFacultyId);
        document.getElementById('editMajor').innerHTML = '<option value="">เลือกสาขาวิชา</option>';
      });

      // เมื่อเลือกภาควิชาใน Modal
      document.getElementById('editDepartment').addEventListener('change', function() {
        const selectedDepartmentId = this.value;
        populateMajors(selectedDepartmentId);
      });

      // เมื่อเลือกระดับการเข้าถึงใน Modal
      document.getElementById('editAccessLevel').addEventListener('change', function() {
        const selectedAccessLevel = this.value;
        setFieldRequirements(selectedAccessLevel);

        // รีเซ็ตฟิลด์ที่ไม่จำเป็น
        if (selectedAccessLevel === 'faculty') {
          document.getElementById('editDepartment').value = '';
          document.getElementById('editMajor').value = '';
          document.getElementById('editDepartment').required = false;
          document.getElementById('editMajor').required = false;
        } else if (selectedAccessLevel === 'department') {
          document.getElementById('editMajor').value = '';
          document.getElementById('editMajor').required = false;
        }
      });

      editUserForm.addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(editUserForm);

        // ตรวจสอบรหัสผ่านใหม่และยืนยันรหัสผ่านใหม่ (ถ้ามีการเปลี่ยนรหัสผ่าน)
        const newPassword = formData.get('new_password');
        const confirmNewPassword = formData.get('confirm_new_password');
        if (newPassword || confirmNewPassword) {
          if (newPassword !== confirmNewPassword) {
            editUserError.textContent = 'รหัสผ่านใหม่และรหัสผ่านยืนยันไม่ตรงกัน';
            editUserSuccess.textContent = '';
            return;
          }
        }

        // ส่งข้อมูลผ่าน AJAX ไปยัง update_employee.php
        fetch('update_employee.php', {
            method: 'POST',
            body: new URLSearchParams(formData)
          })
          .then(response => response.json())
          .then(data => {
            if (data.success) {
              editUserSuccess.textContent = 'แก้ไขข้อมูลสำเร็จ';
              editUserError.textContent = '';
              // ปิด Modal หลังจากแก้ไขสำเร็จ
              setTimeout(() => {
                editUserModal.hide();
                location.reload(); // รีเฟรชหน้าเพื่ออัปเดตข้อมูล
              }, 1500);
            } else {
              editUserError.textContent = data.message || 'เกิดข้อผิดพลาดในการแก้ไขข้อมูล';
              editUserSuccess.textContent = '';
            }
          })
          .catch(error => {
            console.error('Error:', error);
            editUserError.textContent = 'เกิดข้อผิดพลาดในการเชื่อมต่อเซิร์ฟเวอร์';
            editUserSuccess.textContent = '';
          });
      });

      // Reset error and success messagesเมื่อเปิด Modal
      $('#editUserModal').on('show.bs.modal', function() {
        editUserError.textContent = '';
        editUserSuccess.textContent = '';
        // ไม่รีเซ็ตฟอร์มเพื่อให้ข้อมูลที่กรอกไว้ยังคงอยู่
      });
    });

    // ตัวแปรสำหรับ Delete User
    const deleteUserBtns = document.querySelectorAll('.deleteUserBtn');
    const deleteUserModal = new bootstrap.Modal(document.getElementById('deleteUserModal'), {
      keyboard: false
    });
    const deleteUserForm = document.getElementById('deleteUserForm');
    const deleteUserError = document.getElementById('deleteUserError');
    const deleteUserSuccess = document.getElementById('deleteUserSuccess');
    const deleteUsername = document.getElementById('deleteUsername');
    const deleteUserId = document.getElementById('deleteUserId');

    // จัดการเมื่อคลิกปุ่ม "ลบ"
    deleteUserBtns.forEach(btn => {
      btn.addEventListener('click', function() {
        const userId = this.getAttribute('data-user-id');
        const username = this.getAttribute('data-username');

        // ตั้งค่าใน Modal
        deleteUserId.value = userId;
        deleteUsername.textContent = username;
        deleteUserError.textContent = '';
        deleteUserSuccess.textContent = '';

        // เปิด Modal
        deleteUserModal.show();
      });
    });

    // จัดการการส่งฟอร์มลบผู้ใช้
    deleteUserForm.addEventListener('submit', function(e) {
      e.preventDefault();
      const formData = new FormData(deleteUserForm);

      // ส่งข้อมูลผ่าน AJAX ไปยัง delete_employee.php
      fetch('delete_employee.php', {
          method: 'POST',
          body: new URLSearchParams(formData)
        })
        .then(response => response.json())
        .then(data => {
          if (data.success) {
            deleteUserSuccess.textContent = 'ลบผู้ใช้สำเร็จ';
            deleteUserError.textContent = '';
            // ปิด Modal และรีเฟรชหน้าหลังจากลบสำเร็จ
            setTimeout(() => {
              deleteUserModal.hide();
              location.reload(); // รีเฟรชหน้าเพื่ออัปเดตข้อมูล
            }, 1500);
          } else {
            deleteUserError.textContent = data.message || 'เกิดข้อผิดพลาดในการลบผู้ใช้';
            deleteUserSuccess.textContent = '';
          }
        })
        .catch(error => {
          console.error('Error:', error);
          deleteUserError.textContent = 'เกิดข้อผิดพลาดในการเชื่อมต่อเซิร์ฟเวอร์';
          deleteUserSuccess.textContent = '';
        });
    });
  </script>

</body>

</html>