<?php
$hero = $assets . 'assets/images/hero/hero-living.jpg';
$promoH = $assets . 'assets/images/banners/promo-halloween.jpg';
$promoC = $assets . 'assets/images/banners/promo-christmas.jpg';
$heroTitle = theme_setting($settings, 'hero_title', store_ui('home.title_smart'));
$heroText = theme_setting($settings, 'hero_subtitle', store_ui('home.text_smart'));
$heroSlides = !empty($hero_slides) ? $hero_slides : array();
?>
<div class="container">
  <section class="hero" data-slider aria-roledescription="carousel" aria-label="<?= e_ui('home.featured_promos') ?>">
    <div class="hero__track" data-slider-track>
      <?php if (!empty($heroSlides)): ?>
        <?php foreach ($heroSlides as $i => $slide): ?>
          <?php
            $slideImg = trim((string) $slide->image) !== '' ? storefront_asset_url($slide->image) : $hero;
            $btnText = trim((string) $slide->btn_text) !== '' ? $slide->btn_text : store_ui('cta.shop_now');
            $btnLink = trim((string) $slide->btn_link);
            if ($btnLink === '') {
                $btnLink = storefront_url('shop');
            } elseif ($btnLink[0] !== '#' && strpos($btnLink, 'http') !== 0 && strpos($btnLink, '//') !== 0) {
                $btnLink = storefront_url(ltrim($btnLink, '/'));
            }
            $extraText = !isset($slide->extra_text) || (int) $slide->extra_text === 1;
            $imageHref = '';
            $rawImageLink = isset($slide->image_link) ? trim((string) $slide->image_link) : '';
            if (!$extraText && $rawImageLink !== '') {
                if ($rawImageLink[0] === '#' || strpos($rawImageLink, 'http') === 0 || strpos($rawImageLink, '//') === 0) {
                    $imageHref = $rawImageLink;
                } else {
                    $imageHref = storefront_url(ltrim($rawImageLink, '/'));
                }
            }
            $slideStyle = trim((string) $slide->slide_bg) !== '' ? '--slide-bg:' . $slide->slide_bg : '';
            $light = (int) $slide->light_text === 1;
            $kickerStyle = trim((string) $slide->kicker_color) !== '' ? 'color:' . $slide->kicker_color : ($light ? 'color:#ffb74d' : '');
            $bodyStyle = $light ? 'color:#fff' : '';
            $titleStyle = $light ? 'color:#fff' : '';
            $textStyle = $light ? 'color:#e4e0dc' : '';
            $heading = $i === 0 ? 'h1' : 'h2';
            $hasDisc = $extraText && (!isset($slide->disc_on) || (int) $slide->disc_on === 1)
                && (trim((string) $slide->disc_small) !== '' || trim((string) $slide->disc_big) !== '' || trim((string) $slide->disc_span) !== '');
            $discStyle = trim((string) $slide->disc_bg) !== '' ? 'background:' . $slide->disc_bg : '';
            $alt = trim(preg_replace('/\s+/', ' ', str_replace(array("\r", "\n"), ' ', (string) $slide->title)));
            if ($alt === '') {
                $alt = $store->name;
            }
          ?>
          <article class="hero__slide<?= $extraText ? '' : ' is-image-only' ?>"<?php if ($slideStyle !== ''): ?> style="<?= htmlspecialchars($slideStyle) ?>"<?php endif; ?>>
            <?php if ($imageHref !== ''): ?>
            <a class="hero__hit" href="<?= htmlspecialchars($imageHref) ?>">
              <img class="hero__bg" src="<?= htmlspecialchars($slideImg) ?>" alt="<?= htmlspecialchars($alt) ?>">
            </a>
            <?php else: ?>
            <img class="hero__bg" src="<?= htmlspecialchars($slideImg) ?>" alt="<?= htmlspecialchars($alt) ?>">
            <?php endif; ?>
            <?php if ($extraText): ?>
            <div class="hero__body"<?php if ($bodyStyle !== ''): ?> style="<?= htmlspecialchars($bodyStyle) ?>"<?php endif; ?>>
              <?php if (trim((string) $slide->kicker) !== ''): ?>
                <p class="hero__kicker"<?php if ($kickerStyle !== ''): ?> style="<?= htmlspecialchars($kickerStyle) ?>"<?php endif; ?>><?= htmlspecialchars($slide->kicker) ?></p>
              <?php endif; ?>
              <<?= $heading ?> class="hero__title"<?php if ($titleStyle !== ''): ?> style="<?= htmlspecialchars($titleStyle) ?>"<?php endif; ?>><?= nl2br(htmlspecialchars($slide->title)) ?></<?= $heading ?>>
              <?php if (trim((string) $slide->text) !== ''): ?>
                <p class="hero__text"<?php if ($textStyle !== ''): ?> style="<?= htmlspecialchars($textStyle) ?>"<?php endif; ?>><?= htmlspecialchars($slide->text) ?></p>
              <?php endif; ?>
              <a class="btn btn--yellow btn--lg" href="<?= htmlspecialchars($btnLink) ?>"><?= htmlspecialchars($btnText) ?> <span class="arrow" aria-hidden="true">&rarr;</span></a>
            </div>
            <?php endif; ?>
            <?php if ($hasDisc): ?>
              <div class="hero__disc" aria-hidden="true"<?php if ($discStyle !== ''): ?> style="<?= htmlspecialchars($discStyle) ?>"<?php endif; ?>>
                <?php if (trim((string) $slide->disc_small) !== ''): ?><small><?= htmlspecialchars($slide->disc_small) ?></small><?php endif; ?>
                <?php if (trim((string) $slide->disc_big) !== ''): ?><b><?= htmlspecialchars($slide->disc_big) ?></b><?php endif; ?>
                <?php if (trim((string) $slide->disc_span) !== ''): ?><span><?= htmlspecialchars($slide->disc_span) ?></span><?php endif; ?>
              </div>
            <?php endif; ?>
          </article>
        <?php endforeach; ?>
      <?php else: ?>
      <article class="hero__slide">
        <img class="hero__bg" src="<?= $hero ?>" alt="<?= htmlspecialchars($store->name) ?>">
        <div class="hero__body">
          <p class="hero__kicker"><?= e_ui('home.kicker_living') ?></p>
          <h1 class="hero__title"><?= nl2br(htmlspecialchars($heroTitle)) ?></h1>
          <p class="hero__text"><?= htmlspecialchars($heroText) ?></p>
          <a class="btn btn--yellow btn--lg" href="<?= storefront_url('shop') ?>"><?= e_ui('cta.shop_now') ?> <span class="arrow" aria-hidden="true">&rarr;</span></a>
        </div>
        <div class="hero__disc" aria-hidden="true">
          <small><?= e_ui('home.disc_upto') ?></small><b>50%</b><span><?= e_ui('home.disc_off') ?></span>
        </div>
      </article>
      <article class="hero__slide" style="--slide-bg:#1b1016">
        <img class="hero__bg" src="<?= $promoH ?>" alt="<?= e_ui('home.seasonal_alt') ?>">
        <div class="hero__body" style="color:#fff">
          <p class="hero__kicker" style="color:#ffb74d"><?= e_ui('home.kicker_season') ?></p>
          <h2 class="hero__title" style="color:#fff"><?= nl2br(e_ui('home.title_fresh')) ?></h2>
          <p class="hero__text" style="color:#e4e0dc"><?= e_ui('home.text_fresh') ?></p>
          <a class="btn btn--yellow btn--lg" href="<?= storefront_url('shop') ?>"><?= e_ui('cta.browse_shop') ?> <span class="arrow" aria-hidden="true">&rarr;</span></a>
        </div>
      </article>
      <article class="hero__slide" style="--slide-bg:#12231b">
        <img class="hero__bg" src="<?= $promoC ?>" alt="<?= e_ui('home.festive_alt') ?>">
        <div class="hero__body" style="color:#fff">
          <p class="hero__kicker" style="color:#8fe0a8"><?= e_ui('home.kicker_value') ?></p>
          <h2 class="hero__title" style="color:#fff"><?= nl2br(e_ui('home.title_smart')) ?></h2>
          <p class="hero__text" style="color:#dfe6e1"><?= e_ui('home.text_smart') ?></p>
          <a class="btn btn--yellow btn--lg" href="<?= storefront_url('shop') ?>"><?= e_ui('cta.shop_deals') ?> <span class="arrow" aria-hidden="true">&rarr;</span></a>
        </div>
      </article>
      <?php endif; ?>
    </div>
    <button class="hero__arrow hero__arrow--prev" type="button" aria-label="<?= e_ui('home.prev_slide') ?>" data-slider-prev>
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m15 5-7 7 7 7"/></svg>
    </button>
    <button class="hero__arrow hero__arrow--next" type="button" aria-label="<?= e_ui('home.next_slide') ?>" data-slider-next>
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m9 5 7 7-7 7"/></svg>
    </button>
    <div class="hero__dots" data-slider-dots></div>
  </section>
