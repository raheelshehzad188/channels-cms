<?php
if (!function_exists('storefront_admin_can_see_pricing') || !storefront_admin_can_see_pricing()) {
    return;
}
$listing = isset($listing) ? $listing : (isset($product) ? $product : null);
$storeObj = isset($store) ? $store : null;
$meta = function_exists('storefront_admin_card_meta')
    ? storefront_admin_card_meta($listing, $storeObj)
    : null;
if (!$meta) {
    return;
}
if (empty($GLOBALS['ec_admin_card_meta_css'])) {
    $GLOBALS['ec_admin_card_meta_css'] = 1;
    ?>
<style>
.ec-card-meta{
  margin:6px 0 8px;padding:7px 8px;border-radius:6px;
  background:#1d2327;color:#f0f0f1;
  font:11px/1.35 -apple-system,BlinkMacSystemFont,"Segoe UI",sans-serif;
}
.ec-card-meta__row{display:flex;justify-content:space-between;gap:8px;margin:2px 0;}
.ec-card-meta__row dt{margin:0;opacity:.78;font-weight:500;}
.ec-card-meta__row dd{margin:0;font-variant-numeric:tabular-nums;font-weight:700;white-space:nowrap;}
.ec-card-meta__row.is-up dd{color:#68de7c;}
.ec-card-meta__row.is-down dd{color:#ff8085;}
.ec-card-meta__source{
  display:block;margin-top:7px;padding:6px 8px;border-radius:4px;
  background:#2271b1;color:#fff !important;text-align:center;text-decoration:none;
  font-weight:700;letter-spacing:.02em;
}
.ec-card-meta__source:hover{background:#135e96;color:#fff !important;}
</style>
    <?php
}
$marginClass = $meta['margin'] > 0 ? ' is-up' : ($meta['margin'] < 0 ? ' is-down' : '');
$diffClass = $meta['diff'] > 0 ? ' is-up' : ($meta['diff'] < 0 ? ' is-down' : '');
?>
<dl class="ec-card-meta">
  <div class="ec-card-meta__row">
    <dt>Delivery</dt>
    <dd><?= $meta['days'] !== '' ? htmlspecialchars($meta['days']) : '—' ?></dd>
  </div>
  <div class="ec-card-meta__row">
    <dt>Cost</dt>
    <dd><?= htmlspecialchars($meta['cost_formatted']) ?></dd>
  </div>
  <div class="ec-card-meta__row<?= $marginClass ?>">
    <dt>Per item margin</dt>
    <dd><?= htmlspecialchars($meta['margin_formatted']) ?></dd>
  </div>
  <div class="ec-card-meta__row<?= $diffClass ?>">
    <dt>Difference</dt>
    <dd><?= htmlspecialchars($meta['diff_formatted']) ?></dd>
  </div>
  <?php if (!empty($meta['source_url'])): ?>
    <a class="ec-card-meta__source"
      href="<?= htmlspecialchars($meta['source_url']) ?>"
      target="_blank"
      rel="noopener noreferrer"
      onclick="event.stopPropagation();">Source</a>
  <?php endif; ?>
</dl>
