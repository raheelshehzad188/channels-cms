<?php
$homeUrl = !empty($is_preview) ? $preview_back : storefront_url('shop/index');
$pageSlug = isset($page_slug) ? $page_slug : (isset($current_page) ? $current_page : 'page');
$pageTitle = isset($page_title) ? $page_title : ucfirst($pageSlug);
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
        <div id="theme-dynamic-page" data-page="<?= htmlspecialchars($pageSlug) ?>">
            <p class="text-center text-muted mb-0">Loading page…</p>
        </div>
    </div>
</div>
