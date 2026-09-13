USE `ecommerce`;

INSERT IGNORE INTO `platform_settings` (`setting_key`, `setting_value`) VALUES
('platform_country_id', '1');

CREATE TABLE IF NOT EXISTS `currency_rates` (
  `currency` varchar(10) NOT NULL,
  `rate_to_platform` decimal(18,8) NOT NULL DEFAULT 1.00000000,
  PRIMARY KEY (`currency`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT IGNORE INTO `currency_rates` (`currency`, `rate_to_platform`) VALUES
('PKR', 1.00000000),
('USD', 278.00000000),
('GBP', 370.00000000),
('AED', 76.00000000),
('SAR', 74.00000000),
('EUR', 305.00000000),
('INR', 3.35000000),
('SEK', 26.50000000);

ALTER TABLE `store_orders`
  ADD COLUMN IF NOT EXISTS `fx_rate` decimal(18,8) NOT NULL DEFAULT 1 AFTER `currency`,
  ADD COLUMN IF NOT EXISTS `platform_fee_platform` decimal(12,2) NOT NULL DEFAULT 0 AFTER `platform_fee_total`,
  ADD COLUMN IF NOT EXISTS `commission_platform` decimal(12,2) NOT NULL DEFAULT 0 AFTER `commission_total`;
