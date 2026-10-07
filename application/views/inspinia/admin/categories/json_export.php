<?php
$storeId = isset($store_id) ? (int) $store_id : 0;
$store = isset($store) ? $store : null;
?>
<div class="row wrapper border-bottom white-bg page-heading">
    <div class="col-lg-8">
        <h2>Category JSON</h2>
        <ol class="breadcrumb">
            <li><a href="<?= base_url('admin/admin') ?>">Dashboard</a></li>
            <li><a href="<?= base_url('admin/categories') ?>">Categories</a></li>
            <li class="active"><strong>JSON</strong></li>
        </ol>
    </div>
    <div class="col-lg-4 text-right" style="margin-top:26px;">
        <a href="<?= base_url('admin/categories') ?>" class="btn btn-white">Back to Categories</a>
    </div>
</div>
<div class="wrapper wrapper-content">
    <div class="ibox">
        <div class="ibox-title"><h5>Copy categories for AI</h5></div>
        <div class="ibox-content">
            <p class="text-muted">Pick a store, then copy parent categories and subcategories as JSON (id + name). Subcategories also include <code>parent_id</code> so AI can attach them to the right parent.</p>
            <form method="get" action="<?= base_url('admin/categories/json') ?>" class="form-inline" style="margin-bottom:20px;">
                <div class="form-group">
                    <label style="margin-right:8px;">Store</label>
                    <select name="store_id" class="form-control" style="min-width:320px;" onchange="this.form.submit()">
                        <?php foreach (!empty($stores) ? $stores : array() as $row): ?>
                            <option value="<?= (int) $row->id ?>" <?= $storeId === (int) $row->id ? 'selected' : '' ?>>
                                <?= htmlspecialchars($row->name) ?><?php if (!empty($row->country_name)): ?> — <?= htmlspecialchars($row->country_name) ?><?php endif; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </form>
            <?php if ($store): ?>
            <p>
                <strong><?= htmlspecialchars($store->name) ?></strong>
                · <?= (int) $category_count ?> categories
                · <?= (int) $subcategory_count ?> subcategories
            </p>
            <?php endif; ?>

            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Categories JSON</label>
                        <button type="button" class="btn btn-xs btn-primary pull-right js-copy" data-target="catJson">Copy</button>
                        <textarea id="catJson" class="form-control" rows="16" readonly style="font-family:Menlo,Monaco,Consolas,monospace; font-size:12px;"><?= htmlspecialchars(isset($categories_json) ? $categories_json : '[]') ?></textarea>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Subcategories JSON</label>
                        <button type="button" class="btn btn-xs btn-primary pull-right js-copy" data-target="subJson">Copy</button>
                        <textarea id="subJson" class="form-control" rows="16" readonly style="font-family:Menlo,Monaco,Consolas,monospace; font-size:12px;"><?= htmlspecialchars(isset($subcategories_json) ? $subcategories_json : '[]') ?></textarea>
                    </div>
                </div>
            </div>
            <div class="form-group">
                <label>Tree JSON (categories + nested subcategories)</label>
                <button type="button" class="btn btn-xs btn-primary pull-right js-copy" data-target="treeJson">Copy</button>
                <textarea id="treeJson" class="form-control" rows="18" readonly style="font-family:Menlo,Monaco,Consolas,monospace; font-size:12px;"><?= htmlspecialchars(isset($tree_json) ? $tree_json : '[]') ?></textarea>
            </div>
        </div>
    </div>
</div>
<script>
(function () {
    function copyText(id) {
        var el = document.getElementById(id);
        if (!el) return;
        var text = el.value;
        if (navigator.clipboard && navigator.clipboard.writeText) {
            navigator.clipboard.writeText(text).then(function () {
                flashCopied();
            }).catch(function () {
                fallback(el);
            });
            return;
        }
        fallback(el);
    }
    function fallback(el) {
        el.focus();
        el.select();
        try { document.execCommand('copy'); flashCopied(); } catch (e) {}
    }
    function flashCopied() {
        var n = document.createElement('div');
        n.className = 'alert alert-success';
        n.style.cssText = 'position:fixed;top:16px;right:16px;z-index:9999;';
        n.textContent = 'Copied';
        document.body.appendChild(n);
        setTimeout(function () { n.parentNode && n.parentNode.removeChild(n); }, 1200);
    }
    document.querySelectorAll('.js-copy').forEach(function (btn) {
        btn.addEventListener('click', function () {
            copyText(btn.getAttribute('data-target'));
        });
    });
})();
</script>
