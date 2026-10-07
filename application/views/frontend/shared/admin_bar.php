<?php
$product = isset($product) ? $product : null;
$listing = (isset($cart_product) && $cart_product) ? $cart_product : $product;
$editUrl = $product ? storefront_admin_edit_url($product) : '';
$profitUrl = ($listing && function_exists('storefront_admin_profitability_url'))
    ? storefront_admin_profitability_url($listing, isset($store) ? $store : null)
    : '';
$isAdmin = function_exists('storefront_admin_bar_visible') && storefront_admin_bar_visible();
$howdy = $isAdmin ? storefront_admin_bar_name() : '';
$platform = platform_base_url();
$pingUrl = $platform . 'admin_bar/ping';
$dashUrl = $platform . 'admin/admin';
$productsUrl = $platform . 'admin/products';
$listingId = ($listing && !empty($listing->id) && !empty($listing->store_id)) ? (int) $listing->id : 0;
$aiUrl = $listingId ? (function_exists('storefront_url') ? storefront_url('admin_bar/ai_content') : site_url('admin_bar/ai_content')) : '';
$trendingProduct = $product ? $product : $listing;
if ($trendingProduct && function_exists('product_family')) {
    $parentSkuCheck = isset($trendingProduct->parent_sku) ? trim((string) $trendingProduct->parent_sku) : '';
    if ($parentSkuCheck !== '') {
        $fam = product_family($trendingProduct, false);
        if (!empty($fam['parent'])) {
            $trendingProduct = $fam['parent'];
        }
    }
}
$trendingId = ($trendingProduct && !empty($trendingProduct->id) && !empty($trendingProduct->store_id))
    ? (int) $trendingProduct->id
    : 0;
$trendingUrl = $trendingId
    ? (function_exists('storefront_url') ? storefront_url('admin_bar/toggle_trending') : site_url('admin_bar/toggle_trending'))
    : '';
