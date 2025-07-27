<!DOCTYPE html>
<html lang="en">

    <head>
        <base href="../../">
        <?php require_once __DIR__ . "/../../includes/head-setting.php"; ?>
        <link id="manage-orders-css" rel="stylesheet" href="styles/style-manage-orders.css">
        <title>Smile & Sunshine | Manage Orders</title>
    </head>

    <?php

    requireLogin();

    $message = "";
    if (isset($_GET["message"])) {
        $message = $_GET["message"];
    }

    $orderList = [];
    $sql = "SELECT o.oid, o.odate, o.pid, o.oqty, o.ocost, o.cid, o.odeliverdate, o.ostatus, o.ocancel, o.proid, 
                   p.pname, p.pdesc, p.pimage,
                   pr.pstatus as production_status
            FROM orders o
            INNER JOIN product p ON o.pid = p.pid
            LEFT JOIN production pr ON o.proid = pr.proid
            ORDER BY o.oid DESC";
    $rs = mysqli_query($conn, $sql) or die(mysqli_error($conn));

    while ($rc = mysqli_fetch_assoc($rs)) {
        $orderList[] = array(
            "order_id" => (int)$rc["oid"],
            "order_date" => $rc["odate"],
            "product_id" => (int)$rc["pid"],
            "quantity" => (int)$rc["oqty"],
            "total_cost" => (float)$rc["ocost"],
            "customer" => $rc["cid"],
            "delivery_date" => $rc["odeliverdate"],
            "status" => $rc["ostatus"],
            "cancel" => $rc["ocancel"],
            "proid" => $rc["proid"],
            "production_status" => $rc["production_status"],
            "product_name" => $rc["pname"],
            "product_desc" => $rc["pdesc"],
            "product_image" => $rc["pimage"]
        );
    }

    $productList = [];
    $sql = "SELECT pid, pname, pdesc, pcost, pimage, pavail FROM product ORDER BY pid";
    $rs = mysqli_query($conn, $sql) or die(mysqli_error($conn));
    while ($rc = mysqli_fetch_assoc($rs)) {
        $productList[] = array(
            "id" => (int)$rc["pid"],
            "name" => $rc["pname"],
            "description" => $rc["pdesc"],
            "price" => (float)$rc["pcost"],
            "image" => $rc["pimage"],
            "availability" => $rc["pavail"]
        );
    }

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

    $prodmatList = [];
    $sql = "SELECT * FROM prodmat ORDER BY pid ASC, mid ASC";
    $rs = mysqli_query($conn, $sql) or die(mysqli_error($conn));
    while ($rc = mysqli_fetch_assoc($rs)) {
        $prodmatList[$rc["pid"]][] = array(
            "id" => (int)$rc["mid"],
            "qty" => (int)$rc["pmqty"]
        );
    }

    $actualMatList = [];
    $sql = "SELECT * FROM actualmat";
    $rs = mysqli_query($conn, $sql) or die(mysqli_error($conn));
    while ($rc = mysqli_fetch_assoc($rs)) {
        $actualMatList[$rc["oid"]][] = array(
            "product_id" => (int)$rc["pid"],
            "material_id" => (int)$rc["mid"],
            "reserved_qty" => (int)$rc["rqty"],
        );
    }

    $customerList = [];
    $sql = "SELECT * FROM customer ORDER BY cid";
    $rs = mysqli_query($conn, $sql) or die(mysqli_error($conn));
    while ($rc = mysqli_fetch_assoc($rs)) {
        $customerList[] = array(
            "id" => (int)$rc["cid"],
            "name" => $rc["cname"],
            "email" => $rc["cemail"],
            "contact" => $rc["ctel"],
            "address" => $rc["caddr"],
            "company" => $rc["company"]
        );
    }

    mysqli_free_result($rs);
    mysqli_close($conn);

    ?>

    <body>

        <div id="main-page">

            <?php require_once __DIR__ . "/../../includes/header.php"; ?>

            <div id="main-section">

                <div id="orders-container">

                    <div id="orders-header">
                        <h2>Manage Orders</h2>
                        <p>Add, edit, and manage orders</p>
                    </div>

                    <?php if (empty($orderList)): ?>
                        <div id="empty-orders">
                            There are no orders yet.
                        </div>
                    <?php else: ?>

                        <div id="sorting-controls">

                            <h3>Sort Orders</h3>

                            <div id="sort-selections">

                                <div class="sort-row">
                                    <label for="primary-sort">Primary Sort:</label>
                                    <select id="primary-sort" onchange="sortChange()">
                                        <option value="">Select Column</option>
                                        <option value="order_id">Order ID</option>
                                        <option value="order_date">Order Date</option>
                                        <option value="total_cost">Total Cost</option>
                                        <option value="delivery_date">Delivery Date</option>
                                        <option value="status">Status</option>
                                    </select>
                                </div>
                                
                                <div class="sort-row">
                                    <label for="secondary-sort">Secondary Sort:</label>
                                    <select id="secondary-sort" onchange="sortChange()">
                                        <option value="">Select Column</option>
                                        <option value="order_id">Order ID</option>
                                        <option value="order_date">Order Date</option>
                                        <option value="total_cost">Total Cost</option>
                                        <option value="delivery_date">Delivery Date</option>
                                        <option value="status">Status</option>
                                    </select>
                                </div>
                                
                                <div class="sort-row">
                                    <label for="tertiary-sort">Tertiary Sort:</label>
                                    <select id="tertiary-sort" onchange="sortChange()">
                                        <option value="">Select Column</option>
                                        <option value="order_id">Order ID</option>
                                        <option value="order_date">Order Date</option>
                                        <option value="total_cost">Total Cost</option>
                                        <option value="delivery_date">Delivery Date</option>
                                        <option value="status">Status</option>
                                    </select>
                                </div>
                                
                                <div class="sort-actions">
                                    <button type="button" id="clear-sort-btn" onclick="clearAllSort()">Clear All</button>
                                </div>

                            </div>

                        </div>

                        <div id="options">

                            <div id="filter-options">
                                <label for="status-filter">Filter by Status:</label>
                                <select id="status-filter" onchange="applyOrdersDisplay()">
                                    <option value="all">All Orders</option>
                                    <option value="1">Open (Pending Review)</option>
                                    <option value="2">Processing (Under Review)</option>
                                    <option value="3">Approved (Ready for Production)</option>
                                    <option value="4">Pending Delivery</option>
                                    <option value="5">Completed</option>
                                    <option value="0">Rejected/Canceled</option>
                                </select>
                            </div>

                        </div>

                        <div id="orders-section">

                            <div id="result-count">
                                <span id="order-count"><?php echo count($orderList); ?></span><span> orders found</span>
                            </div>
                            
                            <div id="orders-table-container">

                                <table id="orders-table">

                                    <thead>
                                        <tr>
                                            <th class="table-header col1">
                                                <div class="header-content">
                                                    <span class="header-text">Order ID</span>
                                                    <div class="sort-indicators"></div>
                                                </div>
                                            </th>
                                            <th class="table-header col2">
                                                <div class="header-content">
                                                    <span class="header-text">Order Date</span>
                                                    <div class="sort-indicators"></div>
                                                </div>
                                            </th>
                                            <th class="table-header col3">
                                                <div class="header-content">
                                                    <span class="header-text">Product</span>
                                                    <div class="sort-indicators"></div>
                                                </div>
                                            </th>
                                            <th class="table-header col4">
                                                <div class="header-content">
                                                    <span class="header-text">Quantity</span>
                                                    <div class="sort-indicators"></div>
                                                </div>
                                            </th>
                                            <th class="table-header col5">
                                                <div class="header-content">
                                                    <span class="header-text">Total Cost</span>
                                                    <div class="sort-indicators"></div>
                                                </div>
                                            </th>
                                            <th class="table-header col6">
                                                <div class="header-content">
                                                    <span class="header-text">Delivery Date</span>
                                                    <div class="sort-indicators"></div>
                                                </div>
                                            </th>
                                            <th class="table-header col7">
                                                <div class="header-content">
                                                    <span class="header-text">Order Status</span>
                                                    <div class="sort-indicators"></div>
                                                </div>
                                            </th>
                                            <th class="table-header col8">
                                                <div class="header-content">
                                                    <span class="header-text">Production Status</span>
                                                    <div class="sort-indicators"></div>
                                                </div>
                                            </th>
                                            <th class="table-header col9">
                                                <div class="header-content">
                                                    <span class="header-text">Cancel Status</span>
                                                    <div class="sort-indicators"></div>
                                                </div>
                                            </th>
                                            <th class="table-header col10">Actions</th>
                                        </tr>
                                    </thead>

                                    <tbody id="orders-body"></tbody>

                                </table>                       

                            </div>

                        </div>

                    <?php endif; ?>

                </div>

            </div>

            <div id="orderModal">
                <div id="modal-content">
                    <div id="modal-header">
                        <h2>Order Details</h2>
                        <span id="close-btn" onclick="closeOrderModal()">&times;</span>
                    </div>
                    <div id="modal-inner"></div>
                </div>
            </div>

            <?php require_once __DIR__ . "/../../includes/footer.php"; ?>
            <?php require_once __DIR__ . "/../../includes/tools.php"; ?>
            
        </div>

        <script>
            var productList = <?php echo json_encode($productList); ?>;
            var materialList = <?php echo json_encode($materialList); ?>;
            var customerList = <?php echo json_encode($customerList); ?>;
            var prodmatList = <?php echo json_encode($prodmatList); ?>;
            var actualmatList = <?php echo json_encode($actualMatList); ?>;
            var orderList = <?php echo json_encode($orderList); ?>;
        </script>

        <script src="script/script-general.js"></script>
        <script src="script/script-manage-customer-orders.js"></script>

        <?php if (isset($message) && $message != null) {
            echo "<script type='text/javascript'>alert('" . $message . "'); </script>";
        } ?>

    </body>

</html>