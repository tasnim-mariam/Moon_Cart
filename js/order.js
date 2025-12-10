// ==========================================
// MoonCart - Order Management
// Handle order submission, tracking, and status
// ==========================================

// Submit order from checkout page
function submitOrder(event) {
    event.preventDefault();

    // Validate form
    if (!MoonCart.validateForm("checkout-form")) {
        return false;
    }

    MoonCart.showLoading();

    // Get form data
    const formData = new FormData(event.target);
    const orderData = {
        id: MoonCart.generateId(),
        customerId: MoonCart.getCurrentUser()?.id || "guest",
        customerName: formData.get("fullname"),
        email: formData.get("email"),
        phone: formData.get("phone"),
        address: formData.get("address"),
        city: formData.get("city"),
        zipCode: formData.get("zipcode"),
        deliverySlot: formData.get("delivery-slot"),
        paymentMethod: formData.get("payment-method"),
        items: MoonCart.getCart(),
        orderDate: new Date().toISOString(),
        status: "pending",
        ...MoonCart.calculateCartDetails(),
    };

    // Simulate API call delay
    setTimeout(() => {
        // Save order
        const orders = MoonCart.getOrders();
        orders.push(orderData);
        MoonCart.saveOrders(orders);

        // Clear cart
        MoonCart.saveCart([]);

        MoonCart.hideLoading();

        // Show success message
        MoonCart.showNotification("Order placed successfully!", "success");

        // Redirect to order confirmation or customer dashboard
        setTimeout(() => {
            window.location.href = `customer-dashboard.html?order=${orderData.id}`;
        }, 1500);
    }, 1500);

    return false;
}

// Get order by ID
function getOrderById(orderId) {
    const orders = MoonCart.getOrders();
    return orders.find((order) => order.id === orderId);
}

// Get orders by customer
function getOrdersByCustomer(customerId) {
    const orders = MoonCart.getOrders();
    return orders.filter((order) => order.customerId === customerId);
}

// Get all orders (for admin)
function getAllOrders() {
    return MoonCart.getOrders();
}

// Update order status
function updateOrderStatus(orderId, newStatus) {
    const orders = MoonCart.getOrders();
    const orderIndex = orders.findIndex((order) => order.id === orderId);

    if (orderIndex > -1) {
        orders[orderIndex].status = newStatus;
        orders[orderIndex].lastUpdated = new Date().toISOString();

        // Add status history
        if (!orders[orderIndex].statusHistory) {
            orders[orderIndex].statusHistory = [];
        }
        orders[orderIndex].statusHistory.push({
            status: newStatus,
            timestamp: new Date().toISOString(),
        });

        MoonCart.saveOrders(orders);
        MoonCart.showNotification(
            `Order status updated to ${newStatus}`,
            "success"
        );
        return true;
    }

    MoonCart.showNotification("Order not found", "error");
    return false;
}

// Accept order (admin)
function acceptOrder(orderId) {
    return updateOrderStatus(orderId, "confirmed");
}

// Decline order (admin)
function declineOrder(orderId, reason) {
    const orders = MoonCart.getOrders();
    const orderIndex = orders.findIndex((order) => order.id === orderId);

    if (orderIndex > -1) {
        orders[orderIndex].status = "cancelled";
        orders[orderIndex].cancellationReason = reason;
        orders[orderIndex].lastUpdated = new Date().toISOString();
        MoonCart.saveOrders(orders);
        MoonCart.showNotification("Order declined", "success");
        return true;
    }

    return false;
}

// Assign order to delivery person
function assignDelivery(orderId, deliveryPersonId) {
    const orders = MoonCart.getOrders();
    const orderIndex = orders.findIndex((order) => order.id === orderId);

    if (orderIndex > -1) {
        orders[orderIndex].deliveryPersonId = deliveryPersonId;
        orders[orderIndex].status = "out_for_delivery";
        orders[orderIndex].lastUpdated = new Date().toISOString();
        MoonCart.saveOrders(orders);
        MoonCart.showNotification(
            "Order assigned to delivery person",
            "success"
        );
        return true;
    }

    return false;
}

// Complete delivery
function completeDelivery(orderId) {
    return updateOrderStatus(orderId, "delivered");
}

