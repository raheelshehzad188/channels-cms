<ul class="nav nav-tabs mb-3">
  <li class="nav-item">
    <a class="nav-link <?= ($tab === 'general') ? 'active' : '' ?>" href="<?= $storeUrl ?>/theme-settings">General</a>
  </li>
  <?php if (!empty($is_zenvello)): ?>
  <li class="nav-item">
    <a class="nav-link <?= ($tab === 'slider') ? 'active' : '' ?>" href="<?= $storeUrl ?>/theme-settings/slider">Slider</a>
  </li>
  <?php endif; ?>
  <li class="nav-item">
    <a class="nav-link <?= ($tab === 'menu') ? 'active' : '' ?>" href="<?= $storeUrl ?>/theme-settings/menu">Header menu</a>
  </li>
</ul>
