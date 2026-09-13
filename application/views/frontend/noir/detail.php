<section class="container section detail">
    <div class="detail-media">
        <?php if (!empty($product->image)): ?>
            <img src="<?= base_url($product->image) ?>" alt="">
        <?php else: ?>
            <div class="card-media tall"><span><?= htmlspecialchars($product->name) ?></span></div>
        <?php endif; ?>
    </div>
    <div class="detail-copy">
        <p class="eyebrow"><?= htmlspecialchars($product->sku ?: 'Product') ?></p>
        <h1><?= htmlspecialchars($product->name) ?></h1>
        <p class="price large"><?= format_money((float) $product->price) ?></p>
        <p><?= nl2br(htmlspecialchars($product->description ?: 'Selected for the Noir collection.')) ?></p>
        <?php $this->load->view('frontend/shared/product_details', array('product' => $product)); ?>
        <p class="muted">Supplier: <?= htmlspecialchars($product->supplier_name ?: '-') ?> · <?= htmlspecialchars($product->country_name ?: '') ?></p>
        <p class="muted">Stock: <?= (int) $product->stock ?></p>
        <?php $this->load->view('frontend/shared/shipping_eta', array('product' => $product)); ?>
        <a class="btn" href="<?= !empty($is_preview) ? $preview_back : storefront_url('shop') ?>"><?= !empty($is_preview) ? 'Back to products' : 'Back to shop' ?></a>
    </div>
</section>
