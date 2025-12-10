-- Migration: Add delivery and rejection fields to product_requests table
-- Run this SQL in phpMyAdmin or MySQL CLI

USE mooncart_db;

ALTER TABLE product_requests 
ADD COLUMN delivery_time DATETIME NULL AFTER admin_notes,
ADD COLUMN delivery_man_id INT NULL AFTER delivery_time,
ADD COLUMN rejection_reason TEXT NULL AFTER delivery_man_id,
ADD FOREIGN KEY (delivery_man_id) REFERENCES delivery_men(id) ON DELETE SET NULL;
