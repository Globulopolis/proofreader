CREATE TABLE IF NOT EXISTS `#__proofreader_typos` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `typo_text` TEXT NOT NULL,
  `typo_prefix` TEXT NOT NULL,
  `typo_raw` TEXT NOT NULL,
  `typo_suffix` TEXT NOT NULL,
  `typo_comment` TEXT NOT NULL,
  `page_url` VARCHAR(255) NOT NULL DEFAULT '',
  `page_title` VARCHAR(255) NOT NULL DEFAULT '',
  `page_language` VARCHAR(255) NOT NULL DEFAULT '',
  `created` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
  `created_by` INT(11) UNSIGNED NOT NULL DEFAULT '0',
  `created_by_ip` VARCHAR(39) NOT NULL DEFAULT '',
  `created_by_name` VARCHAR(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `idx_created_by`(`created_by`),
  KEY `idx_page_language`(`page_language`)
)
  DEFAULT CHARSET =utf8;