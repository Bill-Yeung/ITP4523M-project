<?php

require_once __DIR__ . "/../../config/database.php";
require_once __DIR__ . "/../../includes/functions.php";

requireLogin();

$message = "";
$materials = [];

$nextIdQuery = $conn->query("SELECT MAX(pid) as max_pid FROM product");
$nextId = 1;
if ($nextIdQuery && $nextIdQuery->num_rows > 0) {
    $result = $nextIdQuery->fetch_assoc();
    $nextId = $result["max_pid"] ? $result["max_pid"] + 1 : 1;
}

$materialQuery = $conn->query("SELECT mid, mname FROM material WHERE mavail = 1");
if ($materialQuery && $materialQuery->num_rows > 0) {
    $materials = $materialQuery->fetch_all(MYSQLI_ASSOC);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $pname = $_POST["product-name"];
    $pdesc = $_POST["product-description"];
    $pcost = floatval($_POST["product-cost"]);

    $totalMaterialQty = 0;
    if (isset($_POST["materials"]) && is_array($_POST["materials"])) {
        foreach ($_POST["materials"] as $mid => $qty) {
            $totalMaterialQty += intval($qty);
        }
    }

    if (empty($pname) || empty($pdesc) || $pcost <= 0) {
        $message = "All fields are required and must be valid.";
    } else if ($totalMaterialQty <= 0) {
        $message = "At least one material quantity must be greater than 0.";
    } else {
        $stmt = $conn->prepare("INSERT INTO product (pname, pdesc, pcost, pavail) VALUES (?, ?, ?, 1)");
        $stmt->bind_param("ssd", $pname, $pdesc, $pcost);

        if ($stmt->execute()) {
            $new_pid = $stmt->insert_id;
            $stmt->close();

            if (isset($_FILES["product-image"]) && $_FILES["product-image"]["error"] == UPLOAD_ERR_OK) {
                $imageFileType = strtolower(pathinfo($_FILES["product-image"]["name"], PATHINFO_EXTENSION));
                $filename = $new_pid . "." . $imageFileType;
                $relativePath = $filename;
                $absolutePath = __DIR__ . "/../../img/product/" . $filename;
                $allowed = ['jpg', 'jpeg', 'png', 'gif'];

                if (in_array($imageFileType, $allowed)) {
                    if (move_uploaded_file($_FILES["product-image"]["tmp_name"], $absolutePath)) {
                        $stmtUpdate = $conn->prepare("UPDATE product SET pimage = ? WHERE pid = ?");
                        $stmtUpdate->bind_param("si", $relativePath, $new_pid);
                        $stmtUpdate->execute();
                        $stmtUpdate->close();
                    } else {
                        $message = "Failed to upload image.";
                    }
                } else {
                    $message = "Invalid image format. Only JPG, JPEG, PNG, GIF are allowed.";
                }
            }

            if (isset($_POST["materials"]) && is_array($_POST["materials"])) {
                foreach ($_POST["materials"] as $mid => $qty) {
                    $qty = intval($qty);
                    if ($qty > 0) {
                        $stmtMat = $conn->prepare("INSERT INTO prodmat (pid, mid, pmqty) VALUES (?, ?, ?)");
                        $stmtMat->bind_param("iii", $new_pid, $mid, $qty);
                        $stmtMat->execute();
                        $stmtMat->close();
                    }
                }
            }

            $message = "Product added successfully.";
            echo "<script type='text/javascript'>alert('" . $message . "');
            window.location.href = './../product/manage-products.php';
            </script>";
            exit();

        } else {
            $message = "Database error while inserting product.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <base href="../../">
    <?php require_once __DIR__ . "/../../includes/head-setting.php"; ?>
    <link rel="stylesheet" href="styles/style-items.css">
    <title>Smile & Sunshine | Add Product</title>
</head>

<body>

<div id="main-page">

    <?php require_once __DIR__ . "/../../includes/header.php"; ?>

    <div id="main-section">

        <div id="items-container">

            <div id="items-header">
                <h2>Add Product</h2>
                <p>Add a new product to the inventory list</p>
            </div>

            <div id="items-controls">
                <div id="control-actions">
                    <button type="button" id="back-btn" onclick="window.location.href='./staff/product/manage-products.php'">Back to Manage Products</button>
                </div>
            </div>

            <div id="items-section">

                <form method="POST" enctype="multipart/form-data">

                    <div id="add-item-form">

                        <table id="add-item">
                            <tr>
                                <td><label for="product-id">Product ID:</label></td>
                                <td><strong><?php echo $nextId; ?></strong> (auto-generated)</td>
                            </tr>
                            <tr>
                                <td><label for="product-name">Product Name:</label></td>
                                <td><input type="text" name="product-name" required></td>
                            </tr>
                            <tr>
                                <td><label for="product-description">Description:</label></td>
                                <td><textarea name="product-description" rows="5" required></textarea></td>
                            </tr>
                            <tr>
                                <td><label for="product-cost">Product Cost:</label></td>
                                <td><input type="number" name="product-cost" min="0" step="0.01" required></td>
                            </tr>
                            <tr>
                                <td><label for="product-image">Upload Image:</label></td>
                                <td><input type="file" name="product-image" accept=".jpg,.jpeg,.png,.gif" required></td>
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
                                                        <input type="number" name="materials[<?php echo $material["mid"]; ?>]" min="0" value="0">
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </td>
                            </tr>
                            <tr>
                                <td colspan="2" id="button-row">
                                    <input id="btn-submit" type="submit" value="Submit">
                                    <input id="btn-reset" type="reset" value="Reset">
                                </td>
                            </tr>
                        </table>

                    </div>

                </form>

            </div>

        </div>

    </div>

    <?php require_once __DIR__ . "/../../includes/footer.php"; ?>
    <?php require_once __DIR__ . "/../../includes/tools.php"; ?>

</div>

<?php if (isset($message) && $message != null) {
    echo "<script type='text/javascript'>alert('" . $message . "'); </script>";
} ?>

<script src="script/script-general.js"></script>
<script src="script/script-manage-products.js"></script>

</body>

</html>
