<?php
$contactEmail = theme_setting($settings, 'email', theme_setting($settings, 'general_contact_email', $store->email ?: 'hello@' . $store->domain));
$contactPhone = theme_setting($settings, 'phone', theme_setting($settings, 'general_support_phone', isset($store->phone) ? $store->phone : ''));
$contactAddress = theme_setting($settings, 'address', '');
$topics = array(
    'An existing order',
    'Delivery & shipping',
    'Returns & refunds',
    'Product question',
    'Something else',
);
$posted = isset($form) && is_array($form) ? $form : array();
$val = function ($key, $default = '') use ($posted) {
    return isset($posted[$key]) ? $posted[$key] : $default;
};
$selectedTopic = $val('topic', 'An existing order');
?>
<nav class="container breadcrumb" aria-label="Breadcrumb">
  <ol>
    <li><a href="<?= storefront_url('shop/index') ?>">Home</a></li>
    <li aria-current="page">Contact Us</li>
  </ol>
</nav>

<div class="container">
  <header class="pagehead">
    <h1>Contact Us</h1>
    <p>Questions about an order, a product or a return? Send us a message and we will come back to you within one working day.</p>
  </header>
</div>

<div class="container content">
  <div>
    <?php if (!empty($flash_success)): ?>
      <p class="notice notice--ok" role="status"><?= htmlspecialchars($flash_success) ?></p>
    <?php endif; ?>
    <?php if (!empty($flash_error)): ?>
      <p class="notice notice--err" role="alert"><?= htmlspecialchars($flash_error) ?></p>
    <?php endif; ?>

    <form class="form" method="post" action="<?= storefront_url('page/contact') ?>" novalidate>
      <p class="visually-hidden" aria-hidden="true">
        <label for="c-company">Company</label>
        <input id="c-company" name="company" type="text" tabindex="-1" autocomplete="off">
      </p>
      <div class="form__row">
        <p class="field">
          <label for="c-name">Full name</label>
          <input id="c-name" name="name" type="text" required value="<?= htmlspecialchars($val('name')) ?>" autocomplete="name">
        </p>
        <p class="field">
          <label for="c-email">Email address</label>
          <input id="c-email" name="email" type="email" required value="<?= htmlspecialchars($val('email')) ?>" autocomplete="email">
        </p>
      </div>
      <p class="field">
        <label for="c-order">Order number <span class="field__hint">(optional)</span></label>
        <input id="c-order" name="order" type="text" placeholder="e.g. 1048372" value="<?= htmlspecialchars($val('order')) ?>">
      </p>
      <p class="field">
        <label for="c-topic">What is it about?</label>
        <select id="c-topic" name="topic">
          <?php foreach ($topics as $topic): ?>
            <option value="<?= htmlspecialchars($topic) ?>"<?= $selectedTopic === $topic ? ' selected' : '' ?>><?= htmlspecialchars($topic) ?></option>
          <?php endforeach; ?>
        </select>
      </p>
      <p class="field">
        <label for="c-message">Message</label>
        <textarea id="c-message" name="message" required><?= htmlspecialchars($val('message')) ?></textarea>
      </p>
      <p><button class="btn btn--yellow btn--lg" type="submit">Send Message</button></p>
      <p class="form__note">We only use these details to reply to your enquiry.</p>
    </form>
  </div>

  <aside class="sidecard">
    <h2>Other ways to reach us</h2>
    <?php if ($contactEmail !== ''): ?>
      <p class="sidecard__row">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="m3 7 9 6 9-6"/></svg>
        <span><b>Email</b><a href="mailto:<?= htmlspecialchars($contactEmail) ?>"><?= htmlspecialchars($contactEmail) ?></a></span>
      </p>
    <?php endif; ?>
    <?php if ($contactPhone !== ''): ?>
      <p class="sidecard__row">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 3h4l2 5-3 2a12 12 0 0 0 6 6l2-3 5 2v4a2 2 0 0 1-2 2A18 18 0 0 1 3 5a2 2 0 0 1 2-2Z"/></svg>
        <span><b>Phone</b><a href="tel:<?= htmlspecialchars(preg_replace('/[^\d+]/', '', $contactPhone)) ?>"><?= htmlspecialchars($contactPhone) ?></a></span>
      </p>
    <?php endif; ?>
    <?php if ($contactAddress !== ''): ?>
      <p class="sidecard__row">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 2a7 7 0 0 0-7 7c0 5 7 13 7 13s7-8 7-13a7 7 0 0 0-7-7Z"/><circle cx="12" cy="9" r="2.4"/></svg>
        <span><b>Address</b><?= htmlspecialchars($contactAddress) ?></span>
      </p>
    <?php endif; ?>
  </aside>
</div>

<section class="container section section--tight" aria-label="Why shop with us">
  <div class="trustbar">
    <div class="trust">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M1 4h14v11H1z"/><path d="M15 8h4l4 4v3h-8z"/><circle cx="5.5" cy="18" r="2.2"/><circle cx="18" cy="18" r="2.2"/></svg>
      <div><p class="trust__t">Free Shipping</p><p class="trust__s">On qualifying orders</p></div>
    </div>
    <div class="trust">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 2.5 20 6v6.2c0 4.7-3.3 7.9-8 9.3-4.7-1.4-8-4.6-8-9.3V6Z"/><path d="m8.8 12 2.2 2.2 4.2-4.4"/></svg>
      <div><p class="trust__t">Secure Payment</p><p class="trust__s">100% secure checkout</p></div>
    </div>
    <div class="trust">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M21 8 12 3 3 8l9 5 9-5Z"/><path d="M3 8v8l9 5 9-5V8"/><path d="M12 13v8"/></svg>
      <div><p class="trust__t">Easy Returns</p><p class="trust__s">Hassle-free policy</p></div>
    </div>
    <div class="trust">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 13v-1a8 8 0 0 1 16 0v1"/><path d="M4 13h2.6v6H5a1 1 0 0 1-1-1Z"/><path d="M20 13h-2.6v6H19a1 1 0 0 0 1-1Z"/><path d="M17.4 19a4 4 0 0 1-4 3h-1.2"/></svg>
      <div><p class="trust__t">24/7 Support</p><p class="trust__s">We're here to help</p></div>
    </div>
  </div>
</section>
