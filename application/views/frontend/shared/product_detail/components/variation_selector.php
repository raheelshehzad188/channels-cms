<?php
$children = isset($child_products) ? $child_products : array();
if (empty($children)) {
    return;
}
$cartProduct = isset($cart_product) && $cart_product ? $cart_product : $product;
$selectedId = (int) $cartProduct->id;
$placeholder = isset($assets) ? $assets . 'assets/images/products/mask-main.jpg' : '';
$shipCount = 0;
$named = 0;
foreach ($children as $child) {
    if (product_ship_option_label($child) !== '') {
        $shipCount++;
    }
    $childName = trim((string) $child->name);
    if ($childName !== '') {
        $named++;
    }
}
$heading = function_exists('product_options_title')
    ? product_options_title($product)
    : (isset($product->options_title) ? trim((string) $product->options_title) : '');
if ($heading === '') {
    $heading = ($named > 0 || $shipCount !== count($children))
        ? store_ui('product.choose_pack')
        : store_ui('product.choose_delivery');
}
$axes = function_exists('product_attribute_axes') ? product_attribute_axes($product->id) : array();
$childAttrMaps = array();
$axisComplete = count($axes) >= 2;
$showAdminPricing = function_exists('storefront_admin_can_see_pricing') && storefront_admin_can_see_pricing();
if ($axisComplete) {
    foreach ($children as $child) {
        $mapped = product_child_attr_map($child, $product, $axes);
        if (count($mapped) !== count($axes)) {
            $axisComplete = false;
            break;
        }
        $childAttrMaps[(int) $child->id] = $mapped;
    }
}
$swatchImages = array();
$displayLabels = function_exists('product_option_display_labels')
    ? product_option_display_labels($children, $product)
    : array();
if ($axisComplete && !empty($axes[0]['name'])) {
    $colorAxis = $axes[0]['name'];
    foreach ($children as $child) {
        $mapped = $childAttrMaps[(int) $child->id];
        $colorVal = isset($mapped[$colorAxis]) ? $mapped[$colorAxis] : '';
        if ($colorVal === '' || isset($swatchImages[$colorVal])) {
            continue;
        }
        $swatchImages[$colorVal] = !empty($child->image) ? product_image_url($child->image) : '';
    }
}
?>
<?php if ($axisComplete): ?>
<div class="pdp-new-axes" data-pdp-axes>
  <?php foreach ($axes as $axisIndex => $axis): ?>
    <?php $axisName = $axis['name']; ?>
    <div class="pdp-new-axis" data-pdp-axis="<?= htmlspecialchars($axisName) ?>">
      <h2 class="pdp-new-axis__title"><?= htmlspecialchars($axisName) ?></h2>
      <div class="pdp-new-axis__row" role="listbox" aria-label="<?= htmlspecialchars($axisName) ?>">
        <?php foreach ($axis['values'] as $value): ?>
          <?php
          $isColor = ($axisIndex === 0 && !empty($swatchImages[$value]));
          $img = $isColor ? $swatchImages[$value] : '';
          $selectedChildMap = isset($childAttrMaps[$selectedId]) ? $childAttrMaps[$selectedId] : array();
          $isOn = isset($selectedChildMap[$axisName]) && $selectedChildMap[$axisName] === $value;
          $valueLabel = function_exists('product_option_compact_text')
              ? product_option_compact_text($value, $product)
              : $value;
          if ($valueLabel === '') {
              $valueLabel = $value;
          }
          ?>
          <button type="button"
            class="pdp-new-swatch<?= $isColor ? ' pdp-new-swatch--img' : '' ?><?= $isOn ? ' is-selected' : '' ?>"
            data-pdp-axis-value="<?= htmlspecialchars($value) ?>"
            title="<?= htmlspecialchars($value) ?>"
            aria-pressed="<?= $isOn ? 'true' : 'false' ?>">
            <?php if ($img !== ''): ?>
              <span class="pdp-new-swatch__img"><img src="<?= htmlspecialchars($img) ?>" alt=""></span>
            <?php endif; ?>
            <span class="pdp-new-swatch__label"><?= htmlspecialchars($valueLabel) ?></span>
          </button>
        <?php endforeach; ?>
      </div>
    </div>
  <?php endforeach; ?>
</div>
<?php endif; ?>
<div class="pdp-new-packs<?= ($axisComplete && !$showAdminPricing) ? ' is-axis-hidden' : '' ?>" data-pdp-packs<?= ($axisComplete && !$showAdminPricing) ? ' hidden' : '' ?>>
  <h2 class="pdp-new-packs__title"><?= htmlspecialchars($heading) ?></h2>
  <div class="pdp-new-packs__grid" role="radiogroup" aria-label="<?= e_ui('product.options') ?>" data-count="<?= count($children) ?>">
    <?php foreach ($children as $child): ?>
      <?php
      $isSelected = (int) $child->id === $selectedId;
      $img = !empty($child->image) ? product_image_url($child->image) : $placeholder;
      $rawName = trim((string) $child->name);
      $fullLabel = $rawName !== '' ? $rawName : product_option_label($child, $product);
      $label = isset($displayLabels[(int) $child->id]) ? $displayLabels[(int) $child->id] : $fullLabel;
      $childCompare = isset($child->compare_price) ? (float) $child->compare_price : 0;
      $childStock = (int) $child->stock;
      ?>
      <div class="pdp-new-pack-slot">
      <label class="pdp-new-pack<?= $isSelected ? ' is-selected' : '' ?><?= $childStock < 1 ? ' is-oos' : '' ?>" title="<?= htmlspecialchars($fullLabel) ?>">
        <input type="radio" name="product_id" form="pdp-buy-form" value="<?= (int) $child->id ?>" <?= $isSelected ? 'checked' : '' ?> data-pdp-variation>
        <span class="pdp-new-pack__radio" aria-hidden="true"></span>
        <?php if ($img !== ''): ?>
          <span class="pdp-new-pack__img"><img src="<?= htmlspecialchars($img) ?>" alt=""></span>
        <?php endif; ?>
        <span class="pdp-new-pack__body">
          <span class="pdp-new-pack__name"><?= htmlspecialchars($label) ?></span>
          <span class="pdp-new-pack__prices">
            <span class="pdp-new-pack__price"><?= format_money((float) $child->price) ?></span>
            <?php if ($childCompare > (float) $child->price): ?>
              <span class="pdp-new-pack__was"><?= format_money($childCompare) ?></span>
            <?php endif; ?>
          </span>
          <?php if ($childStock < 1): ?>
            <span class="pdp-new-pack__stock"><?= e_ui('product.out_of_stock') ?></span>
          <?php endif; ?>
        </span>
      </label>
        <?php if (!empty($showAdminPricing)): ?>
          <?php $this->load->view('frontend/shared/admin_price_breakdown', array(
              'listing' => $child,
              'store' => isset($store) ? $store : null,
              'as_main' => false,
              'as_menu' => true,
          )); ?>
        <?php endif; ?>
      </div>
    <?php endforeach; ?>
  </div>
</div>
