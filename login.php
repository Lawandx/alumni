<?php
//login.php
require 'db_connect.php'; // เรียกใช้ไฟล์เชื่อมต่อฐานข้อมูล
session_start();

$error_message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    // ตรวจสอบว่ามี username และ password ที่ส่งเข้ามาหรือไม่
    if (!empty($username) && !empty($password)) {
        // เตรียมคำสั่ง SQL สำหรับการดึงข้อมูลผู้ใช้
        $stmt = $pdo->prepare("SELECT * FROM User WHERE username = :username");
        $stmt->bindParam(':username', $username);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // ตรวจสอบว่าพบผู้ใช้หรือไม่ และตรวจสอบรหัสผ่าน
        if ($user && password_verify($password, $user['password_hash'])) {
            // ตั้งค่า session สำหรับผู้ใช้ที่เข้าสู่ระบบ
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['access_level'] = $user['access_level'];

            // เก็บ faculty_id และ faculty_name ใน SESSION ถ้าเป็น faculty
            if ($user['access_level'] === 'faculty') {
                $_SESSION['faculty_id'] = $user['faculty_id'];
                $_SESSION['faculty_name'] = $user['faculty_name'];
            }

            if ($user['access_level'] === 'department') {
                $_SESSION['faculty_id'] = $user['faculty_id'];
                $_SESSION['department_id'] = $user['department_id'];
                $_SESSION['department_name'] = $user['department_name'];
            }


            // ตรวจสอบระดับการเข้าถึงและเปลี่ยนเส้นทางไปยังหน้าที่เหมาะสม
            if ($user['access_level'] == 'admin') {
                header("Location: admin_dashboard.php");
            } elseif ($user['access_level'] == 'faculty') {
                header("Location: faculty/faculty_dashboard.php");
            } elseif ($user['access_level'] == 'department') {
                header("Location: department/department_dashboard.php");
            } elseif ($user['access_level'] == 'major') {
                header("Location: major_dashboard.php");
            } else {
                $error_message = "Access level is not recognized.";
            }
            exit();
        } else {
            // กรณีที่รหัสผ่านไม่ถูกต้องหรือไม่พบผู้ใช้
            $error_message = "ชื่อผู้ใช้หรือรหัสผ่านไม่ถูกต้อง";
        }
    } else {
        // กรณีที่ฟิลด์ username หรือ password ว่างเปล่า
        $error_message = "กรุณากรอกชื่อผู้ใช้และรหัสผ่าน";
    }
}
?>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>เข้าสู่ระบบ</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Google Fonts (Sarabun) -->
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@400;500;600&display=swap" rel="stylesheet">
    <!-- สไตล์ที่ปรับปรุงแล้ว -->
    <style>
        body {
            font-family: "Sarabun", sans-serif;
            background-color: #f8f9fa;
            /* สีพื้นหลัง */
        }

        /* สไตล์สำหรับ Navbar */
        .navbar {
            background-color: #343a40;
            /* สีพื้นหลังของ Navbar */
        }

        .navbar-brand {
            margin: 0 auto;
            /* จัดให้อยู่ตรงกลาง */
        }

        .navbar-brand img {
            height: 50px;
            /* ปรับขนาดโลโก้ตามต้องการ */
        }

        .login-container {
            max-width: 400px;
            margin: 80px auto;
            padding: 30px;
            background-color: #ffffff;
            /* สีพื้นหลังของกล่องล็อกอิน */
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        .login-container h3 {
            color: #343a40;
            text-align: center;
            margin-bottom: 30px;
        }

        .btn-primary {
            background-color: #FF8500;
            border-color: #FF8500;
        }

        .btn-primary:hover {
            background-color: #e07a00;
            border-color: #e07a00;
        }

        .error-message {
            color: red;
            text-align: center;
            margin-bottom: 15px;
        }

        @media (max-width: 576px) {
            .navbar-brand img {
                height: 40px;
            }

            .login-container {
                margin: 60px auto;
                padding: 20px;
            }
        }
    </style>
</head>

<body>
    <!-- Navbar แบบกำหนดเอง -->
    <nav class="navbar navbar-dark">
        <a class="navbar-brand" href="#">

            <img src="logo-banner-alumni.png" alt="Logo">

        </a>
    </nav>

    <div class="login-container">
        <h3>เข้าสู่ระบบ</h3>

        <?php if (!empty($error_message)): ?>
            <div class="error-message">
                <?= htmlspecialchars($error_message) ?>
            </div>
        <?php endif; ?>

        <form action="login.php" method="POST">
            <div class="mb-3">
                <label for="username" class="form-label">ชื่อผู้ใช้</label>
                <input type="text" class="form-control" id="username" name="username" required autofocus>
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">รหัสผ่าน</label>
                <input type="password" class="form-control" id="password" name="password" required>
            </div>
            <button type="submit" class="btn btn-primary w-100">เข้าสู่ระบบ</button>
        </form>
    </div>

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Optional JavaScript สำหรับการตรวจสอบฟอร์ม -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const loginForm = document.querySelector('form');
            loginForm.addEventListener('submit', function(e) {
                const username = document.getElementById('username').value.trim();
                const password = document.getElementById('password').value;

                if (username === '' || password === '') {
                    e.preventDefault();
                    alert('กรุณากรอกชื่อผู้ใช้และรหัสผ่าน');
                }
            });
        });
    </script>
</body>

</html>