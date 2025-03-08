<?php
session_start();
if (!isset($_SESSION["UserID"])) {
    header("Location: ../login.php");
    exit();
}

$hostname = "localhost";
$username = "root";
$password = "";
$dbname = "repair_system";

// เชื่อมต่อฐานข้อมูล
$conn = new mysqli($hostname, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// รับค่าจากฟอร์ม
$UserID = $_SESSION["UserID"];
$NameReport = isset($_POST["NameReport"]) ? trim($_POST["NameReport"]) : "";
$Description = isset($_POST["Description"]) ? trim($_POST["Description"]) : "";
$FloorID = isset($_POST["FloorID"]) ? intval($_POST["FloorID"]) : 0;
$DormID = isset($_POST["DormID"]) ? intval($_POST["DormID"]) : 0;
$Picture = NULL;

// ตรวจสอบว่ามีการอัปโหลดไฟล์รูปภาพหรือไม่
if (!empty($_FILES["Picture"]["name"])) {
    $targetDir = "../uploads/";
    $fileName = time() . "_" . basename($_FILES["Picture"]["name"]);
    $targetFilePath = $targetDir . $fileName;

    // ตรวจสอบและย้ายไฟล์
    if (move_uploaded_file($_FILES["Picture"]["tmp_name"], $targetFilePath)) {
        $Picture = $fileName;
    } else {
        die("เกิดข้อผิดพลาดในการอัปโหลดไฟล์");
    }
}

// SQL Insert
$sql = "INSERT INTO reports (NameReport, Description, FloorID, UserID, Picture, DormID) 
        VALUES (?, ?, ?, ?, ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ssiisi", $NameReport, $Description, $FloorID, $UserID, $Picture, $DormID);

if ($stmt->execute()) {
    echo "บันทึกข้อมูลสำเร็จ";
    header("Location: ../../Report.php?UserID=" . $user["UserID"]);
} else {
    echo "เกิดข้อผิดพลาด: " . $stmt->error;
}

$stmt->close();
$conn->close();
?>
