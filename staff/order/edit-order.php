<?php

require_once __DIR__ . "/../../config/database.php";
require_once __DIR__ . "/../../includes/functions.php";

requireLogin();

$statusLabels = [
    1 => "Open",
    2 => "Processing",
    3 => "Approved",
    4 => "Pending delivery",
    5 => "Completed",
    0 => "Rejected"
];

$oid = $_GET["oid"] ?? "";
if (empty($oid)) {
    echo "Invalid order ID.";
    exit;
}

$stmt = $conn->prepare("SELECT o.*, c.*, p.*
                        FROM orders o, customer c, product p
                        WHERE o.cid = c.cid AND o.pid = p.pid AND o.oid = ?");
$stmt->bind_param("i", $oid);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows == 0) {
    echo "Order not found.";
    exit;
}
$order = $result->fetch_assoc();
$stmt->close();

$message = "";
$messageType = "";

if ($order["ostatus"] == 5) {
    $message = "Cannot edit completed orders.";
    $messageType = "error";

} else if ($_SERVER["REQUEST_METHOD"] == "POST") {

    if (isset($_POST["update_order"])) {

        $prodStmt = $conn->prepare("SELECT pstatus FROM production WHERE proid = ?");
        $prodStmt->bind_param("i", $order["proid"]);
        $prodStmt->execute();
        $prodResult = $prodStmt->get_result();

        if ($prodRow = $prodResult->fetch_assoc()) {
            if ($prodRow["pstatus"] == "finished") {
                $message = "Cannot update order details - production has already been completed and materials have been consumed.";
                $messageType = "error";
                $prodStmt->close();
            } else {
                $prodStmt->close();
                
                $pid = $_POST["pid"];
                $oqty = $_POST["oqty"];
                $ocost = $_POST["ocost"];

                $productData = getAvailableProducts($conn);
                $prodmatList = $productData["prodmat"];
                $materialLookup = $productData["material_lookup"];

                $oldMaterialStmt = $conn->prepare("SELECT mid, rqty FROM actualmat WHERE oid = ?");
                $oldMaterialStmt->bind_param("i", $oid);
                $oldMaterialStmt->execute();
                $oldMaterialResult = $oldMaterialStmt->get_result();
                $oldReservations = [];

                while ($oldMaterialRow = $oldMaterialResult->fetch_assoc()) {
                    $mid = $oldMaterialRow["mid"];
                    $oldReservedQty = $oldMaterialRow["rqty"];
                    $oldReservations[$mid] = $oldReservedQty;
                    
                    // Temporarily restore this material to get true available quantity
                    $updateMatStmt = $conn->prepare("UPDATE material SET mrqty = mrqty - ? WHERE mid = ?");
                    $updateMatStmt->bind_param("ii", $oldReservedQty, $mid);
                    $updateMatStmt->execute();
                    $updateMatStmt->close();
                }
                $oldMaterialStmt->close();

                $newCart = [$pid => $oqty];
                $validationErrors = validateCrossProductMaterialAvailability($newCart, $prodmatList, $materialLookup);

                if (!empty($validationErrors)) {

                    // Validation failed - restore old reservations
                    foreach ($oldReservations as $mid => $oldReservedQty) {
                        $restoreStmt = $conn->prepare("UPDATE material SET mrqty = mrqty + ? WHERE mid = ?");
                        $restoreStmt->bind_param("ii", $oldReservedQty, $mid);
                        $restoreStmt->execute();
                        $restoreStmt->close();
                    }

                    $message = "Order cannot be updated:\n\n" . 
                            implode("\n", $validationErrors) . 
                            "\n\nPlease adjust the quantity or choose a different product.";
                    $messageType = "error";

                } else {

                    $stmt = $conn->prepare("UPDATE orders SET pid = ?, oqty = ?, ocost = ? WHERE oid = ?");
                    $stmt->bind_param("iidi", $pid, $oqty, $ocost, $oid);

                    if ($stmt->execute()) {

                        $deleteStmt = $conn->prepare("DELETE FROM actualmat WHERE oid = ?");
                        $deleteStmt->bind_param("i", $oid);
                        $deleteStmt->execute();
                        $deleteStmt->close();
                        
                        $materialStmt = $conn->prepare("SELECT mid, pmqty FROM prodmat WHERE pid = ?");
                        $materialStmt->bind_param("i", $pid);
                        $materialStmt->execute();
                        $materialResult = $materialStmt->get_result();
                        
                        $insertStmt = $conn->prepare("INSERT INTO actualmat (oid, pid, mid, rqty) VALUES (?, ?, ?, ?)");
                        while ($materialRow = $materialResult->fetch_assoc()) {
                            $mid = $materialRow["mid"];
                            $requiredQty = $materialRow["pmqty"] * $oqty;
                            $insertStmt->bind_param("iiii", $oid, $pid, $mid, $requiredQty);
                            $insertStmt->execute();
                            
                            $updateNewMatStmt = $conn->prepare("UPDATE material SET mrqty = mrqty + ? WHERE mid = ?");
                            $updateNewMatStmt->bind_param("ii", $requiredQty, $mid);
                            $updateNewMatStmt->execute();
                            $updateNewMatStmt->close();
                        }
                        $insertStmt->close();
                        $materialStmt->close();

                        $message = "Order updated successfully!";
                        $messageType = "success";

                    } else {

                        foreach ($oldReservations as $mid => $oldReservedQty) {
                            $restoreStmt = $conn->prepare("UPDATE material SET mrqty = mrqty + ? WHERE mid = ?");
                            $restoreStmt->bind_param("ii", $oldReservedQty, $mid);
                            $restoreStmt->execute();
                            $restoreStmt->close();
                        }

                        $message = "Failed to update order: " . $stmt->error;
                        $messageType = "error";

                    }

                    $stmt->close();

                }
            }
        } else {
            $prodStmt->close();
            $message = "Production record not found.";
            $messageType = "error";
        }

    } elseif (isset($_POST["update_status"])) {

        $ostatus = $_POST["ostatus"];
        $odeliverdate = !empty($_POST["odeliverdate"]) ? $_POST["odeliverdate"] : null;
        $previousStatus = $order["ostatus"];

        $canUpdate = true;

        if ($ostatus == 4 || $ostatus == 5) {
            $prodStmt = $conn->prepare("SELECT pstatus FROM production WHERE proid = ?");
            $prodStmt->bind_param("i", $order["proid"]);
            $prodStmt->execute();
            $prodResult = $prodStmt->get_result();
            if ($prodRow = $prodResult->fetch_assoc()) {
                if ($prodRow["pstatus"] != "finished") {
                    $message = "Cannot update order status to 'Pending delivery' or 'Completed' while production status is: " . $prodRow["pstatus"];
                    $messageType = "error";
                    $canUpdate = false;
                }
            }
            $prodStmt->close();
        }

        if ($canUpdate) {

            $validTransitions = [
                1 => [2, 0],
                2 => [3, 0],
                3 => [4, 0],
                4 => [5, 0],
                5 => [],
                0 => []
            ];

            if (!isset($validTransitions[$previousStatus]) || !in_array($ostatus, $validTransitions[$previousStatus])) {
                $message = "Invalid status transition: Cannot change from '" . $statusLabels[$previousStatus] . "' to '" . $statusLabels[$ostatus] . "'";
                $messageType = "error";
                $canUpdate = false;
            }

        }

        if ($canUpdate && $odeliverdate && strtotime($odeliverdate) < time()) {
            $message = "Delivery date cannot be in the past.";
            $messageType = "error";
            $canUpdate = false;
        }

        if ($canUpdate) {

            $stmt = $conn->prepare("UPDATE orders SET ostatus = ?, odeliverdate = ? WHERE oid = ?");
            $stmt->bind_param("isi", $ostatus, $odeliverdate, $oid);

            if ($stmt->execute()) {
            
                if ($ostatus == 0 && $previousStatus != 0) {

                    $materialStmt = $conn->prepare("SELECT mid, rqty FROM actualmat WHERE oid = ?");
                    $materialStmt->bind_param("i", $oid);
                    $materialStmt->execute();
                    $materialResult = $materialStmt->get_result();
                    
                    while ($materialRow = $materialResult->fetch_assoc()) {
                        $mid = $materialRow["mid"];
                        $reservedQty = $materialRow["rqty"];
                        $updateMatStmt = $conn->prepare("UPDATE material SET mrqty = mrqty - ? WHERE mid = ?");
                        $updateMatStmt->bind_param("ii", $reservedQty, $mid);
                        $updateMatStmt->execute();
                        $updateMatStmt->close();
                    }
                    $materialStmt->close();
                    
                    $deleteStmt = $conn->prepare("DELETE FROM actualmat WHERE oid = ?");
                    $deleteStmt->bind_param("i", $oid);
                    $deleteStmt->execute();
                    $deleteStmt->close();

                    if ($order["proid"]) {
                        $prodStmt = $conn->prepare("UPDATE production SET pstatus = 'cancel' WHERE proid = ?");
                        $prodStmt->bind_param("i", $order["proid"]);
                        $prodStmt->execute();
                        $prodStmt->close();
                    }
                    
                }
                
                $message = "Order status updated successfully!";
                $messageType = "success";

            } else {
                $message = "Failed to update status: " . $stmt->error;
                $messageType = "error";
            }

            $stmt->close();

        }

    } elseif (isset($_POST["update_customer"])) {

        $cid = $_POST["cid"];
        $ctel = $_POST["ctel"];
        $caddr = $_POST["caddr"];

        $stmt = $conn->prepare("UPDATE customer SET ctel = ?, caddr = ? WHERE cid = ?");
        $stmt->bind_param("ssi", $ctel, $caddr, $cid);

        if ($stmt->execute()) {
            $message = "Customer information updated successfully!";
            $messageType = "success";
        } else {
            $message = "Failed to update customer: " . $stmt->error;
            $messageType = "error";
        }
        $stmt->close();
        
    } elseif (isset($_POST["approval_cancellation"])) {

        $prodStmt = $conn->prepare("SELECT pstatus FROM production WHERE proid = ?");
        $prodStmt->bind_param("i", $order["proid"]);
        $prodStmt->execute();
        $prodResult = $prodStmt->get_result();
        $prodRow = $prodResult->fetch_assoc();
        $prodStmt->close();

        if ($prodRow && $prodRow["pstatus"] == "finished") {
            $message = "Cannot cancel order - production has already been completed and materials have been used.";
            $messageType = "error";
        } else {
            
            $success = true;
            $errorMessage = "";

            $materialStmt = $conn->prepare("SELECT mid, rqty FROM actualmat WHERE oid = ?");
            $materialStmt->bind_param("i", $oid);
            $materialStmt->execute();
            $materialResult = $materialStmt->get_result();
            
            while ($materialRow = $materialResult->fetch_assoc()) {
                $mid = $materialRow["mid"];
                $reservedQty = $materialRow["rqty"];
                $updateMatStmt = $conn->prepare("UPDATE material SET mrqty = mrqty - ? WHERE mid = ?");
                $updateMatStmt->bind_param("ii", $reservedQty, $mid);
                if (!$updateMatStmt->execute()) {
                    $success = false;
                    $errorMessage = "Failed to update material reservations";
                    break;
                }
                $updateMatStmt->close();
            }
            $materialStmt->close();

            if ($success) {
                $deleteStmt = $conn->prepare("DELETE FROM actualmat WHERE oid = ?");
                $deleteStmt->bind_param("i", $oid);
                if (!$deleteStmt->execute()) {
                    $success = false;
                    $errorMessage = "Failed to delete material records";
                }
                $deleteStmt->close();
            }

            if ($success) {
                $stmt = $conn->prepare("UPDATE orders SET ocancel = 2, ostatus = 0 WHERE oid = ?");
                $stmt->bind_param("i", $oid);
                if (!$stmt->execute()) {
                    $success = false;
                    $errorMessage = "Failed to update order status";
                }
                $stmt->close();
            }

            if ($success && $order["proid"]) {
                $prodStmt = $conn->prepare(query: "UPDATE production SET pstatus = 'cancel' WHERE proid = ?");
                $prodStmt->bind_param("i", $order["proid"]);
                if (!$prodStmt->execute()) {
                    $success = false;
                    $errorMessage = "Failed to update production status";
                }
                $prodStmt->close();
            }
            
            if ($success) {
                $message = "Cancellation request approved! Production order cancelled and material reservations removed.";
                $messageType = "success";
            } else {
                $message = "Failed to approve cancellation: " . $errorMessage;
                $messageType = "error";
            }

        }
        
    } elseif (isset($_POST["reject_cancellation"])) {

        $stmt = $conn->prepare("UPDATE orders SET ocancel = 3 WHERE oid = ?");
        $stmt->bind_param("i", $oid);

        if ($stmt->execute()) {
            $message = "Cancellation request rejected!";
            $messageType = "success";
        } else {
            $message = "Failed to reject cancellation: " . $stmt->error;
            $messageType = "error";
        }
        $stmt->close();

    } elseif (isset($_POST["update_materials"])) {

        $prodStmt = $conn->prepare("SELECT pstatus FROM production WHERE proid = ?");
        $prodStmt->bind_param("i", $order["proid"]);
        $prodStmt->execute();
        $prodResult = $prodStmt->get_result();

        if ($prodRow = $prodResult->fetch_assoc()) {
            if ($prodRow["pstatus"] == "finished") {
                $message = "Cannot update material quantities - production has been completed and materials have already been consumed.";
                $messageType = "error";
                $prodStmt->close();
            } else {
                $prodStmt->close();
                
                if (isset($_POST["material_qty"]) && is_array($_POST["material_qty"])) {

                    $success = true;
                    foreach ($_POST["material_qty"] as $mid => $newRqty) {
                    
                        $currentStmt = $conn->prepare("SELECT rqty FROM actualmat WHERE oid = ? AND mid = ?");
                        $currentStmt->bind_param("ii", $oid, $mid);
                        $currentStmt->execute();
                        $currentResult = $currentStmt->get_result();
                        
                        if ($currentResult->num_rows > 0) {
                            $currentRow = $currentResult->fetch_assoc();
                            $oldRqty = $currentRow["rqty"];
                            $difference = $newRqty - $oldRqty;
                            
                            $updateActualStmt = $conn->prepare("UPDATE actualmat SET rqty = ? WHERE oid = ? AND mid = ?");
                            $updateActualStmt->bind_param("iii", $newRqty, $oid, $mid);
                            
                            if ($updateActualStmt->execute()) {
                                $updateMatStmt = $conn->prepare("UPDATE material SET mrqty = mrqty + ? WHERE mid = ?");
                                $updateMatStmt->bind_param("ii", $difference, $mid);
                                if (!$updateMatStmt->execute()) {
                                    $success = false;
                                }
                                $updateMatStmt->close();
                            } else {
                                $success = false;
                            }
                            $updateActualStmt->close();
                            
                        } else {
                            $success = false;
                        }
                        $currentStmt->close();

                    }
                    
                    if ($success) {
                        $message = "Material quantities updated successfully!";
                        $messageType = "success";
                    } else {
                        $message = "Failed to update material quantities.";
                        $messageType = "error";
                    }
                }

            }

        } else {

            $prodStmt->close();
            $message = "Production record not found.";
            $messageType = "error";

        }

    }

}

