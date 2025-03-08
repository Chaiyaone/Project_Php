<?php
session_start(); 

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
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $Email = trim($_POST['Email']);
    $Password = trim($_POST['Password']);

    // ✅ ป้องกัน SQL Injection โดยใช้ Prepared Statement
    $stmt = $conn->prepare("SELECT UserID, Name, Password , RoleID FROM users WHERE Email = ?");
    $stmt->bind_param("s", $Email);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    // ตรวจสอบว่ามี Email ในระบบหรือไม่
    if ($user) {
        if ($Password == $user['Password'] && $user['RoleID'] == 1) {
            // ✅ Login สำเร็จ -> สร้าง Session และ Redirect ไปหน้า Welcome
            $_SESSION["UserID"] = $user["UserID"];
            $_SESSION["UserName"] = $user["Name"];
            $_SESSION["RoleID"] = $user["RoleID"];

            header("Location: ../../User.php?UserID=" . $user["UserID"]);
            exit();
        }
        if ($Password == $user['Password'] && $user['RoleID'] == 2){
            $_SESSION["UserID"] = $user["UserID"];
            $_SESSION["UserName"] = $user["Name"];

            header("Location: ../../Admin.php?UserID=" . $user["UserID"]);
            exit();
        } else {
            // ❌ รหัสผ่านไม่ถูกต้อง
            echo '<script>
                    alert("รหัสผ่านไม่ถูกต้อง!");
                    window.location.href = "../../login.php";
                  </script>';
        }
    } else {
        // ❌ ไม่พบผู้ใช้
        echo '<script>
                alert("ไม่พบบัญชีผู้ใช้นี้!");
                window.location.href = "../../login.php";
              </script>';
    }

    $stmt->close();
    $conn->close();
}
?>
