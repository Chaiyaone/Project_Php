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

// เพิ่มข้อมูลหอพัก
if (isset($_POST['add_dorm'])) {
    $DormName = $_POST['DormName'];
    $stmt = $conn->prepare("INSERT INTO dorms (DormName) VALUES (?)");
    $stmt->bind_param("s", $DormName);
    $stmt->execute();
    header("Location: ManageDorms.php");
}

// เพิ่มข้อมูลชั้น
if (isset($_POST['add_floor'])) {
    $FloorName = $_POST['FloorName'];
    $stmt = $conn->prepare("INSERT INTO floors (FloorName) VALUES (?)");
    $stmt->bind_param("s", $FloorName);
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

// ลบชั้น
if (isset($_POST['delete_floor'])) {
    $FloorID = $_POST['FloorID'];
    $stmt = $conn->prepare("DELETE FROM floors WHERE FloorID = ?");
    $stmt->bind_param("i", $FloorID);
    $stmt->execute();
    header("Location: ManageDorms.php");
}

// ดึงข้อมูลหอพัก
$dorms = $conn->query("SELECT * FROM dorms");
// ดึงข้อมูลชั้น
$floors = $conn->query("SELECT * FROM floors");
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>จัดการหอพักและชั้น</title>
    <style>
        /* Base styles for the container */
        .container {
            max-width: 1000px;
            margin: 0 auto;
            padding: 20px;
            font-family: 'Kanit', 'Prompt', sans-serif;
        }

        h1, h2 {
            color: #2c3e50;
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 10px;
            border-bottom: 2px solid #007bff;
        }

        h2 {
            margin-top: 40px;
        }

        /* Form styling */
        form {
            margin-bottom: 30px;
            display: flex;
            gap: 10px;
        }

        input[type="text"] {
            flex: 1;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }

        button {
            padding: 10px 15px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        button:hover {
            background-color: #2980b9;
        }

        button[name="delete_dorm"], button[name="delete_floor"] {
            background-color: #e74c3c;
        }

        button[name="delete_dorm"]:hover, button[name="delete_floor"]:hover {
            background-color: #c0392b;
        }

        /* Table styling */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        th, td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        th {
            background-color: #3498db;
            color: white;
            font-weight: 500;
        }

        tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        tr:hover {
            background-color: #f1f1f1;
        }

        /* Back button styling */
        .back-button {
            display: inline-block;
            margin-bottom: 20px;
            padding: 10px 15px;
            background-color: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            transition: background-color 0.3s;
        }

        .back-button:hover {
            background-color: #27ae60;
        }

        .section {
            margin-bottom: 40px;
            padding: 20px;
            background-color: #f8f9fa;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            form {
                flex-direction: column;
            }
            
            table {
                font-size: 14px;
            }
            
            th, td {
                padding: 8px 10px;
            }
        }
    </style>
    <link rel="stylesheet" href="css/styleAdmin.css">
</head>
<body>
    <div class="container">
        <a href="admin.php" class="back-button">← กลับไปหน้าผู้ดูแลระบบ</a>
        
        <h1>จัดการหอพักและชั้น</h1>
        
        <div class="section">
            <h2>จัดการหอพัก</h2>
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
                        <form method="POST" style="display:inline; margin: 0;">
                            <input type="hidden" name="DormID" value="<?php echo $row['DormID']; ?>">
                            <button type="submit" name="delete_dorm">ลบ</button>
                        </form>
                    </td>
                </tr>
                <?php } ?>
            </table>
        </div>
        
        <div class="section">
            <h2>จัดการชั้น</h2>
            <!-- เพิ่มชั้น -->
            <form method="POST">
                <input type="text" name="FloorName" required placeholder="ชื่อชั้น">
                <button type="submit" name="add_floor">เพิ่มชั้น</button>
            </form>
            
            <table>
                <tr>
                    <th>FloorID</th>
                    <th>ชื่อชั้น</th>
                    <th>จัดการ</th>
                </tr>
                <?php while ($row = $floors->fetch_assoc()) { ?>
                <tr>
                    <td><?php echo $row['FloorID']; ?></td>
                    <td><?php echo $row['FloorName']; ?></td>
                    <td>
                        <form method="POST" style="display:inline; margin: 0;">
                            <input type="hidden" name="FloorID" value="<?php echo $row['FloorID']; ?>">
                            <button type="submit" name="delete_floor">ลบ</button>
                        </form>
                    </td>
                </tr>
                <?php } ?>
            </table>
        </div>
    </div>
</body>
</html>