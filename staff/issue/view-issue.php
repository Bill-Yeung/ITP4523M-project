<?php

require_once __DIR__ . "/../../config/database.php";
require_once __DIR__ . "/../../includes/functions.php";
requireLogin();

if ($_SERVER['REQUEST_METHOD'] == "POST" && isset($_POST["resolve_id"])) {
    $resolveId = intval($_POST["resolve_id"]);
    $stmt = $conn->prepare("UPDATE issue SET status = 1 WHERE issueId = ?");
    $stmt->bind_param("i", $resolveId);
    $stmt->execute();
    $stmt->close();
}

$result = $conn->query("SELECT * FROM issue ORDER BY issueId DESC");
?>

<!DOCTYPE html>
<html lang="en">

    <head>
        <base href="../../">
        <?php require_once __DIR__ . "/../../includes/head-setting.php"; ?>
        <link rel="stylesheet" href="styles/style-issues.css">
        <title>Issue Records</title>
    </head>

    <body>
    <?php require_once __DIR__ . "/../../includes/header.php"; ?>

    <section id="main-section">
        <div id="issues-container">
            <div id="issues-header">
                <h2>Issue Records</h2>
                <p>Manage and resolve customer issues</p>
            </div>
            <div id="issues-section">
                <div id="issues-table-container">
                    <table id="issues-table">
                        <thead class="table-header">
                            <tr>
                                <th class="col-id">ID</th>
                                <th class="col-type">Type</th>
                                <th class="col-details">Details</th>
                                <th class="col-screenshot">Screenshot</th>
                                <th class="col-reporter">Reporter</th>
                                <th class="col-email">Email</th>
                                <th class="col-customer">Is Cust</th>
                                <th class="col-cid">cID</th>
                                <th class="col-status">Status</th>
                                <th class="col-action">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($result->num_rows > 0): ?>
                                <?php while ($row = $result->fetch_assoc()): ?>
                                    <tr class="issue-row <?= $row['status'] ? 'resolved-row' : '' ?>">
                                        <td class="col-id"><?= htmlspecialchars($row['issueId']) ?></td>
                                        <td class="col-type"><?= htmlspecialchars($row['issueType']) ?></td>
                                        <td class="col-details">
                                                <p class="issue-details-paragraph"><?= nl2br(htmlspecialchars($row['issueDetails'])) ?></p>
                                        </td>
                                        <td class="col-screenshot">
                                            <img src="img/issues/<?= htmlspecialchars($row['screenshot']) ?>" alt="Screenshot" class="issue-img">
                                        </td>
                                        <td class="col-reporter reporter-info"><?= htmlspecialchars($row['rname']) ?></td>
                                        <td class="col-email email-info"><?= htmlspecialchars($row['remail']) ?></td>
                                        <td class="col-customer">
                                            <span class="customer-badge <?= $row['isCustomer'] ? 'customer-yes' : 'customer-no' ?>">
                                                <?= $row['isCustomer'] ? 'Yes' : 'No' ?>
                                            </span>
                                        </td>
                                        <td class="col-cid"><?= htmlspecialchars($row['cid']) ?></td>
                                        <td class="col-status">
                                            <span class="status-badge <?= $row['status'] ? 'status-resolved' : 'status-pending' ?>">
                                                <?= $row['status'] ? 'Resolved' : 'Pending' ?>
                                            </span>
                                        </td>
                                        <td class="col-action">
                                            <form method="post" style="margin:0;">
                                                <input type="hidden" name="resolve_id" value="<?= $row['issueId'] ?>">
                                                <button type="submit" class="resolve-btn" <?= $row['status'] ? 'disabled' : '' ?>>Resolve</button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr id="empty-issues">
                                    <td colspan="10">
                                        <p>No issues found. Please check back later.</p>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </section>

    <?php require_once __DIR__ . "/../../includes/footer.php"; ?>
    </body>

</html>

<?php $conn->close(); ?>
