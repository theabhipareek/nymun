-- Database Schema for Certificate Verification System
-- Current UTC Time: 2025-05-05 16:56:17
-- User: theabhipareek

-- Create database (if it doesn't exist)
CREATE DATABASE IF NOT EXISTS nymun_certificates;
USE nymun_certificates;

-- Create certificates table
CREATE TABLE IF NOT EXISTS certificates (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    certificate_id VARCHAR(10) NOT NULL UNIQUE,
    event VARCHAR(50) NOT NULL,
    position VARCHAR(50),
    committee VARCHAR(100),
    issue_date DATE NOT NULL,
    is_valid TINYINT(1) NOT NULL DEFAULT 1,
    last_verified DATETIME NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NULL ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_certificate_id (certificate_id),
    INDEX idx_name (name),
    INDEX idx_event (event)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create users table (for admin access)
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(100),
    is_admin TINYINT(1) NOT NULL DEFAULT 0,
    last_login DATETIME NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NULL ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create settings table
CREATE TABLE IF NOT EXISTS settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    setting_name VARCHAR(50) NOT NULL UNIQUE,
    setting_value TEXT,
    updated_at DATETIME NULL ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default admin user (password should be securely hashed in production)
-- Default credentials: username: theabhipareek, password: admin123
INSERT INTO users (username, password, email, is_admin)
VALUES ('theabhipareek', '$2y$10$1234567890123456789012uCYJa/IMBMEYMjl4Ot/dBl5jdJLNFSO', 'admin@nymun.org', 1)
ON DUPLICATE KEY UPDATE username = VALUES(username);

-- Insert default settings
INSERT INTO settings (setting_name, setting_value)
VALUES 
('site_title', 'NYMUN Certificate Verification'),
('contact_email', 'contact@nymun.org'),
('footer_text', '© 2025 NYMUN. All rights reserved.'),
('maintenance_mode', '0')
ON DUPLICATE KEY UPDATE setting_name = VALUES(setting_name);

-- Insert sample certificates (for testing)
INSERT INTO certificates (name, certificate_id, event, position, committee, issue_date, is_valid)
VALUES 
('John Doe', 'ABC123', 'NYMUN 2023', 'Delegate', 'Security Council', '2023-05-15', 1),
('Jane Smith', 'DEF456', 'NYMUN 2023', 'Chair', 'Human Rights Council', '2023-05-15', 1),
('Robert Johnson', 'GHI789', 'NYMUN 2024', 'Delegate', 'General Assembly', '2024-06-20', 1),
('Maria Garcia', 'JKL012', 'NYMUN 2024', 'Vice Chair', 'Economic and Social Council', '2024-06-20', 1),
('James Wilson', 'MNO345', 'NYMUN 2022', 'Delegate', 'World Health Organization', '2022-04-10', 0)
ON DUPLICATE KEY UPDATE certificate_id = VALUES(certificate_id);
