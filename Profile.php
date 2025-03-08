<?php
session_start();

// ตรวจสอบว่าผู้ใช้ Login หรือยัง
if (!isset($_SESSION["UserID"])) {
    header("Location: login.php");
    exit();
}

$UserID = $_SESSION["UserID"];
$UserName = $_SESSION["UserName"];
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>โปรไฟล์</title>
</head>
<body>
    <h1>โปรไฟล์ของ <?php echo htmlspecialchars($UserName); ?></h1>
    <p>รหัสผู้ใช้ของคุณคือ <?php echo $UserID; ?></p>
    <p><a href="dashboard.php?UserID=<?php echo $UserID; ?>">กลับไปหน้าหลัก</a></p>
</body>
</html>
