// ==========================================
// MoonCart - Cart Management (Backend API)
// Handle all cart-related functionality
// ==========================================

// Global cart state
let cartData = {
    items: [],
    subtotal: 0,
    tax: 0,
    shipping: 0,
    total: 0,
    itemCount: 0,
};

// Check if user is logged in
function requireLogin(action = "add items to cart") {
    const currentUser = MoonCart.getCurrentUser();
    if (!currentUser) {
        MoonCart.showNotification(`Please login to ${action}`, "error");
        setTimeout(() => {
            window.location.href = "login.html";
        }, 1500);
        return null;
    }
    return currentUser;
}

// Add product to cart (requires login)
async function addToCart(product) {
    const currentUser = requireLogin("add items to cart");
    if (!currentUser) return;

    // Get product ID - ensure it's a number
    let productId = product.id;
    if (typeof productId === "string") {
        // Try to extract numeric ID
        productId = parseInt(productId) || productId;
    }

    // If product ID is not numeric, we need to find it in the database
    if (isNaN(productId)) {
        MoonCart.showNotification(
            "Invalid product. Please try again.",
            "error"
        );
        return;
    }

    MoonCart.showLoading();

    try {
        const response = await MoonCartAPI.addToCart(
            currentUser.id,
            productId,
            1,
            product.category || "Product"
        );

        MoonCart.hideLoading();

        if (response.success) {
            cartData = response.cart;
            updateCartCount();
            MoonCart.showNotification(
                response.message || "Added to cart!",
                "success"
            );

            // Add animation to cart icon
            const cartIcon = document.querySelector(".cart-icon");
            if (cartIcon) {
                cartIcon.style.animation = "pulse 0.3s ease";
                setTimeout(() => {
                    cartIcon.style.animation = "";
                }, 300);
            }
        } else {
            MoonCart.showNotification(
                response.message || "Failed to add to cart",
                "error"
            );
        }
    } catch (error) {
        MoonCart.hideLoading();
        console.error("Add to cart error:", error);
        MoonCart.showNotification(
            "Failed to add to cart. Please try again.",
            "error"
        );
    }
}

// Remove product from cart
async function removeFromCart(productId) {
    const currentUser = requireLogin("manage cart");
    if (!currentUser) return;

    try {
        const response = await MoonCartAPI.removeFromCart(
            currentUser.id,
            productId
        );

        if (response.success) {
            cartData = response.cart;
            MoonCart.showNotification("Item removed from cart", "success");
            renderCart();
            updateCartCount();
        } else {
            MoonCart.showNotification(
                response.message || "Failed to remove item",
                "error"
            );
        }
    } catch (error) {
        console.error("Remove from cart error:", error);
        MoonCart.showNotification(
            "Failed to remove item. Please try again.",
            "error"
        );
    }
}

// Update product quantity
async function updateQuantity(productId, change) {
    const currentUser = requireLogin("manage cart");
    if (!currentUser) return;

    try {
        const response = await MoonCartAPI.updateCartItem(
            currentUser.id,
            productId,
            change
        );

        if (response.success) {
            cartData = response.cart;
            renderCart();
            updateCartCount();
        } else {
            MoonCart.showNotification(
                response.message || "Failed to update quantity",
                "error"
            );
        }
    } catch (error) {
        console.error("Update quantity error:", error);
        MoonCart.showNotification(
            "Failed to update quantity. Please try again.",
            "error"
        );
    }
}

// Load cart from backend
async function loadCart() {
    const currentUser = MoonCart.getCurrentUser();
    if (!currentUser) {
        cartData = {
            items: [],
            subtotal: 0,
            tax: 0,
            shipping: 0,
            total: 0,
            itemCount: 0,
        };
        updateCartCount();
        return cartData;
    }

    try {
        const response = await MoonCartAPI.getCart(currentUser.id);
        if (response.success) {
            cartData = response.cart;
            updateCartCount();
            return cartData;
        }
    } catch (error) {
        console.error("Load cart error:", error);
    }

    return cartData;
}

