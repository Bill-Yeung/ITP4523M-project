<?php

require_once __DIR__ . "/../../config/database.php";
require_once __DIR__ . "/../../includes/functions.php";

requireLogin();

$error = "";
$success = "";

$pid = $_GET["pid"] ?? null;
$productName = "";
$hasOrders = false;

if ($pid && $_SERVER["REQUEST_METHOD"] != "POST") {

    $stmt = $conn->prepare("SELECT pname FROM product WHERE pid = ? AND pavail = 1");
    $stmt->bind_param("i", $pid);
    $stmt->execute();
    $result = $stmt->get_result();

    // Check if there is any existing order
    if ($result && $row = $result->fetch_assoc()) {
        $productName = $row["pname"];

        $orderCheckStmt = $conn->prepare("SELECT COUNT(*) as order_count FROM orders WHERE pid = ?");
        $orderCheckStmt->bind_param("i", $pid);
        $orderCheckStmt->execute();
        $orderResult = $orderCheckStmt->get_result();
        
        if ($orderResult && $orderRow = $orderResult->fetch_assoc()) {
            $hasOrders = $orderRow["order_count"] > 0;
        }
        $orderCheckStmt->close();

    } else {
        $error = "Product not found or already deleted.";
    }
    $stmt->close();

}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["pid"])) {

    $pid = $_POST["pid"];

    $orderCheckStmt = $conn->prepare("SELECT COUNT(*) as order_count FROM orders WHERE pid = ?");
    $orderCheckStmt->bind_param("i", $pid);
    $orderCheckStmt->execute();
    $orderResult = $orderCheckStmt->get_result();

    // Check if there is any existing order
    if ($orderResult && $orderRow = $orderResult->fetch_assoc()) {
        if ($orderRow["order_count"] > 0) {
            $error = "Cannot delete product. This product has existing orders.";
        } else {
            // Get product name
            $stmt = $conn->prepare("SELECT pname FROM product WHERE pid = ?");
            $stmt->bind_param("i", $pid);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result && $row = $result->fetch_assoc()) {
                $productName = $row["pname"];
            }
            $stmt->close();
            
            // Deactivate product
            $stmt = $conn->prepare("UPDATE product SET pavail = 0 WHERE pid = ?");
            $stmt->bind_param("i", $pid);
            if ($stmt->execute()) {
                $success = "Product " . $productName . " deactivated successfully.";
            } else {
                $error = "Failed to deactivate the product.";
            }
            $stmt->close();
        }
    }

    $orderCheckStmt->close();

}

?>

<!DOCTYPE html>
<html lang="en">

    <head>
        <base href="../../">
        <?php require_once __DIR__ . "/../../includes/head-setting.php"; ?>
        <link rel="stylesheet" href="styles/style-items.css">
        <title>Smile & Sunshine | Delete Product</title>
    </head>

    <body>

        <div id="main-page">

            <?php require_once __DIR__ . "/../../includes/header.php"; ?>

            <div id="main-section">

                <div id="items-container">

                    <div id="items-header">
                        <h2>Delete Product</h2>
                        <p>Remove product from inventory</p>
                    </div>

                    <div id="items-section">

                        <div id="delete-confirmation">

                            <?php if ($error): ?>
                                <div class="alert alert-danger">
                                    <?php echo $error; ?>
                                </div>
                                <div class="action-buttons">
                                    <button onclick="window.location.href='staff/product/manage-products.php'">Back to Manage Products</button>
                                </div>

                            <?php elseif ($success): ?>
                                <div class="alert alert-success">
                                    <?php echo $success; ?>
                                </div>
                                <div class="action-buttons">
                                    <button onclick="window.location.href='staff/product/manage-products.php'">Back to Manage Products</button>
                                </div>

                            <?php elseif ($pid && $productName && $hasOrders): ?>

                                <div class="confirmation-content">

                                    <h3>Cannot Delete Product</h3>
                                    <p>This product cannot be deleted because it has existing orders.</p>
                                    
                                    <div class="product-info">
                                        <strong>Product ID: </strong><?php echo $pid; ?><br>
                                        <strong>Product Name: </strong><?php echo $productName; ?>
                                    </div>

                                    <div class="error-message">
                                        <strong>Restriction:</strong> Products with existing orders cannot be deleted/deactivated.
                                    </div>

                                    <div class="action-buttons">
                                        <button onclick="window.location.href='staff/product/manage-products.php'">Back to Manage Products</button>
                                    </div>

                                </div>

                            <?php elseif ($pid && $productName): ?>

                                <div class="confirmation-content">

                                    <h3>Confirm Deletion</h3>
                                    <p>Are you sure you want to delete the following product?</p>
                                    
                                    <div class="product-info">
                                        <strong>Product ID: </strong><?php echo $pid; ?><br>
                                        <strong>Product Name: </strong><?php echo $productName; ?>
                                    </div>

                                    <div id="warning-message">
                                        <strong>Warning:</strong> This action will delete/deactivate the product and cannot be easily undone.
                                    </div>

                                    <form method="POST">
                                        <input type="hidden" name="pid" value="<?php echo $pid; ?>">
                                        <div class="action-buttons">
                                            <input type="submit" value="Yes, Delete Product" id="btn-delete">
                                            <button type="button" onclick="window.location.href='staff/product/manage-products.php'">Cancel</button>
                                        </div>
                                    </form>

                                </div>

                            <?php else: ?>
                                <div class="alert alert-danger">
                                    No product selected or product not found.
                                </div>
                                <div class="action-buttons">
                                    <button onclick="window.location.href='staff/product/manage-products.php'">Back to Manage Products</button>
                                </div>

                            <?php endif; ?>

                        </div>

                    </div>

                </div>

            </div>

            <?php require_once __DIR__ . "/../../includes/footer.php"; ?>
            <?php require_once __DIR__ . "/../../includes/tools.php"; ?>

        </div>

        <script src="script/script-general.js"></script>
        <script src="script/script-manage-products.js"></script>

    </body>

</html>
