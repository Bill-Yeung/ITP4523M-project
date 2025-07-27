<!DOCTYPE html>
<html lang="en">

    <head>
        <base>
        <?php require_once "includes/head-setting.php"; ?>
        <link id="index-css" rel="stylesheet" href="styles/style-index.css">
        <title>Smile & Sunshine | Welcome</title>
    </head>

    <?php if (isStaff()): ?>
        <script>
            fetch('staff/material/check-material-status.php')
                .then(response => response.json())
                .then(data => {
                    if (data.count > 0) {
                        if (confirm(`${data.count} material(s) are below re-order level.\nDo you want to auto-update their quantities?`)) {
                            fetch('staff/material/update-low-stock.php')
                                .then(response => response.json())
                                .then(result => {
                                    if (result.status === "updated") {
                                        alert("Materials have been updated successfully.");
                                        location.reload();
                                    } else {
                                        alert("Update failed: " + (result.message || "Unknown error."));
                                    }
                                })
                                .catch(error => {
                                    console.error("Update error:", error);
                                    alert("Something went wrong during update.");
                                });
                        } else {
                            alert("Auto-update cancelled.");
                        }
                    }
                })
                .catch(error => {
                    console.error("Check error:", error);
                    alert("Unable to check material status.");
                });
        </script>
    <?php endif; ?>

    <body>

        <div id="main-page">

            <?php require_once "includes/header.php"; ?>
            
            <div id="main-section">

                <div id="background-section">
                    <img id="background-photo" src="img/background-photo.png">
                    <div id="background-items">
                        <h1>Creating Smiles Worldwide</h1>
                        <?php if (!isLoggedIn()): ?>
                            <a onclick="window.location.href='login.php'" id="discover-button">Discover Our Toys</a>
                        <?php elseif (isCustomer()): ?>
                            <a onclick="window.location.href='customer/browse-products.php'" id="discover-button">Discover Our Toys</a>
                        <?php endif; ?>
                    </div>
                </div>

                <div id="introduction-section">
                    <h2>Welcome to Smile & Sunshine</h2>
                    <p>
                        Smile & Sunshine Toy Co, Ltd. is a market leader of the toy industry <br/>
                        that is producing attractive and wonderful toys to the world!
                    </p>
                </div>

                <div id="production-section">
                    <div class="product-card">
                        <div class="img-container">
                            <img src="img/product/1.png">
                        </div>
                        <div class="product-content">
                            <h2>Self-designed products</h2>
                            <p>Discover our unique collection of toys designed by our creative team.</p>
                            <?php if (!isLoggedIn()): ?>
                                <a onclick="window.location.href='login.php'" class="link">View Products</a>
                            <?php elseif (isCustomer()): ?>
                                <a onclick="window.location.href='customer/browse-products.php'" class="link">View Products</a>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="product-card">
                        <div class="img-container">
                            <img src="img/product/2.png">
                        </div>
                        <div class="product-content">
                            <h2>Tailor-made products</h2>
                            <p>Discover our unique collection of toys designed by our creative team.</p>
                            <?php if (!isLoggedIn()): ?>
                                <a onclick="window.location.href='login.php'" class="link">Request Custom Order</a>
                            <?php elseif (isCustomer()): ?>
                                <a onclick="window.location.href='contact-us.html'" class="link">Contact Us</a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

            </div>

            <?php require_once "includes/footer.php"; ?>
            <?php require_once "includes/tools.php"; ?>

        </div>

        <script src="script/script-general.js"></script>

    </body>

</html>