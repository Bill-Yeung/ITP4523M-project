<?php

session_start();
require_once "config/database.php";

$isLoggedInCustomer = isset($_SESSION["userinfo"]) && $_SESSION["userinfo"]["user_type"] == "customer";
$customerData = $isLoggedInCustomer ? $_SESSION["userinfo"] : null;

$uploadSuccess = false;
$uploadError = "";

if (isset($_POST["submit"])) {
    $uploadDir = __DIR__ . "/img/issues/";
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    $uploadOk = 1;

    if (empty($_FILES["fileToUpload"]["name"])) {
        $uploadError = "Screenshot is required. Please upload an image.";
        $uploadOk = 0;
    } else {
        $fileName = uniqid() . '_' . basename($_FILES["fileToUpload"]["name"]);
        $target_file = $uploadDir . $fileName;
        $fileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

        $check = getimagesize($_FILES["fileToUpload"]["tmp_name"]);
        if ($check !== false) {
            $uploadOk = 1;
        } else {
            $uploadError = "File is not an image.";
            $uploadOk = 0;
        }

        if (file_exists($target_file)) {
            $uploadError = "File already exists.";
            $uploadOk = 0;
        }

        if ($_FILES["fileToUpload"]["size"] > 5000000) {
            $uploadError = "File too large (max 5MB).";
            $uploadOk = 0;
        }

        if (!in_array($fileType, ["jpg", "png", "jpeg", "gif"])) {
            $uploadError = "Only JPG, PNG, JPEG & GIF allowed.";
            $uploadOk = 0;
        }

        if ($uploadOk == 1) {
            if (!move_uploaded_file($_FILES["fileToUpload"]["tmp_name"], $target_file)) {
                $uploadError = "Error uploading file.";
                $uploadOk = 0;
            }
        }
    }

    $problemType = $_POST["problem"] ?? "other-issue";
    $problemDetails = $_POST["problem_details"] ?? "";

    if ($isLoggedInCustomer) {
        $reporterName = $customerData["username"];
        $reporterEmail = $customerData["useremail"];
        $isCustomer = 1;
        $customerId = $customerData["user_id"];
        $customerExists = true; // logged-in customers are always valid
    } else {
        $reporterName = $_POST["reporter_name"] ?? "";
        $reporterEmail = $_POST["reporter_email"] ?? "";
        $isCustomer = isset($_POST["is_customer"]) ? 1 : 0;
        $customerId = !empty($_POST["customer_id"]) ? intval($_POST["customer_id"]) : null;
        $customerExists = true;

        if ($isCustomer && $customerId !== null) {
            $checkStmt = $conn->prepare("SELECT cid FROM customer WHERE cid = ?");
            $checkStmt->bind_param("i", $customerId);
            $checkStmt->execute();
            $checkStmt->store_result();

            if ($checkStmt->num_rows === 0) {
                $uploadError = "Invalid Customer ID. Please check and try again.";
                $customerExists = false;
                $customerId = null; // prevent invalid ID from being inserted
            }

            $checkStmt->close();
        } elseif (!$isCustomer) {
            $customerId = null; // anonymous or non-customer
        }
    }

    if ($uploadOk == 1 && $customerExists && !empty($problemDetails) && !empty($reporterName) && !empty($reporterEmail)) {
        $stmt = $conn->prepare("INSERT INTO issue (issueType, issueDetails, screenshot, rname, remail, isCustomer, cid, status) VALUES (?, ?, ?, ?, ?, ?, ?, 0)");
        $stmt->bind_param("sssssii", $problemType, $problemDetails, $fileName, $reporterName, $reporterEmail, $isCustomer, $customerId);
        if ($stmt->execute()) {
            echo "<script>alert('Report submitted successfully!'); window.location.href='report-problem.php';</script>";
            exit();
        } else {
            $uploadError = "Error in submitting the problem. Please double check your entry.";
        }
        $stmt->close();
    } elseif ($uploadOk == 1 && $customerExists) {
        $uploadError = "Please fill in all required fields.";
    }
}

?>