// Render order details
function renderOrderDetails(orderId) {
    const order = getOrderById(orderId);
    const container = document.getElementById("order-details");

    if (!container || !order) return;

    container.innerHTML = `
        <div class="order-header">
            <h2>Order #${order.id.substring(0, 8).toUpperCase()}</h2>
            <span class="status-badge status-${order.status}">${
        order.status
    }</span>
        </div>
        
        <div class="order-info-grid">
            <div class="info-card">
                <h3>Customer Information</h3>
                <p><strong>Name:</strong> ${order.customerName}</p>
                <p><strong>Email:</strong> ${order.email}</p>
                <p><strong>Phone:</strong> ${order.phone}</p>
            </div>
            
            <div class="info-card">
                <h3>Delivery Address</h3>
                <p>${order.address}</p>
                <p>${order.city}, ${order.zipCode}</p>
                <p><strong>Delivery Slot:</strong> ${order.deliverySlot}</p>
            </div>
            
            <div class="info-card">
                <h3>Order Summary</h3>
                <p><strong>Order Date:</strong> ${MoonCart.formatDate(
                    order.orderDate
                )}</p>
                <p><strong>Payment Method:</strong> ${order.paymentMethod}</p>
                <p><strong>Total Items:</strong> ${order.itemCount}</p>
            </div>
        </div>
        
        <div class="order-items">
            <h3>Order Items</h3>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Price</th>
                        <th>Quantity</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                    ${order.items
                        .map(
                            (item) => `
                        <tr>
                            <td>${item.name}</td>
                            <td>${MoonCart.formatCurrency(item.price)}</td>
                            <td>${item.quantity}</td>
                            <td>${MoonCart.formatCurrency(
                                item.price * item.quantity
                            )}</td>
                        </tr>
                    `
                        )
                        .join("")}
                </tbody>
            </table>
        </div>
        
        <div class="order-total">
            <div class="summary-row">
                <span>Subtotal:</span>
                <span>${MoonCart.formatCurrency(order.subtotal)}</span>
            </div>
            <div class="summary-row">
                <span>Tax:</span>
                <span>${MoonCart.formatCurrency(order.tax)}</span>
            </div>
            <div class="summary-row">
                <span>Shipping:</span>
                <span>${
                    order.shipping === 0
                        ? "FREE"
                        : MoonCart.formatCurrency(order.shipping)
                }</span>
            </div>
            <div class="summary-row total">
                <span>Total:</span>
                <span>${MoonCart.formatCurrency(order.total)}</span>
            </div>
        </div>
    `;
}

// Render customer orders
function renderCustomerOrders() {
    const user = MoonCart.getCurrentUser();
    if (!user) {
        window.location.href = "index.html";
        return;
    }

    const orders = getOrdersByCustomer(user.id);
    const container = document.getElementById("customer-orders");

    if (!container) return;

    if (orders.length === 0) {
        container.innerHTML = `
            <div class="empty-state">
                <h3>No orders yet</h3>
                <p>Start shopping to see your orders here!</p>
                <a href="products.html" class="btn btn-primary">Browse Products</a>
            </div>
        `;
        return;
    }

    // Sort orders by date (newest first)
    orders.sort((a, b) => new Date(b.orderDate) - new Date(a.orderDate));

    container.innerHTML = `
        <table class="data-table">
            <thead>
                <tr>
                    <th>Order ID</th>
                    <th>Date</th>
                    <th>Items</th>
                    <th>Total</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                ${orders
                    .map(
                        (order) => `
                    <tr>
                        <td>#${order.id.substring(0, 8).toUpperCase()}</td>
                        <td>${MoonCart.formatDate(order.orderDate)}</td>
                        <td>${order.itemCount} items</td>
                        <td>${MoonCart.formatCurrency(order.total)}</td>
                        <td><span class="status-badge status-${order.status}">${
                            order.status
                        }</span></td>
                        <td>
                            <button class="btn btn-sm" onclick="viewOrderDetails('${
                                order.id
                            }')">View</button>
                        </td>
                    </tr>
                `
                    )
                    .join("")}
            </tbody>
        </table>
    `;
}

// Render admin orders
function renderAdminOrders() {
    const orders = getAllOrders();
    const container = document.getElementById("admin-orders");

    if (!container) return;

    if (orders.length === 0) {
        container.innerHTML = `
            <div class="empty-state">
                <h3>No orders yet</h3>
                <p>Orders will appear here once customers start placing them.</p>
            </div>
        `;
        return;
    }

    // Sort orders by date (newest first)
    orders.sort((a, b) => new Date(b.orderDate) - new Date(a.orderDate));

    container.innerHTML = `
        <table class="data-table">
            <thead>
                <tr>
                    <th>Order ID</th>
                    <th>Customer</th>
                    <th>Date</th>
                    <th>Items</th>
                    <th>Total</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                ${orders
                    .map(
                        (order) => `
                    <tr>
                        <td>#${order.id.substring(0, 8).toUpperCase()}</td>
                        <td>${order.customerName}</td>
                        <td>${MoonCart.formatDate(order.orderDate)}</td>
                        <td>${order.itemCount} items</td>
                        <td>${MoonCart.formatCurrency(order.total)}</td>
                        <td><span class="status-badge status-${order.status}">${
                            order.status
                        }</span></td>
                        <td>
                            <button class="btn btn-sm" onclick="viewOrderDetails('${
                                order.id
                            }')">View</button>
                            ${
                                order.status === "pending"
                                    ? `
                                <button class="btn btn-sm" onclick="acceptOrder('${order.id}')">Accept</button>
                                <button class="btn btn-sm" onclick="showDeclineModal('${order.id}')">Decline</button>
                            `
                                    : ""
                            }
                            ${
                                order.status === "confirmed"
                                    ? `
                                <button class="btn btn-sm" onclick="assignDelivery('${order.id}', 'delivery1')">Assign</button>
                            `
                                    : ""
                            }
                        </td>
                    </tr>
                `
                    )
                    .join("")}
            </tbody>
        </table>
    `;
}

