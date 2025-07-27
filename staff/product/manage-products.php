<?php

require_once __DIR__ . "/../../config/database.php";
require_once __DIR__ . "/../../includes/functions.php";

requireLogin();

$productList = [];
$sql = "SELECT * FROM product ORDER BY pid";
$rs = mysqli_query($conn, $sql) or die(mysqli_error($conn));
while ($rc = mysqli_fetch_assoc($rs)) {
    $productList[] = array("id" => (int)$rc["pid"],
                           "name" => $rc["pname"],
                           "description" => $rc["pdesc"],
                           "price" => (float)$rc["pcost"],
                           "image" => $rc["pimage"],
                           "availability" => $rc["pavail"]);
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
        <title>Smile & Sunshine | Manage Products</title>
    </head>

    <body>

        <div id="main-page">

            <?php require_once __DIR__ . "/../../includes/header.php"; ?>

            <div id="main-section">

                <div id="items-container">
                    
                    <div id="items-header">
                        <h2>Manage Products</h2>
                        <p>Add, edit, and manage products</p>
                    </div>

                    <div id="items-controls">
                        <div id="search-tools">
                            <div id="search-input">
                                <input type="text" id="search" placeholder="Search products by name...">
                                <button type="button" id="search-button" onclick="performSearch()">Search</button>
                                <button type="button" id="reset-button" onclick="resetSearch()">Reset</button>
                            </div>
                            <div id="control-actions">
                                <button type="button" id="add-item-btn" onclick="window.location.href='staff/product/add-product.php'">Add Product</button>
                            </div>
                        </div>
                    </div>

                    <div id="options">
                        <div id="filter-options">
                            <label for="status-filter">Filter by Availability:</label>
                            <select id="status-filter" onchange="applyProductFilter()">
                                <option value="all">All Products</option>
                                <option value="active" selected>Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>
                    </div>

                    <div id="items-section">

                        <?php if ($productList == []): ?>
                            <div id="no-items">
                                There are no products yet. &nbsp;<a href="staff/product/add-product.php">Add Products</a>
                            </div>
                        <?php else: ?>

                            <div id="result-count">
                                <span id="item-count"><?php echo count($productList); ?></span><span> products found</span>
                            </div>

                            <div id="items-table-container">

                                <table id="items-table">

                                    <thead>
                                        <tr>
                                            <th class="table-header col1">Product ID</th>
                                            <th class="table-header col2">Product Name</th>
                                            <th class="table-header col3">Product Image</th>
                                            <th class="table-header col4">Product Cost</th>
                                            <th class="table-header col6">Availability</th>
                                            <th class="table-header col7">Actions</th>
                                        </tr>
                                    </thead>

                                    <tbody id="products-body"></tbody>

                                </table>

                            </div>

                        <?php endif; ?>

                    </div>

                </div>

            </div>

            <div id="itemModal">
                <div id="modal-content">
                    <div id="modal-header">
                        <h2>Product Details</h2>
                        <span id="close-btn" onclick="closeProductModal()">&times;</span>
                    </div>
                    <div id="modal-inner"></div>
                </div>
            </div>

            <?php require_once __DIR__ . "/../../includes/footer.php"; ?>
            <?php require_once __DIR__ . "/../../includes/tools.php"; ?>
            
        </div>

        <script>
            var productList = <?php echo json_encode($productList); ?>;
        </script>

        <script src="script/script-general.js"></script>
        <script src="script/script-manage-products.js"></script>

    </body>
    
</html>
