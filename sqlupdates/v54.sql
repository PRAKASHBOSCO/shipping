UPDATE `business_settings` SET `value` = '5.5' WHERE `business_settings`.`type` = 'current_version';

INSERT INTO `shipment_settings` (`key`, `value`) VALUES ('is_def_mile_or_fees', '2'),('def_pickup_cost', '0'), ('def_supply_cost', '0'), ('def_mile_cost', '0'), ('def_return_mile_cost', '0'), ('def_mile_cost_gram', '0'),('def_return_mile_cost_gram','0'), ('def_shipping_cost', '0');


INSERT INTO `translations` (`id`, `lang`, `lang_key`, `lang_value`, `created_at`, `updated_at`) VALUES (16601, 'en', 'You can\'t delete this addon , this addon required by another addons', 'You can\'t delete this addon , this addon required by another addons', current_timestamp(), current_timestamp());

SET @dbname = DATABASE();
SET @tablename = "shipments";
SET @columnname = "otp";
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE
      (table_name = @tablename)
      AND (table_schema = @dbname)
      AND (column_name = @columnname)
  ) > 0,
  "SELECT 1",
  CONCAT("ALTER TABLE ", @tablename, " ADD ", @columnname, " VARCHAR(25)  NULL;")
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

SET @dbname = DATABASE();
SET @tablename = "transactions";
SET @columnname = "created_by";
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE
      (table_name = @tablename)
      AND (table_schema = @dbname)
      AND (column_name = @columnname)
  ) > 0,
  "SELECT 1",
  CONCAT("ALTER TABLE ", @tablename, " ADD ", @columnname, " INT(11);")
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

SET @dbname = DATABASE();
SET @tablename = "shipments";
SET @columnname = "order_id";
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE
      (table_name = @tablename)
      AND (table_schema = @dbname)
      AND (column_name = @columnname)
  ) > 0,
  "SELECT 1",
  CONCAT("ALTER TABLE ", @tablename, " ADD ", @columnname, " VARCHAR(50) COLLATE utf8_unicode_ci DEFAULT NULL;")
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;


COMMIT;