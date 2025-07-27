<!DOCTYPE html>
<html lang="en">

    <head>
        <base href="./">
        <?php require_once "includes/head-setting.php"; ?>
        <link rel="stylesheet" href="styles/style-update-profile.css">
        <title>Smile & Sunshine | Update Profile</title>
    </head>

    <?php

    requireLogin();

    if (isset($_POST["photosubmit"])) {

        $uploadDir = __DIR__ . "/" . $_SESSION["userinfo"]["user_type"] . "/photos";
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        $imageFileType = strtolower(pathinfo($_FILES["fileToUpload"]["name"], PATHINFO_EXTENSION));
        
        $fileName = $_SESSION["userinfo"]["user_id"] . "_" . date('YmdHis') . "." . $imageFileType;
        $target_file = $uploadDir . "/" . $fileName;
        $uploadOk = 1;

        $check = getimagesize($_FILES["fileToUpload"]["tmp_name"]);
        if ($check != false) {
            $uploadOk = 1;
        } else {
            $message = "File is not an image.";
            $uploadOk = 0;
        }

        if (file_exists($target_file)) {
            unlink($target_file);
        }

        if ($_FILES["fileToUpload"]["size"] > 5000000) {
            $message = "Sorry, your file is too large.";
            $uploadOk = 0;
        }

        if ($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg") {
            $message = "Sorry, only JPG, PNG & JPEG files are allowed.";
            $uploadOk = 0;
        }

        if ($uploadOk == 1) {
            if (move_uploaded_file($_FILES["fileToUpload"]["tmp_name"], $target_file)) {

                if (!empty($_SESSION["userinfo"]["image"]) && $_SESSION["userinfo"]["image"] != $fileName) {
                    $oldImagePath = $uploadDir . "/" . $_SESSION["userinfo"]["image"];
                    if (file_exists($oldImagePath)) {
                        unlink($oldImagePath);
                    }
                }

                if ($_SESSION["userinfo"]["user_type"] == "customer") {
                    $sql = "UPDATE customer SET cimage = '" . $fileName . "' WHERE cid = " . $_SESSION["userinfo"]["user_id"];
                } else {
                    $sql = "UPDATE staff SET simage = '" . $fileName . "' WHERE sid = " . $_SESSION["userinfo"]["user_id"];
                }
                
                $rc = mysqli_query($conn, $sql) or die(mysqli_error($conn));

                if ($rc) {
                    $message = "Your profile picture " . basename($_FILES["fileToUpload"]["name"]) . " has been updated.";
                    $_SESSION["userinfo"]["image"] = $fileName ;
                } else {
                    $message = "Sorry, there was an error uploading your file.";
                }
                
            } else {
                $message = "Sorry, there was an error uploading your file.";
            }
        }

    } else if (isset($_POST["passwordsubmit"])) {

        if (password_verify($_POST["current-password"], $_SESSION["userinfo"]["password"])) {
            
            if ($_SESSION["userinfo"]["user_type"] == "customer") {
                $sql = "UPDATE customer SET cpassword = '" . password_hash($_POST["new-password"], PASSWORD_DEFAULT) . "' WHERE cid = " . $_SESSION["userinfo"]["user_id"];
            } else {
                $sql = "UPDATE staff SET spassword = '" . password_hash($_POST["new-password"], PASSWORD_DEFAULT) . "' WHERE sid = " . $_SESSION["userinfo"]["user_id"];
            }
            
            $rc = mysqli_query($conn, $sql) or die(mysqli_error($conn));

            if ($rc) {
                $message = "Your password has been updated!";
                $_SESSION["userinfo"]["password"] = password_hash($_POST["new-password"], PASSWORD_DEFAULT);
            } else {
                $message = "Sorry, there was an error updating your password. Please try again!";
            }

        } else {
            $message = "Current password is not entered correctly!";
        }

    } else if (isset($_POST["emailsubmit"])) {

        if ($_SESSION["userinfo"]["user_type"] == "customer") {
            $sql = "UPDATE customer SET cemail = '" . $_POST["email-input"] . "' WHERE cid = " . $_SESSION["userinfo"]["user_id"];
        } else {
            $sql = "UPDATE staff SET semail = '" . $_POST["email-input"] . "' WHERE sid = " . $_SESSION["userinfo"]["user_id"];
        }

        $rc = mysqli_query($conn, $sql) or die(mysqli_error($conn));

        if ($rc) {
            $message = "Your email has been updated!";
             $_SESSION["userinfo"]["useremail"] = $_POST["email-input"];
        } else {
            $message = "Sorry, there was an error updating your email. Please try again!";
        }

    } else if (isset($_POST["telephonesubmit"])) {

        if ($_SESSION["userinfo"]["user_type"] == "customer") {
            $sql = "UPDATE customer SET ctel = " . $_POST["telephone-input"] . " WHERE cid = " . $_SESSION["userinfo"]["user_id"];
        } else {
            $sql = "UPDATE staff SET stel = " . $_POST["telephone-input"] . " WHERE sid = " . $_SESSION["userinfo"]["user_id"];
        }

        $rc = mysqli_query($conn, $sql) or die(mysqli_error($conn));

        if ($rc) {
            $message = "Your telephone has been updated!";
             $_SESSION["userinfo"]["telephone"] = $_POST["telephone-input"];
        } else {
            $message = "Sorry, there was an error updating your telephone. Please try again!";
        }

    } else if (isset($_POST["addresssubmit"])) {

        if ($_SESSION["userinfo"]["user_type"] == "customer") {
            $sql = "UPDATE customer SET caddr = '" . $_POST["address-input"] . "' WHERE cid = " . $_SESSION["userinfo"]["user_id"];
        }

        $rc = mysqli_query($conn, $sql) or die(mysqli_error($conn));

        if ($rc) {
            $message = "Your address has been updated!";
             $_SESSION["userinfo"]["address"] = $_POST["address-input"];
        } else {
            $message = "Sorry, there was an error updating your address. Please try again!";
        }

    }

    ?>

    <body>

        <div id="main-page">

            <?php require_once "includes/header.php"; ?>
            
            <div id="main-section">

                <div id="profile-container">

                    <div id="profile-header">
                        <h2>Manage Profile</h2>
                        <p>View and update account information</p>
                    </div>

                    <div id="profile-section">

                        <div id="profile-picture-section">

                            <div id="picture-container">
                                <img id="profile-picture" src="<?php 
                                    echo (isset($_SESSION["userinfo"]["image"])) 
                                    ? $_SESSION["userinfo"]["user_type"] . "/photos/" . $_SESSION["userinfo"]["image"] 
                                    : "img/chatbox/user.svg"; 
                                    ?>">
                                <div id="picture-controls">
                                    <form id="photo-upload" method="POST" enctype="multipart/form-data" action="update-profile.php">
                                        <input type="file" id="photo-input" name="fileToUpload" accept="image/*" onchange="handlePhotoUpload(this)">
                                        <button type="button" id="upload-btn" class="upload-btn" onclick="document.getElementById('photo-input').click()">Upload Photo</button>
                                        <button type="submit" id="save-photo-btn" class="save-photo-btn" name="photosubmit">Save Photo</button>
                                        <button type="button" id="cancel-photo-btn" class="cancel-photo-btn" onclick="cancelPhotoUpload()">Cancel</button>
                                    </form>
                                </div>
                            </div>

                            <div id="basic-info">
                                <h3><?php echo $_SESSION["userinfo"]["username"]; ?></h3>
                                <p><?php echo ucfirst($_SESSION["userinfo"]["user_type"]); ?> ID: <?php echo $_SESSION["userinfo"]["user_id"]; ?></p>
                                <?php if (isCustomer()) : ?>
                                    <p>Company: <?php echo $_SESSION["userinfo"]["company"]; ?></p>
                                <?php endif; ?>
                                <?php if (isStaff()) : ?>
                                    <p>Role: <?php echo $_SESSION["userinfo"]["userrole"]; ?></p>
                                <?php endif; ?>
                            </div>

                        </div>

                        <div id="profile-info-section">
                            
                            <form method="POST" action="update-profile.php">

                                <div id="info-card">

                                    <h3>Account Information</h3>

                                    <div class="info-row">
                                        <div class="info-label">Password:</div>
                                        <div class="info-value">
                                            <span id="password-display">**********</span>
                                            <button type="button" class="edit-btn" onclick="editPassword()">Change</button>
                                        </div>
                                    </div>

                                    <div class="info-row">
                                        <div class="info-label">Email:</div>
                                        <div class="info-value">
                                            <span id="email-display"><?php echo $_SESSION["userinfo"]["useremail"] ?? 'Not provided'; ?></span>
                                            <?php if (isCustomer()) : ?>
                                                <input type="email" id="email-input" name="email-input" value="<?php echo $_SESSION["userinfo"]["useremail"] ?? 'Not provided'; ?>">
                                                <button type="button" id="email-edit-btn" class="edit-btn" onclick="editEmail()">Edit</button>
                                                <button type="submit" id="email-save-btn" class="save-btn" name="emailsubmit" onclick="return saveEmail()">Save</button>
                                                <button type="button" id="email-cancel-btn" class="cancel-btn" onclick="cancelEmail()">Cancel</button>
                                            <?php endif; ?>
                                        </div>
                                    </div>

                                    <div class="info-row">
                                        <div class="info-label">Contact Number:</div>
                                        <div class="info-value">
                                            <span id="telephone-display"><?php echo $_SESSION["userinfo"]["telephone"] ?? 'Not provided'; ?></span>
                                            <?php if (isCustomer()) : ?>
                                                <input type="tel" id="telephone-input" name="telephone-input" value="<?php echo $_SESSION["userinfo"]["telephone"] ?? 'Not provided'; ?>">
                                                <button type="button" id="telephone-edit-btn" class="edit-btn" onclick="editTelephone()">Edit</button>
                                                <button type="submit" id="telephone-save-btn" class="save-btn" name="telephonesubmit" onclick="return saveTelephone()">Save</button>
                                                <button type="button" id="telephone-cancel-btn" class="cancel-btn" onclick="cancelTelephone()">Cancel</button>
                                            <?php endif; ?>
                                        </div>
                                    </div>

                                    <?php if (isCustomer()): ?>

                                        <div class="info-row">
                                            <div class="info-label">Address:</div>
                                            <div class="info-value">
                                                <span id="address-display"><?php echo $_SESSION["userinfo"]["address"] ?? 'Not provided'; ?></span>
                                                <textarea id="address-input" name="address-input"><?php echo $_SESSION["userinfo"]["address"] ?? 'Not provided'; ?></textarea>
                                                <button type="button" id="address-edit-btn" class="edit-btn" onclick="editAddress()">Edit</button>
                                                <button type="submit" id="address-save-btn" class="save-btn" name="addresssubmit" onclick="saveAddress()">Save</button>
                                                <button type="button" id="address-cancel-btn" class="cancel-btn" onclick="cancelAddress()">Cancel</button>
                                            </div>
                                        </div>
                                    
                                    <?php endif; ?>

                                </div>

                            </form>

                            <div id="password-change-form">

                                <div id="form-card">

                                    <h3>Change Password</h3>

                                    <form id="password-form" method="POST" action="update-profile.php">
                                        <table id="password-table">
                                            <tr>
                                                <td class="form-label">Current Password:</td>
                                                <td class="form-input">
                                                    <input type="password" id="current-password" name="current-password">
                                                </td>
                                            </tr>
                                            <tr>
                                                <td class="form-label">New Password:</td>
                                                <td class="form-input">
                                                    <input type="password" id="new-password" name="new-password">
                                                </td>
                                            </tr>
                                            <tr>
                                                <td class="form-label">Confirm New Password:</td>
                                                <td class="form-input">
                                                    <input type="password" id="confirm-password" name="confirm-password">
                                                </td>
                                            </tr>
                                            <tr>
                                                <td colspan="2" id="form-buttons">
                                                    <button type="submit" id="password-save-btn" name="passwordsubmit" class="save-btn" onclick="return checkPassword()">Save Password</button>
                                                    <button type="button" id="password-cancel-btn" class="cancel-btn" onclick="cancelPassword()">Cancel</button>
                                                </td>
                                            </tr>
                                        </table>
                                    </form>

                                </div>

                            </div>

                        </div>

                    </div>

                </div>

            </div>

            <?php require_once "includes/footer.php"; ?>
            <?php require_once "includes/tools.php"; ?>

        </div>

        <script src="script/script-general.js"></script>
        <script src="script/script-manage-profile.js"></script>

        <?php if (isset($message) && $message != null) {
            echo "<script type='text/javascript'>alert('" . $message . "'); </script>";
        } ?>

    </body>

</html>