// Re-load the data
$stmt = $conn->prepare("SELECT o.*, c.*, p.*
                        FROM orders o, customer c, product p
                        WHERE o.cid = c.cid AND o.pid = p.pid AND o.oid = ?");
$stmt->bind_param("i", $oid);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows == 0) {
    echo "Order not found.";
    exit;
}
$order = $result->fetch_assoc();
$stmt->close();

$productList = [];
$sql = "SELECT * FROM product ORDER BY pid";
$rs = mysqli_query($conn, $sql) or die(mysqli_error($conn));
while ($rc = mysqli_fetch_assoc($rs)) {
    $productList[] = array(
        "id" => (int)$rc["pid"],
        "name" => $rc["pname"],
        "description" => $rc["pdesc"],
        "price" => (float)$rc["pcost"],
        "image" => $rc["pimage"],
        "availability" => $rc["pavail"]);
}

$materialUsage = [];
$productMatData = [];
if ($order["proid"]) {

    $materialStmt = $conn->prepare("SELECT am.mid, am.rqty, m.mname, m.munit
                                    FROM actualmat am, material m
                                    WHERE am.mid = m.mid AND am.oid = ?");
    $materialStmt->bind_param("i", $oid);
    $materialStmt->execute();
    $materialResult = $materialStmt->get_result();
    while ($row = $materialResult->fetch_assoc()) {
        $materialUsage[] = $row;
    }
    $materialStmt->close();

    $productMatStmt = $conn->prepare("SELECT pm.pid, pm.mid, pm.pmqty, m.mname, m.munit
                                      FROM prodmat pm, material m
                                      WHERE pm.mid = m.mid");
    $productMatStmt->execute();
    $productMatResult = $productMatStmt->get_result();
    while ($row = $productMatResult->fetch_assoc()) {
        $productMatData[$row["pid"]][] = $row;
    }
    $productMatStmt->close();

}

$materialList = [];
$materialQuery = $conn->query("SELECT * FROM material WHERE mavail = 1 ORDER BY mid");
if ($materialQuery) {
    while ($row = $materialQuery->fetch_assoc()) {
        $materialList[] = $row;
    }
}

$prodStmt = $conn->prepare("SELECT pstatus FROM production WHERE proid = ?");
$prodStmt->bind_param("i", $order["proid"]);
$prodStmt->execute();
$prodResult = $prodStmt->get_result();
$productionFinished = false;
if ($prodRow = $prodResult->fetch_assoc()) {
    $productionFinished = ($prodRow["pstatus"] == "finished");
}
$prodStmt->close();

?>

<!DOCTYPE html>
<html lang="en">

    <head>
        <base href="../../">
        <?php require_once __DIR__ . "/../../includes/head-setting.php"; ?>
        <title>Smile & Sunshine | Edit Order</title>
        <link rel="stylesheet" href="styles/style-items.css">
    </head>

    <body>

        <div id="main-page">

            <?php require_once __DIR__ . "/../../includes/header.php"; ?>

            <div id="main-section">

                <div id="items-container">

                    <div id="items-header">
                        <h2>Edit Order #<?= $oid ?></h2>
                        <p>Order ID: <?= $order["proid"] ?></p>
                    </div>

                    <div id="items-controls">
                        <div id="control-actions">
                            <button id="back-btn" onclick="window.location.href='staff/order/manage-customer-orders.php'">Back to Manage Orders</button>
                        </div>
                    </div>

                    <?php if (!empty($message)): ?>
                        <div class="alert alert-<?= $messageType ?>">
                            <?= htmlspecialchars($message) ?>
                        </div>
                    <?php endif; ?>

                    <div class="edit-section">
                        <h3>Customer Information</h3>
                        <form method="POST">
                            <input type="hidden" name="cid" value="<?= $order["cid"] ?>">
                            <div class="form-grid">
                                <div class="form-group">
                                    <label>Customer ID</label>
                                    <input type="text" value="<?= $order["cid"] ?>" readonly class="readonly">
                                </div>
                                <div class="form-group">
                                    <label>Customer Name</label>
                                    <input type="text" name="cname" value="<?= $order["cname"] ?>" readonly class="readonly">
                                </div>
                                <div class="form-group">
                                    <label>Email</label>
                                    <input type="email" value="<?= $order["cemail"] ?>" readonly class="readonly">
                                </div>
                                <div class="form-group">
                                    <label>Contact Number</label>
                                    <input type="text" name="ctel" value="<?= $order["ctel"] ?>" required>
                                </div>
                            </div>
                            <div class="form-group">
                                <label>Delivery Address</label>
                                <textarea name="caddr" rows="3" required><?= $order["caddr"] ?></textarea>
                            </div>
                            <div class="button-group">
                                <button type="submit" name="update_customer" class="btn btn-primary">Update Customer Info</button>
                            </div>
                        </form>
                    </div>

                    <div class="edit-section">
                        <h3>Order Information</h3>
                        <?php if ($productionFinished): ?>
                            <div class="alert alert-info">
                                <strong>Note:</strong> Production has been completed. Order quantities and materials cannot be modified.
                            </div>
                        <?php endif; ?>
                        <form method="POST">
                            <input type="hidden" name="ostatus" value="<?= $order["ostatus"] ?>">
                            <div class="form-grid">
                                <div class="form-group">
                                    <label>Order ID</label>
                                    <input type="text" value="<?= $order["oid"] ?>" readonly class="readonly">
                                </div>
                                <div class="form-group">
                                    <label>Order Date</label>
                                    <input type="text" value="<?= $order["odate"] ?>" readonly class="readonly">
                                </div>
                                <div class="form-group">
                                    <label>Product</label>
                                    <input type="text" value="<?= $order["pid"] ?> - <?= $order["pname"] ?>" readonly class="readonly">
                                    <input type="hidden" id="current-product-id" name="pid" value="<?= $order["pid"] ?>">
                                </div>
                                <div class="form-group">
                                    <label>Order Quantity</label>
                                    <input type="number" id="oqty" name="oqty" value="<?= $order["oqty"] ?>" min="1" required onchange="calculateTotal()"
                                        <?= $productionFinished ? 'readonly class="readonly"' : '' ?>>
                                </div>
                                <div class="form-group">
                                    <label>Unit Cost</label>
                                    <input type="number" id="unit-cost" value="<?= $order["pcost"] ?>" readonly class="readonly">
                                </div>
                                <div class="form-group">
                                    <label>Total Cost</label>
                                    <input type="text" id="ocost" name="ocost" value="<?= $order["ocost"] ?>" readonly class="readonly">
                                </div>
                            </div>
                            <div class="button-group">
                                <button type="submit" name="update_order" class="btn btn-success" 
                                        <?= $productionFinished ? 'disabled title="Cannot edit - production completed"' : '' ?>>
                                    Update Order
                                </button>
                            </div>
                        </form>
                    </div>

                    <div class="edit-section">
                        <h3>Order Status</h3>
                        <form method="POST">
                            <input type="hidden" name="odeliverdate" value="<?= $order["odeliverdate"] ?? "" ?>">
                            <div class="form-grid">
                                <div class="form-group">
                                    <label>Order Status</label>
                                    <select name="ostatus" required>
                                        <?php foreach ($statusLabels as $key => $label): ?>
                                            <option value="<?= $key ?>" <?= $order["ostatus"] == $key ? "selected" : "" ?>><?= $label ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group">
                                <label>Delivery Date</label>
                                <input type="datetime-local" name="odeliverdate" value="<?= $order["odeliverdate"] ? date("Y-m-d\TH:i", strtotime($order["odeliverdate"])) : "" ?>">
                            </div>
                            <div class="button-group">
                                <button type="submit" name="update_status" class="btn btn-primary">Update Status</button>
                            </div>
                        </form>
                    </div>

                    <?php if ($order["ostatus"] != 0 && $order["ocancel"] == 1): ?>
                    <div class="edit-section">
                        <h3>Order Cancellation Request</h3>
                        <div class="form-group">
                            <label>Cancel Status</label>
                            <input type="text" value="<?= $order["ocancel"] == 0 ? "Not Requested" : 
                                ($order["ocancel"] == 1 ? "Requested" : "Approved/Rejected") ?>" readonly class="readonly">
                        </div>
                        <form method="POST" onsubmit="return confirm('Are you sure you want to approve the cancellation? This action cannot be undone.')">
                            <div class="button-group">
                                <button type="submit" name="approval_cancellation" class="btn btn-danger">Approve Cancellation</button>
                            </div>
                        </form>
                        <form method="POST" onsubmit="return confirm('Are you sure you want to rject the cancellation? This action cannot be undone.')">
                            <div class="button-group">
                                <button type="submit" name="reject_cancellation" class="btn btn-danger">Reject Cancellation</button>
                            </div>
                        </form>
                    </div>
                    <?php endif; ?>

                    <div class="edit-section">
                        <h3>Reserved Materials for Production</h3>
                        <?php if (!empty($materialUsage)): ?>
                            <form method="POST">
                                <table class="material-table">
                                    <thead>
                                        <tr>
                                            <th>Material ID</th>
                                            <th>Material Name</th>
                                            <th>Unit</th>
                                            <th>Suggested Quantity</th>
                                            <th>Reserved Quantity</th>
                                        </tr>
                                    </thead>
                                    <tbody id="material-usage-tbody">
                                        <?php foreach ($materialUsage as $material): ?>
                                            <?php 
                                            $suggestedQty = 0;
                                            if (isset($productMatData[$order["pid"]])) {
                                                foreach ($productMatData[$order["pid"]] as $prodMat) {
                                                    if ($prodMat["mid"] == $material["mid"]) {
                                                        $suggestedQty = $prodMat["pmqty"];
                                                        break;
                                                    }
                                                }
                                            }
                                            $requiredQty = $suggestedQty * $order["oqty"]; 
                                            ?>
                                        <tr>
                                            <td><?= $material["mid"] ?></td>
                                            <td><?= $material["mname"] ?></td>
                                            <td><?= $material["munit"] ?></td>
                                            <td id="required-qty-<?= $material['mid'] ?>" class="required-qty">
                                                <?= $requiredQty ?>
                                            </td>
                                            <td>
                                                <input type="number" 
                                                        id="reserved-qty-<?= $material["mid"] ?>"
                                                        name="material_qty[<?= $material["mid"] ?>]" 
                                                        value="<?= $material["rqty"] ?>" 
                                                        min="0" 
                                                        step="1"
                                                        class="reserved-qty-input"
                                                        <?= $productionFinished ? 'readonly' : '' ?>>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                                <div class="button-group">
                                    <button type="submit" name="update_materials" class="btn btn-primary"
                                            <?= $productionFinished ? 'disabled title="Cannot edit - production completed"' : '' ?>>
                                        Update Material Quantities
                                    </button>
                                </div>
                            </form>
                        <?php else: ?>
                            <p>No materials reserved for this production order.</p>
                        <?php endif; ?>
                    </div>

                    <div class="edit-section">
                        <h3>General Materials Status</h3>
                        <table class="material-table">
                            <thead>
                                <tr>
                                    <th>Material ID</th>
                                    <th>Material Name</th>
                                    <th>Unit</th>
                                    <th>Physical Quantity</th>
                                    <th>Available Quantity</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($materialList as $material): ?>
                                    <?php 
                                    $availableQty = $material["mqty"] - $material["mrqty"];
                                    $isLowStock = $availableQty < $material["mreorderqty"];
                                    ?>
                                <tr class="<?= $isLowStock ? "low-stock-row" : "" ?>">
                                    <td><?= $material["mid"] ?></td>
                                    <td><?= $material["mname"] ?></td>
                                    <td><?= $material["munit"] ?></td>
                                    <td><?= $material["mqty"] ?></td>
                                    <td class="<?= $isLowStock ? "low-stock" : "adequate-stock" ?>">
                                        <?= $availableQty ?>
                                    </td>
                                    <td class="<?= $isLowStock ? "low-stock" : "adequate-stock" ?>">
                                        <?= $isLowStock ? "LOW STOCK" : "OK" ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                </div>
                
            </div>

            <?php require_once __DIR__ . "/../../includes/footer.php"; ?>
            <?php require_once __DIR__ . "/../../includes/tools.php"; ?>

        </div>

        <script>
            var productList = <?php echo json_encode($productList); ?>;
            var productMatData = <?php echo json_encode($productMatData); ?>;
        </script>

        <script src="script/script-general.js"></script>
        <script src="script/script-manage-customer-orders.js"></script>

        <?php if (isset($message) && $message != null) {
            echo "<script type='text/javascript'>alert('" . json_encode($message) . "'); </script>";
        } ?>

    </body>

</html>
