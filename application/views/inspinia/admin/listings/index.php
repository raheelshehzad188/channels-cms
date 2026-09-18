<?php
$stats = isset($stats) ? $stats : (object) array();
$currency = isset($platform_currency) ? $platform_currency : platform_currency();
$feePercent = isset($platform_fee_percent) ? (float) $platform_fee_percent : platform_fee_percent();
$filterCountryId = isset($country_id) ? (int) $country_id : 0;
$filterStoreId = isset($store_id) ? (int) $store_id : 0;
$filterQ = isset($q) ? $q : '';
$sort = isset($sort) ? $sort : 'id';
$dir = isset($dir) ? $dir : 'desc';
$listPage = isset($list_page) ? (int) $list_page : 1;
$listPerPage = isset($per_page) ? (int) $per_page : 25;
$listTotal = isset($total) ? (int) $total : 0;
$listTotalPages = isset($total_pages) ? max(1, (int) $total_pages) : 1;
$listFrom = isset($from_row) ? (int) $from_row : 0;
$listTo = isset($to_row) ? (int) $to_row : 0;
$listPerPageOptions = !empty($per_page_options) ? $per_page_options : array(10, 25, 50, 100);
$pageNumbers = isset($page_numbers) ? $page_numbers : array();
$firstPageUrl = isset($first_page_url) ? $first_page_url : base_url('admin/listings');
$prevPageUrl = isset($prev_page_url) ? $prev_page_url : $firstPageUrl;
$nextPageUrl = isset($next_page_url) ? $next_page_url : $firstPageUrl;
$lastPageUrl = isset($last_page_url) ? $last_page_url : $firstPageUrl;
$hasPrevPage = !empty($has_prev_page);
$hasNextPage = !empty($has_next_page);
$filterQuery = isset($filter_query) ? $filter_query : array();

