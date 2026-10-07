<?php
$currency = isset($totals_currency) ? $totals_currency : null;
$shipQty = isset($shipping_qty) ? (int) $shipping_qty : 0;
$shipAmt = isset($shipping_amount) ? (float) $shipping_amount : 0;
$showShip = !empty($show_shipping) || $shipAmt > 0;
$vatPct = isset($vat_percent) ? (float) $vat_percent : 0;
$vatAmt = isset($vat_amount) ? (float) $vat_amount : 0;
$discountAmt = isset($cart_discount) ? (float) $cart_discount : 0;
$grossAmt = isset($cart_gross) ? (float) $cart_gross : 0;
?>
<?php if ($discountAmt > 0 && $grossAmt > 0): ?>
<div><dt><?= e_ui('cart.subtotal_before') ?></dt><dd><?= format_money($grossAmt, $currency) ?></dd></div>
<div><dt><?= e_ui('cart.discount') ?></dt><dd>−<?= format_money($discountAmt, $currency) ?></dd></div>
<?php endif; ?>
<div><dt><?= e_ui('cart.subtotal') ?></dt><dd><?= format_money((float) $cart_subtotal, $currency) ?></dd></div>
<?php if ($showShip): ?>
<div><dt><?= e_ui('cart.shipping_qty', array('{n}' => $shipQty)) ?></dt><dd><?php if (!empty($shipping_free)): ?><?= e_ui('cart.shipping_free') ?><?php else: ?><?= format_money($shipAmt, $currency) ?><?php endif; ?></dd></div>
<?php endif; ?>
<?php if ($vatPct > 0): ?>
<div><dt><?= e_ui('cart.vat', array('{percent}' => number_format($vatPct, 2))) ?></dt><dd><?= format_money($vatAmt, $currency) ?></dd></div>
<?php endif; ?>
<div class="ec-summary__total"><dt><?= e_ui('cart.total') ?></dt><dd><?= format_money((float) $cart_total, $currency) ?></dd></div>
