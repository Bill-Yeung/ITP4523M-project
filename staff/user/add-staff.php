<?php

require_once __DIR__ . "/../../config/database.php";
require_once __DIR__ . "/../../includes/functions.php";

requireLogin();

$message = "";

$nextIdQuery = $conn->query("SELECT MAX(sid) as max_sid FROM staff");
$nextId = 1;
if ($nextIdQuery && $nextIdQuery->num_rows > 0) {
    $result = $nextIdQuery->fetch_assoc();
    $nextId = $result["max_sid"] ? $result["max_sid"] + 1 : 1;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $sname = trim($_POST["staff-name"]);
    $semail = trim($_POST["staff-email"]);
    $spassword = $_POST["staff-password"];
    $srole = trim($_POST["staff-role"]);
    $stel = !empty($_POST["staff-tel"]) ? intval($_POST["staff-tel"]) : null;

    if (empty($sname) || empty($semail) || empty($spassword) || empty($srole)) {
        $message = "Name, email, password, and role are required fields.";
    } else {
            
        // Hash the password
        $hashedPassword = password_hash($spassword, PASSWORD_DEFAULT);

        $stmt = $conn->prepare("INSERT INTO staff (sname, semail, spassword, srole, stel, savail) VALUES (?, ?, ?, ?, ?, 1)");
        $stmt->bind_param("ssssi", $sname, $semail, $hashedPassword, $srole, $stel);

        if ($stmt->execute()) {
            $new_sid = $stmt->insert_id;
            $stmt->close();

            if (isset($_FILES["staff-image"]) && $_FILES["staff-image"]["error"] == UPLOAD_ERR_OK) {
                $imageFileType = strtolower(pathinfo($_FILES["staff-image"]["name"], PATHINFO_EXTENSION));
                $filename = $new_sid . "." . $imageFileType;
                $relativePath = $filename;
                $absolutePath = __DIR__ . "/../../staff/photos/" . $filename;
                $allowed = ['jpg', 'jpeg', 'png', 'gif'];

                if (in_array($imageFileType, $allowed)) {
                    if (move_uploaded_file($_FILES["staff-image"]["tmp_name"], $absolutePath)) {
                        $stmtUpdate = $conn->prepare("UPDATE staff SET simage = ? WHERE sid = ?");
                        $stmtUpdate->bind_param("si", $relativePath, $new_sid);
                        $stmtUpdate->execute();
                        $stmtUpdate->close();
                    } else {
                        $message = "Failed to upload image, but staff was added successfully.";
                    }
                } else {
                    $message = "Invalid image format. Only JPG, JPEG, PNG, GIF are allowed. Staff was added successfully.";
                }
            }

            $message = "Staff added successfully.";
            echo "<script type='text/javascript'>alert('" . $message . "');
            window.location.href = 'manage-staff.php';
            </script>";
            exit();

        } else {
            $message = "Database error while inserting staff.";
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
        <title>Smile & Sunshine | Add Staff</title>
    </head>

    <body>

        <div id="main-page">

            <?php require_once __DIR__ . "/../../includes/header.php"; ?>

            <div id="main-section">

                <div id="items-container">

                    <div id="items-header">
                        <h2>Add Staff</h2>
                        <p>Add a new staff member to the team</p>
                    </div>

                    <div id="items-controls">
                        <div id="control-actions">
                            <button type="button" id="back-btn" onclick="window.location.href='staff/user/manage-staff.php'">Back to Manage Staff</button>
                        </div>
                    </div>

                    <div id="items-section">

                        <form method="POST" enctype="multipart/form-data">

                            <div id="add-item-form">

                                <table id="add-item">

                                    <tr>
                                        <td><label for="staff-id">Staff ID:</label></td>
                                        <td><strong><?php echo $nextId; ?></strong> (auto-generated)</td>
                                    </tr>
                                    <tr>
                                        <td><label for="staff-name">Staff Name:</label></td>
                                        <td><input type="text" name="staff-name" required></td>
                                    </tr>
                                    <tr>
                                        <td><label for="staff-email">Email Address:</label></td>
                                        <td><input type="email" name="staff-email" required></td>
                                    </tr>
                                    <tr>
                                        <td><label for="staff-password">Password:</label></td>
                                        <td><input type="password" name="staff-password" required></td>
                                    </tr>
                                    <tr>
                                        <td><label for="staff-role">Role:</label></td>
                                        <td><input type="text" name="staff-role" placeholder="e.g., admin, manager, staff" required></td>
                                    </tr>
                                    <tr>
                                        <td><label for="staff-tel">Phone Number:</label></td>
                                        <td><input type="tel" name="staff-tel"></td>
                                    </tr>
                                    <tr>
                                        <td><label for="staff-image">Upload Image:</label></td>
                                        <td><input type="file" name="staff-image" accept=".jpg,.jpeg,.png,.gif"> (Optional)</td>
                                    </tr>
                                    <tr>
                                        <td><label for="staff-availability">Availability:</label></td>
                                        <td><strong>Active</strong> (default)</td>
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