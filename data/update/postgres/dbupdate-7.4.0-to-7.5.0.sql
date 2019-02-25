-- Set storage engine schema version number
UPDATE ezsite_data SET value='7.5.0' WHERE name='ezpublish-version';

--
-- EZP-30139: As an editor I want to hide and reveal a content item
--

ALTER TABLE ezcontentobject ADD is_hidden integer DEFAULT 0 NOT NULL;
