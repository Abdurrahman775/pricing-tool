-- Pricing & PRD Generator - Database Schema
-- Engine: InnoDB | Charset: utf8mb4 | Collation: utf8mb4_unicode_ci

CREATE DATABASE IF NOT EXISTS pricing_tool
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE pricing_tool;

-- Users
CREATE TABLE users (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    name VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Pricing Configurations (named, multi-config per user)
CREATE TABLE pricing_configs (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED DEFAULT NULL,
    name VARCHAR(255) NOT NULL DEFAULT 'Standard',
    currency VARCHAR(10) NOT NULL DEFAULT '₦',
    base_rate DECIMAL(10,2) NOT NULL DEFAULT 20000.00,
    is_default TINYINT(1) NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Screen Complexity Tiers
CREATE TABLE complexity_tiers (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    pricing_config_id INT UNSIGNED NOT NULL,
    name VARCHAR(100) NOT NULL,
    multiplier DECIMAL(5,2) NOT NULL DEFAULT 1.00,
    sort_order INT UNSIGNED NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (pricing_config_id) REFERENCES pricing_configs(id) ON DELETE CASCADE,
    INDEX idx_config_id (pricing_config_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Screen Templates (predefined screens per config, selectable as checkboxes on intake)
CREATE TABLE screen_templates (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    pricing_config_id INT UNSIGNED NOT NULL,
    name VARCHAR(255) NOT NULL,
    complexity_tier_id INT UNSIGNED NOT NULL,
    sort_order INT UNSIGNED NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (pricing_config_id) REFERENCES pricing_configs(id) ON DELETE CASCADE,
    FOREIGN KEY (complexity_tier_id) REFERENCES complexity_tiers(id),
    INDEX idx_config_id (pricing_config_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Integration Catalog (predefined + custom per config)
CREATE TABLE integrations (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    pricing_config_id INT UNSIGNED NOT NULL,
    name VARCHAR(255) NOT NULL,
    points INT UNSIGNED NOT NULL DEFAULT 0,
    sort_order INT UNSIGNED NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (pricing_config_id) REFERENCES pricing_configs(id) ON DELETE CASCADE,
    INDEX idx_config_id (pricing_config_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Platform Multipliers
CREATE TABLE platform_multipliers (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    pricing_config_id INT UNSIGNED NOT NULL,
    platform VARCHAR(50) NOT NULL,
    multiplier DECIMAL(4,2) NOT NULL DEFAULT 1.00,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (pricing_config_id) REFERENCES pricing_configs(id) ON DELETE CASCADE,
    UNIQUE KEY uk_config_platform (pricing_config_id, platform)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Package Tiers (Basic / Professional / Premium)
CREATE TABLE package_tiers (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    pricing_config_id INT UNSIGNED NOT NULL,
    name VARCHAR(100) NOT NULL,
    multiplier DECIMAL(4,2) NOT NULL DEFAULT 1.00,
    sort_order INT UNSIGNED NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (pricing_config_id) REFERENCES pricing_configs(id) ON DELETE CASCADE,
    INDEX idx_config_id (pricing_config_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Maintenance Settings
CREATE TABLE maintenance_settings (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    pricing_config_id INT UNSIGNED NOT NULL,
    model ENUM('flat','percentage') NOT NULL DEFAULT 'percentage',
    value DECIMAL(10,2) NOT NULL DEFAULT 15.00,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (pricing_config_id) REFERENCES pricing_configs(id) ON DELETE CASCADE,
    INDEX idx_config_id (pricing_config_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Projects (scope-lockable, versioned)
CREATE TABLE projects (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    pricing_config_id INT UNSIGNED NOT NULL,
    client_name VARCHAR(255) NOT NULL,
    project_name VARCHAR(255) NOT NULL,
    notes TEXT,
    status ENUM('draft','locked') NOT NULL DEFAULT 'draft',
    actual_cost DECIMAL(12,2) DEFAULT NULL,
    actual_hours DECIMAL(8,2) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (pricing_config_id) REFERENCES pricing_configs(id),
    INDEX idx_user_id (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Project Screens
CREATE TABLE project_screens (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    project_id INT UNSIGNED NOT NULL,
    name VARCHAR(255) NOT NULL,
    complexity_tier_id INT UNSIGNED NOT NULL,
    screen_template_id INT UNSIGNED DEFAULT NULL,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE,
    FOREIGN KEY (complexity_tier_id) REFERENCES complexity_tiers(id),
    FOREIGN KEY (screen_template_id) REFERENCES screen_templates(id) ON DELETE SET NULL,
    INDEX idx_project_id (project_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Project Integrations
CREATE TABLE project_integrations (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    project_id INT UNSIGNED NOT NULL,
    integration_id INT UNSIGNED DEFAULT NULL,
    custom_name VARCHAR(255) DEFAULT NULL,
    custom_points INT UNSIGNED DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE,
    FOREIGN KEY (integration_id) REFERENCES integrations(id),
    INDEX idx_project_id (project_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Project Platforms
CREATE TABLE project_platforms (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    project_id INT UNSIGNED NOT NULL,
    platform VARCHAR(50) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE,
    INDEX idx_project_id (project_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Quote Versions (locked scope snapshots with pricing)
CREATE TABLE quote_versions (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    project_id INT UNSIGNED NOT NULL,
    version_number INT UNSIGNED NOT NULL DEFAULT 1,
    screen_points INT UNSIGNED NOT NULL DEFAULT 0,
    integration_points INT UNSIGNED NOT NULL DEFAULT 0,
    platform_multiplier DECIMAL(4,2) NOT NULL DEFAULT 1.00,
    subtotal DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    base_rate DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    base_price DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    selected_package VARCHAR(100) DEFAULT NULL,
    package_price DECIMAL(12,2) DEFAULT NULL,
    maintenance_model ENUM('flat','percentage') DEFAULT NULL,
    maintenance_value DECIMAL(10,2) DEFAULT NULL,
    pricing_breakdown JSON DEFAULT NULL,
    is_locked TINYINT(1) NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE,
    INDEX idx_project_id (project_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Audit Trail
CREATE TABLE audit_log (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED DEFAULT NULL,
    action VARCHAR(100) NOT NULL,
    ip_address VARCHAR(45) NOT NULL,
    user_agent VARCHAR(500) DEFAULT NULL,
    details JSON DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_user_id (user_id),
    INDEX idx_action (action),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
