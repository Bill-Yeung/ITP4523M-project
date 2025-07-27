<footer id="footer">

    <?php if (!isLoggedIn()): ?>

    <div id="footer-upper">
        <div class="footer-col">
            <h3>Smile & Sunshine</h3>
            <ul>
                <li><a onclick="window.location.href='about-us.php'">About Us</a></li>
                <li><a onclick="window.location.href='contact-us.php'">Contact Us</a></li>
            </ul>
        </div>
        <div class="footer-col">
            <h3>Others</h3>
            <ul>
                <li><a href="report-problem.php">Report Problem</a></li>
            </ul>
        </div>
    </div>

    <?php else: ?>

    <div id="footer-upper">
        <div class="footer-col">
            <h3>Quick Links</h3>
            <ul>
                <?php if ($_SESSION["userinfo"]["user_type"] == "customer"): ?>
                    <li><a href="customer/browse-products.php">Browse Products</a></li>
                    <li><a href="customer/place-orders.php">Place Orders</a></li>
                    <li><a href="customer/manage-orders.php">Manage Orders</a></li>
                <?php elseif ($_SESSION["userinfo"]["user_type"] == "staff"): ?>
                    <li><a href="staff/product/manage-products.php">Manage Products</a></li>
                    <li><a href="staff/material/manage-materials.php">Manage Materials</a></li>
                    <li><a href="staff/production/manage-production.php">Manage Production</a></li>
                    <li><a href="staff/order/manage-customer-orders.php">Manage Orders</a></li>
                <?php endif; ?>
            </ul>
        </div>
        <div class="footer-col">
            <h3>Others</h3>
            <ul>
                <?php if ($_SESSION["userinfo"]["user_type"] == "customer"): ?>
                    <li><a href="report-problem.php">Report Problem</a></li>
                    <li><a href="about-us.php">About Us</a></li>
                    <li><a href="contact-us.php">Contact Us</a></li>
                    <li><a href="update-profile.php">Update Profile</a></li>
                <?php elseif ($_SESSION["userinfo"]["user_type"] == "staff"): ?>
                    <li><a href="staff/report/generate-report.php">Generate Report</a></li>
                    <li><a href="staff/user/manage-customer.php">Manage Customer</a></li>
                    <li><a href="staff/user/manage-staff.php">Manage Staff</a></li>
                    <li><a href="staff/issue/view-issue.php">Manage Issues Raised</a></li>
                    <li><a href="update-profile.php">Update Profile</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </div>

    <?php endif; ?>
    
    <div id="footer-lower">
        <p>Copyright &copy; 2025 Smile & Sunshine Toy Co, Ltd. All rights reserved.</p>
    </div>

</footer>