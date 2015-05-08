CREATE TABLE /*_*/spritesheet (
  `spritesheet_id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varbinary(255) NOT NULL,
  `columns` int(11) NOT NULL,
  `rows` int(11) NOT NULL,
  `inset` int(11) NOT NULL,
  `edited` int(14) NOT NULL DEFAULT '0',
  PRIMARY KEY (`spritesheet_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

ALTER TABLE /*_*/spritesheet ADD UNIQUE `title` (`title`),
ADD INDEX `edited` (`edited`);