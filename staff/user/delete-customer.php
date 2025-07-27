<?php

require_once __DIR__ . "/../../config/database.php";
require_once __DIR__ . "/../../includes/functions.php";

requireLogin();

$cid = $_GET["id"] ?? "";

if (empty($cid) || !is_numeric($cid)) {
    header("Location: manage-customer.php?status=error&msg=" . urlencode("Invalid customer ID provided"));
    exit;
}

$cid = intval($cid);

$checkStmt = $conn->prepare("SELECT cid, cname, cavail FROM customer WHERE cid = ?");
$checkStmt->bind_param("i", $cid);
$checkStmt->execute();
$result = $checkStmt->get_result();

if ($result->num_rows === 0) {
    $checkStmt->close();
    header("Location: manage-customer.php?status=error&msg=" . urlencode("Customer not found"));
    exit;
}

$customer = $result->fetch_assoc();
$checkStmt->close();

if ($customer["cavail"] == 0) {
    header("Location: manage-customer.php?status=warning&msg=" . urlencode("Customer '" . $customer["cname"] . "' is already deactivated"));
    exit;
}

$stmt = $conn->prepare("UPDATE customer SET cavail = 0 WHERE cid = ?");
$stmt->bind_param("i", $cid);

if ($stmt->execute()) {
    if ($stmt->affected_rows > 0) {
        $stmt->close();
        $conn->close();
        header("Location: manage-customer.php?status=success&msg=" . urlencode("Customer '" . $customer["cname"] . "' has been deactivated successfully"));
    } else {
        $stmt->close();
        $conn->close();
        header("Location: manage-customer.php?status=error&msg=" . urlencode("No changes were made to customer '" . $customer["cname"] . "'"));
    }
} else {
    $error_msg = "Failed to deactivate customer '" . $customer["cname"] . "': " . $stmt->error;
    $stmt->close();
    $conn->close();
    header("Location: manage-customer.php?status=error&msg=" . urlencode($error_msg));
}

exit;
?>