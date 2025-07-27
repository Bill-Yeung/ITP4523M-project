<?php

require_once __DIR__ . "/../../config/database.php";
require_once __DIR__ . "/../../includes/functions.php";

requireLogin();

$materialList = [];
$sql = "SELECT * FROM material ORDER BY mid";
$rs = mysqli_query($conn, $sql) or die(mysqli_error($conn));
while ($rc = mysqli_fetch_assoc($rs)) {
    $materialList[] = array("id" => (int)$rc["mid"],
                            "name" => $rc["mname"],
                            "quantity" => (int)$rc["mqty"],
                            "reserved" => (int)$rc["mrqty"],
                            "unit" => $rc["munit"],
                            "reorder" => (int)$rc["mreorderqty"],
                            "image" => $rc["mimage"],
                            "availability" => $rc["mavail"]);
}

mysqli_free_result($rs);
mysqli_close($conn);

?>

<!DOCTYPE html>
<html lang="en">

    <head>
        <base href="../../">
        <?php require_once __DIR__ . "/../../includes/head-setting.php"; ?>
        <link rel="stylesheet" href="styles/style-items.css">
        <title>Smile & Sunshine | Manage Materials</title>
    </head>

    <body>

    <div id="main-page">

        <?php require_once __DIR__ . "/../../includes/header.php"; ?>

        <div id="main-section">

            <div id="items-container">

                <div id="items-header">
                    <h2>Manage Materials</h2>
                    <p>Add, edit, and manage materials</p>
                </div>

                <div id="items-controls">
                    <div id="search-tools">
                        <div id="search-input">
                            <input type="text" id="search" placeholder="Search materials by name...">
                            <button type="button" id="search-button" onclick="performSearch()">Search</button>
                            <button type="button" id="reset-button" onclick="resetSearch()">Reset</button>
                        </div>
                        <div id="control-actions">
                            <button type="button" id="add-item-btn" onclick="window.location.href='staff/material/add-material.php'">Add Material</button>
                        </div>
                    </div>
                </div>

                <div id="options">
                    <div id="filter-options">
                        <label for="status-filter">Filter by Availability:</label>
                        <select id="status-filter" onchange="applyMaterialFilter()">
                            <option value="all">All Products</option>
                            <option value="active" selected>Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                </div>

                <div id="items-section">

                    <?php if ($materialList == []): ?>
                        <div id="no-items">
                            There are no materials yet. &nbsp;<a href="staff/material/add-material.php">Add Materials</a>
                        </div>
                    <?php else: ?>

                        <div id="result-count">
                            <span id="item-count"><?php echo count($materialList); ?></span><span> materials found</span>
                        </div>

                        <div id="items-table-container">

                            <table id="items-table">

                                <thead>
                                    <tr>
                                        <th class="table-header material-col1">Material ID</th>
                                        <th class="table-header material-col2">Material Name</th>
                                        <th class="table-header material-col3">Material Image</th>
                                        <th class="table-header material-col4">Unit</th>
                                        <th class="table-header material-col5">Physical<br>Quantity</th>
                                        <th class="table-header material-col6">Reserved<br>Quantity</th>
                                        <th class="table-header material-col7">Remaining<br>Quantity</th>
                                        <th class="table-header material-col8">Re-order<br>Level</th>
                                        <th class="table-header material-col9">Availability</th>
                                        <th class="table-header material-col10">Actions</th>
                                    </tr>
                                </thead>

                                <tbody id="materials-body"></tbody>

                            </table>

                        </div>

                    <?php endif; ?>

                </div>

            </div>

        </div>

        <div id="itemModal">
            <div id="modal-content">
                <div id="modal-header">
                    <h2>Material Details</h2>
                    <span id="close-btn" onclick="closeMaterialModal()">&times;</span>
                </div>
                <div id="modal-inner"></div>
            </div>
        </div>

        <?php require_once __DIR__ . "/../../includes/footer.php"; ?>
        <?php require_once __DIR__ . "/../../includes/tools.php"; ?>

    </div>

    <script>
        var materialList = <?php echo json_encode($materialList); ?>;
    </script>

    <script src="script/script-general.js"></script>
    <script src="script/script-manage-materials.js"></script>

    </body>

</html>