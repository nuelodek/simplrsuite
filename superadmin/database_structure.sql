CREATE TABLE `aboutusinfo` (
  `id` int NOT NULL,
  `company_history` text COLLATE utf8mb4_general_ci,
  `mission_statement` text COLLATE utf8mb4_general_ci,
  `vision_statement` text COLLATE utf8mb4_general_ci,
  `values` text COLLATE utf8mb4_general_ci,
  `company_id` int unsigned NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `account` (
  `id` int NOT NULL,
  `account_name` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `account_type` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `balance` decimal(10,2) DEFAULT NULL,
  `company_id` int unsigned NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `accounts` (
  `account_id` int NOT NULL,
  `company_id` int unsigned NOT NULL,
  `account_owner` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `account_email` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `account_phone` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `unique_id` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `customer_id` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `status` enum('active','inactive') COLLATE utf8mb4_general_ci DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `aianalytics` (
  `id` int NOT NULL,
  `analysis_type` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `results` text COLLATE utf8mb4_general_ci,
  `company_id` int unsigned NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `aichatbotinteractions` (
  `id` int NOT NULL,
  `user_query` text COLLATE utf8mb4_general_ci,
  `bot_response` text COLLATE utf8mb4_general_ci,
  `company_id` int unsigned NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `aioptimizations` (
  `id` int NOT NULL,
  `optimization_name` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `parameters` text COLLATE utf8mb4_general_ci,
  `results` text COLLATE utf8mb4_general_ci,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `company_id` int unsigned NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `aipredictions` (
  `id` int NOT NULL,
  `model_name` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `input_data` text COLLATE utf8mb4_general_ci,
  `predicted_output` text COLLATE utf8mb4_general_ci,
  `company_id` int unsigned NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `aireports` (
  `id` int NOT NULL,
  `report_name` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `content` text COLLATE utf8mb4_general_ci,
  `company_id` int unsigned NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `alerts` (
  `id` int NOT NULL,
  `company_id` int DEFAULT NULL,
  `title` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `date` date NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `applications` (
  `id` int NOT NULL,
  `applicant_name` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `email` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `phone` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `position_applied_for` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `resume` text COLLATE utf8mb4_general_ci,
  `status` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `company_id` int unsigned NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `assets` (
  `id` int NOT NULL,
  `asset_name` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `description` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `purchase_date` date DEFAULT NULL,
  `value` decimal(10,2) DEFAULT NULL,
  `company_id` int unsigned NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `attendance` (
  `id` int NOT NULL,
  `employee_id` int DEFAULT NULL,
  `date` date DEFAULT NULL,
  `status` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `company_id` int unsigned NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `audit` (
  `id` int NOT NULL,
  `action` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `user_id` int unsigned NOT NULL,
  `company_id` int unsigned NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `bankaccount` (
  `id` int NOT NULL,
  `email` varchar(255) NOT NULL,
  `first_name` varchar(100) NOT NULL,
  `last_name` varchar(100) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `account_number` varchar(50) NOT NULL,
  `bank_name` varchar(100) NOT NULL,
  `company_id` int NOT NULL,
  `user_type` varchar(50) NOT NULL,
  `user_id` int DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `benefits` (
  `id` int NOT NULL,
  `benefit_name` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `description` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `employee_id` int DEFAULT NULL,
  `company_id` int unsigned NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `bookdemo` (
  `id` int unsigned NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `company` varchar(100) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `demo_date` date DEFAULT NULL,
  `demo_time` time DEFAULT NULL,
  `message` text,
  `status` varchar(20) DEFAULT 'Pending',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `preferred_contact_method` enum('email','phone') DEFAULT 'email',
  `industry` varchar(100) DEFAULT NULL,
  `number_of_employees` int DEFAULT NULL,
  `heard_from` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `budgeting` (
  `id` int NOT NULL,
  `budget_name` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `budget_amount` decimal(10,2) DEFAULT NULL,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `company_id` int unsigned NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `cashflow` (
  `id` int NOT NULL,
  `date` date DEFAULT NULL,
  `amount` decimal(10,2) DEFAULT NULL,
  `description` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `company_id` int unsigned NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `clients` (
  `id` int NOT NULL,
  `client_name` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `contact_person` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `email` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `phone` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `address` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `company_id` int unsigned NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `company_widgets` (
  `id` int NOT NULL,
  `company_id` int DEFAULT NULL,
  `widget_id` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `contacts` (
  `contact_id` int NOT NULL,
  `company_id` int unsigned NOT NULL,
  `contact_name` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `contact_email` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `contact_phone` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `unique_id` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `lead_id` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `customer_id` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `customers` (
  `id` int NOT NULL,
  `first_name` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `last_name` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `business_name` text COLLATE utf8mb4_general_ci NOT NULL,
  `acquired_item` text COLLATE utf8mb4_general_ci NOT NULL,
  `customer_email` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `phone_number` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `address` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `customer_id` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `company_id` int unsigned NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `cyber_audit_trails` (
  `id` int NOT NULL,
  `company_id` int NOT NULL,
  `user_id` int NOT NULL,
  `action` text NOT NULL,
  `performed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

CREATE TABLE `cyber_compliance` (
  `id` int NOT NULL,
  `company_id` int NOT NULL,
  `regulation` varchar(255) NOT NULL,
  `status` varchar(50) NOT NULL,
  `checked_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

CREATE TABLE `cyber_data_protection` (
  `id` int NOT NULL,
  `company_id` int NOT NULL,
  `data_type` varchar(255) NOT NULL,
  `protection_status` varchar(50) NOT NULL,
  `assessed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

CREATE TABLE `cyber_incidents` (
  `id` int NOT NULL,
  `company_id` int NOT NULL,
  `description` text NOT NULL,
  `severity` varchar(50) NOT NULL,
  `reported_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `status` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

CREATE TABLE `cyber_network_monitoring` (
  `id` int NOT NULL,
  `company_id` int NOT NULL,
  `device` varchar(255) NOT NULL,
  `status` varchar(50) NOT NULL,
  `monitored_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

CREATE TABLE `cyber_reporting` (
  `id` int NOT NULL,
  `company_id` int NOT NULL,
  `report_type` varchar(255) NOT NULL,
  `generated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

CREATE TABLE `cyber_threat_intelligence` (
  `id` int NOT NULL,
  `company_id` int NOT NULL,
  `threat_type` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `reported_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

CREATE TABLE `cyber_user_access` (
  `id` int NOT NULL,
  `company_id` int NOT NULL,
  `user_id` int NOT NULL,
  `access_level` varchar(255) NOT NULL,
  `assigned_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

CREATE TABLE `cyber_vulnerabilities` (
  `id` int NOT NULL,
  `company_id` int NOT NULL,
  `description` text NOT NULL,
  `severity` varchar(50) NOT NULL,
  `discovered_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `status` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

CREATE TABLE `deals` (
  `deal_id` int NOT NULL,
  `company_id` int unsigned NOT NULL,
  `deal_with` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `lead_id` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `account_id` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `amount` decimal(10,2) DEFAULT NULL,
  `stage` enum('negotiation','proposal','won','lost') COLLATE utf8mb4_general_ci DEFAULT 'negotiation',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `departments` (
  `id` int NOT NULL,
  `department_name` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `manager_id` int DEFAULT NULL,
  `location` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `company_id` int unsigned NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `employees` (
  `id` int NOT NULL,
  `company_id` int NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `hire_date` date NOT NULL,
  `job_title` varchar(100) DEFAULT NULL,
  `department_id` int NOT NULL,
  `manager_id` int NOT NULL,
  `role_id` int NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `unique_id` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `employees_auth` (
  `id` int NOT NULL,
  `company_id` int NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `employee_id` varchar(255) DEFAULT NULL,
  `logout_time` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `events` (
  `event_id` int NOT NULL,
  `event_name` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `event_date` date NOT NULL,
  `start_datetime` datetime NOT NULL,
  `end_datetime` datetime NOT NULL,
  `agenda` text COLLATE utf8mb4_general_ci,
  `event_type` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `meetup_venue` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `meet_link` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `attendees` int DEFAULT '0',
  `event_description` text COLLATE utf8mb4_general_ci,
  `event_images` text COLLATE utf8mb4_general_ci,
  `organizer` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `company_id` int NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `expenses` (
  `id` int NOT NULL,
  `date` date NOT NULL,
  `description` varchar(255) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `company_id` int unsigned NOT NULL,
  `category` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `feedback` (
  `id` int NOT NULL,
  `feedback_date` date DEFAULT NULL,
  `feedback_content` text COLLATE utf8mb4_general_ci,
  `company_id` int unsigned NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `interviews` (
  `id` int unsigned NOT NULL,
  `talent_id` int unsigned NOT NULL,
  `date` date NOT NULL,
  `time` time NOT NULL,
  `interviewer` varchar(255) NOT NULL,
  `attended` tinyint(1) DEFAULT '0',
  `feedback` text,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `company_id` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `inventory` (
  `id` int NOT NULL,
  `product_name` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `quantity` int DEFAULT NULL,
  `unit_price` decimal(10,2) DEFAULT NULL,
  `description` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `company_id` int unsigned NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `inventory_items` (
  `id` int NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text,
  `company_id` int unsigned NOT NULL,
  `quantity` int NOT NULL,
  `unit_price` decimal(10,2) NOT NULL,
  `category` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `invoices` (
  `id` int NOT NULL,
  `invoice_date` date DEFAULT NULL,
  `due_date` date DEFAULT NULL,
  `amount` decimal(10,2) DEFAULT NULL,
  `status` enum('Paid','Unpaid','Cancelled') COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'Unpaid',
  `customer_id` int DEFAULT NULL,
  `company_id` int DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `leads` (
  `lead_id` int NOT NULL,
  `company_id` int unsigned NOT NULL,
  `lead_name` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `lead_email` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `lead_source` enum('Website','Social-Media','Email','Referral') COLLATE utf8mb4_general_ci NOT NULL,
  `social_media_platform` text COLLATE utf8mb4_general_ci,
  `website_url` text COLLATE utf8mb4_general_ci,
  `email_campaign` text COLLATE utf8mb4_general_ci,
  `lead_phone` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `status` enum('new','contacted','qualified','lost') COLLATE utf8mb4_general_ci DEFAULT 'new',
  `unique_id` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `ledger_entries` (
  `id` int NOT NULL,
  `date` date NOT NULL,
  `company_id` int unsigned NOT NULL,
  `description` varchar(255) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `type` varchar(50) NOT NULL,
  `category` varchar(50) NOT NULL,
  `reference_id` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `logs` (
  `id` int NOT NULL,
  `type` varchar(255) NOT NULL,
  `user_id` int NOT NULL,
  `action` varchar(100) NOT NULL,
  `details` text NOT NULL,
  `company_id` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `manager_auth` (
  `id` int NOT NULL,
  `company_id` int NOT NULL,
  `manager_name` varchar(255) NOT NULL,
  `manager_email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `manager_id` varchar(255) DEFAULT NULL,
  `logout_time` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `managers` (
  `id` int NOT NULL,
  `company_id` int NOT NULL,
  `unique_id` varchar(255) DEFAULT NULL,
  `manager_name` varchar(255) NOT NULL,
  `manager_email` varchar(255) NOT NULL,
  `department_id` int NOT NULL,
  `role_id` int NOT NULL,
  `status` varchar(50) NOT NULL DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `meetings` (
  `meeting_id` int NOT NULL,
  `company_id` int unsigned NOT NULL,
  `meeting_title` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `meeting_date` date DEFAULT NULL,
  `meeting_time` time DEFAULT NULL,
  `location` enum('online','in-person') COLLATE utf8mb4_general_ci DEFAULT NULL,
  `online_platform` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `meeting_address` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `details` text COLLATE utf8mb4_general_ci,
  `attendee` text COLLATE utf8mb4_general_ci NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `messages` (
  `id` int NOT NULL,
  `sender_id` int NOT NULL,
  `recipient_id` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `message` text COLLATE utf8mb4_general_ci NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `status` enum('sent','delivered','read') COLLATE utf8mb4_general_ci DEFAULT 'sent'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `nairapayments` (
  `id` int NOT NULL,
  `customername` varchar(255) NOT NULL,
  `companyid` varchar(255) NOT NULL,
  `customeremail` varchar(255) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `currency` varchar(10) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `orders` (
  `id` int NOT NULL,
  `order_date` date DEFAULT NULL,
  `customer_id` int DEFAULT NULL,
  `status` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `company_id` int unsigned NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `password_resets` (
  `id` int NOT NULL,
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `permissions` (
  `id` int NOT NULL,
  `permission_name` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `description` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `role_id` int DEFAULT NULL,
  `company_id` int unsigned NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `productdevelopment` (
  `id` int NOT NULL,
  `product_name` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `stage` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `status` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `company_id` int unsigned NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `projects` (
  `id` int NOT NULL,
  `projectName` varchar(255) NOT NULL,
  `projectDescription` text,
  `startDate` date DEFAULT NULL,
  `endDate` date DEFAULT NULL,
  `department` varchar(50) DEFAULT NULL,
  `status` varchar(50) DEFAULT NULL,
  `projectManager` int DEFAULT NULL,
  `company_id` int DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `project_manager_id` varchar(255) DEFAULT NULL,
  `unique_id` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `roles` (
  `id` int NOT NULL,
  `role_name` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `description` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `company_id` int unsigned NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

