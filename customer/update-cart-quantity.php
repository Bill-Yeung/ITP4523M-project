<?php

require_once __DIR__ . "/../config/database.php";
require_once __DIR__ . "/../includes/functions.php";

requireLogin();

if (isset($_POST["productId"]) && isset($_POST["revisedQuantity"])) {
    $_SESSION["shopping_cart"][$_POST["productId"]] = $_POST["revisedQuantity"];

    $response = ["success" => true,
                 "cart" => $_SESSION["shopping_cart"]];

} else {
    $response = ["success"=> false];
}

echo json_encode($response);

?>