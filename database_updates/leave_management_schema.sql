-- Update leave_records table to add missing columns
ALTER TABLE `leave_records` 
ADD COLUMN `reason` TEXT DEFAULT NULL AFTER `approved_by`,
ADD COLUMN `comments` TEXT DEFAULT NULL AFTER `reason`,
ADD COLUMN `half_day` TINYINT(1) DEFAULT 0 AFTER `comments`,
ADD COLUMN `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP AFTER `half_day`,
ADD COLUMN `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER `created_at`;

-- Update leave_type enum to include more options
ALTER TABLE `leave_records` 
MODIFY COLUMN `leave_type` ENUM('Sick','Vacation','Emergency','Maternity','Paternity','Personal') NOT NULL;

-- Create leave_balances table for tracking annual leave entitlements
CREATE TABLE IF NOT EXISTS `leave_balances` (
  `balance_id` int NOT NULL AUTO_INCREMENT,
  `employee_id` int NOT NULL,
  `year` int NOT NULL,
  `vacation_total` int DEFAULT 15,
  `sick_total` int DEFAULT 10,
  `personal_total` int DEFAULT 5,
  `emergency_total` int DEFAULT 30,
  `maternity_total` int DEFAULT 90,
  `paternity_total` int DEFAULT 7,
  `vacation_used` int DEFAULT 0,
  `sick_used` int DEFAULT 0,
  `personal_used` int DEFAULT 0,
  `emergency_used` int DEFAULT 0,
  `maternity_used` int DEFAULT 0,
  `paternity_used` int DEFAULT 0,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`balance_id`),
  UNIQUE KEY `employee_year` (`employee_id`, `year`),
  KEY `employee_id` (`employee_id`),
  CONSTRAINT `leave_balances_ibfk_1` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`employee_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Initialize leave balances for all existing employees for current year
INSERT INTO `leave_balances` (`employee_id`, `year`)
SELECT `employee_id`, YEAR(CURDATE())
FROM `employees`
WHERE NOT EXISTS (
    SELECT 1 FROM `leave_balances` 
    WHERE `leave_balances`.`employee_id` = `employees`.`employee_id` 
    AND `leave_balances`.`year` = YEAR(CURDATE())
);
