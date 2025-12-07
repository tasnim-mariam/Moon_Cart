// ==========================================
// MoonCart - Cart Management
// Handle all cart-related functionality
// ==========================================

// Add product to cart
function addToCart(product) {
    const cart = MoonCart.getCart();

    // Check if product already exists in cart
    const existingProductIndex = cart.findIndex(
        (item) => item.id === product.id
    );

    if (existingProductIndex > -1) {
        // Increase quantity
        cart[existingProductIndex].quantity += 1;
        MoonCart.showNotification(
            "Product quantity updated in cart!",
            "success"
        );
    } else {
        // Add new product
        cart.push({
            id: product.id,
            name: product.name,
            price: product.price,
            image: product.image,
            category: product.category,
            quantity: 1,
        });
        MoonCart.showNotification("Product added to cart!", "success");
    }

    MoonCart.saveCart(cart);

    // Add animation to cart icon
    const cartIcon = document.querySelector(".cart-icon");
    if (cartIcon) {
        cartIcon.style.animation = "pulse 0.3s ease";
        setTimeout(() => {
            cartIcon.style.animation = "";
        }, 300);
    }
}

// Remove product from cart
function removeFromCart(productId) {
    let cart = MoonCart.getCart();
    cart = cart.filter((item) => item.id !== productId);
    MoonCart.saveCart(cart);
    MoonCart.showNotification("Product removed from cart", "success");
    renderCart();
}

// Update product quantity
function updateQuantity(productId, change) {
    const cart = MoonCart.getCart();
    const productIndex = cart.findIndex((item) => item.id === productId);

    if (productIndex > -1) {
        cart[productIndex].quantity += change;

        // Remove if quantity is 0
        if (cart[productIndex].quantity <= 0) {
            removeFromCart(productId);
            return;
        }

        MoonCart.saveCart(cart);
        renderCart();
    }
}

// Calculate cart total
function calculateCartTotal() {
    const cart = MoonCart.getCart();
    return cart.reduce((total, item) => {
        const price = parseFloat(item.price) || 0;
        const quantity = parseInt(item.quantity) || 0;
        return total + (price * quantity);
    }, 0);
}

// Calculate cart subtotal, tax, and shipping
function calculateCartDetails() {
    const cart = MoonCart.getCart();
    const subtotal = calculateCartTotal();
    const tax = subtotal * 0.1; // 10% tax
    const shipping = subtotal > 5000 ? 0 : 50; // Free shipping over ৳5000
    const total = subtotal + tax + shipping;

    return {
        subtotal,
        tax,
        shipping,
        total,
        itemCount: cart.reduce((sum, item) => sum + (parseInt(item.quantity) || 0), 0),
    };
}

