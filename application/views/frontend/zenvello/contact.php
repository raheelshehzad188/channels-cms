<?php
$contact = function_exists('storefront_contact_info')
    ? storefront_contact_info($store, $settings)
    : array(
        'email' => (!empty($store->email) && filter_var($store->email, FILTER_VALIDATE_EMAIL)) ? $store->email : '',
        'phone' => !empty($store->phone) ? $store->phone : '',
        'address' => '',
        'name' => $store->name,
    );
$contactEmail = $contact['email'];
$contactPhone = $contact['phone'];
$contactAddress = $contact['address'];
$topics = array(
    store_ui('contact.topic_order'),
    store_ui('contact.topic_delivery'),
    store_ui('contact.topic_returns'),
    store_ui('contact.topic_product'),
    store_ui('contact.topic_other'),
);
$posted = isset($form) && is_array($form) ? $form : array();
$val = function ($key, $default = '') use ($posted) {
    return isset($posted[$key]) ? $posted[$key] : $default;
};
$selectedTopic = $val('topic', store_ui('contact.topic_order'));
$formAction = function_exists('contact_url') ? contact_url() : storefront_url('contact');
$privacyUrl = function_exists('page_url') ? page_url('privacy-policy') : storefront_url('privacy-policy');
$termsUrl = function_exists('page_url') ? page_url('terms-of-service') : storefront_url('terms-of-service');
$deletionUrl = function_exists('page_url') ? page_url('data-deletion') : storefront_url('data-deletion');
?>
<nav class="container breadcrumb" aria-label="<?= e_ui('nav.breadcrumb') ?>">
  <ol>
    <li><a href="<?= storefront_url('') ?>"><?= e_ui('nav.home') ?></a></li>
    <li aria-current="page"><?= e_ui('contact.title') ?></li>
  </ol>
</nav>

<div class="container">
  <header class="pagehead">
    <h1><?= e_ui('contact.title') ?></h1>
    <p><?= e_ui('contact.lead') ?></p>
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

    <form class="form" method="post" action="<?= $formAction ?>" novalidate>
      <p class="visually-hidden" aria-hidden="true">
        <label for="c-company">Company</label>
        <input id="c-company" name="company" type="text" tabindex="-1" autocomplete="off">
      </p>
      <div class="form__row">
        <p class="field">
          <label for="c-name"><?= e_ui('contact.full_name') ?></label>
          <input id="c-name" name="name" type="text" required value="<?= htmlspecialchars($val('name')) ?>" autocomplete="name">
        </p>
        <p class="field">
          <label for="c-email"><?= e_ui('contact.email') ?></label>
          <input id="c-email" name="email" type="email" required value="<?= htmlspecialchars($val('email')) ?>" autocomplete="email">
        </p>
      </div>
      <p class="field">
        <label for="c-order"><?= e_ui('contact.order_no') ?> <span class="field__hint"><?= e_ui('contact.optional') ?></span></label>
        <input id="c-order" name="order" type="text" placeholder="<?= e_ui('contact.order_placeholder') ?>" value="<?= htmlspecialchars($val('order')) ?>">
      </p>
      <p class="field">
        <label for="c-topic"><?= e_ui('contact.topic') ?></label>
        <select id="c-topic" name="topic">
          <?php foreach ($topics as $topic): ?>
            <option value="<?= htmlspecialchars($topic) ?>"<?= $selectedTopic === $topic ? ' selected' : '' ?>><?= htmlspecialchars($topic) ?></option>
          <?php endforeach; ?>
        </select>
      </p>
      <p class="field">
        <label for="c-message"><?= e_ui('contact.message') ?></label>
        <textarea id="c-message" name="message" required><?= htmlspecialchars($val('message')) ?></textarea>
      </p>
      <p><button class="btn btn--yellow btn--lg" type="submit"><?= e_ui('contact.send') ?></button></p>
      <p class="form__note"><?= e_ui('contact.note') ?></p>
    </form>
  </div>

  <aside class="sidecard">
    <h2><?= e_ui('contact.other_ways') ?></h2>
    <?php if ($contactEmail !== ''): ?>
      <p class="sidecard__row">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="m3 7 9 6 9-6"/></svg>
        <span><b><?= e_ui('contact.email_label') ?></b><a href="mailto:<?= htmlspecialchars($contactEmail) ?>"><?= htmlspecialchars($contactEmail) ?></a></span>
      </p>
    <?php else: ?>
      <p class="sidecard__row"><span><em>[To be completed by store admin: support email]</em></span></p>
    <?php endif; ?>
    <?php if ($contactPhone !== ''): ?>
      <p class="sidecard__row">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 3h4l2 5-3 2a12 12 0 0 0 6 6l2-3 5 2v4a2 2 0 0 1-2 2A18 18 0 0 1 3 5a2 2 0 0 1 2-2Z"/></svg>
        <span><b><?= e_ui('contact.phone_label') ?></b><a href="tel:<?= htmlspecialchars(preg_replace('/[^\d+]/', '', $contactPhone)) ?>"><?= htmlspecialchars($contactPhone) ?></a></span>
      </p>
    <?php endif; ?>
    <?php if ($contactAddress !== ''): ?>
      <p class="sidecard__row">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 2a7 7 0 0 0-7 7c0 5 7 13 7 13s7-8 7-13a7 7 0 0 0-7-7Z"/><circle cx="12" cy="9" r="2.4"/></svg>
        <span><b><?= e_ui('contact.address_label') ?></b><?= htmlspecialchars($contactAddress) ?></span>
      </p>
    <?php endif; ?>
    <p class="sidecard__row"><span><a href="<?= htmlspecialchars($privacyUrl) ?>"><?= e_ui('contact.privacy') ?></a></span></p>
    <p class="sidecard__row"><span><a href="<?= htmlspecialchars($termsUrl) ?>"><?= e_ui('contact.terms') ?></a></span></p>
    <p class="sidecard__row"><span><a href="<?= htmlspecialchars($deletionUrl) ?>"><?= e_ui('contact.deletion') ?></a></span></p>
  </aside>
