/* ==========================================================================
   ZENVello — shared component behaviour
   Star rendering, drawer, wishlist, cart counter, newsletter, accordions, tabs
   ========================================================================== */
(function () {
  'use strict';

  /* ---------------- Stars ----------------
     <span class="stars" data-stars="4.5"></span> -> 5 svg stars, half supported */
  var STAR_FULL =
    '<svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="m12 2.6 2.9 5.9 6.5.9-4.7 4.6 1.1 6.4-5.8-3-5.8 3 1.1-6.4L2.6 9.4l6.5-.9z"/></svg>';

  function halfStar(id) {
    return (
      '<svg viewBox="0 0 24 24" aria-hidden="true"><defs><linearGradient id="' + id + '">' +
      '<stop offset="50%" stop-color="currentColor"/><stop offset="50%" stop-color="#d9dde3"/>' +
      '</linearGradient></defs><path fill="url(#' + id + ')" d="m12 2.6 2.9 5.9 6.5.9-4.7 4.6 1.1 6.4-5.8-3-5.8 3 1.1-6.4L2.6 9.4l6.5-.9z"/></svg>'
    );
  }

  var starUid = 0;

  function renderStars(el) {
    var value = parseFloat(el.getAttribute('data-stars')) || 0;
    var html = '';
    for (var i = 1; i <= 5; i++) {
      if (value >= i - 0.25) {
        html += STAR_FULL;
      } else if (value >= i - 0.75) {
        html += halfStar('half-' + starUid++);
      } else {
        html += STAR_FULL.replace('<svg', '<svg class="is-empty"');
      }
    }
    el.innerHTML = html;
    if (!el.getAttribute('aria-label')) {
      el.setAttribute('role', 'img');
      el.setAttribute('aria-label', value + ' out of 5 stars');
    }
  }

  function initStars(scope) {
    (scope || document).querySelectorAll('[data-stars]').forEach(renderStars);
  }

  /* ---------------- Mobile drawer ---------------- */
  function initDrawer() {
    var drawer = document.getElementById('drawer');
    var scrim = document.querySelector('.scrim');
    if (!drawer) return;

    function open() {
      drawer.classList.add('is-open');
      if (scrim) scrim.classList.add('is-open');
      document.body.style.overflow = 'hidden';
    }
    function close() {
      drawer.classList.remove('is-open');
      if (scrim) scrim.classList.remove('is-open');
      document.body.style.overflow = '';
    }

    document.querySelectorAll('[data-drawer-open]').forEach(function (b) {
      b.addEventListener('click', function (e) {
        // "All Categories" acts as a menu trigger on every viewport
        e.preventDefault();
        open();
      });
    });
    document.querySelectorAll('[data-drawer-close]').forEach(function (b) {
      b.addEventListener('click', close);
    });
    document.addEventListener('keydown', function (e) {
      if (e.key === 'Escape') close();
    });
  }

  /* ---------------- Cart counter ---------------- */
  function initCart() {
    var badges = document.querySelectorAll('[data-cart-count]');
    var count = 0;

    function paint() {
      badges.forEach(function (b) { b.textContent = String(count); });
    }

    document.addEventListener('click', function (e) {
      var btn = e.target.closest('[data-add-to-cart]');
      if (!btn) return;
      count += 1;
      paint();
      var original = btn.dataset.label || btn.innerHTML;
      btn.dataset.label = original;
      btn.innerHTML = '&#10003; Added';
      btn.style.background = '#1a8f4a';
      btn.style.color = '#fff';
      window.setTimeout(function () {
        btn.innerHTML = original;
        btn.style.background = '';
        btn.style.color = '';
      }, 1200);
    });
  }

  /* ---------------- Wishlist hearts ---------------- */
  function initWishlist() {
    document.addEventListener('click', function (e) {
      var btn = e.target.closest('[data-wish]');
      if (!btn) return;
      e.preventDefault();
      btn.classList.toggle('is-on');
      btn.setAttribute('aria-pressed', btn.classList.contains('is-on') ? 'true' : 'false');
    });
  }

  /* ---------------- Accordion ---------------- */
  function initAccordions() {
    document.querySelectorAll('[data-accordion] .acc__head').forEach(function (head) {
      head.addEventListener('click', function () {
        var item = head.closest('.acc__item');
        var isOpen = item.classList.contains('is-open');
        item.classList.toggle('is-open', !isOpen);
        head.setAttribute('aria-expanded', String(!isOpen));
      });
    });
  }

  /* ---------------- Tabs ---------------- */
  function initTabs() {
    document.querySelectorAll('[data-tabs]').forEach(function (wrap) {
      var tabs = wrap.querySelectorAll('[data-tab]');
      tabs.forEach(function (tab) {
        tab.addEventListener('click', function (e) {
          e.preventDefault();
          tabs.forEach(function (t) {
            t.classList.remove('is-active');
            t.setAttribute('aria-selected', 'false');
          });
          tab.classList.add('is-active');
          tab.setAttribute('aria-selected', 'true');
          var target = document.querySelector(tab.getAttribute('href'));
          if (target) {
            var top = target.getBoundingClientRect().top + window.pageYOffset - 120;
            window.scrollTo({ top: top, behavior: 'smooth' });
          }
        });
      });
    });
  }

  /* ---------------- Collapsible filter groups ---------------- */
  function initFilterGroups() {
    document.querySelectorAll('.fgroup__head').forEach(function (head) {
      head.addEventListener('click', function () {
        var group = head.closest('.fgroup');
        var collapsed = group.classList.toggle('is-collapsed');
        head.setAttribute('aria-expanded', String(!collapsed));
      });
    });
  }

  /* ---------------- Demo forms ----------------
     There is no backend in this build, so the forms confirm inline
     instead of navigating away. */
  function initForms() {
    var forms = document.querySelectorAll('[data-contact], [data-track], [data-signin], [data-postcode]');
    forms.forEach(function (form) {
      form.addEventListener('submit', function (e) {
        e.preventDefault();
        var btn = form.querySelector('button[type="submit"], button:not([type])');
        if (!btn) return;
        var label = btn.textContent;
        btn.textContent = form.hasAttribute('data-postcode') ? 'Available' : 'Sent';
        btn.disabled = true;
        window.setTimeout(function () {
          btn.textContent = label;
          btn.disabled = false;
        }, 2200);
      });
    });
  }

  /* ---------------- Filter panel (mobile) ---------------- */
  function initFilterPanel() {
    document.querySelectorAll('[data-filters-toggle]').forEach(function (btn) {
      btn.addEventListener('click', function () {
        var wrap = btn.closest('.filters');
        var open = wrap.classList.toggle('is-open');
        btn.setAttribute('aria-expanded', String(open));
      });
    });
  }

  /* ---------------- Newsletter (demo only) ---------------- */
  function initNewsletter() {
    document.querySelectorAll('[data-newsletter]').forEach(function (form) {
      form.addEventListener('submit', function (e) {
        e.preventDefault();
        var btn = form.querySelector('button');
        var input = form.querySelector('input');
        btn.textContent = 'Subscribed';
        input.value = '';
        input.placeholder = 'Thanks for subscribing!';
        window.setTimeout(function () {
          btn.textContent = 'Subscribe';
          input.placeholder = 'Your email address';
        }, 2500);
      });
    });
  }

  /* ---------------- Boot ---------------- */
  function boot() {
    initStars();
    initDrawer();
    initCart();
    initWishlist();
    initAccordions();
    initTabs();
    initFilterGroups();
    initFilterPanel();
    initForms();
    initNewsletter();
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', boot);
  } else {
    boot();
  }

  window.ZEN = { initStars: initStars };
})();