// Render delivery orders
function renderDeliveryOrders() {
    const deliveryPerson = MoonCart.getCurrentUser();
    if (!deliveryPerson || deliveryPerson.role !== "delivery") {
        window.location.href = "index.html";
        return;
    }

    const allOrders = getAllOrders();
    const deliveryOrders = allOrders.filter(
        (order) =>
            order.deliveryPersonId === deliveryPerson.id &&
            order.status === "out_for_delivery"
    );

    const container = document.getElementById("delivery-orders");

    if (!container) return;

    if (deliveryOrders.length === 0) {
        container.innerHTML = `
            <div class="empty-state">
                <h3>No deliveries assigned</h3>
                <p>Check back later for new delivery assignments.</p>
            </div>
        `;
        return;
    }

    container.innerHTML = `
        <table class="data-table">
            <thead>
                <tr>
                    <th>Order ID</th>
                    <th>Customer</th>
                    <th>Address</th>
                    <th>Phone</th>
                    <th>Time Slot</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                ${deliveryOrders
                    .map(
                        (order) => `
                    <tr>
                        <td>#${order.id.substring(0, 8).toUpperCase()}</td>
                        <td>${order.customerName}</td>
                        <td>${order.address}, ${order.city}</td>
                        <td>${order.phone}</td>
                        <td>${order.deliverySlot}</td>
                        <td>
                            <button class="btn btn-sm" onclick="viewOrderDetails('${
                                order.id
                            }')">View</button>
                            <button class="btn btn-primary btn-sm" onclick="completeDelivery('${
                                order.id
                            }')">Complete</button>
                        </td>
                    </tr>
                `
                    )
                    .join("")}
            </tbody>
        </table>
    `;
}

// View order details (opens modal)
function viewOrderDetails(orderId) {
    renderOrderDetails(orderId);
    MoonCart.openModal("order-details-modal");
}

// Show decline modal
function showDeclineModal(orderId) {
    const modal = document.getElementById("decline-modal");
    if (modal) {
        modal.dataset.orderId = orderId;
        MoonCart.openModal("decline-modal");
    }
}

// Confirm decline
function confirmDecline() {
    const modal = document.getElementById("decline-modal");
    const orderId = modal.dataset.orderId;
    const reason = document.getElementById("decline-reason").value;

    if (!reason) {
        MoonCart.showNotification("Please provide a reason", "error");
        return;
    }

    declineOrder(orderId, reason);
    MoonCart.closeModal("decline-modal");

    // Refresh the orders list
    if (window.location.pathname.includes("admin-dashboard.html")) {
        renderAdminOrders();
    }
}

// Initialize checkout page
if (window.location.pathname.includes("checkout.html")) {
    document.addEventListener("DOMContentLoaded", function () {
        // Deprecated: Cart validation is now handled in checkout.html using async loadCart()
        /*
        const cart = MoonCart.getCart();

        if (cart.length === 0) {
            window.location.href = "cart.html";
            return;
        }
        */

        // Display order summary - Handled by checkout.html
        /*
        const orderSummary = document.getElementById("order-summary");
        if (orderSummary) {
            const details = MoonCart.calculateCartDetails();
            orderSummary.innerHTML = `
                <h3>Order Summary</h3>
                <div class="summary-items">
                    ${cart
                        .map(
                            (item) => `
                        <div class="summary-item">
                            <span>${item.name} x ${item.quantity}</span>
                            <span>${MoonCart.formatCurrency(
                                item.price * item.quantity
                            )}</span>
                        </div>
                    `
                        )
                        .join("")}
                </div>
                <div class="summary-row">
                    <span>Subtotal:</span>
                    <span>${MoonCart.formatCurrency(details.subtotal)}</span>
                </div>
                <div class="summary-row">
                    <span>Tax:</span>
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
            `;
        }
        */

        // Setup form submission
        const checkoutForm = document.getElementById("checkout-form");
        if (checkoutForm) {
            checkoutForm.addEventListener("submit", submitOrder);
        }
    });
}
