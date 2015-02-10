CREATE TABLE /*_*/spritename (
  `spritename_id` int(14) NOT NULL AUTO_INCREMENT,
  `spritesheet_id` int(14) NOT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `type` enum('sprite','slice') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'sprite',
  `values` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`spritename_id`),
  UNIQUE KEY `name` (`name`),
  KEY `spritesheet_id` (`spritesheet_id`),
  KEY `type` (`type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;