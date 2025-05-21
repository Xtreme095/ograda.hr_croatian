-- Add missing columns to tblorder_master table
-- First check if columns exist before adding them

-- payment_method column
ALTER TABLE `tblorder_master` 
ADD COLUMN IF NOT EXISTS `payment_method` VARCHAR(50) NULL DEFAULT NULL;

-- address_data column (for storing billing and shipping details as JSON)
ALTER TABLE `tblorder_master` 
ADD COLUMN IF NOT EXISTS `address_data` TEXT NULL DEFAULT NULL;

-- order_note column
ALTER TABLE `tblorder_master` 
ADD COLUMN IF NOT EXISTS `order_note` TEXT NULL DEFAULT NULL;

-- Make sure other essential columns exist
ALTER TABLE `tblorder_master`
ADD COLUMN IF NOT EXISTS `email` VARCHAR(100) NULL DEFAULT NULL,
ADD COLUMN IF NOT EXISTS `status` VARCHAR(40) NULL DEFAULT NULL,
ADD COLUMN IF NOT EXISTS `order_date` DATE NULL DEFAULT NULL,
ADD COLUMN IF NOT EXISTS `subtotal` DECIMAL(15,2) NULL DEFAULT 0.00,
ADD COLUMN IF NOT EXISTS `total` DECIMAL(15,2) NULL DEFAULT 0.00;

-- In case the IF NOT EXISTS syntax is not supported in your MySQL version
-- Here's an alternative approach using stored procedures:

DELIMITER $$
DROP PROCEDURE IF EXISTS AddColumnIfNotExists $$
CREATE PROCEDURE AddColumnIfNotExists(
    IN tableName VARCHAR(100),
    IN columnName VARCHAR(100),
    IN columnDefinition TEXT
)
BEGIN
    DECLARE columnExists INT;
    
    SELECT COUNT(*)
    INTO columnExists
    FROM information_schema.COLUMNS 
    WHERE TABLE_SCHEMA = DATABASE()
        AND TABLE_NAME = tableName
        AND COLUMN_NAME = columnName;
    
    IF columnExists = 0 THEN
        SET @sql = CONCAT('ALTER TABLE ', tableName, ' ADD COLUMN ', columnName, ' ', columnDefinition);
        PREPARE stmt FROM @sql;
        EXECUTE stmt;
        DEALLOCATE PREPARE stmt;
    END IF;
END $$
DELIMITER ;

-- Call the stored procedure for each column
CALL AddColumnIfNotExists('tblorder_master', 'payment_method', 'VARCHAR(50) NULL DEFAULT NULL');
CALL AddColumnIfNotExists('tblorder_master', 'address_data', 'TEXT NULL DEFAULT NULL');
CALL AddColumnIfNotExists('tblorder_master', 'order_note', 'TEXT NULL DEFAULT NULL');
CALL AddColumnIfNotExists('tblorder_master', 'email', 'VARCHAR(100) NULL DEFAULT NULL');
CALL AddColumnIfNotExists('tblorder_master', 'status', 'VARCHAR(40) NULL DEFAULT NULL');
CALL AddColumnIfNotExists('tblorder_master', 'order_date', 'DATE NULL DEFAULT NULL');
CALL AddColumnIfNotExists('tblorder_master', 'subtotal', 'DECIMAL(15,2) NULL DEFAULT 0.00');
CALL AddColumnIfNotExists('tblorder_master', 'total', 'DECIMAL(15,2) NULL DEFAULT 0.00');

-- Drop the procedure after use
DROP PROCEDURE IF EXISTS AddColumnIfNotExists; 