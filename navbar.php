<?php
$current_page = basename($_SERVER['PHP_SELF']);
$active_settings = in_array($current_page, ['employees.php', 'faculties.php']) ? 'active' : '';
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

    /* ยกเว้น dropdown-toggle */
    .navbar-nav .nav-link.dropdown-toggle::after {
        display: none;
    }

    /* เมื่อ hover บนลิงก์ */
    .navbar-nav .nav-link:not(.dropdown-toggle):hover::after {
        width: 50%;
        left: 25%;
    }

    /* เมื่อ hover บนลิงก์ */
    .navbar-nav .nav-link:hover {
        color: #FF8500;
    }

    /* สไตล์สำหรับ dropdown-menu */
    .dropdown-menu {
        background-color: #343a40;
        border: none;
        margin-top: 0;
        transition: opacity 0.3s ease, visibility 0.3s ease;
        opacity: 0;
        visibility: hidden;
    }

    /* แสดง dropdown-menu เมื่อ hover */
    .nav-item.dropdown:hover .dropdown-menu {
        opacity: 1;
        visibility: visible;
        display: block;
    }

    /* สไตล์สำหรับ dropdown-item */
    .dropdown-item {
        color: #fff;
        padding: 0.5rem 1rem;
        transition: background-color 0.3s ease;
    }

    /* เมื่อ hover บน dropdown-item */
    .dropdown-item:hover,
    .dropdown-item:focus {
        background-color: #FF8500;
        color: #fff;
    }

    /* dropdown-item ที่ active */
    .dropdown-item.active {
        background-color: #FF8500;
        color: #fff;
    }

    /* ปรับตำแหน่งลูกศรใน dropdown-toggle */
    .dropdown-toggle::after {
        display: inline-block;
        margin-left: 0.255em;
        vertical-align: 0.255em;
        content: "";
        border-top: 0.3em solid;
        border-right: 0.3em solid transparent;
        border-left: 0.3em solid transparent;
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
        <a class="navbar-brand" href="admin_dashboard.php">
            <img src="logo-banner-alumni.png" alt="Logo">
        </a>
        <!-- ปุ่มสำหรับหน้าจอมือถือ -->
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
            aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <!-- ลิงก์เมนู -->
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav mx-auto">
                <!-- หน้าแรก -->
                <li class="nav-item">
                    <a class="nav-link <?= ($current_page == 'admin_dashboard.php') ? 'active' : '' ?>"
                        href="admin_dashboard.php">หน้าแรก</a>
                </li>
                <!-- เพิ่มข้อมูล -->
                <li class="nav-item">
                    <a class="nav-link <?= ($current_page == 'add_personal.php') ? 'active' : '' ?>"
                        href="add_personal.php">เพิ่มข้อมูล</a>
                </li>
                <!-- ค้นหา -->
                <li class="nav-item">
                    <a class="nav-link <?= ($current_page == 'search_results.php') ? 'active' : '' ?>"
                        href="search_results.php">ค้นหา</a>
                </li>
                <!-- เมนูตั้งค่า -->
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle <?= $active_settings ?>" href="#" id="navbarDropdown"
                        role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        ตั้งค่า
                    </a>
                    <ul class="dropdown-menu" aria-labelledby="navbarDropdown">
                        <li>
                            <a class="dropdown-item <?= ($current_page == 'employees.php') ? 'active' : '' ?>"
                                href="employees.php">พนักงาน</a>
                        </li>
                        <li>
                            <a class="dropdown-item <?= ($current_page == 'faculties.php') ? 'active' : '' ?>"
                                href="faculties.php">คณะทั้งหมด</a>
                        </li>
                    </ul>
                </li>
            </ul>
            <!-- เมนูด้านขวา (เข้าสู่ระบบ/ออกจากระบบ) -->
            <ul class="navbar-nav ms-auto">
                <?php if (isset($_SESSION['username'])): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="logout.php">ออกจากระบบ</a>
                    </li>
                <?php else: ?>
                    <li class="nav-item">
                        <a class="nav-link" href="login.php">เข้าสู่ระบบ</a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>