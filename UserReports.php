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

$UserID = $_SESSION["UserID"];

// ตั้งค่าจำนวนรายการต่อหน้า
$items_per_page = 5;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $items_per_page;

// นับจำนวนรายการทั้งหมด
$count_sql = "SELECT COUNT(*) AS total FROM reports WHERE UserID = ?";
$count_stmt = $conn->prepare($count_sql);
$count_stmt->bind_param("i", $UserID);
$count_stmt->execute();
$count_result = $count_stmt->get_result();
$total_items = $count_result->fetch_assoc()['total'];
$total_pages = ceil($total_items / $items_per_page);
$count_stmt->close();

// ดึงข้อมูลแจ้งซ่อมโดยใช้ LIMIT
$sql = "SELECT r.ReportID, r.NameReport, r.Description, r.Picture, r.ReportDate, 
               f.FloorName, d.DormName 
        FROM reports r
        JOIN floors f ON r.FloorID = f.FloorID
        JOIN dorms d ON r.DormID = d.DormID
        WHERE r.UserID = ?
        ORDER BY r.ReportDate DESC
        LIMIT ?, ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("iii", $UserID, $offset, $items_per_page);
$stmt->execute();
$result = $stmt->get_result();
$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>รายการแจ้งซ่อมของฉัน</title>
    <link rel="stylesheet" href="css/NavbarReport.css">
    <link rel="stylesheet" href="css/reportUser.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;500;700&display=swap" rel="stylesheet">
    <style>
        /* เพิ่ม CSS สำหรับรูปภาพและ Modal */
        .report-img {
            max-width: 100px;
            max-height: 80px;
            cursor: pointer;
            border-radius: 4px;
            border: 1px solid #ddd;
            transition: transform 0.2s ease;
        }
        
        .report-img:hover {
            transform: scale(1.05);
        }
        
        /* Modal สำหรับแสดงรูปภาพขนาดใหญ่ */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.85);
            overflow: auto;
        }
        
        .modal-content {
            display: block;
            position: relative;
            margin: auto;
            max-width: 90%;
            max-height: 90%;
            top: 50%;
            transform: translateY(-50%);
        }
        
        .close {
            position: absolute;
            top: 15px;
            right: 25px;
            color: #f1f1f1;
            font-size: 40px;
            font-weight: bold;
            transition: 0.3s;
            cursor: pointer;
        }
        
        .close:hover,
        .close:focus {
            color: #bbb;
            text-decoration: none;
        }
        
        /* ปรับแต่งการแสดงรายละเอียดให้ไม่ขาด */
        .report-table td {
            vertical-align: top;
            padding: 10px;
            word-break: break-word;
        }
        
        /* ทำให้ตารางเลื่อนได้ในแนวนอนบนมือถือ */
        @media (max-width: 768px) {
            .report-container {
                overflow-x: auto;
            }
            
            .report-table {
                min-width: 800px;
            }
        }
    </style>
</head>

