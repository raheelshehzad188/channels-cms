USE `ecommerce`;

CREATE TABLE IF NOT EXISTS `themes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(150) NOT NULL,
  `slug` varchar(100) NOT NULL,
  `description` varchar(255) NOT NULL DEFAULT '',
  `status` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `themes` (`id`, `name`, `slug`, `description`, `status`) VALUES
(1, 'Aurora', 'aurora', 'Light luxury storefront with gold accents.', 1),
(2, 'Noir', 'noir', 'Dark modern storefront with neon accents.', 1);

CREATE TABLE IF NOT EXISTS `theme_setting_fields` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `theme_id` int(11) NOT NULL,
  `field_key` varchar(100) NOT NULL,
  `field_label` varchar(150) NOT NULL,
  `field_type` varchar(50) NOT NULL DEFAULT 'text',
  `is_required` tinyint(1) NOT NULL DEFAULT 0,
  `default_value` varchar(255) NOT NULL DEFAULT '',
  `sort_order` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `theme_id` (`theme_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `theme_setting_fields` (`theme_id`, `field_key`, `field_label`, `field_type`, `is_required`, `default_value`, `sort_order`) VALUES
(1, 'logo', 'Header Logo', 'image', 0, '', 1),
(1, 'primary_color', 'Primary Color', 'color', 1, '#c9a227', 2),
(1, 'header_bg', 'Header Background', 'color', 1, '#fffaf3', 3),
(1, 'footer_text', 'Footer Text', 'text', 1, 'Aurora Store. All rights reserved.', 4),
(2, 'logo', 'Header Logo', 'image', 0, '', 1),
(2, 'primary_color', 'Primary Color', 'color', 1, '#7c5cff', 2),
(2, 'header_bg', 'Header Background', 'color', 1, '#0d0d12', 3),
(2, 'footer_text', 'Footer Text', 'text', 1, 'Noir Store. All rights reserved.', 4);

CREATE TABLE IF NOT EXISTS `stores` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `domain` varchar(255) NOT NULL,
  `theme_id` int(11) DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `domain` (`domain`),
  KEY `theme_id` (`theme_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `store_settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `store_id` int(11) NOT NULL,
  `theme_id` int(11) NOT NULL,
  `field_key` varchar(100) NOT NULL,
  `field_value` text,
  PRIMARY KEY (`id`),
  UNIQUE KEY `store_field` (`store_id`, `field_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `stores` (`id`, `name`, `domain`, `theme_id`, `status`) VALUES
(1, 'Theme One Store', 'theme1.ecommerce.test', 1, 1),
(2, 'Theme Two Store', 'theme2.ecommerce.test', 2, 1);

INSERT INTO `store_settings` (`store_id`, `theme_id`, `field_key`, `field_value`) VALUES
(1, 1, 'primary_color', '#c9a227'),
(1, 1, 'header_bg', '#fffaf3'),
(1, 1, 'footer_text', 'Theme One Store — powered by Aurora'),
(2, 2, 'primary_color', '#7c5cff'),
(2, 2, 'header_bg', '#0d0d12'),
(2, 2, 'footer_text', 'Theme Two Store — powered by Noir');
