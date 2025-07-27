<?php

require_once __DIR__ . "/../../config/database.php";
require_once __DIR__ . "/../../includes/functions.php";

requireLogin();

$staffList = [];
$sql = "SELECT sid, sname, semail, spassword, srole, stel, simage, savail FROM staff where savail = 1";
$result = $conn->query($sql);

if ($result && $result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $staffList[] = array(
            "id" => (int)$row["sid"],
            "name" => $row["sname"],
            "email" => $row["semail"],
            "role" => $row["srole"],
            "phone" => $row["stel"],
            "image" => $row["simage"],
            "availability" => $row["savail"]
        );
    }
}

$conn->close();

?>

<!DOCTYPE html>
<html lang="en">

    <head>
        <base href="../../">
        <?php require_once __DIR__ . "/../../includes/head-setting.php"; ?>
        <link rel="stylesheet" href="styles/style-items.css">
        <title>Smile & Sunshine | Manage Staff</title>
    </head>

    <body>

        <div id="main-page">

            <?php require_once __DIR__ . "/../../includes/header.php"; ?>

            <div id="main-section">

                <div id="items-container">
                    
                    <div id="items-header">
                        <h2>Manage Staff</h2>
                        <p>Manage staff users and their roles</p>
                    </div>

                    <div id="items-controls">
                        <div id="search-tools">
                            <div id="search-input">
                                <input type="text" id="search" placeholder="Search staff by name and other items...">
                                <button type="button" id="search-button" onclick="performSearch()">Search</button>
                                <button type="button" id="reset-button" onclick="resetSearch()">Reset</button>
                            </div>
                            <div id="control-actions">
                                <button type="button" id="add-item-btn" onclick="window.location.href='staff/user/add-staff.php'">Add Staff</button>
                            </div>
                        </div>
                    </div>

                    <div id="items-section">

                        <?php if (empty($staffList)): ?>
                            <div id="no-items">
                                There are no staff members yet. &nbsp;<a href="staff/user/add-staff.php">Add Staff</a>
                            </div>
                        <?php else: ?>

                            <div id="result-count">
                                <span id="item-count"><?php echo count($staffList); ?></span><span> staff members found</span>
                            </div>

                            <div id="items-table-container">

                                <table id="items-table" class="staff-table">

                                    <thead>
                                        <tr>
                                            <th class="table-header col1">Staff ID</th>
                                            <th class="table-header col2">Name</th>
                                            <th class="table-header col3">Email</th>
                                            <th class="table-header col4">Role</th>
                                            <th class="table-header col5">Phone</th>
                                            <th class="table-header col6">Actions</th>
                                        </tr>
                                    </thead>

                                    <tbody id="staff-body"></tbody>

                                </table>

                            </div>

                        <?php endif; ?>

                    </div>

                </div>

            </div>

            <!-- Pop-up View -->
            <div id="itemModal">
                <div id="modal-content">
                    <div id="modal-header">
                        <h2>Staff Details</h2>
                        <span id="close-btn" onclick="closeStaffModal()">&times;</span>
                    </div>
                    <div id="modal-inner"></div>
                </div>
            </div>

            <?php require_once __DIR__ . "/../../includes/footer.php"; ?>
            <?php require_once __DIR__ . "/../../includes/tools.php"; ?>
            
        </div>

        <script>
            var staffList = <?php echo json_encode($staffList); ?>;
        </script>

        <script src="script/script-general.js"></script>
        <script src="script/script-manage-staff.js"></script>

    </body>
    
</html>