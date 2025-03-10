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

// ตรวจสอบว่ามีการแจ้งเตือนถูกส่งกลับมาหรือไม่
$alert_message = "";
$alert_type = "";
if (isset($_SESSION['alert_message'])) {
    $alert_message = $_SESSION['alert_message'];
    $alert_type = $_SESSION['alert_type'];
    // ลบข้อมูลแจ้งเตือนออกจาก session หลังจากใช้งานแล้ว
    unset($_SESSION['alert_message']);
    unset($_SESSION['alert_type']);
}

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
    <style>
        /* เพิ่มสไตล์สำหรับป็อปอัพแจ้งเตือน */
        .alert-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 1000;
            justify-content: center;
            align-items: center;
        }
        
        .alert-box {
            background-color: white;
            padding: 20px;
            border-radius: 5px;
            text-align: center;
            width: 350px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }
        
        .alert-box h3 {
            margin-top: 0;
            margin-bottom: 15px;
        }
        
        .alert-success h3 {
            color: #2ecc71;
        }
        
        .alert-error h3 {
            color: #e74c3c;
        }
        
        .alert-box p {
            margin-bottom: 20px;
        }
        
        .alert-button {
            padding: 10px 20px;
            background-color: #3498db;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-family: 'Kanit', sans-serif;
        }
        
        .alert-button:hover {
            background-color: #2980b9;
        }
    </style>
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
            <form id="reportForm" action="Controllers/Report/postReport.php" method="post" enctype="multipart/form-data">
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

    <!-- ป็อปอัพแจ้งเตือน -->
    <div class="alert-overlay" id="alertOverlay">
        <div class="alert-box" id="alertBox">
            <h3 id="alertTitle">แจ้งเตือน</h3>
            <p id="alertMessage"></p>
            <button class="alert-button" id="alertButton">ตกลง</button>
        </div>
    </div>

    <script>
        // ฟังก์ชันสำหรับแสดงป็อปอัพแจ้งเตือน
        function showAlert(message, type = 'success') {
            const alertOverlay = document.getElementById('alertOverlay');
            const alertBox = document.getElementById('alertBox');
            const alertTitle = document.getElementById('alertTitle');
            const alertMessage = document.getElementById('alertMessage');
            const alertButton = document.getElementById('alertButton');

            alertMessage.textContent = message;

            if (type === 'success') {
                alertBox.className = 'alert-box alert-success';
                alertTitle.textContent = 'สำเร็จ';
            } else {
                alertBox.className = 'alert-box alert-error';
                alertTitle.textContent = 'เกิดข้อผิดพลาด';
            }

            alertOverlay.style.display = 'flex';

            alertButton.onclick = function() {
                alertOverlay.style.display = 'none';
                if (type === 'success') {
                    window.location.href = 'User.php?UserID=<?php echo $_SESSION['UserID']; ?>';
                }
            };
        }

        // ตรวจสอบว่ามีข้อความแจ้งเตือนจาก PHP หรือไม่
        <?php if (!empty($alert_message)) : ?>
        document.addEventListener('DOMContentLoaded', function() {
            showAlert('<?php echo $alert_message; ?>', '<?php echo $alert_type; ?>');
        });
        <?php endif; ?>

        // จัดการการส่งแบบฟอร์ม
        document.getElementById('reportForm').addEventListener('submit', function(event) {
            // ไม่ต้อง preventDefault() เพื่อให้ฟอร์มยังคงส่งไปยัง postReport.php
        });
    </script>
</body>

</html>