// Update cart count in header
function updateCartCount() {
    const cartCountElements = document.querySelectorAll(".cart-count");
    cartCountElements.forEach((el) => {
        el.textContent = cartData.itemCount || 0;
    });
}

// Calculate cart details (from loaded data)
function calculateCartDetails() {
    return {
        subtotal: cartData.subtotal || 0,
        tax: cartData.tax || 0,
        shipping: cartData.shipping || 0,
        total: cartData.total || 0,
        itemCount: cartData.itemCount || 0,
    };
}

// Get cart items
function getCartItems() {
    return cartData.items || [];
}

// Render cart page
async function renderCart() {
    const cartTableBody = document.querySelector(".cart-table tbody");
    const cartSummary = document.querySelector(".cart-summary");

    if (!cartTableBody) return;

    const currentUser = MoonCart.getCurrentUser();

    // If not logged in, show login prompt
    if (!currentUser) {
        cartTableBody.innerHTML = `
            <tr>
                <td colspan="6" style="text-align: center; padding: 40px;">
                    <h3>Please login to view your cart</h3>
                    <p style="margin: 20px 0;">Login to add items and view your shopping cart.</p>
                    <a href="login.html" class="btn btn-primary">Login Now</a>
                </td>
            </tr>
        `;
        if (cartSummary) cartSummary.style.display = "none";
        return;
    }

    // Load cart from backend
    MoonCart.showLoading();
    await loadCart();
    MoonCart.hideLoading();

    // Clear existing content
    cartTableBody.innerHTML = "";

    if (!cartData.items || cartData.items.length === 0) {
        cartTableBody.innerHTML = `
            <tr>
                <td colspan="6" style="text-align: center; padding: 40px;">
                    <h3>Your cart is empty</h3>
                    <p style="margin: 20px 0;">Add some delicious items to get started!</p>
                    <a href="products.html" class="btn btn-primary">Browse Products</a>
                </td>
            </tr>
        `;
        if (cartSummary) cartSummary.style.display = "none";
        return;
    }

    // Render cart items
    cartData.items.forEach((item) => {
        const row = document.createElement("tr");
        const itemTotal =
            (parseFloat(item.price) || 0) * (parseInt(item.quantity) || 0);
        row.innerHTML = `
            <td data-label="Image">
                <img src="${item.image}" alt="${
            item.product_name
        }" class="cart-item-image" 
                     onerror="this.src='data:image/svg+xml,%3Csvg xmlns=%27http://www.w3.org/2000/svg%27 width=%27100%27 height=%27100%27%3E%3Crect fill=%27%23f0f0f0%27 width=%27100%27 height=%27100%27/%3E%3Ctext x=%2750%27 y=%2750%27 text-anchor=%27middle%27 fill=%27%23999%27 font-family=%27Arial%27 font-size=%2712%27%3ENo Image%3C/text%3E%3C/svg%3E'">
            </td>
            <td data-label="Product">${item.product_name}</td>
            <td data-label="Price">${MoonCart.formatCurrency(
                parseFloat(item.price) || 0
            )}</td>
            <td data-label="Quantity">
                <div class="quantity-control">
                    <button class="quantity-btn" onclick="updateQuantity(${
                        item.product_id
                    }, -1)">-</button>
                    <span>${item.quantity}</span>
                    <button class="quantity-btn" onclick="updateQuantity(${
                        item.product_id
                    }, 1)">+</button>
                </div>
            </td>
            <td data-label="Total">${MoonCart.formatCurrency(itemTotal)}</td>
            <td data-label="Remove">
                <button class="remove-btn" onclick="removeFromCart(${
                    item.product_id
                })">Remove</button>
            </td>
        `;
        cartTableBody.appendChild(row);
    });

    // Render cart summary
    if (cartSummary) {
        cartSummary.style.display = "block";

        cartSummary.innerHTML = `
            <h3 style="margin-bottom: 20px;">Cart Summary</h3>
            <div class="summary-row">
                <span>Subtotal (${cartData.itemCount} items):</span>
                <span>${MoonCart.formatCurrency(cartData.subtotal)}</span>
            </div>
            <div class="summary-row">
                <span>Tax (10%):</span>
                <span>${MoonCart.formatCurrency(cartData.tax)}</span>
            </div>
            <div class="summary-row">
                <span>Shipping:</span>
                <span>${
                    cartData.shipping === 0
                        ? "FREE"
                        : MoonCart.formatCurrency(cartData.shipping)
                }</span>
            </div>
            <div class="summary-row total">
                <span>Total:</span>
                <span>${MoonCart.formatCurrency(cartData.total)}</span>
            </div>
            <button type="button" class="btn btn-primary" onclick="handleCheckoutClick(event)" style="width: 100%; margin-top: 20px;">
                Proceed to Checkout
            </button>
            <button type="button" class="btn btn-outline" style="width: 100%; margin-top: 10px;" onclick="window.location.href='products.html'">
                Continue Shopping
            </button>
        `;
    }
}

