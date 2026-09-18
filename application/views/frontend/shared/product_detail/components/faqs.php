<?php $faqs = isset($product_faqs) ? $product_faqs : array(); ?>
<div class="pdp-new-faqs">
  <h2>Frequently Asked Questions</h2>
  <?php if (empty($faqs)): ?>
    <p class="pdp-new-empty">No FAQs have been added for this product yet.</p>
  <?php else: ?>
    <div class="pdp-new-acc" data-pdp-acc>
      <?php foreach ($faqs as $i => $faq): ?>
        <div class="pdp-new-acc__item<?= $i === 0 ? ' is-open' : '' ?>">
          <button type="button" class="pdp-new-acc__q" aria-expanded="<?= $i === 0 ? 'true' : 'false' ?>">
            <span><?= htmlspecialchars($faq->question) ?></span>
            <i aria-hidden="true">+</i>
          </button>
          <div class="pdp-new-acc__a"<?= $i === 0 ? '' : ' hidden' ?>>
            <p><?= nl2br(htmlspecialchars($faq->answer)) ?></p>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</div>
