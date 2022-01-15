UPDATE `business_settings` SET `value` = '5.4' WHERE `business_settings`.`type` = 'current_version';

SET @dbname = DATABASE();
SET @tablename = "users";
SET @columnname = "device_token";
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

INSERT INTO `business_settings` (`id`, `name`, `key`, `type`, `value`, `created_at`, `updated_at`) VALUES (16600, 'Invoice Payment', 'payment_gateway', 'invoice_payment', '0', current_timestamp(), current_timestamp());
INSERT INTO `business_settings` (`id`, `name`, `key`, `type`, `value`, `created_at`, `updated_at`) VALUES (16601, NULL, NULL, 'server_key', NULL, current_timestamp(), current_timestamp());

INSERT INTO `translations` (`id`, `lang`, `lang_key`, `lang_value`, `created_at`, `updated_at`) VALUES (16601, 'en', 'You can\'t delete this addon , this addon required by another addons', 'You can\'t delete this addon , this addon required by another addons', current_timestamp(), current_timestamp());

SET @dbname = DATABASE();
SET @tablename = "staff";
SET @columnname = "branch_id";
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE
      (table_name = @tablename)
      AND (table_schema = @dbname)
      AND (column_name = @columnname)
  ) > 0,
  "SELECT 1",
  CONCAT("ALTER TABLE ", @tablename, " ADD ", @columnname, " int(11)  NULL;")
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;


COMMIT;