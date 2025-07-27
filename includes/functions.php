<?php

// Start session
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
function isLoggedIn() {
    return isset($_SESSION["userinfo"]["user_id"]);
}

function isCustomer() {
    return (isset($_SESSION["userinfo"]["user_type"]) && $_SESSION["userinfo"]["user_type"] == "customer");
}

function isStaff() {
    return (isset($_SESSION["userinfo"]["user_type"]) && $_SESSION["userinfo"]["user_type"] == "staff");
}

// Redirect if not logged in
function requireLogin() {
    if (!isLoggedIn()) {
        header("Location: ../login.php");
        exit();
    }
}

// Display error messages
function display_error($error) {
    if (!empty($error)) {
        echo '<div class="alert alert-danger">' . $error . '</div>';
    }
}

// Sanitize input data
function sanitize($data) {
    global $conn;
    return htmlspecialchars(strip_tags(trim($data)));
}

// Record lookup
function findItemById($item_array, $item_id) {
    foreach ($item_array as $item) {
        if ($item["id"] == $item_id) {
            return $item;
        }
    }
    return null;
}

function calculateMaxProducible($productId, $prodmatList, $materialLookup) {

    if (!isset($prodmatList[$productId])) {
        return 0;
    }
    
    $materials = $prodmatList[$productId];
    $minProducible = null;
    
    foreach ($materials as $material) {

        $materialId = $material["id"];
        $qtyPerUnit = $material["qty"];
        
        if (isset($materialLookup[$materialId])) {

            $materialInfo = $materialLookup[$materialId];
            $availableStock = $materialInfo["quantity"] - $materialInfo["reserved"];
            
            if ($qtyPerUnit > 0) {
                $possibleQuantity = floor($availableStock / $qtyPerUnit);
                if ($minProducible === null) {
                    $minProducible = $possibleQuantity;
                } else {
                    $minProducible = min($minProducible, $possibleQuantity);
                }
            }
        } else {
            return 0;
        }
    }

    return ($minProducible === null) ? 0 : max(0, $minProducible);

}

function getAvailableProducts($conn) {
    
    // Load products
    $productList = [];
    $sql = "SELECT pid, pname, pdesc, pimage, pcost, pavail FROM product WHERE pavail = 1 ORDER BY pid";
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

    // Load materials
    $materialList = [];
    $sql = "SELECT * FROM material ORDER BY mid";
    $rs = mysqli_query($conn, $sql) or die(mysqli_error($conn));
    while ($rc = mysqli_fetch_assoc($rs)) {
        $materialList[] = array(
            "id" => (int)$rc["mid"],
            "name" => $rc["mname"],
            "quantity" => (int)$rc["mqty"],
            "reserved" => (int)$rc["mrqty"],
            "unit" => $rc["munit"],
            "reorder" => (int)$rc["mreorderqty"],
            "image" => $rc["mimage"],
            "availability" => $rc["mavail"]
        );
    }

    // Load prodmat
    $prodmatList = [];
    $sql = "SELECT * FROM prodmat ORDER BY pid ASC, mid ASC";
    $rs = mysqli_query($conn, $sql) or die(mysqli_error($conn));
    while ($rc = mysqli_fetch_assoc($rs)) {
        $prodmatList[$rc["pid"]][] = array(
            "id" => (int)$rc["mid"],
            "qty" => (int)$rc["pmqty"]
        );
    }

    // Create material lookup
    $materialLookup = [];
    foreach ($materialList as $material) {
        $materialLookup[$material["id"]] = $material;
    }

    // Calculate available quantities
    $availableProductList = [];
    foreach ($productList as $product) {
        $productId = $product["id"];
        $availableQuantity = calculateMaxProducible($productId, $prodmatList, $materialLookup);
        
        if ($availableQuantity > 0) {
            $product["quantity"] = $availableQuantity;
            $product["producible_quantity"] = $availableQuantity;
            $availableProductList[] = $product;
        }
    }

    mysqli_free_result($rs);
    
    return [
        'products' => $availableProductList,
        'materials' => $materialList,
        'prodmat' => $prodmatList,
        'material_lookup' => $materialLookup
    ];

}

function validateCrossProductMaterialAvailability($cart, $prodmatList, $materialLookup) {
    $totalMaterialNeeded = [];
    $errors = [];
    
    // Calculate total material needed across all cart items
    foreach ($cart as $product_id => $quantity) {
        if ($quantity > 0 && isset($prodmatList[$product_id])) {
            foreach ($prodmatList[$product_id] as $material) {
                $materialId = $material["id"];
                $qtyPerUnit = $material["qty"];
                $totalNeeded = $qtyPerUnit * $quantity;
                
                if (!isset($totalMaterialNeeded[$materialId])) {
                    $totalMaterialNeeded[$materialId] = 0;
                }
                $totalMaterialNeeded[$materialId] += $totalNeeded;
            }
        }
    }
    
    // Check if total needed exceeds available for each material
    foreach ($totalMaterialNeeded as $materialId => $totalNeeded) {
        if (isset($materialLookup[$materialId])) {
            $materialInfo = $materialLookup[$materialId];
            $available = $materialInfo["quantity"] - $materialInfo["reserved"];
            
            if ($totalNeeded > $available) {
                $materialName = $materialInfo["name"];
                $errors[] = "Material '{$materialName}': Need {$totalNeeded} units, but only {$available} available";
            }
        } else {
            $errors[] = "Material ID {$materialId} not found in system";
        }
    }
    
    return $errors;
}

?>