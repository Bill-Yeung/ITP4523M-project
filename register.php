<!DOCTYPE html>
<html lang="en">

    <head>
        <base>
        <?php require_once "includes/head-setting.php"; ?>
        <link id="login-css" rel="stylesheet" href="styles/style-login.css">
        <title>Smile & Sunshine | Register</title>
    </head>

    <?php

    $username = $email = "";
    $error = "";

    if ($_SERVER["REQUEST_METHOD"] == "POST") {

        $companyname = sanitize($_POST["companyname"]);
        $username = sanitize($_POST["username"]);
        $email = sanitize($_POST["email"]);
        $password = $_POST["password"];
        $confirm_password = $_POST["confirm_password"];
        
        // Validate inputs
        if (empty($username) || empty($email) || empty($password)) {
            $error = 'Please fill all fields!';
        } else if ($password != $confirm_password) {
            $error = 'Passwords do not match!';
        } else if (strlen($password) < 6) {
            $error = 'Password must be at least 6 characters!';
        } else {

            // Check if username or email already exists
            $stmt = $conn->prepare("SELECT cid FROM customer WHERE cname = ? OR cemail = ?");
            $stmt->bind_param("ss", $username, $email);
            $stmt->execute();
            $stmt->store_result();
            
            if ($stmt->num_rows > 0) {
                $error = 'Username or email already exists';
            } else {

                // Hash password
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                
                // Insert new user
                $stmt = $conn->prepare("INSERT INTO customer (cname, cemail, cpassword, company) VALUES (?, ?, ?, ?)");
                $stmt->bind_param("ssss", $username, $email, $hashed_password, $companyname);
                
                if ($stmt->execute()) {
                    $_SESSION['success_msg'] = 'Registration successful! Please login.';
                    header("Location: login.php");
                    exit();
                } else {
                    $error = 'Registration failed. Please try again.';
                }

            }

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
                            <h1>Create an Account</h1>
                            <p>Join Smile & Sunshine today</p>
                        </div>

                        <?php display_error($error); ?>
                        
                        <form id="login-form" method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>">
                            
                            <div class="input-group">
                                <label for="companyname">Company Name</label>
                                <input type="text" id="companyname" name="companyname" placeholder="Enter your company name" required>
                            </div>

                            <div class="input-group">
                                <label for="username">Name</label>
                                <input type="text" id="username" name="username" placeholder="Enter your name" required>
                            </div>
                            
                            <div class="input-group">
                                <label for="email">Email Address</label>
                                <input type="email" id="email" name="email" placeholder="Enter your email address" required>
                            </div>
                            
                            <div class="input-group">
                                <label for="password">Password</label>
                                <input type="password" id="password" name="password" placeholder="Enter your password" required>
                            </div>
                            
                            <div class="input-group">
                                <label for="confirm_password">Confirm Password</label>
                                <input type="password" id="confirm_password" name="confirm_password" placeholder="Confirm your password" required>
                            </div>
                            
                            <div id="button-row">
                                <button type="submit" id="login-button" onclick="register(event)">Create Account</button>
                            </div>

                            <div id="register-row">
                                <p>Already have an account? <a href="login.php">Login Now</a></p>
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