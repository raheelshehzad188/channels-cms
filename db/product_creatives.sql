CREATE TABLE IF NOT EXISTS `product_creatives` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `product_id` INT(11) NOT NULL,
  `link` VARCHAR(1000) NOT NULL DEFAULT '',
  `label` VARCHAR(150) NOT NULL DEFAULT '',
  `sort_order` INT(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `product_id` (`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
