-- Create the database
CREATE DATABASE IF NOT EXISTS user_management_system;
USE user_management_system;
-- Users table (base class for both Admin and Employee)
CREATE TABLE users (
    user_id VARCHAR(36) PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    role ENUM('admin', 'employee') NOT NULL,
    password VARCHAR(255) NOT NULL,
    profile_picture VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
-- Content table (created by Admin users)
CREATE TABLE content (
    content_id VARCHAR(36) PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    body TEXT,
    type ENUM('article', 'news', 'announcement', 'policy') NOT NULL,
    created_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    created_by VARCHAR(36) NOT NULL,
    published BOOLEAN DEFAULT FALSE,
    published_at TIMESTAMP NULL,
    FOREIGN KEY (created_by) REFERENCES users(user_id) ON DELETE CASCADE
);
-- Coupons table (issued by Admin users)
CREATE TABLE coupons (
    coupon_id VARCHAR(36) PRIMARY KEY,
    description TEXT,
    partner_name VARCHAR(255) NOT NULL,
    discount_rate DECIMAL(5,2) NOT NULL CHECK (discount_rate > 0 AND discount_rate <= 100),
    expiry_date DATE NOT NULL,
    usage_count INT DEFAULT 0,
    issued_by VARCHAR(36) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (issued_by) REFERENCES users(user_id) ON DELETE CASCADE
);
-- Coupon redemptions table (to track which employees redeemed which coupons)
CREATE TABLE coupon_redemptions (
    redemption_id VARCHAR(36) PRIMARY KEY,
    coupon_id VARCHAR(36) NOT NULL,
    employee_id VARCHAR(36) NOT NULL,
    redeemed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (coupon_id) REFERENCES coupons(coupon_id) ON DELETE CASCADE,
    FOREIGN KEY (employee_id) REFERENCES users(user_id) ON DELETE CASCADE,
    UNIQUE KEY unique_coupon_employee (coupon_id, employee_id)
);
-- Insert sample admin user
INSERT INTO users (user_id, name, email, role, password) 
VALUES ('admin001', 'System Administrator', 'admin@company.com', 'admin', 'hashed_password_123');
-- Insert sample employee user
INSERT INTO users (user_id, name, email, role, password) 
VALUES ('emp001', 'John Doe', 'john.doe@company.com', 'employee', 'hashed_password_456');
-- Insert sample content
INSERT INTO content (content_id, title, body, type, created_by, published, published_at)
VALUES ('cont001', 'Welcome to Our Company', 'This is the welcome message...', 'announcement', 'admin001', TRUE, NOW());
-- Insert sample coupon
INSERT INTO coupons (coupon_id, description, partner_name, discount_rate, expiry_date, issued_by)
VALUES ('coup001', '20% off at Coffee Shop', 'Coffee Shop Inc.', 20.00, '2023-12-31', 'admin001');
-- Sample coupon redemption
INSERT INTO coupon_redemptions (redemption_id, coupon_id, employee_id)
VALUES ('redemp001', 'coup001', 'emp001');
-- Update coupon usage count after redemption
UPDATE coupons SET usage_count = usage_count + 1 WHERE coupon_id = 'coup001';
i have this data base can you create the interface i want it to be proffissional