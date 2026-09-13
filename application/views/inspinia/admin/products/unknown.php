<div class="row wrapper border-bottom white-bg page-heading">
    <div class="col-lg-8">
        <h2>Unknown Import Links</h2>
        <ol class="breadcrumb">
            <li><a href="<?= base_url('admin/admin') ?>">Dashboard</a></li>
            <li><a href="<?= base_url('admin/products') ?>">Products</a></li>
            <li class="active"><strong>Unknown links</strong></li>
        </ol>
    </div>
</div>
<div class="wrapper wrapper-content animated fadeInRight">
    <div class="row">
        <div class="col-lg-12">
            <div class="ibox float-e-margins">
                <div class="ibox-title"><h5>Domains without an importer class</h5></div>
                <div class="ibox-content">
                    <?php $this->load->view('flash'); ?>
                    <p class="text-muted">When an ecommerce user pastes a URL we cannot import yet, it is recorded here so a class can be added later.</p>
                    <div class="table-responsive">
                        <table class="table table-striped table-bordered">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Domain</th>
                                    <th>URL</th>
                                    <th>Country</th>
                                    <th>User</th>
                                    <th>Hits</th>
                                    <th>Last seen</th>
                                    <th width="90">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php if (empty($links)): ?>
                                <tr><td colspan="8" class="text-center">No unknown links yet.</td></tr>
                            <?php endif; ?>
                            <?php foreach ($links as $link): ?>
                                <tr>
                                    <td><?= (int) $link->id ?></td>
                                    <td><?= htmlspecialchars($link->domain) ?></td>
                                    <td><a href="<?= htmlspecialchars($link->url) ?>" target="_blank" rel="noopener"><?= htmlspecialchars($link->url) ?></a></td>
                                    <td><?= htmlspecialchars($link->country_name ?: '-') ?></td>
                                    <td><?= htmlspecialchars(trim($link->user_name) !== '' ? $link->user_name : ($link->uname ?: '-')) ?></td>
                                    <td><?= (int) $link->hit_count ?></td>
                                    <td><?= htmlspecialchars($link->last_seen_at) ?></td>
                                    <td>
                                        <a class="btn btn-xs btn-danger" href="<?= base_url('admin/products/unknown_delete/' . $link->id) ?>" onclick="return confirm('Remove this unknown link?');">Remove</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
