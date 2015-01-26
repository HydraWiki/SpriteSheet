CREATE TABLE /*_*/spritesheet (
  `sid` int(11) NOT NULL AUTO_INCREMENT,
  `columns` int(11) NOT NULL,
  `rows` int(11) NOT NULL,
  `inset` int(11) NOT NULL,
  PRIMARY KEY (`sid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;