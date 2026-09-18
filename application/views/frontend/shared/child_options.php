<?php
$children = isset($child_products) ? $child_products : array();
if (empty($children)) {
    return;
}
$cartProduct = isset($cart_product) && $cart_product ? $cart_product : $product;
$selectedId = (int) $cartProduct->id;
$isPreview = !empty($is_preview);
$themeSlug = (isset($theme) && !empty($theme->slug)) ? $theme->slug : '';
$placeholder = isset($assets) ? $assets . 'assets/images/products/mask-main.jpg' : '';
?>
<div class="pdp-children" role="list">
  <?php foreach ($children as $child): ?>
    <?php
    $isSelected = (int) $child->id === $selectedId;
    $img = !empty($child->image) ? product_image_url($child->image) : $placeholder;
    $href = product_option_url($child, $isPreview, $themeSlug);
    $shipLabel = function_exists('product_ship_option_label') ? product_ship_option_label($child) : '';
    $label = $shipLabel !== '' ? $shipLabel : (function_exists('product_option_label') ? product_option_label($child, $product) : trim((string) $child->name));
    ?>
    <a class="pdp-child<?= $isSelected ? ' is-selected' : '' ?>" href="<?= htmlspecialchars($href) ?>" role="listitem"<?= $isSelected ? ' aria-current="true"' : '' ?>>
      <?php if ($img !== ''): ?>
        <span class="pdp-child__img"><img src="<?= htmlspecialchars($img) ?>" alt=""></span>
      <?php endif; ?>
      <span class="pdp-child__name"><?= htmlspecialchars($label) ?></span>
      <span class="pdp-child__price"><?= format_money((float) $child->price) ?></span>
    </a>
  <?php endforeach; ?>
</div>
