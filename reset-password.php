<?php
require_once __DIR__ . "/config/database.php";
require_once __DIR__ . "/includes/functions.php";

if (!isset($_SESSION["reset_token"]) || !isset($_SESSION["reset_user_id"]) || !isset($_SESSION["reset_user_type"])) {
    header("Location: forget-pw.php?error=" . urlencode("Invalid access. Please start the password reset process again."));
    exit;
}

$message = "";
$messageType = "";

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["reset_password"])) {

    $newPassword = $_POST["new_password"];
    $confirmPassword = $_POST["confirm_password"];
    
    if (empty($newPassword) || empty($confirmPassword)) {
        $message = "Both password fields are required.";
        $messageType = "error";

    } elseif ($newPassword !== $confirmPassword) {
        $message = "Passwords do not match.";
        $messageType = "error";

    } elseif (strlen($newPassword) < 6) {
        $message = "Password must be at least 6 characters long.";
        $messageType = "error";

    } else {
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        $userId = $_SESSION["reset_user_id"];
        $userType = $_SESSION["reset_user_type"];
        
        if ($userType == "customer") {
            $stmt = $conn->prepare("UPDATE customer SET cpassword = ? WHERE cid = ?");
        } else {
            $stmt = $conn->prepare("UPDATE staff SET spassword = ? WHERE sid = ?");
        }
        
        $stmt->bind_param("si", $hashedPassword, $userId);
        
        if ($stmt->execute()) {
            $stmt->close();
            
            // Disable all reset tokens for user
            if ($userType == "customer") {
                $disableStmt = $conn->prepare("UPDATE cpasswordreset SET is_active = 0 WHERE cid = ?");
            } else {
                $disableStmt = $conn->prepare("UPDATE spasswordreset SET is_active = 0 WHERE sid = ?");
            }
            $disableStmt->bind_param("i", $userId);
            $disableStmt->execute();
            $disableStmt->close();
            
            // Clear session variables
            unset($_SESSION["reset_token"]);
            unset($_SESSION["reset_user_id"]);
            unset($_SESSION["reset_user_type"]);
            unset($_SESSION["reset_email"]);
            
            $message = "Password has been reset successfully! You can now login with your new password.";
            $messageType = "success";
            
            echo "<script>setTimeout(function(){ window.location.href='login.php?success=" . urlencode("Password reset successfully! Please login with your new password.") . "'; }, 2000);</script>";
        } else {
            $message = "Failed to reset password. Please try again.";
            $messageType = "error";
            $stmt->close();
        }
    }
}

mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <base href="">
    <?php require_once __DIR__ . "/includes/head-setting.php"; ?>
    <link rel="stylesheet" href="styles/style-login.css">
    <title>Smile & Sunshine | Reset Password</title>
</head>

<body>
    <div id="main-page">
        
        <?php require_once __DIR__ . "/includes/header.php"; ?>
        
        <div id="main-section">
            <div id="login-container">
                <div id="login-box">
                    
                    <div id="login-header">
                        <h1>Reset Password</h1>
                        <p>Enter your new password</p>
                    </div>
                    
                    <?php if (!empty($message)): ?>
                        <div class="alert alert-<?= $messageType ?>">
                            <?= $message ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($messageType !== "success"): ?>
                        <form method="POST" id="login-form">
                            <div class="input-group">
                                <label for="new_password">New Password</label>
                                <input type="password" id="new_password" name="new_password" placeholder="Enter new password" minlength="6" required>
                            </div>
                            
                            <div class="input-group">
                                <label for="confirm_password">Confirm Password</label>
                                <input type="password" id="confirm_password" name="confirm_password" placeholder="Confirm new password" minlength="6" required>
                            </div>
                            
                            <div id="button-row">
                                <button type="submit" name="reset_password" id="login-button">Reset Password</button>
                            </div>
                            
                            <div style="font-size: 12px; color: gray; margin-top: 10px;">
                                Password must be at least 6 characters long
                            </div>
                        </form>
                    <?php endif; ?>
                    
                    <div id="login-footer">
                        <p><a href="login.php">Back to Login</a></p>
                    </div>
                    
                </div>
            </div>
        </div>
        
        <?php require_once __DIR__ . "/includes/footer.php"; ?>
        <?php require_once __DIR__ . "/includes/tools.php"; ?>
        
    </div>
    
    <script src="script/script-general.js"></script>
    
    <script>
        document.getElementById("confirm_password").addEventListener("input", function() {
            var password = document.getElementById("new_password").value;
            var confirmPassword = this.value;
            
            if (password != confirmPassword) {
                this.setCustomValidity("Passwords do not match");
            } else {
                this.setCustomValidity("");
            }
        });
    </script>
    
</body>

</html>