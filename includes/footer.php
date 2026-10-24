</main>

<footer class="site-footer">
    <div class="container footer-inner">
        <div>
            <img class="logo logo-light" src="assets/img/logo-vdb-it.webp" alt="<?= e(SITE['name']) ?>" width="643" height="315" loading="lazy">
            <img class="logo logo-dark" src="assets/img/logo-vdb-it-dark.webp" alt="" width="643" height="315" loading="lazy">
            <p class="footer-tagline">AI en software voor het MKB en de maakindustrie.</p>
        </div>
        <address>
            <?= e(SITE['name']) ?> &middot; <?= e(SITE['owner']) ?><br>
            <?= e(SITE['street']) ?><br>
            <?= e(SITE['postcode']) ?> <?= e(SITE['city']) ?>
        </address>
        <div>
            <a href="tel:<?= e(SITE['phone_href']) ?>"><?= e(SITE['phone']) ?></a><br>
            <a href="mailto:<?= e(SITE['email']) ?>"><?= e(SITE['email']) ?></a>
            <?php if (SITE['kvk'] !== ''): ?><br>KvK <?= e(SITE['kvk']) ?><?php endif ?>
        </div>
    </div>
    <div class="container footer-bottom">
        <span>&copy; <?= date('Y') ?> <?= e(SITE['name']) ?></span>
        <a href="privacy.php">Privacyverklaring</a>
    </div>
</footer>
</body>
</html>
