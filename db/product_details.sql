USE `ecommerce`;

ALTER TABLE `products`
  ADD COLUMN IF NOT EXISTS `details` mediumtext NULL AFTER `description`;
