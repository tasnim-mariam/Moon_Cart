-- ============================================
-- MoonCart Database Schema
-- Run this SQL in phpMyAdmin or MySQL CLI
-- ============================================

-- Create Database
CREATE DATABASE IF NOT EXISTS mooncart_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE mooncart_db;

-- ============================================
-- 1. USERS TABLE (Customers & Admins)
-- ============================================
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    phone VARCHAR(20),
    role ENUM('customer', 'admin') DEFAULT 'customer',
    avatar VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- ============================================
-- 2. CATEGORIES TABLE
-- ============================================
CREATE TABLE IF NOT EXISTS categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(100) NOT NULL UNIQUE,
    icon VARCHAR(50),
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ============================================
-- 3. PRODUCTS TABLE
-- ============================================
CREATE TABLE IF NOT EXISTS products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(200) NOT NULL,
    slug VARCHAR(200) NOT NULL,
    description TEXT,
    price DECIMAL(10, 2) NOT NULL,
    original_price DECIMAL(10, 2) DEFAULT NULL,
    image VARCHAR(500),
    category_id INT,
    badge VARCHAR(50) DEFAULT NULL,
    stock INT DEFAULT 100,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL
);

-- ============================================
-- 4. ADDRESSES TABLE
-- ============================================
CREATE TABLE IF NOT EXISTS addresses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    label VARCHAR(50) DEFAULT 'Home',
    address_line VARCHAR(255) NOT NULL,
    city VARCHAR(100) NOT NULL,
    zip_code VARCHAR(20),
    phone VARCHAR(20),
    is_default BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- ============================================
-- 5. DELIVERY MEN TABLE
-- ============================================
CREATE TABLE IF NOT EXISTS delivery_men (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    nid VARCHAR(50) NOT NULL UNIQUE,
    profile_image VARCHAR(500),
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- ============================================
-- 6. ORDERS TABLE
-- ============================================
CREATE TABLE IF NOT EXISTS orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_number VARCHAR(20) NOT NULL UNIQUE,
    user_id INT NOT NULL,
    customer_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    address VARCHAR(255) NOT NULL,
    city VARCHAR(100) NOT NULL,
    zip_code VARCHAR(20),
    delivery_slot VARCHAR(50),
    delivery_instructions TEXT,
    payment_method VARCHAR(50) DEFAULT 'card',
    subtotal DECIMAL(10, 2) NOT NULL,
    tax DECIMAL(10, 2) DEFAULT 0.00,
    shipping DECIMAL(10, 2) DEFAULT 0.00,
    total DECIMAL(10, 2) NOT NULL,
    status ENUM('pending', 'confirmed', 'preparing', 'out_for_delivery', 'delivered', 'cancelled') DEFAULT 'pending',
    delivery_man_id INT,
    estimated_delivery_time DATETIME,
    cancellation_reason TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (delivery_man_id) REFERENCES delivery_men(id) ON DELETE SET NULL
);

-- ============================================
-- 7. ORDER ITEMS TABLE
-- ============================================
CREATE TABLE IF NOT EXISTS order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    product_id INT,
    product_name VARCHAR(200) NOT NULL,
    price DECIMAL(10, 2) NOT NULL,
    quantity INT NOT NULL DEFAULT 1,
    total DECIMAL(10, 2) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE SET NULL
);

-- ============================================
-- 8. PRODUCT REQUESTS TABLE
-- ============================================
CREATE TABLE IF NOT EXISTS product_requests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    product_name VARCHAR(200) NOT NULL,
    category VARCHAR(50),
    description TEXT,
    email VARCHAR(100),
    status ENUM('pending', 'under_review', 'approved', 'rejected') DEFAULT 'pending',
    admin_notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);

-- ============================================
-- 9. CART TABLE (User-specific carts)
-- ============================================
CREATE TABLE IF NOT EXISTS cart (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    product_id INT NOT NULL,
    product_name VARCHAR(200) NOT NULL,
    price DECIMAL(10, 2) NOT NULL,
    image VARCHAR(500),
    category VARCHAR(100),
    quantity INT NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_product (user_id, product_id)
);

-- ============================================
-- 10. CONTACT MESSAGES TABLE
-- ============================================
CREATE TABLE IF NOT EXISTS contact_messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    subject VARCHAR(200),
    message TEXT NOT NULL,
    is_read BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ============================================
-- INSERT SAMPLE DATA
-- ============================================

-- Insert Categories
INSERT INTO categories (name, slug, icon, description) VALUES
('Handmade Food', 'handmade', '🍛', 'Fresh homemade Bengali food'),
('Dry Food & Snacks', 'dryfood', '🍪', 'Chips, cookies, instant noodles'),
('Emergency Medicine', 'medicine', '💊', 'Essential medicines for emergencies'),
('Beverages', 'beverages', '🥤', 'Drinks, coffee, energy drinks'),
('Daily Essentials', 'grocery', '🛒', 'Rice, eggs, daily necessities');

