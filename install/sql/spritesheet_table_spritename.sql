CREATE TABLE /*_*/spritename (
  `name_id` int(14) NOT NULL AUTO_INCREMENT,
  `spritesheet_id` int(14) NOT NULL,
  `sprite_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `values` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`name_id`),
  KEY `spritesheet_id` (`spritesheet_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;