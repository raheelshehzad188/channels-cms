(function () {
  'use strict';

  var root = document.querySelector('[data-pdp-new]');
  if (!root) return;

  function qs(sel, el) { return (el || root).querySelector(sel); }
  function qsa(sel, el) { return Array.prototype.slice.call((el || root).querySelectorAll(sel)); }
  function ui(key, fallback) {
    if (window.STORE_UI && window.STORE_UI[key]) return window.STORE_UI[key];
    return fallback;
  }

  var variations = [];
  try { variations = JSON.parse(root.getAttribute('data-variations') || '[]'); } catch (e) { variations = []; }
  window.pdpSetVariationPrice = function (id, listed, formatted) {
    var pid = parseInt(id, 10);
    variations.forEach(function (v) {
      if (parseInt(v.id, 10) === pid) {
        v.price = listed;
        v.price_formatted = formatted;
      }
    });
  };
  var selectedId = parseInt(root.getAttribute('data-selected-id') || '0', 10);

  function variationById(id) {
    id = parseInt(id, 10);
    for (var i = 0; i < variations.length; i++) {
      if (parseInt(variations[i].id, 10) === id) return variations[i];
    }
    return null;
  }

  function initGallery() {
    var gallery = qs('[data-pdp-gallery]');
    if (!gallery) return;
    var main = qs('[data-pdp-main]', gallery);
    var thumbsWrap = qs('[data-pdp-thumbs]', gallery);

    function thumbs() { return qsa('[data-pdp-thumb]', gallery); }
    function currentIndex() {
      var list = thumbs();
      for (var i = 0; i < list.length; i++) {
        if (list[i].classList.contains('is-active')) return i;
      }
      return 0;
    }
    function show(i) {
      var list = thumbs();
      if (!list.length || !main) return;
      i = (i + list.length) % list.length;
      list.forEach(function (t, idx) { t.classList.toggle('is-active', idx === i); });
      var img = list[i].querySelector('img');
      if (img) {
        main.src = img.getAttribute('data-full') || img.src;
        main.alt = img.alt || '';
      }
    }
    gallery.addEventListener('click', function (e) {
      var thumb = e.target.closest('[data-pdp-thumb]');
      if (thumb) {
        var list = thumbs();
        show(list.indexOf(thumb));
      }
    });
    var prev = qs('[data-pdp-prev]', gallery);
    var next = qs('[data-pdp-next]', gallery);
    if (prev) prev.addEventListener('click', function () { show(currentIndex() - 1); });
    if (next) next.addEventListener('click', function () { show(currentIndex() + 1); });
    var zoom = qs('[data-pdp-zoom]', gallery);
    if (zoom) {
      zoom.addEventListener('click', function () {
        var on = gallery.classList.toggle('is-zoomed');
        zoom.setAttribute('aria-pressed', String(on));
      });
    }
    var videoBtn = qs('[data-pdp-video]', gallery);
    if (videoBtn) {
      videoBtn.addEventListener('click', function () {
        var src = videoBtn.getAttribute('data-pdp-video');
        if (!src) return;
        var modal = document.createElement('div');
        modal.className = 'pdp-new-modal is-open';
        modal.innerHTML = '<iframe src="' + src.replace(/"/g, '') + '?autoplay=1" allow="autoplay; encrypted-media" allowfullscreen></iframe>';
        modal.addEventListener('click', function () { modal.remove(); });
        document.body.appendChild(modal);
      });
    }

    function bindBroken(img) {
      if (!img || img._pdpErr) return;
      img._pdpErr = true;
      img.addEventListener('error', function () {
        var thumb = img.closest('[data-pdp-thumb]');
        if (thumb) thumb.remove();
        var left = thumbs();
        gallery.classList.toggle('has-thumbs', left.length > 1);
        if (main && left.length && (!main.getAttribute('src') || main.style.visibility === 'hidden')) {
          var nextImg = left[0].querySelector('img');
          if (nextImg) {
            main.src = nextImg.getAttribute('data-full') || nextImg.src;
            main.style.visibility = '';
          }
        }
      });
    }
    thumbs().forEach(function (thumb) {
      bindBroken(thumb.querySelector('img'));
    });
    if (main) bindBroken(main);

    gallery._rebuild = function (urls, alt) {
      urls = (urls || []).filter(Boolean);
      if (!thumbsWrap) {
        thumbsWrap = document.createElement('div');
        thumbsWrap.className = 'pdp-new-gallery__thumbs';
        thumbsWrap.setAttribute('data-pdp-thumbs', '');
        gallery.appendChild(thumbsWrap);
      }
      thumbsWrap.hidden = urls.length < 2;
      thumbsWrap.innerHTML = '';
      urls.forEach(function (src, i) {
        var b = document.createElement('button');
        b.type = 'button';
        b.className = 'pdp-new-gallery__thumb' + (i === 0 ? ' is-active' : '');
        b.setAttribute('data-pdp-thumb', '');
        b.setAttribute('aria-label', ui('product.show_image', 'Show image').replace('{n}', String(i + 1)));
        b.innerHTML = '<img src="' + src.replace(/"/g, '&quot;') + '" data-full="' + src.replace(/"/g, '&quot;') + '" alt="">';
        thumbsWrap.appendChild(b);
        bindBroken(b.querySelector('img'));
      });
      var many = thumbs().length > 1;
      gallery.classList.toggle('has-thumbs', many);
      if (prev) prev.hidden = !many;
      if (next) next.hidden = !many;
      if (main && urls[0]) {
        main.src = urls[0];
        main.style.visibility = '';
        if (alt) main.alt = alt;
      }
    };
  }

  function applyVariation(item, pushUrl) {
    if (!item) return;
    selectedId = parseInt(item.id, 10);
    var price = qs('[data-pdp-price]');
    var compare = qs('[data-pdp-compare]');
    var stock = qs('[data-pdp-stock]');
    var sku = qs('[data-pdp-sku]');
    var form = qs('[data-pdp-cart]');
    var qty = qs('[data-pdp-qty] input');
    var add = qs('[data-pdp-add]');
    var buy = qs('[data-pdp-buy]');
    var profit = document.querySelector('[data-ec-profit]');
    if (profit && item.profit_url) profit.href = item.profit_url;
    var profitMain = document.querySelector('[data-ec-profit-main]');
    if (profitMain && item.profit_url) profitMain.href = item.profit_url;
    if (price) price.textContent = item.price_formatted;
    if (compare) {
      compare.textContent = item.compare_formatted || '';
      compare.classList.toggle('is-hidden', !item.compare_formatted);
    }
    var sale = qs('[data-pdp-sale]');
    var offerBadge = qs('[data-pdp-offer-badge]');
    var offerSave = qs('[data-pdp-offer-save]');
    var offerPct = qs('[data-pdp-offer-pct]');
    if (item.offer && item.offer.badge && offerBadge) {
      offerBadge.textContent = item.offer.badge;
      offerBadge.classList.remove('is-hidden');
      if (sale) sale.classList.add('is-hidden');
    } else if (sale) {
      var pct = parseInt(item.sale_percent, 10) || 0;
      sale.textContent = pct > 0 ? ('-' + pct + '%') : '';
      sale.classList.toggle('is-hidden', pct < 1);
      if (offerBadge) offerBadge.classList.add('is-hidden');
    }
    if (offerSave) {
      if (item.offer && item.offer.save_formatted) {
        offerSave.textContent = ui('product.offer_save', 'Save {amount}').replace('{amount}', item.offer.save_formatted);
        offerSave.classList.remove('is-hidden');
      } else {
        offerSave.classList.add('is-hidden');
      }
    }
    if (offerPct) {
      if (item.offer && item.offer.label) {
        offerPct.textContent = item.offer.label;
        offerPct.classList.remove('is-hidden');
      } else {
        offerPct.classList.add('is-hidden');
      }
    }
    var offerShip = qs('[data-pdp-offer-ship]');
    if (offerShip) {
      if (item.offer && item.offer.free_shipping) {
        offerShip.textContent = ui('product.offer_free_shipping', '🚚 FREE SHIPPING');
        offerShip.classList.remove('is-hidden');
      } else {
        offerShip.classList.add('is-hidden');
      }
    }
    syncOfferCountdown(item.offer || null);
    if (stock) {
      stock.textContent = item.in_stock ? ui('product.in_stock', 'In stock') : ui('product.out_of_stock', 'Out of stock');
      stock.classList.toggle('is-in', !!item.in_stock);
      stock.classList.toggle('is-out', !item.in_stock);
    }
    if (sku) sku.textContent = item.sku || '';
    var parentTitle = (root.getAttribute('data-parent-title') || '').trim();
    var displayName = parentTitle || item.full_name || item.name || '';
    var title = qs('[data-pdp-title]');
    if (title && parentTitle) {
      title.textContent = parentTitle;
    }
    var crumb = qs('[data-pdp-crumb]');
    if (crumb && parentTitle) {
      crumb.textContent = parentTitle;
    }
    var share = qs('[data-pdp-share]');
    if (share && displayName) {
      share.setAttribute('data-share-title', displayName);
    }
    var shortEl = qs('[data-pdp-short]');
    var shortBody = qs('[data-pdp-short-body]');
    if (shortEl && item.short_html !== undefined) {
      if (shortBody) {
        shortBody.innerHTML = item.short_html || '';
      } else {
        shortEl.innerHTML = item.short_html || '';
      }
      shortEl.classList.toggle('is-hidden', !item.short_html);
      shortEl.classList.remove('is-open', 'is-clamped');
      if (root._pdpShortApply) root._pdpShortApply();
    }
    if (form && item.add_url) form.setAttribute('action', item.add_url);
    var hid = qs('[data-pdp-product-id]');
    if (hid) hid.value = String(selectedId);
    root.setAttribute('data-selected-id', String(selectedId));
    if (qty) {
      qty.max = String(Math.max(1, item.qty_max || 1));
      if (parseInt(qty.value, 10) > item.qty_max) qty.value = String(Math.max(1, item.qty_max || 1));
      qty.disabled = item.qty_max < 1;
    }
    if (add) add.disabled = item.qty_max < 1;
    if (buy) buy.disabled = item.qty_max < 1;
    qsa('.pdp-new-pack').forEach(function (card) {
      var input = card.querySelector('input');
      var on = input && parseInt(input.value, 10) === selectedId;
      card.classList.toggle('is-selected', on);
      if (input) input.checked = on;
    });
    syncAxisButtons(item);
    var gallery = qs('[data-pdp-gallery]');
    if (gallery && gallery._rebuild) {
      var urls = [];
      var seen = {};
      function addUrl(src) {
        src = String(src || '').trim();
        if (!src || seen[src]) return;
        seen[src] = true;
        urls.push(src);
      }
      addUrl(item.image);
      (item.gallery || []).forEach(addUrl);
      if (urls.length) {
        gallery._rebuild(urls, displayName || item.name);
      }
    }
    var delivery = qs('[data-pdp-delivery]');
    if (delivery && (item.delivery_working || item.delivery_short)) {
      var esc = function (s) {
        return String(s || '').replace(/[&<>"]/g, function (c) {
          return ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;' })[c];
        });
      };
      var line = delivery.querySelector('.delivery__eta span') || delivery.querySelector('p.shipping-eta');
      if (line) {
        var working = item.delivery_working || '';
        var eta = item.delivery_eta || '';
        if (line.tagName === 'P') {
          line.textContent = working
            ? (working + (eta ? ' ' + eta : ''))
            : (item.delivery_text || item.delivery_short || '');
        } else {
          line.innerHTML = esc(working) + (eta ? '<small>' + esc(eta) + '</small>' : '');
        }
      }
    }
    if (pushUrl && item.url && window.history && window.history.replaceState) {
      window.history.replaceState({}, '', item.url);
    }
  }

  function variationAttrs(item) {
    return (item && item.attrs && typeof item.attrs === 'object') ? item.attrs : {};
  }

  function findVariationByAttrs(sel) {
    for (var i = 0; i < variations.length; i++) {
      var attrs = variationAttrs(variations[i]);
      var ok = true;
      Object.keys(sel).forEach(function (key) {
        if (String(attrs[key] || '') !== String(sel[key] || '')) ok = false;
      });
      if (ok) return variations[i];
    }
    return null;
  }

  function currentAxisSelection() {
    var sel = {};
    qsa('[data-pdp-axis]').forEach(function (axis) {
      var name = axis.getAttribute('data-pdp-axis') || '';
      var on = axis.querySelector('[data-pdp-axis-value].is-selected');
      if (name && on) sel[name] = on.getAttribute('data-pdp-axis-value') || '';
    });
    return sel;
  }

  function syncAxisButtons(item) {
    var wrap = qs('[data-pdp-axes]');
    if (!wrap) return;
    var attrs = variationAttrs(item);
    qsa('[data-pdp-axis]', wrap).forEach(function (axis) {
      var name = axis.getAttribute('data-pdp-axis') || '';
      qsa('[data-pdp-axis-value]', axis).forEach(function (btn) {
        var on = String(attrs[name] || '') === String(btn.getAttribute('data-pdp-axis-value') || '');
        btn.classList.toggle('is-selected', on);
        btn.setAttribute('aria-pressed', String(on));
      });
    });
    var axes = qsa('[data-pdp-axis]', wrap);
    if (axes.length < 2) return;
    var colorAxis = axes[0];
    var sizeAxis = axes[1];
    var colorName = colorAxis.getAttribute('data-pdp-axis') || '';
    var sizeName = sizeAxis.getAttribute('data-pdp-axis') || '';
    var colorVal = String(attrs[colorName] || '');
    qsa('[data-pdp-axis-value]', sizeAxis).forEach(function (btn) {
      var probe = {};
      probe[colorName] = colorVal;
      probe[sizeName] = btn.getAttribute('data-pdp-axis-value') || '';
      var match = findVariationByAttrs(probe);
      var missing = !match;
      btn.classList.toggle('is-disabled', missing);
      btn.disabled = missing;
    });
  }

  function initAxisPicker() {
    var wrap = qs('[data-pdp-axes]');
    if (!wrap) return;
    qsa('[data-pdp-axis-value]', wrap).forEach(function (btn) {
      btn.addEventListener('click', function () {
        var axis = btn.closest('[data-pdp-axis]');
        if (!axis || btn.disabled) return;
        var name = axis.getAttribute('data-pdp-axis') || '';
        var sel = currentAxisSelection();
        sel[name] = btn.getAttribute('data-pdp-axis-value') || '';
        var item = findVariationByAttrs(sel);
        if (!item) {
          var axes = qsa('[data-pdp-axis]', wrap);
          var other = axes.filter(function (el) { return el !== axis; })[0];
          if (other) {
            var otherName = other.getAttribute('data-pdp-axis') || '';
            var otherBtns = qsa('[data-pdp-axis-value]', other);
            for (var i = 0; i < otherBtns.length; i++) {
              var probe = {};
              probe[name] = sel[name];
              probe[otherName] = otherBtns[i].getAttribute('data-pdp-axis-value') || '';
              item = findVariationByAttrs(probe);
              if (item) break;
            }
          }
        }
        applyVariation(item, true);
      });
    });
    syncAxisButtons(variationById(selectedId));
  }

  function initVariations() {
    qsa('[data-pdp-variation]').forEach(function (input) {
      input.addEventListener('change', function () {
        applyVariation(variationById(input.value), true);
      });
    });
    qsa('.pdp-new-pack').forEach(function (card) {
      card.addEventListener('click', function () {
        var input = card.querySelector('input');
        if (!input || input.disabled) return;
        input.checked = true;
        applyVariation(variationById(input.value), true);
      });
    });
    initAxisPicker();
    var form = qs('[data-pdp-cart]');
    if (form) {
      form.addEventListener('submit', function () {
        var checked = qs('[data-pdp-variation]:checked');
        var item = variationById(checked ? checked.value : selectedId);
        if (!item) return;
        applyVariation(item, false);
      });
    }
  }

  function initQty() {
    qsa('[data-pdp-qty]').forEach(function (wrap) {
      var input = wrap.querySelector('input');
      if (!input) return;
      wrap.addEventListener('click', function (e) {
        var btn = e.target.closest('button');
        if (!btn) return;
        var max = parseInt(input.max || '10', 10) || 10;
        var value = parseInt(input.value, 10) || 1;
        value += btn.getAttribute('data-step') === 'down' ? -1 : 1;
        if (value < 1) value = 1;
        if (value > max) value = max;
        input.value = String(value);
      });
    });
  }

  function initTabs() {
    var tabs = qsa('[data-pdp-tab]');
    if (!tabs.length) return;
    function open(name) {
      tabs.forEach(function (tab) {
        var on = tab.getAttribute('data-pdp-tab') === name;
        tab.classList.toggle('is-active', on);
        tab.setAttribute('aria-selected', String(on));
      });
      qsa('[data-pdp-panel]').forEach(function (panel) {
        panel.classList.toggle('is-active', panel.getAttribute('data-pdp-panel') === name);
      });
      if (name === 'description' && root._pdpDescApply) {
        root._pdpDescApply();
      }
    }
    tabs.forEach(function (tab) {
      tab.addEventListener('click', function () { open(tab.getAttribute('data-pdp-tab')); });
    });
    qsa('[data-pdp-opentab]').forEach(function (link) {
      link.addEventListener('click', function (e) {
        e.preventDefault();
        open(link.getAttribute('data-pdp-opentab'));
        var panel = qs('[data-pdp-panel="' + link.getAttribute('data-pdp-opentab') + '"]');
        if (panel) panel.scrollIntoView({ behavior: 'smooth', block: 'start' });
      });
    });
  }

  function initFaqs() {
    qsa('[data-pdp-acc] .pdp-new-acc__q').forEach(function (btn) {
      btn.addEventListener('click', function () {
        var item = btn.closest('.pdp-new-acc__item');
        var open = !item.classList.contains('is-open');
        qsa('[data-pdp-acc] .pdp-new-acc__item').forEach(function (other) {
          other.classList.remove('is-open');
          var q = other.querySelector('.pdp-new-acc__q');
          var a = other.querySelector('.pdp-new-acc__a');
          if (q) q.setAttribute('aria-expanded', 'false');
          if (a) a.hidden = true;
        });
        if (open) {
          item.classList.add('is-open');
          btn.setAttribute('aria-expanded', 'true');
          var panel = item.querySelector('.pdp-new-acc__a');
          if (panel) panel.hidden = false;
        }
      });
    });
  }

  function initShare() {
    var btn = qs('[data-pdp-share]');
    if (!btn) return;
    btn.addEventListener('click', function () {
      var url = btn.getAttribute('data-share-url') || window.location.href;
      var title = btn.getAttribute('data-share-title') || document.title;
      if (navigator.share) {
        navigator.share({ title: title, url: url }).catch(function () {});
        return;
      }
      if (navigator.clipboard && navigator.clipboard.writeText) {
        navigator.clipboard.writeText(url).then(function () {
          btn.lastChild && (btn.lastChild.textContent = ' Link copied');
        });
      }
    });
  }

  function initWish() {
    var link = qs('[data-pdp-wish]');
    if (!link) return;
    link.addEventListener('click', function (e) {
      if (link.getAttribute('href') === '#') return;
      e.preventDefault();
      var url = link.getAttribute('href') + (link.getAttribute('href').indexOf('?') === -1 ? '?' : '&') + 'ajax=1';
      fetch(url, { credentials: 'same-origin' }).then(function (r) { return r.json(); }).then(function (res) {
        if (!res || !res.ok) return;
        link.classList.toggle('is-on', !!res.added);
        var label = qs('[data-pdp-wish-label]', link);
        if (label) label.textContent = res.added ? ui('product.in_wishlist', 'In Wishlist') : ui('product.add_wishlist', 'Add to Wishlist');
      }).catch(function () { window.location.href = link.getAttribute('href'); });
    });
  }

  function initRelatedRail() {
    var rail = qs('[data-pdp-rail]');
    if (!rail) return;
    var prev = qs('[data-pdp-rail-prev]');
    var next = qs('[data-pdp-rail-next]');
    function amount() {
      var card = rail.querySelector('.pdp-new-card');
      if (!card) return Math.round(rail.clientWidth * 0.8);
      var styles = window.getComputedStyle(rail);
      var gap = parseFloat(styles.columnGap || styles.gap) || 14;
      return card.getBoundingClientRect().width + gap;
    }
    function sync() {
      var max = rail.scrollWidth - rail.clientWidth - 4;
      var overflow = rail.scrollWidth > rail.clientWidth + 8;
      if (prev) {
        prev.hidden = !overflow;
        prev.disabled = rail.scrollLeft <= 4;
      }
      if (next) {
        next.hidden = !overflow;
        next.disabled = rail.scrollLeft >= max;
      }
    }
    if (prev) prev.addEventListener('click', function () { rail.scrollBy({ left: -amount(), behavior: 'smooth' }); });
    if (next) next.addEventListener('click', function () { rail.scrollBy({ left: amount(), behavior: 'smooth' }); });
    rail.addEventListener('scroll', sync, { passive: true });
    window.addEventListener('resize', sync);
    sync();
  }

  function initDescClamp() {
    var wrap = qs('[data-pdp-desc]');
    if (!wrap) return;
    var clip = wrap.querySelector('.pdp-new-desc__clip');
    var body = wrap.querySelector('.pdp-new-desc__body');
    var btn = wrap.querySelector('[data-pdp-desc-toggle]') || qs('[data-pdp-desc-toggle]');
    var label = btn ? btn.querySelector('span') : null;
    if (btn && btn.parentNode !== wrap) wrap.appendChild(btn);
    if (!clip || !btn) return;
    function limit() {
      return window.matchMedia('(max-width: 720px)').matches ? 260 : 340;
    }
    function contentHeight() {
      return (body || clip).scrollHeight;
    }
    function apply() {
      if (wrap.classList.contains('is-open')) {
        btn.hidden = false;
        return;
      }
      var tall = contentHeight() > limit() + 24;
      if (wrap.classList.contains('is-clamped') !== tall) {
        wrap.classList.toggle('is-clamped', tall);
      }
      btn.hidden = !tall;
      btn.setAttribute('aria-expanded', 'false');
      if (label) label.textContent = ui('product.show_more', 'Show more');
    }
    btn.addEventListener('click', function () {
      var open = wrap.classList.toggle('is-open');
      wrap.classList.toggle('is-clamped', !open);
      if (label) label.textContent = open ? ui('product.show_less', 'Show less') : ui('product.show_more', 'Show more');
      btn.setAttribute('aria-expanded', String(open));
      btn.hidden = false;
    });
    apply();
    qsa('img', wrap).forEach(function (img) {
      if (!img.complete) img.addEventListener('load', apply);
    });
    if (typeof ResizeObserver !== 'undefined') {
      var ro = new ResizeObserver(function () { apply(); });
      ro.observe(body || clip);
    }
    window.addEventListener('resize', apply);
    window.addEventListener('load', apply);
    root._pdpDescApply = apply;
  }

  function initShortClamp() {
    var wrap = qs('[data-pdp-short]');
    if (!wrap) return;
    var clip = wrap.querySelector('.pdp-new-info__short-clip');
    var body = wrap.querySelector('[data-pdp-short-body]') || wrap.querySelector('.pdp-new-info__short-body');
    var btn = wrap.querySelector('[data-pdp-short-toggle]');
    var label = btn ? btn.querySelector('span') : null;
    if (!clip || !btn) return;
    function limit() {
      return window.matchMedia('(max-width: 720px)').matches ? 110 : 140;
    }
    function contentHeight() {
      return (body || clip).scrollHeight;
    }
    function apply() {
      if (wrap.classList.contains('is-hidden') || !String((body || clip).innerHTML || '').trim()) {
        wrap.classList.remove('is-clamped', 'is-open');
        btn.hidden = true;
        return;
      }
      if (wrap.classList.contains('is-open')) {
        wrap.classList.remove('is-clamped');
        btn.hidden = false;
        btn.setAttribute('aria-expanded', 'true');
        if (label) label.textContent = ui('product.show_less', 'Show less');
        return;
      }
      var tall = contentHeight() > limit() + 16;
      wrap.classList.toggle('is-clamped', tall);
      btn.hidden = !tall;
      btn.setAttribute('aria-expanded', 'false');
      if (label) label.textContent = ui('product.show_more', 'Show more');
    }
    btn.addEventListener('click', function () {
      var open = wrap.classList.toggle('is-open');
      wrap.classList.toggle('is-clamped', !open);
      if (label) label.textContent = open ? ui('product.show_less', 'Show less') : ui('product.show_more', 'Show more');
      btn.setAttribute('aria-expanded', String(open));
      btn.hidden = false;
    });
    apply();
    if (typeof ResizeObserver !== 'undefined') {
      var ro = new ResizeObserver(function () { apply(); });
      ro.observe(body || clip);
    }
    window.addEventListener('resize', apply);
    window.addEventListener('load', apply);
    root._pdpShortApply = apply;
  }

  var offerTimer = null;
  function offerRootEl() {
    return qs('[data-pdp-offer-root]') || root;
  }
  function pad2(n) { return (n < 10 ? '0' : '') + n; }
  function formatCountdown(ms) {
    if (ms <= 0) return null;
    var s = Math.floor(ms / 1000);
    var d = Math.floor(s / 86400); s -= d * 86400;
    var h = Math.floor(s / 3600); s -= h * 3600;
    var m = Math.floor(s / 60); s -= m * 60;
    return pad2(d) + 'd ' + pad2(h) + 'h ' + pad2(m) + 'm ' + pad2(s) + 's';
  }
  function expireOfferUi() {
    var ore = offerRootEl();
    var badge = qs('[data-pdp-offer-badge]');
    var save = qs('[data-pdp-offer-save]');
    var pct = qs('[data-pdp-offer-pct]');
    var cd = qs('[data-pdp-countdown]');
    var urgency = ore.querySelector('.pdp-new-offer-urgency');
    var compare = qs('[data-pdp-compare]');
    var price = qs('[data-pdp-price]');
    var original = ore.getAttribute('data-offer-original') || '';
    if (badge) badge.classList.add('is-hidden');
    if (save) save.classList.add('is-hidden');
    if (pct) pct.classList.add('is-hidden');
    if (cd) cd.classList.add('is-hidden');
    if (urgency) urgency.classList.add('is-hidden');
    if (compare) {
      compare.textContent = '';
      compare.classList.add('is-hidden');
    }
    if (price && original) price.textContent = original;
    setTimeout(function () { window.location.reload(); }, 800);
  }
  function syncOfferCountdown(offer) {
    if (offerTimer) {
      clearInterval(offerTimer);
      offerTimer = null;
    }
    var ore = offerRootEl();
    var cd = qs('[data-pdp-countdown]');
    var val = qs('[data-pdp-countdown-value]');
    if (!offer || !offer.show_countdown || !offer.ends_at_ts) {
      if (cd) cd.classList.add('is-hidden');
      return;
    }
    if (cd) cd.classList.remove('is-hidden');
    ore.setAttribute('data-offer-ends', String(offer.ends_at_ts));
    if (offer.original_formatted) {
      ore.setAttribute('data-offer-original', offer.original_formatted);
    }
    function tick() {
      var ends = parseInt(ore.getAttribute('data-offer-ends'), 10) || 0;
      var left = ends * 1000 - Date.now();
      var text = formatCountdown(left);
      if (!text) {
        if (val) val.textContent = '00d 00h 00m 00s';
        if (offerTimer) {
          clearInterval(offerTimer);
          offerTimer = null;
        }
        expireOfferUi();
        return;
      }
      if (val) val.textContent = text;
    }
    tick();
    offerTimer = setInterval(tick, 1000);
  }
  function initOfferCountdownFromDom() {
    var ore = offerRootEl();
    var ends = parseInt(ore.getAttribute('data-offer-ends'), 10) || 0;
    if (ends > 0) {
      syncOfferCountdown({ show_countdown: true, ends_at_ts: ends });
    }
  }

  initGallery();
  initVariations();
  var initialVariation = variationById(selectedId);
  if (initialVariation) {
    applyVariation(initialVariation, false);
  }
  initQty();
  initTabs();
  initFaqs();
  initShare();
  initWish();
  initRelatedRail();
  initDescClamp();
  initShortClamp();
  initOfferCountdownFromDom();
})();
