<?php
// faculty/navbar-faculty.php
$current_page = basename($_SERVER['PHP_SELF']);
?>
<style>
    /* กำหนดสีพื้นหลัง Navbar และการจัดวาง */
    .navbar {
        padding: 0.8rem 1rem;
    }

    /* โลโก้ใน Navbar */
    .navbar-brand img {
        max-height: 55px;
        margin-right: 10px;
    }

    /* สไตล์สำหรับลิงก์ใน Navbar */
    .navbar-nav .nav-link {
        position: relative;
        padding: 0.5rem 1rem;
        color: #ffffff;
        transition: color 0.3s ease;
    }

    /* สไตล์สำหรับลิงก์ที่ active */
    .navbar-nav .nav-link.active {
        color: #FF8500;
    }

    /* เส้นขีดใต้สำหรับลิงก์ที่ active */
    .navbar-nav .nav-link.active::after {
        content: '';
        position: absolute;
        bottom: -5px;
        left: 25%;
        width: 50%;
        height: 2px;
        background-color: #FF8500;
    }

    .navbar-nav .nav-link::after {
        content: '';
        position: absolute;
        bottom: -5px;
        left: 50%;
        width: 0%;
        height: 2px;
        background-color: #FF8500;
        transition: width 0.3s ease, left 0.3s ease;
    }

    /* เมื่อ hover บนลิงก์ */
    .navbar-nav .nav-link:hover {
        color: #FF8500;
    }

    /* เมื่อ hover บนลิงก์ */
    .navbar-nav .nav-link:not(.dropdown-toggle):hover::after {
        width: 50%;
        left: 25%;
    }

    /* ปรับแต่ง Navbar สำหรับหน้าจอมือถือ */
    @media (max-width: 991.98px) {

        .navbar-nav .nav-link,
        .navbar-nav .dropdown-item {
            text-align: center;
        }
    }
</style>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container-fluid">
        <!-- โลโก้ของเว็บไซต์ -->
        <a class="navbar-brand" href="faculty_dashboard.php">
            <img src="../logo-banner-alumni.png" alt="Logo">
        </a>
        <!-- ปุ่มสำหรับหน้าจอมือถือ -->
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNavFaculty"
            aria-controls="navbarNavFaculty" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <!-- ลิงก์เมนู -->
        <div class="collapse navbar-collapse" id="navbarNavFaculty">
            <ul class="navbar-nav mx-auto">
                <!-- หน้าแรก -->
                <li class="nav-item">
                    <a class="nav-link <?= ($current_page == 'faculty_dashboard.php') ? 'active' : '' ?>"
                        href="faculty_dashboard.php">หน้าแรก</a>
                </li>
                <!-- เพิ่มข้อมูล -->
                <li class="nav-item">
                    <a class="nav-link <?= ($current_page == 'add_personal.php') ? 'active' : '' ?>"
                        href="add_personal.php">เพิ่มข้อมูลศิษย์เก่า</a>
                </li>
                <!-- ค้นหา -->
                <li class="nav-item">
                    <a class="nav-link <?= ($current_page == 'search_results.php') ? 'active' : '' ?>"
                        href="search_results.php">ค้นหาศิษย์เก่า</a>
                </li>
                <!-- เมนูจัดการภาควิชา -->
                <li class="nav-item">
                    <a class="nav-link <?= ($current_page == 'manage_departments.php') ? 'active' : '' ?>"
                        href="manage_departments.php">จัดการภาควิชา</a>
                </li>
            </ul>
            <!-- เมนูด้านขวา (ออกจากระบบ) -->
            <ul class="navbar-nav ms-auto">
                <?php if (isset($_SESSION['username'])): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="../logout.php">ออกจากระบบ</a>
                    </li>
                <?php else: ?>
                    <li class="nav-item">
                        <a class="nav-link" href="../login.php">เข้าสู่ระบบ</a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>
