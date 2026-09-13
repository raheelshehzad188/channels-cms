-- Phase 2: Store Administration + Tenant Engine

ALTER TABLE `stores`
  ADD COLUMN IF NOT EXISTS `subdomain` varchar(100) DEFAULT NULL AFTER `domain`,
  ADD COLUMN IF NOT EXISTS `custom_domain` varchar(255) DEFAULT NULL AFTER `subdomain`,
  ADD COLUMN IF NOT EXISTS `logo` varchar(500) DEFAULT NULL AFTER `slug`,
  ADD COLUMN IF NOT EXISTS `description` text DEFAULT NULL AFTER `logo`,
  ADD COLUMN IF NOT EXISTS `country` varchar(100) DEFAULT NULL AFTER `address`,
  ADD COLUMN IF NOT EXISTS `currency` varchar(10) DEFAULT 'USD' AFTER `country`,
  ADD COLUMN IF NOT EXISTS `timezone` varchar(64) DEFAULT 'UTC' AFTER `currency`,
  ADD COLUMN IF NOT EXISTS `language` varchar(10) DEFAULT 'en' AFTER `timezone`,
  ADD COLUMN IF NOT EXISTS `social_links` text DEFAULT NULL AFTER `language`,
  ADD COLUMN IF NOT EXISTS `business_name` varchar(255) DEFAULT NULL AFTER `social_links`,
  ADD COLUMN IF NOT EXISTS `business_registration` varchar(100) DEFAULT NULL AFTER `business_name`,
  ADD COLUMN IF NOT EXISTS `tax_id` varchar(100) DEFAULT NULL AFTER `business_registration`,
  ADD COLUMN IF NOT EXISTS `reset_token` varchar(128) DEFAULT NULL AFTER `password`,
  ADD COLUMN IF NOT EXISTS `reset_expires` datetime DEFAULT NULL AFTER `reset_token`;

CREATE TABLE IF NOT EXISTS `staff` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `store_id` int(10) unsigned NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone` varchar(50) DEFAULT NULL,
  `role` varchar(50) NOT NULL DEFAULT 'support',
  `password` varchar(255) NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT 1,
  `permissions` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `store_email` (`store_id`,`email`),
  KEY `store_id` (`store_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `store_settings` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `store_id` int(10) unsigned NOT NULL,
  `setting_key` varchar(100) NOT NULL,
  `setting_value` text DEFAULT NULL,
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `store_setting` (`store_id`,`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `store_sessions` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `store_id` int(10) unsigned NOT NULL,
  `staff_id` int(10) unsigned DEFAULT NULL,
  `session_token` varchar(128) NOT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `expires_at` datetime NOT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `session_token` (`session_token`),
  KEY `store_id` (`store_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `store_permissions` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `role_slug` varchar(50) NOT NULL,
  `role_name` varchar(100) NOT NULL,
  `permissions` text NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `role_slug` (`role_slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `store_themes` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `store_id` int(10) unsigned NOT NULL,
  `theme_name` varchar(100) NOT NULL,
  `theme_slug` varchar(100) NOT NULL,
  `version` varchar(20) DEFAULT '1.0',
  `is_active` tinyint(1) NOT NULL DEFAULT 0,
  `preview_url` varchar(500) DEFAULT NULL,
  `installed_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `store_id` (`store_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `store_apps` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `store_id` int(10) unsigned NOT NULL,
  `app_name` varchar(100) NOT NULL,
  `app_slug` varchar(100) NOT NULL,
  `is_enabled` tinyint(1) NOT NULL DEFAULT 1,
  `config` text DEFAULT NULL,
  `installed_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `store_app` (`store_id`,`app_slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT IGNORE INTO `store_permissions` (`role_slug`, `role_name`, `permissions`) VALUES
('owner', 'Store Owner', '["*"]'),
('manager', 'Manager', '["dashboard","products","orders","customers","staff","settings","themes","apps"]'),
('editor', 'Editor', '["dashboard","products","themes"]'),
('support', 'Support', '["dashboard","orders","customers"]'),
('inventory', 'Inventory', '["dashboard","products"]'),
('marketing', 'Marketing', '["dashboard","customers","apps"]');
