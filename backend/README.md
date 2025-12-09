# MoonCart PHP Backend

A simple PHP backend for the MoonCart e-commerce application using MySQL and XAMPP.

## 📁 Project Structure

```
backend/
├── config/
│   └── database.php      # Database configuration & helper functions
├── api/
│   ├── users.php         # User authentication & management
│   ├── products.php      # Product CRUD operations
│   ├── categories.php    # Category management
│   ├── orders.php        # Order processing
│   ├── addresses.php     # User address management
│   ├── product_requests.php  # Product request system
│   └── contact.php       # Contact form messages
├── database.sql          # Database schema with sample data
└── README.md             # This file
```

---

## 🚀 XAMPP Setup Instructions

### Step 1: Install XAMPP
1. Download XAMPP from: https://www.apachefriends.org/
2. Install XAMPP (default location: `C:\xampp` on Windows, `/Applications/XAMPP` on Mac)
3. Open XAMPP Control Panel

### Step 2: Start Services
1. Click **Start** next to **Apache**
2. Click **Start** next to **MySQL**
3. Both should turn green

### Step 3: Copy Project Files
**Option A: Move the entire project to htdocs**
```bash
# Copy the entire MoonCart folder to:
# Windows: C:\xampp\htdocs\mooncart
# Mac: /Applications/XAMPP/htdocs/mooncart
```

**Option B: Create symbolic link (Recommended)**
```bash
# Windows (Run CMD as Administrator):
mklink /D "C:\xampp\htdocs\mooncart" "YOUR_PROJECT_PATH\coa"

# Mac/Linux:
ln -s /path/to/your/coa /Applications/XAMPP/htdocs/mooncart
```

### Step 4: Create Database
1. Open browser and go to: **http://localhost/phpmyadmin**
2. Click **"Import"** in the top menu
3. Click **"Choose File"** and select `backend/database.sql`
4. Click **"Go"** at the bottom
5. The database `mooncart_db` will be created with all tables and sample data

### Step 5: Test the API
Open browser and visit:
- http://localhost/mooncart/backend/api/products.php
- http://localhost/mooncart/backend/api/categories.php

You should see JSON responses!

---

## 📊 Database Tables

| Table | Description |
|-------|-------------|
| `users` | Customers & Admin accounts |
| `categories` | Product categories (5 default) |
| `products` | All products (16 sample items) |
| `orders` | Customer orders |
| `order_items` | Products in each order |
| `addresses` | Customer delivery addresses |
| `product_requests` | Customer product suggestions |
| `contact_messages` | Contact form submissions |

---

## 👤 Sample Login Credentials

### Admin Account
- **Email:** admin@mooncart.com
- **Password:** password (hashed in DB)

### Customer Accounts
| Name | Email | Password |
|------|-------|----------|
| Rahim Ahmed | rahim@email.com | password |
| Fatima Khan | fatima@email.com | password |

---

## 🔌 API Endpoints

### Users API (`/api/users.php`)

| Method | Action | Description |
|--------|--------|-------------|
| POST | `?action=login` | User login |
| POST | `?action=register` | New user registration |
| GET | `?action=profile&id=1` | Get user profile |
| GET | `?action=all` | Get all users (admin) |
| PUT | `?action=update` | Update user profile |

**Login Example:**
```javascript
fetch('http://localhost/mooncart/backend/api/users.php?action=login', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({
        email: 'rahim@email.com',
        password: 'password'
    })
})
```

---

### Products API (`/api/products.php`)

| Method | Action | Description |
|--------|--------|-------------|
| GET | (default) | Get all products |
| GET | `?action=single&id=1` | Get single product |
| GET | `?action=category&category=handmade` | Get by category |
| GET | `?action=search&q=biryani` | Search products |
| POST | - | Create product (admin) |
| PUT | - | Update product (admin) |
| DELETE | `?id=1` | Delete product (admin) |

---

### Categories API (`/api/categories.php`)

| Method | Action | Description |
|--------|--------|-------------|
| GET | (default) | Get all categories with product count |
| GET | `?action=single&id=1` | Get category with products |
| POST | - | Create category (admin) |
| PUT | - | Update category (admin) |
| DELETE | `?id=1` | Delete category (admin) |

---

### Orders API (`/api/orders.php`)

| Method | Action | Description |
|--------|--------|-------------|
| GET | (default) | Get all orders (admin) |
| GET | `?action=single&id=1` | Get order details |
| GET | `?action=user&user_id=2` | Get user's orders |
| GET | `?action=stats` | Dashboard statistics |
| POST | - | Create new order |
| PUT | `?action=status` | Update order status |

**Create Order Example:**
```javascript
fetch('http://localhost/mooncart/backend/api/orders.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({
        user_id: 2,
        customer_name: 'Rahim Ahmed',
        email: 'rahim@email.com',
        phone: '+8801712345678',
        address: 'House 45, Road 12, Bashundhara R/A',
        city: 'Dhaka',
        zip_code: '1229',
        delivery_slot: '12:00 AM - 1:00 AM',
        items: [
            { product_id: 1, name: 'Chicken Biryani', price: 350, quantity: 2 },
            { product_id: 3, name: 'Samosa', price: 120, quantity: 1 }
        ]
    })
})
```

---

### Addresses API (`/api/addresses.php`)

| Method | Action | Description |
|--------|--------|-------------|
| GET | `?user_id=2` | Get user addresses |
| POST | - | Add new address |
| PUT | - | Update address |
| PUT | `?action=default` | Set default address |
| DELETE | `?id=1` | Delete address |

---

### Product Requests API (`/api/product_requests.php`)

| Method | Action | Description |
|--------|--------|-------------|
| GET | (default) | Get all requests (admin) |
| GET | `?action=user&user_id=2` | Get user's requests |
| POST | - | Submit new request |
| PUT | - | Update status (admin) |
| DELETE | `?id=1` | Delete request |

---

### Contact API (`/api/contact.php`)

| Method | Action | Description |
|--------|--------|-------------|
| GET | (default) | Get all messages (admin) |
| GET | `?unread=true` | Get unread messages |
| POST | - | Submit contact message |
| PUT | `?action=read` | Mark as read |
| DELETE | `?id=1` | Delete message |

---

## 🛠️ Troubleshooting

### CORS Issues
The API includes CORS headers. If you still have issues, make sure:
1. You're accessing via `http://localhost`
2. Apache is running

### Database Connection Failed
1. Check MySQL is running in XAMPP
2. Verify database name is `mooncart_db`
3. Default user is `root` with no password

### 404 Not Found
1. Check the file path in htdocs
2. Make sure Apache is running
3. Try accessing: `http://localhost/mooncart/backend/api/products.php`

---

## 📝 Notes

- Passwords in sample data are hashed versions of "password"
- Currency is in Bangladeshi Taka (৳)
- Free shipping on orders over ৳5,000
- Tax rate is 10%
- Delivery hours: 12:00 AM - 4:00 AM

---

## 🎉 You're Ready!

Your backend is now set up. Access your site at:
- **Frontend:** http://localhost/mooncart/
- **API Base:** http://localhost/mooncart/backend/api/
- **phpMyAdmin:** http://localhost/phpmyadmin

