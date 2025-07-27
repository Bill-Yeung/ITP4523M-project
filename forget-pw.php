<?php
require_once __DIR__ . "/config/database.php";
require_once __DIR__ . "/includes/functions.php";
require_once __DIR__ . "/vendor/autoload.php";
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

$message = "";
$messageType = "";
$step = "email";

if (isset($_GET["step"]) && $_GET["step"] == "otp") {
    $step = "otp";
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    if (isset($_POST["send_otp"])) {

        $email = trim($_POST["email"]);
        
        if (empty($email)) {
            $message = "Email is required";
            $messageType = "error";
        } else {

            $userFound = false;
            $userId = null;
            $userType = null;
            $userName = null;
            
            $stmt = $conn->prepare("SELECT cid, cname FROM customer WHERE cemail = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($row = $result->fetch_assoc()) {
                $userFound = true;
                $userId = $row["cid"];
                $userType = "customer";
                $userName = $row["cname"];
            }
            $stmt->close();
            
            if (!$userFound) {
                $stmt = $conn->prepare("SELECT sid, sname FROM staff WHERE semail = ?");
                $stmt->bind_param("s", $email);
                $stmt->execute();
                $result = $stmt->get_result();
                if ($row = $result->fetch_assoc()) {
                    $userFound = true;
                    $userId = $row["sid"];
                    $userType = "staff";
                    $userName = $row["sname"];
                }
                $stmt->close();
            }
            
            if ($userFound) {

                // Disable old OTPs
                if ($userType == "customer") {
                    $stmt = $conn->prepare("UPDATE cpasswordreset SET is_active = 0 WHERE cid = ? AND is_active = 1");
                } else {
                    $stmt = $conn->prepare("UPDATE spasswordreset SET is_active = 0 WHERE sid = ? AND is_active = 1");
                }
                $stmt->bind_param("i", $userId);
                $stmt->execute();
                $stmt->close();
                

                $otp = sprintf("%06d", mt_rand(100000, 999999));
                $expiry = date("Y-m-d H:i:s", strtotime("+15 minutes"));
                
                // Insert new OTP
                if ($userType == "customer") {
                    $stmt = $conn->prepare("INSERT INTO cpasswordreset (cid, token, expiry) VALUES (?, ?, ?)");
                } else {
                    $stmt = $conn->prepare("INSERT INTO spasswordreset (sid, token, expiry) VALUES (?, ?, ?)");
                }
                $stmt->bind_param("iss", $userId, $otp, $expiry);
                
                if ($stmt->execute()) {
                    $stmt->close();

                    $emailSent = false;
                    $emailError = "";
                    set_time_limit(30);
                    
                    try {
                        $emailSent = sendOTPEmailSafe($email, $userName, $otp);
                    } catch (Exception $e) {
                        $emailError = $e->getMessage();
                        error_log("Email sending failed: " . $emailError);
                    }
                    
                    $_SESSION["reset_email"] = $email;
                    $_SESSION["reset_user_type"] = $userType;
                    
                    if ($emailSent) {
                        header("Location: forget-pw.php?step=otp&success=1&message=" . urlencode("OTP has been sent to your email address."));
                    } else {
                        header("Location: forget-pw.php?step=otp&success=1&otp=" . $otp . "&message=" . urlencode("Email sending failed. Please contact administrator."));
                    }
                    exit;
                }
            } else {
                $message = "Email address not found in our system.";
                $messageType = "error";
            }
        }
    }

    if (isset($_POST["verify_otp"])) {
        $otp = trim($_POST["otp"]);
        
        if (empty($otp)) {
            $message = "OTP is required";
            $messageType = "error";
        } else {

            if (!isset($_SESSION["reset_user_type"])) {
                $message = "Invalid session. Please start over.";
                $messageType = "error";
            } else {
                $userType = $_SESSION["reset_user_type"];

                if ($userType == "customer") {
                    $stmt = $conn->prepare("SELECT cpr.rid, cpr.cid as user_id, cpr.attempt, cpr.expiry, c.cname as user_name 
                                                   FROM cpasswordreset cpr 
                                                   JOIN customer c ON cpr.cid = c.cid 
                                                   WHERE cpr.token = ? AND cpr.is_active = 1");
                } else {
                    $stmt = $conn->prepare("SELECT spr.rid, spr.sid as user_id, spr.attempt, spr.expiry, s.sname as user_name 
                                                   FROM spasswordreset spr 
                                                   JOIN staff s ON spr.sid = s.sid 
                                                   WHERE spr.token = ? AND spr.is_active = 1");
                }
                
                $stmt->bind_param("s", $otp);
                $stmt->execute();
                $result = $stmt->get_result();
                
                if ($row = $result->fetch_assoc()) {
                    if (strtotime($row['expiry']) < time()) {
                        $message = "OTP has expired. Please request a new one.";
                        $messageType = "error";
                    } elseif ($row['attempt'] >= 3) {
                        $message = "Too many attempts. Please request a new OTP.";
                        $messageType = "error";
                    } else {

                        if ($userType == "customer") {
                            $updateStmt = $conn->prepare("UPDATE cpasswordreset SET used_at = NOW() WHERE rid = ?");
                        } else {
                            $updateStmt = $conn->prepare("UPDATE spasswordreset SET used_at = NOW() WHERE rid = ?");
                        }
                        $updateStmt->bind_param("i", $row['rid']);
                        $updateStmt->execute();
                        $updateStmt->close();
                        
                        $message = "SUCCESS! OTP verified for " . $row['user_name'] . ". Redirecting to password reset...";
                        $messageType = "success";
                        
                        $_SESSION["reset_token"] = $otp;
                        $_SESSION["reset_user_id"] = $row['user_id'];
                        $_SESSION["reset_user_type"] = $userType;
                        
                        echo "<script>setTimeout(function(){ window.location.href='reset-password.php'; }, 2000);</script>";
                    }
                } else {
                    $message = "Invalid OTP. Please try again.";
                    $messageType = "error";
                }
                $stmt->close();
            }
        }
    }

}

if (isset($_GET["success"]) && $_GET["success"] == 1 && $step == "otp") {
    if (isset($_GET["message"])) {
        $message = $_GET["message"];
        $messageType = "success";
    }
}

function sendOTPEmailSafe($email, $userName, $otp) {
    try {

        if (!file_exists(__DIR__ . "/config/email.php")) {
            throw new Exception("Email configuration file not found");
        }
        
        $config = require __DIR__ . "/config/email.php";
        
        $mail = new PHPMailer(true);

        $mail->Timeout = 5;
        $mail->SMTPKeepAlive = false;
        $mail->SMTPOptions = array(
            'ssl' => array(
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            )
        );
        
        $mail->isSMTP();
        $mail->Host = $config["smtp_host"];
        $mail->SMTPAuth = true;
        $mail->Username = $config["smtp_username"];
        $mail->Password = $config["smtp_password"];
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        $mail->setFrom($config["from_email"], $config["from_name"]);
        $mail->addAddress($email, $userName);

        $mail->isHTML(true);
        $mail->Subject = "Password Reset OTP";
        $mail->Body = "Your OTP is: <strong>$otp</strong><br>This expires in 15 minutes.";
        
        return $mail->send();
        
    } catch (Exception $e) {
        error_log("PHPMailer Error: " . $e->getMessage());
        return false;
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
        <title>Smile & Sunshine | Forget Password</title>
    </head>

    <body>
        <div id="main-page">
            
            <?php require_once __DIR__ . "/includes/header.php"; ?>
            
            <div id="main-section">
                <div id="login-container">
                    <div id="login-box">
                        
                        <?php if ($step == "email"): ?>
                            <div id="login-header">
                                <h1>Forgot Your Password?</h1>
                                <p>Enter your email address to receive an OTP</p>
                            </div>
                            
                            <?php if (!empty($message)): ?>
                                <div class="alert alert-<?= $messageType ?>">
                                    <?= $message ?>
                                </div>
                            <?php endif; ?>
                            
                            <form method="POST" id="login-form">
                                <div class="input-group">
                                    <label for="email">Email Address</label>
                                    <input type="email" id="email" name="email" placeholder="Enter your email address" required>
                                </div>
                                
                                <div id="button-row">
                                    <button type="submit" name="send_otp" id="login-button">Send OTP</button>
                                </div>
                            </form>
                            
                            <div id="login-footer">
                                <p><a href="login.php">Back to Login</a></p>
                            </div>
                            
                        <?php else: ?>
                            <div id="login-header">
                                <h1>Verify OTP</h1>
                                <p>Enter the 6-digit code sent to your email</p>
                            </div>
                            
                            <?php if (!empty($message)): ?>
                                <div class="alert alert-<?= $messageType ?>">
                                    <?= $message ?>
                                </div>
                            <?php endif; ?>

                            <form method="POST" action="forget-pw.php?step=otp">
                                <div class="input-group">
                                    <label for="otp">OTP Code</label>
                                    <input type="text" id="otp" name="otp" placeholder="Enter 6-digit OTP" maxlength="6" required>
                                </div>
                                
                                <div id="button-row">
                                    <button type="submit" name="verify_otp" id="login-button">Verify OTP</button>
                                </div>
                            </form>
                            
                            <div id="login-footer">
                                <p><a href="forget-pw.php">Request New OTP</a> | <a href="login.php">Back to Login</a></p>
                            </div>
                        <?php endif; ?>
                        
                    </div>
                </div>
            </div>
            
            <?php require_once __DIR__ . "/includes/footer.php"; ?>
            <?php require_once __DIR__ . "/includes/tools.php"; ?>
            
        </div>
        
        <script src="script/script-general.js"></script>

    </body>

</html>