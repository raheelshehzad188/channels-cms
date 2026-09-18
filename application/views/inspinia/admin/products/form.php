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
                        <li><a data-toggle="tab" href="#tab-images">Images</a></li>
                        <li><a data-toggle="tab" href="#tab-pricing">Pricing</a></li>
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
                                    <div class="col-sm-9"><input type="text" name="name" class="form-control" required value="<?= htmlspecialchars($val('name')) ?>"></div>
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
                                <p class="text-muted">Full product details from the imported source page. Edit with the editor below.</p>
                                <textarea name="details" id="productDetails" class="form-control" rows="14"><?= htmlspecialchars((string) $val('details')) ?></textarea>
                                <?php if ($isEdit): ?>
                                <p style="margin-top:12px;">
                                    <button type="button" class="btn btn-white btn-sm" id="fetchDetailsBtn">
                                        <i class="fa fa-download"></i> Load details from source link
                                    </button>
                                    <span class="text-muted" id="fetchDetailsStatus" style="margin-left:8px;"></span>
                                </p>
                                <?php endif; ?>
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
                                    <label class="col-sm-2 control-label">Stock</label>
                                    <div class="col-sm-9"><input type="number" name="stock" class="form-control" required value="<?= (int) $val('stock', 0) ?>"></div>
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
                                <p class="text-muted">Customers see a delivery date range calculated from today plus these days.</p>
                                <div class="form-group">
                                    <label class="col-sm-2 control-label">Minimum days</label>
                                    <div class="col-sm-4">
                                        <input type="number" min="0" step="1" name="ship_min_days" id="shipMinDays" class="form-control" value="<?= (int) $val('ship_min_days', 0) ?>">
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-sm-2 control-label">Maximum days</label>
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
    function setDetailsHtml(html) {
        if ($('#productDetails').next('.note-editor').length) {
            $('#productDetails').summernote('code', html || '');
        } else {
            $('#productDetails').val(html || '');
        }
    }
    function initDetailsEditor() {
        if (detailsReady || !$('#productDetails').length || typeof $.fn.summernote !== 'function') {
            return;
        }
        $('#productDetails').summernote({
            height: 340,
            toolbar: [
                ['style', ['style']],
                ['font', ['bold', 'italic', 'underline', 'clear']],
                ['para', ['ul', 'ol', 'paragraph']],
                ['insert', ['link', 'picture', 'table', 'hr']],
                ['view', ['codeview', 'fullscreen']]
            ]
        });
        detailsReady = true;
    }
    $(document).on('shown.bs.tab', 'a[href="#tab-details"]', function () {
        initDetailsEditor();
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
})(jQuery);
</script>

