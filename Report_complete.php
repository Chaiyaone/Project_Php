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

// อนุมัติแจ้งซ่อม
if (isset($_POST['approve'])) {
    $ReportID = $_POST['ReportID'];
    $stmt = $conn->prepare("INSERT INTO report_completed (ReportID, NameReport, Description, ReportDate) SELECT ReportID, NameReport, Description, ReportDate FROM reports WHERE ReportID = ?");
    $stmt->bind_param("i", $ReportID);
    $stmt->execute();
    
    $stmt = $conn->prepare("DELETE FROM reports WHERE ReportID = ?");
    $stmt->bind_param("i", $ReportID);
    $stmt->execute();
    
    header("Location: ManageReports.php");
}

// ดึงข้อมูลแจ้งซ่อมทั้งหมด
$reports = $conn->query("SELECT * FROM reports");

// ดึงข้อมูลแจ้งซ่อมที่อนุมัติแล้ว
$completed_reports = $conn->query("SELECT * FROM report_completed ORDER BY ReportDate DESC");
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>จัดการปัญหาผู้ใช้</title>
    <style>
        * {
            box-sizing: border-box;
            font-family: 'Prompt', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
        }
        
        body {
            background-color: #f5f5f5;
            color: #333;
            margin: 0;
            padding: 20px;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            padding: 20px;
            margin-bottom: 30px;
        }
        
        h1, h2 {
            color: #2c3e50;
            text-align: center;
            margin-bottom: 30px;
        }
        
        h1 {
            font-size: 28px;
        }
        
        h2 {
            font-size: 24px;
            margin-top: 10px;
        }
        
        .reports-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            overflow: hidden;
        }
        
        .reports-table th {
            background-color: #3498db;
            color: white;
            font-weight: bold;
            text-align: left;
            padding: 12px 15px;
            font-size: 16px;
        }
        
        .completed-table th {
            background-color: #27ae60;
        }
        
        .reports-table td {
            padding: 12px 15px;
            border-bottom: 1px solid #ddd;
            font-size: 15px;
        }
        
        .reports-table tr:hover {
            background-color: #f1f9ff;
        }
        
        .reports-table tr:nth-child(even) {
            background-color: #f8f9fa;
        }
        
        .action-button {
            background-color: #2ecc71;
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 4px;
            cursor: pointer;
            transition: background-color 0.3s;
            font-size: 14px;
        }
        
        .action-button:hover {
            background-color: #27ae60;
        }
        
        .description-cell {
            max-width: 300px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        
        .empty-state {
            text-align: center;
            padding: 30px;
            color: #7f8c8d;
        }
        
        .tab-container {
            display: flex;
            justify-content: center;
            margin-bottom: 20px;
        }
        
        .tab {
            padding: 10px 20px;
            background-color: #e0e0e0;
            border: none;
            cursor: pointer;
            font-size: 16px;
            border-radius: 5px;
            margin: 0 10px;
            transition: background-color 0.3s;
        }
        
        .tab.active {
            background-color: #3498db;
            color: white;
        }
        
        .tab:hover {
            background-color: #d0d0d0;
        }
        
        .tab.active:hover {
            background-color: #2980b9;
        }
        
        .table-container {
            display: none;
        }
        
        .table-container.active {
            display: block;
        }
        
        .status-badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: bold;
        }
        
        .status-badge.completed {
            background-color: #e8f8f5;
            color: #27ae60;
            border: 1px solid #27ae60;
        }
        
        @media (max-width: 768px) {
            .reports-table {
                display: block;
                overflow-x: auto;
            }
            
            .container {
                padding: 15px;
            }
            
            h1 {
                font-size: 24px;
            }
            
            h2 {
                font-size: 20px;
            }
            
            .tab {
                padding: 8px 15px;
                font-size: 14px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>จัดการปัญหาผู้ใช้</h1>
        
        <div class="tab-container">
            <button class="tab active" onclick="showTable('pending')">รายการแจ้งซ่อม</button>
            <button class="tab" onclick="showTable('completed')">รายการที่อนุมัติแล้ว</button>
        </div>
        
        <div id="pending-table" class="table-container active">
            <h2>รายการแจ้งซ่อมที่รอดำเนินการ</h2>
            <table class="reports-table">
                <thead>
                    <tr>
                        <th>หมายเลข</th>
                        <th>หัวข้อ</th>
                        <th>รายละเอียด</th>
                        <th>วันที่แจ้ง</th>
                        <th>จัดการ</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    if ($reports && $reports->num_rows > 0) {
                        while ($row = $reports->fetch_assoc()) { 
                    ?>
                    <tr>
                        <td><?php echo $row['ReportID']; ?></td>
                        <td><?php echo htmlspecialchars($row['NameReport']); ?></td>
                        <td class="description-cell" title="<?php echo htmlspecialchars($row['Description']); ?>">
                            <?php echo htmlspecialchars($row['Description']); ?>
                        </td>
                        <td><?php echo $row['ReportDate']; ?></td>
                        <td>
                            <form method="POST">
                                <input type="hidden" name="ReportID" value="<?php echo $row['ReportID']; ?>">
                                <button type="submit" name="approve" class="action-button">อนุมัติ</button>
                            </form>
                        </td>
                    </tr>
                    <?php 
                        }
                    } else {
                    ?>
                    <tr>
                        <td colspan="5" class="empty-state">ไม่พบข้อมูลการแจ้งซ่อม</td>
                    </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
        
        <div id="completed-table" class="table-container">
            <h2>รายการที่อนุมัติแล้ว</h2>
            <table class="reports-table completed-table">
                <thead>
                    <tr>
                        <th>หมายเลข</th>
                        <th>หัวข้อ</th>
                        <th>รายละเอียด</th>
                        <th>วันที่แจ้ง</th>
                        <th>สถานะ</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    if ($completed_reports && $completed_reports->num_rows > 0) {
                        while ($row = $completed_reports->fetch_assoc()) { 
                    ?>
                    <tr>
                        <td><?php echo $row['ReportID']; ?></td>
                        <td><?php echo htmlspecialchars($row['NameReport']); ?></td>
                        <td class="description-cell" title="<?php echo htmlspecialchars($row['Description']); ?>">
                            <?php echo htmlspecialchars($row['Description']); ?>
                        </td>
                        <td><?php echo $row['ReportDate']; ?></td>
                        <td><span class="status-badge completed">อนุมัติแล้ว</span></td>
                    </tr>
                    <?php 
                        }
                    } else {
                    ?>
                    <tr>
                        <td colspan="5" class="empty-state">ไม่พบข้อมูลการแจ้งซ่อมที่อนุมัติแล้ว</td>
                    </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>

    <script>
        function showTable(tableType) {
            // Hide all tables
            document.querySelectorAll('.table-container').forEach(function(container) {
                container.classList.remove('active');
            });
            
            // Show selected table
            document.getElementById(tableType + '-table').classList.add('active');
            
            // Update tab active state
            document.querySelectorAll('.tab').forEach(function(tab) {
                tab.classList.remove('active');
            });
            
            // Set clicked tab as active
            event.target.classList.add('active');
        }
    </script>
</body>
</html>