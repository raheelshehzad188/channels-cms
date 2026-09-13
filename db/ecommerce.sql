CREATE DATABASE IF NOT EXISTS `ecommerce` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `ecommerce`;

CREATE TABLE IF NOT EXISTS `roles` (
  `roleID` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  PRIMARY KEY (`roleID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `roles` (`roleID`, `name`) VALUES
(1, 'administrator'),
(5, 'ecommerce');

CREATE TABLE IF NOT EXISTS `users` (
  `UserID` int(11) NOT NULL AUTO_INCREMENT,
  `first_name` varchar(100) NOT NULL,
  `last_name` varchar(100) NOT NULL,
  `email` varchar(190) NOT NULL,
  `uname` varchar(100) NOT NULL,
  `upass` varchar(255) NOT NULL,
  `roleID` int(11) NOT NULL,
  `phone` varchar(50) NOT NULL DEFAULT '',
  `status` tinyint(1) NOT NULL DEFAULT 1,
  `create_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `ip` varchar(50) NOT NULL DEFAULT '',
  `token` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`UserID`),
  UNIQUE KEY `email` (`email`),
  UNIQUE KEY `uname` (`uname`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `users` (`UserID`, `first_name`, `last_name`, `email`, `uname`, `upass`, `roleID`, `phone`, `status`) VALUES
(1, 'Super', 'Admin', 'admin@ecommerce.local', 'admin', '21232f297a57a5a743894a0e4a801fc3', 1, '', 1),
(2, 'Store', 'Manager', 'ecommerce@ecommerce.local', 'ecommerce', 'db96ff26706a1a3d595ecb67266c2d94', 5, '', 1);

CREATE TABLE IF NOT EXISTS `countries` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(150) NOT NULL,
  `code` varchar(10) NOT NULL,
  `iso3` varchar(10) NOT NULL DEFAULT '',
  `phone_code` varchar(20) NOT NULL DEFAULT '',
  `currency` varchar(10) NOT NULL DEFAULT '',
  `status` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `countries` (`id`, `name`, `code`, `iso3`, `phone_code`, `currency`, `status`) VALUES
(1, 'Pakistan', 'PK', 'PAK', '+92', 'PKR', 1),
(2, 'United Arab Emirates', 'AE', 'ARE', '+971', 'AED', 1),
(3, 'Saudi Arabia', 'SA', 'SAU', '+966', 'SAR', 1),
(4, 'United States', 'US', 'USA', '+1', 'USD', 1),
(5, 'United Kingdom', 'GB', 'GBR', '+44', 'GBP', 1);

CREATE TABLE IF NOT EXISTS `suppliers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `country_id` int(11) NOT NULL,
  `name` varchar(200) NOT NULL,
  `email` varchar(190) NOT NULL DEFAULT '',
  `phone` varchar(50) NOT NULL DEFAULT '',
  `company` varchar(200) NOT NULL DEFAULT '',
  `address` text,
  `status` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `country_id` (`country_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `suppliers` (`id`, `country_id`, `name`, `email`, `phone`, `company`, `address`, `status`) VALUES
(1, 1, 'Karachi Traders', 'karachi@suppliers.local', '+92-21-111000', 'Karachi Traders LLC', 'Saddar, Karachi', 1),
(2, 1, 'Lahore Wholesale', 'lahore@suppliers.local', '+92-42-222000', 'Lahore Wholesale Co', 'Mall Road, Lahore', 1),
(3, 2, 'Dubai Source', 'dubai@suppliers.local', '+971-4-333000', 'Dubai Source FZE', 'Deira, Dubai', 1),
(4, 3, 'Riyadh Supply', 'riyadh@suppliers.local', '+966-11-444000', 'Riyadh Supply Co', 'Olaya, Riyadh', 1),
(5, 4, 'NY Distributors', 'ny@suppliers.local', '+1-212-555000', 'NY Distributors Inc', 'Manhattan, New York', 1);

CREATE TABLE IF NOT EXISTS `products` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `supplier_id` int(11) DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `sku` varchar(100) NOT NULL DEFAULT '',
  `price` decimal(12,2) NOT NULL DEFAULT 0.00,
  `stock` int(11) NOT NULL DEFAULT 0,
  `description` text,
  `image` varchar(255) NOT NULL DEFAULT '',
  `status` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `supplier_id` (`supplier_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `products` (`id`, `supplier_id`, `name`, `sku`, `price`, `stock`, `description`, `status`) VALUES
(1, 1, 'Wireless Headphones', 'WH-1001', 59.99, 120, 'Bluetooth over-ear headphones with 20 hour battery life.', 1),
(2, 3, 'Smart Watch', 'SW-2002', 129.00, 45, 'Fitness smart watch with heart-rate and sleep tracking.', 1),
(3, 5, 'USB-C Charger 65W', 'CH-3003', 24.50, 200, 'Fast 65W USB-C wall charger for laptops and phones.', 1),
(4, 2, 'Cotton T-Shirt', 'TS-4004', 14.99, 300, 'Plain cotton t-shirt, available in multiple sizes.', 1);
