CREATE TABLE /*_*/spritename_rev (
  `spritename_rev_id` int(14) NOT NULL AUTO_INCREMENT,
  `spritename_id` int(14) NOT NULL,
  `spritesheet_id` int(14) NOT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `type` enum('sprite','slice') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'sprite',
  `values` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `edited` int(14) NOT NULL DEFAULT '0',
  `deleted` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`spritename_rev_id`),
  KEY `spritename_id` (`spritename_id`),
  KEY `spritesheet_id_name` (`spritesheet_id`,`name`),
  KEY `type` (`type`),
  KEY `edited` (`edited`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;