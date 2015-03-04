ALTER TABLE /*_*/spritesheet CHANGE `page_id` `title` VARBINARY( 255 ) NOT NULL;
ALTER TABLE /*_*/spritesheet DROP INDEX `page_id`, ADD UNIQUE `title` (`title`);
