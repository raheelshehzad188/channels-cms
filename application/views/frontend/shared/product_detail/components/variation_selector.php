<?php
$children = isset($child_products) ? $child_products : array();
if (empty($children)) {
    return;
}
$cartProduct = isset($cart_product) && $cart_product ? $cart_product : $product;
$selectedId = (int) $cartProduct->id;
$placeholder = isset($assets) ? $assets . 'assets/images/products/mask-main.jpg' : '';
$shipCount = 0;
foreach ($children as $child) {
    if (product_ship_option_label($child) !== '') {
        $shipCount++;
    }
}
$heading = ($shipCount === count($children)) ? 'Choose Delivery' : 'Choose Your Pack';
?>
<div class="pdp-new-packs" data-pdp-packs>
  <h2 class="pdp-new-packs__title"><?= htmlspecialchars($heading) ?></h2>
  <div class="pdp-new-packs__grid" role="radiogroup" aria-label="Product options">
    <?php foreach ($children as $child): ?>
      <?php
      $isSelected = (int) $child->id === $selectedId;
      $img = !empty($child->image) ? product_image_url($child->image) : $placeholder;
      $shipLabel = product_ship_option_label($child);
      $label = $shipLabel !== '' ? $shipLabel : product_option_label($child, $product);
      $childCompare = isset($child->compare_price) ? (float) $child->compare_price : 0;
      $childStock = (int) $child->stock;
      $window = product_delivery_window($child);
      $desc = '';
      if ($window) {
          $desc = 'Arrives ' . $window['short'];
      } else {
          $desc = trim(strip_tags((string) $child->description));
          if ($desc === '') {
              $desc = !empty($child->sku) ? 'SKU: ' . $child->sku : '';
          } else {
              $desc = strtok($desc, ".\n");
          }
      }
      ?>
      <label class="pdp-new-pack<?= $isSelected ? ' is-selected' : '' ?><?= $childStock < 1 ? ' is-oos' : '' ?>">
        <input type="radio" name="pdp_variation" value="<?= (int) $child->id ?>" <?= $isSelected ? 'checked' : '' ?> data-pdp-variation>
        <span class="pdp-new-pack__radio" aria-hidden="true"></span>
        <?php if ($img !== ''): ?>
          <span class="pdp-new-pack__img"><img src="<?= htmlspecialchars($img) ?>" alt=""></span>
        <?php endif; ?>
        <span class="pdp-new-pack__body">
          <span class="pdp-new-pack__name"><?= htmlspecialchars($label) ?></span>
          <?php if ($desc !== ''): ?>
            <span class="pdp-new-pack__desc"><?= htmlspecialchars($desc) ?></span>
          <?php endif; ?>
          <span class="pdp-new-pack__price"><?= format_money((float) $child->price) ?></span>
          <?php if ($childCompare > (float) $child->price): ?>
            <span class="pdp-new-pack__was"><?= format_money($childCompare) ?></span>
          <?php endif; ?>
          <span class="pdp-new-pack__stock"><?= $childStock > 0 ? 'In stock' : 'Out of stock' ?></span>
        </span>
      </label>
    <?php endforeach; ?>
  </div>
</div>
