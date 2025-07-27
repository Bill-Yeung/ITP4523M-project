<?php

require_once __DIR__ . "/../../config/database.php";
require_once __DIR__ . "/../../includes/functions.php";

requireLogin();

$message = "";

$nextIdQuery = $conn->query("SELECT MAX(mid) as max_mid FROM material");
$nextId = 1;
if ($nextIdQuery && $nextIdQuery->num_rows > 0) {
    $result = $nextIdQuery->fetch_assoc();
    $nextId = $result["max_mid"] ? $result["max_mid"] + 1 : 1;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $mname = $_POST["material-name"];
    $mqty = intval($_POST["material-quantity"]);
    $munit = strtoupper($_POST["material-unit"]);
    $mreorder = intval($_POST["material-reorder"]);

    if (empty($mname) || $mqty <= 0 || empty($munit) || $mreorder <= 0) {
        $message = "All fields are required and must be valid.";
    } else {

        $stmt = $conn->prepare("INSERT INTO material (mname, mqty, munit, mreorderqty, mrqty, mavail) VALUES (?, ?, ?, ?, 0, 1)");
        $stmt->bind_param("sisi", $mname, $mqty, $munit, $mreorder);

        if ($stmt->execute()) {
            $new_mid = $stmt->insert_id;
            $stmt->close();

            if (isset($_FILES["material-image"]) && $_FILES["material-image"]["error"] == UPLOAD_ERR_OK) {
                $imageFileType = strtolower(pathinfo($_FILES["material-image"]["name"], PATHINFO_EXTENSION));
                $filename = $new_mid . "." . $imageFileType;
                $relativePath = $filename;
                $absolutePath = __DIR__ . "/../../img/material/" . $filename;
                $allowed = ['jpg', 'jpeg', 'png', 'gif'];

                if (in_array($imageFileType, $allowed)) {
                    if (move_uploaded_file($_FILES["material-image"]["tmp_name"], $absolutePath)) {
                        $stmtUpdate = $conn->prepare("UPDATE material SET mimage = ? WHERE mid = ?");
                        $stmtUpdate->bind_param("si", $relativePath, $new_mid);
                        $stmtUpdate->execute();
                        $stmtUpdate->close();
                    } else {
                        $message = "Failed to upload image.";
                    }
                } else {
                    $message = "Invalid image format. Only JPG, JPEG, PNG, GIF are allowed.";
                }
            }

            $message = "Material added successfully.";
            echo "<script type='text/javascript'>alert('" . $message . "');
            window.location.href = 'manage-materials.php';
            </script>";
            exit();

        } else {
            $message = "Database error while inserting material.";
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
        <title>Smile & Sunshine | Add Material</title>
    </head>

    <body>

        <div id="main-page">

            <?php require_once __DIR__ . "/../../includes/header.php"; ?>

            <div id="main-section">

                <div id="items-container">

                    <div id="items-header">
                        <h2>Add Material</h2>
                        <p>Add a new material to the material list</p>
                    </div>

                    <div id="items-controls">
                        <div id="control-actions">
                            <button type="button" id="back-btn" onclick="window.location.href='staff/material/manage-materials.php'">Back to Manage Materials</button>
                        </div>
                    </div>

                    <div id="items-section">

                        <form method="POST" enctype="multipart/form-data">

                            <div id="add-item-form">

                                <table id="add-item">

                                    <tr>
                                        <td><label for="material-id">Material ID:</label></td>
                                        <td><strong><?php echo $nextId; ?></strong> (auto-generated)</td>
                                    </tr>
                                    <tr>
                                        <td><label for="material-name">Material Name:</label></td>
                                        <td><input type="text" name="material-name" required></td>
                                    </tr>
                                    <tr>
                                        <td><label for="material-image">Upload Image:</label></td>
                                        <td><input type="file" name="material-image" accept=".jpg,.jpeg,.png,.gif" required></td>
                                    </tr>
                                    <tr>
                                        <td><label for="material-unit">Material Unit:</label></td>
                                        <td><input type="text" name="material-unit" required></td>
                                    </tr>
                                    <tr>
                                        <td><label for="material-quantity">Material Quantity:</label></td>
                                        <td><input type="number" name="material-quantity" min="0" required></td>
                                    </tr>
                                    <tr>
                                        <td><label for="material-reserved">Material Reserved Quantity:</label></td>
                                        <td><strong>0</strong> (default)</td>
                                    </tr>
                                    <tr>
                                        <td><label for="material-reorder">Material Reorder Level:</label></td>
                                        <td><input type="number" name="material-reorder" min="0" required></td>
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

    </body>

</html>
