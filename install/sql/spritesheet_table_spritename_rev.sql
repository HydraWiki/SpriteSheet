CREATE TABLE /*_*/spritename_rev (
  `spritename_rev_id` int(14) NOT NULL AUTO_INCREMENT,
  `spritename_id` int(14) NOT NULL,
  `spritesheet_id` int(14) NOT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `type` enum('sprite','slice') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'sprite',
  `values` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `edited` int(14) NOT NULL DEFAULT '0',
  `deleted` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`spritename_rev_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

ALTER TABLE /*_*/spritename_rev ADD INDEX `spritename_id` (`spritename_id`),
ADD INDEX `spritesheet_id_name` (`spritesheet_id`,`name`),
ADD INDEX `type` (`type`),
ADD INDEX `edited` (`edited`);