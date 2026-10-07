<section class="ec-page ec-flow-page ec-pay">
  <header class="ec-flow-head">
    <h1><?= e_ui('pay.title') ?></h1>
    <p class="ec-lead"><?= e_ui('pay.lead') ?></p>
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
            <?= e_ui('pay.card') ?>
          </button>
          <button type="button" class="ec-pay-tab" data-pay-tab="paypal" aria-selected="false">
            <span class="ec-pay-tab-icon" aria-hidden="true">
              <svg viewBox="0 0 24 24" width="20" height="20" fill="currentColor"><path d="M7.2 20.4 8 15.5h2.6c3.7 0 6.2-1.6 6.9-4.8.3-1.5.1-2.7-.6-3.6-.7-.9-1.9-1.4-3.4-1.4H8.8L7.2 20.4Zm3.4-12.2h1.8c.9 0 1.5.2 1.9.6.4.4.5 1 .4 1.7-.4 2-1.9 2.9-4.4 2.9H9.2l.6-3.8h.8Zm5.9-.7c.9 1.1 1.2 2.5.8 4.3-.7 3.5-3.5 5.3-7.7 5.3H7.3L6 22h2.5l.5-3.1h1.8c4.9 0 8.4-2.3 9.4-6.7.4-1.8.2-3.4-.7-4.7-.6-.9-1.5-1.5-2.6-1.9.6.3 1.1.8 1.5 1.5Z"/></svg>
            </span>
            <?= e_ui('pay.paypal') ?>
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
            <label><?= e_ui('pay.name_on_card') ?></label>
            <input type="text" id="cardName" name="card_name_ui" required placeholder="<?= e_ui('pay.name_placeholder') ?>" autocomplete="cc-name">

            <label><?= e_ui('pay.card_number') ?></label>
            <div class="ec-pay-input-wrap">
              <input type="text" id="cardNumber" inputmode="numeric" required placeholder="XXXX XXXX XXXX XXXX" maxlength="23" autocomplete="cc-number">
              <span class="ec-pay-secure-pill"><?= e_ui('pay.encrypted') ?></span>
            </div>

            <div class="ec-pay-row">
              <div>
                <label><?= e_ui('pay.expiry') ?></label>
                <input type="text" id="cardExpiry" inputmode="numeric" required placeholder="MM / YY" maxlength="7" autocomplete="cc-exp">
              </div>
              <div>
                <label><?= e_ui('pay.cvv') ?></label>
                <input type="password" id="cardCvv" inputmode="numeric" required placeholder="•••" maxlength="4" autocomplete="cc-csc">
              </div>
            </div>

            <p class="ec-pay-note"><?= e_ui('pay.note') ?></p>
            <button class="ec-btn ec-pay-submit" type="submit" id="cardPayBtn">
              <?= e_ui('pay.pay_amount', array('{amount}' => format_money((float) $cart_total, $pay_currency))) ?>
            </button>
          </form>
        </div>

        <div class="ec-pay-panel" data-pay-panel="paypal">
          <div class="ec-pay-paypal-box">
            <p><?= e_ui('pay.paypal_text') ?></p>
            <form method="post" action="<?= storefront_url('payment/paypal') ?>">
              <button class="ec-btn ec-pay-paypal-btn" type="submit"><?= e_ui('pay.paypal_btn') ?></button>
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
        <h2><?= e_ui('cart.summary') ?></h2>
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
          <?php if (!empty($show_shipping) || (isset($shipping_amount) && (float) $shipping_amount > 0)): ?>
            <li>
              <span class="ec-line__ph"></span>
              <div>
                <b><?= e_ui('cart.shipping') ?></b>
                <span>× <?= isset($shipping_qty) ? (int) $shipping_qty : 0 ?></span>
              </div>
              <strong><?php if (!empty($shipping_free)): ?><?= e_ui('cart.shipping_free') ?><?php else: ?><?= format_money(isset($shipping_amount) ? (float) $shipping_amount : 0, $pay_currency) ?><?php endif; ?></strong>
            </li>
          <?php endif; ?>
        </ul>
        <dl>
          <?php $this->load->view('frontend/shared/_cart_totals', array('totals_currency' => $pay_currency)); ?>
        </dl>
        <p class="ec-pay-ship">
          <?= e_ui('pay.shipping_to') ?><br>
          <strong><?= htmlspecialchars($draft['shipping']['name']) ?></strong><br>
          <?= nl2br(htmlspecialchars($draft['shipping']['address'])) ?>
        </p>
        <a class="ec-summary__shop" href="<?= storefront_url('checkout') ?>"><?= e_ui('pay.edit_shipping') ?></a>
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
