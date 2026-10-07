<?php
$itemStatus = !empty($item->fulfillment_status) ? $item->fulfillment_status : 'pending';
$companies = isset($shipping_companies) ? $shipping_companies : Ec_order_model::shipping_companies();
$actionUrl = base_url('admin/orders/item_status/' . (int) $item->id);
$redirectTo = isset($redirect) ? $redirect : '';
$canAct = !empty($can_process);
$isAdminUser = !empty($is_admin);
?>
<?php if (!empty($item->supplier_order_no)): ?>
    <div class="small text-muted">Supplier #<?= htmlspecialchars($item->supplier_order_no) ?></div>
<?php endif; ?>
<?php if (!empty($item->tracking_number)): ?>
    <div class="small"><?= htmlspecialchars(trim((isset($item->shipping_company) ? $item->shipping_company . ' · ' : '') . $item->tracking_number)) ?></div>
<?php endif; ?>
<?php if ($canAct && $itemStatus === 'refund_requested'): ?>
    <form method="post" action="<?= $actionUrl ?>" style="display:inline">
        <?php if ($redirectTo !== ''): ?><input type="hidden" name="redirect" value="<?= htmlspecialchars($redirectTo) ?>"><?php endif; ?>
        <input type="hidden" name="status" value="refunded">
        <button type="submit" class="btn btn-xs btn-warning">Approve refund</button>
    </form>
    <form method="post" action="<?= $actionUrl ?>" style="display:inline">
        <?php if ($redirectTo !== ''): ?><input type="hidden" name="redirect" value="<?= htmlspecialchars($redirectTo) ?>"><?php endif; ?>
        <input type="hidden" name="action" value="reject_refund">
        <button type="submit" class="btn btn-xs btn-white">Decline</button>
    </form>
<?php elseif ($canAct && !$isAdminUser && ($itemStatus === 'pending' || $itemStatus === 'processing')): ?>
    <form method="post" action="<?= $actionUrl ?>" class="form-inline" style="margin:0">
        <?php if ($redirectTo !== ''): ?><input type="hidden" name="redirect" value="<?= htmlspecialchars($redirectTo) ?>"><?php endif; ?>
        <input type="hidden" name="status" value="dispatching">
        <input type="text" name="supplier_order_no" class="form-control input-sm" placeholder="AliExpress / supplier order no" required style="width:180px;display:inline-block;margin:2px 4px 2px 0">
        <button type="submit" class="btn btn-xs btn-primary">Dispatch</button>
    </form>
<?php elseif ($canAct && !$isAdminUser && $itemStatus === 'dispatching'): ?>
    <form method="post" action="<?= $actionUrl ?>" class="form-inline" style="margin:0">
        <?php if ($redirectTo !== ''): ?><input type="hidden" name="redirect" value="<?= htmlspecialchars($redirectTo) ?>"><?php endif; ?>
        <input type="hidden" name="status" value="shipped">
        <input type="text" name="tracking_number" class="form-control input-sm" placeholder="Tracking number" required style="width:140px;display:inline-block;margin:2px 4px 2px 0">
        <select name="shipping_company" class="form-control input-sm" required style="width:170px;display:inline-block;margin:2px 4px 2px 0">
            <option value="">Shipping company</option>
            <?php foreach ($companies as $code => $label): ?>
                <option value="<?= htmlspecialchars($code) ?>"><?= htmlspecialchars($label) ?></option>
            <?php endforeach; ?>
        </select>
        <input type="text" name="shipping_company_other" class="form-control input-sm" placeholder="Other company" style="width:130px;display:inline-block;margin:2px 4px 2px 0">
        <button type="submit" class="btn btn-xs btn-primary">Ship</button>
    </form>
<?php elseif ($canAct && !$isAdminUser && $itemStatus === 'shipped'): ?>
    <form method="post" action="<?= $actionUrl ?>" style="display:inline">
        <?php if ($redirectTo !== ''): ?><input type="hidden" name="redirect" value="<?= htmlspecialchars($redirectTo) ?>"><?php endif; ?>
        <input type="hidden" name="status" value="delivered">
        <button type="submit" class="btn btn-xs btn-primary">Mark delivered</button>
    </form>
<?php elseif ($canAct && $isAdminUser && $itemStatus === 'delivered'): ?>
    <form method="post" action="<?= $actionUrl ?>" style="display:inline">
        <?php if ($redirectTo !== ''): ?><input type="hidden" name="redirect" value="<?= htmlspecialchars($redirectTo) ?>"><?php endif; ?>
        <input type="hidden" name="status" value="completed">
        <button type="submit" class="btn btn-xs btn-primary">Complete</button>
    </form>
<?php elseif ($itemStatus === 'delivered' && !$isAdminUser): ?>
    <span class="text-muted small">Waiting for Super Admin</span>
<?php endif; ?>
