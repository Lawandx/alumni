<?php
$host = 'localhost';  
$dbname = 'alumni';  
$username = 'root';  
$password = 'kosit130646';  

try {
    // สร้างการเชื่อมต่อกับฐานข้อมูลโดยใช้ PDO
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);

    // ตั้งค่า PDO ให้แสดงข้อผิดพลาดในรูปแบบ Exception
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // ตั้งค่าให้ PDO ทำงานในรูปแบบ Prepared Statements
    $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

} catch (PDOException $e) {
    // จัดการข้อผิดพลาดในกรณีที่การเชื่อมต่อล้มเหลว
    echo "Connection failed: " . $e->getMessage();
    exit();
}
?>
