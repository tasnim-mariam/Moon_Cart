// ==========================================
// MoonCart - Main JavaScript
// General functionality and utilities
// ==========================================

// Initialize app when DOM is loaded
document.addEventListener("DOMContentLoaded", function () {
    initializeApp();
});

function initializeApp() {
    initializeNavigation();
    initializeScrollEffects();
    initializeAnimations();
    updateCartCount();
    initializeSearchFunctionality();
    renderAccountSection();
}

function initializeNavigation() {
    const menuToggle = document.querySelector(".menu-toggle");
    const navMenu = document.querySelector(".nav-menu");
    const navLinks = document.querySelectorAll(".nav-link");

    // Mobile menu toggle
    if (menuToggle) {
        menuToggle.addEventListener("click", function () {
            this.classList.toggle("active");
            navMenu.classList.toggle("active");
            document.body.style.overflow = navMenu.classList.contains("active")
                ? "hidden"
                : "";
        });
    }

    // Close mobile menu when clicking on a link
    navLinks.forEach((link) => {
        link.addEventListener("click", function () {
            if (window.innerWidth <= 768) {
                menuToggle?.classList.remove("active");
                navMenu?.classList.remove("active");
                document.body.style.overflow = "";
            }
        });
    });

    // Set active link based on current page
    const currentPage =
        window.location.pathname.split("/").pop() || "index.html";
    navLinks.forEach((link) => {
        if (link.getAttribute("href") === currentPage) {
            link.classList.add("active");
        }
    });
}

function initializeScrollEffects() {
    const header = document.querySelector(".header");
    let lastScroll = 0;

    window.addEventListener("scroll", function () {
        const currentScroll = window.pageYOffset;

        // Add shadow to header on scroll
        if (currentScroll > 50) {
            header?.classList.add("scrolled");
        } else {
            header?.classList.remove("scrolled");
        }

        // Hide/show header on scroll (optional)
        if (currentScroll > lastScroll && currentScroll > 500) {
            if (header) header.style.transform = "translateY(-100%)";
        } else {
            if (header) header.style.transform = "translateY(0)";
        }

        lastScroll = currentScroll;
    });

    // Smooth scroll for anchor links
    document.querySelectorAll('a[href^="#"]').forEach((anchor) => {
        anchor.addEventListener("click", function (e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute("href"));
            if (target) {
                target.scrollIntoView({
                    behavior: "smooth",
                    block: "start",
                });
            }
        });
    });
}

function initializeAnimations() {
    // Intersection Observer for fade-in animations
    const observerOptions = {
        threshold: 0.1,
        rootMargin: "0px 0px -100px 0px",
    };

    const observer = new IntersectionObserver(function (entries) {
        entries.forEach((entry) => {
            if (entry.isIntersecting) {
                entry.target.classList.add("animate-in");
                observer.unobserve(entry.target);
            }
        });
    }, observerOptions);

    // Observe all elements with data-animate attribute
    document.querySelectorAll("[data-animate]").forEach((el) => {
        observer.observe(el);
    });

    // Add animation classes to elements
    document
        .querySelectorAll(".product-card, .stat-card")
        .forEach((el, index) => {
            el.style.animationDelay = `${index * 0.1}s`;
        });
}

function initializeSearchFunctionality() {
    const searchInput = document.querySelector(".search-input");

    if (searchInput) {
        searchInput.addEventListener("input", function (e) {
            const searchTerm = e.target.value.toLowerCase();
            filterProducts(searchTerm);
        });
    }
}

function filterProducts(searchTerm) {
    const productCards = document.querySelectorAll(".product-card");

    productCards.forEach((card) => {
        const productName = card
            .querySelector(".product-name")
            ?.textContent.toLowerCase();
        const productCategory = card
            .querySelector(".product-category")
            ?.textContent.toLowerCase();

        if (
            productName?.includes(searchTerm) ||
            productCategory?.includes(searchTerm)
        ) {
            card.style.display = "block";
            card.style.animation = "fadeInUp 0.5s ease";
        } else {
            card.style.display = "none";
        }
    });
}

function initializeFilterTabs() {
    const filterTabs = document.querySelectorAll(".filter-tab");

    filterTabs.forEach((tab) => {
        tab.addEventListener("click", function () {
            // Remove active class from all tabs
            filterTabs.forEach((t) => t.classList.remove("active"));
            // Add active class to clicked tab
            this.classList.add("active");

            const category = this.dataset.category;
            filterByCategory(category);
        });
    });
}

