<?php
$hostname = "localhost";
$username = "root";
$password = "";
$dbname = "repair_system";

$conn = new mysqli($hostname, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $Name = $_POST['Name'];
    $Email = $_POST['Email'];
    $Password = $_POST['Password'];

    // ✅ ตรวจสอบว่าอีเมลนี้มีอยู่แล้วหรือไม่
    $stmt = $conn->prepare("SELECT * FROM users WHERE Email = ?");
    $stmt->bind_param("s", $Email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // ❌ ถ้าอีเมลมีอยู่แล้ว ให้แจ้งเตือนและอยู่หน้าเดิม
        echo '<script>
                alert("อีเมลนี้ถูกใช้งานแล้ว!");
                window.location.href = "../../Register.php";
              </script>';
    } else {
        // ✅ ถ้าอีเมลยังไม่มี ให้เพิ่มลงในฐานข้อมูล
        $stmt = $conn->prepare("INSERT INTO users (Name, Email, Password) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $Name, $Email, $Password);
        if ($stmt->execute()) {
            // ✅ สมัครสำเร็จ → แจ้งเตือน และอยู่ที่ Register.php
            echo '<script>
                    alert("สมัครสมาชิกสำเร็จ!");
                    window.location.href = "../../Register.php";
                  </script>';
        } else {
            // ❌ เกิดข้อผิดพลาด
            echo '<script>
                    alert("เกิดข้อผิดพลาดในการสมัครสมาชิก!");
                    window.location.href = "../../Register.php";
                  </script>';
        }
    }

    $stmt->close();
    $conn->close();
}
?>
