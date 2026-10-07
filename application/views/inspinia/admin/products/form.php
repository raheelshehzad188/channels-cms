<?php
$isEdit = !empty($product);
$priceLocked = !empty($price_locked);
$storeCopies = !empty($store_copies) ? $store_copies : array();
$ro = $priceLocked ? ' readonly' : '';
$val = function ($key, $default = '') use ($product, $isEdit) {
    return $isEdit && isset($product->$key) ? $product->$key : $default;
};
?>
<div class="row wrapper border-bottom white-bg page-heading">
    <div class="col-lg-10">
        <h2><?= $isEdit ? 'Edit Product' : 'Add Product' ?></h2>
        <ol class="breadcrumb">
            <li><a href="<?= base_url('admin/products') ?>">Products</a></li>
            <li class="active"><strong><?= $isEdit ? 'Edit' : 'Add' ?></strong></li>
        </ol>
    </div>
</div>
<div class="wrapper wrapper-content animated fadeInRight">
    <div class="ibox">
        <div class="ibox-title"><h5><?= $isEdit ? htmlspecialchars($product->name) : 'New product' ?></h5></div>
        <div class="ibox-content">
            <?php $this->load->view('flash'); ?>
            <form method="post" enctype="multipart/form-data" action="<?= base_url('admin/products/save' . ($isEdit ? '/' . $product->id : '')) ?>" class="form-horizontal">
                <div class="tabs-container">
                    <ul class="nav nav-tabs">
                        <li class="active"><a data-toggle="tab" href="#tab-general">General</a></li>
                        <li><a data-toggle="tab" href="#tab-details">Details</a></li>
                        <li><a data-toggle="tab" href="#tab-english">English</a></li>
                        <li><a data-toggle="tab" href="#tab-images">Images</a></li>
                        <li><a data-toggle="tab" href="#tab-pricing">Pricing</a></li>
                        <li><a data-toggle="tab" href="#tab-offer">Offer</a></li>
                        <li><a data-toggle="tab" href="#tab-source">Source</a></li>
                        <li><a data-toggle="tab" href="#tab-creatives">Creatives</a></li>
                        <li><a data-toggle="tab" href="#tab-variation">Variation</a></li>
                        <li><a data-toggle="tab" href="#tab-shipping">Shipping Info</a></li>
                        <li><a data-toggle="tab" href="#tab-seo">SEO</a></li>
                    </ul>
                    <div class="tab-content">
                        <div id="tab-general" class="tab-pane active">
                            <div class="panel-body">
                                <div class="form-group">
                                    <label class="col-sm-2 control-label">Name</label>
                                    <div class="col-sm-9">
                                        <div class="input-group">
                                            <input type="text" name="name" class="form-control" required value="<?= htmlspecialchars($val('name')) ?>">
                                            <span class="input-group-btn">
                                                <button type="button" class="btn btn-primary writeAiBtn" title="Rewrite title, descriptions and SEO with AI">
                                                    <i class="fa fa-magic"></i> Write with AI
                                                </button>
                                            </span>
                                        </div>
                                        <span class="help-block writeAiStatus"></span>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-sm-2 control-label">Brand</label>
                                    <div class="col-sm-9"><input type="text" name="brand" class="form-control" maxlength="150" placeholder="e.g. Samsung, Nike" value="<?= htmlspecialchars($val('brand')) ?>"></div>
                                </div>
                                <div class="form-group">
                                    <label class="col-sm-2 control-label">Made by</label>
                                    <div class="col-sm-9"><input type="text" name="made_by" class="form-control" maxlength="150" placeholder="Manufacturer / made by" value="<?= htmlspecialchars($val('made_by')) ?>"></div>
                                </div>
                                <div class="form-group">
                                    <label class="col-sm-2 control-label">SKU</label>
                                    <div class="col-sm-9"><input type="text" name="sku" class="form-control" value="<?= htmlspecialchars($val('sku')) ?>"></div>
                                </div>
                                <div class="form-group">
                                    <label class="col-sm-2 control-label">Parent SKU</label>
                                    <div class="col-sm-9">
                                        <input type="text" name="parent_sku" class="form-control" maxlength="100" placeholder="Leave empty for a parent product" value="<?= htmlspecialchars($val('parent_sku')) ?>">
                                        <span class="help-block">If this is a sub-product, enter the parent product SKU. Sub-products appear as option boxes on the parent page.</span>
                                    </div>
                                </div>
                                <div class="form-group js-parent-field">
                                    <label class="col-sm-2 control-label">Variation type</label>
                                    <div class="col-sm-9">
                                        <input type="text" name="options_title" id="optionsTitle" class="form-control" maxlength="150" placeholder="Select color, Select size" value="<?= htmlspecialchars($val('options_title')) ?>">
                                        <span class="help-block">Parent products only. This text is shown above the option boxes on the product page. Admin can write anything, e.g. Select color or Select size.</span>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-sm-2 control-label">Default child</label>
                                    <div class="col-sm-9">
                                        <div class="checkbox" style="padding-top:5px;">
                                            <label>
                                                <input type="checkbox" name="is_default" value="1" <?= $isEdit && !empty($product->is_default) ? 'checked' : '' ?>>
                                                Use as the default option when the parent product opens
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-sm-2 control-label">Sort</label>
                                    <div class="col-sm-3">
                                        <input type="number" name="sort_order" class="form-control" min="0" step="1" value="<?= htmlspecialchars((string) (int) $val('sort_order', 0)) ?>">
                                        <span class="help-block">Lower numbers appear first on the storefront. Child products are ordered by this value.</span>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-sm-2 control-label">Supplier</label>
                                    <div class="col-sm-9">
                                        <select name="supplier_id" class="form-control">
                                            <option value="">No supplier</option>
                                            <?php foreach ($suppliers as $supplier): ?>
                                                <option value="<?= (int) $supplier->id ?>" <?= $isEdit && (int)$product->supplier_id === (int)$supplier->id ? 'selected' : '' ?>>
                                                    <?= htmlspecialchars($supplier->name . ($supplier->country_name ? ' — ' . $supplier->country_name : '')) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-sm-2 control-label">Country</label>
                                    <div class="col-sm-9">
                                        <select name="country_id" id="productCountry" class="form-control" required>
                                            <option value="">Select country</option>
                                            <?php foreach ($countries as $country): ?>
                                                <option value="<?= (int) $country->id ?>" <?= $isEdit && (int)$product->country_id === (int)$country->id ? 'selected' : '' ?>>
                                                    <?= htmlspecialchars($country->name) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                                <?php
                                $categoryTree = isset($category_tree) ? $category_tree : array();
                                $selectedCategoryId = isset($selected_category_id) ? (int) $selected_category_id : 0;
                                $selectedSubcategoryId = isset($selected_subcategory_id) ? (int) $selected_subcategory_id : 0;
                                $selectedCategoryName = isset($selected_category_name) ? $selected_category_name : '';
                                $selectedSubcategoryName = isset($selected_subcategory_name) ? $selected_subcategory_name : '';
                                $selectedParent = null;
                                foreach ($categoryTree as $parentCat) {
                                    if ((int) $parentCat['id'] === $selectedCategoryId) {
                                        $selectedParent = $parentCat;
                                        break;
                                    }
                                }
                                $subOptions = $selectedParent && !empty($selectedParent['children']) ? $selectedParent['children'] : array();
                                ?>
                                <div class="form-group">
                                    <label class="col-sm-2 control-label">Category</label>
                                    <div class="col-sm-9">
                                        <select name="category_id" id="productCategory" class="form-control">
                                            <option value="">Select category</option>
                                            <?php foreach ($categoryTree as $parentCat): ?>
                                                <option value="<?= (int) $parentCat['id'] ?>" <?= (int) $parentCat['id'] === $selectedCategoryId ? 'selected' : '' ?>>
                                                    <?= htmlspecialchars($parentCat['name']) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                        <input type="text" name="category_name" id="productCategoryName" class="form-control" style="margin-top:8px;" maxlength="150" placeholder="Type to create or update category name" value="<?= htmlspecialchars($selectedCategoryName) ?>">
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-sm-2 control-label">Sub category</label>
                                    <div class="col-sm-9">
                                        <select name="subcategory_id" id="productSubcategory" class="form-control">
                                            <option value="">Select sub category</option>
                                            <?php foreach ($subOptions as $childCat): ?>
                                                <option value="<?= (int) $childCat['id'] ?>" <?= (int) $childCat['id'] === $selectedSubcategoryId ? 'selected' : '' ?>>
                                                    <?= htmlspecialchars($childCat['name']) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                        <input type="text" name="subcategory_name" id="productSubcategoryName" class="form-control" style="margin-top:8px;" maxlength="150" placeholder="Type to create or update sub category name" value="<?= htmlspecialchars($selectedSubcategoryName) ?>">
                                        <span class="help-block">Pick from the list, or type a name to create a new one / update the selected one. Changing a name updates that category for every product using it. Options change with the selected country.</span>
                                    </div>
                                </div>
                                <?php if (ec_is_admin()): ?>
                                <div class="form-group">
                                    <label class="col-sm-2 control-label">Ecommerce User</label>
                                    <div class="col-sm-9">
                                        <select name="created_by" class="form-control">
                                            <option value="">Select user</option>
                                            <?php foreach ($ecommerce_users as $owner): ?>
                                                <option value="<?= (int) $owner->UserID ?>" <?= $isEdit && (int)$product->created_by === (int)$owner->UserID ? 'selected' : '' ?>>
                                                    <?= htmlspecialchars(trim($owner->first_name . ' ' . $owner->last_name) . ' (' . $owner->uname . ')') ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                                <?php endif; ?>
                                <div class="form-group">
                                    <label class="col-sm-2 control-label">Description</label>
                                    <div class="col-sm-9"><textarea name="description" class="form-control" rows="5"><?= htmlspecialchars($val('description')) ?></textarea></div>
                                </div>
                                <div class="form-group">
                                    <label class="col-sm-2 control-label">Status</label>
                                    <div class="col-sm-9">
                                        <select name="status" class="form-control">
                                            <option value="1" <?= !$isEdit || (int)$product->status === 1 ? 'selected' : '' ?>>Active</option>
                                            <option value="0" <?= $isEdit && (int)$product->status === 0 ? 'selected' : '' ?>>Inactive</option>
                                        </select>
                                        <span class="help-block">Inactive catalog products stay hidden from store owners and customers. Ecommerce users can still open this detail page.</span>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-sm-2 control-label">Trending Picks</label>
                                    <div class="col-sm-9">
                                        <div class="checkbox" style="padding-top:5px;">
                                            <label>
                                                <input type="checkbox" name="is_trending" value="1" <?= $isEdit && !empty($product->is_trending) ? 'checked' : '' ?>>
                                                Show in Trending Picks
                                            </label>
                                        </div>
                                        <span class="help-block">Enable this product to display in the Trending Picks section on product detail pages.</span>
                                        <div class="row" style="margin-top:10px;">
                                            <div class="col-sm-4">
                                                <label class="control-label" style="padding-top:0;">Trending Order</label>
                                                <input type="number" name="trending_order" class="form-control" min="0" step="1" value="<?= htmlspecialchars((string) (int) $val('trending_order', 0)) ?>">
                                                <span class="help-block">Lower number = higher priority. Max 4 products shown per page.</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-sm-2 control-label">Recommended stores</label>
                                    <div class="col-sm-9">
                                        <div class="checkbox" style="padding-top:5px;">
                                            <label>
                                                <input type="checkbox" name="auto_add_to_stores" value="1" <?= $isEdit && !empty($product->auto_add_to_stores) ? 'checked' : '' ?>>
                                                Auto add to recommended stores
                                            </label>
                                        </div>
                                        <span class="help-block">Copies this product to every store in the selected country that has Auto-add enabled. Each store’s plus amount is added to the selling price.</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div id="tab-details" class="tab-pane">
                            <div class="panel-body">
                                <p class="text-muted">Short detail shows on the product page above Add to cart. Full details show in the Description tab.</p>
                                <div class="form-group">
                                    <label>Short detail</label>
                                    <textarea name="short_details" id="productShortDetails" class="form-control" rows="6"><?= htmlspecialchars((string) $val('short_details')) ?></textarea>
                                </div>
                                <div class="form-group" style="margin-top:18px;">
                                    <label>Full details</label>
                                    <textarea name="details" id="productDetails" class="form-control" rows="14"><?= htmlspecialchars((string) $val('details')) ?></textarea>
                                </div>
                                <p style="margin-top:12px;">
                                    <button type="button" class="btn btn-primary btn-sm writeAiBtn">
                                        <i class="fa fa-magic"></i> Write with AI
                                    </button>
                                    <?php if ($isEdit): ?>
                                    <button type="button" class="btn btn-white btn-sm" id="fetchDetailsBtn">
                                        <i class="fa fa-download"></i> Load details from source link
                                    </button>
                                    <?php endif; ?>
                                    <span class="text-muted writeAiStatus" style="margin-left:8px;"></span>
                                    <span class="text-muted" id="fetchDetailsStatus" style="margin-left:8px;"></span>
                                </p>
                            </div>
                        </div>

                        <div id="tab-english" class="tab-pane">
                            <div class="panel-body">
                                <p class="text-muted">English copy for the storefront language switcher. Write with AI fills this in the same request as the country language.</p>
                                <div class="form-group">
                                    <label class="col-sm-2 control-label">Name (English)</label>
                                    <div class="col-sm-9"><input type="text" name="name_en" class="form-control" value="<?= htmlspecialchars($val('name_en')) ?>"></div>
                                </div>
                                <div class="form-group">
                                    <label class="col-sm-2 control-label">Short detail (English)</label>
                                    <div class="col-sm-9"><textarea name="short_details_en" id="productShortDetailsEn" class="form-control" rows="6"><?= htmlspecialchars((string) $val('short_details_en')) ?></textarea></div>
                                </div>
                                <div class="form-group">
                                    <label class="col-sm-2 control-label">Full details (English)</label>
                                    <div class="col-sm-9"><textarea name="details_en" id="productDetailsEn" class="form-control" rows="14"><?= htmlspecialchars((string) $val('details_en')) ?></textarea></div>
                                </div>
                                <div class="form-group">
                                    <label class="col-sm-2 control-label">Meta title (English)</label>
                                    <div class="col-sm-9"><input type="text" name="seo_title_en" class="form-control" value="<?= htmlspecialchars($val('seo_title_en')) ?>"></div>
                                </div>
                                <div class="form-group">
                                    <label class="col-sm-2 control-label">Meta description (English)</label>
                                    <div class="col-sm-9"><textarea name="seo_description_en" class="form-control" rows="3"><?= htmlspecialchars($val('seo_description_en')) ?></textarea></div>
                                </div>
                                <div class="form-group">
                                    <label class="col-sm-2 control-label">Meta keywords (English)</label>
                                    <div class="col-sm-9"><input type="text" name="seo_keywords_en" class="form-control" value="<?= htmlspecialchars($val('seo_keywords_en')) ?>"></div>
                                </div>
                            </div>
                        </div>

                        <div id="tab-images" class="tab-pane">
                            <div class="panel-body">
                                <div class="form-group">
                                    <label class="col-sm-2 control-label">Main Image</label>
                                    <div class="col-sm-9">
                                        <?php if ($isEdit && !empty($product->image)): ?>
                                            <p><img src="<?= base_url($product->image) ?>" alt="" style="max-height:90px;"></p>
                                        <?php endif; ?>
                                        <input type="file" name="image" class="form-control">
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-sm-2 control-label">Gallery</label>
                                    <div class="col-sm-9">
                                        <input type="file" name="gallery[]" class="form-control" multiple>
                                        <span class="help-block">You can select multiple images.</span>
                                        <?php if (!empty($images)): ?>
                                        <div class="row" style="margin-top:15px;">
                                            <?php foreach ($images as $image): ?>
                                            <div class="col-sm-3 text-center" style="margin-bottom:15px;">
                                                <img src="<?= base_url($image->image) ?>" alt="" class="img-responsive" style="max-height:110px; margin:0 auto 8px;">
                                                <a class="btn btn-xs btn-danger" href="<?= base_url('admin/products/delete_image/' . $image->id) ?>" onclick="return confirm('Delete this image?');">Delete</a>
                                            </div>
                                            <?php endforeach; ?>
                                        </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div id="tab-pricing" class="tab-pane">
                            <div class="panel-body">
                                <?php if ($priceLocked): ?>
                                <div class="alert alert-warning">
                                    Selling, compare and cost prices are locked because <?= count($storeCopies) ?> store<?= count($storeCopies) === 1 ? ' has' : 's have' ?> already added this product.
                                    <?php
                                    $copyNames = array();
                                    foreach ($storeCopies as $copy) {
                                        if (!empty($copy->store_name)) {
                                            $copyNames[] = $copy->store_name;
                                        }
                                    }
                                    if ($copyNames) {
                                        echo ' (' . htmlspecialchars(implode(', ', $copyNames)) . ')';
                                    }
                                    ?>
                                    Max Sale Price can still be changed.
                                </div>
                                <?php endif; ?>
                                <div class="form-group">
                                    <label class="col-sm-2 control-label">Selling Price</label>
                                    <div class="col-sm-9"><input type="number" step="0.01" name="price" class="form-control" required<?= $ro ?> value="<?= htmlspecialchars($val('price', '0.00')) ?>"></div>
                                </div>
                                <div class="form-group">
                                    <label class="col-sm-2 control-label">Max Sale Price</label>
                                    <div class="col-sm-9">
                                        <input type="number" step="0.01" name="max_sale_price" class="form-control" value="<?= htmlspecialchars($val('max_sale_price', '0.00')) ?>">
                                        <span class="help-block">Store owners are recommended not to sell above this price. This can be updated even after stores add the product.</span>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-sm-2 control-label">Compare Price</label>
                                    <div class="col-sm-9"><input type="number" step="0.01" name="compare_price" class="form-control"<?= $ro ?> value="<?= htmlspecialchars($val('compare_price', '0.00')) ?>"></div>
                                </div>
                                <div class="form-group">
                                    <label class="col-sm-2 control-label">Base / Cost Price</label>
                                    <div class="col-sm-9">
                                        <input type="number" step="0.01" name="cost_price" class="form-control"<?= $ro ?> value="<?= htmlspecialchars($val('cost_price', '0.00')) ?>">
                                        <span class="help-block">Catalog base price. Store owners pay this plus commission and <?= number_format(platform_fee_percent(), 2) ?>% platform fee, then add their markup on top.</span>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-sm-2 control-label">Extra amount</label>
                                    <div class="col-sm-9">
                                        <input type="number" step="0.01" name="extra_amount" class="form-control" value="<?= htmlspecialchars($val('extra_amount', '0.00')) ?>">
                                        <span class="help-block">Plus or minus. Added after base price, ecommerce plus, platform fee and store plus when store listing prices are built. Example: 5.00 or -3.50.</span>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-sm-2 control-label">Stock</label>
                                    <div class="col-sm-9"><input type="number" name="stock" class="form-control" required value="<?= (int) $val('stock', 0) ?>"></div>
                                </div>
                            </div>
                        </div>

                        <div id="tab-offer" class="tab-pane">
                            <div class="panel-body">
                                <p class="text-muted">Product offers adjust the selling price on the storefront and in cart/checkout (server-side). Dates auto-activate/deactivate the offer. Precedence: product offer &gt; campaign offer &gt; display-only compare discount. Coupons do not stack with active offers.</p>
                                <?php
                                $offerEnabled = (int) $val('offer_enabled', 0) === 1;
                                $offerType = (string) $val('offer_type', 'percent');
                                $offerStarts = $val('offer_starts_at', '');
                                $offerEnds = $val('offer_ends_at', '');
                                if ($offerStarts && $offerStarts !== '0000-00-00 00:00:00') {
                                    $offerStarts = date('Y-m-d\TH:i', strtotime($offerStarts));
                                } else {
                                    $offerStarts = '';
                                }
                                if ($offerEnds && $offerEnds !== '0000-00-00 00:00:00') {
                                    $offerEnds = date('Y-m-d\TH:i', strtotime($offerEnds));
                                } else {
                                    $offerEnds = '';
                                }
                                ?>
                                <div class="form-group">
                                    <label class="col-sm-2 control-label">Enable Offer</label>
                                    <div class="col-sm-9">
                                        <label class="checkbox-inline">
                                            <input type="checkbox" name="offer_enabled" value="1" <?= $offerEnabled ? 'checked' : '' ?>> Enable Offer
                                        </label>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-sm-2 control-label">Offer Type</label>
                                    <div class="col-sm-9">
                                        <select name="offer_type" class="form-control" id="offerTypeSelect">
                                            <option value="percent" <?= $offerType === 'percent' ? 'selected' : '' ?>>Percentage Discount</option>
                                            <option value="fixed" <?= $offerType === 'fixed' ? 'selected' : '' ?>>Fixed Amount Discount</option>
                                            <option value="buy_x_get_y" <?= $offerType === 'buy_x_get_y' ? 'selected' : '' ?>>Buy X Get Y</option>
                                            <option value="bundle" <?= $offerType === 'bundle' ? 'selected' : '' ?>>Bundle Offer</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="form-group offer-field offer-field-value">
                                    <label class="col-sm-2 control-label">Discount Value</label>
                                    <div class="col-sm-9">
                                        <input type="number" step="0.01" min="0" name="offer_value" class="form-control" value="<?= htmlspecialchars($val('offer_value', '0')) ?>">
                                        <span class="help-block">Percent (e.g. 20) or fixed SEK amount (e.g. 40). Final price never goes to zero.</span>
                                    </div>
                                </div>
                                <div class="form-group offer-field offer-field-bxgy" style="display:none;">
                                    <label class="col-sm-2 control-label">Buy X Get Y</label>
                                    <div class="col-sm-4">
                                        <input type="number" min="1" name="offer_buy_qty" class="form-control" placeholder="Buy qty" value="<?= (int) $val('offer_buy_qty', 2) ?>">
                                    </div>
                                    <div class="col-sm-4">
                                        <input type="number" min="1" name="offer_get_qty" class="form-control" placeholder="Get free qty" value="<?= (int) $val('offer_get_qty', 1) ?>">
                                    </div>
                                </div>
                                <div class="form-group offer-field offer-field-bundle" style="display:none;">
                                    <label class="col-sm-2 control-label">Bundle</label>
                                    <div class="col-sm-4">
                                        <input type="number" min="2" name="offer_bundle_qty" class="form-control" placeholder="Qty" value="<?= (int) $val('offer_bundle_qty', 2) ?>">
                                    </div>
                                    <div class="col-sm-4">
                                        <input type="number" step="0.01" min="0.01" name="offer_bundle_price" class="form-control" placeholder="Bundle price (Kr)" value="<?= htmlspecialchars($val('offer_bundle_price', '0')) ?>">
                                    </div>
                                </div>
                                <div class="form-group offer-field offer-field-bundle" style="display:none;">
                                    <label class="col-sm-2 control-label">2nd Bundle (optional)</label>
                                    <div class="col-sm-4">
                                        <input type="number" min="0" name="offer_bundle2_qty" class="form-control" placeholder="Qty e.g. 3" value="<?= (int) $val('offer_bundle2_qty', 0) ?>">
                                    </div>
                                    <div class="col-sm-4">
                                        <input type="number" step="0.01" min="0" name="offer_bundle2_price" class="form-control" placeholder="Bundle price (Kr)" value="<?= htmlspecialchars($val('offer_bundle2_price', '0')) ?>">
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-sm-2 control-label">Offer Label</label>
                                    <div class="col-sm-9">
                                        <input type="text" name="offer_label" class="form-control" maxlength="120" placeholder="20% OFF / HALLOWEEN DEAL / BUY 2 GET 1" value="<?= htmlspecialchars($val('offer_label', '')) ?>">
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-sm-2 control-label">Start Date</label>
                                    <div class="col-sm-9">
                                        <input type="datetime-local" name="offer_starts_at" class="form-control" value="<?= htmlspecialchars($offerStarts) ?>">
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-sm-2 control-label">End Date</label>
                                    <div class="col-sm-9">
                                        <input type="datetime-local" name="offer_ends_at" class="form-control" value="<?= htmlspecialchars($offerEnds) ?>">
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-sm-2 control-label">Priority</label>
                                    <div class="col-sm-9">
                                        <input type="number" name="offer_priority" class="form-control" value="<?= (int) $val('offer_priority', 0) ?>">
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-sm-2 control-label">Display</label>
                                    <div class="col-sm-9">
                                        <label class="checkbox-inline">
                                            <input type="checkbox" name="offer_show_badge" value="1" <?= (int) $val('offer_show_badge', 1) === 1 ? 'checked' : '' ?>> Show Offer Badge
                                        </label>
                                        <label class="checkbox-inline">
                                            <input type="checkbox" name="offer_show_countdown" value="1" <?= (int) $val('offer_show_countdown', 0) === 1 ? 'checked' : '' ?>> Offer Countdown
                                        </label>
                                        <label class="checkbox-inline">
                                            <input type="checkbox" name="offer_free_shipping" value="1" <?= (int) $val('offer_free_shipping', 0) === 1 ? 'checked' : '' ?>> Free Shipping (this product)
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div id="tab-source" class="tab-pane">
                            <div class="panel-body">
                                <p class="text-muted">Dropship order sources — where you can buy this product. Link + supplier cost. Store owners never see this tab.</p>
                                <table class="table table-bordered" id="source-table">
                                    <thead>
                                        <tr>
                                            <th width="180">Label (optional)</th>
                                            <th>Source link</th>
                                            <th width="140">Source price</th>
                                            <th width="90"></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $sourceRows = !empty($sources) ? $sources : array((object) array('label' => '', 'source_url' => '', 'source_price' => '0.00'));
                                        foreach ($sourceRows as $si => $source):
                                        ?>
                                        <tr class="source-row">
                                            <td><input type="text" name="sources[<?= $si ?>][label]" class="form-control" placeholder="Amazon / AliExpress" value="<?= htmlspecialchars($source->label) ?>"></td>
                                            <td><input type="url" name="sources[<?= $si ?>][source_url]" class="form-control" placeholder="https://..." value="<?= htmlspecialchars($source->source_url) ?>"></td>
                                            <td><input type="number" step="0.01" name="sources[<?= $si ?>][source_price]" class="form-control" value="<?= htmlspecialchars($source->source_price) ?>"></td>
                                            <td><button type="button" class="btn btn-white btn-sm js-remove-source">Remove</button></td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                                <button type="button" class="btn btn-white btn-sm" id="add-source">Add source link</button>
                            </div>
                        </div>

                        <div id="tab-creatives" class="tab-pane">
                            <div class="panel-body">
                                <p class="text-muted">Ad / creative links for this product. Store owners never see this tab. You can also add these when importing.</p>
                                <table class="table table-bordered" id="creative-table">
                                    <thead>
                                        <tr>
                                            <th width="180">Label (optional)</th>
                                            <th>Creative link</th>
                                            <th width="90"></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $creativeRows = !empty($creatives) ? $creatives : array((object) array('label' => '', 'link' => ''));
                                        foreach ($creativeRows as $ci => $creative):
                                        ?>
                                        <tr class="creative-row">
                                            <td><input type="text" name="creatives[<?= $ci ?>][label]" class="form-control" placeholder="Facebook / TikTok" value="<?= htmlspecialchars($creative->label) ?>"></td>
                                            <td><input type="text" name="creatives[<?= $ci ?>][link]" class="form-control" placeholder="https://..." value="<?= htmlspecialchars($creative->link) ?>"></td>
                                            <td><button type="button" class="btn btn-white btn-sm js-remove-creative">Remove</button></td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                                <button type="button" class="btn btn-white btn-sm" id="add-creative">Add creative link</button>
                            </div>
                        </div>

                        <div id="tab-variation" class="tab-pane">
                            <div class="panel-body">
                                <div class="form-group js-parent-field" style="margin-bottom:20px;">
                                    <label>Variation type</label>
                                    <input type="text" class="form-control" id="variationTypeMirror" maxlength="150" placeholder="Select color, Select size" value="<?= htmlspecialchars($val('options_title')) ?>">
                                    <span class="help-block">Shown on the product page as the heading above child options. Example: Select color, Select size.</span>
                                </div>
                                <h4>1. Attributes</h4>
                                <p class="text-muted">Add attributes first, for example Size = S, M, L and Color = Red, Blue.</p>
                                <table class="table table-bordered" id="attribute-table">
                                    <thead>
                                        <tr>
                                            <th width="220">Attribute</th>
                                            <th>Values (comma separated)</th>
                                            <th width="90"></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $attrRows = !empty($attributes) ? $attributes : array((object) array('name' => '', 'values_text' => ''));
                                        foreach ($attrRows as $ai => $attribute):
                                        ?>
                                        <tr class="attribute-row">
                                            <td><input type="text" name="attributes[<?= $ai ?>][name]" class="form-control js-attr-name" placeholder="Size" value="<?= htmlspecialchars($attribute->name) ?>"></td>
                                            <td><input type="text" name="attributes[<?= $ai ?>][values]" class="form-control js-attr-values" placeholder="S, M, L" value="<?= htmlspecialchars($attribute->values_text) ?>"></td>
                                            <td><button type="button" class="btn btn-white btn-sm js-remove-attribute">Remove</button></td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                                <button type="button" class="btn btn-white btn-sm" id="add-attribute">Add Attribute</button>
                                <button type="button" class="btn btn-primary btn-sm" id="generate-variations">Generate variations</button>

                                <hr>
                                <h4>2. Variations</h4>
                                <p class="text-muted">System creates every combination. Each row can have its own price, stock and image.</p>
                                <div class="table-responsive">
                                    <table class="table table-bordered" id="variation-table">
                                        <thead>
                                            <tr>
                                                <th>Combination</th>
                                                <th width="140">SKU</th>
                                                <th width="120">Price</th>
                                                <th width="100">Stock</th>
                                                <th width="180">Image</th>
                                                <th width="80"></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($variations as $i => $variation): ?>
                                            <?php
                                            $comboKey = !empty($variation->combination_key)
                                                ? $variation->combination_key
                                                : ($variation->option_name . ':' . $variation->option_value);
                                            $attrJson = !empty($variation->attributes_json)
                                                ? $variation->attributes_json
                                                : json_encode(array($variation->option_name => $variation->option_value));
                                            ?>
                                            <tr class="variation-row" data-key="<?= htmlspecialchars($comboKey) ?>">
                                                <td>
                                                    <strong><?= htmlspecialchars($variation->option_value) ?></strong>
                                                    <input type="hidden" name="variations[<?= $i ?>][option_name]" value="<?= htmlspecialchars($variation->option_name) ?>">
                                                    <input type="hidden" name="variations[<?= $i ?>][option_value]" value="<?= htmlspecialchars($variation->option_value) ?>">
                                                    <input type="hidden" name="variations[<?= $i ?>][combination_key]" value="<?= htmlspecialchars($comboKey) ?>">
                                                    <input type="hidden" name="variations[<?= $i ?>][attributes_json]" value="<?= htmlspecialchars($attrJson) ?>">
                                                </td>
                                                <td><input type="text" name="variations[<?= $i ?>][sku]" class="form-control" value="<?= htmlspecialchars($variation->sku) ?>"></td>
                                                <td><input type="number" step="0.01" name="variations[<?= $i ?>][price]" class="form-control"<?= $ro ?> value="<?= htmlspecialchars($variation->price) ?>"></td>
                                                <td><input type="number" name="variations[<?= $i ?>][stock]" class="form-control" value="<?= (int) $variation->stock ?>"></td>
                                                <td>
                                                    <?php if (!empty($variation->image)): ?>
                                                        <img src="<?= base_url($variation->image) ?>" alt="" style="max-height:36px; margin-bottom:6px; display:block;">
                                                    <?php endif; ?>
                                                    <input type="hidden" name="variations[<?= $i ?>][image]" value="<?= htmlspecialchars($variation->image) ?>">
                                                    <input type="file" name="variation_image[<?= $i ?>]" accept="image/*">
                                                </td>
                                                <td><button type="button" class="btn btn-white btn-sm js-remove-variation">Remove</button></td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                                <p id="variation-empty" class="text-muted" <?= empty($variations) ? '' : 'style="display:none;"' ?>>No combinations yet. Add attributes and click Generate variations.</p>
                            </div>
                        </div>

                        <div id="tab-shipping" class="tab-pane">
                            <div class="panel-body">
                                <p class="text-muted">Estimated Delivery (working days). Customers see a Swedish/English delivery range calculated from today plus these days. AliExpress products without a scraped ETA default to 7–15.</p>
                                <div class="form-group">
                                    <label class="col-sm-2 control-label">Est. delivery min</label>
                                    <div class="col-sm-4">
                                        <input type="number" min="0" step="1" name="ship_min_days" id="shipMinDays" class="form-control" value="<?= (int) $val('ship_min_days', 0) ?>">
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-sm-2 control-label">Est. delivery max</label>
                                    <div class="col-sm-4">
                                        <input type="number" min="0" step="1" name="ship_max_days" id="shipMaxDays" class="form-control" value="<?= (int) $val('ship_max_days', 0) ?>">
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-sm-2 control-label">Customer preview</label>
                                    <div class="col-sm-9">
                                        <p class="form-control-static" id="shipPreview" style="font-weight:600;">Set shipping days to preview delivery dates.</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div id="tab-seo" class="tab-pane">
                            <div class="panel-body">
                                <p style="margin-bottom:16px;">
                                    <button type="button" class="btn btn-primary btn-sm writeAiBtn">
                                        <i class="fa fa-magic"></i> Write with AI
                                    </button>
                                    <span class="text-muted writeAiStatus" style="margin-left:8px;"></span>
                                </p>
                                <div class="form-group">
                                    <label class="col-sm-2 control-label">URL Slug</label>
                                    <div class="col-sm-9"><input type="text" name="slug" class="form-control" value="<?= htmlspecialchars($val('slug')) ?>" placeholder="wireless-headphones"></div>
                                </div>
                                <div class="form-group">
                                    <label class="col-sm-2 control-label">Meta Title</label>
                                    <div class="col-sm-9"><input type="text" name="seo_title" class="form-control" value="<?= htmlspecialchars($val('seo_title')) ?>"></div>
                                </div>
                                <div class="form-group">
                                    <label class="col-sm-2 control-label">Meta Description</label>
                                    <div class="col-sm-9"><textarea name="seo_description" class="form-control" rows="4"><?= htmlspecialchars($val('seo_description')) ?></textarea></div>
                                </div>
                                <div class="form-group">
                                    <label class="col-sm-2 control-label">Meta Keywords</label>
                                    <div class="col-sm-9"><input type="text" name="seo_keywords" class="form-control" value="<?= htmlspecialchars($val('seo_keywords')) ?>" placeholder="headphones, wireless, audio"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="hr-line-dashed"></div>
                <div class="text-right">
                    <a href="<?= base_url('admin/products') ?>" class="btn btn-white">Cancel</a>
                    <button type="submit" class="btn btn-primary"><?= $isEdit ? 'Update' : 'Save' ?></button>
                </div>
            </form>
        </div>
    </div>
