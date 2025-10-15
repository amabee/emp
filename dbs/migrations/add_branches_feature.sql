-- ========================================
-- Migration: Add Branches Feature
-- Date: October 15, 2025
-- Description: Add branches table and link to employees
-- ========================================

-- Step 1: Create branches table
CREATE TABLE IF NOT EXISTS `branches` (
  `branch_id` INT NOT NULL AUTO_INCREMENT,
  `branch_name` VARCHAR(100) NOT NULL,
  `branch_code` VARCHAR(20) DEFAULT NULL COMMENT 'Short code for branch (e.g., CDO-01)',
  `address` TEXT DEFAULT NULL,
  `contact_number` VARCHAR(20) DEFAULT NULL,
  `email` VARCHAR(100) DEFAULT NULL,
  `manager_id` INT DEFAULT NULL COMMENT 'Employee ID of branch manager',
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `created_by` INT DEFAULT NULL,
  `updated_by` INT DEFAULT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`branch_id`),
  UNIQUE KEY `unique_branch_code` (`branch_code`),
  KEY `idx_manager` (`manager_id`),
  KEY `idx_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Step 2: Add branch_id to employees table
ALTER TABLE `employees` 
ADD COLUMN `branch_id` INT DEFAULT NULL AFTER `department_id`,
ADD KEY `idx_branch` (`branch_id`);

-- Step 3: Add foreign key constraints (optional, can be removed if causing issues)
-- ALTER TABLE `employees` 
-- ADD CONSTRAINT `fk_employees_branch` 
-- FOREIGN KEY (`branch_id`) REFERENCES `branches` (`branch_id`) 
-- ON DELETE SET NULL ON UPDATE CASCADE;

-- ALTER TABLE `branches` 
-- ADD CONSTRAINT `fk_branches_manager` 
-- FOREIGN KEY (`manager_id`) REFERENCES `employees` (`employee_id`) 
-- ON DELETE SET NULL ON UPDATE CASCADE;

-- Step 4: Insert sample branches (optional)
INSERT INTO `branches` (`branch_name`, `branch_code`, `address`, `contact_number`, `email`, `is_active`, `created_by`) VALUES
('Main Office', 'MAIN-01', 'Carmen, Cagayan de Oro City', '09569260774', 'main@wonderpetscdo.dev', 1, 1),
('Branch 1 - Downtown', 'CDO-02', 'Downtown, Cagayan de Oro City', '09123456789', 'downtown@wonderpetscdo.dev', 1, 1),
('Branch 2 - Uptown', 'CDO-03', 'Uptown, Cagayan de Oro City', '09234567890', 'uptown@wonderpetscdo.dev', 1, 1);

-- ========================================
-- Rollback Script (if needed)
-- ========================================
-- ALTER TABLE `employees` DROP FOREIGN KEY `fk_employees_branch`;
-- ALTER TABLE `branches` DROP FOREIGN KEY `fk_branches_manager`;
-- ALTER TABLE `employees` DROP COLUMN `branch_id`;
-- DROP TABLE IF EXISTS `branches`;
