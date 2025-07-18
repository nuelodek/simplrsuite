-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Sep 27, 2024 at 04:26 PM
-- Server version: 10.6.19-MariaDB
-- PHP Version: 8.1.29

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `simplrco_dashboard`
--

-- --------------------------------------------------------

--
-- Table structure for table `aboutusinfo`
--

CREATE TABLE `aboutusinfo` (
  `id` int(11) NOT NULL,
  `company_history` text DEFAULT NULL,
  `mission_statement` text DEFAULT NULL,
  `vision_statement` text DEFAULT NULL,
  `values` text DEFAULT NULL,
  `company_id` int(10) UNSIGNED NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `account`
--

CREATE TABLE `account` (
  `id` int(11) NOT NULL,
  `account_name` varchar(100) DEFAULT NULL,
  `account_type` varchar(50) DEFAULT NULL,
  `balance` decimal(10,2) DEFAULT NULL,
  `company_id` int(10) UNSIGNED NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `aianalytics`
--

CREATE TABLE `aianalytics` (
  `id` int(11) NOT NULL,
  `analysis_type` varchar(100) DEFAULT NULL,
  `results` text DEFAULT NULL,
  `company_id` int(10) UNSIGNED NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `aichatbotinteractions`
--

CREATE TABLE `aichatbotinteractions` (
  `id` int(11) NOT NULL,
  `user_query` text DEFAULT NULL,
  `bot_response` text DEFAULT NULL,
  `company_id` int(10) UNSIGNED NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `aioptimizations`
--

CREATE TABLE `aioptimizations` (
  `id` int(11) NOT NULL,
  `optimization_name` varchar(100) DEFAULT NULL,
  `parameters` text DEFAULT NULL,
  `results` text DEFAULT NULL,
  `timestamp` timestamp NOT NULL DEFAULT current_timestamp(),
  `company_id` int(10) UNSIGNED NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `aipredictions`
--

CREATE TABLE `aipredictions` (
  `id` int(11) NOT NULL,
  `model_name` varchar(100) DEFAULT NULL,
  `input_data` text DEFAULT NULL,
  `predicted_output` text DEFAULT NULL,
  `company_id` int(10) UNSIGNED NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `aireports`
--

CREATE TABLE `aireports` (
  `id` int(11) NOT NULL,
  `report_name` varchar(100) DEFAULT NULL,
  `content` text DEFAULT NULL,
  `company_id` int(10) UNSIGNED NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `alerts`
--

CREATE TABLE `alerts` (
  `id` int(11) NOT NULL,
  `company_id` int(11) DEFAULT NULL,
  `title` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `date` date NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `alerts`
--

INSERT INTO `alerts` (`id`, `company_id`, `title`, `message`, `date`, `created_at`) VALUES
(1, NULL, 'New Feature Update', '  \'We are excited to announce the release of a brand new feature aimed at enhancing your experience on our platform. This update includes significant performance improvements, a redesigned user interface, and several new tools to make managing your tasks easier and more efficient. You will now be able to track your progress in real-time, receive personalized suggestions, and enjoy a smoother, faster navigation experience. We highly recommend updating to the latest version and exploring these exciting new capabilities!\', \n', '2023-09-22', '2024-09-22 04:10:09'),
(2, 714980, 'New Feature Update', 'We have added a new feature for better performance', '2023-09-22', '2024-09-22 04:16:27');

-- --------------------------------------------------------

--
-- Table structure for table `applications`
--

CREATE TABLE `applications` (
  `id` int(11) NOT NULL,
  `applicant_name` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `position_applied_for` varchar(100) DEFAULT NULL,
  `resume` text DEFAULT NULL,
  `status` varchar(50) DEFAULT NULL,
  `company_id` int(10) UNSIGNED NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `assets`
--

CREATE TABLE `assets` (
  `id` int(11) NOT NULL,
  `asset_name` varchar(100) DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL,
  `purchase_date` date DEFAULT NULL,
  `value` decimal(10,2) DEFAULT NULL,
  `company_id` int(10) UNSIGNED NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `attendance`
--

CREATE TABLE `attendance` (
  `id` int(11) NOT NULL,
  `employee_id` int(11) DEFAULT NULL,
  `date` date DEFAULT NULL,
  `status` varchar(50) DEFAULT NULL,
  `company_id` int(10) UNSIGNED NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `audit`
--

CREATE TABLE `audit` (
  `id` int(11) NOT NULL,
  `action` varchar(255) DEFAULT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `company_id` int(10) UNSIGNED NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `bankaccount`
--

CREATE TABLE `bankaccount` (
  `id` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `first_name` varchar(100) NOT NULL,
  `last_name` varchar(100) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `account_number` varchar(50) NOT NULL,
  `bank_name` varchar(100) NOT NULL,
  `company_id` int(11) NOT NULL,
  `user_type` varchar(50) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `bankaccount`
--

INSERT INTO `bankaccount` (`id`, `email`, `first_name`, `last_name`, `phone`, `account_number`, `bank_name`, `company_id`, `user_type`, `user_id`, `created_at`) VALUES
(1, '103@gmail.com', 'Simplr Business Suite', 'Laksk', '09151294786', '9307196340', 'Wema Bank', 847772, 'employee', 3, '2024-08-15 17:26:04'),
(2, '104@gmail.com', 'Simplr Business Suite', 'lol', '09151294786', '9307196876', 'Wema Bank', 847772, 'manager', 3, '2024-08-15 17:35:35'),
(3, '106@gmail.com', 'ieiei', 'wiwiw', '09151294786', '9307197213', 'Wema Bank', 1234, 'admin', 11, '2024-08-15 17:43:25'),
(4, '109@gmail.com', 'Simplr Business Suite', 'ii99999', '09151294786', '9307197529', 'Wema Bank', 847772, 'admin', 3, '2024-08-15 17:50:36'),
(5, '109@gmail.com', 'Simplr Business Suite', '2929', '09151294786', '9307197529', 'Wema Bank', 847772, 'admin', 3, '2024-08-16 10:07:59'),
(6, '109@gmail.com', 'Simplr Business Suite', '999', '09151294786', '9307197529', 'Wema Bank', 847772, 'admin', 3, '2024-08-16 10:09:39'),
(7, '110@gmail.com', 'Simplr Business Suite', 'iqiqiqi', '09151294786', '9307227365', 'Wema Bank', 847772, 'admin', 3, '2024-08-16 10:33:22'),
(8, '119@gmail.com', 'Simplr Business Suite', '111', '09151294786', '9307227705', 'Wema Bank', 847772, 'admin', 3, '2024-08-16 10:39:15'),
(9, '119@gmail.com', 'Simplr Business Suite', '111', '09151294786', '9307227705', 'Wema Bank', 847772, 'admin', 3, '2024-08-16 10:40:03');

-- --------------------------------------------------------

--
-- Table structure for table `benefits`
--

CREATE TABLE `benefits` (
  `id` int(11) NOT NULL,
  `benefit_name` varchar(100) DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL,
  `employee_id` int(11) DEFAULT NULL,
  `company_id` int(10) UNSIGNED NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `bookdemo`
--

CREATE TABLE `bookdemo` (
  `id` int(6) UNSIGNED NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `company` varchar(100) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `demo_date` date DEFAULT NULL,
  `demo_time` time DEFAULT NULL,
  `message` text DEFAULT NULL,
  `status` varchar(20) DEFAULT 'Pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `preferred_contact_method` enum('email','phone') DEFAULT 'email',
  `industry` varchar(100) DEFAULT NULL,
  `number_of_employees` int(6) DEFAULT NULL,
  `heard_from` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `bookdemo`
--

INSERT INTO `bookdemo` (`id`, `name`, `email`, `company`, `phone`, `demo_date`, `demo_time`, `message`, `status`, `created_at`, `updated_at`, `preferred_contact_method`, `industry`, `number_of_employees`, `heard_from`) VALUES
(1, 'Emmanuel Odekunle', 'orangeconsultium@gmail.com', 'Orange Consultium', '+2349151294786', '2024-10-09', '16:03:00', 'Let\'s meet via Google Meet!', 'Pending', '2024-09-13 12:32:22', '2024-09-13 12:32:22', 'email', 'Consulting ', 20, 'Social Media'),
(2, 'Emmanuel Odekunle', 'orangeconsultium@gmail.com', 'Orange Consultium', '+2349151294786', '2024-10-09', '16:03:00', 'Let\'s meet via Google Meet!', 'Pending', '2024-09-13 12:38:35', '2024-09-13 12:38:35', 'email', 'Consulting ', 20, 'Social Media');

-- --------------------------------------------------------

--
-- Table structure for table `budgeting`
--

CREATE TABLE `budgeting` (
  `id` int(11) NOT NULL,
  `budget_name` varchar(100) DEFAULT NULL,
  `budget_amount` decimal(10,2) DEFAULT NULL,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `company_id` int(10) UNSIGNED NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cashflow`
--

CREATE TABLE `cashflow` (
  `id` int(11) NOT NULL,
  `date` date DEFAULT NULL,
  `amount` decimal(10,2) DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL,
  `company_id` int(10) UNSIGNED NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `clients`
--

CREATE TABLE `clients` (
  `id` int(11) NOT NULL,
  `client_name` varchar(100) DEFAULT NULL,
  `contact_person` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `company_id` int(10) UNSIGNED NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `company_widgets`
--

CREATE TABLE `company_widgets` (
  `id` int(11) NOT NULL,
  `company_id` int(11) DEFAULT NULL,
  `widget_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `company_widgets`
--

INSERT INTO `company_widgets` (`id`, `company_id`, `widget_id`) VALUES
(5, 714980, 1),
(6, 714980, 2),
(7, 714980, 3),
(8, 714980, 4),
(9, 714980, 5),
(10, 714980, 6),
(11, 714980, 7),
(12, 714980, 8),
(13, 714980, 9),
(14, 714980, 10);

-- --------------------------------------------------------

--
-- Table structure for table `contacts`
--

CREATE TABLE `contacts` (
  `id` int(11) NOT NULL,
  `name` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `company_id` int(10) UNSIGNED NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `customers`
--

CREATE TABLE `customers` (
  `id` int(11) NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `customeremail` varchar(100) NOT NULL,
  `phone_number` varchar(20) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `company_id` int(10) UNSIGNED NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cyber_audit_trails`
--

CREATE TABLE `cyber_audit_trails` (
  `id` int(11) NOT NULL,
  `company_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `action` text NOT NULL,
  `performed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cyber_compliance`
--

CREATE TABLE `cyber_compliance` (
  `id` int(11) NOT NULL,
  `company_id` int(11) NOT NULL,
  `regulation` varchar(255) NOT NULL,
  `status` varchar(50) NOT NULL,
  `checked_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cyber_data_protection`
--

CREATE TABLE `cyber_data_protection` (
  `id` int(11) NOT NULL,
  `company_id` int(11) NOT NULL,
  `data_type` varchar(255) NOT NULL,
  `protection_status` varchar(50) NOT NULL,
  `assessed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cyber_incidents`
--

CREATE TABLE `cyber_incidents` (
  `id` int(11) NOT NULL,
  `company_id` int(11) NOT NULL,
  `description` text NOT NULL,
  `severity` varchar(50) NOT NULL,
  `reported_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cyber_network_monitoring`
--

CREATE TABLE `cyber_network_monitoring` (
  `id` int(11) NOT NULL,
  `company_id` int(11) NOT NULL,
  `device` varchar(255) NOT NULL,
  `status` varchar(50) NOT NULL,
  `monitored_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cyber_reporting`
--

CREATE TABLE `cyber_reporting` (
  `id` int(11) NOT NULL,
  `company_id` int(11) NOT NULL,
  `report_type` varchar(255) NOT NULL,
  `generated_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cyber_threat_intelligence`
--

CREATE TABLE `cyber_threat_intelligence` (
  `id` int(11) NOT NULL,
  `company_id` int(11) NOT NULL,
  `threat_type` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `reported_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cyber_user_access`
--

CREATE TABLE `cyber_user_access` (
  `id` int(11) NOT NULL,
  `company_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `access_level` varchar(255) NOT NULL,
  `assigned_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cyber_vulnerabilities`
--

CREATE TABLE `cyber_vulnerabilities` (
  `id` int(11) NOT NULL,
  `company_id` int(11) NOT NULL,
  `description` text NOT NULL,
  `severity` varchar(50) NOT NULL,
  `discovered_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `departments`
--

CREATE TABLE `departments` (
  `id` int(11) NOT NULL,
  `department_name` varchar(100) DEFAULT NULL,
  `manager_id` int(11) DEFAULT NULL,
  `location` varchar(255) DEFAULT NULL,
  `company_id` int(10) UNSIGNED NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `departments`
--

INSERT INTO `departments` (`id`, `department_name`, `manager_id`, `location`, `company_id`, `created_at`) VALUES
(1, 'Bakery', 1, 'Lag', 714980, '2024-07-27 23:30:00'),
(2, 'Human Resources', NULL, 'Lagos', 714980, '2024-07-28 02:34:33'),
(3, 'Human Capital', NULL, 'Lagos', 714980, '2024-07-28 02:34:56'),
(4, 'Development', NULL, 'Lagos', 714980, '2024-07-28 02:35:37'),
(5, 'Production', NULL, 'Abuja', 714980, '2024-07-28 02:36:21'),
(6, 'Finance', NULL, 'Lagos', 714980, '2024-07-31 07:56:15'),
(7, 'sydgydgwy', NULL, 'gydgydwgyd', 897771, '2024-08-20 15:10:18'),
(8, '', NULL, '', 0, '2024-09-22 09:18:28'),
(9, 'management', NULL, '', 809309, '2024-09-26 14:02:22');

-- --------------------------------------------------------

--
-- Table structure for table `employees`
--

CREATE TABLE `employees` (
  `id` int(11) NOT NULL,
  `company_id` int(11) NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `hire_date` date NOT NULL,
  `job_title` varchar(100) DEFAULT NULL,
  `department_id` int(11) NOT NULL,
  `manager_id` int(11) NOT NULL,
  `role_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `employees`
--

INSERT INTO `employees` (`id`, `company_id`, `first_name`, `last_name`, `email`, `phone`, `hire_date`, `job_title`, `department_id`, `manager_id`, `role_id`, `created_at`, `updated_at`) VALUES
(1, 714980, 'Emmanuel', 'Odekunle', 'nuelodekunle20@gmail.com', '09151294786', '2024-07-09', 'Human Resources Officer', 1, 0, 2, '2024-07-28 02:04:46', '2024-07-28 02:04:46'),
(2, 714980, 'Rosemary', 'Salifu', 'rosemarysalifu247@gmail.com', '09151294786', '2024-08-03', 'Human Resources Officer', 1, 1, 4, '2024-07-28 12:01:29', '2024-08-02 11:53:43'),
(3, 714980, 'Jon', 'Doe', 'test@example.us', '6019521325', '2024-07-24', 'hi', 1, 0, 5, '2024-07-30 10:40:59', '2024-07-30 10:40:59');

-- --------------------------------------------------------

--
-- Table structure for table `employees_auth`
--

CREATE TABLE `employees_auth` (
  `id` int(11) NOT NULL,
  `company_id` int(11) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `employees_auth`
--

INSERT INTO `employees_auth` (`id`, `company_id`, `email`, `password`, `created_at`, `updated_at`) VALUES
(1, 714980, 'nuelodekunle20@gmail.com', '$2y$10$oVjxQNEYsHo0WQO4iAmQFe1vGoYQ3xVXkQh1CowkdKCwIMVlj/bZa', '2024-07-28 02:04:46', '2024-07-28 02:04:46'),
(2, 714980, 'rosemarysalifu247@gmail.com', '$2y$10$3YKDyiyk0wTfZD0n1V3hLe479u.LjzcF2wFecqjIjr2wSWMpbbB8m', '2024-07-28 12:01:29', '2024-07-28 12:01:29'),
(3, 714980, 'test@example.us', '$2y$10$f21INMXuuhWE4v8HDXzL.umgHttpBq5uAStjZQqnioPuvYS4hN6ga', '2024-07-30 10:40:59', '2024-07-30 10:40:59');

-- --------------------------------------------------------

--
-- Table structure for table `expenses`
--

CREATE TABLE `expenses` (
  `id` int(11) NOT NULL,
  `date` date NOT NULL,
  `description` varchar(255) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `company_id` int(10) UNSIGNED NOT NULL,
  `category` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `feedback`
--

CREATE TABLE `feedback` (
  `id` int(11) NOT NULL,
  `feedback_date` date DEFAULT NULL,
  `feedback_content` text DEFAULT NULL,
  `company_id` int(10) UNSIGNED NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `interviews`
--

CREATE TABLE `interviews` (
  `id` int(11) UNSIGNED NOT NULL,
  `talent_id` int(11) UNSIGNED NOT NULL,
  `date` date NOT NULL,
  `time` time NOT NULL,
  `interviewer` varchar(255) NOT NULL,
  `attended` tinyint(1) DEFAULT 0,
  `feedback` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `company_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `interviews`
--

INSERT INTO `interviews` (`id`, `talent_id`, `date`, `time`, `interviewer`, `attended`, `feedback`, `created_at`, `updated_at`, `company_id`) VALUES
(1, 7, '2024-01-08', '05:27:00', 'me', 0, NULL, '2024-08-05 16:26:49', '2024-08-05 16:26:49', 0);

-- --------------------------------------------------------

--
-- Table structure for table `inventory`
--

CREATE TABLE `inventory` (
  `id` int(11) NOT NULL,
  `product_name` varchar(100) DEFAULT NULL,
  `quantity` int(11) DEFAULT NULL,
  `unit_price` decimal(10,2) DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL,
  `company_id` int(10) UNSIGNED NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `inventory_items`
--

CREATE TABLE `inventory_items` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `company_id` int(10) UNSIGNED NOT NULL,
  `quantity` int(11) NOT NULL,
  `unit_price` decimal(10,2) NOT NULL,
  `category` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `invoices`
--

CREATE TABLE `invoices` (
  `id` int(11) NOT NULL,
  `invoice_date` date DEFAULT NULL,
  `due_date` date DEFAULT NULL,
  `amount` decimal(10,2) DEFAULT NULL,
  `status` enum('Paid','Unpaid','Cancelled') NOT NULL DEFAULT 'Unpaid',
  `customer_id` int(11) DEFAULT NULL,
  `company_id` int(10) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `ledger_entries`
--

CREATE TABLE `ledger_entries` (
  `id` int(11) NOT NULL,
  `date` date NOT NULL,
  `company_id` int(10) UNSIGNED NOT NULL,
  `description` varchar(255) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `type` varchar(50) NOT NULL,
  `category` varchar(50) NOT NULL,
  `reference_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `logs`
--

CREATE TABLE `logs` (
  `id` int(11) NOT NULL,
  `type` varchar(255) NOT NULL,
  `user_id` int(11) NOT NULL,
  `action` varchar(100) NOT NULL,
  `details` text NOT NULL,
  `company_id` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `logs`
--

INSERT INTO `logs` (`id`, `type`, `user_id`, `action`, `details`, `company_id`, `created_at`) VALUES
(1, 'User Activity', 1, 'Login', 'User logged in successfully.', '897771', '2024-09-22 10:31:30'),
(2, 'User Activity', 1, 'Login', 'User logged in successfully.', 'COMP001', '2024-09-22 10:53:28');

-- --------------------------------------------------------

--
-- Table structure for table `managers`
--

CREATE TABLE `managers` (
  `id` int(11) NOT NULL,
  `company_id` int(11) NOT NULL,
  `manager_name` varchar(255) NOT NULL,
  `manager_email` varchar(255) NOT NULL,
  `department_id` int(11) NOT NULL,
  `role_id` int(11) NOT NULL,
  `status` varchar(50) NOT NULL DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `managers`
--

INSERT INTO `managers` (`id`, `company_id`, `manager_name`, `manager_email`, `department_id`, `role_id`, `status`, `created_at`) VALUES
(1, 714980, 'Emmanuel', 'nuelodekunle20@gmail.com', 1, 1, 'approved', '2024-07-28 00:37:45'),
(2, 714980, 'Emmanuel', 'emmanuelodekunle247@gmail.com', 3, 3, 'pending', '2024-07-28 03:14:52'),
(3, 714980, 'Rosy Rose', 'emmanuelodekunle0@gmail.com', 6, 6, 'pending', '2024-07-31 07:57:00'),
(4, 897771, '2678j2ijei', 'nwjdnwj@gmail.com', 7, 7, 'pending', '2024-08-20 15:10:41');

-- --------------------------------------------------------

--
-- Table structure for table `manager_auth`
--

CREATE TABLE `manager_auth` (
  `id` int(11) NOT NULL,
  `company_id` int(11) NOT NULL,
  `manager_name` varchar(255) NOT NULL,
  `manager_email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `manager_auth`
--

INSERT INTO `manager_auth` (`id`, `company_id`, `manager_name`, `manager_email`, `password`) VALUES
(2, 714980, 'Emmanuel', 'nuelodekunle20@gmail.com', '$2y$10$CLbj/5YeSlRSSeZtpQ3//ukmg0nX1OBAZgy0LMHt/KDsAQ7Z5gsgW'),
(3, 714980, 'Emmanuel', 'emmanuelodekunle247@gmail.com', '$2y$10$Y8KtfN2Ze2dNhf4UNCLf3.f2RQukhDQSBmXLu7ZfKVBpWzewHFFtG'),
(4, 714980, 'Rosy Rose', 'emmanuelodekunle0@gmail.com', '$2y$10$SWpeTieqMFasAwAjmqpDIOtaw0hl5Dn/FxzdSV4PQQ4rDxqQ2h6uq'),
(5, 897771, '2678j2ijei', 'nwjdnwj@gmail.com', '$2y$10$0.plEUjB1ZwWGKSdQGyrCuvdmvXSYgkE4NYix139AAhWuTA39fQVe');

-- --------------------------------------------------------

--
-- Table structure for table `messages`
--

CREATE TABLE `messages` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `sender_id` INT NOT NULL,
    `recipient_id` INT NOT NULL,
    `message` TEXT NOT NULL,
    `timestamp` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `status` ENUM('sent', 'delivered', 'read') DEFAULT 'sent',
    FOREIGN KEY (sender_id) REFERENCES users(id),
    FOREIGN KEY (recipient_id) REFERENCES users(id)
);

-- --------------------------------------------------------

--
-- Table structure for table `nairapayments`
--

CREATE TABLE `nairapayments` (
  `id` int(11) NOT NULL,
  `customername` varchar(255) NOT NULL,
  `companyid` varchar(255) NOT NULL,
  `customeremail` varchar(255) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `currency` varchar(10) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `nairapayments`
--

INSERT INTO `nairapayments` (`id`, `customername`, `companyid`, `customeremail`, `amount`, `currency`, `created_at`) VALUES
(1, 'The Virtual Adviser', '714980', 'nuelodekunle20@gmail.com', 3000.00, 'NGN', '2024-07-24 17:48:55'),
(2, 'The Virtual Adviser', '714980', 'nuelodekunle20@gmail.com', 3000.00, 'NGN', '2024-07-26 16:40:44'),
(3, 'The Virtual Adviser', '714980', 'nuelodekunle20@gmail.com', 7000.00, 'NGN', '2024-07-26 16:42:18'),
(4, 'The Virtual Adviser', '714980', 'nuelodekunle20@gmail.com', 7000.00, 'NGN', '2024-07-26 16:46:25'),
(5, 'The Virtual Adviser', '714980', 'nuelodekunle20@gmail.com', 10500.00, 'NGN', '2024-07-26 16:48:48'),
(6, 'The Virtual Adviser', '714980', 'nuelodekunle20@gmail.com', 7000.00, 'NGN', '2024-07-26 16:51:32'),
(7, 'The Virtual Adviser', '714980', 'nuelodekunle20@gmail.com', 4000.00, 'NGN', '2024-07-26 16:53:10'),
(8, 'The Virtual Adviser', '714980', 'nuelodekunle20@gmail.com', 7500.00, 'NGN', '2024-07-26 16:57:26'),
(9, 'The Virtual Adviser', '714980', 'nuelodekunle20@gmail.com', 3000.00, 'NGN', '2024-07-26 16:58:22'),
(10, 'The Virtual Adviser', '714980', 'nuelodekunle20@gmail.com', 10500.00, 'NGN', '2024-07-26 17:05:22'),
(11, 'The Virtual Adviser', '714980', 'nuelodekunle20@gmail.com', 16000.00, 'NGN', '2024-07-26 17:08:19'),
(12, 'The Virtual Adviser', '714980', 'nuelodekunle20@gmail.com', 34500.00, 'NGN', '2024-07-26 17:20:39'),
(13, 'The Virtual Adviser', '714980', 'nuelodekunle20@gmail.com', 16000.00, 'NGN', '2024-07-26 17:31:43'),
(14, 'The Virtual Adviser', '714980', 'nuelodekunle20@gmail.com', 18000.00, 'NGN', '2024-07-26 17:35:17'),
(15, 'The Virtual Adviser', '714980', 'nuelodekunle20@gmail.com', 36000.00, 'NGN', '2024-07-26 17:39:33'),
(16, 'The Virtual Adviser', '714980', 'nuelodekunle20@gmail.com', 28000.00, 'NGN', '2024-07-26 17:44:00'),
(17, 'The Virtual Adviser', '714980', 'nuelodekunle20@gmail.com', 36000.00, 'NGN', '2024-07-26 17:46:06'),
(18, 'The Virtual Adviser', '714980', 'nuelodekunle20@gmail.com', 15000.00, 'NGN', '2024-07-26 17:57:24'),
(19, 'The Virtual Adviser', '714980', 'nuelodekunle20@gmail.com', 3000.00, 'NGN', '2024-07-26 18:01:09'),
(20, 'The Virtual Adviser', '714980', 'nuelodekunle20@gmail.com', 7500.00, 'NGN', '2024-07-26 18:05:33'),
(21, 'The Virtual Adviser', '714980', 'nuelodekunle20@gmail.com', 10500.00, 'NGN', '2024-07-26 18:08:28'),
(22, 'The Virtual Adviser', '714980', 'nuelodekunle20@gmail.com', 6000.00, 'NGN', '2024-07-26 18:13:37'),
(23, 'The Virtual Adviser', '714980', 'nuelodekunle20@gmail.com', 6000.00, 'NGN', '2024-07-26 18:19:36'),
(24, 'The Virtual Adviser', '714980', 'nuelodekunle20@gmail.com', 34000.00, 'NGN', '2024-09-05 10:08:37');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `order_date` date DEFAULT NULL,
  `customer_id` int(11) DEFAULT NULL,
  `status` varchar(50) DEFAULT NULL,
  `company_id` int(10) UNSIGNED NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `password_resets`
--

CREATE TABLE `password_resets` (
  `id` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `permissions`
--

CREATE TABLE `permissions` (
  `id` int(11) NOT NULL,
  `permission_name` varchar(50) DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL,
  `role_id` int(11) DEFAULT NULL,
  `company_id` int(10) UNSIGNED NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `productdevelopment`
--

CREATE TABLE `productdevelopment` (
  `id` int(11) NOT NULL,
  `product_name` varchar(100) DEFAULT NULL,
  `stage` varchar(100) DEFAULT NULL,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `status` varchar(50) DEFAULT NULL,
  `company_id` int(10) UNSIGNED NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `projects`
--

CREATE TABLE `projects` (
  `id` int(11) NOT NULL,
  `projectName` varchar(255) NOT NULL,
  `projectDescription` text DEFAULT NULL,
  `startDate` date DEFAULT NULL,
  `endDate` date DEFAULT NULL,
  `department` varchar(50) DEFAULT NULL,
  `status` varchar(50) DEFAULT NULL,
  `projectManager` int(11) DEFAULT NULL,
  `company_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `projects`
--

INSERT INTO `projects` (`id`, `projectName`, `projectDescription`, `startDate`, `endDate`, `department`, `status`, `projectManager`, `company_id`, `created_at`, `updated_at`) VALUES
(1, 'App Development', '<p>Yes</p>', '2024-09-20', '2024-09-30', '4', 'planning', 1, 714980, '2024-09-17 12:19:58', '2024-09-17 12:19:58'),
(2, 'App Development (Phase 2)', '<p>Yes</p>', '2024-09-20', '2024-09-30', '4', 'planning', 1, 714980, '2024-09-17 15:56:27', '2024-09-17 15:56:27'),
(3, 'Make a New Canvas Shoe for Addidas', '', '2024-09-20', '2024-09-30', '2', 'inProgress', 1, 714980, '2024-09-19 13:07:41', '2024-09-19 13:07:41');

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

CREATE TABLE `roles` (
  `id` int(11) NOT NULL,
  `role_name` varchar(50) DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL,
  `company_id` int(10) UNSIGNED NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `roles`
--

INSERT INTO `roles` (`id`, `role_name`, `description`, `company_id`, `created_at`) VALUES
(1, 'Manager', 'Manager with access to managerial functions', 714980, '2024-07-28 00:37:45'),
(2, 'Employee', 'Employee with access to staff functions', 714980, '2024-07-28 02:04:46'),
(3, 'Manager', 'Manager with access to managerial functions', 714980, '2024-07-28 03:14:52'),
(4, 'Employee', 'Employee with access to staff functions', 714980, '2024-07-28 12:01:29'),
(5, 'Employee', 'Employee with access to staff functions', 714980, '2024-07-30 10:40:59'),
(6, 'Manager', 'Manager with access to managerial functions', 714980, '2024-07-31 07:57:00'),
(7, 'Manager', 'Manager with access to managerial functions', 897771, '2024-08-20 15:10:41');

-- --------------------------------------------------------

--
-- Table structure for table `support_requests`
--

CREATE TABLE `support_requests` (
  `id` int(11) NOT NULL,
  `issue_type` varchar(255) NOT NULL,
  `query_text` text NOT NULL,
  `attachment_path` varchar(255) DEFAULT NULL,
  `company_id` int(10) UNSIGNED NOT NULL,
  `resolve_status` enum('pending','resolved') DEFAULT 'pending'
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `support_requests`
--

INSERT INTO `support_requests` (`id`, `issue_type`, `query_text`, `attachment_path`, `company_id`, `resolve_status`) VALUES
(1, 'Technical Issue', 'z', 'uploads/1.0. Consent to Background Screening.pdf', 0, 'pending'),
(2, 'Account Issue', '', 'uploads/edpb.php.txt', 0, 'pending');

-- --------------------------------------------------------

--
-- Table structure for table `talentchat`
--

CREATE TABLE `talentchat` (
  `id` int(11) NOT NULL,
  `chat_url` varchar(255) NOT NULL,
  `recruiter_name` varchar(100) NOT NULL,
  `talent_name` varchar(100) NOT NULL,
  `talent_email` varchar(100) NOT NULL,
  `recruiter_email` varchar(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `talentchat`
--

INSERT INTO `talentchat` (`id`, `chat_url`, `recruiter_name`, `talent_name`, `talent_email`, `recruiter_email`, `created_at`) VALUES
(30, 'chatus.php?unique_id1=rec2&unique_id2=tal689106&created_at=2024-09-05T13%3A48%3A41.125Z', 'Rosemary Salifu', 'Chinwendu Chinecherem Chinenye', 'chinwenduneche@gmail.com', 'rosemarysalifu247@gmail.com', '2024-09-05 13:48:42'),
(36, 'chatus.php?unique_id1=rec2&unique_id2=tal435274&created_at=2024-09-05T15%3A21%3A53.030Z', 'Rosemary Salifu', 'Emmanuel Odekunle', 'nuelodekunle20@gmail.com', 'rosemarysalifu247@gmail.com', '2024-09-05 15:21:54'),
(103, 'chatus.php?unique_id1=rec2&unique_id2=tal0&created_at=2024-09-06T22%3A03%3A00.350Z', 'Rosemary Salifu', '', '', 'rosemarysalifu247@gmail.com', '2024-09-06 22:03:00');

-- --------------------------------------------------------

--
-- Table structure for table `talentprofile`
--

CREATE TABLE `talentprofile` (
  `id` int(11) NOT NULL,
  `full_name` varchar(255) NOT NULL,
  `industry` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `talent_id` varchar(100) NOT NULL,
  `status` enum('Verified','Unverified') NOT NULL DEFAULT 'Unverified',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `token` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `talentprofile`
--

INSERT INTO `talentprofile` (`id`, `full_name`, `industry`, `email`, `password`, `talent_id`, `status`, `created_at`, `token`) VALUES
(4, 'able', 'finance', 'nuelodekunle20@gmail.com', '$2y$10$bKI2avtTAZyFzIhRcuyKYusiA7TKkLqEPkQh1zQRxwzyZ0xOjETX2', '956487', 'Verified', '2024-08-30 10:58:52', NULL),
(5, 'Rojugbokan Oluwafemi', 'other', 'femirojugbokan96@gmail.com', '$2y$10$stWrdPKVRk/yRFNSuztHsu.1JNbR4QoxGOheemNHqVWSGSGaUaLsK', '804513', 'Unverified', '2024-08-30 12:57:24', '5c8b99b00c3b87e4a28a17ea37c8bf6da53119f35e39dc0cb10eb31249e1d9f0'),
(6, 'Adewoye Adewole Moses ', 'consulting', 'Adewolex006@gmail.com', '$2y$10$X.vbIcdPbSNhqcYZiETRV.89cr9w7oJHmWZz1PnM0G1saeHwjpelm', '542398', 'Verified', '2024-08-30 17:03:02', NULL),
(7, 'Olanrewaju Bankole Joshua ', 'finance', 'bankjossy77@yahoo.com', '$2y$10$9IdJtvuAadRY.AuIKIhYlud0/o.CyIO/CZ.xtsfWEuFtgq.ps9z1u', '953514', 'Verified', '2024-08-31 21:53:28', 'f35e17542bf0022978f5bef35b93fb46240e112e97dd413d3aac09381fab2afa'),
(8, 'BALOGUN QUADRI OLATUNDE ', 'other', 'balogunolatunde1997@gmail.com', '$2y$10$P1vzJhbrn3uy0iV01BSLu.JqPInqiOlzCSkDXK8iY1ych4exsQa8G', '726713', 'Verified', '2024-09-02 09:44:46', NULL),
(9, 'Temitayo Gershom', 'healthcare', 'abc12345678@gmail.com', '$2y$10$iMEEu/.EHUdTWTW9FH1MOemnK/Y99XJAyYGd9hHYg2e4/rnRqrt.u', '435274', 'Verified', '2024-09-02 14:13:24', NULL),
(10, 'Chinwendu Chinecherem Chinenye ', 'finance', 'chinwenduneche@gmail.com', '$2y$10$SrObVuyXpcVJ4wEUtAFZUu2UAFxQb3mJNkI5pf8d9BaeM1VqT1RKq', '689106', 'Verified', '2024-09-03 10:37:14', NULL),
(11, 'Adeosun Modupeoluwa Itunu', 'finance', 'dupsyjazz66@gmail.com', '$2y$10$kVxTqGfpQKDf0qWhbxcuoOeWq8md7AQiBZmSeMO9oCUrwj274bTpC', '296214', 'Verified', '2024-09-04 17:03:24', NULL),
(12, 'Oluwatoyin Deborah Adeyemo', 'energy', 'oluwatoyinadeyemo82@gmail.com', '$2y$10$XEJ7NJFg9jX4uVKanUCgcOjnBdj2tW3D0O5.sH7WqGEUy6ohBAwF6', '938702', 'Verified', '2024-09-06 08:38:14', NULL),
(13, 'Enoch ', 'education', 'Enoch@gmail.com', '$2y$10$i7YX2mUTgwYOc6PdnxmcDO9X4Rnk3VHKd4DYRROVN7Hfp/MTPQeOm', '997548', 'Unverified', '2024-09-09 14:07:40', 'e9393235f6a8d930ce0b0e45e098c109a68c3e73a1a9522bc01f94781179b62c'),
(14, 'Johannes Lee', 'it', 'joey031695@gmail.com', '$2y$10$eJ091eKq/plHY7xyv81MSe/Dc8JjZuwBKlN54JrJf8fDjcFXOwK/y', '160656', 'Verified', '2024-09-16 13:12:13', NULL),
(15, 'N8N', 'banking', 'olaayinke@gmail.com', '$2y$10$Obw7B0fJWY/y9Q2LrmOjEudrkwpXIjsIPTLseLXCPzbNpGH.Rx6ru', '788859', 'Unverified', '2024-09-26 06:43:10', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `talents`
--

CREATE TABLE `talents` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `telephone` varchar(20) NOT NULL,
  `preferred_role` varchar(255) NOT NULL,
  `experience` varchar(255) NOT NULL,
  `location` varchar(255) NOT NULL,
  `gender` enum('Male','Female','Other') NOT NULL,
  `profile_picture` varchar(255) DEFAULT NULL,
  `resume_file` varchar(255) DEFAULT NULL,
  `skills` text DEFAULT NULL,
  `education` text DEFAULT NULL,
  `certifications` text DEFAULT NULL,
  `languages` varchar(255) DEFAULT NULL,
  `availability` varchar(50) DEFAULT NULL,
  `salary_expectation` decimal(10,2) DEFAULT NULL,
  `currency` varchar(3) DEFAULT 'NGN',
  `portfolio_url` varchar(255) DEFAULT NULL,
  `linkedin_profile` varchar(255) DEFAULT NULL,
  `contact_references` text DEFAULT NULL,
  `other_information` text DEFAULT NULL,
  `preferred_working_arrangement` enum('Remote','Hybrid','Onsite','Open') NOT NULL,
  `talent_id` int(11) DEFAULT NULL,
  `visasponsorship` varchar(3) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `talents`
--

INSERT INTO `talents` (`id`, `name`, `email`, `telephone`, `preferred_role`, `experience`, `location`, `gender`, `profile_picture`, `resume_file`, `skills`, `education`, `certifications`, `languages`, `availability`, `salary_expectation`, `currency`, `portfolio_url`, `linkedin_profile`, `contact_references`, `other_information`, `preferred_working_arrangement`, `talent_id`, `visasponsorship`) VALUES
(11, 'Chinwendu Chinecherem Chinenye', 'chinwenduneche@gmail.com', '+2348137413574', 'Finance', '1', 'Lagos, Nigeria', 'Female', 'uploads/_1725366066_3638.', 'uploads/Chinwendu CV_1725366066_5945.pdf', 'Array', 'University of Jos, Nigeria.', 'BSC, Accounting \r\nAAT\r\nACA', 'Array', '4 weeks', 20000.00, 'USD', '', 'https://www.linkedin.com/in/chinecherem-chinwendu-aat-aca-40b79b318?utm_source=share&amp;utm_campaign=share_via&amp;utm_content=profile&amp;utm_medium=android_app', '11 Daniel olokutun street Sango ota, Ogun State.', '', 'Remote', 689106, 'yes'),
(13, 'Emmanuel Odekunle', 'nuelodekunle20@gmail.com', '+2349151294786', 'Full Stack Developer: I build robust web platforms for businesses and mentor individuals on how to code &amp; land tech roles. Built 11+ websites in under 2 years, Let&#039;s connect via WhatsApp: +2349151294786.', 'Full Stack Engineer at Simplr Business Suite, Back End Developer (Node.js RestfulAPIs and Express) at Shortchase, Full-stack Developer (MySql, PhP, Bootstrap and JQuery) at Gegeto Edtech , Chief Executive Officer at Numerox Conglomerate, International Tra', 'Lagos, Lagos State, Nigeria', 'Male', 'uploads/WIN_20240711_12_01_27_Pro_1725373477_2047.jpg', 'uploads/_1725373477_5519.', '[\"ad\",\"dkdk\",\"ddkdk\"]', 'Bachelor&#039;s degree from University of Ilorin, Nigeria\r\nWest African Senior School Certificate Examination from Classic City High School', 'User Interface and User Research Fundamentals \r\nFrontend Web Development\r\nAccounting Fundamentals\r\nAdvanced Google Analytics\r\nOnline Marketing Fundamentals\r\nBasic Digital Skills Training\r\nGoogle Web Design Basic', '[\"English\",\"French\",\"Japanese\",\"Yoruba\"]', 'Immediately', 1000.00, 'USD', 'https://www.linkedin.com/in/emmanuel-odekunle/', 'https://www.linkedin.com/in/emmanuel-odekunle/', 'jssjsjjs', 'Read this now ðŸ‘‡\r\n\r\nYou may have browsed through hundreds of LinkedIn profiles, but you&#039;re now seeing the best!\r\n\r\nI am a seasoned full-stack developer with a strong focus on backend development, where my experience spans over a decade across various industries, including FMCG, EdTech, Social Media, SaaS, and Small Business Consulting. My career has been defined by my ability to adapt and excel in different environments, allowing me to acquire the technical skills necessary to bring any creative project to life.\r\n\r\nMy passion lies in building scalable, high-performance web platforms. I am proficient in a wide range of technologies, including Node.js, React, PHP, MySQL, and more. I thrive on leveraging the latest developer tools and frameworks to streamline development processes and deliver exceptional results. Whether it&#039;s deploying complex business models, crafting intuitive user experiences, or leading a team to success, Iâ€™m always eager to dive into new challenges.\r\n\r\nThroughout my career, Iâ€™ve also honed my ability to simplify complicated procedures and translate them into teachable moments. I enjoy sharing my knowledge with others, whether itâ€™s mentoring junior developers or teaching hundreds of students in areas like digital marketing, cryptocurrency investing, and copywriting.\r\n\r\nIn addition to my technical expertise, Iâ€™m a dedicated early adopter of innovative tools and products from platforms like Product Hunt. Some of my go-to apps include VSCode, Postman, Notion, Cody,  Figma, GitHub Copilot, Sentry, Docker and many more.  \r\n\r\nWhat I Can Offer:\r\n\r\nðŸ“ Full-Stack Development Excellence: I bring a wealth of experience in building robust, scalable applications using a wide array of technologies. My backend-heavy approach ensures that every project is optimized for performance, security, and scalability.\r\n\r\nðŸ“ Mastery of Developer Tools: My familiarity with top Product Hunt apps, such as Postman, Notion, and Docker, allows me to enhance productivity, streamline workflows, and deliver high-quality code.\r\n\r\nðŸ“ Mentorship and Knowledge Sharing: Iâ€™m passionate about teaching and mentoring the next generation of developers. Whether through formal education or informal guidance, Iâ€™m committed to helping others grow in their careers.\r\n\r\nLetâ€™s Connect:\r\n\r\nIf youâ€™re looking for a full-stack developer who can not only deliver top-tier technical solutions but also contribute to your teamâ€™s growth and success, Iâ€™d love to connect.\r\n\r\nEmail: nuelodekunle20@gmail.com\r\nWhatsApp: +2349151294786', 'Remote', 435274, 'yes'),
(14, 'Emmanuel Odekunle', 'nuelodekunle20@gmail.com', '+2349151294786', 'Full Stack Developer: I build robust web platforms for businesses and mentor individuals on how to code &amp; land tech roles. Built 11+ websites in under 2 years, Let&#039;s connect via WhatsApp: +2349151294786.', 'Full Stack Engineer at Simplr Business Suite, Back End Developer (Node.js RestfulAPIs and Express) at Shortchase, Full-stack Developer (MySql, PhP, Bootstrap and JQuery) at Gegeto Edtech , Chief Executive Officer at Numerox Conglomerate, International Tra', 'Lagos, Lagos State, Nigeria', 'Male', 'uploads/_1725374165_9958.', 'uploads/_1725374165_8648.', '[\"ope\",\"ood\",\"ool\"]', 'Bachelor&#039;s degree from University of Ilorin, Nigeria\r\nWest African Senior School Certificate Examination from Classic City High School', 'User Interface and User Research Fundamentals \r\nFrontend Web Development\r\nAccounting Fundamentals\r\nAdvanced Google Analytics\r\nOnline Marketing Fundamentals\r\nBasic Digital Skills Training\r\nGoogle Web Design Basic', '[\"English\",\"French\",\"Japanese\",\"Yoruba\"]', '1 week', 0.00, 'USD', '', 'https://www.linkedin.com/in/emmanuel-odekunle/', '', 'Read this now ðŸ‘‡\r\n\r\nYou may have browsed through hundreds of LinkedIn profiles, but you&#039;re now seeing the best!\r\n\r\nI am a seasoned full-stack developer with a strong focus on backend development, where my experience spans over a decade across various industries, including FMCG, EdTech, Social Media, SaaS, and Small Business Consulting. My career has been defined by my ability to adapt and excel in different environments, allowing me to acquire the technical skills necessary to bring any creative project to life.\r\n\r\nMy passion lies in building scalable, high-performance web platforms. I am proficient in a wide range of technologies, including Node.js, React, PHP, MySQL, and more. I thrive on leveraging the latest developer tools and frameworks to streamline development processes and deliver exceptional results. Whether it&#039;s deploying complex business models, crafting intuitive user experiences, or leading a team to success, Iâ€™m always eager to dive into new challenges.\r\n\r\nThroughout my career, Iâ€™ve also honed my ability to simplify complicated procedures and translate them into teachable moments. I enjoy sharing my knowledge with others, whether itâ€™s mentoring junior developers or teaching hundreds of students in areas like digital marketing, cryptocurrency investing, and copywriting.\r\n\r\nIn addition to my technical expertise, Iâ€™m a dedicated early adopter of innovative tools and products from platforms like Product Hunt. Some of my go-to apps include VSCode, Postman, Notion, Cody,  Figma, GitHub Copilot, Sentry, Docker and many more.  \r\n\r\nWhat I Can Offer:\r\n\r\nðŸ“ Full-Stack Development Excellence: I bring a wealth of experience in building robust, scalable applications using a wide array of technologies. My backend-heavy approach ensures that every project is optimized for performance, security, and scalability.\r\n\r\nðŸ“ Mastery of Developer Tools: My familiarity with top Product Hunt apps, such as Postman, Notion, and Docker, allows me to enhance productivity, streamline workflows, and deliver high-quality code.\r\n\r\nðŸ“ Mentorship and Knowledge Sharing: Iâ€™m passionate about teaching and mentoring the next generation of developers. Whether through formal education or informal guidance, Iâ€™m committed to helping others grow in their careers.\r\n\r\nLetâ€™s Connect:\r\n\r\nIf youâ€™re looking for a full-stack developer who can not only deliver top-tier technical solutions but also contribute to your teamâ€™s growth and success, Iâ€™d love to connect.\r\n\r\nEmail: nuelodekunle20@gmail.com\r\nWhatsApp: +2349151294786', 'Hybrid', 956487, 'yes'),
(18, '', '', '', '', '', '', '', 'uploads/_1727437383_5996.', 'uploads/_1727437383_2421.', '[]', '', '', '[]', '', 0.00, '', '', 'https://linkedin.com/in/emmanuel-odekunle', '', '', '', 0, '');

-- --------------------------------------------------------

--
-- Table structure for table `tasks`
--

CREATE TABLE `tasks` (
  `id` int(11) NOT NULL,
  `project_id` int(11) DEFAULT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `due_date` date NOT NULL,
  `priority` enum('low','medium','high') NOT NULL,
  `assignee` varchar(255) NOT NULL,
  `company_id` int(11) NOT NULL,
  `file_path` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `teams`
--

CREATE TABLE `teams` (
  `id` int(11) NOT NULL,
  `team_name` varchar(255) NOT NULL,
  `company_id` int(11) NOT NULL,
  `team_leader_id` int(11) DEFAULT NULL,
  `project_id` int(11) DEFAULT NULL,
  `team_description` text DEFAULT NULL,
  `team_members` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`team_members`)),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `teams`
--

INSERT INTO `teams` (`id`, `team_name`, `company_id`, `team_leader_id`, `project_id`, `team_description`, `team_members`, `created_at`, `updated_at`) VALUES
(1, 'Team 1', 714980, 1, 2, '<p>You are a great team</p>', '[\"2\"]', '2024-09-24 17:48:03', '2024-09-24 17:48:03');

-- --------------------------------------------------------

--
-- Table structure for table `toolkit_subscriptions`
--

CREATE TABLE `toolkit_subscriptions` (
  `id` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `payroll_months` int(11) DEFAULT NULL,
  `payroll_startdate` date DEFAULT NULL,
  `payroll_expireddate` date DEFAULT NULL,
  `recruiting_months` int(11) DEFAULT NULL,
  `recruiting_startdate` date DEFAULT NULL,
  `recruiting_expireddate` date DEFAULT NULL,
  `crm_months` int(11) DEFAULT NULL,
  `crm_startdate` date DEFAULT NULL,
  `crm_expireddate` date DEFAULT NULL,
  `cybersecurity_months` int(11) DEFAULT NULL,
  `cybersecurity_startdate` date DEFAULT NULL,
  `cybersecurity_expireddate` date DEFAULT NULL,
  `project_months` int(11) DEFAULT NULL,
  `project_startdate` date DEFAULT NULL,
  `project_expireddate` date DEFAULT NULL,
  `strategy_months` int(11) DEFAULT NULL,
  `strategy_startdate` date DEFAULT NULL,
  `strategy_expireddate` date DEFAULT NULL,
  `risk_months` int(11) DEFAULT NULL,
  `risk_startdate` date DEFAULT NULL,
  `risk_expireddate` date DEFAULT NULL,
  `sales_months` int(11) DEFAULT NULL,
  `sales_startdate` date DEFAULT NULL,
  `sales_expireddate` date DEFAULT NULL,
  `product_months` int(11) DEFAULT NULL,
  `product_startdate` date DEFAULT NULL,
  `product_expireddate` date DEFAULT NULL,
  `office_months` int(11) DEFAULT NULL,
  `office_startdate` date DEFAULT NULL,
  `office_expireddate` date DEFAULT NULL,
  `company_id` int(10) UNSIGNED NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `toolkit_subscriptions`
--

INSERT INTO `toolkit_subscriptions` (`id`, `email`, `payroll_months`, `payroll_startdate`, `payroll_expireddate`, `recruiting_months`, `recruiting_startdate`, `recruiting_expireddate`, `crm_months`, `crm_startdate`, `crm_expireddate`, `cybersecurity_months`, `cybersecurity_startdate`, `cybersecurity_expireddate`, `project_months`, `project_startdate`, `project_expireddate`, `strategy_months`, `strategy_startdate`, `strategy_expireddate`, `risk_months`, `risk_startdate`, `risk_expireddate`, `sales_months`, `sales_startdate`, `sales_expireddate`, `product_months`, `product_startdate`, `product_expireddate`, `office_months`, `office_startdate`, `office_expireddate`, `company_id`, `created_at`) VALUES
(1, 'nuelodekunle20@gmail.com', 1, '2024-09-05', '2024-10-05', 1, '2024-09-05', '2024-10-05', 1, '2024-09-05', '2024-10-05', 1, '2024-09-05', '2024-10-05', 1, '2024-09-05', '2024-10-05', 1, '2024-09-05', '2024-10-05', 1, '2024-09-05', '2024-10-05', 1, '2024-09-05', '2024-10-05', 1, '2024-09-05', '2024-10-05', 1, '2024-09-05', '2024-10-05', 714980, '2024-09-05 10:08:36');

-- --------------------------------------------------------

--
-- Table structure for table `tools`
--

CREATE TABLE `tools` (
  `id` int(11) NOT NULL,
  `tool_name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `category` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `tools`
--

INSERT INTO `tools` (`id`, `tool_name`, `description`, `category`, `created_at`) VALUES
(1, 'Payroll & Accounting', 'Tools for managing payroll and accounting tasks.', 'Finance', '2024-07-28 08:45:04'),
(2, 'Recruiting & Human Resources', 'Tools for recruiting and managing human resources.', 'HR', '2024-07-28 08:45:04'),
(3, 'Customer Relationship Management', 'Tools for managing customer relationships and interactions.', 'CRM', '2024-07-28 08:45:04'),
(4, 'Cybersecurity Console', 'Tools for ensuring cybersecurity and monitoring threats.', 'Security', '2024-07-28 08:45:04'),
(5, 'Project Management', 'Tools for managing projects and collaboration.', 'Management', '2024-07-28 08:45:04'),
(6, 'Business Strategy', 'Tools for planning and executing business strategies.', 'Strategy', '2024-07-28 08:45:04'),
(7, 'Risk Management & Compliance', 'Tools for managing risks and ensuring compliance with regulations.', 'Compliance', '2024-07-28 08:45:04'),
(8, 'Sales & Marketing', 'Tools for managing sales processes and marketing campaigns.', 'Sales', '2024-07-28 08:45:04'),
(9, 'Product Development', 'Tools for developing and managing products.', 'Development', '2024-07-28 08:45:04'),
(10, 'Office Management & Tasking', 'Tools for managing office tasks and workflows.', 'Administration', '2024-07-28 08:45:04');

-- --------------------------------------------------------

--
-- Table structure for table `tools_assigned`
--

CREATE TABLE `tools_assigned` (
  `id` int(11) NOT NULL,
  `employee_id` int(11) NOT NULL,
  `tool_id` int(11) NOT NULL,
  `assigned_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `company_id` int(11) NOT NULL,
  `assigned_by` int(11) DEFAULT NULL,
  `assignment_notes` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `tools_assigned`
--

INSERT INTO `tools_assigned` (`id`, `employee_id`, `tool_id`, `assigned_at`, `company_id`, `assigned_by`, `assignment_notes`) VALUES
(1, 2, 1, '2024-09-24 09:59:55', 714980, NULL, NULL),
(2, 2, 4, '2024-09-24 09:59:55', 714980, NULL, NULL),
(3, 2, 5, '2024-09-24 09:59:55', 714980, NULL, NULL),
(4, 1, 2, '2024-09-24 10:06:09', 714980, NULL, NULL),
(5, 1, 3, '2024-09-24 10:06:09', 714980, NULL, NULL),
(6, 1, 6, '2024-09-24 10:06:09', 714980, NULL, NULL),
(7, 1, 7, '2024-09-24 10:06:09', 714980, NULL, NULL),
(8, 3, 8, '2024-09-24 10:06:35', 714980, NULL, NULL),
(9, 3, 9, '2024-09-24 10:06:35', 714980, NULL, NULL),
(10, 3, 10, '2024-09-24 10:06:35', 714980, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `totalpayments`
--

CREATE TABLE `totalpayments` (
  `id` int(11) NOT NULL,
  `customername` varchar(255) NOT NULL,
  `companyid` varchar(255) NOT NULL,
  `customeremail` varchar(255) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `currency` varchar(10) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `totalpayments`
--

INSERT INTO `totalpayments` (`id`, `customername`, `companyid`, `customeremail`, `amount`, `currency`, `created_at`) VALUES
(1, 'The Virtual Adviser', '714980', 'nuelodekunle20@gmail.com', 3000.00, 'NGN', '2024-07-24 17:48:55'),
(2, 'The Virtual Adviser', '714980', 'nuelodekunle20@gmail.com', 3000.00, 'NGN', '2024-07-26 16:40:44'),
(3, 'The Virtual Adviser', '714980', 'nuelodekunle20@gmail.com', 7000.00, 'NGN', '2024-07-26 16:42:18'),
(4, 'The Virtual Adviser', '714980', 'nuelodekunle20@gmail.com', 7000.00, 'NGN', '2024-07-26 16:46:25'),
(5, 'The Virtual Adviser', '714980', 'nuelodekunle20@gmail.com', 10500.00, 'NGN', '2024-07-26 16:48:48'),
(6, 'The Virtual Adviser', '714980', 'nuelodekunle20@gmail.com', 7000.00, 'NGN', '2024-07-26 16:51:32'),
(7, 'The Virtual Adviser', '714980', 'nuelodekunle20@gmail.com', 4000.00, 'NGN', '2024-07-26 16:53:10'),
(8, 'The Virtual Adviser', '714980', 'nuelodekunle20@gmail.com', 7500.00, 'NGN', '2024-07-26 16:57:26'),
(9, 'The Virtual Adviser', '714980', 'nuelodekunle20@gmail.com', 3000.00, 'NGN', '2024-07-26 16:58:22'),
(10, 'The Virtual Adviser', '714980', 'nuelodekunle20@gmail.com', 10500.00, 'NGN', '2024-07-26 17:05:22'),
(11, 'The Virtual Adviser', '714980', 'nuelodekunle20@gmail.com', 16000.00, 'NGN', '2024-07-26 17:08:19'),
(12, 'The Virtual Adviser', '714980', 'nuelodekunle20@gmail.com', 34500.00, 'NGN', '2024-07-26 17:20:39'),
(13, 'The Virtual Adviser', '714980', 'nuelodekunle20@gmail.com', 16000.00, 'NGN', '2024-07-26 17:31:43'),
(14, 'The Virtual Adviser', '714980', 'nuelodekunle20@gmail.com', 18000.00, 'NGN', '2024-07-26 17:35:17'),
(15, 'The Virtual Adviser', '714980', 'nuelodekunle20@gmail.com', 36000.00, 'NGN', '2024-07-26 17:39:33'),
(16, 'The Virtual Adviser', '714980', 'nuelodekunle20@gmail.com', 28000.00, 'NGN', '2024-07-26 17:44:00'),
(17, 'The Virtual Adviser', '714980', 'nuelodekunle20@gmail.com', 36000.00, 'NGN', '2024-07-26 17:46:06'),
(18, 'The Virtual Adviser', '714980', 'nuelodekunle20@gmail.com', 15000.00, 'NGN', '2024-07-26 17:57:24'),
(19, 'The Virtual Adviser', '714980', 'nuelodekunle20@gmail.com', 3000.00, 'NGN', '2024-07-26 18:01:09'),
(20, 'The Virtual Adviser', '714980', 'nuelodekunle20@gmail.com', 7500.00, 'NGN', '2024-07-26 18:05:33'),
(21, 'The Virtual Adviser', '714980', 'nuelodekunle20@gmail.com', 10500.00, 'NGN', '2024-07-26 18:08:28'),
(22, 'The Virtual Adviser', '714980', 'nuelodekunle20@gmail.com', 6000.00, 'NGN', '2024-07-26 18:13:37'),
(23, 'The Virtual Adviser', '714980', 'nuelodekunle20@gmail.com', 6000.00, 'NGN', '2024-07-26 18:19:36'),
(24, 'The Virtual Adviser', '714980', 'nuelodekunle20@gmail.com', 13.06, 'USD', '2024-08-01 17:55:24'),
(25, 'The Virtual Adviser', '714980', 'nuelodekunle20@gmail.com', 21.07, 'USD', '2024-08-02 07:45:49'),
(26, 'The Virtual Adviser', '714980', 'nuelodekunle20@gmail.com', 34000.00, 'NGN', '2024-09-05 10:08:37'),
(27, '', '', '', 0.00, '', '2024-09-27 11:39:23');

-- --------------------------------------------------------

--
-- Table structure for table `transactions`
--

CREATE TABLE `transactions` (
  `id` int(11) NOT NULL,
  `transaction_date` date DEFAULT NULL,
  `amount` decimal(10,2) DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL,
  `company_id` int(10) UNSIGNED NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `usdpayment`
--

CREATE TABLE `usdpayment` (
  `id` int(11) NOT NULL,
  `customername` varchar(255) NOT NULL,
  `companyid` varchar(255) NOT NULL,
  `customeremail` varchar(255) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `currency` varchar(10) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `usdpayment`
--

INSERT INTO `usdpayment` (`id`, `customername`, `companyid`, `customeremail`, `amount`, `currency`, `created_at`) VALUES
(1, 'The Virtual Adviser', '714980', 'nuelodekunle20@gmail.com', 13.06, 'USD', '2024-08-01 17:55:24'),
(2, 'The Virtual Adviser', '714980', 'nuelodekunle20@gmail.com', 21.07, 'USD', '2024-08-02 07:45:49');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `industry` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(255) DEFAULT NULL,
  `company_name` varchar(255) DEFAULT NULL,
  `company_id` int(10) UNSIGNED NOT NULL,
  `status` enum('Verified','Unverified','Pending','Suspended') NOT NULL DEFAULT 'Unverified',
  `token` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `industry`, `password`, `email`, `company_name`, `company_id`, `status`, `token`, `created_at`) VALUES
(1, 'insurance', '$2y$10$y34klecf4mKQurnqf6zXf.s4KCFVDoug6k10H/umQFvNp/06hEzHa', 'nuelodekunle20@gmail.com', 'The Virtual Adviser', 714980, 'Verified', '4476c19b28115e7bceaf79ccab3b7fca559d33c55a16b2511dfa55486de3697e', '2024-07-22 12:04:31'),
(2, 'finance', '$2y$10$TAswa49tImUIgu2ShIFqY.1wLb1UlsBbBsSMYMYMCol2tseJInGqi', 'emmanuelodekunle247@gmail.com', 'Grow Social', 897771, 'Verified', '5f5e4391b958293ca00bc56fd310002242fb3fcfe64192a870936890dbb1f76d', '2024-07-23 07:28:25'),
(3, 'food_beverage', '$2y$10$UowD.SII0VGPsmt32Z8J7e6kZzEdLc/YD9W/8vRsFL0T4j9Q4ccDK', 'task@simplr.com.ng', 'Simplr Business Suite', 847772, 'Verified', NULL, '2024-08-13 10:11:00'),
(4, 'legal', '$2y$10$rdmGUUKZiZ/MxZpF8euEgeXMiNjDCWeffya5UyEye7Ifx6Oisth2S', '13-77ja124@students.unilorin.com.ng', 'Certified Now', 102279, 'Unverified', NULL, '2024-09-12 13:33:43'),
(5, 'legal', '$2y$10$P5eztVEKr2f4F873UqHwAe4xa1oGP3ats1hpytKgqaxACmrkOawpy', 'jirdesigno@gufum.com', 'Office 18', 964859, 'Verified', NULL, '2024-09-12 13:44:15'),
(6, 'it', '$2y$10$UeRN80.1J6X3FImwai9oDukCLxQXBHK8uSp80ucKk9CdTFf41rnk6', 'emmajnr1000+Africa@gmail.com', 'syntax joint', 755168, 'Verified', NULL, '2024-09-23 16:12:12'),
(7, 'finance', '$2y$10$kN7j3w7p3rnlb3QFxBWKzuNiJUKgGmwH7zwuhLuY6pt9IN2iV2e12', 'askchronicler@gmail.com', 'Slack Inc.', 571760, 'Unverified', NULL, '2024-09-25 10:37:58'),
(8, 'consulting', '$2y$10$db0or3kUTC3g.aapBkEKMuDIJaR75ZzGHFTCHgeAng9uOd7OnqNay', 'barcodewriters@gmail.com', 'Barcode Writers', 469247, 'Verified', '03bd659d41e12f71081eaab27cfebc26239ae1d21b0e2fd853d269a9893d6f30', '2024-09-25 10:42:20'),
(9, 'it', '$2y$10$r5LdTZP.mECvBPc8IXUYxex.xpahjfLvW1qx3SlJuLh5/U.T/bUv2', 'emeka.ehinze@thinpower.ng', 'Thin Power', 217825, 'Verified', NULL, '2024-09-25 21:11:00'),
(10, 'it', '$2y$10$0gPtdAbMx6624fI5DWJeGe6CDoOeq9S09rM9HxPqIEncPpIf0Ka2q', 'xixlives7@gmail.com', 'elevatedprep.ai', 809309, 'Verified', NULL, '2024-09-26 13:54:52');

-- --------------------------------------------------------

--
-- Table structure for table `widgets`
--

CREATE TABLE `widgets` (
  `id` int(11) NOT NULL,
  `widget_name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `is_default` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `widgets`
--

INSERT INTO `widgets` (`id`, `widget_name`, `description`, `is_default`) VALUES
(1, 'Sales Overview', 'Displays a summary of sales.', 1),
(2, 'Customer Feedback', 'Shows recent customer feedback.', 1),
(3, 'Notifications', 'Displays notifications.', 1),
(4, 'Marketing Analytics', 'Shows marketing performance.', 0),
(5, 'System Health', 'Shows the status of the system.', 1),
(6, 'Team Performance', 'Displays team performance metrics.', 0),
(7, 'Inventory Summary', 'Shows current stock levels.', 0),
(8, 'Financial Summary', 'Shows financial performance.', 1),
(9, 'Upcoming Tasks', 'Lists tasks for the upcoming week.', 1),
(10, 'Calendar', 'Displays company events and meetings.', 0);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `aboutusinfo`
--
ALTER TABLE `aboutusinfo`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `account`
--
ALTER TABLE `account`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `aianalytics`
--
ALTER TABLE `aianalytics`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `aichatbotinteractions`
--
ALTER TABLE `aichatbotinteractions`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `aioptimizations`
--
ALTER TABLE `aioptimizations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `aipredictions`
--
ALTER TABLE `aipredictions`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `aireports`
--
ALTER TABLE `aireports`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `alerts`
--
ALTER TABLE `alerts`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `applications`
--
ALTER TABLE `applications`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `assets`
--
ALTER TABLE `assets`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `attendance`
--
ALTER TABLE `attendance`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `audit`
--
ALTER TABLE `audit`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `bankaccount`
--
ALTER TABLE `bankaccount`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `benefits`
--
ALTER TABLE `benefits`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `bookdemo`
--
ALTER TABLE `bookdemo`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `budgeting`
--
ALTER TABLE `budgeting`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `cashflow`
--
ALTER TABLE `cashflow`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `clients`
--
ALTER TABLE `clients`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `company_widgets`
--
ALTER TABLE `company_widgets`
  ADD PRIMARY KEY (`id`),
  ADD KEY `widget_id` (`widget_id`);

--
-- Indexes for table `contacts`
--
ALTER TABLE `contacts`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `customers`
--
ALTER TABLE `customers`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `cyber_audit_trails`
--
ALTER TABLE `cyber_audit_trails`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `cyber_compliance`
--
ALTER TABLE `cyber_compliance`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `cyber_data_protection`
--
ALTER TABLE `cyber_data_protection`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `cyber_incidents`
--
ALTER TABLE `cyber_incidents`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `cyber_network_monitoring`
--
ALTER TABLE `cyber_network_monitoring`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `cyber_reporting`
--
ALTER TABLE `cyber_reporting`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `cyber_threat_intelligence`
--
ALTER TABLE `cyber_threat_intelligence`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `cyber_user_access`
--
ALTER TABLE `cyber_user_access`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `cyber_vulnerabilities`
--
ALTER TABLE `cyber_vulnerabilities`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `departments`
--
ALTER TABLE `departments`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `employees`
--
ALTER TABLE `employees`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `employees_auth`
--
ALTER TABLE `employees_auth`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `expenses`
--
ALTER TABLE `expenses`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `feedback`
--
ALTER TABLE `feedback`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `interviews`
--
ALTER TABLE `interviews`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `inventory`
--
ALTER TABLE `inventory`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `inventory_items`
--
ALTER TABLE `inventory_items`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `invoices`
--
ALTER TABLE `invoices`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `ledger_entries`
--
ALTER TABLE `ledger_entries`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `logs`
--
ALTER TABLE `logs`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `managers`
--
ALTER TABLE `managers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `manager_email` (`manager_email`);

--
-- Indexes for table `manager_auth`
--
ALTER TABLE `manager_auth`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `nairapayments`
--
ALTER TABLE `nairapayments`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `password_resets`
--
ALTER TABLE `password_resets`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `permissions`
--
ALTER TABLE `permissions`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `productdevelopment`
--
ALTER TABLE `productdevelopment`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `projects`
--
ALTER TABLE `projects`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `support_requests`
--
ALTER TABLE `support_requests`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `talentchat`
--
ALTER TABLE `talentchat`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_chat` (`recruiter_email`,`talent_email`);

--
-- Indexes for table `talentprofile`
--
ALTER TABLE `talentprofile`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `talent_id` (`talent_id`);

--
-- Indexes for table `talents`
--
ALTER TABLE `talents`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `talent_id` (`talent_id`);

--
-- Indexes for table `tasks`
--
ALTER TABLE `tasks`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `teams`
--
ALTER TABLE `teams`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `toolkit_subscriptions`
--
ALTER TABLE `toolkit_subscriptions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `company_id` (`company_id`);

--
-- Indexes for table `tools`
--
ALTER TABLE `tools`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tools_assigned`
--
ALTER TABLE `tools_assigned`
  ADD PRIMARY KEY (`id`),
  ADD KEY `tool_id` (`tool_id`);

--
-- Indexes for table `totalpayments`
--
ALTER TABLE `totalpayments`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `transactions`
--
ALTER TABLE `transactions`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `usdpayment`
--
ALTER TABLE `usdpayment`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `company_id` (`company_id`);

--
-- Indexes for table `widgets`
--
ALTER TABLE `widgets`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `aboutusinfo`
--
ALTER TABLE `aboutusinfo`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `account`
--
ALTER TABLE `account`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `aianalytics`
--
ALTER TABLE `aianalytics`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `aichatbotinteractions`
--
ALTER TABLE `aichatbotinteractions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `aioptimizations`
--
ALTER TABLE `aioptimizations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `aipredictions`
--
ALTER TABLE `aipredictions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `aireports`
--
ALTER TABLE `aireports`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `alerts`
--
ALTER TABLE `alerts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `applications`
--
ALTER TABLE `applications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `assets`
--
ALTER TABLE `assets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `attendance`
--
ALTER TABLE `attendance`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `audit`
--
ALTER TABLE `audit`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `bankaccount`
--
ALTER TABLE `bankaccount`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `benefits`
--
ALTER TABLE `benefits`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `bookdemo`
--
ALTER TABLE `bookdemo`
  MODIFY `id` int(6) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `budgeting`
--
ALTER TABLE `budgeting`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `cashflow`
--
ALTER TABLE `cashflow`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `clients`
--
ALTER TABLE `clients`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `company_widgets`
--
ALTER TABLE `company_widgets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `contacts`
--
ALTER TABLE `contacts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `customers`
--
ALTER TABLE `customers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `cyber_audit_trails`
--
ALTER TABLE `cyber_audit_trails`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `cyber_compliance`
--
ALTER TABLE `cyber_compliance`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `cyber_data_protection`
--
ALTER TABLE `cyber_data_protection`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `cyber_incidents`
--
ALTER TABLE `cyber_incidents`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `cyber_network_monitoring`
--
ALTER TABLE `cyber_network_monitoring`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `cyber_reporting`
--
ALTER TABLE `cyber_reporting`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `cyber_threat_intelligence`
--
ALTER TABLE `cyber_threat_intelligence`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `cyber_user_access`
--
ALTER TABLE `cyber_user_access`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `cyber_vulnerabilities`
--
ALTER TABLE `cyber_vulnerabilities`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `departments`
--
ALTER TABLE `departments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `employees`
--
ALTER TABLE `employees`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `employees_auth`
--
ALTER TABLE `employees_auth`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `expenses`
--
ALTER TABLE `expenses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `feedback`
--
ALTER TABLE `feedback`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `interviews`
--
ALTER TABLE `interviews`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `inventory`
--
ALTER TABLE `inventory`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `inventory_items`
--
ALTER TABLE `inventory_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `invoices`
--
ALTER TABLE `invoices`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `ledger_entries`
--
ALTER TABLE `ledger_entries`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `logs`
--
ALTER TABLE `logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `managers`
--
ALTER TABLE `managers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `manager_auth`
--
ALTER TABLE `manager_auth`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `nairapayments`
--
ALTER TABLE `nairapayments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `password_resets`
--
ALTER TABLE `password_resets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `permissions`
--
ALTER TABLE `permissions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `productdevelopment`
--
ALTER TABLE `productdevelopment`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `projects`
--
ALTER TABLE `projects`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `support_requests`
--
ALTER TABLE `support_requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `talentchat`
--
ALTER TABLE `talentchat`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=157;

--
-- AUTO_INCREMENT for table `talentprofile`
--
ALTER TABLE `talentprofile`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `talents`
--
ALTER TABLE `talents`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `tasks`
--
ALTER TABLE `tasks`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `teams`
--
ALTER TABLE `teams`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `toolkit_subscriptions`
--
ALTER TABLE `toolkit_subscriptions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `tools`
--
ALTER TABLE `tools`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `tools_assigned`
--
ALTER TABLE `tools_assigned`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `totalpayments`
--
ALTER TABLE `totalpayments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT for table `transactions`
--
ALTER TABLE `transactions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `usdpayment`
--
ALTER TABLE `usdpayment`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `widgets`
--
ALTER TABLE `widgets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `company_widgets`
--
ALTER TABLE `company_widgets`
  ADD CONSTRAINT `company_widgets_ibfk_1` FOREIGN KEY (`widget_id`) REFERENCES `widgets` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `tools_assigned`
--
ALTER TABLE `tools_assigned`
  ADD CONSTRAINT `tools_assigned_ibfk_1` FOREIGN KEY (`tool_id`) REFERENCES `tools` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
