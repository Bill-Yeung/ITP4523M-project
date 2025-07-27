<?php

require_once __DIR__ . "/../../config/database.php";
require_once __DIR__ . "/../../includes/functions.php";

requireLogin();

$sql = "SELECT orders.oid, product.pid, product.pname, product.pimage, orders.oqty, product.pcost, (orders.oqty * product.pcost) AS total_amount
        FROM orders
        INNER JOIN product ON orders.pid = product.pid
        ORDER BY orders.oid DESC";

$result = $conn->query($sql);

$rows = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $rows[] = $row;
    }
}

// Aggregate data for chart
$aggregated = [];

foreach ($rows as $row) {
    $pid = (int)$row["pid"];
    $pname = $row["pname"];
    $total = (float)$row["total_amount"];

    if (!isset($aggregated[$pid])) {
        $aggregated[$pid] = [
            "id" => $pid,  // Changed from "pid" to "id" to match JavaScript
            "product" => $pname,
            "total" => 0,
        ];
    }
    $aggregated[$pid]["total"] += $total;
}

$chartData = array_values($aggregated);

// Sort by product ID
usort($chartData, function($a, $b) {
    return $a["id"] <=> $b["id"];
});

if (isset($conn)) {
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <base href="../../">
    <?php require_once __DIR__ . "/../../includes/head-setting.php"; ?>
    <title>Smile & Sunshine | Generate Report</title>
    <link rel="stylesheet" href="styles/style-generate-report.css">
</head>
<body>
    <div id="main-page">
        <?php require_once __DIR__ . "/../../includes/header.php"; ?>
        
        <div id="main-section">
            <div id="report-container">
                <div id="report-header">
                    <h2>Sales Report</h2>
                    <p>Overview of product sales and revenue</p>
                </div>

                <div id="summary-cards">
                    <div class="summary-card">
                        <div class="card-content">
                            <h3><?php echo count($rows); ?></h3>
                            <p>Total Orders</p>
                        </div>
                    </div>
                    <div class="summary-card">
                        <div class="card-content">
                            <h3><?php echo count($chartData); ?></h3>
                            <p>Products Sold</p>
                        </div>
                    </div>
                    <div class="summary-card">
                        <div class="card-content">
                            <h3>$<?php echo number_format(array_sum(array_column($rows, "total_amount")), 2); ?></h3>
                            <p>Total Revenue</p>
                        </div>
                    </div>
                </div>
            </div>

            <div id="chart-section">
                <h3>Revenue by Product</h3>
                <div id="chart-container">
                    <canvas id="barChart" width="1200" height="500"></canvas>
                </div>
            </div>

            <div id="table-section">
                <div id="table-header">
                    <h3>Detailed Sales Data</h3>
                    <div id="table-controls">
                        <span id="record-count"><?php echo count($rows); ?> records found</span>
                    </div>
                </div>
                
                <div id="table-container">
                    <table id="sales-table">
                        <thead>
                            <tr class="table-header-row">
                                <th class="col-id">Order ID</th>
                                <th class="col-product-id">Product ID</th>
                                <th class="col-name">Product Name</th>
                                <th class="col-image">Image</th>
                                <th class="col-qty">Quantity</th>
                                <th class="col-price">Unit Price</th>
                                <th class="col-total">Total Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php if (!empty($rows)): ?>
                            <?php foreach ($rows as $row): ?>
                                <tr class="table-row">
                                    <td class="order-id"><?php echo $row["oid"]; ?></td>
                                    <td class="product-id"><?php echo $row["pid"]; ?></td>
                                    <td class="product-name"><?php echo htmlspecialchars($row["pname"]); ?></td>
                                    <td class="product-image">
                                        <?php if (!empty($row['pimage'])): ?>
                                            <img class="product-img" src="img/product/<?php echo htmlspecialchars($row["pimage"]); ?>" alt="Product Image">
                                        <?php else: ?>
                                            <div class="no-image">No Image</div>
                                        <?php endif; ?>
                                    </td>
                                    <td class="quantity"><?php echo $row["oqty"]; ?></td>
                                    <td class="unit-price">$<?php echo number_format($row["pcost"], 2); ?></td>
                                    <td class="total-amount">
                                        <strong>$<?php echo number_format($row["total_amount"], 2); ?></strong>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr class="no-data">
                                <td colspan="7">
                                    <div id="empty-state">
                                        <h4>No Sales Data Found</h4>
                                        <p>There are no orders to display in the report.</p>
                                    </div>
                                </td>
                            </tr>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <?php require_once __DIR__ . "/../../includes/footer.php"; ?>
        <?php require_once __DIR__ . "/../../includes/tools.php"; ?>
    </div>

    <script>
        // Pass chart data to JavaScript
        var chartData = <?php echo json_encode($chartData); ?>;
        console.log('Chart Data:', chartData); // Debug log
    </script>

    <script src="script/script-general.js"></script>
    <script src="script/script-generate-report.js"></script>
</body>
</html>