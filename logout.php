<?php

require_once "config/database.php";
require_once "includes/functions.php";

if (isCustomer()) {
    $sql = "UPDATE customer SET lastSession = '" . json_encode($_SESSION["shopping_cart"]) . "' WHERE cid = " . $_SESSION["userinfo"]["user_id"];
    $rc = mysqli_query($conn, $sql) or die(mysqli_error($conn));
}

// Destroy all session data
$_SESSION = array();
session_destroy();

// Redirect to index page
header("Location: index.php");
exit();
?>