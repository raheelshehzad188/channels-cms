<?php
if (empty($store) || empty($store->id)) {
    return;
}
$page = isset($current_page) ? $current_page : '';
$productId = 0;
$productName = '';
if ($page === 'detail' && !empty($product)) {
    $productId = !empty($cart_product) ? (int) $cart_product->id : (int) $product->id;
    $productName = isset($product->name) ? $product->name : '';
}
$event = ($page === 'detail' && $productId) ? 'product_view' : 'page_view';
?>
<script>
(function () {
  var cfg = {
    endpoint: <?= json_encode(storefront_url('analytics/collect')) ?>,
    storeId: <?= (int) $store->id ?>,
    event: <?= json_encode($event) ?>,
    productId: <?= (int) $productId ?>,
    productName: <?= json_encode($productName) ?>,
    page: <?= json_encode($page) ?>
  };
  function uid() {
    return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function (c) {
      var r = Math.random() * 16 | 0, v = c === 'x' ? r : (r & 0x3 | 0x8);
      return v.toString(16);
    });
  }
  function cookie(name, value) {
    if (value) {
      document.cookie = name + '=' + encodeURIComponent(value) + ';path=/;max-age=' + (name === 'ec_vid' ? 31536000 : 86400) + ';SameSite=Lax';
      try { localStorage.setItem(name, value); } catch (e) {}
      return value;
    }
    var m = document.cookie.match(new RegExp('(?:^|; )' + name + '=([^;]*)'));
    if (m) return decodeURIComponent(m[1]);
    try { return localStorage.getItem(name) || ''; } catch (e) { return ''; }
  }
  function qs(name) {
    try { return new URLSearchParams(window.location.search).get(name) || ''; } catch (e) { return ''; }
  }
  var vid = cookie('ec_vid') || uid();
  var sid = cookie('ec_sid') || uid();
  cookie('ec_vid', vid);
  cookie('ec_sid', sid);
  var utm = {
    utm_source: qs('utm_source') || sessionStorage.getItem('ec_utm_source') || '',
    utm_medium: qs('utm_medium') || sessionStorage.getItem('ec_utm_medium') || '',
    utm_campaign: qs('utm_campaign') || sessionStorage.getItem('ec_utm_campaign') || '',
    utm_content: qs('utm_content') || sessionStorage.getItem('ec_utm_content') || ''
  };
  Object.keys(utm).forEach(function (k) { if (qs(k.replace('utm_', 'utm_')) || utm[k]) try { sessionStorage.setItem('ec_' + k, utm[k]); } catch (e) {} });
  if (qs('utm_source')) {
    try {
      sessionStorage.setItem('ec_utm_source', qs('utm_source'));
      sessionStorage.setItem('ec_utm_medium', qs('utm_medium'));
      sessionStorage.setItem('ec_utm_campaign', qs('utm_campaign'));
      sessionStorage.setItem('ec_utm_content', qs('utm_content'));
    } catch (e) {}
  }
  function send(type, extra) {
    extra = extra || {};
    var body = {
      event_type: type,
      visitor_id: vid,
      session_id: sid,
      store_id: cfg.storeId,
      product_id: extra.product_id || cfg.productId || 0,
      page_url: location.href,
      page_title: extra.page_title || cfg.productName || document.title,
      referrer: document.referrer || '',
      utm_source: utm.utm_source,
      utm_medium: utm.utm_medium,
      utm_campaign: utm.utm_campaign,
      utm_content: utm.utm_content,
      country_code: (navigator.language || '').split('-')[1] || ''
    };
    if (navigator.sendBeacon) {
      navigator.sendBeacon(cfg.endpoint, new Blob([JSON.stringify(body)], { type: 'application/json' }));
    } else {
      fetch(cfg.endpoint, { method: 'POST', body: JSON.stringify(body), keepalive: true, headers: { 'Content-Type': 'application/json' } }).catch(function () {});
    }
  }
  send(cfg.event);
  if (cfg.event === 'product_view') {
    send('page_view');
  }
  setInterval(function () { send('heartbeat'); }, 20000);
})();
</script>