$sortUrl = function ($column) use ($filterQuery, $sort, $dir) {
    $query = $filterQuery;
    $query['sort'] = $column;
    $query['dir'] = ($sort === $column && $dir === 'desc') ? 'asc' : 'desc';
    $query['page'] = 1;
    return base_url('admin/listings') . '?' . http_build_query($query);
};
$sortIcon = function ($column) use ($sort, $dir) {
    if ($sort !== $column) {
        return ' <i class="fa fa-sort text-muted"></i>';
    }
    return $dir === 'asc'
        ? ' <i class="fa fa-sort-asc text-navy"></i>'
        : ' <i class="fa fa-sort-desc text-navy"></i>';
};
?>
<style>
.listing-product { display:flex; align-items:center; gap:10px; min-width:220px; }
.listing-product img { width:52px; height:52px; object-fit:cover; border-radius:4px; border:1px solid #e7eaec; background:#f5f5f5; flex-shrink:0; }
.listing-product .listing-placeholder { width:52px; height:52px; border-radius:4px; background:#f5f5f5; border:1px solid #e7eaec; display:flex; align-items:center; justify-content:center; color:#ccc; flex-shrink:0; }
.listing-product a { color:#1ab394; font-weight:600; }
.listing-product a:hover { text-decoration:underline; }
.listing-th a { color:#676a6c; display:inline-block; }
.listing-th a:hover { color:#1ab394; }
.listing-stat h2 { margin:4px 0 2px; }
.listing-muted { color:#888; font-size:12px; }
.source-count-btn { cursor:pointer; }
</style>
<div class="row wrapper border-bottom white-bg page-heading">
    <div class="col-lg-8">
        <h2>Store Listings</h2>
        <ol class="breadcrumb">
            <li><a href="<?= base_url('admin/admin') ?>">Dashboard</a></li>
            <li class="active"><strong>Store Listings</strong></li>
        </ol>
    </div>
</div>
<div class="wrapper wrapper-content">
    <?php $this->load->view('flash'); ?>

    <div class="ibox">
        <div class="ibox-title"><h5>Filters</h5></div>
        <div class="ibox-content">
            <form method="get" action="<?= base_url('admin/listings') ?>" class="form-inline" id="listingFilterForm">
                <input type="hidden" name="page" value="1">
                <input type="hidden" name="sort" value="<?= htmlspecialchars($sort) ?>">
                <input type="hidden" name="dir" value="<?= htmlspecialchars($dir) ?>">
                <div class="form-group" style="margin-right:8px; margin-bottom:8px;">
                    <input type="text" name="q" class="form-control" placeholder="Search product, SKU, store…" value="<?= htmlspecialchars($filterQ) ?>" style="min-width:240px;">
                </div>
                <div class="form-group" style="margin-right:8px; margin-bottom:8px;">
                    <select name="country_id" id="listingFilterCountry" class="form-control">
                        <option value="0">All countries</option>
                        <?php foreach (!empty($countries) ? $countries : array() as $country): ?>
                            <option value="<?= (int) $country->id ?>" <?= $filterCountryId === (int) $country->id ? 'selected' : '' ?>><?= htmlspecialchars($country->name) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group" style="margin-right:8px; margin-bottom:8px;">
                    <select name="store_id" id="listingFilterStore" class="form-control">
                        <option value="0">All stores</option>
                        <?php foreach (!empty($stores) ? $stores : array() as $store): ?>
                            <option value="<?= (int) $store->id ?>" <?= $filterStoreId === (int) $store->id ? 'selected' : '' ?>><?= htmlspecialchars($store->name) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group" style="margin-right:8px; margin-bottom:8px;">
                    <select name="per_page" id="listingPerPage" class="form-control">
                        <?php foreach ($listPerPageOptions as $option): ?>
                            <option value="<?= (int) $option ?>" <?= $listPerPage === (int) $option ? 'selected' : '' ?>><?= (int) $option ?> per page</option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary" style="margin-bottom:8px;">Filter</button>
                <a href="<?= base_url('admin/listings') ?>" class="btn btn-white" style="margin-bottom:8px;">Reset</a>
            </form>
        </div>
    </div>

    <div class="row">
        <div class="col-sm-6 col-md-3">
            <div class="ibox listing-stat"><div class="ibox-content">
                <h5>Listed products</h5>
                <h2 class="text-navy"><?= number_format((int) (isset($stats->listed_count) ? $stats->listed_count : 0)) ?></h2>
                <small class="text-muted">Matching current filters</small>
            </div></div>
        </div>
        <div class="col-sm-6 col-md-3">
            <div class="ibox listing-stat"><div class="ibox-content">
                <h5>Stores</h5>
                <h2><?= number_format((int) (isset($stats->store_count) ? $stats->store_count : 0)) ?></h2>
                <small class="text-muted"><?= number_format((int) (isset($stats->country_count) ? $stats->country_count : 0)) ?> countries</small>
            </div></div>
        </div>
        <div class="col-sm-6 col-md-3">
            <div class="ibox listing-stat"><div class="ibox-content">
                <h5>Average listed price</h5>
                <h2><?= format_money(isset($stats->avg_listed) ? $stats->avg_listed : 0, $currency) ?></h2>
                <small class="text-muted">Customer price on store</small>
            </div></div>
        </div>
        <div class="col-sm-6 col-md-3">
            <div class="ibox listing-stat"><div class="ibox-content">
                <h5>Average revenue / sale</h5>
                <h2 class="text-navy"><?= format_money(isset($stats->avg_revenue) ? $stats->avg_revenue : 0, $currency) ?></h2>
                <small class="text-muted"><?= number_format((float) (isset($stats->avg_revenue_pct) ? $stats->avg_revenue_pct : 0), 1) ?>% of listed · listed − base</small>
            </div></div>
        </div>
    </div>

    <div class="row">
        <div class="col-sm-6 col-md-3">
            <div class="ibox listing-stat"><div class="ibox-content">
                <h5>Avg ecommerce commission</h5>
                <h2><?= format_money(isset($stats->avg_commission) ? $stats->avg_commission : 0, $currency) ?></h2>
                <small class="text-muted"><?= number_format((float) (isset($stats->avg_commission_pct) ? $stats->avg_commission_pct : 0), 1) ?>% of listed · one sale</small>
            </div></div>
        </div>
        <div class="col-sm-6 col-md-3">
            <div class="ibox listing-stat"><div class="ibox-content">
                <h5>Avg platform commission</h5>
                <h2><?= format_money(isset($stats->avg_platform) ? $stats->avg_platform : 0, $currency) ?></h2>
                <small class="text-muted"><?= number_format($feePercent, 2) ?>% of base · <?= number_format((float) (isset($stats->avg_platform_pct) ? $stats->avg_platform_pct : 0), 1) ?>% of listed</small>
            </div></div>
        </div>
        <div class="col-sm-6 col-md-3">
            <div class="ibox listing-stat"><div class="ibox-content">
                <h5>Avg store markup</h5>
                <h2><?= format_money(isset($stats->avg_markup) ? $stats->avg_markup : 0, $currency) ?></h2>
                <small class="text-muted"><?= number_format((float) (isset($stats->avg_markup_pct) ? $stats->avg_markup_pct : 0), 1) ?>% of listed</small>
            </div></div>
        </div>
        <div class="col-sm-6 col-md-3">
            <div class="ibox listing-stat"><div class="ibox-content">
                <h5>Avg source links</h5>
                <h2><?= number_format((float) (isset($stats->avg_sources) ? $stats->avg_sources : 0), 1) ?></h2>
                <small class="text-muted">Supplier URLs per product</small>
            </div></div>
        </div>
    </div>

    <div class="row">
        <div class="col-sm-6 col-md-3">
            <div class="ibox listing-stat"><div class="ibox-content">
                <h5>If each listed once</h5>
                <h2><?= format_money(isset($stats->sum_listed) ? $stats->sum_listed : 0, $currency) ?></h2>
                <small class="text-muted">Sum of listed amounts</small>
            </div></div>
        </div>
        <div class="col-sm-6 col-md-3">
            <div class="ibox listing-stat"><div class="ibox-content">
                <h5>Total ecommerce take</h5>
                <h2><?= format_money(isset($stats->sum_commission) ? $stats->sum_commission : 0, $currency) ?></h2>
                <small class="text-muted">Commission if one sale each</small>
            </div></div>
        </div>
        <div class="col-sm-6 col-md-3">
            <div class="ibox listing-stat"><div class="ibox-content">
                <h5>Total platform take</h5>
                <h2><?= format_money(isset($stats->sum_platform) ? $stats->sum_platform : 0, $currency) ?></h2>
                <small class="text-muted">Platform fee if one sale each</small>
            </div></div>
        </div>
        <div class="col-sm-6 col-md-3">
            <div class="ibox listing-stat"><div class="ibox-content">
                <h5>Combined take / sale</h5>
                <h2 class="text-navy"><?= format_money((isset($stats->sum_commission) ? $stats->sum_commission : 0) + (isset($stats->sum_platform) ? $stats->sum_platform : 0), $currency) ?></h2>
                <small class="text-muted">Avg <?= number_format((float) (isset($stats->avg_take_pct) ? $stats->avg_take_pct : 0), 1) ?>% of listed · ecom + platform</small>
            </div></div>
        </div>
    </div>
    <p class="listing-muted" style="margin-top:-8px; margin-bottom:15px;">Dashboard amounts are converted to <?= htmlspecialchars($currency) ?>. Table rows stay in each store’s currency.</p>

    <div class="ibox">
        <div class="ibox-title">
            <h5>All store products <small class="text-muted">(<?= (int) $listTotal ?>)</small></h5>
        </div>
        <div class="ibox-content">
            <p class="text-muted" style="margin:0 0 12px;">
                <?php if ($listTotal > 0): ?>
                    Showing <strong><?= (int) $listFrom ?></strong>–<strong><?= (int) $listTo ?></strong> of <strong><?= (int) $listTotal ?></strong>
                    &nbsp;·&nbsp; Page <strong><?= (int) $listPage ?></strong> of <strong><?= (int) $listTotalPages ?></strong>
                <?php else: ?>
                    No store-listed products match these filters.
                <?php endif; ?>
            </p>
            <div class="table-responsive">
                <table class="table table-striped table-bordered">
                    <thead>
                        <tr>
                            <th class="listing-th"><a href="<?= htmlspecialchars($sortUrl('name')) ?>">Product<?= $sortIcon('name') ?></a></th>
                            <th class="listing-th"><a href="<?= htmlspecialchars($sortUrl('store')) ?>">Store<?= $sortIcon('store') ?></a></th>
                            <th class="listing-th"><a href="<?= htmlspecialchars($sortUrl('country')) ?>">Country<?= $sortIcon('country') ?></a></th>
                            <th class="listing-th"><a href="<?= htmlspecialchars($sortUrl('ship_min_days')) ?>">Min shipping<?= $sortIcon('ship_min_days') ?></a></th>
                            <th class="listing-th"><a href="<?= htmlspecialchars($sortUrl('ship_max_days')) ?>">Max shipping<?= $sortIcon('ship_max_days') ?></a></th>
                            <th class="listing-th"><a href="<?= htmlspecialchars($sortUrl('base_price')) ?>">Base price<?= $sortIcon('base_price') ?></a></th>
                            <th class="listing-th"><a href="<?= htmlspecialchars($sortUrl('ecommerce_commission')) ?>">Ecommerce commission<?= $sortIcon('ecommerce_commission') ?></a></th>
                            <th class="listing-th"><a href="<?= htmlspecialchars($sortUrl('platform_commission')) ?>">Platform commission<?= $sortIcon('platform_commission') ?></a></th>
                            <th class="listing-th"><a href="<?= htmlspecialchars($sortUrl('listed_amount')) ?>">Total listed<?= $sortIcon('listed_amount') ?></a></th>
                            <th class="listing-th"><a href="<?= htmlspecialchars($sortUrl('total_revenue')) ?>">Total revenue / sale<?= $sortIcon('total_revenue') ?></a></th>
                            <th class="listing-th"><a href="<?= htmlspecialchars($sortUrl('source_count')) ?>">Sources<?= $sortIcon('source_count') ?></a></th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if (empty($products)): ?>
                        <tr><td colspan="11" class="text-center">No products listed on stores yet.</td></tr>
                    <?php endif; ?>
                    <?php foreach ($products as $product): ?>
                        <?php
                        $rowCurrency = !empty($product->country_currency) ? $product->country_currency : $currency;
                        $img = !empty($product->image) ? product_image_url($product->image) : '';
                        $sourceUrls = !empty($product->source_urls) ? $product->source_urls : array();
                        $sourceCount = (int) $product->source_count;
                        ?>
                        <tr>
                            <td>
                                <div class="listing-product">
                                    <a href="<?= htmlspecialchars($product->storefront_url) ?>" target="_blank" rel="noopener">
                                        <?php if ($img): ?>
                                            <img src="<?= htmlspecialchars($img) ?>" alt="">
                                        <?php else: ?>
                                            <span class="listing-placeholder"><i class="fa fa-image"></i></span>
                                        <?php endif; ?>
                                    </a>
                                    <div>
                                        <a href="<?= htmlspecialchars($product->storefront_url) ?>" target="_blank" rel="noopener"><?= htmlspecialchars($product->name) ?></a>
                                        <?php if (!empty($product->sku)): ?>
                                            <div class="listing-muted"><?= htmlspecialchars($product->sku) ?></div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <?= htmlspecialchars($product->store_name) ?>
                                <?php if (!empty($product->store_domain)): ?>
                                    <div class="listing-muted"><?= htmlspecialchars($product->store_domain) ?></div>
                                <?php endif; ?>
                            </td>
                            <td><?= htmlspecialchars($product->country_name ?: '-') ?></td>
                            <td><?= !empty($product->ship_min_days) ? ((int) $product->ship_min_days . ' days') : '-' ?></td>
                            <td><?= !empty($product->ship_max_days) ? ((int) $product->ship_max_days . ' days') : '-' ?></td>
                            <td><?= format_money($product->base_price, $rowCurrency) ?></td>
                            <td><?= format_money($product->ecommerce_commission, $rowCurrency) ?></td>
                            <td>
                                <?= format_money($product->platform_commission, $rowCurrency) ?>
                                <div class="listing-muted"><?= number_format($feePercent, 2) ?>% of base</div>
                            </td>
                            <td>
                                <strong><?= format_money($product->listed_amount, $rowCurrency) ?></strong>
                                <div class="listing-muted">Price on store</div>
                            </td>
                            <td>
                                <strong><?= format_money($product->total_revenue, $rowCurrency) ?></strong>
                                <div class="listing-muted">
                                    Markup <?= format_money($product->store_markup, $rowCurrency) ?>
                                    · ecom + platform + store
                                </div>
                            </td>
                            <td>
                                <?php if ($sourceCount > 0): ?>
                                    <button type="button" class="btn btn-xs btn-white source-count-btn js-show-sources"
                                        data-name="<?= htmlspecialchars($product->name, ENT_QUOTES) ?>"
                                        data-sources="<?= htmlspecialchars(json_encode($sourceUrls), ENT_QUOTES) ?>">
                                        <?= (int) $sourceCount ?>
                                    </button>
                                <?php else: ?>
                                    <span class="text-muted">0</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <div class="row" style="margin-top:15px;">
                <div class="col-sm-4">
                    <p class="text-muted" style="margin:8px 0;">
                        <?php if ($listTotal > 0): ?>
                            Showing <?= (int) $listFrom ?>–<?= (int) $listTo ?> of <?= (int) $listTotal ?>
                        <?php else: ?>
                            No listings to show
                        <?php endif; ?>
                    </p>
                </div>
                <div class="col-sm-8 text-right">
                    <ul class="pagination" style="margin:0; vertical-align:middle;">
                        <li class="<?= $hasPrevPage ? '' : 'disabled' ?>">
                            <?php if (!$hasPrevPage): ?><span>&laquo;</span><?php else: ?><a href="<?= htmlspecialchars($firstPageUrl) ?>">&laquo;</a><?php endif; ?>
                        </li>
                        <li class="<?= $hasPrevPage ? '' : 'disabled' ?>">
                            <?php if (!$hasPrevPage): ?><span>&lsaquo;</span><?php else: ?><a href="<?= htmlspecialchars($prevPageUrl) ?>">&lsaquo;</a><?php endif; ?>
                        </li>
                        <?php foreach ($pageNumbers as $pageItem): ?>
                            <?php if ($pageItem['url'] === ''): ?>
                                <li class="disabled"><span>&hellip;</span></li>
                            <?php elseif (!empty($pageItem['current'])): ?>
                                <li class="active"><span><?= (int) $pageItem['label'] ?></span></li>
                            <?php else: ?>
                                <li><a href="<?= htmlspecialchars($pageItem['url']) ?>"><?= (int) $pageItem['label'] ?></a></li>
                            <?php endif; ?>
                        <?php endforeach; ?>
                        <li class="<?= $hasNextPage ? '' : 'disabled' ?>">
                            <?php if (!$hasNextPage): ?><span>&rsaquo;</span><?php else: ?><a href="<?= htmlspecialchars($nextPageUrl) ?>">&rsaquo;</a><?php endif; ?>
                        </li>
                        <li class="<?= $hasNextPage ? '' : 'disabled' ?>">
                            <?php if (!$hasNextPage): ?><span>&raquo;</span><?php else: ?><a href="<?= htmlspecialchars($lastPageUrl) ?>">&raquo;</a><?php endif; ?>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal inmodal fade" id="listingSourcesModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                <h4 class="modal-title">Source links</h4>
                <small class="text-muted" id="listingSourcesName"></small>
            </div>
            <div class="modal-body">
                <ul class="list-group" id="listingSourcesList"></ul>
            </div>
        </div>
    </div>
</div>
<script>
(function ($) {
    $('#listingFilterCountry, #listingFilterStore, #listingPerPage').on('change', function () {
        if (this.id === 'listingFilterCountry') {
            $('#listingFilterStore').val('0');
        }
        $('#listingFilterForm').submit();
    });
    $(document).on('click', '.js-show-sources', function () {
        var name = $(this).data('name') || '';
        var sources = $(this).attr('data-sources');
        var list = [];
        try { list = JSON.parse(sources || '[]'); } catch (e) { list = []; }
        $('#listingSourcesName').text(name);
        var $ul = $('#listingSourcesList').empty();
        if (!list.length) {
            $ul.append('<li class="list-group-item text-muted">No source URLs saved.</li>');
        } else {
            $.each(list, function (_, item) {
                var url = item && item.url ? item.url : '';
                var label = item && item.label ? item.label : '';
                var li = $('<li class="list-group-item"/>');
                if (label) {
                    li.append($('<div class="listing-muted"/>').text(label));
                }
                li.append($('<a target="_blank" rel="noopener"/>').attr('href', url).text(url));
                $ul.append(li);
            });
        }
        $('#listingSourcesModal').modal('show');
    });
})(jQuery);
</script>
