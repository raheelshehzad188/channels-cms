USE `ecommerce`;

ALTER TABLE `stores`
  ADD COLUMN IF NOT EXISTS `email` varchar(190) NOT NULL DEFAULT '' AFTER `domain`,
  ADD COLUMN IF NOT EXISTS `password` varchar(255) NOT NULL DEFAULT '' AFTER `email`,
  ADD COLUMN IF NOT EXISTS `owner_name` varchar(150) NOT NULL DEFAULT '' AFTER `password`,
  ADD COLUMN IF NOT EXISTS `country_id` int(11) DEFAULT NULL AFTER `theme_id`;

ALTER TABLE `products`
  ADD COLUMN IF NOT EXISTS `country_id` int(11) DEFAULT NULL AFTER `supplier_id`,
  ADD COLUMN IF NOT EXISTS `max_sale_price` decimal(12,2) NOT NULL DEFAULT 0.00 AFTER `price`,
  ADD COLUMN IF NOT EXISTS `created_by` int(11) DEFAULT NULL AFTER `status`;

ALTER TABLE `users`
  ADD COLUMN IF NOT EXISTS `commission` decimal(12,2) NOT NULL DEFAULT 0.00 AFTER `phone`;

CREATE TABLE IF NOT EXISTS `platform_settings` (
  `setting_key` varchar(100) NOT NULL,
  `setting_value` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT IGNORE INTO `platform_settings` (`setting_key`, `setting_value`) VALUES
('platform_fee', '2.00'),
('vat', '0.00');

CREATE TABLE IF NOT EXISTS `store_product_prices` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `store_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `markup` decimal(12,2) NOT NULL DEFAULT 0.00,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `store_product` (`store_id`, `product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
