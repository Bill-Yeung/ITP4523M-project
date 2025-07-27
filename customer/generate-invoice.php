<?php

require_once __DIR__ . "/../vendor/autoload.php";
use Mpdf\Mpdf, Mpdf\HTMLParserMode;

$css = file_get_contents(__DIR__ . '/../styles/style-customer-invoice.css');

$pdfStyles = "
body {
    background: white !important;
    background-image: none !important;
    background-color: white !important;
    margin: 0;
    padding: 0;
}

.invoice-container {
    background: white !important;
    background-image: none !important;
    background-color: white !important;
    width: 100%;
    max-width: none;
    margin: 0;
    padding: 20px;
    box-sizing: border-box;
}

/* Ensure all sections have white backgrounds */
.invoice-header,
.customer-section,
.order-section,
.totals-section,
.invoice-footer {
    background: white !important;
    background-image: none !important;
}

/* Ensure proper text colors for printing */
body, p, h1, h2, h3, td, th {
    color: #333 !important;
} ";

$order_id = $_GET["order_id"] ?? "";
if (empty($order_id)) {
    die("Order ID is required!");
}

ob_start();

?>

<!DOCTYPE html>
<html lang="en">
    
    <head>
        <base href="../">
        <?php require_once __DIR__ . "/../includes/head-setting.php"; ?>
        <title>Smile & Sunshine | Customer Invoice</title>
        <style><?= $css ?></style>
        <style><?= $pdfStyles ?></style>
    </head>

    <?php

    requireLogin();

    $sql = "SELECT o.oid, o.odate, p.pname, p.pimage, o.oqty, o.ocost, o.odeliverdate, o.ostatus, c.cname, c.cemail, c.company, c.caddr
            FROM orders o, product p, customer c
            WHERE o.oid = $order_id AND o.pid = p.pid AND o.cid = c.cid AND o.cid = {$_SESSION["userinfo"]["user_id"]}";
    $rs = mysqli_query($conn, $sql) or die(mysqli_error($conn));
    $order = mysqli_fetch_assoc($rs);

    mysqli_free_result($rs);
    mysqli_close($conn);

    if(!$order){
        header("Location: manage-orders.php");
    } 

    $invoice_number = 'INV-' . str_pad($order['oid'], 6, '0', STR_PAD_LEFT);
    $invoice_date = date('Y-m-d', strtotime($order['odate']));

    ?>

    <body>

        <div class="invoice-container">

            <div class="invoice-header">
                <table class="invoice-header-table">
                    <tr>
                        <td class="company-info">
                            <table class="company-layout">
                                <tr>
                                    <td class="logo-cell">
                                        <img src="img/logo/logo.PNG" alt="Company Logo" class="company-logo">
                                    </td>
                                    <td class="company-text">
                                        <h1>Smile & Sunshine Toy Co, Ltd.</h1>
                                        <p class="company-slogan">We are the best toy manufacturing company in the World!</p>
                                        <div class="company-contact">
                                            <p>Email: pr@smilesunshine.com</p>
                                            <p>Phone: +852 2606 6227</p>
                                        </div>
                                    </td>
                                </tr>
                            </table>
                        </td>
                        <td class="invoice-info">
                            <h2>INVOICE</h2>
                            <table class="invoice-details">
                                <tr>
                                    <td>Invoice #:</td>
                                    <td><?php echo $invoice_number; ?></td>
                                </tr>
                                <tr>
                                    <td>Invoice Date:</td>
                                    <td><?php echo $invoice_date; ?></td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>
            </div>

            <div style="min-height: 100vh;">

                <div class="customer-section">
                    <div class="bill-to">
                        <h3>To:</h3>
                        <div class="customer-details">
                            <p><strong><?php echo $order["cname"]; ?></strong></p>
                            <?php if (!empty($order["company"])): ?>
                                <p><?php echo $order["company"]; ?></p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <div class="order-section">
                    <h3>Order Details</h3>
                    <table class="order-table">
                        <thead>
                            <tr>
                                <th style="width: 40%;">Product</th>
                                <th style="width: 15%;" class="text-right">Unit Price</th>
                                <th style="width: 10%;" class="text-right">Quantity</th>
                                <th style="width: 10%;" class="text-right">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>
                                    <table class="product-info-table">
                                        <tr>
                                            <td style="width: 40px;">
                                                <img src="img/product/<?php echo $order["pimage"]; ?>" class="product-image">
                                            </td>
                                            <td>
                                                <?php echo $order["pname"]; ?>
                                            </td>
                                        </tr>
                                    </table>
                                </td>
                                <td class="text-right">US$<?php echo number_format($order["ocost"] / $order["oqty"], 2); ?></td>
                                <td class="text-right"><?php echo $order["oqty"]; ?></td>
                                <td class="text-right">US$<?php echo number_format($order["ocost"], 2); ?></td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="totals-section">
                    <table class="totals-table">
                        <tr>
                            <td>Subtotal:</td>
                            <td>US$<?php echo number_format($order["ocost"], 2); ?></td>
                        </tr>
                        <tr>
                            <td>Tax (0%):</td>
                            <td>US$0.00</td>
                        </tr>
                        <tr class="total-row">
                            <td>Total:</td>
                            <td>US$<?php echo number_format($order["ocost"], 2); ?></td>
                        </tr>
                    </table>
                </div>

            </div>

            <div class="invoice-footer">
                <p>Thank you for your business!</p>
                <p class="small-text">This is a computer-generated invoice and does not require a signature.</p>
                <p class="small-text">For any questions regarding this invoice, please contact us at pr@smilesunshine.com</p>
            </div>

        </div>
        
    </body>

</html>

<?php

$html = ob_get_clean();

$mpdf = new Mpdf(["mode" => "utf-8", "format" => "A4", "margin_left" => 10, "margin_right"=> 10, "margin_top" => 15, "margin_bottom" => 15]);
$mpdf->WriteHTML($html);
$mpdf->Output('invoice.pdf', 'I'); // I: inline, D: download, F: file, S: string

exit;

?>