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

// ลบผู้ใช้
if (isset($_POST['delete_user'])) {
    $UserID = $_POST['UserID'];
    $stmt = $conn->prepare("DELETE FROM users WHERE UserID = ?");
    $stmt->bind_param("i", $UserID);
    $stmt->execute();
    header("Location: ManageUsers.php");
}

// ดึงข้อมูลผู้ใช้ทั้งหมด
$sql = "SELECT * FROM users";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>จัดการผู้ใช้งาน</title>
    <link rel="stylesheet" href="css/styleAdmin.css">
</head>
<body>
    <div class="container">
        <h1>จัดการผู้ใช้งาน</h1>
        <table>
            <tr>
                <th>UserID</th>
                <th>ชื่อ</th>
                <th>อีเมล</th>
                <th>จัดการ</th>
            </tr>
            <?php while ($row = $result->fetch_assoc()) { ?>
            <tr>
                <td><?php echo $row['UserID']; ?></td>
                <td><?php echo $row['Name']; ?></td>
                <td><?php echo $row['Email']; ?></td>
                <td>
                    <a href="EditUser.php?UserID=<?php echo $row['UserID']; ?>">แก้ไข</a>
                    <form method="POST" style="display:inline;">
                        <input type="hidden" name="UserID" value="<?php echo $row['UserID']; ?>">
                        <button type="submit" name="delete_user">ลบ</button>
                    </form>
                </td>
            </tr>
            <?php } ?>
        </table>
    </div>
</body>
</html>