<body>
    <nav class="navbar">
        <a href="#" class="navbar-brand">ระบบแจ้งซ่อม</a>
        <ul class="navbar-nav">
            <li class="nav-item"><a href="User.php?UserID=<?php echo $_SESSION['UserID']; ?>" class="nav-link">หน้าหลัก</a></li>
            <li class="nav-item"><a href="Report.php?UserID=<?php echo $_SESSION['UserID']; ?>" class="nav-link">แจ้งซ่อม</a></li>
            <li class="nav-item"><a href="#" class="nav-link active">สถานะการซ่อม</a></li>
            <li class="nav-item"><a href="logout.php" class="nav-link logout">ออกจากระบบ</a></li>
        </ul>
    </nav>

    <div class="container">
        <div class="report-container">
            <h1>รายการแจ้งซ่อมของฉัน</h1>
            <?php if ($result->num_rows > 0) { ?>
                <table class="report-table">
                    <thead>
                        <tr>
                            <th>หมายเลข</th>
                            <th>หัวข้อ</th>
                            <th>รายละเอียด</th>
                            <th>หอพัก</th>
                            <th>ชั้น</th>
                            <th>วันที่แจ้ง</th>
                            <th>รูปภาพ</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $result->fetch_assoc()) { ?>
                            <tr>
                                <td><?php echo $row["ReportID"]; ?></td>
                                <td><?php echo $row["NameReport"]; ?></td>
                                <td><?php echo nl2br(htmlspecialchars($row["Description"])); ?></td>
                                <td><?php echo $row["DormName"]; ?></td>
                                <td><?php echo $row["FloorName"]; ?></td>
                                <td><?php echo $row["ReportDate"]; ?></td>
                                <td>
                                    <?php if (!empty($row["Picture"])) { 
                                        // แก้ไขพาธรูปภาพ
                                        $picturePath = $row["Picture"];
                                        // ตรวจสอบว่ามี / นำหน้าแล้วหรือไม่
                                        if (substr($picturePath, 0, 1) !== '/') {
                                            $picturePath = '/' . $picturePath;
                                        }
                                    ?>
                                        <img src="Controllers/uploads<?php echo $picturePath; ?>" class="report-img" onclick="openImageModal(this.src)" alt="รูปภาพปัญหา">
                                    <?php } else { ?>
                                        ไม่มีรูปภาพ
                                    <?php } ?>
                                </td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>

                <!-- Pagination -->
                <div class="pagination">
                    <?php if ($page > 1) { ?>
                        <a href="?page=<?php echo $page - 1; ?>" class="page-link">« ก่อนหน้า</a>
                    <?php } ?>
                    <span class="page-info">หน้า <?php echo $page; ?> จาก <?php echo $total_pages; ?></span>
                    <?php if ($page < $total_pages) { ?>
                        <a href="?page=<?php echo $page + 1; ?>" class="page-link">ถัดไป »</a>
                    <?php } ?>
                </div>

            <?php } else { ?>
                <p class="no-data">ยังไม่มีการแจ้งซ่อม</p>
            <?php } ?>
        </div>
    </div>
    
    <!-- Modal สำหรับแสดงรูปภาพขนาดใหญ่ -->
    <div id="imageModal" class="modal">
        <span class="close" onclick="closeImageModal()">&times;</span>
        <img class="modal-content" id="modalImage">
    </div>

    <script>
        // เปิด Modal แสดงรูปภาพขนาดใหญ่
        function openImageModal(src) {
            document.getElementById('imageModal').style.display = 'block';
            document.getElementById('modalImage').src = src;
        }
        
        // ปิด Modal
        function closeImageModal() {
            document.getElementById('imageModal').style.display = 'none';
        }
        
        // ปิด Modal เมื่อคลิกที่พื้นที่ว่าง
        window.onclick = function(event) {
            var modal = document.getElementById('imageModal');
            if (event.target == modal) {
                modal.style.display = 'none';
            }
        }
        
        // ตรวจสอบพาธรูปภาพที่ไม่ถูกต้อง
        document.addEventListener('DOMContentLoaded', function() {
            var images = document.querySelectorAll('.report-img');
            images.forEach(function(img) {
                img.onerror = function() {
                    // ลองเปลี่ยนพาธเป็นอีกรูปแบบหนึ่ง
                    var originalSrc = img.src;
                    // ลองเอา uploads/ หรือ Controllers/uploads/ ออก
                    if (originalSrc.includes('Controllers/uploads/')) {
                        img.src = originalSrc.replace('Controllers/uploads/', '');
                    } else if (originalSrc.includes('/uploads/')) {
                        img.src = originalSrc.replace('/uploads/', '');
                    }
                    
                    // ถ้ายังไม่ได้ ให้แสดงข้อความแทน
                    img.onerror = function() {
                        img.style.display = 'none';
                        img.parentNode.innerHTML = 'ไม่พบรูปภาพ';
                    };
                };
            });
        });
    </script>
</body>

</html>