</div>
<link href="<?= $assets ?>css/plugins/summernote/summernote.css" rel="stylesheet">
<link href="<?= $assets ?>css/plugins/summernote/summernote-bs3.css" rel="stylesheet">
<script src="<?= $assets ?>js/plugins/summernote/summernote.min.js"></script>
<script>
(function ($) {
    var attrIndex = $('#attribute-table tbody tr').length;
    var sourceIndex = $('#source-table tbody tr').length;
    var creativeIndex = $('#creative-table tbody tr').length;
    var defaultPrice = $('input[name="price"]').val() || '0';
    var priceLocked = <?= $priceLocked ? 'true' : 'false' ?>;
    var priceReadonly = priceLocked ? ' readonly' : '';
    var categoriesUrl = <?= json_encode(base_url('admin/products/import_categories')) ?>;
    var categoryTree = <?= json_encode(isset($category_tree) ? $category_tree : array()) ?>;
    var savedCategoryId = <?= (int) (isset($selected_category_id) ? $selected_category_id : 0) ?>;
    var savedSubcategoryId = <?= (int) (isset($selected_subcategory_id) ? $selected_subcategory_id : 0) ?>;

    function syncParentFields() {
        var isChild = $.trim($('input[name="parent_sku"]').val() || '') !== '';
        $('.js-parent-field').toggle(!isChild);
    }
    $('input[name="parent_sku"]').on('input change', syncParentFields);
    syncParentFields();

    $('#optionsTitle, #variationTypeMirror').on('input', function () {
        var value = $(this).val();
        $('#optionsTitle, #variationTypeMirror').not(this).val(value);
    });

    function fillCategorySelect($el, placeholder, items, selectedId) {
        $el.empty().append($('<option/>').val('').text(placeholder));
        $.each(items || [], function (_, item) {
            var opt = $('<option/>').val(item.id).text(item.name);
            if (selectedId && String(item.id) === String(selectedId)) {
                opt.prop('selected', true);
            }
            $el.append(opt);
        });
    }
    function selectedOptionText($el) {
        var val = $el.val();
        if (!val) {
            return '';
        }
        return $.trim($el.find('option:selected').text());
    }
    function syncCategoryName() {
        $('#productCategoryName').val(selectedOptionText($('#productCategory')));
    }
    function syncSubcategoryName() {
        $('#productSubcategoryName').val(selectedOptionText($('#productSubcategory')));
    }
    function selectedCategoryParent() {
        var id = parseInt($('#productCategory').val(), 10) || 0;
        var found = null;
        $.each(categoryTree, function (_, parent) {
            if (parseInt(parent.id, 10) === id) {
                found = parent;
                return false;
            }
        });
        return found;
    }
    function renderProductSubcategories(selectedId) {
        var parent = selectedCategoryParent();
        fillCategorySelect($('#productSubcategory'), 'Select sub category', parent ? parent.children : [], selectedId || '');
        if (!selectedId) {
            syncSubcategoryName();
        }
    }
    function loadProductCategories(countryId, categoryId, subcategoryId, keepNames) {
        var $cat = $('#productCategory');
        var $sub = $('#productSubcategory');
        categoryTree = [];
        fillCategorySelect($cat, 'Select category', [], '');
        fillCategorySelect($sub, 'Select sub category', [], '');
        if (!keepNames) {
            $('#productCategoryName').val('');
            $('#productSubcategoryName').val('');
        }
        if (!countryId) {
            return;
        }
        $cat.prop('disabled', true);
        $sub.prop('disabled', true);
        $.get(categoriesUrl, { country_id: countryId })
            .done(function (res) {
                categoryTree = (res && res.categories) ? res.categories : [];
                fillCategorySelect($cat, 'Select category', categoryTree, categoryId || '');
                renderProductSubcategories(subcategoryId || '');
                if (!keepNames || !$('#productCategoryName').val()) {
                    syncCategoryName();
                }
                if (!keepNames || !$('#productSubcategoryName').val()) {
                    syncSubcategoryName();
                }
            })
            .fail(function () {
                fillCategorySelect($cat, 'Could not load categories', [], '');
            })
            .always(function () {
                $cat.prop('disabled', false);
                $sub.prop('disabled', false);
            });
    }
    $('#productCountry').on('change', function () {
        savedCategoryId = 0;
        savedSubcategoryId = 0;
        loadProductCategories($(this).val(), '', '', false);
    });
    $('#productCategory').on('change', function () {
        savedSubcategoryId = 0;
        syncCategoryName();
        renderProductSubcategories('');
        $('#productSubcategoryName').val('');
    });
    $('#productSubcategory').on('change', function () {
        syncSubcategoryName();
    });
    function applyAiCategories(res) {
        var countryId = $('#productCountry').val();
        var categoryId = parseInt(res.category_id, 10) || 0;
        var subcategoryId = parseInt(res.subcategory_id, 10) || 0;
        if (res.category_name) {
            $('#productCategoryName').val(res.category_name);
        }
        if (res.subcategory_name) {
            $('#productSubcategoryName').val(res.subcategory_name);
        }
        if (!countryId || (!categoryId && !res.category_name)) {
            return;
        }
        savedCategoryId = categoryId;
        savedSubcategoryId = subcategoryId;
        loadProductCategories(countryId, categoryId, subcategoryId, true);
    }
    if (!$('#productCategory option').filter(function () { return this.value !== ''; }).length && $('#productCountry').val()) {
        loadProductCategories($('#productCountry').val(), savedCategoryId, savedSubcategoryId, true);
    }

    $('#add-source').on('click', function () {
        var html = '<tr class="source-row">' +
            '<td><input type="text" name="sources[' + sourceIndex + '][label]" class="form-control" placeholder="Amazon / AliExpress"></td>' +
            '<td><input type="url" name="sources[' + sourceIndex + '][source_url]" class="form-control" placeholder="https://..."></td>' +
            '<td><input type="number" step="0.01" name="sources[' + sourceIndex + '][source_price]" class="form-control" value="0.00"></td>' +
            '<td><button type="button" class="btn btn-white btn-sm js-remove-source">Remove</button></td>' +
            '</tr>';
        $('#source-table tbody').append(html);
        sourceIndex++;
    });
    $(document).on('click', '.js-remove-source', function () {
        var $tbody = $('#source-table tbody');
        if ($tbody.find('tr').length <= 1) {
            $(this).closest('tr').find('input').val('');
            return;
        }
        $(this).closest('tr').remove();
    });

    $('#add-creative').on('click', function () {
        var html = '<tr class="creative-row">' +
            '<td><input type="text" name="creatives[' + creativeIndex + '][label]" class="form-control" placeholder="Facebook / TikTok"></td>' +
            '<td><input type="text" name="creatives[' + creativeIndex + '][link]" class="form-control" placeholder="https://..."></td>' +
            '<td><button type="button" class="btn btn-white btn-sm js-remove-creative">Remove</button></td>' +
            '</tr>';
        $('#creative-table tbody').append(html);
        creativeIndex++;
    });
    $(document).on('click', '.js-remove-creative', function () {
        var $tbody = $('#creative-table tbody');
        if ($tbody.find('tr').length <= 1) {
            $(this).closest('tr').find('input').val('');
            return;
        }
        $(this).closest('tr').remove();
    });

    function escapeHtml(value) {
        return $('<div>').text(value || '').html();
    }

    function parseValues(text) {
        return $.map((text || '').split(','), function (item) {
            return $.trim(item);
        }).filter(function (item) {
            return item !== '';
        });
    }

    function collectAttributes() {
        var attrs = [];
        $('#attribute-table tbody tr').each(function () {
            var name = $.trim($(this).find('.js-attr-name').val());
            var values = parseValues($(this).find('.js-attr-values').val());
            if (name && values.length) {
                attrs.push({ name: name, values: values });
            }
        });
        return attrs;
    }

    function cartesian(list) {
        return list.reduce(function (acc, values) {
            var next = [];
            acc.forEach(function (prefix) {
                values.forEach(function (value) {
                    next.push(prefix.concat([value]));
                });
            });
            return next;
        }, [[]]);
    }

    function currentRows() {
        var map = {};
        $('#variation-table tbody tr').each(function () {
            var key = $(this).attr('data-key');
            if (!key) {
                return;
            }
            map[key] = {
                sku: $(this).find('input[name$="[sku]"]').val(),
                price: $(this).find('input[name$="[price]"]').val(),
                stock: $(this).find('input[name$="[stock]"]').val(),
                image: $(this).find('input[type="hidden"][name$="[image]"]').val(),
                preview: $(this).find('img').attr('src') || ''
            };
        });
        return map;
    }

    function renderVariations(combos) {
        var existing = currentRows();
        var $body = $('#variation-table tbody').empty();
        combos.forEach(function (combo, index) {
            var prev = existing[combo.key] || {};
            var imgHtml = prev.preview
                ? '<img src="' + prev.preview + '" alt="" style="max-height:36px; margin-bottom:6px; display:block;">'
                : '';
            var row = '<tr class="variation-row" data-key="' + escapeHtml(combo.key) + '">' +
                '<td><strong>' + escapeHtml(combo.label) + '</strong>' +
                '<input type="hidden" name="variations[' + index + '][option_name]" value="' + escapeHtml(combo.optionName) + '">' +
                '<input type="hidden" name="variations[' + index + '][option_value]" value="' + escapeHtml(combo.label) + '">' +
                '<input type="hidden" name="variations[' + index + '][combination_key]" value="' + escapeHtml(combo.key) + '">' +
                '<input type="hidden" name="variations[' + index + '][attributes_json]" value="' + escapeHtml(JSON.stringify(combo.attrs)) + '">' +
                '</td>' +
                '<td><input type="text" name="variations[' + index + '][sku]" class="form-control" value="' + escapeHtml(prev.sku || '') + '"></td>' +
                '<td><input type="number" step="0.01" name="variations[' + index + '][price]" class="form-control"' + priceReadonly + ' value="' + escapeHtml(prev.price || defaultPrice) + '"></td>' +
                '<td><input type="number" name="variations[' + index + '][stock]" class="form-control" value="' + escapeHtml(prev.stock || '0') + '"></td>' +
                '<td>' + imgHtml +
                '<input type="hidden" name="variations[' + index + '][image]" value="' + escapeHtml(prev.image || '') + '">' +
                '<input type="file" name="variation_image[' + index + ']" accept="image/*">' +
                '</td>' +
                '<td><button type="button" class="btn btn-white btn-sm js-remove-variation">Remove</button></td>' +
                '</tr>';
            $body.append(row);
        });
        $('#variation-empty').toggle(combos.length === 0);
    }

    $('#add-attribute').on('click', function () {
        var row = '<tr class="attribute-row">' +
            '<td><input type="text" name="attributes[' + attrIndex + '][name]" class="form-control js-attr-name" placeholder="Color"></td>' +
            '<td><input type="text" name="attributes[' + attrIndex + '][values]" class="form-control js-attr-values" placeholder="Red, Blue"></td>' +
            '<td><button type="button" class="btn btn-white btn-sm js-remove-attribute">Remove</button></td>' +
            '</tr>';
        $('#attribute-table tbody').append(row);
        attrIndex++;
    });

    $(document).on('click', '.js-remove-attribute', function () {
        var $rows = $('#attribute-table tbody tr');
        if ($rows.length === 1) {
            $rows.find('input').val('');
            return;
        }
        $(this).closest('tr').remove();
    });

    $(document).on('click', '.js-remove-variation', function () {
        $(this).closest('tr').remove();
        $('#variation-empty').toggle($('#variation-table tbody tr').length === 0);
    });

    $('#generate-variations').on('click', function () {
        var attrs = collectAttributes();
        if (!attrs.length) {
            alert('Add at least one attribute with values first.');
            return;
        }
        var valueSets = attrs.map(function (attr) { return attr.values; });
        var combos = cartesian(valueSets).map(function (values) {
            var pair = {};
            var keys = [];
            var labels = [];
            var names = [];
            attrs.forEach(function (attr, i) {
                pair[attr.name] = values[i];
                names.push(attr.name);
                labels.push(values[i]);
                keys.push(attr.name + ':' + values[i]);
            });
            return {
                attrs: pair,
                optionName: names.join(' / '),
                label: labels.join(' / '),
                key: keys.join('|')
            };
        });
        if (combos.length > 100) {
            alert('Too many combinations (' + combos.length + '). Use fewer attribute values.');
            return;
        }
        renderVariations(combos);
    });

    function shipPreview() {
        var min = parseInt($('#shipMinDays').val(), 10) || 0;
        var max = parseInt($('#shipMaxDays').val(), 10) || 0;
        var $out = $('#shipPreview');
        if (!$out.length) return;
        if (!min && !max) {
            $out.text('Set shipping days to preview delivery dates.');
            return;
        }
        if (max && min && min > max) { var t = min; min = max; max = t; }
        if (!min) min = max;
        if (!max) max = min;
        function label(days) {
            var d = new Date();
            d.setDate(d.getDate() + days);
            var months = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
            return d.getDate() + ' ' + months[d.getMonth()];
        }
        if (min === max) {
            $out.text('This product will arrive on ' + label(min) + '.');
        } else {
            $out.text('This product will arrive between ' + label(min) + ' and ' + label(max) + '.');
        }
    }
    $('#shipMinDays, #shipMaxDays').on('input change', shipPreview);
    shipPreview();

    var detailsReady = false;
    var shortDetailsReady = false;
    var detailsEnReady = false;
    var shortDetailsEnReady = false;
    function editorCode($el, html) {
        if ($el.next('.note-editor').length) {
            $el.summernote('code', html || '');
        } else {
            $el.val(html || '');
        }
    }
    function setDetailsHtml(html) {
        editorCode($('#productDetails'), html);
    }
    function initEditor($el, height, withMedia) {
        if (!$el.length || typeof $.fn.summernote !== 'function' || $el.next('.note-editor').length) {
            return;
        }
        $el.summernote({
            height: height,
            toolbar: [
                ['style', ['style']],
                ['font', ['bold', 'italic', 'underline', 'clear']],
                ['para', ['ul', 'ol', 'paragraph']],
                ['insert', withMedia ? ['link', 'picture', 'table', 'hr'] : ['link', 'hr']],
                ['view', ['codeview', 'fullscreen']]
            ]
        });
    }
    function initDetailsEditor() {
        if (!detailsReady) {
            initEditor($('#productDetails'), 340, true);
            detailsReady = true;
        }
        if (!shortDetailsReady) {
            initEditor($('#productShortDetails'), 180, false);
            shortDetailsReady = true;
        }
        if (!detailsEnReady) {
            initEditor($('#productDetailsEn'), 340, true);
            detailsEnReady = true;
        }
        if (!shortDetailsEnReady) {
            initEditor($('#productShortDetailsEn'), 180, false);
            shortDetailsEnReady = true;
        }
    }
    $(document).on('shown.bs.tab', 'a[href="#tab-details"], a[href="#tab-english"]', function () {
        initDetailsEditor();
        if ($(this).attr('href') !== '#tab-details') {
            return;
        }
        var html = $('#productDetails').next('.note-editor').length
            ? $('#productDetails').summernote('code')
            : ($('#productDetails').val() || '');
        var text = $.trim($('<div>').html(html).text());
        if (text === '' && $('#fetchDetailsBtn').length && !$('#fetchDetailsBtn').data('auto')) {
            $('#fetchDetailsBtn').data('auto', 1).trigger('click');
        }
    });

    $('#fetchDetailsBtn').on('click', function () {
        var $btn = $(this);
        var $status = $('#fetchDetailsStatus');
        $btn.prop('disabled', true);
        $status.text('Fetching source page…');
        $.post(<?= json_encode(base_url('admin/products/fetch_details/' . ($isEdit ? (int) $product->id : 0))) ?>, function (res) {
            if (res && res.ok && res.details) {
                initDetailsEditor();
                setDetailsHtml(res.details);
                $status.text('Details loaded. Save the product to keep them.');
            } else {
                $status.text((res && res.error) ? res.error : 'Could not read details from the source link.');
            }
        }, 'json').fail(function (xhr) {
            var msg = 'Could not read details from the source link.';
            if (xhr.responseJSON && xhr.responseJSON.error) {
                msg = xhr.responseJSON.error;
            }
            $status.text(msg);
        }).always(function () {
            $btn.prop('disabled', false);
        });
    });

    function editorHtml($el) {
        if ($el.next('.note-editor').length) {
            return $el.summernote('code') || '';
        }
        return $el.val() || '';
    }
    function setEditorHtml($el, html) {
        if ($el.next('.note-editor').length) {
            $el.summernote('code', html);
            return;
        }
        $el.val(html);
    }
    $('.writeAiBtn').on('click', function () {
        var $btns = $('.writeAiBtn');
        var $status = $('.writeAiStatus');
        $btns.prop('disabled', true);
        $status.text('Generating AI content…');
        initDetailsEditor();
        $.post(<?= json_encode(base_url('admin/products/write-ai/' . ($isEdit ? (int) $product->id : 0))) ?>, {
            name: $('input[name="name"]').val(),
            brand: $('input[name="brand"]').val(),
            country_id: $('#productCountry').val(),
            short_details: editorHtml($('#productShortDetails')),
            details: editorHtml($('#productDetails'))
        }, function (res) {
            if (!res || !res.ok) {
                $status.text((res && res.error) ? res.error : 'AI generation failed.');
                return;
            }
            $('input[name="name"]').val(res.name || res.title || '');
            initDetailsEditor();
            setEditorHtml($('#productShortDetails'), res.short_details || '');
            setEditorHtml($('#productDetails'), res.details || '');
            $('input[name="slug"]').val(res.slug || '');
            $('input[name="seo_title"]').val(res.seo_title || '');
            $('textarea[name="seo_description"]').val(res.seo_description || '');
            $('input[name="seo_keywords"]').val(res.seo_keywords || '');
            $('input[name="name_en"]').val(res.name_en || '');
            setEditorHtml($('#productShortDetailsEn'), res.short_details_en || '');
            setEditorHtml($('#productDetailsEn'), res.details_en || '');
            $('input[name="seo_title_en"]').val(res.seo_title_en || '');
            $('textarea[name="seo_description_en"]').val(res.seo_description_en || '');
            $('input[name="seo_keywords_en"]').val(res.seo_keywords_en || '');
            applyAiCategories(res);
            var catNote = '';
            if (res.category_name) {
                catNote = ' Category: ' + res.category_name + (res.subcategory_name ? ' / ' + res.subcategory_name : '') + '.';
            }
            $status.text('AI content loaded. Review the fields, then save the product to keep them.' + catNote);
        }, 'json').fail(function (xhr) {
            var msg = 'AI generation failed.';
            if (xhr.responseJSON && xhr.responseJSON.error) {
                msg = xhr.responseJSON.error;
            }
            $status.text(msg);
        }).always(function () {
            $btns.prop('disabled', false);
        });
    });
    function syncOfferTypeFields() {
        var t = $('#offerTypeSelect').val() || 'percent';
        $('.offer-field-value').toggle(t === 'percent' || t === 'fixed');
        $('.offer-field-bxgy').toggle(t === 'buy_x_get_y');
        $('.offer-field-bundle').toggle(t === 'bundle');
    }
    $('#offerTypeSelect').on('change', syncOfferTypeFields);
    syncOfferTypeFields();
})(jQuery);
</script>