-- Insert Admin User (Password: password)
-- NOTE: The password for all test accounts is: password
INSERT INTO users (name, email, password, phone, role) VALUES
('Admin MoonCart', 'admin@mooncart.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '+880171234567', 'admin');

-- Insert Customer Users (Password: password)
INSERT INTO users (name, email, password, phone, role) VALUES
('Rahim Ahmed', 'rahim@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '+8801712345678', 'customer'),
('Fatima Khan', 'fatima@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '+8801898765432', 'customer');

-- Insert Products
INSERT INTO products (name, slug, description, price, original_price, image, category_id, badge, stock) VALUES
-- Handmade Food (category_id = 1)
('Chicken Biryani (Handmade)', 'chicken-biryani', 'Authentic homemade chicken biryani with aromatic rice', 350.00, NULL, 'https://images.unsplash.com/photo-1563379091339-03b21ab4a4f8?w=400&h=300&fit=crop', 1, 'Hot', 50),
('Khichuri with Chicken', 'khichuri-chicken', 'Bengali comfort food with rice, lentils & chicken', 250.00, NULL, 'https://images.unsplash.com/photo-1645177628172-a94c1f96e6db?w=400&h=300&fit=crop', 1, 'Comfort', 40),
('Samosa & Singara (6 pcs)', 'samosa-singara', 'Freshly fried crispy samosas with potato filling', 120.00, NULL, 'https://images.unsplash.com/photo-1601050690597-df0568f70950?w=400&h=300&fit=crop', 1, 'Popular', 100),
('Morog Polao (Handmade)', 'morog-polao', 'Traditional Bengali chicken pulao', 380.00, NULL, 'https://images.unsplash.com/photo-1633945274309-78d05702ff06?w=400&h=300&fit=crop', 1, 'Special', 30),

-- Dry Food & Snacks (category_id = 2)
('Instant Noodles Pack (5 pcs)', 'instant-noodles', 'Quick instant noodles - just add hot water!', 180.00, NULL, 'https://images.unsplash.com/photo-1569718212165-3a8278d5f624?w=400&h=300&fit=crop', 2, 'Quick', 200),
('Premium Potato Chips (Family Pack)', 'potato-chips', 'Crispy chips perfect for late-night cravings', 150.00, NULL, 'https://images.unsplash.com/photo-1566478989037-eec170784d0b?w=400&h=300&fit=crop', 2, 'Deal', 150),
('Energy Bars & Dry Fruits Mix', 'energy-bars-mix', 'Healthy snack with almonds, cashews & dates', 320.00, NULL, 'https://images.unsplash.com/photo-1599599810769-bcde5a160d32?w=400&h=300&fit=crop', 2, 'Healthy', 80),
('Chocolate Chip Cookies (Family Pack)', 'chocolate-cookies', 'Soft, chewy cookies with chocolate chunks', 220.00, NULL, 'https://images.unsplash.com/photo-1499636136210-6f4ee915583e?w=400&h=300&fit=crop', 2, 'Sweet', 120),

-- Medicine (category_id = 3)
('Paracetamol 500mg (Emergency)', 'paracetamol', 'Fast-acting fever & pain relief medicine', 80.00, NULL, 'https://images.unsplash.com/photo-1587854692152-cbe660dbde88?w=400&h=300&fit=crop', 3, 'Emergency', 300),
('Cold & Flu Relief Kit', 'cold-flu-kit', 'Complete kit with cough syrup & nasal spray', 280.00, NULL, 'https://images.unsplash.com/photo-1584308666744-24d5c474f2ae?w=400&h=300&fit=crop', 3, 'Kit', 60),
('First Aid Kit (Emergency)', 'first-aid-kit', 'Complete first aid kit with essential supplies', 450.00, NULL, 'https://images.unsplash.com/photo-1603398938378-e54eab446dde?w=400&h=300&fit=crop', 3, 'Essential', 40),

-- Beverages (category_id = 4)
('Energy Drink Pack (4 cans)', 'energy-drink', 'Boost energy for late-night work or study', 240.00, NULL, 'https://images.unsplash.com/photo-1622543925917-763c34f7f0a6?w=400&h=300&fit=crop', 4, 'Energy', 100),
('Instant Coffee Sachets (20 pcs)', 'instant-coffee', 'Quick caffeine fix - 3-in-1 instant coffee', 280.00, NULL, 'https://images.unsplash.com/photo-1544145945-f90425340c7e?w=400&h=300&fit=crop', 4, NULL, 150),
('Bottled Mineral Water (6 bottles)', 'mineral-water', 'Pure mineral water for hydration', 120.00, NULL, 'https://images.unsplash.com/photo-1548839140-29a749e1cf4d?w=400&h=300&fit=crop', 4, 'Essential', 200),

-- Grocery (category_id = 5)
('Premium Basmati Rice (2kg)', 'basmati-rice', 'Premium basmati rice - kitchen essential', 420.00, NULL, 'https://images.unsplash.com/photo-1509440159596-0249088772ff?w=400&h=300&fit=crop', 5, NULL, 100),
('Fresh Farm Eggs (12 pcs)', 'farm-eggs', 'Fresh eggs from free-range chickens', 140.00, NULL, 'https://images.unsplash.com/photo-1582722872445-44dc5f7e3c8f?w=400&h=300&fit=crop', 5, 'Fresh', 80);

-- Insert Addresses for Customers
INSERT INTO addresses (user_id, label, address_line, city, zip_code, phone, is_default) VALUES
(2, 'Home', 'House 45, Road 12, Bashundhara R/A', 'Dhaka', '1229', '+8801712345678', TRUE),
(2, 'Office', 'Level 5, Building ABC, Gulshan-2', 'Dhaka', '1212', '+8801712345678', FALSE),
(3, 'Home', 'Flat 3B, Green Tower, Dhanmondi', 'Dhaka', '1205', '+8801898765432', TRUE);

-- Insert Sample Orders
INSERT INTO orders (order_number, user_id, customer_name, email, phone, address, city, zip_code, delivery_slot, payment_method, subtotal, tax, shipping, total, status, created_at) VALUES
('ORD20241201001', 2, 'Rahim Ahmed', 'rahim@email.com', '+8801712345678', 'House 45, Road 12, Bashundhara R/A', 'Dhaka', '1229', '12:00 AM - 1:00 AM', 'card', 720.00, 72.00, 0.00, 792.00, 'delivered', DATE_SUB(NOW(), INTERVAL 5 DAY)),
('ORD20241202002', 2, 'Rahim Ahmed', 'rahim@email.com', '+8801712345678', 'House 45, Road 12, Bashundhara R/A', 'Dhaka', '1229', '1:00 AM - 2:00 AM', 'card', 450.00, 45.00, 50.00, 545.00, 'delivered', DATE_SUB(NOW(), INTERVAL 3 DAY)),
('ORD20241205003', 2, 'Rahim Ahmed', 'rahim@email.com', '+8801712345678', 'Level 5, Building ABC, Gulshan-2', 'Dhaka', '1212', '2:00 AM - 3:00 AM', 'card', 380.00, 38.00, 50.00, 468.00, 'confirmed', DATE_SUB(NOW(), INTERVAL 1 DAY)),
('ORD20241206004', 3, 'Fatima Khan', 'fatima@email.com', '+8801898765432', 'Flat 3B, Green Tower, Dhanmondi', 'Dhaka', '1205', '12:00 AM - 1:00 AM', 'card', 600.00, 60.00, 0.00, 660.00, 'pending', NOW());

-- Insert Order Items
INSERT INTO order_items (order_id, product_id, product_name, price, quantity, total) VALUES
-- Order 1 items
(1, 1, 'Chicken Biryani (Handmade)', 350.00, 2, 700.00),
(1, 3, 'Samosa & Singara (6 pcs)', 120.00, 1, 120.00),
-- Order 2 items
(2, 4, 'Morog Polao (Handmade)', 380.00, 1, 380.00),
(2, 6, 'Premium Potato Chips', 150.00, 1, 150.00),
-- Order 3 items
(3, 4, 'Morog Polao (Handmade)', 380.00, 1, 380.00),
-- Order 4 items
(4, 1, 'Chicken Biryani (Handmade)', 350.00, 1, 350.00),
(4, 2, 'Khichuri with Chicken', 250.00, 1, 250.00);

-- Insert Sample Product Requests
INSERT INTO product_requests (user_id, product_name, category, description, email, status) VALUES
(2, 'Organic Honey', 'grocery', 'Looking for pure organic honey from Sundarbans', 'rahim@email.com', 'under_review'),
(3, 'Gluten-Free Bread', 'food', 'Need gluten-free bread options for health reasons', 'fatima@email.com', 'approved'),
(2, 'Herbal Tea Collection', 'beverages', 'Would love to see variety of herbal teas', 'rahim@email.com', 'pending');

-- Insert Sample Contact Message
INSERT INTO contact_messages (name, email, subject, message) VALUES
('Guest User', 'guest@email.com', 'Delivery Query', 'What areas do you deliver to in Dhaka?');

-- ============================================
-- END OF DATABASE SCHEMA
-- ============================================