// Proceed to checkout - make it globally accessible (kept for backward compatibility)
function proceedToCheckout(event) {
    if (event) {
        event.preventDefault();
        event.stopPropagation();
        event.stopImmediatePropagation();
    }

    const currentUser = MoonCart.getCurrentUser();
    if (!currentUser) {
        MoonCart.showNotification("Please login to checkout", "error");
        setTimeout(() => {
            window.location.href = "login.html";
        }, 1500);
        return false;
    }

    if (!cartData.items || cartData.items.length === 0) {
        MoonCart.showNotification("Your cart is empty!", "error");
        return false;
    }

    window.location.href = "checkout.html";
    return false;
}

// Make function globally accessible
window.proceedToCheckout = proceedToCheckout;

// Handle checkout click - simple direct function
function handleCheckoutClick(event) {
    console.log("=== Checkout button clicked ===");

    try {
        if (event) {
            event.preventDefault();
            event.stopPropagation();
        }

        const currentUser = MoonCart.getCurrentUser();
        console.log("Current user:", currentUser);

        if (!currentUser) {
            console.log("No user found, redirecting to login");
            MoonCart.showNotification("Please login to checkout", "error");
            setTimeout(() => {
                window.location.href = "login.html";
            }, 1500);
            return false;
        }

        if (!cartData.items || cartData.items.length === 0) {
            console.log("Cart is empty");
            MoonCart.showNotification("Your cart is empty!", "error");
            return false;
        }

        console.log("All checks passed, redirecting to checkout.html...");
        window.location.href = "checkout.html";
        return false;
    } catch (error) {
        console.error("Error in handleCheckoutClick:", error);
        alert("Error: " + error.message);
        return false;
    }
}

// Make it globally accessible
window.handleCheckoutClick = handleCheckoutClick;

// Clear cart
async function clearCart() {
    const currentUser = requireLogin("clear cart");
    if (!currentUser) return;

    if (!confirm("Are you sure you want to clear your cart?")) return;

    try {
        const response = await MoonCartAPI.clearCart(currentUser.id);

        if (response.success) {
            cartData = response.cart;
            MoonCart.showNotification("Cart cleared!", "success");
            renderCart();
            updateCartCount();
        } else {
            MoonCart.showNotification(
                response.message || "Failed to clear cart",
                "error"
            );
        }
    } catch (error) {
        console.error("Clear cart error:", error);
        MoonCart.showNotification(
            "Failed to clear cart. Please try again.",
            "error"
        );
    }
}