function filterByCategory(category) {
    const productCards = document.querySelectorAll(".product-card");

    productCards.forEach((card) => {
        const productCategory = card.dataset.category;

        if (category === "all" || productCategory === category) {
            card.style.display = "block";
            card.style.animation = "fadeInUp 0.5s ease";
        } else {
            card.style.display = "none";
        }
    });
}

function updateCartCount() {
    const cart = getCart();
    const cartCount = document.querySelector(".cart-count");

    if (cartCount) {
        const totalItems = cart.reduce((sum, item) => sum + item.quantity, 0);
        cartCount.textContent = totalItems;
        cartCount.style.display = totalItems > 0 ? "flex" : "none";
    }
}

function getCart() {
    return JSON.parse(localStorage.getItem("mooncart_cart")) || [];
}

function saveCart(cart) {
    localStorage.setItem("mooncart_cart", JSON.stringify(cart));
    updateCartCount();
}

function getOrders() {
    return JSON.parse(localStorage.getItem("mooncart_orders")) || [];
}

function saveOrders(orders) {
    localStorage.setItem("mooncart_orders", JSON.stringify(orders));
}

function getCurrentUser() {
    return JSON.parse(localStorage.getItem("mooncart_user")) || null;
}

function saveCurrentUser(user) {
    localStorage.setItem("mooncart_user", JSON.stringify(user));
}

function showNotification(message, type = "success") {
    // Remove existing notification
    const existingNotification = document.querySelector(".notification");
    if (existingNotification) {
        existingNotification.remove();
    }

    // Create notification element
    const notification = document.createElement("div");
    notification.className = `notification notification-${type}`;
    notification.innerHTML = `
        <div class="notification-content">
            <span class="notification-icon">${
                type === "success" ? "✓" : "✕"
            }</span>
            <span class="notification-message">${message}</span>
        </div>
    `;

    // Add styles
    notification.style.cssText = `
        position: fixed;
        top: 100px;
        right: 20px;
        background: ${type === "success" ? "#28a745" : "#dc3545"};
        color: white;
        padding: 15px 25px;
        border-radius: 10px;
        box-shadow: 0 5px 20px rgba(0,0,0,0.2);
        z-index: 10000;
        animation: slideInRight 0.3s ease;
    `;

    document.body.appendChild(notification);

    // Auto remove after 3 seconds
    setTimeout(() => {
        notification.style.animation = "slideOutRight 0.3s ease";
        setTimeout(() => notification.remove(), 300);
    }, 3000);
}

function validateForm(formId) {
    const form = document.getElementById(formId);
    if (!form) return false;

    const inputs = form.querySelectorAll(
        "input[required], select[required], textarea[required]"
    );
    let isValid = true;

    inputs.forEach((input) => {
        if (!input.value.trim()) {
            isValid = false;
            input.style.borderColor = "var(--danger-color)";

            // Remove error styling on input
            input.addEventListener(
                "input",
                function () {
                    this.style.borderColor = "var(--border-color)";
                },
                { once: true }
            );
        }
    });

    if (!isValid) {
        showNotification("Please fill in all required fields", "error");
    }

    return isValid;
}

function formatCurrency(amount) {
    return `৳${parseFloat(amount).toFixed(2)}`;
}

function formatDate(date) {
    const options = { year: "numeric", month: "short", day: "numeric" };
    return new Date(date).toLocaleDateString("en-US", options);
}

function generateId() {
    return Date.now().toString(36) + Math.random().toString(36).substr(2);
}

function showLoading() {
    const loader = document.createElement("div");
    loader.id = "loading-spinner";
    loader.innerHTML = `
        <div style="
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.7);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 99999;
        ">
            <div style="
                width: 50px;
                height: 50px;
                border: 5px solid rgba(255,255,255,0.3);
                border-top-color: #e31e24;
                border-radius: 50%;
                animation: spin 1s linear infinite;
            "></div>
        </div>
    `;
    document.body.appendChild(loader);
}

function hideLoading() {
    const loader = document.getElementById("loading-spinner");
    if (loader) {
        loader.remove();
    }
}

function openModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.style.display = "flex";
        document.body.style.overflow = "hidden";
    }
}

function closeModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.style.display = "none";
        document.body.style.overflow = "";
    }
}

// Close modal when clicking outside
window.addEventListener("click", function (e) {
    if (e.target.classList.contains("modal")) {
        closeModal(e.target.id);
    }
});

