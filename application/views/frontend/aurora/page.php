<?php
$pageTitle = isset($page_title) ? $page_title : 'Page';
$pageDetail = isset($page_detail) ? $page_detail : '';
?>
<main class="container section" style="padding:40px 16px">
  <h1><?= htmlspecialchars($pageTitle) ?></h1>
  <article class="page-body" style="margin-top:20px;max-width:760px">
    <?= function_exists('store_page_html') ? store_page_html($pageDetail) : nl2br(htmlspecialchars($pageDetail)) ?>
  </article>
</main>
