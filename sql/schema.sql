-- ============================================================
-- DriveEase Database Schema with Performance Indexes
-- ============================================================
-- ✅ OPTIMIZATION #8: Added database indexes for common queries
-- ============================================================

-- Create users table
CREATE TABLE IF NOT EXISTS users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    full_name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    phone_number VARCHAR(20),
    password_hash VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Create cars table
CREATE TABLE IF NOT EXISTS cars (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    brand VARCHAR(100) NOT NULL,
    category VARCHAR(50),
    price DECIMAL(10, 2) NOT NULL,
    image_url VARCHAR(255),
    available TINYINT DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create bookings table
CREATE TABLE IF NOT EXISTS bookings (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    car_id INT NOT NULL,
    pickup_date DATE NOT NULL,
    return_date DATE NOT NULL,
    total_price DECIMAL(10, 2) NOT NULL,
    status VARCHAR(50) DEFAULT 'confirmed',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (car_id) REFERENCES cars(id)
);

-- ============================================================
-- ✅ OPTIMIZATION #8: Create indexes for common queries
-- ============================================================

-- Index for login queries: SELECT * FROM users WHERE email = ?
CREATE INDEX idx_users_email ON users(email);

-- Index for car availability checks: WHERE available = 1
CREATE INDEX idx_cars_available ON cars(available);

-- Index for user bookings: SELECT FROM bookings WHERE user_id = ?
CREATE INDEX idx_bookings_user_id ON bookings(user_id);

-- Index for booking details: SELECT FROM bookings WHERE car_id = ?
CREATE INDEX idx_bookings_car_id ON bookings(car_id);

-- Composite index for date range queries
CREATE INDEX idx_bookings_dates ON bookings(pickup_date, return_date);

-- ============================================================
-- Insert sample data
-- ============================================================

INSERT INTO users (full_name, email, phone_number, password_hash) VALUES
('Admin User', 'admin@driveease.com', '+1 234 567 8900', '$2y$10$DjW9E6yV8f5XqZ8K7LjLk.M6xT9V3K2B1L0P9M8N7Q6R5S4T3U2V1'),
('John Doe', 'john@example.com', '+1 111 222 3333', '$2y$10$DjW9E6yV8f5XqZ8K7LjLk.M6xT9V3K2B1L0P9M8N7Q6R5S4T3U2V1');

INSERT INTO cars (name, brand, category, price, image_url, available) VALUES
('Audi Q7', 'Audi', 'SUV', 8000, 'https://via.placeholder.com/300x200?text=Audi+Q7', 1),
('Honda Accord', 'Honda', 'Sedan', 6500, 'https://via.placeholder.com/300x200?text=Honda+Accord', 1);

INSERT INTO bookings (user_id, car_id, pickup_date, return_date, total_price, status) VALUES
(2, 1, '2025-05-25', '2025-05-28', 24000, 'confirmed');
