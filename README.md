# MoonCart - Food & Grocery Delivery Website

A beautiful, modern, and fully responsive e-commerce website for food and grocery delivery, inspired by the FreshEat theme.

## 🌟 Features

### Customer Features

-   **Browse Products**: View products with filters by category (Food, Grocery, Medicine, Beverages, Snacks)
-   **Product Search**: Real-time search functionality
-   **Shopping Cart**: Add/remove items, update quantities, view cart totals
-   **Checkout Process**: Complete order form with delivery time slots
-   **Customer Dashboard**: View order history, track orders, manage profile
-   **Wishlist**: Save favorite products
-   **Product Requests**: Request new products to be added to the store

### Admin Features

-   **Order Management**: View, accept, decline, and reschedule orders
-   **Product Management**: Add, edit, and manage product inventory
-   **Customer Management**: View customer details and order history
-   **Delivery Management**: Assign orders to delivery personnel
-   **Dashboard Analytics**: View sales statistics and order metrics

### Delivery Person Features

-   **Active Deliveries**: View assigned delivery orders
-   **Delivery History**: Track completed deliveries
-   **Earnings Dashboard**: View daily, weekly, and monthly earnings
-   **Profile Management**: Update personal and vehicle information

## 📁 Project Structure

```
mooncart/
│
├── assets/                  # Static assets
│   ├── images/             # Product images, logos, etc.
│   └── fonts/              # Custom fonts
│
├── css/                    # Stylesheets
│   ├── main.css            # Main CSS file with animations
│   └── responsive.css      # Mobile-responsive design
│
├── js/                     # JavaScript files
│   ├── main.js            # Core functionality
│   ├── cart.js            # Shopping cart management
│   ├── order.js           # Order processing
│   └── ui.js              # UI enhancements & animations
│
├── index.html             # Landing page
├── products.html          # Products listing page
├── product-detail.html    # Product details page
├── cart.html              # Shopping cart page
├── checkout.html          # Checkout page
├── admin-dashboard.html   # Admin dashboard
├── customer-dashboard.html # Customer dashboard
├── delivery-dashboard.html # Delivery person dashboard
└── README.md              # This file
```

## 🎨 Design Features

