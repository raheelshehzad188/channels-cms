USE `ecommerce`;

ALTER TABLE `products`
  ADD COLUMN IF NOT EXISTS `brand` varchar(150) NOT NULL DEFAULT '' AFTER `sku`;
