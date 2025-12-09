# ⚠️ Database Migration Required

## Quick Fix

You need to run a SQL command to add the 'completed' status to your database.

### Option 1: Using phpMyAdmin (Recommended)

1. Open phpMyAdmin: `http://localhost/phpmyadmin`
2. Select the database: `mooncart_db`
3. Click on the "SQL" tab
4. Paste this SQL command:

```sql
ALTER TABLE orders 
MODIFY COLUMN status ENUM('pending', 'confirmed', 'preparing', 'out_for_delivery', 'delivered', 'completed', 'cancelled') DEFAULT 'pending';
```

5. Click "Go" to execute

### Option 2: Using MySQL Command Line

```bash
mysql -u root -p mooncart_db < backend/add_completed_status.sql
```

Or manually:

```sql
USE mooncart_db;
ALTER TABLE orders 
MODIFY COLUMN status ENUM('pending', 'confirmed', 'preparing', 'out_for_delivery', 'delivered', 'completed', 'cancelled') DEFAULT 'pending';
```

### Option 3: Run the SQL File

1. Open phpMyAdmin
2. Select `mooncart_db` database
3. Go to "SQL" tab
4. Click "Import" or copy-paste the contents of `backend/add_completed_status.sql`

## Verify It Worked

After running the migration, try clicking the "Received" button again. It should work without errors!

## What This Does

This migration adds 'completed' as a valid status option in the orders table, allowing customers to mark their confirmed orders as received.

