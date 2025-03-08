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

// เพิ่มข้อมูล
if (isset($_POST['add_dorm'])) {
    $DormName = $_POST['DormName'];
    $stmt = $conn->prepare("INSERT INTO dorms (DormName) VALUES (?)");
    $stmt->bind_param("s", $DormName);
    $stmt->execute();
    header("Location: ManageDorms.php");
}

// ลบหอพัก
if (isset($_POST['delete_dorm'])) {
    $DormID = $_POST['DormID'];
    $stmt = $conn->prepare("DELETE FROM dorms WHERE DormID = ?");
    $stmt->bind_param("i", $DormID);
    $stmt->execute();
    header("Location: ManageDorms.php");
}

// ดึงข้อมูล
$dorms = $conn->query("SELECT * FROM dorms");
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>จัดการหอพัก</title>
    <link rel="stylesheet" href="css/styleAdmin.css">
</head>
<body>
    <div class="container">
        <h1>จัดการหอพัก</h1>

        <!-- เพิ่มหอพัก -->
        <form method="POST">
            <input type="text" name="DormName" required placeholder="ชื่อหอพัก">
            <button type="submit" name="add_dorm">เพิ่มหอพัก</button>
        </form>

        <table>
            <tr>
                <th>DormID</th>
                <th>ชื่อหอพัก</th>
                <th>จัดการ</th>
            </tr>
            <?php while ($row = $dorms->fetch_assoc()) { ?>
            <tr>
                <td><?php echo $row['DormID']; ?></td>
                <td><?php echo $row['DormName']; ?></td>
                <td>
                    <form method="POST" style="display:inline;">
                        <input type="hidden" name="DormID" value="<?php echo $row['DormID']; ?>">
                        <button type="submit" name="delete_dorm">ลบ</button>
                    </form>
                </td>
            </tr>
            <?php } ?>
        </table>
    </div>
</body>
</html>
