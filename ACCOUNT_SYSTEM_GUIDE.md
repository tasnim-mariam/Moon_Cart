# 🔐 MoonCart Modern Account System

## ✨ Overview

The new account system automatically detects user login state and displays appropriate UI:

-   **Not Logged In**: Shows Login & Sign Up buttons
-   **Logged In**: Shows beautiful profile with dropdown menu

---

## 🎯 Features

### For Guest Users (Not Logged In)

-   Modern **Login** button (outlined style)
-   Eye-catching **Sign Up** button (gradient style)
-   One-click access to authentication
-   Responsive design (icon-only on mobile)

### For Logged-In Users

-   **Profile Avatar** with user's initial
-   **User Name** and role display
-   Beautiful **Dropdown Menu** with:
    -   Dashboard link (role-specific)
    -   My Account
    -   My Orders
    -   Wishlist
    -   Settings
    -   Logout option
-   Smooth animations
-   Click-outside-to-close
-   Escape key support

---

## 🎨 Design Highlights

### Desktop View

**Guest (Not Logged In):**

```
[Login] [Sign Up]
```

-   Login: Outlined with hover fill
-   Sign Up: Gradient background

**Logged In:**

```
┌─────────────────────┐
│ [A] John Doe     ▼ │  ← Profile trigger
│    Customer         │
└─────────────────────┘
        │
        ▼ (on click)
┌─────────────────────┐
│   John Doe          │  ← Gradient header
│   john@email.com    │
├─────────────────────┤
│ 📊 Dashboard        │
│ 👤 My Account       │
│ 📦 My Orders        │
│ ❤️  Wishlist        │
│ ⚙️  Settings        │
├─────────────────────┤
│ 🚪 Logout           │
└─────────────────────┘
```

### Mobile View

**Guest:**

```
[👤] [➕]  ← Icon-only buttons
```

**Logged In:**

```
[A]  ← Avatar only
```

Clicking avatar opens bottom sheet with full menu.

---

## 💻 Implementation

### HTML Structure

The navbar now has a dynamic section:

```html
<div id="account-section" class="account-section">
    <!-- Dynamically populated by JavaScript -->
</div>
```

### JavaScript Logic

1. **Check Login State**: Reads `localStorage.getItem('mooncart_current_user')`
2. **Render UI**: Shows either auth buttons or profile
3. **Event Handlers**: Manages dropdown toggle, outside clicks

### Key Functions

```javascript
// Main initializer
initializeAccountDropdown();

// Render guest view
renderAuthButtons(container);

// Render logged-in view
renderUserProfile(container, user);

// Logout user
logout();
```

---

## 🔄 User Flow

### Sign Up Flow

1. User clicks "Sign Up" button
2. Fills signup form with details
3. Account created → Auto-login
4. User object saved to localStorage
5. Navbar updates to show profile
6. Redirected to dashboard

### Login Flow

1. User clicks "Login" button
2. Selects user type (Customer/Admin)
3. Enters credentials
4. User object saved to localStorage
5. Navbar updates to show profile
6. Redirected to appropriate dashboard

### Logout Flow

1. User clicks profile → Logout
2. Confirmation dialog appears
3. localStorage cleared
4. Success notification
5. Redirected to home page
6. Navbar shows auth buttons again

---

## 🎭 User Roles

The system supports three user roles:

### Customer

-   Default role for signups
-   Access to customer dashboard
-   Can place orders, track deliveries

### Admin

-   System management access
-   View all orders, manage products
-   Assign deliveries

### Delivery

-   Delivery person dashboard
-   View assigned deliveries
-   Mark deliveries complete

---

## 📱 Responsive Behavior

### Desktop (> 768px)

-   Full buttons with text
-   Avatar + name + role
-   Dropdown opens below trigger

### Tablet (768px - 992px)

-   Slightly smaller buttons
-   Full profile visible

### Mobile (< 768px)

-   Icon-only buttons
-   Avatar only (no name/role)
-   Menu slides up from bottom (70vh max)

---

## 🎨 CSS Classes

### Main Container

-   `.account-section` - Flex container for auth/profile

### Guest State

-   `.auth-buttons` - Container for login/signup
-   `.btn-login` - Outlined login button
-   `.btn-signup` - Gradient signup button

### Logged-In State

-   `.user-profile` - Profile wrapper
-   `.profile-trigger` - Clickable profile area
-   `.profile-avatar` - Circular gradient avatar
-   `.profile-info` - Name + role container
-   `.profile-name` - User's display name
-   `.profile-role` - User's role badge
-   `.profile-chevron` - Down arrow icon
-   `.profile-menu` - Dropdown container
-   `.profile-menu-header` - Gradient header
-   `.profile-menu-body` - Menu items container
-   `.profile-menu-item` - Individual menu link
-   `.profile-menu-divider` - Separator line

### State Classes

-   `.active` - Applied to `.user-profile` when menu open

---

## 🔧 Customization

### Change Colors

Edit CSS variables in `css/main.css`:

```css
:root {
    --primary-color: #e31e24; /* Main brand color */
    --secondary-color: #ff6b35; /* Accent color */
}
```

### Add Menu Items

Edit `renderUserProfile()` in `js/main.js`:

```javascript
<a href="your-page.html" class="profile-menu-item">
    <i class="fas fa-your-icon"></i>
    <span>Your Label</span>
</a>
```

### Modify Avatar

The avatar shows the first letter of user's name/email. To change:

```javascript
const userInitial = user.name ? user.name.charAt(0).toUpperCase() : "?";
```

---

## 🐛 Troubleshooting

### "Account section not found" error

-   Ensure `<div id="account-section">` exists in your HTML
-   Check that `js/main.js` is loaded

### Dropdown not opening

-   Open browser console (F12)
-   Look for "Profile menu toggled" message
-   Check if `.active` class is being added

### User stays logged in after logout

-   Check if `localStorage.removeItem('mooncart_current_user')` is called
-   Clear browser cache and localStorage manually

### Profile shows wrong info

-   Check user object structure in localStorage
-   Ensure user has `name`, `email`, and `role` properties

---

## 📦 LocalStorage Structure

### Current User Object

```json
{
  "id": "unique-id-123",
  "name": "John Doe",
  "email": "john@example.com",
  "role": "customer",
  "phone": "01XXXXXXXXX",
  "address": {...},
  "loginTime": "2024-01-01T00:00:00.000Z"
}
```

Stored as: `mooncart_current_user`

---

## ✅ Benefits

1. **Modern UI**: Clean, professional design
2. **Role-Aware**: Different dashboards for different roles
3. **Responsive**: Perfect on all devices
4. **Smooth**: Beautiful animations and transitions
5. **Intuitive**: Clear visual states
6. **Accessible**: Keyboard navigation (Escape key)
7. **Performant**: Lightweight, no external dependencies

---

## 🚀 Future Enhancements

Possible additions:

-   Profile picture upload
-   Notification badges
-   Quick actions (recent orders)
-   Theme switcher in profile menu
-   Multi-account switching
-   Remember me functionality
-   Social login integration

---

## 📝 Notes

-   System uses localStorage for demo purposes
-   In production, integrate with backend API
-   Add proper authentication tokens
-   Implement session management
-   Add security measures (CSRF, XSS protection)

---

**Built with ❤️ for MoonCart**
