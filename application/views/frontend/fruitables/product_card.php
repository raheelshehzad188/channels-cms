<?php
$cardCol = isset($card_col) ? $card_col : 'col-md-6 col-lg-4 col-xl-3';
$itemClass = isset($card_item_class) ? $card_item_class : 'fruite-item';
$imgWrapClass = isset($card_img_class) ? $card_img_class : 'fruite-img';
$badge = isset($card_badge) ? $card_badge : (!empty($product->supplier_name) ? $product->supplier_name : 'Fresh');
$badgeClass = isset($card_badge_class) ? $card_badge_class : 'bg-secondary';
$badgePos = isset($card_badge_pos) ? $card_badge_pos : 'top: 10px; left: 10px;';
$skipCol = !empty($card_skip_col);
$image = !empty($product->image) ? base_url($product->image) : $assets . 'img/fruite-item-5.jpg';
$excerpt = !empty($product->description)
    ? $product->description
    : 'Fresh organic product selected for ' . (isset($store->name) ? $store->name : 'our store') . '.';
$detailUrl = !empty($is_preview)
    ? base_url('admin/products/preview/' . $product->id . '/fruitables')
    : product_url($product);
$cartUrl = !empty($is_preview) ? $detailUrl : storefront_url('cart/add/' . $product->id);
?>
<?php if (!$skipCol): ?>
<div class="<?= htmlspecialchars($cardCol) ?>">
<?php endif; ?>
    <div class="rounded position-relative <?= htmlspecialchars($itemClass) ?>">
        <div class="<?= htmlspecialchars($imgWrapClass) ?>">
            <a href="<?= $detailUrl ?>"><img src="<?= htmlspecialchars($image) ?>" class="img-fluid w-100 rounded-top" alt="<?= htmlspecialchars($product->name) ?>"></a>
        </div>
        <div class="text-white <?= htmlspecialchars($badgeClass) ?> px-3 py-1 rounded position-absolute" style="<?= htmlspecialchars($badgePos) ?>"><?= htmlspecialchars($badge) ?></div>
        <div class="p-4 border border-secondary border-top-0 rounded-bottom">
            <h4><a href="<?= $detailUrl ?>" class="text-dark"><?= htmlspecialchars($product->name) ?></a></h4>
            <p><?php $plain = strip_tags($excerpt); echo htmlspecialchars(strlen($plain) > 90 ? substr($plain, 0, 87) . '...' : $plain); ?></p>
            <div class="d-flex justify-content-between flex-lg-wrap">
                <p class="text-dark fs-5 fw-bold mb-0"><?= format_money((float) $product->price) ?></p>
                <a href="<?= $cartUrl ?>" class="btn border border-secondary rounded-pill px-3 text-primary"><i class="fa fa-shopping-bag me-2 text-primary"></i> Add to cart</a>
            </div>
        </div>
    </div>
<?php if (!$skipCol): ?>
</div>
<?php endif; ?>
