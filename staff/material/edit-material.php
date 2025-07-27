<?php

require_once __DIR__ . "/../../config/database.php";
require_once __DIR__ . "/../../includes/functions.php";

requireLogin();

$material = null;
$materialId = null;
$errorMessage = "";

if (isset($_GET["mid"])) {

    $materialId = $_GET["mid"];

    $stmt = $conn->prepare("SELECT * FROM material WHERE mid = ?");
    if ($stmt) {
        $stmt->bind_param("i", $materialId);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $material = $result->fetch_assoc();
        } else {
            $errorMessage = "Material not found.";
        }
        $stmt->close();
    } else {
        $errorMessage = "Error in loading the material: " . $conn->error;
    }

} else {
    $errorMessage = "No material ID specified.";
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["material-id"])) {

    $mid_to_update = $_POST["material-id"];
    $mname = trim($_POST["material-name"]);
    $mqty = intval($_POST["material-quantity"]);
    $munit = trim($_POST["material-unit"]);
    $mreorderqty = intval($_POST["material-reorder"]);

    if (isset($_FILES["material-image"]) && $_FILES["material-image"]["error"] == UPLOAD_ERR_OK) {

        $imageFileType = strtolower(pathinfo($_FILES["material-image"]["name"], PATHINFO_EXTENSION));
        $filename = $mid_to_update . "_" . date('YmdHis') . "." . $imageFileType;
        $relativePath = $filename;
        $absolutePath = __DIR__ . "/../../img/material/" . $filename;
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];

        if (in_array($imageFileType, $allowed)) {
            if (move_uploaded_file($_FILES["material-image"]["tmp_name"], $absolutePath)) {
                if (!empty($material["mimage"]) && $material["mimage"] != $filename) {
                    $oldImagePath = __DIR__ . "/../../img/material/" . $material["mimage"];
                    if (file_exists($oldImagePath)) {
                        unlink($oldImagePath);
                    }
                }
                $stmtUpdate = $conn->prepare("UPDATE material SET mimage = ? WHERE mid = ?");
                $stmtUpdate->bind_param("si", $relativePath, $mid_to_update);
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

        $sql_update = "UPDATE material SET mname = ?, mqty = ?, munit = ?, mreorderqty = ? WHERE mid = ?";
        $stmt_update = $conn->prepare($sql_update);

        if ($stmt_update) {

            $types = "sisii";
            $params = [$mname, $mqty, $munit, $mreorderqty, $mid_to_update];
            $stmt_update->bind_param($types, ...$params);

            if ($stmt_update->execute()) {
                $stmt_update->close();
                header("Location: edit-material-result.php?status=success&mid=" . urlencode($mid_to_update) . "&mname=" . urlencode($mname));
                exit();

            } else {

                header("Location: edit-material-result.php?status=dberror&mid=" . urlencode($mid_to_update) . "&msg=" . urlencode("Database error during update: " . $stmt_update->error));
                exit();

            }
        } else {

            header("Location: edit-material-result.php?status=preperror&msg=" . urlencode("Database error preparing statement: " . $conn->error));
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
        <title>Smile & Sunshine | Edit Material</title>
    </head>

    <body>

        <div id="main-page">

            <?php require_once __DIR__ . "/../../includes/header.php"; ?>

            <div id="main-section">

                <div id="items-container">

                    <div id="items-header">
                        <h2>Edit Material</h2>
                        <p>Modify material details and information</p>
                    </div>

                    <div id="items-controls">
                        <div id="control-actions">
                            <button type="button" id="back-btn" onclick="window.location.href='staff/material/manage-materials.php'">Back to Manage Materials</button>
                        </div>
                    </div>

                    <div id="items-section">

                        <?php if (!empty($errorMessage)): ?>
                            <div id="alert">
                                <?php echo $errorMessage; ?>
                            </div>
                        <?php endif; ?>

                        <?php if ($material): ?>

                            <form method="POST"
                                action="<?php echo $_SERVER["PHP_SELF"] . '?mid=' . urlencode($materialId); ?>"
                                enctype="multipart/form-data">

                                <div id="add-item-form">

                                    <table id="add-item">
                                        <tr>
                                            <td><label for="material-id">Material ID:</label></td>
                                            <td>
                                                <strong><?php echo $material["mid"]; ?></strong>
                                                <input type="hidden" name="material-id" value="<?php echo $material["mid"]; ?>">
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><label for="material-name">Material Name:</label></td>
                                            <td><input type="text" name="material-name" value="<?php echo $material["mname"]; ?>" required></td>
                                        </tr>
                                        <tr>
                                            <td><label for="current-image">Current Image:</label></td>
                                            <td>
                                                <?php if ($material && !empty($material["mimage"]) && file_exists(__DIR__ . "/../../img/material/" . $material["mimage"])): ?>
                                                    <img class="item-image" src="img/material/<?php echo $material["mimage"]; ?>">
                                                <?php else: ?>
                                                    <span>No image available</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><label for="material-image">Upload New Image:</label></td>
                                            <td>
                                                <label>Leave empty to keep current image (Max: 25MB)</label><br><br>
                                                <input type="file" name="material-image" accept=".jpg,.jpeg,.png,.gif">
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><label for="material-unit">Material Unit:</label></td>
                                            <td><input type="text" name="material-unit" value="<?php echo $material["munit"]; ?>" required></td>
                                        </tr>
                                        <tr>
                                            <td><label for="material-quantity">Material Quantity:</label></td>
                                            <td><input type="number" name="material-quantity" min="0" value="<?php echo $material["mqty"]; ?>" required></td>
                                        </tr>
                                        <tr>
                                            <td><label for="material-reorder">Material Reorder Level:</label></td>
                                            <td><input type="number" name="material-reorder" min="0" value="<?php echo $material["mreorderqty"]; ?>" required></td>
                                        </tr>
                                        <tr>
                                            <td colspan="2" id="button-row">
                                                <input id="btn-submit" type="submit" value="Save Changes">
                                                <input id="btn-reset" type="button" value="Cancel" onclick="window.location.href='staff/material/manage-materials.php'">
                                            </td>
                                        </tr>

                                    </table>

                                </div>

                            </form>

                        <?php else: ?>
                            <p>Material details could not be loaded. <?php echo $errorMessage; ?></p>
                            <p><a onclick="window.location.href='staff/material/manage-materials.php'">Back to Manage Materials</a></p>
                        <?php endif; ?>

                    </div>

                </div>

            </div>

            <?php require_once __DIR__ . "/../../includes/footer.php"; ?>
            <?php require_once __DIR__ . "/../../includes/tools.php"; ?>

        </div>

        <script src="script/script-general.js"></script>
        <script src="script/script-manage-materials.js"></script>

    </body>

</html>