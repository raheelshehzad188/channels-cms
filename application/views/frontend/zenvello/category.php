<?php
$hero = $assets . 'assets/images/hero/hero-living.jpg';
if (!empty($category_hero)) {
    $hero = storefront_asset_url($category_hero);
}
$heroTitle = !empty($category->display_hero_title)
    ? $category->display_hero_title
    : theme_setting($settings, 'hero_title', store_ui('home.title_smart'));
$heroText = !empty($category->display_hero_text)
    ? $category->display_hero_text
    : theme_setting($settings, 'hero_subtitle', store_ui('home.text_smart'));
$heroKicker = !empty($category->display_hero_kicker) ? $category->display_hero_kicker : store_ui('home.kicker_living');
$heroBtnText = !empty($category->display_hero_btn_text) ? $category->display_hero_btn_text : store_ui('cta.shop_now');
$heroBtnLinkRaw = !empty($category->display_hero_btn_link) ? trim((string) $category->display_hero_btn_link) : '';
$extraText = !isset($category->display_hero_extra_text) || (int) $category->display_hero_extra_text === 1;
$heroBtnLink = $heroBtnLinkRaw;
if ($extraText && $heroBtnLink === '') {
    $heroBtnLink = storefront_url('shop');
}
if ($heroBtnLink !== '' && isset($heroBtnLink[0]) && $heroBtnLink[0] !== '#' && strpos($heroBtnLink, 'http') !== 0 && strpos($heroBtnLink, '//') !== 0) {
    $heroBtnLink = storefront_url(ltrim($heroBtnLink, '/'));
}
$discSmall = !empty($category->display_hero_disc_small) ? $category->display_hero_disc_small : store_ui('home.disc_upto');
$discBig = !empty($category->display_hero_disc_big) ? $category->display_hero_disc_big : '50%';
$discSpan = !empty($category->display_hero_disc_span) ? $category->display_hero_disc_span : store_ui('home.disc_off');
$showDisc = !empty($category->display_hero_disc_on) && trim($discSmall . $discBig . $discSpan) !== '';
$imageHref = (!$extraText && $heroBtnLink !== '') ? $heroBtnLink : '';
$heroAlt = category_store_name($category);
?>
<div class="container">
  <section class="hero hero--static" aria-label="<?= htmlspecialchars($heroAlt) ?>">
    <div class="hero__track">
      <article class="hero__slide<?= $extraText ? '' : ' is-image-only' ?>">
        <?php if ($imageHref !== ''): ?>
        <a class="hero__hit" href="<?= htmlspecialchars($imageHref) ?>">
          <img class="hero__bg" src="<?= $hero ?>" alt="<?= htmlspecialchars($heroAlt) ?>">
        </a>
        <?php else: ?>
        <img class="hero__bg" src="<?= $hero ?>" alt="<?= htmlspecialchars($heroAlt) ?>">
        <?php endif; ?>
        <?php if ($extraText): ?>
        <div class="hero__body">
          <p class="hero__kicker"><?= htmlspecialchars($heroKicker) ?></p>
          <h1 class="hero__title"><?= nl2br(htmlspecialchars($heroTitle)) ?></h1>
          <p class="hero__text"><?= htmlspecialchars($heroText) ?></p>
          <a class="btn btn--yellow btn--lg" href="<?= htmlspecialchars($heroBtnLink) ?>"><?= htmlspecialchars($heroBtnText) ?> <span class="arrow" aria-hidden="true">&rarr;</span></a>
        </div>
        <?php if ($showDisc): ?>
        <div class="hero__disc" aria-hidden="true">
          <?php if ($discSmall !== ''): ?><small><?= htmlspecialchars($discSmall) ?></small><?php endif; ?>
          <?php if ($discBig !== ''): ?><b><?= htmlspecialchars($discBig) ?></b><?php endif; ?>
          <?php if ($discSpan !== ''): ?><span><?= htmlspecialchars($discSpan) ?></span><?php endif; ?>
        </div>
        <?php endif; ?>
        <?php endif; ?>
      </article>
    </div>
  </section>
</div>

