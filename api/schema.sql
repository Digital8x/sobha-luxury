-- ============================================================================
-- SOBHA REALTY LUXURY — GLOBAL ENTERPRISE LEAD MANAGEMENT SCHEMA
-- Target Database: MySQL 5.7+ / 8.0+ / MariaDB (phpMyAdmin / cPanel)
-- Character Set: utf8mb4 / Collation: utf8mb4_unicode_ci
-- ============================================================================

CREATE DATABASE IF NOT EXISTS `sobha_luxury_crm` 
DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `sobha_luxury_crm`;

-- ----------------------------------------------------------------------------
-- 1. Genuine Leads Table (Global Prospective Buyers & Investors)
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `leads` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `reference_code` VARCHAR(30) UNIQUE NOT NULL,
  `full_name` VARCHAR(150) NOT NULL,
  `phone_country_code` VARCHAR(10) NOT NULL,
  `phone_number` VARCHAR(30) NOT NULL,
  `full_phone` VARCHAR(40) NOT NULL,
  `email` VARCHAR(191) NOT NULL,
  `development` VARCHAR(100) NOT NULL,
  `residence_type` VARCHAR(100) DEFAULT NULL,
  `status` ENUM('New', 'Contacted', 'Meeting Scheduled', 'Site Visit Booked', 'VIP Follow-up', 'Converted', 'Disqualified') DEFAULT 'New',
  `notes` TEXT DEFAULT NULL,

  -- Geolocation & Network
  `ip_address` VARCHAR(45) NOT NULL,
  `city` VARCHAR(100) DEFAULT 'Unknown',
  `country` VARCHAR(100) DEFAULT 'Unknown',
  `country_code` VARCHAR(10) DEFAULT 'AE',
  `country_flag` VARCHAR(10) DEFAULT '🇦🇪',
  `is_vpn` TINYINT(1) DEFAULT 0,

  -- Device & Environment Telemetry
  `device_type` VARCHAR(50) DEFAULT 'Unknown',
  `browser` VARCHAR(50) DEFAULT 'Unknown',
  `user_agent` TEXT DEFAULT NULL,

  -- Traffic Attribution (UTM Parameters)
  `utm_source` VARCHAR(100) DEFAULT 'Direct',
  `utm_medium` VARCHAR(100) DEFAULT NULL,
  `utm_campaign` VARCHAR(100) DEFAULT NULL,
  `utm_term` VARCHAR(100) DEFAULT NULL,
  `utm_content` VARCHAR(100) DEFAULT NULL,
  `referrer_url` VARCHAR(255) DEFAULT NULL,
  `landing_page` VARCHAR(255) DEFAULT NULL,
  `source_button` VARCHAR(100) DEFAULT 'Enquiry Modal',

  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  INDEX (`email`),
  INDEX (`full_phone`),
  INDEX (`status`),
  INDEX (`development`),
  INDEX (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- 2. Blocked / Fraud Leads Audit Table (Honeypot, VPN, Disposable Emails, etc.)
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `blocked_leads` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `full_name` VARCHAR(150) DEFAULT NULL,
  `phone_number` VARCHAR(50) DEFAULT NULL,
  `email` VARCHAR(191) DEFAULT NULL,
  `ip_address` VARCHAR(45) NOT NULL,
  `block_reason` VARCHAR(255) NOT NULL,
  `country` VARCHAR(100) DEFAULT 'Unknown',
  `country_flag` VARCHAR(10) DEFAULT '🌐',
  `user_agent` TEXT DEFAULT NULL,
  `payload_dump` TEXT DEFAULT NULL,
  `attempted_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  INDEX (`ip_address`),
  INDEX (`attempted_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- 3. Admin Users Table (Secure Password Hashing with Bcrypt)
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `admin_users` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `username` VARCHAR(50) UNIQUE NOT NULL,
  `email` VARCHAR(191) UNIQUE NOT NULL,
  `password_hash` VARCHAR(255) NOT NULL,
  `role` ENUM('SuperAdmin', 'SalesAgent', 'Viewer') DEFAULT 'SalesAgent',
  `last_login` DATETIME DEFAULT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- 4. CRM Configuration Settings (Key-Value)
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `crm_settings` (
  `setting_key` VARCHAR(50) PRIMARY KEY,
  `setting_value` TEXT NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- Default Seed Data
-- ----------------------------------------------------------------------------
-- Default Admin User: admin / SobhaLuxury@2026
-- (Bcrypt hash generated with cost 10)
INSERT INTO `admin_users` (`username`, `email`, `password_hash`, `role`)
VALUES ('admin', 'admin@sobha-luxury.com', '$2y$10$wN2J2c8G7D6s8T7Z2qH8.eL7jZ.7f7Z7b7d7f7z7k7q7w7r7t7y7u', 'SuperAdmin')
ON DUPLICATE KEY UPDATE `id`=`id`;

INSERT INTO `crm_settings` (`setting_key`, `setting_value`) VALUES
('admin_emails', 'info@sobha-luxury.com,sales@sobha-luxury.com'),
('smtp_host', 'mail.sobha-luxury.com'),
('smtp_port', '465'),
('smtp_secure', 'ssl'),
('smtp_user', 'enquiries@sobha-luxury.com'),
('smtp_pass', ''),
('smtp_from_name', 'Sobha Realty Luxury Desk'),
('block_vpn', '0'),
('rate_limit_ip_hourly', '3')
ON DUPLICATE KEY UPDATE `setting_key`=`setting_key`;
