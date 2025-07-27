<!DOCTYPE html>
<html lang="en">

    <head>
        <base href="../">
        <?php require_once __DIR__ . "/../includes/head-setting.php"; ?>
        <link id="browse-products-css"  rel="stylesheet" href="styles/style-browse-products.css">
        <title>Smile & Sunshine | Browse Products</title>
    </head>

    <?php

    requireLogin();

    $productData = getAvailableProducts($conn);
    $availableProductList = $productData["products"];
    $materialList = $productData["materials"];
    $prodmatList = $productData["prodmat"];
    $materialLookup = $productData["material_lookup"];

    if (!isset($_SESSION["shopping_cart"])) {
        $_SESSION["shopping_cart"] = [];
    }

    if (isset($_POST["product_id"])) {

        $product_id = $_POST["product_id"];

        if (!isset($_SESSION["shopping_cart"][$product_id])) {
            $_SESSION["shopping_cart"][$product_id] = 0;
        }

        $currentQuantity = $_SESSION["shopping_cart"][$product_id];
        $addQuantity = $_POST["quantity"];
        $newQuantity = $currentQuantity + $addQuantity;
        $availQuantity = findItemById($availableProductList, $product_id)["quantity"];
        $productName = findItemById($availableProductList, $product_id)["name"];

        if ($newQuantity > $availQuantity) {
            $message = "You currently have " . $currentQuantity . " units of " . $productName . " in your cart. Due to limited stock and material availability, you can only further add " . 
            ($availQuantity - $currentQuantity) . " units to the cart. If you need a bulk purchase, please contact us.";
        } else {
            $_SESSION["shopping_cart"][$product_id] += $addQuantity;
            $message = "Added to cart successfully!";
        }

    }

    mysqli_close($conn);

    ?>

    <body>

        <div id="main-page">

            <?php require_once __DIR__ . "/../includes/header.php"; ?>

            <div id="main-section">

                <div id="product-container">

                    <div id="products-header">
                        <h2>Our Products</h2>
                        <p>Choose from our high-quality toys</p>
                    </div>

                    <div id="search-section">

                        <div id="search-tools">

                            <div id="search-input">
                                <input type="text" id="search" placeholder="Search by name or description...">
                                <button type="button" id="search-button" onclick="performSearch()">Search</button>
                                <button type="button" id="reset-button" onclick="resetSearch()">Reset</button>
                            </div>
                            
                            <div id="options">

                                <div id="sort-options">
                                    <label for="sort-select">Sort by:</label>
                                    <select id="sort-select" onchange="changeSort()">
                                        <option value="default">Default</option>
                                        <option value="price-low">Price: Low to High</option>
                                        <option value="price-high">Price: High to Low</option>
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

                        </div>

                    </div>

                    <div id="product-section">
                        <div id="result-count">
                            <span id="product-count"><?php echo count($availableProductList); ?></span><span> products found</span>
                        </div>
                        <div id="product-table">
                        </div>
                    </div>

                </div>

            </div>

            <?php require_once __DIR__ . "/../includes/footer.php"; ?>
            <?php require_once __DIR__ . "/../includes/tools.php"; ?>
            
        </div>

        <script>
            var productList = <?php echo json_encode($availableProductList); ?>;
            var materialList = <?php echo json_encode($materialList); ?>;
            var prodmatList = <?php echo json_encode($prodmatList); ?>;
        </script>

        <script src="script/script-general.js"></script>
        <script src="script/script-browse-products.js"></script>

        <?php if (isset($message) && $message != null) {
            echo "<script type='text/javascript'>alert('" . $message . "'); </script>";
        } ?>

    </body>

</html>