# Delivery System Setup Guide

## Important: Run Database Migration First!

Before using the delivery system features, you **must** run the database migration SQL.

## Step 1: Run Database Migration

You have two options:

### Option A: Run Migration SQL (for existing databases)
1. Open phpMyAdmin or MySQL CLI
2. Select your `mooncart_db` database
3. Run the SQL from: `backend/delivery_system_migration.sql`

### Option B: Use Updated Database Schema (for new installations)
1. Use the updated `backend/database.sql` file which includes the delivery system tables

## Step 2: Verify Tables Created

After running the migration, verify these tables/columns exist:

- ✅ `delivery_men` table exists
- ✅ `orders` table has `delivery_man_id` column
- ✅ `orders` table has `estimated_delivery_time` column
- ✅ `orders` table has `cancellation_reason` column

## Step 3: Clear Browser Cache

If you see errors like "getDeliveryMen is not a function":
1. Hard refresh your browser (Ctrl+Shift+R or Cmd+Shift+R)
2. Or clear browser cache
3. Make sure `js/api.js` is loading correctly (check browser console)

## Step 4: Test the System

1. Go to **Admin → Delivery Men** and add a delivery man
2. Go to **Admin → Orders** and try to approve a pending order
3. Check that delivery man selection appears in the approve dialog

## Troubleshooting

### Error: "getDeliveryMen is not a function"
- **Solution**: Hard refresh browser (Ctrl+Shift+R) or clear cache
- Verify `js/api.js` is loaded (check Network tab in browser DevTools)

### Error: "500 Internal Server Error" from orders.php
- **Solution**: Run the database migration SQL first
- The orders API will work without delivery_men table, but you need the columns in orders table

### Error: "Table 'delivery_men' doesn't exist"
- **Solution**: Run `backend/delivery_system_migration.sql` in your database

## Features

✅ Search orders by ID, customer name, email, or phone  
✅ Approve orders with delivery man assignment  
✅ Decline orders with cancellation reason  
✅ View delivery man info on customer dashboard  
✅ View cancellation reason on customer dashboard  
✅ Manage delivery men (add, edit, delete)

