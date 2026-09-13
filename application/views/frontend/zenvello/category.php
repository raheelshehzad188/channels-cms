<?php
$hero = $assets . 'assets/images/hero/hero-living.jpg';
if (!empty($category_hero)) {
    $hero = base_url($category_hero);
}
$heroTitle = !empty($category->display_hero_title)
    ? $category->display_hero_title
    : theme_setting($settings, 'hero_title', "Make Your Home\nFeel Like You");
$heroText = !empty($category->display_hero_text)
    ? $category->display_hero_text
    : theme_setting($settings, 'hero_subtitle', 'Discover smart, stylish and affordable products for a better everyday life.');
$heroKicker = !empty($category->display_hero_kicker) ? $category->display_hero_kicker : 'Modern Living';
$heroBtnText = !empty($category->display_hero_btn_text) ? $category->display_hero_btn_text : 'Shop Now';
$heroBtnLink = !empty($category->display_hero_btn_link) ? $category->display_hero_btn_link : storefront_url('shop');
if ($heroBtnLink !== '' && isset($heroBtnLink[0]) && $heroBtnLink[0] !== '#' && strpos($heroBtnLink, 'http') !== 0 && strpos($heroBtnLink, '//') !== 0) {
    $heroBtnLink = storefront_url(ltrim($heroBtnLink, '/'));
}
?>
<div class="container">
  <section class="hero hero--static" aria-label="<?= htmlspecialchars($category->name) ?>">
    <div class="hero__track">
      <article class="hero__slide">
        <img class="hero__bg" src="<?= $hero ?>" alt="<?= htmlspecialchars($category->name) ?>">
        <div class="hero__body">
          <p class="hero__kicker"><?= htmlspecialchars($heroKicker) ?></p>
          <h1 class="hero__title"><?= nl2br(htmlspecialchars($heroTitle)) ?></h1>
          <p class="hero__text"><?= htmlspecialchars($heroText) ?></p>
          <a class="btn btn--yellow btn--lg" href="<?= htmlspecialchars($heroBtnLink) ?>"><?= htmlspecialchars($heroBtnText) ?> <span class="arrow" aria-hidden="true">&rarr;</span></a>
        </div>
        <div class="hero__disc" aria-hidden="true">
          <small>UP TO</small><b>50%</b><span>OFF</span>
        </div>
      </article>
    </div>
  </section>
</div>

<?php if (!empty($subcategories)): ?>
<section class="container catstrip" aria-label="Sub categories">
  <div class="section-head" style="margin-bottom:1rem">
    <div>
      <h2 class="section-title">Shop by sub-category</h2>
      <p class="section-sub">Browse within <?= htmlspecialchars($category->name) ?></p>
    </div>
  </div>
  <ul class="circles">
    <?php foreach ($subcategories as $sub): ?>
      <?php
        $img = !empty($sub->display_image) ? $sub->display_image : $sub->image;
      ?>
      <li>
        <a class="circle" href="<?= storefront_url('category/' . rawurlencode($sub->slug)) ?>">
          <?php if ($img): ?>
            <span class="circle__ring" aria-hidden="true" style="background-image:url('<?= base_url($img) ?>');background-size:cover;background-position:center"></span>
          <?php else: ?>
            <span class="circle__ring" aria-hidden="true"><?= htmlspecialchars($sub->icon ?: '•') ?></span>
          <?php endif; ?>
          <span class="circle__label"><?= htmlspecialchars($sub->name) ?></span>
        </a>
      </li>
    <?php endforeach; ?>
  </ul>
</section>
<?php endif; ?>

<section class="container section" id="category-products" aria-labelledby="cat-products">
  <div class="section-head">
    <div>
      <h2 class="section-title" id="cat-products"><?= htmlspecialchars($category->name) ?> products</h2>
      <p class="section-sub"><?= (int) $products_total ?> item<?= ((int) $products_total === 1) ? '' : 's' ?></p>
    </div>
  </div>
  <div class="grid-6" id="category-product-grid" data-offset="<?= count($products) ?>" data-has-more="<?= !empty($has_more) ? '1' : '0' ?>" data-url="<?= htmlspecialchars($load_more_url) ?>">
    <?php if (empty($products)): ?>
      <p class="section-sub">No products in this category yet.</p>
    <?php else: ?>
      <?php foreach ($products as $product): ?>
        <?php $this->load->view('frontend/zenvello/product_card', array('product' => $product, 'assets' => $assets, 'is_preview' => !empty($is_preview))); ?>
      <?php endforeach; ?>
    <?php endif; ?>
  </div>
  <div id="category-load-status" class="section-sub" style="text-align:center;margin-top:1.5rem;display:none">Loading more…</div>
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
