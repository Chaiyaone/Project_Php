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
$stmt = $conn->prepare("SELECT * FROM users WHERE UserID = ?");
$stmt->bind_param("i", $UserID);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

// ฟังก์ชันดึงข้อมูลชั้น (floors)
function getFloorID($conn) {
    $sql = "SELECT * FROM floors ORDER BY FloorID";
    $dbQuery = $conn->query($sql);
    if (!$dbQuery) {
        die("(functionDB:getFloorID) select floors มีข้อผิดพลาด: " . $conn->error);
    }
    
    $options = '<option value="">เลือกชั้น</option>';
    while ($result = $dbQuery->fetch_object()) {
        $options .= '<option value="' . $result->FloorID . '">' . $result->FloorName . '</option>';
    }
    
    return $options;
}
function getDormID($conn) {
    $sql = "SELECT * FROM dorms ORDER BY DormID";
    $dbQuery = $conn->query($sql);
    if (!$dbQuery) {
        die("(functionDB:getDormID) select dorms มีข้อผิดพลาด: " . $conn->error);
    }
    
    $options = '<option value="">เลือกหอพัก</option>';
    while ($result = $dbQuery->fetch_object()) {
        $options .= '<option value="' . $result->DormID . '">' . $result->DormName . '</option>';
    }
    
    return $options;
}
$dorms = getDormID($conn);
$options = getFloorID($conn); // เรียกใช้ฟังก์ชันก่อนปิด Connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>แบบฟอร์มแจ้งซ่อม</title>
    <link rel="stylesheet" href="css/NavbarReport.css">
    <link rel="stylesheet" href="css/report.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;500;700&display=swap" rel="stylesheet">
</head>

<body>
    <nav class="navbar">
        <a href="#" class="navbar-brand">ระบบแจ้งซ่อม</a>
        <ul class="navbar-nav">
            <li class="nav-item"><a href="User.php?UserID=<?php echo $_SESSION['UserID']; ?>" class="nav-link" class="nav-link active">หน้าหลัก</a></li>
            <li class="nav-item"><a href="Report.php?UserID=<?php echo $_SESSION['UserID']; ?>"
                    class="nav-link">แจ้งซ่อม</a></li>
            <li class="nav-item"><a href="#" class="nav-link">สถานะการซ่อม</a></li>
            <li class="nav-item"><a href="logout.php" class="nav-link logout">ออกจากระบบ</a></li>
        </ul>
    </nav>
    <div class="container">
        <div class="report-container">
            <h1>แบบฟอร์มแจ้งซ่อม</h1>
            <form action="Controllers/Report/postReport.php" method="post" enctype="multipart/form-data">
                <div class="form-row">
                    <div class="form-group">
                        <label>หมายเลขชั้น/สถานที่ที่ต้องการซ่อม:</label>
                        <select name="FloorID" required>
                        <?php echo $options; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>หอพัก:</label>
                        <select name="DormID" required>
                        <?php echo $dorms; ?>
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label>ชื่อปัญหา</label>
                    <input type="text" name="NameReport" required>
                </div>
                <div class="form-group">
                    <label>รายละเอียดปัญหา:</label>
                    <textarea name="Description" rows="4" required></textarea>
                </div>
                <div class="form-group">
                    <label>แนบรูปภาพ (ถ้ามี):</label>
                    <input type="file" name="Picture" accept="image/*">
                </div>
                <button type="submit" class="btn">ส่งแบบฟอร์ม</button>
            </form>
        </div>
    </div>
</body>

</html>