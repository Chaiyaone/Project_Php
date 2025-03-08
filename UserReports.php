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
$sql = "SELECT r.ReportID, r.NameReport, r.Description, r.Picture, r.Created_at, 
               f.FloorName, d.DormName 
        FROM reports r
        JOIN floors f ON r.FloorID = f.FloorID
        JOIN dorms d ON r.DormID = d.DormID
        WHERE r.UserID = ?
        ORDER BY r.Created_at DESC
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
                                <td><?php echo $row["Description"]; ?></td>
                                <td><?php echo $row["DormName"]; ?></td>
                                <td><?php echo $row["FloorName"]; ?></td>
                                <td><?php echo $row["Created_at"]; ?></td>
                                <td>
                                    <?php if (!empty($row["Picture"])) { ?>
                                        <img src="uploads/<?php echo $row["Picture"]; ?>" alt="รูปภาพแจ้งซ่อม" class="report-img">
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
</body>

</html>