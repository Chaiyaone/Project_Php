<?php
session_start();
if (!isset($_SESSION["UserID"])) {
    header("Location: login.php");
    exit();
}

$conn = new mysqli("localhost", "root", "", "repair_system");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// อนุมัติแจ้งซ่อม
if (isset($_POST['approve'])) {
    $ReportID = $_POST['ReportID'];
    $stmt = $conn->prepare("INSERT INTO report_completed (ReportID, NameReport, Description, Created_at) SELECT ReportID, NameReport, Description, Created_at FROM reports WHERE ReportID = ?");
    $stmt->bind_param("i", $ReportID);
    $stmt->execute();

    $stmt = $conn->prepare("DELETE FROM reports WHERE ReportID = ?");
    $stmt->bind_param("i", $ReportID);
    $stmt->execute();

    header("Location: ManageReports.php");
}

// ดึงข้อมูลแจ้งซ่อมทั้งหมด
$reports = $conn->query("SELECT * FROM reports");
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>จัดการปัญหาผู้ใช้</title>
    <link rel="stylesheet" href="css/styleAdmin.css">
</head>
<body>
    <div class="container">
        <h1>จัดการปัญหาผู้ใช้</h1>
        <table>
            <tr>
                <th>หมายเลข</th>
                <th>หัวข้อ</th>
                <th>รายละเอียด</th>
                <th>วันที่แจ้ง</th>
                <th>จัดการ</th>
            </tr>
            <?php while ($row = $reports->fetch_assoc()) { ?>
            <tr>
                <td><?php echo $row['ReportID']; ?></td>
                <td><?php echo $row['NameReport']; ?></td>
                <td><?php echo $row['Description']; ?></td>
                <td><?php echo $row['Created_at']; ?></td>
                <td>
                    <form method="POST">
                        <input type="hidden" name="ReportID" value="<?php echo $row['ReportID']; ?>">
                        <button type="submit" name="approve">อนุมัติ</button>
                    </form>
                </td>
            </tr>
            <?php } ?>
        </table>
    </div>
</body>
</html>
