CREATE TABLE /*_*/spritesheet (
  `sid` int(11) NOT NULL AUTO_INCREMENT,
  `page_id` int(10) unsigned NOT NULL,
  `columns` int(11) NOT NULL,
  `rows` int(11) NOT NULL,
  `inset` int(11) NOT NULL,
  PRIMARY KEY (`sid`),
  KEY `page_id` (`page_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;