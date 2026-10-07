<?php
if (!function_exists('storefront_admin_can_see_pricing') || !storefront_admin_can_see_pricing()) {
    return;
}
$listing = isset($listing) ? $listing : (isset($product) ? $product : null);
$storeObj = isset($store) ? $store : null;
$asMain = !empty($as_main);
$breakdown = function_exists('storefront_admin_price_breakdown')
    ? storefront_admin_price_breakdown($listing, $storeObj)
    : null;
if (!$breakdown || empty($breakdown['rows'])) {
    return;
}
$calcJson = htmlspecialchars(json_encode($breakdown), ENT_QUOTES, 'UTF-8');
if (!empty($silent)) {
    echo '<span hidden data-ec-calc="' . $calcJson . '"></span>';
    return;
}
if (empty($GLOBALS['ec_price_calc_assets'])) {
    $GLOBALS['ec_price_calc_assets'] = 1;
    ?>
<style>
.ec-see-calc{
  display:inline-flex;align-items:center;margin:0;padding:4px 10px;border:0;border-radius:6px;
  background:#1d2327;color:#00b9eb;cursor:pointer;white-space:nowrap;
  font:600 11px/1.2 -apple-system,BlinkMacSystemFont,"Segoe UI",sans-serif;
  vertical-align:middle;
}
.ec-see-calc:hover{color:#fff;background:#2271b1;}
.ec-profit-btn{
  display:inline-flex;align-items:center;margin:0;padding:4px 10px;border-radius:6px;
  background:#00a32a;color:#fff !important;text-decoration:none;white-space:nowrap;
  font:600 11px/1.2 -apple-system,BlinkMacSystemFont,"Segoe UI",sans-serif;
  vertical-align:middle;
}
.ec-profit-btn:hover{background:#008a20;color:#fff !important;}
.ec-admin-price-actions{display:inline-flex;flex-wrap:wrap;gap:6px;align-items:center;}
.pdp-new-info__price{align-items:center;flex-wrap:wrap;}
.pdp-new-pack__prices{align-items:center;flex-wrap:wrap;gap:6px;}
.pdp-child-block{display:flex;flex-direction:column;align-items:stretch;gap:6px;width:max-content;}
.pdp-new-pack-slot{display:flex;flex-direction:column;align-items:stretch;min-width:0;}
.pdp-new-pack-slot>.pdp-new-pack{flex:1;}
.ec-price-menu{position:relative;display:flex;justify-content:center;width:100%;margin-top:2px;}
.ec-price-menu__icon{
  width:28px;height:28px;padding:0;border:0;border-radius:999px;cursor:pointer;
  background:#1d2327;color:#00b9eb;display:inline-flex;align-items:center;justify-content:center;
}
.ec-price-menu__icon:hover{background:#2271b1;color:#fff;}
.ec-price-menu__icon svg{width:16px;height:16px;display:block;}
.ec-price-menu__pop{
  position:absolute;z-index:30;top:calc(100% + 6px);left:50%;transform:translateX(-50%);
  display:flex;flex-direction:column;gap:6px;min-width:148px;
  background:#fff;border:1px solid #dcdcde;border-radius:8px;padding:8px;
  box-shadow:0 10px 28px rgba(0,0,0,.18);
}
.ec-price-menu__pop[hidden]{display:none !important;}
.ec-price-menu__pop .ec-see-calc,
.ec-price-menu__pop .ec-profit-btn{width:100%;justify-content:center;box-sizing:border-box;}
.ec-calc-modal{position:fixed;inset:0;z-index:100050;display:flex;align-items:center;justify-content:center;padding:18px;}
.ec-calc-modal[hidden]{display:none !important;}
.ec-calc-modal__bg{position:absolute;inset:0;background:rgba(0,0,0,.55);}
.ec-calc-modal__box{
  position:relative;width:min(440px,100%);max-height:min(86vh,720px);overflow:auto;
  background:#1d2327;color:#f0f0f1;border-radius:10px;padding:18px 18px 16px;
  font:13px/1.45 -apple-system,BlinkMacSystemFont,"Segoe UI",sans-serif;
  box-shadow:0 18px 50px rgba(0,0,0,.4);
}
.ec-calc-modal__title{margin:0 28px 4px 0;font-size:15px;font-weight:700;color:#00b9eb;}
.ec-calc-modal__name{margin:0 0 12px;opacity:.8;font-size:12px;}
.ec-calc-modal__close{
  position:absolute;top:10px;right:10px;width:32px;height:32px;border:0;border-radius:6px;
  background:transparent;color:#fff;font-size:22px;line-height:32px;cursor:pointer;
}
.ec-calc-modal__close:hover{background:#3c434a;}
.ec-calc-modal__row{display:flex;justify-content:space-between;gap:12px;margin:4px 0;}
.ec-calc-modal__row dt{opacity:.88;font-weight:400;}
.ec-calc-modal__row dd{margin:0;font-variant-numeric:tabular-nums;white-space:nowrap;}
.ec-calc-modal__row.is-total{margin-top:8px;padding-top:8px;border-top:1px solid #3c434a;font-weight:700;}
.ec-calc-modal__sum{margin:10px 0 0;opacity:.75;font-size:12px;word-break:break-word;}
.ec-calc-modal__extra{margin-top:14px;padding-top:12px;border-top:1px solid #3c434a;}
.ec-calc-modal__extra label{display:block;font-size:11px;font-weight:700;letter-spacing:.04em;text-transform:uppercase;color:#00b9eb;margin-bottom:6px;}
.ec-calc-modal__extra-row{display:flex;gap:8px;}
.ec-calc-modal__extra input{
  flex:1;min-width:0;height:36px;border:1px solid #3c434a;border-radius:6px;background:#2c3338;color:#fff;
  padding:0 10px;font:13px/36px inherit;
}
.ec-calc-modal__save{
  height:36px;padding:0 14px;border:0;border-radius:6px;background:#2271b1;color:#fff;font-weight:700;cursor:pointer;
}
.ec-calc-modal__save:hover{background:#135e96;}
.ec-calc-modal__save:disabled{opacity:.6;cursor:wait;}
.ec-calc-modal__hint{margin:6px 0 0;opacity:.7;font-size:11px;}
.ec-calc-modal__msg{margin:8px 0 0;font-size:12px;min-height:1em;}
.ec-calc-modal__msg.is-ok{color:#68de7c;}
.ec-calc-modal__msg.is-err{color:#ff8085;}
</style>
<div class="ec-calc-modal" id="ec-calc-modal" hidden>
  <div class="ec-calc-modal__bg" data-ec-calc-close></div>
  <div class="ec-calc-modal__box" role="dialog" aria-modal="true" aria-labelledby="ec-calc-title">
    <button type="button" class="ec-calc-modal__close" data-ec-calc-close aria-label="Close">&times;</button>
    <h3 class="ec-calc-modal__title" id="ec-calc-title">Price calculation</h3>
    <p class="ec-calc-modal__name" id="ec-calc-name"></p>
    <dl id="ec-calc-rows"></dl>
    <p class="ec-calc-modal__sum" id="ec-calc-formula"></p>
    <div class="ec-calc-modal__extra">
      <label for="ec-calc-extra">Extra amount (plus or minus)</label>
      <div class="ec-calc-modal__extra-row">
        <input id="ec-calc-extra" type="number" step="0.01" name="extra_amount">
        <button type="button" class="ec-calc-modal__save" id="ec-calc-save">Save</button>
      </div>
      <p class="ec-calc-modal__hint">Added after base price, ecommerce plus, platform fee and store plus.</p>
      <p class="ec-calc-modal__msg" id="ec-calc-msg"></p>
    </div>
  </div>
</div>
<script>
(function () {
  if (window.__ecCalcModal) return;
  window.__ecCalcModal = true;
  var modal = document.getElementById('ec-calc-modal');
  var rowsEl = document.getElementById('ec-calc-rows');
  var formulaEl = document.getElementById('ec-calc-formula');
  var nameEl = document.getElementById('ec-calc-name');
  var extraEl = document.getElementById('ec-calc-extra');
  var saveEl = document.getElementById('ec-calc-save');
  var msgEl = document.getElementById('ec-calc-msg');
  var current = null;

  function parseCalc(el) {
    try { return JSON.parse(el.getAttribute('data-ec-calc') || '{}'); } catch (e) { return null; }
  }
  function currentCalc(btn) {
    if (btn.closest('.pdp-new-info__price, .pdp__price')) {
      var checked = document.querySelector('[data-pdp-variation]:checked');
      if (checked) {
        var pack = checked.closest('.pdp-new-pack');
        var packScope = pack ? (pack.closest('.pdp-new-pack-slot') || pack) : null;
        var packBtn = packScope && packScope.querySelector('[data-ec-calc]');
        if (packBtn) return parseCalc(packBtn);
      }
      var sel = document.querySelector('.pdp-child.is-selected');
      var childBtn = sel && sel.parentNode && sel.parentNode.querySelector('[data-ec-calc]');
      if (childBtn) return parseCalc(childBtn);
    }
    return btn ? parseCalc(btn) : null;
  }
  function render(data) {
    current = data;
    if (!data) return;
    nameEl.textContent = data.name || '';
    rowsEl.innerHTML = '';
    (data.rows || []).forEach(function (row) {
      var wrap = document.createElement('div');
      wrap.className = 'ec-calc-modal__row' + (row.total ? ' is-total' : '');
      var dt = document.createElement('dt');
      dt.textContent = row.label || '';
      var dd = document.createElement('dd');
      dd.textContent = row.amount_formatted || row.amount;
      wrap.appendChild(dt);
      wrap.appendChild(dd);
      rowsEl.appendChild(wrap);
    });
    formulaEl.textContent = data.formula || '';
    extraEl.value = (data.extra_amount != null ? data.extra_amount : 0);
    msgEl.textContent = '';
    msgEl.className = 'ec-calc-modal__msg';
  }
  function open(data) {
    render(data);
    modal.hidden = false;
    extraEl.focus();
  }
  function close() {
    modal.hidden = true;
  }
  function updateLive(data) {
    if (!data || !data.product_id) return;
    var id = String(data.product_id);
    document.querySelectorAll('[data-ec-calc]').forEach(function (btn) {
      var d = parseCalc(btn);
      if (d && String(d.product_id) === id) {
        btn.setAttribute('data-ec-calc', JSON.stringify(data));
      }
    });
    var packInput = document.querySelector('.pdp-new-pack input[value="' + id + '"]');
    if (packInput) {
      var priceEl = packInput.closest('.pdp-new-pack').querySelector('.pdp-new-pack__price');
      if (priceEl && data.listed_formatted) priceEl.textContent = data.listed_formatted;
    }
    var childLink = document.querySelector('.pdp-child[data-child-id="' + id + '"]');
    if (childLink) {
      var childPrice = childLink.querySelector('.pdp-child__price');
      if (childPrice && data.listed_formatted) childPrice.textContent = data.listed_formatted;
    }
    var checked = document.querySelector('[data-pdp-variation]:checked');
    var main = document.querySelector('[data-pdp-price]');
    if (main && data.listed_formatted && (!checked || checked.value === id)) {
      main.textContent = data.listed_formatted;
    }
    var oldNow = document.querySelector('.pdp__price-now');
    if (oldNow && data.listed_formatted && (!checked || checked.value === id)) {
      oldNow.textContent = data.listed_formatted;
    }
    if (typeof window.pdpSetVariationPrice === 'function') {
      window.pdpSetVariationPrice(data.product_id, data.listed, data.listed_formatted);
    }
  }
  document.addEventListener('click', function (e) {
    var menuBtn = e.target.closest('[data-ec-price-menu]');
    if (menuBtn) {
      e.preventDefault();
      e.stopPropagation();
      var pop = menuBtn.parentNode.querySelector('.ec-price-menu__pop');
      var shouldOpen = pop && pop.hidden;
      document.querySelectorAll('.ec-price-menu__pop').forEach(function (el) { el.hidden = true; });
      if (pop && shouldOpen) pop.hidden = false;
      return;
    }
    if (!e.target.closest('.ec-price-menu') || e.target.closest('[data-ec-calc]')) {
      document.querySelectorAll('.ec-price-menu__pop').forEach(function (el) { el.hidden = true; });
    }
    var closeBtn = e.target.closest('[data-ec-calc-close]');
    if (closeBtn) {
      e.preventDefault();
      close();
      return;
    }
    var btn = e.target.closest('[data-ec-calc]');
    if (!btn || btn.tagName === 'SPAN') return;
    e.preventDefault();
    e.stopPropagation();
    open(currentCalc(btn));
  }, true);
  document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape' && modal && !modal.hidden) close();
  });
  saveEl.addEventListener('click', function () {
    if (!current) return;
    saveEl.disabled = true;
    msgEl.textContent = 'Saving…';
    msgEl.className = 'ec-calc-modal__msg';
    var body = new FormData();
    body.append('product_id', current.product_id);
    body.append('extra_amount', extraEl.value || '0');
    fetch(current.save_url || '/admin_bar/extra_amount', {
      method: 'POST',
      body: body,
      credentials: 'same-origin'
    }).then(function (r) { return r.json(); }).then(function (res) {
      saveEl.disabled = false;
      if (!res || !res.ok) {
        msgEl.textContent = (res && res.error) ? res.error : 'Could not save extra amount.';
        msgEl.className = 'ec-calc-modal__msg is-err';
        return;
      }
      var data = res.calc || res.breakdown || current;
      render(data);
      updateLive(data);
      msgEl.textContent = 'Saved. Selling price is now ' + (data.listed_formatted || data.listed) + '.';
      msgEl.className = 'ec-calc-modal__msg is-ok';
    }).catch(function () {
      saveEl.disabled = false;
      msgEl.textContent = 'Could not save extra amount.';
      msgEl.className = 'ec-calc-modal__msg is-err';
    });
  });
})();
</script>
    <?php
}
?>
<?php
$profitUrl = function_exists('storefront_admin_profitability_url')
    ? storefront_admin_profitability_url($listing, $storeObj)
    : '';
$asMenu = !empty($as_menu);
$calcBtn = '<button type="button" class="ec-see-calc' . ($asMain ? ' ec-see-calc--main' : '') . '" data-ec-calc="' . $calcJson . '"' . ($asMain ? ' data-ec-calc-main' : '') . '>See calculation</button>';
$profitBtn = '';
if ($profitUrl !== '') {
    $profitBtn = '<a class="ec-profit-btn" href="' . htmlspecialchars($profitUrl) . '"' . ($asMain ? ' data-ec-profit-main' : '') . ' onclick="event.stopPropagation();">Profitability</a>';
}
if ($asMenu):
?>
<span class="ec-price-menu">
  <button type="button" class="ec-price-menu__icon" data-ec-price-menu aria-label="Price tools">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><rect x="2" y="6" width="20" height="12" rx="2"/><circle cx="12" cy="12" r="2.5"/><path d="M6 9.5h.01M18 14.5h.01"/></svg>
  </button>
  <span class="ec-price-menu__pop" hidden>
    <?= $calcBtn ?>
    <?= $profitBtn ?>
  </span>
</span>
<?php else: ?>
<?= $calcBtn ?>
<?= $profitBtn ?>
<?php endif; ?>