$isTrending = $trendingId && !empty($trendingProduct->is_trending);
if (!$isTrending && $trendingId && function_exists('product_family')) {
    $fam = product_family($trendingProduct, false);
    foreach (!empty($fam['children']) ? $fam['children'] : array() as $child) {
        if (!empty($child->is_trending)) {
            $isTrending = true;
            break;
        }
    }
}
$trendingLabel = $isTrending ? 'Remove Trending' : 'Make Trending';
?>
<style>
.ec-admin-bar{
  position:fixed;top:0;left:0;right:0;z-index:100000;height:32px;
  display:flex;align-items:center;gap:12px;padding:0 12px;
  background:#1d2327;color:#fff;font:13px/32px -apple-system,BlinkMacSystemFont,"Segoe UI",sans-serif;
  box-sizing:border-box;
}
.ec-admin-bar[hidden]{display:none !important;}
.ec-admin-bar a{color:#fff;text-decoration:none;white-space:nowrap;}
.ec-admin-bar a:hover{color:#00b9eb;}
.ec-admin-bar__brand{font-weight:700;margin-right:6px;}
.ec-admin-bar__btn{
  display:inline-flex;align-items:center;height:24px;padding:0 10px;border-radius:3px;
  background:#2271b1;font-weight:600;line-height:24px;
}
.ec-admin-bar__btn:hover{background:#135e96;color:#fff;}
.ec-admin-bar__btn--profit{background:#00a32a;}
.ec-admin-bar__btn--profit:hover{background:#008a20;color:#fff;}
.ec-admin-bar__btn--ai{background:#9b51e0;border:0;cursor:pointer;color:#fff;font:inherit;font-weight:600;font-size:13px;line-height:24px;}
.ec-admin-bar__btn--ai:hover{background:#7b33c0;color:#fff;}
.ec-admin-bar__btn--ai[disabled]{opacity:.65;cursor:wait;}
.ec-admin-bar__btn--trending{background:#d63638;border:0;cursor:pointer;color:#fff;font:inherit;font-weight:600;font-size:13px;line-height:24px;}
.ec-admin-bar__btn--trending:hover{background:#b32d2e;color:#fff;}
.ec-admin-bar__btn--trending.is-on{background:#dba617;}
.ec-admin-bar__btn--trending.is-on:hover{background:#b38600;color:#fff;}
.ec-admin-bar__btn--trending[disabled]{opacity:.65;cursor:wait;}
.ec-ai-modal{
  position:fixed;inset:0;z-index:100001;display:none;place-items:center;
  background:rgba(15,23,42,.45);padding:16px;
}
.ec-ai-modal.is-open{display:grid;}
.ec-ai-modal__box{
  width:min(420px,100%);background:#fff;color:#1d2327;border-radius:8px;
  box-shadow:0 16px 40px rgba(0,0,0,.28);padding:20px 20px 16px;
  font:14px/1.4 -apple-system,BlinkMacSystemFont,"Segoe UI",sans-serif;
}
.ec-ai-modal__box h2{margin:0 0 8px;font-size:18px;}
.ec-ai-modal__box p{margin:0 0 12px;color:#50575e;}
.ec-ai-modal__hint{font-size:12px !important;margin-top:-6px !important;}
.ec-ai-modal__box label{display:block;font-weight:600;margin-bottom:6px;}
.ec-ai-modal__box input[type=number]{
  width:100%;height:36px;padding:0 10px;border:1px solid #c3c4c7;border-radius:4px;
  font:inherit;box-sizing:border-box;
}
.ec-ai-modal__err{color:#b32d2e;font-size:13px;min-height:18px;margin:8px 0;}
.ec-ai-modal__actions{display:flex;justify-content:flex-end;gap:8px;margin-top:8px;}
.ec-ai-modal__actions button{
  height:32px;padding:0 12px;border-radius:3px;border:0;cursor:pointer;font-weight:600;
}
.ec-ai-modal__cancel{background:#f0f0f1;color:#1d2327;}
.ec-ai-modal__go{background:#9b51e0;color:#fff;}
.ec-ai-modal__go[disabled]{opacity:.65;cursor:wait;}
.ec-admin-bar__spacer{flex:1;}
.ec-admin-bar__howdy{opacity:.85;font-size:12px;}
html.ec-has-admin-bar{margin-top:32px !important;}
html.ec-has-admin-bar body .container-fluid.fixed-top{top:32px;}
html.ec-has-admin-bar .site-header{top:32px;}
</style>
<div id="ec-admin-bar" class="ec-admin-bar" <?= $isAdmin ? '' : 'hidden' ?> role="navigation" aria-label="Admin bar">
  <a class="ec-admin-bar__brand" href="<?= htmlspecialchars($dashUrl) ?>">ZENVello</a>
  <a href="<?= htmlspecialchars($dashUrl) ?>">Dashboard</a>
  <a href="<?= htmlspecialchars($productsUrl) ?>">Products</a>
  <?php if ($editUrl !== ''): ?>
    <a class="ec-admin-bar__btn" href="<?= htmlspecialchars($editUrl) ?>">Edit product</a>
  <?php endif; ?>
  <?php if ($profitUrl !== ''): ?>
    <a class="ec-admin-bar__btn ec-admin-bar__btn--profit" data-ec-profit href="<?= htmlspecialchars($profitUrl) ?>">Profitability</a>
  <?php endif; ?>
  <?php if ($trendingId && $trendingUrl !== ''): ?>
    <button type="button"
      class="ec-admin-bar__btn ec-admin-bar__btn--trending<?= $isTrending ? ' is-on' : '' ?>"
      data-ec-trending
      data-product-id="<?= $trendingId ?>"
      data-url="<?= htmlspecialchars($trendingUrl) ?>"
      data-on="<?= $isTrending ? '1' : '0' ?>"
    ><?= htmlspecialchars($trendingLabel) ?></button>
  <?php endif; ?>
  <?php if ($listingId && $aiUrl !== ''): ?>
    <button type="button" class="ec-admin-bar__btn ec-admin-bar__btn--ai" data-ec-ai-content data-product-id="<?= $listingId ?>" data-url="<?= htmlspecialchars($aiUrl) ?>">Regenerate AI content</button>
  <?php endif; ?>
  <span class="ec-admin-bar__spacer"></span>
  <span class="ec-admin-bar__howdy" data-ec-howdy><?= $howdy !== '' ? 'Howdy, ' . htmlspecialchars($howdy) : '' ?></span>
</div>
<?php if ($isAdmin): ?>
<script>document.documentElement.classList.add('ec-has-admin-bar');</script>
<?php else: ?>
<iframe id="ec-admin-bar-ping" src="<?= htmlspecialchars($pingUrl) ?>" title="" style="display:none;width:0;height:0;border:0"></iframe>
<script>
(function () {
  var origin = <?= json_encode(rtrim($platform, '/')) ?>;
  window.addEventListener('message', function (e) {
    if (!e.data || !e.data.ecAdminBar) return;
    if (origin && e.origin !== origin) return;
    var bar = document.getElementById('ec-admin-bar');
    if (!bar) return;
    bar.hidden = false;
    document.documentElement.classList.add('ec-has-admin-bar');
    var howdy = bar.querySelector('[data-ec-howdy]');
    if (howdy && e.data.name) howdy.textContent = 'Howdy, ' + e.data.name;
    var frame = document.getElementById('ec-admin-bar-ping');
    if (frame && frame.parentNode) frame.parentNode.removeChild(frame);
  });
})();
</script>
<?php endif; ?>
<?php if ($trendingId && $trendingUrl !== ''): ?>
<script>
(function () {
  var btn = document.querySelector('[data-ec-trending]');
  if (!btn) return;
  var busy = false;
  btn.addEventListener('click', function (e) {
    e.preventDefault();
    if (busy || btn.disabled) return;
    var url = btn.getAttribute('data-url');
    var id = btn.getAttribute('data-product-id');
    if (!url || !id) return;
    busy = true;
    btn.disabled = true;
    var prev = btn.textContent;
    btn.textContent = 'Saving…';
    var body = new FormData();
    body.append('product_id', id);
    fetch(url, { method: 'POST', body: body, credentials: 'same-origin' })
      .then(function (r) {
        return r.json().then(function (data) {
          return { httpOk: r.ok, data: data };
        }).catch(function () {
          return { httpOk: false, data: null };
        });
      })
      .then(function (res) {
        var data = res.data || {};
        busy = false;
        btn.disabled = false;
        if (!data.ok) {
          btn.textContent = prev;
          window.alert(data.error || 'Could not update trending.');
          return;
        }
        var on = !!data.is_trending;
        btn.setAttribute('data-on', on ? '1' : '0');
        if (data.product_id) btn.setAttribute('data-product-id', String(data.product_id));
        btn.textContent = data.label || (on ? 'Remove Trending' : 'Make Trending');
        if (on) btn.classList.add('is-on');
        else btn.classList.remove('is-on');
      })
      .catch(function () {
        busy = false;
        btn.disabled = false;
        btn.textContent = prev;
        window.alert('Could not update trending.');
      });
  });
})();
</script>
<?php endif; ?>
<?php if ($listingId && $aiUrl !== ''): ?>
<div id="ec-ai-modal" class="ec-ai-modal" hidden>
  <div class="ec-ai-modal__box" role="dialog" aria-labelledby="ec-ai-modal-title" aria-modal="true">
    <h2 id="ec-ai-modal-title">Generate AI Content</h2>
    <p>How many reviews would you like to generate?</p>
    <p class="ec-ai-modal__hint">Previous AI sample reviews for this product will be replaced. Real customer reviews are kept.</p>
    <label for="ec-ai-review-count">Number of reviews</label>
    <input id="ec-ai-review-count" type="number" min="1" max="50" step="1" value="5" inputmode="numeric">
    <div class="ec-ai-modal__err" data-ec-ai-error></div>
    <div class="ec-ai-modal__actions">
      <button type="button" class="ec-ai-modal__cancel" data-ec-ai-cancel>Cancel</button>
      <button type="button" class="ec-ai-modal__go" data-ec-ai-go>Generate</button>
    </div>
  </div>
</div>
<script>
(function () {
  var btn = document.querySelector('[data-ec-ai-content]');
  var modal = document.getElementById('ec-ai-modal');
  if (!btn || !modal) return;
  var countEl = document.getElementById('ec-ai-review-count');
  var errEl = modal.querySelector('[data-ec-ai-error]');
  var goBtn = modal.querySelector('[data-ec-ai-go]');
  var cancelBtn = modal.querySelector('[data-ec-ai-cancel]');
  var label = btn.textContent;
  var busy = false;

  function showError(msg) {
    if (errEl) errEl.textContent = msg || '';
  }
  function closeModal() {
    modal.classList.remove('is-open');
    modal.hidden = true;
    showError('');
  }
  function openModal() {
    showError('');
    if (countEl && !countEl.value) countEl.value = '5';
    modal.hidden = false;
    modal.classList.add('is-open');
    if (countEl) countEl.focus();
  }
  function parsedCount() {
    var raw = countEl ? String(countEl.value).trim() : '';
    if (!/^[1-9]\d*$/.test(raw)) return 0;
    var n = parseInt(raw, 10);
    if (n < 1 || n > 50) return 0;
    return n;
  }
  function finish(next, reloadAnyway) {
    busy = false;
    btn.disabled = false;
    btn.textContent = label;
    goBtn.disabled = false;
    goBtn.textContent = 'Generate';
    if (next) {
      try {
        var a = document.createElement('a');
        a.href = next;
        if (a.pathname === location.pathname) {
          location.href = next;
          return;
        }
        location.href = next;
        return;
      } catch (err) {
        location.href = next;
        return;
      }
    }
    if (reloadAnyway) location.reload();
  }

  btn.addEventListener('click', function (e) {
    e.preventDefault();
    if (busy || btn.disabled) return;
    openModal();
  });
  cancelBtn.addEventListener('click', function () {
    if (busy) return;
    closeModal();
  });
  modal.addEventListener('click', function (e) {
    if (e.target === modal && !busy) closeModal();
  });
  countEl.addEventListener('keydown', function (e) {
    if (e.key === 'Enter') {
      e.preventDefault();
      goBtn.click();
    }
    if (e.key === 'Escape' && !busy) closeModal();
  });
  document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape' && modal.classList.contains('is-open') && !busy) closeModal();
  });
  goBtn.addEventListener('click', function () {
    if (busy) return;
    var n = parsedCount();
    if (!n) {
      showError('Enter a whole number between 1 and 50.');
      return;
    }
    var url = btn.getAttribute('data-url');
    var id = btn.getAttribute('data-product-id');
    if (!url || !id) return;
    busy = true;
    btn.disabled = true;
    btn.textContent = 'Generating AI content and reviews…';
    goBtn.disabled = true;
    goBtn.textContent = 'Generating…';
    showError('Generating AI content and reviews…');
    var body = new FormData();
    body.append('product_id', id);
    body.append('review_count', String(n));
    fetch(url, { method: 'POST', body: body, credentials: 'same-origin' })
      .then(function (r) {
        return r.json().then(function (data) {
          return { httpOk: r.ok, data: data };
        }).catch(function () {
          return { httpOk: false, data: null };
        });
      })
      .then(function (res) {
        var data = res.data || {};
        if (!data.ok) {
          showError(data.error || 'AI generation failed.');
          var saved = !!data.content_saved;
          if (saved) {
            window.alert(data.error || 'Product content was updated, but reviews could not be generated.');
            closeModal();
            finish(data.url || '', true);
            return;
          }
          busy = false;
          btn.disabled = false;
          btn.textContent = label;
          goBtn.disabled = false;
          goBtn.textContent = 'Generate';
          return;
        }
        closeModal();
        window.alert('AI content and reviews regenerated successfully.');
        finish(data.url || '', true);
      })
      .catch(function () {
        showError('AI generation failed.');
        busy = false;
        btn.disabled = false;
        btn.textContent = label;
        goBtn.disabled = false;
        goBtn.textContent = 'Generate';
      });
  });
})();
</script>
<?php endif; ?>