</div>

<?php if (!empty($home_categories)): ?>
<section class="container catstrip" aria-label="<?= e_ui('home.shop_by_category') ?>">
  <div class="catstrip__slider" data-cat-slider>
    <button class="catstrip__arrow catstrip__arrow--prev" type="button" aria-label="<?= e_ui('home.prev_slide') ?>" data-cat-prev>
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m15 5-7 7 7 7"/></svg>
    </button>
    <div class="catstrip__viewport" data-cat-track>
      <ul class="circles circles--row">
        <?php foreach ($home_categories as $cat): ?>
          <?php
            $img = !empty($cat->display_image) ? $cat->display_image : (!empty($cat->store_image) ? $cat->store_image : $cat->image);
          ?>
          <li>
            <a class="circle<?= $cat->slug === 'deals' ? ' circle--deals' : '' ?>" href="<?= category_url($cat) ?>">
              <?php if ($img): ?>
                <span class="circle__ring" aria-hidden="true" style="background-image:url('<?= storefront_asset_url($img) ?>');background-size:cover;background-position:center"></span>
              <?php else: ?>
                <?php
                  $iconRaw = trim((string) ($cat->icon ?: ''));
                  $iconShow = ($iconRaw !== '' && !preg_match('/^[a-z0-9_\- ]+$/i', $iconRaw)) ? $iconRaw : '•';
                ?>
                <span class="circle__ring" aria-hidden="true"><?= htmlspecialchars($iconShow) ?></span>
              <?php endif; ?>
              <span class="circle__label"><?= htmlspecialchars(category_store_name($cat)) ?></span>
            </a>
          </li>
        <?php endforeach; ?>
      </ul>
    </div>
    <button class="catstrip__arrow catstrip__arrow--next" type="button" aria-label="<?= e_ui('home.next_slide') ?>" data-cat-next>
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m9 5 7 7-7 7"/></svg>
    </button>
  </div>
