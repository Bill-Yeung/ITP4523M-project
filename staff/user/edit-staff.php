<?php

require_once __DIR__ . "/../../config/database.php";
require_once __DIR__ . "/../../includes/functions.php";

requireLogin();

$sid = $_GET["id"] ?? "";
if (empty($sid)) {
    die("Missing staff ID.");
}

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $sname = trim($_POST["staff-name"]);
    $semail = trim($_POST["staff-email"]);
    $stel = !empty($_POST["staff-tel"]) ? intval($_POST["staff-tel"]) : null;
    $srole = trim($_POST["staff-role"]);
    $savail = isset($_POST["staff-active"]) ? 1 : 0;

    if (empty($sname) || empty($semail) || empty($srole)) {
        $message = "Name, email, and role are required fields.";
    } else {

        $stmt = $conn->prepare("UPDATE staff SET sname = ?, semail = ?, stel = ?, srole = ?, savail = ? WHERE sid = ?");
        $stmt->bind_param("ssissi", $sname, $semail, $stel, $srole, $savail, $sid);

        if ($stmt->execute()) {
            $message = "Staff updated successfully.";
            echo "<script type='text/javascript'>alert('" . $message . "');
            window.location.href = 'manage-staff.php';
            </script>";
            exit();
        } else {
            $message = "Update failed: " . $stmt->error;
        }
        $stmt->close();
    }

}

$stmt = $conn->prepare("SELECT * FROM staff WHERE sid = ?");
$stmt->bind_param("i", $sid);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows === 0) {
    die("Staff member not found.");
}
$staff = $result->fetch_assoc();
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">

    <head>
        <base href="../../">
        <?php require_once __DIR__ . "/../../includes/head-setting.php"; ?>
        <link rel="stylesheet" href="styles/style-items.css">
        <title>Smile & Sunshine | Edit Staff</title>
    </head>

    <body>

        <div id="main-page">

            <?php require_once __DIR__ . "/../../includes/header.php"; ?>

            <div id="main-section">

                <div id="items-container">

                    <div id="items-header">
                        <h2>Edit Staff</h2>
                        <p>Update staff member information</p>
                    </div>

                    <div id="items-controls">
                        <div id="control-actions">
                            <button type="button" id="back-btn" onclick="window.location.href='staff/user/manage-staff.php'">Back to Manage Staff</button>
                        </div>
                    </div>

                    <div id="items-section">

                        <form method="POST">

                            <div id="add-item-form">

                                <table id="add-item">

                                    <tr>
                                        <td><label for="staff-id">Staff ID:</label></td>
                                        <td><strong><?php echo $staff["sid"]; ?></strong> (read-only)</td>
                                    </tr>
                                    <tr>
                                        <td><label for="staff-name">Staff Name:</label></td>
                                        <td><input type="text" name="staff-name" value="<?php echo $staff["sname"]; ?>" required></td>
                                    </tr>
                                    <tr>
                                        <td><label for="staff-email">Email Address:</label></td>
                                        <td><input type="email" name="staff-email" value="<?php echo $staff["semail"]; ?>" required></td>
                                    </tr>
                                    <tr>
                                        <td><label for="staff-role">Role:</label></td>
                                        <td><input type="text" name="staff-role" value="<?php echo $staff["srole"]; ?>" placeholder="e.g., admin, manager, staff" required></td>
                                    </tr>
                                    <tr>
                                        <td><label for="staff-tel">Phone Number:</label></td>
                                        <td><input type="tel" name="staff-tel" value="<?php echo $staff["stel"]; ?>" placeholder="Optional"></td>
                                    </tr>
                                    <tr>
                                        <td><label for="staff-active">Account Status:</label></td>
                                        <td>
                                            <label class="checkbox-label">
                                                <input type="checkbox" name="staff-active" <?php echo $staff['savail'] ? 'checked' : ''; ?>>
                                                Active Account
                                            </label>
                                        </td>
                                    </tr>

                                    <tr>
                                        <td colspan="2" id="button-row">
                                            <input id="btn-submit" type="submit" value="Update Staff">
                                            <input id="btn-reset" type="button" value="Cancel" onclick="window.location.href='staff/user/manage-staff.php'">
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

<?php $conn->close(); ?>