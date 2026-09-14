</main>

<footer class="site-footer">
  <div class="container">
    <div class="footer__grid">
      <div class="footer__brand">
        <a class="logo" href="<?= storefront_url('shop/index') ?>">
          <?php $footerLogo = theme_setting($settings, 'logo'); ?>
          <?php if ($footerLogo): ?>
            <img src="<?= storefront_asset_url($footerLogo) ?>" alt="<?= htmlspecialchars($store->name) ?>" style="max-height:40px">
          <?php else: ?>
            <span class="logo__word">ZEN<em>Vello</em></span>
            <span class="logo__tag"><?= htmlspecialchars($store->name) ?></span>
          <?php endif; ?>
        </a>
        <p class="footer__about"><?= htmlspecialchars(theme_setting($settings, 'footer_about', 'Your one-stop shop for quality products at the best prices. Shop smart, live better.')) ?></p>
      </div>

      <nav class="footer__col" aria-labelledby="f-shop">
        <h3 id="f-shop">Shop</h3>
        <ul>
          <li><a href="<?= storefront_url('shop') ?>">All Products</a></li>
          <li><a href="<?= storefront_url('cart') ?>">Cart</a></li>
          <li><a href="<?= storefront_url(storefront_customer() ? 'account' : 'account/login') ?>">Account</a></li>
        </ul>
      </nav>

      <nav class="footer__col" aria-labelledby="f-service">
        <h3 id="f-service">Customer Service</h3>
        <ul>
          <li><a href="<?= storefront_url('page/contact') ?>">Contact Us</a></li>
          <li><a href="<?= storefront_url('page/contact') ?>">Help Center</a></li>
        </ul>
      </nav>

      <nav class="footer__col" aria-labelledby="f-about">
        <h3 id="f-about">About</h3>
        <ul>
          <li><a href="<?= storefront_url('page/contact') ?>">About Us</a></li>
        </ul>
      </nav>

      <div class="footer__col">
        <h3>Stay in touch</h3>
        <p class="newsletter__lead"><?= htmlspecialchars(theme_setting($settings, 'footer_text', $store->name . '. All rights reserved.')) ?></p>
      </div>
    </div>

    <div class="footer__bottom">
      <p>&copy; <?= date('Y') ?> <?= htmlspecialchars($store->name) ?>. All rights reserved.</p>
      <p>Shop smart · Live better</p>
    </div>
  </div>
</footer>

<script src="<?= $assets ?>js/components.js"></script>
<script src="<?= $assets ?>js/main.js"></script>
</body>
</html>
