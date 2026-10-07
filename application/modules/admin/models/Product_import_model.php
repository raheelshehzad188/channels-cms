<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Product_import_model extends CI_Model {

    public function find_source($host)
    {
        $this->ensure_sources_table();
        $host = $this->normalize_host($host);
        if ($host === '') {
            return null;
        }

        $sources = $this->db->where('status', 1)->get('product_import_sources')->result();
        $suffixMatch = null;
        foreach ($sources as $source) {
            $domain = $this->normalize_host($source->domain);
            if ($domain === '') {
                continue;
            }
            if ($host === $domain) {
                return $source;
            }
            $needle = '.' . $domain;
            if (strlen($host) > strlen($needle) && substr($host, -strlen($needle)) === $needle) {
                $suffixMatch = $source;
            }
        }
        return $suffixMatch;
    }

    public function learn_source($host, $countryId = 0)
    {
        return $this->ensure_source($host, $countryId);
    }

    public function ensure_source($host, $countryId = 0)
    {
        $this->ensure_sources_table();
        $host = $this->normalize_host($host);
        if ($host === '') {
            return null;
        }

        $spec = $this->marketplace_spec($host, $countryId);
        if ($spec && $this->has_importer_class($spec['class'])) {
            $supplierId = $this->find_or_create_supplier($spec['supplier'], $spec['country']);
            if (!$supplierId) {
                return $this->find_source($host);
            }
            $payload = array(
                'domain' => $spec['domain'],
                'importer_class' => $spec['class'],
                'supplier_id' => $supplierId,
                'country_id' => $this->country_id_from_code($spec['country']),
                'status' => 1,
            );
            $row = $this->db->where('domain', $spec['domain'])->get('product_import_sources')->row();
            if ($row) {
                $this->db->where('id', (int) $row->id)->update('product_import_sources', $payload);
            } else {
                $this->db->insert('product_import_sources', $payload);
            }
            return $this->find_source($host);
        }

        $existing = $this->find_source($host);
        if ($existing && $this->has_importer_class($existing->importer_class)) {
            return $existing;
        }
        return $existing;
    }

    public function country_id_for_host($host)
    {
        $this->ensure_sources_table();
        $host = $this->normalize_host($host);
        if ($host === '') {
            return 0;
        }
        $source = $this->ensure_source($host);
        if ($source && !empty($source->country_id)) {
            return (int) $source->country_id;
        }
        $spec = $this->marketplace_spec($host);
        if ($spec) {
            $id = $this->country_id_from_code($spec['country']);
            if ($id) {
                return $id;
            }
        }
        if ($source && !empty($source->supplier_id) && $this->db->table_exists('suppliers')) {
            $supplier = $this->db->select('country_id')->where('id', (int) $source->supplier_id)->get('suppliers')->row();
            if ($supplier) {
                return (int) $supplier->country_id;
            }
        }
        return 0;
    }

    public function country_id_from_code($code)
    {
        $code = strtoupper(trim((string) $code));
        if ($code === '' || !$this->db->table_exists('countries')) {
            return 0;
        }
        $row = $this->db->select('id')->where('code', $code)->get('countries')->row();
        return $row ? (int) $row->id : 0;
    }

    protected function marketplace_spec($host, $countryId = 0)
    {
        $map = array(
            'amazon.co.uk' => array('domain' => 'amazon.co.uk', 'class' => 'Amazon_uk', 'supplier' => 'AP UK', 'country' => 'GB'),
            'amazon.com.au' => array('domain' => 'amazon.com.au', 'class' => 'Amazon_au', 'supplier' => 'Amazon AU', 'country' => 'AU'),
            'amazon.in' => array('domain' => 'amazon.in', 'class' => 'Amazon_in', 'supplier' => 'MN IN', 'country' => 'IN'),
            'amazon.com' => array('domain' => 'amazon.com', 'class' => 'Amazon_uk', 'supplier' => 'AP UK', 'country' => 'US'),
            'onworks.net' => array('domain' => 'onworks.net', 'class' => 'Onworks', 'supplier' => 'AP UK', 'country' => 'GB'),
            'ebay.co.uk' => array('domain' => 'ebay.co.uk', 'class' => 'Ebay_uk', 'supplier' => 'AP UK', 'country' => 'GB'),
            'ebay.com' => array('domain' => 'ebay.com', 'class' => 'Ebay_com', 'supplier' => 'eBay US', 'country' => 'US'),
            'godropship.co.uk' => array('domain' => 'godropship.co.uk', 'class' => 'Godropship_uk', 'supplier' => 'AP UK', 'country' => 'GB'),
            'aw-dropship.com' => array('domain' => 'aw-dropship.com', 'class' => 'Aw_dropship', 'supplier' => 'AW Dropship', 'country' => 'GB'),
            'discountpartysupplies.com.au' => array('domain' => 'discountpartysupplies.com.au', 'class' => 'Discountpartysupplies_au', 'supplier' => 'Discount Party Supplies', 'country' => 'AU'),
            'spotlightstores.com' => array('domain' => 'spotlightstores.com', 'class' => 'Spotlight_au', 'supplier' => 'Spotlight Australia', 'country' => 'AU'),
            'fyndiq.se' => array('domain' => 'fyndiq.se', 'class' => 'Fyndiq_se', 'supplier' => 'FYNDIQ SE', 'country' => 'SE'),
            'cdon.se' => array('domain' => 'cdon.se', 'class' => 'Cdon_se', 'supplier' => 'CDON SE', 'country' => 'SE'),
            'partyhallen.se' => array('domain' => 'partyhallen.se', 'class' => 'Partyhallen_se', 'supplier' => 'Partyhallen SE', 'country' => 'SE'),
            'dollarstore.se' => array('domain' => 'dollarstore.se', 'class' => 'Dollarstore_se', 'supplier' => 'Dollarstore SE', 'country' => 'SE'),
            'husglad.se' => array('domain' => 'husglad.se', 'class' => 'Husglad_se', 'supplier' => 'Husglad SE', 'country' => 'SE'),
            'partykungen.se' => array('domain' => 'partykungen.se', 'class' => 'Partykungen_se', 'supplier' => 'Partykungen SE', 'country' => 'SE'),
            'ilovefancydress.com' => array('domain' => 'ilovefancydress.com', 'class' => 'Ilovefancydress_com', 'supplier' => 'I Love Fancy Dress UK', 'country' => 'GB'),
            'novo-haven.com' => array('domain' => 'novo-haven.com', 'class' => 'Shopify_store', 'supplier' => 'Novo Haven UK', 'country' => 'GB'),
            'aveltashop.com' => array('domain' => 'aveltashop.com', 'class' => 'Shopify_store', 'supplier' => 'Avelta Shop UK', 'country' => 'GB'),
            'eselic.us' => array('domain' => 'eselic.us', 'class' => 'Eselic_us', 'supplier' => 'Eselic SE', 'country' => 'SE'),
            'partyvikings.se' => array('domain' => 'partyvikings.se', 'class' => 'Jsonld_store', 'supplier' => 'PartyVikings Sweden', 'country' => 'SE'),
            'promixsweden.se' => array('domain' => 'promixsweden.se', 'class' => 'Jsonld_store', 'supplier' => 'Promix Sweden', 'country' => 'SE'),
            'temashop.se' => array('domain' => 'temashop.se', 'class' => 'Jsonld_store', 'supplier' => 'Temashop Sweden', 'country' => 'SE'),
            'joom.com' => array('domain' => 'joom.com', 'class' => 'Jsonld_store', 'supplier' => 'Joom Sweden', 'country' => 'SE'),
            'fruugo.se' => array('domain' => 'fruugo.se', 'class' => 'Jsonld_store', 'supplier' => 'Fruugo Sweden', 'country' => 'SE'),
            'aliexpress.com' => array('domain' => 'aliexpress.com', 'class' => 'Aliexpress_com', 'supplier' => 'AliExpress SE', 'country' => 'SE'),
        );
        foreach ($map as $domain => $spec) {
            if ($host === $domain || substr($host, -strlen('.' . $domain)) === '.' . $domain) {
                return $spec;
            }
        }
        if (preg_match('/(^|\.)amazon\./', $host)) {
            $country = $this->country_code((int) $countryId);
            if ($country === 'AU') {
                return $map['amazon.com.au'];
            }
            if ($country === 'IN') {
                return $map['amazon.in'];
            }
            return $map['amazon.co.uk'];
        }
        if (preg_match('/(^|\.)ebay\.com$/', $host)) {
            return $map['ebay.com'];
        }
        if (preg_match('/(^|\.)ebay\./', $host)) {
            return $map['ebay.co.uk'];
        }
        return null;
    }

    protected function country_code($countryId)
    {
        if ($countryId < 1 || !$this->db->table_exists('countries')) {
            return '';
        }
        $row = $this->db->select('code')->where('id', (int) $countryId)->get('countries')->row();
        return $row ? strtoupper((string) $row->code) : '';
    }

    protected function find_or_create_supplier($name, $countryCode)
    {
        $name = trim((string) $name);
        if ($name === '') {
            return 0;
        }
        $countryId = $this->country_id_from_code($countryCode);
        if ($countryId) {
            $row = $this->db->where('name', $name)->where('country_id', $countryId)->get('suppliers')->row();
            if ($row) {
                return (int) $row->id;
            }
        }
        $row = $this->db->where('name', $name)->get('suppliers')->row();
        if ($row) {
            return (int) $row->id;
        }
        if (!$countryId) {
            $country = $this->db->order_by('id', 'asc')->limit(1)->get('countries')->row();
            $countryId = $country ? (int) $country->id : 0;
        }
        if (!$countryId) {
            return 0;
        }
        $this->db->insert('suppliers', array(
            'country_id' => $countryId,
            'name' => $name,
            'email' => '',
            'phone' => '',
            'company' => $name,
            'address' => '',
            'status' => 1,
        ));
        return (int) $this->db->insert_id();
    }

    public function ensure_supplier_named($name, $countryCode)
    {
        return $this->find_or_create_supplier($name, $countryCode);
    }

    public function ensure_sources_table()
    {
        $this->db->query("CREATE TABLE IF NOT EXISTS `product_import_sources` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `domain` varchar(190) NOT NULL,
            `importer_class` varchar(100) NOT NULL,
            `supplier_id` int(11) NOT NULL,
            `country_id` int(11) DEFAULT NULL,
            `status` tinyint(1) NOT NULL DEFAULT 1,
            `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            UNIQUE KEY `domain` (`domain`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        if ($this->db->table_exists('product_import_sources') && !$this->db->field_exists('country_id', 'product_import_sources')) {
            $this->db->query("ALTER TABLE product_import_sources ADD COLUMN country_id INT(11) DEFAULT NULL AFTER supplier_id");
        }
    }

    public function class_file($className)
    {
        $className = preg_replace('/[^A-Za-z0-9_]/', '', (string) $className);
        if ($className === '') {
            return '';
        }
        return APPPATH . 'libraries/importers/' . $className . '.php';
    }

    public function has_importer_class($className)
    {
        $file = $this->class_file($className);
        return $file !== '' && is_file($file);
    }

    public function record_unknown($url, $domain, $countryId, $userId)
    {
        return $this->record_failed(array(
            'url' => $url,
            'domain' => $domain,
            'country_id' => $countryId,
            'user_id' => $userId,
            'error' => 'Unknown domain',
        ));
    }

    public function ensure_failed_table()
    {
        $this->db->query("CREATE TABLE IF NOT EXISTS `product_import_unknown_links` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `url` text NOT NULL,
            `product_name` varchar(255) NOT NULL DEFAULT '',
            `url_hash` char(40) NOT NULL,
            `domain` varchar(190) NOT NULL,
            `country_id` int(11) DEFAULT NULL,
            `user_id` int(11) NOT NULL DEFAULT 0,
            `category_id` int(11) DEFAULT NULL,
            `subcategory_id` int(11) DEFAULT NULL,
            `base_price` decimal(12,2) NOT NULL DEFAULT 0.00,
            `ship_min_days` int(11) NOT NULL DEFAULT 0,
            `ship_max_days` int(11) NOT NULL DEFAULT 0,
            `stock` int(11) NOT NULL DEFAULT 0,
            `error_message` varchar(255) NOT NULL DEFAULT '',
            `hit_count` int(11) NOT NULL DEFAULT 1,
            `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `last_seen_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            KEY `url_user` (`url_hash`, `user_id`),
            KEY `domain` (`domain`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

        $cols = array(
            'category_id' => "ALTER TABLE product_import_unknown_links ADD COLUMN category_id INT(11) DEFAULT NULL AFTER user_id",
            'subcategory_id' => "ALTER TABLE product_import_unknown_links ADD COLUMN subcategory_id INT(11) DEFAULT NULL AFTER category_id",
            'base_price' => "ALTER TABLE product_import_unknown_links ADD COLUMN base_price DECIMAL(12,2) NOT NULL DEFAULT 0.00 AFTER subcategory_id",
            'ship_min_days' => "ALTER TABLE product_import_unknown_links ADD COLUMN ship_min_days INT(11) NOT NULL DEFAULT 0 AFTER base_price",
            'ship_max_days' => "ALTER TABLE product_import_unknown_links ADD COLUMN ship_max_days INT(11) NOT NULL DEFAULT 0 AFTER ship_min_days",
            'stock' => "ALTER TABLE product_import_unknown_links ADD COLUMN stock INT(11) NOT NULL DEFAULT 0 AFTER ship_max_days",
            'error_message' => "ALTER TABLE product_import_unknown_links ADD COLUMN error_message VARCHAR(255) NOT NULL DEFAULT '' AFTER stock",
            'product_name' => "ALTER TABLE product_import_unknown_links ADD COLUMN product_name VARCHAR(255) NOT NULL DEFAULT '' AFTER url",
        );
        foreach ($cols as $field => $sql) {
            if (!$this->db->field_exists($field, 'product_import_unknown_links')) {
                $this->db->query($sql);
            }
        }

        $unique = $this->db->query("SHOW INDEX FROM product_import_unknown_links WHERE Key_name = 'url_hash'")->result();
        if ($unique) {
            $this->db->query('ALTER TABLE product_import_unknown_links DROP INDEX url_hash');
        }
        $userIdx = $this->db->query("SHOW INDEX FROM product_import_unknown_links WHERE Key_name = 'url_user'")->result();
        if (empty($userIdx)) {
            $this->db->query('ALTER TABLE product_import_unknown_links ADD KEY url_user (url_hash, user_id)');
        }
    }

    public function record_failed($data)
    {
        $this->ensure_failed_table();
        $url = trim((string) (isset($data['url']) ? $data['url'] : ''));
        if ($url === '') {
            return 0;
        }
        $host = isset($data['domain']) ? $this->normalize_host($data['domain']) : '';
        if ($host === '') {
            $parts = parse_url($url);
            $host = $this->normalize_host(isset($parts['host']) ? $parts['host'] : '');
        }
        $userId = isset($data['user_id']) ? (int) $data['user_id'] : 0;
        $hash = sha1($url);
        $payload = array(
            'url' => $url,
            'url_hash' => $hash,
            'domain' => $host,
            'country_id' => !empty($data['country_id']) ? (int) $data['country_id'] : null,
            'user_id' => $userId,
            'category_id' => !empty($data['category_id']) ? (int) $data['category_id'] : null,
            'subcategory_id' => !empty($data['subcategory_id']) ? (int) $data['subcategory_id'] : null,
            'base_price' => isset($data['base_price']) && $data['base_price'] !== null && $data['base_price'] !== ''
                ? (float) $data['base_price'] : 0,
            'ship_min_days' => max(0, (int) (isset($data['ship_min_days']) ? $data['ship_min_days'] : 0)),
            'ship_max_days' => max(0, (int) (isset($data['ship_max_days']) ? $data['ship_max_days'] : 0)),
            'stock' => max(0, (int) (isset($data['stock']) ? $data['stock'] : 0)),
            'error_message' => substr(trim((string) (isset($data['error']) ? $data['error'] : '')), 0, 255),
            'product_name' => substr(trim((string) (isset($data['product_name']) ? $data['product_name'] : '')), 0, 255),
            'last_seen_at' => date('Y-m-d H:i:s'),
        );

        $existing = $this->db
            ->where('url_hash', $hash)
            ->where('user_id', $userId)
            ->get('product_import_unknown_links')
            ->row();
        if (!$existing) {
            $rows = $this->db->where('url_hash', $hash)->get('product_import_unknown_links')->result();
            foreach ($rows as $row) {
                if ((int) $row->user_id === $userId || (int) $row->user_id === 0) {
                    $existing = $row;
                    break;
                }
            }
        }
        if ($existing) {
            if ($payload['product_name'] === '' && !empty($existing->product_name)) {
                unset($payload['product_name']);
            }
            $payload['hit_count'] = (int) $existing->hit_count + 1;
            $this->db->where('id', (int) $existing->id)->update('product_import_unknown_links', $payload);
            return (int) $existing->id;
        }

        $payload['hit_count'] = 1;
        $payload['created_at'] = date('Y-m-d H:i:s');
        $this->db->insert('product_import_unknown_links', $payload);
        return (int) $this->db->insert_id();
    }

    public function get_failed($id)
    {
        $this->ensure_failed_table();
        return $this->db->where('id', (int) $id)->get('product_import_unknown_links')->row();
    }

    public function unknown_links()
    {
        $this->ensure_failed_table();
        $select = array('product_import_unknown_links.*');
        $select[] = $this->db->table_exists('countries')
            ? 'countries.name as country_name'
            : 'NULL as country_name';
        $select[] = $this->db->table_exists('categories')
            ? 'cat.name as category_name, sub.name as subcategory_name'
            : 'NULL as category_name, NULL as subcategory_name';
        $select[] = $this->db->table_exists('users')
            ? 'CONCAT(users.first_name, " ", users.last_name) as user_name, users.uname'
            : 'NULL as user_name, NULL as uname';
        $this->db
            ->select(implode(', ', $select), false)
            ->from('product_import_unknown_links');
        if ($this->db->table_exists('countries')) {
            $this->db->join('countries', 'countries.id = product_import_unknown_links.country_id', 'left');
        }
        if ($this->db->table_exists('categories')) {
            $this->db->join('categories cat', 'cat.id = product_import_unknown_links.category_id', 'left');
            $this->db->join('categories sub', 'sub.id = product_import_unknown_links.subcategory_id', 'left');
        }
        if ($this->db->table_exists('users')) {
            $this->db->join('users', 'users.UserID = product_import_unknown_links.user_id', 'left');
        }
        return $this->db
            ->order_by('product_import_unknown_links.last_seen_at', 'desc')
            ->get()
            ->result();
    }

    public function unknown_count()
    {
        $this->ensure_failed_table();
        return (int) $this->db->count_all('product_import_unknown_links');
    }

    public function unknown_ids()
    {
        $this->ensure_failed_table();
        $rows = $this->db
            ->select('id')
            ->order_by('id', 'asc')
            ->get('product_import_unknown_links')
            ->result();
        $ids = array();
        foreach ($rows as $row) {
            $ids[] = (int) $row->id;
        }
        return $ids;
    }

    public function delete_unknown($id)
    {
        $this->ensure_failed_table();
        return $this->db->where('id', (int) $id)->delete('product_import_unknown_links');
    }

    public function clear_failed($url, $userId = 0)
    {
        $this->ensure_failed_table();
        $url = trim((string) $url);
        if ($url === '') {
            return;
        }
        $hash = sha1($url);
        $this->db->where('url_hash', $hash);
        if ($userId) {
            $this->db->group_start()
                ->where('user_id', (int) $userId)
                ->or_where('user_id', 0)
                ->group_end();
        }
        $this->db->delete('product_import_unknown_links');
    }

    public function normalize_host($host)
    {
        $host = strtolower(trim((string) $host));
        $host = preg_replace('#^https?://#i', '', $host);
        $host = preg_replace('#/.*$#', '', $host);
        $host = preg_replace('/:\d+$/', '', $host);
        return preg_replace('/^www\./', '', $host);
    }

    public function csv_required_columns()
    {
        return array(
            'product',
            'link',
            'category',
            'sub_category',
            'price',
            'delivery_min_days',
            'delivery_max_days',
        );
    }

    public function csv_template_header()
    {
        return implode(',', $this->csv_required_columns());
    }

    public function parse_csv_file($path)
    {
        if (function_exists('ini_set')) {
            @ini_set('auto_detect_line_endings', '1');
        }
        $fh = @fopen($path, 'r');
        if (!$fh) {
            return array('ok' => false, 'error' => 'Could not read the CSV file.');
        }

        $firstLine = fgets($fh);
        if ($firstLine === false) {
            fclose($fh);
            return array('ok' => false, 'error' => 'The CSV file is empty.');
        }
        if (substr($firstLine, 0, 3) === "\xEF\xBB\xBF") {
            $firstLine = substr($firstLine, 3);
        }
        $delimiter = (substr_count($firstLine, ';') > substr_count($firstLine, ',')) ? ';' : ',';
        $headers = str_getcsv($firstLine, $delimiter);
        $map = array();
        foreach ($headers as $i => $header) {
            $key = $this->normalize_csv_header($header);
            if ($key !== '') {
                $map[$key] = $i;
            }
        }

        $required = $this->csv_required_columns();
        $missing = array();
        foreach ($required as $col) {
            if (!isset($map[$col])) {
                $missing[] = $col;
            }
        }
        if ($missing) {
            fclose($fh);
            return array(
                'ok' => false,
                'error' => 'CSV must contain these columns: ' . implode(', ', $required)
                    . '. Missing: ' . implode(', ', $missing) . '. Do not include country_id — select the country in the import form.',
            );
        }

        $rows = array();
        $line = 1;
        while (($cols = fgetcsv($fh, 0, $delimiter)) !== false) {
            $line++;
            if ($this->csv_row_empty($cols)) {
                continue;
            }
            $get = function ($name) use ($map, $cols) {
                if (!isset($map[$name]) || !isset($cols[$map[$name]])) {
                    return '';
                }
                return trim((string) $cols[$map[$name]]);
            };
            $rows[] = array(
                'line' => $line,
                'product' => $get('product'),
                'link' => $get('link'),
                'category' => $get('category'),
                'sub_category' => $get('sub_category'),
                'price' => $get('price'),
                'delivery_min_days' => $get('delivery_min_days'),
                'delivery_max_days' => $get('delivery_max_days'),
            );
        }
        fclose($fh);

        if (!$rows) {
            return array('ok' => false, 'error' => 'The CSV file has no product rows.');
        }
        if (count($rows) > 2000) {
            return array('ok' => false, 'error' => 'CSV is too large. Import at most 2000 products at a time.');
        }

        return array('ok' => true, 'rows' => $this->validate_csv_rows($rows));
    }

    public function validate_csv_rows($rows)
    {
        $out = array();
        foreach ($rows as $row) {
            $error = $this->validate_csv_row($row);
            $row['valid'] = ($error === '');
            $row['error'] = $error;
            if ($row['valid']) {
                $row['price'] = $this->normalize_csv_price($row['price']);
                $row['delivery_min_days'] = (int) $row['delivery_min_days'];
                $row['delivery_max_days'] = (int) $row['delivery_max_days'];
            }
            $out[] = $row;
        }
        return $out;
    }

    public function validate_csv_row($row)
    {
        $product = trim(isset($row['product']) ? (string) $row['product'] : '');
        if ($product === '') {
            return 'Product name cannot be empty.';
        }

        $link = trim(isset($row['link']) ? (string) $row['link'] : '');
        if ($link === '' || !preg_match('#^https?://#i', $link) || !filter_var($link, FILTER_VALIDATE_URL)) {
            return 'Link must be a valid URL.';
        }

        $category = trim(isset($row['category']) ? (string) $row['category'] : '');
        $sub = trim(isset($row['sub_category']) ? (string) $row['sub_category'] : '');
        if ($category === '') {
            return 'Category cannot be empty.';
        }
        if ($sub === '') {
            return 'Sub category cannot be empty.';
        }

        $priceRaw = trim(isset($row['price']) ? (string) $row['price'] : '');
        $price = $this->normalize_csv_price($priceRaw);
        if ($priceRaw === '' || $price === false) {
            return 'Price must be numeric.';
        }
        if ($price < 0) {
            return 'Price cannot be negative.';
        }

        $minRaw = trim(isset($row['delivery_min_days']) ? (string) $row['delivery_min_days'] : '');
        $maxRaw = trim(isset($row['delivery_max_days']) ? (string) $row['delivery_max_days'] : '');
        if (!$this->is_int_string($minRaw)) {
            return 'delivery_min_days must be an integer.';
        }
        if (!$this->is_int_string($maxRaw)) {
            return 'delivery_max_days must be an integer.';
        }
        $min = (int) $minRaw;
        $max = (int) $maxRaw;
        if ($min < 0 || $max < 0) {
            return 'Delivery days cannot be negative.';
        }
        if ($min > $max) {
            return 'delivery_min_days cannot be greater than delivery_max_days.';
        }

        return '';
    }

    public function store_batch($countryId, $countryName, $userId, $rows)
    {
        $this->cleanup_old_batches();
        $token = bin2hex(function_exists('random_bytes') ? random_bytes(16) : openssl_random_pseudo_bytes(16));
        $payload = array(
            'token' => $token,
            'user_id' => (int) $userId,
            'country_id' => (int) $countryId,
            'country_name' => (string) $countryName,
            'created_at' => time(),
            'rows' => $rows,
        );
        $dir = $this->batch_dir();
        if (!is_dir($dir) && !@mkdir($dir, 0755, true)) {
            throw new Exception('Could not prepare the import queue.');
        }
        $path = $this->batch_path($token);
        if (@file_put_contents($path, json_encode($payload)) === false) {
            throw new Exception('Could not save the import queue.');
        }
        $this->ci_session_set($token);
        return $payload;
    }

    public function load_batch($token, $userId)
    {
        $token = preg_replace('/[^a-f0-9]/', '', strtolower((string) $token));
        if ($token === '' || $token !== (string) $this->ci_session_token()) {
            return null;
        }
        $path = $this->batch_path($token);
        if (!is_file($path)) {
            return null;
        }
        $payload = json_decode((string) file_get_contents($path), true);
        if (!is_array($payload) || (int) $payload['user_id'] !== (int) $userId) {
            return null;
        }
        return $payload;
    }

    public function batch_row($token, $userId, $index)
    {
        $batch = $this->load_batch($token, $userId);
        if (!$batch || !isset($batch['rows'][$index])) {
            return null;
        }
        return array(
            'batch' => $batch,
            'row' => $batch['rows'][$index],
            'total' => count($batch['rows']),
        );
    }

    public function delete_batch($token)
    {
        $path = $this->batch_path($token);
        if (is_file($path)) {
            @unlink($path);
        }
        $this->ci_session_set('');
    }

    protected function normalize_csv_header($header)
    {
        $key = strtolower(trim((string) $header));
        $key = trim($key, "\"'");
        $key = str_replace(array(' ', '-'), '_', $key);
        $aliases = array(
            'product_name' => 'product',
            'url' => 'link',
            'source_url' => 'link',
            'product_url' => 'link',
            'subcategory' => 'sub_category',
            'min_days' => 'delivery_min_days',
            'max_days' => 'delivery_max_days',
        );
        if (isset($aliases[$key])) {
            return $aliases[$key];
        }
        return $key;
    }

    protected function csv_row_empty($cols)
    {
        if (!is_array($cols)) {
            return true;
        }
        foreach ($cols as $col) {
            if (trim((string) $col) !== '') {
                return false;
            }
        }
        return true;
    }

    protected function normalize_csv_price($value)
    {
        $value = trim((string) $value);
        $value = str_replace(array(' ', ','), array('', ''), $value);
        if ($value === '' || !is_numeric($value)) {
            return false;
        }
        return round((float) $value, 2);
    }

    protected function is_int_string($value)
    {
        return $value !== '' && preg_match('/^-?\d+$/', (string) $value);
    }

    protected function batch_dir()
    {
        return APPPATH . 'cache/product_csv_imports/';
    }

    protected function batch_path($token)
    {
        $token = preg_replace('/[^a-f0-9]/', '', strtolower((string) $token));
        return $this->batch_dir() . $token . '.json';
    }

    protected function cleanup_old_batches()
    {
        $dir = $this->batch_dir();
        if (!is_dir($dir)) {
            return;
        }
        $cutoff = time() - 86400;
        foreach (glob($dir . '*.json') ?: array() as $file) {
            if (is_file($file) && filemtime($file) < $cutoff) {
                @unlink($file);
            }
        }
    }

    protected function ci_session_set($token)
    {
        $ci =& get_instance();
        if ($token === '') {
            $ci->session->unset_userdata('product_csv_import_token');
            return;
        }
        $ci->session->set_userdata('product_csv_import_token', $token);
    }

    protected function ci_session_token()
    {
        $ci =& get_instance();
        return $ci->session->userdata('product_csv_import_token');
    }
}
