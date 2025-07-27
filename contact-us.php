<!DOCTYPE html>
<html lang="en">

    <head>
        <base>
        <?php require_once "includes/head-setting.php"; ?>
        <link id="contact-us-css" rel="stylesheet" href="styles/style-contact-us.css">
        <title>Smile & Sunshine | Contact Us</title>
    </head>

    <body>

        <div id="main-page">

            <?php require_once "includes/header.php"; ?>

            <div id="main-section">
                
                <div id="contact-header">
                    <h1>Contact Us</h1>
                    <p id="subtitle">Have any questions regarding our products or services? Please feel free to contact us through the following methods.</p>
                </div>
        
                <div id="contact-content">

                    <div id="map-container">
                        <h2>Our Location</h2>
                        <div id="map-wrapper">
                            <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3689.018893376746!2d114.19549227556516!3d22.39064517962172!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x340406497098eb4b%3A0xf2db767fa9507095!2z6aaZ5riv5bCI5qWt5pWZ6IKy5a246ZmiKOaymeeUsCk!5e0!3m2!1szh-TW!2shk!4v1744895249041!5m2!1szh-TW!2shk"></iframe>
                        </div>
                    </div>

                    <div id="contact-info">

                        <div class="contact-card">
                            <div class="icon-container">
                                <img src="img/contact/favorite-place.svg">
                            </div>
                            <div class="info-content">
                                <h3>Visit Us</h3>
                                <p>21 Yuen Wo Road<br>Sha Tin, New Territories<br>Hong Kong</p>
                                <a href="https://maps.app.goo.gl/9HGaADZ3Vwh3iT8k6" id="map-link">Get direction</a>
                            </div>
                        </div>
    
                        <div class="contact-card">
                            <div class="icon-container">
                                <img src="img/contact/call.svg">
                            </div>
                            <div class="info-content">
                                <h3>Call Us</h3>
                                <p>We are available on Monday to Friday, 9 AM to 6 PM (HKT)</p>
                                <a href="tel:+85226066227" id="phone-link">+852 2606 6227</a>
                            </div>
                        </div>
    
                        <div class="contact-card">
                            <div class="icon-container">
                                <img src="img/contact/email.svg">
                            </div>
                            <div class="info-content">
                                <h3>Email Us</h3>
                                <p>We will respond within 24 hours</p>
                                <a href="mailto:pr@smilesunshine.com" id="email-link">pr@smilesunshine.com</a>
                            </div>
                        </div>

                    </div>

                </div>
        
                <div id="faq-section">

                    <h2>Frequently Asked Questions</h2>

                    <div id="faq-items">
                        <div class="faq-item">
                            <h3>What are your office hours?</h3>
                            <p>Our office is opened from Monday to Friday, 9:00 AM to 6:00 PM (HK time). We are closed on Hong Kong public holidays.</p>
                        </div>
                        <div class="faq-item">
                            <h3>How quickly will I receive a response?</h3>
                            <p>We aim to respond to all inquiries within 24 hours during business days. For urgent matters, please call our office directly.</p>
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
