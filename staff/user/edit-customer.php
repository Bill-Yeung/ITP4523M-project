<?php
require_once __DIR__ . "/../../config/database.php";
require_once __DIR__ . "/../../includes/functions.php";
requireLogin();

$cid = $_GET["id"] ?? "";
if (empty($cid)) {
    die("Missing customer ID.");
}

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $cname = trim($_POST["customer-name"]);
    $cemail = trim($_POST["customer-email"]);
    $ctel = trim($_POST["customer-tel"]);
    $caddr = trim($_POST["customer-address"]);
    $company = trim($_POST["customer-company"]);
    $cavail = isset($_POST["customer-active"]) ? 1 : 0;

    if (empty($cname) || empty($cemail)) {
        $message = "Name and email are required fields.";
    } else {
        
        $stmt = $conn->prepare("UPDATE customer SET cname = ?, cemail = ?, ctel = ?, caddr = ?, company = ?, cavail = ? WHERE cid = ?");
        $stmt->bind_param("sssssii", $cname, $cemail, $ctel, $caddr, $company, $cavail, $cid);

        if ($stmt->execute()) {
            $message = "Customer updated successfully.";
            echo "<script type='text/javascript'>alert('" . $message . "');
            window.location.href = 'manage-customer.php';
            </script>";
            exit();
        } else {
            $message = "Update failed: " . $stmt->error;
        }
        $stmt->close();
    }

}

$stmt = $conn->prepare("SELECT * FROM customer WHERE cid = ?");
$stmt->bind_param("i", $cid);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows === 0) {
    die("Customer not found.");
}
$customer = $result->fetch_assoc();
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">

    <head>
        <base href="../../">
        <?php require_once __DIR__ . "/../../includes/head-setting.php"; ?>
        <link rel="stylesheet" href="styles/style-items.css">
        <title>Smile & Sunshine | Edit Customer</title>
    </head>

    <body>

        <div id="main-page">

            <?php require_once __DIR__ . "/../../includes/header.php"; ?>

            <div id="main-section">

                <div id="items-container">

                    <div id="items-header">
                        <h2>Edit Customer</h2>
                        <p>Update customer information</p>
                    </div>

                    <div id="items-controls">
                        <div id="control-actions">
                            <button type="button" id="back-btn" onclick="window.location.href='staff/user/manage-customer.php'">Back to Manage Customers</button>
                        </div>
                    </div>

                    <div id="items-section">

                        <form method="POST">

                            <div id="add-item-form">

                                <table id="add-item">

                                    <tr>
                                        <td><label for="customer-id">Customer ID:</label></td>
                                        <td><strong><?php echo $customer["cid"]; ?></strong> (read-only)</td>
                                    </tr>
                                    <tr>
                                        <td><label for="customer-name">Customer Name:</label></td>
                                        <td><input type="text" name="customer-name" value="<?php echo $customer["cname"]; ?>" required></td>
                                    </tr>
                                    <tr>
                                        <td><label for="customer-email">Email Address:</label></td>
                                        <td><input type="email" name="customer-email" value="<?php echo $customer["cemail"]; ?>" required></td>
                                    </tr>
                                    <tr>
                                        <td><label for="customer-tel">Phone Number:</label></td>
                                        <td><input type="tel" name="customer-tel" value="<?php echo $customer["ctel"]; ?>"></td>
                                    </tr>
                                    <tr>
                                        <td><label for="customer-address">Address:</label></td>
                                        <td><textarea name="customer-address" rows="3"><?php echo $customer["caddr"]; ?></textarea></td>
                                    </tr>
                                    <tr>
                                        <td><label for="customer-company">Company:</label></td>
                                        <td><input type="text" name="customer-company" value="<?php echo $customer["company"]; ?>"></td>
                                    </tr>
                                    <tr>
                                        <td><label for="customer-active">Account Status:</label></td>
                                        <td>
                                            <label class="checkbox-label">
                                                <input type="checkbox" name="customer-active" <?php echo $customer["cavail"] ? "checked" : ""; ?>>
                                                Active Account
                                            </label>
                                        </td>
                                    </tr>

                                    <tr>
                                        <td colspan="2" id="button-row">
                                            <input id="btn-submit" type="submit" value="Update Customer">
                                            <input id="btn-reset" type="button" value="Cancel" onclick="window.location.href='staff/user/manage-customer.php'">
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