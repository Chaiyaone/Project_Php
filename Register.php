<?php
$hostname = "localhost";
$username = "root";
$password = "";
$dbname = "repair_system";

$conn = mysqli_connect($hostname, $username, $password);
if (!$conn)
    die("ไม่สามารถติดต่อกับ MySQL ได้");
mysqli_select_db($conn, $dbname) or die("ไม่สามารถเลือกฐานข้อมูล itbookได้");

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HELLO</title>
    <link rel="stylesheet" href="css/authCss/register.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;500;700&display=swap" rel="stylesheet">
    <script>
        function validateForm(event){
            let password = document.forms["save"]["Password"].value;
            let confirmPassword = document.forms["save"]["Confirm-Password"].value;
            let errorMessage = "";

            if (password !== confirmPassword) {
                errorMessage += "รหัสผ่านและยืนยันรหัสผ่านไม่ตรงกัน\n";
            }
            if (errorMessage !== "") {
                alert(errorMessage);
                event.preventDefault(); // ป้องกันฟอร์มส่งข้อมูลไปที่ InsertUser.php
                return false;
            }
            return true;
        }
    </script>
</head>

<body>
    
    <div class="register-container">
        <h1>Register</h1>
        <form enctype="multipart/form-data" name="save" method="post" action="Controllers/Users/InsertUser.php" onsubmit="return validateForm(event)">
            <div class="form-group">
                <label>ชื่อ:</label>
                <input type="text" name="Name" required>
                <label>อีเมล:</label>
                <input type="text" name="Email" required>
                <span id="email-feedback" style="font-size: 12px;"></span>
                <label>รหัสผ่าน:</label>
                <input type="password" name="Password" required>
                <label>ยืนยันรหัสผ่าน:</label>
                <input type="password" name="Confirm-Password" required>
            </div>
            <input type="submit" name="submit" class="btn" value="Register" style="cursor:hand;">
        </form>
        <br>
        <a href="Login.php"><p align="left">มีบัญชีแล้ว?</p></a>
    </div>
</body>

</html>