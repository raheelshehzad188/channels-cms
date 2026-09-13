<section class="hero">
    <div class="container">
        <p class="eyebrow">Noir Drop</p>
        <h1>Night-ready essentials.</h1>
        <p>A darker catalog built for <?= htmlspecialchars($store->name) ?>.</p>
        <a class="btn" href="<?= storefront_url('shop') ?>">Browse products</a>
    </div>
</section>
<section class="container section">
    <h2>Now showing</h2>
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
