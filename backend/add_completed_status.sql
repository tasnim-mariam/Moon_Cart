-- Migration: Add 'completed' status to orders table
-- Run this SQL in phpMyAdmin or MySQL CLI

USE mooncart_db;

-- Modify the orders table to add 'completed' to the status ENUM
ALTER TABLE orders 
MODIFY COLUMN status ENUM('pending', 'confirmed', 'preparing', 'out_for_delivery', 'delivered', 'completed', 'cancelled') DEFAULT 'pending';

