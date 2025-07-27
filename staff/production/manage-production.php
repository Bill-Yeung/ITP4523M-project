<?php

require_once __DIR__ . "/../../config/database.php";
require_once __DIR__ . "/../../includes/functions.php";

requireLogin();

$message = "";
$messageType = "";

if (isset($_GET["message"])) {
    $message = $_GET["message"];
    $messageType = "success";
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    if (isset($_POST["start_production"])) {

        $proid = $_POST["proid"];
        
        $checkStmt = $conn->prepare("SELECT pstatus FROM production WHERE proid = ?");
        $checkStmt->bind_param("i", $proid);
        $checkStmt->execute();
        $result = $checkStmt->get_result();
        $currentProduction = $result->fetch_assoc();
        $checkStmt->close();
        
        if ($currentProduction["pstatus"] == "started") {
            $message = "Production is already started!";
            $messageType = "error";
        } elseif ($currentProduction["pstatus"] == "finished") {
            $message = "Production is already finished!";
            $messageType = "error";
        } elseif ($currentProduction["pstatus"] == "cancel") {
            $message = "Cannot start a canceled production!";
            $messageType = "error";
        } else {
            $stmt = $conn->prepare("UPDATE production SET pstatus = 'started' WHERE proid = ?");
            $stmt->bind_param("i", $proid);
            
            if ($stmt->execute()) {
                $stmt->close();
                $message = "Production started successfully!";
                $messageType = "success";
            } else {
                $message = "Failed to start production";
                $messageType = "error";
            }
        }

    } else if (isset($_POST["mark_finished"])) {

        $proid = $_POST["proid"];
        
        $checkStmt = $conn->prepare("SELECT pstatus FROM production WHERE proid = ?");
        $checkStmt->bind_param("i", $proid);
        $checkStmt->execute();
        $result = $checkStmt->get_result();
        $currentProduction = $result->fetch_assoc();
        $checkStmt->close();
        
        if ($currentProduction["pstatus"] == "finished") {
            $message = "Production is already finished!";
            $messageType = "error";
        } elseif ($currentProduction["pstatus"] == "cancel") {
            $message = "Cannot finish a canceled production!";
            $messageType = "error";
        } else {

            $stmt = $conn->prepare("UPDATE production SET pstatus = 'finished' WHERE proid = ?");
            $stmt->bind_param("i", $proid);
            
            if ($stmt->execute()) {
                $stmt->close();

                $materialStmt = $conn->prepare("SELECT am.mid, am.rqty 
                                                       FROM actualmat am, orders o
                                                       WHERE am.oid = o.oid AND o.proid = ?");
                $materialStmt->bind_param("i", $proid);
                $materialStmt->execute();
                $materialResult = $materialStmt->get_result();
                    
                while ($materialRow = $materialResult->fetch_assoc()) {
                    $mid = $materialRow["mid"];
                    $usedQty = $materialRow["rqty"];
                        
                    $updateStmt = $conn->prepare("UPDATE material 
                                                         SET mqty = mqty - ?, mrqty = mrqty - ? 
                                                         WHERE mid = ?");
                    $updateStmt->bind_param("iii", $usedQty, $usedQty, $mid);
                    $updateStmt->execute();
                    $updateStmt->close();
                }
                    
                $materialStmt->close();
                
                $message = "Production marked as finished! Material quantities have been updated.";
                $messageType = "success";
                
            } else {

                $message = "Failed to update production status";
                $messageType = "error";

            }

        }

    }

}

$productionList = [];
$sql = "SELECT p.proid, p.pstatus,
               o.oid, o.oqty, o.pid as order_pid, o.ostatus,
               pr.pname as product_name, pr.pimage as product_image
        FROM production p
        INNER JOIN orders o ON p.proid = o.proid
        INNER JOIN product pr ON o.pid = pr.pid
        ORDER BY p.proid DESC";
        
$rs = mysqli_query($conn, $sql) or die(mysqli_error($conn));

while ($rc = mysqli_fetch_assoc($rs)) {
    $productionList[] = array(
        "proid" => (int)$rc["proid"],
        "pstatus" => $rc["pstatus"],
        "order_id" => (int)$rc["oid"],
        "quantity" => (int)$rc["oqty"],
        "product_id" => (int)$rc["order_pid"],
        "product_name" => $rc["product_name"],
        "product_image" => $rc["product_image"],
        "order_status" => (int)$rc["ostatus"]
    );
}

mysqli_free_result($rs);
mysqli_close($conn);

?>

<!DOCTYPE html>
<html lang="en">

    <head>
        <base href="../../">
        <?php require_once __DIR__ . "/../../includes/head-setting.php"; ?>
        <link id="manage-orders-css" rel="stylesheet" href="styles/style-manage-orders.css">
        <title>Smile & Sunshine | Manage Production</title>
    </head>

    <body>

        <div id="main-page">

            <?php require_once __DIR__ . "/../../includes/header.php"; ?>

            <div id="main-section">

                <div id="orders-container">

                    <div id="orders-header">
                        <h2>Manage Production</h2>
                        <p>Monitor production of the company</p>
                    </div>

                    <?php if (!empty($message)): ?>
                        <div class="alert alert-<?= $messageType ?>">
                            <?= $message ?>
                        </div>
                    <?php endif; ?>

                    <?php if (empty($productionList)): ?>
                        <div id="empty-orders">
                            There is no production yet.
                        </div>
                    <?php else: ?>

                        <div id="options">
                            <div id="filter-options">
                                <label for="status-filter">Filter by Status:</label>
                                <select id="status-filter" onchange="applyProductionDisplay()">
                                    <option value="all">All Orders</option>
                                    <option value="pending">Pending Orders (Not Approved)</option>
                                    <option value="approved" selected>Approved Orders</option>
                                    <option value="approved-open">Approved - Ready to Start</option>
                                    <option value="approved-started">Approved - Production Started</option>
                                    <option value="approved-finished">Approved - Production Finished</option>
                                    <option value="canceled">Canceled Orders</option>
                                </select>
                            </div>
                        </div>

                        <div id="orders-section">

                            <div id="result-count">
                                <span id="order-count"><?php echo count($productionList); ?></span><span> production orders found</span>
                            </div>
                            
                            <div id="orders-table-container">

                                <table id="orders-table">

                                    <thead>
                                        <tr>
                                            <th class="table-header col1">Production ID</th>
                                            <th class="table-header col2">Order ID</th>
                                            <th class="table-header col3">Product Requested</th>
                                            <th class="table-header col4">Product Quantity</th>
                                            <th class="table-header col5">Status</th>
                                            <th class="table-header col6">Actions</th>
                                        </tr>
                                    </thead>

                                    <tbody id="production-body">
                                        <?php foreach ($productionList as $production): ?>
                                            <tr class="order-row">
                                                <td class="col1"><?= $production["proid"] ?></td>
                                                <td class="col2"><?= $production["order_id"] ?></td>
                                                <td class="col3">
                                                    <div class="product-info">
                                                        <?php if ($production["product_image"]): ?>
                                                            <img src="img/product/<?= $production["product_image"] ?>">
                                                        <?php endif; ?>
                                                        <div>
                                                            <div class="product-name"><?= $production["product_name"] ?></div>
                                                            <div class="product-id">Product ID: <?= $production["product_id"] ?></div>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="col4">
                                                    <span><?= $production["quantity"] ?></span>
                                                </td>
                                                <td class="col5">
                                                    <span class="status-<?= $production["pstatus"] ?>"><?= ucfirst($production["pstatus"]) ?></span>
                                                </td>
                                                <td class="col6">
                                                    <div class="action-buttons">
                                                        <?php if ($production["order_status"] == 0): ?>
                                                            <span class="canceled-text">Order Canceled</span>
                                                        <?php elseif ($production["order_status"] < 3): ?>
                                                            <span class="pending-text">Waiting for Approval</span>
                                                        <?php elseif ($production["pstatus"] == "open"): ?>
                                                            <form method="POST" onsubmit="return confirm('Are you sure you want to start this production?');">
                                                                <input type="hidden" name="proid" value="<?= $production["proid"] ?>">
                                                                <button type="submit" name="start_production" class="action-btn view-btn">Start</button>
                                                            </form>
                                                        <?php elseif ($production["pstatus"] == "started"): ?>
                                                            <form method="POST" onsubmit="return confirm('Are you sure you want to mark this production as finished? This will permanently update material quantities and cannot be undone.');">
                                                                <input type="hidden" name="proid" value="<?= $production["proid"] ?>">
                                                                <button type="submit" name="mark_finished" class="action-btn invoice-btn">Finish</button>
                                                            </form>
                                                        <?php elseif ($production["pstatus"] == "finished"): ?>
                                                            <span>Completed</span>
                                                        <?php elseif ($production["pstatus"] == "cancel"): ?>
                                                            <span class="canceled-text">Canceled</span>
                                                        <?php endif; ?>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>

                                </table>

                            </div>

                        </div>

                    <?php endif; ?>

                </div>

            </div>

            <?php require_once __DIR__ . "/../../includes/footer.php"; ?>
            <?php require_once __DIR__ . "/../../includes/tools.php"; ?>

        </div>

        <script>
            var productionList = <?php echo json_encode($productionList); ?>;
        </script>

        <script src="script/script-general.js"></script>
        <script src="script/script-manage-production.js"></script>

    </body>

</html>