<?php
session_start();
if (!isset($_SESSION["UserID"])) {
    header("Location: login.php");
    exit();
}

    $hostname = "localhost";
    $username = "root";
    $password = "";
    $dbname = "repair_system";

    $conn = new mysqli($hostname, $username, $password, $dbname);
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

// ดึงข้อมูลของ User ที่ล็อกอินอยู่
    $UserID = $_SESSION["UserID"];
    $stmt = $conn->prepare("SELECT * FROM users WHERE UserID = ?"); // ดึงข้อมูลจากDatabase 
    $stmt->bind_param("i", $UserID);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    $stmt->close();
    $conn->close();
?>

<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <title>Dashboard</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/Navbar.css">
    <link rel="stylesheet" href="css/styleAdmin.css">
</head>

<body>
    <nav class="navbar">
        <a href="#" class="navbar-brand">ระบบแจ้งซ่อม</a>
        <ul class="navbar-nav">
            <li class="nav-item"><a href="Admin.php?UserID=<?php echo $_SESSION['UserID']; ?>" class="nav-link" class="nav-link active">หน้าหลัก</a></li>
            <li class="nav-item"><a href="logout.php" class="nav-link logout">ออกจากระบบ</a></li>
        </ul>
    </nav>
    <div class="container">
        <h1>ยินดีต้อนรับสู่ระบบรายงานปัญหาแจ้งซ่อม (Admin)</h1>
        <h2>Hello Admin <?php echo htmlspecialchars($user["Name"]); ?> ต้องการแก้ไขอะไร?</h2>
        

        <div class="features">
            <div class="feature-card">
                <h2>จัดการหอพัก</h2>
                <p>แจ้งปัญหาการซ่อมบำรุงผ่านระบบออนไลน์ได้ทันที</p>
                <a href="ManageDorms.php?UserID=<?php echo $_SESSION['UserID']; ?>" class="btn">แจ้งซ่อม</a>
            </div>
            <div class="feature-card">
                <h2>จัดการผู้ใช้งาน</h2>
                <p>ตรวจสอบสถานะการซ่อมบำรุงได้ตลอดเวลา</p>
                <a href="ManageUser.php?UserID=<?php echo $_SESSION['UserID']; ?>" class="btn">ตรวจสอบสถานะ</a>
            </div>
            <div class="feature-card">
                <h2>จัดการปัญหา</h2>
                <p>ตรวจสอบสถานะการซ่อมบำรุงได้ตลอดเวลา</p>
                <a href="ManageReports.php?UserID=<?php echo $_SESSION['UserID']; ?>" class="btn">ตรวจสอบสถานะ</a>
            </div>
            <div class="feature-card">
                <h2>จัดการปัญหา</h2>
                <p>ตรวจสอบสถานะการซ่อมบำรุงได้ตลอดเวลา</p>
                <a href="ManageReports.php?UserID=<?php echo $_SESSION['UserID']; ?>" class="btn">ตรวจสอบสถานะ</a>
            </div>
        </div>
    </div>
</body>

</html>