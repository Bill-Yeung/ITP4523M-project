<!DOCTYPE html>
<html lang="en">

    <head>
        <base href="../">
        <?php require_once __DIR__ . "/../includes/head-setting.php"; ?>
        <link id="manage-orders-css" rel="stylesheet" href="styles/style-manage-orders.css">
        <title>Smile & Sunshine | Manage Orders</title>
    </head>

    <?php

    requireLogin();

    if (isset($_POST["cancel_order"])) {

        $order_id = $_POST["cancel_order"];
        $sql = "SELECT pid, oqty, odeliverdate, ostatus, ocancel, proid FROM orders WHERE oid = $order_id AND cid = {$_SESSION["userinfo"]["user_id"]}";
        $rs = mysqli_query($conn, $sql) or die(mysqli_error($conn));
        
        if (mysqli_num_rows($rs) > 0) {

            $order = mysqli_fetch_assoc($rs);
            $order_id = $_POST["cancel_order"];
            $sql = "SELECT pid, oqty, odeliverdate, ostatus, ocancel, proid FROM orders WHERE oid = $order_id AND cid = {$_SESSION["userinfo"]["user_id"]}";
            $rs = mysqli_query($conn, $sql) or die(mysqli_error($conn));

            if (mysqli_num_rows($rs) > 0) {

                $order = mysqli_fetch_assoc($rs);
                $delivery_date = strtotime($order["odeliverdate"]);
                $current_date = time();
                $two_days_after = $current_date + (2 * 24 * 60 * 60);

                if ($order["odeliverdate"] && $delivery_date < $two_days_after) {
                    $message = "Cancellation cannot be requested since the order is within two days before delivery date!";
                } else {
                    
                    $sql = "UPDATE orders SET ocancel = 1 WHERE oid = $order_id AND cid = {$_SESSION["userinfo"]["user_id"]}";
                    $ru = mysqli_query($conn, $sql) or die(mysqli_error($conn));
                    $message = "Cancellation request submitted for approval!";
                    
                }

            }

        }
        
    }

    $orderList = [];
    $sql = "SELECT o.oid, o.odate, o.pid, o.oqty, o.ocost, o.odeliverdate, o.ostatus, o.ocancel, p.pname, p.pdesc, p.pimage
            FROM orders o, product p
            WHERE o.pid = p.pid AND o.cid = {$_SESSION["userinfo"]["user_id"]}
            ORDER BY o.oid DESC";
    $rs = mysqli_query($conn, $sql) or die(mysqli_error($conn));

    while ($rc = mysqli_fetch_assoc($rs)) {
        $orderList[] = array(
            "order_id" => (int)$rc["oid"],
            "order_date" => $rc["odate"],
            "product_id" => (int)$rc["pid"],
            "quantity" => (int)$rc["oqty"],
            "total_cost" => (float)$rc["ocost"],
            "delivery_date" => $rc["odeliverdate"],
            "status" => $rc["ostatus"],
            "cancel" => $rc["ocancel"],
            "product_name" => $rc["pname"],
            "product_desc" => $rc["pdesc"],
            "product_image" => $rc["pimage"],
        );
    }

    mysqli_free_result($rs);
    mysqli_close($conn);

    ?>

    <body>

        <div id="main-page">

            <?php require_once __DIR__ . "/../includes/header.php"; ?>

            <div id="main-section">

                <div id="orders-container">

                    <div id="orders-header">
                        <h2>Manage Your Orders</h2>
                        <p>View and manage your order history</p>
                        <div id="customer-info">
                            <span>Customer ID: <?php echo $_SESSION["userinfo"]["user_id"]; ?></span>
                            <span>Customer Name: <?php echo $_SESSION["userinfo"]["username"]; ?></span>
                        </div>
                    </div>

                    <?php if (empty($orderList)): ?>
                        <div id="empty-orders">
                            You haven't placed any orders yet. &nbsp;<a href="customer/browse-products.php">Browse Products</a>
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
                                    <option value="1">Open</option>
                                    <option value="2">Processing</option>
                                    <option value="3">Approved</option>
                                    <option value="4">Pending delivery</option>
                                    <option value="5">Completed</option>
                                    <option value="0">Rejected</option>
                                </select>
                            </div>

                            <div id="currency-options">
                                <label for="currency-select">Currency:</label>
                                <select id="currency-select" onchange="changeCurrencyDisplay()">
                                    <option value="USD">USD (US$)</option>
                                    <option value="HKD">HKD (HK$)</option>
                                    <option value="EUR">EUR (€)</option>
                                    <option value="JPY">JPY (¥)</option>
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
                                                    <span class="header-text">Status</span>
                                                    <div class="sort-indicators"></div>
                                                </div>
                                            </th>
                                            <th class="table-header col8">Actions</th>
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

            <?php require_once __DIR__ . "/../includes/footer.php"; ?>
            <?php require_once __DIR__ . "/../includes/tools.php"; ?>
            
        </div>

        <script>
            var orderList = <?php echo json_encode($orderList); ?>;
        </script>

        <script src="script/script-general.js"></script>
        <script src="script/script-manage-orders.js"></script>

        <?php if (isset($message) && $message != null) {
            echo "<script type='text/javascript'>alert('" . $message . "'); </script>";
        } ?>

    </body>

</html>