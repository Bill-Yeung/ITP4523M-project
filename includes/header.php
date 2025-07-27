<div id="header">

    <div id="header-first">

        <div id="logo">
            <a href="index.php"><img src="img/logo/logo.PNG" alt="Logo"/></a>
            <div id="headline">
                <a href="index.php"><h2>Smile & Sunshine Toy Co, Ltd.</h2></a>
                <a href="index.php"><h3>We are the best toy manufacturing company in the World !</h3></a>
            </div>
        </div>

        <div id="tool-section">
            <div id="user-section">
                <?php if (!isLoggedIn()): ?>
                    <a onclick="window.location.href='login.php'" class="button"><span>Login</span><span>|</span><span>Register</span></a>
                <?php else: ?>
                    <div id="user-icon">
                        <img src="<?php 
                                    echo (isset($_SESSION["userinfo"]["image"])) 
                                    ? $_SESSION["userinfo"]["user_type"] . "/photos/" . $_SESSION["userinfo"]["image"] 
                                    : "img/chatbox/user.svg"; 
                                    ?>" alt="User Icon" />
                    </div>
                    <div id="user-name">
                        <span><?php echo $_SESSION["userinfo"]["username"]; ?></span>
                    </div>
                    <div id="user-manual">
                        <ul id="user-actions">
                            <li><a onclick="window.location.href='update-profile.php'">Update Profile</a></li>
                            <li><a onclick="alert('You have logout successfully!'); window.location.href='logout.php';">Logout</a></li>
                        </ul>
                    </div>
                <?php endif; ?>
            </div>
        </div>

    </div>

    <div id="header-second">

        <div id="menu">
            <?php if (isCustomer()): ?>
                <ul>
                    <li><a href="customer/browse-products.php">Browse Products</a></li>
                    <li><a href="customer/place-orders.php">Order Cart</a></li>
                    <li><a href="customer/manage-orders.php">Manage Orders</a></li>
                    <li><a href="report-problem.php">Report Problem</a></li>
                    <li>
                        <a>Others</a>
                        <ul class="sub">
                            <li><a href="about-us.php">About Us</a></li>
                            <li><a href="contact-us.php">Contact Us</a></li>
                        </ul>
                    </li>
                </ul>
            <?php elseif (isStaff()): ?>
                <ul>
                    <li><a href="staff/product/manage-products.php">Manage Products</a></li>
                    <li><a href="staff/material/manage-materials.php">Manage Materials</a></li>
                    <li><a href="staff/production/manage-production.php">Manage Production</a></li>
                    <li><a href="staff/order/manage-customer-orders.php">Manage Orders</a></li>
                    <li><a href="staff/report/generate-report.php">Generate Report</a></li>
                    <li>
                        <a>Manage Users</a>
                        <ul class="sub">
                            <li><a href="staff/user/manage-customer.php">Manage Customer</a></li>
                            <li><a href="staff/user/manage-staff.php">Manage Staff</a></li>
                        </ul>
                    </li>
                    <li><a href="staff/issue/view-issue.php">Manage Issues Raised</a></li>
                    <li><a href="about-us.php">About Us</a></li>
                </ul>
            <?php else: ?>
                <ul>
                    <li><a href="about-us.php">About Us</a></li>
                    <li><a href="contact-us.php">Contact Us</a></li>
                    <li><a href="report-problem.php">Report Problem</a></li>
                </ul>
            <?php endif; ?>
        </div>

    </div>

</div>
