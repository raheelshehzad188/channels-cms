USE `ecommerce`;

CREATE TABLE IF NOT EXISTS `product_hunting` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `image` varchar(255) NOT NULL DEFAULT '',
  `notes` text NULL,
  `status` tinyint(1) NOT NULL DEFAULT 1,
  `created_by` int(11) NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NULL,
  PRIMARY KEY (`id`),
  KEY `status` (`status`),
  KEY `created_by` (`created_by`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `product_hunting_creatives` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `hunting_id` int(11) NOT NULL,
  `link` varchar(1000) NOT NULL,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `hunting_id` (`hunting_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `product_hunting_suppliers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `hunting_id` int(11) NOT NULL,
  `country_id` int(11) NOT NULL,
  `link` varchar(1000) NOT NULL,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `hunting_id` (`hunting_id`),
  KEY `country_id` (`country_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
