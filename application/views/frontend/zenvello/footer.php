</main>

<footer class="site-footer">
  <div class="container">
    <div class="footer__grid">
      <div class="footer__brand">
        <a class="logo" href="<?= !empty($is_preview) ? $preview_back : storefront_url('') ?>">
          <?php
            $footerIcon = theme_setting($settings, 'footer_icon');
            $footerLogo = theme_setting($settings, 'logo');
          ?>
          <?php if ($footerIcon): ?>
            <img class="footer__icon" src="<?= storefront_asset_url($footerIcon) ?>" alt="<?= htmlspecialchars($store->name) ?>">
          <?php elseif ($footerLogo): ?>
            <img class="footer__logo" src="<?= storefront_asset_url($footerLogo) ?>" alt="<?= htmlspecialchars($store->name) ?>">
          <?php else: ?>
            <span class="logo__word">ZEN<em>Vello</em></span>
            <span class="logo__tag"><?= htmlspecialchars($store->name) ?></span>
          <?php endif; ?>
        </a>
        <p class="footer__about"><?= htmlspecialchars(theme_setting($settings, 'footer_about', store_ui('footer.about_default'))) ?></p>
      </div>

      <nav class="footer__col" aria-labelledby="f-shop">
        <h3 id="f-shop"><?= e_ui('footer.shop') ?></h3>
        <ul>
          <li><a href="<?= storefront_url('shop') ?>"><?= e_ui('footer.all_products') ?></a></li>
          <li><a href="<?= storefront_url('cart') ?>"><?= e_ui('nav.cart') ?></a></li>
          <li><a href="<?= storefront_url(storefront_customer() ? 'account' : 'account/login') ?>"><?= e_ui('nav.account') ?></a></li>
        </ul>
      </nav>

      <nav class="footer__col" aria-labelledby="f-service">
        <h3 id="f-service"><?= e_ui('footer.service') ?></h3>
        <ul>
          <li><a href="<?= function_exists('contact_url') ? contact_url() : storefront_url('contact') ?>"><?= e_ui('footer.contact') ?></a></li>
          <li><a href="<?= function_exists('contact_url') ? contact_url() : storefront_url('contact') ?>"><?= e_ui('footer.help') ?></a></li>
        </ul>
      </nav>

      <nav class="footer__col" aria-labelledby="f-about">
        <h3 id="f-about"><?= e_ui('footer.about') ?></h3>
        <ul>
          <li><a href="<?= function_exists('contact_url') ? contact_url() : storefront_url('contact') ?>"><?= e_ui('footer.about_us') ?></a></li>
          <?php if (!empty($store_pages_footer)): ?>
            <?php foreach ($store_pages_footer as $cmsPage): ?>
              <li><a href="<?= page_url($cmsPage) ?>"><?= htmlspecialchars($cmsPage->title) ?></a></li>
            <?php endforeach; ?>
          <?php endif; ?>
        </ul>
      </nav>

      <div class="footer__col">
        <h3><?= e_ui('footer.stay') ?></h3>
        <p class="newsletter__lead"><?= htmlspecialchars(theme_setting($settings, 'footer_text', $store->name . '. ' . store_ui('footer.rights'))) ?></p>
      </div>
    </div>

    <div class="footer__bottom">
      <p>&copy; <?= date('Y') ?> <?= htmlspecialchars($store->name) ?>. <?= e_ui('footer.rights') ?></p>
      <p><?= e_ui('footer.tagline') ?></p>
    </div>
  </div>
</footer>

<script src="<?= $assets ?>js/components.js?v=3"></script>
<script src="<?= $assets ?>js/main.js?v=7"></script>
</body>
</html>
