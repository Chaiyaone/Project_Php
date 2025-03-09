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

// ตัวแปรสำหรับข้อความแจ้งเตือน
$message = "";
$messageType = "";

// ลบผู้ใช้ 
if (isset($_POST['delete_user'])) {     
    $UserID = $_POST['UserID'];
    
    // ตรวจสอบว่าไม่ใช่การลบตัวเอง
    if ($_SESSION["UserID"] != $UserID) {
        // เริ่มต้น transaction เพื่อให้แน่ใจว่าการลบข้อมูลที่เกี่ยวข้องทั้งหมดเสร็จสมบูรณ์
        $conn->begin_transaction();
        
        try {
            // ลบรายงานที่เกี่ยวข้องกับผู้ใช้ (ถ้ามี)
            $stmt = $conn->prepare("DELETE FROM reports WHERE UserID = ?");     
            $stmt->bind_param("i", $UserID);     
            $stmt->execute();
            $stmt->close();
            
            // ลบข้อมูลอื่นๆ ที่เกี่ยวข้องกับผู้ใช้ (ถ้ามี)
            // เช่น ข้อมูลโปรไฟล์, การตั้งค่า, ฯลฯ
            
            // ลบข้อมูลผู้ใช้
            $stmt = $conn->prepare("DELETE FROM users WHERE UserID = ?");     
            $stmt->bind_param("i", $UserID);     
            $stmt->execute();
            
            // ยืนยัน transaction
            $conn->commit();
            
            $message = "ลบผู้ใช้เรียบร้อยแล้ว";
            $messageType = "success";
        } catch (Exception $e) {
            // ยกเลิก transaction หากมีข้อผิดพลาด
            $conn->rollback();
            
            $message = "เกิดข้อผิดพลาดในการลบผู้ใช้: " . $e->getMessage();
            $messageType = "error";
        }
    } else {
        $message = "ไม่สามารถลบบัญชีผู้ใช้ของตนเองได้";
        $messageType = "error";
    }
}

// ลบผู้ใช้หลายคนพร้อมกัน
if (isset($_POST['delete_selected'])) {
    if (isset($_POST['selected_users']) && is_array($_POST['selected_users'])) {
        $selected_users = $_POST['selected_users'];
        
        // เริ่มต้น transaction
        $conn->begin_transaction();
        
        try {
            foreach ($selected_users as $UserID) {
                // ข้ามถ้าเป็นตัวเอง
                if ($_SESSION["UserID"] == $UserID) {
                    continue;
                }
                
                // ลบรายงานที่เกี่ยวข้องกับผู้ใช้
                $stmt = $conn->prepare("DELETE FROM reports WHERE UserID = ?");     
                $stmt->bind_param("i", $UserID);     
                $stmt->execute();
                $stmt->close();
                
                // ลบข้อมูลผู้ใช้
                $stmt = $conn->prepare("DELETE FROM users WHERE UserID = ?");     
                $stmt->bind_param("i", $UserID);     
                $stmt->execute();
                $stmt->close();
            }
            
            // ยืนยัน transaction
            $conn->commit();
            
            $message = "ลบผู้ใช้ที่เลือกเรียบร้อยแล้ว";
            $messageType = "success";
        } catch (Exception $e) {
            // ยกเลิก transaction หากมีข้อผิดพลาด
            $conn->rollback();
            
            $message = "เกิดข้อผิดพลาดในการลบผู้ใช้: " . $e->getMessage();
            $messageType = "error";
        }
    } else {
        $message = "กรุณาเลือกผู้ใช้ที่ต้องการลบ";
        $messageType = "error";
    }
}

// แก้ไขข้อมูลผู้ใช้และอัพเดทสถานะ admin
if (isset($_POST['update_user'])) {
    $UserID = $_POST['UserID'];
    $Name = $_POST['Name'];
    $Email = $_POST['Email'];
    $isAdmin = isset($_POST['isAdmin']) ? 1 : 0;
    
    try {
        // อัพเดทข้อมูลผู้ใช้และสถานะ admin
        $stmt = $conn->prepare("UPDATE users SET Name = ?, Email = ?, isAdmin = ? WHERE UserID = ?");
        $stmt->bind_param("ssii", $Name, $Email, $isAdmin, $UserID);
        $stmt->execute();
        
        if ($stmt->affected_rows > 0) {
            $message = "อัพเดทข้อมูลผู้ใช้เรียบร้อยแล้ว";
            $messageType = "success";
        } else {
            $message = "ไม่มีการเปลี่ยนแปลงข้อมูล";
            $messageType = "info";
        }
        
        $stmt->close();
    } catch (Exception $e) {
        $message = "เกิดข้อผิดพลาดในการอัพเดทข้อมูล: " . $e->getMessage();
        $messageType = "error";
    }
}

