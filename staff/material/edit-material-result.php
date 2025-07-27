<?php

require_once __DIR__ . "/../../config/database.php";
require_once __DIR__ . "/../../includes/functions.php";

requireLogin();

$status = $_GET["status"] ?? "unknown";
$mid = $_GET["mid"] ?? null;
$mname = $_GET["mname"] ?? null;
$message = $_GET["msg"] ?? "";

$pageTitle = "Edit Material Result";
$resultMessage = "";
$resultClass = "";

switch ($status) {
    case "success":
        $pageTitle = "Updated successfully";
        $resultMessage = "Material '" . ($mname ? $mname : $mid) . "' (ID: " . $mid . ") has been updated successfully.";
        $resultClass = "alert-success";
        break;
    case "dberror":
        $pageTitle = "Update failed";
        $resultMessage = "Failed to update material (ID: " . $mid . "). ";
        $resultMessage .= "Error: " . $message;
        $resultClass = "alert-danger";
        break;
    case "preperror":
        $pageTitle = "Update failed";
        $resultMessage = "A database error occurred. ";
        $resultMessage .= "Error: " . $message;
        $resultClass = "alert-danger";
        break;
    default:
        $pageTitle = "Unknown status";
        $resultMessage = "An unknown error has occurred.";
        $resultClass = "alert-warning";
        break;
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
        <title>Smile & Sunshine | <?php echo $pageTitle; ?></title>
    </head>

    <body>

        <div id="main-page">

            <?php require_once __DIR__ . "/../../includes/header.php"; ?>

            <div id="main-section">

                <div id="items-container">

                    <div id="items-header">
                        <h2><?php echo $pageTitle; ?></h2>
                        <p>Material update result</p>
                    </div>

                    <div id="items-section">

                        <div id="result-container">

                            <div class="alert <?php echo $resultClass; ?>">
                                <?php echo $resultMessage; ?>
                            </div>

                            <div id="result-actions">
                                <?php if ($status == "success" && $mid): ?>
                                    <button onclick="window.location.href='staff/material/edit-material.php?mid=<?php echo urlencode($mid); ?>'">Edit Again</button>
                                <?php elseif ($mid): ?>
                                    <button onclick="window.location.href='staff/material/edit-material.php?mid=<?php echo urlencode($mid); ?>'">Try Again</button>
                                <?php endif; ?>
                                <button onclick="window.location.href='staff/material/manage-materials.php'">Back to Manage Materials</button>
                            </div>

                        </div>

                    </div>

                </div>

            </div>

            <?php require_once __DIR__ . "/../../includes/footer.php"; ?>
            <?php require_once __DIR__ . "/../../includes/tools.php"; ?>

        </div>

    </body>

</html>
