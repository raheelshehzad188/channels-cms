USE `ecommerce`;

ALTER TABLE `products`
  ADD COLUMN IF NOT EXISTS `parent_sku` varchar(100) NOT NULL DEFAULT '' AFTER `sku`,
  ADD COLUMN IF NOT EXISTS `is_default` tinyint(1) NOT NULL DEFAULT 0 AFTER `parent_sku`;
