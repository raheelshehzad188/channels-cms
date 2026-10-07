<?php
$addr = isset($addr) && is_array($addr) ? $addr : array();
$countries = isset($countries) && is_array($countries) ? $countries : array();
$showBilling = !isset($show_billing) || $show_billing;
$billingOpen = $showBilling && empty($addr['billing_same']);
$field = function ($key) use ($addr) {
    return isset($addr[$key]) ? (string) $addr[$key] : '';
};
$countryOptions = function ($selected) use ($countries) {
    $selected = (string) $selected;
    $html = '';
    $seen = false;
    foreach ($countries as $country) {
        $name = isset($country->name) ? trim((string) $country->name) : '';
        if ($name === '') {
            continue;
        }
        if (strcasecmp($name, $selected) === 0) {
            $seen = true;
        }
        $html .= '<option value="' . htmlspecialchars($name) . '"' . (strcasecmp($name, $selected) === 0 ? ' selected' : '') . '>' . htmlspecialchars($name) . '</option>';
    }
    if ($selected !== '' && !$seen) {
        $html = '<option value="' . htmlspecialchars($selected) . '" selected>' . htmlspecialchars($selected) . '</option>' . $html;
    }
    return $html;
};
?>
<div class="ec-form__row">
  <div>
    <label><?= e_ui('checkout.first_name') ?> *</label>
    <input type="text" name="first_name" required value="<?= htmlspecialchars($field('first_name')) ?>" autocomplete="given-name">
  </div>
  <div>
    <label><?= e_ui('checkout.last_name') ?> *</label>
    <input type="text" name="last_name" required value="<?= htmlspecialchars($field('last_name')) ?>" autocomplete="family-name">
  </div>
</div>
<div class="ec-form__row">
  <div>
    <label><?= e_ui('checkout.email') ?> *</label>
    <input type="email" name="email" required value="<?= htmlspecialchars($field('email')) ?>" autocomplete="email">
  </div>
  <div>
    <label><?= e_ui('checkout.whatsapp') ?> *</label>
    <input type="text" name="phone" required value="<?= htmlspecialchars($field('phone')) ?>" autocomplete="tel" placeholder="46701234567" inputmode="tel">
    <span class="ec-hint"><?= e_ui('checkout.whatsapp_hint') ?></span>
  </div>
</div>
<label><?= e_ui('checkout.street') ?> *</label>
<input type="text" name="street" required value="<?= htmlspecialchars($field('street')) ?>" autocomplete="address-line1">
<label><?= e_ui('checkout.apartment') ?></label>
<input type="text" name="apartment" value="<?= htmlspecialchars($field('apartment')) ?>" autocomplete="address-line2">
<div class="ec-form__row">
  <div>
    <label><?= e_ui('checkout.postcode') ?> *</label>
    <input type="text" name="postcode" required value="<?= htmlspecialchars($field('postcode')) ?>" autocomplete="postal-code">
  </div>
  <div>
    <label><?= e_ui('checkout.city') ?> *</label>
    <input type="text" name="city" required value="<?= htmlspecialchars($field('city')) ?>" autocomplete="address-level2">
  </div>
</div>
<label><?= e_ui('checkout.country') ?> *</label>
<select name="country" required autocomplete="country-name">
  <?= $countryOptions($field('country')) ?>
</select>
<?php if ($showBilling): ?>
<label class="ec-choice">
  <input type="checkbox" name="billing_same" value="1" <?= !empty($addr['billing_same']) ? 'checked' : '' ?> data-billing-same>
  <span><?= e_ui('checkout.billing_same') ?></span>
</label>
<div class="ec-billing" data-billing-fields <?= $billingOpen ? '' : 'hidden' ?>>
  <h3><?= e_ui('checkout.billing') ?></h3>
  <label><?= e_ui('checkout.street') ?> *</label>
  <input type="text" name="billing_street" value="<?= htmlspecialchars($field('billing_street')) ?>" data-billing-required autocomplete="billing address-line1">
  <label><?= e_ui('checkout.apartment') ?></label>
  <input type="text" name="billing_apartment" value="<?= htmlspecialchars($field('billing_apartment')) ?>" autocomplete="billing address-line2">
  <div class="ec-form__row">
    <div>
      <label><?= e_ui('checkout.postcode') ?> *</label>
      <input type="text" name="billing_postcode" value="<?= htmlspecialchars($field('billing_postcode')) ?>" data-billing-required autocomplete="billing postal-code">
    </div>
    <div>
      <label><?= e_ui('checkout.city') ?> *</label>
      <input type="text" name="billing_city" value="<?= htmlspecialchars($field('billing_city')) ?>" data-billing-required autocomplete="billing address-level2">
    </div>
  </div>
  <label><?= e_ui('checkout.country') ?> *</label>
  <select name="billing_country" data-billing-required autocomplete="billing country-name">
    <?= $countryOptions($field('billing_country')) ?>
  </select>
</div>
<script>
(function () {
  var box = document.currentScript && document.currentScript.previousElementSibling;
  var root = box ? box.parentNode : null;
  if (!root) return;
  var toggle = root.querySelector('[data-billing-same]');
  if (!toggle || !box) return;
  function sync() {
    var open = !toggle.checked;
    box.hidden = !open;
    var fields = box.querySelectorAll('[data-billing-required]');
    for (var i = 0; i < fields.length; i++) {
      if (open) fields[i].setAttribute('required', 'required');
      else fields[i].removeAttribute('required');
    }
  }
  toggle.addEventListener('change', sync);
  sync();
})();
</script>
<?php endif; ?>
