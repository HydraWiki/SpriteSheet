CREATE TABLE /*_*/spritesheet (
  `spritesheet_id` int(11) NOT NULL AUTO_INCREMENT,
  `namespace` int(11) NOT NULL,
  `title` varbinary(255) NOT NULL,
  `columns` int(11) NOT NULL,
  `rows` int(11) NOT NULL,
  `inset` int(11) NOT NULL,
  PRIMARY KEY (`spritesheet_id`),
  UNIQUE KEY `namespace_title` (`namespace`,`title`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;