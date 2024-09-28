<?php
session_start();

// ตรวจสอบว่าผู้ใช้ได้เข้าสู่ระบบแล้วหรือยัง และมีสิทธิ์เป็น admin หรือไม่
if (!isset($_SESSION['user_id']) || $_SESSION['access_level'] !== 'admin') {
    header("Location: login.php");
    exit();
}

require 'db_connect.php'; // เรียกใช้ไฟล์เชื่อมต่อฐานข้อมูล

// ดึงข้อมูลสำหรับรายงานต่างๆ

// 1. จำนวนผู้ใช้ทั้งหมด
$stmt_total_users = $pdo->prepare("SELECT COUNT(*) AS total_users FROM PersonalInfo");
$stmt_total_users->execute();
$total_users = $stmt_total_users->fetch(PDO::FETCH_ASSOC)['total_users'];

// 2. จำนวนผู้ใช้ตามจังหวัด
$stmt_users_by_province = $pdo->prepare("
    SELECT prov.name_in_thai AS province_name, COUNT(p.person_id) AS user_count
    FROM PersonalInfo p
    JOIN Address a ON p.person_id = a.person_id
    JOIN provinces prov ON a.province = prov.id
    GROUP BY prov.name_in_thai
    ORDER BY user_count DESC
");
$stmt_users_by_province->execute();
$users_by_province = $stmt_users_by_province->fetchAll(PDO::FETCH_ASSOC);

// 3. จำนวนรางวัลทั้งหมด
$stmt_total_awards = $pdo->prepare("
    SELECT COUNT(*) AS total_awards
    FROM (
        SELECT person_id FROM studentaward
        UNION ALL
        SELECT person_id FROM awardhistory
    ) AS awards
");
$stmt_total_awards->execute();
$total_awards = $stmt_total_awards->fetch(PDO::FETCH_ASSOC)['total_awards'];

// 4. จำนวนผู้ใช้ตามคณะ
$stmt_users_by_faculty = $pdo->prepare("
    SELECT edu.faculty_name AS faculty, COUNT(p.person_id) AS user_count
    FROM PersonalInfo p
    JOIN education edu ON p.person_id = edu.person_id
    GROUP BY edu.faculty_name
    ORDER BY user_count DESC
");
$stmt_users_by_faculty->execute();
$users_by_faculty = $stmt_users_by_faculty->fetchAll(PDO::FETCH_ASSOC);

// 5. จำนวนผู้ใช้ที่มีประสบการณ์ทำงานตามประเทศ
$stmt_users_by_work_country = $pdo->prepare("
    SELECT we.country AS work_country, COUNT(p.person_id) AS user_count
    FROM PersonalInfo p
    JOIN workexperience we ON p.person_id = we.person_id
    GROUP BY we.country
    ORDER BY user_count DESC
");
$stmt_users_by_work_country->execute();
$users_by_work_country = $stmt_users_by_work_country->fetchAll(PDO::FETCH_ASSOC);

// 6. ดึงข้อมูลของผู้ใช้ที่ล็อกอินเข้ามา
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
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <!-- jQuery (Optional, for easier AJAX handling) -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        body {
            font-family: "Sarabun", sans-serif;
        }
    </style>
</head>

<body>
    <?php include 'navbar.php'; ?>

    <div class="container mt-5">
        <!-- แสดงข้อความยินดีต้อนรับพร้อมชื่อผู้ใช้ -->
        <h3 class="mb-4">ยินดีต้อนรับ, <?= htmlspecialchars($current_user['username']) ?>!</h3>

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


        <!-- รายงานสรุป -->
        <div class="row g-4">
            <!-- รายงานจำนวนผู้ใช้ทั้งหมด -->
            <div class="col-md-4">
                <div class="card text-white bg-primary h-100">
                    <div class="card-body">
                        <div class="card-title">
                            <i class="fas fa-users fa-2x"></i>
                            <span class="float-end"><?= $total_users ?></span>
                        </div>
                        <p class="card-text">จำนวนผู้ใช้ทั้งหมด</p>
                    </div>
                </div>
            </div>

            <!-- รายงานจำนวนรางวัลทั้งหมด -->
            <div class="col-md-4">
                <div class="card text-white bg-success h-100">
                    <div class="card-body">
                        <div class="card-title">
                            <i class="fas fa-trophy fa-2x"></i>
                            <span class="float-end"><?= $total_awards ?></span>
                        </div>
                        <p class="card-text">จำนวนรางวัลทั้งหมด</p>
                    </div>
                </div>
            </div>

            <!-- รายงานจำนวนผู้ใช้ตามคณะ -->
            <div class="col-md-4">
                <div class="card text-white bg-warning h-100">
                    <div class="card-body">
                        <div class="card-title">
                            <i class="fas fa-building fa-2x"></i>
                            <span class="float-end"><?= count($users_by_faculty) ?></span>
                        </div>
                        <p class="card-text">จำนวนคณะทั้งหมด</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- กราฟแสดงจำนวนผู้ใช้ตามจังหวัด -->
        <div class="row mt-5">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <i class="fas fa-map-marked-alt"></i> จำนวนผู้ใช้ตามจังหวัด
                    </div>
                    <div class="card-body">
                        <canvas id="usersByProvinceChart"></canvas>
                    </div>
                </div>
            </div>

            <!-- กราฟแสดงจำนวนผู้ใช้ที่มีประสบการณ์ทำงานตามประเทศ -->
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <i class="fas fa-globe-americas"></i> จำนวนผู้ใช้ที่มีประสบการณ์ทำงานตามประเทศ
                    </div>
                    <div class="card-body">
                        <canvas id="usersByWorkCountryChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- ตารางแสดงจำนวนผู้ใช้ตามคณะ -->
        <div class="row mt-5">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <i class="fas fa-chart-bar"></i> จำนวนผู้ใช้ตามคณะ
                    </div>
                    <div class="card-body">
                        <table class="table table-striped table-bordered">
                            <thead class="table-light">
                                <tr>
                                    <th>คณะ</th>
                                    <th>จำนวนผู้ใช้</th>
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

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Chart.js Scripts -->
    <script>
        // กราฟจำนวนผู้ใช้ตามจังหวัด
        const usersByProvinceCtx = document.getElementById('usersByProvinceChart').getContext('2d');
        const usersByProvinceChart = new Chart(usersByProvinceCtx, {
            type: 'bar',
            data: {
                labels: <?= json_encode(array_column($users_by_province, 'province_name')) ?>,
                datasets: [{
                    label: 'จำนวนผู้ใช้',
                    data: <?= json_encode(array_column($users_by_province, 'user_count')) ?>,
                    backgroundColor: 'rgba(54, 162, 235, 0.6)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        display: false
                    },
                    title: {
                        display: true,
                        text: 'จำนวนผู้ใช้ตามจังหวัด'
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        precision: 0
                    }
                }
            }
        });

        // กราฟจำนวนผู้ใช้ที่มีประสบการณ์ทำงานตามประเทศ
        const usersByWorkCountryCtx = document.getElementById('usersByWorkCountryChart').getContext('2d');
        const usersByWorkCountryChart = new Chart(usersByWorkCountryCtx, {
            type: 'pie',
            data: {
                labels: <?= json_encode(array_column($users_by_work_country, 'work_country')) ?>,
                datasets: [{
                    label: 'จำนวนผู้ใช้',
                    data: <?= json_encode(array_column($users_by_work_country, 'user_count')) ?>,
                    backgroundColor: [
                        'rgba(255, 99, 132, 0.6)',
                        'rgba(54, 162, 235, 0.6)',
                        'rgba(255, 206, 86, 0.6)',
                        'rgba(75, 192, 192, 0.6)',
                        'rgba(153, 102, 255, 0.6)',
                        'rgba(255, 159, 64, 0.6)'
                    ],
                    borderColor: [
                        'rgba(255,99,132,1)',
                        'rgba(54, 162, 235, 1)',
                        'rgba(255, 206, 86, 1)',
                        'rgba(75, 192, 192, 1)',
                        'rgba(153, 102, 255, 1)',
                        'rgba(255, 159, 64, 1)'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    title: {
                        display: true,
                        text: 'จำนวนผู้ใช้ที่มีประสบการณ์ทำงานตามประเทศ'
                    }
                }
            }
        });
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const revealBtn = document.getElementById('revealPasswordPartBtn');
            const passwordForm = document.getElementById('passwordForm');
            const passwordModal = new bootstrap.Modal(document.getElementById('passwordModal'), {
                keyboard: false
            });
            const passwordError = document.getElementById('passwordError');
            const passwordPartMasked = document.getElementById('passwordPartMasked');

            passwordForm.addEventListener('submit', function(e) {
                e.preventDefault();
                const password = document.getElementById('adminPassword').value;

                // ส่ง AJAX request ไปยัง reveal_password_part.php
                fetch('reveal_password_part.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: 'password=' + encodeURIComponent(password)
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // แสดง password_part ที่ได้รับจากเซิร์ฟเวอร์
                            passwordPartMasked.textContent = data.password_part;
                            // ปิด Modal
                            passwordModal.hide();
                            // ล้างฟอร์มและข้อความผิดพลาด
                            passwordForm.reset();
                            passwordError.textContent = '';
                        } else {
                            // แสดงข้อความผิดพลาด
                            passwordError.textContent = data.message || 'เกิดข้อผิดพลาด';
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        passwordError.textContent = 'เกิดข้อผิดพลาดในการเชื่อมต่อเซิร์ฟเวอร์';
                    });
            });
        });

          // JavaScript สำหรับการแก้ไขข้อมูลผู้ใช้
          document.addEventListener('DOMContentLoaded', function() {
            const editUserForm = document.getElementById('editUserForm');
            const editUserError = document.getElementById('editUserError');
            const editUserSuccess = document.getElementById('editUserSuccess');

            editUserForm.addEventListener('submit', function(e) {
                e.preventDefault();
                const email = document.getElementById('editEmail').value;
                const currentPassword = document.getElementById('currentPassword').value;
                const newPassword = document.getElementById('newPassword').value;
                const confirmNewPassword = document.getElementById('confirmNewPassword').value;

                // ตรวจสอบว่ารหัสผ่านใหม่และรหัสผ่านยืนยันตรงกันหรือไม่
                if (newPassword !== confirmNewPassword) {
                    editUserError.textContent = 'รหัสผ่านใหม่และรหัสผ่านยืนยันไม่ตรงกัน';
                    editUserSuccess.textContent = '';
                    return;
                }

                // ส่งข้อมูลผ่าน AJAX ไปยัง update_user.php
                const formData = new URLSearchParams();
                formData.append('email', email);
                formData.append('current_password', currentPassword);
                formData.append('new_password', newPassword);
                formData.append('confirm_new_password', confirmNewPassword);

                fetch('update_user.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: formData.toString()
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        editUserSuccess.textContent = 'แก้ไขข้อมูลสำเร็จ';
                        editUserError.textContent = '';
                        // ปิด Modal หลังจากแก้ไขสำเร็จ
                        setTimeout(() => {
                            $('#editUserModal').modal('hide');
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
            $('#editUserModal').on('show.bs.modal', function () {
                editUserError.textContent = '';
                editUserSuccess.textContent = '';
                editUserForm.reset();
            });
        });
    </script>

</body>

</html>