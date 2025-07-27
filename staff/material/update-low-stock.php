<?php
require_once __DIR__ . "/../../config/database.php";

header('Content-Type: application/json');

if (function_exists('requireLogin')) {
    requireLogin();
}

$updateSql = "UPDATE material SET mqty = (mreorderqty * 2) + mrqty WHERE (mqty - mrqty) < mreorderqty";

if (mysqli_query($conn, $updateSql)) {
    echo json_encode(["status" => "updated"]);
} else {
    echo json_encode(["status" => "error", "message" => mysqli_error($conn)]);
}

mysqli_close($conn);
?>
