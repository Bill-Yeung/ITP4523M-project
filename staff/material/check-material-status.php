<?php
require_once __DIR__ . "/../../config/database.php";

header('Content-Type: application/json');

$sql = "SELECT COUNT(*) AS count FROM material WHERE (mqty - mrqty) < mreorderqty";
$rs = mysqli_query($conn, $sql);

if ($rs) {
    $row = mysqli_fetch_assoc($rs);
    echo json_encode(["count" => (int)$row["count"]]);
    mysqli_free_result($rs);
} else {
    echo json_encode(["count" => 0, "error" => "Query failed"]);
}

mysqli_close($conn);
?>