-   **Modern UI**: Clean, professional design with smooth animations
-   **Color Scheme**: Red (#e31e24) primary color inspired by FreshEat
-   **Responsive Design**: Works perfectly on desktop, tablet, and mobile
-   **Animations**: Smooth transitions, hover effects, and loading animations
-   **Icons**: Font Awesome 6.4.0 icons throughout
-   **Typography**: Poppins font family for modern look

## 🚀 Getting Started

### Prerequisites

-   A modern web browser (Chrome, Firefox, Safari, Edge)
-   Local web server (optional but recommended)

### Installation

1. **Download/Clone the project**

    ```bash
    cd /path/to/mooncart
    ```

2. **Open with a local server** (Recommended)

    Using Python:

    ```bash
    python -m http.server 8000
    ```

    Then visit: `http://localhost:8000`

    Using Node.js (http-server):

    ```bash
    npx http-server
    ```

    Or simply open `index.html` in your browser (some features may not work without a server)

## 📱 Pages Overview

### Public Pages

#### 1. **Landing Page** (`index.html`)

-   Hero section with call-to-action
-   Featured products
-   Services highlights
-   About section
-   Download app CTA

#### 2. **Products Page** (`products.html`)

-   Product grid with 16+ sample products
-   Category filters (All, Food, Grocery, Medicine, Beverages, Snacks)
-   Search functionality
-   Quick view modal
-   Add to cart functionality

#### 3. **Product Detail Page** (`product-detail.html`)

-   Large product image
-   Detailed description
-   Add to cart with quantity selector
-   Related products
-   Share functionality

#### 4. **Shopping Cart** (`cart.html`)

-   Cart items table
-   Quantity controls
-   Remove items
-   Coupon code support (Try: MOONCART10, WELCOME20, SAVE15)
-   Cart totals with tax and shipping
-   Recommended products

#### 5. **Checkout** (`checkout.html`)

-   Customer information form
-   Delivery address
-   Delivery time slot selection
-   Payment method selection
-   Order summary
-   Secure checkout badges

### Dashboard Pages

#### 6. **Admin Dashboard** (`admin-dashboard.html`)

Access with admin role for:

-   Overview with statistics
-   Order management (accept/decline/assign)
-   Product management
-   Customer list
-   Delivery personnel management
-   Settings

#### 7. **Customer Dashboard** (`customer-dashboard.html`)

Access with customer role for:

-   Order history
-   Track orders
-   Wishlist management
-   Saved addresses
-   Profile settings
-   Product request submission

#### 8. **Delivery Dashboard** (`delivery-dashboard.html`)

Access with delivery role for:

-   Active deliveries list
-   Delivery history
-   Earnings breakdown
-   Profile and vehicle info
-   Online/offline status toggle

## 💾 Data Storage

The website uses **localStorage** for data persistence:

-   `mooncart_cart`: Shopping cart items
-   `mooncart_orders`: Order history
-   `mooncart_user`: Current user session
-   `mooncart_coupon`: Applied coupon codes

## 🎯 Key Functionality

### Shopping Cart

```javascript
// Add product to cart
addToCart(product);

// Remove from cart
removeFromCart(productId);

// Update quantity
updateQuantity(productId, change);

// Calculate totals
calculateCartDetails();
```

### Order Management

```javascript
// Submit new order
submitOrder(event);

// Update order status
updateOrderStatus(orderId, newStatus);

// Accept/decline orders (admin)
acceptOrder(orderId);
declineOrder(orderId, reason);

// Complete delivery
completeDelivery(orderId);
```

### UI Features

```javascript
// Show notifications
MoonCart.showNotification(message, type);

// Loading spinner
MoonCart.showLoading();
MoonCart.hideLoading();

// Modals
MoonCart.openModal(modalId);
MoonCart.closeModal(modalId);
```

## 🎨 Customization

### Colors

Edit `css/main.css` to change the color scheme:

```css
:root {
    --primary-color: #e31e24; /* Main brand color */
    --secondary-color: #ff6b35; /* Secondary color */
    --dark-color: #1a1a1a; /* Dark elements */
    --success-color: #28a745; /* Success messages */
    --warning-color: #ffc107; /* Warnings */
    --danger-color: #dc3545; /* Errors */
}
```

### Typography

Change the font in `css/main.css`:

```css
:root {
    --font-main: "Poppins", sans-serif;
}
```

## 📸 Sample Data

The website includes sample products with Unsplash images:

-   Pizza, Burgers, Pasta, Chicken, Noodles
-   Fresh Vegetables, Fruits, Rice, Milk
-   Vitamins, Pain Relief Medicine
-   Orange Juice, Coffee
-   Chips, Cookies, Mixed Nuts

## 🔒 Mock Authentication

The dashboards use mock authentication stored in localStorage:

-   Admin: `{ id: 'admin1', role: 'admin' }`
-   Customer: `{ id: 'cust1', role: 'customer' }`
-   Delivery: `{ id: 'delivery1', role: 'delivery' }`

## 🌐 Browser Support

-   Chrome (latest)
-   Firefox (latest)
-   Safari (latest)
-   Edge (latest)
-   Mobile browsers (iOS Safari, Chrome Mobile)

## 📱 Responsive Breakpoints

-   **Desktop**: 1024px and above
-   **Tablet**: 768px - 1024px
-   **Mobile**: Below 768px

## ⚡ Performance Features

-   Lazy loading images
-   Smooth scrolling
-   Optimized animations
-   Efficient DOM manipulation
-   Local storage for fast data access

## 🎬 Animations

-   Fade in on scroll
-   Floating elements
-   Pulse effects
-   Slide in transitions
-   Hover effects
-   Loading spinners

## 🛠️ Technologies Used

-   **HTML5**: Semantic markup
-   **CSS3**: Modern styling with CSS Grid and Flexbox
-   **JavaScript (ES6+)**: Vanilla JS for all functionality
-   **Font Awesome 6.4.0**: Icons
-   **Google Fonts**: Poppins font family
-   **LocalStorage API**: Data persistence
-   **Unsplash**: Sample product images

## 📝 Future Enhancements

-   Backend API integration
-   Real payment gateway
-   User authentication system
-   Real-time order tracking
-   Push notifications
-   Product reviews and ratings
-   Advanced search filters
-   Order rating system
-   Chat support
-   Multi-language support

## 🤝 Contributing

Feel free to fork this project and customize it for your needs!

## 📄 License

This project is open source and available for personal and commercial use.

## 👨‍💻 Developer

Built with ❤️ for the MoonCart project

---

## 🎓 Learning Resources

This project demonstrates:

-   Modern HTML/CSS/JavaScript
-   Responsive web design
-   localStorage for data persistence
-   DOM manipulation
-   Event handling
-   Form validation
-   UI/UX best practices
-   Component-based architecture

## 🐛 Known Issues

-   Orders are stored locally (not persistent across browsers)
-   No real payment processing
-   No email notifications
-   Images are loaded from external CDN (Unsplash)

## 🚀 Quick Start Guide

1. Open `index.html` in your browser
2. Browse products on the Products page
3. Add items to cart
4. Proceed to checkout
5. Access dashboards:
    - `admin-dashboard.html` for admin view
    - `customer-dashboard.html` for customer view
    - `delivery-dashboard.html` for delivery partner view

Enjoy using MoonCart! 🌙🛒
# MoonCart
# MoonCart
# Moon
# Moon
# Moon_Cart
