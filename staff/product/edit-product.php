<?php

require_once __DIR__ . "/../../config/database.php";
require_once __DIR__ . "/../../includes/functions.php";

requireLogin();

$product = null;
$productId = null;
$errorMessage = "";
$materials = [];

if (isset($_GET["pid"])) {

    $productId = $_GET["pid"];

    $stmt = $conn->prepare("SELECT * FROM product WHERE pid = ?");
    if ($stmt) {
        $stmt->bind_param("i", $productId);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $product = $result->fetch_assoc();
        } else {
            $errorMessage = "Product not found.";
        }
        $stmt->close();
    } else {
        $errorMessage = "Error in loading the product: " . $conn->error;
    }

    $stmt = $conn->prepare("SELECT m.mid, m.mname, pm.pmqty
                            FROM material m, prodmat pm
                            WHERE m.mid = pm.mid AND pm.pid = ?");
    $stmt->bind_param("i", $productId);
    $stmt->execute();
    $materials = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
} else {
    $errorMessage = "No product ID specified.";
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["product-id"])) {

    $pid_to_update = $_POST["product-id"];
    $pname = trim($_POST["product-name"]);
    $pdesc = trim($_POST["product-description"]);
    $pcost = floatval($_POST["product-cost"]);

    if (isset($_FILES["product-image"]) && $_FILES["product-image"]["error"] == UPLOAD_ERR_OK) {

        $imageFileType = strtolower(pathinfo($_FILES["product-image"]["name"], PATHINFO_EXTENSION));
        $filename = $pid_to_update . "_" . date('YmdHis') . "." . $imageFileType;
        $relativePath = $filename;
        $absolutePath = __DIR__ . "/../../img/product/" . $filename;
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];

        if (in_array($imageFileType, $allowed)) {
            if (move_uploaded_file($_FILES["product-image"]["tmp_name"], $absolutePath)) {
                if (!empty($product["pimage"]) && $product["pimage"] != $filename) {
                    $oldImagePath = __DIR__ . "/../../img/product/" . $product["pimage"];
                    if (file_exists($oldImagePath)) {
                        unlink($oldImagePath);
                    }
                }
                $stmtUpdate = $conn->prepare("UPDATE product SET pimage = ? WHERE pid = ?");
                $stmtUpdate->bind_param("si", $relativePath, $pid_to_update);
                $stmtUpdate->execute();
                $stmtUpdate->close();
            } else {
                $errorMessage = "Failed to upload image.";
            }
        } else {
            $errorMessage = "Invalid image format. Only JPG, JPEG, PNG, GIF are allowed.";
        }

    }

    if (empty($errorMessage)) {

        $sql_update = "UPDATE product SET pname = ?, pdesc = ?, pcost = ? WHERE pid = ?";
        $stmt_update = $conn->prepare($sql_update);

        if ($stmt_update) {

            $types = "ssdi";
            $params = [$pname, $pdesc, $pcost, $pid_to_update];
            $stmt_update->bind_param($types, ...$params);

            if ($stmt_update->execute()) {
                $stmt_update->close();

                if (!empty($_POST["materials"])) {
                    foreach ($_POST["materials"] as $mid => $pmqty) {
                        $stmt_mat = $conn->prepare("UPDATE prodmat SET pmqty = ? WHERE pid = ? AND mid = ?");
                        $stmt_mat->bind_param("iii", $pmqty, $pid_to_update, $mid);
                        $stmt_mat->execute();
                        $stmt_mat->close();
                    }
                }

                header("Location: edit-product-result.php?status=success&pid=" . urlencode($pid_to_update) . "&pname=" . urlencode($pname));
                exit();

            } else {

                header("Location: edit-product-result.php?status=dberror&pid=" . urlencode($pid_to_update) . "&msg=" . urlencode("Database error during update: " . $stmt_update->error));
                exit();

            }
        } else {

            header("Location: edit-product-result.php?status=preperror&msg=" . urlencode("Database error preparing statement: " . $conn->error));
            exit();

        }

    }
    
}

if ($conn) {
    $conn->close();
}

?>