// Add to cart from product detail page
async function addToCartFromDetail() {
    const currentUser = requireLogin("add items to cart");
    if (!currentUser) return;

    const productIdEl = document.getElementById("product-id");
    const productNameEl = document.getElementById("product-name");
    const productPriceEl = document.getElementById("product-price");
    const productCategoryEl = document.getElementById("product-category");

    if (!productIdEl) {
        MoonCart.showNotification("Product information not found", "error");
        return;
    }

    const productId = parseInt(productIdEl.value);
    if (isNaN(productId)) {
        MoonCart.showNotification("Invalid product", "error");
        return;
    }

    MoonCart.showLoading();

    try {
        const response = await MoonCartAPI.addToCart(
            currentUser.id,
            productId,
            1,
            productCategoryEl ? productCategoryEl.textContent : "Product"
        );

        MoonCart.hideLoading();

        if (response.success) {
            cartData = response.cart;
            updateCartCount();
            MoonCart.showNotification("Added to cart!", "success");
        } else {
            MoonCart.showNotification(
                response.message || "Failed to add to cart",
                "error"
            );
        }
    } catch (error) {
        MoonCart.hideLoading();
        console.error("Add to cart error:", error);
        MoonCart.showNotification(
            "Failed to add to cart. Please try again.",
            "error"
        );
    }
}

// Add to cart from API products (products page)
async function addToCartFromAPI(productId, name, price, image, category) {
    const currentUser = requireLogin("add items to cart");
    if (!currentUser) return;

    MoonCart.showLoading();

    try {
        const response = await MoonCartAPI.addToCart(
            currentUser.id,
            parseInt(productId),
            1,
            category
        );

        MoonCart.hideLoading();

        if (response.success) {
            cartData = response.cart;
            updateCartCount();
            MoonCart.showNotification(`${name} added to cart!`, "success");

            // Add animation to cart icon
            const cartIcon = document.querySelector(".cart-icon");
            if (cartIcon) {
                cartIcon.style.animation = "pulse 0.3s ease";
                setTimeout(() => {
                    cartIcon.style.animation = "";
                }, 300);
            }
        } else {
            MoonCart.showNotification(
                response.message || "Failed to add to cart",
                "error"
            );
        }
    } catch (error) {
        MoonCart.hideLoading();
        console.error("Add to cart error:", error);
        MoonCart.showNotification(
            "Failed to add to cart. Please try again.",
            "error"
        );
    }
}

// Initialize cart on page load
document.addEventListener("DOMContentLoaded", async function () {
    // Load cart data
    await loadCart();

    // Initialize cart page if on cart.html
    if (window.location.pathname.includes("cart.html")) {
        renderCart();
    }

    // Setup "Add to Cart" buttons on static product cards
    const addToCartButtons = document.querySelectorAll(
        ".add-to-cart:not([onclick])"
    );
    addToCartButtons.forEach((button) => {
        button.addEventListener("click", function (e) {
            e.preventDefault();
            e.stopPropagation();

            const currentUser = MoonCart.getCurrentUser();
            if (!currentUser) {
                MoonCart.showNotification(
                    "Please login to add items to cart",
                    "error"
                );
                setTimeout(() => {
                    window.location.href = "login.html";
                }, 1500);
                return;
            }

            const productCard = this.closest(".product-card");
            if (!productCard) return;

            // Get product ID from data attribute
            const productId = productCard.dataset.id;
            if (!productId || isNaN(parseInt(productId))) {
                MoonCart.showNotification(
                    "Invalid product. Please refresh and try again.",
                    "error"
                );
                return;
            }

            // Extract price
            const priceText =
                productCard
                    .querySelector(".product-price")
                    ?.textContent.trim() || "0";
            const priceValue =
                parseFloat(priceText.replace(/[৳$,\s]/g, "")) || 0;

            const product = {
                id: parseInt(productId),
                name:
                    productCard
                        .querySelector(".product-name")
                        ?.textContent.trim() || "Product",
                price: priceValue,
                image:
                    productCard.querySelector(".product-image img")?.src || "",
                category:
                    productCard
                        .querySelector(".product-category")
                        ?.textContent.trim() || "Product",
            };

            addToCart(product);
        });
    });

    // Initialize product detail page add to cart
    if (window.location.pathname.includes("product-detail.html")) {
        const addToCartBtn = document.getElementById("add-to-cart-btn");
        if (addToCartBtn) {
            addToCartBtn.addEventListener("click", addToCartFromDetail);
        }
    }
});
