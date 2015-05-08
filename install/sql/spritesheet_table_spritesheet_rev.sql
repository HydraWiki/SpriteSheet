CREATE TABLE /*_*/spritesheet_rev (
  `spritesheet_rev_id` int(11) NOT NULL AUTO_INCREMENT,
  `spritesheet_id` int(11) NOT NULL DEFAULT '0',
  `title` varbinary(255) NOT NULL,
  `columns` int(11) NOT NULL,
  `rows` int(11) NOT NULL,
  `inset` int(11) NOT NULL,
  `edited` int(14) NOT NULL DEFAULT '0',
  PRIMARY KEY (`spritesheet_rev_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

ALTER TABLE /*_*/spritesheet_rev ADD INDEX `spritesheet_id` (`spritesheet_id`),
ADD INDEX `title` (`title`),
ADD INDEX `edited` (`edited`);