</section>
<?php endif; ?>

<section class="container section" aria-labelledby="featured">
  <div class="section-head">
    <div>
      <h2 class="section-title" id="featured"><?= e_ui('home.featured') ?></h2>
      <p class="section-sub"><?= e_ui('home.handpicked') ?></p>
    </div>
    <a class="link-more" href="<?= storefront_url('shop') ?>"><?= e_ui('home.view_all') ?> <span aria-hidden="true">&rarr;</span></a>
  </div>
  <div class="grid-6">
    <?php if (empty($products)): ?>
      <p class="section-sub"><?= e_ui('home.no_products') ?></p>
    <?php else: ?>
      <?php foreach ($products as $product): ?>
        <?php $this->load->view('frontend/zenvello/product_card', array('product' => $product, 'assets' => $assets, 'is_preview' => !empty($is_preview))); ?>
      <?php endforeach; ?>
    <?php endif; ?>
  </div>
</section>

<section class="container section section--tight" aria-label="<?= e_ui('home.why_shop') ?>">
  <div class="trustbar">
    <div class="trust">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M1 4h14v11H1z"/><path d="M15 8h4l4 4v3h-8z"/><circle cx="5.5" cy="18" r="2.2"/><circle cx="18" cy="18" r="2.2"/></svg>
      <div><p class="trust__t"><?= e_ui('trust.free_shipping') ?></p><p class="trust__s"><?= e_ui('trust.free_shipping_text') ?></p></div>
    </div>
    <div class="trust">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 2.5 20 6v6.2c0 4.7-3.3 7.9-8 9.3-4.7-1.4-8-4.6-8-9.3V6Z"/><path d="m8.8 12 2.2 2.2 4.2-4.4"/></svg>
      <div><p class="trust__t"><?= e_ui('trust.secure') ?></p><p class="trust__s"><?= e_ui('trust.secure_text') ?></p></div>
    </div>
    <div class="trust">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M21 8 12 3 3 8l9 5 9-5Z"/><path d="M3 8v8l9 5 9-5V8"/><path d="M12 13v8"/></svg>
      <div><p class="trust__t"><?= e_ui('trust.returns') ?></p><p class="trust__s"><?= e_ui('trust.returns_text') ?></p></div>
    </div>
    <div class="trust">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 13v-1a8 8 0 0 1 16 0v1"/><path d="M4 13h2.6v6H5a1 1 0 0 1-1-1Z"/><path d="M20 13h-2.6v6H19a1 1 0 0 0 1-1Z"/><path d="M17.4 19a4 4 0 0 1-4 3h-1.2"/></svg>
      <div><p class="trust__t"><?= e_ui('trust.support') ?></p><p class="trust__s"><?= e_ui('trust.support_text') ?></p></div>
    </div>
  </div>