// อัพเดทสถานะ admin ให้กับผู้ใช้ที่เลือก
if (isset($_POST['update_admin_status'])) {
    if (isset($_POST['selected_users']) && is_array($_POST['selected_users'])) {
        $selected_users = $_POST['selected_users'];
        $admin_status = $_POST['admin_action'];
        $status_value = ($admin_status == 'make_admin') ? 1 : 0;
        
        // เริ่มต้น transaction
        $conn->begin_transaction();
        
        try {
            foreach ($selected_users as $UserID) {
                $stmt = $conn->prepare("UPDATE users SET isAdmin = ? WHERE UserID = ?");
                $stmt->bind_param("ii", $status_value, $UserID);
                $stmt->execute();
                $stmt->close();
            }
            
            // ยืนยัน transaction
            $conn->commit();
            
            $status_text = ($admin_status == 'make_admin') ? "ตั้งเป็นผู้ดูแลระบบ" : "ยกเลิกสิทธิ์ผู้ดูแลระบบ";
            $message = "อัพเดทสถานะผู้ใช้ที่เลือกเป็น" . $status_text . "เรียบร้อยแล้ว";
            $messageType = "success";
        } catch (Exception $e) {
            // ยกเลิก transaction หากมีข้อผิดพลาด
            $conn->rollback();
            
            $message = "เกิดข้อผิดพลาดในการอัพเดทสถานะ: " . $e->getMessage();
            $messageType = "error";
        }
    } else {
        $message = "กรุณาเลือกผู้ใช้ที่ต้องการอัพเดทสถานะ";
        $messageType = "error";
    }
}

// ดึงข้อมูลผู้ใช้ทั้งหมด 
$sql = "SELECT * FROM users ORDER BY UserID"; 
$result = $conn->query($sql); 
?>  

