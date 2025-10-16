-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Oct 16, 2025 at 02:45 PM
-- Server version: 8.0.41
-- PHP Version: 8.3.16

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `emp`
--

-- --------------------------------------------------------

--
-- Table structure for table `allowance`
--

CREATE TABLE `allowance` (
  `allowance_id` int NOT NULL,
  `allowance_type` varchar(100) NOT NULL,
  `description` text,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_by` int DEFAULT NULL,
  `updated_by` int DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `allowance`
--

INSERT INTO `allowance` (`allowance_id`, `allowance_type`, `description`, `is_active`, `created_by`, `updated_by`, `created_at`, `updated_at`) VALUES
(3, 'PhilHealth', 'PhilHealth Insurance', 1, NULL, NULL, '2025-10-02 20:46:39', '2025-10-02 20:46:39'),
(4, 'Food Allowance', 'cash or meal stubs to help with daily food expenses', 1, NULL, NULL, '2025-10-02 20:47:31', '2025-10-02 20:47:31'),
(5, 'Housing Allowance', 'expat packages', 1, NULL, 1, '2025-10-02 21:03:57', '2025-10-02 21:15:06'),
(11, 'Miscellaneous', 'can include hazard pay (for risky jobs), education allowance, or dependents’ allowance (rare but exists in big firms).', 1, NULL, NULL, '2025-10-02 21:22:57', '2025-10-02 21:22:57');

-- --------------------------------------------------------

--
-- Table structure for table `applicants`
--

CREATE TABLE `applicants` (
  `applicant_id` int NOT NULL,
  `first_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `middle_name` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `last_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password_hash` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `alternative_phone` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address` text COLLATE utf8mb4_unicode_ci,
  `city` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `state` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `zip_code` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `date_of_birth` date DEFAULT NULL,
  `gender` enum('Male','Female','Other') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `position_applied` int DEFAULT NULL,
  `branch_applied` int DEFAULT NULL,
  `department_applied` int DEFAULT NULL,
  `resume_path` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cover_letter` text COLLATE utf8mb4_unicode_ci,
  `application_date` datetime DEFAULT CURRENT_TIMESTAMP,
  `status` enum('pending','reviewing','interview_scheduled','interviewed','accepted','hired','rejected','withdrawn') COLLATE utf8mb4_unicode_ci DEFAULT 'pending',
  `interview_date` datetime DEFAULT NULL,
  `interview_notes` text COLLATE utf8mb4_unicode_ci,
  `interviewer_id` int DEFAULT NULL,
  `hr_notes` text COLLATE utf8mb4_unicode_ci,
  `skills` text COLLATE utf8mb4_unicode_ci,
  `experience_years` decimal(4,2) DEFAULT NULL,
  `expected_salary` decimal(12,2) DEFAULT NULL,
  `available_start_date` date DEFAULT NULL,
  `reference_name` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `reference_contact` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `reference_relationship` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT '1',
  `email_verified` tinyint(1) DEFAULT '0',
  `verification_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `last_login` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `applicants`
--

INSERT INTO `applicants` (`applicant_id`, `first_name`, `middle_name`, `last_name`, `email`, `password_hash`, `phone`, `alternative_phone`, `address`, `city`, `state`, `zip_code`, `date_of_birth`, `gender`, `position_applied`, `branch_applied`, `department_applied`, `resume_path`, `cover_letter`, `application_date`, `status`, `interview_date`, `interview_notes`, `interviewer_id`, `hr_notes`, `skills`, `experience_years`, `expected_salary`, `available_start_date`, `reference_name`, `reference_contact`, `reference_relationship`, `is_active`, `email_verified`, `verification_token`, `last_login`, `created_at`, `updated_at`) VALUES
(4, 'Meghan', 'Byron Ortiz', 'Molina', 'jazicej@mailinator.com', '$2y$10$6fPz/w5v34V3etjK0nXlOOuKcCvo5fFDj1q50s36MHfO5n4t8yAny', '+1 (432) 841-9926', '+1 (722) 479-6436', 'Laboris ut qui ab ip', 'Modi voluptas omnis', 'Dolorem earum et nih', '85574', '1986-02-11', 'Female', 12, 5, 3, 'uploads/applicants/resume_1760615619_68f0dcc31b7fb.docx', 'Eum cupidatat in aut', '2025-10-16 19:53:39', 'accepted', NULL, NULL, NULL, NULL, 'Consectetur ipsum mo', 5.00, 15000.00, '1992-08-13', 'Neville Wilson', 'Eius dolore nisi at', 'Commodo ea est non e', 1, 0, '7e92d45833d68eecdad96f723315a7480e4d76748270380b69cad14ab53124b3', NULL, '2025-10-16 11:53:39', '2025-10-16 12:40:33'),
(5, 'Byron', 'Mcfarland', 'Wilkins', 'dancethenightaway.kr@gmail.com', '$2y$10$QzwYk2kqFK2JDoZXZiyB1.ZVRx7JSKYRH99JixHim5.eJrK9aHoxy', '+1 (975) 826-8905', '+1 (269) 224-1198', 'Quam aut tempora qui', 'Illo quia sed ducimu', 'Quas occaecat repreh', '34492', '1991-09-24', 'Male', 3, 1, 2, 'uploads/applicants/resume_1760619891_68f0ed737172b.docx', 'Vel voluptatem aliq', '2025-10-16 21:04:51', 'reviewing', NULL, NULL, NULL, NULL, 'Tenetur magni magna', 7.00, 25000.00, '2025-10-31', 'Giselle Preston', 'Qui officiis in et u', 'Labore repellendus', 1, 0, '271a87c4507a28339aa5c3146984757dca0508076bab0329bb02db434e0d3b03', NULL, '2025-10-16 13:04:51', '2025-10-16 13:49:24');

-- --------------------------------------------------------

--
-- Table structure for table `applicant_activity_log`
--

CREATE TABLE `applicant_activity_log` (
  `log_id` int NOT NULL,
  `applicant_id` int NOT NULL,
  `action` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `old_status` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `new_status` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `performed_by` int DEFAULT NULL,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `applicant_activity_log`
--

INSERT INTO `applicant_activity_log` (`log_id`, `applicant_id`, `action`, `old_status`, `new_status`, `performed_by`, `notes`, `created_at`) VALUES
(1, 4, 'registration', NULL, 'pending', NULL, 'Applicant registered', '2025-10-16 11:53:39'),
(2, 3, 'status_change', 'interview_scheduled', 'accepted', 1, '', '2025-10-16 12:33:07'),
(3, 2, 'status_change', 'reviewing', 'accepted', 1, 'Nice', '2025-10-16 12:38:02'),
(4, 1, 'status_change', 'pending', 'accepted', 1, '', '2025-10-16 12:39:41'),
(5, 4, 'status_change', 'pending', 'accepted', 1, '', '2025-10-16 12:40:33'),
(6, 5, 'registration', NULL, 'pending', NULL, 'Applicant registered', '2025-10-16 13:04:51'),
(7, 5, 'status_change', 'pending', 'reviewing', 1, 'Under Review', '2025-10-16 13:31:37'),
(8, 5, 'status_change', 'pending', 'reviewing', 1, 'Your application has been received and is currently being reviewed by our hiring team. We’ll reach out to you once there’s an update regarding the next steps.', '2025-10-16 13:45:08'),
(9, 5, 'status_change', 'pending', 'reviewing', 1, 'Your application has been received and is currently being reviewed by our hiring team. We’ll reach out to you once there’s an update regarding the next steps.', '2025-10-16 13:49:24');

-- --------------------------------------------------------

--
-- Table structure for table `applicant_documents`
--

CREATE TABLE `applicant_documents` (
  `document_id` int NOT NULL,
  `applicant_id` int NOT NULL,
  `document_type` enum('resume','cover_letter','certificate','id_proof','portfolio','other') COLLATE utf8mb4_unicode_ci NOT NULL,
  `document_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `document_path` varchar(500) COLLATE utf8mb4_unicode_ci NOT NULL,
  `file_size` int DEFAULT NULL,
  `uploaded_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `attendance`
--

CREATE TABLE `attendance` (
  `attendance_id` int NOT NULL,
  `employee_id` int NOT NULL,
  `schedule_id` int DEFAULT NULL,
  `calendar_id` int DEFAULT NULL,
  `date` date NOT NULL,
  `time_in` time DEFAULT NULL,
  `time_out` time DEFAULT NULL,
  `status` enum('Present','Absent','Late','On Leave','Overtime') CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `remarks` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `attendance`
--

INSERT INTO `attendance` (`attendance_id`, `employee_id`, `schedule_id`, `calendar_id`, `date`, `time_in`, `time_out`, `status`, `remarks`) VALUES
(44, 12, 3582, 340, '2025-10-06', '07:55:47', '12:08:00', 'Present', 'Time in via DTR system - AM session - Morning time in - Lunch break out'),
(47, 12, 3582, 340, '2025-10-06', '12:18:44', '19:19:00', 'Present', 'Time in via DTR system - PM session - Afternoon time in - Lunch break out - Overtime: 2h 19m beyond scheduled end time');

-- --------------------------------------------------------

--
-- Table structure for table `attendance_log`
--

CREATE TABLE `attendance_log` (
  `log_id` int NOT NULL,
  `attendance_id` int NOT NULL,
  `time_in` time DEFAULT NULL,
  `time_out` time DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `attendance_log`
--

INSERT INTO `attendance_log` (`log_id`, `attendance_id`, `time_in`, `time_out`) VALUES
(40, 44, '07:55:47', '12:08:00'),
(43, 47, '12:18:44', '19:19:00');

-- --------------------------------------------------------

--
-- Table structure for table `branches`
--

CREATE TABLE `branches` (
  `branch_id` int NOT NULL,
  `branch_name` varchar(100) NOT NULL,
  `branch_code` varchar(20) DEFAULT NULL COMMENT 'Short code for branch (e.g., CDO-01)',
  `address` text,
  `contact_number` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `manager_id` int DEFAULT NULL COMMENT 'Employee ID of branch manager',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_by` int DEFAULT NULL,
  `updated_by` int DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `branches`
--

INSERT INTO `branches` (`branch_id`, `branch_name`, `branch_code`, `address`, `contact_number`, `email`, `manager_id`, `is_active`, `created_by`, `updated_by`, `created_at`, `updated_at`) VALUES
(1, 'Main Office', 'MAIN-01', 'Carmen, Cagayan de Oro City', '09569260774', 'main@wonderpetscdo.dev', NULL, 1, 1, NULL, '2025-10-15 19:55:58', '2025-10-15 19:55:58'),
(5, 'SM Downtown Branch', 'CDO-Downtown-002', 'Claro M. Recto Ave, Cagayan De Oro City, 9000 Misamis Oriental', '09569260777', 'wonderpets_official@gmail.com', NULL, 1, 1, NULL, '2025-10-16 05:12:37', '2025-10-16 05:12:37');

-- --------------------------------------------------------

--
-- Table structure for table `company_info`
--

CREATE TABLE `company_info` (
  `company_id` int NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `address` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `contact_number` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `website` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `logo` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `company_info`
--

INSERT INTO `company_info` (`company_id`, `name`, `address`, `email`, `contact_number`, `website`, `logo`, `created_at`, `updated_at`) VALUES
(1, 'Wonder Pets', 'Carmen, Cagayan de Oro City', 'wonderpets_official@gmail.com', '09569260774', 'https://wonderpetscdo.dev', 'logo_1760528665_68ef89190d32a.png', '2025-09-24 20:07:45', '2025-10-16 21:03:08');

-- --------------------------------------------------------

--
-- Table structure for table `deduction_type`
--

CREATE TABLE `deduction_type` (
  `deduction_type_id` int NOT NULL,
  `type_name` varchar(100) NOT NULL,
  `amount_type` enum('fixed','percentage') DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `statutory` tinyint NOT NULL DEFAULT '0',
  `created_by` int NOT NULL,
  `updated_by` int DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT NULL,
  `is_dynamic` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0 = fixed/recurring, 1 = computed per payroll'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `deduction_type`
--

INSERT INTO `deduction_type` (`deduction_type_id`, `type_name`, `amount_type`, `is_active`, `statutory`, `created_by`, `updated_by`, `created_at`, `updated_at`, `is_dynamic`) VALUES
(3, 'r00tk1t', 'fixed', 1, 0, 1, 1, '2025-09-30 21:46:47', '2025-10-04 23:20:49', 0),
(4, 'Bulad Pinikas', 'fixed', 0, 1, 1, 1, '2025-09-30 21:48:29', '2025-09-30 21:48:29', 0),
(5, 'SUPPANGGA', 'fixed', 0, 0, 1, 1, '2025-09-30 21:51:58', '2025-09-30 21:51:58', 0),
(6, 'Pag-IBIG', 'fixed', 1, 0, 1, 1, '2025-10-03 20:25:05', '2025-10-04 22:44:22', 0),
(7, 'PhilHealth', 'fixed', 0, 0, 1, 1, '2025-10-03 20:25:51', '2025-10-03 20:25:51', 0),
(8, 'SSS', 'fixed', 1, 0, 1, 1, '2025-10-03 20:28:11', '2025-10-04 22:52:33', 0),
(9, 'Taxes', 'percentage', 1, 0, 1, 1, '2025-10-03 20:30:34', '2025-10-05 07:11:26', 0),
(10, 'Loan', 'percentage', 0, 0, 1, 1, '2025-10-03 20:32:31', '2025-10-03 20:32:31', 0),
(11, 'Late Deduction', 'fixed', 1, 0, 1, 1, '2025-10-03 23:01:14', '2025-10-04 22:43:16', 1);

-- --------------------------------------------------------

--
-- Table structure for table `department`
--

CREATE TABLE `department` (
  `department_id` int NOT NULL,
  `department_name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `department`
--

INSERT INTO `department` (`department_id`, `department_name`) VALUES
(1, 'Human Resources'),
(2, 'Finance'),
(3, 'Accounting'),
(4, 'Information Technology'),
(5, 'Administration'),
(6, 'Procurement'),
(7, 'Customer Service'),
(8, 'Sales'),
(9, 'Marketing'),
(10, 'Legal'),
(11, 'Operations'),
(12, 'Logistics'),
(13, 'Research and Development'),
(14, 'Engineering'),
(15, 'Quality Assurance'),
(16, 'Production'),
(17, 'Training and Development'),
(18, 'Public Relations'),
(19, 'Health and Safety'),
(20, 'Executive Management');

-- --------------------------------------------------------

--
-- Table structure for table `employees`
--

CREATE TABLE `employees` (
  `employee_id` int NOT NULL,
  `applicant_id` int DEFAULT NULL,
  `user_id` int NOT NULL,
  `department_id` int DEFAULT NULL,
  `branch_id` int DEFAULT NULL,
  `position_id` int DEFAULT NULL,
  `first_name` varchar(100) NOT NULL,
  `middle_name` varchar(100) DEFAULT NULL,
  `last_name` varchar(100) NOT NULL,
  `gender` enum('male','female') NOT NULL,
  `birthdate` date DEFAULT NULL,
  `contact_number` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `email` varchar(255) NOT NULL,
  `image` varchar(500) DEFAULT NULL,
  `basic_salary` decimal(10,2) NOT NULL,
  `employment_status` tinyint(1) NOT NULL DEFAULT '1',
  `date_hired` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `employees`
--

INSERT INTO `employees` (`employee_id`, `applicant_id`, `user_id`, `department_id`, `branch_id`, `position_id`, `first_name`, `middle_name`, `last_name`, `gender`, `birthdate`, `contact_number`, `email`, `image`, `basic_salary`, `employment_status`, `date_hired`) VALUES
(1, NULL, 1, NULL, NULL, NULL, 'Mary Elle', NULL, 'Fanning', 'male', NULL, '8700', 'tomorrow_elle@gmail.com', 'https://avatar.iran.liara.run/public/boy?username=maryelle', 14000.00, 1, '2025-09-30'),
(12, NULL, 14, 1, 5, 6, 'Christopher', 'Myles Talley', 'Porter', 'male', '1999-03-25', '8700', 'guro@mailinator.com', 'https://avatar.iran.liara.run/public/boy?username=christopherporter543', 14000.00, 1, '2025-10-06'),
(13, NULL, 15, 11, 1, 10, 'Nicholas', 'Savannah Molina', 'Aguirre', 'female', '2005-05-29', '9922444', 'ciqahulyta@mailinator.com', 'https://avatar.iran.liara.run/public/girl?username=nicholasaguirre560', 28000.00, 1, '2025-10-16'),
(14, NULL, 17, 3, NULL, 4, 'Meghan', 'Byron Ortiz', 'Molina', 'female', '1986-02-11', '+1 (432) 841-9926', 'jazicej@mailinator.com', 'https://avatar.iran.liara.run/public/girl?username=meghanmolina386', 14000.00, 1, '2025-10-16');

-- --------------------------------------------------------

--
-- Table structure for table `employee_allowance`
--

CREATE TABLE `employee_allowance` (
  `employee_allowance_id` int NOT NULL,
  `employee_id` int NOT NULL,
  `allowance_id` int NOT NULL,
  `allowance_amount` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `employee_allowance`
--

INSERT INTO `employee_allowance` (`employee_allowance_id`, `employee_id`, `allowance_id`, `allowance_amount`) VALUES
(34, 12, 3, 46.00),
(35, 12, 4, 500.00),
(36, 12, 11, 7.00);

-- --------------------------------------------------------

--
-- Table structure for table `employee_deduction`
--

CREATE TABLE `employee_deduction` (
  `employee_deduction_id` int NOT NULL,
  `employee_id` int NOT NULL,
  `deduction_type_id` int NOT NULL,
  `amount` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `employee_deduction`
--

INSERT INTO `employee_deduction` (`employee_deduction_id`, `employee_id`, `deduction_type_id`, `amount`) VALUES
(80, 12, 3, 54.00),
(81, 12, 8, 788.00),
(82, 12, 9, 16.00),
(86, 14, 6, 150.00),
(87, 14, 8, 200.00),
(88, 14, 9, 2.00);

-- --------------------------------------------------------

--
-- Table structure for table `employee_schedule`
--

CREATE TABLE `employee_schedule` (
  `schedule_id` int NOT NULL,
  `employee_id` int NOT NULL,
  `work_date` date NOT NULL,
  `shift_in` time DEFAULT NULL,
  `shift_out` time DEFAULT NULL,
  `is_rest_day` tinyint(1) NOT NULL DEFAULT '0',
  `calendar_id` int DEFAULT NULL COMMENT 'link to working_calendar',
  `notes` varchar(255) DEFAULT NULL,
  `created_by` int DEFAULT NULL,
  `updated_by` int DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `employee_schedule`
--

INSERT INTO `employee_schedule` (`schedule_id`, `employee_id`, `work_date`, `shift_in`, `shift_out`, `is_rest_day`, `calendar_id`, `notes`, `created_by`, `updated_by`, `created_at`, `updated_at`) VALUES
(3521, 1, '2025-10-01', '08:00:00', '17:00:00', 0, 310, 'Auto-assigned working day', 1, NULL, '2025-10-06 08:49:11', '2025-10-06 08:49:11'),
(3522, 12, '2025-10-01', '08:00:00', '17:00:00', 0, 310, 'Auto-assigned working day', 1, NULL, '2025-10-06 08:49:11', '2025-10-06 08:49:11'),
(3523, 1, '2025-10-02', '08:00:00', '17:00:00', 0, 311, 'Auto-assigned working day', 1, NULL, '2025-10-06 08:49:11', '2025-10-06 08:49:11'),
(3524, 12, '2025-10-02', '08:00:00', '17:00:00', 0, 311, 'Auto-assigned working day', 1, NULL, '2025-10-06 08:49:11', '2025-10-06 08:49:11'),
(3525, 1, '2025-10-03', '08:00:00', '17:00:00', 0, 312, 'Auto-assigned working day', 1, NULL, '2025-10-06 08:49:11', '2025-10-06 08:49:11'),
(3526, 12, '2025-10-03', '08:00:00', '17:00:00', 0, 312, 'Auto-assigned working day', 1, NULL, '2025-10-06 08:49:11', '2025-10-06 08:49:11'),
(3527, 1, '2025-10-06', '08:00:00', '17:00:00', 0, 313, 'Auto-assigned working day', 1, NULL, '2025-10-06 08:49:11', '2025-10-06 08:49:11'),
(3528, 12, '2025-10-06', '08:00:00', '17:00:00', 0, 313, 'Auto-assigned working day', 1, NULL, '2025-10-06 08:49:11', '2025-10-06 08:49:11'),
(3529, 1, '2025-10-07', '08:00:00', '17:00:00', 0, 314, 'Auto-assigned working day', 1, NULL, '2025-10-06 08:49:11', '2025-10-06 08:49:11'),
(3530, 12, '2025-10-07', '08:00:00', '17:00:00', 0, 314, 'Auto-assigned working day', 1, NULL, '2025-10-06 08:49:11', '2025-10-06 08:49:11'),
(3531, 1, '2025-10-08', '08:00:00', '17:00:00', 0, 315, 'Auto-assigned working day', 1, NULL, '2025-10-06 08:49:11', '2025-10-06 08:49:11'),
(3532, 12, '2025-10-08', '08:00:00', '17:00:00', 0, 315, 'Auto-assigned working day', 1, NULL, '2025-10-06 08:49:11', '2025-10-06 08:49:11'),
(3533, 1, '2025-10-09', '08:00:00', '17:00:00', 0, 316, 'Auto-assigned working day', 1, NULL, '2025-10-06 08:49:11', '2025-10-06 08:49:11'),
(3534, 12, '2025-10-09', '08:00:00', '17:00:00', 0, 316, 'Auto-assigned working day', 1, NULL, '2025-10-06 08:49:11', '2025-10-06 08:49:11'),
(3535, 1, '2025-10-10', '08:00:00', '17:00:00', 0, 317, 'Auto-assigned working day', 1, NULL, '2025-10-06 08:49:11', '2025-10-06 08:49:11'),
(3536, 12, '2025-10-10', '08:00:00', '17:00:00', 0, 317, 'Auto-assigned working day', 1, NULL, '2025-10-06 08:49:11', '2025-10-06 08:49:11'),
(3537, 1, '2025-10-13', '08:00:00', '17:00:00', 0, 318, 'Auto-assigned working day', 1, NULL, '2025-10-06 08:49:11', '2025-10-06 08:49:11'),
(3538, 12, '2025-10-13', '08:00:00', '17:00:00', 0, 318, 'Auto-assigned working day', 1, NULL, '2025-10-06 08:49:11', '2025-10-06 08:49:11'),
(3539, 1, '2025-10-14', '08:00:00', '17:00:00', 0, 319, 'Auto-assigned working day', 1, NULL, '2025-10-06 08:49:11', '2025-10-06 08:49:11'),
(3540, 12, '2025-10-14', '08:00:00', '17:00:00', 0, 319, 'Auto-assigned working day', 1, NULL, '2025-10-06 08:49:11', '2025-10-06 08:49:11'),
(3541, 1, '2025-10-15', '08:00:00', '17:00:00', 0, 320, 'Auto-assigned working day', 1, NULL, '2025-10-06 08:49:11', '2025-10-06 08:49:11'),
(3542, 12, '2025-10-15', '08:00:00', '17:00:00', 0, 320, 'Auto-assigned working day', 1, NULL, '2025-10-06 08:49:11', '2025-10-06 08:49:11'),
(3543, 1, '2025-10-16', '08:00:00', '17:00:00', 0, 321, 'Auto-assigned working day', 1, NULL, '2025-10-06 08:49:11', '2025-10-06 08:49:11'),
(3544, 12, '2025-10-16', '08:00:00', '17:00:00', 0, 321, 'Auto-assigned working day', 1, NULL, '2025-10-06 08:49:11', '2025-10-06 08:49:11'),
(3545, 1, '2025-10-17', '08:00:00', '17:00:00', 0, 322, 'Auto-assigned working day', 1, NULL, '2025-10-06 08:49:11', '2025-10-06 08:49:11'),
(3546, 12, '2025-10-17', '08:00:00', '17:00:00', 0, 322, 'Auto-assigned working day', 1, NULL, '2025-10-06 08:49:11', '2025-10-06 08:49:11'),
(3547, 1, '2025-10-20', '08:00:00', '17:00:00', 0, 323, 'Auto-assigned working day', 1, NULL, '2025-10-06 08:49:11', '2025-10-06 08:49:11'),
(3548, 12, '2025-10-20', '08:00:00', '17:00:00', 0, 323, 'Auto-assigned working day', 1, NULL, '2025-10-06 08:49:11', '2025-10-06 08:49:11'),
(3549, 1, '2025-10-21', '08:00:00', '17:00:00', 0, 324, 'Auto-assigned working day', 1, NULL, '2025-10-06 08:49:11', '2025-10-06 08:49:11'),
(3550, 12, '2025-10-21', '08:00:00', '17:00:00', 0, 324, 'Auto-assigned working day', 1, NULL, '2025-10-06 08:49:11', '2025-10-06 08:49:11'),
(3551, 1, '2025-10-22', '08:00:00', '17:00:00', 0, 325, 'Auto-assigned working day', 1, NULL, '2025-10-06 08:49:11', '2025-10-06 08:49:11'),
(3552, 12, '2025-10-22', '08:00:00', '17:00:00', 0, 325, 'Auto-assigned working day', 1, NULL, '2025-10-06 08:49:11', '2025-10-06 08:49:11'),
(3553, 1, '2025-10-23', '08:00:00', '17:00:00', 0, 326, 'Auto-assigned working day', 1, NULL, '2025-10-06 08:49:11', '2025-10-06 08:49:11'),
(3554, 12, '2025-10-23', '08:00:00', '17:00:00', 0, 326, 'Auto-assigned working day', 1, NULL, '2025-10-06 08:49:11', '2025-10-06 08:49:11'),
(3555, 1, '2025-10-24', '08:00:00', '17:00:00', 0, 327, 'Auto-assigned working day', 1, NULL, '2025-10-06 08:49:11', '2025-10-06 08:49:11'),
(3556, 12, '2025-10-24', '08:00:00', '17:00:00', 0, 327, 'Auto-assigned working day', 1, NULL, '2025-10-06 08:49:11', '2025-10-06 08:49:11'),
(3557, 1, '2025-10-27', '08:00:00', '17:00:00', 0, 328, 'Auto-assigned working day', 1, NULL, '2025-10-06 08:49:11', '2025-10-06 08:49:11'),
(3558, 12, '2025-10-27', '08:00:00', '17:00:00', 0, 328, 'Auto-assigned working day', 1, NULL, '2025-10-06 08:49:11', '2025-10-06 08:49:11'),
(3559, 1, '2025-10-28', '08:00:00', '17:00:00', 0, 329, 'Auto-assigned working day', 1, NULL, '2025-10-06 08:49:11', '2025-10-06 08:49:11'),
(3560, 12, '2025-10-28', '08:00:00', '17:00:00', 0, 329, 'Auto-assigned working day', 1, NULL, '2025-10-06 08:49:11', '2025-10-06 08:49:11'),
(3561, 1, '2025-10-29', '08:00:00', '17:00:00', 0, 330, 'Auto-assigned working day', 1, NULL, '2025-10-06 08:49:11', '2025-10-06 08:49:11'),
(3562, 12, '2025-10-29', '08:00:00', '17:00:00', 0, 330, 'Auto-assigned working day', 1, NULL, '2025-10-06 08:49:11', '2025-10-06 08:49:11'),
(3563, 1, '2025-10-30', '08:00:00', '17:00:00', 0, 331, 'Auto-assigned working day', 1, NULL, '2025-10-06 08:49:11', '2025-10-06 08:49:11'),
(3564, 12, '2025-10-30', '08:00:00', '17:00:00', 0, 331, 'Auto-assigned working day', 1, NULL, '2025-10-06 08:49:11', '2025-10-06 08:49:11'),
(3565, 1, '2025-10-31', '08:00:00', '17:00:00', 0, 332, 'Auto-assigned working day', 1, NULL, '2025-10-06 08:49:11', '2025-10-06 08:49:11'),
(3566, 12, '2025-10-31', '08:00:00', '17:00:00', 0, 332, 'Auto-assigned working day', 1, NULL, '2025-10-06 08:49:11', '2025-10-06 08:49:11'),
(3567, 1, '2025-10-04', NULL, NULL, 1, 333, 'Auto-assigned rest day', 1, NULL, '2025-10-06 08:49:11', '2025-10-06 08:49:11'),
(3568, 12, '2025-10-04', NULL, NULL, 1, 333, 'Auto-assigned rest day', 1, NULL, '2025-10-06 08:49:11', '2025-10-06 08:49:11'),
(3569, 1, '2025-10-05', NULL, NULL, 1, 334, 'Auto-assigned rest day', 1, NULL, '2025-10-06 08:49:11', '2025-10-06 08:49:11'),
(3570, 12, '2025-10-05', NULL, NULL, 1, 334, 'Auto-assigned rest day', 1, NULL, '2025-10-06 08:49:11', '2025-10-06 08:49:11'),
(3571, 1, '2025-10-11', NULL, NULL, 1, 335, 'Auto-assigned rest day', 1, NULL, '2025-10-06 08:49:11', '2025-10-06 08:49:11'),
(3572, 12, '2025-10-11', NULL, NULL, 1, 335, 'Auto-assigned rest day', 1, NULL, '2025-10-06 08:49:11', '2025-10-06 08:49:11'),
(3573, 1, '2025-10-12', NULL, NULL, 1, 336, 'Auto-assigned rest day', 1, NULL, '2025-10-06 08:49:11', '2025-10-06 08:49:11'),
(3574, 12, '2025-10-12', NULL, NULL, 1, 336, 'Auto-assigned rest day', 1, NULL, '2025-10-06 08:49:11', '2025-10-06 08:49:11'),
(3575, 1, '2025-10-18', NULL, NULL, 1, 337, 'Auto-assigned rest day', 1, NULL, '2025-10-06 08:49:11', '2025-10-06 08:49:11'),
(3576, 12, '2025-10-18', NULL, NULL, 1, 337, 'Auto-assigned rest day', 1, NULL, '2025-10-06 08:49:11', '2025-10-06 08:49:11'),
(3577, 1, '2025-10-19', NULL, NULL, 1, 338, 'Auto-assigned rest day', 1, NULL, '2025-10-06 08:49:11', '2025-10-06 08:49:11'),
(3578, 12, '2025-10-19', NULL, NULL, 1, 338, 'Auto-assigned rest day', 1, NULL, '2025-10-06 08:49:11', '2025-10-06 08:49:11'),
(3579, 1, '2025-10-25', NULL, NULL, 1, 339, 'Auto-assigned rest day', 1, NULL, '2025-10-06 08:49:11', '2025-10-06 08:49:11'),
(3580, 12, '2025-10-25', NULL, NULL, 1, 339, 'Auto-assigned rest day', 1, NULL, '2025-10-06 08:49:11', '2025-10-06 08:49:11'),
(3581, 1, '2025-10-26', NULL, NULL, 1, 340, 'Auto-assigned rest day', 1, NULL, '2025-10-06 08:49:11', '2025-10-06 08:49:11'),
(3582, 12, '2025-10-26', NULL, NULL, 1, 340, 'Auto-assigned rest day', 1, NULL, '2025-10-06 08:49:11', '2025-10-06 08:49:11'),
(3583, 13, '2025-10-16', '08:00:00', '17:00:00', 0, 321, 'Auto-assigned working day', 1, NULL, '2025-10-16 05:37:53', '2025-10-16 05:37:53'),
(3584, 13, '2025-10-17', '08:00:00', '17:00:00', 0, 322, 'Auto-assigned working day', 1, NULL, '2025-10-16 05:37:53', '2025-10-16 05:37:53'),
(3585, 13, '2025-10-18', NULL, NULL, 1, 337, 'Company rest day', 1, NULL, '2025-10-16 05:37:53', '2025-10-16 05:37:53'),
(3586, 13, '2025-10-19', NULL, NULL, 1, 338, 'Company rest day', 1, NULL, '2025-10-16 05:37:53', '2025-10-16 05:37:53'),
(3587, 13, '2025-10-20', '08:00:00', '17:00:00', 0, 323, 'Auto-assigned working day', 1, NULL, '2025-10-16 05:37:53', '2025-10-16 05:37:53'),
(3588, 13, '2025-10-21', '08:00:00', '17:00:00', 0, 324, 'Auto-assigned working day', 1, NULL, '2025-10-16 05:37:53', '2025-10-16 05:37:53'),
(3589, 13, '2025-10-22', '08:00:00', '17:00:00', 0, 325, 'Auto-assigned working day', 1, NULL, '2025-10-16 05:37:53', '2025-10-16 05:37:53'),
(3590, 13, '2025-10-23', '08:00:00', '17:00:00', 0, 326, 'Auto-assigned working day', 1, NULL, '2025-10-16 05:37:53', '2025-10-16 05:37:53'),
(3591, 13, '2025-10-24', '08:00:00', '17:00:00', 0, 327, 'Auto-assigned working day', 1, NULL, '2025-10-16 05:37:53', '2025-10-16 05:37:53'),
(3592, 13, '2025-10-25', NULL, NULL, 1, 339, 'Company rest day', 1, NULL, '2025-10-16 05:37:53', '2025-10-16 05:37:53'),
(3593, 13, '2025-10-26', NULL, NULL, 1, 340, 'Company rest day', 1, NULL, '2025-10-16 05:37:53', '2025-10-16 05:37:53'),
(3594, 13, '2025-10-27', '08:00:00', '17:00:00', 0, 328, 'Auto-assigned working day', 1, NULL, '2025-10-16 05:37:53', '2025-10-16 05:37:53'),
(3595, 13, '2025-10-28', '08:00:00', '17:00:00', 0, 329, 'Auto-assigned working day', 1, NULL, '2025-10-16 05:37:53', '2025-10-16 05:37:53'),
(3596, 13, '2025-10-29', '08:00:00', '17:00:00', 0, 330, 'Auto-assigned working day', 1, NULL, '2025-10-16 05:37:53', '2025-10-16 05:37:53'),
(3597, 13, '2025-10-30', '08:00:00', '17:00:00', 0, 331, 'Auto-assigned working day', 1, NULL, '2025-10-16 05:37:53', '2025-10-16 05:37:53'),
(3598, 13, '2025-10-31', '08:00:00', '17:00:00', 0, 332, 'Auto-assigned working day', 1, NULL, '2025-10-16 05:37:53', '2025-10-16 05:37:53'),
(3599, 14, '2025-10-16', '08:00:00', '17:00:00', 0, 321, 'Auto-assigned working day', 1, NULL, '2025-10-16 20:59:43', '2025-10-16 20:59:43'),
(3600, 14, '2025-10-17', '08:00:00', '17:00:00', 0, 322, 'Auto-assigned working day', 1, NULL, '2025-10-16 20:59:43', '2025-10-16 20:59:43'),
(3601, 14, '2025-10-18', NULL, NULL, 1, 337, 'Company rest day', 1, NULL, '2025-10-16 20:59:43', '2025-10-16 20:59:43'),
(3602, 14, '2025-10-19', NULL, NULL, 1, 338, 'Company rest day', 1, NULL, '2025-10-16 20:59:43', '2025-10-16 20:59:43'),
(3603, 14, '2025-10-20', '08:00:00', '17:00:00', 0, 323, 'Auto-assigned working day', 1, NULL, '2025-10-16 20:59:43', '2025-10-16 20:59:43'),
(3604, 14, '2025-10-21', '08:00:00', '17:00:00', 0, 324, 'Auto-assigned working day', 1, NULL, '2025-10-16 20:59:43', '2025-10-16 20:59:43'),
(3605, 14, '2025-10-22', '08:00:00', '17:00:00', 0, 325, 'Auto-assigned working day', 1, NULL, '2025-10-16 20:59:43', '2025-10-16 20:59:43'),
(3606, 14, '2025-10-23', '08:00:00', '17:00:00', 0, 326, 'Auto-assigned working day', 1, NULL, '2025-10-16 20:59:43', '2025-10-16 20:59:43'),
(3607, 14, '2025-10-24', '08:00:00', '17:00:00', 0, 327, 'Auto-assigned working day', 1, NULL, '2025-10-16 20:59:43', '2025-10-16 20:59:43'),
(3608, 14, '2025-10-25', NULL, NULL, 1, 339, 'Company rest day', 1, NULL, '2025-10-16 20:59:43', '2025-10-16 20:59:43'),
(3609, 14, '2025-10-26', NULL, NULL, 1, 340, 'Company rest day', 1, NULL, '2025-10-16 20:59:43', '2025-10-16 20:59:43'),
(3610, 14, '2025-10-27', '08:00:00', '17:00:00', 0, 328, 'Auto-assigned working day', 1, NULL, '2025-10-16 20:59:43', '2025-10-16 20:59:43'),
(3611, 14, '2025-10-28', '08:00:00', '17:00:00', 0, 329, 'Auto-assigned working day', 1, NULL, '2025-10-16 20:59:43', '2025-10-16 20:59:43'),
(3612, 14, '2025-10-29', '08:00:00', '17:00:00', 0, 330, 'Auto-assigned working day', 1, NULL, '2025-10-16 20:59:43', '2025-10-16 20:59:43'),
(3613, 14, '2025-10-30', '08:00:00', '17:00:00', 0, 331, 'Auto-assigned working day', 1, NULL, '2025-10-16 20:59:43', '2025-10-16 20:59:43'),
(3614, 14, '2025-10-31', '08:00:00', '17:00:00', 0, 332, 'Auto-assigned working day', 1, NULL, '2025-10-16 20:59:43', '2025-10-16 20:59:43');

-- --------------------------------------------------------

--
-- Table structure for table `job_position`
--

CREATE TABLE `job_position` (
  `position_id` int NOT NULL,
  `position_name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `job_position`
--

INSERT INTO `job_position` (`position_id`, `position_name`) VALUES
(1, 'HR Manager'),
(2, 'HR Assistant'),
(3, 'Finance Manager'),
(4, 'Accountant'),
(5, 'IT Support Specialist'),
(6, 'Systems Administrator'),
(7, 'Administrative Assistant'),
(8, 'Procurement Officer'),
(9, 'Customer Service Representative'),
(10, 'Sales Executive'),
(11, 'Marketing Specialist'),
(12, 'Legal Officer'),
(13, 'Operations Supervisor'),
(14, 'Logistics Coordinator'),
(15, 'R&D Analyst'),
(16, 'Mechanical Engineer'),
(17, 'Quality Assurance Inspector'),
(18, 'Production Worker'),
(19, 'Training Officer'),
(20, 'Public Relations Officer');

-- --------------------------------------------------------

--
-- Table structure for table `leave_balances`
--

CREATE TABLE `leave_balances` (
  `balance_id` int NOT NULL,
  `employee_id` int NOT NULL,
  `year` int NOT NULL,
  `vacation_total` int DEFAULT '15',
  `sick_total` int DEFAULT '10',
  `personal_total` int DEFAULT '5',
  `emergency_total` int DEFAULT '30',
  `maternity_total` int DEFAULT '90',
  `paternity_total` int DEFAULT '7',
  `vacation_used` int DEFAULT '0',
  `sick_used` int DEFAULT '0',
  `personal_used` int DEFAULT '0',
  `emergency_used` int DEFAULT '0',
  `maternity_used` int DEFAULT '0',
  `paternity_used` int DEFAULT '0',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `leave_balances`
--

INSERT INTO `leave_balances` (`balance_id`, `employee_id`, `year`, `vacation_total`, `sick_total`, `personal_total`, `emergency_total`, `maternity_total`, `paternity_total`, `vacation_used`, `sick_used`, `personal_used`, `emergency_used`, `maternity_used`, `paternity_used`, `created_at`, `updated_at`) VALUES
(1, 1, 2025, 15, 10, 5, 30, 90, 7, 0, 0, 0, 0, 0, 0, '2025-10-06 22:52:27', '2025-10-06 22:52:27'),
(2, 12, 2025, 15, 10, 5, 30, 90, 7, 0, 0, 0, 0, 0, 0, '2025-10-06 22:52:27', '2025-10-06 22:52:27');

-- --------------------------------------------------------

--
-- Table structure for table `leave_records`
--

CREATE TABLE `leave_records` (
  `leave_id` int NOT NULL,
  `employee_id` int NOT NULL,
  `leave_type` enum('Sick','Vacation','Emergency','Maternity','Paternity','Personal') NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `status` enum('Pending','Approved','Rejected') DEFAULT 'Pending',
  `approved_by` int DEFAULT NULL,
  `reason` text,
  `comments` text,
  `half_day` tinyint(1) DEFAULT '0',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `overtime_records`
--

CREATE TABLE `overtime_records` (
  `overtime_id` int NOT NULL,
  `employee_id` int NOT NULL,
  `date` date NOT NULL,
  `hours` int NOT NULL,
  `rate` decimal(10,2) NOT NULL,
  `approved_by` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `overtime_records`
--

INSERT INTO `overtime_records` (`overtime_id`, `employee_id`, `date`, `hours`, `rate`, `approved_by`) VALUES
(2, 12, '2025-10-06', 2, 233.89, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `payroll`
--

CREATE TABLE `payroll` (
  `payroll_id` int NOT NULL,
  `employee_id` int NOT NULL,
  `period_start` date NOT NULL,
  `period_end` date NOT NULL,
  `basic_salary` decimal(10,2) NOT NULL,
  `net_pay` decimal(10,2) NOT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `payroll_allowance`
--

CREATE TABLE `payroll_allowance` (
  `payroll_allowance_id` int NOT NULL,
  `payroll_id` int NOT NULL,
  `allowance_id` int NOT NULL,
  `amount` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `payroll_deduction`
--

CREATE TABLE `payroll_deduction` (
  `payroll_deduction_id` int NOT NULL,
  `payroll_id` int NOT NULL,
  `deduction_type_id` int NOT NULL,
  `amount` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `performance`
--

CREATE TABLE `performance` (
  `performance_id` int NOT NULL,
  `employee_id` int NOT NULL,
  `period_start` date NOT NULL,
  `period_end` date NOT NULL,
  `rating` int NOT NULL,
  `remarks` text,
  `evaluated_by` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `system_logs`
--

CREATE TABLE `system_logs` (
  `log_id` int NOT NULL,
  `user_id` int NOT NULL,
  `action_performed` enum('LOGIN','LOGOUT','CREATE','UPDATE','DELETE') NOT NULL,
  `full_description` text NOT NULL,
  `date_performed` datetime DEFAULT CURRENT_TIMESTAMP,
  `ip_address` varchar(45) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `system_logs`
--

INSERT INTO `system_logs` (`log_id`, `user_id`, `action_performed`, `full_description`, `date_performed`, `ip_address`) VALUES
(1, 1, 'UPDATE', '', '2025-09-28 14:33:09', '127.0.0.1'),
(2, 1, 'UPDATE', '', '2025-09-28 14:38:15', '127.0.0.1'),
(3, 1, 'UPDATE', '', '2025-09-28 14:42:58', '127.0.0.1'),
(4, 1, 'CREATE', 'Mary Elle Fanning CREATED Deduction Type: r00tk1t - Created deduction id 3', '2025-09-30 21:46:47', '127.0.0.1'),
(5, 1, 'CREATE', 'Mary Elle Fanning CREATED Deduction Type: Bulad Pinikas - Created deduction id 4', '2025-09-30 21:48:29', '127.0.0.1'),
(6, 1, 'CREATE', 'Mary Elle Fanning CREATED Deduction Type: SUPPANGGA - Created deduction id 5', '2025-09-30 21:51:58', '127.0.0.1'),
(7, 1, 'DELETE', 'Mary Elle Fanning DELETED Deduction Type: ID: 5', '2025-09-30 22:05:42', '127.0.0.1'),
(8, 1, 'DELETE', 'Mary Elle Fanning DELETED Deduction Type: ID: 5', '2025-09-30 22:06:22', '127.0.0.1'),
(9, 1, 'DELETE', 'Mary Elle Fanning DELETED Deduction Type: ID: 4', '2025-09-30 22:09:01', '127.0.0.1'),
(10, 1, 'DELETE', 'Mary Elle Fanning DELETED Employee Deduction: ID: 10 - The deduction \"Bulad Pinikas\" has been removed from Peter Kyla Burch Buchanan', '2025-10-01 21:54:42', '127.0.0.1'),
(11, 1, 'DELETE', 'Mary Elle Fanning DELETED Employee Deduction: ID: 6 - The deduction \"r00tk1t\" has been removed from Naomi Maxwell Obrien Shannon', '2025-10-01 22:02:29', '127.0.0.1'),
(12, 1, 'DELETE', 'Mary Elle Fanning DELETED Employee Deduction: ID: 2 - The deduction \"r00tk1t\" has been removed from Daniel Joseph Mendoza', '2025-10-01 22:02:45', '127.0.0.1'),
(13, 1, 'DELETE', 'Mary Elle Fanning DELETED Employee Deduction: ID: 3 - The deduction \"r00tk1t\" has been removed from Clarissa  Dela Cruz', '2025-10-01 22:02:53', '127.0.0.1'),
(14, 1, 'CREATE', 'Mary Elle Fanning CREATED Allowance: SMOKE Test Allowance 68dd3a01c27bf - Created allowance id 1', '2025-10-01 22:26:09', 'Unknown'),
(15, 1, 'DELETE', 'Mary Elle Fanning DELETED Allowance: ID: 1', '2025-10-01 22:27:25', '127.0.0.1'),
(16, 1, 'DELETE', 'Mary Elle Fanning DELETED Allowance: ID: 1', '2025-10-01 22:27:43', '127.0.0.1'),
(17, 1, 'CREATE', 'Mary Elle Fanning CREATED Allowance: Transportation Allowance - Created allowance id 2', '2025-10-01 22:44:05', '127.0.0.1'),
(18, 1, 'CREATE', 'Mary Elle Fanning CREATED Allowance: PhilHealth - Created allowance id 3', '2025-10-02 20:46:39', '127.0.0.1'),
(19, 1, 'CREATE', 'Mary Elle Fanning CREATED Allowance: Food Allowance - Created allowance id 4', '2025-10-02 20:47:31', '127.0.0.1'),
(20, 1, 'CREATE', 'Mary Elle Fanning CREATED Allowance: Housing Allowance - Created allowance id 5', '2025-10-02 21:03:57', '127.0.0.1'),
(21, 1, 'DELETE', 'Mary Elle Fanning DELETED Employee Allowance: ID: 25 - The allowance \"Housing Allowance\" has been removed from Paul Daayata Sho', '2025-10-02 21:04:56', '127.0.0.1'),
(22, 1, 'DELETE', 'Mary Elle Fanning DELETED Employee Allowance: ID: 26 - The allowance \"Housing Allowance\" has been removed from Clarissa  Dela Cruz', '2025-10-02 21:04:58', '127.0.0.1'),
(23, 1, 'DELETE', 'Mary Elle Fanning DELETED Employee Allowance: ID: 27 - The allowance \"Housing Allowance\" has been removed from Peter Kyla Burch Buchanan', '2025-10-02 21:05:02', '127.0.0.1'),
(24, 1, 'UPDATE', 'Mary Elle Fanning UPDATED Allowance: Housing Allowance - Updated allowance id 5', '2025-10-02 21:05:25', '127.0.0.1'),
(25, 1, 'UPDATE', 'Mary Elle Fanning UPDATED Allowance: Housing Allowance - Updated allowance id 5', '2025-10-02 21:15:06', '127.0.0.1'),
(26, 1, 'CREATE', 'Mary Elle Fanning CREATED Allowance: Miscellaneous - Created allowance id 7', '2025-10-02 21:16:21', '127.0.0.1'),
(27, 1, 'CREATE', 'Mary Elle Fanning CREATED Allowance: Miscellaneous - Created allowance id 8', '2025-10-02 21:16:21', '127.0.0.1'),
(28, 1, 'CREATE', 'Mary Elle Fanning CREATED Allowance: Hazard Pay - Created allowance id 9', '2025-10-02 21:17:07', '127.0.0.1'),
(29, 1, 'CREATE', 'Mary Elle Fanning CREATED Allowance: Hazard Pay - Created allowance id 10', '2025-10-02 21:17:07', '127.0.0.1'),
(30, 1, 'CREATE', 'Mary Elle Fanning CREATED Allowance: Miscellaneous - Created allowance id 11', '2025-10-02 21:22:57', '127.0.0.1'),
(31, 1, 'UPDATE', 'Failed to add employee - Email: vateh@mailinator.com', '2025-10-02 22:12:13', '127.0.0.1'),
(32, 1, 'UPDATE', 'Failed to add employee - Email: ronoseto@mailinator.com', '2025-10-02 22:17:15', '127.0.0.1'),
(33, 1, 'CREATE', 'Mary Elle Fanning created employee record for Sigourney Ruiz - Employee added to system', '2025-10-02 22:25:33', '127.0.0.1'),
(34, 1, 'CREATE', 'Mary Elle Fanning created employee record for Cade Cannon - Employee added to system', '2025-10-02 22:49:36', '127.0.0.1'),
(35, 1, 'CREATE', 'Mary Elle Fanning CREATED Deduction Type: Pag-IBIG - Created deduction id 6', '2025-10-03 20:25:05', '127.0.0.1'),
(36, 1, 'CREATE', 'Mary Elle Fanning CREATED Deduction Type: PhilHealth - Created deduction id 7', '2025-10-03 20:25:51', '127.0.0.1'),
(37, 1, 'CREATE', 'Mary Elle Fanning CREATED Deduction Type: SSS - Created deduction id 8', '2025-10-03 20:28:11', '127.0.0.1'),
(38, 1, 'CREATE', 'Mary Elle Fanning CREATED Deduction Type: Tax - Created deduction id 9', '2025-10-03 20:30:34', '127.0.0.1'),
(39, 1, 'CREATE', 'Mary Elle Fanning CREATED Deduction Type: Loan - Created deduction id 10', '2025-10-03 20:32:31', '127.0.0.1'),
(40, 1, 'UPDATE', 'Mary Elle Fanning UPDATED employee record for Courtney Atkins - Updated assignment id 48', '2025-10-03 21:17:21', '127.0.0.1'),
(41, 1, 'UPDATE', 'Mary Elle Fanning UPDATED employee record for Paul Sho - Updated allowance \"Miscellaneous\" for Paul Daayata Sho', '2025-10-03 21:46:54', '127.0.0.1'),
(42, 1, 'CREATE', 'Mary Elle Fanning CREATED Deduction Type: Late Deduction - Created deduction id 11', '2025-10-03 23:01:14', '127.0.0.1'),
(43, 1, 'UPDATE', 'Mary Elle Fanning UPDATED Deduction Type: Late Deduction - Updated deduction id 11', '2025-10-03 23:01:25', '127.0.0.1'),
(44, 1, 'UPDATE', 'Mary Elle Fanning UPDATED Deduction Type: Late Deduction - Updated deduction id 11', '2025-10-03 23:02:48', '127.0.0.1'),
(45, 1, 'UPDATE', 'Mary Elle Fanning UPDATED Deduction Type: Late Deduction - Updated deduction id 11', '2025-10-03 23:03:02', '127.0.0.1'),
(46, 1, 'UPDATE', 'Mary Elle Fanning UPDATED Deduction Type: Late Deduction - Updated deduction id 11', '2025-10-03 23:06:38', '127.0.0.1'),
(47, 1, 'UPDATE', 'Mary Elle Fanning UPDATED Deduction Type: Late Deduction - Updated deduction id 11', '2025-10-03 23:07:11', '127.0.0.1'),
(48, 1, 'UPDATE', 'Mary Elle Fanning UPDATED Deduction Type: Late Deduction - Updated deduction id 11', '2025-10-03 23:07:19', '127.0.0.1'),
(49, 1, 'UPDATE', 'Mary Elle Fanning UPDATED Deduction Type: Late Deduction - Updated deduction id 11', '2025-10-03 23:15:20', '127.0.0.1'),
(50, 1, 'UPDATE', 'Mary Elle Fanning UPDATED Deduction Type: Late Deduction - Updated deduction id 11', '2025-10-03 23:15:57', '127.0.0.1'),
(51, 1, 'UPDATE', 'Mary Elle Fanning UPDATED Deduction Type: Late Deduction - Updated deduction id 11', '2025-10-03 23:21:10', '127.0.0.1'),
(52, 1, 'UPDATE', 'Mary Elle Fanning UPDATED Deduction Type: Late Deduction - Updated deduction id 11', '2025-10-03 23:21:24', '127.0.0.1'),
(53, 1, 'UPDATE', 'Mary Elle Fanning UPDATED Deduction Type: Late Deduction - Updated deduction id 11', '2025-10-04 21:57:34', '127.0.0.1'),
(54, 1, 'UPDATE', 'Mary Elle Fanning UPDATED Deduction Type: Late Deduction - Updated deduction id 11', '2025-10-04 21:58:06', '127.0.0.1'),
(55, 1, 'UPDATE', 'Mary Elle Fanning UPDATED Deduction Type: Loan - Updated deduction id 10', '2025-10-04 21:58:46', '127.0.0.1'),
(56, 1, 'UPDATE', 'Mary Elle Fanning UPDATED Deduction Type: Late Deduction - Updated deduction id 11', '2025-10-04 22:02:23', '127.0.0.1'),
(57, 1, 'UPDATE', 'Mary Elle Fanning UPDATED Deduction Type: Loan - Updated deduction id 10', '2025-10-04 22:02:53', '127.0.0.1'),
(58, 1, 'UPDATE', 'Mary Elle Fanning UPDATED Deduction Type: Loan - Updated deduction id 10', '2025-10-04 22:04:09', '127.0.0.1'),
(59, 1, 'UPDATE', 'Mary Elle Fanning UPDATED Deduction Type: Loan - Updated deduction id 10', '2025-10-04 22:05:07', '127.0.0.1'),
(60, 1, 'UPDATE', 'Mary Elle Fanning UPDATED Deduction Type: Loan - Updated deduction id 10', '2025-10-04 22:10:48', '127.0.0.1'),
(61, 1, 'DELETE', 'Mary Elle Fanning DELETED Deduction Type: ID: 10', '2025-10-04 22:11:12', '127.0.0.1'),
(62, 1, 'UPDATE', 'Mary Elle Fanning UPDATED Deduction Type: Tax - Updated deduction id 9', '2025-10-04 22:11:23', '127.0.0.1'),
(63, 1, 'UPDATE', 'Mary Elle Fanning UPDATED Deduction Type: Tax - Updated deduction id 9', '2025-10-04 22:16:50', '127.0.0.1'),
(64, 1, 'UPDATE', 'Mary Elle Fanning UPDATED Deduction Type: Late Deduction - Updated deduction id 11', '2025-10-04 22:17:08', '127.0.0.1'),
(65, 1, 'UPDATE', 'Mary Elle Fanning UPDATED Deduction Type: Late Deduction - Updated deduction id 11', '2025-10-04 22:20:12', '127.0.0.1'),
(66, 1, 'UPDATE', 'Mary Elle Fanning UPDATED Deduction Type: Tax - Updated deduction id 9', '2025-10-04 22:24:52', '127.0.0.1'),
(67, 1, 'UPDATE', 'Mary Elle Fanning UPDATED Deduction Type: Tax - Updated deduction id 9', '2025-10-04 22:26:32', '127.0.0.1'),
(68, 1, 'UPDATE', 'Mary Elle Fanning UPDATED Deduction Type: Tax - Updated deduction id 9', '2025-10-04 22:27:33', '127.0.0.1'),
(69, 1, 'UPDATE', 'Mary Elle Fanning UPDATED Deduction Type: Tax - Updated deduction id 9', '2025-10-04 22:29:01', '127.0.0.1'),
(70, 1, 'UPDATE', 'Mary Elle Fanning UPDATED Deduction Type: Tax - Updated deduction id 9', '2025-10-04 22:29:42', '127.0.0.1'),
(71, 1, 'UPDATE', 'Mary Elle Fanning UPDATED Deduction Type: Tax - Updated deduction id 9', '2025-10-04 22:30:48', '127.0.0.1'),
(72, 1, 'UPDATE', 'Mary Elle Fanning UPDATED Deduction Type: Tax - Updated deduction id 9', '2025-10-04 22:33:31', '127.0.0.1'),
(73, 1, 'UPDATE', 'Mary Elle Fanning UPDATED Deduction Type: Tax - Updated deduction id 9', '2025-10-04 22:34:14', '127.0.0.1'),
(74, 1, 'UPDATE', 'Mary Elle Fanning UPDATED Deduction Type: Tax - Updated deduction id 9', '2025-10-04 22:40:21', '127.0.0.1'),
(75, 1, 'UPDATE', 'Mary Elle Fanning UPDATED Deduction Type: Tax - Updated deduction id 9', '2025-10-04 22:40:31', '127.0.0.1'),
(76, 1, 'UPDATE', 'Mary Elle Fanning UPDATED Deduction Type: Late Deduction - Updated deduction id 11', '2025-10-04 22:40:44', '127.0.0.1'),
(77, 1, 'UPDATE', 'Mary Elle Fanning UPDATED Deduction Type: Late Deduction - Updated deduction id 11', '2025-10-04 22:40:57', '127.0.0.1'),
(78, 1, 'UPDATE', 'Mary Elle Fanning UPDATED Deduction Type: Late Deduction - Updated deduction id 11', '2025-10-04 22:43:05', '127.0.0.1'),
(79, 1, 'UPDATE', 'Mary Elle Fanning UPDATED Deduction Type: Late Deduction - Updated deduction id 11', '2025-10-04 22:43:16', '127.0.0.1'),
(80, 1, 'UPDATE', 'Mary Elle Fanning UPDATED Deduction Type: Pag-IBIG - Updated deduction id 6', '2025-10-04 22:43:33', '127.0.0.1'),
(81, 1, 'UPDATE', 'Mary Elle Fanning UPDATED Deduction Type: Pag-IBIG - Updated deduction id 6', '2025-10-04 22:44:22', '127.0.0.1'),
(82, 1, 'UPDATE', 'Mary Elle Fanning UPDATED Deduction Type: Tax - Updated deduction id 9', '2025-10-04 22:49:45', '127.0.0.1'),
(83, 1, 'UPDATE', 'Mary Elle Fanning UPDATED Deduction Type: SSS - Updated deduction id 8', '2025-10-04 22:50:05', '127.0.0.1'),
(84, 1, 'UPDATE', 'Mary Elle Fanning UPDATED Deduction Type: Tax - Updated deduction id 9', '2025-10-04 22:52:05', '127.0.0.1'),
(85, 1, 'UPDATE', 'Mary Elle Fanning UPDATED Deduction Type: SSS - Updated deduction id 8', '2025-10-04 22:52:33', '127.0.0.1'),
(86, 1, 'DELETE', 'Mary Elle Fanning DELETED Deduction Type: ID: 7', '2025-10-04 22:54:06', '127.0.0.1'),
(87, 1, 'CREATE', 'Mary Elle Fanning created employee record for Audra Huff - Employee added to system', '2025-10-04 23:16:37', '127.0.0.1'),
(88, 1, 'UPDATE', 'Mary Elle Fanning UPDATED Deduction Type: r00tk1t - Updated deduction id 3', '2025-10-04 23:20:49', '127.0.0.1'),
(89, 1, 'UPDATE', 'Mary Elle Fanning UPDATED Deduction Type: Taxes - Updated deduction id 9', '2025-10-05 06:52:26', '127.0.0.1'),
(90, 1, 'UPDATE', 'Mary Elle Fanning UPDATED Deduction Type: Taxes - Updated deduction id 9', '2025-10-05 06:52:39', '127.0.0.1'),
(91, 1, 'UPDATE', 'Mary Elle Fanning UPDATED Deduction Type: Taxes - Updated deduction id 9', '2025-10-05 06:56:32', '127.0.0.1'),
(92, 1, 'UPDATE', 'Mary Elle Fanning UPDATED Deduction Type: Taxes - Updated deduction id 9', '2025-10-05 06:56:49', '127.0.0.1'),
(93, 1, 'UPDATE', 'Mary Elle Fanning UPDATED Deduction Type: Taxes - Updated deduction id 9', '2025-10-05 06:58:47', '127.0.0.1'),
(94, 1, 'UPDATE', 'Mary Elle Fanning UPDATED Deduction Type: Taxes - Updated deduction id 9', '2025-10-05 07:04:08', '127.0.0.1'),
(95, 1, 'UPDATE', 'Mary Elle Fanning UPDATED Deduction Type: Taxes - Updated deduction id 9', '2025-10-05 07:04:21', '127.0.0.1'),
(96, 1, 'UPDATE', 'Mary Elle Fanning UPDATED Deduction Type: Taxes - Updated deduction id 9', '2025-10-05 07:04:42', '127.0.0.1'),
(97, 1, 'UPDATE', 'Mary Elle Fanning UPDATED Deduction Type: Taxes - Updated deduction id 9', '2025-10-05 07:05:15', '127.0.0.1'),
(98, 1, 'UPDATE', 'Mary Elle Fanning UPDATED Deduction Type: Taxes - Updated deduction id 9', '2025-10-05 07:11:26', '127.0.0.1'),
(99, 1, 'UPDATE', 'Mary Elle Fanning reset password for user account for Naomi Shannon - Password was successfully reset', '2025-10-05 10:33:50', '127.0.0.1'),
(100, 1, 'UPDATE', 'Mary Elle Fanning reset password for user account for Cade Cannon - Password was successfully reset', '2025-10-05 11:07:46', '127.0.0.1'),
(101, 1, 'UPDATE', 'Mary Elle Fanning reset password for user account for Audra Huff - Password was successfully reset', '2025-10-06 20:24:37', '127.0.0.1'),
(102, 1, 'CREATE', 'Mary Elle Fanning created employee record for Christopher Porter - Employee added to system', '2025-10-06 08:46:36', '127.0.0.1'),
(103, 1, 'UPDATE', 'Mary Elle Fanning reset password for user account for Christopher Porter - Password was successfully reset', '2025-10-06 08:47:31', '127.0.0.1'),
(104, 1, 'LOGIN', 'Mary Elle Fanning LOGIN', '2025-10-15 19:10:12', '127.0.0.1'),
(105, 1, 'UPDATE', 'Company information updated - Name: Wonder Pets', '2025-10-15 19:43:55', '127.0.0.1'),
(106, 1, 'UPDATE', 'Company information updated - Name: Wonder Pets', '2025-10-15 19:44:01', '127.0.0.1'),
(107, 1, 'UPDATE', 'Company information updated - Name: Wonder Pets', '2025-10-15 19:44:25', '127.0.0.1'),
(108, 1, 'LOGIN', 'Mary Elle Fanning LOGIN', '2025-10-16 04:50:47', '127.0.0.1'),
(109, 1, 'CREATE', 'Mary Elle Fanning created branch: SM Uptown Branch', '2025-10-16 05:09:40', '127.0.0.1'),
(110, 1, 'CREATE', 'Mary Elle Fanning created branch: SM Downtown Branch', '2025-10-16 05:12:37', '127.0.0.1'),
(111, 1, 'DELETE', 'Mary Elle Fanning deleted branch: Branch 1 - Downtown', '2025-10-16 05:18:10', '127.0.0.1'),
(112, 1, 'DELETE', 'Mary Elle Fanning deleted branch: Branch 2 - Uptown', '2025-10-16 05:18:45', '127.0.0.1'),
(113, 1, 'DELETE', 'Mary Elle Fanning deleted branch: SM Uptown Branch', '2025-10-16 05:18:56', '127.0.0.1'),
(114, 1, 'CREATE', 'Mary Elle Fanning created branch: SM Uptown Branch', '2025-10-16 05:19:43', '127.0.0.1'),
(115, 1, 'DELETE', 'Mary Elle Fanning deleted branch: SM Uptown Branch', '2025-10-16 05:20:08', '127.0.0.1'),
(116, 1, 'CREATE', 'Mary Elle Fanning created employee record for Nicholas Aguirre - Employee added to system', '2025-10-16 05:37:54', '127.0.0.1'),
(117, 1, 'UPDATE', 'Mary Elle Fanning updated employee record for Nicholas Aguirre - Employee information updated', '2025-10-16 05:41:44', '127.0.0.1'),
(118, 1, 'LOGIN', 'Mary Elle Fanning LOGIN', '2025-10-16 19:18:39', '127.0.0.1'),
(119, 1, 'UPDATE', 'Failed to add employee - Email: jazicej@mailinator.com', '2025-10-16 20:59:02', '127.0.0.1'),
(120, 1, 'CREATE', 'Mary Elle Fanning created employee record for Meghan Molina - Employee added to system', '2025-10-16 20:59:43', '127.0.0.1'),
(121, 1, 'UPDATE', 'Mary Elle Fanning updated user account for Christopher Porter - User account information updated', '2025-10-16 22:02:34', '127.0.0.1'),
(122, 14, 'LOGIN', 'Christopher Porter LOGIN', '2025-10-16 22:02:43', '127.0.0.1'),
(123, 14, 'LOGOUT', 'Christopher Porter LOGOUT', '2025-10-16 22:32:35', '127.0.0.1'),
(124, 14, 'LOGIN', 'Christopher Porter LOGIN', '2025-10-16 22:34:58', '127.0.0.1'),
(125, 14, 'LOGOUT', 'Christopher Porter LOGOUT', '2025-10-16 22:36:15', '127.0.0.1'),
(126, 14, 'LOGIN', 'Christopher Porter LOGIN', '2025-10-16 22:36:28', '127.0.0.1');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `user_type_id` int NOT NULL,
  `active_status` enum('active','locked') NOT NULL,
  `last_login` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `username`, `password`, `user_type_id`, `active_status`, `last_login`, `created_at`, `updated_at`) VALUES
(1, 'maryelle', '$2y$10$IKImRich3JaRfGYugjsCoeoMLuhjm4/p4EvGCgfxqOhL7Gx8S9B.2', 1, 'active', '2025-10-16 19:18:39', '2025-09-23 22:58:50', '2025-10-16 19:18:39'),
(14, 'christopherporter543', '$2y$10$m7lsVOAzxjK7wCtXl45PNeGhBfzYtGGxdzBVyRKT/wMswC0Ssat6K', 3, 'active', '2025-10-16 22:36:28', '2025-10-06 08:46:36', '2025-10-16 22:36:28'),
(15, 'nicholasaguirre560', '$2y$10$0s5YydruJaR/yJduslB3s.9AFQQRnIcSIYSsE5PZ.Ol/yJ8lK7X5q', 4, 'active', NULL, '2025-10-16 05:37:52', '2025-10-16 05:37:52'),
(17, 'meghanmolina386', '$2y$10$.w7FI3ci8HI0qhsIGnoKdOY7lmkRypeUvwQ9RfxHmzYde2KI5pRuK', 4, 'active', NULL, '2025-10-16 20:59:43', '2025-10-16 20:59:43');

-- --------------------------------------------------------

--
-- Table structure for table `user_type`
--

CREATE TABLE `user_type` (
  `user_type_id` int NOT NULL,
  `type_name` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `user_type`
--

INSERT INTO `user_type` (`user_type_id`, `type_name`) VALUES
(1, 'Admin'),
(2, 'Supervisor'),
(3, 'HR'),
(4, 'Employee');

-- --------------------------------------------------------

--
-- Stand-in structure for view `v_applicant_statistics`
-- (See below for the actual view)
--
CREATE TABLE `v_applicant_statistics` (
`accepted_count` decimal(23,0)
,`hired_count` decimal(23,0)
,`interview_scheduled_count` decimal(23,0)
,`interviewed_count` decimal(23,0)
,`month_applications` decimal(23,0)
,`pending_count` decimal(23,0)
,`rejected_count` decimal(23,0)
,`reviewing_count` decimal(23,0)
,`today_applications` decimal(23,0)
,`total_applicants` bigint
,`week_applications` decimal(23,0)
);

-- --------------------------------------------------------

--
-- Table structure for table `working_calendar`
--

CREATE TABLE `working_calendar` (
  `id` int NOT NULL,
  `work_date` date DEFAULT NULL COMMENT 'actual date',
  `day_of_week` tinyint NOT NULL COMMENT '1=Mon … 7=Sun (optional, for quick lookup)',
  `is_working` tinyint(1) NOT NULL DEFAULT '1' COMMENT '1=working day, 0=non-working',
  `is_holiday` tinyint(1) NOT NULL DEFAULT '0' COMMENT '1=holiday, 0=not holiday',
  `holiday_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL COMMENT 'for naming holidays like "Christmas"',
  `remarks` varchar(500) DEFAULT NULL COMMENT 'remarks (optional)',
  `created_by` int NOT NULL,
  `updated_by` int DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT NULL,
  `is_half_day` tinyint(1) DEFAULT '0' COMMENT 'Indicates if this is a half-day for the organization (0=full day, 1=half day)'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `working_calendar`
--

INSERT INTO `working_calendar` (`id`, `work_date`, `day_of_week`, `is_working`, `is_holiday`, `holiday_name`, `remarks`, `created_by`, `updated_by`, `created_at`, `updated_at`, `is_half_day`) VALUES
(310, '2025-10-01', 3, 1, 0, NULL, 'Working day - Auto assigned', 1, NULL, '2025-10-06 08:49:11', NULL, 0),
(311, '2025-10-02', 4, 1, 0, NULL, 'Working day - Auto assigned', 1, NULL, '2025-10-06 08:49:11', NULL, 0),
(312, '2025-10-03', 5, 1, 0, NULL, 'Working day - Auto assigned', 1, NULL, '2025-10-06 08:49:11', NULL, 0),
(313, '2025-10-06', 1, 1, 0, NULL, 'Working day - Auto assigned', 1, NULL, '2025-10-06 08:49:11', NULL, 0),
(314, '2025-10-07', 2, 1, 0, NULL, 'Working day - Auto assigned', 1, NULL, '2025-10-06 08:49:11', NULL, 0),
(315, '2025-10-08', 3, 1, 0, NULL, 'Working day - Auto assigned', 1, NULL, '2025-10-06 08:49:11', NULL, 0),
(316, '2025-10-09', 4, 1, 0, NULL, 'Working day - Auto assigned', 1, NULL, '2025-10-06 08:49:11', NULL, 0),
(317, '2025-10-10', 5, 1, 0, NULL, 'Working day - Auto assigned', 1, NULL, '2025-10-06 08:49:11', NULL, 0),
(318, '2025-10-13', 1, 1, 0, NULL, 'Working day - Auto assigned', 1, NULL, '2025-10-06 08:49:11', NULL, 0),
(319, '2025-10-14', 2, 1, 0, NULL, 'Working day - Auto assigned', 1, NULL, '2025-10-06 08:49:11', NULL, 0),
(320, '2025-10-15', 3, 1, 0, NULL, 'Working day - Auto assigned', 1, NULL, '2025-10-06 08:49:11', NULL, 0),
(321, '2025-10-16', 4, 1, 0, NULL, 'Working day - Auto assigned', 1, NULL, '2025-10-06 08:49:11', NULL, 0),
(322, '2025-10-17', 5, 1, 0, NULL, 'Working day - Auto assigned', 1, NULL, '2025-10-06 08:49:11', NULL, 0),
(323, '2025-10-20', 1, 1, 0, NULL, 'Working day - Auto assigned', 1, NULL, '2025-10-06 08:49:11', NULL, 0),
(324, '2025-10-21', 2, 1, 0, NULL, 'Working day - Auto assigned', 1, NULL, '2025-10-06 08:49:11', NULL, 0),
(325, '2025-10-22', 3, 1, 0, NULL, 'Working day - Auto assigned', 1, NULL, '2025-10-06 08:49:11', NULL, 0),
(326, '2025-10-23', 4, 1, 0, NULL, 'Working day - Auto assigned', 1, NULL, '2025-10-06 08:49:11', NULL, 0),
(327, '2025-10-24', 5, 1, 0, NULL, 'Working day - Auto assigned', 1, NULL, '2025-10-06 08:49:11', NULL, 0),
(328, '2025-10-27', 1, 1, 0, NULL, 'Working day - Auto assigned', 1, NULL, '2025-10-06 08:49:11', NULL, 0),
(329, '2025-10-28', 2, 1, 0, NULL, 'Working day - Auto assigned', 1, NULL, '2025-10-06 08:49:11', NULL, 0),
(330, '2025-10-29', 3, 1, 0, NULL, 'Working day - Auto assigned', 1, NULL, '2025-10-06 08:49:11', NULL, 0),
(331, '2025-10-30', 4, 1, 0, NULL, 'Working day - Auto assigned', 1, NULL, '2025-10-06 08:49:11', NULL, 0),
(332, '2025-10-31', 5, 1, 0, NULL, 'Working day - Auto assigned', 1, NULL, '2025-10-06 08:49:11', NULL, 0),
(333, '2025-10-04', 6, 0, 0, NULL, 'Non-working day - Auto assigned', 1, NULL, '2025-10-06 08:49:11', NULL, 0),
(334, '2025-10-05', 7, 0, 0, NULL, 'Non-working day - Auto assigned', 1, NULL, '2025-10-06 08:49:11', NULL, 0),
(335, '2025-10-11', 6, 0, 0, NULL, 'Non-working day - Auto assigned', 1, NULL, '2025-10-06 08:49:11', NULL, 0),
(336, '2025-10-12', 7, 0, 0, NULL, 'Non-working day - Auto assigned', 1, NULL, '2025-10-06 08:49:11', NULL, 0),
(337, '2025-10-18', 6, 0, 0, NULL, 'Non-working day - Auto assigned', 1, NULL, '2025-10-06 08:49:11', NULL, 0),
(338, '2025-10-19', 7, 0, 0, NULL, 'Non-working day - Auto assigned', 1, NULL, '2025-10-06 08:49:11', NULL, 0),
(339, '2025-10-25', 6, 0, 0, NULL, 'Non-working day - Auto assigned', 1, NULL, '2025-10-06 08:49:11', NULL, 0),
(340, '2025-10-26', 7, 0, 0, NULL, 'Non-working day - Auto assigned', 1, NULL, '2025-10-06 08:49:11', NULL, 0);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `allowance`
--
ALTER TABLE `allowance`
  ADD PRIMARY KEY (`allowance_id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `updated_by` (`updated_by`);

--
-- Indexes for table `applicants`
--
ALTER TABLE `applicants`
  ADD PRIMARY KEY (`applicant_id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `fk_applicant_department` (`department_applied`),
  ADD KEY `fk_applicant_interviewer` (`interviewer_id`),
  ADD KEY `idx_email` (`email`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_application_date` (`application_date`),
  ADD KEY `idx_position_applied` (`position_applied`),
  ADD KEY `idx_branch_applied` (`branch_applied`);

--
-- Indexes for table `applicant_activity_log`
--
ALTER TABLE `applicant_activity_log`
  ADD PRIMARY KEY (`log_id`),
  ADD KEY `fk_log_performer` (`performed_by`),
  ADD KEY `idx_applicant_log` (`applicant_id`,`created_at`);

--
-- Indexes for table `applicant_documents`
--
ALTER TABLE `applicant_documents`
  ADD PRIMARY KEY (`document_id`),
  ADD KEY `idx_applicant_documents` (`applicant_id`,`document_type`);

--
-- Indexes for table `attendance`
--
ALTER TABLE `attendance`
  ADD PRIMARY KEY (`attendance_id`),
  ADD KEY `employee_id` (`employee_id`),
  ADD KEY `fk_attendance_schedule` (`schedule_id`),
  ADD KEY `fk_attendance_calendar` (`calendar_id`);

--
-- Indexes for table `attendance_log`
--
ALTER TABLE `attendance_log`
  ADD PRIMARY KEY (`log_id`),
  ADD KEY `attendance_id` (`attendance_id`);

--
-- Indexes for table `branches`
--
ALTER TABLE `branches`
  ADD PRIMARY KEY (`branch_id`),
  ADD UNIQUE KEY `unique_branch_code` (`branch_code`),
  ADD KEY `idx_manager` (`manager_id`),
  ADD KEY `idx_active` (`is_active`);

--
-- Indexes for table `company_info`
--
ALTER TABLE `company_info`
  ADD PRIMARY KEY (`company_id`);

--
-- Indexes for table `deduction_type`
--
ALTER TABLE `deduction_type`
  ADD PRIMARY KEY (`deduction_type_id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `updated_by` (`updated_by`);

--
-- Indexes for table `department`
--
ALTER TABLE `department`
  ADD PRIMARY KEY (`department_id`);

--
-- Indexes for table `employees`
--
ALTER TABLE `employees`
  ADD PRIMARY KEY (`employee_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `department_id` (`department_id`),
  ADD KEY `position_id` (`position_id`),
  ADD KEY `idx_branch` (`branch_id`),
  ADD KEY `idx_applicant_id` (`applicant_id`);

--
-- Indexes for table `employee_allowance`
--
ALTER TABLE `employee_allowance`
  ADD PRIMARY KEY (`employee_allowance_id`),
  ADD KEY `employee_id` (`employee_id`),
  ADD KEY `allowance_id` (`allowance_id`);

--
-- Indexes for table `employee_deduction`
--
ALTER TABLE `employee_deduction`
  ADD PRIMARY KEY (`employee_deduction_id`),
  ADD KEY `employee_id` (`employee_id`),
  ADD KEY `deduction_type_id` (`deduction_type_id`);

--
-- Indexes for table `employee_schedule`
--
ALTER TABLE `employee_schedule`
  ADD PRIMARY KEY (`schedule_id`),
  ADD KEY `employee_id` (`employee_id`),
  ADD KEY `calendar_id` (`calendar_id`);

--
-- Indexes for table `job_position`
--
ALTER TABLE `job_position`
  ADD PRIMARY KEY (`position_id`);

--
-- Indexes for table `leave_balances`
--
ALTER TABLE `leave_balances`
  ADD PRIMARY KEY (`balance_id`),
  ADD UNIQUE KEY `employee_year` (`employee_id`,`year`),
  ADD KEY `employee_id` (`employee_id`);

--
-- Indexes for table `leave_records`
--
ALTER TABLE `leave_records`
  ADD PRIMARY KEY (`leave_id`),
  ADD KEY `employee_id` (`employee_id`),
  ADD KEY `approved_by` (`approved_by`);

--
-- Indexes for table `overtime_records`
--
ALTER TABLE `overtime_records`
  ADD PRIMARY KEY (`overtime_id`),
  ADD KEY `employee_id` (`employee_id`),
  ADD KEY `approved_by` (`approved_by`);

--
-- Indexes for table `payroll`
--
ALTER TABLE `payroll`
  ADD PRIMARY KEY (`payroll_id`),
  ADD KEY `employee_id` (`employee_id`);

--
-- Indexes for table `payroll_allowance`
--
ALTER TABLE `payroll_allowance`
  ADD PRIMARY KEY (`payroll_allowance_id`),
  ADD KEY `payroll_id` (`payroll_id`),
  ADD KEY `allowance_id` (`allowance_id`);

--
-- Indexes for table `payroll_deduction`
--
ALTER TABLE `payroll_deduction`
  ADD PRIMARY KEY (`payroll_deduction_id`),
  ADD KEY `payroll_id` (`payroll_id`),
  ADD KEY `deduction_type_id` (`deduction_type_id`);

--
-- Indexes for table `performance`
--
ALTER TABLE `performance`
  ADD PRIMARY KEY (`performance_id`),
  ADD KEY `employee_id` (`employee_id`),
  ADD KEY `evaluated_by` (`evaluated_by`);

--
-- Indexes for table `system_logs`
--
ALTER TABLE `system_logs`
  ADD PRIMARY KEY (`log_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD KEY `user_type_id` (`user_type_id`);

--
-- Indexes for table `user_type`
--
ALTER TABLE `user_type`
  ADD PRIMARY KEY (`user_type_id`);

--
-- Indexes for table `working_calendar`
--
ALTER TABLE `working_calendar`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `updated_by` (`updated_by`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `allowance`
--
ALTER TABLE `allowance`
  MODIFY `allowance_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `applicants`
--
ALTER TABLE `applicants`
  MODIFY `applicant_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `applicant_activity_log`
--
ALTER TABLE `applicant_activity_log`
  MODIFY `log_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `applicant_documents`
--
ALTER TABLE `applicant_documents`
  MODIFY `document_id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `attendance`
--
ALTER TABLE `attendance`
  MODIFY `attendance_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=48;

--
-- AUTO_INCREMENT for table `attendance_log`
--
ALTER TABLE `attendance_log`
  MODIFY `log_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=44;

--
-- AUTO_INCREMENT for table `branches`
--
ALTER TABLE `branches`
  MODIFY `branch_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `company_info`
--
ALTER TABLE `company_info`
  MODIFY `company_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `deduction_type`
--
ALTER TABLE `deduction_type`
  MODIFY `deduction_type_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `department`
--
ALTER TABLE `department`
  MODIFY `department_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `employees`
--
ALTER TABLE `employees`
  MODIFY `employee_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `employee_allowance`
--
ALTER TABLE `employee_allowance`
  MODIFY `employee_allowance_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=38;

--
-- AUTO_INCREMENT for table `employee_deduction`
--
ALTER TABLE `employee_deduction`
  MODIFY `employee_deduction_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=89;

--
-- AUTO_INCREMENT for table `employee_schedule`
--
ALTER TABLE `employee_schedule`
  MODIFY `schedule_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3615;

--
-- AUTO_INCREMENT for table `job_position`
--
ALTER TABLE `job_position`
  MODIFY `position_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `leave_balances`
--
ALTER TABLE `leave_balances`
  MODIFY `balance_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `leave_records`
--
ALTER TABLE `leave_records`
  MODIFY `leave_id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `overtime_records`
--
ALTER TABLE `overtime_records`
  MODIFY `overtime_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `payroll`
--
ALTER TABLE `payroll`
  MODIFY `payroll_id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `payroll_allowance`
--
ALTER TABLE `payroll_allowance`
  MODIFY `payroll_allowance_id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `payroll_deduction`
--
ALTER TABLE `payroll_deduction`
  MODIFY `payroll_deduction_id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `performance`
--
ALTER TABLE `performance`
  MODIFY `performance_id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `system_logs`
--
ALTER TABLE `system_logs`
  MODIFY `log_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=127;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `user_type`
--
ALTER TABLE `user_type`
  MODIFY `user_type_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `working_calendar`
--
ALTER TABLE `working_calendar`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=341;

-- --------------------------------------------------------

--
-- Structure for view `v_applicant_statistics`
--
DROP TABLE IF EXISTS `v_applicant_statistics`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_applicant_statistics`  AS SELECT count(0) AS `total_applicants`, sum((case when (`applicants`.`status` = 'pending') then 1 else 0 end)) AS `pending_count`, sum((case when (`applicants`.`status` = 'reviewing') then 1 else 0 end)) AS `reviewing_count`, sum((case when (`applicants`.`status` = 'interview_scheduled') then 1 else 0 end)) AS `interview_scheduled_count`, sum((case when (`applicants`.`status` = 'interviewed') then 1 else 0 end)) AS `interviewed_count`, sum((case when (`applicants`.`status` = 'accepted') then 1 else 0 end)) AS `accepted_count`, sum((case when (`applicants`.`status` = 'hired') then 1 else 0 end)) AS `hired_count`, sum((case when (`applicants`.`status` = 'rejected') then 1 else 0 end)) AS `rejected_count`, sum((case when (cast(`applicants`.`application_date` as date) = curdate()) then 1 else 0 end)) AS `today_applications`, sum((case when (cast(`applicants`.`application_date` as date) >= (curdate() - interval 7 day)) then 1 else 0 end)) AS `week_applications`, sum((case when (cast(`applicants`.`application_date` as date) >= (curdate() - interval 30 day)) then 1 else 0 end)) AS `month_applications` FROM `applicants` WHERE (`applicants`.`is_active` = true) ;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `allowance`
--
ALTER TABLE `allowance`
  ADD CONSTRAINT `allowance_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`user_id`),
  ADD CONSTRAINT `allowance_ibfk_2` FOREIGN KEY (`updated_by`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `applicants`
--
ALTER TABLE `applicants`
  ADD CONSTRAINT `fk_applicant_branch` FOREIGN KEY (`branch_applied`) REFERENCES `branches` (`branch_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_applicant_department` FOREIGN KEY (`department_applied`) REFERENCES `department` (`department_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_applicant_interviewer` FOREIGN KEY (`interviewer_id`) REFERENCES `employees` (`employee_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_applicant_position` FOREIGN KEY (`position_applied`) REFERENCES `job_position` (`position_id`) ON DELETE SET NULL;

--
-- Constraints for table `applicant_activity_log`
--
ALTER TABLE `applicant_activity_log`
  ADD CONSTRAINT `fk_log_applicant` FOREIGN KEY (`applicant_id`) REFERENCES `applicants` (`applicant_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_log_performer` FOREIGN KEY (`performed_by`) REFERENCES `employees` (`employee_id`) ON DELETE SET NULL;

--
-- Constraints for table `applicant_documents`
--
ALTER TABLE `applicant_documents`
  ADD CONSTRAINT `fk_document_applicant` FOREIGN KEY (`applicant_id`) REFERENCES `applicants` (`applicant_id`) ON DELETE CASCADE;

--
-- Constraints for table `attendance`
--
ALTER TABLE `attendance`
  ADD CONSTRAINT `attendance_ibfk_1` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`employee_id`),
  ADD CONSTRAINT `fk_attendance_calendar` FOREIGN KEY (`calendar_id`) REFERENCES `working_calendar` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_attendance_schedule` FOREIGN KEY (`schedule_id`) REFERENCES `employee_schedule` (`schedule_id`) ON DELETE SET NULL;

--
-- Constraints for table `attendance_log`
--
ALTER TABLE `attendance_log`
  ADD CONSTRAINT `attendance_log_ibfk_1` FOREIGN KEY (`attendance_id`) REFERENCES `attendance` (`attendance_id`) ON DELETE CASCADE ON UPDATE RESTRICT;

--
-- Constraints for table `deduction_type`
--
ALTER TABLE `deduction_type`
  ADD CONSTRAINT `deduction_type_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`user_id`),
  ADD CONSTRAINT `deduction_type_ibfk_2` FOREIGN KEY (`updated_by`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `employees`
--
ALTER TABLE `employees`
  ADD CONSTRAINT `employees_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`),
  ADD CONSTRAINT `employees_ibfk_2` FOREIGN KEY (`department_id`) REFERENCES `department` (`department_id`),
  ADD CONSTRAINT `employees_ibfk_3` FOREIGN KEY (`position_id`) REFERENCES `job_position` (`position_id`),
  ADD CONSTRAINT `employees_ibfk_4` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`branch_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_employee_applicant` FOREIGN KEY (`applicant_id`) REFERENCES `applicants` (`applicant_id`) ON DELETE SET NULL;

--
-- Constraints for table `employee_allowance`
--
ALTER TABLE `employee_allowance`
  ADD CONSTRAINT `employee_allowance_ibfk_1` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`employee_id`) ON DELETE CASCADE ON UPDATE RESTRICT,
  ADD CONSTRAINT `employee_allowance_ibfk_2` FOREIGN KEY (`allowance_id`) REFERENCES `allowance` (`allowance_id`) ON DELETE CASCADE;

--
-- Constraints for table `employee_deduction`
--
ALTER TABLE `employee_deduction`
  ADD CONSTRAINT `employee_deduction_ibfk_1` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`employee_id`) ON DELETE CASCADE ON UPDATE RESTRICT,
  ADD CONSTRAINT `employee_deduction_ibfk_2` FOREIGN KEY (`deduction_type_id`) REFERENCES `deduction_type` (`deduction_type_id`) ON DELETE CASCADE ON UPDATE RESTRICT;

--
-- Constraints for table `employee_schedule`
--
ALTER TABLE `employee_schedule`
  ADD CONSTRAINT `employee_schedule_ibfk_1` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`employee_id`),
  ADD CONSTRAINT `employee_schedule_ibfk_2` FOREIGN KEY (`calendar_id`) REFERENCES `working_calendar` (`id`);

--
-- Constraints for table `leave_balances`
--
ALTER TABLE `leave_balances`
  ADD CONSTRAINT `leave_balances_ibfk_1` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`employee_id`) ON DELETE CASCADE;

--
-- Constraints for table `leave_records`
--
ALTER TABLE `leave_records`
  ADD CONSTRAINT `leave_records_ibfk_1` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`employee_id`),
  ADD CONSTRAINT `leave_records_ibfk_2` FOREIGN KEY (`approved_by`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `overtime_records`
--
ALTER TABLE `overtime_records`
  ADD CONSTRAINT `overtime_records_ibfk_1` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`employee_id`),
  ADD CONSTRAINT `overtime_records_ibfk_2` FOREIGN KEY (`approved_by`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `payroll`
--
ALTER TABLE `payroll`
  ADD CONSTRAINT `payroll_ibfk_1` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`employee_id`);

--
-- Constraints for table `payroll_allowance`
--
ALTER TABLE `payroll_allowance`
  ADD CONSTRAINT `payroll_allowance_ibfk_1` FOREIGN KEY (`payroll_id`) REFERENCES `payroll` (`payroll_id`),
  ADD CONSTRAINT `payroll_allowance_ibfk_2` FOREIGN KEY (`allowance_id`) REFERENCES `allowance` (`allowance_id`);

--
-- Constraints for table `payroll_deduction`
--
ALTER TABLE `payroll_deduction`
  ADD CONSTRAINT `payroll_deduction_ibfk_1` FOREIGN KEY (`payroll_id`) REFERENCES `payroll` (`payroll_id`),
  ADD CONSTRAINT `payroll_deduction_ibfk_2` FOREIGN KEY (`deduction_type_id`) REFERENCES `deduction_type` (`deduction_type_id`);

--
-- Constraints for table `performance`
--
ALTER TABLE `performance`
  ADD CONSTRAINT `performance_ibfk_1` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`employee_id`),
  ADD CONSTRAINT `performance_ibfk_2` FOREIGN KEY (`evaluated_by`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `system_logs`
--
ALTER TABLE `system_logs`
  ADD CONSTRAINT `system_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_ibfk_1` FOREIGN KEY (`user_type_id`) REFERENCES `user_type` (`user_type_id`);

--
-- Constraints for table `working_calendar`
--
ALTER TABLE `working_calendar`
  ADD CONSTRAINT `working_calendar_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `working_calendar_ibfk_2` FOREIGN KEY (`updated_by`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
