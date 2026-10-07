<?php
if (empty($tracking) || !is_array($tracking)) {
    return;
}
$metaPixel = isset($tracking['meta_pixel_id']) ? $tracking['meta_pixel_id'] : '';
$ttPixel = isset($tracking['tiktok_pixel_id']) ? $tracking['tiktok_pixel_id'] : '';
if ($metaPixel === '' && $ttPixel === '') {
    return;
}
$event = isset($tracking['event']) ? $tracking['event'] : '';
$eventId = isset($tracking['event_id']) ? $tracking['event_id'] : '';
$currency = isset($tracking['currency']) ? $tracking['currency'] : 'USD';
$value = isset($tracking['value']) ? (float) $tracking['value'] : 0;
$ids = isset($tracking['content_ids']) && is_array($tracking['content_ids']) ? $tracking['content_ids'] : array();
$contentName = isset($tracking['content_name']) ? $tracking['content_name'] : '';
$orderId = isset($tracking['order_id']) ? $tracking['order_id'] : '';
$pageViewId = isset($tracking['pageview_event_id']) ? $tracking['pageview_event_id'] : '';
$contents = isset($tracking['contents']) && is_array($tracking['contents']) ? $tracking['contents'] : array();
$numItems = isset($tracking['num_items']) ? (int) $tracking['num_items'] : count($ids);
$contentsJs = $contents ? '  contents: ' . json_encode($contents) . ",\n" : '';
?>
<?php if ($metaPixel !== ''): ?>
<script>
!function(f,b,e,v,n,t,s){if(f.fbq)return;n=f.fbq=function(){n.callMethod?
n.callMethod.apply(n,arguments):n.queue.push(arguments)};if(!f._fbq)f._fbq=n;
n.push=n;n.loaded=!0;n.version='2.0';n.queue=[];t=b.createElement(e);t.async=!0;
t.src=v;s=b.getElementsByTagName(e)[0];s.parentNode.insertBefore(t,s)}(window, document,'script',
'https://connect.facebook.net/en_US/fbevents.js');
fbq('init', <?= json_encode($metaPixel) ?>);
<?php if ($pageViewId !== ''): ?>
fbq('track', 'PageView', {}, {eventID: <?= json_encode($pageViewId) ?>});
<?php else: ?>
fbq('track', 'PageView');
<?php endif; ?>
<?php if ($event === 'ViewContent'): ?>
fbq('track', 'ViewContent', {
  content_ids: <?= json_encode($ids) ?>,
  content_type: 'product',
  content_name: <?= json_encode($contentName) ?>,
  currency: <?= json_encode($currency) ?>,
  value: <?= json_encode($value) ?>,
<?= $contentsJs ?>}, {eventID: <?= json_encode($eventId) ?>});
<?php elseif ($event === 'AddToCart'): ?>
fbq('track', 'AddToCart', {
  content_ids: <?= json_encode($ids) ?>,
  content_type: 'product',
  content_name: <?= json_encode($contentName) ?>,
  currency: <?= json_encode($currency) ?>,
  value: <?= json_encode($value) ?>,
  num_items: <?= json_encode(max(1, $numItems)) ?>,
<?= $contentsJs ?>}, {eventID: <?= json_encode($eventId) ?>});
<?php elseif ($event === 'InitiateCheckout'): ?>
fbq('track', 'InitiateCheckout', {
  content_ids: <?= json_encode($ids) ?>,
  content_type: 'product',
  content_name: <?= json_encode($contentName) ?>,
  currency: <?= json_encode($currency) ?>,
  value: <?= json_encode($value) ?>,
  num_items: <?= json_encode(max(1, $numItems)) ?>,
<?= $contentsJs ?>}, {eventID: <?= json_encode($eventId) ?>});
<?php elseif ($event === 'Purchase'): ?>
fbq('track', 'Purchase', {
  content_ids: <?= json_encode($ids) ?>,
  content_type: 'product',
  currency: <?= json_encode($currency) ?>,
  value: <?= json_encode($value) ?>,
  num_items: <?= json_encode(max(1, $numItems)) ?>,
  order_id: <?= json_encode($orderId) ?>,
<?= $contentsJs ?>}, {eventID: <?= json_encode($eventId) ?>});
<?php endif; ?>
</script>
<noscript><img height="1" width="1" style="display:none" src="https://www.facebook.com/tr?id=<?= rawurlencode($metaPixel) ?>&ev=PageView&noscript=1" alt=""></noscript>
<?php endif; ?>

<?php if ($ttPixel !== ''): ?>
<script>
!function (w, d, t) {
  w.TiktokAnalyticsObject=t;var ttq=w[t]=w[t]||[];
  ttq.methods=["page","track","identify","instances","debug","on","off","once","ready","alias","group","enableCookie","disableCookie"];
  ttq.setAndDefer=function(t,e){t[e]=function(){t.push([e].concat(Array.prototype.slice.call(arguments,0)))}};
  for(var i=0;i<ttq.methods.length;i++)ttq.setAndDefer(ttq,ttq.methods[i]);
  ttq.instance=function(t){for(var e=ttq._i[t]||[],n=0;n<ttq.methods.length;n++)ttq.setAndDefer(e,ttq.methods[n]);return e};
  ttq.load=function(e,n){var i="https://analytics.tiktok.com/i18n/pixel/events.js";
  ttq._i=ttq._i||{};ttq._i[e]=[];ttq._i[e]._u=i;ttq._t=ttq._t||{};ttq._t[e]=+new Date;ttq._o=ttq._o||{};ttq._o[e]=n||{};
  var o=document.createElement("script");o.type="text/javascript";o.async=!0;o.src=i+"?sdkid="+e+"&lib="+t;
  var a=document.getElementsByTagName("script")[0];a.parentNode.insertBefore(o,a)};
  ttq.load(<?= json_encode($ttPixel) ?>);
  ttq.page();
<?php if ($event === 'ViewContent'): ?>
  ttq.track('ViewContent', {
    content_id: <?= json_encode(isset($ids[0]) ? $ids[0] : '') ?>,
    content_type: 'product',
    content_name: <?= json_encode($contentName) ?>,
    currency: <?= json_encode($currency) ?>,
    value: <?= json_encode($value) ?>
  }, { event_id: <?= json_encode($eventId) ?> });
<?php elseif ($event === 'AddToCart'): ?>
  ttq.track('AddToCart', {
    content_id: <?= json_encode(isset($ids[0]) ? $ids[0] : '') ?>,
    content_type: 'product',
    content_name: <?= json_encode($contentName) ?>,
    currency: <?= json_encode($currency) ?>,
    value: <?= json_encode($value) ?>
  }, { event_id: <?= json_encode($eventId) ?> });
<?php elseif ($event === 'InitiateCheckout'): ?>
  ttq.track('InitiateCheckout', {
    content_id: <?= json_encode(isset($ids[0]) ? $ids[0] : '') ?>,
    content_type: 'product',
    content_name: <?= json_encode($contentName) ?>,
    currency: <?= json_encode($currency) ?>,
    value: <?= json_encode($value) ?>
  }, { event_id: <?= json_encode($eventId) ?> });
<?php elseif ($event === 'Purchase'): ?>
  ttq.track('CompletePayment', {
    content_id: <?= json_encode($ids) ?>,
    content_type: 'product',
    currency: <?= json_encode($currency) ?>,
    value: <?= json_encode($value) ?>
  }, { event_id: <?= json_encode($eventId) ?> });
<?php endif; ?>
}(window, document, 'ttq');
</script>
<?php endif; ?>
