<?php
$preview = isset($preview) ? $preview : array();
$this->load->view('inspinia/admin/product_analyzer/_nav', array('section' => 'research'));
?>
    <div class="row">
        <div class="col-md-6">
            <div class="ibox">
                <div class="ibox-title"><h5>Research workflow</h5></div>
                <div class="ibox-content">
                    <ol>
                        <li>Export a research sheet keyed by Product ID.</li>
                        <li>Fill cheaper supplier options against the same Product ID.</li>
                        <li>Preview the import. Matching rows update existing products only.</li>
                        <li>Preferred supplier is never changed automatically.</li>
                    </ol>
                    <a href="<?= base_url('admin/product-analyzer/export/research') ?>" class="btn btn-primary">Export research sheet</a>
                    <a href="<?= base_url('admin/product-analyzer/export/research-results') ?>" class="btn btn-white">Export results</a>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="ibox">
                <div class="ibox-title"><h5>Import supplier research</h5></div>
                <div class="ibox-content">
                    <p class="text-muted">Required: <code>product_id</code>, <code>supplier_name</code> or <code>new_supplier</code>, <code>product_cost</code>, <code>shipping_cost</code>. Unknown Product IDs are skipped.</p>
                    <form method="post" action="<?= base_url('admin/product-analyzer/import-preview') ?>" enctype="multipart/form-data">
                        <div class="form-group">
                            <input type="file" name="file" class="form-control" accept=".csv,text/csv" required>
                        </div>
                        <button type="submit" class="btn btn-success">Preview import</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <?php if ($preview): ?>
    <div class="ibox">
        <div class="ibox-title"><h5>Import preview</h5></div>
        <div class="ibox-content">
            <div class="table-responsive">
                <table class="table table-striped table-condensed">
                    <thead>
                        <tr>
                            <th>Row</th>
                            <th>Action</th>
                            <th>Product ID</th>
                            <th>Product</th>
                            <th>New supplier</th>
                            <th>Cost</th>
                            <th>Ship</th>
                            <th>Saving</th>
                            <th>Issues</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($preview as $row): ?>
                        <tr class="<?= $row['action'] === 'add_or_update' ? '' : 'warning' ?>">
                            <td><?= (int) $row['row'] ?></td>
                            <td><?= htmlspecialchars($row['action']) ?></td>
                            <td><?= htmlspecialchars($row['product_id']) ?></td>
                            <td><?= htmlspecialchars($row['product_name']) ?></td>
                            <td><?= htmlspecialchars($row['new_supplier']) ?></td>
                            <td><?= htmlspecialchars($row['product_cost']) ?></td>
                            <td><?= htmlspecialchars($row['shipping_cost']) ?></td>
                            <td><?= $row['impact'] ? number_format($row['impact']['savings'], 2) : '—' ?></td>
                            <td><?= htmlspecialchars(implode(' ', $row['issues'])) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <form method="post" action="<?= base_url('admin/product-analyzer/import-confirm') ?>" class="form-inline">
                <button type="submit" class="btn btn-primary">Import supplier options</button>
                <a href="<?= base_url('admin/product-analyzer/import-cancel') ?>" class="btn btn-white">Cancel</a>
            </form>
        </div>
    </div>
    <?php endif; ?>

    <div class="ibox">
        <div class="ibox-title"><h5>Products with cheaper suppliers</h5></div>
        <div class="ibox-content">
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Product ID</th>
                            <th>Product</th>
                            <th>Current supplier</th>
                            <th>Recommended</th>
                            <th>Saving</th>
                            <th>Options</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($cheaper as $item): ?>
                        <tr>
                            <td><code><?= htmlspecialchars($item->analyzer_id) ?></code></td>
                            <td><?= htmlspecialchars($item->name) ?></td>
                            <td><?= htmlspecialchars($item->supplier_name) ?></td>
                            <td><?= $item->recommended_supplier ? htmlspecialchars($item->recommended_supplier->supplier_name) : '—' ?></td>
                            <td class="text-navy"><?= profit_money($item->supplier_saving, $item->currency) ?></td>
                            <td><?= (int) $item->cheaper_count ?></td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($cheaper)): ?>
                        <tr><td colspan="6" class="text-center text-muted">No cheaper supplier options recorded yet.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="ibox">
        <div class="ibox-title"><h5>Products with one supplier only</h5></div>
        <div class="ibox-content">
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Product ID</th>
                            <th>Product</th>
                            <th>Supplier</th>
                            <th>Landed cost</th>
                            <th>Net profit</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($without as $item): ?>
                        <tr>
                            <td><code><?= htmlspecialchars($item->analyzer_id) ?></code></td>
                            <td><?= htmlspecialchars($item->name) ?></td>
                            <td><?= htmlspecialchars($item->supplier_name) ?></td>
                            <td><?= profit_money($item->metrics['landed_cost'], $item->currency) ?></td>
                            <td><?= profit_money($item->metrics['net_profit'], $item->currency) ?></td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($without)): ?>
                        <tr><td colspan="5" class="text-center text-muted">Every product already has more than one supplier option.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
