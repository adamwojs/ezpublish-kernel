-- Set storage engine schema version number
UPDATE ezsite_data SET value='7.3.0' WHERE name='ezpublish-version';

--
-- EZP-28881: Add a field to support "date object was trashed"
--

ALTER TABLE ezcontentobject_trash add  trashed integer DEFAULT 0 NOT NULL;
