USE `ecommerce`;

ALTER TABLE `products`
  ADD COLUMN IF NOT EXISTS `made_by` varchar(150) NOT NULL DEFAULT '' AFTER `brand`;
