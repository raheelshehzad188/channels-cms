<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Shop extends CI_Controller {

    protected $store;
    protected $theme;
    protected $settings = array();

    public function __construct()
    {
        parent::__construct();
        $this->load->library('tenant');
        $hint = $this->input->get('domain');
        $this->store = $this->tenant->resolve($hint ? $hint : null);
        $this->theme = $this->tenant->get_theme();
        $this->settings = $this->tenant->get_settings();

        if (!$this->store || !$this->theme) {
            show_error('No active store theme is configured for this domain.', 404);
            return;
        }
        if (function_exists('ensure_user_commission_schema')) {
            ensure_user_commission_schema();
        }
        $this->store = hydrate_store_currency($this->store);
    }

    public function index()
    {
        $this->load->model('Ec_category_model');
        $homeCategories = $this->Ec_category_model->home_for_store($this->store->id);
        foreach ($homeCategories as $cat) {
            $this->Ec_category_model->resolve_for_store($cat, $this->store->id);
        }
        $heroSlides = array();
        if ($this->theme && $this->theme->slug === 'zenvello') {
            $this->load->model('Store_hero_model');
            $heroSlides = $this->Store_hero_model->active_for_store($this->store->id);
        }
        $this->render('home', array(
            'title' => $this->store->name,
            'products' => $this->products(8),
            'home_categories' => $homeCategories,
            'hero_slides' => $heroSlides,
        ));
    }

    public function category($slug = '')
    {
        $this->load->model('Ec_category_model');
        $slug = trim((string) $slug);
        $countryId = (int) (isset($this->store->country_id) ? $this->store->country_id : 0);
        $category = $this->Ec_category_model->get_by_slug($slug, $countryId);
        if (!$category) {
            show_404();
        }

        $setting = $this->Ec_category_model->store_setting($this->store->id, $category->id);
        if (!$setting || !(int) $setting->enabled) {
            // allow direct visit for enabled parent children too
            $parentOk = false;
            if (!empty($category->parent_id)) {
                $parentSetting = $this->Ec_category_model->store_setting($this->store->id, $category->parent_id);
                $parentOk = $parentSetting && (int) $parentSetting->enabled;
            }
            if (!$parentOk) {
                show_404();
            }
        }

        $category = $this->Ec_category_model->resolve_for_store($category, $this->store->id);
        $subcategories = $this->Ec_category_model->storefront_children(
            $category->id,
            $this->store->id,
            setting_flag_on($this->settings, 'hide_empty_subcategories')
        );

        $pageSize = 12;
        $categoryIds = $this->Ec_category_model->category_product_ids($category->id, true);
        $products = $this->products_in_categories($categoryIds, $pageSize, 0);
        $total = $this->count_in_categories($categoryIds);

        $defaultHero = theme_setting($this->settings, 'category_hero_default', '');
        $hero = $category->display_hero !== '' ? $category->display_hero : $defaultHero;

        $this->render('category', array(
            'title' => $category->display_seo_title . ' — ' . $this->store->name,
            'meta_description' => $category->display_seo_description,
            'meta_keywords' => $category->display_seo_keywords,
            'page_slug' => 'category',
            'category' => $category,
            'subcategories' => $subcategories,
            'products' => $products,
            'category_hero' => $hero,
            'products_total' => $total,
            'products_page_size' => $pageSize,
            'has_more' => $total > count($products),
            'active_category_slug' => $category->slug,
            'load_more_url' => storefront_url('category/' . rawurlencode($category->slug) . '/more'),
        ));
    }

    public function category_more($slug = '')
    {
        $this->load->model('Ec_category_model');
        $slug = trim((string) $slug);
        $countryId = (int) (isset($this->store->country_id) ? $this->store->country_id : 0);
        $category = $this->Ec_category_model->get_by_slug($slug, $countryId);
        if (!$category) {
            $this->output->set_status_header(404)->set_content_type('application/json')
                ->set_output(json_encode(array('ok' => false)));
            return;
        }

        $offset = max(0, (int) $this->input->get('offset'));
        $pageSize = 12;
        $categoryIds = $this->Ec_category_model->category_product_ids($category->id, true);
        $products = $this->products_in_categories($categoryIds, $pageSize, $offset);
        $total = $this->count_in_categories($categoryIds);

        $html = '';
        $slugTheme = $this->theme->slug;
        $assets = storefront_asset_url('assets/frontend/' . $slugTheme . '/');
        foreach ($products as $product) {
            ob_start();
            $this->load->view('frontend/' . $slugTheme . '/product_card', array(
                'product' => $product,
                'assets' => $assets,
                'is_preview' => false,
            ));
            $html .= ob_get_clean();
        }

        $this->output->set_content_type('application/json')->set_output(json_encode(array(
            'ok' => true,
            'html' => $html,
            'count' => count($products),
            'next_offset' => $offset + count($products),
            'has_more' => ($offset + count($products)) < $total,
        )));
    }

    public function catalog()
    {
        $this->load->model('Ec_category_model');
        $filters = $this->catalog_filters();
        $products = $this->filtered_products($filters);
        $categories = $this->Ec_category_model->with_counts_for_store(
            $this->store->id,
            (int) (isset($this->store->country_id) ? $this->store->country_id : 0)
        );
        $activeCategory = null;
        if (!empty($filters['category'])) {
            $activeCategory = $this->Ec_category_model->get_by_slug(
                $filters['category'],
                (int) (isset($this->store->country_id) ? $this->store->country_id : 0)
            );
        }

        $this->render('shop', array(
            'title' => ($activeCategory ? category_store_name($activeCategory) . ' — ' : 'Shop — ') . $this->store->name,
            'products' => $products,
            'categories' => $categories,
            'filters' => $filters,
            'active_category' => $activeCategory,
            'price_bounds' => $this->price_bounds(),
        ));
    }

    public function detail($key = '')
    {
        $key = rawurldecode(trim((string) $key));
        if ($key === '') {
            show_404();
        }

        $product = $this->catalog_query()
            ->where('products.slug', $key)
            ->get()
            ->row();
        if (!$product && ctype_digit($key)) {
            $product = $this->catalog_query()
                ->where('products.id', (int) $key)
                ->get()
                ->row();
            if ($product && !$this->matches_store_product($product)) {
                show_404();
            }
            $slug = ($product && isset($product->slug)) ? trim((string) $product->slug) : '';
            if ($product && $slug !== '' && $slug !== $key) {
                redirect(product_url($product), 'location', 301);
                return;
            }
        }
        if (!$product || !$this->matches_store_product($product)) {
            show_404();
        }
        $openedProduct = $product;
        $family = function_exists('product_family')
            ? product_family($product, true)
            : array('parent' => $product, 'selected' => $product, 'children' => array());
        $display = $family['parent'] ? $family['parent'] : $product;
        $cartProduct = $family['selected'] ? $family['selected'] : $product;
        apply_storefront_pricing($display, $this->store->id);
        apply_storefront_pricing($cartProduct, $this->store->id);
        apply_storefront_pricing($family['children'], $this->store->id);

        $this->load->model('Ec_category_model');
        $trail = $this->Ec_category_model->trail_for_storefront($display->id, $this->store->id);

        $this->load->library('channel_events');
        $tracking = $this->channel_events->view_content($this->store, $openedProduct);

        $detailData = array(
            'title' => $display->name . ' — ' . $this->store->name,
            'tracking' => $tracking,
            'product' => $display,
            'cart_product' => $cartProduct,
            'child_products' => $family['children'],
            'product_images' => $this->product_gallery_rows($display->id),
            'product_category' => $trail['category'],
            'product_subcategory' => $trail['subcategory'],
            'products' => $this->products(8),
            'canonical_url' => product_url($openedProduct),
            'pdp_design' => product_detail_design($this->settings),
        );

        if ($detailData['pdp_design'] === 'new') {
            $this->load->model('Product_faq_model');
            $this->load->model('Product_review_model');
            $reviewPage = max(1, (int) $this->input->get('reviews_page'));
            $reviewLimit = 8;
            $reviewOffset = ($reviewPage - 1) * $reviewLimit;
            $reviewSummary = $this->Product_review_model->summary($this->store->id, $display->id);
            $reviewTotal = (int) $reviewSummary['count'];
            $childGalleries = array();
            foreach ($family['children'] as $child) {
                $childGalleries[(int) $child->id] = product_gallery_urls($child, $this->product_gallery_rows($child->id));
            }
            $detailData['products'] = $this->related_products($display, $trail, 8);
            $detailData['product_attributes'] = product_attributes_rows($display->id);
            $detailData['product_faqs'] = $this->Product_faq_model->for_product($this->store->id, $display->id, true);
            $detailData['product_reviews'] = $this->Product_review_model->published_for_product($this->store->id, $display->id, $reviewLimit, $reviewOffset);
            $detailData['review_summary'] = $reviewSummary;
            $detailData['review_page'] = $reviewPage;
            $detailData['review_limit'] = $reviewLimit;
            $detailData['review_total'] = $reviewTotal;
            $detailData['review_pages'] = $reviewLimit > 0 ? (int) ceil($reviewTotal / $reviewLimit) : 1;
            $detailData['child_galleries'] = $childGalleries;
            $detailData['in_wishlist'] = product_in_wishlist($this->store->id, $display->id);
            $detailData['open_tab'] = trim((string) $this->input->get('tab'));
            $detailData['flash_success'] = ec_take_flash('success');
            $detailData['flash_error'] = ec_take_flash('error');
        }

        $this->render('detail', $detailData);
    }

    public function page($slug = 'contact')
    {
        $slug = strtolower(preg_replace('/[^a-z0-9\-]/', '', (string) $slug));
        if ($slug === 'contact') {
            if (strtoupper((string) $this->input->method()) === 'POST') {
                $this->submit_contact();
                return;
            }
            $this->render('contact', array(
                'title' => 'Contact — ' . $this->store->name,
                'page_slug' => 'contact',
                'page_title' => 'Contact Us',
                'meta_description' => 'Contact the ' . $this->store->name . ' team by message, email or phone. We reply within one working day.',
                'flash_success' => ec_take_flash('success'),
                'flash_error' => ec_take_flash('error'),
                'form' => $this->session->flashdata('contact_form') ?: array(),
            ));
            return;
        }
        show_404();
    }

    public function cart()
    {
        $this->load->library('channel_events');
        $this->render('cart', array_merge(array(
            'title' => 'Cart — ' . $this->store->name,
            'cart_items' => $this->cart_items(),
            'tracking' => $this->channel_events->consume_browser_event($this->store),
            'flash_success' => ec_take_flash('success'),
            'flash_error' => ec_take_flash('error'),
        ), $this->cart_totals()));
    }

    public function add_to_cart($id = 0)
    {
        $product = $this->catalog_query()->where('products.id', (int) $id)->get()->row();
        if (!$product || !$this->matches_store_product($product)) {
            show_404();
        }
        if (function_exists('product_family')) {
            $family = product_family($product, true);
            if (!empty($family['selected']) && (int) $family['selected']->id !== (int) $product->id) {
                $mapped = $this->catalog_query()->where('products.id', (int) $family['selected']->id)->get()->row();
                if ($mapped) {
                    $product = $mapped;
                    $id = (int) $mapped->id;
                }
            }
        }
        $stock = (int) $product->stock;
        if ($stock <= 0) {
            $this->session->set_flashdata('error', 'This product is out of stock.');
            redirect(product_url($product));
            return;
        }
        $qty = (int) $this->input->get_post('qty');
        if ($qty < 1) {
            $qty = 1;
        }
        $qty = min($qty, min(10, $stock));
        $cart = storefront_cart($this->store->id);
        $id = (int) $id;
        $existing = isset($cart[$id]) ? (int) $cart[$id] : 0;
        $add = min($qty, max(0, $stock - $existing));
        if ($add < 1) {
            $this->session->set_flashdata('error', 'No more stock available for this product.');
            redirect(storefront_url('cart'));
            return;
        }
        $cart[$id] = $existing + $add;
        $_SESSION['storefront_cart'][$this->store->id] = $cart;
        apply_storefront_pricing($product, $this->store->id);
        $this->load->library('channel_events');
        $this->channel_events->add_to_cart($this->store, $product, $add);
        $this->session->set_flashdata('success', 'Product added to cart.');
        $next = strtolower(trim((string) $this->input->get_post('next')));
        if ($next === 'checkout') {
            redirect(storefront_url('checkout'));
            return;
        }
        redirect(storefront_url('cart'));
    }

    public function wishlist_toggle($id = 0)
    {
        $product = $this->catalog_query()->where('products.id', (int) $id)->get()->row();
        if (!$product || !$this->matches_store_product($product)) {
            show_404();
        }
        $storeId = (int) $this->store->id;
        $id = (int) $id;
        $list = storefront_wishlist($storeId);
        $added = false;
        if (isset($list[$id])) {
            unset($list[$id]);
        } else {
            $list[$id] = $id;
            $added = true;
        }
        $_SESSION['storefront_wishlist'][$storeId] = array_values($list);
        if ($this->input->is_ajax_request() || $this->input->get('ajax')) {
            $this->output->set_content_type('application/json')->set_output(json_encode(array(
                'ok' => true,
                'added' => $added,
            )));
            return;
        }
        $this->session->set_flashdata('success', $added ? 'Added to wishlist.' : 'Removed from wishlist.');
        redirect(product_url($product));
    }

    public function submit_review($id = 0)
    {
        if (strtoupper((string) $this->input->method()) !== 'POST') {
            redirect(storefront_url('shop'));
            return;
        }
        $product = $this->catalog_query()->where('products.id', (int) $id)->get()->row();
        if (!$product || !$this->matches_store_product($product)) {
            show_404();
        }
        $family = function_exists('product_family')
            ? product_family($product, true)
            : array('parent' => $product);
        $display = !empty($family['parent']) ? $family['parent'] : $product;
        $customer = $this->current_customer();
        if (!$customer) {
            $this->session->set_flashdata('error', 'Please login to write a review.');
            redirect(storefront_url('account/login'));
            return;
        }
        $name = trim((string) $this->input->post('customer_name'));
        if ($name === '') {
            $name = trim((string) $customer->name);
        }
        $content = trim((string) $this->input->post('content'));
        $title = trim((string) $this->input->post('title'));
        $rating = (int) $this->input->post('rating');
        if ($name === '' || $content === '') {
            $this->session->set_flashdata('error', 'Please add your name and review.');
            redirect(product_url($display) . '?tab=reviews');
            return;
        }
        $this->load->model('Product_review_model');
        $this->Product_review_model->save($this->store->id, array(
            'product_id' => (int) $display->id,
            'customer_id' => (int) $customer->id,
            'customer_name' => $name,
            'rating' => $rating,
            'title' => $title,
            'content' => $content,
            'status' => 1,
            'approval_status' => 'pending',
        ), 0);
        $this->session->set_flashdata('success', 'Thanks for your review. It will appear after approval.');
        redirect(product_url($display) . '?tab=reviews');
    }

    public function update_cart()
    {
        $qty = $this->input->post('qty');
        $cart = array();
        if (is_array($qty)) {
            foreach ($qty as $id => $value) {
                $value = (int) $value;
                if ($value > 0) {
                    $cart[(int) $id] = $value;
                }
            }
        }
        $_SESSION['storefront_cart'][$this->store->id] = $cart;
        $this->session->set_flashdata('success', 'Cart updated.');
        redirect(storefront_url('cart'));
    }

    public function remove_from_cart($id = 0)
    {
        $cart = storefront_cart($this->store->id);
        unset($cart[(int) $id]);
        $_SESSION['storefront_cart'][$this->store->id] = $cart;
        redirect(storefront_url('cart'));
    }

    public function checkout()
    {
        $customer = $this->current_customer();
        if (!$customer) {
            $this->session->set_flashdata('error', 'Please login to checkout.');
            redirect(storefront_url('account/login'));
            return;
        }
        if (!$this->cart_items()) {
            $this->session->set_flashdata('error', 'Your cart is empty.');
            redirect(storefront_url('cart'));
            return;
        }

        if ($this->input->post()) {
            $shipping = array(
                'name' => trim($this->input->post('name')),
                'email' => strtolower(trim($this->input->post('email'))),
                'phone' => trim((string) $this->input->post('phone')),
                'address' => trim($this->input->post('address')),
            );
            if ($shipping['name'] === '' || $shipping['email'] === '' || $shipping['address'] === '' || $shipping['phone'] === '') {
                $this->session->set_flashdata('error', 'Name, email, WhatsApp number and address are required.');
                redirect(storefront_url('checkout'));
                return;
            }

            $this->db->where('id', (int) $customer->id)->where('store_id', $this->store->id)->update('store_customers', array(
                'name' => $shipping['name'],
                'phone' => $shipping['phone'],
                'address' => $shipping['address'],
            ));

            $totals = $this->cart_totals();
            $_SESSION['checkout_draft'][$this->store->id] = array(
                'shipping' => $shipping,
                'customer_id' => (int) $customer->id,
                'amount' => $totals['cart_total'],
                'subtotal' => $totals['cart_subtotal'],
                'vat_amount' => $totals['vat_amount'],
                'created_at' => time(),
            );
            redirect(storefront_url('payment'));
            return;
        }

        $this->render('checkout', array_merge(array(
            'title' => 'Checkout — ' . $this->store->name,
            'customer' => $customer,
            'cart_items' => $this->cart_items(),
            'flash_error' => ec_take_flash('error'),
        ), $this->cart_totals()));
    }

    public function payment()
    {
        $customer = $this->current_customer();
        $draft = $this->checkout_draft();
        if (!$customer || !$draft) {
            $this->session->set_flashdata('error', 'Please complete shipping details first.');
            redirect(storefront_url('checkout'));
            return;
        }
        if (!$this->cart_items()) {
            $this->session->set_flashdata('error', 'Your cart is empty.');
            redirect(storefront_url('cart'));
            return;
        }

        $this->load->library('payment_crypto');
        $this->load->library('paypal');

        $this->render('payment', array_merge(array(
            'title' => 'Secure Payment — ' . $this->store->name,
            'customer' => $customer,
            'draft' => $draft,
            'cart_items' => $this->cart_items(),
            'flash_error' => ec_take_flash('error'),
            'flash_success' => ec_take_flash('success'),
            'paypal_client_id' => $this->paypal->client_id(),
            'paypal_mode' => $this->paypal->mode(),
            'pay_currency' => $this->paypal->currency($this->store),
            'card_public_key' => $this->payment_crypto->public_key_spki_base64(),
        ), $this->cart_totals()));
    }

    public function payment_card()
    {
        $customer = $this->current_customer();
        $draft = $this->checkout_draft();
        if (!$customer || !$draft || !$this->cart_items()) {
            $this->session->set_flashdata('error', 'Payment session expired. Please checkout again.');
            redirect(storefront_url('checkout'));
            return;
        }

        $this->load->library('payment_crypto');
        $this->load->library('paypal');

        $encrypted = trim((string) $this->input->post('card_payload'));
        $card = $this->payment_crypto->decrypt_payload($encrypted);
        if (!$card || empty($card['number']) || empty($card['exp_month']) || empty($card['exp_year']) || empty($card['cvv'])) {
            $this->session->set_flashdata('error', 'Could not read card details securely. Please try again.');
            redirect(storefront_url('payment'));
            return;
        }

        $amount = (float) $draft['amount'];
        $currency = $this->paypal->currency($this->store);
        $meta = array(
            'order_no' => 'TMP' . strtoupper(substr(uniqid(), -8)),
            'description' => $this->store->name . ' order',
        );
        $result = $this->paypal->pay_with_card($amount, $currency, array(
            'number' => $card['number'],
            'exp_month' => $card['exp_month'],
            'exp_year' => $card['exp_year'],
            'cvv' => $card['cvv'],
            'name' => !empty($card['name']) ? $card['name'] : $draft['shipping']['name'],
        ), $meta);

        if (!$result || empty($result['id'])) {
            $this->session->set_flashdata('error', $this->paypal->last_error() ?: 'Card payment failed.');
            redirect(storefront_url('payment'));
            return;
        }

        $status = isset($result['status']) ? strtoupper($result['status']) : '';
        if ($status === 'CREATED' || $status === 'APPROVED') {
            $captured = $this->paypal->capture_order($result['id']);
            if ($captured) {
                $result = $captured;
                $status = isset($result['status']) ? strtoupper($result['status']) : $status;
            }
        }

        if ($status !== 'COMPLETED' && $status !== 'APPROVED') {
            $this->session->set_flashdata('error', $this->paypal->last_error() ?: 'Card payment was not completed.');
            redirect(storefront_url('payment'));
            return;
        }

        $this->finalize_paid_order($customer, $draft, 'card', $result['id'], $currency, $amount);
    }

    public function payment_paypal()
    {
        $customer = $this->current_customer();
        $draft = $this->checkout_draft();
        if (!$customer || !$draft || !$this->cart_items()) {
            $this->session->set_flashdata('error', 'Payment session expired. Please checkout again.');
            redirect(storefront_url('checkout'));
            return;
        }

        $this->load->library('paypal');
        $amount = (float) $draft['amount'];
        $currency = $this->paypal->currency($this->store);
        $order = $this->paypal->create_order($amount, $currency, array(
            'order_no' => 'TMP' . strtoupper(substr(uniqid(), -8)),
            'description' => $this->store->name . ' order',
            'return_url' => storefront_url('payment/return'),
            'cancel_url' => storefront_url('payment/cancel'),
        ));

        if (!$order || empty($order['id'])) {
            $this->session->set_flashdata('error', $this->paypal->last_error() ?: 'Unable to start PayPal checkout.');
            redirect(storefront_url('payment'));
            return;
        }

        $_SESSION['checkout_draft'][$this->store->id]['paypal_order_id'] = $order['id'];
        $approve = $this->paypal->approve_link($order);
        if ($approve === '') {
            $this->session->set_flashdata('error', 'PayPal approval link missing.');
            redirect(storefront_url('payment'));
            return;
        }
        redirect($approve);
    }

    public function payment_return()
    {
        $customer = $this->current_customer();
        $draft = $this->checkout_draft();
        if (!$customer || !$draft || !$this->cart_items()) {
            $this->session->set_flashdata('error', 'Payment session expired.');
            redirect(storefront_url('checkout'));
            return;
        }

        $this->load->library('paypal');
        $paypalOrderId = $this->input->get('token') ?: (isset($draft['paypal_order_id']) ? $draft['paypal_order_id'] : '');
        if ($paypalOrderId === '') {
            $this->session->set_flashdata('error', 'Missing PayPal order reference.');
            redirect(storefront_url('payment'));
            return;
        }

        $captured = $this->paypal->capture_order($paypalOrderId);
        $status = $captured && !empty($captured['status']) ? strtoupper($captured['status']) : '';
        if ($status !== 'COMPLETED') {
            $this->session->set_flashdata('error', $this->paypal->last_error() ?: 'PayPal payment was not completed.');
            redirect(storefront_url('payment'));
            return;
        }

        $this->finalize_paid_order(
            $customer,
            $draft,
            'paypal',
            $paypalOrderId,
            $this->paypal->currency($this->store),
            (float) $draft['amount']
        );
    }

    public function payment_cancel()
    {
        $this->session->set_flashdata('error', 'PayPal payment was cancelled. You can try again.');
        redirect(storefront_url('payment'));
    }

    protected function checkout_draft()
    {
        $storeId = (int) $this->store->id;
        if (empty($_SESSION['checkout_draft'][$storeId]) || !is_array($_SESSION['checkout_draft'][$storeId])) {
            return null;
        }
        $draft = $_SESSION['checkout_draft'][$storeId];
        if (empty($draft['shipping']) || empty($draft['created_at']) || (time() - (int) $draft['created_at']) > 3600) {
            unset($_SESSION['checkout_draft'][$storeId]);
            return null;
        }
        return $draft;
    }

    protected function finalize_paid_order($customer, $draft, $method, $paypalOrderId, $currency, $amount)
    {
        $this->load->model('Ec_order_model');
        $order = $this->Ec_order_model->create_from_cart(
            $this->store,
            $customer,
            $this->cart_items(),
            $draft['shipping'],
            array(
                'payment_method' => $method,
                'payment_status' => 'paid',
                'paypal_order_id' => $paypalOrderId,
                'payment_currency' => $currency,
                'paid_amount' => $amount,
            )
        );
        $_SESSION['storefront_cart'][$this->store->id] = array();
        unset($_SESSION['checkout_draft'][$this->store->id]);
        $this->Ec_order_model->notify_status($order);

        $this->load->library('channel_events');
        $tracking = $this->channel_events->purchase($this->store, $order, $this->Ec_order_model->items($order->id));

        $this->render('thanks', array(
            'title' => 'Thank you — ' . $this->store->name,
            'order' => $order,
            'tracking' => $tracking,
        ));
    }

    public function customer_login()
    {
        if ($this->current_customer()) {
            redirect(storefront_url('account'));
        }
        if ($this->input->post()) {
            $email = strtolower(trim($this->input->post('email')));
            $row = $this->db
                ->where('store_id', $this->store->id)
                ->where('email', $email)
                ->get('store_customers')
                ->row();
            if ($row && $row->password === md5($this->input->post('password'))) {
                $this->set_customer($row);
                redirect(storefront_url('account'));
                return;
            }
            $this->session->set_flashdata('error', 'Invalid email or password.');
        }
        $this->render('login', array(
            'title' => 'Login — ' . $this->store->name,
            'flash_error' => ec_take_flash('error'),
            'flash_success' => ec_take_flash('success'),
        ));
    }

    public function customer_signup()
    {
        if ($this->current_customer()) {
            redirect(storefront_url('account'));
        }
        if ($this->input->post()) {
            $name = trim($this->input->post('name'));
            $email = strtolower(trim($this->input->post('email')));
            $password = $this->input->post('password');
            if ($name === '' || $email === '' || $password === '') {
                $this->session->set_flashdata('error', 'Name, email and password are required.');
            } else {
                $exists = $this->db->where('store_id', $this->store->id)->where('email', $email)->get('store_customers')->row();
                if ($exists) {
                    $this->session->set_flashdata('error', 'This email is already registered for this store.');
                } else {
                    $this->db->insert('store_customers', array(
                        'store_id' => $this->store->id,
                        'name' => $name,
                        'email' => $email,
                        'password' => md5($password),
                        'phone' => trim((string) $this->input->post('phone')),
                    ));
                    $row = $this->db->where('id', $this->db->insert_id())->get('store_customers')->row();
                    $this->set_customer($row);
                    redirect(storefront_url('account'));
                    return;
                }
            }
        }
        $this->render('signup', array(
            'title' => 'Sign up — ' . $this->store->name,
            'flash_error' => ec_take_flash('error'),
        ));
    }

    public function customer_profile()
    {
        $customer = $this->current_customer();
        if (!$customer) {
            redirect(storefront_url('account/login'));
            return;
        }
        if ($this->input->post()) {
            $payload = array(
                'name' => trim($this->input->post('name')),
                'email' => strtolower(trim($this->input->post('email'))),
                'phone' => trim((string) $this->input->post('phone')),
                'address' => trim((string) $this->input->post('address')),
            );
            if ($this->input->post('password') !== '') {
                $payload['password'] = md5($this->input->post('password'));
            }
            $this->db->where('id', $customer->id)->where('store_id', $this->store->id)->update('store_customers', $payload);
            $row = $this->db->where('id', $customer->id)->get('store_customers')->row();
            $this->set_customer($row);
            $this->session->set_flashdata('success', 'Profile updated.');
            redirect(storefront_url('account'));
            return;
        }
        $this->load->model('Ec_order_model');
        $this->render('profile', array(
            'title' => 'Account — ' . $this->store->name,
            'customer' => $customer,
            'orders' => $this->Ec_order_model->for_customer($this->store->id, $customer->id),
            'statuses' => Ec_order_model::statuses(),
            'flash_success' => ec_take_flash('success'),
            'flash_error' => ec_take_flash('error'),
        ));
    }

    public function customer_order($orderNo = '')
    {
        $customer = $this->current_customer();
        if (!$customer) {
            redirect(storefront_url('account/login'));
            return;
        }
        $this->load->model('Ec_order_model');
        $order = $this->Ec_order_model->for_customer_by_no($this->store->id, $customer->id, $orderNo);
        if (!$order) {
            $this->session->set_flashdata('error', 'Order not found.');
            redirect(storefront_url('account'));
            return;
        }
        $this->render('order', array(
            'title' => 'Order ' . $order->order_no . ' — ' . $this->store->name,
            'customer' => $customer,
            'order' => $order,
            'items' => $this->Ec_order_model->items($order->id),
            'logs' => $this->Ec_order_model->logs($order->id),
            'statuses' => Ec_order_model::statuses(),
        ));
    }

    public function customer_logout()
    {
        unset($_SESSION['storefront_customer']);
        redirect(storefront_url('account/login'));
    }

    protected function render($page, $data = array())
    {
        $slug = $this->theme->slug;
        $shared = array('cart', 'checkout', 'payment', 'login', 'signup', 'profile', 'order', 'thanks');
        $data['store'] = $this->store;
        $data['theme'] = $this->theme;
        $data['settings'] = $this->settings;
        $data['custom_css'] = store_custom_css($this->store);
        $data['customer'] = isset($data['customer']) ? $data['customer'] : $this->current_customer();
        $data['cart_count'] = storefront_cart_count($this->store->id);
        $data['current_page'] = isset($data['page_slug']) ? $data['page_slug'] : $page;
        $data['assets'] = storefront_asset_url('assets/frontend/' . $slug . '/');
        $data['setting'] = function ($key, $default = '') {
            return isset($this->settings[$key]) && $this->settings[$key] !== '' ? $this->settings[$key] : $default;
        };

        if (!isset($data['nav_categories'])) {
            $this->load->model('Ec_category_model');
            $countryId = (int) (isset($this->store->country_id) ? $this->store->country_id : 0);
            $data['nav_categories'] = $this->Ec_category_model->home_for_store($this->store->id);
            if (empty($data['nav_categories']) && $countryId) {
                $data['nav_categories'] = $this->Ec_category_model->for_store($this->store->id, $countryId, true);
                $roots = array();
                foreach ($data['nav_categories'] as $nav) {
                    if (empty($nav->parent_id)) {
                        $roots[] = $nav;
                    }
                }
                $data['nav_categories'] = $roots;
            }
        }
        if (!isset($data['active_category_slug'])) {
            $data['active_category_slug'] = trim((string) $this->input->get('category'));
        }
        if (!isset($data['tracking'])) {
            $this->load->library('channel_events');
            $data['tracking'] = $this->channel_events->tracking_payload($this->store);
        }

        if (!isset($data['pdp_design'])) {
            $data['pdp_design'] = product_detail_design($this->settings);
        }

        $this->load->view('frontend/' . $slug . '/header', $data);
        if (in_array($page, $shared, true)) {
            $this->load->view('frontend/shared/' . $page, $data);
        } elseif ($page === 'detail' && $data['pdp_design'] === 'new') {
            $this->load->view('frontend/shared/product_detail/new', $data);
        } elseif ($page === 'contact' && is_file(APPPATH . 'views/frontend/' . $slug . '/contact.php')) {
            $this->load->view('frontend/' . $slug . '/contact', $data);
        } elseif ($page === 'contact' && is_file(APPPATH . 'views/frontend/' . $slug . '/page.php')) {
            $this->load->view('frontend/' . $slug . '/page', $data);
        } else {
            $this->load->view('frontend/' . $slug . '/' . $page, $data);
        }
        $this->load->view('frontend/' . $slug . '/footer', $data);
    }

    protected function current_customer()
    {
        $customer = storefront_customer();
        if (!$customer || (int) $customer->store_id !== (int) $this->store->id) {
            return null;
        }
        return $customer;
    }

    protected function set_customer($row)
    {
        $_SESSION['storefront_customer'] = array(
            'id' => (int) $row->id,
            'store_id' => (int) $row->store_id,
            'name' => $row->name,
            'email' => $row->email,
            'phone' => isset($row->phone) ? $row->phone : '',
            'address' => isset($row->address) ? $row->address : '',
        );
    }

    protected function cart_items()
    {
        $items = array();
        foreach (storefront_cart($this->store->id) as $id => $qty) {
            $product = $this->catalog_query()->where('products.id', (int) $id)->get()->row();
            if (!$product) {
                continue;
            }
            apply_storefront_pricing($product, $this->store->id);
            $product->qty = (int) $qty;
            $product->image = !empty($product->image) ? base_url($product->image) : '';
            $product->line_total = $product->price * $product->qty;
            $items[] = $product;
        }
        return $items;
    }

    protected function cart_subtotal()
    {
        $total = 0;
        foreach ($this->cart_items() as $item) {
            $total += $item->line_total;
        }
        return round($total, 2);
    }

    protected function cart_total()
    {
        return cart_total_with_vat($this->cart_subtotal());
    }

    protected function cart_totals()
    {
        $subtotal = $this->cart_subtotal();
        return array(
            'cart_subtotal' => $subtotal,
            'vat_percent' => (float) platform_setting('vat', 0),
            'vat_amount' => cart_vat_amount($subtotal),
            'cart_total' => cart_total_with_vat($subtotal),
        );
    }

    protected function products($limit = 0)
    {
        $this->catalog_query()->where('products.status', 1);
        $this->exclude_child_products();
        $this->db->order_by('products.id', 'desc');
        if ($limit) {
            $this->db->limit((int) $limit);
        }
        $products = $this->db->get()->result();
        return apply_storefront_pricing($products, $this->store->id);
    }

    protected function related_products($product, $trail, $limit = 8)
    {
        $excludeId = $product ? (int) $product->id : 0;
        $categoryIds = array();
        if (!empty($trail['subcategory']) && !empty($trail['subcategory']->id)) {
            $categoryIds[] = (int) $trail['subcategory']->id;
        }
        if (!empty($trail['category']) && !empty($trail['category']->id)) {
            $categoryIds[] = (int) $trail['category']->id;
        }
        $related = array();
        if ($categoryIds) {
            foreach ($this->products_in_categories($categoryIds, $limit + 2, 0) as $item) {
                if ((int) $item->id === $excludeId) {
                    continue;
                }
                $related[(int) $item->id] = $item;
                if (count($related) >= $limit) {
                    break;
                }
            }
        }
        if (count($related) < $limit) {
            foreach ($this->products($limit + 4) as $item) {
                if ((int) $item->id === $excludeId || isset($related[(int) $item->id])) {
                    continue;
                }
                $related[(int) $item->id] = $item;
                if (count($related) >= $limit) {
                    break;
                }
            }
        }
        return array_values($related);
    }

    protected function products_in_categories($categoryIds, $limit = 12, $offset = 0)
    {
        $categoryIds = array_values(array_filter(array_map('intval', (array) $categoryIds)));
        if (empty($categoryIds)) {
            return array();
        }
        $this->catalog_query()
            ->join('product_categories pc_cat', 'pc_cat.product_id = products.id', 'inner')
            ->where_in('pc_cat.category_id', $categoryIds)
            ->where('products.status', 1);
        $this->exclude_child_products();
        $this->db
            ->group_by('products.id')
            ->order_by('products.id', 'desc')
            ->limit((int) $limit, (int) $offset);
        $products = $this->db->get()->result();
        return apply_storefront_pricing($products, $this->store->id);
    }

    protected function count_in_categories($categoryIds)
    {
        $categoryIds = array_values(array_filter(array_map('intval', (array) $categoryIds)));
        if (empty($categoryIds)) {
            return 0;
        }
        $row = $this->db
            ->select('COUNT(DISTINCT products.id) as total', false)
            ->from('products')
            ->join('product_categories pc_cat', 'pc_cat.product_id = products.id', 'inner')
            ->where('products.store_id', (int) $this->store->id)
            ->where('products.status', 1)
            ->where_in('pc_cat.category_id', $categoryIds);
        if ($this->db->field_exists('parent_sku', 'products')) {
            $this->db->group_start()
                ->where('products.parent_sku', '')
                ->or_where('products.parent_sku IS NULL', null, false)
                ->group_end();
        }
        $row = $this->db
            ->get()
            ->row();
        return $row ? (int) $row->total : 0;
    }

    protected function catalog_filters()
    {
        $min = $this->input->get('min');
        $max = $this->input->get('max');
        return array(
            'category' => trim((string) $this->input->get('category')),
            'q' => trim((string) $this->input->get('q')),
            'min' => ($min !== null && $min !== '') ? (float) $min : null,
            'max' => ($max !== null && $max !== '') ? (float) $max : null,
            'in_stock' => (int) $this->input->get('in_stock') === 1,
            'on_sale' => (int) $this->input->get('on_sale') === 1,
            'sort' => trim((string) $this->input->get('sort')),
        );
    }

    protected function filtered_products($filters)
    {
        $this->catalog_query()->where('products.status', 1);
        $this->exclude_child_products();

        if (!empty($filters['category'])) {
            $this->db
                ->join('product_categories pc_filter', 'pc_filter.product_id = products.id', 'inner')
                ->join('categories c_filter', 'c_filter.id = pc_filter.category_id', 'inner')
                ->where('c_filter.slug', $filters['category'])
                ->where('c_filter.status', 1);
        }
        if (!empty($filters['q'])) {
            $q = $this->db->escape_like_str($filters['q']);
            $this->db->group_start()
                ->like('products.name', $q)
                ->or_like('products.description', $q)
                ->or_like('products.sku', $q)
                ->group_end();
        }
        if ($filters['min'] !== null) {
            $this->db->where('products.price >=', (float) $filters['min']);
        }
        if ($filters['max'] !== null) {
            $this->db->where('products.price <=', (float) $filters['max']);
        }
        if (!empty($filters['in_stock'])) {
            $this->db->where('products.stock >', 0);
        }
        if (!empty($filters['on_sale'])) {
            $this->db->where('products.compare_price > products.price', null, false);
        }

        if ($filters['sort'] === 'price_asc') {
            $this->db->order_by('products.price', 'asc');
        } elseif ($filters['sort'] === 'price_desc') {
            $this->db->order_by('products.price', 'desc');
        } elseif ($filters['sort'] === 'name') {
            $this->db->order_by('products.name', 'asc');
        } else {
            $this->db->order_by('products.id', 'desc');
        }

        $this->db->group_by('products.id');
        $products = $this->db->get()->result();
        return apply_storefront_pricing($products, $this->store->id);
    }

    protected function price_bounds()
    {
        $this->db
            ->select('MIN(price) as min_price, MAX(price) as max_price', false)
            ->where('store_id', (int) $this->store->id)
            ->where('status', 1);
        if ($this->db->field_exists('parent_sku', 'products')) {
            $this->db->group_start()
                ->where('parent_sku', '')
                ->or_where('parent_sku IS NULL', null, false)
                ->group_end();
        }
        $row = $this->db->get('products')->row();
        return array(
            'min' => $row && $row->min_price !== null ? (float) $row->min_price : 0,
            'max' => $row && $row->max_price !== null ? (float) $row->max_price : 100,
        );
    }

    protected function catalog_query()
    {
        return $this->db
            ->select('products.*, suppliers.name as supplier_name, users.commission as owner_commission, users.commission_percent as owner_commission_percent, COALESCE(product_country.name, supplier_country.name) as country_name', false)
            ->from('products')
            ->join('suppliers', 'suppliers.id = products.supplier_id', 'left')
            ->join('countries as product_country', 'product_country.id = products.country_id', 'left')
            ->join('countries as supplier_country', 'supplier_country.id = suppliers.country_id', 'left')
            ->join('users', 'users.UserID = products.created_by', 'left')
            ->where('products.store_id', (int) $this->store->id)
            ->where('products.status', 1);
    }

    protected function exclude_child_products()
    {
        if (!$this->db->field_exists('parent_sku', 'products')) {
            return;
        }
        $this->db->group_start()
            ->where('products.parent_sku', '')
            ->or_where('products.parent_sku IS NULL', null, false)
            ->group_end();
    }

    protected function matches_store_product($product)
    {
        return $product && (int) $product->store_id === (int) $this->store->id;
    }

    protected function product_gallery_rows($productId)
    {
        if (!$this->db->table_exists('product_images')) {
            return array();
        }
        return $this->db
            ->where('product_id', (int) $productId)
            ->order_by('sort_order', 'asc')
            ->order_by('id', 'asc')
            ->get('product_images')
            ->result();
    }
}
