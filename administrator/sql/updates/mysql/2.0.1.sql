DROP INDEX `idx_language` ON `#__proofreader_typos`;

ALTER TABLE `#__proofreader_typos` RENAME COLUMN `language` TO `page_language`;
ALTER TABLE `#__proofreader_typos` RENAME COLUMN `ip` TO `created_by_ip`;
ALTER TABLE `#__proofreader_typos` RENAME COLUMN `comment` TO `typo_comment`;

CREATE INDEX `idx_page_language` ON `#__proofreader_typos`(`page_language`);