<!DOCTYPE html>
<html lang="en">

    <head>
        <base>
        <?php require_once "includes/head-setting.php"; ?>
        <link rel="stylesheet" href="styles/style-report-problem.css">
        <title>Smile & Sunshine | Report Problem</title>
    </head>
    
    <body>
        <div id="main-page">
            <?php require_once "includes/header.php"; ?>
            <div id="main-section">
                <div id="report-form">
                    <div id="form-header">
                        <h1>Report a Problem</h1>
                        <p id="subtitle">Help us improve user experience by reporting issues!</p>
                    </div>

                    <?php if (!empty($uploadError)): ?>
                        <div class="error-message" style="background: #f8d7da; color: #721c24; padding: 10px; border-radius: 5px; margin-bottom: 20px;">
                            <?= htmlspecialchars($uploadError) ?>
                        </div>
                    <?php endif; ?>

                    <form id="form" method="POST" action="report-problem.php" enctype="multipart/form-data">
                        <div class="form-group">
                            <label>Problem Type</label>
                            <div class="radio-group">
                                <label class="radio-label">
                                    <input type="radio" name="problem" value="wrong-info">
                                    <span class="radio-button"></span><span class="radio-word">Outdated or incorrect information</span>
                                </label>
                                <label class="radio-label">
                                    <input type="radio" name="problem" value="website-problem">
                                    <span class="radio-button"></span><span class="radio-word">Technical issues</span>
                                </label>
                                <label class="radio-label">
                                    <input type="radio" name="problem" value="security-issue">
                                    <span class="radio-button"></span><span class="radio-word">Security concern</span>
                                </label>
                                <label class="radio-label">
                                    <input type="radio" name="problem" value="content-issue">
                                    <span class="radio-button"></span><span class="radio-word">Inappropriate content</span>
                                </label>
                                <label class="radio-label">
                                    <input type="radio" name="problem" value="other-issue" checked>
                                    <span class="radio-button"></span><span class="radio-word">Other issues</span>
                                </label>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="problem_details">Detailed Issue</label>
                            <textarea id="problem_details" name="problem_details" placeholder="Please explain the issues in details ..." rows="6" required></textarea>
                        </div>

                        <?php if (!$isLoggedInCustomer): ?>
                        <div class="form-group">
                            <label for="reporter_name">Your Name</label>
                            <input type="text" id="reporter_name" name="reporter_name" placeholder="Enter your full name" required>
                        </div>

                        <div class="form-group">
                            <label for="reporter_email">Your Email</label>
                            <input type="email" id="reporter_email" name="reporter_email" placeholder="Enter your email address" required>
                        </div>

                        <div class="form-group">
                            <label class="checkbox-label">
                                <input type="checkbox" name="is_customer" id="is_customer" onchange="toggleCustomerId()">
                                <span class="checkbox-button"></span>
                                <span class="checkbox-word">I am a registered customer</span>
                            </label>
                        </div>

                        <div class="form-group" id="customer-id-group" style="display:none;">
                            <label for="customer_id">Customer ID</label>
                            <input type="number" id="customer_id" name="customer_id" placeholder="Enter your customer ID">
                        </div>

                        <?php else: ?>
                        <div class="form-group">
                            <label>Reporter</label>
                            <div style="padding: 12px; background: #f8f9fa; border-radius: 5px; border: 1px solid #dee2e6;">
                                <strong><?php echo $customerData["username"]; ?></strong> (Customer ID: <?php echo $customerData["user_id"]; ?>)
                                <br><small><?php echo $customerData["useremail"]; ?></small>
                            </div>
                        </div>
                        <?php endif; ?>

                        <div class="form-group">
                            <label for="file">Screenshot</label>
                            <div id="file-container">
                                <label id="file-label">
                                    <input type="file" id="file" name="fileToUpload" accept="image/*">
                                    <span>Choose file or drag & drop</span>
                                </label>
                            </div>
                        </div>

                        <div id="button-container">
                            <button type="submit" name="submit" id="btn-submit">Submit Report</button>
                            <button type="reset" id="btn-reset" onclick="resetForm()">Clear Form</button>
                        </div>
                    </form>
                </div>
            </div>

            <?php require_once "includes/footer.php"; ?>
            <?php require_once "includes/tools.php"; ?>
        </div>

        <script src="script/script-general.js"></script>
        <?php if (!$isLoggedInCustomer): ?>
        <script>
            function toggleCustomerId() {
                const checkbox = document.getElementById('is_customer');
                const customerIdGroup = document.getElementById('customer-id-group');
                const customerIdInput = document.getElementById('customer_id');
                
                if (checkbox.checked) {
                    customerIdGroup.style.display = 'block';
                    customerIdInput.required = true;
                } else {
                    customerIdGroup.style.display = 'none';
                    customerIdInput.required = false;
                    customerIdInput.value = '';
                }
            }

            function resetForm() {
                document.getElementById('customer-id-group').style.display = 'none';
                document.getElementById('customer_id').required = false;
                document.getElementById('is_customer').checked = false;
            }

            toggleCustomerId();

        </script>
        <?php endif; ?>

    </body>

</html>

<?php if (isset($conn)) $conn->close(); ?>
