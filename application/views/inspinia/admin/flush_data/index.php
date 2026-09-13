<div class="row wrapper border-bottom white-bg page-heading">
    <div class="col-lg-10">
        <h2>Flush Data</h2>
        <ol class="breadcrumb">
            <li><a href="<?= base_url('admin/admin') ?>">Dashboard</a></li>
            <li class="active"><strong>Flush Data</strong></li>
        </ol>
    </div>
</div>
<div class="wrapper wrapper-content">
    <div class="row">
        <div class="col-lg-8">
            <div class="ibox">
                <div class="ibox-title"><h5>Truncate selected tables</h5></div>
                <div class="ibox-content">
                    <?php $this->load->view('flash'); ?>
                    <p class="text-muted">Check the data you want to empty, then submit. This cannot be undone. Your own super admin account is never deleted.</p>
                    <form method="post" action="<?= base_url('admin/flush-data/submit') ?>" id="flush-data-form">
                        <?php foreach ($groups as $key => $group): ?>
                        <div class="checkbox">
                            <label>
                                <input type="checkbox" name="flush[]" value="<?= htmlspecialchars($key) ?>">
                                <strong><?= htmlspecialchars($group['label']) ?></strong>
                                <span class="text-muted"> — <?= htmlspecialchars($group['hint']) ?></span>
                            </label>
                        </div>
                        <?php endforeach; ?>
                        <div class="hr-line-dashed"></div>
                        <button type="submit" class="btn btn-danger">Flush selected</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
$(function () {
    $('#flush-data-form').on('submit', function () {
        var labels = [];
        $(this).find('input[name="flush[]"]:checked').each(function () {
            labels.push($(this).closest('label').find('strong').text());
        });
        if (!labels.length) {
            alert('Select at least one item to flush.');
            return false;
        }
        return confirm('Permanently delete: ' + labels.join(', ') + '? This cannot be undone.');
    });
});
</script>
