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
            if ($this->router->method === 'track') {
                return;
            }
            show_error('No active store theme is configured for this domain.', 404);
            return;
        }
        if (function_exists('ensure_user_commission_schema')) {
            ensure_user_commission_schema();
        }
        $this->store = hydrate_store_currency($this->store);
        if (function_exists('ensure_product_i18n_columns')) {
            ensure_product_i18n_columns();
        }
        if (function_exists('ensure_product_trending_columns')) {
            ensure_product_trending_columns();
        }
        if (function_exists('ensure_nav_i18n')) {
            ensure_nav_i18n();
        }
    }

    public function index()
    {
        $this->load->model('Ec_category_model');
        $countryId = (int) (isset($this->store->country_id) ? $this->store->country_id : 0);
        $homeCategories = $this->Ec_category_model->storefront_parents_with_products($this->store->id, $countryId);
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
            'products' => $this->trending_products(null, 8),
            'home_categories' => $homeCategories,
            'hero_slides' => $heroSlides,
        ));
    }

    public function set_language($code = '')
    {
        $code = strtolower(trim((string) $code));
        $native = function_exists('storefront_native_locale') ? storefront_native_locale() : 'en';
        $allowed = array('en', $native);
        if (!in_array($code, $allowed, true)) {
            $code = $native;
        }
        $expire = time() + 31536000;
        $secure = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
        if (PHP_VERSION_ID >= 70300) {
            setcookie('ec_lang', $code, array(
                'expires' => $expire,
                'path' => '/',
                'secure' => $secure,
                'httponly' => true,
                'samesite' => 'Lax',
            ));
        } else {
            setcookie('ec_lang', $code, $expire, '/', '', $secure, true);
        }
        $_COOKIE['ec_lang'] = $code;
        $back = isset($_SERVER['HTTP_REFERER']) ? trim((string) $_SERVER['HTTP_REFERER']) : '';
        $home = storefront_url('');
        if ($back !== '') {
            $backHost = parse_url($back, PHP_URL_HOST);
            $homeHost = parse_url($home, PHP_URL_HOST);
            if ($backHost && $homeHost && strtolower($backHost) === strtolower($homeHost)) {
                $back = preg_replace('/([?&])lang=[^&]*/', '$1', $back);
                $back = rtrim($back, '?&');
                redirect($back);
                return;
            }
        }
        redirect($home);
    }

    public function category($slug = '')
    {
        $this->load->model('Ec_category_model');
        $slug = rawurldecode(trim((string) $slug));
        $countryId = (int) (isset($this->store->country_id) ? $this->store->country_id : 0);
        $category = $this->Ec_category_model->get_by_slug($slug, $countryId);
        if (!$category) {
            show_404();
        }
        $canonical = isset($category->slug) ? trim((string) $category->slug) : '';
        if ($canonical !== '' && $canonical !== $slug) {
            redirect(category_url($category), 'location', 301);
            return;
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
                $treeIds = $this->Ec_category_model->category_product_ids($category->id, true);
                $counts = $this->Ec_category_model->store_product_counts($this->store->id, $treeIds);
                $hasProducts = false;
                foreach ($counts as $n) {
                    if ((int) $n > 0) {
                        $hasProducts = true;
                        break;
                    }
                }
                if (!$hasProducts) {
                    show_404();
                }
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
            'load_more_url' => storefront_url('category/' . storefront_path_slug($category->slug, 'category') . '/more'),
        ));
    }

    public function category_more($slug = '')
    {
        $this->load->model('Ec_category_model');
        $slug = rawurldecode(trim((string) $slug));
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
        if (function_exists('storefront_localize_products')) {
            $products = storefront_localize_products($products);
        }

        $html = '';
        $slugTheme = $this->theme->slug;
        $assets = storefront_asset_url('assets/frontend/' . $slugTheme . '/');
        foreach ($products as $product) {
            ob_start();
            $this->load->view('frontend/' . $slugTheme . '/product_card', array(
                'product' => $product,
                'assets' => $assets,
                'is_preview' => false,
                'store' => $this->store,
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
            'title' => ($activeCategory ? category_store_name($activeCategory) . ' — ' : store_ui('page.shop') . ' — ') . $this->store->name,
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
            redirect(storefront_url(''), 'location', 301);
            return;
        }

        $product = null;
        foreach (ec_slug_candidates($key) as $try) {
            $product = $this->catalog_query()
                ->where('products.slug', $try)
                ->get()
                ->row();
            if ($product) {
                break;
            }
        }
        if (!$product && ctype_digit($key)) {
            $product = $this->catalog_query()
                ->where('products.id', (int) $key)
                ->get()
                ->row();
            if ($product && !$this->matches_store_product($product)) {
                show_404();
            }
        }
        $slug = ($product && isset($product->slug)) ? trim((string) $product->slug) : '';
        if ($product && $slug !== '' && $slug !== $key) {
            redirect(product_url($product), 'location', 301);
            return;
        }
        if (!$product) {
            redirect(storefront_url(''), 'location', 301);
            return;
        }
        if (!$this->matches_store_product($product)) {
            show_404();
        }
        $openedProduct = $product;
        $family = function_exists('product_family')
            ? product_family($product, true)
            : array('parent' => $product, 'selected' => $product, 'children' => array());
        $display = $family['parent'] ? $family['parent'] : $product;
        $cartProduct = $family['selected'] ? $family['selected'] : $product;
        $openedChild = isset($openedProduct->parent_sku) ? trim((string) $openedProduct->parent_sku) : '';
        if ($openedChild !== '' && $display && (int) $display->id !== (int) $openedProduct->id) {
            $target = product_url($display);
            $qs = trim((string) $this->input->server('QUERY_STRING'));
            if ($qs !== '') {
                $target .= (strpos($target, '?') === false ? '?' : '&') . $qs;
            }
            redirect($target, 'location', 301);
            return;
        }
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
            'products' => $this->trending_products($display, 4),
            'canonical_url' => product_url($display),
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
            $detailData['products'] = $this->trending_products($display, 4);
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
                'title' => store_ui('page.contact') . ' — ' . $this->store->name,
                'page_slug' => 'contact',
                'page_title' => store_ui('contact.title'),
                'meta_description' => store_ui('contact.meta'),
                'flash_success' => ec_take_flash('success'),
                'flash_error' => ec_take_flash('error'),
                'form' => $this->session->flashdata('contact_form') ?: array(),
            ));
            return;
        }
        $this->load->model('Store_page_model');
        $cmsPage = $this->Store_page_model->get_published_by_slug($this->store->id, $slug);
        if (!$cmsPage) {
            show_404();
            return;
        }
        $this->render('page', array(
            'title' => $cmsPage->title . ' — ' . $this->store->name,
            'page_slug' => $cmsPage->slug,
            'page_title' => $cmsPage->title,
            'page_detail' => $cmsPage->detail,
            'meta_description' => $cmsPage->meta_description !== '' ? $cmsPage->meta_description : $cmsPage->title,
        ));
    }

    protected function submit_contact()
    {
        $honeypot = trim((string) $this->input->post('company'));
        $name = trim((string) $this->input->post('name'));
        $email = trim((string) $this->input->post('email'));
        $order = trim((string) $this->input->post('order'));
        $topic = trim((string) $this->input->post('topic'));
        $message = trim((string) $this->input->post('message'));
        $form = array(
            'name' => $name,
            'email' => $email,
            'order' => $order,
            'topic' => $topic,
            'message' => $message,
        );
        $this->session->set_flashdata('contact_form', $form);
        $goContact = function_exists('contact_url') ? contact_url() : storefront_url('contact');
        if ($honeypot !== '') {
            $this->session->set_flashdata('success', function_exists('store_ui') ? store_ui('contact.sent') : 'Thank you. We have received your message.');
            redirect($goContact);
            return;
        }
        if ($name === '' || $message === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->session->set_flashdata('error', function_exists('store_ui') ? store_ui('contact.invalid') : 'Please fill in your name, a valid email, and a message.');
            redirect($goContact);
            return;
        }
        $recipients = $this->contact_notify_emails();
        if (empty($recipients)) {
            $this->session->set_flashdata('error', function_exists('store_ui') ? store_ui('contact.no_email') : 'This store has not configured a contact email yet.');
            redirect($goContact);
            return;
        }
        $safe = function ($value) {
            return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
        };
        $storeName = isset($this->store->name) ? $this->store->name : 'Store';
        $storeDomain = isset($this->store->domain) ? $this->store->domain : '';
        $html = '<div style="font-family:Arial,sans-serif;font-size:14px;color:#222;line-height:1.5">'
            . '<h2 style="margin:0 0 12px">New contact form message</h2>'
            . '<p><strong>Store:</strong> ' . $safe($storeName)
            . ($storeDomain !== '' ? ' (' . $safe($storeDomain) . ')' : '')
            . '</p>'
            . '<p><strong>Name:</strong> ' . $safe($name) . '</p>'
            . '<p><strong>Email:</strong> <a href="mailto:' . $safe($email) . '">' . $safe($email) . '</a></p>'
            . '<p><strong>Topic:</strong> ' . $safe($topic !== '' ? $topic : '—') . '</p>'
            . '<p><strong>Order:</strong> ' . $safe($order !== '' ? $order : '—') . '</p>'
            . '<p><strong>Message:</strong></p>'
            . '<p>' . nl2br($safe($message)) . '</p>'
            . '</div>';
        $subject = '[' . $storeName . '] ' . ($topic !== '' ? $topic : 'Contact form');
        $this->load->library('ec_mail');
        $sent = $this->ec_mail->send_many($recipients, $subject, $html, array('reply_to' => $email));
        if ($sent < 1) {
            $this->session->set_flashdata('error', function_exists('store_ui') ? store_ui('contact.send_failed') : 'We could not send your message just now. Please try again.');
            redirect($goContact);
            return;
        }
        $this->session->set_flashdata('success', function_exists('store_ui') ? store_ui('contact.sent') : 'Thank you. We have received your message.');
        redirect($goContact);
    }

    protected function contact_notify_emails()
    {
        $emails = array();
        $info = function_exists('storefront_contact_info')
            ? storefront_contact_info($this->store, $this->settings)
            : array();
        if (!empty($info['email'])) {
            $emails[] = $info['email'];
        } elseif (!empty($this->store->email)) {
            $emails[] = $this->store->email;
        }
        $adminNotify = function_exists('platform_setting') ? trim((string) platform_setting('admin_notify_email', '')) : '';
        if ($adminNotify !== '') {
            $emails[] = $adminNotify;
        }
        if ($this->db->table_exists('users')) {
            $admin = $this->db->where('roleID', 1)->where('status', 1)->order_by('UserID', 'asc')->get('users')->row();
            if ($admin && !empty($admin->email)) {
                $emails[] = $admin->email;
            }
        }
        $unique = array();
        foreach ($emails as $email) {
            $email = strtolower(trim((string) $email));
            if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || isset($unique[$email])) {
                continue;
            }
            $unique[$email] = true;
        }
        return array_keys($unique);
    }

    public function cart()
    {
        $this->load->library('channel_events');
        $this->render('cart', array_merge(array(
            'title' => store_ui('page.cart') . ' — ' . $this->store->name,
            'cart_items' => $this->cart_items(),
            'tracking' => $this->channel_events->consume_browser_event($this->store),
            'flash_success' => ec_take_flash('success'),
            'flash_error' => ec_take_flash('error'),
        ), $this->cart_totals()));
    }

    public function add_to_cart($id = 0)
    {
        $posted = (int) $this->input->get_post('product_id');
        if ($posted < 1) {
            $posted = (int) $this->input->get_post('id');
        }
        if ($posted > 0) {
            $id = $posted;
        }
        $product = $this->catalog_query()->where('products.id', (int) $id)->get()->row();
        if (!$product || !$this->matches_store_product($product)) {
            show_404();
        }
        $parentSku = isset($product->parent_sku) ? trim((string) $product->parent_sku) : '';
        if ($parentSku === '' && function_exists('product_family')) {
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
            $this->session->set_flashdata('error', store_ui('msg.out_of_stock'));
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
            $this->session->set_flashdata('error', store_ui('msg.no_more_stock'));
            redirect(storefront_url('cart'));
            return;
        }
        $cart[$id] = $existing + $add;
        $_SESSION['storefront_cart'][$this->store->id] = $cart;
        apply_storefront_pricing($product, $this->store->id);
        $this->load->library('channel_events');
        $this->channel_events->add_to_cart($this->store, $product, $add);
        $this->analytics_event('add_to_cart', array(
            'product_id' => (int) $product->id,
            'page_url' => storefront_url('cart'),
            'page_title' => isset($product->name) ? $product->name : 'Cart',
        ));
        $this->session->set_flashdata('success', store_ui('msg.added_cart'));
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
        $this->session->set_flashdata('success', $added ? store_ui('msg.added_wish') : store_ui('msg.removed_wish'));
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
            $this->session->set_flashdata('error', store_ui('msg.login_review'));
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
            $this->session->set_flashdata('error', store_ui('msg.review_required'));
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
        $this->session->set_flashdata('success', store_ui('msg.review_thanks'));
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
        $this->session->set_flashdata('success', store_ui('msg.cart_updated'));
        redirect(storefront_url('cart'));
    }

    public function remove_from_cart($id = 0)
    {
        $cart = storefront_cart($this->store->id);
        unset($cart[(int) $id]);
        $_SESSION['storefront_cart'][$this->store->id] = $cart;
        redirect(storefront_url('cart'));
    }

    public function checkout_guest()
    {
        if (!$this->cart_items()) {
            $this->session->set_flashdata('error', store_ui('msg.cart_empty'));
            redirect(storefront_url('cart'));
            return;
        }
        $_SESSION['checkout_guest'][(int) $this->store->id] = 1;
        redirect(storefront_url('checkout'));
    }

    public function checkout()
    {
        $customer = $this->current_customer();
        if (!$this->cart_items()) {
            $this->session->set_flashdata('error', store_ui('msg.cart_empty'));
            redirect(storefront_url('cart'));
            return;
        }
        $guest = !empty($_SESSION['checkout_guest'][(int) $this->store->id]);
        if (!$customer && !$guest) {
            $this->remember_auth_return('checkout');
            $this->session->set_flashdata('error', store_ui('msg.login_checkout'));
            redirect(storefront_url('account/login'));
            return;
        }

        ensure_customer_address_columns();
        $defaultCountry = storefront_default_country($this->store);
        if ($this->input->post()) {
            $posted = customer_address_from_post($defaultCountry);
            if (customer_address_missing($posted, true)) {
                $this->session->set_flashdata('error', store_ui('msg.checkout_required'));
                redirect(storefront_url('checkout'));
                return;
            }
            if ($customer) {
                $taken = $this->db
                    ->where('store_id', $this->store->id)
                    ->where('email', $posted['email'])
                    ->where('id !=', (int) $customer->id)
                    ->get('store_customers')
                    ->row();
                if ($taken) {
                    $this->session->set_flashdata('error', store_ui('msg.email_taken'));
                    redirect(storefront_url('checkout'));
                    return;
                }
                $this->db->where('id', (int) $customer->id)->where('store_id', $this->store->id)->update('store_customers', customer_address_save_payload($posted));
                $customer = $this->db->where('id', (int) $customer->id)->get('store_customers')->row();
                $this->set_customer($customer);
            }
            $shipping = customer_shipping_for_order($posted);

            $totals = $this->cart_totals();
            $_SESSION['checkout_draft'][$this->store->id] = array(
                'shipping' => $shipping,
                'address_payload' => customer_address_save_payload($posted),
                'guest' => $customer ? 0 : 1,
                'customer_id' => $customer ? (int) $customer->id : 0,
                'amount' => $totals['cart_total'],
                'subtotal' => $totals['cart_subtotal'],
                'vat_amount' => $totals['vat_amount'],
                'shipping_amount' => $totals['shipping_amount'],
                'shipping_qty' => $totals['shipping_qty'],
                'created_at' => time(),
            );
            redirect(storefront_url('payment'));
            return;
        }

        $this->load->library('channel_events');
        $checkoutItems = $this->cart_items();
        $checkoutTotals = $this->cart_totals();
        $this->render('checkout', array_merge(array(
            'title' => store_ui('page.checkout') . ' — ' . $this->store->name,
            'customer' => $customer,
            'addr' => customer_address_from_row($customer, $defaultCountry),
            'countries' => storefront_country_options(),
            'cart_items' => $checkoutItems,
            'flash_error' => ec_take_flash('error'),
            'tracking' => $this->channel_events->initiate_checkout($this->store, $checkoutItems, $checkoutTotals['cart_total']),
        ), $checkoutTotals));
        $this->analytics_event('begin_checkout', array(
            'product_id' => $this->analytics_cart_product_id(),
            'page_url' => storefront_url('checkout'),
            'page_title' => 'Checkout',
        ));
    }

    public function payment()
    {
        $customer = $this->current_customer();
        $draft = $this->checkout_draft();
        if (!$this->checkout_party_ready($customer, $draft)) {
            $this->session->set_flashdata('error', store_ui('msg.complete_shipping'));
            redirect(storefront_url('checkout'));
            return;
        }
        if (!$this->cart_items()) {
            $this->session->set_flashdata('error', store_ui('msg.cart_empty'));
            redirect(storefront_url('cart'));
            return;
        }

        $this->load->library('payment_crypto');
        $this->load->library('paypal');
        $this->apply_paypal_for_draft($draft);

        $this->render('payment', array_merge(array(
            'title' => store_ui('page.payment') . ' — ' . $this->store->name,
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
        $this->analytics_event('payment_page_view', array(
            'product_id' => $this->analytics_cart_product_id(),
            'page_url' => storefront_url('payment'),
            'page_title' => 'Payment',
        ));
    }

    public function payment_card()
    {
        $this->load->library('paypal');
        $customer = $this->current_customer();
        $draft = $this->checkout_draft();
        $this->apply_paypal_for_draft($draft);
        if (!$this->checkout_party_ready($customer, $draft) || !$this->cart_items()) {
            $this->paypal->log_event('card_attempt', array(
                'ok' => 0,
                'error' => 'expired_session',
                'has_customer' => $customer ? 1 : 0,
                'has_draft' => $draft ? 1 : 0,
                'mode' => $this->paypal->mode(),
            ));
            $this->session->set_flashdata('error', store_ui('msg.pay_expired'));
            redirect(storefront_url('checkout'));
            return;
        }

        $this->load->library('payment_crypto');

        $encrypted = trim((string) $this->input->post('card_payload'));
        $this->analytics_event('payment_attempt', array(
            'product_id' => $this->analytics_cart_product_id(),
            'page_url' => storefront_url('payment'),
            'page_title' => 'Payment',
        ));
        $this->paypal->log_event('card_attempt', array(
            'store_id' => (int) $this->store->id,
            'mode' => $this->paypal->mode(),
            'payload_len' => strlen($encrypted),
            'has_customer' => $customer ? 1 : 0,
            'has_draft' => $draft ? 1 : 0,
            'test_buyer' => $this->is_draft_test_buyer($draft) ? 1 : 0,
        ));
        $card = $this->payment_crypto->decrypt_payload($encrypted);
        if (!$card || empty($card['number']) || empty($card['exp_month']) || empty($card['exp_year']) || empty($card['cvv'])) {
            $this->paypal->log_event('card_decrypt', array(
                'ok' => 0,
                'error' => $this->payment_crypto->last_error() ?: 'decrypt_failed',
                'payload_len' => strlen($encrypted),
            ));
            $this->session->set_flashdata('error', store_ui('msg.pay_card_read'));
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

        $this->complete_paypal_payment($customer, $draft, 'card', $result, $currency, $amount);
    }

    public function payment_paypal()
    {
        $customer = $this->current_customer();
        $draft = $this->checkout_draft();
        if (!$this->checkout_party_ready($customer, $draft) || !$this->cart_items()) {
            $this->session->set_flashdata('error', store_ui('msg.pay_expired'));
            redirect(storefront_url('checkout'));
            return;
        }

        $this->load->library('paypal');
        $this->apply_paypal_for_draft($draft);
        $this->analytics_event('payment_attempt', array(
            'product_id' => $this->analytics_cart_product_id(),
            'page_url' => storefront_url('payment'),
            'page_title' => 'Payment',
        ));
        $amount = (float) $draft['amount'];
        $currency = $this->paypal->currency($this->store);
        $order = $this->paypal->create_order($amount, $currency, array(
            'order_no' => 'TMP' . strtoupper(substr(uniqid(), -8)),
            'description' => $this->store->name . ' order',
            'return_url' => storefront_url('payment/return'),
            'cancel_url' => storefront_url('payment/cancel'),
        ));

        if (!$order || empty($order['id'])) {
            $this->session->set_flashdata('error', $this->paypal->last_error() ?: store_ui('msg.pay_paypal_start'));
            redirect(storefront_url('payment'));
            return;
        }

        $_SESSION['checkout_draft'][$this->store->id]['paypal_order_id'] = $order['id'];
        $approve = $this->paypal->approve_link($order);
        if ($approve === '') {
            $this->session->set_flashdata('error', store_ui('msg.pay_paypal_link'));
            redirect(storefront_url('payment'));
            return;
        }
        redirect($approve);
    }

    public function payment_return()
    {
        $customer = $this->current_customer();
        $draft = $this->checkout_draft();
        if (!$this->checkout_party_ready($customer, $draft) || !$this->cart_items()) {
            $this->session->set_flashdata('error', store_ui('msg.pay_expired_short'));
            redirect(storefront_url('checkout'));
            return;
        }

        $this->load->library('paypal');
        $this->apply_paypal_for_draft($draft);
        $paypalOrderId = $this->input->get('token') ?: (isset($draft['paypal_order_id']) ? $draft['paypal_order_id'] : '');
        if ($paypalOrderId === '') {
            $this->session->set_flashdata('error', store_ui('msg.pay_paypal_ref'));
            redirect(storefront_url('payment'));
            return;
        }

        $captured = $this->paypal->capture_order($paypalOrderId);
        $this->complete_paypal_payment(
            $customer,
            $draft,
            'paypal',
            $captured,
            $this->paypal->currency($this->store),
            (float) $draft['amount']
        );
    }

    public function payment_cancel()
    {
        $this->session->set_flashdata('error', store_ui('msg.pay_paypal_cancel'));
        redirect(storefront_url('payment'));
    }

    /**
     * Success only when nested capture status is COMPLETED.
     * Order-level COMPLETED/HTTP 201 is not enough (DECLINED captures still return those).
     */
    protected function complete_paypal_payment($customer, $draft, $method, $result, $currency, $amount)
    {
        if (!is_array($result) || empty($result['id'])) {
            $this->session->set_flashdata('error', $this->paypal->last_error() ?: ($method === 'card' ? store_ui('msg.pay_card_failed') : store_ui('msg.pay_paypal_incomplete')));
            redirect(storefront_url('payment'));
            return;
        }

        $summary = $this->paypal->capture_summary($result);
        $orderStatus = $summary['order_status'];
        if ($summary['capture_status'] === null && in_array($orderStatus, array('CREATED', 'APPROVED'), true)) {
            $captured = $this->paypal->capture_order($summary['order_id']);
            if (is_array($captured) && !empty($captured['id'])) {
                $result = $captured;
                $summary = $this->paypal->capture_summary($result);
            }
        }

        $this->paypal->log_event('capture_check', array(
            'paypal_order_id' => $summary['order_id'],
            'capture_id' => $summary['capture_id'],
            'order_status' => $summary['order_status'],
            'capture_status' => $summary['capture_status'],
            'processor_response_code' => $summary['processor_response_code'],
            'amount' => $summary['amount'],
            'currency' => $summary['currency'],
        ));

        $captureStatus = $summary['capture_status'];
        if ($captureStatus === 'COMPLETED') {
            $this->finalize_paid_order($customer, $draft, $method, $summary['order_id'], $currency, $amount);
            return;
        }

        if ($captureStatus === 'PENDING') {
            $this->store_pending_paypal_order($customer, $draft, $method, $summary['order_id'], $currency, $amount);
            $this->session->set_flashdata('error', store_ui('msg.pay_pending'));
            redirect(storefront_url('payment'));
            return;
        }

        $message = store_ui('msg.pay_card_failed');
        if ($summary['processor_response_code'] === '5120') {
            $message = store_ui('msg.pay_insufficient_funds');
        } elseif ($captureStatus === 'DECLINED' || $captureStatus === 'FAILED') {
            $message = store_ui('msg.pay_declined');
        } elseif ($method !== 'card') {
            $message = $this->paypal->last_error() ?: store_ui('msg.pay_paypal_incomplete');
        } elseif ($this->paypal->last_error()) {
            $message = $this->paypal->last_error();
        }
        $this->session->set_flashdata('error', $message);
        redirect(storefront_url('payment'));
    }

    protected function store_pending_paypal_order($customer, $draft, $method, $paypalOrderId, $currency, $amount)
    {
        $this->load->model('Ec_order_model');
        $existing = $this->Ec_order_model->find_by_paypal_order_id($paypalOrderId, (int) $this->store->id);
        if ($existing) {
            if (isset($existing->payment_status) && $existing->payment_status !== 'paid') {
                $this->Ec_order_model->mark_payment_status((int) $existing->id, 'pending');
            }
            return $existing;
        }
        $order = $this->Ec_order_model->create_from_cart(
            $this->store,
            $customer,
            $this->cart_items(),
            $draft['shipping'],
            array(
                'payment_method' => $method,
                'payment_status' => 'pending',
                'paypal_order_id' => $paypalOrderId,
                'payment_currency' => $currency,
                'paid_amount' => 0,
            )
        );
        $_SESSION['storefront_cart'][$this->store->id] = array();
        unset($_SESSION['checkout_draft'][$this->store->id]);
        return $order;
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

    protected function draft_checkout_email($draft)
    {
        if (!is_array($draft) || empty($draft['shipping']['email'])) {
            return '';
        }
        return strtolower(trim((string) $draft['shipping']['email']));
    }

    protected function is_draft_test_buyer($draft)
    {
        if (!function_exists('is_checkout_test_buyer')) {
            return false;
        }
        if (is_checkout_test_buyer($this->draft_checkout_email($draft))) {
            return true;
        }
        // Also match logged-in account email (shipping field may differ).
        $customer = $this->current_customer();
        if ($customer && !empty($customer->email) && is_checkout_test_buyer($customer->email)) {
            return true;
        }
        return false;
    }

    /**
     * QA buyer only: force PayPal sandbox for this request. Live mode stays for everyone else.
     */
    protected function apply_paypal_for_draft($draft)
    {
        if (!$this->is_draft_test_buyer($draft) || !isset($this->paypal) || !is_object($this->paypal)) {
            return;
        }
        if (method_exists($this->paypal, 'force_sandbox')) {
            $this->paypal->force_sandbox();
            $this->paypal->log_event('force_sandbox', array(
                'email' => $this->draft_checkout_email($draft),
                'mode' => $this->paypal->mode(),
                'store_id' => (int) $this->store->id,
            ));
        }
    }

    protected function finalize_paid_order($customer, $draft, $method, $paypalOrderId, $currency, $amount)
    {
        $this->load->model('Ec_order_model');
        $accountMail = null;
        $customer = $this->attach_guest_account($customer, $draft, $accountMail);
        $paypalOrderId = trim((string) $paypalOrderId);
        $existing = $paypalOrderId !== ''
            ? $this->Ec_order_model->find_by_paypal_order_id($paypalOrderId, (int) $this->store->id)
            : null;

        if ($existing && isset($existing->payment_status) && $existing->payment_status === 'paid') {
            $this->render('thanks', array(
                'title' => store_ui('page.thanks') . ' — ' . $this->store->name,
                'order' => $existing,
                'tracking' => array(),
            ));
            return;
        }

        if ($existing) {
            if ($customer && (int) $existing->customer_id !== (int) $customer->id) {
                $this->db->where('id', (int) $existing->id)->update('store_orders', array('customer_id' => (int) $customer->id));
            }
            $this->Ec_order_model->mark_payment_status((int) $existing->id, 'paid', array(
                'paid_amount' => $amount,
                'payment_currency' => $currency,
                'payment_method' => $method,
                'paypal_order_id' => $paypalOrderId,
            ));
            $this->Ec_order_model->add_log((int) $existing->id, $existing->status, 'Payment captured via ' . $method, 'customer', $customer ? $customer->id : 0);
            $order = $this->Ec_order_model->get((int) $existing->id);
            $_SESSION['storefront_cart'][$this->store->id] = array();
            unset($_SESSION['checkout_draft'][$this->store->id]);
        } else {
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
        }

        $this->Ec_order_model->notify_status($order);
        if ($accountMail && $order) {
            $this->send_guest_account_email($accountMail['email'], $accountMail['name'], $accountMail['password'], $order);
        }

        $this->load->library('channel_events');
        $tracking = $this->channel_events->purchase($this->store, $order, $this->Ec_order_model->items($order->id));
        foreach ($this->Ec_order_model->items($order->id) as $item) {
            $this->analytics_event('purchase', array(
                'product_id' => (int) $item->product_id,
                'page_url' => storefront_url('thanks'),
                'page_title' => $item->product_name,
                'revenue' => (float) $item->line_total,
                'event_key' => 'purchase-' . $order->id . '-' . $item->product_id,
            ));
        }

        $this->render('thanks', array(
            'title' => store_ui('page.thanks') . ' — ' . $this->store->name,
            'order' => $order,
            'tracking' => $tracking,
        ));
    }

    public function customer_login()
    {
        if ($this->current_customer()) {
            $this->redirect_after_auth();
            return;
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
                $this->redirect_after_auth();
                return;
            }
            $this->session->set_flashdata('error', store_ui('msg.login_invalid'));
        }
        $this->render('login', array(
            'title' => store_ui('page.login') . ' — ' . $this->store->name,
            'guest_checkout' => $this->auth_return_path() === 'checkout',
            'flash_error' => ec_take_flash('error'),
            'flash_success' => ec_take_flash('success'),
        ));
    }

    public function customer_signup()
    {
        if ($this->current_customer()) {
            $this->redirect_after_auth();
            return;
        }
        if ($this->input->post()) {
            $name = trim($this->input->post('name'));
            $email = strtolower(trim($this->input->post('email')));
            $password = $this->input->post('password');
            if ($name === '' || $email === '' || $password === '') {
                $this->session->set_flashdata('error', store_ui('msg.signup_required'));
            } else {
                $exists = $this->db->where('store_id', $this->store->id)->where('email', $email)->get('store_customers')->row();
                if ($exists) {
                    $this->session->set_flashdata('error', store_ui('msg.email_taken'));
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
                    $this->redirect_after_auth();
                    return;
                }
            }
        }
        $this->render('signup', array(
            'title' => store_ui('page.signup') . ' — ' . $this->store->name,
            'flash_error' => ec_take_flash('error'),
        ));
    }

    public function customer_profile($section = 'orders')
    {
        $customer = $this->current_customer();
        if (!$customer) {
            redirect(storefront_url('account/login'));
            return;
        }
        $section = strtolower(trim((string) $section));
        if (!in_array($section, array('orders', 'profile'), true)) {
            $section = 'orders';
        }
        ensure_customer_address_columns();
        $defaultCountry = storefront_default_country($this->store);
        if ($this->input->post()) {
            if ($section !== 'profile') {
                redirect(storefront_url('account/profile'));
                return;
            }
            $posted = customer_address_from_post($defaultCountry);
            if (customer_address_missing($posted, false)) {
                $this->session->set_flashdata('error', store_ui('msg.checkout_required'));
                redirect(storefront_url('account/profile'));
                return;
            }
            $taken = $this->db
                ->where('store_id', $this->store->id)
                ->where('email', $posted['email'])
                ->where('id !=', (int) $customer->id)
                ->get('store_customers')
                ->row();
            if ($taken) {
                $this->session->set_flashdata('error', store_ui('msg.email_taken'));
                redirect(storefront_url('account/profile'));
                return;
            }
            $payload = customer_address_save_payload($posted);
            if ($this->input->post('password') !== '') {
                $payload['password'] = md5($this->input->post('password'));
            }
            $this->db->where('id', $customer->id)->where('store_id', $this->store->id)->update('store_customers', $payload);
            $row = $this->db->where('id', $customer->id)->get('store_customers')->row();
            $this->set_customer($row);
            $this->session->set_flashdata('success', store_ui('msg.profile_updated'));
            redirect(storefront_url('account/profile'));
            return;
        }
        $this->load->model('Ec_order_model');
        $allItems = $section === 'orders'
            ? $this->Ec_order_model->for_customer_items($this->store->id, $customer->id)
            : array();
        $statusFilter = strtolower(trim((string) $this->input->get('status')));
        $statusTabs = array('pending', 'dispatching', 'shipped', 'delivered', 'completed');
        if (!in_array($statusFilter, $statusTabs, true)) {
            $statusFilter = '';
        }
        $statusCounts = array('all' => count($allItems));
        foreach ($statusTabs as $tab) {
            $statusCounts[$tab] = 0;
        }
        foreach ($allItems as $row) {
            $bucket = Ec_order_model::customer_status_bucket($row);
            if (isset($statusCounts[$bucket])) {
                $statusCounts[$bucket]++;
            }
        }
        $orderItems = $allItems;
        if ($statusFilter !== '') {
            $orderItems = array();
            foreach ($allItems as $row) {
                if (Ec_order_model::customer_status_bucket($row) === $statusFilter) {
                    $orderItems[] = $row;
                }
            }
        }
        $this->render('profile', array(
            'title' => store_ui('page.account') . ' — ' . $this->store->name,
            'customer' => $customer,
            'addr' => customer_address_from_row($customer, $defaultCountry),
            'countries' => storefront_country_options(),
            'account_section' => $section,
            'order_items' => $orderItems,
            'order_status_filter' => $statusFilter,
            'order_status_counts' => $statusCounts,
            'order_status_tabs' => $statusTabs,
            'has_orders' => !empty($allItems),
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
            $this->session->set_flashdata('error', store_ui('msg.order_missing'));
            redirect(storefront_url('account'));
            return;
        }
        $this->render('order', array(
            'title' => store_ui('page.order', array('{order}' => $order->order_no)) . ' — ' . $this->store->name,
            'customer' => $customer,
            'account_section' => 'orders',
            'order' => $order,
            'items' => $this->Ec_order_model->items($order->id),
            'logs' => $this->Ec_order_model->logs($order->id),
            'item_logs' => $this->Ec_order_model->item_logs_for_order($order->id),
            'statuses' => Ec_order_model::statuses(),
        ));
    }

    public function track()
    {
        $payload = json_decode($this->input->raw_input_stream, true);
        if (!is_array($payload) || !$payload) {
            $payload = $this->input->post();
        }
        if (!is_array($payload) || !$payload) {
            $this->output->set_content_type('application/json')->set_output(json_encode(array('ok' => true)));
            return;
        }
        $this->load->helper('store_analytics');
        $this->load->model('Store_analytics_model');
        $type = isset($payload['event_type']) ? $payload['event_type'] : 'page_view';
        $store = $this->store;
        if (!$store) {
            $store = (object) array(
                'id' => isset($payload['store_id']) ? (int) $payload['store_id'] : 0,
            );
        }
        $this->Store_analytics_model->ingest($store, $type, $payload);
        $this->output->set_content_type('application/json')->set_output(json_encode(array('ok' => true)));
    }

    protected function analytics_event($type, $extra = array())
    {
        $this->load->helper('store_analytics');
        $this->load->model('Store_analytics_model');
        if (!empty($this->store)) {
            $this->Store_analytics_model->ingest($this->store, $type, $extra);
        }
    }

    protected function analytics_cart_product_id()
    {
        $items = $this->cart_items();
        if (!$items) {
            return 0;
        }
        $first = reset($items);
        return $first && isset($first->id) ? (int) $first->id : 0;
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

        if (!isset($data['store_pages_nav']) || !isset($data['store_pages_footer'])) {
            $this->load->model('Store_page_model');
            $data['store_pages_nav'] = $this->Store_page_model->published_for($this->store->id, 'nav');
            $data['store_pages_footer'] = $this->Store_page_model->published_for($this->store->id, 'footer');
        }
        if (!isset($data['header_menu'])) {
            $data['header_menu'] = function_exists('storefront_header_menu_items')
                ? storefront_header_menu_items($this->store->id, isset($data['current_page']) ? $data['current_page'] : '')
                : array();
            if (empty($data['header_menu']) && !empty($data['store_pages_nav'])) {
                foreach ($data['store_pages_nav'] as $cmsPage) {
                    $data['header_menu'][] = (object) array(
                        'label' => $cmsPage->title,
                        'url' => page_url($cmsPage),
                        'slug' => $cmsPage->slug,
                        'active' => isset($data['current_page']) && $data['current_page'] === $cmsPage->slug,
                    );
                }
            }
        }
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
        $this->load->library('channel_events');
        if (!isset($data['tracking'])) {
            $data['tracking'] = $this->channel_events->tracking_payload($this->store);
        }
        $data['tracking'] = $this->channel_events->attach_page_view($this->store, $data['tracking']);

        if (!isset($data['pdp_design'])) {
            $data['pdp_design'] = product_detail_design($this->settings);
        }

        if (function_exists('storefront_localize_products')) {
            foreach (array('products', 'product', 'cart_product', 'child_products', 'cart_items', 'related_products') as $key) {
                if (isset($data[$key])) {
                    $data[$key] = storefront_localize_products($data[$key]);
                }
            }
            if ($page === 'detail' && !empty($data['product']) && is_object($data['product'])) {
                $data['title'] = $data['product']->name . ' — ' . $this->store->name;
                if (!empty($data['product']->seo_title)) {
                    $data['title'] = $data['product']->seo_title . ' — ' . $this->store->name;
                }
                if (!empty($data['product']->seo_description)) {
                    $data['meta_description'] = $data['product']->seo_description;
                }
                if (!empty($data['product']->seo_keywords)) {
                    $data['meta_keywords'] = $data['product']->seo_keywords;
                }
            }
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
        $this->load->view('frontend/shared/store_analytics_tracker', $data);
    }

    protected function current_customer()
    {
        $customer = storefront_customer();
        if (!$customer || (int) $customer->store_id !== (int) $this->store->id) {
            return null;
        }
        if (!empty($customer->id) && $this->db->table_exists('store_customers')) {
            $row = $this->db
                ->where('id', (int) $customer->id)
                ->where('store_id', (int) $this->store->id)
                ->get('store_customers')
                ->row();
            if ($row) {
                $this->set_customer($row);
                return $row;
            }
        }
        return $customer;
    }

    protected function set_customer($row)
    {
        $session = array(
            'id' => (int) $row->id,
            'store_id' => (int) $row->store_id,
            'name' => $row->name,
            'email' => $row->email,
            'phone' => isset($row->phone) ? $row->phone : '',
            'address' => isset($row->address) ? $row->address : '',
        );
        foreach (array('first_name', 'last_name', 'street', 'apartment', 'postcode', 'city', 'country', 'billing_same', 'billing_street', 'billing_apartment', 'billing_postcode', 'billing_city', 'billing_country') as $key) {
            if (isset($row->$key)) {
                $session[$key] = $row->$key;
            }
        }
        $_SESSION['storefront_customer'] = $session;
        unset($_SESSION['checkout_guest'][(int) $row->store_id]);
    }

    protected function remember_auth_return($path)
    {
        $path = trim((string) $path, '/');
        if (!preg_match('/^[a-z0-9\/_-]+$/i', $path)) {
            return;
        }
        $_SESSION['storefront_return'] = $path;
    }

    protected function auth_return_path()
    {
        return !empty($_SESSION['storefront_return']) ? (string) $_SESSION['storefront_return'] : '';
    }

    protected function redirect_after_auth()
    {
        $path = $this->auth_return_path();
        unset($_SESSION['storefront_return']);
        if ($path === '' || !preg_match('/^[a-z0-9\/_-]+$/i', $path)) {
            redirect(storefront_url('account'));
            return;
        }
        redirect(storefront_url($path));
    }

    protected function checkout_party_ready($customer, $draft)
    {
        if (!$draft || empty($draft['shipping'])) {
            return false;
        }
        if ($customer) {
            return true;
        }
        return !empty($draft['guest']);
    }

    protected function attach_guest_account($customer, $draft, &$accountMail)
    {
        $accountMail = null;
        if ($customer || empty($draft['guest']) || empty($draft['address_payload']) || !is_array($draft['address_payload'])) {
            return $customer;
        }
        ensure_customer_address_columns();
        $payload = $draft['address_payload'];
        $email = strtolower(trim(isset($payload['email']) ? $payload['email'] : ''));
        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return $customer;
        }
        $existing = $this->db
            ->where('store_id', (int) $this->store->id)
            ->where('email', $email)
            ->get('store_customers')
            ->row();
        if ($existing) {
            $this->set_customer($existing);
            return $existing;
        }
        $plain = substr(bin2hex(random_bytes(5)), 0, 10);
        $payload['email'] = $email;
        $payload['store_id'] = (int) $this->store->id;
        $payload['password'] = md5($plain);
        $this->db->insert('store_customers', $payload);
        $row = $this->db->where('id', (int) $this->db->insert_id())->get('store_customers')->row();
        if (!$row) {
            return $customer;
        }
        $this->set_customer($row);
        $accountMail = array(
            'email' => $email,
            'name' => isset($payload['name']) ? $payload['name'] : '',
            'password' => $plain,
        );
        return $row;
    }

    protected function send_guest_account_email($email, $name, $password, $order)
    {
        $email = strtolower(trim((string) $email));
        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || !$order) {
            return;
        }
        $storeName = isset($this->store->name) ? $this->store->name : 'Store';
        $loginUrl = storefront_url('account/login');
        $safe = function ($value) {
            return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
        };
        $html = '<div style="font-family:Arial,sans-serif;font-size:14px;color:#222;line-height:1.5">'
            . '<p>' . $safe(store_ui('mail.account_intro', array('{order}' => $order->order_no, '{store}' => $storeName))) . '</p>'
            . '<p><strong>' . $safe(store_ui('checkout.email')) . ':</strong> ' . $safe($email) . '<br>'
            . '<strong>' . $safe(store_ui('mail.account_password')) . ':</strong> ' . $safe($password) . '</p>'
            . '<p><strong>' . $safe(store_ui('cart.total')) . ':</strong> ' . $safe(format_money((float) $order->total, $order->currency)) . '</p>'
            . '<p>' . nl2br($safe(trim($name . "\n" . (isset($order->shipping_address) ? $order->shipping_address : '')))) . '</p>'
            . '<p><a href="' . $safe($loginUrl) . '">' . $safe(store_ui('mail.account_login')) . '</a></p>'
            . '</div>';
        $this->load->library('ec_mail');
        $this->ec_mail->send(
            $email,
            store_ui('mail.account_subject', array('{store}' => $storeName, '{order}' => $order->order_no)),
            $html
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
            $line = function_exists('offer_cart_line_calc')
                ? offer_cart_line_calc($product, $product->qty)
                : array(
                    'line_total' => round((float) $product->price * $product->qty, 2),
                    'gross_total' => round((float) $product->price * $product->qty, 2),
                    'discount' => 0,
                    'original_unit' => (float) $product->price,
                    'effective_unit' => (float) $product->price,
                );
            $product->line_total = (float) $line['line_total'];
            $product->line_gross = (float) $line['gross_total'];
            $product->line_discount = (float) $line['discount'];
            $product->original_unit_price = (float) $line['original_unit'];
            // Effective charged unit for order persistence (handles BXGY/bundle)
            $product->price = (float) $line['effective_unit'];
            $items[] = function_exists('storefront_localize_product') ? storefront_localize_product($product) : $product;
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
        $totals = $this->cart_totals();
        return $totals['cart_total'];
    }

    protected function cart_totals()
    {
        $subtotal = 0;
        $gross = 0;
        $discount = 0;
        $qty = 0;
        $items = $this->cart_items();
        foreach ($items as $item) {
            $subtotal += isset($item->line_total) ? (float) $item->line_total : 0;
            $gross += isset($item->line_gross) ? (float) $item->line_gross : (isset($item->line_total) ? (float) $item->line_total : 0);
            $discount += isset($item->line_discount) ? (float) $item->line_discount : 0;
            $qty += isset($item->qty) ? (int) $item->qty : 0;
        }
        $subtotal = round($subtotal, 2);
        $gross = round($gross, 2);
        $discount = round($discount, 2);
        $shipping = cart_shipping_amount($this->settings, $qty, $subtotal, $items);
        $shippingFree = ($shipping <= 0.0001) && ($qty > 0 || cart_shipping_is_free($subtotal));
        return array(
            'cart_subtotal' => $subtotal,
            'cart_gross' => $gross,
            'cart_discount' => $discount,
            'vat_percent' => (float) platform_setting('vat', 0),
            'vat_amount' => cart_vat_amount($subtotal),
            'shipping_qty' => $qty,
            'shipping_rate' => store_shipping_flat_rate($this->settings),
            'shipping_amount' => $shipping,
            'shipping_free' => $shippingFree,
            'shipping_free_min' => platform_shipping_free_min(),
            'show_shipping' => cart_show_shipping($this->settings, $shipping) || $shippingFree,
            'cart_total' => cart_total_payable($subtotal, $shipping),
        );
    }

    protected function products($limit = 0)
    {
        $this->catalog_query()->where('products.status', 1);
        $this->exclude_child_products();
        if (function_exists('storefront_order_by_sort')) {
            storefront_order_by_sort('products', 'desc');
        } else {
            $this->db->order_by('products.id', 'desc');
        }
        if ($limit) {
            $this->db->limit((int) $limit);
        }
        $products = $this->db->get()->result();
        $products = apply_storefront_pricing($products, $this->store->id);
        return function_exists('storefront_listing_products') ? storefront_listing_products($products) : $products;
    }

    protected function related_products($product, $trail, $limit = 8)
    {
        return $this->trending_products($product, $limit);
    }

    protected function trending_products($product, $limit = 4)
    {
        if (function_exists('ensure_product_trending_columns')) {
            ensure_product_trending_columns();
        }
        if (!$this->db->field_exists('is_trending', 'products')) {
            return array();
        }
        $excludeId = $product ? (int) $product->id : 0;
        if ($product && function_exists('storefront_listing_product')) {
            $excludeListing = storefront_listing_product($product);
            if ($excludeListing && !empty($excludeListing->id)) {
                $excludeId = (int) $excludeListing->id;
            }
        }
        $limit = max(1, (int) $limit);
        $storeId = (int) $this->store->id;

        $flagged = $this->db
            ->select('id, store_id, sku, parent_sku, is_trending, trending_order, status, price, compare_price')
            ->from('products')
            ->where('store_id', $storeId)
            ->where('status', 1)
            ->where('is_trending', 1)
            ->get()
            ->result();

        $parentIds = array();
        $orderById = array();
        foreach ($flagged as $row) {
            $listing = function_exists('storefront_listing_product')
                ? storefront_listing_product($row)
                : $row;
            if (!$listing || empty($listing->id)) {
                continue;
            }
            $parentSku = isset($listing->parent_sku) ? trim((string) $listing->parent_sku) : '';
            if ($parentSku !== '') {
                continue;
            }
            $pid = (int) $listing->id;
            if ($pid < 1 || $pid === $excludeId) {
                continue;
            }
            $parentIds[$pid] = $pid;
            $tord = isset($row->trending_order) ? (int) $row->trending_order : 0;
            if (!isset($orderById[$pid]) || $tord < $orderById[$pid]) {
                $orderById[$pid] = $tord;
            }
        }
        if (empty($parentIds)) {
            return array();
        }

        $this->catalog_query()->where('products.status', 1);
        $this->exclude_child_products();
        $this->db->where_in('products.id', array_values($parentIds));
        $products = $this->db->get()->result();

        usort($products, function ($a, $b) use ($orderById) {
            $oa = isset($orderById[(int) $a->id]) ? (int) $orderById[(int) $a->id] : 0;
            $ob = isset($orderById[(int) $b->id]) ? (int) $orderById[(int) $b->id] : 0;
            if ($oa === $ob) {
                return (int) $b->id - (int) $a->id;
            }
            return $oa - $ob;
        });
        if (count($products) > $limit) {
            $products = array_slice($products, 0, $limit);
        }

        $products = apply_storefront_pricing($products, $this->store->id);
        return function_exists('storefront_listing_products') ? storefront_listing_products($products) : $products;
    }

    protected function parent_ids_in_categories($categoryIds)
    {
        $categoryIds = array_values(array_unique(array_filter(array_map('intval', (array) $categoryIds))));
        if (empty($categoryIds)) {
            return array();
        }
        $storeId = (int) $this->store->id;
        $in = implode(',', $categoryIds);
        if ($this->db->field_exists('parent_sku', 'products')) {
            $sql = "SELECT DISTINCT listing.id AS pid
                FROM products p
                INNER JOIN product_categories pc ON pc.product_id = p.id
                LEFT JOIN products parent
                    ON parent.store_id = p.store_id
                    AND parent.sku = p.parent_sku
                    AND TRIM(IFNULL(p.parent_sku, '')) != ''
                INNER JOIN products listing
                    ON listing.id = IF(TRIM(IFNULL(p.parent_sku, '')) = '', p.id, IFNULL(parent.id, 0))
                WHERE p.store_id = ?
                  AND p.status = 1
                  AND listing.store_id = ?
                  AND listing.status = 1
                  AND TRIM(IFNULL(listing.parent_sku, '')) = ''
                  AND pc.category_id IN (" . $in . ")";
            $rows = $this->db->query($sql, array($storeId, $storeId))->result();
        } else {
            $rows = $this->db->query(
                "SELECT DISTINCT p.id AS pid
                 FROM products p
                 INNER JOIN product_categories pc ON pc.product_id = p.id
                 WHERE p.store_id = ? AND p.status = 1 AND pc.category_id IN (" . $in . ")",
                array($storeId)
            )->result();
        }
        $ids = array();
        foreach ($rows as $row) {
            $id = (int) $row->pid;
            if ($id > 0) {
                $ids[$id] = $id;
            }
        }
        return array_values($ids);
    }

    protected function products_in_categories($categoryIds, $limit = 12, $offset = 0)
    {
        $ids = $this->parent_ids_in_categories($categoryIds);
        if (empty($ids)) {
            return array();
        }
        $this->catalog_query()
            ->where_in('products.id', $ids)
            ->where('products.status', 1);
        $this->exclude_child_products();
        $this->db->group_by('products.id');
        if (function_exists('storefront_order_by_sort')) {
            storefront_order_by_sort('products', 'desc');
        } else {
            $this->db->order_by('products.id', 'desc');
        }
        $this->db->limit((int) $limit, (int) $offset);
        $products = $this->db->get()->result();
        $products = apply_storefront_pricing($products, $this->store->id);
        return function_exists('storefront_listing_products') ? storefront_listing_products($products) : $products;
    }

    protected function count_in_categories($categoryIds)
    {
        return count($this->parent_ids_in_categories($categoryIds));
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
        if (empty($filters['category'])) {
            $this->exclude_child_products();
        }

        if (!empty($filters['category'])) {
            $catSlugs = function_exists('ec_slug_candidates')
                ? ec_slug_candidates($filters['category'])
                : array($filters['category']);
            $this->db
                ->join('product_categories pc_filter', 'pc_filter.product_id = products.id', 'inner')
                ->join('categories c_filter', 'c_filter.id = pc_filter.category_id', 'inner')
                ->where_in('c_filter.slug', $catSlugs)
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

        if ($filters['sort'] === 'price_asc') {
            $this->db->order_by('products.price', 'asc');
        } elseif ($filters['sort'] === 'price_desc') {
            $this->db->order_by('products.price', 'desc');
        } elseif ($filters['sort'] === 'name') {
            $this->db->order_by('products.name', 'asc');
        } elseif (function_exists('storefront_order_by_sort')) {
            storefront_order_by_sort('products', 'desc');
        } else {
            $this->db->order_by('products.id', 'desc');
        }

        $this->db->group_by('products.id');
        $products = $this->db->get()->result();
        $products = apply_storefront_pricing($products, $this->store->id);
        if (function_exists('storefront_listing_products')) {
            $products = storefront_listing_products($products);
        }
        if (!empty($filters['on_sale'])) {
            $filtered = array();
            foreach ($products as $product) {
                if (isset($product->compare_price) && (float) $product->compare_price > (float) $product->price) {
                    $filtered[] = $product;
                }
            }
            $products = $filtered;
        }
        return $products;
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
