<?php
$reviewCount = isset($review_summary['count']) ? (int) $review_summary['count'] : 0;
$openTab = isset($open_tab) ? $open_tab : 'description';
$bullets = product_description_bullets($product);
$specs = product_spec_rows($product, isset($cart_product) ? $cart_product : $product, isset($attributes) ? $attributes : array());
$detailsHtml = ec_product_details_html($product);
?>
<section class="pdp-new-tabs" data-pdp-tabs>
  <div class="pdp-new-tabs__nav" role="tablist">
    <button type="button" role="tab" data-pdp-tab="description" aria-selected="<?= $openTab === 'description' ? 'true' : 'false' ?>" class="<?= $openTab === 'description' ? 'is-active' : '' ?>">Description</button>
    <button type="button" role="tab" data-pdp-tab="specifications" aria-selected="<?= $openTab === 'specifications' ? 'true' : 'false' ?>" class="<?= $openTab === 'specifications' ? 'is-active' : '' ?>">Specifications</button>
    <button type="button" role="tab" data-pdp-tab="reviews" aria-selected="<?= $openTab === 'reviews' ? 'true' : 'false' ?>" class="<?= $openTab === 'reviews' ? 'is-active' : '' ?>">Reviews<?= $reviewCount ? ' (' . $reviewCount . ')' : '' ?></button>
    <button type="button" role="tab" data-pdp-tab="faqs" aria-selected="<?= $openTab === 'faqs' ? 'true' : 'false' ?>" class="<?= $openTab === 'faqs' ? 'is-active' : '' ?>">FAQs</button>
  </div>

  <div class="pdp-new-tabs__panel<?= $openTab === 'description' ? ' is-active' : '' ?>" data-pdp-panel="description" id="pdp-tab-description">
    <div class="pdp-new-desc" data-pdp-desc>
      <div class="pdp-new-desc__clip">
        <div class="pdp-new-desc__body">
          <div class="pdp-new-desc__col">
            <h2>Product Description</h2>
            <?php if (!empty($bullets)): ?>
              <ul class="pdp-new-desc__bullets">
                <?php foreach ($bullets as $line): ?>
                  <li><?= htmlspecialchars($line) ?></li>
                <?php endforeach; ?>
              </ul>
            <?php endif; ?>
            <?php if ($detailsHtml !== ''): ?>
              <article class="pdp-new-desc__html"><?= $detailsHtml ?></article>
            <?php elseif (empty($bullets)): ?>
              <p>Details for this product will appear here.</p>
            <?php endif; ?>
          </div>
          <?php if (!empty($main)): ?>
            <figure class="pdp-new-desc__media">
              <?php if (!empty($video_embed)): ?>
                <iframe src="<?= htmlspecialchars($video_embed) ?>" title="Product video" allowfullscreen loading="lazy"></iframe>
              <?php else: ?>
                <img src="<?= htmlspecialchars($main) ?>" alt="<?= htmlspecialchars($product->name) ?>">
              <?php endif; ?>
            </figure>
          <?php endif; ?>
        </div>
      </div>
      <button class="pdp-new-desc__more" type="button" data-pdp-desc-toggle hidden aria-expanded="false">
        <span>Show more</span>
      </button>
    </div>
  </div>

  <div class="pdp-new-tabs__panel<?= $openTab === 'specifications' ? ' is-active' : '' ?>" data-pdp-panel="specifications" id="pdp-tab-specifications">
    <?php if (!empty($specs)): ?>
      <dl class="pdp-new-specs">
        <?php foreach ($specs as $row): ?>
          <div>
            <dt><?= htmlspecialchars($row['label']) ?></dt>
            <dd><?= htmlspecialchars($row['value']) ?></dd>
          </div>
        <?php endforeach; ?>
      </dl>
    <?php else: ?>
      <p class="pdp-new-empty">No specifications have been added for this product yet.</p>
    <?php endif; ?>
  </div>

  <div class="pdp-new-tabs__panel<?= $openTab === 'reviews' ? ' is-active' : '' ?>" data-pdp-panel="reviews" id="pdp-tab-reviews">
    <?php $this->load->view('frontend/shared/product_detail/components/reviews'); ?>
  </div>

  <div class="pdp-new-tabs__panel<?= $openTab === 'faqs' ? ' is-active' : '' ?>" data-pdp-panel="faqs" id="pdp-tab-faqs">
    <?php $this->load->view('frontend/shared/product_detail/components/faqs'); ?>
  </div>
</section>
