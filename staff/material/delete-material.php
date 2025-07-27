<?php

require_once __DIR__ . "/../../config/database.php";
require_once __DIR__ . "/../../includes/functions.php";

requireLogin();

$error = "";
$success = "";

$mid = $_GET["mid"] ?? null;
$materialName = "";
$hasReservedQty = false;

if ($mid && $_SERVER["REQUEST_METHOD"] != "POST") {

    $stmt = $conn->prepare("SELECT mname, mrqty FROM material WHERE mid = ? AND mavail = 1");
    $stmt->bind_param("i", $mid);
    $stmt->execute();
    $result = $stmt->get_result();

    // Check if there is any existing reserved material
    if ($result && $row = $result->fetch_assoc()) {
        $materialName = $row["mname"];
        $reservedQty = $row["mrqty"];
        $hasReservedQty = $reservedQty > 0;
    } else {
        $error = "Material not found or already deleted.";
    }
    $stmt->close();

}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["mid"])) {

    $mid = $_POST["mid"];

    $stmt = $conn->prepare("SELECT mname FROM material WHERE mid = ?");
    $stmt->bind_param("i", $mid);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result && $row = $result->fetch_assoc()) {
        $materialName = $row["mname"];
    }
    $stmt->close();
    
    // Deactivate material
    $stmt = $conn->prepare("UPDATE material SET mavail = 0 WHERE mid = ?");
    $stmt->bind_param("i", $mid);
    if ($stmt->execute()) {
        $success = "Material " . $materialName . " deactivated successfully.";
    } else {
        $error = "Failed to deactivate the material.";
    }
    $stmt->close();

}

?>

<!DOCTYPE html>
<html lang="en">

    <head>
        <base href="../../">
        <?php require_once __DIR__ . "/../../includes/head-setting.php"; ?>
        <link rel="stylesheet" href="styles/style-items.css">
        <title>Smile & Sunshine | Delete Material</title>
    </head>

    <body>

        <div id="main-page">

            <?php require_once __DIR__ . "/../../includes/header.php"; ?>

            <div id="main-section">

                <div id="items-container">

                    <div id="items-header">
                        <h2>Delete Material</h2>
                        <p>Remove material from material list</p>
                    </div>

                    <div id="items-section">

                        <div id="delete-confirmation">

                            <?php if ($error): ?>
                                <div class="alert alert-danger">
                                    <?php echo $error; ?>
                                </div>
                                <div class="action-buttons">
                                    <button onclick="window.location.href='staff/material/manage-materials.php'">Back to Manage Materials</button>
                                </div>

                            <?php elseif ($success): ?>
                                <div class="alert alert-success">
                                    <?php echo $success; ?>
                                </div>
                                <div class="action-buttons">
                                    <button onclick="window.location.href='staff/material/manage-materials.php'">Back to Manage Materials</button>
                                </div>

                            <?php elseif ($mid && $materialName && $hasReservedQty): ?>

                                <div class="confirmation-content">

                                    <h3>Cannot Delete Material</h3>
                                    <p>This material cannot be deleted because it has reserved quantities.</p>
                                    
                                    <div class="product-info">
                                        <strong>Material ID: </strong><?php echo $mid; ?><br>
                                        <strong>Material Name: </strong><?php echo $materialName; ?>
                                    </div>

                                    <div class="error-message">
                                        <strong>Restriction:</strong> Materials with reserved quantities cannot be deleted/deactivated.
                                    </div>

                                    <div class="action-buttons">
                                        <button onclick="window.location.href='staff/material/manage-materials.php'">Back to Manage Materials</button>
                                    </div>

                                </div>

                            <?php elseif ($mid && $materialName): ?>

                                <div class="confirmation-content">

                                    <h3>Confirm Deletion</h3>
                                    <p>Are you sure you want to delete the following material?</p>
                                    
                                    <div class="product-info">
                                        <strong>Material ID: </strong><?php echo $mid; ?><br>
                                        <strong>Material Name: </strong><?php echo $materialName; ?>
                                    </div>

                                    <div id="warning-message">
                                        <strong>Warning:</strong> This action will delete/deactivate the material and cannot be easily undone.
                                    </div>

                                    <form method="POST">
                                        <input type="hidden" name="mid" value="<?php echo $mid; ?>">
                                        <div class="action-buttons">
                                            <input type="submit" value="Yes, Delete Material" id="btn-delete">
                                            <button type="button" onclick="window.location.href='staff/material/manage-materials.php'">Cancel</button>
                                        </div>
                                    </form>

                                </div>

                            <?php else: ?>
                                <div class="alert alert-danger">
                                    No material selected or material not found.
                                </div>
                                <div class="action-buttons">
                                    <button onclick="window.location.href='staff/material/manage-materials.php'">Back to Manage Materials</button>
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
        <script src="script/script-manage-materials.js"></script>

    <body>

</html>