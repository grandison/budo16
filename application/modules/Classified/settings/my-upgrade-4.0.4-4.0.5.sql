
ALTER TABLE `engine4_classified_fields_meta`
ADD COLUMN `show` tinyint(1) unsigned NOT NULL default '1'
AFTER `search` ;
