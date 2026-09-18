(function () {
  'use strict';

  var root = document.querySelector('[data-pdp-new]');
  if (!root) return;

  function qs(sel, el) { return (el || root).querySelector(sel); }
  function qsa(sel, el) { return Array.prototype.slice.call((el || root).querySelectorAll(sel)); }

  var variations = [];
  try { variations = JSON.parse(root.getAttribute('data-variations') || '[]'); } catch (e) { variations = []; }
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

    gallery._rebuild = function (urls, alt) {
      if (!thumbsWrap) return;
      thumbsWrap.innerHTML = '';
      (urls || []).forEach(function (src, i) {
        var b = document.createElement('button');
        b.type = 'button';
        b.className = 'pdp-new-gallery__thumb' + (i === 0 ? ' is-active' : '');
        b.setAttribute('data-pdp-thumb', '');
        b.innerHTML = '<img src="' + src + '" data-full="' + src + '" alt="">';
        thumbsWrap.appendChild(b);
      });
      gallery.classList.toggle('has-thumbs', (urls || []).length > 1);
      if (main && urls && urls[0]) {
        main.src = urls[0];
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
    if (price) price.textContent = item.price_formatted;
    if (compare) {
      compare.textContent = item.compare_formatted || '';
      compare.classList.toggle('is-hidden', !item.compare_formatted);
    }
    if (stock) {
      stock.textContent = item.in_stock ? 'In stock' : 'Out of stock';
      stock.classList.toggle('is-in', !!item.in_stock);
      stock.classList.toggle('is-out', !item.in_stock);
    }
    if (sku) sku.textContent = item.sku || '';
    if (form && item.add_url) form.setAttribute('action', item.add_url);
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
    var gallery = qs('[data-pdp-gallery]');
    if (gallery && gallery._rebuild && item.gallery && item.gallery.length) {
      gallery._rebuild(item.gallery, item.full_name || item.name);
    }
    var delivery = qs('[data-pdp-delivery]');
    if (delivery && item.delivery_short) {
      var esc = function (s) {
        return String(s || '').replace(/[&<>"]/g, function (c) {
          return ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;' })[c];
        });
      };
      var line = delivery.querySelector('.delivery__eta span') || delivery.querySelector('p.shipping-eta');
      if (line) {
        if (line.tagName === 'P') {
          line.textContent = item.delivery_text || item.delivery_short;
        } else {
          line.innerHTML = 'Estimated delivery: ' + esc(item.delivery_short) +
            (item.delivery_text ? '<small>' + esc(item.delivery_text) + '</small>' : '');
        }
      }
    }
    if (pushUrl && item.url && window.history && window.history.replaceState) {
      window.history.replaceState({}, '', item.url);
    }
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
        if (label) label.textContent = res.added ? 'In Wishlist' : 'Add to Wishlist';
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
      if (label) label.textContent = 'Show more';
    }
    btn.addEventListener('click', function () {
      var open = wrap.classList.toggle('is-open');
      wrap.classList.toggle('is-clamped', !open);
      if (label) label.textContent = open ? 'Show less' : 'Show more';
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

  initGallery();
  initVariations();
  initQty();
  initTabs();
  initFaqs();
  initShare();
  initWish();
  initRelatedRail();
  initDescClamp();
})();
