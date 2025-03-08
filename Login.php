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
</head>

<body>
    <div class="register-container">
        <h1>Login</h1>
        <form enctype="multipart/form-data" name="save" method="post" action="Controllers/Users/CheckUser.php">
            <div class="form-group">
                <label>อีเมล:</label>
                <input type="text" name="Email" required>
                <label>รหัสผ่าน:</label>
                <input type="password" name="Password" required>
            </div>
            <input type="submit" name="submit" class="btn" value="Login" style="cursor:hand;">
            
        </form>
        <br>
        <a href="Register.php"><p align="left">ไม่มีบัญชี?</p></a>
    </div>
</body>

</html>