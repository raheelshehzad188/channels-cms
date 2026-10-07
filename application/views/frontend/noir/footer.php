</main>
<footer class="site-footer">
    <div class="container">
        <?php $footerIcon = theme_setting($settings, 'footer_icon'); ?>
        <?php if ($footerIcon): ?>
        <p><img src="<?= storefront_asset_url($footerIcon) ?>" alt="" style="width:36px;height:36px;object-fit:contain;vertical-align:middle;margin-right:8px"><?= htmlspecialchars(theme_setting($settings, 'footer_text', $store->name)) ?></p>
        <?php else: ?>
        <p><?= htmlspecialchars(theme_setting($settings, 'footer_text', $store->name)) ?></p>
        <?php endif; ?>
    </div>
</footer>
<script src="<?= $assets ?>js/theme.js"></script>
</body>
</html>
