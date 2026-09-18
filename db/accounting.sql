-- Accounting wallets, ledger, bank details and payout requests.
-- Tables are also created at runtime by Accounting_model::ensure_tables().

CREATE TABLE IF NOT EXISTS `accounting_wallets` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `party_type` varchar(20) NOT NULL,
  `party_id` int(11) NOT NULL,
  `balance` decimal(12,2) NOT NULL DEFAULT 0.00,
  `currency` varchar(10) NOT NULL DEFAULT '',
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `party` (`party_type`,`party_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `accounting_ledger` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `party_type` varchar(20) NOT NULL,
  `party_id` int(11) NOT NULL,
  `order_id` int(11) DEFAULT NULL,
  `item_id` int(11) DEFAULT NULL,
  `payout_id` int(11) DEFAULT NULL,
  `entry_type` varchar(30) NOT NULL,
  `amount` decimal(12,2) NOT NULL,
  `currency` varchar(10) NOT NULL,
  `balance_after` decimal(12,2) NOT NULL DEFAULT 0.00,
  `note` varchar(255) NOT NULL DEFAULT '',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `party` (`party_type`,`party_id`),
  KEY `order_id` (`order_id`),
  KEY `item_id` (`item_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `accounting_bank_details` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `party_type` varchar(20) NOT NULL,
  `party_id` int(11) NOT NULL,
  `account_holder` varchar(150) NOT NULL DEFAULT '',
  `bank_name` varchar(150) NOT NULL DEFAULT '',
  `account_number` varchar(64) NOT NULL DEFAULT '',
  `iban` varchar(64) NOT NULL DEFAULT '',
  `swift` varchar(32) NOT NULL DEFAULT '',
  `routing_number` varchar(64) NOT NULL DEFAULT '',
  `paypal_email` varchar(150) NOT NULL DEFAULT '',
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `party` (`party_type`,`party_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `accounting_payout_requests` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `party_type` varchar(20) NOT NULL,
  `party_id` int(11) NOT NULL,
  `amount` decimal(12,2) NOT NULL,
  `currency` varchar(10) NOT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'pending',
  `bank_snapshot` text,
  `admin_note` varchar(255) NOT NULL DEFAULT '',
  `requested_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `processed_at` datetime DEFAULT NULL,
  `processed_by` int(11) DEFAULT NULL,
  `receipt` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `party` (`party_type`,`party_id`),
  KEY `status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `order_item_status_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `order_id` int(11) NOT NULL,
  `item_id` int(11) NOT NULL,
  `status` varchar(30) NOT NULL,
  `note` varchar(255) NOT NULL DEFAULT '',
  `created_by_type` varchar(30) NOT NULL DEFAULT '',
  `created_by_id` int(11) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `order_id` (`order_id`),
  KEY `item_id` (`item_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
