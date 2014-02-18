/* Base DB Structure */

CREATE TABLE IF NOT EXISTS `cold_static_updates` (
  `key`         VARCHAR(40) NOT NULL
  COMMENT 'Key to search static update',
  `update_time` INT(11)     NOT NULL
  COMMENT 'Update time in Unix Timestamp',
  PRIMARY KEY (`key`)
)
  ENGINE =InnoDB
  DEFAULT CHARSET =utf8
  COMMENT ='Table holds static tables update time';

CREATE TABLE IF NOT EXISTS `languages` (
  `lang_code`        VARCHAR(2)  NOT NULL
  COMMENT 'Language code in ISO 639‑1 scheme',
  `lang_title`       VARCHAR(64) NOT NULL
  COMMENT 'International language title',
  `lang_title_local` VARCHAR(64) NOT NULL
  COMMENT 'Local language title'
)
  ENGINE =InnoDB
  DEFAULT CHARSET =utf8
  COMMENT ='Languages list table';


/* Triggers */
DROP TRIGGER IF EXISTS `languages_invalidate_delete`;
DELIMITER //
CREATE TRIGGER `languages_invalidate_delete` AFTER DELETE ON `languages`
FOR EACH ROW INSERT INTO `cold_static_updates` (`key`, `update_time`)
VALUES (sha1('languages'), UNIX_TIMESTAMP())
ON DUPLICATE KEY UPDATE `update_time` = UNIX_TIMESTAMP()
//
DELIMITER ;
DROP TRIGGER IF EXISTS `languages_invalidate_insert`;
DELIMITER //
CREATE TRIGGER `languages_invalidate_insert` AFTER INSERT ON `languages`
FOR EACH ROW INSERT INTO `cold_static_updates` (`key`, `update_time`)
VALUES (sha1('languages'), UNIX_TIMESTAMP())
ON DUPLICATE KEY UPDATE `update_time` = UNIX_TIMESTAMP()
//
DELIMITER ;
DROP TRIGGER IF EXISTS `languages_invalidate_update`;
DELIMITER //
CREATE TRIGGER `languages_invalidate_update` AFTER UPDATE ON `languages`
FOR EACH ROW INSERT INTO `cold_static_updates` (`key`, `update_time`)
VALUES (sha1('languages'), UNIX_TIMESTAMP())
ON DUPLICATE KEY UPDATE `update_time` = UNIX_TIMESTAMP()
//
DELIMITER ;
