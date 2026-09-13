<section class="page-head">
    <div class="container">
        <p class="eyebrow">Catalog</p>
        <h1>Shop</h1>
    </div>
</section>
<section class="container section">
    <div class="grid">
        <?php if (empty($products)): ?>
            <p>No products available.</p>
        <?php endif; ?>
        <?php foreach ($products as $product): ?>
        <article class="card">
            <a href="<?= product_url($product) ?>">
                <div class="card-media">
                    <?php if (!empty($product->image)): ?>
                        <img src="<?= base_url($product->image) ?>" alt="">
                    <?php else: ?>
                        <span><?= htmlspecialchars($product->name) ?></span>
                    <?php endif; ?>
                </div>
                <h3><?= htmlspecialchars($product->name) ?></h3>
                <p class="muted"><?= htmlspecialchars($product->supplier_name ?: '') ?></p>
                <p class="price"><?= format_money((float) $product->price) ?></p>
            </a>
        </article>
        <?php endforeach; ?>
    </div>
</section>
