<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Product_import_model extends CI_Model {

    public function find_source($host)
    {
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
        $url = trim((string) $url);
        $domain = $this->normalize_host($domain);
        $hash = sha1($url);
        $existing = $this->db->where('url_hash', $hash)->get('product_import_unknown_links')->row();
        if ($existing) {
            $this->db->where('id', (int) $existing->id)->update('product_import_unknown_links', array(
                'hit_count' => (int) $existing->hit_count + 1,
                'country_id' => $countryId ? (int) $countryId : $existing->country_id,
                'user_id' => $userId ? (int) $userId : $existing->user_id,
                'last_seen_at' => date('Y-m-d H:i:s'),
            ));
            return (int) $existing->id;
        }

        $this->db->insert('product_import_unknown_links', array(
            'url' => $url,
            'url_hash' => $hash,
            'domain' => $domain,
            'country_id' => $countryId ? (int) $countryId : null,
            'user_id' => $userId ? (int) $userId : null,
            'hit_count' => 1,
            'created_at' => date('Y-m-d H:i:s'),
            'last_seen_at' => date('Y-m-d H:i:s'),
        ));
        return (int) $this->db->insert_id();
    }

    public function unknown_links()
    {
        return $this->db
            ->select('product_import_unknown_links.*, countries.name as country_name, CONCAT(users.first_name, " ", users.last_name) as user_name, users.uname', false)
            ->from('product_import_unknown_links')
            ->join('countries', 'countries.id = product_import_unknown_links.country_id', 'left')
            ->join('users', 'users.UserID = product_import_unknown_links.user_id', 'left')
            ->order_by('product_import_unknown_links.last_seen_at', 'desc')
            ->get()
            ->result();
    }

    public function unknown_count()
    {
        return (int) $this->db->count_all('product_import_unknown_links');
    }

    public function delete_unknown($id)
    {
        return $this->db->where('id', (int) $id)->delete('product_import_unknown_links');
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
