<?php
$items = isset($trust_items) ? $trust_items : array();
if (empty($items)) {
    return;
}
?>
<ul class="pdp-new-trust">
  <?php foreach ($items as $item): ?>
    <li>
      <span class="pdp-new-trust__icon" aria-hidden="true">
        <?php if ($item['key'] === 'delivery'): ?>
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M1 5h13v10H1z"/><path d="M14 9h4l4 4v2h-8z"/><circle cx="5.5" cy="17.5" r="1.8"/><circle cx="18" cy="17.5" r="1.8"/></svg>
        <?php elseif ($item['key'] === 'returns'): ?>
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M4 7h9a5 5 0 1 1 0 10H8"/><path d="M8 4 4 7l4 3"/></svg>
        <?php elseif ($item['key'] === 'secure'): ?>
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="5" y="11" width="14" height="9" rx="2"/><path d="M8 11V8a4 4 0 0 1 8 0v3"/></svg>
        <?php else: ?>
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M4 17v-1a4 4 0 0 1 4-4h0a4 4 0 0 1 4 4v1"/><circle cx="8" cy="8" r="3"/><path d="M16 20v-1a3.5 3.5 0 0 1 3-3.45"/><circle cx="18.5" cy="8.5" r="2.4"/></svg>
        <?php endif; ?>
      </span>
      <span>
        <b><?= htmlspecialchars($item['title']) ?></b>
        <small><?= htmlspecialchars($item['text']) ?></small>
      </span>
    </li>
  <?php endforeach; ?>
</ul>