</div>

<section class="container section section--tight" aria-label="<?= e_ui('home.why_shop') ?>">
  <div class="trustbar">
    <div class="trust">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M1 4h14v11H1z"/><path d="M15 8h4l4 4v3h-8z"/><circle cx="5.5" cy="18" r="2.2"/><circle cx="18" cy="18" r="2.2"/></svg>
      <div><p class="trust__t"><?= e_ui('trust.free_shipping') ?></p><p class="trust__s"><?= e_ui('trust.free_shipping_text') ?></p></div>
    </div>
    <div class="trust">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 2.5 20 6v6.2c0 4.7-3.3 7.9-8 9.3-4.7-1.4-8-4.6-8-9.3V6Z"/><path d="m8.8 12 2.2 2.2 4.2-4.4"/></svg>
      <div><p class="trust__t"><?= e_ui('trust.secure') ?></p><p class="trust__s"><?= e_ui('trust.secure_text') ?></p></div>
    </div>
    <div class="trust">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M21 8 12 3 3 8l9 5 9-5Z"/><path d="M3 8v8l9 5 9-5V8"/><path d="M12 13v8"/></svg>
      <div><p class="trust__t"><?= e_ui('trust.returns') ?></p><p class="trust__s"><?= e_ui('trust.returns_text') ?></p></div>
    </div>
    <div class="trust">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 13v-1a8 8 0 0 1 16 0v1"/><path d="M4 13h2.6v6H5a1 1 0 0 1-1-1Z"/><path d="M20 13h-2.6v6H19a1 1 0 0 0 1-1Z"/><path d="M17.4 19a4 4 0 0 1-4 3h-1.2"/></svg>
      <div><p class="trust__t"><?= e_ui('trust.support') ?></p><p class="trust__s"><?= e_ui('trust.support_text') ?></p></div>
    </div>
  </div>
</section>
