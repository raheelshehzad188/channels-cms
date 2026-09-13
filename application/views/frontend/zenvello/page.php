<nav class="container breadcrumb" aria-label="Breadcrumb">
  <ol>
    <li><a href="<?= storefront_url('shop/index') ?>">Home</a></li>
    <li aria-current="page"><?= htmlspecialchars(isset($page_title) ? $page_title : 'Contact') ?></li>
  </ol>
</nav>

<section class="container section">
  <div class="section-head">
    <div>
      <h1 class="section-title"><?= htmlspecialchars(isset($page_title) ? $page_title : 'Contact') ?></h1>
      <p class="section-sub">Get in touch with <?= htmlspecialchars($store->name) ?>.</p>
    </div>
  </div>
  <div class="simple-card" style="max-width:640px;background:#fff;border:1px solid var(--line);border-radius:12px;padding:24px">
    <p><strong>Email:</strong> <?= htmlspecialchars(theme_setting($settings, 'email', $store->email ?: 'hello@' . $store->domain)) ?></p>
    <p><strong>Phone:</strong> <?= htmlspecialchars(theme_setting($settings, 'phone', '')) ?></p>
    <p><strong>Address:</strong> <?= htmlspecialchars(theme_setting($settings, 'address', '')) ?></p>
    <p class="section-sub" style="margin-top:16px"><?= htmlspecialchars(theme_setting($settings, 'footer_about', 'We would love to hear from you.')) ?></p>
  </div>
</section>
