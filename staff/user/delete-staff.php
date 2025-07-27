<?php
require_once __DIR__ . "/../../config/database.php";
require_once __DIR__ . "/../../includes/functions.php";

requireLogin();

$sid = $_GET["id"] ?? "";

if (empty($sid) || !is_numeric($sid)) {
    header("Location: manage-staff.php?status=error&msg=" . urlencode("Invalid staff ID provided"));
    exit;
}

$sid = intval($sid);

$checkStmt = $conn->prepare("SELECT sid, sname, savail FROM staff WHERE sid = ?");
$checkStmt->bind_param("i", $sid);
$checkStmt->execute();
$result = $checkStmt->get_result();

if ($result->num_rows === 0) {
    $checkStmt->close();
    header("Location: manage-staff.php?status=error&msg=" . urlencode("Staff member not found"));
    exit;
}

$staff = $result->fetch_assoc();
$checkStmt->close();

if ($staff["savail"] == 0) {
    header("Location: manage-staff.php?status=warning&msg=" . urlencode("Staff member '" . $staff["sname"] . "' is already deactivated"));
    exit;
}

$stmt = $conn->prepare("UPDATE staff SET savail = 0 WHERE sid = ?");
$stmt->bind_param("i", $sid);

if ($stmt->execute()) {
    if ($stmt->affected_rows > 0) {
        $stmt->close();
        $conn->close();
        header("Location: manage-staff.php?status=success&msg=" . urlencode("Staff member '" . $staff["sname"] . "' has been deactivated successfully"));
    } else {
        $stmt->close();
        $conn->close();
        header("Location: manage-staff.php?status=error&msg=" . urlencode("No changes were made to staff member '" . $staff["sname"] . "'"));
    }
} else {
    $error_msg = "Failed to deactivate staff member '" . $staff["sname"] . "': " . $stmt->error;
    $stmt->close();
    $conn->close();
    header("Location: manage-staff.php?status=error&msg=" . urlencode($error_msg));
}

exit;
?>