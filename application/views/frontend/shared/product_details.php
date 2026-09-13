<?php
$html = ec_product_details_html(isset($product) ? $product : null);
$fallback = isset($fallback) ? $fallback : '';
?>
<?php if ($html !== ''): ?>
<div class="product-html"><?= $html ?></div>
<?php elseif ($fallback !== ''): ?>
<p><?= nl2br(htmlspecialchars($fallback)) ?></p>
<?php endif; ?>
