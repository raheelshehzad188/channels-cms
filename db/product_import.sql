USE `ecommerce`;

ALTER TABLE `products`
  ADD COLUMN IF NOT EXISTS `source_url` varchar(500) NOT NULL DEFAULT '' AFTER `image`;

INSERT IGNORE INTO `countries` (`name`, `code`, `iso3`, `phone_code`, `currency`, `status`) VALUES
('India', 'IN', 'IND', '+91', 'INR', 1),
('Sweden', 'SE', 'SWE', '+46', 'SEK', 1);

INSERT INTO `suppliers` (`country_id`, `name`, `email`, `phone`, `company`, `address`, `status`)
SELECT c.id, 'AP UK', '', '', 'AP UK', 'United Kingdom', 1
FROM `countries` c
WHERE c.code = 'GB'
  AND NOT EXISTS (SELECT 1 FROM `suppliers` s WHERE s.name = 'AP UK')
LIMIT 1;

INSERT INTO `suppliers` (`country_id`, `name`, `email`, `phone`, `company`, `address`, `status`)
SELECT c.id, 'MN IN', '', '', 'MN IN', 'India', 1
FROM `countries` c
WHERE c.code = 'IN'
  AND NOT EXISTS (SELECT 1 FROM `suppliers` s WHERE s.name = 'MN IN')
LIMIT 1;

CREATE TABLE IF NOT EXISTS `product_import_sources` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `domain` varchar(190) NOT NULL,
  `importer_class` varchar(100) NOT NULL,
  `supplier_id` int(11) NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `domain` (`domain`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `product_import_unknown_links` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `url` text NOT NULL,
  `url_hash` char(40) NOT NULL,
  `domain` varchar(190) NOT NULL,
  `country_id` int(11) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `hit_count` int(11) NOT NULL DEFAULT 1,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `last_seen_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `url_hash` (`url_hash`),
  KEY `domain` (`domain`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `product_import_sources` (`domain`, `importer_class`, `supplier_id`, `status`)
SELECT 'amazon.co.uk', 'Amazon_uk', s.id, 1
FROM `suppliers` s
WHERE s.name = 'AP UK'
ON DUPLICATE KEY UPDATE `importer_class` = VALUES(`importer_class`), `supplier_id` = VALUES(`supplier_id`), `status` = 1;

INSERT INTO `product_import_sources` (`domain`, `importer_class`, `supplier_id`, `status`)
SELECT 'ebay.co.uk', 'Ebay_uk', s.id, 1
FROM `suppliers` s
WHERE s.name = 'AP UK'
ON DUPLICATE KEY UPDATE `importer_class` = VALUES(`importer_class`), `supplier_id` = VALUES(`supplier_id`), `status` = 1;

INSERT INTO `product_import_sources` (`domain`, `importer_class`, `supplier_id`, `status`)
SELECT 'amazon.in', 'Amazon_in', s.id, 1
FROM `suppliers` s
WHERE s.name = 'MN IN'
ON DUPLICATE KEY UPDATE `importer_class` = VALUES(`importer_class`), `supplier_id` = VALUES(`supplier_id`), `status` = 1;

INSERT INTO `product_import_sources` (`domain`, `importer_class`, `supplier_id`, `status`)
SELECT 'godropship.co.uk', 'Godropship_uk', s.id, 1
FROM `suppliers` s
WHERE s.name = 'AP UK'
ON DUPLICATE KEY UPDATE `importer_class` = VALUES(`importer_class`), `supplier_id` = VALUES(`supplier_id`), `status` = 1;

INSERT INTO `suppliers` (`country_id`, `name`, `email`, `phone`, `company`, `address`, `status`)
SELECT c.id, 'FYNDIQ SE', '', '', 'FYNDIQ SE', 'Sweden', 1
FROM `countries` c
WHERE c.code = 'SE'
  AND NOT EXISTS (SELECT 1 FROM `suppliers` s WHERE s.name = 'FYNDIQ SE')
LIMIT 1;

INSERT INTO `suppliers` (`country_id`, `name`, `email`, `phone`, `company`, `address`, `status`)
SELECT c.id, 'CDON SE', '', '', 'CDON SE', 'Sweden', 1
FROM `countries` c
WHERE c.code = 'SE'
  AND NOT EXISTS (SELECT 1 FROM `suppliers` s WHERE s.name = 'CDON SE')
LIMIT 1;

INSERT INTO `product_import_sources` (`domain`, `importer_class`, `supplier_id`, `status`)
SELECT 'fyndiq.se', 'Fyndiq_se', s.id, 1
FROM `suppliers` s
WHERE s.name = 'FYNDIQ SE'
ON DUPLICATE KEY UPDATE `importer_class` = VALUES(`importer_class`), `supplier_id` = VALUES(`supplier_id`), `status` = 1;

INSERT INTO `product_import_sources` (`domain`, `importer_class`, `supplier_id`, `status`)
SELECT 'cdon.se', 'Cdon_se', s.id, 1
FROM `suppliers` s
WHERE s.name = 'CDON SE'
ON DUPLICATE KEY UPDATE `importer_class` = VALUES(`importer_class`), `supplier_id` = VALUES(`supplier_id`), `status` = 1;