</section>

<section class="container promo-section" aria-label="<?= e_ui('home.seasonal') ?>">
  <?php
    $bannerHref = function ($link) {
        $link = trim((string) $link);
        if ($link === '') {
            return storefront_url('shop');
        }
        if ($link[0] === '#' || strpos($link, 'http') === 0 || strpos($link, '//') === 0) {
            return $link;
        }
        return storefront_url(ltrim($link, '/'));
    };
    $b1img = theme_setting($settings, 'banner_1_image', '');
    $b1img = $b1img !== '' ? storefront_asset_url($b1img) : $promoH;
    $b2img = theme_setting($settings, 'banner_2_image', '');
    $b2img = $b2img !== '' ? storefront_asset_url($b2img) : $promoC;
  ?>
  <div class="promos">
    <article class="promo">
      <img src="<?= htmlspecialchars($b1img) ?>" alt="<?= htmlspecialchars(theme_setting($settings, 'banner_1_title', store_ui('home.top_picks'))) ?>">
      <div class="promo__body">
        <p class="promo__kicker"><?= htmlspecialchars(theme_setting($settings, 'banner_1_kicker', store_ui('home.explore'))) ?></p>
        <h2 class="promo__title"><?= htmlspecialchars(theme_setting($settings, 'banner_1_title', store_ui('home.top_picks'))) ?></h2>
        <p class="promo__sub"><?= htmlspecialchars(theme_setting($settings, 'banner_1_text', store_ui('home.top_picks_text'))) ?></p>
        <a class="btn btn--yellow" href="<?= htmlspecialchars($bannerHref(theme_setting($settings, 'banner_1_btn_link', 'shop'))) ?>"><?= htmlspecialchars(theme_setting($settings, 'banner_1_btn_text', store_ui('cta.shop_now'))) ?> <span class="arrow" aria-hidden="true">&rarr;</span></a>
      </div>
    </article>
    <article class="promo">
      <img src="<?= htmlspecialchars($b2img) ?>" alt="<?= htmlspecialchars(theme_setting($settings, 'banner_2_title', store_ui('home.gifts'))) ?>">
      <div class="promo__body">
        <p class="promo__kicker"><?= htmlspecialchars(theme_setting($settings, 'banner_2_kicker', store_ui('home.gifts_kicker'))) ?></p>
        <h2 class="promo__title"><?= htmlspecialchars(theme_setting($settings, 'banner_2_title', store_ui('home.gifts'))) ?></h2>
        <p class="promo__sub"><?= htmlspecialchars(theme_setting($settings, 'banner_2_text', store_ui('home.gifts_text'))) ?></p>
        <a class="btn btn--yellow" href="<?= htmlspecialchars($bannerHref(theme_setting($settings, 'banner_2_btn_link', 'shop'))) ?>"><?= htmlspecialchars(theme_setting($settings, 'banner_2_btn_text', store_ui('cta.browse_gifts'))) ?> <span class="arrow" aria-hidden="true">&rarr;</span></a>
      </div>
    </article>
  </div>
</section>
