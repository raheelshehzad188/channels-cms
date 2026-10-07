<?php
$pageTitle = isset($page_title) ? $page_title : 'Page';
$pageDetail = isset($page_detail) ? $page_detail : '';
?>
<nav class="container breadcrumb" aria-label="<?= e_ui('nav.breadcrumb') ?>">
  <ol>
    <li><a href="<?= storefront_url('') ?>"><?= e_ui('nav.home') ?></a></li>
    <li aria-current="page"><?= htmlspecialchars($pageTitle) ?></li>
  </ol>
</nav>

<div class="container">
  <header class="pagehead">
    <h1><?= htmlspecialchars($pageTitle) ?></h1>
  </header>
</div>

<div class="container content content--single">
  <article class="simple-card page-body prose-block">
    <?= function_exists('store_page_html') ? store_page_html($pageDetail) : nl2br(htmlspecialchars($pageDetail)) ?>
  </article>
</div>
