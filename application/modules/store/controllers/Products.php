<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require_once APPPATH . 'modules/store/controllers/Store_base.php';

class Products extends Store_base {

    public function __construct()
    {
        parent::__construct();
        $this->load->model('Store_product_model');
    }

    public function index()
    {
        $this->requireAuth();
        $this->load->model('Ec_category_model');
        $filters = $this->available_filters();
        $products = $this->Store_product_model->available_for_store($this->store, $filters);
        $copied = $this->Store_product_model->copied_source_ids($this->store->id);
        ec_refresh_store_copy_costs((int) $this->store->id);
        foreach ($products as $product) {
            $product->base_price = product_base_price($product);
            $product->wholesale_price = product_wholesale_price($product, $this->store->id);
            $product->store_markup = product_store_markup($this->store->id, $product->id);
            $product->customer_price = product_customer_price($product, $this->store->id);
            $product->copied_id = isset($copied[$product->id]) ? $copied[$product->id] : 0;
        }

        $this->template->store('products/index', $this->viewData(array(
            'page' => 'Available Products',
            'title' => 'Available Products',
            'products' => $products,
            'filters' => $filters,
            'category_tree' => $filters['category_tree'],
            'has_filters' => ($filters['q'] !== '' || $filters['category_id'] > 0 || $filters['subcategory_id'] > 0),
            'platform_fee_percent' => platform_fee_percent(),
        )));
    }

    protected function available_filters()
    {
        $q = trim((string) $this->input->get('q'));
        $categoryId = (int) $this->input->get('category_id');
        $subcategoryId = (int) $this->input->get('subcategory_id');
        $countryId = (int) (isset($this->store->country_id) ? $this->store->country_id : 0);
        $tree = $this->Ec_category_model->tree_for_country($countryId);

        $validCategory = false;
        $subParent = 0;
        foreach ($tree as $parent) {
            $parentId = (int) $parent['id'];
            if ($parentId === $categoryId) {
                $validCategory = true;
            }
            foreach ($parent['children'] as $child) {
                if ((int) $child['id'] === $subcategoryId) {
                    $subParent = $parentId;
                }
            }
        }
        if (!$validCategory) {
            $categoryId = 0;
        }
        if ($subParent < 1) {
            $subcategoryId = 0;
        } elseif ($categoryId > 0 && $categoryId !== $subParent) {
            $subcategoryId = 0;
        } elseif ($categoryId < 1 && $subParent > 0) {
            $categoryId = $subParent;
        }

        return array(
            'q' => $q,
            'category_id' => $categoryId,
            'subcategory_id' => $subcategoryId,
            'category_tree' => $tree,
        );
    }

    public function mine()
    {
        $this->requireAuth();
        $this->template->store('products/mine', $this->viewData(array(
            'page' => 'My Products',
            'title' => 'My Products',
            'products' => $this->Store_product_model->mine($this->store->id),
        )));
    }

    public function add($id = 0)
    {
        $this->requireAuth();
        $existing = $this->Store_product_model->find_copy($this->store->id, $id);
        if ($existing) {
            redirect('store/products/form/' . $existing->id);
            return;
        }

        $source = $this->Store_product_model->catalog_item_for_store($this->store, $id);
        if (!$source) {
            $this->session->set_flashdata('error', 'This product is not available for your store.');
            redirect('store/products');
            return;
        }

        $copyId = $this->Store_product_model->copy_from_catalog($this->store, $source);
        $this->session->set_flashdata('success', 'Product copied to your store. You can now edit it.');
        redirect('store/products/form/' . $copyId);
    }

