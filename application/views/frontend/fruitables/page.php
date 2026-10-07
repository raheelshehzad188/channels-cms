<?php
$homeUrl = !empty($is_preview) ? $preview_back : storefront_url('');
$pageTitle = isset($page_title) ? $page_title : 'Page';
$pageDetail = isset($page_detail) ? $page_detail : '';
?>
<div class="container-fluid page-header py-5">
    <h1 class="text-center text-white display-6"><?= htmlspecialchars($pageTitle) ?></h1>
    <ol class="breadcrumb justify-content-center mb-0">
        <li class="breadcrumb-item"><a href="<?= $homeUrl ?>">Home</a></li>
        <li class="breadcrumb-item active text-white"><?= htmlspecialchars($pageTitle) ?></li>
    </ol>
</div>

<div class="container-fluid py-5">
    <div class="container py-5">
        <article class="page-body" style="max-width:760px;margin:0 auto">
            <?= function_exists('store_page_html') ? store_page_html($pageDetail) : nl2br(htmlspecialchars($pageDetail)) ?>
        </article>
    </div>
</div>
