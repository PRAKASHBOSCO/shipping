INSERT INTO `business_settings` (`id`, `type`, `value`, `created_at`, `updated_at`) VALUES (NULL, 'def_shipment_code_type', 'random', current_timestamp(), current_timestamp());

UPDATE `business_settings` SET `value` = '5.2' WHERE `business_settings`.`type` = 'current_version';

SET @dbname = DATABASE();
SET @tablename = "languages";
SET @columnname = "icon";
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE
      (table_name = @tablename)
      AND (table_schema = @dbname)
      AND (column_name = @columnname)
  ) > 0,
  "SELECT 1",
  CONCAT("ALTER TABLE ", @tablename, " ADD ", @columnname, " VARCHAR(255) COLLATE utf8_unicode_ci DEFAULT NULL;")
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;


CREATE TABLE `address_client` (`id` int NOT NULL AUTO_INCREMENT, `client_id` int NOT NULL, `address` varchar(255) NULL, `client_street_address_map` varchar(255) NULL, `client_lat` varchar(255) NULL, `client_lng` varchar(255) NULL, `client_url` varchar(255) NULL, `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP, `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,  PRIMARY KEY (`id`));

INSERT INTO `uploads` (`id`, `file_original_name`, `file_name`, `user_id`, `file_size`, `extension`, `type`, `created_at`, `updated_at`) VALUES (16500, 'ar', 'uploads/all/ar.svg', '1', '1391', 'svg', 'image', current_timestamp(), current_timestamp());
INSERT INTO `uploads` (`id`, `file_original_name`, `file_name`, `user_id`, `file_size`, `extension`, `type`, `created_at`, `updated_at`) VALUES (16501, 'en', 'uploads/all/en.svg', '1', '1391', 'svg', 'image', current_timestamp(), current_timestamp());

UPDATE `languages` SET `code` = 'ar', `icon` = '16500' WHERE `code` = 'eg';
UPDATE `languages` SET `code` = 'en', `icon` = '16501' WHERE `code` = 'en';

COMMIT;