// Render cart page
function renderCart() {
    const cart = MoonCart.getCart();
    const cartTableBody = document.querySelector(".cart-table tbody");
    const cartSummary = document.querySelector(".cart-summary");

    if (!cartTableBody) return;

    // Clear existing content
    cartTableBody.innerHTML = "";

    if (cart.length === 0) {
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
    cart.forEach((item) => {
        console.log("Rendering cart item:", item); // Debug log
        const row = document.createElement("tr");
        const itemTotal = (parseFloat(item.price) || 0) * (parseInt(item.quantity) || 0);
        row.innerHTML = `
            <td data-label="Image">
                <img src="${item.image}" alt="${item.name}" class="cart-item-image">
            </td>
            <td data-label="Product">${item.name}</td>
            <td data-label="Price">${MoonCart.formatCurrency(parseFloat(item.price) || 0)}</td>
            <td data-label="Quantity">
                <div class="quantity-control">
                    <button class="quantity-btn" onclick="updateQuantity('${item.id}', -1)">-</button>
                    <span>${item.quantity}</span>
                    <button class="quantity-btn" onclick="updateQuantity('${item.id}', 1)">+</button>
                </div>
            </td>
            <td data-label="Total">${MoonCart.formatCurrency(itemTotal)}</td>
            <td data-label="Remove">
                <button class="remove-btn" onclick="removeFromCart('${item.id}')">Remove</button>
            </td>
        `;
        cartTableBody.appendChild(row);
    });

    // Render cart summary
    if (cartSummary) {
        cartSummary.style.display = "block";
        const details = calculateCartDetails();
        console.log("Cart details:", details); // Debug log

        cartSummary.innerHTML = `
            <h3 style="margin-bottom: 20px;">Cart Summary</h3>
            <div class="summary-row">
                <span>Subtotal (${details.itemCount} items):</span>
                <span>${MoonCart.formatCurrency(details.subtotal)}</span>
            </div>
            <div class="summary-row">
                <span>Tax (10%):</span>
                <span>${MoonCart.formatCurrency(details.tax)}</span>
            </div>
            <div class="summary-row">
                <span>Shipping:</span>
                <span>${
                    details.shipping === 0
                        ? "FREE"
                        : MoonCart.formatCurrency(details.shipping)
                }</span>
            </div>
            <div class="summary-row total">
                <span>Total:</span>
                <span>${MoonCart.formatCurrency(details.total)}</span>
            </div>
            <button class="btn btn-primary" style="width: 100%; margin-top: 20px;" onclick="proceedToCheckout()">
                Proceed to Checkout
            </button>
            <button class="btn btn-outline" style="width: 100%; margin-top: 10px;" onclick="window.location.href='products.html'">
                Continue Shopping
            </button>
        `;
    }
}

// Proceed to checkout
function proceedToCheckout() {
    const cart = MoonCart.getCart();

    if (cart.length === 0) {
        MoonCart.showNotification("Your cart is empty!", "error");
        return;
    }

    window.location.href = "checkout.html";
}

// Clear cart
function clearCart() {
    if (confirm("Are you sure you want to clear your cart?")) {
        MoonCart.saveCart([]);
        MoonCart.showNotification("Cart cleared!", "success");
        renderCart();
    }
}

// Add to cart from product detail page
function addToCartFromDetail() {
    const product = {
        id: document.getElementById("product-id").value,
        name: document.getElementById("product-name").textContent,
        price: parseFloat(
            document.getElementById("product-price").dataset.price
        ),
        image: document.getElementById("product-image").src,
        category: document.getElementById("product-category").textContent,
    };

    addToCart(product);
}

// Apply coupon code (mock implementation)
function applyCoupon() {
    const couponInput = document.getElementById("coupon-code");
    if (!couponInput) return;

    const code = couponInput.value.trim().toUpperCase();

    const validCoupons = {
        MOONCART10: 10,
        WELCOME20: 20,
        SAVE15: 15,
    };

    if (validCoupons[code]) {
        const discount = validCoupons[code];
        MoonCart.showNotification(
            `Coupon applied! ${discount}% discount`,
            "success"
        );

        // Store coupon in localStorage
        localStorage.setItem(
            "mooncart_coupon",
            JSON.stringify({ code, discount })
        );
        renderCart();
    } else {
        MoonCart.showNotification("Invalid coupon code", "error");
    }
}

// Get applied coupon
function getAppliedCoupon() {
    return JSON.parse(localStorage.getItem("mooncart_coupon")) || null;
}

// Remove coupon
function removeCoupon() {
    localStorage.removeItem("mooncart_coupon");
    MoonCart.showNotification("Coupon removed", "success");
    renderCart();
}

// Initialize cart page if on cart.html
if (window.location.pathname.includes("cart.html")) {
    document.addEventListener("DOMContentLoaded", renderCart);
}

// Initialize product detail page add to cart
if (window.location.pathname.includes("product-detail.html")) {
    document.addEventListener("DOMContentLoaded", function () {
        const addToCartBtn = document.getElementById("add-to-cart-btn");
        if (addToCartBtn) {
            addToCartBtn.addEventListener("click", addToCartFromDetail);
        }
    });
}

// Setup "Add to Cart" buttons on product cards
document.addEventListener("DOMContentLoaded", function () {
    const addToCartButtons = document.querySelectorAll(".add-to-cart");

    addToCartButtons.forEach((button) => {
        button.addEventListener("click", function (e) {
            e.preventDefault();
            const productCard = this.closest(".product-card");

            // Extract price and handle both $ and ৳ symbols
            const priceText = productCard
                .querySelector(".product-price")
                .textContent.trim();
            const priceValue = parseFloat(
                priceText.replace(/[$৳,\s]/g, "")
            );

            const product = {
                id: productCard.dataset.id || MoonCart.generateId(),
                name: productCard.querySelector(".product-name").textContent.trim(),
                price: priceValue,
                image: productCard.querySelector(".product-image img").src,
                category:
                    productCard.querySelector(".product-category")
                        ?.textContent.trim() || "General",
            };

            console.log("Adding product to cart:", product);
            addToCart(product);
        });
    });
});
