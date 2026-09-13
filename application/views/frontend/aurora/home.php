<section class="hero">
    <div class="container">
        <p class="eyebrow">Aurora Collection</p>
        <h1>Quiet luxury for everyday living.</h1>
        <p>Discover curated products styled for <?= htmlspecialchars($store->name) ?>.</p>
        <a class="btn" href="<?= storefront_url('shop') ?>">Shop now</a>
    </div>
</section>
<section class="container section">
    <h2>Featured products</h2>
    <div class="grid">
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
                <p class="price"><?= format_money((float) $product->price) ?></p>
            </a>
        </article>
        <?php endforeach; ?>
    </div>
</section>
