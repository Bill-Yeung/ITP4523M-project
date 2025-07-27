<?php

require_once __DIR__ . "/../../config/database.php";
require_once __DIR__ . "/../../includes/functions.php";

requireLogin();

$sql = "SELECT cid, cname, cemail, ctel, caddr, company, cimage FROM customer WHERE cavail = 1 ORDER BY cid DESC";
$result = $conn->query($sql);

$customerList = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $customerList[] = array(
            "id" => (int)$row["cid"],
            "name" => $row["cname"],
            "email" => $row["cemail"],
            "telephone" => $row["ctel"],
            "address" => $row["caddr"],
            "company" => $row["company"],
            "image" => $row["cimage"]
        );
    }
}

mysqli_close($conn);

?>

<!DOCTYPE html>
<html lang="en">

    <head>
        <base href="../../">
        <?php require_once __DIR__ . "/../../includes/head-setting.php"; ?>
        <link rel="stylesheet" href="styles/style-items.css">
        <title>Smile & Sunshine | Manage Customer</title>
    </head>

    <body>

        <div id="main-page">

            <?php require_once __DIR__ . "/../../includes/header.php"; ?>

            <div id="main-section">

                <div id="items-container">

                    <div id="items-header">
                        <h2>Manage Customer</h2>
                        <p>View and manage customer accounts</p>
                    </div>

                    <div id="items-controls">
                        <div id="search-tools">
                            <div id="search-input">
                                <input type="text" id="search" placeholder="Search customers by name and other items...">
                                <button type="button" id="search-button" onclick="performSearch()">Search</button>
                                <button type="button" id="reset-button" onclick="resetSearch()">Reset</button>
                            </div>
                        </div>
                    </div>

                    <div id="items-section">

                        <?php if (empty($customerList)): ?>
                            <div id="no-items">
                                No customers found.
                            </div>
                        <?php else: ?>

                            <div id="result-count">
                                <span id="item-count"><?php echo count($customerList); ?></span><span> customers found</span>
                            </div>

                            <div id="items-table-container">

                                <table id="items-table" class="users-table">

                                    <thead>
                                        <tr>
                                            <th class="table-header col1">Customer ID</th>
                                            <th class="table-header col2">Name</th>
                                            <th class="table-header col3">Email</th>
                                            <th class="table-header col4">Telephone</th>
                                            <th class="table-header col5">Address</th>
                                            <th class="table-header col6">Company</th>
                                            <th class="table-header col7">Actions</th>
                                        </tr>
                                    </thead>

                                    <tbody id="users-body"></tbody>

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
                        <h2>Customer Details</h2>
                        <span id="close-btn" onclick="closeCustomerModal()">&times;</span>
                    </div>
                    <div id="modal-inner"></div>
                </div>
            </div>

            <?php require_once __DIR__ . "/../../includes/footer.php"; ?>
            <?php require_once __DIR__ . "/../../includes/tools.php"; ?>

        </div>

        <script>
            var customerList = <?php echo json_encode($customerList); ?>;
        </script>

        <script src="script/script-general.js"></script>
        <script src="script/script-manage-users.js"></script>

    </body>

</html>