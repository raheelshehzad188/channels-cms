<?php
$hero = $assets . 'assets/images/hero/hero-living.jpg';
$promoH = $assets . 'assets/images/banners/promo-halloween.jpg';
$promoC = $assets . 'assets/images/banners/promo-christmas.jpg';
$heroTitle = theme_setting($settings, 'hero_title', "Make Your Home\nFeel Like You");
$heroText = theme_setting($settings, 'hero_subtitle', 'Discover smart, stylish and affordable products for a better everyday life.');
?>
<div class="container">
  <section class="hero" data-slider aria-roledescription="carousel" aria-label="Featured promotions">
    <div class="hero__track" data-slider-track>
      <article class="hero__slide">
        <img class="hero__bg" src="<?= $hero ?>" alt="<?= htmlspecialchars($store->name) ?>">
        <div class="hero__body">
          <p class="hero__kicker">Modern Living</p>
          <h1 class="hero__title"><?= nl2br(htmlspecialchars($heroTitle)) ?></h1>
          <p class="hero__text"><?= htmlspecialchars($heroText) ?></p>
          <a class="btn btn--yellow btn--lg" href="<?= storefront_url('shop') ?>">Shop Now <span class="arrow" aria-hidden="true">&rarr;</span></a>
        </div>
        <div class="hero__disc" aria-hidden="true">
          <small>UP TO</small><b>50%</b><span>OFF</span>
        </div>
      </article>
      <article class="hero__slide" style="--slide-bg:#1b1016">
        <img class="hero__bg" src="<?= $promoH ?>" alt="Seasonal promo">
        <div class="hero__body" style="color:#fff">
          <p class="hero__kicker" style="color:#ffb74d">New Season</p>
          <h2 class="hero__title" style="color:#fff">Fresh Picks<br>For You</h2>
          <p class="hero__text" style="color:#e4e0dc">Explore the latest products curated for <?= htmlspecialchars($store->name) ?>.</p>
          <a class="btn btn--yellow btn--lg" href="<?= storefront_url('shop') ?>">Browse Shop <span class="arrow" aria-hidden="true">&rarr;</span></a>
        </div>
      </article>
      <article class="hero__slide" style="--slide-bg:#12231b">
        <img class="hero__bg" src="<?= $promoC ?>" alt="Festive promo">
        <div class="hero__body" style="color:#fff">
          <p class="hero__kicker" style="color:#8fe0a8">Best Value</p>
          <h2 class="hero__title" style="color:#fff">Shop Smart<br>Live Better</h2>
          <p class="hero__text" style="color:#dfe6e1">Quality products at prices you'll love.</p>
          <a class="btn btn--yellow btn--lg" href="<?= storefront_url('shop') ?>">Shop Deals <span class="arrow" aria-hidden="true">&rarr;</span></a>
        </div>
      </article>
    </div>
    <button class="hero__arrow hero__arrow--prev" type="button" aria-label="Previous slide" data-slider-prev>
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m15 5-7 7 7 7"/></svg>
    </button>
    <button class="hero__arrow hero__arrow--next" type="button" aria-label="Next slide" data-slider-next>
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m9 5 7 7-7 7"/></svg>
    </button>
    <div class="hero__dots" data-slider-dots></div>
  </section>
</div>

<?php if (!empty($home_categories)): ?>
<section class="container catstrip" aria-label="Shop by category">
  <ul class="circles">
    <?php foreach ($home_categories as $cat): ?>
      <?php
        $img = !empty($cat->display_image) ? $cat->display_image : (!empty($cat->store_image) ? $cat->store_image : $cat->image);
      ?>
      <li>
        <a class="circle<?= $cat->slug === 'deals' ? ' circle--deals' : '' ?>" href="<?= storefront_url('category/' . rawurlencode($cat->slug)) ?>">
          <?php if ($img): ?>
            <span class="circle__ring" aria-hidden="true" style="background-image:url('<?= base_url($img) ?>');background-size:cover;background-position:center"></span>
          <?php else: ?>
            <span class="circle__ring" aria-hidden="true"><?= htmlspecialchars($cat->icon ?: '•') ?></span>
          <?php endif; ?>
          <span class="circle__label"><?= htmlspecialchars($cat->name) ?></span>
        </a>
      </li>
    <?php endforeach; ?>
  </ul>
