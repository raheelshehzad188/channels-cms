USE `ecommerce`;

INSERT INTO `themes` (`name`, `slug`, `description`, `status`)
SELECT 'Fruitables', 'fruitables', 'Organic fruits and vegetables storefront.', 1
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `themes` WHERE `slug` = 'fruitables');

INSERT INTO `theme_setting_fields` (`theme_id`, `field_key`, `field_label`, `field_type`, `is_required`, `default_value`, `sort_order`)
SELECT t.id, v.field_key, v.field_label, v.field_type, v.is_required, v.default_value, v.sort_order
FROM `themes` t
JOIN (
    SELECT 'logo' AS field_key, 'Header Logo' AS field_label, 'image' AS field_type, 0 AS is_required, '' AS default_value, 1 AS sort_order
    UNION ALL SELECT 'primary_color', 'Primary Color', 'color', 1, '#81C408', 2
    UNION ALL SELECT 'secondary_color', 'Secondary Color', 'color', 1, '#FFB524', 3
    UNION ALL SELECT 'footer_text', 'Footer Text', 'text', 1, 'Fruitables. All rights reserved.', 4
    UNION ALL SELECT 'hero_title', 'Hero Title', 'text', 0, 'Organic Veggies & Fruits Foods', 5
    UNION ALL SELECT 'hero_subtitle', 'Hero Subtitle', 'text', 0, '100% Organic Foods', 6
    UNION ALL SELECT 'address', 'Address', 'text', 0, '123 Street, New York', 7
    UNION ALL SELECT 'email', 'Email', 'text', 0, 'email@example.com', 8
    UNION ALL SELECT 'phone', 'Phone', 'text', 0, '+0123 4567 8910', 9
) v
WHERE t.slug = 'fruitables'
  AND NOT EXISTS (
      SELECT 1 FROM `theme_setting_fields` f
      WHERE f.theme_id = t.id AND f.field_key = v.field_key
  );

INSERT INTO `stores` (`name`, `domain`, `theme_id`, `status`)
SELECT 'Fruitables Store', 'fruitables.ecommerce.test', t.id, 1
FROM `themes` t
WHERE t.slug = 'fruitables'
  AND NOT EXISTS (SELECT 1 FROM `stores` WHERE `domain` = 'fruitables.ecommerce.test');

INSERT INTO `store_settings` (`store_id`, `theme_id`, `field_key`, `field_value`)
SELECT s.id, s.theme_id, f.field_key, f.default_value
FROM `stores` s
JOIN `theme_setting_fields` f ON f.theme_id = s.theme_id
WHERE s.domain = 'fruitables.ecommerce.test'
  AND NOT EXISTS (
      SELECT 1 FROM `store_settings` ss
      WHERE ss.store_id = s.id AND ss.field_key = f.field_key
  );