<!DOCTYPE html> 
<html lang="th"> 
<head>     
    <meta charset="UTF-8">     
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>จัดการผู้ใช้งาน</title>     
    <link rel="stylesheet" href="css/styleAdmin.css">
    <style>
        body {
            font-family: 'Kanit', sans-serif;
            background-color: #f5f5f5;
            margin: 0;
            padding: 20px;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        
        h1 {
            color: #333;
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #4CAF50;
            padding-bottom: 10px;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        
        th, td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        
        th {
            background-color: #4CAF50;
            color: white;
            font-weight: 500;
        }
        
        tr:hover {
            background-color: #f1f1f1;
        }
        
        .action-btn {
            display: inline-block;
            padding: 6px 12px;
            text-decoration: none;
            border-radius: 4px;
            margin-right: 5px;
            color: white;
            font-size: 14px;
            cursor: pointer;
        }
        
        .edit-btn {
            background-color: #2196F3;
        }
        
        .delete-btn {
            background-color: #f44336;
            border: none;
        }
        
        .controls {
            margin: 20px 0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .back-btn {
            background-color: #4CAF50;
            color: white;
            padding: 8px 15px;
            text-decoration: none;
            border-radius: 4px;
            display: inline-block;
        }
        
        .delete-selected-btn, .admin-action-btn {
            background-color: #f44336;
            color: white;
            padding: 8px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            margin-right: 5px;
        }
        
        .admin-action-btn {
            background-color: #FF9800;
        }
        
        .message {
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 4px;
        }
        
        .success {
            background-color: #dff0d8;
            color: #3c763d;
            border: 1px solid #d6e9c6;
        }
        
        .error {
            background-color: #f2dede;
            color: #a94442;
            border: 1px solid #ebccd1;
        }
        
        .info {
            background-color: #d9edf7;
            color: #31708f;
            border: 1px solid #bce8f1;
        }
        
        .search-box {
            padding: 8px;
            width: 300px;
            border: 1px solid #ddd;
            border-radius: 4px;
            margin-right: 10px;
        }
        
        .select-all-container {
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 10px;
        }
        
        .checkbox-cell {
            width: 40px;
            text-align: center;
        }
        
        /* Modal styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
        }
        
        .modal-content {
            background-color: #fefefe;
            margin: 10% auto;
            padding: 20px;
            border-radius: 8px;
            width: 50%;
            max-width: 500px;
        }
        
        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
        }
        
        .close:hover {
            color: black;
        }
        
        .form-group {
            margin-bottom: 15px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 500;
        }
        
        .form-group input[type="text"],
        .form-group input[type="email"] {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        
        .form-actions {
            text-align: right;
            margin-top: 20px;
        }
        
        .submit-btn {
            background-color: #4CAF50;
            color: white;
            padding: 8px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        
        .cancel-btn {
            background-color: #ccc;
            color: black;
            padding: 8px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            margin-right: 10px;
        }
        
        .admin-badge {
            background-color: #FF9800;
            color: white;
            padding: 3px 6px;
            border-radius: 3px;
            font-size: 12px;
            margin-left: 5px;
        }
        
        /* เพิ่มความตอบสนองสำหรับอุปกรณ์มือถือ */
        @media screen and (max-width: 768px) {
            .controls {
                flex-direction: column;
                gap: 10px;
            }
            
            .search-box {
                width: 100%;
                margin-bottom: 10px;
            }
            
            table {
                border: 0;
            }
            
            table thead {
                display: none;
            }
            
            table tr {
                margin-bottom: 10px;
                display: block;
                border: 1px solid #ddd;
                border-radius: 4px;
            }
            
            table td {
                display: block;
                text-align: right;
                border-bottom: 1px solid #ddd;
                position: relative;
                padding-left: 50%;
            }
            
            table td:before {
                content: attr(data-label);
                position: absolute;
                left: 12px;
                font-weight: bold;
            }
            
            .checkbox-cell {
                text-align: right;
                width: auto;
            }
            
            .modal-content {
                width: 90%;
            }
            
            .select-all-container {
                flex-direction: column;
                align-items: flex-start;
            }
        }
    </style>
</head> 
<body>     
    <div class="container">         
        <h1>จัดการผู้ใช้งาน</h1>
        
        <?php if (!empty($message)): ?>
            <div class="message <?php echo $messageType; ?>">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>
        
        <form method="POST" id="user-management-form">
            <div class="controls">
                <div>
                    <a href="Admin.php" class="back-btn">กลับไปหน้าหลัก</a>
                </div>
                <div>
                    <input type="text" id="searchInput" class="search-box" placeholder="ค้นหาผู้ใช้...">
                </div>
            </div>
            
            <?php if ($result->num_rows > 0) { ?>
                <div class="select-all-container">
                    <label>
                        <input type="checkbox" id="select-all"> เลือกทั้งหมด
                    </label>
                    <button type="submit" name="delete_selected" class="delete-selected-btn" onclick="return confirmDeleteSelected();">ลบผู้ใช้ที่เลือก</button>
                    <button type="button" onclick="showAdminActionModal()" class="admin-action-btn">จัดการสิทธิ์ผู้ใช้ที่เลือก</button>
                </div>
                
                <table id="users-table">             
                    <thead>
                        <tr>
                            <th class="checkbox-cell"><input type="checkbox" id="select-all-head"></th>
                            <th>UserID</th>                 
                            <th>ชื่อ</th>                 
                            <th>อีเมล</th>
                            <th>สถานะ</th>                 
                            <th>จัดการ</th>             
                        </tr>
                    </thead>
                    <tbody>             
                        <?php while ($row = $result->fetch_assoc()) { ?>             
                        <tr>
                            <td class="checkbox-cell">
                                <input type="checkbox" name="selected_users[]" value="<?php echo $row['UserID']; ?>" <?php echo ($row['UserID'] == $_SESSION["UserID"]) ? 'disabled' : ''; ?>>
                            </td>
                            <td data-label="UserID"><?php echo $row['UserID']; ?></td>                 
                            <td data-label="ชื่อ"><?php echo $row['Name']; ?></td>                 
                            <td data-label="อีเมล"><?php echo $row['Email']; ?></td>
                            <td data-label="สถานะ">
                                <?php echo (isset($row['isAdmin']) && $row['isAdmin'] == 1) ? 'ผู้ใช้ <span class="admin-badge">ADMIN</span>' : 'ผู้ใช้'; ?>
                            </td>                  
                            <td data-label="จัดการ">                     
                                <button type="button" class="action-btn edit-btn" 
                                        onclick="showEditModal(<?php echo $row['UserID']; ?>, '<?php echo htmlspecialchars($row['Name'], ENT_QUOTES); ?>', '<?php echo htmlspecialchars($row['Email'], ENT_QUOTES); ?>', <?php echo isset($row['isAdmin']) ? $row['isAdmin'] : 0; ?>)">
                                    แก้ไข
                                </button>
                                <?php if ($row['UserID'] != $_SESSION["UserID"]) { ?>
                                    <button type="button" class="action-btn delete-btn" 
                                            onclick="confirmDelete(<?php echo $row['UserID']; ?>, '<?php echo htmlspecialchars($row['Name'], ENT_QUOTES); ?>')">
                                        ลบ
                                    </button>
                                <?php } ?>
                            </td>             
                        </tr>             
                        <?php } ?>
                    </tbody>         
                </table>
                
                <!-- Form สำหรับการลบผู้ใช้รายคน -->
                <div id="delete-form-container" style="display:none;">
                    <form id="delete-form" method="POST">
                        <input type="hidden" id="delete-user-id" name="UserID">
                        <input type="hidden" name="delete_user" value="1">
                    </form>
                </div>
            <?php } else { ?>
                <p class="message">ไม่พบข้อมูลผู้ใช้ในระบบ</p>
            <?php } ?>
        </form>
    </div>
    
    <!-- Modal แก้ไขข้อมูลผู้ใช้ -->
    <div id="editModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeEditModal()">&times;</span>
            <h2>แก้ไขข้อมูลผู้ใช้</h2>
            <form method="POST" id="edit-form">
                <input type="hidden" id="edit-user-id" name="UserID">
                <input type="hidden" name="update_user" value="1">
                
                <div class="form-group">
                    <label for="edit-name">ชื่อ:</label>
                    <input type="text" id="edit-name" name="Name" required>
                </div>
                
                <div class="form-group">
                    <label for="edit-email">อีเมล:</label>
                    <input type="email" id="edit-email" name="Email" required>
                </div>
                
                <div class="form-group">
                    <label>
                        <input type="checkbox" id="edit-is-admin" name="isAdmin">
                        กำหนดเป็นผู้ดูแลระบบ (Admin)
                    </label>
                </div>
                
                <div class="form-actions">
                    <button type="button" class="cancel-btn" onclick="closeEditModal()">ยกเลิก</button>
                    <button type="submit" class="submit-btn">บันทึก</button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Modal จัดการสิทธิ์ผู้ใช้ -->
    <div id="adminActionModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeAdminActionModal()">&times;</span>
            <h2>จัดการสิทธิ์ผู้ใช้ที่เลือก</h2>
            <p>เลือกการดำเนินการกับผู้ใช้ที่เลือก:</p>
            
            <form method="POST" id="admin-action-form">
                <input type="hidden" name="update_admin_status" value="1">
                
                <!-- เพิ่ม input hidden ที่จะส่งรายการผู้ใช้ที่เลือก -->
                <div id="selected-users-container"></div>
                
                <div class="form-group">
                    <label>
                        <input type="radio" name="admin_action" value="make_admin" checked>
                        กำหนดเป็นผู้ดูแลระบบ (Admin)
                    </label>
                </div>
                
                <div class="form-group">
                    <label>
                        <input type="radio" name="admin_action" value="remove_admin">
                        ยกเลิกสิทธิ์ผู้ดูแลระบบ
                    </label>
                </div>
                
                <div class="form-actions">
                    <button type="button" class="cancel-btn" onclick="closeAdminActionModal()">ยกเลิก</button>
                    <button type="submit" class="submit-btn">ดำเนินการ</button>
                </div>
            </form>
        </div>
    </div>
    
    <script>
        // ฟังก์ชันยืนยันการลบผู้ใช้
        function confirmDelete(userID, userName) {
            if (confirm('คุณต้องการลบผู้ใช้ "' + userName + '" ใช่หรือไม่?')) {
                document.getElementById('delete-user-id').value = userID;
                document.getElementById('delete-form').submit();
            }
        }
        
        // ฟังก์ชันยืนยันการลบผู้ใช้ที่เลือก
        function confirmDeleteSelected() {
            const checkboxes = document.querySelectorAll('input[name="selected_users[]"]:checked');
            if (checkboxes.length === 0) {
                alert('กรุณาเลือกผู้ใช้ที่ต้องการลบอย่างน้อย 1 คน');
                return false;
            }
            
            return confirm('คุณต้องการลบผู้ใช้ที่เลือกทั้งหมด ' + checkboxes.length + ' คนใช่หรือไม่?');
        }
        
        // เลือกทั้งหมด
        document.getElementById('select-all').addEventListener('change', function() {
            const checkboxes = document.querySelectorAll('input[name="selected_users[]"]:not([disabled])');
            checkboxes.forEach(checkbox => {
                checkbox.checked = this.checked;
            });
        });
        
        document.getElementById('select-all-head').addEventListener('change', function() {
            document.getElementById('select-all').checked = this.checked;
            const checkboxes = document.querySelectorAll('input[name="selected_users[]"]:not([disabled])');
            checkboxes.forEach(checkbox => {
                checkbox.checked = this.checked;
            });
        });
        
        // ค้นหาผู้ใช้
        document.getElementById('searchInput').addEventListener('keyup', function() {
            const searchText = this.value.toLowerCase();
            const rows = document.querySelectorAll('#users-table tbody tr');
            
            rows.forEach(row => {
                const name = row.querySelector('td[data-label="ชื่อ"]').textContent.toLowerCase();
                const email = row.querySelector('td[data-label="อีเมล"]').textContent.toLowerCase();
                const userId = row.querySelector('td[data-label="UserID"]').textContent.toLowerCase();
                
                if (name.includes(searchText) || email.includes(searchText) || userId.includes(searchText)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });
        
        // แสดง modal แก้ไขข้อมูลผู้ใช้
        function showEditModal(userID, userName, userEmail, isAdmin) {
            document.getElementById('edit-user-id').value = userID;
            document.getElementById('edit-name').value = userName;
            document.getElementById('edit-email').value = userEmail;
            document.getElementById('edit-is-admin').checked = isAdmin == 1;
            
            document.getElementById('editModal').style.display = 'block';
        }
        
        // ปิด modal แก้ไขข้อมูลผู้ใช้
        function closeEditModal() {
            document.getElementById('editModal').style.display = 'none';
        }
        
        // แสดง modal จัดการสิทธิ์ผู้ใช้
        function showAdminActionModal() {
            const checkboxes = document.querySelectorAll('input[name="selected_users[]"]:checked');
            if (checkboxes.length === 0) {
                alert('กรุณาเลือกผู้ใช้ที่ต้องการจัดการสิทธิ์อย่างน้อย 1 คน');
                return;
            }
            
            // สร้าง input hidden สำหรับผู้ใช้ที่เลือก
            const container = document.getElementById('selected-users-container');
            container.innerHTML = '';
            
            checkboxes.forEach(checkbox => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'selected_users[]';
                input.value = checkbox.value;
                container.appendChild(input);
            });
            
            document.getElementById('adminActionModal').style.display = 'block';
        }
        
        // ปิด modal จัดการสิทธิ์ผู้ใช้
        function closeAdminActionModal() {
            document.getElementById('adminActionModal').style.display = 'none';
        }
        
        // ปิด modal เมื่อคลิกนอกพื้นที่
        window.onclick = function(event) {
            const editModal = document.getElementById('editModal');
            const adminActionModal = document.getElementById('adminActionModal');
            
            if (event.target == editModal) {
                editModal.style.display = 'none';
            }
            
            if (event.target == adminActionModal) {
                adminActionModal.style.display = 'none';
            }
        }
    </script>
</body> 
</html>