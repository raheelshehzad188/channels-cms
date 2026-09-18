USE `ecommerce`;

ALTER TABLE `stores`
  ADD COLUMN IF NOT EXISTS `auto_add_products` TINYINT(1) NOT NULL DEFAULT 0,
  ADD COLUMN IF NOT EXISTS `price_plus_amount` DECIMAL(12,2) NOT NULL DEFAULT 0.00;

ALTER TABLE `products`
  ADD COLUMN IF NOT EXISTS `auto_add_to_stores` TINYINT(1) NOT NULL DEFAULT 0;
