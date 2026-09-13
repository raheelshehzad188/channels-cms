<section class="ec-page ec-flow-page ec-pay">
  <header class="ec-flow-head">
    <h1>Payment</h1>
    <p class="ec-lead">Encrypted checkout — card details are sealed in your browser before they leave this page.</p>
    <?php $this->load->view('frontend/shared/_checkout_steps', array('checkout_step' => 'payment')); ?>
  </header>

    <?php if (!empty($flash_error)): ?><div class="ec-alert error"><?= htmlspecialchars($flash_error) ?></div><?php endif; ?>
    <?php if (!empty($flash_success)): ?><div class="ec-alert success"><?= htmlspecialchars($flash_success) ?></div><?php endif; ?>

    <div class="ec-flow">
      <div class="ec-flow__main">
        <div class="ec-panel">
        <div class="ec-pay-methods" role="tablist">
          <button type="button" class="ec-pay-tab is-active" data-pay-tab="card" aria-selected="true">
            <span class="ec-pay-tab-icon" aria-hidden="true">
              <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.7"><rect x="2" y="5" width="20" height="14" rx="2"/><path d="M2 10h20"/><path d="M6 15h4"/></svg>
            </span>
            Pay by Card
          </button>
          <button type="button" class="ec-pay-tab" data-pay-tab="paypal" aria-selected="false">
            <span class="ec-pay-tab-icon" aria-hidden="true">
              <svg viewBox="0 0 24 24" width="20" height="20" fill="currentColor"><path d="M7.2 20.4 8 15.5h2.6c3.7 0 6.2-1.6 6.9-4.8.3-1.5.1-2.7-.6-3.6-.7-.9-1.9-1.4-3.4-1.4H8.8L7.2 20.4Zm3.4-12.2h1.8c.9 0 1.5.2 1.9.6.4.4.5 1 .4 1.7-.4 2-1.9 2.9-4.4 2.9H9.2l.6-3.8h.8Zm5.9-.7c.9 1.1 1.2 2.5.8 4.3-.7 3.5-3.5 5.3-7.7 5.3H7.3L6 22h2.5l.5-3.1h1.8c4.9 0 8.4-2.3 9.4-6.7.4-1.8.2-3.4-.7-4.7-.6-.9-1.5-1.5-2.6-1.9.6.3 1.1.8 1.5 1.5Z"/></svg>
            </span>
            PayPal
          </button>
        </div>

        <div class="ec-pay-panel is-active" data-pay-panel="card">
          <div class="ec-pay-card-visual" aria-hidden="true">
            <div class="ec-pay-chip"></div>
            <div class="ec-pay-card-num">•••• •••• •••• ••••</div>
            <div class="ec-pay-card-meta"><span>CARDHOLDER</span><span>MM/YY</span></div>
          </div>

          <form class="ec-form ec-pay-form" id="ecCardForm" method="post" action="<?= storefront_url('payment/card') ?>" autocomplete="off">
            <input type="hidden" name="card_payload" id="cardPayload">
            <label>Name on card</label>
            <input type="text" id="cardName" name="card_name_ui" required placeholder="Name as printed on card" autocomplete="cc-name">

            <label>Card number</label>
            <div class="ec-pay-input-wrap">
              <input type="text" id="cardNumber" inputmode="numeric" required placeholder="XXXX XXXX XXXX XXXX" maxlength="23" autocomplete="cc-number">
              <span class="ec-pay-secure-pill">Encrypted</span>
            </div>

            <div class="ec-pay-row">
              <div>
                <label>Expiry</label>
                <input type="text" id="cardExpiry" inputmode="numeric" required placeholder="MM / YY" maxlength="7" autocomplete="cc-exp">
              </div>
              <div>
                <label>CVV</label>
                <input type="password" id="cardCvv" inputmode="numeric" required placeholder="•••" maxlength="4" autocomplete="cc-csc">
              </div>
            </div>

            <p class="ec-pay-note">Card details never touch our servers in plain text. They are RSA-encrypted in your browser, then charged through PayPal.</p>
            <button class="ec-btn ec-pay-submit" type="submit" id="cardPayBtn">
              Pay <?= format_money((float) $cart_total, $pay_currency) ?>
            </button>
          </form>
        </div>

        <div class="ec-pay-panel" data-pay-panel="paypal">
          <div class="ec-pay-paypal-box">
            <p>Continue with your PayPal account. You’ll confirm the payment on PayPal’s secure site, then return here.</p>
            <form method="post" action="<?= storefront_url('payment/paypal') ?>">
              <button class="ec-btn ec-pay-paypal-btn" type="submit">Continue with PayPal</button>
            </form>
          </div>
        </div>

        <div class="ec-pay-trust">
          <div><strong>256-bit TLS</strong><span>Transport encryption</span></div>
          <div><strong>RSA browser vault</strong><span>Card payload sealed client-side</span></div>
          <div><strong>PayPal processing</strong><span>Card &amp; wallet via PayPal API</span></div>
        </div>
        </div>
      </div>

      <aside class="ec-summary">
        <h2>Order summary</h2>
        <ul class="ec-mini">
          <?php foreach ($cart_items as $item): ?>
            <li>
              <?php if (!empty($item->image)): ?>
                <img src="<?= htmlspecialchars($item->image) ?>" alt="">
              <?php else: ?>
                <span class="ec-line__ph"></span>
              <?php endif; ?>
              <div>
                <b><?= htmlspecialchars($item->name) ?></b>
                <span>× <?= (int) $item->qty ?></span>
              </div>
              <strong><?= format_money((float) $item->line_total, $pay_currency) ?></strong>
            </li>
          <?php endforeach; ?>
        </ul>
        <dl>
          <div><dt>Subtotal</dt><dd><?= format_money((float) $cart_subtotal, $pay_currency) ?></dd></div>
          <?php if (!empty($vat_percent) && (float) $vat_percent > 0): ?>
            <div><dt>VAT (<?= number_format((float) $vat_percent, 2) ?>%)</dt><dd><?= format_money((float) $vat_amount, $pay_currency) ?></dd></div>
          <?php endif; ?>
          <div class="ec-summary__total"><dt>Total</dt><dd><?= format_money((float) $cart_total, $pay_currency) ?></dd></div>
        </dl>
        <p class="ec-pay-ship">
          Shipping to<br>
          <strong><?= htmlspecialchars($draft['shipping']['name']) ?></strong><br>
          <?= nl2br(htmlspecialchars($draft['shipping']['address'])) ?>
        </p>
        <a class="ec-summary__shop" href="<?= storefront_url('checkout') ?>">Edit shipping</a>
      </aside>
    </div>
</section>

<script>
window.EC_PAYMENT = {
  publicKey: <?= json_encode($card_public_key) ?>,
  currency: <?= json_encode($pay_currency) ?>
};
</script>
<script src="<?= base_url('assets/frontend/shared/js/payment.js') ?>"></script>