    public function form($id = 0)
    {
        $this->requireAuth();
        $this->load->model('Ec_category_model');
        $product = $id ? $this->Store_product_model->get_owned($this->store->id, $id) : null;
        if ($id && !$product) {
            $this->session->set_flashdata('error', 'Product not found in your store.');
            redirect('store/my-products');
            return;
        }
        if ($product) {
            ec_refresh_store_copy_costs((int) $this->store->id);
            $product = $this->Store_product_model->get_owned($this->store->id, $id);
        }

        $this->template->store('products/form', $this->viewData(array(
            'page' => $product ? 'Edit Product' : 'Add Product',
            'title' => $product ? 'Edit Product' : 'Add Product',
            'product' => $product,
            'images' => $product ? $this->Store_product_model->images($product->id) : array(),
            'all_categories' => $this->Ec_category_model->all_active((int) (isset($this->store->country_id) ? $this->store->country_id : 0)),
            'product_category_ids' => $product ? $this->Ec_category_model->ids_for_product($product->id) : array(),
        )));
    }

    public function save($id = 0)
    {
        $this->requireAuth();
        $this->load->model('Ec_category_model');
        $existing = $id ? $this->Store_product_model->get_owned($this->store->id, $id) : null;
        if ($id && !$existing) {
            $this->session->set_flashdata('error', 'Product not found in your store.');
            redirect('store/my-products');
            return;
        }

        // Existing store products: images + SEO + selling price + categories.
        if ($existing) {
            $costPrice = (float) $existing->cost_price;
            $maxSale = (float) $existing->max_sale_price;
            $price = (float) $this->input->post('price');

            if ($costPrice > 0 && $price < $costPrice) {
                $this->session->set_flashdata('error', 'Selling price cannot be less than your cost price (' . number_format($costPrice, 2) . ').');
                redirect('store/products/form/' . $id);
                return;
            }

            $slug = url_title(trim($this->input->post('slug')) ?: $existing->name, 'dash', true);
            $payload = array(
                'price' => $price,
                'slug' => $this->Store_product_model->unique_slug($slug, $this->store->id, $id),
                'made_by' => trim((string) $this->input->post('made_by')),
                'seo_title' => trim((string) $this->input->post('seo_title')),
                'seo_description' => trim((string) $this->input->post('seo_description')),
                'seo_keywords' => trim((string) $this->input->post('seo_keywords')),
                'ship_min_days' => max(0, (int) $this->input->post('ship_min_days')),
                'ship_max_days' => max(0, (int) $this->input->post('ship_max_days')),
            );
            $image = $this->upload_image('image');
            if ($image) {
                $payload['image'] = $image;
            }
            $productId = $this->Store_product_model->save($payload, $id);
            $this->save_gallery($productId);
            $cats = $this->input->post('categories');
            $this->Ec_category_model->set_product_categories($productId, is_array($cats) ? $cats : array());
            $this->channel_sync_product($productId);

            if ($maxSale > 0 && $price > $maxSale) {
                $this->session->set_flashdata('success', 'Product updated. Warning: selling price is above the recommended maximum (' . number_format($maxSale, 2) . ').');
            } else {
                $this->session->set_flashdata('success', 'Product updated.');
            }
            redirect('store/products/form/' . $productId);
            return;
        }

        $name = trim($this->input->post('name'));
        if ($name === '') {
            $this->session->set_flashdata('error', 'Title is required.');
            redirect('store/products/form');
            return;
        }

        $slug = url_title(trim($this->input->post('slug')) ?: $name, 'dash', true);
        $payload = array(
            'store_id' => (int) $this->store->id,
            'country_id' => $this->store->country_id,
            'name' => $name,
            'sku' => trim((string) $this->input->post('sku')),
            'parent_sku' => trim((string) $this->input->post('parent_sku')),
            'is_default' => ($this->input->post('parent_sku') && $this->input->post('is_default')) ? 1 : 0,
            'brand' => trim((string) $this->input->post('brand')),
            'made_by' => trim((string) $this->input->post('made_by')),
            'slug' => $this->Store_product_model->unique_slug($slug, $this->store->id, 0),
            'description' => trim((string) $this->input->post('description')),
            'details' => ec_sanitize_product_html($this->input->post('details')),
            'price' => (float) $this->input->post('price'),
            'compare_price' => (float) $this->input->post('compare_price'),
            'stock' => (int) $this->input->post('stock'),
            'seo_title' => trim((string) $this->input->post('seo_title')),
            'seo_description' => trim((string) $this->input->post('seo_description')),
            'seo_keywords' => trim((string) $this->input->post('seo_keywords')),
            'status' => (int) $this->input->post('status') === 1 ? 1 : 0,
            'max_sale_price' => 0,
            'cost_price' => 0,
            'ship_min_days' => max(0, (int) $this->input->post('ship_min_days')),
            'ship_max_days' => max(0, (int) $this->input->post('ship_max_days')),
            'image' => '',
        );

        $image = $this->upload_image('image');
        if ($image) {
            $payload['image'] = $image;
        }

        $productId = $this->Store_product_model->save($payload, 0);
        if (function_exists('product_sync_default_child')) {
            product_sync_default_child($productId);
        }
        $this->save_gallery($productId);
        $this->channel_sync_product($productId);
        $this->session->set_flashdata('success', 'Product created.');
        redirect('store/products/form/' . $productId);
    }

