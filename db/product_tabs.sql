USE `ecommerce`;

ALTER TABLE `products`
  ADD COLUMN `compare_price` decimal(12,2) NOT NULL DEFAULT 0.00 AFTER `price`,
  ADD COLUMN `cost_price` decimal(12,2) NOT NULL DEFAULT 0.00 AFTER `compare_price`,
  ADD COLUMN `slug` varchar(255) NOT NULL DEFAULT '' AFTER `sku`,
  ADD COLUMN `seo_title` varchar(255) NOT NULL DEFAULT '' AFTER `description`,
  ADD COLUMN `seo_description` text AFTER `seo_title`,
  ADD COLUMN `seo_keywords` varchar(255) NOT NULL DEFAULT '' AFTER `seo_description`;

CREATE TABLE IF NOT EXISTS `product_images` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `product_id` int(11) NOT NULL,
  `image` varchar(255) NOT NULL,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `product_id` (`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `product_variations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `product_id` int(11) NOT NULL,
  `option_name` varchar(100) NOT NULL DEFAULT '',
  `option_value` varchar(100) NOT NULL DEFAULT '',
  `sku` varchar(100) NOT NULL DEFAULT '',
  `price` decimal(12,2) NOT NULL DEFAULT 0.00,
  `stock` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `product_id` (`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
