<!DOCTYPE html>
<html lang="en">

    <head>
        <base href="../">
        <?php require_once __DIR__ . "/../includes/head-setting.php"; ?>
        <link id="place-order-css" rel="stylesheet" href="styles/style-place-orders.css">
        <title>Smile & Sunshine | Place Orders</title>
    </head>

    <?php

    requireLogin();

    function isCartEmpty($shopping_cart) {

        if ($shopping_cart == []) {
            return true;
        }
        
        foreach ($shopping_cart as $product_id => $quantity) {
            if ($quantity > 0) {
                return false;
            }
        }
        
        return true;
    }

    $productData = getAvailableProducts($conn);
    $availableProductList = $productData["products"];
    $materialList = $productData["materials"];
    $prodmatList = $productData["prodmat"];
    $materialLookup = $productData["material_lookup"];

    $totalAmount = 0;

    if (isset($_POST["remove"])) {

        $product_id = $_POST["remove"];
        $_SESSION["shopping_cart"][$product_id] = 0;

    } elseif (isset($_POST["clear"])) {

        $_SESSION["shopping_cart"] = [];

    } elseif (isset($_POST["confirm"])) {

        if (password_verify($_POST["confirm"], $_SESSION["userinfo"]["password"])) {

            if (!isCartEmpty($_SESSION["shopping_cart"])) {

                $validationErrors = [];
            
                foreach ($_SESSION["shopping_cart"] as $product_id => $quantity) {
                    if ($quantity > 0) {
                        $currentProduct = findItemById($availableProductList, $product_id);
                        if (!$currentProduct) {
                            $validationErrors[] = "Product ID {$product_id} is no longer available.";
                        }
                    }
                }

                if (empty($validationErrors)) {
                    $materialErrors = validateCrossProductMaterialAvailability($_SESSION["shopping_cart"], $prodmatList, $materialLookup);
                    $validationErrors = array_merge($validationErrors, $materialErrors);
                }

                if (!empty($validationErrors)) {
                    $message = "Order cannot be processed:\n\n" . 
                          implode("\n", $validationErrors) . 
                          "\n\nPlease update your cart.";
                } else {

                    $allOrdersSuccessful = true;
                    $errorMessage = "";

                    foreach ($_SESSION["shopping_cart"] as $product_id => $quantity) {

                        if ($quantity > 0) {

                            $unitPrice = findItemById($availableProductList, $product_id)["price"];
                            $totalCost = $unitPrice * $quantity;

                            $prodStmt = $conn->prepare("INSERT INTO production (pstatus) VALUES ('open')");

                            if ($prodStmt->execute()) {

                                $proid = $conn->insert_id;
                                $prodStmt->close();

                                $orderStmt = $conn->prepare("INSERT INTO orders (odate, pid, oqty, ocost, cid, ostatus, ocancel, proid) VALUES (?, ?, ?, ?, ?, 1, 0, ?)");
                                $orderDate = date("Y-m-d H:i:s");
                                $userId = $_SESSION["userinfo"]["user_id"];
                                $orderStmt->bind_param("siidii", $orderDate, $product_id, $quantity, $totalCost, $userId, $proid);
                                
                                if ($orderStmt->execute()) {
                                    $oid = $conn->insert_id;
                                    $orderStmt->close();
                                    
                                    if (isset($prodmatList[$product_id])) {
                                        foreach ($prodmatList[$product_id] as $material) {

                                            $materialId = $material["id"];
                                            $qtyPerUnit = $material["qty"];
                                            $totalReserved = $qtyPerUnit * $quantity;
                                            
                                            $reserveStmt = $conn->prepare("UPDATE material SET mrqty = mrqty + ? WHERE mid = ?");
                                            $reserveStmt->bind_param("ii", $totalReserved, $materialId);
                                            $reserveStmt->execute();
                                            $reserveStmt->close();

                                            $actualmatStmt = $conn->prepare("INSERT INTO actualmat (oid, pid, mid, rqty) VALUES (?, ?, ?, ?)");
                                            $actualmatStmt->bind_param("iiii", $oid, $product_id, $materialId, $totalReserved);

                                            if (!$actualmatStmt->execute()) {
                                                $allOrdersSuccessful = false;
                                                $errorMessage = "Failed to record material reservation for order ID: " . $oid;
                                                $actualmatStmt->close();
                                                break 2;
                                            }
                                            $actualmatStmt->close();

                                        }
                                    }
                                    
                                } else {
                                    $allOrdersSuccessful = false;
                                    $errorMessage = "Failed to create order for product ID: " . $product_id;
                                    break;
                                }
                                
                            } else {
                                $allOrdersSuccessful = false;
                                $errorMessage = "Failed to create production record for product ID: " . $product_id;
                                break;
                            }
                        }
                    }

                    if ($allOrdersSuccessful) {
                        $_SESSION["shopping_cart"] = [];
                        $message = "Thank you for your order! Your order has been confirmed and production has been scheduled.";
                    } else {
                        $message = "There was an error processing your order: " . $errorMessage . ". Please try again.";
                    }

                }

            } else {
                $message = "Your cart is empty! Please add some products before placing an order.";
            }

        } else {
            $message = "Please enter a correct password!";
        }

    }

    mysqli_close($conn);

    ?>

    <body>

        <div id="main-page">

            <?php require_once __DIR__ . "/../includes/header.php"; ?>

            <div id="main-section">

                <div id="cart-container">

                    <div id="cart-header">
                        <h2>Your Order Cart</h2>
                        <p>Review and confirm your order</p>
                    </div>

                    <?php if (!isset($_SESSION["shopping_cart"]) || isCartEmpty($_SESSION["shopping_cart"])): ?>
                        <div id="empty-cart">
                            Your cart is empty. &nbsp;<a href="customer/browse-products.php">Browse Products</a>
                        </div>
                    <?php else: ?>

                        <div id="options">
                            <div id="warning">
                                <label id="warning-label">* Foreign currency amounts are for reference only</label>
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

                        <form id="order-form" method="POST" action="customer/place-orders.php">

                            <div id="cart-items">

                                <table id="cart-table">
                                    <thead>
                                        <tr>
                                            <th class="col1">Product</th>
                                            <th class="col2">Name</th>
                                            <th class="col3">Price</th>
                                            <th class="col4">Quantity</th>
                                            <th class="col5">Total</th>
                                            <th class="col6">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody id="cart-body">
                                        <?php foreach ($_SESSION["shopping_cart"] as $product_id => $quantity): ?>
                                            <?php if ($quantity > 0): ?>
                                                <?php $item = findItemById($availableProductList, $product_id) ?>
                                                <?php $totalAmount += $item["price"] * $quantity; ?>
                                                <tr id="cart-row-<?php echo $product_id; ?>">
                                                    <td><img src="img/product/<?php echo $item["image"]; ?>"></td>
                                                    <td class="product-name"><?php echo $item["name"]; ?></td>
                                                    <td><span id="price-<?php echo $product_id; ?>"><?php echo "US$" . number_format($item["price"], 2); ?></span></td>
                                                    <td>
                                                        <div class="quantity">
                                                            <button type="submit" onclick="updateCart(<?php echo $product_id; ?>, -1); return false;">-</button>
                                                            <input type="number" id="quantity-<?php echo $product_id; ?>"
                                                                name="quantity-<?php echo $product_id; ?>" value="<?php echo $quantity; ?>" class="quantity-input"
                                                                min="0" max="<?php echo $item["quantity"]; ?>"
                                                                onchange="validateCart(<?php echo $product_id; ?>)">
                                                            <button type="submit" onclick="updateCart(<?php echo $product_id; ?>, 1); return false;">+</button>
                                                        </div>
                                                    </td>
                                                    <td id="cart-total-<?php echo $product_id; ?>" class="item-total col5"><?php echo "US$" . number_format($item["price"] * $quantity, 2); ?></td>
                                                    <td><button type="submit" name="remove" value="<?php echo $product_id; ?>" class="delete-btn">Remove</button></td>
                                                </tr>
                                            <?php endif; ?>
                                        <?php endforeach; ?>
                                    </tbody>
                                    <tfoot>
                                        <tr id="cart-total">
                                            <td colspan="4" id="total-text"><strong>Order Total:</strong></td>
                                            <td id="cart-total-amount" class="col5"><?php echo "US$" . number_format($totalAmount, 2); ?></td>
                                            <td></td>
                                        </tr>
                                    </tfoot>
                                </table>

                            </div>

                            <div id="customer-info">

                                <div class="customer-info-header">
                                    <h3>Customer Information</h3>
                                    <a href="update-profile.php" id="edit-button">Edit Information</a>
                                </div>

                                <div class="info-fields">

                                    <div class="info-field">
                                        <label>Customer ID:</label>
                                        <span id="customer-id"><?php echo $_SESSION["userinfo"]["user_id"]; ?></span>
                                    </div>
                                    <div class="info-field">
                                        <label>Customer Name:</label>
                                        <span id="customer-name"><?php echo $_SESSION["userinfo"]["username"]; ?></span>
                                    </div>
                                    <div class="info-field">
                                        <label>Company Name:</label>
                                        <span id="customer-companyname"><?php echo $_SESSION["userinfo"]["company"]; ?></span>
                                    </div>
                                    <div class="info-field">
                                        <label>Contact Number:</label>
                                        <span id="customer-telephone"><?php echo $_SESSION["userinfo"]["telephone"]; ?></span>
                                    </div>
                                    <div class="info-field">
                                        <label>Address:</label>
                                        <span id="customer-address"><?php echo $_SESSION["userinfo"]["address"]; ?></span>
                                    </div>

                                    <?php if (!isset($_SESSION["userinfo"]["telephone"]) || !isset($_SESSION["userinfo"]["address"])) {
                                        $message = "Please update your profile, including contact number and address, before placing your order!";
                                    }?>

                                </div>

                            </div>

                            <div id="cart-actions">
                                <button type="button" id="browse-product" onclick="window.location.href='customer/browse-products.php'" class="action-button">Browse Products</button>
                                <button type="submit" id="confirm-order" name="confirm" value="confirm" class="action-button"
                                    onclick="return confirmOrder()">Confirm Order</button>
                                <button type="submit" id="clear-cart" name="clear" value="clear" class="action-button"
                                    onclick="return window.confirm('Are you sure that you want to clear the cart?');">Clear Cart</button>
                            </div>

                        </form>

                    <?php endif; ?>

                </div>

            </div>

            <?php require_once __DIR__ . "/../includes/footer.php"; ?>
            <?php require_once __DIR__ . "/../includes/tools.php"; ?>

        </div>

        <script>
            var productList = <?php echo json_encode($availableProductList); ?>;
            var totalAmount = <?php echo $totalAmount; ?>;
        </script>

        <script src="script/script-general.js"></script>
        <script src="script/script-place-orders.js"></script>

        <?php if (isset($message) && $message != null) {
            echo "<script type='text/javascript'>alert(" . json_encode($message) . "); </script>";
        } ?>

    </body>

</html>