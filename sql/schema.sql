-- ============================================================
-- DriveEase - Car Rental System
-- Database Schema for MySQL
-- ============================================================
-- HOW TO USE:
--   1. Open phpMyAdmin (http://localhost/phpmyadmin)
--   2. Click "New" to create a database named: driveease
--   3. Select the database, click "SQL" tab
--   4. Paste this entire file and click "Go"
-- ============================================================

-- Create and select the database
CREATE DATABASE IF NOT EXISTS driveease CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE driveease;

-- ────────────────────────────────────────────
-- TABLE: users
-- Stores registered user accounts
-- ────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS users (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    name        VARCHAR(100)  NOT NULL,            -- Full name
    email       VARCHAR(150)  NOT NULL UNIQUE,      -- Login email (must be unique)
    password    VARCHAR(255)  NOT NULL,             -- Hashed password (bcrypt)
    phone       VARCHAR(20)   DEFAULT NULL,         -- Optional phone number
    role        ENUM('user','admin') DEFAULT 'user', -- User role
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ────────────────────────────────────────────
-- TABLE: cars
-- Stores the rental car inventory
-- ────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS cars (
    id           INT AUTO_INCREMENT PRIMARY KEY,
    name         VARCHAR(100)  NOT NULL,            -- Car model name (e.g. Audi Q7)
    brand        VARCHAR(50)   NOT NULL,            -- Brand (e.g. Audi)
    price        DECIMAL(10,2) NOT NULL,            -- Price per day in DZD (Algerian Dinar)
    image_url    VARCHAR(500)  NOT NULL,            -- Path to car image
    available    TINYINT(1)    DEFAULT 1,           -- 1 = available, 0 = not available
    created_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ────────────────────────────────────────────
-- TABLE: bookings
-- Stores all car rental reservations
-- ────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS bookings (
    id             INT AUTO_INCREMENT PRIMARY KEY,
    user_id        INT          NOT NULL,
    car_id         INT          NOT NULL,
    pickup_date    DATE         NOT NULL,
    return_date    DATE         NOT NULL,
    total_price    DECIMAL(10,2) NOT NULL,
    status         ENUM('pending','confirmed','cancelled') DEFAULT 'pending',
    created_at     TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    -- Foreign keys: link to users and cars tables
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (car_id)  REFERENCES cars(id)  ON DELETE CASCADE
);

-- ────────────────────────────────────────────
-- SAMPLE DATA: Cars (only 2 cars)
-- Prices are in Algerian Dinar (DZD)
-- ────────────────────────────────────────────
INSERT INTO cars (name, brand, price, image_url) VALUES
('Audi Q7',      'Audi',  15000.00, 'images/audi_q7.png'),
('Honda Accord', 'Honda',  8000.00, 'images/honda_accord.png');

-- ────────────────────────────────────────────
-- SAMPLE DATA: Admin user
-- Password: admin123  (hashed with bcrypt)
-- ────────────────────────────────────────────
INSERT INTO users (name, email, password, role) VALUES
('Admin', 'admin@driveease.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/agi', 'admin');
-- Note: The hash above = "password" — change it immediately in production!
