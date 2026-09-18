<?php
$summary = isset($review_summary) ? $review_summary : array('count' => 0, 'average' => 0, 'breakdown' => array(5 => 0, 4 => 0, 3 => 0, 2 => 0, 1 => 0));
$count = (int) $summary['count'];
$average = (float) $summary['average'];
$breakdown = $summary['breakdown'];
$reviews = isset($product_reviews) ? $product_reviews : array();
$page = isset($review_page) ? (int) $review_page : 1;
$pages = isset($review_pages) ? (int) $review_pages : 1;
$customer = isset($customer) ? $customer : storefront_customer();
?>
<div class="pdp-new-reviews">
  <div class="pdp-new-reviews__summary">
    <h2>Customer Reviews</h2>
    <div class="pdp-new-reviews__score">
      <strong><?= $count ? htmlspecialchars(number_format($average, 1)) : '—' ?></strong>
      <?= product_star_html($average) ?>
      <p>Based on <?= (int) $count ?> review<?= $count === 1 ? '' : 's' ?></p>
    </div>
    <ul class="pdp-new-reviews__bars">
      <?php for ($i = 5; $i >= 1; $i--): ?>
        <?php $n = isset($breakdown[$i]) ? (int) $breakdown[$i] : 0; $pct = $count > 0 ? round(($n / $count) * 100) : 0; ?>
        <li>
          <span><?= $i ?> ★</span>
          <span class="pdp-new-reviews__bar"><i style="width:<?= $pct ?>%"></i></span>
          <em><?= $n ?></em>
        </li>
      <?php endfor; ?>
    </ul>
    <?php if (empty($is_preview)): ?>
      <?php if ($customer): ?>
        <details class="pdp-new-review-form">
          <summary>Write a Review</summary>
          <form method="post" action="<?= storefront_url('product-review/' . (int) $product->id) ?>">
            <label>Your name
              <input type="text" name="customer_name" required maxlength="150" value="<?= htmlspecialchars($customer->name) ?>">
            </label>
            <label>Rating
              <select name="rating">
                <?php for ($i = 5; $i >= 1; $i--): ?>
                  <option value="<?= $i ?>" <?= $i === 5 ? 'selected' : '' ?>><?= $i ?> star<?= $i === 1 ? '' : 's' ?></option>
                <?php endfor; ?>
              </select>
            </label>
            <label>Title <input type="text" name="title" maxlength="255"></label>
            <label>Review <textarea name="content" rows="4" required></textarea></label>
            <button class="pdp-new-btn pdp-new-btn--cart" type="submit">Submit review</button>
          </form>
        </details>
      <?php else: ?>
        <a class="pdp-new-btn pdp-new-btn--cart" href="<?= storefront_url('account/login') ?>">Write a Review</a>
      <?php endif; ?>
    <?php endif; ?>
  </div>

  <div class="pdp-new-reviews__list">
    <?php if (empty($reviews)): ?>
      <p class="pdp-new-empty">No reviews yet.</p>
    <?php else: ?>
      <?php foreach ($reviews as $review): ?>
        <article class="pdp-new-review">
          <header>
            <b><?= htmlspecialchars($review->customer_name) ?></b>
            <?= product_star_html((int) $review->rating) ?>
            <time datetime="<?= htmlspecialchars($review->created_at) ?>"><?= htmlspecialchars(date('j M Y', strtotime($review->created_at))) ?></time>
          </header>
          <?php if ($review->title !== ''): ?>
            <h3><?= htmlspecialchars($review->title) ?></h3>
          <?php endif; ?>
          <p><?= nl2br(htmlspecialchars($review->content)) ?></p>
        </article>
      <?php endforeach; ?>
      <?php if ($pages > 1): ?>
        <nav class="pdp-new-pager" aria-label="Reviews pages">
          <?php for ($i = 1; $i <= $pages; $i++): ?>
            <a class="<?= $i === $page ? 'is-active' : '' ?>" href="<?= htmlspecialchars(product_url($product) . '?tab=reviews&reviews_page=' . $i) ?>"><?= $i ?></a>
          <?php endfor; ?>
        </nav>
      <?php endif; ?>
    <?php endif; ?>
  </div>
</div>
