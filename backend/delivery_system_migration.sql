-- ============================================
-- Delivery System Migration SQL
-- Run this SQL to add delivery system features
-- ============================================

USE mooncart_db;

-- Create delivery_men table
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

-- Add delivery fields to orders table
-- Note: If columns already exist, you'll get an error - that's okay, just skip those lines

-- Add delivery_man_id column
ALTER TABLE orders ADD COLUMN delivery_man_id INT;

-- Add estimated_delivery_time column
ALTER TABLE orders ADD COLUMN estimated_delivery_time DATETIME;

-- Add cancellation_reason column
ALTER TABLE orders ADD COLUMN cancellation_reason TEXT;

-- Add foreign key constraint for delivery_man_id
-- Note: If constraint already exists, you'll get an error - that's okay
ALTER TABLE orders 
ADD CONSTRAINT fk_orders_delivery_man 
FOREIGN KEY (delivery_man_id) REFERENCES delivery_men(id) ON DELETE SET NULL;

-- ============================================
-- Migration Complete
-- ============================================

