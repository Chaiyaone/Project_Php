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
    <link rel="stylesheet" href="css/style.css">
</head>

<body>
    <nav class="navbar">
        <a href="#" class="navbar-brand">ระบบแจ้งซ่อม</a>
        <ul class="navbar-nav">
            <li class="nav-item"><a href="User.php?UserID=<?php echo $_SESSION['UserID']; ?>" class="nav-link" class="nav-link active">หน้าหลัก</a></li>
            <li class="nav-item"><a href="Report.php?UserID=<?php echo $_SESSION['UserID']; ?>" class="nav-link">แจ้งซ่อม</a></li>
            <li class="nav-item"><a href="UserReports.php?UserID=<?php echo $_SESSION['UserID']; ?>" class="nav-link">สถานะการซ่อม</a></li>
            <li class="nav-item"><a href="logout.php" class="nav-link logout">ออกจากระบบ</a></li>
        </ul>
    </nav>
    <div class="container">
        <h1>ยินดีต้อนรับสู่ระบบรายงานปัญหาแจ้งซ่อม</h1>
        <p>ระบบนี้ช่วยให้คุณสามารถแจ้งปัญหาการซ่อมบำรุงได้อย่างสะดวกและรวดเร็ว</p>
        <h2>สวัสดีคุณ <?php echo htmlspecialchars($user["Name"]); ?> ต้องการความช่วยเหลือหรือไม่?</h2>
        

        <div class="features">
            <div class="feature-card">
                <h2>แจ้งซ่อม</h2>
                <p>แจ้งปัญหาการซ่อมบำรุงผ่านระบบออนไลน์ได้ทันที</p>
                <a href="Report.php?UserID=<?php echo $_SESSION['UserID']; ?>" class="btn">แจ้งซ่อม</a>
            </div>
            <div class="feature-card">
                <h2>ตรวจสอบรายการแจ้งซ่อม</h2>
                <p>ตรวจสอบสถานะการซ่อมบำรุงได้ตลอดเวลา</p>
                <a href="UserReports.php?UserID=<?php echo $_SESSION['UserID']; ?>" class="btn">ตรวจสอบสถานะ</a>
            </div>
            
        </div>
    </div>
</body>

</html>