    public function delete($id = 0)
    {
        $this->requireAuth();
        $product = $this->Store_product_model->get_owned($this->store->id, $id);
        if (!$product) {
            $this->session->set_flashdata('error', 'Product not found.');
            redirect('store/my-products');
            return;
        }
        $this->channel_delete_product($id);
        $this->Store_product_model->delete_owned($this->store->id, $id);
        $this->session->set_flashdata('success', 'Product deleted.');
        redirect('store/my-products');
    }

    public function delete_image($id = 0)
    {
        $this->requireAuth();
        $productId = $this->Store_product_model->delete_image($this->store->id, $id);
        if (!$productId) {
            $this->session->set_flashdata('error', 'Image not found.');
            redirect('store/my-products');
            return;
        }
        $this->session->set_flashdata('success', 'Image deleted.');
        redirect('store/products/form/' . $productId);
    }

    protected function save_gallery($productId)
    {
        if (empty($_FILES['gallery']['name']) || !is_array($_FILES['gallery']['name'])) {
            return;
        }
        $count = count($_FILES['gallery']['name']);
        for ($i = 0; $i < $count; $i++) {
            if (empty($_FILES['gallery']['name'][$i]) || (int) $_FILES['gallery']['error'][$i] !== 0) {
                continue;
            }
            $_FILES['gallery_item'] = array(
                'name' => $_FILES['gallery']['name'][$i],
                'type' => $_FILES['gallery']['type'][$i],
                'tmp_name' => $_FILES['gallery']['tmp_name'][$i],
                'error' => $_FILES['gallery']['error'][$i],
                'size' => $_FILES['gallery']['size'][$i],
            );
            $path = $this->upload_image('gallery_item');
            if ($path) {
                $this->Store_product_model->add_image($productId, $path);
            }
        }
    }

    protected function upload_image($field)
    {
        if (empty($_FILES[$field]['name'])) {
            return '';
        }
        $dir = FCPATH . 'uploads/products/';
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        $config = array(
            'upload_path' => $dir,
            'allowed_types' => 'jpg|jpeg|png|gif|webp',
            'max_size' => 4096,
            'encrypt_name' => true,
        );
        $this->load->library('upload', $config);
        $this->upload->initialize($config);
        if (!$this->upload->do_upload($field)) {
            return '';
        }
        $uploaded = $this->upload->data();
        return 'uploads/products/' . $uploaded['file_name'];
    }

    protected function channel_sync_product($productId)
    {
        $this->load->model('Store_channel_model');
        $this->Store_channel_model->queue_sync_product($this->store, $productId);
    }

    protected function channel_delete_product($productId)
    {
        $this->load->model('Store_channel_model');
        $this->Store_channel_model->queue_delete_product($this->store, $productId);
    }
}
