<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Gemini_content {

    const PROMPT_VERSION = 'ai-content-v1';
    const REQUIRED_FIELDS = array(
        'title',
        'short_detail',
        'long_detail',
        'seo_title',
        'seo_description',
        'seo_keywords',
        'seo_slug',
    );

    protected $CI;
    protected $lastError = '';

    public function __construct()
    {
        $this->CI =& get_instance();
    }

    public function last_error()
    {
        return $this->lastError;
    }

    public function api_key()
    {
        $key = trim((string) platform_setting('ai_agent_api_key', ''));
        if ($key !== '') {
            return $key;
        }
        $legacy = trim((string) platform_setting('gemini_api_key', ''));
        if ($legacy !== '' && strpos($legacy, 'ag_') === 0) {
            return $legacy;
        }
        $env = trim((string) getenv('AI_AGENT_API_KEY'));
        return $env;
    }

    public function has_api_key()
    {
        return $this->api_key() !== '';
    }

    public function api_url()
    {
        $url = trim((string) platform_setting('ai_agent_url', ''));
        return $url !== '' ? $url : 'https://zenvello.co.uk/api/v1/agent';
    }

    public function language_for($country = null, $store = null)
    {
        if ($store && !empty($store->language)) {
            $lang = strtolower(trim((string) $store->language));
            if (isset($this->language_names()[$lang])) {
                return $lang;
            }
        }
        $code = '';
        if ($store && !empty($store->country_code)) {
            $code = strtoupper(trim((string) $store->country_code));
        }
        if ($code === '' && $country && !empty($country->code)) {
            $code = strtoupper(trim((string) $country->code));
        }
        $map = array(
            'SE' => 'sv',
            'GB' => 'en',
            'UK' => 'en',
            'US' => 'en',
            'IE' => 'en',
            'AU' => 'en',
            'CA' => 'en',
            'IT' => 'it',
            'DE' => 'de',
            'AT' => 'de',
            'CH' => 'de',
            'FR' => 'fr',
            'ES' => 'es',
            'NL' => 'nl',
            'BE' => 'nl',
            'NO' => 'nb',
            'DK' => 'da',
            'FI' => 'fi',
            'PL' => 'pl',
            'PT' => 'pt',
            'BR' => 'pt',
        );
        if ($code !== '' && isset($map[$code])) {
            return $map[$code];
        }
        return 'en';
    }

    public function language_name($code)
    {
        $names = $this->language_names();
        $code = strtolower(trim((string) $code));
        return isset($names[$code]) ? $names[$code] : 'English';
    }

    public function extract_quantity($text)
    {
        $text = trim((string) $text);
        if ($text === '') {
            return 0;
        }
        if (preg_match('/\b(\d{1,4})\s*[-\/]?\s*(pack|packs|pcs|pc|stk|st\.?|pieces?|pezzi|stuks|pk)\b/iu', $text, $m)) {
            return (int) $m[1];
        }
        if (preg_match('/\b(\d{1,4})\s*[x×]\b/u', $text, $m)) {
            return (int) $m[1];
        }
        return 0;
    }

    public function rewrite($product, $context = array())
    {
        $this->lastError = '';
        if (!$this->has_api_key()) {
            $this->lastError = 'AI agent API key is not configured. Add it under Admin > AI Settings.';
            return null;
        }

        $quantity = $this->extract_quantity(isset($product->name) ? $product->name : '');
        if (!$quantity && !empty($context['quantity'])) {
            $quantity = (int) $context['quantity'];
        }
        if (!$quantity && !empty($context['form_title'])) {
            $quantity = $this->extract_quantity($context['form_title']);
        }

        $raw = $this->request_agent($product, $context);
        if ($raw === null) {
            return null;
        }

        $validated = $this->validate($raw, $product, $quantity);
        if ($validated === null) {
            return null;
        }

        return $validated;
    }

    public function generate_reviews($product, $context, $count)
    {
        $this->lastError = '';
        $count = (int) $count;
        if ($count < 1 || $count > 50) {
            $this->lastError = 'Review count must be between 1 and 50.';
            return null;
        }
        if (!$this->has_api_key()) {
            $this->lastError = 'AI agent API key is not configured. Add it under Admin > AI Settings.';
            return null;
        }

        $language = isset($context['language']) ? (string) $context['language'] : 'en';
        $title = isset($product->name) ? trim((string) $product->name) : '';
        $details = '';
        if (!empty($product->details)) {
            $details = $this->plain_text($product->details);
        } elseif (!empty($product->short_details)) {
            $details = $this->plain_text($product->short_details);
        } elseif (!empty($product->description)) {
            $details = $this->plain_text($product->description);
        }
        $features = isset($context['features']) ? trim((string) $context['features']) : '';
        $variations = isset($context['variations']) ? trim((string) $context['variations']) : '';
        $facts = $title;
        if ($details !== '') {
            $facts .= "\n" . $details;
        }
        if ($features !== '') {
            $facts .= "\nFeatures: " . $features;
        }
        if ($variations !== '') {
            $facts .= "\nVariations: " . $variations;
        }
        if (function_exists('mb_substr')) {
            $facts = mb_substr($facts, 0, 3500, 'UTF-8');
        } else {
            $facts = substr($facts, 0, 3500);
        }

        $instruction = 'Return ONLY a JSON object with key "reviews" containing exactly '
            . $count . ' review objects. Each object must have integer rating (1-5), string name, optional string title, and string text. '
            . 'Write every review in ' . $this->language_name($language) . ' (' . $language . '). '
            . 'Reviews must be short, natural, and different from each other. Use only facts from the product data. '
            . 'Do not invent features, certifications, verified purchase claims, order claims, or medical claims. '
            . 'No emojis, no "??", no "???", no Chinese or corrupted characters, no ALL CAPS marketing, no SEO stuffing. '
            . 'Do not mention price, shipping, SKU, or competitors. Do not rewrite the product listing.';

        $payload = array(
            'title' => 'Generate ' . $count . ' JSON product reviews for: ' . $title,
            'country_id' => isset($context['country_id']) ? (int) $context['country_id'] : 0,
            'country' => isset($context['country_name']) ? (string) $context['country_name'] : '',
            'country_code' => isset($context['country_code']) ? (string) $context['country_code'] : '',
            'currency' => isset($context['currency']) ? (string) $context['currency'] : '',
            'currency_symbol' => isset($context['currency_symbol']) ? (string) $context['currency_symbol'] : '',
            'details' => $instruction . "\n\nProduct data:\n" . $facts,
            'task' => 'product_reviews',
            'mode' => 'product_reviews',
            'review_count' => $count,
            'language' => $language,
            'instruction' => $instruction,
        );

        $result = $this->http_post($this->api_url(), $payload, 90, array(
            'Content-Type: application/json',
            'X-API-Key: ' . $this->api_key(),
        ));
        if ($result['ok'] === false) {
            $this->lastError = $this->extract_api_error($result['body'], $result['status']);
            return null;
        }

        $decoded = json_decode($result['body'], true);
        if (!is_array($decoded)) {
            $this->lastError = 'AI agent returned invalid JSON.';
            return null;
        }
        $rows = $this->extract_review_rows($decoded);
        return $this->validate_reviews($rows, $count, $language);
    }

    protected function extract_review_rows($decoded)
    {
        if (!is_array($decoded)) {
            return null;
        }
        $candidates = array($decoded);
        if (!empty($decoded['data']) && is_array($decoded['data'])) {
            $candidates[] = $decoded['data'];
            if (!empty($decoded['data']['data']) && is_array($decoded['data']['data'])) {
                $candidates[] = $decoded['data']['data'];
            }
        }
        foreach ($candidates as $block) {
            $rows = $this->reviews_from_block($block);
            if ($rows !== null) {
                return $rows;
            }
        }
        return null;
    }

    protected function reviews_from_block($block)
    {
        if (!is_array($block)) {
            if (is_string($block)) {
                return $this->parse_reviews_json($block);
            }
            return null;
        }
        if (isset($block['reviews']) && is_array($block['reviews'])) {
            return $block['reviews'];
        }
        $keys = array('long_detail', 'short_detail', 'details', 'content', 'text', 'output', 'message', 'title');
        foreach ($keys as $key) {
            if (!empty($block[$key]) && is_string($block[$key])) {
                $parsed = $this->parse_reviews_json($block[$key]);
                if ($parsed !== null) {
                    return $parsed;
                }
            }
        }
        return null;
    }

    protected function parse_reviews_json($text)
    {
        $text = trim((string) $text);
        if ($text === '') {
            return null;
        }
        if (preg_match('/```(?:json)?\s*([\s\S]*?)```/i', $text, $m)) {
            $text = trim($m[1]);
        }
        $decoded = json_decode($text, true);
        if (!is_array($decoded)) {
            $startObj = strpos($text, '{');
            $startArr = strpos($text, '[');
            $start = false;
            if ($startObj !== false && ($startArr === false || $startObj < $startArr)) {
                $start = $startObj;
                $end = strrpos($text, '}');
            } elseif ($startArr !== false) {
                $start = $startArr;
                $end = strrpos($text, ']');
            }
            if ($start !== false && $end !== false && $end > $start) {
                $decoded = json_decode(substr($text, $start, $end - $start + 1), true);
            }
        }
        if (!is_array($decoded)) {
            return null;
        }
        if (isset($decoded['reviews']) && is_array($decoded['reviews'])) {
            return $decoded['reviews'];
        }
        if (array_keys($decoded) === range(0, count($decoded) - 1)) {
            $first = reset($decoded);
            if (is_array($first) && (isset($first['text']) || isset($first['content']) || isset($first['review']))) {
                return $decoded;
            }
        }
        return null;
    }

    protected function validate_reviews($rows, $count, $language = 'en')
    {
        $count = (int) $count;
        if (!is_array($rows)) {
            $this->lastError = 'AI did not return a reviews array.';
            return null;
        }
        if ($count > 0 && count($rows) !== $count) {
            $this->lastError = 'AI returned ' . count($rows) . ' reviews, but ' . $count . ' were requested.';
            return null;
        }
        if ($count < 1 && (count($rows) < 1 || count($rows) > 50)) {
            $this->lastError = 'AI returned an invalid number of reviews.';
            return null;
        }
        $out = array();
        $seen = array();
        foreach ($rows as $i => $row) {
            if (!is_array($row)) {
                $this->lastError = 'AI review ' . ($i + 1) . ' is invalid.';
                return null;
            }
            $text = $this->first_string($row, array('text', 'content', 'review', 'body', 'comment'));
            $text = trim(preg_replace('/\s+/u', ' ', $text));
            if ($text === '') {
                $this->lastError = 'AI review ' . ($i + 1) . ' has empty text.';
                return null;
            }
            if (strpos($text, '??') !== false || $this->has_broken_text($text) || $this->has_cjk($text) || $this->has_emoji($text) || $this->has_all_caps_marketing($text)) {
                $this->lastError = 'AI review ' . ($i + 1) . ' contains invalid characters or marketing text.';
                return null;
            }
            $rating = 0;
            if (isset($row['rating']) && is_numeric($row['rating'])) {
                $rating = (int) $row['rating'];
            } elseif (isset($row['stars']) && is_numeric($row['stars'])) {
                $rating = (int) $row['stars'];
            }
            if ($rating < 1 || $rating > 5) {
                $this->lastError = 'AI review ' . ($i + 1) . ' has an invalid rating.';
                return null;
            }
            $name = $this->first_string($row, array('name', 'customer_name', 'reviewer', 'author'));
            $name = trim(preg_replace('/\s+/u', ' ', $name));
            if ($name === '' || $this->has_broken_text($name) || $this->has_cjk($name) || $this->has_emoji($name)) {
                $name = $this->fallback_reviewer_name($language, $i);
            }
            $title = $this->first_string($row, array('title', 'headline'));
            $title = trim(preg_replace('/\s+/u', ' ', $title));
            if ($title !== '' && ($this->has_broken_text($title) || $this->has_cjk($title) || $this->has_emoji($title))) {
                $title = '';
            }
            $key = function_exists('mb_strtolower') ? mb_strtolower($text, 'UTF-8') : strtolower($text);
            if (isset($seen[$key])) {
                $this->lastError = 'AI returned duplicate review text.';
                return null;
            }
            $seen[$key] = true;
            $out[] = array(
                'rating' => $rating,
                'name' => $this->clip($name, 150),
                'title' => $this->clip($title, 255),
                'text' => $text,
                'created_at' => date('Y-m-d H:i:s', time() - (($i + 1) * 86400)),
            );
        }
        return $out;
    }

    protected function has_cjk($text)
    {
        return (bool) preg_match('/[\x{3400}-\x{9FFF}\x{F900}-\x{FAFF}\x{3040}-\x{30FF}\x{AC00}-\x{D7AF}]/u', (string) $text);
    }

    protected function has_all_caps_marketing($text)
    {
        $letters = preg_replace('/[^\p{L}]+/u', '', (string) $text);
        if ($letters === '' || (function_exists('mb_strlen') ? mb_strlen($letters, 'UTF-8') : strlen($letters)) < 12) {
            return false;
        }
        $upper = function_exists('mb_strtoupper') ? mb_strtoupper($letters, 'UTF-8') : strtoupper($letters);
        return $letters === $upper;
    }

    protected function fallback_reviewer_name($language, $index)
    {
        $names = array(
            'sv' => array('Anna', 'Erik', 'Lisa', 'Johan', 'Maria', 'Anders', 'Emma', 'Lars', 'Sofia', 'Mikael'),
            'en' => array('Sarah', 'James', 'Emily', 'Daniel', 'Olivia', 'Tom', 'Hannah', 'Chris', 'Grace', 'Ben'),
            'de' => array('Anna', 'Lukas', 'Laura', 'Jonas', 'Mia', 'Paul'),
            'fr' => array('Camille', 'Lucas', 'Lea', 'Hugo', 'Chloe', 'Louis'),
            'es' => array('Lucia', 'Carlos', 'Maria', 'Diego', 'Sofia', 'Pablo'),
            'it' => array('Giulia', 'Luca', 'Francesca', 'Marco', 'Elena', 'Andrea'),
            'nl' => array('Emma', 'Daan', 'Sophie', 'Lars', 'Eva', 'Tim'),
            'nb' => array('Ingrid', 'Ole', 'Nora', 'Jonas', 'Maja', 'Erik'),
            'da' => array('Freja', 'Mikkel', 'Ida', 'Anders', 'Emma', 'Lars'),
            'fi' => array('Aino', 'Mikko', 'Emma', 'Jussi', 'Sofia', 'Antti'),
            'pl' => array('Anna', 'Piotr', 'Kasia', 'Tomek', 'Ola', 'Marek'),
            'pt' => array('Ana', 'Pedro', 'Mariana', 'Joao', 'Beatriz', 'Miguel'),
        );
        $language = strtolower(trim((string) $language));
        $pool = isset($names[$language]) ? $names[$language] : $names['en'];
        return $pool[$index % count($pool)];
    }

    protected function request_agent($product, $context)
    {
        $title = !empty($context['form_title']) ? (string) $context['form_title'] : (isset($product->name) ? (string) $product->name : '');
        $details = '';
        if (!empty($context['form_long'])) {
            $details = $this->plain_text($context['form_long']);
        } elseif (!empty($product->details)) {
            $details = $this->plain_text($product->details);
        } elseif (!empty($context['form_short'])) {
            $details = $this->plain_text($context['form_short']);
        } elseif (!empty($product->short_details)) {
            $details = $this->plain_text($product->short_details);
        }

        $payload = array(
            'title' => $title,
            'country_id' => isset($context['country_id']) ? (int) $context['country_id'] : 0,
            'country' => isset($context['country_name']) ? (string) $context['country_name'] : '',
            'country_code' => isset($context['country_code']) ? (string) $context['country_code'] : '',
            'currency' => isset($context['currency']) ? (string) $context['currency'] : '',
            'currency_symbol' => isset($context['currency_symbol']) ? (string) $context['currency_symbol'] : '',
            'details' => $details,
            'language' => isset($context['language']) ? (string) $context['language'] : 'en',
            'language_name' => $this->language_name(isset($context['language']) ? $context['language'] : 'en'),
            'include_english' => true,
            'instruction' => $this->bilingual_instruction(isset($context['language']) ? $context['language'] : 'en'),
            'task' => 'product_rewrite',
            'mode' => 'product_rewrite',
        );
        $reviewCount = isset($context['review_count']) ? (int) $context['review_count'] : 0;
        if ($reviewCount > 0) {
            $payload['review_count'] = $reviewCount;
            $payload['include_reviews'] = true;
        }

        $result = $this->http_post($this->api_url(), $payload, 90, array(
            'Content-Type: application/json',
            'X-API-Key: ' . $this->api_key(),
        ));
        if ($result['ok'] === false) {
            $this->lastError = $this->extract_api_error($result['body'], $result['status']);
            return null;
        }

        $decoded = json_decode($result['body'], true);
        if (!is_array($decoded)) {
            $this->lastError = 'AI agent returned invalid JSON.';
            return null;
        }
        $ok = !empty($decoded['status']) || !empty($decoded['ok']) || !empty($decoded['success']);
        $data = array();
        if (!empty($decoded['data']) && is_array($decoded['data'])) {
            $data = $decoded['data'];
            $ok = true;
        } else {
            $data = $decoded;
        }
        if (!$ok) {
            $this->lastError = $this->extract_api_error($result['body'], $result['status']);
            return null;
        }

        $mapped = $this->map_agent_to_product($data);
        if (!$mapped) {
            $this->lastError = 'AI agent response is missing required content fields.';
            return null;
        }
        $language = isset($context['language']) ? (string) $context['language'] : 'en';
        $reviewRows = $this->extract_review_rows($decoded);
        if ($reviewRows === null) {
            $reviewRows = $this->extract_review_rows($data);
        }
        if ($reviewRows) {
            $savedError = $this->lastError;
            $wanted = isset($context['review_count']) ? (int) $context['review_count'] : 0;
            $validReviews = $this->validate_reviews($reviewRows, $wanted > 0 ? $wanted : 0, $language);
            if (!$validReviews && $wanted > 0) {
                $validReviews = $this->validate_reviews($reviewRows, 0, $language);
            }
            if ($validReviews) {
                $mapped['reviews'] = $validReviews;
            }
            $this->lastError = $savedError;
        }
        return $mapped;
    }

    /**
     * Live /api/v1/agent data keys → products table columns:
     * title            → name
     * short_detail     → short_details
     * long_detail      → details
     * seo_title        → seo_title
     * seo_description  → seo_description
     * seo_keywords     → seo_keywords
     * seo_slug         → slug
     * title_en         → name_en
     * short_detail_en  → short_details_en
     * long_detail_en   → details_en
     * seo_title_en     → seo_title_en
     * seo_description_en → seo_description_en
     * seo_keywords_en  → seo_keywords_en
     * category         → {id,name} existing category, or name to create
     * sub_category     → {id,name} existing subcategory, or name to create
     * test             → ignored
     */
    protected function map_agent_to_product($data)
    {
        if (!is_array($data)) {
            return null;
        }

        $title = $this->first_string($data, array('title', 'name'));
        $short = $this->first_string($data, array('short_detail', 'short_details'));
        $long = $this->first_string($data, array('long_detail', 'details', 'long_details'));
        $seoTitle = $this->first_string($data, array('seo_title'));
        $seoDescription = $this->first_string($data, array('seo_description'));
        $seoKeywords = $this->first_string($data, array('seo_keywords'));
        $slug = $this->first_string($data, array('seo_slug', 'slug'));

        if ($title === '' || $short === '' || $long === '' || $seoTitle === '' || $seoDescription === '' || $seoKeywords === '' || $slug === '') {
            return null;
        }

        $category = $this->named_ref($data, array('category', 'category_id', 'category_name'));
        $sub = $this->named_ref($data, array('sub_category', 'subcategory', 'subcategory_id', 'sub_category_name', 'subcategory_name'));

        $mapped = array(
            'title' => $this->clip($title, 255),
            'short_detail' => $short,
            'long_detail' => $long,
            'seo_title' => $this->clip($seoTitle, 255),
            'seo_description' => $seoDescription,
            'seo_keywords' => $this->clip($seoKeywords, 255),
            'seo_slug' => $slug,
            'name' => $this->clip($title, 255),
            'short_details' => $short,
            'details' => $long,
            'slug' => $slug,
            'category' => $category,
            'sub_category' => $sub,
        );
        $this->merge_english_fields($mapped, $data);
        return $mapped;
    }

    public function bilingual_instruction($language = 'sv')
    {
        $language = strtolower(trim((string) $language));
        if ($language === '') {
            $language = 'sv';
        }
        $nativeName = $this->language_name($language);
        return <<<TXT
Write the product listing twice in ONE JSON object. Do not make two API calls.

Native market language is {$nativeName} ({$language}). English is always the second language.

Return ONLY JSON with these string keys:
title, short_detail, long_detail, seo_title, seo_description, seo_keywords, seo_slug,
title_en, short_detail_en, long_detail_en, seo_title_en, seo_description_en, seo_keywords_en, seo_slug_en

Native fields ({$nativeName}):
- title, short_detail, long_detail, seo_title, seo_description, seo_keywords, seo_slug

English fields (must be real English, not empty, not a word-for-word paste of the native text):
- title_en, short_detail_en, long_detail_en, seo_title_en, seo_description_en, seo_keywords_en, seo_slug_en

Rules for both languages
- Same facts, same pack quantity, same product. Do not add extras in English that are missing in {$nativeName}.
- Rewrite supplier SEO. No price, SKU, shipping days, medical claims, emojis, or ALL CAPS.
- short_detail / short_detail_en: 2–4 short HTML paragraphs or one list. Tags: p, br, ul, ol, li, strong, em.
- long_detail / long_detail_en: structured HTML with h3 headings. Tags: p, br, ul, ol, li, strong, em, h3, h4.
- seo_slug and seo_slug_en: lowercase ASCII letters, numbers, hyphens only.
- If the native language is already English, still fill the *_en keys with the same English copy.

Never wrap JSON in markdown. Never add commentary.
TXT;
    }

    protected function merge_english_fields(&$mapped, $data)
    {
        $titleEn = $this->first_string($data, array('title_en', 'name_en'));
        $shortEn = $this->first_string($data, array('short_detail_en', 'short_details_en'));
        $longEn = $this->first_string($data, array('long_detail_en', 'details_en', 'long_details_en'));
        $seoTitleEn = $this->first_string($data, array('seo_title_en'));
        $seoDescriptionEn = $this->first_string($data, array('seo_description_en'));
        $seoKeywordsEn = $this->first_string($data, array('seo_keywords_en'));
        if ($titleEn === '' && $shortEn === '' && $longEn === '') {
            return;
        }
        if ($titleEn !== '') {
            $mapped['title_en'] = $this->clip($titleEn, 255);
            $mapped['name_en'] = $mapped['title_en'];
        }
        if ($shortEn !== '') {
            if (strip_tags($shortEn) === $shortEn) {
                $shortEn = '<p>' . nl2br(htmlspecialchars($shortEn, ENT_QUOTES, 'UTF-8'), false) . '</p>';
            }
            $mapped['short_detail_en'] = function_exists('ec_sanitize_product_html')
                ? ec_sanitize_product_html($shortEn)
                : $shortEn;
            $mapped['short_details_en'] = $mapped['short_detail_en'];
        }
        if ($longEn !== '') {
            $mapped['long_detail_en'] = function_exists('ec_sanitize_product_html')
                ? ec_sanitize_product_html($longEn)
                : $longEn;
            $mapped['details_en'] = $mapped['long_detail_en'];
        }
        if ($seoTitleEn !== '') {
            $mapped['seo_title_en'] = $this->clip($seoTitleEn, 255);
        }
        if ($seoDescriptionEn !== '') {
            $mapped['seo_description_en'] = $seoDescriptionEn;
        }
        if ($seoKeywordsEn !== '') {
            $mapped['seo_keywords_en'] = $this->clip($seoKeywordsEn, 255);
        }
    }

    protected function named_ref($data, $keys)
    {
        $out = array('id' => 0, 'name' => '');
        if (!is_array($data)) {
            return $out;
        }
        foreach ($keys as $key) {
            if (!array_key_exists($key, $data)) {
                continue;
            }
            $value = $data[$key];
            if (is_array($value)) {
                foreach (array('id', 'category_id', 'subcategory_id', 'sub_category_id') as $idKey) {
                    if (isset($value[$idKey]) && is_numeric($value[$idKey]) && (int) $value[$idKey] > 0) {
                        $out['id'] = (int) $value[$idKey];
                        break;
                    }
                }
                foreach (array('name', 'title', 'label') as $nameKey) {
                    if (isset($value[$nameKey]) && is_string($value[$nameKey]) && trim($value[$nameKey]) !== '') {
                        $out['name'] = trim($value[$nameKey]);
                        break;
                    }
                }
            } elseif (is_numeric($value) && (int) $value > 0 && preg_match('/id$/i', $key)) {
                $out['id'] = (int) $value;
            } elseif (is_string($value)) {
                $value = trim($value);
                if ($value === '') {
                    continue;
                }
                if (preg_match('/^\d+$/', $value) && preg_match('/id$/i', $key)) {
                    $out['id'] = (int) $value;
                } else {
                    $out['name'] = $value;
                }
            }
            if ($out['id'] > 0 || $out['name'] !== '') {
                return $out;
            }
        }
        return $out;
    }

    protected function first_string($data, $keys)
    {
        foreach ($keys as $key) {
            if (isset($data[$key]) && is_string($data[$key]) && trim($data[$key]) !== '') {
                return trim($data[$key]);
            }
        }
        return '';
    }

    protected function clip($value, $max)
    {
        $value = trim((string) $value);
        if (function_exists('mb_substr')) {
            return mb_substr($value, 0, (int) $max);
        }
        return substr($value, 0, (int) $max);
    }

    protected function build_user_prompt($product, $language, $countryName, $quantity, $context)
    {
        $payload = array(
            'language_code' => $language,
            'language_name' => $this->language_name($language),
            'country' => $countryName,
            'selected_quantity' => $quantity > 0 ? $quantity : null,
            'package_hint' => $quantity > 0 ? ($quantity . '-pack') : null,
            'current_title' => isset($product->name) ? (string) $product->name : '',
            'current_short_detail' => isset($product->short_details) ? $this->plain_text($product->short_details) : '',
            'current_long_detail' => isset($product->details) ? $this->plain_text($product->details) : '',
            'brand' => isset($product->brand) ? (string) $product->brand : '',
            'sku' => isset($product->sku) ? (string) $product->sku : '',
        );
        if (!empty($context['form_short'])) {
            $payload['current_short_detail'] = $this->plain_text($context['form_short']);
        }
        if (!empty($context['form_long'])) {
            $payload['current_long_detail'] = $this->plain_text($context['form_long']);
        }
        if (!empty($context['form_title'])) {
            $payload['current_title'] = (string) $context['form_title'];
            if ($quantity < 1) {
                $quantity = $this->extract_quantity($payload['current_title']);
                $payload['selected_quantity'] = $quantity > 0 ? $quantity : null;
                $payload['package_hint'] = $quantity > 0 ? ($quantity . '-pack') : null;
            }
        }

        $json = json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        return "Rewrite the product content below.\n"
            . "Write every field in {$this->language_name($language)} ({$language}).\n"
            . "Return JSON only.\n\n"
            . $json;
    }

    protected function generate_json($userPrompt)
    {
        $cacheName = $this->ensure_cache();
        $body = array(
            'contents' => array(
                array(
                    'role' => 'user',
                    'parts' => array(array('text' => $userPrompt)),
                ),
            ),
            'generationConfig' => array(
                'temperature' => 0.35,
                'maxOutputTokens' => 4096,
                'responseMimeType' => 'application/json',
            ),
        );
        if ($cacheName) {
            $body['cachedContent'] = $cacheName;
        } else {
            $body['systemInstruction'] = array(
                'parts' => array(array('text' => $this->system_instruction())),
            );
        }

        $url = 'https://generativelanguage.googleapis.com/v1beta/models/'
            . rawurlencode($this->model_name())
            . ':generateContent?key=' . rawurlencode($this->api_key());

        $result = $this->http_post($url, $body, 90);
        if ($result['ok'] === false) {
            if ($cacheName && $this->is_cache_error($result['body'])) {
                $this->clear_cache();
                $body['systemInstruction'] = array(
                    'parts' => array(array('text' => $this->system_instruction())),
                );
                unset($body['cachedContent']);
                $result = $this->http_post($url, $body, 90);
            }
        }
        if ($result['ok'] === false) {
            $this->lastError = $this->extract_api_error($result['body'], $result['status']);
            return null;
        }

        $decoded = json_decode($result['body'], true);
        if (!is_array($decoded)) {
            $this->lastError = 'Gemini returned invalid JSON.';
            return null;
        }
        $text = '';
        if (!empty($decoded['candidates'][0]['content']['parts'])) {
            foreach ($decoded['candidates'][0]['content']['parts'] as $part) {
                if (!empty($part['text'])) {
                    $text .= $part['text'];
                }
            }
        }
        $text = trim($text);
        if ($text === '') {
            $this->lastError = 'Gemini returned an empty response.';
            return null;
        }
        return $text;
    }

    protected function ensure_cache()
    {
        $existing = trim((string) platform_setting('gemini_cache_name', ''));
        $version = trim((string) platform_setting('gemini_cache_version', ''));
        $expires = trim((string) platform_setting('gemini_cache_expires', ''));
        if ($existing !== '' && $version === self::PROMPT_VERSION && $expires !== '' && strtotime($expires) > time() + 120) {
            return $existing;
        }

        $url = 'https://generativelanguage.googleapis.com/v1beta/cachedContents?key=' . rawurlencode($this->api_key());
        $payload = array(
            'model' => 'models/' . $this->model_name(),
            'displayName' => 'zenvello-' . self::PROMPT_VERSION,
            'systemInstruction' => array(
                'parts' => array(array('text' => $this->system_instruction())),
            ),
            'ttl' => '3600s',
        );
        $result = $this->http_post($url, $payload, 30);
        if ($result['ok'] === false) {
            return '';
        }
        $decoded = json_decode($result['body'], true);
        if (!is_array($decoded) || empty($decoded['name'])) {
            return '';
        }
        $expireAt = date('Y-m-d H:i:s', time() + 3300);
        if (!empty($decoded['expireTime'])) {
            $ts = strtotime($decoded['expireTime']);
            if ($ts) {
                $expireAt = date('Y-m-d H:i:s', $ts);
            }
        }
        $this->write_setting('gemini_cache_name', $decoded['name']);
        $this->write_setting('gemini_cache_version', self::PROMPT_VERSION);
        $this->write_setting('gemini_cache_expires', $expireAt);
        return $decoded['name'];
    }

    protected function clear_cache()
    {
        $this->write_setting('gemini_cache_name', '');
        $this->write_setting('gemini_cache_expires', '');
    }

    protected function parse_json($raw)
    {
        $raw = trim((string) $raw);
        if (preg_match('/^```(?:json)?\s*([\s\S]*?)```$/i', $raw, $m)) {
            $raw = trim($m[1]);
        }
        $data = json_decode($raw, true);
        if (!is_array($data)) {
            $this->lastError = 'AI response was not valid JSON.';
            return null;
        }
        return $data;
    }

    public function validate($data, $product, $quantity = 0)
    {
        foreach (self::REQUIRED_FIELDS as $field) {
            if (!isset($data[$field]) || !is_string($data[$field])) {
                $this->lastError = 'AI response is missing ' . $field . '.';
                return null;
            }
            $data[$field] = trim($data[$field]);
            if ($data[$field] === '') {
                $this->lastError = 'AI response field ' . $field . ' is empty.';
                return null;
            }
            if ($this->has_broken_text($data[$field])) {
                $this->lastError = 'AI response contains broken characters in ' . $field . '.';
                return null;
            }
            if ($this->has_emoji($data[$field])) {
                $this->lastError = 'AI response contains emoji in ' . $field . '.';
                return null;
            }
        }

        if (strip_tags($data['short_detail']) === $data['short_detail']) {
            $data['short_detail'] = '<p>' . nl2br(htmlspecialchars($data['short_detail'], ENT_QUOTES, 'UTF-8'), false) . '</p>';
        }

        $data['short_detail'] = function_exists('ec_sanitize_product_html')
            ? ec_sanitize_product_html($data['short_detail'])
            : $data['short_detail'];
        $data['long_detail'] = function_exists('ec_sanitize_product_html')
            ? ec_sanitize_product_html($data['long_detail'])
            : $data['long_detail'];
        if (trim(strip_tags($data['short_detail'])) === '' || trim(strip_tags($data['long_detail'])) === '') {
            $this->lastError = 'AI description HTML was empty after sanitizing.';
            return null;
        }

        $slug = function_exists('ec_ascii_slug')
            ? ec_ascii_slug($data['seo_slug'], '')
            : strtolower(preg_replace('/[^a-z0-9]+/i', '-', $data['seo_slug']));
        $slug = trim($slug, '-');
        if ($slug === '' || !preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/', $slug)) {
            $slug = function_exists('ec_ascii_slug')
                ? ec_ascii_slug($data['title'], 'product')
                : 'product';
        }
        if ($slug === '' || !preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/', $slug)) {
            $this->lastError = 'AI slug is not valid.';
            return null;
        }
        $data['seo_slug'] = $slug;

        $source = $this->source_text($product);
        $combined = $data['title'] . ' ' . $data['short_detail'] . ' ' . $data['long_detail'];
        if ($this->has_unsupported_claim($combined, $source)) {
            $this->lastError = 'AI response includes unsupported claims.';
            return null;
        }

        if ($quantity > 0) {
            if (!preg_match('/\b' . preg_quote((string) $quantity, '/') . '\b/', $data['title'])) {
                $this->lastError = 'AI title does not include the selected package quantity (' . $quantity . ').';
                return null;
            }
            if (preg_match_all('/\b(\d{1,4})\s*[-\/]?\s*(pack|packs|pcs|pc|stk|pieces?)\b/iu', $combined, $matches)) {
                foreach ($matches[1] as $found) {
                    if ((int) $found !== (int) $quantity) {
                        $this->lastError = 'AI content mentions a conflicting package quantity.';
                        return null;
                    }
                }
            }
        }

        $out = array(
            'title' => $this->clip($data['title'], 255),
            'short_detail' => $data['short_detail'],
            'long_detail' => $data['long_detail'],
            'seo_title' => $this->clip($data['seo_title'], 255),
            'seo_description' => $data['seo_description'],
            'seo_keywords' => $this->clip($data['seo_keywords'], 255),
            'seo_slug' => $data['seo_slug'],
            'name' => $this->clip($data['title'], 255),
            'short_details' => $data['short_detail'],
            'details' => $data['long_detail'],
            'slug' => $data['seo_slug'],
        );
        if (!empty($data['category'])) {
            $out['category'] = $data['category'];
        }
        if (!empty($data['sub_category'])) {
            $out['sub_category'] = $data['sub_category'];
        }
        if (!empty($data['reviews']) && is_array($data['reviews'])) {
            $out['reviews'] = $data['reviews'];
        }
        foreach (array('title_en', 'name_en', 'short_detail_en', 'short_details_en', 'long_detail_en', 'details_en', 'seo_title_en', 'seo_description_en', 'seo_keywords_en') as $enKey) {
            if (!empty($data[$enKey]) && is_string($data[$enKey])) {
                $out[$enKey] = $data[$enKey];
            }
        }
        if (!empty($out['title_en']) && empty($out['name_en'])) {
            $out['name_en'] = $out['title_en'];
        }
        if (!empty($out['short_detail_en']) && empty($out['short_details_en'])) {
            $out['short_details_en'] = $out['short_detail_en'];
        }
        if (!empty($out['long_detail_en']) && empty($out['details_en'])) {
            $out['details_en'] = $out['long_detail_en'];
        }
        return $out;
    }

    protected function source_text($product)
    {
        $parts = array();
        foreach (array('name', 'short_details', 'details', 'description', 'brand') as $key) {
            if (!empty($product->$key)) {
                $parts[] = $this->plain_text($product->$key);
            }
        }
        return strtolower(implode(' ', $parts));
    }

    protected function has_unsupported_claim($text, $source)
    {
        $needles = array(
            'fda approved',
            'clinically proven',
            'medically proven',
            'cures',
            'guaranteed to',
            '100% guaranteed',
            'best in the world',
            'number 1 in the world',
            'medical device',
        );
        $hay = strtolower($this->plain_text($text));
        foreach ($needles as $needle) {
            if (strpos($hay, $needle) !== false && strpos($source, $needle) === false) {
                return true;
            }
        }
        return false;
    }

    protected function has_broken_text($text)
    {
        if (strpos($text, "\xEF\xBF\xBD") !== false) {
            return true;
        }
        if (preg_match('/\?{3,}/', $text)) {
            return true;
        }
        if (function_exists('mb_check_encoding') && !mb_check_encoding($text, 'UTF-8')) {
            return true;
        }
        return false;
    }

    protected function has_emoji($text)
    {
        return (bool) preg_match('/[\x{1F300}-\x{1FAFF}\x{2600}-\x{27BF}\x{FE0F}]/u', $text);
    }

    protected function plain_text($html)
    {
        $html = html_entity_decode((string) $html, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $text = trim(preg_replace('/\s+/', ' ', strip_tags($html)));
        return $text;
    }

    protected function language_names()
    {
        return array(
            'sv' => 'Swedish',
            'en' => 'English',
            'it' => 'Italian',
            'de' => 'German',
            'fr' => 'French',
            'es' => 'Spanish',
            'nl' => 'Dutch',
            'nb' => 'Norwegian',
            'da' => 'Danish',
            'fi' => 'Finnish',
            'pl' => 'Polish',
            'pt' => 'Portuguese',
        );
    }

    protected function system_instruction()
    {
        return <<<'PROMPT'
You are the ZENvello product content writer for a multi-country ecommerce platform.

Return ONLY a JSON object with exactly these string keys:
title, short_detail, long_detail, seo_title, seo_description, seo_keywords, seo_slug

Never wrap the JSON in markdown. Never add commentary.

Language
- Write every field in the language named in the user payload (language_name / language_code).
- Do not default to Swedish. Sweden uses Swedish only when the payload says Swedish/sv. United Kingdom and similar markets use English. Italy uses Italian. Match the given country.
- Use natural native phrasing, not machine-translated English word order.

Package quantity is the source of truth
- If selected_quantity is set (for example 4), the product is that pack size.
- The title MUST include that quantity clearly (for example "4-pack" in the market language).
- Do not mention other pack sizes, bundles, or conflicting counts.
- Do not invent a quantity when none is provided.

Facts
- Rewrite; do not copy supplier SEO titles, meta descriptions, or keyword lists.
- Do not invent certifications, medical claims, FDA approval, clinical proof, "cures", "guaranteed results", or "best in the world".
- Do not invent materials, battery life, dimensions, or safety ratings that are not supported by the source text.
- Do not include price, currency, SKU, stock, shipping days, or supplier URLs.
- Do not mention competitors.

Style
- Title: retail product name, specific, readable, not stuffed with keywords.
- short_detail: 2 to 4 short HTML paragraphs or one short list. About 400 to 900 characters of visible text. No h1. Allowed tags: p, br, ul, ol, li, strong, em.
- long_detail: structured HTML with h3 section headings and paragraphs. About 1200 to 3500 characters of visible text. Allowed tags: p, br, ul, ol, li, strong, em, h3, h4. No scripts, forms, iframes, or inline event handlers.
- Valid nested HTML only. Close every tag.

SEO
- seo_title: under 60 characters, natural, includes the product idea and quantity when relevant.
- seo_description: under 160 characters, one or two sentences, no keyword stuffing.
- seo_keywords: 5 to 10 comma-separated phrases, no repetition, no stuffing.
- seo_slug: lowercase ASCII letters, numbers, hyphens only. Derived from the rewritten title. No stop-word spam.

Forbidden
- Emojis
- Replacement characters or broken Unicode
- Sequences of question marks such as ????
- ALL CAPS shouting
- Duplicate sentences
- Copying the supplier title verbatim

If source details are thin, write honest, generic-but-useful copy around the product type and quantity. Do not fabricate specifics.
PROMPT;
    }

    protected function http_post($url, $payload, $timeout = 60, $headers = null)
    {
        if (!is_array($headers) || !$headers) {
            $headers = array('Content-Type: application/json');
        }
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        curl_setopt($ch, CURLOPT_TIMEOUT, (int) $timeout);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 15);
        $body = curl_exec($ch);
        $status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $err = curl_error($ch);
        curl_close($ch);
        if ($body === false) {
            return array('ok' => false, 'status' => 0, 'body' => $err);
        }
        if ($status < 200 || $status >= 300) {
            return array('ok' => false, 'status' => $status, 'body' => $body);
        }
        return array('ok' => true, 'status' => $status, 'body' => $body);
    }

    protected function extract_api_error($body, $status)
    {
        $decoded = json_decode((string) $body, true);
        if (is_array($decoded)) {
            foreach (array('error', 'message', 'detail') as $key) {
                if (!empty($decoded[$key]) && is_string($decoded[$key])) {
                    return 'AI agent error: ' . $decoded[$key];
                }
            }
            if (!empty($decoded['error']['message'])) {
                return 'AI agent error: ' . $decoded['error']['message'];
            }
        }
        $trim = trim(strip_tags((string) $body));
        if ($trim !== '') {
            return 'AI agent HTTP ' . (int) $status . ': ' . substr($trim, 0, 240);
        }
        return 'AI agent request failed (HTTP ' . (int) $status . ').';
    }

    protected function write_setting($key, $value)
    {
        if (!$this->CI->db->table_exists('platform_settings')) {
            return;
        }
        $exists = $this->CI->db->where('setting_key', $key)->get('platform_settings')->row();
        if ($exists) {
            $this->CI->db->where('setting_key', $key)->update('platform_settings', array(
                'setting_value' => (string) $value,
            ));
            return;
        }
        $this->CI->db->insert('platform_settings', array(
            'setting_key' => $key,
            'setting_value' => (string) $value,
        ));
    }
}