</section>
<?php endif; ?>

<section class="container section" aria-labelledby="featured">
  <div class="section-head">
    <div>
      <h2 class="section-title" id="featured">Featured Products</h2>
      <p class="section-sub">Handpicked for <?= htmlspecialchars($store->name) ?></p>
    </div>
    <a class="link-more" href="<?= storefront_url('shop') ?>">View All Products <span aria-hidden="true">&rarr;</span></a>
  </div>
  <div class="grid-6">
    <?php if (empty($products)): ?>
      <p class="section-sub">No products available yet.</p>
    <?php else: ?>
      <?php foreach ($products as $product): ?>
        <?php $this->load->view('frontend/zenvello/product_card', array('product' => $product, 'assets' => $assets, 'is_preview' => !empty($is_preview))); ?>
      <?php endforeach; ?>
    <?php endif; ?>
  </div>
</section>

<section class="container section section--tight" aria-label="Why shop with us">
  <div class="trustbar">
    <div class="trust">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M1 4h14v11H1z"/><path d="M15 8h4l4 4v3h-8z"/><circle cx="5.5" cy="18" r="2.2"/><circle cx="18" cy="18" r="2.2"/></svg>
      <div><p class="trust__t">Free Shipping</p><p class="trust__s">On qualifying orders</p></div>
    </div>
    <div class="trust">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 2.5 20 6v6.2c0 4.7-3.3 7.9-8 9.3-4.7-1.4-8-4.6-8-9.3V6Z"/><path d="m8.8 12 2.2 2.2 4.2-4.4"/></svg>
      <div><p class="trust__t">Secure Payment</p><p class="trust__s">100% secure checkout</p></div>
    </div>
    <div class="trust">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M21 8 12 3 3 8l9 5 9-5Z"/><path d="M3 8v8l9 5 9-5V8"/><path d="M12 13v8"/></svg>
      <div><p class="trust__t">Easy Returns</p><p class="trust__s">Hassle-free policy</p></div>
    </div>
    <div class="trust">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 13v-1a8 8 0 0 1 16 0v1"/><path d="M4 13h2.6v6H5a1 1 0 0 1-1-1Z"/><path d="M20 13h-2.6v6H19a1 1 0 0 0 1-1Z"/><path d="M17.4 19a4 4 0 0 1-4 3h-1.2"/></svg>
      <div><p class="trust__t">24/7 Support</p><p class="trust__s">We're here to help</p></div>
    </div>
  </div>
</section>

<section class="container promo-section" aria-label="Seasonal shops">
  <div class="promos">
    <article class="promo">
      <img src="<?= $promoH ?>" alt="Seasonal collection">
      <div class="promo__body">
        <p class="promo__kicker">Explore</p>
        <h2 class="promo__title">Top Picks</h2>
        <p class="promo__sub">Curated essentials &amp; more</p>
        <a class="btn btn--yellow" href="<?= storefront_url('shop') ?>">Shop Now <span class="arrow" aria-hidden="true">&rarr;</span></a>
      </div>
    </article>
    <article class="promo">
      <img src="<?= $promoC ?>" alt="Gift ideas">
      <div class="promo__body">
        <p class="promo__kicker">Make It Special</p>
        <h2 class="promo__title">Gift Ideas</h2>
        <p class="promo__sub">Finds the whole family will love</p>
        <a class="btn btn--yellow" href="<?= storefront_url('shop') ?>">Browse Gifts <span class="arrow" aria-hidden="true">&rarr;</span></a>
      </div>
    </article>
  </div>
</section>
