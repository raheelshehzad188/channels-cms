USE `ecommerce`;

ALTER TABLE `stores`
  ADD COLUMN IF NOT EXISTS `custom_css` mediumtext NULL;

CREATE TABLE IF NOT EXISTS `store_customers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `store_id` int(11) NOT NULL,
  `name` varchar(150) NOT NULL,
  `email` varchar(190) NOT NULL,
  `password` varchar(255) NOT NULL,
  `phone` varchar(50) NOT NULL DEFAULT '',
  `address` text,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `store_email` (`store_id`, `email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
