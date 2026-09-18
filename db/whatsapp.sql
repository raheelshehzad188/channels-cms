USE `ecommerce`;

ALTER TABLE `users`
  ADD COLUMN IF NOT EXISTS `whatsapp_number` varchar(40) NOT NULL DEFAULT '' AFTER `phone`;

INSERT IGNORE INTO `platform_settings` (`setting_key`, `setting_value`) VALUES
('whatsapp_enabled', '0'),
('whatsapp_api_url', 'https://khaki-gerbil-447172.hostingersite.com/api/send-message.php'),
('whatsapp_api_key', ''),
('whatsapp_api_secret', ''),
('whatsapp_session_name', ''),
('admin_whatsapp_number', '');