<?php
$hideEmptySubs = setting_flag_on($settings, 'hide_empty_subcategories');
$visibleSubs = array();
if (!empty($subcategories)) {
    foreach ($subcategories as $sub) {
        if ($hideEmptySubs && isset($sub->product_count) && (int) $sub->product_count < 1) {
            continue;
        }
        $visibleSubs[] = $sub;
    }
}
?>
<?php if (!empty($visibleSubs)): ?>
<section class="container catstrip" aria-label="<?= e_ui('shop.subcats') ?>">
  <div class="section-head" style="margin-bottom:1rem">
    <div>
      <h2 class="section-title"><?= e_ui('shop.shop_subcat') ?></h2>
      <p class="section-sub"><?= e_ui('shop.browse_within', array('{name}' => category_store_name($category))) ?></p>
    </div>
  </div>
  <ul class="circles">
    <?php foreach ($visibleSubs as $sub): ?>
      <?php
        $img = !empty($sub->display_image) ? $sub->display_image : $sub->image;
      ?>
      <li>
        <a class="circle" href="<?= category_url($sub) ?>">
          <?php if ($img): ?>
            <span class="circle__ring" aria-hidden="true" style="background-image:url('<?= storefront_asset_url($img) ?>');background-size:cover;background-position:center"></span>
          <?php else: ?>
            <span class="circle__ring" aria-hidden="true"><?= htmlspecialchars($sub->icon ?: '•') ?></span>
          <?php endif; ?>
          <span class="circle__label"><?= htmlspecialchars(category_store_name($sub)) ?></span>
        </a>
      </li>
    <?php endforeach; ?>
  </ul>
</section>
<?php endif; ?>

<section class="container section" id="category-products" aria-labelledby="cat-products">
  <div class="section-head">
    <div>
      <h2 class="section-title" id="cat-products"><?= e_ui('shop.category_products', array('{name}' => category_store_name($category))) ?></h2>
      <p class="section-sub"><?= htmlspecialchars(storefront_ui_count('shop.item_one', 'shop.item_many', (int) $products_total)) ?></p>
    </div>
  </div>
  <div class="grid-4" id="category-product-grid" data-offset="<?= count($products) ?>" data-has-more="<?= !empty($has_more) ? '1' : '0' ?>" data-url="<?= htmlspecialchars($load_more_url) ?>">
    <?php if (empty($products)): ?>
        <p class="section-sub"><?= e_ui('shop.no_category') ?></p>
    <?php else: ?>
      <?php foreach ($products as $product): ?>
        <?php $this->load->view('frontend/zenvello/product_card', array('product' => $product, 'assets' => $assets, 'is_preview' => !empty($is_preview))); ?>
      <?php endforeach; ?>
    <?php endif; ?>
  </div>
  <div id="category-load-status" class="section-sub" style="text-align:center;margin-top:1.5rem;display:none"><?= e_ui('shop.loading_more') ?></div>
</section>

<script>
(function () {
  var grid = document.getElementById('category-product-grid');
  if (!grid || grid.getAttribute('data-has-more') !== '1') return;
  var loading = false;
  var status = document.getElementById('category-load-status');
  var sentinel = document.createElement('div');
  sentinel.id = 'category-scroll-sentinel';
  sentinel.style.height = '1px';
  grid.parentNode.appendChild(sentinel);

  function loadMore() {
    if (loading || grid.getAttribute('data-has-more') !== '1') return;
    loading = true;
    if (status) status.style.display = 'block';
    var offset = parseInt(grid.getAttribute('data-offset') || '0', 10);
    var url = grid.getAttribute('data-url') + (grid.getAttribute('data-url').indexOf('?') >= 0 ? '&' : '?') + 'offset=' + offset;
    fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
      .then(function (r) { return r.json(); })
      .then(function (data) {
        if (data && data.ok && data.html) {
          grid.insertAdjacentHTML('beforeend', data.html);
          grid.setAttribute('data-offset', String(data.next_offset || offset));
          grid.setAttribute('data-has-more', data.has_more ? '1' : '0');
        } else {
          grid.setAttribute('data-has-more', '0');
        }
      })
      .catch(function () { grid.setAttribute('data-has-more', '0'); })
      .finally(function () {
        loading = false;
        if (status) status.style.display = 'none';
      });
  }

  if ('IntersectionObserver' in window) {
    var io = new IntersectionObserver(function (entries) {
      entries.forEach(function (entry) {
        if (entry.isIntersecting) loadMore();
      });
    }, { rootMargin: '400px 0px' });
    io.observe(sentinel);
  } else {
    window.addEventListener('scroll', function () {
      if ((window.innerHeight + window.scrollY) >= (document.body.offsetHeight - 800)) loadMore();
    });
  }
})();
</script>
