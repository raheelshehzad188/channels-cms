<?php
$gallery = isset($gallery) ? $gallery : array();
$main = isset($main) ? $main : '';
$hasThumbs = count($gallery) > 1;
$videoEmbed = isset($video_embed) ? $video_embed : '';
?>
<div class="pdp-new-gallery<?= $hasThumbs ? ' has-thumbs' : '' ?>" data-pdp-gallery>
  <div class="pdp-new-gallery__stage">
    <?php if ($videoEmbed !== ''): ?>
      <button class="pdp-new-gallery__video" type="button" data-pdp-video="<?= htmlspecialchars($videoEmbed) ?>"><?= e_ui('product.watch_video') ?></button>
    <?php endif; ?>
    <img src="<?= htmlspecialchars($main) ?>" alt="<?= htmlspecialchars($product->name) ?>" data-pdp-main onerror="this.removeAttribute('src'); this.style.visibility='hidden';">
    <button class="pdp-new-gallery__nav pdp-new-gallery__nav--prev" type="button" data-pdp-prev aria-label="<?= e_ui('product.prev_image') ?>"<?= $hasThumbs ? '' : ' hidden' ?>>
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M15 5l-7 7 7 7"/></svg>
    </button>
    <button class="pdp-new-gallery__nav pdp-new-gallery__nav--next" type="button" data-pdp-next aria-label="<?= e_ui('product.next_image') ?>"<?= $hasThumbs ? '' : ' hidden' ?>>
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 5l7 7-7 7"/></svg>
    </button>
    <button class="pdp-new-gallery__zoom" type="button" aria-label="<?= e_ui('product.zoom') ?>" aria-pressed="false" data-pdp-zoom>
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5M11 8.5v5M8.5 11h5"/></svg>
    </button>
  </div>
  <div class="pdp-new-gallery__thumbs" data-pdp-thumbs<?= $hasThumbs ? '' : ' hidden' ?>>
    <?php foreach ($gallery as $i => $src): ?>
      <button class="pdp-new-gallery__thumb<?= $i === 0 ? ' is-active' : '' ?>" type="button" data-pdp-thumb aria-label="<?= e_ui('product.show_image', array('{n}' => $i + 1)) ?>">
        <img src="<?= htmlspecialchars($src) ?>" data-full="<?= htmlspecialchars($src) ?>" alt="<?= htmlspecialchars($product->name) ?>" onerror="var t=this.closest('[data-pdp-thumb]'); if(t) t.remove();">
      </button>
    <?php endforeach; ?>
  </div>
</div>