<!DOCTYPE html>
<html lang="en">

    <head>
        <base href="../../">
        <?php require_once __DIR__ . "/../../includes/head-setting.php"; ?>
        <link rel="stylesheet" href="styles/style-items.css">
        <title>Smile & Sunshine | Edit Product</title>
    </head>

    <body>

        <div id="main-page">

            <?php require_once __DIR__ . "/../../includes/header.php"; ?>

            <div id="main-section">

                <div id="items-container">

                    <div id="items-header">
                        <h2>Edit Product</h2>
                        <p>Modify product details and inventory information</p>
                    </div>

                    <div id="items-controls">
                        <div id="control-actions">
                            <button type="button" id="back-btn" onclick="window.location.href='staff/product/manage-products.php'">Back to Manage Products</button>
                        </div>
                    </div>

                    <div id="items-section">

                        <?php if (!empty($errorMessage)): ?>
                            <div id="alert">
                                <?php echo $errorMessage; ?>
                            </div>
                        <?php endif; ?>

                        <?php if ($product): ?>

                            <form method="POST"
                                action="<?php echo $_SERVER["PHP_SELF"] . '?pid=' . urlencode($productId); ?>"
                                enctype="multipart/form-data">

                                <div id="add-item-form">

                                    <table id="add-item">
                                        <tr>
                                            <td><label for="product-id">Product ID:</label></td>
                                            <td>
                                                <strong><?php echo $product["pid"]; ?></strong>
                                                <input type="hidden" name="product-id" value="<?php echo $product["pid"]; ?>">
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><label for="product-name">Product Name:</label></td>
                                            <td><input type="text" name="product-name" value="<?php echo $product["pname"]; ?>" required></td>
                                        </tr>
                                        <tr>
                                            <td><label for="product-description">Description:</label></td>
                                            <td><textarea name="product-description" rows="5" required><?php echo $product["pdesc"]; ?></textarea></td>
                                        </tr>
                                        <tr>
                                            <td><label for="product-cost">Product Cost:</label></td>
                                            <td><input type="number" name="product-cost" min="0" step="0.01" value="<?php echo $product["pcost"]; ?>" required></td>
                                        </tr>
                                        <tr>
                                            <td><label for="current-image">Current Image:</label></td>
                                            <td>
                                                <?php if ($product && !empty($product["pimage"]) && file_exists(__DIR__ . "/../../img/product/" . $product["pimage"])): ?>
                                                    <img class="item-image" src="img/product/<?php echo $product["pimage"]; ?>">
                                                <?php else: ?>
                                                    <span>No image available</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><label for="product-image">Upload New Image:</label></td>
                                            <td>
                                                <label>Leave empty to keep current image (Max: 25MB)</label><br><br>
                                                <input type="file" name="product-image" accept=".jpg,.jpeg,.png,.gif">
                                            </td>
                                        </tr>
                                        <tr>
                                            <td colspan="2"><strong id="material-header">Material Quantities</strong></td>
                                        </tr>

                                        <tr>
                                            <td colspan="2">
                                                <table id="material-table">
                                                    <thead>
                                                        <tr>
                                                            <th>Material ID</th>
                                                            <th>Material Name</th>
                                                            <th>Quantity</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php foreach ($materials as $material): ?>
                                                            <tr>
                                                                <td><?php echo $material["mid"]; ?></td>
                                                                <td><?php echo $material["mname"]; ?></td>
                                                                <td>
                                                                    <input type="number" name="materials[<?php echo $material['mid']; ?>]" min="0" 
                                                                        value="<?php echo $material["pmqty"]; ?>">
                                                                </td>
                                                            </tr>
                                                        <?php endforeach; ?>
                                                    </tbody>
                                                </table>
                                            </td>
                                        </tr>
                                        
                                        <tr>
                                            <td colspan="2" id="button-row">
                                                <input id="btn-submit" type="submit" value="Save Changes">
                                                <input id="btn-reset" type="button" value="Cancel" onclick="window.location.href='staff/product/manage-products.php'">
                                            </td>
                                        </tr>

                                    </table>

                                </div>

                            </form>

                        <?php else: ?>
                            <p>Product details could not be loaded. <?php echo $errorMessage; ?></p>
                            <p><a onclick="window.location.href='staff/product/manage-products.php'">Back to Manage Products</a></p>
                        <?php endif; ?>

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