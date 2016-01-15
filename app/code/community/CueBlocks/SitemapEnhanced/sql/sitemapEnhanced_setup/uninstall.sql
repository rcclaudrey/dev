DROP TABLE IF EXISTS `cb_sitemapenhanced_files`;
DROP TABLE IF EXISTS `cb_sitemapenhanced`;
DELETE FROM `core_config_data` WHERE `path` LIKE '%sitemapEnhanced%';
DELETE FROM `core_resource` WHERE CONVERT(`core_resource`.`code` USING utf8) = 'sitemapEnhanced_setup' LIMIT 1;