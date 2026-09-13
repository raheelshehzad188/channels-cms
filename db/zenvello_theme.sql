USE `ecommerce`;

INSERT INTO `themes` (`name`, `slug`, `description`, `status`)
SELECT 'ZENVello', 'zenvello', 'Shop smart, live better — modern multi-category storefront.', 1
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `themes` WHERE `slug` = 'zenvello');

INSERT INTO `theme_setting_fields` (`theme_id`, `field_key`, `field_label`, `field_type`, `is_required`, `default_value`, `sort_order`)
SELECT t.id, v.field_key, v.field_label, v.field_type, v.is_required, v.default_value, v.sort_order
FROM `themes` t
JOIN (
    SELECT 'logo' AS field_key, 'Header Logo' AS field_label, 'image' AS field_type, 0 AS is_required, '' AS default_value, 1 AS sort_order
    UNION ALL SELECT 'primary_color', 'Primary Color', 'color', 1, '#ffd814', 2
    UNION ALL SELECT 'secondary_color', 'Secondary Color', 'color', 1, '#111111', 3
    UNION ALL SELECT 'footer_text', 'Footer Text', 'text', 1, 'ZENVello. All rights reserved.', 4
    UNION ALL SELECT 'footer_about', 'Footer About', 'text', 0, 'Your one-stop shop for quality products at the best prices. Shop smart, live better with ZENVello.', 5
    UNION ALL SELECT 'hero_title', 'Hero Title', 'text', 0, 'Make Your Home Feel Like You', 6
    UNION ALL SELECT 'hero_subtitle', 'Hero Subtitle', 'text', 0, 'Discover smart, stylish and affordable products for a better everyday life.', 7
    UNION ALL SELECT 'category_hero_default', 'Default Category Hero Image', 'image', 0, '', 8
    UNION ALL SELECT 'promo_text', 'Top Bar Promo', 'text', 0, 'Free Shipping on Orders Over £50', 9
    UNION ALL SELECT 'address', 'Address', 'text', 0, '123 High Street, London', 10
    UNION ALL SELECT 'email', 'Email', 'text', 0, 'hello@zenvello.ecommerce.test', 11
    UNION ALL SELECT 'phone', 'Phone', 'text', 0, '+44 20 1234 5678', 12
) v
WHERE t.slug = 'zenvello'
  AND NOT EXISTS (
      SELECT 1 FROM `theme_setting_fields` f
      WHERE f.theme_id = t.id AND f.field_key = v.field_key
  );

INSERT INTO `stores` (`name`, `domain`, `email`, `password`, `owner_name`, `theme_id`, `status`)
SELECT 'ZENVello Store', 'zenvello.ecommerce.test', 'owner@zenvello.ecommerce.test', MD5('zenvello'), 'ZENVello Owner', t.id, 1
FROM `themes` t
WHERE t.slug = 'zenvello'
  AND NOT EXISTS (SELECT 1 FROM `stores` WHERE `domain` = 'zenvello.ecommerce.test');

UPDATE `stores` s
JOIN `themes` t ON t.slug = 'zenvello'
SET s.theme_id = t.id,
    s.email = IF(s.email = '' OR s.email IS NULL, 'owner@zenvello.ecommerce.test', s.email),
    s.password = IF(s.password = '' OR s.password IS NULL, MD5('zenvello'), s.password),
    s.owner_name = IF(s.owner_name = '' OR s.owner_name IS NULL, 'ZENVello Owner', s.owner_name)
WHERE s.domain = 'zenvello.ecommerce.test';

INSERT INTO `store_settings` (`store_id`, `theme_id`, `field_key`, `field_value`)
SELECT s.id, s.theme_id, f.field_key, f.default_value
FROM `stores` s
JOIN `theme_setting_fields` f ON f.theme_id = s.theme_id
WHERE s.domain = 'zenvello.ecommerce.test'
  AND NOT EXISTS (
      SELECT 1 FROM `store_settings` ss
      WHERE ss.store_id = s.id AND ss.field_key = f.field_key
  );
