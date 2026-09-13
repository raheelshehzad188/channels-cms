<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Categories extends CI_Controller {

    public function __construct()
    {
        parent::__construct();
        ec_require_admin();
        $this->load->model('Ec_category_model');
        $this->load->model('Country_model');
    }

    public function index()
    {
        $countryId = (int) $this->input->get('country_id');
        $data = array(
            'title' => 'Categories',
            'countries' => $this->Country_model->all(),
            'country_id' => $countryId,
            'categories' => $this->Ec_category_model->all($countryId ? array('country_id' => $countryId) : array()),
        );
        $this->template->admin('categories/index', $data);
    }

    public function form($id = 0)
    {
        $category = $id ? $this->Ec_category_model->get($id) : null;
        if ($id && !$category) {
            $this->session->set_flashdata('error', 'Category not found.');
            redirect('admin/categories');
            return;
        }
        $countryId = $category ? (int) $category->country_id : (int) $this->input->get('country_id');
        $parents = $countryId
            ? $this->Ec_category_model->all(array('country_id' => $countryId, 'parent_id' => 0))
            : array();

        $data = array(
            'title' => $category ? 'Edit Category' : 'Add Category',
            'category' => $category,
            'countries' => $this->Country_model->all(),
            'parents' => $parents,
            'country_id' => $countryId,
        );
        $this->template->admin('categories/form', $data);
    }

    public function save($id = 0)
    {
        $name = trim((string) $this->input->post('name'));
        $countryId = (int) $this->input->post('country_id');
        if ($name === '' || !$countryId) {
            $this->session->set_flashdata('error', 'Name and country are required.');
            redirect($id ? 'admin/categories/form/' . $id : 'admin/categories/form');
            return;
        }

        $slug = url_title(trim((string) $this->input->post('slug')) ?: $name, 'dash', true);
        $parentId = (int) $this->input->post('parent_id');
        if ($parentId && $id && $parentId === (int) $id) {
            $parentId = 0;
        }

        $payload = array(
            'country_id' => $countryId,
            'parent_id' => $parentId ? $parentId : null,
            'name' => $name,
            'slug' => $slug,
            'icon' => trim((string) $this->input->post('icon')),
            'description' => trim((string) $this->input->post('description')),
            'seo_title' => trim((string) $this->input->post('seo_title')),
            'seo_description' => trim((string) $this->input->post('seo_description')),
            'seo_keywords' => trim((string) $this->input->post('seo_keywords')),
            'hero_kicker' => trim((string) $this->input->post('hero_kicker')),
            'hero_title' => trim((string) $this->input->post('hero_title')),
            'hero_text' => trim((string) $this->input->post('hero_text')),
            'hero_btn_text' => trim((string) $this->input->post('hero_btn_text')),
            'hero_btn_link' => trim((string) $this->input->post('hero_btn_link')),
            'status' => (int) $this->input->post('status') === 1 ? 1 : 0,
            'sort_order' => (int) $this->input->post('sort_order'),
        );

        $existing = $id ? $this->Ec_category_model->get($id) : null;
        $image = $this->upload_field('image');
        if ($image) {
            $payload['image'] = $image;
        } elseif (!$existing) {
            $payload['image'] = '';
        }
        $hero = $this->upload_field('hero_image');
        if ($hero) {
            $payload['hero_image'] = $hero;
        } elseif (!$existing) {
            $payload['hero_image'] = '';
        }

        // unique slug per country
        $dup = $this->db
            ->where('country_id', $countryId)
            ->where('slug', $slug)
            ->where('id !=', (int) $id)
            ->get('categories')
            ->row();
        if ($dup) {
            $payload['slug'] = $slug . '-' . substr(uniqid(), -4);
        }

        $this->Ec_category_model->save($payload, $id);
        $this->session->set_flashdata('success', $id ? 'Category updated.' : 'Category created.');
        redirect('admin/categories?country_id=' . $countryId);
    }

    public function delete($id = 0)
    {
        if (!$id || !$this->Ec_category_model->get($id)) {
            $this->session->set_flashdata('error', 'Category not found.');
            redirect('admin/categories');
            return;
        }
        $this->Ec_category_model->delete($id);
        $this->session->set_flashdata('success', 'Category deleted.');
        redirect('admin/categories');
    }

    public function parents()
    {
        $countryId = (int) $this->input->get('country_id');
        $excludeId = (int) $this->input->get('exclude_id');
        $parents = $countryId
            ? $this->Ec_category_model->all(array('country_id' => $countryId, 'parent_id' => 0))
            : array();
        $out = array();
        foreach ($parents as $parent) {
            if ($excludeId && (int) $parent->id === $excludeId) {
                continue;
            }
            $out[] = array(
                'id' => (int) $parent->id,
                'name' => $parent->name,
            );
        }
        $this->json_out($out);
    }

    public function import_preview()
    {
        $parsed = $this->read_import_csv();
        if (empty($parsed['ok'])) {
            $this->json_out($parsed, 400);
            return;
        }
        $plan = $this->Ec_category_model->plan_csv_import($parsed['rows'], $parsed['format'], $parsed['country_id']);
        $plan['country_id'] = $parsed['country_id'];
        $plan['format'] = $parsed['format'];
        $this->json_out($this->trim_plan_for_json($plan));
    }

    public function import_save()
    {
        @set_time_limit(0);
        $parsed = $this->read_import_csv();
        if (empty($parsed['ok'])) {
            $this->json_out($parsed, 400);
            return;
        }
        $plan = $this->Ec_category_model->plan_csv_import($parsed['rows'], $parsed['format'], $parsed['country_id']);
        if (empty($plan['ok'])) {
            $this->json_out($plan, 400);
            return;
        }
        if (!empty($plan['creates'])) {
            foreach ($plan['creates'] as &$payload) {
                if (!empty($payload['_image_url'])) {
                    $payload['image'] = $this->download_image_from_url($payload['_image_url']);
                }
                if (!empty($payload['_hero_url'])) {
                    $payload['hero_image'] = $this->download_image_from_url($payload['_hero_url']);
                }
            }
            unset($payload);
        }
        $result = $this->Ec_category_model->execute_csv_import($plan);
        if (empty($result['ok'])) {
            $this->json_out($result, 400);
            return;
        }

        $summary = 'Imported: ' . (int) $result['imported']
            . '<br>Skipped/Duplicate: ' . (int) $result['skipped']
            . '<br>Failed: ' . (int) $result['failed'];
        $this->session->set_flashdata('success', $summary);
        $result['country_id'] = $parsed['country_id'];
        $result['summary'] = $summary;
        $this->json_out($this->trim_plan_for_json($result));
    }

    protected function trim_plan_for_json($plan)
    {
        unset($plan['creates'], $plan['new_roots'], $plan['new_children'], $plan['resolve_parents']);
        if (!empty($plan['rows']) && count($plan['rows']) > 300) {
            $plan['rows'] = array_slice($plan['rows'], 0, 300);
            $plan['preview_truncated'] = true;
        }
        return $plan;
    }

    protected function read_import_csv()
    {
        if (empty($_FILES['csv']['name']) || empty($_FILES['csv']['tmp_name'])) {
            return array('ok' => false, 'error' => 'Please select a CSV file.');
        }
        if (!empty($_FILES['csv']['error']) && (int) $_FILES['csv']['error'] !== UPLOAD_ERR_OK) {
            return array('ok' => false, 'error' => 'Could not upload the CSV file.');
        }
        if ((int) $_FILES['csv']['size'] > 10 * 1024 * 1024) {
            return array('ok' => false, 'error' => 'CSV file is too large (max 10 MB).');
        }
        $ext = strtolower(pathinfo($_FILES['csv']['name'], PATHINFO_EXTENSION));
        if ($ext !== 'csv') {
            return array('ok' => false, 'error' => 'Please upload a .csv file.');
        }
        if (!is_uploaded_file($_FILES['csv']['tmp_name'])) {
            return array('ok' => false, 'error' => 'Invalid upload.');
        }

        $parsed = $this->parse_category_csv($_FILES['csv']['tmp_name']);
        if (empty($parsed['ok'])) {
            return $parsed;
        }

        $countryId = (int) $this->input->post('country_id');
        if ($parsed['format'] === 'name_parent') {
            if (!$countryId) {
                return array('ok' => false, 'error' => 'Please select a country before importing a Name/Parent CSV.');
            }
            if (!$this->Country_model->get($countryId)) {
                return array('ok' => false, 'error' => 'Selected country was not found.');
            }
        } elseif (!empty($parsed['rows'][0]['country_id'])) {
            $countryId = (int) $parsed['rows'][0]['country_id'];
        }
        $parsed['country_id'] = $countryId;
        return $parsed;
    }

    protected function parse_category_csv($path)
    {
        if (function_exists('ini_set')) {
            @ini_set('auto_detect_line_endings', '1');
        }
        $fh = fopen($path, 'r');
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
            $key = strtolower(trim((string) $header));
            $key = trim($key, "\"'");
            if ($key !== '') {
                $map[$key] = $i;
            }
        }

        $fullCols = array(
            'country_id', 'parent', 'name', 'slug', 'icon',
            'category_image_url', 'default_hero_image_url',
            'hero_kicker', 'hero_title', 'hero_text',
        );
        $isFull = true;
        $missing = array();
        foreach ($fullCols as $col) {
            if (!isset($map[$col])) {
                $isFull = false;
                $missing[] = $col;
            }
        }

        $isNameParent = isset($map['name']) && isset($map['parent']) && !isset($map['country_id']);
        if (!$isFull && !$isNameParent) {
            fclose($fh);
            $hint = isset($map['parent_id'])
                ? ' Use the parent column (parent category name), not parent_id.'
                : '';
            return array(
                'ok' => false,
                'error' => 'CSV must contain these columns: ' . implode(', ', $fullCols)
                    . '. Missing: ' . implode(', ', $missing) . '.' . $hint,
            );
        }

        $format = $isFull ? 'full' : 'name_parent';
        $rows = array();
        $line = 1;
        while (($data = fgetcsv($fh, 0, $delimiter)) !== false) {
            $line++;
            if ($data === array(null) || $data === false) {
                continue;
            }
            $empty = true;
            foreach ($data as $cell) {
                if (trim((string) $cell) !== '') {
                    $empty = false;
                    break;
                }
            }
            if ($empty) {
                continue;
            }
            if ($format === 'full') {
                $row = array('line' => $line);
                foreach ($fullCols as $col) {
                    $row[$col] = isset($data[$map[$col]]) ? trim((string) $data[$map[$col]]) : '';
                }
                $rows[] = $row;
            } else {
                $rows[] = array(
                    'line' => $line,
                    'name' => isset($data[$map['name']]) ? trim((string) $data[$map['name']]) : '',
                    'parent' => isset($data[$map['parent']]) ? trim((string) $data[$map['parent']]) : '',
                );
            }
        }
        fclose($fh);

        if (empty($rows)) {
            return array('ok' => false, 'error' => 'The CSV file has a valid header but no category rows.');
        }

        return array('ok' => true, 'format' => $format, 'rows' => $rows);
    }

    protected function json_out($data, $code = 200)
    {
        $this->output
            ->set_status_header((int) $code)
            ->set_content_type('application/json', 'utf-8')
            ->set_output(json_encode($data));
    }

    protected function download_image_from_url($url)
    {
        $url = trim((string) $url);
        if ($url === '' || !preg_match('#^https?://#i', $url)) {
            return '';
        }

        $dir = FCPATH . 'uploads/categories/';
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $base = 'cat_' . md5($url);
        $webpName = $base . '.webp';
        if (is_file($dir . $webpName)) {
            return 'uploads/categories/' . $webpName;
        }

        $ch = curl_init($url);
        curl_setopt_array($ch, array(
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_TIMEOUT => 20,
            CURLOPT_USERAGENT => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0.0.0 Safari/537.36',
            CURLOPT_HTTPHEADER => array(
                'Accept: image/avif,image/webp,image/apng,image/*,*/*;q=0.8',
                'Referer: ' . $url,
            ),
        ));
        $bin = curl_exec($ch);
        $code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $ctype = (string) curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
        curl_close($ch);
        if ($bin === false || $code >= 400 || strlen($bin) < 500) {
            return '';
        }
        if (preg_match('/^\s*</', $bin)) {
            return '';
        }

        $alreadyWebp = (stripos($ctype, 'webp') !== false)
            || (substr($bin, 0, 4) === 'RIFF' && stripos(substr($bin, 0, 16), 'WEBP') !== false);
        if ($alreadyWebp) {
            if (@file_put_contents($dir . $webpName, $bin) !== false) {
                return 'uploads/categories/' . $webpName;
            }
            return '';
        }

        $ext = 'jpg';
        if (stripos($ctype, 'png') !== false || substr($bin, 0, 8) === "\x89PNG\r\n\x1a\n") {
            $ext = 'png';
        } elseif (stripos($ctype, 'gif') !== false || substr($bin, 0, 3) === 'GIF') {
            $ext = 'gif';
        }
        $tmpName = $base . '.src.' . $ext;
        if (@file_put_contents($dir . $tmpName, $bin) === false) {
            return '';
        }
        $converted = ec_convert_image_to_webp($dir . $tmpName);
        return ec_public_upload_path($converted);
    }

    protected function upload_field($field)
    {
        if (empty($_FILES[$field]['name'])) {
            return '';
        }
        $dir = FCPATH . 'uploads/categories/';
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
        $data = $this->upload->data();
        $converted = ec_convert_image_to_webp($data['full_path']);
        return ec_public_upload_path($converted);
    }
}
