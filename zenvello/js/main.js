/* ==========================================================================
   ZENVello — page-level behaviour
   Hero slider, product gallery, quantity stepper, price range, sticky tabs
   ========================================================================== */
(function () {
  'use strict';

  /* ---------------- Hero slider ---------------- */
  function initSlider() {
    document.querySelectorAll('[data-slider]').forEach(function (root) {
      var track = root.querySelector('[data-slider-track]');
      var slides = track ? Array.prototype.slice.call(track.children) : [];
      var dotsWrap = root.querySelector('[data-slider-dots]');
      if (!track || slides.length < 2) return;

      var index = 0;
      var timer = null;

      var dots = slides.map(function (_, i) {
        var b = document.createElement('button');
        b.type = 'button';
        b.setAttribute('aria-label', 'Go to slide ' + (i + 1));
        b.addEventListener('click', function () { go(i); restart(); });
        if (dotsWrap) dotsWrap.appendChild(b);
        return b;
      });

      function paint() {
        track.style.transform = 'translateX(' + (-index * 100) + '%)';
        dots.forEach(function (d, i) { d.classList.toggle('is-active', i === index); });
        slides.forEach(function (s, i) { s.setAttribute('aria-hidden', String(i !== index)); });
      }

      function go(i) {
        index = (i + slides.length) % slides.length;
        paint();
      }

      function next() { go(index + 1); }
      function prev() { go(index - 1); }

      function start() { timer = window.setInterval(next, 6000); }
      function stop() { window.clearInterval(timer); }
      function restart() { stop(); start(); }

      var nextBtn = root.querySelector('[data-slider-next]');
      var prevBtn = root.querySelector('[data-slider-prev]');
      if (nextBtn) nextBtn.addEventListener('click', function () { next(); restart(); });
      if (prevBtn) prevBtn.addEventListener('click', function () { prev(); restart(); });

      root.addEventListener('mouseenter', stop);
      root.addEventListener('mouseleave', start);

      /* touch swipe */
      var startX = null;
      root.addEventListener('touchstart', function (e) { startX = e.touches[0].clientX; stop(); }, { passive: true });
      root.addEventListener('touchend', function (e) {
        if (startX === null) return;
        var dx = e.changedTouches[0].clientX - startX;
        if (Math.abs(dx) > 45) { dx < 0 ? next() : prev(); }
        startX = null;
        start();
      });

      paint();
      start();
    });
  }

  /* ---------------- Product gallery ---------------- */
  function initGallery() {
    var gallery = document.querySelector('[data-gallery]');
    if (!gallery) return;

    var main = gallery.querySelector('[data-gallery-main]');
    var thumbs = gallery.querySelectorAll('[data-gallery-thumb]');

    thumbs.forEach(function (thumb) {
      thumb.addEventListener('click', function () {
        thumbs.forEach(function (t) { t.classList.remove('is-active'); });
        thumb.classList.add('is-active');
        var img = thumb.querySelector('img');
        main.src = img.getAttribute('data-full') || img.src;
        main.alt = img.alt;
      });
    });

    /* zoom toggle */
    var zoomBtn = gallery.querySelector('[data-zoom]');
    if (zoomBtn) {
      zoomBtn.addEventListener('click', function () {
        var on = gallery.classList.toggle('is-zoomed');
        zoomBtn.setAttribute('aria-pressed', String(on));
      });
    }
  }

  /* ---------------- Quantity stepper ---------------- */
  function initQty() {
    document.querySelectorAll('[data-qty]').forEach(function (wrap) {
      var input = wrap.querySelector('input');
      wrap.querySelectorAll('button').forEach(function (btn) {
        btn.addEventListener('click', function () {
          var step = btn.dataset.step === 'down' ? -1 : 1;
          var value = Math.max(1, (parseInt(input.value, 10) || 1) + step);
          input.value = value;
        });
      });
    });
  }

  /* ---------------- Price range (dual handle) ---------------- */
  function initRange() {
    document.querySelectorAll('[data-range]').forEach(function (wrap) {
      var min = wrap.querySelector('[data-range-min]');
      var max = wrap.querySelector('[data-range-max]');
      var fill = wrap.querySelector('[data-range-fill]');
      var outMin = wrap.querySelector('[data-range-out-min]');
      var outMax = wrap.querySelector('[data-range-out-max]');
      if (!min || !max) return;

      function paint() {
        var lo = Math.min(+min.value, +max.value);
        var hi = Math.max(+min.value, +max.value);
        var span = +min.max - +min.min;
        if (fill) {
          fill.style.left = ((lo - +min.min) / span) * 100 + '%';
          fill.style.right = 100 - ((hi - +min.min) / span) * 100 + '%';
        }
        if (outMin) outMin.value = lo;
        if (outMax) outMax.value = hi;
        var label = wrap.querySelector('[data-range-label]');
        if (label) label.textContent = '£' + lo + ' - £' + hi;
      }

      [min, max].forEach(function (input) { input.addEventListener('input', paint); });
      [outMin, outMax].forEach(function (input) {
        if (!input) return;
        input.addEventListener('change', function () {
          if (input === outMin) min.value = input.value;
          else max.value = input.value;
          paint();
        });
      });
      paint();
    });
  }

  /* ---------------- Sticky tab highlight on the product page ---------------- */
  function initScrollSpy() {
    var tabs = document.querySelectorAll('[data-tabs] [data-tab]');
    if (!tabs.length || !('IntersectionObserver' in window)) return;

    var map = {};
    tabs.forEach(function (t) {
      var id = t.getAttribute('href');
      var section = document.querySelector(id);
      if (section) map[id] = { tab: t, section: section };
    });

    var observer = new IntersectionObserver(function (entries) {
      entries.forEach(function (entry) {
        if (!entry.isIntersecting) return;
        var id = '#' + entry.target.id;
        tabs.forEach(function (t) {
          var on = t.getAttribute('href') === id;
          t.classList.toggle('is-active', on);
          t.setAttribute('aria-selected', String(on));
        });
      });
    }, { rootMargin: '-130px 0px -70% 0px' });

    Object.keys(map).forEach(function (id) { observer.observe(map[id].section); });
  }

  /* ---------------- Horizontal "view all" rails ---------------- */
  function initRails() {
    document.querySelectorAll('[data-rail]').forEach(function (rail) {
      var prev = rail.parentElement.querySelector('[data-rail-prev]');
      var next = rail.parentElement.querySelector('[data-rail-next]');
      function step(dir) {
        rail.scrollBy({ left: dir * Math.round(rail.clientWidth * 0.8), behavior: 'smooth' });
      }
      if (prev) prev.addEventListener('click', function () { step(-1); });
      if (next) next.addEventListener('click', function () { step(1); });
    });
  }

  function boot() {
    initSlider();
    initGallery();
    initQty();
    initRange();
    initScrollSpy();
    initRails();
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', boot);
  } else {
    boot();
  }
})();
