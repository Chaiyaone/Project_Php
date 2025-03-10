<?php
session_start();
if (!isset($_SESSION["UserID"])) {
    header("Location: ../../login.php");
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

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $UserID = $_SESSION["UserID"];
    $FloorID = $_POST["FloorID"];
    $DormID = $_POST["DormID"];
    $NameReport = $_POST["NameReport"];
    $Description = $_POST["Description"];
    $Picture = $_POST["Picture"];

    // จัดการอัปโหลดรูปภาพ
    if (isset($_FILES["Picture"]) && $_FILES["Picture"]["error"] == 0) {
        $upload_dir = "../../uploads/";
        
        // สร้างโฟลเดอร์ถ้ายังไม่มี
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        $temp_name = $_FILES["Picture"]["tmp_name"];
        $file_name = time() . "_" . $_FILES["Picture"]["name"];
        $file_path = $upload_dir . $file_name;

        if (move_uploaded_file($temp_name, $file_path)) {
            $Picture = "uploads/" . $file_name;
        } else {
            // กรณีอัปโหลดไฟล์ไม่สำเร็จ
            $_SESSION['alert_message'] = "เกิดข้อผิดพลาดในการอัปโหลดรูปภาพ";
            $_SESSION['alert_type'] = "error";
            header("Location: ../../Report.php");
            exit();
        }
    }

    // เพิ่มข้อมูลลงในฐานข้อมูล
    $RepairStatus = "รอดำเนินการ"; // สถานะเริ่มต้น
    $ReportDate = date("Y-m-d H:i:s"); // วันที่ปัจจุบัน

    $stmt = $conn->prepare("INSERT INTO reports (NameReport, Description, FloorID, UserID, Picture, RepairStatus, ReportDate,DormID) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssiisssi", $NameReport, $Description, $FloorID, $UserID, $Picture, $RepairStatus, $ReportDate, $DormID);

    if ($stmt->execute()) {
        // บันทึกข้อมูลสำเร็จ
        $_SESSION['alert_message'] = "ส่งแบบฟอร์มแจ้งซ่อมเรียบร้อยแล้ว";
        $_SESSION['alert_type'] = "success";
        header("Location: ../../Report.php");
    } else {
        // เกิดข้อผิดพลาด
        $_SESSION['alert_message'] = "เกิดข้อผิดพลาดในการส่งแบบฟอร์ม: " . $stmt->error;
        $_SESSION['alert_type'] = "error";
        header("Location: ../../Report.php");
    }

    $stmt->close();
} else {
    // ถ้าไม่ได้ส่งมาด้วยวิธี POST
    $_SESSION['alert_message'] = "เกิดข้อผิดพลาด: วิธีการส่งคำขอไม่ถูกต้อง";
    $_SESSION['alert_type'] = "error";
    header("Location: ../../Report.php");
}

$conn->close();
?>