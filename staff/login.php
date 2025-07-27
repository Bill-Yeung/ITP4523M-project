<!DOCTYPE html>
<html lang="en">

    <head>
        <base href="../">
        <?php require_once "../includes/head-setting.php"; ?>
        <link id="login-css" rel="stylesheet" href="styles/style-login.css">
        <title>Smile & Sunshine | LOGIN</title>
    </head>

    <?php

    $username = "";
    $error = "";

    // Redirect if already logged in
    if (isLoggedIn()) {
        header("Location: ../index.php");
        exit();
    }

    // Check for success message from registration
    if (isset($_SESSION['success_msg'])) {
        $success_msg = $_SESSION['success_msg'];
        unset($_SESSION['success_msg']);
    }

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {

        $usertype = $_POST['userType'];
        $username = sanitize($_POST['username']);
        $password = $_POST['password'];

        if (empty($usertype)) {
            $error = 'Please select the user type!';
            return;
        }

        if (empty($username)) {
            $error = 'Please enter your username!';
            return;
        }

        if (empty($password)) {
            $error = 'Please enter your password!';
            return;
        }
        
        // Check user credentials
        if ($usertype == 'customer') {
            $stmt = $conn->prepare("SELECT * FROM customer WHERE cname = ?");
        } else if ($usertype == 'staff') {
            $stmt = $conn->prepare("SELECT * FROM staff WHERE sname = ?");
        }
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows == 1) {

            $user = $result->fetch_assoc();
            if ($usertype == 'customer') {
                $dbpassword = $user['cpassword'];
            } else if ($usertype == 'staff') {
                $dbpassword = $user['spassword'];
            }

            if ($usertype == "customer" && $user["cavail"] == 0) {

                $error = "The account has been disabled. Please contact our company.";

            } else if ($usertype == "staff" && $user["savail"] == 0) {

                $error = "The account has been disabled. Please contact the IT administrator.";

            } else {

                if (password_verify($password, $dbpassword)) {
                
                // Set session variables
                if ($usertype == "customer") {

                    $_SESSION["userinfo"]["user_type"] = "customer";
                    $_SESSION["userinfo"]["user_id"] = $user["cid"];
                    $_SESSION["userinfo"]["username"] = $user["cname"];
                    $_SESSION["userinfo"]["password"] = $user["cpassword"];
                    $_SESSION["userinfo"]["useremail"] = $user["cemail"];
                    $_SESSION["userinfo"]["telephone"] = $user["ctel"];
                    $_SESSION["userinfo"]["address"] = $user["caddr"];
                    $_SESSION["userinfo"]["company"] = $user["company"];
                    $_SESSION["userinfo"]["image"] = $user["cimage"];

                    if ($user["lastSession"] != null) {
                        $_SESSION["shopping_cart"] = json_decode($user["lastSession"], true);
                    }

                } else if ($usertype = 'staff') {

                    $_SESSION["userinfo"]['user_type'] = "staff";
                    $_SESSION["userinfo"]['user_id'] = $user['sid'];
                    $_SESSION["userinfo"]['username'] = $user['sname'];
                    $_SESSION["userinfo"]['password'] = $user['spassword'];
                    $_SESSION["userinfo"]["useremail"] = $user["semail"];
                    $_SESSION["userinfo"]["telephone"] = $user["stel"];
                    $_SESSION["userinfo"]["userrole"] = $user["srole"];
                    $_SESSION["userinfo"]["image"] = $user["simage"];
                    
                }

                // Redirect to home page
                header("Location: ../index.php");
                exit();

                } else {
                    $error = "Incorrect password";
                }

            }
            
        } else {
            $error = "Username not found";
        }

    }

    ?>

    <body>

        <div id="main-page">

            <?php require_once "includes/header.php"; ?>
            
            <div id="main-section">

                <div id="login-container">

                    <div id="login-box">

                        <div id="login-header">
                            <h1>Welcome Back</h1>
                            <p>Please login to your account</p>
                        </div>

                        <?php 
                            if (isset($success_msg)) {
                                echo '<div class="alert alert-success">' . $success_msg . '</div>';
                            }
                            display_error($error); 
                        ?>
                        
                        <form id="login-form" method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>">

                            <div id="user-type-selection">

                                <label>Please select your account type:</label>

                                <div id="radio-container">

                                    <label class="radio-label">
                                        <input type="radio" name="userType" value="customer" required checked>
                                        <span class="radio-button"></span>
                                        <span class="radio-word">Customer</span>
                                    </label>
                                    <label class="radio-label">
                                        <input type="radio" name="userType" value="staff" required>
                                        <span class="radio-button"></span>
                                        <span class="radio-word">Staff</span>
                                    </label>
                                </div>

                            </div>
                            
                            <div class="input-group">
                                <label for="username">Username</label>
                                <input type="text" id="username" name="username" placeholder="Enter your username" required>
                            </div>
                            
                            <div class="input-group">
                                <label for="password">Password</label>
                                <input type="password" id="password" name="password" placeholder="Enter your password" required>
                            </div>
                            
                            <div id="action-row">
                                <a href="forget-pw.html">Forgot Password?</a>
                            </div>
                            
                            <div id="button-row">
                                <button type="submit" id="login-button">Login</button>
                            </div>

                            <div id="register-row">
                                <p>Don't have an account? <a href="register.php">Register Now</a></p>
                            </div>

                        </form>

                    </div>

                </div>

            </div>

            <?php require_once "includes/footer.php"; ?>
            <?php require_once "includes/tools.php"; ?>

        </div>

        <script src="script/script-general.js"></script>

    </body>

</html>