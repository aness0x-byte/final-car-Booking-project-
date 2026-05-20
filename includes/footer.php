<?php
$basePath = $basePath ?? '';
?>

<!-- ═══════════════════════════════════════════════
     FOOTER
     ═══════════════════════════════════════════════ -->
<footer class="footer">
    <div class="container">
        <div class="footer-grid">

            <!-- Brand column -->
            <div>
                <span class="footer-brand">🚗 DriveEase</span>
                <p style="font-size:.88rem;line-height:1.7;">
                    Simple car rental service. Pick a car, choose your dates,
                    and book it — that's it!
                </p>
            </div>

            <!-- Quick links -->
            <div>
                <h4>Quick Links</h4>
                <ul>
                    <li><a href="<?= $basePath ?>index.php">Browse Cars</a></li>
                    <li><a href="<?= $basePath ?>login.php">Sign In</a></li>
                    <li><a href="<?= $basePath ?>register.php">Create Account</a></li>
                    <li><a href="<?= $basePath ?>my-bookings.php">My Bookings</a></li>
                </ul>
            </div>
        </div>

        <!-- Bottom bar -->
        <div class="footer-bottom">
            <p>© <?= date('Y') ?> DriveEase. All rights reserved.
                Built with HTML, PHP, JavaScript, CSS &amp; MySQL.
            </p>
        </div>
    </div>
</footer>

<!-- Main JavaScript file -->
<script src="<?= $basePath ?>js/main.js"></script>
</body>
</html>
