SET default_storage_engine=InnoDB;
-- Set storage engine schema version number
UPDATE ezsite_data SET value='7.5.0' WHERE name='ezpublish-version';

--
-- EZP-30139: As an editor I want to hide and reveal a content item
--

ALTER TABLE `ezcontentobject` ADD COLUMN `is_hidden` INT(11) NOT NULL DEFAULT '0';