function initializeBackToTop() {
    const backToTop = document.createElement("button");
    backToTop.innerHTML = "↑";
    backToTop.className = "back-to-top";
    backToTop.style.cssText = `
        position: fixed;
        bottom: 30px;
        right: 30px;
        width: 50px;
        height: 50px;
        background: var(--primary-color);
        color: white;
        border: none;
        border-radius: 50%;
        font-size: 24px;
        cursor: pointer;
        display: none;
        z-index: 999;
        transition: all 0.3s ease;
    `;

    document.body.appendChild(backToTop);

    window.addEventListener("scroll", function () {
        if (window.pageYOffset > 300) {
            backToTop.style.display = "block";
        } else {
            backToTop.style.display = "none";
        }
    });

    backToTop.addEventListener("click", function () {
        window.scrollTo({
            top: 0,
            behavior: "smooth",
        });
    });
}

// Initialize back to top button
initializeBackToTop();

function renderAccountSection() {
    const accountSection = document.getElementById("account-section");
    if (!accountSection) {
        return;
    }

    const currentUser = getCurrentUser();

    if (currentUser) {
        // User is logged in - show profile
        renderUserProfile(accountSection, currentUser);
    } else {
        // User not logged in - show login/signup buttons
        renderAuthButtons(accountSection);
    }
}

function renderAuthButtons(container) {
    container.innerHTML = `
        <div class="auth-buttons">
            <a href="login.html" class="btn-login">
                <i class="fas fa-sign-in-alt"></i>
                <span>Login</span>
            </a>
            <a href="signup.html" class="btn-signup">
                <i class="fas fa-user-plus"></i>
                <span>Sign Up</span>
            </a>
        </div>
    `;
}

function renderUserProfile(container, user) {
    const fullName = user.name || user.email.split("@")[0];
    const firstName = fullName.split(" ")[0];
    const avatarInitial = firstName.charAt(0).toUpperCase();
    const dashboardUrl =
        user.role === "admin"
            ? "admin-dashboard.html"
            : "customer-dashboard.html";

    container.innerHTML = `
        <div class="user-profile">
            <button type="button" class="profile-trigger" onclick="toggleProfileMenu(event)">
                <div class="profile-avatar">${avatarInitial}</div>
                <div class="profile-info">
                    <div class="profile-name">${firstName}</div>
                </div>
                <i class="fas fa-chevron-down profile-chevron"></i>
            </button>
            <div class="profile-menu">
                <div class="profile-menu-header">
                    <div class="profile-menu-avatar">${avatarInitial}</div>
                    <div class="profile-menu-info">
                        <h4>${fullName}</h4>
                        <p>${user.email}</p>
                    </div>
                </div>
                <div class="profile-menu-body">
                    <a href="${dashboardUrl}" class="profile-menu-item">
                        <i class="fas fa-tachometer-alt"></i>
                        <span>Dashboard</span>
                    </a>
                    <a href="#" class="profile-menu-item" onclick="handleLogout(event)">
                        <i class="fas fa-sign-out-alt"></i>
                        <span>Logout</span>
                    </a>
                </div>
            </div>
        </div>
    `;
}

function toggleProfileMenu(event) {
    event.stopPropagation();
    const userProfile = event.currentTarget.closest(".user-profile");
    if (userProfile) {
        userProfile.classList.toggle("active");
    }
}

function handleLogout(event) {
    if (event) {
        event.preventDefault();
    }
    localStorage.removeItem("mooncart_user");
    showNotification("Logged out successfully", "success");
    setTimeout(() => {
        window.location.href = "index.html";
    }, 1000);
}

// Close profile menu when clicking outside
document.addEventListener("click", function (event) {
    const userProfile = document.querySelector(".user-profile");
    if (userProfile && !userProfile.contains(event.target)) {
        userProfile.classList.remove("active");
    }
});

// Create MoonCart namespace for compatibility with other pages
window.MoonCart = {
    // Utility functions
    showNotification: showNotification,
    showLoading: showLoading,
    hideLoading: hideLoading,
    formatCurrency: formatCurrency,
    formatDate: formatDate,
    generateId: generateId,
    validateForm: validateForm,

    // Cart functions
    getCart: getCart,
    saveCart: saveCart,
    updateCartCount: updateCartCount,

    // Order functions
    getOrders: getOrders,
    saveOrders: saveOrders,

    // User functions
    getCurrentUser: getCurrentUser,
    saveCurrentUser: saveCurrentUser,
    setCurrentUser: saveCurrentUser, // Alias for compatibility

    // Modal functions
    openModal: openModal,
    closeModal: closeModal,

    // Account functions
    renderAccountSection: renderAccountSection,
    handleLogout: handleLogout,
    toggleProfileMenu: toggleProfileMenu,
};
