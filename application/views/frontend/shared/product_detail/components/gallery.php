<?php
$gallery = isset($gallery) ? $gallery : array();
$main = isset($main) ? $main : '';
$hasThumbs = count($gallery) > 1;
$videoEmbed = isset($video_embed) ? $video_embed : '';
?>
<div class="pdp-new-gallery<?= $hasThumbs ? ' has-thumbs' : '' ?>" data-pdp-gallery>
  <div class="pdp-new-gallery__stage">
    <?php if ($videoEmbed !== ''): ?>
      <button class="pdp-new-gallery__video" type="button" data-pdp-video="<?= htmlspecialchars($videoEmbed) ?>">Watch Video</button>
    <?php endif; ?>
    <img src="<?= htmlspecialchars($main) ?>" alt="<?= htmlspecialchars($product->name) ?>" data-pdp-main>
    <?php if ($hasThumbs): ?>
    <button class="pdp-new-gallery__nav pdp-new-gallery__nav--prev" type="button" data-pdp-prev aria-label="Previous image">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M15 5l-7 7 7 7"/></svg>
    </button>
    <button class="pdp-new-gallery__nav pdp-new-gallery__nav--next" type="button" data-pdp-next aria-label="Next image">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 5l7 7-7 7"/></svg>
    </button>
    <?php endif; ?>
    <button class="pdp-new-gallery__zoom" type="button" aria-label="Zoom image" aria-pressed="false" data-pdp-zoom>
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5M11 8.5v5M8.5 11h5"/></svg>
    </button>
  </div>
  <?php if ($hasThumbs): ?>
  <div class="pdp-new-gallery__thumbs" data-pdp-thumbs>
    <?php foreach ($gallery as $i => $src): ?>
      <button class="pdp-new-gallery__thumb<?= $i === 0 ? ' is-active' : '' ?>" type="button" data-pdp-thumb aria-label="Show image <?= $i + 1 ?>">
        <img src="<?= htmlspecialchars($src) ?>" data-full="<?= htmlspecialchars($src) ?>" alt="<?= htmlspecialchars($product->name) ?>">
      </button>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>
</div>
